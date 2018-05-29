<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/11/20
 * Time: 11:20
 *
 * YSDK失败订单模型
 */

namespace Admin\Model;

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
     * 获取失败YSDK订单表数据
     * @param $map
     * @return mixed
     */
    public function getYsdkOrder($map)
    {
        return M($this->tableName, C("DB_PREFIX_API"), "CySlave")->where($map)->select();
    }

    /**
     * 修改YSDK失败订单的数据
     * @param $info
     * @param $orderId
     * @return bool
     */
    public function saveYsdkOrder($info, $orderId)
    {
        if (!$orderId) return false;

        return M($this->tableName, C("DB_PREFIX_API"))->where("orderId = '{$orderId}'")->save($info);
    }
}