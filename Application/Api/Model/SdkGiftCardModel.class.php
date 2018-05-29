<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 2018/3/5
 * Time: 15:33
 *
 * SDK礼包码模块
 */

namespace Api\Model;

use Think\Model;

class SdkGiftCardModel extends Model
{

    protected $tableName = "";                                                //数据表名（不包括前缀）

    /**
     * 初始化
     */
    public function __construct()
    {
        $this->tableName = "sdk_gift_card";
    }

    /**
     * 获取SDK礼包的存量
     * @param $id
     * @return array
     */
    public function getCardStock($id)
    {
        //判断数据是否完整
        if (!$id) return false;

        $count  = M($this->tableName)->where("sdk_gift_id = ".$id)->count();
        $stock  = M($this->tableName)->where("sdk_gift_id = ".$id." AND status != 1")->count();
        return array("count" => $count, "stock" => $stock);
    }

    /**
     * 获取已经领取的礼包
     * @param $gameId
     * @param $agent
     * @param $userCode
     * @param $start
     * @return bool
     */
    public function getOwnCard($gameId, $agent, $userCode, $start)
    {
        //判断数据是否完全
        if (!$gameId || !$agent || !$userCode) return false;

        return M($this->tableName)->field("id,gift,card,FROM_UNIXTIME(receiveTime) AS time")->where("status = 1 AND userCode = '{$userCode}' AND (game_id = '{$gameId}' OR game_id IS NULL OR game_id = '') AND (agent = '{$agent}' OR agent IS NULL OR agent = '')")->order("receiveTime DESC,id DESC")->limit($start, 10)->select();
    }

    /**
     * 获取一个礼包码
     * @param $map
     * @return mixed
     */
    public function getOneCard($map)
    {
        return M($this->tableName)->where($map)->find();
    }

    /**
     * 更新一条礼包码数据
     * @param $map
     * @param $info
     * @return bool
     */
    public function updateGiftCardOne($map, $info)
    {
        return M($this->tableName)->where($map)->limit(1)->save($info);
    }
}