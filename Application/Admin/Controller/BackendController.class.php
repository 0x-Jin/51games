<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/6/2
 * Time: 9:51
 *
 * 后台继承模块
 */

namespace Admin\Controller;

use Think\Controller;

class BackendController extends Controller
{
    protected $table         = null; //数据表
    protected $tpl           = null; //前端模板文件
    protected $partment      = null; //部门列表
    protected $parId         = null; //所属部门id
    protected $agentArr      = null; //用户可以查看的渠道号
    protected $gameId        = null; //用户可以查看的游戏ID
    protected $contractArr   = null; //可以查看合同的人
    protected $contractAll   = null; //可以所有合同的人
    protected $contractOne   = null; //可以看一部合同的人
    protected $contractTwo   = null; //可以看二部合同的人
    protected $contractEdit  = null; //可以编辑合同的人
    protected $orderSupple   = null; //可以补单的账号ID
    protected $tplPartment   = null; //前端页面用的部门模板

    public function _initialize()
    {
        
//        if(!in_array($_SERVER['HTTP_HOST'],array('adv.cmgcwl.cn','gc.cmgcwl.cn')) ){
//            header('HTTP/1.1 404 Not Found');
//            die();
//        }
        
        if ((in_array(session('admin.uid'),array(114,112)) && ! in_array(get_ip_address(), array(
            '218.19.99.122',
            '61.145.249.43',
            '183.6.114.241'
        ))) || (session('admin') && !in_array(session('admin.uid'),array(114,112)) && $_SERVER['HTTP_HOST'] == 'gc.cmgcwl.cn')) {
            // 禁止在公司范围外登录
            session('admin', null);
            header('HTTP/1.1 404 Not Found');
            die();
        }

        $this->table = (string)$_REQUEST['table'];
        $this->tpl = (string)$_REQUEST['tpl'];
        $this->CheckAdmin();
        $this->parId = session('admin.partment') == 0 ? 0 : array(session('admin.partment'),3); //所属部门ID
        $this->partment = array(
            array('partment_id'=>1,'name'=>'发行一部'),
            array('partment_id'=>2,'name'=>'发行二部'),
            array('partment_id'=>3,'name'=>'公共部门'),
        );

        //前端页面公共部门下拉框控制
        if (session('admin.partment') == 0) {
            $this->tplPartment = '<option value="1">发行一部</option><option value="2">发行二部</option><option value="3">公共部门</option>';
        } elseif (session('admin.partment') == 1) {
            $this->tplPartment = '<option value="1">发行一部</option> <option value="3">公共部门</option>';
        } elseif (session('admin.partment') == 2) {
            $this->tplPartment = '<option value="2">发行二部</option> <option value="3">公共部门</option>';
        }
        $this->assign('tplPartment',$this->tplPartment);
        
        //可以查看合同的人
        $this->contractArr = array(4,6,7,9,10,11,12,13,18,20,21,22,23,32,33,45,53,54,55,56,57,58,59,83,85,86);
        //可以查看所有部门合同的人
        $this->contractAll = array(7,4,9,20,22,32,33,45,54,55,56,57,58,59,83,85,86);
        //可以查看一部合同的人
        $this->contractOne = array(6,10,11,12,13,18);
        //可以查看二部合同的人
        $this->contractTwo = array(21,23,53);
        //编辑合同的人
        $this->contractEdit= array(9,53,54,1,3,8);
        //可以补单的账号ID
        $this->orderSupple = array(
            17,     //蔡典融
            66,     //马宇荻
            70,     //李天皓
            71,     //赖钧焯
            72,     //曾然
            78,     //段梦云
        );

        $data = array();

        if (!empty(session('admin.username'))) {
            $cacheName = session('admin.username').'_'.session('admin.role_id').'_'.session('admin.partment').'_agentCache';

            if (!S($cacheName)) {
                $map = array();
                // if (!in_array(session('admin.role_id'),array(1))) {
                //     //不在上面三个角色里不可以看融合的包
                // }
                // $map['channel_id'] = array('ELT',1);
                $map['platform_id'] = 1;

                if ($this->parId) {
                    $map['departmentId'] = array('IN',$this->parId);
                }
                if (in_array(session('admin.role_id'),array(1,3)) || session('admin.uid') == 85) {
                    //如果是运营数据概况则不需要
                    if (ACTION_NAME == 'getAgent') {

                    } else {
                        if ((ACTION_NAME != 'userRegRemain' && ACTION_NAME != 'order') || CONTROLLER_NAME != 'Data') $map['game_id'] = array('neq',104);
                    }

                } elseif (session('admin.role_id') == 27) {
                    $map['game_id'] = array('eq',104);  
                } else {
                    $map['game_id'] = array('neq',104);
                }
                $this->agentArr = array_column(M('agent',C('DB_PREFIX_API'),'CySlave')->field('agent')->where($map)->select(), 'agent');
                if (in_array(session('admin.role_id'),array(3))) $this->agentArr[] = 'jyjhAAA';
                if ($this->agentArr && (!in_array(session('admin.role_id'),array(3)) && session('admin.uid') != 85) ){
                    $data['agent'] = $this->agentArr;
                    S($cacheName,$data,600);
                }
            } else {
                $data = S($cacheName);
                $this->agentArr = $data['agent'];
            }
        }
    }

