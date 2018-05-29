<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/6/27
 * Time: 11:17
 *
 * 角色信息模板
 */

namespace Admin\Model;

use Think\Model;

class RoleModel extends Model
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
     * 获取角色信息
     * @param $map
     * @param $first
     * @param $size
     * @return mixed
     */
    public function getRole($map, $first, $size)
    {
        return M($this->tableName, C("DB_PREFIX_API"),'CySlave')->alias("a")->field('a.*,b.gameName,c.userName')->join(array("lg_game b ON b.id = a.game_id", "lg_user c ON c.userCode = a.userCode"), "left")->where($map)->order("a.id DESC")->limit($first, $size)->select();
    }

    /**
     * 获取角色信息总和
     * @param $map
     * @return mixed
     */
    public function getRoleCount($map)
    {
        return M($this->tableName, C("DB_PREFIX_API"),'CySlave')->alias("a FORCE INDEX(createTime) ")->join("lg_user c ON c.userCode = a.userCode", "left")->where($map)->count();
    }
}