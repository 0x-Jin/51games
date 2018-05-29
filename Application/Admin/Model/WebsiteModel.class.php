<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/8/16
 * Time: 16:59
 *
 * 后台模块
 */

namespace Admin\Model;

use Think\Model;

class WebsiteModel extends Model
{

    private $prefix = "";

    public function __construct()
    {
        $this->prefix = C("DB_PREFIX_WEBSITE");
    }

    /**
     * @param string $table 表名，不需要前缀
     * @param array $map 搜索条件
     * @param int $page 查询页码
     * @param int $offset 查询条数
     * @param string $field 表字段
     * @param string $prefix
     * @return bool|mixed 查询结果
     */
    public function commonQuery($table, $map = array(), $page = 0, $offset = 1, $field = "*", $prefix = "", $order = "")
    {
        //前缀
        $prefix = $prefix? $prefix: $this->prefix;

        if ($offset === 1) {
            if ($field == "*") {
                $res = M($table, $prefix, "WEBSITE")->where($map)->find();
            } else {
                $res = M($table, $prefix, "WEBSITE")->field($field)->where($map)->find();
            }
        } else {
            if ($field == "*") {
                $res = M($table, $prefix, "WEBSITE")->where($map)->order($order)->limit($page, $offset)->select();
            } else {
                $res = M($table, $prefix, "WEBSITE")->field($field)->where($map)->order($order)->limit($page, $offset)->select();
            }
        }
        if (!$res) {
            return false;
        }
        return $res;
    }

    /**
     * @param string $table 表名，不需要前缀
     * @param array $map 搜索条件
     * @param string $prefix
     * @return mixed 查询结果
     */
    public function commonCount($table, $map = array(), $prefix = "")
    {
        //前缀
        $prefix = $prefix? $prefix: $this->prefix;

        return M($table, $prefix, "WEBSITE")->where($map)->count();
    }

    /**
     * 后台公用数据添加
     * @param string $table 表名，不需要前缀
     * @param array $data 添加的数据
     * @param string $prefix
     * @return bool|mixed 返回受影响条数
     */
    public function commonAdd($table, $data = array(), $prefix = "")
    {
        //前缀
        $prefix = $prefix? $prefix: $this->prefix;

        $res = false;
        if ($table && count($data) > 0) {
            $mod = M($table, $prefix, "WEBSITE");
            $res = $mod->add($data);
            bgLog(4, $mod->getTableName()."  添加ID".$res." ".$mod->_sql());
        }
        return $res;
    }

    /**
     * 后台公用数据更新
     * @param string $table 表名，不需要前缀
     * @param array $map 更新条件
     * @param array $data 更新的数据
     * @param string $prefix
     * @return bool 返回受影响条数
     */
    public function commonExecute($table, $map = array(), $data = array(), $prefix = "")
    {
        //前缀
        $prefix = $prefix? $prefix: $this->prefix;

        $res    = false;
        $mod    = M($table, $prefix, "WEBSITE");
        $pk     = $mod->getPk();
        $update = array();
        $fields = $mod->getDbFields();
        foreach ($fields as $k => $v) {
            isset($data[$v]) && $update[$v] = $data[$v];
        }

        if (count($update) > 0 && count($map) > 0) {
            $res = $mod->where($map)->save($update);
            bgLog(3, $mod->getTableName()."  修改ID:".$map[$pk]." ".$mod->_sql());
        }

        return $res;
    }

    /**
     * 后台公用数据删除
     *
     * @param string  $table 表名，不需要前缀
     * @param Array $map   删除条件
     * @return int 返回受影响条数
     */
    public function commonDelete($table, $map = array(), $prefix='')
    {
        //前缀
        $prefix = $prefix? $prefix: $this->prefix;
        
        $res = false;
        if ($table &&  count($map) > 0) {
            $mod = M($table,$prefix, "WEBSITE");
            $res = $mod->where($map)->delete();
            bgLog(2,$mod->getTableName()."  删除ID:".implode(',', $ids).$mod->_sql());
        }
        return $res;
    }

    /**
     * 获取咨询上下文章
     *
     * @param int  $home_id 官网ID
     * @param int  $column_id 栏目ID
     * @param int  $id 所选文章的ID
     * @param int  $link 文章的链接
     * @return int 文章信息
     */
    public function getArticle($home_id = 0, $column_id = 0, $id = 0, $link = '')
    {
        $mod  = M('article',$this->prefix,'WEBSITE');
        $list = $mod->query('SELECT id,column_id,title,CONCAT("'.$link.'",column_id) AS link FROM '.$this->prefix.'article WHERE id = (SELECT MIN(id) FROM gw_article WHERE home_id='.$home_id.' and id > '.$id.' AND column_id='.$column_id.' AND status=1)
                 UNION ALL SELECT id,column_id,title,CONCAT("'.$link.'",column_id) AS link FROM '.$this->prefix.'article WHERE id = (SELECT MAX(id) FROM gw_article WHERE home_id='.$home_id.' and id < '.$id.' AND column_id='.$column_id.' AND status=1)');
        return $list;
    }
}