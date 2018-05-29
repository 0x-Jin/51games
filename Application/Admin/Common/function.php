<?php
use Think\Upload; 
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/5/20
 * Time: 16:02
 */

/**
 * 进行登陆验证码验证
 * @param $code
 * @return bool
 */
function check_verify($code){
    $verify = new Think\Verify();
    return $verify->check($code);
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
 * 后台操作日志记录
 * @param $type  操作类型 1登录,2删除,3修改,4,新增
 * @param $record   操作信息
 * @return bool
 */
function bgLog($type=1, $record = '')
{
    $params = http_build_query(I());
    $data = array(
        'controller' => CONTROLLER_NAME,
        'action' => ACTION_NAME,
        'params' => $params,
        'type' => $type,
        'author' => session('admin.username')? session('admin.username'): 'API',
        'ip' => get_client_ip(),
        'record' => $record,
        'create_time' => date('Y-m-d H:i:s')
    );
    $res = M('operation_log')->add($data);
    return $res;
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
 * 图片文件上传
 * @param string $path  文件上传的路径
 * @return array 上传文件信息
 */
function file_upload($path,$out_flag = false,$path_name = ''){
        ini_set('memory_limit','1024M');
        set_time_limit(600);

        $upload = new \Think\Upload(array('replace'=>true));
        $upload->maxSize  = 9999999 ;// 设置附件上传大小
        $upload->exts = array('jpg','png','jpeg','gif','bmp','acc','mp3','mp4','ogg');
        $upload->replace = true;
        $upload->pathId = $path;
        $upload->rootPath = './Uploads/';
        $upload->subName = '';
        $upload->savePath = ($path_name? $path_name: ($out_flag? 'material/out_media': 'material')).'/'.$path.'/';

        if(!is_dir($upload->rootPath.$upload->savePath)){
            @mkdir($upload->rootPath.$upload->savePath,0777,true);
        }
        if(!$info = $upload->upload()) {// 上传错误提示错误信息
            $info = $upload->getError();
        }
        return $info;
}

/**
 * Excel文件上传
 * @param string $dirname  文件上传的目录（基于Uploads）
 * @return array 上传文件信息
 */
function excel_file_upload($dirname = ''){
        ini_set('memory_limit','1024M');
        set_time_limit(600);
        $upload = new \Think\Upload();
        $upload->maxSize  = 20*1024*1024 ;// 设置附件上传大小
        $upload->exts = array('xls','csv', 'xlsx');
        $upload->rootPath = './Uploads/';
        $upload->savePath = empty($dirname) ? '' : $dirname.'/';

        if(!is_dir($upload->rootPath.$upload->savePath)){
            @mkdir($upload->rootPath.$upload->savePath,0777,true);
        }
        if(!$info = $upload->upload()) {// 上传错误提示错误信息
            $info = $upload->getError();
        }
        return $info;
}

/**
 * Excel文件上传
 * @param string $dirname  文件上传的目录（基于Uploads）
 * @return array 上传文件信息
 */
function file_upload_all($dirname = ""){
    ini_set('memory_limit','1024M');
    set_time_limit(600);
    $upload = new \Think\Upload();
    $upload->maxSize  = 100*1024*1024 ;// 设置附件上传大小
    $upload->exts = array('xls', 'csv', 'xlsx', 'pdf', 'jpg', 'png', 'rar', 'zip');
    $upload->replace = true;
    $upload->saveName = "";
    $upload->subName = '';
    $upload->rootPath = './Uploads/';
    $upload->savePath = empty($dirname)? '': $dirname.'/';

    if(!is_dir($upload->rootPath.$upload->savePath)){
        @mkdir($upload->rootPath.$upload->savePath, 0777, true);
    }
    if(!$info = $upload->upload()) {// 上传错误提示错误信息
        $info = $upload->getError();
    }
    return $info;
}

/**
 * Excel数据转数组
 * @param string $filename  文件的路径
 * @return array excel数据转换后的数组
 */
function excel_to_array($filename){
    import("Org.Util.PHPExcel",LIB_PATH,".php");

    $file_types = explode(".", $filename);
    $file_type  = $file_types[count($file_types) - 1];

    if($file_type == 'xls'){
        $objReader      = PHPExcel_IOFactory::createReader('Excel5');
    }elseif($file_type == 'xlsx'){
        $objReader      = PHPExcel_IOFactory::createReader('Excel2007');
    }
    $objReader->setReadDataOnly(true);
    $objPHPExcel        = $objReader->load($filename);
    $objWorksheet       = $objPHPExcel->getActiveSheet();
    $highestRow         = $objWorksheet->getHighestRow();
    $highestColumn      = $objWorksheet->getHighestColumn();
    $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
    $excelData          = array();
    for($row = 1; $row <= $highestRow; $row++){
        //如果整一行都是空数据则跳过
        $status = 0;
        for($col = 0; $col < $highestColumnIndex; $col++){
            if(!empty((string)(string)$objWorksheet->getCellByColumnAndRow($col, $row)->getValue())){
                $status = 1;
                break;
            }
        }
        if($status == 0) continue;
        for($col = 0; $col < $highestColumnIndex; $col++){
            // if(empty((string)$objWorksheet->getCellByColumnAndRow($col, $row)->getValue())) continue;
            $excelData[$row][] = (string)$objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
        }
    }
    return $excelData;
}

/**
 * 负责人列表获取
 * @param mix $pids  负责人id
 * @return array 负责人信息
 */
function principalList($pids)
{
    $map = array();
    if($pids){
        $map['id'] = array('in', $pids);
    }
    $mod = M('principal',C('DB_PREFIX'),'CySlave');
    return $mod->where($map)->select();
}

/**
 * 游戏列表
 * @param bool $flag  标识 true获取全部  false去除游戏key
 * @return array 游戏列表
 */
function getGameList($flag = false) 
{
    if(!$flag){
        $field = 'id,gameName,createTime,loginStatus,payStatus,updateTime,unit,ratio';
    }elseif($flag === true){
        $field = '*';
    }

    $mod = M('game',C('DB_PREFIX_API'),'CySlave');
    $list = $mod->field($field)->select();
    $arr = array();
    foreach ($list as $key => $value) {
        $arr[$value['id']] = $value;
    }
    return $arr;
}


/**
 * 获取以指定key做键名的数组列表
 * @param string $table 表名
 * @param string $key 键名
 * @param string $prifix 表前缀
 * @param array $map 搜索条件
 * @param string $dbconfig 数据配置
 * @return array 数组列表
 */
function getDataList($table,$_key,$prifix='la_',$map=array(),$dbconfig='') 
{
    $mod = empty($dbconfig) ? M($table,$prifix,'CySlave') : M($table,$prifix,$dbconfig);
    $list = $mod->where($map)->select();
    $arr = array();
    foreach ($list as $key => $value) {
        $arr[$value[$_key]] = $value;
    }
    return $arr;
}

/**
 * 获取以指定多个key做键名的数组列表
 * @param string $table 表名
 * @param array $keys 键名，一维数组
 * @param string $prifix 表前缀
 * @param array $map 搜索条件
 * @param string $dbconfig 数据配置
 * @return array 数组列表
 */
function getDataListForKeys($table, $keys = array(), $prifix = "la_", $map = array(), $dbconfig = "")
{
    $mod    = empty($dbconfig)? M($table, $prifix, "CySlave"): M($table, $prifix, $dbconfig);
    $list   = $mod->where($map)->select();
    $arr    = array();
    foreach ($list as $key => $value) {
        $str = "";
        foreach ($keys as $v) {
            $str .= $value[$v]."_";
        }
        $arr[trim($str, "_")] = $value;
    }
    return $arr;
}

/**
 * 数字格式化
 * @param decimal $num 数值
 * @param bool $rate 比率
 * @param string $format 格式
 * @return string 
 */
function numFormat($num, $rate=false, $format='%.2f')
{
    $rate && $num = $num * 100;
    return sprintf($format, $num) . ($rate ? '%' : '');
}

/**
 * CURL模拟POST请求
 * @param $url
 * @param $params
 * @param int $timeout
 * @return mixed
 */
function curl_post($url, $params, $timeout = 5, $header = array('Content-Type: application/x-www-form-urlencoded'))
{
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
function curl_get($url, $timeout = 5)
{
    $ch = curl_init();
    
    $curl_opt = array(
        CURLOPT_URL => $url,
        CURLOPT_AUTOREFERER => TRUE,
        CURLOPT_RETURNTRANSFER => TRUE,
        CURLOPT_CONNECTTIMEOUT => 0,
        CURLOPT_TIMEOUT => $time
    );

    curl_setopt_array($ch, $curl_opt);
    $contents = curl_exec($ch);
    curl_close($ch);

    return $contents;
}

//设置代理
 function proxy_curl($url,$proxy,$timeout = 180)
{
    $ch = curl_init();
    // 设置 curl 相应属性
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_PROXYPORT, 80);
    curl_setopt ($ch, CURLOPT_PROXY, $proxy);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $returnTransfer = curl_exec($ch);
    curl_close($ch);
    return $returnTransfer;
}

/**
 * 白山云CDN刷新
 * @param string $secretKey secretKey
 * @param string $secretId  secretId 
 * @param string $action 操作类型
 * @param array $urlArr 要刷新的url数组
 * @return mixed
 */
function push_cdn($token,$params)
{
    $url = 'https://purge.qingcdn.com/purge/purge?token='.$token;
    return curl_post($url,json_encode($params),5,array('Content-Type: application/json'));
}

/**
 * 腾讯云CDN刷新
 * @param string $secretKey secretKey
 * @param string $secretId  secretId 
 * @param string $action 操作类型
 * @param array $urlArr 要刷新的url数组
 * @return mixed
 */
function refresh_cdn($secretKey,$secretId,$action,$urlArr){

/*****************参数************************/
/**
  参数名        类型        是否必填        描述
urls            array          是         刷新的目录
**/

/*参数*/
$PRIVATE_PARAMS = $urlArr; /*array(
                'urls.0'=> 'http://ping.cdn.qcloud.com/ping/t0.css',
                'urls.1'=> 'http://ping.cdn.qcloud.com/ping/t1.css',
                );*/

$HttpUrl="cdn.api.qcloud.com";

/*除非有特殊说明，如MultipartUploadVodFile，其它接口都支持GET及POST*/
$HttpMethod="POST";

/*是否https协议，大部分接口都必须为https，只有少部分接口除外（如MultipartUploadVodFile）*/
$isHttps =TRUE;

/*下面这五个参数为所有接口的 公共参数；对于某些接口没有地域概念，则不用传递Region（如DescribeDeals）*/
$COMMON_PARAMS = array(
                'Nonce' => rand(),
                'Timestamp' =>time(NULL),
                'Action' =>$action,
                'SecretId' => $secretId,
                );

/***********************************************************************************/

return CreateRequest($HttpUrl,$HttpMethod,$COMMON_PARAMS,$secretKey, $PRIVATE_PARAMS, $isHttps);
}

function CreateRequest($HttpUrl,$HttpMethod,$COMMON_PARAMS,$secretKey, $PRIVATE_PARAMS, $isHttps)
{
    $FullHttpUrl = $HttpUrl."/v2/index.php";

    /***************对请求参数 按参数名 做字典序升序排列，注意此排序区分大小写*************/
    $ReqParaArray = array_merge($COMMON_PARAMS, $PRIVATE_PARAMS);
    ksort($ReqParaArray);

    /**********************************生成签名原文**********************************
     * 将 请求方法, URI地址,及排序好的请求参数  按照下面格式  拼接在一起, 生成签名原文，此请求中的原文为 
     * GETcvm.api.qcloud.com/v2/index.php?Action=DescribeInstances&Nonce=345122&Region=gz
     * &SecretId=AKIDz8krbsJ5yKBZQ    ·1pn74WFkmLPx3gnPhESA&Timestamp=1408704141
     * &instanceIds.0=qcvm12345&instanceIds.1=qcvm56789
     * ****************************************************************************/
    $SigTxt = $HttpMethod.$FullHttpUrl."?";

    $isFirst = true;
    foreach ($ReqParaArray as $key => $value)
    {
            if (!$isFirst) 
            {
                    $SigTxt = $SigTxt."&";
            }
            $isFirst= false;

            /*拼接签名原文时，如果参数名称中携带_，需要替换成.*/
            if(strpos($key, '_'))
            {
                    $key = str_replace('_', '.', $key);
            }

            $SigTxt=$SigTxt.$key."=".$value;
    }

    /*********************根据签名原文字符串 $SigTxt，生成签名 Signature******************/
    $Signature = base64_encode(hash_hmac('sha1', $SigTxt, $secretKey, true));


    /***************拼接请求串,对于请求参数及签名，需要进行urlencode编码********************/
    $Req = "Signature=".urlencode($Signature);
    foreach ($ReqParaArray as $key => $value)
    {
            $Req=$Req."&".$key."=".urlencode($value);
    }

    /*********************************发送请求********************************/
    if($HttpMethod === 'GET')
    {
            if($isHttps === true)
            {
                    $Req="https://".$FullHttpUrl."?".$Req;
            }
            else
            {
                    $Req="http://".$FullHttpUrl."?".$Req;
            }

            $Rsp = file_get_contents($Req);

    }
    else
    {
            if($isHttps === true)
            {
                    $Rsp= SendPost("https://".$FullHttpUrl,$Req,$isHttps);
            }
            else
            {
                    $Rsp= SendPost("http://".$FullHttpUrl,$Req,$isHttps);
            }
    }

    return json_decode($Rsp,true);
}

function SendPost($FullHttpUrl, $Req, $isHttps)
{

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $Req);

    curl_setopt($ch, CURLOPT_URL, $FullHttpUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    if ($isHttps === true) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,  false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  false);
    }

    $result = curl_exec($ch);

    return $result;
}


