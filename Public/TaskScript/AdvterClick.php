<?php
/**
 * 安卓推广广告点击收集
 */
require_once dirname(__FILE__) . '/public/ApiMongoDB.php';

class AdvterClick
{
    private $log_start = null; //脚本开始时间
    private $log_end   = null; //脚本结束时间
    private $advterArr = null; //广告商数组
    public function __construct()
    {
        date_default_timezone_set('PRC');
        ini_set('memory_limit', '1024M');
        error_reporting(0);
        set_time_limit(0);
        $this->advterArr = array(
            1  => 'cy', //创娱
            2  => 'gdt', //广点通
            3  => 'aqy', //爱奇艺
            4  => 'zht', //智汇推
            5  => 'bdss', //百度搜索
            6  => 'jrtt', //今日头条
            7  => 'uctt', //UC头条
            8  => 'dqd', //懂球帝
            9  => 'fhxw', //凤凰新闻
            10 => 'wx', //微信
            11 => 'xlfy', //新浪扶翼
            12 => 'smss', //神马搜索
            13 => 'sgss', //搜狗搜索
            14 => 'bdxxl', //百度信息流
            15 => 'taptap', //taptap
            16 => 'ASO', //ASO
            17 => 'shhs', //搜狐汇算
            29 => 'ks', //快手
            76 => 'dm', //多盟
            79 => 'bdyd', //百度移动DSP
            85 => 'unityAds', //unityAds
            89 => '360cpc', //360cpc
            90 => 'mgtv', //芒果TV
            95 => 'xmob', //xmob
            71 => 'qqbrowser-pairui', //qq浏览器-派瑞
            97 => 'qqbrowser-fengteng', //qq浏览器-风腾
        );

    }

    /**
     * 广告点击数据插入
     * @AuthorHTL
     * @DateTime  2017-10-10T14:52:37+0800
     * @param     [type]                   $data  [数据]
     * @return    [type]                          [true/false]
     */
    private function insert($data)
    {
        if (empty($data)) {
            return false;
        }

        $mongo = new ApiMongoDB(array('host' => 'localhost', 'port' => 59818, 'username' => 'ZgMongoAdvter', 'password' => 'lkjet#$lj10!~!3sji^', 'db' => 'advter', 'cmd' => '$'));
        $res   = $mongo->insert("advand", $data);
        $mongo->close();
        if ($res) {
            return true;
        }
        return false;
    }

