<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 2018/4/13
 * Time: 14:59
 *
 * 龙猫
 */

namespace Fusion\Model;

class LongmaoModel extends SdkModel
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
        $this->login_url        = "http://passport.aiyougs.com/user/getuserbytoken";
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
            "AdTrackingId"  => $key["AdTrackingId"],
            "TdAppId"       => $key["TdAppId"]
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

        // 发起请求
        $result = curl_post($this->login_url, http_build_query(array("token" => $data["token"])));
        $Result = json_decode($result, true);

        //验证失败
        if (!$result || $Result["errno"] != "0") {
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
                    "channelUserCode"   => $Result["data"]["uid"],
                    "channelUserName"   => $Result["data"]["username"]? $Result["data"]["username"]: ($data["userName"]? $data["userName"]: $Result["data"]["username"])
                )
            );
            return $res;
        }
    }

    /**
     * 获取数据的方法
     * @return mixed
     */
    public function getInput()
    {
        $input  = file_get_contents("php://input");
        $data   = json_decode($input, true);
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
        $order  = D("Api/Order")->getOrderById($data["gameOrderId"]);
        if (!$order) return false;

        //获取渠道参数
        $key    = $this->getKey($order["agent"]);

        //进行签名验证
        $str    = $data["uid"].$data["orderId"].$data["source"].$data["amount"].$data["gameOrderId"].$data["externalOrderId"].$data["ts"].$data["status"].$key["EncryptKey"];

        //验证失败
        if($data["sign"] != md5($str)) return false;

        //组装数据
        $res = array(
            "status"    => $data["status"] == "1"? "success": "fail",                       //success:充值成功的订单，fail及其他:充值失败的订单
            "orderId"   => $data["gameOrderId"],                                            //我们的订单号
            "tranId"    => $data["orderId"],                                                //渠道订单号
            "amount"    => $data["amount"]                                                  //订单金额，单位元
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