<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/11/27
 * Time: 11:08
 *
 * 后台账号类
 */

namespace Api\Model;

use Think\Model;

class AdvterAccountModel extends Model
{
    protected $tableName = "";                                                //数据表名（不包括前缀）

    /**
     * 初始化
     */
    public function __construct()
    {
        $this->tableName = "advter_account";
    }

    /**
     * 获取平台账号
     * @param $id
     * @return mixed
     */
    public function getAdvterAccount($id)
    {
        return M($this->tableName, C("DB_PREFIX_ADMIN"))->alias("a")->join("LEFT JOIN la_advteruser b ON b.id = a.advteruserId")->field("a.id,a.account AS name,b.company_name AS backstage")->where("a.id IN (".($id? $id: 0).") AND a.status = 1")->order("a.advteruserId ASC, a.id ASC")->select();
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

        return M($this->tableName, C("DB_PREFIX_ADMIN"))->alias("a")->join("LEFT JOIN la_advteruser b ON b.id = a.advteruserId")->field("a.account,a.password,a.advteruserId AS backstage_id,b.url")->where("a.id = ".$id." AND a.status = 1")->find();
    }
}