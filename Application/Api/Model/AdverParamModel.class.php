<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 2018/4/13
 * Time: 10:16
 *
 * 投放参数
 */

namespace Api\Model;

use Think\Model;

class AdverParamModel extends Model
{
    protected $tableName = "";                                                //数据表名（不包括前缀）

    /**
     * 初始化
     */
    public function __construct()
    {
        $this->tableName = "adver_param";
    }

    /**
     * 获取参数配置
     * @param $agent
     * @return mixed
     */
    public function getAdverParam($agent)
    {
        if (!$agent) return array();
        $param  = M($this->tableName)->where(array("agent" => $agent, "status" => 0))->select();
        if (!$param) return array();
        $list   = array();
        foreach ($param as $value) {
            $arr    = array("id" => $value["advteruser_id"]);
            for ($i = 1; $i <= 10; $i ++) {
                if ($value["param".$i]) $arr[$value["param".$i]] = $value["value".$i];
            }
            $list[] = $arr;
        }
        return $list;
    }
}