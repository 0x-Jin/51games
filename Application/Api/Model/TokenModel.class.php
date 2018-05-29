<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/5/30
 * Time: 15:45
 *
 * TOKEN模块
 */

namespace Api\Model;

use Think\Model;

class TokenModel extends Model
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
     * 更新TOKEN数据
     * @param $data
     * @return bool
     */
    public function addToken($data)
    {
        //判断用户唯一标识符、TOKEN是否存在
        if (!$data["userCode"] || !$data["loginToken"] || !$data["secretToken"]) return false;

        //参数填充
        !isset($data["loginTime"]) && $data["loginTime"] = time();

        //组装SQL语句
        $sql = "INSERT INTO `".C("DB_PREFIX")."{$this->getModelName()}` (`userCode`,`loginToken`,`secretToken`,`loginTime`) VALUE 
                ('{$data['userCode']}','{$data['loginToken']}','{$data['secretToken']}','{$data['loginTime']}')
                ON DUPLICATE KEY UPDATE `loginToken` = '{$data['loginToken']}',`secretToken` = '{$data['secretToken']}',`loginTime` = '{$data['loginTime']}'";

        //添加数据
        return M($this->tableName)->execute($sql);
    }

    /**
     * 获取用户TOKEN
     * @param $userCode
     * @return bool
     */
    public function getToken($userCode)
    {
        //判断用户唯一标识符是否存在
        if (!$userCode) return false;

        return M($this->tableName)->where("userCode = '{$userCode}'")->find();
    }
}