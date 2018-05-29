<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/6/19
 * Time: 16:25
 *
 * 渠道模型
 */

namespace Api\Model;

use Think\Model;

class ChannelModel extends Model
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
     * @param $id
     * @return bool
     */
    public function getChannel($id)
    {
        return M($this->tableName)->where("id = '{$id}'")->find();
    }
}