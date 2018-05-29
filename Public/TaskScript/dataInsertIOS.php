<?php
/**
 * 角色在线信息异步入库
 */
require_once dirname(__FILE__) . '/public/Connect.php';

class dataInsertIOS extends Connect
{
    const DS           = DIRECTORY_SEPARATOR;
    private $sql       = ''; //sql语句
    private $limit     = 1000; //限定5000条
    private $num       = 0; //计数变量
    private $maxTime   = 0; //mongo每次查询记录最大时间
    private $conn      = null; //数据库链接资源
    private $mongo     = null; //mongo资源
    private $status    = true;
    private $table     = null;
    private $checkSign = "signCheck1233Cy"; /*jlsjlkjethlj79837gg   139.199.197.21*/

    public function __construct()
    {

        date_default_timezone_set('PRC');
        // error_reporting(0);
        set_time_limit(0);
        ini_set('memory_limit', '2048M');
        $this->conn = parent::dbConnect('127.0.0.1', 'root', '', 'lgame');
        $this->conn->query("set names utf8;");
        $start = time();
        $this->onlineCount();
        mysqli_close($this->conn);
        echo time() - $start;
        exit('ok');
    }

    //添加设备
    public function createDeviceData()
    {
        //udid  game_id agent   channel_id  mac serial  imei    imei2   idfa    idfv    systemId    systemInfo  createTime  ip  city    province    lastInit    lastLogin   lastPay
        $this->table = 'lg_device_game';

        //lg_device_game    新增
        //2017/11/16        40860
        //2017/11/17        39283
        //2017/11/18        38727
        //2017/11/19        36709
        //2017/11/20        33554
        //2017/11/21        28615
        //2017/11/22        30392

        //这个有点问题，应该以时间作为key，数值作为value，避免相同数量时出错
        $dayTime     = array(
            '2017-11-16' => 40860,
            '2017-11-17' => 39283,
            '2017-11-18' => 38727,
            '2017-11-19' => 36709,
            '2017-11-20' => 33554,
            '2017-11-21' => 28615,
            '2017-11-22' => 30392,
        );
        $data        = array();
        foreach ($dayTime as $k => $v) {
            for ($i = 0; $i < $v; $i++) {
                $arr['udid']       = 'DeviceTestIos' . str_replace('-', '', $k) . str_pad($i + 1, 8, '0', STR_PAD_LEFT);
                $arr['game_id']    = '104';
                $arr['agent']      = 'cqxyIOS';
                $arr['channel_id'] = '1';
                $arr['mac']        = '123456789';
                $arr['serial']     = '';
                $arr['imei']       = '987654321';
                $arr['imei2']      = '';
                $arr['idfa']       = '00000000-0000-0000-000000000000';
                $arr['idfv']       = '00000000-0000-0000-000000000000';
                $arr['systemId']   = '';
                $arr['systemInfo'] = '';
                $arr['createTime'] = strtotime($k . " 12:00:00");
                $arr['ip']         = '127.0.0.1';
                $arr['city']       = '';
                $arr['province']   = '';
                $arr['lastInit']   = 0;
                $arr['lastLogin']  = 0;
                $arr['lastPay']    = 0;
                $data[]            = $arr;
            }
        }

        return $data;
    }

    //添加新增用户
    public function createUserData()
    {

        $this->table = 'lg_user_game';

        //lg_user_game      新增用户    对应设备数   重复的设备或昨日设备
        //2017/11/16        38012   33451       4561
        //2017/11/17        36129   31794       4335
        //2017/11/18        35129   30914       4215
        //2017/11/19        32087   28237       3850
        //2017/11/20        30125   26510       3615
        //2017/11/21        25871   22766       3105
        //2017/11/22        26739   23530       3209


        $dayTime     = array(
            array('dayTime' => '2017-11-16', 'max' => 38012, 'min' => 33451),
            array('dayTime' => '2017-11-17', 'max' => 36129, 'min' => 31794),
            array('dayTime' => '2017-11-18', 'max' => 35129, 'min' => 30914),
            array('dayTime' => '2017-11-19', 'max' => 32087, 'min' => 28237),
            array('dayTime' => '2017-11-20', 'max' => 30125, 'min' => 26510),
            array('dayTime' => '2017-11-21', 'max' => 25871, 'min' => 22766),
            array('dayTime' => '2017-11-22', 'max' => 26739, 'min' => 23530),
        );

        $data = array();
        foreach ($dayTime as $k => $v) {
            for ($i = 0; $i < $v['max']; $i++) {
                $arr['userCode'] = 'UserTestIos' . str_replace('-', '', $v['dayTime']) . str_pad($i + 1, 8, '0', STR_PAD_LEFT);
                if ($i >= $v['min']) {
                    $arr['udid'] = 'DeviceTestIos' . str_replace('-', '', date('Y-m-d', strtotime($v['dayTime'] . ' -1 day'))) . str_pad($i + 1, 8, '0', STR_PAD_LEFT);
                } else {
                    $arr['udid'] = 'DeviceTestIos' . str_replace('-', '', $v['dayTime']) . str_pad($i + 1, 8, '0', STR_PAD_LEFT);
                }
                $arr['userName']          = $arr['userCode'];
                $arr['game_id']           = '104';
                $arr['agent']             = 'cqxyIOS';
                $arr['channel_id']        = '1';
                $arr['serverId']          = '1';
                $arr['device_id']         = '0';
                $arr['type']              = '1';
                $arr['ver']               = '1';
                $arr['imei']              = '987654321';
                $arr['imei2']             = '';
                $arr['idfa']              = '00000000-0000-0000-000000000000';
                $arr['createTime']        = strtotime($v['dayTime'] . " 12:00:00");
                $arr['ip']                = '127.0.0.1';
                $arr['city']              = '';
                $arr['province']          = '';
                $arr['lastIP']            = '127.0.0.1';
                $arr['lastLogin']         = '';
                $arr['lastPay']           = '';
                $arr['lastGameId']        = '104';
                $arr['lastAgent']         = 'cqxyIOS';
                $arr['lastPayRoleId']     = '';
                $arr['lastPayRoleName']   = '';
                $arr['lastPayServerId']   = '';
                $arr['lastPayServerName'] = '';
                $data[]                   = $arr;
            }
        }

        return $data;
    }

