<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/6/14
 * Time: 9:38
 *
 * 充值回调补单控制器
 */

namespace Cy\Controller;

use Think\Controller;

class PayController extends Controller
{

    /**
     * 补单接口
     */
    public function Supplement()
    {

        //获取当前时间
        $time   = time();
        $date   = date("Y-m-d H:i:s");
        $this->logSave("[time:{$date}    now:".date("Y-m-d H:i:s")."]Pay/Supplement  Begin");

        //不同的回调失败次数对应不同的时间间隔
        $limit  = array(
            1 => 1,                 //与上次间距1分钟
            2 => 3,                 //与上次间距2分钟
            3 => 8,                 //与上次间距5分钟
            4 => 18,                //与上次间距10分钟
            5 => 33,                //与上次间距15分钟
            6 => 63,                //与上次间距30分钟
            7 => 123,               //与上次间距60分钟
            8 => 243                //与上次间距120分钟
        );

        //先处理发送游戏失败的订单
        for ($i = 1; $i < 9; $i ++) {
            //获取不同次数中已经达到回调时间的回调失败订单
            $map    = array(
                "num"               => $i,
                "orderStatus"       => 0,
                "gameOrderStatus"   => 1,
                "paymentTime"       => array("ELT", $time - $limit[$i] * 60)
            );
            C("DB_PREFIX", "lg_");
            $order = M("order")->where($map)->select();
            //回调失败的订单
            foreach ($order as $val) {
                //判断脚本是否已经跑了55秒以上，跑过55秒则停止脚本
                if (time() - $time > 55) {
                    $this->logSave("[time:{$date}    now:".date("Y-m-d H:i:s")."]Pay/Supplement  End[TimeOut]", "pay_supplement_log");
                    exit();
                }

                $this->logSave("[time:{$date}    now:".date("Y-m-d H:i:s")."][callback begin]orderId:".$val["orderId"]);
                $res = R("Api/Reply/gameCallback", array($val["orderId"], 0));
                $this->logSave("[time:{$date}    now:".date("Y-m-d H:i:s")."][callback end]orderId:".$val["orderId"]."    res:".json_encode($res));
            }

            //判断脚本是否已经跑了55秒以上，跑过55秒则停止脚本
            if (time() - $time > 55) {
                $this->logSave("[time:{$date}    now:".date("Y-m-d H:i:s")."]Pay/Supplement  End[TimeOut]");
                exit();
            }
        }

        //不同的回调失败次数对应不同的时间间隔
        $limit  = array(
            1 => 1,                 //与上次间距1分钟
            2 => 2,                 //与上次间距2分钟
            3 => 5,                 //与上次间距5分钟
            4 => 10,                //与上次间距10分钟
            5 => 15,                //与上次间距15分钟
            6 => 30,                //与上次间距30分钟
            7 => 60,                //与上次间距60分钟
            8 => 120                //与上次间距120分钟
        );

        //后处理苹果失败的订单
        for ($i = 1; $i < 9; $i ++) {
            //获取不同次数中已经达到回调时间的回调失败订单
            $map    = array(
                "num"           => $i,
                "status"        => 1,
                "lastTime"      => array("ELT", $time - $limit[$i] * 60)
            );
            $order  = D("Admin/AppleQuerys")->getQuerysByMap($map);

            //回调失败的订单
            foreach ($order as $val) {
                //判断脚本是否已经跑了55秒以上，跑过55秒则停止脚本
                if (time() - $time > 55) {
                    $this->logSave("Pay/Supplement  End[TimeOut]");
                    exit();
                }

                $this->logSave("[time:{$date}    now:".date("Y-m-d H:i:s")."][callback begin]appleOrder:".$val["transactionId"]);
                $res = $this->AppleStoreQuerys($val["receiptData"], $val["transactionId"], $val["game_id"], $val["userCode"], $val["goodsCode"], $val["udid"], $val["num"], $val["agent"]);
                $this->logSave("[time:{$date}    now:".date("Y-m-d H:i:s")."][callback end]appleOrder:".$val["transactionId"]."    res:".json_encode($res));
            }

            //判断脚本是否已经跑了55秒以上，跑过55秒则停止脚本
            if (time() - $time > 55) {
                $this->logSave("[time:{$date}    now:".date("Y-m-d H:i:s")."]Pay/Supplement  End[TimeOut]");
                exit();
            }
        }
        $this->logSave("[time:{$date}    now:".date("Y-m-d H:i:s")."]Pay/Supplement  End");
    }

