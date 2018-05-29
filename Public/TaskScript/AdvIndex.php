<?php
/**
 * 广告类统计脚本
 */
class AdvIndex
{
    const DS           = DIRECTORY_SEPARATOR;
    private $advterArr = null; // 广告商数组
    public function __construct()
    {
        date_default_timezone_set('PRC');
        error_reporting(0);
        //set_time_limit(0);
        $this->advterArr = array(
            1  => 'cy', // 创娱
            2  => 'gdt', // 广点通
            3  => 'aqy', // 爱奇艺
            4  => 'zht', // 智汇推
            5  => 'bdss', // 百度搜索
            6  => 'jrtt', // 今日头条
            7  => 'uctt', // UC头条
            8  => 'dqd', // 懂球帝
            9  => 'fhxw', // 凤凰新闻
            10 => 'wx', // 微信
            11 => 'xlfy', // 新浪扶翼
            12 => 'smss', // 神马搜索
            13 => 'sgss', // 搜狗搜索
            14 => 'bdxxl', // 百度信息流
            15 => 'taptap', // taptap
            16 => 'ASO', // ASO
            17 => 'shhs', // 搜狐汇算
            36 => 'cx', // 畅效
            29 => 'ks', // 快手
            70 => 'lz', // 来赚
            67 => 'YYQ', // YYQ
        );
        $this->fallCount();
    }

    /**
     * 落地页PV、CLICK统计
     *
     * @param type 1:打开 2:点击下载
     * @param ver 渠道号
     * @param appid 游戏id
     */
    public function fallCount()
    {
        $data = $this->dealData($_REQUEST);
        if ($data['type'] == '1') {
            if ($data['ver'] && $data['advid'] && !empty(intval($data['appid'])) && $this->checkparam($data['ver'], 'ver')) {
                $ip                    = $this->get_ip_address();
                $newdata['agent']      = $data['ver'];
                $newdata['advid']      = $data['advid'];
                $newdata['appid']      = $data['appid'];
                $newdata['requestIp']  = strlen($ip) > 15 ? '0.0.0.0' : $ip;
                $newdata['url']        = $data['url'];
                $newdata['cmtype']     = $data['cmtype'] ?: '';
                $newdata['createTime'] = time();
                $this->log_save($newdata, 'sql', 'fall_open_log', 'fallOpenLog.log', 'fallCount' . self::DS . 'Open');
            }
        } elseif ($data['type'] == '2') {
            if ($data['ver'] && $data['advid'] && $this->checkparam($data['downloadUrl']) && !empty(intval($data['appid'])) && $this->checkparam($data['ver'], 'ver')) {
                $ip                     = $this->get_ip_address();
                $newdata['agent']       = $data['ver'];
                $newdata['advid']       = $data['advid'];
                $newdata['appid']       = $data['appid'];
                $newdata['requestIp']   = strlen($ip) > 15 ? '0.0.0.0' : $ip;
                $newdata['downloadUrl'] = $data['downloadUrl'];
                $newdata['url']         = $data['url'];
                $newdata['cmtype']      = $data['cmtype'] ?: '';
                $newdata['createTime']  = time();
                $this->log_save($newdata, 'sql', 'fall_download_log', 'fallDownLoadLog.log', 'fallCount' . self::DS . 'Download');
            }
            $this->fallClick($data);
        }
        exit($data['callback'] . '(' . json_encode(array('ret' => 0, 'msg' => 'success')) . ')');
    }

    /**
     * 记录日志
     *
     * @param mixed $data 信息,type=info时为字符串类型，type=sql为数组，数组的键名与表字段名一致
     * @param string $type 类型-info：本地调试、sql：异步入库
     * @param string $tabletype 表类型-login_log，init_log
     * @param string $filename 日志文件名
     * @param string $dir 日志目录
     * @return boolean ture/false
     */
    private function log_save($data, $type = 'info', $tabletype = '', $filename = '', $dir = '')
    {
        if ($type == 'info') {
            $config = array(
                'path'      => dirname(__FILE__) . self::DS . ($dir ? $dir . self::DS : 'debug' . self::DS . date('Ym') . self::DS),
                'file_size' => 2097152, // 大于2M重命名
            );

            !is_dir($config['path']) && mkdir($config['path'], 0777, true);
            $destination = $config['path'] . (empty($filename) ? date('y-m-d') . '.log' : $filename);

            if (is_file($destination) && floor($config['file_size']) <= filesize($destination)) {
                rename($destination, dirname($destination) . self::DS . $_SERVER['REQUEST_TIME'] . '-' . basename($destination));
            }

            is_array($data) && $data = json_encode($data);
            error_log('【' . date('Y-m-d H:i:s') . '】' . $data . "\r\n", 3, $destination);
        } else {
            $sql = $this->build_sql($data, $tabletype);
            if ($sql === false) {
                return false;
            }
            $this->log_save($sql, 'info', '', $filename, $dir);
        }
        return true;
    }

