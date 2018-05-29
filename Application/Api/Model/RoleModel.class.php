<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/6/1
 * Time: 11:34
 *
 * 角色模块
 */

namespace Api\Model;

use Think\Model;

class RoleModel extends Model
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
     * 更新角色信息
     * @param $data
     * @return bool
     */
    public function addRole($data)
    {
        //判断必要数据是否存在
        if (!$data["userCode"] || !$data["game_id"] || !$data["agent"] || !$data["udid"] || !$data["roleId"]) return false;

        //参数填充
        !isset($data["createTime"]) && $data["createTime"] = time();
        !isset($data["updateTime"]) && $data["updateTime"] = time();

        //组装SQL语句
        $sql = "INSERT INTO `".C("DB_PREFIX")."{$this->tableName}` (`userCode`,`agent`,`udid`,`game_id`,`roleId`,`roleName`,`serverId`,`serverName`,`level`,`currency`,`vip`,`balance`,`power`,`processId`,`scene`,`createTime`,`updateTime`) VALUE 
                ('{$data['userCode']}','{$data['agent']}','{$data['udid']}','{$data['game_id']}','{$data['roleId']}','{$data['roleName']}','{$data['serverId']}','{$data['serverName']}','{$data['level']}','{$data['currency']}','{$data['vip']}','{$data['balance']}','{$data['power']}','{$data['processId']}','{$data['scene']}','{$data['createTime']}','{$data['updateTime']}')
                ON DUPLICATE KEY UPDATE `roleName` = '{$data['roleName']}',`level` = '{$data['level']}',`serverId` = '{$data['serverId']}',`serverName` = '{$data['serverName']}',`currency` = '{$data['currency']}',`vip` = '{$data['vip']}',`balance` = '{$data['balance']}',`power` = '{$data['power']}',`processId` = '{$data['processId']}',`scene` = '{$data['scene']}',`updateTime` = '{$data['updateTime']}'";

        //添加数据
        return M($this->tableName)->execute($sql);
    }

    /**
     * 获取角色信息
     * @param $map
     * @return mixed
     */
    public function getRole($map)
    {
        return M($this->tableName)->where($map)->find();
    }

    /**
     * 获取角色信息列表
     * @param $map
     * @return mixed
     */
    public function getList($map)
    {
        return M($this->tableName)->field("roleId,roleName")->where($map)->select();
    }

    /**
     * 记录角色首次充值时间
     * @param $game_id
     * @param $userCode
     * @param $roleId
     * @param $time
     * @return bool
     */
    public function saveFirstPay($game_id, $userCode, $roleId, $time)
    {
        //判断主要参数是否存在，否则返回错误
        if (!$userCode || !$game_id || !$roleId) return false;
        !$time && $time = time();
        $map    = "userCode = '{$userCode}' AND game_id = '{$game_id}' AND roleId = '{$roleId}'";
        $role   = M($this->tableName)->where($map)->find();
        //如果存在首次充值时间，则不记录首充
        if (!$role || $role["firstPay"]) return true;
        //记录首次充值时间
        return M($this->tableName)->where($map)->save(array("firstPay" => $time));
    }
}
