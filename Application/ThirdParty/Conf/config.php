<?php
return array(
	//'配置项'=>'配置值'
    'DB_HOST' => '172.16.0.8',
    'DB_NAME' => 'lgame',
    'DB_USER' => 'root',
    'DB_PWD'  => 'jlsjlkjethlj79837gg',
    'DB_PORT' => '3306',
    'DB_PREFIX' => 'la_',
    'DB_PREFIX_API' => 'lg_',
    'DB_PREFIX_LOG' => 'nl_',
    'DB_PREFIX_TP'  => 'tp_',
    'DB_TYPE' => 'mysql',   //数据库类型
    'DB_PARAMS' => array(\PDO::ATTR_CASE => \PDO::CASE_NATURAL),    //数据库强制转小写关闭
    'DEFAULT_FILTER' => 'trim', //后台不用htmlspecialchars
    'LOG_LEVEL'            =>  'EMERG,ALERT,CRIT,ERR,WARN,INFO,DEBUG,SQL',  // 允许记录的日志级别

    'COMPANY_PASSWORD' => 'La_',    //后台密码前缀
    'URL_CASE_INSENSITIVE' => false,    //区分大小写
    'TMPL_ACTION_SUCCESS' => 'Public:success',   //成功跳转页面
    'TMPL_ACTION_ERROR' => 'Public:error',   //失败跳转页面
    'TMPL_PARSE_STRING' =>array(
        '__PUBLIC__' => '',     //增加新的Public类库替换规则
        '__JS__' => STATIC_PATH.'admin/js',     //增加新的JS类库路径替换规则
        '__CSS__' => STATIC_PATH.'admin/css',    //增加新的CSS类库路径替换规则
        '__IMG__' => STATIC_PATH.'admin/img',   //增加新的IMG类库路径替换规则
    ),
    'SESSION_OPTIONS'         =>  array(
        'name'                =>  'BJYSESSION',                    //设置session名
        'expire'              =>  5*3600*24,                      //SESSION保存15天
        'use_trans_sid'       =>  1,                               //跨页传递
    ),
    //创娱从库
    "CySlave" => array(
        "DB_TYPE"   => "mysql",
        "DB_HOST"   => "127.0.0.1",
        "DB_PORT"   => "3306",
        "DB_NAME"   => "lgame",
        "DB_USER"   => "root",
        "DB_PWD"    => "jlsjlkjethlj79837gg",
        "DB_PREFIX" => "la_",
        "DB_PARAMS" => array(\PDO::ATTR_CASE => \PDO::CASE_NATURAL)    //数据库强制转小写关闭
    ),
);
