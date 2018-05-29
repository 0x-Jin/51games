<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 2018/3/21
 * Time: 17:25
 *
 * 254
 */

namespace Fusion\Model;

class EwsModel extends SdkModel
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
        if (!$data["agent"] || !$data["token"] || !$data["uid"] || !$data["userName"]) {
            $res = array(
                "Result"    => false,
                "Data"      => array()
            );
            return $res;
        }

        //获取渠道参数
        $key    = $this->getKey($data["agent"]);

        //拼接参数
        $str    = $data["userName"].$key["AppKey"];

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
        //解析数据
        $str    = base64_decode($data["content"]);
        $info   = json_decode($str, true);
        if (!$info) return false;

        //根据订单号获取渠道号，从而获取开发者的配置参数
        $order  = D("Api/Order")->getOrderById($info["cp_order_id"]);
        if (!$order) return false;

        //获取渠道参数
        $key    = $this->getKey($order["agent"]);

        //进行签名验证
        $sign   = $str."&key=".$key["AppKey"];

        //验证失败
        if($data["sign"] != md5($sign)) return false;

        //组装数据
        $res = array(
            "status"    => $info["payStatus"] == "0"? "success": "fail",                    //success:充值成功的订单，fail及其他:充值失败的订单
            "orderId"   => $info["cp_order_id"],                                            //我们的订单号
            "tranId"    => $info["pay_no"],                                                 //渠道订单号
            "amount"    => $info["amount"]/100                                              //订单金额，单位元
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
     * 失败返回借口
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