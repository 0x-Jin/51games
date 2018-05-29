<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 2018/3/21
 * Time: 9:36
 *
 * 美图接口函数库
 */


/**
 * 使用密钥生成 HMAC-Sha1 签名
 * @param array $params 请求参数
 * @param string $signKey 签名密钥
 * @return string
 */
function hmacSha1Sign($params, $signKey)
{
    ksort($params);
    $paramString = "";
    foreach ($params as $key => $value) {
        if (is_null($value) || $value == "" || $key == "sign") continue;
        $paramString .= $key."=".$value."&";
    }
    $paramString = substr($paramString, 0, -1);
    $sign = base64_encode(hash_hmac("sha1", $paramString, $signKey, true));
    return $sign;
}