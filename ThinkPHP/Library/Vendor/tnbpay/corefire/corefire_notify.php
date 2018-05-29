<?php
/**
 * Created on 2017/03/03
 * @author: xiaobing
 */

require_once "CorefireWxPay.Config.php";
require_once("CorefireWxPay.Api.php");
require_once("CorefireWxPay.Notify.php");

class CorefireWxPayNotifyCallBack extends CorefireWxPayNotify
{

	private $channel = null;
	public static $_registry = null;
	//查询订单
	public function Queryorder($out_trade_no,$time_end)
	{
	
	    //生产时使用真实的商户号、商户appid和商户key代替
	    $input = new CorefireAliPayOrderQuery(CorefireAliPayConfig::TEST_MCH_KEY);
	    $input->SetAppid(CorefireAliPayConfig::TEST_MCH_APPID);
	    $input->SetMch_id(CorefireAliPayConfig::TEST_MCH_ID);
	    $input->SetOut_trade_no($out_trade_no);
	    $input->SetMethod("mbupay.wxpay.query");
	    $result = CorefireAliPayApi::orderQuery($input);
	
	    if($result['return_code']=='SUCCESS'&&$result['result_code']=='SUCCESS')
	    {
	        return true;
	    }else{
	        return false;
	    }
	}
	
	

	//重写回调处理函数
	public function NotifyProcess($data, &$msg)
	{
		$notfiyOutput = array();

		if(!array_key_exists("out_trade_no", $data)){
			$msg = "输入参数不正确";
			return false;
		}
		//查询订单，判断订单真实性
		if(!$this->Queryorder($data["out_trade_no"])){
			$msg = "订单查询失败";
			return false;
		}
		return true;
	}

}

$notify = new CorefireWxPayNotifyCallBack();
$notify->Handle(false);

?>