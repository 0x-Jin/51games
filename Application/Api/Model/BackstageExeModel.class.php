<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/10/30
 * Time: 17:47
 */

namespace Api\Model;

use Think\Model;

class BackstageExeModel extends Model
{
    protected $tableName = "";                                                //数据表名（不包括前缀）

    /**
     * 初始化
     */
    public function __construct()
    {
        $this->tableName = "backstage_exe";
    }

    /**
     * 获取版本信息
     * @return bool|mixed
     */
    public function getVer()
    {
        return M($this->tableName, C("DB_PREFIX_ADMIN"))->order("ver DESC,id DESC")->find();
    }
}