<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/12/21
 * Time: 19:41
 *
 * 游戏礼包列表模块
 */

namespace Api\Model;

use Think\Model;

class GiftModel extends Model
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
     * @param $gift_id
     * @return bool
     */
    public function getGift($gift_id)
    {
        //判断主要参数是否存在，否则返回错误
        if (!$gift_id) return false;

        return M($this->tableName)->where("id = '{$gift_id}'")->find();
    }
}