    //新用户登录
    public function createNewLoginData()
    {
        $this->table = 'nl_role_login';

        //注册日期          用户数 设备数
        //2017/11/13        43896   39028
        //2017/11/14        42167   37225
        //2017/11/15        41027   36145
        //2017/11/16        38012   33451
        //2017/11/17        36129   31794
        //2017/11/18        35129   30914
        //2017/11/19        32087   28237
        //2017/11/20        30125   26510
        //2017/11/21        25871   22766
        //2017/11/22        26739   23530

        $dayTime     = array(
            array('dayTime' => '2017-11-13', 'max' => 43896, 'min' => 39028),
            array('dayTime' => '2017-11-14', 'max' => 42167, 'min' => 37225),
            array('dayTime' => '2017-11-15', 'max' => 41027, 'min' => 36145),
            array('dayTime' => '2017-11-16', 'max' => 38012, 'min' => 33451),
            array('dayTime' => '2017-11-17', 'max' => 36129, 'min' => 31794),
            array('dayTime' => '2017-11-18', 'max' => 35129, 'min' => 30914),
            array('dayTime' => '2017-11-19', 'max' => 32087, 'min' => 28237),
            array('dayTime' => '2017-11-20', 'max' => 30125, 'min' => 26510),
            array('dayTime' => '2017-11-21', 'max' => 25871, 'min' => 22766),
            array('dayTime' => '2017-11-22', 'max' => 26739, 'min' => 23530),
        );

        $data = array();
        foreach ($dayTime as $k => $v) {
            for ($i = 0; $i < $v['max']; $i++) {
                $arr['userCode'] = 'UserTestIos' . str_replace('-', '', $v['dayTime']) . str_pad($i + 1, 8, '0', STR_PAD_LEFT);
                if ($i >= $v['min']) {
                    $arr['udid'] = 'DeviceTestIos' . str_replace('-', '', date('Y-m-d', strtotime($v['dayTime'] . ' -1 day'))) . str_pad($i + 1, 8, '0', STR_PAD_LEFT);
                } else {
                    $arr['udid'] = 'DeviceTestIos' . str_replace('-', '', $v['dayTime']) . str_pad($i + 1, 8, '0', STR_PAD_LEFT);
                }
                $arr['game_id']       = '104';
                $arr['roleId']        = '1';
                $arr['roleName']      = 'test';
                $arr['agent']         = 'cqxyIOS';
                $arr['regAgent']      = 'cqxyIOS';
                $arr['serverId']      = '1';
                $arr['loginServerId'] = '1';
                $arr['serverName']    = '1';
                $arr['level']         = '1';
                $arr['currency']      = '1';
                $arr['vip']           = '1';
                $arr['balance']       = '1';
                $arr['power']         = '1';
                $arr['regTime']       = strtotime($v['dayTime'] . " 12:00:00");
                $arr['time']          = strtotime($v['dayTime'] . " 12:00:00");
                $data[]               = $arr;

            }
        }

        return $data;
    }

//    //老用户登录
    //    public function createOldLoginData()
    //    {
    //        $this->table = 'nl_role_login';
    //        $dayTime     = array(
    //            array('dayTime' => '2017-11-02', 'max' => 41083),
    //            array('dayTime' => '2017-11-03', 'max' => 39068),
    //            array('dayTime' => '2017-11-04', 'max' => 38674),
    //            array('dayTime' => '2017-11-05', 'max' => 37765),
    //            array('dayTime' => '2017-11-06', 'max' => 38451),
    //            array('dayTime' => '2017-11-07', 'max' => 37723),
    //        );
    //
    //        $data = array();
    //        foreach ($dayTime as $k => $v) {
    //            for ($i = 0; $i < $v['max']; $i++) {
    //                $arr['userCode'] = 'UserTestIos' . str_replace('-', '', $v['dayTime']) . str_pad($i + 1, 8, '0', STR_PAD_LEFT);
    //                $arr['udid'] = 'DeviceTestIos' . str_replace('-', '', $v['dayTime']) . str_pad($i + 1, 8, '0', STR_PAD_LEFT);
    //                $arr['game_id']       = '104';
    //                $arr['roleId']        = '1';
    //                $arr['roleName']      = 'test';
    //                $arr['agent']         = 'cqxyIOS';
    //                $arr['regAgent']      = 'cqxyIOS';
    //                $arr['serverId']      = '1';
    //                $arr['loginServerId'] = '1';
    //                $arr['serverName']    = '1';
    //                $arr['level']         = '1';
    //                $arr['currency']      = '1';
    //                $arr['vip']           = '1';
    //                $arr['balance']       = '1';
    //                $arr['power']         = '1';
    //                $arr['regTime']       = strtotime(strtotime($v['dayTime'].' -1 day') . " 12:00:00");
    //                $arr['time']          = strtotime($v['dayTime'] . " 12:00:00");
    //                $data[]               = $arr;
    //
    //            }
    //        }
    //
    //        return $data;
    //    }

