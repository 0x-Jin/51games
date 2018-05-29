<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/6/3
 * Time: 14:05
 *
 * 账户模块
 */

namespace ThirdParty\Model;

use Think\Model;

class AdminModel extends Model
{
    public function __construct(){
        parent::__construct();
    }
    
    /**
     * 后台公用数据查询
     * @param string $table 表名，不需要前缀
     * @param Array $map 搜索条件
     * @param int 查询页码
     * @param int 查询条数
     * @param string $field 表字段
     * @return Array 查询结果
     */
    public  function commonQuery($table, $map = array(), $page=0, $offset=1, $field='*',$prefix='la_' )
    {
        if($offset === 1){
            if($field == '*'){
                $res = M($table,$prefix,'CySlave')->where($map)->find();
            }else{
                $res = M($table,$prefix,'CySlave')->field($field)->where($map)->find();
            }
        }else{
            if($field == '*'){
                $res = M($table,$prefix,'CySlave')->where($map)->limit($page,$offset)->select();
            }else{
                $res = M($table,$prefix,'CySlave')->field($field)->where($map)->limit($page,$offset)->select();
            }
            
        }
        
        
        if(!$res){
            return false;
        }
        return $res;
        
    }

    /**
     * 后台公用数据更新
     * 
     * @param string $table 表名，不需要前缀
     * @param Array $map   更新条件
     * @param Array $data   更新的数据
     * @return int 返回受影响条数
     */
    public function commonExecute($table, $map = array(), $data = array(), $prefix='la_')
    {
        $res = false;
        $mod = M($table,$prefix);
        $pk  = $mod->getPk();
        $fields = $mod->getDbFields();
        foreach ($fields as $k => $v) {
            if (isset($data[$v])) {
                $update[$v] = $data[$v];
            }
        }
        
        if (count($update) > 0 && count($map) > 0) {
            $res = $mod->where($map)->save($update);
            bgLog(3,$mod->getTableName()."  修改ID:".$map[$pk]);
        }

        return $res;
    }
    
    /**
     * 后台公用数据删除
     *
     * @param string  $table 表名，不需要前缀
     * @param Array $map   删除条件
     * @return int 返回受影响条数
     */
    public function commonDelete($table, $map = array(), $prefix='la_')
    {
        $res = false;
        if ($table &&  count($map) > 0) {
            $mod = M($table,$prefix);
            $res = $mod->where($map)->delete();
            bgLog(2,$mod->getTableName()."  删除ID:".implode(',', $ids).$mod->_sql());
        }
        return $res;
    }
    
    /**
     * 后台公用数据添加
     *
     * @param string $table 表名，不需要前缀
     * @param Array $data   添加的数据
     * @return int 返回受影响条数
     */
    public function commonAdd($table, $data = array(), $prefix='la_')
    {
        $res = false;
        if ($table &&  count($data) > 0) {
            $mod = M($table,$prefix);
            $res = $mod->add($data);
            bgLog(4,$mod->getTableName()."  添加ID".$id.$mod->_sql());
        }
        return $res;
    }

    /**
     * 后台公用数据批量添加
     *
     * @param string $table 表名，不需要前缀
     * @param Array $data   添加的数据
     * @return int 返回受影响条数
     */
    public function commonAddAll($table, $data = array(), $prefix='la_')
    {
        $res = false;
        if ($table &&  count($data) > 0) {
            $mod = M($table,$prefix);
            $res = $mod->addAll($data);
            bgLog(4,$mod->getTableName()."  添加ID".$id.$mod->_sql());
        }
        return $res;
    }
    
    /**
     * 后台菜单读取
     * @param int $pid  父id
     * @return Array  菜单数组
     */
    public function bgMenu($pid=0) {
        $pid = intval($pid);
        $map['pid'] = $pid;
        $map['status'] = 1;
        $menus = M("menu",C('DB_PREFIX_TP'),'CySlave')->where($map)->order('order_id')->select();
        $auth = M('manager_menu',C('DB_PREFIX_TP'),'CySlave')->where(array('manager_id'=>session('admin.role_id')))->select();
        if(session('admin.role_id')==1) {    //超级管理员默认不用验证
            $my_menus=$menus;
        }else {
            foreach($menus as $v) {
                foreach($auth as $val) {
                    if($v['id']==$val['menu_id']) {
                        $my_menus[]=$v;
                    }
                }
            }
        }
        return $my_menus;
    }
    
    /**
     * 后台所有菜单读取
     */
    public function getMenu() {
        $menus = M("menu",C('DB_PREFIX_TP'),'CySlave')->order('order_id')->select();
        return $menus;
    }
    
    /**
     * 后台角色列表获取
     * @param Array $map 搜索条件
     * @param Int $start 从第几条记录开始
     * @param Int $pageSize 每页显示的条数
     * @param string $type all不需要分页，limit需要分页
     * @return Array 返回结果数组
     */
    public function getManager($map=1,$start=0,$pageSize=30,$type = 'all',$prefix='la_') {
        if($type == 'all'){
            $list = M("manager",$prefix,'CySlave')->where($map)->select();
        }elseif($type == 'limit' ){
            $list = M('manager',$prefix,'CySlave')->where($map)->limit($start,$pageSize)->select();
        }
        $count = M("manager",$prefix,'CySlave')->where($map)->count();
        
        return array('list'=>$list,'count'=>$count);
    }
    
    /**
     * 后台账户列表
     * @param Array $map 搜索条件
     * @param Int $start 从第几条记录开始
     * @param Int $pageSize 每页显示的条数
     * @return Array 返回结果数组
     */
    public function getUser($map=array(),$start=0,$pageSize=30)
    {
        $count = M('admin a',C('DB_PREFIX_TP'),'CySlave')->where($map)->count();
        $list = M('admin a',C('DB_PREFIX_TP'),'CySlave')->field('a.id,a.name,a.manager_id,a.real,FROM_UNIXTIME(a.lastLogin) as lastLogin,a.lastIP,a.status,b.name as role_name,partment')->join('left join tp_manager b on a.manager_id=b.id')->where($map)->limit($start,$pageSize)->select();
        return array('list'=>$list,'count'=>$count);
    }

    /**
     * 根据广告商id或者渠道id获取agent信息
     * @param int $advid 广告商id
     * @param int $channel_id 渠道id
     * @return array 渠道号信息
     */
    public function getAgentInfo($advid=0,$channel_id=0)
    {
        $mod = M('agent','lg_','CySlave');
        !empty($advid) && $map['advteruser_id'] = $advid;
        !empty($channel_id) && $map['channel_id'] = $channel_id;
        return $mod->where($map)->select();
    }
    

    /**
     * 根据负责人id获取渠道号
     * @param int $principal_id 负责人id
     * @return array 渠道号信息
     */
    public function getAgent($principal_id=0)
    {
        $mod = M('agent','lg_','CySlave');
        if(empty($principal_id)) return false;
        return $mod->where(array('principal_id'=>$principal_id))->select();
    }

    /**
     * 菜单等级
     * @param int $id
     * @param array $array
     * @param int $i
     * @return number
     */
    public function getLevel($id,$array=array(),$i=0) {
        foreach($array as $n=>$value){
            if ($value['id'] == $id) {
                if($value['pid']== '0') return $i;
                $i++;
                return $this->getLevel($value['pid'],$array,$i);
            }
        }
    }
    
    /**
     * 获取角色的权限
     * @param int $role_id
     */
    public function getRole($role_id) {
        //查出自己
        $role = M('manager',C('DB_PREFIX_TP'),'CySlave')->where(array('id'=>$role_id))->find();;
        $role_menu = M('manager_menu',C('DB_PREFIX_TP'),'CySlave')->where(array('manager_id'=>$role_id))->select();
        $role['role_priv'] = $role_menu;
        return $role;
    }
    
