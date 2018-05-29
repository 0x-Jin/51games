<?php
/**
 * Created by Zend.
 * User: XSM
 * Date: 2017/6/6
 * Time: 20:19
 *
 * AJAX数据返回
 */

namespace Cy\Controller;
use Cy\Controller\BackendController;

class AjaxController extends BackendController
{
    private $agentPath    = "https://static.chuangyunet.net/";                                  //静态渠道包的下载链接
    private $oldAgentPath = "https://fall.chuangyunet.net/Game/apk/";                            //旧静态渠道包的下载链接
    /**
     * 后台菜单列表
     */
    public function menuList()
    {
        $tree = new \Vendor\Tree\Tree();
        $tree->icon = array('&nbsp;&nbsp;&nbsp;│ ','&nbsp;&nbsp;&nbsp;├─ ','&nbsp;&nbsp;&nbsp;└─ ');
        $tree->nbsp = '&nbsp;&nbsp;&nbsp;';
        $result = D('Admin')->getMenu();
        $array = array();
        foreach($result as $r) {
            $r['cname'] = L($r['name']);
            $r['link'] = "<a href=\'javascript:;\' class=\'menuadd\' title=\'添加子菜单\' onclick=\'subMenuAdd(".$r['id'].")\' >添加子菜单</a> | <a href=\'javascript:;\' class=\'menuedit\' title=\'编辑菜单\' onclick=\'menuEdit(".$r['id'].")\'>编辑</a> | <a href=\'javascript:;\' class=\'menudelte\' title=\'删除菜单\' onclick=\'menuDelete(".$r['id'].")\'>删除</a>";
            $array[] = $r;
        }
        $str = "{'id':\$id,'name':'\$spacer\$name','order_id':\$order_id,'link':'\$link'},";
        /* $str  = "<tr onMouseOver='mouseColor(this,1)' onMouseOut='mouseColor(this,0)'>
                <td align='center'><input type='checkbox' value='\$id' class='J_checkitem'></td>
                <td align='center'>\$id</td>
                <td>\$spacer<span data-tdtype='edit' data-field='name' data-id='\$id' class='tdedit'>\$name</span></td>
                <td align='center'><span data-tdtype='edit' data-field='ordid' data-id='\$id' class='tdedit'>\$order_id</span></td>
                <td align='center'>\$str_manage</td>
                </tr>"; */
        $tree->init($array);
        $menu_list = $tree->get_tree(0, $str);
        $menu_str = '['.rtrim(str_replace("'", '"', $menu_list),',').']';
        
        $menu_arr = json_decode($menu_str,true);
        $list = array(
            'rows'=>$menu_arr,
            'results'=>count($menu_arr)
        );
        exit(json_encode($list));
    }

    /**
     * 栏目列表
     */
    public function columnList()
    {
        $colmun = D("Website")->commonQuery("column", array(), 0, 99999, "id,columnName,pid,FROM_UNIXTIME(createTime,'%Y-%m-%d') AS createTime");
        
        $array = array();
        $tree_model = new \Vendor\Tree\Tree();
        $tree_model->icon = array('&nbsp;&nbsp;&nbsp;│ ','&nbsp;&nbsp;&nbsp;├─ ','&nbsp;&nbsp;&nbsp;└─ ');
        $tree_model->nbsp = '&nbsp;&nbsp;&nbsp;';
        foreach($colmun as $r) {
 
            $r['str_manage'] = createBtn("<a href=\'javascript:;\' class=\'menuadd\' title=\'添加子菜单\' onclick=\'subColumnAdd(".$r['id'].")\' >添加子栏目</a> | <a href=\'javascript:;\' class=\'menuedit\' title=\'编辑栏目\' onclick=\'columnEdit(".$r['id'].")\'>编辑</a>");
            $array[] = $r;
        }

        $str = "{'id':\$id,'columnName':'\$spacer\$columnName','createTime':'\$createTime','str_manage':'\$str_manage'},";

        $tree_model->init($array);
        $menu_list = $tree_model->get_tree(0, $str);

        $menu_str = '['.rtrim(str_replace("'", '"', $menu_list),',').']';
        $menu_arr = json_decode($menu_str,true);
        $list = array(
            'rows'=>$menu_arr,
            'results'=>count($menu_arr)
        );

        exit(json_encode($list));
    }

