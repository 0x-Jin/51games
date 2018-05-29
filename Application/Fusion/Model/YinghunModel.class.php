<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 2018/1/22
 * Time: 11:17
 *
 * 鹰魂
 */

namespace Fusion\Model;

class YinghunModel extends SdkModel
{
    private $channel_id     = "";                                                                           //渠道ID
    private $login_url      = "";                                                                           //获取用户信息地址
    private $callback_url   = "";                                                                           //充值回调地址

    public function __construct()
    {
        parent::__construct();
        //渠道ID
        $this->channel_id       = $this->getChannelId();
        //验证地址
        $this->login_url        = "http://token.aiyinghun.com/user/token";
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
        if (!$data["agent"] || !$data["token"]) {
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
            "gameId"    => $key["GameId"],
            "channelId" => $data["channelId"],
            "appId"     => $data["appId"],
            "sid"       => $data["token"],
            "userId"    => $data["uid"]
        );
        ksort($param);
        $str    = "";
        foreach ($param as $k => $v) {
            $str .= $k."=".$v;
        }

        //生成签名
        $param["sign"] = md5($str.$key["AppSecret"]);

        // 发起请求
        $result = curl_post($this->login_url, http_build_query($param));
        $Result = json_decode($result, true);

        //验证失败
        if (!$result || $Result["ret"] != "0") {
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
                    "channelUserCode"   => $Result["content"]["data"]["userId"],
                    "channelUserName"   => $data["userName"]? $data["userName"]: $Result["content"]["data"]["userId"],
                    "extInfo"           => $Result["content"]["data"]
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
        $order  = D("Api/Order")->getOrderById($data["cpOrderId"]);
        if (!$order) return false;

        //获取渠道参数
        $key    = $this->getKey($order["agent"]);

        //进行签名验证
        ksort($data);
        $str    = "";
        foreach ($data as $k => $v) {
            if ($k != "sign") $str .= $k."=".$v;
        }

        //验证失败
        if($data["sign"] != md5($str.$key["AppSecret"])) return false;

        //组装数据
        $res = array(
            "status"    => $data["orderStatus"] == "1"? "success": "fail",                  //success:充值成功的订单，fail及其他:充值失败的订单
            "orderId"   => $data["cpOrderId"],                                              //我们的订单号
            "tranId"    => $data["bfOrderId"],                                              //渠道订单号
            "amount"    => $data["money"]/100                                               //订单金额，单位元
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
        echo json_encode(array("ret" => 0));
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
        echo json_encode(array("ret" => 1));
        exit();
    }
}