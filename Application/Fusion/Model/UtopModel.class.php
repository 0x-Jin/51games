<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 2018/5/10
 * Time: 11:42
 *
 * 游淘
 */

namespace Fusion\Model;

class UtopModel extends SdkModel
{
    private $channel_id     = "";                                                                           //渠道ID
    private $login_url      = "";                                                                           //获取用户信息地址
    private $callback_url   = "";                                                                           //充值回调地址

    public function __construct()
    {
        parent::__construct();
        //渠道ID
        $this->channel_id       = $this->getChannelId();
        //验证地址
        $this->login_url        = "http://sdk.17utop.com/OAuth1/User/GetInfo";
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
            "ClientAppId"       => $key["ClientAppId"],
            "ClientAppSecret"   => $key["ClientAppSecret"],
            "Source"            => $key["Source"]
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

        //拼接参数
        $param  = array(
            "appid"         => $key["ServerAppId"],
            "appvers"       => $data["appVer"],
            "device"        => $data["device"],
            "deviceuuid"    => $data["uuid"],
            "fun"           => "GetInfo",
            "os"            => $data["os"],
            "osvers"        => $data["osVer"],
            "time"          => time(),
            "token"         => $data["token"],
        );
        $param["sign"] = md5($key["ServerAppId"].$data["appVer"].$data["device"].$data["uuid"]."GetInfo".$data["os"].$data["osVer"].$param["time"].$data["token"].$key["ServerAppSecret"]);

        // 发起请求
        $result = curl_post($this->login_url, http_build_query($param));
        $Result = json_decode($result, true);

        //验证失败
        if (!$result || $Result["state"] != "0") {
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
                    "channelUserCode"   => $Result["data"]["account"],
                    "channelUserName"   => $Result["data"]["passport"]? $Result["data"]["passport"]: ($data["userName"]? $data["userName"]: $Result["data"]["account"])
                )
            );
            return $res;
        }
    }

    /**
     * 回调验证接口
     * @param $data
     * @return array|bool
     */
    public function callbackCheck($data)
    {
        //根据订单号获取渠道号，从而获取开发者的配置参数
        $order  = D("Api/Order")->getOrderById($data["orderid"]);
        if (!$order) return false;

        //获取渠道参数
        $key    = $this->getKey($order["agent"]);

        //进行签名验证
        $str    = $data["account"].$data["amount"].$data["area"].$data["ext1"].$data["ext2"].$data["lzw_orderid"].$data["orderid"].$data["productdesc"].$data["state"].$data["time"].$data["totalfee"].$key["ServerAppSecret"];

        //验证失败
        if($data["sign"] != md5($str)) return false;

        //组装数据
        $res = array(
            "status"    => $data["state"] == "success"? "success": "fail",                  //success:充值成功的订单，fail及其他:充值失败的订单
            "orderId"   => $data["orderid"],                                                //我们的订单号
            "tranId"    => $data["lzw_orderid"],                                            //渠道订单号
            "amount"    => $data["totalfee"]                                                //订单金额，单位元
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
        echo "fail";
        exit();
    }
}