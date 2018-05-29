<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/5/26
 * Time: 11:12
 *
 * 用户模型
 */

namespace ThirdParty\Model;

use Think\Model;

class UserModel extends Model
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
     * 更新用户数据
     * @param $info
     * @param $userCode
     * @return bool|false|int
     */
    public function saveUser($info, $userCode)
    {
        //判断主要参数是否存在，否则返回错误
        if (!$userCode || !$info) return false;

        return M($this->tableName, "lg_")->where("userCode = '{$userCode}'")->save($info);
    }
}