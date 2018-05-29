<?php
/**
 * Created by Sublime.
 * User: XSM
 * Date: 2017/6/26
 * Time: 15:22
 *
 * 运营管理控制器
 */

namespace ThirdParty\Controller;
use ThirdParty\Controller\BackendController;

class AdvterDataController extends BackendController
{
    private $userDataTime = '';

    public function _initialize(){
        parent::_initialize();
        $this->userDataTime = $this->dataTime[session('admin.username')];
    }
    /**
     * 落地页数据统计
     */
    public function fallData()
    {
        if(IS_POST){

            $data = I();
            $agent_info     = $_REQUEST["agent"];
            $agent_p_info   = $_REQUEST["agent_p"];
            $start          = $data["start"]? $data["start"]: 0;
            $pageSize       = $data["limit"]? $data["limit"]: 30;

            //处理搜索条件
            $agentArr = dealList($agent_info,$agent_p_info);

            if($agentArr['agent']){
                $map['agent'] = array('in',$agentArr['agent']); 
            }else{
                $agent_infos = $map_arr =  array();

                if(!empty($agentArr['pAgent'])){
                    $map_arr['_string'] = "id IN ('".implode("','", $agentArr['pAgent'])."') OR pid IN ('".implode("','", $agentArr['pAgent'])."')";
                }

                if($data['advteruser_id']){
                    $map_arr['advteruser_id'] = $data['advteruser_id'];
                }

                if($data['game_id']){
                    $map_arr['game_id'] = $data['game_id'];
                }
                if($map_arr){
                    $agent_infos = array_keys(getDataList('agent','agent',C('DB_PREFIX_API'),$map_arr));
                }


                $arr = $this->agentArr;
                if($agent_infos){
                        $arr = array_intersect($arr, $agent_infos);
                }elseif($map_arr && !$agent_infos){
                    exit(json_encode(array('rows'=>array(), 'results'=>0)));
                }

                sort($arr);
                if(count($arr) < 1) $arr[] = '-1';
                $map['agent'] = array('in',$arr);

            }

            if($data['startDate'] && $data['startDate']){
                $map['dayTime'] = array(array('egt',$data['startDate']), array('lt',date('Y-m-d',strtotime($data['endDate'].'+1 day'))), 'and');
            }
            $res        = D('Admin')->getBuiList("sp_fall_day", $map, $start, $pageSize, C('DB_PREFIX'), 'openNum');

            $results    = $res['count'];
            $game_list  = getDataList('game','id',C('DB_PREFIX_API'));
            $agent_list = getDataList('agent','agent',C('DB_PREFIX_API'));
            $advterlist = getDataList('advter_list','id',C('DB_PREFIX'),array('agent'=>array('IN',$this->agentArr)));

            $advteruser_list = getDataList('advteruser','id',C('DB_PREFIX'));
            foreach ($res['list'] as $key=>$val){
                $res['list'][$key]['openNum']        = intval($val['openNum']);
                $res['list'][$key]['advname']        = $advterlist[$val['advid']]['adv_name'];
                $res['list'][$key]['disOpenNum']     = intval($val['disOpenNum']);
                $res['list'][$key]['downloadNum']    = intval($val['downloadNum']);
                $res['list'][$key]['disDownloadNum'] = intval($val['disDownloadNum']);
                $res['list'][$key]['gameName']   = $game_list[$agent_list[$val['agent']]['game_id']]['gameName'];
                $res['list'][$key]['advteruser'] = $advteruser_list[$agent_list[$val['agent']]['advteruser_id']]['company_name'];
                $res['list'][$key]['rate']       = numFormat($val['disDownloadNum']/$val['disOpenNum'],true);
                $rows[] = $res['list'][$key];
            }

            $arr = array('rows'=>empty($rows) ? array() : $rows, 'results'=>$results);
            exit(json_encode($arr));
        }else{
            if(IS_AJAX){
                $response = $this->fetch();
                $this->ajaxReturn(array('status'=>1,'_html'=>$response));
            }else{
                $this->display();
            }
        }
    }

    /**
     * 投放数据概况统计
     */
    public function advData()
    {
        if(IS_POST){

            $data = I();
            $agent_info = $_REQUEST['agent'];

            !$data['game_id'] && exit(json_encode(array('rows'=>array(), 'results'=>0)));

            //处理搜索条件
            if(is_array($agent_info) && in_array('--请选择渠道号--', $agent_info)){
                unset($agent_info[array_search('--请选择渠道号--', $agent_info)]);
            }elseif(is_string($agent_info) && $agent_info == '--请选择渠道号--'){
                $agent_info = '';
            }elseif(is_string($agent_info)){
                $agent_info = explode(',', $agent_info);
            }

            $start      = $data['start']? $data['start']: 0;
            $pageSize   = $data['limit']? $data['limit']: 30;
            $where = '1';
            if($agent_info){
                $data['agent'] = $agent_info;
                $where .= ' and a.agent in("'.implode('","',$data['agent']).'")';
                $map2['a.regAgent'] = array('in',$data['agent']);
                $map['regAgent'] = array('in',$data['agent']);
            }else{
                if(empty($data['creater'])){
                    //权限控制
                    $where .= ' and a.agent in("'.implode('","', $this->agentArr).'")';
                    $map2['a.regAgent'] = array('in',$this->agentArr);
                    $map['regAgent'] = array('in',$this->agentArr);
                }else{
                    if($data['advteruser_id']){
                        $arrmap = array('creater'=>$data['creater'],'advteruser_id'=>$data['advteruser_id']);
                    }else{
                        $arrmap = array('creater'=>$data['creater']);
                    }

                    $createrAgent = array_keys(getDataList('agent','agent',C('DB_PREFIX_API'),$arrmap));
                    $arr = array_intersect($this->agentArr, $createrAgent);
                    sort($arr);
                    if(count($arr) < 1) $arr[] = 0;
                    $where .= ' and a.agent in("'.implode('","', $arr).'")';
                    $map2['a.regAgent'] = array('in',$arr);
                    $map['regAgent'] = array('in',$arr);
                }
            }

            if($data['game_id']){
                $where .= ' and a.game_id='.$data['game_id'];
                $map['game_id'] = $data['game_id'];
                $map2['a.game_id'] = $data['game_id'];
            }

            if($data['startDate'] && $data['startDate']){
                $where .= ' and a.dayTime>="'.$data['startDate'].'" and a.dayTime<"'.date('Y-m-d',strtotime($data['endDate'].'+1 day')).'"';
                $map2['a.time'] = array(array('egt',strtotime($data['startDate'])), array('lt',strtotime(date('Y-m-d',strtotime($data['endDate'].'+1 day')))), 'and');
                $map['paymentTime'] = array(array('egt',strtotime($data['startDate'])), array('lt',strtotime(date('Y-m-d',strtotime($data['endDate'].'+1 day')))), 'and');
            }

            if($data['advteruser_id']){
                $where .= ' and a.advteruser_id = '.$data['advteruser_id'];
                if(empty($agent_info) && empty($data['creater'])){
                    $agentArr = array_keys(getDataList('agent','agent',C('DB_PREFIX_API'),array('advteruser_id'=>$data['advteruser_id'])));
                    $arr = array_intersect($this->agentArr, $agentArr);
                    sort($arr);
                    $map2['a.regAgent'] = array('in',$arr);
                    $map['regAgent'] = array('in',$arr);
                }
            }
            if($data['os'] == 2){
                $where     .= ' and a.gameType = 2';
                $map2['b.gameType'] = array('eq',2);
                $map['type'] = array('eq',2);
            }else{
                $where     .= ' and a.gameType = 1';
                $map2['b.gameType'] = array('neq',2);
                $map['type'] = array('neq',2);

            }
            $res        = D('Admin')->getAdvData($map,$start,$pageSize,$where,$map2);

            $allPayUser   = $res['count']['allPayUser'];
            $DAU          = $res['count']['login']['DAU'];
            $oldUserLogin = $res['count']['login']['oldUserLogin'];
            $game_list  = getDataList('game','id',C('DB_PREFIX_API'));
            $agent_list = getDataList('agent','agent',C('DB_PREFIX_API'));
            $advteruser_list = getDataList('advteruser','id',C('DB_PREFIX'));
            $total      = $newRes = array();
            foreach ($res['list'] as $key=>$val){
                $newRes[$key] = $val;
                $res['list'][$key]['agent']          = '-';
                //游戏的汇总
                $res['list'][$key]['allPay']         = floatval($val['allPay']);
                $res['list'][$key]['cost']           = floatval($val['cost']);
                $res['list'][$key]['gameName']       = $game_list[$val['game_id']]['gameName'];
                $res['list'][$key]['advteruserName'] = '-';
                $res['list'][$key]['regCost']        = round(floatval($val['cost']/$val['newUser']),2);//注册单价

                $res['list'][$key]['newPayRate']     = numFormat(($val['newPayUser']/$val['newUser']),true);//新增付费率
                $res['list'][$key]['actPayRate']     = numFormat(($val['allPayUser']/$val['DAU']),true);//活跃付费率
                $res['list'][$key]['dayRate']        = numFormat(($val['newPay']/$val['cost']),true);//1日回本率
                $res['list'][$key]['allPayRate']     = numFormat(($val['allPayNow']/$val['cost']),true);//至今回本率
                $res['list'][$key]['totalPayRate']   = numFormat(($val['totalPay']/$val['cost']),true); //累计回本率
                $res['list'][$key]['regRate']        = numFormat(($val['newUser']/$val['newDevice']),true); //注册转化率
                $res['list'][$key]['day1']           = numFormat(($val['day1']/$val['newUser']),true); //次留

                $res['list'][$key]['ARPU']           = round(floatval($val['allPay']/$val['DAU']),2);//活跃ARPU
                $res['list'][$key]['ARPPU']           = round(floatval($val['allPay']/$val['allPayUser']),2);//活跃ARPPU

                $res['list'][$key]['newARPU']        = round(floatval($val['newPay']/$val['newUser']),2);//新增ARPU
                $res['list'][$key]['newARPPU']        = round(floatval($val['newPay']/$val['newPayUser']),2);//新增ARPPU

                //渠道的汇总

                //子级汇总
                $childrenSum = $this->summarys($res['list'][$key]['children']);
                $childrenSum['dayTime'] = '汇总';

                foreach ($res['list'][$key]['children'] as $k => $v) {
                    $res['list'][$key]['children'][$k]['allPay']         = floatval($v['allPay']);
                    $res['list'][$key]['children'][$k]['cost']           = floatval($v['cost']);
                    $res['list'][$key]['children'][$k]['gameName']       = $game_list[$v['game_id']]['gameName'];
                    $res['list'][$key]['children'][$k]['advteruserName'] = $advteruser_list[$v['advteruser_id']]['company_name'];
                    $res['list'][$key]['children'][$k]['regCost']        = round(floatval($v['cost']/$v['newUser']),2);//注册单价

                    $res['list'][$key]['children'][$k]['newPayRate']     = numFormat(($v['newPayUser']/$v['newUser']),true);//新增付费率
                    $res['list'][$key]['children'][$k]['actPayRate']     = numFormat(($v['allPayUser']/$v['DAU']),true);//活跃付费率

                    $res['list'][$key]['children'][$k]['dayRate']        = numFormat(($v['newPay']/$v['cost']),true);//1日回本率
                    $res['list'][$key]['children'][$k]['allPayRate']     = numFormat(($v['allPayNow']/$v['cost']),true);//至今回本率
                    $res['list'][$key]['children'][$k]['totalPayRate']   = numFormat(($v['totalPay']/$v['cost']),true); //累计回本率
                    $res['list'][$key]['children'][$k]['regRate']        = numFormat(($v['newUser']/$v['newDevice']),true); //注册转化率

                    $res['list'][$key]['children'][$k]['day1']           = numFormat(($v['day1']/$v['newUser']),true); //次留

                    $res['list'][$key]['children'][$k]['ARPU']           = round(floatval($v['allPay']/$v['DAU']),2);//活跃ARPU
                    $res['list'][$key]['children'][$k]['ARPPU']           = round(floatval($v['allPay']/$v['allPayUser']),2);//活跃ARPPU

                    $res['list'][$key]['children'][$k]['newARPU']        = round(floatval($v['newPay']/$v['newUser']),2);//新增ARPU
                    $res['list'][$key]['children'][$k]['newARPPU']        = round(floatval($v['newPay']/$v['newPayUser']),2);//新增ARPPU
                }

                $res['list'][$key]['children'][] = $childrenSum;
                if($data['export'] == 1){
                    foreach ($res['list'][$key]['children'] as $ck => $cv) {
                        $rows[] = $cv;
                    }
                }

            }
            //父级汇总
            $parentSum = $this->summarys($newRes,1,$allPayUser,$DAU,$oldUserLogin);
            $parentSum['dayTime'] = '汇总';
            $res['list'][] = $parentSum;
            if($data['export'] != 1){
                $rows = $res['list'];
            }
            unset($res);
            // $pagesummary = $this->summarys($rows);

            if($data['export'] == 1){

                $col = array('dayTime'=>'注册日期', 'gameName'=>'游戏名称','agent'=>'渠道号', 'advteruserName'=>'渠道商','cost'=>'成本','newDevice'=>'新增设备数','newUser'=>'新增账号数','regRate'=>'注册转化率','regCost'=>'注册单价','newPay'=>'新增充值金额','newPayUser'=>'新增充值人数','newPayRate'=>'新增付费率','newARPU'=>'新增ARPU','newARPPU'=>'新增ARPPU','allPay'=>'当天充值金额','allPayUser'=>'充值人数','actPayRate'=>'活跃付费率','ARPU'=>'活跃ARPU','ARPPU'=>'活跃ARPPU','day1'=>'次留','oldUserLogin'=>'老用户活跃数','DAU'=>'DAU','dayRate'=>'1日回本率','allPayNow'=>'区间充值金额','allPayRate'=>'区间回本率','totalPay'=>'至今充值金额','totalPayRate'=>'至今回本率');

                array_unshift($rows, $col);
                /*$pagesummary['dayTime'] = '汇总';

                array_push($rows,$pagesummary);*/
                export_to_csv($rows,'投放数据概况',$col);
                exit();
            }
            $arr = array('rows'=>$rows ? $rows : array(), 'results'=>$results);
            exit(json_encode($arr));
        }else{
            if(IS_AJAX){
                $response = $this->fetch();
                $this->ajaxReturn(array('status'=>1,'_html'=>$response));
            }else{
                $this->display();
            }
        }
    }

