<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 2018/3/5
 * Time: 15:08
 *
 * SDK礼包模块
 */

namespace Api\Model;

use Think\Model;

class SdkGiftModel extends Model
{

    protected $tableName = "";                                                //数据表名（不包括前缀）

    /**
     * 初始化
     */
    public function __construct()
    {
        $this->tableName = "sdk_gift";
    }

    /**
     * 获取礼包列表（10条）
     * @param $gameId
     * @param $agent
     * @param $start
     * @return bool
     */
    public function getList($gameId, $agent, $start)
    {
        //判断数据是否完全
        if (!$gameId || !$agent) return false;

        $time = time();
        return M($this->tableName)->field("id,gift,content")->where("status = 0 AND (startTime < {$time} OR startTime = '' OR startTime IS NULL) AND (endTime > {$time} OR endTime = '' OR endTime IS NULL) AND (game_id = '{$gameId}' OR game_id IS NULL OR game_id = '') AND (agent = '{$agent}' OR agent IS NULL OR agent = '')")->order("id DESC")->limit($start, 10)->select();
    }

    /**
     * 获取礼包信息
     * @param $id
     * @return bool|mixed
     */
    public function getInfo($id)
    {
        //判断必要数据是否存在
        if (!$id) return false;

        return M($this->tableName)->where("id = '{$id}'")->find();
    }
}