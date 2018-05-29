<?php
/**
 * Created by Sublime.
 * User: XSM
 * Date: 2017/10/30
 * Time: 14:41
 *
 *
 * 安卓推广匹配模型
 */

namespace Api\Model;

use Think\Model;

class ANDMatchModel extends Model
{
    protected $autoCheckFields = false; //关闭自动检测数据库字段
    private $mongo             = null; //mongo对象

    //融合的热云域名
    private $fusionReyunUrl = "http://log.reyun.com";
    //融合的热云地址
    private $fusionReyunUrltype = array(
        1 => "/receive/rest/install",
        2 => "/receive/rest/startup",
        3 => "/receive/rest/register",
        4 => "/receive/rest/loggedin",
        5 => "/receive/rest/payment",
    );
    //融合的旧版热云地址
    private $fusionOldReyunUrltype = array(
        1 => "/receive/track/install",
        2 => "/receive/track/startup",
        3 => "/receive/track/register",
        4 => "/receive/track/loggedin",
        5 => "/receive/track/payment",
    );
    //融合的热云参数
    private $fusionReyunConfig = array(
        "snfxjDYBAND" => array("appid" => "baba21fb01df3abf25d151f0211a57d8"), //少年焚仙记[第一波]
        "xlhxDYBAND"  => array("appid" => "4de781878b7a87b78eea6f7461dff6f4"), //仙灵幻想[第一波]
    );
    //融合的旧版热云参数
    private $fusionOldReyunConfig = array(
        "jmtxJWAND" => array("appid" => "b5ea79c9594aed5c448483dd1fd584f9"), //剑萌天下[嘉玩]
    );

    /**
     * 主动上报激活，充值数据，注册
     * @param $data
     * @param $advterStaus 1:激活 2:充值报送 3:注册报送
     * @return bool|mixed
     */
    public function activeReport($data, $advterStaus = 1)
    {

        if (!$data['agent'] || !$data['game_id']) {
            return false;
        }

        $this->gdtReport($data, $advterStaus);
        $this->andAdvterCallBack($data, $advterStaus);
        if ($this->mongo) {
            $this->mongo->close();
        }
    }

