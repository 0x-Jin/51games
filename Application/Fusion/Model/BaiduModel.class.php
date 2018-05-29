<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/11/2
 * Time: 10:18
 *
 * 百度
 */

namespace Fusion\Model;

class BaiduModel extends SdkModel
{
    protected $autoCheckFields  = false;                                            //关闭自动检测数据库字段
    private $channel_id         = "";                                                                           //渠道ID
    private $callback_url       = "";                                                                           //充值回调地址

    public function __construct(){
        parent::__construct();
        //渠道ID
        $this->channel_id   = $this->getChannelId();
        //充值回调地址
        $this->callback_url = C("COMPANY_DOMAIN")."Api/Reply/ChannelCallback/CyChannelId/".$this->channel_id;
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

        require_once(LIB_PATH."/Org/Baidu/Sdk.php");
        $sdk    = new \Sdk($key["AppId"], $key["SecretKey"]);

        //二登验证
        $Res    = $sdk->login_state_result($data["token"]);

        if ($Res["ResultCode"] == "1" && $Res["Sign"] == $sdk->SignMd5($Res["ResultCode"], $Res["Content"])) {
            //Content参数需要urldecode后再进行base64解码
            $result = json_decode(base64_decode(urldecode($Res["Content"])), true);

            //用户数据
            $res = array(
                "Result"    => true,
                "Data"      => array(
                    "channelUserCode"   => $result["UID"],
                    "channelUserName"   => $result["UID"]
                )
            );

            return $res;
        } else {
            $res = array(
                "Result"    => false,
                "Data"      => array()
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

        return array("ratio" => $game["ratio"]);
    }

    /**
     * 获取数据接口
     * @return mixed
     */
    public function getInput()
    {
        $input  = file_get_contents("php://input");
        $data   = array();
        if (!empty($input)) {
            if (strpos($input, "&") && strpos($input, "=")) {
                $list = explode("&", $input);
                for ($i = 0; $i < count($list); $i++) {
                    $kv = explode("=", $list[$i]);
                    if (count($kv) > 1) {
                        if ($kv[0]=="Content") {
                            $data[$kv[0]] = urldecode($kv[1]);	//读取POST流的方式需要进行UrlDecode解码操作
                        } else {
                            $data[$kv[0]] = $kv[1];
                        }
                    }
                }
            }
        }
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
        $order  = D("Api/Order")->getOrderById($data["CooperatorOrderSerial"]);
        if (!$order) return false;

        //获取渠道配置
        $key    = $this->getKey($order["agent"]);

        //组装签名原文
        $signSource     = $key["AppId"].$data["OrderSerial"].$data["CooperatorOrderSerial"].$data["Content"].$key["SecretKey"];
        //MD5加密签名
        $sign           = md5($signSource);

        //验证失败
        if ($sign != $data["Sign"]) return false;

        $result         = base64_decode(str_replace(" ", "+", urldecode($data["Content"])));
        //json解析
        $Res            = json_decode($result, true);

        //组装数据
        $res = array(
            "status"    => $Res["OrderStatus"] == 1? "success": "fail",                     //success:充值成功的订单，fail及其他:充值失败的订单
            "orderId"   => $data["CooperatorOrderSerial"],                                  //我们的订单号
            "tranId"    => $data["OrderSerial"],                                            //渠道订单号
            "amount"    => $Res["OrderMoney"],                                              //订单金额，单位元
            "userCode"  => $Res["UID"]                                                      //用户账号
        );

        return $res;
    }

    /**
     * 成功返回接口
     * @param array $data 返回数据
     */
    public function callbackSuc($data = array())
    {
        //根据订单号获取渠道号，从而获取开发者的配置参数
        $order  = D("Api/Order")->getOrderById($data["CooperatorOrderSerial"]);
        //无法获取订单信息时返回错误
        if (!$order) {
            $Result["AppID"]        = $data["AppID"];
            $Result["ResultCode"]   = 1000;
            $Result["ResultMsg"]    = urlencode("接收参数失败");
            $Result["Sign"]         = md5($data["AppID"].$Result["ResultCode"]);
            $Result["Content"]      = "";
            echo urldecode(json_encode($Result));
            exit();
        }
        //获取渠道配置
        $key    = $this->getKey($order["agent"]);

        //返回成功
        $Result["AppID"]        = $key["AppId"];
        $Result["ResultCode"]   = 1;
        $Result["ResultMsg"]    = urlencode("成功");
        $Result["Sign"]         = md5($key["AppId"].$Result["ResultCode"].$key["SecretKey"]);
        $Result["Content"]      = "";
        echo urldecode(json_encode($Result));
        exit();
    }

    /**
     * 失败返回接口
     * @param 错误类型 $num
     * @param array $data 返回数据
     */
    public function callbackErr($num, $data = array())
    {
        //根据订单号获取渠道号，从而获取开发者的配置参数
        $order  = D("Api/Order")->getOrderById($data["CooperatorOrderSerial"]);
        //无法获取订单信息时返回错误
        if (!$order) {
            $Result["AppID"]        = $data["AppID"];
            $Result["ResultCode"]   = 1000;
            $Result["ResultMsg"]    = urlencode("接收参数失败");
            $Result["Sign"]         = md5($data["AppID"].$Result["ResultCode"]);
            $Result["Content"]      = "";
            echo urldecode(json_encode($Result));
            exit();
        }
        //获取渠道配置
        $key    = $this->getKey($order["agent"]);

        $Result["AppID"]    = $key["AppId"];
        $Result["Content"]  = "";
        switch ($num) {
            case 1:
                $Result["ResultCode"]   = 1001;
                $Result["ResultMsg"]    = urlencode("验证失败");
                break;
            case 4:
                $Result["ResultCode"]   = 1002;
                $Result["ResultMsg"]    = urlencode("订单失败");
                break;
            case 2:
            case 3:
            case 5:
            default:
                $Result["ResultCode"]   = 91;
                $Result["ResultMsg"]    = urlencode("订单错误");
        }
        $Result["Sign"]     = md5($key["AppId"].$Result["ResultCode"].$key["SecretKey"]);
        echo urldecode(json_encode($Result));
        exit();
    }
}
