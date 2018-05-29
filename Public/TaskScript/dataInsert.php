<?php
/**
 * 角色在线信息异步入库
 */
require_once dirname(__FILE__) . '/public/Connect.php';

class dataInsert extends Connect
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
        ini_set('memory_limit', '1024M');
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
        //2017/11/16        13284
        //2017/11/17        14749
        //2017/11/18        14332
        //2017/11/19        13670
        //2017/11/20        13257
        //2017/11/21        13372
        //2017/11/22        13729

        $dayTime     = array(
            '2017-11-16' => 13284,
            '2017-11-17' => 14749,
            '2017-11-18' => 14332,
            '2017-11-19' => 13670,
            '2017-11-20' => 13257,
            '2017-11-21' => 13372,
            '2017-11-22' => 13729,
        );
        $data        = array();
        foreach ($dayTime as $k => $v) {
            for ($i = 0; $i < $v; $i++) {
                $arr['udid']       = 'DeviceTest' . str_replace('-', '', $k) . str_pad($i + 1, 8, '0', STR_PAD_LEFT);
                $arr['game_id']    = '104';
                $arr['agent']      = 'cqxyAND';
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
        //2017/11/16        14021   12381       1640
        //2017/11/17        14821   13087       1734
        //2017/11/18        14762   13035       1727
        //2017/11/19        13981   12345       1636
        //2017/11/20        13054   11527       1527
        //2017/11/21        14088   12440       1648
        //2017/11/22        14281   12610       1671

        $dayTime     = array(
            array('dayTime' => '2017-11-16', 'max' => 14021, 'min' => 12381),
            array('dayTime' => '2017-11-17', 'max' => 14821, 'min' => 13087),
            array('dayTime' => '2017-11-18', 'max' => 14762, 'min' => 13035),
            array('dayTime' => '2017-11-19', 'max' => 13981, 'min' => 12345),
            array('dayTime' => '2017-11-20', 'max' => 13054, 'min' => 11527),
            array('dayTime' => '2017-11-21', 'max' => 14088, 'min' => 12440),
            array('dayTime' => '2017-11-22', 'max' => 14281, 'min' => 12610),
        );

        $data = array();
        foreach ($dayTime as $k => $v) {
            for ($i = 0; $i < $v['max']; $i++) {
                $arr['userCode'] = 'UserTest' . str_replace('-', '', $v['dayTime']) . str_pad($i + 1, 8, '0', STR_PAD_LEFT);
                if ($i >= $v['min']) {
                    $arr['udid'] = 'DeviceTest' . str_replace('-', '', date('Y-m-d', strtotime($v['dayTime'] . ' -1 day'))) . str_pad($i + 1, 8, '0', STR_PAD_LEFT);
                } else {
                    $arr['udid'] = 'DeviceTest' . str_replace('-', '', $v['dayTime']) . str_pad($i + 1, 8, '0', STR_PAD_LEFT);
                }
                $arr['userName']          = $arr['userCode'];
                $arr['game_id']           = '104';
                $arr['agent']             = 'cqxyAND';
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
                $arr['lastAgent']         = 'cqxyAND';
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
        //新用户登录
        //注册日期          用户数 设备数
        //2017/11/14        17649   15937
        //2017/11/15        15479   13668
        //2017/11/16        14021   12381
        //2017/11/17        14821   13087
        //2017/11/18        14762   13035
        //2017/11/19        13981   12345
        //2017/11/20        13054   11527
        //2017/11/21        14088   12440
        //2017/11/22        14281   12610

        $dayTime     = array(
            array('dayTime' => '2017-11-14', 'max' => 17649, 'min' => 15937),
            array('dayTime' => '2017-11-15', 'max' => 15479, 'min' => 13668),
            array('dayTime' => '2017-11-16', 'max' => 14021, 'min' => 12381),
            array('dayTime' => '2017-11-17', 'max' => 14821, 'min' => 13087),
            array('dayTime' => '2017-11-18', 'max' => 14762, 'min' => 13035),
            array('dayTime' => '2017-11-19', 'max' => 13981, 'min' => 12345),
            array('dayTime' => '2017-11-20', 'max' => 13054, 'min' => 11527),
            array('dayTime' => '2017-11-21', 'max' => 14088, 'min' => 12440),
            array('dayTime' => '2017-11-22', 'max' => 14281, 'min' => 12610),
        );

        $data = array();
        foreach ($dayTime as $k => $v) {
            for ($i = 0; $i < $v['max']; $i++) {
                $arr['userCode'] = 'UserTest' . str_replace('-', '', $v['dayTime']) . str_pad($i + 1, 8, '0', STR_PAD_LEFT);
                if ($i >= $v['min']) {
                    $arr['udid'] = 'DeviceTest' . str_replace('-', '', date('Y-m-d', strtotime($v['dayTime'] . ' -1 day'))) . str_pad($i + 1, 8, '0', STR_PAD_LEFT);
                } else {
                    $arr['udid'] = 'DeviceTest' . str_replace('-', '', $v['dayTime']) . str_pad($i + 1, 8, '0', STR_PAD_LEFT);
                }
                $arr['game_id']       = '104';
                $arr['roleId']        = '1';
                $arr['roleName']      = 'test';
                $arr['agent']         = 'cqxyAND';
                $arr['regAgent']      = 'cqxyAND';
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
    //                $arr['userCode'] = 'UserTest' . str_replace('-', '', $v['dayTime']) . str_pad($i + 1, 8, '0', STR_PAD_LEFT);
    //                $arr['udid'] = 'DeviceTest' . str_replace('-', '', $v['dayTime']) . str_pad($i + 1, 8, '0', STR_PAD_LEFT);
    //                $arr['game_id']       = '104';
    //                $arr['roleId']        = '1';
    //                $arr['roleName']      = 'test';
    //                $arr['agent']         = 'cqxyAND';
    //                $arr['regAgent']      = 'cqxyAND';
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
        //2017/11/13    2017/11/14      4131    4131
        //2017/11/14    2017/11/15      4959    4959
        //2017/11/15    2017/11/16      4326    4326
        //2017/11/16    2017/11/17      4129    4129
        //2017/11/17    2017/11/18      4088    4088
        //2017/11/18    2017/11/19      4204    4204
        //2017/11/19    2017/11/20      4298    4298
        //2017/11/20    2017/11/21      3801    3801
        //2017/11/21    2017/11/22      4291    4291

        $dayTime     = array(
            array('dayTime' => '2017-11-13', 'max' => 4131, 'min' => 4131),
            array('dayTime' => '2017-11-14', 'max' => 4959, 'min' => 4959),
            array('dayTime' => '2017-11-15', 'max' => 4326, 'min' => 4326),
            array('dayTime' => '2017-11-16', 'max' => 4129, 'min' => 4129),
            array('dayTime' => '2017-11-17', 'max' => 4088, 'min' => 4088),
            array('dayTime' => '2017-11-18', 'max' => 4204, 'min' => 4204),
            array('dayTime' => '2017-11-19', 'max' => 4298, 'min' => 4298),
            array('dayTime' => '2017-11-20', 'max' => 3801, 'min' => 3801),
            array('dayTime' => '2017-11-21', 'max' => 4291, 'min' => 4291),
        );
        $data = array();
        foreach ($dayTime as $k => $v) {
            for ($i = 0; $i < $v['max']; $i++) {
                $arr['userCode']      = 'UserTest' . str_replace('-', '', $v['dayTime']) . str_pad($i + 1, 8, '0', STR_PAD_LEFT);
                if ($i >= $v['min']) {
                    $arr['udid'] = 'DeviceTest' . str_replace('-', '', date('Y-m-d', strtotime($v['dayTime'] . ' -1 day'))) . str_pad($i + 1, 8, '0', STR_PAD_LEFT);
                } else {
                    $arr['udid'] = 'DeviceTest' . str_replace('-', '', $v['dayTime']) . str_pad($i + 1, 8, '0', STR_PAD_LEFT);
                }
                $arr['game_id']       = '104';
                $arr['roleId']        = '1';
                $arr['roleName']      = 'test';
                $arr['agent']         = 'cqxyAND';
                $arr['regAgent']      = 'cqxyAND';
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
        //2017/11/12    2017/11/14      16649   14252
        //2017/11/13    2017/11/15      15214   13434
        //2017/11/14    2017/11/16      17649   15937
        //2017/11/15    2017/11/17      15479   13668
        //2017/11/16    2017/11/18      14021   12381
        //2017/11/17    2017/11/19      14821   13087
        //2017/11/18    2017/11/20      14762   13035
        //2017/11/19    2017/11/21      13981   12345
        //2017/11/20    2017/11/22      13054   11527

        $dayTime     = array(
            array('dayTime' => '2017-11-12', 'max' => 16649, 'min' => 14252),
            array('dayTime' => '2017-11-13', 'max' => 15214, 'min' => 13434),
            array('dayTime' => '2017-11-14', 'max' => 17649, 'min' => 15937),
            array('dayTime' => '2017-11-15', 'max' => 15479, 'min' => 13668),
            array('dayTime' => '2017-11-16', 'max' => 14021, 'min' => 12381),
            array('dayTime' => '2017-11-17', 'max' => 14821, 'min' => 13087),
            array('dayTime' => '2017-11-18', 'max' => 14762, 'min' => 13035),
            array('dayTime' => '2017-11-19', 'max' => 13981, 'min' => 12345),
            array('dayTime' => '2017-11-20', 'max' => 13054, 'min' => 11527),
        );

        $data = array();
        foreach ($dayTime as $k => $v) {
            for ($i = 0; $i < $v['max']; $i++) {
                $arr['userCode']      = 'UserTest' . str_replace('-', '', $v['dayTime']) . str_pad($i + 1, 8, '0', STR_PAD_LEFT);
                if ($i >= $v['min']) {
                    $arr['udid'] = 'DeviceTest' . str_replace('-', '', date('Y-m-d', strtotime($v['dayTime'] . ' -1 day'))) . str_pad($i + 1, 8, '0', STR_PAD_LEFT);
                } else {
                    $arr['udid'] = 'DeviceTest' . str_replace('-', '', $v['dayTime']) . str_pad($i + 1, 8, '0', STR_PAD_LEFT);
                }
                $arr['game_id']       = '104';
                $arr['roleId']        = '1';
                $arr['roleName']      = 'test';
                $arr['agent']         = 'cqxyAND';
                $arr['regAgent']      = 'cqxyAND';
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
        //2017/11/11    2017/11/14      7979    7979
        //2017/11/12    2017/11/15      5272    5272
        //2017/11/13    2017/11/16      5423    5423
        //2017/11/14    2017/11/17      8764    8764
        //2017/11/15    2017/11/18      7295    7295
        //2017/11/16    2017/11/19      6614    6614
        //2017/11/17    2017/11/20      6053    6053
        //2017/11/18    2017/11/21      6318    6318
        //2017/11/19    2017/11/22      6707    6707

        $dayTime     = array(
            array('dayTime' => '2017-11-11', 'max' => 7979, 'min' => 7979),
            array('dayTime' => '2017-11-12', 'max' => 5272, 'min' => 5272),
            array('dayTime' => '2017-11-13', 'max' => 5423, 'min' => 5423),
            array('dayTime' => '2017-11-14', 'max' => 8764, 'min' => 8764),
            array('dayTime' => '2017-11-15', 'max' => 7295, 'min' => 7295),
            array('dayTime' => '2017-11-16', 'max' => 6614, 'min' => 6614),
            array('dayTime' => '2017-11-17', 'max' => 6053, 'min' => 6053),
            array('dayTime' => '2017-11-18', 'max' => 6318, 'min' => 6318),
            array('dayTime' => '2017-11-19', 'max' => 6707, 'min' => 6707),
        );

        $data = array();
        foreach ($dayTime as $k => $v) {
            for ($i = 0; $i < $v['max']; $i++) {
                $arr['userCode']      = 'UserTest' . str_replace('-', '', $v['dayTime']) . str_pad($i + 1, 8, '0', STR_PAD_LEFT);
                if ($i >= $v['min']) {
                    $arr['udid'] = 'DeviceTest' . str_replace('-', '', date('Y-m-d', strtotime($v['dayTime'] . ' -1 day'))) . str_pad($i + 1, 8, '0', STR_PAD_LEFT);
                } else {
                    $arr['udid'] = 'DeviceTest' . str_replace('-', '', $v['dayTime']) . str_pad($i + 1, 8, '0', STR_PAD_LEFT);
                }
                $arr['game_id']       = '104';
                $arr['roleId']        = '1';
                $arr['roleName']      = 'test';
                $arr['agent']         = 'cqxyAND';
                $arr['regAgent']      = 'cqxyAND';
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

        //注册日期      登陆日期            用户数 设备数
        //2017/11/8     2017/11/14      1645    1645
        //2017/11/9     2017/11/15      1750    1750
        //2017/11/10    2017/11/16      1601    1601
        //2017/11/11    2017/11/17      1762    1762
        //2017/11/12    2017/11/18      1355    1355
        //2017/11/13    2017/11/19      1389    1389
        //2017/11/14    2017/11/20      1516    1516
        //2017/11/15    2017/11/21      1257    1257
        //2017/11/16    2017/11/22      1151    1151

        $dayTime     = array(
            array('dayTime' => '2017-11-08', 'max' => 1645, 'min' => 1645),
            array('dayTime' => '2017-11-09', 'max' => 1750, 'min' => 1750),
            array('dayTime' => '2017-11-10', 'max' => 1601, 'min' => 1601),
            array('dayTime' => '2017-11-11', 'max' => 1762, 'min' => 1762),
            array('dayTime' => '2017-11-12', 'max' => 1355, 'min' => 1355),
            array('dayTime' => '2017-11-13', 'max' => 1389, 'min' => 1389),
            array('dayTime' => '2017-11-14', 'max' => 1516, 'min' => 1516),
            array('dayTime' => '2017-11-15', 'max' => 1257, 'min' => 1257),
            array('dayTime' => '2017-11-16', 'max' => 1151, 'min' => 1151),
        );

        $data = array();
        foreach ($dayTime as $k => $v) {
            for ($i = 0; $i < $v['max']; $i++) {
                $arr['userCode']      = 'UserTest' . str_replace('-', '', $v['dayTime']) . str_pad($i + 1, 8, '0', STR_PAD_LEFT);
                if ($i >= $v['min']) {
                    $arr['udid'] = 'DeviceTest' . str_replace('-', '', date('Y-m-d', strtotime($v['dayTime'] . ' -1 day'))) . str_pad($i + 1, 8, '0', STR_PAD_LEFT);
                } else {
                    $arr['udid'] = 'DeviceTest' . str_replace('-', '', $v['dayTime']) . str_pad($i + 1, 8, '0', STR_PAD_LEFT);
                }
                $arr['game_id']       = '104';
                $arr['roleId']        = '1';
                $arr['roleName']      = 'test';
                $arr['agent']         = 'cqxyAND';
                $arr['regAgent']      = 'cqxyAND';
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
        $this->table = 'nl_role_login';

        //注册日期      登陆日期            用户数 设备数
        //2017/11/1     2017/11/14      1496    1496
        //2017/11/2     2017/11/15      1299    1299
        //2017/11/3     2017/11/16      919     919
        //2017/11/4     2017/11/17      1037    1037
        //2017/11/5     2017/11/18      1083    1083
        //2017/11/6     2017/11/19      1117    1117
        //2017/11/7     2017/11/20      1135    1135
        //2017/11/8     2017/11/21      914     914
        //2017/11/9     2017/11/22      1064    1064

        $dayTime     = array(
            array('dayTime' => '2017-11-01', 'max' => 1496, 'min' => 1496),
            array('dayTime' => '2017-11-02', 'max' => 1299, 'min' => 1299),
            array('dayTime' => '2017-11-03', 'max' => 919, 'min' => 919),
            array('dayTime' => '2017-11-04', 'max' => 1037, 'min' => 1037),
            array('dayTime' => '2017-11-05', 'max' => 1083, 'min' => 1083),
            array('dayTime' => '2017-11-06', 'max' => 1117, 'min' => 1117),
            array('dayTime' => '2017-11-07', 'max' => 1135, 'min' => 1135),
            array('dayTime' => '2017-11-08', 'max' => 914, 'min' => 914),
            array('dayTime' => '2017-11-09', 'max' => 1064, 'min' => 1064),
        );

        $data = array();
        foreach ($dayTime as $k => $v) {
            for ($i = 0; $i < $v['max']; $i++) {
                $arr['userCode']      = 'UserTest' . str_replace('-', '', $v['dayTime']) . str_pad($i + 1, 8, '0', STR_PAD_LEFT);
                if ($i >= $v['min']) {
                    $arr['udid'] = 'DeviceTest' . str_replace('-', '', date('Y-m-d', strtotime($v['dayTime'] . ' -1 day'))) . str_pad($i + 1, 8, '0', STR_PAD_LEFT);
                } else {
                    $arr['udid'] = 'DeviceTest' . str_replace('-', '', $v['dayTime']) . str_pad($i + 1, 8, '0', STR_PAD_LEFT);
                }
                $arr['game_id']       = '104';
                $arr['roleId']        = '1';
                $arr['roleName']      = 'test';
                $arr['agent']         = 'cqxyAND';
                $arr['regAgent']      = 'cqxyAND';
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

    public function create30LoginData()
    {
        $this->table = 'nl_role_login';

        //注册日期      登陆日期            用户数 设备数
        //2017/10/23    2017/11/21      339     339
        //2017/10/24    2017/11/22      226     226

        $dayTime     = array(
            array('dayTime' => '2017-10-23', 'max' => 339, 'min' => 339),
            array('dayTime' => '2017-10-24', 'max' => 226, 'min' => 226),
        );

        $data = array();
        foreach ($dayTime as $k => $v) {
            for ($i = 0; $i < $v['max']; $i++) {
                $arr['userCode']      = 'UserTest' . str_replace('-', '', $v['dayTime']) . str_pad($i + 1, 8, '0', STR_PAD_LEFT);
                if ($i >= $v['min']) {
                    $arr['udid'] = 'DeviceTest' . str_replace('-', '', date('Y-m-d', strtotime($v['dayTime'] . ' -1 day'))) . str_pad($i + 1, 8, '0', STR_PAD_LEFT);
                } else {
                    $arr['udid'] = 'DeviceTest' . str_replace('-', '', $v['dayTime']) . str_pad($i + 1, 8, '0', STR_PAD_LEFT);
                }
                $arr['game_id']       = '104';
                $arr['roleId']        = '1';
                $arr['roleName']      = 'test';
                $arr['agent']         = 'cqxyAND';
                $arr['regAgent']      = 'cqxyAND';
                $arr['serverId']      = '1';
                $arr['loginServerId'] = '1';
                $arr['serverName']    = '1';
                $arr['level']         = '1';
                $arr['currency']      = '1';
                $arr['vip']           = '1';
                $arr['balance']       = '1';
                $arr['power']         = '1';
                $arr['regTime']       = strtotime($v['dayTime'] . " 12:00:00");
                $arr['time']          = strtotime(date('Y-m-d', strtotime($v['dayTime'] . ' +29 day')) . " 12:00:00");
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
            1 => 0,
            6 => 309,
            68 => 161,
            98 => 168,
            198 => 33,
            328 => 9,
            648 => 3,
        ));

        //金额，单数
        $list[] = array('dayTime' => '2017-11-17', 'data' => array(
            1 => 3,
            6 => 279,
            68 => 191,
            98 => 112,
            198 => 40,
            328 => 9,
            648 => 6,
        ));

        //    //金额，单数
        $list[] = array('dayTime' => '2017-11-18', 'data' => array(
            1 => 1,
            6 => 294,
            68 => 221,
            98 => 108,
            198 => 13,
            328 => 6,
            648 => 1,
        ));

        //    //金额，单数
        $list[] = array('dayTime' => '2017-11-19', 'data' => array(
            1 => 1,
            6 => 266,
            68 => 219,
            98 => 134,
            198 => 37,
            328 => 7,
            648 => 4,
        ));

        //    //金额，单数
        $list[] = array('dayTime' => '2017-11-20', 'data' => array(
            1 => 0,
            6 => 264,
            68 => 217,
            98 => 104,
            198 => 46,
            328 => 6,
            648 => 5,
        ));

        //    //金额，单数
        $list[] = array('dayTime' => '2017-11-21', 'data' => array(
            1 => 7,
            6 => 365,
            68 => 236,
            98 => 71,
            198 => 23,
            328 => 3,
            648 => 2,
        ));

        //    //金额，单数
        $list[] = array('dayTime' => '2017-11-22', 'data' => array(
            1 => 8,
            6 => 262,
            68 => 196,
            98 => 91,
            198 => 8,
            328 => 2,
            648 => 1,
        ));

        $data = array();
        foreach ($list as $k => $v) {
            $dayTime = $v['dayTime'];
            $datas   = $v['data'];
            $key     = 0;
            foreach ($datas as $k2 => $v2) {
                for ($i = 0; $i < $v2; $i++) {
                    $arr['orderId']         = 'OrderTest' . str_replace('-', '', $dayTime) . $k2 . str_pad($i + 1, 8, '0', STR_PAD_LEFT);
                    $arr['billNo']          = 'OrderTest' . str_replace('-', '', $dayTime) . $k2 . str_pad($i + 1, 8, '0', STR_PAD_LEFT);
                    $arr['tranId']          = 'OrderTest' . str_replace('-', '', $dayTime) . $k2 . str_pad($i + 1, 8, '0', STR_PAD_LEFT);
                    $arr['userCode']        = 'UserTest' . str_replace('-', '', $dayTime) . str_pad($key + 1, 8, '0', STR_PAD_LEFT);
                    $arr['userName']        = 'UserTest' . str_replace('-', '', $dayTime) . str_pad($key + 1, 8, '0', STR_PAD_LEFT);
                    $arr['agent']           = 'cqxyAND';
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
                    $arr['udid']            = 'DeviceTest' . str_replace('-', '', $dayTime) . str_pad($key + 1, 8, '0', STR_PAD_LEFT);
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
                    $arr['type']            = 1;
                    $arr['regTime']         = strtotime($dayTime . " 12:00:00");
                    $arr['regAgent']        = 'cqxyAND';
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
            1 => 0,
            6 => 1085,
            68 => 1147,
            98 => 973,
            198 => 206,
            328 => 87,
            648 => 49,
        ));

        //金额，单数
        $list[] = array('dayTime' => '2017-11-17', 'data' => array(
            1 => 3,
            6 => 1051,
            68 => 1109,
            98 => 763,
            198 => 228,
            328 => 134,
            648 => 86,
        ));

        //    //金额，单数
        $list[] = array('dayTime' => '2017-11-18', 'data' => array(
            1 => 3,
            6 => 1050,
            68 => 1156,
            98 => 736,
            198 => 136,
            328 => 94,
            648 => 66,
        ));

        //    //金额，单数
        $list[] = array('dayTime' => '2017-11-19', 'data' => array(
            1 => 3,
            6 => 988,
            68 => 1101,
            98 => 980,
            198 => 176,
            328 => 103,
            648 => 78,
        ));

        //    //金额，单数
        $list[] = array('dayTime' => '2017-11-20', 'data' => array(
            1 => 0,
            6 => 987,
            68 => 1106,
            98 => 1020,
            198 => 176,
            328 => 95,
            648 => 52,
        ));

        //    //金额，单数
        $list[] = array('dayTime' => '2017-11-21', 'data' => array(
            1 => 11,
            6 => 988,
            68 => 1012,
            98 => 905,
            198 => 223,
            328 => 114,
            648 => 63,
        ));

        //    //金额，单数
        $list[] = array('dayTime' => '2017-11-22', 'data' => array(
            1 => 0,
            6 => 1250,
            68 => 1205,
            98 => 1131,
            198 => 179,
            328 => 93,
            648 => 23,
        ));


        $data = array();
        foreach ($list as $k => $v) {
            $dayTime = $v['dayTime'];
            $datas   = $v['data'];
            $key     = 0;
            foreach ($datas as $k2 => $v2) {
                for ($i = 0; $i < $v2; $i++) {
                    $arr['orderId']         = 'OrderTest2' . str_replace('-', '', $dayTime) . $k2 . str_pad($i + 1, 8, '0', STR_PAD_LEFT);
                    $arr['billNo']          = 'OrderTest2' . str_replace('-', '', $dayTime) . $k2 . str_pad($i + 1, 8, '0', STR_PAD_LEFT);
                    $arr['tranId']          = 'OrderTest2' . str_replace('-', '', $dayTime) . $k2 . str_pad($i + 1, 8, '0', STR_PAD_LEFT);
                    $arr['userCode']        = 'UserTest' . str_replace('-', '', date('Y-m-d', strtotime($dayTime . ' -1 day'))) . str_pad($key + 1, 8, '0', STR_PAD_LEFT);
                    $arr['userName']        = 'UserTest' . str_replace('-', '', date('Y-m-d', strtotime($dayTime . ' -1 day'))) . str_pad($key + 1, 8, '0', STR_PAD_LEFT);
                    $arr['agent']           = 'cqxyAND';
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
                    $arr['udid']            = 'DeviceTest' . str_replace('-', '', date('Y-m-d', strtotime($dayTime . ' -1 day'))) . str_pad($key + 1, 8, '0', STR_PAD_LEFT);
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
                    $arr['type']            = 1;
                    $arr['regTime']         = strtotime(date('Y-m-d', strtotime($dayTime . ' -1 day')) . " 12:00:00");
                    $arr['regAgent']        = 'cqxyAND';
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
            // 'create30LoginData',
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

$obj = new dataInsert();
