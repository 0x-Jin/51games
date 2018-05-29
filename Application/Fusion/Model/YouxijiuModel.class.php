<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 2018/2/8
 * Time: 17:23
 *
 * 游戏久
 */

namespace Fusion\Model;

class YouxijiuModel extends SdkModel
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
        $this->login_url        = "http://sdk.utoozs.com/youxi/sdk/checkUsertoken.php";
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

        //拼接参数
        $param  = array(
            "user_token"    => $data["token"],
            "mem_id"        => $data["uid"],
            "app_id"        => $key["AppId"]
        );
        //生成签名
        $param["sign"] = md5("app_id=".$key["AppId"]."&mem_id=".$data["uid"]."&user_token=".$data["token"]."&app_key=".$key["AppKey"]);

        // 发起请求
        $result = $this->http_post_param($this->login_url, json_encode($param));
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

    /**
     * @param $url
     * @param $data_string
     * @return string
     */
    private function http_post_param($url, $data_string) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);//设置等待时间
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json; charset=utf-8",
            "Content-Length: ".strlen($data_string))
        );
        ob_start();
        $returnTransfer = curl_exec($ch);
        curl_close($ch);
        return $returnTransfer;
    }
}