<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/10/12
 * Time: 15:52
 * 平台账号控制器
 */

namespace Admin\Model;

use Think\Model;

class BackstageAccountModel extends Model
{
    protected $tableName = "";                                                //数据表名（不包括前缀）

    /**
     * 初始化
     */
    public function __construct()
    {
        $this->tableName = "backstage_account";
    }

    /**
     * 获取平台账号
     * @param $map
     * @return mixed
     */
    public function getAccount($map)
    {
        return M($this->tableName,C('DB_PREFIX'),'CySlave')->alias("a")->join("LEFT JOIN la_backstage b ON b.id = a.backstage_id")->field("a.id,CONCAT(a.name, '（', b.backstage, '）', IF(a.status = 1, '', '【关闭】' )) AS backstage_name")->where($map)->order("a.backstage_id ASC, a.id ASC")->select();
    }
}