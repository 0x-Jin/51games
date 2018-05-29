<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 2018/2/24
 * Time: 15:05
 *
 * 天宇游
 */

namespace Fusion\Model;

class TianyuyouModel extends SdkModel
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
        $this->login_url        = "http://api.tianyuyou.com/notice/gamecp/checkusertoken.php";
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
            "ClienId"   => $key["ClienId"],
            "ClienKey"  => $key["ClienKey"]
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
            "app_id"        => $key["AppId"],
            "mem_id"        => $data["uid"],
            "user_token"    => $data["token"],
            "sign"          => md5("app_id=".$key["AppId"]."&mem_id=".$data["uid"]."&user_token=".$data["token"]."&app_key=".$key["AppKey"])
        );

        // 发起请求
        $result = curl_post($this->login_url, json_encode($param));
        $Result = json_decode($result, true);

        //验证失败
        if (!$result || $Result["status"] != "1") {
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
     * 默认的获取数据方法
     * @return mixed
     */
    public function getInput()
    {
        $input = $GLOBALS["HTTP_RAW_POST_DATA"];
        return json_decode($input, true);
    }

    /**
     * 回调验证接口
     * @param $data
     * @return array|bool
     */
    public function callbackCheck($data)
    {
        //根据订单号获取渠道号，从而获取开发者的配置参数
        $order  = D("Api/Order")->getOrderById($data["attach"]);
        if (!$order) return false;

        //获取渠道参数
        $key    = $this->getKey($order["agent"]);

        //进行签名验证
        $str    = "order_id=".$data["order_id"]."&mem_id=".$data["mem_id"]."&app_id=".$data["app_id"]."&money=".$data["money"]."&order_status=".$data["order_status"]."&paytime=".$data["paytime"]."&attach=".$data["attach"]."&app_key=".$key["AppKey"];

        //验证失败
        if($data["sign"] != md5($str)) return false;

        //组装数据
        $res = array(
            "status"    => $data["order_status"] == "2"? "success": "fail",                 //success:充值成功的订单，fail及其他:充值失败的订单
            "orderId"   => $data["attach"],                                                 //我们的订单号
            "tranId"    => $data["order_id"],                                               //渠道订单号
            "amount"    => $data["money"]                                                   //订单金额，单位元
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