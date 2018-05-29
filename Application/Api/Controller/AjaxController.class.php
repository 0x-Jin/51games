<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/7/17
 * Time: 12:03
 * 获取数据接口
 */
namespace Api\Controller;

class AjaxController extends ApiController
{

    protected $checkSign = "signCheck1233Cy";

    public function GetGKey()
    {
        $data = I();
        echo json_encode($data);
    }

    /**
     * 获取游戏名称接口
     */
    public function GetGame()
    {
        $data = $this->getInput();
        if (! $this->checkSign($data["sign"]))
            die("sign error");
        $game = D("Api/Game")->getAllName();
        echo json_encode($game);
    }

    /**
     * 获取所有未打包的渠道号
     */
    public function GetPackageAgent()
    {
        $data = $this->getInput();
        if (! $this->checkSign($data["sign"]))
            die("sign error");
        $agent = D("Api/Agent")->getAllPackageAgent();
        $arr = array();
        foreach ($agent as $k => $v) {
            $arr[] = $v["changeAgent"];
            $sdkParam = D("Api/AdverParam")->getAdverParam($v["changeAgent"]);
            if ($sdkParam) {
                $num    = 1;
                foreach ($sdkParam as $list) {
                    foreach ($list as $a => $b) {
                        if ($a != "id") {
                            $agent[$k]["sdkAdvParamName".$num]  = $a;
                            $agent[$k]["sdkAdvParamValue".$num] = $b;
                            $num++;
                        }
                    }
                }
            }
        }
        !$arr && $arr = "1";
        D("Api/Agent")->beginAgentPackageStatus(array(
            "agent" => array(
                "IN",
                $arr
            )
        ));
        echo json_encode($agent);
    }

    /**
     * 更新渠道号打包任务为已完成
     */
    public function SavePackageAgent()
    {
        $data = $this->getInput();
        if (! $this->checkSign($data["sign"]))
            die("sign error");
        if (! $data["agent"])
            die("data error");
        D("Api/Agent")->finishAgentPackageStatus($data["agent"]);
        echo "OK!";
    }

    /**
     * 更新最新包的时间
     */
    public function UpdateNewPackageAgent()
    {
        $data = $this->getInput();
        if (! $this->checkSign($data["sign"]))
            die("sign error");
        if (! $data["agent"])
            die("data error");
        $map["agent"] = $data["agent"];
        D("Api/Agent")->updateAgentNewPackageTime($map);
        echo "OK!";
    }

    /**
     * 签名验证
     * 
     * @param
     *            $sign
     * @return bool
     */
    private function checkSign($sign)
    {
        if ($sign != $this->checkSign)
            return false;
        return true;
    }

    /**
     * 七麦idfa排重接口
     */
    public function idfaMatch()
    {
        // return true;
        $data = I('request.', '', 'trim');
        if (! $data['appid'] || ! $data['idfa'])
            exit('-1');
            
            // 匹配激活idfa
        $idfa = explode(',', $data['idfa']);
        if (! is_array($idfa))
            $idfa = array(
                $idfa
            );
        count($idfa) > 500 && exit('-1');
        
        $res = D('Api/AdvterRecord')->idfaMatch($idfa, $data['appid']);
        if ($res === false)
            exit('-1');
        
        $resIdfa = array_column($res, 'idfa');
        
        $arr = array(); // 我方未查到的idfa,0
        $arr2 = array(); // 我发查到的idfa,1
        foreach ($idfa as $key => $value) {
            if (in_array($value, $resIdfa)) {
                $arr2[$value] = "1";
            } else {
                $arr[$value] = "0";
            }
        }
        
        if (count($arr) > 0 && count($arr2) > 0) {
            $returnArr = array_merge($arr, $arr2);
        } elseif (count($arr) > 0) {
            $returnArr = $arr;
        } elseif (count($arr2) > 0) {
            $returnArr = $arr2;
        }
        
        // $insert = array();
        // $num = 0;
        // foreach ($returnArr as $key => $value) {
        //     $insert[$num]['status'] = $value;
        //     $insert[$num]['idfa'] = $key;
        //     $num ++;
        // }
        
        // // 插入数据
        // D('Api/AdvterRecord')->idfaRecord($insert, $data['appid']);
        
        exit(json_encode($returnArr));
    }

