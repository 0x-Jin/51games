<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/12/6
 * Time: 15:46
 *
 * 版本补丁模型
 */

namespace Api\Model;

use Think\Model;

class PatchModel extends Model
{

    protected $tableName = "patch";                                                 //数据表名（不包括前缀）

    /**
     * 初始化
     */
    public function __construct()
    {
        $this->tableName = $this->getModelName();
    }

    /**
     * 获取最新的版本补丁
     * @param $type
     * @param $ver
     * @param $game_id
     * @param $channel_id
     * @param $agent
     * @param $patchVer
     * @return bool
     */
    public function getVerByGameAgent($type, $ver, $game_id, $channel_id, $agent, $patchVer)
    {
        //判断主要参数是否存在，否则返回错误
        if (!isset($type)) return false;

        if ($agent) {
            $agent_p = D("Api/Agent")->getAgent($agent);
            if ($agent_p["pid"]) {
                $agent_new  = D("Api/Agent")->getAgentById($agent_p["pid"]);
                $agent_pid  = $agent_new["agent"];
            }
        }

        $time = strtotime(date("Y-m-d H:00:00"));
        return M($this->tableName)->where("status = 0 AND (startTime < '".$time."' OR startTime = '' OR startTime IS NULL) AND (endTime > '".$time."' OR endTime = '' OR endTime IS NULL) AND type = {$type} AND ver = '{$ver}' AND patchVer > {$patchVer} AND (game_id = '{$game_id}' OR game_id IS NULL OR game_id = '') AND (channel_id = '{$channel_id}' OR channel_id IS NULL OR channel_id = '') AND (agent = '{$agent}' OR agent IS NULL OR agent = '' ".(isset($agent_pid)? "OR agent = '".$agent_pid."'": "").")")->order("patchVer ASC")->limit(1)->find();
    }
}