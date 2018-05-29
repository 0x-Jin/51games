<?php
/**
 * Modify on 2016/12/03
 * @author: xiaobing
 */
require_once "CorefireWxPay.Exception.php";
require_once "CorefireWxPay.Config.php";
require_once "CorefireWxPay.Data.php";

class CorefireWxPayApi
{
    public static function jswap($inputObj, $timeOut = 30)
    {
        $url = "https://api.tectopper.com/pay/gateway";
        //检测必填参数
        if(!$inputObj->IsOut_trade_noSet()) {
            throw new CorefireWxPayException("缺少统一支付接口必填参数out_trade_no！");
        }else if(!$inputObj->IsBodySet()){
            throw new CorefireWxPayException("缺少统一支付接口必填参数body！");
        }else if(!$inputObj->IsTotal_feeSet()) {
            throw new CorefireWxPayException("缺少统一支付接口必填参数total_fee！");
        }else if(!$inputObj->IsAppidSet()) {
            throw new CorefireWxPayException("缺少统一支付接口必填参数appid！");
        }else if(!$inputObj->IsMch_idSet()) {
            throw new CorefireWxPayException("缺少统一支付接口必填参数mch_id！");
        }else if(!$inputObj->IsMethodSet()) {
            throw new CorefireWxPayException("缺少统一支付接口必填参数method！");
        }
    
        //异步通知url未设置，则使用配置文件中的url
        if(!$inputObj->IsNotify_urlSet()){
            $inputObj->SetNotify_url(CorefireWxPayConfig::NOTIFY_URL);//异步通知url
        }
    
        if(!$inputObj->IsVersionSet()){
            $inputObj->SetVersion(CorefireWxPayConfig::VERSION);
        }
    
        //$inputObj->SetSpbill_create_ip($_SERVER['REMOTE_ADDR']);//终端ip
        $inputObj->SetNonce_str(self::getNonceStr());//随机字符串
    
        //签名
        $inputObj->SetSign();
        $xml = $inputObj->ToXml();

        // 		$startTimeStamp = self::getMillisecond();//请求开始时间
        $response = self::postXmlCurl($xml, $url, false, $timeOut);
        
        //echo '<textarea cols="50" rows="10">'.$xml.'</textarea>';
        //echo '<textarea cols="50" rows="10">'.$response.'</textarea>';
        $result = CorefireWxPayResults::Init($response,$inputObj->GetKey());
        // 		self::reportCostTime($url, $startTimeStamp, $result);//上报请求花费时间
       
        return $result;
    }
	/**
	 * 
	 * 统一下单，CorefireWxPayUnifiedOrder中appid,mch_id,out_trade_no、body、total_fee、trade_type必填
	 * spbill_create_ip、nonce_str不需要填入
	 * @param WxPayUnifiedOrder $inputObj
	 * @param int $timeOut
	 * @throws CorefireWxPayException
	 * @return 成功时返回，其他抛异常
	 */
	public static function unifiedOrder($inputObj, $timeOut = 30)
	{
		$url = "https://api.tectopper.com/pay/gateway";
		//检测必填参数
		if(!$inputObj->IsOut_trade_noSet()) {
			throw new CorefireWxPayException("缺少统一支付接口必填参数out_trade_no！");
		}else if(!$inputObj->IsBodySet()){
			throw new CorefireWxPayException("缺少统一支付接口必填参数body！");
		}else if(!$inputObj->IsTotal_feeSet()) {
			throw new CorefireWxPayException("缺少统一支付接口必填参数total_fee！");
		}else if(!$inputObj->IsAppidSet()) {
			throw new CorefireWxPayException("缺少统一支付接口必填参数appid！");
		}else if(!$inputObj->IsMch_idSet()) {
			throw new CorefireWxPayException("缺少统一支付接口必填参数mch_id！");
        }else if(!$inputObj->IsMethodSet()) {
            throw new CorefireWxPayException("缺少统一支付接口必填参数method！");
        }else if(!$inputObj->IsOpenidSet()) {
            throw new CorefireWxPayException("缺少统一支付接口必填参数支付宝用户号或者账号openid！");
        }
		
				
		//异步通知url未设置，则使用配置文件中的url
		if(!$inputObj->IsNotify_urlSet()){
			$inputObj->SetNotify_url(CorefireWxPayConfig::NOTIFY_URL);//异步通知url
		}

		if(!$inputObj->IsVersionSet()){
		   $inputObj->SetVersion(CorefireWxPayConfig::VERSION);
		}
		
		//$inputObj->SetSpbill_create_ip($_SERVER['REMOTE_ADDR']);//终端ip
		$inputObj->SetNonce_str(self::getNonceStr());//随机字符串
		
		//签名
		$inputObj->SetSign();
		$xml = $inputObj->ToXml();
// 		$startTimeStamp = self::getMillisecond();//请求开始时间
		$response = self::postXmlCurl($xml, $url, false, $timeOut);
		//echo '<textarea cols="50" rows="10">'.$xml.'</textarea>';
		//echo '<textarea cols="50" rows="10">'.$response.'</textarea>';
		$result = CorefireWxPayResults::Init($response,$inputObj->GetKey());
// 		self::reportCostTime($url, $startTimeStamp, $result);//上报请求花费时间
		
		return $result;
	}
	
