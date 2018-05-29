<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/11/20
 * Time: 21:50
 *
 * 封号记录
 */

namespace Api\Model;

use Think\Model;

class BanUserModel extends Model
{

    protected $tableName = "";                                                //数据表名（不包括前缀）

    /**
     * 初始化
     */
    public function __construct()
    {
        $this->tableName = "ban_user";
    }

    /**
     * 进行封号
     * @param $data
     * @return mixed
     */
    public function addLog($data)
    {
        return M($this->tableName, "la_")->add($data);
    }
}