<?php
/**
 * Created by Sublime.
 * User: XSM
 * Date: 2017/10/09
 * Time: 15:09
 *
 * IOS推广管理控制器
 */
namespace Cy\Controller;
use Cy\Controller\BackendController;

class IOSController extends BackendController
{
	/**
	 * IOS推广管理列表
	 */
	public function eventsList()
	{
		if(IS_POST){
			$data       = I();
            //搜索条件
            $map = array();
            $data['agent'] && $map['agent'] = $data['agent'];
            $data['advteruser_id'] && $map['advteruser_id'] = $data['advteruser_id'];
            $data['events_name'] && $map['events_name'] = array('like','%'.$data['events_name'].'%');
            $data['groupId'] && $map['events_groupId'] = $data['groupId'];

            $start      = $data['start']? $data['start']: 0;
            $pageSize   = $data['limit']? $data['limit']: 30;
            if(session('admin.partment')){
                $map['department'] = session('admin.partment');
            }
            session('admin.role_id') != 1 && $map['is_zrl']  = array('neq',1);
            $list 	= D('IOS')->getEvents($map,$start,$pageSize);
            $count	= $list['count'];
            //获取广告商和游戏名
            $advterName = getDataList('advteruser','id',C('DB_PREFIX'));
            $group      = getDataList('events_group','id',C('DB_PREFIX'));
            $gameName 	= getDataList('agent','agent',C('DB_PREFIX_API'),array('agentType'=>1));
            $status 	= array('<span class="button button-danger">停用</span>','<span class="button button-info">启用</span>');
            $callbk 	= array('全部','激活报送','充值完成报送','注册报送');
            foreach ($list['list'] as $key => $val){
                $list['list'][$key]['callback']     = $callbk[$val['callBackStatus']];
            	$list['list'][$key]['groupName']	= $group[$val['events_groupId']]['groupName'];
                $list['list'][$key]['monitor_link'] = $val['monitor_link'] ? htmlspecialchars($val['monitor_link']) : '';
                $list['list'][$key]['createTime'] 	= date('Y-m-d H:i:s',$val['createTime']);
                $list['list'][$key]['advterUser'] 	= $advterName[$val['advteruser_id']]['company_name'];
                $list['list'][$key]['gameName']   	= $gameName[$val['agent']]['agentName'];
                $list['list'][$key]['opt']   	  	= createBtn('<a href="javascript:;" class="button button-primary" onclick="eventsEdit(\''.$val['id'].'\',this)">编辑</a>&nbsp;&nbsp;&nbsp;');

                in_array($val['advteruser_id'], array(10,2,14)) ? ($list['list'][$key]['opt'] .= createBtn(' | &nbsp;&nbsp;&nbsp;<a href="javascript:;" class="button button-primary" onclick="eventsConfig(\''.$val['id'].'\',this)">配置</a>&nbsp;&nbsp;&nbsp;  | &nbsp;&nbsp;&nbsp;<a href="javascript:;" onclick="eventsStop(\''.$val['id'].'\','.$val['status'].')">'.$status[$val['status']].'</a>')) : ($list['list'][$key]['opt'] .= createBtn(' | &nbsp;&nbsp;&nbsp;<a href="javascript:;" onclick="eventsStop(\''.$val['id'].'\','.$val['status'].')">'.$status[$val['status']].'</a>'));
            }

            $arr = array('rows' => $list['list'], 'results' => $count);
            exit(json_encode($arr));
		}else{
			if(IS_AJAX){
				$respose = $this->fetch();
            	$this->ajaxReturn(
            		array(
            			'status' 	=> 1,
            		 	'_html' 	=> $respose
            		 	)
            		);
			}else{
				$this->display();
			}
		}
	}

