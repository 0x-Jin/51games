<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/6/14
 * Time: 10:12
 *
 * 订单模块
 */

namespace Cy\Model;

use Think\Model;

class OrderModel extends Model
{
    protected $tableName = "";                                                //数据表名（不包括前缀）

    /**
     * 初始化
     */
    public function __construct()
    {
        $this->tableName = $this->getModelName();
    }

    /**
     * 获取订单表数据
     * @param $map
     * @return mixed
     */
    public function getOrderByMap($map)
    {
        return M($this->tableName, C("DB_PREFIX_API"),'CySlave')->where($map)->select();
    }

    /**
     * 获取订单数据
     * @param $map
     * @param $start
     * @param $length
     * @return mixed
     */
    public function getOrderLimit($map, $start, $length)
    {
        $export = I('export',0,'intval');
        if($export == 1){
            ini_set('memory_limit','1024M'); 
            return M($this->tableName, C("DB_PREFIX_API"),'CySlave')->alias('FORCE INDEX(createTime)')->where($map)->order("id DESC")->select();
        }else{
            return M($this->tableName, C("DB_PREFIX_API"),'CySlave')->alias('FORCE INDEX(createTime)')->where($map)->order("id DESC")->limit($start, $length)->select();
        }
    }

    /**
     * 获取财务订单数据
     * @param $map
     * @param $start
     * @param $length
     * @param $tableIndex 索引
     * @return mixed
     */
    public function getIncomeOrderLimit($map, $start, $length, $tableIndex = '')
    {
        $index = !empty($tableIndex) ? 'FORCE INDEX('.$tableIndex.')' : '';
        return M($this->tableName, C("DB_PREFIX_API"),'CySlave')->alias('a '.$index)->field('a.*,SUM(a.amount) AS amount,FROM_UNIXTIME(a.createTime,"%Y-%m-%d") AS payTime,b.pid')->join('LEFT JOIN lg_agent b ON a.agent=b.agent')->where($map)->group('a.payType,pid,a.type,a.agent,payTime')->select();
        /*$export = I('export',0,'intval');
        if($export == 1){
            
        }else{
            return M($this->tableName, C("DB_PREFIX_API"),'CySlave')->alias('a')->field('a.*,SUM(a.amount) AS amount,b.pid')->join('LEFT JOIN lg_agent b ON a.agent=b.agent')->where($map)->group('a.payType,pid,a.type,a.agent')->limit($start, $length)->select();
        }*/
    }

    /**
     * 获取订单统计
     * @param $map
     * @return mixed
     */
    public function getCount($map)
    {
        return M($this->tableName, C("DB_PREFIX_API"),'CySlave')->alias('FORCE INDEX(createTime)')->where($map)->count();
    }

    /**
     * 获取财务订单统计
     * @param $map
     * @return mixed
     */
    public function getIncomeCount($map)
    {
        $res = M($this->tableName, C("DB_PREFIX_API"),'CySlave')->alias('a')->field('a.*,SUM(a.amount) AS amount,b.pid')->join('LEFT JOIN lg_agent b ON a.agent=b.agent')->where($map)->group('a.payType,pid,a.type,a.agent')->select();
        return count($res);
    }

    /**
     * 获取财务订单金额汇总
     * @param $map
     * @return mixed
     */
    public function getIncomeSum($map)
    {
        return M($this->tableName, C("DB_PREFIX_API"),'CySlave')->alias('a')->where($map)->sum('amount');
    }

    /**
     * 获取订单金额汇总
     * @param $map
     * @return mixed
     */
    public function getSum($map)
    {
        return M($this->tableName, C("DB_PREFIX_API"),'CySlave')->alias('FORCE INDEX(createTime)')->where($map)->sum('amount');
    }

    /**
     * 获取订单详情
     * @param $orderId
     * @return bool|mixed
     */
    public function getOrder($orderId)
    {
        if (!$orderId) return false;

        return M($this->tableName, C("DB_PREFIX_API"),'CySlave')->where("orderId = '{$orderId}'")->find();
    }
}