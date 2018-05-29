<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 2018/4/10
 * Time: 17:40
 *
 * 快玩（9665）
 */

namespace Fusion\Model;

class KuaiwanModel extends SdkModel
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
        $this->login_url        = "http://api3.9665.com/conapi/Confirmdata/ucCheck.html";
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
        if (!$data["agent"] || !$data["uid"]) {
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
            "userid"    => $data["uid"],
            "specialid" => $key["GameId"]
        );
        ksort($param);
        //生成签名
        $param["sign"] = md5(http_build_query($param).$key["GameKey"]);

        // 发起请求
        $result = curl_post($this->login_url, http_build_query($param));
        $Result = json_decode($result, true);

        //验证失败
        if (!$result || $Result["result"] != "1") {
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
        $input  = file_get_contents("php://input");
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
        $order  = D("Api/Order")->getOrderById($data["orderId"]);
        if (!$order) return false;

        //获取渠道参数
        $key    = $this->getKey($order["agent"]);

        //进行签名验证
        ksort($data);
        $str    = "";
        foreach ($data as $k => $v) {
            if ($k != "sign") $str .= $k."=".$v."&";
        }

        //验证失败
        if($data["sign"] != md5(trim($str, "&").$key["GameKey"])) return false;

        //组装数据
        $res = array(
            "status"    => $data["status"] == "succ"? "success": "fail",                    //success:充值成功的订单，fail及其他:充值失败的订单
            "orderId"   => $data["orderId"],                                                //我们的订单号
            "tranId"    => $data["trade_sn"],                                               //渠道订单号
            "amount"    => $data["fee"]                                                     //订单金额，单位元
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