<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/12/22
 * Time: 10:23
 *
 * 礼包卡模块
 */

namespace Api\Model;

use Think\Model;

class GiftCardModel extends Model
{

    protected $tableName = "";                                                //数据表名（不包括前缀）

    /**
     * 初始化
     */
    public function __construct()
    {
        $this->tableName = "gift_card";
    }

    /**
     * 获取一张礼包码
     * @param $map
     * @return bool|mixed
     */
    public function getGiftCard($map)
    {
        return M($this->tableName)->where($map)->find();
    }

    /**
     * 更新一条礼包数据
     * @param $map
     * @param $info
     * @return bool
     */
    public function updateGiftCardOne($map, $info)
    {
        return M($this->tableName)->where($map)->limit(1)->save($info);
    }
}