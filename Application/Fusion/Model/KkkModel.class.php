<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 2018/4/9
 * Time: 15:49
 *
 * 3K
 */

namespace Fusion\Model;

class KkkModel extends SdkModel
{
    private $channel_id     = "";                                                                           //渠道ID
    private $callback_url   = "";                                                                           //充值回调地址

    public function __construct()
    {
        parent::__construct();
        //渠道ID
        $this->channel_id       = $this->getChannelId();
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
            "GameId"        => $key["GameId"],
            "AppId"         => $key["AppId"],
            "AppKey"        => $key["AppKey"],
            "PublicKey"     => $key["PublicKey"],
            "MerchantId"    => $key["MerchantId"],
            "GameName"      => $key["GameName"],
            "BuglyAppId"    => $key["BuglyAppId"]
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
        if (!$data["agent"] || !$data["token"] || !$data["uid"] || !$data["guid"] || !$data["time"] || !$data["ext"]) {
            $res = array(
                "Result"    => false,
                "Data"      => array()
            );
            return $res;
        }

        //获取渠道参数
        $key    = $this->getKey($data["agent"]);

        //拼接参数
        $str    = $data["guid"].$data["ext"].$data["time"].$data["uid"].$key["GameSecret"];

        //验证失败
        if ($data["token"] != md5(md5($str))) {
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
     * 回调验证接口
     * @param $data
     * @return array|bool
     */
    public function callbackCheck($data)
    {
        //根据订单号获取渠道号，从而获取开发者的配置参数
        $order  = D("Api/Order")->getOrderById($data["callback_info"]);
        if (!$order) return false;

        //获取渠道参数
        $key    = $this->getKey($order["agent"]);

        //组装参数
        $str    = $data["amount"]."#".$data["callback_info"]."#".$data["order_id"]."#".$data["role_id"]."#".$data["server_id"]."#".$data["status"]."#".$data["timestamp"]."#".$data["type"]."#".$data["user_id"]."#".$key["GameSecret"];

        //验证失败
        if($data["sign"] != md5($str)) return false;

        //组装数据
        $res = array(
            "status"    => $data["status"] == 1? "success": "fail",                         //success:充值成功的订单，fail及其他:充值失败的订单
            "orderId"   => $data["callback_info"],                                          //我们的订单号
            "tranId"    => $data["order_id"],                                               //渠道订单号
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
        switch ($num) {
            case 1:
                $str = "ErrorSign";
                break;
            case 5:
                $str = "ErrorUser";
                break;
            default:
                $str = "FAILURE";
        }
        echo $str;
        exit();
    }
}