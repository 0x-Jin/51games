<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 2018/3/6
 * Time: 14:12
 *
 * 区服模块
 */

namespace Api\Model;

use Think\Model;

class ServerModel extends Model
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
     * 获取区服列表
     * @param $map
     * @return mixed
     */
    public function getList($map)
    {
        return M($this->tableName)->field("serverId,serverName")->where($map)->order("id ASC")->select();
    }
}