<?php
/**
 * Created by Sublime.
 * User: XSM
 * Date: 2017/7/19
 * Time: 18:49
 *
 * 广告报送表
 */

namespace Api\Model;

use Think\Model;

class AdvterRecordModel extends Model
{

    protected $tableName = "";                                                //数据表名（不包括前缀）

    /**
     * 初始化
     */
    public function __construct()
    {
        $this->tableName = "advter_record";
    }

    /**
     * 主动上报激活，充值数据，注册
     * @param $data
     * @param $advterStaus 1:激活 2:充值报送 3:注册报送
     * @return bool|mixed
     */
    public function activeReport($data,$advterStaus=1)
    {   
        

        if(!$data['agent'] || !$data['game_id']) return false;

        /*$agentStatus = M('agent')->where(array('agent'=>$data['agent'],'advterStaus'=>$advterStaus))->find();
        if(!$agentStatus || count($agentStatus) < 1) return false;*/
        $this->gdtReport($data,$advterStaus);
        $this->advterCallBack($data,$advterStaus);
    }


    /**
     * 报送广告激活数据
     * @param $data
     * @param $reportType [上报的类型] 1:激活 2:充值报送 3:注册报送
     * @return bool|mixed
     */
    public function advterCallBack($data,$reportType = 1)
    {
        if(!$data['agent'] || !$data['game_id']){
            //调试记录日志
            log_save("[data]我方数据:".json_encode($data)."  [msg]游戏或渠道号异常日志", "info", "", "advter_log_". date("Y-m-d").'.log','debug_advter_log');
            return false;
        }

        $agentStatus = M('agent')->where(array('agent'=>$data['agent']))->find();
        if(!$agentStatus || count($agentStatus) < 1) return false;

        if($agentStatus['advterStaus']){
            if($agentStatus['advterStaus'] != $reportType)
            {
                log_save("[data]我方数据:".json_encode($data)."  [msg]报送类型与后台设置不一致日志", "info", "", "advter_log_". date("Y-m-d").'.log','debug_advter_log');
                return false;
            }
        }

        if(!in_array($agentStatus['advteruser_id'],array(10,6,7))) return false;

        //7天内的数据
        $star_time = strtotime(date('Y-m-d').' -7 day');
        $end_time  = strtotime(date('Y-m-d 23:59:59'));
        $map['clickTime'] = array('between',array($star_time,$end_time));
       
        //安卓报送
        if(!empty($data['imei']) && $agentStatus['gameType'] == 1) {
            $map['agent']    = $data['agent'];
            $map['uniqueId'] = strtolower(md5($data['imei']));
            $map['os']       = 1;
            $map['game_id']  = $data['game_id'];
            $reportType == 1 && $map['status']   = 1;
            $res = M($this->tableName,'la_')->where($map)->find();
        }
        //ios报送
        if (!empty($data['idfa']) && $agentStatus['gameType'] == 2) {
            $map['agent']    = $data['agent'];
            $map['uniqueId'] = strtolower(md5($data['idfa']));
            $map['game_id']  = $data['game_id'];
            $map['os']       = 2;
            $reportType == 1 && $map['status']   = 1;
            $res = M($this->tableName,'la_')->where($map)->find();
        }

        if($res){
            if($res['advterId'] == 10){ 
                //微信
                $result = $this->wxReport($res);
                $reportType == 1 && $this->saveAdvterRecoed(array('status'=>0),$map['uniqueId'],$map['game_id']);

                return true;
            }elseif($res['advterId'] == 6){
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
                $res['callBackUrl'] .= '&event_type='.$event_type;
            }elseif($res['advterId'] == 7){
                //UC头条
            }
            $result = curl_get($res['callBackUrl'],15);
            //记录日志
            log_save("[result]".$result."    [data]".json_encode($res)."\r\n"."我方数据:".json_encode($data)."  [msg]报送日志", "info", "", "advter_log_".$res['advterType'].'_'. date("Y-m-d").'.log','advter_log');
            $reportType == 1 && $this->saveAdvterRecoed(array('status'=>0),$map['uniqueId'],$map['game_id']);
        }else{
            //调试记录日志
            log_save("[result]".M($this->tableName,'la_')->_sql()."   [data]我方数据:".json_encode($data)."  [msg]imei异常日志", "info", "", "advter_log_".$agentStatus['advteruser_id'].'_'. date("Y-m-d").'.log','debug_advter_log');
        }
    }

