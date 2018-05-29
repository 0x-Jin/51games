<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/10/25
 * Time: 11:36
 *
 * 后台账号模块
 */

namespace Api\Model;

use Think\Model;

class AdminModel extends Model
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
     * 获取后台用户信息
     * @param $name
     * @return bool|mixed
     */
    public function getAdmin($name)
    {
        //判断必要数据是否存在
        if (!$name) return false;

        return M($this->tableName, C("DB_PREFIX_ADMIN"))->where(array("name" => $name, "status" => 0))->find();
    }

    /**
     * 更新用户的session
     * @param $name
     * @param string $session
     * @param int $time
     * @return bool
     */
    public function saveSession($name, $session = "", $time = 0)
    {
        //判断必要数据是否存在
        if (!$name) return false;
        if (!$session) $session = md5($name.time().uniqid());
        if (!$time) $time = time();

        return M($this->tableName, C("DB_PREFIX_ADMIN"))->where(array("name" => $name))->save(array("backstageSession" => $session, "backstageSessionTime" => $time));
    }

    /**
     * 通过session寻找用户
     * @param $session
     * @return bool|mixed
     */
    public function getAdminBySession($session)
    {
        //判断必要数据是否存在
        if (!$session) return false;

        return M($this->tableName, C("DB_PREFIX_ADMIN"))->where(array("backstageSession" => $session, "status" => 0))->find();
    }
}