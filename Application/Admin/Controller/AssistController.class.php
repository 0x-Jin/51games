<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/9/11
 * Time: 15:54
 *
 * 辅助控制器
 */

namespace Admin\Controller;

class AssistController extends BackendController
{

    /**
     * 崩溃日志列表
     */
    public function crashLog()
    {
        if (IS_POST) {
            $data = I();
            !$data["date"] && exit(json_encode(array("rows" => array(), "results" => 0)));
            $start    = $data['start'] ? $data['start'] : 0;
            $pageSize = $data['limit'] ? $data['limit'] : 30;
//            $fileName   = "crashLog_".$data["date"];
            //            $filePath   = LOG_PATH."CrashLog/".date("Ym", strtotime($data["date"]));
            //            $dir        = opendir($filePath);
            //            !$dir && exit(json_encode(array("rows" => array(), "results" => 0)));
            //            $str        = "";
            //            while (false !== ($file = readdir($dir))) {
            //                if (strpos($file, $fileName) !== false) {
            //                    $str .= file_get_contents($filePath."/".$file);
            //                }
            //            }
            //
            $proxy  = '139.199.227.201';
            $proxy2 = '139.199.181.156';
            $url    = "http://apisdk.chuangyunet.net/Api/Ajax/getCrashLog?sign=signCheck1233Cy&date=" . urlencode($data["date"]);
            $str    = proxy_curl($url, $proxy);
            $str .= proxy_curl($url, $proxy2);
            //导出
            if ($data["export_name"] == 1) {
                Header("Content-type:   application/octet-stream ");
                Header("Accept-Ranges:   bytes ");
                header("Content-Disposition:   attachment;   filename=CrashLog.txt ");
                header("Expires:   0 ");
                header("Cache-Control:   must-revalidate,   post-check=0,   pre-check=0 ");
                header("Pragma:   public ");
                echo $str;
                exit();
            }
            !$str && exit(json_encode(array("rows" => array(), "results" => 0)));
            $count = substr_count($str, "【Api/crashlog】");
            if ($start > $count) {
                exit(json_encode(array("rows" => array(), "results" => $count)));
            }

            $begin = 0;
            $end   = min($count, $start + $pageSize);
            $arr   = array();
            if ($data['gameType']) {
                $map_arr['gameType'] = $data['gameType'];
            }
            for ($i = 0; $i <= $end; $i++) {
                $begin = strpos($str, "【Api/crashlog】", $begin);
                if (!$begin) {
                    break;
                }

                $i != $end && $begin++;
                if ($i > $start) {
                    $ip_start     = strpos($str, "[ip]", $begin);
                    $ip           = $ip_start ? substr($str, $ip_start + 4, strpos($str, "[", $ip_start + 4) - $ip_start - 8) : "";
                    $time_start   = strpos($str, "[time]", $begin);
                    $time         = $time_start ? substr($str, $time_start + 6, strpos($str, "[", $time_start + 6) - $time_start - 10) : "";
                    $device_start = strpos($str, "[device]", $begin);
                    $device       = $device_start ? substr($str, $device_start + 8, strpos($str, "[", $device_start + 8) - $device_start - 12) : "";
                    $gid_start    = strpos($str, "[gid]", $begin);
                    $gid          = $gid_start ? substr($str, $gid_start + 5, strpos($str, "[", $gid_start + 5) - $gid_start - 9) : "";
                    $agent_start  = strpos($str, "[agent]", $begin);
                    $agent        = $agent_start ? substr($str, $agent_start + 7, strpos($str, "[", $agent_start + 7) - $agent_start - 11) : "";
                    $ver_start    = strpos($str, "[ver]", $begin);
                    $ver          = $ver_start ? substr($str, $ver_start + 5, strpos($str, "[", $ver_start + 5) - $ver_start - 8) : "";
                    $type_start   = strpos($str, "[type]", $begin);
                    $type         = $type_start ? substr($str, $type_start + 6, strpos($str, "[", $type_start + 6) - $type_start - 10) : "";
                    $log_start    = strpos($str, "[log]", $begin);
                    $log_end      = strpos($str, "【", $log_start);
                    $log          = $log_start ? substr($str, $log_start + 5, $log_end ? ($log_end - $log_start - 5) : -1) : "";
                    $arr[]        = array("ip" => $ip, "time" => $time, "device" => $device, "gid" => $gid, "agent" => $agent, "ver" => $ver, "type" => $type, "log" => str_replace(" ", "&nbsp;", $log));

                }
            }
            exit(json_encode(array("rows" => $arr, "results" => $count)));
        } else {
            $this->display();
        }
    }

