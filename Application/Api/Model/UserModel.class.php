<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/5/26
 * Time: 11:12
 *
 * 用户模型
 */

namespace Api\Model;

use Think\Model;

class UserModel extends Model
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
     * 注册用户
     * @param $info
     * @return bool
     */
    public function addUser($info)
    {
        //判断主要参数是否存在，否则返回错误
        if (!$info["userCode"] || !$info["userName"] || !$info["password"] || !$info["game_id"]) return false;

        return M($this->tableName)->add($info);
    }

    /**
     * 获取用户数据
     * @param $map
     * @return mixed
     */
    public function getUser($map)
    {
        return M($this->tableName)->where($map)->order("id DESC")->limit(1)->find();
    }

    /**
     * @param $userCode
     * @return bool
     */
    public function getUserByCode($userCode)
    {
        //判断主要参数是否存在，否则返回错误
        if (!$userCode) return false;

        return M($this->tableName)->where("userCode = '{$userCode}'")->find();
    }

    /**
     * 更新用户数据
     * @param $info
     * @param $userCode
     * @return bool|false|int
     */
    public function saveUser($info, $userCode)
    {
        //判断主要参数是否存在，否则返回错误
        if (!$userCode || !$info) return false;

        return M($this->tableName)->where("userCode = '{$userCode}'")->save($info);
    }

    /**
     * 获取用户统计
     * @param $map
     * @return mixed
     */
    public function getCount($map)
    {
        return M($this->tableName)->where($map)->count();
    }

    /**
     * 获取用户数据
     * @param $map
     * @return mixed
     */
    public function getUserByMap($map)
    {
        return M($this->tableName)->where($map)->select();
    }
}