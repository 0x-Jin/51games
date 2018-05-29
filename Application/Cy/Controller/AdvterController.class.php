<?php
/**
 * Created by Zend.
 * User: XSM
 * Date: 2017/6/8
 * Time: 14:28
 *
 * 广告管理控制器
 */
namespace Cy\Controller;

use Cy\Controller\BackendController;

class AdvterController extends BackendController
{
    //CDN刷新地址
    public $cdnDomains = array(
        1=>'http://fall.chuangyunet.net/',
//        2=>'http://cy.33hiwan.com/',
//        3=>'http://cy.wodou2015.com/',
//        4=>'http://cy.a28d.com/',
//        5=>'http://cy.xydevops.com/',
        6=>'http://nxs.aiyoumeng.cn/',
//        7=>'http://txs.aiyoumeng.cn/',
//        8=>'http://cy.akmedia.com.cn/',
        9=>'http://fall.chuangyunet.com.cn/',
//        10=>'http://cy.zcplayer.com/',
//        11=>'http://cy.zqgame.com/',
        12=>'http://suq.rzhushou.com/',
//        13=>'http://cy.weists.cn/',
        /*14=>'http://cmgcwl.cn/',
        15=>'http://cmzgwl.cn/',*/
        16=>'http://cy.bqcyxs.com/',
        17=>'http://cy.whdw8.com/',
        18=>'http://zgsg.fapk.me/',
    );

    //模板类型
    public $tplList = array(
        1=>'banner',
        2=>'banner+幻灯片',
        3=>'banner+一屏幻灯片',
        4=>'无banner+无幻灯片',
        5=>'向上滑动落地页',
        6=>'无banner+一屏幻灯片',
        7=>'无banner+幻灯片',
        8=>'无banner+幻灯片+弹窗',
        9=>'底部banner+幻灯片',
        10=>'底部banner',
        11=>'底部banner+一屏幻灯片',
        12=>'顶部banner+3d轮播图',
    );

    private $agentPath = "https://static.chuangyunet.net/";                                  //静态渠道包的下载链接
    private $secretKey='UtCNQIyVq34FKF3f7PVP7f6PiuwcAXjj';
    private $secretId='AKIDVbf6TjplYRgk3Cr7pQr9GC0dyCzp49fA';
    private $action='RefreshCdnUrl';
    function _initialize()
    {
        parent::_initialize();
        $this->table = (string)$_REQUEST['table'];
        $this->tpl = (string)$_REQUEST['tpl'];
    }

    /**
     * 负责人列表
     */
    public function principalList()
    {
        if(IS_POST){
            $data = I();
            $map = array();
            $data['principal_name'] && $map['principal_name'] = array('like','%'.$data['principal_name'].'%');
            $data['department'] && $map['department'] = $data['department'];

            $start=I('start',0,'intval');
            $pageSize=I('limit',30,'intval');
            $list = D('Admin')->getBuiList($this->table,$map,$start,$pageSize);
            $partment_arr = field_to_key($this->partment,'partment_id');
            foreach ($list['list'] as $key=>$val){
                $list['list'][$key]['department'] = $partment_arr[$val['department']]['name'];
                $list['list'][$key]['opt'] = createBtn('<a href="javascript:;" onclick="principalEdit('.$val['id'].',this)">编辑</a> | <a href="javascript:;" onclick="principalDelete('.$val['id'].',this)">删除</a>');
                $list['list'][$key]['create_time'] = date('Y-m-d H:i:s',$val['create_time']);
                $rows[] = $list['list'][$key];
            }
            $arr = array('rows'=>(empty($rows) ? array() : $rows),'results'=>$list['count']);
            exit(json_encode($arr));
        }else{
            $this->assign('partment',$this->partment);
            $this->display();
        }
        
    }

    /**
     * 渠道列表
     */
    public function agentList()
    {
        if(IS_POST){
            $data = I();
            $map = array();
            if($data['agentTpl'] == '0'){
                $map['agentType'] = 1;//母包
            }elseif($data['agentTpl'] == '-1'){
                $map['agentType'] = 0;//子包
            }else{
                $map['pid'] = $data['agentTpl'];//母包下的所有子包
            }

            if ($data['departmentId']) {
                $map['departmentId'] = $data['departmentId'];
            } else {
                $this->parId && $map['departmentId'] = array('IN',$this->parId);
            }

            if($data['agent']){
                $map['agent'] = $data['agent'];
            }
            if(!in_array(session('admin.role_id'),array(1,3,17,25))){
                $map["_string"] = " agent NOT IN ('jyQIHOO','jyBAIDU','ceshi','jyQQLAND','jyTYYAND','jyWJZAND','jyYXJAND','jyHYAND','jyHDAND','jyLEYOU','jyDUOYOU','xxcqYXJAND','xxcqWJZAND','xxcqTYYAND','xxcqQQLAND','xxcqHANGYOUAND','xxcqHANGDONGAND','xxcqDUOYOUAND','xxcqBAIDUAND','xxcqQIHOOAND') AND channel_id > 1";
            }else{
                $map["_string"] = " channel_id > 1";
            }

            $data['creater'] && $map['creater'] = $data['creater'];
            $data['channel_id'] && $map['channel_id'] = $data['channel_id'];
            $data['game_id'] && $map['game_id'] = $data['game_id'];
            $data['advteruser_id'] && $map['advteruser_id'] = $data['advteruser_id'];
            $data['principal_id'] && $map['principal_id'] = $data['principal_id'];
            $data['proxyId'] && $map['proxyId'] = $data['proxyId'];
            $data['advterAccountId'] && $map['advterAccountId'] = $data['advterAccountId'];
            //权限控制
            // var_dump($map);die;
            $start=I('start',0,'intval');
            $pageSize=I('limit',30,'intval');
            $list = D('Admin')->getBuiList($this->table,$map,$start,$pageSize,'lg_');
            $channel_list = getDataList('channel','id',C('DB_PREFIX_API'),array('channelStatus'=>0)); //渠道分类信息
            $advteruser_list     = getDataList('advteruser','id',C('DB_PREFIX'),array('status'=>1)); //广告商列表
            $principal_list      = getDataList('principal','id',C('DB_PREFIX'),array('status'=>1)); //负责人列表
            $proxy_list          = getDataList('proxy','id',C('DB_PREFIX')); //代理商列表
            $advter_account_list = getDataList('advter_account','id',C('DB_PREFIX')); //广告账号
            $p_agent_list        = getDataList('agent','id',C('DB_PREFIX_API'),array('pid'=>0,'agentType'=>1)); //母包列表
            $game_list = getGameList(); //游戏列表
            $gameType = array(1=>'Android',2=>'IOS');
            $partmentType = array(1=>'<span style="color:green;">发行一部</span>',2=>'<span style="color:#ee5f5b;">发行二部</span>',3=>'融合');
            $advterStaus = array(0=>'全部报送',1=>'激活报送',2=>'充值完成报送',3=>'注册报送');
            foreach ($list['list'] as $key=>$val){
                $list['list'][$key]['opt'] = createBtn('<a href="javascript:;" onclick="agentEdit('.$val['id'].',this)">编辑</a>');
                if (!$channel_list[$val['channel_id']]['goodsId']) $list['list'][$key]['opt'] .= ' <a href="javascript:;" onclick="goodsShow(\''.$val['agent'].'\',this)">商品</a>';
                $list['list'][$key]['advteruserName'] = $advteruser_list[$val['advteruser_id']]['company_name'];
                $list['list'][$key]['proxyName']      = $proxy_list[$val['proxyId']]['proxyName'];
                $list['list'][$key]['accountName']    = $advter_account_list[$val['advterAccountId']]['account'];
                $list['list'][$key]['channelTypeName'] = $channel_list[$val['channel_id']]['channelName'];
                $list['list'][$key]['principalName'] = $principal_list[$val['principal_id']]['principal_name'];
                $list['list'][$key]['gameName'] = $game_list[$val['game_id']]['gameName'];
                $list['list'][$key]['createTime'] = date('Y-m-d H:i:s',$val['createTime']);
                $list['list'][$key]['gameType'] = $gameType[$val['gameType']];
                $list['list'][$key]['departmentId'] = $partmentType[$val['departmentId']];
                $list['list'][$key]['advterStaus'] = $advterStaus[$val['advterStaus']];
                //属于这个两个母包的sqmxTAND,sqmxAND，下载链接换新的
                if(in_array($val['pid'],array(8453,9535))){
                    $this->agentPath = 'https://cdn.chuangyunet.net/';
                }
                $list['list'][$key]['packageStatusName'] = ($val['pid'] != 0 && $val['agentType'] == 0)? ($val['packageStatus'] == 2? $this->agentPath.$val["agent"].".apk": $val["packageStatus"]): '-1';
                if (!$val['agentType'] && $val['pid']) {
                    $list['list'][$key]['isNew'] = ($p_agent_list[$val['pid']]['newPackageTime'] > 1508817600)? (($val['lastPackageTime'] > $p_agent_list[$val['pid']]['newPackageTime'])? '<span style="color: green">是</span>': '<span style="color: red">否</span>'): '<span style="color: grey">未知</span>';
                } else {
                    $list['list'][$key]['isNew'] = ($val['newPackageTime'] > 1508817600)? (($val['lastPackageTime'] > $val['newPackageTime'])? '<span style="color: green">是</span>': '<span style="color: red">否</span>'): '<span style="color: grey">未知</span>';
                }
                $rows[] = $list['list'][$key];
            }
            $arr = array('rows'=>(empty($rows) ? array() : $rows),'results'=>$list['count']);
            exit(json_encode($arr));
        }else{
            //创建人
            $creater = M('agent',C('DB_PREFIX_API'))->field('DISTINCT creater AS creater')->select();
            $creater_list = '<option value="">全部</option>';
            foreach ($creater as $key => $value) {
                if(empty($value['creater'])) continue;
                $creater_list .= "<option value='{$value['creater']}'>{$value['creater']}</option>";
            }
            $this->assign('creater',$creater_list);
            $this->display();
        }
        
    }

