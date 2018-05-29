<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 2018/1/26
 * Time: 15:23
 *
 * 母包设备类
 */

namespace Api\Model;

use Think\Model;

class DeviceAgentModel extends Model
{
    protected $tableName = "";                                                //数据表名（不包括前缀）

    /**
     * 初始化
     */
    public function __construct()
    {
        $this->tableName = "device_agent";
    }

    /**
     * 添加信息
     * @param $data
     * @return bool|mixed
     */
    public function addDeviceAgent($data)
    {
        // 判断必要数据是否存在
        if (! $data["udid"] || ! $data["agent"])
            return false;
        
        $register = false;
        
        // 判断是不是白名单
        $white = whiteList($data['imei'], $data['idfa']);
        if ($white) {
            // 判断是否已经存在设备
            $register = $this->getDeviceAgent($data["udid"], $data["agent"]);
        }
        
        if (! $register) {
            $res = M($this->tableName)->add($data);
        }
        
        if ($res != false || $white) {
            $reportData = $data;
            $reportData['agent'] = $data['regAgent'];
            D('Api/ANDMatch')->activeReport($reportData, 1); // 激活报送
        }
        
        return $res;
    }

    /**
     * 获取设备游戏信息
     * @param $udid
     * @param $agent
     * @return mixed
     */
    public function getDeviceAgent($udid, $agent)
    {
        //判断必要数据是否存在
        if (!$udid || !$agent) return false;

        return M($this->tableName)->where("udid = '{$udid}' AND agent = '{$agent}'")->find();
    }
}