<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/5/27
 * Time: 15:12
 *
 * SMS模型
 */

namespace Api\Model;

use Think\Model;

class SmsModel extends Model
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
     * 根据手机号获取SMS数据信息
     * @param $mobile
     * @return bool
     */
    public function getSmsByMobile($mobile)
    {
        //判断手机号是否存在
        if (!$mobile) return false;

        return M($this->tableName)->where("mobile = '{$mobile}'")->find();
    }

    /**
     * 添加SMS数据表的信息
     * @param $data
     * @return bool
     */
    public function addSms($data)
    {
        //判断手机号、验证码是否存在
        if (!$data["mobile"] || !$data["code"]) return false;

        //参数填充
        !isset($data["type"]) && $data["type"] = 0;
        !isset($data["time"]) && $data["time"] = time();
        !isset($data["status"]) && $data["status"] = 1;

        //组装SQL语句
        $sql = "INSERT INTO `".C("DB_PREFIX")."{$this->getModelName()}` (`mobile`,`code`,`type`,`time`,`status`) VALUE 
                ('{$data['mobile']}','{$data['code']}','{$data['type']}','{$data['time']}','{$data['status']}')
                ON DUPLICATE KEY UPDATE `code` = '{$data['code']}',`type` = '{$data['type']}',`time` = '{$data['time']}',`status` = '{$data['status']}'";

        //添加数据
        return M($this->tableName)->execute($sql);
    }

    /**
     * 添加SMS日志LOG
     * @param $data
     * @return bool|mixed
     */
    public function addLog($data)
    {
        //判断手机号、验证码是否存在
        if (!$data["mobile"] || !$data["code"]) return false;

        //数据库前缀设置
//        C("DB_PREFIX", C("DB_PREFIX_LOG"));
        return M($this->tableName, C("DB_PREFIX_LOG"))->add($data);
    }

    /**
     * 使用SMS短信验证
     * @param $mobile
     * @return bool
     */
    public function useSms($mobile)
    {
        //判断手机号是否存在
        if (!$mobile) return false;

        return M($this->tableName)->where("mobile = '{$mobile}'")->save(array("status" => 0));
    }
}