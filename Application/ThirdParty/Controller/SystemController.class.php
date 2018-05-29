<?php
/**
 * Created by Zend.
 * User: XSM
 * Date: 2017/6/6
 * Time: 11:19
 *
 * 系统管理控制器
 */

namespace ThirdParty\Controller;
use ThirdParty\Controller\BackendController;

class SystemController extends BackendController
{
    /**
     * 系统菜单
     */
    public function menu()
    {
        $this->display();

    }

    /**
     * 后台操作日志
     * @DateTime  2017-06-26T10:42:16+0800
     */
    public function operationList()
    {
        if(IS_POST){
            $data = I();
            $map = array();
            $admin = getDataList('admin','real',C('DB_PREFIX_TP'),array('real'=>$data['author']));
            $data['author'] && $map['author'] = $admin[$data['author']]['name'];
            $data['type'] && $map['type'] = $data['type'];
            $data['startDate'] && $map['create_time'][] = array('egt',$data['startDate']);
            $data['endDate'] && $map['create_time'][] = array('lt',date('Y-m-d',strtotime($data['endDate'].' +1 day')));
            $start=I('start',0,'intval');
            $pageSize=I('limit',30,'intval');
            $list = D('Admin')->getBuiList($this->table,$map,$start,$pageSize);
            $admin_list = getDataList('admin','name',C('DB_PREFIX_TP'));
            $type = array(1=>'登录',2=>'删除',3=>'修改',4=>'编辑');

            foreach ($list['list'] as $key=>$val){
                $list['list'][$key]['type'] = $type[$val['type']];
                $list['list'][$key]['opt'] = '<a href="javascript:;" onclick="detail('.$val['id'].',this)">详情</a>';
                $list['list'][$key]['author'] = $admin_list[$val['author']]['real'];
                $rows[] = $list['list'][$key];
            }
            $arr = array('rows'=>(empty($rows) ? array() : $rows),'results'=>$list['count']);
            exit(json_encode($arr));
        }else{
            $this->display();
        }
        
    }

    /**
     * 后台操作日志前置方法
     * @DateTime  2017-06-26T10:42:16+0800
     */
    public function _get_before_update($table,$info)
    {
        if($table == 'operation_log'){
            $type = array(1=>'登录',2=>'删除',3=>'修改',4=>'编辑');
            $admin_list = getDataList('admin','name',C('DB_PREFIX_TP'));
            $info['type'] = $type[$info['type']];
            $info['author'] = $admin_list[$info['author']]['real'];
            return $info;
        }
    }
    
    /**
     * 系统菜单编辑
     */
    public function menuEdit()
    {
        if(IS_POST){
            $data = I();
            if(!$data['id']){
                $this->error('参数有误',U('System/menu'));
            }
            if(D('Admin')->commonExecute('menu',array('id'=>$data['id']),$data,C('DB_PREFIX_TP'))){
                bgLog(3, session('admin.username') . '修改了菜单，修改的参数:'.http_build_query($data));
                $this->success('修改成功',U('System/menu'));
            }else{
                $this->error('修改失败',U('System/menu'));
            }
        }else{
            $id = I('id',0,'intval');
            $menu_info = D('Admin')->commonQuery('menu',array('id'=>$id),0,1,'*',C('DB_PREFIX_TP'));
            $menu_list = D('Admin')->getMenu();
            
            $tree = new \Vendor\Tree\Tree();
            foreach($menu_list as $r) {
                $r['selected'] = $r['id'] == $menu_info['pid'] ? 'selected' : '';
                $array[] = $r;
            }
            $str  = "<option value='\$id' \$selected>\$spacer \$name</option>";
            $tree->init($array);
            $select_menus = $tree->get_tree(0, $str);
            $this->assign('info',$menu_info);
            $this->assign('select_menus',$select_menus);
            
            if(IS_AJAX){
                $respose = $this->fetch();
                $this->ajaxReturn(array('status'=>0,'_html'=>$respose));
            }else{
                $this->display();
            }
        }
    }
    
    /**
     * 系统菜单添加
     */
    public function menuAdd()
    {
        if(IS_POST){
            $data = I();
            if(!$data){
                $this->error('请检查必填参数是否为空',U('System/menu'));
            }
            if($data['pid'] == 0){
                $success = '一级菜单添加成功';
                $error = '一级菜单添加失败';
            }else{
                $success = '子菜单添加成功';
                $error = '子菜单添加失败';
            }
            if(D('Admin')->commonAdd('menu',$data,C('DB_PREFIX_TP'))){
                bgLog(4, session('admin.username') . '添加了菜单，参数：'.http_build_query($data));
                $this->success($success,U('System/menu'));
            }else{
                $this->error($error,U('System/menu'));
            }
        }else{
            $pid = I('pid',0,'intval');
            $menu_list = D('Admin')->getMenu();
    
            $tree = new \Vendor\Tree\Tree();
            foreach($menu_list as $r) {
                $r['selected'] = $r['id'] == $pid ? 'selected' : '';
                $array[] = $r;
            }
            $str  = "<option value='\$id' \$selected>\$spacer \$name</option>";
            $tree->init($array);
            $select_menus = $tree->get_tree(0, $str);
            $this->assign('select_menus',$select_menus);
    
            if(IS_AJAX){
                $respose = $this->fetch();
                $this->ajaxReturn(array('status'=>0,'_html'=>$respose));
            }else{
                $this->display();
            }
        }
    }
    
