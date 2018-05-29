<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/6/21
 * Time: 14:14
 *
 * 渠道模板
 */

namespace Fusion\Model;

use Think\Model;

class SdkModel extends Model
{

    protected $autoCheckFields  = false;                                            //关闭自动检测数据库字段
    protected $tableName        = "";                                               //数据表名（不包括前缀）
    protected $mod              = "";                                               //数据模型
    protected $modelName        = "";                                               //模块名称

    /**
     * 初始化
     */
    public function __construct()
    {
        $this->tableName    = "channel";
        $this->mod          = M($this->tableName);
        $this->modelName    = $this->getModelName();
    }

    /**
     * 获取渠道ID
     * @return mixed
     */
    public function getChannelId()
    {
        $abbr       = preg_replace('|[0-9]+|', '', $this->modelName);
        $channel    = $this->mod->where("channelAbbr = '".$abbr."'")->find();
        return $channel["id"];
    }

    /**
     * 获取渠道号的配置信息
     * @param $agent 渠道号
     * @return array
     */
    public function getKey($agent)
    {
        $agent_info     = D("Api/Agent")->getAgent($agent);
        $channel_info   = D("Api/Channel")->getChannel($agent_info["channel_id"]);
        $res            = array();
        for($i = 1; !empty($channel_info["param".$i]); $i++){
            $res[$channel_info["param".$i]] = $agent_info["value".$i];
        }
        return $res;
    }

    /**
     * 二登验证接口
     * @param $data
     * @return array
     */
    public function loginCheck($data)
    {
        //判断必要数据是否齐全
        if (!$data["uid"] || !$data["agent"] || !$data["token"]) {
            $res = array(
                "Result"    => false,
                "Data"      => array()
            );
            return $res;
        }

        //二登验证
        if ($data["token"] != md5($data["uid"].$data["userName"])) {
            $res = array(
                "Result"    => false,
                "Data"      => array()
            );
        } else {
            //用户数据
            $res = array(
                "Result"    => true,
                "Data"      => array(
                    "channelUserCode"   => $data["uid"],
                    "channelUserName"   => $data["userName"]
                )
            );
        }
        return $res;
    }

    /**
     * 获取商品ID
     * @param $agent
     * @param $goodsCode
     * @return mixed
     */
    public function getFusionGoods($agent, $goodsCode)
    {
        $goods = D("Api/FusionGoods")->getFusionGoods($agent, $goodsCode);
        return $goods["channelGoods"];
    }

    /**
     * 默认的获取数据方法
     * @return mixed
     */
    public function getInput()
    {
        $data = $_REQUEST;
        unset($data["CyChannelId"], $data["CyChannelVer"], $data["PHPSESSID"]);
        return $data;
    }

    /**
     * 默认的加密验证方法
     * @param $data
     * @return bool
     */
    public function callbackCheck($data)
    {
        $str = "";
        foreach ($data as $k => $v) {
            if ($k != "sign") $str .= $k."=".$v."&";
        }
        if ($data["sign"] != md5(trim($str, "&"))) {
            return false;
        } else {
            return $data;
        }
    }

    /**
     * 默认的正确返回方式
     * @param array $data 返回数据
     */
    public function callbackSuc($data = array())
    {
        echo "success";
        exit();
    }

    /**
     * 默认的错误返回方式
     * @param $num 错误类型，1：验证失败，2：数据错误，3：商品价格错误，4：回调失败，5：错误用户
     * @param array $data 返回数据
     */
    public function callbackErr($num, $data = array())
    {
        echo "fail";
        exit();
    }
}