<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 2018/4/8
 * Time: 14:09
 *
 * 9377
 */

namespace Fusion\Model;

class JsqqModel extends SdkModel
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
     * 二登验证
     * @param $data
     * @return array
     */
    public function loginCheck($data)
    {
        //判断必要数据是否齐全
        if (!$data["agent"] || !$data["token"] || !$data["uid"] || !$data["userName"] || !$data["time"]) {
            $res = array(
                "Result"    => false,
                "Data"      => array()
            );
            return $res;
        }

        //获取渠道参数
        $key    = $this->getKey($data["agent"]);

        //拼接参数
        $str    = $data["userName"].$data["time"].$key["GameKey"];

        //验证失败
        if ($data["token"] != md5($str)) {
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
                    "channelUserName"   => $data["userName"]
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
        $order  = D("Api/Order")->getOrderById($data["extra_info"]);
        if (!$order) return false;

        //获取渠道参数
        $key    = $this->getKey($order["agent"]);

        //组装参数
        $str    = $data["username"].$data["order_id"].$data["server"].$data["amount"].$data["extra_info"].$data["timestamp"].$key["GameKey"];

        //验证失败
        if($data["sign"] != md5($str)) return false;

        //组装数据
        $res = array(
            "status"    => "success",                                                       //success:充值成功的订单，fail及其他:充值失败的订单
            "orderId"   => $data["extra_info"],                                             //我们的订单号
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
        $res = array(
            "state" => 1,
            "msg"   => "成功"
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
                $res["state"]   = 2;
                $res["msg"]     = "验证失败";
                break;
            case 2:
                $res["state"]   = 3;
                $res["msg"]     = "订单不存在";
                break;
            case 3:
                $res["state"]   = 4;
                $res["msg"]     = "订单金额不一致";
                break;
            case 4:
                $res["state"]   = 5;
                $res["msg"]     = "订单记录错误";
                break;
            case 5:
                $res["state"]   = 6;
                $res["msg"]     = "订单用户不一致";
                break;
            default:
                $res["state"]   = 9;
                $res["msg"]     = "其他错误";
        }
        echo json_encode($res);
        exit();
    }
}