    /**
     * 渠道编辑
     */
    public function agentEdit()
    {
        if(IS_POST){
            $data = I();

            if($data['gameType'] == 1) {
                $data['bundleId'] = '';
                $data['appleId'] = '';
                $data['trialVer'] = '';
            }
            //查出负责人所属的部门
            // $department = D('Admin')->commonQuery('principal',array('id'=>$data['principal_id']),0,1,'department');
            
            $data['updateTime'] = time();
            // $data['departmentId'] = $department['department'];

            if(D('Admin')->commonExecute('agent',array('id'=>$data['id']),$data,'lg_')){
                //判断该包是否有子包
                $subAgentArr = D('Admin')->commonQuery('agent',array('pid'=>$data['id']),0,10000,'id,agent',C('DB_PREFIX_API'));
                if($subAgentArr){
                    foreach ($subAgentArr as $subAgent){
                        $id = $subAgent['id'];
                        $agent = $subAgent['agent'];
                        preg_match('/\d\$/',$agent,$number);
                        $res = D('Admin')->commonExecute('agent',array('id'=>array('IN',$id)),array('agentCallbackUrl'=>$data['agentCallbackUrl'],'mainbody_id'=>$data['mainbody_id'],'agentName'=>($data['agentName'].$agent)),'lg_');
                    }
                }
                $this->success('操作成功');
            }else{
                $this->error('操作失败');
            }
        }else{
            $id = I('id',0,'intval');
            $info = D('Admin')->commonQuery('agent',array('id'=>$id),0,1,'*','lg_');
            //查出渠道号对应的渠道分类参数个数
            $channelType = D('Admin')->commonQuery('channel',array('id'=>$info['channel_id']),0,1,'*','lg_');
            $val_arr = array();
            for($i=1;$i<=10;$i++){
                if(empty($channelType['param'.$i])){
                    //清除对应渠道号的值
                    unset($info['value'.$i]);
                    continue;
                }
                $val_arr['value'.$i]['val'] = $info['value'.$i];
                $val_arr['value'.$i]['param'] = $channelType['param'.$i];
                $val_arr['value'.$i]['num'] = $i;
            }
            $advteruser_list     = getDataList('advteruser','id',C('DB_PREFIX'),array('id'=>$info['advteruser_id'])); //广告商
            $channel_list        = getDataList('channel','id',C('DB_PREFIX_API'),array('id'=>$info['channel_id'])); //渠道分类id
            $proxy_list          = getDataList('proxy','id',C('DB_PREFIX')); //代理商列表
            $advter_account_list = getDataList('advter_account','id',C('DB_PREFIX')); //广告账号
            $info['advteruserName'] = $advteruser_list[$info['advteruser_id']]['company_name'];
            $info['channelName'] = $channel_list[$info['channel_id']]['channelName'];
            $info['proxyName']   = $proxy_list[$info['proxyId']]['proxyName'];
            $this->assign('info', $info);
            $this->assign('valArr',$val_arr);
            if (IS_AJAX) {
                $response = $this->fetch();
                $this->ajaxReturn(array('status'=>1,'_html'=>$response));
            } else {
                $this->display();
            }
        }
        
    }

    /**
     * 渠道状态编辑
     */
    public function agentStatusEdit()
    {
        if(IS_POST){
            $data = I();
            if(!$data['id']) $this->error('操作失败');
            if(D('Admin')->commonExecute('agent',array('id'=>$data['id']),$data,'lg_')){
                $this->success('操作成功');
            }else{
                $this->error('操作失败');
            }
        }
        
    }

    /**
     * 渠道删除
     */
    public function agentDelete()
    {
        if(IS_POST){
            $id = I('id',0,'intval');
            if(empty($id)) $this->ajaxReturn(array('status'=>0,'info'=>'参数有误'));
            if(D('Admin')->commonDelete('agent',array('id'=>$id),'lg_')){
                $this->ajaxReturn(array('status'=>1,'info'=>'操作成功'));
            }else{
                $this->ajaxReturn(array('status'=>0,'info'=>'操作失败'));
            }
        }
        
    }

    /**
     * 渠道新增
     */
    public function agentAdd()
    {
        if(IS_POST){
            $data = I();
            if(empty($data['agent'])){
                $this->error('包号不能为空');
            }

            if(empty($data['agentName'])){
                $this->error('包名称不能为空');
            }

            $agent = D('Admin')->commonQuery('agent',array('agent'=>$data['agent']),0,1,'agent','lg_');
            
            if($agent){
                $this->error('包号已经存在');
            }
            //渠道版本
            if ($data["channel_id"]) {
                $channel = D("Admin")->commonQuery("channel", array("id" => $data["channel_id"]), 0, 1, "channelVer", "lg_");
                if ($channel) $data["channelVer"] = $channel["channelVer"];
            }

            //查出负责人所属的部门
            // $department = D('Admin')->commonQuery('principal',array('id'=>$data['principal_id']),0,1,'department');
            if($data['gameType'] == 1) unset($data['bundleId'], $data['appleId']);
            $data['updateTime']  = time();
            $data['createTime']  = time();
            $data['platform_id'] = 2;
            $data['agentType']   = 1;
            $data['pid']         = 0;
            $data['advterStaus'] = 0;
            // $data['departmentId']  = $department['department'];
            $data['creater']     = session('admin.realname');
            $data['lastPackageTime'] = time();
            //广点通渠道需要换包名
            if($data['advteruser_id'] == 2) $data['changePackage'] = 1;

            if(D('Admin')->commonAdd('agent',$data,'lg_')){
                $this->success('操作成功');
            }else{
                $this->error('操作失败');
            }
        }else{
            if (IS_AJAX) {
                $response = $this->fetch();
                $this->ajaxReturn(array('status'=>1,'_html'=>$response));
            } else {
                $this->display();
            }
        }
    }

    /**
     * 展示融合商品ID
     */
    public function goodsShow()
    {
        $agent  = I("agent");
        $info   = D("Admin")->commonQuery("agent", array("agent" => $agent), 0, 1, "*", "lg_");
        $data   = D("Admin")->commonQuery("fusion_goods", array("agent" => $agent), 0, 9999, "*", "lg_");
        $this->assign("info", $info);
        $this->assign("data", $data);
        if (IS_AJAX) {
            $response = $this->fetch();
            $this->ajaxReturn(array("status" =>1, "_html" => $response));
        } else {
            $this->display();
        }
    }

    /**
     * 添加融合商品ID
     */
    public function addFusionGoods()
    {
        $data   = I();
        //判断数据是否齐全
        if(!$data["agent"] || !$data["goodsCode"] || !$data["channelGoods"]) {
            $res = array(
                "Result"    => false,
                "Msg"       => "商品数据未填写完整！"
            );
            exit(json_encode($res));
        }
        $data["createTime"] = time();
        $id     = D("Admin")->commonAdd("fusion_goods", $data, "lg_");
        if ($id) {
            $res = array(
                "Result"    => true,
                "Msg"       => "添加商品成功！"
            );
        } else {
            $res = array(
                "Result"    => false,
                "Msg"       => "添加商品失败！"
            );
        }
        exit(json_encode($res));
    }

    /**
     * 删除融合商品ID
     */
    public function deleteFusionGoods()
    {
        $agent      = I("agent");
        $goodsCode  = I("goodsCode");
        //判断数据是否齐全
        if(!$agent || !$goodsCode) {
            $res = array(
                "Result"    => false,
                "Msg"       => "获取数据不完整！",
            );
            exit(json_encode($res));
        }

        if (D("Admin")->commonDelete("fusion_goods", array("agent" => $agent, "goodsCode" => $goodsCode), "lg_")) {
            $res = array(
                "Result"    => true,
                "Msg"       => "删除商品ID成功！",
            );
            exit(json_encode($res));
        } else {
            $res = array(
                "Result"    => false,
                "Msg"       => "删除商品ID失败！",
            );
            exit(json_encode($res));
        }
    }

    /**
     * 渠道号重打包
     */
    public function packageAgent()
    {
        if(IS_POST){
            $data = I();
            if (empty($data["type"])) {
                $this->error("类型错误！");
            }

            if (($data["type"] == "1" && empty($data["agent"])) || ($data["type"] == 2 && (empty($data["agent_first"]) || empty($data["agent_last1"]) || empty($data["agent_last2"])))) {
                $this->error("数据未填写完整！");
            }

            if ($data["type"] == "1") {
                //单个渠道号
                $agent = D("Admin")->commonQuery("agent", array("agent" => $data["agent"]), 0, 1, "*", "lg_");
                //判断渠道号是否符合标准
                if ($agent["gameType"] != "1" || $agent["agentType"] != "0" || $agent["pid"] == "0" || $agent["packageStatus"] != "2") {
                    $this->error("该渠道号".$data["agent"]."无法进行重打包操作！");
                }
                //重打包
                if (D("Admin")->commonExecute("agent", array("agent" => $data["agent"], "gameType" => 1, "agentType" => 0, "pid" => array("NEQ", 0), "packageStatus" => 2), array("packageStatus" => 0, "updateTime" => time(), "packagePower" => 1), "lg_")) {
                    $this->success('操作成功');
                } else {
                    $this->error('操作失败');
                }
            } elseif ($data["type"] == "2") {
                //批量打包
                if (!is_numeric($data["agent_last1"]) || !is_numeric($data["agent_last2"])) {
                    $this->error("编号请填写数字！");
                }
                if ($data["agent_last2"] < $data["agent_last1"]) {
                    $this->error("编号顺序填写错误！");
                }
                if (strlen($data["agent_last1"]) != strlen($data["agent_last2"])) {
                    $this->error("编号长度请填写一致！");
                }
                $start_num  = "1".$data["agent_last1"];
                $end_num    = "1".$data["agent_last2"];
                $agent_arr  = array();
                for ($i = $start_num; $i <= $end_num; $i ++) {
                    $agent_arr[] = $data["agent_first"].substr($i, 1);
                }
                $agent      = D("Admin")->commonQuery("agent", array("agent" => array("IN", $agent_arr)), 0, 100000000000, "*", "lg_");

                //判断渠道号是否符合标准
                foreach ($agent as $val) {
                    if ($val["gameType"] != "1" || $val["agentType"] != "0" || $val["pid"] == "0" || $val["packageStatus"] != "2") {
                        $this->error("该渠道号".$val["agent"]."无法进行重打包操作！");
                    }
                }
                //重打包
                if (D("Admin")->commonExecute("agent", array("agent" => array("IN", $agent_arr), "id" => array("GT", 388), "gameType" => 1, "agentType" => 0, "pid" => array("NEQ", 0), "packageStatus" => 2), array("packageStatus" => 0, "updateTime" => time()), "lg_")) {
                    $this->success('操作成功');
                } else {
                    $this->error('操作失败');
                }
            }
        }else{
            if (IS_AJAX) {
                $response = $this->fetch();
                $this->ajaxReturn(array('status'=>1,'_html'=>$response));
            } else {
                $this->display();
            }
        }
    }

    /**
     * 按母包来重打所有渠道包
     */
    public function packageAll()
    {
        if (IS_POST) {
            $data = I();
            if ($data["game_id"] < 1) $this->error("游戏必选");
            if ($data["agent_id"] < 1) $this->error("母包必选");

            //母包信息
            $agent = D("Admin")->commonQuery("agent", array("id"=>$data["agent_id"]), 0, 1, "*", C("DB_PREFIX_API"));
            if (!$agent) $this->error("母包不存在");

            //重打包
            if ($data["type"]) {
                $res = D("Admin")->commonExecute("agent", array("gameType" => 1, "agentType" => 0, "pid" => $agent["id"], "packageStatus" => 2), array("packageStatus" => 0), C("DB_PREFIX_API"));
            } else {
                $res = D("Admin")->commonExecute("agent", array("gameType" => 1, "agentType" => 0, "pid" => $agent["id"], "packageStatus" => 2, "status" => 0), array("packageStatus" => 0), C("DB_PREFIX_API"));
            }
            if ($res) {
                D("Admin")->commonExecute("agent", array("id" => $data["agent_id"]), array("lastPackageTime" => time()), C("DB_PREFIX_API"));
                $this->success("操作成功");
            } else {
                $this->error("操作失败");
            }
        } else {
            if (session("admin.role_id") == 1) {
                $response = $this->fetch();
                $this->ajaxReturn(array("status" => 1, "_html" => $response));
            } else {
                $this->ajaxReturn(array("status" => 0, "_html" => ""));
            }
        }
    }