    /**
     * 投放数据概况统计
     */
    public function advDataBak()
    {
        if(IS_POST){

            $data = I();
            $agent_info = $_REQUEST['agent'];
            $agent_p_info = $_REQUEST['agent_p'];

            //处理搜索条件
            $agentArr = dealAllList($agent_info,$agent_p_info);
            
                
            $start      = $data['start']? $data['start']: 0;
            $pageSize   = $data['limit']? $data['limit']: 60;
            $where = '1';
            if($agentArr['info']){
                $data['agent'] = $agentArr['info'];
                $where .= ' and a.agent in("'.implode('","',$data['agent']).'")';
                $map2['regAgent'] = array('in',$data['agent']);
                $map['agent'] = array('in',$data['agent']);
            }else{

                $agent_infos = $map_arr = array();

                if(!empty($agentArr['pinfo'])){
                    $map_arr['_string'] = "id IN ('".implode("','", $agentArr['pinfo'])."') OR pid IN ('".implode("','", $agentArr['pinfo'])."')";
                }

                if($data['advteruser_id']){
                    $map_arr['advteruser_id'] = $data['advteruser_id'];
                }

                if($map_arr){
                    $agent_infos = array_keys(getDataList('agent','agent',C('DB_PREFIX_API'),$map_arr));
                }

                $arr = $this->agentArr;
                if($agent_infos){
                    $arr = array_intersect($arr, $agent_infos);
                }elseif($map_arr && !$agent_infos){
                    $arr = array();
                }

                sort($arr);
                if(count($arr) < 1) $arr[] = '-1';
                $where .= ' and a.agent in("'.implode('","', $arr).'")';
                $map2['regAgent'] = array('in',$arr);
                $map['agent'] = array('in',$arr);

            }

            $game_id = dealAllList($data['game_id']);
            $tmp = $data['id'] ? explode('_', $data['id']) : array();

            if(count($tmp) > 1 && $tmp[0] == 'and'){
                $where .= ' and a.game_id ='.$tmp[2];
                $map['game_id'] = $map2['game_id'] = $tmp[2];
                $game_id['info'] = array($tmp[2]);
                $data['startDate'] = $data['endDate'] = $tmp[1];
            }elseif($game_id['info']){
                $data['game_id'] = $game_id['info'];
                $where .= ' and a.game_id IN("'.implode('","', $data['game_id']).'")';
                $map['game_id'] = array('IN',$data['game_id']);
                $map2['game_id'] = array('IN',$data['game_id']);
            }

            //是否查询一个月的数据
            $dayOne     = date("Y-m-01", strtotime($data["startDate"]));
            $isMonth    = 0;
            if ($dayOne && $dayOne == $data["startDate"] && date("Y-m-d", strtotime($dayOne." +1 month -1 day")) == $data["endDate"]) {
                $isMonth    = 1;
            }

            if($data['startDate'] && $data['startDate']){
                
                if( (strtotime($data['startDate']) < strtotime($this->userDataTime['startDate'])) && $this->userDataTime['startDate'] ) {
                    $data['startDate'] = $this->userDataTime['startDate'];
                }

                if( (strtotime($data['endDate']) > strtotime($this->userDataTime['endDate'])) && $this->userDataTime['endDate'] ) {
                    $data['endDate'] = $this->userDataTime['endDate'];
                }

                $where .= ' and a.dayTime>="'.$data['startDate'].'" and a.dayTime<"'.date('Y-m-d',strtotime($data['endDate'].'+1 day')).'"';
                if($isMonth == 1){
                    $map2['a.dayTime'] = array(array('egt',$data['startDate']), array('lt',date('Y-m-d',strtotime($data['endDate'].'+1 day'))), 'and');
                }else{
                    $map2['dayTime'] = array(array('egt',$data['startDate']), array('lt',date('Y-m-d',strtotime($data['endDate'].'+1 day'))), 'and');
                }
                $map['paymentTime'] = array(array('egt',strtotime($data['startDate'])), array('lt',strtotime(date('Y-m-d',strtotime($data['endDate'].'+1 day')))), 'and');
            }else{
                exit(json_encode(array('hasError'=>true, 'error'=>'日期必选')));
            }

            $where     .= ' and a.gameType = 1';
            $map['type'] = array('eq',1);

            if(!$data['system']){
                if(count($tmp) > 1 && $tmp[0] == 'and'){
                    $res1   = D('Admin')->getAdvDataBak($map,$start,$pageSize,$where,$map2,$isMonth,$game_id['info'],$data['id'],$data['startDate'],$data['endDate']);
                } elseif($tmp[0] == 'ios'){
                    $res2   = $this->advDataIosBak();
                }else{
                    $res1   = D('Admin')->getAdvDataBak($map,$start,$pageSize,$where,$map2,$isMonth,$game_id['info'],$data['id'],$data['startDate'],$data['endDate']);
                    $res2   = $this->advDataIosBak();
                }
                if($res1 && $res2){
                $res['list']  = array_merge($res1['list'],$res2['list']);
                $res['count'] = array(
                    'allPayUser'=>($res1['count']['allPayUser']+$res2['count']['allPayUser']),
                    'login'=>array(
                        'DAU'=>($res1['count']['login']['DAU']+$res2['count']['login']['DAU']),
                        'oldUserLogin'=>($res1['count']['login']['oldUserLogin']+$res2['count']['login']['oldUserLogin'])
                        )
                    );
                }elseif($res1){
                    $res = $res1;
                }elseif($res2){
                    $res = $res2;
                }

            }elseif($data['system'] == 1){
                $res    = D('Admin')->getAdvDataBak($map,$start,$pageSize,$where,$map2,$isMonth,$game_id['info'],$data['id'],$data['startDate'],$data['endDate']);
            }elseif($data['system'] == 2){
                $res    = $this->advDataIosBak();
            }

            $allPayUser   = $res['count']['allPayUser'];
            $DAU          = $res['count']['login']['DAU'];
            $oldUserLogin = $res['count']['login']['oldUserLogin'];
            $game_list    = getDataList('game','id',C('DB_PREFIX_API'));
            $agent_list   = getDataList('agent','agent',C('DB_PREFIX_API'));
            $event_list   = getDataList('events','id');

            $advteruser_list = getDataList('advteruser','id',C('DB_PREFIX'));
            $total      = $newRes = $sortArr = array();
            foreach ($res['list'] as $key=>$val){
                    //如果游戏汇总全是0则去除
                    if(empty($val['allPayUser']) && empty($val['newDevice']) && empty($val['newUser']) && empty($val['oldUserLogin']) && empty($val['DAU']) && empty($val['day1']) && empty((float)$val['allPay']) && empty((float)$val['newPay']) && empty($val['newPayUser']) && empty((float)$val['totalPay']) && empty((float)$val['cost'])){
                        unset($res['list'][$key]);
                        continue;
                    }


            }
            sort($res['list']);
            
            if($data['id']){
                $sortArr = array_column($res['list'], 'newUser');
                array_multisort($sortArr, SORT_DESC, $res['list']);
                unset($sortArr);
            } 

            foreach ($res['list'] as $key=>$val){
                $newRes[$key] = $val;

                if(isset($tmp) && count($tmp)>1){
                    if($val['advterId']){
                        $res['list'][$key]['parentId'] = $data['id'].'_'.$val['day_time'].'_'.$val['advterId'];
                    }else{
                        $res['list'][$key]['parentId'] = $data['id'].'_'.$val['day_time'].'_'.$val['agent'];
                    }
                    $res['list'][$key]['agent']          = $agent_list[$val['agent']]['gameType'] == 1 ? $val['agent'] : $event_list[$val['advterId']]['events_name'];  //推广活动名称
                    $res['list'][$key]['advteruserName'] = $advteruser_list[$val['advteruser_id']]['company_name'];

                }else{
                    $res['list'][$key]['agent']     = '-';
                    $res['list'][$key]['advteruserName'] = '-';
                    if($val['advterId']){
                        $res['list'][$key]['parentId'] = 'ios_'.$val['dayTime'].'_'.$val['agent'];
                    }else{
                        $res['list'][$key]['parentId'] = 'and_'.$val['dayTime'].'_'.$val['game_id'];
                    }
                }

                $res['list'][$key]['state'] = count($tmp) < 1 ? 'closed' : 'open';

                //游戏的汇总
                $res['list'][$key]['allPay']         = floatval($val['allPay']);
                $res['list'][$key]['cost']           = floatval($val['cost']);
                $res['list'][$key]['gameName']       = $agent_list[$val['agent']]['gameType'] == 1 ? $game_list[$val['game_id']]['gameName'] : $agent_list[$val['agent']]['agentName'];

                $res['list'][$key]['regCost']        = round(floatval($val['cost']/$val['newUser']),2);//注册单价

                $res['list'][$key]['newPayRate']     = numFormat(($val['newPayUser']/$val['newUser']),true);//新增付费率
                $res['list'][$key]['actPayRate']     = numFormat(($val['allPayUser']/$val['DAU']),true);//活跃付费率
                $res['list'][$key]['dayRate']        = numFormat(($val['newPay']/$val['cost']),true);//1日回本率
                $res['list'][$key]['allPayRate']     = numFormat(($val['allPayNow']/$val['cost']),true);//至今回本率
                $res['list'][$key]['totalPayRate']   = numFormat(($val['totalPay']/$val['cost']),true); //累计回本率
                $res['list'][$key]['regRate']        = numFormat(($val['disUdid']/$val['newDevice']),true);

                $res['list'][$key]['day1']           = numFormat(($val['day1']/$val['newUser']),true); //次留
                $res['list'][$key]['ARPU']           = round(floatval($val['allPay']/$val['DAU']),2);//活跃ARPU
                $res['list'][$key]['ARPPU']           = round(floatval($val['allPay']/$val['allPayUser']),2);//活跃ARPPU

                $res['list'][$key]['newARPU']        = round(floatval($val['newPay']/$val['newUser']),2);//新增ARPU
                $res['list'][$key]['newARPPU']        = round(floatval($val['newPay']/$val['newPayUser']),2);//新增ARPPU

            }

            //父级汇总
            if($data['isCount'] == 1){
                $parentSum = $this->summarys($newRes,1,$allPayUser,$DAU,$oldUserLogin);
                //一个月的数据暂时先注释，后期删去
                if ($isMonth && $parentSum['DAU'] == 0 && $parentSum['oldUserLogin'] == 0) {
                    $parentSum['DAU']           = "-";
                    $parentSum['oldUserLogin']  = "-";
                    $parentSum['actPayRate']    = "-";//活跃付费率
                    $parentSum['ARPU']          = "-";//活跃ARPU
                }
                $parentSum['parentId'] = $data['id'].'_sum';
                $parentSum['dayTime'] = (isset($tmp) && count($tmp)>1) ? '子级汇总' : '父级汇总';
                $res['list'][] = $parentSum;
            }
            
            if($data['export'] != 1){
                $rows = $res['list'];
            }
            unset($res);

            if($data['export'] == 1){
                $rows[] = $parentSum;
                $col = array('dayTime'=>'注册日期', 'gameName'=>'游戏名称','agent'=>'渠道号', 'advteruserName'=>'渠道商','cost'=>'成本','newDevice'=>'新增设备数','disUdid'=>'唯一注册数','newUser'=>'新增账号数','regRate'=>'注册转化率','regCost'=>'注册单价','newPay'=>'新增充值金额','newPayUser'=>'新增充值人数','newPayRate'=>'新增付费率','newARPU'=>'新增ARPU','newARPPU'=>'新增ARPPU','allPay'=>'当天充值金额','allPayUser'=>'充值人数','actPayRate'=>'活跃付费率','ARPU'=>'活跃ARPU','ARPPU'=>'活跃ARPPU','day1'=>'次留','oldUserLogin'=>'老用户活跃数','DAU'=>'DAU','dayRate'=>'1日回本率','allPayNow'=>'区间充值金额','allPayRate'=>'区间回本率','totalPay'=>'至今充值金额','totalPayRate'=>'至今回本率');
                //各个包创建人的注册和充值情况
                $rows = $this->principalInfo($rows);
                $rows = $this->channelInfo($rows);

                array_unshift($rows, $col);

                export_to_csv($rows,'投放数据概况',$col);
                exit();
            }

             if(!$data['id']){
                echo json_encode(array('rows'=>($rows ? $rows : array()), 'total'=>count(($rows ? $rows : array()))));
            }else{
                echo json_encode($rows);
            }
        }else{
            if(IS_AJAX){
                $response = $this->fetch();
                $this->ajaxReturn(array('status'=>1,'_html'=>$response));
            }else{
                $this->display();
            }
        }
    }

