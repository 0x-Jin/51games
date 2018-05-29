<?php
/**
 * Created by sublime.
 * User: XSM
 * Date: 2017/9/04
 * Time: 14:27
 *
 * AJAX请求获取数据模型
 */

namespace Api\Model;

use Think\Model;

class AjaxModel extends Model
{
    protected $autoCheckFields = false; //关闭自动检测数据库字段

	/**
	 * 获取区服信息
	 * @param $game_id 游戏ID
	 * @param $agent 母包渠道号
	 * @return array
	 */
	public function getServers($game_id,$agent)
	{
		if(empty($game_id) || empty($agent)) return false;
		$mod = M('server');
		$map = array('game_id'=>$game_id,'agent'=>$agent);
		$list = $mod->where($map)->field('serverType,serverName,openTime,serverId')->select();
		if($list){
			$ios = $android = $new = array();
			foreach ($list as $key => $value) {
				$new[] = $value;
				if($value['serverType'] == 1){ //IOS
					$ios[] = $value;
				}elseif($value['serverType'] == 2){ //ANDROID
					$android[] = $value;
				}
			}
			return array('ios'=>$ios,'android'=>$android,'new'=>$new);
		}else{
			return false;
		}
	}

	/**
	 * 获取官网最新公告
	 * @param $home_id 官网ID
	 * @return array
	 */
	public function getNewGongGao($home_id)
	{
		if(empty($home_id)) return false;
		$mod = M('article',C('DB_PREFIX_WEBSITE'),'WEBSITE');
		$map = array('home_id'=>$home_id,'column_id'=>3);
		$list = $mod->where($map)->order('id DESC')->limit(1)->select();
		if($list){
			return $list;
		}else{
			return false;
		}
	}

	/**
	 * 获取官网下载链接
	 * @param $abbr 官网缩写
	 * @return array
	 */
	public function getLink($abbr)
	{
		if(!$abbr) return false;
		$mod = M('home',C('DB_PREFIX_WEBSITE'),'WEBSITE');
		$map = array('abbr'=>$abbr);
		$res = $mod->field('androidDownload,iosDownload')->where($map)->find();
		return $res;
	}

	/**
	 * 获取短链接下载链接
	 * @param $abbr 官网缩写
	 * @return array
	 */
	public function getShortLink($id)
	{
		if(!$id) return false;
		$mod = M('short_link',C('DB_PREFIX_ADMIN'),'CySlave');
		$map = array('id'=>$id);
		$res = $mod->field('iosLink,andLink')->where($map)->find();
		return $res;
	}

	/**
	 * 获取乱世英雄安卓包数据
	 * @param $date 日期
	 * @return array
	 */
	public function getLsyxAndData($date)
	{
		$agent = array(
			'lsyxzjAND651',
			'lsyxzjAND715',
			'lsyxzjAND713',
			'lsyxzjAND649',
			'lsyxzjAND650',
			'lsyxzjAND842',
			'lsyxzjAND843',
			'lsyxzjAND844',
			'lsyxzjAND845',
			'lsyxzjAND846',
			'lsyxzjAND652',
			'lsyxzjAND653',
			'lsyxzjAND711',
			'lsyxzjAND712',
			'lsyxzjAND714',
			'lsyxzjAND847',
			'lsyxzjAND848',
			'lsyxzjAND849',
			'lsyxzjAND850',
			'lsyxzjAND851'
		);

		$map['agent'] = array(
						'IN',
						$agent
						);
		$map['dayTime'] = $date;

		$deviceModel = M('sp_device_day',C('DB_PREFIX_ADMIN'),'CySlave');
		$device = field_to_key($deviceModel->field('agent,SUM(newDevice) AS newDevice,SUM(disUdid) AS disUdid')->where($map)->group('agent')->select(),'agent'); //设备数

		$userMode = M('sp_user_game_day',C('DB_PREFIX_ADMIN'),'CySlave');
		$user = field_to_key($userMode->field('agent,SUM(newUser) AS newUser,SUM(newUserLogin+oldUserLogin) AS actUser')->where($map)->group('agent')->select(),'agent'); //用户数

		$payMode = M('sp_agent_server_pay_day',C('DB_PREFIX_ADMIN'),'CySlave');
		$pay = field_to_key($payMode->field('agent,SUM(newPayUser) AS newPayUser,SUM(newPay) AS newPay,SUM(allPayUser) AS allPayUser,SUM(allPay) AS allPay')->where($map)->group('agent')->select(),'agent'); //充值数

		$info = array();
		foreach ($agent as $value) {
			$info[$value] = array(
				'date'       => date('Ymd',strtotime($date)),
				'game'       => '乱世英雄战纪',
				'syst'       => 0,
				'uniqMark'   => $value.'.apk',
				'newDevice'  => !$device[$value]['newDevice'] ? 0 : $device[$value]['newDevice'],
				'disUdid'    => !$device[$value]['disUdid']   ? 0 : $device[$value]['disUdid'],
				'newUser'    => !$user[$value]['newUser']     ? 0 : $user[$value]['newUser'],
				'actUser'    => !$user[$value]['actUser']     ? 0 : $user[$value]['actUser'],
				'newPayUser' => !$pay[$value]['newPayUser']   ? 0 : $pay[$value]['newPayUser'],
				'newPay'     => !$pay[$value]['newPay']       ? 0 : $pay[$value]['newPay'],
				'allPayUser' => !$pay[$value]['allPayUser']   ? 0 : $pay[$value]['allPayUser'],
				'allPay'     => !$pay[$value]['allPay']       ? 0 : $pay[$value]['allPay'],

			);
		}
		sort($info);
		return $info;
	}
}