<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/6/23
 * Time: 14:12
 *
 * 用户游戏模板
 */

namespace Api\Model;

use Think\Model;

class UserGameModel extends Model
{

    protected $tableName = "";                                                //数据表名（不包括前缀）

    /**
     * 初始化
     */
    public function __construct()
    {
        $this->tableName = "user_game";
    }

    /**
     * 添加用户游戏数据
     * @param $data
     * @return bool|false|int
     */
    public function addUserGame($data)
    {
        //判断主要参数是否存在，否则返回错误
        if (!$data["userCode"] || !$data["agent"] || !$data["game_id"] || !isset($data["channel_id"]) || !$data["userName"]) return false;

        //参数填充
        !isset($data["createTime"]) && $data["createTime"] = time();
        !isset($data["lastLogin"]) && $data["lastLogin"] = time();

        //组装SQL语句
        $sql = "INSERT INTO `".C("DB_PREFIX")."{$this->tableName}` (`userCode`,`userName`,`agent`,`udid`,`imei`,`imei2`,`idfa`,`game_id`,`channel_id`,`ip`,`city`,`province`,`createTime`,`device_id`,`type`,`ver`,`lastIP`,`lastLogin`,`lastPay`,`lastGameId`,`lastAgent`) VALUE 
                ('{$data['userCode']}','{$data['userName']}','{$data['agent']}','{$data['udid']}','{$data['imei']}','{$data['imei2']}','{$data['idfa']}','{$data['game_id']}','{$data['channel_id']}','{$data['ip']}','{$data['city']}','{$data['province']}','{$data['createTime']}','{$data['device_id']}','{$data['type']}','{$data['ver']}','{$data['lastIP']}','{$data['lastLogin']}','{$data['lastPay']}','{$data['lastGameId']}','{$data['lastAgent']}')
                ON DUPLICATE KEY UPDATE `lastIP` = '{$data['lastIP']}',`lastLogin` = '{$data['lastLogin']}',`lastGameId` = '{$data['lastGameId']}',`lastAgent` = '{$data['lastAgent']}'";

        //添加数据
        return M($this->tableName)->execute($sql);
    }

    /**
     * 更新用户游戏数据
     * @param $info
     * @param $userCode
     * @param $game_id
     * @return bool
     */
    public function saveUserGame($info, $userCode, $game_id)
    {
        //判断主要参数是否存在，否则返回错误
        if (!$userCode || !$game_id) return false;

        return M($this->tableName)->where("userCode = '{$userCode}' AND game_id = '{$game_id}'")->save($info);
    }

    /**
     * 获取用户游戏信息
     * @param array $map 搜索条件
     * @return array
     */
    public function getUserInfo($map = array())
    {
        //搜素条件是否为空，是则返回错误
        if (empty($map)) return false;

        return M($this->tableName)->where($map)->find();
    }

    /**
     * 记录首次充值时间
     * @param $userCode
     * @param $game_id
     * @param $time
     * @return bool
     */
    public function saveFirstPay($userCode, $game_id, $time)
    {
        //判断主要参数是否存在，否则返回错误
        if (!$userCode || !$game_id) return false;
        !$time && $time = time();
        $map    = "userCode = '{$userCode}' AND game_id = '{$game_id}'";
        $user   = M($this->tableName)->where($map)->find();
        //如果存在最后充值时间，则不记录首充
        if (!$user || $user["lastPay"]) return true;
        //记录首次充值时间
        return M($this->tableName)->where($map)->save(array("firstPay" => $time));
    }
}