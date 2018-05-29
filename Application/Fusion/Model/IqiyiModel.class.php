<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 2018/3/26
 * Time: 11:15
 *
 * 爱奇艺
 */

namespace Fusion\Model;

class IqiyiModel extends SdkModel
{
    private $channel_id     = "";                                                                           //渠道ID
    private $callback_url   = "";                                                                           //充值回调地址

    public function __construct()
    {
        parent::__construct();
        //渠道ID
        $this->channel_id       = $this->getChannelId();
        //充值回调地址
        $this->callback_url     = C("COMPANY_DOMAIN")."Api/Reply/ChannelCallback/CyChannelId/".$this->channel_id;
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
            "GameId"    => $key["GameId"]
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
        if (!$data["agent"] || !$data["token"] || !$data["uid"] || !$data["time"]) {
            $res = array(
                "Result"    => false,
                "Data"      => array()
            );
            return $res;
        }

        //获取渠道参数
        $key    = $this->getKey($data["agent"]);

        //验证失败
        if ($data["token"] != md5($data["uid"]."&".$data["time"]."&".$key["LoginKey"])) {
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
                    "channelUserName"   => $data["userName"]? $data["userName"]: $data["uid"]
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
        $order  = D("Api/Order")->getOrderById($data["userData"]);
        if (!$order) return false;

        //获取渠道参数
        $key    = $this->getKey($order["agent"]);

        //进行签名验证
        $str    = $data["user_id"].$data["role_id"].$data["order_id"].$data["money"].$data["time"].$key["PayKey"];

        //验证失败
        if($data["sign"] != md5($str)) return false;

        //组装数据
        $res = array(
            "status"    => "success",                                                       //success:充值成功的订单，fail及其他:充值失败的订单
            "orderId"   => $data["userData"],                                               //我们的订单号
            "tranId"    => $data["order_id"],                                               //渠道订单号
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
        echo json_encode(array("result" => 0, "message" => "充值成功"));
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
        echo json_encode(array("result" => 0, "message" => "fail"));

        switch ($num) {
            case 1:
                $Result["result"]   = -1;
                $Result["message"]  = "验证失败";
                break;
            case 2:
                $Result["code"]     = -2;
                $Result["message"]  = "订单不存在";
                break;
            case 3:
                $Result["code"]     = -2;
                $Result["message"]  = "订单金额不一致";
                break;
            case 4:
                $Result["code"]     = -2;
                $Result["message"]  = "订单记录错误";
                break;
            case 5:
                $Result["code"]     = -3;
                $Result["message"]  = "订单用户不一致";
                break;
            default:
                $Result["code"]     = -6;
                $Result["message"]  = "其他错误";
        }
        echo json_encode($Result);
        exit();
    }
}