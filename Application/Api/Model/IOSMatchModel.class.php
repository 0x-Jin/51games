<?php
/**
 * Created by Sublime.
 * User: XSM
 * Date: 2017/10/10
 * Time: 14:41
 *
 *
 * IOS推广匹配模型
 */

namespace Api\Model;

use Think\Model;

class IOSMatchModel extends Model
{
    protected $autoCheckFields = false; //关闭自动检测数据库字段

    /**
     * 注册匹配
     * @AuthorHTL
     * @DateTime  2017-10-11T09:47:39+0800
     * @param     [array]                   $data     [用户注册数据]
     * @return    [bool]                              [true/false]
     */
    public function registerMatch($data)
    {
        if (empty($data) || empty($data['userCode'])) {
            return true;
        }
        $existUdid = $this->getExistUdid($data['udid'], $data['agent']);
        $userip    = get_client_ip();

        $inserts = array(
            'advter_id'  => '1',
            'advUser'    => '1',
            'channel_id' => '1',
            'userCode'   => $data['userCode'],
            'sole_udid'  => $existUdid ? 0 : 1, //0:周期内已经注册过 1:周期内没有注册过
            'mac'        => $data['mac'] ? $data['mac'] : '',
            'agent'      => $data['agent'] ? $data['agent'] : '',
            'game_id'    => $data['game_id'],
            'idfa'       => $data['idfa'] ? $data['idfa'] : '00000000-0000-0000-0000-000000000000',
            'idfv'       => $data['idfv'] ? $data['idfv'] : '',
            'ip'         => $data['ip'] ? $data['ip'] : $userip,
            'udid'       => $data['udid'] ? $data['udid'] : '',
            'city'       => $data['city'],
            'province'   => $data['province'],
            'createTime' => $data['createTime'],
            'status'     => 0,
            'systemId'   => $data['systemId'] ? $data['systemId'] : '',
            'systemInfo' => $data['systemInfo'] ? $data['systemInfo'] : '',
            'netInfo'    => $data['net'],
            'serial'     => $data['serial'],
            'imei'       => $data['imei'] ? $data['imei'] : '',
            'ver'        => $data['ver'] ? $data['ver'] : '',
        );
        $newData = $this->IOSMatch($inserts);
        if ($newData) {
            $res = M('ios_user_game_log')->add($newData);
            if ($inserts['sole_udid'] == 1) {
                $this->iosAdvterCallBack($newData, 3);
            }
            return $res;
        }
    }

    /**
     * 设备激活匹配
     * @AuthorHTL
     * @DateTime  2017-10-11T09:47:39+0800
     * @param     [array]                   $data     [用户注册数据]
     * @return    [bool]                              [true/false]
     */
    public function deviceMatch($data)
    {
        if (empty($data)) {
            return true;
        }
        $userip = get_client_ip();

        $inserts = array(
            'advter_id'  => '1',
            'advUser'    => '1',
            'channel_id' => '1',
            'sole_udid'  => '1',
            'mac'        => $data['mac'] ? $data['mac'] : '',
            'agent'      => $data['agent'] ? $data['agent'] : '',
            'game_id'    => $data['game_id'],
            'idfa'       => $data['idfa'] ? $data['idfa'] : '00000000-0000-0000-0000-000000000000',
            'idfv'       => $data['idfv'] ? $data['idfv'] : '',
            'ip'         => $data['ip'] ? $data['ip'] : $userip,
            'city'       => $data['city'],
            'province'   => $data['province'],
            'createTime' => $data['createTime'],
            'status'     => 0,
            'systemId'   => $data['systemId'] ? $data['systemId'] : '',
            'systemInfo' => $data['systemInfo'] ? $data['systemInfo'] : '',
            'netInfo'    => $data['net'],
            'serial'     => $data['serial'],
            'udid'       => $data['udid'] ? $data['udid'] : '',
            'imei'       => $data['imei'] ? $data['imei'] : '',
            'ver'        => $data['ver'] ? $data['ver'] : '',
        );
        $newData = $this->IOSMatch($inserts);
        if ($newData) {
            //判断是否激活过
            if (!$this->getDeviceAgent($newData['udid'], $newData['agent'])) {
                $res = M('ios_device_agent_log')->add($newData);
            }
            $this->iosAdvterCallBack($newData, 1);
            return $res;
        }
    }

