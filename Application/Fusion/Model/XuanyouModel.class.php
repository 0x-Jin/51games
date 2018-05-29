<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 2018/4/8
 * Time: 17:43
 *
 * 炫游
 */

namespace Fusion\Model;

class XuanyouModel extends SdkModel
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
        $this->login_url        = "http://gw1.xy.youyantech.com/user/verify.php";
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
            "app_id"        => $key["GameId"],
            "session_id"    => $data["token"],
            "time"          => time()
        );
        $param["sign"]  = md5($key["GameId"].$data["token"].$param["time"].$key["GameKey"]);

        // 发起请求
        $result = curl_get($this->login_url."?".http_build_query($param));
        $Result = json_decode($result, true);

        //验证失败
        if ($Result["ret"] != "0") {
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
        $order  = D("Api/Order")->getOrderById($data["app_order_id"]);
        if (!$order) return false;

        //获取渠道参数
        $key    = $this->getKey($order["agent"]);

        //组装参数
        $info   = $data;
        unset($info["sign"]);
        ksort($info);
        $str    = implode($info).$key["GameKey"];
        $pem    = "-----BEGIN PUBLIC KEY-----\n".chunk_split($key["PayKey"], 64, "\n")."-----END PUBLIC KEY-----\n";
        $pubKey = openssl_get_publickey($pem);

        //验证失败
        if(!openssl_verify($str, base64_decode($data["sign"]), $pubKey)) return false;

        //组装数据
        $res = array(
            "status"    => "success",                                                       //success:充值成功的订单，fail及其他:充值失败的订单
            "orderId"   => $data["app_order_id"],                                           //我们的订单号
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
        $res = array(
            "ret" => 0,
            "msg" => "success"
        );
        echo json_encode($res);
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
        $res = array(
            "ret" => $num,
            "msg" => "fail"
        );
        echo json_encode($res);
        exit();
    }
}