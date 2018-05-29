<?php
/**
 * ios ocpa激活回传 5分钟跑一次
 */
require_once dirname(__FILE__) . '/public/Connect.php';
require_once dirname(__FILE__) . '/public/ApiMongoDB.php';


class OcpaIost extends Connect
{
	const DS           = DIRECTORY_SEPARATOR;
	private $conn      = null; //数据库链接资源
	private $sql       = '';   //sql语句
	private $limit     = 100;  //限定100条
	private $num       = 0;    //计数变量
	private $maxTime   = 0;    //mongo每次查询记录最大时间
	private $mongo     = null; //mongo资源
	private $mongoId   = '';   //mongoId
	private $mongoNum  = 0;    //mongo设定的上报条数
	private $reportNum = 0;    //mongo已经上报条数
	private $filename  = '';   //锁文件名
	private $msg       = array();   //msg
	private $log_start = '';   //脚本开始时间
	private $log_end   = '';   //脚本结束时间
	private $adUserId  = '2';   //广点通

	public function __construct()
	{
		date_default_timezone_set('PRC');
		error_reporting(0);
		set_time_limit(0);
		// ini_set('memory_limit', '1024M');
		//判断ocpa锁是否未开启
        $this->filename = __DIR__.'/OcpaLog/ios/2ocpaLock.log';
        if(file_exists($this->filename) && filesize($this->filename) > 0){
            die('die');
        }
		$this->conn = parent::dbConnect('127.0.0.1', 'root', 'jlsjlkjethlj79837gg', 'lgame');

		$this->conn->query("set names utf8;");

		$this->log_start =  "\r\n" ."【OcpaIosStart:".date('Y-m-d H:i:s')."】";
		$this->eventRecord($this->log_start, 'ios', 'OcpaIosRun.log', false, 2, false);

		$res = $this->ocpaReport();

		$this->log_end = "\r\n" . "【OcpaIosEnd:".date('Y-m-d H:i:s')."】";
		$this->eventRecord($this->log_end, 'ios', 'OcpaIosRun.log', false, 2, false);

		mysqli_close($this->conn);

		if ($res === false) {
		    exit(implode('--', $this->msg));
		} else {
		    exit('ok');
		}
	}

	//百度信息流IOS激活上报
	private function baiduReport(&$data,&$reportNum,&$insertData,&$config)
    {
    	foreach ($data as $key => $val) {
	        if (!$val['advter_id'] || !$val['muid']) {
	        	$this->msg[] = 'advter_id or muid is null';
	            return false;
	        }

	        $callback_url = $val['callBackUrl'];
	        $akey         = $config['sign_key'];
			$conv_type    = 'activate';
	        
	        $callback_url = str_replace("{{ATYPE}}", $conv_type, $callback_url);
	        $value        = 0;
	        $callback_url = str_replace("{{AVALUE}}", $value, $callback_url);

	        $signature = md5($callback_url . $akey);

	        $url = $callback_url . '&sign=' . $signature;
	        
	        $res = json_decode(curl_get($url), true);

	        $this->eventRecord("[result]返回结果：" . json_encode($res) . "    [data]请求参数：" . $url . "   " . "传入数据：" . json_encode($val) . "  [msg]报送日志\r\n", 'ios', 'OcpaIosBdxxlReport.log', false, 2, false);

	        if ($res['error_code'] != 0) {
	    		unset($data[$key]);
	    	} elseif($res['ret'] == 0) {
	    		$reportNum ++;
	    		$insertData[] = $val;
	    		if (($reportNum + $this->reportNum) >= $this->mongoNum) {
	    			//超出设定值更新并跳出
            		$this->mongo->update('ocpa', array('reportNum'=>(int)($reportNum + $this->reportNum)),array('_id'=>$this->mongoId));
	    			$this->lockOcpa(($reportNum + $this->reportNum),$this->mongoNum);
	    			break;
	    		}
	    	}
	    }
	    if (($reportNum + $this->reportNum) < $this->mongoNum) {
            //超出设定值更新并跳出
            $this->mongo->update('ocpa', array('reportNum'=>(int)($reportNum + $this->reportNum)),array('_id'=>$this->mongoId));
        }

       if($this->mongo){
           $this->mongo->close();
       }

    }

