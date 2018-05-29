<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/6/19
 * Time: 14:09
 *
 * 数据管理控制器
 */

namespace Admin\Controller;

class DataController extends BackendController
{
    private $specialData = null;

    /**
     * 订单列表
     */
    public function order()
    {
        if (IS_POST) {
            $data = I();
            //搜索条件
            $start                                     = $data['start'] ? $data['start'] : 0;
            $pageSize                                  = $data['limit'] ? $data['limit'] : 30;
            $agentStr                                  = implode("','", $this->agentArr);
            $map["_string"]                            = "agent IN ('" . $agentStr . "')";
            !is_null($this->gameId) && $map['game_id'] = array('in', $this->gameId); //CP权限控制
            $data['orderId'] && $map['orderId']        = $data['orderId'];
            $data['billNo'] && $map['billNo']          = $data['billNo'];
            $data['tranId'] && $map['tranId']          = $data['tranId'];
            $data['serverId'] && $map['serverId']      = $data['serverId'];
            $data['userCode'] && $map['userCode']      = $data['userCode'];
            $data['roleName'] && $map['roleName']      = $data['roleName'];
            $data['agent'] && $map['agent']            = $data['agent'];
            if ($data['game_id']) {
                $map['game_id'] = $data['game_id'];
            } else {
                if (!in_array(session('admin.role_id'), array(1, 3))) {
                    $map['game_id'] = array('neq', 108);
                }
            }
            $data['channelId'] && $map['channel_id'] = $data['channelId'];
            if ($data["startDate"] && $data["endDate"]) {
                $map["createTime"] = array("BETWEEN", array(strtotime($data["startDate"]), strtotime($data["endDate"])));
            } elseif ($data["startDate"]) {
                $map["createTime"] = array("EGT", strtotime($data["startDate"]));
            } elseif ($data["endDate"]) {
                $map["createTime"] = array("ELT", strtotime($data["endDate"]));
            }
            switch ($data["status"]) {
                case 1:
                    $map["orderStatus"] = $map["gameOrderStatus"] = 1;
                    break;
                case 2:
                    $map["orderStatus"]     = 0;
                    $map["gameOrderStatus"] = 1;
                    break;
                case 3:
                    $map["orderStatus"] = $map["gameOrderStatus"] = 0;
                    break;
                case 4:
                    $map["orderStatus"] = 0;
                    break;
            }

            if ($data['orderType']) {
                $data['orderType'] == 1 && $map['orderType'] = 0; //正式订单
                $data['orderType'] == 2 && $map['orderType'] = 1; //测试订单
            }

            switch ($data["payType"]) {
                case 1:
                    $map["_string"] .= " AND payType = 1 AND channel_id <= 1";
                    break;
                case 2:
                    $map["_string"] .= " AND payType = 2 AND channel_id <= 1";
                    break;
                case 3:
                    $map["_string"] .= " AND payType = 3 AND channel_id <= 1";
                    break;
                case 4:
                    $map["_string"] .= " AND payType = 0 AND channel_id <= 1 AND type = 2";
                    break;
            }

            //去除三个刷单的非法用户
            if ($data['userName']) {
                $map['userName'] = $data['userName'];
            } else {
                $map['userName'] = array('not in', array('Lgame41400432', 'Love100200', 'Lgame49382694'));
            }

            $count   = D("Admin/Order")->getCount($map);
            $sum     = D("Admin/Order")->getSum($map);
            $row     = D("Admin/Order")->getOrderLimit($map, $start, $pageSize);
            $agent   = getDataList("agent", "agent", C("DB_PREFIX_API"));
            $channel = getDataList("channel", "id", C("DB_PREFIX_API"));

            foreach ($row as $k => $v) {
                if ($v['agent'] == 'jyjhAAA') {
                    $v['agent'] = $row[$k]['agent'] = 'ytxjlAND048';
                }
                if ($v["orderStatus"]) {
                    $row[$k]["status"] = $data['export'] == 1 ? '待充值' : "<span style='color: red;'>待充值</span>";
                } elseif (!$v["orderStatus"] && $v["gameOrderStatus"]) {
                    $row[$k]["status"] = $data['export'] == 1 ? '未发货' : "<span style='color: blue;'>未发货</span>";
                } elseif (!$v["orderStatus"] && !$v["gameOrderStatus"]) {
                    $row[$k]["status"] = $data['export'] == 1 ? '已完成' : "<span style='color: green'>已完成</span>";
                }
                if ($v["orderType"]) {
                    $row[$k]["orderTypeName"] = $data['export'] == 1 ? '测试订单' : "<span style='color: red;'>测试订单</span>";
                } else {
                    $row[$k]["orderTypeName"] = $data['export'] == 1 ? '正式订单' : "<span style='color: green'>正式订单</span>";
                }
//                if($data['export'] == 1){
                //                    if ($v["orderStatus"]) {
                //                        $type       = "无";
                //                        $type_abbr  = null;
                //                    } else {
                if ($v["channel_id"] <= 1) {
                    switch ($v["payType"]) {
                        case 1:
                            $type      = "支付宝";
                            $type_abbr = "AliPay";
                            break;
                        case 2:
                            $type      = "微信";
                            $type_abbr = "WeixinPay";
                            break;
                        case 3:
                            $type      = "银联";
                            $type_abbr = "UnionPay";
                            break;
                        case 0:
                            if ($v["type"] == 2 && !$v["orderStatus"]) {
                                $type      = "苹果";
                                $type_abbr = "ApplePay";
                            } elseif (!$v["orderStatus"]) {
                                $type      = "渠道";
                                $type_abbr = null;
                            } else {
                                $type      = "无";
                                $type_abbr = null;
                            };
                            break;
                        default:
                            $type      = "无";
                            $type_abbr = null;
                    }
                } else {
                    $type      = $channel[$v['channel_id']]['channelName'];
                    $type_abbr = null;
                }
//                    }
                $row[$k]['payType'] = $type;
                if ($data['export'] != 1) {
                    $row[$k]['payTypeName'] = $type_abbr ? '<img width="18px;" alt="' . $type . '" title="' . $type . '" src="' . C("TMPL_PARSE_STRING")["__IMG__"] . '/' . $type_abbr . '.png" />' : $type;
                }
//                }
                $row[$k]['amount']    = number_format($v['amount'], 2);
                $row[$k]["create"]    = date("Y-m-d H:i:s", $v["createTime"]);
                $row[$k]["regTime"]   = date("Y-m-d H:i:s", $v["regTime"]);
                $row[$k]["payment"]   = $v["paymentTime"] ? date("Y-m-d H:i:s", $v["paymentTime"]) : "（无）";
                $row[$k]["agentName"] = $agent[$v["agent"]]["agentName"];
                $row[$k]["opt"]       = createBtn('<a href="javascript:;" onclick="orderInfo(\'' . $v['orderId'] . '\',this)">详情</a>');
                if ((session("admin.role_id") == 1 || in_array(session("admin.uid"), $this->orderSupple)) && $v["orderStatus"]) {
                    $row[$k]["opt"] .= createBtn('&nbsp;<a href="javascript:;" onclick="orderSupplement(\'' . $v['orderId'] . '\',this)">补单</a>');
                }

            }

            //导出
            if ($data['export'] == 1) {
                $pageSummary = array('amount' => $sum);
                $col         = array('userCode' => '用户标识符', 'userName' => '用户账户', 'agent' => '渠道号', 'agentName' => '包名称', 'gameName' =>
                    '游戏名称', 'channelName' => '渠道名称', 'amount' => '充值金额', 'subject' => '商品名称', 'status' => '订单状态', 'orderId' => '订单号', 'billNo' => '游戏订单号', 'tranId' => '渠道订单号', 'orderTypeName' => '订单类型', 'payType' => '充值方式', 'create' => '下单时间', 'regTime' => '注册时间', 'payment' => '支付时间');
                array_unshift($row, $col);
                $pageSummary['userCode'] = '汇总';
                array_push($row, $pageSummary);
                export_to_csv($row, '订单列表', $col);
                exit();
            }

            $arr = array('rows' => $row, 'results' => $count, 'summary' => array('amount' => number_format($sum, 2)));
            exit(json_encode($arr));
        } else {
            $this->display();
        }
    }

    /**
     * 财务订单列表
     */
    public function incomeOrder()
    {

        if (IS_POST) {
            $data = I();
            //搜索条件
            $start    = $data['start'] ? $data['start'] : 0;
            $pageSize = $data['limit'] ? $data['limit'] : 30;
            // $agentStr   = implode("','", $this->agentArr);
            // $map["_string"] = "agent IN ('".$agentStr."')";
            if ($data['game_id']) {
                $map['a.game_id'] = $data['game_id'];
            } else {
                $map['a.game_id'] = array('NOT IN', array(104, 108));
            }
            if ($data["type"]) {
                switch ($data["type"]) {
                    case 1:
                        $map["a.payType"] = 0;
                        $map["a.type"]    = 2;
                        break;
                    case 2:
                        $map["a.payType"] = 1;
                        break;
                    case 3:
                        $map["a.payType"] = 2;
                        break;
                    case 4:
                        $map["a.payType"] = 3;
                        break;
                    default:
                        break;
                }
            }
            $map['a.agent'] = array('IN', array_keys(getDataList('agent', 'agent', C('DB_PREFIX_API'), array('platform_id' => 1, 'agent' => array('NEQ', 'jyjhTAPTAP')))));
            $startMonth     = (int) strtotime($data['startDate']);
            $endMonth       = (int) strtotime($data['endDate']);

            $map["a.orderStatus"] = 0; //只要我方收到款的
            $map['a.orderType']   = 0; //正式订单

            if ($data['department']) {
                //$pid = array_keys(getDataList('principal','id',C('DB_PREFIX'),array('department'=>$data['department'])));
                //找出对应的渠道号
                $agent = array_keys(getDataList('agent', 'agent', C('DB_PREFIX_API'), array('departmentId' => $data['department'])));
                if (empty($agent)) {
                    $map['a.agent'] = array('IN', '0');
                } else {
                    $map['a.agent'] = array('IN', $agent);
                }
            }

            if (!in_array(session('admin.role_id'), array(1, 17, 25))) {
                $map['_string'] = 'a.agent NOT IN("jyQIHOO","jyBAIDU","ceshi","jyDUOYOU","jyLEYOU")';
            }

            //去除三个刷单的非法用户
            $map['a.userName'] = array('not in', array('Lgame41400432', 'Love100200', 'Lgame49382694'));

            // $count     = D("Admin/Order")->getIncomeCount($map);
            // $sum       = D("Admin/Order")->getIncomeSum($map);
            if ($startMonth < strtotime('2017-10-01') && $endMonth < strtotime('2017-10-01')) {
                //2017年7,8,9月以下单时间算
                if ($data["startDate"] && $data["endDate"]) {
                    $map["a.createTime"] = array("BETWEEN", array(strtotime($data["startDate"]), strtotime($data["endDate"])));
                }
                $row = D("Admin/Order")->getIncomeOrderLimit($map, $start, $pageSize, 'createTime');
            } elseif ($startMonth < strtotime('2017-10-01') && $endMonth >= strtotime('2017-10-01')) {
                // exit(json_encode(array('hasError'=>true, 'error'=>'日期跨度不能越过九月三十号')));
                if ($data["startDate"] && $data["endDate"]) {
                    $map["a.createTime"] = array("BETWEEN", array(strtotime($data["startDate"]), strtotime('2017-09-30 23:59:59')));
                }
                $row1 = D("Admin/Order")->getIncomeOrderLimit($map, $start, $pageSize, 'createTime');

                unset($map['a.createTime']);
                $map["a.paymentTime"] = array("BETWEEN", array(strtotime('2017-10-01'), strtotime($data['endDate'])));
                $row2                 = D("Admin/Order")->getIncomeOrderLimit($map, $start, $pageSize, 'paymentTime');

                $row = array_merge($row1, $row2);

            } else {
                $createTimeIndex = false;

                //2017年10月以后以到账时间算
                if ($data["startDate"] && $data["endDate"]) {
                    $map["a.paymentTime"] = array("BETWEEN", array(strtotime($data["startDate"]), strtotime($data["endDate"])));
                } elseif ($data["startDate"]) {
                    $map["a.paymentTime"] = array("EGT", strtotime($data["startDate"]));
                } elseif ($data["endDate"]) {
                    $map["a.paymentTime"] = array("ELT", strtotime($data["endDate"]));
                }
                $row = D("Admin/Order")->getIncomeOrderLimit($map, $start, $pageSize, 'paymentTime');

            }

            $pagent  = getDataList("agent", "id", C("DB_PREFIX_API"), array('agentType' => 1)); //母包
            $agent   = getDataList("agent", "agent", C("DB_PREFIX_API"));
            $channel = getDataList("channel", "id", C("DB_PREFIX_API"));
            $newRow  = array();
            foreach ($row as $k => $v) {
                $row[$k]['mainBody'] = '海南创娱';
                if ($v["orderStatus"]) {
                    $type = "无";
                } else {
                    if ($v["channel_id"] <= 1) {
                        switch ($v["payType"]) {
                            case 1:
                                $type = "支付宝";
                                if (strtotime($v['payTime']) >= strtotime('2017-10-17')) {
                                    $row[$k]['realAmount'] = ($v['amount'] - ($v['amount'] * 0.006)); //0.6%
                                    $row[$k]['poundage']   = ($v['amount'] * 0.006); //手续费
                                } else {
                                    $row[$k]['realAmount'] = ($v['amount'] - ($v['amount'] * 0.008)); //0.8%
                                    $row[$k]['poundage']   = ($v['amount'] * 0.008); //手续费
                                }
                                break;
                            case 2:
                                $type                  = "微信";
                                $row[$k]['realAmount'] = ($v['amount'] - ($v['amount'] * 0.008)); //0.8%
                                $row[$k]['poundage']   = ($v['amount'] * 0.008); //手续费
                                break;
                            case 3:
                                $type                  = "银联";
                                $row[$k]['realAmount'] = ($v['amount'] - ($v['amount'] * 0.008)); //0.8%
                                $row[$k]['poundage']   = ($v['amount'] * 0.008); //手续费
                                break;
                            case 0:
                                if ($v["type"] == 2) {
                                    $type                  = "苹果";
                                    $row[$k]['realAmount'] = ($v['amount'] - ($v['amount'] * 0.3)); //30%
                                    $row[$k]['poundage']   = ($v['amount'] * 0.3); //手续费
                                } else {
                                    $type                  = "测试";
                                    $row[$k]['realAmount'] = 0;
                                    $row[$k]['poundage']   = 0;
                                };
                                break;
                            default:
                                $type                  = "测试";
                                $row[$k]['realAmount'] = 0;
                                $row[$k]['poundage']   = 0;
                        }
                    } else {

                        switch ($v["payType"]) {
                            //切支付情况
                            case 1:
                                $type = "支付宝";
                                if (strtotime($v['payTime']) >= strtotime('2017-10-17')) {
                                    $row[$k]['realAmount'] = ($v['amount'] - ($v['amount'] * 0.006)); //0.6%
                                    $row[$k]['poundage']   = ($v['amount'] * 0.006); //手续费
                                } else {
                                    $row[$k]['realAmount'] = ($v['amount'] - ($v['amount'] * 0.008)); //0.8%
                                    $row[$k]['poundage']   = ($v['amount'] * 0.008); //手续费
                                }
                                break;
                            case 2:
                                $type                  = "微信";
                                $row[$k]['realAmount'] = ($v['amount'] - ($v['amount'] * 0.008)); //0.8%
                                $row[$k]['poundage']   = ($v['amount'] * 0.008); //手续费
                                break;
                            case 3:
                                $type                  = "银联";
                                $row[$k]['realAmount'] = ($v['amount'] - ($v['amount'] * 0.008)); //0.8%
                                $row[$k]['poundage']   = ($v['amount'] * 0.008); //手续费
                                break;
                            case 0:
                                //融合本渠道
                                if ($v["type"] == 2) {
                                    $type                  = "苹果";
                                    $row[$k]['realAmount'] = ($v['amount'] - ($v['amount'] * 0.3)); //30%
                                    $row[$k]['poundage']   = ($v['amount'] * 0.3); //手续费
                                } else {
                                    $type                  = $channel[$v['channel_id']]['channelName'];
                                    $row[$k]['realAmount'] = ($v['amount'] - ($v['amount'] * $channel[$v['channel_id']]['rate']));
                                    $row[$k]['poundage']   = ($v['amount'] * $channel[$v['channel_id']]['rate']); //手续费
                                };
                                break;
                            default:
                                $type                  = "测试";
                                $row[$k]['realAmount'] = 0;
                                $row[$k]['poundage']   = 0;
                        }

                    }
                }
                $row[$k]['payType']    = $type;
                $row[$k]['department'] = $agent[$v['agent']]['departmentId'] == 1 ? '发行一部' : ($agent[$v['agent']]['departmentId'] == 2 ? '发行二部' : ($agent[$v['agent']]['departmentId'] == 3 ? '融合' : '未知'));
                $row[$k]["agentName"]  = $v['pid'] != 0 ? $pagent[$v["pid"]]["agentName"] : $agent[$v['agent']]['agentName'];
                $row[$k]["payTime"]    = date("Y/m/d", strtotime($data["startDate"])) . '—' . date("Y/m/d", strtotime($data["endDate"]));

                $newRow[$row[$k]["payTime"] . '_' . $row[$k]['department'] . '_' . $row[$k]['gameName'] . '_' . $row[$k]['agentName'] . '_' . $row[$k]['channelName'] . '_' . $type]['pay'] += $v['amount'];
                $newRow[$row[$k]["payTime"] . '_' . $row[$k]['department'] . '_' . $row[$k]['gameName'] . '_' . $row[$k]['agentName'] . '_' . $row[$k]['channelName'] . '_' . $type]['realAmount'] += $row[$k]['realAmount'];
                $newRow[$row[$k]["payTime"] . '_' . $row[$k]['department'] . '_' . $row[$k]['gameName'] . '_' . $row[$k]['agentName'] . '_' . $row[$k]['channelName'] . '_' . $type]['poundage'] += $row[$k]['poundage'];
            }
            unset($row);
            //处理newRow结果集
            foreach ($newRow as $key => $value) {
                $keys  = explode('_', $key);
                $row[] = array(
                    'mainBody'    => '海南创娱',
                    'payTime'     => $keys[0],
                    'department'  => $keys[1],
                    'gameName'    => $keys[2],
                    'agentName'   => $keys[3],
                    'channelName' => $keys[4],
                    'payType'     => $keys[5],
                    'amount'      => $value['pay'],
                    'realAmount'  => $value['realAmount'],
                    'poundage'    => $value['poundage'],
                );
            }

            $results = count($row);

            //导出
            if ($data['export'] == 1) {
                $pageSummary = array('amount' => array_sum(array_column($row, 'amount')));
                $col         = array('payTime' => '时间', 'mainBody' => '主体名称', 'department' => '部门', 'gameName' => '游戏名称', 'agentName' => '包名称', 'channelName' => '渠道名称', 'payType' => '充值方式', 'amount' => '充值金额', 'realAmount' => '实收金额', 'poundage' => '手续费');
                array_unshift($row, $col);
                $pageSummary['payTime'] = '汇总';
                array_push($row, $pageSummary);
                export_to_csv($row, '财务订单列表', $col);
                exit();
            } else {
                $row = array_slice($row, $start, $pageSize);
            }

            $arr = array('rows' => empty($row) ? array() : $row, 'results' => $results);
            exit(json_encode($arr));
        } else {
            $this->display();
        }
    }

    /**
     * 订单详情
     */
    public function orderInfo()
    {
        $orderId            = I("orderId");
        $order              = D("Admin/Order")->getOrder($orderId);
        $callback           = D("Admin/Callback")->getCallbackOneByOrderId($orderId);
        $agent              = D("Admin/Admin")->commonQuery("agent", array("agent" => $order["agent"]), 0, 1, '*', 'lg_');
        $order["agentName"] = $agent["agentName"];
        if ($order["orderStatus"]) {
            $type = "无";
        } else {
            if ($order["channel_id"] <= 1) {
                switch ($order["payType"]) {
                    case 1:
                        $type = "支付宝";
                        break;
                    case 2:
                        $type = "微信";
                        break;
                    case 0:
                        if ($order["type"] == 2) {
                            $type = "苹果";
                        } else {
                            $type = "渠道";
                        };
                        break;
                    default:
                        $type = "渠道";
                }
            } else {
                $channel = D("Admin/Admin")->commonQuery("channel", array("id" => $order["channel_id"]), 0, 1, '*', 'lg_');
                $type    = $channel["channelName"];
            }
        }

        $this->assign("type", $type);
        $this->assign("order", $order);
        $this->assign("callback", $callback);

        if (IS_AJAX) {
            $respose = $this->fetch();
            $this->ajaxReturn(array('status' => 1, '_html' => $respose));
        } else {
            $this->display();
        }
    }

    /**
     * 补单
     */
    public function orderSupplement()
    {
        if (IS_POST) {
            if (!(session("admin.role_id") == 1 || in_array(session("admin.uid"), $this->orderSupple))) {
                $this->error("您无此操作权限！");
            }
            $data = I();
            if (!$data["orderId"]) {
                $this->error("ID错误！");
            }
            if (!$data["tranId"] || !$data["paymentTime"]) {
                $this->error("有参数未输入！");
            }
            if (D("Admin")->commonExecute("order", array("orderId" => $data["orderId"]), array("tranId" => $data["tranId"], "paymentTime" => strtotime($data["paymentTime"]), "orderStatus" => 0, "num" => 1), C("DB_PREFIX_API"))) {
                $this->success("补单成功！");
            } else {
                $this->error("补单失败！");
            }
        } else {
            if (!(session("admin.role_id") == 1 || in_array(session("admin.uid"), $this->orderSupple))) {
                $this->ajaxReturn(array("status" => 0, "msg" => "您无此操作权限！"));
            } else {
                $orderId = I("orderId");
                $order   = D("Admin/Order")->getOrder($orderId);
                if ($order["orderStatus"]) {
                    $agent = D("Admin/Admin")->commonQuery("agent", array("agent" => $order["agent"]), 0, 1, '*', 'lg_');
                    $this->assign("order", $order);
                    $this->assign("agent", $agent);
                    $this->ajaxReturn(array("status" => 1, "html" => $this->fetch()));
                } else {
                    $this->ajaxReturn(array("status" => 0, "msg" => "该订单已补单！"));
                }
            }
        }
    }

    /**
     * 用户列表
     */
    public function user()
    {
        if (IS_POST) {
            $data = I();
            //搜索条件
            $start                                              = $data['start'] ? $data['start'] : 0;
            $pageSize                                           = $data['limit'] ? $data['limit'] : 30;
            $agentStr                                           = implode("','", $this->agentArr);
            $map["_string"]                                     = "agent IN ('" . $agentStr . "')";
            $data['userCode'] && $map['userCode']               = $data['userCode'];
            $data['userName'] && $map['userName']               = $data['userName'];
            $data['channelUserCode'] && $map['channelUserCode'] = $data['channelUserCode'];
            $data['channelUserName'] && $map['channelUserName'] = $data['channelUserName'];
            $data['agent'] && $map['agent']                     = $data['agent'];
            $data['game_id'] && $map['game_id']                 = $data['game_id'];
            $data['udid'] && $map['udid']                       = $data['udid'];
            $data['imei'] && $map["_string"] .= " AND (imei = '" . $data['imei'] . "' OR imei2 = '" . $data['imei'] . "' OR idfa = '" . $data['imei'] . "')";
            if ($data['mobile']) {
                $map['mobile']       = $data['mobile'];
                $map["mobileStatus"] = "0";
            }
            if (!$data['userCode'] && !$data['userName']) {
                //用户标识符和用户名都不存在时选用时间
                if ($data["startDate"] && $data["endDate"]) {
                    $map["createTime"] = array("BETWEEN", array(strtotime($data["startDate"]), strtotime($data["endDate"])));
                } elseif ($data["startDate"]) {
                    $map["createTime"] = array("EGT", strtotime($data["startDate"]));
                } elseif ($data["endDate"]) {
                    $map["createTime"] = array("ELT", strtotime($data["endDate"]));
                }
            }

            $agent   = getDataList("agent", "agent", C("DB_PREFIX_API"));
            $res     = D('Admin')->getBuiList("user", $map, $start, $pageSize, "lg_");
            $results = $res['count'];
            foreach ($res['list'] as $key => $val) {
                $res['list'][$key]['create']    = date('Y-m-d H:i:s', $val['createTime']);
                $res['list'][$key]['login']     = date('Y-m-d H:i:s', $val['lastLogin']);
                $res['list'][$key]['agentName'] = $agent[$val["agent"]]["agentName"];
                $res['list'][$key]['opt']       = createBtn('<a href="javascript:;" onclick="userInfo(\'' . $val['userCode'] . '\',this)">详情</a>');
                if (session("admin.role_id") == 1 || session("admin.role_id") == 23 || session("admin.role_id") == 31) {
                    $res['list'][$key]['opt'] .= createBtn('&nbsp;<a href="javascript:;" onclick="editPassword(\'' . $val['userCode'] . '\',this)">改密</a>');
                    $res['list'][$key]['opt'] .= createBtn('&nbsp;<a href="javascript:;" onclick="editName(\'' . $val['userCode'] . '\',this)">改名</a>');
                    $res['list'][$key]['opt'] .= createBtn('&nbsp;<a href="javascript:;" onclick="editMobile(\'' . $val['userCode'] . '\',this)">手机号绑定</a>');
                }
//                $res['list'][$key]['opt']      .= '&nbsp;<a href="javascript:;" onclick="roleInfo(\''.$val['userCode'].'\',this)">角色</a>';
                $rows[] = $res['list'][$key];
            }
            $arr = array('rows' => $rows, 'results' => $results);
            exit(json_encode($arr));
        } else {
            $this->display();
        }
    }

    /**
     * 用户详情
     */
    public function userInfo()
    {
        $userCode = I("userCode");
        $user     = D("Admin/Admin")->commonQuery("user", array("userCode" => $userCode), 0, 1, "*", "lg_");

        $agent             = D("Admin/Admin")->commonQuery("agent", array("agent" => $user["agent"]), 0, 1, '*', 'lg_');
        $user["agentName"] = $agent["agentName"];
        $this->assign("user", $user);

        if (IS_AJAX) {
            $respose = $this->fetch();
            $this->ajaxReturn(array('status' => 1, '_html' => $respose));
        } else {
            $this->display();
        }
    }

    /**
     * 修改用户密码
     */
    public function editPassword()
    {
        if (IS_POST) {
            $data = I();
            if (!$data['password'] || !$data["userCode"]) {
                IS_AJAX && $this->ajaxReturn(array(
                    'status' => 0,
                    'info'   => '参数有误',
                ));
                $this->error('参数有误');
            }
            $password = password_hash("Ls_" . md5($data["password"] . "Cy@mwonv2219jdwjcnsmou29&" . $data["password"]), PASSWORD_DEFAULT);
            if (D("Admin/User")->saveUser(array("password" => $password), $data["userCode"])) {
                IS_AJAX && $this->ajaxReturn(array(
                    'status' => 1,
                    'info'   => '修改成功',
                ));
                $this->success('修改成功');
            } else {
                IS_AJAX && $this->ajaxReturn(array(
                    'status' => 0,
                    'info'   => '修改失败',
                ));
                $this->error('修改失败');
            }
        } else {
            $userCode = I("userCode");
            $user     = D("Admin/Admin")->commonQuery("user", array("userCode" => $userCode), 0, 1, "*", "lg_");
            $this->assign("user", $user);

            if (IS_AJAX) {
                $respose = $this->fetch();
                $this->ajaxReturn(array('status' => 1, '_html' => $respose));
            } else {
                $this->display();
            }
        }
    }