    /**
     * 广点通数据上报
     * @DateTime  2017-07-26T22:47:06+0800
     * @param     [array]         $data       [需要上报的数据]
     * @param     [int]           $reportType [上报的类型] 1:激活 2:充值报送 3:注册报送
     * @return    [bool]                      [上报是否成功]
     */
    private function gdtReport($data,$reportType=1)
    {
        //上报地址格式--http://t.gdt.qq.com/conv/app/{appid}/conv?v={data}&conv_type={conv_type}&app_type={app_type}&advertiser_id={advertiser_id}
         $_config = array(
            'sgqxzAND050' => array('appId'=>'1106303976','advertiser_id'=>'4936389','sign_key'=>'305ad9b729759d2c', 'encrypt_key'=>'BAAAAAAAAAAAS1LF','conv_type'=>'UNIONANDROID'),    //三国群雄志
            'qyjAND033'   => array('appId'=>'1106345400','advertiser_id'=>'4936389','sign_key'=>'305ad9b729759d2c', 'encrypt_key'=>'BAAAAAAAAAAAS1LF','conv_type'=>'UNIONANDROID'),    //青云决
            'qyjAND188'   => array('appId'=>'1106281877','advertiser_id'=>'5296952','sign_key'=>'e6584a1010ff7e97', 'encrypt_key'=>'BAAAAAAAAAAAUNM4','conv_type'=>'UNIONANDROID'),    //青云决
            'qyjAND177'  => array('appId'=>'1106281835','advertiser_id'=>'4851368','sign_key'=>'bc6237d84457ff73', 'encrypt_key'=>'BAAAAAAAAAAASgao','conv_type'=>'UNIONANDROID'),      //青云决
            'qyjAND260'  => array('appId'=>'1106293783','advertiser_id'=>'1786832','sign_key'=>'c33e962f89594f73', 'encrypt_key'=>'BAAAAAAAAAAAG0PQ','conv_type'=>'UNIONANDROID'),      //青云决
            'sgqxzAND153' => array('appId'=>'1106402644','advertiser_id'=>'4851368','sign_key'=>'bc6237d84457ff73', 'encrypt_key'=>'BAAAAAAAAAAASgao','conv_type'=>'UNIONANDROID'),    //三国群雄志
            'sszszjAND002' => array('appId'=>'1106429440','advertiser_id'=>'4936389','sign_key'=>'305ad9b729759d2c', 'encrypt_key'=>'BAAAAAAAAAAAS1LF','conv_type'=>'UNIONANDROID'),    //永恒仙域
            'sszszjAND003' => array('appId'=>'1106353059','advertiser_id'=>'5296952','sign_key'=>'e6584a1010ff7e97', 'encrypt_key'=>'BAAAAAAAAAAAUNM4','conv_type'=>'UNIONANDROID'),    //永恒仙域
            'sszszjAND004' => array('appId'=>'1106432752','advertiser_id'=>'4851368','sign_key'=>'bc6237d84457ff73', 'encrypt_key'=>'BAAAAAAAAAAASgao','conv_type'=>'UNIONANDROID'),    //永恒仙域
            'sszszjAND005' => array('appId'=>'1106430730','advertiser_id'=>'1786832','sign_key'=>'c33e962f89594f73', 'encrypt_key'=>'BAAAAAAAAAAAG0PQ','conv_type'=>'UNIONANDROID'),    //永恒仙域
            'djmxTAND009'  => array('appId'=>'1106452340','advertiser_id'=>'5296952','sign_key'=>'e6584a1010ff7e97', 'encrypt_key'=>'BAAAAAAAAAAAUNM4','conv_type'=>'UNIONANDROID'),    //校花
            'djmxTAND008'  => array('appId'=>'1106452342','advertiser_id'=>'4936389','sign_key'=>'305ad9b729759d2c', 'encrypt_key'=>'BAAAAAAAAAAAS1LF','conv_type'=>'UNIONANDROID'),    //校花
            'djmxTAND007'  => array('appId'=>'1106376099','advertiser_id'=>'2246298','sign_key'=>'23ce4c3b16b360ef', 'encrypt_key'=>'BAAAAAAAAAAAIkaa','conv_type'=>'UNIONANDROID'),    //校花
            'djmxTAND002'  => array('appId'=>'1106452338','advertiser_id'=>'5452408','sign_key'=>'a01bb373263422c0', 'encrypt_key'=>'BAAAAAAAAAAAUzJ4','conv_type'=>'UNIONANDROID'),    //校花
            'qyjAND323'  => array('appId'=>'1106468040','advertiser_id'=>'2246298','sign_key'=>'23ce4c3b16b360ef', 'encrypt_key'=>'BAAAAAAAAAAAIkaa','conv_type'=>'UNIONANDROID'),      //青云决
            'zsgwsAND003'  => array('appId'=>'1106405381','advertiser_id'=>'5939250','sign_key'=>'e547aaac04ea06c9', 'encrypt_key'=>'BAAAAAAAAAAAWqAy','conv_type'=>'UNIONANDROID'),      //战三国无双
            'zsgwsAND002'  => array('appId'=>'1106481896','advertiser_id'=>'6295256','sign_key'=>'fc9fcf334e7adb88', 'encrypt_key'=>'BAAAAAAAAAAAYA7Y','conv_type'=>'UNIONANDROID'),      //战三国无双
            'zsgwsAND006'  => array('appId'=>'1106481858','advertiser_id'=>'5296952','sign_key'=>'e6584a1010ff7e97', 'encrypt_key'=>'BAAAAAAAAAAAUNM4','conv_type'=>'UNIONANDROID'),      //战三国无双
            'zsgwsAND007'  => array('appId'=>'1106405359','advertiser_id'=>'4936389','sign_key'=>'305ad9b729759d2c', 'encrypt_key'=>'BAAAAAAAAAAAS1LF','conv_type'=>'UNIONANDROID'),      //战三国无双
            'zsgwsAND008'  => array('appId'=>'1106405351','advertiser_id'=>'2246298','sign_key'=>'23ce4c3b16b360ef', 'encrypt_key'=>'BAAAAAAAAAAAIkaa','conv_type'=>'UNIONANDROID'),      //战三国无双
            'ztfylAND015'  => array('appId'=>'1106498714','advertiser_id'=>'2246298','sign_key'=>'23ce4c3b16b360ef', 'encrypt_key'=>'BAAAAAAAAAAAIkaa','conv_type'=>'UNIONANDROID'),      //青云决
            'ztfylAND246'  => array('appId'=>'1106424065','advertiser_id'=>'4851368','sign_key'=>'bc6237d84457ff73', 'encrypt_key'=>'BAAAAAAAAAAASgao','conv_type'=>'UNIONANDROID'),      //青云决
            'ztfylAND247'  => array('appId'=>'1106424069','advertiser_id'=>'1786832','sign_key'=>'c33e962f89594f73', 'encrypt_key'=>'BAAAAAAAAAAAG0PQ','conv_type'=>'UNIONANDROID'),      //青云决
            'zsgwsAND001'  => array('appId'=>'1106481872','advertiser_id'=>'1786832','sign_key'=>'c33e962f89594f73', 'encrypt_key'=>'BAAAAAAAAAAAG0PQ','conv_type'=>'UNIONANDROID'),      //战三国无双
            'ztfylAND014'  => array('appId'=>'1106438159','advertiser_id'=>'5490029','sign_key'=>'7517b9f30e832ae3', 'encrypt_key'=>'BAAAAAAAAAAAU8Vt','conv_type'=>'UNIONANDROID'),      //泽天风云录
            'ztfylAND016'  => array('appId'=>'1106438129','advertiser_id'=>'4936389','sign_key'=>'305ad9b729759d2c', 'encrypt_key'=>'BAAAAAAAAAAAS1LF','conv_type'=>'UNIONANDROID'),      //泽天风云录
            'mhxfTAND003'  => array('appId'=>'1106568504','advertiser_id'=>'6302559','sign_key'=>'aef4ef4cd6e22406', 'encrypt_key'=>'BAAAAAAAAAAAYCtf','conv_type'=>'UNIONANDROID'),      //梦幻仙凡
            'mhxfTAND005'  => array('appId'=>'1106491645','advertiser_id'=>'6295256','sign_key'=>'fc9fcf334e7adb88', 'encrypt_key'=>'BAAAAAAAAAAAYA7Y','conv_type'=>'UNIONANDROID'),      //梦幻仙凡
            'mhxfTAND004'  => array('appId'=>'1106569150','advertiser_id'=>'6295204','sign_key'=>'a9993832b4ccd416', 'encrypt_key'=>'BAAAAAAAAAAAYA6k','conv_type'=>'UNIONANDROID'),      //梦幻仙凡
        );

        if(!$_config[$data['agent']]) return false;

        //组合参数 
        $param = array(
            'muid'      => !empty($data['imei']) ? strtolower(md5($data['imei'])) : strtolower(md5($data['idfa'])),
            'conv_time' => time(),
            'client_ip' => get_ip_address()
        );
        $reportType == 2 && $param['value'] = ($data['amount'] * 100)*0.3;
        $query_string = http_build_query($param);

        //参数签名
        $page         = 'http://t.gdt.qq.com/conv/app/'.$_config[$data['agent']]['appId'].'/conv?'.$query_string;
        $property     = $_config[$data['agent']]['sign_key'].'&GET&'.urlencode($page);
        $signature    = strtolower(md5($property));
        //参数加密
        $base_data    = $query_string.'&sign='.urlencode($signature);
        $secret_data  = $this->simpleXor($base_data,$_config[$data['agent']]['encrypt_key']);
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
        $request_param = array(
            'conv_type'     => $conv_type,
            'app_type'      => !empty($data['imei']) ? $_config[$data['agent']]['conv_type'] : 'IOS',
            'advertiser_id' => $_config[$data['agent']]['advertiser_id'],
        );
        $attachment = http_build_query($request_param);


        $url = "http://t.gdt.qq.com/conv/app/{$_config[$data['agent']]['appId']}/conv?v={$secret_data}&{$attachment}";
        $res = json_decode(curl_get($url),true);
        if($res['ret'] == 0){
            log_save("[result]".json_encode($res)."    [data]".$query_string.'___'.json_encode($request_param)."\r\n"."我方数据:".json_encode($data)."  [msg]报送日志", "info", "", "advter_log_gdt".'_'. date("Y-m-d").'.log','advter_log');
            return true;
        }else{
            return false;
        }
        
    }

