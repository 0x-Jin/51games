<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 2018/1/23
 * Time: 10:47
 *
 * 快速登陆账号类
 */

namespace Api\Model;

use Think\Model;

class FastLoginModel extends Model
{

    protected $tableName = "";                                                //数据表名（不包括前缀）

    /**
     * 初始化
     */
    public function __construct()
    {
        $this->tableName = "fast_login";
    }

    /**
     * 获取快速登陆的用户ID
     * @param $udid
     * @param $agent
     * @return bool|mixed
     */
    public function getFastLogin($udid, $agent)
    {
        //判断必要参数是否存在
        if (!$udid || !$agent) return false;

        return M($this->tableName)->where("udid = '{$udid}' AND agent = '{$agent}'")->find();
    }

    /**
     * 新增快速登陆的信息
     * @param $udid
     * @param $agent
     * @param $userCode
     * @return bool|mixed
     */
    public function addFastLogin($udid, $agent, $userCode)
    {
        //判断必要参数是否存在
        if (!$udid || !$agent || !$userCode) return false;

        return M($this->tableName)->add(array("udid" => $udid, "agent" => $agent, "userCode" => $userCode));
    }
}