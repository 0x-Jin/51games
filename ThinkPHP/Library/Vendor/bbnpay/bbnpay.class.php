<?php
namespace Vendor\bbnpay;

class bbnpay{

    private $orderUrl   = "https://payh5.bbnpay.com/cpapi/place_order.php";         //下单请求的接口
    private $payUrl     = "https://payh5.bbnpay.com/browserh5/paymobile.php";       //收银台页面地址
    private $quickUrl   = "https://payh5.bbnpay.com/h5pay/way.php";                 //快速下单页面地址
//    private $appKey     = "3ee8bc373ac89d82c16c10e3d79c9a3e";                       //* appkey
//    private $appid      = "8662017090684199";                                       //* appid
//    private $pcuserid   = "fxpay@chuangyunet.com";                                  //* 用户在商户应用的唯一标识 一般为用户名或用户ID
//    private $goodid     = 3995;                                                     //* 商品ID
    private $appKey     = "1f34955c49d2b6425f9a0c26634bfabe";                       //* appkey
    private $appid      = "1761201712271276";                                       //* appid
    private $pcuserid   = "3475703587@qq.com";                                      //* 用户在商户应用的唯一标识 一般为用户名或用户ID
    private $goodid     = 5194;                                                     //* 商品ID
    private $notifyUrl  = "http://apisdk.chuangyunet.net/Api/Reply/bbnpayCallback"; //* 支付回调通知地址
    private $backUrl    = "http://apisdk.chuangyunet.net/html/bbnpayPayBack.html";  //* 用户支付后同步跳转页面
    private $goods      = array(
        "0.01"  => 667,
    );

    public function __construct(){
        
    }

    /**
     * 下单接口
     * @param $orderId
     * @param $amount
     * @return bool|string
     */
    public function submitOrder($orderId, $amount)
    {

        $transData = array(
            "appid"     => $this->appid,
            "goodsid"   => $this->goodid,//$this->goods[$amount],                       //* 商品编号
            "pcorderid" => $orderId,                                                    //商户订单号
            "money"     => $amount * 100,                                               //支付金额 以分为单位，例如如果该商品为1元，则应该填写100
            "currency"  => "CHY",                                                       //货币类型
            "pcuserid"  => $this->pcuserid,                                             //* 用户在商户应用的唯一标识 一般为用户名或用户ID
            "notifyurl" => $this->notifyUrl                                             // *支付回调  商户服务端接收支付回调通知的地址
        );

        $url_transData = urlencode(json_encode($transData));                //array转json并url编码
        $sign = $this->getSign($transData, $this->appKey);

        $info = array(
            "transdata" =>$url_transData,
            "sign"      =>$sign,
            "signtype"  =>"MD5"
        );

        //下单
        $res = $this->send($this->orderUrl, $info);
        $str = urldecode($res);                                                 //应答参数
        $arr = explode("&",$str);
        foreach ($arr as $key => $val) {
            $arr[$key] = explode("=", $val);
        }
        $trans_arr  = json_decode($arr[0][1],true);
        $rep_sign   = md5("code={$trans_arr['code']}&transid={$trans_arr['transid']}&key={$this->appKey}");

        //验证签名无误后生成进入收银台的连接
        if ($arr[1][1] == $rep_sign) {
            //快速支付
            $data = array(
                "appid"     => $transData["appid"],
                "transid"   => $trans_arr["transid"],
                "paytype"   => 1,
                "backurl"   => $this->backUrl                   // * 支付后要跳转到的页面
            );
            $data_zhifu = urlencode(json_encode($data));  //用户url传输的data数据
            $sign_zhifu = $this->getSign($data, $this->appKey);  //sign签名  注意排序
            $url_zhifu  = $this->quickUrl."?data=".$data_zhifu."&sign=".$sign_zhifu."&signtype=MD5";

            return $url_zhifu;
        }
        return false;
    }

    /**
     * 解密验证
     * @param $info
     * @return bool|mixed
     */
    public function callback($info)
    {
        //判断数据是否完整
        if (!isset($info["transdata"]) || !isset($info["sign"])) return false;

        $data = json_decode($info["transdata"], true);
        //签名验证
        $sign = $this->getSign($data, $this->appKey);
        if ($sign != $info["sign"]) return false;

        return $data;
    }

    /**
     * 请求方法
     * @param $url
     * @param $data
     * @return mixed
     */
    private function send($url, $data)
    {
        $ch      = curl_init();
        $timeout = 30;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); // 从证书中检查SSL加密算法是否存在

        $html     = curl_exec($ch);
        $curlinfo = curl_getinfo($ch);
        curl_close($ch);
        return $html;
    }

    /**
     * 参数加密
     * @param $array
     * @param $appkey
     * @return string
     */
    private function getSign($array, $appkey){
        $str="";
        ksort($array);//按字典排序
        foreach($array as $k=>$v){
            $str .=$k.'='.$v.'&';   //以key=value&key=value格式处理好数据
        }
        $str .='key='.$appkey;  //最后加上签名
        return md5($str);
    }
}