    //微信IOS激活上报
    private function wxReport(&$data,&$reportNum,&$insertData,&$config)
    {
    	foreach ($data as $key => $val) {
	        if (!$val['advter_id'] || !$val['muid']) {
	        	$this->msg[] = 'advter_id or muid is null';
	            return false;
	        }

	        //拼接参数
	        $request_param = array(
	            'app_type'  => 'IOS',
	            'click_id'  => $val['click_id'],
	            'client_ip' => $val['ip'],
	            'conv_time' => $val['createTime']+300,
	            'muid'      => $val['muid'],
	            'sign_key'  => $config['sign_key'],
	        );
	        $query_string = http_build_query($request_param);

	        //post参数
	        $param = array(
	            'click_id'      => $val['click_id'],
	            'muid'          => $val['muid'],
	            'appid'         => $config['appid'],
	            'conv_time'     => $val['createTime']+300,
	            'client_ip'     => $val['ip'],
	            'encstr'        => md5($query_string),
	            'encver'        => '1.0',
	            'advertiser_id' => $config['advertiser_id'],
	            'app_type'      => 'IOS',
	            'conv_type'     => 'MOBILEAPP_ACTIVITE',
	        );

	        $url = "https://t.gdt.qq.com/conv/app/{$config['appid']}/conv";
	        $res = json_decode($this->curl_post($url,http_build_query($param)), true);
	        $this->eventRecord("[result]返回结果：" . json_encode($res) . "    [data]" . $query_string . '___' . json_encode($request_param) . "   " . "传入数据：" . json_encode($val) . "  [msg]报送日志\r\n", 'ios', 'OcpaIosWxReport.log', false, 2, false);

	        if ($res['ret'] != 0) {
	    		unset($data[$key]);
	    	} elseif($res['ret'] == 0) {
	    		$reportNum ++;
	    		$insertData[] = $val;
	    		if (($reportNum + $this->reportNum) >= $this->mongoNum) {
	    			//超出设定值更新并跳出
            		$this->mongo->update('ocpa', array('reportNum'=>(int)($reportNum + $this->reportNum)),array('_id'=>$this->mongoId));
	    			$this->lockOcpa(($reportNum + $this->reportNum),$this->mongoNum);
	    			break;
	    		}
	    	}

	    }

       if (($reportNum + $this->reportNum) < $this->mongoNum) {
            //超出设定值更新并跳出
            $this->mongo->update('ocpa', array('reportNum'=>(int)($reportNum + $this->reportNum)),array('_id'=>$this->mongoId));
        }


       if($this->mongo){
           $this->mongo->close();
       }
    }

    //广点通IOS激活上报
    private function gdtReport(&$data,&$reportNum,&$insertData,&$config)
    {
    	foreach ($data as $key => $val) {
	    	if (!$val['advter_id'] || !$val['muid']) {
	    		$this->msg[] = 'advter_id or muid is null';
	    	    return false;
	    	}

	    	//组合参数
	    	$param = array(
	    	    'muid'      => $val['muid'],
	    	    'conv_time' => $val['createTime']+300,
	    	    'click_id'  => $val['click_id'],
	    	    'client_ip' => $val['ip'],
	    	);
	    	$query_string  = http_build_query($param);

	    	$page      = 'http://t.gdt.qq.com/conv/app/' . $config['appid'] . '/conv?' . $query_string;
	    	$property  = $config['sign_key'] . '&GET&' . urlencode($page);
	    	$signature = strtolower(md5($property));
	    	//参数加密
	    	$base_data   = $query_string . '&sign=' . urlencode($signature);
	    	$secret_data = $this->simpleXor($base_data, $config['encrypt_key']);

	    	$request_param = array(
	    		'conv_type'     => 'MOBILEAPP_ACTIVITE',
	    		'app_type'      => 'IOS',
	    		'advertiser_id' => $config['advertiser_id'],
	    	);
	    	$attachment = http_build_query($request_param);
	    	$url = "http://t.gdt.qq.com/conv/app/{$config['appid']}/conv?v={$secret_data}&{$attachment}";
	    	$res = json_decode($this->curl_get($url), true);

	    	$this->eventRecord("[result]返回结果：" . json_encode($res) . "    [data]" . $query_string . '___' . json_encode($request_param) . "   " . "传入数据：" . json_encode($val) . "  [msg]报送日志\r\n", 'ios', 'OcpaIosReport.log', false, 2, false);

	    	if ($res['ret'] != 0) {
	    		unset($data[$key]);
	    	} elseif($res['ret'] == 0) {
	    		$reportNum ++;
	    		$insertData[] = $val;
	    		if (($reportNum + $this->reportNum) >= $this->mongoNum) {
	    			//超出设定值更新并跳出
            		$this->mongo->update('ocpa', array('reportNum'=>(int)($reportNum + $this->reportNum)),array('_id'=>$this->mongoId));
	    			$this->lockOcpa(($reportNum + $this->reportNum),$this->mongoNum);
	    			break;
	    		}
	    	}
	    }

        if (($reportNum + $this->reportNum) < $this->mongoNum) {
            //超出设定值更新并跳出
            $this->mongo->update('ocpa', array('reportNum'=>(int)($reportNum + $this->reportNum)),array('_id'=>$this->mongoId));
        }


	    if($this->mongo){
	    	$this->mongo->close();
	    }

    }

