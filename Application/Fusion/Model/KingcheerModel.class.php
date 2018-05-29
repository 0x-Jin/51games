<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 2018/4/11
 * Time: 14:16
 *
 * 鲸旗
 */

namespace Fusion\Model;

class KingcheerModel extends SdkModel
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
        $this->login_url        = "http://app.kingcheergame.cn/api/cp/v1/checkUser";
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

        //拼接参数
        $param  = array(
            "appId" => $key["AppId"],
            "uid"   => $data["uid"],
            "token" => $data["token"]
        );
        ksort($param);
        $str    = "";
        foreach ($param as $k => $v) {
            $str .= $k.$v;
        }
        //生成签名
        $param["sign"] = md5($str.$key["AppKey"]);

        // 发起请求
        $result = $this->curlPost($this->login_url, json_encode($param));
        $Result = json_decode($result, true);

        //验证失败
        if (!$result || $Result["head"]["responseCode"] != "00000") {
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
                    "channelUserCode"   => $Result["body"]["uid"],
                    "channelUserName"   => $Result["body"]["userName"]? $Result["body"]["userName"]: ($data["userName"]? $data["userName"]: $Result["body"]["uid"])
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
        $order  = D("Api/Order")->getOrderById($data["billno"]);
        if (!$order) return false;

        //获取渠道参数
        $key    = $this->getKey($order["agent"]);

        //进行签名验证
        ksort($data);
        $str    = "";
        foreach ($data as $k => $v) {
            if ($k != "sign") $str .= $k.$v;
        }

        //验证失败
        if($data["sign"] != md5($str.$key["PayKey"])) return false;

        //组装数据
        $res = array(
            "status"    => "success",                                                       //success:充值成功的订单，fail及其他:充值失败的订单
            "orderId"   => $data["billno"],                                                 //我们的订单号
            "tranId"    => $data["orderId"],                                                //渠道订单号
            "amount"    => $data["amount"],                                                 //订单金额，单位元
            "sandbox"   => $data["test"] == "1"? "1": "0"                                   //0：正式环境，1：沙箱环境
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

    /**
     * POST方法，json形式传递数据
     * @param $url
     * @param $body
     * @return mixed
     */
    private function curlPost($url, $body)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Content-Type: application/json; charset=utf-8",
            ]
        );
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        $resp = curl_exec($ch);
        curl_close($ch);
        return $resp;
    }
}