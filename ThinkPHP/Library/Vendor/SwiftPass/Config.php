<?php
class Config{
    private $cfg = array(
        'url'=>'https://pay.swiftpass.cn/pay/gateway',	//支付请求url，无需更改
//        'mchId'=>'175510359638',		//测试商户号，商户正式上线时需更改为自己的
        'mchId'=>'101560787390',		//测试商户号，商户正式上线时需更改为自己的
//        'appid'=>'wx2a5538052969956e',              //测试微信商户appid
//        'key'=>'61307e5f2aebcacecbcca6fe5296df9c',   //测试密钥，商户需更改为自己的
        'key'=>'bcbfcf113c517ea32cd8b9435de18ac6',   //测试密钥，商户需更改为自己的
        'device'=>'AND_WAP',   //充值类型
        'appName'=>'贵诚网络',   //应用名称
        'appId'=>'https://www.cmgcwl.cn/',   //首页
//		'notify_url'=>'http://lgame.com/Api/Reply/SwiftPass',//测试通知url，此处默认为空格商户需更改为自己的，保证能被外网访问到（否则支付成功后收不到威富通服务器所发通知）
        'version'=>'2.0'		//版本号
       );
    
    public function C($cfgName){
        return $this->cfg[$cfgName];
    }
}
?>