<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 2018/1/19
 * Time: 15:07
 *
 * 拇指游玩
 */

namespace Fusion\Model;

class MuzhiModel extends SdkModel
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
            "GameId"    => $key["GameId"],
            "PacketId"  => $key["PacketId"],
            "AESKey"    => $key["AESKey"],
            "AESAPIKey" => $key["AESAPIKey"]
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
        if (!$data["agent"] || !$data["token"] || !$data["uid"] || !$data["time"] || ($data["time"] > (time() + 600) * 1000)) {
            $res = array(
                "Result"    => false,
                "Data"      => array()
            );
            return $res;
        }

        //获取渠道参数
        $key    = $this->getKey($data["agent"]);

        //拼接参数
        $str    = $data["uid"].$key["GameKey"].$data["time"];

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
        //解析数据
        $str    = base64_decode($data["content"]);
        $info   = json_decode($str, true);
        if (!$info) return false;

        //判断是否是测试订单
        if (strpos($info["cp_order_id"], "_") !== false) {
            $param                  = explode("_", $info["cp_order_id"]);
            $info["cp_order_id"]    = $param[0];
        }

        //根据订单号获取渠道号，从而获取开发者的配置参数
        $order  = D("Api/Order")->getOrderById($info["cp_order_id"]);
        if (!$order) return false;

        //获取渠道参数
        $key    = $this->getKey($order["agent"]);

        //进行签名验证
        $sign   = $str."&key=".$key["GameKey"];

        //验证失败
        if($data["sign"] != md5($sign)) return false;

        //组装数据
        $res = array(
            "status"    => $info["payStatus"] == "0"? "success": "fail",                    //success:充值成功的订单，fail及其他:充值失败的订单
            "orderId"   => $info["cp_order_id"],                                            //我们的订单号
            "tranId"    => $info["pay_no"],                                                 //渠道订单号
            "amount"    => $info["amount"]/100,                                             //订单金额，单位元
            "sandbox"   => (isset($param) && $param[1] == "test")? "1": "0"                 //0：正式环境，1：沙箱环境
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
        echo "success";
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
        echo "fail";
        exit();
    }
}