    /**
     * 渠道号批量添加
     */
    public function batchAddAgent()
    {
        if(IS_POST){
            $data = I();

            if($data['agentnum']<1 || $data['agentnum'] > 100){
                $this->error('生成包个数一次只能1-100个');
            }
            if($data['game_id'] <1){
                $this->error('游戏必选');
            }
            if($data['agent_id']<1){
                $this->error('母包必选');
            }


            //母包信息
            $agent = D('Admin')->commonQuery('agent',array('id'=>$data['agent_id']),0,1,'*',C('DB_PREFIX_API'));
            if(!$agent){
                $this->error('母包不存在');
            }

            //广点通渠道需要换包名
            if ($data['advteruser_id'] == 2) {
                $changePackage = 1;
            } else {
                $changePackage = 0;
            }

            $childAgent = array();
            for ($i=($data['maxAgent']+1); $i <= ($data['maxAgent']+$data['agentnum']); $i++) { 
                $childAgent[] = array(
                    'agentName'        => $agent['agentName'].str_pad($i, 3,'0',STR_PAD_LEFT),
                    'agent'            => preg_replace('/\d/s', '', $agent['agent']).str_pad($i, 3,'0',STR_PAD_LEFT),
                    'game_id'          => $agent['game_id'],
                    'gameType'         => $agent['gameType'],
                    'channel_id'       => $agent['channel_id'],
                    'channelVer'       => $agent['channelVer'],
                    'bundleId'         => $agent['bundleId'],
                    'advteruser_id'    => $data['advteruser_id'],
                    'proxyId'          => $data['proxyId'],
                    'advterAccountId'  => $data['advterAccountId'],
                    'principal_id'     => $agent['principal_id'],
                    'departmentId'     => $agent['departmentId'],
                    'mainbody_id'      => $agent['mainbody_id'],
                    'status'           => $agent['status'],
                    'loginStatus'      => $agent['loginStatus'],
                    'payStatus'        => $agent['payStatus'],
                    'agentCallbackUrl' => $agent['agentCallbackUrl'],
                    'changePackage'    => $changePackage,
                    'createTime'       => time(),
                    'updateTime'       => time(),
                    'agentType'        => 0,
                    'advterStaus'      => 0,
                    'packagePower'     => 1,
                    'pid'              => $agent['id'],
                    'platform_id'      => $agent['platform_id'],
                    'creater'          => session('admin.realname'),
                    'value1'           => $agent['value1'],
                    'value2'           => $agent['value2'],
                    'value3'           => $agent['value3'],
                    'value4'           => $agent['value4'],
                    'value5'           => $agent['value5'],
                    'value6'           => $agent['value6'],
                    'value7'           => $agent['value7'],
                    'value8'           => $agent['value8'],
                    'value9'           => $agent['value9'],
                    'value10'          => $agent['value10'],
                );
            }
            //var_dump($childAgent);die;

            if(D('Admin')->commonAddAll('agent',$childAgent,'lg_')){
                $this->success('操作成功');
            }else{
                $this->error('操作失败');
            }
        }else{
            if (IS_AJAX) {
                $response = $this->fetch();
                $this->ajaxReturn(array('status'=>1,'_html'=>$response));
            } else {
                $this->display();
            }
        }
        
    }

    /**
     * 渠道分类列表
     */
    public function channelTypeList()
    {
        if(IS_POST){
            $data = I();
            $map = array();
            $data['channelName'] && $map['channelName'] = array('like','%'.$data['channelName'].'%');
            $data['advteruser_id'] && $map['advteruser_id'] = $data['advteruser_id'];
            $data['principal_id'] && $map['principal_id'] = $data['principal_id'];
            //权限控制
            $this->pids && $map['principal_id'] = array('in',$this->pids);

            $start=I('start',0,'intval');
            $pageSize=I('limit',30,'intval');
            $list = D('Admin')->getBuiList($this->table,$map,$start,$pageSize,'lg_');
            $advteruser_list = getDataList('advteruser','id',C('DB_PREFIX'),array('status'=>1));
            $principal_list = getDataList('principal','id',C('DB_PREFIX'),array('status'=>1));
            foreach ($list['list'] as $key=>$val){
                $list['list'][$key]['opt'] = createBtn('<a href="javascript:;" onclick="channelTypeEdit('.$val['id'].',this)">编辑</a>');
                $list['list'][$key]['advteruserName'] = $advteruser_list[$val['advteruser_id']]['company_name'];
                $list['list'][$key]['principalName'] = $principal_list[$val['principal_id']]['principal_name'];
                $list['list'][$key]['createTime'] = date('Y-m-d H:i:s',$val['createTime']);
                $rows[] = $list['list'][$key];
            }
            $arr = array('rows'=>(empty($rows) ? array() : $rows),'results'=>$list['count']);
            exit(json_encode($arr));
        }else{
            $this->display();
        }
        
    }

    /**
     * 渠道分类添加
     */
    public function channelTypeAdd()
    {
        if(IS_POST){
            $data = I();
            if(empty($data['channelName'])){
                $this->error('渠道分类名称不能为空');
            }
            if(empty($data['channelAbbr'])){
                $this->error('渠道分类缩写不能为空');
            }
            $old_channelName = D('Admin')->commonQuery('channel',array('channelName'=>$data['channelName']),0,1,'channelName','lg_');
            $old_channelAbbr = D('Admin')->commonQuery('channel',array('channelAbbr'=>$data['channelAbbr']),0,1,'channelAbbr','lg_');
            if($data['channelName'] != $old_channelName['channelName'] && $old_channelName){
                $this->error('渠道分类名称已经存在');
            }
            if($data['channelAbbr'] != $old_channelAbbr['channelAbbr'] && $old_channelAbbr){
                $this->error('渠道分类缩写已经存在');
            }
            $data['createTime'] = time();
            $data['updateTime'] = time();
            if(D('Admin')->commonAdd('channel',$data,'lg_')){
                $this->success('操作成功');
            }else{
                $this->error('操作失败');
            }
        }else{
            if (IS_AJAX) {
                $response = $this->fetch();
                $this->ajaxReturn(array('status'=>1,'_html'=>$response));
            } else {
                $this->display();
            }
        }
        
    }

    /**
     * 渠道分类编辑
     */
    public function channelTypeEdit()
    {
        if(IS_POST){
            $data = I();
            if($data['type'] != 'setchannelStatus'){
                if(empty($data['channelName'])){
                    $this->error('渠道名称不能为空');
                }
                if(empty($data['channelAbbr'])){
                    $this->error('渠道缩写不能为空');
                }
            }

            $old_channelName = D('Admin')->commonQuery('channel',array('channelName'=>$data['channelName']),0,1,'channelName','lg_');
            $old_channelAbbr = D('Admin')->commonQuery('channel',array('channelAbbr'=>$data['channelAbbr']),0,1,'channelAbbr','lg_');
            if($data['channelName'] != $old_channelName['channelName'] && $old_channelName){
                $this->error('渠道名称已经存在');
            }
            if($data['channelAbbr'] != $old_channelAbbr['channelAbbr'] && $old_channelAbbr){
                $this->error('渠道缩写已经存在');
            }
            $data['updateTime'] = time();
            if(D('Admin')->commonExecute('channel',array('id'=>$data['id']),$data,'lg_')){
                $this->success('操作成功');
            }else{
                $this->error('操作失败');
            }
        }else{
            $id = I('id',0,'intval');
            $info = D('Admin')->commonQuery('channel',array('id'=>$id),0,1,'*','lg_');
            
            $this->assign('info', $info);
            if (IS_AJAX) {
                $response = $this->fetch();
                $this->ajaxReturn(array('status'=>1,'_html'=>$response));
            } else {
                $this->display();
            }
        }
        
    }

    /**
     * 批量添加落地页
     */
    public function advListBatchAdd()
    {
        if(IS_POST){
            $data = I();
            !$data['adv_name'] && $this->error('广告名称不能为空');
            !$data['agent'] && $this->error('包编号不能为空');
            $agentArr   = $data['agent'];
            unset($data['agent']);
            $agentId = getDataList('agent','id',C('DB_PREFIX_API'));

            //查出所属广告模板
            $page_type = D('Admin')->commonQuery('material',array('material_id'=>$data['material_id']));
            $data['adv_tpl_id'] = $page_type['page_type'];
            $pic_info = getDataList('file','file_id',C('DB_PREFIX'),array('id'=>$data['material_id']));
            //查出素材的图片个数
            $pic_count = count($pic_info);
            $ioslink = '';
            if(!empty($data['oldid'])){
                $oldlist = D('Admin')->commonQuery('advter_list',array('id'=>$data['oldid']));
                $iosLinksArr = explode(',', $oldlist['ios_url_down']);
                foreach ($iosLinksArr as $key => $value) {
                    if(empty($value)) continue;
                    $ioslink = $value;
                    if($ioslink) break; 
                }
            }

            //组装参数
            $param = array();

            foreach ($agentArr as $key => $value) {
                $advname = D('Admin')->commonQuery('advter_list',array('adv_name'=>$data['adv_name'].'-('.$agentId[$value]['agent'].')'));
                if($advname){
                    $this->error('广告名称已存在,请修改后再添加！');
                }
                $param[$key] = $data;
                $android_url_down = $ios_url_down =  array();
                //安卓链接
                if($data['isall'] == 1){
                    for ($i=1; $i <= $pic_count ; $i++) { 
                        $agentId[$value]['gameType'] == 1 && $android_url_down[] = 'https://static.chuangyunet.net/'.$agentId[$value]['agent'].'.apk';
                    }

                    //ios链接
                    for ($i=1; $i <= $pic_count ; $i++) { 
                        if($agentId[$value]['gameType'] == 2 && !$ioslink){
                            $ios_url_down[] = $agentId[$value]['iosDownUrl'];
                        }else{
                            $ios_url_down[] = $ioslink;
                        }
                    }
                }elseif(count($data['download']) > 0){
                    $downloadNum = array_combine($data['download'],$data['download']);
                    //第几张图需要下载链接
                    for ($i=0; $i < $pic_count ; $i++) {
                        if($agentId[$value]['gameType'] == 1 && $i == $downloadNum[$i]){
                         $android_url_down[] = 'https://static.chuangyunet.net/'.$agentId[$value]['agent'].'.apk';
                        }else{
                         $android_url_down[] = '';
                        }
                    }
                    //ios链接
                    for ($i=0; $i < $pic_count ; $i++) { 
                        if(($agentId[$value]['gameType'] == 2 && !$ioslink) && $i == $downloadNum[$i]){
                            $ios_url_down[] = $agentId[$value]['iosDownUrl'];
                        }else{
                            $ios_url_down[] = $ioslink;
                        }
                    }
                }
                

                

                //生成监控链接
                $monitor_link = $this->createLink($agentId[$value]['agent'],$agentId[$value]['game_id'],$agentId[$value]['gameType'],$agentId[$value]['advteruser_id']);
                $android_url_down = implode(',', $android_url_down);
                $ios_url_down     = implode(',', $ios_url_down);
                $param[$key]['adv_name']         = $data['adv_name'].'-('.$agentId[$value]['agent'].')';
                $param[$key]['android_url_down'] = $android_url_down;
                $param[$key]['ios_url_down']     = $ios_url_down;
                $param[$key]['agent_id']         = $value;
                $param[$key]['advteruser_id']    = $agentId[$value]['advteruser_id'];
                $param[$key]['creater']          = session('admin.realname');
                $param[$key]['create_time']      = time();
                $param[$key]['game_id']          = $agentId[$value]['game_id'];
                $param[$key]['monitor_link']     = $monitor_link;
                unset($android_url_down,$ios_url_down);
            }

            if($param){
                $res = D('Admin')->commonAddAll('advter_list',$param);
                if($res){
                    $this->success('操作成功');
                }else{
                    $this->error('操作失败');
                }
            }else{
                $this->error('操作失败');
            }
        }
    }

