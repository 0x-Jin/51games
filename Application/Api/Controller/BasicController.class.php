<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/5/26
 * Time: 15:38
 *
 * 重要控制器
 */

namespace Api\Controller;

class BasicController extends ApiController
{
    //需要报送热云的游戏ID
    private $ryGameId = array(101,102,103,110,112,116);

    /**
     * 初始化接口
     */
    public function Init()
    {
        //获取数据
        $input = $this->getInput("post", "trim");

        //解密出来的数据
        $data = $this->getDecrypt($input);

        //判断必要数据是否存在
        if (!$data["udid"] || !$data["gid"] || !isset($data["type"]) || !$data["agent"]) {
            $res = array(
                "Msg" => "未获取到数据！请重新打开游戏！"
            );
            $this->returnMsg($res, 4, $input["Gid"], "详细数据缺失", 1, 0, $input["Version"]);
        }

        //获取游戏信息
        $game = D("Api/Game")->getGame($data["gid"]);
        if (!$game) {
            $res = array(
                "Msg" => "数据错误！请重新打开游戏！"
            );
            $this->returnMsg($res, 6, $input["Gid"], "无该游戏", 1, 0, $input["Version"]);
        }

        //获取渠道号信息
        $agent = D("Api/Agent")->getAgent($data["agent"]);
        if (!$agent || $agent["game_id"] != $data["gid"]) {
            $res = array(
                "Msg" => "数据错误！请重新打开游戏！"
            );
            $this->returnMsg($res, 6, $input["Gid"], "无该渠道号", 1, 0, $input["Version"]);
        }

        //添加初始化日志
        $ip     = get_ip_address();
        $area   = ip_to_location($ip);
        $log    = array(
            "udid"          => $data["udid"],
            "game_id"       => $data["gid"],
            "channel_id"    => $agent["channel_id"],
            "agent"         => $data["agent"],
            "mac"           => $data["mac"],
            "idfa"          => $data["idfa"],
            "idfv"          => $data["idfv"],
            "serial"        => $data["serial"],
            "imei"          => empty($data["imei"])? (empty($data["imei2"]? "": $data["imei2"])): $data["imei"],
            "imei2"         => empty($data["imei"])? "": (empty($data["imei2"])? "": $data["imei2"]),
            "systemId"      => $data["systemId"],
            "systemInfo"    => $data["systemInfo"],
            "screen"        => $data["screen"],
            "net"           => $data["net"],
            "type"          => $data["type"],
            "ip"            => $ip,
            "city"          => $area["city"],
            "province"      => $area["province"],
            "ver"           => $input["Version"],
            "gameVer"       => $data["gameVer"],
            "time"          => time()
        );
        D("Api/Init")->addLog($log);

        //组装设备数据
        $device = $log;
        unset($device["time"], $device["net"], $device["gameVer"]);
        $device["createTime"] = $device["lastInit"] = time();

        //存储设备数据
        D("Api/Device")->addDevice($device);

        //记录热云每款游戏首次打开
        $this->ryOpenReport($device);

        //获取设备信息
        $device_info = D("Api/Device")->getDeviceByUdid($data["udid"]);

        if (!$device_info) {
            $res = array(
                "Msg" => "初始化失败！"
            );
            $this->returnMsg($res, 5, $input["Gid"], "初始化失败！", 1, 0, $input["Version"]);
        }

        //更新数据库里设备的空信息
        $device_update = array();
        foreach ($device_info as $k => $v) {
            if ($k != "udid" && !$v && $v !== 0 && isset($device[$k]) && $v != $device[$k]) {
                $device_update[$k] = $device[$k];
            }
        }
        count($device_update) && D("Api/Device")->saveDeviceByUdid($device_update, $data["udid"]);

        //添加设备游戏表的数据
        if (!D("Api/DeviceGame")->getDeviceGame($data["udid"], $data["gid"])) {
            D("Api/DeviceGame")->addDeviceGame($device);

            //安卓融合的热云游戏报送
            if ($data["type"] == 1 && $agent["channel_id"] > 1) {
                //首次打开报送
                D("Api/ANDMatch")->gameReyunReport($device, 1);
            }
        } else {
            D("Api/DeviceGame")->saveDeviceGame(array("lastInit" => $device["lastInit"]), $data["udid"], $data["gid"]);

            //安卓融合的热云游戏报送
            if ($data["type"] == 1 && $agent["channel_id"] > 1) {
                //每次打开报送
                D("Api/ANDMatch")->gameReyunReport($device, 2);
            }
        }

        //IOS设备激活添加
        if($data['type'] == 2){
            if(!D('Api/IOSMatch')->getDeviceAgent($data['udid'], $data['agent']) || whiteList($data['imei'],$data['idfa'])) {
                $iosDevice = $log;
                $iosDevice['createTime'] = $device["createTime"];
                D("Api/IOSMatch")->deviceMatch($iosDevice);
                unset($iosDevice);
            }
        }

        //获取母包的渠道信息
        if ($agent["pid"] > 0) {
            $agent_parent   = D("Api/Agent")->getAgentById($agent["pid"]);
        } else {
            $agent_parent   = $agent;
        }
        if (!$agent_parent) {
            $res = array(
                "Msg" => "数据异常！请重新登陆！"
            );
            $this->returnMsg($res, 6, $input["Gid"], "无该母包渠道号", 1, 0, $input["Version"]);
        }

        //如果未存在，则添加渠道
        if (!D("Api/DeviceAgent")->getDeviceAgent($data["udid"], $agent_parent["agent"]) || whiteList($data['imei'],$data['idfa'])) {
            $device_agent               = $device;
            $device_agent["regAgent"]   = $data["agent"];
            $device_agent["agent"]      = $agent_parent["agent"];
            unset($device_agent["ver"], $device_agent["screen"], $device_agent["lastInit"]);

            D("Api/DeviceAgent")->addDeviceAgent($device_agent);
        }

        //获取版本更新
        $update = D("Api/Update")->getVerByGameAgent($data["type"], $input["Version"], $data["gid"], $agent["channel_id"], $data["agent"]);
        if ($update) {
            $update_data = array(
                "Update"    => $update["update"] + 1,                       //更新类型，0:不进行更新，1：选择性更新，2：强制性更新
                "Path"      => $update["path"],                             //下载地址
                "Content"   => $update["content"],                          //更新提示
                "Ver"       => $update["ver"]                               //版本
            );

            //校花强更包优化
            if ($update["id"] == "4" || $update["id"] == "5") {
                $update_agent           = $agent["packageStatus"] == 2? ($data["agent"] == $update["agent"]? $update["agent"]."001": $data["agent"]): $update["agent"]."001";
                $update_data["Path"]    = "http://static.chuangyunet.net/".$update_agent.".apk";
            }
        } else {
            $update_data = array(
                "Update"    => 0                                            //更新类型，0:不进行更新，1：选择性更新，2：强制性更新
            );
        }

//        if ($data["udid"] == "386693cc-9d46-34f1-97a7-52019f2d607a") {
//            $update_data = array(
//                "Update"    => 2,                       //更新类型，0:不进行更新，1：选择性更新，2：强制性更新
//                "Path"      => "https://static.chuangyunet.net/UF1522054855590.apk",                             //下载地址
//                "Content"   => "测试",                          //更新提示
//                "Ver"       => 1                               //版本
//            );
//
//        }

        //获取版本补丁
        $patch = D("Api/Patch")->getVerByGameAgent($data["type"], $input["Version"], $data["gid"], $agent["channel_id"], $data["agent"], isset($data["patch"])? $data["patch"]: 1);
        if ($patch) {
            $patch_data = array(
                "Patch"     => 1,                                           //补丁状态，0:不进行补丁，1：进行补丁
                "Path"      => $patch["path"],                              //下载地址
                "Ver"       => intval($patch["patchVer"])                   //补丁版本
            );
        } else {
            $patch_data = array(
                "Patch"     => 0                                            //更新类型，0:不进行补丁，1：进行补丁
            );
        }

        //获取公告
        $notice = D("Api/Notice")->getNoticeByGameAgent($data["type"], $input["Version"], $data["gid"], $agent["channel_id"], $data["agent"]);
        if ($notice) {
            $notice_data = array(
                "Notice"    => 0,                                           //公告开关，0：开启，1：关闭
                "Content"   => $notice["content"]                           //公告
            );
        } else {
            $notice_data = array(
                "Notice"    => 1                                            //公告开关，0：开启，1：关闭
            );
        }

        //渠道初始化参数
        $init = array();
        if (!in_array($agent["channel_id"], array(0, 1, 14))) {
            //获取渠道信息
            $channel = D("Api/Channel")->getChannel($agent["channel_id"]);

            //渠道初始化
            $channel_name = ucfirst($channel["channelAbbr"]).($agent["channelVer"] > 1? $agent["channelVer"]: "");
            if (method_exists(D("Fusion/".$channel_name), "init")) {
                $init = D("Fusion/".$channel_name)->init($data["agent"]);
            }
        }

        //投放配置参数
        $adverParam = D("Api/AdverParam")->getAdverParam($data["agent"]);

        $qqGroup = "";
        $qq      = "";
        //查询渠道包的母包
        if ($agent["pid"] == '10639' || $data["agent"] == "xhdtsgsAND") {
            $qqGroup = "556314388";
            $qq      = "3003704951";
        }

        $centerStatus   = $data["agent"] == "zfdldIOS"? 0: 1;              //苹果GameCenter开关，0：开启，1：关闭
//        $centerStatus   = 1;
        $VungleAPPID    = '5a0cedcdcb98a09074004e5f';
        $VungleIDsArray = array('DEFAULT24350','TEST1-2492323');




        //返回的数据
        $res = array(
            "Msg"               => "初始化成功！",
            "Login"             => ($device_info["loginStatus"] || $game["loginStatus"] || $agent["loginStatus"])? "false": "true",              //返回登陆的开关
            "Update"            => $update_data,                                                                        //版本更新数据
            "Patch"             => $patch_data,                                                                         //版本补丁
            "Notice"            => $notice_data,                                                                        //公告
            "FuseInit"          => $init,                                                                               //初始化渠道参数
            "Channel"           => $agent["channel_id"],                                                                //登陆的渠道ID
            "QQ"                => $qq ? $qq : C("COMPANY_QQ"),                                                         //客服QQ
            "QQGroup"           => $qqGroup,                                                                            //客服QQ
            "NewQQ"             => "http://wpa.b.qq.com/cgi/wpa.php?ln=1&key=XzgwMDE4MDE4M180ODAxODZfODAwMTgwMTgzXzJf", //营销QQ所需字段
            "Center"            => $centerStatus,
            "Phone"             => C("COMPANY_PHONE"),                                                                  //客服号码
            "onlineTime"        => 0,                                                                                   //在线传回的时间间隔，0为关闭，单位是分钟
            "Privacy"           => C("COMPANY_PRIVACY"),                                                                //隐私条件
            "Agreement"         => C("COMPANY_AGREEMENT"),                                                              //用户协议
            "VungleAPPID"       => $VungleAPPID,                                                                        //
            "VungleIDsArray"    => $VungleIDsArray,
            "Gift"              => 0,                                                                                   //礼包状态，0：开启，1：关闭
            "AdverParam"        => $adverParam,                                                                      //广告参数状态
            "H5LoginLink"       => $agent_parent["H5LoginLink"]                                                                          //广告参数状态
        );

        $this->returnMsg($res, 0, $input["Gid"], "初始化成功！", 1, 0, $input["Version"]);
    }

