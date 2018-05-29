<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/6/2
 * Time: 9:51
 *
 * 后台继承模块
 */

namespace ThirdParty\Controller;

use Think\Controller;

class BackendController extends Controller
{
    protected $table         = null; //数据表
    protected $tpl           = null;
    protected $pids          = null; //负责人id
    protected $agentArr      = null; //用户可以查看的渠道号
    protected $advterArr     = null; //用户可以查看的渠道商ID
    protected $gameId        = null; //用户可以查看的游戏ID
    protected $events        = null; //用户可以查看的广告位
    protected $groups        = null; //用户可以查看的广告组
    protected $dataTime      = null; //用户可以查看的数据的时间区间

    public function _initialize()
    {
        $this->table = (string)$_REQUEST['table'];
        $this->tpl = (string)$_REQUEST['tpl'];
        $this->CheckAdmin();
        $this->pids = session('admin.principal_ids'); //可查看的负责人ID
        $this->parId = session('admin.partment'); //所属部门ID
        $this->partment = array(
            array('partment_id'=>1,'name'=>'发行一部'),
            array('partment_id'=>2,'name'=>'发行二部')
        );
        $this->dataTime = array(
            'youxiang' => array('startDate'=>'2018-02-01','endDate'=>''),
        );

        $data = array();
        if(!empty(session('admin.username'))){
            $cacheName = session('admin.username').'_'.session('admin.role_id').'_advteruserCache';

            if(!S($cacheName)){
                //权限控制
                $user = D('Admin')->commonQuery('admin',array('name'=>session('admin.username')),0,1,'*',C('DB_PREFIX_TP'));
                
                if(!empty($user['agent_ids'])) $this->agentArr    = $data['agent']  = explode(',',$user['agent_ids']);
                if(!empty($user['advter_ids'])) $this->advterArr  = $data['advter'] = explode(',',$user['advter_ids']);
                if(!empty($user['game_ids'])) $this->gameId       = $data['game']   = explode(',',$user['game_ids']);
                if(!empty($user['events'])) $this->events         = $data['events'] = explode(',',$user['events']);
                if(!empty($user['groups'])) $this->groups         = $data['groups'] = explode(',',$user['groups']);
                if(isset($this->gameId[104])) {
                    unset($this->gameId[104]);
                    unset($data['game'][104]);
                }

                if(is_null($this->agentArr) && !is_null($this->gameId)){
                    $this->agentArr = $data['agent'] = array_column(M('agent',C('DB_PREFIX_API'),'CySlave')->where(array('game_id'=>array('IN',$this->gameId)))->select(),'agent');
                }

                if(is_null($this->agentArr) && !is_null($this->advterArr)){
                    $this->agentArr = $data['agent'] = array_column(M('agent',C('DB_PREFIX_API'),'CySlave')->where(array('advteruser_id'=>array('IN',$this->advterArr),'game_id'=>array('neq',104)))->select(),'agent');
                }


                if(session('admin.role_id') == 1 && is_null($this->agentArr)){
                    $this->agentArr = $data['agent'] = array_column(M('agent',C('DB_PREFIX_API'),'CySlave')->where(array('game_id'=>array('neq',104)))->select(),'agent');
                }

                if(session('admin.role_id') == 1 && is_null($this->events)){
                    $this->events   = $data['events'] = array_column(M('events',C('DB_PREFIX'),'CySlave')->select(),'id');
                }

                if(session('admin.role_id') == 1 && is_null($this->groups)){
                    $this->groups   = $data['groups'] = array_column(M('events_group',C('DB_PREFIX'),'CySlave')->select(),'id');
                }

                S($cacheName,$data,600);
            }else{
                $data = S($cacheName);
                $this->agentArr  = $data['agent'];
                $this->advterArr = $data['advter'];
                $this->gameId    = $data['game'];
                $this->events    = $data['events'];
                $this->groups    = $data['groups'];
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
        if(empty($mod)){
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
            if(false !== $id = $mod->add($data) ){
                bgLog(4,$mod->getTableName()."  添加ID".$id.$mod->_sql());
                if( method_exists($this, '_after_insert')){
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
        if(empty($mod)){
            IS_AJAX && $this->ajaxReturn(array('status'=>0,'info'=>'数据模型不能为空'));
            $this->error('数据模型不能为空');
        }
        $pk = $mod->getPk();
        $id = array_map('intval', $_REQUEST[$pk]);
        if(is_array($id)){
            $ids = $id;
        }else{
            $id = $_REQUEST[$pk];
            $ids = array_map('intval', explode(',', $id));
        }
        if ($ids) {
            if (method_exists($this, '_before_del')) {
                $ids = $this->_before_del($ids);
            }
            if(count($ids) < 1 || empty($ids)){
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