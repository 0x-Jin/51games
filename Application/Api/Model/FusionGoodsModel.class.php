<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/11/24
 * Time: 10:56
 *
 * 融合商品ID类
 */

namespace Api\Model;

use Think\Model;

class FusionGoodsModel extends Model
{

    protected $tableName = "";                                                //数据表名（不包括前缀）

    /**
     * 初始化
     */
    public function __construct()
    {
        $this->tableName = "fusion_goods";
    }

    /**
     * 获取融合商品ID
     * @param $agent
     * @param $goodsCode
     * @return bool|mixed
     */
    public function getFusionGoods($agent, $goodsCode)
    {
        //判断商品唯一标识符是否存在
        if (!$agent || !$goodsCode) return false;

        return M($this->tableName)->where("goodsCode = '{$goodsCode}' AND agent = '{$agent}'")->find();
    }
}