<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/6/14
 * Time: 9:38
 *
 * 充值回调补单控制器
 */

namespace ThirdParty\Controller;

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
            1 => 15,                //与上次间距15分钟
            2 => 45,                //与上次间距30分钟
            3 => 105,               //与上次间距60分钟
            4 => 225,               //与上次间距120分钟
            5 => 465,               //与上次间距240分钟
            6 => 825,               //与上次间距360分钟
            7 => 1545,              //与上次间距720分钟
            8 => 2985               //与上次间距1440分钟
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
                //判断脚本是否已经跑了14分钟以上，跑过14分钟则停止脚本
                if (time() - $time > 840) {
                    $this->logSave("[time:{$date}    now:".date("Y-m-d H:i:s")."]Pay/Supplement  End[TimeOut]", "pay_supplement_log");
                    exit();
                }

                $this->logSave("[time:{$date}    now:".date("Y-m-d H:i:s")."][callback begin]orderId:".$val["orderId"]);
                $res = R("Api/Reply/gameCallback", array($val["orderId"]));
                $this->logSave("[time:{$date}    now:".date("Y-m-d H:i:s")."][callback end]orderId:".$val["orderId"]."    res:".json_encode($res));
            }

            //判断脚本是否已经跑了14分钟以上，跑过14分钟则停止脚本
            if (time() - $time > 840) {
                $this->logSave("[time:{$date}    now:".date("Y-m-d H:i:s")."]Pay/Supplement  End[TimeOut]");
                exit();
            }
        }

        //不同的回调失败次数对应不同的时间间隔
        $limit  = array(
            1 => 15,                //与上次间距15分钟
            2 => 30,                //与上次间距30分钟
            3 => 60,                //与上次间距60分钟
            4 => 120,               //与上次间距120分钟
            5 => 240,               //与上次间距240分钟
            6 => 360,               //与上次间距360分钟
            7 => 720,               //与上次间距720分钟
            8 => 1440               //与上次间距1440分钟
        );

        //后处理苹果失败的订单
        for ($i = 1; $i < 9; $i ++) {
            //获取不同次数中已经达到回调时间的回调失败订单
            $map    = array(
                "num"           => $i,
                "status"        => 1,
                "lastTime"      => array("ELT", $time - $limit[$i] * 60)
            );
            $order  = D("ThirdParty/AppleQuerys")->getQuerysByMap($map);

            //回调失败的订单
            foreach ($order as $val) {
                //判断脚本是否已经跑了14分钟以上，跑过14分钟则停止脚本
                if (time() - $time > 840) {
                    $this->logSave("Pay/Supplement  End[TimeOut]");
                    exit();
                }

                $this->logSave("[time:{$date}    now:".date("Y-m-d H:i:s")."][callback begin]appleOrder:".$val["transactionId"]);
                $res = $this->AppleStoreQuerys($val["receiptData"], $val["transactionId"], $val["game_id"], $val["userCode"], $val["goodsCode"], $val["udid"], $val["num"], $val["agent"]);
                $this->logSave("[time:{$date}    now:".date("Y-m-d H:i:s")."][callback end]appleOrder:".$val["transactionId"]."    res:".json_encode($res));
            }

            //判断脚本是否已经跑了14分钟以上，跑过14分钟则停止脚本
            if (time() - $time > 840) {
                $this->logSave("[time:{$date}    now:".date("Y-m-d H:i:s")."]Pay/Supplement  End[TimeOut]");
                exit();
            }
        }
        $this->logSave("[time:{$date}    now:".date("Y-m-d H:i:s")."]Pay/Supplement  End");
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

            D("ThirdParty/AppleQuerys")->saveQuerys($query, $transactionId);

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

                    D("ThirdParty/AppleQuerys")->saveQuerys($query, $transactionId);

                    return "order false";
                }

                //修改订单状态
                if (!D("Api/Order")->saveOrder(array("orderStatus" => 0, "paymentTime" => $time), $order["orderId"])) {
                    //将错误信息存进数据库
                    $query = array(
                        "num"       => $num + 1,
                        "lastTime"  => $time
                    );

                    D("ThirdParty/AppleQuerys")->saveQuerys($query, $transactionId);

                    return "save false";
                };
            }

            //判断是否需要进行游戏补单
            if ($order["gameOrderStatus"]) {
                //进行游戏补单
                $res = R("Api/Reply/gameCallback", array($order["orderId"]));

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

                D("ThirdParty/AppleQuerys")->saveQuerys($query, $transactionId);

                return json_encode($res);
            }

            //更新错误信息存进数据库
            $query = array(
                "num"       => $num + 1,
                "lastTime"  => $time,
                "status"    => 0
            );

            D("ThirdParty/AppleQuerys")->saveQuerys($query, $transactionId);

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

                D("ThirdParty/AppleQuerys")->saveQuerys($query, $transactionId);

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

                D("ThirdParty/AppleQuerys")->saveQuerys($query, $transactionId);

                return "save false";
            };

            //进行游戏补单
            R("Api/Reply/gameCallback", array($bill["orderId"]));

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
            'path'      => LOG_PATH.'debug'.DS.date('Ym').DS,
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