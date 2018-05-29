<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/6/5
 * Time: 15:01
 *
 * 操作日志LOG模块
 */

namespace Cy\Model;

use Think\Model;

class OperationModel extends Model
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
     * 添加操作记录日志
     * @param $data
     * @return mixed
     */
    public function addLog($data)
    {
        //判断必要数据是否完整
        if (!$data["admin_id"] || !isset($data["type"])) return false;

        return M($this->tableName, C("DB_PREFIX_ADMIN"))->add($data);
    }
}