	/**
	 * ocpa激活上报
	 */
	private function ocpaReport()
	{
	    //读取mongo的数据
	    $data = $this->mongoData('advter', 'advios');

	    if ($data === false) {
	        return false;
	    }

	    $advter_id  = (int) end($data)['advter_id'];

	    if($result = mysqli_query($this->conn, 'select * from la_events where id = '.$advter_id)){
	    	while($row = mysqli_fetch_array($result, MYSQLI_ASSOC))
	    	{
	    	    $config['appid']         = $row['config_appid'];
	    	    $config['advertiser_id'] = $row['config_advertiser_id'];
	    	    $config['sign_key']      = $row['config_sign_key'];
	    	    $config['encrypt_key']   = $row['config_encrypt_key'];
	    	}
	    	mysqli_free_result($result);
	    }else{
	    	$this->msg[] = 'mysql data is null';
	    	return false;
	    }

	    $reportNum = 0;
	    $insertData = array();

        if ($this->adUserId == '2') {
	    	//广点通
	    	$this->gdtReport($data,$reportNum,$insertData,$config);
	    } elseif($this->adUserId == '10') {
	    	//微信
	    	$this->wxReport($data,$reportNum,$insertData,$config);
	    } elseif($this->adUserId == '14') {
	    	$this->baiduReport($data,$reportNum,$insertData,$config);
	    }

	    //开启事务
	    $this->conn->autocommit(false);
	    $insertTime = time();
	    foreach ($insertData as $k => $v) {
	        $sql = "insert into lgame.`la_ios_ocpa_log`(advter_id,game_id,agent,muid,adUserId,advterType,ip,mac,clickTime,createTime,insertTime) values('{$v['advter_id']}','{$v['game_id']}','{$v['agent']}','{$v['muid']}','{$v['adUserId']}','{$v['advterType']}','{$v['ip']}','{$v['mac']}','{$v['clickTime']}','{$v['createTime']}','{$insertTime}');";

	        $this->sql .= $sql;

	        if ($this->num >= $this->limit) {
	            //每100条提交一次
	            if (false !== mysqli_multi_query($this->conn, $this->sql)) {
	                $this->sql = '';
	                $this->num = 0;
	                //事务提交
	                $this->conn->commit();
	                //释放结果集
	                while (mysqli_next_result($this->conn) && mysqli_more_results($this->conn)) {

	                }
	            } else {
	                if (!is_dir(__DIR__.'/OcpaLog')) {
	                    mkdir(__DIR__ . '/OcpaLog', 0755, true);
	                }

	                error_log("\r\n" . '[ ' . date('Ymd H:i:s') . mysqli_error($this->conn) . '] ', 3, __DIR__ . '/OcpaLog' . self::DS . 'OcpaLogIos.log');
	                continue;
	            }
	        }
	        $this->num++;
	    }

	    //不够100条重新提交一次
	    if ($this->sql != '') {
	        if (false !== mysqli_multi_query($this->conn, $this->sql)) {
	            $this->sql = '';
	            $this->num = 0;
	            //事务提交
	            $this->conn->commit();
	            //释放结果集
	            while (mysqli_next_result($this->conn) && mysqli_more_results($this->conn)) {

	            }
	        } else {
	            if (!is_dir(__DIR__.'/OcpaLog')) {
	                mkdir(__DIR__ . '/OcpaLog', 0755, true);
	            }

	            error_log("\r\n" . '[【' . date('Ymd H:i:s') .'】'. mysqli_error($this->conn) . '] ', 3, __DIR__ . '/OcpaLog' . self::DS . 'OcpaLogIos.log');
	            continue;
	        }

	    }

	    //事务关闭
	    $this->conn->autocommit(true);
	}

