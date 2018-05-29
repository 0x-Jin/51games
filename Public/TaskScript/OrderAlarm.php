<?php 
//导入类库
require_once(dirname(__FILE__).'/public/Connect.php');
require_once(dirname(__FILE__).'/public/Sms.php');
class OrderAlarm extends Connect
{
	private $conn   = null; //数据库链接资源
	private $sms    = null; //短信资源
	private $mobile = null; //手机号码
	public function __construct()
	{	
		parent::__construct();
        set_time_limit(0);
		//链接数据库
		if(is_null($this->conn)){
			$this->conn = parent::dbConnect('127.0.0.1', 'root', 'jlsjlkjethlj79837gg', 'lgame');
		}
		$this->sms = new Sms();
		$this->mobile = array(
			'18814188325', //上明
			'13828466369', //嘉乐
			'15625179613', //家伦
		);

	}

	public function getUnusualOrder()
	{
		list($usec, $ts) = explode(" ", microtime());

		$dirname = dirname(__FILE__).'/monitor/';
        !file_exists($dirname) && mkdir($dirname, 0777, true);

		//用户当天的充值金额10000元以上报警
		$startTime = strtotime(date('Y-m-d'));
		$sql = "SELECT userName,userCode,SUM(amount) totalSum,COUNT(orderId) AS totalOrder,COUNT(DISTINCT udid) AS totalUdid,COUNT(DISTINCT ip) AS totalIp,`type`,COUNT(DISTINCT `type`) AS typeNum FROM lgame.lg_order 
            WHERE `paymentTime` >= $startTime AND `orderStatus` = 0 GROUP BY userName ORDER BY totalSum DESC";
		$order = $this->conn->query($sql);

		//封禁用户排除校花
		$banSql = "SELECT userName,userCode,SUM(amount) totalSum,COUNT(orderId) AS totalOrder,COUNT(DISTINCT udid) AS totalUdid,COUNT(DISTINCT ip) AS totalIp,`type`,COUNT(DISTINCT `type`) AS typeNum FROM lgame.lg_order 
            WHERE game_id != 112 AND `paymentTime` >= $startTime AND `orderStatus` = 0 GROUP BY userName ORDER BY totalSum DESC";
        $banOrder = $this->conn->query($banSql);

		if(filesize($dirname.date('Y-m',$ts).'.log') > 2097152){
			rename($dirname.date('Y-m',$ts).'.log', $dirname.date('Y-m',$ts).'-'.intval($usec*100000).'.log');
		}

		error_log('[ '.date('Y-m-d H:i:s '.intval($usec*100000), $ts).' ] 内容 ： Alarm Start SQL:'. $sql ." \r\n", 3, $dirname.date('Y-m', $ts).'.log');
		$update = array();
		if($order && $order->num_rows > 0) {
			while($orderInfo = $order->fetch_assoc()) {
				if($orderInfo['totalSum'] < 10000 && (($orderInfo['totalUdid'] < 3 && $orderInfo['totalIp'] < 10) || ($orderInfo['typeNum'] < 2 && $orderInfo['type'] != 2))) continue;
//				if(($orderInfo['totalUdid'] >= 3 || $orderInfo['totalIp'] >= 10) && ($orderInfo['typeNum'] > 1 || $orderInfo['type'] == 2)){
//					//封号
//					$update[] = $orderInfo['userName'];
//				}
				$arr[] = $orderInfo;
			}

			while ($orderInfo = $banOrder->fetch_assoc()) {
                if($orderInfo['totalSum'] < 10000 && (($orderInfo['totalUdid'] < 3 && $orderInfo['totalIp'] < 10) || ($orderInfo['typeNum'] < 2 && $orderInfo['type'] != 2))) continue;
                if(($orderInfo['totalUdid'] >= 3 || $orderInfo['totalIp'] >= 10) && ($orderInfo['typeNum'] > 1 || $orderInfo['type'] == 2)){
                    //封号
                    $update[] = $orderInfo['userName'];
                }
            }
			
			$users = array_column($arr, 'userName');
			sort($users);
			$userName = implode(',', $users);

			//判断今天是否已经报警过
			$dirPath  = dirname(__FILE__).'/orderAlarm/';
			$filePath = $dirPath.'smsLock.log';
			if (file_exists($filePath)) {
				//判断是不是昨天的文件
				$filemdate = date('Y-m-d',filemtime($filePath));
				if ($filemdate != date('Y-m-d') || md5_file($filePath) != md5(trim($userName))) {
					$oldName = explode(',', file_get_contents($filePath));
					file_put_contents($filePath, $userName);

					//设备和IP异常，关闭用户登录
					if(count($update) >= 1 && !empty($update)){
						if($oldName){
							foreach ($oldName as $key => $value) {
								if(in_array($value, $update)) unset($update[array_search($value,$update)]);
							}
						}
						if($update){
							$where = implode("','", $update);
							$upres = $this->conn->query("UPDATE lgame.lg_user SET status = 1 WHERE userName IN('{$where}')");
							//封号的用户写入库
							$sql = $this->banSql($update,$arr);
							if($sql !== false && $upres){
								error_log($sql." \r\n", 3, $dirPath.'_sql'.'.log');
								$this->conn->query($sql);
							}
						}
					}
					foreach($arr as $v){
						if(in_array($v['userName'], $oldName) || $v['totalSum'] < 10000) continue;
						
						$tpl = "【充值预警】{$v['userName']}充值总额：{$v['totalSum']}元，总充值订单数：{$v['totalOrder']}单，充值设备：{$v['totalUdid']}台，充值IP：{$v['totalIp']}个";
						$res = $this->sms->sendSms($this->mobile,$tpl);
						usleep(500000); //睡0.5秒
						error_log('[ '.date('Y-m-d H:i:s '.intval($usec*100000), $ts).' ] 内容 ： '.$tpl. ' 结果：' .(is_array($res) ? json_encode($res) : $res)." \r\n", 3, $dirPath.date('Y-m', $ts).'.log');
					}
					
				}else{
					exit('order alarmed');
				}
				
			} else {
				//创建文件并且报警
				@mkdir($dirPath,0777,true);
				$oldName = explode(',', file_get_contents($filePath));
				file_put_contents($filePath, $userName);
				//设备和IP异常，关闭用户登录
				if(count($update) >= 1 && !empty($update)){
					if($oldName){
						foreach ($oldName as $key => $value) {
							if(in_array($value, $update)) unset($update[array_search($value,$update)]);
						}
					}

					if($update){
						$where = implode("','", $update);
						$upres = $this->conn->query("UPDATE lgame.lg_user SET status = 1 WHERE userName IN('{$where}')");
						//封号的用户写入库
						$sql = $this->banSql($update,$arr);
						if($sql !== false  && $upres){
							error_log($sql." \r\n", 3, $dirPath.'_sql'.'.log');
							$this->conn->query($sql);
						}
					}
				}
				
				foreach($arr as $v){
					if(in_array($v['userName'], $oldName)  || $v['totalSum'] < 10000) continue;

					$tpl = "【充值预警】{$v['userName']}充值总额：{$v['totalSum']}元，总充值订单数：{$v['totalOrder']}单，充值设备：{$v['totalUdid']}台，充值IP：{$v['totalIp']}个";
					$this->sms->sendSms($this->mobile,$tpl);
					usleep(500000); //睡0.5秒
					error_log('[ '.date('Y-m-d H:i:s '.intval($usec*100000), $ts).' ] 内容 ： '.$tpl. ' 结果：' .(is_array($res) ? json_encode($res) : $res)." \r\n", 3, $dirPath.date('Y-m', $ts).'.log');
				}
				
			}

		} else {
			exit('order normal');
		}
	}