    /**
     * 腾讯应用宝补单
     */
    public function YsdkSupplement()
    {
        //获取当前时间
        $time   = time();
        $date   = date("Y-m-d H:i:s");
        $this->logSave("[time:{$date}    now:".date("Y-m-d H:i:s")."]Pay/YsdkSupplement  Begin", "ysdk_pay_supplement_log");

        //不同的回调失败次数对应不同的时间间隔
        $limit  = array(
            0 => 2,             //与上次间距2分钟
            1 => 2,             //与上次间距2分钟
            2 => 2,             //与上次间距2分钟
            3 => 4,             //与上次间距4分钟
            4 => 6,             //与上次间距6分钟
        );

        C("DB_PREFIX", "lg_");
        //后处理苹果失败的订单
        for ($i = 0; $i < 5; $i ++) {
            //获取不同次数中已经达到回调时间的回调失败订单
            $map    = array(
                "num"           => $i,
                "status"        => 1,
                "updateTime"    => array("ELT", $time - $limit[$i] * 60)
            );
            $order  = D("Admin/YsdkPayFailOrder")->getYsdkOrder($map);

            //回调失败的订单
            foreach ($order as $val) {
                //判断脚本是否已经跑了一分半以上，跑过一分半则停止脚本
                if (time() - $time > 90) {
                    $this->logSave("[time:{$date}    now:".date("Y-m-d H:i:s")."]Pay/YsdkSupplement  End[TimeOut]", "ysdk_pay_supplement_log");
                    exit();
                }

                $success = D("Admin/Order")->getOrder($val["orderId"]);

                //订单不存在
                if (!$success) {
                    $this->logSave("[time:{$date}    now:".date("Y-m-d H:i:s")."][no order]order:".$val["orderId"], "ysdk_pay_supplement_log");

                    $data["num"]            = $val["num"] + 1;
                    $data["status"]         = 1;
                    $data["updateTime"]     = time();
                    //记录失败次数
                    D("Admin/YsdkPayFailOrder")->saveYsdkOrder($data, $val["orderId"]);
                    continue;
                }
                //订单已经成功
                if (!$success["orderStatus"]) {
                    $this->logSave("[time:{$date}    now:".date("Y-m-d H:i:s")."][already success]order:".$val["orderId"], "ysdk_pay_supplement_log");

                    $data["num"]            = $val["num"] + 1;
                    $data["status"]         = 0;
                    $data["updateTime"]     = time();
                    //记录成功
                    D("Admin/YsdkPayFailOrder")->saveYsdkOrder($data, $val["orderId"]);
                    continue;
                }

                //开始
                $this->logSave("[time:{$date}    now:".date("Y-m-d H:i:s")."][order begin]order:".$val["orderId"], "ysdk_pay_supplement_log");

                $res    = D("Fusion/Ysdk")->callback($val);
                $this->logSave("[time:{$date}    now:".date("Y-m-d H:i:s")."][order end]order:".$val["orderId"]."    res:".json_encode($res), "ysdk_pay_supplement_log");
                //失败回调
                if ($res["code"]) {
                    $this->logSave("[time:{$date}    now:".date("Y-m-d H:i:s")."][callback error]order:".$val["orderId"], "ysdk_pay_supplement_log");
                    if ($res["code"] == 1) {
                        //如果因为余额而失败的，添加进失败记录
                        $data["balance"]    = $res["balance"];
                    }

                    $data["num"]            = $val["num"] + 1;
                    $data["status"]         = 1;
                    $data["updateTime"]     = time();

                    //记录失败次数
                    D("Admin/YsdkPayFailOrder")->saveYsdkOrder($data, $val["orderId"]);
                    continue;
                }

                //记录数据
                $save = array(
                    "tranId"        => $val["orderId"],
                    "orderStatus"   => 0,
                    "orderType"     => $res["test"],
                    "paymentTime"   => time()
                );
                if (!D("Api/Order")->saveOrder($save, $val["orderId"])) {
                    $this->logSave("[time:{$date}    now:".date("Y-m-d H:i:s")."][save error]order:".$val["orderId"], "ysdk_pay_supplement_log");
                }

                //记录最后的支付时间
                D("Api/Device")->saveDeviceByUdid(array("lastPay" => $time), $order["udid"]);
                D("Api/User")->saveUser(array("lastPay" => $time), $order["userCode"]);
                D("Api/UserGame")->saveUserGame(array("lastPay" => $time, "lastPayRoleId" => $order["roleId"], "lastPayRoleName" => $order["roleName"], "lastPayServerId" => $order["serverId"], "lastPayServerName" => $order["serverName"]), $order["userCode"], $order["game_id"]);
                D("Api/DeviceGame")->saveDeviceGame(array("lastPay" => $time), $order["udid"], $order["game_id"]);

                //进行游戏补单
                R("Api/Reply/gameCallback", array($val["orderId"], 0));

                $data["num"]            = $val["num"] + 1;
                $data["status"]         = 0;
                $data["updateTime"]     = time();
                //记录成功
                D("Admin/YsdkPayFailOrder")->saveYsdkOrder($data, $val["orderId"]);
            }

            //判断脚本是否已经跑了一分半以上，跑过一分半则停止脚本
            if (time() - $time > 90) {
                $this->logSave("[time:{$date}    now:".date("Y-m-d H:i:s")."]Pay/YsdkSupplement  End[TimeOut]", "ysdk_pay_supplement_log");
                exit();
            }
        }
        $this->logSave("[time:{$date}    now:".date("Y-m-d H:i:s")."]Pay/YsdkSupplement  End", "ysdk_pay_supplement_log");
    }

