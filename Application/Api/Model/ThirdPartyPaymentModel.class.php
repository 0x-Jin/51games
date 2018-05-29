<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/6/8
 * Time: 11:25
 *
 * 第三方支付模块
 */

namespace Api\Model;

class ThirdPartyPaymentModel
{
    //支付宝网页快捷支付应用ID
//    private $aliWapAppId            = "2017101609334952";
    private $aliWapAppId            = "2017122001008933";
    //支付宝网页快捷支付私钥
//    private $aliWapRsaPrivateKey    = "MIIEpAIBAAKCAQEAt/N6cGIzM3KNTFhwdEBaj5fTYEUl3B79MCudt7U5TFXShN9uHXeP4PjBi4z42nGy74J+NDA5rD9Tt+PX5US/N+WZl/suQSsXt4SqESmTqaxzVgcJyRIGIatdUMyCF12nsykpKzA82YTrn5uCFCJ78XxYWpKwFZE24Sp/hzvU9gF6NyqqFz896R4bfjWxeLFcVYCGReEZgOsTGV2WYut2TVUiQ3YRTyFmuT7NMR1cQE6b+TusbTxwpFKr0ug06k/ie2EoS3j83D0dtkYOOqjPMfqwOls79JnhBH0umdJCUVQPIUnF14+6XI+tuU838WV+t2R9i78O+Vh6qHs7GTtHcQIDAQABAoIBAEpqJM3x6+SUIrUP0e41Wm1cbhYz4uz5XFNwUY1Psq4+ybsW5+TjyUCpYSKjFMjJ1ikuEP/rwmj79VIeyeflt7VVHJ1u73dNh3qmIGZw+1tYeBAFKOA+elyEhmt5T+dD6+N+czkVeohETU10C1s52AoI03VQggs3g4vzNUPv4Gbj4WS/NiCzw+ZlzCd4ZFJZS3tXl6PjgmG/pkc/JV/Ed+/Et+jEYjdLXmkDsdUyy1V4KLViAMA1QhP/27oznPyFKIxN4L+IPX6/tHXalTRWTew2QRNWkeXiURsYNZzxbrr3vAvaLmVLv7YAB8VB10SHufpJxFy22EsNLgBinwFZ9AECgYEA8z01+WL0gudvsrznBRTwSY0Mw6YZ387xwp2PQ9gepr0V7k4Um9WLvKK3R6cKETHQErO+mLP5nBUIubbIVvL3FNJvxvCtZgAYGf8hbZIMDTeV91BuFlVqbZAkzLCUC0qyYefnKebd0ATV4+FhN6iqQ4sJoN1WmMUIy8iGGxs+OmkCgYEAwZoCBsJo96AZwmOjiEnSZdts7wIVphnGj+o34Dm/wjxjR+msvzYX2XnZm9r8k76WCYbkOKB70TnVdVWad7BCCyKfrioKzpDHDMbpMb2qy4hVwywhoARW7zsXfL5hLD71zy284/DAY8J8KoHIVm5wKRPojEfecsmAapxvzP1Is8kCgYEAwMB66xtto+Z+72ceszo6iC6MNOaFjoPRtWViSGMVNxCUNnoNfsgkqeFP2CoYojOVLZzepufIH9XTSkf8TlrPTeLMzRugbToZ30/8T3Xysu6fmpJUCsK5SgV2A/bR+njBDzDUULIwiE2sqZ7KiW7RLiEaCi4cF4fcRpCrJQPbtjECgYEAuBJc0N5w/NedBkTEDGXcBHo/NXPu73FcaCLSI3/kwaG2533WgCrHPD/tVZE9SqAPeVlmjiyP7NsnCBtu9VOBR6MQ3YAdrE7c4loRB/kEdeXXXO0Nv13A5k5xw2oja/Uks2oSSUrzMSaN9cBVoU/5liTWmuIOL2dEkJjSd71uLXkCgYBlysTulH62WQd+l9VYvvYTZ1tXW0hYETeHqehnXFl2LjlyzVQVYIRe6P8HmgX8/gOAGYZbsS2ZboRwZbPdUU/YKZYlW6xQg8LuNpEL2DMJpEVXbQyxvLOUXYaHMWTPnB5k7P5+RpUuQvGp8k6y4lkC5rrYEZ/M3MUfx0kbtBubEg==";
    private $aliWapRsaPrivateKey    = "MIIEowIBAAKCAQEAylumftffPln13NWGOlja2B7/5nuNjHs0fZNY90fqUIHmxvzgC0J04lRxEe14X1VaJbO+EAU24B0Swvaevwm5alLzi9SchHyqvvxALFKAU0LxgHpjABMcU3MV6IO2ZDOXA7VpBXnrqgOJbvzTF8kXjNfq4jhcjDGsYpfufrI7VzXOqoPsRv9PbS8XvrXClsTQ4lAWzRKNzVT1nvmiIPuszJNYuhKkF+b3av3tFHSLwK5ppPaTcncx2EvWBvkpIm0qUQxvLbHvoccIfdjoLXMHvMZj9mO0A4zYnh5jsFTiEJVjWcL3IcdezJx60OT0fCCiaO0v7STmo/uO7buofVPdGQIDAQABAoIBAAapyeu3VASLlcr1xPu63Unsi2SdgOW1UN8psO5DkfWgsWawAPid8ZdaTHbYqPQKrXM8Xe/NHCd5DZsZ39ROj6punNJn59d8+2paiAptlQgo1iby4Lup5W7iBUCoxaK7CCf5G0Iw54+rmiTrclAAMSRUH8nfLTz4jcKZWiMV31pZviUOs4TmstxCAH6DKa1674sRT0an+Sph5kJDIIYxVHfuSKn1YsYNm9P3/4iDJkwMkOw3RjdVczuQW4BTVnCZp2EzUJkzopLVv+0qxXMZtQXnDVcP4HvOT/XldFmjM9Kt1plLr6r+1yrYZwkLfLE5KRrGjEfijQvS0MR6yxrBO3kCgYEA/pATtzkaorQ4OY5f4LCGwmpbcWvAXe/xSh8j8RhuSjkGFMx00HeGr+siRD05ALlrhKAtDX73n75IGMM5BEMOP+TJ+oE20fgKxxUObgVCDZBxAMFBwWi+lI34sONCKyiDwwjKKRXB668LQL6IK+daXBzfA0gpVxkIkPtgi/GTYNsCgYEAy4Ae/z6BStv53PEHU/GO1vM6W9Wqd7YdmmXPMTuUbKD8d9vQ/L0J7pQLpPvtJwBHSYXU3D2P380xKo8ZQwLyuqII04Ph3+unhhrjUOO5YpzIyL1pLbghkhiFSJ4mO8jKiO49QfwQlNfdZoEZbw0UxIZ2NGwt/GYw2IszifmP0hsCgYEApwD4nkSNT96x4Diid/L3hcZm1WMeYcJPZxRE7R/dAz6j2bNEk5tGtlSpN2F+6xW6DtlSlT8NzzPzcqNo3X3sdEhxpbFtuJRk5bTPsagrbGCtchRXQj6ogSce11VQjKXYw3QZxJhsj2VYGZKmHT97TWD3gdyummBm+U6hSU4kJO8CgYAgKBq90q6zmZceJqg1x5H+vWiXYmgRiqGa6EOZUATgNYAvoHiht21+Wb6NT5Hl+9FH+PHt63x0rQP76ajgQfMBMuGaKtjifWZ3doA9I/8DaisALY4VP2duoplNJFB+WfhHMv5TEvW7z3CY5gWS2spRU74SApYxsCQ/059NSjZDyQKBgHqFjB1siwNij97munIyNYv/glHcTPQeJ5+uiLaSS/3VFhOANUkFL9QqsuRhf/NDY0910boYixBGxMpzqWrNCC1+r5N1jYRDCt67GpOPJGHZorywTBLAOkUUIT+QFy76FBjWkDBUXcap/KrZfeYgKFcYwKDwEKryPEYTzhA1VXFJ";
    //支付宝网页快捷支付公钥
//    private $aliWapRsaPublicKey     = "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAt/N6cGIzM3KNTFhwdEBaj5fTYEUl3B79MCudt7U5TFXShN9uHXeP4PjBi4z42nGy74J+NDA5rD9Tt+PX5US/N+WZl/suQSsXt4SqESmTqaxzVgcJyRIGIatdUMyCF12nsykpKzA82YTrn5uCFCJ78XxYWpKwFZE24Sp/hzvU9gF6NyqqFz896R4bfjWxeLFcVYCGReEZgOsTGV2WYut2TVUiQ3YRTyFmuT7NMR1cQE6b+TusbTxwpFKr0ug06k/ie2EoS3j83D0dtkYOOqjPMfqwOls79JnhBH0umdJCUVQPIUnF14+6XI+tuU838WV+t2R9i78O+Vh6qHs7GTtHcQIDAQAB";
    private $aliWapRsaPublicKey     = "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAylumftffPln13NWGOlja2B7/5nuNjHs0fZNY90fqUIHmxvzgC0J04lRxEe14X1VaJbO+EAU24B0Swvaevwm5alLzi9SchHyqvvxALFKAU0LxgHpjABMcU3MV6IO2ZDOXA7VpBXnrqgOJbvzTF8kXjNfq4jhcjDGsYpfufrI7VzXOqoPsRv9PbS8XvrXClsTQ4lAWzRKNzVT1nvmiIPuszJNYuhKkF+b3av3tFHSLwK5ppPaTcncx2EvWBvkpIm0qUQxvLbHvoccIfdjoLXMHvMZj9mO0A4zYnh5jsFTiEJVjWcL3IcdezJx60OT0fCCiaO0v7STmo/uO7buofVPdGQIDAQAB";
    //支付宝网页快捷支付支付宝公钥
//    private $aliWapPayRsaPublicKey  = "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAqoB05nSQmflWrE3RNB4T4TTnphZrp5OjXQIs5/ALBC2qGfw4L64+EURh6e3Z0oQ4nc8+w24N7KIWkwZv3DB27xy2Tu+ZYnH+C80gi8ioCYP1uPEXekeMsElyrGxhDRwO6r+nUO6JoCWhHZClK8+KMGPxIVVrOXH2YFr7oDw/ozVduc5oApZzzsx2IRMKmf5WgZTPyDy6CsznyrCG8sVfBSHO3lbEkKtFIR6usUrghVfwlz6Xk3V+kkAiINzCzUIR5e10hXubwOhVsoGGYSg6Fb4cgqj7VVEN+d5NRzuScnlei6W1IXd7cJwzF01CLpVOdQoXAYeY5Gu9goDiqEA9dwIDAQAB";
    private $aliWapPayRsaPublicKey  = "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAhOmFzJvXH0BbhbbehxLTnGl1qqIpmDWDqUsldIuZvlKdaPpfaE3UJ8OsazJxW6o25K7VW3vXekMRGlnKcouxE7ChRt5c6YMnECLbMWy2gmMQcJYc40gw8VAMzYC5gLPWFJfCQ8R9mLrXEE8f6UrmmaG6zbdFm8SgB8JEdNXNqPJvRdXF2VuSEeMuFNCuJhxzKbnu3VkWNmfrh18pAZ2ZVupXOqMrG7+y2OkbCsCduyXMUON5+mER03M8m3131AVP7r8dYiyNhiCwuUHB8lFhfGsb1F+dKq3Zxfihi5YrEWzf2lmqHh5ynT7tWgIFVzpUWBxCxjCT8sWGnnGKU6P4dwIDAQAB";
    //支付宝网页快捷支付回调地址
    private $aliWapCallback         = "http://apisdk.chuangyunet.net/Api/Reply/AliWapQuickPay";

