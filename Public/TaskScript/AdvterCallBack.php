<?php 
/**
 * 推广广告激活匹配并回调
 */
require_once(dirname(__FILE__).'/public/Connect.php');
class AdvterCallBack extends Connect
{

	public  $sql = '';			  //sql语句
	private $conn = null; 		  //数据库链接资源
	private $start_time = 0;      //开始时间
	private $end_time = 0;        //结束时间
	const   ACTIVITY_NUM = 2000;  // 第一次注册条数
	public function __construct()
	{
		date_default_timezone_set('PRC');
		error_reporting(0);
        ini_set('memory_limit','1024M'); 
        set_time_limit(0);
        //报送7天内点击的数据
        $this->start_time = strtotime(date('Y-m-d').' -7 day');
        $this->end_time   = time();
		$this->conn = $this->connect('127.0.0.1', 'root', 'jlsjlkjethlj79837gg', 'lgame');
        $this->conn->query("set names utf8;");
	}

	private function connect($host, $user, $password, $db)
	{
        $conn = new mysqli($host, $user, $password, $db);
        if(mysqli_connect_errno()){
            die("Error:(".mysqli_connect_errno().")".mysqli_connect_error());
        }
        return $conn;
    }

    private function adCallBack()
    {
    	$lastId = @file_get_contents(dirname(__FILE__).'/ChannelClick/LastId.log');
    	empty($lastId) && $lastId = 0;
    	//激活的数据
    	$res    = $this->conn->query("SELECT a.id,b.agent,b.uniqueId,b.callBackUrl,b.ip,b.os FROM lg_device_game a LEFT JOIN (SELECT * FROM la_advter_record FORCE INDEX(clickTime) WHERE clickTime>={$this->start_time} AND clickTime<={$this->end_time}) b ON a.agent=b.agent AND (b.uniqueId=MD5(a.imei) OR b.uniqueId=a.idfa) WHERE a.id>{$lastId} LIMIT ".self::ACTIVITY_NUM);
    	if($res->num_rows < 1) return false;
    	$arr = array();
        if(is_object($res)){
            while ($row = $res->fetch_assoc()) {
                $last_id = $row['id'];
                if($row['callBackUrl'] && $row['agent']) {
                	$arr[] = $row;
                	$returnTransfer = $this->curl_get($row['callBackUrl']);
                	$arr['returnTransfer'] = $returnTransfer;
                }
            }
            //记录最后读取的id
            @file_put_contents(dirname(__FILE__).'/ChannelClick/LastId.log',$last_id);
            //将报送成功的数据存文件
            if(count($arr)>0){
	            $this->eventRecord('jrtt',$arr,'CallBackResult');
            }
        }
    	
    }

    /**
     * 写日志
     * @param  $channelType 广告类型
     * @param  $data        数据
     * @param  $dir         目录
     * @param  $filename    文件名
     * @param   $encrypt    是否要编码
     * @return 
     */
    private function eventRecord($channelType, $data, $dir = '', $filename = '', $encrypt = true, $size = 2, $recordid = false){
        $maxsize = $size * 1024 * 1024;
        $basedir = dirname(__FILE__)."/ChannelClick/".$channelType.'/'.$dir.'/';

        //目录不存在则创建
        if(!is_dir($basedir)){
            mkdir($basedir, 0777, true);
        }
        if(empty($filename)){
            $filename = date('Y-m-d').".log";
        }
        $path = $basedir.$filename;
        //检测文件大小，默认超过2M则备份文件重新生成 2*1024*1024
        if(is_file($path) && $maxsize <= filesize($path) )
              rename($path,dirname($path).'/'.time().'-'.basename($path));
        if($encrypt){
            $data = json_encode($data)."\r\n";
        }

        if($recordid === true){
            //覆盖的形式
            @file_put_contents($path, $data);
        }else{
            //以追加的方式写入文件
            error_log($data, 3, $path);
        }
    }

    public function run()
    {
    	$this->adCallBack();
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

}
$obj = new AdvterCallBack();
$obj->run();

