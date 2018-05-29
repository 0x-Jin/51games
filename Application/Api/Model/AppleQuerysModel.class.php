<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/6/13
 * Time: 15:54
 *
 * 失败的苹果订单数据表
 */

namespace Api\Model;

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
     * 添加错误的苹果订单信息
     * @param $data
     * @return bool
     */
    public function addQuery($data)
    {
        //判断主要参数是否存在，否则返回错误
        if (!$data["receiptData"] || !$data["transactionId"] || !$data["userCode"] || !$data["game_id"] || !$data["goodsCode"]) return false;

        if ($this->getQueryByTran($data["transactionId"])) return true;

        return M($this->tableName)->add($data);
    }

    /**
     * 获取信息
     * @param $transactionId
     * @return bool|mixed
     */
    public function getQueryByTran($transactionId)
    {
        if (!$transactionId) return false;

        return M($this->tableName)->where("transactionId = '{$transactionId}'")->find();
    }
}