	/*退款*/

	public static function refundOrder($url,$inputObj, $timeOut = 30)
	{
	    //检测必填参数
	    if(!$inputObj->IsTransaction_idSet()&&!$inputObj->IsOut_trade_noSet()&&!$inputObj->IsPass_trade_noSet()){
	        throw new CorefireWxPayException("缺少退款接口必填参数transaction_id！");
	    }else if(!$inputObj->IsTotal_feeSet()) {
	        throw new CorefireWxPayException("缺少退款接口必填参数total_fee！");
	    }else if(!$inputObj->IsRefund_feeSet()) {
	        throw new CorefireWxPayException("缺少退款接口必填参数refund_fee！");
	    }else if(!$inputObj->IsAppidSet()) {
	        throw new CorefireWxPayException("缺少退款接口必填参数appid！");
	    }else if(!$inputObj->IsMch_idSet()) {
	        throw new CorefireWxPayException("缺少退款接口必填参数mch_id！");
	    }else if(!$inputObj->IsMethodSet()) {
            throw new CorefireWxPayException("缺少退款接口必填参数method！");
        }
	    $inputObj->SetVersion(CorefireWxPayConfig::VERSION);
	
	    $inputObj->SetNonce_str(self::getNonceStr());//随机字符串

	    //签名
	    $inputObj->SetSign();
	     
	    $xml = $inputObj->ToXml();

	    // 		$startTimeStamp = self::getMillisecond();//请求开始时间
	    $response = self::postXmlCurl($xml, $url, $timeOut);
	    
	    $result = CorefireWxPayResults::Init($response,$inputObj->GetKey());
	    // 		self::reportCostTime($url, $startTimeStamp, $result);//上报请求花费时间
	     
	    return $result;
	}
	
	/**
	 *
	 * 关闭订单，CorefireWxPayCloseOrder中appid,mchid,out_trade_no必填
	 * 
	 * @param WxPayCloseOrder $inputObj
	 * @param int $timeOut
	 * @throws CorefireWxPayException
	 * @return 成功时返回，其他抛异常
	 */
	public static function closeOrder($inputObj,$timeOut = 30)
	{
		$url = "https://api.tectopper.com/pay/gateway";
		//检测必填参数
		if(!$inputObj->IsOut_trade_noSet()) {
			throw new CorefireWxPayException("订单查询接口中，out_trade_no必填！");
		}else if(!$inputObj->IsAppidSet()) {
			throw new CorefireWxPayException("缺少统一支付接口必填参数appid！");
		}else if(!$inputObj->IsMch_idSet()) {
			throw new CorefireWxPayException("缺少统一支付接口必填参数mch_id！");
        }else if(!$inputObj->IsMethodSet()) {
            throw new CorefireWxPayException("缺少统一支付接口必填参数method！");
        }
		
		$inputObj->SetVersion(CorefireWxPayConfig::VERSION);
		$inputObj->SetNonce_str(self::getNonceStr());//随机字符串
	
		$inputObj->SetSign();//签名
		$xml = $inputObj->ToXml();
	
		$response = self::postXmlCurl($xml, $url, false, $timeOut);
		$result = CorefireWxPayResults::Init($response,$inputObj->GetKey());
	
		return $result;
	}
	
