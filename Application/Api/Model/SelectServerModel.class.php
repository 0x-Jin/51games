<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/9/21
 * Time: 11:45
 */

namespace Api\Model;

use Think\Model;

class SelectServerModel extends Model
{

    protected $tableName = "";                                                //数据表名（不包括前缀）

    /**
     * 初始化
     */
    public function __construct()
    {
        $this->tableName = "select_server";
    }

    /**
     * 添加选服日志
     * @param $data
     * @return bool
     */
    public function addLog($data)
    {
        //判断主要参数是否存在，否则返回错误
        if (!$data["userCode"] || !$data["regAgent"] || !$data["game_id"] || !$data["udid"] || !$data["selectServerId"]) return false;

        return M($this->tableName, C("DB_PREFIX_LOG"))->add($data);
    }
}