<?php
return array(
	//"配置项"=>"配置值"
    "DB_HOST" => "127.0.0.1",
    "DB_NAME" => "lgame",
    "DB_USER" => "root",
    "DB_PWD" => "root",
    "DB_PORT" => "3306",
    "DB_PREFIX" => "lg_",
    "DB_PREFIX_DATA" => "lg_",
    "DB_PREFIX_ADMIN" => "la_",
    "DB_PREFIX_LOG" => "nl_",
    "DB_TYPE" => "mysql",                                                       //数据库类型
    "DB_PARAMS" => array(\PDO::ATTR_CASE => \PDO::CASE_NATURAL),                //数据库强制转小写关闭

    "COMPANY_NAME"      => "创娱网络",                                           //公司名称
    "COMPANY_CODE"      => "Cy51",                                              //用户前缀
    "COMPANY_PASSWORD"  => "Ls_",                                               //密码前缀
    "COMPANY_ORDER"     => "L",                                                 //订单前缀
    "COMPANY_QQ"        => "10001",                                             //客服QQ
    "COMPANY_PHONE"     => "800180183",                                             //客服号码
    "COMPANY_DOMAIN"    => "http://apisdk.chuangyunet.net/",                    //域名地址
    "COMPANY_PRIVACY"   => "http://apisdk.chuangyunet.net/html/PrivacyPolicy.html",         //隐私条件
    "COMPANY_AGREEMENT" => "http://apisdk.chuangyunet.net/html/UserAgreement.html",         //用户协议

    "SMS_USER"          => "200990",                                            //SMS账号
    "SMS_PASSWORD"      => "cywl20170712",                                      //SMS密码
    "SESSION_AUTO_START"    => false,                                           //关闭SESSION

    "REGION" => array(                                                          //IP需要的地址配置
        "北京" => "北京",
        "天津" => "天津",
        "山西" => "太原",
        "辽宁" => "沈阳",
        "吉林" => "长春",
        "上海" => "上海",
        "江苏" => "南京",
        "浙江" => "杭州",
        "安徽" => "合肥",
        "福建" => "福州",
        "江西" => "南昌",
        "山东" => "济南",
        "河南" => "郑州",
        "湖北" => "武汉",
        "湖南" => "长沙",
        "广东" => "广州",
        "广西" => "南宁",
        "海南" => "海口",
        "重庆" => "重庆",
        "四川" => "成都",
        "贵州" => "贵阳",
        "云南" => "昆明",
        "西藏" => "拉萨",
        "陕西" => "西安",
        "甘肃" => "兰州",
        "青海" => "西宁",
        "宁夏" => "银川",
        "香港" => "香港",
        "澳门" => "澳门",
        "台湾" => "台湾",
        "河北" => "石家庄",
        "新疆" => "乌鲁木齐",
        "内蒙古" => "呼和浩特",
        "黑龙江" => "哈尔滨",
    )
);
?>