	//生成封号sql
	private function banSql(&$banUser,&$alarmUser)
	{
		$user = $this->field_to_key($alarmUser, 'userName');
		$time = time();
		$sql  = "INSERT INTO la_ban_user(userCode,userName,remark,creater,status,createTime) VALUES";
		$sqlArr = array();
		foreach($banUser as $v){
			//userName,userCode,SUM(amount) totalSum,COUNT(orderId) AS totalOrder,COUNT(DISTINCT udid) AS totalUdid,COUNT(DISTINCT ip) AS totalIp
			$sqlArr[] =  "('{$user[$v]['userCode']}','$v','用户名：{$v}充值总额：{$user[$v]['totalSum']}元，总充值订单数：{$user[$v]['totalOrder']}单，充值设备：{$user[$v]['totalUdid']}台，充值IP：{$user[$v]['totalIp']}个,原因：充值".($user[$v]['totalUdid'] >= 3 ? '设备' : 'IP')."数超过设定值。封停时间：".date('Y-m-d H:i:s',$time)."','system','1',{$time})";
		}
		if($sqlArr){
			$sql .= implode(',', $sqlArr);
			return $sql;
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
	private function field_to_key($array,$key)
	{
	    if(count($array) < 1) return false;
	    $arr = array();
	    foreach($array as $k => $v){
	        isset($v[$key]) && $arr[$v[$key]] = $v;
	    }
	    return $arr;
	}


}
$obj = new OrderAlarm();
$obj->getUnusualOrder();
?>