    public function event_add()
    {
        if(IS_POST){
            $data = I();
            if(!$data['events_name']){
                $this->error('推广活动名称不能为空');
            }

            if(!$data['events_groupId']){
                $this->error('推广活动组名称不能为空');
            }

            if(!$data['agent']){
                $this->error('请选择游戏');
            }

            if($data['num'] < 1){
                $this->error('生成的个数不能小于0');
            }

            $nowEvents = getDataList('events','events_name',C('DB_PREFIX')); //现有推广活动名称
            if(D('Admin')->commonQuery('events',array('events_name'=>$data['events_name']))){
                $this->error('推广活动名称已存在');
            }

            $game_id     = D('Admin')->commonQuery('agent',array('agent'=>$data['agent']),0,1,'game_id',C('DB_PREFIX_API'))['game_id'];

            $groupInfo = getDataList('events_group','id',C('DB_PREFIX'),array('id'=> $data['events_groupId']));
            $events_groupId = $data['events_groupId'];
            
            $time        = time();
            $department  = session('admin.partment');
            $creater     = session('admin.realname');
            $insert      = array();
            $updateEvent = array();
            $appid         = '';
            $advteruser_id = '';
            $sign_key      = '';
            $encrypt_key   = '';
            if(in_array($data['advteruser_id'],array(10,2,14))){
                $groupInfo[$events_groupId]['config_appid']         && $appid = $groupInfo[$events_groupId]['config_appid'];
                $groupInfo[$events_groupId]['config_advertiser_id'] && $advertiser_id = $groupInfo[$events_groupId]['config_advertiser_id'];
                $groupInfo[$events_groupId]['config_sign_key']      && $sign_key = $groupInfo[$events_groupId]['config_sign_key'];
                $groupInfo[$events_groupId]['config_encrypt_key']   && $encrypt_key = $groupInfo[$events_groupId]['config_encrypt_key'];
            }

            for ($i=1; $i <= $data['num']; $i++) { 
                $updateEvent[] = $events_name = $data['events_name'].'-'.$i;
                if(isset($nowEvents[$events_name])){
                    $this->error('推广活动名称已存在');
                    die();
                }
                $insert[] = array(
                    'createTime'        => $time,
                    'department'        => $department,
                    'game_id'           => $game_id,
                    'creater'           => $creater,
                    'events_name'       => $events_name,
                    'agent'             => $data['agent'],
                    'events_groupId'    => $data['events_groupId'],
                    'advteruser_id'     => $data['advteruser_id'],
                    'callBackStatus'    => $data['callBackStatus'],
                    'config_appid'            => $appid,
                    'config_advertiser_id'    => $advertiser_id,
                    'config_sign_key'         => $sign_key,
                    'config_encrypt_key'      => $encrypt_key,
                );
            }
            if(count($insert) > 0){
                $res = D('Admin')->commonAddAll('events',$insert);
                sleep(2);
            }

            if($res){
                $update = getDataList('events','id',C('DB_PREFIX'),array('events_name'=>array('IN',$updateEvent)));
                foreach ($update as $key => $value) {
                    $link = $this->createLink($value['id'],$value['agent'],$value['game_id'],$value['advteruser_id']);
                    //更新监控链接
                    D('Admin')->commonExecute('events',array('id'=>$value['id']),array('monitor_link'=>$link));
                }
                

                $this->success('操作成功');
            }else{
                $this->error('操作失败');
            }
        }
    }

    public function groupList()
    {
        if(IS_POST){
            $data       = I();
            //搜索条件
            $map = array();

            $data['groupName'] && $map['groupName'] = array('like','%'.$data['groupName'].'%');
            $data['creater'] && $map['creater'] = array('like','%'.$data['creater'].'%');

            $start      = $data['start']? $data['start']: 0;
            $pageSize   = $data['limit']? $data['limit']: 30;
            if(session('admin.partment')){
                $map['department'] = session('admin.partment');
            }
            $list   = D('IOS')->getEventsGroup($map,$start,$pageSize);
            $count  = $list['count'];

            foreach ($list['list'] as $key => $val){
                $list['list'][$key]['createTime']   = date('Y-m-d H:i:s',$val['createTime']);
                $list['list'][$key]['opt']          = '<a href="javascript:;" class="button button-primary" onclick="groupEdit(\''.$val['id'].'\',this)">编辑</a>&nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;<a href="javascript:;" class="button button-primary" onclick="groupConfig(\''.$val['id'].'\',this)">配置</a>&nbsp;&nbsp;&nbsp;';
            }

            $arr = array('rows' => $list['list'], 'results' => $count);
            exit(json_encode($arr));
        }else{
            if(IS_AJAX){
                $respose = $this->fetch();
                $this->ajaxReturn(
                    array(
                        'status'    => 1,
                        '_html'     => $respose
                        )
                    );
            }else{
                $this->display();
            }
        }
    }
	/**
     * 推广广告状态修改
     * @param string $table 操作的数据表
     */

	public function eventsStop()
	{
		$id 	= I('id',0,'intval');
		$status = I('status');
		!$id && $this->error('参数有误');
		if($status == 0){
			$res = D('Admin')->commonExecute('events', array('id'=>$id,'status'=>0), array('status'=>1));
		}elseif($status == 1){
			$res = D('Admin')->commonExecute('events', array('id'=>$id,'status'=>1), array('status'=>0));
		}
		if($res){
            $basedir = './TaskScript/advterLock/';
            if(!is_dir($basedir)){
                mkdir($basedir, 0777, true);
            }
            //判断活动状态
            if($status == 0){
                //停用，枷锁
                file_put_contents($basedir.$id.'Lock.log', '*');
            }elseif($status == 1){
                //启用，解锁
                file_put_contents($basedir.$id.'Lock.log', '');

            }

			$this->ajaxReturn(array('status'=>1,'info'=>'操作成功'));
		}else{
			$this->ajaxReturn(array('status'=>0,'info'=>'操作失败'));
		}
	}