    public function banLog()
    {
        if (IS_POST) {
            $data         = I();
            $start        = $data['start'] ? $data['start'] : 0;
            $pageSize     = $data['limit'] ? $data['limit'] : 30;
            $agent_info   = $data["agent"];
            $agent_p_info = $data["agent_p"];
            //处理搜索条件
            $agentArr = dealList($agent_info, $agent_p_info);
            if ($agentArr['agent']) {
                $map["agent"] = array('IN', $agentArr['agent']);
            } else {
                if ($agentArr['pAgent']) {
                    $agent_p_arr  = array_keys(getDataList('agent', 'id', C('DB_PREFIX_API'), array('agent' => array('IN', array_values($agentArr['pAgent'])))));
                    $agent_arr    = array_values($agentArr['pAgent']);
                    $agent_subarr = array_keys(getDataList('agent', 'agent', C('DB_PREFIX_API'), array('pid' => array('IN', $agent_p_arr))));
                    if ($agent_subarr) {
                        $agent_arr = array_merge($agent_arr, $agent_subarr);
                    }
                    $map['agent'] = array('IN', $agent_arr);
                }
            }
            if ($data["game_id"]) {
                $game_agent     = array_keys(getDataList('agent', 'agent', C('DB_PREFIX_API'), array('game_id' => $data["game_id"])));
                $map['_string'] = 'agent IN ("' . implode('","', $game_agent) . '")';
            }

            if ($data['userCode']) {
                $map['userCode'] = $data['userCode'];
            }

            if ($data['status'] != 'all') {
                $map['status'] = $data['status'];
            }

            if ($data['date']) {
                $map['createTime'] = array(array('egt', strtotime($data['date'])), array('lt', strtotime($data['date'] . '+1 day')), 'and');
            }

            $res   = D('Admin')->getBanLog($map, $start, $pageSize);
            $row   = $res['list'];
            $agent = getDataList("agent", "agent", C("DB_PREFIX_API"));
            if (!empty($row)) {
                foreach ($row as $key => &$val) {
                    $val['createTime'] = date('Y-m-d H:i:s', $val['createTime']);
                    switch ($val["status"]) {
                        case 0:
                            $val["status"] = "解封";
                            break;
                        case 1:
                            $val["status"] = "封号";
                            break;
                        case 2:
                            $val["status"] = "异常";
                            break;
                        default:
                            $val["status"] = "其他";
                    };
                    $val['pay']       = D('Order')->getIncomeSum(array('userCode' => $val['userCode'], 'orderStatus' => 0, 'orderType' => 0));
                    $val["agentName"] = $val["agent"] ? $agent[$val["agent"]]["agentName"] : "-";
                }
            }
            $results = $res['count'];

            exit(json_encode(array('rows' => $row ? $row : array(), "results" => $results)));
        } else {
            if (IS_AJAX) {
                $response = $this->fetch();
                $this->ajaxReturn(array('status' => 1, '_html' => $response));
            } else {
                $this->display();
            }
        }
    }

