<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/11/17
 * Time: 9:56
 *
 * YSDK失败订单类
 */

namespace Api\Model;

use Think\Model;

class YsdkPayFailOrderModel extends Model
{

    protected $tableName = "";                                                //数据表名（不包括前缀）

    /**
     * 初始化
     */
    public function __construct()
    {
        $this->tableName = "ysdk_pay_fail_order";
    }

    /**
     * 添加YSDK的失败订单记录
     * @param $data
     * @return bool
     */
    public function addOrder($data)
    {
        //判断主要参数是否存在，否则返回错误
        if (!$data["orderId"] || !$data["openId"] || !$data["payToken"] || !$data["accessToken"] || !$data["platform"] || !$data["pf"] || !$data["pfKey"]) return false;

        $data["createTime"] = $data["updateTime"] = time();

        return M($this->tableName)->add($data);
    }

    /**
     * 更新YSDK的失败订单记录
     * @param $map
     * @param $data
     * @return bool
     */
    public function updateOrder($map, $data)
    {
        return M($this->tableName)->where($map)->save($data);
    }
}