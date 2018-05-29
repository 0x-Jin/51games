<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 2018/1/17
 * Time: 11:21
 *
 * 第一波
 */

namespace Fusion\Model;

class DiyiboModel extends SdkModel
{
    private $channel_id     = "";                                                                           //渠道ID
    private $callback_url   = "";                                                                           //充值回调地址
    private $and_login_url  = "";                                                                           //获取用户信息地址
    private $ios_login_url  = "";                                                                           //获取用户信息地址

    public function __construct()
    {
        parent::__construct();
        //渠道ID
        $this->channel_id       = $this->getChannelId();
        //安卓验证地址
        $this->and_login_url    = "http://juhe.sdk.szdiyibo.com/integration/api/verifyToken";
        //IOS验证地址
        $this->ios_login_url    = "http://sdk1.szdiyibo.com/check_tokens.php";
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
        if (!$data["agent"] || !$data["token"]) {
            $res = array(
                "Result"    => false,
                "Data"      => array()
            );
            return $res;
        }

        //获取渠道参数
        $key    = $this->getKey($data["agent"]);
        $agent  = D("Api/Agent")->getAgent($data["agent"]);

        if ($agent["gameType"] == 2) {
            //IOS

            //拼接参数
            $param  = array(
                "game_id"   => (int)$key["GameId"],
                "user_id"   => $data["uid"],
                "time"      => time(),
                "tokens"    => $data["token"]
            );
            //生成sign
            ksort($param);
            $str    = "";
            foreach ($param as $v) {
                $str .= $v;
            }
            $param["sign"]  = md5($str.$key["AppSecret"]);

            // 发起请求
            $result = curl_get($this->ios_login_url."?".http_build_query($param));

            //判断请求是否正确
            if ($result != "True") {
                $Result["code"] = "100";
            } else {
                $Result = array(
                    "code" => "200",
                    "data" => array(
                        "userId" => $data["uid"]
                    )
                );
            }
        } else {
            //安卓

            //拼接参数
            $param  = array(
                "appKey"    => $key["AppKey"],
                "token"     => $data["token"],
                "sign"      => md5($data["token"].$key["AppKey"].$key["AppSecret"])
            );

            // 发起请求
            $result = curl_post($this->and_login_url, http_build_query($param));
            $Result = json_decode($result, true);
        }

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
                    "channelUserCode"   => $Result["data"]["userId"],
                    "channelUserName"   => $data["userName"]? $data["userName"]: $Result["data"]["userId"]
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

        //获取商品ID
        $goods = $this->getFusionGoods($data["agent"], $data["goodsCode"]);
        return array("productId" => $goods);
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
        if (!$order) {
            //IOS订单查询
            $order  = D("Api/Order")->getOrderById($data["game_order_id"]);
            if (!$order) return false;
        }

        //获取渠道参数
        $key    = $this->getKey($order["agent"]);
        $agent  = D("Api/Agent")->getAgent($order["agent"]);

        if ($agent["gameType"] == 2) {
            //IOS

            //进行签名验证
            ksort($data);
            $str    = "";
            foreach ($data as $k => $v) {
                if ($k == "sign") continue;
                if ($k == "game_callback_info") {
                    $str .= urlencode($v);
                } else {
                    $str .= $v;
                }
            }

            //验证失败
            if($data["sign"] != md5($str.$key["AppSecret"])) return false;

            //组装数据
            $res = array(
                "status"    => $data["order_status"] == "success"? "success": "fail",           //success:充值成功的订单，fail及其他:充值失败的订单
                "orderId"   => $data["game_order_id"],                                          //我们的订单号
                "tranId"    => $data["order_id"],                                               //渠道订单号
                "amount"    => $data["order_amount"]/100,                                       //订单金额，单位元
                "sandbox"   => $data["sandbox"] == "1"? "1": "0"                                //0：正式环境，1：沙箱环境
            );
        } else {
            //安卓

            //进行签名验证
            $str    = $data["gameOrderId"].$data["productId"].$data["transactionId"].$key["AppKey"].$key["AppSecret"];

            //验证失败
            if($data["sign"] != md5($str)) return false;

            //组装数据
            $res = array(
                "status"    => $data["status"] == "1"? "success": "fail",                       //success:充值成功的订单，fail及其他:充值失败的订单
                "orderId"   => $data["gameOrderId"],                                            //我们的订单号
                "tranId"    => $data["transactionId"],                                          //渠道订单号
                "amount"    => $data["money"]                                                   //订单金额，单位元
            );
        }

        return $res;
    }

    /**
     * 成功返回接口
     * @param array $data 返回数据
     */
    public function callbackSuc($data = array())
    {
        //返回成功
        if (isset($data["game_order_id"])) {
            echo "Success";
        } else {
            echo "ok";
        }
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
        if (isset($data["game_order_id"])) {
            echo "Failure";
        } else {
            echo "fail";
        }
        exit();
    }
}