    /**
     * 微信IOS激活上报
     * @DateTime  2017-07-25T22:47:06+0800
     * @param     [array]         $data [收集到的点击数据]
     * @return    [bool]                [上报是否成功]
     */
    private function wxReport($data)
    {
        //上报地址格式--http://t.gdt.qq.com/conv/app/{appid}/conv?v={data}&conv_type={conv_type}&app_type={app_type}&advertiser_id={uid}
         $_config = array(
            '1252545986' => array('sign_key'=>'c727ef4f65ce083d', 'encrypt_key'=>'BAAAAAAAAAAATIfF'),    //乱世英雄战纪
        );

        //组合参数
        $param = array(
            'click_id'  => $data['click_id'],
            'muid'      => $data['uniqueId'],
            'conv_time' => time(),
            'client_ip' => $data['ip']
        );
        $query_string = http_build_query($param);

        //参数签名
        $page         = $data['callBackUrl'].$data['appId'].'/conv?'.$query_string;
        $property     = $_config[$data['appId']]['sign_key'].'&GET&'.urlencode($page);
        $signature    = strtolower(md5($property));
        //参数加密
        $base_data    = $query_string.'&sign='.urlencode($signature);
        $secret_data  = $this->simpleXor($base_data,$_config[$data['appId']]['encrypt_key']);
        //组装请求
        $request_param = array(
            'conv_type'     => 'MOBILEAPP_ACTIVITE',
            'app_type'      => ($data['os'] == 2) ? 'IOS' : '',
            'advertiser_id' => $data['advertiserId'],
        );
        $attachment = http_build_query($request_param);

        $url = "http://t.gdt.qq.com/conv/app/{$data['appId']}/conv?v={$secret_data}&{$attachment}";
        $res = json_decode(curl_get($url),true);

        if($res['ret'] == 0){
            return true;
        }else{
            return false;
        }
        
    }