    /**
     * 登录匹配
     * @AuthorHTL
     * @DateTime  2017-10-11T16:54:02+0800
     * @param     [type]                   $data     [登录数据]
     * @return    [type]                             [description]
     */
    public function loginMatch($data)
    {
        if (empty($data['userCode'])) {
            return true;
        }
        $user = M('ios_user_game_log')->where(array('userCode' => $data['userCode']))->find();
        if (!$user) {
            return true;
        }

        $userip  = get_client_ip();
        $inserts = array(
            'advter_id'  => $user['advter_id'],
            'advUser'    => $user['advUser'],
            'channel_id' => $user['channel_id'],
            'regTime'    => $user['createTime'],
            'userCode'   => $data['userCode'],
            'mac'        => $data['mac'] ? $data['mac'] : '',
            'agent'      => $data['agent'] ? $data['agent'] : '',
            'regAgent'   => $user['agent'],
            'game_id'    => $data['game_id'],
            'idfa'       => $data['idfa'] ? $data['idfa'] : '00000000-0000-0000-0000-000000000000',
            'idfv'       => $data['idfv'] ? $data['idfv'] : '',
            'ip'         => $data['ip'] ? $data['ip'] : $userip,
            'udid'       => $data['udid'] ? $data['udid'] : '',
            'city'       => $data['city'],
            'province'   => $data['province'],
            'loginTime'  => $data['time'],
            'status'     => 0,
            'systemId'   => $data['systemId'] ? $data['systemId'] : '',
            'systemInfo' => $data['systemInfo'] ? $data['systemInfo'] : '',
            'netInfo'    => $data['net'],
            'serial'     => $data['serial'],
            'imei'       => $data['imei'] ? $data['imei'] : '',
            'ver'        => $data['ver'] ? $data['ver'] : '',
        );
        $res = M('ios_role_login_log')->add($inserts);
        return $res;
    }

    /**
     * 获取IOS设备游戏信息
     * @param $udid
     * @param $agent
     * @return mixed
     */
    public function getDeviceAgent($udid, $agent)
    {
        //判断必要数据是否存在
        if (!$udid || !$agent) {
            return false;
        }

        return M('ios_device_agent_log')->where("udid = '{$udid}' AND agent = '{$agent}'")->find();
    }

    /**
     * 获取IOS用户信息
     * @param $userCode
     * @param $agent
     * @return mixed
     */
    public function getUserInfo($userCode, $agent)
    {
        //判断必要数据是否存在
        if (!$userCode) {
            return false;
        }

        $iosuser = M('ios_user_game_log')->where(array('userCode' => $userCode))->field('advter_id')->find();
        if ($iosuser) {
            return $iosuser;
        } else {
            //没有找到用户的，根据渠道号找到对应的推广自然量
            return M('events', 'la_')->where(array('agent' => $agent, 'is_zrl' => 1))->field('id AS advter_id')->find();
        }
    }

    /**
     * IOS推广活动匹配
     * @AuthorHTL
     * @DateTime  2017-10-11T10:16:23+0800
     * @param     [array]                   $data [需要匹配的数据]
     * @return    [array]                         [数组]
     */
    private function IOSMatch($data)
    {
        if (!empty($data['idfa']) && $data['idfa'] != '00000000-0000-0000-0000-000000000000') {
            $mongo = new \Vendor\ApiMongoDB\ApiMongoDB(array('host' => '172.16.0.9', 'port' => 59818, 'username' => 'ZgMongoAdvter', 'password' => 'lkjet#$lj10!~!3sji^', 'db' => 'advter', 'cmd' => '$'));
            //匹配用户来源
            $startTime        = $data['createTime'] - 604800; //7天内的有效
            $map['clickTime'] = array($mongo->cmd('>') => $startTime);
            $map['muid']      = (string) strtolower(md5($data['idfa']));
            $map['agent']     = (string) $data['agent'];

            $res = $mongo->select('advios', $map, array('muid', 'advter_id', 'game_id', 'adUserId', 'agent'), array('createTime' => -1), 1);

            if (empty($res)) {
                //根据ip匹配，查看是否为落地页推广用户
                $map['muid'] = (string) strtolower(md5($data['ip']));
                $res         = $mongo->select('advios', $map, array('muid', 'advter_id', 'game_id', 'adUserId', 'agent'), array('createTime' => -1), 1);
            }

            if (!empty($res)) {
                $data['advter_id'] = $res[0]['advter_id'];
                $data['advUser']   = $res[0]['adUserId'];
            }
            $mongo->close();
        }

        //没有匹配或者idfa异常的都归为自然量
        if (empty($res)) {
            $eventInfo = M('events', 'la_')->where(array('agent' => $data['agent'], 'is_zrl' => 1))->field('id')->find();
            if (!empty($eventInfo)) {
                $data['advter_id'] = $eventInfo['id'];
            } else {
                $agentInfo = M('agent')->where(array('agent' => $data['agent']))->field('departmentId')->find();
                //自动新增新渠道包自然量
                $rowid = M('events', 'la_')->add(
                    array(
                        'events_name'   => '自然量【' . $data['agent'] . '】',
                        'game_id'       => $data['game_id'],
                        'agent'         => $data['agent'],
                        'is_zrl'        => 1,
                        'advteruser_id' => 1,
                        'department'    => $agentInfo['departmentId'],
                        'createTime'    => time(),
                        'creater'       => 'system',
                    )
                );
                $data['advter_id'] = $rowid;
            }
        }

        return $data;
    }

