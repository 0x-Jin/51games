<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/5/20
 * Time: 16:26
 *
 *
 * 初始化日志模型
 */

namespace Api\Model;

use Think\Model;

class InitModel extends Model
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
        //判断主要参数是否存在，否则返回错误
        if (!$info["udid"] || !$info["game_id"]) return false;

        //参数填充
        !isset($info["type"]) && $info["type"] = 0;
        !isset($info["time"]) && $info["time"] = time();

        //添加数据
        return M($this->tableName, C("DB_PREFIX_LOG"))->add($info);
    }
}