    /**
     * IOS投放数据概况统计
     */
    public function advDataIosBak(){
        if(IS_POST){
            $data = I();
            $agent_info   = $_REQUEST['advter_id'];
            $agent_p_info = $_REQUEST['events_groupId'];
            $agent = $_REQUEST['agent'];

            //处理搜索条件
            $start      = $data['start']? $data['start']: 0;
            $pageSize   = $data['limit']? $data['limit']: 60;
            $where = '1';

            $agentArr   = dealAllList($agent_info,$agent_p_info);

            if($agentArr['info']){
                $where .= ' and a.advterId in("'.implode('","',$agentArr['info']).'")';
                $map2['advter_id'] = array('in',$agentArr['info']);
                $map['advter_id'] = array('in',$agentArr['info']); 
            }else{
                $agent_infos = $map_arr = array();

                if(!empty($agentArr['pinfo'])){
                    $map_arr['_string'] = "events_groupId IN ('".implode("','", $agentArr['pinfo'])."')";
                }

                if($data['advteruser_id']){
                    $map_arr['advteruser_id'] = $data['advteruser_id'];
                }

                $agent = dealAllList($agent);
                if($agent['info']){
                    $map_arr['agent'] = array('IN',$agent['info']);
                }
                
                $game_info = dealAllList($data['game_id']);
                if($game_info['info']){
                    $map_arr['game_id'] = array('IN',$game_info['info']);
                }

                $agent_infos = array_keys(getDataList('events','id',C('DB_PREFIX'),$map_arr));

                if($agent_infos){
                    $arr = array_intersect($this->events, $agent_infos);
                }elseif($map_arr && !$agent_infos){
                    $arr = array('-1');
                }
                sort($arr);

                $where .= ' and a.advterId in("'.implode('","',$arr).'")';
                $map2['advter_id'] = array('in',$arr);
                $map['advter_id'] = array('IN',$arr);
            }

            $tmp = $data['id'] ? explode('_', $data['id']) : array();

            if(count($tmp) > 1){
                $data['startDate'] = $data['endDate'] = $tmp[1];
                $map['agent'] = $tmp[2];
                $where .= ' and a.agent="'.$tmp[2].'"';
            }
            //是否查询一个月的数据
            $dayOne     = date("Y-m-01", strtotime($data["startDate"]));
            $isMonth    = 0;
            if ($dayOne && $dayOne == $data["startDate"] && date("Y-m-d", strtotime($dayOne." +1 month -1 day")) == $data["endDate"]) {
                $isMonth    = 1;
            }

            if($data['startDate'] && $data['startDate']){
                if( (strtotime($data['startDate']) < strtotime($this->userDataTime['startDate'])) && $this->userDataTime['startDate'] ) {
                    $data['startDate'] = $this->userDataTime['startDate'];
                }

                if( (strtotime($data['endDate']) > strtotime($this->userDataTime['endDate'])) && $this->userDataTime['endDate'] ) {
                    $data['endDate'] = $this->userDataTime['endDate'];
                }

                $where .= ' and a.dayTime>="'.$data['startDate'].'" and a.dayTime<"'.date('Y-m-d',strtotime($data['endDate'].'+1 day')).'"';
                if($isMonth == 1){
                    $map2['a.dayTime'] = array(array('egt',$data['startDate']), array('lt',date('Y-m-d',strtotime($data['endDate'].'+1 day'))), 'and');
                }else{
                    $map2['dayTime'] = array(array('egt',$data['startDate']), array('lt',date('Y-m-d',strtotime($data['endDate'].'+1 day'))), 'and');
                }
                $map['paymentTime'] = array(array('egt',strtotime($data['startDate'])), array('lt',strtotime(date('Y-m-d',strtotime($data['endDate'].'+1 day')))), 'and');
            }

            $map['type'] = array('eq',2);

            //去除advter_id为15的测试数据
            $where .= ' and a.advterId <> 15';
            $map2['_string'] = ' advter_id <> 15';
            $map['_string']  = ' advter_id <> 15';

            $res = D('Admin')->getAdvDataIosBak($map,$start,$pageSize,$where,$map2,$isMonth,$data['id'],$data['startDate'],$data['endDate']);
            return $res;
        }
    }