    //二登
    public function create2LoginData()
    {
        $this->table = 'nl_role_login';

        //注册日期      登陆日期            用户数 设备数
        //2017/11/12    2017/11/13      12124   12124
        //2017/11/13    2017/11/14      15368   15368
        //2017/11/14    2017/11/15      11874   11874
        //2017/11/15    2017/11/16      12981   12981
        //2017/11/16    2017/11/17      11206   11206
        //2017/11/17    2017/11/18      11756   11756
        //2017/11/18    2017/11/19      11645   11645
        //2017/11/19    2017/11/20      9677    9677
        //2017/11/20    2017/11/21      8543    8543
        //2017/11/21    2017/11/22      7544    7544

        $dayTime     = array(
            array('dayTime' => '2017-11-12', 'max' => 12124, 'min' => 12124),
            array('dayTime' => '2017-11-13', 'max' => 15368, 'min' => 15368),
            array('dayTime' => '2017-11-14', 'max' => 11874, 'min' => 11874),
            array('dayTime' => '2017-11-15', 'max' => 12981, 'min' => 12981),
            array('dayTime' => '2017-11-16', 'max' => 11206, 'min' => 11206),
            array('dayTime' => '2017-11-17', 'max' => 11756, 'min' => 11756),
            array('dayTime' => '2017-11-18', 'max' => 11645, 'min' => 11645),
            array('dayTime' => '2017-11-19', 'max' => 9677, 'min' => 9677),
            array('dayTime' => '2017-11-20', 'max' => 8543, 'min' => 8543),
            array('dayTime' => '2017-11-21', 'max' => 7544, 'min' => 7544),
        );
        $data = array();
        foreach ($dayTime as $k => $v) {
            for ($i = 0; $i < $v['max']; $i++) {
                $arr['userCode']      = 'UserTestIos' . str_replace('-', '', $v['dayTime']) . str_pad($i + 1, 8, '0', STR_PAD_LEFT);
                if ($i >= $v['min']) {
                    $arr['udid'] = 'DeviceTestIos' . str_replace('-', '', date('Y-m-d', strtotime($v['dayTime'] . ' -1 day'))) . str_pad($i + 1, 8, '0', STR_PAD_LEFT);
                } else {
                    $arr['udid'] = 'DeviceTestIos' . str_replace('-', '', $v['dayTime']) . str_pad($i + 1, 8, '0', STR_PAD_LEFT);
                }
                $arr['game_id']       = '104';
                $arr['roleId']        = '1';
                $arr['roleName']      = 'test';
                $arr['agent']         = 'cqxyIOS';
                $arr['regAgent']      = 'cqxyIOS';
                $arr['serverId']      = '1';
                $arr['loginServerId'] = '1';
                $arr['serverName']    = '1';
                $arr['level']         = '1';
                $arr['currency']      = '1';
                $arr['vip']           = '1';
                $arr['balance']       = '1';
                $arr['power']         = '1';
                $arr['regTime']       = strtotime($v['dayTime'] . " 12:00:00");
                $arr['time']          = strtotime(date('Y-m-d', strtotime($v['dayTime'] . ' +1 day')) . " 12:00:00");
                $data[]               = $arr;

            }
        }

        return $data;
    }

