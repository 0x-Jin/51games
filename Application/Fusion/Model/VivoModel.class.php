<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/11/9
 * Time: 15:09
 *
 * VIVO
 */

namespace Fusion\Model;

class VivoModel extends SdkModel
{

    private $channel_id     = "";                                                                           //渠道ID
    private $login_url      = "";                                                                           //二登验证地址
    private $order_url      = "";                                                                           //下单地址
    private $callback_url   = "";                                                                           //充值回调地址

    public function __construct(){
        parent::__construct();
        //渠道ID
        $this->channel_id       = $this->getChannelId();
        //验证地址
        $this->login_url        = "https://usrsys.vivo.com.cn/sdk/user/auth.do";
        //下单地址
        $this->order_url        = "https://pay.vivo.com.cn/vcoin/trade";
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

        // 发起请求
        $result = curl_post($this->login_url, http_build_query(array("authtoken" => $data["token"])));
        $Res    = json_decode($result, true);

        //验证失败
        if ($Res["retcode"] != "0" || $Res["data"]["success"] != true || $Res["data"]["openid"] != $data["uid"]) {
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

        //商品信息
        $goods  = D("Api/Goods")->getGoods($data["goodsCode"]);

        //组装数据
        $info   = array(
            "version"       => "1.0.0",
            "cpId"          => $key["CpId"],
            "appId"         => $key["AppId"],
            "cpOrderNumber" => $orderId,
            "notifyUrl"     => $this->callback_url,
            "orderTime"     => date("YmdHis"),
            "orderAmount"   => $goods["amount"]*100,
            "orderTitle"    => $goods["name"],
            "orderDesc"     => $goods["name"],
            "extInfo"       => $orderId,
        );

        ksort($info);
        $str    = "";
        foreach ($info as $k => $v) {
            $str .= $k."=".$v."&";
        }
        $info["signMethod"]    = "MD5";
        $info["signature"]     = md5($str.md5($key["AppKey"]));

        //传递订单信息
        $result = curl_post($this->order_url, http_build_query($info));
        $Res    = json_decode($result, true);

        if ($Res["respCode"] == 200) {
            ksort($Res);
            $sign_str   = "";
            foreach ($Res as $k2 => $v2) {
                if ($k2 != "signMethod" && $k2 != "signature" && $v2 != "") $sign_str .= $k2."=".$v2."&";
            }
            if ($Res["signature"] == md5($sign_str.md5($key["AppKey"]))) {
                $info["accessKey"]      = $Res["accessKey"];
                $info["orderNumber"]    = $Res["orderNumber"];
                return $info;
            }
        }
        return array();
    }

    /**
     * 获取数据接口
     * @return mixed
     */
    public function getInput()
    {
        $data = $_REQUEST;
        unset($data["CyChannelId"], $data["CyChannelVer"]);
        return $data;
    }

    /**
     * 回调验证接口
     * @param $data
     * @return array|bool
     */
    public function callbackCheck($data)
    {
        //根据订单号获取渠道号，从而获取开发者的配置参数
        $order  = D("Api/Order")->getOrderById($data["cpOrderNumber"]);
        if (!$order) return false;

        //获取渠道参数
        $key    = $this->getKey($order["agent"]);

        //进行签名验证
        $str    = "";
        ksort($data);
        foreach ($data as $k => $v) {
            if ($k != "signMethod" && $k != "signature" && $v != "") $str .= $k."=".$v."&";
        }

        //验证失败
        if($data["signature"] != strtolower(md5($str.md5($key["AppKey"])))) return false;

        //组装数据
        $res = array(
            "status"    => $data["respCode"] == "200"? "success": "fail",                   //success:充值成功的订单，fail及其他:充值失败的订单
            "orderId"   => $data["cpOrderNumber"],                                          //我们的订单号
            "tranId"    => $data["orderNumber"],                                            //渠道订单号
            "amount"    => $data["orderAmount"]/100                                         //订单金额，单位元
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
        echo "success";
        exit();
    }

    /**
     * 失败返回借口
     * @param 错误类型 $num
     * @param array $data 返回数据
     */
    public function callbackErr($num, $data = array())
    {
        echo "fail";
        exit();
    }
}