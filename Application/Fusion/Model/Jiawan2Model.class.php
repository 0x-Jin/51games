<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 2018/3/22
 * Time: 10:29
 *
 * 嘉玩v2 （IOS专用）
 */

namespace Fusion\Model;

class Jiawan2Model extends SdkModel
{
    private $channel_id                 = "";                                                                           //渠道ID
    private $callback_url               = "";
    private $jw_aliWapAppId             = "";
    private $jw_aliWapRsaPrivateKey     = "";
    private $jw_aliWapRsaPublicKey      = "";
    private $jw_aliWapPayRsaPublicKey   = "";
    private $jw_aliWapCallback          = "";

    public function __construct()
    {
        parent::__construct();
        //渠道ID
        $this->channel_id                   = $this->getChannelId();
        //充值回调地址
        $this->callback_url                 = C("COMPANY_DOMAIN")."Api/Reply/ChannelCallback/CyChannelId/".$this->channel_id."/CyChannelVer/2";
        //支付宝网页快捷支付应用ID
        $this->jw_aliWapAppId               = "2018032202425682";
        //支付宝网页快捷支付私钥
        $this->jw_aliWapRsaPrivateKey       = "MIIEowIBAAKCAQEArGGTb5yM9YZfyqFCneJlQ4cVsT5bt3q8KDKlBsozGXAvC+eMgMLBljsId/yNWIT6B+krw6ToRk2Zt5bN0T0pPcoPkm0OzMjG2LU4OGHiuGV11gyx/mYOyUvS3vspd9LQ0ZYEXCrrn/x4dcGIByiP6Dlf8KXdRUGydRPC7/fKv8ttJLzrx5KJgmZlncAWVQbnYlHiD2OwYc1M4ywvYBzWAWwy0MnAk4m+jweEy5IV1JsVxXCA7U7etmE2HpeL0VyWLVVsxX3l7OmvFkF+ZgAmQ7qANxv+eZ89exp/YZXlkkaMqw9f3jhejvvV2IycyD6mMWOkszLUTF0zSAf1f78QLQIDAQABAoIBACX7OekVrVlLyj9zWKJBB97hHL545ux+dobE4eelFa09MqCE3EhioRTg3PTTCLHAWvbzQVlSNHuJDZ2N9LttpnCe3N9+eAxXELke9Mw3hSTr9hK7qVxMUGW59zR6UqC8KpaDX2KPcmtFzaTkh3xMS6j3O1Rit2ZTG2cAe6s0BdBMPVdOAcqWUNsFxMdKnGbSWb+OOeJxy2KoZcPI+dIxipOg97R3LdfH0G6sHNxpM3hJHTRvpTNJF3HaE9NfO3bbdodVRfylKuZPNkbJj8JK59tTaITbcsYeoXLfETXq3iPCAxGvzD6pCyOBSbgSG18JJ5fn8lBiQUeh7hplrelJ8mECgYEA49nQotjufSJvFC4cU3MKbNK6qb9jDQdgIhw9RHJ4IXpAQ6if5AfH2N8gvGz6hzXrc5quGRY5y3QsQitYqp348FQJicAJvYqJwEZkhg8mlu6fxVhm72HkkG2pA7tey7EYnzK7HTh+06iPrXiV0hmDuRP87Y8qOv5Cemcu1KjsbAsCgYEAwa1vLEW4sbkPAcfeIQgEsdcSlcPz6XqyOt8Q/7iKPZWTRJU/zdBxnl/UbJjq9EjMeBZkWC/lp/v5kWy8/FLk5lTC7Z/ZShPoAbXpVbxaBKffzZYppfhcOdyA3rLoUIICjIkn5573EGvyPBCdPLEz7ha+U238p+FD5HwVQguV36cCgYEAlg9r4vRwDSXSdj3wFd2cLhOTMByGBZyn6Y8joqKpD5NOI8E4nJurON+q1a4ISWhvixGCO69xnNcEFwgpOyUTQGR4a6p4P8av9lvl9Iyh46GAxB32nQ2h1KUEPRr30bru9loY9aOxk4BeL+dM9LQtFoVdfK9fJr8x1R+DrjcajgUCgYBA9wsGf7CPNLL69u7kh4sDmE/smpkTZQupwa8zB9SfCbAnXiTTxaqG5EAd2UFehZjIY0JvbkmLinLRO/c8cBXFyQLFsEuzlG/LOxi0oIRVcXYZwNfhiyhsZDF6Aer5LlLqjwsqn2DiSkMrsKr9c7cmksxuscMBEQez+Ycr6zTvmwKBgBKnxfQhtywe7pYTgd6Tn0Br3duvo9uMwQ44/6bMtXVIy+Bjudj5c9d3rgLH23kGx5CG7zfVGZddMHJSYg0opXQeya4gBjro/pBD2Za6OqwnYOQqKoiz8DUNCYSaijxYTtNBPMxjCGpd1V/tvaLBnQmLl3J+GwBTCTnrpsVnSPYy";
        //支付宝网页快捷支付公钥
        $this->jw_aliWapRsaPublicKey        = "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEArGGTb5yM9YZfyqFCneJlQ4cVsT5bt3q8KDKlBsozGXAvC+eMgMLBljsId/yNWIT6B+krw6ToRk2Zt5bN0T0pPcoPkm0OzMjG2LU4OGHiuGV11gyx/mYOyUvS3vspd9LQ0ZYEXCrrn/x4dcGIByiP6Dlf8KXdRUGydRPC7/fKv8ttJLzrx5KJgmZlncAWVQbnYlHiD2OwYc1M4ywvYBzWAWwy0MnAk4m+jweEy5IV1JsVxXCA7U7etmE2HpeL0VyWLVVsxX3l7OmvFkF+ZgAmQ7qANxv+eZ89exp/YZXlkkaMqw9f3jhejvvV2IycyD6mMWOkszLUTF0zSAf1f78QLQIDAQAB";
        //支付宝网页快捷支付支付宝公钥
        $this->jw_aliWapPayRsaPublicKey     = "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAnhDAtRMjObZri/byr1XClRijRHlEYzTwj5vTlDTjM0V0CNopH2UTTtdhKeNRNz4wxv+nxu0jiO72tZ2axG+SFhhYxaA/BaPIWy2ySnkmIFYpsSUoZWd2SY6qDmSZ21z3hBUWAFzVVLAwnFUm7mQYSSRFedvi+sS5tbS4nzNKG6HiRS68Fi+BXJBho4FZxgJVI7G9C9oj7IHizTrv9lRndLxS72QKgmSKopp3n3KUDe94TznAhMMAnhG91nzg1vg5/0/5zN/jOYM8KcODikAtmFvqzDMvY2xI/MVi7ylL99l4Xp1YxWlFtu46e97y1M83W3YVCCmSEm+n0cklJXH9IwIDAQAB";
        //支付宝网页快捷支付回调地址
        $this->jw_aliWapCallback            = "http://apisdk.chuangyunet.net/Api/Reply/ChannelCallback/CyChannelId/27/CyChannelVer/2/PayType/1";
    }