    /**
     * 获取菜单ID
     */
    public function getMenuId()
    {
        $name = I("menu");
        $menu = M("menu",C('DB_PREFIX'),'CySlave')->where(array("name" => $name))->field("id")->find();
        exit(json_encode($menu? array("id" => $menu["id"]): array()));
    }

    //获取部门负责人(添加后台账号用)
    public function departmentPrincipals()
    {
        $departmentId = I('departmentId',0,'intval');
        $map = array();
        !empty($departmentId) && $map['department'] = $departmentId;
        $list = M('principal')->field('principal_name as name,id')->where($map)->select();
        echo json_encode($list ? $list : array());
        exit();
    }
    
    //获取负责人
    public function principals()
    {
        $all = (int)$_REQUEST['all'];
        $map = array();

        //权限控制
        $this->pids && $map['id'] = array('in',$this->pids);
        $map['status'] = 1;
        $list = M('principal',C('DB_PREFIX'),'CySlave')->field('principal_name as name,id')->where($map)->select();
        !empty($all) && array_unshift($list, array('id'=>0,'name'=>'--全部--'));
        echo json_encode($list ? $list : array());
        exit();
    }
    
    //获取广告商列表
    public function adv_company() 
    {

        $all = (int)$_REQUEST['all'];
        $map = array();
        $map['a.status'] = 1;
        //权限控制
        // $this->pids && $map['b.id'] = array('in',$this->pids);

        $list = M('advteruser a',C('DB_PREFIX'),'CySlave')->join(C('DB_PREFIX').'principal b on a.principal_name=b.principal_name')->field('a.id,a.company_name')->where($map)->order('a.id asc ')->select(); 
        !empty($all) && array_unshift($list, array('id'=>0,'company_name'=>'--全部--'));
        echo json_encode($list);
        exit();
    }

    //获取代理商列表
    public function getProxy() 
    {

        $all = (int)$_REQUEST['all'];
        $map = array();

        $list = M('proxy',C('DB_PREFIX'),'CySlave')->field('id,proxyName')->select();
        !empty($all) && array_unshift($list, array('id'=>0,'proxyName'=>'--无--'));
        echo json_encode($list);
        exit();
    }

    //获取广告账户列表
    public function getAdvterAccount() 
    {

        $all = (int)$_REQUEST['all'];
        $map = array();

        $list = M('advter_account',C('DB_PREFIX'),'CySlave')->field('id,account')->select();
        !empty($all) && array_unshift($list, array('id'=>0,'account'=>'--无--'));
        echo json_encode($list);
        exit();
    }
    
    //渠道号
    public function agent()
    {
        $cid = I("request.principal_id",0,"intval");
        $principal_name = I("request.principal_name");
        $aid = I("request.advteruser_id",0,"intval");
        $game_id = I("request.game_id",0,"intval");
        $packageStatus = I("request.packageStatus",0,"intval");
        $partmentId = I("request.partmentId",0,"intval");

        $where = 'a.status=0';
        $packageStatus && $where .= ' and a.packageStatus = 2';
        $game_id && $where .= ' and a.game_id = '.$game_id;
        $cid && $where .= ' and a.principal_id='.$cid;
        $principal_name && $where .= ' and b.principal_name="'.$principal_name.'"';
        $aid && $where .= ' and a.advteruser_id='.$aid;
        $partmentId && $where .= ' and a.departmentId='.$partmentId;
        
        $data = M('agent a',C('DB_PREFIX_API'),'CySlave')->field("a.id,CONCAT(a.agentName,'[',a.agent,']') AS agent")->join('la_principal b on a.principal_id=b.id')->where($where)->select(); 
        //array_unshift($data, array('id'=>0,'text'=>'全部','agent'=>'0'));
        echo json_encode($data ? $data : array());
        exit();
    }

