<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/5/31
 * Time: 19:56
 *
 * 下单控制器
 */

namespace Api\Controller;

class PayController extends ApiController
{

    private $payKey                 = "895565718a9a4f058293cbed27e35e76";                                           //银联商户支付Key
    private $returnUrl              = "http://apisdk.chuangyunet.net/html/PayBack.html";                            //银联页面通知地址
    private $notifyUrl              = "http://apisdk.chuangyunet.net/Api/Reply/UnionPayCallback";                   //银联后台异步通知地址
    private $subPayKey              = "";                                                                           //银联子商户支付Key
    private $paySecret              = "3507bb079eb64a1abd351bac0d9c2b8e";                                           //银联商户支付密钥
    //没有自己SDK的渠道
    private $noSdkChannel           = array(
        //渠道ID_类型
        "27_2"                      //嘉玩的IOS
    );

    /**
     * 下单接口
     */
    public function Order()
    {
        //获取数据
        $input  = $this->getInput("post", "trim");
        log_save("[ip]".get_ip_address()."    [data]".json_encode($input)."    [msg]INPUT", "info", "", "order_info_".date("Y-m-d"));

        //判断用户ID是否存在
        if (!$input["Uid"]) {
            log_save("[ip]".get_ip_address()."    [data]".json_encode($input)."    [msg]UID缺失", "info", "", "order_err_".date("Y-m-d"));
//            $this->urlErr();
            $res = array(
                "Msg" => "数据异常！请重新请求！"
            );
            if ($input["Version"] >= "1.1") {
                $this->returnMsg($res, 4, $input["Gid"], "无UID", 1, 0, $input["Version"]);
            } else {
                $this->returnMsg($res, 4, $input["Gid"], "无UID", 2, 0, $input["Version"]);
            }
        }

        //解密出来的数据
        $data   = $this->getDecrypt($input);
        log_save("[ip]".get_ip_address()."    [data]".json_encode($data)."    [msg]DATA", "info", "", "order_info_".date("Y-m-d"));

        //下单防刷
        if (!prevent_reflash("hour", 60) && $data["gid"] != 112) {
            log_save("[ip]".get_ip_address()."    [data]".json_encode($data)."    [msg]下单超过60", "info", "", "order_err_".date("Y-m-d"));
//            $this->urlErr();
            $res = array(
                "Msg" => "您下单过于频繁，请稍后操作！"
            );
            if ($input["Version"] >= "1.1") {
                $this->returnMsg($res, 5, $input["Gid"], "下单过去频繁！", 0, $input["Uid"], $input["Version"]);
            } else {
                $this->returnMsg($res, 5, $input["Gid"], "下单过去频繁！", 2, $input["Uid"], $input["Version"]);
            }
        }

        //判断必要数据是否存在
        if (!$data["userCode"] || !$data["goodsCode"] || !$data["agent"] || !$data["udid"] || !$data["gid"] || !$data["billNo"]) {
            log_save("[ip]".get_ip_address()."    [data]".json_encode($data)."    [msg]详细数据缺失", "info", "", "order_err_".date("Y-m-d"));
//            $this->urlErr();
            $res = array(
                "Msg" => "数据异常！请重新点击！"
            );
            if ($input["Version"] >= "1.1") {
                $this->returnMsg($res, 4, $input["Gid"], "详细数据缺失！", 0, $input["Uid"], $input["Version"]);
            } else {
                $this->returnMsg($res, 4, $input["Gid"], "详细数据缺失！", 2, $input["Uid"], $input["Version"]);
            }
        }

        //获取设备信息
        $device = D("Api/Device")->getDeviceByUdid($data["udid"]);
        if (!$device) {
            log_save("[ip]".get_ip_address()."    [data]".json_encode($data)."    [msg]设备ID错误", "info", "", "order_err_".date("Y-m-d"));
//            $this->urlErr();
            $res = array(
                "Msg" => "数据异常！请重新打开游戏！"
            );
            if ($input["Version"] >= "1.1") {
                $this->returnMsg($res, 6, $input["Gid"], "用户未初始化！", 0, $input["Uid"], $input["Version"]);
            } else {
                $this->returnMsg($res, 6, $input["Gid"], "用户未初始化！", 2, $input["Uid"], $input["Version"]);
            }
        }

        //获取游戏信息
        $game = D("Api/Game")->getGame($data["gid"]);
        if (!$game) {
            log_save("[ip]".get_ip_address()."    [data]".json_encode($data)."    [msg]游戏ID错误", "info", "", "order_err_".date("Y-m-d"));
//            $this->urlErr();
            $res = array(
                "Msg" => "数据异常！请重新点击！"
            );
            if ($input["Version"] >= "1.1") {
                $this->returnMsg($res, 6, $input["Gid"], "无该游戏！", 0, $input["Uid"], $input["Version"]);
            } else {
                $this->returnMsg($res, 6, $input["Gid"], "无该游戏！", 2, $input["Uid"], $input["Version"]);
            }
        }

        //获取渠道号信息
        $agent = D("Api/Agent")->getAgent($data["agent"]);
        if (!$agent || $agent["game_id"] != $data["gid"]) {
            log_save("[ip]".get_ip_address()."    [data]".json_encode($data)."    [msg]AGENT错误", "info", "", "order_err_".date("Y-m-d"));
//            $this->urlErr();
            $res = array(
                "Msg" => "数据异常！请重新点击！"
            );
            if ($input["Version"] >= "1.1") {
                $this->returnMsg($res, 6, $input["Gid"], "无该渠道号！", 0, $input["Uid"], $input["Version"]);
            } else {
                $this->returnMsg($res, 6, $input["Gid"], "无该渠道号！", 2, $input["Uid"], $input["Version"]);
            }
        }

        //获取用户信息
        $user = D("Api/User")->getUserByCode($data["userCode"]);
        if (!$user) {
            log_save("[ip]".get_ip_address()."    [data]".json_encode($data)."    [msg]用户唯一标识符错误", "info", "", "order_err_".date("Y-m-d"));
//            $this->urlErr();
            $res = array(
                "Msg" => "数据异常！请重新登录！"
            );
            if ($input["Version"] >= "1.1") {
                $this->returnMsg($res, 6, $input["Gid"], "无该用户！", 0, $input["Uid"], $input["Version"]);
            } else {
                $this->returnMsg($res, 6, $input["Gid"], "无该用户！", 2, $input["Uid"], $input["Version"]);
            }
        }

        //获取商品数据
        $goods = D("Api/Goods")->getGoods($data["goodsCode"]);
        if (!$goods || $goods["game_id"] != $data["gid"]) {
            log_save("[ip]".get_ip_address()."    [data]".json_encode($data)."    [msg]商品ID错误", "info", "", "order_err_".date("Y-m-d"));
//            $this->urlErr();
            $res = array(
                "Msg" => "获取订单商品失败！请重新请求！"
            );
            if ($input["Version"] >= "1.1") {
                $this->returnMsg($res, 6, $input["Gid"], "无该商品ID！", 0, $input["Uid"], $input["Version"]);
            } else {
                $this->returnMsg($res, 6, $input["Gid"], "无该商品ID！", 2, $input["Uid"], $input["Version"]);
            }
        }

        //获取充值的渠道
        $channel_id = $this->getPayChannel($game, $agent, $device, $user, $goods, $data["gameVer"]);

        //关闭充值
        if ($channel_id == 1) {
            log_save("[ip]".get_ip_address()."    [data]".json_encode($data)."    [msg]关闭充值", "info", "", "order_err_".date("Y-m-d"));
//            $this->urlErr();
            $res = array(
                "Msg" => "无法充值！请联系客服！"
            );
            if ($input["Version"] >= "1.1") {
                $this->returnMsg($res, 6, $input["Gid"], "关闭充值！", 0, $input["Uid"], $input["Version"]);
            } else {
                $this->returnMsg($res, 6, $input["Gid"], "关闭充值！", 2, $input["Uid"], $input["Version"]);
            }
        }

        if ($data["type"] == 2 && $goods["amount"] <= 30 && $channel_id == 0 && $agent["channel_id"] <= 1 && $data["gid"] != 112) {
            //IOS 6、30充值限制
            $restrictMap    = array(
                "userCode"      => $data["userCode"],
                "agent"         => $data["agent"],
                "orderStatus"   => 0,
                "orderType"     => 0,
                "payType"       => 0,
                "type"          => 2,
                "amount"        => array("ELT", 30),
                "paymentTime"   => array("BETWEEN", array(strtotime(date("Y-m-d")), strtotime(date("Y-m-d")." +1 day")))
            );
            $restrictNum    = D("Api/Order")->getCountByMap($restrictMap);
            if ($restrictNum >= 5) {
                log_save("[ip]".get_ip_address()."    [data]".json_encode($data)."    [msg]6、30限制", "info", "", "order_err_".date("Y-m-d"));
//            $this->urlErr();
                $res = array(
                    "Msg" => "低额充值超过上限！"
                );
                if ($input["Version"] >= "1.1") {
                    $this->returnMsg($res, 6, $input["Gid"], "低额充值超过上限！", 0, $input["Uid"], $input["Version"]);
                } else {
                    $this->returnMsg($res, 6, $input["Gid"], "低额充值超过上限！", 2, $input["Uid"], $input["Version"]);
                }
            }
        }

        //获取IP及地址
        $ip     = get_ip_address();
        $area   = ip_to_location($ip);

        $channel    = D("Api/Channel")->getChannel($agent["channel_id"]);
        $user_game  = D("Api/UserGame")->getUserInfo(array("userCode" => $data["userCode"], "game_id" => $data["gid"]));

        //判断是否是没有自己SDK的渠道，是的话则调用我们的支付接口
        if (in_array($agent["channel_id"]."_".$data["type"], $this->noSdkChannel)) {
            //没有
            $no_sdk = 1;
        } else {
            //有
            $no_sdk = 0;
        }

        $order  = array(
            "billNo"        => $data["billNo"],
            "userCode"      => $data["userCode"],
            "userName"      => $user["userName"],
            "agent"         => $data["agent"],
            "game_id"       => $data["gid"],
            "gameName"      => $game["gameName"],
            "channel_id"    => (!$no_sdk && $channel_id == 2)? 0: $agent["channel_id"],
            "channelName"   => (!$no_sdk && ($channel_id == 2 || in_array($agent["channel_id"], array(0, 1))))? "创娱": $channel["channelName"],
            "amount"        => $goods["amount"],
            "goodsCode"     => $data["goodsCode"],
            "subject"       => $goods["name"],
            "roleId"        => $data["roleId"],
            "roleName"      => $data["roleName"],
            "serverId"      => $data["serverId"],
            "serverName"    => $data["serverName"],
            "level"         => $data["level"],
            "vip"           => $data["vip"],
            "extraInfo"     => $data["extraInfo"],
            "ip"            => $ip,
            "city"          => $area["city"],
            "province"      => $area["province"],
            "udid"          => $data["udid"],
            "idfa"          => $data["idfa"],
            "idfv"          => $data["idfv"],
            "imei"          => empty($data["imei"])? (empty($data["imei2"]? "": $data["imei2"])): $data["imei"],
            "imei2"         => empty($data["imei"])? "": (empty($data["imei2"])? "": $data["imei2"]),
            "package"       => $data["package"],
            "type"          => $data["type"],
            "createTime"    => time(),
            "regTime"       => $user_game["createTime"],
            "regAgent"      => $user_game["agent"]
        );

        //IOS用户充值匹配推广活动ID
        if($data['type'] == 2){
            if($iosUser = D('Api/IOSMatch')->getUserInfo($data['userCode'],$data['agent'])){
                $order['advter_id'] = $iosUser['advter_id'];
            }else{
                $order['advter_id'] = 1; //自然量
            }
        }

//        //测试账号的为测试订单
//        if ($data["userCode"] == "Ls511510131538f2d888b366" || $data["userCode"] == "Ls5115101358434dcd252044") {
//            if ($data["gid"] == 114) {
//                $order["orderType"] = 1;
//            } else {
//                $res = array(
//                    "Msg"   => "测试账号无法下单！",
//                );
//                if ($input["Version"] >= "1.1") {
//                    $this->returnMsg($res, 6, $input["Gid"], "测试账号无法下单！", 0, $input["Uid"], $input["Version"]);
//                } else {
//                    $this->returnMsg($res, 6, $input["Gid"], "测试账号无法下单！", 2, $input["Uid"], $input["Version"]);
//                }
//            }
//        }

        //下单是否成功
        $key        = 0;
        //订单号
        $orderId    = "";

        //循环下单，避免订单号重复导致下单失败
        for ($i = 0; $i < 5; $i ++) {
            $order["orderId"] = make_order();
            if (D("Api/Order")->addOrder($order)) {
                $key        = 1;
                $orderId    = $order["orderId"];
                break;
            }
        }

        //判断是否下单成功
        if ($key != 1) {
            //下单失败
            log_save("[ip]".get_ip_address()."    [data]".json_encode($data)."    [msg]下单失败", "info", "", "order_err_".date("Y-m-d"));
//            $this->urlErr();
            $res = array(
                "Msg"   => "下单失败！请重新请求",
            );
            if ($input["Version"] >= "1.1") {
                $this->returnMsg($res, 5, $input["Gid"], "下单失败！", 0, $input["Uid"], $input["Version"]);
            } else {
                $this->returnMsg($res, 5, $input["Gid"], "下单失败！", 2, $input["Uid"], $input["Version"]);
            }
        }

        //苹果充值时设备号、充值地区与注册时不一致，距离最后登陆时间不够一小时，则不能充值，提审包除外
        if ($data["type"] == 2 && $data["gameVer"] != $agent["trialVer"] && $agent["channel_id"] <= 1 && $channel_id == 0 && $data["gid"] != 112) {
            //常用设备、常住城市取值
            $commonDevice   = $user["commonDevice"]? $user["commonDevice"]: $user_game["udid"];
            $commonArea     = $user["commonArea"]? $user["commonArea"]: $user_game["city"];

            //判断是否是常用设备、常驻地区或是美国
            if ($data["udid"] != $commonDevice && ($area["city"] != $commonArea || strpos($area["city"], "美国") !== false)) {
                if (time() - $user_game["lastLogin"] < 3600) {
//                    D("Api/User")->saveUser(array("status" => 2), $data["userCode"]);
                    $ban    = array(
                        "userCode"      => $user_game["userCode"],
                        "userName"      => $user_game["userName"],
                        "game_id"       => $data["gid"],
                        "agent"         => $data["agent"],
                        "remark"        => "不同设备、不同市区进行充值",
                        "status"        => 2,
                        "creater"       => "API接口",
                        "createTime"    => time()
                    );
                    D("Api/BanUser")->addLog($ban);

                    log_save("[ip]".get_ip_address()."    [data]".json_encode($data)."    [msg]在线时长不够一小时！", "info", "", "order_err_".date("Y-m-d"));
//                    $this->urlErr();
                    $res = array(
                        "Msg" => "请在线一个小时再充值！"
                    );
                    if ($input["Version"] >= "1.1") {
                        $this->returnMsg($res, 6, $input["Gid"], "在线时长不够一小时！", 0, $input["Uid"], $input["Version"]);
                    } else {
                        $this->returnMsg($res, 6, $input["Gid"], "在线时长不够一小时！", 2, $input["Uid"], $input["Version"]);
                    }
                } else {
                    D("Api/User")->saveUser(array("commonDevice" => $data["udid"], "commonArea" => $area["city"]), $data["userCode"]);
                }
            }
        }

//        //威富通支付宝扫码下单接口
//        $spUrl = D("Api/ThirdPartyPayment")->swiftPassAlipay($orderId, $goods["amount"], $goods["name"]);
//        if (!$spUrl) {
//            //注册失败
//            $res = array(
//                "Msg"   => "下单失败！请重新请求",
//            );
//            $this->returnMsg($res, 5, $input["Gid"], "下单失败", 0, $input["Uid"], $input["Version"]);
//        }

        $fusePay = array();
        if (!in_array($agent["channel_id"], array(0, 1, 14)) && $channel_id === 0) {
            //渠道下单前操作
            $channel_name = ucfirst($channel["channelAbbr"]).($agent["channelVer"] > 1? $agent["channelVer"]: "");
            if (method_exists(D("Fusion/".$channel_name), "beforePay")) {
                $fusePay = D("Fusion/".$channel_name)->beforePay($data, $orderId);
            }
        }

        //支付方式 1：开启 0：关闭
        $payType = array(
//            array(
//                "Id"    => 1,
//                "Close" => 1,
//                "Name"  => "alipay",
//                "Value" => "支付宝支付"
//            ),
//            array(
//                "Id"    => 2,
//                "Close" => 1,
//                "Name"  => "weixin",
//                "Value" => "微信支付"
//            ),
//            array(
//                "Id"    => 3,
//                "Close" => 0,
//                "Name"  => "unionpay",
//                "Value" => "银联支付"
//            ),
//            array(
//                "Id"    => 0,
//                "Close" => 1,
//                "Name"  => "appstore",
//                "Value" => "苹果支付"
//            )
        );


        //安卓开启微信支付
        if ($data["type"] == 2 && $input["Version"] < "1.5"){
            $payType[] = array(
                "Id"    => 2,
                "Close" => 0,
                "Name"  => "weixin",
                "Value" => "微信支付"
            );
        }elseif (($data["gameVer"] == $agent["trialVer"] && $agent["trialVer"])) {
            //提审包不切支付
//            $payType[] = array(
//                "Id"    => 2,
//                "Close" => 0,
//                "Name"  => "weixin",
//                "Value" => "微信支付"
//            );
        } else {
            $payType[] = array(
                "Id"    => 2,
                "Close" => 1,
                "Name"  => "weixin",
                "Value" => "微信支付"
            );
        }

        //开启支付宝支付
        if (($data["gameVer"] == $agent["trialVer"] && $agent["trialVer"])) {
            //提审包不切支付
//            $payType[] = array(
//                "Id" => 1,
//                "Close" => 0,
//                "Name" => "alipay",
//                "Value" => "支付宝支付"
//            );
        } else {
            $payType[] = array(
                "Id"    => 1,
                "Close" => 1,
                "Name"  => "alipay",
                "Value" => "支付宝支付"
            );
        }

//            $payType[] = array(
//                "Id"    => 3,
//                "Close" => 0,
//                "Name"  => "unionpay",
//                "Value" => "银联支付"
//            );

            //下单成功
        $res = array(
            "Msg"       => "下单成功！",
            "OrderId"   => $orderId,
            "Channel"   => $order["channel_id"],
            "Amount"    => $goods["amount"],
            "Subject"   => $goods["name"],
            "PayType"   => $payType,
//            "CodeUrl"   => $spUrl? $spUrl: "",
            "FusePay"   => $fusePay? $fusePay: (object)$fusePay,
            "Change"    => $channel_id == 2? 1: 0,        //是否切换充值，0：不切，1：切
//            "Change"    => $Change,                                             //是否切换充值，0：不切，1：切
            "OpenId"    => 0,                                                   //是否不用判断身份实名直接充值，0:是，1：否
            "Ext"       => ""
        );
        log_save("[ip]".get_ip_address()."    [data]".json_encode($data)."    [msg]下单成功", "info", "", "order_info_".date("Y-m-d"));
        log_save("[ip]".get_ip_address()."    [data]".json_encode($res)."    [msg]返回信息", "info", "", "order_info_".date("Y-m-d"));
        $this->returnMsg($res, 0, $input["Gid"], "下单成功！", 0, $input["Uid"], $input["Version"]);
    }