    /**
     * 支付宝支付
     * @param $order
     * @return string|\提交表单HTML文本
     */
    public function aliPay($order)
    {
        require_once(LIB_PATH."Org/Jiawan2/AopClient.php");
        require_once(LIB_PATH."Org/Jiawan2/request/AlipayTradeWapPayRequest.php");
        $aop                        = new \jw_AopClient();
        $aop->gatewayUrl            = "https://openapi.alipay.com/gateway.do";
        $aop->appId                 = $this->jw_aliWapAppId;
        $aop->rsaPrivateKey         = $this->jw_aliWapRsaPrivateKey;
        $aop->alipayrsaPublicKey    = $this->jw_aliWapPayRsaPublicKey;
        $aop->apiVersion            = "1.0";
        $aop->postCharset           = "UTF-8";
        $aop->format                = "json";
        $aop->signType              = "RSA2";
        $request                    = new \jw_AlipayTradeWapPayRequest();
        $request->setNotifyUrl($this->jw_aliWapCallback);
        $request->setBizContent("{" .
            "    \"body\":\"".urlencode($order["subject"])."\"," .
            "    \"subject\":\"".urlencode($order["subject"])."\"," .
            "    \"out_trade_no\":\"{$order["orderId"]}\"," .
            "    \"timeout_express\":\"60m\"," .
            "    \"total_amount\":{$order["amount"]}," .
            "    \"product_code\":\"QUICK_WAP_WAY\"" .
            "  }");
        $result                     = $aop->pageExecute($request, "GET");
        return $result;
    }

