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
 * 生成用户唯一标识符
 * @return string
 */
function make_user_code()
{
    return C("COMPANY_CODE").time().substr(uniqid(true), -5).substr(md5(microtime(true)), -5);
}

/**
 * 生成数据库保存的不可以逆密码
 * @param $password 用户密码
 * @return bool|string
 */
function make_password($password)
{
    return password_hash(C("COMPANY_PASSWORD").$password, PASSWORD_DEFAULT);
}

/**
 * 密码验证
 * @param $password 用户密码
 * @param $hash 数据库保存的不可逆密码
 * @return bool
 */
function check_password($password, $hash)
{
    return password_verify(C("COMPANY_PASSWORD").$password, $hash);
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
 * 发送手机验证码
 * @param $mobile 手机号
 * @param $type 验证码类型，0：其他，1：手机注册，2：绑定手机，3：手机解绑，4：修改密码
 * @param $name bool 信息头
 * @return array
 */
function sms_code($mobile, $type, $name = false)
{
    //判断数据是否完整
    if (!$mobile) {
        $res = array(
            "Code"  => false,
            "Msg"   => "请输入手机号码！"
        );
        return $res;
    }
    if (!isset($type)) {
        $res = array(
            "Code"  => false,
            "Msg"   => "请确定验证码类型！"
        );
        return $res;
    }

    //判断是否是正确的手机号码
    if (!preg_match("/^1\d{10}$/", $mobile)) {
        $res = array(
            "Code"  => false,
            "Msg"   => "请输入正确的手机号码！"
        );
        return $res;
    }

    //公司名称
    $company    = $name === false? "【".C("COMPANY_NAME")."】": $name;

    //短信模板
    $template   = array(
        0 => "{$company}您本次操作的验证码为%s，有效时间是5分钟，感谢您的使用！",
        1 => "{$company}您现在正进行手机账号注册操作，验证码为%s，有效时间是5分钟，感谢您的使用！",
        2 => "{$company}您现在正进行账号绑定手机操作，验证码为%s，有效时间是5分钟，感谢您的使用！",
        3 => "{$company}您现在正进行账号解绑手机操作，验证码为%s，有效时间是5分钟，感谢您的使用！",
        4 => "{$company}您现在正进行修改账号密码操作，验证码为%s，有效时间是5分钟，感谢您的使用！",
        5 => "{$company}您现在正进行手机验证码登陆操作，验证码为%s，有效时间是5分钟，感谢您的使用！"
    );

    //生成数字随机验证码
    $code   = rand(100000, 999999);

    //生成短信详情
    $msg    = vsprintf($template[$type], $code);
    if (empty($msg)) {
        $res = array(
            "Code"  => false,
            "Msg"   => "请确定验证码类型！"
        );
        return $res;
    }

    $sms = D("Api/Sms")->getSmsByMobile($mobile);

    //判断手机号是否最近1分钟已经收过验证码并未使用的
    if ($sms && $sms["status"] == 1 && time() - $sms["time"] < 10) {
        $res = array(
            "Code"  => false,
            "Msg"   => "获取验证码过于频繁！请稍后再试！"
        );
        return $res;
    }

    /**
     * 进行SMS接口对接
     */
    $sms        = false;
    $user       = C("SMS_USER");
    $password   = C("SMS_PASSWORD");
    $seqid      = date("ymdHis").rand(100000, 999999);
    $send       = array(
        "id"        => $seqid,
        "method"    => "send",
        "params"    => array(
            "userid"    => $user,
            "seqid"     => $seqid,
            "sign"      => md5($seqid.md5($password)),
            "submit"    => array(
                array(
                    "content"   => $msg,
                    "phone"     => $mobile
                )
            )
        )
    );

    //发送SMS请求
    $res = curl_post("https://112.74.139.4:8008/sms3_api/jsonapi/jsonrpc2.jsp", json_encode($send));
    $Res = json_decode($res, true);

    if($Res["result"][0]["return"] === "0"){
        $sms = true;
    }

    //是否请求发送成功
    if ($sms) {
        $data = array(
            "mobile"    => $mobile,
            "code"      => $code,
            "type"      => $type,
            "time"      => time(),
            "status"    => 1
        );
        if (D("Api/Sms")->addSms($data)) {
            //成功
            //拼接SMS的日志LOG数据
            $log            = $data;
            $log["content"] = $msg;
            $log["res"]     = $sms;
            //添加LOG日志
            D("Api/Sms")->addLog($log);

            $res = array(
                "Code"  => true,
                "Sms"   => $code,
                "Msg"   => "发送验证码成功！"
            );
        } else {
            //失败
            $res = array(
                "Code"  => false,
                "Msg"   => "记录验证码失败！请重新请求！"
            );
        }
    } else {
        $res = array(
            "Code"  => false,
            "Msg"   => "发送验证码失败！"
        );
    }
    return $res;
}

/**
 * 生成订单
 * @return string
 */
function make_order()
{
    return C("COMPANY_ORDER").date("YmdHis").rand(1000, 9999).substr(md5(microtime(true)), -5);
}

/**
 * 身份证验证
 * @param $IDCard
 * @return bool
 */
function check_IDCard($IDCard)
{
    if (strlen($IDCard) == 18) {
        return IDCard_check18($IDCard);
    } elseif ((strlen($IDCard) == 15)) {
        $IDCard = IDCard_15_to_18($IDCard);
        return IDCard_check18($IDCard);
    } else {
        return false;
    }
}

/**
 * 计算身份证校验码，根据国家标准GB 11643-1999
 * @param $number 身份证的前17位
 * @return bool|mixed 身份证的末位数字
 */
function IDCard_verify_number($number)
{
    if (strlen($number)!=17) {
        return false;
    }
    //加权因子
    $factor     = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
    //校验码对应值
    $list       = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
    $checksum   = 0;
    for ($i=0; $i < strlen($number); $i++) {
        $checksum += substr($number, $i, 1) * $factor[$i];
    }
    $mod = $checksum % 11;
    return $list[$mod];
}

/**
 * 将15位身份证升级到18位
 * @param $IDCard 15位身份证号码
 * @return bool|string 18位身份证号码
 */
function IDCard_15_to_18($IDCard){
    if (strlen($IDCard) != 15) {
        return false;
    } else {
        // 如果身份证顺序码是996 997 998 999，这些是为百岁以上老人的特殊编码
        if (array_search(substr($IDCard, 12, 3), array('996','997','998','999')) !== false) {
            $IDCard = substr($IDCard, 0, 6).'18'.substr($IDCard, 6, 9);
        } else {
            $IDCard = substr($IDCard, 0, 6).'19'.substr($IDCard, 6, 9);
        }
    }
    return $IDCard.IDCard_verify_number($IDCard);
}

/**
 * 18位身份证校验码有效性检查
 * @param $IDCard 18位身份证号码
 * @return bool
 */
function IDCard_check18($IDCard){
    if (strlen($IDCard) != 18) {
        return false;
    }
    $num = substr($IDCard, 0, 17);
    if (IDCard_verify_number($num) != strtoupper(substr($IDCard,17,1))) {
        return false;
    } else {
        return true;
    }
}

/**
 * CURL模拟POST请求
 * @param $url
 * @param $params
 * @param int $timeout
 * @param $header
 * @return mixed
 */
function curl_post($url, $params, $timeout = 5, $header = array("Content-Type: application/x-www-form-urlencoded")) {
    $ch = curl_init();
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


/**

 * 投放测试设备白名单
 * @DateTime  2017-12-01T16:43:58+0800
 * @param     [string]  $imei [测试设备imei]
 * @param     [string]  $idfa [测试设备idfa]
 * @param     [string]  $udid [测试设备udid]
 * @param     [string]  $ip   [测试设备ip]
 * @return    [bool]
 */
function whiteList($imei='',$idfa='',$udid='',$ip='')
{
    $imeiArr = array(
        '354765086797529',
        '862224033429559',
        '865428039097552',
        '351810083425386',
        '355457085520875',
        '990009261972973',
        '861648036376626',
        '861648036376634',
        '355830081717010',
        '866258030172982',
        '865759039995266',
        '864228032956460',
        '865714021375407',
        '865762035759702',
        '865762035691954',
        '865902032225188',
        '353570060894784',
        '863549030739645',
        '866265037069053',
        '866265037069046',
        '863072038209768',
        '863072038209776',
        '99000943907999',
        '864103024544261',
        '864327033202393', 
        '867305032378064',
        '867305032378072',
        '866654026741436',
        '99000968611259',
        '869158025888906',
        '869158028921290',
        '865759031397941',
        '865902032225196',
        '863339038880751',
    );  
    $idfaArr = array(
        'AF5E6D3B-06FE-4472-B72A-7E470DEF4923',
        '4879FDF6-7B7B-406F-A66E-28CD86D98E1B',
        '615C3227-1ED7-4450-B713-1B6B93BE557C',
        'F721C2D9-EB74-4A04-9DDC-F0D53295AFE0',
        'E240189B-AE7A-4458-9E54-D23B98D2602B',
        '064FAA88-9E18-4267-8055-BBB25477DFF8',
        'F42D1BE0-4F05-4A99-8E30-C07882936499',
        'F188C73C-9BEA-4E07-B0C7-399C1EEB7524',
        '17AB9032-C70C-4907-A344-3E2CBDFBB3B4',
        '4039E99C-2F47-4622-AE7E-A9E5410F450D',
        '9E07F043-F5B6-499F-9E1C-9E772E83F9FD',
        '6162B4BD-E4BB-4819-BB49-696C95A5F0AB',
        '73982869-2046-46F9-B930-A18F052D9E4B',
        '12EFD079-64A4-4353-AD84-2D32198F03FF',
        'F215B22A-1E85-40EB-87D3-2F0D7644528A',
        'D697F3C9-69C5-4CF2-9BBC-CF90913F4157',
        '6100B2EC-1F5E-4E7B-B1EC-B8A07868FC36',
        '6206F1DC-2DE1-4715-AC69-88B2D6A06F78',
        'B5B729AA-24F4-4E53-A014-B35093099DCD',
    );
    $udidArr = array(
        'f8efbf97-b623-3cbc-a946-0d954284808b'
    );
    $ipArr = array(
        '14.18.236.160'
    );
    if(in_array($imei,$imeiArr) || in_array($idfa,$idfaArr) || in_array($udid,$udidArr) || in_array($ip,$ipArr)){
        return true;
    }else{
        return false;
    }
}

/**
 * 指定关键字做数组的键名
 * @param Array $array  要操作的二维数组
 * @param string $key   二维数组里面的某个键名
 * @return Array 
 */
function field_to_key($array,$key)
{
    if(count($array) < 1) return false;
    $arr = array();
    foreach($array as $k => $v){
        isset($v[$key]) && $arr[$v[$key]] = $v;
    }
    return $arr;
}

/**
 * 获取当前时间毫秒
 * @return $msectime 
 */
function msectime()
{
    list($msec, $sec) = explode(' ', microtime());
    $msectime =  (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
    return $msectime;
}
