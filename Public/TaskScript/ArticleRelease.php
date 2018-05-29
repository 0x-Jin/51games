<?php 
/**
 * 游戏官网资讯自动发布  一小时更新一次
 */

require_once(dirname(__FILE__).'/public/Connect.php');

class ArticleRelease extends Connect
{
	private $conn = null; //数据库链接资源
	public function __construct()
	{
		parent::__construct();
		set_time_limit(0);
		//链接数据库
		if(is_null($this->conn)){
			$this->conn = parent::dbConnect('127.0.0.1', 'root', 'jlsjlkjethlj79837gg', 'website');
		}
	}
    
    /**
	 * 获取文章
	 */
	public function getArticle()
	{
		$startTime = strtotime(date('Y-m-d H:00:00').' -1 hour');
		$endTime   = strtotime(date('Y-m-d H:00:00'));
		//获取官网缩写
		$home_sql = "SELECT id,abbr FROM `gw_home`";
		$home = $this->conn->query($home_sql);
		$homeArr = array();
		foreach ($home as $key => $value) {
			$homeArr[$value['id']] = $value['abbr'];
		}

		//一个小时内的所有文章
		$sql = "SELECT id,home_id FROM `gw_article` WHERE releaseTime >= {$startTime} AND releaseTime <= {$endTime}";
		$row = $this->conn->query($sql);
		if($row && $row->num_rows > 0) {
			$url = 'http://adv.cmgcwl.cn/Admin/Web/read.html?cache=1&';
			foreach ($row as $key => $val) {
				$this->curl_get($url."&abbr={$homeArr[$val['home_id']]}&id={$val['id']}");
			}
			die('exec ok');
		}else{
			die('ok');
		}
	}

	/**
	 * CURL模拟GET请求
	 * @param $url
	 * @param int $timeout
	 * @return mixed
	 */
	protected function curl_get($url, $timeout = 15) {
	    $curl_opt = array(
	        CURLOPT_URL => $url,
	        CURLOPT_AUTOREFERER => TRUE,
	        CURLOPT_RETURNTRANSFER => TRUE,
	        CURLOPT_CONNECTTIMEOUT => 0,
	        CURLOPT_TIMEOUT => $time
	    );

	    $ch = curl_init();
	    curl_setopt_array($ch, $curl_opt);
	    $contents = curl_exec($ch);
	    curl_close($ch);


	    return $contents;
	}

}

$a = new ArticleRelease();
$a->getArticle();