    /**
     * 苹果回调接口模块
     * @param $receiptData  苹果凭证
     * @param $transactionId  交易号
     * @param $gameId  游戏ID
     * @param $userCode  用户唯一标识符
     * @param $goodsCode  商品ID
     * @param $amount  金额
     * @param $bundleId  包名
     * @return array
     */
    public function appleStorePay($receiptData, $transactionId, $gameId, $userCode, $goodsCode, $amount, $bundleId)
    {
        //判断必要数据是否存在
        if (!$receiptData || !$transactionId || !$userCode || !$gameId || !$goodsCode || !$amount || !$bundleId) {
            $return = array(
                "result"    => false,
                "msg"       => "数据异常！",
                "data"      => array()
            );
            return $return;
        }

        //初始化苹果类
        $cla    = new \Vendor\AppleStore\AppStore();
        $res    = $cla->PayNotify($receiptData, $transactionId, $goodsCode, $bundleId);
        return $res;
    }

    /**
     * 支付宝网页快捷支付
     * @param $orderId
     * @param $amount
     * @param $subject
     * @return string|\提交表单HTML文本
     */
    public function aliWapQuick($orderId, $amount, $subject)
    {
        Vendor("Ali.AopClient");
        Vendor("Ali.request.AlipayTradeWapPayRequest");
        $aop                        = new \AopClient();
        $aop->gatewayUrl            = "https://openapi.alipay.com/gateway.do";
        $aop->appId                 = $this->aliWapAppId;
        $aop->rsaPrivateKey         = $this->aliWapRsaPrivateKey;
        $aop->alipayrsaPublicKey    = $this->aliWapPayRsaPublicKey;
        $aop->apiVersion            = "1.0";
        $aop->postCharset           = "UTF-8";
        $aop->format                = "json";
        $aop->signType              = "RSA2";
        $request                    = new \AlipayTradeWapPayRequest();
        $request->setNotifyUrl($this->aliWapCallback);
        $request->setBizContent("{" .
            "    \"body\":\"".urlencode($subject)."\"," .
            "    \"subject\":\"".urlencode($subject)."\"," .
            "    \"out_trade_no\":\"{$orderId}\"," .
            "    \"timeout_express\":\"60m\"," .
            "    \"total_amount\":{$amount}," .
            "    \"product_code\":\"QUICK_WAP_WAY\"" .
            "  }");
        $result                     = $aop->pageExecute($request, "GET");
        return $result;
    }

