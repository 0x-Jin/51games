<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 2018/4/3
 * Time: 16:39
 *
 * 腾讯云
 */

namespace Fusion\Model;

class TengxunyunModel extends SdkModel
{
    private $channel_id     = "";                                                                           //渠道ID
    private $callback_url   = "";                                                                           //充值回调地址

    public function __construct()
    {
        parent::__construct();
        //渠道ID
        $this->channel_id       = $this->getChannelId();
        //充值回调地址
        $this->callback_url     = C("COMPANY_DOMAIN")."Api/Reply/ChannelCallback/CyChannelId/".$this->channel_id;
    }

    /**
     * 初始化接口
     * @param $agent
     * @return array
     */
    public function init($agent)
    {
        $key = $this->getKey($agent);
        $res = array(
            "AppId" => $key["AppId"]
        );
        return $res;
    }

    /**
     * 二登验证
     * @param $data
     * @return array
     */
    public function loginCheck($data)
    {
        //判断必要数据是否齐全
        if (!$data["uid"] || !$data["agent"]) {
            $res = array(
                "Result"    => false,
                "Data"      => array()
            );
            return $res;
        }

        //用户数据
        $res = array(
            "Result"    => true,
            "Data"      => array(
                "channelUserCode"   => $data["uid"],
                "channelUserName"   => $data["userName"]? $data["userName"]: $data["uid"]
            )
        );
        return $res;
    }
}