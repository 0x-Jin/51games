<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/5/21
 * Time: 20:05
 *
 * 游戏模型
 */

namespace Api\Model;

use Think\Model;

class GameModel extends Model
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
     * 获取游戏信息
     * @param $game_id
     * @return bool
     */
    public function getGame($game_id)
    {
        //判断主要参数是否存在，否则返回错误
        if (!$game_id) return false;

        return M($this->tableName)->where("id = '{$game_id}'")->find();
    }

    /**
     * 获取所有游戏的名称
     * @return mixed
     */
    public function getAllName()
    {
        return M($this->tableName)->field("id,gameName")->order("id ASC")->select();
    }
}