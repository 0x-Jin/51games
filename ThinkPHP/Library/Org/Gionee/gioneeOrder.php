<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/11/14
 * Time: 16:57
 *
 * 金立订单类
 */

class gioneeOrder
{

    protected $uri          = "";
    protected $port         = "";
    protected $host         = "";
    protected $method       = "";
    protected $orderUrl     = "";
    protected $apiKey       = "";
    protected $secretKey    = "";
    protected $publicKey    = "";
    protected $privateKey   = "";

    function __construct($publicKey = "", $privateKey = "")
    {
        $this->uri          = "/account/verify.do";
        $this->port         = "443";
        $this->host         = "id.gionee.com";
        $this->method       = "POST";
        $this->orderUrl     = "https://pay.gionee.com/amigo/create/order";
        $this->publicKey    = $publicKey;
        $this->privateKey   = $privateKey;
    }

    public function createOrder($data)
    {
        $data["sign"]   = $this->rsa_sign($data);
        $json = json_encode($data);

        $return_json    = $this->https_curl($this->orderUrl, $json);
        $return_arr     = json_decode($return_json, 1);

        //订单创建成功的状态码判断
        if ($return_arr["status"] !== "200010000") {
            return false;
        } else {
            return array("order_no" => $return_arr["order_no"], "info" => $return_json);
        }
    }

    public function rsa_verify($data){
        ksort($data);
        $str = "";
        foreach($data as $key => $value){
            if($key == "sign") continue;
            $str .= $key."=".$value."&";
        }
        $signature_str = substr($str, 0, -1);
        $pem = chunk_split($this->publicKey, 64, "\n");
        $pem = "-----BEGIN PUBLIC KEY-----\n".$pem."-----END PUBLIC KEY-----\n";
        $public_key_id = openssl_pkey_get_public($pem);
        $signature = base64_decode($data["sign"]);
        $res = openssl_verify($signature_str, $signature, $public_key_id);
        openssl_free_key($public_key_id);
        return $res;
    }

    private function rsa_sign($data)
    {
        ksort($data);
        $str = "";
        foreach($data as $key => $value){
            $str .= $value;
        }
        $pem = chunk_split($this->privateKey, 64, "\n");
        $pem = "-----BEGIN PRIVATE KEY-----\n".$pem."-----END PRIVATE KEY-----\n";
        $private_key_id = openssl_pkey_get_private($pem);
        $signature = false;
        openssl_sign($str, $signature, $private_key_id);
        $sign = base64_encode($signature);
        openssl_free_key($private_key_id);
        return $sign;
    }

    private function https_curl($url, $post_arr = array(), $timeout = 10)
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_arr);
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        $content = curl_exec($curl);
        curl_close($curl);

        return $content;
    }
}