	/**
     * 监控链接生成
     */
    protected function createLink($advter_id,$agent,$game_id,$adid)
    {
        //生成监控链接
        $param = D('Admin')->commonQuery('advteruser',array('id'=>$adid),0,1,'iosParam,id');
        $link = '';
        if(!empty($param['iosParam']) && $adid != 10){

            $link = "http://count.chuangyunet.net/AdvterClickIos.php?events={$advter_id}&gf=".$agent.'_'.$game_id."&adUser={$param['id']}&{$param['iosParam']}";
        }elseif($adid == 10 || $adid == 2){
            $link = "http://count.chuangyunet.net/AdvterClickIos.php?events={$advter_id}&gf=".$agent.'_'.$game_id."&adUser={$param['id']}";
        }
        return $link;
    }

	/**
     * 插入前置操作
     * @param string $table 操作的数据表
     */
    public function _before_insert($data)
    {
        
        if($this->table == 'events_group'){
            if(!$data['groupName']){
                $this->error('推广活动组名称不能为空');
            }

            if(D('Admin')->commonQuery('events_group',array('groupName'=>$data['groupName']))){
                $this->error('推广活动组名称已存在');
            }
            $data['createTime'] = time();
            $data['department'] = session('admin.partment');
            $data['creater']    = session('admin.realname');
        }

        return $data;
    }

    /**
     * 插入后置操作
     * @param string $table 操作的数据表
     */
    public function _after_insert($id)
    {
    	if(!$id) return false;
        /*if($this->table == 'events'){
        	$eventsinfo = D('Admin')->commonQuery('events',array('id'=>$id));
            $link = $this->createLink($id,$eventsinfo['agent'],$eventsinfo['game_id'],$eventsinfo['advteruser_id']);
            //更新监控链接
            D('Admin')->commonExecute($this->table,array('id'=>$id),array('monitor_link'=>$link));
        }*/

    }

    /**
     * 更新前置操作
     * @param string $table 操作的数据表
     */
    public function _before_update($data)
    {

        if($this->table == 'events'){
            if(I('config') == 1){
                return $data;
            }
        	if(!$data['events_name']){
                $this->error('推广活动名称不能为空');
            }

            if(!$data['agent']){
            	$this->error('请选择游戏');
            }
            
            if(D('Admin')->commonQuery('events',array('events_name'=>$data['events_name'],'id'=>array('neq',$data['id'])))){
                $this->error('推广活动名称已存在');
            }

            $data['game_id']	= D('Admin')->commonQuery('agent',array('agent'=>$data['agent']),0,1,'game_id',C('DB_PREFIX_API'))['game_id'];

            $groupInfo = getDataList('events_group','id',C('DB_PREFIX'),array('id'=> $data['events_groupId']));
            $events_groupId = $data['events_groupId'];

            $appid         = '';
            $advteruser_id = '';
            $sign_key      = '';
            $encrypt_key   = '';
            if(in_array($data['advteruser_id'],array(10,2,14))){
                $groupInfo[$events_groupId]['config_appid']         && $data['config_appid'] = $groupInfo[$events_groupId]['config_appid'];
                $groupInfo[$events_groupId]['config_advertiser_id'] && $data['config_advertiser_id'] = $groupInfo[$events_groupId]['config_advertiser_id'];
                $groupInfo[$events_groupId]['config_sign_key']      && $data['config_sign_key'] = $groupInfo[$events_groupId]['config_sign_key'];
                $groupInfo[$events_groupId]['config_encrypt_key']   && $data['config_encrypt_key'] = $groupInfo[$events_groupId]['config_encrypt_key'];
            }

            $link = $this->createLink($data['id'],$data['agent'],$data['game_id'],$data['advteruser_id']);

            $data['monitor_link'] 	= $link;
        }

        if($this->table == 'events_group'){

            if(!$data['groupName']){
                $this->error('推广活动组名称不能为空');
            }
            
            if(D('Admin')->commonQuery('events_group',array('groupName'=>$data['groupName'],'id'=>array('neq',$data['id'])))){
                $this->error('推广活动组名称已存在');
            }
        }

        return $data;
    }

    /**
     * 更新前置操作
     * @param string $table 操作的数据表
     */
    public function _after_update($id)
    {
        if($this->table == 'events_group'){
            $gconfig = I('gconfig');
            if($gconfig == 'groupConfig'){
                $group_config = D('Admin')->commonQuery('events_group',array('id'=>$id));
                //更改活动的配置
                if($group_config){
                    $data = I();
                    $update = array(
                            'config_appid'=>$data['config_appid'],
                            'config_advertiser_id'=>$data['config_advertiser_id'],
                            'config_sign_key'=>$data['config_sign_key'],
                            'config_encrypt_key'=>$data['config_encrypt_key'],
                        );
                    D('Admin')->commonExecute('events',array('events_groupId'=>$id,'advteruser_id'=>array('IN',array(10,2,14))),$update);
                }
            }

        }

    }
}