<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/11/14
 * Time: 11:29
 *
 * 金立登陆类
 */

class gioneeLogin
{

    protected $uri          = "";
    protected $port         = "";
    protected $host         = "";
    protected $method       = "";
    protected $verifyUrl    = "";
    protected $apiKey       = "";
    protected $secretKey    = "";

    function __construct($apiKey = "", $secretKey = "")
    {
        $this->uri          = "/account/verify.do";
        $this->port         = "443";
        $this->host         = "id.gionee.com";
        $this->method       = "POST";
        $this->verifyUrl    = "https://id.gionee.com/account/verify.do";
        $this->apiKey       = $apiKey;
        $this->secretKey    = $secretKey;
    }

    /**
     * 登陆验证
     * @param $content
     * @return bool
     */
    public function loginCheck($content)
    {
        $ts     =  time();
        $nonce  = strtoupper(substr(uniqid(),0,8)) ;

        $signature_str  = $ts."\n".$nonce."\n".$this->method."\n".$this->uri."\n".$this->host."\n".$this->port."\n\n";
        $signature      = base64_encode(hash_hmac("sha1", $signature_str, $this->secretKey, true));
        $Authorization  = "MAC id=\"{$this->apiKey}\",ts=\"{$ts}\",nonce=\"{$nonce}\",mac=\"{$signature}\"";
        $result_json    = $this->doCurl($content, $Authorization);
        $result_arr     = json_decode($result_json, true);
        if (isset($result_arr["r"])) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @param $content
     * @param $Authorization
     * @return mixed
     */
    private function doCurl($content, $Authorization)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->verifyUrl);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: ".$Authorization));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        $result = curl_exec ($ch);
        curl_close($ch);
        return $result;
    }
}