    /**
     * 创建SQL
     *
     * @param array $data 数据
     * @param string $type 表类型
     * @return string $sql 组装的sql语句
     */
    private function build_sql($data, $type)
    {
        $tab_map = array(
            'fall_open_log'     => array(
                'table' => 'lgame.la_fall_open_log_2018',
                'field' => array(
                    'agent',
                    'advid',
                    'appid',
                    'requestIp',
                    'url',
                    'cmtype',
                    'createTime',
                ),
            ),
            'fall_download_log' => array(
                'table' => 'lgame.la_fall_download_log_2018',
                'field' => array(
                    'agent',
                    'advid',
                    'appid',
                    'downloadUrl',
                    'requestIp',
                    'url',
                    'cmtype',
                    'createTime',
                ),
            ),
        );
        if (!in_array($type, array_keys($tab_map))) {
            return false;
        }
        // 字段过滤
        foreach ($tab_map[$type]['field'] as $value) {
            if (isset($data[$value])) {
                $arr[$value] = $data[$value];
            }
        }
        $table  = $tab_map[$type]['table'];
        $field  = implode(',', array_keys($arr));
        $values = implode("','", $arr);
        $sql    = "insert into {$table}({$field}) values('{$values}');";
        return $sql;
    }

    private function checkparam($param, $type = 'url')
    {
        if ($type == 'url') {
            if (preg_match("/^http(s?):\/\/(?:[A-za-z0-9-]+\.)+[A-za-z]{2,4}(?:[\/\?#][\/=\?%\-&~`@[\]\':+!\.#\w]*)?$/", $param)) {
                return true;
            }
        } else {
            if (preg_match("/^(\w-*)+$/", $param)) {
                return true;
            }
        }
        return false;
    }

    // mongo插入
    private function insert($data)
    {
        if (empty($data)) {
            return false;
        }

        $mongo = new ApiMongoDB(array(
            'host'     => '127.0.0.1',
            'port'     => 59818,
            'username' => 'ZgMongoAdvter',
            'password' => 'lkjet#$lj10!~!3sji^',
            'db'       => 'advter',
            'cmd'      => '$',
        ));
        $res = $mongo->insert("advios", $data);
        $mongo->close();
        if ($res) {
            return true;
        }
        return false;
    }

    // 落地页点击下载监控
    private function fallClick(&$data)
    {
        if ($data['events'] && $data['gf'] && $data['adUser'] && $data['appleLink']) {
            // 判断广告链接状态是否未开启
            $filename = __DIR__ . self::DS . 'advterLock' . self::DS . $data['events'] . 'Lock.log';
            if (!file_exists($filename) || filesize($filename) == 0) {
                $ip        = $this->get_ip_address();
                $game_info = explode('_', $data['gf']);
                $time      = time();

                require_once dirname(__FILE__) . '/public/ApiMongoDB.php';
                $insert = array(
                    'advter_id'   => $data['events'],
                    'game_id'     => $game_info[1],
                    'agent'       => $game_info[0],
                    'muid'        => strtolower(md5($ip)),
                    'adUserId'    => $data['adUser'],
                    'os'          => 2,
                    'advterType'  => $this->advterArr[$data['adUser']],
                    'clickTime'   => $time,
                    'createTime'  => $time,
                    'ip'          => strlen($ip) > 15 ? '0.0.0.0' : $ip,
                    'ua'          => $_SERVER['HTTP_USER_AGENT'],
                    'callBackUrl' => $_SERVER['REQUEST_URI'],
                    'status'      => 1,
                );
                $res = $this->insert($insert);
                if ($res === true) {
                    header('Location:' . $data['appleLink']);
                }
            }
        }
    }

    /**
     * 获取IP地址
     *
     * @return string
     */
    private function get_ip_address()
    {
        if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
            $ip = $_SERVER["HTTP_CLIENT_IP"];
        } elseif (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        } elseif (!empty($_SERVER["REMOTE_ADDR"])) {
            $ip = $_SERVER["REMOTE_ADDR"];
        } else {
            $ip = "";
        }
        return $ip;
    }

    /**
     * 处理cmtype解密
     * @AuthorHTL
     * @DateTime  2018-04-10T15:20:31+0800
     * @param     [type]                   $data [description]
     * @return    [type]                         [description]
     */
    private function dealData($data)
    {
        if (!$data['cmtype']) {
            return $data;
        }
        
        $cmtype = str_replace('_', '%', $data['cmtype']);
        $newStr = '';
        for ($i=0; $i < strlen($cmtype); $i++) {
            if ($cmtype[$i] != '%') {
                if (is_numeric($cmtype[$i])) {
                    $newStr .= $this->dealNum($cmtype[$i]);
                } else {
                    $newStr .= $this->dealStr($cmtype[$i]);
                }
            } else {
                $newStr .= $cmtype[$i];
            }
        }
        $data['cmtype'] = urldecode($newStr);
        return $data;
    }

    /**
     * 处理字母
     * @AuthorHTL
     * @DateTime  2018-04-10T15:21:40+0800
     * @param     [type]                   $str [description]
     * @return    [type]                        [description]
     */
    private function dealStr($str) 
    {
        $str = strtolower($str);
        return chr(ord($str) - 5);
    }

    /**
     * 处理数字
     * @AuthorHTL
     * @DateTime  2018-04-10T15:21:51+0800
     * @param     [type]                   $num [description]
     * @return    [type]                        [description]
     */
    private function dealNum($num) 
    {
        $num = ($num - 3);
        if ($num < 0) {
            $num = (10 + $num);
        }
        return $num;
    }
}

$obj = new AdvIndex();
