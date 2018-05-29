<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 2018/2/8
 * Time: 16:37
 *
 * 杭动
 */

namespace Fusion\Model;

class HangdongModel extends SdkModel
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
        $this->login_url        = "http://api.hangdongyouxi.com/api/cp/user/check";
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
            "user_token"    => $data["token"],
            "mem_id"        => $data["uid"],
            "app_id"        => $key["AppId"]
        );
        //生成签名
        $param["sign"] = md5("app_id=".$key["AppId"]."&mem_id=".$data["uid"]."&user_token=".$data["token"]."&app_key=".$key["AppKey"]);

        // 发起请求
        $result = curl_post($this->login_url, http_build_query($param));
        $Result = json_decode($result, true);

        //验证失败
        if (!$result || $Result["status"] != "1") {
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
        $order  = D("Api/Order")->getOrderById($data["cp_order_id"]);
        if (!$order) return false;

        //获取渠道参数
        $key    = $this->getKey($order["agent"]);

        //进行签名验证
        $str    = "app_id=".$data["app_id"]."&cp_order_id=".$data["cp_order_id"]."&mem_id=".$data["mem_id"]."&order_id=".$data["order_id"]."&order_status=".$data["order_status"]."&pay_time=".$data["pay_time"]."&product_id=".$data["product_id"]."&product_name=".urlencode($data["product_name"])."&product_price=".$data["product_price"]."&app_key=".$key["AppKey"];

        //验证失败
        if($data["sign"] != md5($str)) return false;

        //组装数据
        $res = array(
            "status"    => $data["order_status"] == "2"? "success": "fail",                 //success:充值成功的订单，fail及其他:充值失败的订单
            "orderId"   => $data["cp_order_id"],                                            //我们的订单号
            "tranId"    => $data["order_id"],                                               //渠道订单号
            "amount"    => $data["product_price"]                                           //订单金额，单位元
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
        echo "FAILURE";
        exit();
    }
}