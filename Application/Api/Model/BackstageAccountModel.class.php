<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/10/25
 * Time: 15:35
 *
 * 平台账号模块
 */

namespace Api\Model;

use Think\Model;

class BackstageAccountModel extends Model
{
    protected $tableName = "";                                                //数据表名（不包括前缀）

    /**
     * 初始化
     */
    public function __construct()
    {
        $this->tableName = "backstage_account";
    }

    /**
     * 获取平台账号
     * @param $id
     * @return mixed
     */
    public function getAdminAccount($id)
    {
        return M($this->tableName, C("DB_PREFIX_ADMIN"))->alias("a")->join("LEFT JOIN la_backstage b ON b.id = a.backstage_id")->field("a.id,a.name,b.backstage")->where("a.id IN (".($id? $id: 0).") AND a.status = 1")->order("a.backstage_id ASC, a.id ASC")->select();
    }

    /**
     * 获取账号信息
     * @param $id
     * @return bool|mixed
     */
    public function getAccount($id)
    {
        //判断必要数据是否存在
        if (!$id) return false;

        return M($this->tableName, C("DB_PREFIX_ADMIN"))->alias("a")->join("LEFT JOIN la_backstage b ON b.id = a.backstage_id")->field("a.account,a.password,a.backstage_id,b.url")->where("a.id = ".$id." AND a.status = 1")->find();
    }
}