    /**
     * 广告列表
     */
    public function advList()
    {
        if(IS_POST){
            $data = I();
            $map = array();
            //权限控制
            $agent = getDataList('agent','agent','lg_');
            $this->pids && $map['principal_id'] = array('in',$this->pids);
            $data['id'] && $map['id'] = $data['id'];
            $data['adv_name'] && $map['adv_name'] = array('like','%'.$data['adv_name'].'%');
            $data['adv_title'] && $map['adv_title'] = array('like','%'.$data['adv_title'].'%');
            $data['material_id'] && $map['material_id'] = $data['material_id'];
            $data['adv_tpl_id'] && $map['adv_tpl_id'] = $data['adv_tpl_id'];
            if($data['creater']){
                $map['creater'] = $data['creater'];
            }else{
                session('admin.partment') && $map['creater'] = session('admin.realname');
            }
            $data['agent_id'] && $map['agent_id'] = $agent[$data['agent_id']]['id'];
            $data['advteruser_id'] && $map['agent_id'] = array('in',array_column(D('Admin')->getAgentInfo($data['advteruser_id']),'id')); //属于该广告商的渠道号id
            $start=I('start',0,'intval');
            $pageSize=I('limit',30,'intval');
            if($data['status'] != '-1'){
                $map['status'] = $data['status'];
            }
            $list = D('Admin')->getBuiList($this->table,$map,$start,$pageSize);
            $game_list = getGameList();
            $agent_list = getDataList('agent','id','lg_');
            $template_list = getDataList('template','id','la_');
            $material_id = getDataList('material','material_id','la_');
            $advteruser = getDataList('advteruser','id','la_');
            $materialStatus = array(
                0 => '<span style="color:green">正常</span>',
                1 => '<span style="color:#001fff">素材有变</span>',
                2 => '<span style="color:red">素材已被删除</span>',
            );

            foreach ($list['list'] as $key=>$val){
               
                $list['list'][$key]['monitor_link'] = htmlspecialchars($val['monitor_link']);
                $list['list'][$key]['opt'] = createBtn('<a target="_blank" href="'.U('Advter/preview',array('adv_id'=>$val['id'])).'">预览</a> | <a onclick=recreate('.$val['id'].','.$val['material_id'].','.$val['cdnId'].') href="javascript:;">重新生成</a> | <a onclick=advListEdit('.$val['id'].') href="javascript:;">编辑</a> | <a onclick=advListDelete('.$val['id'].') href="javascript:;">删除</a> | <a onclick=advListCopy('.$val['id'].') href="javascript:;">复制</a>');
                $list['list'][$key]['game_name'] = $game_list[$val['game_id']]['gameName'];
                $list['list'][$key]['agent'] = $agent_list[$val['agent_id']]['agent'];
                $list['list'][$key]['advteruser'] = $advteruser[$val['advteruser_id']]['company_name'];
                $list['list'][$key]['tpl_name'] = $template_list[$val['adv_tpl_id']]['tpl_name'];
                $list['list'][$key]['create_time'] = date('Y-m-d H:i:s',$val['create_time']);
                $list['list'][$key]['html_filename'] = '<a target="_blank" href="'.$this->cdnDomains[$val['cdnId']].$val['html_filename'].'" onmouseover="showQR(\''.$this->cdnDomains[$val['cdnId']].$val['html_filename'].'\',this)" onmouseout="hideQR()">'.$this->cdnDomains[$val['cdnId']].$val['html_filename'].'</a>';
                $list['list'][$key]['materialStatus'] = $materialStatus[$val['status']];
                $rows[] = $list['list'][$key];
            }
            $arr = array('rows'=>(empty($rows) ? array() : $rows),'results'=>$list['count']);
            exit(json_encode($arr));
        }else{
            $str = '';
            $crt = '';
            foreach (D('Admin')->getTemplate() as $k => $val) {
                $str .= "<option value='{$val['id']}'>{$val['tpl_name']}</option>";
            }
            $creater = M('advter_list')->field('distinct creater')->select();
            foreach ($creater as $k => $val) {
                $crt .= "<option value='{$val['creater']}'>{$val['creater']}</option>";
            }

            $this->assign('tpl_list',$str);
            $this->assign('creater',$crt);
            $this->display();
        }
    }

    /**
     * 广告预览
     */
    public function preview()
    {
        $aid = I('get.adv_id', '0', 'intval');
        empty($aid) && $this->error(L('参数有误'));

        $file = $this->create_static_tpl_new($aid, false, true);
        if ($file !== false) {
            echo $file;
            // $this->display('', '', '', $file);
        } else {
            $this->error('预览错误');
        }
    }

    /**
     * 生成HTML
     * @param inter $aid 推广ID
     * @param bool $create_flag 是否强制从新生成
     */
    private function create_static_tpl_new($aid, $create_flag = false, $preview = false) {

        $info = D('Admin')->getTplInfo($aid);

        if (! $info) {
            return false;
        }

        $htmlfile = 'Game/'.$info['game_id'] . '/' .  $info['agent'] . '/' . $aid . '.html';

        if ($preview || $create_flag || ($create_flag === false && ! file_exists($htmlfile))) { // 文件是否已经存在
            D('Admin')->commonExecute('advter_list',array('id'=>$aid),array('html_filename'=>$htmlfile));
            // 处理备用字段问题
            $mdata = $tarr = array();
            foreach ($info as $k => $v) {
                if (stripos($k, 'material_id') !== false && empty($mdata) && $v) {
                    $tarr = M('material m')->join('la_file f ON m.material_id=f.id')
                        ->field('f.*,m.*')
                        ->where(array('m.material_id' => $v, 'f.type' => 1))
                        ->order('f.order_num asc')
                        ->select();
                }
            }
            // 处理down_url
            if (! empty($info['android_url_down'])) {
                $arr = explode(",", $info['android_url_down']);
                $info['android_url_down'] = $arr;
            }
            if (! empty($info['ios_url_down'])) {
                $iosarr = explode(",", $info['ios_url_down']);
                $info['ios_url_down'] = $iosarr;
            }

            $popArr = $imgArr = array();
            if (! empty($tarr)) {
                foreach ($tarr as $v) {
                    if($v['pop'] != 1){
                        $imgArr[] = $v;
                    }else{
                        $popArr[] = $v;
                    }
                }
            }

            if (! empty($tarr)) {
                $info['images']    = $imgArr;
                $info['opoImages'] = $popArr;
            }
            ! is_dir(dirname($htmlfile)) && @mkdir(dirname($htmlfile), 0777, true);
            $info['slide_position'] = $tarr[0]['slide_position'];
            $info['bgm'] = $tarr[0]['bgm'];
            $this->assign('info', $info);
            $this->assign('mdata', $mdata); // 素材相关资料
            $this->assign('domain', $this->cdnDomains[$info['cdnId']]); // 素材相关资
            if (! $preview) {
                $this->assign('preview', false);
                $this->assign('path_url', $this->cdnDomains[$info['cdnId']]);
                $content = $this->fetch('',$info['tpl_text']);
                if (false === file_put_contents($htmlfile, $content)) {
                    return false;
                }
                return $htmlfile;
            } else {
                $this->assign('preview', true);
                $this->assign('path_url', 'https://' . I('server.HTTP_HOST') . '/');
                return $this->fetch('',$info['tpl_text']);
            }
        }
    }

    /**
     * 重新生成模板
     * @param int $aid 广告id
     */
    public function recreate($aid = '') {
        $advIdArr = I('adv_id');
        empty($aid) ? $adv_id = is_array($advIdArr) ? $advIdArr : array($advIdArr) : $adv_id[] = $aid;
        empty($adv_id) && $this->ajaxReturn(0, L('参数有误'));

        $data = array();
        $file = array();
        foreach ($adv_id as $v) {
            $res = $this->create_static_tpl_new($v, true);
            $file[] = $res;
        }
        // sleep(3);

        if(count($file)<1){
            $this->ajaxReturn(array('status'=>0,'info'=>'刷新失败'));
        }

        $cdnId = getDataList('advter_list','id',C('DB_PREFIX'),array('id'=>array('in',$advIdArr))); //CDNID列表
        foreach ($adv_id as $key => $value) {
            $domain = $this->cdnDomains[$cdnId[$value]['cdnId']];
            $urlArr['urls.'.$key] = $domain.$file[$key];
        }

        $return_data = refresh_cdn($this->secretKey,$this->secretId,$this->action,$urlArr);
        /*var_dump($return_data);die;
        foreach ($domain as $key => $value) {
            foreach ($file as $fk => $fv) {
                $urlArr['urls.'.$fk] = $value.$fv;
            }
            $return_data = refresh_cdn($this->secretKey,$this->secretId,$this->action,$urlArr);
            unset($urlArr);
        }*/
        if (! empty($aid)) {
            //修改素材状态
            D('Admin')->commonExecute('advter_list',array('id'=>array('in',$adv_id)),array('status'=>0));//修改了素材
            
            IS_AJAX && $this->ajaxReturn(array('status'=>1,'info'=>'操作成功'));
            $this->success(L('操作成功'));
        } else {
             //修改素材状态
            D('Admin')->commonExecute('advter_list',array('id'=>array('in',$adv_id)),array('status'=>0));//修改了素材
            $this->ajaxReturn(array('status'=>1,'info'=>'已经重新生成，CDN刷新状态：'.$return_data['code'].',成功刷新条数：'.$return_data['data']['count']));
        }
    }

    /**
     * 广告模板列表
     */
    public function advterTplList()
    {
        if(IS_POST){
            $start=I('start',0,'intval');
            $pageSize=I('limit',30,'intval');
            $list = D('Admin')->getBuiList($this->table,array(),$start,$pageSize);
            foreach ($list['list'] as $key=>$val){
                $list['list'][$key]['opt'] = createBtn('<a href="javascript:;" onclick="advterTplEdit('.$val['id'].',this)">编辑</a> | <a onclick=delete_tpl("'.$val['tpl_name'].'",'.$val['id'].') href="javascript:;">删除</a>');
                $rows[] = $list['list'][$key];
            }
            $arr = array('rows'=>(empty($rows) ? array() : $rows),'results'=>$list['count']);
            exit(json_encode($arr));
        }else{
            $this->display();
        }
        
    }

    /**
     * 代理商列表
     */
    public function proxyList()
    {
        if(IS_POST){
            $data = I();
            $start = $data['start'] ? $data['start'] : 0;
            $pageSize = $data['limit'] ? $data['limit'] : 30;
            $data['id'] && $map['id'] = $data['id'];
            $data['proxyName'] && $map['proxyName'] = array('like','%'.$data['proxyName'].'%');
            $res = D('Admin')->getBuiList($data['table'],$map,$start,$pageSize);
            $results = $res['count'];
            foreach ($res['list'] as $key=>$val){
                $res['list'][$key]['opt'] = createBtn('<a href="javascript:;" onclick="proxyEdit('.$val['id'].',this)">编辑</a>');
                $rows[] = $res['list'][$key];
            }
            $arr = array('rows'=>(empty($rows) ? array() : $rows),'results'=>$results);
            exit(json_encode($arr));
        }else{
            $this->display();
        }
    }

