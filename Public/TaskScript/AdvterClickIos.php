<?php
/**
 * IOS推广广告点击收集
 */
require_once dirname(__FILE__) . '/public/ApiMongoDB.php';

class AdvterClickIos
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
            33 => 'aso100', //ASO100 
            36 => 'cx', //畅效
            29 => 'ks', //快手
            70 => 'lz', //来赚
            67 => 'YYQ', //YYQ
            76 => 'dm', //多盟
            77 => 'spll', //视频流量
            78 => 'vungle', //vungle
            79 => 'bdyd', //百度移动DSP
            80 => 'MobCastle', //MobCastle
            82 => 'YYQ_v2', //YYQ_v2
            85 => 'unityAds', //unityAds
            89 => '360cpc', //360cpc
            90 => 'mgtv', //芒果TV
            94 => 'anshangCPA', //安尚CPA
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
    private function insert($data, $table = 'advios')
    {
        if (empty($data)) {
            return false;
        }

        $mongo = new ApiMongoDB(array('host' => 'localhost', 'port' => 59818, 'username' => 'ZgMongoAdvter', 'password' => 'lkjet#$lj10!~!3sji^', 'db' => 'advter', 'cmd' => '$'));
        $res   = $mongo->insert($table, $data);
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

        //判断广告链接状态是否未开启
        $filename = __DIR__ . '/advterLock/' . $data['events'] . 'Lock.log';
        if (file_exists($filename) && filesize($filename) > 0) {
            exit('200');
        }
        empty($data['ip']) && $data['ip'] = $this->get_ip_address();
        $game_info                        = explode('_', $data['gf']);

        if ($data['adUser'] == 7) {
            //UC头条
            if (empty($data['idfa'])) {
                exit(json_encode(array('ret' => -1, 'msg' => '请求非法参数')));
            }

            // http://count.chuangyunet.net/AdvterClickIos.php?events=4&gf=jxqt001_101&adUser=7&muid=c600fd18c92a750a693ed1f74ef9cb18&time=1507621325&callback=http://ad.toutiao.com/track/activate/?callback=CKnit6KLAhDe9tKjiwIYzY_mzSsgzY_mzSsoADAOOA1CIDIwMTcxMDEwMTU0MDA4MDEwMDEwMDMxMDUwMzQ2RTEySAs=&os=0&muid=31986ca250d0d0a8ab03d8aa0cf35f60
            $insert = array(
                'advter_id'   => $data['events'],
                'game_id'     => $game_info[1],
                'agent'       => $game_info[0],
                'muid'        => strtolower($data['idfa']),
                'adUserId'    => $data['adUser'],
                'os'          => 2,
                'advterType'  => $this->advterArr[$data['adUser']],
                'clickTime'   => intval(($data['time'] / 1000)),
                'createTime'  => time(),
                'ip'          => $data['ip'],
                'callBackUrl' => $data['callback'],
                'status'      => 1,

            );
            $res = $this->insert($insert);
            if ($res) {
                exit('200');
            }
        } elseif ($data['adUser'] == 6) {
            //今日头条
            if (empty($data['idfa'])) {
                exit(json_encode(array('ret' => -1, 'msg' => '请求非法参数')));
            }

            $insert = array(
                'advter_id'   => $data['events'],
                'game_id'     => $game_info[1],
                'agent'       => $game_info[0],
                'muid'        => strtolower(md5($data['idfa'])),
                'adUserId'    => $data['adUser'],
                'os'          => 2,
                'advterType'  => $this->advterArr[$data['adUser']],
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
        } elseif ($data['adUser'] == 10) {
            //微信
            if (empty($data['muid'])) {
                exit(json_encode(array('ret' => -1, 'msg' => '请求非法参数')));
            }

            $insert = array(
                'advter_id'     => $data['events'],
                'game_id'       => $game_info[1],
                'agent'         => $game_info[0],
                'muid'          => strtolower($data['muid']),
                'adUserId'      => $data['adUser'],
                'os'            => 2,
                'advterType'    => $this->advterArr[$data['adUser']],
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
        } elseif ($data['adUser'] == 14) {
            //百度信息流
            if (empty($data['idfa'])) {
                exit(json_encode(array('ret' => -1, 'msg' => '请求非法参数')));
            }

            $insert = array(
                'advter_id'   => $data['events'],
                'game_id'     => $game_info[1],
                'agent'       => $game_info[0],
                'idfa'        => $data['idfa'],
                'muid'        => strtolower(md5($data['idfa'])),
                'adUserId'    => $data['adUser'],
                'os'          => 2,
                'advterType'  => $this->advterArr[$data['adUser']],
                'clickTime'   => intval(($data['ts'] / 1000)),
                'createTime'  => time(),
                'ip'          => $data['ip'],
                'callBackUrl' => $data['callback_url'],
                'aid'         => $data['aid'],
                'pid'         => $data['pid'],
                'uid'         => $data['uid'],
                'userid'      => $data['userid'],
                'click_id'    => $data['click_id'],
                'ua'          => $data['ua'],
                'status'      => 1,

            );
            $res = $this->insert($insert);
            if ($res) {
                exit(json_encode(array('error_code' => 0, 'error_msg' => 'success')));
            }
        } elseif ($data['adUser'] == 2) {
            //广点通
            if (empty($data['muid'])) {
                exit(json_encode(array('ret' => -1, 'msg' => '请求非法参数')));
            }

            $insert = array(
                'advter_id'     => $data['events'],
                'game_id'       => $game_info[1],
                'agent'         => $game_info[0],
                'muid'          => strtolower($data['muid']),
                'adUserId'      => $data['adUser'],
                'os'            => 2,
                'advterType'    => $this->advterArr[$data['adUser']],
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
        } elseif ($data['adUser'] == 3) {
            //爱奇艺
            if (empty($data['m5'])) {
                exit(json_encode(array('ret' => -1, 'msg' => '请求非法参数')));
            }

            $insert = array(
                'advter_id'   => $data['events'],
                'game_id'     => $game_info[1],
                'agent'       => $game_info[0],
                'idfa'        => $data['m5'],
                'muid'        => strtolower(md5($data['m5'])),
                'adUserId'    => $data['adUser'],
                'os'          => 2,
                'advterType'  => $this->advterArr[$data['adUser']],
                'clickTime'   => intval($data['ts']),
                'createTime'  => time(),
                'ip'          => $data['ns'],
                'callBackUrl' => '',
                'mac'         => $data['m6a'],
                'openudid'    => $data['m0'],
                'udid'        => $data['m0a'],
                'status'      => 1,
            );
            $res = $this->insert($insert);
            if ($res) {
                exit(json_encode(array('ret' => 0, 'msg' => 'success')));
            }
        } elseif ($data['adUser'] == 36) {
            //畅效
            if (empty($data['idfa'])) {
                exit(json_encode(array('ret' => -1, 'msg' => '请求非法参数')));
            }

            $insert = array(
                'advter_id'   => $data['events'],
                'game_id'     => $game_info[1],
                'agent'       => $game_info[0],
                'idfa'        => $data['idfa'],
                'muid'        => strtolower(md5($data['idfa'])),
                'adUserId'    => $data['adUser'],
                'os'          => 2,
                'advterType'  => $this->advterArr[$data['adUser']],
                'clickTime'   => intval($data['clktime']),
                'createTime'  => time(),
                'ip'          => $data['clkip'],
                'callBackUrl' => $data['callbackurl'],
                'status'      => 1,
            );
            $res = $this->insert($insert);
            if ($res) {
                exit(json_encode(array('code' => 200, 'msg' => 'success')));
            }
        } elseif ($data['adUser'] == 29) {
            //快手
            if (empty($data['idfa'])) {
                exit(json_encode(array('ret' => -1, 'msg' => '请求非法参数')));
            }

            $insert = array(
                'advter_id'   => $data['events'],
                'game_id'     => $game_info[1],
                'agent'       => $game_info[0],
                'idfa'        => $data['idfa'],
                'muid'        => strtolower(md5($data['idfa'])),
                'adUserId'    => $data['adUser'],
                'os'          => 2,
                'advterType'  => $this->advterArr[$data['adUser']],
                'clickTime'   => intval($data['ts'] / 1000),
                'createTime'  => time(),
                'ip'          => $data['ip'],
                'callBackUrl' => $data['callbackurl'],
                'adid'        => $data['adid'],
                'cid'         => $data['cid'],
                'status'      => 1,
            );
            $res = $this->insert($insert);
            if ($res) {
                exit(json_encode(array('code' => 200, 'msg' => 'success')));
            }
        } elseif ($data['adUser'] == 70) {
            //来赚
            if (empty($data['idfa'])) {
                exit(json_encode(array('ret' => -1, 'msg' => '请求非法参数')));
            }

            $insert = array(
                'advter_id'  => $data['events'],
                'game_id'    => $game_info[1],
                'agent'      => $game_info[0],
                'idfa'       => $data['idfa'],
                'muid'       => strtolower(md5($data['idfa'])),
                'adUserId'   => $data['adUser'],
                'os'         => 2,
                'advterType' => $this->advterArr[$data['adUser']],
                'createTime' => time(),
                'clickTime'  => time(),
                'ip'         => $data['ip'],
                'appid'      => $data['appid'],
                'source'     => $data['source'],
                'status'     => 1,
            );
            $res = $this->insert($insert);
            if ($res) {
                exit(json_encode(array('code' => 200, 'msg' => 'success')));
            }
        } elseif ($data['adUser'] == 67) {
            //YYQ
            if (empty($data['idfa'])) {
                exit(json_encode(array('ret' => -1, 'msg' => '请求非法参数')));
            }

            $insert = array(
                'advter_id'   => $data['events'],
                'game_id'     => $game_info[1],
                'agent'       => $game_info[0],
                'idfa'        => $data['idfa'],
                'muid'        => strtolower(md5($data['idfa'])),
                'adUserId'    => $data['adUser'],
                'os'          => 2,
                'advterType'  => $this->advterArr[$data['adUser']],
                'clickTime'   => intval($data['ts']),
                'createTime'  => time(),
                'ip'          => $data['ip'],
                'callBackUrl' => $data['callback'],
                'status'      => 1,
            );
            $res = $this->insert($insert);
            if ($res) {
                exit(json_encode(array('code' => 200, 'msg' => 'success')));
            }
        } elseif ($data['adUser'] == 76) {
            //多盟
            if (empty($data['ifa']) && empty($data['ifamd5'])) {
                exit(json_encode(array('success' => false, 'message' => '请求非法参数')));
            }

            $insert = array(
                'advter_id'  => $data['events'],
                'game_id'    => $game_info[1],
                'agent'      => $game_info[0],
                'idfa'       => $data['ifa'],
                'ifamd5'     => $data['ifamd5'],
                'muid'       => $data['ifa'] ? strtolower(md5($data['ifa'])) : strtolower($data['ifamd5']),
                'adUserId'   => $data['adUser'],
                'os'         => 2,
                'advterType' => $this->advterArr[$data['adUser']],
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
        } elseif ($data['adUser'] == 77) {
            //视频流量
            if (empty($data['idfa'])) {
                exit(json_encode(array('ret' => -1, 'msg' => '请求非法参数')));
            }

            $insert = array(
                'advter_id'   => $data['events'],
                'game_id'     => $game_info[1],
                'agent'       => $game_info[0],
                'idfa'        => $data['idfa'],
                'muid'        => strtolower(md5($data['idfa'])),
                'adUserId'    => $data['adUser'],
                'os'          => 2,
                'advterType'  => $this->advterArr[$data['adUser']],
                'clickTime'   => intval($data['clicktime']),
                'createTime'  => time(),
                'appid'       => $data['appid'],
                'clickid'     => $data['clickid'],
                'ip'          => $data['ip'],
                'callBackUrl' => 'http://s2s.global.daoyoudao.com/s2s/report.jsp?clickid=' . $data['clickid'],
                'status'      => 1,
            );
            $res = $this->insert($insert);
            if ($res) {
                exit(json_encode(array('code' => 200, 'msg' => 'success')));
            }
        } elseif ($data['adUser'] == 78) {
            //vungle
            if (empty($data['ifa'])) {
                exit(json_encode(array('ret' => -1, 'msg' => '请求非法参数')));
            }

            $insert = array(
                'advter_id'  => $data['events'],
                'game_id'    => $game_info[1],
                'agent'      => $game_info[0],
                'adUserId'   => $data['adUser'],
                'idfa'       => $data['ifa'],
                'muid'       => strtolower(md5(strtoupper($data['ifa']))),
                'appid'      => $data['app_id'],
                'eid'        => $data['eid'],
                'sub_pub'    => $data['sub_pub'],
                'os'         => 2,
                'advterType' => $this->advterArr[$data['adUser']],
                'clickTime'  => time(),
                'createTime' => time(),
                'ip'         => $data['ip'],
                'status'     => 1,
            );
            $res = $this->insert($insert);
            if ($res) {
                exit(json_encode(array('code' => 200, 'msg' => 'success')));
            }
        } elseif ($data['adUser'] == 79) {
            //百度移动DSP
            if (empty($data['idfa'])) {
                exit(json_encode(array('code' => -1, 'msg' => '请求非法参数')));
            }

            $insert = array(
                'advter_id'  => $data['events'],
                'game_id'    => $game_info[1],
                'agent'      => $game_info[0],
                'mac'        => $data['mac'],
                'idfa'       => $data['idfa'],
                'muid'       => strtolower(md5($data['idfa'])),
                'adUserId'   => $data['adUser'],
                'advterType' => $this->advterArr[$data['adUser']],
                'appid'      => $data['appid'],
                'cid'        => $data['cid'],
                'crid'       => $data['crid'],
                'traceid'    => $data['traceid'],
                'ip'         => $data['ip'],
                'devicetype' => $data['devicetype'],
                'osversion'  => $data['osversion'],
                'pk'         => $data['pk'],
                'clickTime'  => intval($data['clktime']),
                'createTime' => time(),
                'os'         => 2,
                'status'     => 1,
            );
            $res = $this->insert($insert);
            if ($res) {
                exit(json_encode(array('code' => 200, 'msg' => 'success')));
            }
        } elseif ($data['adUser'] == 80) {
            //MobCastle
            if (empty($data['deviceid'])) {
                exit(json_encode(array('code' => -1, 'msg' => '请求非法参数')));
            }

            $insert = array(
                'advter_id'  => $data['events'],
                'game_id'    => $game_info[1],
                'agent'      => $game_info[0],
                'clickid'    => $data['clickid'],
                'idfa'       => $data['deviceid'],
                'muid'       => strtolower(md5($data['deviceid'])),
                'adUserId'   => $data['adUser'],
                'advterType' => $this->advterArr[$data['adUser']],
                'ip'         => $data['ip'],
                'os_v'       => $data['os_v'],
                's1'         => $data['s1'],
                'callback'   => $data['callback'],
                'clickTime'  => time(),
                'createTime' => time(),
                'os'         => 2,
                'status'     => 1,
            );
            $res = $this->insert($insert);
            if ($res) {
                exit(json_encode(array('code' => 200, 'msg' => 'success')));
            }
        } elseif ($data['adUser'] == 82) {
            //YYQ_v2
            if (empty($data['idfa'])) {
                exit(json_encode(array('ret' => -1, 'msg' => '请求非法参数')));
            }

            $insert = array(
                'advter_id'   => $data['events'],
                'game_id'     => $game_info[1],
                'agent'       => $game_info[0],
                'idfa'        => $data['idfa'],
                'muid'        => strtolower(md5($data['idfa'])),
                'adUserId'    => $data['adUser'],
                'os'          => 2,
                'advterType'  => $this->advterArr[$data['adUser']],
                'appid'       => $data['appId'],
                'clickTime'   => time(),
                'createTime'  => time(),
                'ip'          => $data['ip'],
                'callBackUrl' => $data['callbackurl'],
                'status'      => 1,
            );
            $res = $this->insert($insert);
            if ($res) {
                exit(json_encode(array('code' => 200, 'msg' => 'success')));
            }
        } elseif ($data['adUser'] == 85) {
            //unityAds
            if (empty($data['idfa'])) {
                exit(json_encode(array('code' => -1, 'msg' => '请求非法参数')));
            }

            $insert = array(
                'advter_id'  => $data['events'],
                'game_id'    => $game_info[1],
                'agent'      => $game_info[0],
                'idfa'       => $data['idfa'],
                'idfa_md5'   => $data['idfa_md5'],
                'muid'       => strtolower(md5($data['idfa'])),
                'adUserId'   => $data['adUser'],
                'os'         => 2,
                'advterType' => $this->advterArr[$data['adUser']],
                'cid'        => $data['cid'],
                'gid'        => $data['gid'],
                'sgid'       => $data['sgid'],
                'ip'         => $data['ip'],
                'clickTime'  => time(),
                'createTime' => time(),
                'status'     => 1,
            );
            $res = $this->insert($insert);
            if ($res) {
                exit(json_encode(array('code' => 200, 'msg' => 'success')));
            }
        } elseif ($data['adUser'] == 90) {
            //芒果TV
            if (empty($data['idfa'])) {
                exit(json_encode(array('code' => -1, 'msg' => '请求非法参数')));
            }

            $insert = array(
                'advter_id'  => $data['events'],
                'game_id'    => $game_info[1],
                'agent'      => $game_info[0],
                'idfa'       => $data['idfa'],
                'openudid'   => $data['openudid'],
                'muid'       => strtolower(md5($data['idfa'])),
                'adUserId'   => $data['adUser'],
                'os'         => 2,
                'advterType' => $this->advterArr[$data['adUser']],
                'clickTime'  => intval($data['ts']) / 1000,
                'createTime' => time(),
                'ua'         => $data['ua'],
                'uuid'       => $data['uuid'],
                'mac'        => $data['mac'],
                'aid'        => $data['aid'],
                'cid'        => $data['cid'],
                'ip'         => $data['ip'],
                'status'     => 1,
            );
            $res = $this->insert($insert);
            if ($res) {
                exit(json_encode(array('code' => 200, 'msg' => 'success')));
            }
        } elseif ($data['adUser'] == 89) {
            //360cpc
            if (empty($data['idfa'])) {
                exit(json_encode(array('code' => -1, 'msg' => '请求非法参数')));
            }

            if ($data['clicktime'] == '__clicktime__') {
                $data['clicktime'] = time();
            }

            $insert = array(
                'advter_id'   => $data['events'],
                'game_id'     => $game_info[1],
                'agent'       => $game_info[0],
                'idfa'        => $data['idfa'],
                'muid'        => strtolower(md5($data['idfa'])),
                'uniqueID'    => $data['uniqueID'],
                'adUserId'    => $data['adUser'],
                'os'          => 2,
                'system'      => $data['os'],
                'advterType'  => $this->advterArr[$data['adUser']],
                'clickTime'   => intval($data['clicktime']),
                'createTime'  => time(),
                'devicetype'  => $data['devicetype'],
                'callBackUrl' => $data['callback_url'],
                'mac_md5'     => $data['mac_md5'],
                'ip'          => $data['ip'],
                'status'      => 1,
            );

            $res = $this->insert($insert);
            if ($res) {
                exit(json_encode(array('code' => 200, 'msg' => 'success')));
            }
        } elseif ($data['adUser'] == 33) {
            //aso100
            if (empty($data['idfa'])) {
                exit(json_encode(array('ret' => -1, 'msg' => '请求非法参数')));
            }

            $insert = array(
                'advter_id'  => $data['events'],
                'game_id'    => $game_info[1],
                'agent'      => $game_info[0],
                'idfa'       => $data['idfa'],
                'muid'       => strtolower(md5($data['idfa'])),
                'adUserId'   => $data['adUser'],
                'os'         => 2,
                'advterType' => $this->advterArr[$data['adUser']],
                'createTime' => time(),
                'clickTime'  => time(),
                'ip'         => $data['ip'],
                'appid'      => $data['appid'],
                'source'     => $data['source'],
                'status'     => 1,
            );
            $res = $this->insert($insert);
            if ($res) {
                exit(json_encode(array('code' => 200, 'msg' => 'success')));
            }
        } elseif ($data['adUser'] == 94) {
            //安尚CPA
            if (empty($data['idfa'])) {
                exit(json_encode(array('ret' => -1, 'msg' => '请求非法参数')));
            }

            $insert = array(
                'advter_id'  => $data['events'],
                'game_id'    => $game_info[1],
                'agent'      => $game_info[0],
                'idfa'       => $data['idfa'],
                'idfamd5'    => $data['idfamd5'],
                'muid'       => strtolower(md5($data['idfa'])),
                'adUserId'   => $data['adUser'],
                'os'         => 2,
                'advterType' => $this->advterArr[$data['adUser']],
                'createTime' => time(),
                'clickTime'  => intval($data['clktime']),
                'ip'         => $data['ip'],
                'appid'      => $data['appid'],
                'cid'        => $data['cid'],
                'status'     => 1,
            );
            $res = $this->insert($insert);
            if ($res) {
                exit(json_encode(array('code' => 200, 'msg' => 'success')));
            }
        } elseif ($data['adUser'] == 95) {
            //xmob
            if (empty($data['idfa'])) {
                exit(json_encode(array('ret' => -1, 'msg' => '请求非法参数')));
            }

            $insert = array(
                'advter_id'  => $data['events'],
                'game_id'    => $game_info[1],
                'agent'      => $game_info[0],
                'idfa'       => $data['idfa'],
                'muid'       => strtolower(md5($data['idfa'])),
                'adUserId'   => $data['adUser'],
                'os'         => 2,
                'advterType' => $this->advterArr[$data['adUser']],
                'createTime' => time(),
                'clickTime'  => time(),
                'ip'         => $data['ip'],
                'mac'        => $data['mac'],
                'useragent'  => $data['useragent'],
                'callBackUrl'=> $data['callback_url'],
                'status'     => 1,
            );
            $res = $this->insert($insert);
            if ($res) {
                exit(json_encode(array('code' => 200, 'msg' => 'success')));
            }
        } elseif (in_array($data['adUser'],[71, 97])) {
            //qq浏览器
            if (empty($data['muid_md5'])) {
                exit(json_encode(array('ret' => -1, 'msg' => '请求非法参数')));
            }

            $insert = array(
                'advter_id'  => $data['events'],
                'game_id'    => $game_info[1],
                'agent'      => $game_info[0],
                'muid'       => strtolower($data['muid_md5']),
                'adUserId'   => $data['adUser'],
                'os'         => 2,
                'deviceType' => $data['device_type'],
                'advterType' => $this->advterArr[$data['adUser']],
                'createTime' => time(),
                'clickId'    => $data['click_id'], 
                'clickTime'  => floor($data['click_time']/1000),
                'originTime' => $data['click_time'],
                'ip'         => $data['ip'],
                'status'     => 1,
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

$obj = new AdvterClickIos();
$obj->run();