    /**
     * 获取素材列表
     * @param array $map 查询条件
     * @param int $pageSize 每页显示多少条
     * @return array $res 返回结果
     */
    public function getMaterial($map=array(),$pageSize=20) {
        //素材
        $mod = M('material',C('DB_PREFIX'),'CySlave');
        $count = $mod->where($map)->count();
        $page = new \Think\Page($count,$pageSize);

        $page -> setConfig('header','共%TOTAL_ROW%条');
        $page -> setConfig('first','首页');
        $page -> setConfig('last','共%TOTAL_PAGE%页');
        $page -> setConfig('prev','上一页');
        $page -> setConfig('next','下一页');
        $page -> setConfig('link','indexpagenumb');//pagenumb 会替换成页码
        $page -> setConfig('theme','%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $material = $mod->where($map)->limit($page->firstRow,$page->listRows)->order('material_id desc')->select();

        $mid = array_unique(array_column($material, 'material_id'));
        $uid = array_column($material, 'author');
        if(!mid || !$uid) return false;
        //素材文件
        $file = field_to_key(M('file',C('DB_PREFIX'),'CySlave')->field('url,file_id,id')->where(array('id'=>array('in',array_unique($mid)),'type'=>1))->order('order_num desc')->select(), 'id');
        //制作人
        $authors = field_to_key(M('admin',C('DB_PREFIX'),'CySlave')->field('id,real')->where(array('id'=>array('in',array_unique($uid))))->select(), 'id');

        //素材分类
        $mtypes = M('material_type',C('DB_PREFIX'),'CySlave')->where(array('status'=>1))->select();
        $search = I('request.','','urldecode');
        $search['spage'] = $pageSize;
        $page->parameter = $search;
        $show = $page->show();
 
        return array('show'=>$show,'file'=>$file,'author'=>$authors,'mtypes'=>$mtypes,'list'=>$material);
        
    }
    
    /**
     * 获取素材和对应的图片
     * @param int $mid 素材id
     * @return array 素材信息
     */
    public function material_files($mid)
    {
        $list = M('material m',C('DB_PREFIX'),'CySlave')->join('la_file f ON m.material_id=f.id')
        ->field('f.*,m.*')
        ->where(array('m.material_id'=>$mid,'f.thumb'=>'0', 'f.type'=>1))->order('f.order_num asc')->select();

        return $list;
    }

    /**
     * 获取素材分类
     * @return string 素材分类
     */
    public function getMaterialType()
    {
        $list = M('material_type',C('DB_PREFIX'),'CySlave')->where(array('status'=>1))->select();
        $str = '';
        foreach ($list as $key => $value) {
            $str .= "<option value={$value['material_type_id']}>{$value['mtype_name']}</option>";
        }
        return $str;
    }

    /**
     * 获取广告模板
     * @return string 模板列表
     */
    public function getTemplate()
    {
        $list = M('template',C('DB_PREFIX'),'CySlave')->select();
        return $list;
    }
    
    /**
     * 广告商账户列表集合
     * @param array $map 搜索条件
     * @param int $start 从第几条记录开始
     * @param int $pageSize 每页显示的条数
     * @return array 返回结果数组
     */
    public function getAdvterUser($map=array(),$start=0,$pageSize=30)
    {
        $mod = M('advter_company_user a',C('DB_PREFIX'),'CySlave');
        $list = $mod->where($map)->field('a.*,b.name,b.real,b.status')->join('left join la_admin b on a.admin_id = b.id')->limit($start,$pageSize)->select();
        $prins = principalList();
        $pid = array_column($prins, 'principal_name','id');
        $advteruser = getDataList('advteruser','id');

        foreach ($list as $k => $v){
            if(is_array($company_id = explode(',',$v['company_id']))){
                foreach($company_id as $key=>$value){
                    $list[$k]['company_name'] .= ','.$advteruser[$value]['company_name'];
                }
            }else{
                $list[$k]['company_name'] = $advteruser[$value]['company_name'];
            }
            $list[$k]['company_name'] = trim($list[$k]['company_name'],',');
            if($pid[$v['principal_id']]){
                $list[$k]['principal_name'] = $pid[$v['principal_id']];
            }
        }

        return array('list'=>$list,'count'=>count($list));
    }

    /**
     * 广告模板信息
     * @param int $aid 广告id
     * @return array 返回结果数组
     */
    public function getTplInfo($aid)
    {
        $mod = M('advter_list a',C('DB_PREFIX'),'CySlave');
        $info = $mod->join('JOIN la_template t ON a.adv_tpl_id=t.id')
            ->join('JOIN lg_agent c ON a.agent_id=c.id')
            ->field('a.isall,a.copyright_company,a.company_address,a.company_tel,a.id,a.cnzz_code,a.ios_url_down,a.android_url_down,a.advteruser_id,a.auto_download,c.game_id,c.agent,t.tpl_text,a.adv_title,a.material_id,a.color,a.bgcolor')
            ->where(array('a.id' => $aid))
            ->find();
        return $info;
    }

    /**
     * 查出负责人月份业绩
     * @param date $month 月份
     * @param array $agent 负责人所有的渠道号
     * @return array 返回结果数组
     */
    public function getMonthOrder($month='',$agent=array())
    {   
        if(empty($agent) || empty($month)) return false;
        $mod = M('order',C('DB_PREFIX_API'),'CySlave');
        $start = strtotime($month);
        $end = strtotime($month.'+1 month');
        $map['createTime'] = array(array('egt',$start),array('lt',$end),'and');
        $map['orderStatus'] = 0;
        $map['agent'] = array('in',$agent);
        $amount = $mod->where($map)->sum('amount');
        $amount<=0 && $amount=0;
        return $amount;
    }

    /**
     * 获取用户留存数据
     * @param array $map 搜索条件
     * @param string $where 汇总搜索条件
     * @return array 返回结果数组
     */
    public function getUserRemainData($map,$start=0,$pageSize=30,$where=1)
    {
        //lookType 1：根据游戏查看汇总 2：查看明细
        $lookType = I('lookType',0,'intval');
        $export = I('export',0,'intval');
        if(empty($lookType)) return array('list'=>array(),'count'=>array());

        $mod = M('sp_user_game_day',C('DB_PREFIX'),'CySlave');
        $days = '';
        $day = '';
//        $day_arr = array(1,2,3,4,5,6,7,8,9,13,14,15,29,30,59,89);
        $day_arr = array();//定义数组
        for($i = 1; $i<90; $i++){
            $day_arr[] = $i;
        }
        for($i=0;$i<=120;$i++){
            if(in_array($i, $day_arr)){
                $days .= "SUM(day{$i}) AS day{$i},";
                $day .= "day{$i},";
            }
        }
        $days = trim($days,',');
        $day = trim($day,',');

        $field2 = 'dayTime,agent,gameId,SUM(newUserLogin) AS newUser,'.$days.'';

        $count = count($mod->field('COUNT(*) AS tp_count')->where($map)->group('dayTime,gameId')->select());
        if($export != 1){
            $list = $mod->field($field2)->where($map)->limit($start,$pageSize)->group('dayTime,gameId')->order('dayTime ASC')->select();
        }else{
            $list = $mod->field($field2)->where($map)->group('dayTime,gameId')->order('dayTime ASC')->select();
        }
        
        return array('list'=>$list ? $list : array(),'count'=>$count);
    }

    /**
     * 获取设备留存数据
     * @param array $map 搜索条件
     * @param string $where 汇总搜索条件
     * @return array 返回结果数组
     */
    public function getDeviceRemainData($map,$start=0,$pageSize=30,$where=1)
    {
        //lookType 1：根据游戏查看汇总 2：查看明细
        $lookType = I('lookType',0,'intval');
        $export = I('export',0,'intval');
        if(empty($lookType)) return array('list'=>array(),'count'=>array());

        $mod = M('sp_device_day',C('DB_PREFIX'),'CySlave');
        $days = '';
        $day = '';
        $day_arr = array(1,2,3,4,5,6,7,8,9,13,14,15,29,30,59,89);
        for($i=0;$i<=120;$i++){
            if(in_array($i, $day_arr)){
                $days .= "SUM(day{$i}) AS day{$i},";
                $day .= "day{$i},";
            }
        }
        $days = trim($days,',');
        $day = trim($day,',');

        $field1 = 'dayTime,agent,gameId,newDevice,'.$day.'';
        $field2 = 'dayTime,agent,gameId,SUM(newDevice) AS newDevice,'.$days.'';
        
        $count = count($mod->field('COUNT(*) AS tp_count')->where($map)->group('dayTime,gameId')->select());
            
        if($export != 1){
            $list = $mod->field($field2)->where($map)->limit($start,$pageSize)->group('dayTime,gameId')->order('dayTime ASC')->select();
        }else{
            $list = $mod->field($field2)->where($map)->group('dayTime,gameId')->order('dayTime ASC')->select();
        }
        
        return array('list'=>$list ? $list : array(),'count'=>$count);
    }


    /**
     * 获取活跃用户数据
     * @param array $map 搜索条件
     * @param string $where 汇总搜索条件
     * @return array 返回结果数组
     */
    public function getActUserData($map,$start=0,$pageSize=30,$where=1)
    {
        //lookType 1：根据游戏查看汇总 2：查看明细
        $lookType = I('lookType',0,'intval');
        $export = I('export',0,'intval');
        if(empty($lookType)) return array('list'=>array(),'count'=>array());

        $mod = M('sp_user_game_day',C('DB_PREFIX'),'CySlave');
        
        $field1 = 'dayTime,gameId,serverId,serverName,distinctReg,newUserLogin AS newUser,newUserLogin,oldUserLogin,monthLogin';
        $field2 = 'dayTime,gameId,serverId,serverName,SUM(distinctReg) AS distinctReg,SUM(newUserLogin) AS newUser,SUM(newUserLogin) AS newUserLogin,SUM(oldUserLogin) AS oldUserLogin,SUM(monthLogin) AS monthLogin';
        if($lookType == 1){

            $count = $mod->query("SELECT COUNT(1) AS total FROM (SELECT {$field2} FROM la_sp_user_game_day WHERE {$where} GROUP BY dayTime,gameId ) a");
            $count = $count[0]['total'];

            $list = $mod->query("SELECT {$field2} FROM la_sp_user_game_day WHERE {$where} GROUP BY dayTime,gameId  ORDER BY dayTime ASC ".($export != 1 ? " LIMIT {$start},{$pageSize}" : '')."");

        }elseif($lookType == 2){
            $count = $mod->where($map)->count();
            if($export != 1){
                $list = $mod->field($field1)->where($map)->limit($start,$pageSize)->order('dayTime ASC')->select();
            }else{
                $list = $mod->field($field1)->where($map)->order('dayTime ASC')->select();
            }
            
        }
        
        return array('list'=>$list ? $list : array(),'count'=>$count);
    }

    /**
     * 渠道数据统计
     * @param string $where 汇总搜索条件
     * @return array 返回结果数组
     */
    public function getAgentDataCount($start=0,$pageSize=30,$where=1,$where2=1)
    {
        //lookType 1：根据游戏查看汇总 2：查看明细
        $lookType = I('lookType',0,'intval');
        $export = I('export',0,'intval');
        if(empty($lookType)) return array('list'=>array(),'count'=>array());

        $mod = M('sp_user_game_day a',C('DB_PREFIX'),'CySlave');
        
        $field2 = 'dayTime,agent,gameId,SUM(newUserLogin) AS newUser,SUM(newUserLogin+oldUserLogin) AS allUserLogin,SUM(day1) AS day1,SUM(day6) AS day6,SUM(day13) AS day13,SUM(day29) AS day29';

        $field3 = 'a.dayTime,a.agent,a.gameId,a.newUser,a.allUserLogin,a.day1,a.day6,a.day13,a.day29,IFNULL(SUM(b.allPay),0) AS allPay,IFNULL(SUM(b.allPayUser),0) AS allPayUser,IFNULL(c.newDevice,0) AS newDevice';

        $field4 = 'a.dayTime,a.agent,a.gameId,SUM(a.newUser) AS newUser,SUM(a.allUserLogin) AS allUserLogin,SUM(a.day1) AS day1,SUM(a.day6) AS day6,SUM(a.day13) AS day13,SUM(a.day29) AS day29,IFNULL(SUM(b.allPay),0) AS allPay,IFNULL(SUM(b.allPayUser),0) AS allPayUser,IFNULL(SUM(c.newDevice),0) AS newDevice';

        if($lookType == 1){

            $count = $mod->query("SELECT COUNT(1) AS total,SUM(newUser) AS newUser,SUM(allUserLogin) AS allUserLogin,SUM(allPay) AS allPay FROM (SELECT SUM(newUser) AS newUser,SUM(allUserLogin) AS allUserLogin,SUM(allPay) AS allPay FROM (SELECT a.agent,newUser,allUserLogin,SUM(allPay) AS allPay FROM (SELECT dayTime,agent,gameId,SUM(newUserLogin) AS newUser,SUM(newUserLogin+oldUserLogin) AS allUserLogin FROM la_sp_user_game_day WHERE {$where} GROUP BY dayTime,agent) a LEFT JOIN la_sp_agent_server_pay_day b ON a.dayTime = b.dayTime AND a.agent = b.agent GROUP BY a.agent,a.dayTime) a GROUP BY agent) a ");
            $count = $count[0];

            $list = $mod->query("SELECT {$field4} FROM (SELECT {$field2} FROM la_sp_user_game_day WHERE {$where} GROUP BY dayTime,agent) a LEFT JOIN (SELECT agent,dayTime , SUM(allPay) AS allPay,SUM(allPayUser) AS allPayUser FROM la_sp_agent_server_pay_day a WHERE {$where2} GROUP BY agent,dayTime) b ON a.dayTime = b.dayTime AND a.agent = b.agent LEFT JOIN la_sp_device_day c ON a.dayTime = c.dayTime AND a.agent = c.agent GROUP BY agent ORDER BY allPay DESC ".($export != 1 ? " LIMIT {$start},{$pageSize}" : '')."");
        }elseif($lookType == 2){
            $count = $mod->query("SELECT COUNT(1) AS total,SUM(newUser) AS newUser,SUM(allUserLogin) AS allUserLogin,SUM(allPay) AS allPay FROM (SELECT 1 AS total,newUser,allUserLogin,allPay FROM (SELECT dayTime,agent,gameId,SUM(newUserLogin) AS newUser,SUM(newUserLogin+oldUserLogin) AS allUserLogin FROM la_sp_user_game_day WHERE {$where} GROUP BY dayTime,agent) a LEFT JOIN la_sp_agent_server_pay_day b ON a.dayTime = b.dayTime AND a.agent = b.agent GROUP BY a.dayTime,a.agent) a");
            $count = $count[0];

            $list = $mod->query("SELECT {$field3} FROM (SELECT {$field2} FROM la_sp_user_game_day WHERE {$where} GROUP BY dayTime,agent) a LEFT JOIN la_sp_agent_server_pay_day b ON a.dayTime = b.dayTime AND a.agent = b.agent LEFT JOIN la_sp_device_day c ON a.dayTime = c.dayTime AND a.agent = c.agent GROUP BY dayTime,agent ORDER BY allPay DESC ".($export != 1 ? " LIMIT {$start},{$pageSize}" : '')."");
        }

        return array('list'=>$list ? $list : array(),'count'=>$count);
    }

    /**
     * 投放数据概况统计
     * @param string $map   汇总搜索条件
     * @param string $where 汇总搜索条件
     * @return array 返回结果数组
     */
    public function getAdvData($map=array(),$start=0,$pageSize=30,$where=1,$map2=array())
    {
        $export = I('export',0,'intval');
        $data = I();
        
        $mod = M('',C('DB_PREFIX'),'CySlave');

        $field4 = 'a.dayTime, a.agent, a.game_id,SUM(a.allPayUser) AS allPayUser, SUM(a.newDevice) AS newDevice, SUM(a.newUser) AS newUser, SUM(a.oldUserLogin) AS oldUserLogin, SUM(a.DAU) AS DAU, SUM(c.day1) AS day1, SUM(a.allPay) AS allPay,SUM(a.newPay) AS newPay,SUM(a.newPayUser) AS newPayUser,SUM(a.totalPay) AS totalPay, a.advteruser_id, IFNULL(SUM(b.cost),0) AS cost';

        //先查游戏的数据
        $list = $mod->query("SELECT {$field4} FROM la_sp_advter_cost a LEFT JOIN (SELECT SUM(day1) AS day1,agent,gameId,dayTime FROM `la_sp_user_game_day` FORCE INDEX(dayTime_agent) WHERE dayTime>='{$data['startDate']}' AND dayTime<'".date('Y-m-d',strtotime($data['endDate'].'+1 day'))."' GROUP BY agent,dayTime) c ON a.agent=c.agent AND a.dayTime=c.dayTime AND a.game_id=c.gameId LEFT JOIN (SELECT agent,SUM(cost) AS cost,costMonth FROM la_advter_cost WHERE costMonth>='{$data['startDate']}' AND costMonth<'".date('Y-m-d',strtotime($data['endDate'].'+1 day'))."' GROUP BY agent,costMonth) b ON a.agent=b.agent AND a.dayTime=b.costMonth WHERE {$where} GROUP BY a.dayTime,a.game_id".($export != 1 ? " LIMIT {$start},{$pageSize}" : '')."");

        //计算各游戏的充值金额
        $map['orderType']   = 0;
        $map['orderStatus'] = 0;
        $map['userName']    = array('not in',array('Lgame41400432','Love100200','Lgame49382694'));
        $order = M('order',C('DB_PREFIX_API'),'CySlave')->where($map)->field('SUM(amount) AS amount,CONCAT(FROM_UNIXTIME(regTime,"%Y-%m-%d"),"_",game_id) AS _key,FROM_UNIXTIME(regTime,"%Y-%m-%d") AS regDate,game_id')->group('_key')->select();

        $orderArr = field_to_key($order,'_key');

        $newList = array();
        foreach ($list as $k => $val) {
            $newList[$k] = $val;

            if(array_key_exists($val['dayTime'].'_'.$val['game_id'], $orderArr)){
                $newList[$k]['allPayNow'] += $orderArr[$val['dayTime'].'_'.$val['game_id']]['amount'];
            }else{
                $newList[$k]['allPayNow'] += 0;
            }
        }

        //后查游戏对应渠道的数据
        $agentList = $mod->query("SELECT {$field4} FROM la_sp_advter_cost a LEFT JOIN (SELECT SUM(day1) AS day1,agent,gameId,dayTime FROM `la_sp_user_game_day` FORCE INDEX(dayTime_agent) WHERE dayTime>='{$data['startDate']}' AND dayTime<'".date('Y-m-d',strtotime($data['endDate'].'+1 day'))."' GROUP BY agent,dayTime) c ON a.agent=c.agent AND a.dayTime=c.dayTime AND a.game_id=c.gameId LEFT JOIN (SELECT agent,SUM(cost) AS cost,costMonth FROM la_advter_cost WHERE costMonth>='{$data['startDate']}' AND costMonth<'".date('Y-m-d',strtotime($data['endDate'].'+1 day'))."' GROUP BY agent,costMonth) b ON a.agent=b.agent AND a.dayTime=b.costMonth WHERE {$where} GROUP BY a.dayTime,a.agent ORDER BY newUser DESC");
        //计算各游戏的充值金额
        $order = M('order',C('DB_PREFIX_API'),'CySlave')->where($map)->field('SUM(amount) AS amount,CONCAT(FROM_UNIXTIME(regTime,"%Y-%m-%d"),"_",regAgent) AS _key,FROM_UNIXTIME(regTime,"%Y-%m-%d") AS regDate,regAgent')->group('_key')->select();
        $orderArr = field_to_key($order,'_key');

        $newAgentList = array();
        foreach ($agentList as $k => $val) {
            $newAgentList[$k] = $val;

            if(array_key_exists($val['dayTime'].'_'.$val['agent'], $orderArr)){
                $newAgentList[$k]['allPayNow'] += $orderArr[$val['dayTime'].'_'.$val['agent']]['amount'];
            }else{
                $newAgentList[$k]['allPayNow'] += 0;
            }
        }
        //匹配数据
        foreach ($newList as $key => $value) {
            foreach ($newAgentList as $k => $v) {

                if($value['game_id'] == $v['game_id'] && $value['dayTime'] == $v['dayTime']){
                    //放进父级里
                    $newList[$key]['children'][] = $v;
                }
            }
        }

        //查出条件内的汇总充值人数、DAU、老用户活跃
        $allPayUser = M('order',C('DB_PREFIX_API'),'CySlave')->where($map)->count('distinct userCode');
        $DAU     = M('role_login','nl_','CySlave')->alias('a FORCE INDEX(game_id)')->join('LEFT JOIN lg_agent b ON a.agent=b.agent')->field("COUNT(DISTINCT userCode) AS DAU,COUNT(DISTINCT CASE WHEN regTime<".strtotime($data['startDate'])." THEN userCode END) AS oldUserLogin")->where($map2)->select();
        $count = array('allPayUser'=>$allPayUser,'login'=>$DAU[0]);
        unset($list,$newAgentList);
        return array('list'=>$newList ? $newList : array(),'count'=>$count);
    }

    /**
     * 获取合同数据
     * @param $map
     * @return array
     */
    public function getContractData($map)
    {
        $mod = M("contract",C('DB_PREFIX'),'CySlave');

        //父类条件
        isset($map["status"]) && $search["status"] = $map["status"];
        isset($map["string"]) && $search["_string"] = $map["string"];
        isset($map["childNo"]) && $search["childNo"] = $map["childNo"];
        isset($map["partment"]) && $search["partment"] = $map["partment"];
        isset($map["contractNo"]) && $search["contractNo"] = $map["contractNo"];
        isset($map["followAdmin"]) && $search["followAdmin"] = $map["followAdmin"];
        isset($map["principalId"]) && $search["principalId"] = $map["principalId"];
        isset($map["game"]) && $search["game"] = array("LIKE", "%".$map["game"]."%");
        isset($map["contract"]) && $search["contract"] = array("LIKE", "%".$map["contract"]."%");

        //先搜索一下满足条件需要全部显示的父类ID
        $search["parentId"] = 0;
        $p_info = $mod->field("id")->where($search)->select();
        $pid    = array();
        foreach ($p_info as $v) {
            $pid[] = $v["id"];
        }

        //子类条件
        $map_str = "";
        isset($map["string"]) && $map_str .= " AND ".$map["string"];
        isset($map["status"]) && $map_str .= " AND status = ".$map["status"];
        isset($map["partment"]) && $map_str .= " AND partment = ".$map["partment"];
        isset($map["childNo"]) && $map_str .= " AND childNo = '".$map["childNo"]."'";
        isset($map["followAdmin"]) && $map_str .= " AND followAdmin = ".$map["followAdmin"];
        isset($map["principalId"]) && $map_str .= " AND principalId = ".$map["principalId"];
        isset($map["contractNo"]) && $map_str .= " AND contractNo = '".$map["contractNo"]."'";
        isset($map["game"]) && $map_str .= " AND game LIKE '%".$map["game"]."%'";
        isset($map["contract"]) && $map_str .= " AND contract LIKE '%".$map["contract"]."%'";

        //搜索条件判断
        if ($map_str) {
            if ($pid) {
                $map_str = "(parentId IN (".implode(",", $pid).")) OR (parentId != 0 ".$map_str.")";
            } else {
                $map_str = "parentId != 0 ".$map_str;
            }
        } elseif ($pid) {
            $map_str = "parentId IN (".implode(",", $pid).")";
        } else {
            return array();
        }

        $son_data   = $mod->field("*")->where($map_str)->order("contractNo DESC,-substring_index(childNo, '-', -1) DESC,id DESC")->select();
        $son        = array();
        $parentId   = array();
        foreach ($son_data as $val) {
            $son[$val["parentId"]][] = $val;
            $parentId[] = $val["parentId"];
        }

        if ($parentId) {
            $data_map = array(
                "_complex"  => $search,
                "id"        => array("IN", $parentId),
                "_logic"    => "OR"
            );
        } else {
            $data_map = $search;
        }

        //重新获取父类信息
        $data   = $mod->where($data_map)->order("contractNo DESC,childNo ASC,id DESC")->select();

        foreach ($data as $key => $val) {
            $data[$key]["children"] = $son[$val["id"]]? $son[$val["id"]]: array(0 => array());
        }

        return $data;
    }

    /**
     * 获取用户注册留存、充值数据
     * @param array $map 搜索条件
     * @param string $where 汇总搜索条件
     * @param string $where2 汇总搜索条件
     * @return array 返回结果数组
     */
    public function getRegChargeData($map,$start=0,$pageSize=30,$where=1,$where2=1)
    {
        //lookType 1：根据游戏查看汇总 2：查看明细
        $lookType = I('lookType',0,'intval');
        $export = I('export',0,'intval');
        if(empty($lookType)) return array('list'=>array(),'count'=>array());

        $mod = M('sp_user_game_day a',C('DB_PREFIX'),'CySlave');
        $days = '';
        $day = '';
        $day_arr = array(1,6,13,29);
        for($i=0;$i<=120;$i++){
            if(in_array($i, $day_arr)){
                $days .= "SUM(a.day{$i}) AS day{$i},";
                $day .= "day{$i},";
            }
        }
        $days = trim($days,',');
        $day = trim($day,',');

        if($lookType == 1){
            $field1 = 'a.dayTime,a.agent,a.gameId,a.serverId,a.serverName,b.newDevice AS newDevice,IFNULL(b.disUdid,0) AS disUdid,a.distinctReg AS distinctReg,a.newUser AS newUser,a.newUserLogin AS newUserLogin,a.oldUserLogin AS oldUserLogin,a.monthLogin AS monthLogin,'.$day.'';
            $field2 = 'a.dayTime,a.agent,a.gameId,a.serverId,a.serverName,SUM(a.distinctReg) AS distinctReg,SUM(a.newUserLogin) AS newUser,SUM(a.newUserLogin) AS newUserLogin,SUM(a.oldUserLogin) AS oldUserLogin,SUM(a.monthLogin) AS monthLogin,'.$days.'';
            $count = $mod->query("SELECT COUNT(1) AS total FROM (SELECT {$field2} FROM la_sp_user_game_day a  WHERE {$where} GROUP BY a.dayTime,a.gameId ) a LEFT JOIN (SELECT dayTime,agent,gameId,SUM(disUdid) AS disUdid,SUM(IFNULL(newDevice,0)) AS newDevice FROM la_sp_device_day a WHERE {$where2} GROUP BY dayTime,gameId) b ON a.dayTime=b.dayTime AND a.gameId = b.gameId");
            $count = $count[0]['total'];
            
            $list = $mod->query("SELECT {$field1} FROM (SELECT {$field2} FROM la_sp_user_game_day a WHERE {$where} GROUP BY a.dayTime,a.gameId ".($export != 1 ? " LIMIT {$start},{$pageSize}" : '').") a LEFT JOIN (SELECT dayTime,agent,gameId,SUM(disUdid) AS disUdid,SUM(IFNULL(newDevice,0)) AS newDevice FROM la_sp_device_day a WHERE {$where2} GROUP BY dayTime,gameId) b ON a.dayTime=b.dayTime AND a.gameId = b.gameId ORDER BY dayTime ASC");;

        }elseif($lookType == 2){
            $field = 'a.dayTime,a.agent,a.gameId,a.serverId,a.serverName,a.distinctReg,a.newUser,a.newUserLogin,a.oldUserLogin,a.monthLogin,a.day1,a.day6,a.day13,a.day29,IFNULL(b.allPay,0) AS allPay,IFNULL(b.allPayUser,0) AS allPayUser,IFNULL(b.newPay,0) AS newPay,IFNULL(b.newPayUser,0) AS newPayUser';
            $count = $mod->where($map)->count();
            if($export != 1){
                $list = $mod->field($field)->join('left join la_sp_agent_server_pay_day b on a.dayTime = b.dayTime and a.agent = b.agent and a.serverId = b.serverId')->where($map)->limit($start,$pageSize)->order('dayTime ASC')->select();
            }else{
                $list = $mod->field($field)->join('left join la_sp_agent_server_pay_day b on a.dayTime = b.dayTime and a.agent = b.agent and a.serverId = b.serverId')->where($map)->order('dayTime ASC')->select();
            }
            
        }
        
        return array('list'=>$list ? $list : array(),'count'=>$count);
    }

    /**
     * 获取用户充值LTV数据
     * @param array $map 搜索条件
     * @param string $where 汇总搜索条件
     * @return array 返回结果数组
     */
    public function getPayLtvData($map,$start=0,$pageSize=30,$where=1)
    {
        //lookType 1：根据游戏查看汇总 2：查看明细
        $lookType = I('lookType',0,'intval');
        $export = I('export',0,'intval');
        if(empty($lookType)) return array('list'=>array(),'count'=>array());

        $mod = M('sp_pay_day',C('DB_PREFIX'),'CySlave');
        $days = '';
        $users = '';
//        $day_arr = array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,29,59,89);
        $day_arr = array();//定义数组
        for($i = 0; $i<90; $i++){
            $day_arr[] = $i;
        }

        for($i=0;$i<=120;$i++){
            if(in_array($i, $day_arr)){
                $days .= "SUM(SUBSTRING_INDEX(day{$i},',',1)) AS day{$i},";
                $users .= "SUM(SUBSTRING_INDEX(day{$i},',',-1)) AS user{$i},";
            }
        }
        $days = trim($days,',');
        $users = trim($users,',');
        $field = 'dayTime,agent,gameId,serverId,IFNULL(SUM(newUser),0) AS newUser,'.$days.','.$users;

        $count = $mod->query("SELECT COUNT(1) AS total FROM (SELECT * FROM la_sp_pay_day WHERE {$where} GROUP BY dayTime,gameId) a")[0]['total'];
        $list = $mod->query("SELECT {$field} FROM la_sp_pay_day WHERE {$where} GROUP BY dayTime,gameId ORDER BY dayTime ASC ".($export != 1 ? " LIMIT {$start},{$pageSize}" : ''));
        
        return array('list'=>$list ? $list : array(),'count'=>$count);
    }

    /**
     * 获取渠道充值汇总数据
     * @param array $map 搜索条件
     * @param string $where 汇总搜索条件
     * @return array 返回结果数组
     */
    public function getAgentPayData($map,$start=0,$pageSize=30,$where=1)
    {
        //lookType 1：根据游戏查看汇总 2：查看明细
        $lookType = I('lookType',0,'intval');
        $export = I('export',0,'intval');
        if(empty($lookType)) return array('list'=>array(),'count'=>array());
        $mod = M('sp_agent_server_pay_day',C('DB_PREFIX'),'CySlave');
        if($lookType == 1){
            $field = 'game_id,agent,SUM(allPay) AS totalPay';
            $count = $mod->query("SELECT COUNT(1) AS total,SUM(allPay) AS totalMoney FROM (SELECT SUM(allPay) AS allPay FROM la_sp_agent_server_pay_day WHERE {$where} GROUP BY agent) a");

            $list = $mod->query("SELECT {$field} FROM la_sp_agent_server_pay_day WHERE {$where} GROUP BY agent ORDER BY totalPay DESC ".($export != 1 ? " LIMIT {$start},{$pageSize}" : ''));
        }elseif($lookType == 2){
            $field = 'game_id,agent,allPay AS totalPay';
            $count = $mod->field('COUNT(1) AS total,SUM(allPay) AS totalMoney')->where($map)->select();
            if($export == 1){
                $list = $mod->field($field)->where($map)->order('totalPay DESC')->select();
            }else{
                $list = $mod->field($field)->where($map)->limit($start,$pageSize)->order('totalPay DESC')->select();
            }
        }
        return array('list'=>$list ? $list : array(),'count'=>$count[0]);
    }

    /**
     * 获取充值等级汇总数据
     * @param array $map 搜索条件
     * @param string $where 汇总搜索条件
     * @return array 返回结果数组
     */
    public function getPayLevelData($map,$start=0,$pageSize=30,$where=1)
    {
        //lookType 1：根据游戏查看汇总 2：查看明细
        $lookType = I('lookType',0,'intval');
        $export   = I('export',0,'intval');
        $chart    = I('chart',0,'intval');
        if(empty($lookType)) return array('list'=>array(),'count'=>array());
        $mod = M('sp_agent_server_level_pay_day',C('DB_PREFIX'),'CySlave');
        if($lookType == 1){
            $field = 'game_id,level,SUM(amount) AS totalPay,SUM(user) AS totalUser';
            $count = $mod->query("SELECT COUNT(1) AS total,SUM(allPay) AS totalMoney,SUM(user) AS totalUser FROM (SELECT SUM(amount) AS allPay,SUM(user) AS user FROM la_sp_agent_server_level_pay_day WHERE {$where} GROUP BY game_id,level) a");

            $list = $mod->query("SELECT {$field} FROM la_sp_agent_server_level_pay_day WHERE {$where} GROUP BY game_id,level ORDER BY level ASC ".(($export != 1 && $chart != 1) ? " LIMIT {$start},{$pageSize}" : ''));
        }elseif($lookType == 2){
            $field = 'dayTime,agent,game_id,serverId,level,amount AS totalPay,user AS totalUser';
            $count = $mod->field('COUNT(1) AS total,SUM(amount) AS totalMoney,SUM(user) AS totalUser')->where($map)->select();
            if($export != 1){
                $list = $mod->field($field)->where($map)->limit($start,$pageSize)->order('totalPay ASC')->select();
            }else{
                $list = $mod->field($field)->where($map)->order('totalPay ASC')->select();
            }
        }
        return array('list'=>$list ? $list : array(),'count'=>$count[0]);
    }

    /**
     * 获取活跃玩家等级汇总数据
     * @param string $where 汇总搜索条件
     * @return array 返回结果数组
     */
    public function getActPlayerData($start=0,$pageSize=30,$where=1)
    {
        //lookType 1：根据游戏查看汇总 2：查看明细
        $lookType = I('lookType',0,'intval');
        $export   = I('export',0,'intval');
        $chart    = I('chart',0,'intval');
        if(empty($lookType)) return array('list'=>array(),'count'=>array());
        $mod = M('sp_act_player_level_day',C('DB_PREFIX'),'CySlave');
        if($lookType == 1){
            $field = 'COUNT(DISTINCT userCode) AS totalUser,CAST(`level`  AS SIGNED) AS `level`,game_id';
            $count = $mod->query("SELECT COUNT(1) AS total,SUM(totalUser) AS totalUser FROM (SELECT {$field} FROM `lg_role` WHERE {$where} GROUP BY LEVEL,game_id) a");

            $list = $mod->query("SELECT {$field} FROM `lg_role` WHERE {$where} GROUP BY `level`,game_id ORDER BY `level` ".(($export != 1 && $chart != 1) ? " LIMIT {$start},{$pageSize}" : ''));
        }
        return array('list'=>$list ? $list : array(),'count'=>$count[0]);
    }

    /**
     * 获取充值档位汇总数据
     * @param array $map 搜索条件
     * @param string $where 汇总搜索条件
     * @return array 返回结果数组
     */
    public function getPayGearData($map,$start=0,$pageSize=30,$where=1)
    {
        //lookType 1：根据游戏查看汇总 2：查看明细
        $lookType = I('lookType',0,'intval');
        $export   = I('export',0,'intval');
        $chart    = I('chart',0,'intval');
        if(empty($lookType)) return array('list'=>array(),'count'=>array());
        $mod = M('sp_agent_server_goods_pay_day',C('DB_PREFIX'),'CySlave');
        if($lookType == 1){
            $field = 'game_id,`subject` AS goods,SUM(amount) AS totalPay';
            $count = $mod->query("SELECT COUNT(1) AS total,SUM(allPay) AS totalMoney FROM (SELECT SUM(amount) AS allPay FROM la_sp_agent_server_goods_pay_day WHERE {$where} GROUP BY game_id,subject) a");

            $list = $mod->query("SELECT {$field} FROM la_sp_agent_server_goods_pay_day WHERE {$where} GROUP BY game_id,subject ORDER BY totalPay DESC ".(($export != 1 && $chart != 1) ? " LIMIT {$start},{$pageSize}" : ''));
        }elseif($lookType == 2){
            $field = 'dayTime,agent,game_id,serverId,`subject` AS goods,amount AS totalPay';
            $count = $mod->field('COUNT(1) AS total,SUM(amount) AS totalMoney')->where($map)->select();
            if($export != 1){
                $list = $mod->field($field)->where($map)->limit($start,$pageSize)->order('totalPay DESC')->select();
            }else{
                $list = $mod->field($field)->where($map)->order('totalPay DESC')->select();
            }
        }
        return array('list'=>$list ? $list : array(),'count'=>$count[0]);
    }

    /**
     * 获取充值排行汇总数据
     * @param string $where 搜索条件
     * @return array 返回结果数组
     */
    public function getPayRangeData($start=0,$pageSize=30,$where)
    {
        $export   = I('export',0,'intval');
        $mod = M('sp_agent_server_user_pay_day',C('DB_PREFIX'),'CySlave');
        $field = 'a.game_id,a.userCode,a.userName,a.agent,b.city,b.province,SUM(a.amount) AS totalPay,SUM(a.balance) AS totalBalance,FROM_UNIXTIME(b.createTime) AS createTime,b.lastAgent,b.lastPayRoleName,b.lastPayServerName,FROM_UNIXTIME(b.lastPay) AS lastPay,b.lastLogin AS lastLogin';
        $count = $mod->query("SELECT COUNT(1) AS total,SUM(amount) as totalPay FROM (SELECT SUM(a.amount) AS amount FROM la_sp_agent_server_user_pay_day a LEFT JOIN lg_user_game b ON a.userCode=b.userCode AND a.game_id=b.game_id WHERE {$where} GROUP BY a.userCode,a.game_id) a");
        $list = $mod->query("SELECT {$field} FROM la_sp_agent_server_user_pay_day a LEFT JOIN lg_user_game b ON a.userCode=b.userCode AND a.game_id=b.game_id WHERE {$where} GROUP BY a.userCode,a.game_id ORDER BY totalPay DESC ".($export != 1 ? " LIMIT {$start},{$pageSize}" : ''));
        return array('list'=>$list ? $list : array(),'count'=>$count[0]);
    }

    /**
     * 获取充值地区分布数据
     * @param string $where 搜索条件
     * @return array 返回结果数组
     */
    public function getAreaPayData($start=0,$pageSize=30,$where)
    {
        $export     = I('export',0,'intval');
        $lookType   = I('lookType',0,'intval');
        $mod   = M('sp_area_pay',C('DB_PREFIX'),'CySlave');
        $field = 'dayTime,game_id,agent,serverId,serverName,SUM(amount) AS amount,province,city';

        $provincePay = $cityPay = 0;
        if($lookType == 1){
            $province_list = $mod->query("SELECT {$field} FROM la_sp_area_pay WHERE {$where} GROUP BY province ORDER BY amount DESC");
            $city_list     = $mod->query("SELECT {$field} FROM la_sp_area_pay WHERE {$where} GROUP BY province,city ORDER BY amount DESC");
            //计算省份总额
            $provincePay   = $mod->query("SELECT SUM(amount) AS amount FROM(SELECT SUM(amount) AS amount FROM la_sp_area_pay WHERE {$where} GROUP BY province) a")[0]['amount'];

            //计算城市总额
            $cityPay       = $mod->query("SELECT SUM(amount) AS amount FROM(SELECT SUM(amount) AS amount FROM la_sp_area_pay WHERE {$where} GROUP BY province,city) a")[0]['amount'];
        }elseif($lookType == 2){
            $province_list = $mod->query("SELECT {$field} FROM la_sp_area_pay WHERE {$where} GROUP BY dayTime,province ORDER BY amount DESC");
            $city_list     = $mod->query("SELECT {$field} FROM la_sp_area_pay WHERE {$where} GROUP BY dayTime,province,city ORDER BY amount DESC");

            //计算省份总额
            $provincePay   = $mod->query("SELECT SUM(amount) AS amount FROM(SELECT {$field} FROM la_sp_area_pay WHERE {$where} GROUP BY dayTime,province) a")[0]['amount'];

            //计算城市总额
            $cityPay       = $mod->query("SELECT SUM(amount) AS amount FROM(SELECT {$field} FROM la_sp_area_pay WHERE {$where} GROUP BY dayTime,province,city) a")[0]['amount'];
        }

        //匹配数据
        foreach ($province_list as $key => $value) {
            foreach ($city_list as $k => $v) {
                if($lookType == 2){
                    if($value['province'] == $v['province'] && $value['dayTime'] == $v['dayTime']){
                        //放进父级里
                        $province_list[$key]['children'][] = $v;
                    }
                }else{
                    if($value['province'] == $v['province']){
                        //放进父级里
                        $province_list[$key]['children'][] = $v;
                    }
                }
                
            }
        }
        return array('list'=>$province_list ? $province_list : array(),'count'=>array('provincePay'=>$provincePay,'cityPay'=>$cityPay));
    }

    /**
     * 获取等级流失率数据
     * @param string $where 搜索条件
     * @return array 返回结果数组
     */
    public function getLevelLossData($start=0,$pageSize=30,$where)
    {
        $export     = I('export',0,'intval');
        $loosType   = I('loosType',0,'intval');
        $mod        = M('sp_player_loss',C('DB_PREFIX'),'CySlave');
        $field      = 'level, DATEDIFF("'.date('Y-m-d').'", FROM_UNIXTIME(updateTime, "%Y-%m-%d") ) AS days';
        $field2     = 'IFNULL(level, 0)+0 AS level, COUNT(CASE WHEN days >= 3 THEN 1 END) AS day3, COUNT(CASE WHEN days >= 7 THEN 1 END) AS day7';

        if($loosType == 1){
            $loss_list = $mod->query("SELECT $field2 FROM (SELECT {$field} FROM `lg_role` a FORCE INDEX (uptime) WHERE {$where} AND a.updateTime < ".strtotime(date('Y-m-d',strtotime(date('Y-m-d').' -3 day'))).") a GROUP BY level ORDER BY level ASC ".($export != 1 ? " LIMIT {$start},{$pageSize}" : '')."");
            
                //流失数据汇总
                $count = array(
                    'total' => count($loss_list),
                    'day3'  => array_sum(array_column($loss_list, 'day3')),
                    'day7'  => array_sum(array_column($loss_list, 'day7')),
                );

        }elseif($loosType == 2){
            //付费用户
            $loss_list = $mod->query("SELECT $field2 FROM (SELECT {$field} FROM `lg_role` a FORCE INDEX (uptime) LEFT JOIN `lg_user_game` b ON a.userCode = b.userCode WHERE {$where} AND b.lastPay > 0 AND a.updateTime < ".strtotime(date('Y-m-d',strtotime(date('Y-m-d').' -3 day'))).") a GROUP BY level ORDER BY level ASC ".($export != 1 ? " LIMIT {$start},{$pageSize}" : '')."");
            
                //流失数据汇总
                $count = array(
                    'total' => count($loss_list),
                    'day3'  => array_sum(array_column($loss_list, 'day3')),
                    'day7'  => array_sum(array_column($loss_list, 'day7')),
                );
        }

        return array('list'=>$loss_list ? $loss_list : array(),'count'=>$count);
    }

    /**
     * 获取部门日报数据
     * @param string $where 搜索条件
     * @return array 返回结果数组
     */
    public function getDepartmentDayReportData($start=0,$pageSize=30,$where)
    {

        $export     = I('export',0,'intval');
        $mod        = M('sp_department_day_report',C('DB_PREFIX'),'CySlave');
        $field      = 'dayTime,department,SUM(newDevice) AS newDevice,SUM(newUser) AS newUser,SUM(actUser) AS actUser,SUM(allPay) AS allPay,SUM(allPayUser) AS allPayUser';

        $count = $mod->query("SELECT COUNT(1) AS total,SUM(allPayUser) AS allPayUser,SUM(actUser) AS actUser FROM (SELECT dayTime,SUM(actUser) AS actUser,SUM(allPayUser) AS allPayUser FROM  la_sp_department_day_report WHERE $where GROUP BY dayTime) a");

        $loss_list = $mod->query("SELECT $field FROM  la_sp_department_day_report WHERE $where GROUP BY dayTime ORDER BY dayTime ".($export != 1 ? " LIMIT {$start},{$pageSize}" : '')."");
        
        return array('list'=>$loss_list ? $loss_list : array(),'count'=>$count[0]);
    }

    /**
     * 每个小时的注册数
     * @param $map
     * @return mixed
     */
    public function getHourRegisterCount($map)
    {
        return M("role_login", 'nl_','CySlave')->alias('FORCE INDEX(`regtime`)')->field("COUNT(DISTINCT userCode) AS user,FROM_UNIXTIME(regTime, '%H') as hour,regAgent")->where($map)->group("FROM_UNIXTIME(regTime, '%H'),regAgent")->order('user ASC')->select();
    }

    /**
     * 每个小时的充值金额
     * @param $map
     * @return mixed
     */
    public function getHourPayCount($map)
    {
        return M("order", C('DB_PREFIX_API'),'CySlave')->field("SUM(amount) AS amount,FROM_UNIXTIME(createTime, '%H') as hour,agent")->where($map)->group("FROM_UNIXTIME(createTime, '%H'),agent")->order('amount DESC')->select();
    }

    /**
     * 每小时的注册数IOS
     * @param  [type] $map [description]
     * @return [type]      [description]
     */
    public function getHourRegisterCountIos($map)
    {
        return M("ios_user_game_log", 'lg_','CySlave')->alias(' FORCE INDEX(`create_time`)')->field("COUNT(DISTINCT userCode) AS user,FROM_UNIXTIME(createTime, '%H') as hour,advter_id as regAgent")->where($map)->group("FROM_UNIXTIME(createTime, '%H'),advter_id")->order('user ASC')->select();
    }

    /**
     * 每小时的充值金额IOS
     * @param  [type] $map [description]
     * @return [type]      [description]
     */
    public function getHourPayCountIos($map)
    {
        return M("order", C('DB_PREFIX_API'),'CySlave')->alias(' FORCE INDEX(`createTime`)')->field("SUM(amount) AS amount,FROM_UNIXTIME(createTime, '%H') as hour,advter_id as agent")->where($map)->group("FROM_UNIXTIME(createTime, '%H'),advter_id")->order('amount DESC')->select();
    }

    /**
     * 获取广告成本的汇总
     * @param $map
     * @return mixed
     */
    public function getAdvterSum($map)
    {
        return M("advter_cost",C('DB_PREFIX'),'CySlave')->where($map)->sum('cost');
    }
    

    /**
     * bui列表集合
     * @param string $table 表名
     * @param array $map 搜索条件
     * @param int $start 从第几条记录开始
     * @param int $pageSize 每页显示的条数
     * @param string $prefix 表前缀
     * @param string $_order 排序关键字
     * @param string $_desc asc升序 desc降序
     * @return array 返回结果数组
     */
    public function getBuiList($table,$map=array(),$start=0,$pageSize=30,$prefix='la_',$_order='',$_desc = 'desc')
    {
        $export = I('export',0,'intval');
        $mod = M($table,$prefix,'CySlave');
        empty($_order) && $_order = $mod->getPk();
        $count = $mod->where($map)->count();
        if($export == 1){
            $list = $mod->where($map)->order($_order.' '.$_desc)->select();
        }else{
            $list = $mod->where($map)->limit($start,$pageSize)->order($_order.' '.$_desc)->select();
        }
        return array('list'=>$list,'count'=>$count);
    }
    
    /**
     * * 获取每个小时的数据统计
     * @param $table 数据表
     * @param $info 统计的数据字段
     * @param $map 搜索条件
     * @param $time 时间字段
     * @param $agent 渠道字段
     * @param bool $distinct 是否去重
     * @param string $prefix 数据表前缀
     * @param null $join 连表信息
     * @param bool $sum 是否为求和
     * @return mixed
     */
    public function getHourCount($table, $info, $map, $time, $agent, $distinct = false, $prefix = "lg_", $join = null, $sum = false)
    {
        return M($table, $prefix,'CySlave')->alias("a")->field(($sum? "SUM": "COUNT")."(".($distinct? "DISTINCT ": "").$info.") AS num,FROM_UNIXTIME(".$time.", '%H') as hour,".$agent." AS agent")->join($join)->where($map)->group("FROM_UNIXTIME(".$time.", '%H'),".$agent)->order("num ASC")->select();
    }

    /**
     * 投放数据概况统计
     * @param string $map   汇总搜索条件
     * @param string $where 汇总搜索条件
     * @return array 返回结果数组
     */
    public function getAdvDataBak($map=array(),$start=0,$pageSize=30,$where=1,$map2=array(),$isMonth=0,$game_id = array(),$parentId = false,$startDate,$endDate)
    {
        $export = I('export',0,'intval');
        $isCount = I('isCount',0,'intval');
        
        $mod = M('',C('DB_PREFIX'),'CySlave');

        $field4 = 'a.dayTime, a.agent, a.game_id,SUM(a.allPayUser) AS allPayUser, SUM(a.newDevice) AS newDevice,SUM(a.disUdid) AS disUdid, SUM(a.newUser) AS newUser, SUM(a.oldUserLogin) AS oldUserLogin, SUM(a.DAU) AS DAU, SUM(c.day1) AS day1, SUM(a.allPay) AS allPay,SUM(a.newPay) AS newPay,SUM(a.newPayUser) AS newPayUser,SUM(a.totalPay) AS totalPay, a.advteruser_id, IFNULL(SUM(b.cost),0) AS cost';
        $map['orderType']   = 0;
        $map['orderStatus'] = 0;

        if(!$parentId){
            //先查游戏的数据
            $list = $mod->query("SELECT {$field4} FROM la_sp_advter_cost a LEFT JOIN (SELECT SUM(day1) AS day1,agent,gameId,dayTime FROM `la_sp_user_game_day` FORCE INDEX(`dayTime`) WHERE dayTime>='{$startDate}' AND dayTime<'".date('Y-m-d',strtotime($endDate.'+1 day'))."' ".($game_id ? ' AND gameId IN('.implode(',', $game_id).')' : '')." GROUP BY agent,dayTime) c ON a.agent=c.agent AND a.dayTime=c.dayTime AND a.game_id=c.gameId LEFT JOIN (SELECT agent,SUM(cost) AS cost,costMonth FROM la_advter_cost WHERE costMonth>='{$startDate}' AND costMonth<'".date('Y-m-d',strtotime($endDate.'+1 day'))."' GROUP BY agent,costMonth) b ON a.agent=b.agent AND a.dayTime=b.costMonth WHERE {$where} GROUP BY a.dayTime,a.game_id".($export != 1 ? " LIMIT {$start},{$pageSize}" : '')."");

            //计算各游戏的充值金额

            $order = M('order',C('DB_PREFIX_API'),'CySlave')->alias(' FORCE INDEX(`paymentTime`)')->where($map)->field('SUM(amount) AS amount,CONCAT(FROM_UNIXTIME(regTime,"%Y-%m-%d"),"_",game_id) AS _key,FROM_UNIXTIME(regTime,"%Y-%m-%d") AS regDate,game_id')->group('_key')->select();

            $orderArr = field_to_key($order,'_key');

            $newList = array();
            foreach ($list as $k => $val) {
                $newList[$k] = $val;

                if(array_key_exists($val['dayTime'].'_'.$val['game_id'], $orderArr)){
                    $newList[$k]['allPayNow'] += $orderArr[$val['dayTime'].'_'.$val['game_id']]['amount'];
                }else{
                    $newList[$k]['allPayNow'] += 0;
                }
            }


        }else{
            //后查游戏对应渠道的数据
            $list = $mod->query("SELECT {$field4} FROM la_sp_advter_cost a LEFT JOIN (SELECT SUM(day1) AS day1,agent,gameId,dayTime FROM `la_sp_user_game_day` FORCE INDEX(`dayTime`) WHERE dayTime>='{$startDate}' AND dayTime<'".date('Y-m-d',strtotime($endDate.'+1 day'))."' ".($game_id ? ' AND gameId IN('.implode(',', $game_id).')' : '')." GROUP BY agent,dayTime) c ON a.agent=c.agent AND a.dayTime=c.dayTime AND a.game_id=c.gameId LEFT JOIN (SELECT agent,SUM(cost) AS cost,costMonth FROM la_advter_cost WHERE costMonth>='{$startDate}' AND costMonth<'".date('Y-m-d',strtotime($endDate.'+1 day'))."' GROUP BY agent,costMonth) b ON a.agent=b.agent AND a.dayTime=b.costMonth WHERE {$where} GROUP BY a.dayTime,a.agent");
            //计算各游戏的充值金额
            $order = M('order',C('DB_PREFIX_API'),'CySlave')->alias(' FORCE INDEX(`paymentTime`)')->where($map)->field('SUM(amount) AS amount,CONCAT(FROM_UNIXTIME(regTime,"%Y-%m-%d"),"_",agent) AS _key,FROM_UNIXTIME(regTime,"%Y-%m-%d") AS regDate,agent')->group('_key')->select();
            $orderArr = field_to_key($order,'_key');


            $newList = array();
            foreach ($list as $k => $val) {
                $newList[$k] = $val;

                if(array_key_exists($val['dayTime'].'_'.$val['agent'], $orderArr)){
                    $newList[$k]['allPayNow'] += $orderArr[$val['dayTime'].'_'.$val['agent']]['amount'];
                }else{
                    $newList[$k]['allPayNow'] += 0;
                }
            }

        }
        //查出条件内的汇总充值人数、DAU、老用户活跃
        $count = array();
        if($isCount == 1){
            $allPayUser = M('order',C('DB_PREFIX_API'),'CySlave')->alias(' FORCE INDEX(`paymentTime`)')->where($map)->count('distinct userCode');
            if ($isMonth) {
                $DAU = M('sp_month_dau','la_','CySlave')->alias('a')->field("SUM(DAU) AS DAU,SUM(oldUserLogin) AS oldUserLogin")->where($map2)->select();
                if(empty($DAU)){
                    //当月没有数据则为0
                    $DAU[0] = array("DAU" => 0, "oldUserLogin" => 0);
                }
            } else {
                $DAU    = M('sp_user_dau','la_','CySlave')->alias(' FORCE INDEX(`dayTime`) ')->field("COUNT(DISTINCT userCode) AS DAU,COUNT(DISTINCT CASE WHEN regTime<".strtotime($startDate)." THEN userCode END) AS oldUserLogin")->where($map2)->select();
            }
            $count = array('allPayUser'=>$allPayUser,'login'=>$DAU[0]);
        }
        
        return array('list'=>$newList ? $newList : array(),'count'=>$count);
    }

    /**
     * 投放数据概况IOS统计
     * @param string $map   汇总搜索条件
     * @param string $where 汇总搜索条件
     * @return array 返回结果数组
     */
    public function getAdvDataIosBak($map=array(),$start=0,$pageSize=30,$where=1,$map2=array(),$isMonth=0,$parentId = false,$startDate,$endDate)
    {
        $export = I('export',0,'intval');
        $isCount = I('isCount',0,'intval');

        $mod = M('',C('DB_PREFIX'),'CySlave');

        $field4 = 'a.dayTime, a.agent, a.gameId AS game_id, a.advterId ,SUM(a.allPayUser) AS allPayUser,SUM(a.soleUdids) AS soleUdids, SUM(a.newDevice) AS newDevice, SUM(a.disUdid) AS disUdid,SUM(a.newUser) AS newUser, SUM(a.oldUserLogin) AS oldUserLogin, SUM(a.DAU) AS DAU, SUM(c.day1) AS day1, SUM(a.allPay) AS allPay,SUM(a.newPay) AS newPay,SUM(a.newPayUser) AS newPayUser,SUM(a.totalPay) AS totalPay, a.advterUserId as advteruser_id, IFNULL(SUM(b.cost),0) AS cost';
        $map['orderType']   = 0;
        $map['orderStatus'] = 0;
        if(!$parentId){
        //先查游戏的数据
        $list = $mod->query("SELECT {$field4} FROM la_sp_ios_advter_cost a LEFT JOIN (SELECT SUM(day1) AS day1,advterId,dayTime FROM `la_sp_ios_user_game_day`  WHERE dayTime>='{$startDate}' AND dayTime<'".date('Y-m-d',strtotime($endDate.'+1 day'))."' GROUP BY advterId,dayTime) c ON a.advterId=c.advterId AND a.dayTime=c.dayTime LEFT JOIN (SELECT agent,SUM(cost) AS cost,costMonth,advter_id FROM la_advter_cost WHERE gameType='ios' AND costMonth>='{$startDate}' AND costMonth<'".date('Y-m-d',strtotime($endDate.'+1 day'))."' GROUP BY advter_id,costMonth) b ON a.dayTime=b.costMonth AND b.advter_id = a.advterId WHERE {$where} GROUP BY a.dayTime,a.agent".($export != 1 ? " LIMIT {$start},{$pageSize}" : '')."");

        //计算各游戏的充值金额
        $order = M('order',C('DB_PREFIX_API'),'CySlave')->alias(' FORCE INDEX(`paymentTime`)')->where($map)->field('SUM(amount) AS amount,CONCAT(FROM_UNIXTIME(regTime,"%Y-%m-%d"),"_",agent) AS _key,FROM_UNIXTIME(regTime,"%Y-%m-%d") AS regDate,agent')->group('_key')->select();
        $orderArr = field_to_key($order,'_key');

        $newList = array();
        foreach ($list as $k => $val) {
            $newList[$k] = $val;
            if(array_key_exists($val['dayTime'].'_'.$val['agent'], $orderArr)){
                $newList[$k]['allPayNow'] += $orderArr[$val['dayTime'].'_'.$val['agent']]['amount'];
            }else{
                $newList[$k]['allPayNow'] += 0;
            }
        }
    }else{
        //后查游戏对应广告位的数据
        $list = $mod->query("SELECT {$field4} FROM la_sp_ios_advter_cost a LEFT JOIN (SELECT SUM(day1) AS day1,advterId,dayTime FROM `la_sp_ios_user_game_day`  WHERE dayTime>='{$startDate}' AND dayTime<'".date('Y-m-d',strtotime($endDate.'+1 day'))."' GROUP BY advterId,dayTime) c ON a.advterId=c.advterId AND a.dayTime=c.dayTime LEFT JOIN (SELECT agent,SUM(cost) AS cost,costMonth,advter_id FROM la_advter_cost WHERE  gameType='ios' AND costMonth>='{$startDate}' AND costMonth<'".date('Y-m-d',strtotime($endDate.'+1 day'))."' GROUP BY advter_id,costMonth) b ON a.dayTime=b.costMonth  AND b.advter_id = a.advterId WHERE {$where} GROUP BY a.dayTime,a.advterId ORDER BY newUser DESC");
        //计算各推广活动的充值金额
        $order = M('order',C('DB_PREFIX_API'),'CySlave')->alias(' FORCE INDEX(`paymentTime`)')->where($map)->field('SUM(amount) AS amount,CONCAT(FROM_UNIXTIME(regTime,"%Y-%m-%d"),"_",advter_id) AS _key,FROM_UNIXTIME(regTime,"%Y-%m-%d") AS regDate,advter_id')->group('_key')->select();

        $orderArr = field_to_key($order,'_key');
        $newList = array();
        foreach ($list as $k => $val) {
            $newList[$k] = $val;
            if(array_key_exists($val['dayTime'].'_'.$val['advterId'], $orderArr)){
                $newList[$k]['allPayNow'] += $orderArr[$val['dayTime'].'_'.$val['advterId']]['amount'];
            }else{
                $newList[$k]['allPayNow'] += 0;
            }
        }
    }
    //查出条件内的汇总充值人数、DAU、老用户活跃
    $count = array();
    if($isCount == 1){
        $allPayUser = M('order',C('DB_PREFIX_API'),'CySlave')->alias(' FORCE INDEX(`paymentTime`)')->where($map)->count('distinct userCode');
        if ($isMonth) {
            $DAU = M('sp_ios_month_dau','la_','CySlave')->alias('a')->field("SUM(DAU) AS DAU,SUM(oldUserLogin) AS oldUserLogin")->where($map2)->select();
            if(empty($DAU)){
                //没有当月的数据则为空
                $DAU[0] = array("DAU" => 0, "oldUserLogin" => 0);
            }
        } else {
            $DAU    = M('sp_ios_user_dau','la_','CySlave')->field("COUNT(DISTINCT userCode) AS DAU,COUNT(DISTINCT CASE WHEN regTime<".strtotime($startDate)." THEN userCode END) AS oldUserLogin")->where($map2)->select();
        }
        $count = array('allPayUser'=>$allPayUser,'login'=>$DAU[0]);
    }
    
    unset($list);
    return array('list'=>$newList ? $newList : array(),'count'=>$count);
    }

   /**
     * 获取老用户数据总和
     * @param $map
     * @return mixed
     */
    public function getOldUserTableCount($map)
    {
        return count(M("sp_user_game_day",C('DB_PREFIX'),'CySlave')->alias("a")->where($map)->group("a.dayTime,gameId")->select());
    }

    /**
     * 获取老用户统计信息
     * @param $map
     * @param $fist
     * @param $size
     * @return mixed
     */
    public function getOldUserTable($map, $fist, $size)
    {
        return M("sp_user_game_day",C('DB_PREFIX'),'CySlave')->alias("a")->field("SUM(oldUserLogin) AS oldLogin,a.dayTime AS day,gameId")->where($map)->group("a.dayTime,gameId")->order("a.dayTime ASC")->limit($fist, $size)->select(); 
    }

    /**
     * 获取用户充值LTV(包)数据
     * @param array $map 搜索条件
     * @param string $where 汇总搜索条件
     * @return array 返回结果数组
     */
    public function getPayLtvAgentData($map,$start=0,$pageSize=30,$where=1)
    {
        //lookType 1：根据游戏查看汇总 2：查看明细
        $lookType = I('lookType',0,'intval');
        $export = I('export',0,'intval');
        if(empty($lookType)) return array('list'=>array(),'count'=>array());

        $mod = M('sp_agent_pay_day',C('DB_PREFIX'),'CySlave');
        $days = '';
        $users = '';
        $day_arr = array();//定义数组
        for($i = 0; $i<90; $i++){
            $day_arr[] = $i;
        }

        for($i=0;$i<=120;$i++){
            if(in_array($i, $day_arr)){
                $days .= "SUM(SUBSTRING_INDEX(day{$i},',',1)) AS day{$i},";
                $users .= "SUM(SUBSTRING_INDEX(day{$i},',',-1)) AS user{$i},";
            }
        }
        $days = trim($days,',');
        $users = trim($users,',');
            $field = 'dayTime,agent,gameId,IFNULL(SUM(newUser),0) AS newUser,'.$days.','.$users;

            $count = $mod->query("SELECT COUNT(1) AS total FROM (SELECT * FROM la_sp_agent_pay_day WHERE {$where} GROUP BY dayTime,gameId) a")[0]['total'];
            $list = $mod->query("SELECT {$field} FROM la_sp_agent_pay_day WHERE {$where} GROUP BY dayTime,gameId ORDER BY dayTime ASC ".($export != 1 ? " LIMIT {$start},{$pageSize}" : ''));
        
        return array('list'=>$list ? $list : array(),'count'=>$count);
    }

    /**
     * 获取每日的DAU
     * @return [type] [description]
     */
    public function getDauData($map,$start=0,$pageSize=30,$where=1){
        $export = I('export',0,'intval');

        $mod = M('sp_user_game_day',C('DB_PREFIX'),'CySlave');
        $days = '';
//        $day_arr = array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,29,59,89);
        $day_arr = array();//定义数组
        for($i = 0; $i<90; $i++){
            $day_arr[] = $i;
        }
        for($i=0;$i<=120;$i++){
            if(in_array($i, $day_arr)){
                $days .= "SUM(day{$i}) AS day{$i},";
            }
        }
        $days = trim($days,',');
        $field = 'dayTime,gameId,'.$days;

        $list = $mod->query("SELECT {$field} FROM la_sp_user_game_day WHERE {$where} GROUP BY dayTime,gameId ORDER BY dayTime ASC ".($export != 1 ? " LIMIT {$start},{$pageSize}" : ''));
        
        return array('list'=>$list ? $list : array());
    }

    /**
     * 获取滚服统计数据 
     * @return [type] [description]
     */
    public function getGunfuData($map,$start=0,$pageSize=30)
    {
        $export = I('export',0,'intval');
        $mod = M('sp_gunfu_day',C('DB_PREFIX'),'CySlave');
        $field = "dayTime,gameId,SUM(newUser) AS newUser,SUM(actUser) AS actUser,SUM(allPay) AS allPay,SUM(allPayUser) AS allPayUser";
        if($export == 1){
            $list = $mod->field($field)->where($map)->group("dayTime,gameId")->select();
        }else{
            $list = $mod->field($field)->where($map)->group("dayTime,gameId")->limit($start,$pageSize)->select();
        }
        return array('list'=>$list);
    }   

}