    /**
     * 支付宝支付回调
     * @param $info
     * @return bool
     */
    public function aliWapQuickCallback($info)
    {
        Vendor("Ali.AopClient");
        $aop = new \AopClient;
        $aop->alipayrsaPublicKey    = $this->aliWapPayRsaPublicKey;
        //此处验签方式必须与下单时的签名方式一致
        return $aop->rsaCheckV1($info, NULL, "RSA2");
    }

    /**
     * 威富通支付宝扫码下单接口
     * @param $orderId
     * @param $subject
     * @param $amount
     * @return array
     */
    public function swiftPassAlipay($orderId, $subject, $amount)
    {
        //初始化类
        $cla    = new \Vendor\SwiftPass\Request();

        //需要传递的参数
        $info   = array(
            "out_trade_no"  => $orderId,
            "body"          => $subject,
            "total_fee"     => $amount * 100,
            "service"       => "pay.alipay.native",
            "notify_url"    => C("COMPANY_DOMAIN")."Api/Reply/SwiftPass",
            "mch_create_ip" => get_ip_address()
        );

        //提交数据
        return $cla->submitOrderInfo($info);
    }

    /**
     * 威富通微信下单接口
     * @param $orderId
     * @param $subject
     * @param $amount
     * @return array
     */
    public function swiftPassWeixin($orderId, $subject, $amount)
    {
        //初始化类
        $cla    = new \Vendor\SwiftPass\Request();

        //需要传递的参数
        $info   = array(
            "out_trade_no"  => $orderId,
            "body"          => $subject,
            "total_fee"     => $amount * 100,
            "service"       => "pay.weixin.raw.app",
            "notify_url"    => C("COMPANY_DOMAIN")."Api/Reply/SwiftPass",
            "mch_create_ip" => get_ip_address()
        );

        //提交数据
        return $cla->submitOrderInfo($info, 2);
    }

