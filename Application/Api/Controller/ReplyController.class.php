<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/6/8
 * Time: 9:49
 *
 * 回调控制器
 */

namespace Api\Controller;

class ReplyController extends ApiController
{
    private $payKey     = "895565718a9a4f058293cbed27e35e76";                                           //银联商户支付Key
    private $paySecret  = "3507bb079eb64a1abd351bac0d9c2b8e";                                           //银联商户支付密钥

//    public function testPay()
//    {
//        $res = $this->gameCallback("L20171217022312173411bfc", 0);
//        var_dump($res);
//    }

    /**
     * 苹果支付回调接口
     */
    public function AppleStore()
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

        //日期
        $date   = date("Y-m-d");
        log_save("[ip]".get_ip_address()."    [data]".json_encode($data), "info", "", "apple_pay_info_".$date);

        //判断必要数据是否齐全
        if (!$data["userCode"] || !$data["receiptData"] || !$data["transactionId"] || !$data["gid"] || !$data["goodsCode"] || !$data["udid"] || !$data["agent"]) {
            $res = array(
                "Msg" => "数据异常！"
            );

            //记录日志
            log_save("[ip]".get_ip_address()."    [data]".json_encode($data)."  [msg]必要数据缺失", "info", "", "apple_pay_err_".$date);
            $this->returnMsg($res, 4, $input["Gid"], "必要数据缺失", 0, $input["Uid"], $input["Version"]);
        }

        //获取游戏信息
        $game = D("Api/Game")->getGame($data["gid"]);
        if (!$game) {
            $res = array(
                "Msg" => "数据异常！"
            );

            //记录日志
            log_save("[ip]".get_ip_address()."    [data]".json_encode($data)."  [msg]无该游戏", "info", "", "apple_pay_err_".$date);
            $this->returnMsg($res, 6, $input["Gid"], "无该游戏！", 0, $input["Uid"], $input["Version"]);
        }

        //获取渠道号信息
        $agent = D("Api/Agent")->getAgent($data["agent"]);
        if (!$agent || $agent["game_id"] != $data["gid"]) {
            $res = array(
                "Msg" => "数据异常！"
            );

            //记录日志
            log_save("[ip]".get_ip_address()."    [data]".json_encode($data)."  [msg]无该渠道号", "info", "", "apple_pay_err_".$date);
            $this->returnMsg($res, 6, $input["Gid"], "无该渠道号", 0, $input["Uid"], $input["Version"]);
        }

        //获取用户信息
        $user = D("Api/User")->getUserByCode($data["userCode"]);
        if (!$user) {
            $res = array(
                "Msg" => "数据异常！"
            );

            //记录日志
            log_save("[ip]".get_ip_address()."    [data]".json_encode($data)."  [msg]无该用户", "info", "", "apple_pay_err_".$date);
            $this->returnMsg($res, 6, $input["Gid"], "无该用户", 0, $input["Uid"], $input["Version"]);
        }

        //获取商品数据
        $goods = D("Api/Goods")->getGoods($data["goodsCode"]);
        if (!$goods || $goods["game_id"] != $data["gid"]) {
            $res = array(
                "Msg" => "获取不到商品！"
            );

            //记录日志
            log_save("[ip]".get_ip_address()."    [data]".json_encode($data)."  [msg]无该商品ID", "info", "", "apple_pay_err_".$date);
            $this->returnMsg($res, 6, $input["Gid"], "无该商品ID", 0, $input["Uid"], $input["Version"]);
        }

        //进行苹果支付回调操作
        $ApplePay = D("Api/ThirdPartyPayment")->appleStorePay($data["receiptData"], $data["transactionId"], $data["gid"], $data["userCode"], $data["goodsCode"], $goods["amount"], $agent["bundleId"]);
//        //记录日志
//        log_save("[ip]".get_ip_address()."    [data]".json_encode($ApplePay)."  [msg]苹果请求数据", "info", "", "apple_pay_info_".$date);

        //判断是否回调失败
        if (!$ApplePay["result"]) {
            //将错误信息存进数据库
            if (strpos($ApplePay["msg"], "异常") === false) {
                //信息数据
                $query = array(
                    "receiptData"   => $data["receiptData"],
                    "transactionId" => $data["transactionId"],
                    "game_id"       => $data["gid"],
                    "userCode"      => $data["userCode"],
                    "goodsCode"     => $data["goodsCode"],
                    "agent"         => $data["agent"],
                    "num"           => 1,
                    "lastTime"      => time()
                );

                D("AppleQuerys")->addQuery($query);
            }

            $res = array(
                "Msg" => $ApplePay["msg"]? $ApplePay["msg"]: "充值异常！"
            );

            //记录日志
            log_save("[ip]".get_ip_address()."    [data]".json_encode($data)."    [res]".json_encode($ApplePay)."    [msg]".$ApplePay["str"], "info", "", "apple_pay_err_".$date);
            $this->returnMsg($res, 5, $input["Gid"], $ApplePay["str"]? $ApplePay["msg"]: "充值异常！", 0, $input["Uid"], $input["Version"]);
        }

        $time = time();
        //记录最后的支付时间
        D("Api/Device")->saveDeviceByUdid(array("lastPay" => $time), $data["udid"]);
        D("Api/User")->saveUser(array("lastPay" => $time), $data["userCode"]);
        D("Api/DeviceGame")->saveDeviceGame(array("lastPay" => $time), $data["udid"], $data["gid"]);

        //判断$ApplePay["data"]["transaction_id"]是否为空值
        if(!$ApplePay["data"]["transaction_id"]){
            $res = array(
                "Msg" => "渠道订单信息有误！"
            );
            //记录日志
            log_save("[ip]".get_ip_address()."    [data]".json_encode($data)."    [res]".json_encode($ApplePay)."    [msg]渠道订单信息有误！苹果返回transaction_id为空或者不存在", "info", "", "apple_pay_err_".$date);
            $this->returnMsg($res, 5, $input["Gid"], "渠道订单号为空", 0, $input["Uid"], $input["Version"]);
        }