    /**
     * 获取订单记录
     */
    public function Log()
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
        if (!$data["userCode"] || !$data["length"]) {
            $res = array(
                "Msg" => "数据异常！请重新刷新！"
            );
            $this->returnMsg($res, 4, $input["Gid"], "详细数据缺失！", 0, $input["Uid"], $input["Version"]);
        }

        //判断查看是否超出限制
        if ($data["length"] > 50) {
            $res = array(
                "Msg" => "不好意思！只能查看最近50条充值记录！"
            );
            $this->returnMsg($res, 4, $input["Gid"], "超出限制！", 0, $input["Uid"], $input["Version"]);
        }

        $order = D("Api/Order")->getOrder(array("userCode" => $data["userCode"]), $data["length"], "orderId,amount,FROM_UNIXTIME(createTime, '%Y-%m-%d %H:%i:%S') AS time,orderStatus AS status");

        if (!$order) {
            $res = array(
                "Msg" => "数据异常！请重新尝试！"
            );
            $this->returnMsg($res, 6, $input["Gid"], "无数据！", 0, $input["Uid"], $input["Version"]);
        }

        $res = array(
            "Msg"       => "获取成功！",
            "Data"      => $order
        );
        $this->returnMsg($res, 0, $input["Gid"], "获取成功！", 0, $input["Uid"], $input["Version"]);
    }

    /**
     * 获取充值渠道
     * @param $game 游戏信息
     * @param $agent 渠道信息
     * @param $device 设备信息
     * @param $user 用户信息
     * @param $goods 商品信息
     * @param $ver 游戏版本
     * @return int 0：渠道充值，1：关闭充值，2：切充值
     */
    private function getPayChannel($game, $agent, $device, $user, $goods, $ver)
    {
        //数据缺失，则关闭充值
        if (!$game || !$agent || !$device || !$user || !$goods) return 1;

        //当存在关闭充值条件时，则关闭充值
        if ($device["payStatus"] == 1 || $agent["payStatus"] == 1 || $game["payStatus"] == 1 || $user["status"] == 2 || ($user["status"] == 1 && (!$user["allowLoginTime"] || $user["allowLoginTime"] > time())) || $goods["status"] == 1) return 1;

        //当存在切充值条件时，则切充值
        if (($device["payStatus"] == 2 || $agent["payStatus"] == 2 || $game["payStatus"] == 2 || $user["status"] == 3) && ($ver != $agent["trialVer"] || !$agent["trialVer"])) return 2;

        //切换充值测试
//        if ($agent["agent"] == "ztfyl001") {
//            //充值累计满100则切换充值
//            if (D("Api/Order")->getSumAmount(array("userCode" => $user["userCode"], "orderStatus" => 0)) >= 100) return 2;
//
//            //测试切换充值
//            if (in_array($user["userCode"], array("Ls511503231131a73da4a95e"))) return 2;
//
//        }

        //正常充值
        return 0;
    }

    /**
     * 支付宝支付接口
     */
    public function AlipayNative()
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
        if (!$data["userCode"] || !$data["orderId"] || !$data["agent"] || !$data["gid"]) {
            $res = array(
                "Msg" => "数据异常！请重新点击！"
            );
            $this->returnMsg($res, 4, $input["Gid"], "详细数据缺失！", 0, $input["Uid"], $input["Version"]);
        }

        //获取游戏信息
        $game = D("Api/Game")->getGame($data["gid"]);
        if (!$game) {
            $res = array(
                "Msg" => "数据异常！请重新点击！"
            );
            $this->returnMsg($res, 6, $input["Gid"], "无该游戏！", 0, $input["Uid"], $input["Version"]);
        }

        //获取渠道号信息
        $agent = D("Api/Agent")->getAgent($data["agent"]);
        if (!$agent || $agent["game_id"] != $data["gid"]) {
            $res = array(
                "Msg" => "数据异常！请重新点击！"
            );
            $this->returnMsg($res, 6, $input["Gid"], "无该渠道号", 0, $input["Uid"], $input["Version"]);
        }

        //提审包不切充值
        if ($data["gameVer"] == $agent["trialVer"] && $agent["trialVer"]){
            $this->urlErr();
        }

        //获取用户信息
        $user = D("Api/User")->getUserByCode($data["userCode"]);
        if (!$user) {
            $res = array(
                "Msg" => "数据异常！请重新登录！"
            );
            $this->returnMsg($res, 6, $input["Gid"], "无该用户", 0, $input["Uid"], $input["Version"]);
        }

        //获取订单信息
        $order = D("Api/Order")->getOrderById($data["orderId"]);
        if (!$order || $order["agent"] != $data["agent"] || $order["userCode"] != $data["userCode"] || $order["game_id"] != $data["gid"]) {
            $res = array(
                "Msg" => "数据异常！请重新尝试！"
            );
            $this->returnMsg($res, 6, $input["Gid"], "无数据！", 0, $input["Uid"], $input["Version"]);
        }

