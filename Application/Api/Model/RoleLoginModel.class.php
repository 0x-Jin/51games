<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/6/23
 * Time: 10:01
 *
 * 角色登陆模板
 */

namespace Api\Model;

use Think\Model;

class RoleLoginModel extends Model
{

    protected $tableName = "";                                                //数据表名（不包括前缀）

    /**
     * 初始化
     */
    public function __construct()
    {
        $this->tableName = "role_login";
    }

    /**
     * 添加登陆日志
     * @param $data
     * @return bool
     */
    public function addLogin($data)
    {
        //判断主要参数是否存在，否则返回错误
        if (!$data["userCode"] || !$data["agent"] || !$data["game_id"] || !$data["udid"] || !$data["roleId"]) return false;

        return M($this->tableName, C("DB_PREFIX_LOG"))->add($data);
    }
}