    /**
     * 报送广告激活数据
     * @param $data
     * @param $reportType [上报的类型] 1:激活 2:充值报送 3:注册报送
     * @return bool|mixed
     */
    public function andAdvterCallBack($data, $reportType = 1)
    {

        if (!$data['agent'] || !$data['game_id']) {
            //调试记录日志
            log_save("[data]我方数据:" . json_encode($data) . "  [msg]游戏或渠道号异常日志", "info", "", "advter_log_" . date("Y-m-d") . '.log', 'and_debug_advter_log');
            return false;
        }

        $agentStatus = M('agent')->where(array('agent' => $data['agent']))->find();

        if (!$agentStatus || count($agentStatus) < 1) {
            return false;
        }

        if ($agentStatus['advterStaus']) {
            if ($agentStatus['advterStaus'] != $reportType) {
                log_save("[data]我方数据:" . json_encode($data) . "  [msg]报送类型与后台设置不一致日志", "info", "", "advter_log_" . date("Y-m-d") . '.log', 'and_debug_advter_log');
                return false;
            }
        }

        //微信广点通5天，其他7天内的数据(查从库)
        $this->mongo = new \Vendor\ApiMongoDB\ApiMongoDB(array('host' => '172.16.0.9', 'port' => 59818, 'username' => 'ZgMongoAdvter', 'password' => 'lkjet#$lj10!~!3sji^', 'db' => 'advter', 'cmd' => '$'));
        //匹配用户来源
        if ($agentStatus['advteruser_id'] == 2 || $agentStatus['advteruser_id'] == 10) {
            $startTime = time() - 432000; //5天内的有效
        } else {
            $startTime = time() - 604800; //7天内的有效
        }
        $map['clickTime'] = array($this->mongo->cmd('>') => $startTime);

        //安卓报送
        if (!empty($data['imei']) || !empty($data['imei2'])) {
            if ($agentStatus['advteruser_id'] == 2 || $agentStatus['advteruser_id'] == 10) {
                $muid  = strtolower(md5(strtolower($data['imei'])));
                $muid2 = strtolower(md5(strtolower($data['imei2'])));
            } elseif ($agentStatus['advteruser_id'] == 7) {
                if (whitelist($imei = $data['imei'])) {
                    $temp = M('whitelist', 'lg_')->field('imeiProxy')->where(array('imei' => $data['imei']))->order('id DESC')->find();
                    if ($temp) {
                        log_save("old imei:" . $data['imei'], "info", "", "imei_log_" . date("Y-m-d") . '.log', 'and_advter_log');
                        $data['imei'] = $temp['imeiProxy'];
                        log_save("proxy imei:" . $data['imei'], "info", "", "imei_log_" . date("Y-m-d") . '.log', 'and_advter_log');
                    }
                }
                $muid  = strtolower(md5(strtoupper($data['imei'])));
                $muid2 = strtolower(md5(strtoupper($data['imei2'])));
            } elseif ($agentStatus['advteruser_id'] == 78 || $agentStatus['advteruser_id'] == 85) {
                $muid = strtolower($data['udid']);
            } else {
                $muid  = strtolower(md5($data['imei']));
                $muid2 = strtolower(md5($data['imei2']));
            }

            // $map[$this->mongo->cmd('or')] = array( array('muid'=>$muid), array('muid'=>$muid2) );
            $map['muid']    = (string) $muid;
            $map['agent']   = (string) $data['agent'];
            $map['game_id'] = (string) $data['game_id'];
            $map['os']      = 1;
            $res            = $this->mongo->select('advand', $map, array(), array('createTime' => -1), 1);
            if (!$res && !empty($data['imei2'])) {
                //用imei2查询
                $map['muid'] = (string) $muid2;
                $res         = $this->mongo->select('advand', $map, array(), array('createTime' => -1), 1);
            }
            $result = $res[0];
        }

        if ($result) {
            if ($reportType == 2) {
                $result['amount'] = $data['amount'];
            }

            if ($result['adUserId'] == 10) {
                //微信
                $result['departmentId'] = $agentStatus['departmentId'];
                if ($data && $result) {
                    $result = array_merge($data, $result);
                }

                $this->wxReport($result, $reportType);
                return true;
            } elseif ($result['adUserId'] == 6) {
                //今日头条
                $result['departmentId'] = $agentStatus['departmentId'];
                if ($data && $result) {
                    $result = array_merge($data, $result);
                }

                $this->jrttReport($result, $reportType);
                return true;
            } elseif ($result['adUserId'] == 2) {
                //广点通点击上报
                $result['departmentId'] = $agentStatus['departmentId'];
                if ($data && $result) {
                    $result = array_merge($data, $result);
                }

                $this->gdtClickReport($result, $reportType);
                return true;
            } elseif (in_array($result['adUserId'],[3, 71, 97])) {
                //爱奇艺、qq浏览器不需要上报
                return true;
            } elseif ($result['adUserId'] == 14) {
                //百度信息流
                $result['departmentId'] = $agentStatus['departmentId'];
                $result['imei']         = $data['imei'];
                $result['imei2']        = $data['imei2'];
                $this->baiduReport($result, $reportType);
                return true;
            } elseif ($result['adUserId'] == 76) {
                //多盟
                $result['departmentId'] = $agentStatus['departmentId'];
                if (empty($result['imei'])) {
                    $result['imei'] = $data['imei'];
                }

                if ($data && $result) {
                    $result = array_merge($data, $result);
                }

                $this->dmReport($result, $reportType);
                return true;
            } elseif ($result['adUserId'] == 79) {
                //百度移动DSP
                $result['departmentId'] = $agentStatus['departmentId'];
                if ($data && $result) {
                    $result = array_merge($data, $result);
                }

                $this->bdydReport($result, $reportType);
                return true;
            } elseif ($result['adUserId'] == 78) {
                //vungle
                if (!isset($result['callBackUrl'])) {
                    $url           = 'http://api.vungle.com/api/v3/new';
                    $request_param = array(
                        'app_id'     => $result['appid'],
                        'aaid'       => $result['aaid'],
                        'isu'        => $result['android_id'],
                        'conversion' => 1,
                        'event_id'   => $result['id'],
                    );
                    $attachment            = http_build_query($request_param);
                    $result['callBackUrl'] = $url . '?' . $attachment;
                }
            } elseif ($result['adUserId'] == 85) {
                //unityAds
                $result['departmentId'] = $agentStatus['departmentId'];
                if ($data && $result) {
                    $result = array_merge($data, $result);
                }

                $this->unityAdsReport($result, $reportType);
                return true;
            } elseif (in_array($result['adUserId'],[89,95,29,7])) {
                 // uc头条、快手、360cpc、xmob 不需要特殊处理
            }

            $r    = curl_get($result['callBackUrl'], 5);
            $info = json_decode($r, true);
            if ($result['adUserId'] == 6 && $info['msg'] != 'success') {
                //头条报送失败，重新报送一次
                $r = curl_get($result['callBackUrl'], 5);
            }
            //记录上报过的广告数据
            $insert               = $result;
            $insert['ret']        = $r;
            $insert['imei']       = $data['imei'];
            $insert['imei2']      = $data['imei2'];
            $insert['department'] = $agentStatus['departmentId'];
            $this->advAndReport($insert, $reportType);

            //报送过的更新状态
            $this->mongo->update('advand', array('status' => 0), array('_id' => $result['_id']));
            //记录日志
            log_save("[result]返回结果：" . $r . "    [data]请求参数：" . json_encode($result) . "   " . "传入数据：" . json_encode($data) . "  [msg]报送日志", "info", "", "advter_log_" . $result['advterType'] . '_' . date("Y-m-d") . '.log', 'and_advter_log');
        } else {
            //调试记录日志
            log_save("[data]传入数据:" . json_encode($data) . "  [msg]imei异常日志", "info", "", "advter_log_" . $agentStatus['advteruser_id'] . '_' . date("Y-m-d") . '.log', 'and_debug_advter_log');
        }
    }

