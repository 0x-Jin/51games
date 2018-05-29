<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 2018/4/8
 * Time: 10:31
 *
 * PPTV
 */

namespace Fusion\Model;

class PptvModel extends SdkModel
{
    private $channel_id     = "";                                                                           //渠道ID
    private $login_url      = "";                                                                           //充值回调地址
    private $callback_url   = "";                                                                           //充值回调地址

    public function __construct()
    {
        parent::__construct();
        //渠道ID
        $this->channel_id       = $this->getChannelId();
        //验证地址
        $this->login_url        = "http://game.g.pptv.com/api/sdk/integration/check_user_info.php";
        //充值回调地址
        $this->callback_url     = C("COMPANY_DOMAIN")."Api/Reply/ChannelCallback/CyChannelId/".$this->channel_id;
    }

    /**
     * 二登验证
     * @param $data
     * @return array
     */
    public function loginCheck($data)
    {
        //判断必要数据是否齐全
        if (!$data["agent"] || !$data["token"] || !$data["uid"] || !$data["userName"] || !isset($data["platform"]) || !isset($data["subPlatform"])) {
            $res = array(
                "Result"    => false,
                "Data"      => array()
            );
            return $res;
        }

        //获取渠道参数
        $key    = $this->getKey($data["agent"]);

        //拼接参数
        $param  = array(
            "user_id"       => $data["uid"],
            "username"      => $data["userName"],
            "token"         => $data["token"],
            "ext"           => $data["extra"],
            "platform"      => $data["platform"],
            "sub_platform"  => $data["subPlatform"],
            "time"          => time(),
            "gid"           => $key["AppId"]
        );
        ksort($param);
        $param["sign"]  = md5(http_build_query($param).$key["LoginKey"]);

        // 发起请求
        $result = curl_post($this->login_url, http_build_query($param));
        $Result = json_decode($result, true);

        //验证失败
        if ($Result["code"] != 1) {
            $res = array(
                "Result"    => false,
                "Data"      => array()
            );
            return $res;
        } else {
            //用户数据
            $res = array(
                "Result"    => true,
                "Data"      => array(
                    "channelUserCode"   => $data["uid"],
                    "channelUserName"   => $data["userName"]
                )
            );
            return $res;
        }
    }

    /**
     * 回调验证接口
     * @param $data
     * @return array|bool
     */
    public function callbackCheck($data)
    {
        //根据订单号获取渠道号，从而获取开发者的配置参数
        $order  = D("Api/Order")->getOrderById($data["out_trade_no"]);
        if (!$order) return false;

        //获取渠道参数
        $key    = $this->getKey($order["agent"]);

        //组装参数
        $info   = $data;
        unset($info["sign"]);

        ksort($info);
        //验证失败
        if($data["sign"] != md5(http_build_query($info).$key["PayKey"])) return false;

        //组装数据
        $res = array(
            "status"    => "success",                                                       //success:充值成功的订单，fail及其他:充值失败的订单
            "orderId"   => $data["out_trade_no"],                                           //我们的订单号
            "tranId"    => $data["trade_no"],                                               //渠道订单号
            "amount"    => $data["amount"]                                                  //订单金额，单位元
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
        $res = array(
            "code"      => 1,
            "data"      => array(),
            "message"   => "成功"
        );
        echo json_encode($res);
        exit();
    }

    /**
     * 失败返回接口
     * @param 错误类型 $num
     * @param array $data 返回数据
     */
    public function callbackErr($num, $data = array())
    {
        //返回失败
        $res = array("data" => array());

        switch ($num) {
            case 1:
                $res["code"]    = 1001;
                $res["message"] = "签名错误";
                break;
            case 3:
                $res["code"]    = 1006;
                $res["message"] = "充值金额错误";
                break;
            default:
                $res["code"]    = 1002;
                $res["message"] = "参数缺失";
        }

        echo json_encode($res);
        exit();
    }
}