    public function create3LoginData()
    {

        $this->table = 'nl_role_login';

        //注册日期      登陆日期            用户数 设备数
        //2017/11/11    2017/11/13      28286   28286
        //2017/11/12    2017/11/14      29537   29537
        //2017/11/13    2017/11/15      35317   35317
        //2017/11/14    2017/11/16      37042   37042
        //2017/11/15    2017/11/17      41027   36145
        //2017/11/16    2017/11/18      38012   33451
        //2017/11/17    2017/11/19      36129   31794
        //2017/11/18    2017/11/20      35129   30914
        //2017/11/19    2017/11/21      32087   28237
        //2017/11/20    2017/11/22      30125   26510

        $dayTime     = array(
            array('dayTime' => '2017-11-11', 'max' => 28286, 'min' => 28286),
            array('dayTime' => '2017-11-12', 'max' => 29537, 'min' => 29537),
            array('dayTime' => '2017-11-13', 'max' => 35317, 'min' => 35317),
            array('dayTime' => '2017-11-14', 'max' => 37042, 'min' => 37042),
            array('dayTime' => '2017-11-15', 'max' => 41027, 'min' => 36145),
            array('dayTime' => '2017-11-16', 'max' => 38012, 'min' => 33451),
            array('dayTime' => '2017-11-17', 'max' => 36129, 'min' => 31794),
            array('dayTime' => '2017-11-18', 'max' => 35129, 'min' => 30914),
            array('dayTime' => '2017-11-19', 'max' => 32087, 'min' => 28237),
            array('dayTime' => '2017-11-20', 'max' => 30125, 'min' => 26510),
        );

        $data = array();
        foreach ($dayTime as $k => $v) {
            for ($i = 0; $i < $v['max']; $i++) {
                $arr['userCode']      = 'UserTestIos' . str_replace('-', '', $v['dayTime']) . str_pad($i + 1, 8, '0', STR_PAD_LEFT);
                if ($i >= $v['min']) {
                    $arr['udid'] = 'DeviceTestIos' . str_replace('-', '', date('Y-m-d', strtotime($v['dayTime'] . ' -1 day'))) . str_pad($i + 1, 8, '0', STR_PAD_LEFT);
                } else {
                    $arr['udid'] = 'DeviceTestIos' . str_replace('-', '', $v['dayTime']) . str_pad($i + 1, 8, '0', STR_PAD_LEFT);
                }
                $arr['game_id']       = '104';
                $arr['roleId']        = '1';
                $arr['roleName']      = 'test';
                $arr['agent']         = 'cqxyIOS';
                $arr['regAgent']      = 'cqxyIOS';
                $arr['serverId']      = '1';
                $arr['loginServerId'] = '1';
                $arr['serverName']    = '1';
                $arr['level']         = '1';
                $arr['currency']      = '1';
                $arr['vip']           = '1';
                $arr['balance']       = '1';
                $arr['power']         = '1';
                $arr['regTime']       = strtotime($v['dayTime'] . " 12:00:00");
                $arr['time']          = strtotime(date('Y-m-d', strtotime($v['dayTime'] . ' +2 day')) . " 12:00:00");
                $data[]               = $arr;
            }
        }

        return $data;
    }

    public function create4LoginData()
    {

        $this->table = 'nl_role_login';

        //注册日期      登陆日期            用户数 设备数
        //2017/11/14    2017/11/17      51      51
        //2017/11/15    2017/11/18      3019    3019
        //2017/11/16    2017/11/19      5886    5886
        //2017/11/17    2017/11/20      10448   10448
        //2017/11/18    2017/11/21      13801   13801
        //2017/11/19    2017/11/22      17004   17004

        $dayTime     = array(
            array('dayTime' => '2017-11-14', 'max' => 51, 'min' => 51),
            array('dayTime' => '2017-11-15', 'max' => 3019, 'min' => 3019),
            array('dayTime' => '2017-11-16', 'max' => 5886, 'min' => 5886),
            array('dayTime' => '2017-11-17', 'max' => 10448, 'min' => 10448),
            array('dayTime' => '2017-11-18', 'max' => 13801, 'min' => 13801),
            array('dayTime' => '2017-11-19', 'max' => 17004, 'min' => 17004),
        );

        $data = array();
        foreach ($dayTime as $k => $v) {
            for ($i = 0; $i < $v['max']; $i++) {
                $arr['userCode']      = 'UserTestIos' . str_replace('-', '', $v['dayTime']) . str_pad($i + 1, 8, '0', STR_PAD_LEFT);
                if ($i >= $v['min']) {
                    $arr['udid'] = 'DeviceTestIos' . str_replace('-', '', date('Y-m-d', strtotime($v['dayTime'] . ' -1 day'))) . str_pad($i + 1, 8, '0', STR_PAD_LEFT);
                } else {
                    $arr['udid'] = 'DeviceTestIos' . str_replace('-', '', $v['dayTime']) . str_pad($i + 1, 8, '0', STR_PAD_LEFT);
                }
                $arr['game_id']       = '104';
                $arr['roleId']        = '1';
                $arr['roleName']      = 'test';
                $arr['agent']         = 'cqxyIOS';
                $arr['regAgent']      = 'cqxyIOS';
                $arr['serverId']      = '1';
                $arr['loginServerId'] = '1';
                $arr['serverName']    = '1';
                $arr['level']         = '1';
                $arr['currency']      = '1';
                $arr['vip']           = '1';
                $arr['balance']       = '1';
                $arr['power']         = '1';
                $arr['regTime']       = strtotime($v['dayTime'] . " 12:00:00");
                $arr['time']          = strtotime(date('Y-m-d', strtotime($v['dayTime'] . ' +3 day')) . " 12:00:00");
                $data[]               = $arr;

            }
        }

        return $data;
    }

