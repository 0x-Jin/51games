<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 2018/3/20
 * Time: 14:20
 *
 * 美图
 */

namespace Fusion\Model;

class MeituModel extends SdkModel
{
    private $channel_id     = "";                                                                           //渠道ID
    private $login_url      = "";                                                                           //渠道ID
    private $callback_url   = "";                                                                           //充值回调地址

    public function __construct()
    {
        parent::__construct();
        //渠道ID
        $this->channel_id       = $this->getChannelId();
        //验证地址
        $this->login_url        = "https://openapi.account.meitu.com/open/user/info.json";
        //充值回调地址
        $this->callback_url     = C("COMPANY_DOMAIN")."Api/Reply/ChannelCallback/CyChannelId/".$this->channel_id;
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
            "AppId"     => $key["AppId"],
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
        if (!$data["uid"] || !$data["agent"] || !$data["token"]) {
            $res = array(
                "Result"    => false,
                "Data"      => array()
            );
            return $res;
        }

        //获取渠道参数
        $key    = $this->getKey($data["agent"]);

        //拼接参数
        $param  = array(
            "client_id"     => $key["ClientId"],
            "access_token"  => $data["token"]
        );

        //发送请求
        $result = curl_get($this->login_url."?".http_build_query($param));
        $Result = json_decode($result, true);

        //验证失败
        if (!$result || $Result["meta"]["code"] != "0") {
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
                    "channelUserCode"   => $Result["response"]["user"]["id"]? $Result["response"]["user"]["id"]: $data["uid"],
                    "channelUserName"   => $Result["response"]["user"]["screen_name"]? $Result["response"]["user"]["screen_name"]: ($data["userName"]? $data["userName"]: $data["uid"])
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
        if (!$data["agent"] || !$data["goodsCode"] || !$data["token"]) return false;

        //获取商品参数
        $goods  = D("Api/Goods")->getGoods($data["goodsCode"]);

        //获取渠道参数
        $key    = $this->getKey($data["agent"]);

        //组装参数
        $info   = array(
            "app_id"            => $key["AppId"],
            "format"            => "JSON",
            "version"           => "1.0",
            "sign_type"         => "HMAC-SHA1",
            "timestamp"         => date("Y-m-d H:i:s"),
            "access_token"      => $data["token"],
            "cp_order_id"       => $orderId,
            "product_name"      => $goods["name"],
            "product_describe"  => $goods["name"],
            "pay_fee"           => $goods["amount"] * 100,
            "notify_url"        => $this->callback_url
        );

        require_once(LIB_PATH."/Org/Meitu/base.php");

        //生成签名
        $info["sign"] = hmacSha1Sign($info, $key["AppSecret"]);

        return $info;
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

        require_once(LIB_PATH."/Org/Meitu/base.php");

        //验证失败
        if ($data["sign"] != hmacSha1Sign($data, $key["AppSecret"])) return false;

        //组装数据
        $res = array(
            "status"    => in_array($data["order_status"], array("TRADE_SUCCESS", "TRADE_FINISHED"))? "success": "fail",    //success:充值成功的订单，fail及其他:充值失败的订单
            "orderId"   => $data["cp_order_id"],                                            //我们的订单号
            "tranId"    => $data["notify_id"],                                              //渠道订单号
            "amount"    => $data["pay_fee"]/100,                                            //订单金额，单位元
            "userCode"  => $data["uid"]                                                     //用户账号
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
     * 失败返回接口
     * @param 错误类型 $num
     * @param array $data 返回数据
     */
    public function callbackErr($num, $data = array())
    {
        //返回失败
        echo "failure";
        exit();
    }
}