    /**
     * 修改用户账号
     */
    public function editName()
    {
        if (IS_POST) {
            $data = I();
            if (!$data['userName'] || !$data["userCode"]) {
                IS_AJAX && $this->ajaxReturn(array(
                    'status' => 0,
                    'info'   => '参数有误',
                ));
                $this->error('参数有误');
            }
            //判断输入的用户名格式是否正确
            if (!preg_match("/^[A-Za-z0-9\-]+$/", $data["userName"]) || preg_match("/^[0-9]$/", substr($data["userName"], 0, 1)) || strlen($data["userName"]) > 20 || strlen($data["userName"]) < 6) {
                IS_AJAX && $this->ajaxReturn(array(
                    'status' => 0,
                    'info'   => '请输入正确的用户名称！6~20位字母数字组成，需字母开头！',
                ));
                $this->error('请输入正确的用户名称！6~20位字母数字组成，需字母开头！');
            }
            if (D("Admin/Admin")->commonQuery("user", array("userName" => $data["userName"]), 0, 1, "*", "lg_")) {
                IS_AJAX && $this->ajaxReturn(array(
                    'status' => 0,
                    'info'   => '用户账号重复',
                ));
                $this->error('用户账号重复');
            }
            if (D("Admin/User")->saveUser(array("userName" => $data["userName"]), $data["userCode"])) {
                IS_AJAX && $this->ajaxReturn(array(
                    'status' => 1,
                    'info'   => '修改成功',
                ));
                $this->success('修改成功');
            } else {
                IS_AJAX && $this->ajaxReturn(array(
                    'status' => 0,
                    'info'   => '修改失败',
                ));
                $this->error('修改失败');
            }
        } else {
            $userCode = I("userCode");
            $user     = D("Admin/Admin")->commonQuery("user", array("userCode" => $userCode), 0, 1, "*", "lg_");
            $this->assign("user", $user);

            if (IS_AJAX) {
                $respose = $this->fetch();
                $this->ajaxReturn(array('status' => 1, '_html' => $respose));
            } else {
                $this->display();
            }
        }
    }

    /**
     * 修改用户手机号和绑定状态
     */
    public function editMobile()
    {
        if (IS_POST) {
            $data = I();
            if (!$data['mobile'] || !$data["userCode"]) {
                IS_AJAX && $this->ajaxReturn(array(
                    'status' => 0,
                    'info'   => '参数有误',
                ));
                $this->error('参数有误');
            }

            if (!preg_match("/^0?(13|14|15|17|18)[0-9]{9}$/", $data["mobile"])) {
                IS_AJAX && $this->ajaxReturn(array(
                    'status' => 0,
                    'info'   => '请输入正确的手机号码！',
                ));
                $this->error('请输入正确的手机号码！');
            }

            if ($data['oldMobile'] != $data['mobile']) {
                if (D("Admin/Admin")->commonQuery("user", array("mobile" => $data["mobile"]), 0, 1, "*", "lg_")) {
                    IS_AJAX && $this->ajaxReturn(array(
                        'status' => 0,
                        'info'   => '手机号码重复',
                    ));
                    $this->error('手机号码重复');
                }
            }

            if (D("Admin/User")->saveUser(array("mobile" => $data["mobile"], "mobileStatus" => $data["mobileStatus"]), $data["userCode"])) {
                IS_AJAX && $this->ajaxReturn(array(
                    'status' => 1,
                    'info'   => '修改成功',
                ));
                $this->success('修改成功');
            } else {
                IS_AJAX && $this->ajaxReturn(array(
                    'status' => 0,
                    'info'   => '修改失败',
                ));
                $this->error('修改失败');
            }
        } else {
            $userCode = I("userCode");
            $user     = D("Admin/Admin")->commonQuery("user", array("userCode" => $userCode), 0, 1, "*", "lg_");
            $this->assign("user", $user);

            if (IS_AJAX) {
                $respose = $this->fetch();
                $this->ajaxReturn(array('status' => 1, '_html' => $respose));
            } else {
                $this->display();
            }
        }
    }

    /**
     * 用户详情修改
     */
    public function userInfoEdit()
    {
        $data = I();
        if (!in_array(session('admin.role_id'), array(1, 31))) {
            $this->ajaxReturn(array('status' => 0, 'info' => '无权修改'));
        }

        if (!$data['userCode']) {
            $this->ajaxReturn(array('status' => 0, 'info' => 'userCode缺省'));
        }

        $res = D('Admin')->commonExecute('user', array('userCode' => $data['userCode']), array('status' => $data['status']), C('DB_PREFIX_API'));
        if ($res) {
            $msg  = '';
            $time = time();

            switch ($data['status']) {
                case '0': //正常
                    $msg    = "用户名：{$data['userName']}已被" . session('admin.realname') . "解封。解封时间：" . date('Y-m-d H:i:s', $time);
                    $status = 0;
                    break;
                case '1': //关闭登陆
                    $msg    = "用户名：{$data['userName']}已被" . session('admin.realname') . "关闭登录。封禁时间：" . date('Y-m-d H:i:s', $time);
                    $status = 1;

                    break;
                case '2': //关闭充值
                    $msg    = "用户名：{$data['userName']}已被" . session('admin.realname') . "关闭充值。封禁时间：" . date('Y-m-d H:i:s', $time);
                    $status = 1;

                    break;
                case '3': //切充值
                    $msg    = "用户名：{$data['userName']}已被" . session('admin.realname') . "切充值。切充值时间：" . date('Y-m-d H:i:s', $time);
                    $status = 0;

                    break;

                default:
                    $msg    = "未知原因，错误时间：" . date('Y-m-d H:i:s', $time);
                    $status = 0;
                    break;
            }
            //写入日志
            $insert = array(
                'userCode'   => $data['userCode'],
                'userName'   => $data['userName'],
                'remark'     => $msg,
                'creater'    => session('admin.realname'),
                'status'     => $status,
                'createTime' => $time,
            );
            D('Admin')->commonAdd('ban_user', $insert);
            $this->ajaxReturn(array('status' => 1, 'info' => '修改成功'));
        } else {
            $this->ajaxReturn(array('status' => 0, 'info' => '修改失败'));
        }
    }

    /**
     * 角色列表
     */
    public function roleList()
    {
        if (IS_POST) {
            $data = I();
            //搜索条件
            $start    = $data['start'] ? $data['start'] : 0;
            $pageSize = $data['limit'] ? $data['limit'] : 30;
            $agentStr = implode("','", $this->agentArr);
            if ($data['accurate'] == 1) {
                $data['agent'] && $map['a.agent']       = $data['agent'];
                $data['roleName'] && $map['a.roleName'] = $data['roleName'];
            } else {
                $data['agent'] && $map['a.agent']       = array('like', '%' . $data['agent'] . '%');
                $data['roleName'] && $map['a.roleName'] = array('like', '%' . $data['roleName'] . '%');
            }

            if (!$data['agent']) {
                $map["_string"] = "a.agent IN ('" . $agentStr . "')";
            }

            $data['game_id'] && $map['a.game_id']       = $data['game_id'];
            $data['serverName'] && $map['a.serverName'] = $data['serverName'];
            $data['userCode'] && $map['a.userCode']     = $data['userCode'];
            $data['userName'] && $map['userName']       = $data['userName'];
            $data['roleId'] && $map['a.roleId']         = $data['roleId'];

            if (!$data['userCode'] && !$data['userName']) {
                //用户标识符和用户名都不存在时选用时间
                if ($data["startDate"] && $data["endDate"]) {
                    $d1       = date_create($data['startDate']);
                    $d2       = date_create($data['endDate']);
                    $diff     = date_diff($d1, $d2);
                    $accurate = $data['accurate'];
                    if ($diff->format("%a") > ($accurate ? 180 : 61)) {
                        if ($accurate) {
                            $msg = '精确查询日期跨度不能大于180天';
                        } else {
                            $msg = '模糊查询日期跨度不能大于60天';
                        }
                        exit(json_encode(array('hasError' => true, 'error' => $msg)));
                    }
                    $map["a.createTime"] = array("BETWEEN", array(strtotime($data["startDate"]), strtotime($data["endDate"])));
                } else {
                    exit(json_encode(array('hasError' => true, 'error' => '时间必须')));
                }
            }
            $row = D("Admin/Role")->getRole($map, $start, $pageSize);
//
            //            if(!$data['userCode'] && !$data['userName']){
            //                $createTime = $map["a.createTime"];
            //                unset($map['a.createTime']);
            //                $createTime && $map['createTime'] = $createTime;
            //            }

            $count = D("Admin/Role")->getRoleCount($map);
            foreach ($row as $key => $val) {
                $row[$key]['create'] = date('Y-m-d H:i:s', $val['createTime']);
                $row[$key]['update'] = date('Y-m-d H:i:s', $val['updateTime']);
            }
            $arr = array('rows' => $row, 'results' => $count);
            exit(json_encode($arr));
        } else {
            $search = I();
            $this->assign("search", $search);
            $this->display();
        }
    }

    /**
     * 角色信息
     */
    public function role()
    {
        if (IS_POST) {
            $data = I();
            //搜索条件
            $start                                = $data['start'] ? $data['start'] : 0;
            $pageSize                             = $data['limit'] ? $data['limit'] : 30;
            $data['userCode'] && $map['userCode'] = $data['userCode'];

            $row   = D("Admin/Role")->getRole($map, $start, $pageSize);
            $count = D("Admin/Role")->getRoleCount($map);
            foreach ($row as $key => $val) {
                $row[$key]['create'] = date('Y-m-d H:i:s', $val['createTime']);
                $row[$key]['update'] = date('Y-m-d H:i:s', $val['updateTime']);
            }
            $arr = array('rows' => $row, 'results' => $count);
            exit(json_encode($arr));
        } else {
            $search = I();
            $this->assign("search", $search);
            $this->display();
        }
    }

