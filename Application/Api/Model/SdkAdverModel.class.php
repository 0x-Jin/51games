<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 2018/4/16
 * Time: 11:35
 *
 * 对接了SDK的广告商报送
 */

namespace Api\Model;

use Think\Model;

class SdkAdverModel extends Model
{

    protected $autoCheckFields  = false; //关闭自动检测数据库字段
    private $GDTAccessToken     = array(
        "7002236"   => "2517560cf7cb7367ced4a9e8a362b2fb",   //广点通的用户ID => 广点通的token
    );

    private $GDTSubmitUrl       = "https://api.e.qq.com/v1.0/user_actions/add";     //广点通报送地址
    private $JRTTSubmitUrl      = "http://mcs.snssdk.com/v2/event/json";            //今日头条报送地址

    /**
     * 上报数据
     * @param array $info 用户信息
     * @param array $data 客户端信息
     * @param array $param 配置参数
     * @param int $type 1:注册，2:充值
     * @return bool
     */
    public function submit($info, $data, $param, $type = 1)
    {
        if ($type == 1) {
            //注册
            if ($param["id"] == 6) {
                //今日头条
                if (!$info["userCode"] || !$data["package"] || !$param["Akey"]) return false;
                $this->JRTTsubmit($param["Akey"], $info["userCode"], $data["package"], array("method" => ($info["method"]? $info["method"]: "account")), $data["type"], 1);
            }
        } elseif ($type == 2) {
            //充值
            if ($param["id"] == 2) {
                //广点通
                if (!$info["type"] || !$param["AccountId"] || !$param["UserActionSetId"] || !$info["orderId"]) return false;
                if (($info["type"] == 1 && !$info["imei"]) || ($info["type"] == 2 && !$info["idfa"])) return false;
                $list = array("type" => $info["type"], "imei" => $info["imei"], "idfa" => $info["idfa"], "nonce" => "L".$info["orderId"]);
                $this->GDTsumbit($list, $param["AccountId"], $param["UserActionSetId"], $this->GDTAccessToken[$param["AccountId"]], 2);
            }
            if ($param["id"] == 6) {
                //今日头条
                if (!$info["userCode"] || !$info["amount"] || !$info["package"] || !$param["Akey"]) return false;
                $this->JRTTsubmit($param["Akey"], $info["userCode"], $info["package"], array("amount" => $info["amount"]), $info["type"], 2);
            }
        }
        return true;
    }

    /**
     * 广点通报送
     * @param $data
     * @param $account
     * @param $action
     * @param $token
     * @param $type
     * @return bool
     */
    private function GDTsumbit($data, $account, $action, $token, $type)
    {
        $event      = array();
        $user       = array();
        if ($data["type"] == 1) {
            //安卓
            $user   = array("hash_imei" => md5(strtolower($data["imei"])));
        } elseif ($data["type"] == 2) {
            //IOS
            $user   = array("hash_idfa" => md5(strtoupper($data["idfa"])));
        }
        if ($type == 1) {
            //注册
            $event  = array(
                "action_time"   => time(),
                "user_id"       => $user,
                "action_type"   => "REGISTER"
            );
        } elseif ($type == 2) {
            //充值
            $event  = array(
                "action_time"   => time(),
                "user_id"       => $user,
                "action_type"   => "PURCHASE"
            );
        }
        $info   = array(
            "account_id"            => $account,
            "user_action_set_id"    => $action,
            "actions"               => array(
                $event
            )
        );

        //进行报送
        $res    = $this->GDTcurl($info, $token, $data["nonce"]);
        $Res    = json_decode($res, true);
        if ($Res["code"] == "0") return true;
        return false;
    }

    /**
     * 今日头条报送
     * @param $key
     * @param $userCode
     * @param $package
     * @param $data
     * @param $mobile
     * @param int $type
     * @return bool
     */
    private function JRTTsubmit($key, $userCode, $package, $data, $mobile, $type = 1)
    {
        $event      = array();
        if ($type == 1) {
            //注册
            $event  = array(
                "event"         => "register_server",
                "params"        => json_encode(array("method" => $data["method"], "is_success" => "yes")),
                "local_time_ms" => time()*1000
            );
        } elseif ($type == 2) {
            //充值
            $event  = array(
                "event"         => "purchase",
                "params"        => json_encode(array("currency" => "rmb", "is_success" => "yes", "currency_amount" => intval($data["amount"]))),
                "local_time_ms" => time()*1000
            );
        }
        $info   = array(
            "user"      => array(
                "user_unique_id"    => $userCode
            ),
            "header"    => array(
                "app_package"       => $package,
                "os_name"           => $mobile == 1? "android": ($mobile == 2? "ios": "unknown")
            ),
            "events"    => array(
                $event
            )
        );

        //进行报送
        $res    = $this->JRTTcurl($info, $key);

        if ($res == "200") return true;
        return false;
    }

    /**
     * 广点通报送
     * @param $info
     * @param $token
     * @param $nonce
     * @param int $timeout
     * @return mixed
     */
    private function GDTcurl($info, $token, $nonce, $timeout = 5)
    {
        $ch     = curl_init();
        $url    = $this->GDTSubmitUrl."?access_token=".$token."&timestamp=".time()."&nonce=".$nonce;
        $header = array(
            "Content-Type: application/json"
        );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header); //定义请求类型
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_POST, true);
        if (strpos(strtolower($url), "https://") !== false) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($info));
        $returnTransfer = curl_exec($ch);
        curl_close($ch);
        log_save("[type]send    [url]".$url."    [data]".json_encode($info)."    [header]".json_encode($header), "info", "", "gdt_sdk_adv_submit_".date("Ymd"));
        log_save("[type]res    [res]".$returnTransfer, "info", "", "gdt_sdk_adv_submit_".date("Ymd"));

        return $returnTransfer;
    }

    /**
     * 今日头条报送
     * @param $info
     * @param $key
     * @param int $timeout
     * @return mixed
     */
    private function JRTTcurl($info, $key, $timeout = 5)
    {
        $ch     = curl_init();
        $url    = $this->JRTTSubmitUrl;
        $header = array(
            "Content-Type: application/json",
            "X-MCS-AppKey: ".$key
        );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header); //定义请求类型
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_POST, true);
        if (strpos(strtolower($url), "https://") !== false) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($info));
        $returnTransfer = curl_exec($ch);
        // 获得响应结果里的状态码
        $headerCode     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        log_save("[type]send    [url]".$this->JRTTSubmitUrl."    [data]".json_encode($info)."    [header]".json_encode($header), "info", "", "jrtt_sdk_adv_submit_".date("Ymd"));
        log_save("[type]res    [res]".str_replace("\r\n", "--", $returnTransfer)."    [code]".$headerCode, "info", "", "jrtt_sdk_adv_submit_".date("Ymd"));

        return $headerCode;
    }
}