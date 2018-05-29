<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用入口文件

// 检测PHP环境
header("Content-type: text/html; charset=utf-8");
include './Webscan.class.php';
if(version_compare(PHP_VERSION,'5.3.0','<'))  die('require PHP > 5.3.0 !');

// 开启调试模式 建议开发阶段开启 部署阶段注释或者设为false

define('APP_DEBUG',false);
define('BIND_MODULE','ThirdParty');
// 定义应用目录
define('APP_PATH','../Application/');

// 定义资源目录
define('STATIC_PATH','/static/');

//定义正反斜杠
if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

// 引入ThinkPHP入口文件
require '../ThinkPHP/ThinkPHP.php';
// 亲^_^ 后面不需要任何代码了 就是如此简单