    //根据游戏id获取渠道号
    public function getAgentByGame()
    {
        $game_id = I('request.game_id',0,'intval');
        $advteruser_id = I('request.advteruser_id',0,'intval');
        $creater = I('request.creater');
        $agent = I('request.agent');
        $gameType = I('request.gameType',0,'intval');

        if($game_id && is_array($game_id)){
            $map['game_id'] = array('in',$game_id);
        }elseif($game_id){
            $map['game_id'] = $game_id;
        }

        if($advteruser_id && is_array($advteruser_id)){
            $map['advteruser_id'] = array('in',$advteruser_id);
        }elseif($advteruser_id){
            $map['advteruser_id'] = $advteruser_id;
        }

        if($creater && is_array($creater)){
            $map['creater'] = array('in',$creater);
        }elseif($creater){
            $map['creater'] = $creater;
        }

        if (false !== ($key = array_search("--请选择母包--", $agent)) || false !== ($key = array_search("--全部--", $agent))) array_splice($agent, $key, 1);
        if ($agent) {
            $map["pid"] = array("in", $agent);
        }else{
            $map['agent'] = array('in',$this->agentArr);
        }

        !empty($gameType) && $map['gameType']  = $gameType;

        $list = M('agent',C('DB_PREFIX_API'),'CySlave')->where($map)->field("agent as agents,CONCAT(agentName,'[',agent,']') as agent")->select();  

        exit(json_encode($list ? $list : array()));
    }

    /**
     * 根据母包寻找所有子包
     */
    public function getChildAgentByAgent()
    {
        $agent          = I('request.agent');
        $game_id        = I('request.game_id');
        $advteruser_id  = I('request.advteruser_id',0,'intval');

        if (false !== ($key = array_search("--请选择母包--", $agent))) array_splice($agent, $key, 1);

        if($game_id && is_array($game_id)){
            $map['a.game_id'] = array('in',$game_id);
        }elseif($game_id){
            $map['a.game_id'] = $game_id;
        }

        if($advteruser_id && is_array($advteruser_id)){
            $map['a.advteruser_id'] = array('in',$advteruser_id);
        }elseif($advteruser_id){
            $map['a.advteruser_id'] = $advteruser_id;
        }

        if ($agent) {
            $map["b.agent"] = array("IN", $agent);
        }
        $map["a.agent"] = array("IN", $this->agentArr);
        $map['a.status'] = 0;   //渠道状态为开启
        $list           = M("agent", C("DB_PREFIX_API"),'CySlave')->alias("a")->join("lg_agent b on b.id = a.pid", "LEFT")->where($map)->field("a.agent as agent,CONCAT(a.agentName,'[',a.agent,']') as agentAll")->order("a.agent ASC")->select();
        exit(json_encode($list ? $list : array()));
    }

    public function getEventList()
    {
        $all = (int)$_REQUEST['all'];
        $map = array();
        session('admin.partment') && $map['department'] = session('admin.partment');
        $list =  M('events',C('DB_PREFIX'),'CySlave')->field('id,events_name')->where($map)->select();
        // !empty($all) && array_unshift($list, array('id'=>0,'events_name'=>'--全部--'));
        echo json_encode($list ? $list : array());
        exit();
    }

    //广告模板
    public function tplList()
    {
        $data = M('template',C('DB_PREFIX'),'CySlave')->field('id,tpl_name')->select(); 
        echo json_encode($data ? $data : array());
        exit();
    }

    //素材图片链接
    public function getImageList()
    {
        $mid = I('mid');
        if ($mid) {
            //查出素材类型
            $page_type = M('material',C('DB_PREFIX'),'CySlave')->field('page_type')->where(array('material_id'=>$mid))->find();
            $img_list  = M('file',C('DB_PREFIX'),'CySlave')->where(array('type' => 1, 'id' => $mid))->field('filename,url')->order('order_num asc')->select();
            foreach ($img_list as $key => $value) {
                $img_list[$key]['page_type'] = $page_type['page_type'];
            }
            echo json_encode($img_list ? $img_list : array());
        }
    }


