<?php
/**
 * Created by sublime.
 * User: XSM
 * Date: 2017/10/09
 * Time: 17:27
 *
 * IOS推广数据模型
 */

namespace Cy\Model;

use Think\Model;


class IOSModel extends Model
{
    protected $autoCheckFields = false; //关闭自动检测数据库字段

    public function __construct(){
        parent::__construct();
    }

    /**
     * 获取IOS推广活动列表
     * @AuthorHTL
     * @DateTime  2017-10-09T17:03:11+0800
     * @param     array                    $map      [description]
     * @param     integer                  $start    [description]
     * @param     integer                  $pageSize [description]
     * @return    [type]                             [description]
     */
    public function getEvents($map=array(),$start=0,$pageSize=30)
    {
    	$mod   = M('events',C('DB_PREFIX'),'CySlave');
    	$count = $mod->where($map)->count(); 
    	$list  = $mod->where($map)->limit($start,$pageSize)->order('id DESC')->select();
    	return array('list'=>$list,'count'=>$count);
    }

    public function getEventsGroup($map=array(),$start=0,$pageSize=30)
    {
        $mod = M('events_group',C('DB_PREFIX'),'CySlave');
        $count = $mod->where($map)->count();
        $list  = $mod->where($map)->limit($start,$pageSize)->order('id DESC')->select();
        return array('list'=>$list,'count'=>$count);
    }
}