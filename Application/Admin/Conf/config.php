<?php
return array(
    //"配置项"=>"配置值"
    "DB_HOST" => "127.0.0.1",
    "DB_NAME" => "lgame",
    "DB_USER" => "root",
    "DB_PWD" => "root",
    "DB_PORT" => "3306",
    "DB_PREFIX" => "la_",
    "DB_PREFIX_API" => "lg_",
    "DB_PREFIX_LOG" => "nl_",
    "DB_PREFIX_WEBSITE" => "gw_",
    "DB_TYPE" => "mysql",   //数据库类型
    "DB_PARAMS" => array(\PDO::ATTR_CASE => \PDO::CASE_NATURAL),    //数据库强制转小写关闭
    "DEFAULT_FILTER" => 'trim', //后台不用htmlspecialchars

    "COMPANY_PASSWORD" => 'La_',    //后台密码前缀
    "URL_CASE_INSENSITIVE" => false,    //区分大小写
    'TMPL_ACTION_SUCCESS' => 'Public:success',   //成功跳转页面
    'TMPL_ACTION_ERROR' => 'Public:error',   //失败跳转页面
    'TMPL_PARSE_STRING' =>array(
        '__PUBLIC__' => '',     //增加新的Public类库替换规则
        '__JS__'  => STATIC_PATH.'admin/js',     //增加新的JS类库路径替换规则
        '__CSS__' => STATIC_PATH.'admin/css',    //增加新的CSS类库路径替换规则
        '__IMG__' => STATIC_PATH.'admin/img',   //增加新的IMG类库路径替换规则
        '__WEB__' => STATIC_PATH.'web',    //增加新的IMG类库路径替换规则
        '__WAP__'    => STATIC_PATH.'wap',       //增加新的IMG类库路径替换规则
    ),
    'LOG_LEVEL'            =>  'EMERG,ALERT,CRIT,ERR,WARN,INFO,DEBUG,SQL',  // 允许记录的日志级别
    'SHOW_ERROR_MSG'       => true,    // 显示错误信息
    'SESSION_OPTIONS'      => array(
        'name'             => 'CYsession',   //session名
        'expire'           => 24*3600,       //session保存一天
        'use_trans_sid'    => 1,             //跨页传递session
        'use_only_cookies' => 0
    ),
    //官网平台数据库
    "WEBSITE" => array(
        "DB_TYPE" => "mysql",
        "DB_HOST" => "127.0.0.1",
        "DB_PORT" => "3306",
        "DB_NAME" => "website",
        "DB_USER" => "root",
//        "DB_PWD"  => "jlsjlkjethlj79837gg",
        "DB_PWD"  => "root",
        "DB_PREFIX" => "gw_",
        "DB_PARAMS" => array(\PDO::ATTR_CASE => \PDO::CASE_NATURAL)    //数据库强制转小写关闭
    ),

    //创娱从库
    "CySlave" => array(
        "DB_TYPE"   => "mysql",
        "DB_HOST"   => "127.0.0.1",
        "DB_PORT"   => "3306",
        "DB_NAME"   => "lgame",
        "DB_USER"   => "root",
        "DB_PWD"    => "root",
//        "DB_PWD"    => "jlsjlkjethlj79837gg",
        "DB_PREFIX" => "la_",
        "DB_PARAMS" => array(\PDO::ATTR_CASE => \PDO::CASE_NATURAL)    //数据库强制转小写关闭
    ),
);

?>
