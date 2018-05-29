<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/6/5
 * Time: 20:22
 *
 * 菜单模块
 */

namespace Cy\Model;

use Think\Model;

class MenuModel extends Model
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
     * 获取菜单
     * @param $map
     * @return mixed
     */
    public function getMenu($map)
    {
        return M($this->tableName, C("DB_PREFIX_ADMIN"))->field(C("DB_PREFIX_ADMIN")."menu.*")->join(C("DB_PREFIX_ADMIN")."manager_menu g ON g.menu_id = ".C("DB_PREFIX_ADMIN").$this->tableName.".id")->where($map)->order("`order` DESC,id ASC")->select();
    }

    /**
     * 存储菜单信息
     * @param $info
     * @param $id
     * @return bool|false|int
     */
    public function saveMenu($info, $id)
    {
        return M($this->tableName, C("DB_PREFIX_ADMIN"))->where("id = %d", $id)->save($info);
    }
}