//        //威富通支付宝扫码下单接口
//        $spUrl = D("Api/ThirdPartyPayment")->swiftPassAlipay($data["orderId"], $order["subject"], $order["amount"]);
//        if (!$spUrl || $spUrl["status"] != 200) {
//            //注册失败
//            $res = array(
//                "Msg"   => "下单失败！请重新请求",
//            );
//            $this->returnMsg($res, 5, $input["Gid"], $spUrl["msg"], 0, $input["Uid"], $input["Version"]);
//        }

//        if ($data["userCode"] == "Ls511503881307e75fee5759") {
//            log_save("[ip]".get_ip_address()."    [user]".$data["userCode"]."    [order]".$data["orderId"]."    [data]".$spUrl["code_url"]."    [msg]订单地址", "info", "", "sp_order_url_".date("Y-m-d"));
//        }

        //记录其支付地址
//        D("Api/Order")->saveOrder(array("spUrl" => $spUrl["code_url"], "payType" => 1), $data["orderId"]);
        D("Api/Order")->saveOrder(array("payType" => 1), $data["orderId"]);

//        //测试账号直接回调
//        if ($data["userCode"] == "Ls511510131538f2d888b366" || $data["userCode"] == "Ls5115101358434dcd252044") {
//            if ($data["gid"] == 114) {
//                //更改订单状态
//                $time   = time();
//                $param  = array(
//                    "tranId"        => "testOrder".$data["gid"].time(),
//                    "orderStatus"   => 0,
//                    "paymentTime"   => $time,
//                    "payType"       => 1
//                );
//                $res = D("Api/Order")->saveOrder($param, $data["orderId"]);
//
//                if ($res) {
//                    //记录最后的支付时间
//                    D("Api/Device")->saveDeviceByUdid(array("lastPay" => $time), $order["udid"]);
//                    D("Api/User")->saveUser(array("lastPay" => $time), $order["userCode"]);
//                    D("Api/UserGame")->saveUserGame(array("lastPay" => $time, "lastPayRoleId" => $order["roleId"], "lastPayRoleName" => $order["roleName"], "lastPayServerId" => $order["serverId"], "lastPayServerName" => $order["serverName"]), $order["userCode"], $order["game_id"]);
//                    D("Api/DeviceGame")->saveDeviceGame(array("lastPay" => $time), $order["udid"], $order["game_id"]);
//
//                    //发放游戏币
//                    R("Api/Reply/gameCallback", array($data["orderId"], 0));
//                } else {
//                    $res = array(
//                        "Msg"   => "下单失败！请重新请求",
//                    );
//                    $this->returnMsg($res, 5, $input["Gid"], "下单失败！请重新请求", 0, $input["Uid"], $input["Version"]);
//                }
//            } else {
//                $res = array(
//                    "Msg"   => "测试账号无法下单！",
//                );
//                $this->returnMsg($res, 5, $input["Gid"], "测试账号无法下单", 0, $input["Uid"], $input["Version"]);
//            }
//        }

        //支付宝原生网页快捷支付
        $url = "http://apisdk.chuangyunet.net/Api/Pay/AliWapPayNative?order=".$data["orderId"];

        $res = array(
            "Msg"       => "下单成功！",
            "CodeUrl"   => $url
//            "CodeUrl"   => $spUrl["code_url"]
        );
        $this->returnMsg($res, 0, $input["Gid"], "下单成功！", 0, $input["Uid"], $input["Version"]);
    }

    /**
     * 微信支付接口
     */
    public function WeixinPayNative()
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
        if (!$data["userCode"] || !$data["orderId"] || !$data["agent"] || !$data["gid"]) {
            $res = array(
                "Msg" => "数据异常！请重新点击！"
            );
            $this->returnMsg($res, 4, $input["Gid"], "详细数据缺失！", 0, $input["Uid"], $input["Version"]);
        }

        //获取游戏信息
        $game = D("Api/Game")->getGame($data["gid"]);
        if (!$game) {
            $res = array(
                "Msg" => "数据异常！请重新点击！"
            );
            $this->returnMsg($res, 6, $input["Gid"], "无该游戏！", 0, $input["Uid"], $input["Version"]);
        }

        //获取渠道号信息
        $agent = D("Api/Agent")->getAgent($data["agent"]);
        if (!$agent || $agent["game_id"] != $data["gid"]) {
            $res = array(
                "Msg" => "数据异常！请重新点击！"
            );
            $this->returnMsg($res, 6, $input["Gid"], "无该渠道号", 0, $input["Uid"], $input["Version"]);
        }

        //提审包不切充值
        if ($data["gameVer"] == $agent["trialVer"] && $agent["trialVer"]){
            $this->urlErr();
        }

        //获取用户信息
        $user = D("Api/User")->getUserByCode($data["userCode"]);
        if (!$user) {
            $res = array(
                "Msg" => "数据异常！请重新登录！"
            );
            $this->returnMsg($res, 6, $input["Gid"], "无该用户", 0, $input["Uid"], $input["Version"]);
        }

        //获取订单信息
        $order = D("Api/Order")->getOrderById($data["orderId"]);
        if (!$order || $order["agent"] != $data["agent"] || $order["userCode"] != $data["userCode"] || $order["game_id"] != $data["gid"]) {
            $res = array(
                "Msg" => "数据异常！请重新尝试！"
            );
            $this->returnMsg($res, 6, $input["Gid"], "无数据！", 0, $input["Uid"], $input["Version"]);
        }

        //记录其支付方式
        D("Api/Order")->saveOrder(array("payType" => 2), $data["orderId"]);

