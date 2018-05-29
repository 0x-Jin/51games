<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/7/26
 * Time: 15:12
 *
 * iuuc支付类
 */

namespace Vendor\iuuc;

class iuuc
{

    private $siteid     = "101500081236";
    private $ckey       = "67085edf9c3000de90f82ccd04743c86";
    private $orderUrl   = "http://pay.iuuc.net/zfpay/interface/payinit.php";                //下单地址
    private $notifyUrl  = "http://apisdk.chuangyunet.net/Api/Reply/iuucCallback";           //支付回调地址
    private $returnUrl  = "http://apisdk.chuangyunet.net/html/PayBack.html";                //同步回调地址

    public function __construct()
    {

    }

    /**
     * 微信H5支付下单接口
     * @param $orderId
     * @param $amount
     * @param $subject
     * @return bool
     */
    public function submitWeixinH5Order($orderId, $amount, $subject)
    {
        //下单参数
        $transData = array(
            "siteid"        => $this->siteid,                                               //渠道编号
            "paytypeid"     => 14,                                                          //支付主接口类型编号，14：微信H5
            "siteorderid"   => $this->siteid."_".$orderId,                                  //商户订单号，硬性规定前5位必须为渠道编号
            "paymoney"      => $amount * 100,                                               //支付金额，以分为单位
            "goodsname"     => $subject,                                                    //商品名称
            "client_ip"     => get_client_ip(),                                             //客户端ip
            "thereport_url" => $this->notifyUrl,                                            //支付回调地址
            "thenotify_url" => $this->returnUrl,                                            //同步回调地址
            "tcid"          => 100                                                          //透传参数区分id，默认100
        );
        $transData["md5key"] = md5($transData["siteid"].$transData["siteorderid"].$transData["paymoney"].$transData["goodsname"].$transData["thereport_url"].$transData["thenotify_url"].$this->ckey);

        //下单
        $res = $this->sendPost($this->orderUrl, $transData);
        $Res = json_decode($res, true);

        //下单成功
        if ($Res["returncode"] == 100) {
            return $Res["h5url"];
        }
        return false;
    }

    /**
     * 回调验证接口
     * @param $info
     * @return bool
     */
    public function callback($info)
    {
        //判断数据是否完整
        if (!isset($info["siteid"]) || !isset($info["stat"]) || !isset($info["paymoney"]) || !isset($info["myorderid"]) || !isset($info["siteorderid"]) || !isset($info["md5key"])) return false;

        //签名验证
        if ($info["md5key"] != md5($info["siteid"].$info["stat"].$info["paymoney"].$info["myorderid"].$info["siteorderid"].$this->ckey)) return false;

        return true;
    }

    /**
     * 请求方法
     * @param $url
     * @param $data
     * @return mixed
     */
    private function sendPost($url, $data)
    {
        $ch      = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); // 从证书中检查SSL加密算法是否存在

        $html     = curl_exec($ch);
        $curlinfo = curl_getinfo($ch);
        curl_close($ch);
        return $html;
    }
}