/**
 * 导出csv文件
 *
 * @param array $data 数据（如果需要，列标题也包含在这里）
 * @param string $filename 文件名（不含扩展名）
 * @param array $cokey 表格标题（和数据字段名对应） 
 * @param string $to_charset 目标编码
 */
function export_to_csv($data, $filename, $cokey=array(), $to_charset = 'gb2312'){
    ini_set('memory_limit','2048M');
    if (stripos($_SERVER["HTTP_USER_AGENT"], "msie")) {
        $filename = urlencode($filename);
        $filename = str_replace('+', '%20', $filename);
    }
    header("Content-type:text/csv");
    header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
    header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
    header('Expires:0');
    header('Pragma:public');
    $_arr = array();
    foreach ($data as $row) {
        if($cokey){
            foreach(array_keys($cokey) as $ik){
                $tmp = iconv('utf-8', $to_charset.'//TRANSLIT', $row[$ik]);
                $tmp = str_replace("\r\n", "", $tmp);
                $tmp = str_replace("\t", "    ", $tmp);
                $tmp = str_replace("\n", "", $tmp);
                if(is_numeric($tmp) && strlen($tmp) > 11){ //数字超过11位转为字符
                    $_arr[$ik] = '="' . str_replace('"', '""', $tmp) . '"';
                }else{
                    $_arr[$ik] = str_replace('"', '""', $tmp);
                }
            }
            echo join(',', $_arr) . PHP_EOL;
        }else{
            foreach ($row as $key => $col) {
                $col = iconv('utf-8', $to_charset.'//TRANSLIT', $col);
                $col = str_replace("\r\n", "", $col);
                $col = str_replace("\t", "    ", $col);
                $col = str_replace("\n", "", $col);
                if(is_numeric($col) && strlen($col) > 11){
                    $row[$key] = '="' . str_replace('"', '""', $col) . '"';
                }else{
                    $row[$key] = str_replace('"', '""', $col);
                }
            }
            echo join(',', $row) . PHP_EOL;
        }
        unset($row);
    }
    unset($data);

}