    /**
     * 国连四方网络H5收银台
     * @param $orderId
     * @param $amount
     * @return bool|string
     */
    public function bbnpayH5($orderId, $amount)
    {
        //初始化类
        $cla    = new \Vendor\bbnpay\bbnpay();

        //提交数据
        return $cla->submitOrder($orderId, $amount);
    }

    /**
     * 微信H5收银台
     * @param $orderId
     * @param $amount
     * @param $subject
     * @return bool
     */
    public function iuucWeixinH5($orderId, $amount, $subject)
    {
        //初始化类
        $cla    = new \Vendor\iuuc\iuuc();

        //提交数据
        return $cla->submitWeixinH5Order($orderId, $amount, $subject);
    }

    /**
     * 微信H5公众号预下单
     * @param $orderId
     * @param $amount
     * @param $subject
     * @return bool
     */
    public function tnbpayWeixinH5($orderId, $amount, $subject)
    {
        //初始化类
        $cla    = new \Vendor\tnbpay\tnbpay();

        //提交数据
        return $cla->submitOrder($orderId, $amount, $subject);
    }

    /**
     * 原生微信H5支付下单
     * @param $orderId
     * @param $amount
     * @param $subject
     * @return bool
     */
    public function weixinH5($orderId, $amount, $subject)
    {
        $cla    = new \Vendor\WeixinH5\WeixinH5();

        //提交数据
        return $cla->submitOrder($orderId, $amount, $subject, get_ip_address());
    }