    /**
     * 查看设备是否已经注册过账号
     * @AuthorHTL
     * @DateTime  2017-10-11T11:04:07+0800
     * @param     [string]                   $udid         [设备唯一标识符]
     * @param     [string]                   $agent        [渠道号]
     * @return    [bool]                                 [description]
     */
    private function getExistUdid($udid, $agent)
    {
        //历史注册31天算一个周期
        return M('ios_user_game_log')->where(array('udid' => $udid, 'agent' => $agent, 'createTime' => array('egt', strtotime(date('Y-m-d') . ' -31 day'))))->count();
    }

    /**
     * [修改mongo记录并关闭mongo]
     * @AuthorHTL
     * @DateTime  2018-03-01T12:52:20+0800
     * @param     [obj]                   $mongo [mongo对象]
     * @param     [string]                $_id   [修改mongo记录的id]
     */
    private function saveMongo($mongo, $_id)
    {
        $mongo->update('advios', array('status' => 0), array('_id' => $_id));
        $mongo->close();
    }

    /**
     * 报送广告激活数据
     * @param $data
     * @param $reportType [上报的类型] 1:激活 2:充值报送 3:注册报送
     * @return bool|mixed
     */
    public function iosAdvterCallBack($data, $reportType = 1)
    {

        if (!$data['advter_id'] || !$data['game_id']) {
            //调试记录日志
            log_save("[data]传入数据：" . json_encode($data) . "  [msg]游戏或广告位id异常日志", "info", "", "new_advter_log_" . date("Y-m-d") . '.log', 'ios_debug_advter_log');
            return false;
        }

        //判断广告链接状态是否未开启
        $filename = './TaskScript/advterLock/' . $data['advter_id'] . 'Lock.log';
        if (file_exists($filename) && filesize($filename) > 0) {
            return false;
        }

        $events = M('events', 'la_')->where(array('id' => $data['advter_id']))->find();
        if (!$events || count($events) < 1) {
            return false;
        }

        if ($events['callBackStatus']) {
            if ($events['callBackStatus'] != $reportType) {
                log_save("[data]传入数据：" . json_encode($data) . "  [msg]报送类型与后台设置不一致日志", "info", "", "new_advter_log_" . date("Y-m-d") . '.log', 'ios_debug_advter_log');
                return false;
            }
        }

        //微信广点通5天，其他7天内的数据(查从库)
        $mongo = new \Vendor\ApiMongoDB\ApiMongoDB(array('host' => '172.16.0.9', 'port' => 59818, 'username' => 'ZgMongoAdvter', 'password' => 'lkjet#$lj10!~!3sji^', 'db' => 'advter', 'cmd' => '$'));
        //匹配用户来源
        if ($events['advteruser_id'] == 2 || $events['advteruser_id'] == 10) {
            $startTime = time() - 432000; //5天内的有效
        } else {
            $startTime = time() - 604800; //7天内的有效
        }
        $map['clickTime'] = array($mongo->cmd('>') => $startTime);

        //ios报送
        if (!empty($data['idfa'])) {
            $map['advter_id'] = (string) $data['advter_id'];
            $map['muid']      = (string) strtolower(md5($data['idfa']));
            $map['agent']     = (string) $data['agent'];
            $map['game_id']   = (string) $data['game_id'];
            $map['os']        = 2;
            $res              = $mongo->select('advios', $map, array(), array('createTime' => -1), 1);
            $result           = $res[0];
        }

        if ($result) {
            if ($reportType == 2) {
                $result['amount'] = $data['amount'];
            }

            if ($result['adUserId'] == 10) {
                //微信
                $result['_config']['appid']         = $events['config_appid'];
                $result['_config']['advertiser_id'] = $events['config_advertiser_id'];
                $result['_config']['sign_key']      = $events['config_sign_key'];
                $result['_config']['encrypt_key']   = $events['config_encrypt_key'];
                $result['idfa']                     = $data['idfa'];
                $result['department']               = $events['department'];
                $this->wxReport($result, $reportType);
                $this->saveMongo($mongo, $result['_id']);
                return true;
            } elseif ($result['adUserId'] == 2) {
                //广点通
                $result['_config']['appid']         = $events['config_appid'];
                $result['_config']['advertiser_id'] = $events['config_advertiser_id'];
                $result['_config']['sign_key']      = $events['config_sign_key'];
                $result['_config']['encrypt_key']   = $events['config_encrypt_key'];
                $result['idfa']                     = $data['idfa'];
                $result['department']               = $events['department'];
                $this->gdtReport($result, $reportType);
                $this->saveMongo($mongo, $result['_id']);
                return true;
            } elseif ($result['adUserId'] == 6) {
                //今日头条
                switch ($reportType) {
                    case '1':
                        $event_type = '0'; //激活
                        break;
                    case '2':
                        $event_type = '2'; //充值
                        break;
                    case '3':
                        $event_type = '1'; //注册
                        break;
                    default:
                        $event_type = '';
                        break;
                }
                $result['callBackUrl'] .= '&event_type=' . $event_type;
            } elseif ($result['adUserId'] == 7) {
                //UC头条
            } elseif ($result['adUserId'] == 14) {
                //百度信息流
                $result['_config']['sign_key'] = $events['config_sign_key'];
                $r                             = $this->baiduReport($result, $reportType);
                //记录报送过的数据
                $insert               = $result;
                $insert['idfa']       = $data['idfa'];
                $insert['ret']        = $r;
                $insert['department'] = $events['department'];
                $this->advIosReport($insert, $reportType);
                $this->saveMongo($mongo, $result['_id']);
                return true;
            } elseif ($result['adUserId'] == 36) {
                //畅效
            } elseif ($result['adUserId'] == 29) {
                //快手
            } elseif ($result['adUserId'] == 67) {
                //YYQ
            } elseif ($result['adUserId'] == 76) {
                //多盟
                $result['_config']['appid']    = $events['config_appid'];
                $result['_config']['sign_key'] = $events['config_sign_key'];
                if (empty($result['idfa'])) {
                    $result['idfa'] = $data['idfa'];
                }

                $r = $this->dmReport($result, $reportType);
                //记录报送过的数据
                $insert               = $result;
                $insert['idfa']       = $data['idfa'];
                $insert['ret']        = $r;
                $insert['department'] = $events['department'];
                $this->advIosReport($insert, $reportType);
                $this->saveMongo($mongo, $result['_id']);
                return true;
            } elseif ($result['adUserId'] == 79) {
                //百度移动DSP
                $r = $this->bdydReport($result, $reportType);
                //记录报送过的数据
                $insert               = $result;
                $insert['idfa']       = $data['idfa'];
                $insert['ret']        = $r;
                $insert['department'] = $events['department'];
                $this->advIosReport($insert, $reportType);
                $this->saveMongo($mongo, $result['_id']);
                return true;
            } elseif ($result['adUserId'] == 80) {
                //MobCastle
                $r = $this->mobCastleReport($result, $reportType);
                //记录报送过的数据
                $insert               = $result;
                $insert['idfa']       = $data['idfa'];
                $insert['ret']        = $r;
                $insert['department'] = $events['department'];
                $this->advIosReport($insert, $reportType);
                $this->saveMongo($mongo, $result['_id']);
                return true;
            } elseif ($result['adUserId'] == 82) {
                //YYQ_v2
            } elseif ($result['adUserId'] == 78) {
                //vungle
                if (!isset($result['callBackUrl'])) {
                    $url           = 'http://api.vungle.com/api/v3/new';
                    $request_param = array(
                        'app_id'     => $result['appid'],
                        'ifa'        => $result['idfa'],
                        'conversion' => 1,
                        'event_id'   => $result['eid'],
                    );
                    $attachment            = http_build_query($request_param);
                    $result['callBackUrl'] = $url . '?' . $attachment;
                }
            } elseif ($result['adUserId'] == 85) {
                //unityAds
                $r = $this->unityAdsReport($result, $reportType);
                //记录报送过的数据
                $insert               = $result;
                $insert['idfa']       = $data['idfa'];
                $insert['ret']        = $r;
                $insert['department'] = $events['department'];
                $this->advIosReport($insert, $reportType);
                $this->saveMongo($mongo, $result['_id']);
                return true;
            } elseif ($result['adUserId'] == 89) {
                //360cpc
            } elseif ($result['adUserId'] == 94) {
                //安尚CPA
                $url   = 'http://adsdos.cn/iosact/act.php';
                $param = array(
                    'appid'     => $result['appid'],
                    'idfa'      => $result['idfa'],
                    'idfamd5'   => $result['idfamd5'],
                    'acttime'   => time(),
                    'actip'     => $result['ip'],
                );
                $attachment            = http_build_query($param);
                $result['callBackUrl'] = $url . '?' . $attachment;
            } elseif ($result['adUserId'] == 95) {
                //xmob
            } elseif(in_array($result['adUserId'], [71, 97])) {
                //qq浏览器
                return true;
            }

            $r    = curl_get($result['callBackUrl'], 5);
            $info = json_decode($r, true); //{"msg": "success", "code": 0, "ret": 0}
            if ($result['adUserId'] == 6 && $info['msg'] != 'success') {
                //头条报送失败，重新报送一次
                $r = curl_get($result['callBackUrl'], 5);
            }
            //记录报送过的数据
            $insert               = $result;
            $insert['idfa']       = $data['idfa'];
            $insert['ret']        = $r;
            $insert['department'] = $events['department'];
            $this->advIosReport($insert, $reportType);
            $this->saveMongo($mongo, $result['_id']);
            // $mongo->update('advios', array('status'=>0),array('_id'=>$result['_id']));
            // $mongo->close();
            //记录日志
            log_save("[result]返回结果：" . $r . "    [data]请求参数：" . json_encode($result) . "   " . "传入数据：" . json_encode($data) . "  [msg]报送日志", "info", "", "new_advter_log_" . $result['advterType'] . '_' . date("Y-m-d") . '.log', 'ios_advter_log');
        } else {
            //调试记录日志
            log_save("[data]传入数据：" . json_encode($data) . "  [msg]idfa异常日志", "info", "", "new_advter_log_" . $events['advteruser_id'] . '_' . date("Y-m-d") . '.log', 'ios_debug_advter_log');
        }
    }