    /**
     * 账号注册接口
     */
    public function AccountRegister()
    {
        //获取数据
        $input  = $this->getInput("post", "trim");

        //解密出来的数据
        $data   = $this->getDecrypt($input);

        //判断必要数据是否存在
        if (!$data["userName"] || !$data["password"]) {
            $res = array(
                "Msg" => "请输入用户账号或密码！"
            );
            $this->returnMsg($res, 4, $input["Gid"], "请输入用户账号或密码!", 1, 0, $input["Version"]);
        }
        if (!$data["gid"] || !$data["udid"] || !$data["agent"]) {
            $res = array(
                "Msg" => "数据错误！请重新注册！"
            );
            $this->returnMsg($res, 4, $input["Gid"], "详细数据缺失！", 1, 0, $input["Version"]);
        }

        //判断输入的用户名格式是否正确
        if (!preg_match("/^[A-Za-z0-9\-]+$/", $data["userName"]) || preg_match("/^[0-9]$/", substr($data["userName"], 0, 1)) || strlen($data["userName"]) > 20 || strlen($data["userName"]) < 6) {
            $res = array(
                "Msg" => "请输入正确的用户名称！6~20位字母数字组成，需字母开头！"
            );
            $this->returnMsg($res, 4, $input["Gid"], "用户名错误！", 1, 0, $input["Version"]);
        }

        //获取渠道号信息
        $agent = D("Api/Agent")->getAgent($data["agent"]);
        if (!$agent || $agent["game_id"] != $data["gid"] || !in_array($agent["channel_id"], array(0, 1))) {
            $res = array(
                "Msg" => "数据错误！请重新注册！"
            );
            $this->returnMsg($res, 6, $input["Gid"], "无该渠道号", 1, 0, $input["Version"]);
        }

        //获取渠道
        $channel = D("Api/Channel")->getChannel($agent["channel_id"]);

        //IP地址
        $ip     = get_ip_address();
        $area   = ip_to_location($ip);

        //同一IP同一天内不允许超过20个注册
        if (!whiteList("", "", $data["udid"], $ip) && (20 < D("Api/User")->getCount(array("ip" => $ip, "createTime" => array("BETWEEN", array(strtotime(date("Y-m-d")), strtotime(date("Y-m-d 23:59:59")))))))) {
            $res = array(
                "Msg" => "注册账号超过限制！"
            );
            $this->returnMsg($res, 4, $input["Gid"], "注册账号超过限制", 1, 0, $input["Version"]);
        }

        //获取设备信息
        $device = D("Api/Device")->getDeviceByUdid($data["udid"]);
        if (!$device) {
//            $res = array(
//                "Msg" => "发生异常！请重新打开游戏！"
//            );
//            $this->returnMsg($res, 6, $input["Gid"], "用户未初始化！", 1, 0, $input["Version"]);

            //添加初始化日志
            $log    = array(
                "udid"          => $data["udid"],
                "game_id"       => $data["gid"],
                "channel_id"    => $agent["channel_id"],
                "agent"         => $data["agent"],
                "mac"           => $data["mac"],
                "idfa"          => $data["idfa"],
                "idfv"          => $data["idfv"],
                "serial"        => $data["serial"],
                "imei"          => empty($data["imei"])? (empty($data["imei2"]? "": $data["imei2"])): $data["imei"],
                "imei2"         => empty($data["imei"])? "": (empty($data["imei2"])? "": $data["imei2"]),
                "systemId"      => $data["systemId"],
                "systemInfo"    => $data["systemInfo"],
                "screen"        => $data["screen"],
                "net"           => $data["net"],
                "type"          => $data["type"],
                "ip"            => $ip,
                "city"          => $area["city"],
                "province"      => $area["province"],
                "ver"           => $input["Version"],
                "gameVer"       => $data["gameVer"],
                "time"          => time()
            );
            D("Api/Init")->addLog($log);

            //组装设备数据
            $device = $log;
            unset($device["time"], $device["net"], $device["gameVer"]);
            $device["createTime"] = $device["lastInit"] = time();

            //存储设备数据
            D("Api/Device")->addDevice($device);

            //记录热云每款游戏首次打开
            $this->ryOpenReport($device);

            //获取设备信息
            $device_info = D("Api/Device")->getDeviceByUdid($data["udid"]);

            if (!$device_info) {
                $res = array(
                    "Msg" => "初始化失败！"
                );
                $this->returnMsg($res, 5, $input["Gid"], "初始化失败！", 1, 0, $input["Version"]);
            }

            //添加设备游戏表的数据
            D("Api/DeviceGame")->addDeviceGame($device);

            $device = $device_info;
        }

        //获取游戏信息
        $game = D("Api/Game")->getGame($data["gid"]);
        if (!$game) {
            $res = array(
                "Msg" => "数据错误！请重新注册！"
            );
            $this->returnMsg($res, 6, $input["Gid"], "无该游戏！", 1, 0, $input["Version"]);
        }

        //判断是否可以注册
        if ($device["loginStatus"] > 0 || $game["loginStatus"] > 0 || $agent["loginStatus"] > 0) {
            $res = array(
                "Msg" => "无法注册！请您联系客服人员！"
            );
            $this->returnMsg($res, 5, $input["Gid"], "关闭新增！", 1, 0, $input["Version"]);
        }

        //判断用户数据是否已经存在
        if (D("Api/User")->getUser(array("userName" => $data["userName"]))) {
            $res = array(
                "Msg" => "账号已被注册！请选择其他用户名进行注册！"
            );
            $this->returnMsg($res, 5, $input["Gid"], "账号已存在！", 1, 0, $input["Version"]);
        };

        //用户注册数据
        $info   = array(
            "userName"          => $data["userName"],
            "channelUserName"   => $data["userName"],
            "password"          => make_password($data["password"]),
            "game_id"           => $data["gid"],
            "gameName"          => $game["gameName"],
            "channel_id"        => $agent["channel_id"],
            "channelName"       => $channel["channelName"],
            "agent"             => $data["agent"],
            "ip"                => $ip,
            "city"              => $area["city"],
            "province"          => $area["province"],
            "createTime"        => time(),
            "udid"              => $data["udid"],
            "imei"              => empty($data["imei"])? (empty($data["imei2"]? "": $data["imei2"])): $data["imei"],
            "imei2"             => empty($data["imei"])? "": (empty($data["imei2"])? "": $data["imei2"]),
            "idfa"              => $data["idfa"],
            "device_id"         => $device["id"],
            "type"              => $data["type"],
            "oneKey"            => (isset($data["oneKey"]) && $data["oneKey"] == 0)? 3: 2,
            "register"          => (isset($data["oneKey"]) && $data["oneKey"] == 0)? 3: 2,
            "ver"               => $input["Version"],
            "lastIP"            => $ip,
            "lastLogin"         => time(),
            "lastGameId"        => $data["gid"],
            "lastAgent"         => $data["agent"]
        );

        //注册是否成功
        $key    = 0;
        //用户的唯一标识码
        $code   = "";

        //循环注册，避免唯一标识符重复导致注册失败
        for ($i = 0; $i < 5; $i ++) {
            $info["channelUserCode"] = $info["userCode"] = make_user_code();

            //提审包注册的用户特殊标记
            if ($agent["trialVer"] && $data["gameVer"] == $agent["trialVer"]) {
                $info["channelUserCode"] .= "_tishen";
            }

            if (D("Api/User")->addUser($info)) {
                $key    = 1;
                $code   = $info["userCode"];
                break;
            }
        }

        //判断是否注册成功
        if ($key != 1) {
            //注册失败
            $res = array(
                "Msg"   => "注册失败！",
            );
            $this->returnMsg($res, 5, $input["Gid"], "注册失败", 1, 0, $input["Version"]);
        }

        //生成登陆、二登验证TOKEN
        $loginToken     = make_random(24);
        $secretToken    = make_random(24);

        //组装TOKEN数据
        $token = array(
            "userCode"      => $code,
            "game_id"       => $data["gid"],
            "loginToken"    => $loginToken,
            "secretToken"   => $secretToken,
            "loginTime"     => time()
        );

        //更新TOKEN
        if (!D("Api/TokenGame")->addToken($token)) {
            $res = array(
                "Msg" => "获取数据失败！请重新登陆！"
            );
            $this->returnMsg($res, 5, $input["Gid"], "更新TOKEN失败", 1, 0, $input["Version"]);
        }

        //更新用户游戏表
        $user_game = array(
            "userCode"      => $code,
            "userName"      => $data["userName"],
            "game_id"       => $data["gid"],
            "channel_id"    => $agent["channel_id"],
            "agent"         => $data["agent"],
            "ip"            => $ip,
            "city"          => $area["city"],
            "province"      => $area["province"],
            "createTime"    => time(),
            "udid"          => $data["udid"],
            "imei"          => empty($data["imei"])? (empty($data["imei2"]? "": $data["imei2"])): $data["imei"],
            "imei2"         => empty($data["imei"])? "": (empty($data["imei2"])? "": $data["imei2"]),
            "idfa"          => $data["idfa"],
            "device_id"     => $device["id"],
            "type"          => $data["type"],
            "ver"           => $input["Version"],
            "lastIP"        => $ip,
            "lastLogin"     => time(),
            "lastGameId"    => $data["gid"],
            "lastAgent"     => $data["agent"]
        );
        D("Api/UserGame")->addUserGame($user_game);

        //IOS用户添加
        if($data['type'] == 2){
            $iosUser = $user_game;
            
            $iosUser['idfv']        = $data['idfv'];
            $iosUser["systemId"]    = $data["systemId"];
            $iosUser["systemInfo"]  = $data["systemInfo"];
            $iosUser["net"]         = $data["net"];
            $iosUser["mac"]         = $data["mac"];
            $iosUser['serial']      = $data["serial"];
            D("Api/IOSMatch")->registerMatch($iosUser);
            unset($iosUser);
        }

        //获取母包的渠道信息
        if ($agent["pid"] > 0) {
            $agent_parent   = D("Api/Agent")->getAgentById($agent["pid"]);
        } else {
            $agent_parent   = $agent;
        }

        //如果未存在，则添加渠道
        if (!D("Api/UserAgent")->getUserAgent(array("agent" => $agent_parent["agent"], "userCode" => $code))) {
            $user_agent             = $user_game;
            $user_agent["regAgent"] = $device["agent"];
            $user_agent["agent"]    = $agent_parent["agent"];
            unset($user_agent["ver"], $user_agent["lastIP"], $user_agent["lastLogin"], $user_agent["lastGameId"], $user_agent["lastAgent"]);
            if (D("Api/UserAgent")->getUserAgent(array("agent" => $agent_parent["agent"], "udid" => $data["udid"]))) {
                $user_agent["solo"] = 0;
            } else {
                $user_agent["solo"] = 1;
            }

            D("Api/UserAgent")->addUserAgent($user_agent);

//            //投放配置参数
//            $adv_param  = D("Api/AdverParam")->getAdverParam($data["agent"]);
//            if ($adv_param) {
//                $user_agent["method"]   = "account";
//                foreach ($adv_param as $adv_value) {
//                    if ($adv_value) D("Api/SdkAdver")->submit($user_agent, $data, $adv_value, 1);
//                }
//            }
        }

        //设备添加最后登录时间
        D("Api/Device")->saveDeviceByUdid(array("lastLogin" => time()), $data["udid"]);
        D("Api/DeviceGame")->saveDeviceGame(array("lastLogin" => time()), $data["udid"], $data["gid"]);

        //用户登陆LOG数据
        $log = array(
            "userCode"          => $code,
            "channelUserCode"   => $code,
            "udid"              => $data["udid"],
            "mac"               => $data["mac"],
            "idfa"              => $data["idfa"],
            "idfv"              => $data["idfv"],
            "imei"              => empty($data["imei"])? (empty($data["imei2"]? "": $data["imei2"])): $data["imei"],
            "imei2"             => empty($data["imei"])? "": (empty($data["imei2"])? "": $data["imei2"]),
            "type"              => $data["type"],
            "agent"             => $data["agent"],
            "game_id"           => $data["gid"],
            "channel_id"        => $agent["channel_id"],
            "ver"               => $input["Version"],
            "time"              => time(),
            "ip"                => $ip,
            "city"              => $area["city"],
            "province"          => $area["province"],
            "net"               => $data["net"],
            "regAgent"          => $info["agent"],
            "regTime"           => $info["createTime"]
        );

        //添加登陆LOG日志
        D("Api/Login")->addLog($log);

        //用户首次注册报送热云记录
        $ryData = array(
            "userCode"      => $info["userCode"],
            "userName"      => $info['userName'],
            "gid"           => $data["gid"],
            "idfa"          => $data['idfa'],
            "idfv"          => $data['idfv'],
            "channel_id"    => $info['channel_id'],
            "agent"         => $info["agent"],
            "ip"            => $ip,
            "createTime"    => $info['createTime'],
            "udid"          => $data["udid"],
            "device_id"     => $device["id"],
            "type"          => $data["type"],
            "lastIP"        => $ip
        );
        $this->ryRegistReport($ryData);
       

        //登陆成功
        $res = array(
            "Msg"           => "登陆成功！",
            "UserCode"      => $code,                                                   //返回用户的唯一标识符
            "UserName"      => $data["userName"],                                       //返回用户的账号名称
            "OneKey"        => isset($data["oneKey"])? $data["oneKey"]: 1,              //是否是一键注册用户，0：是，1：否
            "LoginToken"    => $loginToken,                                             //返回用户的二登验证TOKEN
            "SecretToken"   => $secretToken,                                            //返回用户的交互加密TOKEN
            "Mobile"        => 1,                                                       //手机是否绑定，0：已绑定，1：未绑定
            "IDCard"        => 1,                                                       //身份证是否绑定，0：已绑定，1：未绑定
            "MobileNum"     => "",                                                      //手机号码
            "AccountName"   => $data["userName"],                                       //用户账号
            "Register"      => 2                                                        //是否为注册，0：不是，1：手机注册，2：账号注册，3：一键注册，4：手机验证码注册，5：快速游戏注册，6：融合注册
        );
        $this->returnMsg($res, 0, $input["Gid"], "登陆成功", 1, 0, $input["Version"]);
    }
    