	/**
	 *
	 * 查询订单，CorefireWxPayApiOrderQuery中out_trade_no、transaction_id至少填一个
	 * appid、mchid、spbill_create_ip、nonce_str不需要填入
	 * @param CorefireWxPayOrderQuery $inputObj
	 * @param int $timeOut
	 * @throws CorefireWxPayException
	 * @return 成功时返回，其他抛异常
	 */
	public static function orderQuery($inputObj, $timeOut = 30)
	{
		$url = "https://api.tectopper.com/pay/gateway";
        if(!$inputObj->IsAppidSet()) {
            throw new CorefireWxPayException("缺少统一支付接口必填参数appid！");
        }else if(!$inputObj->IsMch_idSet()) {
            throw new CorefireWxPayException("缺少统一支付接口必填参数mch_id！");
        }else if(!$inputObj->IsMethodSet()) {
            throw new CorefireWxPayException("缺少统一支付接口必填参数method！");
        }
		//检测必填参数
		if(!$inputObj->IsOut_trade_noSet() && !$inputObj->IsTransaction_idSet() && !$inputObj->IsPass_trade_noSet()) {
			throw new CorefireWxPayException("订单查询接口中，out_trade_no、transaction_id,pass_trade_no至少填一个！");
		}

		$inputObj->SetVersion(CorefireWxPayConfig::VERSION);
		$inputObj->SetNonce_str(self::getNonceStr());//随机字符串
	
		$inputObj->SetSign();//签名
		$xml = $inputObj->ToXml();
	
		$response = self::postXmlCurl($xml, $url, false, $timeOut);
		$result = CorefireWxPayResults::Init($response,$inputObj->GetKey());
	
		return $result;
	}
	
	/**
	 * 
	 * 产生随机字符串，不长于32位
	 * @param int $length
	 * @return 产生的随机字符串
	 */
	public static function getNonceStr($length = 32) 
	{
		$chars = "abcdefghijklmnopqrstuvwxyz0123456789";  
		$str ="";
		for ( $i = 0; $i < $length; $i++ )  {  
			$str .= substr($chars, mt_rand(0, strlen($chars)-1), 1);  
		} 
		return $str;
	}
	
	/**
	 * 直接输出xml
	 * @param string $xml
	 */
	public static function replyNotify($xml)
	{
		echo $xml;
	}
	
	/**
	 * 以post方式提交xml到对应的接口url
	 * 
	 * @param string $xml  需要post的xml数据
	 * @param string $url  url
	 * @param bool $useCert 是否需要证书，默认不需要
	 * @param int $second   url执行超时时间，默认30s
	 * @throws CorefireWxPayException
	 */
	public static function postXmlCurl($xml, $url,$second = 30)
	{		
		$ch = curl_init();
		//设置超时
		curl_setopt($ch, CURLOPT_TIMEOUT, $second);
		
		//如果有配置代理这里就设置代理
		if(CorefireWxPayConfig::CURL_PROXY_HOST != "0.0.0.0"
			&& CorefireWxPayConfig::CURL_PROXY_PORT != 0){
			curl_setopt($ch,CURLOPT_PROXY, CorefireWxPayConfig::CURL_PROXY_HOST);
			curl_setopt($ch,CURLOPT_PROXYPORT, CorefireWxPayConfig::CURL_PROXY_PORT);
		}
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);//TRUE
		curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);//2严格校验
		//设置header
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		//要求结果为字符串且输出到屏幕上
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
		//post提交方式
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		//运行curl
		$data = curl_exec($ch);
		//返回结果
		if($data){
			curl_close($ch);
			return $data;
		} else { 
			$error = curl_errno($ch);
			curl_close($ch);
			throw new CorefireWxPayException("curl出错，错误码:$error");
		}
	}
	
	/**
	 * 获取毫秒级别的时间戳
	 */
	private static function getMillisecond()
	{
		//获取毫秒的时间戳
		$time = explode ( " ", microtime () );
		$time = $time[1] . ($time[0] * 1000);
		$time2 = explode( ".", $time );
		$time = $time2[0];
		return $time;
	}
}