    /**
     * 广告账号列表
     */
    public function advterAccountList()
    {
        if(IS_POST){
            $data = I();
            $start = $data['start'] ? $data['start'] : 0;
            $pageSize = $data['limit'] ? $data['limit'] : 30;
            $data['id'] && $map['id'] = $data['id'];
            $data['account'] && $map['account'] = array('like','%'.$data['account'].'%');

            //添加权限，部门之间只能看到自己部门的
            if(session('admin.partment')!=0)
                $map['departmentId'] = session('admin.partment');

            $res = D('Admin')->getBuiList($data['table'],$map,$start,$pageSize);
            $results = $res['count'];
            $advterUser = getDataList('advteruser','id');
            $proxy      = getDataList('proxy','id');
            foreach ($res['list'] as $key=>$val){
//                $advteruserIdArr = explode(',', $val['advteruserId']);
//                $proxyArr        = explode(',', $val['proxyId']);
//                foreach ($advteruserIdArr as $k => $v) {
//                    $res['list'][$key]['advterUser'] .= ','.$advterUser[$v]['company_name'];
//                }
//
//                foreach ($proxyArr as $k2 => $v2) {
//                    $res['list'][$key]['proxy']      .= ','.$proxy[$v2]['proxyName'];
//                }
//                $res['list'][$key]['proxy']      = trim($res['list'][$key]['proxy'],',');
//                $res['list'][$key]['advterUser'] = trim($res['list'][$key]['advterUser'],',');
                if ($val['departmentId'] == '1') {
                    $res['list'][$key]['department'] = '发行一部';
                } elseif ($val['departmentId'] == '2') {
                    $res['list'][$key]['department'] = '发行二部';
                }
                $res['list'][$key]['advterUser'] = $advterUser[$val['advteruserId']]['company_name'];
                $res['list'][$key]['proxy']      = $proxy[$val['proxyId']]['proxyName'];
                $res['list'][$key]['opt'] = createBtn('<a href="javascript:;" onclick="advterAccountEdit('.$val['id'].',this)">编辑</a>');
                $rows[] = $res['list'][$key];
            }
            $arr = array('rows'=>(empty($rows) ? array() : $rows),'results'=>$results);
            exit(json_encode($arr));
        }else{
            $this->display();
        }
    }

    /**
     * 广告商列表
     */
    public function advterUserList()
    {
        if(IS_POST){
            $data = I();
            $start = $data['start'] ? $data['start'] : 0;
            $pageSize = $data['limit'] ? $data['limit'] : 30;
            $data['id'] && $map['id'] = $data['id'];
            $data['company_name'] && $map['company_name'] = array('like','%'.$data['company_name'].'%');
            $data['principal_name'] && $map['principal_name'] = $data['principal_name'];
            // $principal = principalList($this->pids);
            //权限控制
            // $map['principal_name'] = array('in',array_column($principal,'principal_name'));
            $res = D('Admin')->getBuiList($data['table'],$map,$start,$pageSize);
            $results = $res['count'];
            foreach ($res['list'] as $key=>$val){
                $res['list'][$key]['param'] = htmlspecialchars($val['param']);
                $res['list'][$key]['iosParam'] = htmlspecialchars($val['iosParam']);
                $res['list'][$key]['create_time'] = date('Y-m-d H:i:s',$val['create_time']);
                $res['list'][$key]['opt'] = createBtn('<a href="javascript:;" onclick="advterUserEdit('.$val['id'].',this)">编辑</a>');
                $rows[] = $res['list'][$key];
            }
            $arr = array('rows'=>(empty($rows) ? array() : $rows),'results'=>$results);
            exit(json_encode($arr));
        }else{
            $prins = principalList($this->pids);
            $this->assign('aprincipals',$prins);
            $this->display();
        }
    }
    
    /**
     * 广告商账号列表
     */
    public function advterCompanyList()
    {
        if(IS_POST){
            $data = I();
            $start = $data['start'] ? $data['start'] : 0;
            $pageSize = $data['limit'] ? $data['limit'] : 30;
            $map['b.user_type'] = 2;
            $data['company_name'] && $map['c.company_name'] = array('like','%'.$data['company_name'].'%');
            $data['name'] && $map['b.name'] = array('like','%'.$data['name'].'%');
            $data['company_id'] && $map['a.company_id'] = $data['company_id'];
            $data['principal_id'] && $map['a.principal_id'] = $data['principal_id'];
            $data['company_id'] && $map['a.company_id'] = $data['company_id'];
            //权限控制
            $this->pids && $map['a.principal_id'] = array('in',$this->pids);

            $res = D('Admin')->getAdvterUser($map,$start,$pageSize);
            $results = $res['count'];
            foreach ($res['list'] as $key=>$val){
                $res['list'][$key]['create_time'] = date('Y-m-d H:i:s',$val['create_time']);
                $res['list'][$key]['opt'] = createBtn('<a href="javascript:;" onclick="advterCompanyEdit('.$val['id'].',this)">编辑</a>');
                $rows[] = $res['list'][$key];
            }
            $arr = array('rows'=>(empty($rows) ? array() : $rows),'results'=>$results);
            exit(json_encode($arr));
        }else{
            $prins = principalList();
            $this->assign('aprincipals',$prins);
            $this->display();
        }
    }
    
    /**
     * 编辑get前置操作
     * @param string $table 操作的数据表
     * @param array $info 编辑前的数组
     */
    public function _get_before_update($table,$info)
    {
        if($table == 'advteruser'){
            $pris = principalList();
            $this->assign('aprincipals', $pris);
        }elseif($table == 'advter_company_user'){
            $id = I('id',0,'intval');
            $admin_id = D('Admin')->commonQuery('advter_company_user',array('id'=>$id),0,1,'admin_id');
            $user_info = D('Admin')->commonQuery('admin',array('id'=>$admin_id['admin_id'],'user_type'=>2),0,1,'name,real,status');
            $princials = principalList();
            $map = $ids = array();
            foreach($princials as $v){
                $ids[] = $v['id'];
            }
            $map['a.principal_id'] = array('in', $ids);
            $map['a.pids'] = '0';
            $account_id = M('advter_company_user a')->field('b.name,a.id,a.company_id')->join('left join la_admin b on a.admin_id=b.id')->where($map)->select();
            $this->assign('account_pid',$account_id);
            $this->assign('advterUser',$user_info);
        }elseif($this->table == 'advter_list'){
            $imglist = M('file')->where(array('type' => 1, 'id' => $info['material_id'])) ->field('filename,url') ->order('order_num asc') ->select(); 
            $links = explode(',', $info['android_url_down']);
            $ios_links = explode(',', $info['ios_url_down']);
            $link_arr = array();
            foreach ($imglist as $key => $val) {
                $link_arr['android_links'][$key] = array(
                    'link'     => $links[$key],
                    'filename' => $val['filename'],
                    'url'      => $val['url']
                );
                $link_arr['ios_links'][$key] = array(
                    'link'     => $ios_links[$key],
                    'filename' => $val['filename'],
                    'url'      => $val['url']

                );
            }
            $company_name = D('Admin')->commonQuery('advteruser',array('id'=>$info['advteruser_id']),0,1,'company_name');
            $this->assign('advteruser',$company_name['company_name']);
            $this->assign('cdnDomains',$this->cdnDomains);
            $this->assign('links',$link_arr);
            
        }elseif($table == 'principal'){
            $this->assign('partment',$this->partment);
        }
    }

    /**
     * 删除前置操作
     * @param mix $ids 主键id
     * @return array 处理后数据
     */
    public function _before_del($ids){
        if($this->table == 'advter_list'){
            //判断创建人
            $creater = getDataList($this->table,'creater',C('DB_PREFIX'),array('id'=>array('in',$ids)));
            $arr = array();
            if(session('admin.role_id') != 1){
                //去除不是该创建人的id realname
                foreach ($creater as $key => $value) {
                    if(isset($creater[session('admin.realname')])){
                        $arr[] = $value['id'];
                    }
                }
            }else{
                $arr = $ids;
            }
            $ids = $arr;
        }
        return $ids;
    }
    
    /**
     * 编辑前置操作
     * @param array $data 入库前数据
     * @return array 处理后数据
     */
    public function _before_update($data)
    {
        if($this->table == 'advter_company_user'){
            //判断是否修改广告商密码
            $password = I('password','','string');
            $repassword = I('repassword','','string');
            if(!empty($password) || !empty($repassword)){
                if($repassword != $password){
                    $this->error('两次密码不一致');
                }
                $admin_id = D('Admin')->commonQuery('advter_company_user',array('id'=>$data['id']),0,1,'admin_id')['admin_id'];
                $res = D('Admin')->commonExecute('admin',array('id'=>$admin_id,'user_type'=>2),array('password'=>make_password($password)));
                if(!$res){
                    $this->error('密码修改失败');
                }
            }
            $pids = implode(',', $data['pids']);
            $agent_ids    = implode(',',$data['agent_ids']);
            $game_ids     = implode(',',$data['game_ids']);
            $company_ids  = implode(',', $data['company_id']);

            $data['pids'] = $pids;
            $data['agent_ids']  = $agent_ids;
            $data['game_ids']   = $game_ids;
            $data['company_id'] = $company_ids;

        }elseif($this->table == 'advteruser'){
            $adv_name = D('Admin')->commonQuery('advteruser',array('company_name'=>$data['company_name'],'id'=>array('neq',$data['id'])));
            if($adv_name){
                IS_AJAX && $this->ajaxReturn(0, L('广告商已存在'));
                $this->error(L('广告商已存在'));
            }
        }elseif($this->table == 'advter_list'){
            $android_links = I('android_links');
            $ios_links = I('ios_links');
            $isall = I('isall');

            if ($android_links) {
                if ($isall) {
                    foreach ($android_links as $k => $v) {
                        $android_links[$k] = $android_links[0];
                    }
                }
                $data['android_url_down'] = implode(",", $android_links);
            }
            if ($ios_links) {
                if ($isall) {
                    foreach ($ios_links as $k => $v) {
                        $ios_links[$k] = $ios_links[0];
                    }
                }
                $data['ios_url_down'] = implode(",", $ios_links);
            }

            //判断广告名称是否存在
            $adv_name = D('Admin')->commonQuery('advter_list',array('adv_name'=>$data['adv_name'],'id'=>array('neq',$data['id'])));
            if($adv_name){
                IS_AJAX && $this->ajaxReturn(0, L('广告名称已存在'));
                $this->error(L('广告名称已存在'));
            }

            $agent_id = I('agent_id', '0', 'intval');
            if (! $agent_id) {
                IS_AJAX && $this->ajaxReturn(0, L('选择的渠道号不正确'));
                $this->error(L('选择的渠道号不正确'));
            }
            //渠道号查出对应的游戏id
            $agent_info = D('Admin')->commonQuery('agent',array('id'=>$agent_id),0,1,'agent,game_id,advteruser_id,gameType','lg_');
            if(!$agent_info['game_id']){
                IS_AJAX && $this->ajaxReturn(0, L('渠道号没有对应的游戏ID'));
                $this->error(L('渠道号没有对应的游戏ID'));
            }

            //查出所属广告模板
            $page_type = D('Admin')->commonQuery('material',array('material_id'=>$data['material_id']));
            $data['adv_tpl_id'] = $page_type['page_type'];

            //生成监控链接
            $data['monitor_link'] = $this->createLink($agent_info['agent'],$agent_info['game_id'],$agent_info['gameType'],$agent_info['advteruser_id']);
            
            !isset($data['isall']) && $data['isall'] = 0;
            $data['advteruser_id'] = $agent_info['advteruser_id'];
            $data['game_id'] = $agent_info['game_id'];
            $data['departmentId'] = session('admin.partment');

        }elseif($this->table == 'principal'){
            //判断是否存在同名负责人
            $principal_rolename = D('Admin')->commonQuery('principal',array('principal_rolename'=>$data['principal_rolename'],'id'=>array('neq',$data['id'])));
            $principal_name = D('Admin')->commonQuery('principal',array('principal_name'=>$data['principal_name'],'id'=>array('neq',$data['id'])));

            if($principal_rolename){
                IS_AJAX && $this->ajaxReturn(0, L('负责人简称已存在'));
                $this->error(L('负责人简称已存在'));
            }

            if($principal_name){
                IS_AJAX && $this->ajaxReturn(0, L('负责人真名已存在'));
                $this->error(L('负责人真名已存在'));
            }

        }elseif($this->table == 'advter_account'){
//            $proxyId      = I('request.proxy');
//            $advteruserId = I('request.advteruserId');
//            $data['advteruserId'] = implode(',', $advteruserId);
//            $data['proxyId']      = implode(',', $proxyId);
            $data['updateTime']   = date('Y-m-d H:i:s');
            $data['rebate']       = str_replace('%', '', $data['rebate']);
            if (!$data['password']) unset($data['password']);
        }elseif($this->table == 'mainbody'){
            //判断主体名称是否存在
            $adv_name = D('Admin')->commonQuery('mainbody',array('mainBody'=>$data['mainBody'],'id'=>array('neq',$data['id'])));
            
            if($adv_name){
                IS_AJAX && $this->ajaxReturn(0, L('主体名称已存在'));
                $this->error(L('主体名称已存在'));
            }
        }
        return $data;
    }

