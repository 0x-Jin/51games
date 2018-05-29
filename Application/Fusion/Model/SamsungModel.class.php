<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 2018/3/20
 * Time: 10:01
 *
 * 三星
 */

namespace Fusion\Model;

class SamsungModel extends SdkModel
{
    private $channel_id     = "";                                                                           //渠道ID
    private $login_url      = "";                                                                           //渠道ID
    private $order_url      = "";                                                                           //渠道ID
    private $callback_url   = "";                                                                           //充值回调地址

    public function __construct()
    {
        parent::__construct();
        //渠道ID
        $this->channel_id       = $this->getChannelId();
        //验证地址
        $this->login_url        = "https://siapcn1.ipengtai.com/api/oauth/get_token_info";
        //下单地址
        $this->order_url        = "http://siapcn1.ipengtai.com:7002/payapi/order";
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
            "AppId"             => $key["AppId"],
            "ClientId"          => $key["ClientId"],
            "ClientSecret"      => $key["ClientSecret"],
            "AppPrivateKey"     => $key["AppPrivateKey"]
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
        if (!$data["agent"] || !$data["token"]) {
            $res = array(
                "Result"    => false,
                "Data"      => array()
            );
            return $res;
        }

        //获取渠道参数
        $key    = $this->getKey($data["agent"]);

        require_once (LIB_PATH."/Org/Samsung/base.php");

        //拼接参数
        $param  = array(
            "appid" => $key["AppId"],
            "token" => $data["token"]
        );
        ksort($param);
        //生成签名
        $param["sign"] = sign(http_build_query($param), formatPriKey($key["AppPrivateKey"]));

        //发送到爱贝服务后台
        $result = request_by_curl($this->login_url, http_build_query($param), "token");
        $Result = json_decode($result, true);

        //返回报文解析
        if ($Result["code"] != "0" || !verify($Result["data"], $Result["sign"], formatPubKey($key["PlatformPublicKey"]))) {
            $res = array(
                "Result"    => false,
                "Data"      => array()
            );
            return $res;
        } else {
            $info = json_decode($Result["data"], true);
            //用户数据
            $res = array(
                "Result"    => true,
                "Data"      => array(
                    "channelUserCode"   => $info["uid"],
                    "channelUserName"   => $info["loginname"]
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
        if (!$data["agent"] || !$data["goodsCode"]) return false;

        //获取用户信息
        $user   = D("Api/User")->getUserByCode($data["userCode"]);

        //获取商品参数
        $goods  = D("Api/Goods")->getGoods($data["goodsCode"]);

        //获取商品ID
        $goodId = $this->getFusionGoods($data["agent"], $data["goodsCode"]);

        //获取渠道参数
        $key    = $this->getKey($data["agent"]);

        //组装参数
        $info   = array(
            "appid"     => $key["AppId"],
            "waresid"   => (int)$goodId,
            "cporderid" => $orderId,
            "price"     => $goods["amount"],
            "currency"  => "RMB",
            "appuserid" => $user["channelUserCode"]."#".$data["serverId"],
            "notifyurl" => $this->callback_url
        );

        require_once (LIB_PATH."/Org/Samsung/base.php");

        //组装请求报文
        $str    = composeReq($info, $key["AppPrivateKey"]);

        //发送到爱贝服务后台
        $res    = request_by_curl($this->order_url, $str, "order test");

        //返回报文解析
        if (!parseResp($res, $key["PlatformPublicKey"], $Res)) {
            $id = "";
        } else {
            $id = $Res["transid"];
        }
        return array("transId" => $id);
    }

    /**
     * 回调验证接口
     * @param $data
     * @return array|bool
     */
    public function callbackCheck($data)
    {
        //解析数据
        $param  = json_decode($data["transdata"], true);

        //根据订单号获取渠道号，从而获取开发者的配置参数
        $order  = D("Api/Order")->getOrderById($param["cporderid"]);
        if (!$order) return false;

        //获取渠道参数
        $key    = $this->getKey($order["agent"]);

        require_once (LIB_PATH."/Org/Samsung/base.php");

        //验证失败
        if (!verify($data["transdata"], $data["sign"], formatPubKey($key["PlatformPublicKey"]))) return false;

        //组装数据
        $res = array(
            "status"    => $param["result"] == "0"? "success": "fail",                      //success:充值成功的订单，fail及其他:充值失败的订单
            "orderId"   => $param["cporderid"],                                             //我们的订单号
            "tranId"    => $param["transid"],                                               //渠道订单号
            "amount"    => $param["money"]                                                  //订单金额，单位元
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
        echo "SUCCESS";
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
        echo "FAILURE";
        exit();
    }
}