    /**
     * 手机注册接口
     */
    public function MobileRegister()
    {
        //获取数据
        $input  = $this->getInput("post", "trim");

        //解密出来的数据
        $data   = $this->getDecrypt($input);

        //判断必要数据是否存在
        if (!$data["mobile"] || !preg_match("/^1\d{10}$/", $data["mobile"])) {
            $res = array(
                "Msg" => "请输入手机号码!"
            );
            $this->returnMsg($res, 4, $input["Gid"], "请输入手机号码!", 1, 0, $input["Version"]);
        }
        if (!$data["code"]) {
            $res = array(
                "Msg" => "请输入验证码!"
            );
            $this->returnMsg($res, 4, $input["Gid"], "请输入验证码！", 1, 0, $input["Version"]);
        }
        if (!$data["password"]) {
            $res = array(
                "Msg" => "请输入密码!"
            );
            $this->returnMsg($res, 4, $input["Gid"], "请输入密码！", 1, 0, $input["Version"]);
        }
        if (!$data["gid"] || !$data["udid"] || !$data["agent"]) {
            $res = array(
                "Msg" => "数据错误！请重新注册！"
            );
            $this->returnMsg($res, 4, $input["Gid"], "详细数据缺失！", 1, 0, $input["Version"]);
        }

        //获取渠道号信息
        $agent = D("Api/Agent")->getAgent($data["agent"]);
        if (!$agent || $agent["game_id"] != $data["gid"]) {
            $res = array(
                "Msg" => "数据错误！请重新注册！"
            );
            $this->returnMsg($res, 6, $input["Gid"], "无该渠道号", 1, 0, $input["Version"]);
        }

        //获取渠道
        $channel = D("Api/Channel")->getChannel($agent["channel_id"]);

        //IP地址
        $ip     = get_ip_address();
        $area   = ip_to_location($ip);

        //同一IP同一天内不允许超过20个注册
        if (20 < D("Api/User")->getCount(array("ip" => $ip, "createTime" => array("BETWEEN", array(strtotime(date("Y-m-d")), strtotime(date("Y-m-d 23:59:59"))))))) {
            $res = array(
                "Msg" => "数据错误！请重新注册！"
            );
            $this->returnMsg($res, 4, $input["Gid"], "无该渠道号", 1, 0, $input["Version"]);
        }

        //获取设备信息
        $device = D("Api/Device")->getDeviceByUdid($data["udid"]);
        if (!$device) {
//            $res = array(
//                "Msg" => "数据错误！请重新注册！"
//            );
//            $this->returnMsg($res, 6, $input["Gid"], "用户未初始化！", 1, 0, $input["Version"]);

            //添加初始化日志
            $log    = array(
                "udid"          => $data["udid"],
                "game_id"       => $data["gid"],
                "channel_id"    => $agent["channel_id"],
                "agent"         => $data["agent"],
                "mac"           => $data["mac"],
                "idfa"          => $data["idfa"],
                "idfv"          => $data["idfv"],
                "serial"        => $data["serial"],
                "imei"          => empty($data["imei"])? (empty($data["imei2"]? "": $data["imei2"])): $data["imei"],
                "imei2"         => empty($data["imei"])? "": (empty($data["imei2"])? "": $data["imei2"]),
                "systemId"      => $data["systemId"],
                "systemInfo"    => $data["systemInfo"],
                "screen"        => $data["screen"],
                "net"           => $data["net"],
                "type"          => $data["type"],
                "ip"            => $ip,
                "city"          => $area["city"],
                "province"      => $area["province"],
                "ver"           => $input["Version"],
                "gameVer"       => $data["gameVer"],
                "time"          => time()
            );
            D("Api/Init")->addLog($log);

            //组装设备数据
            $device = $log;
            unset($device["time"], $device["net"], $device["gameVer"]);
            $device["createTime"] = $device["lastInit"] = time();

            //存储设备数据
            D("Api/Device")->addDevice($device);

            //记录热云每款游戏首次打开
            $this->ryOpenReport($device);

            //获取设备信息
            $device_info = D("Api/Device")->getDeviceByUdid($data["udid"]);

            if (!$device_info) {
                $res = array(
                    "Msg" => "初始化失败！"
                );
                $this->returnMsg($res, 5, $input["Gid"], "初始化失败！", 1, 0, $input["Version"]);
            }

            //添加设备游戏表的数据
            D("Api/DeviceGame")->addDeviceGame($device);

            $device = $device_info;
        }

        //获取游戏信息
        $game = D("Api/Game")->getGame($data["gid"]);
        if (!$game) {
            $res = array(
                "Msg" => "发生异常！请重新打开游戏！"
            );
            $this->returnMsg($res, 6, $input["Gid"], "无该游戏！", 1, 0, $input["Version"]);
        }

        //判断是否可以注册
        if ($device["loginStatus"] > 0 || $game["loginStatus"] > 0 || $agent["loginStatus"] > 0) {
            $res = array(
                "Msg" => "无法注册！请您联系客服人员！"
            );
            $this->returnMsg($res, 5, $input["Gid"], "关闭新增！", 1, 0, $input["Version"]);
        }

        //判断用户数据是否已经存在
        if (D("Api/User")->getUser(array("mobile" => $data["mobile"], "mobileStatus" => 0))) {
            $res = array(
                "Msg" => "该手机号已被注册！请选择其他手机号码进行注册！"
            );
            $this->returnMsg($res, 5, $input["Gid"], "手机号已存在！", 1, 0, $input["Version"]);
        };

        //获取保存的验证码
        $code = D("Api/Sms")->getSmsByMobile($data["mobile"]);

        //验证码不正确或类型不对
        if ($data["code"] != $code["code"] || $code["type"] != 1) {
            $res = array(
                "Msg" => "验证码不正确！请重新输入！"
            );
            $this->returnMsg($res, 5, $input["Gid"], "验证码不正确", 1, 0, $input["Version"]);
        }

        //验证码过期
        if (time() - $code["time"] > 300) {
            $res = array(
                "Msg" => "验证码已过期！请重新获取！"
            );
            $this->returnMsg($res, 5, $input["Gid"], "验证码已过期", 1, 0, $input["Version"]);
        }

        //验证码已使用
        if ($code["status"] == "0") {
            $res = array(
                "Msg" => "验证码已使用！请重新获取！"
            );
            $this->returnMsg($res, 5, $input["Gid"], "验证码已使用", 1, 0, $input["Version"]);
        }

        //判断是否是已经注册过的手机号码
        if (D("Api/User")->getUser(array("mobileStatus" => 0, "mobile" => $data["mobile"]))) {
            $res = array(
                "Msg" => "手机号码已注册！请使用其他手机号码！"
            );
            $this->returnMsg($res, 5, $input["Gid"], "手机号码已注册", 1, 0, $input["Version"]);
        }

        //用户注册数据
        $info   = array(
            "password"      => make_password($data["password"]),
            "game_id"       => $data["gid"],
            "gameName"      => $game["gameName"],
            "channel_id"    => $agent["channel_id"],
            "channelName"   => $channel["channelName"],
            "agent"         => $data["agent"],
            "ip"            => $ip,
            "city"          => $area["city"],
            "province"      => $area["province"],
            "createTime"    => time(),
            "udid"          => $data["udid"],
            "idfa"          => $data["idfa"],
            "imei"          => empty($data["imei"])? (empty($data["imei2"]? "": $data["imei2"])): $data["imei"],
            "imei2"         => empty($data["imei"])? "": (empty($data["imei2"])? "": $data["imei2"]),
            "device_id"     => $device["id"],
            "mobile"        => $data["mobile"],
            "mobileStatus"  => 0,
            "type"          => $data["type"],
            "oneKey"        => 1,
            "register"      => 1,
            "ver"           => $input["Version"],
            "lastIP"        => $ip,
            "lastLogin"     => time(),
            "lastGameId"    => $data["gid"],
            "lastAgent"     => $data["agent"]
        );

        //注册是否成功
        $key    = 0;
        //用户的唯一标识码
        $code   = "";

        //循环注册，避免唯一标识符重复导致注册失败
        for ($i = 0; $i < 5; $i ++) {
            $info["channelUserName"]    = $info["channelUserCode"] = $info["userCode"] = make_user_code();
            $info["userName"]           = C("COMPANY_CODE").time().substr(uniqid(true), -5);

            //提审包注册的用户特殊标记
            if ($agent["trialVer"] && $data["gameVer"] == $agent["trialVer"]) {
                $info["channelUserCode"] .= "_tishen";
            }

            if (D("Api/User")->addUser($info)) {
                $key    = 1;
                $code   = $info["userCode"];
                break;
            }
        }

        //判断是否注册成功
        if ($key != 1) {
            //注册失败
            $res = array(
                "Msg"   => "注册失败！",
            );
            $this->returnMsg($res, 5, $input["Gid"], "注册失败", 1, 0, $input["Version"]);
        }

        //使用短信验证码
        D("Api/Sms")->useSms($data["mobile"]);

        //生成登陆、二登验证TOKEN
        $loginToken     = make_random(24);
        $secretToken    = make_random(24);

        //组装TOKEN数据
        $token = array(
            "userCode"      => $code,
            "game_id"       => $data["gid"],
            "loginToken"    => $loginToken,
            "secretToken"   => $secretToken,
            "loginTime"     => time()
        );

        //更新TOKEN
        if (!D("Api/TokenGame")->addToken($token)) {
            $res = array(
                "Msg" => "获取数据失败！请重新登陆！"
            );
            $this->returnMsg($res, 5, $input["Gid"], "更新TOKEN失败", 1, 0, $input["Version"]);
        }

        //更新用户游戏表
        $user_game = array(
            "userCode"      => $code,
            "userName"      => $info["userName"],
            "game_id"       => $data["gid"],
            "channel_id"    => $agent["channel_id"],
            "agent"         => $data["agent"],
            "ip"            => $ip,
            "city"          => $area["city"],
            "province"      => $area["province"],
            "createTime"    => time(),
            "udid"          => $data["udid"],
            "idfa"          => $data["idfa"],
            "imei"          => empty($data["imei"])? (empty($data["imei2"]? "": $data["imei2"])): $data["imei"],
            "imei2"         => empty($data["imei"])? "": (empty($data["imei2"])? "": $data["imei2"]),
            "device_id"     => $device["id"],
            "type"          => $data["type"],
            "ver"           => $input["Version"],
            "lastIP"        => $ip,
            "lastLogin"     => time(),
            "lastGameId"    => $data["gid"],
            "lastAgent"     => $data["agent"]
        );
        D("Api/UserGame")->addUserGame($user_game);

        //IOS用户添加
        if($data['type'] == 2){
            $iosUser = $user_game;
            
            $iosUser['idfv']        = $data['idfv'];
            $iosUser["systemId"]    = $data["systemId"];
            $iosUser["systemInfo"]  = $data["systemInfo"];
            $iosUser["net"]         = $data["net"];
            $iosUser["mac"]         = $data["mac"];
            $iosUser['serial']      = $data["serial"];
            D("Api/IOSMatch")->registerMatch($iosUser);
            unset($iosUser);
        }

        //获取母包的渠道信息
        if ($agent["pid"] > 0) {
            $agent_parent   = D("Api/Agent")->getAgentById($agent["pid"]);
        } else {
            $agent_parent   = $agent;
        }

        //如果未存在，则添加渠道
        if (!D("Api/UserAgent")->getUserAgent(array("agent" => $agent_parent["agent"], "userCode" => $code))) {
            $user_agent             = $user_game;
            $user_agent["regAgent"] = $device["agent"];
            $user_agent["agent"]    = $agent_parent["agent"];
            unset($user_agent["ver"], $user_agent["lastIP"], $user_agent["lastLogin"], $user_agent["lastGameId"], $user_agent["lastAgent"]);
            if (D("Api/UserAgent")->getUserAgent(array("agent" => $agent_parent["agent"], "udid" => $data["udid"]))) {
                $user_agent["solo"] = 0;
            } else {
                $user_agent["solo"] = 1;
            }

            D("Api/UserAgent")->addUserAgent($user_agent);

//            //投放配置参数
//            $adv_param  = D("Api/AdverParam")->getAdverParam($data["agent"]);
//            if ($adv_param) {
//                $user_agent["method"]   = "mobile";
//                foreach ($adv_param as $adv_value) {
//                    if ($adv_value) D("Api/SdkAdver")->submit($user_agent, $data, $adv_value, 1);
//                }
//            }
        }

        //设备添加最后登录时间
        D("Api/Device")->saveDeviceByUdid(array("lastLogin" => time()), $data["udid"]);
        D("Api/DeviceGame")->saveDeviceGame(array("lastLogin" => time()), $data["udid"], $data["gid"]);

        //用户登陆LOG数据
        $log = array(
            "userCode"          => $code,
            "channelUserCode"   => $code,
            "udid"              => $data["udid"],
            "mac"               => $data["mac"],
            "imei"              => empty($data["imei"])? (empty($data["imei2"]? "": $data["imei2"])): $data["imei"],
            "imei2"             => empty($data["imei"])? "": (empty($data["imei2"])? "": $data["imei2"]),
            "type"              => $data["type"],
            "agent"             => $data["agent"],
            "game_id"           => $data["gid"],
            "channel_id"        => $agent["channel_id"],
            "ver"               => $input["Version"],
            "time"              => time(),
            "ip"                => $ip,
            "city"              => $area["city"],
            "province"          => $area["province"],
            "net"               => $data["net"],
            "regAgent"          => $info["agent"],
            "regTime"           => $info["createTime"]
        );

        //添加登陆LOG日志
        D("Api/Login")->addLog($log);

        //用户首次注册报送热云记录
        $ryData = array(
            "userCode"      => $info["userCode"],
            "userName"      => $info['userName'],
            "gid"           => $data["gid"],
            "idfa"          => $data['idfa'],
            "idfv"          => $data['idfv'],
            "channel_id"    => $info['channel_id'],
            "agent"         => $info["agent"],
            "ip"            => $ip,
            "createTime"    => $info['createTime'],
            "udid"          => $data["udid"],
            "device_id"     => $device["id"],
            "type"          => $data["type"],
            "lastIP"        => $ip
        );
        $this->ryRegistReport($ryData);

        //登陆成功
        $res = array(
            "Msg"           => "登陆成功！",
            "UserCode"      => $code,                                                   //返回用户的唯一标识符
            "UserName"      => $data["mobile"],                                         //返回用户的手机号码
            "OneKey"        => 1,                                                       //是否是一键注册用户，0：是，1：否
            "LoginToken"    => $loginToken,                                             //返回用户的二登验证TOKEN
            "SecretToken"   => $secretToken,                                            //返回用户的交互加密TOKEN
            "Mobile"        => 0,                                                       //手机是否绑定，0：已绑定，1：未绑定
            "IDCard"        => 1,                                                       //身份证是否绑定，0：已绑定，1：未绑定
            "MobileNum"     => $data["mobile"],                                         //手机号码
            "AccountName"   => $info["userName"],                                       //用户账号
            "Register"      => 1                                                        //是否为注册，0：不是，1：手机注册，2：账号注册，3：一键注册，4：手机验证码注册，5：快速游戏注册，6：融合注册
        );
        $this->returnMsg($res, 0, $input["Gid"], "登陆成功", 1, 0, $input["Version"]);
    }