    public function create7LoginData()
    {

        $this->table = 'nl_role_login';

        //注册日期  登陆日期        用户数 设备数
        //2017/11/7     2017/11/13      1933    1933
        //2017/11/8     2017/11/14      2201    2201
        //2017/11/9     2017/11/15      2461    2461
        //2017/11/10    2017/11/16      3370    3370
        //2017/11/11    2017/11/17      3299    3299
        //2017/11/12    2017/11/18      4069    4069
        //2017/11/13    2017/11/19      4671    4671
        //2017/11/14    2017/11/20      3862    3862
        //2017/11/15    2017/11/21      4759    4759
        //2017/11/16    2017/11/22      3946    3946

        $dayTime     = array(
            array('dayTime' => '2017-11-07', 'max' => 1933, 'min' => 1933),
            array('dayTime' => '2017-11-08', 'max' => 2201, 'min' => 2201),
            array('dayTime' => '2017-11-09', 'max' => 2461, 'min' => 2461),
            array('dayTime' => '2017-11-10', 'max' => 3370, 'min' => 3370),
            array('dayTime' => '2017-11-11', 'max' => 3299, 'min' => 3299),
            array('dayTime' => '2017-11-12', 'max' => 4069, 'min' => 4069),
            array('dayTime' => '2017-11-13', 'max' => 4671, 'min' => 4671),
            array('dayTime' => '2017-11-14', 'max' => 3862, 'min' => 3862),
            array('dayTime' => '2017-11-15', 'max' => 4759, 'min' => 4759),
            array('dayTime' => '2017-11-16', 'max' => 3946, 'min' => 3946),
        );

        $data = array();
        foreach ($dayTime as $k => $v) {
            for ($i = 0; $i < $v['max']; $i++) {
                $arr['userCode']      = 'UserTestIos' . str_replace('-', '', $v['dayTime']) . str_pad($i + 1, 8, '0', STR_PAD_LEFT);
                if ($i >= $v['min']) {
                    $arr['udid'] = 'DeviceTestIos' . str_replace('-', '', date('Y-m-d', strtotime($v['dayTime'] . ' -1 day'))) . str_pad($i + 1, 8, '0', STR_PAD_LEFT);
                } else {
                    $arr['udid'] = 'DeviceTestIos' . str_replace('-', '', $v['dayTime']) . str_pad($i + 1, 8, '0', STR_PAD_LEFT);
                }
                $arr['game_id']       = '104';
                $arr['roleId']        = '1';
                $arr['roleName']      = 'test';
                $arr['agent']         = 'cqxyIOS';
                $arr['regAgent']      = 'cqxyIOS';
                $arr['serverId']      = '1';
                $arr['loginServerId'] = '1';
                $arr['serverName']    = '1';
                $arr['level']         = '1';
                $arr['currency']      = '1';
                $arr['vip']           = '1';
                $arr['balance']       = '1';
                $arr['power']         = '1';
                $arr['regTime']       = strtotime($v['dayTime'] . " 12:00:00");
                $arr['time']          = strtotime(date('Y-m-d', strtotime($v['dayTime'] . ' +6 day')) . " 12:00:00");
                $data[]               = $arr;

            }
        }

        return $data;
    }

