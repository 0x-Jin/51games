<?php
/**
 * 清除8天前投放mongo过期数据，超过8天的
 */
require_once(dirname(__FILE__).'/public/ApiMongoDB.php');

class clearMongo
{
	protected $res = array();
	public function __construct()
	{
		date_default_timezone_set('PRC');
		error_reporting(0);
        set_time_limit(0);
        ini_set('memory_limit','1024M');

		$this->clearData();
		exit(json_encode($this->res));
	}

	/**
	 * 用户在线信息统计 
	 */
	private function clearData()
	{
		//读取mongo的数据
        $clickStart = strtotime(date('Y-m-d').' -8 day');
        //删除IOS点击数据
		$this->mongoData('advter','advios',$clickStart);
		//删除安卓点击数据
		$this->mongoData('advter','advand',$clickStart);
		

        $start = strtotime(date('Y-m-d').' -15 day');
		//删除IOS的上报数据
		$this->mongoData('advter','advIosReport',$start);
		//删除安卓上报数据
		$this->mongoData('advter','advAndReport',$start);
		return true;
	}

	//mongo链接并清除数据
	private function mongoData($db,$table,$start)
	{
		$mongo = new ApiMongoDB(array('host' => 'localhost', 'port' => 59818, 'username' => 'ZgMongoAdvter', 'password' => 'lkjet#$lj10!~!3sji^', 'db' => $db, 'cmd' => '$'));
		$map['createTime'] = array($mongo->cmd('<')=>$start);
		$this->res['map'][] = $map; 
		$this->res['res'][] = $mongo->delete($table,$map);
		$mongo->close();
		return true;
	}

}

$obj = new clearMongo();