    /**
     * 新增设备留存统计
     */
    public function deviceRemain()
    {
        if (IS_POST) {
            $data = I();
            !$data['game_id'] && exit(json_encode(array('rows' => array(), 'results' => 0)));
            $agent_info   = $_REQUEST["agent"];
            $agent_p_info = $_REQUEST["agent_p"];
            $start        = $data["start"] ? $data["start"] : 0;
            $pageSize     = $data["limit"] ? $data["limit"] : 30;

            //处理搜索条件
            $agentArr = dealList($agent_info, $agent_p_info);

            $where = '1';
            if ($agentArr['agent']) {
                $where .= ' and agent in("' . implode('","', $agentArr['agent']) . '")';
                $map['agent'] = array('in', $agentArr['agent']);
            } else {
                if ($agentArr['pAgent']) {
                    $agent_p_arr = array_keys(getDataList('agent', 'id', C('DB_PREFIX_API'), array('agent' => array('IN', array_values($agentArr['pAgent'])))));

                    $agent_arr    = array_values($agentArr['pAgent']);
                    $agent_subarr = array_keys(getDataList('agent', 'agent', C('DB_PREFIX_API'), array('pid' => array('IN', $agent_p_arr))));
                    if ($agent_subarr) {
                        $agent_arr = array_merge($agent_arr, $agent_subarr);
                    }
                    $where .= ' and agent in("' . implode('","', $agent_arr) . '")';
                    $map['agent'] = array('in', $agent_arr);
                } else {
                    //权限控制
                    $where .= ' and agent in("' . implode('","', $this->agentArr) . '")';
                    $map['agent'] = array('in', $this->agentArr);
                }
            }

            if ($data['game_id']) {
                $where .= ' and gameId=' . $data['game_id'];
                $map['gameId'] = $data['game_id'];
            }

            if ($data['startDate'] && $data['endDate']) {
                $where .= ' and dayTime>="' . $data['startDate'] . '" and dayTime<"' . date('Y-m-d', strtotime($data['endDate'] . '+1 day')) . '"';
                $map['dayTime'] = array(array('egt', $data['startDate']), array('lt', date('Y-m-d', strtotime($data['endDate'] . '+1 day'))), 'and');
            }

            $res        = D('Admin')->getDeviceRemainData($map, $start, $pageSize, $where); //设备留存数据
            $results    = $res['count'];
            $game_list  = getDataList('game', 'id', C('DB_PREFIX_API'));
            $agent_list = getDataList('agent', 'agent', C('DB_PREFIX_API'));

            foreach ($res['list'] as $key => $val) {
                if ($data['lookType'] == 1) {
                    $res['list'][$key]['agent'] = $res['list'][$key]['agentName'] = $res['list'][$key]['dayTime'] = '-';
                }

                // $res['list'][$key]['agentName'] = $agent_list[$val['agent']]['agentName'];
                $res['list'][$key]['gameName'] = $game_list[$val['gameId']]['gameName'];
                //处理留存率
                if ($data['lookType'] == 2) {
                    $remainArr = $this->deviceRemainSet($res['list'][$key]);
                    $rows[]    = $remainArr;
                }
            }

            if ($data['lookType'] == 1) {
                $rows = $this->deviceRemainSetNew($res['list']);
            }

            //显示图表
            if ($data['chart'] == 1) {
                $deviceChart = $this->deviceChart($rows);
                if ($deviceChart === false) {
                    $this->ajaxReturn(array('status' => 0, 'info' => array('day' => array(), 'day1' => array(), 'day6' => array(), 'day29' => array())));
                }
                $this->ajaxReturn(array('status' => 1, 'info' => $deviceChart));
            }

            //数据汇总
            $pagesummary = $this->summarys($rows);
            if ($data['export'] == 1) {

                $col     = array('dayTime' => '注册日期', /*'agent'=>'渠道号',*/'gameName' => '游戏名称', 'newDevice' => '新增设备数');
                $day_arr = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 13, 14, 15, 29, 30, 59, 89);

                for ($i = 0; $i <= 120; $i++) {
                    if (in_array($i, $day_arr)) {
                        $col['day' . $i] = ($i + 1) . '日留存';
                    }
                }
                array_unshift($rows, $col);
                $pagesummary['dayTime'] = '汇总';
                // $pagesummary['agent'] = '-';
                $pagesummary['gameName'] = '-';
                array_push($rows, $pagesummary);
                export_to_csv($rows, '设备留存', $col);
                exit();
            }
            $arr = array('rows' => $rows ? $rows : array(), 'results' => $results, 'pageSummary' => $pagesummary);
            unset($rows, $remainArr);
            exit(json_encode($arr));
        } else {
            if (IS_AJAX) {
                $response = $this->fetch();
                $this->ajaxReturn(array('status' => 1, '_html' => $response));
            } else {
                $this->display();
            }
        }
    }

    //设备留存图表
    protected function deviceChart($data)
    {
        if (!$data) {
            return false;
        }

        $chart = array();

        $chart['day'] = array_column($data, 'dayTime');

        foreach ($data as $key => $value) {
            $chart['remain']['day1'][]  = $value['day1'] + 0;
            $chart['remain']['day6'][]  = $value['day6'] + 0;
            $chart['remain']['day29'][] = $value['day29'] + 0;
        }

        return $chart;
    }

    /**
     * 新增账户留存统计
     */
    public function userRemain()
    {
        if (IS_POST) {
            $data = I();
            !$data['game_id'] && exit(json_encode(array('rows' => array(), 'results' => 0)));
            $agent_info   = $_REQUEST["agent"];
            $agent_p_info = $_REQUEST["agent_p"];
            $start        = $data["start"] ? $data["start"] : 0;
            $pageSize     = $data["limit"] ? $data["limit"] : 30;

            //处理搜索条件
            $agentArr = dealList($agent_info, $agent_p_info);

            $where = '1';

            if ($agentArr['agent']) {
                $where .= ' and agent in("' . implode('","', $agentArr['agent']) . '")';
                $map['agent'] = array('in', $agentArr['agent']);
            } else {
                $agent_infos = $map_arr = array();

                if (!empty($agentArr['pAgent'])) {
                    $agent_p_arr        = array_keys(getDataList('agent', 'id', C('DB_PREFIX_API'), array('agent' => array('IN', array_values($agentArr['pAgent'])))));
                    $map_arr['_string'] = "id IN ('" . implode("','", $agent_p_arr) . "') OR pid IN ('" . implode("','", $agent_p_arr) . "')";
                }

                if ($data['game_id']) {
                    $map_arr['game_id'] = $data['game_id'];
                }

                if ($map_arr) {
                    $agent_infos = array_keys(getDataList('agent', 'agent', C('DB_PREFIX_API'), $map_arr));
                }

                $arr = $this->agentArr;
                if ($agent_infos) {
                    $arr = array_intersect($arr, $agent_infos);
                }

                sort($arr);
                if (count($arr) < 1) {
                    exit(json_encode(array('rows' => array(), 'results' => 0)));
                }

                $where .= ' and agent in("' . implode('","', $arr) . '")';
                $map['agent'] = array('in', $arr);
            }

            if ($data['game_id']) {
                $where .= ' and gameId=' . $data['game_id'];
                $map['gameId'] = $data['game_id'];
            }

            if ($data['startDate'] && $data['endDate']) {
                $where .= ' and dayTime>="' . $data['startDate'] . '" and dayTime<"' . date('Y-m-d', strtotime($data['endDate'] . '+1 day')) . '"';
                $map['dayTime'] = array(array('egt', $data['startDate']), array('lt', date('Y-m-d', strtotime($data['endDate'] . '+1 day'))), 'and');
            }

            if ($data['serverId']) {
                $where .= ' and serverId="' . $data['serverId'] . '"';
                $map['serverId'] = $data['serverId'];
            }

            $res        = D('Admin')->getUserRemainData($map, $start, $pageSize, $where); //用户留存数据
            $results    = $res['count'];
            $game_list  = getDataList('game', 'id', C('DB_PREFIX_API'));
            $agent_list = getDataList('agent', 'agent', C('DB_PREFIX_API'));

            foreach ($res['list'] as $key => $val) {
                if ($data['lookType'] == 1) {
                    $res['list'][$key]['agent'] = $res['list'][$key]['dayTime'] = '-';
                }
                $res['list'][$key]['agentName'] = $agent_list[$val['agent']]['agentName'];
                $res['list'][$key]['gameName']  = $game_list[$val['gameId']]['gameName'];
                //处理留存率
                if ($data['lookType'] == 2) {
                    $remainArr = $this->userRemainSet($res['list'][$key]);
                    $rows[]    = $remainArr;
                }
            }

            if ($data['lookType'] == 1) {
                $rows = $this->userRemainSetNew($res['list']);
            }

            //显示图表
            if ($data['chart'] == 1) {
                $deviceChart = $this->deviceChart($rows); //和设备图表一样
                if ($deviceChart === false) {
                    $this->ajaxReturn(array('status' => 0, 'info' => array('day' => array(), 'day1' => array(), 'day6' => array(), 'day29' => array())));
                }
                $this->ajaxReturn(array('status' => 1, 'info' => $deviceChart));
            }

            //数据汇总
            $pagesummary = $this->summarys($rows);
            if ($data['export'] == 1) {

                $col = array('dayTime' => '注册日期', 'gameName' => '游戏名称', 'newUser' => '新增账户数');
                $day_arr = array();
                for ($i = 0; $i <= 90; $i++) {
                    $day_arr[] = $i;
                }
                for ($i = 0; $i <= 120; $i++) {
                    if (in_array($i, $day_arr)) {
                        $col['day' . $i] = ($i + 1) . '日留存';
                    }
                }
                array_unshift($rows, $col);
                $pagesummary['dayTime'] = '汇总';
                // $pagesummary['agent'] = '-';
                $pagesummary['gameName'] = '-';
                array_push($rows, $pagesummary);
                export_to_csv($rows, '账户留存', $col);
                exit();
            }
            $arr = array('rows' => $rows ? $rows : array(), 'results' => $results, 'pageSummary' => $pagesummary);
            unset($rows, $remainArr);
            exit(json_encode($arr));
        } else {
            if (IS_AJAX) {
                $response = $this->fetch();
                $this->ajaxReturn(array('status' => 1, '_html' => $response));
            } else {
                $this->display();
            }
        }
    }

    /**
     * 活跃玩家概况
     */
    public function actUser()
    {
        if (IS_POST) {
            $data = I();
            !$data['game_id'] && exit(json_encode(array('rows' => array(), 'results' => 0)));
            $agent_info   = $_REQUEST["agent"];
            $agent_p_info = $_REQUEST["agent_p"];
            $start        = $data["start"] ? $data["start"] : 0;
            $pageSize     = $data["limit"] ? $data["limit"] : 30;

            //处理搜索条件
            $agentArr = dealList($agent_info, $agent_p_info);

            $where = '1';

            if ($agentArr['agent']) {
                $where .= ' AND agent IN("' . implode('","', $agentArr['agent']) . '")';
                $map['agent'] = array('in', $agentArr['agent']);
            } else {
                if ($agentArr['pAgent']) {
                    $agent_p_arr = array_keys(getDataList('agent', 'id', C('DB_PREFIX_API'), array('agent' => array('IN', array_values($agentArr['pAgent'])))));

                    $agent_arr    = array_values($agentArr['pAgent']);
                    $agent_subarr = array_keys(getDataList('agent', 'agent', C('DB_PREFIX_API'), array('pid' => array('IN', $agent_p_arr))));
                    if ($agent_subarr) {
                        $agent_arr = array_merge($agent_arr, $agent_subarr);
                    }
                    $where .= ' AND agent IN("' . implode('","', $agent_arr) . '")';
                    $map['agent'] = array('in', $agent_arr);
                } else {
                    //权限控制
                    $where .= ' AND agent IN("' . implode('","', $this->agentArr) . '")';
                    $map['agent'] = array('in', $this->agentArr);
                }
            }

            if ($data['game_id']) {
                $where .= ' and gameId=' . $data['game_id'];
                $map['gameId'] = $data['game_id'];
            }

            if ($data['startDate'] && $data['endDate']) {
                $where .= ' and dayTime>="' . $data['startDate'] . '" and dayTime<"' . date('Y-m-d', strtotime($data['endDate'] . '+1 day')) . '"';
                $map['dayTime'] = array(array('egt', $data['startDate']), array('lt', date('Y-m-d', strtotime($data['endDate'] . '+1 day'))), 'and');
            }

            if ($data['serverId']) {
                $where .= ' and serverId="' . $data['serverId'] . '"';
                $map['serverId'] = $data['serverId'];
            }

            $res = D('Admin')->getActUserData($map, $start, $pageSize, $where); //用户留存数据

            $results    = $res['count'];
            $game_list  = getDataList('game', 'id', C('DB_PREFIX_API'));
            $agent_list = getDataList('agent', 'agent', C('DB_PREFIX_API'));

            foreach ($res['list'] as $key => $val) {

                $data['lookType'] == 1 && $res['list'][$key]['agent']      = '-';
                $data['lookType'] == 1 && $res['list'][$key]['serverName'] = '-';
                //用户新增
                $res['list'][$key]['agentName']    = $data['lookType'] == 1 ? '-' : $agent_list[$val['agent']]['agentName'];
                $res['list'][$key]['gameName']     = $game_list[$val['gameId']]['gameName'];
                $res['list'][$key]['newDevice']    = isset($val['newDevice']) ? $val['newDevice'] : '-';
                $res['list'][$key]['userRate']     = isset($val['newDevice']) ? numFormat(($val['distinctReg'] / $val['newDevice']), true) : '-';
                $res['list'][$key]['allUserLogin'] = $val['newUserLogin'] + $val['oldUserLogin'];
                $res['list'][$key]['activeRate']   = numFormat((($val['newUserLogin'] + $val['oldUserLogin']) / $val['monthLogin']), true);
            }
            //数据汇总
            $pagesummary = $this->summarys($res['list']);

            if ($data['export'] == 1) {

                $col = array('dayTime' => '注册日期', 'gameName' => '游戏名称', 'allUserLogin' => 'DAU', 'newUserLogin' => '新用户日活跃数', 'oldUserLogin' => '老用户日活跃数', 'monthLogin' => 'MAU', 'activeRate' => 'DAU/MAU');

                array_unshift($res['list'], $col);
                /*$pagesummary['dayTime'] = '汇总';
                array_push($res['list'],$pagesummary);*/
                export_to_csv($res['list'], '活跃用户概况', $col);
                exit();
            }
            $arr = array('rows' => $res['list'] ? $res['list'] : array(), 'results' => $results);
            unset($rows, $remainArr);
            exit(json_encode($arr));
        } else {
            if (IS_AJAX) {
                $response = $this->fetch();
                $this->ajaxReturn(array('status' => 1, '_html' => $response));
            } else {
                $this->display();
            }
        }
    }

    /**
     * 运营数据概况
     */
    public function userRegRemain()
    {
        if (IS_POST) {
            $data         = I();
            $agent_info   = $_REQUEST["agent"];
            $agent_p_info = $_REQUEST["agent_p"];
            $start        = $data["start"] ? $data["start"] : 0;
            $pageSize     = $data["limit"] ? $data["limit"] : 30;

            //处理搜索条件
            $agentArr = dealList($agent_info, $agent_p_info);

            $where  = '1';
            $where2 = '1';

            if ($agentArr['agent']) {
                $where2         = $where .= ' AND a.agent IN("' . implode('","', $agentArr['agent']) . '")';
                $map['a.agent'] = array('IN', $agentArr['agent']);
            } else {
                if ($agentArr['pAgent']) {
                    $agent_p_arr  = array_keys(getDataList('agent', 'id', C('DB_PREFIX_API'), array('agent' => array('IN', array_values($agentArr['pAgent'])))));
                    $agent_arr    = array_values($agentArr['pAgent']);
                    $agent_subarr = array_keys(getDataList('agent', 'agent', C('DB_PREFIX_API'), array('pid' => array('IN', $agent_p_arr))));
                    if ($agent_subarr) {
                        $agent_arr = array_merge($agent_arr, $agent_subarr);
                    }
                    $where2         = $where .= ' AND a.agent IN("' . implode('","', $agent_arr) . '")';
                    $map['a.agent'] = array('IN', $agent_arr);
                } else {
                    //权限控制
                    if ($data['lookType'] == 1) {
                        $where2         = $where .= ' AND a.agent IN("' . implode('","', $this->agentArr) . '")';
                        $map['a.agent'] = array('IN', $this->agentArr);
                    } elseif ($data['lookType'] == 2) {
                        //只要融合的渠道号
                        $fusionAgent = array_keys(getDataList('agent', 'agent', C('DB_PREFIX_API'), array('channel_id' => array('GT', 1), 'agent' => array('LIKE', '%TAPTAP%'), '_logic' => 'OR')));

                        $agent = array_intersect($fusionAgent, $this->agentArr);
                        if (!$agent) {
                            $agent = array('-1');
                        }
                        $where2         = $where .= ' AND a.agent IN("' . implode('","', $agent) . '")';
                        $map['a.agent'] = array('IN', $agent);
                    }
                }
            }

            if ($data['game_id']) {
                $where2          = $where .= ' AND a.gameId=' . $data['game_id'];
                $map['a.gameId'] = $data['game_id'];
            }

            if ($data['startDate'] && $data['endDate']) {
                $where2           = $where .= ' AND a.dayTime>="' . $data['startDate'] . '" AND a.dayTime<"' . date('Y-m-d', strtotime($data['endDate'] . '+1 day')) . '"';
                $map['a.dayTime'] = array(array('egt', $data['startDate']), array('lt', date('Y-m-d', strtotime($data['endDate'] . '+1 day'))), 'AND');
            }

            if ($data['serverId']) {
                $serverId = $data['serverId'];
                if (is_array($serverId) && in_array("--全部----开服时间--", $serverId)) {
                    unset($serverId[array_search("--全部----开服时间--", $serverId)]);
                } elseif (is_string($serverId) && $serverId == "--全部----开服时间--") {
                    $serverId = "";
                } elseif (is_string($serverId) && !empty($serverId)) {
                    $serverId = explode(',', $serverId);
                    if (in_array("--全部----开服时间--", $serverId)) {
                        unset($serverId[array_search("--全部----开服时间--", $serverId)]);
                    }
                }
                if (!empty($serverId)) {
                    $where .= ' AND a.serverId IN("' . implode('","', $serverId) . '")';
                    $map['a.serverId'] = array('IN', $serverId);
                }
            }

            $res = D('Admin')->getRegChargeData($map, $start, $pageSize, $where, $where2); //用户留存数据

            //区服id为0的充值
            if ($map['a.gameId']) {
                $map['a.game_id'] = $map['a.gameId'];
                unset($map['a.gameId']);
            }
            if ($data['lookType'] == 1) {
                $payServer = M('sp_agent_server_pay_day a', C('DB_PREFIX'), 'CySlave')->field('SUM(allPay) AS allPay,SUM(allPayUser) AS allPayUser,SUM(newPay) AS newPay,SUM(newPayUser) AS newPayUser,game_id,dayTime')->where($map)->group('game_id,dayTime')->select();
            } elseif ($data['lookType'] == 2) {
                //融合数据
                $payServer = M('sp_agent_server_pay_day a', C('DB_PREFIX'), 'CySlave')->field('SUM(allPay) AS allPay,SUM(allPayUser) AS allPayUser,SUM(newPay) AS newPay,SUM(newPayUser) AS newPayUser,agent,game_id,dayTime')->where($map)->group('agent,dayTime')->select();
            }

            $game_list  = getDataList('game', 'id', C('DB_PREFIX_API'));
            $agent_list = getDataList('agent', 'agent', C('DB_PREFIX_API'));

            if ((in_array(session('admin.role_id'), array(3)) || session('admin.uid') == 85) && (!$map["a.game_id"] || $map["a.game_id"] == 104)) {
                if (strtotime($data['endDate']) > strtotime("2017-11-23")) {
                    //查询出刀剑特殊游戏的数据
                    $this->specialData = D('Admin')->getSpecialData($data['startDate'], $data['endDate']);

                    $game_start_time = (strtotime($data['startDate']) > strtotime("2017-11-23") ? $data['startDate'] : "2017-11-23");
                    while ($game_start_time <= $data["endDate"] && $game_start_time < date("Y-m-d")) {
                        $game_start_arr = array(
                            "dayTime"    => $game_start_time,
                            "agent"      => "cqxyAND",
                            "gameId"     => 104,
                            "serverId"   => 1,
                            "serverName" => 1,
                        );
                        array_push($res["list"], $game_start_arr);

                        $game_pay_arr = array(
                            "dayTime" => $game_start_time,
                            "game_id" => 104,
                        );
                        array_push($payServer, $game_pay_arr);

                        $game_start_time = date("Y-m-d", strtotime($game_start_time . " +1 day"));

                        $res['count']++;
                    }

                    $foreach_arr = array();
                    foreach ($res["list"] as $v) {
                        $foreach_arr[strtotime($v["dayTime"]) . $v["gameId"]] = $v;
                    }
                    ksort($foreach_arr);
                    $res["list"] = array_values($foreach_arr);
                }
            }

            $results = $res['count'];
            foreach ($res['list'] as $key => $val) {
                if (in_array(session('admin.role_id'), array(3)) || session('admin.uid') == 85) {
                    $this->ortherInfo($res['list'][$key], $val);
                }

                if ($payServer && $data['lookType'] == 1) {
                    foreach ($payServer as $k => $v) {
                        if ($val['gameId'] == $v['game_id'] && $val['dayTime'] == $v['dayTime']) {
                            if (in_array(session('admin.role_id'), array(3)) || session('admin.uid') == 85) {
                                $ortherPay = $this->ortherPay($val);
                            }

                            if (!$ortherPay) {
                                $val['allPay'] += floatval($v['allPay']);
                                $val['allPayUser'] += $v['allPayUser'];
                                $val['newPay'] += floatval($v['newPay']);
                                $val['newPayUser'] += $v['newPayUser'];
                            }

                        }
                    }
                } elseif ($payServer && $data['lookType'] == 2) {
                    //融合数据
                    foreach ($payServer as $k => $v) {
                        if ($val['agent'] == $v['agent'] && $val['dayTime'] == $v['dayTime']) {
                            $val['allPay'] += floatval($v['allPay']);
                            $val['allPayUser'] += $v['allPayUser'];
                            $val['newPay'] += floatval($v['newPay']);
                            $val['newPayUser'] += $v['newPayUser'];
                        }
                    }
                }

                if ($data['serverId']) {
                    $val['newDevice'] = '-';
                }

                if ($data['serverId']) {
                    $val['disUdid'] = '-';
                }

                $res['list'][$key]['allPay']     = floatval($val['allPay']);
                $res['list'][$key]['allPayUser'] = $val['allPayUser'];
                $res['list'][$key]['newPay']     = floatval($val['newPay']);
                $res['list'][$key]['newPayUser'] = $val['newPayUser'];

                $data['lookType'] == 1 && $res['list'][$key]['agent']      = '-';
                $data['lookType'] == 1 && $res['list'][$key]['serverName'] = '-';
                $res['list'][$key]['newPay']                               = floatval($val['newPay']);
                //用户新增
                $res['list'][$key]['agentName'] = $data['lookType'] == 1 ? '-' : $agent_list[$val['agent']]['agentName'];
                $res['list'][$key]['gameName']  = $data['lookType'] == 2 ? $agent_list[$val['agent']]['agentName'] : $game_list[$val['gameId']]['gameName'];
                $res['list'][$key]['newDevice'] = isset($val['newDevice']) ? $val['newDevice'] : '-';
                $res['list'][$key]['disUdid']   = isset($val['disUdid']) ? $val['disUdid'] : '-';
                if (strtotime($data['startDate']) >= strtotime('2017-10-08') || strtotime($val['dayTime']) >= strtotime('2017-10-08')) {
                    $res['list'][$key]['userRate'] = isset($val['newDevice']) ? numFormat(($val['disUdid'] / $val['newDevice']), true) : '-';
                } else {
                    $res['list'][$key]['disUdid']  = $val['distinctReg'];
                    $res['list'][$key]['userRate'] = isset($val['newDevice']) ? numFormat(($val['distinctReg'] / $val['newDevice']), true) : '-';
                }
                $res['list'][$key]['allUserLogin'] = $val['newUserLogin'] + $val['oldUserLogin'];
                $res['list'][$key]['activeRate']   = numFormat((($val['newUserLogin'] + $val['oldUserLogin']) / $val['monthLogin']), true);
                //充值概况
                $res['list'][$key]['payRate'] = numFormat(($val['allPayUser'] / ($val['newUserLogin'] + $val['oldUserLogin'])), true);
                $res['list'][$key]['ARPU']    = (false === $num = sprintf("%.2f", $val['allPay'] / ($val['newUserLogin'] + $val['oldUserLogin']))) ? 0 : $num;
                $res['list'][$key]['ARPPU']   = (false === $num = sprintf("%.2f", $val['allPay'] / $val['allPayUser'])) ? 0 : $num;

                //新增充值
                $res['list'][$key]['newPayRate'] = numFormat(($val['newPayUser'] / $val['newUser']), true);
                $res['list'][$key]['newARPU']    = (false === $num = sprintf("%.2f", $val['newPay'] / $val['newUser'])) ? 0 : $num;
                $res['list'][$key]['newARPPU']   = (false === $num = sprintf("%.2f", $val['newPay'] / $val['newPayUser'])) ? 0 : $num;
                //处理留存率
                $remainArr = $this->remain($res['list'][$key]);
                $rows[]    = $remainArr;
            }
            //数据汇总
            $pagesummary = $this->summarys($rows);
            if ($data['export'] == 1) {
                $col     = array('dayTime' => '注册日期', 'gameName' => '游戏名称', 'newDevice' => '新增设备数', 'disUdid' => '唯一注册数', 'newUser' => '新增用户数', 'userRate' => '用户转化率', 'allUserLogin' => '总日活跃数', 'newUserLogin' => '新用户日活跃数', 'oldUserLogin' => '老用户日活跃数', 'monthLogin' => 'MAU', 'activeRate' => 'DAU/MAU', 'allPay' => '充值总额', 'allPayUser' => '充值总账号数', 'payRate' => '付费率', 'ARPU' => 'ARPU', 'ARPPU' => 'ARPPU', 'newPay' => '新用户充值总额', 'newPayUser' => '新用户充值总账号数', 'newPayRate' => '新增付费率', 'newARPU' => '新增ARPU', 'newARPPU' => '新增ARPPU');
                $day_arr = array(1, 2, 3, 4, 5, 6, 13, 29);

                for ($i = 0; $i <= 120; $i++) {
                    if (in_array($i, $day_arr)) {
                        $col['day' . $i] = ($i + 1) . '日留存';
                    }
                }
                array_unshift($rows, $col);
                $pagesummary['dayTime']    = '汇总';
                $pagesummary['agent']      = '-';
                $pagesummary['gameName']   = '-';
                $pagesummary['serverName'] = '-';
                array_push($rows, $pagesummary);
                export_to_csv($rows, '运营数据概况', $col);
                exit();
            }
            $arr = array('rows' => $rows ? $rows : array(), 'results' => $results, 'pageSummary' => $pagesummary);
            unset($rows, $remainArr);
            exit(json_encode($arr));
        } else {
            if (IS_AJAX) {
                $response = $this->fetch();
                $this->ajaxReturn(array('status' => 1, '_html' => $response));
            } else {
                $this->display();
            }
        }
    }

    //用户注册留存数据汇总
    private function summarys($data)
    {

        $sum      = array();
        $data_num = count($data);
        $day_arr  = array();
        for ($i = 0; $i <= 90; $i++) {
            $day_arr[] = 'day' . $i;
        }
        $day_num = array();
        //开始统计
        foreach ($data as $k => $val) {
            $sum['newDevice'] += $val['newDevice'];
            $sum['allPay'] += $val['allPay'];
            $sum['distinctReg'] += $val['distinctReg'];
            $sum['newUser'] += $val['newUser'];
            $sum['newRole'] += $val['newRole'];
            $sum['payRate'] += $val['payRate'];
            $sum['ARPU'] += $val['ARPU'];
            $sum['ARPPU'] += $val['ARPPU'];
            $sum['newPayRate'] += $val['newPayRate'];
            $sum['newARPU'] += $val['newARPU'];
            $sum['newARPPU'] += $val['newARPPU'];
            $sum['newPay']       = '-';
            $sum['allPayUser']   = '-';
            $sum['newPayUser']   = '-';
            $sum['newUserLogin'] = '-';
            $sum['oldUserLogin'] = '-';
            $sum['monthLogin']   = '-';
            foreach ($val as $key => $value) {
                if (in_array($key, $day_arr)) {
                    $sum[$key] += $value;
                    $value > 0 && $day_num[$key]++;
                }
            }
        }
        //开始计算

        //用户新增
        $sum['userRate']     = '-';
        $sum['allUserLogin'] = '-';
        $sum['activeRate']   = '-';

        //充值概况平均值
        $sum['payRate'] = sprintf("%.2f", $sum['payRate'] / $data_num) . '%';
        $sum['ARPU']    = sprintf("%.2f", $sum['ARPU'] / $data_num);
        $sum['ARPPU']   = sprintf("%.2f", $sum['ARPPU'] / $data_num);

        //新增充值平均值
        $sum['newPayRate'] = sprintf("%.2f", $sum['newPayRate'] / $data_num) . '%';
        $sum['newARPU']    = sprintf("%.2f", $sum['newARPU'] / $data_num);
        $sum['newARPPU']   = sprintf("%.2f", $sum['newARPPU'] / $data_num);

        //留存平均值
        foreach ($sum as $k => $v) {
            if (in_array($k, $day_arr)) {
                $sum[$k] = sprintf("%.2f", $v / $day_num[$k]) . '%';
            }
        }
        return $sum;
    }

    //付费用户留存数据汇总
    private function firstPaysummarys($data)
    {
        $sum      = array();
        $day_arr  = array('day1', 'day2', 'day6', 'day13', 'day29');
        $day_num = array();
        //开始统计
        foreach ($data as $k => $val) {
            $sum['allFirstPay'] += $val['allFirstPay'];
            $sum['newFirstPay'] += $val['newFirstPay'];
            $sum['oldFirstPay'] += $val['oldFirstPay'];
            foreach ($val as $key => $value) {
                if (in_array($key, $day_arr)) {
                    $sum[$key] += $value;
                    $value > 0 && $day_num[$key]++;
                }
            }
        }
        //开始计算

        //留存平均值
        foreach ($sum as $k => $v) {
            if (in_array($k, $day_arr)) {
                $sum[$k] = sprintf("%.2f", $v / $day_num[$k]) . '%';
            }
        }
        return $sum;
    }

    //用户注册留存数据汇总
    private function summarys_new($data, $encode = 1)
    {

        $sum      = array();
        $data_num = count($data);
        $day_arr  = array('day0', 'day1', 'day2', 'day3', 'day4', 'day5', 'day6', 'day7', 'day8', 'day9', 'day10', 'day13', 'day29', 'day59', 'day89');
        $day_num  = array();
        //开始统计
        foreach ($data as $k => $val) {
            $sum['newDevice'] += $val['newDevice'];
            $sum['allPay'] += $val['allPay'];
            $sum['distinctReg'] += $val['distinctReg'];
            $sum['newUser'] += $val['newUser'];
            // $sum['payRate']      += $val['payRate'];
            $sum['ARPU'] += $val['ARPU'];
            $sum['ARPPU'] += $val['ARPPU'];
            $sum['newPayRate'] += $val['newPayRate'];
            $sum['newARPU'] += $val['newARPU'];
            $sum['newARPPU'] += $val['newARPPU'];
            $sum['newPay'] += $val['newPay'];
            $sum['allPayUser'] += $val['allPayUser'];
            $sum['newPayUser'] += $val['newPayUser'];
            $sum['newUserLogin'] += $val['newUserLogin'];
            $sum['oldUserLogin'] += $val['oldUserLogin'];
            $sum['monthLogin'] += $val['monthLogin'];
            $sum['allUserLogin'] += $val['allUserLogin'];
            foreach ($val as $key => $value) {
                if (in_array($key, $day_arr)) {
                    $sum[$key] += $value;
                    $value > 0 && $day_num[$key]++;
                }
            }
        }
        //开始计算

        //用户新增
        $sum['userRate']   = '-';
        $sum['activeRate'] = '-';

        //充值概况平均值
        // $sum['payRate'] = sprintf("%.2f",$sum['payRate']/$data_num).'%';
        $sum['ARPU']  = sprintf("%.2f", $sum['ARPU'] / $data_num);
        $sum['ARPPU'] = sprintf("%.2f", $sum['ARPPU'] / $data_num);

        //新增充值平均值
        $sum['newPayRate'] = sprintf("%.2f", $sum['newPayRate'] / $data_num) . '%';
        $sum['newARPU']    = sprintf("%.2f", $sum['newARPU'] / $data_num);
        $sum['newARPPU']   = sprintf("%.2f", $sum['newARPPU'] / $data_num);

        //留存平均值
        foreach ($sum as $k => $v) {
            if (in_array($k, $day_arr)) {
                if ($encode == 0) {
                    $sum[$k] = sprintf("%.2f", $v / $day_num[$k]) . '%';
                } else {
                    $sum[$k] = sprintf("%.2f", $v / $day_num[$k]);
                }
            }
        }
        return $sum;
    }

    //付费衰减数据汇总
    private function payRemainSummarys($data)
    {

        $sum      = array();
        $data_num = count($data);
        $day_arr  = array('day0', 'day1', 'day3', 'day7', 'day15', 'day30', 'day60');
        $day_num  = array();
        //开始统计
        foreach ($data as $k => $val) {
            $sum['newUser'] += $val['newUser'];
            $sum['newPay'] += $val['newPay'];
            foreach ($val as $key => $value) {
                if (in_array($key, $day_arr)) {
                    $sum[$key] += $value;
                    $value > 0 && $day_num[$key]++;
                }
            }
        }

        //留存平均值
        foreach ($sum as $k => $v) {
            if (in_array($k, $day_arr)) {
                $sum[$k] = sprintf("%.2f", $v / $day_num[$k]) . '%';
            }
        }
        return $sum;
    }

    //处理留存数据
    private function remain($info)
    {
        if (empty($info)) {
            return false;
        }

        for ($i = 0; $i <= 120; $i++) {
            if (isset($info['day' . $i])) {
                $info['day' . $i] = numFormat($info['day' . $i] / $info['newUser'], true);
            }
        }
        return $info;
    }

    //处理新增用户留存数据 $encode=0输出%，1输出值
    private function userRemainSet($info, $encode = 0,$field = 'newUser')
    {

        if (empty($info)) {
            return false;
        }

        for ($i = 0; $i <= 120; $i++) {
            if (isset($info['day' . $i])) {
                if ($encode == 0) {
                    $info['day' . $i] = numFormat($info['day' . $i] / $info[$field], true);
                } elseif ($encode == 1) {
                    $info['day' . $i] = numFormat($info['day' . $i] / $info[$field], true) + 0;
                }
            }
        }
        return $info;
    }

    //处理渠道新增用户留存数据 $encode=0输出%，1输出值
    private function userRemainSetAgent($info, $encode = 0)
    {

        if (empty($info)) {
            return false;
        }

        for ($i = 0; $i <= 120; $i++) {
            if (isset($info['day' . $i])) {
                if ($encode == 0) {
                    $info['day' . $i] = numFormat($info['day' . $i] / $info['newUser' . $i], true);
                } elseif ($encode == 1) {
                    $info['day' . $i] = numFormat($info['day' . $i] / $info['newUser' . $i], true) + 0;
                }
            }
        }
        return $info;
    }

    //处理新增用户留存数据 $encode=0输出%，1输出值
    private function userRemainSetNew($info, $encode = 0)
    {

        if (empty($info)) {
            return false;
        }

        $row = $newUser = $remain = $allNewUser = $newInfo = array();

        foreach ($info as $key => $value) {
            for ($i = 0; $i <= 120; $i++) {
                if (isset($value['day' . $i]) && $value['day' . $i] > 0) {
                    $newInfo[$value['gameId']]['agentName'] = $value['agentName'];
                    $newInfo[$value['gameId']]['gameName']  = $value['gameName'];

                    //计算汇总的新增人数
                    $newUser[$value['gameId']]['day' . $i] += $value['newUser'];
                    //计算汇总的留存人数
                    $remain[$value['gameId']]['day' . $i] += $value['day' . $i];
                }
            }
        }
        //新增用户汇总
        foreach ($info as $k => $v) {
            $allNewUser[$v['gameId']] += $v['newUser'];
        }

        //组合数据
        foreach ($newUser as $k => $v) {

            $dayKey = array_keys($v);

            for ($i = 0; $i <= 120; $i++) {
                if (isset($v['day' . $i])) {
                    if ($encode == 0) {
                        $newInfo[$k]['day' . $i] = numFormat($remain[$k]['day' . $i] / $v['day' . $i], true);
                    } elseif ($encode == 1) {
                        $newInfo[$k]['day' . $i] = numFormat($remain[$k]['day' . $i] / $v['day' . $i], true) + 0;
                    }
                } else {
                    $newInfo[$k]['day' . $i] = '0.00%';
                }
            }
            $newInfo[$k]['newUser'] += $allNewUser[$k];

        }
        sort($newInfo);
        unset($info);
        return $newInfo;
    }

    //处理新增设备留存数据 $encode=0输出%，1输出值
    private function deviceRemainSetNew($info, $encode = 0)
    {

        if (empty($info)) {
            return false;
        }

        $row = $newDevice = $remain = $allNewDevice = $newInfo = array();

        foreach ($info as $key => $value) {
            for ($i = 0; $i <= 120; $i++) {
                if (isset($value['day' . $i]) && $value['day' . $i] > 0) {
                    $newInfo[$value['gameId']]['agentName'] = $value['agentName'];
                    $newInfo[$value['gameId']]['gameName']  = $value['gameName'];

                    //计算汇总的新增人数
                    $newDevice[$value['gameId']]['day' . $i] += $value['newDevice'];
                    //计算汇总的留存人数
                    $remain[$value['gameId']]['day' . $i] += $value['day' . $i];
                }
            }
        }
        //新增用户汇总
        foreach ($info as $k => $v) {
            $allNewDevice[$v['gameId']] += $v['newDevice'];
        }

        //组合数据
        foreach ($newDevice as $k => $v) {

            $dayKey = array_keys($v);

            for ($i = 0; $i <= 120; $i++) {
                if (isset($v['day' . $i])) {
                    if ($encode == 0) {
                        $newInfo[$k]['day' . $i] = numFormat($remain[$k]['day' . $i] / $v['day' . $i], true);
                    } elseif ($encode == 1) {
                        $newInfo[$k]['day' . $i] = numFormat($remain[$k]['day' . $i] / $v['day' . $i], true) + 0;
                    }
                } else {
                    $newInfo[$k]['day' . $i] = '0.00%';
                }
            }
            $newInfo[$k]['newDevice'] += $allNewDevice[$k];

        }
        sort($newInfo);
        unset($info);
        return $newInfo;
    }

    //处理设备留存数据
    private function deviceRemainSet($info)
    {

        if (empty($info)) {
            return false;
        }

        for ($i = 0; $i <= 120; $i++) {
            if (isset($info['day' . $i])) {
                $info['day' . $i] = numFormat($info['day' . $i] / $info['newDevice'], true);
            }
        }
        return $info;
    }

    //处理付费衰减数据
    private function payRemainSet($info, $startDate)
    {

        if (empty($info)) {
            return false;
        }

        for ($i = 0; $i <= 120; $i++) {
            if (isset($info['day' . $i])) {
                $i != 0 && $info['day' . $i] += $info['day' . ($i - 1)];
            }
        }
        if ($info['dayTime'] == '汇总') {
            $dayTime = strtotime($startDate);
        } else {
            $dayTime = strtotime($info['dayTime']);
        }
        $todayTime      = strtotime(date('Y-m-d 00:00:00'));
        $days           = ceil(($todayTime - $dayTime) / 3600 / 24) + 1;
        $info['newPay'] = $info['day0'];

        if ($days > 2) {
            $info['day1'] = numFormat(($info['day1'] - $info['day0']) / $info['day0'], true);
        } else {
            $info['day1'] = '';
        }
        if ($days > 6) {
            $info['day3'] = numFormat(($info['day5'] - $info['day2']) / $info['day2'], true);
        } else {
            $info['day3'] = '';
        }
        if ($days > 14) {
            $info['day7'] = numFormat(($info['day13'] - $info['day6']) / $info['day6'], true);
        } else {
            $info['day7'] = '';
        }
        if ($days > 29) {
            $info['day15'] = numFormat(($info['day29'] - $info['day14']) / $info['day14'], true);
        } else {
            $info['day15'] = '';
        }
        if ($days > 59) {
            $info['day30'] = numFormat(($info['day59'] - $info['day29']) / $info['day29'], true);
        } else {
            $info['day30'] = '';
        }
        if ($days > 119) {
            $info['day60'] = numFormat(($info['day119'] - $info['day59']) / $info['day59'], true);
        } else {
            $info['day60'] = '';
        }

        return $info;
    }

    /**
     * 用户充值ltv统计
     */
    public function payLtv()
    {
        if (IS_POST) {
            $data = I();
            !$data['game_id'] && exit(json_encode(array('rows' => array(), 'results' => 0)));
            $agent_info   = $_REQUEST["agent"];
            $agent_p_info = $_REQUEST["agent_p"];
            $start        = $data["start"] ? $data["start"] : 0;
            $pageSize     = $data["limit"] ? $data["limit"] : 30;

            //处理搜索条件
            $agentArr = dealList($agent_info, $agent_p_info);

            $where = '1';
            // if ($agentArr['agent']) {
            //     $where .= ' and agent in("'.implode('","',$agentArr['agent']).'")';
            //     $map['agent'] = array('in',$agentArr['agent']);
            // } elseif ($agentArr['pAgent']) {
            //     $agent_p_arr       = array_keys(getDataList('agent','id',C('DB_PREFIX_API'),array('agent'=>array('IN',array_values($agentArr['pAgent'])))));
            //     $agent_arr         = array_values($agentArr['pAgent']);
            //     $agent_subarr      = array_keys(getDataList('agent','agent',C('DB_PREFIX_API'),array('pid'=>array('IN',$agent_p_arr))));
            //     if($agent_subarr){
            //             $agent_arr = array_merge($agent_arr,$agent_subarr);
            //     }
            //     $where .= ' and agent in("'.implode('","',$agent_arr).'")';
            //     $map['agent'] = array('in',$agent_arr);
            // } elseif($data['advteruser_id']) {
            //     $_map['advteruser_id'] = $data['advteruser_id'];
            //     $data['game_id'] && $_map['game_id'] = $data['game_id'];
            //     $agent_info = array_keys(getDataList('agent','agent',C('DB_PREFIX_API'),$_map));
            //     $data['agent'] = $agent_info;
            //     $where .= ' and agent in("'.implode('","',$data['agent']).'")';
            //     var_dump($agent_info);DIE;
            //     $map['agent'] = array('in',$data['agent']);
            // } else {
            //     //权限控制
            //     $where .= ' and agent in("'.implode('","', $this->agentArr).'")';
            //     $map['agent'] = array('in',$this->agentArr);
            // }

            if ($agentArr['agent']) {
                $where .= ' and agent in("' . implode('","', $agentArr['agent']) . '")';
                $map['agent'] = array('in', $agentArr['agent']);
            } else {
                $agent_infos = $map_arr = array();

                if (!empty($agentArr['pAgent'])) {
                    $agent_p_arr = array_keys(getDataList('agent', 'id', C('DB_PREFIX_API'), array('agent' => array('IN', array_values($agentArr['pAgent'])))));

                    $map_arr['_string'] = "id IN ('" . implode("','", $agent_p_arr) . "') OR pid IN ('" . implode("','", $agent_p_arr) . "')";
                }

                if ($data['advteruser_id']) {
                    $map_arr['advteruser_id'] = $data['advteruser_id'];
                }

                if ($map_arr) {
                    $agent_infos = array_keys(getDataList('agent', 'agent', C('DB_PREFIX_API'), $map_arr));
                }

                $arr = $this->agentArr;
                if ($agent_infos) {
                    $arr = array_intersect($arr, $agent_infos);
                } elseif ($map_arr && !$agent_infos) {
                    exit(json_encode(array('rows' => array(), 'results' => 0)));
                }

                sort($arr);
                if (count($arr) < 1) {
                    $arr[] = '-1';
                }

                $where .= ' and agent in("' . implode('","', $arr) . '")';
                $map['agent'] = array('in', $arr);
            }

            if ($data['game_id']) {
                $where .= ' and gameId=' . $data['game_id'];
                $map['gameId'] = $data['game_id'];
            }

            if ($data['startDate'] && $data['endDate']) {
                $where .= ' and dayTime>="' . $data['startDate'] . '" and dayTime<"' . date('Y-m-d', strtotime($data['endDate'] . '+1 day')) . '"';
                $map['dayTime'] = array(array('egt', $data['startDate']), array('lt', date('Y-m-d', strtotime($data['endDate'] . '+1 day'))), 'and');
            }

            if ($data['serverId']) {
                $where .= ' and serverId="' . $data['serverId'] . '"';
                $map['serverId'] = $data['serverId'];
            }

            $res        = D('Admin')->getPayLtvData($map, $start, $pageSize, $where); //LTV留存数据
            $res_dau    = D('Admin')->getDauData($map, $start, $pageSize, $where); //Dau数据
            $results    = $res['count'];
            $game_list  = getDataList('game', 'id', C('DB_PREFIX_API'));
            $agent_list = getDataList('agent', 'agent', C('DB_PREFIX_API'));
            foreach ($res['list'] as $key => $val) {
                $data['lookType'] == 1 && $res['list'][$key]['agent']    = '-';
                $data['lookType'] == 1 && $res['list'][$key]['serverId'] = '-';
                $res['list'][$key]['gameName']                           = $game_list[$val['gameId']]['gameName'];
                $res['list'][$key]['agentName']                          = $agent_list[$val['agent']]['agentName'];

            }

            if ($data['lookType'] == 3 || $data['lookType'] == 4) {
//                $day_arr = array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,29,59,89);
                $day_arr = array();
                for ($i = 0; $i <= 90; $i++) {
                    $day_arr[] = $i;
                }
                foreach ($res_dau['list'] as $key => $value) {
                    for ($i = 0; $i < 120; $i++) {
                        if (in_array($i, $day_arr)) {
                            if (isset($res['list'][$key])) {
                                $res['list'][$key]['dau' . $i] = $value['day' . $i];
                            }
                        }
                    }
                }
            }

            $remainArr = $this->ltvRemain($res['list'], $data['lookType']);
            $rows      = $remainArr;
            //显示图表
            if ($data['chart'] == 1) {
                $ltvChart = $this->ltvChart($rows);
                if ($ltvChart === false) {
                    $this->ajaxReturn(array('status' => 0, 'info' => array('dayTime' => array(), 'key' => array(), 'data' => array())));
                }
                $this->ajaxReturn(array('status' => 1, 'info' => $ltvChart));
            }
            //数据汇总
            $pageSummary = $this->ltvSummarys($rows, $data['lookType']);

//            $day_arr = array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,29,59,89);
            $day_arr = array();
            for ($i = 0; $i <= 90; $i++) {
                $day_arr[] = $i;
            }
            if ($data['lookType'] == 3) {
                foreach ($rows as $key => &$value) {
                    for ($i = 0; $i <= 120; $i++) {
                        if (in_array($i, $day_arr)) {
                            if ($value['ltv' . $i] != 0) {
                                $value['ltv' . $i] = numFormat($value['ltv' . $i] / 100, true);
                            }
                        }
                    }
                }
            }
            if ($data['export'] == 1) {
                if ($data['lookType'] == 1) {
                    $title = 'Ltv';
                } elseif ($data['lookType'] == 2) {
                    $title = '充值金额';
                } elseif ($data['lookType'] == 3) {
                    $title = '付费率';
                } else {
                    $title = 'ARPU';
                }

                $col = array('dayTime' => '注册日期', 'gameName' => '游戏名称', 'newUser' => '新增用户数', 'allmoney' => '充值金额');

                for ($i = 0; $i <= 120; $i++) {
                    if (in_array($i, $day_arr)) {
                        $col['ltv' . $i] = ($i + 1) . '日' . $title;
                    }
                }
                array_unshift($rows, $col);
                $pageSummary['dayTime'] = '汇总';
                array_push($rows, $pageSummary);
                export_to_csv($rows, $title . '数据统计', $col);
                exit();
            }
            $arr = array('rows' => $rows ? $rows : array(), 'results' => $results, 'pageSummary' => $pageSummary);
            unset($rows, $remainArr);
            exit(json_encode($arr));
        } else {
            if (IS_AJAX) {
                $response = $this->fetch();
                $this->ajaxReturn(array('status' => 1, '_html' => $response));
            } else {
                $this->display();
            }
        }
    }

    /**
     * 用户充值ltv（包）统计
     */
    public function payLtvAgent()
    {
        if (IS_POST) {
            $data = I();
            !$data['game_id'] && exit(json_encode(array('rows' => array(), 'results' => 0)));
            $agent_info   = $_REQUEST["agent"];
            $agent_p_info = $_REQUEST["agent_p"];
            $start        = $data["start"] ? $data["start"] : 0;
            $pageSize     = $data["limit"] ? $data["limit"] : 30;

            //处理搜索条件
            $agentArr = dealList($agent_info, $agent_p_info);

            $where = '1';
            if ($agentArr['agent']) {
                $where .= ' and agent in("' . implode('","', $agentArr['agent']) . '")';
                $map['agent'] = array('in', $agentArr['agent']);
            } else {
                $agent_infos = $map_arr = array();

                if (!empty($agentArr['pAgent'])) {
                    $agent_p_arr = array_keys(getDataList('agent', 'id', C('DB_PREFIX_API'), array('agent' => array('IN', array_values($agentArr['pAgent'])))));

                    $map_arr['_string'] = "id IN ('" . implode("','", $agent_p_arr) . "') OR pid IN ('" . implode("','", $agent_p_arr) . "')";
                }

                if ($data['advteruser_id']) {
                    $map_arr['advteruser_id'] = array('IN', $data['advteruser_id']);
                }

                if ($map_arr) {
                    $agent_infos = array_keys(getDataList('agent', 'agent', C('DB_PREFIX_API'), $map_arr));
                }

                $arr = $this->agentArr;
                if ($agent_infos) {
                    $arr = array_intersect($arr, $agent_infos);
                } elseif ($map_arr && !$agent_infos) {
                    exit(json_encode(array('rows' => array(), 'results' => 0)));
                }

                sort($arr);
                if (count($arr) < 1) {
                    $arr[] = '-1';
                }

                $where .= ' and agent in("' . implode('","', $arr) . '")';
                $map['agent'] = array('in', $arr);
            }

            if ($data['game_id']) {
                $where .= ' and gameId=' . $data['game_id'];
                $map['gameId'] = $data['game_id'];
            }

            if ($data['startDate'] && $data['endDate']) {
                $where .= ' and dayTime>="' . $data['startDate'] . '" and dayTime<"' . date('Y-m-d', strtotime($data['endDate'] . '+1 day')) . '"';
                $map['dayTime'] = array(array('egt', $data['startDate']), array('lt', date('Y-m-d', strtotime($data['endDate'] . '+1 day'))), 'and');
            }

            $res        = D('Admin')->getPayLtvAgentData($map, $start, $pageSize, $where); //LTV留存数据
            $res_dau    = D('Admin')->getDauData($map, $start, $pageSize, $where); //Dau数据
            $results    = $res['count'];
            $game_list  = getDataList('game', 'id', C('DB_PREFIX_API'));
            $agent_list = getDataList('agent', 'agent', C('DB_PREFIX_API'));
            foreach ($res['list'] as $key => $val) {
                $data['lookType'] == 1 && $res['list'][$key]['agent']    = '-';
                $data['lookType'] == 1 && $res['list'][$key]['serverId'] = '-';
                $res['list'][$key]['gameName']                           = $game_list[$val['gameId']]['gameName'];
                $res['list'][$key]['agentName']                          = $agent_list[$val['agent']]['agentName'];

            }

            if ($data['lookType'] == 3 || $data['lookType'] == 4) {
                $day_arr = array();
                for ($i = 0; $i <= 90; $i++) {
                    $day_arr[] = $i;
                }
                foreach ($res_dau['list'] as $key => $value) {
                    for ($i = 0; $i < 120; $i++) {
                        if (in_array($i, $day_arr)) {
                            if (isset($res['list'][$key])) {
                                $res['list'][$key]['dau' . $i] = $value['day' . $i];
                            }
                        }
                    }
                }
            }

            $remainArr = $this->ltvRemain($res['list'], $data['lookType']);
            $rows      = $remainArr;
            //显示图表
            if ($data['chart'] == 1) {
                $ltvChart = $this->ltvChart($rows);
                if ($ltvChart === false) {
                    $this->ajaxReturn(array('status' => 0, 'info' => array('dayTime' => array(), 'key' => array(), 'data' => array())));
                }
                $this->ajaxReturn(array('status' => 1, 'info' => $ltvChart));
            }
            //数据汇总
            $pageSummary = $this->ltvSummarys($rows, $data['lookType']);

            $day_arr = array();
            for ($i = 0; $i <= 90; $i++) {
                $day_arr[] = $i;
            }
            if ($data['lookType'] == 3) {
                foreach ($rows as $key => &$value) {
                    for ($i = 0; $i <= 120; $i++) {
                        if (in_array($i, $day_arr)) {
                            if ($value['ltv' . $i] != 0) {
                                $value['ltv' . $i] = numFormat($value['ltv' . $i] / 100, true);
                            }
                        }
                    }
                }
            }
            if ($data['export'] == 1) {
                if ($data['lookType'] == 1) {
                    $title = 'Ltv';
                } elseif ($data['lookType'] == 2) {
                    $title = '充值金额';
                } elseif ($data['lookType'] == 3) {
                    $title = '付费率';
                } else {
                    $title = 'ARPU';
                }

                $col = array('dayTime' => '注册日期', 'gameName' => '游戏名称', 'newUser' => '新增用户数', 'allmoney' => '充值金额');

                for ($i = 0; $i <= 120; $i++) {
                    if (in_array($i, $day_arr)) {
                        $col['ltv' . $i] = ($i + 1) . '日' . $title;
                    }
                }
                array_unshift($rows, $col);
                $pageSummary['dayTime'] = '汇总';
                array_push($rows, $pageSummary);
                export_to_csv($rows, $title . '数据统计', $col);
                exit();
            }
            $arr = array('rows' => $rows ? $rows : array(), 'results' => $results, 'pageSummary' => $pageSummary);
            unset($rows, $remainArr);
            exit(json_encode($arr));
        } else {
            if (IS_AJAX) {
                $response = $this->fetch();
                $this->ajaxReturn(array('status' => 1, '_html' => $response));
            } else {
                $this->display();
            }
        }
    }

    //处理ltv数据
    private function ltvRemain($info, $lookType)
    {
        if (empty($info)) {
            return false;
        }

        $allmoney = 0;
        if ($lookType == '1') {
            foreach ($info as $k => $v) {
                $days = (strtotime(date('Y-m-d')) - strtotime($v['dayTime'])) / 86400;
                for ($i = 0; $i < $days; $i++) {
                    $allmoney += $v['day' . $i]; //每一天的充值金额累计起来
                    if (isset($v['day' . $i])) {
                        $info[$k]['ltv' . $i] = numFormat($allmoney / $v['newUser']) ? numFormat($allmoney / $v['newUser']) : 0;
                    }
                }
                $info[$k]['allmoney'] = $allmoney;
                $allmoney             = 0;
            }
        } elseif ($lookType == '2') {
            foreach ($info as $k => $v) {
                $days = (strtotime(date('Y-m-d')) - strtotime($v['dayTime'])) / 86400;
                for ($i = 0; $i < $days; $i++) {
                    $allmoney += $v['day' . $i]; //每一天的充值金额累计起来
                    if (isset($v['day' . $i])) {
                        $info[$k]['ltv' . $i] = $v['day' . $i] ? $v['day' . $i] : 0;
                    }
                }
                $info[$k]['allmoney'] = $allmoney;
                $allmoney             = 0;
            }
        } elseif ($lookType == '3') {
            foreach ($info as $k => $v) {
                $days = (strtotime(date('Y-m-d')) - strtotime($v['dayTime'])) / 86400;
                for ($i = 0; $i < $days; $i++) {
                    $allmoney += $v['day' . $i]; //每一天的充值金额累计起来
                    if (isset($v['user' . $i])) {
                        if ($v['dau' . $i] == 0) {
                            if ($i == 0) {
                                $info[$k]['ltv' . $i] = numFormat($v['user' . $i] * 100 / $v['newUser']) ? numFormat($v['user' . $i] * 100 / $v['newUser']) : 0;
                            } else {
                                $info[$k]['ltv' . $i] = 0;
                            }
                        } else {
                            $info[$k]['ltv' . $i] = numFormat($v['user' . $i] / $v['dau' . $i] * 100) ? numFormat($v['user' . $i] * 100 / $v['dau' . $i]) : 0;
                        }
                    }
                }
                $info[$k]['allmoney'] = $allmoney;
                $allmoney             = 0;
            }
        } else {
            foreach ($info as $k => $v) {
                $days = (strtotime(date('Y-m-d')) - strtotime($v['dayTime'])) / 86400;
                for ($i = 0; $i < $days; $i++) {
                    $allmoney += $v['day' . $i]; //每一天的充值金额累计起来
                    if (isset($v['day' . $i])) {
                        if ($v['day' . $i] == 0) {
                            if ($i == 0) {
                                $info[$k]['ltv' . $i] = numFormat($v['day' . $i] / $v['newUser']) ? numFormat($v['day' . $i] / $v['newUser']) : 0;
                            } else {
                                $info[$k]['ltv' . $i] = 0;
                            }
                        } else {
                            $info[$k]['ltv' . $i] = numFormat($v['day' . $i] / $v['dau' . $i]) ? numFormat($v['day' . $i] / $v['dau' . $i]) : 0;
                        }
                    }
                }
                $info[$k]['allmoney'] = $allmoney;
                $allmoney             = 0;
            }
        }

        return $info;
    }

    //ltv数据汇总
    private function ltvSummarys($data, $lookType)
    {
        /*$sum = array();
        $data_num = count($data);
        $day_arr = array('ltv0','ltv1','ltv2','ltv3','ltv4','ltv5','ltv6','ltv7','ltv8','ltv9','ltv13','ltv29','ltv59','ltv89');
        $day_num = array();
        //开始统计
        foreach ($data as $k => $val) {
        $sum['newUser'] += $val['newUser'];
        foreach ($val as $key => $value) {
        if(in_array($key, $day_arr)){
        $sum[$key] += $value;
        $value > 0 &&  $day_num[$key] ++;
        }
        }
        }

        //ltv平均值
        foreach ($sum as $k => $v) {
        if(in_array($k, $day_arr)){
        $sum[$k] = sprintf("%.2f",$v/$day_num[$k]);
        }
        }
        return $sum;*/

        $sum      = array();
        $ltv_sum  = array();
        $sum_user = array();
        $sum_dau  = array();
        $day_num  = array();
//        $day_arr = array('day0','day1','day2','day3','day4','day5','day6','day7','day8','day9','day10','day11','day12','day13','day14','day29','day59','day89');
        //        $user_arr = array('user0','user1','user2','user3','user4','user5','user6','user7','user8','user9','user10','user11','user12','user13','user14','user29','user59','user89');
        //        $dau_arr = array('dau0','dau1','dau2','dau3','dau4','dau5','dau6','dau7','dau8','dau9','dau10','dau11','dau12','dau13','dau14','dau29','dau59','dau89');
        $day_arr  = array();
        $user_arr = array();
        $dau_arr  = array();
        for ($i = 0; $i <= 90; $i++) {
            $day_arr[]  = "day" . $i;
            $ltv_arr[]  = "ltv" . $i;
            $user_arr[] = "user" . $i;
            $dau_arr[]  = "dau" . $i;
        }

        $allmoney = 0;

        //开始统计
        foreach ($data as $k => $val) {
            $sum['newUser'] += $val['newUser'];
            $allmoney += $val['allmoney'];
            foreach ($val as $key => $value) {
                if (in_array($key, $day_arr)) {
                    $sum[$key] += $value;
                }
                if (in_array($key, $user_arr)) {
                    $sum_user[$key] += $value;
                }
                if (in_array($key, $dau_arr)) {
                    $sum_dau[$key] += $value;
                }
                if (in_array($key, $ltv_arr)) {
                    $ltv_sum[$key] += $value;
                    $value > 0 && $day_num[$key]++;
                }
            }
        }

        $days = (strtotime(date('Y-m-d')) - strtotime($data[0]['dayTime'])) / 86400;
        if ($lookType == 1) {
            //ltv汇总
            for ($i = 0; $i < $days; $i++) {
                if (isset($ltv_sum['ltv' . $i])) {
                    $info['ltv' . $i] = sprintf("%.2f", $ltv_sum['ltv' . $i] / $day_num['ltv' . $i]);
                }
            }
        } elseif ($lookType == 2) {
            for ($i = 0; $i < $days; $i++) {
                if (isset($sum['day' . $i])) {
                    $info['ltv' . $i] = $sum['day' . $i] ? $sum['day' . $i] : 0;
                }
            }
        } elseif ($lookType == 3) {
            for ($i = 0; $i < $days; $i++) {
                if (isset($sum_dau['dau' . $i])) {
                    $info['ltv' . $i] = numFormat($sum_user['user' . $i] / $sum_dau['dau' . $i], true) ? numFormat($sum_user['user' . $i] / $sum_dau['dau' . $i], true) : '0%';
                }
            }
        } else {
            for ($i = 0; $i < $days; $i++) {
                if (isset($sum_dau['dau' . $i])) {
                    $info['ltv' . $i] = numFormat($sum['day' . $i] / $sum_dau['dau' . $i]) ? numFormat($sum['day' . $i] / $sum_dau['dau' . $i]) : 0;
                }
            }
        }
        $info['newUser']  = $sum['newUser'];
        $info['allmoney'] = $allmoney;
        return $info;

    }

    //ltv图表数据格式处理
    private function ltvChart($data)
    {
        if (!$data) {
            return false;
        }

        // sort($data);
        $chart = array();
        //ltv key
        //        $_key = array('1','2','3','4','5','6','7','8','9','10','11','12','13','14','15','30','60','90');
        $_key = array();
        for ($i = 0; $i <= 90; $i++) {
            $_key[] = $i;
        }
        foreach ($data as $k => $v) {
            $chart['dayTime'][] = $v['dayTime'];
            foreach ($_key as $key => $value) {
                $chart['ltv' . $value][] = $v['ltv' . ($value - 1)];
            }
        }

        foreach ($_key as $k => $v) {
            $_key[$k] = 'ltv' . $v;
        }
        $chart['key'] = $_key;

        foreach ($_key as $k => $v) {
            $chart['data'][] = array('name' => $v, 'type' => 'line', 'smooth' => true, 'data' => $chart[$v]);
            unset($chart[$v]);
        }
        return $chart;
    }

    /**
     * 渠道数据统计
     */
    public function agentDataCount()
    {
        if (IS_POST) {
            $data = I();
            !$data['game_id'] && exit(json_encode(array('rows' => array(), 'results' => 0)));
            $agent_info   = $_REQUEST["agent"];
            $agent_p_info = $_REQUEST["agent_p"];
            $start        = $data["start"] ? $data["start"] : 0;
            $pageSize     = $data["limit"] ? $data["limit"] : 30;

            //处理搜索条件
            $agentArr = dealList($agent_info, $agent_p_info);

            $where = $where2 = '1';

            if ($agentArr['agent']) {
                $where2 = $where .= ' and agent in("' . implode('","', $agentArr['agent']) . '")';
            } else {
                if ($agentArr['pAgent']) {
                    $agent_p_arr = array_keys(getDataList('agent', 'id', C('DB_PREFIX_API'), array('agent' => array('IN', array_values($agentArr['pAgent'])))));

                    $agent_arr    = array_values($agentArr['pAgent']);
                    $agent_subarr = array_keys(getDataList('agent', 'agent', C('DB_PREFIX_API'), array('pid' => array('IN', $agent_p_arr))));
                    if ($agent_subarr) {
                        $agent_arr = array_merge($agent_arr, $agent_subarr);
                    }
                    $where2 = $where .= ' and agent in("' . implode('","', $agent_arr) . '")';
                } else {
                    //权限控制
                    $where2 = $where .= ' and agent in("' . implode('","', $this->agentArr) . '")';
                }
            }

            if ($data['startDate'] && $data['endDate']) {
                $where2 = $where .= ' and dayTime>="' . $data['startDate'] . '" and dayTime<"' . date('Y-m-d', strtotime($data['endDate'] . '+1 day')) . '"';
            }

            if ($data['serverId']) {
                $where2 .= ' and serverId="' . $data['serverId'] . '"';
            }

            if ($data['game_id']) {
                $where .= ' and gameId=' . $data['game_id'];
                $where2 .= ' and game_id=' . $data['game_id'];
            }
            $game_list    = getDataList('game', 'id', C('DB_PREFIX_API'));
            $agent_list   = getDataList('agent', 'agent', C('DB_PREFIX_API'));
            $res          = D('Admin')->getAgentDataCount($start, $pageSize, $where, $where2); //渠道充值分布数据
            $results      = $res['count']['total']; //总行数
            $totalMoney   = $res['count']['allPay']; //总金额
            $totalPayUser = $res['count']['allPayUser']; //总充值账号数
            $totalLogin   = $res['count']['allUserLogin']; //总活跃
            $totalnewUser = $res['count']['newUser']; //总新增人数

            foreach ($res['list'] as $key => $val) {
                if ($data['lookType'] == 1) {
                    $res['list'][$key]['dayTime'] = '-';
                }

                $res['list'][$key]['newDevice']    = intval($val['newDevice']);
                $res['list'][$key]['disUdid']      = intval($val['disUdid']); //唯一注册数
                $res['list'][$key]['newUser']      = intval($val['newUser']);
                $res['list'][$key]['newUserRate']  = numFormat(($val['disUdid'] / $val['newDevice']), true); //用户转化率
                $res['list'][$key]['oldUserLogin'] = intval($val['allUserLogin']) - intval($val['newUser']); //老用户活跃数
                $res['list'][$key]['userRate']     = numFormat(($val['newUser'] / $totalnewUser), true);
                $res['list'][$key]['loginRate']    = numFormat(($val['allUserLogin'] / $totalLogin), true);
                $res['list'][$key]['chargeRate']   = numFormat(($val['allPay'] / $totalMoney), true);
                $res['list'][$key]['payRate']      = numFormat(($val['allPayUser'] / $val['allUserLogin']), true);
                $res['list'][$key]['allPay']       = floatval($val['allPay']);
                $res['list'][$key]['ARPU']         = sprintf("%.2f", $val['allPay'] / $val['allUserLogin']);
                $res['list'][$key]['ARPPU']        = sprintf("%.2f", $val['allPay'] / $val['allPayUser']);
                $res['list'][$key]['newPay']       = floatval($val['newPay']); //新用户充值总额
                $res['list'][$key]['newPayUser']   = floatval($val['newPayUser']); //新用户充值总额
                $res['list'][$key]['payRateNew']   = numFormat(($val['newPayUser'] / $val['newUser']), true); //新增付费率
                $res['list'][$key]['newARPU']      = sprintf("%.2f", $val['newPay'] / $val['newUser']); //新增ARPU
                $res['list'][$key]['newARPPU']     = sprintf("%.2f", $val['newPay'] / $val['newPayUser']); //新增ARPPU

                $res['list'][$key]['gameName']  = $game_list[$val['gameId']]['gameName'];
                $res['list'][$key]['agentName'] = $agent_list[$val['agent']]['agentName'];
                if ($data['export'] == 1) {
                    $remainArr = $this->userRemainSetAgent($res['list'][$key]);
                } else {
                    $remainArr = $this->userRemainSetAgent($res['list'][$key], 1);
                }
                $rows[] = $remainArr;
            }
            // $rows = $res['list'];
            //显示图表
            if ($data['chart'] == 1) {
                $agentPayChart              = $this->agentPayChart($rows, $totalMoney);
                $agentPayChart['pageCount'] = ceil($results / $pageSize);
                if ($agentPayChart === false) {
                    $this->ajaxReturn(array('status' => 0, 'info' => array('agent' => array(), 'rate' => array(), 'data' => array())));
                }
                $this->ajaxReturn(array('status' => 1, 'info' => $agentPayChart));
            }
            //数据汇总
            /*$pageSummary = array('newDevice'=>array_sum(array_column($res['list'], 'newDevice')),'newUser'=>array_sum(array_column($res['list'], 'newUser')),'allUserLogin'=>array_sum(array_column($res['list'], 'allUserLogin')),'allPayUser'=>array_sum(array_column($res['list'], 'allPayUser')),'allPay'=>array_sum(array_column($res['list'], 'allPay')),'ARPU'=>sprintf("%.2f",(array_sum(array_column($res['list'], 'ARPU')))/count($res['list'])),'ARPPU'=>sprintf("%.2f",(array_sum(array_column($res['list'], 'ARPPU')))/count($res['list'])));
             */if ($data['export'] == 1) {
                $pageSummary = $this->summarys_new($rows, 0);
            } else {
                $pageSummary = $this->summarys_new($rows);
            }
            if ($data['export'] == 1) {
                $col     = array('dayTime' => '统计日期', 'agent' => '渠道号', 'agentName' => '包名称', 'gameName' => '游戏名称', 'newDevice' => '激活设备数', 'disUdid' => '唯一注册数', 'newUser' => '新增用户数', 'newUserRate' => '用户转化率', 'userRate' => '新增用户占比', 'allUserLogin' => '活跃用户数', 'oldUserLogin' => '老用户活跃数', 'loginRate' => '活跃用户占比', 'allPay' => '充值金额', 'chargeRate' => '充值占比', 'allPayUser' => '充值用户数', 'payRate' => '付费率', 'ARPU' => 'ARPU', 'ARPPU' => 'ARPPU', 'newPay' => '新用户充值总额', 'newPayUser' => '新用户充值账号', 'payRateNew' => '新增付费率', 'newARPU' => '新增ARPU', 'newARPPU' => '新增ARPPU');
                $day_arr = array(1, 2, 3, 4, 5, 6, 13, 29);

                for ($i = 0; $i <= 120; $i++) {
                    if (in_array($i, $day_arr)) {
                        $col['day' . $i] = ($i + 1) . '日留存';
                    }
                }

                array_unshift($rows, $col);
                $pageSummary['dayTime'] = '汇总';
                array_push($rows, $pageSummary);
                export_to_csv($rows, '渠道数据统计', $col);
                exit();
            }
            $arr = array('rows' => $rows ? $rows : array(), 'results' => $results, 'pageSummary' => $pageSummary);
            unset($rows, $remainArr);
            exit(json_encode($arr));
        } else {
            if (IS_AJAX) {
                $response = $this->fetch();
                $this->ajaxReturn(array('status' => 1, '_html' => $response));
            } else {
                $this->display();
            }
        }
    }

    /**
     * 渠道充值分布统计
     */
    public function agentPay()
    {
        if (IS_POST) {
            $data = I();
            !$data['game_id'] && exit(json_encode(array('rows' => array(), 'results' => 0)));
            $agent_info   = $_REQUEST["agent"];
            $agent_p_info = $_REQUEST["agent_p"];
            $start        = $data["start"] ? $data["start"] : 0;
            $pageSize     = $data["limit"] ? $data["limit"] : 30;

            //处理搜索条件
            $agentArr = dealList($agent_info, $agent_p_info);

            $where = '1';

            if ($agentArr['agent']) {
                $where .= ' AND agent IN("' . implode('","', $agentArr['agent']) . '")';
                $map['agent'] = array('in', $agentArr['agent']);
            } else {
                if ($agentArr['pAgent']) {
                    $agent_p_arr = array_keys(getDataList('agent', 'id', C('DB_PREFIX_API'), array('agent' => array('IN', array_values($agentArr['pAgent'])))));

                    $agent_arr    = array_values($agentArr['pAgent']);
                    $agent_subarr = array_keys(getDataList('agent', 'agent', C('DB_PREFIX_API'), array('pid' => array('IN', $agent_p_arr))));
                    if ($agent_subarr) {
                        $agent_arr = array_merge($agent_arr, $agent_subarr);
                    }
                    $where .= ' AND agent IN("' . implode('","', $agent_arr) . '")';
                    $map['agent'] = array('in', $agent_arr);
                } else {
                    //权限控制
                    $where .= ' AND agent IN("' . implode('","', $this->agentArr) . '")';
                    $map['agent'] = array('in', $this->agentArr);
                }
            }

            if ($data['game_id']) {
                $where .= ' and game_id=' . $data['game_id'];
                $map['game_id'] = $data['game_id'];
            }

            if ($data['startDate'] && $data['endDate']) {
                $where .= ' and dayTime>="' . $data['startDate'] . '" and dayTime<"' . date('Y-m-d', strtotime($data['endDate'] . '+1 day')) . '"';
                $map['dayTime'] = array(array('egt', $data['startDate']), array('lt', date('Y-m-d', strtotime($data['endDate'] . '+1 day'))), 'and');
            }

            if ($data['serverId']) {
                $where .= ' and serverId="' . $data['serverId'] . '"';
                $map['serverId'] = $data['serverId'];
            }
            $game_list  = getDataList('game', 'id', C('DB_PREFIX_API'));
            $res        = D('Admin')->getAgentPayData($map, $start, $pageSize, $where); //渠道充值分布数据
            $results    = $res['count']['total']; //总行数
            $totalMoney = $res['count']['totalMoney']; //总金额

            $dayTime = array('2017-11-09', '2017-11-10', '2017-11-11', '2017-11-12', '2017-11-13', '2017-11-14');

            if (in_array(session('admin.role_id'), array(1, 3)) && $data['game_id'] == 113) {
                $ordermap['paymentTime'] = array(array('EGT', strtotime($data['startDate'])), array('LT', strtotime(date('Y-m-d', strtotime($data['endDate'] . '+1 day')))), 'AND');
                $ordermap['game_id']     = 108;
                $amount                  = M('order', 'lg_')->where($ordermap)->sum('amount');

            }
            foreach ($res['list'] as $key => $val) {
                if ($val['agent'] == 'ytxjlAND048' && $amount) {
                    $res['list'][$key]['totalPay'] = floatval($val['totalPay'] + $amount);
                    $res['list'][$key]['rate']     = numFormat((($val['totalPay'] + $amount) / ($totalMoney + $amount)), true);
                    $res['list'][$key]['gameName'] = $game_list[$val['game_id']]['gameName'];
                } else {

                    $res['list'][$key]['totalPay'] = floatval($val['totalPay']);

                    $res['list'][$key]['rate']     = numFormat(($val['totalPay'] / $totalMoney), true);
                    $res['list'][$key]['gameName'] = $game_list[$val['game_id']]['gameName'];
                }
            }
            $rows = $res['list'];
            //显示图表
            if ($data['chart'] == 1) {
                $agentPayChart              = $this->agentPayChart($rows, $totalMoney);
                $agentPayChart['pageCount'] = ceil($results / $pageSize);
                if ($agentPayChart === false) {
                    $this->ajaxReturn(array('status' => 0, 'info' => array('agent' => array(), 'rate' => array(), 'data' => array())));
                }
                $this->ajaxReturn(array('status' => 1, 'info' => $agentPayChart));
            }
            //数据汇总
            $pageSummary = array('totalPay' => sprintf("%.2f", array_sum(array_column($res['list'], 'totalPay'))));
            if ($data['export'] == 1) {
                $col = array('gameName' => '游戏名称', 'agent' => '渠道号', 'totalPay' => '充值金额', 'rate' => '百分比');
                array_unshift($rows, $col);
                $pageSummary['gameName'] = '汇总';
                array_push($rows, $pageSummary);
                export_to_csv($rows, '渠道充值分布', $col);
                exit();
            }
            $arr = array('rows' => $rows ? $rows : array(), 'results' => $results, 'pageSummary' => $pageSummary);
            unset($rows, $remainArr);
            exit(json_encode($arr));
        } else {
            if (IS_AJAX) {
                $response = $this->fetch();
                $this->ajaxReturn(array('status' => 1, '_html' => $response));
            } else {
                $this->display();
            }
        }
    }

    //渠道充值分布图表数据格式处理
    private function agentPayChart($data, $totalMoney)
    {
        if (!$data) {
            return false;
        }

        rsort($data);
        $chart = array();
        foreach ($data as $key => $value) {
            $chart['agent'][] = $value['agent'];
            $chart['data'][]  = $value['totalPay'];
            $chart['rate'][]  = numFormat(($value['totalPay'] / $totalMoney), true);
        }
        return $chart;
    }

    /**
     * 充值等级分布统计
     */
    public function payLevel()
    {
        if (IS_POST) {
            $data = I();
            !$data['game_id'] && exit(json_encode(array('rows' => array(), 'results' => 0)));
            $agent_info   = $_REQUEST["agent"];
            $agent_p_info = $_REQUEST["agent_p"];
            $start        = $data["start"] ? $data["start"] : 0;
            $pageSize     = $data["limit"] ? $data["limit"] : 30;

            //处理搜索条件
            $agentArr = dealList($agent_info, $agent_p_info);

            $where = '1';
            if ($agentArr['agent']) {
                $where .= ' and agent in("' . implode('","', $agentArr['agent']) . '")';
                $map['agent'] = array('in', $agentArr['agent']);
            } else {
                if ($agentArr['pAgent']) {
                    $agent_p_arr = array_keys(getDataList('agent', 'id', C('DB_PREFIX_API'), array('agent' => array('IN', array_values($agentArr['pAgent'])))));

                    $agent_arr    = array_values($agentArr['pAgent']);
                    $agent_subarr = array_keys(getDataList('agent', 'agent', C('DB_PREFIX_API'), array('pid' => array('IN', $agent_p_arr))));
                    if ($agent_subarr) {
                        $agent_arr = array_merge($agent_arr, $agent_subarr);
                    }
                    $where .= ' and agent in("' . implode('","', $agent_arr) . '")';
                    $map['agent'] = array('in', $agent_arr);
                } else {
                    //权限控制
                    $where .= ' and agent in("' . implode('","', $this->agentArr) . '")';
                    $map['agent'] = array('in', $this->agentArr);
                }
            }

            if ($data['game_id']) {
                $where .= ' and game_id=' . $data['game_id'];
                $map['game_id'] = $data['game_id'];
            }

            if ($data['startDate'] && $data['endDate']) {
                $where .= ' and dayTime>="' . $data['startDate'] . '" and dayTime<"' . date('Y-m-d', strtotime($data['endDate'] . '+1 day')) . '"';
                $map['dayTime'] = array(array('egt', $data['startDate']), array('lt', date('Y-m-d', strtotime($data['endDate'] . '+1 day'))), 'and');
            }

            if ($data['serverId']) {
                $where .= ' and serverId="' . $data['serverId'] . '"';
                $map['serverId'] = $data['serverId'];
            }
            $game_list  = getDataList('game', 'id', C('DB_PREFIX_API'));
            $res        = D('Admin')->getPayLevelData($map, $start, $pageSize, $where); //充值等级分布数据
            $results    = $res['count']['total']; //总行数
            $totalMoney = $res['count']['totalMoney']; //总金额
            $totalUser  = $res['count']['totalUser']; //总用户
            $payRate    = 0;
            $userRate   = 0;
            foreach ($res['list'] as $key => $val) {
                $payRate += $res['list'][$key]['payRate']   = numFormat(($val['totalPay'] / $totalMoney), true);
                $userRate += $res['list'][$key]['userRate'] = numFormat(($val['totalUser'] / $totalUser), true);
                $res['list'][$key]['totalPay']              = floatval($val['totalPay']);
                $res['list'][$key]['userRate']              = numFormat(($val['totalUser'] / $totalUser), true);
                $res['list'][$key]['gameName']              = $game_list[$val['game_id']]['gameName'];
            }
            $rows = $res['list'];
            //显示图表
            if ($data['chart'] == 1) {
                $payLevelChart = $this->payLevelChart($rows, $totalMoney);
                if ($payLevelChart === false) {
                    $this->ajaxReturn(array('status' => 0, 'info' => array('level' => 0, 'pay' => array('payMax' => 0, 'totalPay' => array()), 'user' => array('userMax' => 0, 'totalUser' => array()))));
                }
                $this->ajaxReturn(array('status' => 1, 'info' => $payLevelChart));
            }
            //数据汇总
            $pageSummary = array('totalPay' => sprintf("%.2f", array_sum(array_column($res['list'], 'totalPay'))), 'totalUser' => sprintf("%.2f", array_sum(array_column($res['list'], 'totalUser'))), 'payRate' => number_format($payRate / count($res['list']), 2) . '%', 'userRate' => number_format($userRate / count($res['list']), 2) . '%');
            if ($data['export'] == 1) {
                $col = array('gameName' => '游戏名称', 'level' => '等级', 'totalPay' => '充值金额', 'payRate' => '充值金额占比', 'totalUser' => '充值账号数', 'userRate' => '账号数占比');
                array_unshift($rows, $col);
                $pageSummary['gameName'] = '汇总';
                array_push($rows, $pageSummary);
                export_to_csv($rows, '充值等级分布', $col);
                exit();
            }
            $arr = array('rows' => $rows ? $rows : array(), 'results' => $results, 'pageSummary' => $pageSummary);
            unset($rows, $remainArr);
            exit(json_encode($arr));
        } else {
            if (IS_AJAX) {
                $response = $this->fetch();
                $this->ajaxReturn(array('status' => 1, '_html' => $response));
            } else {
                $this->display();
            }
        }
    }

    //充值等级分布图表数据格式处理
    private function payLevelChart($data, $totalMoney)
    {
        if (!$data) {
            return false;
        }

        $chart = array();
        $level = array_column($data, 'level');
        rsort($level);
        $maxLevel = $level[0];
        //循环生成等级
        for ($i = 0; $i <= $maxLevel; $i++) {
            $chart['level'][] = $i;
        }

        //充值金额
        $payMax = array_column($data, 'totalPay');
        rsort($payMax);
        $chart['pay']['payMax'] = $payMax[0] + 100; //最大值加上100为了好看

        //充值人数
        $userMax = array_column($data, 'totalUser');
        rsort($userMax);
        $chart['user']['userMax'] = $userMax[0] + 10; //最大值加上10

        //拼接图表数据
        $totalPay  = array_column($data, 'totalPay', 'level'); //每个等级充值金额
        $totalUser = array_column($data, 'totalUser', 'level'); //每个等级充值人数

        foreach ($chart['level'] as $value) {
            $chart['pay']['totalPay'][]   = is_null($totalPay[$value]) ? 0 : $totalPay[$value];
            $chart['user']['totalUser'][] = is_null($totalUser[$value]) ? 0 : $totalUser[$value];
        }

        return $chart;
    }

    /**
     * 活跃玩家等级分布统计
     */
    public function actPlayer()
    {
        if (IS_POST) {
            $data = I();
            !$data['game_id'] && exit(json_encode(array('rows' => array(), 'results' => 0)));
            $agent_info   = $_REQUEST["agent"];
            $agent_p_info = $_REQUEST["agent_p"];
            $start        = $data["start"] ? $data["start"] : 0;
            $pageSize     = $data["limit"] ? $data["limit"] : 30;

            //处理搜索条件
            $agentArr = dealList($agent_info, $agent_p_info);

            $where = '1';

            if ($agentArr['agent']) {
                $where .= ' AND agent IN("' . implode('","', $agentArr['agent']) . '")';
            } else {
                if ($agentArr['pAgent']) {
                    $agent_p_arr = array_keys(getDataList('agent', 'id', C('DB_PREFIX_API'), array('agent' => array('IN', array_values($agentArr['pAgent'])))));

                    $agent_arr    = array_values($agentArr['pAgent']);
                    $agent_subarr = array_keys(getDataList('agent', 'agent', C('DB_PREFIX_API'), array('pid' => array('IN', $agent_p_arr))));
                    if ($agent_subarr) {
                        $agent_arr = array_merge($agent_arr, $agent_subarr);
                    }
                    $where .= ' AND agent IN("' . implode('","', $agent_arr) . '")';
                } else {
                    //权限控制
                    $where .= ' AND agent IN("' . implode('","', $this->agentArr) . '")';
                }
            }

            if ($data['game_id']) {
                $where .= ' and game_id=' . $data['game_id'];
            }

            if ($data['startDate']) {
                $where .= ' and updateTime>="' . strtotime($data['startDate']) . '" and updateTime<"' . strtotime($data['startDate'] . '+1 day') . '"';
            }

            if ($data['serverId']) {
                $where .= ' and serverId="' . $data['serverId'] . '"';
            }
            $game_list = getDataList('game', 'id', C('DB_PREFIX_API'));
            $res       = D('Admin')->getActPlayerData($start, $pageSize, $where); //活跃用户等级分布数据
            $results   = $res['count']['total']; //总行数
            $totalUser = $res['count']['totalUser']; //总用户

            foreach ($res['list'] as $key => $val) {
                $res['list'][$key]['actUserRate'] = $data['export'] == 1 ? floatval(sprintf("%.2f", ($val['totalUser'] / $totalUser) * 100)) . '%' : floatval(sprintf("%.2f", ($val['totalUser'] / $totalUser) * 100));

                $res['list'][$key]['totalUser'] = intval($val['totalUser']);
                $res['list'][$key]['gameName']  = $game_list[$val['game_id']]['gameName'];
            }
            $rows = $res['list'];
            //显示图表
            if ($data['chart'] == 1) {
                $actPlayerChart = $this->actPlayerChart($rows, $totalUser);
                if ($actPlayerChart === false) {
                    $this->ajaxReturn(array('status' => 0, 'info' => array('level' => 0, 'user' => array('userMax' => 0, 'totalUser' => array(), 'rate' => array()))));
                }
                $this->ajaxReturn(array('status' => 1, 'info' => $actPlayerChart));
            }
            //数据汇总
            $pageSummary = array('totalUser' => sprintf("%.2f", array_sum(array_column($res['list'], 'totalUser'))));
            if ($data['export'] == 1) {
                $col = array('gameName' => '游戏名称', 'level' => '等级', 'totalUser' => '账号数', 'actUserRate' => '账号数占比');
                array_unshift($rows, $col);
                $pageSummary['gameName'] = '汇总';
                array_push($rows, $pageSummary);
                export_to_csv($rows, '活跃玩家等级分布', $col);
                exit();
            }
            $arr = array('rows' => $rows ? $rows : array(), 'results' => $results, 'pageSummary' => $pageSummary);
            unset($rows, $remainArr);
            exit(json_encode($arr));
        } else {
            if (IS_AJAX) {
                $response = $this->fetch();
                $this->ajaxReturn(array('status' => 1, '_html' => $response));
            } else {
                $this->display();
            }
        }
    }

    //活跃玩家图表数据格式处理
    private function actPlayerChart($data, $totalUser)
    {
        if (!$data) {
            return false;
        }

        $chart = array();
        $level = array_column($data, 'level');
        rsort($level);
        $maxLevel = $level[0];
        //循环生成等级
        for ($i = 0; $i <= $maxLevel; $i++) {
            $chart['level'][] = $i;
        }

        //充值人数
        $userMax = array_column($data, 'totalUser');
        rsort($userMax);
        $chart['user']['userMax'] = $userMax[0] + 10; //最大值加上10

        //拼接图表数据
        $totalUserArr = array_column($data, 'totalUser', 'level'); //每个等级活跃人数

        foreach ($chart['level'] as $value) {
            $chart['user']['rate'][]      = is_null($totalUserArr[$value]) ? 0 : floatval((numFormat(($totalUserArr[$value] / $totalUser)) * 100));
            $chart['user']['totalUser'][] = is_null($totalUserArr[$value]) ? 0 : $totalUserArr[$value];
        }

        return $chart;
    }

    /**
     * 充值档位分布统计
     */
    public function payGear()
    {
        if (IS_POST) {
            $data = I();
            !$data['game_id'] && exit(json_encode(array('rows' => array(), 'results' => 0)));
            $agent_info   = $_REQUEST["agent"];
            $agent_p_info = $_REQUEST["agent_p"];
            $start        = $data["start"] ? $data["start"] : 0;
            $pageSize     = $data["limit"] ? $data["limit"] : 30;

            //处理搜索条件
            $agentArr = dealList($agent_info, $agent_p_info);

            $where = '1';
            if ($agentArr['agent']) {
                $where .= ' and agent in("' . implode('","', $agentArr['agent']) . '")';
                $map['agent'] = array('in', $agentArr['agent']);
            } else {
                if ($agentArr['pAgent']) {
                    $agent_p_arr = array_keys(getDataList('agent', 'id', C('DB_PREFIX_API'), array('agent' => array('IN', array_values($agentArr['pAgent'])))));

                    $agent_arr    = array_values($agentArr['pAgent']);
                    $agent_subarr = array_keys(getDataList('agent', 'agent', C('DB_PREFIX_API'), array('pid' => array('IN', $agent_p_arr))));
                    if ($agent_subarr) {
                        $agent_arr = array_merge($agent_arr, $agent_subarr);
                    }
                    $where .= ' and agent in("' . implode('","', $agent_arr) . '")';
                    $map['agent'] = array('in', $agent_arr);
                } else {
                    //权限控制
                    $where .= ' and agent in("' . implode('","', $this->agentArr) . '")';
                    $map['agent'] = array('in', $this->agentArr);
                }
            }

            if ($data['game_id']) {
                $where .= ' and game_id=' . $data['game_id'];
                $map['game_id'] = $data['game_id'];
            }

            if ($data['startDate'] && $data['endDate']) {
                $where .= ' and dayTime>="' . $data['startDate'] . '" and dayTime<"' . date('Y-m-d', strtotime($data['endDate'] . '+1 day')) . '"';
                $map['dayTime'] = array(array('egt', $data['startDate']), array('lt', date('Y-m-d', strtotime($data['endDate'] . '+1 day'))), 'and');
            }

            if ($data['serverId']) {
                $where .= ' and serverId="' . $data['serverId'] . '"';
                $map['serverId'] = $data['serverId'];
            }
            $game_list  = getDataList('game', 'id', C('DB_PREFIX_API'));
            $res        = D('Admin')->getPayGearData($map, $start, $pageSize, $where); //渠道充值分布数据
            $results    = $res['count']['total']; //总行数
            $totalMoney = $res['count']['totalMoney']; //总金额
            $payRate    = 0;
            $userRate   = 0;
            foreach ($res['list'] as $key => $val) {
                $payRate += $res['list'][$key]['payRate'] = numFormat(($val['totalPay'] / $totalMoney), true);
                $res['list'][$key]['totalPay']            = floatval($val['totalPay']);
                $res['list'][$key]['gameName']            = $game_list[$val['game_id']]['gameName'];
            }
            $rows = $res['list'];
            //显示图表
            if ($data['chart'] == 1) {
                $payGearChart = $this->payGearChart($rows, $totalMoney);
                if ($payGearChart === false) {
                    $this->ajaxReturn(array('status' => 0, 'info' => array('key' => 0, 'data' => array())));
                }
                $this->ajaxReturn(array('status' => 1, 'info' => $payGearChart));
            }
            //数据汇总
            $pageSummary = array('totalPay' => sprintf("%.2f", array_sum(array_column($res['list'], 'totalPay'))));
            if ($data['export'] == 1) {
                $col = array('gameName' => '游戏名称', 'goods' => '商品档位', 'totalPay' => '充值金额', 'payRate' => '充值金额占比');
                array_unshift($rows, $col);
                $pageSummary['gameName'] = '汇总';
                array_push($rows, $pageSummary);
                export_to_csv($rows, '充值档位分布', $col);
                exit();
            }
            $arr = array('rows' => $rows ? $rows : array(), 'results' => $results, 'pageSummary' => $pageSummary);
            unset($rows, $remainArr);
            exit(json_encode($arr));
        } else {
            if (IS_AJAX) {
                $response = $this->fetch();
                $this->ajaxReturn(array('status' => 1, '_html' => $response));
            } else {
                $this->display();
            }
        }
    }

    //充值档位分布图表数据格式处理
    private function payGearChart($data, $totalMoney)
    {
        if (!$data) {
            return false;
        }

        $chart = array();

        $chart['key'] = array_column($data, 'goods');
        foreach ($data as $key => $value) {
            $chart['data'][$key]['value'] = $value['totalPay'];
            $chart['data'][$key]['name']  = $value['goods'];
        }

        return $chart;
    }

    /**
     * 实时注册图表
     */
    public function registerChart()
    {
        if (IS_POST) {
            $data         = I();
            $agent_info   = $_REQUEST["agent"];
            $agent_p_info = $_REQUEST["agent_p"];

            //处理搜索条件
            $agentArr = dealList($agent_info, $agent_p_info);

            /*if ($data["gameType"] || $data["advteruser_id"] || $data["creater"]) {
            $agent_map  = array();
            $data["creater"] && $agent_map["creater"] = $data["creater"];
            $data["gameType"] && $agent_map["gameType"] = $data["gameType"];
            $data["advteruser_id"] && $agent_map["advteruser_id"] = $data["advteruser_id"];
            $agent      = D("Admin")->commonQuery("agent", $agent_map, 0, null, "agent", "lg_");
            $agent_a[]  = "1";
            foreach ($agent as $a) {
            $agent_a[] = $a["agent"];
            }
            $map["_string"] = "regAgent IN ('".implode("','", $agent_a)."')";
            }

            if ($agentArr['agent']) {
            $map["regAgent"]    = array("in", $agentArr['agent']);
            } else {
            if ($agentArr['pAgent']) {

            $agent_p_arr       = array_keys(getDataList('agent','id',C('DB_PREFIX_API'),array('agent'=>array('IN',array_values($agentArr['pAgent'])))));

            $agent_arr         = array_values($agentArr['pAgent']);
            $agent_subarr      = array_keys(getDataList('agent','agent',C('DB_PREFIX_API'),array('pid'=>array('IN',$agent_p_arr))));
            if($agent_subarr){
            $agent_arr = array_merge($agent_arr,$agent_subarr);
            }
            $map["regAgent"]    = array("in", $agent_arr);
            } else {
            //权限控制
            $map["regAgent"]    = array("in", $this->agentArr);
            }
            }*/
            if ($agentArr['agent']) {
                $map['regAgent'] = array('in', $agentArr['agent']);
            } else {
                $agent_infos = $map_arr = array();

                if (!empty($agentArr['pAgent'])) {
                    $map_arr['_string'] = "id IN ('" . implode("','", $agentArr['pAgent']) . "') OR pid IN ('" . implode("','", $agentArr['pAgent']) . "')";
                }

                if ($data['advteruser_id']) {
                    $map_arr['advteruser_id'] = $data['advteruser_id'];
                }

                if ($data['creater']) {
                    $map_arr['creater'] = $data['creater'];
                }

                if ($data['gameType']) {
                    $map_arr['gameType'] = $data['gameType'];
                }

                if ($data['department']) {
                    $map_arr['departmentId'] = $data['department'];
                }

                if ($map_arr) {
                    $agent_infos = array_keys(getDataList('agent', 'agent', C('DB_PREFIX_API'), $map_arr));
                }

                $arr = $this->agentArr;
                if ($agent_infos) {
                    $arr = array_intersect($arr, $agent_infos);
                } elseif ($map_arr && !$agent_infos) {
                    exit(json_encode(array('rows' => array(), 'results' => 0)));
                }

                sort($arr);
                if (count($arr) < 1) {
                    $arr[] = '-1';
                }

                $map['regAgent'] = array('in', $arr);

            }

            if ($data["game_id"]) {
                $map["game_id"] = $data["game_id"];
            }

            if ($data["serverId"]) {
                $map["serverId"] = $data["serverId"];
            }

            $search = $map;
            if ($data["date"]) {
                $map["regTime"] = array(array("egt", strtotime($data["date"])), array("lt", strtotime($data["date"] . " +1 day")), "and");
                if ($data["date"] == date("Y-m-d")) {
                    $search["regTime"] = array(array("egt", strtotime($data["date"] . " -1 day")), array("lt", strtotime(date("Y-m-d H:i:s") . " -1 day")), "and");
                } else {
                    $search["regTime"] = array(array("egt", strtotime($data["date"] . " -1 day")), array("lt", strtotime($data["date"])), "and");
                }
            }

            $res      = D("Admin")->getHourRegisterCount($map);
            $bef      = D("Admin")->getHourRegisterCount($search);
            $max      = 0;
            $arr      = array();
            $arr_ber  = array();
            $info     = array();
            $info_bef = array();
            $list     = array();
            $row      = array();
            foreach ($res as $val) {
                $val["hour"] > $max && $max = $val["hour"];
                $arr[intval($val["hour"])] += intval($val["user"]);
                $list[$val["regAgent"]]["regAgent"]   = $val["regAgent"];
                $list[$val["regAgent"]][$val["hour"]] = $val["user"];
                $list[$val["regAgent"]]["count"] += intval($val["user"]);
                $list["统计"][$val["hour"]] += $val["user"];
                $list["统计"]["count"] += $val["user"];
            }

            foreach ($bef as $val) {
                $arr_ber[intval($val["hour"])] += intval($val["user"]);
                if (isset($list[$val["regAgent"]])) {
                    $list[$val["regAgent"]]["count_bef"] += intval($val["user"]);
                }
                $list["统计"]["count_bef"] += intval($val["user"]);
            }

            $n = 1;
            foreach ($list as $k => $v) {
                if ($k == "统计") {
                    continue;
                }

                $agent                                      = D("Admin/Admin")->commonQuery("agent", array("agent" => $v["regAgent"]), 0, 1, "*", "lg_");
                $row[$v["count"] * 10000 + $n]              = $v;
                $row[$v["count"] * 10000 + $n]["agentName"] = $agent["agentName"] ? $agent["agentName"] : "-";
                $n++;
            }

            krsort($row);
            $list["统计"]["regAgent"]  = "统计";
            $list["统计"]["agentName"] = "-";
            array_unshift($row, $list["统计"]);

            for ($i = 0; $i <= $max; $i++) {
                $info[]     = $arr[$i] ? $arr[$i] : 0;
                $info_bef[] = $arr_ber[$i] ? $arr_ber[$i] : 0;
            }
            if ($data['export'] == 1) {
                $col = array('regAgent' => '包号', 'agentName' => '游戏', 'count' => '统计', 'count_bef' => '昨日统计', '00' => '0时', '01' => '1时', '02' => '2时', '03' => '3时', '04' => '4时', '05' => '5时', '06' => '6时', '07' => '7时', '08' => '8时', '09' => '9时', '10' => '10时', '11' => '11时', '12' => '12时', '13' => '13时', '14' => '14时', '15' => '15时', '16' => '16时', '17' => '17时', '18' => '18时', '19' => '19时', '20' => '20时', '21' => '21时', '22' => '22时', '23' => '23时');
                array_unshift($row, $col);
                export_to_csv($row, '实时注册数据统计', $col);
                exit();
            }

            exit(json_encode(array("info" => $info, "yesterday" => $info_bef, "list" => array_values($row))));
        } else {
            if (IS_AJAX) {
                $response = $this->fetch();
                $this->ajaxReturn(array("status" => 1, "_html" => $response));
            } else {
                $this->display();
            }
        }
    }

    /**
     * 实时充值图表
     */
    public function payChart()
    {
        if (IS_POST) {
            $data         = I();
            $agent_info   = $_REQUEST["agent"];
            $agent_p_info = $_REQUEST["agent_p"];

            //处理搜索条件
            $agentArr = dealList($agent_info, $agent_p_info);

            /*if ($data["gameType"] || $data["advteruser_id"] || $data["creater"]) {
            $agent_map  = array();
            $data["creater"] && $agent_map["creater"] = $data["creater"];
            $data["gameType"] && $agent_map["gameType"] = $data["gameType"];
            $data["advteruser_id"] && $agent_map["advteruser_id"] = $data["advteruser_id"];
            $agent      = D("Admin")->commonQuery("agent", $agent_map, 0, null, "agent", "lg_");
            $agent_a[]  = "1";
            foreach ($agent as $a) {
            $agent_a[] = $a["agent"];
            }
            $map["_string"] = "agent IN ('".implode("','", $agent_a)."')";
            }

            if ($agentArr['agent']) {
            $map["agent"]    = array("in", $agentArr['agent']);
            } else {
            if ($agentArr['pAgent']) {

            $agent_p_arr       = array_keys(getDataList('agent','id',C('DB_PREFIX_API'),array('agent'=>array('IN',array_values($agentArr['pAgent'])))));

            $agent_arr         = array_values($agentArr['pAgent']);
            $agent_subarr      = array_keys(getDataList('agent','agent',C('DB_PREFIX_API'),array('pid'=>array('IN',$agent_p_arr))));
            if($agent_subarr){
            $agent_arr = array_merge($agent_arr,$agent_subarr);
            }
            $map["agent"]    = array("in", $agent_arr);
            } else {
            //权限控制
            $map["agent"]    = array("in", $this->agentArr);
            }
            }*/

            if ($agentArr['agent']) {
                $map['agent'] = array('in', $agentArr['agent']);
            } else {
                $agent_infos = $map_arr = array();

                if (!empty($agentArr['pAgent'])) {
                    $map_arr['_string'] = "id IN ('" . implode("','", $agentArr['pAgent']) . "') OR pid IN ('" . implode("','", $agentArr['pAgent']) . "')";
                }

                if ($data['advteruser_id']) {
                    $map_arr['advteruser_id'] = $data['advteruser_id'];
                }

                if ($data['creater']) {
                    $map_arr['creater'] = $data['creater'];
                }

                if ($data['gameType']) {
                    $map_arr['gameType'] = $data['gameType'];
                }

                if ($data['department']) {
                    $map_arr['departmentId'] = $data['department'];
                }

                if ($map_arr) {
                    $agent_infos = array_keys(getDataList('agent', 'agent', C('DB_PREFIX_API'), $map_arr));
                }

                $arr = $this->agentArr;
                if ($agent_infos) {
                    $arr = array_intersect($arr, $agent_infos);
                } elseif ($map_arr && !$agent_infos) {
                    exit(json_encode(array('rows' => array(), 'results' => 0)));
                }

                sort($arr);
                if (count($arr) < 1) {
                    $arr[] = '-1';
                }

                $map['agent'] = array('in', $arr);

            }

            if ($data['game_id']) {
                $map['game_id'] = $data['game_id'];
            }

            if ($data['serverId']) {
                $map['serverId'] = $data['serverId'];
            }

            $map['orderStatus'] = 0;
            $map['orderType']   = 0;

            $search = $map;
            if ($data['date']) {
                $map['createTime'] = array(array('egt', strtotime($data['date'])), array('lt', strtotime($data['date'] . '+1 day')), 'and');
                if ($data["date"] == date("Y-m-d")) {
                    $search["createTime"] = array(array("egt", strtotime($data["date"] . " -1 day")), array("lt", strtotime(date("Y-m-d H:i:s") . " -1 day")), "and");
                } else {
                    $search["createTime"] = array(array("egt", strtotime($data["date"] . " -1 day")), array("lt", strtotime($data["date"])), "and");
                }
            }

            $res      = D("Admin")->getHourPayCount($map);
            $bef      = D("Admin")->getHourPayCount($search);
            $max      = 0;
            $arr      = array();
            $arr_ber  = array();
            $info     = array();
            $info_bef = array();
            $list     = array();
            $row      = array();
            foreach ($res as $val) {
                $val["hour"] > $max && $max = $val["hour"];
                $arr[intval($val["hour"])] += intval($val["amount"]);
                $list[$val["agent"]]["agent"]      = $val["agent"];
                $list[$val["agent"]][$val["hour"]] = $val["amount"];
                $list[$val["agent"]]["count"] += intval($val["amount"]);
                $list["统计"][$val["hour"]] += $val["amount"];
                $list["统计"]["count"] += $val["amount"];
            }

            foreach ($bef as $val) {
                $arr_ber[intval($val["hour"])] += intval($val["amount"]);
                if (isset($list[$val["agent"]])) {
                    $list[$val["agent"]]["count_bef"] += intval($val["amount"]);
                }
                $list["统计"]["count_bef"] += intval($val["amount"]);
            }

            $n = 1;
            foreach ($list as $k => $v) {
                if ($k == "统计") {
                    continue;
                }

                $agent                                      = D("Admin/Admin")->commonQuery("agent", array("agent" => $v["agent"]), 0, 1, '*', 'lg_');
                $row[$v["count"] * 10000 + $n]              = $v;
                $row[$v["count"] * 10000 + $n]["agentName"] = $agent["agentName"] ? $agent["agentName"] : "-";
                $n++;
            }

            krsort($row);
            $list["统计"]["agent"]     = "统计";
            $list["统计"]["agentName"] = "-";
            array_unshift($row, $list["统计"]);

            for ($i = 0; $i <= $max; $i++) {
                $info[]     = $arr[$i] ? $arr[$i] : 0;
                $info_bef[] = $arr_ber[$i] ? $arr_ber[$i] : 0;
            }

            if ($data['export'] == 1) {
                $col = array('agent' => '包号', 'agentName' => '游戏', 'count' => '统计', 'count_bef' => '昨日统计', '00' => '0时', '01' => '1时', '02' => '2时', '03' => '3时', '04' => '4时', '05' => '5时', '06' => '6时', '07' => '7时', '08' => '8时', '09' => '9时', '10' => '10时', '11' => '11时', '12' => '12时', '13' => '13时', '14' => '14时', '15' => '15时', '16' => '16时', '17' => '17时', '18' => '18时', '19' => '19时', '20' => '20时', '21' => '21时', '22' => '22时', '23' => '23时');
                array_unshift($row, $col);
                export_to_csv($row, '实时充值数据统计', $col);
                exit();
            }
            exit(json_encode(array("info" => $info, "yesterday" => $info_bef, "list" => array_values($row))));
        } else {
            if (IS_AJAX) {
                $response = $this->fetch();
                $this->ajaxReturn(array('status' => 1, '_html' => $response));
            } else {
                $this->display();
            }
        }
    }

    /**
     * 实时图表
     */
    public function realtimeChart()
    {
        if (IS_POST) {
            $data         = I();
            $agent_info   = $_REQUEST["agent"];
            $agent_p_info = $_REQUEST["agent_p"];

            $join = null;
            $sum  = false;
            if ($data["showType"] == 1) {
                $table    = "login";
                $info     = "userCode";
                $time     = "time";
                $agent    = "regAgent";
                $distinct = true;
                $prefix   = C("DB_PREFIX_LOG");
            } elseif ($data["showType"] == 2) {
                $table    = "login";
                $info     = "udid";
                $time     = "time";
                $agent    = "regAgent";
                $distinct = true;
                $prefix   = C("DB_PREFIX_LOG");
            } elseif ($data["showType"] == 3) {
                $table    = "device_game";
                $info     = "1";
                $time     = "createTime";
                $agent    = "agent";
                $distinct = false;
                $prefix   = C("DB_PREFIX_API");
            } elseif ($data["showType"] == 4) {
                $table            = "device_game";
                $info             = "1";
                $time             = "createTime";
                $agent            = "agent";
                $distinct         = false;
                $prefix           = C("DB_PREFIX_API");
                $map["lastLogin"] = array("EXP", 'IS NOT NULL');
            } elseif ($data["showType"] == 5) {
                $table               = "role";
                $info                = "a.udid";
                $time                = "a.createTime";
                $agent               = "b.agent";
                $distinct            = true;
                $prefix              = C("DB_PREFIX_API");
                $join                = "LEFT JOIN lg_device_game b ON a.udid = b.udid";
                $map["b.createTime"] = array(array("egt", strtotime($data["date"])), array("lt", strtotime($data["date"] . "+1 day")), "and");
            } elseif ($data["showType"] == 6) {
                $table              = "order";
                $info               = "userCode";
                $time               = "paymentTime";
                $agent              = "agent";
                $distinct           = true;
                $prefix             = C("DB_PREFIX_API");
                $map["orderType"]   = 0;
                $map["orderStatus"] = 0;
            } elseif ($data["showType"] == 7) {
                $table              = "order";
                $info               = "amount";
                $time               = "paymentTime";
                $agent              = "agent";
                $distinct           = false;
                $sum                = true;
                $prefix             = C("DB_PREFIX_API");
                $map["orderType"]   = 0;
                $map["orderStatus"] = 0;
            } elseif ($data["showType"] == 8) {
                $table              = "order";
                $info               = "1";
                $time               = "paymentTime";
                $agent              = "agent";
                $distinct           = false;
                $prefix             = C("DB_PREFIX_API");
                $map["orderType"]   = 0;
                $map["orderStatus"] = 0;
            } elseif ($data["showType"] == 9) {
                $table    = "fall_open_log";
                $info     = "requestIp";
                $time     = "createTime";
                $agent    = "agent";
                $distinct = true;
                $prefix   = C("DB_PREFIX");
            } elseif ($data["showType"] == 10) {
                $table    = "fall_download_log";
                $info     = "requestIp";
                $time     = "createTime";
                $agent    = "agent";
                $distinct = true;
                $prefix   = C("DB_PREFIX");
            }

            //处理搜索条件
            $agentArr = dealList($agent_info, $agent_p_info);

            /*if ($data["gameType"] || $data["advteruser_id"] || $data["creater"]) {
            $agent_map  = array();
            $data["creater"] && $agent_map["creater"] = $data["creater"];
            $data["gameType"] && $agent_map["gameType"] = $data["gameType"];
            $data["advteruser_id"] && $agent_map["advteruser_id"] = $data["advteruser_id"];
            $agent_arr  = D("Admin")->commonQuery("agent", $agent_map, 0, null, "agent", "lg_");
            $agent_a[]  = "1";
            foreach ($agent_arr as $a) {
            $agent_a[]  = $a["agent"];
            }
            $map["_string"] = $agent." IN ('".implode("','", $agent_a)."')";
            unset($agent_arr);
            }

            if ($agentArr['agent']) {
            $map["regAgent"]    = array("in", $agentArr['agent']);
            } else {
            if ($agentArr['pAgent']) {

            $agent_p_arr       = array_keys(getDataList('agent','id',C('DB_PREFIX_API'),array('agent'=>array('IN',array_values($agentArr['pAgent'])))));

            $agent_arr         = array_values($agentArr['pAgent']);
            $agent_subarr      = array_keys(getDataList('agent','agent',C('DB_PREFIX_API'),array('pid'=>array('IN',$agent_p_arr))));
            if($agent_subarr){
            $agent_arr = array_merge($agent_arr,$agent_subarr);
            }
            $map[$agent]    = array("in", $agent_arr);
            } else {
            //权限控制
            $map[$agent]    = array("in", $this->agentArr);
            }
            }*/

            if ($agentArr['agent']) {
                $map['agent'] = array('in', $agentArr['agent']);
            } else {
                $agent_infos = $map_arr = array();

                if (!empty($agentArr['pAgent'])) {
                    $map_arr['_string'] = "id IN ('" . implode("','", $agentArr['pAgent']) . "') OR pid IN ('" . implode("','", $agentArr['pAgent']) . "')";
                }

                if ($data['advteruser_id']) {
                    $map_arr['advteruser_id'] = $data['advteruser_id'];
                }

                if ($data['creater']) {
                    $map_arr['creater'] = $data['creater'];
                }

                if ($data['gameType']) {
                    $map_arr['gameType'] = $data['gameType'];
                }

                if ($data['department']) {
                    $map_arr['departmentId'] = $data['department'];
                }

                if ($map_arr) {
                    $agent_infos = array_keys(getDataList('agent', 'agent', C('DB_PREFIX_API'), $map_arr));
                }

                $arr = $this->agentArr;
                if ($agent_infos) {
                    $arr = array_intersect($arr, $agent_infos);
                } elseif ($map_arr && !$agent_infos) {
                    exit(json_encode(array('rows' => array(), 'results' => 0)));
                }

                sort($arr);
                if (count($arr) < 1) {
                    $arr[] = '-1';
                }

                $map['agent'] = array('in', $arr);

            }

            if ($data["game_id"]) {
                if ($data["showType"] == 5) {
                    $map["a.game_id"] = $data["game_id"];
                } else {
                    $map["game_id"] = $data["game_id"];
                }
            }

            if ($data["date"]) {
                $map[$time] = array(array("egt", strtotime($data["date"])), array("lt", strtotime($data["date"] . "+1 day")), "and");
            }

            $res  = D("Admin")->getHourCount($table, $info, $map, $time, $agent, $distinct, $prefix, $join, $sum);
            $arr  = array();
            $max  = 0;
            $info = array();
            $list = array();
            $row  = array();
            foreach ($res as $val) {
                $val["hour"] > $max && $max = $val["hour"];
                $arr[intval($val["hour"])] += intval($val["num"]);
                $list[$val["agent"]]["agent"]      = $val["agent"];
                $list[$val["agent"]][$val["hour"]] = $val["num"];
                $list[$val["agent"]]["count"] += intval($val["num"]);
                $list["统计"][$val["hour"]] += $val["num"];
                $list["统计"]["count"] += $val["num"];
            }

            $n = 1;
            foreach ($list as $k => $v) {
                if ($k == "统计") {
                    continue;
                }

                $agent                                      = D("Admin/Admin")->commonQuery("agent", array("agent" => $v["agent"]), 0, 1, "*", "lg_");
                $row[$v["count"] * 10000 + $n]              = $v;
                $row[$v["count"] * 10000 + $n]["agentName"] = $agent["agentName"] ? $agent["agentName"] : "-";
                $n++;
            }

            krsort($row);
            $list["统计"]["agent"]     = "统计";
            $list["统计"]["agentName"] = "-";
            $row["统计"]               = $list["统计"];

            for ($i = 0; $i <= $max; $i++) {
                $info[] = $arr[$i] ? $arr[$i] : 0;
            }
            exit(json_encode(array("info" => $info, "list" => array_values($row))));
        } else {
            if (IS_AJAX) {
                $response = $this->fetch();
                $this->ajaxReturn(array("status" => 1, "_html" => $response));
            } else {
                $this->display();
            }
        }
    }

    /**
     * 充值排行统计
     */
    public function payRange()
    {
        if (IS_POST) {
            $data = I();
            !$data['game_id'] && exit(json_encode(array('rows' => array(), 'results' => 0)));
            $agent_info   = $_REQUEST["agent"];
            $agent_p_info = $_REQUEST["agent_p"];
            $start        = $data["start"] ? $data["start"] : 0;
            $pageSize     = $data["limit"] ? $data["limit"] : 30;

            //处理搜索条件
            $agentArr = dealList($agent_info, $agent_p_info);

            $where = '1';

            if ($agentArr['agent']) {
                $where .= ' AND a.agent IN("' . implode('","', $agentArr['agent']) . '")';
            } else {
                if ($agentArr['pAgent']) {
                    $agent_p_arr = array_keys(getDataList('agent', 'id', C('DB_PREFIX_API'), array('agent' => array('IN', array_values($agentArr['pAgent'])))));

                    $agent_arr    = array_values($agentArr['pAgent']);
                    $agent_subarr = array_keys(getDataList('agent', 'agent', C('DB_PREFIX_API'), array('pid' => array('IN', $agent_p_arr))));
                    if ($agent_subarr) {
                        $agent_arr = array_merge($agent_arr, $agent_subarr);
                    }
                    $where .= ' AND a.agent IN("' . implode('","', $agent_arr) . '")';
                } else {
                    //权限控制
                    $where .= ' AND a.agent IN("' . implode('","', $this->agentArr) . '")';
                }
            }

            if ($data['game_id']) {
                $where .= ' and a.game_id=' . $data['game_id'];
            }

            if ($data['startDate'] && $data['endDate']) {
                $where .= ' and a.dayTime>="' . $data['startDate'] . '" and a.dayTime<"' . date('Y-m-d', strtotime($data['endDate'] . '+1 day')) . '"';
            }

            if ($data['userName']) {
                $where .= ' and a.userName="' . $data['userName'] . '"';
            }

            if ($data['userCode']) {
                $where .= ' and a.userCode="' . $data['userCode'] . '"';
            }

            if ($data['serverId']) {
                $where .= ' and a.serverId="' . $data['serverId'] . '"';
            }

            if ($data['min']) {
                $having  = 'amount > ' . $data['min'];
                $having2 = 'totalPay > ' . $data['min'];
            } else {
                $having  = 'amount > 0';
                $having2 = 'totalPay > 0';
            }

            if ($data['max']) {
                $having .= ' and amount < ' . $data['max'];
                $having2 .= ' and totalPay < ' . $data['max'];
            }

            if ($data['lastPayRoleName']) {
                $where .= ' and b.lastPayRoleName LIKE "%' . $data['lastPayRoleName'] . '%"';
            }
            $game_list = getDataList('game', 'id', C('DB_PREFIX_API'));
            $res       = D('Admin')->getPayRangeData($start, $pageSize, $where, $having, $having2); //充值排行数据
            $results   = $res['count']['total']; //总行数
            $totalPay  = $res['count']['totalPay']; //总行数
            foreach ($res['list'] as $key => $val) {
                $res['list'][$key]['range']        = $start + ($key + 1);
                $res['list'][$key]['gameName']     = $game_list[$val['game_id']]['gameName'];
                $res['list'][$key]['totalPay']     = floatval($val['totalPay']);
                $res['list'][$key]['totalBalance'] = floatval($val['totalBalance']);
                $res['list'][$key]['ratio']        = round(floatval($val['totalPay']) / $totalPay * 100, 2) . "%";
                $res['list'][$key]['noLogin']      = round((strtotime(date("Y-m-d")) - strtotime(date("Y-m-d", $val["lastLogin"]))) / 86400);
                $res['list'][$key]['noPay']        = round((time() - strtotime($val["lastPay"])) / 86400);
            }
            $rows = $res['list'];

            //数据汇总
            $pageSummary = array('totalPay' => sprintf("%.2f", array_sum(array_column($res['list'], 'totalPay'))));
            if ($data['export'] == 1) {

                $col = array('range' => '排名', 'gameName' => '游戏名称', 'agent' => '渠道号', /*'city'=>'注册城市',*/'userCode' => '用户标识符', 'userName' => '充值账号', 'totalPay' => '充值金额', 'totalBalance' => '充入游戏币', 'createTime' => '账号创建时间', 'lastPayRoleName' => '最后充值角色名', 'lastPayServerName' => '最后充值服务器名', 'lastPay' => '最后充值时间', 'noLogin' => '离线天数', 'noPay' => '未充值天数');
                array_unshift($rows, $col);
                $pageSummary['gameName'] = '汇总';
                array_push($rows, $pageSummary);
                export_to_csv($rows, '充值排行', $col);
                exit();
            }
            $arr = array('rows' => $rows ? $rows : array(), 'results' => $results, 'summary' => array('totalPay' => number_format($totalPay, 2)));
            unset($rows, $remainArr);
            exit(json_encode($arr));
        } else {
            if (IS_AJAX) {
                $response = $this->fetch();
                $this->ajaxReturn(array('status' => 1, '_html' => $response));
            } else {
                $this->display();
            }
        }
    }

    /**
     * 充值地区分布
     */
    public function areaPay()
    {
        if (IS_POST) {
            $data = I();
            !$data['game_id'] && exit(json_encode(array('rows' => array(), 'results' => 0)));
            $agent_info   = $_REQUEST["agent"];
            $agent_p_info = $_REQUEST["agent_p"];
            $start        = $data["start"] ? $data["start"] : 0;
            $pageSize     = $data["limit"] ? $data["limit"] : 30;

            //处理搜索条件
            $agentArr = dealList($agent_info, $agent_p_info);

            $where = '1';
            if ($agentArr['agent']) {
                $where .= ' and agent in("' . implode('","', $agentArr['agent']) . '")';
            } else {
                if ($agentArr['pAgent']) {
                    $agent_p_arr = array_keys(getDataList('agent', 'id', C('DB_PREFIX_API'), array('agent' => array('IN', array_values($agentArr['pAgent'])))));

                    $agent_arr    = array_values($agentArr['pAgent']);
                    $agent_subarr = array_keys(getDataList('agent', 'agent', C('DB_PREFIX_API'), array('pid' => array('IN', $agent_p_arr))));
                    if ($agent_subarr) {
                        $agent_arr = array_merge($agent_arr, $agent_subarr);
                    }
                    $where .= ' and agent in("' . implode('","', $agent_arr) . '")';
                } else {
                    //权限控制
                    $where .= ' and agent in("' . implode('","', $this->agentArr) . '")';
                }
            }

            if ($data['game_id']) {
                $where .= ' and game_id=' . $data['game_id'];
            }

            if ($data['startDate'] && $data['endDate']) {
                $where .= ' and dayTime>="' . $data['startDate'] . '" and dayTime<"' . date('Y-m-d', strtotime($data['endDate'] . '+1 day')) . '"';
            }

            if ($data['serverId']) {
                $where .= ' and serverId="' . $data['serverId'] . '"';
            }
            $game_list = getDataList('game', 'id', C('DB_PREFIX_API'));
            $res       = D('Admin')->getAreaPayData($start, $pageSize, $where); //充值地区分布
            $allPay    = $res['count'];
            foreach ($res['list'] as $key => $val) {
                $data['lookType'] == 1 && $res['list'][$key]['dayTime'] = '-';
                $res['list'][$key]['city']                              = '-';

                $res['list'][$key]['Rate'] = numFormat(($val['amount'] / $allPay['provincePay']), true);

                //城市
                foreach ($res['list'][$key]['children'] as $k => $v) {
                    $data['lookType'] == 1 && $res['list'][$key]['children'][$k]['dayTime'] = '-';
                    $res['list'][$key]['children'][$k]['Rate']                              = numFormat(($v['amount'] / $allPay['provincePay']), true);
                    if ($data['export'] == 1) {
                        $exportArr[] = $res['list'][$key]['children'][$k];
                    }
                }
            }
            $res['list'][] = array('province' => '汇总', 'amount' => $allPay['provincePay']);
            $rows          = $res['list'];
            unset($res);

            if ($data['export'] == 1) {
                $col = array('dayTime' => '日期', 'province' => '省份', 'city' => '城市', 'amount' => '充值金额', 'Rate' => '占比');
                array_unshift($exportArr, $col);
                export_to_csv($exportArr, '充值地区分布', $col);
                exit();
            }
            $arr = array('rows' => $rows ? $rows : array(), 'results' => $results, 'pageSummary' => $allPay);
            unset($rows, $remainArr);
            exit(json_encode($arr));
        } else {
            if (IS_AJAX) {
                $response = $this->fetch();
                $this->ajaxReturn(array('status' => 1, '_html' => $response));
            } else {
                $this->display();
            }
        }
    }

    /**
     * 注册地区分布
     */
    public function areaRegister()
    {
        if (IS_POST) {
            $data = I();
            !$data['game_id'] && exit(json_encode(array('rows' => array(), 'results' => 0)));
            $agent_info   = $_REQUEST["agent"];
            $agent_p_info = $_REQUEST["agent_p"];
            $where        = '1';
            //处理搜索条件
            $agentArr = dealList($agent_info, $agent_p_info);
            if ($agentArr['agent']) {
                $where .= ' and agent in("' . implode('","', $agentArr['agent']) . '")';
            } else {
                $agent_infos = $map_arr = array();

                if (!empty($agentArr['pAgent'])) {
                    $map_arr['_string'] = "id IN ('" . implode("','", $agentArr['pAgent']) . "') OR pid IN ('" . implode("','", $agentArr['pAgent']) . "')";
                }

                if ($data['advteruser_id']) {
                    $map_arr['advteruser_id'] = $data['advteruser_id'];
                }

                if ($data['game_id']) {
                    $map_arr['game_id'] = $data['game_id'];
                }
                if ($map_arr) {
                    $agent_infos = array_keys(getDataList('agent', 'agent', C('DB_PREFIX_API'), $map_arr));
                }

                $arr = $this->agentArr;
                if ($agent_infos) {
                    $arr = array_intersect($arr, $agent_infos);
                } elseif ($map_arr && !$agent_infos) {
                    $arr = array();
                }

                sort($arr);
                if (count($arr) < 1) {
                    $arr[] = '-1';
                }

                $where .= ' and agent in("' . implode('","', $arr) . '")';
            }

            if ($data["startDate"] && $data["endDate"]) {
                $where .= ' AND dayTime >= "' . $data["startDate"] . '" AND dayTime < "' . date("Y-m-d", strtotime($data["endDate"] . "+1 day")) . '"';
            }

            $res         = D("Admin")->getAreaRegisterData($where, $data["lookType"]); //充值地区分布
            $allRegister = $res["count"];
            foreach ($res["list"] as $key => $val) {
                $data["lookType"] == 1 && $res["list"][$key]["dayTime"] = "-";
                $res["list"][$key]["city"]                              = "-";
                $res["list"][$key]["Rate"]                              = numFormat(($val["register"] / $allRegister["provinceRegister"]), true);

                //城市
                foreach ($res["list"][$key]["children"] as $k => $v) {
                    $data["lookType"] == 1 && $res["list"][$key]["children"][$k]["dayTime"] = "-";
                    $res["list"][$key]["children"][$k]["Rate"]                              = numFormat(($v["register"] / $allRegister["provinceRegister"]), true);
                    if ($data["export"] == 1) {
                        $exportArr[] = $res["list"][$key]["children"][$k];
                    }
                }
            }
            $res["list"][] = array("province" => "汇总", "city" => "-", "register" => $allRegister["provinceRegister"], "Rate" => "100%");
            $rows          = $res["list"];
            unset($res);

            if ($data["export"] == 1) {
                $col = array("dayTime" => "日期", "province" => "省份", "city" => "城市", "register" => "人数", "Rate" => "占比");
                array_unshift($exportArr, $col);
                export_to_csv($exportArr, "注册地区分布", $col);
                exit();
            }
            $arr = array("rows" => $rows ? $rows : array(), "pageSummary" => $allRegister);
            unset($rows, $remainArr);
            exit(json_encode($arr));
        } else {
            if (IS_AJAX) {
                $response = $this->fetch();
                $this->ajaxReturn(array("status" => 1, "_html" => $response));
            } else {
                $this->display();
            }
        }
    }

    /**
     * 等级流失率统计
     */
    public function levelLoss()
    {
        if (IS_POST) {
            $data = I();
            !$data['game_id'] && exit(json_encode(array('rows' => array(), 'results' => 0)));
            $agent_info   = $_REQUEST["agent"];
            $agent_p_info = $_REQUEST["agent_p"];
            $start        = $data["start"] ? $data["start"] : 0;
            $pageSize     = $data["limit"] ? $data["limit"] : 30;

            //处理搜索条件
            $agentArr = dealList($agent_info, $agent_p_info);

            $where = '1';

            if ($agentArr['agent']) {
                $where .= ' AND a.agent IN("' . implode('","', $agentArr['agent']) . '")';
            } else {
                if ($agentArr['pAgent']) {
                    $agent_p_arr = array_keys(getDataList('agent', 'id', C('DB_PREFIX_API'), array('agent' => array('IN', array_values($agentArr['pAgent'])))));

                    $agent_arr    = array_values($agentArr['pAgent']);
                    $agent_subarr = array_keys(getDataList('agent', 'agent', C('DB_PREFIX_API'), array('pid' => array('IN', $agent_p_arr))));
                    if ($agent_subarr) {
                        $agent_arr = array_merge($agent_arr, $agent_subarr);
                    }
                    $where .= ' AND a.agent IN("' . implode('","', $agent_arr) . '")';
                } else {
                    //权限控制
                    $where .= ' AND a.agent IN("' . implode('","', $this->agentArr) . '")';
                }
            }
            // $data = I();
            // $agent_info = $_REQUEST["agent"];
            // !$data['game_id'] && exit(json_encode(array('rows'=>array(), 'results'=>0)));

            // //处理搜索条件
            // if(is_array($agent_info) && in_array('--请选择渠道号--', $agent_info)){
            //     unset($agent_info[array_search('--请选择渠道号--', $agent_info)]);
            // }elseif(is_string($agent_info) && $agent_info == '--请选择渠道号--'){
            //     $agent_info = '';
            // }elseif(is_string($agent_info) && !empty($agent_info)){
            //     $agent_info = explode(',', $agent_info);
            // }

            // $start      = $data['start']? $data['start']: 0;
            // $pageSize   = $data['limit']? $data['limit']: 500;
            // $where = '1';
            // if($agent_info){
            //     $data['agent'] = $agent_info;
            //     $where .= ' and a.agent in("'.implode('","',$data['agent']).'")';
            // }else{
            //     //权限控制
            //     $where .= ' and a.agent in("'.implode('","', $this->agentArr).'")';
            // }

            if ($data['game_id']) {
                $where .= ' and a.game_id=' . $data['game_id'];
            }

            if ($data['startDate'] && $data['endDate']) {
                $where .= ' and a.createTime>=' . strtotime($data['startDate']) . ' and a.createTime<' . strtotime(date('Y-m-d', strtotime($data['endDate'] . '+1 day')));
            }

            if ($data['serverId']) {
                $where .= ' and a.serverId="' . $data['serverId'] . '"';
            }
            $game_list = getDataList('game', 'id', C('DB_PREFIX_API'));
            $res       = D('Admin')->getLevelLossData($start, $pageSize, $where); //等级流失分布
            $results   = $res['count']['total'];
            $day3      = $day7      = $day3Num      = $day7Num      = 0;
            foreach ($res['list'] as $key => $val) {
                $res['list'][$key]['level']    = intval($val['level']);
                $res['list'][$key]['day3']     = intval($val['day3']);
                $res['list'][$key]['day7']     = intval($val['day7']);
                $res['list'][$key]['day3Rate'] = numFormat(($val['day3'] / $res['count']['day3']), true);
                $res['list'][$key]['day7Rate'] = numFormat(($val['day7'] / $res['count']['day7']), true);
                $day3 += $res['list'][$key]['day3Rate'];
                $day7 += $res['list'][$key]['day7Rate'];
                if ($res['list'][$key]['day3Rate'] + 0 > 0) {
                    $day3Num++;
                }

                if ($res['list'][$key]['day7Rate'] + 0 > 0) {
                    $day7Num++;
                }

            }
            $rows = $res['list'];
            unset($res);

            $pageSummary = array('day3' => array_sum(array_column($rows, 'day3')), 'day7' => array_sum(array_column($rows, 'day7')), 'day3Rate' => numFormat(($day3 / $day3Num), false) . '%', 'day7Rate' => numFormat(($day7 / $day7Num), false) . '%');

            if ($data['export'] == 1) {
                $col = array('level' => '等级', 'day3' => '流失用户（三天未登录）', 'day3Rate' => '占比', 'day7' => '流失用户（七天未登录）', 'day7Rate' => '占比');
                array_unshift($rows, $col);
                $pageSummary['level'] = '汇总';
                array_push($rows, $pageSummary);
                export_to_csv($rows, '等级流失分布', $col);
                exit();
            }
            $arr = array('rows' => $rows ? $rows : array(), 'results' => $results, 'pageSummary' => $pageSummary);
            unset($rows, $remainArr);
            exit(json_encode($arr));
        } else {
            if (IS_AJAX) {
                $response = $this->fetch();
                $this->ajaxReturn(array('status' => 1, '_html' => $response));
            } else {
                $this->display();
            }
        }
    }

    /**
     * 部门日报数据
     */
    public function departmentDayReport()
    {
        if (IS_POST) {
            $data = I();

            $start    = $data['start'] ? $data['start'] : 0;
            $pageSize = $data['limit'] ? $data['limit'] : 500;

            $where = '1';
            $data['os'] && $where .= " AND os = {$data['os']}";
            if ($data['departmentId']) {
                $where .= " AND department = {$data['departmentId']}";
            } else {
                $where .= " AND department <> 0 AND department <> 3";
            }
            if ($data['startDate'] && $data['endDate']) {
                $where .= " AND dayTime >= '{$data['startDate']}' AND dayTime < '" . date('Y-m-d', strtotime($data['endDate'] . ' + 1 day')) . "'";
            }

            $res        = D('Admin')->getDepartmentDayReportData($start, $pageSize, $where); //部门日报数据
            $results    = $res['count']['total'];
            $department = array(1 => '发行一部', 2 => '发行二部');
            foreach ($res['list'] as $key => $val) {
                $res['list'][$key]['allPay'] = round($val['allPay'], 2);
                if ($data['export'] == 1) {
                    $res['list'][$key]['payRate'] = round(($val['allPayUser'] / $val['actUser']) * 100, 2) . '%'; //部门充值帐户数/部门活跃帐户数
                } else {
                    $res['list'][$key]['payRate'] = round(($val['allPayUser'] / $val['actUser']) * 100, 2); //部门充值帐户数/部门活跃帐户数
                }
            }
            $rows = $res['list'];
            unset($res);

            $pageSummary = array('newDevice' => array_sum(array_column($rows, 'newDevice')), 'newUser' => array_sum(array_column($rows, 'newUser')), 'actUser' => array_sum(array_column($rows, 'actUser')), 'allPay' => array_sum(array_column($rows, 'allPay')), 'allPayUser' => array_sum(array_column($rows, 'allPayUser')));

            if ($data['export'] == 1) {
                $col = array('dayTime' => '日期', 'newDevice' => '新增设备数', 'newUser' => '新增账户数', 'actUser' => '活跃账户数', 'allPay' => '充值金额', 'allPayUser' => '充值账户数', 'payRate' => '付费率');
                array_unshift($rows, $col);
                $pageSummary['dayTime'] = '汇总';
                array_push($rows, $pageSummary);
                export_to_csv($rows, '部门日报', $col);
                exit();
            }
            $arr = array('rows' => $rows ? $rows : array(), 'results' => $results, 'pageSummary' => $pageSummary);
            unset($rows, $remainArr);
            exit(json_encode($arr));
        } else {
            if (IS_AJAX) {
                $response = $this->fetch();
                $this->ajaxReturn(array('status' => 1, '_html' => $response));
            } else {
                $this->display();
            }
        }
    }

    /**
     * 充值走势
     */
    public function payDiagram()
    {
        if (IS_AJAX) {
            $data         = I();
            $agent_info   = $_REQUEST["agent"];
            $agent_p_info = $_REQUEST["agent_p"];

            //处理搜索条件
            $agentArr = dealList($agent_info, $agent_p_info);
            if ($agentArr['agent']) {
                $data['agent'] = $agentArr['agent'];
                $map['agent']  = array('in', $data['agent']);
            } else {
                if ($agentArr['pAgent']) {
                    $agent_p_arr = array_keys(getDataList('agent', 'id', C('DB_PREFIX_API'), array('agent' => array('IN', array_values($agentArr['pAgent'])))));

                    $agent_arr    = array_values($agentArr['pAgent']);
                    $agent_subarr = array_keys(getDataList('agent', 'agent', C('DB_PREFIX_API'), array('pid' => array('IN', $agent_p_arr))));
                    if ($agent_subarr) {
                        $agent_arr = array_merge($agent_arr, $agent_subarr);
                    }

                    $data['agent'] = $agent_arr;
                    $map['agent']  = array('in', $data['agent']);
                } else {
                    //权限控制
                    $map['agent'] = array('in', $this->agentArr);
                }
            }

            if ($data['game_id']) {
                $map['game_id'] = $data['game_id'];
            }
            if ($data['startDate'] && $data['endDate']) {
                $map['dayTime'] = array("BETWEEN", array($data['startDate'], $data['endDate']));
            } elseif ($data['startDate']) {
                $map['dayTime'] = array("EGT", $data['startDate']);
            } elseif ($data['endDate']) {
                $map['dayTime'] = array("ELT", $data['endDate']);
            }

            $info = D("Admin")->getCountAmountGroupByDay($map);
            $arr  = array();
            $min  = isset($data["startDate"]) ? $data["startDate"] : 0;
            $max  = isset($data["endDate"]) ? $data["endDate"] : 0;
            foreach ($info as $v) {
                $arr[$v["day"]] = $v["amount"];
                if ($min == 0) {
                    $min = $v["day"];
                } else {
                    $min = ($min > $v["day"]) ? $v["day"] : $min;
                }
                if ($max == 0) {
                    $max = $v["day"];
                } else {
                    $max = ($max < $v["day"]) ? $v["day"] : $max;
                }
            }
            $list = array();
            for (; $min <= $max; $min = date("Y-m-d", strtotime($min . " +1day"))) {
                if (!isset($arr[$min])) {
                    $list[] = array("day" => date("m/d", strtotime($min)), "amount" => 0);
                } else {
                    $list[] = array("day" => date("m/d", strtotime($min)), "amount" => $arr[$min]);
                }
            }
            echo json_encode($list);
            exit;
        } else {
            $this->display();
        }
    }

    /**
     * 老用户统计
     */
    public function oldUserTable()
    {
        if (IS_AJAX) {
            $data         = I();
            $agent_info   = $_REQUEST["agent"];
            $agent_p_info = $_REQUEST["agent_p"];
            $start        = $data["start"] ? $data["start"] : 0;
            $pageSize     = $data["limit"] ? $data["limit"] : 30;

            //处理搜索条件
            if (is_array($agent_info) && in_array("--请选择子包--", $agent_info)) {
                unset($agent_info[array_search("--请选择子包--", $agent_info)]);
            } elseif (is_string($agent_info) && $agent_info == "--请选择子包--") {
                $agent_info = "";
            }
            if (is_array($agent_p_info) && in_array("--请选择母包--", $agent_p_info)) {
                unset($agent_p_info[array_search("--请选择母包--", $agent_p_info)]);
            } elseif (is_string($agent_p_info) && $agent_p_info == "--请选择母包--") {
                $agent_p_info = "";
            }

            if ($agent_info) {
                $map["a.agent"] = array("IN", array_values($agent_info));
            } else {
                if ($agent_p_info) {
                    $agent_p_list = D("Admin")->commonQuery("agent", array("agent" => array("IN", array_values($agent_p_info))), 0, 99999999, "id", "lg_");
                    $agent_p_arr  = array();
                    foreach ($agent_p_list as $value) {
                        $agent_p_arr[] = $value["id"];
                    }
                    $agent_list = D("Admin")->commonQuery("agent", array("pid" => array("IN", $agent_p_arr)), 0, 99999999, "agent", "lg_");
                    $agent_arr  = array_values($agent_p_info);
                    foreach ($agent_list as $value) {
                        $agent_arr[] = $value["agent"];
                    }
                    $map["a.agent"] = array("IN", $agent_arr ? $agent_arr : "0");
                } else {
                    //权限控制
                    $map["a.agent"] = array("IN", $this->agentArr);
                }
            }
            if ($data["game_id"]) {
                $map["a.gameId"] = $data["game_id"];
            }

            if ($data['serverId']) {
                $serverId = $data['serverId'];
                if (is_array($serverId) && in_array("--全部--", $serverId)) {
                    unset($serverId[array_search("--全部--", $serverId)]);
                } elseif (is_string($serverId) && $serverId == "--全部--") {
                    $serverId = "";
                } elseif (is_string($serverId) && !empty($serverId)) {
                    $serverId = explode(',', $serverId);
                    if (in_array("--全部--", $serverId)) {
                        unset($serverId[array_search("--全部--", $serverId)]);
                    }
                }
                if (!empty($serverId)) {
                    $map['a.serverId'] = array('IN', $serverId);
                }
            }

            if ($data["startDate"] && $data["endDate"]) {
                $map["a.dayTime"] = array("BETWEEN", array($data["startDate"], $data["endDate"]));
            } elseif ($data["startDate"]) {
                $map["a.dayTime"] = array("EGT", $data["startDate"]);
            } elseif ($data["endDate"]) {
                $map["a.dayTime"] = array("ELT", $data["endDate"]);
            }

            $count = D("Admin")->getOldUserTableCount($map);
            $info  = D("Admin")->getOldUserTable($map, $start, $pageSize);

            if ($map['a.gameId']) {
                $map['a.game_id'] = $map['a.gameId'];
                unset($map['a.gameId']);
            }

            $payServer = M('sp_agent_server_pay_day a', C('DB_PREFIX'), 'CySlave')->field('SUM(allPay) AS allPay,SUM(allPayUser) AS allPayUser,SUM(newPay) AS newPay,SUM(newPayUser) AS newPayUser,game_id,dayTime')->where($map)->group('game_id,dayTime')->select();

            $game = getDataList("game", "id", C("DB_PREFIX_API"));
            $sum  = array("oldLogin" => 0, "oldPay" => 0, "oldPayAmount" => 0);
            foreach ($info as $k => $v) {
                if ($payServer) {
                    foreach ($payServer as $key => $val) {
                        if ($v['gameId'] == $val['game_id'] && $v['day'] == $val['dayTime']) {
                            $v['allPay'] += floatval($val['allPay']);
                            $v['allPayUser'] += $val['allPayUser'];
                            $v['newPay'] += floatval($val['newPay']);
                            $v['newPayUser'] += $val['newPayUser'];
                        }
                    }
                }

                $oldPay       = $v["allPayUser"] - $v["newPayUser"];
                $oldPayAmount = $v["allPay"] - $v["newPay"];
                if (!$v["oldLogin"] && !$oldPay && !$oldPayAmount) {
                    unset($info[$k]);
                    continue;
                }
                $info[$k]["gameName"]     = $game[$v["gameId"]]["gameName"];
                $info[$k]["oldPay"]       = $oldPay;
                $info[$k]["oldPayAmount"] = $oldPayAmount;
                $sum["oldLogin"] += $v["oldLogin"];
                $sum["oldPay"] += $oldPay;
                $sum["oldPayAmount"] += $oldPayAmount;
                $info[$k]["oldPayRatio"] = round($oldPay / $v["oldLogin"] * 100, 2) . "%";
                $info[$k]["oldARPU"]     = round($oldPayAmount / $v["oldLogin"], 2);
                $info[$k]["oldARPPU"]    = round($oldPayAmount / $oldPay, 2);
            }
            $sum["oldPayRatio"] = round($sum["oldPay"] / $sum["oldLogin"] * 100, 2) . "%";
            $sum["oldARPU"]     = round($sum["oldPayAmount"] / $sum["oldLogin"], 2);
            $sum["oldARPPU"]    = round($sum["oldPayAmount"] / $sum["oldPay"], 2);
            $arr                = array("rows" => $info ? $info : array(), "results" => $count, "pageSummary" => $sum);
            exit(json_encode($arr));
        } else {
            $this->display();
        }
    }

    /**
     * 付费衰减
     */
    public function payRemain()
    {
        if (IS_POST) {
            $data = I();
            !$data['game_id'] && exit(json_encode(array('rows' => array(), 'results' => 0)));
            $agent_info   = $_REQUEST["agent"];
            $agent_p_info = $_REQUEST["agent_p"];
            $start        = $data["start"] ? $data["start"] : 0;
            $pageSize     = $data["limit"] ? $data["limit"] : 30;

            //处理搜索条件
            $agentArr = dealList($agent_info, $agent_p_info);

            $where = '1';
            if ($agentArr['agent']) {
                $where .= ' and agent in("' . implode('","', $agentArr['agent']) . '")';
                $map['agent'] = array('in', $agentArr['agent']);
            } elseif ($agentArr['pAgent']) {
                $agent_p_arr  = array_keys(getDataList('agent', 'id', C('DB_PREFIX_API'), array('agent' => array('IN', array_values($agentArr['pAgent'])))));
                $agent_arr    = array_values($agentArr['pAgent']);
                $agent_subarr = array_keys(getDataList('agent', 'agent', C('DB_PREFIX_API'), array('pid' => array('IN', $agent_p_arr))));
                if ($agent_subarr) {
                    $agent_arr = array_merge($agent_arr, $agent_subarr);
                }
                $where .= ' and agent in("' . implode('","', $agent_arr) . '")';
                $map['agent'] = array('in', $agent_arr);
            } elseif ($data['advteruser_id']) {
                $_map['advteruser_id']               = $data['advteruser_id'];
                $data['game_id'] && $_map['game_id'] = $data['game_id'];
                $agent_info                          = array_keys(getDataList('agent', 'agent', C('DB_PREFIX_API'), $_map));
                $data['agent']                       = $agent_info;
                $where .= ' and agent in("' . implode('","', $data['agent']) . '")';
                $map['agent'] = array('in', $data['agent']);
            } else {
                //权限控制
                $where .= ' and agent in("' . implode('","', $this->agentArr) . '")';
                $map['agent'] = array('in', $this->agentArr);
            }

            if ($data['game_id']) {
                $where .= ' and gameId=' . $data['game_id'];
                $map['gameId'] = $data['game_id'];
            }

            if ($data['startDate'] && $data['endDate']) {
                $where .= ' and dayTime>="' . $data['startDate'] . '" and dayTime<"' . date('Y-m-d', strtotime($data['endDate'] . '+1 day')) . '"';
                $map['dayTime'] = array(array('egt', $data['startDate']), array('lt', date('Y-m-d', strtotime($data['endDate'] . '+1 day'))), 'and');
            }

            $res        = D('Admin')->getPayRemainData($map, $start, $pageSize, $where); //付费衰减数据
            $results    = $res['count'];
            $game_list  = getDataList('game', 'id', C('DB_PREFIX_API'));
            $agent_list = getDataList('agent', 'agent', C('DB_PREFIX_API'));

            foreach ($res['list'] as $key => $val) {
                $data['lookType'] == 1 && $res['list'][$key]['agent']     = '-';
                $data['lookType'] == 1 && $res['list'][$key]['agentName'] = '-';
                $res['list'][$key]['gameName']                            = $game_list[$val['gameId']]['gameName'];
                //处理留存率
                $remainArr = $this->payRemainSet($res['list'][$key], $res['list'][0]['dayTime']);
                $rows[]    = $remainArr;
            }

            //显示图表
            if ($data['chart'] == 1) {
                $payRemainChart = $this->payRemainChart($rows);
                if ($payRemainChart === false) {
                    $this->ajaxReturn(array('status' => 0, 'info' => array('day1' => array(), 'day' => array(), 'day7' => array(), 'day15' => array(), 'day30' => array(), 'day60' => array())));
                }
                $this->ajaxReturn(array('status' => 1, 'info' => $payRemainChart));
            }

            //数据汇总
            $pagesummary = $this->payRemainSummarys($rows);
            if ($data['export'] == 1) {

                $col     = array('dayTime' => '注册日期', 'gameName' => '游戏名称', 'newUser' => '注册用户数', 'newPay' => '首日付费金额');
                $day_arr = array(1, 3, 7, 15, 30, 60);

                for ($i = 0; $i <= 120; $i++) {
                    if (in_array($i, $day_arr)) {
                        $i == 1 && $col['day' . $i] = '次日流水增降幅度';
                        $col['day' . $i]            = ($i) . '日流水增降幅度';
                    }
                }
                array_unshift($rows, $col);
                export_to_csv($rows, '流水衰减', $col);
                exit();
            }
            $arr = array('rows' => $rows ? $rows : array(), 'results' => $results, 'pageSummary' => $pagesummary);
            unset($rows, $remainArr);
            exit(json_encode($arr));
        } else {
            if (IS_AJAX) {
                $response = $this->fetch();
                $this->ajaxReturn(array('status' => 1, '_html' => $response));
            } else {
                $this->display();
            }
        }
    }

    //付费留存图表
    protected function payRemainChart($data)
    {
        if (!$data) {
            return false;
        }

        array_pop($data);
        $chart = array();

        $chart['day'] = array_column($data, 'dayTime');

        foreach ($data as $key => $value) {
            $chart['remain']['day1'][]  = $value['day1'] + 0;
            $chart['remain']['day3'][]  = $value['day3'] + 0;
            $chart['remain']['day7'][]  = $value['day7'] + 0;
            $chart['remain']['day15'][] = $value['day15'] + 0;
            $chart['remain']['day30'][] = $value['day30'] + 0;
            $chart['remain']['day60'][] = $value['day60'] + 0;
        }

        return $chart;
    }

    /**
     * 支付方式占比
     */
    public function payTypeRate()
    {
        if (IS_POST) {
            $data = I();
            if ($data['startDate'] && $data['endDate']) {
                $d1   = date_create($data['startDate']);
                $d2   = date_create($data['endDate']);
                $diff = date_diff($d1, $d2);
                if ($diff->format("%a") > 7) {
                    exit(json_encode(array('hasError' => true, 'error' => '日期跨度不能大于7天')));
                }
            } else {
                exit(json_encode(array('hasError' => true, 'error' => '时间必选')));
            }
            $map['agent'] = array('IN', $this->agentArr);
            // $map['orderStatus'] = 0
            $map['orderType']  = 0;
            $map['createTime'] = array(array('egt', strtotime($data['startDate'])), array('lt', strtotime($data['endDate'] . ' +1 day')), 'AND');
            $list              = D('Admin')->getPayTypeRate($map);
            $row               = array();
            foreach ($list as $k => $val) {
                if ($val['payType'] == 0 && $val['type'] == 1) {
                    continue;
                }

                if (!isset($row[$val['days']])) {
                    $row[$val['days']]['dayTime'] = $val['days'];
                }

                $row[$val['days']]['orderNum'] += $val['orderNum'];

                if ($val['payType'] == 0) {
                    if ($val['type'] == 2) {
                        $row[$val['days']]['apple'] += $val['orderNum'];
                    }
                } elseif ($val['payType'] == 1) {
                    $row[$val['days']]['zhifubao'] += $val['orderNum'];
                } elseif ($val['payType'] == 2) {
                    $row[$val['days']]['weixin'] += $val['orderNum'];
                } elseif ($val['payType'] == 3) {
                    $row[$val['days']]['yinlian'] += $val['orderNum'];
                }

            }
            sort($row);
            foreach ($row as $k => $val) {
                $newRow[$k]['dayTime']  = $val['dayTime'];
                $newRow[$k]['orderNum'] = $val['orderNum'];
                $newRow[$k]['apple']    = numFormat($val['apple'] / $val['orderNum'], true);
                $newRow[$k]['zhifubao'] = numFormat($val['zhifubao'] / $val['orderNum'], true);
                $newRow[$k]['weixin']   = numFormat($val['weixin'] / $val['orderNum'], true);
                $newRow[$k]['yinlian']  = numFormat($val['yinlian'] / $val['orderNum'], true);
            }
            //显示图表
            if ($data['chart'] == 1) {
                $Chart = $this->PayTypeRateChart($row);
                if ($Chart === false) {
                    $this->ajaxReturn(array('status' => 0, 'info' => array('dayTime' => array(), 'key' => array(), 'data' => array())));
                }
                $this->ajaxReturn(array('status' => 1, 'info' => $Chart));
            }

            $results = count($newRow);
            $arr     = array('rows' => $newRow ? $newRow : array(), 'results' => $results);
            exit(json_encode($arr));
        } else {
            if (IS_AJAX) {
                $response = $this->fetch();
                $this->ajaxReturn(array('status' => 1, '_html' => $response));
            } else {
                $this->display();
            }
        }
    }

    /**
     * 获取支付方式图表
     * @AuthorHTL
     * @DateTime  2017-10-12T16:08:58+0800
     * @param     [type]                   $data [description]
     * @return    [type]                         [description]
     */
    protected function PayTypeRateChart($data)
    {
        if (!$data) {
            return false;
        }

        $chart            = array();
        $chart['key']     = ['苹果订单占比', '支付宝订单占比', '微信订单占比', '银联订单占比'];
        $chart['dayTime'] = array_column($data, 'dayTime');
        $chart['data'][]  = array('name' => '苹果订单占比', 'type' => 'line', 'smooth' => true, 'data' => $this->delRate(array_column($data, 'apple'), array_column($data, 'orderNum')));
        $chart['data'][]  = array('name' => '支付宝订单占比', 'type' => 'line', 'smooth' => true, 'data' => $this->delRate(array_column($data, 'zhifubao'), array_column($data, 'orderNum')));
        $chart['data'][]  = array('name' => '微信订单占比', 'type' => 'line', 'smooth' => true, 'data' => $this->delRate(array_column($data, 'weixin'), array_column($data, 'orderNum')));
        $chart['data'][]  = array('name' => '银联订单占比', 'type' => 'line', 'smooth' => true, 'data' => $this->delRate(array_column($data, 'yinlian'), array_column($data, 'orderNum')));
        return $chart;
    }

    protected function delRate($data, $orderNum)
    {
        $arr = array();
        foreach ($data as $k => $val) {
            $arr[$k] = sprintf("%.2f", ($val / $orderNum[$k]) * 100);
        }
        return $arr;
    }

    /**
     * 实时活跃在线统计
     * @return [type] [description]
     */
    public function onlineDau()
    {
        if (IS_POST) {
            $data = I();
            !$data['game_id'] && exit(json_encode(array('rows' => array(), 'results' => 0)));

            $agent_info   = $_REQUEST["agent"];
            $agent_p_info = $_REQUEST["agent_p"];

            $start    = $data["start"] ? $data["start"] : 0;
            $pageSize = $data["limit"] ? $data["limit"] : 30;

            //处理搜索条件
            $agentArr = dealList($agent_info, $agent_p_info);

            if ($agentArr['agent']) {
                $map["agent"]  = array("in", $agentArr['agent']);
                $map2["agent"] = array("in", $agentArr['agent']);
            } else {
                if ($agentArr['pAgent']) {

                    $agent_p_arr = array_keys(getDataList('agent', 'id', C('DB_PREFIX_API'), array('agent' => array('IN', array_values($agentArr['pAgent'])))));

                    $agent_arr    = array_values($agentArr['pAgent']);
                    $agent_subarr = array_keys(getDataList('agent', 'agent', C('DB_PREFIX_API'), array('pid' => array('IN', $agent_p_arr))));
                    if ($agent_subarr) {
                        $agent_arr = array_merge($agent_arr, $agent_subarr);
                    }
                    $map["agent"]  = array("in", $agent_arr);
                    $map2["agent"] = array("in", $agent_arr);
                } else {
                    //权限控制
                    $map["agent"]  = array("in", $this->agentArr);
                    $map2["agent"] = array("in", $this->agentArr);
                }
            }

            if ($data['game_id']) {
                $map['game_id']  = $data['game_id'];
                $map2['game_id'] = $data['game_id'];
            }

            if ($data['serverId']) {
                $map['serverId']  = $data['serverId'];
                $map2['serverId'] = $data['serverId'];
            }

            $search = $map;
            if ($data['date']) {
                $map['dayTime']    = array('eq', $data['date']);
                $search['dayTime'] = array('eq', date('Y-m-d', strtotime($data['date'] . '-1 day')));
                $map2['dayTime']   = array('eq', $data['date']);
            }
            $search['time'] = array('lt', time() - 86400 - 600);
            if ($data['roleName']) {
                $map2['roleName'] = array('like', '%' . $data['roleName'] . '%');
            }

            $resDau     = D("Admin")->getOnlineDau($map);
            $resDau_bef = D("Admin")->getOnlineDau($search);
            $resDetail  = D("Admin")->getOnlineDetail($start, $pageSize, $map2);
            $count      = $resDetail['count'];

            array_shift($resDau);
            array_shift($resDau_bef);

            foreach ($resDetail['list'] as $key => $value) {
                $resDetail['list'][$key]['onlineTime'] = dateformat(($value['onlineTime'] * 60));
            }

            if ($data['export'] == 1) {
                $col = array('userCode' => '用户标识', 'roleName' => '角色名称', 'serverName' => '服务器', 'onlineTime' => '在线时长', 'ip' => '登录IP', 'province' => '省份', 'city' => '城市');
                array_unshift($resDetail['list'], $col);
                export_to_csv($resDetail['list'], '实时在线数据统计', $col);
                exit();
            }
            exit(json_encode(array("info" => $resDau, "yesterday" => $resDau_bef, "rows" => $resDetail['list'], 'results' => $count)));
        } else {
            if (IS_AJAX) {
                $response = $this->fetch();
                $this->ajaxReturn(array('status' => 1, '_html' => $response));
            } else {
                $this->display();
            }
        }
    }

    /**
     * 每日峰值统计
     * @return [type] [description]
     */
    public function onlineDayPeak()
    {
        if (IS_POST) {
            $data         = I();
            $agent_info   = $_REQUEST["agent"];
            $agent_p_info = $_REQUEST["agent_p"];
            !$data['game_id'] && exit(json_encode(array('rows' => array(), 'results' => 0)));

            //处理搜索条件
            $agentArr = dealList($agent_info, $agent_p_info);

            if ($agentArr['agent']) {
                $map["agent"] = array("in", $agentArr['agent']);
            } else {
                if ($agentArr['pAgent']) {

                    $agent_p_arr = array_keys(getDataList('agent', 'id', C('DB_PREFIX_API'), array('agent' => array('IN', array_values($agentArr['pAgent'])))));

                    $agent_arr    = array_values($agentArr['pAgent']);
                    $agent_subarr = array_keys(getDataList('agent', 'agent', C('DB_PREFIX_API'), array('pid' => array('IN', $agent_p_arr))));
                    if ($agent_subarr) {
                        $agent_arr = array_merge($agent_arr, $agent_subarr);
                    }
                    $map["agent"] = array("in", $agent_arr);
                } else {
                    //权限控制
                    $map["agent"] = array("in", $this->agentArr);
                }
            }

            if ($data['game_id']) {
                $map['game_id'] = $data['game_id'];
            }

            if ($data['serverId']) {
                $map['serverId'] = $data['serverId'];
            }

            if ($data['startDate'] && $data['endDate']) {
                $map['dayTime'] = array(array('egt', $data['startDate']), array('lt', date('Y-m-d', strtotime($data['endDate'] . '+1 day'))), 'and');
            }

            $res = D("Admin")->getOnlineDau($map);

            $max     = array();
            $avg     = array();
            $min     = array();
            $summary = array();

            if (!empty($res)) {
                foreach ($res as $value) {
                    $info[$value['dayTime']][] = $value['amount'];
                }
                ksort($info);

                //最大，最小，平均值
                foreach ($info as $key => $val) {
                    $max[$key] = max($val);
                    $min[$key] = min($val);
                    $avg[$key] = numFormat(array_sum($val) / count($val));
                }

                //汇总
                foreach ($max as $k => $v) {
                    $summary[$k]['dayTime'] = $k;
                    $summary[$k]['max']     = $max[$k];
                    $summary[$k]['min']     = $min[$k];
                    $summary[$k]['avg']     = $avg[$k];
                }
            }
            if ($data['export'] == 1) {
                $col = array('dayTime' => '日期', 'max' => '峰值', 'min' => '最低值', 'avg' => '平均值');
                array_unshift($summary, $col);
                export_to_csv($summary, '在线峰值数据统计', $col);
                exit();
            }
            exit(json_encode(array("max" => $max, "min" => $min, "avg" => $avg, "summary" => $summary)));
        } else {
            if (IS_AJAX) {
                $response = $this->fetch();
                $this->ajaxReturn(array('status' => 1, '_html' => $response));
            } else {
                $this->display();
            }
        }
    }

    public function gunfuData()
    {
        if (IS_POST) {
            $data         = I();
            $agent_info   = $_REQUEST["agent"];
            $agent_p_info = $_REQUEST["agent_p"];

            $start    = $data["start"] ? $data["start"] : 0;
            $pageSize = $data["limit"] ? $data["limit"] : 30;

            //处理搜索条件
            $agentArr = dealList($agent_info, $agent_p_info);

            if ($agentArr['agent']) {
                $map['agent'] = array('IN', $agentArr['agent']);
            } else {
                if ($agentArr['pAgent']) {
                    $agent_p_arr = array_keys(getDataList('agent', 'id', C('DB_PREFIX_API'), array('agent' => array('IN', array_values($agentArr['pAgent'])))));

                    $agent_arr    = array_values($agentArr['pAgent']);
                    $agent_subarr = array_keys(getDataList('agent', 'agent', C('DB_PREFIX_API'), array('pid' => array('IN', $agent_p_arr))));
                    if ($agent_subarr) {
                        $agent_arr = array_merge($agent_arr, $agent_subarr);
                    }
                    $map['agent'] = array('IN', $agent_arr);
                } else {
                    //权限控制
                    $map['agent'] = array('IN', $this->agentArr);
                }
            }

            if ($data['game_id']) {
                $map['gameId'] = $data['game_id'];
            }

            if ($data['startDate'] && $data['endDate']) {
                $map['dayTime'] = array(array('egt', $data['startDate']), array('lt', date('Y-m-d', strtotime($data['endDate'] . '+1 day'))), 'AND');
            }

            if ($data['serverId']) {
                $map['serverId'] = $data['serverId'];
            }

            $res     = D('Admin')->getGunfuData($map, $start, $pageSize);
            $results = count($res['list']);

            $game_list = getDataList('game', 'id', C('DB_PREFIX_API'));
            $rows      = array();

            foreach ($res['list'] as $key => $val) {
                $res['list'][$key]['dayTime']    = $val['dayTime'];
                $res['list'][$key]['gameName']   = $game_list[$val['gameId']]['gameName'];
                $res['list'][$key]['newUser']    = $val['newUser'];
                $res['list'][$key]['actUser']    = $val['actUser'];
                $res['list'][$key]['allPay']     = $val['allPay'];
                $res['list'][$key]['allPayUser'] = $val['allPayUser'];
                $res['list'][$key]['payRate']    = numFormat($val['allPayUser'] / $val['actUser'], true);
                $res['list'][$key]['ARPPU']      = numFormat($val['allPay'] / $val['allPayUser']);
            }
            $row = $res['list'];

            $summary = array();
            foreach ($res['list'] as $key => $val) {
                $summary['newUser'] += $val['newUser'];
                $summary['actUser'] += $val['actUser'];
                $summary['allPay'] += $val['allPay'];
                $summary['allPayUser'] += $val['allPayUser'];
            }
            $summary['payRate']  = numFormat($summary['allPayUser'] / $summary['actUser'], true);
            $summary['ARPPU']    = numFormat($summary['allPay'] / $summary['allPayUser']);
            $summary['dayTime']  = '汇总';
            $summary['gameName'] = '-';

            if ($data['export'] == 1) {
                $col = array('dayTime' => '日期', 'gameName' => '游戏名称', 'newUser' => '新增滚服人数', 'actUser' => '滚服活跃人数', 'allPay' => '充值总额', 'allPayUser' => '充值人数', 'payRate' => '付费率', 'ARPPU' => 'ARPPU');

                array_unshift($row, $col);
                array_push($row, $summary);
                export_to_csv($row, '滚服数据概况', $col);
                exit();
            }
            $arr = array('rows' => $row ? $row : array(), 'results' => $results, 'pageSummary' => $summary);
            exit(json_encode($arr));
        } else {
            if (IS_AJAX) {
                $response = $this->fetch();
                $this->ajaxReturn(array('status' => 1, '_html' => $response));
            } else {
                $this->display();
            }
        }
    }

    /**
     * 获取VIP用户数据
     * @return [type] [description]
     */
    public function vipUser()
    {
        if (IS_POST) {
            $data = I();

            $start    = $data["start"] ? $data["start"] : 0;
            $pageSize = $data["limit"] ? $data["limit"] : 30;

            if ($data['game_id']) {
                $map['gameId'] = $data['game_id'];
            }

            if ($data['creater']) {
                $map['creater'] = $data['creater'];
            }

            $noLogin  = $data['noLogin'] ? $data['noLogin'] : 0;
            $noCharge = $data['noCharge'] ? $data['noCharge'] : 0;

            $min = $data['min'] ? $data['min'] : 0;
            $max = $data['max'] ? $data['max'] : 0;

            if ($noLogin || $noCharge || $min || $max) {
                $flag = true;
            } else {
                $flag = false;
            }
            if ($flag) {
                $res = D('Admin')->getVipUser($map);
            } else {
                $list    = D('Admin')->getVipUser_v2($map, $start, $pageSize);
                $res     = $list['list'];
                $results = $list['results'];
            }

            if (!empty($res)) {
                foreach ($res as $key => $val) {
                    $map2['userCode']      = $val['userCode'];
                    $map2['roleId']        = $val['roleId'];
                    $map2['loginServerId'] = $val['serverId'];
                    $map2['roleName']      = $val['roleName'];
                    $result                = D('Admin')->getVipUserInfo($map2);
                    if (!empty($result)) {
                        $res[$key]['roleId']     = $result['roleId'];
                        $res[$key]['roleName']   = $result['roleName'];
                        $res[$key]['lastLogin']  = $result['lastLogin'] ? date('Y-m-d H:i:s', $result['lastLogin']) : '';
                        $res[$key]['lastCharge'] = $result['lastCharge'] ? date('Y-m-d H:i:s', $result['lastCharge']) : '';
                        $res[$key]['amount']     = $result['amount'] ? $result['amount'] : 0;
                        $res[$key]['noLogin']    = $result['lastLogin'] ? floor((time() - $result['lastLogin']) / 86400) : 999;
                        $res[$key]['noCharge']   = $result['lastCharge'] ? floor((time() - $result['lastCharge']) / 86400) : 999;

                        if ($result['userName']) {
                            $res[$key]['userName'] = $result['userName'];
                        }

                        if ($res[$key]['noLogin'] < $noLogin) {
                            unset($res[$key]);
                        }

                        if ($res[$key]['noCharge'] < $noCharge) {
                            unset($res[$key]);
                        }

                        if ($res[$key]['amount'] < $min) {
                            unset($res[$key]);
                        }

                        if ($max) {
                            if ($res[$key]['amount'] > $max) {
                                unset($res[$key]);
                            }

                        }
                    }
                }
            }

            //导出
            if ($data['export'] == 1) {
                $col = array('gameId' => '游戏名称', 'agent' => '渠道号', 'userName' => '用户账号', 'serverName' => '区服名称', 'roleId' =>
                    '角色ID', 'roleName' => '角色名称', 'lastLogin' => '最后登录', 'lastCharge' => '最后充值', 'amount' => '累计充值', 'noLogin' => '未登录天数', 'noCharge' => '未充值天数');
                array_unshift($res, $col);
                export_to_csv($res, 'VIP用户数据', $col);
                exit();
            }

            if ($flag) {
                $results = count($res);
                $row     = array_slice($res, $start, $pageSize);
            } else {
                $row = $res;
            }

            $arr = array('rows' => $row ? $row : array(), 'results' => $results);
            exit(json_encode($arr));
        } else {
            if (IS_AJAX) {
                $response = $this->fetch();
                $this->ajaxReturn(array('status' => 1, '_html' => $response));
            } else {
                $this->display();
            }
        }
    }

    /**
     * VIP用户插入前置操作
     * @param string $table 操作的数据表
     */
    public function _before_insert($data)
    {
        if ($this->table == 'vip_user') {
            if (!$data['gameId']) {
                $this->error('请选择游戏');
            }

            $map['gameId']     = $data['gameId'];
            $map['roleId']     = $data['roleId'];
            $map['serverName'] = $data['serverName'];
            $map['agent']      = $data['agent'];

            if (D('Admin')->commonQuery('vip_user', $map)) {
                $this->error('该角色已存在');
            }

            $res = getDataList('role', 'roleId', C('DB_PREFIX_API'), $map);

            if (empty($res)) {
                $this->error('系统匹配不到该角色');
            } else {
                $data['userCode'] = $res[$data['roleId']]['userCode'];
                $data['roleName'] = $res[$data['roleId']]['roleName'];
                $data['serverId'] = $res[$data['roleId']]['serverId'];

                if ($data['userCode']) {
                    $result = getDataList('user', 'userCode', C('DB_PREFIX_API'), array('userCode' => $data['userCode']));
                    if (!empty($result)) {
                        $data['userName'] = $result[$data['userCode']]['userName'];
                        $data['gameName'] = $result[$data['userCode']]['gameName'];
                    }
                }
            }

            $data['createTime'] = time();
            $data['creater']    = session('admin.realname');
        }

        return $data;
    }

    /**
     * 导入VIP用户
     */
    public function importVIP()
    {
        if (IS_POST) {
            if (!$_FILES['vipUserFile']['name']) {
                $this->error('没有传入Excel');
            }
            //文件上传
            $file_info = excel_file_upload('VipUser');
            if ($file_info && $file_info != '没有文件被上传！') {
                //获取文件数据并且转数组
                $fileName = './Uploads/' . $file_info['vipUserFile']['savepath'] . $file_info['vipUserFile']['savename'];
                $data     = excel_to_array($fileName);
                if ($data) {
                    $msg = '';
                    $arr = array();
                    unset($data[1]);
                    foreach ($data as $key => $val) {
                        if (empty($val[0])) {
                            @unlink($fileName);
                            $this->error('存在空用户标识符');
                        }

                        if (empty($val[1])) {
                            @unlink($fileName);
                            $this->error('存在空区服');
                        }

                        if (empty($val[2])) {
                            @unlink($fileName);
                            $this->error('存在空角色名');
                        }

                        if (empty($val[3])) {
                            @unlink($fileName);
                            $this->error('存在空角色ID');
                        }

                        if (empty($val[4])) {
                            @unlink($fileName);
                            $this->error('存在空QQ');
                        }

                        $map['userCode']   = $val[0];
                        $map['serverName'] = $val[1];
                        $map['roleId']     = $roleId     = $val[3];

                        $gameList = getDataList('game', 'id', C('DB_PREFIX_API'));

                        if (D('Admin')->commonQuery('vip_user', $map)) {
                            $msg .= ($key) . '行,角色已存在;';
                            continue;
                        }

                        $roleInfo = getDataList('role', 'roleId', C('DB_PREFIX_API'), $map);
                        if (empty($roleInfo)) {
                            $msg .= ($key) . '行,角色匹配不到;';
                            continue;
                        } else {
                            foreach ($roleInfo as $value) {
                                $result = getDataList('user', 'userCode', C('DB_PREFIX_API'), array('userCode' => $value['userCode']));

                                $arr[] = array(
                                    'gameId'     => $value['game_id'],
                                    'gameName'   => $gameList[$value['game_id']]['gameName'],
                                    'agent'      => $value['agent'],
                                    'userCode'   => $value['userCode'],
                                    'userName'   => $result[$value['userCode']]['userName'],
                                    'roleId'     => $value['roleId'],
                                    'roleName'   => $value['roleName'],
                                    'serverId'   => $value['serverId'],
                                    'serverName' => $value['serverName'],
                                    'qq'         => $val[4],
                                    'phone'      => $val[5],
                                    'name'       => $val[6],
                                    'createTime' => time(),
                                    'creater'    => session('admin.realname'),
                                );
                            }
                        }
                    }

                    if ($msg) {
                        $errorInfo = '存在错误数据，请修改再上传,信息为：<br/>' . $msg;
                        unset($arr);
                        $this->error($errorInfo, '', 10);
                    }

                    if ($arr && D('Admin')->commonAddAll('vip_user', $arr)) {
                        @unlink($fileName);
                        $this->success('VIP用户导入成功');
                    } else {
                        @unlink($fileName);
                        $this->error('VIP用户导入失败');
                    }
                }
            }
        } else {
            if (IS_AJAX) {
                $response = $this->fetch();
                $this->ajaxReturn(array('status' => 1, '_html' => $response));
            } else {
                $this->display();
            }
        }
    }

    private function ortherInfo(&$info, &$val)
    {
        if ($val['gameId'] != 113) {
            foreach ($this->specialData as $key => $value) {
                if ($val['dayTime'] == $value['dayTime'] && $val['gameId'] == $value['gameId']) {
                    $info['newDevice']    = $val['newDevice']    = $value['newDevice'];
                    $info['disUdid']      = $val['disUdid']      = $value['disUdid'];
                    $info['newUser']      = $val['newUser']      = $value['newUser'];
                    $info['newUserLogin'] = $val['newUserLogin'] = $value['newUserLogin'];
                    $info['oldUserLogin'] = $val['oldUserLogin'] = $value['oldUserLogin'];

                    $info['day1']  = $val['day1']  = $value['day1'];
                    $info['day2']  = $val['day2']  = $value['day2'];
                    $info['day6']  = $val['day6']  = $value['day6'];
                    $info['day13'] = $val['day13'] = $value['day13'];
                    $info['day29'] = $val['day29'] = $value['day29'];
                    break;

                } elseif (strtotime($val['dayTime']) <= strtotime('2017-11-16')) {
                    $info['newDevice']    = $val['newDevice']    = 0;
                    $info['disUdid']      = $val['disUdid']      = 0;
                    $info['distinctReg']  = $val['distinctReg']  = 0;
                    $info['newUser']      = $val['newUser']      = 0;
                    $info['newUserLogin'] = $val['newUserLogin'] = 0;
                    $info['oldUserLogin'] = $val['oldUserLogin'] = 0;
                    $info['monthLogin']   = $val['monthLogin']   = 0;

                    $info['day1']  = $val['day1']  = 0;
                    $info['day2']  = $val['day2']  = 0;
                    $info['day6']  = $val['day6']  = 0;
                    $info['day13'] = $val['day13'] = 0;
                    $info['day29'] = $val['day29'] = 0;
                    break;
                }
            }
        } elseif ($val['gameId'] == 113) {
            if ($val['dayTime'] == '2017-11-09') {
                $info['oldUserLogin'] = $val['oldUserLogin'] += 0;
                $info['newUser']      = $val['newUser'] += 3;
                $info['day1'] += 445;
                $val['newPay'] += 223;
                $val['allPay'] += 110;
                $val['allPayUser'] += 2;

            }

            if ($val['dayTime'] == '2017-11-10') {
                $info['oldUserLogin'] = $val['oldUserLogin'] += 446;
                $info['newUser']      = $val['newUser'] += 1;
                $info['day1'] += 34;
                $val['allPay'] += 3710;
                $val['allPayUser'] += 57;
            }

            if ($val['dayTime'] == '2017-11-11') {
                $info['oldUserLogin'] = $val['oldUserLogin'] += 270;
                $info['day1'] += 5;
                $val['allPay'] += 3442;
                $val['allPayUser'] += 30;

            }

            if ($val['dayTime'] == '2017-11-12') {
                $info['oldUserLogin'] = $val['oldUserLogin'] += 214;
                $val['allPay'] += 2094;
                $val['allPayUser'] += 20;

            }

            if ($val['dayTime'] == '2017-11-13') {
                $info['oldUserLogin'] = $val['oldUserLogin'] += 172;
                $info['day1'] += 7;

                $val['allPay'] += 1172;
                $val['allPayUser'] += 18;
            }

            if ($val['dayTime'] == '2017-11-14') {
                $info['oldUserLogin'] = $val['oldUserLogin'] += 160;
                $val['allPay'] += 643;
                $val['allPayUser'] += 7;
            }

            if ($val['dayTime'] == '2017-11-15') {
                $info['oldUserLogin'] = $val['oldUserLogin'] += 100;
                $val['allPay'] += 800;
                $val['allPayUser'] += 10;
            }
            if ($val['dayTime'] == '2017-11-16') {
                $info['oldUserLogin'] = $val['oldUserLogin'] += 100;

            }
            if ($val['dayTime'] == '2017-11-17') {
                $info['oldUserLogin'] = $val['oldUserLogin'] += 100;

            }
        }

        $date                         = (strtotime(date('Y-m-d')) - strtotime($val['dayTime'])) / 86400;
        $date <= 1 && $info['day1']   = $val['day1']   = 0;
        $date <= 6 && $info['day6']   = $val['day6']   = 0;
        $date <= 13 && $info['day13'] = $val['day13'] = 0;
        $date <= 29 && $info['day29'] = $val['day29'] = 0;
    }

    private function ortherPay(&$val)
    {
        $status = false;
        foreach ($this->specialData as $key => $value) {
            if ($val['dayTime'] == $value['dayTime'] && $val['gameId'] == $value['gameId']) {
                $val['allPay']     = $value['allPay'];
                $val['allPayUser'] = $value['allPayUser'];
                $val['newPay']     = $value['newPay'];
                $val['newPayUser'] = $value['newPayUser'];
                $status = true;
                break;
            } elseif (strtotime($val['dayTime']) <= strtotime('2017-11-16')) {
                $val['allPay']     = 0;
                $val['allPayUser'] = 0;
                $val['newPay']     = 0;
                $val['newPayUser'] = 0;
                $status = true;
                break;
            }
        }
        if (!$status) {
            return false;
        }
        return true;
    }

    /**
     * 苹果日报
     */
    public function appleDaily()
    {
        if (IS_POST) {
            $data     = I();
            $start    = $data["start"] ? $data["start"] : 0;
            $pageSize = $data["limit"] ? $data["limit"] : 30;
            //搜索条件
            $map                        = array();
            $data["day"] && $map["day"] = $data["day"];
            if ($data["agent"]) {
                $d_agent       = D("Admin")->commonQuery("agent", array("agent" => $data["agent"]), 0, 1, "appleId", C("DB_PREFIX_API"));
                $map["bundle"] = $d_agent["appleId"] ? $d_agent["appleId"] : "-1";
            };

            $info  = D("Admin")->getBuiList("apple_platform_daily", $map, $start, $pageSize, C("DB_PREFIX"), "id", "ASC");
            $agent = getDataList("agent", "appleId", C("DB_PREFIX_API"), array("gameType" => 2));
            $row   = $info["list"];
            foreach ($row as $k => $v) {
                $row[$k]["app"]  = $agent[$v["bundle"]] ? $agent[$v["bundle"]]["agentName"] : "未知（应用ID：" . $v["bundle"] . "）";
                $row[$k]["time"] = date("Y-m-d H:i:s", $v["createTime"]);
            }
            exit(json_encode(array("rows" => $row, "results" => $info["count"])));
        } else {
            $this->display();
        }
    }

    /**
     * 登陆日志
     */
    public function loginLog()
    {
        if (IS_POST) {
            $data     = I();
            $start    = $data["start"] ? $data["start"] : 0;
            $pageSize = $data["limit"] ? $data["limit"] : 30;
            if (!$data["day"]) {
                exit(json_encode(array("rows" => array(), "results" => 0)));
            }

            $map["time"]                          = array("BETWEEN", array(strtotime($data["day"]), strtotime($data["day"] . " + 1day") - 1));
            $data["userCode"] && $map["userCode"] = $data["userCode"];
            $info                                 = D("Admin")->getBuiList("login", $map, $start, $pageSize, C("DB_PREFIX_LOG"), "id", "DESC");
            $row                                  = $info["list"];
            foreach ($row as $k => $v) {
                $agent                = D("Admin")->commonQuery("agent", array("agent" => $v["agent"]), 0, 1, "agentName", C("DB_PREFIX_API"));
                $row[$k]["agentName"] = $agent["agentName"];
            }
            exit(json_encode(array("rows" => $row, "results" => $info["count"])));
        } else {
            $this->display();
        }
    }

    /**
     * 操作日志
     */
    public function operationLog()
    {
        if (IS_POST) {
            $data     = I();
            $start    = $data["start"] ? $data["start"] : 0;
            $pageSize = $data["limit"] ? $data["limit"] : 30;
            if ($data["startDay"] && $data["endDay"]) {
                $map["time"] = array("BETWEEN", array(strtotime($data["startDay"]), strtotime($data["endDay"] . " + 1day") - 1));
            } elseif ($data["startDay"]) {
                $map["time"] = array("EGT", strtotime($data["startDay"]));
            } elseif ($data["endDay"]) {
                $map["time"] = array("ELT", strtotime($data["endDay"] . " + 1day") - 1);
            }
            $data["user"] && $map["user"]     = $data["user"];
            $data["action"] && $map["action"] = $data["action"];
            $data["log"] && $map["log"]       = array("LIKE", "%" . $data["log"] . "%");
            $info                             = D("Admin")->getBuiList("operation", $map, $start, $pageSize, C("DB_PREFIX_LOG"), "id", "DESC");
            $row                              = $info["list"];
            foreach ($row as $k => $v) {
                $agent                = D("Admin")->commonQuery("agent", array("agent" => $v["agent"]), 0, 1, "agentName", C("DB_PREFIX_API"));
                $row[$k]["agentName"] = $agent["agentName"] ? $agent["agentName"] : "（无）";
            }
            exit(json_encode(array("rows" => $row, "results" => $info["count"])));
        } else {
            $this->display();
        }
    }

    /**
     * 付费帐户留存统计
     */
    public function firstPayRemain()
    {
        if (IS_POST) {
            $data = I();
            !$data['game_id'] && exit(json_encode(array('rows' => array(), 'results' => 0)));
            $agent_info   = $_REQUEST["agent"];
            $agent_p_info = $_REQUEST["agent_p"];
            $start        = $data["start"] ? $data["start"] : 0;
            $pageSize     = $data["limit"] ? $data["limit"] : 30;

            //处理搜索条件
            $agentArr = dealList($agent_info, $agent_p_info);

            $where = '1';

            if ($agentArr['agent']) {
                $where .= ' and agent IN("' . implode('","', $agentArr['agent']) . '")';
                $map['agent'] = array('IN', $agentArr['agent']);
            } else {
                $agent_infos = $map_arr = array();

                if (!empty($agentArr['pAgent'])) {
                    $agent_p_arr        = array_keys(getDataList('agent', 'id', C('DB_PREFIX_API'), array('agent' => array('IN', array_values($agentArr['pAgent'])))));
                    $map_arr['_string'] = "id IN ('" . implode("','", $agent_p_arr) . "') OR pid IN ('" . implode("','", $agent_p_arr) . "')";
                }

                if ($data['game_id']) {
                    $map_arr['game_id'] = $data['game_id'];
                }

                if ($map_arr) {
                    $agent_infos = array_keys(getDataList('agent', 'agent', C('DB_PREFIX_API'), $map_arr));
                }

                $arr = $this->agentArr;
                if ($agent_infos) {
                    $arr = array_intersect($arr, $agent_infos);
                }

                sort($arr);
                if (count($arr) < 1) {
                    exit(json_encode(array('rows' => array(), 'results' => 0)));
                }

                $where .= ' and agent IN("' . implode('","', $arr) . '")';
                $map['agent'] = array('IN', $arr);
            }

            if ($data['game_id']) {
                $where .= ' and gameId=' . $data['game_id'];
                $map['gameId'] = $data['game_id'];
            }

            if ($data['startDate'] && $data['endDate']) {
                $where .= ' and dayTime>="' . $data['startDate'] . '" and dayTime<"' . date('Y-m-d', strtotime($data['endDate'] . '+1 day')) . '"';
                $map['dayTime'] = array(array('egt', $data['startDate']), array('lt', date('Y-m-d', strtotime($data['endDate'] . '+1 day'))), 'and');
            }

            if ($data['serverId']) {
                $where .= ' and serverId="' . $data['serverId'] . '"';
                $map['serverId'] = $data['serverId'];
            }

            $res        = D('Admin')->getFirstPayRemainData($map, $start, $pageSize, $where); //付费帐户留存数据
            $results    = $res['count'];

            foreach ($res['list'] as $key => $val) {
                //处理留存率
                $remainArr = $this->userRemainSet($res['list'][$key],0,'allFirstPay');
                $rows[]    = $remainArr;
            }

            //数据汇总
            $pagesummary = $this->firstPaysummarys($rows);
            if ($data['export'] == 1) {
                $col = array('dayTime' => '日期', 'allFirstPay' => '新增付费帐号', 'newFirstPay' => '当日注册并付费', 'oldFirstPay' => '老用户首次付费', 'day1' => '次日留存', 'day2' => '三日留存', 'day6' => '七日留存', 'day13' => '十四日留存', 'day29' => '三十日留存');
                array_unshift($rows, $col);
                $pagesummary['dayTime'] = '汇总';
                array_push($rows, $pagesummary);
                export_to_csv($rows, '付费帐户留存', $col);
                exit();
            }
            $arr = array('rows' => $rows ? $rows : array(), 'results' => $results, 'pageSummary' => $pagesummary);
            unset($rows, $remainArr);
            exit(json_encode($arr));
        } else {
            if (IS_AJAX) {
                $response = $this->fetch();
                $this->ajaxReturn(array('status' => 1, '_html' => $response));
            } else {
                $this->display();
            }
        }
    }

    /**
     * 注册付费帐户留存统计
     */
    public function newFirstPayRemain()
    {
        if (IS_POST) {
            $data = I();
            !$data['game_id'] && exit(json_encode(array('rows' => array(), 'results' => 0)));
            $agent_info   = $_REQUEST["agent"];
            $agent_p_info = $_REQUEST["agent_p"];
            $start        = $data["start"] ? $data["start"] : 0;
            $pageSize     = $data["limit"] ? $data["limit"] : 30;

            //处理搜索条件
            $agentArr = dealList($agent_info, $agent_p_info);

            $where = '1';

            if ($agentArr['agent']) {
                $where .= ' and agent IN("' . implode('","', $agentArr['agent']) . '")';
                $map['agent'] = array('IN', $agentArr['agent']);
            } else {
                $agent_infos = $map_arr = array();

                if (!empty($agentArr['pAgent'])) {
                    $agent_p_arr        = array_keys(getDataList('agent', 'id', C('DB_PREFIX_API'), array('agent' => array('IN', array_values($agentArr['pAgent'])))));
                    $map_arr['_string'] = "id IN ('" . implode("','", $agent_p_arr) . "') OR pid IN ('" . implode("','", $agent_p_arr) . "')";
                }

                if ($data['game_id']) {
                    $map_arr['game_id'] = $data['game_id'];
                }

                if ($map_arr) {
                    $agent_infos = array_keys(getDataList('agent', 'agent', C('DB_PREFIX_API'), $map_arr));
                }

                $arr = $this->agentArr;
                if ($agent_infos) {
                    $arr = array_intersect($arr, $agent_infos);
                }

                sort($arr);
                if (count($arr) < 1) {
                    exit(json_encode(array('rows' => array(), 'results' => 0)));
                }

                $where .= ' and agent IN("' . implode('","', $arr) . '")';
                $map['agent'] = array('IN', $arr);
            }

            if ($data['game_id']) {
                $where .= ' and gameId=' . $data['game_id'];
                $map['gameId'] = $data['game_id'];
            }

            if ($data['startDate'] && $data['endDate']) {
                $where .= ' and dayTime>="' . $data['startDate'] . '" and dayTime<"' . date('Y-m-d', strtotime($data['endDate'] . '+1 day')) . '"';
                $map['dayTime'] = array(array('egt', $data['startDate']), array('lt', date('Y-m-d', strtotime($data['endDate'] . '+1 day'))), 'and');
            }

            if ($data['serverId']) {
                $where .= ' and serverId="' . $data['serverId'] . '"';
                $map['serverId'] = $data['serverId'];
            }

            $res        = D('Admin')->getNewFirstPayRemainData($map, $start, $pageSize, $where); //付费帐户留存数据
            $results    = $res['count'];

            foreach ($res['list'] as $key => $val) {
                //处理留存率
                $remainArr = $this->userRemainSet($res['list'][$key],0,'newFirstPay');
                $rows[]    = $remainArr;
            }

            //数据汇总
            $pagesummary = $this->firstPaysummarys($rows);
            if ($data['export'] == 1) {
                $col = array('dayTime' => '日期',  'newFirstPay' => '当日注册并付费', 'day1' => '次日留存', 'day2' => '三日留存', 'day6' => '七日留存', 'day13' => '十四日留存', 'day29' => '三十日留存');
                array_unshift($rows, $col);
                $pagesummary['dayTime'] = '汇总';
                array_push($rows, $pagesummary);
                export_to_csv($rows, '当日注册并付费留存', $col);
                exit();
            }
            $arr = array('rows' => $rows ? $rows : array(), 'results' => $results, 'pageSummary' => $pagesummary);
            unset($rows, $remainArr);
            exit(json_encode($arr));
        } else {
            if (IS_AJAX) {
                $response = $this->fetch();
                $this->ajaxReturn(array('status' => 1, '_html' => $response));
            } else {
                $this->display();
            }
        }
    }
}