    //数据汇总
    private function summarys($data,$type=2,$allPayUser=0,$DAU=0,$oldUserLogin=0)
    {

        $sum = array();
        $data_num = count($data);

        $now = strtotime(date('Y-m-d'));
        
        //开始统计
        foreach ($data as $k => $val) {
            $days = floor(($now - strtotime($val['dayTime']))/86400);
            $sum['cost']         += $val['cost'];
            $sum['newUser']      += $val['newUser'];
            $sum['newDevice']    += $val['newDevice'];
            $sum['regCost']      += $val['regCost'];
            $sum['allPay']       += $val['allPay'];
            $sum['allPayUser']   += $val['allPayUser'];
            $sum['oldUserLogin'] += $val['oldUserLogin'];
            $sum['DAU']          += $val['DAU'];
            $sum['newPayRate']   += $val['newPayRate'];
            $sum['newARPU']      += $val['newARPU'];
            $sum['ARPU']         += $val['ARPU'];
            $sum['newPay']       += $val['newPay'];
            $sum['newPayUser']   += $val['newPayUser'];
            $sum['allPayNow']    += $val['allPayNow'];
            $sum['totalPay']     += $val['totalPay'];
            $sum['remainNewUser']+= ($type == 1 && $days>=2) ? $val['newUser'] : 0;
            $sum['day1']         += $val['day1'];
        }
        //开始计算
        
        if($type == 1){
            // if(session('admin.uid') == 1) var_dump($sum['remainNewUser'],$sum['day1'],$data);
            $sum['DAU'] = $DAU;
            $sum['oldUserLogin'] = $oldUserLogin;
            $sum['allPayUser'] = $allPayUser;
            $sum['day1']   = numFormat(($sum['day1']/$sum['remainNewUser']),true); //次留
        }else{
            $sum['day1']       = numFormat(($sum['day1']/$sum['newUser']),true); //次留
        }

        $sum['regRate']    = numFormat(($sum['newUser']/$sum['newDevice']),true); //注册转化率
        $sum['regCost']    = round(floatval($sum['cost']/$sum['newUser']),2);//注册单价
        $sum['newPayRate'] = numFormat(($sum['newPayUser']/$sum['newUser']),true);//新增付费率
        $sum['newARPU']    = round(floatval($sum['newPay']/$sum['newUser']),2);//新增ARPU
        $sum['newARPPU']   = round(floatval($sum['newPay']/$sum['newPayUser']),2);//新增ARPPU
        $sum['actPayRate'] = numFormat(($sum['allPayUser']/$sum['DAU']),true);//活跃付费率
        $sum['ARPU']       = round(floatval($sum['allPay']/$sum['DAU']),2);//活跃ARPU
        $sum['ARPPU']      = round(floatval($sum['allPay']/$sum['allPayUser']),2);//活跃ARPPU
        

        $sum['dayRate']      = numFormat(($sum['newPay']/$sum['cost']),true);//1日回本率
        $sum['allPayRate']   = numFormat(($sum['allPayNow']/$sum['cost']),true);//区间回本率
        $sum['totalPayRate'] = numFormat(($sum['totalPay']/$sum['cost']),true); //至今回本率


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
        if(IS_POST){
            $data = I();
            $map = array();

            $data['principal']      && $map['principal']      = $data['principal'];
            $data['gameType']       && $map['gameType']       = $data['gameType'];
            $data['media']          && $map['media']          = array('like','%'.$data['media'].'%');
            $data['gameName']       && $map['gameName']       = $data['gameName'];
            $data['agent']          && $map['agent']          = $data['agent'];
            $data['channelAccount'] && $map['channelAccount'] = $data['channelAccount'];
            $data['startMonth']     && $map['costMonth'][] = array('egt',date('Y-m-d',strtotime($data['startMonth'])));
            $data['endMonth']       && $map['costMonth'][] = array('lt',date('Y-m-d',strtotime($data['endMonth'].'+1 day')));
            if(session('admin.role_id') !=1 && session('admin.role_id') !=3) $map['departmentId'] = session('admin.partment');
            $start=I('start',0,'intval');
            $pageSize=I('limit',30,'intval');
            $list = D('Admin')->getBuiList($this->table,$map,$start,$pageSize);
            /*$principal_list = getDataList('principal','id');
            $game_list = getDataList('game','id',C('DB_PREFIX_API'));*/
            $cost = D('Admin')->getAdvterSum($map);
            foreach ($list['list'] as $key=>$val){
                $list['list'][$key]['principalName'] = $val['principal'];
                $list['list'][$key]['gameName'] = $val['gameName'];
                $list['list'][$key]['cost'] = $val['cost'];
                $list['list'][$key]['opt'] =  '<a href="javascript:;" onclick="costEdit('.$val['id'].',this)">编辑</a> | <a href="javascript:;" onclick="costDelete('.$val['id'].',this)">删除</a>';
                $rows[] = $list['list'][$key];
            }
            unset($res);

            $pageSummary = array('cost'=>array_sum(array_column($rows, 'cost')));

            if($data['export'] == 1){
                $col = array('costMonth'=>'日期','principalName'=>'负责人','gameName'=>'游戏','gameType'=>'系统','media'=>'媒体','agent'=>'包号','channelAccount'=>'渠道账号','cost'=>'支出金额');
                array_unshift($rows, $col);
                $pageSummary['costMonth']  = '汇总';
                array_push($rows,$pageSummary);
                export_to_csv($rows,'广告支出',$col);
                exit();
            }

            $arr = array('rows'=>(empty($rows) ? array() : $rows),'results'=>$list['count'],'summary'=>array('cost'=>number_format($cost,2)));
            exit(json_encode($arr));
        }else{
            $principal = M('advter_cost')->field('distinct principal as principal')->select();
            $gameName  = M('advter_cost')->field('distinct gameName as gameName')->select();
            $gameType  = M('advter_cost')->field('distinct gameType as gameType')->select();
            $principal_list = $gameName_list = $gameType_list = '<option value="0">全部</option>';

            //负责人
            foreach($principal as $k=>$v){
                $principal_list .= "<option value='{$v['principal']}'>{$v['principal']}</option>";
            }
            //游戏名
            foreach ($gameName as $key => $value) {
                $gameName_list  .= "<option value='{$value['gameName']}'>{$value['gameName']}</option>";
            }
            //系统
            foreach ($gameType as $key => $value) {
                $gameType_list  .= "<option value='{$value['gameType']}'>{$value['gameType']}</option>";
            }
            $this->assign('principal_list',$principal_list);
            $this->assign('gameName_list',$gameName_list);
            $this->assign('gameType_list',$gameType_list);
            $this->display();
        }
    }

    /**
     * 导入广告成本
     */
    public function importCost()
    {
        if(IS_POST){
            if(!$_FILES['costFile']['name'] ){
                $this->error('没有传入Excel');
            }
            //文件上传
            $file_info = excel_file_upload('Cost');
            if($file_info && $file_info != '没有文件被上传！'){
                //获取文件数据并且转数组
                $fileName = './Uploads/'.$file_info['costFile']['savepath'].$file_info['costFile']['savename'];
                $data = excel_to_array($fileName);
                if($data){
                    /*$principal_list = getDataList('principal','principal_name',C('DB_PREFIX'));
                    $game_list      = getDataList('game','gameName',C('DB_PREFIX_API'));*/

                    $arr = array();
                    unset($data[1]);//第一个行为标题，不需要入库
                    foreach($data as $key => $val){
                        $arr[] = array(
                            'costMonth'      => is_null(date('Y-m-d',strtotime($val[0]))) ? '' : date('Y-m-d',strtotime($val[0])),
                            'principal'      => is_null($val[1])  ? '' : $val[1],
                            'gameName'       => is_null($val[2])  ? '' : $val[2],
                            'gameType'       => is_null($val[3])  ? '' : $val[3],
                            'agent'          => is_null($val[4])  ? '' : $val[4],
                            'channelAccount' => is_null($val[6])  ? '' : $val[6],
                            'cost'           => is_null($val[7])  ? '' : $val[7],
                            'media'          => is_null($val[5])  ? '' : $val[5],
                            'createTime'     => time(),
                            'creater'        => session('admin.realname'),
                            'departmentId'   => session('admin.partment'),
                        );
                    }
                    if($arr && D('Admin')->commonAddAll('advter_cost',$arr)){
                        $this->success('成本导入成功');
                    }else{
                        $this->error('成本导入失败');
                    }
                }
            }
        }else{
            if(IS_AJAX){
                $response = $this->fetch();
                $this->ajaxReturn(array('status'=>1,'_html'=>$response));
            }else{
                $this->display();
            }
        }
        
    }

    /**
     * 月度指标列表
     */
    public function monthTarget()
    {
        if(IS_POST){
            $data = I();
            $map = array();
            $principals = trim($_SESSION['admin']['principal_ids']);
            if($data['principalId']){
            	$map['principalId'] = $data['principalId'];
            }elseif($principals !== '0'){
            	$map['principalId'] = array('in',explode(',', $principals));
            };

            $data['gameId'] && $map['gameId'] = $data['gameId'];
            $data['startMonth'] && $map['costMonth'][] = array('egt',date('Y-m-01',strtotime($data['startMonth'])));
            $data['endMonth'] && $map['costMonth'][] = array('lt',date('Y-m-01',strtotime($data['endMonth'].'+1 month')));
            $start=I('start',0,'intval');
            $pageSize=I('limit',30,'intval');
            $list = D('Admin')->getBuiList($this->table,$map,$start,$pageSize);
            $principal_list = getDataList('principal','id');
            $game_list = getDataList('game','id',C('DB_PREFIX_API'));
            foreach ($list['list'] as $key=>$val){
            	$agent_list = array_column(D('Admin')->getAgent($val['principalId']), 'agent');
            	$amount = D('Admin')->getMonthOrder($val['TargetMonth'],$agent_list);

                $list['list'][$key]['completeRate'] = numFormat($amount/$val['monthTarget'],true);
                $list['list'][$key]['amount'] = $amount ? $amount : 0;
                $list['list'][$key]['principalName'] = $principal_list[$val['principalId']]['principal_name'];
                $list['list'][$key]['gameName'] = $game_list[$val['gameId']]['gameName'];
                $rows[] = $list['list'][$key];
            }
            $arr = array('rows'=>(empty($rows) ? array() : $rows),'results'=>$list['count']);
            exit(json_encode($arr));
        }else{
            $this->display();
        }
        
    }