//扫描文件
function scanfiles($path){
    global $arrs;
    if(!is_file($path) && !is_dir($path)){
        return false;
    }
    $handle = opendir($path);
    while (false !== ($file = readdir($handle))) {
        if($file != '.' && $file != '..'){
            if(is_dir($path.DS.$file)){
                scanfiles($path.DS.$file);
            }elseif(is_file($path.DS.$file)){
                $arrs[] = $path.DS.$file;
            }
        }
    }
    closedir($handle);
    return $arrs;
}

//处理列表数据
function dealList($agent,$pAgent)
{
    //处理搜索条件
    if (is_array($agent) && in_array("--请选择子包--", $agent)) {
        unset($agent[array_search("--请选择子包--", $agent)]);
    } elseif (is_string($agent) && $agent == "--请选择子包--") {
        $agent     = "";
    } elseif (is_string($agent) && !empty($agent)){
        $agent     = explode(',', $agent);
        if(in_array("--请选择子包--", $agent)){
            unset($agent[array_search("--请选择子包--", $agent)]);
        }
    }

    if (is_array($pAgent) && in_array("--请选择母包--", $pAgent)) {
        unset($pAgent[array_search("--请选择母包--", $pAgent)]);
    } elseif (is_string($pAgent) && $pAgent == "--请选择母包--") {
        $pAgent   = "";
    } elseif (is_string($pAgent) && !empty($pAgent)){
        $pAgent     = explode(',', $pAgent);
        if(in_array("--请选择母包--", $pAgent)){
            unset($pAgent[array_search("--请选择母包--", $pAgent)]);
        } 
    }
    
    return array('agent'=>$agent,'pAgent'=>$pAgent);
}

