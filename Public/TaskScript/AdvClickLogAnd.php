<?php
/**
 * 安卓点击数据异步入库
 */
require_once dirname(__FILE__) . '/public/Connect.php';
require_once dirname(__FILE__) . '/public/ApiMongoDB.php';

class AdvClickLogAnd extends Connect
{
    const DS           = DIRECTORY_SEPARATOR;
    private $sql       = ''; //sql语句
    private $limit     = 10000; //限定10000条
    private $num       = 0; //计数变量
    private $maxTime   = 0; //mongo每次查询记录最大时间
    private $conn      = null; //数据库链接资源
    private $mongo     = null; //mongo资源
    private $status    = true;
    private $log_start = '';    //脚本开始时间
    private $log_end   = '';      //脚本结束时间

    public function __construct()
    {

        date_default_timezone_set('PRC');
        error_reporting(0);
        set_time_limit(0);
        // ini_set('memory_limit', '1024M');
        $this->conn = parent::dbConnect('127.0.0.1', 'root', 'jlsjlkjethlj79837gg', 'lgame');
        $this->conn->query("set names utf8;");

        $this->log_start = "\r\n" ."【AdvClickAndStart:".date('Y-m-d H:i:s')."】";
        $this->eventRecord($this->log_start, 'and', 'AdvClickAndRun.log', false, 2, false);

        $res = $this->advterClick();

        $this->log_end = "\r\n" ."【AdvClickAndEnd:".date('Y-m-d H:i:s')."】";
        $this->eventRecord($this->log_end, 'and', 'AdvClickAndRun.log', false, 2, false);
        
        mysqli_close($this->conn);
        if ($res === false) {
            exit('error');
        } else {
            exit('ok');
        }
    }


    /**
     * 点击数据入库
     */
    private function advterClick()
    {
        //读取mongo的数据
        $data = $this->mongoData('advter', 'advand');
        if($this->mongo){
            $this->mongo->close();
        }
        if ($data === false) {
            return false;
        }

        //开启事务
        $this->conn->autocommit(false);
        foreach ($data as $k => $v) {
            $sql = "insert into lgame.`la_and_click_log`(game_id,agent,muid,adUserId,advterType,ip,mac,clickTime,createTime) values('{$v['game_id']}','{$v['agent']}','{$v['muid']}','{$v['adUserId']}','{$v['advterType']}','{$v['ip']}','{$v['mac']}','{$v['clickTime']}','{$v['createTime']}');";
            $this->sql .= $sql;

            if ($this->num >= $this->limit) {
                //每10000条提交一次
                if (false !== mysqli_multi_query($this->conn, $this->sql)) {
                    $this->sql = '';
                    $this->num = 0;
                    //事务提交
                    $this->conn->commit();
                    //释放结果集
                    while (mysqli_next_result($this->conn) && mysqli_more_results($this->conn)) {

                    }
                } else {
                    if (!is_dir(__DIR__ . '/AdvClickLog')) {
                        mkdir(__DIR__ . '/AdvClickLog', 0755, true);
                    }

                    error_log("\r\n" . '[ 【' . date('Ymd H:i:s') .'】'.   mysqli_error($this->conn) . '] ', 3, __DIR__ . '/AdvClickLog' . self::DS . 'AdvClickLogAnd.log');
                    continue;
                }
            }

            $this->num++;
        } //end while

        //不够10000条重新提交一次
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
                if (!is_dir(__DIR__ . '/AdvClickLog')) {
                    mkdir(__DIR__ . '/AdvClickLog', 0755, true);
                }

                error_log("\r\n" . '[ ' . date('Ymd H:i:s') . mysqli_error($this->conn) . '] ', 3, __DIR__ . '/AdvClickLog' . self::DS . 'AdvClickLogAnd.log');
                continue;
            }

        }

        //事务关闭
        $this->conn->autocommit(true);
    }

    //mongo链接并读取数据
    private function mongoData($db, $table)
    {
        $this->mongo = new ApiMongoDB(array('host' => 'localhost', 'port' => 59818, 'username' => 'ZgMongoAdvter', 'password' => 'lkjet#$lj10!~!3sji^', 'db' => $db, 'cmd' => '$'));
        //是否存在前一次的最大时间
        $filePath = dirname(__FILE__) . "/AdvClickLog/and/maxTime.log";
        $map      = array();

        if (file_exists($filePath)) {
            $maxTime = file_get_contents($filePath);
            if ($maxTime < time()-1800) {
                $maxTime = time()-1800;
            }
            $maxTime && $map['createTime'] = array($this->mongo->cmd('>') => (int) $maxTime);
        } else {
            $map['createTime'] = array($this->mongo->cmd('>') => (int) time()-1800);
        }

        $data = $this->mongo->select($table, $map, array(), array('createTime' => 1), $this->limit);

        if ($data) {
            $maxTime       = end($data);
            $this->maxTime = (int) $maxTime['createTime'];

            if (empty($this->maxTime)) {
                return false;
            }

            //记录最大时间
            $this->eventRecord($this->maxTime, 'and', 'maxTime.log');

            return $data;
        } else {
            return false;
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
        $basedir = dirname(__FILE__) . "/AdvClickLog/" . $dir . '/';

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

}

$obj = new AdvClickLogAnd();
