<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/11/2
 * Time: 15:41
 *
 * 360
 */

namespace Fusion\Model;

class QihooModel extends SdkModel
{

    private $channel_id     = "";                                                                           //渠道ID
    private $order_url      = "";                                                                           //下单地址
    private $callback_url   = "";                                                                           //充值回调地址

    public function __construct(){
        parent::__construct();
        //渠道ID
        $this->channel_id   = $this->getChannelId();
        //下单地址
        $this->order_url    = "https://mgame.360.cn/srvorder/get_token.json";
        //充值回调地址
        $this->callback_url = C("COMPANY_DOMAIN")."Api/Reply/ChannelCallback/CyChannelId/".$this->channel_id;
    }

    /**
     * 二登验证
     * @param $data
     * @return array
     */
    public function loginCheck($data)
    {
        //判断必要数据是否齐全
        if (!$data["agent"] || !$data["token"]) {
            $res = array(
                "Result"    => false,
                "Data"      => array()
            );
            return $res;
        }

        //获取渠道参数
        $key    = $this->getKey($data["agent"]);

        require_once(LIB_PATH."/Org/Qihoo/common.inc.php");
        $sdk    = new \Qihoo_OAuth2($key["APPID"], $key["APPKEY"], "");

        //二登验证
        $Res    = $sdk->userMe($data["token"]);
        if ($Res) {
            //用户数据
            $res = array(
                "Result"    => true,
                "Data"      => array(
                    "channelUserCode"   => $Res["id"],
                    "channelUserName"   => $Res["name"]
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
    }

    /**
     * 下单前的数据整理
     * @param $data
     * @param $orderId
     * @return array|bool
     */
    public function beforePay($data, $orderId)
    {
        //获取游戏信息
        $game           = D("Api/Game")->getGame($data["gid"]);
        $Res["unit"]    = $game["unit"];
        $Res["ratio"]   = $game["ratio"];

        return $Res;
    }

    /**
     * 获取数据接口
     * @return mixed
     */
    public function getInput()
    {
        $data = $_REQUEST;
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
        $order  = D("Api/Order")->getOrderById($data["app_order_id"]);
        if (!$order) return false;

        //获取渠道参数
        $key    = $this->getKey($order["agent"]);

        require_once(LIB_PATH."/Org/Qihoo/common.inc.php");
        $myApp  = new \PayApp_Demo($key["APPKEY"], $key["APPSECRET"]);
        $sdk    = new \Qihoo_Pay($myApp);
        $res    = $sdk->processRequest($data);

        //验证失败
        if ($res != "ok") return false;

        //组装数据
        $res = array(
            "status"    => $data["gateway_flag"],                                           //success:充值成功的订单，fail及其他:充值失败的订单
            "orderId"   => $data["app_order_id"],                                           //我们的订单号
            "tranId"    => $data["order_id"],                                               //渠道订单号
            "amount"    => $data["amount"]/100                                              //订单金额，单位元
        );

        return $res;
    }

    /**
     * 成功返回接口
     * @param array $data 返回数据
     */
    public function callbackSuc($data = array())
    {
        //返回成功
        $Result["status"]   = "ok";
        $Result["delivery"] = "success";
        $Result["msg"]      = "成功";
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
                $Result["msg"]  = "验签失败";
                break;
            case 2:
                $Result["msg"]  = "订单号不匹配";
                break;
            case 3:
                $Result["msg"]  = "价格不匹配";
                break;
            case 4:
                $Result["msg"]  = "验签失败";
                break;
            case 5:
            default:
                $Result["msg"]  = "其他错误";
        }
        echo json_encode($Result);
        exit();
    }
}