//处理列表数据
function dealAllList($info = array(), $pinfo = array())
{
    //处理搜索条件
    if (is_array($info) && in_array("--全部--", $info)) {
        unset($info[array_search("--全部--", $info)]);
    } elseif (is_string($info) && $info == "--全部--") {
        $info     = "";
    } elseif (is_string($info) && !empty($info)){
        $info     = explode(',', $info);
        if(in_array("--全部--", $info)){
            unset($info[array_search("--全部--", $info)]);
        }
    }

    if (is_array($pinfo) && in_array("--全部--", $pinfo)) {
        unset($pinfo[array_search("--全部--", $pinfo)]);
    } elseif (is_string($pinfo) && $pinfo == "--全部--") {
        $pinfo   = "";
    } elseif (is_string($pinfo) && !empty($pinfo)){
        $pinfo     = explode(',', $pinfo);
        if(in_array("--全部--", $pinfo)){
            unset($pinfo[array_search("--全部--", $pinfo)]);
        }        
    }

    return array('info'=>$info,'pinfo'=>$pinfo);
}

//格式化时间
function dateformat($num) {
  $hour = floor($num/3600);
  $minute = floor(($num-3600*$hour)/60);
  $second = floor((($num-3600*$hour)-60*$minute)%60);
  return $hour.':'.($minute < 10 ? '0'.$minute : $minute).':'.($second < 10 ? '0'.$second : $second);
 }

