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

// 开启调试模式 建议开发阶段开启 部署阶段注释或者设为false
define('APP_DEBUG',FALSE);
//define('APP_DEBUG',true);

define('APP_MODE', 'cli');  //CLI模式

// 定义应用目录
define('APP_PATH',dirname(dirname(__FILE__)).'/Application/');

//定义正反斜杠
if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

// 引入ThinkPHP入口文件
require dirname(dirname(__FILE__)).'/ThinkPHP/ThinkPHP.php';

// 亲^_^ 后面不需要任何代码了 就是如此简单