    //获取素材id列表
    public function materialId()
    {
        $agent_id = I('agent_id',0,'intval');
        $map = array();
        if($agent_id){
            if(is_array($agent_id)){
                $agent = M('agent',C('DB_PREFIX_API'),'CySlave')->where(array('id'=>array('IN',$agent_id)))->select();
                $arr   = array();
                foreach ($agent as $k => $v) {
                    $v['pid'] ? $arr[] = $v['pid'] : $arr[] = $v['id'];
                }
                $map['agent_id'] = array('IN',$arr);
            }else{
                $agent = M('agent',C('DB_PREFIX_API'),'CySlave')->where(array('id'=>$agent_id))->find()['pid'];
                $map['agent_id'] = ($agent? $agent: $agent_id);
            }
            
        }
        //查出素材
        $list = M('material',C('DB_PREFIX'),'CySlave')->field("material_id AS id,CONCAT(material_name,'[',material_id,']') AS name,page_type")->where($map)->select();
        echo json_encode($list ? $list : array());
    }

    //获取素材id列表(二部)
    public function materialId2()
    {
        $agent_id = (int)$_REQUEST['agent_id'];
        //查出渠道号的母包id
        $agentId = M('agent',C('DB_PREFIX_API'),'CySlave')->field('pid')->where(array('id'=>$agent_id))->find();
        //查出素材
        $list = M('material',C('DB_PREFIX'),'CySlave')->field("material_id AS id,CONCAT(material_name,'[',material_id,']') AS name,page_type")->where(array('agent_id'=>$agentId['pid']))->select();
        echo json_encode($list ? $list : array());
    }

    /**
     * 游戏列表
     * @return array 游戏列表
     */
    public function getGameList() 
    {
        //CP查看游戏列表有限制
        $map = array();
        !is_null($this->gameId) && $map['id'] = array('in',$this->gameId);
        $all = (int)$_REQUEST['all'];
        $map['_string'] = ' id <> 104';
        $field = 'id,gameName';
        $mod = M('game',C('DB_PREFIX_API'),'CySlave');
        $list = $mod->field($field)->where($map)->select();
        !empty($all) && array_unshift($list, array('id'=>0,'gameName'=>'--请选择游戏--'));
        echo json_encode($list ? $list : array());
        exit();
    }

    /**
     * 获取区服列表
     * @return [type] [description]
     */
    function getServerList()
    {
        $game_id        = I('request.game_id');
        $agent          = I('request.agent');
        if (false !== ($key = array_search("--请选择母包--", $agent))) array_splice($agent, $key, 1);

        $game_id && $map['game_id'] = $game_id;
        $agent && $map["agent"] = array("IN", $agent);
        $mod = M('server',C('DB_PREFIX_API'),'CySlave');
        $list = $mod->field("serverId,serverName,openTime")->where($map)->group("serverId")->select();

        echo json_encode($list ? $list : array());
        exit();
    }
    
    /**
     * 渠道分类列表
     * @return array 渠道分类列表
     */
    public function getChannelList() 
    {
        $all = (int)$_REQUEST['all'];
        $field = 'id,channelName';
        $mod = M('channel',C('DB_PREFIX_API'),'CySlave');
        $list = $mod->where($map)->field($field)->select();
        !empty($all) && array_unshift($list, array('id'=>0,'channelName'=>'--全部--'));
        echo json_encode($list ? $list : array());
        exit();
    }

    /**
     * 获取渠道的参数
     */
    public function getChannelKey()
    {
        $id     = $_REQUEST["id"];
        $list   = M("channel", C("DB_PREFIX_API"),'CySlave')->where("id = ".$id)->find();
        if (!$list) exit(json_encode(array()));
        $arr    = array();
        for ($i = 1; $i <= 10; $i ++) {
            if ($list["param".$i]) {
                $arr[] = array("name" => $i, "value" =>$list["param".$i]);
            } else {
                break;
            }
        }
        exit(json_encode($arr));
    }