    //收集点击数据
    private function collectClick()
    {
        $data = $_REQUEST;
        // return true;
        empty($data['ip']) && $data['ip'] = $this->get_ip_address();

        if ($data['adUserId'] == 7) {
            //UC头条
            if (empty($data['muid'])) {
                exit(json_encode(array('ret' => -1, 'msg' => '请求非法参数')));
            }

            $insert = array(
                'game_id'     => $data['game_id'],
                'agent'       => $data['agent'],
                'muid'        => strtolower($data['muid']),
                'adUserId'    => $data['adUserId'],
                'os'          => 1,
                'advterType'  => $this->advterArr[$data['adUserId']],
                'clickTime'   => intval(($data['clickTime'] / 1000)),
                'createTime'  => time(),
                'ip'          => $data['ip'],
                'callBackUrl' => $data['callback'],
                'status'      => 1,
            );
            $res = $this->insert($insert);
            if ($res) {
                exit('200');
            }
        } elseif ($data['adUserId'] == 6) {
            //今日头条
            if (empty($data['imei'])) {
                exit(json_encode(array('ret' => -1, 'msg' => '请求非法参数')));
            }

            $insert = array(
                'game_id'     => $data['game_id'],
                'agent'       => $data['agent'],
                'muid'        => strtolower($data['imei']),
                'adUserId'    => $data['adUserId'],
                'os'          => 1,
                'advterType'  => $this->advterArr[$data['adUserId']],
                'clickTime'   => intval(($data['timestamp'] / 1000)),
                'createTime'  => time(),
                'ip'          => $data['ip'],
                'callBackUrl' => $data['callback_url'],
                'adid'        => $data['adid'],
                'cid'         => $data['cid'],
                'mac'         => $data['mac'],
                'status'      => 1,

            );
            $res = $this->insert($insert);
            if ($res) {
                exit('200');
            }
        } elseif ($data['adUserId'] == 2) {
            //广点通
            if (empty($data['muid'])) {
                exit(json_encode(array('ret' => -1, 'msg' => '请求非法参数')));
            }

            $insert = array(
                'game_id'       => $data['game_id'],
                'agent'         => $data['agent'],
                'muid'          => strtolower($data['muid']),
                'adUserId'      => $data['adUserId'],
                'os'            => 1,
                'advterType'    => $this->advterArr[$data['adUserId']],
                'clickTime'     => intval($data['click_time']),
                'createTime'    => time(),
                'ip'            => $data['ip'],
                'callBackUrl'   => 'http://t.gdt.qq.com/conv/app/',
                'appid'         => $data['appid'],
                'click_id'      => $data['click_id'],
                'app_type'      => $data['app_type'],
                'advertiser_id' => $data['advertiser_id'],
                'status'        => 1,

            );
            $res = $this->insert($insert);
            if ($res) {
                exit(json_encode(array('ret' => 0, 'msg' => 'success')));
            }
        } elseif ($data['adUserId'] == 3) {
            //爱奇艺
            if (empty($data['m2'])) {
                exit(json_encode(array('ret' => -1, 'msg' => '请求非法参数')));
            }

            $insert = array(
                'game_id'     => $data['game_id'],
                'agent'       => $data['agent'],
                'muid'        => strtolower($data['m2']),
                'adUserId'    => $data['adUserId'],
                'os'          => 1,
                'advterType'  => $this->advterArr[$data['adUser']],
                'clickTime'   => intval($data['ts']),
                'createTime'  => time(),
                'ip'          => $data['ip'],
                'callBackUrl' => '',
                'mac'         => $data['m6a'],
                'andriodId'   => $data['m1a'],
                'status'      => 1,
            );
            $res = $this->insert($insert);
            if ($res) {
                exit(json_encode(array('ret' => 0, 'msg' => 'success')));
            }
        } elseif ($data['adUserId'] == 14) {
            //百度信息流
            if (empty($data['imei_md5'])) {
                exit(json_encode(array('ret' => -1, 'msg' => '请求非法参数')));
            }

            $insert = array(
                'game_id'     => $data['game_id'],
                'agent'       => $data['agent'],
                'muid'        => strtolower($data['imei_md5']),
                'adUserId'    => $data['adUserId'],
                'os'          => 1,
                'advterType'  => $this->advterArr[$data['adUserId']],
                'clickTime'   => intval(($data['ts'] / 1000)),
                'createTime'  => time(),
                'ip'          => $data['ip'],
                'callBackUrl' => $data['callback_url'],
                'aid'         => $data['aid'],
                'pid'         => $data['pid'],
                'uid'         => $data['uid'],
                'userid'      => $data['userid'],
                'android_id'  => $data['android_id'],
                'click_id'    => $data['click_id'],
                'ua'          => $data['ua'],
                'status'      => 1,
            );
            $res = $this->insert($insert);
            if ($res) {
                exit(json_encode(array('error_code' => 0, 'error_msg' => 'success')));
            }
        } elseif ($data['adUserId'] == 29) {
            //快手
            if (empty($data['imei'])) {
                exit(json_encode(array('ret' => -1, 'msg' => '请求非法参数')));
            }

            $insert = array(
                'game_id'     => $data['game_id'],
                'agent'       => $data['agent'],
                'imei'        => $data['imei'],
                'muid'        => strtolower(md5($data['imei'])),
                'adUserId'    => $data['adUserId'],
                'os'          => 1,
                'advterType'  => $this->advterArr[$data['adUserId']],
                'clickTime'   => intval(($data['ts'] / 1000)),
                'createTime'  => time(),
                'ip'          => $data['ip'],
                'callBackUrl' => $data['callbackurl'],
                'mac'         => $data['mac'],
                'adid'        => $data['adid'],
                'cid'         => $data['cid'],
                'status'      => 1,
            );
            $res = $this->insert($insert);
            if ($res) {
                exit(json_encode(array('error_code' => 0, 'error_msg' => 'success')));
            }
        } elseif ($data['adUserId'] == 10) {
            //微信
            if (empty($data['muid'])) {
                exit(json_encode(array('ret' => -1, 'msg' => '请求非法参数')));
            }

            $insert = array(
                'game_id'       => $data['game_id'],
                'agent'         => $data['agent'],
                'muid'          => strtolower($data['muid']),
                'adUserId'      => $data['adUserId'],
                'os'            => 1,
                'advterType'    => $this->advterArr[$data['adUserId']],
                'clickTime'     => intval($data['click_time']),
                'createTime'    => time(),
                'ip'            => $data['ip'],
                'appid'         => $data['appid'],
                'click_id'      => $data['click_id'],
                'app_type'      => $data['app_type'],
                'advertiser_id' => $data['advertiser_id'],
                'status'        => 1,
            );
            $res = $this->insert($insert);
            if ($res) {
                exit(json_encode(array('ret' => 0, 'msg' => 'success')));
            }
        } elseif ($data['adUserId'] == 76) {
            //多盟
            if (empty($data['imei']) && empty($data['imeimd5'])) {
                exit(json_encode(array('success' => false, 'message' => '请求非法参数')));
            }

            $insert = array(
                'game_id'    => $data['game_id'],
                'agent'      => $data['agent'],
                'imei'       => $data['imei'],
                'imeimd5'    => $data['imeimd5'],
                'muid'       => $data['imei'] ? strtolower(md5($data['imei'])) : $data['imeimd5'],
                'adUserId'   => $data['adUserId'],
                'os'         => 1,
                'advterType' => $this->advterArr[$data['adUserId']],
                'clickTime'  => time(),
                'createTime' => time(),
                'appid'      => $data['appkey'],
                'ip'         => $data['ip'],
                'source'     => $data['source'],
                'status'     => 1,
            );
            $res = $this->insert($insert);
            if ($res) {
                exit(json_encode(array('success' => true, 'message' => '请求成功')));
            } else {
                exit(json_encode(array('success' => false, 'message' => '请求失败')));
            }
        } elseif ($data['adUserId'] == 79) {
            //百度移动
            if (empty($data['imei'])) {
                exit(json_encode(array('code' => -1, 'msg' => '请求非法参数')));
            }

            $insert = array(
                'game_id'    => $data['game_id'],
                'agent'      => $data['agent'],
                'imei'       => $data['imei'],
                'muid'       => strtolower(md5($data['imei'])),
                'android_id' => $data['android_id'],
                'mac'        => $data['mac'],
                'adUserId'   => $data['adUserId'],
                'os'         => 1,
                'advterType' => $this->advterArr[$data['adUserId']],
                'clickTime'  => intval($data['clktime']),
                'createTime' => time(),
                'appid'      => $data['appid'],
                'cid'        => $data['cid'],
                'crid'       => $data['crid'],
                'traceid'    => $data['traceid'],
                'ip'         => $data['ip'],
                'devicetype' => $data['devicetype'],
                'osversion'  => $data['osversion'],
                'pk'         => $data['pk'],
                'status'     => 1,
            );

            $res = $this->insert($insert);
            if ($res) {
                exit(json_encode(array('code' => 200, 'msg' => 'success')));
            }
        } elseif ($data['adUserId'] == 78) {
            //vungle
            if (empty($data['android_id'])) {
                exit(json_encode(array('code' => -1, 'msg' => '请求非法参数')));
            }

            $insert = array(
                'game_id'    => $data['game_id'],
                'agent'      => $data['agent'],
                'android_id' => $data['android_id'],
                'muid'       => strtolower($data['android_id']),
                'adUserId'   => $data['adUserId'],
                'os'         => 1,
                'advterType' => $this->advterArr[$data['adUserId']],
                'clickTime'  => time(),
                'createTime' => time(),
                'appid'      => $data['app_id'],
                'id'         => $data['id'],
                'adgroup'    => $data['adgroup'],
                'ip'         => $data['ip'],
                'status'     => 1,
            );

            $res = $this->insert($insert);
            if ($res) {
                exit(json_encode(array('code' => 200, 'msg' => 'success')));
            }
        } elseif ($data['adUserId'] == 85) {
            //unityAds
            if (empty($data['android_id'])) {
                exit(json_encode(array('code' => -1, 'msg' => '请求非法参数')));
            }

            $insert = array(
                'game_id'        => $data['game_id'],
                'agent'          => $data['agent'],
                'ifa'            => $data['ifa'],
                'ifa_md5'        => $data['ifa_md5'],
                'android_id'     => $data['android_id'],
                'android_id_md5' => $data['android_id_md5'],
                'muid'           => strtolower($data['android_id']),
                'adUserId'       => $data['adUserId'],
                'os'             => 1,
                'advterType'     => $this->advterArr[$data['adUserId']],
                'clickTime'      => time(),
                'createTime'     => time(),
                'cid'            => $data['cid'],
                'gid'            => $data['gid'],
                'sgid'           => $data['sgid'],
                'ip'             => $data['ip'],
                'status'         => 1,
            );

            $res = $this->insert($insert);
            if ($res) {
                exit(header("Location: https://static.chuangyunet.net/".$data['agent'].".apk")); 
                // exit(json_encode(array('code' => 200, 'msg' => 'success')));
            }
        } elseif ($data['adUserId'] == 90) {
            //芒果TV
            if (empty($data['imei'])) {
                exit(json_encode(array('code' => -1, 'msg' => '请求非法参数')));
            }

            $insert = array(
                'game_id'        => $data['game_id'],
                'agent'          => $data['agent'],
                'imei'           => $data['imei'],
                'imei_md5'       => $data['imei_md5'],
                'muid'           => md5(strtolower($data['imei'])),
                'android_id'     => $data['androidId'],
                'android_id_md5' => $data['androidId_md5'],
                'adUserId'       => $data['adUserId'],
                'os'             => 1,
                'advterType'     => $this->advterArr[$data['adUserId']],
                'clickTime'      => intval($data['ts']) / 1000,
                'createTime'     => time(),
                'ua'             => $data['ua'],
                'uuid'           => $data['uuid'],
                'mac'            => $data['mac'],
                'aid'            => $data['aid'],
                'cid'            => $data['cid'],
                'ip'             => $data['ip'],
                'status'         => 1,
            );

            $res = $this->insert($insert);
            if ($res) {
                exit(json_encode(array('code' => 200, 'msg' => 'success')));
            }
        } elseif ($data['adUserId'] == 89) {
            //360cpc
            if (empty($data['imei_md5'])) {
                exit(json_encode(array('code' => -1, 'msg' => '请求非法参数')));
            }

            if ($data['clicktime'] == '__clicktime__') {
                $data['clicktime'] = time();
            }

            $insert = array(
                'game_id'     => $data['game_id'],
                'agent'       => $data['agent'],
                'imei_md5'    => $data['imei_md5'],
                'muid'        => strtolower($data['imei_md5']),
                'uniqueID'    => $data['uniqueID'],
                'adUserId'    => $data['adUserId'],
                'os'          => 1,
                'system'      => $data['os'],
                'advterType'  => $this->advterArr[$data['adUserId']],
                'clickTime'   => intval($data['clicktime']),
                'createTime'  => time(),
                'devicetype'  => $data['devicetype'],
                'mac_md5'     => $data['mac_md5'],
                'callBackUrl' => $data['callback_url'],
                'ip'          => $data['ip'],
                'status'      => 1,
            );

            $res = $this->insert($insert);
            if ($res) {
                exit(json_encode(array('code' => 200, 'msg' => 'success')));
            }
        } elseif ($data['adUserId'] == 95) {
            //xmob
            if (empty($data['imei'])) {
                exit(json_encode(array('code' => -1, 'msg' => '请求非法参数')));
            }

            $insert = array(
                'game_id'     => $data['game_id'],
                'agent'       => $data['agent'],
                'imei'        => $data['imei'],
                'muid'        => strtolower($data['imei']),
                'androidid'   => $data['androidid'],
                'adUserId'    => $data['adUserId'],
                'os'          => 1,
                'useragent'   => $data['useragent'],
                'advterType'  => $this->advterArr[$data['adUserId']],
                'clickTime'   => time(),
                'createTime'  => time(),
                'ip'          => $data['ip'],
                'mac'         => $data['mac'],
                'callBackUrl' => $data['callback_url'],
                'status'      => 1,
            );

            $res = $this->insert($insert);
            if ($res) {
                exit(json_encode(array('code' => 200, 'msg' => 'success')));
            }
        } elseif (in_array($data['adUserId'],[71, 97])) {
            //qq浏览器
            if (empty($data['muid_md5'])) {
                exit(json_encode(array('code' => -1, 'msg' => '请求非法参数')));
            }

            $insert = array(
                'game_id'     => $data['game_id'],
                'agent'       => $data['agent'],
                'muid'        => strtolower($data['muid_md5']),
                'adUserId'    => $data['adUserId'],
                'os'          => 1,
                'deviceType'  => $data['device_type'],
                'useragent'   => $data['useragent'],
                'advterType'  => $this->advterArr[$data['adUserId']],
                'createTime'  => time(),
                'clickId'     => $data['click_id'], 
                'clickTime'   => floor($data['click_time']/1000),
                'originTime'  => $data['click_time'],
                'ip'          => $data['ip'],
                'status'      => 1,
            );

            $res = $this->insert($insert);
            if ($res) {
                exit(json_encode(array('code' => 200, 'msg' => 'success')));
            }
        }
    }

    /**
     * 获取IP地址
     * @return string
     */
    public function get_ip_address()
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
     * 写日志
     * @param  $channelType 广告类型
     * @param  $data        数据
     * @param  $dir         目录
     * @param  $filename    文件名
     * @param   $encrypt    是否要编码
     * @return
     */
    private function eventRecord($channelType, $data, $dir = '', $filename = '', $encrypt = true, $size = 2, $recordid = false)
    {
        $maxsize = $size * 1024 * 1024;
        $basedir = dirname(__FILE__) . "/ChannelClick/" . $channelType . '/' . $dir . '/';

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
        $this->collectClick();
        exit('ok');
    }
}

$obj = new AdvterClick();
$obj->run();