    /**
     * Imei代理列表
     * @return [type] [description]
     */
    public function imeiProxy()
    {
        if (IS_POST) {
            $data     = I();
            $start    = $data['start'] ? $data['start'] : 0;
            $pageSize = $data['limit'] ? $data['limit'] : 30;

            if ($data['creater']) {
                $map['creater'] = $data['creater'];
            }

            $department = session('admin.partment');
            if ($department != 0) {
                $map['departmentId'] = $department;
            }

            $res = D('Admin')->getImeiProxy($map, $start, $pageSize);
            $row = $res['list'];
            if (!empty($row)) {
                foreach ($row as $key => &$val) {
                    $val['createTime'] = date('Y-m-d H:i:s', $val['createTime']);
                }
            }
            $results = $res['results'];

            exit(json_encode(array('rows' => $row ? $row : array(), "results" => $results)));
        } else {
            if (IS_AJAX) {
                $response = $this->fetch();
                $this->ajaxReturn(array('status' => 1, '_html' => $response));
            } else {
                $this->display();
            }
        }
    }

    /**
     * 添加imei代理
     * @return [type] [description]
     */
    public function imeiAdd()
    {
        if (IS_POST) {
            $data = I();

            if (!$data['imei']) {
                $this->error('本机IMEI/IDFA不能为空');
            }

            if (!$data['imeiProxy']) {
                $this->error('代理IMEI/IDFA不能为空');
            }

            // if(D('Admin')->commonQuery('whitelist',array('imeiProxy'=>$data['imeiProxy']), 0, 1, '*','lg_')){
            //     $this->error('代理IMEI/IDFA已存在');
            // }

            $time       = time();
            $department = session('admin.partment');
            $creater    = session('admin.realname');

            $insert[] = array(
                'createTime'   => $time,
                'departmentId' => $department,
                'imei'         => $data['imei'],
                'creater'      => $creater,
                'imeiProxy'    => $data['imeiProxy'],
            );

            $res = D('Admin')->commonAddAll('whitelist', $insert, $prefix = 'lg_');
            if ($res) {
                $this->success('操作成功');
            } else {
                $this->error('操作失败');
            }

        } else {
            $this->assign('partment', $this->partment);
            $this->ajaxReturn(array("status" => 1, "_html" => $this->fetch()));
        }
    }