    /**
     * 渠道号自动分类（根据游戏缩写分配）
     * @return array 渠道号
     */
    public function autoCreateAgent() 
    {
        $game_id = I('game_id',0,'intval');
        empty($game_id) && exit(json_encode(array()));
        //查出游戏的缩写
        $mod = M('game',C('DB_PREFIX_API'),'CySlave');
        $game_info = $mod->where(array('id'=>$game_id))->field('gameAlias')->find();
        //查出该游戏的最新渠道号
        $agent_info = M('agent',C('DB_PREFIX_API'),'CySlave')->where(array('game_id'=>$game_id))->field('agent')->order('id desc')->find();

        if(!$agent_info){
            $agent = $game_info['gameAlias'].'001';
        }else{
            //判断渠道号后面是否为数字
            if(!preg_replace('/\D/s', '', $agent_info['agent'])){
                $agent = $agent_info['agent'].'001';
            }else{
                //在原来的渠道号+1
                $agent = $game_info['gameAlias'].str_pad(preg_replace('/\D/s', '', $agent_info['agent'])+1, 3,'0',STR_PAD_LEFT);
            }
            
        }
        $list = array(array('agent'=>$agent));
        echo json_encode($list ? $list : array());
        exit();
    }

    /**
     * 获取渠道号名称
     * @return array 渠道号
     */
    public function getAgentName() 
    {
        $all = (int)$_REQUEST['all'];
        $game_id = (int)$_REQUEST['game_id'];
        if(empty($game_id)){return false;}
        $mod = M('agent',C('DB_PREFIX_API'),'CySlave');
        $map['agent'] = array('in',$this->agentArr);
        $map['game_id'] = $game_id;
        $list = $mod->field('distinct agentName as agentName,agentName as id')->where($map)->select();
        !empty($all) && array_unshift($list, array('agentName'=>'--全部--','id'=>0));
        echo json_encode($list ? $list : array());
        exit();
    }

    /**
     * 获取母包渠道号模板
     * @return array 渠道号
     */
    public function getAgent() 
    {
        $power      = (int)$_REQUEST['power'];
        $gameType   = (int)$_REQUEST['gameType'];
        $game_id    = $_REQUEST['game_id'];
        $creater    = $_REQUEST['creater'];
        $channel    = $_REQUEST['channel'];

        if(is_array($game_id) && in_array('--全部--',$game_id)) unset($game_id[array_search("--全部--", $game_id)]);

        $advteruser_id = (int)$_REQUEST['advteruser_id'];

        if($advteruser_id && is_array($advteruser_id)){
            $map['advteruser_id'] = array('in',$advteruser_id);
        }elseif($advteruser_id){
            $map['advteruser_id'] = $advteruser_id;
        }

        if($creater && is_array($creater)){
            $map['creater'] = array('in',$creater);
        }elseif($creater){
            $map['creater'] = $creater;
        }

        !empty($game_id) && $map['game_id']    = array('IN',$game_id);
        !empty($gameType) && $map['gameType']  = $gameType;
        empty($power) && $map['agent'] = array('in',$this->agentArr);
        
        if($channel == 'all'){
//            $map['channel_id'] = array('gt',1);
            $map['_string'] = 'platform_id = 2 OR agent LIKE "%TAPTAP%"';
        }elseif($channel && $channel != 'all'){
            $map['channel_id'] = $channel;
        }

        $mod = M('agent',C('DB_PREFIX_API'),'CySlave');
        $map['agentType'] = 1; //子渠道号
        $map['status'] = 0;   //渠道状态为开启
        $list = $mod->field('id,agent,agentName,CONCAT(agentName,"[",agent,"]") AS agentAll')->where($map)->select();
        echo json_encode($list ? $list : array());
        exit();
    }

