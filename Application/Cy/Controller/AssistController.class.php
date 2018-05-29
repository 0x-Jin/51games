<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/9/11
 * Time: 15:54
 *
 * 辅助控制器
 */

namespace Cy\Controller;

class AssistController extends BackendController
{

    /**
     * 崩溃日志列表
     */
    public function crashLog()
    {
        if (IS_POST) {
            $data       = I();
            !$data["date"] && exit(json_encode(array("rows" => array(), "results" => 0)));
            $start      = $data['start']? $data['start']: 0;
            $pageSize   = $data['limit']? $data['limit']: 30;
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
            $url = "http://apisdk.chuangyunet.net/Api/Ajax/getCrashLog?sign=signCheck1233Cy&date=".urlencode($data["date"]);
            $str = file_get_contents($url);
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
            $count      = substr_count($str, "【Api/crashlog】");
            if ($start > $count) exit(json_encode(array("rows" => array(), "results" => $count)));
            $begin      = 0;
            $end        = min($count, $start + $pageSize);
            $arr        = array();
            if($data['gameType']){
                $map_arr['gameType'] = $data['gameType'];
            }
            for($i = 0; $i <= $end; $i++) {
                $begin  = strpos($str, "【Api/crashlog】", $begin);
                if (!$begin) break;
                $i != $end && $begin++;
                if ($i > $start) {
                    $ip_start       = strpos($str, "[ip]", $begin);
                    $ip             = $ip_start? substr($str, $ip_start + 4, strpos($str, "[", $ip_start + 4) - $ip_start - 8): "";
                    $time_start     = strpos($str, "[time]", $begin);
                    $time           = $time_start? substr($str, $time_start + 6, strpos($str, "[", $time_start + 6) - $time_start - 10): "";
                    $device_start   = strpos($str, "[device]", $begin);
                    $device         = $device_start? substr($str, $device_start + 8, strpos($str, "[", $device_start + 8) - $device_start - 12): "";
                    $gid_start      = strpos($str, "[gid]", $begin);
                    $gid            = $gid_start? substr($str, $gid_start + 5, strpos($str, "[", $gid_start + 5) - $gid_start - 9): "";
                    $agent_start    = strpos($str, "[agent]", $begin);
                    $agent          = $agent_start? substr($str, $agent_start + 7, strpos($str, "[", $agent_start + 7) - $agent_start - 11): "";
                    $ver_start      = strpos($str, "[ver]", $begin);
                    $ver            = $ver_start? substr($str, $ver_start + 5, strpos($str, "[", $ver_start + 5) - $ver_start - 8): "";
                    $type_start     = strpos($str, "[type]", $begin);
                    $type           = $type_start? substr($str, $type_start + 6, strpos($str, "[", $type_start + 6) - $type_start - 10): "";
                    $log_start      = strpos($str, "[log]", $begin);
                    $log_end        = strpos($str, "【", $log_start);
                    $log            = $log_start? substr($str, $log_start + 5, $log_end? ($log_end - $log_start - 5): -1): "";
                    $arr[]          = array("ip" => $ip, "time" => $time, "device" => $device, "gid" => $gid, "agent" => $agent, "ver" => $ver, "type" => $type, "log" => str_replace(" ", "&nbsp;", $log));

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
            $data       = I();
            $start      = $data['start']? $data['start']: 0;
            $pageSize   = $data['limit']? $data['limit']: 30;

            if($data['userCode']){
                $map['userCode'] = $data['userCode'];
            }

            if($data['status'] != 'all'){
                $map['status'] = $data['status'];
            }

            if($data['date']){
                $map['createTime'] = array(array('egt',strtotime($data['date'])), array('lt',strtotime($data['date'].'+1 day')), 'and');
            }

            $res = D('Admin')->getBanLog($map, $start, $pageSize);
            $row = $res['list'];
            if(!empty($row)){
                foreach ($row as $key => &$val) {
                    $val['createTime'] = date('Y-m-d H:i:s',$val['createTime']);
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
                    $val['pay'] = D('Order')->getIncomeSum(array('userCode' => $val['userCode'], 'orderStatus' => 0, 'orderType' => 0));
                }
            }
            $results = $res['count'];

            exit(json_encode(array('rows'=>$row ? $row : array(), "results" => $results)));
        } else{
            if (IS_AJAX) {
                $response = $this->fetch();
                $this->ajaxReturn(array('status'=>1, '_html'=>$response));
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
            $data       = I();
            $start      = $data['start']? $data['start']: 0;
            $pageSize   = $data['limit']? $data['limit']: 30;

            if($data['creater']){
                $map['creater'] = $data['creater'];
            }
            
            $department  = session('admin.partment');
            if($department!=0){
                $map['departmentId'] = $department;
            }

            $res = D('Admin')->getImeiProxy($map, $start, $pageSize);
            $row = $res['list'];
            if(!empty($row)){
                foreach ($row as $key => &$val) {
                    $val['createTime'] = date('Y-m-d H:i:s',$val['createTime']);
                }
            }
            $results = $res['count'];

            exit(json_encode(array('rows'=>$row ? $row : array(), "results" => $results)));
        } else{
            if (IS_AJAX) {
                $response = $this->fetch();
                $this->ajaxReturn(array('status'=>1, '_html'=>$response));
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
        if(IS_POST){
            $data = I();

            if(!$data['imei']){
                $this->error('本机IMEI不能为空');
            }

            if(!$data['imeiProxy']){
                $this->error('代理IMEI不能为空');
            }

            if(D('Admin')->commonQuery('whitelist',array('imeiProxy'=>$data['imeiProxy']), 0, 1, '*','lg_')){
                $this->error('代理IMEI已存在');
            }

            $time        = time();
            $department  = session('admin.partment');
            $creater     = session('admin.realname');

            $insert[] = array(
                'createTime'        => $time,
                'departmentId'      => $department,
                'imei'              => $data['imei'],
                'creater'           => $creater,
                'imeiProxy'         => $data['imeiProxy'],
            );

            $res = D('Admin')->commonAddAll('whitelist',$insert,$prefix='lg_');
            if($res){
                $this->success('操作成功');
            }else{
                $this->error('操作失败');
            }

        }else{
            $this->assign('partment',$this->partment);
            $this->ajaxReturn(array("status"=>1, "_html"=>$this->fetch()));
        }
    }

    /**
     * 投放上报渠道商结果列表
     * @return [type] [description]
     */
    public function advterReport()
    {
        if (IS_POST) {
            $data       = I();
            if(!$data['game_id'] || !$data['system'])  
            exit(json_encode(array('rows'=>$row ? $row : array(), "results" => 0, 'hasError' => true, 'error'=>'游戏和系统必选！')));
            $start      = $data['start']? $data['start']: 0;
            $pageSize   = $data['limit']? $data['limit']: 30;

            if($data['system'] == 1){
                if($data['agent']){
                    $map['agent'] = (array)$data['agent'];
                }else{

                    $agent_infos = $map_arr = array();

                    if(!empty($data['agent_p'])){
                        $map_arr['_string'] = "id IN ('".implode("','", (array)$data['agent_p'])."') OR pid IN ('".implode("','", (array)$data['agent_p'])."')";
                    }

                    if($data['game_id']){
                        $map_arr['game_id'] = $data['game_id'];
                    }

                    if($data['advteruser_id']){
                        $map_arr['advteruser_id'] = $data['advteruser_id'];
                    }

                    if(session('admin.partment')){
                        $map_arr['departmentId'] = session('admin.partment');
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
                    $map['agent'] = $arr;

                }

                if(!$data['startDate'] || !$data['endDate']){
                    exit(json_encode(array('rows'=>$row ? $row : array(), "results" => 0, 'hasError' => true, 'error'=>'日期必须')));
                }
                $map['startDate'] = $data['startDate'];
                $map['endDate'] = $data['endDate'];
                $map['reportType'] = $data['reportType'];
                $res = D('Admin')->advterReport($map,$start,$pageSize);
            }else{
                $res = $this->advterIosReport();
            }
            $count = $res['count'];
            $gameName = getDataList("game", "id", C("DB_PREFIX_API"));
            $advteruser = getDataList('advteruser','id',C('DB_PREFIX'));
            $events = getDataList('events','id',C('DB_PREFIX'));

            foreach ($res['list'] as $key => $value) {
                if($res['list'][$key]['adUserId'] == 2){
                    if($res['list'][$key]['advterType'] == 'gdt2'){
                        $res['list'][$key]['advterUser'] = '广点通（方案二）';
                    }else{
                        $res['list'][$key]['advterUser'] = '广点通（方案一）';
                    }
                }else{
                    $res['list'][$key]['advterUser'] = $advteruser[$value['adUserId']]['company_name'];
                }
                $res['list'][$key]['gameName'] = $gameName[$value['game_id']]['gameName'];
                $res['list'][$key]['createTime'] = date('Y-m-d H:i:s',$value['createTime']);
                $res['list'][$key]['deviceId'] = $data['system'] == 1 ? $value['imei'] : $value['idfa'];
                $data['system'] == 2 && $res['list'][$key]['agent'] = $events[$value['advter_id']]['events_name'];
            }

            exit(json_encode(array('rows'=>$res['list'] ? $res['list'] : array(), "results" => $count)));
        } else{
            if (IS_AJAX) {
                $response = $this->fetch();
                $this->ajaxReturn(array('status'=>1, '_html'=>$response));
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
            $data       = I();
            $start      = $data['start']? $data['start']: 0;
            $pageSize   = $data['limit']? $data['limit']: 30;

            if($data['advter_id']){
                $map['advter_id'] = (array)$data['advter_id'];
            }else{
                $agent_infos = $map_arr = array();

                if(!empty($data['events_groupId'])){
                    $map_arr['_string'] = "events_groupId IN ('".implode("','", (array)$data['events_groupId'])."')";
                }

                if($data['advteruser_id']){
                    $map_arr['advteruser_id'] = $data['advteruser_id'];
                }

                if($data['game_id']){
                    $map_arr['game_id'] = $data['game_id'];
                }

                if(session('admin.partment')){
                    $map_arr['department'] = session('admin.partment');
                }


                if($data['agent_p']){
                    $agent_p    = array_keys(getDataList('agent','agent',C('DB_PREFIX_API'),array('id'=>array('IN',$data['agent_p']))));
                    if($agent_p){
                        $map_arr['agent'] = array('IN',$agent_p);
                    }
                }

                $agent_infos = array_keys(getDataList('events','id',C('DB_PREFIX'),$map_arr));

                if($agent_infos){
                    $arr = $agent_infos;
                }elseif($map_arr && !$agent_infos){
                    $arr = array('-1');
                }

                $map['advter_id'] = $arr;
            }

            if(!$data['startDate'] || !$data['endDate']){
                exit(json_encode(array('rows'=>$row ? $row : array(), "results" => 0, 'hasError' => true, 'error'=>'日期必须')));
            }
            $map['startDate'] = $data['startDate'];
            $map['endDate'] = $data['endDate'];
            $map['reportType'] = $data['reportType'];
            $res = D('Admin')->advterReport($map,$start,$pageSize,$data['system']);
            return $res;
        } else{
            if (IS_AJAX) {
                $response = $this->fetch();
                $this->ajaxReturn(array('status'=>1, '_html'=>$response));
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
        if(IS_POST){
            $data       = I();
            $start      = $data['start']? $data['start']: 0;
            $pageSize   = $data['limit']? $data['limit']: 30;

            if($data['shortLink']){
                $map['shortLink'] = $data['shortLink'];
            }
            $res = D('Admin')->getBuiList('short_link',$map,$start,$pageSize);

            if(!empty($res['list'])){
                foreach ($res['list'] as $key => &$val) {
                    $res['list'][$key]['createTime'] = date('Y-m-d H:i:s',$val['createTime']);
                    $res['list'][$key]['opt'] = createBtn('<a onclick=shortLinkEdit('.$val['id'].') href="javascript:;">编辑</a>');
                }
            }
            $results = $res['count'];

            exit(json_encode(array('rows'=>$res['list'] ? $res['list'] : array(), "results" => $results)));
        }else{
            if (IS_AJAX) {
                $response = $this->fetch();
                $this->ajaxReturn(array('status'=>1, '_html'=>$response));
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
        if($this->table == 'short_link'){
            $data['createTime'] = time();
            $data['departmentId'] = session('admin.partment');
            $data['creater'] = session('admin.realname');
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
            $apiLink   = urlencode('http://apisdk.chuangyunet.net/Api/Ajax/qrcodeLink.html?shortKey='.$id);
            $shortLink = curl_get('http://api.t.sina.com.cn/short_url/shorten.json?source=3271760578&url_long='.$apiLink);
            $url_short = json_decode($shortLink,true)[0]['url_short'];
            $res = D('Admin')->commonExecute('short_link',array('id'=>$id),array('shortLink'=>$url_short));
            if($res){
                $this->success('操作成功');
            }else{
                $this->error('操作失败');
            }
        }
    }

}