    /**
     * 编辑后置操作
     * @param $id
     */
    public function _after_update($id)
    {
        if ($this->table == "advter_account") {
            $info = D("Admin")->commonQuery("advter_account", array("id" => $id), 0, 1, "status");
            D("Admin")->commonExecute("agent", array("advterAccountId" => $id, "status" => $info["status"], "agentType" => 0, "pid" => array("NEQ", 0)), array("status" => (1 - $info["status"])), "lg_");
        }
    }
    
    /**
     * 插入get前置操作
     * @param string $table 操作的数据表
     * @param array $info 插入前的数据
     */
    public function _get_before_insert($table,$info)
    {
        if($table == 'advteruser'){
            $pris = principalList();
            $this->assign('aprincipals', $pris);
        }elseif($table == 'advter_company_user'){
            $princials = principalList();
            $map = $ids = array();
            foreach($princials as $v){
                $ids[] = $v['id'];
            }
            //查出不显示的广告商
            $id = array_column(M('advter_company_user')->field('pids')->where(array('pids'=>array('gt',0)))->select(), 'pids');
            $id = implode(',',$id);
            $map['a.principal_id'] = array('in', $ids);
            $map['a.pids'] = '0';
            $map['b.status'] = 0;
            $map['a.id'] = array('not in',explode(',', $id));
            $account_id = M('advter_company_user a')->field('b.name,a.id,a.company_id')->join('left join la_admin b on a.admin_id=b.id')->where($map)->select();
            $this->assign('account_pid',$account_id);
        }elseif($table == 'advter_list'){
            $this->assign('cdnDomains',$this->cdnDomains);
        }elseif($table == 'principal'){
            $this->assign('partment',$this->partment);
        }
    }
    
    /**
     * 插入前置操作
     * @param string $table 操作的数据表
     */
    public function _before_insert($data)
    {
        if($this->table == 'advteruser'){
            if(D('Admin')->commonQuery('advteruser',array('company_name'=>$data['company_name']))){
                $this->error('广告商名称已存在');
            }
            $data['create_time'] = time();
        } elseif ($this->table == 'advter_company_user'){

            $password = I('password','','string');
            $repassword = I('repassword','','string');
            $name = I('name','','string');
            if(empty($password) || empty($repassword)){
                $this->error('密码不能为空');
            }
            if($repassword != $password){
                $this->error('两次密码不一致');
            }
            if(empty($name)){
                $this->error('账号不能为空');
            }
            $exist = D('Admin')->commonQuery('admin',array('name'=>$name));
            if($exist){
                $this->error('账号已存在');
            }
            //默认关闭状态
            $res = D('Admin')->commonAdd('admin',array('name'=>$name,'real'=>'广告商','manager_id'=>4,'status'=>1,'password'=>make_password($password),'user_type'=>2));
            if(!$res){
                $this->error('账号添加失败');
            }
            $data['admin_id'] = $res;
            $pids         = implode(',', $data['pids']);
            $agent_ids    = implode(',', $data['agent_ids']);
            $game_ids     = implode(',', $data['game_ids']);
            $company_ids  = implode(',', $data['company_id']);

            $data['pids'] = $pids ? $pids : 0;
            $data['agent_ids'] = $agent_ids;
            $data['game_ids']  = $game_ids;
            $data['company_id']  = $company_ids;
            $data['create_time']  = time();

        }elseif($this->table == 'advter_list'){
            $android_links = I('android_links');
            $ios_links = I('ios_links');
            $isall = I('isall');

            
            if ($android_links) {
                if ($isall) {
                    foreach ($android_links as $k => $v) {
                        $android_links[$k] = $android_links[0];
                    }
                }
                $data['android_url_down'] = implode(",", $android_links);
            }
            if ($ios_links) {
                if ($isall) {
                    foreach ($ios_links as $k => $v) {
                        $ios_links[$k] = $ios_links[0];
                    }
                }
                $data['ios_url_down'] = implode(",", $ios_links);
            }

            //判断广告名称是否存在
            $adv_name = D('Admin')->commonQuery('advter_list',array('adv_name'=>$data['adv_name']));
            if($adv_name){
                IS_AJAX && $this->ajaxReturn(0, L('广告名称已存在'));
                $this->error(L('广告名称已存在'));
            }

            $agent_id = I('agent_id', '0', 'intval');
            if (! $agent_id) {
                IS_AJAX && $this->ajaxReturn(0, L('选择的渠道号不正确'));
                $this->error(L('选择的渠道号不正确'));
            }
            //渠道号查出对应的游戏id
            $agent_info = D('Admin')->commonQuery('agent',array('id'=>$agent_id),0,1,'game_id,agent,advteruser_id,gameType','lg_');
            if(!$agent_info['game_id']){
                IS_AJAX && $this->ajaxReturn(0, L('渠道号没有对应的游戏ID'));
                $this->error(L('渠道号没有对应的游戏ID'));
            }

            //查出所属广告模板
            $page_type = D('Admin')->commonQuery('material',array('material_id'=>$data['material_id']));
            $data['adv_tpl_id'] = $page_type['page_type'];

            //生成监控链接
            $data['monitor_link'] = $this->createLink($agent_info['agent'],$agent_info['game_id'],$agent_info['gameType'],$agent_info['advteruser_id']);
            
            $data['advteruser_id'] = $agent_info['advteruser_id'];
            $data['departmentId'] = session('admin.partment');
            $data['creater']       = session('admin.realname');
            $data['create_time']   = time();
            $data['game_id']       = $agent_info['game_id'];
           
        }elseif($this->table == 'principal'){
            $data['create_time'] = time();
            //判断是否存在同名负责人
            $principal_rolename = D('Admin')->commonQuery('principal',array('principal_rolename'=>$data['principal_rolename']));
            $principal_name = D('Admin')->commonQuery('principal',array('principal_name'=>$data['principal_name']));

            if(!empty($principal_rolename['principal_rolename'])){
                IS_AJAX && $this->ajaxReturn(0, L('负责人简称已存在'));
                $this->error(L('负责人简称已存在'));
            }

            if(!empty($principal_name['principal_name'])){
                IS_AJAX && $this->ajaxReturn(0, L('负责人真名已存在'));
                $this->error(L('负责人真名已存在'));
            }
        }elseif($this->table == 'proxy'){
            $data['createTime'] = date('Y-m-d H:i:s');
            $data['creater']    = session('admin.realname');
        }elseif($this->table == 'advter_account'){
//            $proxyId      = I('request.proxy');
//            $advteruserId = I('request.advteruserId');
//            $data['advteruserId'] = implode(',', $advteruserId);
//            $data['proxyId']      = implode(',', $proxyId);

            //判断广告名称是否存在
            $advter_account = D('Admin')->commonQuery('advter_account', array('account' => $data['account'], 'advteruserId' => $data['advteruserId']));
            if ($advter_account) {
                IS_AJAX && $this->ajaxReturn(0, L('广告账户已存在'));
                $this->error(L('广告账户已存在'));
            }
            $data['rebate']       = str_replace('%', '', $data['rebate']);
            $data['createTime']   = date('Y-m-d H:i:s');
            $data['updateTime']   = date('Y-m-d H:i:s');
            $data['creater']      = session('admin.realname');
        }elseif($this->table == 'mainbody'){
            //判断主体名称是否存在
            $adv_name = D('Admin')->commonQuery('mainbody',array('mainBody'=>$data['mainBody']));
            if($adv_name){
                IS_AJAX && $this->ajaxReturn(0, L('主体名称已存在'));
                $this->error(L('主体名称已存在'));
            }
            $data['creater'] = session('admin.realname');
            $data['createTime'] = time();
        }
        return $data;
    }

    /**
     * 插入后置操作
     * @param $id
     */
    public function _after_insert($id)
    {
        if ($this->table == 'advter_account') {
            $admin = D('Admin')->commonQuery('admin', array('id' => session('admin.uid')), 0, 1);
            if($admin){
                if($admin['backstage_account_id']){
                    $info['backstage_account_id'] = $admin['backstage_account_id'].','.$id;
                }else{
                    $info['backstage_account_id'] = $id;
                }
                D('Admin')->commonExecute('admin',array('id'=>session('admin.uid')),$info);
            }
            $finance    = D('Admin')->commonQuery('admin', array('manager_id' => array("IN", array(17, 25)), 'status' => 0));
            if ($finance) {
                foreach ($finance as $value) {
                    if($value['backstage_account_id']){
                        $info['backstage_account_id'] = $value['backstage_account_id'].','.$id;
                    }else{
                        $info['backstage_account_id'] = $id;
                    }
                    D('Admin')->commonExecute('admin',array('id'=>$value['id']),$info);
                }
            }
        }
    }

    /**
     * 监控链接生成
     */
    protected function createLink($agent,$game_id,$os,$adid)
    {
        if($adid == 2){
            $link = "https://count.chuangyunet.net/AdvterClick.php?agent={$agent}&game_id={$game_id}&cy_os=1&adUserId=2";
            return $link;
        }
        //生成监控链接
        $param = D('Admin')->commonQuery('advteruser',array('id'=>$adid),0,1,'param,id');
        $link = '';
        if(!empty($param['param'])){
            if($param['id'] == 7){
                //UC头条
                if($os == 1){
                    $link = "https://count.chuangyunet.net/AdvterClick.php?agent={$agent}&game_id={$game_id}&cy_os={$os}&adUserId={$param['id']}&muid={IMEI_SUM}&{$param['param']}";
                }
                
            }else{
                if($os == 1){
                    $link = "https://count.chuangyunet.net/AdvterClick.php?agent={$agent}&game_id={$game_id}&cy_os={$os}&adUserId={$param['id']}&{$param['param']}";
                }
            }
            
        }
        return $link;
    }