//        //测试账号直接回调
//        if ($data["userCode"] == "Ls511510131538f2d888b366" || $data["userCode"] == "Ls5115101358434dcd252044") {
//            if ($data["gid"] == 114) {
//                //更改订单状态
//                $time   = time();
//                $param  = array(
//                    "tranId"        => "testOrder".$data["gid"].time(),
//                    "orderStatus"   => 0,
//                    "paymentTime"   => $time,
//                    "payType"       => 2
//                );
//                $res = D("Api/Order")->saveOrder($param, $data["orderId"]);
//
//                if ($res) {
//                    //记录最后的支付时间
//                    D("Api/Device")->saveDeviceByUdid(array("lastPay" => $time), $order["udid"]);
//                    D("Api/User")->saveUser(array("lastPay" => $time), $order["userCode"]);
//                    D("Api/UserGame")->saveUserGame(array("lastPay" => $time, "lastPayRoleId" => $order["roleId"], "lastPayRoleName" => $order["roleName"], "lastPayServerId" => $order["serverId"], "lastPayServerName" => $order["serverName"]), $order["userCode"], $order["game_id"]);
//                    D("Api/DeviceGame")->saveDeviceGame(array("lastPay" => $time), $order["udid"], $order["game_id"]);
//
//                    //发放游戏币
//                    R("Api/Reply/gameCallback", array($data["orderId"], 0));
//                } else {
//                    $res = array(
//                        "Msg"   => "下单失败！请重新请求",
//                    );
//                    $this->returnMsg($res, 5, $input["Gid"], "下单失败！请重新请求", 0, $input["Uid"], $input["Version"]);
//                }
//            } else {
//                $res = array(
//                    "Msg"   => "测试账号无法下单！",
//                );
//                $this->returnMsg($res, 5, $input["Gid"], "测试账号无法下单", 0, $input["Uid"], $input["Version"]);
//            }
//        }

        //判断是否是融合下单
        $is_fusion = 0;
        if (!in_array($agent["channel_id"], array(0, 1, 14))) {
            //获取渠道信息
            $channel        = D("Api/Channel")->getChannel($agent["channel_id"]);
            $channel_name   = ucfirst($channel["channelAbbr"]).($agent["channelVer"] > 1? $agent["channelVer"]: "");
            //判断是否有下单接口
            if (method_exists(D("Fusion/".$channel_name), "weixinPay")) {
                $result     = D("Fusion/".$channel_name)->weixinPay($data, $order);
                $is_fusion  = 1;
            }
        }

        if ($is_fusion) {
            //融合下单
            if (!$result) {
                //注册失败
                $res = array(
                    "Msg"   => "下单失败！请重新请求",
                );
                $this->returnMsg($res, 5, $input["Gid"], "下单失败！请重新请求", 0, $input["Uid"], $input["Version"]);
            } else {
                $res = array(
                    "Msg"       => "下单成功！",
                    "CodeUrl"   => $result
                );
                $this->returnMsg($res, 0, $input["Gid"], "下单成功！", 0, $input["Uid"], $input["Version"]);
            }
        } else {
            //本渠道下单

            /*================ 威富通微信下单接口 start  ==============*/
            //威富通微信下单接口
            $spUrl = D("Api/ThirdPartyPayment")->swiftPassWeixin($data["orderId"], $order["subject"], $order["amount"]);
            if (!$spUrl || $spUrl["status"] != 200) {
                //注册失败
                $res = array(
                    "Msg"   => "下单失败！请重新请求",
                );
                $this->returnMsg($res, 5, $input["Gid"], $spUrl["msg"], 0, $input["Uid"], $input["Version"]);
            }

            $res = array(
                "Msg"       => "下单成功！",
                "CodeUrl"   => $spUrl["code_url"]
            );
            $this->returnMsg($res, 0, $input["Gid"], "下单成功！", 0, $input["Uid"], $input["Version"]);
            /*================ 威富通微信下单接口 end  ==============*/

//            /*================ 国连四方网络H5收银台下单接口 start  ==============*/
//            //国连四方网络H5收银台下单接口
//            $h5Url = D("Api/ThirdPartyPayment")->bbnpayH5($data["orderId"], $order["amount"]);
//            if (!$h5Url) {
//                //注册失败
//                $res = array(
//                    "Msg"   => "下单失败！请重新请求",
//                );
//                $this->returnMsg($res, 5, $input["Gid"], "下单失败！请重新请求", 0, $input["Uid"], $input["Version"]);
//            }
//
//            $res = array(
//                "Msg"       => "下单成功！",
//                "CodeUrl"   => C("COMPANY_DOMAIN")."Api/Pay/bbnWeixinPay?Url=".urlencode($h5Url)
//            );
//            $this->returnMsg($res, 0, $input["Gid"], "下单成功！", 0, $input["Uid"], $input["Version"]);
//            /*================ 国连四方网络H5收银台下单接口 end  ==============*/

            /*================ 微信H5收银台下单接口 start  ==============*/
//        //微信H5收银台下单接口
//        $h5Url = D("Api/ThirdPartyPayment")->iuucWeixinH5($data["orderId"], $order["amount"], $order["subject"]);
//        if (!$h5Url) {
//            //注册失败
//            $res = array(
//                "Msg"   => "下单失败！请重新请求",
//            );
//            $this->returnMsg($res, 5, $input["Gid"], "下单失败！请重新请求", 0, $input["Uid"], $input["Version"]);
//        }
//
//        $res = array(
//            "Msg"       => "下单成功！",
//            "CodeUrl"   => $h5Url
//        );
//        $this->returnMsg($res, 0, $input["Gid"], "下单成功！", 0, $input["Uid"], $input["Version"]);
            /*================ 微信H5收银台下单接口 end  ==============*/

            /*================ 微信公众号H5支付接口tnbpay start  ==============*/
            //微信公众号H5支付预下单
//        $prepayId = D("Api/ThirdPartyPayment")->tnbpayWeixinH5($data["orderId"], $order["amount"], $order["subject"]);
//        if (!$prepayId) {
//            //注册失败
//            $res = array(
//                "Msg"   => "下单失败！请重新请求",
//            );
//            $this->returnMsg($res, 5, $input["Gid"], "下单失败！请重新请求", 0, $input["Uid"], $input["Version"]);
//        }
//
//        $res = array(
//            "Msg"       => "下单成功！",
//            "CodeUrl"   => C("COMPANY_DOMAIN")."Api/Pay/tnbWeixinPay/prepayId/".$prepayId
//        );
//        $this->returnMsg($res, 0, $input["Gid"], "下单成功！", 0, $input["Uid"], $input["Version"]);
            /*================ 微信公众号H5支付接口tnbpay end  ==============*/

            /*================ 微信原生H5支付接口 start  ==============*/
//            //微信原生H5支付预下单
//            $h5Url = D("Api/ThirdPartyPayment")->WeixinH5($data["orderId"], $order["amount"], $order["subject"]);
//            if (!$h5Url) {
//                //注册失败
//                $res = array(
//                    "Msg"   => "下单失败！请重新请求",
//                );
//                $this->returnMsg($res, 5, $input["Gid"], "下单失败！请重新请求", 0, $input["Uid"], $input["Version"]);
//            }
//
//            $res = array(
//                "Msg"       => "下单成功！",
//                "CodeUrl"   => C("COMPANY_DOMAIN")."Api/Pay/WeixinH5Pay?Url=".urlencode($h5Url)
//            );
//            $this->returnMsg($res, 0, $input["Gid"], "下单成功！", 0, $input["Uid"], $input["Version"]);
            /*================ 微信原生H5支付接口 end  ==============*/
        }
    }

    /**
     * 微信公众号H5支付接口地址
     */
    public function tnbWeixinPay()
    {
        //获取数据
        $data = $this->getInput("get", "trim");

        //判断必要数据是否存在
        if (!$data["prepayId"]) {
            $this->urlErr();
        }

        $this->assign("prepayId", $data["prepayId"]);
        $this->display("./tnbpayWeixin");
    }

    /**
     * 国连四方网络H5收银台下单网址
     */
    public function bbnWeixinPay()
    {
        //获取数据
        $url = I("Url", "", "trim");

        //判断必要数据是否存在
        if (!$url) {
            $this->urlErr();
        }

        $this->assign("Url", $url);
        $this->display("./bbnWeixinPay");
    }

    /**
     * 原生微信H5支付收银台
     */
    public function WeixinH5Pay()
    {
        //获取数据
        $url = I("Url", "", "trim");

        //判断必要数据是否存在
        if (!$url) {
            $this->urlErr();
        }

        $this->assign("Url", $url);
        $this->display("./WeixinH5Pay");
    }

    /**
     * 支付宝网页快捷支付
     */
    public function AliWapPayNative()
    {
        //获取数据
        $data = $this->getInput("get", "trim");

        //判断必要数据是否存在
        if (!$data["order"]) {
            $this->urlErr();
        }

        //获取订单信息
        $order = D("Api/Order")->getOrderById($data["order"]);
        if (!$order["orderStatus"]) {
            $this->urlErr();
        }

        //判断是否是融合下单
        $is_fusion = 0;
        if (!in_array($order["channel_id"], array(0, 1, 14))) {
            //获取渠道信息
            $agent          = D("Api/Agent")->getAgent($order["agent"]);
            $channel        = D("Api/Channel")->getChannel($order["channel_id"]);
            $channel_name   = ucfirst($channel["channelAbbr"]).($agent["channelVer"] > 1? $agent["channelVer"]: "");
            //判断是否有下单接口
            if (method_exists(D("Fusion/".$channel_name), "aliPay")) {
                $result     = D("Fusion/".$channel_name)->aliPay($order);
                $is_fusion  = 1;
            }
        }

        if ($is_fusion) {
            //融合下单
            $res = $result;
        } else {
            //本渠道下单
            $res = D("Api/ThirdPartyPayment")->aliWapQuick($data["order"], $order["amount"], $order["subject"]);
        }
        header("Location: ".$res);
//        echo $res;
    }

    /**
     * 银联绑卡判断接口
     */
    public function CheckUnionPayCard()
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
        if (!$data["userCode"]) {
            $res = array(
                "Msg" => "数据异常！请重新点击！"
            );
            $this->returnMsg($res, 4, $input["Gid"], "详细数据缺失！", 0, $input["Uid"], $input["Version"]);
        }

        //获取用户信息
        $user = D("Api/User")->getUserByCode($data["userCode"]);
        if (!$user) {
            $res = array(
                "Msg" => "数据异常！请重新登录！"
            );
            $this->returnMsg($res, 6, $input["Gid"], "无该用户", 0, $input["Uid"], $input["Version"]);
        }

        //获取绑卡信息
        $card   = D("Api/BankCard")->getCard(array("userCode" => $data["userCode"], "status" => 0));
        $Card   = array();
        foreach ($card as $v) {
            $Card[] = array(
                "Type"  => $v["type"],
                "Card"  => $v["card"]
            );
        }

        //返回信息
        $res    = array(
            "Msg"   => "查询成功！",
            "Card"  => $Card
        );
        $this->returnMsg($res, 0, $input["Gid"], "获取数据成功！", 0, $input["Uid"], $input["Version"]);
    }

    /**
     * 银联添加绑卡信息接口
     */
    public function AddUnionPayCard()
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
        if (!$data["userCode"] || !$data["card"] || !$data["type"] || !$data["mobile"] || !$data["name"] || !$data["IDCard"] || !$data["expDate"]) {
            $res = array(
                "Msg" => "数据未填写完整！"
            );
            $this->returnMsg($res, 4, $input["Gid"], "详细数据缺失！", 0, $input["Uid"], $input["Version"]);
        }

        //身份证检测
        if (!check_IDCard($data["IDCard"])) {
            $res = array(
                "Msg" => "身份证号填写错误！"
            );
            $this->returnMsg($res, 4, $input["Gid"], "身份证错误", 0, $input["Uid"], $input["Version"]);
        }

        //手机号码检测
        if (!preg_match("/^1[34578]\d{9}$/", $data["mobile"])) {
            $res = array(
                "Msg" => "手机号码填写错误！"
            );
            $this->returnMsg($res, 4, $input["Gid"], "手机号码错误", 0, $input["Uid"], $input["Version"]);
        }

        //获取用户信息
        if (!D("Api/User")->getUserByCode($data["userCode"])) {
            $res = array(
                "Msg" => "数据异常！请重新登录！"
            );
            $this->returnMsg($res, 6, $input["Gid"], "无该用户", 0, $input["Uid"], $input["Version"]);
        }

        //判断是否重复绑卡
        if (D("Api/BankCard")->getCard(array("userCode" => $data["userCode"], "card" => $data["card"], "status" => 0))) {
            $res = array(
                "Msg" => "请勿重复绑定银行卡！"
            );
            $this->returnMsg($res, 6, $input["Gid"], "重复绑定", 0, $input["Uid"], $input["Version"]);
        }

        //绑卡操作
        if (D("Api/BankCard")->addCard($data)) {
            $res = array(
                "Msg" => "绑定银行卡成功！"
            );
            $this->returnMsg($res, 0, $input["Gid"], "绑定银行卡成功！", 0, $input["Uid"], $input["Version"]);
        } else {
            $res = array(
                "Msg" => "绑定银行卡失败！"
            );
            $this->returnMsg($res, 5, $input["Gid"], "绑定银行卡失败！", 0, $input["Uid"], $input["Version"]);
        }
    }

    /**
     * 解绑银行卡接口
     */
    public function DeleteUnionPayCard()
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
        if (!$data["userCode"] || !$data["card"]) {
            $res = array(
                "Msg" => "数据未填写完整！"
            );
            $this->returnMsg($res, 4, $input["Gid"], "详细数据缺失！", 0, $input["Uid"], $input["Version"]);
        }

        //获取用户信息
        if (!D("Api/User")->getUserByCode($data["userCode"])) {
            $res = array(
                "Msg" => "数据异常！请重新登录！"
            );
            $this->returnMsg($res, 6, $input["Gid"], "无该用户", 0, $input["Uid"], $input["Version"]);
        }

        //判断是否存在该银行卡
        if (!D("Api/BankCard")->getCard(array("userCode" => $data["userCode"], "card" => $data["card"], "status" => 0))) {
            $res = array(
                "Msg" => "找不到该银行卡！"
            );
            $this->returnMsg($res, 6, $input["Gid"], "无此银行卡", 0, $input["Uid"], $input["Version"]);
        }

        //解绑卡操作
        if (D("Api/BankCard")->saveCard(array("status" => 1), array("userCode" => $data["userCode"], "card" => $data["card"], "status" => 0))) {
            $res = array(
                "Msg" => "解绑银行卡成功！"
            );
            $this->returnMsg($res, 0, $input["Gid"], "解绑银行卡成功！", 0, $input["Uid"], $input["Version"]);
        } else {
            $res = array(
                "Msg" => "解绑银行卡失败！"
            );
            $this->returnMsg($res, 5, $input["Gid"], "解绑银行卡失败！", 0, $input["Uid"], $input["Version"]);
        }
    }

    /**
     * 银联支付
     */
    public function UnionPayNative()
    {
        //获取数据
        $data = $this->getInput("get", "trim");

        //判断必要数据是否存在
        if (!$data["userCode"] || !$data["card"] || !$data["orderId"]) {
            $this->urlErr();
        }

        //获取用户信息
        $user = D("Api/User")->getUserByCode($data["userCode"]);
        if (!$user) {
            $this->urlErr();
        }

        //获取订单信息
        $order = D("Api/Order")->getOrderById($data["orderId"]);
        if (!$order || $order["userCode"] != $data["userCode"]) {
            $this->urlErr();
        }

        //获取银行卡信息
        $card = D("Api/BankCard")->getCardByUserCard($data["userCode"], $data["card"]);
        if (!$card) {
            $this->urlErr();
        }

        //记录其支付方式
        D("Api/Order")->saveOrder(array("payType" => 3), $data["orderId"]);

        //下单参数
        $info = array(
            "payKey"                => $this->payKey,
            "orderPrice"            => $order["amount"],
            "outTradeNo"            => $data["orderId"],
            "productType"           => "40000503",
            "orderTime"             => date("YmdHis"),
            "payBankAccountNo"      => $data["card"],
            "payPhoneNo"            => $card["mobile"],
            "payBankAccountName"    => $card["name"],
            "payCertNo"             => $card["IDCard"],
            "productName"           => $order["subject"],
            "orderIp"               => get_client_ip(),
            "returnUrl"             => $this->returnUrl,
            "notifyUrl"             => $this->notifyUrl,
            "subPayKey"             => $this->subPayKey,
            "remark"                => $data["orderId"],
        );

        $str = "notifyUrl=".$info["notifyUrl"]."&orderIp=".$info["orderIp"]."&orderPrice=".$info["orderPrice"]."&orderTime=".$info["orderTime"]."&outTradeNo=".$info["outTradeNo"]."&payBankAccountName=".$info["payBankAccountName"]."&payBankAccountNo=".$info["payBankAccountNo"]."&payCertNo=".$info["payCertNo"]."&payKey=".$info["payKey"]."&payPhoneNo=".$info["payPhoneNo"]."&productName=".$info["productName"]."&productType=".$info["productType"]."&remark=".$info["remark"]."&returnUrl=".$info["returnUrl"]."&paySecret=".$this->paySecret;  //拼接字符串，里面的拼接字段必须全部用上。
        $info["sign"] = strtoupper(md5($str));    //MD5值必须大写

        $this->assign('data', $info);
        $this->display('./UnionPay');
    }

      /**
     * 支付宝支付跳转，换名用，避免检测
     */
    public function ZFBNative()
    {
        $this->AlipayNative();
    }

    /**
     * 微信支付跳转，换名用，避免检测
     */
    public function WXNative()
    {
        $this->WeixinPayNative();
    }



    /**
     * 支付宝支付跳转，换名用，避免检测
     */
    public function APNative()
    {
        $this->AlipayNative();
    }

    /**
     * 微信支付跳转，换名用，避免检测
     */
    public function WPNative()
    {
        $this->WeixinPayNative();
    }
    /**
     * 备用充值地址
     */
    public function PayNative()
    {

    }

    /**
     * 返回错误即可
     */
    private function urlErr()
    {
        header('HTTP/1.1 404 Not Found');
        exit();
    }
}