<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 2018/5/10
 * Time: 10:16
 *
 * 草花
 */

namespace Fusion\Model;

class CaohuaModel extends SdkModel
{
    private $channel_id     = "";                                                                           //渠道ID
    private $and_login_url  = "";                                                                           //获取用户信息地址
    private $ios_login_url  = "";                                                                           //获取用户信息地址
    private $callback_url   = "";                                                                           //充值回调地址

    public function __construct()
    {
        parent::__construct();
        //渠道ID
        $this->channel_id       = $this->getChannelId();
        //安卓验证地址
        $this->and_login_url    = "http://passport.sdk.caohua.com/api/verifyToken";
        //IOS验证地址
        $this->ios_login_url    = "http://passport.ios.caohua.com/api/verifyToken";
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
        if (!$data["agent"] || !$data["token"]) {
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
            "appid"     => $key["AppId"],
            "userid"    => $data["uid"],
            "times"     => time(),
            "token"     => str_replace(" ", "+", $data["token"])
        );
        ksort($param);
        $str    = "";
        foreach ($param as $k => $v) {
            $str .= $k."=".$v."&";
        }
        $param["sign"]  = strtoupper(md5(trim($str, "&").$key["AppKey"]));

        // 发起请求
        $result = curl_post(($data["type"] == 2? $this->ios_login_url: $this->and_login_url), http_build_query($param));
        $Result = json_decode($result, true);

        //验证失败
        if (!$result || $Result["code"] != "200") {
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
        if (!$data["gid"]) return false;

        //获取游戏信息
        $game   = D("Api/Game")->getGame($data["gid"]);

        //获取订单信息
        $order  = D("Api/Order")->getOrderById($orderId);

        return array("gameMoney" => intval($order["amount"]*$game["ratio"]));
    }

    /**
     * 回调验证接口
     * @param $data
     * @return array|bool
     */
    public function callbackCheck($data)
    {
        //根据订单号获取渠道号，从而获取开发者的配置参数
        $order  = D("Api/Order")->getOrderById($data["orderno_cp"]);
        if (!$order) return false;

        //获取渠道参数
        $key    = $this->getKey($order["agent"]);

        //进行签名验证
        $str    = "extra=".$data["extra"]."&order_amt=".$data["order_amt"]."&orderno=".$data["orderno"]."&orderno_cp=".$data["orderno_cp"]."&pay_amt=".$data["pay_amt"]."&pay_time=".$data["pay_time"]."&userid=".$data["userid"].$key["ServerKey"];

        //验证失败
        if($data["sign"] != strtoupper(md5($str))) return false;

        //组装数据
        $res = array(
            "status"    => "success",                                                       //success:充值成功的订单，fail及其他:充值失败的订单
            "orderId"   => $data["orderno_cp"],                                             //我们的订单号
            "tranId"    => $data["orderno"],                                                //渠道订单号
            "amount"    => $data["pay_amt"]/100                                             //订单金额，单位元
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
        $res = array(
            "code"  => 200,
            "msg"   => "成功",
            "data"  => array()
        );
        echo json_encode($res);
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
        switch ($num) {
            case 1:
                $res = array(
                    "code"  => 202,
                    "msg"   => "签名校验失败",
                    "data"  => array()
                );
                break;
            case 2:
            case 3:
            case 4:
            case 5:
                $res = array(
                    "code"  => 201,
                    "msg"   => "参数错误",
                    "data"  => array()
                );
                break;
            default:
                $res = array(
                    "code"  => 203,
                    "msg"   => "其他错误",
                    "data"  => array()
                );
        }
        echo json_encode($res);
        exit();
    }
}