//生成HTML按钮
function createBtn($html) {
    if(session('admin.role_id') == 17 || session('admin.role_id') == 25){
        $html = '';
    }
    return $html;
}

function export_to_excel($data, $fileName){
    ini_set('memory_limit','2048M');
    import('Org.Util.PHPExcel');
    $objPHPExcel = new \PHPExcel;
    $objProperties = $objPHPExcel->getProperties();
    $objProperties->setCreator('Author')->setLastModifiedBy(date('Y/m/d H:i:s'))
                ->setTitle('data')->setSubject('remark')->setDescription('description')->setCategory('category');

    if(!empty($data)){
        $objPHPExcel->getActiveSheetIndex(0);
        foreach ($data as $key => $value) {
            $k = $k + 1;

        }
    }
}

function exportExcel($expTitle,$expCellName,$expTableData,$sheetName,$tableHeader='',$defaultWidth=15)
{
    /**文件名称*/
    $xlsTitle = iconv('utf-8', 'gb2312', $expTitle);
    $fileName = $expTitle.date('_YmdHis');
    $cellNum = count($expCellName);

    /** 实例化 */
    ini_set('memory_limit','2048M');

    import("Org.Util.PHPExcel", LIB_PATH, ".php");
    $objPHPExcel = new \PHPExcel();

    $cellName = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ');

    foreach($expTableData as $key => $item) {
        if($key !== 0) $objPHPExcel->createSheet();
        $objPHPExcel->setActiveSheetIndex($key);
        /** 设置工作表名称 */
        $PHPSheet = $objPHPExcel->getActiveSheet($key);
        $PHPSheet->setTitle($sheetName[$key]);

        for($i = 0; $i < $cellNum; $i++)
        {
            /** 垂直居中 */
            $objPHPExcel->setActiveSheetIndex($key)->getStyle($cellName[$i])->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            /** 水平居中 */
            $objPHPExcel->setActiveSheetIndex($key)->getStyle($cellName[$i])->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            /** 设置默认宽度 */
            $objPHPExcel->setActiveSheetIndex($key)->getColumnDimension($cellName[$i])->setWidth($defaultWidth);

            $objPHPExcel->setActiveSheetIndex($key)->setCellValue($cellName[$i].'1', $expCellName[$i][1]);
        }

        /** 写入多行数据 */
        for($i = 0; $i < count($item); $i++)
        {
            for($j = 0; $j < $cellNum; $j++)
            {
              $objPHPExcel->getActiveSheet($key)->setCellValue($cellName[$j].($i+2), $item[$i][$expCellName[$j][0]]);
            }
        }
    }

    // /** 设置第一个工作表为活动工作表 */
    $objPHPExcel->setActiveSheetIndex(0);
    @header('pragma:public');
    @header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'.$xlsTitle.'.xlsx"');
    @header("Content-Disposition:attachment;filename=$fileName.xlsx");
    $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save('php://output');
    exit;
}

/**
 * 获取最近为5倍分钟数的时间
 * @param  [type] $date [description]
 * @return [type]       [description]
 */
function dateHandle($date){
    $years   = strtotime(date('Y-m-d H:00:00',strtotime($date)));
    $minutes = floor(substr($date, 14, 2)/5)*5*60;
    $date    = date('Y-m-d H:i:00',$years+$minutes);

    return $date;
}