    /**
     * 登陆接口
     */
    public function Login()
    {
        //获取数据
        $input  = $this->getInput("post", "trim");

        //解密出来的数据
        $data   = $this->getDecrypt($input);

        //判断必要数据是否存在
        if (!$data["userName"]) {
            $res = array(
                "Msg" => "请输入用户账号或手机号码！"
            );
            $this->returnMsg($res, 4, $input["Gid"], "请输入用户账号或手机号码！", 1, 0, $input["Version"]);
        }
        if (!$data["password"]) {
            $res = array(
                "Msg" => "请输入密码！"
            );
            $this->returnMsg($res, 4, $input["Gid"], "请输入密码！", 1, 0, $input["Version"]);
        }
        if (!$data["gid"] || !$data["udid"] || !$data["agent"]) {
            $res = array(
                "Msg" => "数据错误！请重新登陆！"
            );
            $this->returnMsg($res, 4, $input["Gid"], "详细数据缺失！", 1, 0, $input["Version"]);
        }

        //获取渠道号信息
        $agent = D("Api/Agent")->getAgent($data["agent"]);
        if (!$agent || $agent["game_id"] != $data["gid"]) {
            $res = array(
                "Msg" => "数据异常！请重新登陆！"
            );
            $this->returnMsg($res, 6, $input["Gid"], "无该渠道号", 1, 0, $input["Version"]);
        }

        //IP地址
        $ip     = get_ip_address();
        $area   = ip_to_location($ip);

        //获取设备信息
        $device = D("Api/Device")->getDeviceByUdid($data["udid"]);
        if (!$device) {
//            $res = array(
//                "Msg" => "数据异常！请重新打开游戏！"
//            );
//            $this->returnMsg($res, 6, $input["Gid"], "用户未初始化！", 1, 0, $input["Version"]);

            //添加初始化日志
            $log    = array(
                "udid"          => $data["udid"],
                "game_id"       => $data["gid"],
                "channel_id"    => $agent["channel_id"],
                "agent"         => $data["agent"],
                "mac"           => $data["mac"],
                "idfa"          => $data["idfa"],
                "idfv"          => $data["idfv"],
                "serial"        => $data["serial"],
                "imei"          => empty($data["imei"])? (empty($data["imei2"]? "": $data["imei2"])): $data["imei"],
                "imei2"         => empty($data["imei"])? "": (empty($data["imei2"])? "": $data["imei2"]),
                "systemId"      => $data["systemId"],
                "systemInfo"    => $data["systemInfo"],
                "screen"        => $data["screen"],
                "net"           => $data["net"],
                "type"          => $data["type"],
                "ip"            => $ip,
                "city"          => $area["city"],
                "province"      => $area["province"],
                "ver"           => $input["Version"],
                "gameVer"       => $data["gameVer"],
                "time"          => time()
            );
            D("Api/Init")->addLog($log);

            //组装设备数据
            $device = $log;
            unset($device["time"], $device["net"], $device["gameVer"]);
            $device["createTime"] = $device["lastInit"] = time();

            //存储设备数据
            D("Api/Device")->addDevice($device);

            //记录热云每款游戏首次打开
            $this->ryOpenReport($device);

            //获取设备信息
            $device_info = D("Api/Device")->getDeviceByUdid($data["udid"]);

            if (!$device_info) {
                $res = array(
                    "Msg" => "初始化失败！"
                );
                $this->returnMsg($res, 5, $input["Gid"], "初始化失败！", 1, 0, $input["Version"]);
            }

            //添加设备游戏表的数据
            D("Api/DeviceGame")->addDeviceGame($device);

            $device = $device_info;
        }

        //获取游戏信息
        $game = D("Api/Game")->getGame($data["gid"]);
        if (!$game) {
            $res = array(
                "Msg" => "数据异常！请重新登陆！"
            );
            $this->returnMsg($res, 6, $input["Gid"], "无该游戏！", 1, 0, $input["Version"]);
        }

        //判断是否可以登陆
        if ($device["loginStatus"] == 1 || $game["loginStatus"] == 1 || $agent["loginStatus"] == 1) {
            $res = array(
                "Msg" => "无法登陆！请您联系客服人员！"
            );
            $this->returnMsg($res, 5, $input["Gid"], "关闭登陆！", 1, 0, $input["Version"]);
        }

        //判断是否是用手机号登陆
        $map    = array();
        $c      = preg_match("/^[0-9]$/", substr($data["userName"], 0, 1));
        if ($c) {
            $map["mobileStatus"]    = 0;
            $map["mobile"]          = $data["userName"];
        } else {
            $map["userName"]        = $data["userName"];
        }

        //获取用户数据
        $user = D("Api/User")->getUser($map);

        //判断用户数据是否存在
        if (!$user) {
            $res = array(
                "Msg" => "无此用户！请输入正确的用户账号或手机号码！"
            );
            $this->returnMsg($res, 5, $input["Gid"], "无此用户", 1, 0, $input["Version"]);
        }

        //判断密码是否正确
        if (!check_password($data["password"], $user["password"])) {
            $res = array(
                "Msg" => "密码错误！请输入正确的密码！"
            );
            $this->returnMsg($res, 5, $input["Gid"], "密码错误", 1, 0, $input["Version"]);
        }

        //判断用户是否可以登陆
        if ($user["status"] == 1 && (!$user["allowLoginTime"] || $user["allowLoginTime"] > time())) {
            $res = array(
                "Msg" => "无法登陆！请您联系客服人员！"
            );
            $this->returnMsg($res, 5, $input["Gid"], "用户关闭登陆！", 1, 0, $input["Version"]);
        }

        //提审包，用户的创建渠道号跟包的渠道号不一致
        if (($data["gameVer"] == $agent["trialVer"]) && ($user["agent"] != $data["agent"])) {
            $res = array(
                "Msg" => "错误码：042801"
            );
            $this->returnMsg($res, 5, $input["Gid"], "错误码：042801！", 1, 0, $input["Version"]);
        }
        //提审用户，用户登陆的渠道号跟包的渠道号不一致
        if ((substr($user["channelUserCode"], -7) == "_tishen") && ($user["agent"] != $data["agent"])) {
            $res = array(
                "Msg" => "错误码：042802"
            );
            $this->returnMsg($res, 5, $input["Gid"], "错误码：042802！", 1, 0, $input["Version"]);
        }

        //登陆TOKEN
        $token          = D("Api/Token")->getToken($user["userCode"]);

        //生成登陆、二登验证TOKEN
        $loginToken     = ($token && ((time() - $token["loginTime"]) < 10))? $token["loginToken"]: make_random(24);
        $secretToken    = make_random(24);

        //组装TOKEN数据
        $token = array(
            "userCode"      => $user["userCode"],
            "game_id"       => $data["gid"],
            "loginToken"    => $loginToken,
            "secretToken"   => $secretToken,
            "loginTime"     => time()
        );

        //更新TOKEN
        if (!D("Api/TokenGame")->addToken($token)) {
            $res = array(
                "Msg" => "获取数据失败！请重新登陆！"
            );
            $this->returnMsg($res, 5, $input["Gid"], "更新TOKEN失败", 1, 0, $input["Version"]);
        }

        //用户登陆数据
        $info   = array(
            "lastIP"        => $ip,
            "lastLogin"     => time(),
            "lastGameId"    => $data["gid"],
            "lastAgent"     => $data["agent"]
        );

        //判断是否更新最后登录数据成功
        if (!D("Api/User")->saveUser($info, $user["userCode"])) {
            $res = array(
                "Msg"   => "用户数据异常！请重新登陆！",
            );
            $this->returnMsg($res, 5, $input["Gid"], "更新最后登录数据失败", 1, 0, $input["Version"]);
        }

        //设备添加最后登录时间
        D("Api/Device")->saveDeviceByUdid(array("lastLogin" => time()), $data["udid"]);
        D("Api/DeviceGame")->saveDeviceGame(array("lastLogin" => time()), $data["udid"], $data["gid"]);

        //更新用户游戏表
        $user_game = array(
            "userCode"      => $user["userCode"],
            "userName"      => $user['userName'],
            "game_id"       => $data["gid"],
            "channel_id"    => $agent["channel_id"],
            "agent"         => $data["agent"],
            "ip"            => $ip,
            "city"          => $area["city"],
            "province"      => $area["province"],
            "createTime"    => time(),
            "udid"          => $data["udid"],
            "idfa"          => $data["idfa"],
            "imei"          => empty($data["imei"])? (empty($data["imei2"]? "": $data["imei2"])): $data["imei"],
            "imei2"         => empty($data["imei"])? "": (empty($data["imei2"])? "": $data["imei2"]),
            "device_id"     => $device["id"],
            "type"          => $data["type"],
            "ver"           => $input["Version"],
            "lastIP"        => $ip,
            "lastLogin"     => time(),
            "lastGameId"    => $data["gid"],
            "lastAgent"     => $data["agent"]
        );
        D("Api/UserGame")->addUserGame($user_game);

        //获取母包的渠道信息
        if ($agent["pid"] > 0) {
            $agent_parent   = D("Api/Agent")->getAgentById($agent["pid"]);
        } else {
            $agent_parent   = $agent;
        }

        //如果未存在，则添加渠道
        if (!D("Api/UserAgent")->getUserAgent(array("agent" => $agent_parent["agent"], "userCode" => $user["userCode"]))) {
            $user_agent             = $user_game;
            $user_agent["regAgent"] = $device["agent"];
            $user_agent["agent"]    = $agent_parent["agent"];
            unset($user_agent["ver"], $user_agent["lastIP"], $user_agent["lastLogin"], $user_agent["lastGameId"], $user_agent["lastAgent"]);
            if (D("Api/UserAgent")->getUserAgent(array("agent" => $agent_parent["agent"], "udid" => $data["udid"]))) {
                $user_agent["solo"] = 0;
            } else {
                $user_agent["solo"] = 1;
            }

            D("Api/UserAgent")->addUserAgent($user_agent);

//            //投放配置参数
//            $adv_param  = D("Api/AdverParam")->getAdverParam($data["agent"]);
//            if ($adv_param) {
//                $user_agent["method"]   = "login";
//                foreach ($adv_param as $adv_value) {
//                    if ($adv_value) D("Api/SdkAdver")->submit($user_agent, $data, $adv_value, 1);
//                }
//            }
        }

        //用户登陆LOG数据
        $log = array(
            "userCode"          => $user["userCode"],
            "channelUserCode"   => $user["channelUserCode"],
            "udid"              => $data["udid"],
            "mac"               => $data["mac"],
            "idfv"              => $data["idfv"],
            "idfa"              => $data["idfa"],
            "imei"              => empty($data["imei"])? (empty($data["imei2"]? "": $data["imei2"])): $data["imei"],
            "imei2"             => empty($data["imei"])? "": (empty($data["imei2"])? "": $data["imei2"]),
            "type"              => $data["type"],
            "agent"             => $data["agent"],
            "game_id"           => $data["gid"],
            "channel_id"        => $agent["channel_id"],
            "ver"               => $input["Version"],
            "time"              => time(),
            "ip"                => $ip,
            "city"              => $area["city"],
            "province"          => $area["province"],
            "net"               => $data["net"],
            "regAgent"          => $user["agent"],
            "regTime"           => $user["createTime"]
        );
        //添加登陆LOG日志
        D("Api/Login")->addLog($log);

        //用户首次注册报送热云记录
        $ry_data = array(
                "userCode"      => $user["userCode"],
                "userName"      => $user['userName'],
                "gid"           => $data["gid"],
                "idfa"          => $data['idfa'],
                "idfv"          => $data['idfv'],
                "channel_id"    => $user['channel_id'],
                "agent"         => $data["agent"],
                "ip"            => $ip,
                "createTime"    => time(),
                "udid"          => $data["udid"],
                "device_id"     => $device["id"],
                "type"          => $data["type"],
                "lastIP"        => $ip
            );
        $this->ryRegistReport($ry_data);

        //登陆成功
        $res = array(
            "Msg"           => "登陆成功！",
            "UserCode"      => $user["userCode"],                                       //返回用户的唯一标识符
            "UserName"      => $data["userName"],                                       //返回用户的账号名称
            "OneKey"        => in_array($user["oneKey"], array(1, 3, 4, 5))? 0: 1,      //是否是一键注册用户，0：是，1：否
            "LoginToken"    => $loginToken,                                             //返回用户的二登验证TOKEN
            "SecretToken"   => $secretToken,                                            //返回用户的交互加密TOKEN
            "Mobile"        => $user["mobileStatus"],                                   //手机是否绑定，0：已绑定，1：未绑定
            "IDCard"        => $user["IDCardStatus"],                                   //身份证是否绑定，0：已绑定，1：未绑定
            "MobileNum"     => $user["mobile"],                                         //手机号码
            "AccountName"   => $user["userName"],                                       //用户账号
            "Register"      => 0                                                        //是否为注册，0：不是，1：手机注册，2：账号注册，3：一键注册，4：手机验证码注册，5：快速游戏注册，6：融合注册
        );
        $this->returnMsg($res, 0, $input["Gid"], "登陆成功", 1, 0, $input["Version"]);
    }

