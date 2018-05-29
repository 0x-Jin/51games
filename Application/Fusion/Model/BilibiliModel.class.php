<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 2018/3/9
 * Time: 10:26
 *
 * 哔哩哔哩
 */

namespace Fusion\Model;

class BilibiliModel extends SdkModel
{
    protected $autoCheckFields  = false;                                                                        //关闭自动检测数据库字段
    private $channel_id         = "";                                                                           //渠道ID
    private $login_url_1        = "";                                                                           //获取用户信息地址1
    private $login_url_2        = "";                                                                           //获取用户信息地址2
    private $callback_url       = "";                                                                           //充值回调地址

    public function __construct(){
        parent::__construct();
        //渠道ID
        $this->channel_id   = $this->getChannelId();
        //验证地址1线
        $this->login_url_1  = "http://pnew.biligame.net/api/server/session.verify";
        //验证地址2线
        $this->login_url_2  = "http://pserver.bilibiligame.net/api/server/session.verify";
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
            "ServerId"      => $key["ServerId"],
            "MerchantId"    => $key["MerchantId"],
            "AppId"         => $key["AppId"],
            "AppKey"        => $key["AppKey"],
            "ServerName"    => $key["ServerName"]
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

        include_once(LIB_PATH."/Org/Bilibili/SignHelper.class.php");
        include_once(LIB_PATH."/Org/Bilibili/BiliApiHttpClient.class.php");
        $sdk    = new \BiliApiHttpClient();

        //配置参数
        $param = array(
            "uid"           => $data["uid"],
            "access_key"    => $data["token"]
        );

        $config = array(
            "game_id"           => $key["AppId"],
            "server_id"         => $key["ServerId"],
            "merchant_id"       => $key["MerchantId"],
            "secret_key"        => $key["SecretKey"],
            "user_agent"        => "Mozilla/5.0 GameServer",
        );

        //二登验证
        $result = $sdk->post($this->login_url_1, $param, $config);
        $Result = json_decode($result, true);

        //切线操作
        if (!$result || $Result["code"] != "0") {
            $result = $sdk->post($this->login_url_2, $param, $config);
            $Result = json_decode($result, true);

            //验证失败
            if (!$result || $Result["code"] != "0") {
                $res = array(
                    "Result"    => false,
                    "Data"      => array()
                );
                return $res;
            }
        }
        //用户数据
        $res = array(
            "Result"    => true,
            "Data"      => array(
                "channelUserCode"   => $Result["open_id"]? $Result["open_id"]: $data["uid"],
                "channelUserName"   => $Result["uname"]? $Result["uname"]: $data["userName"]
            )
        );
        return $res;
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
        if (!$data["gid"] || !$data["agent"] || !$data["goodsCode"]) return false;

        //获取游戏信息
        $key    = $this->getKey($data["agent"]);
        $game   = D("Api/Game")->getGame($data["gid"]);
        $goods  = D("Api/Goods")->getGoods($data["goodsCode"]);
        $str    = intval($goods["amount"]*$game["ratio"]).intval($goods["amount"]*100).$this->callback_url.$orderId.$key["SecretKey"];

        return array("gameMoney" => intval($goods["amount"]*$game["ratio"]), "sign" => md5($str));
    }

    /**
     * 获取数据接口
     * @return mixed
     */
    public function getInput()
    {
        $data   = $_REQUEST;
        return json_decode($data["data"], true);
    }

    /**
     * 回调验证接口
     * @param $data
     * @return array|bool
     */
    public function callbackCheck($data)
    {
        //根据订单号获取渠道号，从而获取开发者的配置参数
        $order  = D("Api/Order")->getOrderById($data["out_trade_no"]);
        if (!$order) return false;

        //获取渠道配置
        $key    = $this->getKey($order["agent"]);

        include_once(LIB_PATH."/Org/Bilibili/SignHelper.class.php");
        //验证失败
        if (!\SignHelper::checkSign($data, $key["SecretKey"], $data["sign"])) return false;

        //组装数据
        $res = array(
            "status"    => $data["order_status"] == "1"? "success": "fail",                 //success:充值成功的订单，fail及其他:充值失败的订单
            "orderId"   => $data["out_trade_no"],                                           //我们的订单号
            "tranId"    => $data["order_no"],                                               //渠道订单号
            "amount"    => $data["pay_money"]/100                                           //订单金额，单位元
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
        echo "failure";
        exit();
    }
}