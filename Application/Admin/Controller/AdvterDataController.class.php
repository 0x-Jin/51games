<?php
/**
 * Created by Sublime.
 * User: XSM
 * Date: 2017/6/26
 * Time: 15:22
 *
 * 运营管理控制器
 */

namespace Admin\Controller;

use Admin\Controller\BackendController;

class AdvterDataController extends BackendController
{

    /**
     * 落地页数据统计
     */
    public function fallData()
    {
        if (IS_POST) {

            $data         = I();
            $agent_info   = $_REQUEST["agent"];
            $agent_p_info = $_REQUEST["agent_p"];
            $start        = $data["start"] ? $data["start"] : 0;
            $pageSize     = $data["limit"] ? $data["limit"] : 30;

            //处理搜索条件
            $agentArr = dealList($agent_info, $agent_p_info);
            $advArr   = dealAllList($data['adv_id']);

            if ($agentArr['agent']) {
                $map['agent'] = array('in', $agentArr['agent']);
            } else {
                $agent_infos = $map_arr = array();

                if (!empty($agentArr['pAgent'])) {
                    $map_arr['_string'] = "id IN ('" . implode("','", $agentArr['pAgent']) . "') OR pid IN ('" . implode("','", $agentArr['pAgent']) . "')";
                }

                if ($data['advteruser_id']) {
                    $map_arr['advteruser_id'] = $data['advteruser_id'];
                }

                if ($data['creater']) {
                    $map_arr['creater'] = $data['creater'];
                }

                if ($data['gameType']) {
                    $map_arr['gameType'] = $data['gameType'];
                }

                if ($data['department']) {
                    $map_arr['departmentId'] = $data['department'];
                }

                if ($data['game_id']) {
                    $map_arr['game_id'] = $data['game_id'];
                }
                if ($map_arr) {
                    $agent_infos = array_keys(getDataList('agent', 'agent', C('DB_PREFIX_API'), $map_arr));
                }

                $arr = $this->agentArr;
                if ($agent_infos) {
                    $arr = array_intersect($arr, $agent_infos);
                } elseif ($map_arr && !$agent_infos) {
                    exit(json_encode(array('rows' => array(), 'results' => 0)));
                }

                sort($arr);
                if (count($arr) < 1) {
                    $arr[] = '-1';
                }

                $map['agent'] = array('in', $arr);

            }

            if ($advArr['info']) {
                $map['advid'] = array('IN', $advArr['info']);
            }

            if ($data['startDate'] && $data['endDate']) {
                $map['dayTime'] = array(array('egt', $data['startDate']), array('lt', date('Y-m-d', strtotime($data['endDate'] . '+1 day'))), 'and');
            }
            $res = D('Admin')->getBuiList("sp_fall_day", $map, $start, $pageSize, C('DB_PREFIX'), 'openNum');

            $results    = $res['count'];
            $game_list  = getDataList('game', 'id', C('DB_PREFIX_API'));
            $agent_list = getDataList('agent', 'agent', C('DB_PREFIX_API'));
            $advterlist = getDataList('advter_list', 'id', C('DB_PREFIX'), array('agent' => array('IN', $this->agentArr)));

            $advteruser_list = getDataList('advteruser', 'id', C('DB_PREFIX'));
            foreach ($res['list'] as $key => $val) {
                $res['list'][$key]['openNum']        = intval($val['openNum']);
                $res['list'][$key]['advname']        = '<a href="javascript:;" onclick=semInfo(' . $val['advid'] . ',"' . $val['dayTime'] . '")>' . $advterlist[$val['advid']]['adv_name'] . '</a>';
                $res['list'][$key]['disOpenNum']     = intval($val['disOpenNum']);
                $res['list'][$key]['downloadNum']    = intval($val['downloadNum']);
                $res['list'][$key]['disDownloadNum'] = intval($val['disDownloadNum']);
                $res['list'][$key]['gameName']       = $game_list[$agent_list[$val['agent']]['game_id']]['gameName'];
                $res['list'][$key]['advteruser']     = $advteruser_list[$agent_list[$val['agent']]['advteruser_id']]['company_name'];
                $res['list'][$key]['rate']           = numFormat($val['disDownloadNum'] / $val['disOpenNum'], true);
                $rows[]                              = $res['list'][$key];
            }

            $arr = array('rows' => empty($rows) ? array() : $rows, 'results' => $results);
            exit(json_encode($arr));
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
     * 落地页sem信息
     */
    public function semInfo()
    {
        $data = I();
        if (empty($data['advid']) || empty($data['dayTime'])) {
            $this->ajaxReturn(array('status' => 0, '_html' => '该落地页对应的SEM信息'));
        }

        $map['advid']   = $data['advid'];
        $map['dayTime'] = $data['dayTime'];
        $list           = D('Admin')->getSemInfo($map);
        if ($list) {
            $advterlist = getDataList('advter_list', 'id', C('DB_PREFIX'), array('id' => $map['advid']));
            $this->assign('advname', $advterlist[$map['advid']]['adv_name']);
            $this->assign('list', $list);
            if (IS_AJAX) {
                $respose = $this->fetch();
                $this->ajaxReturn(array('status' => 1, '_html' => $respose));
            } else {
                $this->display();
            }
        } else {
            $this->ajaxReturn(array('status' => 0, '_html' => '该落地页对应的SEM信息'));
        }
    }

    /**
     * 导出落地页sem信息
     */
    public function exportSemInfo()
    {
        $data = I();
        if ((empty($data['advid']) || empty($data['dayTime'])) && !$data['export']) {
            $this->error('该落地页对应的SEM信息');
        }

        $map2 = [];
        $advid;

        if ($data['export'] == 1) {
            $agent_info   = $_REQUEST["agent"];
            $agent_p_info = $_REQUEST["agent_p"];

            //处理搜索条件
            $agentArr = dealList($agent_info, $agent_p_info);
            $advArr   = dealAllList($data['adv_id']);

            if ($agentArr['agent']) {
                $map['agent'] = array('in', $agentArr['agent']);
            } else {
                $agent_infos = $map_arr = array();

                if (!empty($agentArr['pAgent'])) {
                    $map_arr['_string'] = "id IN ('" . implode("','", $agentArr['pAgent']) . "') OR pid IN ('" . implode("','", $agentArr['pAgent']) . "')";
                }

                if ($data['advteruser_id']) {
                    $map_arr['advteruser_id'] = $data['advteruser_id'];
                }

                if ($data['creater']) {
                    $map_arr['creater'] = $data['creater'];
                }

                if ($data['gameType']) {
                    $map_arr['gameType'] = $data['gameType'];
                }

                if ($data['department']) {
                    $map_arr['departmentId'] = $data['department'];
                }

                if ($data['game_id']) {
                    $map_arr['game_id'] = $data['game_id'];
                }
                if ($map_arr) {
                    $agent_infos = array_keys(getDataList('agent', 'agent', C('DB_PREFIX_API'), $map_arr));
                }

                $arr = $this->agentArr;
                if ($agent_infos) {
                    $arr = array_intersect($arr, $agent_infos);
                }
                sort($arr);
                if (count($arr) < 1) {
                    exit(json_encode(array('rows' => array(), 'results' => 0)));
                }

                $map['agent'] = array('in', $arr);
            }

            if ($advArr['info']) {
                $map['advid'] = array('IN', $advArr['info']);
            }

            if ($data['startDate'] && $data['endDate']) {
            $map2['dayTime'] = $map['dayTime'] = array(array('egt', $data['startDate']), array('lt', date('Y-m-d', strtotime($data['endDate'] . '+1 day'))), 'and');
            }
            $list = D('Admin')->getBuiList("sp_fall_day", $map, 0, 999999, C('DB_PREFIX'));
            //查出落地页id
            $advid = array_column($list['list'],'advid');
            $map2['advid'] = ['IN',$advid];
        } else {
            $advid = $data['advid'];
            $map2['advid']   = $advid;
            $map2['dayTime'] = $data['dayTime'];
        }
        $list           = D('Admin')->getSemInfo($map2);
        if ($list) {
            $advterlist = getDataList('advter_list', 'id', C('DB_PREFIX'), array('id' => array('IN',$advid)));
            foreach ($list as $key => $value) {
                $list[$key]['advname'] = $advterlist[$value['advid']]['adv_name'];
            }

            $col = array(
                'dayTime'        => '日期',
                'advname'        => '落地页名称',
                'cmtype'         => '标记',
                'openNum'        => '打开数',
                'disOpenNum'     => '唯一打开数',
                'downloadNum'    => '下载数',
                'disDownloadNum' => '唯一下载数',
            );
            array_unshift($list, $col);
            export_to_csv($list, 'SEM信息_' . date('Y-m-d'), $col);
            exit();
        } else {
            $this->error('该落地页对应的SEM信息');
        }
    }

    /**
     * IOS投放数据概况统计
     */
    public function advDataIos()
    {
        if (IS_POST) {
            $data         = I();
            $agent_info   = $_REQUEST['advter_id'];
            $agent_p_info = $_REQUEST['events_groupId'];

            //处理搜索条件
            $start    = $data['start'] ? $data['start'] : 0;
            $pageSize = $data['limit'] ? $data['limit'] : 200;
            $where    = '1';

            $department = session('admin.partment');

            $agentArr = dealAllList($agent_info, $agent_p_info);

            if ($agentArr['info']) {
                $where .= ' and a.advterId in("' . implode('","', $agentArr['info']) . '")';
                $map2['advter_id'] = array('in', $agentArr['info']);
                $map['advter_id']  = array('in', $agentArr['info']);
            } else {
                $agent_infos = $map_arr = array();

                if (!empty($agentArr['pinfo'])) {
                    $map_arr['_string'] = "events_groupId IN ('" . implode("','", $agentArr['pinfo']) . "')";
                }

                $advteruser_id = dealAllList($data['advteruser_id']);

                if ($advteruser_id['info']) {
                    $map_arr['advteruser_id'] = array('IN', $advteruser_id['info']);
                }

                if ($data['creater']) {
                    $map_arr['creater'] = $data['creater'];
                }

                if ($data['department']) {
                    $map_arr['department'] = $data['department'];
                } else {
                    $department != '0' && $map_arr['department'] = $department;
                }

                $game_info = dealAllList($data['game_id']);
                if ($game_info['info']) {
                    $map_arr['game_id'] = array('IN', $game_info['info']);
                }

                $agent_info = dealAllList($data['agent_p']);
                if ($agent_info['info']) {
                    $map_arr['agent'] = array('IN', $agent_info['info']);
                }

                $agent_infos = array_keys(getDataList('events', 'id', C('DB_PREFIX'), $map_arr));

                if ($agent_infos) {
                    $arr = $agent_infos;
                } elseif ($map_arr && !$agent_infos) {
                    $arr = array('-1');
                }

                $where .= ' and a.advterId in("' . implode('","', $arr) . '")';
                $map2['advter_id'] = array('in', $arr);
                $map['advter_id']  = array('IN', $arr);
            }

            //是否查询一个月的数据
            $dayOne  = date("Y-m-01", strtotime($data["startDate"]));
            $isMonth = 0;
            if ($dayOne && $dayOne == $data["startDate"] && date("Y-m-d", strtotime($dayOne . " +1 month -1 day")) == $data["endDate"]) {
                $isMonth = 1;
            }

            if ($data['startDate'] && $data['startDate']) {
                $where .= ' and a.dayTime>="' . $data['startDate'] . '" and a.dayTime<"' . date('Y-m-d', strtotime($data['endDate'] . '+1 day')) . '"';
                if ($isMonth == 1) {
                    $map2['a.dayTime'] = array(array('egt', $data['startDate']), array('lt', date('Y-m-d', strtotime($data['endDate'] . '+1 day'))), 'and');
                } else {
                    $map2['dayTime'] = array(array('egt', $data['startDate']), array('lt', date('Y-m-d', strtotime($data['endDate'] . '+1 day'))), 'and');
                }
                $map['paymentTime'] = array(array('egt', strtotime($data['startDate'])), array('lt', strtotime(date('Y-m-d', strtotime($data['endDate'] . '+1 day')))), 'and');
            }

            $map['type'] = array('eq', 2);

            //去除advter_id为15的测试数据
            $where .= ' and a.advterId <> 15';
            $map2['_string'] = ' advter_id <> 15';
            $map['_string']  = ' advter_id <> 15';

            $res = D('Admin')->getAdvDataIos($map, $start, $pageSize, $where, $map2, $isMonth);
            return $res;
            $gameName        = getDataList('agent', 'agent', C('DB_PREFIX_API'), array('agentType' => 1));
            $agent_list      = getDataList('agent', 'agent', C('DB_PREFIX_API'));
            $event_list      = getDataList('events', 'id');
            $advteruser_list = getDataList('advteruser', 'id', C('DB_PREFIX'));
            $total           = $newRes           = array();

            foreach ($res['list'] as $key => $val) {
                $newRes[$key] = $val;
                //游戏的汇总
                $res['list'][$key]['allPay']         = floatval($val['allPay']);
                $res['list'][$key]['cost']           = floatval($val['cost']);
                $res['list'][$key]['gameName']       = $gameName[$val['agent']]['agentName'] . '[' . $val['agent'] . ']';
                $res['list'][$key]['eventName']      = '-';
                $res['list'][$key]['advteruserName'] = '-';
                $res['list'][$key]['regCost']        = round(floatval($val['cost'] / $val['newUser']), 2); //注册单价

                $res['list'][$key]['newPayRate']   = numFormat(($val['newPayUser'] / $val['newUser']), true); //新增付费率
                $res['list'][$key]['actPayRate']   = numFormat(($val['allPayUser'] / $val['DAU']), true); //活跃付费率
                $res['list'][$key]['dayRate']      = numFormat(($val['newPay'] / $val['cost']), true); //1日回本率
                $res['list'][$key]['allPayRate']   = numFormat(($val['allPayNow'] / $val['cost']), true); //至今回本率
                $res['list'][$key]['totalPayRate'] = numFormat(($val['totalPay'] / $val['cost']), true); //累计回本率
                $res['list'][$key]['regRate']      = numFormat(($val['disUdid'] / $val['newDevice']), true); //注册转化率
                $res['list'][$key]['day1']         = numFormat(($val['day1'] / $val['newUser']), true); //次留

                $res['list'][$key]['ARPU']  = round(floatval($val['allPay'] / $val['DAU']), 2); //活跃ARPU
                $res['list'][$key]['ARPPU'] = round(floatval($val['allPay'] / $val['allPayUser']), 2); //活跃ARPPU

                $res['list'][$key]['newARPU']  = round(floatval($val['newPay'] / $val['newUser']), 2); //新增ARPU
                $res['list'][$key]['newARPPU'] = round(floatval($val['newPay'] / $val['newPayUser']), 2); //新增ARPPU

                //子级汇总
                $childrenSum            = $this->summarys($res['list'][$key]['children']);
                $childrenSum['dayTime'] = '子级汇总';
                foreach ($res['list'][$key]['children'] as $k => $v) {
                    $res['list'][$key]['children'][$k]['allPay']         = floatval($v['allPay']);
                    $res['list'][$key]['children'][$k]['cost']           = floatval($v['cost']);
                    $res['list'][$key]['children'][$k]['gameName']       = $gameName[$val['agent']]['agentName'] . '[' . $val['agent'] . ']';
                    $res['list'][$key]['children'][$k]['agent']          = $event_list[$v['advterId']]['events_name']; //推广活动名称
                    $res['list'][$key]['children'][$k]['advteruserName'] = $advteruser_list[$v['advterUserId']]['company_name'];
                    $res['list'][$key]['children'][$k]['regCost']        = round(floatval($v['cost'] / $v['newUser']), 2); //注册单价

                    $res['list'][$key]['children'][$k]['newPayRate'] = numFormat(($v['newPayUser'] / $v['newUser']), true); //新增付费率
                    $res['list'][$key]['children'][$k]['actPayRate'] = numFormat(($v['allPayUser'] / $v['DAU']), true); //活跃付费率

                    $res['list'][$key]['children'][$k]['dayRate']      = numFormat(($v['newPay'] / $v['cost']), true); //1日回本率
                    $res['list'][$key]['children'][$k]['allPayRate']   = numFormat(($v['allPayNow'] / $v['cost']), true); //至今回本率
                    $res['list'][$key]['children'][$k]['totalPayRate'] = numFormat(($v['totalPay'] / $v['cost']), true); //累计回本率
                    $res['list'][$key]['children'][$k]['regRate']      = numFormat(($v['disUdid'] / $v['newDevice']), true); //注册转化率

                    $res['list'][$key]['children'][$k]['day1'] = numFormat(($v['day1'] / $v['newUser']), true); //次留

                    $res['list'][$key]['children'][$k]['ARPU']  = round(floatval($v['allPay'] / $v['DAU']), 2); //活跃ARPU
                    $res['list'][$key]['children'][$k]['ARPPU'] = round(floatval($v['allPay'] / $v['allPayUser']), 2); //活跃ARPPU

                    $res['list'][$key]['children'][$k]['newARPU']  = round(floatval($v['newPay'] / $v['newUser']), 2); //新增ARPU
                    $res['list'][$key]['children'][$k]['newARPPU'] = round(floatval($v['newPay'] / $v['newPayUser']), 2); //新增ARPPU
                }

                $res['list'][$key]['children'][] = $childrenSum;
                if ($data['export'] == 1) {
                    foreach ($res['list'][$key]['children'] as $ck => $cv) {
                        $rows[] = $cv;
                    }
                }

            }

            if ($data['export'] != 1) {
                $rows = $res['list'];
            }
            unset($res);

            if ($data['export'] == 1) {
                $rows[] = $parentSum;
                $col    = array('dayTime' => '注册日期', 'gameName' => '游戏名称', 'advteruserName' => '渠道商', 'eventName' => '推广活动', 'cost' => '成本', 'newDevice' => '新增设备数', 'disUdid' => '唯一注册数', 'newUser' => '新增账号数', 'soleUdids' => '历史设备注册数', 'regRate' => '注册转化率', 'regCost' => '注册单价', 'newPay' => '新增充值金额', 'newPayUser' => '新增充值人数', 'newPayRate' => '新增付费率', 'newARPU' => '新增ARPU', 'newARPPU' => '新增ARPPU', 'allPay' => '当天充值金额', 'allPayUser' => '充值人数', 'actPayRate' => '活跃付费率', 'ARPU' => '活跃ARPU', 'ARPPU' => '活跃ARPPU', 'day1' => '次留', 'oldUserLogin' => '老用户活跃数', 'DAU' => 'DAU', 'dayRate' => '1日回本率', 'allPayNow' => '区间充值金额', 'allPayRate' => '区间回本率', 'totalPay' => '至今充值金额', 'totalPayRate' => '至今回本率');

                array_unshift($rows, $col);

                export_to_csv($rows, 'IOS投放数据概况', $col);
                exit();
            }
            $arr = array('rows' => $rows ? $rows : array(), 'results' => $results);
            exit(json_encode($arr));
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
     * 投放数据概况统计
     */
    public function advData()
    {
        if (IS_POST) {

            $data         = I();
            $agent_info   = $_REQUEST['agent'];
            $agent_p_info = $_REQUEST['agent_p'];

            //处理搜索条件
            $agentArr = dealAllList($agent_info, $agent_p_info);

            $start    = $data['start'] ? $data['start'] : 0;
            $pageSize = $data['limit'] ? $data['limit'] : 200;
            $where    = '1';
            if ($agentArr['info']) {
                $data['agent'] = $agentArr['info'];
                $where .= ' and a.agent in("' . implode('","', $data['agent']) . '")';
                $map2['regAgent'] = array('in', $data['agent']);
                $map['agent']     = array('in', $data['agent']);
            } else {

                $agent_infos = $map_arr = array();

                if (!empty($agentArr['pinfo'])) {
                    //母包id
                    $pid                = array_keys(getDataList('agent', 'id', C('DB_PREFIX_API'), array('agent' => array('IN', $agentArr['pinfo']))));
                    $map_arr['_string'] = "id IN ('" . implode("','", $pid) . "') OR pid IN ('" . implode("','", $pid) . "')";
                }

                $advteruser_id = dealAllList($data['advteruser_id']);

                if ($advteruser_id['info']) {
                    $map_arr['advteruser_id'] = array('IN', $advteruser_id['info']);
                }

                if ($data['creater']) {
                    $map_arr['creater'] = $data['creater'];
                }

                if ($data['department']) {
                    $map_arr['departmentId'] = $data['department'];
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

                $where .= ' and a.agent in("' . implode('","', $arr) . '")';
                $map2['regAgent'] = array('in', $arr);
                $map['agent']     = array('in', $arr);

            }

            $game_id = dealAllList($data['game_id']);

            if ($game_id['info']) {
                $data['game_id'] = $game_id['info'];
                $where .= ' and a.game_id IN("' . implode('","', $data['game_id']) . '")';
                $map['game_id']  = array('IN', $data['game_id']);
                $map2['game_id'] = array('IN', $data['game_id']);
            }

            //是否查询一个月的数据
            $dayOne  = date("Y-m-01", strtotime($data["startDate"]));
            $isMonth = 0;
            if ($dayOne && $dayOne == $data["startDate"] && date("Y-m-d", strtotime($dayOne . " +1 month -1 day")) == $data["endDate"]) {
                $isMonth = 1;
            }

            if ($data['startDate'] && $data['startDate']) {
                $where .= ' and a.dayTime>="' . $data['startDate'] . '" and a.dayTime<"' . date('Y-m-d', strtotime($data['endDate'] . '+1 day')) . '"';
                if ($isMonth == 1) {
                    $map2['a.dayTime'] = array(array('egt', $data['startDate']), array('lt', date('Y-m-d', strtotime($data['endDate'] . '+1 day'))), 'and');
                } else {
                    $map2['dayTime'] = array(array('egt', $data['startDate']), array('lt', date('Y-m-d', strtotime($data['endDate'] . '+1 day'))), 'and');
                }
                $map['paymentTime'] = array(array('egt', strtotime($data['startDate'])), array('lt', strtotime(date('Y-m-d', strtotime($data['endDate'] . '+1 day')))), 'and');
            }

            $where .= ' and a.gameType = 1';
            // $isMonth == 1 ? $map2['a.gameType'] = array('eq',1) : $map2['b.gameType'] = array('eq',1);
            $map['type'] = array('eq', 1);

            if (!$data['system']) {
                $res1 = D('Admin')->getAdvData($map, $start, $pageSize, $where, $map2, $isMonth, $game_id['info']);
                $res2 = $this->advDataIos();
                if ($res1 && $res2) {
                    $res['list']  = array_merge($res1['list'], $res2['list']);
                    $res['count'] = array(
                        'allPayUser' => ($res1['count']['allPayUser'] + $res2['count']['allPayUser']),
                        'login'      => array(
                            'DAU'          => ($res1['count']['login']['DAU'] + $res2['count']['login']['DAU']),
                            'oldUserLogin' => ($res1['count']['login']['oldUserLogin'] + $res2['count']['login']['oldUserLogin']),
                        ),
                    );
                } elseif ($res1) {
                    $res = $res1;
                } elseif ($res2) {
                    $res = $res2;
                }

            } elseif ($data['system'] == 1) {
                $res = D('Admin')->getAdvData($map, $start, $pageSize, $where, $map2, $isMonth, $game_id['info']);
            } elseif ($data['system'] == 2) {
                $res = $this->advDataIos();
            }

            $allPayUser   = $res['count']['allPayUser'];
            $DAU          = $res['count']['login']['DAU'];
            $oldUserLogin = $res['count']['login']['oldUserLogin'];
            $game_list    = getDataList('game', 'id', C('DB_PREFIX_API'));
            $agent_list   = getDataList('agent', 'agent', C('DB_PREFIX_API'));
            $event_list   = getDataList('events', 'id');

            $advteruser_list = getDataList('advteruser', 'id', C('DB_PREFIX'));
            $total           = $newRes           = array();
            $sortArr         = array();
            foreach ($res['list'] as $key => $val) {
                //如果游戏汇总全是0则去除
                if (empty($val['allPayUser']) && empty($val['newDevice']) && empty($val['disUdid']) && empty($val['newUser']) && empty($val['oldUserLogin']) && empty($val['DAU']) && empty($val['day1']) && empty((float) $val['allPay']) && empty((float) $val['newPay']) && empty($val['newPayUser']) && empty((float) $val['totalPay']) && empty((float) $val['cost'])) {
                    unset($res['list'][$key]);
                    continue;
                } else {
                    foreach ($res['list'][$key]['children'] as $k => $v) {
                        if (empty($v['allPayUser']) && empty($v['newDevice']) && empty($v['disUdid']) && empty($v['newUser']) && empty($v['oldUserLogin']) && empty($v['DAU']) && empty($v['day1']) && empty((float) $v['allPay']) && empty((float) $v['newPay']) && empty($v['newPayUser']) && empty((float) $v['totalPay']) && empty((float) $v['cost'])) {
                            unset($res['list'][$key]['children'][$k]);
                            continue;
                        }
                    }
                    $sortArr = array_column($res['list'][$key]['children'], 'newUser');

                    array_multisort($sortArr, SORT_DESC, $res['list'][$key]['children']);
                    unset($sortArr);
                }

            }

            sort($res['list']);

            foreach ($res['list'] as $key => $val) {
                $newRes[$key]               = $val;
                $res['list'][$key]['agent'] = '-';
                //游戏的汇总
                $res['list'][$key]['allPay']         = floatval($val['allPay']);
                $res['list'][$key]['cost']           = floatval($val['cost']);
                $res['list'][$key]['gameName']       = $agent_list[$val['agent']]['gameType'] == 1 ? $game_list[$val['game_id']]['gameName'] : $agent_list[$val['agent']]['agentName'];
                $res['list'][$key]['advteruserName'] = '-';
                $res['list'][$key]['regCost']        = round(floatval($val['cost'] / $val['newUser']), 2); //注册单价

                $res['list'][$key]['newPayRate']   = numFormat(($val['newPayUser'] / $val['newUser']), true); //新增付费率
                $res['list'][$key]['actPayRate']   = numFormat(($val['allPayUser'] / $val['DAU']), true); //活跃付费率
                $res['list'][$key]['dayRate']      = numFormat(($val['newPay'] / $val['cost']), true); //1日回本率
                $res['list'][$key]['allPayRate']   = numFormat(($val['allPayNow'] / $val['cost']), true); //至今回本率
                $res['list'][$key]['totalPayRate'] = numFormat(($val['totalPay'] / $val['cost']), true); //累计回本率
                $res['list'][$key]['regRate']      = numFormat(($val['disUdid'] / $val['newDevice']), true);

                // $agent_list[$val['agent']]['gameType'] == 1 ? numFormat(($val['newUser']/$val['newDevice']),true) : numFormat(($val['disUdid']/$val['newDevice']),true); //注册转化率

                $res['list'][$key]['day1'] = numFormat(($val['day1'] / $val['newUser']), true); //次留

                $res['list'][$key]['ARPU']  = round(floatval($val['allPay'] / $val['DAU']), 2); //活跃ARPU
                $res['list'][$key]['ARPPU'] = round(floatval($val['allPay'] / $val['allPayUser']), 2); //活跃ARPPU

                $res['list'][$key]['newARPU']  = round(floatval($val['newPay'] / $val['newUser']), 2); //新增ARPU
                $res['list'][$key]['newARPPU'] = round(floatval($val['newPay'] / $val['newPayUser']), 2); //新增ARPPU

                //渠道的汇总

                //子级汇总
                $childrenSum            = $this->summarys($res['list'][$key]['children']);
                $childrenSum['dayTime'] = '子级汇总';

                foreach ($res['list'][$key]['children'] as $k => $v) {

                    $res['list'][$key]['children'][$k]['allPay']   = floatval($v['allPay']);
                    $res['list'][$key]['children'][$k]['cost']     = floatval($v['cost']);
                    $res['list'][$key]['children'][$k]['gameName'] = $agent_list[$v['agent']]['gameType'] == 1 ? $game_list[$v['game_id']]['gameName'] : $agent_list[$v['agent']]['agentName'];

                    $res['list'][$key]['children'][$k]['agent'] = $agent_list[$v['agent']]['gameType'] == 1 ? $v['agent'] : $event_list[$v['advterId']]['events_name']; //推广活动名称

                    $res['list'][$key]['children'][$k]['advteruserName'] = $advteruser_list[$v['advteruser_id']]['company_name'];
                    $res['list'][$key]['children'][$k]['regCost']        = round(floatval($v['cost'] / $v['newUser']), 2); //注册单价

                    $res['list'][$key]['children'][$k]['newPayRate'] = numFormat(($v['newPayUser'] / $v['newUser']), true); //新增付费率
                    $res['list'][$key]['children'][$k]['actPayRate'] = numFormat(($v['allPayUser'] / $v['DAU']), true); //活跃付费率

                    $res['list'][$key]['children'][$k]['dayRate']      = numFormat(($v['newPay'] / $v['cost']), true); //1日回本率
                    $res['list'][$key]['children'][$k]['allPayRate']   = numFormat(($v['allPayNow'] / $v['cost']), true); //至今回本率
                    $res['list'][$key]['children'][$k]['totalPayRate'] = numFormat(($v['totalPay'] / $v['cost']), true); //累计回本率
                    $res['list'][$key]['children'][$k]['regRate']      = numFormat(($v['disUdid'] / $v['newDevice']), true); //注册转化率

                    $res['list'][$key]['children'][$k]['day1'] = numFormat(($v['day1'] / $v['newUser']), true); //次留

                    $res['list'][$key]['children'][$k]['ARPU']  = round(floatval($v['allPay'] / $v['DAU']), 2); //活跃ARPU
                    $res['list'][$key]['children'][$k]['ARPPU'] = round(floatval($v['allPay'] / $v['allPayUser']), 2); //活跃ARPPU

                    $res['list'][$key]['children'][$k]['newARPU']  = round(floatval($v['newPay'] / $v['newUser']), 2); //新增ARPU
                    $res['list'][$key]['children'][$k]['newARPPU'] = round(floatval($v['newPay'] / $v['newPayUser']), 2); //新增ARPPU
                }

                $res['list'][$key]['children'][] = $childrenSum;
                if ($data['export'] == 1) {
                    foreach ($res['list'][$key]['children'] as $ck => $cv) {
                        $rows[] = $cv;
                    }
                }

            }
            //父级汇总
            if ($data['isCount'] == 1) {
                $parentSum = $this->summarys($newRes, 1, $allPayUser, $DAU, $oldUserLogin);

                //一个月的数据暂时先注释，后期删去
                if ($isMonth && $parentSum['DAU'] == 0 && $parentSum['oldUserLogin'] == 0) {
                    $parentSum['DAU']          = "-";
                    $parentSum['oldUserLogin'] = "-";
                    $parentSum['actPayRate']   = "-"; //活跃付费率
                    $parentSum['ARPU']         = "-"; //活跃ARPU
                }

                $parentSum['dayTime'] = '父级汇总';
                $res['list'][]        = $parentSum;
            }
            if ($data['export'] != 1) {
                $rows          = $res['list'];
                $displayColumn = array_map(function ($args) {return explode("_", $args)[0];}, $data['displayColumn']);
                $diff = array_diff(array_keys($rows[0]), $displayColumn);
                foreach ($rows as &$value) {
                    foreach ($value['children'] as &$val) {
                        foreach ($diff as $m) {
                            unset($val[$m]);
                        }
                    }
                    foreach ($diff as $t) {
                        if ($t == 'children') {
                            continue;
                        }

                        unset($value[$t]);
                    }
                }
            }
            unset($res);

            if ($data['export'] == 1) {
                $rows[] = $parentSum;
                $col    = array('dayTime' => '注册日期', 'gameName' => '游戏名称', 'agent' => '渠道号', 'advteruserName' => '渠道商', 'cost' => '成本', 'newDevice' => '新增设备数', 'disUdid' => '唯一注册数', 'newUser' => '新增账号数', 'regRate' => '注册转化率', 'regCost' => '注册单价', 'newPay' => '新增充值金额', 'newPayUser' => '新增充值人数', 'newPayRate' => '新增付费率', 'newARPU' => '新增ARPU', 'newARPPU' => '新增ARPPU', 'allPay' => '当天充值金额', 'allPayUser' => '充值人数', 'actPayRate' => '活跃付费率', 'ARPU' => '活跃ARPU', 'ARPPU' => '活跃ARPPU', 'day1' => '次留', 'oldUserLogin' => '老用户活跃数', 'DAU' => 'DAU', 'dayRate' => '1日回本率', 'allPayNow' => '区间充值金额', 'allPayRate' => '区间回本率', 'totalPay' => '至今充值金额', 'totalPayRate' => '至今回本率');
                //各个包创建人的注册和充值情况
                $rows = $this->principalInfo($rows);
                $rows = $this->channelInfo($rows);

                array_unshift($rows, $col);
                /*$pagesummary['dayTime'] = '汇总';

                array_push($rows,$pagesummary);*/
                export_to_csv($rows, '投放数据概况', $col);
                exit();
            }
            $arr = array('rows' => $rows ? $rows : array(), 'results' => $results);
            exit(json_encode($arr));
        } else {
            if (IS_AJAX) {
                $response = $this->fetch();
                $this->ajaxReturn(array('status' => 1, '_html' => $response));
            } else {
                $this->display();
            }
        }
    }

    //各包负责人的充值和注册情况
    private function principalInfo($data)
    {
        $agentInfo = getDataList('agent', 'agent', C('DB_PREFIX_API'));
        $arr       = array();
        foreach ($data as $k => $v) {
            if (!isset($agentInfo[$v['agent']])) {
                continue;
            }

            if (session('admin.partment') && !in_array($agentInfo[$v['agent']]['departmentId'],array(session('admin.partment'),3))) {
                continue;
            }

            $arr[$agentInfo[$v['agent']]['creater']]['newPay'] += $v['newPay'];
            $arr[$agentInfo[$v['agent']]['creater']]['allPay'] += $v['allPay'];
            $arr[$agentInfo[$v['agent']]['creater']]['newUser'] += $v['newUser'];
            $arr[$agentInfo[$v['agent']]['creater']]['newDevice'] += $v['newDevice'];
        }
        $data[]['dayTime'] = "\r\r" . '各包号创建人充值和注册情况' . "\r";
        $data[]            = array('dayTime' => '姓名', 'gameName' => '新增充值金额', 'agent' => '当天充值金额', 'advteruserName' => '新增账号数', 'cost' => '新增设备数');

        foreach ($arr as $k => $v) {
            $data[] = array(
                'dayTime'        => $k,
                'gameName'       => $v['newPay'],
                'agent'          => $v['allPay'],
                'advteruserName' => $v['newUser'],
                'cost'           => $v['newDevice'],
            );
        }

        return $data;
    }

    //各渠道的充值和注册情况
    private function channelInfo($data)
    {
        $agentInfo      = getDataList('agent', 'agent', C('DB_PREFIX_API'));
        $advteruserInfo = getDataList('advteruser', 'id');
        $arr            = array();

        foreach ($data as $k => $v) {
            if (!isset($agentInfo[$v['agent']])) {
                continue;
            }

            if (session('admin.partment') && !in_array($agentInfo[$v['agent']]['departmentId'],array(session('admin.partment'),3))) {
                continue;
            }

            $arr[$agentInfo[$v['agent']]['advteruser_id']]['newPay'] += $v['newPay'];
            $arr[$agentInfo[$v['agent']]['advteruser_id']]['newPay'] += $v['newPay'];
            $arr[$agentInfo[$v['agent']]['advteruser_id']]['allPay'] += $v['allPay'];
            $arr[$agentInfo[$v['agent']]['advteruser_id']]['newUser'] += $v['newUser'];
            $arr[$agentInfo[$v['agent']]['advteruser_id']]['newDevice'] += $v['newDevice'];
            $arr[$agentInfo[$v['agent']]['advteruser_id']]['newPayUser'] += $v['newPayUser'];
            $arr[$agentInfo[$v['agent']]['advteruser_id']]['DAU'] += $v['DAU'];
        }
        $data[]['dayTime'] = "\r\r\r" . '各渠道的充值和注册情况' . "\r";
        $data[]            = array('dayTime' => '渠道', 'gameName' => '新增充值金额', 'agent' => '当天充值金额', 'advteruserName' => '新增账号数', 'cost' => '新增设备数', 'newDevice' => '新增付费率', 'newUser' => '新增ARPU');

        foreach ($arr as $k => $v) {
            $data[] = array(
                'dayTime'        => $advteruserInfo[$k]['company_name'],
                'gameName'       => $v['newPay'],
                'agent'          => $v['allPay'],
                'advteruserName' => $v['newUser'],
                'cost'           => $v['newDevice'],
                'newDevice'      => numFormat(($v['newPayUser'] / $v['newUser']), true),
                'newUser'        => round(floatval($v['allPay'] / $v['DAU']), 2), //活跃ARPU
            );
        }

        return $data;
    }

    //数据汇总
    private function summarys($data, $type = 2, $allPayUser = 0, $DAU = 0, $oldUserLogin = 0)
    {

        $sum      = array();
        $data_num = count($data);

        $now = strtotime(date('Y-m-d'));

        //开始统计
        foreach ($data as $k => $val) {
            $days = floor(($now - strtotime($val['dayTime'])) / 86400);
            $sum['cost'] += $val['cost'];
            $sum['newUser'] += $val['newUser'];
            $sum['disUdid'] += $val['disUdid'];
            $sum['newDevice'] += $val['newDevice'];
            $sum['regCost'] += $val['regCost'];
            $sum['allPay'] += $val['allPay'];
            $sum['allPayUser'] += $val['allPayUser'];
            $sum['oldUserLogin'] += $val['oldUserLogin'];
            $sum['DAU'] += $val['DAU'];
            $sum['newPayRate'] += $val['newPayRate'];
            $sum['newARPU'] += $val['newARPU'];
            $sum['ARPU'] += $val['ARPU'];
            $sum['newPay'] += $val['newPay'];
            $sum['newPayUser'] += $val['newPayUser'];
            $sum['allPayNow'] += $val['allPayNow'];
            $sum['totalPay'] += $val['totalPay'];
            $sum['remainNewUser'] += ($type == 1 && $days >= 2) ? $val['newUser'] : 0;
            $sum['day1'] += $val['day1'];
        }
        //开始计算

        if ($type == 1) {
            // if(session('admin.uid') == 1) var_dump($sum['remainNewUser'],$sum['day1'],$data);
            $sum['DAU']          = $DAU;
            $sum['oldUserLogin'] = $oldUserLogin;
            $sum['allPayUser']   = $allPayUser;
            $sum['day1']         = numFormat(($sum['day1'] / $sum['remainNewUser']), true); //次留
        } else {
            $sum['day1'] = numFormat(($sum['day1'] / $sum['newUser']), true); //次留
        }

        $sum['regRate']    = numFormat(($sum['disUdid'] / $sum['newDevice']), true); //注册转化率
        $sum['regCost']    = round(floatval($sum['cost'] / $sum['newUser']), 2); //注册单价
        $sum['newPayRate'] = numFormat(($sum['newPayUser'] / $sum['newUser']), true); //新增付费率
        $sum['newARPU']    = round(floatval($sum['newPay'] / $sum['newUser']), 2); //新增ARPU
        $sum['newARPPU']   = round(floatval($sum['newPay'] / $sum['newPayUser']), 2); //新增ARPPU
        $sum['actPayRate'] = numFormat(($sum['allPayUser'] / $sum['DAU']), true); //活跃付费率
        $sum['ARPU']       = round(floatval($sum['allPay'] / $sum['DAU']), 2); //活跃ARPU
        $sum['ARPPU']      = round(floatval($sum['allPay'] / $sum['allPayUser']), 2); //活跃ARPPU

        $sum['dayRate']      = numFormat(($sum['newPay'] / $sum['cost']), true); //1日回本率
        $sum['allPayRate']   = numFormat(($sum['allPayNow'] / $sum['cost']), true); //区间回本率
        $sum['totalPayRate'] = numFormat(($sum['totalPay'] / $sum['cost']), true); //至今回本率

        /*$sum['regCost'] = sprintf("%.2f",$sum['regCost']/$data_num);
        $sum['ARPU']    = sprintf("%.2f",$sum['ARPU']/$data_num);
        $sum['ARPPU']   = sprintf("%.2f",$sum['ARPPU']/$data_num);

        $sum['newPayRate'] = sprintf("%.2f",$sum['newPayRate']/$data_num).'%';
        $sum['newARPU']    = sprintf("%.2f",$sum['newARPU']/$data_num);
        $sum['newARPPU']   = sprintf("%.2f",$sum['newARPPU']/$data_num);*/

        return $sum;
    }

    /**
     * 广告成本
     */
    public function advCost()
    {
        if (IS_POST) {
            $data = I();
            $map  = array();

            $data['game_id'] && $map['game_id'] = array('IN', $data['game_id']);
//            $data['principal']      && $map['principal']      = $data['principal'];
            $data['media'] && $map['media']                   = $data['media'];
            $data['gameName'] && $map['gameName']             = array('IN', $data['gameName']);
            $data['agent'] && $map['agent']                   = $data['agent'];
            $data['gameType'] && $map['gameType']             = $data['gameType'];
            $data['channelAccount'] && $map['channelAccount'] = $data['channelAccount'];
            $data['startMonth'] && $map['costMonth'][]        = array('egt', date('Y-m-d', strtotime($data['startMonth'])));
            $data['endMonth'] && $map['costMonth'][]          = array('lt', date('Y-m-d', strtotime($data['endMonth'] . '+1 day')));

            if ($data["principal"]) {
                $admin                 = D("Admin")->commonQuery("admin", array("real" => $data["principal"]));
                $account               = getDataList("advter_account", "account", C("DB_PREFIX"), array("id" => array("IN", $admin["backstage_account_id"] ? $admin["backstage_account_id"] : "0")));
                $arr_key               = array_keys($account);
                $map["channelAccount"] = array("IN", empty($arr_key) ? "0" : $arr_key);
            }

            if (session('admin.role_id') != 1 && session('admin.role_id') != 3) {
                $map['departmentId'] = session('admin.partment');
            }

            $start    = I('start', 0, 'intval');
            $pageSize = I('limit', 30, 'intval');
            $list     = D('Admin')->getBuiList('advter_cost', $map, $start, $pageSize);
            $event    = getDataList('events', 'id', C('DB_PREFIX'));

            $cost = D('Admin')->getAdvterSum($map);
            foreach ($list['list'] as $key => $val) {
                $list['list'][$key]['eventName']     = (isset($event[$val['advter_id']]) && $event[$val['advter_id']]['is_zrl'] != 1) ? $event[$val['advter_id']]['events_name'] : '-';
                $list['list'][$key]['principalName'] = $val['principal'];
                $list['list'][$key]['gameName']      = $val['gameName'];
                $list['list'][$key]['gameType']      = strtolower($val['gameType']);
                $list['list'][$key]['cost']          = $val['cost'];
                $list['list'][$key]['opt']           = createBtn('<a href="javascript:;" onclick="costEdit(' . $val['id'] . ',this)">编辑</a> | <a href="javascript:;" onclick="costDelete(' . $val['id'] . ',this)">删除</a>');
                $rows[]                              = $list['list'][$key];
            }
            unset($res);

            $pageSummary = array('cost' => array_sum(array_column($rows, 'cost')));

            if ($data['export'] == 1) {
                $col                                     = array('costMonth' => '日期', 'principalName' => '负责人', 'gameName' => '游戏', 'gameType' => '系统', 'media' => '媒体', 'agent' => '包号', 'channelAccount' => '渠道账号', 'cost' => '支出金额');
                $sysType == 'ios' && $col['events_name'] = '推广活动名称';
                array_unshift($rows, $col);
                $pageSummary['costMonth'] = '汇总';
                array_push($rows, $pageSummary);
                export_to_csv($rows, '广告支出', $col);
                exit();
            }

            $arr = array('rows' => (empty($rows) ? array() : $rows), 'results' => $list['count'], 'summary' => array('cost' => number_format($cost, 2)));
            exit(json_encode($arr));
        } else {

            $principal = M('advter_cost')->field('distinct trim(principal) as principal')->select();
            $media = M('advteruser')->field('company_name AS name')->select();
            $principal_list = $gameName_list = $gameType_list = '<option value="0">全部</option>';
            //负责人
            foreach ($principal as $k => $v) {
                $principal_list .= "<option value='{$v['principal']}'>{$v['principal']}</option>";
            }
            //系统
            $gameType_list = "<option value='0'>全部</option> <option value='ios'>ios</option> <option value='安卓'>安卓</option>";

            $this->assign('principal_list', $principal_list);
            $this->assign('media', $media);
            $this->assign('gameType_list', $gameType_list);
            $this->display();
        }
    }

    /**
     * 导入安卓广告成本
     */
    public function importCost()
    {
        if (IS_POST) {
            if (!$_FILES['costFile']['name']) {
                $this->error('没有传入Excel');
            }
            //文件上传
            $file_info = excel_file_upload('Cost');
            if ($file_info && $file_info != '没有文件被上传！') {
                //获取文件数据并且转数组
                $fileName = './Uploads/' . $file_info['costFile']['savepath'] . $file_info['costFile']['savename'];
                $data     = excel_to_array($fileName);
                if ($data) {
                    $advterUser = getDataList('advteruser', 'company_name', C('DB_PREFIX'));
                    $agent      = getDataList('agent', 'agent', C('DB_PREFIX_API'), array('gameType' => 1));

                    $msg = array();
                    $arr = array();
                    unset($data[1]); //第一个行为标题，不需要入库
                    foreach ($data as $key => $val) {
                        //是否为空日期
                        if (empty($val[0])) {
                            $this->error('存在空日期');
                        }

                        //日期小于当年的话不允许录入
                        if (strtotime($val[0]) < strtotime(date('Y-01-01 00:00:00'))) {
                            $this->error('EXCEL中的日期存在格式错误');

                        }

                        if (empty($val[1])) {
                            $this->error('存在空负责人');
                        }

                        if (empty($val[2])) {
                            $this->error('存在空游戏名');
                        }

                        //是否存在错误广告商
                        if (!array_key_exists(trim($val[4]), $advterUser)) {
                            $msg['advter'] .= ',' . $val[4];
                        }

                        //是否存在错误渠道号
                        if (!array_key_exists(trim($val[3]), $agent)) {
                            $msg['agent'] .= ',' . $val[3];
                        }

                        $arr[] = array(
                            'costMonth'      => empty(date('Y-m-d', strtotime($val[0]))) ? '' : date('Y-m-d', strtotime($val[0])),
                            'principal'      => empty($val[1]) ? '' : $val[1],
                            'gameName'       => empty($val[2]) ? '' : $val[2],
                            'gameType'       => '安卓',
                            'agent'          => empty($val[3]) ? '' : $val[3],
                            'media'          => empty($val[4]) ? '' : $val[4],
                            'cost'           => empty($val[5]) ? 0 : $val[5],
                            'channelAccount' => empty($val[6]) ? '' : $val[6],
                            'createTime'     => time(),
                            'creater'        => session('admin.realname'),
                            'departmentId'   => session('admin.partment'),
                            'game_id'        => $agent[$val[3]]['game_id'],
                        );
                    }

                    if ($msg || count($msg) > 0) {
                        $errorInfo = '存在错误数据，请修改再上传,信息为：<br/>';
                        if ($msg['advter']) {
                            $errorInfo .= ' 错误媒体:【' . trim($msg['advter'], ',') . '】<br/>';
                        }
                        if ($msg['agent']) {
                            $errorInfo .= ' 错误包号:【' . trim($msg['agent'], ',') . '】';
                        }

                        unset($arr);
                        $this->error($errorInfo, '', 10);
                    }

                    if ($arr && D('Admin')->commonAddAll('advter_cost', $arr)) {
                        @unlink($fileName);
                        $this->success('成本导入成功');
                    } else {
                        @unlink($fileName);
                        $this->error('成本导入失败');
                    }
                }
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

    /**
     * 导入IOS广告成本
     */
    public function iosImportCost()
    {
        if (IS_POST) {
            if (!$_FILES['costFile']['name']) {
                $this->error('没有传入Excel');
            }
            //文件上传
            $file_info = excel_file_upload('Cost');
            if ($file_info && $file_info != '没有文件被上传！') {
                //获取文件数据并且转数组
                $fileName = './Uploads/' . $file_info['costFile']['savepath'] . $file_info['costFile']['savename'];
                $data     = excel_to_array($fileName);
                if ($data) {
                    $advterUser = getDataList('advteruser', 'company_name', C('DB_PREFIX'));
                    $agent      = getDataList('agent', 'agent', C('DB_PREFIX_API'), array('gameType' => 2));
                    $event      = getDataList('events', 'id', C('DB_PREFIX'));
                    $eventAgent = getDataList('events', 'agent', C('DB_PREFIX'), array('is_zrl' => 1));

                    $msg = array();
                    $arr = array();
                    unset($data[1]); //第一个行为标题，不需要入库
                    foreach ($data as $key => $val) {
                        if (empty($val[0])) {
                            $this->error('存在空日期');
                        }

                        //日期小于当年的话不允许录入
                        if (strtotime($val[0]) < strtotime(date('Y-01-01  00:00:00'))) {
                            $this->error('EXCEL中的日期存在格式错误');

                        }

                        if (empty($val[1])) {
                            $this->error('存在空负责人');
                        }

                        if (empty($val[2])) {
                            $this->error('存在空游戏名');
                        }

                        if (empty($val[6])) {
                            $this->error('存在空渠道账号');
                        }

                        //是否存在错误广告商
                        if (!array_key_exists(trim($val[4]), $advterUser)) {
                            $msg['advter'] .= ',' . $val[4];
                        }

                        //是否存在错误渠道号
                        if (!array_key_exists(trim($val[3]), $agent)) {
                            $msg['agent'] .= ',' . $val[3];
                        }

                        //是否存在错误推广告ID
                        if (!empty($val[7]) && !array_key_exists(trim($val[7]), $event) && $event[$val[7]]['agent'] != $val[3]) {
                            $msg['events'] .= ',' . $val[7];
                        }

                        $arr[] = array(
                            'costMonth'      => empty(date('Y-m-d', strtotime($val[0]))) ? '' : date('Y-m-d', strtotime($val[0])),
                            'principal'      => empty($val[1]) ? '' : $val[1],
                            'gameName'       => empty($val[2]) ? '' : $val[2],
                            'gameType'       => 'ios',
                            'agent'          => empty($val[3]) ? '' : $val[3],
                            'media'          => empty($val[4]) ? '' : $val[4],
                            'cost'           => empty($val[5]) ? 0 : $val[5],
                            'channelAccount' => empty($val[6]) ? '' : $val[6],
                            'advter_id'      => empty($val[7]) ? $eventAgent[$val[3]]['id'] : $val[7],
                            'createTime'     => time(),
                            'creater'        => session('admin.realname'),
                            'departmentId'   => session('admin.partment'),
                            'game_id'        => $agent[$val[3]]['game_id'],
                        );
                    }

                    if ($msg || count($msg) > 0) {
                        $errorInfo = '存在错误数据，请修改再上传,信息为：<br/>';
                        if ($msg['advter']) {
                            $errorInfo .= ' 错误媒体:【' . trim($msg['advter'], ',') . '】<br/>';
                        }
                        if ($msg['agent']) {
                            $errorInfo .= ' 错误包号:【' . trim($msg['agent'], ',') . '】<br/>';
                        }

                        if ($msg['events']) {
                            $errorInfo .= ' 推广活动ID与推广渠道包不一致或错误推广活动ID:【' . trim($msg['events'], ',') . '】';
                        }

                        unset($arr);
                        $this->error($errorInfo, '', 10);
                    }

                    if ($arr && D('Admin')->commonAddAll('advter_cost', $arr)) {
                        @unlink($fileName);
                        $this->success('成本导入成功');
                    } else {
                        @unlink($fileName);
                        $this->error('成本导入失败');
                    }
                }
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

    /**
     * 月度指标列表
     */
    public function monthTarget()
    {
        if (IS_POST) {
            $data       = I();
            $map        = array();
            $principals = trim($_SESSION['admin']['principal_ids']);
            if ($data['principalId']) {
                $map['principalId'] = $data['principalId'];
            } elseif ($principals !== '0') {
                $map['principalId'] = array('in', explode(',', $principals));
            };

            $data['gameId'] && $map['gameId']          = $data['gameId'];
            $data['startMonth'] && $map['costMonth'][] = array('egt', date('Y-m-01', strtotime($data['startMonth'])));
            $data['endMonth'] && $map['costMonth'][]   = array('lt', date('Y-m-01', strtotime($data['endMonth'] . '+1 month')));
            $start                                     = I('start', 0, 'intval');
            $pageSize                                  = I('limit', 30, 'intval');
            $list                                      = D('Admin')->getBuiList($this->table, $map, $start, $pageSize);
            $principal_list                            = getDataList('principal', 'id');
            $game_list                                 = getDataList('game', 'id', C('DB_PREFIX_API'));
            foreach ($list['list'] as $key => $val) {
                $agent_list = array_column(D('Admin')->getAgent($val['principalId']), 'agent');
                $amount     = D('Admin')->getMonthOrder($val['TargetMonth'], $agent_list);

                $list['list'][$key]['completeRate']  = numFormat($amount / $val['monthTarget'], true);
                $list['list'][$key]['amount']        = $amount ? $amount : 0;
                $list['list'][$key]['principalName'] = $principal_list[$val['principalId']]['principal_name'];
                $list['list'][$key]['gameName']      = $game_list[$val['gameId']]['gameName'];
                $rows[]                              = $list['list'][$key];
            }
            $arr = array('rows' => (empty($rows) ? array() : $rows), 'results' => $list['count']);
            exit(json_encode($arr));
        } else {
            $this->display();
        }

    }

    /**
     * 合同列表
     */
    public function contractList()
    {
        if (IS_POST) {
            $data = I();

            //处理搜索条件
            $map    = array();
            $roleId = trim($_SESSION["admin"]["role_id"]);
            if (!in_array($roleId, array(1, 3, 8, 13, 14, 17, 25, 32))) {
                $map["string"] = "partment = " . trim($_SESSION["admin"]["partment"]);
            }
            $data["game"] && $map["game"]               = $data["game"];
            $data["type"] && $map["type"]               = $data["type"];
            $data["company"] && $map["company"]         = $data["company"];
            $data["childNo"] && $map["childNo"]         = $data["childNo"];
            $data["status"] && $map["status"]           = $data["status"] - 1;
            $data["contract"] && $map["contract"]       = $data["contract"];
            $data["contractNo"] && $map["contractNo"]   = $data["contractNo"];
            $data["followAdmin"] && $map["followAdmin"] = $data["followAdmin"];
            $data["principalId"] && $map["principalId"] = $data["principalId"];
            $order                                      = $data["order"] ? $data["order"] : "DESC";

            //权限控制
            $uid    = session('admin.uid');
            $roleId = session('admin.role_id');
            if (in_array($uid, $this->contractAll) || $roleId == 1) {
//查看全部部门
                $data["partment"] && $map["partment"] = $data["partment"];
            } elseif (in_array($uid, $this->contractOne)) {
//查看一部
                if (!$data['partment']) {
                    $map['partment'] = 1;
                } else {
                    $data['partment'] == 1 ? $map['partment'] = 1 : $map['partment'] = 0;
                }
            } elseif (in_array($uid, $this->contractTwo)) {
//查看二部
                if (!$data['partment']) {
                    $map['partment'] = 2;
                } else {
                    $data['partment'] == 2 ? $map['partment'] = 2 : $map['partment'] = 0;
                }
            } else {
                $map['partment'] = 0;
            }

            $res = D("Admin")->getContractData($map, $order);

            $admin_list     = getDataList("admin", "id", C("DB_PREFIX"));
            $principal_list = getDataList("principal", "id", C("DB_PREFIX"));
            $partment_list  = array("1" => "发行一部", "2" => "发行二部");
            $status_list    = array("0" => "是", "1" => "否", "2" => "空号", "3" => "作废");
            $pay_list       = array("1" => "日结", "2" => "月结", "3" => "预付", "4" => "垫付", "5" => "分期");

            if ($data["export"] == 1) {
                import("Org.Util.PHPExcel", LIB_PATH, ".php");
                error_reporting(E_ALL);
//                date_default_timezone_set('Europe/London');
                date_default_timezone_set("PRC");
                $objPHPExcel = new \PHPExcel();

                $objPHPExcel->getActiveSheet()->setCellValue('A1', '单位：海南创娱网络科技有限公司');
                $objPHPExcel->getActiveSheet()->setCellValue('A2', '单元格');
                $objPHPExcel->getActiveSheet()->setCellValue('B2', '');
                $objPHPExcel->getActiveSheet()->setCellValue('C2', '表示：合同作废');
                $objPHPExcel->getActiveSheet()->setCellValue('A3', '单元格');
                $objPHPExcel->getActiveSheet()->setCellValue('B3', '');
                $objPHPExcel->getActiveSheet()->setCellValue('C3', '表示：合同号空号');

                $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setSize(14)->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyle('A2')->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyle('B2')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB("00FFFF00"); //设置颜色
                $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyle('B3')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB("0087CEFA"); //设置颜色

                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A4', '序号')
                    ->setCellValue('B4', '部门')
                    ->setCellValue('C4', '跟进人')
                    ->setCellValue('D4', '是否生效')
                    ->setCellValue('E4', '签订日期')
                    ->setCellValue('F4', '生效日期')
                    ->setCellValue('G4', '失效日期')
                    ->setCellValue('H4', '有效天数')
                    ->setCellValue('I4', '签订人')
                    ->setCellValue('J4', '类别')
                    ->setCellValue('K4', '游戏名称')
                    ->setCellValue('L4', '合同名称')
                    ->setCellValue('M4', '合同编号')
                    ->setCellValue('N4', '信息服务/签或报告编号')
                    ->setCellValue('O4', '充值账号')
                    ->setCellValue('P4', '充值金额')
                    ->setCellValue('Q4', '合同签订单位')
                    ->setCellValue('R4', '主要约定条款')
                    ->setCellValue('S4', '结算方式')
                    ->setCellValue('T4', '总金额')
                    ->setCellValue('U4', '已付金额')
                    ->setCellValue('V4', '付款时间')
                    ->setCellValue('W4', '未付金额')
                    ->setCellValue('X4', '票据号')
                    ->setCellValue('Y4', '收到发票金额')
                    ->setCellValue('Z4', '未到票金额')
                    ->setCellValue('AA4', '备注');
                $objPHPExcel->getActiveSheet()->getStyle('A4:AA4')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                $num            = 4;
                $amount_sum     = 0;
                $pay_amount_sum = 0;
                $invoice_sum    = 0;
                foreach ($res as $key => $val) {
                    $num++;
                    $day = ($val["endTime"] ? (($val["endTime"] - strtotime(date("Y-m-d")) > 0) ? ($val["endTime"] - strtotime(date("Y-m-d"))) / 86400 : 0) : "");
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('A' . $num, $val['id'], \PHPExcel_Cell_DataType::TYPE_NUMERIC);
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('B' . $num, $partment_list[$val["partment"]] ? $partment_list[$val["partment"]] : "（未知）", \PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('C' . $num, $admin_list[$val["followAdmin"]]["real"] ? $admin_list[$val["followAdmin"]]["real"] : "（未知）", \PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('D' . $num, $status_list[$val["status"]] ? $status_list[$val["status"]] : "（未知）", \PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('E' . $num, $val["fileTime"] ? date("Ymd", $val["fileTime"]) : "", \PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('F' . $num, $val["startTime"] ? date("Ymd", $val["startTime"]) : "", \PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('G' . $num, $val["endTime"] ? date("Ymd", $val["endTime"]) : "", \PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('H' . $num, $day, $val["endTime"] ? \PHPExcel_Cell_DataType::TYPE_NUMERIC : \PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('I' . $num, $principal_list[$val["principalId"]]["principal_name"] ? $principal_list[$val["principalId"]]["principal_name"] : "（未知）", \PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('J' . $num, $val["type"] ? $val["type"] : "", \PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('K' . $num, $val["game"] ? $val["game"] : "", \PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('L' . $num, $val["contract"] ? $val["contract"] : "", \PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('M' . $num, $val["contractNo"] ? $val["contractNo"] : "", \PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('N' . $num, $val["childNo"] ? $val["childNo"] : "", \PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('Q' . $num, $val["company"] ? $val["company"] : "", \PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('R' . $num, $val["info"] ? $val["info"] : "", \PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('S' . $num, $pay_list[$val["payType"]] ? $pay_list[$val["payType"]] : "（未知）", \PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('T' . $num, $val["amount"] ? $val["amount"] : "", $val["amount"] ? \PHPExcel_Cell_DataType::TYPE_NUMERIC : \PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('X' . $num, $val["receipt"] ? $val["receipt"] : "", \PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('Y' . $num, $val["invoiceAmount"] ? $val["invoiceAmount"] : "", $val["invoiceAmount"] ? \PHPExcel_Cell_DataType::TYPE_NUMERIC : \PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('AA' . $num, $val["ext"] ? $val["ext"] : "", \PHPExcel_Cell_DataType::TYPE_STRING);

                    $objPHPExcel->getActiveSheet()->getStyle('A' . $num . ":AA" . $num)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    if ($val["status"] == 2) {
                        $objPHPExcel->getActiveSheet()->getStyle('A' . $num . ":AA" . $num)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB("0087CEFA"); //设置颜色
                    } elseif ($val["status"] == 3) {
                        $objPHPExcel->getActiveSheet()->getStyle('A' . $num . ":AA" . $num)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB("00FFFF00"); //设置颜色
                    }

                    $now_num = $num;

                    $arr     = explode("|", $val["account"]);
                    $acc_sum = 0;
                    if (!$arr) {
                        $arr[] = $val["account"];
                    }

                    foreach ($arr as $list) {
                        $list_arr = explode(",", $list);
                        if ($list_arr && $list[0]) {
                            $acc_sum += $list_arr[1];

                            $objPHPExcel->getActiveSheet()->setCellValueExplicit('O' . $num, $list_arr[0], \PHPExcel_Cell_DataType::TYPE_STRING);
                            $objPHPExcel->getActiveSheet()->setCellValueExplicit('P' . $num, $list_arr[1], \PHPExcel_Cell_DataType::TYPE_NUMERIC);
                            $objPHPExcel->getActiveSheet()->getStyle('A' . $num . ":AA" . $num)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
                            if ($val["status"] == 2) {
                                $objPHPExcel->getActiveSheet()->getStyle('A' . $num . ":AA" . $num)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB("0087CEFA"); //设置颜色
                            } elseif ($val["status"] == 3) {
                                $objPHPExcel->getActiveSheet()->getStyle('A' . $num . ":AA" . $num)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB("00FFFF00"); //设置颜色
                            }
                            $num++;
                        }
                    }

                    $max_num = $num;
                    $num     = $now_num;

                    $arr     = explode("|", $val["payAmountTime"]);
                    $pay_sum = 0;
                    if (!$arr) {
                        $arr[] = $val["payAmountTime"];
                    }

                    foreach ($arr as $list) {
                        $list_arr = explode(",", $list);
                        if ($list_arr && $list[0]) {
                            $pay_sum += $list_arr[0];

                            $objPHPExcel->getActiveSheet()->setCellValueExplicit('U' . $num, $list_arr[0], \PHPExcel_Cell_DataType::TYPE_NUMERIC);
                            $objPHPExcel->getActiveSheet()->setCellValueExplicit('V' . $num, date("Y-m-d", $list_arr[1]), \PHPExcel_Cell_DataType::TYPE_STRING);
                            $objPHPExcel->getActiveSheet()->getStyle('A' . $num . ":AA" . $num)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
                            if ($val["status"] == 2) {
                                $objPHPExcel->getActiveSheet()->getStyle('A' . $num . ":AA" . $num)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB("0087CEFA"); //设置颜色
                            } elseif ($val["status"] == 3) {
                                $objPHPExcel->getActiveSheet()->getStyle('A' . $num . ":AA" . $num)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB("00FFFF00"); //设置颜色
                            }
                            $num++;
                        }
                    }
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('W' . $now_num, ($val["amount"] || $pay_sum) ? ($val["amount"] - $pay_sum) : "", $val["amount"] ? \PHPExcel_Cell_DataType::TYPE_NUMERIC : \PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('Z' . $now_num, $pay_sum ? ($pay_sum - $val["invoiceAmount"]) : "", $pay_sum ? \PHPExcel_Cell_DataType::TYPE_NUMERIC : \PHPExcel_Cell_DataType::TYPE_STRING);
                    $amount_sum += $val["amount"];
                    $pay_amount_sum += $pay_sum;
                    $invoice_sum += $val["invoiceAmount"];

                    $max_num = max($num, $max_num);

                    if ($max_num > $now_num + 1) {
                        $objPHPExcel->getActiveSheet()->mergeCells('A' . $now_num . ':A' . ($max_num - 1));
                        $objPHPExcel->getActiveSheet()->mergeCells('B' . $now_num . ':B' . ($max_num - 1));
                        $objPHPExcel->getActiveSheet()->mergeCells('C' . $now_num . ':C' . ($max_num - 1));
                        $objPHPExcel->getActiveSheet()->mergeCells('D' . $now_num . ':D' . ($max_num - 1));
                        $objPHPExcel->getActiveSheet()->mergeCells('E' . $now_num . ':E' . ($max_num - 1));
                        $objPHPExcel->getActiveSheet()->mergeCells('F' . $now_num . ':F' . ($max_num - 1));
                        $objPHPExcel->getActiveSheet()->mergeCells('G' . $now_num . ':G' . ($max_num - 1));
                        $objPHPExcel->getActiveSheet()->mergeCells('H' . $now_num . ':H' . ($max_num - 1));
                        $objPHPExcel->getActiveSheet()->mergeCells('I' . $now_num . ':I' . ($max_num - 1));
                        $objPHPExcel->getActiveSheet()->mergeCells('J' . $now_num . ':J' . ($max_num - 1));
                        $objPHPExcel->getActiveSheet()->mergeCells('K' . $now_num . ':K' . ($max_num - 1));
                        $objPHPExcel->getActiveSheet()->mergeCells('L' . $now_num . ':L' . ($max_num - 1));
                        $objPHPExcel->getActiveSheet()->mergeCells('M' . $now_num . ':M' . ($max_num - 1));
                        $objPHPExcel->getActiveSheet()->mergeCells('N' . $now_num . ':N' . ($max_num - 1));
                        $objPHPExcel->getActiveSheet()->mergeCells('Q' . $now_num . ':Q' . ($max_num - 1));
                        $objPHPExcel->getActiveSheet()->mergeCells('R' . $now_num . ':R' . ($max_num - 1));
                        $objPHPExcel->getActiveSheet()->mergeCells('S' . $now_num . ':S' . ($max_num - 1));
                        $objPHPExcel->getActiveSheet()->mergeCells('T' . $now_num . ':T' . ($max_num - 1));
                        $objPHPExcel->getActiveSheet()->mergeCells('W' . $now_num . ':W' . ($max_num - 1));
                        $objPHPExcel->getActiveSheet()->mergeCells('X' . $now_num . ':X' . ($max_num - 1));
                        $objPHPExcel->getActiveSheet()->mergeCells('Y' . $now_num . ':Y' . ($max_num - 1));
                        $objPHPExcel->getActiveSheet()->mergeCells('Z' . $now_num . ':Z' . ($max_num - 1));
                        $objPHPExcel->getActiveSheet()->mergeCells('AA' . $now_num . ':AA' . ($max_num - 1));
                    }
                    if ($max_num > $now_num) {
                        $num = $max_num - 1;
                    }

                    //子类
                    foreach ($res[$key]["children"] as $k => $v) {
                        if (!$v["id"]) {
                            continue;
                        }

                        $num++;
                        $day = $v["endTime"] ? (($v["endTime"] - strtotime(date("Y-m-d")) > 0) ? ($v["endTime"] - strtotime(date("Y-m-d"))) / 86400 : 0) : "";
                        $objPHPExcel->getActiveSheet()->setCellValueExplicit('A' . $num, $v['id'], \PHPExcel_Cell_DataType::TYPE_NUMERIC);
                        $objPHPExcel->getActiveSheet()->setCellValueExplicit('B' . $num, $partment_list[$v["partment"]] ? $partment_list[$v["partment"]] : "（未知）", \PHPExcel_Cell_DataType::TYPE_STRING);
                        $objPHPExcel->getActiveSheet()->setCellValueExplicit('C' . $num, $admin_list[$v["followAdmin"]]["real"] ? $admin_list[$v["followAdmin"]]["real"] : "（未知）", \PHPExcel_Cell_DataType::TYPE_STRING);
                        $objPHPExcel->getActiveSheet()->setCellValueExplicit('D' . $num, $status_list[$v["status"]] ? $status_list[$v["status"]] : "（未知）", \PHPExcel_Cell_DataType::TYPE_STRING);
                        $objPHPExcel->getActiveSheet()->setCellValueExplicit('E' . $num, $v["fileTime"] ? date("Ymd", $v["fileTime"]) : "", \PHPExcel_Cell_DataType::TYPE_STRING);
                        $objPHPExcel->getActiveSheet()->setCellValueExplicit('F' . $num, $v["startTime"] ? date("Ymd", $v["startTime"]) : "", \PHPExcel_Cell_DataType::TYPE_STRING);
                        $objPHPExcel->getActiveSheet()->setCellValueExplicit('G' . $num, $v["endTime"] ? date("Ymd", $v["endTime"]) : "", \PHPExcel_Cell_DataType::TYPE_STRING);
                        $objPHPExcel->getActiveSheet()->setCellValueExplicit('H' . $num, $day, $v["endTime"] ? \PHPExcel_Cell_DataType::TYPE_NUMERIC : \PHPExcel_Cell_DataType::TYPE_STRING);
                        $objPHPExcel->getActiveSheet()->setCellValueExplicit('I' . $num, $principal_list[$v["principalId"]]["principal_name"] ? $principal_list[$v["principalId"]]["principal_name"] : "（未知）", \PHPExcel_Cell_DataType::TYPE_STRING);
                        $objPHPExcel->getActiveSheet()->setCellValueExplicit('J' . $num, $v["type"] ? $v["type"] : "", \PHPExcel_Cell_DataType::TYPE_STRING);
                        $objPHPExcel->getActiveSheet()->setCellValueExplicit('K' . $num, $v["game"] ? $v["game"] : "", \PHPExcel_Cell_DataType::TYPE_STRING);
                        $objPHPExcel->getActiveSheet()->setCellValueExplicit('L' . $num, $v["contract"] ? $v["contract"] : "", \PHPExcel_Cell_DataType::TYPE_STRING);
                        $objPHPExcel->getActiveSheet()->setCellValueExplicit('M' . $num, $v["contractNo"] ? $v["contractNo"] : "", \PHPExcel_Cell_DataType::TYPE_STRING);
                        $objPHPExcel->getActiveSheet()->setCellValueExplicit('N' . $num, $v["childNo"] ? $v["childNo"] : "", \PHPExcel_Cell_DataType::TYPE_STRING);
                        $objPHPExcel->getActiveSheet()->setCellValueExplicit('Q' . $num, $v["company"] ? $v["company"] : "", \PHPExcel_Cell_DataType::TYPE_STRING);
                        $objPHPExcel->getActiveSheet()->setCellValueExplicit('R' . $num, $v["info"] ? $v["info"] : "", \PHPExcel_Cell_DataType::TYPE_STRING);
                        $objPHPExcel->getActiveSheet()->setCellValueExplicit('S' . $num, $pay_list[$v["payType"]] ? $pay_list[$v["payType"]] : "（未知）", \PHPExcel_Cell_DataType::TYPE_STRING);
                        $objPHPExcel->getActiveSheet()->setCellValueExplicit('T' . $num, $v["amount"] ? $v["amount"] : "", $v["amount"] ? \PHPExcel_Cell_DataType::TYPE_NUMERIC : \PHPExcel_Cell_DataType::TYPE_STRING);
                        $objPHPExcel->getActiveSheet()->setCellValueExplicit('X' . $num, $v["receipt"] ? $v["receipt"] : "", \PHPExcel_Cell_DataType::TYPE_STRING);
                        $objPHPExcel->getActiveSheet()->setCellValueExplicit('Y' . $num, $v["invoiceAmount"] ? $v["invoiceAmount"] : "", $v["invoiceAmount"] ? \PHPExcel_Cell_DataType::TYPE_NUMERIC : \PHPExcel_Cell_DataType::TYPE_STRING);
                        $objPHPExcel->getActiveSheet()->setCellValueExplicit('AA' . $num, $v["ext"] ? $v["ext"] : "", \PHPExcel_Cell_DataType::TYPE_STRING);

                        $objPHPExcel->getActiveSheet()->getStyle('A' . $num . ":AA" . $num)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                        if ($v["status"] == 2) {
                            $objPHPExcel->getActiveSheet()->getStyle('A' . $num . ":AA" . $num)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB("0087CEFA"); //设置颜色
                        } elseif ($v["status"] == 3) {
                            $objPHPExcel->getActiveSheet()->getStyle('A' . $num . ":AA" . $num)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB("00FFFF00"); //设置颜色
                        }

                        $now_num = $num;

                        $arr     = explode("|", $v["account"]);
                        $acc_sum = 0;
                        if (!$arr) {
                            $arr[] = $v["account"];
                        }

                        foreach ($arr as $list) {
                            $list_arr = explode(",", $list);
                            if ($list_arr && $list[0]) {
                                $acc_sum += $list_arr[1];

                                $objPHPExcel->getActiveSheet()->setCellValueExplicit('O' . $num, $list_arr[0], \PHPExcel_Cell_DataType::TYPE_STRING);
                                $objPHPExcel->getActiveSheet()->setCellValueExplicit('P' . $num, $list_arr[1], \PHPExcel_Cell_DataType::TYPE_NUMERIC);
                                $objPHPExcel->getActiveSheet()->getStyle('A' . $num . ":AA" . $num)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
                                if ($v["status"] == 2) {
                                    $objPHPExcel->getActiveSheet()->getStyle('A' . $num . ":AA" . $num)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB("0087CEFA"); //设置颜色
                                } elseif ($v["status"] == 3) {
                                    $objPHPExcel->getActiveSheet()->getStyle('A' . $num . ":AA" . $num)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB("00FFFF00"); //设置颜色
                                }
                                $num++;
                            }
                        }

                        $max_num = $num;
                        $num     = $now_num;

                        $arr     = explode("|", $v["payAmountTime"]);
                        $pay_sum = 0;
                        if (!$arr) {
                            $arr[] = $v["payAmountTime"];
                        }

                        foreach ($arr as $list) {
                            $list_arr = explode(",", $list);
                            if ($list_arr && $list[0]) {
                                $pay_sum += $list_arr[0];

                                $objPHPExcel->getActiveSheet()->setCellValueExplicit('U' . $num, $list_arr[0], \PHPExcel_Cell_DataType::TYPE_NUMERIC);
                                $objPHPExcel->getActiveSheet()->setCellValueExplicit('V' . $num, date("Y-m-d", $list_arr[1]), \PHPExcel_Cell_DataType::TYPE_STRING);
                                $objPHPExcel->getActiveSheet()->getStyle('A' . $num . ":AA" . $num)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
                                if ($v["status"] == 2) {
                                    $objPHPExcel->getActiveSheet()->getStyle('A' . $num . ":AA" . $num)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB("0087CEFA"); //设置颜色
                                } elseif ($v["status"] == 3) {
                                    $objPHPExcel->getActiveSheet()->getStyle('A' . $num . ":AA" . $num)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB("00FFFF00"); //设置颜色
                                }
                                $num++;
                            }
                        }
                        $objPHPExcel->getActiveSheet()->setCellValueExplicit('W' . $now_num, ($v["amount"] || $pay_sum) ? ($v["amount"] - $pay_sum) : "", $v["amount"] ? \PHPExcel_Cell_DataType::TYPE_NUMERIC : \PHPExcel_Cell_DataType::TYPE_STRING);
                        $objPHPExcel->getActiveSheet()->setCellValueExplicit('Z' . $now_num, $pay_sum ? ($pay_sum - $v["invoiceAmount"]) : "", $pay_sum ? \PHPExcel_Cell_DataType::TYPE_NUMERIC : \PHPExcel_Cell_DataType::TYPE_STRING);
                        $amount_sum += $v["amount"];
                        $pay_amount_sum += $pay_sum;
                        $invoice_sum += $v["invoiceAmount"];

                        $max_num = max($num, $max_num);

                        if ($max_num > $now_num + 1) {
                            $objPHPExcel->getActiveSheet()->mergeCells('A' . $now_num . ':A' . ($max_num - 1));
                            $objPHPExcel->getActiveSheet()->mergeCells('B' . $now_num . ':B' . ($max_num - 1));
                            $objPHPExcel->getActiveSheet()->mergeCells('C' . $now_num . ':C' . ($max_num - 1));
                            $objPHPExcel->getActiveSheet()->mergeCells('D' . $now_num . ':D' . ($max_num - 1));
                            $objPHPExcel->getActiveSheet()->mergeCells('E' . $now_num . ':E' . ($max_num - 1));
                            $objPHPExcel->getActiveSheet()->mergeCells('F' . $now_num . ':F' . ($max_num - 1));
                            $objPHPExcel->getActiveSheet()->mergeCells('G' . $now_num . ':G' . ($max_num - 1));
                            $objPHPExcel->getActiveSheet()->mergeCells('H' . $now_num . ':H' . ($max_num - 1));
                            $objPHPExcel->getActiveSheet()->mergeCells('I' . $now_num . ':I' . ($max_num - 1));
                            $objPHPExcel->getActiveSheet()->mergeCells('J' . $now_num . ':J' . ($max_num - 1));
                            $objPHPExcel->getActiveSheet()->mergeCells('K' . $now_num . ':K' . ($max_num - 1));
                            $objPHPExcel->getActiveSheet()->mergeCells('L' . $now_num . ':L' . ($max_num - 1));
                            $objPHPExcel->getActiveSheet()->mergeCells('M' . $now_num . ':M' . ($max_num - 1));
                            $objPHPExcel->getActiveSheet()->mergeCells('N' . $now_num . ':N' . ($max_num - 1));
                            $objPHPExcel->getActiveSheet()->mergeCells('Q' . $now_num . ':Q' . ($max_num - 1));
                            $objPHPExcel->getActiveSheet()->mergeCells('R' . $now_num . ':R' . ($max_num - 1));
                            $objPHPExcel->getActiveSheet()->mergeCells('S' . $now_num . ':S' . ($max_num - 1));
                            $objPHPExcel->getActiveSheet()->mergeCells('T' . $now_num . ':T' . ($max_num - 1));
                            $objPHPExcel->getActiveSheet()->mergeCells('W' . $now_num . ':W' . ($max_num - 1));
                            $objPHPExcel->getActiveSheet()->mergeCells('X' . $now_num . ':X' . ($max_num - 1));
                            $objPHPExcel->getActiveSheet()->mergeCells('Y' . $now_num . ':Y' . ($max_num - 1));
                            $objPHPExcel->getActiveSheet()->mergeCells('Z' . $now_num . ':Z' . ($max_num - 1));
                            $objPHPExcel->getActiveSheet()->mergeCells('AA' . $now_num . ':AA' . ($max_num - 1));
                        }
                        if ($max_num > $now_num) {
                            $num = $max_num - 1;
                        }

                    }
                }
                $num++;
                $objPHPExcel->getActiveSheet()->setCellValueExplicit('A' . $num, "汇总", \PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->setCellValueExplicit('T' . $num, $amount_sum ? $amount_sum : "", $amount_sum ? \PHPExcel_Cell_DataType::TYPE_NUMERIC : \PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->setCellValueExplicit('U' . $num, $pay_amount_sum ? $pay_amount_sum : "", $pay_amount_sum ? \PHPExcel_Cell_DataType::TYPE_NUMERIC : \PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->setCellValueExplicit('W' . $num, $amount_sum ? $amount_sum - $pay_amount_sum : "", $amount_sum ? \PHPExcel_Cell_DataType::TYPE_NUMERIC : \PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->setCellValueExplicit('Y' . $num, $invoice_sum ? $invoice_sum : "", $invoice_sum ? \PHPExcel_Cell_DataType::TYPE_NUMERIC : \PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->setCellValueExplicit('Z' . $num, $pay_amount_sum ? $pay_amount_sum - $invoice_sum : "", $pay_amount_sum ? \PHPExcel_Cell_DataType::TYPE_NUMERIC : \PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->getStyle('A' . $num . ":AA" . $num)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(6);
                $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
                $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(8);
                $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(8);
                $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(12);
                $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(12);
                $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(12);
                $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(10);
                $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(8);
                $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(30);
                $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(30);
                $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(20);
                $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(22);
                $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(24);
                $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(12);
                $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(35);
                $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(50);
                $objPHPExcel->getActiveSheet()->getColumnDimension('S')->setWidth(10);
                $objPHPExcel->getActiveSheet()->getColumnDimension('T')->setWidth(12);
                $objPHPExcel->getActiveSheet()->getColumnDimension('U')->setWidth(12);
                $objPHPExcel->getActiveSheet()->getColumnDimension('V')->setWidth(12);
                $objPHPExcel->getActiveSheet()->getColumnDimension('W')->setWidth(12);
                $objPHPExcel->getActiveSheet()->getColumnDimension('X')->setWidth(20);
                $objPHPExcel->getActiveSheet()->getColumnDimension('Y')->setWidth(12);
                $objPHPExcel->getActiveSheet()->getColumnDimension('Y')->setWidth(12);
                $objPHPExcel->getActiveSheet()->getColumnDimension('AA')->setWidth(25);

                $objPHPExcel->getActiveSheet()->setTitle('User');
                $objPHPExcel->setActiveSheetIndex(0);
                header('Content-Type: application/vnd.ms-excel');
                header('Content-Disposition: attachment;filename=合同明细表.xls');
                header('Cache-Control: max-age=0');
                $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
                $objWriter->save('php://output');
                exit;
            } else {
                $amount_sum     = 0;
                $pay_amount_sum = 0;
                $invoice_sum    = 0;
                foreach ($res as $key => $val) {
                    $file        = explode(".", $val["attachment"]);
                    $file_name   = explode("/", $val["attachment"]);
                    $arr         = explode("|", $val["account"]);
                    $acc_sum     = 0;
                    $i           = 1;
                    $account_str = "";
                    if (!$arr) {
                        $arr[] = $val["account"];
                    }

                    foreach ($arr as $list) {
                        $list_arr = explode(",", $list);
                        if ($list_arr && $list[0]) {
                            $account_str .= ($i == 1 ? "" : "<div class='br_line'></div>") . "<span class='br_span1'>" . $list_arr[0] . "</span><span class='br_span2'>" . $list_arr[1] . "元</span>";
                            $acc_sum += $list_arr[1];
                            $i++;
                        }
                    }
                    if ($acc_sum) {
                        $account_str .= "<div class='br_line'></div><span class='br_span1'>汇总</span><span class='br_span2'>" . $acc_sum . "元</span>";
                    }

                    $arr           = explode("|", $val["payAmountTime"]);
                    $payAmountTime = "";
                    $pay_sum       = 0;
                    $i             = 1;
                    if (!$arr) {
                        $arr[] = $val["payAmountTime"];
                    }

                    foreach ($arr as $list) {
                        $list_arr = explode(",", $list);
                        if ($list_arr && $list[0]) {
                            $payAmountTime .= ($i == 1 ? "" : "<div class='br_line'></div>") . "<span class='br_span1'>" . $list_arr[0] . "元</span><span class='br_span2'>" . date("Y-m-d", $list_arr[1]) . "</span>";
                            $pay_sum += $list_arr[0];
                            $i++;
                        }
                    }
                    if ($pay_sum) {
                        $payAmountTime .= "<div class='br_line'></div><span class='br_span1'>汇总</span><span class='br_span2'>" . $pay_sum . "元</span>";
                    }

                    $amount_sum += $val["amount"];
                    $pay_amount_sum += $pay_sum;
                    $invoice_sum += $val["invoiceAmount"];
                    $res[$key]["partmentName"]    = $partment_list[$val["partment"]] ? $partment_list[$val["partment"]] : "（未知）";
                    $res[$key]["follow"]          = $admin_list[$val["followAdmin"]]["real"] ? $admin_list[$val["followAdmin"]]["real"] : "（未知）";
                    $res[$key]["accountStr"]      = $account_str;
                    $res[$key]["statusName"]      = $status_list[$val["status"]] ? $status_list[$val["status"]] : "（未知）";
                    $res[$key]["file"]            = $val["fileTime"] ? date("Ymd", $val["fileTime"]) : "";
                    $res[$key]["start"]           = $val["startTime"] ? date("Ymd", $val["startTime"]) : "";
                    $res[$key]["end"]             = $val["endTime"] ? date("Ymd", $val["endTime"]) : "";
                    $res[$key]["day"]             = $val["endTime"] ? (($val["endTime"] - strtotime(date("Y-m-d")) > 0) ? ($val["endTime"] - strtotime(date("Y-m-d"))) / 86400 : 0) : "";
                    $res[$key]["principal"]       = $principal_list[$val["principalId"]]["principal_name"] ? $principal_list[$val["principalId"]]["principal_name"] : "（未知）";
                    $res[$key]["thePayTime"]      = $val["payTime"] ? date("Ymd", $val["payTime"]) : "";
                    $res[$key]["payTypeName"]     = $pay_list[$val["payType"]] ? $pay_list[$val["payType"]] : "（未知）";
                    $res[$key]["amountTime"]      = $payAmountTime;
                    $res[$key]["unpaidAmount"]    = $val["amount"] ? $val["amount"] - $pay_sum : "";
                    $res[$key]["unInvoiceAmount"] = $pay_sum ? $pay_sum - $val["invoiceAmount"] : "";
                    $res[$key]["infoExt"]         = "<div onclick='showExt(" . $val["id"] . ")' style='width: 280px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;'>" . $val["info"] . "</div>";
                    $res[$key]["atta"]            = $val["attachment"] ? (in_array($file[count($file) - 1], array("jpg", "png")) ? "<a href='javascript:;' id='openImg' value='" . $val["attachment"] . "' onclick='openImg(\"" . $val["attachment"] . "\")'>查看图片</a>" : "<a href='http://" . I("server.HTTP_HOST") . $val["attachment"] . "' download='" . $file_name[count($file_name) - 1] . "'>点击下载</a>") : "";
                    $res[$key]["update"]          = $val["updateTime"] ? date("Y-m-d H:i:s", $val["updateTime"]) : "";
                    $res[$key]["opt"]             = "<a href='javascript:;' onclick='doInfo(\"" . $val["id"] . "\")'>查看详情</a>";
                    if (in_array($uid, $this->contractEdit)) {
                        $res[$key]["opt"] .= " <a href='javascript:;' onclick='doEdit(\"" . $val["id"] . "\")'>合同编辑</a>";
                        $res[$key]["opt"] .= " <a href='javascript:;' onclick='addChild(\"" . $val["id"] . "\")'>添加附属</a>";
                    }
                    //子类
                    foreach ($res[$key]["children"] as $k => $v) {
                        if (!$v) {
                            continue;
                        }

                        $file      = explode(".", $v["attachment"]);
                        $file_name = explode("/", $v["attachment"]);
                        $arr       = explode("|", $v["account"]);
                        $account   = "";
                        $i         = 1;
                        $acc_sum   = 0;
                        if (!$arr) {
                            $arr[] = $v["account"];
                        }

                        foreach ($arr as $list) {
                            $list_arr = explode(",", $list);
                            if ($list_arr && $list[0]) {
                                $account .= ($i == 1 ? "" : "<div class='br_line'></div>") . "<span class='br_span1'>" . $list_arr[0] . "</span><span class='br_span2'>" . $list_arr[1] . "元</span>";
                                $acc_sum += $list_arr[1];
                                $i++;
                            }
                        }
                        if ($acc_sum) {
                            $account .= "<div class='br_line'></div><span class='br_span1'>汇总</span><span class='br_span2'>" . $acc_sum . "元</span>";
                        }

                        $arr           = explode("|", $v["payAmountTime"]);
                        $payAmountTime = "";
                        $pay_sum       = 0;
                        if (!$arr) {
                            $arr[] = $v["payAmountTime"];
                        }

                        foreach ($arr as $list) {
                            $list_arr = explode(",", $list);
                            if ($list_arr && $list[0]) {
                                $payAmountTime .= ($i == 1 ? "" : "<div class='br_line'></div>") . "<span class='br_span1'>" . $list_arr[0] . "元</span><span class='br_span2'>" . date("Y-m-d", $list_arr[1]) . "</span>";
                                $pay_sum += $list_arr[0];
                                $i++;
                            }
                        }
                        if ($pay_sum) {
                            $payAmountTime .= "<div class='br_line'></div><span class='br_span1'>汇总</span><span class='br_span2'>" . $pay_sum . "元</span>";
                        }

                        $amount_sum += $v["amount"];
                        $pay_amount_sum += $pay_sum;
                        $invoice_sum += $v["invoiceAmount"];
                        $res[$key]["children"][$k]["partmentName"]    = $partment_list[$v["partment"]] ? $partment_list[$v["partment"]] : "（未知）";
                        $res[$key]["children"][$k]["follow"]          = $admin_list[$v["followAdmin"]]["real"] ? $admin_list[$v["followAdmin"]]["real"] : "（未知）";
                        $res[$key]["children"][$k]["accountStr"]      = $account;
                        $res[$key]["children"][$k]["statusName"]      = $status_list[$v["status"]] ? $status_list[$v["status"]] : "（未知）";
                        $res[$key]["children"][$k]["file"]            = $v["fileTime"] ? date("Ymd", $v["fileTime"]) : "";
                        $res[$key]["children"][$k]["start"]           = $v["startTime"] ? date("Ymd", $v["startTime"]) : "";
                        $res[$key]["children"][$k]["end"]             = $v["endTime"] ? date("Ymd", $v["endTime"]) : "";
                        $res[$key]["children"][$k]["day"]             = $v["endTime"] ? (($v["endTime"] - strtotime(date("Y-m-d")) > 0) ? ($v["endTime"] - strtotime(date("Y-m-d"))) / 86400 : 0) : "";
                        $res[$key]["children"][$k]["principal"]       = $principal_list[$v["principalId"]]["principal_name"] ? $principal_list[$v["principalId"]]["principal_name"] : "（未知）";
                        $res[$key]["children"][$k]["thePayTime"]      = $v["payTime"] ? date("Ymd", $v["payTime"]) : "";
                        $res[$key]["children"][$k]["payTypeName"]     = $pay_list[$v["payType"]] ? $pay_list[$v["payType"]] : "（未知）";
                        $res[$key]["children"][$k]["amountTime"]      = $payAmountTime;
                        $res[$key]["children"][$k]["unpaidAmount"]    = $v["amount"] ? $v["amount"] - $pay_sum : "";
                        $res[$key]["children"][$k]["unInvoiceAmount"] = $pay_sum ? $pay_sum - $v["invoiceAmount"] : "";
                        $res[$key]["children"][$k]["infoExt"]         = "<div onclick='showExt(" . $v["id"] . ")' style='width: 280px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;'>" . $v["info"] . "</div>";
                        $res[$key]["children"][$k]["atta"]            = $v["attachment"] ? (in_array($file[count($file) - 1], array("jpg", "png")) ? "<a href='javascript:;' onclick='openImg(\"" . $v["attachment"] . "\")'>查看图片</a>" : "<a href='http://" . I("server.HTTP_HOST") . $v["attachment"] . "' download='" . $file_name[count($file_name) - 1] . "'>点击下载</a>") : "";
                        $res[$key]["children"][$k]["update"]          = $v["updateTime"] ? date("Y-m-d H:i:s", $v["updateTime"]) : "";
                        $res[$key]["children"][$k]["opt"]             = "<a href='javascript:;' onclick='doInfo(\"" . $v["id"] . "\")'>查看详情</a>";
                        if (in_array($uid, $this->contractEdit)) {
                            $res[$key]["children"][$k]["opt"] .= " <a href='javascript:;' onclick='doEdit(\"" . $v["id"] . "\")'>合同编辑</a>";
                        }
                    }
                }
                $res[] = array(
                    "id"              => "汇总",
                    "amount"          => $amount_sum,
                    "amountTime"      => $pay_amount_sum,
                    "unpaidAmount"    => $amount_sum ? $amount_sum - $pay_amount_sum : "",
                    "invoiceAmount"   => $invoice_sum,
                    "unInvoiceAmount" => $pay_amount_sum ? $pay_amount_sum - $invoice_sum : "",
                );

                $arr = array("rows" => $res ? $res : array(), "results" => 0);
                exit(json_encode($arr));
            }
        } else {
            $uid            = D("Admin")->commonQuery("admin", array("id" => session("admin.uid")));
            $type           = D("Admin")->commonQuery("contract", array("type != '' AND type IS NOT NULL"), 0, 100000, "DISTINCT(TRIM(BOTH '\r\n' FROM type)) AS type");
            $admin          = D("Admin")->commonQuery("admin", array("manager_id" => 8), 0, 1000, "id,real");
            $childNo        = D("Admin")->commonQuery("contract", array("childNo != '' AND childNo IS NOT NULL"), 0, 100000, "DISTINCT(TRIM(BOTH '\r\n' FROM childNo)) AS childNo");
            $contractNo     = D("Admin")->commonQuery("contract", array("contractNo != '' AND contractNo IS NOT NULL"), 0, 100000, "DISTINCT(TRIM(BOTH '\r\n' FROM contractNo)) AS contractNo");
            $option         = array();
            $contractOption = explode(",", $uid["contractOption"]);
            foreach ($contractOption as $v) {
                if ($v !== "" && $v !== null) {
                    $option[$v] = $v;
                }

            }
            if (!$option) {
                $option = array('0' => '0');
            }

            $this->assign("uid", $uid);
            $this->assign("type", $type);
            $this->assign("admin", $admin);
            $this->assign("option", $option);
            $this->assign("childNo", $childNo);
            $this->assign("contractNo", $contractNo);
            $this->display();
        }
    }

    /**
     * 母类合同录入
     */
    public function contractAdd()
    {
        if (IS_POST) {
            $data          = I("");
            $account       = "";
            $payAmountTime = "";
            foreach ($data as $key => $val) {
                if (strpos($key, "accountA") !== false) {
                    $account_id = substr($key, 8);
                    if ($val && $data["accountM" . $account_id]) {
                        $account .= "|" . $val . "," . $data["accountM" . $account_id];
                    }

                }
                if (strpos($key, "payAmountTimeA") !== false) {
                    $payAmountTime_id = substr($key, 14);
                    if ($val && $data["payAmountTimeT" . $payAmountTime_id]) {
                        $payAmountTime .= "|" . $val . "," . strtotime($data["payAmountTimeT" . $payAmountTime_id]);
                    }

                }
            }
            $account && $data["account"]             = trim($account, "|");
            $payAmountTime && $data["payAmountTime"] = trim($payAmountTime, "|");
            $data["parentId"]                        = 0;
            $data["fileTime"]                        = $data["fileTime"] ? strtotime($data["fileTime"]) : 0;
            $data["startTime"]                       = $data["startTime"] ? strtotime($data["startTime"]) : 0;
            $data["endTime"]                         = $data["endTime"] ? strtotime($data["endTime"]) : 0;
            $data["payTime"]                         = $data["payTime"] ? strtotime($data["payTime"]) : 0;
            $data["createTime"]                      = $data["updateTime"]                      = time();
            $res                                     = D("admin")->commonAdd("contract", $data);
            if ($res) {
                $str   = "";
                $files = file_upload_all("contract/" . $res);
                if (is_array($files)) {
                    D("Admin")->commonExecute("contract", array("id" => $res), array("attachment" => "/Uploads/" . $files["file"]["savepath"] . $files["file"]["savename"]));
                }
                $status = array(
                    0 => "生效",
                    1 => "失效",
                    2 => "空号",
                    3 => "作废",
                );
                $payType = array(
                    1 => "日结",
                    2 => "月结",
                    3 => "预付",
                    4 => "垫付",
                    5 => "分期",
                );

                foreach ($data as $k => $v) {
                    if ($k == "partment") {
                        $str .= "，部门为“" . ($v == 1 ? "发行1部" : "发行2部") . "”";
                    }

                    if ($k == "company") {
                        $str .= "，签约单位为“" . $v . "”";
                    }

                    if ($k == "contract") {
                        $str .= "，合同名称为“" . $v . "”";
                    }

                    if ($k == "contractNo") {
                        $str .= "，合同编号为“" . $v . "”";
                    }

                    if ($k == "game") {
                        $str .= "，游戏名称为“" . $v . "”";
                    }

                    if ($k == "type") {
                        $str .= "，合同类别为“" . $v . "”";
                    }

                    if ($k == "account" && $v) {
                        $account_arr = explode("|", $v);
                        if (!$account_arr) {
                            $account_arr[] = $v;
                        }

                        foreach ($account_arr as $list) {
                            $list_arr = explode(",", $list);
                            $str .= "，充值账号“" . $list_arr[0] . "”，充值金额“" . ($list_arr[1] ? $list_arr[1] : "0") . "”";
                        }
                    }
                    if ($k == "info") {
                        $str .= "，主要条款为“" . $v . "”";
                    }

                    if ($k == "status") {
                        $str .= "，效果为“" . $status[$v] . "”";
                    }

                    if ($k == "payType") {
                        $str .= "，结算方式为“" . $payType[$v] . "”";
                    }

                    if ($k == "fileTime" && $v) {
                        $str .= "，签订日期为“" . date("Y-m-d", $v) . "”";
                    }

                    if ($k == "startTime" && $v) {
                        $str .= "，生效日期为“" . date("Y-m-d", $v) . "”";
                    }

                    if ($k == "endTime" && $v) {
                        $str .= "，终止日期为“" . date("Y-m-d", $v) . "”";
                    }

                    if ($k == "principalId") {
                        if ($v) {
                            $principal = D("Admin")->commonQuery("principal", array("id" => $v), 0, 1, "principal_name");
                            $str .= "，签订人为“" . $principal["principal_name"] . "”";
                        }
                    }
                    if ($k == "followAdmin") {
                        if ($v) {
                            $follow = D("Admin")->commonQuery("admin", array("id" => $v), 0, 1, "real");
                            $str .= "，跟进人为“" . $follow["real"] . "”";
                        }
                    }
                    if ($k == "amount") {
                        $str .= "，总金额为“" . $v . "”";
                    }

                    if ($k == "payAmountTime" && $v) {
                        $amountTime_arr = explode("|", $v);
                        if (!$amountTime_arr) {
                            $amountTime_arr[] = $v;
                        }

                        foreach ($amountTime_arr as $list) {
                            $list_arr = explode(",", $list);
                            $str .= "，支付金额“" . $list_arr[0] . "”，支付时间“" . ($list_arr[1] ? date("Y-m-d", $list_arr[1]) : "") . "”";
                        }
                    }
                    if ($k == "receipt" && $v) {
                        $str .= "，票据号为“" . $v . "”";
                    }

                    if ($k == "invoiceAmount") {
                        $str .= "，收到发票金额为“" . $v . "”";
                    }

                    if ($k == "ext" && $v) {
                        $str .= "，备注为“" . $v . "”";
                    }

                }

                bgLog(4, session("admin.realname") . "添加了合同：" . trim($str, "，"));

                exit(json_encode(array("code" => true)));
//                $this->success("合同录入成功！");
            } else {
                exit(json_encode(array("code" => false)));
//                $this->error("合同录入失败！");
            }
        } else {
            $type      = D("Admin")->commonQuery("contract", array("type != '' AND type IS NOT NULL"), 0, 100000, "DISTINCT(type) AS type");
            $follow    = D("Admin")->commonQuery("admin", array("manager_id" => 8), 0, 1000, "id,real");
            $principal = D("Admin")->commonQuery("principal", array(), 0, 1000, "id,principal_name");
            $this->assign("type", $type);
            $this->assign("follow", $follow);
            $this->assign("principal", $principal);
            if (IS_AJAX) {
                $response = $this->fetch();
                $this->ajaxReturn(array("status" => 1, "_html" => $response));
            } else {
                $this->display();
            }
        }
    }

    /**
     * 子类合同录入
     */
    public function contractChildAdd()
    {
        if (IS_POST) {
            $data          = I("");
            $account       = "";
            $payAmountTime = "";
            foreach ($data as $key => $val) {
                if (strpos($key, "accountA") !== false) {
                    $account_id = substr($key, 8);
                    if ($val && $data["accountM" . $account_id]) {
                        $account .= "|" . $val . "," . $data["accountM" . $account_id];
                    }

                }
                if (strpos($key, "payAmountTimeA") !== false) {
                    $payAmountTime_id = substr($key, 14);
                    if ($val && $data["payAmountTimeT" . $payAmountTime_id]) {
                        $payAmountTime .= "|" . $val . "," . strtotime($data["payAmountTimeT" . $payAmountTime_id]);
                    }

                }
            }
            $account && $data["account"]             = trim($account, "|");
            $payAmountTime && $data["payAmountTime"] = trim($payAmountTime, "|");
            $data["fileTime"]                        = $data["fileTime"] ? strtotime($data["fileTime"]) : 0;
            $data["startTime"]                       = $data["startTime"] ? strtotime($data["startTime"]) : 0;
            $data["endTime"]                         = $data["endTime"] ? strtotime($data["endTime"]) : 0;
            $data["payTime"]                         = $data["payTime"] ? strtotime($data["payTime"]) : 0;
            $data["createTime"]                      = $data["updateTime"]                      = time();
            $res                                     = D("admin")->commonAdd("contract", $data);
            if ($res !== false) {
                $str   = "";
                $files = file_upload_all("contract/" . $res);
                if (is_array($files)) {
                    D("Admin")->commonExecute("contract", array("id" => $res), array("attachment" => "/Uploads/" . $files["file"]["savepath"] . $files["file"]["savename"]));
                }
                $status = array(
                    0 => "生效",
                    1 => "失效",
                    2 => "空号",
                    3 => "作废",
                );
                $payType = array(
                    1 => "日结",
                    2 => "月结",
                    3 => "预付",
                    4 => "垫付",
                    5 => "分期",
                );

                foreach ($data as $k => $v) {
                    if ($k == "partment") {
                        $str .= "，部门为“" . ($v == 1 ? "发行1部" : "发行2部") . "”";
                    }

                    if ($k == "company") {
                        $str .= "，签约单位为“" . $v . "”";
                    }

                    if ($k == "contract") {
                        $str .= "，合同名称为“" . $v . "”";
                    }

                    if ($k == "contractNo") {
                        $str .= "，合同编号为“" . $v . "”";
                    }

                    if ($k == "childNo") {
                        $str .= "，信息/签或编号为“" . $v . "”";
                    }

                    if ($k == "game") {
                        $str .= "，游戏名称为“" . $v . "”";
                    }

                    if ($k == "type") {
                        $str .= "，合同类别为“" . $v . "”";
                    }

                    if ($k == "account" && $v) {
                        $account_arr = explode("|", $v);
                        if (!$account_arr) {
                            $account_arr[] = $v;
                        }

                        foreach ($account_arr as $list) {
                            $list_arr = explode(",", $list);
                            $str .= "，充值账号“" . $list_arr[0] . "”，充值金额“" . ($list_arr[1] ? $list_arr[1] : "0") . "”";
                        }
                    }
                    if ($k == "info") {
                        $str .= "，主要条款为“" . $v . "”";
                    }

                    if ($k == "status") {
                        $str .= "，效果为“" . $status[$v] . "”";
                    }

                    if ($k == "payType") {
                        $str .= "，结算方式为“" . $payType[$v] . "”";
                    }

                    if ($k == "fileTime" && $v) {
                        $str .= "，签订日期为“" . date("Y-m-d", $v) . "”";
                    }

                    if ($k == "startTime" && $v) {
                        $str .= "，生效日期为“" . date("Y-m-d", $v) . "”";
                    }

                    if ($k == "endTime" && $v) {
                        $str .= "，终止日期为“" . date("Y-m-d", $v) . "”";
                    }

                    if ($k == "principalId") {
                        if ($v) {
                            $principal = D("Admin")->commonQuery("principal", array("id" => $v), 0, 1, "principal_name");
                            $str .= "，签订人为“" . $principal["principal_name"] . "”";
                        }
                    }
                    if ($k == "followAdmin") {
                        if ($v) {
                            $follow = D("Admin")->commonQuery("admin", array("id" => $v), 0, 1, "real");
                            $str .= "，跟进人为“" . $follow["real"] . "”";
                        }
                    }
                    if ($k == "amount") {
                        $str .= "，总金额为“" . $v . "”";
                    }

                    if ($k == "payAmountTime" && $v) {
                        $amountTime_arr = explode("|", $v);
                        if (!$amountTime_arr) {
                            $amountTime_arr[] = $v;
                        }

                        foreach ($amountTime_arr as $list) {
                            $list_arr = explode(",", $list);
                            $str .= "，支付金额“" . $list_arr[0] . "”，支付时间“" . ($list_arr[1] ? date("Y-m-d", $list_arr[1]) : "") . "”";
                        }
                    }
                    if ($k == "receipt" && $v) {
                        $str .= "，票据号为“" . $v . "”";
                    }

                    if ($k == "invoiceAmount") {
                        $str .= "，收到发票金额为“" . $v . "”";
                    }

                    if ($k == "ext" && $v) {
                        $str .= "，备注为“" . $v . "”";
                    }

                }

                bgLog(4, session("admin.realname") . "添加了附属合同：" . trim($str, "，"));

                exit(json_encode(array("code" => true)));
//                $this->success("添加附属成功！");
            } else {
                exit(json_encode(array("code" => false)));
//                $this->error("添加附属失败！");
            }
        } else {
            $id = I("id");
            if (!$id) {
                if (IS_AJAX) {
                    $this->ajaxReturn(array("status" => 1, "_html" => "数据异常！"));
                } else {
                    $this->error("数据异常！");
                }
            }
            $info      = D("Admin")->commonQuery("contract", array("id" => $id), 0, 1);
            $type      = D("Admin")->commonQuery("contract", array("type != '' AND type IS NOT NULL"), 0, 100000, "DISTINCT(type) AS type");
            $follow    = D("Admin")->commonQuery("admin", array("manager_id" => 8), 0, 1000, "id,real");
            $principal = D("Admin")->commonQuery("principal", array(), 0, 1000, "id,principal_name");
            $this->assign("type", $type);
            $this->assign("follow", $follow);
            $this->assign("info", $info);
            $this->assign("principal", $principal);
            if (IS_AJAX) {
                $response = $this->fetch();
                $this->ajaxReturn(array("status" => 1, "_html" => $response));
            } else {
                $this->display();
            }
        }
    }

    /**
     * 合同编辑
     */
    public function contractEdit()
    {
        if (IS_POST) {
            $data          = I("");
            $account       = "";
            $payAmountTime = "";
            foreach ($data as $key => $val) {
                if (strpos($key, "accountA") !== false) {
                    $account_id = substr($key, 8);
                    if ($val && $data["accountM" . $account_id]) {
                        $account .= "|" . $val . "," . $data["accountM" . $account_id];
                    }

                }
                if (strpos($key, "payAmountTimeA") !== false) {
                    $payAmountTime_id = substr($key, 14);
                    if ($val && $data["payAmountTimeT" . $payAmountTime_id]) {
                        $payAmountTime .= "|" . $val . "," . strtotime($data["payAmountTimeT" . $payAmountTime_id]);
                    }

                }
            }
            $account && $data["account"]             = trim($account, "|");
            $payAmountTime && $data["payAmountTime"] = trim($payAmountTime, "|");
            $data["fileTime"]                        = $data["fileTime"] ? strtotime($data["fileTime"]) : 0;
            $data["startTime"]                       = $data["startTime"] ? strtotime($data["startTime"]) : 0;
            $data["endTime"]                         = $data["endTime"] ? strtotime($data["endTime"]) : 0;
            $data["payTime"]                         = $data["payTime"] ? strtotime($data["payTime"]) : 0;
            $data["updateTime"]                      = time();
            $contract                                = D("Admin")->commonQuery("contract", array("id" => $data["id"]));
            $res                                     = D("admin")->commonExecute("contract", array("id" => $data["id"]), $data);
            if ($res !== false) {
                $str   = "";
                $files = file_upload_all("contract/" . $data["id"]);
                if (is_array($files)) {
                    D("Admin")->commonExecute("contract", array("id" => $data["id"]), array("attachment" => "/Uploads/" . $files["file"]["savepath"] . $files["file"]["savename"]));
                }

                $status = array(
                    0 => "生效",
                    1 => "失效",
                    2 => "空号",
                    3 => "作废",
                );
                $payType = array(
                    1 => "日结",
                    2 => "月结",
                    3 => "预付",
                    4 => "垫付",
                    5 => "分期",
                );

                foreach ($data as $k => $v) {
                    if ($v == $contract[$k]) {
                        continue;
                    }

                    if ($k == "partment") {
                        $str .= "，部门由“" . ($contract[$k] == 1 ? "发行1部" : "发行2部") . "”改为“" . ($v == 1 ? "发行1部" : "发行2部") . "”";
                    }

                    if ($k == "company") {
                        $str .= "，签约单位由“" . $contract[$k] . "”改为“" . $v . "”";
                    }

                    if ($k == "contract") {
                        $str .= "，合同名称由“" . $contract[$k] . "”改为“" . $v . "”";
                    }

                    if ($k == "contractNo") {
                        $str .= "，合同编号由“" . $contract[$k] . "”改为“" . $v . "”";
                    }

                    if ($k == "childNo") {
                        $str .= "，信息/签或编号由“" . $contract[$k] . "”改为“" . $v . "”";
                    }

                    if ($k == "game") {
                        $str .= "，游戏名称由“" . $contract[$k] . "”改为“" . $v . "”";
                    }

                    if ($k == "type") {
                        $str .= "，合同类别由“" . $contract[$k] . "”改为“" . $v . "”";
                    }

                    if ($k == "account" && $v) {
                        $old_str     = "";
                        $new_str     = "";
                        $account_arr = explode("|", $contract[$k]);
                        if (!$account_arr) {
                            $account_arr[] = $contract[$k];
                        }

                        foreach ($account_arr as $list) {
                            $list_arr = explode(",", $list);
                            $old_str .= "，充值账号“" . $list_arr[0] . "”，充值金额“" . ($list_arr[1] ? $list_arr[1] : "0") . "”";
                        }
                        $account_arr = explode("|", $v);
                        if (!$account_arr) {
                            $account_arr[] = $v;
                        }

                        foreach ($account_arr as $list) {
                            $list_arr = explode(",", $list);
                            $new_str .= "，充值账号“" . $list_arr[0] . "”，充值金额“" . ($list_arr[1] ? $list_arr[1] : "0") . "”";
                        }
                        $str .= "，账号方面的变化为：由" . trim($old_str, "，") . "改为" . trim($new_str, "，");
                    }
                    if ($k == "info") {
                        $str .= "，主要条款由“" . $contract[$k] . "”改为“" . $v . "”";
                    }

                    if ($k == "status") {
                        $str .= "，效果由“" . $status[$contract[$k]] . "”改为“" . $status[$v] . "”";
                    }

                    if ($k == "payType") {
                        $str .= "，结算方式由“" . $payType[$contract[$k]] . "”改为“" . $payType[$v] . "”";
                    }

                    if ($k == "fileTime" && $v) {
                        $str .= "，签订日期由“" . date("Y-m-d", $contract[$k]) . "”改为“" . date("Y-m-d", $v) . "”";
                    }

                    if ($k == "startTime" && $v) {
                        $str .= "，生效日期由“" . date("Y-m-d", $contract[$k]) . "”改为“" . date("Y-m-d", $v) . "”";
                    }

                    if ($k == "endTime" && $v) {
                        $str .= "，终止日期由“" . date("Y-m-d", $contract[$k]) . "”改为“" . date("Y-m-d", $v) . "”";
                    }

                    if ($k == "principalId") {
                        if ($v) {
                            $principal     = D("Admin")->commonQuery("principal", array("id" => $v), 0, 1, "principal_name");
                            $old_principal = D("Admin")->commonQuery("principal", array("id" => $contract[$k]), 0, 1, "principal_name");
                            $str .= "，签订人由“" . $old_principal["principal_name"] . "”改为“" . $principal["principal_name"] . "”";
                        }
                    }
                    if ($k == "followAdmin") {
                        if ($v) {
                            $follow     = D("Admin")->commonQuery("admin", array("id" => $v), 0, 1, "real");
                            $old_follow = D("Admin")->commonQuery("admin", array("id" => $contract[$k]), 0, 1, "real");
                            $str .= "，跟进人由“" . $old_follow["real"] . "”改为“" . $follow["real"] . "”";
                        }
                    }
                    if ($k == "amount") {
                        $str .= "，总金额由“" . $contract[$k] . "”改为“" . $v . "”";
                    }

                    if ($k == "payAmountTime" && $v) {
                        $old_str        = "";
                        $new_str        = "";
                        $amountTime_arr = explode("|", $contract[$k]);
                        if (!$amountTime_arr) {
                            $amountTime_arr[] = $contract[$k];
                        }

                        foreach ($amountTime_arr as $list) {
                            $list_arr = explode(",", $list);
                            $old_str .= "，支付金额“" . $list_arr[0] . "”，支付时间“" . ($list_arr[1] ? date("Y-m-d", $list_arr[1]) : "") . "”";
                        }
                        $amountTime_arr = explode("|", $v);
                        if (!$amountTime_arr) {
                            $amountTime_arr[] = $v;
                        }

                        foreach ($amountTime_arr as $list) {
                            $list_arr = explode(",", $list);
                            $new_str .= "，支付金额“" . $list_arr[0] . "”，支付时间“" . ($list_arr[1] ? date("Y-m-d", $list_arr[1]) : "") . "”";
                        }
                        $str .= "，金额支付方面的变化为：由" . trim($old_str, "，") . "改为" . trim($new_str, "，");
                    }
                    if ($k == "receipt" && $v) {
                        $str .= "，票据号由“" . $contract[$k] . "”改为“" . $v . "”";
                    }

                    if ($k == "invoiceAmount") {
                        $str .= "，收到发票金额由“" . $contract[$k] . "”改为“" . $v . "”";
                    }

                    if ($k == "ext" && $v) {
                        $str .= "，备注由“" . $contract[$k] . "”改为“" . $v . "”";
                    }

                }

                bgLog(3, session("admin.realname") . "修改了合同：合同ID为：" . $data["id"] . "，合同编号为：“" . $contract["contractNo"] . "”" . "，信息/签或编号为：“" . ($contract["childNo"] ? $contract["childNo"] : "（无）") . "”，合同改动为：" . trim($str, "，"));

                exit(json_encode(array("code" => true)));
//                $this->success("合同编辑成功！");
            } else {
                exit(json_encode(array("code" => false)));
//                $this->error("合同编辑失败！");
            }
        } else {
            $id = I("id");
            if (!$id) {
                if (IS_AJAX) {
                    $this->ajaxReturn(array("status" => 1, "_html" => "数据异常！"));
                } else {
                    $this->error("数据异常！");
                }
            }
            $info              = D("Admin")->commonQuery("contract", array("id" => $id), 0, 1);
            $type              = D("Admin")->commonQuery("contract", array("type != '' AND type IS NOT NULL"), 0, 100000, "DISTINCT(type) AS type");
            $follow            = D("Admin")->commonQuery("admin", array("manager_id" => 8), 0, 1000, "id,real");
            $principal         = D("Admin")->commonQuery("principal", array(), 0, 1000, "id,principal_name");
            $info["fileTime"]  = $info["fileTime"] ? date("Y-m-d", $info["fileTime"]) : "";
            $info["startTime"] = $info["startTime"] ? date("Y-m-d", $info["startTime"]) : "";
            $info["endTime"]   = $info["endTime"] ? date("Y-m-d", $info["endTime"]) : "";
            $info["payTime"]   = $info["payTime"] ? date("Y-m-d", $info["payTime"]) : "";
            $account           = array();
            $payAmountTime     = array();
            if ($info["account"]) {
                $arr = explode("|", $info["account"]);
                if (!$arr) {
                    $arr[] = $info["account"];
                }

                foreach ($arr as $list) {
                    $list_arr = explode(",", $list);
                    if ($list_arr[0]) {
                        $account[] = array("accountA" => $list_arr[0], "accountM" => $list_arr[1]);
                    }

                }
            }
            if ($info["payAmountTime"]) {
                $arr = explode("|", $info["payAmountTime"]);
                if (!$arr) {
                    $arr[] = $info["payAmountTime"];
                }

                foreach ($arr as $list) {
                    $list_arr = explode(",", $list);
                    if ($list_arr[0]) {
                        $payAmountTime[] = array("payAmountTimeA" => $list_arr[0], "payAmountTimeT" => ($list_arr[1] ? date("Y-m-d", $list_arr[1]) : ""));
                    }

                }
            }
            $account_num       = count($account) + 1;
            $payAmountTime_num = count($payAmountTime) + 1;

            $this->assign("type", $type);
            $this->assign("info", $info);
            $this->assign("follow", $follow);
            $this->assign("principal", $principal);
            $this->assign("account", $account);
            $this->assign("payAmountTime", $payAmountTime);
            $this->assign("account_num", $account_num);
            $this->assign("payAmountTime_num", $payAmountTime_num);
            if (IS_AJAX) {
                $response = $this->fetch();
                $this->ajaxReturn(array("status" => 1, "_html" => $response));
            } else {
                $this->display();
            }
        }
    }

    /**
     * 合同详情
     */
    public function contractInfo()
    {
        $id = I("id");
        if (!$id) {
            if (IS_AJAX) {
                $this->ajaxReturn(array("status" => 1, "_html" => "数据异常！"));
            } else {
                $this->error("数据异常！");
            }
        }
        $info               = D("Admin")->commonQuery("contract", array("id" => $id), 0, 1);
        $follow             = D("Admin")->commonQuery("admin", array("id" => $info["followAdmin"]));
        $principal          = D("Admin")->commonQuery("principal", array("id" => $info["principalId"]));
        $info["admin"]      = $follow["real"];
        $info["principal"]  = $principal["principal_name"];
        $info["fileTime"]   = $info["fileTime"] ? date("Y-m-d", $info["fileTime"]) : "";
        $info["startTime"]  = $info["startTime"] ? date("Y-m-d", $info["startTime"]) : "";
        $info["endTime"]    = $info["endTime"] ? date("Y-m-d", $info["endTime"]) : "";
        $info["payTime"]    = $info["payTime"] ? date("Y-m-d", $info["payTime"]) : "";
        $info["updateTime"] = $info["updateTime"] ? date("Y-m-d", $info["updateTime"]) : "";
        $account            = array();
        $payAmountTime      = array();
        $account_sum        = 0;
        $payAmountTime_sum  = 0;
        if ($info["account"]) {
            $arr = explode("|", $info["account"]);
            if (!$arr) {
                $arr[] = $info["account"];
            }

            foreach ($arr as $list) {
                $list_arr = explode(",", $list);
                if ($list_arr[0]) {
                    $account[] = array("accountA" => $list_arr[0], "accountM" => $list_arr[1]);
                    $account_sum += $list_arr[1];
                }
            }
        }
        if ($info["payAmountTime"]) {
            $arr = explode("|", $info["payAmountTime"]);
            if (!$arr) {
                $arr[] = $info["payAmountTime"];
            }

            foreach ($arr as $list) {
                $list_arr = explode(",", $list);
                if ($list_arr[0]) {
                    $payAmountTime[] = array("payAmountTimeA" => $list_arr[0], "payAmountTimeT" => ($list_arr[1] ? date("Y-m-d", $list_arr[1]) : ""));
                    $payAmountTime_sum += $list_arr[0];
                }
            }
        }

        $this->assign("info", $info);
        $this->assign("account", $account);
        $this->assign("payAmountTime", $payAmountTime);
        $this->assign("account_sum", $account_sum);
        $this->assign("payAmountTime_sum", $payAmountTime_sum);
        if (IS_AJAX) {
            $response = $this->fetch();
            $this->ajaxReturn(array("status" => 1, "_html" => $response));
        } else {
            $this->display();
        }
    }

    /**
     * 合同记录
     */
    public function contractLog()
    {
        if (IS_POST) {
            $data     = I();
            $start    = $data['start'] ? $data['start'] : 0;
            $pageSize = $data['limit'] ? $data['limit'] : 30;
            if ($data["startDate"] && $data["endDate"]) {
                $map["create_time"] = array("BETWEEN", array($data["startDate"], date("Y-m-d", strtotime($data["endDate"] . " +1 day"))));
            } elseif ($data["startDate"]) {
                $map["create_time"] = array("GT", $data["startDate"]);
            } elseif ($data["endDate"]) {
                $map["create_time"] = array("LT", date("Y-m-d", strtotime($data["endDate"] . " +1 day")));
            }

            $count = D("Admin")->getContractLogCount($map);
            $res   = D("Admin")->getContractLog($map, $start, $pageSize);
            $type  = array(
                1 => "登陆",
                2 => "删除",
                3 => "修改",
                4 => "新增",
            );
            foreach ($res as $key => $val) {
                $res[$key]["typeName"]  = $type[$val["type"]];
                $res[$key]["recordExt"] = "<div onclick='showExt(" . $val["id"] . ")' style='width: 1000px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;'>" . $val["record"] . "</div>";
            }
            exit(json_encode(array("rows" => $res ? $res : array(), "results" => $count)));
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
     * 计划消耗
     */
    public function financeCost()
    {
        if (IS_POST) {
            $data = I();
            if ($data["startDate"] && $data["endDate"]) {
                $map["date"] = array("BETWEEN", array($data["startDate"], $data["endDate"]));
            } elseif ($data["startDate"]) {
                $map["date"] = array("EGT", $data["startDate"]);
            } elseif ($data["endDate"]) {
                $map["date"] = array("ELT", $data["endDate"]);
            }
            if ($data["companyName"]) {
                $map["companyName"] = $data["companyName"];
            }

            if ($data["mainbody"]) {
                $map["mainbody"] = $data["mainbody"];
            }

            if ($data["proxyName"]) {
                $map["proxyName"] = $data["proxyName"];
            }

            if ($data["account"]) {
                $map["account"] = array("LIKE", "%" . $data["account"] . "%");
            }

            //财务可以看全部数据，其他人只看自己
            if (!in_array(session('admin.role_id'), array(1, 13, 14, 17, 25, 32, 33))) {
//                $map["createUser"] = session("admin.realname");
                $admin            = D("Admin")->commonQuery("admin", array("id" => session("admin.uid")));
                $map["accountId"] = array("IN", $admin["backstage_account_id"] ? $admin["backstage_account_id"] : "0");
            }
            if ($data["export"] == 1) {
                $res = D("Admin")->commonQuery("finance_cost", $map, 0, 99999);
                import("Org.Util.PHPExcel", LIB_PATH, ".php");
                error_reporting(E_ALL);
//                date_default_timezone_set("Europe/London");
                date_default_timezone_set("PRC");
                $objPHPExcel = new \PHPExcel();
                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue("A1", "日期")
                    ->setCellValue("B1", "主体")
                    ->setCellValue("C1", "代理")
                    ->setCellValue("D1", "渠道")
                    ->setCellValue("E1", "账号")
                    ->setCellValue("F1", "游戏")
                    ->setCellValue("G1", "母包")
                    ->setCellValue("H1", "消耗")
                    ->setCellValue("I1", "折返类型")
                    ->setCellValue("J1", "折返比率")
                    ->setCellValue("K1", "实际消耗");
                $objPHPExcel->getActiveSheet()->getStyle("A1:K1")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $num = 1;
                foreach ($res as $val) {
                    $num++;
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit("A" . $num, $val["date"], \PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit("B" . $num, $val["mainbody"], \PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit("C" . $num, $val["proxyName"], \PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit("D" . $num, $val["companyName"], \PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit("E" . $num, $val["account"], \PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit("F" . $num, $val["gameName"], \PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit("G" . $num, $val["agentName"], \PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit("H" . $num, $val["cost"], \PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit("I" . $num, $val["rebateType"] ? "返现" : "返点", \PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit("J" . $num, $val["rebate"] . "%", \PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit("K" . $num, $val["realCost"], \PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->getStyle("A" . $num . ":K" . $num)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
                }
                $objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(12);
                $objPHPExcel->getActiveSheet()->getColumnDimension("B")->setWidth(30);
                $objPHPExcel->getActiveSheet()->getColumnDimension("C")->setWidth(35);
                $objPHPExcel->getActiveSheet()->getColumnDimension("D")->setWidth(12);
                $objPHPExcel->getActiveSheet()->getColumnDimension("E")->setWidth(30);
                $objPHPExcel->getActiveSheet()->getColumnDimension("F")->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension("G")->setWidth(25);
                $objPHPExcel->getActiveSheet()->getColumnDimension("H")->setWidth(10);
                $objPHPExcel->getActiveSheet()->getColumnDimension("I")->setWidth(8);
                $objPHPExcel->getActiveSheet()->getColumnDimension("J")->setWidth(8);
                $objPHPExcel->getActiveSheet()->getColumnDimension("K")->setWidth(10);
                $objPHPExcel->getActiveSheet()->setTitle("User");
                $objPHPExcel->setActiveSheetIndex(0);
                header("Content-Type: application/vnd.ms-excel");
                header("Content-Disposition: attachment;filename=计划消耗.xls");
                header("Cache-Control: max-age=0");
                $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel5");
                $objWriter->save("php://output");
                exit;
            } else {
                $start    = $data["start"] ? $data["start"] : 0;
                $pageSize = $data["limit"] ? $data["limit"] : 30;
                $res      = D("Admin")->getBuiList("finance_cost", $map, $start, $pageSize);
                $sum      = D("Admin")->getFinanceCostSum($map);
                if ($res) {
                    foreach ($res["list"] as $v) {
                        $list                   = $v;
                        $list["rebateName"]     = "<span id='rebate_" . $v["id"] . "'>" . $v["rebate"] . "%" . "</span>";
                        $list["rebateTypeName"] = $v["rebateType"] ? "返现" : "返点";
                        if ($v["twoExamine"]) {
                            $list["status"]      = "<span id='examine_" . $v["id"] . "'><span style='color:green;'>财务审核</span></span>";
                            $list["examineTime"] = "<span id='time_" . $v["id"] . "'>" . date("Y-m-d H:i:s", $v["twoExamineTime"]) . "</span>";
                            $list["opt"]         = "<span id='opt_" . $v["id"] . "'>（无）</span>";
                        } elseif ($v["oneExamine"]) {
                            $list["status"]      = "<span id='examine_" . $v["id"] . "'><a href='javascript:;' onclick='doExamine(" . $v["id"] . ",\"" . $v["account"] . "\",\"" . $v["date"] . "\")'><span style='color:blue;'>投放审核</span></a></span>";
                            $list["examineTime"] = "<span id='time_" . $v["id"] . "'>" . date("Y-m-d H:i:s", $v["oneExamineTime"]) . "</span>";
                            if (in_array(session("admin.role_id"), array(17, 25))) {
                                $list["opt"] = "<span id='opt_" . $v["id"] . "'><a href='javascript:;' onclick='doEdit(" . $v["id"] . ")'>编辑</a>&nbsp;<a href='javascript:;' class='btn-del' data-id='" . $v["id"] . "' data-account='" . $v["account"] . "' data-date='" . $v["date"] . "'>删除</a></span>";
                            } else {
                                $list["opt"] = "<a href='javascript:;' class='btn-del' data-id='" . $v["id"] . "' data-account='" . $v["account"] . "' data-date='" . $v["date"] . "'>删除</a></span>";
                            }
                        } else {
                            $list["status"]      = "<span id='examine_" . $v["id"] . "'><a href='javascript:;' onclick='doExamine(" . $v["id"] . ",\"" . $v["account"] . "\",\"" . $v["date"] . "\")'><span style='color:red;'>未审核</span></a></span>";
                            $list["examineTime"] = "<span id='time_" . $v["id"] . "'>（无）</span>";
                            if (in_array(session("admin.role_id"), array(17, 25))) {
                                $list["opt"] = "<span id='opt_" . $v["id"] . "'><a href='javascript:;' onclick='doEdit(" . $v["id"] . ")'>编辑</a>&nbsp;<a href='javascript:;' class='btn-del' data-id='" . $v["id"] . "' data-account='" . $v["account"] . "' data-date='" . $v["date"] . "'>删除</a></span>";
                            } else {
                                $list["opt"] = "<a href='javascript:;' class='btn-del' data-id='" . $v["id"] . "' data-account='" . $v["account"] . "' data-date='" . $v["date"] . "'>删除</a></span>";
                            }
                        }

                        $rows[] = $list;
                    }
                } else {
                    $rows = array();
                }
                $arr = array("rows" => $rows, "results" => $res ? $res["count"] : 0, "summary" => $sum[0]);
                exit(json_encode($arr));
            }
        } else {
            $proxy      = D("Admin")->commonQuery("proxy", array(), 0, 9999999);
            $mainbody   = D("Admin")->commonQuery("mainbody", array(), 0, 9999999);
            $advteruser = D("Admin")->commonQuery("advteruser", array(), 0, 9999999);
            $this->assign("proxy", $proxy);
            $this->assign("mainbody", $mainbody);
            $this->assign("advteruser", $advteruser);
            $this->display();
        }
    }

    /**
     * 计划消耗编辑
     */
    public function financeCostEdit()
    {
        if (IS_POST) {
            $data = I("");
            if (!$data["id"] || !$data["cost"] || !$data["realCost"] || !$data["rebate"]) {
                $this->ajaxReturn(array("Result" => false, "Msg" => "数据错误！"));
            }

            $data["oneExamineTime"] = 0;
            $res                    = D("admin")->commonExecute("finance_cost", array("id" => $data["id"]), $data);
            if (!$res) {
                $this->ajaxReturn(array("Result" => false, "Msg" => "编辑失败！"));
            }

            $this->ajaxReturn(array("Result" => true, "Msg" => "编辑成功！"));
        } else {
            $id = I("id");
            if (!$id) {
                $this->ajaxReturn(array("Result" => false, "Msg" => "ID错误！"));
            }

            $info = D("Admin")->commonQuery("finance_cost", array("id" => $id), 0, 1);
            if (!$info) {
                $this->ajaxReturn(array("Result" => false, "Msg" => "数据异常！"));
            }

            $this->assign("info", $info);
            $response = $this->fetch();
            $this->ajaxReturn(array("Result" => true, "Html" => $response));
        }
    }

    /**
     * 计划消耗删除
     */
    public function financeCostDelete()
    {
        $id = I("id");
        if (!$id) {
            $this->ajaxReturn(array("Result" => false, "Msg" => "ID错误！"));
        }

        $res = D("Admin")->commonDelete("finance_cost", array("id" => $id));
        if (!$res) {
            $this->ajaxReturn(array("Result" => false, "Msg" => "删除失败！"));
        }

        $this->ajaxReturn(array("Result" => true, "Msg" => "删除成功！"));
    }

    /**
     * 计划消耗录入
     */
    public function financeCostAdd()
    {
        if (IS_POST) {
            if (!$_FILES["costFile"]["name"]) {
                $this->error("没有传入Excel");
            }
            //文件上传
            $file_info = excel_file_upload("Cost");
            if ($file_info && $file_info != "没有文件被上传！") {
                //获取文件数据并且转数组
                $fileName = "./Uploads/" . $file_info["costFile"]["savepath"] . $file_info["costFile"]["savename"];
                $data     = excel_to_array($fileName);
                if ($data) {
                    $proxy         = getDataList("proxy", "id", C("DB_PREFIX"));
                    $game          = getDataList("game", "id", C("DB_PREFIX_API"));
                    $mainbody      = getDataList("mainbody", "id", C("DB_PREFIX"));
                    $advterUser    = getDataList("advteruser", "company_name", C("DB_PREFIX"));
                    $advterAccount = getDataListForKeys("advter_account", array("account", "advteruserId"), C("DB_PREFIX"));
                    $agent         = getDataList("agent", "agent", C("DB_PREFIX_API"));
                    $agent_id      = getDataList("agent", "id", C("DB_PREFIX_API"), array("agentType" => 1, "pid" => 0));
                    $eventAgent    = getDataList('events', 'agent', C('DB_PREFIX'), array('is_zrl' => 1));

                    $msg   = array();
                    $arr   = array();
                    $param = array();
                    $key   = 1;
                    unset($data[1]); //第一个行为标题，不需要入库
                    foreach ($data as $key => $val) {
                        $val = array_map("trim", $val);
                        if (empty($val[0])) {
                            $this->error("存在空日期！");
                        }
                        if (empty($val[1])) {
                            $this->error("存在空渠道！");
                        }
                        if (empty($val[2])) {
                            $this->error("存在空账号！");
                        }
                        if (empty($val[3])) {
                            $this->error("存在空渠道号！");
                        }
                        if (empty($val[5])) {
                            $this->error("存在空消耗！");
                        }
                        //是否存在错误的日期
                        if (!strtotime($val[0])) {
                            $msg["Date"] .= "," . $val[0];
                            $key = 0;
                        }
                        //是否存在错误渠道
                        if (!array_key_exists($val[1], $advterUser)) {
                            $msg["advter"] .= "," . $val[1];
                            $key = 0;
                        } else {
                            //是否存在错误的账号
                            $advterUserId = $advterUser[$val[1]]["id"];
                            if (!array_key_exists($val[2] . "_" . $advterUserId, $advterAccount)) {
                                $msg["account"] .= "," . $val[2];
                                $key = 0;
                            }
                        }
                        //是否存在错误渠道号
                        if (!array_key_exists($val[3], $agent)) {
                            $msg["agent"] .= "," . $val[3];
                            $key = 0;
                        }
                        if ($key) {
                            $advterUserId = $advterUser[$val[1]]["id"];
                            if ($agent[$val[3]]["pid"] != 0 && $agent[$val[3]]["agentType"] != 1) {
                                $agent_a    = $agent_id[$agent[$val[3]]["pid"]]["agent"];
                                $agent_name = $agent_id[$agent[$val[3]]["pid"]]["agentName"];
                            } else {
                                $agent_a    = $val[3];
                                $agent_name = $agent[$val[3]]["agentName"];
                            }
                            $date = empty(date("Y-m-d", strtotime($val[0]))) ? '' : date('Y-m-d', strtotime($val[0]));
                            if (isset($arr[$agent_a . "_" . $date . "_" . $val[2] . "_" . $val[1]])) {
                                $cost = $arr[$agent_a . "_" . $date . "_" . $val[2] . "_" . $val[1]]["cost"] + $val[5];
                            } else {
                                $cost = $val[5];
                            }
                            if ($advterAccount[$val[2] . "_" . $advterUserId]["rebateType"]) {
                                $realCost  = $cost * (1 - ($advterAccount[$val[2] . "_" . $advterUserId]["rebate"]) / 100);
                                $paramCost = $val[5] * (1 - ($advterAccount[$val[2] . "_" . $advterUserId]["rebate"]) / 100);
                            } else {
                                $realCost  = $cost / (1 + ($advterAccount[$val[2] . "_" . $advterUserId]["rebate"]) / 100);
                                $paramCost = $val[5] / (1 + ($advterAccount[$val[2] . "_" . $advterUserId]["rebate"]) / 100);
                            }
                            $arr[$agent_a . "_" . $date . "_" . $val[2] . "_" . $val[1]] = array(
                                "accountId"   => $advterAccount[$val[2] . "_" . $advterUserId]["id"],
                                "account"     => $val[2],
                                "mainbody"    => $mainbody[$agent[$agent_a]["mainbody_id"]]["mainBody"],
                                "proxyName"   => $proxy[$advterAccount[$val[2] . "_" . $advterUserId]["proxyId"]]["proxyName"],
                                "companyName" => $val[1],
                                "gameId"      => $agent[$agent_a]["game_id"],
                                "gameName"    => $game[$agent[$agent_a]["game_id"]]["gameName"],
                                "agent"       => $agent_a,
                                "agentName"   => $agent_name,
                                "date"        => $date,
                                "cost"        => $cost,
                                "rebateType"  => $advterAccount[$val[2] . "_" . $advterUserId]["rebateType"],
                                "rebate"      => $advterAccount[$val[2] . "_" . $advterUserId]["rebate"],
                                "realCost"    => $realCost,
                                "createTime"  => time(),
                                "createUser"  => session("admin.realname"),
                                "oneExamine"  => 0,
                                "twoExamine"  => 0,
                            );
                            $param[] = array(
                                "costMonth"      => $date,
                                "advter_id"      => $val[4] ? $val[4] : $eventAgent[$val[3]]['id'],
                                "principal"      => session("admin.realname"),
                                "gameType"       => $agent[$val[3]]["gameType"] == 1 ? "安卓" : "ios",
                                "gameName"       => $agent_name,
                                "agent"          => $val[3],
                                "channelAccount" => $val[2],
                                "cost"           => $paramCost,
                                "createTime"     => time(),
                                "media"          => $val[1],
                                "creater"        => session("admin.realname"),
                                "departmentId"   => session("admin.partment"),
                                'game_id'        => $agent[$val[3]]['game_id'],
                            );
                        }
                    }
                    if ($msg || count($msg) > 0) {
                        $errorInfo = "存在错误数据，请修改再上传，错误信息为：";
                        if ($msg["advter"]) {
                            $errorInfo .= " 错误渠道【" . trim($msg["advter"], ",") . "】";
                        }
                        if ($msg["account"]) {
                            $errorInfo .= " 错误账号【" . trim($msg["account"], ",") . "】";
                        }
                        if ($msg["agent"]) {
                            $errorInfo .= " 错误母包【" . trim($msg["agent"], ",") . "】";
                        }
                        unset($arr);
                        $this->error($errorInfo, "", 10);
                    }
                    if ($arr) {
                        foreach ($arr as $list) {
                            $info = D("Admin")->commonQuery("finance_cost", array("date" => $list["date"], "account" => $list["account"], "companyName" => $list["companyName"], "agent" => $list["agent"]), 0, 1);
                            if ($info) {
                                if (!$info["twoExamine"]) {
                                    D("Admin")->commonExecute("finance_cost", array("date" => $list["date"], "account" => $list["account"], "companyName" => $list["companyName"], "agent" => $list["agent"]), $list);
                                }
                            } else {
                                D("Admin")->commonAdd("finance_cost", $list);
                            }
                        }
                    }
                    if ($param) {
                        foreach ($param as $list) {
                            $map = array(
                                "costMonth"      => $list["costMonth"],
                                "agent"          => $list["agent"],
                                "channelAccount" => $list["channelAccount"],
                                "media"          => $list["media"],
                            );
                            if ($list["advter_id"]) {
                                $map["advter_id"] = $list["advter_id"];
                            } else {
                                $map["_string"] = "advter_id IS NULL OR advter_id = ''";
                            }
                            //判断是否已经存在
                            $advter_cost = D("Admin")->commonQuery("advter_cost", $map, 0, 1);
                            if ($advter_cost) {
                                //修改
                                D("Admin")->commonExecute("advter_cost", $map, $list);
                            } else {
                                //插入
                                D("Admin")->commonAdd("advter_cost", $list);
                            }
                        }
                    }
                    @unlink($fileName);
                    $this->success("成本导入成功");
                }
            }
        } else {
            $response = $this->fetch();
            $this->ajaxReturn(array("Result" => true, "Html" => $response));
        }
    }

    /**
     * 通过审核
     */
    public function examineCost()
    {
        $id = I("id");
        if (!$id) {
            $this->ajaxReturn(array("Result" => false, "Msg" => "ID错误！"));
        }

        $info = D("Admin")->commonQuery("finance_cost", array("id" => $id));
        if (!$info) {
            $this->ajaxReturn(array("Result" => false, "Msg" => "无此消耗信息！"));
        }

        if ($info["twoExamine"]) {
            $this->ajaxReturn(array("Result" => false, "Msg" => "该消耗已被财务审核，无法再次审核！"));
        }

        $time = time();
        if (session("admin.role_id") == 17) {
            D("Admin")->commonExecute("finance_cost", array("id" => $id), array("twoExamine" => 1, "twoExamineTime" => $time, "lastExamineUser" => session("admin.realname")));
            $this->ajaxReturn(array("Result" => true, "Code" => 2, "Time" => date("Y-m-d H:i:s", $time), "Msg" => "审核成功！"));
        } else {
            D("Admin")->commonExecute("finance_cost", array("id" => $id), array("oneExamine" => 1, "oneExamineTime" => $time, "lastExamineUser" => session("admin.realname")));
            $this->ajaxReturn(array("Result" => true, "Code" => 1, "Time" => date("Y-m-d H:i:s", $time), "Msg" => "审核成功！"));
        }
    }

    /**
     * 投放账号消耗月报
     */
    public function advterDetailMonth()
    {
        if (IS_POST) {
            $data = I();

            if ($data["month"]) {
                $map["month"] = date("Y-m", strtotime($data["month"]));
            } else {
                echo json_encode(array("status" => false, "msg" => "请选择日期"));
                exit();
            }
            if ($data["account"]) $map["account"] = $data["account"];

            //财务可以看全部数据，其他人只看自己
            if (!in_array(session('admin.role_id'), array(1, 13, 14, 17, 25, 32, 33))) {
                $admin            = D("Admin")->commonQuery("admin", array("id" => session("admin.uid")));
                $map["accountId"] = array("IN", $admin["backstage_account_id"] ? $admin["backstage_account_id"] : "0");
            }

            //获取所有账号的数据
            $account = getDataList("advter_account", "id");
            foreach ($account as $k => $v) {
                $advteruser                 = D("Admin")->commonQuery("advteruser", array("id" => $v["advteruserId"]), 0, 1, "company_name");
                $proxy                      = D("Admin")->commonQuery("proxy", array("id" => $v["proxyId"]), 0, 1, "proxyName");
                $account[$k]["companyName"] = $advteruser["company_name"];
                $account[$k]["proxyName"]   = $proxy["proxyName"];
            }
            if ($data["export"] == 1) {
                $col    = array("account" => "账号", "month" => "月份", "proxyName" => "代理商", "companyName" => "渠道", "cz" => "充值", "zr" => "转入", "zs" => "赠送", "zc" => "转出", "xh" => "消耗", "balance" => "余额");
                $monthF = date("Y-m-01", strtotime($map["month"]));
                $monthL = date("Y-m-d", strtotime($monthF." +1 month -1 day"));
                if ($map["month"] == date("Y-m", time()) || time() < strtotime(date("Y-m-03 06:00:00", time())) && $map["month"] = date("Y-m", strtotime(date("Y-m-d")." -1 month"))) {
                    $map["date"]    = array("BETWEEN", array($monthF, $monthL));
                    $list           = D("Admin")->getAdvterDetailType($map);
                    $res            = array();
                    foreach ($list as $k => $v) {
                        if (!$res[$v["accountId"]."_".$v["day"]]["month"]) $res[$v["accountId"]."_".$v["day"]]["month"] = $v["day"];
                        if (!$res[$v["accountId"]."_".$v["day"]]["account"]) $res[$v["accountId"]."_".$v["day"]]["account"] = $v["account"];
                        if (!$res[$v["accountId"]."_".$v["day"]]["accountId"]) $res[$v["accountId"]."_".$v["day"]]["accountId"] = $v["accountId"];
                        if (!$res[$v["accountId"]."_".$v["day"]]["companyName"]) $res[$v["accountId"]."_".$v["day"]]["companyName"] = $account[$v["accountId"]]["companyName"];
                        if (!$res[$v["accountId"]."_".$v["day"]]["proxyName"]) $res[$v["accountId"]."_".$v["day"]]["proxyName"] = $account[$v["accountId"]]["proxyName"];;
                        switch ($v["type"]) {
                            case 1:
                                $res[$v["accountId"]."_".$v["day"]]["cz"] = $v["amount"];
                                break;
                            case 2:
                                $res[$v["accountId"]."_".$v["day"]]["zr"] = $v["amount"];
                                break;
                            case 3:
                                $res[$v["accountId"]."_".$v["day"]]["zs"] = $v["amount"];
                                break;
                            case 4:
                                $res[$v["accountId"]."_".$v["day"]]["zc"] = $v["amount"];
                                break;
                        }
                    }
                    $cost           = D("Admin")->getAccountFinanceCostSum($map);
                    foreach ($cost as $k => $v) {
                        if (!$res[$v["accountId"]."_".$v["day"]]["month"]) $res[$v["accountId"]."_".$v["day"]]["month"] = $v["day"];
                        if (!$res[$v["accountId"]."_".$v["day"]]["account"]) $res[$v["accountId"]."_".$v["day"]]["account"] = $v["account"];
                        if (!$res[$v["accountId"]."_".$v["day"]]["accountId"]) $res[$v["accountId"]."_".$v["day"]]["accountId"] = $v["accountId"];
                        if (!$res[$v["accountId"]."_".$v["day"]]["companyName"]) $res[$v["accountId"]."_".$v["day"]]["companyName"] = $account[$v["accountId"]]["companyName"];
                        if (!$res[$v["accountId"]."_".$v["day"]]["proxyName"]) $res[$v["accountId"]."_".$v["day"]]["proxyName"] = $account[$v["accountId"]]["proxyName"];;
                        $res[$v["accountId"]."_".$v["day"]]["xh"] = $v["amount"];
                    }
                    $show = array();
                    foreach ($res as $v) {
                        $show[]             = $v;
                        $i_map["month"]     = $v["month"];
                        $i_map["accountId"] = $v["accountId"];
                        $i_res              = D("Admin")->getAdvterDetail($i_map);
                        foreach ($i_res as $val) {
                            $i_list["companyName"]  = $account[$val["accountId"]]["companyName"];
                            $i_list["proxyName"]    = $account[$val["accountId"]]["proxyName"];
                            $i_list["month"]        = $val["month"];
                            switch ($val["type"]) {
                                case 1:
                                    $i_list["cz"] = $val["amount"];
                                    break;
                                case 2:
                                    $i_list["zr"] = $val["amount"];
                                    break;
                                case 3:
                                    $i_list["zs"] = $val["amount"];
                                    break;
                                case 4:
                                    $i_list["zc"] = $val["amount"];
                                    break;
                                case 5:
                                    $i_list["xh"] = $val["amount"]."（".$val["agentName"]."）";
                                    break;
                            }
                            $show[] = $i_list;
                            unset($i_list);
                        }
                    }
                } else {
                    $res    = D("Admin")->commonQuery("sp_advter_account_detail_month", $map, 0, 999999);
                    $show   = array();
                    foreach ($res as $k => $v) {
                        $list                   = $v;
                        $list["companyName"]    = $account[$v["accountId"]]["companyName"];
                        $list["proxyName"]      = $account[$v["accountId"]]["proxyName"];
                        $list_map       = array(
                            "accountId" => $v["accountId"],
                            "date"      => array("BETWEEN", array($monthF, $monthL)),
                        );
                        $cost           = D("Admin")->getFinanceCostSum($list_map);
                        $list["xh"]     = $cost[0]["cost"];
                        for ($i = 1; $i <= 4; $i++) {
                            $list_map["type"]   = $i;
                            $sum                = D("Admin")->getAdvterDetailSum($list_map);
                            switch ($i) {
                                case 1:
                                    $list["cz"] = $sum;
                                    break;
                                case 2:
                                    $list["zr"] = $sum;
                                    break;
                                case 3:
                                    $list["zs"] = $sum;
                                    break;
                                case 4:
                                    $list["zc"] = $sum;
                                    break;
                            }
                        }

                        $show[]             = $list;
                        $i_map["month"]     = $v["month"];
                        $i_map["accountId"] = $v["accountId"];
                        $i_res              = D("Admin")->getAdvterDetail($i_map);
                        foreach ($i_res as $val) {
                            $i_list["companyName"]  = $account[$v["accountId"]]["companyName"];
                            $i_list["proxyName"]    = $account[$v["accountId"]]["proxyName"];
                            $i_list["month"]        = $val["month"];
                            switch ($val["type"]) {
                                case 1:
                                    $i_list["cz"] = $val["amount"];
                                    break;
                                case 2:
                                    $i_list["zr"] = $val["amount"];
                                    break;
                                case 3:
                                    $i_list["zs"] = $val["amount"];
                                    break;
                                case 4:
                                    $i_list["zc"] = $val["amount"];
                                    break;
                                case 5:
                                    $i_list["xh"] = $val["amount"]."（".$val["agentName"]."）";
                                    break;
                            }
                            $show[] = $i_list;
                            unset($i_list);
                        }
                    }
                }
                array_unshift($show, $col);
                export_to_csv($show, "投放消耗月报", $col);
                exit();
            } else {
                if ($data["id"]) {
                    $arr = explode("_", $data["id"]);
                    $map["month"]       = date("Y-m", strtotime($arr[0]));
                    $map["accountId"]   = $arr[1];
                    $res                = D("Admin")->getAdvterDetail($map);
                    foreach ($res as $k => $v) {
                        $res[$k]["companyName"] = $account[$v["accountId"]]["companyName"];
                        $res[$k]["proxyName"]   = $account[$v["accountId"]]["proxyName"];
                        $res[$k]["parentId"]    = $data["id"];
                        $res[$k]["state"]       = "open";
                        switch ($v["type"]) {
                            case 1:
                                $res[$k]["cz"] = $v["amount"];
                                break;
                            case 2:
                                $res[$k]["zr"] = $v["amount"];
                                break;
                            case 3:
                                $res[$k]["zs"] = $v["amount"];
                                break;
                            case 4:
                                $res[$k]["zc"] = $v["amount"];
                                break;
                            case 5:
                                $res[$k]["xh"] = $v["amount"]."（".$v["agentName"]."）";
                                break;
                        }
                    }
                    echo json_encode($res);
                    exit();
                } else {
                    $monthF = date("Y-m-01", strtotime($map["month"]));
                    $monthL = date("Y-m-d", strtotime($monthF." +1 month -1 day"));
                    if ($map["month"] == date("Y-m", time()) || time() < strtotime(date("Y-m-03 06:00:00", time())) && $map["month"] = date("Y-m", strtotime(date("Y-m-d")." -1 month"))) {
                        $map["date"]    = array("BETWEEN", array($monthF, $monthL));
                        $list           = D("Admin")->getAdvterDetailType($map);
                        $res            = array();
                        foreach ($list as $k => $v) {
                            if (!$res[$v["accountId"]."_".$v["day"]]["month"]) $res[$v["accountId"]."_".$v["day"]]["month"] = $v["day"];
                            if (!$res[$v["accountId"]."_".$v["day"]]["account"]) $res[$v["accountId"]."_".$v["day"]]["account"] = $v["account"];
                            if (!$res[$v["accountId"]."_".$v["day"]]["accountId"]) $res[$v["accountId"]."_".$v["day"]]["accountId"] = $v["accountId"];
                            if (!$res[$v["accountId"]."_".$v["day"]]["companyName"]) $res[$v["accountId"]."_".$v["day"]]["companyName"] = $account[$v["accountId"]]["companyName"];
                            if (!$res[$v["accountId"]."_".$v["day"]]["proxyName"]) $res[$v["accountId"]."_".$v["day"]]["proxyName"] = $account[$v["accountId"]]["proxyName"];;
                            if (!$res[$v["accountId"]."_".$v["day"]]["parentId"]) $res[$v["accountId"]."_".$v["day"]]["parentId"] = $v["day"]."_".$v["accountId"];
                            if (!$res[$v["accountId"]."_".$v["day"]]["state"]) $res[$v["accountId"]."_".$v["day"]]["state"] = "closed";
                            switch ($v["type"]) {
                                case 1:
                                    $res[$v["accountId"]."_".$v["day"]]["cz"] = $v["amount"];
                                    break;
                                case 2:
                                    $res[$v["accountId"]."_".$v["day"]]["zr"] = $v["amount"];
                                    break;
                                case 3:
                                    $res[$v["accountId"]."_".$v["day"]]["zs"] = $v["amount"];
                                    break;
                                case 4:
                                    $res[$v["accountId"]."_".$v["day"]]["zc"] = $v["amount"];
                                    break;
                            }
                        }
                        $cost           = D("Admin")->getAccountFinanceCostSum($map);
                        foreach ($cost as $k => $v) {
                            if (!$res[$v["accountId"]."_".$v["day"]]["month"]) $res[$v["accountId"]."_".$v["day"]]["month"] = $v["day"];
                            if (!$res[$v["accountId"]."_".$v["day"]]["account"]) $res[$v["accountId"]."_".$v["day"]]["account"] = $v["account"];
                            if (!$res[$v["accountId"]."_".$v["day"]]["accountId"]) $res[$v["accountId"]."_".$v["day"]]["accountId"] = $v["accountId"];
                            if (!$res[$v["accountId"]."_".$v["day"]]["companyName"]) $res[$v["accountId"]."_".$v["day"]]["companyName"] = $account[$v["accountId"]]["companyName"];
                            if (!$res[$v["accountId"]."_".$v["day"]]["proxyName"]) $res[$v["accountId"]."_".$v["day"]]["proxyName"] = $account[$v["accountId"]]["proxyName"];
                            if (!$res[$v["accountId"]."_".$v["day"]]["parentId"]) $res[$v["accountId"]."_".$v["day"]]["parentId"] = $v["day"]."_".$v["accountId"];
                            if (!$res[$v["accountId"]."_".$v["day"]]["state"]) $res[$v["accountId"]."_".$v["day"]]["state"] = "closed";
                            $res[$v["accountId"]."_".$v["day"]]["xh"] += $v["amount"];
                        }
                        $res            = array_values($res);
                    } else {
                        $res    = D("Admin")->commonQuery("sp_advter_account_detail_month", $map, 0, 999999);
                        foreach ($res as $k => $v) {
                            $res[$k]["companyName"] = $account[$v["accountId"]]["companyName"];
                            $res[$k]["proxyName"]   = $account[$v["accountId"]]["proxyName"];
                            $res[$k]["parentId"]    = $v["month"]."_".$v["accountId"];
                            $res[$k]["state"]       = "closed";
                            $list_map       = array(
                                "accountId" => $v["accountId"],
                                "date"      => array("BETWEEN", array($monthF, $monthL)),
                            );
                            $cost           = D("Admin")->getFinanceCostSum($list_map);
                            $res[$k]["xh"]  = $cost[0]["cost"];
                            for ($i = 1; $i <= 4; $i++) {
                                $list_map["type"]   = $i;
                                $sum                = D("Admin")->getAdvterDetailSum($list_map);
                                switch ($i) {
                                    case 1:
                                        $res[$k]["cz"] = $sum;
                                        break;
                                    case 2:
                                        $res[$k]["zr"] = $sum;
                                        break;
                                    case 3:
                                        $res[$k]["zs"] = $sum;
                                        break;
                                    case 4:
                                        $res[$k]["zc"] = $sum;
                                        break;
                                }
                            }
                        }
                    }
                    echo json_encode(array("rows" => ($res? $res: array()), "total" => count(($res? $res: array()))));
                    exit();
                }
            }
        } else {
            $this->display();
        }
    }

    /**
     * 录入投放账号的数据
     */
    public function advterDetailAdd()
    {
        if (IS_POST) {
            if (!$_FILES["detailFile"]["name"]) {
                $this->error("没有传入Excel");
            }
            //文件上传
            $file_info = excel_file_upload("Detail");
            if ($file_info && $file_info != "没有文件被上传！") {
                //获取文件数据并且转数组
                $fileName = "./Uploads/".$file_info["detailFile"]["savepath"].$file_info["detailFile"]["savename"];
                $data     = excel_to_array($fileName);
                if ($data) {
                    $advterUser    = getDataList("advteruser", "company_name", C("DB_PREFIX"));
                    $advterAccount = getDataListForKeys("advter_account", array("account", "advteruserId"), C("DB_PREFIX"));

                    $msg   = array();
                    $arr   = array();
                    $key   = 1;
                    unset($data[1]); //第一个行为标题，不需要入库
                    foreach ($data as $key => $val) {
                        $val = array_map("trim", $val);
                        if (empty($val[0])) {
                            $this->error("存在空日期！");
                        }
                        if (empty($val[1])) {
                            $this->error("存在空渠道！");
                        }
                        if (empty($val[2])) {
                            $this->error("存在空账号！");
                        }
                        if (empty($val[3])) {
                            $this->error("存在空类型！");
                        }
                        if (empty($val[4])) {
                            $this->error("存在空金额！");
                        }
                        //是否存在错误的日期
                        if (!strtotime($val[0])) {
                            $msg["date"] .= ",".$val[0];
                            $key = 0;
                        }
                        //是否存在错误渠道
                        if (!array_key_exists($val[1], $advterUser)) {
                            $msg["advter"] .= ",".$val[1];
                            $key = 0;
                        } else {
                            //是否存在错误的账号
                            $advterUserId = $advterUser[$val[1]]["id"];
                            if (!array_key_exists($val[2]."_".$advterUserId, $advterAccount)) {
                                $msg["account"] .= ",".$val[2];
                                $key = 0;
                            }
                        }
                        //是否存在错误的类型
                        if (!is_numeric($val[3])) {
                            $msg["type"] .= ",".$val[3];
                            $key = 0;
                        }
                        if ($key) {
                            $advterUserId   = $advterUser[$val[1]]["id"];
                            $date           = empty(date("Y-m-d", strtotime($val[0])))? "": date("Y-m-d", strtotime($val[0]));
                            switch ($val[3]) {
                                case "充值":
                                    $val[3] = 1;
                                    break;
                                case "转入":
                                    $val[3] = 2;
                                    break;
                                case "赠送":
                                    $val[3] = 3;
                                    break;
                                case "转出":
                                    $val[3] = 4;
                                    break;
                                default:
                                    null;
                            }
                            $arr[]          = array(
                                "accountId" => $advterAccount[$val[2]."_".$advterUserId]["id"],
                                "account"   => $val[2],
                                "date"      => $date,
                                "type"      => $val[3],
                                "amount"    => $val[4],
                                "time"      => time(),
                                "creator"   => session("admin.realname")
                            );
                        }
                    }
                    if ($msg || count($msg) > 0) {
                        $errorInfo = "存在错误数据，请修改再上传，错误信息为：";
                        if ($msg["advter"]) {
                            $errorInfo .= " 错误渠道【" . trim($msg["advter"], ",") . "】";
                        }
                        if ($msg["account"]) {
                            $errorInfo .= " 错误账号【" . trim($msg["account"], ",") . "】";
                        }
                        if ($msg["date"]) {
                            $errorInfo .= " 错误日期【" . trim($msg["date"], ",") . "】";
                        }
                        if ($msg["type"]) {
                            $errorInfo .= " 错误类型【" . trim($msg["type"], ",") . "】";
                        }
                        unset($arr);
                        $this->error($errorInfo, "", 10);
                    }
                    if ($arr) {
                        D("Admin")->commonAddAll("advter_account_detail", $arr);
                    }
                    @unlink($fileName);
                    $this->success("成本导入成功");
                }
            }
        } else {
            $response = $this->fetch();
            $this->ajaxReturn(array("Result" => true, "Html" => $response));
        }
    }

    /**
     * 插入前置方法
     */
    public function _before_insert($data)
    {
        if ($this->table == 'advter_cost') {
            empty($data['costMonth']) && $this->error('月份不能为空');
            $data['createTime'] = time();
        } elseif ($this->table == 'month_target') {
            empty($data['TargetMonth']) && $this->error('月份不能为空');
            $data['createTime'] = time();
        }
        return $data;
    }

    /**
     * 更新前置方法
     */
    public function _before_update($data)
    {
        if ($this->table == 'advter_cost') {
            $agent = getDataList('agent', 'agent', C('DB_PREFIX_API'));
            if (!isset($agent[$data['agent']])) {
                $this->error('渠道号有误');
            }

            $advterUser = getDataList('advteruser', 'company_name', C('DB_PREFIX'));
            if (!isset($advterUser[$data['media']])) {
                $this->error('媒体有误');
            }

            empty($data['costMonth']) && $this->error('日期不能为空');
        }
        return $data;
    }

    //获取月付费留存和LTV汇总
    public function backPeriod()
    {
        if (IS_POST) {
            $data = I();
            !$data['game_id'] && exit(json_encode(array('rows' => array(), 'results' => 0)));
            $agent_info   = $_REQUEST["agent"];
            $agent_p_info = $_REQUEST["agent_p"];
            $start        = $data["start"] ? $data["start"] : 0;
            $pageSize     = $data["limit"] ? $data["limit"] : 30;

            //处理搜索条件
            $agentArr = dealList($agent_info, $agent_p_info);

            $where = '1';
            if ($agentArr['agent']) {
                $where .= ' and agent in("' . implode('","', $agentArr['agent']) . '")';
                $map['agent'] = array('in', $agentArr['agent']);
            } elseif ($agentArr['pAgent']) {
                $agent_p_arr  = array_keys(getDataList('agent', 'id', C('DB_PREFIX_API'), array('agent' => array('IN', array_values($agentArr['pAgent'])))));
                $agent_arr    = array_values($agentArr['pAgent']);
                $agent_subarr = array_keys(getDataList('agent', 'agent', C('DB_PREFIX_API'), array('pid' => array('IN', $agent_p_arr))));
                if ($agent_subarr) {
                    $agent_arr = array_merge($agent_arr, $agent_subarr);
                }
                $where .= ' and agent in("' . implode('","', $agent_arr) . '")';
                $map['agent'] = array('in', $agent_arr);
            } elseif ($data['advteruser_id']) {
                $_map['advteruser_id']               = $data['advteruser_id'];
                $data['game_id'] && $_map['game_id'] = $data['game_id'];
                $agent_info                          = array_keys(getDataList('agent', 'agent', C('DB_PREFIX_API'), $_map));
                $data['agent']                       = $agent_info;
                $where .= ' and agent in("' . implode('","', $data['agent']) . '")';
                $map['agent'] = array('in', $data['agent']);
            } else {
                //权限控制
                $where .= ' and agent in("' . implode('","', $this->agentArr) . '")';
                $map['agent'] = array('in', $this->agentArr);
            }

            if ($data['game_id']) {
                $where .= ' and gameId=' . $data['game_id'];
                $map['gameId'] = $data['game_id'];
            }

            if ($data['startDate'] && $data['endDate']) {
                $where .= ' and dayTime>="' . $data['startDate'] . '" and dayTime<"' . date('Y-m-d', strtotime($data['endDate'] . '+1 day')) . '"';
                $map['dayTime'] = array(array('egt', $data['startDate']), array('lt', date('Y-m-d', strtotime($data['endDate'] . '+1 day'))), 'and');
            }

            $res        = D('Admin')->getPayRemainData($map, $start, $pageSize, $where); //数据汇总
            $game_list  = getDataList('game', 'id', C('DB_PREFIX_API'));
            $agent_list = getDataList('agent', 'agent', C('DB_PREFIX_API'));

            foreach ($res['list'] as $key => $val) {
                $data['lookType'] == 1 && $res['list'][$key]['agent']     = '-';
                $data['lookType'] == 1 && $res['list'][$key]['agentName'] = '-';
                $res['list'][$key]['gameName']                            = $game_list[$val['gameId']]['gameName'];
                //处理留存率
                $remainArr = $this->backPeriodSet($res['list'][$key], $res['list'][0]['dayTime']);
                if ($remainArr['dayTime'] == '汇总') {
                    $remainArr['gameName'] = $game_list[$val['gameId']]['gameName'];
                }
                $rows[] = $remainArr;
            }

            $row[] = end($rows);
            $arr   = array('rows' => $row ? $row : array());
            unset($rows, $remainArr);
            exit(json_encode($arr));
        } else {
            if (IS_AJAX) {
                $response = $this->fetch();
                $this->ajaxReturn(array('status' => 1, '_html' => $response));
            } else {
                $this->display();
            }
        }
    }

    //获取回款周期和预估单价
    public function getPeriodDataByAjax()
    {
        $data = I();
        !$data['game_id'] && exit(json_encode(array('rows' => array(), 'results' => 0)));
        $agent_info   = $_REQUEST["agent"];
        $agent_p_info = $_REQUEST["agent_p"];
        $start        = $data["start"] ? $data["start"] : 0;
        $pageSize     = $data["limit"] ? $data["limit"] : 30;

        //处理搜索条件
        $agentArr = dealList($agent_info, $agent_p_info);

        $where = '1';
        if ($agentArr['agent']) {
            $where .= ' and agent in("' . implode('","', $agentArr['agent']) . '")';
            $map['agent'] = array('in', $agentArr['agent']);
        } elseif ($agentArr['pAgent']) {
            $agent_p_arr  = array_keys(getDataList('agent', 'id', C('DB_PREFIX_API'), array('agent' => array('IN', array_values($agentArr['pAgent'])))));
            $agent_arr    = array_values($agentArr['pAgent']);
            $agent_subarr = array_keys(getDataList('agent', 'agent', C('DB_PREFIX_API'), array('pid' => array('IN', $agent_p_arr))));
            if ($agent_subarr) {
                $agent_arr = array_merge($agent_arr, $agent_subarr);
            }
            $where .= ' and agent in("' . implode('","', $agent_arr) . '")';
            $map['agent'] = array('in', $agent_arr);
        } elseif ($data['advteruser_id']) {
            $_map['advteruser_id']               = $data['advteruser_id'];
            $data['game_id'] && $_map['game_id'] = $data['game_id'];
            $agent_info                          = array_keys(getDataList('agent', 'agent', C('DB_PREFIX_API'), $_map));
            $data['agent']                       = $agent_info;
            $where .= ' and agent in("' . implode('","', $data['agent']) . '")';
            $map['agent'] = array('in', $data['agent']);
        } else {
            //权限控制
            $where .= ' and agent in("' . implode('","', $this->agentArr) . '")';
            $map['agent'] = array('in', $this->agentArr);
        }

        if ($data['game_id']) {
            $where .= ' and gameId=' . $data['game_id'];
            $map['gameId'] = $data['game_id'];
        }

        if ($data['startDate'] && $data['endDate']) {
            $where .= ' and dayTime>="' . $data['startDate'] . '" and dayTime<"' . date('Y-m-d', strtotime($data['endDate'] . '+1 day')) . '"';
            $map['dayTime'] = array(array('egt', $data['startDate']), array('lt', date('Y-m-d', strtotime($data['endDate'] . '+1 day'))), 'and');
        }

        $res        = D('Admin')->getPayRemainData($map, $start, $pageSize, $where); //数据汇总
        $game_list  = getDataList('game', 'id', C('DB_PREFIX_API'));
        $agent_list = getDataList('agent', 'agent', C('DB_PREFIX_API'));

        foreach ($res['list'] as $key => $val) {
            $data['lookType'] == 1 && $res['list'][$key]['agent']     = '-';
            $data['lookType'] == 1 && $res['list'][$key]['agentName'] = '-';
            $res['list'][$key]['gameName']                            = $game_list[$val['gameId']]['gameName'];
            //处理留存率
            $remainArr = $this->backPeriodSet($res['list'][$key], $res['list'][0]['dayTime']);
            if ($remainArr['dayTime'] == '汇总') {
                $remainArr['gameName'] = $game_list[$val['gameId']]['gameName'];
            }
            $rows[] = $remainArr;
        }

        $d             = end($rows);
        $payRemainData = array();
        $ltvData       = array();
        $number        = array(1, 3, 7, 15, 30);
        foreach ($number as $value) {
            $payRemainData[] = $d['day' . $value];
            $ltvData[]       = $d['ltv' . $value];
        }

        if ($data['ourRatio']) {
            $ourRatio = numFormat($data['ourRatio'] / 100, false);
        } else {
            $ourRatio = 0.55;
        }

        if ($data['firstLtv']) {
            $firstLtv = $data['firstLtv'];
        } else {
            //首月LTV，没有则取最近的数据(30,15,7,3,1)
            $firstLtv = end(array_filter($ltvData));
        }

        if ($data['monthPayRemain']) {
            $monthPayRemain = numFormat($data['monthPayRemain'] / 100, false);
        } else {
            //月付费留存，没有则取最近的数据(30,15,7,3,1)
            $monthPayRemain = floatval(end(array_filter($payRemainData))) / 100;
        }
        $putPrice = '';
        if ($data['backRatio']) {
            $backRatio = numFormat($data['backRatio'] / 100, false);
            $putPrice  = numFormat($firstLtv / $backRatio, false);
        } else {
            if ($data['putPrice']) {
                $putPrice = $data['putPrice'];
            } else {
                $putPrice = 60;
            }
            $backRatio = numFormat($firstLtv / $putPrice, false);
        }

        //获取回款周期数据
        $backPeriodData = $this->getBackPeriodData($monthPayRemain, $ourRatio, $backRatio);
        $month          = $backPeriodData['month'];
        $maxPriceData   = $this->getMaxPrice($monthPayRemain, $firstLtv, $month, $ourRatio);

        $backPeriodData['gameName']       = $d['gameName'];
        $backPeriodData['putPrice']       = $putPrice;
        $backPeriodData['ourRatio']       = numFormat($ourRatio, true);
        $backPeriodData['backRatio']      = numFormat($backRatio, true);
        $backPeriodData['monthPayRemain'] = numFormat($monthPayRemain, true);

        $maxPriceData['gameName']       = $d['gameName'];
        $maxPriceData['putPrice']       = $putPrice;
        $maxPriceData['ourRatio']       = numFormat($ourRatio, true);
        $maxPriceData['backRatio']      = numFormat($backRatio, true);
        $maxPriceData['monthPayRemain'] = numFormat($monthPayRemain, true);

        $backPeriod = $backPeriodData;
        $maxPrice   = $maxPriceData;

        $arr = array('backPeriod' => $backPeriod ? $backPeriod : array(), 'maxPrice' => $maxPrice ? $maxPrice : array());
        unset($rows, $remainArr);
        exit(json_encode($arr));
    }

    //计算回款周期
    public function getBackPeriodData($monthPayRemain, $ourRatio, $backRatio)
    {
        $backTarget = numFormat(100 / ($ourRatio * 100), false);

        if ($monthPayRemain == 1) {
            $month = ceil($backTarget / $backRatio);
        } else {
            $base   = $monthPayRemain;
            $logNum = (1 - ($backTarget * (1 - $monthPayRemain)) / $backRatio);
            if ($logNum < 0) {
                $month = '无解';
            } else {
                $month = ceil(log($logNum, $base));
            }
        }

        $currentBackRatio = array();
        for ($i = 1; $i < 7; $i++) {
            if ($i == 1) {
                $currentBackRatio['month' . $i] = $backRatio;
            } else {
                $currentBackRatio['month' . $i] = $currentBackRatio['month' . ($i - 1)] * $monthPayRemain;
            }
        }

        $totalBackRatio = array();
        for ($j = 1; $j < 7; $j++) {
            if ($j == 1) {
                $totalBackRatio['month' . $j] = $currentBackRatio['month' . $j];
            } else {
                $totalBackRatio['month' . $j] = $totalBackRatio['month' . ($j - 1)] + $currentBackRatio['month' . $j];
            }
        }
        foreach ($currentBackRatio as &$value) {
            $value = numFormat($value, true);
        }
        foreach ($totalBackRatio as &$val) {
            $val = numFormat($val, true);
        }

        $data = array(
            'backTarget'       => numFormat($backTarget, true),
            'currentBackRatio' => $currentBackRatio,
            'totalBackRatio'   => $totalBackRatio,
            'month'            => $month,
        );

        return $data;
    }

    //计算最高单价投放预估
    public function getMaxPrice($monthPayRemain, $firstLtv, $month, $ourRatio)
    {
        $currentLtv = array();
        $totalLtv   = array();
        $m          = $month;
        if ($m == '无解') {
            for ($i = 1; $i < 7; $i++) {
                $currentLtv['month' . $i] = '';
            }
            for ($j = 1; $j < 7; $j++) {
                $totalLtv['month' . $j] = '';
            }
            $maxPrice = '';
        } else {
            $m < 6 && $m = 6;
            for ($i = 1; $i < $m + 1; $i++) {
                if ($i == 1) {
                    $currentLtv['month' . $i] = $firstLtv;
                } else {
                    $currentLtv['month' . $i] = $currentLtv['month' . ($i - 1)] * $monthPayRemain;
                }
            }
            for ($j = 1; $j < $m + 1; $j++) {
                if ($j == 1) {
                    $totalLtv['month' . $j] = $currentLtv['month' . $j];
                } else {
                    $totalLtv['month' . $j] = $totalLtv['month' . ($j - 1)] + $currentLtv['month' . $j];
                }
            }
            $maxPrice = numFormat($totalLtv['month' . $month] * $ourRatio, false);
            foreach ($currentLtv as &$value) {
                $value = numFormat($value, false);
            }
            foreach ($totalLtv as &$val) {
                $val = numFormat($val, false);
            }
        }

        $data = array(
            'currentLtv' => $currentLtv,
            'totalLtv'   => $totalLtv,
            'maxPrice'   => $maxPrice,
            'month'      => $month,
            'firstLtv'   => $firstLtv,
        );

        return $data;
    }

    //处理月付费留存和LTV数据
    private function backPeriodSet($info, $startDate)
    {
        if (empty($info)) {
            return false;
        }

        for ($i = 0; $i <= 120; $i++) {
            if (isset($info['day' . $i])) {
                $i != 0 && $info['day' . $i] += $info['day' . ($i - 1)];
            }
        }
        if ($info['dayTime'] == '汇总') {
            $dayTime = strtotime($startDate);
        } else {
            $dayTime = strtotime($info['dayTime']);
        }
        $todayTime      = strtotime(date('Y-m-d 00:00:00'));
        $days           = ceil(($todayTime - $dayTime) / 3600 / 24) + 1;
        $info['newPay'] = $info['day0'];

        if ($days > 0) {
            $info['ltv1'] = numFormat(($info['day0']) / $info['newUser'], false);
        } else {
            $info['ltv1'] = '';
        }

        if ($days > 2) {
            $info['day1'] = numFormat(($info['day1'] - $info['day0']) / $info['day0'], true);
            $info['ltv3'] = numFormat(($info['day2']) / $info['newUser'], false);
        } else {
            $info['day1'] = '';
            $info['ltv3'] = '';
        }
        if ($days > 6) {
            $info['day3'] = numFormat(($info['day5'] - $info['day2']) / $info['day2'], true);
            $info['ltv7'] = numFormat(($info['day6']) / $info['newUser'], false);
        } else {
            $info['day3'] = '';
            $info['ltv7'] = '';
        }
        if ($days > 14) {
            $info['day7']  = numFormat(($info['day13'] - $info['day6']) / $info['day6'], true);
            $info['ltv15'] = numFormat(($info['day14']) / $info['newUser'], false);
        } else {
            $info['day7']  = '';
            $info['ltv15'] = '';
        }
        if ($days > 29) {
            $info['day15'] = numFormat(($info['day29'] - $info['day14']) / $info['day14'], true);
            $info['ltv30'] = numFormat(($info['day29']) / $info['newUser'], false);
        } else {
            $info['day15'] = '';
            $info['ltv30'] = '';
        }
        if ($days > 59) {
            $info['day30'] = numFormat(($info['day59'] - $info['day29']) / $info['day29'], true);
        } else {
            $info['day30'] = '';
        }
        return $info;
    }

    /**
     * 实时充值IOS
     * @return [type] [description]
     */
    public function payChartIos()
    {
        $data         = I();
        $agent_info   = $_REQUEST['advter_id'];
        $agent_p_info = $_REQUEST['events_groupId'];

        $start    = $data['start'] ? $data['start'] : 0;
        $pageSize = $data['limit'] ? $data['limit'] : 60;

        $department = session('admin.partment');

        //处理搜索条件
        $agentArr = dealAllList($agent_info, $agent_p_info);

        if ($agentArr['info']) {
            $map['advter_id'] = array('in', $agentArr['info']);
        } else {
            $agent_infos = $map_arr = array();

            if (!empty($agentArr['pinfo'])) {
                $map_arr['_string'] = "events_groupId IN ('" . implode("','", $agentArr['pinfo']) . "')";
            }

            if ($data['advteruser_id']) {
                $map_arr['advteruser_id'] = $data['advteruser_id'];
            }

            if ($data['creater']) {
                $map_arr['creater'] = $data['creater'];
            }

            if ($data['department']) {
                $map_arr['department'] = $data['department'];
            } else {
                $department != '0' && $map_arr['department'] = $department;
            }

            if ($data['game_id']) {
                $map_arr['game_id'] = $data['game_id'];
            }

            $agent_info = dealAllList($data['agent_p']);
            if ($agent_info['info']) {
                $agent_p = array_keys(getDataList('agent', 'agent', C('DB_PREFIX_API'), array('id' => array('IN', $agent_info['info']))));
                if ($agent_p) {
                    $map_arr['agent'] = array('IN', $agent_p);
                }
            }

            /*if($map_arr){
            }*/
            $agent_infos = array_keys(getDataList('events', 'id', C('DB_PREFIX'), $map_arr));

            if ($agent_infos) {
                $arr = $agent_infos;
            } elseif ($map_arr && !$agent_infos) {
                $arr = array('-1');
            }

            $map['advter_id'] = array('in', $arr);
        }

        $map['orderStatus'] = 0;
        $map['orderType']   = 0;
        $map['type']        = 2;

        $search = $map;
        if ($data['date']) {
            $map['createTime'] = array(array('egt', strtotime($data['date'])), array('lt', strtotime($data['date'] . '+1 day')), 'and');
            if ($data["date"] == date("Y-m-d")) {
                $search["createTime"] = array(array("egt", strtotime($data["date"] . " -1 day")), array("lt", strtotime(date("Y-m-d H:i:s") . " -1 day")), "and");
            } else {
                $search["createTime"] = array(array("egt", strtotime($data["date"] . " -1 day")), array("lt", strtotime($data["date"])), "and");
            }
        }

        $res = D("Admin")->getHourPayCountIos($map);
        $bef = D("Admin")->getHourPayCountIos($search);

        return array('res' => $res, 'bef' => $bef);
    }

    /**
     * 实时充值图表
     */
    public function payChart()
    {
        if (IS_POST) {
            $data         = I();
            $agent_info   = $_REQUEST["agent"];
            $agent_p_info = $_REQUEST["agent_p"];

            //处理搜索条件
            $agentArr = dealAllList($agent_info, $agent_p_info);

            if ($agentArr['info']) {
                $map['agent'] = array('in', $agentArr['info']);
            } else {
                $agent_infos = $map_arr = array();

                if (!empty($agentArr['pinfo'])) {
                    $map_arr['_string'] = "id IN ('" . implode("','", $agentArr['pinfo']) . "') OR pid IN ('" . implode("','", $agentArr['pinfo']) . "')";
                }

                if ($data['advteruser_id']) {
                    $map_arr['advteruser_id'] = $data['advteruser_id'];
                }

                if ($data['creater']) {
                    $map_arr['creater'] = $data['creater'];
                }

                $map_arr['gameType'] = 1;

                if ($data['department']) {
                    $map_arr['departmentId'] = $data['department'];
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

                $map['agent'] = array('in', $arr);
            }

            if ($data['game_id']) {
                $map['game_id'] = $data['game_id'];
            }

            if ($data['serverId']) {
                $map['serverId'] = $data['serverId'];
            }

            $map['orderStatus'] = 0;
            $map['orderType']   = 0;
            $map['type']        = 1;

            $search = $map;
            if ($data['date']) {
                $map['createTime'] = array(array('egt', strtotime($data['date'])), array('lt', strtotime($data['date'] . '+1 day')), 'and');
                if ($data["date"] == date("Y-m-d")) {
                    $search["createTime"] = array(array("egt", strtotime($data["date"] . " -1 day")), array("lt", strtotime(date("Y-m-d H:i:s") . " -1 day")), "and");
                } else {
                    $search["createTime"] = array(array("egt", strtotime($data["date"] . " -1 day")), array("lt", strtotime($data["date"])), "and");
                }
            }

            if (!$data['gameType']) {
                $res1 = D("Admin")->getHourPayCount($map);
                $bef1 = D("Admin")->getHourPayCount($search);

                $dataIos = $this->payChartIos();
                $res2    = $dataIos['res'];
                $bef2    = $dataIos['bef'];

                if ($res1 && $res2) {
                    $res = array_merge($res1, $res2);
                } elseif ($res1) {
                    $res = $res1;
                } elseif ($res2) {
                    $res = $res2;
                }

                if ($bef1 && $bef2) {
                    $bef = array_merge($bef1, $bef2);
                } elseif ($bef1) {
                    $bef = $bef1;
                } elseif ($bef2) {
                    $bef = $bef2;
                }

            } elseif ($data['gameType'] == 1) {
                $res = D("Admin")->getHourPayCount($map);
                $bef = D("Admin")->getHourPayCount($search);
            } elseif ($data['gameType'] == 2) {
                $dataIos = $this->payChartIos();
                $res     = $dataIos['res'];
                $bef     = $dataIos['bef'];
            }

            $max      = 0;
            $arr      = array();
            $arr_ber  = array();
            $info     = array();
            $info_bef = array();
            $list     = array();
            $row      = array();
            foreach ($res as $val) {
                $val["hour"] > $max && $max = $val["hour"];
                $arr[intval($val["hour"])] += intval($val["amount"]);
                $list[$val["agent"]]["agent"]      = $val["agent"];
                $list[$val["agent"]][$val["hour"]] = $val["amount"];
                $list[$val["agent"]]["count"] += intval($val["amount"]);
                $list["统计"][$val["hour"]] += $val["amount"];
                $list["统计"]["count"] += $val["amount"];
            }

            foreach ($bef as $val) {
                $arr_ber[intval($val["hour"])] += intval($val["amount"]);
                if (isset($list[$val["agent"]])) {
                    $list[$val["agent"]]["count_bef"] += intval($val["amount"]);
                }
                $list["统计"]["count_bef"] += intval($val["amount"]);
            }

            $n = 1;
            foreach ($list as $k => $v) {
                if ($k == "统计") {
                    continue;
                }

                $agent                                      = D("Admin/Admin")->commonQuery("agent", array("agent" => $v["agent"]), 0, 1, '*', 'lg_');
                $advter                                     = D("Admin/Admin")->commonQuery("events", array("id" => $v["agent"]), 0, 1, '*', 'la_');
                $row[$v["count"] * 10000 + $n]              = $v;
                $row[$v["count"] * 10000 + $n]["agentName"] = $agent["agentName"] ? $agent["agentName"] : "-";
                if ($row[$v["count"] * 10000 + $n]["agentName"] == '-') {
                    $row[$v["count"] * 10000 + $n]["agentName"] = $advter["events_name"] ? $advter["events_name"] : "-";
                    $row[$v["count"] * 10000 + $n]["agent"]     = $advter["agent"] ? $advter["agent"] : "-";
                }
                $n++;
            }

            krsort($row);
            $list["统计"]["agent"]     = "统计";
            $list["统计"]["agentName"] = "-";
            array_unshift($row, $list["统计"]);

            for ($i = 0; $i <= $max; $i++) {
                $info[]     = $arr[$i] ? $arr[$i] : 0;
                $info_bef[] = $arr_ber[$i] ? $arr_ber[$i] : 0;
            }

            if ($data['export'] == 1) {
                $col = array('agent' => '包号', 'agentName' => '游戏', 'count' => '统计', 'count_bef' => '昨日统计', '00' => '0时', '01' => '1时', '02' => '2时', '03' => '3时', '04' => '4时', '05' => '5时', '06' => '6时', '07' => '7时', '08' => '8时', '09' => '9时', '10' => '10时', '11' => '11时', '12' => '12时', '13' => '13时', '14' => '14时', '15' => '15时', '16' => '16时', '17' => '17时', '18' => '18时', '19' => '19时', '20' => '20时', '21' => '21时', '22' => '22时', '23' => '23时');
                array_unshift($row, $col);
                export_to_csv($row, '实时充值数据统计', $col);
                exit();
            }
            exit(json_encode(array("info" => $info, "yesterday" => $info_bef, "list" => array_values($row))));
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
     * 实时注册统计IOS
     * @return [type] [description]
     */
    public function registerChartIos()
    {
        $data         = I();
        $agent_info   = $_REQUEST['advter_id'];
        $agent_p_info = $_REQUEST['events_groupId'];

        //处理搜索条件
        $start      = $data['start'] ? $data['start'] : 0;
        $pageSize   = $data['limit'] ? $data['limit'] : 60;
        $department = session('admin.partment');

        $agentArr = dealAllList($agent_info, $agent_p_info);

        if ($agentArr['info']) {

            $map['advter_id'] = array('in', $agentArr['info']);
        } else {
            $agent_infos = $map_arr = array();

            if (!empty($agentArr['pinfo'])) {
                $map_arr['_string'] = "events_groupId IN ('" . implode("','", $agentArr['pinfo']) . "')";
            }

            if ($data['advteruser_id']) {
                $map_arr['advteruser_id'] = $data['advteruser_id'];
            }

            if ($data['creater']) {
                $map_arr['creater'] = $data['creater'];
            }

            if ($data['department']) {
                $map_arr['department'] = $data['department'];
            } else {
                $department != '0' && $map_arr['department'] = $department;
            }

            if ($data['game_id']) {
                $map_arr['game_id'] = $data['game_id'];
            }

            $agent_info = dealAllList($data['agent_p']);
            if ($agent_info['info']) {
                $agent_p = array_keys(getDataList('agent', 'agent', C('DB_PREFIX_API'), array('id' => array('IN', $agent_info['info']))));
                if ($agent_p) {
                    $map_arr['agent'] = array('IN', $agent_p);
                }
            }

            /*if($map_arr){
            }*/
            $agent_infos = array_keys(getDataList('events', 'id', C('DB_PREFIX'), $map_arr));

            if ($agent_infos) {
                $arr = $agent_infos;
            } elseif ($map_arr && !$agent_infos) {
                $arr = array('-1');
            }

            $map['advter_id'] = array('IN', $arr);
        }

        $search = $map;
        if ($data["date"]) {
            $map["createTime"] = array(array("egt", strtotime($data["date"])), array("lt", strtotime($data["date"] . " +1 day")), "and");
            if ($data["date"] == date("Y-m-d")) {
                $search["createTime"] = array(array("egt", strtotime($data["date"] . " -1 day")), array("lt", strtotime(date("Y-m-d H:i:s") . " -1 day")), "and");
            } else {
                $search["createTime"] = array(array("egt", strtotime($data["date"] . " -1 day")), array("lt", strtotime($data["date"])), "and");
            }
        }

        $res = D("Admin")->getHourRegisterCountIos($map);
        $bef = D("Admin")->getHourRegisterCountIos($search);

        return array('res' => $res, 'bef' => $bef);

    }

    /**
     * 实时注册图表
     */
    public function registerChart()
    {
        if (IS_POST) {
            $data         = I();
            $agent_info   = $_REQUEST["agent"];
            $agent_p_info = $_REQUEST["agent_p"];

            //处理搜索条件
            $agentArr = dealAllList($agent_info, $agent_p_info);

            if ($agentArr['info']) {
                $map['regAgent'] = $map2['agent'] = array('in', $agentArr['info']);
            } else {
                $agent_infos = $map_arr = array();

                if (!empty($agentArr['pinfo'])) {
                    $map_arr['_string'] = "id IN ('" . implode("','", $agentArr['pinfo']) . "') OR pid IN ('" . implode("','", $agentArr['pinfo']) . "')";
                }

                if ($data['advteruser_id']) {
                    $map_arr['advteruser_id'] = $data['advteruser_id'];
                }

                if ($data['creater']) {
                    $map_arr['creater'] = $data['creater'];
                }

                $map_arr['gameType'] = 1;

                if ($data['department']) {
                    $map_arr['departmentId'] = $data['department'];
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

                $map['regAgent'] = array('in', $arr);
                $map2['agent']   = array('in', $arr);
            }

            if ($data["game_id"]) {
                $map["game_id"]  = $data["game_id"];
                $map2["game_id"] = $data["game_id"];
            }

            if ($data["serverId"]) {
                $map["serverId"]  = $data["serverId"];
                $map2["serverId"] = $data["serverId"];
            }

            $search = $map;
            if ($data["date"]) {
                $map["regTime"] = array(array("egt", strtotime($data["date"])), array("lt", strtotime($data["date"] . " +1 day")), "and");
                if ($data["date"] == date("Y-m-d")) {
                    $search["regTime"] = array(array("egt", strtotime($data["date"] . " -1 day")), array("lt", strtotime(date("Y-m-d H:i:s") . " -1 day")), "and");

                } else {
                    $search["regTime"] = array(array("egt", strtotime($data["date"] . " -1 day")), array("lt", strtotime($data["date"])), "and");
                }
            }

            if (!$data['gameType']) {
                $res1 = D("Admin")->getHourRegisterCount($map);
                $bef1 = D("Admin")->getHourRegisterCount($search);

                $dataIos = $this->registerChartIos();
                $res2    = $dataIos['res'];
                $bef2    = $dataIos['bef'];

                if ($res1 && $res2) {
                    $res = array_merge($res1, $res2);
                } elseif ($res1) {
                    $res = $res1;
                } elseif ($res2) {
                    $res = $res2;
                }

                if ($bef1 && $bef2) {
                    $bef = array_merge($bef1, $bef2);
                } elseif ($bef1) {
                    $bef = $bef1;
                } elseif ($bef2) {
                    $bef = $bef2;
                }

            } elseif ($data['gameType'] == 1) {
                $res = D("Admin")->getHourRegisterCount($map);
                $bef = D("Admin")->getHourRegisterCount($search);
            } elseif ($data['gameType'] == 2) {
                $dataIos = $this->registerChartIos();
                $res     = $dataIos['res'];
                $bef     = $dataIos['bef'];
            }
            $max      = 0;
            $arr      = array();
            $arr_ber  = array();
            $info     = array();
            $info_bef = array();
            $list     = array();
            $row      = array();
            foreach ($res as $val) {
                $val["hour"] > $max && $max = $val["hour"];
                $arr[intval($val["hour"])] += intval($val["user"]);
                $list[$val["regAgent"]]["regAgent"]   = $val["regAgent"];
                $list[$val["regAgent"]][$val["hour"]] = $val["user"];
                $list[$val["regAgent"]]["count"] += intval($val["user"]);
                $list["统计"][$val["hour"]] += $val["user"];
                $list["统计"]["count"] += $val["user"];
            }

            foreach ($bef as $val) {
                $arr_ber[intval($val["hour"])] += intval($val["user"]);
                if (isset($list[$val["regAgent"]])) {
                    $list[$val["regAgent"]]["count_bef"] += intval($val["user"]);
                }
                $list["统计"]["count_bef"] += intval($val["user"]);
            }

            $n = 1;
            foreach ($list as $k => $v) {
                if ($k == "统计") {
                    continue;
                }

                $agent                                      = D("Admin/Admin")->commonQuery("agent", array("agent" => $v["regAgent"]), 0, 1, "*", "lg_");
                $advter                                     = D("Admin/Admin")->commonQuery("events", array("id" => $v["regAgent"]), 0, 1, '*', 'la_');
                $row[$v["count"] * 10000 + $n]              = $v;
                $row[$v["count"] * 10000 + $n]["agentName"] = $agent["agentName"] ? $agent["agentName"] : "-";
                if ($row[$v["count"] * 10000 + $n]["agentName"] == '-') {
                    $row[$v["count"] * 10000 + $n]["agentName"] = $advter["events_name"] ? $advter["events_name"] : "-";
                    $row[$v["count"] * 10000 + $n]["regAgent"]  = $advter["agent"] ? $advter["agent"] : "-";
                }
                $n++;
            }

            krsort($row);
            $list["统计"]["regAgent"]  = "统计";
            $list["统计"]["agentName"] = "-";
            array_unshift($row, $list["统计"]);

            for ($i = 0; $i <= $max; $i++) {
                $info[]     = $arr[$i] ? $arr[$i] : 0;
                $info_bef[] = $arr_ber[$i] ? $arr_ber[$i] : 0;
            }
            if ($data['export'] == 1) {
                $col = array('regAgent' => '包号', 'agentName' => '游戏', 'count' => '统计', 'count_bef' => '昨日日统计', '00' => '0时', '01' => '1时', '02' => '2时', '03' => '3时', '04' => '4时', '05' => '5时', '06' => '6时', '07' => '7时', '08' => '8时', '09' => '9时', '10' => '10时', '11' => '11时', '12' => '12时', '13' => '13时', '14' => '14时', '15' => '15时', '16' => '16时', '17' => '17时', '18' => '18时', '19' => '19时', '20' => '20时', '21' => '21时', '22' => '22时', '23' => '23时');
                array_unshift($row, $col);
                export_to_csv($row, '实时注册数据统计', $col);
                exit();
            }

            exit(json_encode(array("info" => $info, "yesterday" => $info_bef, "list" => array_values($row))));
        } else {
            if (IS_AJAX) {
                $response = $this->fetch();
                $this->ajaxReturn(array("status" => 1, "_html" => $response));
            } else {
                $this->display();
            }
        }
    }

    /**
     * 新增账户留存统计
     */
    public function userRemain()
    {
        if (IS_POST) {
            $data = I();
            !$data['game_id'] && exit(json_encode(array('rows' => array(), 'results' => 0)));
            $agent_info   = $_REQUEST["agent"];
            $agent_p_info = $_REQUEST["agent_p"];
            $start        = $data["start"] ? $data["start"] : 0;
            $pageSize     = $data["limit"] ? $data["limit"] : 30;

            //处理搜索条件
            $agentArr = dealAllList($agent_info, $agent_p_info);

            if ($agentArr['info']) {
                //查出安卓的渠道号
                $keys = array_keys(getDataList('agent', 'agent', 'lg_', array('gameType' => 1, 'agent' => array('IN', $agentArr['info']))));
                if ($keys) {
                    $map['agent'] = array('IN', $keys);
                } else {
                    $map['agent'] = '-1';
                }
            } else {
                $agent_infos         = $map_arr         = array();
                $map_arr['gameType'] = 1;

                if (!empty($agentArr['pinfo'])) {
                    $map_arr['_string'] = "id IN ('" . implode("','", $agentArr['pinfo']) . "') OR pid IN ('" . implode("','", $agentArr['pinfo']) . "')";
                }

                if ($data['advteruser_id']) {
                    $map_arr['advteruser_id'] = $data['advteruser_id'];
                }

                if ($map_arr) {
                    $agent_infos = array_keys(getDataList('agent', 'agent', C('DB_PREFIX_API'), $map_arr));
                }

                $arr = $this->agentArr;
                if ($agent_infos) {
                    $arr = array_intersect($arr, $agent_infos);
                } elseif ($map_arr && !$agent_infos) {
                    $arr = array('-1');
                }

                sort($arr);
                $map['agent'] = array('in', $arr);
            }

            if ($data['game_id']) {
                $map['gameId'] = $data['game_id'];
            }

            if ($data['startDate'] && $data['endDate']) {
                $map['dayTime'] = array(array('egt', $data['startDate']), array('lt', date('Y-m-d', strtotime($data['endDate'] . '+1 day'))), 'and');
            }

            if (!$data['system']) {
                $res1 = D('Admin')->getUserRemainData($map, $start, $pageSize); //安卓用户留存数据
                $res2 = $this->userRemainIos();
                if ($res1 && $res2) {
                    $res['list']  = array_merge($res1['list'], $res2['list']);
                    $res['count'] = $res1['count'] + $res2['count'];
                    //合并同一天的数据
                    $arr = array();
                    $i   = 0;
                    foreach ($res['list'] as $key => $value) {
                        $arr[$value['gameId'] . '_' . $value['dayTime']]['dayTime'] = $value['dayTime'];
                        $arr[$value['gameId'] . '_' . $value['dayTime']]['agent']   = $value['agent'];
                        $arr[$value['gameId'] . '_' . $value['dayTime']]['gameId']  = $value['gameId'];
                        $arr[$value['gameId'] . '_' . $value['dayTime']]['newUser'] += $value['newUser'];
                        for ($i = 0; $i < 120; $i++) {
                            if (isset($value['day' . $i])) {
                                $arr[$value['gameId'] . '_' . $value['dayTime']]['day' . $i] += $value['day' . $i];
                            }
                        }
                        $i = 0;
                    }
                    sort($arr);
                    $res['list'] = $arr;
                    unset($arr);
                } elseif ($res1) {
                    $res = $res1;
                } elseif ($res2) {
                    $res = $res2;
                }
            } elseif ($data['system'] == 1) {
                $res = D('Admin')->getUserRemainData($map, $start, $pageSize); //安卓用户留存数据
            } elseif ($data['system'] == 2) {
                $res = $this->userRemainIos();
            }
            $results    = $res['count'];
            $game_list  = getDataList('game', 'id', C('DB_PREFIX_API'));
            $agent_list = getDataList('agent', 'agent', C('DB_PREFIX_API'));

            foreach ($res['list'] as $key => $val) {
                if ($data['lookType'] == 1) {
                    $res['list'][$key]['agent'] = $res['list'][$key]['dayTime'] = '-';
                }
                $res['list'][$key]['agentName'] = $agent_list[$val['agent']]['agentName'];
                $res['list'][$key]['gameName']  = $game_list[$val['gameId']]['gameName'];
                //处理留存率
                if ($data['lookType'] == 2) {
                    $remainArr = $this->userRemainSet($res['list'][$key]);
                    $rows[]    = $remainArr;
                }
            }

            if ($data['lookType'] == 1) {
                $rows = $this->userRemainSetNew($res['list']);
            }

            //显示图表
            if ($data['chart'] == 1) {
                $remainChart = $this->remainChart($rows);
                if ($remainChart === false) {
                    $this->ajaxReturn(array('status' => 0, 'info' => array('day' => array(), 'day1' => array(), 'day6' => array(), 'day29' => array())));
                }
                $this->ajaxReturn(array('status' => 1, 'info' => $remainChart));
            }

            //数据汇总
            $pagesummary = $this->userSummarys($rows);
            if ($data['export'] == 1) {

                $col     = array('dayTime' => '注册日期', 'gameName' => '游戏名称', 'newUser' => '新增账户数');
                $day_arr = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 13, 29, 29, 89);
                for ($i = 0; $i <= 120; $i++) {
                    if (in_array($i, $day_arr)) {
                        $col['day' . $i] = ($i + 1) . '日留存';
                    }
                }
                array_unshift($rows, $col);
                $pagesummary['dayTime']  = '汇总';
                $pagesummary['gameName'] = '-';
                array_push($rows, $pagesummary);
                export_to_csv($rows, '账户留存', $col);
                exit();
            }
            $arr = array('rows' => $rows ? $rows : array(), 'results' => $results, 'pageSummary' => $pagesummary);
            unset($rows, $remainArr);
            exit(json_encode($arr));
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
     * IOS用戶留存统计
     */
    public function userRemainIos()
    {
        if (IS_POST) {
            $data         = I();
            $agent_info   = $_REQUEST['advter_id'];
            $agent_p_info = $_REQUEST['events_groupId'];

            //处理搜索条件
            $start    = $data['start'] ? $data['start'] : 0;
            $pageSize = $data['limit'] ? $data['limit'] : 60;
            $where    = '1';

            $department = session('admin.partment');

            $agentArr = dealAllList($agent_info, $agent_p_info);

            if ($agentArr['info']) {
                $map['advterId'] = array('IN', $agentArr['info']);
            } else {
                $agent_infos = $map_arr = array();

                if (!empty($agentArr['pinfo'])) {
                    $map_arr['_string'] = "events_groupId IN ('" . implode("','", $agentArr['pinfo']) . "')";
                }

                if ($data['advteruser_id']) {
                    $map_arr['advteruser_id'] = $data['advteruser_id'];
                }

                if ($data['department']) {
                    $map_arr['department'] = $data['department'];
                } else {
                    $department != '0' && $map_arr['department'] = $department;
                }

                if ($data['game_id']) {
                    $map_arr['game_id'] = $data['game_id'];
                }

                $agent_info = dealAllList($data['agent_p']);
                if ($agent_info['info']) {
                    $agent_p = array_keys(getDataList('agent', 'agent', C('DB_PREFIX_API'), array('id' => array('IN', $agent_info['info']))));
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

                $map['advterId'] = array('IN', $arr);
            }

            if ($data['startDate'] && $data['endDate']) {
                $map['dayTime'] = array(array('egt', $data['startDate']), array('lt', date('Y-m-d', strtotime($data['endDate'] . '+1 day'))), 'and');
            }

            $res = D('Admin')->getIosUserRemainData($map, $start, $pageSize);
            return $res;

        }
    }

    //处理新增用户留存数据 $encode=0输出%，1输出值
    private function userRemainSet($info, $encode = 0)
    {

        if (empty($info)) {
            return false;
        }

        for ($i = 0; $i <= 120; $i++) {
            if (isset($info['day' . $i])) {
                if ($encode == 0) {
                    $info['day' . $i] = numFormat($info['day' . $i] / $info['newUser'], true);
                } elseif ($encode == 1) {
                    $info['day' . $i] = numFormat($info['day' . $i] / $info['newUser'], true) + 0;
                }
            }
        }
        return $info;
    }

    //处理新增用户留存数据 $encode=0输出%，1输出值
    private function userRemainSetNew($info, $encode = 0)
    {

        if (empty($info)) {
            return false;
        }

        $row = $newUser = $remain = $allNewUser = $newInfo = array();

        foreach ($info as $key => $value) {
            for ($i = 0; $i <= 120; $i++) {
                if (isset($value['day' . $i]) && $value['day' . $i] > 0) {
                    $newInfo[$value['gameId']]['agentName'] = $value['agentName'];
                    $newInfo[$value['gameId']]['gameName']  = $value['gameName'];

                    //计算汇总的新增人数
                    $newUser[$value['gameId']]['day' . $i] += $value['newUser'];
                    //计算汇总的留存人数
                    $remain[$value['gameId']]['day' . $i] += $value['day' . $i];
                }
            }
        }
        //新增用户汇总
        foreach ($info as $k => $v) {
            $allNewUser[$v['gameId']] += $v['newUser'];
        }

        //组合数据
        foreach ($newUser as $k => $v) {

            $dayKey = array_keys($v);

            for ($i = 0; $i <= 120; $i++) {
                if (isset($v['day' . $i])) {
                    if ($encode == 0) {
                        $newInfo[$k]['day' . $i] = numFormat($remain[$k]['day' . $i] / $v['day' . $i], true);
                    } elseif ($encode == 1) {
                        $newInfo[$k]['day' . $i] = numFormat($remain[$k]['day' . $i] / $v['day' . $i], true) + 0;
                    }
                } else {
                    $newInfo[$k]['day' . $i] = '0.00%';
                }
            }
            $newInfo[$k]['newUser'] += $allNewUser[$k];

        }
        sort($newInfo);
        unset($info);
        return $newInfo;
    }

    //留存图表
    protected function remainChart($data)
    {
        if (!$data) {
            return false;
        }

        $chart = array();

        $chart['day'] = array_column($data, 'dayTime');

        foreach ($data as $key => $value) {
            $chart['remain']['day1'][]  = $value['day1'] + 0;
            $chart['remain']['day2'][]  = $value['day2'] + 0;
            $chart['remain']['day3'][]  = $value['day3'] + 0;
            $chart['remain']['day4'][]  = $value['day4'] + 0;
            $chart['remain']['day5'][]  = $value['day5'] + 0;
            $chart['remain']['day6'][]  = $value['day6'] + 0;
            $chart['remain']['day7'][]  = $value['day7'] + 0;
            $chart['remain']['day8'][]  = $value['day8'] + 0;
            $chart['remain']['day9'][]  = $value['day9'] + 0;
            $chart['remain']['day13'][] = $value['day13'] + 0;
            $chart['remain']['day59'][] = $value['day59'] + 0;
            $chart['remain']['day89'][] = $value['day89'] + 0;
        }

        return $chart;
    }

    //用户注册留存数据汇总
    private function userSummarys($data)
    {

        $sum      = array();
        $data_num = count($data);
        $day_arr  = array();
        for ($i = 0; $i <= 90; $i++) {
            $day_arr[] = 'day' . $i;
        }
        $day_num = array();
        //开始统计
        foreach ($data as $k => $val) {
            $sum['newDevice'] += $val['newDevice'];
            $sum['allPay'] += $val['allPay'];
            $sum['distinctReg'] += $val['distinctReg'];
            $sum['newUser'] += $val['newUser'];
            $sum['payRate'] += $val['payRate'];
            $sum['ARPU'] += $val['ARPU'];
            $sum['ARPPU'] += $val['ARPPU'];
            $sum['newPayRate'] += $val['newPayRate'];
            $sum['newARPU'] += $val['newARPU'];
            $sum['newARPPU'] += $val['newARPPU'];
            $sum['newPay']       = '-';
            $sum['allPayUser']   = '-';
            $sum['newPayUser']   = '-';
            $sum['newUserLogin'] = '-';
            $sum['oldUserLogin'] = '-';
            $sum['monthLogin']   = '-';
            foreach ($val as $key => $value) {
                if (in_array($key, $day_arr)) {
                    $sum[$key] += $value;
                    $value > 0 && $day_num[$key]++;
                }
            }
        }
        //开始计算

        //用户新增
        $sum['userRate']     = '-';
        $sum['allUserLogin'] = '-';
        $sum['activeRate']   = '-';

        //充值概况平均值
        $sum['payRate'] = sprintf("%.2f", $sum['payRate'] / $data_num) . '%';
        $sum['ARPU']    = sprintf("%.2f", $sum['ARPU'] / $data_num);
        $sum['ARPPU']   = sprintf("%.2f", $sum['ARPPU'] / $data_num);

        //新增充值平均值
        $sum['newPayRate'] = sprintf("%.2f", $sum['newPayRate'] / $data_num) . '%';
        $sum['newARPU']    = sprintf("%.2f", $sum['newARPU'] / $data_num);
        $sum['newARPPU']   = sprintf("%.2f", $sum['newARPPU'] / $data_num);

        //留存平均值
        foreach ($sum as $k => $v) {
            if (in_array($k, $day_arr)) {
                $sum[$k] = sprintf("%.2f", $v / $day_num[$k]) . '%';
            }
        }
        return $sum;
    }

    /**
     * 投放数据概况统计
     */
    public function advDataBak()
    {
        if (IS_POST) {

            $data         = I();
            $agent_info   = $_REQUEST['agent'];
            $agent_p_info = $_REQUEST['agent_p'];
            if (in_array(session('admin.uid'), array(114, 112)) && $data['id']) {
                return false;
            }

            //处理搜索条件
            $agentArr = dealAllList($agent_info, $agent_p_info);

            $start    = $data['start'] ? $data['start'] : 0;
            $pageSize = $data['limit'] ? $data['limit'] : 10000;
            $where    = '1';
            if ($agentArr['info']) {
                $data['agent'] = $agentArr['info'];
                $where .= ' and a.agent in("' . implode('","', $data['agent']) . '")';
                $map2['regAgent'] = array('in', $data['agent']);
                $map['agent']     = array('in', $data['agent']);
            } else {

                $agent_infos = $map_arr = array();

                if (!empty($agentArr['pinfo'])) {
                    //母包id
                    $pid                = array_keys(getDataList('agent', 'id', C('DB_PREFIX_API'), array('agent' => array('IN', $agentArr['pinfo']))));
                    $map_arr['_string'] = "id IN ('" . implode("','", $pid) . "') OR pid IN ('" . implode("','", $pid) . "')";
                }

                $advteruser_id = dealAllList($data['advteruser_id']);

                if ($advteruser_id['info']) {
                    $map_arr['advteruser_id'] = array('IN', $advteruser_id['info']);
                }

                if ($data['creater']) {
                    $map_arr['creater'] = $data['creater'];
                }

                if ($data['department']) {
                    $map_arr['departmentId'] = $data['department'];
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

                $where .= ' and a.agent in("' . implode('","', $arr) . '")';
                $map2['regAgent'] = array('in', $arr);
                $map['agent']     = array('in', $arr);

            }

            $game_id = dealAllList($data['game_id']);
            $tmp     = $data['id'] ? explode('_', $data['id']) : array();

            if (count($tmp) > 1 && $tmp[0] == 'and') {
                $where .= ' and a.game_id =' . $tmp[2];
                $map['game_id']    = $map2['game_id']    = $tmp[2];
                $game_id['info']   = array($tmp[2]);
                $data['startDate'] = $data['endDate'] = $tmp[1];
            } elseif ($game_id['info']) {
                $data['game_id'] = $game_id['info'];
                $where .= ' and a.game_id IN("' . implode('","', $data['game_id']) . '")';
                $map['game_id']  = array('IN', $data['game_id']);
                $map2['game_id'] = array('IN', $data['game_id']);
            }

            //是否查询一个月的数据
            $dayOne  = date("Y-m-01", strtotime($data["startDate"]));
            $isMonth = 0;
            if ($dayOne && $dayOne == $data["startDate"] && date("Y-m-d", strtotime($dayOne . " +1 month -1 day")) == $data["endDate"]) {
                $isMonth = 1;
            }

            if ($data['startDate'] && $data['startDate']) {
                $where .= ' and a.dayTime>="' . $data['startDate'] . '" and a.dayTime<"' . date('Y-m-d', strtotime($data['endDate'] . '+1 day')) . '"';
                if ($isMonth == 1) {
                    $map2['a.dayTime'] = array(array('egt', $data['startDate']), array('lt', date('Y-m-d', strtotime($data['endDate'] . '+1 day'))), 'and');
                } else {
                    $map2['dayTime'] = array(array('egt', $data['startDate']), array('lt', date('Y-m-d', strtotime($data['endDate'] . '+1 day'))), 'and');
                }
                $map['paymentTime'] = array(array('egt', strtotime($data['startDate'])), array('lt', strtotime(date('Y-m-d', strtotime($data['endDate'] . '+1 day')))), 'and');
            }

            $where .= ' and a.gameType = 1';
            $map['type'] = array('eq', 1);

            if (!$data['system']) {
                if (count($tmp) > 1 && $tmp[0] == 'and') {
                    $res1 = D('Admin')->getAdvDataBak($map, $start, $pageSize, $where, $map2, $isMonth, $game_id['info'], $data['id'], $data['startDate'], $data['endDate']);
                } elseif ($tmp[0] == 'ios') {
                    $res2 = $this->advDataIosBak();
                } else {
                    $res1 = D('Admin')->getAdvDataBak($map, $start, $pageSize, $where, $map2, $isMonth, $game_id['info'], $data['id'], $data['startDate'], $data['endDate']);
                    $res2 = $this->advDataIosBak();
                }
                if ($res1 && $res2) {
                    $res['list']  = array_merge($res1['list'], $res2['list']);
                    $res['count'] = array(
                        'allPayUser' => ($res1['count']['allPayUser'] + $res2['count']['allPayUser']),
                        'login'      => array(
                            'DAU'          => ($res1['count']['login']['DAU'] + $res2['count']['login']['DAU']),
                            'oldUserLogin' => ($res1['count']['login']['oldUserLogin'] + $res2['count']['login']['oldUserLogin']),
                        ),
                    );
                } elseif ($res1) {
                    $res = $res1;
                } elseif ($res2) {
                    $res = $res2;
                }

            } elseif ($data['system'] == 1) {
                $res = D('Admin')->getAdvDataBak($map, $start, $pageSize, $where, $map2, $isMonth, $game_id['info'], $data['id'], $data['startDate'], $data['endDate']);
            } elseif ($data['system'] == 2) {
                $res = $this->advDataIosBak();
            }

            $allPayUser   = $res['count']['allPayUser'];
            $DAU          = $res['count']['login']['DAU'];
            $oldUserLogin = $res['count']['login']['oldUserLogin'];
            $game_list    = getDataList('game', 'id', C('DB_PREFIX_API'));
            $agent_list   = getDataList('agent', 'agent', C('DB_PREFIX_API'));
            $event_list   = getDataList('events', 'id');

            $advteruser_list = getDataList('advteruser', 'id', C('DB_PREFIX'));
            $total           = $newRes           = $sortArr           = array();
            foreach ($res['list'] as $key => $val) {
                //如果游戏汇总全是0则去除
                if (empty($val['allPayUser']) && empty($val['soleUdids']) &&  empty($val['newDevice']) && empty($val['newUser']) && empty($val['oldUserLogin']) && empty($val['DAU']) && empty($val['day1']) && empty((float) $val['allPay']) && empty((float) $val['newPay']) && empty($val['newPayUser']) && empty((float) $val['totalPay']) && empty((float) $val['cost'])) {
                    unset($res['list'][$key]);
                    continue;
                }

            }
            sort($res['list']);

            if ($data['id']) {
                $sortArr = array_column($res['list'], 'newUser');
                array_multisort($sortArr, SORT_DESC, $res['list']);
                unset($sortArr);
            }

            foreach ($res['list'] as $key => $val) {
                $newRes[$key] = $val;

                if (isset($tmp) && count($tmp) > 1) {
                    if ($val['advterId']) {
                        $res['list'][$key]['parentId'] = $data['id'] . '_' . $val['day_time'] . '_' . $val['advterId'];
                    } else {
                        $res['list'][$key]['parentId'] = $data['id'] . '_' . $val['day_time'] . '_' . $val['agent'];
                    }
                    $res['list'][$key]['agent']          = $agent_list[$val['agent']]['gameType'] == 1 ? $val['agent'] : $event_list[$val['advterId']]['events_name']; //推广活动名称
                    $res['list'][$key]['advteruserName'] = $advteruser_list[$val['advteruser_id']]['company_name'];

                } else {
                    $res['list'][$key]['agent']          = '-';
                    $res['list'][$key]['advteruserName'] = '-';
                    if ($val['advterId']) {
                        $res['list'][$key]['parentId'] = 'ios_' . $val['dayTime'] . '_' . $val['agent'];
                    } else {
                        $res['list'][$key]['parentId'] = 'and_' . $val['dayTime'] . '_' . $val['game_id'];
                    }
                }

                $res['list'][$key]['state'] = count($tmp) < 1 ? 'closed' : 'open';

                //游戏的汇总
                $res['list'][$key]['allPay']   = floatval($val['allPay']);
                $res['list'][$key]['cost']     = floatval($val['cost']);
                $res['list'][$key]['gameName'] = $agent_list[$val['agent']]['gameType'] == 1 ? $game_list[$val['game_id']]['gameName'] : $agent_list[$val['agent']]['agentName'];

                $res['list'][$key]['regCost'] = round(floatval($val['cost'] / $val['newUser']), 2); //注册单价

                $res['list'][$key]['newPayRate']   = numFormat(($val['newPayUser'] / $val['newUser']), true); //新增付费率
                $res['list'][$key]['actPayRate']   = numFormat(($val['allPayUser'] / $val['DAU']), true); //活跃付费率
                $res['list'][$key]['dayRate']      = numFormat(($val['newPay'] / $val['cost']), true); //1日回本率
                $res['list'][$key]['allPayRate']   = numFormat(($val['allPayNow'] / $val['cost']), true); //至今回本率
                $res['list'][$key]['totalPayRate'] = numFormat(($val['totalPay'] / $val['cost']), true); //累计回本率
                $res['list'][$key]['regRate']      = numFormat(($val['disUdid'] / $val['newDevice']), true);

                $res['list'][$key]['day1']  = numFormat(($val['day1'] / $val['newUser']), true); //次留
                $res['list'][$key]['ARPU']  = round(floatval($val['allPay'] / $val['DAU']), 2); //活跃ARPU
                $res['list'][$key]['ARPPU'] = round(floatval($val['allPay'] / $val['allPayUser']), 2); //活跃ARPPU

                $res['list'][$key]['newARPU']  = round(floatval($val['newPay'] / $val['newUser']), 2); //新增ARPU
                $res['list'][$key]['newARPPU'] = round(floatval($val['newPay'] / $val['newPayUser']), 2); //新增ARPPU

            }

            //父级汇总
            if ($data['isCount'] == 1) {
                $parentSum = $this->summarys($newRes, 1, $allPayUser, $DAU, $oldUserLogin);
                //一个月的数据暂时先注释，后期删去
                if ($isMonth && $parentSum['DAU'] == 0 && $parentSum['oldUserLogin'] == 0) {
                    $parentSum['DAU']          = "-";
                    $parentSum['oldUserLogin'] = "-";
                    $parentSum['actPayRate']   = "-"; //活跃付费率
                    $parentSum['ARPU']         = "-"; //活跃ARPU
                }
                $parentSum['parentId'] = $data['id'] . '_sum';
                $parentSum['dayTime']  = (isset($tmp) && count($tmp) > 1) ? '子级汇总' : '父级汇总';
                $res['list'][]         = $parentSum;
            }

            if ($data['export'] != 1) {
                $rows          = $res['list'];
                $displayColumn = array_map(function ($args) {return explode("_", $args)[0];}, $data['displayColumn']);
                $diff = array_diff(array_keys($rows[0]), $displayColumn);
                foreach ($rows as &$value) {
                    foreach ($diff as $t) {
                        if (in_array($t, array('state', 'parentId'))) {
                            continue;
                        }
                        unset($value[$t]);
                    }
                }
            }
            unset($res);

            if ($data['export'] == 1) {
                $rows[] = $parentSum;
                $col    = array('dayTime' => '注册日期', 'gameName' => '游戏名称', 'agent' => '渠道号', 'advteruserName' => '渠道商', 'cost' => '成本', 'newDevice' => '新增设备数', 'disUdid' => '唯一注册数', 'newUser' => '新增账号数', 'regRate' => '注册转化率', 'regCost' => '注册单价', 'newPay' => '新增充值金额', 'newPayUser' => '新增充值人数', 'newPayRate' => '新增付费率', 'newARPU' => '新增ARPU', 'newARPPU' => '新增ARPPU', 'allPay' => '当天充值金额', 'allPayUser' => '充值人数', 'actPayRate' => '活跃付费率', 'ARPU' => '活跃ARPU', 'ARPPU' => '活跃ARPPU', 'day1' => '次留', 'oldUserLogin' => '老用户活跃数', 'DAU' => 'DAU', 'dayRate' => '1日回本率', 'allPayNow' => '区间充值金额', 'allPayRate' => '区间回本率', 'totalPay' => '至今充值金额', 'totalPayRate' => '至今回本率');
                //各个包创建人的注册和充值情况
                $rows = $this->principalInfo($rows);
                $rows = $this->channelInfo($rows);

                array_unshift($rows, $col);

                export_to_csv($rows, '投放数据概况', $col);
                exit();
            }

            if (!$data['id']) {
                echo json_encode(array('rows' => ($rows ? $rows : array()), 'total' => count(($rows ? $rows : array()))));
            } else {
                echo json_encode($rows);
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

    /**
     * IOS投放数据概况统计
     */
    public function advDataIosBak()
    {
        if (IS_POST) {
            $data         = I();
            $agent_info   = $_REQUEST['advter_id'];
            $agent_p_info = $_REQUEST['events_groupId'];

            //处理搜索条件
            $start    = $data['start'] ? $data['start'] : 0;
            $pageSize = $data['limit'] ? $data['limit'] : 10000;
            $where    = '1';

            $department = session('admin.partment');

            $agentArr = dealAllList($agent_info, $agent_p_info);

            if ($agentArr['info']) {
                $where .= ' and a.advterId in("' . implode('","', $agentArr['info']) . '")';
                $map2['advter_id'] = array('in', $agentArr['info']);
                $map['advter_id']  = array('in', $agentArr['info']);
            } else {
                $agent_infos = $map_arr = array();

                if (!empty($agentArr['pinfo'])) {
                    $map_arr['_string'] = "events_groupId IN ('" . implode("','", $agentArr['pinfo']) . "')";
                }

                $advteruser_id = dealAllList($data['advteruser_id']);

                if ($advteruser_id['info']) {
                    $map_arr['advteruser_id'] = array('IN', $advteruser_id['info']);
                }

                if ($data['creater']) {
                    $map_arr['creater'] = $data['creater'];
                }

                if ($data['department']) {
                    $map_arr['department'] = $data['department'];
                } else {
                    $department != '0' && $map_arr['department'] = $department;
                }

                $game_info = dealAllList($data['game_id']);
                if ($game_info['info']) {
                    $map_arr['game_id'] = array('IN', $game_info['info']);
                }

                $agent_info = dealAllList($data['agent_p']);
                if ($agent_info['info']) {
                    $map_arr['agent'] = array('IN', $agent_info['info']);
                }

                $agent_infos = array_keys(getDataList('events', 'id', C('DB_PREFIX'), $map_arr));

                if ($agent_infos) {
                    $arr = $agent_infos;
                } elseif ($map_arr && !$agent_infos) {
                    $arr = array('-1');
                }

                $where .= ' and a.advterId in("' . implode('","', $arr) . '")';
                $map2['advter_id'] = array('in', $arr);
                $map['advter_id']  = array('IN', $arr);
            }

            $tmp = $data['id'] ? explode('_', $data['id']) : array();

            if (count($tmp) > 1) {
                $data['startDate'] = $data['endDate'] = $tmp[1];
                $map['agent']      = $tmp[2];
                $where .= ' and a.agent="' . $tmp[2] . '"';
            }
            //是否查询一个月的数据
            $dayOne  = date("Y-m-01", strtotime($data["startDate"]));
            $isMonth = 0;
            if ($dayOne && $dayOne == $data["startDate"] && date("Y-m-d", strtotime($dayOne . " +1 month -1 day")) == $data["endDate"]) {
                $isMonth = 1;
            }

            if ($data['startDate'] && $data['startDate']) {
                $where .= ' and a.dayTime>="' . $data['startDate'] . '" and a.dayTime<"' . date('Y-m-d', strtotime($data['endDate'] . '+1 day')) . '"';
                if ($isMonth == 1) {
                    $map2['a.dayTime'] = array(array('egt', $data['startDate']), array('lt', date('Y-m-d', strtotime($data['endDate'] . '+1 day'))), 'and');
                } else {
                    $map2['dayTime'] = array(array('egt', $data['startDate']), array('lt', date('Y-m-d', strtotime($data['endDate'] . '+1 day'))), 'and');
                }
                $map['paymentTime'] = array(array('egt', strtotime($data['startDate'])), array('lt', strtotime(date('Y-m-d', strtotime($data['endDate'] . '+1 day')))), 'and');
            }

            $map['type'] = array('eq', 2);

            //去除advter_id为15的测试数据
            $where .= ' and a.advterId <> 15';
            $map2['_string'] = ' advter_id <> 15';
            $map['_string']  = ' advter_id <> 15';

            $res = D('Admin')->getAdvDataIosBak($map, $start, $pageSize, $where, $map2, $isMonth, $data['id'], $data['startDate'], $data['endDate']);
            return $res;
        }
    }

    /**
     * 实时图表
     */
    public function realtimeChart()
    {
        if (IS_POST) {
            $data         = I();
            $agent_info   = $_REQUEST["agent"];
            $agent_p_info = $_REQUEST["agent_p"];

            $join = null;
            $sum  = false;
            if ($data["showType"] == 1) {
                $table    = "login";
                $info     = "userCode";
                $time     = "time";
                $agent    = "regAgent";
                $distinct = true;
                $prefix   = C("DB_PREFIX_LOG");
            } elseif ($data["showType"] == 2) {
                $table    = "login";
                $info     = "udid";
                $time     = "time";
                $agent    = "regAgent";
                $distinct = true;
                $prefix   = C("DB_PREFIX_LOG");
            } elseif ($data["showType"] == 3) {
                $table    = "device_game";
                $info     = "1";
                $time     = "createTime";
                $agent    = "agent";
                $distinct = false;
                $prefix   = C("DB_PREFIX_API");
            } elseif ($data["showType"] == 4) {
                $table            = "device_game";
                $info             = "1";
                $time             = "createTime";
                $agent            = "agent";
                $distinct         = false;
                $prefix           = C("DB_PREFIX_API");
                $map["lastLogin"] = array("EXP", 'IS NOT NULL');
            } elseif ($data["showType"] == 5) {
                $table               = "role";
                $info                = "a.udid";
                $time                = "a.createTime";
                $agent               = "b.agent";
                $distinct            = true;
                $prefix              = C("DB_PREFIX_API");
                $join                = "LEFT JOIN lg_device_game b ON a.udid = b.udid";
                $map["b.createTime"] = array(array("egt", strtotime($data["date"])), array("lt", strtotime($data["date"] . "+1 day")), "and");
            } elseif ($data["showType"] == 6) {
                $table              = "order";
                $info               = "userCode";
                $time               = "paymentTime";
                $agent              = "agent";
                $distinct           = true;
                $prefix             = C("DB_PREFIX_API");
                $map["orderType"]   = 0;
                $map["orderStatus"] = 0;
            } elseif ($data["showType"] == 7) {
                $table              = "order";
                $info               = "amount";
                $time               = "paymentTime";
                $agent              = "agent";
                $distinct           = false;
                $sum                = true;
                $prefix             = C("DB_PREFIX_API");
                $map["orderType"]   = 0;
                $map["orderStatus"] = 0;
            } elseif ($data["showType"] == 8) {
                $table              = "order";
                $info               = "1";
                $time               = "paymentTime";
                $agent              = "agent";
                $distinct           = false;
                $prefix             = C("DB_PREFIX_API");
                $map["orderType"]   = 0;
                $map["orderStatus"] = 0;
            } elseif ($data["showType"] == 9) {
                $table    = "fall_open_log";
                $info     = "requestIp";
                $time     = "createTime";
                $agent    = "agent";
                $distinct = true;
                $prefix   = C("DB_PREFIX");
            } elseif ($data["showType"] == 10) {
                $table    = "fall_download_log";
                $info     = "requestIp";
                $time     = "createTime";
                $agent    = "agent";
                $distinct = true;
                $prefix   = C("DB_PREFIX");
            }

            //处理搜索条件
            $agentArr = dealList($agent_info, $agent_p_info);

            if ($agentArr['agent']) {
                $map['agent'] = array('in', $agentArr['agent']);
            } else {
                $agent_infos = $map_arr = array();

                if (!empty($agentArr['pAgent'])) {
                    $map_arr['_string'] = "id IN ('" . implode("','", $agentArr['pAgent']) . "') OR pid IN ('" . implode("','", $agentArr['pAgent']) . "')";
                }

                if ($data['advteruser_id']) {
                    $map_arr['advteruser_id'] = $data['advteruser_id'];
                }

                if ($data['creater']) {
                    $map_arr['creater'] = $data['creater'];
                }

                if ($data['gameType']) {
                    $map_arr['gameType'] = $data['gameType'];
                }

                if ($data['department']) {
                    $map_arr['departmentId'] = $data['department'];
                }

                if ($map_arr) {
                    $agent_infos = array_keys(getDataList('agent', 'agent', C('DB_PREFIX_API'), $map_arr));
                }

                $arr = $this->agentArr;
                if ($agent_infos) {
                    $arr = array_intersect($arr, $agent_infos);
                } elseif ($map_arr && !$agent_infos) {
                    exit(json_encode(array('rows' => array(), 'results' => 0)));
                }

                sort($arr);
                if (count($arr) < 1) {
                    $arr[] = '-1';
                }

                $map['agent'] = array('in', $arr);

            }

            if ($data["game_id"]) {
                if ($data["showType"] == 5) {
                    $map["a.game_id"] = $data["game_id"];
                } else {
                    $map["game_id"] = $data["game_id"];
                }
            }

            if ($data["date"]) {
                $map[$time] = array(array("egt", strtotime($data["date"])), array("lt", strtotime($data["date"] . "+1 day")), "and");
            }

            $res  = D("Admin")->getHourCount($table, $info, $map, $time, $agent, $distinct, $prefix, $join, $sum);
            $arr  = array();
            $max  = 0;
            $info = array();
            $list = array();
            $row  = array();
            foreach ($res as $val) {
                $val["hour"] > $max && $max = $val["hour"];
                $arr[intval($val["hour"])] += intval($val["num"]);
                $list[$val["agent"]]["agent"]      = $val["agent"];
                $list[$val["agent"]][$val["hour"]] = $val["num"];
                $list[$val["agent"]]["count"] += intval($val["num"]);
                $list["统计"][$val["hour"]] += $val["num"];
                $list["统计"]["count"] += $val["num"];
            }

            $n = 1;
            foreach ($list as $k => $v) {
                if ($k == "统计") {
                    continue;
                }

                $agent                                      = D("Admin/Admin")->commonQuery("agent", array("agent" => $v["agent"]), 0, 1, "*", "lg_");
                $row[$v["count"] * 10000 + $n]              = $v;
                $row[$v["count"] * 10000 + $n]["agentName"] = $agent["agentName"] ? $agent["agentName"] : "-";
                $n++;
            }

            krsort($row);
            $list["统计"]["agent"]     = "统计";
            $list["统计"]["agentName"] = "-";
            $row["统计"]               = $list["统计"];

            for ($i = 0; $i <= $max; $i++) {
                $info[] = $arr[$i] ? $arr[$i] : 0;
            }
            exit(json_encode(array("info" => $info, "list" => array_values($row))));
        } else {
            if (IS_AJAX) {
                $response = $this->fetch();
                $this->ajaxReturn(array("status" => 1, "_html" => $response));
            } else {
                $this->display();
            }
        }
    }

    /**
     * 注册地区分布
     */
    public function areaRegister()
    {
        if (IS_POST) {
            $data = I();
            !$data['game_id'] && exit(json_encode(array('rows' => array(), 'results' => 0, 'info' => '请选择游戏')));
            $agent_info   = $_REQUEST["agent"];
            $agent_p_info = $_REQUEST["agent_p"];
            $where        = '1';
            //处理搜索条件
            $agentArr = dealList($agent_info, $agent_p_info);
            if ($agentArr['agent']) {
                $where .= ' and agent in("' . implode('","', $agentArr['agent']) . '")';
            } else {
                $agent_infos = $map_arr = array();

                if (!empty($agentArr['pAgent'])) {
                    $map_arr['_string'] = "id IN ('" . implode("','", $agentArr['pAgent']) . "') OR pid IN ('" . implode("','", $agentArr['pAgent']) . "')";
                }

                if ($data['advteruser_id']) {
                    $map_arr['advteruser_id'] = $data['advteruser_id'];
                }

                if ($data['game_id']) {
                    $map_arr['game_id'] = $data['game_id'];
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

                $where .= ' and agent in("' . implode('","', $arr) . '")';
            }

            if ($data["startDate"] && $data["endDate"]) {
                $where .= ' AND dayTime >= "' . $data["startDate"] . '" AND dayTime < "' . date("Y-m-d", strtotime($data["endDate"] . "+1 day")) . '"';
            }

            $res         = D("Admin")->getAreaRegisterData($where, $data["lookType"]); //充值地区分布
            $allRegister = $res["count"];
            foreach ($res["list"] as $key => $val) {
                $data["lookType"] == 1 && $res["list"][$key]["dayTime"] = "-";
                $res["list"][$key]["city"]                              = "-";
                $res["list"][$key]["Rate"]                              = numFormat(($val["register"] / $allRegister["provinceRegister"]), true);

                //城市
                foreach ($res["list"][$key]["children"] as $k => $v) {
                    $data["lookType"] == 1 && $res["list"][$key]["children"][$k]["dayTime"] = "-";
                    $res["list"][$key]["children"][$k]["Rate"]                              = numFormat(($v["register"] / $allRegister["provinceRegister"]), true);
                    if ($data["export"] == 1) {
                        $exportArr[] = $res["list"][$key]["children"][$k];
                    }
                }
            }
            $res["list"][] = array("province" => "汇总", "city" => "-", "register" => $allRegister["provinceRegister"], "Rate" => "100%");
            $rows          = $res["list"];
            unset($res);

            if ($data["export"] == 1) {
                $col = array("dayTime" => "日期", "province" => "省份", "city" => "城市", "register" => "人数", "Rate" => "占比");
                array_unshift($exportArr, $col);
                export_to_csv($exportArr, "注册地区分布", $col);
                exit();
            }
            $arr = array("rows" => $rows ? $rows : array(), "pageSummary" => $allRegister);
            unset($rows, $remainArr);
            exit(json_encode($arr));
        } else {
            if (IS_AJAX) {
                $response = $this->fetch();
                $this->ajaxReturn(array("status" => 1, "_html" => $response));
            } else {
                $this->display();
            }
        }
    }

    /**
     * 付费衰减
     */
    public function payRemain()
    {
        if (IS_POST) {
            $data = I();
            !$data['game_id'] && exit(json_encode(array('rows' => array(), 'results' => 0)));
            $agent_info   = $_REQUEST["agent"];
            $agent_p_info = $_REQUEST["agent_p"];
            $start        = $data["start"] ? $data["start"] : 0;
            $pageSize     = $data["limit"] ? $data["limit"] : 30;

            //处理搜索条件
            $agentArr = dealList($agent_info, $agent_p_info);

            $where = '1';
            if ($agentArr['agent']) {
                $where .= ' and agent in("' . implode('","', $agentArr['agent']) . '")';
                $map['agent'] = array('in', $agentArr['agent']);
            } elseif ($agentArr['pAgent']) {
                $agent_p_arr  = array_keys(getDataList('agent', 'id', C('DB_PREFIX_API'), array('agent' => array('IN', array_values($agentArr['pAgent'])))));
                $agent_arr    = array_values($agentArr['pAgent']);
                $agent_subarr = array_keys(getDataList('agent', 'agent', C('DB_PREFIX_API'), array('pid' => array('IN', $agent_p_arr))));
                if ($agent_subarr) {
                    $agent_arr = array_merge($agent_arr, $agent_subarr);
                }
                $where .= ' and agent in("' . implode('","', $agent_arr) . '")';
                $map['agent'] = array('in', $agent_arr);
            } elseif ($data['advteruser_id']) {
                $_map['advteruser_id']               = $data['advteruser_id'];
                $data['game_id'] && $_map['game_id'] = $data['game_id'];
                $agent_info                          = array_keys(getDataList('agent', 'agent', C('DB_PREFIX_API'), $_map));
                $data['agent']                       = $agent_info;
                $where .= ' and agent in("' . implode('","', $data['agent']) . '")';
                $map['agent'] = array('in', $data['agent']);
            } else {
                //权限控制
                $where .= ' and agent in("' . implode('","', $this->agentArr) . '")';
                $map['agent'] = array('in', $this->agentArr);
            }

            if ($data['game_id']) {
                $where .= ' and gameId=' . $data['game_id'];
                $map['gameId'] = $data['game_id'];
            }

            if ($data['startDate'] && $data['endDate']) {
                $where .= ' and dayTime>="' . $data['startDate'] . '" and dayTime<"' . date('Y-m-d', strtotime($data['endDate'] . '+1 day')) . '"';
                $map['dayTime'] = array(array('egt', $data['startDate']), array('lt', date('Y-m-d', strtotime($data['endDate'] . '+1 day'))), 'and');
            }

            $res        = D('Admin')->getPayRemainData($map, $start, $pageSize, $where); //付费衰减数据
            $results    = $res['count'];
            $game_list  = getDataList('game', 'id', C('DB_PREFIX_API'));
            $agent_list = getDataList('agent', 'agent', C('DB_PREFIX_API'));

            foreach ($res['list'] as $key => $val) {
                $data['lookType'] == 1 && $res['list'][$key]['agent']     = '-';
                $data['lookType'] == 1 && $res['list'][$key]['agentName'] = '-';
                $res['list'][$key]['gameName']                            = $game_list[$val['gameId']]['gameName'];
                //处理留存率
                $remainArr = $this->payRemainSet($res['list'][$key], $res['list'][0]['dayTime']);
                $rows[]    = $remainArr;
            }

            //显示图表
            if ($data['chart'] == 1) {
                $payRemainChart = $this->payRemainChart($rows);
                if ($payRemainChart === false) {
                    $this->ajaxReturn(array('status' => 0, 'info' => array('day1' => array(), 'day' => array(), 'day7' => array(), 'day15' => array(), 'day30' => array(), 'day60' => array())));
                }
                $this->ajaxReturn(array('status' => 1, 'info' => $payRemainChart));
            }

            //数据汇总
            $pagesummary = $this->payRemainSummarys($rows);
            if ($data['export'] == 1) {

                $col     = array('dayTime' => '注册日期', 'gameName' => '游戏名称', 'newUser' => '注册用户数', 'newPay' => '首日付费金额');
                $day_arr = array(1, 3, 7, 15, 30, 60);

                for ($i = 0; $i <= 120; $i++) {
                    if (in_array($i, $day_arr)) {
                        $i == 1 && $col['day' . $i] = '次日流水增降幅度';
                        $col['day' . $i]            = ($i) . '日流水增降幅度';
                    }
                }
                array_unshift($rows, $col);
                export_to_csv($rows, '流水衰减', $col);
                exit();
            }
            $arr = array('rows' => $rows ? $rows : array(), 'results' => $results, 'pageSummary' => $pagesummary);
            unset($rows, $remainArr);
            exit(json_encode($arr));
        } else {
            if (IS_AJAX) {
                $response = $this->fetch();
                $this->ajaxReturn(array('status' => 1, '_html' => $response));
            } else {
                $this->display();
            }
        }
    }

    //付费留存图表
    protected function payRemainChart($data)
    {
        if (!$data) {
            return false;
        }

        array_pop($data);
        $chart = array();

        $chart['day'] = array_column($data, 'dayTime');

        foreach ($data as $key => $value) {
            $chart['remain']['day1'][]  = $value['day1'] + 0;
            $chart['remain']['day3'][]  = $value['day3'] + 0;
            $chart['remain']['day7'][]  = $value['day7'] + 0;
            $chart['remain']['day15'][] = $value['day15'] + 0;
            $chart['remain']['day30'][] = $value['day30'] + 0;
            $chart['remain']['day60'][] = $value['day60'] + 0;
        }

        return $chart;
    }

    //付费衰减数据汇总
    private function payRemainSummarys($data)
    {

        $sum      = array();
        $data_num = count($data);
        $day_arr  = array('day0', 'day1', 'day3', 'day7', 'day15', 'day30', 'day60');
        $day_num  = array();
        //开始统计
        foreach ($data as $k => $val) {
            $sum['newUser'] += $val['newUser'];
            $sum['newPay'] += $val['newPay'];
            foreach ($val as $key => $value) {
                if (in_array($key, $day_arr)) {
                    $sum[$key] += $value;
                    $value > 0 && $day_num[$key]++;
                }
            }
        }

        //留存平均值
        foreach ($sum as $k => $v) {
            if (in_array($k, $day_arr)) {
                $sum[$k] = sprintf("%.2f", $v / $day_num[$k]) . '%';
            }
        }
        return $sum;
    }

    /**
     * 激活走势
     */
    public function activateDiagram()
    {
        if (IS_AJAX) {
            $data         = I();
            $agent_info   = $_REQUEST["agent"];
            $agent_p_info = $_REQUEST["agent_p"];

            //处理搜索条件
            $agentArr = dealList($agent_info, $agent_p_info);
            if ($agentArr['agent']) {
                $data['agent'] = $agentArr['agent'];
                $map['agent']  = array('in', $data['agent']);
            } else {
                if ($agentArr['pAgent']) {
                    $agent_p_arr = array_keys(getDataList('agent', 'id', C('DB_PREFIX_API'), array('agent' => array('IN', array_values($agentArr['pAgent'])))));

                    $agent_arr    = array_values($agentArr['pAgent']);
                    $agent_subarr = array_keys(getDataList('agent', 'agent', C('DB_PREFIX_API'), array('pid' => array('IN', $agent_p_arr))));
                    if ($agent_subarr) {
                        $agent_arr = array_merge($agent_arr, $agent_subarr);
                    }

                    $data['agent'] = $agent_arr;
                    $map['agent']  = array('in', $data['agent']);
                } else {
                    //权限控制
                    $map['agent'] = array('in', $this->agentArr);
                }
            }

            if ($data['game_id']) {
                if ($data['type'] == '3') {
                    $map['game_id'] = $data['game_id'];
                } else {
                    $map['gameId'] = $data['game_id'];
                }
            }
            if ($data['startDate'] && $data['endDate']) {
                $map['dayTime'] = array("BETWEEN", array($data['startDate'], $data['endDate']));
            } elseif ($data['startDate']) {
                $map['dayTime'] = array("EGT", $data['startDate']);
            } elseif ($data['endDate']) {
                $map['dayTime'] = array("ELT", $data['endDate']);
            }

            $info = D("Admin")->getCountActivateGroupByDay($map, $data['type']);
            // var_dump($info);die;
            $arr = array();
            $min = isset($data["startDate"]) ? $data["startDate"] : 0;
            $max = isset($data["endDate"]) ? $data["endDate"] : 0;
            foreach ($info as $v) {
                $arr[$v["day"]] = $v["totalData"];
                if ($min == 0) {
                    $min = $v["day"];
                } else {
                    $min = ($min > $v["day"]) ? $v["day"] : $min;
                }
                if ($max == 0) {
                    $max = $v["day"];
                } else {
                    $max = ($max < $v["day"]) ? $v["day"] : $max;
                }
            }
            $list = array();
            for (; $min <= $max; $min = date("Y-m-d", strtotime($min . " +1day"))) {
                if (!isset($arr[$min])) {
                    $list[] = array("day" => $min, "totalData" => 0);
                } else {
                    $list[] = array("day" => $min, "totalData" => $arr[$min]);
                }
            }
            echo json_encode($list);
            exit;
        } else {
            $this->display();
        }
    }

    /**
     * vungle数据统计
     */
    public function vungleData()
    {
        if (IS_POST) {
            $data = I();
            !$data['game_id'] && exit(json_encode(array('rows' => array(), 'results' => 0)));

            $start    = $data["start"] ? $data["start"] : 0;
            $pageSize = $data["limit"] ? $data["limit"] : 30;

            //处理搜索条件
            $agentArr = dealAllList($data['advter_id'], $data['events_groupId']);

            if ($agentArr['info']) {
                $map['advter_id'] = $agentArr['info'];
            } else {
                $agent_infos = $map_arr = array();

                if (!empty($agentArr['pinfo'])) {
                    $map_arr['_string'] = "events_groupId IN ('" . implode("','", $agentArr['pinfo']) . "')";
                }

                if ($data['advteruser_id']) {
                    $map_arr['advteruser_id'] = $data['advteruser_id'];
                }

                if ($data['game_id']) {
                    $map_arr['game_id'] = $data['game_id'];
                }

                $agent_info = dealAllList($data['agent_p']);
                if ($agent_info['info']) {
                    $map_arr['agent'] = array('IN', $agent_info['info']);
                } else {
                    $map_arr['agent'] = array('IN', $this->agentArr);
                }

                $agent_infos = array_keys(getDataList('events', 'id', C('DB_PREFIX'), $map_arr));

                if ($agent_infos) {
                    $arr = $agent_infos;
                } elseif ($map_arr && !$agent_infos) {
                    $arr = array('-1');
                }

                $map['advter_id'] = array_map(function ($num) {return (string) $num;}, $arr);
            }

            if ($data['startDate'] && $data['startDate']) {
                $map['startDate'] = strtotime($data['startDate']);
                $map['endDate']   = strtotime($data['endDate'] . ' +1 day');
            }
            $res = D('Admin')->getVungleData($map, $start, $pageSize);

            $results    = $res['count'];
            $game_list  = getDataList('game', 'id', C('DB_PREFIX_API'));
            $agent_list = getDataList('agent', 'agent', C('DB_PREFIX_API'));
            $event_list = getDataList('events', 'id');
            $advterlist = getDataList('advter_list', 'id', C('DB_PREFIX'), array('agent' => array('IN', $this->agentArr)));

            $advteruser_list = getDataList('advteruser', 'id', C('DB_PREFIX'));
            foreach ($res['list'] as $key => $val) {
                $res['list'][$key]['gameName']       = $game_list[$val['game_id']]['gameName'];
                $res['list'][$key]['eventName']      = $event_list[$val['advter_id']]['events_name'];
                $res['list'][$key]['advteruserName'] = $advteruser_list[$val['adUserId']]['company_name'];
                $res['list'][$key]['clickNum']       = $val['clickNum'];
                $rows[]                              = $res['list'][$key];
            }

            $arr = array('rows' => empty($rows) ? array() : $rows, 'results' => $results);
            exit(json_encode($arr));
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