    /**
     * 广告素材列表
     */
    public function material()
    {
        $data = I('request.','','urldecode');
        $data['material_id'] && $map['material_id'] = $data['material_id'];
        $data['material_type_id'] && $map['material_type_id'] = $data['material_type_id'];
        $data['material_name'] && $map['material_name'] = array('like','%'.$data['material_name'].'%');
        
        $data['page_type'] && $map['page_type'] = $data['page_type'];
        $data['begin'] && $data['end'] && $map['create_time'] = array(
            array(
                'egt',
                strtotime($data['begin'])
            ),
            array(
                'elt',
                strtotime($data['end'])
            ),
            'and'
        );
        !$data['spage'] && $data['spage'] = 8;

        session('admin.partment') && $map['author'] = array('IN',array_keys(getDataList('admin','id',C('DB_PREFIX'),array('partment'=>session('admin.partment')))));
        $author = D('Admin')->commonQuery('admin', array('real' => $data['author'] ))['id'];

        ($data['author'] && $author) && $map['_string'] = ' author='.$author;

        $res = D('Admin')->getMaterial($map, $data['spage']);
        foreach ($res['list'] as $k => $v) {
            $res['list'][$k]['tpl_name'] = $this->tplList[$v['page_type']];
        }
        $materialtype = getDataList('material_type','material_type_id',C('DB_PREFIX'),array('status'=>1));
        $this->assign('tplList',$this->tplList);
        $this->assign('search', I('request.','','urldecode'));
        $this->assign('path_url', 'https://' . I('server.HTTP_HOST') . '/');
        $this->assign('spage', $data['spage']);
        $this->assign('page', $res['show']);
        $this->assign('files', $res['file']);
        $this->assign('authors', $res['author']);
        $this->assign('materialTypeId',$materialtype);
        $this->assign('list', $res['list']);
        $this->display();
    }

    /**
     * 素材预览
     */
    public function view()
    {
        $mid = I('get.material_id', '', 'intval');
        $tarr = D("Admin")->material_files($mid);
        $popArr = $imgArr = array();
        if (! empty($tarr)) {
            foreach ($tarr as $v) {
                if($v['pop'] != 1){
                    $imgArr[] = $v;
                }else{
                    $popArr[] = $v;
                }
                if (stripos($v['filename'], '_bg') !== false) {
                    $mdata['bg'] = $v;
                } elseif (stripos($v['filename'], '_banner') !== false) {
                    $mdata['banner'][] = $v;
                } else {
                    $mdata['other'][] = $v;
                }
                empty($mdata['btn']) && $mdata['btn'] = $v;
            }
        }
        $info = array(
            'game_id' => 0,
            'slide_position' => $tarr[0]['slide_position'],
            'adv_title' => '素材预览：' . $tarr[0]['material_name']
        );
        $info['images']    = $imgArr;
        $info['opoImages'] = $popArr;

        $this->assign('info', $info); // 素材相关资料
        $this->assign('mdata', $mdata); // 素材相关资料
        $this->assign('preview', true);
        $this->assign('path_url', 'https://' . I('server.HTTP_HOST') . '/');
        if (I('page_type') == 1) {
            $this->display('Template/banner');
        } elseif (I('page_type') == 2) {
            $this->display('Template/banner_slide');
        } elseif (I('page_type') == 3) {
            $this->display('Template/banner_slide_two');
        } elseif (I('page_type') == 4) {
            $this->display('Template/not_banner_slide');
        } elseif (I('page_type') == 5) {
            $this->display('Template/up_slide');
        } elseif (I('page_type') == 6) {
            $this->display('Template/banner_slide_three');
        } elseif (I('page_type') == 7) {
            $this->display('Template/no_banner_slide');
        } elseif (I('page_type') == 8) {
            $this->display('Template/noBanner_pop');
        } elseif (I('page_type') == 9) {
            $this->display('Template/banner_slide_bottom');
        } elseif (I('page_type') == 10) {
            $this->display('Template/banner_bottom');
        } elseif (I('page_type') == 11) {
            $this->display('Template/banner_one_slide_bottom');
        } elseif (I('page_type') == 12) {
            $this->display('Template/banner_slide_new');
        }
    }

    /**
     * 广告素材删除
     */
    public function materialDelete()
    {
        $id = I('material_id', '0', 'intval');
        // 超级管理员可以删除，其他账户只有作者可以删除
        
        if (! D('Admin')->commonQuery("material", array(
            'author' => session('admin.uid'),
            'material_id' => $id
        )) && session('admin.role_id') != 1) {
            $this->error('操作失败');
        }
        
        // 删除文件
        $list = M('file')->where(array(
            'id' => $id,
            'type' => 1
        ))->select();
        if ($list) {
            foreach ($list as $v) {
                @unlink('./' . $v['url']);
            }
        }
        $list = M('file')->where(array(
            'id' => $id,
            'type' => 1
        ))->delete();
        // 删除素材
        $res = D('Admin')->commonDelete('material', array(
            'material_id' => $id
        ));
        if ($res) {
            D('Admin')->commonExecute('advter_list',array('material_id'=>$id),array('status'=>2));

            IS_AJAX && $this->ajaxReturn(array('status'=>1,'info'=>'操作成功'));
            $this->success('操作成功');
        } else {
            IS_AJAX && $this->ajaxReturn(array('status'=>0,'info'=>'操作失败'));
            $this->error('操作失败');
        }
    }

