<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/5/31
 * Time: 20:47
 *
 * 商品ID模板
 */

namespace Api\Model;

use Think\Model;

class GoodsModel extends Model
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
     * 获取商品信息
     * @param $goodsCode
     * @return bool
     */
    public function getGoods($goodsCode)
    {
        //判断商品唯一标识符是否存在
        if (!$goodsCode) return false;

        return M($this->tableName)->where("goodsCode = '{$goodsCode}'")->find();
    }
}