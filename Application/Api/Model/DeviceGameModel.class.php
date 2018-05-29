<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/6/27
 * Time: 15:49
 *
 * 设备游戏表
 */

namespace Api\Model;

use Think\Model;

class DeviceGameModel extends Model
{

    protected $tableName = "";                                                //数据表名（不包括前缀）

    /**
     * 初始化
     */
    public function __construct()
    {
        $this->tableName = "device_game";
    }

    /**
     * 添加信息
     * @param $data
     * @return bool|mixed
     */
    public function addDeviceGame($data)
    {
        //判断必要数据是否存在
        if (!$data["udid"] || !$data["game_id"]) return false;
        
        $res = M($this->tableName)->add($data);

        return $res;
    }

    /**
     * 获取设备游戏信息
     * @param $udid
     * @param $game_id
     * @return mixed
     */
    public function getDeviceGame($udid, $game_id)
    {
        //判断必要数据是否存在
        if (!$udid || !$game_id) return false;

        return M($this->tableName)->where("udid = '{$udid}' AND game_id = '{$game_id}'")->find();
    }

    /**
     * 更新数据
     * @param $info
     * @param $udid
     * @param $game_id
     * @return bool
     */
    public function saveDeviceGame($info, $udid, $game_id)
    {
        //判断必要数据是否存在
        if (!$udid || !$game_id) return false;

        return M($this->tableName)->where("udid = '{$udid}' AND game_id = '{$game_id}'")->save($info);
    }
}