    /**
     * 广点通数据上报
     * @DateTime  2017-07-26T22:47:06+0800
     * @param     [array]         $data       [需要上报的数据]
     * @param     [int]           $reportType [上报的类型] 1:激活 2:充值报送 3:注册报送
     * @return    [bool]                      [上报是否成功]
     */
    private function gdtReport($data, $reportType = 1)
    {
        //上报地址格式--http://t.gdt.qq.com/conv/app/{appid}/conv?v={data}&conv_type={conv_type}&app_type={app_type}&advertiser_id={advertiser_id}
        if (!$data['advter_id'] || !$data['muid']) {
            return false;
        }

        //组合参数
        $param = array(
            'muid'      => $data['muid'],
            'conv_time' => time(),
            'click_id'  => $data['click_id'],
            'client_ip' => $data['ip'],
        );
        $reportType == 2 && $param['value'] = ($data['amount'] * 100) * 0.3;
        $query_string                       = http_build_query($param);

        //参数签名
        $page      = 'http://t.gdt.qq.com/conv/app/' . $data['_config']['appid'] . '/conv?' . $query_string;
        $property  = $data['_config']['sign_key'] . '&GET&' . urlencode($page);
        $signature = strtolower(md5($property));
        //参数加密
        $base_data   = $query_string . '&sign=' . urlencode($signature);
        $secret_data = $this->simpleXor($base_data, $data['_config']['encrypt_key']);
        //组装请求
        switch ($reportType) {
            case '1':
                $conv_type = 'MOBILEAPP_ACTIVITE';
                break;
            case '2':
                $conv_type = 'MOBILEAPP_COST';
                break;
            case '3':
                $conv_type = 'MOBILEAPP_REGISTER';
                break;
            default:
                $conv_type = '';
                break;
        }

        //组合参数
        $request_param = array(
            'conv_type'     => $conv_type,
            'app_type'      => 'IOS',
            'advertiser_id' => $data['_config']['advertiser_id'],
        );
        $attachment = http_build_query($request_param);

        $url = "http://t.gdt.qq.com/conv/app/{$data['_config']['appid']}/conv?v={$secret_data}&{$attachment}";
        $res = json_decode(curl_get($url), true);
        //记录报送过的数据
        $insert               = $data;
        $insert['idfa']       = $data['idfa'];
        $insert['ret']        = json_encode($res);
        $insert['department'] = $data['department'];
        $this->advIosReport($insert, $reportType);

        if ($res['ret'] == 0) {
            log_save("[result]返回结果：" . json_encode($res) . "    [data]" . $query_string . '___' . json_encode($request_param) . "   " . "传入数据：" . json_encode($data) . "  [msg]报送日志", "info", "", "new_advter_log_gdt" . '_' . date("Y-m-d") . '.log', 'ios_advter_log');
        } else {
            log_save("[result]返回结果：" . json_encode($res) . "    [data]" . $query_string . '___' . json_encode($request_param) . "   " . "传入数据：" . json_encode($data) . "  [msg]报送失败日志", "info", "", "new_advter_log_gdt" . '_' . date("Y-m-d") . '.log', 'ios_debug_advter_log');
        }

        return true;
    }

