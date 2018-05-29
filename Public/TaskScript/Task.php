<?php
class Task
{
    private $conn; //数据库链接资源
    public $startTime; //查询开始时间
    public $endTime; //查询结束时间
    public $map;
    public $count;
    public $mh;

    private $log_start;
    private $log_end;

    const REG_NUM     = 2000; // 第一次注册条数
    const INSTALL_NUM = 3000; // 第一次打开条数
    const LOGIN_NUM   = 3000; // 每次登录条数
    const STARTUP_NUM = 3000; // 每次打开条数
    const PAY_NUM     = 2000; // 支付条数

    public function __construct()
    {
        error_reporting(0);
        date_default_timezone_set('PRC');
        ini_set('memory_limit', '1024M');
        set_time_limit(0);

        $this->startTime = strtotime(date('Y-m-d') . '-1 day');
        $this->endTime   = strtotime(date('Y-m-d') . '+1 day');
        $this->map       = array();
        $this->count     = 0;
        $this->mh        = curl_multi_init();

        $this->conn = $this->connect('127.0.0.1', 'root', 'jlsjlkjethlj79837gg', 'lgame');
        $this->conn->query("set names utf8;");
        $this->log_start = "【start:" . date('Y-m-d H:i:s') . "】";
        error_log($this->log_start . "\r\n", 3, __DIR__ . "/runlog.log");

    }

    public function __destruct()
    {
        $this->conn->colse();

    }

    private function connect($host, $user, $password, $db)
    {
        $conn = new mysqli($host, $user, $password, $db);
        if (mysqli_connect_errno()) {
            die("Error:(" . mysqli_connect_errno() . ")" . mysqli_connect_error());
        }
        return $conn;
    }

    //首次打开数据报送
    public function openData()
    {
        $lastId                   = @file_get_contents(dirname(__FILE__) . '/reyun/' . 'openData/lastId/openDataLastId.log');
        empty($lastId) && $lastId = 0;
        $res                      = $this->conn->query("select id,idfa,mac,idfv,agent,game_id,createTime,ip,systemInfo from ry_openreport where id > {$lastId} and createTime > {$this->startTime} and createTime < {$this->endTime} and type=2  limit " . self::INSTALL_NUM);
        if ($res->num_rows < 1) {
            return false;
        }

        if (is_object($res)) {
            while ($row = $res->fetch_assoc()) {
                $last_id        = $row['id'];
                list($ch, $url) = $this->reyun($row, 1, true);
                $this->addcurl($this->mh, $ch, $this->count, $this->map, $row, $url, 'reyun', 'openData');
            }
            //不够500再提交一次
            if ($this->count < 500 && !empty($this->count)) {
                $this->addcurl($this->mh, $ch, $this->count, $this->map, $row, $url, 'reyun', 'openData', true);
            }
            //记录最后读取的id
            $this->eventRecord('openData', $last_id, 'openDataLastId.log', 'lastId/', false, 2, true);
        }
    }

    //首次注册数据报送
    public function registerData()
    {
        $lastId                   = @file_get_contents(dirname(__FILE__) . '/reyun/' . 'registerData/lastId/registerDataLastId.log');
        empty($lastId) && $lastId = 0;
        $res                      = $this->conn->query("select id,userCode,idfa,idfv,agent,game_id,createTime,ip from ry_registreport where id > {$lastId} and createTime > {$this->startTime} and createTime < {$this->endTime} and type=2  limit " . self::REG_NUM);
        if ($res->num_rows < 1) {
            return false;
        }

        if (is_object($res)) {
            while ($row = $res->fetch_assoc()) {
                $last_id        = $row['id'];
                list($ch, $url) = $this->reyun($row, 2, true);
                $this->addcurl($this->mh, $ch, $this->count, $this->map, $row, $url, 'reyun', 'registerData');
            }
            //不够500再提交一次
            if ($this->count < 500 && !empty($this->count)) {
                $this->addcurl($this->mh, $ch, $this->count, $this->map, $row, $url, 'reyun', 'registerData', true);
            }
            //记录最后读取的id
            $this->eventRecord('registerData', $last_id, 'registerDataLastId.log', 'lastId/', false, 2, true);
        }
    }

