<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 2018/1/16
 * Time: 9:41
 *
 * 悦玩
 */

namespace Fusion\Model;

class YuewanModel extends SdkModel
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
        $this->login_url        = "https://oauth.52ywan.com/oauth/token";
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
            "AppKey"    => $key["AppKey"],
            "AppSecret" => $key["AppSecret"]
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
            "authorize_code"    => $data["token"],
            "app_key"           => $key["AppKey"],
            "time"              => time()
        );
        //生成签名
        $param["sign"] = md5("authorize_code=".$data["token"]."&app_key=".$key["AppKey"]."&jh_sign=".$key["AppSecret"]."&time=".$param["time"]);

        // 发起请求
        $result = curl_post($this->login_url, http_build_query($param));
        $Result = json_decode($result, true);

        //验证失败
        if (!$result || $Result["ret"] != "1") {
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
                    "channelUserCode"   => $Result["content"]["user_id"],
                    "channelUserName"   => $Result["content"]["user_name"],
                    "extInfo"           => array(
                        "token"         => $Result["content"]["access_token"],
                        "userId"        => $Result["content"]["user_id"],
                        "userName"      => $Result["content"]["user_name"]
                    ),
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
        $data["sandbox"]    = $_SERVER["HTTP_X_YWPAY_SANDBOX"];
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
        $order  = D("Api/Order")->getOrderById($data["app_order_id"]);
        if (!$order) return false;

        //获取渠道参数
        $key    = $this->getKey($order["agent"]);

        //进行签名验证
        $str    = "app_key=".$data["app_key"]."&app_order_id=".$data["app_order_id"]."&app_role_id=".$data["app_role_id"]."&order_id=".$data["order_id"]."&pay_result=".$data["pay_result"]."&product_id=".$data["product_id"]."&server_id=".$data["server_id"]."&total_fee=".$data["total_fee"]."&user_id=".$data["user_id"] ."&jh_sign=".($key["AppKey"] >= 100000100? $key["PaySign"]: $key["AppSecret"])."&time=".$data["time"];

        //验证失败
        if($data["sign"] != md5($str)) return false;

        //组装数据
        $res = array(
            "status"    => $data["pay_result"] == "1"? "success": "fail",                   //success:充值成功的订单，fail及其他:充值失败的订单
            "orderId"   => $data["app_order_id"],                                           //我们的订单号
            "tranId"    => $data["order_id"],                                               //渠道订单号
            "amount"    => $data["total_fee"]/100,                                          //订单金额，单位元
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
        echo json_encode(array("ret" => 1, "msg" => "success", "content" => ""));
        exit();
    }

    /**
     * 失败返回接口
     * @param 错误类型 $num
     * @param array $data 返回数据
     */
    public function callbackErr($num, $data = array())
    {
        switch ($num) {
            case 1:
                $msg = "sign_error";
                break;
            case 2:
                $msg = "order_error";
                break;
            case 3:
                $msg = "amount_error";
                break;
            case 4:
                $msg = "save_error";
                break;
            case 5:
                $msg = "user_error";
                break;
            default:
                $msg = "unknown_error";
        }
        echo json_encode(array("ret" => $num + 1, "msg" => $msg, "content" => ""));
        exit();
    }
}