    /**
     * 获取渠道包创建人列表
     * @return array 渠道号
     */
    public function getAgentCreater() 
    {
        $all = (int)$_REQUEST['all'];
        $cacheName = 'createrName_'.session('admin.username').'_'.session('admin.partment').'_'.$all;
        if(!S($cacheName)){
            //查出所在部门所有的用户名
            if(session('admin.partment')){
                $adminUser = M('admin',C('DB_PREFIX'),'CySlave')->field('real')->where(array('partment'=>session('admin.partment')))->select();
                if($adminUser){
                    $adminUser = array_column($adminUser, 'real');
                }
            }
            $mod = M('agent',C('DB_PREFIX_API'),'CySlave');
            $map = array('creater IS NOT NULL');
            if($adminUser){
                $map['creater'] = array('IN',$adminUser);
            }
            $list = $mod->field('distinct creater as creater')->where($map)->select();
            !empty($all) && array_unshift($list, array('creater'=>'--全部--'));
            S($cacheName,$list,600);
        }else{
            $list = S($cacheName);
        }
        echo json_encode($list ? $list : array());
        exit();
    }

    /**
     * 获取导入创建人
     * @return [type] [description]
     */
    public function getImportCreater() 
    {
        $all = (int)$_REQUEST['all'];
        $list = M('vip_user',C('DB_PREFIX'),'CySlave')->field("DISTINCT creater")->select();
        !empty($all) && array_unshift($list, array('creater'=>'--全部--'));
        
        echo json_encode($list ? $list : array());
        exit();
    }

    /**
     * 获取广告组列表
     * @return [type] [description]
     */
    public function getEventGroup()
    {
        $mod = M('events_group',C('DB_PREFIX'),'CySlave');
        $map = array();
        session('admin.partment') && $map['department'] = session('admin.partment');
        $list = $mod->field('id,groupName')->where($map)->order('id DESC')->select();
        echo json_encode($list ? $list : array());
        exit();
    }

    public function getEventByGroup(){
        $events_groupId = I('request.events_groupId');

        if (false !== ($key = array_search("--全部--", $events_groupId))) array_splice($events_groupId, $key, 1);
        if ($events_groupId) {
            $map["events_groupId"] = array("in", $events_groupId);
        }

        $mod = M('events',C('DB_PREFIX'),'CySlave');
        session('admin.partment') && $map['departments'] = session('admin.partment');
        $list = $mod->field('id,events_name')->where($map)->order('id DESC')->select();
        echo json_encode($list ? $list : array());
        exit();
    }

    /**
     * 获取属于当前母包id的子包最大渠道编号
     * @return array 渠道号
     */
    public function getMaxAgentId() 
    {
        $agent_id = (int)$_REQUEST['agent_id'];
        if(empty($agent_id)){return false;}
        $mod = M('agent',C('DB_PREFIX_API'),'CySlave');
        $map['agentType'] = 0; //子渠道号
        $map['pid']       = $agent_id;
        $list = $mod->field('id,agent')->where($map)->select();

        $agentId = $mod->field('agent')->where(array('id'=>$agent_id))->find();
        $agentName  = preg_replace('/\d/s', '', $agentId['agent']);
        if(!$list){
            //返回母包编号
            $maxAgentId = preg_replace('/\D/s', '', $agentId['agent'])+0;
            empty($maxAgentId) && $maxAgentId = 0;
            exit(json_encode(array('maxAgentId'=>$maxAgentId,'agentName'=>$agentName)));
        }else{
            //判断渠道号后面是否为数字
            $agentArr = array();
            foreach($list as $k=>$v){
                $agentArr[] = preg_replace('/\D/s', '', $v['agent'])+0;
            }
            rsort($agentArr);
            if(!$agentArr[0] || !is_numeric($agentArr[0])){
                //如果最大为非数字
                exit(json_encode(array('maxAgentId'=>0,'agentName'=>$agentName)));
            }else{
                exit(json_encode(array('maxAgentId'=>$agentArr[0],'agentName'=>$agentName)));
            }
            
        }
        
    }