    //每次打开应用数据报送
    public function startUpData()
    {
        $lastId                   = @file_get_contents(dirname(__FILE__) . '/reyun/' . 'startUpData/lastId/startUpDataLastId.log');
        empty($lastId) && $lastId = 0;
        $res                      = $this->conn->query("select id,idfa,idfv,agent,game_id,time,ip from nl_init where id > {$lastId} and time > {$this->startTime} and time < {$this->endTime} and type=2  limit " . self::STARTUP_NUM);
        if ($res->num_rows < 1) {
            return false;
        }

        if (is_object($res)) {
            while ($row = $res->fetch_assoc()) {
                $last_id        = $row['id'];
                list($ch, $url) = $this->reyun($row, 7, true);
                $this->addcurl($this->mh, $ch, $this->count, $this->map, $row, $url, 'reyun', 'startUpData');
            }
            //不够500再提交一次
            if ($this->count < 500 && !empty($this->count)) {
                $this->addcurl($this->mh, $ch, $this->count, $this->map, $row, $url, 'reyun', 'startUpData', true);
            }
            //记录最后读取的id
            $this->eventRecord('startUpData', $last_id, 'startUpDataLastId.log', 'lastId/', false, 2, true);
        }
    }

    //登录数据报送
    public function loginData()
    {
        $lastId                   = @file_get_contents(dirname(__FILE__) . '/reyun/' . 'loginData/lastId/loginDataLastId.log');
        empty($lastId) && $lastId = 0;
        $res                      = $this->conn->query("select id,userCode,idfa,idfv,agent,game_id,time,ip from nl_login where id > {$lastId} and time > {$this->startTime} and time < {$this->endTime} and type=2  limit " . self::LOGIN_NUM);
        if ($res->num_rows < 1) {
            return false;
        }

        if (is_object($res)) {
            while ($row = $res->fetch_assoc()) {
                $last_id        = $row['id'];
                list($ch, $url) = $this->reyun($row, 5, true);
                $this->addcurl($this->mh, $ch, $this->count, $this->map, $row, $url, 'reyun', 'loginData');
            }
            //不够500再提交一次
            if ($this->count < 500 && !empty($this->count)) {
                $this->addcurl($this->mh, $ch, $this->count, $this->map, $row, $url, 'reyun', 'loginData', true);
            }
            //记录最后读取的id
            $this->eventRecord('loginData', $last_id, 'loginDataLastId.log', 'lastId/', false, 2, true);
        }
    }

    //充值数据报送
    public function payData()
    {
        $lastId                   = @file_get_contents(dirname(__FILE__) . '/reyun/' . 'payData/lastId/payDataLastId.log');
        empty($lastId) && $lastId = 0;
        $res                      = $this->conn->query("select id,userCode,idfa,idfv,agent,game_id,paymentTime,orderId,amount from lg_order where id > {$lastId} and paymentTime > {$this->startTime} and paymentTime < {$this->endTime} and type=2 and orderStatus = 0 and orderType = 0  limit " . self::PAY_NUM);
        if ($res->num_rows < 1) {
            return false;
        }

        if (is_object($res)) {
            while ($row = $res->fetch_assoc()) {
                $last_id        = $row['id'];
                list($ch, $url) = $this->reyun($row, 3, true);
                $this->addcurl($this->mh, $ch, $this->count, $this->map, $row, $url, 'reyun', 'payData');
            }
            //不够500再提交一次
            if ($this->count < 500 && !empty($this->count)) {
                $this->addcurl($this->mh, $ch, $this->count, $this->map, $row, $url, 'reyun', 'payData', true);
            }
            //记录最后读取的id
            $this->eventRecord('payData', $last_id, 'payDataLastId.log', 'lastId/', false, 2, true);
        }
    }