    /**
     * 广告素材添加
     */
    public function materialAdd()
    {
        if (IS_POST) {
            $mod = D('material');
            $data = $mod->create();
            $page_type = I('page_type', 0, 'intval');
            
            if ($page_type == 1) {
                unset($data['hdp_width'], $data['slide_position'],$data['slide']);
            } elseif ($page_type == 2 || $page_type == 3 || $page_type == 6 || $page_type == 7 || $page_type == 8) {
                if (!$data['hdp_width']) {
                    // 1:banner 2:banner+幻灯片混合 3:banner+一屏落地页
                    if($page_type == 3 || $page_type == 6){
                        $data['hdp_width'] = 100;
                    }else{
                        $data['hdp_width'] = 88;
                    }
                }
                if(!$data['hdp_time']){
                    $data['hdp_time'] = 5;
                }
                
            } elseif($page_type == 5 && empty($data['bgcolor'])) {
                $data['bgcolor'] = '100, 103, 213';
            }
            $material_name = D('Admin')->commonQuery('material',array('material_name'=>$data['material_name']));
            if($material_name){
                IS_AJAX && $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => '素材名称已存在'
                ));
                $this->error('素材名称已存在');
            }
            $data['author'] = session('admin.uid');
            $data['create_time'] = time();
            $data['status'] = 0;
            $id = $mod->add($data);
            if ($id) {
                // 文件上传
                $files = file_upload($id);
                
                $file_order  = I('order_num');
                $file_check  = I('file_check');
                $pop_check   = I('pop_check');
                $video_check = I('video_check');

                $data2 = array();
                $msize = $f = $b = 0;
                $bgm   = '';
                foreach ($files as $val) {

                    $order_num = $check_num = $pop_num = $video_num = '';
                    if ($val['key'] === 'screenshot') {
                        $thumb = 1;
                    } else {
                        $thumb = '0';
                        $msize += $val['size'];
                        if ($val['key'] === 'file' && !in_array($val['ext'],array('aac','mp3'))) {
                            if (isset($file_order[$f]) && $file_order[$f]) {
                                $order_num = intval($file_order[$f]);
                            }
                            if (isset($file_check[$f]) && $file_check[$f]) {
                                $check_num = intval($file_check[$f]);
                            }
                            if (isset($pop_check[$f]) && $pop_check[$f]) {
                                $pop_num = intval($pop_check[$f]);
                            }
                            if (isset($video_check[$f]) && $video_check[$f]) {
                                $video_num = intval($video_check[$f]);
                            }
                            $f ++;
                        }elseif(in_array($val['ext'],array('aac','mp3'))){
                            $bgm = '/Uploads/' . $val['savepath'] . $val['savename'];
                        }
                    }
                    
                    $data2[] = array(
                        'filename' => $val['savename'],
                        'url'      => '/Uploads/' . $val['savepath'] . $val['savename'],
                        'filesize' => $val['size'],
                        'type'     => 1,
                        'thumb'    => $thumb,
                        'id'       => $id,
                        'order_num'=> $order_num,
                        'slide'    => $check_num,
                        'pop'      => $pop_num,
                        'video'    => $video_num,
                        'link'     => '',
                        'create_time' => time()
                    );
                }

                $bgm && $mod->where(array('material_id'=>$id))->save(array('bgm'=>$bgm));
                
                M("file")->addAll($data2);
                $mod->where(array(
                    'material_id' => $id
                ))->setField(array(
                    'material_file_size' => $msize
                ));
                bgLog(4, session('admin.username') . '添加了素材');
                IS_AJAX && $this->ajaxReturn(1, L('素材新增成功'), '', 'add');
                $this->success(L('素材新增成功'));
            } else {
                IS_AJAX && $this->ajaxReturn(0, L('operation_failure'));
                $this->error(L('operation_failure'));
            }
        } else {
            $materialtype = D('Admin')->getMaterialType();
            $this->assign('tplList',$this->tplList);
            $this->assign('material_type_id',$materialtype);
            IS_AJAX && $this->ajaxReturn(array(
                'status' => 1,
                '_html' => $this->fetch()
            ));
            $this->display();
        }
    }

    /**
     * 广告素材编辑
     */
    public function materialEdit()
    {
        $mod = D('material');
        $mid = I('material_id', '0', 'intval');
        $page_type = I('page_type');
        
        $list = D('Admin')->commonQuery('material', array(
            'material_id' => $mid
        ));
        if (IS_POST) {
            if (false === $data = $mod->create()) {
                IS_AJAX && $this->ajaxReturn(0, $mod->getError());
                $this->error($mod->getError());
            }
            
            if ($page_type == 1) {
                unset($data['hdp_width'], $data['slide_position'],$data['slide']);
            } elseif ($page_type == 2 || $page_type == 3 || $page_type == 6 || $page_type == 7 || $page_type == 8) {
                if (!$data['hdp_width']) {
                    // 1:banner 2:banner+幻灯片混合 3:banner+一屏落地页
                    if($page_type == 3 || $page_type == 6){
                        $data['hdp_width'] = 100;
                    }else{
                        $data['hdp_width'] = 88;
                    }
                }
                
            } elseif ($page_type == 5 && empty($data['bgcolor'])) {
                $data['bgcolor'] = '100, 103, 213';
            }
            
            if ($list) {
                // 文件上传
                $files = file_upload($mid);
                
                $data2 = $data3 = array();
                $ctime = time();
                
                $file_order = I('order_num');
                
                $f = $b = 0;
                $bgm = '';
                foreach ($files as $val) {
                    $order_num = $link = '';
                    if ($val['key'] === 'screenshot') {
                        $thumb = 1;
                    } else {
                        $thumb = '0';
                        if ($val['key'] === 'file' && !in_array($val['ext'],array('aac','mp3'))) {
                            if (isset($file_order[$f]) && $file_order[$f]) {
                                $order_num = intval($file_order[$f]);
                            }
                            
                            $f ++;
                        }elseif(in_array($val['ext'],array('aac','mp3'))){
                            $bgm = '/Uploads/' . $val['savepath'] . $val['savename'];
                        }
                    }
                    $tarr = array(
                        'filename' => $val['savename'],
                        'url' => '/Uploads/' . $val['savepath'] . $val['savename'],
                        'filesize' => $val['size'],
                        'type' => 1,
                        'thumb' => $thumb,
                        'id' => $mid,
                        'order_num' => $order_num,
                        'link' => '',
                        'create_time' => $ctime
                    );
                    if ($val['key'] === 'screenshot') {
                        $data3 = $tarr;
                    } else {
                        $list['material_file_size'] += $val['size'];
                        $data2[] = $tarr;
                    }
                }
                $data['material_file_size'] = $list['material_file_size'];
                $bgm && $data['bgm'] = $bgm;
                $filemodule = M("file");
                
                $filemodule->addAll($data2);
                
                if ($data3) {
                    $row = $filemodule->where(array(
                        'id' => $mid,
                        'type' => 1,
                        'thumb' => 1
                    ))->find();
                    if ($row) {
                        $data3['file_id'] = $row['file_id'];
                        $filemodule->save($data3);
                    } else {
                        $filemodule->add($data3);
                    }
                }
                if (false !== $mod->where(array(
                    'material_id' => $mid
                ))->save($data)) {
                    bgLog(3, session('admin.username') . '修改了素材');
                    //修改落地页的状态
                    D('Admin')->commonExecute('advter_list',array('material_id'=>$mid),array('status'=>1));
                    $this->success(L('操作成功'));
                } else {
                    $this->error(L('操作失败'));
                }
            } else {
                $this->error(L('操作失败'));
            }
        } else {
            $this->assign('tplList',$this->tplList);
            $this->assign('files', D('Admin')->material_files($mid));
            $this->assign('path_url', 'https://' . I('server.HTTP_HOST') . '/');
            $this->assign('mtypes', M('material_type')->where(array(
                'status' => 1
            ))->select());
            $this->assign('info', $list);
            if (IS_AJAX) {
                $response = $this->fetch();
                $this->ajaxReturn(array(
                    'status' => 1,
                    '_html' => $response
                ));
            } else {
                $this->display();
            }
        }
    }
    
    /**
     * 修改素材排序
     */
    public function orderEdit()
    {
        $param = I();
        $rs = D('Admin')->commonExecute('file',array('file_id'=>$param['id']),array('order_num'=>$param['order_num']));
        $material_id = D('Admin')->commonQuery('file',array('file_id'=>$param['id']));
        if($rs !== false){
            D('Admin')->commonExecute('advter_list',array('material_id'=>$material_id['id']),array('status'=>1));
            $this->ajaxReturn(array('status'=>1,'info'=>'修改成功'));
        }else{
            $this->ajaxReturn(array('status'=>0,'info'=>'修改失败'));
        }
    }

    /**
     * 复制素材
     */
    public function copyMaterial()
    {
        $mid = I('mid',0,'intval');
        if(empty($mid)) $this->ajaxReturn(array('status'=>0,'info'=>'参数有误'));
        //查出该素材的信息
        $minfo = D('Admin')->commonQuery('material',array('material_id'=>$mid));
        $finfo = D('Admin')->commonQuery('file',array('id'=>$mid),0, 1000, '*');
        if($minfo && $finfo){
            $minfo['material_name'] = $minfo['material_name'].time();
            $minfo['create_time']   = time();
            $minfo['author']        = session('admin.uid');
            unset($minfo['material_id']);
            $lastId = D('Admin')->commonAdd('material',$minfo);
            if($lastId){
                //复制图片 35_0.png"
                if(!file_exists('./Uploads/material/'.$lastId)) mkdir('./Uploads/material/'.$lastId,0755,true);
                $fileArr = array();

                foreach ($finfo as $k => $val) {
                    list($first,$ext) = explode('_', basename($val['url']));
                    $first = $lastId;
                    $filePath = './Uploads/material/'.$lastId.'/'.$first.'_'.$ext;
                    copy('.'.$val['url'], $filePath);
                    unset($val['file_id']);
                    $val['filename']    = $first.'_'.$ext;
                    $val['url']         = substr($filePath, 1);
                    $val['id']          = $lastId;
                    $val['create_time'] = time();
                    $fileArr[] = $val;
                }

                
                $res = D('Admin')->commonAddAll('file',$fileArr);
                if($res){
                    $this->ajaxReturn(array('status'=>1,'info'=>'复制成功'));
                }
            }
        }else{
            $this->ajaxReturn(array('status'=>0,'info'=>'参数有误'));
        }
    }
    
    /**
     * 修改素材幻灯片
     */
    public function slideEdit()
    {
        $param = I();
        $rs = D('Admin')->commonExecute('file',array('file_id'=>$param['id']),array('slide'=>$param['slide']));
        $material_id = D('Admin')->commonQuery('file',array('file_id'=>$param['id']));
        if($rs !== false){
            D('Admin')->commonExecute('advter_list',array('material_id'=>$material_id['id']),array('status'=>1));
            $this->ajaxReturn(array('status'=>1,'info'=>'修改成功'));
        }else{
            $this->ajaxReturn(array('status'=>0,'info'=>'修改失败'));
        }
    }


    /**
     * 修改素材弹窗
     */
    public function popEdit()
    {
        $param = I();
        $rs = D('Admin')->commonExecute('file',array('file_id'=>$param['id']),array('pop'=>$param['pop']));
        $material_id = D('Admin')->commonQuery('file',array('file_id'=>$param['id']));
        if($rs !== false){
            D('Admin')->commonExecute('advter_list',array('material_id'=>$material_id['id']),array('status'=>1));
            $this->ajaxReturn(array('status'=>1,'info'=>'修改成功'));
        }else{
            $this->ajaxReturn(array('status'=>0,'info'=>'修改失败'));
        }
    }

    /**
     * 修改素材视频
     */
    public function videoEdit()
    {
        $param = I();
        $rs = D('Admin')->commonExecute('file',array('file_id'=>$param['id']),array('video'=>$param['video']));
        $material_id = D('Admin')->commonQuery('file',array('file_id'=>$param['id']));
        if($rs !== false){
            D('Admin')->commonExecute('advter_list',array('material_id'=>$material_id['id']),array('status'=>1));
            $this->ajaxReturn(array('status'=>1,'info'=>'修改成功'));
        }else{
            $this->ajaxReturn(array('status'=>0,'info'=>'修改失败'));
        }
    }
    
    /**
     * 删除素材图片
     */
    public function deleteFile()
    {
        $file_id = I('file_id',0,'intval');
        $mid = I('material_id',0,'intval');
        if ($file_id && $mid) {
            $mod = D('Admin');
            $finfo = $mod->commonQuery('file',array('file_id'=>$file_id,'type'=>1,'id'=>$mid));
            if(!finfo){
                $this->ajaxReturn(array('status'=>0,'info'=>'删除失败'));
            }
            //超级管理员可以不判断权限
            if(session('admin.role_id') == 1){
                $res = $mod->commonDelete('file',array('type'=>1,'id'=>$mid,'file_id'=>$file_id));
            }else{
                $m_find = $mod->commonQuery('material',array('author'=>session('admin.uid'),'material_id'=>$mid));
                $m_find && $file_delete = $mod->commonDelete('file',array('type'=>1,'id'=>$mid,'file_id'=>$file_id));
                $m_find && $file_delete && $res = true;
            }
            
            if ($res) {
                @unlink('./'.$finfo['url']);
                M('material')->where(array('material_id'=>$mid))->setDec('material_file_size', $finfo['filesize']);
                bgLog(2,session('admin.username').'删除了素材文件'.$mod->_sql());
                D('Admin')->commonExecute('advter_list',array('material_id'=>$mid),array('status'=>1));//修改了素材
                $this->ajaxReturn(array('status'=>1,'info'=>'删除成功'));
            } else {
                $this->ajaxReturn(array('status'=>0,'info'=>'删除失败'));
            }
        } else {
            $this->ajaxReturn(array('status'=>0,'info'=>'删除失败'));
        }
    }
    
    /**
     * 素材分类列表
     */
    public function materialType(){
        if(IS_POST){
            $data = I();
            $start = $data['start'] ? $data['start'] : 0;
            $pageSize = $data['limit'] ? $data['limit'] : 30;
            $data['material_type_id'] && $map['material_type_id'] = $data['material_type_id'];
            $data['mtype_name'] && $map['mtype_name'] = array('like','%'.$data['mtype_name'].'%');
            $res = D('Admin')->getBuiList($data['table'],$map,$start,$pageSize);
            $results = $res['count'];
            foreach ($res['list'] as $key=>$val){
                $res['list'][$key]['create_time'] = date('Y-m-d H:i:s',$val['create_time']);
                $res['list'][$key]['opt'] = createBtn('<a href="javascript:;" onclick="materialTypeEdit('.$val['material_type_id'].',this)">编辑</a>');
                $rows[] = $res['list'][$key];
            }
            $arr = array('rows'=>$rows,'results'=>$results);
            exit(json_encode($arr));
        }else{
            $this->display();
        }
    }
    
    /**
     * 素材分类添加
     */
    public function materialTypeAdd()
    {
        if(IS_POST){
            $data = I();
            $data['create_time'] = time();
            $res = D('Admin')->commonAdd('material_type',$data);
            if($res){
                $this->success('操作成功');
            }else{
                $this->error('操作失败');
            }
        }else{
            $this->ajaxReturn(array('status'=>1,'_html'=>$this->fetch()));
        }
    }
    
    /**
     * 素材分类状态修改
     */
    public function materialTypeEdit()
    {
        if(IS_POST){
            $data = I();
            if(!$data['id']){
                IS_AJAX && $this->ajaxReturn(array(
                    'status'=>0,
                    'info'=>'参数有误'
                ));
                $this->error('参数有误');
            }

            if(D('Admin')->commonExecute('material_type',array('material_type_id'=>$data['id']),$data)){
                IS_AJAX && $this->ajaxReturn(array(
                    'status'=>1,
                    'info'=>'修改成功'
                ));
                $this->success('修改成功');
            }else{
                IS_AJAX && $this->ajaxReturn(array(
                    'status'=>0,
                    'info'=>'修改失败'
                ));
                $this->success('修改失败');
            }
        }else{
            $data = I();
            $mtype = D('Admin')->commonQuery('material_type',array('material_type_id'=>$data['id']));
            $this->assign('mtype',$mtype);

            if(IS_AJAX){
                $respose = $this->fetch();
                $this->ajaxReturn(array('status'=>1,'_html'=>$respose));
            }else{
                $this->display();
            }
        }
    }

    /**
     * 主体列表
     */
    public function mainBody(){
        if(IS_POST){
            $data = I();
            $start = $data['start'] ? $data['start'] : 0;
            $pageSize = $data['limit'] ? $data['limit'] : 30;
            $data['mainBody'] && $map['mainBody'] = array('like','%'.$data['mainBody'].'%');
            $res = D('Admin')->getBuiList($data['table'],$map,$start,$pageSize);
            $results = $res['count'];
            foreach ($res['list'] as $key=>$val){
                $res['list'][$key]['createTime'] = date('Y-m-d H:i:s',$val['createTime']);
                $res['list'][$key]['opt'] = createBtn('<a href="javascript:;" onclick="mainBodyEdit('.$val['id'].',this)">编辑</a>');
                $rows[] = $res['list'][$key];
            }
            $arr = array('rows'=>$rows,'results'=>$results);
            exit(json_encode($arr));
        }else{
            $this->display();
        }
    }

}