    /**
     * 微信IOS激活上报
     * @DateTime  2017-07-25T22:47:06+0800
     * @param     [array]         $data [收集到的点击数据]
     * @return    [bool]                [上报是否成功]
     */
    private function wxReport($data, $reportType)
    {
        //上报地址格式--http://t.gdt.qq.com/conv/app/{appid}/conv?v={data}&conv_type={conv_type}&app_type={app_type}&advertiser_id={uid}
        if (!$data['advter_id'] || !$data['idfa']) {
            return false;
        }

        $time = time();
        //拼接参数
        $request_param = array(
            'app_type'  => 'IOS',
            'click_id'  => $data['click_id'],
            'client_ip' => $data['ip'],
            'conv_time' => $time,
            'muid'      => $data['muid'],
            'sign_key'  => $data['_config']['sign_key'],
        );
        $query_string = http_build_query($request_param);

        //组装请求
        switch ($reportType) {
            case '1':
                $conv_type = 'MOBILEAPP_ACTIVITE';
                break;
            case '2':
                $conv_type = 'MOBILEAPP_COST';
                break;
            case '3':
                $conv_type = 'MOBILEAPP_REGISTER';
                break;
            default:
                $conv_type = '';
                break;
        }

        //post参数
        $param = array(
            'click_id'      => $data['click_id'],
            'muid'          => $data['muid'],
            'appid'         => $data['_config']['appid'],
            'conv_time'     => $time,
            'client_ip'     => $data['ip'],
            'encstr'        => md5($query_string),
            'encver'        => '1.0',
            'advertiser_id' => $data['_config']['advertiser_id'],
            'app_type'      => 'IOS',
            'conv_type'     => $conv_type,
        );

        if ($reportType == 2) {
            $param['value'] = ($data['amount'] * 100);
        }

        $url = "https://t.gdt.qq.com/conv/app/{$data['_config']['appid']}/conv";
        $res = json_decode(curl_post($url, http_build_query($param)), true);
        //记录报送过的数据
        $insert               = $data;
        $insert['idfa']       = $data['idfa'];
        $insert['ret']        = json_encode($res);
        $insert['department'] = $data['department'];
        $this->advIosReport($insert, $reportType);

        if ($res['ret'] == 0) {
            log_save("[result]返回结果：" . json_encode($res) . "    [data]请求参数：" . json_encode($request_param) . "   " . "传入数据：" . json_encode($data) . "  [msg]报送日志", "info", "", "new_advter_log_wx" . '_' . date("Y-m-d") . '.log', 'ios_advter_log');

            return json_encode($res);
        } else {
            log_save("[result]返回结果：" . json_encode($res) . "    [data]请求参数：" . json_encode($request_param) . "   " . "传入数据：" . json_encode($data) . "  [msg]报送失败日志", "info", "", "new_advter_log_wx" . '_' . date("Y-m-d") . '.log', 'ios_debug_advter_log');

            return json_encode($res);
        }

    }

