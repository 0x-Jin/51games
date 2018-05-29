<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 2018/1/29
 * Time: 17:57
 *
 * 乐游
 */

namespace Fusion\Model;

class LeyouModel extends SdkModel
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
        $this->login_url        = "http://game.6y.com.cn/sdk/islogin.php";
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

        //拼接参数
        $param  = array(
            "username"  => $data["uid"],
            "memkey"    => $data["token"]
        );

        // 发起请求
        $result = curl_post($this->login_url, http_build_query($param));

        //验证失败
        if (!$result || $result != "success") {
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
                    "channelUserName"   => $data["uid"]
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
        $order  = D("Api/Order")->getOrderById($data["attach"]);
        if (!$order) return false;

        //获取渠道参数
        $key    = $this->getKey($order["agent"]);

        //进行签名验证
        $str    = "orderid=".urlencode($data["orderid"])."&username=".urlencode($data["username"])."&gameid=".urlencode($data["gameid"])."&roleid=".urlencode($data["roleid"])."&serverid=".urlencode($data["serverid"])."&paytype=".urlencode($data["paytype"])."&amount=".urlencode($data["amount"])."&paytime=".urlencode($data["paytime"])."&attach=".urlencode($data["attach"])."&appkey=".$key["AppKey"];

        //验证失败
        if($data["sign"] != md5($str)) return false;

        //组装数据
        $res = array(
            "status"    => "success",                                                       //success:充值成功的订单，fail及其他:充值失败的订单
            "orderId"   => $data["attach"],                                                 //我们的订单号
            "tranId"    => $data["orderid"],                                                //渠道订单号
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
        echo "error";
        exit();
    }
}