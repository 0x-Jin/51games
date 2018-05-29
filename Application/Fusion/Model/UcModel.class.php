<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/6/21
 * Time: 14:27
 *
 * UC渠道
 */

namespace Fusion\Model;

class UcModel extends SdkModel
{

    private $channel_id     = "";                                                                           //渠道ID
    private $callback_url   = "";                                                                           //充值回调地址

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
            "gameId" => $key["gameId"]
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

        require_once(LIB_PATH."/Org/Uc/service/SDKServerService.php");
        require_once(LIB_PATH."/Org/Uc/model/SDKException.php");
        require_once(LIB_PATH."/Org/Uc/config/config.inc.php");

        new \configinc($key["gameId"], $key["apiKey"]);

        //二登验证
        try {
            $sessionInfo = \SDKServerService::verifySession($data["token"]);
        } catch (\SDKException $e){
            $res = array(
                "Result"    => false,
                "Data"      => array()
            );
            return $res;
        }

        //用户数据
        $res = array(
            "Result"    => true,
            "Data"      => array(
                "channelUserCode"   => $sessionInfo->accountId,
                "channelUserName"   => $sessionInfo->nickName
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
        if (!$data["agent"] || !$data["userCode"] || !$data["goodsCode"]) return false;

        //获取渠道参数
        $key    = $this->getKey($data["agent"]);

        //获取用户信息
        $user   = D("Api/User")->getUserByCode($data["userCode"]);

        //商品信息
        $goods  = D("Api/Goods")->getGoods($data["goodsCode"]);

        //返回的数据
        $info   = array(
            "callbackInfo"  => $orderId,
            "amount"        => $goods["amount"],
            "notifyUrl"     => $this->callback_url,
            "cpOrderId"     => $orderId,
            "accountId"     => $user["channelUserCode"],
            "signType"      => "MD5"
        );

        //拼接字符串
        ksort($info);
        $str = "";
        foreach ($info as $k => $v) {
            if ($k != "signType" && $v !== null) $str .= $k."=".$v;
        }

        //签名
        $info["sign"] = md5($str.$key["apiKey"]);

        return $info;
    }

    /**
     * 获取数据接口
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
        $order  = D("Api/Order")->getOrderById($data["data"]["cpOrderId"]);
        if (!$order) return false;

        //获取渠道配置
        $key    = $this->getKey($order["agent"]);

        require_once(LIB_PATH."/Org/Uc/service/SDKServerService.php");
        require_once(LIB_PATH."/Org/Uc/model/SDKException.php");

        $baseService    = new \BaseSDKService();
        //组装签名原文
        $signSource     = $baseService->getSignData($data["data"]).$key["apiKey"];
        //MD5加密签名
        $sign           = md5($signSource);

        //验证失败
        if ($sign != $data["sign"]) return false;

        //组装数据
        $res = array(
            "status"    => $data["data"]["orderStatus"] == "S"? "success": "fail",          //success:充值成功的订单，fail及其他:充值失败的订单
            "orderId"   => $data["data"]["cpOrderId"],                                      //我们的订单号
            "tranId"    => $data["data"]["orderId"],                                        //渠道订单号
            "amount"    => $data["data"]["amount"]                                          //订单金额，单位元
        );

        return $res;
    }

    /**
     * 成功返回接口
     * @param array $data 返回数据
     */
    public function callbackSuc($data = array())
    {
        echo "SUCCESS";
        exit();
    }

    /**
     * 失败返回借口
     * @param 错误类型 $num
     * @param array $data 返回数据
     */
    public function callbackErr($num, $data = array())
    {
        echo "FAILURE";
        exit();
    }
}