    /**
     * 手机号验证码登陆接口
     */
    public function CodeLogin()
    {
        //获取数据
        $input  = $this->getInput("post", "trim");

        //解密出来的数据
        $data   = $this->getDecrypt($input);

        //判断必要数据是否存在
        if (!$data["mobile"]) {
            $res = array(
                "Msg" => "请输入手机号码！"
            );
            $this->returnMsg($res, 4, $input["Gid"], "请输入手机号码！", 1, 0, $input["Version"]);
        }
        if (!$data["code"]) {
            $res = array(
                "Msg" => "请输入验证码！"
            );
            $this->returnMsg($res, 4, $input["Gid"], "请输入验证码！", 1, 0, $input["Version"]);
        }
        if (!$data["gid"] || !$data["udid"] || !$data["agent"] || !isset($data["codeType"])) {
            $res = array(
                "Msg" => "数据错误！请重新登陆！"
            );
            $this->returnMsg($res, 4, $input["Gid"], "详细数据缺失！", 1, 0, $input["Version"]);
        }

        //获取渠道号信息
        $agent = D("Api/Agent")->getAgent($data["agent"]);
        if (!$agent || $agent["game_id"] != $data["gid"]) {
            $res = array(
                "Msg" => "数据异常！请重新登陆！"
            );
            $this->returnMsg($res, 6, $input["Gid"], "无该渠道号", 1, 0, $input["Version"]);
        }

        //获取渠道
        $channel = D("Api/Channel")->getChannel($agent["channel_id"]);

        //IP地址
        $ip     = get_ip_address();
        $area   = ip_to_location($ip);

        //获取设备信息
        $device = D("Api/Device")->getDeviceByUdid($data["udid"]);
        if (!$device) {
//            $res = array(
//                "Msg" => "数据异常！请重新打开游戏！"
//            );
//            $this->returnMsg($res, 6, $input["Gid"], "用户未初始化！", 1, 0, $input["Version"]);

            //添加初始化日志
            $log    = array(
                "udid"          => $data["udid"],
                "game_id"       => $data["gid"],
                "channel_id"    => $agent["channel_id"],
                "agent"         => $data["agent"],
                "mac"           => $data["mac"],
                "idfa"          => $data["idfa"],
                "idfv"          => $data["idfv"],
                "serial"        => $data["serial"],
                "imei"          => empty($data["imei"])? (empty($data["imei2"]? "": $data["imei2"])): $data["imei"],
                "imei2"         => empty($data["imei"])? "": (empty($data["imei2"])? "": $data["imei2"]),
                "systemId"      => $data["systemId"],
                "systemInfo"    => $data["systemInfo"],
                "screen"        => $data["screen"],
                "net"           => $data["net"],
                "type"          => $data["type"],
                "ip"            => $ip,
                "city"          => $area["city"],
                "province"      => $area["province"],
                "ver"           => $input["Version"],
                "gameVer"       => $data["gameVer"],
                "time"          => time()
            );
            D("Api/Init")->addLog($log);

            //组装设备数据
            $device = $log;
            unset($device["time"], $device["net"], $device["gameVer"]);
            $device["createTime"] = $device["lastInit"] = time();

            //存储设备数据
            D("Api/Device")->addDevice($device);

            //记录热云每款游戏首次打开
            $this->ryOpenReport($device);

            //获取设备信息
            $device_info = D("Api/Device")->getDeviceByUdid($data["udid"]);

            if (!$device_info) {
                $res = array(
                    "Msg" => "初始化失败！"
                );
                $this->returnMsg($res, 5, $input["Gid"], "初始化失败！", 1, 0, $input["Version"]);
            }

            //添加设备游戏表的数据
            D("Api/DeviceGame")->addDeviceGame($device);

            $device = $device_info;
        }

        //获取游戏信息
        $game = D("Api/Game")->getGame($data["gid"]);
        if (!$game) {
            $res = array(
                "Msg" => "数据异常！请重新登陆！"
            );
            $this->returnMsg($res, 6, $input["Gid"], "无该游戏！", 1, 0, $input["Version"]);
        }

        $map["mobileStatus"]    = 0;
        $map["mobile"]          = $data["mobile"];

        //获取用户数据
        $user = D("Api/User")->getUser($map);

        //判断验证码是否为手动输入的
        if ($data["codeType"] == "0") {
            //手动输入
            //获取保存的验证码
            $code = D("Api/Sms")->getSmsByMobile($data["mobile"]);

            //验证码不正确或类型不对
            if ($data["code"] != $code["code"] || $code["type"] != 5) {
                $res = array(
                    "Msg" => "验证码不正确！请重新输入！"
                );
                $this->returnMsg($res, 5, $input["Gid"], "验证码不正确", 1, 0, $input["Version"]);
            }

            //验证码过期
            if (time() - $code["time"] > 300) {
                $res = array(
                    "Msg" => "验证码已过期！请重新获取！"
                );
                $this->returnMsg($res, 5, $input["Gid"], "验证码已过期", 1, 0, $input["Version"]);
            }

            //验证码已使用
            if ($code["status"] == "0") {
                $res = array(
                    "Msg" => "验证码已使用！请重新获取！"
                );
                $this->returnMsg($res, 5, $input["Gid"], "验证码已使用", 1, 0, $input["Version"]);
            }

            //判断是否已经注册过的账号，未注册则自动注册
            if (!$user) {
                //判断是否可以注册
                if ($device["loginStatus"] > 0 || $game["loginStatus"] > 0 || $agent["loginStatus"] > 0) {
                    $res = array(
                        "Msg" => "无法注册！请您联系客服人员！"
                    );
                    $this->returnMsg($res, 5, $input["Gid"], "关闭新增！", 1, 0, $input["Version"]);
                }

                //用户注册数据
                $info   = array(
                    "password"      => make_password(uniqid(true)),
                    "game_id"       => $data["gid"],
                    "gameName"      => $game["gameName"],
                    "channel_id"    => $agent["channel_id"],
                    "channelName"   => $channel["channelName"],
                    "agent"         => $data["agent"],
                    "ip"            => $ip,
                    "city"          => $area["city"],
                    "province"      => $area["province"],
                    "createTime"    => time(),
                    "udid"          => $data["udid"],
                    "idfa"          => $data["idfa"],
                    "imei"          => empty($data["imei"])? (empty($data["imei2"]? "": $data["imei2"])): $data["imei"],
                    "imei2"         => empty($data["imei"])? "": (empty($data["imei2"])? "": $data["imei2"]),
                    "device_id"     => $device["id"],
                    "mobile"        => $data["mobile"],
                    "mobileStatus"  => 0,
                    "mobileCode"    => $data["code"],
                    "type"          => $data["type"],
                    "oneKey"        => 4,
                    "register"      => 4,
                    "ver"           => $input["Version"]
                );

                //注册是否成功
                $key        = 0;
                //用户的唯一标识码
                $userCode   = "";

                //循环注册，避免唯一标识符重复导致注册失败
                for ($i = 0; $i < 5; $i ++) {
                    $info["channelUserName"]    = $info["channelUserCode"] = $info["userCode"] = make_user_code();
                    $info["userName"]           = C("COMPANY_CODE").time().substr(uniqid(true), -5);

                    //提审包注册的用户特殊标记
                    if ($agent["trialVer"] && $data["gameVer"] == $agent["trialVer"]) {
                        $info["channelUserCode"] .= "_tishen";
                    }

                    if (D("Api/User")->addUser($info)) {
                        $key        = 1;
                        $userCode   = $info["userCode"];
                        break;
                    }
                }

                //判断是否注册成功
                if ($key != 1) {
                    //注册失败
                    $res = array(
                        "Msg"   => "注册失败！",
                    );
                    $this->returnMsg($res, 5, $input["Gid"], "注册失败", 1, 0, $input["Version"]);
                }

                //获取用户信息
                $user = D("Api/User")->getUser(array("userCode" => $userCode));
            } else {
                if (!D("Api/User")->saveUser(array("mobileCode" => $data["code"]), $user["userCode"])) {
                    $res = array(
                        "Msg"   => "更新验证码失败！",
                    );
                    $this->returnMsg($res, 5, $input["Gid"], "更新验证码失败！", 1, 0, $input["Version"]);
                }
            }

            //使用短信验证码
            D("Api/Sms")->useSms($data["mobile"]);
        } else {
            //自动登陆
            if (!$user) {
                $res = array(
                    "Msg"   => "无效用户！",
                );
                $this->returnMsg($res, 5, $input["Gid"], "无效用户", 1, 0, $input["Version"]);
            }
            //验证码不正确或类型不对
            if ($data["code"] != $user["mobileCode"]) {
                $res = array(
                    "Msg" => "验证码不正确！请重新输入！"
                );
                $this->returnMsg($res, 5, $input["Gid"], "验证码不正确", 1, 0, $input["Version"]);
            }
        }

        //判断是否可以登陆
        if ($device["loginStatus"] == 1 || $game["loginStatus"] == 1 || $agent["loginStatus"] == 1) {
            $res = array(
                "Msg" => "无法登陆！请您联系客服人员！"
            );
            $this->returnMsg($res, 5, $input["Gid"], "关闭登陆！", 1, 0, $input["Version"]);
        }

        //判断用户是否可以登陆
        if ($user["status"] == 1) {
            $res = array(
                "Msg" => "无法登陆！请您联系客服人员！"
            );
            $this->returnMsg($res, 5, $input["Gid"], "用户关闭登陆！", 1, 0, $input["Version"]);
        }

        //提审包，用户的创建渠道号跟包的渠道号不一致
        if (($data["gameVer"] == $agent["trialVer"]) && ($user["agent"] != $data["agent"])) {
            $res = array(
                "Msg" => "错误码：042801"
            );
            $this->returnMsg($res, 5, $input["Gid"], "错误码：042801！", 1, 0, $input["Version"]);
        }
        //提审用户，用户登陆的渠道号跟包的渠道号不一致
        if ((substr($user["channelUserCode"], -7) == "_tishen") && ($user["agent"] != $data["agent"])) {
            $res = array(
                "Msg" => "错误码：042802"
            );
            $this->returnMsg($res, 5, $input["Gid"], "错误码：042802！", 1, 0, $input["Version"]);
        }

        //生成登陆、二登验证TOKEN
        $loginToken     = make_random(24);
        $secretToken    = make_random(24);

        //组装TOKEN数据
        $token = array(
            "userCode"      => $user["userCode"],
            "game_id"       => $data["gid"],
            "loginToken"    => $loginToken,
            "secretToken"   => $secretToken,
            "loginTime"     => time()
        );

        //更新TOKEN
        if (!D("Api/TokenGame")->addToken($token)) {
            $res = array(
                "Msg" => "获取数据失败！请重新登陆！"
            );
            $this->returnMsg($res, 5, $input["Gid"], "更新TOKEN失败", 1, 0, $input["Version"]);
        }

        //用户登陆数据
        $info   = array(
            "lastIP"        => $ip,
            "lastLogin"     => time(),
            "lastGameId"    => $data["gid"],
            "lastAgent"     => $data["agent"]
        );

        //判断是否更新最后登录数据成功
        $res = D("Api/User")->saveUser($info, $user["userCode"]);
        if (!$res) {
            $res = array(
                "Msg"   => "用户数据异常！请重新登陆！",
            );
            $this->returnMsg($res, 5, $input["Gid"], "更新最后登录数据失败", 1, 0, $input["Version"]);
        }

        //设备添加最后登录时间
        D("Api/Device")->saveDeviceByUdid(array("lastLogin" => time()), $data["udid"]);
        D("Api/DeviceGame")->saveDeviceGame(array("lastLogin" => time()), $data["udid"], $data["gid"]);

        //更新用户游戏表
        $user_game = array(
            "userCode"      => $user["userCode"],
            "userName"      => $user['userName'],
            "game_id"       => $data["gid"],
            "channel_id"    => $agent["channel_id"],
            "agent"         => $data["agent"],
            "ip"            => $ip,
            "city"          => $area["city"],
            "province"      => $area["province"],
            "createTime"    => time(),
            "udid"          => $data["udid"],
            "idfa"          => $data["idfa"],
            "imei"          => empty($data["imei"])? (empty($data["imei2"]? "": $data["imei2"])): $data["imei"],
            "imei2"         => empty($data["imei"])? "": (empty($data["imei2"])? "": $data["imei2"]),
            "device_id"     => $device["id"],
            "type"          => $data["type"],
            "ver"           => $input["Version"],
            "lastIP"        => $ip,
            "lastLogin"     => time(),
            "lastGameId"    => $data["gid"],
            "lastAgent"     => $data["agent"]
        );
        D("Api/UserGame")->addUserGame($user_game);

        //IOS用户添加
        if($data['type'] == 2 && isset($key) && $key == 1){
            $iosUser = $user_game;

            $iosUser['idfv']        = $data['idfv'];
            $iosUser["systemId"]    = $data["systemId"];
            $iosUser["systemInfo"]  = $data["systemInfo"];
            $iosUser["net"]         = $data["net"];
            $iosUser["mac"]         = $data["mac"];
            $iosUser['serial']      = $data["serial"];
            D("Api/IOSMatch")->registerMatch($iosUser);
            unset($iosUser);
        }

        //获取母包的渠道信息
        if ($agent["pid"] > 0) {
            $agent_parent   = D("Api/Agent")->getAgentById($agent["pid"]);
        } else {
            $agent_parent   = $agent;
        }

        //如果未存在，则添加渠道
        if (!D("Api/UserAgent")->getUserAgent(array("agent" => $agent_parent["agent"], "userCode" => $user["userCode"]))) {
            $user_agent             = $user_game;
            $user_agent["regAgent"] = $device["agent"];
            $user_agent["agent"]    = $agent_parent["agent"];
            unset($user_agent["ver"], $user_agent["lastIP"], $user_agent["lastLogin"], $user_agent["lastGameId"], $user_agent["lastAgent"]);
            if (D("Api/UserAgent")->getUserAgent(array("agent" => $agent_parent["agent"], "udid" => $data["udid"]))) {
                $user_agent["solo"] = 0;
            } else {
                $user_agent["solo"] = 1;
            }

            D("Api/UserAgent")->addUserAgent($user_agent);

//            //投放配置参数
//            $adv_param  = D("Api/AdverParam")->getAdverParam($data["agent"]);
//            if ($adv_param) {
//                $user_agent["method"]   = "code";
//                foreach ($adv_param as $adv_value) {
//                    if ($adv_value) D("Api/SdkAdver")->submit($user_agent, $data, $adv_value, 1);
//                }
//            }
        }

        //用户登陆LOG数据
        $log = array(
            "userCode"          => $user["userCode"],
            "channelUserCode"   => $user["channelUserCode"],
            "udid"              => $data["udid"],
            "mac"               => $data["mac"],
            "idfv"              => $data["idfv"],
            "idfa"              => $data["idfa"],
            "imei"              => empty($data["imei"])? (empty($data["imei2"]? "": $data["imei2"])): $data["imei"],
            "imei2"             => empty($data["imei"])? "": (empty($data["imei2"])? "": $data["imei2"]),
            "type"              => $data["type"],
            "agent"             => $data["agent"],
            "game_id"           => $data["gid"],
            "channel_id"        => $agent["channel_id"],
            "ver"               => $input["Version"],
            "time"              => time(),
            "ip"                => $ip,
            "city"              => $area["city"],
            "province"          => $area["province"],
            "net"               => $data["net"],
            "regAgent"          => $user["agent"],
            "regTime"           => $user["createTime"]
        );
        //添加登陆LOG日志
        D("Api/Login")->addLog($log);

        //用户首次注册报送热云记录
        $ry_data = array(
            "userCode"      => $user["userCode"],
            "userName"      => $user['userName'],
            "gid"           => $data["gid"],
            "idfa"          => $data['idfa'],
            "idfv"          => $data['idfv'],
            "channel_id"    => $user['channel_id'],
            "agent"         => $data["agent"],
            "ip"            => $ip,
            "createTime"    => time(),
            "udid"          => $data["udid"],
            "device_id"     => $device["id"],
            "type"          => $data["type"],
            "lastIP"        => $ip
        );
        $this->ryRegistReport($ry_data);

        //登陆成功
        $res = array(
            "Msg"           => "登陆成功！",
            "UserCode"      => $user["userCode"],                                       //返回用户的唯一标识符
            "UserName"      => $data["mobile"],                                         //返回用户的账号名称
            "OneKey"        => in_array($user["oneKey"], array(1, 3, 4, 5))? 0: 1,      //是否是一键注册用户，0：是，1：否
            "LoginToken"    => $loginToken,                                             //返回用户的二登验证TOKEN
            "SecretToken"   => $secretToken,                                            //返回用户的交互加密TOKEN
            "Mobile"        => $user["mobileStatus"],                                   //手机是否绑定，0：已绑定，1：未绑定
            "IDCard"        => $user["IDCardStatus"],                                   //身份证是否绑定，0：已绑定，1：未绑定
            "MobileNum"     => $user["mobile"],                                         //手机号码
            "AccountName"   => $user["userName"],                                       //用户账号
            "Register"      => (isset($key) && $key == 1)? 4: 0                         //是否为注册，0：不是，1：手机注册，2：账号注册，3：一键注册，4：手机验证码注册，5：快速游戏注册，6：融合注册
        );
        $this->returnMsg($res, 0, $input["Gid"], "登陆成功", 1, 0, $input["Version"]);
    }