    /**
     * 投放上报渠道商结果列表
     * @return [type] [description]
     */
    public function advterReport()
    {
        if (IS_POST) {
            $data = I();
            if (!$data['game_id'] || !$data['system']) {
                exit(json_encode(array('rows' => $row ? $row : array(), "results" => 0, 'hasError' => true, 'error' => '游戏和系统必选！')));
            }

            $start    = $data['start'] ? $data['start'] : 0;
            $pageSize = $data['limit'] ? $data['limit'] : 30;

            if ($data['system'] == 1) {
                if ($data['agent']) {
                    $map['agent'] = (array) $data['agent'];
                } else {

                    $agent_infos = $map_arr = array();

                    if (!empty($data['agent_p'])) {
                        $map_arr['_string'] = "id IN ('" . implode("','", (array) $data['agent_p']) . "') OR pid IN ('" . implode("','", (array) $data['agent_p']) . "')";
                    }

                    if ($data['game_id']) {
                        $map_arr['game_id'] = $data['game_id'];
                    }

                    if ($data['advteruser_id']) {
                        $map_arr['advteruser_id'] = $data['advteruser_id'];
                    }

                    if (session('admin.partment')) {
                        $map_arr['departmentId'] = session('admin.partment');
                    }

                    if ($map_arr) {
                        $agent_infos = array_keys(getDataList('agent', 'agent', C('DB_PREFIX_API'), $map_arr));
                    }

                    $arr = $this->agentArr;
                    if ($agent_infos) {
                        $arr = array_intersect($arr, $agent_infos);
                    } elseif ($map_arr && !$agent_infos) {
                        $arr = array();
                    }

                    sort($arr);
                    if (count($arr) < 1) {
                        $arr[] = '-1';
                    }

                    $map['agent'] = $arr;

                }

                if (!$data['startDate'] || !$data['endDate']) {
                    exit(json_encode(array('rows' => $row ? $row : array(), "results" => 0, 'hasError' => true, 'error' => '日期必须')));
                }
                $map['startDate']  = $data['startDate'];
                $map['endDate']    = $data['endDate'];
                $map['reportType'] = $data['reportType'];
                $res               = D('Admin')->advterReport($map, $start, $pageSize);
            } else {
                $res = $this->advterIosReport();
            }
            $count      = $res['count'];
            $gameName   = getDataList("game", "id", C("DB_PREFIX_API"));
            $advteruser = getDataList('advteruser', 'id', C('DB_PREFIX'));
            $events     = getDataList('events', 'id', C('DB_PREFIX'));

            foreach ($res['list'] as $key => $value) {
                if ($res['list'][$key]['adUserId'] == 2) {
                    if ($res['list'][$key]['advterType'] == 'gdt2') {
                        $res['list'][$key]['advterUser'] = '广点通（方案二）';
                    } else {
                        $res['list'][$key]['advterUser'] = '广点通（方案一）';
                    }
                } else {
                    $res['list'][$key]['advterUser'] = $advteruser[$value['adUserId']]['company_name'];
                }
                $res['list'][$key]['gameName']   = $gameName[$value['game_id']]['gameName'];
                $res['list'][$key]['createTime'] = date('Y-m-d H:i:s', $value['createTime']);
                $res['list'][$key]['clickTime']  = date('Y-m-d H:i:s', $value['clickTime']);

                if ($data['system'] == 1) {

                    if ($value['adUserId'] == 2 && (md5(strtolower($value['imei'])) == $value['muid'])) {
                        // 广点通
                        $res['list'][$key]['deviceId'] = $value['imei'];
                    } elseif ($value['adUserId'] == 2 && (md5(strtolower($value['imei2'])) == $value['muid'])) {
                        $res['list'][$key]['deviceId'] = $value['imei2'] . '【imei2】';
                    } elseif ($value['adUserId'] == 7 && (md5(strtoupper($value['imei'])) == $value['muid'])) {
                        // UC
                        $res['list'][$key]['deviceId'] = $value['imei'];
                    } elseif ($value['adUserId'] == 7 && (md5(strtoupper($value['imei2'])) == $value['muid'])) {
                        $res['list'][$key]['deviceId'] = $value['imei2'] . '【imei2】';
                    } elseif ((md5($value['imei']) == $value['muid'])) {
                        $res['list'][$key]['deviceId'] = $value['imei'];
                    } elseif ((md5($value['imei2']) == $value['muid'])) {
                        $res['list'][$key]['deviceId'] = $value['imei2'] . '【imei2】';
                    } else {
                        $res['list'][$key]['deviceId'] = '';
                    }
                } else {
                    $res['list'][$key]['deviceId'] = $value['idfa'];
                }

                $data['system'] == 2 && $res['list'][$key]['agent'] = $events[$value['advter_id']]['events_name'];
            }

            exit(json_encode(array('rows' => $res['list'] ? $res['list'] : array(), "results" => $count)));
        } else {
            if (IS_AJAX) {
                $response = $this->fetch();
                $this->ajaxReturn(array('status' => 1, '_html' => $response));
            } else {
                $this->display();
            }
        }
    }

