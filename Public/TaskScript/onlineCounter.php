<?php
/**
 * 角色在线信息异步入库
 */
require_once dirname(__FILE__) . '/public/Connect.php';
require_once dirname(__FILE__) . '/public/ApiMongoDB.php';

class onlineCounter extends Connect
{
    const DS           = DIRECTORY_SEPARATOR;
    private $sql       = ''; //sql语句
    private $limit     = 1000; //限定2000条
    private $num       = 0; //计数变量
    private $maxTime   = 0; //mongo每次查询记录最大时间
    private $conn      = null; //数据库链接资源
    private $mongo     = null; //mongo资源
    private $status    = true;
    private $log_start = '';    //脚本开始时间
    private $log_end   = '';      //脚本结束时间

    public function __construct()
    {
        /*$sign = $_GET['sign'];
        if (!$this->checkSign($sign)) {
            header('HTTP/1.1 404 Not Found');
            exit();
        }*/
        date_default_timezone_set('PRC');
        error_reporting(0);
        set_time_limit(0);
        ini_set('memory_limit', '1024M');
        $this->conn = parent::dbConnect('127.0.0.1', 'root', 'jlsjlkjethlj79837gg', 'lgame');
        $this->conn->query("set names utf8;");

        $this->log_start = "【onLineStart:".date('Y-m-d H:i:s')."】";
        error_log("\r\n" . $this->log_start, 3, __DIR__ . '/onlineLog' . self::DS . 'run.log');

        $this->onlineCount();

        $this->log_end = "【onLineEnd:".date('Y-m-d H:i:s')."】";
        error_log("\r\n" . $this->log_end, 3, __DIR__ . '/onlineLog' . self::DS . 'run.log');
        mysqli_close($this->conn);
        exit('ok');
    }

    /**
     * 签名验证
     * @param $sign
     * @return bool
     */
    private function checkSign($sign)
    {
        if ($sign != $this->checkSign) {
            return false;
        }

        return true;
    }

    /**
     * 用户在线信息统计
     */
    private function onlineCount()
    {
        //读取mongo的数据
        $data = $this->mongoData('Cy', 'online');
        if ($data === false) {
            return false;
        }

        //开启事务
        $this->conn->autocommit(false);
        foreach ($data as $k => $v) {
            /*$this->arr[$v['agent'].'_'.$v['game_id'].'_'.$v['serverId'].'_'.$v['serverName']]['num'] += 1;*/
            $sql = "insert into lgame.`lg_online_temp`(agent,game_id,imei,imei2,mac,roleId,roleName,serverId,serverName,systemId,systemInfo,type,udid,userCode,userName,createtime,ip,city,province,time) values('{$v['agent']}','{$v['game_id']}','{$v['imei']}','{$v['imei2']}','{$v['mac']}','{$v['roleId']}','{$v['roleName']}','{$v['serverId']}','{$v['serverName']}','{$v['systemId']}','{$v['systemInfo']}','{$v['type']}','{$v['udid']}','{$v['userCode']}','{$v['userName']}','{$v['createtime']}','{$v['ip']}','{$v['city']}','{$v['province']}'," . time() . ");";

            //入库用户在线数据
            $this->sql .= $sql;

            if ($this->num >= $this->limit) {
                //每2000条提交一次
                if (false !== mysqli_multi_query($this->conn, $this->sql)) {
                    $this->sql = '';
                    $this->num = 0;
                    //事务提交
                    $this->conn->commit();
                    //释放结果集
                    while (mysqli_next_result($this->conn) && mysqli_more_results($this->conn)) {

                    }
                } else {
                    $this->status = false;
                    if (!is_dir('onlineLog')) {
                        mkdir(__DIR__ . '/onlineLog', 0755, true);
                    }

                    error_log("\r\n" . '[ ' . date('Ymd H:i:s') . mysqli_error($this->conn) . '] ', 3, __DIR__ . '/onlineLog' . self::DS . 'onlineInserterror.log');
                    continue;
                }
            }

            $this->num++;
        } //end while

        //不够2000条重新提交一次
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
                $this->status = false;
                if (!is_dir('onlineLog')) {
                    mkdir(__DIR__ . '/onlineLog', 0755, true);
                }

                error_log("\r\n" . '[ ' . date('Ymd H:i:s') . mysqli_error($this->conn) . '] ', 3, __DIR__ . '/onlineLog' . self::DS . 'onlineInserterror.log');
                continue;
            }

        }

        if ($this->status === true) {
            //删除mongo，maxTime之前的数据
            $this->delMongo('online', $this->maxTime);
        }

        //事务关闭
        $this->conn->autocommit(true);
    }

    //mongo链接并读取数据
    private function mongoData($db, $table)
    {
        $this->mongo = new ApiMongoDB(array('host' => 'localhost', 'port' => 59817, 'username' => 'CyMongo', 'password' => 'lkjet#$lj10!~!3sji^', 'db' => $db, 'cmd' => '$'));
        $data = $this->mongo->select($table, array(), array(), array('createtime' => 1), $this->limit);

        if ($data) {
        	$maxTime = end($data);
            $this->maxTime = $maxTime['createtime'];
            if (empty($this->maxTime)) {
                return false;
            }

            unset($data);

            //防止出现同时间点的数据漏掉，再查一次最大时间之前的数据
            $map['createtime'] = array($this->mongo->cmd('<=') => $this->maxTime);
            $data              = $this->mongo->select($table, $map, array());
            if (!$data) {
                return false;
            }

            return $data;
        } else {
            return false;
        }
    }

    //入库完成删除原有记录
    private function delMongo($table, $time)
    {
        $map['createtime'] = array($this->mongo->cmd('<=') => $time);
        $res               = $this->mongo->delete($table, $map);
        if ($res) {
            $this->mongo->close();
        }
    }

}

$obj = new onlineCounter();