    /**
     * 快速游戏
     */
    public function FastLogin()
    {
        //获取数据
        $input  = $this->getInput("post", "trim");

        //解密出来的数据
        $data   = $this->getDecrypt($input);

        //判断必要数据是否存在
        if (!$data["gid"] || !$data["udid"] || !$data["agent"]) {
            $res = array(
                "Msg" => "数据错误！"
            );
            $this->returnMsg($res, 4, $input["Gid"], "详细数据缺失！", 1, 0, $input["Version"]);
        }

        //获取渠道号信息
        $agent = D("Api/Agent")->getAgent($data["agent"]);
        if (!$agent || $agent["game_id"] != $data["gid"]) {
            $res = array(
                "Msg" => "数据异常！请重新登陆！"
            );
            $this->returnMsg($res, 6, $input["Gid"], "无该渠道号", 1, 0, $input["Version"]);
        }

        //获取母包的渠道信息
        if ($agent["pid"] > 0) {
            $agentParent    = D("Api/Agent")->getAgentById($agent["pid"]);
        } else {
            $agentParent    = $agent;
        }
        if (!$agentParent) {
            $res = array(
                "Msg" => "数据异常！请重新登陆！"
            );
            $this->returnMsg($res, 6, $input["Gid"], "无该母包渠道号", 1, 0, $input["Version"]);
        }

        //获取渠道
        $channel = D("Api/Channel")->getChannel($agent["channel_id"]);

        //IP地址
        $ip     = get_ip_address();
        $area   = ip_to_location($ip);

        //获取设备信息
        $device = D("Api/Device")->getDeviceByUdid($data["udid"]);
        if (!$device) {
//            $res = array(
//                "Msg" => "数据异常！请重新打开游戏！"
//            );
//            $this->returnMsg($res, 6, $input["Gid"], "用户未初始化！", 1, 0, $input["Version"]);

            //添加初始化日志
            $log    = array(
                "udid"          => $data["udid"],
                "game_id"       => $data["gid"],
                "channel_id"    => $agent["channel_id"],
                "agent"         => $data["agent"],
                "mac"           => $data["mac"],
                "idfa"          => $data["idfa"],
                "idfv"          => $data["idfv"],
                "serial"        => $data["serial"],
                "imei"          => empty($data["imei"])? (empty($data["imei2"]? "": $data["imei2"])): $data["imei"],
                "imei2"         => empty($data["imei"])? "": (empty($data["imei2"])? "": $data["imei2"]),
                "systemId"      => $data["systemId"],
                "systemInfo"    => $data["systemInfo"],
                "screen"        => $data["screen"],
                "net"           => $data["net"],
                "type"          => $data["type"],
                "ip"            => $ip,
                "city"          => $area["city"],
                "province"      => $area["province"],
                "ver"           => $input["Version"],
                "gameVer"       => $data["gameVer"],
                "time"          => time()
            );
            D("Api/Init")->addLog($log);

            //组装设备数据
            $device = $log;
            unset($device["time"], $device["net"], $device["gameVer"]);
            $device["createTime"] = $device["lastInit"] = time();

            //存储设备数据
            D("Api/Device")->addDevice($device);

            //记录热云每款游戏首次打开
            $this->ryOpenReport($device);

            //获取设备信息
            $device_info = D("Api/Device")->getDeviceByUdid($data["udid"]);

            if (!$device_info) {
                $res = array(
                    "Msg" => "初始化失败！"
                );
                $this->returnMsg($res, 5, $input["Gid"], "初始化失败！", 1, 0, $input["Version"]);
            }

            //添加设备游戏表的数据
            D("Api/DeviceGame")->addDeviceGame($device);

            $device = $device_info;
        }

        //获取游戏信息
        $game = D("Api/Game")->getGame($data["gid"]);
        if (!$game) {
            $res = array(
                "Msg" => "数据异常！请重新登陆！"
            );
            $this->returnMsg($res, 6, $input["Gid"], "无该游戏！", 1, 0, $input["Version"]);
        }

        //获取之前快速登陆的账号
        $fastUser = D("Api/FastLogin")->getFastLogin($data["udid"], $agentParent["agent"]);
        //判断是否存在
        if ($fastUser) {
            $userCode   = $fastUser["userCode"];
            //获取用户信息
            $user       = D("Api/User")->getUser(array("userCode" => $userCode));

            //账户状态判断
            if (!$user["mobileStatus"]) {
                //已绑定手机
                $res = array(
                    "Msg"           => "您已绑定手机！",
                    "LoginStatus"   => 1,                                                       //登陆状态，0：快速登陆，1：请用手机登陆，2：请用账号登陆
                    "MobileNum"     => $user["mobile"]                                          //手机号码
                );
                $this->returnMsg($res, 0, $input["Gid"], "请用手机登陆", 1, 0, $input["Version"]);
            }
            if ($user["oneKey"] == 2) {
                //已绑定账号
                $res = array(
                    "Msg"           => "您已绑定账号！",
                    "LoginStatus"   => 2,                                                       //登陆状态，0：快速登陆，1：请用手机登陆，2：请用账号登陆
                    "AccountName"   => $user["userName"]                                        //用户账号
                );
                $this->returnMsg($res, 0, $input["Gid"], "请用手机登陆", 1, 0, $input["Version"]);
            }
        } else {
            //判断是否可以注册
            if ($device["loginStatus"] > 0 || $game["loginStatus"] > 0 || $agent["loginStatus"] > 0) {
                $res = array(
                    "Msg" => "无法注册！请您联系客服人员！"
                );
                $this->returnMsg($res, 5, $input["Gid"], "关闭新增！", 1, 0, $input["Version"]);
            }

            //用户注册数据
            $info   = array(
                "password"      => make_password(uniqid(true)),
                "game_id"       => $data["gid"],
                "gameName"      => $game["gameName"],
                "channel_id"    => $agent["channel_id"],
                "channelName"   => $channel["channelName"],
                "agent"         => $data["agent"],
                "ip"            => $ip,
                "city"          => $area["city"],
                "province"      => $area["province"],
                "createTime"    => time(),
                "udid"          => $data["udid"],
                "idfa"          => $data["idfa"],
                "imei"          => empty($data["imei"])? (empty($data["imei2"]? "": $data["imei2"])): $data["imei"],
                "imei2"         => empty($data["imei"])? "": (empty($data["imei2"])? "": $data["imei2"]),
                "device_id"     => $device["id"],
                "type"          => $data["type"],
                "oneKey"        => 5,
                "register"      => 5,
                "ver"           => $input["Version"]
            );

            //注册是否成功
            $key        = 0;
            //用户的唯一标识码
            $userCode   = "";

            //循环注册，避免唯一标识符重复导致注册失败
            for ($i = 0; $i < 5; $i ++) {
                $info["channelUserName"]    = $info["channelUserCode"] = $info["userCode"] = make_user_code();
                $info["userName"]           = C("COMPANY_CODE").time().substr(uniqid(true), -5);

                //提审包注册的用户特殊标记
                if ($agent["trialVer"] && $data["gameVer"] == $agent["trialVer"]) {
                    $info["channelUserCode"] .= "_tishen";
                }

                if (D("Api/User")->addUser($info)) {
                    $key        = 1;
                    $userCode   = $info["userCode"];
                    break;
                }
            }

            //判断是否注册成功
            if ($key != 1) {
                //注册失败
                $res = array(
                    "Msg"   => "注册失败！",
                );
                $this->returnMsg($res, 5, $input["Gid"], "注册失败", 1, 0, $input["Version"]);
            }

            //获取用户信息
            $user = D("Api/User")->getUser(array("userCode" => $userCode));

            //报错快速游戏的账号
            if (!D("Api/FastLogin")->addFastLogin($data["udid"], $agentParent["agent"], $userCode)) {
                //注册失败
                $res = array(
                    "Msg"   => "快速游戏注册失败！",
                );
                $this->returnMsg($res, 5, $input["Gid"], "快速游戏注册失败", 1, 0, $input["Version"]);
            }
        }

        //判断是否可以登陆
        if ($device["loginStatus"] == 1 || $game["loginStatus"] == 1 || $agent["loginStatus"] == 1) {
            $res = array(
                "Msg" => "无法登陆！请您联系客服人员！"
            );
            $this->returnMsg($res, 5, $input["Gid"], "关闭登陆！", 1, 0, $input["Version"]);
        }

        //判断用户是否可以登陆
        if ($user["status"] == 1) {
            $res = array(
                "Msg" => "无法登陆！请您联系客服人员！"
            );
            $this->returnMsg($res, 5, $input["Gid"], "用户关闭登陆！", 1, 0, $input["Version"]);
        }

        //提审包，用户的创建渠道号跟包的渠道号不一致
        if (($data["gameVer"] == $agent["trialVer"]) && ($user["agent"] != $data["agent"])) {
            $res = array(
                "Msg" => "错误码：042801"
            );
            $this->returnMsg($res, 5, $input["Gid"], "错误码：042801！", 1, 0, $input["Version"]);
        }
        //提审用户，用户登陆的渠道号跟包的渠道号不一致
        if ((substr($user["channelUserCode"], -7) == "_tishen") && ($user["agent"] != $data["agent"])) {
            $res = array(
                "Msg" => "错误码：042802"
            );
            $this->returnMsg($res, 5, $input["Gid"], "错误码：042802！", 1, 0, $input["Version"]);
        }

        //生成登陆、二登验证TOKEN
        $loginToken     = make_random(24);
        $secretToken    = make_random(24);

        //组装TOKEN数据
        $token = array(
            "userCode"      => $user["userCode"],
            "game_id"       => $data["gid"],
            "loginToken"    => $loginToken,
            "secretToken"   => $secretToken,
            "loginTime"     => time()
        );

        //更新TOKEN
        if (!D("Api/TokenGame")->addToken($token)) {
            $res = array(
                "Msg" => "获取数据失败！请重新登陆！"
            );
            $this->returnMsg($res, 5, $input["Gid"], "更新TOKEN失败", 1, 0, $input["Version"]);
        }

        //用户登陆数据
        $info   = array(
            "lastIP"        => $ip,
            "lastLogin"     => time(),
            "lastGameId"    => $data["gid"],
            "lastAgent"     => $data["agent"]
        );

        //判断是否更新最后登录数据成功
        $res = D("Api/User")->saveUser($info, $user["userCode"]);
        if (!$res) {
            $res = array(
                "Msg"   => "用户数据异常！请重新登陆！",
            );
            $this->returnMsg($res, 5, $input["Gid"], "更新最后登录数据失败", 1, 0, $input["Version"]);
        }

        //设备添加最后登录时间
        D("Api/Device")->saveDeviceByUdid(array("lastLogin" => time()), $data["udid"]);
        D("Api/DeviceGame")->saveDeviceGame(array("lastLogin" => time()), $data["udid"], $data["gid"]);

        //更新用户游戏表
        $user_game = array(
            "userCode"      => $user["userCode"],
            "userName"      => $user['userName'],
            "game_id"       => $data["gid"],
            "channel_id"    => $agent["channel_id"],
            "agent"         => $data["agent"],
            "ip"            => $ip,
            "city"          => $area["city"],
            "province"      => $area["province"],
            "createTime"    => time(),
            "udid"          => $data["udid"],
            "idfa"          => $data["idfa"],
            "imei"          => empty($data["imei"])? (empty($data["imei2"]? "": $data["imei2"])): $data["imei"],
            "imei2"         => empty($data["imei"])? "": (empty($data["imei2"])? "": $data["imei2"]),
            "device_id"     => $device["id"],
            "type"          => $data["type"],
            "ver"           => $input["Version"],
            "lastIP"        => $ip,
            "lastLogin"     => time(),
            "lastGameId"    => $data["gid"],
            "lastAgent"     => $data["agent"]
        );
        D("Api/UserGame")->addUserGame($user_game);

        //IOS用户添加
        if($data['type'] == 2 && isset($key) && $key == 1){
            $iosUser = $user_game;

            $iosUser['idfv']        = $data['idfv'];
            $iosUser["systemId"]    = $data["systemId"];
            $iosUser["systemInfo"]  = $data["systemInfo"];
            $iosUser["net"]         = $data["net"];
            $iosUser["mac"]         = $data["mac"];
            $iosUser['serial']      = $data["serial"];
            D("Api/IOSMatch")->registerMatch($iosUser);
            unset($iosUser);
        }

        //获取母包的渠道信息
        if ($agent["pid"] > 0) {
            $agent_parent   = D("Api/Agent")->getAgentById($agent["pid"]);
        } else {
            $agent_parent   = $agent;
        }

        //如果未存在，则添加渠道
        if (!D("Api/UserAgent")->getUserAgent(array("agent" => $agent_parent["agent"], "userCode" => $user["userCode"]))) {
            $user_agent             = $user_game;
            $user_agent["regAgent"] = $device["agent"];
            $user_agent["agent"]    = $agent_parent["agent"];
            unset($user_agent["ver"], $user_agent["lastIP"], $user_agent["lastLogin"], $user_agent["lastGameId"], $user_agent["lastAgent"]);
            if (D("Api/UserAgent")->getUserAgent(array("agent" => $agent_parent["agent"], "udid" => $data["udid"]))) {
                $user_agent["solo"] = 0;
            } else {
                $user_agent["solo"] = 1;
            }

            D("Api/UserAgent")->addUserAgent($user_agent);

//            //投放配置参数
//            $adv_param  = D("Api/AdverParam")->getAdverParam($data["agent"]);
//            if ($adv_param) {
//                $user_agent["method"]   = "fast";
//                foreach ($adv_param as $adv_value) {
//                    if ($adv_value) D("Api/SdkAdver")->submit($user_agent, $data, $adv_value, 1);
//                }
//            }
        }

        //用户登陆LOG数据
        $log = array(
            "userCode"          => $user["userCode"],
            "channelUserCode"   => $user["channelUserCode"],
            "udid"              => $data["udid"],
            "mac"               => $data["mac"],
            "idfv"              => $data["idfv"],
            "idfa"              => $data["idfa"],
            "imei"              => empty($data["imei"])? (empty($data["imei2"]? "": $data["imei2"])): $data["imei"],
            "imei2"             => empty($data["imei"])? "": (empty($data["imei2"])? "": $data["imei2"]),
            "type"              => $data["type"],
            "agent"             => $data["agent"],
            "game_id"           => $data["gid"],
            "channel_id"        => $agent["channel_id"],
            "ver"               => $input["Version"],
            "time"              => time(),
            "ip"                => $ip,
            "city"              => $area["city"],
            "province"          => $area["province"],
            "net"               => $data["net"],
            "regAgent"          => $user["agent"],
            "regTime"           => $user["createTime"]
        );
        //添加登陆LOG日志
        D("Api/Login")->addLog($log);

        //用户首次注册报送热云记录
        $ry_data = array(
            "userCode"      => $user["userCode"],
            "userName"      => $user['userName'],
            "gid"           => $data["gid"],
            "idfa"          => $data['idfa'],
            "idfv"          => $data['idfv'],
            "channel_id"    => $user['channel_id'],
            "agent"         => $data["agent"],
            "ip"            => $ip,
            "createTime"    => time(),
            "udid"          => $data["udid"],
            "device_id"     => $device["id"],
            "type"          => $data["type"],
            "lastIP"        => $ip
        );
        $this->ryRegistReport($ry_data);

        //登陆成功
        $res = array(
            "Msg"           => "登陆成功！",
            "LoginStatus"   => 0,                                                       //登陆状态，0：快速登陆，1：请用手机登陆，2：请用账号登陆
            "UserCode"      => $user["userCode"],                                       //返回用户的唯一标识符
            "UserName"      => $user["userName"],                                       //返回用户的账号名称
            "OneKey"        => in_array($user["oneKey"], array(1, 3, 4, 5))? 0: 1,      //是否是一键注册用户，0：是，1：否
            "LoginToken"    => $loginToken,                                             //返回用户的二登验证TOKEN
            "SecretToken"   => $secretToken,                                            //返回用户的交互加密TOKEN
            "Mobile"        => $user["mobileStatus"],                                   //手机是否绑定，0：已绑定，1：未绑定
            "IDCard"        => $user["IDCardStatus"],                                   //身份证是否绑定，0：已绑定，1：未绑定
            "MobileNum"     => $user["mobile"],                                         //手机号码
            "AccountName"   => $user["userName"],                                       //用户账号
            "Register"      => (isset($key) && $key == 1)? 5: 0                         //是否为注册，0：不是，1：手机注册，2：账号注册，3：一键注册，4：手机验证码注册，5：快速游戏注册，6：融合注册
        );
        $this->returnMsg($res, 0, $input["Gid"], "登陆成功", 1, 0, $input["Version"]);
    }