    /**
     * 简单异或加密
     * @DateTime  2017-07-25T22:45:40+0800
     * @param     [string]       $base_data   [待加密字符串]
     * @param     [string]       $encrypt_key [加密key]
     * @return    [string]       [加密后结果]
     */
    private function simpleXor($base_data,$encrypt_key){
        $retval = '';
        $source_arr = str_split($base_data);
        
        $j = 0;        
        foreach($source_arr as $ch){      
            $retval .= chr(ord($ch)^ord($encrypt_key[$j]));
            $j = $j + 1 ;
            $j = $j % (strlen($encrypt_key));
        }
        
        return urlencode(base64_encode($retval));
    }

    /**
     * IDFA匹配
     * @DateTime  2017-08-02T14:45:40+0800
     * @param     [array]        $idfa   [待匹配的idfa]
     * @param     [string]       $appid  [苹果的appleID]
     * @return    [array]       [查到的idfa]
     */
    public function idfaMatch($idfa,$appid)
    {
        if(!$idfa || !is_array($idfa) || !$appid) return false;
        $agent = M('agent')->where(array('appleId'=>$appid))->field('agent')->find();
        if(!$agent) return false;

        $res   = M('device_game')->where(array('agent'=>$agent['agent'],'idfa'=>array('IN',$idfa)))->field('DISTINCT idfa')->select();
        return $res;
    }

