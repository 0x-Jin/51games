<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/11/15
 * Time: 14:13
 *
 * 应用宝YSDK
 */

namespace Fusion\Model;

class YsdkModel extends SdkModel
{

    private $channel_id     = "";                                                                           //渠道ID
    private $callback_url   = "";                                                                           //充值回调地址
    private $qq_app_id      = "";                                                                           //QQAppId
    private $qq_app_key     = "";                                                                           //QQAppKey
    private $wx_app_id      = "";                                                                           //WXAppId
    private $wx_app_key     = "";                                                                           //WXAppKey
    private $pay_app_Key    = "";                                                                           //OfferId
    private $server_name    = "";                                                                           //环境域名

    public function __construct(){
        parent::__construct();
        //渠道ID
        $this->channel_id   = $this->getChannelId();
        //充值回调地址
        $this->callback_url = C("COMPANY_DOMAIN")."Api/Reply/ChannelCallback/CyChannelId/".$this->channel_id;
    }

    /**
     * 设置参数
     * @param $key
     */
    private function setKey($key)
    {
        $this->qq_app_id    = $key["QQAppId"];
        $this->qq_app_key   = $key["QQAppKey"];
        $this->wx_app_id    = $key["WXAppId"];
        $this->wx_app_key   = $key["WXAppKey"];
        $this->pay_app_Key  = $key["PayAppKey"];
        if($key["test"] != 1){
            $this->server_name  = "ysdk.qq.com";
        }else{
            $this->server_name  = "ysdktest.qq.com";
        }
    }

    /**
     * 二登验证
     * @param $data
     * @return array
     */
    public function loginCheck($data)
    {
        //判断必要数据是否齐全
        if (!$data["agent"] || !$data["param"]) {
            $res = array(
                "Result"    => false,
                "Data"      => array()
            );
            return $res;
        }

        //判断参数是否完整
        $param  = json_decode($data["param"], true);
        if (!$param["openId"] || !$param["accessToken"] || !$param["platform"]) {
            $res = array(
                "Result"    => false,
                "Data"      => array()
            );
            return $res;
        }

        //获取渠道参数
        $key    = $this->getKey($data["agent"]);
        $this->setKey($key);

        require_once(LIB_PATH."/Org/Ysdk/Api.php");
        require_once(LIB_PATH."/Org/Ysdk/Ysdk.php");
        require_once(LIB_PATH."/Org/Ysdk/Payments.php");

        //判断登陆方式
        if ($param["platform"] == "QQ") {
            $res = $this->qq_login($param["openId"], $param["accessToken"]);
        } else {
            $res = $this->wx_login($param["openId"], $param["accessToken"]);
        }

        //验证失败
        if ($res["ret"] != 0) {
            $res = array(
                "Result"    => false,
                "Data"      => array()
            );
            return $res;
        }

        //更新登陆的参数，用于失败订单的补单
        $ysdkMap    = array(
            "status"        => 1,
            "num"           => array("LT", 5),
            "openId"        => $param["openId"],
            "platform"      => $param["platform"]
        );
        $ysdkData   = array(
            "pf"            => $param["pf"],
            "pfKey"         => $param["pfKey"],
            "payToken"      => $param["payToken"],
            "accessToken"   => $param["accessToken"]
        );
        D("Api/YsdkPayFailOrder")->updateOrder($ysdkMap, $ysdkData);

        //用户数据
        $res = array(
            "Result"    => true,
            "Data"      => array(
                "channelUserCode"   => $param["openId"],
                "channelUserName"   => $param["nickName"]
            )
        );

        return $res;
    }

    /**
     * QQ登陆验证
     * @param $openId
     * @param $accessToken
     * @return array
     */
    private function qq_login($openId, $accessToken)
    {
        //初始化SDK配置
        $sdk    = new \Api($this->qq_app_id, $this->qq_app_key);
        //设置调用环境，测试环境 or 现网环境
        $sdk->setServerName($this->server_name);
        $ts     = time();  //当前ts时间戳
        //接口的请求参数
        $params = array(
            "appid"     => $this->qq_app_id,
            "openid"    => $openId,
            "openkey"   => $accessToken,
            "userip"    => "",
            "sig"       => md5($this->qq_app_key.$ts),
            "timestamp" => $ts,
        );
        //验证登陆是否通过
        $ret = qq_check_token($sdk, $params);
        return $ret;
    }

