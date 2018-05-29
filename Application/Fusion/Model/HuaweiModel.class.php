<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/11/7
 * Time: 16:34
 *
 * 华为
 */

namespace Fusion\Model;

class HuaweiModel extends SdkModel
{

    private $channel_id     = "";                                                                           //渠道ID
    private $callback_url   = "";                                                                           //充值回调地址
    private $huawei_public  = "";                                                                           //华为公钥

    public function __construct(){
        parent::__construct();
        //渠道ID
        $this->channel_id       = $this->getChannelId();
        //充值回调地址
        $this->callback_url     = C("COMPANY_DOMAIN")."Api/Reply/ChannelCallback/CyChannelId/".$this->channel_id;
        //华为公钥
        $this->huawei_public    = "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAmKLBMs2vXosqSR2rojMzioTRVt8oc1ox2uKjyZt6bHUK0u+OpantyFYwF3w1d0U3mCF6rGUnEADzXiX/2/RgLQDEXRD22er31ep3yevtL/r0qcO8GMDzy3RJexdLB6z20voNM551yhKhB18qyFesiPhcPKBQM5dnAOdZLSaLYHzQkQKANy9fYFJlLDo11I3AxefCBuoG+g7ilti5qgpbkm6rK2lLGWOeJMrF+Hu+cxd9H2y3cXWXxkwWM1OZZTgTq3Frlsv1fgkrByJotDpRe8SwkiVuRycR0AHsFfIsuZCFwZML16EGnHqm2jLJXMKIBgkZTzL8Z+201RmOheV4AQIDAQAB";
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
            "CP_ID"         => $key["CP_ID"],
            "APP_ID"        => $key["APP_ID"],
            "PAY_ID"        => $key["PAY_ID"],
            "BUO_SECRET"    => $key["BUO_SECRET"]
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
        if (!$data["agent"] || !$data["token"] || !$data["uid"]) {
            $res = array(
                "Result"    => false,
                "Data"      => array()
            );
            return $res;
        }

        //获取渠道参数
        $key    = $this->getKey($data["agent"]);
        //参数拼接
        $str    = $key["APP_ID"].$data["ts"].$data["uid"];
        //生成公钥
        $pubKey = "-----BEGIN PUBLIC KEY-----\n".chunk_split($this->huawei_public, 64, "\n")."-----END PUBLIC KEY-----\n";
        $openssl_public_key = @openssl_get_publickey($pubKey);
        $result = @openssl_verify($str, base64_decode($data["token"]), $openssl_public_key, OPENSSL_ALGO_SHA256);
        @openssl_free_key($openssl_public_key);

        //验证失败
        if (!$result) {
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
     * 下单前的数据整理
     * @param $data
     * @param $orderId
     * @return array|bool
     */
    public function beforePay($data, $orderId)
    {
        //判断必要数据是否存在
        if (!$data["agent"] || !$data["userCode"] || !$data["goodsCode"]) return false;

        //获取渠道参数
        $key    = $this->getKey($data["agent"]);

        //获取用户信息
        $user   = D("Api/User")->getUserByCode($data["userCode"]);

        //商品信息
        $goods  = D("Api/Goods")->getGoods($data["goodsCode"]);

        //组装需要签名的数据
        $info   = array(
            "userID"        => $key["PAY_ID"],
            "applicationID" => $key["APP_ID"],
            "amount"        => $goods["amount"],
            "productName"   => $goods["name"],
            "requestId"     => $orderId,
            "productDesc"   => $goods["name"]
        );

        ksort($info);
        $arr    = array();
        foreach ($info as $k => $v) {
            if ($v) $arr[] = $k."=".$v;
        }
        //加密字符串
        $str    = implode("&", $arr);
        //生成私钥
        $priKey = "-----BEGIN PRIVATE KEY-----\n".chunk_split($key["PAY_RSA_PRIVATE"], 64, "\n")."-----END PRIVATE KEY-----\n";
        $openssl_private_key = openssl_get_privatekey($priKey);
        @openssl_sign($str, $signature, $openssl_private_key, OPENSSL_ALGO_SHA256);
        @openssl_free_key($openssl_private_key);

        $info["sign"]       = base64_encode($signature);
        $info["userName"]   = $user["channelUserName"];
        $info["signType"]   = "RSA256";

        return $info;
    }

    /**
     * 回调验证接口
     * @param $data
     * @return array|bool
     */
    public function callbackCheck($data)
    {
        //根据订单号获取渠道号，从而获取开发者的配置参数
        $order  = D("Api/Order")->getOrderById($data["requestId"]);
        if (!$order) return false;

        //获取渠道参数
        $key    = $this->getKey($order["agent"]);

        $str    = "";
        ksort($data);
        foreach ($data as $k => $v) {
            if ($k != "sign" && $k != "signType") $str .= $k."=".$v."&";
        }

        //生成公钥
        $pubKey = "-----BEGIN PUBLIC KEY-----\n".chunk_split($key["PAY_RSA_PUBLIC"], 64, "\n")."-----END PUBLIC KEY-----\n";
        $openssl_public_key = @openssl_get_publickey($pubKey);
        $result = @openssl_verify(trim($str, "&"), base64_decode($data["sign"]), $openssl_public_key, OPENSSL_ALGO_SHA256);
        @openssl_free_key($openssl_public_key);

        //验证失败
        if (!$result) return false;

        //组装数据
        $res = array(
            "status"    => $data["result"] == "0"? "success": "fail",                       //success:充值成功的订单，fail及其他:充值失败的订单
            "orderId"   => $data["requestId"],                                              //我们的订单号
            "tranId"    => $data["orderId"],                                                //渠道订单号
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
        $Result["result"]   = 0;
        echo json_encode($Result);
        exit();
    }

    /**
     * 失败返回借口
     * @param 错误类型 $num
     * @param array $data 返回数据
     */
    public function callbackErr($num, $data = array())
    {
        switch ($num) {
            case 1:
                $Result["result"]   = 1;
                break;
            case 2:
            case 3:
            case 5:
                $Result["result"]   = 3;
                break;
            case 4:
            default:
                $Result["result"]   = 99;
        }
        echo json_encode($Result);
        exit();
    }
}