    /**
     * 微信下单
     * @param $data
     * @param $order
     * @return array|bool
     */
    public function weixinPay($data, $order)
    {
        /**国连四方下单开始**/
//        require_once(LIB_PATH."/Org/Jiawan2/bbnpay.class.php");
//
//        $class  = new \jw_bbnpay();
//
//        //下单
//        $res    = $class->submitOrder($order["orderId"], $order["amount"]);
//        return $res? C("COMPANY_DOMAIN")."Api/Pay/bbnWeixinPay?Url=".urlencode($res): false;
        /**国连四方下单结束**/

        /**威富通下单开始**/
        //初始化类
        require_once(LIB_PATH."/Org/Jiawan2/SwiftPass/Request.class.php");
        $cla    = new \jw_Request();

        //需要传递的参数
        $info   = array(
            "out_trade_no"  => $data["orderId"],
            "body"          => $order["subject"],
            "total_fee"     => intval($order["amount"] * 100),
            "service"       => "pay.weixin.raw.app",
            "notify_url"    => "http://apisdk.chuangyunet.net/Api/Reply/ChannelCallback/CyChannelId/27/CyChannelVer/2/PayType/2/PayChannel/2",
            "mch_create_ip" => get_ip_address()
        );

        //提交数据
        $res = $cla->submitOrderInfo($info, 2);
        return ($res && $res["status"] == 200)? $res["code_url"]: false;
        /**威富通下单结束**/
    }

    /**
     * 获取数据接口
     * @return mixed
     */
    public function getInput()
    {
        $data               = $_POST;
        $data["payType"]    = $_GET["PayType"];
        $data["PayChannel"] = $_GET["PayChannel"];
        unset($data["CyChannelId"], $data["CyChannelVer"], $data["PHPSESSID"]);
        return $data;
    }

    /**
     * 回调验证接口
     * @param $data
     * @return array|bool
     */
    public function callbackCheck($data)
    {
        if ($data["PayChannel"] == 2) {
            if ($data["payType"] == 2) {
                unset($data["payType"], $data["PayChannel"]);

                require_once(LIB_PATH."/Org/Jiawan2/SwiftPass/Request.class.php");

                $class  = new \jw_Request();

                //回调验证
                $info   = $class->callback();

                //验证失败
                if (!$info) return false;

                //组装数据
                $res = array(
                    "status"    => $info["pay_result"] == "0"? "success": "fail",                   //success:充值成功的订单，fail及其他:充值失败的订单
                    "orderId"   => $info["out_trade_no"],                                           //我们的订单号
                    "tranId"    => $info["transaction_id"],                                         //渠道订单号
                    "amount"    => $info["total_fee"]/100                                           //订单金额，单位元
                );

                return $res;
            }
        } else {
            if ($data["payType"] == 1) {
                unset($data["payType"], $data["PayChannel"]);

                require_once(LIB_PATH."/Org/Jiawan2/AopClient.php");

                $aop = new \jw_AopClient;

                $aop->alipayrsaPublicKey    = $this->jw_aliWapPayRsaPublicKey;

                //此处验签方式必须与下单时的签名方式一致
                if (!$aop->rsaCheckV1($data, NULL, "RSA2")) return false;

                //组装数据
                $res = array(
                    "status"    => in_array($data["trade_status"], array("TRADE_SUCCESS", "TRADE_FINISHED"))? "success": "fail",    //success:充值成功的订单，fail及其他:充值失败的订单
                    "orderId"   => $data["out_trade_no"],                                           //我们的订单号
                    "tranId"    => $data["trade_no"],                                               //渠道订单号
                    "amount"    => $data["total_amount"]                                            //订单金额，单位元
                );

                return $res;
            } elseif ($data["payType"] == 2) {
                unset($data["payType"], $data["PayChannel"]);

                require_once(LIB_PATH."/Org/Jiawan2/bbnpay.class.php");

                $class  = new \jw_bbnpay();

                //回调验证
                $info   = $class->callback($data);

                //验证失败
                if (!$info) return false;

                //组装数据
                $res = array(
                    "status"    => $info["result"] == "1"? "success": "fail",                       //success:充值成功的订单，fail及其他:充值失败的订单
                    "orderId"   => $info["cporderid"],                                              //我们的订单号
                    "tranId"    => $info["transid"],                                                //渠道订单号
                    "amount"    => $info["money"] / 100                                             //订单金额，单位元
                );

                return $res;
            }
        }
        return false;
    }

    /**
     * 成功返回接口
     * @param array $data 返回数据
     */
    public function callbackSuc($data = array())
    {
        //返回成功
        echo ($data["payType"] == 1)? "success": "SUCCESS";
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
        echo ($data["payType"] == 1)? "failure": "FAIL";
        exit();
    }
}