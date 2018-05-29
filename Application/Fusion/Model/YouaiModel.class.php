<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 2018/4/3
 * Time: 17:17
 *
 * 游爱
 */

namespace Fusion\Model;

class YouaiModel extends SdkModel
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
     * 二登验证
     * @param $data
     * @return array
     */
    public function loginCheck($data)
    {
        //判断必要数据是否齐全
        if (!$data["agent"] || !$data["token"] || !$data["uid"] || !$data["userType"] || !$data["timestamp"]) {
            $res = array(
                "Result"    => false,
                "Data"      => array()
            );
            return $res;
        }

        //获取渠道参数
        $key    = $this->getKey($data["agent"]);

        //生成字符串
        $str    = "gameAppkey=".$key["GameKey"]."&userType=".$data["userType"]."&openId=".$data["uid"]."&timestamp=".$data["timestamp"];

        //验证失败
        if ($data["token"] != md5($str)) {
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
        $order  = D("Api/Order")->getOrderById($data["custom"]);
        if (!$order) return false;

        //获取渠道参数
        $key    = $this->getKey($order["agent"]);

        //进行签名验证
        $str    = "serverId=".$data["serverId"]."&playerId=".$data["playerId"]."&orderId=".$data["orderId"]."&gameAppKey=".$key["GameKey"];

        //验证失败
        if($data["serverSign"] != md5($str)) return false;

        //组装数据
        $res = array(
            "status"    => "success",                                                       //success:充值成功的订单，fail及其他:充值失败的订单
            "orderId"   => $data["custom"],                                                 //我们的订单号
            "tranId"    => $data["orderId"],                                                //渠道订单号
            "amount"    => $data["payAmount"],                                              //订单金额，单位元
            "sandbox"   => substr($data["orderId"], -8) == "_sandbox"? "1": "0"                    //0：正式环境，1：沙箱环境
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
        echo json_encode(array("code" => 1, "message" => "成功"));
        exit();
    }

    /**
     * 失败返回接口
     * @param 错误类型 $num
     * @param array $data 返回数据
     */
    public function callbackErr($num, $data = array())
    {
        switch ($num) {
            case 1:
                $msg = "签名错误";
                break;
            case 2:
                $msg = "订单错误";
                break;
            case 3:
                $msg = "金额错误";
                break;
            case 4:
                $msg = "保存错误";
                break;
            case 5:
                $msg = "用户错误";
                break;
            default:
                $msg = "未知错误";
        }
        echo json_encode(array("code" => 0, "message" => $msg));
        exit();
    }
}