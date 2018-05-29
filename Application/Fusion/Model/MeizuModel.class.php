<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/11/22
 * Time: 11:00
 *
 * 魅族
 */

namespace Fusion\Model;

class MeizuModel extends SdkModel
{

    private $channel_id     = "";                                                                           //渠道ID
    private $login_url      = "";                                                                           //二登验证地址
    private $callback_url   = "";                                                                           //充值回调地址

    public function __construct(){
        parent::__construct();
        //渠道ID
        $this->channel_id   = $this->getChannelId();
        //验证地址
        $this->login_url    = "https://api.game.meizu.com/game/security/checksession";
        //充值回调地址
        $this->callback_url = C("COMPANY_DOMAIN")."Api/Reply/ChannelCallback/CyChannelId/".$this->channel_id;
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
            "AppID"     => $key["AppID"],
            "AppKey"    => $key["AppKey"]
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

        $info   = array(
            "app_id"        => $key["AppID"],
            "session_id"    => $data["token"],
            "uid"           => $data["uid"],
            "ts"            => time()*100,
            "sign_type"     => "md5"
        );
        $info["sign"]   = md5("app_id=".$info["app_id"]."&session_id=".$info["session_id"]."&ts=".$info["ts"]."&uid=".$info["uid"].":".$key["AppSecret"]);

        //发起请求
        $result = curl_post($this->login_url, http_build_query($info));
        $Res    = json_decode($result, true);

        //验证失败
        if ($Res["code"] != "200") {
            $res = array(
                "Result"    => false,
                "Data"      => array()
            );
        } else {
            //用户数据
            $res = array(
                "Result"    => true,
                "Data"      => array(
                    "channelUserCode"   => $data["uid"],
                    "channelUserName"   => $data["userName"]? $data["userName"]: $data["uid"]
                )
            );
        }
        return $res;
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

        //获取用户信息
        $user   = D("Api/User")->getUserByCode($data["userCode"]);

        //组装数据
        $info   = array(
            "app_id"            => $key["AppID"],
            "cp_order_id"       => $orderId,
            "uid"               => $user["channelUserCode"],
            "product_id"        => $data["goodsCode"],
            "product_subject"   => $goods["name"],
            "product_body"      => $goods["name"],
            "product_unit"      => "",
            "buy_amount"        => 1,
            "product_per_price" => $goods["amount"],
            "total_price"       => $goods["amount"],
            "create_time"       => time(),
            "pay_type"          => 0,
            "user_info"         => $orderId,
            "sign_type"         => "md5",
        );
        $info["sign"]   = md5("app_id=".$info["app_id"]."&buy_amount=".$info["buy_amount"]."&cp_order_id=".$info["cp_order_id"]."&create_time=".$info["create_time"]."&pay_type=".$info["pay_type"]."&product_body=".$info["product_body"]."&product_id=".$info["product_id"]."&product_per_price=".$info["product_per_price"]."&product_subject=".$info["product_subject"]."&product_unit=".$info["product_unit"]."&total_price=".$info["total_price"]."&uid=".$info["uid"]."&user_info=".$info["user_info"].":".$key["AppSecret"]);

        return $info;
    }

    /**
     * 获取数据接口
     * @return mixed
     */
    public function getInput()
    {
        $data   = $_REQUEST;
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
        $order  = D("Api/Order")->getOrderById($data["cp_order_id"]);
        if (!$order) return false;

        //获取渠道参数
        $key    = $this->getKey($order["agent"]);

        //进行签名验证
        $str    = "app_id=".$data["app_id"]."&buy_amount=".$data["buy_amount"]."&cp_order_id=".$data["cp_order_id"]."&create_time=".$data["create_time"]."&notify_id=".$data["notify_id"]."&notify_time=".$data["notify_time"]."&order_id=".$data["order_id"]."&partner_id=".$data["partner_id"]."&pay_time=".$data["pay_time"]."&pay_type=".$data["pay_type"]."&product_id=".$data["product_id"]."&product_per_price=".$data["product_per_price"]."&product_unit=".$data["product_unit"]."&total_price=".$data["total_price"]."&trade_status=".$data["trade_status"]."&uid=".$data["uid"]."&user_info=".$data["user_info"].":".$key["AppSecret"];

        //验证失败
        if ($data["sign"] != md5($str)) return false;

        //组装数据
        $res = array(
            "status"    => $data["trade_status"] == "3"? "success": "fail",                 //success:充值成功的订单，fail及其他:充值失败的订单
            "orderId"   => $data["cp_order_id"],                                            //我们的订单号
            "tranId"    => $data["order_id"],                                               //渠道订单号
            "amount"    => $data["total_price"]                                             //订单金额，单位元
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
        $Result["code"]     = 200;
        $Result["message"]  = "成功";
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
                $Result["code"]     = 120013;
                $Result["message"]  = "验证失败";
                break;
            case 2:
                $Result["code"]     = 120013;
                $Result["message"]  = "订单不存在";
                break;
            case 3:
                $Result["code"]     = 120013;
                $Result["message"]  = "订单金额不一致";
                break;
            case 4:
                $Result["code"]     = 120014;
                $Result["message"]  = "订单记录错误";
                break;
            case 5:
                $Result["code"]     = 120013;
                $Result["message"]  = "订单用户不一致";
                break;
            default:
                $Result["code"]     = 900000;
                $Result["message"]  = "其他错误";
        }
        echo json_encode($Result);
        exit();
    }
}