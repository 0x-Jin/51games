<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/7/25
 * Time: 11:52
 *
 * 银行卡模型
 */

namespace Api\Model;

use Think\Model;

class BankCardModel extends Model
{

    protected $tableName = "";                                                //数据表名（不包括前缀）

    /**
     * 初始化
     */
    public function __construct()
    {
        $this->tableName = "bank_card";
    }

    /**
     * 通过用户唯一标识符以及卡号获取银行卡绑卡数据
     * @param $userCode
     * @param $card
     * @return bool|mixed
     */
    public function getCardByUserCard($userCode, $card)
    {
        //判断必要数据是否存在
        if (!$userCode) return false;

        return M($this->tableName)->where("userCode = '{$userCode}' AND card = '{$card}' AND status = 0")->find();
    }

    /**
     * 获取银行卡信息数据
     * @param $map
     * @return mixed
     */
    public function getCard($map)
    {
        return M($this->tableName)->where($map)->select();
    }

    /**
     * 添加银行卡号
     * @param $data
     * @return bool|mixed
     */
    public function addCard($data)
    {
        //判断必要数据是否存在
        if (!$data["userCode"] || !$data["card"] || !$data["type"] || !$data["mobile"] || !$data["name"] || !$data["IDCard"]) return false;

        //数据填充
        !$data["createTime"] && $data["createTime"] = time();
        !$data["updateTime"] && $data["updateTime"] = time();

        //添加数据
        return M($this->tableName)->add($data);
    }

    /**
     * 修改银行卡数据
     * @param $data
     * @param $map
     * @return bool
     */
    public function saveCard($data, $map)
    {
        //数据填充
        !$data["updateTime"] && $data["updateTime"] = time();

        //修改数据
        return M($this->tableName)->where($map)->save($data);
    }
}