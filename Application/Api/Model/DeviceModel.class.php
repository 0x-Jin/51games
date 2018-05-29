<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/5/20
 * Time: 17:01
 *
 *
 * 设备模型
 */

namespace Api\Model;

use Think\Model;

class DeviceModel extends Model
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
     * 添加设备信息
     * @param $info
     * @return bool
     */
    public function addDevice($info)
    {
        //判断主要参数是否存在，否则返回错误
        if (!$info["udid"] || !$info["game_id"]) return false;

        //参数填充
        !isset($info["type"]) && $info["type"] = 0;
        !isset($info["createTime"]) && $info["createTime"] = time();
        !isset($info["lastInit"]) && $info["lastInit"] = time();

        //组装SQL语句
        $sql = "INSERT INTO `".C("DB_PREFIX")."{$this->getModelName()}` (`udid`,`idfa`,`idfv`,`mac`,`serial`,`agent`,`imei`,`imei2`,`systemId`,`systemInfo`,`type`,`ip`,`ver`,`game_id`,`channel_id`,`createTime`,`city`,`province`,`lastInit`) VALUE 
                ('{$info['udid']}','{$info['idfa']}','{$info['idfv']}','{$info['mac']}','{$info['serial']}','{$info['agent']}','{$info['imei']}','{$info['imei2']}','{$info['systemId']}','{$info['systemInfo']}','{$info['type']}','{$info['ip']}','{$info['ver']}','{$info['game_id']}','{$info['channel_id']}','{$info['createTime']}','{$info['city']}','{$info['province']}','{$info['lastInit']}')
                ON DUPLICATE KEY UPDATE lastInit = '{$info['lastInit']}'";

        //添加数据
        return M($this->tableName)->execute($sql);
    }

    /**
     * 记录热云报送首次打开数据
     * @param $info
     * @return bool
     */
    public function addRyOpenReport($info)
    {
        //参数填充
        !isset($info["type"]) && $info["type"] = 0;
        !isset($info["createTime"]) && $info["createTime"] = time();
        !isset($info["lastInit"]) && $info["lastInit"] = time();

        //组装SQL语句
        $sql = "INSERT INTO lgame.`ry_openreport` (`udid`,`mac`,`serial`,`agent`,`imei`,`idfa`,`idfv`,`systemId`,`systemInfo`,`type`,`ip`,`game_id`,`channel_id`,`createTime`,`lastInit`) VALUE 
                ('{$info['udid']}','{$info['mac']}','{$info['serial']}','{$info['agent']}','{$info['imei']}','{$info['idfa']}','{$info['idfv']}','{$info['systemId']}','{$info['systemInfo']}','{$info['type']}','{$info['ip']}','{$info['game_id']}','{$info['channel_id']}','{$info['createTime']}','{$info['lastInit']}')
                ON DUPLICATE KEY UPDATE lastInit = '{$info['lastInit']}'";

        //添加数据
        return M()->execute($sql);
    }

    /**
     * 记录热云报送首次注册数据
     * @param $info
     * @return bool
     */
    public function addRyRegistReport($info)
    {
        //参数填充
        !isset($info["type"]) && $info["type"] = 0;
        !isset($info["createTime"]) && $info["createTime"] = time();
        !isset($info["lastLogin"]) && $info["lastLogin"] = time();

        //组装SQL语句
        $sql = "INSERT INTO lgame.`ry_registreport` (`userCode`,`userName`,`game_id`,`idfa`,`idfv`,`channel_id`,`agent`,`ip`,`createTime`,`udid`,`device_id`,`type`,`lastIP`) VALUE 
                ('{$info['userCode']}','{$info['userName']}','{$info['game_id']}','{$info['idfa']}','{$info['idfv']}','{$info['channel_id']}','{$info['agent']}','{$info['ip']}','{$info['createTime']}','{$info['udid']}','{$info['device_id']}','{$info['type']}','{$info['lastIP']}')
                ON DUPLICATE KEY UPDATE lastLogin = '{$info['lastLogin']}'";

        //添加数据
        return M()->execute($sql);
    }

    /**
     * 通过UDID获取设备信息
     * @param $udid
     * @return bool
     */
    public function getDeviceByUdid($udid)
    {
        //判断主要参数是否存在，否则返回错误
        if (!$udid) return false;

        return M($this->tableName)->where("udid = '{$udid}'")->find();
    }

    /**
     * 通过UDID修改设备信息
     * @param $info
     * @param $udid
     * @return bool
     */
    public function saveDeviceByUdid($info, $udid)
    {
        //判断主要参数是否存在，否则返回错误
        if (!$udid) return false;

        return M($this->tableName)->where("udid = '{$udid}'")->save($info);
    }
}