    public function create14LoginData()
    {
        //注册日期      登陆日期            用户数 设备数
        //2017/11/1     2017/11/14      387     387
        //2017/11/2     2017/11/15      1553    1553
        //2017/11/3     2017/11/16      905     905
        //2017/11/4     2017/11/17      708     708
        //2017/11/5     2017/11/18      1064    1064
        //2017/11/6     2017/11/19      1087    1087
        //2017/11/7     2017/11/20      971     971
        //2017/11/8     2017/11/21      1352    1352
        //2017/11/9     2017/11/22      1421    1421

        $this->table = 'nl_role_login';
        $dayTime     = array(
            array('dayTime' => '2017-11-01', 'max' => 387, 'min' => 387),
            array('dayTime' => '2017-11-02', 'max' => 1553, 'min' => 1553),
            array('dayTime' => '2017-11-03', 'max' => 905, 'min' => 905),
            array('dayTime' => '2017-11-04', 'max' => 708, 'min' => 708),
            array('dayTime' => '2017-11-05', 'max' => 1064, 'min' => 1064),
            array('dayTime' => '2017-11-06', 'max' => 1087, 'min' => 1087),
            array('dayTime' => '2017-11-07', 'max' => 971, 'min' => 971),
            array('dayTime' => '2017-11-08', 'max' => 1352, 'min' => 1352),
            array('dayTime' => '2017-11-09', 'max' => 1421, 'min' => 1421),
        );

        $data = array();
        foreach ($dayTime as $k => $v) {
            for ($i = 0; $i < $v['max']; $i++) {
                $arr['userCode']      = 'UserTestIos' . str_replace('-', '', $v['dayTime']) . str_pad($i + 1, 8, '0', STR_PAD_LEFT);
                if ($i >= $v['min']) {
                    $arr['udid'] = 'DeviceTestIos' . str_replace('-', '', date('Y-m-d', strtotime($v['dayTime'] . ' -1 day'))) . str_pad($i + 1, 8, '0', STR_PAD_LEFT);
                } else {
                    $arr['udid'] = 'DeviceTestIos' . str_replace('-', '', $v['dayTime']) . str_pad($i + 1, 8, '0', STR_PAD_LEFT);
                }
                $arr['game_id']       = '104';
                $arr['roleId']        = '1';
                $arr['roleName']      = 'test';
                $arr['agent']         = 'cqxyIOS';
                $arr['regAgent']      = 'cqxyIOS';
                $arr['serverId']      = '1';
                $arr['loginServerId'] = '1';
                $arr['serverName']    = '1';
                $arr['level']         = '1';
                $arr['currency']      = '1';
                $arr['vip']           = '1';
                $arr['balance']       = '1';
                $arr['power']         = '1';
                $arr['regTime']       = strtotime($v['dayTime'] . " 12:00:00");
                $arr['time']          = strtotime(date('Y-m-d', strtotime($v['dayTime'] . ' +13 day')) . " 12:00:00");
                $data[]               = $arr;

            }
        }

        return $data;
    }

