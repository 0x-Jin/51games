<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/6/14
 * Time: 15:37
 *
 * 失败的苹果订单数据表
 */

namespace Cy\Model;

use Think\Model;

class AppleQuerysModel extends Model
{

    protected $tableName = "";                                                //数据表名（不包括前缀）

    /**
     * 初始化
     */
    public function __construct()
    {
        $this->tableName = "apple_querys";
    }

    /**
     * 获取失败苹果订单表数据
     * @param $map
     * @return mixed
     */
    public function getQuerysByMap($map)
    {
        return M($this->tableName, C("DB_PREFIX_API"),'CySlave')->where($map)->select();
    }

    /**
     * 修改苹果失败订单的数据
     * @param $info
     * @param $transactionId
     * @return bool
     */
    public function saveQuerys($info, $transactionId)
    {
        if (!$transactionId) return false;

        return M($this->tableName, C("DB_PREFIX_API"))->where("transactionId = '{$transactionId}'")->save($info);
    }
}