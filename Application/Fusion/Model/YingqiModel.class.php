<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 2018/4/23
 * Time: 17:56
 *
 * 盈麒
 */

namespace Fusion\Model;

class YingqiModel extends SdkModel
{
    private $channel_id     = "";                                                                           //渠道ID
    private $login_url      = "";                                                                           //充值回调地址
    private $callback_url   = "";                                                                           //充值回调地址

    public function __construct()
    {
        parent::__construct();
        //渠道ID
        $this->channel_id       = $this->getChannelId();
        //验证地址
        $this->login_url        = "http://chess.imyingqi.com/tokenCheck.do";
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
            "AppId" => intval($key["AppId"])
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
        if (!$data["agent"] || !$data["token"] || !$data["uid"]) {
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
            "uid"       => $data["uid"],
            "gameToken" => $data["token"],
            "gameCode"  => $key["AppId"]
        );

        // 发起请求
        $result = curl_get($this->login_url."?".http_build_query($param));
        $Result = json_decode($result, true);

        //验证失败
        if ($Result["state"] != true) {
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
                    "channelUserName"   => $data["userName"]? $data["userName"]: ($Result["data"]? $Result["data"]: $data["uid"])
                )
            );
            return $res;
        }
    }

    /**
     * 获取数据接口
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
        $order  = D("Api/Order")->getOrderById($data["data"]["outTradeId"]);
        if (!$order) return false;

        //获取渠道参数
        $key    = $this->getKey($order["agent"]);

        //组装参数
        $str    = "amt=".$data["data"]["amt"]."orderId=".$data["data"]["orderId"]."outTradeId=".$data["data"]["outTradeId"]."payWay=".$data["data"]["payWay"].$key["ApiKey"];

        //验证失败
        if($data["sign"] != md5($str)) return false;

        //组装数据
        $res = array(
            "status"    => "success",                                                       //success:充值成功的订单，fail及其他:充值失败的订单
            "orderId"   => $data["data"]["outTradeId"],                                     //我们的订单号
            "tranId"    => $data["data"]["orderId"],                                        //渠道订单号
            "amount"    => $data["data"]["amt"]                                             //订单金额，单位元
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