    public function createNewOrderData()
    {
        $this->table = 'lg_order';

//              新用户充值   新用户充值数
        //金额，单数
        $list[] = array('dayTime' => '2017-11-16', 'data' => array(
            1 => 31,
            6 => 1067,
            68 => 841,
            98 => 183,
            198 => 87,
            328 => 25,
            648 => 5,
        ));

        //金额，单数
        $list[] = array('dayTime' => '2017-11-17', 'data' => array(
            1 => 2,
            6 => 563,
            68 => 1090,
            98 => 413,
            198 => 126,
            328 => 43,
            648 => 7,
        ));

        //    //金额，单数
        $list[] = array('dayTime' => '2017-11-18', 'data' => array(
            1 => 2,
            6 => 574,
            68 => 514,
            98 => 319,
            198 => 43,
            328 => 17,
            648 => 3,
        ));

        //    //金额，单数
        $list[] = array('dayTime' => '2017-11-19', 'data' => array(
            1 => 0,
            6 => 780,
            68 => 715,
            98 => 401,
            198 => 103,
            328 => 29,
            648 => 6,
        ));

        //    //金额，单数
        $list[] = array('dayTime' => '2017-11-20', 'data' => array(
            1 => 4,
            6 => 685,
            68 => 486,
            98 => 257,
            198 => 55,
            328 => 15,
            648 => 4,
        ));

        //    //金额，单数
        $list[] = array('dayTime' => '2017-11-21', 'data' => array(
            1 => 2,
            6 => 637,
            68 => 439,
            98 => 157,
            198 => 41,
            328 => 15,
            648 => 3,
        ));

        //    //金额，单数
        $list[] = array('dayTime' => '2017-11-22', 'data' => array(
            1 => 0,
            6 => 638,
            68 => 431,
            98 => 187,
            198 => 51,
            328 => 22,
            648 => 8,
        ));

        $data = array();
        foreach ($list as $k => $v) {
            $dayTime = $v['dayTime'];
            $datas   = $v['data'];
            $key     = 0;
            foreach ($datas as $k2 => $v2) {
                for ($i = 0; $i < $v2; $i++) {
                    $arr['orderId']         = 'OrderTestIos' . str_replace('-', '', $dayTime) . $k2 . str_pad($i + 1, 6, '0', STR_PAD_LEFT);
                    $arr['billNo']          = 'OrderTestIos' . str_replace('-', '', $dayTime) . $k2 . str_pad($i + 1, 6, '0', STR_PAD_LEFT);
                    $arr['tranId']          = 'OrderTestIos' . str_replace('-', '', $dayTime) . $k2 . str_pad($i + 1, 6, '0', STR_PAD_LEFT);
                    $arr['userCode']        = 'UserTestIos' . str_replace('-', '', $dayTime) . str_pad($key + 1, 8, '0', STR_PAD_LEFT);
                    $arr['userName']        = 'UserTestIos' . str_replace('-', '', $dayTime) . str_pad($key + 1, 8, '0', STR_PAD_LEFT);
                    $arr['agent']           = 'cqxyIOS';
                    $arr['game_id']         = '104';
                    $arr['gameName']        = '超强学院';
                    $arr['channel_id']      = '1';
                    $arr['channelName']     = '创娱';
                    $arr['amount']          = $k2; //金额
                    $arr['goodsCode']       = 'cqxy' . $arr['amount'];
                    $arr['subject']         = '商品';
                    $arr['roleId']          = '1';
                    $arr['roleName']        = '1';
                    $arr['serverId']        = '1';
                    $arr['serverName']      = '1';
                    $arr['level']           = '1';
                    $arr['vip']             = '1';
                    $arr['extraInfo']       = '1';
                    $arr['ip']              = '127.0.0.1';
                    $arr['city']            = '';
                    $arr['province']        = '';
                    $arr['udid']            = 'DeviceTestIos' . str_replace('-', '', $dayTime) . str_pad($key + 1, 8, '0', STR_PAD_LEFT);
                    $arr['orderStatus']     = '0';
                    $arr['gameOrderStatus'] = '0';
                    $arr['createTime']      = $arr['paymentTime']      = $arr['callbackTime']      = strtotime($dayTime . " 12:00:00");
                    $arr['num']             = 1;
                    $arr['res']             = 'success';
                    $arr['platform_id']     = 1;
                    $arr['payType']         = 0;
                    $arr['orderType']       = 0;
                    $arr['advter_id']       = '';
                    $arr['idfa']            = '00000000-0000-0000-000000000000';
                    $arr['idfv']            = '00000000-0000-0000-000000000000';
                    $arr['imei']            = '';
                    $arr['imei2']           = '';
                    $arr['type']            = 2;
                    $arr['regTime']         = strtotime($dayTime . " 12:00:00");
                    $arr['regAgent']        = 'cqxyIOS';
                    $arr['spUrl']           = '';
                    $data[]                 = $arr;
                    $key++;
                }
            }
        }
        return $data;
    }