    //热云数据监控
    public function reyun($v, $type, $isreturn = false)
    {
        $appArr = array(
            /*==========================IOS=================================*/
            'cyqyj0613'   => '5afe78d506acffdfd2c7e5470da8b526', //青云幻剑录
            'qysg001'     => '6b8bde2f4273a2dc206d3da86f1b1991', //乱世英雄战纪
            'gjxy001'     => 'eb70fa4b98837810a821af22f163c809', //古剑仙缘
            'jxqtIOS'     => 'bcd074ecc2a4a77556016a077e6584e4', //剑侠奇谭
            'sgqxz001'    => 'e870ca8fe3b13ff5bb59b1b6903fa4ae', //三国群雄志
            'ztfyl001'    => '3cf815aa56ef130b6a3999230b0e7f54', //择天风云录
            'wzsgdnxn001' => 'e250ea647756d79949ad2da6ea1650cb', //我在三国的那些年
            'gcsg001'     => '0533b0bc80260248e4ed0ff06d9bccb1', //傲世三国志
            'fhxdc001'    => '0e826633724499496ff78deafb55aed5', //烽火戏貂蝉
            'sgzlsxxIOS'  => 'd7e178d19232f74502ec3747717313c9', //三国之乱世枭雄
            'xcqyIOS'     => '93e568e0541c694479855f30a5505c5b', //仙尘奇缘
            'zsgwsIOS'    => '4e335ab5b4e5fb29d3fc639e3fb6f7fe', //战三国无双
            'sszszjIOS'   => '5a6ba0178bbe1754e3081fb02852b7d6', //苍穹异世诀
            'sgzbyyIOS'   => 'c1e9fd497ff1e062e944a3700bee636c', //三国争霸演义
            'xhyzsgIOS'   => '9ab4e14ccff26a18924a9e257d423523', //红颜醉三国
            'djmxTIOS'    => 'c04d7bcf89cacbc2a0d2f388075e7c29', //刀剑萌侠二部包
            'mxcsIOS'     => 'ffcba63205f4515d38ee262f49050cd3', //萌侠传说一部包
            'sncjhIOS'    => '087035ba2320581695b4288d4ad9a57b', //少年闯江湖一部包
            'gsmxIOS'     => '96d602d1889ed6dac681747bef41d92c', //盖世萌侠一部包
            'gmsgcyIOS'   => '59f93350e873ea8eff189875173a1842', //鬼谋三国二部包
            /*==========================IOS=================================*/

            /*==========================IOS融合=================================*/
            'jmtxJWIOS'   => 'b9d195c078f6ea880a23fdd9e83554b4', //嘉玩的包，特殊添加
            'djrmJWIOS'   => '799efdbd52ec40c1b6dd1c9b1a5a8242', //嘉玩的包，特殊添加
            /*==========================IOS融合=================================*/
        );

        if (!(array_key_exists($v['agent'], $appArr))) {
            return "1";
        }

        if (empty($v['idfa'])) {
            $v['idfa'] = "00000000-0000-0000-0000-000000000000";
        }

        $domain = 'http://log.reyun.com/receive/tkio/';
        $params = array('appid' => $appArr[$v['agent']], 'context' => array('_deviceid' => $v['idfa'], '_channelid' => '_default_', '_idfa' => $v['idfa'], '_idfv' => $v['idfv']));

        switch ($type) {
            case 1: //激活
                $domain .= 'install';

                $params['context']['_ip']           = $v['ip'];
                $params['context']['_manufacturer'] = 'apple';
                $params['context']['_ryos']         = 'ios';
                $params['context']['_ryosversion']  = $v['systemInfo'];
                break;
            case 7: //打开
                $domain .= 'startup';
                break;
            case 2: //注册
                $domain .= 'register';
                $params['who'] = $v['userCode']; //账户唯一编码
                break;
            case 5: //登录
                $domain .= 'loggedin';
                $params['who'] = $v['userCode']; //账户唯一编码
                break;
            case 3: //支付
                $domain .= 'payment';
                $params['who']                        = $v['userCode']; //账户唯一编码
                $params['context']['_transactionid']  = $v['orderId'];
                $params['context']['_paymenttype']    = 'appstore';
                $params['context']['_currencytype']   = 'CNY';
                $params['context']['_currencyamount'] = $v['amount'];
                break;
        }

        $params = json_encode($params);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json')); //定义请求类型
        curl_setopt($ch, CURLOPT_URL, $domain);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);