    private function baiduReport($data, $reportType = 1)
    {
        //上报地址格式--http://als.baidu.com/cb/actionCb?a_type=activate&a_value=0&ext_info=T6H2n7u&sign=cab06bc0cebd20482b5892cb72864a62

        if (!$data['advter_id'] || !$data['idfa']) {
            return false;
        }

        $callback_url = $data['callBackUrl'];
        $akey         = $data['_config']['sign_key'];

        switch ($reportType) {
            case '1':
                $conv_type = 'activate';
                break;
            case '2':
                $conv_type = 'orders';
                break;
            case '3':
                $conv_type = 'register';
                break;
            default:
                $conv_type = '';
                break;
        }

        $callback_url = str_replace("{{ATYPE}}", $conv_type, $callback_url);

        if ($reportType == 2) {
            $value = ($data['amount'] * 100);
        } else {
            $value = 0;
        }
        $callback_url = str_replace("{{AVALUE}}", $value, $callback_url);

        $signature = md5($callback_url . $akey);

        $url = $callback_url . '&sign=' . $signature;

        $res = json_decode(curl_get($url), true);

        if ($res['error_code'] == 0) {
            log_save("[result]返回结果：" . json_encode($res) . "    [data]请求参数：" . $url . "   " . "传入数据：" . json_encode($data) . "  [msg]报送日志", "info", "", "new_advter_log_" . $data['advterType'] . '_' . date("Y-m-d") . '.log', 'ios_advter_log');
            return json_encode($res);
        } else {
            log_save("[result]返回结果：" . json_encode($res) . "    [data]请求参数：" . $url . "   " . "传入数据：" . json_encode($data) . "  [msg]报送失败日志", "info", "", "new_advter_log_" . $data['advterType'] . '_' . date("Y-m-d") . '.log', 'ios_debug_advter_log');
            return json_encode($res);
        }
    }