    /**
     * 投放IOS上报渠道商结果列表
     * @return [type] [description]
     */
    public function advterIosReport()
    {
        if (IS_POST) {
            $data     = I();
            $start    = $data['start'] ? $data['start'] : 0;
            $pageSize = $data['limit'] ? $data['limit'] : 30;

            if ($data['advter_id']) {
                $map['advter_id'] = (array) $data['advter_id'];
            } else {
                $agent_infos = $map_arr = array();

                if (!empty($data['events_groupId'])) {
                    $map_arr['_string'] = "events_groupId IN ('" . implode("','", (array) $data['events_groupId']) . "')";
                }

                if ($data['advteruser_id']) {
                    $map_arr['advteruser_id'] = $data['advteruser_id'];
                }

                if ($data['game_id']) {
                    $map_arr['game_id'] = $data['game_id'];
                }

                if (session('admin.partment')) {
                    $map_arr['department'] = session('admin.partment');
                }

                if ($data['agent_p']) {
                    $agent_p = array_keys(getDataList('agent', 'agent', C('DB_PREFIX_API'), array('id' => array('IN', $data['agent_p']))));
                    if ($agent_p) {
                        $map_arr['agent'] = array('IN', $agent_p);
                    }
                }

                $agent_infos = array_keys(getDataList('events', 'id', C('DB_PREFIX'), $map_arr));

                if ($agent_infos) {
                    $arr = $agent_infos;
                } elseif ($map_arr && !$agent_infos) {
                    $arr = array('-1');
                }

                $map['advter_id'] = $arr;
            }

            if (!$data['startDate'] || !$data['endDate']) {
                exit(json_encode(array('rows' => $row ? $row : array(), "results" => 0, 'hasError' => true, 'error' => '日期必须')));
            }
            $map['startDate']  = $data['startDate'];
            $map['endDate']    = $data['endDate'];
            $map['reportType'] = $data['reportType'];
            $res               = D('Admin')->advterReport($map, $start, $pageSize, $data['system']);
            return $res;
        } else {
            if (IS_AJAX) {
                $response = $this->fetch();
                $this->ajaxReturn(array('status' => 1, '_html' => $response));
            } else {
                $this->display();
            }
        }
    }

    /**
     * 短链接生成
     * @return [type] [description]
     */
    public function shortLink()
    {
        if (IS_POST) {
            $data     = I();
            $start    = $data['start'] ? $data['start'] : 0;
            $pageSize = $data['limit'] ? $data['limit'] : 30;

            if ($data['shortLink']) {
                $map['shortLink'] = $data['shortLink'];
            }
            $res = D('Admin')->getBuiList('short_link', $map, $start, $pageSize);

            if (!empty($res['list'])) {
                foreach ($res['list'] as $key => &$val) {
                    $res['list'][$key]['createTime'] = date('Y-m-d H:i:s', $val['createTime']);
                    $res['list'][$key]['opt']        = createBtn('<a onclick=shortLinkEdit(' . $val['id'] . ') href="javascript:;">编辑</a>');
                }
            }
            $results = $res['count'];

            exit(json_encode(array('rows' => $res['list'] ? $res['list'] : array(), "results" => $results)));
        } else {
            if (IS_AJAX) {
                $response = $this->fetch();
                $this->ajaxReturn(array('status' => 1, '_html' => $response));
            } else {
                $this->display();
            }
        }
    }

    /**
     * 插入前置操作
     * @param string $table 操作的数据表
     */
    public function _before_insert($data)
    {
        if ($this->table == 'short_link') {
            $data['createTime']   = time();
            $data['departmentId'] = session('admin.partment');
            $data['creater']      = session('admin.realname');
        }
        return $data;
    }

    /**
     * 插入后置操作
     * @param $id
     */
    public function _after_insert($id)
    {
        if ($this->table == 'short_link') {
            //生成短链
            $apiLink   = urlencode('http://apisdk.chuangyunet.net/Api/Ajax/qrcodeLink.html?shortKey=' . $id);
            $shortLink = curl_get('http://api.t.sina.com.cn/short_url/shorten.json?source=3271760578&url_long=' . $apiLink);
            $url_short = json_decode($shortLink, true)[0]['url_short'];
            $res       = D('Admin')->commonExecute('short_link', array('id' => $id), array('shortLink' => $url_short));
            if ($res) {
                $this->success('操作成功');
            } else {
                $this->error('操作失败');
            }
        }
    }