    /**
     * 添加角色信息接口
     */
    public function Role()
    {
        //获取数据
        $input  = $this->getInput("post", "trim");

        //判断用户ID是否存在
        if (!$input["Uid"]) {
            $res = array(
                "Msg" => "数据异常！请重新请求！"
            );
            $this->returnMsg($res, 4, $input["Gid"], "无UID", 1, 0, $input["Version"]);
        }

        //解密出来的数据
        $data   = $this->getDecrypt($input);

        //判断必要数据是否存在
        if (!$data["userCode"] || !$data["gid"] || !$data["agent"] || !$data["udid"] || (!$data["roleId"] && $data["sceneId"] != "selectServer")) {
            $res = array(
                "Msg" => "数据异常！请重新登陆！"
            );
            $this->returnMsg($res, 4, $input["Gid"], "必要数据缺失！", 0, $input["Uid"], $input["Version"]);
        }

        //获取设备信息
        $device = D("Api/Device")->getDeviceByUdid($data["udid"]);
        if (!$device) {
            $res = array(
                "Msg" => "数据异常！请重新打开游戏！"
            );
            $this->returnMsg($res, 6, $input["Gid"], "用户未初始化！", 0, $input["Uid"], $input["Version"]);
        }

        //获取游戏信息
        $game = D("Api/Game")->getGame($data["gid"]);
        if (!$game) {
            $res = array(
                "Msg" => "数据异常！请重新登陆！"
            );
            $this->returnMsg($res, 6, $input["Gid"], "无该游戏！", 0, $input["Uid"], $input["Version"]);
        }

        //获取渠道号信息
        $agent = D("Api/Agent")->getAgent($data["agent"]);
        if (!$agent || $agent["game_id"] != $data["gid"]) {
            $res = array(
                "Msg" => "数据异常！请重新登陆！"
            );
            $this->returnMsg($res, 6, $input["Gid"], "无该渠道号", 0, $input["Uid"], $input["Version"]);
        }

        //获取用户信息
        $user = D("Api/User")->getUserByCode($data["userCode"]);
        if (!$user) {
            $res = array(
                "Msg" => "数据异常！请重新登录！"
            );
            $this->returnMsg($res, 6, $input["Gid"], "无该用户", 0, $input["Uid"], $input["Version"]);
        }

        //获取用户游戏信息
        $user_info = D("Api/UserGame")->getUserInfo(array("userCode" => $data["userCode"], "game_id" => $data["gid"]));

        //存储创建时的区服ID
        if (!$user_info["serverId"]) D("Api/UserGame")->saveUserGame(array("serverId" => $data["serverId"]), $data["userCode"], $data["gid"]);

        //选择服务器的数据另存
        if ($data["sceneId"] == "selectServer") {
            $log = array(
                "userCode"          => $data["userCode"],
                "agent"             => $data["agent"],
                "regAgent"          => $user_info["agent"],
                "udid"              => $data["udid"],
                "game_id"           => $data["gid"],
                "serverId"          => $user_info["serverId"]? $user_info["serverId"]: $data["serverId"],
                "selectServerId"    => $data["serverId"],
                "serverName"        => $data["serverName"],
                "time"              => time(),
                "regTime"           => $user_info["createTime"]
            );
            D("Api/SelectServer")->addLog($log);
            $res = array(
                "Msg" => "选服信息获取成功！"
            );
            $this->returnMsg($res, 0, $input["Gid"], "选服信息获取成功！", 0, $input["Uid"], $input["Version"]);
        }

        //角色信息数据
        $role = array(
            "userCode"      => $data["userCode"],
            "agent"         => $data["agent"],
            "udid"          => $data["udid"],
            "game_id"       => $data["gid"],
            "roleId"        => $data["roleId"],
            "roleName"      => $data["roleName"],
            "serverId"      => $data["serverId"],
            "serverName"    => $data["serverName"],
            "level"         => $data["level"],
            "currency"      => $data["currency"],
            "vip"           => $data["vip"],
            "balance"       => $data["balance"],
            "power"         => $data["power"],
            "processId"     => $data["processId"],
            "scene"         => $data["sceneId"],
            "createTime"    => time(),
            "updateTime"    => time()
        );

        //安卓融合的热云游戏报送
        if ($agent["gameType"] == 1 && $agent["channel_id"] > 1 && ($data["sceneId"] == "enterServer" || $data["sceneId"] == "createRole")) {
            $gameReyun                  = $role;
            $gameReyun["channelCode"]   = $user["channelUserCode"];
            $gameReyun["systemId"]      = $data["systemId"];
            $gameReyun["ip"]            = get_client_ip();
            $gameReyun["imei"]          = empty($data["imei"])? (empty($data["imei2"]? "": $data["imei2"])): $data["imei"];
            if (!D("Api/Role")->getRole(array("userCode" => $data["userCode"], "game_id" => $data["gid"], "roleId" => $data["roleId"]))) {
                //初次进服报送
                D("Api/ANDMatch")->gameReyunReport($gameReyun, 3);
            } else {
                //每次进服报送
                D("Api/ANDMatch")->gameReyunReport($gameReyun, 4);
            }
        }

        //添加角色
        if (!D("Api/Role")->addRole($role)) {
            $res = array(
                "Msg" => "添加角色信息失败！"
            );
            $this->returnMsg($res, 5, $input["Gid"], "添加角色信息失败！", 0, $input["Uid"], $input["Version"]);
        }

        //记录角色登陆表
        if ($data["sceneId"] == "enterServer" || $data["sceneId"] == "createRole" || $data["gid"] == "103") {
            $login = $role;
            $login["time"]          = $login["createTime"];
            $login["serverId"]      = $user_info["serverId"]? $user_info["serverId"]: $role["serverId"];
            $login["regTime"]       = $user_info["createTime"];
            $login["regAgent"]      = $user_info["agent"];
            $login["loginServerId"] = $data["serverId"];
            unset($login["createTime"], $login["updateTime"]);

            D("Api/RoleLogin")->addLogin($login);

            //IOS用户登陆添加
            if($data['type'] == 2){
                $ip     = get_ip_address();
                $area   = ip_to_location($ip);

                $iosUserLog = $login;

                $iosUserLog['idfa']        = $data['idfa'];
                $iosUserLog['idfv']        = $data['idfv'];
                $iosUserLog['imei']        = $data['imei'];
                $iosUserLog['ver']         = $data['ver'];
                $iosUserLog['mac']         = $data['mac'];
                $iosUserLog['net']         = $data['net'];
                $iosUserLog['city']        = $area['city'];
                $iosUserLog['province']    = $area['province'];
                $iosUserLog["systemId"]    = $data["systemId"];
                $iosUserLog["systemInfo"]  = $data["systemInfo"];
                $iosUserLog['serial']      = $data["serial"];
                D("Api/IOSMatch")->loginMatch($iosUserLog);
                unset($iosUserLog);
            }
        }

        $res = array(
            "Msg" => "添加角色信息成功！"
        );
        $this->returnMsg($res, 0, $input["Gid"], "添加角色信息成功！", 0, $input["Uid"], $input["Version"]);
    }

