<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 2018/4/17
 * Time: 16:59
 *
 * 操作日志模型
 */

namespace Api\Model;

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
     * 添加Log日志
     * @param $info
     * @return bool
     */
    public function addLog($info)
    {
        if (!$info["time"]) $info["time"] = time();

        //添加数据
        return M($this->tableName, C("DB_PREFIX_LOG"))->add($info);
    }
}