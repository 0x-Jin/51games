<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/5/20
 * Time: 16:02
 */

/**
 * 获取IP地址
 * @return string
 */
function get_ip_address()
{
    if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
        $ip = $_SERVER["HTTP_CLIENT_IP"];
    } elseif ( ! empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
        $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
    } elseif ( ! empty($_SERVER["REMOTE_ADDR"])) {
        $ip = $_SERVER["REMOTE_ADDR"];
    } else {
        $ip = "";
    }
    return $ip;
}

/**
 * 通过IP获取地区区域
 * @param $ip IP
 * @return array 数组，city：城市，province：省市
 */
function ip_to_location($ip)
{
    $reg    = C("REGION");
    $ips    = Vendor\IP\IP::find($ip);
    $res    =  array(
        "city"      => (!empty($ips[2]) && $ips[2] != $ips[1]? $ips[2]: (isset($reg[$ips[1]])? $reg[$ips[1]]: $ips[1])),
        "province"  => $ips[1]
    );
    return $res;
}

/**
 * 获取随机字符串
 * @param $length 字符串长度
 * @return string
 */
function make_random($length)
{
    $chars  = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $str    = '';
    for ($i = 0; $i < $length; $i++ ) {
        $str .= $chars[mt_rand(0, strlen($chars) - 1)];
    }
    return $str;
}

/**
 * CURL模拟POST请求
 * @param $url
 * @param $params
 * @param int $timeout
 * @return mixed
 */
function curl_post($url, $params, $timeout = 5) {
    $ch = curl_init();
    $header = array(
        'Content-Type: application/x-www-form-urlencoded',
    );
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header); //定义请求类型
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_POST, true);
    if (strpos(strtolower($url), "https://") !== false) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    }
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    $returnTransfer = curl_exec($ch);
    curl_close($ch);
    return $returnTransfer;
}

/**
 * CURL模拟GET请求
 * @param $url
 * @param int $timeout
 * @return mixed
 */
function curl_get($url, $timeout = 5) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    $returnTransfer = curl_exec($ch);
    curl_close($ch);
    return $returnTransfer;
}

/**
 * 防刷[限定到每个模块中的某个方法]
 * 传入参数：
 * @param [string] $type 防刷类型  hour:小时  day:天
 * @param [int] $num 每个IP限制的每天/每小时可以访问的次数
 *
 * 返回接口访问状态
 * return [boolean]  true|false
 */
function prevent_reflash($type = 'hour', $num = 100)
{
    $ip = get_ip_address();
    $dir_path = RUNTIME_PATH . "Logs" . DS . "IpLimit" . DS . MODULE_NAME . DS . CONTROLLER_NAME . DS .  ACTION_NAME  . DS;
    $filePath = $dir_path . ($type == 'hour'? date("Y-m-d H"): date("Y-m-d")).'-'.$ip.".log";
    !is_dir(dirname($filePath)) && mkdir(dirname($filePath), 0777, true);
    @file_put_contents($filePath, "*", FILE_APPEND);

    //删除上一次的文件
    $file_name = ($type == 'hour' ? date('Y-m-d H', strtotime('-1 hour')): date('Y-m-d', strtotime('-1 day'))).'-'.$ip.".log";
    $unlink_file_path = $dir_path . $file_name;
    if(file_exists($unlink_file_path)) @unlink($unlink_file_path);
    
    if (filesize($filePath) > $num) {
        return false;
    } else {
        return true;
    }
}

/**
 * 记录日志
 * @param  [mixed] $data      [信息,type=info时为字符串类型，type=sql为数组，数组的键名与表字段名一致]
 * @param  [string] $type       [类型-info：本地调试、sql：异步入库]
 * @param  [string] $tabletype  [表类型-login_log，init_log]
 * @param  [string] $filename   [日志文件名]
 * @param  [string] $dir        [日志目录]
 * @return [boolean] ture/false
 */
function log_save($data, $type = 'info', $tabletype = '', $filename = '', $dir = '')
{
    if ($type == 'info') {
        $config = array(
            'path'      => LOG_PATH.($dir? $dir.'/': 'debug'.DS.date('Ym').DS),
            'file_size' => 2097152
        );

        !is_dir($config['path']) && mkdir($config['path'], 0755, true);
        $destination = $config['path'].(empty($filename)? date('y-m-d').'.log': $filename);

        if (is_file($destination) && floor($config['file_size']) <= filesize($destination)) {
            rename($destination, dirname($destination).DS.$_SERVER['REQUEST_TIME'].'-'.basename($destination));
        }
        
        is_array($data) && $data = json_encode($data);
        error_log('【'.date('Ymd H:i:s').'】【'.MODULE_NAME.'/'.ACTION_NAME.'】'.$data."\r\n", 3, $destination);
    } else {
        $sql = build_sql($data, $tabletype);
        if($sql === false){
            return  false;
        }
        log_save($sql, 'info', '', $filename, $dir);
    }
    
    return true;
}

/**
 * 创建SQL
 * @param  [array] $data  数据
 * @param  [string] $type 表类型
 * @return [string] $sql  组装的sql语句
 */
function build_sql($data, $type)
{
   $tab_map = array(
        'login_log' => array(
            'table' => 'lgame.nl_login',
            'field' => array('userCode','channelUserCode','udid','mac','imei','imei2','type','agent','game_id','channel_id','ver','time','ip','city','province','net'),
        ),
       'init_log' => array(
           'table' => 'lgame.nl_init',
           'field' => array('udid','game_id','channel_id','agent','mac','serial','imei','imei2','systemId','systemInfo','screen','type','net','ip','ver','gameVer','time','city','province'),
       ),
    );
   if(!in_array($type,array_keys($tab_map))) {
       return false;
   }
    // 字段过滤
    foreach ($tab_map[$type]['field'] as $value) {
        if(isset($data[$value])){
            $arr[$value] = $data[$value];
        }
    }
    $table = $tab_map[$type]['table'];
    $field = implode(',', array_keys($arr));
    $values = implode("','", $arr);
    $sql = "insert into {$table}({$field}) values('{$values}');";
    return $sql;
}