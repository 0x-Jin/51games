<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 2018/4/13
 * Time: 14:09
 *
 * 银狐
 */

namespace Fusion\Model;

class InfoxgameModel extends SdkModel
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
        $this->login_url        = "https://sdkapi.infoxgame.com/user/cptoken.do";
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

        // 发起请求
        $result = curl_get($this->login_url."?token=".$data["token"]);
        $Result = json_decode($result, true);

        //验证失败
        if (!$result || $Result["code"] != "1") {
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
                    "channelUserCode"   => $Result["data"]["userId"],
                    "channelUserName"   => $Result["data"]["userName"]
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
        $order  = D("Api/Order")->getOrderById($data["extension"]);
        if (!$order) return false;

        //获取渠道参数
        $key    = $this->getKey($order["agent"]);

        //进行签名验证
        $str    = "orderID=".$data["orderID"]."userID=".$data["userID"]."appID=".$data["appID"]."serverID=".$data["serverID"]."money=".$data["money"]."currency=".$data["currency"]."extension=".$data["extension"]."appKey=".$key["ServerAppKey"];

        //验证失败
        if($data["sign"] != md5($str)) return false;

        //组装数据
        $res = array(
            "status"    => $data["state"] == "1"? "success": "fail",                        //success:充值成功的订单，fail及其他:充值失败的订单
            "orderId"   => $data["extension"],                                              //我们的订单号
            "tranId"    => $data["orderID"],                                                //渠道订单号
            "amount"    => $data["money"]                                                   //订单金额，单位元
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
        echo "SUCCESS";
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
        echo "FAIL";
        exit();
    }
}