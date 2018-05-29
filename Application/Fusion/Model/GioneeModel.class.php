<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/11/14
 * Time: 10:33
 *
 * 金立
 */

namespace Fusion\Model;

class GioneeModel extends SdkModel
{

    private $channel_id     = "";                                                                           //渠道ID
    private $callback_url   = "";                                                                           //充值回调地址

    public function __construct(){
        parent::__construct();
        //渠道ID
        $this->channel_id   = $this->getChannelId();
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
            "APIKey"    => $key["APIKey"]
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

        require_once(LIB_PATH."/Org/Gionee/gioneeLogin.php");
        $obj    = new \gioneeLogin($key["APIKey"], $key["SecretKey"]);
        //登陆验证
        $Res    = $obj->LoginCheck($data["token"]);

        if ($Res) {
            //用户数据
            $res = array(
                "Result"    => true,
                "Data"      => array(
                    "channelUserCode"   => $data["uid"],
                    "channelUserName"   => $data["userName"]? $data["userName"]: $data["uid"]
                )
            );
        } else {
            $res = array(
                "Result"    => false,
                "Data"      => array()
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

        //组装需要签名的数据
        $info   = array(
            "user_id"       => $data["user_id"],
            "out_order_no"  => $orderId,
            "subject"       => $goods["name"],
            "submit_time"   => date("YmdHis"),
            "total_fee"     => $goods["amount"],
            "api_key"       => $key["APIKey"],
            "deal_price"    => $goods["amount"],
            "deliver_type"  => "1"
        );

        require_once(LIB_PATH."/Org/Gionee/gioneeOrder.php");
        $obj    = new \gioneeOrder($key["PublicKey"], $key["PrivateKey"]);
        //创建订单
        $Res    = $obj->createOrder($info);

        //创建订单失败
        if (!$Res) return array();

        D("Api/Order")->saveOrder(array("tranId" => $Res["order_no"]), $orderId);

        return $Res["info"];
    }

    /**
     * 获取数据接口
     * @return mixed
     */
    public function getInput()
    {
        $data   = $_POST;
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
        $order  = D("Api/Order")->getOrderById($data["out_order_no"]);
        if (!$order) return false;

        //获取渠道配置
        $key    = $this->getKey($order["agent"]);

        require_once(LIB_PATH."/Org/Gionee/gioneeOrder.php");
        $obj    = new \gioneeOrder($key["PublicKey"], $key["PrivateKey"]);
        //签名验证
        $Res    = $obj->rsa_verify($data);

        //验证失败
        if (!$Res) return false;

        //组装数据
        $res = array(
            "status"    => "success",                                                       //success:充值成功的订单，fail及其他:充值失败的订单
            "orderId"   => $data["out_order_no"],                                           //我们的订单号
            "tranId"    => $order["tranId"],                                                //渠道订单号
            "amount"    => $data["deal_price"]                                              //订单金额，单位元
        );

        return $res;
    }

    /**
     * 成功返回接口
     * @param array $data 返回数据
     */
    public function callbackSuc($data = array())
    {
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