    /**
     * 七麦idfa录入
     * @return [type] [description]
     */
    public function idfaRecord()
    {
        $data = I('request.', '', 'trim');
        if (! $data['appid'] || ! $data['idfa']) $this->ajaxReturn(array('result'=>0, 'error'=>'参数缺失'));

        $agent = M('agent')->where(array('appleId'=>$data['appid']))->field('agent')->find();
        if(!$agent) $this->ajaxReturn(array('result'=>0, 'error'=>'appid参数错误'));

        $db = new \Vendor\ApiMongoDB\ApiMongoDB(array('host' => '172.16.0.9', 'port' => 59818, 'username' => 'ZgMongoAdvter', 'password' => 'lkjet#$lj10!~!3sji^', 'db' => 'advter', 'cmd' => '$'));

        $insert = array(
            'appid'      => $data['appid'],
            'idfa'       => $data['idfa'],
            'createTime' => time(),
            'status'     => 1
        );

        $res = $db->insert("aso100", $insert);
        $db->close();

        if($res){
            $this->ajaxReturn(array('result'=>1, 'error'=>'成功'));
        }else{
            $this->ajaxReturn(array('result'=>0, 'error'=>'失败'));
        }
    }

    /**
     * V5后台获取游戏礼包
     */
    public function GetV5Gift()
    {
        $input = $this->getInput();
        if (! empty($input["query"]) && strpos($input["query"], "礼包") !== false) {
            echo "很抱歉！暂无礼包";
            exit();
        }
    }

    /**
     * 获取开服信息
     */
    public function getServer()
    {
        $data = $this->getInput();
        $cacheName = $data['game_id'] . '_' . $data['agent'];
        if (! S($cacheName)) {
            $res = D('Ajax')->getServers($data['game_id'], $data['agent']);
            S($cacheName, $res, 3600); // 缓存3600秒
        } else {
            $res = S($cacheName);
        }
        
        $this->ajaxReturn(array(
            'status' => 1,
            'info' => $res
        ), 'jsonp');
    }

    /**
     * 清除缓存
     */
    public function clearCache()
    {
        $data = $this->getInput();
        if (! $this->checkSign($data["sign"]))
            die("error");
        S($data['key'], null);
        exit('200');
    }

    /**
     * 获取最新公告
     */
    public function getNewGongGao()
    {
        $data = $this->getInput();
        $cacheName = $data['home_id'] . 'gongggao';
        if (! S($cacheName)) {
            $res = D('Ajax')->getNewGongGao($data['home_id']);
            S($cacheName, $res, 1800); // 缓存1800秒
        } else {
            $res = S($cacheName);
        }
        
        $this->ajaxReturn(array(
            'status' => 1,
            'info' => $res
        ), 'jsonp');
    }

    /**
     * 游戏官网二维码跳转
     */
    public function qrcodeLink()
    {
        
        $abbr = I('abbr', '', 'trim');
        $shortKey = I('shortKey', 0, 'intval');
        $type = $this->get_device_type();
        
        if(!empty($shortKey)){
            $rs = D('Ajax')->getShortLink($shortKey);
            $iosLink = $rs['iosLink'];
            $andLink = $rs['andLink'];
        }else{
            $rs = D('Ajax')->getLink($abbr);
            $iosLink = $rs['iosDownload'];
            $andLink = $rs['androidDownload'];
        }
        
        switch ($type) {
            case 'ios':
                header("Location:" . $iosLink);
                break;
            case 'android':
                header("Location:" . $andLink);
                break;
            
            default:
                header('HTTP/1.1 404 Not Found');
                break;
        }
    }

    /**
     * 获取UA
     */
    protected function get_device_type()
    {
        // 全部变成小写字母
        $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
        $type = 'other';
        // 分别进行判断
        if (strpos($agent, 'iphone') || strpos($agent, 'ipad')) {
            $type = 'ios';
        }
        
        if (strpos($agent, 'android')) {
            $type = 'android';
        }
        return $type;
    }

    /**
     * 落地页下载链接转跳
     */
    public function fallLink()
    {
        $downLink = I('downLink', '', 'trim');
        if (! $downLink) {
            header('HTTP/1.1 404 Not Found');
            exit();
        }
        header("Location:" . $downLink);
    }

    // public function onlineReport()
    // {
    //     return true;
    //     $input = $this->getInput("post", "trim");
    //     $data = $this->getDecrypt($input);
    //     if (! $data["udid"] || ! $data["gid"] || ! isset($data["type"]) || ! $data["agent"]) {
    //         $res = array(
    //             "Msg" => "数据异常！请重新请求！"
    //         );
    //         $this->returnMsg($res, 4, $input["Gid"], "数据异常", 1, 0, $input["Version"]);
    //     }
    //     $ip = get_ip_address();
    //     $location = ip_to_location($ip);
    //     // 用户在线数据
    //     $insert = array(
    //         'createtime' => time(),
    //         'agent' => $data['agent'],
    //         'game_id' => intval($data['gid']),
    //         'imei' => $data['imei'],
    //         'imei2' => $data['imei2'],
    //         'mac' => $data['mac'],
    //         'roleId' => $data['roleId'],
    //         'roleName' => $data['roleName'],
    //         'serverId' => $data['serverId'],
    //         'serverName' => $data['serverName'],
    //         'systemId' => $data['systemId'],
    //         'systemInfo' => $data['systemInfo'],
    //         'type' => $data['type'],
    //         'udid' => $data['udid'],
    //         'userCode' => $data['userCode'],
    //         'userName' => $data['userName'],
    //         'ip' => $ip,
    //         'city' => $location['city'],
    //         'province' => $location['province']
    //     );
        
