<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/8/23
 * Time: 15:52
 */

namespace Vendor\tnbpay;

class tnbpay
{

    private $mch_id     = "m20170823000072514";
    private $mch_appid  = "a20170823000072514";
    private $mch_key    = "db9fd06a689288b7f77bdc93ddb8719b";
    private $notify_url = "http://apisdk.chuangyunet.net/Api/Reply/tnbpayCallback";
    private $return_url = "http://apisdk.chuangyunet.net/html/tnbpayPayBack.html";

    public function __construct()
    {

    }

    /**
     * 提交订单
     * @param $orderId
     * @param $amount
     * @param $subject
     * @return bool
     */
    public function submitOrder($orderId, $amount, $subject)
    {
        include_once("corefire/CorefireWxPay.Data.php");
        include_once("corefire/CorefireWxPay.Api.php");

        $input = new \CorefireWxPayJsWapOrder($this->mch_key);
        $input->SetAppid($this->mch_appid);
        $input->SetMch_id($this->mch_id);
        $input->SetMethod("mbupay.wxpay.jswap2");
        $input->SetBody($subject);
        $input->SetOut_trade_no($orderId);
        $input->SetTotal_fee($amount * 100);
        $input->SetNotify_url($this->notify_url);
        $input->SetReturn_url($this->return_url);

        $order = \CorefireWxPayApi::jswap($input);

        if (isset($order['return_code']) && isset($order['result_code']) && $order['return_code'] == 'SUCCESS' && $order['result_code'] == 'SUCCESS') {
            return $order['prepay_id'];
        } else {
            return false;
        }
    }

    /**
     * 充值回调
     * @param $xml
     * @return array
     */
    public function callback($xml)
    {

        include_once("Utils.class.php");

        //提取数据
        $info = \Utils::parseXML($xml);

        //判断数据是否正常
        if ($info["return_code"] != "SUCCESS") {
            $res = array("return_code" => "SUCCESS");
            return array(
                "Code"  => false,
                "Res"   => $res,
                "Msg"   => "info error"
            );
        }

        //验证
        $str = "";
        ksort($info);
        foreach ($info as $k => $v) {
            if ($v !== "" && $v !== null && $k != "sign") $str .= $k."=".$v."&";
        }
        if (strtoupper(md5($str."key=".$this->mch_key)) != $info["sign"]) {
            $res = array("return_code" => "FAIL", "return_msg" => "签名错误");
            return array(
                "Code"  => false,
                "Res"   => $res,
                "Msg"   => "sign error"
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
        include_once("Utils.class.php");

        echo \Utils::toXml($arr);
        exit();
    }
}