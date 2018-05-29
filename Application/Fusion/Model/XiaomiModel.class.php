<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/11/7
 * Time: 10:21
 *
 * 小米
 */

namespace Fusion\Model;

class XiaomiModel extends SdkModel
{

    private $channel_id     = "";                                                                           //渠道ID
    private $login_url      = "";                                                                           //验证地址
    private $callback_url   = "";                                                                           //充值回调地址

    public function __construct(){
        parent::__construct();
        //渠道ID
        $this->channel_id   = $this->getChannelId();
        //验证地址
        $this->login_url    = "http://mis.migc.xiaomi.com/api/biz/service/verifySession.do";
        //充值回调地址
        $this->callback_url = C("COMPANY_DOMAIN")."Api/Reply/ChannelCallback/CyChannelId/".$this->channel_id;
    }

    /**
     * 初始化接口
     * @param $agent
     * @return array
     */
    public function init($agent)
    {
        $key = $this->getKey($agent);
        $res = array(
            "AppId"     => $key["AppId"],
            "AppKey"    => $key["AppKey"]
        );
        return $res;
    }

    /**
     * 二登验证
     * @param $data
     * @return array
     */
    public function loginCheck($data)
    {
        //判断必要数据是否齐全
        if (!$data["agent"] || !$data["token"] || !$data["uid"]) {
            $res = array(
                "Result"    => false,
                "Data"      => array()
            );
            return $res;
        }

        //获取渠道参数
        $key    = $this->getKey($data["agent"]);

        require_once(LIB_PATH."/Org/Xiaomi/SignatureHelper.php");
        require_once(LIB_PATH."/Org/Xiaomi/HttpHelper.php");

        //参数
        $param              = array("appId" => $key["AppId"], "uid" => $data["uid"], "session" => $data["token"]);
        $signObj            = new \SignatureHelper();
        $param["signature"] = $signObj->sign($param, $key["AppSecret"]);

        //发起请求
        $request            = new \HttpHelper();
        $Res                = $request->get($this->login_url, $param);

        if ($Res != false) {
            //解码
            $result = json_decode(urldecode($Res), true);

            if ($result["errcode"] == "200") {
                //用户数据
                $res = array(
                    "Result"    => true,
                    "Data"      => array(
                        "channelUserCode"   => $data["uid"],
                        "channelUserName"   => $data["userName"]? $data["userName"]: $data["uid"]
                    )
                );
                return $res;
            } else {
                $res = array(
                    "Result"    => false,
                    "Data"      => array()
                );
                return $res;
            }
        } else {
            $res = array(
                "Result"    => false,
                "Data"      => array()
            );
            return $res;
        }
    }

    /**
     * 获取数据接口
     * @return mixed
     */
    public function getInput()
    {
        $data   = $_REQUEST;
        unset($data["CyChannelId"], $data["CyChannelVer"]);
        return $data;
    }

    /**
     * 回调验证接口
     * @param $data
     * @return array|bool
     */
    public function callbackCheck($data)
    {
        //根据订单号获取渠道号，从而获取开发者的配置参数
        $order  = D("Api/Order")->getOrderById($data["cpOrderId"]);
        if (!$order) return false;

        //获取渠道参数
        $key    = $this->getKey($order["agent"]);

        require_once(LIB_PATH."/Org/Xiaomi/SignatureHelper.php");
        require_once(LIB_PATH."/Org/Xiaomi/HttpHelper.php");

        $httpHelper = new \HttpHelper();
        $params     = array();
        //组装参数
        foreach ($data as $k => $value) {
            if ($k != "signature") $params[$k] = $httpHelper->urlDecode($value);
        }
        $signature  = $data["signature"];
        $signObj    = new \SignatureHelper();

        if ($signObj->verifySignature($params, $signature, $key["AppSecret"])) {
            //组装数据
            $res = array(
                "status"    => $data["orderStatus"] == "TRADE_SUCCESS"? "success": "fail",      //success:充值成功的订单，fail及其他:充值失败的订单
                "orderId"   => $data["cpOrderId"],                                              //我们的订单号
                "tranId"    => $data["orderId"],                                                //渠道订单号
                "amount"    => $data["payFee"]/100                                              //订单金额，单位元
            );

            return $res;
        } else {
            return false;
        }
    }

    /**
     * 成功返回接口
     * @param array $data 返回数据
     */
    public function callbackSuc($data = array())
    {
        //返回成功
        $Result["errcode"]  = 200;
        $Result["errMsg"]   = "成功";
        echo json_encode($Result);
        exit();
    }

    /**
     * 失败返回借口
     * @param 错误类型 $num
     * @param array $data 返回数据
     */
    public function callbackErr($num, $data = array())
    {
        $Result["status"]   = "error";
        $Result["delivery"] = "other";
        switch ($num) {
            case 1:
                $Result["errcode"]  = 1525;
                $Result["errMsg"]   = "验签失败";
                break;
            case 2:
                $Result["errcode"]  = 1506;
                $Result["errMsg"]   = "订单号不匹配";
                break;
            case 3:
                $Result["errcode"]  = 3515;
                $Result["errMsg"]   = "价格不匹配";
                break;
            case 4:
            case 5:
            default:
                $Result["errcode"]  = 3515;
                $Result["errMsg"]   = "其他错误";
        }
        echo json_encode($Result);
        exit();
    }
}