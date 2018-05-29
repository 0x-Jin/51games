<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/6/5
 * Time: 10:03
 *
 * 账号角色模块
 */

namespace ThirdParty\Model;

use Think\Model;

class ManagerModel extends Model
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
     * 通过用户名获取用户
     * @param $id
     * @return mixed
     */
    public function getManager($id)
    {
        //判断是否有传入用户账号
        if (!$id) return false;

        return M($this->tableName, C("DB_PREFIX_ADMIN"))->where("id = %d", $id)->find();
    }
}
