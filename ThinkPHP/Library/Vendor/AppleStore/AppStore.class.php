<?php
namespace Vendor\AppleStore;

class AppStore{

    public function __construct(){
        
    }

    /**
     * 返回信息
     * @param bool $result
     * @param string $msg
     * @param string $str
     * @param array $data
     * @return array
     */
    private function ReturnInfo($result = true, $msg = "请求成功", $str = "", $data = array())
    {
        $return = array(
            "result"    => $result,
            "msg"       => $msg,
            "str"       => $str,
            "data"      => $data
        );
        return $return;
    }
    
    /**
     * $purchasedata;
         * string(783) "{
            	"original-purchase-date-pst" = "2017-04-01 08:04:46 America/Los_Angeles";
            	"purchase-date-ms" = "1491059086961";
            	"unique-identifier" = "f18ec445f482e235335206fbec37a119ea718942";
            	"original-transaction-id" = "390000149269490";
            	"bvrs" = "1.0.0.0";
            	"app-item-id" = "1207131910";
            	"transaction-id" = "390000149269490";
            	"quantity" = "1";
            	"original-purchase-date-ms" = "1491059086961";
            	"unique-vendor-identifier" = "00CDE998-B992-4A88-8DC5-23DBBED86BD3";
            	"item-id" = "1207135401";
            	"version-external-identifier" = "820997692";
            	"product-id" = "com.qdazzle.jdfy.rmb6";
            	"purchase-date" = "2017-04-01 15:04:46 Etc/GMT";
            	"original-purchase-date" = "2017-04-01 15:04:46 Etc/GMT";
            	"bid" = "com.king.game.wjgyios";
            	"purchase-date-pst" = "2017-04-01 08:04:46 America/Los_Angeles";
            }"
            
            $purinfoarr
            array(8) {
              ["original-transaction-id"]=>
              string(15) "390000149269490"
              ["app-item-id"]=>
              string(10) "1207131910"
              ["transaction-id"]=>
              string(15) "390000149269490"
              ["quantity"]=>
              string(1) "1"
              ["item-id"]=>
              string(10) "1207135401"
              ["product-id"]=>
              string(21) "com.qdazzle.jdfy.rmb6"
              ["purchase-date"]=>
              string(27) "2017-04-01 15:04:46 Etc/GMT"
              ["bid"]=>
              string(21) "com.king.game.wjgyios"
            }
         * 
     * 回调验证方法
     */
    public function PayNotify($receiptData, $transactionId, $goodsCode, $bundleId)
    {
        $type = 0;
        if (empty($receiptData)) {
            return $this->ReturnInfo(false, "数据异常！", "receiptData is null!");
        }

        $returns    = $this->RequestValid(json_encode(array("receipt-data" => $receiptData, "password" => "")));
        $re_one     = json_decode($returns[0],true);
        $re_two     = $returns[1];
        !empty($re_one) && $returnTransfer = $re_one;

        //判断是否是测试环境的参数
        if (!empty($returnTransfer) && $returnTransfer["status"] == "21007") {
            $returns    = $this->RequestValid(json_encode(array("receipt-data" => $receiptData, "password" => "")), true);
            $re_one     = json_decode($returns[0],true);
            $re_two     = $returns[1];
            !empty($re_one) && $returnTransfer = $re_one;
            $type = 1;
        }

        $receipt    = $re_one["receipt"];
        $all_filter = empty($re_one)? false: true;
        if (!$receipt["in_app"] || count($receipt['in_app']) < 1 || $receipt["bundle_id"] != $bundleId) $all_filter = false;

        $filter     = false;
        foreach ($receipt["in_app"] as $v) {
            $in_app = $v;
            $filter = empty($in_app)? false: true;

            if (empty($in_app["product_id"]) || $in_app["product_id"] != $goodsCode) {
                //验证商品ID是否存在
                $filter .= "product-id:".$in_app["product_id"]." ";
            }
            if (time() - strtotime($in_app["purchase_date"]) > 1728000) {
                //20天以内，超过20则无效票据
                $filter .= "purchase-date:".$in_app["purchase_date"]." ";
            }
            if (empty($in_app["transaction_id"]) || $in_app["transaction_id"] != $transactionId) {
                //商品订单号是否一致
                $filter .= "transaction_id:".$in_app["transaction_id"]." ";
            }
            if ($filter === true) break;
        }
        
        if (!empty($returnTransfer) && isset($returnTransfer["status"]) && $returnTransfer["status"] === 0 && $filter === true && $all_filter === true) {
            $in_app["orderType"] = $type;
            return self::ReturnInfo(true, "支付成功，请返回游戏查看是否到账", "支付成功！", $in_app);
        } else {
            return self::ReturnInfo(false, "服务器繁忙，请稍等一段时间再查看是否到账，错误码：".$re_two, "查询凭证失败！", $filter);
        }
    }

    /**
     * 请求苹果支付
     * @param $data
     * @param bool $tester
     * @return array
     */
    private function RequestValid($data, $tester = false)
    {
        $purl = $tester? "https://sandbox.itunes.apple.com/verifyReceipt": "https://buy.itunes.apple.com/verifyReceipt";
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "Content-Length: ".strlen($data)
            )
        );
        curl_setopt($ch, CURLOPT_URL, $purl);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $returnTransfer = curl_exec($ch);
        $http_code      = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return array($returnTransfer, $http_code);
    }
}