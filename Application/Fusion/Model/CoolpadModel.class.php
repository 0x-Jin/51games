<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/11/23
 * Time: 10:09
 *
 * 酷派
 */

namespace Fusion\Model;

class CoolpadModel extends SdkModel
{

    private $channel_id     = "";                                                                           //渠道ID
    private $token_url      = "";                                                                           //获取token地址
    private $login_url      = "";                                                                           //获取用户信息地址
    private $callback_url   = "";                                                                           //充值回调地址

    public function __construct()
    {
        parent::__construct();
        //渠道ID
        $this->channel_id       = $this->getChannelId();
        //获取token地址
        $this->token_url        = "https://openapi.coolyun.com/oauth2/token";
        //验证地址
        $this->login_url        = "https://openapi.coolyun.com/oauth2/api/get_user_info";
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
            "AppID"     => $key["AppID"],
            "PayKey"    => $key["PayKey"]
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

        //拼接参数
        $param  = array(
            "grant_type"    => "authorization_code",
            "client_id"     => $key["AppID"],
            "client_secret" => $key["AppKey"],
            "code"          => $data["token"],
            "redirect_uri"  => $key["AppKey"]
        );

        //获取token
        $result = curl_get($this->token_url."?".http_build_query($param));
        $Result = json_decode($result, true);
        if (!$Result["access_token"] || !$Result["openid"]) {
            $res = array(
                "Result"    => false,
                "Data"      => array()
            );
            return $res;
        }

        //拼接参数
        $Param  = array(
            "access_token"          => $Result["access_token"],
            "oauth_consumer_key"    => $key["AppID"],
            "openid"                => $Result["openid"]
        );

        //获取用户信息
        $res    = curl_get($this->login_url."?".http_build_query($Param));
        $Res    = json_decode($res, true);
        if ($Res["rtn_code"] != "0") {
            $res = array(
                "Result"    => false,
                "Data"      => array()
            );
        } else {
            //用户数据
            $res = array(
                "Result"    => true,
                "Data"      => array(
                    "channelUserCode"   => $Result["openid"],
                    "channelUserName"   => $Res["nickname"],
                    "extInfo"           => array(
                        "accessToken"   => $Result["access_token"],
                        "openId"        => $Result["openid"]
                    ),
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
        if (!$data["agent"] || !$data["goodsCode"]) return false;

        //获取商品ID
        $goods = $this->getFusionGoods($data["agent"], $data["goodsCode"]);
        return array("point" => $goods);
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
        $trans  = json_decode($data["transdata"], true);

        //根据订单号获取渠道号，从而获取开发者的配置参数
        $order  = D("Api/Order")->getOrderById($trans["exorderno"]);
        if (!$order) return false;

        //获取渠道参数
        $key    = $this->getKey($order["agent"]);

        //进行签名验证
        require_once(LIB_PATH."/Org/Coolpad/CoolpayDecrypt.php");
        $obj    = new \CoolpayDecrypt();
        $Res    = $obj->validsign($data["transdata"], $data["sign"], $key["PayKey"]);

        //验证失败
        if($Res != 0) return false;

        //组装数据
        $res = array(
            "status"    => $trans["result"] == "0"? "success": "fail",                      //success:充值成功的订单，fail及其他:充值失败的订单
            "orderId"   => $trans["exorderno"],                                             //我们的订单号
            "tranId"    => $trans["transid"],                                               //渠道订单号
            "amount"    => $trans["money"]/100                                              //订单金额，单位元
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
     * 失败返回借口
     * @param 错误类型 $num
     * @param array $data 返回数据
     */
    public function callbackErr($num, $data = array())
    {
        echo "FAILURE";
        exit();
    }
}