    public function createOldOrderData()
    {

        $this->table = 'lg_order';

        //金额，单数
        $list[] = array('dayTime' => '2017-11-16', 'data' => array(
            1 => 12,
            6 => 1678,
            68 => 1083,
            98 => 790,
            198 => 518,
            328 => 147,
            648 => 123,
        ));

        //    //金额，单数
        $list[] = array('dayTime' => '2017-11-17', 'data' => array(
            1 => 22,
            6 => 1799,
            68 => 1102,
            98 => 803,
            198 => 545,
            328 => 145,
            648 => 139,
        ));

        //    //金额，单数
        $list[] = array('dayTime' => '2017-11-18', 'data' => array(
            1 => 1,
            6 => 2065,
            68 => 1186,
            98 => 891,
            198 => 629,
            328 => 186,
            648 => 144,
        ));

        //    //金额，单数
        $list[] = array('dayTime' => '2017-11-19', 'data' => array(
            1 => 12,
            6 => 2069,
            68 => 1523,
            98 => 923,
            198 => 631,
            328 => 189,
            648 => 157,
        ));

        //    //金额，单数
        $list[] = array('dayTime' => '2017-11-20', 'data' => array(
            1 => 13,
            6 => 2110,
            68 => 1525,
            98 => 923,
            198 => 635,
            328 => 185,
            648 => 155,
        ));

        //    //金额，单数
        $list[] = array('dayTime' => '2017-11-21', 'data' => array(
            1 => 9,
            6 => 2113,
            68 => 1525,
            98 => 923,
            198 => 635,
            328 => 194,
            648 => 162,
        ));

        //    //金额，单数
        $list[] = array('dayTime' => '2017-11-22', 'data' => array(
            1 => 6,
            6 => 2096,
            68 => 1511,
            98 => 913,
            198 => 632,
            328 => 171,
            648 => 155,
        ));

        $data = array();
        foreach ($list as $k => $v) {
            $dayTime = $v['dayTime'];
            $datas   = $v['data'];
            $key     = 0;
            foreach ($datas as $k2 => $v2) {
                for ($i = 0; $i < $v2; $i++) {
                    $arr['orderId']         = 'OrderTestIos2' . str_replace('-', '', $dayTime) . $k2 . str_pad($i + 1, 6, '0', STR_PAD_LEFT);
                    $arr['billNo']          = 'OrderTestIos2' . str_replace('-', '', $dayTime) . $k2 . str_pad($i + 1, 6, '0', STR_PAD_LEFT);
                    $arr['tranId']          = 'OrderTestIos2' . str_replace('-', '', $dayTime) . $k2 . str_pad($i + 1, 6, '0', STR_PAD_LEFT);
                    $arr['userCode']        = 'UserTestIos' . str_replace('-', '', date('Y-m-d', strtotime($dayTime . ' -1 day'))) . str_pad($key + 1, 8, '0', STR_PAD_LEFT);
                    $arr['userName']        = 'UserTestIos' . str_replace('-', '', date('Y-m-d', strtotime($dayTime . ' -1 day'))) . str_pad($key + 1, 8, '0', STR_PAD_LEFT);
                    $arr['agent']           = 'cqxyIOS';
                    $arr['game_id']         = '104';
                    $arr['gameName']        = '超强学院';
                    $arr['channel_id']      = '1';
                    $arr['channelName']     = '创娱';
                    $arr['amount']          = $k2; //金额
                    $arr['goodsCode']       = 'cqxy' . $arr['amount'];
                    $arr['subject']         = '商品';
                    $arr['roleId']          = '1';
                    $arr['roleName']        = '1';
                    $arr['serverId']        = '1';
                    $arr['serverName']      = '1';
                    $arr['level']           = '1';
                    $arr['vip']             = '1';
                    $arr['extraInfo']       = '1';
                    $arr['ip']              = '127.0.0.1';
                    $arr['city']            = '';
                    $arr['province']        = '';
                    $arr['udid']            = 'DeviceTestIos' . str_replace('-', '', date('Y-m-d', strtotime($dayTime . ' -1 day'))) . str_pad($key + 1, 8, '0', STR_PAD_LEFT);
                    $arr['orderStatus']     = '0';
                    $arr['gameOrderStatus'] = '0';
                    $arr['createTime']      = $arr['paymentTime']      = $arr['callbackTime']      = strtotime($dayTime . " 12:00:00");
                    $arr['num']             = 1;
                    $arr['res']             = 'success';
                    $arr['platform_id']     = 1;
                    $arr['payType']         = 0;
                    $arr['orderType']       = 0;
                    $arr['advter_id']       = '';
                    $arr['idfa']            = '00000000-0000-0000-000000000000';
                    $arr['idfv']            = '00000000-0000-0000-000000000000';
                    $arr['imei']            = '';
                    $arr['imei2']           = '';
                    $arr['type']            = 2;
                    $arr['regTime']         = strtotime(date('Y-m-d', strtotime($dayTime . ' -1 day')) . " 12:00:00");
                    $arr['regAgent']        = 'cqxyIOS';
                    $arr['spUrl']           = '';
                    $data[]                 = $arr;
                    $key++;
                }
            }
        }
        return $data;
    }

    /**
     * 用户在线信息统计
     */
    private function onlineCount()
    {
        $fun = array(
            // 'createDeviceData',
            // 'createUserData',
            // 'createNewLoginData',
            // 'create2LoginData',
            // 'create3LoginData',
            // 'create4LoginData',
            // 'create7LoginData',
            // 'create14LoginData',
            // 'createNewOrderData',
             'createOldOrderData'
            );
        foreach ($fun as $key => $value) {

            $data = $this->$value();
            //开启事务
            $this->conn->autocommit(false);
            foreach ($data as $k => $v) {
                /*$this->arr[$v['agent'].'_'.$v['game_id'].'_'.$v['serverId'].'_'.$v['serverName']]['num'] += 1;*/
                $key = implode(',', array_keys($v));
                $val = implode("','", array_values($v));
                $sql = "insert into lgame.`" . $this->table . "`(" . $key . ") values('" . $val . "');";
                //入库用户在线数据
                $this->sql .= $sql;

                if ($this->num >= $this->limit) {

                    //每5000条提交一次
                    if (false !== mysqli_multi_query($this->conn, $this->sql)) {
                        $this->sql = '';
                        $this->num = 0;
                        //事务提交
                        $this->conn->commit();
                        //释放结果集
                        while (mysqli_next_result($this->conn) && mysqli_more_results($this->conn)) {

                        }
                    } else {
                        die(mysqli_error($this->conn));
                        continue;
                    }
                }

                $this->num++;
            } //end while

            //不够5000条重新提交一次
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
                    continue;
                }

            }

            //事务关闭
            $this->conn->autocommit(true);
            unset($data);
        }
    }

}

$obj = new dataInsertIOS();