    /**
     * 微信登陆验证
     * @param $openId
     * @param $accessToken
     * @return array
     */
    private function wx_login($openId, $accessToken)
    {
        //初始化SDK配置
        $sdk    = new \Api($this->wx_app_id, $this->wx_app_key);
        //设置调用环境，测试环境 or 现网环境
        $sdk->setServerName($this->server_name);
        $ts     = time();  //当前ts时间戳
        //接口的请求参数
        $params = array(
            "appid"     => $this->wx_app_id,
            "openid"    => $openId,
            "openkey"   => $accessToken,
            "userip"    => "",
            "sig"       => md5($this->wx_app_key.$ts),
            "timestamp" => $ts,
        );
        //验证登陆是否通过
        $ret = wx_check_token($sdk, $params);
        return $ret;
    }

    /**
     * 充值下单返回比率
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
     * 充值扣款
     * @param $data
     * @return bool|int
     */
    public function callback($data)
    {
        //判断必要数据是否齐全
        if (!$data["orderId"] || !$data["openId"] || !$data["payToken"] || !$data["accessToken"] || !$data["platform"] || !$data["pf"] || !$data["pfKey"]) return array("code" => 2);

        $order  = D("Api/Order")->getOrderById($data["orderId"]);
        //判断订单
        if (!$order || $order["channel_id"] != $this->channel_id || !$order["orderStatus"]) return array("code" => 3);
        $agent  = D("Api/Agent")->getAgent($order["agent"]);
        $game   = D("Api/Game")->getGame($agent["game_id"]);
        if (!$game) return array("code" => 3);

        //获取渠道参数
        $key    = $this->getKey($order["agent"]);
        $this->setKey($key);

        //加载YSDK类库
        require_once(LIB_PATH."/Org/Ysdk/Api.php");
        require_once(LIB_PATH."/Org/Ysdk/Ysdk.php");
        require_once(LIB_PATH."/Org/Ysdk/Payments.php");

        //判断该支付的用户是用哪种登录
        if ($data["platform"] == "QQ") {
            //QQ 初始化SDK配置
            $sdk    = new \Api($this->qq_app_id, $this->qq_app_key);
            $type   = "qq";
            $token  = $data["payToken"];
        } else {
            //微信 初始化SDK配置
            $sdk    = new \Api($this->wx_app_id, $this->wx_app_key);
            $type   = "wx";
            $token  = $data["accessToken"];
        }
        //设置支付id，支付key
        $sdk->setPay($this->qq_app_id, $this->pay_app_Key);
        //设置调用环境，测试环境or现网环境
        $sdk->setServerName($this->server_name);

        //查询余额
        $amount = $this->get_balance($data["openId"], $data["pf"], $data["pfKey"], $token, $type, $sdk);
        //余额是否足够
        if ($amount < $order["amount"] * $game["ratio"]) return array("code" => 1, "balance" => $amount);
        //扣除余额
        $res    = $this->pay_now($data["orderId"], $data["openId"], $data["pf"], $data["pfKey"], $token, $order["amount"] * $game["ratio"], $type, $sdk);
        //是否扣款成功
        if (!$res || $res["ret"] != 0) return array("code" => 1, "balance" => $amount);

        return array("code" => 0, "test" => $key["test"], "balance" => $amount);
    }

    /**
     * 查询余额
     * @param $openId
     * @param $pf
     * @param $pfKey
     * @param $token
     * @param $account_type
     * @param $sdk
     * @return mixed
     */
    private function get_balance($openId, $pf, $pfKey, $token, $account_type, $sdk){
        //参数
        $params = array(
            "openid"    => $openId,
            "openkey"   => $token,
            "ts"        => time(),              //当前ts时间戳
            "pf"        => $pf,
            "pfkey"     => $pfKey,
            "zoneid"    => 1,
        );
        //查询余额
        $ret = get_balance_m($sdk, $params, $account_type);
        return $ret["balance"];
    }

    /**
     * 扣除余额
     * @param $orderId
     * @param $openId
     * @param $pf
     * @param $pfKey
     * @param $token
     * @param $amount
     * @param $account_type
     * @param $sdk
     * @return mixed
     */
    private function pay_now($orderId, $openId, $pf, $pfKey, $token, $amount, $account_type, $sdk){
        //参数
        $params = array(
            "openid"    => $openId,
            "openkey"   => $token,
            "ts"        => time(),
            "pf"        => $pf,
            "pfkey"     => $pfKey,
            "zoneid"    => 1,
            "amt"       => $amount,
            "billno"    => $orderId
        );
        //扣除余额
        $ret = pay_m($sdk, $params, $account_type);
        return $ret;
    }
}