	//mongo链接并读取数据
	private function mongoData($db, $table)
	{
	    $this->mongo = new ApiMongoDB(array('host' => '127.0.0.1', 'port' => 59818, 'username' => 'ZgMongoAdvter', 'password' => 'lkjet#$lj10!~!3sji^', 'db' => $db, 'cmd' => '$'));
	    //是否存在前一次的最大时间
	    $filePath = dirname(__FILE__) . "/OcpaLog/ios/2maxTime.log";
	    $map      = array();

	    $config = $this->mongo->select('ocpa',array('status'=>'0','departmentId'=>'2'));
	    $this->mongoNum = $config[0]['Num'];
	    $this->reportNum = $config[0]['reportNum'];
	    $this->mongoId = $config[0]['_id'];
	    $this->mongoNum && $this->limit = ($this->mongoNum - $this->reportNum);

	    if(!empty($config) && ($this->reportNum < $this->mongoNum)){
	    	if ($config[0]['ocpaType'] == 2) {
	    		//微信
	    		$this->adUserId = '10';
	    		$map['adUserId']      = $this->adUserId;
	    		$map['advertiser_id'] = $config[0]['advertiser_id'];
	    		$map['appid']         = $config[0]['appid'];
	    	} elseif ($config[0]['ocpaType'] == 3) {
	    		//百度信息流
	    		$this->adUserId  = '14';
	    		$map['adUserId'] = $this->adUserId;
	    		$map['userid']   = $config[0]['advertiser_id'];
	    		$map['pid']      = $config[0]['appid'];
	    	} else {
	    		//广点通
	    		$map['adUserId']      = $this->adUserId;
	    		$map['advertiser_id'] = $config[0]['advertiser_id'];
	    		$map['appid']         = $config[0]['appid'];
	    	}
	    }else{
	    	$this->lockOcpa($this->reportNum,$this->mongoNum);
	    	die('456');
	    }

	    if (file_exists($filePath)) {
	        $maxTime = file_get_contents($filePath);
	        if ($maxTime < (time()-1800)) {
	        	$maxTime = (time()-1800);
	        }
	        $maxTime && $map['createTime'] = array($this->mongo->cmd('>') => (int) $maxTime);
	    }else{
	    	$map['createTime'] = array($this->mongo->cmd('>') => (int) strtotime(date("Y-m-d")));
	    }

	    $data = $this->mongo->select($table, $map, array(), array('createTime' => 1), $this->limit);

	    if ($data) {
	        $maxTime       = end($data);
	        $this->maxTime = (int) $maxTime['createTime'];

	        if (empty($this->maxTime)) {
	            $this->msg[] = 'time is empty';
	            return false;
	        }

	        //记录最大时间
	        $this->eventRecord($this->maxTime, 'ios', '2maxTime.log');

	        return $data;
	    } else {
	    	$this->msg[] = 'empty data';
	        return false;
	    }
	}

	private function lockOcpa($reportNum,$Num)
	{
		if ($reportNum >= $Num) {
    		//关闭ocpa报送
        	$res = $this->mongo->update('ocpa', array('status'=>'1'),array('_id'=>$this->mongoId));
    		if ($res) {
    			file_put_contents($this->filename, '*');
    		}
    	}
	}

	/**
	 * 写日志
	 * @param  $data        数据
	 * @param  $dir         目录
	 * @param  $filename    文件名
	 * @param   $encrypt    是否要编码
	 * @return
	 */
	private function eventRecord($data, $dir = '', $filename = '', $encrypt = true, $size = 2, $recordid = true)
	{
	    $maxsize = $size * 1024 * 1024;
	    $basedir = dirname(__FILE__) . "/OcpaLog/" . $dir . '/';

	    //目录不存在则创建
	    if (!is_dir($basedir)) {
	        mkdir($basedir, 0777, true);
	    }
	    if (empty($filename)) {
	        $filename = date('Y-m-d') . ".log";
	    }
	    $path = $basedir . $filename;
	    //检测文件大小，默认超过2M则备份文件重新生成 2*1024*1024
	    if (is_file($path) && $maxsize <= filesize($path)) {
	        rename($path, dirname($path) . '/' . time() . '-' . basename($path));
	    }

	    if ($encrypt) {
	        $data = json_encode($data) . "\r\n";
	    }

	    if ($recordid === true) {
	        //覆盖的形式
	        @file_put_contents($path, $data);
	    } else {
	        //以追加的方式写入文件
	        error_log($data, 3, $path);
	    }
	}

	/**
	 * 简单异或加密
	 * @DateTime  2017-07-25T22:45:40+0800
	 * @param     [string]       $base_data   [待加密字符串]
	 * @param     [string]       $encrypt_key [加密key]
	 * @return    [string]       [加密后结果]
	 */
	private function simpleXor($base_data, $encrypt_key)
	{
	    $retval     = '';
	    $source_arr = str_split($base_data);

	    $j = 0;
	    foreach ($source_arr as $ch) {
	        $retval .= chr(ord($ch) ^ ord($encrypt_key[$j]));
	        $j = $j + 1;
	        $j = $j % (strlen($encrypt_key));
	    }

	    return urlencode(base64_encode($retval));
	}

    /**
	 * CURL模拟get请求
	 * @param $url
	 * @param int $timeout
	 * @return mixed
	 */
	private function curl_get($url, $timeout = 15) {
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
	 * CURL模拟POST请求
	 * @param $url
	 * @param $params
	 * @param int $timeout
	 * @param $header
	 * @return mixed
	 */
	private function curl_post($url, $params, $timeout = 5, $header = array("Content-Type: application/x-www-form-urlencoded")) {
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

}

$obj = new OcpaIost();