    /**
     * 苹果支付回调接口
     * @param $receiptData
     * @param $transactionId
     * @param $game_id
     * @param $userCode
     * @param $goodsCode
     * @param $udid
     * @param $num
     * @param $agentId
     * @return string
     */
    private function AppleStoreQuerys($receiptData, $transactionId, $game_id, $userCode, $goodsCode, $udid, $num, $agentId)
    {
        //获取商品数据
        $goods = D("Api/Goods")->getGoods($goodsCode);
        if (!$goods || $goods["game_id"] != $game_id) return "goods false";

        //获取渠道号信息
        $agent = D("Api/Agent")->getAgent($agentId);
        if (!$agent || $agent["game_id"] != $game_id) {
            return "agent false";
        }

        //日期
        $time   = time();

        //进行苹果支付回调操作
        $ApplePay = D("Api/ThirdPartyPayment")->appleStorePay($receiptData, $transactionId, $game_id, $userCode, $goodsCode, $goods["amount"], $agent["bundleId"]);

        //判断是否回调失败
        if (!$ApplePay["result"]) {
            //信息数据
            $query = array(
                "num"       => $num + 1,
                "lastTime"  => $time
            );

            D("Admin/AppleQuerys")->saveQuerys($query, $transactionId);

            return "result false";
        }

        //记录最后的支付时间
        D("Api/Device")->saveDeviceByUdid(array("lastPay" => $time), $udid);
        D("Api/User")->saveUser(array("lastPay" => $time), $userCode);
        D("Api/DeviceGame")->saveDeviceGame(array("lastPay" => $time), $udid, $game_id);

        //判断订单是否是回调成功的
        $order = D("Api/Order")->getOrderByMap(array("tranId" => $transactionId));
        if ($order) {
            //记录最后的支付时间
            D("Api/UserGame")->saveUserGame(array("lastPay" => $time, "lastPayRoleId" => $order["roleId"], "lastPayRoleName" => $order["roleName"], "lastPayServerId" => $order["serverId"], "lastPayServerName" => $order["serverName"]), $userCode, $game_id);

            //已经存在的订单
            if ($order["orderStatus"]) {
                //未充值成功的订单，进行订单数据判断
                if ($order["amount"] != $goods["amount"] || $order["userCode"] != $userCode || $order["game_id"] != $game_id) {
                    //将错误信息存进数据库
                    $query = array(
                        "num"       => $num + 1,
                        "lastTime"  => $time
                    );

                    D("Admin/AppleQuerys")->saveQuerys($query, $transactionId);

                    return "order false";
                }

                //修改订单状态
                if (!D("Api/Order")->saveOrder(array("orderStatus" => 0, "paymentTime" => $time), $order["orderId"])) {
                    //将错误信息存进数据库
                    $query = array(
                        "num"       => $num + 1,
                        "lastTime"  => $time
                    );

                    D("Admin/AppleQuerys")->saveQuerys($query, $transactionId);

                    return "save false";
                };
            }

            //判断是否需要进行游戏补单
            if ($order["gameOrderStatus"]) {
                //进行游戏补单
                $res = R("Api/Reply/gameCallback", array($order["orderId"], 0));

                //更新错误信息存进数据库
                $query = array(
                    "num"       => $num + 1,
                    "lastTime"  => $time
                );

                if ($res["Code"]) {
                    $query["status"] = 0;
                } else {
                    $query["status"] = 1;
                }

                D("Admin/AppleQuerys")->saveQuerys($query, $transactionId);

                return json_encode($res);
            }

            //更新错误信息存进数据库
            $query = array(
                "num"       => $num + 1,
                "lastTime"  => $time,
                "status"    => 0
            );

            D("Admin/AppleQuerys")->saveQuerys($query, $transactionId);

            return "true";
        } else {
            //未记录的订单
            $map = array(
                "userCode"      => $userCode,
                "goodsCode"     => $goodsCode,
                "agent"         => $agentId,
                "amount"        => $goods["amount"],
                "orderStatus"   => 1,
                "game_id"       => $game_id,
                "createTime"    => array("GT", $time - 86400)
            );

            //寻找订单进行匹配
            $bill = D("Api/Order")->getOrderByMap($map);

            //为获取相对应的订单
            if (!$bill) {
                //将错误信息存进数据库
                $query = array(
                    "num"       => $num + 1,
                    "lastTime"  => $time
                );

                D("Admin/AppleQuerys")->saveQuerys($query, $transactionId);

                return "order false";
            }

            //记录最后的支付时间
            D("Api/UserGame")->saveUserGame(array("lastPay" => $time, "lastPayRoleId" => $bill["roleId"], "lastPayRoleName" => $bill["roleName"], "lastPayServerId" => $bill["serverId"], "lastPayServerName" => $bill["serverName"]), $userCode, $game_id);

            //保存的订单信息
            $info = array(
                "tranId"        => $transactionId,
                "orderStatus"   => 0,
                "paymentTime"   => $time
            );

            //保存订单失败
            if (!D("Api/Order")->saveOrder($info, $bill["orderId"])) {
                //将错误信息存进数据库
                $query = array(
                    "num"       => $num + 1,
                    "lastTime"  => $time
                );

                D("Admin/AppleQuerys")->saveQuerys($query, $transactionId);

                return "save false";
            };

            //进行游戏补单
            R("Api/Reply/gameCallback", array($bill["orderId"], 0));

            //将错误信息存进数据库
            $query = array(
                "num"       => $num + 1,
                "lastTime"  => $time,
                "status"    => 0
            );

            D("AppleQuerys")->addQuery($query);

            return "true";
        }
    }

    /**
     * 日志记录接口
     * @param $data
     * @param string $filename
     * @return bool
     */
    private function logSave($data, $filename = 'pay_supplement_log')
    {
        $config = array(
            'path'      => LOG_PATH.'cli'.DS.date('Ym').DS,
            'file_size' => 2097152
        );

        !is_dir($config['path']) && mkdir($config['path'], 0777, true);
        $destination = $config['path'].(empty($filename)? date('y-m-d').'.log': date('y-m-d').'-'.$filename);

        if (is_file($destination) && floor($config['file_size']) <= filesize($destination)) {
            rename($destination, dirname($destination).DS.$_SERVER['REQUEST_TIME'].'-'.basename($destination));
        }

        is_array($data) && $data = json_encode($data);
        error_log('【'.date('Ymd H:i:s').'】【'.MODULE_NAME.'/'.ACTION_NAME.'】'.$data."\r\n", 3, $destination);

        return true;
    }
}