<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/6/7
 * Time: 15:51
 *
 * 公共模块
 */

namespace Api\Model;

use Think\Model;

class NoticeModel extends Model
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
     * 获取公告内容
     * @param $type
     * @param $ver
     * @param $game_id
     * @param $channel_id
     * @param $agent
     * @return bool
     */
    public function getNoticeByGameAgent($type, $ver, $game_id, $channel_id, $agent)
    {
        //判断主要参数是否存在，否则返回错误
        if (!isset($type)) return false;

        $time = strtotime(date("Y-m-d H:00:00"));
        return M($this->tableName)->where("status = 0 AND startTime < '{$time}' AND endTime > '{$time}' AND type = {$type} AND (ver >= {$ver} OR ver IS NULL OR ver = '') AND (game_id = '{$game_id}' OR game_id IS NULL OR game_id = '') AND (channel_id = '{$channel_id}' OR channel_id IS NULL OR channel_id = '') AND (agent = '{$agent}' OR agent IS NULL OR agent = '')")->order("ver ASC")->limit(1)->find();
    }
}