    /**
     * 合同列表
     */
    public function contractList()
    {
        if(IS_POST){
            $data   = I();

            //处理搜索条件
            $map    = array();
            // $roleId = trim($_SESSION["admin"]["role_id"]);
            // if (!in_array($roleId, array(1, 3, 8, 13, 14, 17))) {
            //     $map["string"] = "partment = ".trim($_SESSION["admin"]["partment"]);
            // }
            $data["game"] && $map["game"] = $data["game"];
            $data["childNo"] && $map["childNo"] = $data["childNo"];
            $data["status"] && $map["status"] = $data["status"] - 1;
            $data["contract"] && $map["contract"] = $data["contract"];
            $data["contractNo"] && $map["contractNo"] = $data["contractNo"];
            $data["followAdmin"] && $map["followAdmin"] = $data["followAdmin"];
            $data["principalId"] && $map["principalId"] = $data["principalId"];

            //权限控制
            $uid    = session('admin.uid');
            $roleId = session('admin.role_id');
            if(in_array($uid,$this->contractAll) || $roleId == 1){//查看全部部门
                $data["partment"] && $map["partment"] = $data["partment"];
            }elseif(in_array($uid,$this->contractOne)){//查看一部
                if(!$data['partment']){
                    $map['partment'] = 1;
                }else{
                    $data['partment'] == 1 ? $map['partment'] = 1 : $map['partment'] = 0;
                }
            }elseif(in_array($uid,$this->contractTwo)){//查看二部
                if(!$data['partment']){
                    $map['partment'] = 2;
                }else{
                    $data['partment'] == 2 ? $map['partment'] = 2 : $map['partment'] = 0;
                }
            }else{
                $map['partment'] = 0;
            }

            $res        = D("Admin")->getContractData($map);

            $admin_list     = getDataList("admin", "id", C("DB_PREFIX"));
            $principal_list = getDataList("principal", "id", C("DB_PREFIX"));
            $partment_list  = array("1" => "发行一部", "2" => "发行二部");
            $status_list    = array("0" => "是", "1" => "否", "2" => "空号", "3" => "作废");
            $pay_list       = array("1" => "日结", "2" => "月结", "3" => "预付", "4" => "垫付", "5" => "分期");

            if ($data["export"] == 1) {
                import("Org.Util.PHPExcel", LIB_PATH, ".php");
                error_reporting(E_ALL);
                date_default_timezone_set('Europe/London');
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
                $objPHPExcel->getActiveSheet()->getStyle('B2')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB("00FFFF00");      //设置颜色
                $objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyle('B3')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB("0087CEFA");      //设置颜色


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
                    ->setCellValue('O4', '合同签订单位')
                    ->setCellValue('P4', '主要约定条款')
                    ->setCellValue('Q4', '结算方式')
                    ->setCellValue('R4', '总金额')
                    ->setCellValue('S4', '付款时间')
                    ->setCellValue('T4', '已付金额')
                    ->setCellValue('U4', '未付金额')
                    ->setCellValue('V4', '票据号')
                    ->setCellValue('W4', '收到发票金额')
                    ->setCellValue('X4', '未到票金额')
                    ->setCellValue('Y4', '备注');
                $objPHPExcel->getActiveSheet()->getStyle('A4:Y4')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                $num = 4;
                foreach($res as $key => $val) {
                    $num++;
                    $day = $val["endTime"]? (($val["endTime"] - strtotime(date("Y-m-d")) > 0)? ($val["endTime"] - strtotime(date("Y-m-d")))/86400: 0): "";
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('A'.$num, $val['id'], \PHPExcel_Cell_DataType::TYPE_NUMERIC);
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('B'.$num, $partment_list[$val["partment"]]? $partment_list[$val["partment"]]: "（未知）", \PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('C'.$num, $admin_list[$val["followAdmin"]]["real"]? $admin_list[$val["followAdmin"]]["real"]: "（未知）", \PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('D'.$num, $status_list[$val["status"]]? $status_list[$val["status"]]: "（未知）", \PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('E'.$num, $val["fileTime"]? date("Ymd", $val["fileTime"]): "", \PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('F'.$num, $val["startTime"]? date("Ymd", $val["startTime"]): "", \PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('G'.$num, $val["endTime"]? date("Ymd", $val["endTime"]): "", \PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('H'.$num, $day, $val["endTime"]? \PHPExcel_Cell_DataType::TYPE_NUMERIC: \PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('I'.$num, $principal_list[$val["principalId"]]["principal_name"]? $principal_list[$val["principalId"]]["principal_name"]: "（未知）", \PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('J'.$num, $val["type"]? $val["type"]: "", \PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('K'.$num, $val["game"]? $val["game"]: "", \PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('L'.$num, $val["contract"]? $val["contract"]: "", \PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('M'.$num, $val["contractNo"]? $val["contractNo"]: "", \PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('N'.$num, $val["childNo"]? $val["childNo"]: "", \PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('O'.$num, $val["company"]? $val["company"]: "", \PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('P'.$num, $val["info"]? $val["info"]: "", \PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('Q'.$num, $pay_list[$val["payType"]]? $pay_list[$val["payType"]]: "（未知）", \PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('R'.$num, $val["amount"]? $val["amount"]: "", $val["amount"]? \PHPExcel_Cell_DataType::TYPE_NUMERIC: \PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('S'.$num, $val["payTime"]? date("Ymd", $val["payTime"]): "", \PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('T'.$num, $val["payAmount"]? $val["payAmount"]: "", $val["payAmount"]? \PHPExcel_Cell_DataType::TYPE_NUMERIC: \PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('U'.$num, $val["unpaidAmount"]? $val["unpaidAmount"]: "", $val["unpaidAmount"]? \PHPExcel_Cell_DataType::TYPE_NUMERIC: \PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('V'.$num, $val["receipt"]? $val["receipt"]: "", \PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('W'.$num, $val["invoiceAmount"]? $val["invoiceAmount"]: "", $val["invoiceAmount"]? \PHPExcel_Cell_DataType::TYPE_NUMERIC: \PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('X'.$num, $val["unInvoiceAmount"]? $val["unInvoiceAmount"]: "", $val["unInvoiceAmount"]? \PHPExcel_Cell_DataType::TYPE_NUMERIC: \PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('Y'.$num, $val["ext"]? $val["ext"]: "", \PHPExcel_Cell_DataType::TYPE_STRING);

                    $objPHPExcel->getActiveSheet()->getStyle('A'.$num.":Y".$num)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    if ($val["status"] == 2) {
                        $objPHPExcel->getActiveSheet()->getStyle('A'.$num.":Y".$num)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB("0087CEFA");      //设置颜色
                    } elseif ($val["status"] == 3) {
                        $objPHPExcel->getActiveSheet()->getStyle('A'.$num.":Y".$num)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB("00FFFF00");      //设置颜色
                    }

                    //子类
                    foreach ($res[$key]["children"] as $k => $v) {
                        if (!$v["id"]) continue;
                        $num++;
                        $day = $v["endTime"]? (($v["endTime"] - strtotime(date("Y-m-d")) > 0)? ($v["endTime"] - strtotime(date("Y-m-d")))/86400: 0): "";
                        $objPHPExcel->getActiveSheet()->setCellValueExplicit('A'.$num, $v['id'], \PHPExcel_Cell_DataType::TYPE_NUMERIC);
                        $objPHPExcel->getActiveSheet()->setCellValueExplicit('B'.$num, $partment_list[$v["partment"]]? $partment_list[$v["partment"]]: "（未知）", \PHPExcel_Cell_DataType::TYPE_STRING);
                        $objPHPExcel->getActiveSheet()->setCellValueExplicit('C'.$num, $admin_list[$v["followAdmin"]]["real"]? $admin_list[$v["followAdmin"]]["real"]: "（未知）", \PHPExcel_Cell_DataType::TYPE_STRING);
                        $objPHPExcel->getActiveSheet()->setCellValueExplicit('D'.$num, $status_list[$v["status"]]? $status_list[$v["status"]]: "（未知）", \PHPExcel_Cell_DataType::TYPE_STRING);
                        $objPHPExcel->getActiveSheet()->setCellValueExplicit('E'.$num, $v["fileTime"]? date("Ymd", $v["fileTime"]): "", \PHPExcel_Cell_DataType::TYPE_STRING);
                        $objPHPExcel->getActiveSheet()->setCellValueExplicit('F'.$num, $v["startTime"]? date("Ymd", $v["startTime"]): "", \PHPExcel_Cell_DataType::TYPE_STRING);
                        $objPHPExcel->getActiveSheet()->setCellValueExplicit('G'.$num, $v["endTime"]? date("Ymd", $v["endTime"]): "", \PHPExcel_Cell_DataType::TYPE_STRING);
                        $objPHPExcel->getActiveSheet()->setCellValueExplicit('H'.$num, $day, $v["endTime"]? \PHPExcel_Cell_DataType::TYPE_NUMERIC: \PHPExcel_Cell_DataType::TYPE_STRING);
                        $objPHPExcel->getActiveSheet()->setCellValueExplicit('I'.$num, $principal_list[$v["principalId"]]["principal_name"]? $principal_list[$v["principalId"]]["principal_name"]: "（未知）", \PHPExcel_Cell_DataType::TYPE_STRING);
                        $objPHPExcel->getActiveSheet()->setCellValueExplicit('J'.$num, $v["type"]? $v["type"]: "", \PHPExcel_Cell_DataType::TYPE_STRING);
                        $objPHPExcel->getActiveSheet()->setCellValueExplicit('K'.$num, $v["game"]? $v["game"]: "", \PHPExcel_Cell_DataType::TYPE_STRING);
                        $objPHPExcel->getActiveSheet()->setCellValueExplicit('L'.$num, $v["contract"]? $v["contract"]: "", \PHPExcel_Cell_DataType::TYPE_STRING);
                        $objPHPExcel->getActiveSheet()->setCellValueExplicit('M'.$num, $v["contractNo"]? $v["contractNo"]: "", \PHPExcel_Cell_DataType::TYPE_STRING);
                        $objPHPExcel->getActiveSheet()->setCellValueExplicit('N'.$num, $v["childNo"]? $v["childNo"]: "", \PHPExcel_Cell_DataType::TYPE_STRING);
                        $objPHPExcel->getActiveSheet()->setCellValueExplicit('O'.$num, $v["company"]? $v["company"]: "", \PHPExcel_Cell_DataType::TYPE_STRING);
                        $objPHPExcel->getActiveSheet()->setCellValueExplicit('P'.$num, $v["info"]? $v["info"]: "", \PHPExcel_Cell_DataType::TYPE_STRING);
                        $objPHPExcel->getActiveSheet()->setCellValueExplicit('Q'.$num, $pay_list[$v["payType"]]? $pay_list[$v["payType"]]: "（未知）", \PHPExcel_Cell_DataType::TYPE_STRING);
                        $objPHPExcel->getActiveSheet()->setCellValueExplicit('R'.$num, $v["amount"]? $v["amount"]: "", $v["amount"]? \PHPExcel_Cell_DataType::TYPE_NUMERIC: \PHPExcel_Cell_DataType::TYPE_STRING);
                        $objPHPExcel->getActiveSheet()->setCellValueExplicit('S'.$num, $v["payTime"]? date("Ymd", $v["payTime"]): "", \PHPExcel_Cell_DataType::TYPE_STRING);
                        $objPHPExcel->getActiveSheet()->setCellValueExplicit('T'.$num, $v["payAmount"]? $v["payAmount"]: "", $v["payAmount"]? \PHPExcel_Cell_DataType::TYPE_NUMERIC: \PHPExcel_Cell_DataType::TYPE_STRING);
                        $objPHPExcel->getActiveSheet()->setCellValueExplicit('U'.$num, $v["unpaidAmount"]? $v["unpaidAmount"]: "", $v["unpaidAmount"]? \PHPExcel_Cell_DataType::TYPE_NUMERIC: \PHPExcel_Cell_DataType::TYPE_STRING);
                        $objPHPExcel->getActiveSheet()->setCellValueExplicit('V'.$num, $v["receipt"]? $v["receipt"]: "", \PHPExcel_Cell_DataType::TYPE_STRING);
                        $objPHPExcel->getActiveSheet()->setCellValueExplicit('W'.$num, $v["invoiceAmount"]? $v["invoiceAmount"]: "", $v["invoiceAmount"]? \PHPExcel_Cell_DataType::TYPE_NUMERIC: \PHPExcel_Cell_DataType::TYPE_STRING);
                        $objPHPExcel->getActiveSheet()->setCellValueExplicit('X'.$num, $v["unInvoiceAmount"]? $v["unInvoiceAmount"]: "", $v["unInvoiceAmount"]? \PHPExcel_Cell_DataType::TYPE_NUMERIC: \PHPExcel_Cell_DataType::TYPE_STRING);
                        $objPHPExcel->getActiveSheet()->setCellValueExplicit('Y'.$num, $v["ext"]? $v["ext"]: "", \PHPExcel_Cell_DataType::TYPE_STRING);

                        $objPHPExcel->getActiveSheet()->getStyle('A'.$num.":Y".$num)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                        if ($v["status"] == 2) {
                            $objPHPExcel->getActiveSheet()->getStyle('A'.$num.":Y".$num)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB("0087CEFA");      //设置颜色
                        } elseif ($v["status"] == 3) {
                            $objPHPExcel->getActiveSheet()->getStyle('A'.$num.":Y".$num)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB("00FFFF00");      //设置颜色
                        }
                    }
                }
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
                $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(35);
                $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(50);
                $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(10);
                $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(12);
                $objPHPExcel->getActiveSheet()->getColumnDimension('S')->setWidth(12);
                $objPHPExcel->getActiveSheet()->getColumnDimension('T')->setWidth(12);
                $objPHPExcel->getActiveSheet()->getColumnDimension('U')->setWidth(12);
                $objPHPExcel->getActiveSheet()->getColumnDimension('V')->setWidth(20);
                $objPHPExcel->getActiveSheet()->getColumnDimension('W')->setWidth(12);
                $objPHPExcel->getActiveSheet()->getColumnDimension('X')->setWidth(12);
                $objPHPExcel->getActiveSheet()->getColumnDimension('Y')->setWidth(25);

                $objPHPExcel->getActiveSheet()->setTitle('User');
                $objPHPExcel->setActiveSheetIndex(0);
                header('Content-Type: application/vnd.ms-excel');
                header('Content-Disposition: attachment;filename=合同明细表.xls');
                header('Cache-Control: max-age=0');
                $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
                $objWriter->save('php://output');
                exit;
            } else {
                foreach ($res as $key => $val) {
                    $file       = explode(".", $val["attachment"]);
                    $file_name  = explode("/", $val["attachment"]);
                    $res[$key]["partmentName"]      = $partment_list[$val["partment"]]? $partment_list[$val["partment"]]: "（未知）";
                    $res[$key]["follow"]            = $admin_list[$val["followAdmin"]]["real"]? $admin_list[$val["followAdmin"]]["real"]: "（未知）";
                    $res[$key]["statusName"]        = $status_list[$val["status"]]? $status_list[$val["status"]]: "（未知）";
                    $res[$key]["file"]              = $val["fileTime"]? date("Ymd", $val["fileTime"]): "";
                    $res[$key]["start"]             = $val["startTime"]? date("Ymd", $val["startTime"]): "";
                    $res[$key]["end"]               = $val["endTime"]? date("Ymd", $val["endTime"]): "";
                    $res[$key]["day"]               = $val["endTime"]? (($val["endTime"] - strtotime(date("Y-m-d")) > 0)? ($val["endTime"] - strtotime(date("Y-m-d")))/86400: 0): "";
                    $res[$key]["principal"]         = $principal_list[$val["principalId"]]["principal_name"]? $principal_list[$val["principalId"]]["principal_name"]: "（未知）";
                    $res[$key]["thePayTime"]        = $val["payTime"]? date("Ymd", $val["payTime"]): "";
                    $res[$key]["payTypeName"]       = $pay_list[$val["payType"]]? $pay_list[$val["payType"]]: "（未知）";
                    $res[$key]["unpaidAmount"]      = $val["amount"]? $val["amount"] - $val["payAmount"]: "";
                    $res[$key]["unInvoiceAmount"]   = $val["payAmount"]? $val["payAmount"] - $val["invoiceAmount"]: "";
                    $res[$key]["infoExt"]           = "<div onclick='showExt(".$val["id"].")' style='width: 280px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;'>".$val["info"]."</div>";
                    $res[$key]["atta"]              = $val["attachment"]? (in_array($file[count($file) - 1], array("jpg", "png"))? "<a href='javascript:;' id='openImg' value='".$val["attachment"]."' onclick='openImg(\"".$val["attachment"]."\")'>查看图片</a>": "<a href='http://".I("server.HTTP_HOST").$val["attachment"]."' download='".$file_name[count($file_name) - 1]."'>点击下载</a>"): "";
                    if(in_array($uid,$this->contractEdit)){
                        $res[$key]["opt"]           = "<a href='javascript:;' onclick='doEdit(\"".$val["id"]."\")'>合同编辑</a>";
                        $res[$key]["opt"]           .= " <a href='javascript:;' onclick='addChild(\"".$val["id"]."\")'>添加附属</a>";
                    }
                    //子类
                    foreach ($res[$key]["children"] as $k => $v) {
                        if (!$v) continue;
                        $file       = explode(".", $v["attachment"]);
                        $file_name  = explode("/", $v["attachment"]);
                        $res[$key]["children"][$k]["partmentName"]      = $partment_list[$v["partment"]]? $partment_list[$v["partment"]]: "（未知）";
                        $res[$key]["children"][$k]["follow"]            = $admin_list[$v["followAdmin"]]["real"]? $admin_list[$v["followAdmin"]]["real"]: "（未知）";
                        $res[$key]["children"][$k]["statusName"]        = $status_list[$v["status"]]? $status_list[$v["status"]]: "（未知）";
                        $res[$key]["children"][$k]["file"]              = $v["fileTime"]? date("Ymd", $v["fileTime"]): "";
                        $res[$key]["children"][$k]["start"]             = $v["startTime"]? date("Ymd", $v["startTime"]): "";
                        $res[$key]["children"][$k]["end"]               = $v["endTime"]? date("Ymd", $v["endTime"]): "";
                        $res[$key]["children"][$k]["day"]               = $v["endTime"]? (($v["endTime"] - strtotime(date("Y-m-d")) > 0)? ($v["endTime"] - strtotime(date("Y-m-d")))/86400: 0): "";
                        $res[$key]["children"][$k]["principal"]         = $principal_list[$v["principalId"]]["principal_name"]? $principal_list[$v["principalId"]]["principal_name"]: "（未知）";
                        $res[$key]["children"][$k]["thePayTime"]        = $v["payTime"]? date("Ymd", $v["payTime"]): "";
                        $res[$key]["children"][$k]["payTypeName"]       = $pay_list[$v["payType"]]? $pay_list[$v["payType"]]: "（未知）";
                        $res[$key]["children"][$k]["unpaidAmount"]      = $v["amount"]? $v["amount"] - $v["payAmount"]: "";
                        $res[$key]["children"][$k]["unInvoiceAmount"]   = $v["payAmount"]? $v["payAmount"] - $v["invoiceAmount"]: "";
                        $res[$key]["children"][$k]["infoExt"]           = "<div onclick='showExt(".$v["id"].")' style='width: 280px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;'>".$v["info"]."</div>";
                        $res[$key]["children"][$k]["atta"]              = $v["attachment"]? (in_array($file[count($file) - 1], array("jpg", "png"))? "<a href='javascript:;' onclick='openImg(\"".$v["attachment"]."\")'>查看图片</a>": "<a href='http://".I("server.HTTP_HOST").$v["attachment"]."' download='".$file_name[count($file_name) - 1]."'>点击下载</a>"): "";
                        if(in_array($uid,$this->contractEdit)){
                            $res[$key]["children"][$k]["opt"]           = "<a href='javascript:;' onclick='doEdit(\"".$v["id"]."\")'>合同编辑</a>";
                        }
                    }
                }

                $arr = array("rows" => $res? $res : array(), "results" => 0);
                exit(json_encode($arr));
            }
        }else{
            $admin = D("Admin")->commonQuery("admin", array("manager_id" => 8), 0, 1000, "id,real");
            $this->assign("admin", $admin);
            $this->assign("path_url", "http://".I("server.HTTP_HOST")."/");
            $this->display();
        }
    }

    /**
     * 母类合同录入
     */
    public function contractAdd()
    {
        if (IS_POST) {
            $data = I("");
            $data["parentId"] = 0;
            $data["fileTime"] = $data["fileTime"]? strtotime($data["fileTime"]): 0;
            $data["startTime"] = $data["startTime"]? strtotime($data["startTime"]): 0;
            $data["endTime"] = $data["endTime"]? strtotime($data["endTime"]): 0;
            $data["payTime"] = $data["payTime"]? strtotime($data["payTime"]): 0;
            $res = D("admin")->commonAdd("contract", $data);
            if($res){
                $files  = file_upload_all("contract/".$res);
                if(is_array($files)){
                    D("Admin")->commonExecute("contract", array("id" => $res), array("attachment" => "/Uploads/".$files["file"]["savepath"].$files["file"]["savename"]));
                }

                $this->success("合同录入成功！");
            } else {
                $this->error("合同录入失败！");
            }
        } else {
            $type = D("Admin")->commonQuery("contract", array("type != '' AND type IS NOT NULL"), 0, 100000, "DISTINCT(type) AS type");
            $follow = D("Admin")->commonQuery("admin", array("manager_id" => 8), 0, 1000, "id,real");
            $principal = D("Admin")->commonQuery("principal", array(), 0, 1000, "id,principal_name");
            $this->assign("type", $type);
            $this->assign("follow", $follow);
            $this->assign("principal", $principal);
            if (IS_AJAX) {
                $response = $this->fetch();
                $this->ajaxReturn(array("status"=>1, "_html"=>$response));
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
            $data = I("");
            $data["fileTime"] = $data["fileTime"]? strtotime($data["fileTime"]): 0;
            $data["startTime"] = $data["startTime"]? strtotime($data["startTime"]): 0;
            $data["endTime"] = $data["endTime"]? strtotime($data["endTime"]): 0;
            $data["payTime"] = $data["payTime"]? strtotime($data["payTime"]): 0;
            $res = D("admin")->commonAdd("contract", $data);
            if($res !== false){
                $files  = file_upload_all("contract/".$res);
                if(is_array($files)){
                    D("Admin")->commonExecute("contract", array("id" => $res), array("attachment" => "/Uploads/".$files["file"]["savepath"].$files["file"]["savename"]));
                }

                $this->success("添加附属成功！");
            } else {
                $this->error("添加附属失败！");
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
            $info = D("Admin")->commonQuery("contract", array("id" => $id), 0, 1);
            $type = D("Admin")->commonQuery("contract", array("type != '' AND type IS NOT NULL"), 0, 100000, "DISTINCT(type) AS type");
            $follow = D("Admin")->commonQuery("admin", array("manager_id" => 8), 0, 1000, "id,real");
            $principal = D("Admin")->commonQuery("principal", array(), 0, 1000, "id,principal_name");
            $this->assign("type", $type);
            $this->assign("follow", $follow);
            $this->assign("info", $info);
            $this->assign("principal", $principal);
            if (IS_AJAX) {
                $response = $this->fetch();
                $this->ajaxReturn(array("status"=>1, "_html"=>$response));
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
            $data = I("");
            $data["fileTime"] = $data["fileTime"]? strtotime($data["fileTime"]): 0;
            $data["startTime"] = $data["startTime"]? strtotime($data["startTime"]): 0;
            $data["endTime"] = $data["endTime"]? strtotime($data["endTime"]): 0;
            $data["payTime"] = $data["payTime"]? strtotime($data["payTime"]): 0;
            $res = D("admin")->commonExecute("contract", array("id" => $data["id"]), $data);
            if($res !== false){
                $files  = file_upload_all("contract/".$data["id"]);
                if(is_array($files)){
                    D("Admin")->commonExecute("contract", array("id" => $data["id"]), array("attachment" => "/Uploads/".$files["file"]["savepath"].$files["file"]["savename"]));
                }

                $this->success("合同编辑成功！");
            } else {
                $this->error("合同编辑失败！");
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
            $info = D("Admin")->commonQuery("contract", array("id" => $id), 0, 1);
            $type = D("Admin")->commonQuery("contract", array("type != '' AND type IS NOT NULL"), 0, 100000, "DISTINCT(type) AS type");
            $follow = D("Admin")->commonQuery("admin", array("manager_id" => 8), 0, 1000, "id,real");
            $principal = D("Admin")->commonQuery("principal", array(), 0, 1000, "id,principal_name");
            $info["fileTime"] = $info["fileTime"]? date("Y-m-d", $info["fileTime"]): "";
            $info["startTime"] = $info["startTime"]? date("Y-m-d", $info["startTime"]): "";
            $info["endTime"] = $info["endTime"]? date("Y-m-d", $info["endTime"]): "";
            $info["payTime"] = $info["payTime"]? date("Y-m-d", $info["payTime"]): "";
            $this->assign("type", $type);
            $this->assign("follow", $follow);
            $this->assign("info", $info);
            $this->assign("principal", $principal);
            if (IS_AJAX) {
                $response = $this->fetch();
                $this->ajaxReturn(array("status"=>1, "_html"=>$response));
            } else {
                $this->display();
            }
        }
    }

    /**
     * 插入前置方法
     */
    public function _before_insert($data)
    {
    	if($this->table == 'advter_cost'){
            empty($data['costMonth']) && $this->error('月份不能为空');
    		$data['createTime'] = time();
    		$data['costMonth'] = date('Y-m-01',strtotime($data['costMonth']));
    	}elseif($this->table == 'month_target'){
            empty($data['TargetMonth']) && $this->error('月份不能为空');
    		$data['createTime'] = time();
    	}
    	return $data;
    }

    /**
     * 实时注册图表
     */
    public function registerChart()
    {
        if (IS_POST) {
            $data = I();
            $agent_info     = $_REQUEST["agent"];

            //处理搜索条件
            $agentArr = dealAllList($agent_info);

            if($agentArr['info']){
                $agent = array_keys(getDataList('agent','agent',C('DB_PREFIX_API'),array('agent'=>array('IN',$agentArr['info']),'gameType'=>1)));
                if(!$agent){
                    $agent = array('-1');
                }
                $map['regAgent'] = array('in',$agent); 
            }else{
                $agent_infos = $map_arr = array();

                if($data['advteruser_id']){
                    $map_arr['advteruser_id'] = $data['advteruser_id'];
                }

                $map_arr['gameType'] = 1;


                if($map_arr){
                    $agent_infos = array_keys(getDataList('agent','agent',C('DB_PREFIX_API'),$map_arr));
                }

                $arr = $this->agentArr;
                if($agent_infos){
                    $arr = array_intersect($arr, $agent_infos);
                    
                }elseif($map_arr && !$agent_infos){
                    $arr = array();
                }

                sort($arr);
                if(count($arr) < 1) $arr[] = '-1';
                $map['regAgent'] = array('in',$arr);
            }

            if ($data["game_id"]) {
                $map["game_id"]     = $data["game_id"];
            }


            $search = $map;
            if ($data["date"]) {
                
                if( (strtotime($data['date']) < strtotime($this->userDataTime['startDate'])) && $this->userDataTime['startDate'] ) {
                    exit(json_encode(array("info" => array(), "yesterday" => array(), "list" => array_values(array()))));
                }

                $map["regTime"]     = array(array("egt", strtotime($data["date"])), array("lt", strtotime($data["date"]." +1 day")), "and");
                if ($data["date"] == date("Y-m-d")) {
                    $search["regTime"]  = array(array("egt", strtotime($data["date"]." -1 day")), array("lt", strtotime(date("Y-m-d H:i:s")." -1 day")), "and");

                } else {
                    $search["regTime"]  = array(array("egt", strtotime($data["date"]." -1 day")), array("lt", strtotime($data["date"])), "and");
                }
            }

            if(!$data['gameType']){
                $res1        = D("Admin")->getHourRegisterCount($map);
                $bef1        = D("Admin")->getHourRegisterCount($search);

                $dataIos     = $this->registerChartIos();
                $res2        = $dataIos['res'];
                $bef2        = $dataIos['bef'];

                if($res1 && $res2){
                    $res  = array_merge($res1,$res2);
                }elseif($res1){
                    $res = $res1;
                }elseif($res2){
                    $res = $res2;
                }

                if($bef1 && $bef2){
                    $bef  = array_merge($bef1,$bef2);
                }elseif($bef1){
                    $bef = $bef1;
                }elseif($bef2){
                    $bef = $bef2;
                }

            }elseif($data['gameType'] == 1){
                $res        = D("Admin")->getHourRegisterCount($map);
                $bef        = D("Admin")->getHourRegisterCount($search);
            }elseif($data['gameType'] == 2){
                $dataIos     = $this->registerChartIos();
                $res        = $dataIos['res'];
                $bef        = $dataIos['bef'];
            }
            $max        = 0;
            $arr        = array();
            $arr_ber    = array();
            $info       = array();
            $info_bef   = array();
            $list       = array();
            $row        = array();
            foreach ($res as $val) {
                $val["hour"] > $max && $max = $val["hour"];
                $arr[intval($val["hour"])]              += intval($val["user"]);
                $list[$val["regAgent"]]["regAgent"]     = $val["regAgent"];
                $list[$val["regAgent"]][$val["hour"]]   = $val["user"];
                $list[$val["regAgent"]]["count"]        += intval($val["user"]);
                $list["统计"][$val["hour"]]             += $val["user"];
                $list["统计"]["count"]                  += $val["user"];
            }

            foreach ($bef as $val) {
                $arr_ber[intval($val["hour"])]              += intval($val["user"]);
                if (isset($list[$val["regAgent"]])) {
                    $list[$val["regAgent"]]["count_bef"]    += intval($val["user"]);
                }
                $list["统计"]["count_bef"]                   += intval($val["user"]);
            }

            $n      = 1;
            foreach ($list as $k => $v) {
                if ($k == "统计") continue;
                $agent                                      = D("Admin/Admin")->commonQuery("agent", array("agent" => $v["regAgent"]), 0, 1, "*", "lg_");
                $advter                                     = D("Admin/Admin")->commonQuery("events", array("id" => $v["regAgent"]), 0, 1, '*', 'la_');
                $row[$v["count"]*10000 + $n]                = $v;
                $row[$v["count"]*10000 + $n]["agentName"]   = $agent["agentName"]? $agent["agentName"]: "-";
                if($row[$v["count"]*10000 + $n]["agentName"]=='-'){
                    $row[$v["count"]*10000 + $n]["agentName"]   = $advter["events_name"]? $advter["events_name"]: "-";
                    $row[$v["count"]*10000 + $n]["regAgent"]   = $advter["agent"]? $advter["agent"]: "-";
                }
                $n++;
            }

            krsort($row);
            $list["统计"]["regAgent"]     = "统计";
            $list["统计"]["agentName"]    = "-";
            $row["统计"]                  = $list["统计"];

            for ($i = 0; $i <= $max; $i++) {
                $info[]     = $arr[$i]? $arr[$i]: 0;
                $info_bef[] = $arr_ber[$i]? $arr_ber[$i]: 0;
            }
            if($data['export'] == 1){
                $col = array('regAgent'=>'包号','agentName'=>'游戏','count'=>'统计','count_bef'=>'昨日日统计','00'=>'0时','01'=>'1时','02'=>'2时','03'=>'3时','04'=>'4时','05'=>'5时','06'=>'6时','07'=>'7时','08'=>'8时','09'=>'9时','10'=>'10时','11'=>'11时','12'=>'12时','13'=>'13时','14'=>'14时','15'=>'15时','16'=>'16时','17'=>'17时','18'=>'18时','19'=>'19时','20'=>'20时','21'=>'21时','22'=>'22时','23'=>'23时');
                array_unshift($row, $col);
                export_to_csv($row,'实时注册数据统计',$col);
                exit();
            }

            exit(json_encode(array("info" => $info, "yesterday" => $info_bef, "list" => array_values($row))));
        } else {
            if (IS_AJAX) {
                $response = $this->fetch();
                $this->ajaxReturn(array("status"=>1, "_html"=>$response));
            } else {
                $this->display();
            }
        }
    }

    /**
     * 实时注册统计IOS
     * @return [type] [description]
     */
    public function registerChartIos(){
        $data = I();
        $agent_info   = $_REQUEST['advter_id'];
        $agent_p_info = $_REQUEST['events_groupId'];

        //处理搜索条件
        $start      = $data['start']? $data['start']: 0;
        $pageSize   = $data['limit']? $data['limit']: 60;

        $agentArr   = dealAllList($agent_info,$agent_p_info);

        if($agentArr['info']){

            $map['advter_id'] = array('in',$agentArr['info']); 
        }else{
            $agent_infos = $map_arr = array();

            if(!empty($agentArr['pinfo'])){
                $map_arr['_string'] = "events_groupId IN ('".implode("','", $agentArr['pinfo'])."')";
            }

            if($data['advteruser_id']){
                $map_arr['advteruser_id'] = $data['advteruser_id'];
            }

            if($data['game_id']){
                $map_arr['game_id'] = $data['game_id'];
            }

            $agent_info = dealAllList($data['agent']);
            if($agent_info['info']){
                $map_arr['agent'] = array('IN',$agent_info['info']);
            }

            if($map_arr){
                $agent_infos = array_keys(getDataList('events','id',C('DB_PREFIX'),$map_arr));
            }

            $arr = $this->events;
            if($agent_infos){
                $arr = array_intersect($arr, $agent_infos);
                
            }elseif($map_arr && !$agent_infos){
                $arr = array();
            }
            sort($arr);
            if(count($arr) < 1) $arr[] = '-1';
            $map['advter_id'] = array('IN',$arr);
        }

        $search = $map;
        if ($data["date"]) {
            $map["createTime"]     = array(array("egt", strtotime($data["date"])), array("lt", strtotime($data["date"]." +1 day")), "and");
            if ($data["date"] == date("Y-m-d")) {
                $search["createTime"]  = array(array("egt", strtotime($data["date"]." -1 day")), array("lt", strtotime(date("Y-m-d H:i:s")." -1 day")), "and");
            } else {
                $search["createTime"]  = array(array("egt", strtotime($data["date"]." -1 day")), array("lt", strtotime($data["date"])), "and");
            }
        }

        $res        = D("Admin")->getHourRegisterCountIos($map);
        $bef        = D("Admin")->getHourRegisterCountIos($search);

        return array('res'=>$res,'bef'=>$bef);

    }

     /**
     * 实时充值图表
     */
    public function payChart()
    {
        if (IS_POST) {
            $data = I();
            $agent_info     = $_REQUEST["agent"];

            //处理搜索条件
            $agentArr = dealAllList($agent_info);

            if($agentArr['info']){
                $agent = array_keys(getDataList('agent','agent',C('DB_PREFIX_API'),array('agent'=>array('IN',$agentArr['info']),'gameType'=>1)));
                if(!$agent){
                    $agent = array('-1');
                }
                $map['agent'] = array('in',$agent); 
            }else{
                $agent_infos = $map_arr = array();

                if($data['advteruser_id']){
                    $map_arr['advteruser_id'] = $data['advteruser_id'];
                }

                $map_arr['gameType'] = 1;


                if($map_arr){
                    $agent_infos = array_keys(getDataList('agent','agent',C('DB_PREFIX_API'),$map_arr));
                }

                $arr = $this->agentArr;
                if($agent_infos){
                    $arr = array_intersect($arr, $agent_infos);
                    
                }elseif($map_arr && !$agent_infos){
                    $arr = array();
                }

                sort($arr);
                if(count($arr) < 1) $arr[] = '-1';

                $map['agent'] = array('in',$arr);
            }

            if ($data['game_id']) {
                $map['game_id'] = $data['game_id'];
            }


            $map['orderStatus'] = 0;
            $map['orderType']   = 0;
            $map['type']   = 1;

            $search = $map;
            if ($data['date']) {

                if( (strtotime($data['date']) < strtotime($this->userDataTime['startDate'])) && $this->userDataTime['startDate'] ) {
                    exit(json_encode(array("info" => array(), "yesterday" => array(), "list" => array_values(array()))));
                }
                $map['createTime'] = array(array('egt', strtotime($data['date'])), array('lt', strtotime($data['date'].'+1 day')), 'and');
                if ($data["date"] == date("Y-m-d")) {
                    $search["createTime"]  = array(array("egt", strtotime($data["date"]." -1 day")), array("lt", strtotime(date("Y-m-d H:i:s")." -1 day")), "and");
                } else {
                    $search["createTime"]  = array(array("egt", strtotime($data["date"]." -1 day")), array("lt", strtotime($data["date"])), "and");
                }
            }

            if(!$data['gameType']){
                $res1        = D("Admin")->getHourPayCount($map);
                $bef1        = D("Admin")->getHourPayCount($search);

                $dataIos     = $this->payChartIos();
                $res2        = $dataIos['res'];
                $bef2        = $dataIos['bef'];

                if($res1 && $res2){
                    $res  = array_merge($res1,$res2);
                }elseif($res1){
                    $res = $res1;
                }elseif($res2){
                    $res = $res2;
                }

                if($bef1 && $bef2){
                    $bef  = array_merge($bef1,$bef2);
                }elseif($bef1){
                    $bef = $bef1;
                }elseif($bef2){
                    $bef = $bef2;
                }

            }elseif($data['gameType']==1){
                $res        = D("Admin")->getHourPayCount($map);
                $bef        = D("Admin")->getHourPayCount($search);
            }elseif($data['gameType']==2){
                $dataIos    = $this->payChartIos();
                $res        = $dataIos['res'];
                $bef        = $dataIos['bef'];
            }

            $max        = 0;
            $arr        = array();
            $arr_ber    = array();
            $info       = array();
            $info_bef   = array();
            $list       = array();
            $row        = array();

            $multiple = 1;
            //鸿雁传书数据除2
            if(session('admin.uid') == "30" && strtotime($data['date']) >= strtotime('2017-12-02')){
                $multiple = 2;
            }

            foreach ($res as $val) {
                $val["hour"] > $max && $max = $val["hour"];
                $arr[intval($val["hour"])]          += intval($val["amount"]);
                $list[$val["agent"]]["agent"]       = $val["agent"];
                $list[$val["agent"]][$val["hour"]]  = round (($val["amount"])/$multiple);
                $list[$val["agent"]]["count"]       += round ((intval($val["amount"]))/$multiple);
                $list["统计"][$val["hour"]]         += round (($val["amount"])/$multiple);
                $list["统计"]["count"]              += round ((intval($val["amount"]))/$multiple);
            }

            foreach ($bef as $val) {
                $arr_ber[intval($val["hour"])]          += intval($val["amount"]);
                if (isset($list[$val["agent"]])) {
                    $list[$val["agent"]]["count_bef"]   += intval($val["amount"]);
                }
                $list["统计"]["count_bef"]              += intval($val["amount"]);
            }

            $n      = 1;
            foreach ($list as $k => $v) {
                if ($k == "统计") continue;
                $agent                                      = D("Admin/Admin")->commonQuery("agent", array("agent" => $v["agent"]), 0, 1, '*', 'lg_');
                $advter                                     = D("Admin/Admin")->commonQuery("events", array("id" => $v["agent"]), 0, 1, '*', 'la_');
                $row[$v["count"]*10000 + $n]                = $v;
                $row[$v["count"]*10000 + $n]["agentName"]   = $agent["agentName"]? $agent["agentName"]: "-";
                if($row[$v["count"]*10000 + $n]["agentName"]=='-'){
                    $row[$v["count"]*10000 + $n]["agentName"]   = $advter["events_name"]? $advter["events_name"]: "-";
                    $row[$v["count"]*10000 + $n]["agent"]   = $advter["agent"]? $advter["agent"]: "-";
                }
                $n++;
            }

            krsort($row);
            $list["统计"]["agent"]        = "统计";
            $list["统计"]["agentName"]    = "-";
            $row["统计"]                  = $list["统计"];

            for ($i = 0; $i <= $max; $i++) {
                $info[]     = $arr[$i]? $arr[$i]: 0;
                $info_bef[] = $arr_ber[$i]? $arr_ber[$i]: 0;
            }

            if($data['export'] == 1){
                $col = array('agent'=>'包号','agentName'=>'游戏','count'=>'统计','count_bef'=>'昨日统计','00'=>'0时','01'=>'1时','02'=>'2时','03'=>'3时','04'=>'4时','05'=>'5时','06'=>'6时','07'=>'7时','08'=>'8时','09'=>'9时','10'=>'10时','11'=>'11时','12'=>'12时','13'=>'13时','14'=>'14时','15'=>'15时','16'=>'16时','17'=>'17时','18'=>'18时','19'=>'19时','20'=>'20时','21'=>'21时','22'=>'22时','23'=>'23时');
                array_unshift($row, $col);
                export_to_csv($row,'实时充值数据统计',$col);
                exit();
            }
            exit(json_encode(array("info" => $info, "yesterday" => $info_bef, "list" => array_values($row))));
        } else {
            if (IS_AJAX) {
                $response = $this->fetch();
                $this->ajaxReturn(array('status'=>1, '_html'=>$response));
            } else {
                $this->display();
            }
        }
    }

    /**
     * 实时充值IOS
     * @return [type] [description]
     */
    public function payChartIos()
    {
        $data = I();
        $agent_info   = $_REQUEST['advter_id'];
        $agent_p_info = $_REQUEST['events_groupId'];

        //处理搜索条件
        $start      = $data['start']? $data['start']: 0;
        $pageSize   = $data['limit']? $data['limit']: 60;

        $agentArr   = dealAllList($agent_info,$agent_p_info);

        if($agentArr['info']){
            $map['advter_id'] = array('in',$agentArr['info']); 
        }else{
            $agent_infos = $map_arr = array();

            if(!empty($agentArr['pinfo'])){
                $map_arr['_string'] = "events_groupId IN ('".implode("','", $agentArr['pinfo'])."')";
            }

            if($data['advteruser_id']){
                $map_arr['advteruser_id'] = $data['advteruser_id'];
            }

            if($data['game_id']){
                $map_arr['game_id'] = $data['game_id'];
            }

            $agent_info = dealAllList($data['agent']);
            if($agent_info['info']){
                $map_arr['agent'] = array('IN',$agent_info['info']);
            }

            if($map_arr){
                $agent_infos = array_keys(getDataList('events','id',C('DB_PREFIX'),$map_arr));
            }

            $arr = $this->events;
            if($agent_infos){
                $arr = array_intersect($arr, $agent_infos);
            }elseif($map_arr && !$agent_infos){
                $arr = array();
            }

            sort($arr);
            if(count($arr) < 1) $arr[] = '-1';
            $map['advter_id'] = array('IN',$arr);
        }

       $map['orderStatus'] = 0;
       $map['orderType']   = 0;
       $map['type'] = 2;

       $search =  $map;
       if ($data['date']) {
           $map['createTime'] = array(array('egt', strtotime($data['date'])), array('lt', strtotime($data['date'].'+1 day')), 'and');
           if ($data["date"] == date("Y-m-d")) {
               $search["createTime"]  = array(array("egt", strtotime($data["date"]." -1 day")), array("lt", strtotime(date("Y-m-d H:i:s")." -1 day")), "and");
           } else {
               $search["createTime"]  = array(array("egt", strtotime($data["date"]." -1 day")), array("lt", strtotime($data["date"])), "and");
           }
       }

        $res        = D("Admin")->getHourPayCountIos($map);
        $bef        = D("Admin")->getHourPayCountIos($search);

        return array('res'=>$res,'bef'=>$bef);
    }

}