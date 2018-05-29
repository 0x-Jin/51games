<?php
/**
 * Modify on 2017/06/07
 * @author: xb
 */
/**
* 	配置账号信息
*/
define('D_WX_NOTIFY_URL', "http://xxx.xxx.xxx/corefire/corefire_notify.php");
define('D_GATEWAY_URL', "https://api.tnbpay.com/pay/gateway");
class CorefireWxPayConfig
{
	//=======【curl代理设置】===================================
	/**
	 * TODO：这里设置代理机器，只有需要代理的时候才设置，不需要代理，请设置为0.0.0.0和0
	 * 本例程通过curl使用HTTP POST方法，此处可修改代理服务器，
	 * 默认CURL_PROXY_HOST=0.0.0.0和CURL_PROXY_PORT=0，此时不开启代理（如有需要才设置）
	 * @var unknown_type
	 */
	const CURL_PROXY_HOST = "0.0.0.0";//"10.152.18.220";
	const CURL_PROXY_PORT = 0;//8080;
	
	const NOTIFY_URL = D_WX_NOTIFY_URL;
	const GATEWAY_URL = D_GATEWAY_URL;
	const VERSION = "2.0.0";  //支付接口版本号
	const TEST_MCH_ID = "m20160923000000641";  //测试商户号
	const TEST_MCH_APPID = "a20160923000000641";  //测试商户appid
	const TEST_MCH_KEY = "b42d7012db98127cb1d005d1c2a828fa";  //测试商户key
}