    /**
     * 广点通数据上报(方案二之主动上报)
     * @DateTime  2017-07-26T22:47:06+0800
     * @param     [array]         $data       [需要上报的数据]
     * @param     [int]           $reportType [上报的类型] 1:激活 2:充值报送 3:注册报送
     * @return    [bool]                      [上报是否成功]
     */
    private function gdtReport($data, $reportType = 1)
    {
        //上报地址格式--http://t.gdt.qq.com/conv/app/{appid}/conv?v={data}&conv_type={conv_type}&app_type={app_type}&advertiser_id={advertiser_id}

        $_config = M('and_agent_config')->where(array('agent' => $data['agent'], 'gdt_type' => 2))->find();
        if (!$_config['config_sign_key'] || !$_config['config_appid'] || !$_config['config_encrypt_key']) {
            return false;
        }
        $agentStatus = M('agent')->where(array('agent' => $data['agent']))->find();

        //组合参数
        $param = array(
            'muid'      => strtolower(md5($data['imei'])),
            'conv_time' => time(),
            'client_ip' => $data['ip'],
        );
        $reportType == 2 && $param['value'] = ($data['amount'] * 100) * 0.3;
        $query_string                       = http_build_query($param);

        //参数签名
        $page      = 'http://t.gdt.qq.com/conv/app/' . $_config['config_appid'] . '/conv?' . $query_string;
        $property  = $_config['config_sign_key'] . '&GET&' . urlencode($page);
        $signature = strtolower(md5($property));
        //参数加密
        $base_data   = $query_string . '&sign=' . urlencode($signature);
        $secret_data = $this->simpleXor($base_data, $_config['config_encrypt_key']);
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

        if ($reportType == 1) {
            return true; //由于广点通的bug，激活暂时放到注册报送
        } elseif ($reportType == 3) {
            $conv_type_arr = array('MOBILEAPP_REGISTER', 'MOBILEAPP_ACTIVITE');
            foreach ($conv_type_arr as $value) {
                $request_param = array(
                    'conv_type'     => $value,
                    'app_type'      => $_config['config_conv_type'],
                    'advertiser_id' => $_config['config_advertiser_id'],
                );
                $attachment = http_build_query($request_param);
                $url        = "http://t.gdt.qq.com/conv/app/{$_config['config_appid']}/conv?v={$secret_data}&{$attachment}";
                $res        = json_decode(curl_get($url), true);

                //记录上报过的广告数据
                $insert                                 = $data;
                $insert['ret']                          = json_encode($res);
                $insert['imei']                         = $data['imei'];
                $insert['imei2']                        = $data['imei2'];
                $insert['callBackUrl']                  = $url;
                $insert['department']                   = $agentStatus['departmentId'];
                $insert['adUserId']                     = $agentStatus['advteruser_id'];
                $insert['advterType']                   = 'gdt2';
                $value == 'MOBILEAPP_ACTIVITE' ? $rtype = 1 : $rtype = 3;
                $this->advAndReport($insert, $rtype);

                log_save("[result]返回结果：" . json_encode($res) . "    [data]请求参数：" . $query_string . '___' . json_encode($request_param) . "   " . "传入数据：" . json_encode($data) . "  [msg]报送日志", "info", "", "advter_log_gdt" . '_' . date("Y-m-d") . '.log', 'and_advter_log');
            }
        } else {
            $request_param = array(
                'conv_type'     => $conv_type,
                'app_type'      => $_config['config_conv_type'],
                'advertiser_id' => $_config['config_advertiser_id'],
            );
            $attachment = http_build_query($request_param);

            $url = "http://t.gdt.qq.com/conv/app/{$_config['config_appid']}/conv?v={$secret_data}&{$attachment}";
            $res = json_decode(curl_get($url), true);

            //记录上报过的广告数据
            $insert                = $data;
            $insert['ret']         = json_encode($res);
            $insert['callBackUrl'] = $url;
            $insert['department']  = $agentStatus['departmentId'];
            $insert['adUserId']    = $agentStatus['advteruser_id'];
            $insert['advterType']  = 'gdt2';
            $this->advAndReport($insert, $reportType);

            log_save("[result]返回结果：" . json_encode($res) . "    [data]请求参数：" . $query_string . '___' . json_encode($request_param) . "   " . "传入数据：" . json_encode($data) . "  [msg]报送日志", "info", "", "advter_log_gdt" . '_' . date("Y-m-d") . '.log', 'and_advter_log');
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
        $_config = M('and_agent_config')->where(array('agent' => $data['agent']))->find();
        if (!$_config['config_sign_key'] || !$_config['config_appid'] || !$_config['config_encrypt_key']) {
            return false;
        }
        $agentStatus = M('agent')->where(array('agent' => $data['agent']))->find();

        //拼接参数
        $request_param = array(
            'app_type'  => 'ANDROID',
            'click_id'  => $data['click_id'],
            'client_ip' => $data['ip'],
            'conv_time' => time(),
            'muid'      => $data['muid'],
            'sign_key'  => $_config['config_sign_key'],
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
            'appid'         => $_config['config_appid'],
            'conv_time'     => time(),
            'client_ip'     => $data['ip'],
            'encstr'        => md5($query_string),
            'encver'        => '1.0',
            'advertiser_id' => $_config['advertiser_id'],
            'app_type'      => 'ANDROID',
            'conv_type'     => $conv_type,
        );

        if ($reportType == 2) {
            $param['value'] = ($data['amount'] * 100);
        }

        $url = "https://t.gdt.qq.com/conv/app/{$_config['config_appid']}/conv";
        $res = json_decode(curl_post($url, http_build_query($param)), true);

        //记录上报过的广告数据
        $insert                = $data;
        $insert['ret']         = json_encode($res);
        $insert['callBackUrl'] = $url;
        $insert['department']  = $agentStatus['departmentId'];
        $insert['adUserId']    = $agentStatus['advteruser_id'];
        $insert['advterType']  = 'wx';
        $this->advAndReport($insert, $reportType);

        $this->mongo->update('advand', array('status' => 0), array('_id' => $data['_id']));
        $this->mongo->close();
        if ($res['ret'] == 0) {
            log_save("[result]返回结果：" . json_encode($res) . "    [data]请求参数：" . $query_string . '___' . json_encode($request_param) . "   " . "传入数据：" . json_encode($data) . "  [msg]报送日志", "info", "", "advter_log_wx" . '_' . date("Y-m-d") . '.log', 'and_advter_log');
        } else {
            log_save("[result]返回结果：" . json_encode($res) . "    [data]请求参数：" . $url . "   " . "传入数据：" . json_encode($data) . "  [msg]报送失败日志", "info", "", "advter_log_wx" . '_' . date("Y-m-d") . '.log', 'and_debug_advter_log');
        }

        return true;
    }

    /**
     * 广点通数据上报(方案一)
     * @DateTime  2017-07-26T22:47:06+0800
     * @param     [array]         $data       [需要上报的数据]
     * @param     [int]           $reportType [上报的类型] 1:激活 2:充值报送 3:注册报送
     * @return    [bool]                      [上报是否成功]
     */
    private function gdtClickReport($data, $reportType = 1)
    {
        //上报地址格式--http://t.gdt.qq.com/conv/app/{appid}/conv?v={data}&conv_type={conv_type}&app_type={app_type}&advertiser_id={advertiser_id}
        if (!$data['agent'] || !$data['muid']) {
            return false;
        }

        $specialAgents = array();
        if (in_array($data['agent'], $specialAgents) && $reportType != 2) {
            return true;
        }

        $_config = M('and_agent_config')->where(array('agent' => $data['agent'], 'gdt_type' => 1))->find();
        if (!$_config['config_sign_key'] || !$_config['config_appid'] || !$_config['config_encrypt_key']) {
            return false;
        }

        $time            = time();
        $encstr          = md5("app_type={$_config['config_conv_type']}&click_id={$data['click_id']}&client_ip={$data['ip']}&conv_time={$time}&muid={$data['muid']}&sign_key={$_config['config_sign_key']}");
        $data['nowTime'] = $time;
        $data['encstr']  = $encstr;
        $agents          = '';
        if ($reportType == 1) {
            return true; //由于广点通的bug，激活暂时放到注册报送
        } elseif ($reportType == 3) {
            //报注册
            $conv_type_arr = array('MOBILEAPP_REGISTER', 'MOBILEAPP_ACTIVITE');
        } else {
            if (in_array($data['agent'], $specialAgents)) {
                $agents        = $data['agent'];
                $conv_type_arr = array('MOBILEAPP_REGISTER', 'MOBILEAPP_COST', 'MOBILEAPP_ACTIVITE');
            } else {
                $conv_type_arr = array('MOBILEAPP_COST');
            }
        }
        $this->specialReport($conv_type_arr, $data, $_config, $reportType, $agents);

        $this->mongo->update('advand', array('status' => 0), array('_id' => $data['_id']));

        return true;
    }

    private function specialReport($conv_type_arr, $data, $_config, $reportType, $agents = '')
    {

        foreach ($conv_type_arr as $value) {

            $param = array(
                'click_id'      => $data['click_id'],
                'muid'          => $data['muid'],
                'appid'         => $_config['config_appid'],
                'conv_time'     => $data['nowTime'],
                'client_ip'     => $data['ip'],
                'encstr'        => $data['encstr'],
                'encver'        => '1.0',
                'advertiser_id' => $_config['config_advertiser_id'],
                'app_type'      => $_config['config_conv_type'],
                'conv_type'     => $value,
            );
            if ($agents) {
                $reportType == 2 && $param['value'] = ($data['amount'] * 100);
            } else {
                if (in_array($data['agent'], array('dxcymxAND004', 'dxcdjbAND059', 'djryHAND002'))) {
                    $reportType == 2 && $param['value'] = ($data['amount'] * 100) * 3;
                } else {
                    $reportType == 2 && $param['value'] = ($data['amount'] * 100) * 0.3;
                }

            }

            $url = "https://t.gdt.qq.com/conv/app/{$_config['config_appid']}/conv";
            $res = json_decode(curl_post($url, http_build_query($param)), true);
            //记录上报过的广告数据
            $insert               = $data;
            $insert['ret']        = json_encode($res);
            $insert['department'] = $data['departmentId'];
            switch ($value) {
                case 'MOBILEAPP_ACTIVITE':
                    $rtype = 1;
                    break;
                case 'MOBILEAPP_COST':
                    $rtype = 2;
                    break;
                case 'MOBILEAPP_REGISTER':
                    $rtype = 3;
                    break;
                default:
                    $rtype = '';
                    break;
            }
            $this->advAndReport($insert, $rtype);
            if ($res['ret'] == 0) {
                log_save("[result]返回结果：" . json_encode($res) . "    [data]请求参数：" . json_encode($param) . "   " . "传入数据：" . json_encode($data) . "  [msg]报送日志", "info", "", "advter_log_gdt1" . '_' . date("Y-m-d") . '.log', 'and_advter_log');
            } else {
                log_save("[result]返回结果：" . json_encode($res) . "    [data]请求参数：" . $url . "   " . "传入数据：" . json_encode($data) . "  [msg]报送失败日志", "info", "", "advter_log_gdt1" . '_' . date("Y-m-d") . '.log', 'and_debug_advter_log');
            }
        }

    }

    /**
     * 百度信息流上报
     * @DateTime  2017-12-05T09:47:06+0800
     * @param     [array]         $data       [需要上报的数据]
     * @return    [bool]                      [上报是否成功]
     */
    private function baiduReport($data, $reportType = 1)
    {
        //上报地址格式--http://als.baidu.com/cb/actionCb?a_type=activate&a_value=0&ext_info=T6H2n7u&sign=cab06bc0cebd20482b5892cb72864a62
        $_config = M('and_agent_config')->where(array('agent' => $data['agent']))->find();
        if (!$_config['config_sign_key']) {
            return false;
        }

        $callback_url = $data['callBackUrl'];
        $akey         = $_config['config_sign_key'];

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
        //记录上报过的广告数据
        $insert               = $data;
        $insert['ret']        = json_encode($res);
        $insert['department'] = $data['departmentId'];
        $this->advAndReport($insert, $reportType);

        $this->mongo->update('advand', array('status' => 0), array('_id' => $data['_id']));
        if ($res['error_code'] === 0) {
            log_save("[result]返回结果：" . json_encode($res) . "    [data]请求参数：" . $url . "   " . "传入数据：" . json_encode($data) . "  [msg]报送日志", "info", "", "advter_log_baidu" . '_' . date("Y-m-d") . '.log', 'and_advter_log');
            return true;
        } else {
            log_save("[result]返回结果：" . json_encode($res) . "    [data]请求参数：" . $url . "   " . "传入数据：" . json_encode($data) . "  [msg]报送失败日志", "info", "", "advter_log_baidu" . '_' . date("Y-m-d") . '.log', 'and_debug_advter_log');

            return false;
        }
    }

    /**
     * 多盟上报
     * @DateTime  2017-12-05T09:47:06+0800
     * @param     [array]         $data       [需要上报的数据]
     * @return    [bool]                      [上报是否成功]
     */
    private function dmReport($data, $reportType = 1)
    {
        //上报地址格式--http://e.domob.cn/track/android/api/callback?appkey=531266294&mac=1C:AB:A7:D6:E7:83&imei=864103021667081&aid=3ec0da198ba79d80&aaid=38400000-8cf0-11bd-b23e-10b96e40000d&acttime=1391502359&acttype=2&sign=32b84c2302a1d7ca933f74f73f519814&actip=115.183.152.45&appversion=2.0.1&userid=4124bc0a9335c27f086f24ba207a4912&clktime=1391501359&clkip=119.255.14.220
        $_config = M('and_agent_config')->where(array('agent' => $data['agent']))->find();
        if (!$_config['config_sign_key']) {
            return false;
        }

        $appkey   = $_config['config_appid'];
        $sign_key = $_config['config_sign_key'];

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
            'appkey'       => $appkey,
            'acttype'      => $acttype,
            'imei'         => $data['imei'],
            'imeimd5'      => $data['imeimd5'],
            'acttime'      => time(),
            'clktime'      => $data['clickTime'],
            'returnFormat' => 1,
        );

        if ($reportType == 2) {
            $request_param['price'] = $data['amount'] * 100;
        }
        $attachment = http_build_query($request_param);

        $sign = $this->getDomobSign($appkey, '', $data['imei'], '', '', $sign_key);

        $url = 'http://e.domob.cn/track/android/api/callback?sign=' . $sign . '&' . $attachment;

        $res = curl_get($url, 3);
        //记录上报过的广告数据
        $insert               = $data;
        $insert['ret']        = $res;
        $insert['department'] = $data['departmentId'];
        $this->advAndReport($insert, $reportType);

        $this->mongo->update('advand', array('status' => 0), array('_id' => $data['_id']));
        if ($res == 'ok') {
            log_save("[result]返回结果：" . $res . "    [data]请求参数：" . $url . "   " . "传入数据：" . json_encode($data) . "  [msg]报送日志", "info", "", "advter_log_dm" . '_' . date("Y-m-d") . '.log', 'and_advter_log');
            return true;
        } else {
            log_save("[result]返回结果：" . $res . "    [data]请求参数：" . $url . "   " . "传入数据：" . json_encode($data) . "  [msg]报送失败日志", "info", "", "advter_log_dm" . '_' . date("Y-m-d") . '.log', 'and_debug_advter_log');
            return false;
        }
    }