    public function ocpaSwitch()
    {
        if (IS_POST) {
            $data       = I();
            $department = session('admin.partment');
            $map        = array();
            if ($department != 0) {
                $map['departmentId'] = $department;
            }

            $db = new \Vendor\ApiMongoDB\ApiMongoDB(array('host' => '127.0.0.1', 'port' => 59818, 'username' => 'ZgMongoAdvter', 'password' => 'lkjet#$lj10!~!3sji^', 'db' => 'advter', 'cmd' => '$'));

            $res = $db->select('ocpa', $map, array(), array('createTime' => -1));
            $db->close();
            $ocpaType = array(1 => '广点通', 2 => '微信');
            $row      = $res;
            if (!empty($row)) {
                foreach ($row as $key => &$val) {
                    $val['createTime'] = date('Y-m-d H:i:s', $val['createTime']);
                    $val['ocpaType']   = empty($val['ocpaType']) ? '广点通' : $ocpaType[$val['ocpaType']];
                    if ($val['status'] == 0) {
                        $val['status'] = '<span style="color:green;">开启</span>';
                    } else {
                        $val['status'] = '<span style="color:red;">关闭</span>';
                    }
                    $val['opt'] = createBtn('<a href="javascript:;" class="button button-primary" onclick=ocpaEdit("' . $val['_id'] . '",this)>编辑</a>');
                }
            }
            $results = count($res['results']);

            exit(json_encode(array('rows' => $row ? $row : array(), "results" => $results)));
        } else {
            if (IS_AJAX) {
                $response = $this->fetch();
                $this->ajaxReturn(array('status' => 1, '_html' => $response));
            } else {
                $this->display();
            }
        }
    }

    public function ocpaAdd()
    {
        if (IS_POST) {
            $data = I();

            if (!$data['advertiser_id']) {
                $this->error('账号id不能为空');
            }

            if (!$data['appid']) {
                $this->error('应用id不能为空');
            }

            $time       = time();
            $department = session('admin.partment');
            $creater    = session('admin.realname');

            $db = new \Vendor\ApiMongoDB\ApiMongoDB(array('host' => '127.0.0.1', 'port' => 59818, 'username' => 'ZgMongoAdvter', 'password' => 'lkjet#$lj10!~!3sji^', 'db' => 'advter', 'cmd' => '$'));

            $count = $db->select('ocpa', array('status' => '0', 'departmentId' => $department));

            if ($count) {
                $db->close();
                $this->error('操作失败,存在正在运行的配置');
            }

            $insert = array(
                'createTime'    => $time,
                'departmentId'  => $department,
                'advertiser_id' => $data['advertiser_id'],
                'creater'       => $creater,
                'appid'         => $data['appid'],
                'Num'           => $data['Num'],
                'reportNum'     => 0,
                'ocpaType'      => (int) $data['ocpaType'],
                'status'        => $data['status'],
            );

            $res = $db->insert("ocpa", $insert);
            $db->close();

            if ($res) {
                $basedir = './TaskScript/OcpaLog/ios';
                if (!is_dir($basedir)) {
                    mkdir($basedir, 0777, true);
                }

                //判断活动状态
                if ($data['status'] == 1) {
                    //停用，枷锁
                    file_put_contents($basedir . '/' . $department . 'ocpaLock.log', '*');
                } elseif ($data['status'] == 0) {
                    //启用，解锁
                    file_put_contents($basedir . '/' . $department . 'ocpaLock.log', '');
                }
            }
            if ($res) {
                $this->success('操作成功');
            } else {
                $this->error('操作失败');
            }
        } else {
            $this->assign('partment', $this->partment);
            $this->ajaxReturn(array("status" => 1, "_html" => $this->fetch()));
        }
    }

