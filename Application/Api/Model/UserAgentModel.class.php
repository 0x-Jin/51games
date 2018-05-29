<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 2018/1/26
 * Time: 17:15
 *
 * 用户渠道模块
 */

namespace Api\Model;

use Think\Model;

class UserAgentModel extends Model
{
    protected $tableName = "";                                                //数据表名（不包括前缀）

    /**
     * 初始化
     */
    public function __construct()
    {
        $this->tableName = "user_agent";
    }

    /**
     * 添加信息
     * @param $data
     * @return bool|mixed
     */
    public function addUserAgent($data)
    {
        //判断必要数据是否存在
        if (!$data["udid"] || !$data["agent"]) return false;

        //判断用户是否已经存在,不存在上报
        if(!$this->getUserAgent(array('userCode' => $data['userCode'],'agent' => $data["agent"]))){
            $reportData = $data;
            $reportData['agent'] = $data['regAgent'];
            if ($data['solo'] == 1 && $data['type'] == 1) {
                D('Api/ANDMatch')->activeReport($reportData,3);//主动上报注册
            }
            
        }

        return M($this->tableName)->add($data);
    }

    /**
     * 获取用户渠道信息
     * @param $map
     * @return mixed
     */
    public function getUserAgent($map)
    {
        return M($this->tableName)->where($map)->find();
    }
}