        $loop_num = 0;
        while ($loop_num < 3) {
            ++$loop_num;
            if ($type == 3) {
                $returnTransfer = curl_exec($ch);
                $log            = '【' . date('y-m-d H:i:s') . '】domain' . $loop_num . ':' . $domain . '**params:' . $params . '**rs:' . $returnTransfer . "\r\n";
                $this->eventRecord('payData', $log, date('Y-m-d') . '_paycallbackrs.log', 'callback/', false, 5);
                if ($returnTransfer != false) {
                    curl_close($ch);
                    return "1";
                    break;
                } elseif ($loop_num == 3) {
                    return "1";
                }
            } else {
                break;
            }
        }

        if ($isreturn) {
            return array($ch, $domain . "&" . $params);
        }

        unset($v);
    }

    protected function addcurl(&$mh, $ch, &$count, &$map, $v, $url, $adname = '', $type = 'free', $remain = false)
    {
        if (is_resource($ch)) {

            curl_multi_add_handle($mh, $ch); //添加单独的curl句柄
            $map[(string) $ch] = array(
                'data' => json_encode($v),
                'url'  => $url,
                'ad'   => $adname,
            );

            ++$count;
            //500个提交一次
            if ($count == 500) {
                $rs = $this->curl_multi($mh, $map, $type);
                curl_multi_close($mh);

                $map   = array();
                $mh    = curl_multi_init();
                $count = 0;
            }
            //不够500也提交一次
            if ($remain === true) {
                $rs = $this->curl_multi($mh, $map, $type);
                curl_multi_close($mh);

                $map   = array();
                $mh    = curl_multi_init();
                $count = 0;
            }
        } elseif ($ch != "1") {
            $this->eventRecord($type, $adname . ":" . json_encode($v), date('Y-m-d') . '_false_callbackrs.log', 'callback/', false, 5);
        }
    }

    private function curl_multi($mh, &$map, $type)
    {
        do {
            while (($code = curl_multi_exec($mh, $active)) == CURLM_CALL_MULTI_PERFORM);
            if ($code != CURLM_OK) {break;}
            while ($done = curl_multi_info_read($mh)) {
                //获取结果
                $error   = curl_error($done['handle']); //获取错误信息
                $results = curl_multi_getcontent($done['handle']);

                $responses[$map[(string) $done['handle']]['data']][$map[(string) $done['handle']]['ad']]['url'] = $map[(string) $done['handle']]['url']; //获取提交信息

                $responses[$map[(string) $done['handle']]['data']][$map[(string) $done['handle']]['ad']]['result'] = compact('error', 'results'); //将返回信息组合成数组

                curl_multi_remove_handle($mh, $done['handle']);
                curl_close($done['handle']);
            }
            if ($active > 0) {
                curl_multi_select($mh); //没数据时等待
            }
        } while ($active);
        // var_dump($responses);
        $this->eventRecord($type, $responses, date('Y-m-d') . '_callbackrs.log', 'callback/', true, 5);
    }

    /**
     * 写日志
     * @param  $type
     * @param  $data    数据
     * @param  $filename 文件名
     * @param   $encrypt 是否要编码
     * @return
     */
    private function eventRecord($type, $data, $filename = '', $dir = '', $encrypt = true, $size = 2, $recordid = false)
    {
        $maxsize = $size * 1024 * 1024;
        $basedir = dirname(__FILE__) . "/reyun/" . $type . '/' . $dir . ($recordid === true ? '' : date('Y-m-d')) . '/';

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

    public function run()
    {
        $this->openData();
        $this->registerData();
        $this->loginData();
        $this->startUpData();
        $this->payData();
        $this->log_end = "【end:" . date('Y-m-d H:i:s') . "】";
        error_log($this->log_end . "\r\n", 3, __DIR__ . "/runlog.log");
    }
}

$obj = new Task();
$obj->run();
