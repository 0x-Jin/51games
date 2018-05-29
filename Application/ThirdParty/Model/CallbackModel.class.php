<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/6/19
 * Time: 19:23
 *
 * 回调模型
 */

namespace ThirdParty\Model;

use Think\Model;

class CallbackModel extends Model
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
     * 获取回调信息
     * @param $orderId
     * @return bool
     */
    public function getCallbackOneByOrderId($orderId)
    {
        if (!$orderId) return false;

        return M($this->tableName, C("DB_PREFIX_LOG"))->where("orderId = '{$orderId}'")->order("id DESC")->find();
    }
}