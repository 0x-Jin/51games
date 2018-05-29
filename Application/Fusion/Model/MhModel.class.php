<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 2018/5/15
 * Time: 9:57
 *
 * 墨海
 */

namespace Fusion\Model;

class MhModel extends SdkModel
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
        $this->login_url        = "http://apksdk.goleuu.com/cpVerify.php";
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
        if (!$data["agent"] || !$data["token"] || !$data["uid"]) {
            $res = array(
                "Result"    => false,
                "Data"      => array()
            );
            return $res;
        }

        //获取渠道参数
        $key    = $this->getKey($data["agent"]);

        //请求参数
        $param  = array(
            "id"        => time(),
            "appid"     => $key["AppId"],
            "username"  => $data["uid"],
            "token"     => $data["token"]
        );

        // 发起请求
        $result = $this->curl_post($this->login_url, json_encode($param));
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
        $order  = D("Api/Order")->getOrderById($data["attach"]);
        if (!$order) return false;

        //获取渠道参数
        $key    = $this->getKey($order["agent"]);

        //进行签名验证
        $str    = "orderid=".$data["orderid"]."&username=".urlencode($data["username"])."&appid=".$data["appid"]."&roleid=".urlencode($data["roleid"])."&serverid=".urlencode($data["serverid"])."&amount=".$data["amount"]."&paytime=".$data["paytime"]."&attach=".$data["attach"]."&productname=".urlencode($data["productname"])."&appkey=".$key["AppKey"];

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

    /**
     * 数据请求
     * @param $url
     * @param $data
     * @param int $timeout
     * @return mixed
     */
    private function curl_post($url, $data, $timeout = 5)
    {
        $ch     = curl_init();
        $header = array(
            "Content-Type: application/json",
        );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header); //定义请求类型
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_POST, true);
        if (strpos(strtolower($url), "https://") !== false) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $returnTransfer = curl_exec($ch);
        curl_close($ch);
        return $returnTransfer;
    }
}