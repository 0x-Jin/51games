<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/5/31
 * Time: 17:32
 *
 * 渠道号模块
 */

namespace Api\Model;

use Think\Model;

class AgentModel extends Model
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
     * 通过渠道名获取渠道信息
     * @param $agent
     * @return bool
     */
    public function getAgent($agent)
    {
        //判断主要参数是否存在，否则返回错误
        if (!$agent) return false;

        return M($this->tableName)->where("agent = '{$agent}'")->find();
    }

    /**
     * 获取渠道信息
     * @return mixed
     */
    public function getAllPackageAgent()
    {
        //组装SQL
        $sql = "SELECT a.agent AS changeAgent,b.agent AS agent,a.changePackage AS changePackage,a.packagePower as power FROM lg_agent a LEFT JOIN lg_agent b ON a.pid = b.id WHERE a.pid != 0 AND a.packageStatus = 0 AND a.gameType = 1 AND a.agentType = 0";

        //获取所有的渠道号
        return M($this->tableName)->query($sql);
    }

    /**
     * 获取渠道包所属部门
     * @return mixed
     */
    public function getAgentDepartment($agent='')
    {
        $department = M($this->tableName)->alias("a")->field("b.department")->join("LEFT JOIN la_principal b ON a.principal_id = b.id")->where(array("a.agent"=>$agent))->find();
        return $department['department'];
    }

    /**
     * 更新渠道号的打包状态为开始
     * @param $map
     * @return bool
     */
    public function beginAgentPackageStatus($map)
    {
        return M($this->tableName)->where($map)->save(array("packageStatus" => "1", "lastPackageTime" => time(), "packagePower" => 0));
    }

    /**
     * 更新渠道号的打包状态为完成
     * @param $agent
     * @return bool
     */
    public function finishAgentPackageStatus($agent)
    {
        //判断渠道号是否正确
        if (!$agent) return false;

        return M($this->tableName)->where("packageStatus = 1 AND pid != 0 AND gameType = 1 AND agentType = 0 AND agent = '{$agent}'")->save(array("packageStatus" => "2"));
    }

    /**
     * 通过ID获取渠道号
     * @param $id
     * @return bool|mixed
     */
    public function getAgentById($id)
    {
        //判断主要参数是否存在，否则返回错误
        if (!$id) return false;

        return M($this->tableName)->where("id = '{$id}'")->find();
    }

    /**
     * 更新最新母包的上传时间
     * @param $map
     * @return bool
     */
    public function updateAgentNewPackageTime($map)
    {
        return M($this->tableName)->where($map)->save(array("newPackageTime" => time()));
    }
}