    /**
     * 融合登陆接口
     */
    public function FuseLogin()
    {
        //获取数据
        $input  = $this->getInput("post", "trim");

        //解密出来的数据
        $data   = $this->getDecrypt($input);

        //判断必要数据是否存在
        if (!$data["gid"] || !$data["udid"] || !$data["agent"]) {
            $res = array(
                "Msg" => "数据错误！请重新登陆！"
            );
            $this->returnMsg($res, 4, $input["Gid"], "详细数据缺失！", 1, 0, $input["Version"]);
        }

        //获取设备信息
        $device = D("Api/Device")->getDeviceByUdid($data["udid"]);
        if (!$device) {
            $res = array(
                "Msg" => "数据异常！请重新打开游戏！"
            );
            $this->returnMsg($res, 6, $input["Gid"], "用户未初始化！", 1, 0, $input["Version"]);
        }

        //获取游戏信息
        $game = D("Api/Game")->getGame($data["gid"]);
        if (!$game) {
            $res = array(
                "Msg" => "数据异常！请重新登陆！"
            );
            $this->returnMsg($res, 6, $input["Gid"], "无该游戏！", 1, 0, $input["Version"]);
        }

        //获取渠道号信息
        $agent = D("Api/Agent")->getAgent($data["agent"]);
        if (!$agent || $agent["game_id"] != $data["gid"]) {
            $res = array(
                "Msg" => "数据异常！请重新登陆！"
            );
            $this->returnMsg($res, 6, $input["Gid"], "无该渠道号", 1, 0, $input["Version"]);
        }

        //是否为融合渠道
        if ($agent["channel_id"] <= 1) {
            $res = array(
                "Msg" => "接口错误！"
            );
            $this->returnMsg($res, 6, $input["Gid"], "非渠道接口", 1, 0, $input["Version"]);
        }

        $channel = D("Api/Channel")->getChannel($agent["channel_id"]);
        if (!$channel) {
            $res = array(
                "Msg" => "数据异常！请重新登陆！"
            );
            $this->returnMsg($res, 6, $input["Gid"], "无该渠道", 1, 0, $input["Version"]);
        }

        //判断是否可以登陆
        if ($device["loginStatus"] == 1 || $game["loginStatus"] == 1 || $agent["loginStatus"] == 1) {
            $res = array(
                "Msg" => "无法登陆！请您联系客服人员！"
            );
            $this->returnMsg($res, 5, $input["Gid"], "关闭登陆！", 1, 0, $input["Version"]);
        }

        //二登验证
        $channel_name   = ucfirst($channel["channelAbbr"]).($agent["channelVer"] > 1? $agent["channelVer"]: "");
        $mix_login      = D("Fusion/".$channel_name)->loginCheck($data);
        if (!$mix_login["Result"]) {
            //二登验证失败
            $res = array(
                "Msg" => "登陆失败！请你重新登陆！"
            );
            $this->returnMsg($res, 5, $input["Gid"], "二登验证失败！", 1, 0, $input["Version"]);
        }

        //获取用户信息
        $param = $mix_login["Data"];

        //搜索条件
        $map                    = array();
        $map["channelUserCode"] = $param["channelUserCode"];
        $map["channel_id"]      = $agent["channel_id"];

        //获取用户数据
        $user   = D("Api/User")->getUser($map);

        //IP地址
        $ip     = get_ip_address();
        $area   = ip_to_location($ip);

        //判断用户数据是否存在
        if (!$user) {
            //判断是否可以注册
            if ($device["loginStatus"] > 0 || $game["loginStatus"] > 0 || $agent["loginStatus"] > 0) {
                $res = array(
                    "Msg" => "无法注册！请您联系客服人员！"
                );
                $this->returnMsg($res, 5, $input["Gid"], "关闭新增！", 1, 0, $input["Version"]);
            }

            //用户注册数据
            $info   = array(
                "userName"          => $param["channelUserCode"]."_".$channel["channelAbbr"],
                "channelUserCode"   => $param["channelUserCode"],
                "channelUserName"   => $param["channelUserName"]? $param["channelUserName"]: $param["channelUserCode"],
                "password"          => make_password(md5(uniqid())),
                "game_id"           => $data["gid"],
                "gameName"          => $game["gameName"],
                "channel_id"        => $agent["channel_id"],
                "channelName"       => $channel["channelName"],
                "agent"             => $data["agent"],
                "ip"                => $ip,
                "city"              => $area["city"],
                "province"          => $area["province"],
                "createTime"        => time(),
                "udid"              => $data["udid"],
                "idfa"              => $data["idfa"],
                "imei"              => empty($data["imei"])? (empty($data["imei2"]? "": $data["imei2"])): $data["imei"],
                "imei2"             => empty($data["imei"])? "": (empty($data["imei2"])? "": $data["imei2"]),
                "device_id"         => $device["id"],
                "type"              => $data["type"],
                "oneKey"            => 2,
                "register"          => 2,
                "ver"               => $input["Version"]
            );

            //注册是否成功
            $key    = 0;
            //用户的唯一标识码
            $code   = "";

            //循环注册，避免唯一标识符重复导致注册失败
            for ($i = 0; $i < 5; $i ++) {
                $info["userCode"] = make_user_code();
                if (D("Api/User")->addUser($info)) {
                    $key    = 1;
                    $code   = $info["userCode"];
                    break;
                }
            }

            //判断是否注册成功
            if ($key != 1) {
                //注册失败
                $res = array(
                    "Msg"   => "注册失败！",
                );
                $this->returnMsg($res, 5, $input["Gid"], "注册失败", 1, 0, $input["Version"]);
            }
        } else {
            //判断用户是否可以登陆
            if ($user["status"] == 1 && (!$user["allowLoginTime"] || $user["allowLoginTime"] > time())) {
                $res = array(
                    "Msg" => "无法登陆！请您联系客服人员！"
                );
                $this->returnMsg($res, 5, $input["Gid"], "用户关闭登陆！", 1, 0, $input["Version"]);
            }

            $code = $user["userCode"];
        }

        //生成登陆、二登验证TOKEN
        $loginToken     = make_random(24);
        $secretToken    = make_random(24);

        //组装TOKEN数据
        $token = array(
            "userCode"      => $code,
            "game_id"       => $data["gid"],
            "loginToken"    => $loginToken,
            "secretToken"   => $secretToken,
            "loginTime"     => time()
        );

        //更新TOKEN
        if (!D("Api/TokenGame")->addToken($token)) {
            $res = array(
                "Msg" => "获取数据失败！请重新登陆！"
            );
            $this->returnMsg($res, 5, $input["Gid"], "更新TOKEN失败", 1, 0, $input["Version"]);
        }

        //用户登陆数据
        $info   = array(
            "lastIP"        => $ip,
            "lastLogin"     => time(),
            "lastGameId"    => $data["gid"],
            "lastAgent"     => $data["agent"]
        );

        //判断是否更新最后登录数据成功
        D("Api/User")->saveUser($info, $code);

        //设备添加最后登录时间
        D("Api/Device")->saveDeviceByUdid(array("lastLogin" => time()), $data["udid"]);
        D("Api/DeviceGame")->saveDeviceGame(array("lastLogin" => time()), $data["udid"], $data["gid"]);

        //更新用户游戏表
        $user_game = array(
            "userCode"      => $code,
            "userName"      => $user? $user['userName']: $param["channelUserCode"]."_".$channel["channelAbbr"],
            "game_id"       => $data["gid"],
            "channel_id"    => $agent["channel_id"],
            "agent"         => $data["agent"],
            "ip"            => $ip,
            "city"          => $area["city"],
            "province"      => $area["province"],
            "createTime"    => time(),
            "udid"          => $data["udid"],
            "idfa"          => $data["idfa"],
            "imei"          => empty($data["imei"])? (empty($data["imei2"]? "": $data["imei2"])): $data["imei"],
            "imei2"         => empty($data["imei"])? "": (empty($data["imei2"])? "": $data["imei2"]),
            "device_id"     => $device["id"],
            "type"          => $data["type"],
            "ver"           => $input["Version"],
            "lastIP"        => $ip,
            "lastLogin"     => time(),
            "lastGameId"    => $data["gid"],
            "lastAgent"     => $data["agent"]
        );
        D("Api/UserGame")->addUserGame($user_game);

        //获取母包的渠道信息
        if ($agent["pid"] > 0) {
            $agent_parent   = D("Api/Agent")->getAgentById($agent["pid"]);
        } else {
            $agent_parent   = $agent;
        }

        //如果未存在，则添加渠道
        if (!D("Api/UserAgent")->getUserAgent(array("agent" => $agent_parent["agent"], "userCode" => $code))) {
            $user_agent             = $user_game;
            $user_agent["regAgent"] = $device["agent"];
            $user_agent["agent"]    = $agent_parent["agent"];
            unset($user_agent["ver"], $user_agent["lastIP"], $user_agent["lastLogin"], $user_agent["lastGameId"], $user_agent["lastAgent"]);
            if (D("Api/UserAgent")->getUserAgent(array("agent" => $agent_parent["agent"], "udid" => $data["udid"]))) {
                $user_agent["solo"] = 0;
            } else {
                $user_agent["solo"] = 1;
            }

            D("Api/UserAgent")->addUserAgent($user_agent);

//            //投放配置参数
//            $adv_param  = D("Api/AdverParam")->getAdverParam($data["agent"]);
//            if ($adv_param) {
//                $user_agent["method"]   = "fuse";
//                foreach ($adv_param as $adv_value) {
//                    if ($adv_value) D("Api/SdkAdver")->submit($user_agent, $data, $adv_value, 1);
//                }
//            }
        }

        //用户登陆LOG数据
        $log = array(
            "userCode"          => $code,
            "channelUserCode"   => $param["channelUserCode"],
            "udid"              => $data["udid"],
            "mac"               => $data["mac"],
            "imei"              => empty($data["imei"])? (empty($data["imei2"]? "": $data["imei2"])): $data["imei"],
            "imei2"             => empty($data["imei"])? "": (empty($data["imei2"])? "": $data["imei2"]),
            "type"              => $data["type"],
            "agent"             => $data["agent"],
            "game_id"           => $data["gid"],
            "channel_id"        => $agent["channel_id"],
            "ver"               => $input["Version"],
            "time"              => time(),
            "ip"                => $ip,
            "city"              => $area["city"],
            "province"          => $area["province"],
            "net"               => $data["net"]
        );

        //添加登陆LOG日志
        D("Api/Login")->addLog($log);

        //融合剑雨没传递角色信息的BUG问题临时解决方法！！！
        // if (($input["Version"] == "2.2") && in_array($data["agent"], array("jyjhCOOLPAD", "jyjhUC", "jyjhXIAOMI", "jyjhHUAWEI", "jyjhVIVO", "jyjhOPPO", "jyQIHOO"))) {
        if (
            (($input["Version"] == "2.2" ) && in_array($data["agent"], array("jyjhCOOLPAD", "jyjhUC", "jyjhXIAOMI", "jyjhVIVO", "jyjhOPPO", "jyQIHOO")))
            ||  (($input["Version"] == "2.0" ) && in_array($data["agent"], array( "jyjhHUAWEI")))
        ) {
            //获取用户游戏信息
            $user_info = D("Api/UserGame")->getUserInfo(array("userCode" => $code, "game_id" => $data["gid"]));

            //角色信息数据
            $login = array(
                "userCode"      => $code,
                "agent"         => $data["agent"],
                "udid"          => $data["udid"],
                "game_id"       => $data["gid"],
                "roleId"        => $code,
                "roleName"      => $code,
                "serverId"      => "383001",
                "serverName"    => "1.武当山顶",
                "level"         => "1",
                "currency"      => "1",
                "vip"           => "1",
                "balance"       => "1",
                "power"         => "1",
                "time"          => time(),
                "regTime"       => $user_info["createTime"],
                "regAgent"      => $user_info["agent"],
                "loginServerId" => "383001"
            );

            //添加模拟用户登录的数据
            D("Api/RoleLogin")->addLogin($login);
        }

        //登陆成功
        $res = array(
            "Msg"           => "登陆成功！",
            "UserCode"      => $code,                                                   //返回用户的唯一标识符
            "UserName"      => $param["channelUserCode"]."_".$channel["channelAbbr"],   //返回用户的唯一标识符
            "ChannelCode"   => $param["channelUserCode"],                               //返回用户的渠道唯一标识符
            "OneKey"        => 0,                                                       //是否是一键注册用户，0：是，1：否
            "LoginToken"    => $loginToken,                                             //返回用户的二登验证TOKEN
            "SecretToken"   => $secretToken,                                            //返回用户的交互加密TOKEN
            "ExtInfo"       => $param["extInfo"]? $param["extInfo"]: array(),           //返回融合登陆需要的额外信息
            "Mobile"        => 1,                                                       //手机是否绑定，0：已绑定，1：未绑定
            "IDCard"        => 1,                                                       //身份证是否绑定，0：已绑定，1：未绑定
            "Register"      => (isset($key) && $key == 1)? 6: 0                         //是否为注册，0：不是，1：手机注册，2：账号注册，3：一键注册，4：手机验证码注册，5：快速游戏注册，6：融合注册
        );

        $this->returnMsg($res, 0, $input["Gid"], "登陆成功", 1, 0, $input["Version"]);
    }

    /**
     * 用户首次注册报送热云记录
     */
    protected function ryRegistReport($data){
        if(in_array($data['gid'], $this->ryGameId)){
            $ry_data = array(
                "userCode"      => $data["userCode"],
                "userName"      => $data['userName'],
                "game_id"       => $data["gid"],
                "idfa"          => $data['idfa'],
                "idfv"          => $data['idfv'],
                "channel_id"    => $data['channel_id'],
                "agent"         => $data["agent"],
                "ip"            => $data['ip'],
                "createTime"    => $data['createTime'],
                "udid"          => $data["udid"],
                "device_id"     => $data["device_id"],
                "type"          => $data["type"],
                "lastIP"        => $data['ip']
            );
            D("Api/Device")->addRyRegistReport($ry_data);
        }
    }

    /**
     * 记录热云每款游戏首次打开
     */
    protected function ryOpenReport($data){
        if(in_array($data['game_id'],$this->ryGameId)){
            D("Api/Device")->addRyOpenReport($data);
        }
    }

    /**
     * 崩溃日志接口
     */
    public function CrashLog()
    {
        //获取数据
        $input  = $this->getInput("post", "trim");

        //解密出来的数据
        $data   = $this->getDecrypt($input);

        //判断必要数据是否存在
        if (!$data["log"] && !$data["systemInfo"] && !$data["gid"] && !$data["agent"]) {
            $res = array(
                "Msg" => "获取日志失败！"
            );
            $this->returnMsg($res, 4, $input["Gid"], "获取日志失败！", 1, 0, $input["Version"]);
        }

        log_save("[ip]".get_ip_address()."    [time]".date("Y-m-d H:i:s")."    [device]".$data["systemInfo"]."    [gid]".$data["gid"]."    [agent]".$data["agent"]."    [ver]".$input["Version"]."   [type]".$input["type"]."    [log]".$data["log"], "info", "", "crashLog_".date("Y-m-d"), "CrashLog/".date('Ym')."/");

        $res = array(
            "Msg" => "获取日志成功！"
        );
        $this->returnMsg($res, 0, $input["Gid"], "获取日志成功！", 1, 0, $input["Version"]);
    }
}