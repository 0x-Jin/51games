<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/6/2
 * Time: 11:19
 *
 * 登陆控制器
 */
namespace Admin\Controller;

use Admin\Controller\BackendController;
use Think\Verify;

class IndexController extends BackendController
{

    public function index()
    {
        $topMenu = D('Admin')->bgMenu(0); // 一级目录
        foreach ($topMenu as $tkey => $tval) {

            $left_menu = D('Admin')->bgMenu($tval['id']); // 二级目录
            foreach ($left_menu as $lkey => $lval) {
                //合同管理id 10650
                if(!in_array(session('admin.uid'), $this->contractArr) && $lval['id'] == 10650 && session('admin.role_id') != 1) continue;

                $left_menu_sub = D('Admin')->bgMenu($lval['id']); // 三级目录
                foreach ($left_menu_sub as $sval) {
                    $itmes[] = array(
                        'id' => $sval['id'],
                        'text' => $sval['name'],
                        'href' => U($sval['controllerName'] . '/' . $sval['actionName']).($sval['data'] ? '?'.$sval['data'] : '')
                    );
                }
                $menu[] = array(
                    'text' => $lval['name'],
                    'items' => $itmes
                );
                unset($itmes);
            }
            
            $nleft_menu[] = array(
                'id' => $tval['id'],
                'menu' => $menu
            );
            /*
             * if($tkey == 2){
             * var_dump(json_encode($nleft_menu));die;
             * }
             */
            
            unset($menu);
        }
        
        $leftmenu = json_encode($nleft_menu);
        $adminInfo = array(
            'username' => session('admin.realname'),
            'rolename' => session('admin.role_name')
        );
        $this->assign('adminInfo', $adminInfo);
        $this->assign('topMenu', $topMenu);
        $this->assign('leftmenu', $leftmenu);
        $this->display();
    }
    
    /**
     * 密码修改
     */
    public function edit()
    {
        if (IS_POST) {
            $newpwd = I('newpwd', '', 'trim');
            $oldpwd = I('oldpwd', '', 'trim');
            $repwd = I('repwd', '', 'trim');
            if (! preg_match('/^[a-zA-Z]{1}[\w!@#$%]{5,16}$/', $newpwd)) {
                IS_AJAX && $this->ajaxReturn(array(
                    'status'=>-1,
                    'msg'=>'新密码必须由6-16位字母、数字、下划线组成，字母开头'
                ));
                //$this->error('新密码必须由6-16位字母、数字、下划线组成，字母开头');
            }
            // 查询账号是否存在
            $admin = D('Admin')->commonQuery('admin', array(
                'id' => session('admin.uid'),
                'status' => 0
            ));
            if (! check_password($oldpwd, $admin['password'])) {
                IS_AJAX && $this->ajaxReturn(array(
                    'status'=>-1,
                    'msg'=>'旧密码有误'
                ));
            }
            if ($newpwd != $repwd) {
                IS_AJAX && $this->ajaxReturn(array(
                    'status'=>-1,
                    'msg'=>'新密码和确认密码不一致'
                ));
            }
            $res = D('Admin')->commonExecute('admin', array(
                'id' => $admin['id']
            ), array(
                'password' => make_password($newpwd)
            ));
            if($res){
                bgLog(3, session('admin.username') . '修改了密码');
                IS_AJAX && $this->ajaxReturn(array(
                    'status'=>0,
                    'msg'=>'密码修改成功'
                ));
            }else{
                IS_AJAX && $this->ajaxReturn(array(
                    'status'=>-1,
                    'msg'=>'密码修改失败'
                ));
            }
        } else {
            $tpl = $this->fetch();
            $this->ajaxReturn($tpl);
        }
    }
    
    /**
     * 登录
     */
    public function login()
    {

        if (IS_POST) {
            $name = trim($_POST['name']);
            $password = trim($_POST['password']);
            $code = trim($_POST['code']);
            if (! check_verify($code)) {
                $this->error("验证码输入错误！");
            }
            
            // 查询账号是否存在
            $admin = D('Admin')->commonQuery('admin', array(
                'name' => $name,
                'status' => 0
            ));

            if (empty($admin)) {
                $this->error('用户不存在或已被冻结');
            }
            
            // 权限
            $manager = D('Admin')->commonQuery('manager', array(
                'id' => $admin['manager_id'],
                'status' => 0
            ));
            $admin['role_name'] = $manager['name'];
            
            if (! check_password($password, $admin['password'])) {
                $this->error('密码错误');
            }
            session('admin', array(
                'uid'           => $admin['id'],
                'role_id'       => $admin['manager_id'],
                'username'      => $admin['name'],
                'realname'      => $admin['real'],
                'partment'      => $admin['partment'],
                'role_name'     => $admin['role_name'],
                'principal_ids' => $admin['principal_id'],
                'game_id'       => $admin['game_id'],
                'user_type'     => $admin['user_type'],
            ));

            D('Admin')->commonExecute('admin', array(
                'id' => $admin['id']
            ), array(
                'updateTime' => time(),
                'lastLogin' => time(),
                'lastIP' => get_client_ip(),
                'last_session_id' => session_id()
            ));
            bgLog(1, $admin['name'] . '登录了数据后台');
            $this->success('登录成功', U('Index/index'));
        } else {
            $this->display();
        }
    }
    
    /**
     * 登出
     */
    public function logout()
    {
        session('admin', null);
        $this->success(L('退出成功'), U('Index/login'));
        exit;
    }

    /**
     * 验证码
     */
    public function verify()
    {
        $Verify = new Verify();
        $Verify->fontSize = 21;
        $Verify->length = 4;
        $Verify->useNoise = false;
        $Verify->codeSet = '0123456789';
        $Verify->imageW = 150;
        $Verify->entry();
    }
}