        //判断订单是否是回调成功的
        $order = D("Api/Order")->getOrderByMap(array("tranId" => $ApplePay["data"]["transaction_id"]));
        if ($order) {
            //记录首次的支付时间
            D("Api/UserGame")->saveFirstPay($order["userCode"], $order["game_id"], $time);
            D("Api/Role")->saveFirstPay($order["game_id"], $order["userCode"], $order["roleId"], $time);

            //记录最后的支付时间
            D("Api/UserGame")->saveUserGame(array("lastPay" => $time, "lastPayRoleId" => $order["roleId"], "lastPayRoleName" => $order["roleName"], "lastPayServerId" => $order["serverId"], "lastPayServerName" => $order["serverName"]), $data["userCode"], $data["gid"]);

            //已经存在的订单
            if ($order["orderStatus"]) {
                //未充值成功的订单，进行订单数据判断
                if ($order["amount"] != $goods["amount"] || $order["userCode"] != $data["userCode"] || $order["game_id"] != $data["gid"]) {
                    //将错误信息存进数据库
                    $query = array(
                        "receiptData"   => $data["receiptData"],
                        "transactionId" => $data["transactionId"],
                        "game_id"       => $data["gid"],
                        "userCode"      => $data["userCode"],
                        "goodsCode"     => $data["goodsCode"],
                        "agent"         => $data["agent"],
                        "num"           => 1,
                        "lastTime"      => time()
                    );
                    D("AppleQuerys")->addQuery($query);

                    $res = array(
                        "Msg" => "订单信息有误！"
                    );

                    //记录日志
                    log_save("[ip]".get_ip_address()."    [data]".json_encode($data)."    [res]".json_encode($ApplePay)."    [msg]已绑定订单，订单信息不符", "info", "", "apple_pay_err_".$date);
                    $this->returnMsg($res, 5, $input["Gid"], "已绑定订单，订单信息不符", 0, $input["Uid"], $input["Version"]);
                }

                //修改订单状态
                if (!D("Api/Order")->saveOrder(array("orderStatus" => 0, "paymentTime" => $time, "orderType" => $ApplePay["data"]["orderType"]), $order["orderId"])) {
                    //将错误信息存进数据库
                    $query = array(
                        "receiptData"   => $data["receiptData"],
                        "transactionId" => $data["transactionId"],
                        "game_id"       => $data["gid"],
                        "userCode"      => $data["userCode"],
                        "goodsCode"     => $data["goodsCode"],
                        "agent"         => $data["agent"],
                        "num"           => 1,
                        "lastTime"      => time()
                    );
                    D("AppleQuerys")->addQuery($query);

                    $res = array(
                        "Msg" => "订单无法进行充值！"
                    );

                    //记录日志
                    log_save("[ip]".get_ip_address()."    [data]".json_encode($data)."    [res]".json_encode($ApplePay)."    [msg]已绑定订单，无法修改状态", "info", "", "apple_pay_err_".$date);
                    $this->returnMsg($res, 5, $input["Gid"], "已绑定订单，无法修改状态！", 0, $input["Uid"], $input["Version"]);
                };
            }

            //判断是否需要进行游戏补单
            if ($order["gameOrderStatus"]) {
                //进行游戏补单
                $this->gameCallback($order["orderId"]);
            }

            $res = array(
                "Msg" => "充值成功！"
            );

            //记录日志
            log_save("[ip]".get_ip_address()."    [data]".json_encode($data)."    [res]".json_encode($ApplePay)."    [msg]充值成功", "info", "", "apple_pay_success_".$date);
            $this->returnMsg($res, 0, $input["Gid"], "充值成功！", 0, $input["Uid"], $input["Version"]);
        } else {
            //未记录的订单
            $map = array(
                "userCode"      => $data["userCode"],
                "goodsCode"     => $data["goodsCode"],
                "amount"        => $goods["amount"],
                "agent"         => $data["agent"],
                "orderStatus"   => 1,
                "payType"       => 0,
                "game_id"       => $data["gid"],
                "createTime"    => array("GT", $time - 86400)
            );

            //寻找订单进行匹配
            $bill = D("Api/Order")->getOrderByMap($map);

            //为获取相对应的订单
            if (!$bill) {
                //将错误信息存进数据库
                $query = array(
                    "receiptData"   => $data["receiptData"],
                    "transactionId" => $data["transactionId"],
                    "game_id"       => $data["gid"],
                    "userCode"      => $data["userCode"],
                    "goodsCode"     => $data["goodsCode"],
                    "agent"         => $data["agent"],
                    "num"           => 1,
                    "lastTime"      => time()
                );
                D("AppleQuerys")->addQuery($query);

                $res = array(
                    "Msg" => "匹配不到对应的订单！"
                );

                //记录日志
                log_save("[ip]".get_ip_address()."    [data]".json_encode($data)."    [res]".json_encode($ApplePay)."    [msg]找不到对应的未充值订单", "info", "", "apple_pay_err_".$date);
                $this->returnMsg($res, 5, $input["Gid"], "找不到对应的未充值订单", 0, $input["Uid"], $input["Version"]);
            }

            //记录首次的支付时间
            D("Api/UserGame")->saveFirstPay($bill["userCode"], $bill["game_id"], $time);
            D("Api/Role")->saveFirstPay($bill["game_id"], $bill["userCode"], $bill["roleId"], $time);

            //记录最后的支付时间
            D("Api/UserGame")->saveUserGame(array("lastPay" => $time, "lastPayRoleId" => $bill["roleId"], "lastPayRoleName" => $bill["roleName"], "lastPayServerId" => $bill["serverId"], "lastPayServerName" => $bill["serverName"]), $data["userCode"], $data["gid"]);

            //保存的订单信息
            $info = array(
                "tranId"        => $ApplePay["data"]["transaction_id"],
                "orderStatus"   => 0,
                "orderType"     => $ApplePay["data"]["orderType"],
                "paymentTime"   => $time
            );

            //保存订单失败
            if (!D("Api/Order")->saveOrder($info, $bill["orderId"])) {
                //将错误信息存进数据库
                $query = array(
                    "receiptData"   => $data["receiptData"],
                    "transactionId" => $data["transactionId"],
                    "game_id"       => $data["gid"],
                    "userCode"      => $data["userCode"],
                    "goodsCode"     => $data["goodsCode"],
                    "agent"         => $data["agent"],
                    "num"           => 1,
                    "lastTime"      => time()
                );
                D("AppleQuerys")->addQuery($query);

                $res = array(
                    "Msg" => "订单无法进行充值！"
                );

                //记录日志
                log_save("[ip]".get_ip_address()."    [data]".json_encode($data)."    [res]".json_encode($ApplePay)."    [msg]无法修改订单状态", "info", "", "apple_pay_err_".$date);
                $this->returnMsg($res, 5, $input["Gid"], "无法修改订单状态！", 0, $input["Uid"], $input["Version"]);
            };

            //进行游戏补单
            $this->gameCallback($bill["orderId"]);

            $res = array(
                "Msg" => "充值成功！"
            );

            //记录日志
            log_save("[ip]".get_ip_address()."    [data]".json_encode($data)."    [res]".json_encode($ApplePay)."    [msg]充值成功", "info", "", "apple_pay_success_".$date);
            $this->returnMsg($res, 0, $input["Gid"], "充值成功！", 0, $input["Uid"], $input["Version"]);
        }
    }

    /**
     * 支付宝网页快捷支付回调
     */
    public function AliWapQuickPay()
    {
        $date   = date("Y-m-d");
        //获取回调数据
        $input  = $_POST;
        log_save("[ip]".get_ip_address()."    [data]".json_encode($input)."  [msg]callback", "info", "", "aliwap_pay_suc_".$date);

        $result = D("Api/ThirdPartyPayment")->aliWapQuickCallback($input);
        if (!$result) {
            log_save("[ip]".get_ip_address()."    [data]".json_encode($input)."  [msg]sign_err", "info", "", "aliwap_pay_err_".$date);
            echo "failure";
            exit();
        }

        //是否是充值成功的，不是则默认返回success
        if ($input["trade_status"] != "TRADE_SUCCESS" && $input["trade_status"] != "TRADE_FINISHED") {
            log_save("[ip]".get_ip_address()."    [data]".json_encode($input)."  [msg]no_pay", "info", "", "aliwap_pay_err_".$date);
            echo "success";
            exit();
        }

        //获取订单信息
        $order = D("Api/Order")->getOrderById($input["out_trade_no"]);
        if (!$order) {
            log_save("[ip]".get_ip_address()."    [data]".json_encode($input)."  [msg]no_order", "info", "", "aliwap_pay_err_".$date);
            echo "failure";
            exit();
        }

        //判断订单是否是已经充值成功，是则返回success
        if (!$order["orderStatus"]) {
            log_save("[ip]".get_ip_address()."    [data]".json_encode($input)."  [msg]have_success", "info", "", "aliwap_pay_err_".$date);
            echo "success";
            exit();
        }

        //判断订单金额是否一致
        if ($order["amount"] != $input["total_amount"]) {
            log_save("[ip]".get_ip_address()."    [data]".json_encode($input)."  [msg]amount_error", "info", "", "aliwap_pay_err_".$date);
            echo "failure";
            exit();
        }

        //更改订单状态
        $time   = time();
        $param  = array(
            "tranId"        => $input["trade_no"],
            "orderStatus"   => 0,
            "paymentTime"   => $time,
            "payType"       => 1
        );
        $res = D("Api/Order")->saveOrder($param, $input["out_trade_no"]);

        if ($res) {
            //记录首次的支付时间
            D("Api/UserGame")->saveFirstPay($order["userCode"], $order["game_id"], $time);
            D("Api/Role")->saveFirstPay($order["game_id"], $order["userCode"], $order["roleId"], $time);

            //记录最后的支付时间
            D("Api/Device")->saveDeviceByUdid(array("lastPay" => $time), $order["udid"]);
            D("Api/User")->saveUser(array("lastPay" => $time), $order["userCode"]);
            D("Api/UserGame")->saveUserGame(array("lastPay" => $time, "lastPayRoleId" => $order["roleId"], "lastPayRoleName" => $order["roleName"], "lastPayServerId" => $order["serverId"], "lastPayServerName" => $order["serverName"]), $order["userCode"], $order["game_id"]);
            D("Api/DeviceGame")->saveDeviceGame(array("lastPay" => $time), $order["udid"], $order["game_id"]);

            //发放游戏币
            $this->gameCallback($input["out_trade_no"]);

            log_save("[ip]".get_ip_address()."    [data]".json_encode($input)."  [msg]success", "info", "", "aliwap_pay_suc_".$date);
            echo "success";
            exit();
        } else {
            log_save("[ip]".get_ip_address()."    [data]".json_encode($input)."  [msg]pay_error", "info", "", "aliwap_pay_err_".$date);
            echo "failure";
            exit();
        }
    }

    /**
     * 威富通支付回调
     */
    public function SwiftPass()
    {
        $date = date("Y-m-d");
        //获取回调数据
        $info = D("Api/ThirdPartyPayment")->getSwiftPassCallback();
        log_save("[ip]".get_ip_address()."    [data]".json_encode($info)."  [msg]callback", "info", "", "swift_pay_suc_".$date);

        if (!$info) {
            log_save("[ip]".get_ip_address()."    [data]".json_encode($info)."  [msg]no_info", "info", "", "swift_pay_err_".$date);
            echo "failure";
            exit();
        }

        //是否是充值成功的，不是则默认返回success
        if ($info["pay_result"] != 0) {
            log_save("[ip]".get_ip_address()."    [data]".json_encode($info)."  [msg]no_pay", "info", "", "swift_pay_err_".$date);
            echo "success";
            exit();
        }

        //获取订单信息
        $order = D("Api/Order")->getOrderById($info["out_trade_no"]);
        if (!$order) {
            log_save("[ip]".get_ip_address()."    [data]".json_encode($info)."  [msg]no_order", "info", "", "swift_pay_err_".$date);
            echo "failure";
            exit();
        }

        //判断订单是否是已经充值成功，是则返回success
        if (!$order["orderStatus"]) {
            log_save("[ip]".get_ip_address()."    [data]".json_encode($info)."  [msg]have_success", "info", "", "swift_pay_err_".$date);
            echo "success";
            exit();
        }

        //判断订单金额是否一致
        if ($order["amount"] != $info["total_fee"]/100) {
            log_save("[ip]".get_ip_address()."    [data]".json_encode($info)."  [msg]amount_error", "info", "", "swift_pay_err_".$date);
            echo "failure";
            exit();
        }

        //更改订单状态
        $time   = time();
        $param  = array(
            "tranId"        => $info["transaction_id"],
            "orderStatus"   => 0,
            "paymentTime"   => $time,
            "payType"       => $info["trade_type"] == "pay.weixin.wappay"? 2: 1
        );
        $res = D("Api/Order")->saveOrder($param, $info["out_trade_no"]);

        if ($res) {
            //记录首次的支付时间
            D("Api/UserGame")->saveFirstPay($order["userCode"], $order["game_id"], $time);
            D("Api/Role")->saveFirstPay($order["game_id"], $order["userCode"], $order["roleId"], $time);

            //记录最后的支付时间
            D("Api/Device")->saveDeviceByUdid(array("lastPay" => $time), $order["udid"]);
            D("Api/User")->saveUser(array("lastPay" => $time), $order["userCode"]);
            D("Api/UserGame")->saveUserGame(array("lastPay" => $time, "lastPayRoleId" => $order["roleId"], "lastPayRoleName" => $order["roleName"], "lastPayServerId" => $order["serverId"], "lastPayServerName" => $order["serverName"]), $order["userCode"], $order["game_id"]);
            D("Api/DeviceGame")->saveDeviceGame(array("lastPay" => $time), $order["udid"], $order["game_id"]);

            //发放游戏币
            $this->gameCallback($info["out_trade_no"]);

            log_save("[ip]".get_ip_address()."    [data]".json_encode($info)."  [msg]success", "info", "", "swift_pay_suc_".$date);
            echo "success";
            exit();
        } else {
            log_save("[ip]".get_ip_address()."    [data]".json_encode($info)."  [msg]pay_error", "info", "", "swift_pay_err_".$date);
            echo "failure";
            exit();
        }
    }

    /**
     * 国连四方网络H5收银台回调
     */
    public function bbnpayCallback()
    {
        $date   = date("Y-m-d");
        //获取回调数据
        $input  = $_POST;
        log_save("[ip]".get_ip_address()."    [data]".json_encode($input)."  [msg]callback", "info", "", "bbnpay_pay_suc_".$date);

        $info = D("Api/ThirdPartyPayment")->bbnpayH5Callback($input);

        if (!$info) {
            log_save("[ip]".get_ip_address()."    [data]".json_encode($input)."  [msg]callback", "info", "", "bbnpay_pay_err_".$date);
            echo "FAIL";
            exit();
        }

        //是否是充值成功的，不是则默认返回success
        if ($info["result"] != 1) {
            log_save("[ip]".get_ip_address()."    [data]".json_encode($input)."  [msg]no_pay", "info", "", "bbnpay_pay_err_".$date);
            echo "SUCCESS";
            exit();
        }

        //获取订单信息
        $order = D("Api/Order")->getOrderById($info["cporderid"]);
        if (!$order) {
            log_save("[ip]".get_ip_address()."    [data]".json_encode($input)."  [msg]no_order", "info", "", "bbnpay_pay_err_".$date);
            echo "FAIL";
            exit();
        }

        //判断订单是否是已经充值成功，是则返回success
        if (!$order["orderStatus"]) {
            log_save("[ip]".get_ip_address()."    [data]".json_encode($input)."  [msg]have_success", "info", "", "bbnpay_pay_err_".$date);
            echo "SUCCESS";
            exit();
        }

        //判断订单金额是否一致
        if ($order["amount"] != $info["money"]/100) {
            log_save("[ip]".get_ip_address()."    [data]".json_encode($input)."  [msg]amount_error", "info", "", "bbnpay_pay_err_".$date);
            echo "FAIL";
            exit();
        }

        //更改订单状态
        $time   = time();
        $param  = array(
            "tranId"        => $info["transid"],
            "orderStatus"   => 0,
            "paymentTime"   => $time,
            "payType"       => $info["paytype"] == "1"? 2: 0
        );
        $res = D("Api/Order")->saveOrder($param, $info["cporderid"]);

        if ($res) {
            //记录首次的支付时间
            D("Api/UserGame")->saveFirstPay($order["userCode"], $order["game_id"], $time);
            D("Api/Role")->saveFirstPay($order["game_id"], $order["userCode"], $order["roleId"], $time);

            //记录最后的支付时间
            D("Api/Device")->saveDeviceByUdid(array("lastPay" => $time), $order["udid"]);
            D("Api/User")->saveUser(array("lastPay" => $time), $order["userCode"]);
            D("Api/UserGame")->saveUserGame(array("lastPay" => $time, "lastPayRoleId" => $order["roleId"], "lastPayRoleName" => $order["roleName"], "lastPayServerId" => $order["serverId"], "lastPayServerName" => $order["serverName"]), $order["userCode"], $order["game_id"]);
            D("Api/DeviceGame")->saveDeviceGame(array("lastPay" => $time), $order["udid"], $order["game_id"]);

            //发放游戏币
            $this->gameCallback($info["cporderid"]);

            log_save("[ip]".get_ip_address()."    [data]".json_encode($input)."  [msg]success", "info", "", "bbnpay_pay_suc_".$date);
            echo "SUCCESS";
            exit();
        } else {
            log_save("[ip]".get_ip_address()."    [data]".json_encode($input)."  [msg]pay_error", "info", "", "bbnpay_pay_err_".$date);
            echo "FAIL";
            exit();
        }
    }

    /**
     * 微信H5收银台回调
     */
    public function iuucCallback()
    {
        $date   = date("Y-m-d");
        //获取回调数据
        $info   = $_GET;
        log_save("[ip]".get_ip_address()."    [data]".json_encode($info)."  [msg]callback", "info", "", "iuuc_pay_suc_".$date);


        if (!D("Api/ThirdPartyPayment")->iuucH5Callback($info)) {
            log_save("[ip]".get_ip_address()."    [data]".json_encode($info)."  [msg]callback", "info", "", "iuuc_pay_err_".$date);
            echo "fail";
            exit();
        }

        //是否是充值成功的，不是则默认返回success
        if ($info["stat"] != "DELIVRD") {
            log_save("[ip]".get_ip_address()."    [data]".json_encode($info)."  [msg]no_pay", "info", "", "iuuc_pay_err_".$date);
            echo "ok";
            exit();
        }

        //解析获取订单号
        $order_arr  = explode("_", $info["siteorderid"]);
        $order_id   = $order_arr[1];

        //获取订单信息
        $order = D("Api/Order")->getOrderById($order_id);
        if (!$order) {
            log_save("[ip]".get_ip_address()."    [data]".json_encode($info)."  [msg]no_order", "info", "", "iuuc_pay_err_".$date);
            echo "fail";
            exit();
        }

        //判断订单是否是已经充值成功，是则返回success
        if (!$order["orderStatus"]) {
            log_save("[ip]".get_ip_address()."    [data]".json_encode($info)."  [msg]have_success", "info", "", "iuuc_pay_err_".$date);
            echo "ok";
            exit();
        }

        //判断订单金额是否一致
        if ($order["amount"] != $info["paymoney"]/100) {
            log_save("[ip]".get_ip_address()."    [data]".json_encode($info)."  [msg]amount_error", "info", "", "iuuc_pay_err_".$date);
            echo "fail";
            exit();
        }

        //更改订单状态
        $time   = time();
        $param  = array(
            "tranId"        => $info["myorderid"],
            "orderStatus"   => 0,
            "paymentTime"   => $time,
            "payType"       => 2
        );
        $res = D("Api/Order")->saveOrder($param, $order_id);

        if ($res) {
            //记录首次的支付时间
            D("Api/UserGame")->saveFirstPay($order["userCode"], $order["game_id"], $time);
            D("Api/Role")->saveFirstPay($order["game_id"], $order["userCode"], $order["roleId"], $time);

            //记录最后的支付时间
            D("Api/Device")->saveDeviceByUdid(array("lastPay" => $time), $order["udid"]);
            D("Api/User")->saveUser(array("lastPay" => $time), $order["userCode"]);
            D("Api/UserGame")->saveUserGame(array("lastPay" => $time, "lastPayRoleId" => $order["roleId"], "lastPayRoleName" => $order["roleName"], "lastPayServerId" => $order["serverId"], "lastPayServerName" => $order["serverName"]), $order["userCode"], $order["game_id"]);
            D("Api/DeviceGame")->saveDeviceGame(array("lastPay" => $time), $order["udid"], $order["game_id"]);

            //发放游戏币
            $this->gameCallback($order_id);

            log_save("[ip]".get_ip_address()."    [data]".json_encode($info)."  [msg]success", "info", "", "iuuc_pay_suc_".$date);
            echo "ok";
            exit();
        } else {
            log_save("[ip]".get_ip_address()."    [data]".json_encode($info)."  [msg]pay_error", "info", "", "iuuc_pay_err_".$date);
            echo "fail";
            exit();
        }
    }

    /**
     * tnb微信公众号H5支付回调
     */
    public function tnbpayCallback()
    {
        $date   = date("Y-m-d");
        //获取回调数据
        $file   = file_get_contents("php://input");
        log_save("[ip]".get_ip_address()."    [file]".$file."  [msg]callback", "info", "", "tnbPay_pay_suc_".$date);

        //解析数据
        $result = D("Api/ThirdPartyPayment")->tnbH5WeixinCallback($file);
        //数据是否通过验证
        if (!$result["Code"]) {
            log_save("[ip]".get_ip_address()."    [data]".$file."  [msg]".$result["Msg"], "info", "", "tnbPay_pay_err_".$date);
            D("Api/ThirdPartyPayment")->tnbH5WeixinCallbackMsg($result["Res"]);
        }

        //解析出来的数据
        $info   = $result["Res"];
        log_save("[ip]".get_ip_address()."    [file]".json_encode($info)."  [msg]info", "info", "", "tnbPay_pay_suc_".$date);

        //是否是充值成功的，不是则默认返回success
        if ($info["result_code"] != "SUCCESS") {
            log_save("[ip]".get_ip_address()."    [data]".json_encode($info)."  [msg]no_pay", "info", "", "tnbPay_pay_err_".$date);
            D("Api/ThirdPartyPayment")->tnbH5WeixinCallbackMsg(array("return_code" => "SUCCESS"));
        }

        //获取订单信息
        $order = D("Api/Order")->getOrderById($info["out_trade_no"]);
        if (!$order) {
            log_save("[ip]".get_ip_address()."    [data]".json_encode($info)."  [msg]no_order", "info", "", "tnbPay_pay_err_".$date);
            D("Api/ThirdPartyPayment")->tnbH5WeixinCallbackMsg(array("return_code" => "FAIL", "return_msg" => "订单号不存在"));
        }

        //判断订单是否是已经充值成功，是则返回success
        if (!$order["orderStatus"]) {
            log_save("[ip]".get_ip_address()."    [data]".json_encode($info)."  [msg]have_success", "info", "", "tnbPay_pay_err_".$date);
            D("Api/ThirdPartyPayment")->tnbH5WeixinCallbackMsg(array("return_code" => "SUCCESS"));
        }

        //判断订单金额是否一致
        if ($order["amount"] != $info["total_fee"]/100) {
            log_save("[ip]".get_ip_address()."    [data]".json_encode($info)."  [msg]amount_error", "info", "", "tnbPay_pay_err_".$date);
            D("Api/ThirdPartyPayment")->tnbH5WeixinCallbackMsg(array("return_code" => "FAIL", "return_msg" => "商品价格不一致"));
        }

        //更改订单状态
        $time   = time();
        $param  = array(
            "tranId"        => $info["transaction_id"],
            "orderStatus"   => 0,
            "paymentTime"   => $time,
            "payType"       => 2
        );
        $res = D("Api/Order")->saveOrder($param, $info["out_trade_no"]);

        if ($res) {
            //记录首次的支付时间
            D("Api/UserGame")->saveFirstPay($order["userCode"], $order["game_id"], $time);
            D("Api/Role")->saveFirstPay($order["game_id"], $order["userCode"], $order["roleId"], $time);

            //记录最后的支付时间
            D("Api/Device")->saveDeviceByUdid(array("lastPay" => $time), $order["udid"]);
            D("Api/User")->saveUser(array("lastPay" => $time), $order["userCode"]);
            D("Api/UserGame")->saveUserGame(array("lastPay" => $time, "lastPayRoleId" => $order["roleId"], "lastPayRoleName" => $order["roleName"], "lastPayServerId" => $order["serverId"], "lastPayServerName" => $order["serverName"]), $order["userCode"], $order["game_id"]);
            D("Api/DeviceGame")->saveDeviceGame(array("lastPay" => $time), $order["udid"], $order["game_id"]);

            //发放游戏币
            $this->gameCallback($info["out_trade_no"]);

            log_save("[ip]".get_ip_address()."    [data]".json_encode($info)."  [msg]success", "info", "", "tnbPay_pay_suc_".$date);
            D("Api/ThirdPartyPayment")->tnbH5WeixinCallbackMsg(array("return_code" => "SUCCESS"));
        } else {
            log_save("[ip]".get_ip_address()."    [data]".json_encode($info)."  [msg]pay_error", "info", "", "tnbPay_pay_err_".$date);
            D("Api/ThirdPartyPayment")->tnbH5WeixinCallbackMsg(array("return_code" => "FAIL", "return_msg" => "充值失败"));
        }
    }

    /**
     * 原生微信H5支付充值回调
     */
    public function weixinH5Callback()
    {
        $date = date("Y-m-d");
        //获取回调数据
        $info = D("Api/ThirdPartyPayment")->weixinH5Callback();
        log_save("[ip]".get_ip_address()."    [data]".json_encode($info)."  [msg]callback", "info", "", "weixinH5_pay_suc_".$date);

        if (!$info) {
            log_save("[ip]".get_ip_address()."    [data]".json_encode($info)."  [msg]no_info", "info", "", "weixinH5_pay_err_".$date);
            D("Api/ThirdPartyPayment")->weixinH5Msg(array("return_code" => "FAIL", "return_msg" => "获取不到参数"));
            exit();
        }

        //是否是充值成功的，不是则默认返回success
        if (!$info["Code"]) {
            log_save("[ip]".get_ip_address()."    [data]".json_encode($info)."  [msg]".$info["Msg"], "info", "", "weixinH5_pay_err_".$date);
            D("Api/ThirdPartyPayment")->weixinH5Msg($info["Res"]);
            exit();
        }

        //获取订单信息
        $order = D("Api/Order")->getOrderById($info["Res"]["out_trade_no"]);
        if (!$order) {
            log_save("[ip]".get_ip_address()."    [data]".json_encode($info)."  [msg]no_order", "info", "", "weixinH5_pay_err_".$date);
            D("Api/ThirdPartyPayment")->weixinH5Msg(array("return_code" => "FAIL", "return_msg" => "订单错误"));
            exit();
        }

        //判断订单是否是已经充值成功，是则返回success
        if (!$order["orderStatus"]) {
            log_save("[ip]".get_ip_address()."    [data]".json_encode($info)."  [msg]have_success", "info", "", "weixinH5_pay_err_".$date);
            D("Api/ThirdPartyPayment")->weixinH5Msg(array("return_code" => "FAIL", "return_msg" => "重复订单"));
            exit();
        }

        //判断订单金额是否一致
        if ($order["amount"] != $info["Res"]["total_fee"]/100) {
            log_save("[ip]".get_ip_address()."    [data]".json_encode($info)."  [msg]amount_error", "info", "", "weixinH5_pay_err_".$date);
            D("Api/ThirdPartyPayment")->weixinH5Msg(array("return_code" => "FAIL", "return_msg" => "金额错误"));
            exit();
        }

        //更改订单状态
        $time   = time();
        $param  = array(
            "tranId"        => $info["Res"]["transaction_id"],
            "orderStatus"   => 0,
            "paymentTime"   => $time,
            "payType"       => 2
        );
        $res = D("Api/Order")->saveOrder($param, $info["Res"]["out_trade_no"]);

        if ($res) {
            //记录首次的支付时间
            D("Api/UserGame")->saveFirstPay($order["userCode"], $order["game_id"], $time);
            D("Api/Role")->saveFirstPay($order["game_id"], $order["userCode"], $order["roleId"], $time);

            //记录最后的支付时间
            D("Api/Device")->saveDeviceByUdid(array("lastPay" => $time), $order["udid"]);
            D("Api/User")->saveUser(array("lastPay" => $time), $order["userCode"]);
            D("Api/UserGame")->saveUserGame(array("lastPay" => $time, "lastPayRoleId" => $order["roleId"], "lastPayRoleName" => $order["roleName"], "lastPayServerId" => $order["serverId"], "lastPayServerName" => $order["serverName"]), $order["userCode"], $order["game_id"]);
            D("Api/DeviceGame")->saveDeviceGame(array("lastPay" => $time), $order["udid"], $order["game_id"]);

            //发放游戏币
            $this->gameCallback($info["Res"]["out_trade_no"]);

            log_save("[ip]".get_ip_address()."    [data]".json_encode($info)."  [msg]success", "info", "", "weixinH5_pay_suc_".$date);
            D("Api/ThirdPartyPayment")->weixinH5Msg(array("return_code" => "SUCCESS", "return_msg" => "充值成功"));
            exit();
        } else {
            log_save("[ip]".get_ip_address()."    [data]".json_encode($info)."  [msg]pay_error", "info", "", "weixinH5_pay_err_".$date);
            D("Api/ThirdPartyPayment")->weixinH5Msg(array("return_code" => "FAIL", "return_msg" => "充值失败"));
            exit();
        }
    }

    /**
     * 银联充值回调接口
     */
    public function UnionPayCallback()
    {
        $date   = date("Y-m-d");
        //获取回调数据
        $input  = $_REQUEST;
        log_save("[ip]".get_ip_address()."    [data]".json_encode($input)."  [msg]callback", "info", "", "UnionPay_pay_suc_".$date);

        //判断商品key是否正确
        if ($input["payKey"] != $this->payKey) {
            log_save("[ip]".get_ip_address()."    [data]".json_encode($input)."  [msg]payKey_error", "info", "", "UnionPay_pay_err_".$date);
            echo "ERROR";
            exit();
        }

        ksort($input);
        $str = "";
        foreach ($input as $k => $v) {
            if ($k == "sign") continue;
            $str .= $k."=".$v."&";
        }
        $str .= "paySecret=".$this->paySecret;
        //判断签名是否正确
        if ($input["sign"] != strtoupper(md5($str))) {
            log_save("[ip]".get_ip_address()."    [data]".json_encode($input)."  [msg]sign_error", "info", "", "UnionPay_pay_err_".$date);
            echo "ERROR";
            exit();
        }

        //获取订单信息
        $order = D("Api/Order")->getOrderById($input["outTradeNo"]);
        if (!$order) {
            log_save("[ip]".get_ip_address()."    [data]".json_encode($input)."  [msg]no_order", "info", "", "UnionPay_pay_err_".$date);
            echo "ERROR";
            exit();
        }

        //判断订单是否是已经充值成功，是则返回success
        if (!$order["orderStatus"]) {
            log_save("[ip]".get_ip_address()."    [data]".json_encode($input)."  [msg]have_success", "info", "", "UnionPay_pay_err_".$date);
            echo "SUCCESS";
            exit();
        }

        //判断订单金额是否一致
        if ($order["amount"] != $input["orderPrice"]) {
            log_save("[ip]".get_ip_address()."    [data]".json_encode($input)."  [msg]amount_error", "info", "", "UnionPay_pay_err_".$date);
            echo "ERROR";
            exit();
        }

        //更改订单状态
        $time   = time();
        $param  = array(
            "tranId"        => $input["trxNo"],
            "orderStatus"   => 0,
            "paymentTime"   => $time,
            "payType"       => 3
        );
        $res = D("Api/Order")->saveOrder($param, $input["outTradeNo"]);

        if ($res) {
            //记录首次的支付时间
            D("Api/UserGame")->saveFirstPay($order["userCode"], $order["game_id"], $time);
            D("Api/Role")->saveFirstPay($order["game_id"], $order["userCode"], $order["roleId"], $time);

            //记录最后的支付时间
            D("Api/Device")->saveDeviceByUdid(array("lastPay" => $time), $order["udid"]);
            D("Api/User")->saveUser(array("lastPay" => $time), $order["userCode"]);
            D("Api/UserGame")->saveUserGame(array("lastPay" => $time, "lastPayRoleId" => $order["roleId"], "lastPayRoleName" => $order["roleName"], "lastPayServerId" => $order["serverId"], "lastPayServerName" => $order["serverName"]), $order["userCode"], $order["game_id"]);
            D("Api/DeviceGame")->saveDeviceGame(array("lastPay" => $time), $order["udid"], $order["game_id"]);

            //发放游戏币
            $this->gameCallback($input["outTradeNo"]);

            log_save("[ip]".get_ip_address()."    [data]".json_encode($input)."  [msg]success", "info", "", "UnionPay_pay_suc_".$date);
            echo "SUCCESS";
            exit();
        } else {
            log_save("[ip]".get_ip_address()."    [data]".json_encode($input)."  [msg]pay_error", "info", "", "UnionPay_pay_err_".$date);
            echo "ERROR";
            exit();
        }
    }

    /**
     * 游戏币回调
     * @param $orderId
     * @param int $sup 是否为补单，0：是，1：否
     * @return array
     */
    public function gameCallback($orderId, $sup = 1)
    {
        //获取订单信息
        $order = D("Api/Order")->getOrderById($orderId);

        //判断是否需要回调
        if (!$order || $order["orderStatus"] != 0) {
            $return = array(
                "Code"  => false,
                "Msg"   => "订单不存在或未充值！",
                "Res"   => "",
            );
            return $return;
        }
        if ($order["gameOrderStatus"] == 0) {
            $return = array(
                "Code"  => false,
                "Msg"   => "成功的订单！请勿重复回调！",
                "Res"   => "",
            );
            return $return;
        }

        //保存游戏订单回调次数
        D("Api/Order")->saveOrder(array("num" => $order["num"] + 1), $order["orderId"]);

        //获取游戏信息
        $game = D("Api/Game")->getGame($order["game_id"]);
        if (!$game) {
            $return = array(
                "Code"  => false,
                "Msg"   => "游戏数据异常！",
                "Res"   => "",
            );
            return $return;
        }

        //获取渠道号信息
        $agent = D("Api/Agent")->getAgent($order["agent"]);
        if (!$agent || $agent["game_id"] != $order["game_id"]) {
            $return = array(
                "Code"  => false,
                "Msg"   => "渠道数据异常！",
                "Res"   => "",
            );
            return $return;
        }

        //获取商品数据
        $goods = D("Api/Goods")->getGoods($order["goodsCode"]);
        //判断商品数据是否异常
        if (!$goods || $goods["game_id"] != $order["game_id"] || $goods["amount"] != $order["amount"]) {
            $return = array(
                "Code"  => false,
                "Msg"   => "商品数据异常！",
                "Res"   => "",
            );
            return $return;
        }

        //回调信息集合
        $info   = array(
            "OrderId"   => $orderId,
            "BillNo"    => $order["billNo"],
            "UserCode"  => $order["userCode"],
            "GameId"    => $order["game_id"],
            "Amount"    => $order["amount"],
            "ProductId" => $order["goodsCode"],
            "RoleId"    => $order["roleId"],
            "ServerId"  => $order["serverId"],
            "Status"    => $order["orderStatus"],
            "ExtraInfo" => $order["extraInfo"]
        );

        //生成签名
        $param  = $info;
        ksort($param);
        $str    = "";
        foreach ($param as $k => $v) {
            $str .= "{$k}={$v}&";
        }
        $info["Sign"]   = strtolower(md5("{$str}PayKey={$game['payKey']}"));

        //进行回调
        $callbackUrl    = $agent["agentCallbackUrl"]? $agent["agentCallbackUrl"]: $game["callbackUrl"];

        //判断是否是测试订单
        if ($order["orderType"]) {
            $header = array(
                "Content-Type: application/x-www-form-urlencoded",
                "X-Order-Sandbox: 1"
            );
        } else {
            $header = array("Content-Type: application/x-www-form-urlencoded");
        }

        $res            = curl_post($callbackUrl, http_build_query($info), 30, $header);

        //回调LOG的信息
        $log = array(
            "game_id"   => $order["game_id"],
            "userCode"  => $order["userCode"],
            "orderId"   => $order["orderId"],
            "billNo"    => $order["billNo"],
            "res"       => $res,
            "url"       => $callbackUrl,
            "data"      => http_build_query($info),
            "time"      => time()
        );

        if (strtolower(trim($res)) === "success") {
            //添加回调LOG记录
            $log["status"] = 0;
            D("Api/Callback")->addLog($log);

            //判断是否是补单的
            if ($sup == 1) {
                //只上报新用户的充值
                $userRegDate = date('Y-m-d',$order['regTime']);
                $nowDate     = date('Y-m-d');
                if($userRegDate == $nowDate){
                    if ($order['type'] == 1) {
                        //安卓广告报送
                        $advterCall = array(
                            'agent'   => $order['agent'],
                            'game_id' => $order['game_id'],
                            'imei'    => $order['imei'],
                            'imei2'   => $order['imei2'],
                            'idfa'    => $order['idfa'],
                            'amount'  => $order['amount']
                        );
                        D('Api/ANDMatch')->activeReport($advterCall,2);//主动上报
                    }

                    if($order['type'] == 2){
                        //IOS广告报送
                        $iosData = array(
                            'advter_id' => $order['advter_id'],
                            'game_id'   => $order['game_id'],
                            'idfa'      => $order['idfa'],
                            'agent'     => $order['agent'],
                            'userCode'  => $order['userCode'],
                            'orderId'   => $order['orderId'],
                            'billNo'    => $order['billNo'],
                            'amount'    => $order['amount'],
                        );
                        D('Api/IOSMatch')->iosAdvterCallBack($iosData,2); //IOS数据上报
                    }
                }
            }

            //投放配置参数
            $adv_param  = D("Api/AdverParam")->getAdverParam($order["agent"]);
            if ($adv_param) {
                foreach ($adv_param as $adv_value) {
                    if ($adv_value) D("Api/SdkAdver")->submit($order, array(), $adv_value, 2);
                }
            }

            //保存游戏订单回调详情
            if (D("Api/Order")->saveOrder(array("gameOrderStatus" => 0, "callbackTime" => time(), "res" => $res), $order["orderId"])) {
                $return = array(
                    "Code"  => true,
                    "Msg"   => "游戏币发放成功！",
                    "Res"   => $res,
                );
                return $return;
            } else {
                $return = array(
                    "Code"  => false,
                    "Msg"   => "修改订单成功！",
                    "Res"   => $res,
                );
                return $return;
            }
        } else {
            //添加回调LOG记录
            $log["status"] = 1;
            D("Api/Callback")->addLog($log);

            //保存游戏订单回调详情
            D("Api/Order")->saveOrder(array("res" => $res), $order["orderId"]);

            $return = array(
                "Code"  => false,
                "Msg"   => "回调游戏失败！",
                "Res"   => $res,
            );
            return $return;
        }
    }

    /**
     * 渠道回调接口
     */
    public function ChannelCallback()
    {
        //获取渠道ID
        $channel_id     = $_GET["CyChannelId"];
        if (!$channel_id || !is_numeric($channel_id)) $this->channelCallbackErr();
        //获取版本
        $channel_ver    = $_GET["CyChannelVer"];

        //获取渠道信息
        $channel        = D("Api/Channel")->getChannel($channel_id);
        //模块名称
        $channel_name   = ucfirst($channel["channelAbbr"]).($channel_ver > 1? $channel_ver: "");

        //获取数据
        if (!method_exists(D("Fusion/".$channel_name), "getInput")) $this->channelCallbackErr();
        $data           = D("Fusion/".$channel_name)->getInput();
        log_save("[ip]".get_ip_address()."    [data]".json_encode($data)."    [msg]info", "info", "", $channel_name."_pay_info");

        //验证
        $info           = D("Fusion/".$channel_name)->callbackCheck($data);
        //验证失败
        if (!$info) {
            log_save("[ip]".get_ip_address()."    [data]".json_encode($data)."    [msg]check_error    [res]".json_encode($info), "info", "", $channel_name."_pay_err");
            D("Fusion/".$channel_name)->callbackErr(1, $data);
        }

        //判断订单是否是成功订单
        if ($info["status"] != "success") {
            log_save("[ip]".get_ip_address()."    [data]".json_encode($data)."    [msg]not_success", "info", "", $channel_name."_pay_err");
            D("Fusion/".$channel_name)->callbackSuc($data);
        }

        //获取订单信息
        $order = D("Api/Order")->getOrderById($info["orderId"]);
        //订单信息不匹配
        if (!$order || $order["channel_id"] != $channel_id) {
            log_save("[ip]".get_ip_address()."    [data]".json_encode($data)."    [msg]info_error", "info", "", $channel_name."_pay_err");
            D("Fusion/".$channel_name)->callbackErr(2, $data);
        }

        //订单已回调成功
        if ($order["orderStatus"] == 0) {
            log_save("[ip]".get_ip_address()."    [data]".json_encode($data)."    [msg]had_success", "info", "", $channel_name."_pay_err");
            D("Fusion/".$channel_name)->callbackSuc($data);
        }

        //获取商品数据
        $goods = D("Api/Goods")->getGoods($order["goodsCode"]);
        //判断商品价格是否一致
        if ($goods["amount"] != $order["amount"] || $goods["amount"] != $info["amount"]) {
            log_save("[ip]".get_ip_address()."    [data]".json_encode($data)."    [msg]amount_error", "info", "", $channel_name."_pay_err");
            D("Fusion/".$channel_name)->callbackErr(3, $data);
        }

        //如果存在充值用户信息，则判断用户是否一致
        if (isset($info["userCode"])) {
            $user = D("Api/User")->getUserByCode($order["userCode"]);
            if ($user["channelUserCode"] != $info["userCode"]) {
                log_save("[ip]".get_ip_address()."    [data]".json_encode($data)."    [msg]user_error{pay_user:".$info["userCode"].",login_user:".$user["channelUserCode"]."}", "info", "", $channel_name."_pay_err");
                D("Fusion/".$channel_name)->callbackErr(5, $data);
            }
        }

        //记录数据
        $save = array(
            "tranId"        => $info["tranId"],
            "orderStatus"   => 0,
            "paymentTime"   => time(),
            "orderType"     => (isset($info["sandbox"]) && $info["sandbox"] == "1")? 1: 0
        );
        if (!D("Api/Order")->saveOrder($save, $info["orderId"])) {
            log_save("[ip]".get_ip_address()."    [data]".json_encode($data)."    [msg]save_error", "info", "", $channel_name."_pay_err");
            D("Fusion/".$channel_name)->callbackErr(4, $data);
        }

        $time = time();
        //记录首次的支付时间
        D("Api/UserGame")->saveFirstPay($order["userCode"], $order["game_id"], $time);
        D("Api/Role")->saveFirstPay($order["game_id"], $order["userCode"], $order["roleId"], $time);

        //记录最后的支付时间
        D("Api/Device")->saveDeviceByUdid(array("lastPay" => $time), $order["udid"]);
        D("Api/User")->saveUser(array("lastPay" => $time), $order["userCode"]);
        D("Api/UserGame")->saveUserGame(array("lastPay" => $time, "lastPayRoleId" => $order["roleId"], "lastPayRoleName" => $order["roleName"], "lastPayServerId" => $order["serverId"], "lastPayServerName" => $order["serverName"]), $order["userCode"], $order["game_id"]);
        D("Api/DeviceGame")->saveDeviceGame(array("lastPay" => $time), $order["udid"], $order["game_id"]);

        //游戏回调
        $this->gameCallback($info["orderId"]);

        log_save("[ip]".get_ip_address()."    [data]".json_encode($data)."    [msg]success", "info", "", $channel_name."_pay_info");

        //融合的热云游戏报送
        //每次充值报送
        $fusionReyun                = $order;
        $fusionUser                 = D("Api/User")->getUserByCode($order["userCode"]);
        $fusionReyun["channelCode"] = $fusionUser["channelUserCode"];
        $fusionReyun["tranId"]      = $info["tranId"];
        D("Api/ANDMatch")->gameReyunReport($fusionReyun, 5);

        D("Fusion/".$channel_name)->callbackSuc($data);
    }

    /**
     * 返回错误即可
     */
    private function channelCallbackErr()
    {
        header('HTTP/1.1 404 Not Found');
        exit();
    }

    /**
     * 应用宝的回调接口
     */
    public function YsdkCallback()
    {
        $date   = date("Ymd");
        //获取数据
        $input  = $this->getInput("post", "trim");

        log_save("[ip]".get_ip_address()."    [data]".json_encode($input)."    [msg]input", "info", "", "ysdk_pay_info_".$date);

        //判断用户ID是否存在
        if (!$input["Uid"]) {
            log_save("[ip]".get_ip_address()."    [data]".json_encode($input)."    [msg]no_uid", "info", "", "ysdk_pay_err_".$date);

            $res = array(
                "Msg" => "数据异常！请重新请求！"
            );
            $this->returnMsg($res, 4, $input["Gid"], "无UID", 1, 0, $input["Version"]);
        }

        //解密出来的数据
        $data   = $this->getDecrypt($input);

        log_save("[ip]".get_ip_address()."    [data]".json_encode($data)."    [msg]data", "info", "", "ysdk_pay_info_".$date);

        $res    = D("Fusion/Ysdk")->callback($data);

        //获取订单信息
        $order = D("Api/Order")->getOrderById($data["orderId"]);

        //失败回调
        if ($res["code"]) {
            log_save("[ip]".get_ip_address()."    [data]".json_encode($data)."    [res]".json_encode($res)."    [msg]pay", "info", "", "ysdk_pay_err_".$date);

            if ($res["code"] == 1) {
                //如果因为余额而失败的，添加进失败记录
                $data["orderAmount"]    = $order["amount"];
                $data["balance"]        = $res["balance"];
                D("Api/YsdkPayFailOrder")->addOrder($data);
            }

            if ($res["code"] == 2) {
                //无参数的回调
                log_save("[ip]".get_ip_address()."    [data]".json_encode($data)."    [res]".json_encode($res)."    [msg]pay", "info", "", "ysdk_pay_lost_".$date);
            }

            $res = array(
                "Msg" => "充值失败！"
            );
            $this->returnMsg($res, 5, $input["Gid"], "充值失败", 0, $input["Uid"], $input["Version"]);
        }

        //记录数据
        $save = array(
            "tranId"        => $data["orderId"],
            "orderStatus"   => 0,
            "orderType"     => $res["test"],
            "paymentTime"   => time()
        );
        if (!D("Api/Order")->saveOrder($save, $data["orderId"])) {
            log_save("[ip]".get_ip_address()."    [data]".json_encode($data)."    [res]".json_encode($res)."    [msg]save_error", "info", "", "ysdk_pay_err_".$date);

            $res = array(
                "Msg" => "记录订单失败！"
            );
            $this->returnMsg($res, 5, $input["Gid"], "记录订单失败", 1, 0, $input["Version"]);
        }

        $time = time();
        //记录首次的支付时间
        D("Api/UserGame")->saveFirstPay($order["userCode"], $order["game_id"], $time);
        D("Api/Role")->saveFirstPay($order["game_id"], $order["userCode"], $order["roleId"], $time);

        //记录最后的支付时间
        D("Api/Device")->saveDeviceByUdid(array("lastPay" => $time), $order["udid"]);
        D("Api/User")->saveUser(array("lastPay" => $time), $order["userCode"]);
        D("Api/UserGame")->saveUserGame(array("lastPay" => $time, "lastPayRoleId" => $order["roleId"], "lastPayRoleName" => $order["roleName"], "lastPayServerId" => $order["serverId"], "lastPayServerName" => $order["serverName"]), $order["userCode"], $order["game_id"]);
        D("Api/DeviceGame")->saveDeviceGame(array("lastPay" => $time), $order["udid"], $order["game_id"]);

        //游戏回调
        $this->gameCallback($data["orderId"]);

        log_save("[ip]".get_ip_address()."    [data]".json_encode($data)."    [msg]success_".json_encode($res), "info", "", "ysdk_pay_info_".$date);

        $res = array(
            "Msg" => "充值成功！"
        );
        $this->returnMsg($res, 0, $input["Gid"], "充值成功", 1, 0, $input["Version"]);
    }
}