<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/11/13
 * Time: 10:51
 *
 * OPPO登陆验证类
 */

class oppoLogin
{

    protected $AppKey       = "";
    protected $AppSecret    = "";
    protected $serverUrl    = "http://i.open.game.oppomobile.com/gameopen/user/fileIdInfo";

    function __construct($AppKey = "", $AppSecret = "")
    {
        $this->AppKey      =$AppKey;
        $this->AppSecret   =$AppSecret;
    }

    /**
     * 登陆验证
     * @param $uid
     * @param $token
     * @return mixed
     */
    public function LoginCheck($uid, $token)
    {
        $request_serverUrl   = $this->serverUrl."?fileId=".$uid."&token=".$token;
        $time                = microtime(true);
        $dataParams['oauthConsumerKey'] 	= $this->AppKey;
        $dataParams['oauthToken'] 			= $token;
        $dataParams['oauthSignatureMethod'] = "HMAC-SHA1";
        $dataParams['oauthTimestamp'] 		= intval($time*1000);
        $dataParams['oauthNonce'] 			= intval($time) + rand(0, 9);
        $dataParams['oauthVersion'] 		= "1.0";
        $requestString 						= $this->_assemblyParameters($dataParams);

        $oauthSignature = $this->AppSecret."&";
        $sign 			= $this->_signatureNew($oauthSignature, $requestString);
        $result 		= $this->_oauthPostExecuteNew($sign, $requestString, $request_serverUrl);
        return json_decode($result, true);			//结果也是一个json格式字符串
    }


    /**
     * 请求的参数串组合
     * @param $dataParams
     * @return string
     */
    private function _assemblyParameters($dataParams)
    {
        $requestString = "";
        foreach ($dataParams as $key => $value) {
            $requestString = $requestString.$key."=".$value."&";
        }
        return $requestString;
    }


    /**
     * 使用HMAC-SHA1算法生成签名
     * @param $oauthSignature
     * @param $requestString
     * @return string
     */
    private function _signatureNew($oauthSignature, $requestString)
    {
        return urlencode(base64_encode(hash_hmac("sha1", $requestString, $oauthSignature, true)));
    }


    /**
     * Oauth身份认证请求
     * @param $sign
     * @param $requestString 请求头值
     * @param $request_serverUrl 请求url
     * @return bool|string
     */
    private function _oauthPostExecuteNew($sign, $requestString, $request_serverUrl)
    {
        $opt = array(
            "http" => array(
                "method"    => "GET",
                "header"    => array("param:".$requestString, "oauthsignature:".$sign),
            )
        );
        $res = file_get_contents($request_serverUrl, null, stream_context_create($opt));
        return $res;
    }
}