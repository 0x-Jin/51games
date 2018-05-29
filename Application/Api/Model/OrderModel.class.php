<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/6/1
 * Time: 10:25
 *
 * 订单模块
 */

namespace Api\Model;

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
     * 添加订单数据
     * @param $data
     * @return bool|mixed
     */
    public function addOrder($data)
    {
        //判断手机号、验证码是否存在
        if (!$data["orderId"] || !$data["billNo"] || !$data["userCode"] || !$data["agent"] || !$data["game_id"] || !$data["udid"] || !$data["goodsCode"]) return false;

        //参数填充
        !isset($data["createTime"]) && $data["createTime"] = time();

        return M($this->tableName)->add($data);
    }

    /**
     * 更新订单信息
     * @param $info
     * @param $orderId
     * @return bool|false|int
     */
    public function saveOrder($info, $orderId)
    {
        if (!$orderId) return false;

        return M($this->tableName)->where("orderId = '{$orderId}'")->save($info);
    }

    /**
     * 获取订单数据
     * @param $map
     * @param $length
     * @param $field
     * @param $order
     * @return mixed
     */
    public function getOrder($map, $length, $field = '*', $order = "id DESC")
    {
        return M($this->tableName)->field($field)->where($map)->order($order)->limit($length)->select();
    }

    /**
     * 获取订单信息
     * @param $orderId
     * @return mixed
     */
    public function getOrderById($orderId)
    {
        //判断订单号是否存在
        if (!$orderId) return false;

        return M($this->tableName)->where("orderId = '{$orderId}'")->find();
    }

    /**
     * 获取订单
     * @param $map
     * @return array|false|mixed|\PDOStatement|string|Model
     */
    public function getOrderByMap($map)
    {
        return M($this->tableName)->where($map)->order("id DESC")->limit(1)->find();
    }

    /**
     * 获取订单数据的总数
     * @param $map
     * @return mixed
     */
    public function getCountByMap($map)
    {
        return M($this->tableName)->where($map)->count();
    }

    /**
     * 获取订单的充值总额
     * @param $map
     * @return mixed
     */
    public function getSumAmount($map)
    {
        return M($this->tableName)->where($map)->sum("amount");
    }
}