    private function dmReport($data, $reportType = 1)
    {
        //上报地址格式--http://e.domob.cn/track/ow/api/callback?appkey=531266294&ifa=511F7987-6E2F-423A-BFED-E4C52CB5A6DC&acttime=1391502359000&acttype=2&returnFormat=1&sign=ed1e44129c95972df08a9e95c8bebd23&actip=115.183.152.45&appversion=2.0.1&userid=4124bc0a9335c27f086f24ba207a4912&clktime=1391501359000&clkip=119.255.14.220

        if (!$data['advter_id'] || !$data['idfa']) {
            return false;
        }

        switch ($reportType) {
            case '1':
                $acttype = '2';
                break;
            case '2':
                $acttype = '4';
                break;
            case '3':
                $acttype = '11';
                break;
            default:
                $acttype = '';
                break;
        }

        $request_param = array(
            'appkey'       => $data['_config']['appid'],
            'acttype'      => $acttype,
            'ifa'          => $data['idfa'],
            'ifamd5'       => $data['ifamd5'],
            'acttime'      => msectime(),
            'clktime'      => ($data['clickTime'] * 1000),
            'returnFormat' => 1,
        );

        if ($reportType == 2) {
            $request_param['price'] = $data['amount'] * 100;
        }

        $attachment = http_build_query($request_param);

        $sign = $this->getDomobSign($data['_config']['appid'], $data['idfa'], $data['ifamd5'], $data['_config']['sign_key']);

        $url = 'http://e.domob.cn/track/ow/api/callback?sign=' . $sign . '&' . $attachment;

        $res = json_decode(curl_get($url, 3), true);

        if ($res['success'] == true) {
            log_save("[result]返回结果：" . json_encode($res) . "    [data]请求参数：" . $url . "   " . "传入数据：" . json_encode($data) . "  [msg]报送日志", "info", "", "new_advter_log_" . $data['advterType'] . '_' . date("Y-m-d") . '.log', 'ios_advter_log');
            return json_encode($res);
        } else {
            log_save("[result]返回结果：" . json_encode($res) . "    [data]请求参数：" . $url . "   " . "传入数据：" . json_encode($data) . "  [msg]报送失败日志", "info", "", "new_advter_log_" . $data['advterType'] . '_' . date("Y-m-d") . '.log', 'ios_debug_advter_log');
            return json_encode($res);
        }
    }

    private function bdydReport($data, $reportType = 1)
    {
        //上报地址格式--http://mobads-logs.baidu.com/dz.zb?type=12&mac=02:EA:FF:21:AA:20&idfa=111F7987-6E2F-473A-BFED-E4C52CB5A6DC&md5=0&t=20150606123015&traceid= 550e0d5bb0d8ebb3fd6d48,42aba3cafce300&act=0&pk=com.baidu.mobads&crid=1000&appid=a1cd7e67

        if (!$data['advter_id'] || !$data['idfa']) {
            return false;
        }

        switch ($reportType) {
            case '1':
                $act = '0';
                break;
            case '2':
                $act = '6';
                break;
            case '3':
                $act = '3';
                break;
            default:
                $act = '';
                break;
        }

        $request_param = array(
            'mac'     => $data['mac'],
            'idfa'    => $data['idfa'],
            'traceid' => $data['traceid'],
            't'       => date('YmdHis'),
            'act'     => $act,
            'md5'     => 0,
            'crid'    => $data['crid'],
            'appid'   => $data['appid'],
            'pk'      => $data['pk'],
        );

        // if($reportType == 2){
        //     $request_param['amount'] = $data['amount'] * 100;
        // }

        $attachment = http_build_query($request_param);

        $url = 'http://mobads-logs.baidu.com/dz.zb?type=12&' . $attachment;

        $res = json_decode(curl_get($url, 3), true);

        if ($res['error'] == 0) {
            log_save("[result]返回结果：" . json_encode($res) . "    [data]请求参数：" . $url . "   " . "传入数据：" . json_encode($data) . "  [msg]报送日志", "info", "", "new_advter_log_" . $data['advterType'] . '_' . date("Y-m-d") . '.log', 'ios_advter_log');
            return json_encode($res);
        } else {
            log_save("[result]返回结果：" . json_encode($res) . "    [data]请求参数：" . $url . "   " . "传入数据：" . json_encode($data) . "  [msg]报送失败日志", "info", "", "new_advter_log_" . $data['advterType'] . '_' . date("Y-m-d") . '.log', 'ios_debug_advter_log');
            return json_encode($res);
        }
    }

    private function mobCastleReport($data, $reportType = 1)
    {
        //上报地址格式--http://mobads-logs.baidu.com/dz.zb?type=12&mac=02:EA:FF:21:AA:20&idfa=111F7987-6E2F-473A-BFED-E4C52CB5A6DC&md5=0&t=20150606123015&traceid= 550e0d5bb0d8ebb3fd6d48,42aba3cafce300&act=0&pk=com.baidu.mobads&crid=1000&appid=a1cd7e67

        if (!$data['advter_id'] || !$data['idfa']) {
            return false;
        }

        switch ($reportType) {
            case '1':
                $act = 'fopen';
                break;
            case '2':
                $act = 'purchase';
                break;
            case '3':
                $act = 'register';
                break;
            default:
                $act = '';
                break;
        }

        $request_param = array(
            'clickid'    => $data['clickid'],
            'c_idfa'     => $data['idfa'],
            'source'     => 'cmgc',
            'event_name' => $act,
        );

        if ($reportType == 2) {
            $request_param['value'] = $data['amount'];
        }

        $attachment = http_build_query($request_param);

        if ($data['callBackUrl']) {
            $url = $data['callBackUrl'] . '?' . $attachment;
        } else {
            $url = 'http://e.cpa.mobcastlead.com/e?' . $attachment;
        }

        $res = curl_get($url, 3);

        if ($res == 'success') {
            log_save("[result]返回结果：" . $res . "    [data]请求参数：" . $url . "   " . "传入数据：" . json_encode($data) . "  [msg]报送日志", "info", "", "new_advter_log_" . $data['advterType'] . '_' . date("Y-m-d") . '.log', 'ios_advter_log');
            return json_encode($res);
        } else {
            log_save("[result]返回结果：" . $res . "    [data]请求参数：" . $url . "   " . "传入数据：" . json_encode($data) . "  [msg]报送失败日志", "info", "", "new_advter_log_" . $data['advterType'] . '_' . date("Y-m-d") . '.log', 'ios_debug_advter_log');
            return json_encode($res);
        }
    }