    /**
     * 检测用户是否已经登陆
     */
    public function CheckAdmin() {     //权限验证
        if ((!isset($_SESSION["admin"]) || !$_SESSION["admin"]) && !in_array(strtolower(ACTION_NAME), array("login", "verify"))) {
            exit('<script>top.location.href="'.U("Index/login").'";</script>');
        }
    }
    
    /**
     * 后台公用添加
     */
    public function add($mod = null) 
    {
        $mod = $mod ? $mod:D($this->table);
        if (empty($mod)) {
            IS_AJAX && $this->ajaxReturn(array('status'=>0,'info'=>'数据模型不能为空'));
            $this->error('数据模型不能为空');
        }
        if (IS_POST) {
            $data = $mod->create();
            if (false === $data = $mod->create()) {
                IS_AJAX && $this->ajaxReturn(array('status'=>0,'info'=>$mod->getError()));
                $this->error($mod->getError());
            }
            if (method_exists($this, '_before_insert')) {
                $data = $this->_before_insert($data);
                 
            }
            if (false !== $id = $mod->add($data)) {
                bgLog(4,$mod->getTableName()."  添加ID".$id.$mod->_sql());
                if ( method_exists($this, '_after_insert')) {
//                    $id = $mod->getLastInsID();
                    $this->_after_insert($id);
                }
                
                IS_AJAX && $this->ajaxReturn(array('status'=>1,'info'=>'操作成功'));
                $this->success(L('操作成功'));
            } else {
                IS_AJAX && $this->ajaxReturn(array('status'=>0,'info'=>'操作失败'));
                $this->error(L('操作失败'));
            }
        } else {
            if (method_exists($this, '_get_before_insert')) {
                $this->_get_before_insert($this->table);
            }
            if (IS_AJAX) {
                $response = $this->fetch($this->tpl);
                $this->ajaxReturn(array('status'=>1,'_html'=>$response));
            } else {
                $this->display($this->tpl);
            }
        }
    }
    
    /**
     * 后台公用修改方法
     */
    public function edit($mod = null)
    {
        $mod =$mod? $mod : D($this->table);
        if(empty($mod)){
            IS_AJAX && $this->ajaxReturn(array('status'=>0,'info'=>'数据模型不能为空'));
            $this->error('数据模型不能为空');
        }
        $pk = $mod->getPk();
        if (IS_POST) {
            if (false === $data = $mod->create()) {
                IS_AJAX && $this->ajaxReturn(array('status'=>0,'info'=>$mod->getError()));
                $this->error($mod->getError());
            }

            if (method_exists($this, '_before_update')) {
                $data = $this->_before_update($data);
            }

            if (false !== $mod->save($data)) {
                if( method_exists($this, '_after_update')){
                    $id = $data[$pk];
                    $this->_after_update($id);
                }
                bgLog(3,$mod->getTableName()."  修改ID".$data[$pk]);
                IS_AJAX && $this->ajaxReturn(array('status'=>1,'info'=>'操作成功'));
                $this->success(L('操作成功'));
            } else {
                IS_AJAX && $this->ajaxReturn(array('status'=>0,'info'=>'操作失败'));
                $this->error(L('操作失败'));
            }
        } else {
            $id = I($pk,0,'intval');
            $info = $mod->find($id);
            if (method_exists($this, '_get_before_update')) {
                $reinfo = $this->_get_before_update($this->table,$info);
                !empty($reinfo) && $info = $reinfo;
            }
            $this->assign('info', $info);
            if (IS_AJAX) {
                $response = $this->fetch($this->tpl);
                $this->ajaxReturn(array('status'=>1,'_html'=>$response));
            } else {
                $this->display($this->tpl);
            }
        }
    }
    
    /**
     * 后台公用删除
     */
    public function delete($mod = NULL)
    {
    
        $mod = $mod ? $mod : D($this->table);
        if (empty($mod)) {
            IS_AJAX && $this->ajaxReturn(array('status'=>0,'info'=>'数据模型不能为空'));
            $this->error('数据模型不能为空');
        }
        $pk = $mod->getPk();
        $id = array_map('intval', $_REQUEST[$pk]);
        if (is_array($id)) {
            $ids = $id;
        } else {
            $id = $_REQUEST[$pk];
            $ids = array_map('intval', explode(',', $id));
        }
        if ($ids) {
            if (method_exists($this, '_before_del')) {
                $ids = $this->_before_del($ids);
            }
            if (count($ids) < 1 || empty($ids)) {
                IS_AJAX && $this->ajaxReturn(array('status'=>0,'info'=>'操作失败'));
                $this->error('操作失败');
            }
            if (false !== $mod->where(array($pk=>array('in',$ids)))->delete()) {
                if( method_exists($this, '_after_del')){
                    $this->_after_delete($ids);
                }
                bgLog(2,$mod->getTableName()."  删除ID:".implode(',', $ids).$mod->_sql());
                IS_AJAX && $this->ajaxReturn(array('status'=>1,'info'=>'操作成功'));
                $this->success(L('操作成功'));
            } else {
                IS_AJAX && $this->ajaxReturn(array('status'=>0,'info'=>'操作失败'));
                $this->error(L('操作失败'));
            }
        } else {
            IS_AJAX && $this->ajaxReturn(array('status'=>0,'info'=>'缺少主键参数'));
            $this->error(L('缺少主键参数'));
        }
    }

}
