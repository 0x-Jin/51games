<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/11/23
 * Time: 9:39
 *
 * 联想
 */

namespace Fusion\Model;

class LenovoModel extends SdkModel
{

    private $channel_id     = "";                                                                           //渠道ID
    private $login_url      = "";                                                                           //二登验证地址
    private $callback_url   = "";                                                                           //充值回调地址

    public function __construct()
    {
        parent::__construct();
        //渠道ID
        $this->channel_id       = $this->getChannelId();
        //验证地址
        $this->login_url        = "http://passport.lenovo.com/interserver/authen/1.2/getaccountid";
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
            "AppID"     => $key["AppID"],
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

        //拼接参数
        $param  = array(
            "lpsust"    => $data["token"],
            "realm"     => $key["AppID"]
        );

        // 发起请求
        $result = curl_get($this->login_url."?".http_build_query($param));
        $xml    = simplexml_load_string($result);
        $uid    = trim($xml->AccountID);
        $name   = trim($xml->Username);

        //验证失败
        if (!$xml || !$uid) {
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
                    "channelUserCode"   => $uid,
                    "channelUserName"   => $name
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
        return array("waresid" => $goods);
    }

    /**
     * 回调验证接口
     * @param $data
     * @return array|bool
     */
    public function callbackCheck($data)
    {
        $trans  = json_decode($data["transdata"], true);

        //根据订单号获取渠道号，从而获取开发者的配置参数
        $order  = D("Api/Order")->getOrderById($trans["exorderno"]);
        if (!$order) return false;

        //获取渠道参数
        $key    = $this->getKey($order["agent"]);

        //进行签名验证
        require_once(LIB_PATH."/Org/Lenovo/lenovo_cashier_rsa.inc.php");
        $result = verify($data["transdata"], $key["AppKey"], $data["sign"]);

        //验证失败
        if(!$result) return false;

        //组装数据
        $res = array(
            "status"    => $trans["result"] == "0"? "success": "fail",                      //success:充值成功的订单，fail及其他:充值失败的订单
            "orderId"   => $trans["exorderno"],                                             //我们的订单号
            "tranId"    => $trans["transid"],                                               //渠道订单号
            "amount"    => $trans["money"]/100                                              //订单金额，单位元
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