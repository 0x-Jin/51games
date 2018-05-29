<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/11/13
 * Time: 10:35
 *
 * OPPO
 */

namespace Fusion\Model;

class OppoModel extends SdkModel
{

    private $channel_id     = "";                                                                           //渠道ID
    private $callback_url   = "";                                                                           //充值回调地址
    private $oppo_public    = "";                                                                           //OPPO公钥

    public function __construct(){
        parent::__construct();
        //渠道ID
        $this->channel_id   = $this->getChannelId();
        //充值回调地址
        $this->callback_url = C("COMPANY_DOMAIN")."Api/Reply/ChannelCallback/CyChannelId/".$this->channel_id;
        //OPPO公钥
        $this->oppo_public  = "MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCmreYIkPwVovKR8rLHWlFVw7YDfm9uQOJKL89Smt6ypXGVdrAKKl0wNYc3/jecAoPi2ylChfa2iRu5gunJyNmpWZzlCNRIau55fxGW0XEu553IiprOZcaw5OuYGlf60ga8QT6qToP0/dpiL/ZbmNUO9kUhosIjEu22uFgR+5cYyQIDAQAB";
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
        if (!$data["agent"] || !$data["token"] || !$data["uid"]) {
            $res = array(
                "Result"    => false,
                "Data"      => array()
            );
            return $res;
        }

        //获取渠道参数
        $key    = $this->getKey($data["agent"]);

        require_once(LIB_PATH."/Org/Oppo/oppoLogin.php");
        $obj    = new \oppoLogin($key["AppKey"], $key["AppSecret"]);
        //登陆验证
        $Res    = $obj->LoginCheck($data["uid"], urlencode($data["token"]));

        //验证失败
        if ($Res["resultCode"] != "200" || $Res["ssoid"] != $data["uid"]) {
            $res = array(
                "Result"    => false,
                "Data"      => array()
            );
        } else {
            //用户数据
            $res = array(
                "Result"    => true,
                "Data"      => array(
                    "channelUserCode"   => $data["uid"],
                    "channelUserName"   => $Res["userName"]? $Res["userName"]: ($data["userName"]? $data["userName"]: $data["uid"])
                )
            );
        }
        return $res;
    }

    /**
     * 获取数据接口
     * @return mixed
     */
    public function getInput()
    {
        $data   = $_REQUEST;
        unset($data["CyChannelId"], $data["CyChannelVer"]);
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
        $order  = D("Api/Order")->getOrderById($data["partnerOrder"]);
        if (!$order) return false;

        //进行签名验证
        $str    = "";
        ksort($data);
        foreach ($data as $k => $v) {
            if ($k != "signMethod" && $k != "signature" && $v != "") $str .= $k."=".$v."&";
        }

        $str    = "notifyId={$data['notifyId']}&partnerOrder={$data['partnerOrder']}&productName={$data['productName']}&productDesc={$data['productDesc']}&price={$data['price']}&count={$data['count']}&attach={$data['attach']}";
        //生成公钥
        $pubKey = "-----BEGIN PUBLIC KEY-----\n".chunk_split($this->oppo_public, 64, "\n")."-----END PUBLIC KEY-----\n";
        $openssl_public_key = @openssl_get_publickey($pubKey);
        $result = @openssl_verify($str, base64_decode($data["sign"]), $openssl_public_key);
        @openssl_free_key($openssl_public_key);

        //验证失败
        if (!$result) return false;

        //组装数据
        $res = array(
            "status"    => "success",                                                       //success:充值成功的订单，fail及其他:充值失败的订单
            "orderId"   => $data["partnerOrder"],                                           //我们的订单号
            "tranId"    => $data["notifyId"],                                               //渠道订单号
            "amount"    => $data["price"]/100                                               //订单金额，单位元
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
        echo "result=OK&resultMsg=成功";
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
                $result = "验证失败";
                break;
            case 2:
                $result = "订单不存在";
                break;
            case 3:
                $result = "订单金额不一致";
                break;
            case 4:
                $result = "订单记录错误";
                break;
            case 5:
                $result = "订单用户不一致";
                break;
            default:
                $result = "其他错误";
        }
        echo "result=FAIL&resultMsg={$result}";
        exit();
    }
}