    /**
     * 获取威富通支付的回调信息
     * @return bool
     */
    public function getSwiftPassCallback()
    {
        //初始化类
        $cla    = new \Vendor\SwiftPass\Request();
        return $cla->callback();
    }

    /**
     * 回调验证
     * @param $info
     * @return mixed
     */
    public function bbnpayH5Callback($info)
    {
        //初始化类
        $cla    = new \Vendor\bbnpay\bbnpay();
        return $cla->callback($info);
    }

    /**
     * 微信H5回调验证
     * @param $info
     * @return bool|string
     */
    public function iuucH5Callback($info)
    {
        //初始化类
        $cla    = new \Vendor\iuuc\iuuc();

        //提交数据
        return $cla->callback($info);
    }

    /**
     * tnb微信公众号回调验证
     * @param $xml
     * @return mixed
     */
    public function tnbH5WeixinCallback($xml)
    {
        //初始化类
        $cla    = new \Vendor\tnbpay\tnbpay();

        //提交数据
        return $cla->callback($xml);
    }

    /**
     * tnb微信公众号回调信息
     * @param $res
     * @return array
     */
    public function tnbH5WeixinCallbackMsg($res)
    {
        //初始化类
        $cla    = new \Vendor\tnbpay\tnbpay();

        //回调信息
        $cla->callbackMsg($res);
    }

    /**
     * 获取原生微信H5返回的数据
     * @return array
     */
    public function weixinH5Callback()
    {
        //初始化类
        $cla    = new \Vendor\WeixinH5\WeixinH5();

        //获取回调信息
        return $cla->callback();
    }

    /**
     * 原生微信H5返回信息
     * @param $res
     */
    public function weixinH5Msg($res)
    {
        //初始化类
        $cla    = new \Vendor\WeixinH5\WeixinH5();

        //获取回调信息
        $cla->callbackMsg($res);
    }
}