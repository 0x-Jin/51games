<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 2018/5/4
 * Time: 15:17
 *
 * 微信H5支付
 */

namespace Vendor\WeixinH5;

class WeixinH5
{
    private $appid          = "a20170823000072514";
    private $mch_id         = "m20170823000072514";
    private $mch_key        = "db9fd06a689288b7f77bdc93ddb8719b";
    private $notify_url     = "http://apisdk.chuangyunet.net/Api/Reply/weixinH5Callback";
    private $time_out       = 5;
    private $weixinH5Url    = "https://api.mch.weixin.qq.com/pay/unifiedorder";

    public function __construct()
    {

    }

    /**
     * 提交订单
     * @param $orderId
     * @param $amount
     * @param $subject
     * @param $ip
     * @return bool
     */
    public function submitOrder($orderId, $amount, $subject, $ip)
    {
        //参数
        $param = array(
            "appid"             => $this->appid,
            "mch_id"            => $this->mch_id,
            "nonce_str"         => "cmgc".time().rand(10000, 99999),
            "body"              => $subject,
            "out_trade_no"      => $orderId,
            "total_fee"         => intval($amount*100),
            "spbill_create_ip"  => $ip,
            "notify_url"        => $this->notify_url,
            "trade_type"        => "MWEB",
            "scene_info"        => json_encode(array("h5_info" => array("type" => "Wap", "wap_url" => "https://www.cmgcwl.cn/", "wap_name" => "贵诚网络")))
        );
        $param["sign"]  = $this->createSign($param);

        //生成XML格式
        $xml    = $this->toXml($param);

        //发送请求
        $res    = $this->postXml($xml, $this->weixinH5Url);
        if (!$res["status"]) return false;

        //解析返回的数据
        $result = $this->parseXml($xml);

        //检验签名是否正确
        if ($result["sign"] != $this->createSign($result)) return false;

        //是否是正确的返回
        if (isset($result["return_code"]) && isset($result["result_code"]) && $result["return_code"] == "SUCCESS" && $result["result_code"] == "SUCCESS") {
            return $result["mweb_url"];
        } else {
            return false;
        }
    }

    /**
     * 充值回调
     * @return array
     */
    public function callback()
    {
        $xml    = file_get_contents("php://input");

        //提取数据
        $info   = $this->parseXML($xml);

        //判断数据是否正常
        if ($info["return_code"] != "SUCCESS") {
            $res = array("return_code" => "SUCCESS");
            return array(
                "Code"  => false,
                "Res"   => $res,
                "Msg"   => "info_error"
            );
        }

        //验证
        if ($this->createSign($info) != $info["sign"]) {
            $res = array("return_code" => "FAIL", "return_msg" => "签名错误");
            return array(
                "Code"  => false,
                "Res"   => $res,
                "Msg"   => "sign_error"
            );
        }

        //返回数据
        return array(
            "Code"  => true,
            "Res"   => $info,
            "Msg"   => "info"
        );
    }

    /**
     * 回调输出
     * @param $arr
     */
    public function callbackMsg($arr)
    {
        echo $this->toXml($arr);
        exit();
    }

    /**
     * 生成签名
     * @param $param
     * @return string
     */
    private function createSign($param)
    {
        ksort($param);
        $str = "";
        foreach ($param as $key => $value) {
            if ($key != "sign" && $value) $str .= $key."=".$value."&";
        }
        $str .= "key=".$this->mch_key;
        return strtoupper(md5($str));
    }

    /**
     * 生成XML格式
     * @param $param
     * @return string
     */
    private function toXml($param)
    {
        $xml = "<xml>";
        foreach ($param as $key => $value) {
            $xml .= "<".$key."><![CDATA['.$value.']]></".$key.">";
        }
        $xml .= "</xml>";
        return $xml;
    }

    /**
     * 解析XML
     * @param $str
     * @return array
     */
    private function parseXml($str)
    {
        $xml    = simplexml_load_string($str);
        $ret    = preg_match ("/<?xml[^>]* encoding=\"(.*)\"[^>]* ?>/i", $str, $arr);
        if ($ret) {
            $code = strtoupper($arr[1]);
        } else {
            $code = "";
        }

        $list   = array();
        if ($xml && $xml->children()) {
            foreach ($xml->children() as $node){
                //有子节点
                if ($node->children()) {
                    $key    = $node->getName();
                    $nodeXml = $node->asXML();
                    $val    = substr($nodeXml, strlen($key)+2, strlen($nodeXml)-2*strlen($key)-5);
                } else {
                    $key    = $node->getName();
                    $val    = (string)$node;
                }
                if ($code != "" && $code != "UTF-8") {
                    $key    = iconv("UTF-8", $code, $key);
                    $val    = iconv("UTF-8", $code, $val);
                }
                $list[$key] = $val;
            }
        }
        return $list;
    }

    /**
     * 请求
     * @param $xml
     * @param $url
     * @return mixed
     */
    private function postXml($xml, $url)
    {
        $ch     = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->time_out);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//TRUE
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);//2严格校验
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, false);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: text/xml"));
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //运行curl
        $data   = curl_exec($ch);
        //返回结果
        if ($data) {
            curl_close($ch);
            return array("status" => true, "data" => $data);
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            return array("status" => false, "data" => $error);
        }
    }
}