    /**
     * 系统菜单删除
     */
    public function menuDelete()
    {
        $id = I('id',0,'intval');
        if(!$id){
            $this->error('参数有误');
        }
        is_int($id) && $id = array($id);
        if(D('Admin')->commonDelete('menu',array('id'=>array('in',$id)),C('DB_PREFIX_TP'))){
            bgLog(2, session('admin.username') . '删除了菜单，参数id：'.http_build_query($id));
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
    }
    
    /**
     * 后台用户列表
     */
    public function user()
    {
        if(IS_POST){
            $data = I();
            $start = $data['start'] ? $data['start'] : 0;
            $pageSize = $data['limit'] ? $data['limit'] : 30;
            $map = array();
            $data['name'] && $map['a.name'] = array('like','%'.$data['name'].'%');
            $data['real'] && $map['a.real'] = array('like','%'.$data['real'].'%');
            $data['id'] && $map['a.id'] = $data['id'];
            $data['user_type'] && $map['a.user_type'] = $data['user_type'];
            $list = D('Admin')->getUser($map,$start,$pageSize);
            $results = $list['count'];
            foreach ($list['list'] as $key=>$val){
                $list['list'][$key]['opt'] = '<a href="javascript:;" manager_id='.$val['manager_id'].' onclick="userEdit('.$val['id'].',this)">编辑</a>';
                $list['list'][$key]['opt'] .= ' | <a href="javascript:;" onclick="clearCache('.$val['id'].',this)">清缓存</a>';

                $rows[] = $list['list'][$key];
            }
            $arr = array('rows'=>$rows,'results'=>$results);
            exit(json_encode($arr));
        }else{
            $this->display();
        }
    }

    /**
     * 后台缓存清除
     */
    public function clearCache()
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
            $user = D('Admin')->commonQuery('admin',array('id'=>$data['id']),0,1,'*',C('DB_PREFIX_TP'));
            $cacheName = $user['name'].'_'.$user['manager_id'].'_advteruserCache';
            if(S($cacheName,null)){
                IS_AJAX && $this->ajaxReturn(array(
                    'status'=>1,
                    'info'=>'清除成功'
                ));
            }else{
                IS_AJAX && $this->ajaxReturn(array(
                    'status'=>0,
                    'info'=>'无缓存'
                ));
            }
        }
    }
    
    /**
     * 后台用户信息修改
     */
    public function userEdit()
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
            if($data['password'] && $data['repassword']){
                if (! preg_match('/^[a-zA-Z]{1}[\w!@#$%]{5,16}$/', $data['password'])) {
                    IS_AJAX && $this->ajaxReturn(array(
                        'status'=>0,
                        'msg'=>'新密码必须由6-16位字母、数字、_!@#$%组成，字母开头'
                    ));
                    $this->error('新密码必须由6-16位字母、数字、_!@#$%组成，字母开头');
                }
                
                if($data['password'] != $data['repassword']){
                    $this->error('新密码和确认密码不一致');
                }
                $password = make_password(trim($data['password']));
                $data['password'] = $password;
                unset($data['repassword']);
            }else{
                unset($data['repassword']);
                unset($data['password']);
            }

            $agent_ids  = implode(',', $data['agent_ids']);
            $game_ids   = implode(',', $data['game_ids']);
            $advter_ids = implode(',', $data['advter_ids']);
            $events     = implode(',', $data['events']);
            $groups     = implode(',', $data['groups']);

            $data['agent_ids']   = is_null($agent_ids) ? '' : $agent_ids;
            $data['game_ids']    = is_null($game_ids) ? '' : $game_ids;
            $data['advter_ids']  = is_null($advter_ids) ? '' : $advter_ids;
            $data['events']      = is_null($events) ? '' : $events;
            $data['groups']      = is_null($groups) ? '' : $groups;

            $data['updateTime'] = time();
            
            if(D('Admin')->commonExecute('admin',array('id'=>$data['id']),$data,C('DB_PREFIX_TP'))){
                bgLog(3, session('admin.username') . '修改了用户信息');
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
            $manager = D('Admin')->getManager(array('status'=>0),$start = 0,$pageSize = 30,$type = 'all',C('DB_PREFIX_TP'));
            $user_info = D('Admin')->commonQuery('admin',array('id'=>$data['id']),0,1,'*',C('DB_PREFIX_TP'));
            $gameList = getDataList('game','id',C('DB_PREFIX_API'));

            $role_list = '';
            $game_list = '';
            foreach ($manager['list'] as $k => $v){
                $role_list .= "<option value='{$v['id']}' ".($v['id'] == $data['manager_id'] ? 'selected=selected' : '').">{$v['name']}</option>";
            }

            foreach ($gameList as $key => $value) {
                $game_list .= "<option value='{$key}' ".(in_array($key,explode(',',$user_info['game_id'])) ? 'selected=selected' : '').">{$value['gameName']}</option>";
            }
            $map = array();
            if($user_info['partment'] != '0' && $user_info['manager_id'] != 1) {
                $map['department'] = $user_info['partment'];
            }
            $PrincipalList = M('principal')->where($map)->field("*")->select();
            $this->assign('aprincipals', $PrincipalList);
            $this->assign('partment',$this->partment);
            $this->assign('role_list',$role_list);
            $this->assign('game_list',$game_list);
            $this->assign('info',$user_info);
            if(IS_AJAX){
                    $respose = $this->fetch();
                    $this->ajaxReturn(array('status'=>1,'_html'=>$respose));
            }else{
                    $this->display();
            }
        }
    }
    
    /**
     * 后台用户信息新增
     */
    public function userAdd()
    {
        if (IS_POST) {
            $data = I();
            if(empty($data['name']) || empty($data['real'])){
                $this->error('账号和真实姓名不能为空');
            }
            if(D('Admin')->commonQuery('admin',array('name'=>$data['name']),0,1,'*',C('DB_PREFIX_TP'))){
                $this->error('用户【'.$data['name'].'】已经存在');
            }
            if (! preg_match('/^[a-zA-Z]{1}[\w!@#$%]{5,16}$/', $data['password'])) {
                IS_AJAX && $this->ajaxReturn(array(
                    'status' => 0,
                    'msg' => '新密码必须由6-16位字母、数字、_!@#$%组成，字母开头'
                ));
                $this->error('新密码必须由6-16位字母、数字、_!@#$%组成，字母开头');
            }
            
            if ($data['password'] != $data['repassword']) {
                $this->error('新密码和确认密码不一致');
            }

            $agent_ids    = implode(',', $data['agent_ids']);
            $game_ids     = implode(',', $data['game_ids']);
            $advter_ids   = implode(',', $data['advter_ids']);
            $events       = implode(',', $data['events']);
            $groups       = implode(',', $data['groups']);

            $data['agent_ids'] = $agent_ids;
            $data['game_ids']  = $game_ids;
            $data['advter_ids']= $advter_ids;
            $data['events']    = $events;
            $data['groups']    = $groups;

            $password = make_password(trim($data['password']));
            $data['password']     = $password;
            $data['createTime']   = time();
            unset($data['repassword']);
            
            if (D('Admin')->commonAdd('admin', $data,C('DB_PREFIX_TP'))) {
                bgLog(4, session('admin.username') . '添加用户');
                IS_AJAX && $this->ajaxReturn(array(
                    'status' => 1,
                    'info' => '新增成功'
                ));
                $this->success('新增成功');
            } else {
                IS_AJAX && $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => '新增失败'
                ));
                $this->success('新增失败');
            }
        } else {
            $manager = D('Admin')->getManager(array('status'=>0),$start = 0,$pageSize = 30,$type = 'all',C('DB_PREFIX_TP'));
            $role_list = '';
            foreach ($manager['list'] as $k => $v) {
                $role_list .= "<option value='{$v['id']}'>{$v['name']}</option>";
            }
            $PrincipalList = M('principal')->field("*")->select();
            $this->assign('aprincipals', $PrincipalList);
            $this->assign('partment',$this->partment);
            $this->assign('role_list', $role_list);
            if (IS_AJAX) {
                $respose = $this->fetch();
                $this->ajaxReturn(array(
                    'status' => 1,
                    '_html' => $respose
                ));
            } else {
                $this->display();
            }
        }
    }
    
    /**
     * 后台角色列表
     */
    public function role()
    {
        if(IS_POST){
            $data = I();
            $start = $data['start'] ? $data['start'] : 0;
            $pageSize = $data['limit'] ? $data['limit'] : 30;
            $map = array();
            $data['name'] && $map['name'] = $data['name'];

            $list = D('Admin')->getManager($map,$start,$pageSize,'limit',C('DB_PREFIX_TP'));
            $results = $list['count'];
            foreach ($list['list'] as $key=>$val){
                $list['list'][$key]['opt'] = '<a href="'.U('System/auth',array('role_id'=>$val['id'])).'" >授权</a> | <a href="javascript:;" onclick="roleEdit('.$val['id'].',this)">编辑</a> | <a href="javascript:;" onclick="roleDelete('.$val['id'].',this)">删除</a>';
                $rows[] = $list['list'][$key];
            }
            $arr = array('rows'=>$rows,'results'=>$results);
            exit(json_encode($arr));
        }else{
            $this->display();
        }
    }
    
    /**
     * 后台角色信息修改
     */
    public function roleEdit()
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
            $data['updateTime'] = time();
            if(D('Admin')->commonExecute('manager',array('id'=>$data['id']),$data,C('DB_PREFIX_TP'))){
                bgLog(3, session('admin.username') . '修改了角色');
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
            $role_info = D('Admin')->commonQuery('manager',array('id'=>$data['id']),0,1,'*',C('DB_PREFIX_TP'));
            $this->assign('role_info',$role_info);
            if(IS_AJAX){
                $respose = $this->fetch();
                $this->ajaxReturn(array('status'=>1,'_html'=>$respose));
            }else{
                $this->display();
            }
        }
    }
    
    /**
     * 后台角色删除
     */
    public function roleDelete()
    {
        $id = I('id',0,'intval');
        if(!$id){
            $this->error('参数有误');
        }
        is_int($id) && $id = array($id);
        if(D('Admin')->commonDelete('manager',array('id'=>array('in',$id),C('DB_PREFIX_TP')))){
            bgLog(2, session('admin.username') . '删除了角色');
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
    }
    
    /**
     * 后台角色添加
     */
    public function roleAdd()
    {
        if (IS_POST) {
            $data = I();
            if(empty($data['name'])){
                $this->error('角色名称不能为空');
            }
            $data['createTime'] = time();

            if (D('Admin')->commonAdd('manager', $data, C('DB_PREFIX_TP'))) {
                bgLog(4, session('admin.username') . '添加角色');
                IS_AJAX && $this->ajaxReturn(array(
                    'status' => 1,
                    'info' => '新增成功'
                ));
                $this->success('新增成功');
            } else {
                IS_AJAX && $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => '新增失败'
                ));
                $this->success('新增失败');
            }
        } else {
            if (IS_AJAX) {
                $respose = $this->fetch();
                $this->ajaxReturn(array(
                    'status' => 1,
                    '_html' => $respose
                ));
            } else {
                $this->display();
            }
        }
    }
    
    /**
     * 后台授权
     */
    public function auth()
    {
        if (isset($_POST['dosubmit'])) {
            $id = intval($_POST['role_id']);
            //清空权限
            D('Admin')->commonDelete('manager_menu',array('manager_id'=>$id),C('DB_PREFIX_TP'));
            if (is_array($_POST['menu_id']) && count($_POST['menu_id']) > 0) {
                foreach ($_POST['menu_id'] as $menu_id) {
                    D('Admin')->commonAdd('manager_menu',array('manager_id'=>$id,'menu_id'=>$menu_id),C('DB_PREFIX_TP'));
                }
            }
            bgLog(4, session('admin.username') . "赋予角色ID：".I('role_id')."，权限id：".implode(",",I("menu_id")));
            $this->success(L('授权成功'));
        } else {
            $id = I('role_id',0, 'intval');
            $tree = new \Vendor\Tree\Tree();
            $tree->icon = array('│ ','├─ ','└─ ');
            $tree->nbsp = '&nbsp;&nbsp;&nbsp;';
            $result = D('Admin')->getMenu();
            //获取被操作角色权限
            $role_data = D('Admin')->getRole($id);
            $priv_ids = array();
            foreach ($role_data['role_priv'] as $val) {
                $priv_ids[] = $val['menu_id'];
            }

            foreach($result as $k=>$v) {
                $result[$k]['level'] = D('Admin')->getLevel($v['id'],$result);
                $result[$k]['checked'] = (in_array($v['id'], $priv_ids))? ' checked' : '';
                $result[$k]['parentid_node'] = ($v['pid'])? ' class="child-of-node-'.$v['pid'].'"' : '';
            }
            $str  = "<tr id='node-\$id' \$parentid_node>" .
                "<td style='padding-left:10px;'>\$spacer<input type='checkbox' name='menu_id[]' value='\$id' class='J_checkitem' level='\$level' \$checked> \$name</td>
                    </tr>";
            $tree->init($result);
            $menu_list = $tree->get_tree(0, $str);
            $this->assign('list', $menu_list);
            $this->assign('role', $role_data);
            $this->display();
    }
    }
}
