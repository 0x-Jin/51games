<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/6/9
 * Time: 11:07
 *
 * 回调游戏的日志LOG模块
 */

namespace Api\Model;

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
     * 添加回调日志LOG
     * @param $data
     * @return bool|mixed
     */
    public function addLog($data)
    {
        //判断必要数据是否传递
        if (!$data["game_id"] || !$data["userCode"] || !$data["orderId"]) return false;

        return M($this->tableName, C("DB_PREFIX_LOG"))->add($data);
    }
}