    //     $db = new \Vendor\ApiMongoDB\ApiMongoDB(array(
    //         'host' => 'localhost',
    //         'port' => 59817,
    //         'username' => 'CyMongo',
    //         'password' => 'lkjet#$lj10!~!3sji^',
    //         'db' => 'Cy',
    //         'cmd' => '$'
    //     ));
    //     // $res = $db->select('test',array('createtime'=>))
    //     // $db->delete('test');
    //     // $res = $db->select('test',array('testName'=>'12333','age'=>123));
    //     $res = $db->insert("online", $insert);
    //     $db->close();
    //     if ($res) {
    //         $res = array(
    //             // Offline 是否强制用户下线
    //             // 0 不强制
    //             // 1 强制
    //             "Offline" => "0",
    //             "Msg" => "操作成功"
    //         );
    //         $this->returnMsg($res, 0, $input["Gid"], "操作成功", 1, 0, $input["Version"]);
    //     } else {
    //         $res = array(
    //             "Msg" => "操作失败"
    //         );
    //         $this->returnMsg($res, 5, $input["Gid"], "数据异常", 1, 0, $input["Version"]);
    //     }
    // }

    /**
     * 获取崩溃日志
     */
    public function getCrashLog()
    {
        $data = $this->getInput();
        if (! $this->checkSign($data["sign"]))
            die("sign error");
        $fileName = "crashLog_" . $data["date"];
        $filePath = LOG_PATH . "CrashLog/" . date("Ym", strtotime($data["date"]));
        $dir = opendir($filePath);
        ! $dir && exit("");
        $str = "";
        while (false !== ($file = readdir($dir))) {
            if (strpos($file, $fileName) !== false) {
                $str .= file_get_contents($filePath . "/" . $file);
            }
        }
        exit($str);
    }

    public function advterLock()
    {
        $data = $this->getInput();
        $ip = get_ip_address();
        if ($data['sign'] === $this->checkSign && $ip === '119.29.98.50') {
            $basedir = './TaskScript/advterLock/';
            if (!is_dir($basedir)) {
                mkdir($basedir, 0777, true);
            }
            //判断活动状态
            if ($data['status'] == 0) {
                //停用，枷锁
                file_put_contents($basedir.$data['fileName'], '*');
            } elseif ($data['status'] == 1) {
                //启用，解锁
                file_put_contents($basedir.$data['fileName'], '');
            }
            return true;
        } else {
            die($ip);
        }
    }

    /**
     * IOS渠道上报测试
     * @AuthorHTL
     * @DateTime  2018-03-19T17:24:52+0800
     * @return    [type]                   [description]
     */
    public function iosTest()
    {
        $info = $this->getInput();
        if($this->checkSign != $info['sign']) die('签名校验失败');
        if(! in_array(get_ip_address(), array(
            '218.19.99.122',
            '61.145.249.43',
            '183.6.114.241',
            '119.130.230.100'
        ))) die('IP ERROR');

        $data = array(
            'advter_id'  => $info['advter_id'],
            'advUser'    => $info['advUser'],
            'agent'      => $info['agent'],
            'game_id'    => $info['game_id'],
            'idfa'       => $info['idfa'],
        );

        $res =  D("Api/IOSMatch")->iosAdvterCallBack($data,3);
        exit(json_encode($res));

    }

    /**
     * 新蝴蝶-游戏数据统计
     */
    /*public function getGameData()
    {
        $data = $this->getInput();
        // ip限制
        $ip = get_ip_address();
        if ($ip != '39.108.193.66') {
            log_save('ip有误，请求ip:' . $ip . '，请求参数：' . json_encode($data), 'info', '', 'xhdError', 'xhdLogError_' . date("Y-m-d"));
            header('HTTP/1.1 404 Not Found');
            exit();
        }
        
        log_save($data, 'info', '', 'xhdGameInfo', 'xhdLog_' . date("Y-m-d"));
        if ($data["sign"] != '0fc4d9595108b9cc00f81770887259a3')
            die("sign error");
        if (empty($data['d']) || $data['d'] == 'null') {
            $date = date('Y-m-d');
        } else {
            $date = date('Y-m-d', strtotime($data['d']));
            if ($date == '1970-01-01') {
                $date = date('Y-m-d');
            }
        }
        
        $info = D('Ajax')->getLsyxAndData($date);
        if ($info) {
            exit(json_encode($info));
        } else {
            exit(json_encode((object) array()));
        }
    }*/

}