    /**
     * [unityAdsReport description]
     * @param  [type]  $data       [description]
     * @param  integer $reportType [description]
     * @return [type]              [description]
     */
    private function unityAdsReport($data, $reportType = 1)
    {
        if (!$data['advter_id'] || !$data['idfa']) {
            return false;
        }

        $gamerId = $data['gid'];

        $url = 'https://postback.unityads.unity3d.com/games/' . $gamerId . '/install?advertisingTrackingId=' . strtoupper($data['idfa']);

        $res = json_decode(curl_get($url, 3), true);

        if ($res['install'] == true) {
            log_save("[result]返回结果：" . json_encode($res) . "    [data]请求参数：" . $url . "   " . "传入数据：" . json_encode($data) . "  [msg]报送日志", "info", "", "new_advter_log_" . $data['advterType'] . '_' . date("Y-m-d") . '.log', 'ios_advter_log');
            return json_encode($res);
        } else {
            log_save("[result]返回结果：" . json_encode($res) . "    [data]请求参数：" . $url . "   " . "传入数据：" . json_encode($data) . "  [msg]报送失败日志", "info", "", "new_advter_log_" . $data['advterType'] . '_' . date("Y-m-d") . '.log', 'ios_debug_advter_log');
            return json_encode($res);
        }
    }

    /** 生成domob激活回调的签名。 */
    private function getDomobSign($appkey, $ifa, $ifamd5, $sign_key)
    {
        $s = sprintf("%s,%s,%s,%s", $appkey, $ifa, $ifamd5, $sign_key);
        return md5($s);
    }

    /**
     * 简单异或加密
     * @DateTime  2017-07-25T22:45:40+0800
     * @param     [string]       $base_data   [待加密字符串]
     * @param     [string]       $encrypt_key [加密key]
     * @return    [string]       [加密后结果]
     */
    private function simpleXor($base_data, $encrypt_key)
    {
        $retval     = '';
        $source_arr = str_split($base_data);

        $j = 0;
        foreach ($source_arr as $ch) {
            $retval .= chr(ord($ch) ^ ord($encrypt_key[$j]));
            $j = $j + 1;
            $j = $j % (strlen($encrypt_key));
        }

        return urlencode(base64_encode($retval));
    }

    /**
     * IOS广告报送数据插入
     *
     * @param array $data                  [插入的数据]
     * @param int $reportType            [上报的类型] 1:激活 2:充值报送 3:注册报送
     * @return boolean
     */
    private function advIosReport($data, $reportType = 1)
    {
        if (empty($data)) {
            return false;
        }

        $mongo = new \Vendor\ApiMongoDB\ApiMongoDB(array(
            'host'     => '172.16.0.9',
            'port'     => 59818,
            'username' => 'ZgMongoAdvter',
            'password' => 'lkjet#$lj10!~!3sji^',
            'db'       => 'advter',
            'cmd'      => '$',
        ));
        $time = time();
        //记录报送过的数据
        $insert = array(
            'department'  => $data['department'],
            'game_id'     => $data['game_id'],
            'agent'       => $data['agent'],
            'advter_id'   => $data['advter_id'],
            'muid'        => $data['muid'] ? $data['muid'] : '',
            'idfa'        => $data['idfa'],
            'amount'      => $data['amount'] ? $data['amount'] : '0',
            'adUserId'    => $data['adUserId'],
            'advterType'  => $data['advterType'],
            'ip'          => $data['ip'] ? $data['ip'] : get_ip_address(),
            'callBackUrl' => $data['callBackUrl'],
            'reportType'  => $reportType,
            'ret'         => $data['ret'],
            'clickTime'   => (int) $data['clickTime'] ? (int) $data['clickTime'] : $time,
            'createTime'  => $time,
        );
        $res = $mongo->insert("advIosReport", $insert);
        $mongo->close();
        if ($res) {
            return true;
        }
        return false;
    }
}
