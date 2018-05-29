<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 2018/3/30
 * Time: 10:20
 *
 * 畅酷
 */

namespace Fusion\Model;

class CkModel extends SdkModel
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
        $this->login_url        = "http://api.changkunet.com/sdkapi.php";
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
            "LoginKey"  => $key["LoginKey"]
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
        if (!$data["agent"] || !$data["token"] || !$data["ver"]) {
            $res = array(
                "Result"    => false,
                "Data"      => array()
            );
            return $res;
        }

        //获取渠道参数
        $key    = $this->getKey($data["agent"]);

        //请求参数
        $param  = array(
            "ac"            => "check",
            "appid"         => $key["AppId"],
            "sdkversion"    => $data["ver"],
            "sessionid"     => str_replace(" ", "+", urldecode($data["token"])),
            "time"          => time()
        );
        $param["sign"]  = md5("ac=check&appid=".$param["appid"]."&sdkversion=".$param["sdkversion"]."&sessionid=".$param["sessionid"]."&time=".$param["time"].$key["LoginKey"]);

        // 发起请求
        $result = curl_post($this->login_url, http_build_query($param));
        $Result = json_decode($result, true);

        //验证失败
        if (!$result || $Result["code"] != "1") {
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
                    "channelUserCode"   => $Result["userInfo"]["uid"]? $Result["userInfo"]["uid"]: $data["uid"],
                    "channelUserName"   => $Result["userInfo"]["username"]? $Result["userInfo"]["username"]: ($data["userName"]? $data["userName"]: ($Result["userInfo"]["uid"]? $Result["userInfo"]["uid"]: $data["uid"]))
                )
            );
            return $res;
        }
    }

    /**
     * 默认的获取数据方法
     * @return mixed
     */
    public function getInput()
    {
        $data               = $_REQUEST;
        $data["sandbox"]    = $_SERVER["HTTP_X_ORDER_SANDBOX"];
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
        //根据订单号获取渠道号，从而获取开发者的配置参数
        $order  = D("Api/Order")->getOrderById($data["cporderid"]);
        if (!$order) return false;

        //获取渠道参数
        $key    = $this->getKey($order["agent"]);

        //进行签名验证
        $str    = "amount=".$data["amount"]."&appid=".$data["appid"]."&charid=".$data["charid"]."&cporderid=".$data["cporderid"]."&extinfo=".urlencode($data["extinfo"])."&gold=".$data["gold"]."&orderid=".$data["orderid"]."&serverid=".$data["serverid"]."&time=".$data["time"]."&uid=".$data["uid"].$key["PayKey"];
        $str2   = "amount=".$data["amount"]."&appid=".$data["appid"]."&charid=".$data["charid"]."&cporderid=".$data["cporderid"]."&extinfo=".$data["extinfo"]."&gold=".$data["gold"]."&orderid=".$data["orderid"]."&serverid=".$data["serverid"]."&time=".$data["time"]."&uid=".$data["uid"].$key["PayKey"];

        //验证失败
        if($data["sign"] != md5($str) && $data["sign"] != md5($str2)) return false;

        //组装数据
        $res = array(
            "status"    => "success",                                                       //success:充值成功的订单，fail及其他:充值失败的订单
            "orderId"   => $data["cporderid"],                                              //我们的订单号
            "tranId"    => $data["orderid"],                                                //渠道订单号
            "amount"    => $data["amount"],                                                 //订单金额，单位元
            "sandbox"   => $data["sandbox"] == "1"? "1": "0"                                //0：正式环境，1：沙箱环境
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
        echo "ERROR";
        exit();
    }
}