    public function ocpaEdit()
    {
        $db         = new \Vendor\ApiMongoDB\ApiMongoDB(array('host' => '127.0.0.1', 'port' => 59818, 'username' => 'ZgMongoAdvter', 'password' => 'lkjet#$lj10!~!3sji^', 'db' => 'advter', 'cmd' => '$'));
        $department = session('admin.partment');

        if (IS_POST) {
            $data = I();
            if (!$data['id']) {
                $this->error('参数有误');
            }

            $count = $db->select('ocpa', array('status' => '0', 'departmentId' => $department));

            if ($count && $count[0]['_id'] != $data['id']) {
                $db->close();
                $this->error('操作失败,存在正在运行的配置');
            }

            $map = array(
                'Num'      => $data['Num'],
                'ocpaType' => (int) $data['ocpaType'],
                'status'   => $data['status'],
            );

            $res = $db->update('ocpa', $map, array('_id' => $data['id']));
            $db->close();

            if ($res) {
                $basedir = './TaskScript/OcpaLog/ios';
                if (!is_dir($basedir)) {
                    mkdir($basedir, 0777, true);
                }

                //判断活动状态
                if ($data['status'] == 1) {
                    //停用，枷锁
                    file_put_contents($basedir . '/' . $department . 'ocpaLock.log', '*');
                } elseif ($data['status'] == 0) {
                    //启用，解锁
                    file_put_contents($basedir . '/' . $department . 'ocpaLock.log', '');
                }

            }

            if ($res) {
                $this->success('操作成功');
            } else {
                $this->error('操作失败');
            }
        } else {
            $id  = I('id');
            $res = $db->select('ocpa', array('_id' => $id, 'departmentId' => $department));
            $db->close();

            if (!empty($res)) {
                $this->assign('advertiser_id', $res[0]['advertiser_id']);
                $this->assign('appid', $res[0]['appid']);
                $this->assign('status', $res[0]['status']);
                $this->assign('Num', $res[0]['Num']);
                $this->assign('ocpaType', $res[0]['ocpaType']);
            } else {
                exit($this->ajaxReturn(array('status' => -1, 'info' => '不允许编辑')));
            }

            if (IS_AJAX) {
                $respose = $this->fetch();
                $this->ajaxReturn(array('status' => 0, '_html' => $respose));
            } else {
                $this->display();
            }
        }
    }

    /**
     * 导入特殊游戏数据信息
     */
    public function specialDataImport()
    {
        if (IS_POST) {
            if (session('admin.role_id') !=1) {
                $this->error('你无权操作');
            }
            if (!$_FILES['ortherInfo']['name']) {
                $this->error('没有传入Excel');
            }
            //文件上传
            $file_info = excel_file_upload('ortherInfoFile');
            if ($file_info && $file_info != '没有文件被上传！') {
                //获取文件数据并且转数组
                $fileName = './Uploads/' . $file_info['ortherInfo']['savepath'] . $file_info['ortherInfo']['savename'];
                $data     = excel_to_array($fileName);
                if ($data) {
                    unset($data[1]); //第一个行为标题，不需要入库
                    $time = time();
                    foreach ($data as $val) {
                        $arr[] = array(
                            "dayTime"      => $val[0],
                            "gameId"       => $val[1],
                            "newDevice"    => $val[2],
                            "disUdid"      => $val[3],
                            "newUser"      => $val[4],
                            "newUserLogin" => $val[5],
                            "oldUserLogin" => $val[6],
                            "allPay"       => $val[7],
                            "allPayUser"   => $val[8],
                            "newPay"       => $val[9],
                            "newPayUser"   => $val[10],
                            "day1"         => $val[11],
                            "day2"         => $val[12],
                            "day6"         => $val[13],
                            "day13"        => $val[14],
                            "day29"        => $val[15],
                            "createTime"   => $time,
                            "creater"      => session('admin.realname'),
                        );
                    }

                    if (count($arr) > 0 && is_array($arr)) {
                        $res = D('Admin')->commonAddAll('orther_info', $arr);
                    }
                    if ($res) {
                        $this->success('导入成功');
                    } else {
                        $this->error('导入失败');
                    }
                } else {
                    $this->error('导入失败');
                }
            } else {
                $this->error('导入失败');
            }
        } else {
            if (IS_AJAX) {
                $response = $this->fetch();
                $this->ajaxReturn(array('status' => 1, '_html' => $response));
            } else {
                $this->display();
            }
        }
    }
}