    /**
     * 百度移动DSP上报
     * @DateTime  2017-12-05T09:47:06+0800
     * @param     [array]         $data       [需要上报的数据]
     * @return    [bool]                      [上报是否成功]
     */
    private function bdydReport($data, $reportType = 1)
    {
        //上报地址格式--http://mobads-logs.baidu.com/dz.zb?type=12&mac=02:EA:FF:21:AA:20&idfa=111F7987-6E2F-473A-BFED-E4C52CB5A6DC&md5=0&t=20150606123015&traceid= 550e0d5bb0d8ebb3fd6d48,42aba3cafce300&act=0&pk=com.baidu.mobads&crid=1000&appid=a1cd7e67
        if (!$data['agent'] || !$data['muid']) {
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
            'imei'    => $data['imei'],
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
        //记录上报过的广告数据
        $insert               = $data;
        $insert['ret']        = $res;
        $insert['department'] = $data['departmentId'];
        $this->advAndReport($insert, $reportType);

        $this->mongo->update('advand', array('status' => 0), array('_id' => $data['_id']));
        if ($res['error'] == 0) {
            log_save("[result]返回结果：" . json_encode($res) . "    [data]请求参数：" . $url . "   " . "传入数据：" . json_encode($data) . "  [msg]报送日志", "info", "", "advter_log_bdyd" . '_' . date("Y-m-d") . '.log', 'and_advter_log');
            return true;
        } else {
            log_save("[result]返回结果：" . json_encode($res) . "    [data]请求参数：" . $url . "   " . "传入数据：" . json_encode($data) . "  [msg]报送失败日志", "info", "", "advter_log_bdyd" . '_' . date("Y-m-d") . '.log', 'and_debug_advter_log');
            return false;
        }
    }

    /**
     * unityAds上报
     * @param     [array]         $data       [需要上报的数据]
     * @return    [bool]                      [上报是否成功]
     */
    private function unityAdsReport($data, $reportType = 1)
    {
        if (!$data['agent'] || !$data['muid']) {
            return false;
        }
        $gamerId = $data['gid'];

        $url = 'https://postback.unityads.unity3d.com/games/' . $gamerId . '/install?advertisingTrackingId=' . strtolower($data['ifa']);

        $res = json_decode(curl_get($url, 3), true);
        //记录上报过的广告数据
        $insert               = $data;
        $insert['ret']        = $res;
        $insert['department'] = $data['departmentId'];
        $this->advAndReport($insert, $reportType);

        $this->mongo->update('advand', array('status' => 0), array('_id' => $data['_id']));
        if ($res['install'] == true) {
            log_save("[result]返回结果：" . json_encode($res) . "    [data]请求参数：" . $url . "   " . "传入数据：" . json_encode($data) . "  [msg]报送日志", "info", "", "advter_log_unityAds" . '_' . date("Y-m-d") . '.log', 'and_advter_log');
            return true;
        } else {
            log_save("[result]返回结果：" . json_encode($res) . "    [data]请求参数：" . $url . "   " . "传入数据：" . json_encode($data) . "  [msg]报送失败日志", "info", "", "advter_log_unityAds" . '_' . date("Y-m-d") . '.log', 'and_debug_advter_log');
            return false;
        }
    }

    /** 生成domob激活回调的签名。 */
    private function getDomobSign($appkey, $mac, $imei, $aid, $aaid, $sign_key)
    {
        $s = sprintf("%s,%s,%s,%s,%s,%s", $appkey, $mac, $imei, $aid, $aaid, $sign_key);
        return md5($s);
    }

    /**
     * 今日头条
     * @param  [type]  $data       [description]
     * @param  integer $reportType [description]
     * @return [type]              [description]
     */
    private function jrttReport($data, $reportType = 1)
    {
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

        $specialAgents = array('mxcsHAND250', 'mxcsHAND251', 'mxcsHAND252', 'mxcsHAND253', 'mxcsHAND254');
        if (in_array($data['agent'], $specialAgents) && $reportType != 2) {
            return true;
        } elseif (in_array($data['agent'], $specialAgents) && $reportType == 2) {
            $types = ['0', '1', '2'];
            foreach ($types as $value) {
                $url = $data['callBackUrl'] . '&event_type=' . $value;
                $res = json_decode(curl_get($url), true);
                //记录上报过的广告数据
                $insert               = $data;
                $insert['ret']        = json_encode($res);
                $insert['department'] = $data['departmentId'];
                switch ($value) {
                    case '0':
                        $rtype = 1;
                        break;
                    case '1':
                        $rtype = 3;
                        break;
                    case '2':
                        $rtype = 2;
                        break;
                    default:
                        $rtype = '';
                        break;
                }
                $this->advAndReport($insert, $rtype);

                if ($res['error_code'] === 0) {
                    log_save("[result]返回结果：" . json_encode($res) . "    [data]请求参数：" . $url . "   " . "传入数据：" . json_encode($data) . "  [msg]报送日志", "info", "", "advter_log_jrtt" . '_' . date("Y-m-d") . '.log', 'and_advter_log');
                } else {
                    log_save("[result]返回结果：" . json_encode($res) . "    [data]请求参数：" . $url . "   " . "传入数据：" . json_encode($data) . "  [msg]报送失败日志", "info", "", "advter_log_jrtt" . '_' . date("Y-m-d") . '.log', 'and_debug_advter_log');
                }
            }
        } else {
            $url = $data['callBackUrl'] . '&event_type=' . $event_type;
            $res = json_decode(curl_get($url), true);
            //记录上报过的广告数据
            $insert               = $data;
            $insert['ret']        = json_encode($res);
            $insert['department'] = $data['departmentId'];
            $this->advAndReport($insert, $reportType);

            if ($res['error_code'] === 0) {
                log_save("[result]返回结果：" . json_encode($res) . "    [data]请求参数：" . $url . "   " . "传入数据：" . json_encode($data) . "  [msg]报送日志", "info", "", "advter_log_jrtt" . '_' . date("Y-m-d") . '.log', 'and_advter_log');
            } else {
                log_save("[result]返回结果：" . json_encode($res) . "    [data]请求参数：" . $url . "   " . "传入数据：" . json_encode($data) . "  [msg]报送失败日志", "info", "", "advter_log_jrtt" . '_' . date("Y-m-d") . '.log', 'and_debug_advter_log');
            }
        }
        $this->mongo->update('advand', array('status' => 0), array('_id' => $data['_id']));
        return true;
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
     * 安卓广告报送数据插入
     *
     * @param array $data                  [插入的数据]
     * @param int $reportType            [上报的类型] 1:激活 2:充值报送 3:注册报送
     * @return boolean
     */
    private function advAndReport($data, $reportType = 1)
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
            'muid'        => $data['muid'] ? $data['muid'] : '',
            'imei'        => $data['imei'],
            'imei2'       => $data['imei2'],
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
        $res = $mongo->insert("advAndReport", $insert);
        $mongo->close();
        if ($res) {
            return true;
        }
        return false;
    }

    /**
     * 融合SDK，接游戏热云
     * @param $data
     * @param int $reportType 报送类型，1：首次打开，2:每次打开，3:初次进服，4：每次进服，5：每次充值
     * @return bool
     */
    public function gameReyunReport($data, $reportType = 1)
    {
        //是否需要上报
        if (!$this->fusionReyunConfig[$data["agent"]] && !$this->fusionOldReyunConfig[$data["agent"]]) {
            return false;
        }

        if ($this->fusionReyunConfig[$data["agent"]]) {
            //新版热云
            //参数配置
            $config = $this->fusionReyunConfig[$data["agent"]];
            if ($reportType == 1 || $reportType == 2) {
                $param = array(
                    "appid"   => $config["appid"],
                    "context" => array(
                        "deviceid" => $data["imei"] ? $data["imei"] : "unknown",
                    ),
                );
            } elseif ($reportType == 3) {
                $param = array(
                    "appid"   => $config["appid"],
                    "who"     => $data["channelCode"] ? $data["channelCode"] : $data["userCode"],
                    "context" => array(
                        "deviceid" => $data["imei"] ? $data["imei"] : "unknown",
                        "serverid" => $data["serverId"] ? $data["serverId"] : "unknown",
                    ),
                );
            } elseif ($reportType == 4) {
                $param = array(
                    "appid"   => $config["appid"],
                    "who"     => $data["channelCode"] ? $data["channelCode"] : $data["userCode"],
                    "context" => array(
                        "deviceid" => $data["imei"] ? $data["imei"] : "unknown",
                        "serverid" => $data["serverId"] ? $data["serverId"] : "unknown",
                        "level"    => $data["level"] ? $data["serverId"] : "0",
                    ),
                );
            } elseif ($reportType == 5) {
                $param = array(
                    "appid"   => $config["appid"],
                    "who"     => $data["channelCode"],
                    "context" => array(
                        "deviceid"          => $data["imei"] ? $data["imei"] : "unknown",
                        "transactionid"     => $data["tranId"] ? $data["tranId"] : "unknown",
                        "paymenttype"       => $data["channelName"] ? $data["channelName"] : "unknown",
                        "currencytype"      => "CNY",
                        "currencyamount"    => $data["amount"] ? $data["amount"] : "unknown",
                        "virtualcoinamount" => $data["amount"] ? $data["amount"] : "unknown",
                        "iapname"           => $data["subject"] ? $data["subject"] : "unknown",
                        "iapamount"         => 1,
                        "serverid"          => $data["serverId"] ? $data["serverId"] : "unknown",
                        "level"             => $data["level"] ? $data["serverId"] : "0",
                    ),
                );
            }

            //新版请求地址
            $url = $this->fusionReyunUrl . $this->fusionReyunUrltype[$reportType];
        } elseif ($this->fusionOldReyunConfig[$data["agent"]]) {
            //旧版热云
            //参数配置
            $config = $this->fusionOldReyunConfig[$data["agent"]];
            if ($reportType == 1) {
                $param = array(
                    "appid"   => $config["appid"],
                    "context" => array(
                        "deviceid"  => $data["imei"] ? $data["imei"] : "unknown",
                        "channelid" => "_default_",
                        "androidid" => $data["systemId"] ? $data["systemId"] : "",
                        "imei"      => $data["imei"] ? $data["imei"] : "unknown",
                        "ip"        => $data["ip"],
                    ),
                );
            } elseif ($reportType == 2 || $reportType == 3 || $reportType == 4) {
                $param = array(
                    "appid"   => $config["appid"],
                    "who"     => $data["channelCode"] ? $data["channelCode"] : $data["userCode"],
                    "context" => array(
                        "deviceid"  => $data["imei"] ? $data["imei"] : "unknown",
                        "channelid" => "_default_",
                        "imei"      => $data["imei"] ? $data["imei"] : "unknown",
                    ),
                );
            } elseif ($reportType == 5) {
                $param = array(
                    "appid"   => $config["appid"],
                    "who"     => $data["channelCode"] ? $data["channelCode"] : $data["userCode"],
                    "context" => array(
                        "deviceid"       => $data["imei"] ? $data["imei"] : "unknown",
                        "transactionid"  => $data["tranId"] ? $data["tranId"] : "unknown",
                        "paymenttype"    => $data["channelName"] ? $data["channelName"] : "unknown",
                        "currencytype"   => "CNY",
                        "currencyamount" => $data["amount"] ? $data["amount"] : "unknown",
                        "imei"           => $data["imei"] ? $data["imei"] : "unknown",
                    ),
                );
            }

            //旧版请求地址
            $url = $this->fusionReyunUrl . $this->fusionOldReyunUrltype[$reportType];
        }

        //上报
        $Param = json_encode($param);
        $ch    = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-type: application/json")); //定义请求类型
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $Param);
        $num = 0;
        while ($num < 3) {
            ++$num;
            $returnTransfer = curl_exec($ch);
            //写日志
            log_save("[data]" . $Param . "    [res]" . json_encode($returnTransfer) . "    [url]" . $url . "    [msg]报送日志", "info", "", "fusion_reyun_game_" . $data["agent"] . "_" . date("Y-m-d") . ".log", "and_advter_log");
            if ($returnTransfer != false) {
                curl_close($ch);
                break;
            }
        }
        return true;
    }
}