    /**
     * 获取渠道包的下载链接
     * 
     */
    public function getAgentLink()
    {
        $data = I();
        $link = '';
        if(!$data['agent_id']) exit(json_encode(array('link'=>'','gameType'=>'','creater'=>'')));
        $agent_info = M('agent',C('DB_PREFIX_API'),'CySlave')->where(array('id'=>$data['agent_id']))->find();
        //属于这个两个母包的sqmxTAND,sqmxAND，下载链接换新的
        if(in_array($agent_info['pid'],array(8453,9535))){
            $this->agentPath = 'https://cdn.chuangyunet.net/';
        }
        if($agent_info){
            //旧链接 https://fall.chuangyunet.net/Game/apk/lsyxzjAND005.apk
            if($agent_info['packageStatus'] == 2 && $agent_info['pid'] != 0){
                if($agent_info['id']<=398){
                    $link = $this->oldAgentPath.$agent_info['agent'].'.apk';
                }else{
                    $link = $this->agentPath.$agent_info['agent'].'.apk';
                }
            }elseif($agent_info['gameType'] == 2){
                $link = $agent_info['iosDownUrl'];
            }
            
            exit(json_encode(array('link'=>$link,'gameType'=>$agent_info['gameType'],'creater'=>$agent_info['creater'])));
        }else{
            exit(json_encode(array('link'=>'','gameType'=>'','creater'=>$agent_info['creater'])));
        }
    }

    /**
     * 获取合同的主要条款
     */
    public function getContractInfo()
    {
        $data = I();
        //C("DEFAULT_FILTER", "htmlspecialchars");
        if(!$data["id"]) exit(json_encode(array("info"=>"")));
        $contract = M("contract",C('DB_PREFIX'),'CySlave')->where(array("id"=>$data["id"]))->find();
        if ($contract) {
            exit(json_encode(array("info" => str_replace("\r\n", "<br/>", $contract["info"]))));
        } else {
            exit(json_encode(array("info" => "")));
        }
    }

    /**
     * 获取合同的主要条款
     */
    public function getContractLog()
    {
        $data = I();
        if(!$data["id"]) exit(json_encode(array("info" => "")));
        $operation = M("operation_log",C('DB_PREFIX'),'CySlave')->where(array("id" => $data["id"]))->find();
        if ($operation) {
            exit(json_encode(array("info" => $operation["record"])));
        } else {
            exit(json_encode(array("info" => "")));
        }
    }

    /**
     * 获取官网的栏目数据
     */
    public function getWebsiteColumn()
    {
        $column = D("Website")->commonQuery("column", array(), 0, 999999);
        $res    = array();
        foreach ($column as $v) {
            $res[] = array("text" => $v["columnName"], "value" => $v["id"]);
        }
        exit(json_encode($res));
    }

    /**
     * 获取官网列表
     */
    public function getWebsiteHome()
    {
        $map = array();
        $department = session('admin.partment');
        $department && $map['departmentId'] = $department;
        $list = D("Website")->commonQuery("home", $map, 0, 999999);
        echo json_encode($list? $list: array());
        exit();
    }

    /**
     * 保存用户的合同显示项的设置
     */
    public function saveContractOption()
    {
        $data = I();
        if(!$data["option"]) $data["option"] = "0";
        $uid = session("admin.uid");
        $option = implode(",", $data["option"]);
        if (false !== M("admin")->where("id = ".$uid)->save(array("contractOption" => $option))) {
            exit(json_encode(array("code" => true)));
        } else {
            exit(json_encode(array("code" => false)));
        }
    }

    /**
     * 获取主体名称
     * @return array 主体名称
     */
    public function getMainBody() 
    {
        $mod = M('mainbody',C('DB_PREFIX'),'CySlave');
        $list = $mod->field('id,mainBody')->select();
        array_unshift($list, array('mainBody'=>'--选择主体--','id'=>0));
        echo json_encode($list ? $list : array());
        exit();
    }
}