    /**
     * IDFA匹配插入数据表
     * @DateTime  2017-08-02T14:45:40+0800
     * @param     [array]        $data   [带插入的数据]
     * @param     [string]       $appid  [苹果的appleID]
     * @return    [bool]         [结果]
     */
    public function idfaRecord($data,$appid)
    {
        if(!$data || count($data) < 1 || !$appid) return false;
        $agent = M('agent')->where(array('appleId'=>$appid))->field('agent,game_id')->find();
        $insert = array();
        foreach ($data as $key => $value) {
            $insert[$key]['appleId']    = $appid;
            $insert[$key]['agent']      = $agent['agent'];
            $insert[$key]['game_id']    = $agent['game_id'];
            $insert[$key]['status']     = $value['status'];
            $insert[$key]['idfa']       = $value['idfa'];
            $insert[$key]['createTime'] = time();
        }
        $res = M('idfa_record','la_')->addAll($insert);
        return $res;
    }

    /**
     * 更新数据
     * @param $info
     * @param $uniqueId
     * @param $game_id
     * @return bool
     */
    private function saveAdvterRecoed($info, $uniqueId, $game_id)
    {
        //判断必要数据是否存在
        if (!$uniqueId || !$game_id) return false;

        return M($this->tableName,'la_')->where(array('uniqueId'=>$uniqueId,'game_id'=>$game_id,'status'=>1))->save($info);
    }
}