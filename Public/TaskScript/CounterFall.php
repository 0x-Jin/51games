<?php
/**
 * 落地页PV\CLICK数据异步入库
 */
class CountFallIndex
{
	const DS = DIRECTORY_SEPARATOR;
	public $arrs; 				//文件名集合
	public $num = 0; 			//500条sql插一次
	public $sql = '';			//sql语句
	private $conn = null; 		//数据库链接资源
	private $log_start = ''; 	//脚本开始时间
	private $log_end = ''; 		//脚本结束时间
	public function __construct()
	{
		date_default_timezone_set('PRC');
		error_reporting(0);
        ini_set('memory_limit','1024M'); 
        set_time_limit(0);
		$this->conn = $this->connect('127.0.0.1', 'root', 'jlsjlkjethlj79837gg', 'lgame');
        $this->conn->query("set names utf8;");
        $this->log_start = "【Fallstart:".date('Y-m-d H:i:s')."】";
        error_log($this->log_start."\r\n", 3, __DIR__.self::DS."Fallrunlog.log");

		$this->fallCounter(__DIR__.self::DS.'fallCount'.self::DS.'Open'); //OPEN
		$this->fallCounter(__DIR__.self::DS.'fallCount'.self::DS.'Download'); //DOWNLOAD

		$this->log_end = "【Fallend:".date('Y-m-d H:i:s')."】";
		mysqli_close($this->conn);
        error_log($this->log_end."\r\n", 3, __DIR__.self::DS."Fallrunlog.log");
		exit('ok');
	}

	private function connect($host, $user, $password, $db){
        $conn = new mysqli($host, $user, $password, $db);
        if(mysqli_connect_errno()){
            die("Error:(".mysqli_connect_errno().")".mysqli_connect_error());
        }
        return $conn;
    }

	//扫描文件
	private function scanfiles($path){
		if(!is_file($path) && !is_dir($path)){
			return false;
		}
		$handle = opendir($path);
		while (false !== ($file = readdir($handle))) {
			if($file != '.' && $file != '..'){
				if(is_dir($path.self::DS.$file)){
					$this->scanfiles($path.self::DS.$file);
				}elseif(is_file($path.self::DS.$file)){
					$this->arrs[] = $path.self::DS.$file;
				}
			}
		}
		closedir($handle);
		return $this->arrs;
	}

	/**
	 * 落地页PV、CLICK统计 
	 */
	public function fallCounter($path)
	{
		//判断备份目录是否存在
		$backup_path = dirname($path).self::DS.'FallBackUp'.self::DS.basename($path);
		!is_dir($backup_path) && @mkdir($backup_path,0777,true);

		//分开读取，先读取Open,再读取Download
		$files = $this->scanfiles($path);
		if(empty($files)) return false;
		//开启事务
		$this->conn->autocommit(false);
		foreach ($files as $filename) {
			$newname = str_replace('log', 'backup', $filename);
			if(!file_exists($filename)){
				continue;
			}
			
			$fp = fopen($filename,'r');
			if(!is_resource($fp)){
                continue;
            }
            while(!feof($fp)){
                $ts = fgets($fp); //每次读取一行
				if(empty($ts) || !strpos($ts, 'insert')){
					continue;
				}
				$this->sql .= substr($ts,strpos($ts, 'insert'));

                if($this->num >= 500){ //每500条提交一次
                	if(false !== mysqli_multi_query($this->conn,$this->sql)){
                		$this->sql = '';
                		$this->num = 0;
                		//事务提交
                		$this->conn->commit();
                		//释放结果集
	            		while(mysqli_next_result($this->conn) && mysqli_more_results($this->conn)){
	            			
	            		}
                	}else{
                        error_log("\r\n".'[ '.date('Ymd H:i:s').' '.$filename.' '.mysqli_error($this->conn).'] ', 3, $backup_path.self::DS.'fallInserterror.log');
                		continue;
                	}
                }
				                
                $this->num ++;
            }//end while
            fclose($fp);
            //不够500条重新提交一次
            if($this->sql != ''){
            	if(false !== mysqli_multi_query($this->conn,$this->sql)){
            		$this->sql = '';
            		$this->num = 0;
            		//事务提交
                	$this->conn->commit();
            		//释放结果集
            		while(mysqli_next_result($this->conn) && mysqli_more_results($this->conn)){

            		}
            	}else{
                    error_log("\r\n".'[ '.date('Ymd H:i:s').' '.$filename.' '.mysqli_error($this->conn).'] ', 3, $backup_path.self::DS.'fallInserterror.log');
            		continue;
            	}

            }
			//文件备份
			if(file_exists($backup_path.self::DS.basename($newname))){
				//改名
				rename($backup_path.self::DS.basename($newname), $backup_path.self::DS.date('Y-m-dH:i:s').'-'.basename($newname));
			}
			if(!rename($filename, $backup_path.self::DS.basename($newname))) {
               error_log("\r\n".'[ '.date('Ymd H:i:s').' '.$filename.' '.$newname.'] move failure', 3, $backup_path.self::DS.'fallerror.log');
			}

		}
		//事务关闭
		$this->conn->autocommit(true);
		//清除文件集合
		unset($this->arrs);
	}

}

$obj = new CountFallIndex();