<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/6/19
 * Time: 14:09
 *
 * 数据管理控制器
 */

namespace ThirdParty\Controller;

class DataController extends BackendController
{

    /**
     * 订单列表
     */
    public function order()
    {
        if (IS_POST) {
            $data       = I();
            !$data['game_id'] && exit(json_encode(array('rows'=>array(), 'results'=>0)));

            //搜索条件
            $start      = $data['start']? $data['start']: 0;
            $pageSize   = $data['limit']? $data['limit']: 30;
            $agentStr   = implode("','", $this->agentArr);
            $map["_string"] = "agent IN ('".$agentStr."')";
            !is_null($this->gameId) && $map['game_id'] = array('in',$this->gameId); //CP权限控制
            $data['orderId'] && $map['orderId'] = $data['orderId'];
            $data['billNo'] && $map['billNo'] = $data['billNo'];
            $data['tranId'] && $map['tranId'] = $data['tranId'];
            $data['userCode'] && $map['userCode'] = $data['userCode'];
            $data['agent'] && $map['agent'] = $data['agent'];
            $data['game_id'] && $map['game_id'] = $data['game_id'];
            $data['channelId'] && $map['channel_id'] = $data['channelId'];
            if ($data["startDate"] && $data["endDate"]) {
                $d1 = date_create($data['startDate']);
                $d2 = date_create($data['endDate']);
                $diff = date_diff($d1,$d2);
                if($diff->format("%a") >= 31){
                    exit(json_encode(array('hasError'=>true, 'error'=>'日期跨度不能大于一个月')));
                }

                $map["createTime"] = array("BETWEEN", array(strtotime($data["startDate"]), strtotime($data["endDate"])));
            } else {
                exit(json_encode(array('hasError'=>true, 'error'=>'日期必选')));
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
                    $map["orderStatus"]     = 0;
                    break;
            }

            if($data['orderType']){
                $data['orderType'] == 1 && $map['orderType'] = 0; //正式订单
                $data['orderType'] == 2 && $map['orderType'] = 1; //测试订单
            }

            //去除三个刷单的非法用户
            if($data['userName']){
                $map['userName'] = $data['userName'];
            }

            $count   = D("ThirdParty/Order")->getCount($map);
            $sum     = D("ThirdParty/Order")->getSum($map);
            $row     = D("ThirdParty/Order")->getOrderLimit($map, $start, $pageSize);
            $agent   = getDataList("agent", "agent", C("DB_PREFIX_API"));
            $data['export'] == 1 && $channel = getDataList("channel", "id", C("DB_PREFIX_API"));
            $pagent  = getDataList('agent','id',C('DB_PREFIX_API'),array('agentType'=>1,'game_id'=>$data['game_id']));
            foreach ($row as $k => $v) {
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
                if($data['export'] == 1){
                    if ($v["orderStatus"]) {
                        $type = "无";
                    } else {
                        if ($v["channel_id"] <= 1) {
                            switch ($v["payType"]) {
                                case 1:
                                    $type = "支付宝";
                                    break;
                                case 2:
                                    $type = "微信";
                                    break;
                                case 0:
                                    if ($v["type"] == 2) {
                                        $type = "苹果";
                                    } else {
                                        $type = "渠道";
                                    };
                                    break;
                                default:
                                    $type = "渠道";
                            }
                        } else {
                            $type = $channel[$row['channel_id']]['channelName'];
                        }
                    }
                    $row[$k]['payType'] = $type;
                }
                $row[$k]['amount']      = number_format($v['amount'],2);
                $row[$k]["create"]      = date("Y-m-d H:i:s", $v["createTime"]);
                $row[$k]["payment"]     = $v["paymentTime"]? date("Y-m-d H:i:s", $v["paymentTime"]): "（无）";
                $row[$k]["agentName"]   = $agent[$v["agent"]]["pid"] == 0 ? $pagent[$agent[$v["agent"]]["id"]]['agentName']  : $pagent[$agent[$v["agent"]]["pid"]]['agentName'];
                $row[$k]["opt"]         = '<a href="javascript:;" onclick="orderInfo(\''.$v['orderId'].'\',this)">详情</a>';
            }

            //导出
            if($data['export'] == 1){
                $pageSummary = array('amount'=>$sum);
                $col = array('userCode'=>'用户标识符','userName'=>'用户账户', 'agentName'=>'包名称','gameName'=>
                    '游戏名称','channelName'=>'渠道名称','amount'=>'充值金额','subject'=>'商品名称','status'=>'订单状态','orderId'=>'订单号','billNo'=>'游戏订单号','tranId'=>'渠道订单号','orderTypeName'=>'订单类型','payType'=>'充值方式','create'=>'下单时间','payment'=>'支付时间');
                array_unshift($row, $col);
                $pageSummary['userCode']  = '汇总';
                array_push($row,$pageSummary);
                export_to_csv($row,'订单列表',$col);
                exit();
            }

            $arr = array('rows' => $row, 'results' => $count, 'summary'=>array('amount'=>number_format($sum,2)));
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
            $data       = I();
            !$data['game_id'] && exit(json_encode(array('rows'=>array(), 'results'=>0)));

            //搜索条件
            $start      = $data['start']? $data['start']: 0;
            $pageSize   = $data['limit']? $data['limit']: 30;

            $agent_info = $_REQUEST['agent'];
            
            //处理搜索条件
            if(is_array($agent_info) && in_array('--请选择渠道号--', $agent_info)){
                unset($agent_info[array_search('--请选择渠道号--', $agent_info)]);
            }elseif(is_string($agent_info) && $agent_info == '--请选择渠道号--'){
                $agent_info = '';
            }elseif(is_string($agent_info) && !empty($agent_info)){
                $agent_info = explode(',', $agent_info);
            }
            if($agent_info){
                $data['agent'] = $agent_info;
                $map['a.agent'] = array('IN',$data['agent']);
            }else{
                //权限控制
                $map['a.agent'] = array('IN',$this->agentArr);
            }

            
            !is_null($this->gameId) && $map['a.game_id'] = array('in',$this->gameId); //CP权限控制
            $data['game_id'] && $map['a.game_id'] = $data['game_id'];
            $data['channelId'] && $map['a.channel_id'] = $data['channelId'];
            if ($data["startDate"] && $data["endDate"]) {
                $d1 = date_create($data['startDate']);
                $d2 = date_create($data['endDate']);
                $diff = date_diff($d1,$d2);
                if($diff->format("%a") >= 31){
                    exit(json_encode(array('hasError'=>true, 'error'=>'日期跨度不能大于一个月')));
                }
                $map["a.paymentTime"] = array("BETWEEN", array(strtotime($data["startDate"]), strtotime($data["endDate"])));
            } else {
                    exit(json_encode(array('hasError'=>true, 'error'=>'日期必选')));
            }

            $map["a.orderStatus"] =  0; //只要我方收到款的
            $map['a.orderType'] = 0; //正式订单


            //去除三个刷单的非法用户
            $map['a.userName'] = array('not in',array('Lgame41400432','Love100200','Lgame49382694'));
            // $count     = D("ThirdParty/Order")->getIncomeCount($map);
            // $sum       = D("ThirdParty/Order")->getIncomeSum($map);
            $row       = D("ThirdParty/Order")->getIncomeOrderLimit($map, $start, $pageSize);
            $agent     = getDataList("agent", "agent", C("DB_PREFIX_API"));
            $data['export'] == 1 && $channel = getDataList("channel", "id", C("DB_PREFIX_API"));
            foreach ($row as $k => $v) {
                if ($v["orderStatus"]) {
                    $type = "无";
                } else {
                    if ($v["channel_id"] <= 1) {
                        switch ($v["payType"]) {
                            case 1:
                                $type = "支付宝";
                                $row[$k]['realAmount'] = ($v['amount'] - ($v['amount'] * 0.008)); //0.8%
                                $row[$k]['poundage']   = ($v['amount'] * 0.008);//手续费
                                break;
                            case 2:
                                $type = "微信";
                                $row[$k]['realAmount'] = ($v['amount'] - ($v['amount'] * 0.008)); //1.0%
                                $row[$k]['poundage']   = ($v['amount'] * 0.008);//手续费
                                break;
                            case 3:
                                $type = "银联";
                                $row[$k]['realAmount'] = ($v['amount'] - ($v['amount'] * 0.008)); //0.8%
                                $row[$k]['poundage']   = ($v['amount'] * 0.008);//手续费
                                break;
                            case 0:
                                if ($v["type"] == 2) {
                                    $type = "苹果";
                                    $row[$k]['realAmount'] = ($v['amount'] - ($v['amount'] * 0.3)); //30%
                                    $row[$k]['poundage']   = ($v['amount'] * 0.3);//手续费
                                } else {
                                    $type = "测试";
                                    $row[$k]['realAmount'] = 0;
                                    $row[$k]['poundage']   = 0;
                                };
                                break;
                            default:
                                $type = "测试";
                                $row[$k]['realAmount'] = 0;
                                $row[$k]['poundage']   = 0;
                        }
                    } else {
                        $type = $channel[$row['channel_id']]['channelName'];
                    }
                }
                $row[$k]['payType']     = $type;
                $row[$k]["agentName"]   = $agent[$v['agent']]['agentName'];
                $row[$k]["payTime"]     = date("Y/m/d", strtotime($data["startDate"])).'—'.date("Y/m/d", strtotime($data["endDate"]));

                $newRow[$row[$k]["payTime"].'_'.$row[$k]['gameName'].'_'.$row[$k]['agentName'].'_'.$type]['pay'] += $v['amount'];
                $newRow[$row[$k]["payTime"].'_'.$row[$k]['gameName'].'_'.$row[$k]['agentName'].'_'.$type]['realAmount'] += $row[$k]['realAmount'];
                $newRow[$row[$k]["payTime"].'_'.$row[$k]['gameName'].'_'.$row[$k]['agentName'].'_'.$type]['poundage'] += $row[$k]['poundage'];

            }
            unset($row);
            //处理newRow结果集
            foreach ($newRow as $key => $value) {
                $keys = explode('_',$key);
                $row[] = array(
                    'payTime'     => $keys[0],
                    'gameName'    => $keys[1],
                    'agentName'   => $keys[2],
                    'payType'     => $keys[3],
                    'amount'      => $value['pay'],
                    'realAmount'  => $value['realAmount'],
                    'poundage'    => $value['poundage']
                );
            }


            $results = count($row);

            //导出
            if($data['export'] == 1){
                $pageSummary = array('amount'=>array_sum(array_column($row, 'amount')));
                $col = array('payTime'=>'时间','gameName'=>'游戏名称','agentName'=>'包名称','payType'=>'充值方式','amount'=>'充值金额','realAmount'=>'实收金额','poundage'=>'手续费');
                array_unshift($row, $col);
                $pageSummary['payTime']  = '汇总';
                array_push($row,$pageSummary);
                export_to_csv($row,'结算汇总',$col);
                exit();
            }else{
                $row = array_slice($row, $start,$pageSize);
            }

            $arr = array('rows' => $row, 'results' => $results);
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
        $orderId    = I("orderId");
        $order      = D("ThirdParty/Order")->getOrder($orderId);
        $callback   = D("ThirdParty/Callback")->getCallbackOneByOrderId($orderId);
        $agent      = D("ThirdParty/Admin")->commonQuery("agent", array("agent" => $order["agent"]), 0, 1, '*', 'lg_');
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
                $channel    = D("ThirdParty/Admin")->commonQuery("channel", array("id" => $order["channel_id"]), 0, 1, '*', 'lg_');
                $type       = $channel["channelName"];
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
     * 用户列表
     */
    public function user()
    {
        if (IS_POST) {
            $data       = I();
            //搜索条件
            $start      = $data['start']? $data['start']: 0;
            $pageSize   = $data['limit']? $data['limit']: 30;
            $agentStr   = implode("','", $this->agentArr);
            $map["_string"] = "agent IN ('".$agentStr."')";
            $data['userCode'] && $map['userCode'] = $data['userCode'];
            $data['userName'] && $map['userName'] = $data['userName'];
            $data['channelUserCode'] && $map['channelUserCode'] = $data['channelUserCode'];
            $data['channelUserName'] && $map['channelUserName'] = $data['channelUserName'];
            $data['agent'] && $map['agent'] = $data['agent'];
            $data['game_id'] && $map['game_id'] = $data['game_id'];
            $data['udid'] && $map['udid'] = $data['udid'];
            if ($data['mobile']) {
                $map['mobile']          = $data['mobile'];
                $map["mobileStatus"]    = "0";
            }
            if(!$data['userCode'] && !$data['userName']){ //用户标识符和用户名都不存在时选用时间
                if ($data["startDate"] && $data["endDate"]) {
                    $map["createTime"] = array("BETWEEN", array(strtotime($data["startDate"]), strtotime($data["endDate"])));
                } elseif ($data["startDate"]) {
                    $map["createTime"] = array("EGT", strtotime($data["startDate"]));
                } elseif ($data["endDate"]) {
                    $map["createTime"] = array("ELT", strtotime($data["endDate"]));
                }
            }

            $agent      = getDataList("agent", "agent", C("DB_PREFIX_API"));
            $res        = D('Admin')->getBuiList("user", $map, $start, $pageSize, "lg_");
            $results    = $res['count'];
            foreach ($res['list'] as $key => $val){
                $res['list'][$key]['create']    = date('Y-m-d H:i:s', $val['createTime']);
                $res['list'][$key]['login']     = date('Y-m-d H:i:s', $val['lastLogin']);
                $res['list'][$key]['agentName'] = $agent[$val["agent"]]["agentName"];
                $res['list'][$key]['opt']       = '<a href="javascript:;" onclick="userInfo(\''.$val['userCode'].'\',this)">详情</a>';
                if (session("admin.role_id") == 1) {
                    $res['list'][$key]['opt']   .= '&nbsp;<a href="javascript:;" onclick="editPassword(\''.$val['userCode'].'\',this)">改密</a>';
                }
//                $res['list'][$key]['opt']      .= '&nbsp;<a href="javascript:;" onclick="roleInfo(\''.$val['userCode'].'\',this)">角色</a>';
                $rows[] = $res['list'][$key];
            }
            $arr = array('rows'=>$rows, 'results'=>$results);
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
        $userCode   = I("userCode");
        $user       = D("ThirdParty/Admin")->commonQuery("user", array("userCode" => $userCode), 0, 1, "*", "lg_");

        $agent      = D("ThirdParty/Admin")->commonQuery("agent", array("agent" => $user["agent"]), 0, 1, '*', 'lg_');
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
            $data       = I();
            if (!$data['password'] || !$data["userCode"]) {
                IS_AJAX && $this->ajaxReturn(array(
                    'status'    => 0,
                    'info'      => '参数有误'
                ));
                $this->error('参数有误');
            }
            $password = password_hash("Ls_".md5($data["password"]."Cy@mwonv2219jdwjcnsmou29&".$data["password"]), PASSWORD_DEFAULT);
            if(D("ThirdParty/User")->saveUser(array("password" => $password), $data["userCode"])){
                IS_AJAX && $this->ajaxReturn(array(
                    'status'    => 1,
                    'info'      => '修改成功'
                ));
                $this->success('修改成功');
            } else {
                IS_AJAX && $this->ajaxReturn(array(
                    'status'    => 0,
                    'info'      => '修改失败'
                ));
                $this->error('修改失败');
            }
        } else {
            $userCode   = I("userCode");
            $user       = D("ThirdParty/Admin")->commonQuery("user", array("userCode" => $userCode), 0, 1, "*", "lg_");
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
        if(session('admin.role_id') != 1){
            $this->ajaxReturn(array('status'=>0,'info'=> '无权修改'));
        }

        if(!$data['userCode']){
            $this->ajaxReturn(array('status'=> 0,'info'=> 'userCode缺省'));
        }

        $res = D('Admin')->commonExecute('user',array('userCode'=>$data['userCode']),array('status'=>$data['status']),C('DB_PREFIX_API'));
        if($res){
            $this->ajaxReturn(array('status'=> 1,'info'=> '修改成功'));
        }else{
            $this->ajaxReturn(array('status'=> 0,'info'=> '修改失败'));
        }
    }

    /**
     * 角色列表
     */
    public function roleList()
    {
        if (IS_POST) {
            $data       = I();
            //搜索条件
            $start      = $data['start']? $data['start']: 0;
            $pageSize   = $data['limit']? $data['limit']: 30;
            $agentStr   = implode("','", $this->agentArr);
            $map["_string"] = "a.agent IN ('".$agentStr."')";
            $data['agent'] && $map['a.agent'] = array('like','%'.$data['agent'].'%');
            $data['game_id'] && $map['a.game_id'] = $data['game_id'];
            $data['roleName'] && $map['a.roleName'] = array('like','%'.$data['roleName'].'%');
            $data['serverName'] && $map['a.serverName'] = $data['serverName'];
            $data['userCode'] && $map['a.userCode'] = $data['userCode'];
            $data['userName'] && $map['userName'] = $data['userName'];
            if(!$data['userCode'] && !$data['userName']){ //用户标识符和用户名都不存在时选用时间
                if ($data["startDate"] && $data["endDate"]) {
                    $map["a.createTime"] = array("BETWEEN", array(strtotime($data["startDate"]), strtotime($data["endDate"])));
                } elseif ($data["startDate"]) {
                    $map["a.createTime"] = array("EGT", strtotime($data["startDate"]));
                } elseif ($data["endDate"]) {
                    $map["a.createTime"] = array("ELT", strtotime($data["endDate"]));
                }
            }
            $row    = D("ThirdParty/Role")->getRole($map, $start, $pageSize);
//
//            if(!$data['userCode'] && !$data['userName']){
//                $createTime = $map["a.createTime"];
//                unset($map['a.createTime']);
//                $createTime && $map['createTime'] = $createTime;
//            }

            $count  = D("ThirdParty/Role")->getRoleCount($map);
            foreach ($row as $key => $val){
                $row[$key]['create']    = date('Y-m-d H:i:s', $val['createTime']);
                $row[$key]['update']    = date('Y-m-d H:i:s', $val['updateTime']);
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
            $data       = I();
            //搜索条件
            $start      = $data['start']? $data['start']: 0;
            $pageSize   = $data['limit']? $data['limit']: 30;
            $data['userCode'] && $map['userCode'] = $data['userCode'];

            $row    = D("ThirdParty/Role")->getRole($map, $start, $pageSize);
            $count  = D("ThirdParty/Role")->getRoleCount($map);
            foreach ($row as $key => $val){
                $row[$key]['create']    = date('Y-m-d H:i:s', $val['createTime']);
                $row[$key]['update']    = date('Y-m-d H:i:s', $val['updateTime']);
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
        if(IS_POST){
            $data = I();
            !$data['game_id'] && exit(json_encode(array('rows'=>array(), 'results'=>0)));
            $start          = $data["start"]? $data["start"]: 0;
            $pageSize       = $data["limit"]? $data["limit"]: 30;
            $where = '1';
            
            if($data['os'] == 1){
                $andAgent = array_keys(getDataList('agent','agent',C('DB_PREFIX_API'),array('gameType'=>1,'game_id'=>$data['game_id'])));
                $arr = array_intersect($this->agentArr, $andAgent);
                sort($arr);
                
            }elseif($data['os'] == 2){
                $iosAgent = array_keys(getDataList('agent','agent',C('DB_PREFIX_API'),array('gameType'=>2,'game_id'=>$data['game_id'])));
                $arr = array_intersect($this->agentArr, $iosAgent);
                sort($arr);
            }

            if(empty($arr)) $arr = array('-1');

            //权限控制
            if(!$data['os']){
                $where .= ' and agent IN("'.implode('","',$this->agentArr).'")'; 
                $map['agent'] = array('IN',$this->agentArr);
            }else{
                $where .= ' and agent in("'.implode('","',$arr).'")'; 
                $map['agent'] = array('IN',$arr);   
            }

            if($data['game_id']) { 
                $where .= ' and gameId='.$data['game_id']; 
                $map['gameId'] = $data['game_id'];
            }

            if($data['startDate'] && $data['endDate']){
                $where .= ' and dayTime>="'.$data['startDate'].'" and dayTime<"'.date('Y-m-d',strtotime($data['endDate'].'+1 day')).'"';
                $map['dayTime'] = array(array('egt',$data['startDate']), array('lt',date('Y-m-d',strtotime($data['endDate'].'+1 day'))), 'and');
            }

            $res      = D('Admin')->getDeviceRemainData($map,$start,$pageSize,$where); //设备留存数据
            $results = $res['count'];
            $game_list = getDataList('game','id',C('DB_PREFIX_API'));
            $agent_list = getDataList('agent','agent',C('DB_PREFIX_API'));

            foreach ($res['list'] as $key=>$val){
                if($data['lookType'] == 1){
                    $res['list'][$key]['agent'] = $res['list'][$key]['agentName'] = $res['list'][$key]['dayTime']= '-';
                }
                $res['list'][$key]['gameName'] = $game_list[$val['gameId']]['gameName'];
                //处理留存率
                if($data['lookType'] == 2){
                    $remainArr = $this->deviceRemainSet($res['list'][$key]);
                    $rows[] = $remainArr;
                }
            }

            if($data['lookType'] == 1){
                $rows = $this->deviceRemainSetNew($res['list']);
            }

            //显示图表
            if($data['chart'] == 1){
                $deviceChart = $this->deviceChart($rows);
                if($deviceChart === false){
                    $this->ajaxReturn(array('status'=>0,'info'=>array('day'=>array(),'day1'=>array(),'day6'=>array(),'day29'=>array())));
                }
                $this->ajaxReturn(array('status'=>1,'info'=>$deviceChart));
            }

            //数据汇总
            $pagesummary = $this->summarys($rows);
            if($data['export'] == 1){

                $col = array('dayTime'=>'注册日期', /*'agent'=>'渠道号',*/'gameName'=>'游戏名称','newDevice'=>'新增设备数');
                $day_arr = array(1,2,3,4,5,6,7,8,9,13,14,15,29,30,59,89);

                for($i=0;$i<=120;$i++){
                    if(in_array($i, $day_arr)){
                        $col['day'.$i] = ($i+1).'日留存';
                    }
                }
                array_unshift($rows, $col);
                $pagesummary['dayTime'] = '汇总';
                // $pagesummary['agent'] = '-';
                $pagesummary['gameName'] = '-';
                array_push($rows,$pagesummary);
                export_to_csv($rows,'设备留存',$col);
                exit();
            }
            $arr = array('rows'=>$rows ? $rows : array(), 'results'=>$results, 'pageSummary'=>$pagesummary);
            unset($rows,$remainArr);
            exit(json_encode($arr));
        }else{
            if(IS_AJAX){
                $response = $this->fetch();
                $this->ajaxReturn(array('status'=>1,'_html'=>$response));
            }else{
                $this->display();
            }
        }
    }

    //设备留存图表
    protected function deviceChart($data)
    {
        if(!$data) return false;
        $chart = array();

        $chart['day'] = array_column($data, 'dayTime');

        foreach ($data as $key=>$value) {
            $chart['remain']['day1'][]  = $value['day1']+0;
            $chart['remain']['day6'][]  = $value['day6']+0;
            $chart['remain']['day29'][] = $value['day29']+0;
        }

        return $chart;
    }

    /**
     * 新增账户留存统计
     */
    public function userRemain()
    {
        if(IS_POST){
            $data = I();
            !$data['game_id'] && exit(json_encode(array('rows'=>array(), 'results'=>0)));
            $start          = $data["start"]? $data["start"]: 0;
            $pageSize       = $data["limit"]? $data["limit"]: 30;
            $where = '1';
            
            if($data['os'] == 1){
                $andAgent = array_keys(getDataList('agent','agent',C('DB_PREFIX_API'),array('gameType'=>1,'game_id'=>$data['game_id'])));
                $arr = array_intersect($this->agentArr, $andAgent);
                sort($arr);
                
            }elseif($data['os'] == 2){
                $iosAgent = array_keys(getDataList('agent','agent',C('DB_PREFIX_API'),array('gameType'=>2,'game_id'=>$data['game_id'])));
                $arr = array_intersect($this->agentArr, $iosAgent);
                sort($arr);
            }

            if(empty($arr)) $arr = array('-1');

            //权限控制
            if(!$data['os']){
                $where .= ' and agent IN("'.implode('","',$this->agentArr).'")'; 
                $map['agent'] = array('IN',$this->agentArr);
            }else{
                $where .= ' and agent in("'.implode('","',$arr).'")'; 
                $map['agent'] = array('IN',$arr);   
            }

            if($data['game_id']) { 
                $where .= ' and gameId='.$data['game_id']; 
                $map['gameId'] = $data['game_id'];
            }

            if($data['startDate'] && $data['endDate']){
                $where .= ' and dayTime>="'.$data['startDate'].'" and dayTime<"'.date('Y-m-d',strtotime($data['endDate'].'+1 day')).'"';
                $map['dayTime'] = array(array('egt',$data['startDate']), array('lt',date('Y-m-d',strtotime($data['endDate'].'+1 day'))), 'and');
            }

            $res      = D('Admin')->getUserRemainData($map,$start,$pageSize,$where); //用户留存数据
            $results = $res['count'];
            $game_list = getDataList('game','id',C('DB_PREFIX_API'));
            $agent_list = getDataList('agent','agent',C('DB_PREFIX_API'));
         
            foreach ($res['list'] as $key=>$val){
                if($data['lookType'] == 1) {
                    $res['list'][$key]['agent'] = $res['list'][$key]['dayTime'] = '-';
                }
                $res['list'][$key]['agentName'] = $agent_list[$val['agent']]['agentName'];
                $res['list'][$key]['gameName'] = $game_list[$val['gameId']]['gameName'];
                //处理留存率
                if($data['lookType'] == 2){
                    $remainArr = $this->userRemainSet($res['list'][$key]);
                    $rows[] = $remainArr;
                }
            }

            if($data['lookType'] == 1){
                $rows = $this->userRemainSetNew($res['list']);
            }

            //显示图表
            if($data['chart'] == 1){
                $deviceChart = $this->deviceChart($rows); //和设备图表一样
                if($deviceChart === false){
                    $this->ajaxReturn(array('status'=>0,'info'=>array('day'=>array(),'day1'=>array(),'day6'=>array(),'day29'=>array())));
                }
                $this->ajaxReturn(array('status'=>1,'info'=>$deviceChart));
            }

            //数据汇总
            $pagesummary = $this->summarys($rows);
            if($data['export'] == 1){

                $col = array('dayTime'=>'注册日期', /*'agent'=>'渠道号',*/'gameName'=>'游戏名称','newUser'=>'新增账户数');
                $day_arr = array();
                for($i = 0; $i<=90; $i++){
                    $day_arr[] = $i;
                }
                for($i=0;$i<=120;$i++){
                    if(in_array($i, $day_arr)){
                        $col['day'.$i] = ($i+1).'日留存';
                    }
                }
                array_unshift($rows, $col);
                $pagesummary['dayTime'] = '汇总';
                // $pagesummary['agent'] = '-';
                $pagesummary['gameName'] = '-';
                array_push($rows,$pagesummary);
                export_to_csv($rows,'账户留存',$col);
                exit();
            }
            $arr = array('rows'=>$rows ? $rows : array(), 'results'=>$results, 'pageSummary'=>$pagesummary);
            unset($rows,$remainArr);
            exit(json_encode($arr));
        }else{
            if(IS_AJAX){
                $response = $this->fetch();
                $this->ajaxReturn(array('status'=>1,'_html'=>$response));
            }else{
                $this->display();
            }
        }
    }

    /**
     * 活跃玩家概况
     */
    public function actUser()
    {
        if(IS_POST){
            $data = I();
            !$data['game_id'] && exit(json_encode(array('rows'=>array(), 'results'=>0)));
            $start          = $data["start"]? $data["start"]: 0;
            $pageSize       = $data["limit"]? $data["limit"]: 30;
            $where = '1';
            
            if($data['os'] == 1){
                $andAgent = array_keys(getDataList('agent','agent',C('DB_PREFIX_API'),array('gameType'=>1,'game_id'=>$data['game_id'])));
                $arr = array_intersect($this->agentArr, $andAgent);
                sort($arr);
                
            }elseif($data['os'] == 2){
                $iosAgent = array_keys(getDataList('agent','agent',C('DB_PREFIX_API'),array('gameType'=>2,'game_id'=>$data['game_id'])));
                $arr = array_intersect($this->agentArr, $iosAgent);
                sort($arr);
            }

            if(empty($arr)) $arr = array('-1');

            //权限控制
            if(!$data['os']){
                $where .= ' and agent IN("'.implode('","',$this->agentArr).'")'; 
                $map['agent'] = array('IN',$this->agentArr);
            }else{
                $where .= ' and agent in("'.implode('","',$arr).'")'; 
                $map['agent'] = array('IN',$arr);   
            }


            if($data['game_id']) { 
                $where .= ' and gameId='.$data['game_id']; 
                $map['gameId'] = $data['game_id'];
            }

            if($data['startDate'] && $data['endDate']){
                $where .= ' and dayTime>="'.$data['startDate'].'" and dayTime<"'.date('Y-m-d',strtotime($data['endDate'].'+1 day')).'"';
                $map['dayTime'] = array(array('egt',$data['startDate']), array('lt',date('Y-m-d',strtotime($data['endDate'].'+1 day'))), 'and');
            }


            $res      = D('Admin')->getActUserData($map,$start,$pageSize,$where); //用户留存数据
            
            $results = $res['count'];
            $game_list = getDataList('game','id',C('DB_PREFIX_API'));
            $agent_list = getDataList('agent','agent',C('DB_PREFIX_API'));

            foreach ($res['list'] as $key=>$val){

                $data['lookType'] == 1 && $res['list'][$key]['agent'] = '-';
                $data['lookType'] == 1 && $res['list'][$key]['serverName'] = '-';
                //用户新增
                $res['list'][$key]['agentName'] = $data['lookType'] == 1 ? '-' : $agent_list[$val['agent']]['agentName'];
                $res['list'][$key]['gameName'] = $game_list[$val['gameId']]['gameName'];
                $res['list'][$key]['newDevice'] = isset($val['newDevice']) ? $val['newDevice'] : '-';
                $res['list'][$key]['userRate'] = isset($val['newDevice']) ? numFormat(($val['distinctReg']/$val['newDevice']),true) : '-';
                $res['list'][$key]['allUserLogin'] = $val['newUserLogin'] + $val['oldUserLogin'];
                $res['list'][$key]['activeRate'] = numFormat((($val['newUserLogin']+$val['oldUserLogin'])/$val['monthLogin']),true);
            }
            //数据汇总
            $pagesummary = $this->summarys($res['list']);

            if($data['export'] == 1){

                $col = array('dayTime'=>'注册日期', 'gameName'=>'游戏名称','allUserLogin'=>'DAU','newUserLogin'=>'新用户日活跃数','oldUserLogin'=>'老用户日活跃数','monthLogin'=>'MAU','activeRate'=>'DAU/MAU');
                
                array_unshift($res['list'], $col);
                /*$pagesummary['dayTime'] = '汇总';
                array_push($res['list'],$pagesummary);*/
                export_to_csv($res['list'],'活跃用户概况',$col);
                exit();
            }
            $arr = array('rows'=>$res['list'] ? $res['list'] : array(), 'results'=>$results);
            unset($rows,$remainArr);
            exit(json_encode($arr));
        }else{
            if(IS_AJAX){
                $response = $this->fetch();
                $this->ajaxReturn(array('status'=>1,'_html'=>$response));
            }else{
                $this->display();
            }
        }
    }



    /**
     * 用户注册留存统计
     */
    public function userRegRemain()
    {
        if(IS_POST){

            $data = I();
            !$data['game_id'] && exit(json_encode(array('rows'=>array(), 'results'=>0)));
            
            $start      = $data['start']? $data['start']: 0;
            $pageSize   = $data['limit']? $data['limit']: 30;
            $where = '1';
            $where2 = '1';
            if($data['os'] == 1){
                $andAgent = array_keys(getDataList('agent','agent',C('DB_PREFIX_API'),array('gameType'=>1,'game_id'=>$data['game_id'])));
                $arr = array_intersect($this->agentArr, $andAgent);
                sort($arr);
                
            }elseif($data['os'] == 2){
                $iosAgent = array_keys(getDataList('agent','agent',C('DB_PREFIX_API'),array('gameType'=>2,'game_id'=>$data['game_id'])));
                $arr = array_intersect($this->agentArr, $iosAgent);
                sort($arr);
                
            }

            if(false){
                $data['agent'] = $agent_info;
                $where2 = $where .= ' and a.agent in("'.implode('","',$data['agent']).'")'; 
                $map['a.agent'] = array('in',$data['agent']);
            }else{
                //权限控制
                if(!$data['os']){
                    $where2 = $where .= ' and a.agent in("'.implode('","', $this->agentArr).'")';
                    $map['a.agent'] = array('in',$this->agentArr);   
                }else{
                    $where2 = $where .= ' and a.agent in("'.implode('","', $arr).'")';
                    $map['a.agent'] = array('in',$arr);   
                }
            }

            if($data['game_id']) { 
                $where2 = $where .= ' and a.gameId='.$data['game_id']; 
                $map['a.gameId'] = $data['game_id'];
            }

            if($data['startDate'] && $data['endDate']){
                $where2 = $where .= ' and a.dayTime>="'.$data['startDate'].'" and a.dayTime<"'.date('Y-m-d',strtotime($data['endDate'].'+1 day')).'"';
                $map['a.dayTime'] = array(array('egt',$data['startDate']), array('lt',date('Y-m-d',strtotime($data['endDate'].'+1 day'))), 'and');
            }

            if($data['serverId']) { 
                $where .= ' and a.serverId="'.$data['serverId'].'"'; 
                $map['a.serverId'] = $data['serverId'];
            }

            $res      = D('Admin')->getRegChargeData($map,$start,$pageSize,$where,$where2); //用户留存数据
            //区服id为0的充值
            if($map['a.gameId']){
                $map['a.game_id'] = $map['a.gameId'];
                unset($map['a.gameId']);
            }
            // $map['serverId'] = 0;
            $payServer = M('sp_agent_server_pay_day a',C('DB_PREFIX'),'CySlave')->field('SUM(allPay) AS allPay,SUM(allPayUser) AS allPayUser,SUM(newPay) AS newPay,SUM(newPayUser) AS newPayUser,game_id,dayTime')->where($map)->group('game_id,dayTime')->select();
            $results = $res['count'];
            $game_list = getDataList('game','id',C('DB_PREFIX_API'));
            $agent_list = getDataList('agent','agent',C('DB_PREFIX_API'));

            foreach ($res['list'] as $key=>$val){

                if($payServer && $data['lookType'] == 1){
                    foreach($payServer as $k=>$v){
                        if($val['gameId'] == $v['game_id'] && $val['dayTime'] == $v['dayTime']){
                            $val['allPay']     += floatval($v['allPay']);
                            $val['allPayUser'] += $v['allPayUser'];
                            $val['newPay']     += floatval($v['newPay']);
                            $val['newPayUser'] += $v['newPayUser'];
                        }
                    }
                }
                $res['list'][$key]['allPay'] = floatval($val['allPay']);
                $res['list'][$key]['allPayUser'] = $val['allPayUser'];
                $res['list'][$key]['newPay'] = floatval($val['newPay']);
                $res['list'][$key]['newPayUser'] = $val['newPayUser'];

                $data['lookType'] == 1 && $res['list'][$key]['agent'] = '-';
                $data['lookType'] == 1 && $res['list'][$key]['serverName'] = '-';
                $res['list'][$key]['newPay'] = floatval($val['newPay']);
                //用户新增
                $res['list'][$key]['agentName'] = $data['lookType'] == 1 ? '-' : $agent_list[$val['agent']]['agentName'];
                $res['list'][$key]['gameName'] = $game_list[$val['gameId']]['gameName'];
                $res['list'][$key]['newDevice'] = isset($val['newDevice']) ? $val['newDevice'] : '-';
                $res['list'][$key]['userRate'] = isset($val['newDevice']) ? numFormat(($val['distinctReg']/$val['newDevice']),true) : '-';
                $res['list'][$key]['allUserLogin'] = $val['newUserLogin'] + $val['oldUserLogin'];
                $res['list'][$key]['activeRate'] = numFormat((($val['newUserLogin']+$val['oldUserLogin'])/$val['monthLogin']),true);
                //充值概况
                $res['list'][$key]['payRate'] = numFormat(($val['allPayUser']/($val['newUserLogin']+$val['oldUserLogin'])),true);
                $res['list'][$key]['ARPU'] = (false === $num = sprintf("%.2f",$val['allPay']/($val['newUserLogin']+$val['oldUserLogin']))) ? 0 : $num;
                $res['list'][$key]['ARPPU'] = (false === $num = sprintf("%.2f",$val['allPay']/$val['allPayUser'])) ? 0 : $num ;

                //新增充值
                $res['list'][$key]['newPayRate'] = numFormat(($val['newPayUser']/$val['newUser']),true);
                $res['list'][$key]['newARPU'] = (false === $num = sprintf("%.2f",$val['newPay']/$val['newUser'])) ? 0 : $num;
                $res['list'][$key]['newARPPU'] = (false === $num = sprintf("%.2f",$val['newPay']/$val['newPayUser'])) ? 0 : $num;
                //处理留存率
                $remainArr = $this->remain($res['list'][$key]);
                $rows[] = $remainArr;
            }
            //数据汇总
            $pagesummary = $this->summarys($rows);
            if($data['export'] == 1){

                $col = array('dayTime'=>'注册日期', 'gameName'=>'游戏名称', 'newDevice'=>'新增设备数','distinctReg'=>'唯一注册数','newUser'=>'新增用户数','userRate'=>'用户转化率','allUserLogin'=>'总日活跃数','newUserLogin'=>'新用户日活跃数','oldUserLogin'=>'老用户日活跃数','monthLogin'=>'MAU','activeRate'=>'DAU/MAU','allPay'=>'充值总额','allPayUser'=>'充值总账号数','payRate'=>'付费率','ARPU'=>'ARPU','ARPPU'=>'ARPPU','newPay'=>'新用户充值总额','newPayUser'=>'新用户充值总账号数','newPayRate'=>'新增付费率','newARPU'=>'新增ARPU','newARPPU'=>'新增ARPPU');
                $day_arr = array(1,6,13,29);

                for($i=0;$i<=120;$i++){
                    if(in_array($i, $day_arr)){
                        $col['day'.$i] = ($i+1).'日留存';
                    }
                }
                array_unshift($rows, $col);
                $pagesummary['dayTime'] = '汇总';
                $pagesummary['agent'] = '-';
                $pagesummary['gameName'] = '-';
                $pagesummary['serverName'] = '-';
                array_push($rows,$pagesummary);
                export_to_csv($rows,'运营数据概况',$col);
                exit();
            }
            $arr = array('rows'=>$rows ? $rows : array(), 'results'=>$results, 'pageSummary'=>$pagesummary);
            unset($rows,$remainArr);
            exit(json_encode($arr));
        }else{
            if(IS_AJAX){
                $response = $this->fetch();
                $this->ajaxReturn(array('status'=>1,'_html'=>$response));
            }else{
                $this->display();
            }
        }
    }


    //用户注册留存数据汇总
    private function summarys($data)
    {

        $sum = array();
        $data_num = count($data);
        $day_arr = array();
        for($i = 0; $i<=90; $i++){
            $day_arr[] = 'day'.$i;
        }
        $day_num = array();
        //开始统计
        foreach ($data as $k => $val) {
            $sum['newDevice']    += $val['newDevice'];
            $sum['allPay']       += $val['allPay'];
            $sum['distinctReg']  += $val['distinctReg'];
            $sum['newUser']      += $val['newUser'];
            $sum['payRate']      += $val['payRate'];
            $sum['ARPU']         += $val['ARPU'];
            $sum['ARPPU']        += $val['ARPPU'];
            $sum['newPayRate']   += $val['newPayRate'];
            $sum['newARPU']      += $val['newARPU'];
            $sum['newARPPU']     += $val['newARPPU'];
            $sum['newPay']        = '-';
            $sum['allPayUser']    = '-';
            $sum['newPayUser']    = '-';
            $sum['newUserLogin']  = '-';
            $sum['oldUserLogin']  = '-';
            $sum['monthLogin']    = '-';
            foreach ($val as $key => $value) {
                if(in_array($key, $day_arr)){
                    $sum[$key] += $value;
                    $value > 0 && $day_num[$key] ++;
                }
            }
        }
        //开始计算
        
        //用户新增
        $sum['userRate'] = '-';
        $sum['allUserLogin'] = '-';
        $sum['activeRate'] = '-';

        //充值概况平均值
        $sum['payRate'] = sprintf("%.2f",$sum['payRate']/$data_num).'%';
        $sum['ARPU'] = sprintf("%.2f",$sum['ARPU']/$data_num);
        $sum['ARPPU'] = sprintf("%.2f",$sum['ARPPU']/$data_num);

        //新增充值平均值
        $sum['newPayRate'] = sprintf("%.2f",$sum['newPayRate']/$data_num).'%';
        $sum['newARPU'] = sprintf("%.2f",$sum['newARPU']/$data_num);
        $sum['newARPPU'] = sprintf("%.2f",$sum['newARPPU']/$data_num);

        //留存平均值
        foreach ($sum as $k => $v) {
            if(in_array($k, $day_arr)){
                $sum[$k] = sprintf("%.2f",$v/$day_num[$k]).'%';
            }
        }
        return $sum;
    }

    //处理留存数据
    private function remain($info)
    {
        if(empty($info)) return false;
        for($i=0;$i<=120;$i++){
            if(isset($info['day'.$i])){
                $info['day'.$i] = numFormat($info['day'.$i]/$info['newUser'],true);
            }
        }
        return $info;
    }

    //处理新增用户留存数据 $encode=0输出%，1输出值
    private function userRemainSetNew($info,$encode = 0)
    {

        if(empty($info)) return false;
        $row = $newUser = $remain = $allNewUser = $newInfo = array();

        foreach ($info as $key => $value) {
            for($i=0;$i<=120;$i++){
                if(isset($value['day'.$i]) && $value['day'.$i] > 0){
                    $newInfo[$value['gameId']]['agentName'] = $value['agentName'];
                    $newInfo[$value['gameId']]['gameName'] = $value['gameName'];

                    //计算汇总的新增人数
                    $newUser[$value['gameId']]['day'.$i] += $value['newUser'];
                    //计算汇总的留存人数
                    $remain[$value['gameId']]['day'.$i] += $value['day'.$i];
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

            for($i=0;$i<=120;$i++){
                if(isset($v['day'.$i])){
                    if($encode == 0){
                        $newInfo[$k]['day'.$i] = numFormat($remain[$k]['day'.$i]/$v['day'.$i],true);
                    }elseif($encode == 1){
                        $newInfo[$k]['day'.$i] = numFormat($remain[$k]['day'.$i]/$v['day'.$i],true)+0;
                    }
                }else{
                    $newInfo[$k]['day'.$i] = '0.00%';
                }
            }
            $newInfo[$k]['newUser'] += $allNewUser[$k];
            $newInfo[$k]['dayTime'] = '-';

        }
        sort($newInfo);
        unset($info);
        return $newInfo;
    }

    //处理新增用户留存数据 $encode=0输出%，1输出值
    private function userRemainSet($info,$encode = 0)
    {

        if(empty($info)) return false;
        for($i=0;$i<=120;$i++){
            if(isset($info['day'.$i])){
                if($encode == 0){
                    $info['day'.$i] = numFormat($info['day'.$i]/$info['newUser'],true);
                }elseif($encode == 1){
                    $info['day'.$i] = numFormat($info['day'.$i]/$info['newUser'],true)+0;
                }
            }
        }
        return $info;
    }

    //处理新增设备留存数据 $encode=0输出%，1输出值
    private function deviceRemainSetNew($info,$encode = 0)
    {

        if(empty($info)) return false;
        $row = $newDevice = $remain = $allNewDevice = $newInfo = array();

        foreach ($info as $key => $value) {
            for($i=0;$i<=120;$i++){
                if(isset($value['day'.$i]) && $value['day'.$i] > 0){
                    $newInfo[$value['gameId']]['agentName'] = $value['agentName'];
                    $newInfo[$value['gameId']]['gameName'] = $value['gameName'];

                    //计算汇总的新增人数
                    $newDevice[$value['gameId']]['day'.$i] += $value['newDevice'];
                    //计算汇总的留存人数
                    $remain[$value['gameId']]['day'.$i] += $value['day'.$i];
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

            for($i=0;$i<=120;$i++){
                if(isset($v['day'.$i])){
                    if($encode == 0){
                        $newInfo[$k]['day'.$i] = numFormat($remain[$k]['day'.$i]/$v['day'.$i],true);
                    }elseif($encode == 1){
                        $newInfo[$k]['day'.$i] = numFormat($remain[$k]['day'.$i]/$v['day'.$i],true)+0;
                    }
                }else{
                    $newInfo[$k]['day'.$i] = '0.00%';
                }
            }
            $newInfo[$k]['newDevice'] += $allNewDevice[$k];
            $newInfo[$k]['dayTime'] = '-';

        }
        sort($newInfo);
        unset($info);
        return $newInfo;
    }

    //处理设备留存数据
    private function deviceRemainSet($info)
    {

        if(empty($info)) return false;
        for($i=0;$i<=120;$i++){
            if(isset($info['day'.$i])){
                $info['day'.$i] = numFormat($info['day'.$i]/$info['newDevice'],true);
            }
        }
        return $info;
    }

    

    //处理ltv数据
    private function ltvRemain($info,$lookType)
    {
        if(empty($info)) return false;
        $allmoney = 0;
        if($lookType=='1'){
          foreach ($info as $k => $v) {
              $days = (strtotime(date('Y-m-d')) - strtotime($v['dayTime'])) / 86400;
              for($i=0;$i<$days;$i++){
                  $allmoney += $v['day'.$i]; //每一天的充值金额累计起来
                  if(isset($v['day'.$i])){
                      $info[$k]['ltv'.$i] = numFormat($allmoney/$v['newUser']) ? numFormat($allmoney/$v['newUser']) : 0;
                  }
              }
              $info[$k]['allmoney'] = $allmoney;
              $allmoney = 0;
          }  
        }elseif($lookType=='2'){
            foreach ($info as $k => $v) {
                $days = (strtotime(date('Y-m-d')) - strtotime($v['dayTime'])) / 86400;
                for($i=0;$i<$days;$i++){
                    $allmoney += $v['day'.$i]; //每一天的充值金额累计起来
                    if(isset($v['day'.$i])){
                        $info[$k]['ltv'.$i] = $v['day'.$i] ? $v['day'.$i] : 0;
                    }
                }
                $info[$k]['allmoney'] = $allmoney;
                $allmoney = 0;
            }
        }elseif ($lookType=='3') {
            foreach ($info as $k => $v) {
                $days = (strtotime(date('Y-m-d')) - strtotime($v['dayTime'])) / 86400;
                for($i=0;$i<$days;$i++){
                    $allmoney += $v['day'.$i]; //每一天的充值金额累计起来
                    if(isset($v['user'.$i])){
                        if($v['dau'.$i]==0){
                            if($i==0){
                                $info[$k]['ltv'.$i] = numFormat($v['user'.$i]*100/$v['newUser']) ? numFormat($v['user'.$i]*100/$v['newUser']) : 0;
                            }else{
                                $info[$k]['ltv'.$i] = 0;
                            }
                        }else{
                            $info[$k]['ltv'.$i] = numFormat($v['user'.$i]/$v['dau'.$i]*100) ? numFormat($v['user'.$i]*100/$v['dau'.$i]) : 0;                         
                        }
                    }
                }
                $info[$k]['allmoney'] = $allmoney;
                $allmoney = 0;
            }
        }else{
            foreach ($info as $k => $v) {
                $days = (strtotime(date('Y-m-d')) - strtotime($v['dayTime'])) / 86400;
                for($i=0;$i<$days;$i++){
                    $allmoney += $v['day'.$i]; //每一天的充值金额累计起来
                    if(isset($v['day'.$i])){
                        if($v['day'.$i]==0){
                            if($i==0){
                                $info[$k]['ltv'.$i] = numFormat($v['day'.$i]/$v['newUser']) ? numFormat($v['day'.$i]/$v['newUser']) : 0;
                            }else{
                                $info[$k]['ltv'.$i] = 0;
                            }
                        }else{
                            $info[$k]['ltv'.$i] = numFormat($v['day'.$i]/$v['dau'.$i]) ? numFormat($v['day'.$i]/$v['dau'.$i]) : 0;
                        }
                    }
                }
                $info[$k]['allmoney'] = $allmoney;
                $allmoney = 0;
            }
        }

        return $info;
    }

    //ltv数据汇总
    private function ltvSummarys($data,$lookType)
    {
        $sum = array();
        $ltv_sum = array();
        $sum_user = array();
        $sum_dau = array();
        $day_num = array();
        $day_arr = array();
        $user_arr = array();
        $dau_arr = array();
        for($i = 0; $i<=90; $i++){
            $day_arr[] = "day"+$i;
            $ltv_arr[] = "ltv"+$i;
            $user_arr[] = "user"+$i;
            $dau_arr[] = "dau"+$i;
        }

        $allmoney = 0;

        //开始统计
        foreach ($data as $k => $val) {
            $sum['newUser'] += $val['newUser'];
            $allmoney += $val['allmoney'];
            foreach ($val as $key => $value) {
                if(in_array($key, $day_arr)){
                   $sum[$key] += $value;
                }
                if(in_array($key, $user_arr)){
                   $sum_user[$key] += $value;
                }
                if(in_array($key, $dau_arr)){
                   $sum_dau[$key] += $value;
                }
                if(in_array($key, $ltv_arr)){
                    $ltv_sum[$key] += $value;
                    $value > 0 &&  $day_num[$key] ++;
                }
            }
        }

        $days = (strtotime(date('Y-m-d')) - strtotime($data[0]['dayTime'])) / 86400;
        if($lookType==1){
            //ltv汇总
            for($i=0;$i<$days;$i++){
                if(isset($ltv_sum['ltv'.$i])){
                    $info['ltv'.$i] = sprintf("%.2f",$ltv_sum['ltv'.$i]/$day_num['ltv'.$i]);
                }
            }
        }elseif($lookType==2){
            for($i=0;$i<$days;$i++){
                if(isset($sum['day'.$i])){
                    $info['ltv'.$i] = $sum['day'.$i] ? $sum['day'.$i] : 0;
                }
            }
        }elseif($lookType==3){
            for($i=0;$i<$days;$i++){
                if(isset($sum_dau['dau'.$i])){
                    $info['ltv'.$i] = numFormat($sum_user['user'.$i]/$sum_dau['dau'.$i],true) ? numFormat($sum_user['user'.$i]/$sum_dau['dau'.$i],true) : '0%';
                }
            }
        }else{
            for($i=0;$i<$days;$i++){
                if(isset($sum_dau['dau'.$i])){
                    $info['ltv'.$i] = numFormat($sum['day'.$i]/$sum_dau['dau'.$i]) ? numFormat($sum['day'.$i]/$sum_dau['dau'.$i]) : 0;
                }
            }
        }
        $info['newUser'] = $sum['newUser'];
        $info['allmoney'] = $sum['allmoney'];
        return $info;
    }

    //ltv图表数据格式处理
    private function ltvChart($data)
    {
        if(!$data) return false;
        $chart = array();
        //ltv key
        //$_key = array('1','2','3','4','5','6','7','8','9','10','11','12','13','14','15','30','60','90');
        $_key = array();
        for($i = 0; $i<=90; $i++){
            $_key[] = $i;
        }
        foreach ($data as $k => $v) {
            $chart['dayTime'][] = $v['dayTime'];
            foreach ($_key as $key => $value) {
               $chart['ltv'.$value][] = $v['ltv'.($value-1)];
            }
        }

        foreach ($_key as $k => $v) {
            $_key[$k] = 'ltv'.$v;
        }
        $chart['key'] = $_key;

        foreach ($_key as $k => $v) {
            $chart['data'][] = array('name'=>$v,'type'=>'line','smooth'=>true,'data'=>$chart[$v]);
            unset($chart[$v]);
        }
        return $chart;           
    }

    /**
     * 渠道数据统计
     */
    public function agentDataCount()
    {
        if(IS_POST){
            $data = I();
            $agent_info = $_REQUEST['agent'];
            !$data['game_id'] && exit(json_encode(array('rows'=>array(), 'results'=>0)));
            //处理搜索条件
            if(is_array($agent_info) && in_array('--请选择渠道号--', $agent_info)){
                unset($agent_info[array_search('--请选择渠道号--', $agent_info)]);
            }elseif(is_string($agent_info) && $agent_info == '--请选择渠道号--'){
                $agent_info = '';
            }elseif(is_string($agent_info) && !empty($agent_info)){
                $agent_info = explode(',', $agent_info);
            }
            $start      = $data['start']? $data['start']: 0;
            $pageSize   = $data['limit']? $data['limit']: 30;
            $where = $where2 = '1';
            if($agent_info){
                $data['agent'] = $agent_info;
                $where2 = $where .= ' and agent in("'.implode('","',$data['agent']).'")'; 
            }else{
                //权限控制
                $where2 = $where .= ' and agent in("'.implode('","', $this->agentArr).'")';
            }

            

            if($data['startDate'] && $data['endDate']){
                $where2 = $where .= ' and dayTime>="'.$data['startDate'].'" and dayTime<"'.date('Y-m-d',strtotime($data['endDate'].'+1 day')).'"';
            }

            if($data['serverId']) { 
                $where2 = $where .= ' and serverId="'.$data['serverId'].'"'; 
            }

            if($data['game_id']) { 
                $where  .= ' and gameId='.$data['game_id']; 
                $where2 .= ' and game_id='.$data['game_id']; 
            }
            $game_list    = getDataList('game','id',C('DB_PREFIX_API'));
            $agent_list    = getDataList('agent','agent',C('DB_PREFIX_API'));
            $res          = D('Admin')->getAgentDataCount($start,$pageSize,$where,$where2); //渠道充值分布数据
            $results      = $res['count']['total'];        //总行数
            $totalMoney   = $res['count']['allPay'];       //总金额
            $totalPayUser = $res['count']['allPayUser'];   //总充值账号数
            $totalLogin   = $res['count']['allUserLogin']; //总活跃
            $totalnewUser = $res['count']['newUser'];      //总新增人数

            foreach ($res['list'] as $key=>$val){
                if($data['lookType'] == 1) $res['list'][$key]['dayTime'] = '-';
                $res['list'][$key]['newDevice']  = intval($val['newDevice']);
                $res['list'][$key]['newUser']    = intval($val['newUser']);
                $res['list'][$key]['userRate']   = numFormat(($val['newUser']/$totalnewUser),true);
                $res['list'][$key]['loginRate']  = numFormat(($val['allUserLogin']/$totalLogin),true);
                $res['list'][$key]['chargeRate'] = numFormat(($val['allPay']/$totalMoney),true); 
                $res['list'][$key]['payRate']    = numFormat(($val['allPayUser']/$val['allUserLogin']),true);
                $res['list'][$key]['allPay']     = floatval($val['allPay']);
                
                $res['list'][$key]['ARPU']  = sprintf("%.2f",$val['allPay']/$val['allUserLogin']);
                $res['list'][$key]['ARPPU'] = sprintf("%.2f",$val['allPay']/$val['allPayUser']);

                $res['list'][$key]['gameName']   = $game_list[$val['gameId']]['gameName'];
                $res['list'][$key]['agentName']  = $agent_list[$val['agent']]['agentName'];
                if($data['export'] == 1){
                    $remainArr = $this->userRemainSet($res['list'][$key]);
                }else{
                    $remainArr = $this->userRemainSet($res['list'][$key],1);
                }
                $rows[] = $remainArr;
            }
            // $rows = $res['list'];
            //显示图表
            if($data['chart'] == 1){
                $agentPayChart = $this->agentPayChart($rows,$totalMoney);
                $agentPayChart['pageCount'] = ceil($results/$pageSize);
                if($agentPayChart === false){
                    $this->ajaxReturn(array('status'=>0,'info'=>array('agent'=>array(),'rate'=>array(),'data'=>array())));
                }
                $this->ajaxReturn(array('status'=>1,'info'=>$agentPayChart));
            }
            //数据汇总
            $pageSummary = array('newDevice'=>array_sum(array_column($res['list'], 'newDevice')),'newUser'=>array_sum(array_column($res['list'], 'newUser')),'allUserLogin'=>array_sum(array_column($res['list'], 'allUserLogin')),'allPayUser'=>array_sum(array_column($res['list'], 'allPayUser')),'allPay'=>array_sum(array_column($res['list'], 'allPay')),'ARPU'=>sprintf("%.2f",(array_sum(array_column($res['list'], 'ARPU')))/count($res['list'])),'ARPPU'=>sprintf("%.2f",(array_sum(array_column($res['list'], 'ARPPU')))/count($res['list'])));
            if($data['export'] == 1){
                $col = array('dayTime'=>'统计日期','agent'=>'渠道号','agentName'=>'包名称','gameName'=>'游戏名称','newDevice'=>'激活设备数','newUser'=>'新增用户数','userRate'=>'新增用户占比','allUserLogin'=>'活跃用户数','loginRate'=>'活跃用户占比','allPay'=>'充值金额','chargeRate'=>'充值占比','allPayUser'=>'充值用户数','payRate'=>'付费率','ARPU'=>'ARPU','ARPPU'=>'ARPPU');
                $day_arr = array(1,6,13,29);

                for($i=0;$i<=120;$i++){
                    if(in_array($i, $day_arr)){
                        $col['day'.$i] = ($i+1).'日留存';
                    }
                }

                array_unshift($rows, $col);
                $pageSummary['dayTime']  = '汇总';
                array_push($rows,$pageSummary);
                export_to_csv($rows,'渠道数据统计',$col);
                exit();
            }
            $arr = array('rows'=>$rows ? $rows : array(), 'results'=>$results, 'pageSummary'=>$pageSummary);
            unset($rows,$remainArr);
            exit(json_encode($arr));
        }else{
            if(IS_AJAX){
                $response = $this->fetch();
                $this->ajaxReturn(array('status'=>1,'_html'=>$response));
            }else{
                $this->display();
            }   
        }
    }

    /**
     * 渠道充值分布统计
     */
    public function agentPay()
    {
        if(IS_POST){
            $data = I();
            $agent_info = $_REQUEST['agent'];
            !$data['game_id'] && exit(json_encode(array('rows'=>array(), 'results'=>0)));
            //处理搜索条件
            if(is_array($agent_info) && in_array('--请选择渠道号--', $agent_info)){
                unset($agent_info[array_search('--请选择渠道号--', $agent_info)]);
            }elseif(is_string($agent_info) && $agent_info == '--请选择渠道号--'){
                $agent_info = '';
            }elseif(is_string($agent_info) && !empty($agent_info)){
                $agent_info = explode(',', $agent_info);
            }

            $start      = $data['start']? $data['start']: 0;
            $pageSize   = $data['limit']? $data['limit']: 30;
            $where = '1';
            if($agent_info){
                $data['agent'] = $agent_info;
                $where .= ' and agent in("'.implode('","',$data['agent']).'")'; 
                $map['agent'] = array('in',$data['agent']);
            }else{
                //权限控制
                $where .= ' and agent in("'.implode('","', $this->agentArr).'")';
                $map['agent'] = array('in',$this->agentArr);
            }

            if($data['game_id']) { 
                $where .= ' and game_id='.$data['game_id']; 
                $map['game_id'] = $data['game_id'];
            }

            if($data['startDate'] && $data['endDate']){
                $where .= ' and dayTime>="'.$data['startDate'].'" and dayTime<"'.date('Y-m-d',strtotime($data['endDate'].'+1 day')).'"';
                $map['dayTime'] = array(array('egt',$data['startDate']), array('lt',date('Y-m-d',strtotime($data['endDate'].'+1 day'))), 'and');
            }

            if($data['serverId']) { 
                $where .= ' and serverId="'.$data['serverId'].'"'; 
                $map['serverId'] = $data['serverId'];
            }
            $game_list  = getDataList('game','id',C('DB_PREFIX_API'));
            $res        = D('Admin')->getAgentPayData($map,$start,$pageSize,$where); //渠道充值分布数据
            $results    = $res['count']['total']; //总行数
            $totalMoney = $res['count']['totalMoney']; //总金额
            foreach ($res['list'] as $key=>$val){
                $res['list'][$key]['totalPay'] = floatval($val['totalPay']);

                $res['list'][$key]['rate']      = numFormat(($val['totalPay']/$totalMoney),true);
                $res['list'][$key]['gameName']  = $game_list[$val['game_id']]['gameName'];
            }
            $rows = $res['list'];
            //显示图表
            if($data['chart'] == 1){
                $agentPayChart = $this->agentPayChart($rows,$totalMoney);
                $agentPayChart['pageCount'] = ceil($results/$pageSize);
                if($agentPayChart === false){
                    $this->ajaxReturn(array('status'=>0,'info'=>array('agent'=>array(),'rate'=>array(),'data'=>array())));
                }
                $this->ajaxReturn(array('status'=>1,'info'=>$agentPayChart));
            }
            //数据汇总
            $pageSummary = array('totalPay'=>sprintf("%.2f", array_sum(array_column($res['list'], 'totalPay'))));
            if($data['export'] == 1){
                $col = array('gameName'=>'游戏名称','agent'=>'渠道号', 'totalPay'=>'充值金额','rate'=>'百分比');
                array_unshift($rows, $col);
                $pageSummary['gameName']  = '汇总';
                array_push($rows,$pageSummary);
                export_to_csv($rows,'渠道充值分布',$col);
                exit();
            }
            $arr = array('rows'=>$rows ? $rows : array(), 'results'=>$results, 'pageSummary'=>$pageSummary);
            unset($rows,$remainArr);
            exit(json_encode($arr));
        }else{
            if(IS_AJAX){
                $response = $this->fetch();
                $this->ajaxReturn(array('status'=>1,'_html'=>$response));
            }else{
                $this->display();
            }   
        }
    }

    //渠道充值分布图表数据格式处理
    private function agentPayChart($data,$totalMoney)
    {
        if(!$data) return false;
        rsort($data);
        $chart = array();
        foreach ($data as $key => $value) {
            $chart['agent'][] = $value['agent'];
            $chart['data'][]  = $value['totalPay'];
            $chart['rate'][]  = numFormat(($value['totalPay']/$totalMoney),true);
        }
        return $chart;        
    }

    /**
     * 充值等级分布统计
     */
    public function payLevel()
    {
        if(IS_POST){
            $data = I();
            !$data['game_id'] && exit(json_encode(array('rows'=>array(), 'results'=>0)));
            $start          = $data["start"]? $data["start"]: 0;
            $pageSize       = $data["limit"]? $data["limit"]: 30;
            $where = '1';
            
            if($data['os'] == 1){
                $andAgent = array_keys(getDataList('agent','agent',C('DB_PREFIX_API'),array('gameType'=>1,'game_id'=>$data['game_id'])));
                $arr = array_intersect($this->agentArr, $andAgent);
                sort($arr);
                
            }elseif($data['os'] == 2){
                $iosAgent = array_keys(getDataList('agent','agent',C('DB_PREFIX_API'),array('gameType'=>2,'game_id'=>$data['game_id'])));
                $arr = array_intersect($this->agentArr, $iosAgent);
                sort($arr);
            }

            if(empty($arr)) $arr = array('-1');

            //权限控制
            if(!$data['os']){
                $where .= ' AND agent IN("'.implode('","',$this->agentArr).'")'; 
                $map['agent'] = array('IN',$this->agentArr);
            }else{
                $where .= ' AND agent in("'.implode('","',$arr).'")';
                $map['agent'] = array('IN',$arr);
            }
            
            if($data['game_id']) { 
                $where .= ' and game_id='.$data['game_id']; 
                $map['game_id'] = $data['game_id'];
            }

            if($data['startDate'] && $data['endDate']){
                $where .= ' and dayTime>="'.$data['startDate'].'" and dayTime<"'.date('Y-m-d',strtotime($data['endDate'].'+1 day')).'"';
                $map['dayTime'] = array(array('egt',$data['startDate']), array('lt',date('Y-m-d',strtotime($data['endDate'].'+1 day'))), 'and');
            }

            if($data['serverId']) { 
                $where .= ' and serverId="'.$data['serverId'].'"'; 
                $map['serverId'] = $data['serverId'];
            }
            $game_list  = getDataList('game','id',C('DB_PREFIX_API'));
            $res        = D('Admin')->getPayLevelData($map,$start,$pageSize,$where); //充值等级分布数据
            $results    = $res['count']['total']; //总行数
            $totalMoney = $res['count']['totalMoney']; //总金额
            $totalUser  = $res['count']['totalUser']; //总用户
            $payRate    = 0;
            $userRate   = 0;
            foreach ($res['list'] as $key=>$val){
                $payRate  += $res['list'][$key]['payRate']   = numFormat(($val['totalPay']/$totalMoney),true);
                $userRate += $res['list'][$key]['userRate']  = numFormat(($val['totalUser']/$totalUser),true);
                $res['list'][$key]['totalPay']  = floatval($val['totalPay']);
                $res['list'][$key]['userRate']  = numFormat(($val['totalUser']/$totalUser),true);
                $res['list'][$key]['gameName']  = $game_list[$val['game_id']]['gameName'];
            }
            $rows = $res['list'];
            //显示图表
            if($data['chart'] == 1){
                $payLevelChart = $this->payLevelChart($rows,$totalMoney);
                if($payLevelChart === false){
                    $this->ajaxReturn(array('status'=>0,'info'=>array('level'=>0,'pay'=>array('payMax'=>0,'totalPay'=>array()),'user'=>array('userMax'=>0,'totalUser'=>array()))));
                }
                $this->ajaxReturn(array('status'=>1,'info'=>$payLevelChart));
            }
            //数据汇总
            $pageSummary = array('totalPay'=>sprintf("%.2f", array_sum(array_column($res['list'], 'totalPay'))) ,'totalUser'=>sprintf("%.2f",array_sum(array_column($res['list'], 'totalUser'))),'payRate'=>number_format($payRate/count($res['list']),2).'%','userRate'=>number_format($userRate/count($res['list']),2).'%');
            if($data['export'] == 1){
                $col = array('gameName'=>'游戏名称','level'=>'等级', 'totalPay'=>'充值金额','payRate'=>'充值金额占比','totalUser'=>'充值账号数','userRate'=>'账号数占比');
                array_unshift($rows, $col);
                $pageSummary['gameName']  = '汇总';
                array_push($rows,$pageSummary);
                export_to_csv($rows,'充值等级分布',$col);
                exit();
            }
            $arr = array('rows'=>$rows ? $rows : array(), 'results'=>$results, 'pageSummary'=>$pageSummary);
            unset($rows,$remainArr);
            exit(json_encode($arr));
        }else{
            if(IS_AJAX){
                $response = $this->fetch();
                $this->ajaxReturn(array('status'=>1,'_html'=>$response));
            }else{
                $this->display();
            }   
        }
    }

    //充值等级分布图表数据格式处理
    private function payLevelChart($data,$totalMoney)
    {
        if(!$data) return false;
        $chart = array();
        $level = array_column($data, 'level');
        rsort($level);
        $maxLevel = $level[0];
        //循环生成等级
        for ($i=0; $i <=$maxLevel ; $i++) { 
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
        $totalPay  = array_column($data, 'totalPay','level'); //每个等级充值金额
        $totalUser = array_column($data, 'totalUser','level');//每个等级充值人数

        foreach ($chart['level'] as $value) {
            $chart['pay']['totalPay'][]    = is_null($totalPay[$value]) ? 0 : $totalPay[$value];
            $chart['user']['totalUser'][]  = is_null($totalUser[$value]) ? 0 : $totalUser[$value];
        }

        return $chart;        
    }

    /**
     * 活跃玩家等级分布统计
     */
    public function actPlayer()
    {
        if(IS_POST){
            $data = I();
            !$data['game_id'] && exit(json_encode(array('rows'=>array(), 'results'=>0)));
            $start          = $data["start"]? $data["start"]: 0;
            $pageSize       = $data["limit"]? $data["limit"]: 30;
            $where = '1';
            
            if($data['os'] == 1){
                $andAgent = array_keys(getDataList('agent','agent',C('DB_PREFIX_API'),array('gameType'=>1,'game_id'=>$data['game_id'])));
                $arr = array_intersect($this->agentArr, $andAgent);
                sort($arr);
                
            }elseif($data['os'] == 2){
                $iosAgent = array_keys(getDataList('agent','agent',C('DB_PREFIX_API'),array('gameType'=>2,'game_id'=>$data['game_id'])));
                $arr = array_intersect($this->agentArr, $iosAgent);
                sort($arr);
            }

            if(empty($arr)) $arr = array('-1');

            //权限控制
            if(!$data['os']){
                $where .= ' AND agent IN("'.implode('","',$this->agentArr).'")'; 
            }else{
                $where .= ' AND agent in("'.implode('","',$arr).'")';
            }
            
            if($data['game_id']) { 
                $where .= ' and game_id='.$data['game_id']; 
            }

            if($data['startDate']){
                $where .= ' and updateTime>="'.strtotime($data['startDate']).'" and updateTime<"'.strtotime($data['startDate'].'+1 day').'"';
            }

            if($data['serverId']) { 
                $where .= ' and serverId="'.$data['serverId'].'"'; 
            }
            $game_list   = getDataList('game','id',C('DB_PREFIX_API'));
            $res         = D('Admin')->getActPlayerData($start,$pageSize,$where); //活跃用户等级分布数据
            $results     = $res['count']['total']; //总行数
            $totalUser   = $res['count']['totalUser']; //总用户

            foreach ($res['list'] as $key=>$val){
                $res['list'][$key]['actUserRate'] = $data['export'] == 1 ? floatval(sprintf("%.2f",($val['totalUser']/$totalUser)*100)).'%' : floatval(sprintf("%.2f",($val['totalUser']/$totalUser)*100));

                $res['list'][$key]['totalUser']   = intval($val['totalUser']);
                $res['list'][$key]['gameName']    = $game_list[$val['game_id']]['gameName'];
            }
            $rows = $res['list'];
            //显示图表
            if($data['chart'] == 1){
                $actPlayerChart = $this->actPlayerChart($rows,$totalUser);
                if($actPlayerChart === false){
                    $this->ajaxReturn(array('status'=>0,'info'=>array('level'=>0,'user'=>array('userMax'=>0,'totalUser'=>array(),'rate'=>array()))));
                }
                $this->ajaxReturn(array('status'=>1,'info'=>$actPlayerChart));
            }
            //数据汇总
            $pageSummary = array('totalUser'=>sprintf("%.2f",array_sum(array_column($res['list'], 'totalUser'))));
            if($data['export'] == 1){
                $col = array('gameName'=>'游戏名称','level'=>'等级', 'totalUser'=>'账号数','actUserRate'=>'账号数占比');
                array_unshift($rows, $col);
                $pageSummary['gameName']  = '汇总';
                array_push($rows,$pageSummary);
                export_to_csv($rows,'活跃玩家等级分布',$col);
                exit();
            }
            $arr = array('rows'=>$rows ? $rows : array(), 'results'=>$results, 'pageSummary'=>$pageSummary);
            unset($rows,$remainArr);
            exit(json_encode($arr));
        }else{
            if(IS_AJAX){
                $response = $this->fetch();
                $this->ajaxReturn(array('status'=>1,'_html'=>$response));
            }else{
                $this->display();
            }   
        }
    }

    //活跃玩家图表数据格式处理
    private function actPlayerChart($data,$totalUser)
    {
        if(!$data) return false;
        $chart = array();
        $level = array_column($data, 'level');
        rsort($level);
        $maxLevel = $level[0];
        //循环生成等级
        for ($i=0; $i <=$maxLevel ; $i++) { 
            $chart['level'][] = $i;
        }

        //充值人数
        $userMax = array_column($data, 'totalUser');
        rsort($userMax);
        $chart['user']['userMax'] = $userMax[0] + 10; //最大值加上10

        //拼接图表数据
        $totalUserArr = array_column($data, 'totalUser','level');//每个等级活跃人数

        foreach ($chart['level'] as $value) {
            $chart['user']['rate'][]       = is_null($totalUserArr[$value]) ? 0 : floatval((numFormat(($totalUserArr[$value]/$totalUser))*100));
            $chart['user']['totalUser'][]  = is_null($totalUserArr[$value]) ? 0 : $totalUserArr[$value];
        }

        return $chart;
    }

    /**
     * 充值档位分布统计
     */
    public function payGear()
    {
        if(IS_POST){
            $data = I();
            !$data['game_id'] && exit(json_encode(array('rows'=>array(), 'results'=>0)));
            
            $start      = $data['start']? $data['start']: 0;
            $pageSize   = $data['limit']? $data['limit']: 30;
            $where = '1';
            if($data['os'] == 1){
                $andAgent = array_keys(getDataList('agent','agent',C('DB_PREFIX_API'),array('gameType'=>1,'game_id'=>$data['game_id'])));
                $arr = array_intersect($this->agentArr, $andAgent);
                sort($arr);
                
            }elseif($data['os'] == 2){
                $iosAgent = array_keys(getDataList('agent','agent',C('DB_PREFIX_API'),array('gameType'=>2,'game_id'=>$data['game_id'])));
                $arr = array_intersect($this->agentArr, $iosAgent);
                sort($arr);
                
            }

            //权限控制
            if(!$data['os']){
                $where .= ' and agent in("'.implode('","', $this->agentArr).'")';
                $map['agent'] = array('in',$this->agentArr);   
            }else{
                $where .= ' and agent in("'.implode('","', $arr).'")';
                $map['agent'] = array('in',$arr);   
            }
            
            if($data['game_id']) { 
                $where .= ' and game_id='.$data['game_id']; 
                $map['game_id'] = $data['game_id'];
            }

            if($data['startDate'] && $data['endDate']){
                $where .= ' and dayTime>="'.$data['startDate'].'" and dayTime<"'.date('Y-m-d',strtotime($data['endDate'].'+1 day')).'"';
                $map['dayTime'] = array(array('egt',$data['startDate']), array('lt',date('Y-m-d',strtotime($data['endDate'].'+1 day'))), 'and');
            }


            $game_list  = getDataList('game','id',C('DB_PREFIX_API'));
            $res        = D('Admin')->getPayGearData($map,$start,$pageSize,$where); //渠道充值分布数据
            $results    = $res['count']['total']; //总行数
            $totalMoney = $res['count']['totalMoney']; //总金额
            $payRate    = 0;
            $userRate   = 0;
            foreach ($res['list'] as $key=>$val){
                $payRate  += $res['list'][$key]['payRate']   = numFormat(($val['totalPay']/$totalMoney),true);
                $res['list'][$key]['totalPay']  = floatval($val['totalPay']);
                $res['list'][$key]['gameName']  = $game_list[$val['game_id']]['gameName'];
            }
            $rows = $res['list'];
            //显示图表
            if($data['chart'] == 1){
                $payGearChart = $this->payGearChart($rows,$totalMoney);
                if($payGearChart === false){
                    $this->ajaxReturn(array('status'=>0,'info'=>array('key'=>0,'data'=>array())));
                }
                $this->ajaxReturn(array('status'=>1,'info'=>$payGearChart));
            }
            //数据汇总
            $pageSummary = array('totalPay'=>sprintf("%.2f", array_sum(array_column($res['list'], 'totalPay'))));
            if($data['export'] == 1){
                $col = array('gameName'=>'游戏名称','goods'=>'商品档位', 'totalPay'=>'充值金额','payRate'=>'充值金额占比');
                array_unshift($rows, $col);
                $pageSummary['gameName']  = '汇总';
                array_push($rows,$pageSummary);
                export_to_csv($rows,'充值档位分布',$col);
                exit();
            }
            $arr = array('rows'=>$rows ? $rows : array(), 'results'=>$results, 'pageSummary'=>$pageSummary);
            unset($rows,$remainArr);
            exit(json_encode($arr));
        }else{
            if(IS_AJAX){
                $response = $this->fetch();
                $this->ajaxReturn(array('status'=>1,'_html'=>$response));
            }else{
                $this->display();
            }   
        }
    }

    //充值档位分布图表数据格式处理
    private function payGearChart($data,$totalMoney)
    {
        if(!$data) return false;
        $chart = array();

        $chart['key'] = array_column($data, 'goods');
        foreach ($data as $key=>$value) {
            $chart['data'][$key]['value'] = $value['totalPay'];
            $chart['data'][$key]['name'] = $value['goods'];
        }

        return $chart;        
    }

    /**
     * 实时注册图表
     */
    public function registerChart()
    {
        if (IS_POST) {
            $data  = I();
            $agent = $_REQUEST['regAgent'];


            //处理搜索条件
            if (is_array($agent) && in_array("--请选择渠道号--", $agent)) {
                unset($agent[array_search("--请选择渠道号--", $agent)]);
            } elseif (is_string($agent) && $agent == "--请选择渠道号--") {
                $agent     = "";
            } elseif (is_string($agent) && !empty($agent)){
                $agent     = explode(',', $agent);
                unset($agent[array_search("--请选择渠道号--", $agent)]);
            }

            if($agent){
                $map['regAgent'] = array('in',$agent); 
            }else{
                $agent_infos = $map_arr = array();

                if($data['advteruser_id']){
                    $map_arr['advteruser_id'] = $data['advteruser_id'];
                }

                if($data['gameType']){
                    $map_arr['gameType'] = $data['gameType'];
                }


                if($map_arr){
                    $agent_infos = array_keys(getDataList('agent','agent',C('DB_PREFIX_API'),$map_arr));
                }

                $arr = $this->agentArr;
                if($agent_infos){
                    $arr = array_intersect($arr, $agent_infos);
                }elseif($map_arr && !$agent_infos){
                    exit(json_encode(array('rows'=>array(), 'results'=>0)));
                }

                sort($arr);
                if(count($arr) < 1) $arr[] = '-1';
                $map['regAgent'] = array('in',$arr);

            }

            if ($data['game_id']) {
                $map['game_id'] = $data['game_id'];
            }

            if ($data['date']) {
                $map['regTime'] = array(array('egt', strtotime($data['date'])), array('lt', strtotime($data['date'].'+1 day')), 'and');
            }

            $res    = D("Admin")->getHourRegisterCount($map);
            $arr    = array();
            $max    = 0;
            $info   = array();
            $list   = array();
            $multiple = 1;
            //鸿雁传书数据除2
            if(session('admin.uid') == "30" && strtotime($data['date']) >= strtotime('2017-11-14')){
                $multiple = 2;
            }
            foreach ($res as $val) {
                $val["hour"] > $max && $max = $val["hour"];
                $arr[intval($val["hour"])]          += intval($val["user"]);
                $list[$val["regAgent"]]["regAgent"]       = $val["regAgent"];
                $list[$val["regAgent"]][$val["hour"]]  = round (($val["user"])/$multiple);
                $list[$val["regAgent"]]["count"]       += round ((intval($val["user"]))/$multiple);
                $list["统计"][$val["hour"]]         += round (($val["user"])/$multiple);
                $list["统计"]["count"]              += round ((intval($val["user"]))/$multiple);
            }
            foreach ($list as $k => $v) {
                $agent                  = D("ThirdParty/Admin")->commonQuery("agent", array("agent" => $v["regAgent"]), 0, 1, '*', 'lg_');
                $list[$k]["agentName"]  = $agent["agentName"]? $agent["agentName"]: "-";
            }
            ksort($list);
            $list["统计"]["regAgent"]        = "统计";
            $list["统计"]["agentName"]    = "-";
            for ($i = 0; $i <= $max; $i++) {
                $info[] = $arr[$i]? round(($arr[$i])/$multiple) : 0;
            }
            exit(json_encode(array("info" => $info, "list" => array_values($list))));
        } else {
            if (IS_AJAX) {
                $response = $this->fetch();
                $this->ajaxReturn(array('status'=>1, '_html'=>$response));
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
            $data = I();
            $agent_info = $_REQUEST['agent'];

            //处理搜索条件
            if (is_array($agent_info) && in_array('--请选择渠道号--', $agent_info)) {
                unset($agent_info[array_search('--请选择渠道号--', $agent_info)]);
            } elseif (is_string($agent_info) && $agent_info == '--请选择渠道号--') {
                $agent_info = '';
            }

            if ($data["gameType"] || $data["advteruser_id"]) {
                $agent_map  = array();
                $data["gameType"] && $agent_map["gameType"] = $data["gameType"];
                $data["advteruser_id"] && $agent_map["advteruser_id"] = $data["advteruser_id"];
                $agent      = D("Admin")->commonQuery("agent", $agent_map, 0, null, "agent", "lg_");
                $agent_a[]  = "1";
                foreach ($agent as $a) {
                    $agent_a[] = $a["agent"];
                }
                $map["_string"] = "agent IN ('".implode("','", $agent_a)."')";
            }

            if ($agent_info) {
                $res = array();
                if (is_array($agent_info)) {
                    $str = implode('', $agent_info);
                } else {
                    $str = str_replace(',', '', $agent_info);
                }
                preg_match_all('/\[([\x{4e00}-\x{9fa5}a-zA-Z0-9]+)\]/use', $str, $res);
                $data['agent']  = $res[1];
                $map['agent']   = array('in', $data['agent']);
            } else {
                //权限控制
                $map['agent'] = array('in', $this->agentArr);
            }

            if ($data['game_id']) {
                $map['game_id'] = $data['game_id'];
            }

            if ($data['date']) {
                $map['createTime'] = array(array('egt', strtotime($data['date'])), array('lt', strtotime($data['date'].'+1 day')), 'and');
            }

            $map['orderStatus'] = 0;
            $map['orderType']   = 0;

            $res    = D("Admin")->getHourPayCount($map);
            $arr    = array();
            $max    = 0;
            $info   = array();
            $list   = array();
            $multiple = 1;
            //鸿雁传书数据除2
            if(session('admin.uid') == "30" && strtotime($data['date']) >= strtotime('2017-11-14')){
                $multiple = 2;
            }
            foreach ($res as $val) {
                $val["hour"] > $max && $max = $val["hour"];
                $arr[intval($val["hour"])]          += intval($val["amount"]);
                $list[$val["agent"]]["agent"]       = $val["agent"];
                $list[$val["agent"]][$val["hour"]]  = round (($val["amount"])/$multiple);
                $list[$val["agent"]]["count"]       += round ((intval($val["amount"]))/$multiple);
                $list["统计"][$val["hour"]]         += round (($val["amount"])/$multiple);
                $list["统计"]["count"]              += round ((intval($val["amount"]))/$multiple);
            }

            foreach ($list as $k => $v) {
                $agent                  = D("Admin/Admin")->commonQuery("agent", array("agent" => $v["agent"]), 0, 1, '*', 'lg_');
                $list[$k]["agentName"]  = $agent["agentName"]? $agent["agentName"]: "-";
            }
            ksort($list);
            $list["统计"]["agent"]        = "统计";
            $list["统计"]["agentName"]    = "-";
            for ($i = 0; $i <= $max; $i++) {
                $info[] = $arr[$i]? round(($arr[$i])/$multiple): 0;
            }
            exit(json_encode(array("info" => $info, "list" => array_values($list))));
        } else {
            if (IS_AJAX) {
                $response = $this->fetch();
                $this->ajaxReturn(array('status'=>1, '_html'=>$response));
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
        if(IS_POST){
            $data = I();
            !$data['game_id'] && exit(json_encode(array('rows'=>array(), 'results'=>0)));
            $start          = $data["start"]? $data["start"]: 0;
            $pageSize       = $data["limit"]? $data["limit"]: 30;
            $where = '1';
            
            if($data['os'] == 1){
                $andAgent = array_keys(getDataList('agent','agent',C('DB_PREFIX_API'),array('gameType'=>1,'game_id'=>$data['game_id'])));
                $arr = array_intersect($this->agentArr, $andAgent);
                sort($arr);
                
            }elseif($data['os'] == 2){
                $iosAgent = array_keys(getDataList('agent','agent',C('DB_PREFIX_API'),array('gameType'=>2,'game_id'=>$data['game_id'])));
                $arr = array_intersect($this->agentArr, $iosAgent);
                sort($arr);
            }

            if(empty($arr)) $arr = array('-1');

            //权限控制
            if(!$data['os']){
                $where .= ' AND a.agent IN("'.implode('","',$this->agentArr).'")'; 
            }else{
                $where .= ' AND a.agent in("'.implode('","',$arr).'")'; 
            }
            
            if($data['game_id']) {
                $where .= ' and a.game_id='.$data['game_id']; 
            }

            if($data['startDate'] && $data['endDate']){
                $where .= ' and a.dayTime>="'.$data['startDate'].'" and a.dayTime<"'.date('Y-m-d',strtotime($data['endDate'].'+1 day')).'"';
            }

            $game_list  = getDataList('game','id',C('DB_PREFIX_API'));
            $res        = D('Admin')->getPayRangeData($start,$pageSize,$where); //充值排行数据
            $results    = $res['count']['total']; //总行数
            $totalPay   = $res['count']['totalPay']; //总行数
            foreach($res['list'] as $key => $val){
                $res['list'][$key]['range']         = $start+($key+1);
                $res['list'][$key]['gameName']      = $game_list[$val['game_id']]['gameName'];
                $res['list'][$key]['totalPay']      = floatval($val['totalPay']);
                $res['list'][$key]['totalBalance']  = floatval($val['totalBalance']);
                $res['list'][$key]['ratio']         = round(floatval($val['totalPay']) / $totalPay * 100, 2)."%";
                $res['list'][$key]['noLogin']       = round((strtotime(date("Y-m-d")) - strtotime(date("Y-m-d", $val["lastLogin"]))) / 86400);
                $res['list'][$key]['noPay']         = round((time() - strtotime($val["lastPay"])) / 86400);
            }
            $rows = $res['list'];

            //数据汇总
            $pageSummary = array('totalPay'=>sprintf("%.2f", array_sum(array_column($res['list'], 'totalPay'))));
            if($data['export'] == 1){

                $col = array('range'=>'排名','gameName'=>'游戏名称','agent'=>'渠道号',/*'city'=>'注册城市',*/'userCode'=>'用户标识符','userName'=>'充值账号', 'totalPay'=>'充值金额','totalBalance'=>'充入游戏币','createTime'=>'账号创建时间','lastPayRoleName'=>'最后充值角色名','lastPayServerName'=>'最后充值服务器名','lastPay'=>'最后充值时间','noLogin'=>'离线天数','noPay'=>'未充值天数');
                array_unshift($rows, $col);
                $pageSummary['gameName']  = '汇总';
                array_push($rows,$pageSummary);
                export_to_csv($rows,'充值排行',$col);
                exit();
            }
            $arr = array('rows'=>$rows ? $rows : array(), 'results'=>$results,'summary'=>array('totalPay'=>number_format($totalPay,2)));
            unset($rows,$remainArr);
            exit(json_encode($arr));
        }else{
            if(IS_AJAX){
                $response = $this->fetch();
                $this->ajaxReturn(array('status'=>1,'_html'=>$response));
            }else{
                $this->display();
            }   
        }
    }

    /**
     * 充值地区分布
     */
    public function areaPay()
    {
        if(IS_POST){
            $data = I();
            $agent_info = $_REQUEST['agent'];
            !$data['game_id'] && exit(json_encode(array('rows'=>array(), 'results'=>0)));

            //处理搜索条件
            if(is_array($agent_info) && in_array('--请选择渠道号--', $agent_info)){
                unset($agent_info[array_search('--请选择渠道号--', $agent_info)]);
            }elseif(is_string($agent_info) && $agent_info == '--请选择渠道号--'){
                $agent_info = '';
            }elseif(is_string($agent_info) && !empty($agent_info)){
                $agent_info = explode(',', $agent_info);
            }

            $start      = $data['start']? $data['start']: 0;
            $pageSize   = $data['limit']? $data['limit']: 30;
            $where = '1';
            if($agent_info){
                $data['agent'] = $agent_info;
                $where .= ' and agent in("'.implode('","',$data['agent']).'")'; 
            }else{
                //权限控制
                $where .= ' and agent in("'.implode('","', $this->agentArr).'")';
            }
            
            if($data['game_id']) { 
                $where .= ' and game_id='.$data['game_id']; 
            }

            if($data['startDate'] && $data['endDate']){
                $where .= ' and dayTime>="'.$data['startDate'].'" and dayTime<"'.date('Y-m-d',strtotime($data['endDate'].'+1 day')).'"';
            }

            if($data['serverId']) { 
                $where .= ' and serverId="'.$data['serverId'].'"'; 
            }
            $game_list   = getDataList('game','id',C('DB_PREFIX_API'));
            $res         = D('Admin')->getAreaPayData($start,$pageSize,$where); //充值地区分布
            $allPay      = $res['count'];
            foreach($res['list'] as $key => $val){
                $data['lookType'] == 1 && $res['list'][$key]['dayTime'] = '-';
                $data['lookType'] == 1 && $res['list'][$key]['city']    = '-';
                $res['list'][$key]['Rate']  = numFormat(($val['amount']/$allPay['provincePay']),true);

                //城市
                foreach ($res['list'][$key]['children'] as $k => $v) {
                    $data['lookType'] == 1 && $res['list'][$key]['children'][$k]['dayTime'] = '-';
                    $res['list'][$key]['children'][$k]['Rate']  = numFormat(($v['amount']/$allPay['cityPay']),true);
                    if($data['export'] == 1){
                        $exportArr[] = $res['list'][$key]['children'][$k];
                    }
                }
            }
            $res['list'][] = array('province'=>'汇总','amount'=>$allPay['provincePay']);
            $rows = $res['list'];
            unset($res);

            if($data['export'] == 1){
                $col = array('dayTime'=>'日期','province'=>'省份','city'=>'城市','amount'=>'充值金额', 'Rate'=>'占比');
                array_unshift($exportArr, $col);
                export_to_csv($exportArr,'充值地区分布',$col);
                exit();
            }
            $arr = array('rows'=>$rows ? $rows : array(), 'results'=>$results, 'pageSummary'=>$allPay);
            unset($rows,$remainArr);
            exit(json_encode($arr));
        }else{
            if(IS_AJAX){
                $response = $this->fetch();
                $this->ajaxReturn(array('status'=>1,'_html'=>$response));
            }else{
                $this->display();
            }   
        }
    }

    /**
     * 等级流失率统计
     */
    public function levelLoss()
    {
        if(IS_POST){
            $data = I();
            !$data['game_id'] && exit(json_encode(array('rows'=>array(), 'results'=>0)));
            $start          = $data["start"]? $data["start"]: 0;
            $pageSize       = $data["limit"]? $data["limit"]: 30;
            $where = '1';
            
            if($data['startDate'] && $data['endDate']){
                $d1 = date_create($data['startDate']);
                $d2 = date_create($data['endDate']);
                $diff = date_diff($d1,$d2);
                if($diff->format("%a") > 7){
                    exit(json_encode(array('hasError'=>true, 'error'=>'日期跨度不能大于7天')));
                }
            }else{
                exit(json_encode(array('hasError'=>true, 'error'=>'时间必选')));
            }
            
            if($data['os'] == 1){
                $andAgent = array_keys(getDataList('agent','agent',C('DB_PREFIX_API'),array('gameType'=>1,'game_id'=>$data['game_id'])));
                $arr = array_intersect($this->agentArr, $andAgent);
                sort($arr);
                
            }elseif($data['os'] == 2){
                $iosAgent = array_keys(getDataList('agent','agent',C('DB_PREFIX_API'),array('gameType'=>2,'game_id'=>$data['game_id'])));
                $arr = array_intersect($this->agentArr, $iosAgent);
                sort($arr);
            }

            if(empty($arr)) $arr = array('-1');

            //权限控制
            if(!$data['os']){
                $where .= ' and a.agent IN("'.implode('","',$this->agentArr).'")'; 
            }else{
                $where .= ' and a.agent in("'.implode('","',$arr).'")'; 
            }

            if($data['game_id']) { 
                $where .= ' and a.game_id='.$data['game_id']; 
            }

            if($data['startDate'] && $data['endDate']){
                $where .= ' and a.createTime>='.strtotime($data['startDate']).' and a.createTime<'.strtotime(date('Y-m-d',strtotime($data['endDate'].'+1 day')));
            }

            $game_list   = getDataList('game','id',C('DB_PREFIX_API'));
            $res         = D('Admin')->getLevelLossData($start,$pageSize,$where); //等级流失分布
            $results     = $res['count']['total'];
            $day3 = $day7 = $day3Num = $day7Num = 0;
            foreach($res['list'] as $key => $val){
                $res['list'][$key]['level']     = intval($val['level']);
                $res['list'][$key]['day3']     = intval($val['day3']);
                $res['list'][$key]['day7']     = intval($val['day7']);
                $res['list'][$key]['day3Rate'] = numFormat(($val['day3']/$res['count']['day3']),true);
                $res['list'][$key]['day7Rate'] = numFormat(($val['day7']/$res['count']['day7']),true);
                $day3 += $res['list'][$key]['day3Rate'];
                $day7 += $res['list'][$key]['day7Rate'];
                if($res['list'][$key]['day3Rate']+0 > 0) $day3Num++;
                if($res['list'][$key]['day7Rate']+0 > 0) $day7Num++;
            }
            $rows = $res['list'];
            unset($res);

            $pageSummary = array('day3'=>array_sum(array_column($rows, 'day3')),'day7'=>array_sum(array_column($rows, 'day7')),'day3Rate'=>numFormat(($day3/$day3Num),false).'%','day7Rate'=>numFormat(($day7/$day7Num),false).'%');

            if($data['export'] == 1){
                $col = array('level'=>'等级','day3'=>'流失用户（三天未登录）','day3Rate'=>'占比','day7'=>'流失用户（七天未登录）','day7Rate'=>'占比');
                array_unshift($rows, $col);
                $pageSummary['level']  = '汇总';
                array_push($rows,$pageSummary);
                export_to_csv($rows,'等级流失分布',$col);
                exit();
            }
            $arr = array('rows'=>$rows ? $rows : array(), 'results'=>$results, 'pageSummary'=>$pageSummary);
            unset($rows,$remainArr);
            exit(json_encode($arr));
        }else{
            if(IS_AJAX){
                $response = $this->fetch();
                $this->ajaxReturn(array('status'=>1,'_html'=>$response));
            }else{
                $this->display();
            }   
        }
    }

    /**
     * 部门日报数据
     */
    public function departmentDayReport()
    {
        if(IS_POST){
            $data = I();

            $start      = $data['start']? $data['start']: 0;
            $pageSize   = $data['limit']? $data['limit']: 500;
            
            $where = '1';
            $data['departmentId'] && $where .= " AND department = {$data['departmentId']}";
            if($data['startDate'] && $data['endDate']){
                $where .= " AND dayTime >= '{$data['startDate']}' AND dayTime < '".date('Y-m-d',strtotime($data['endDate'].' + 1 day'))."'";
            }

            $res     = D('Admin')->getDepartmentDayReportData($start,$pageSize,$where); //部门日报数据
            $results = $res['count']['total'];
            $department = array(1=>'发行一部',2=>'发行二部');
            foreach($res['list'] as $key => $val){
                $res['list'][$key]['allPay'] = round($val['allPay'],2);
                if($data['export'] == 1){
                    $res['list'][$key]['payRate'] = round(($val['allPayUser']/$val['actUser'])*100,2).'%'; //部门充值帐户数/部门活跃帐户数
                }else{
                    $res['list'][$key]['payRate'] = round(($val['allPayUser']/$val['actUser'])*100,2); //部门充值帐户数/部门活跃帐户数
                }
            }
            $rows = $res['list'];
            unset($res);

            $pageSummary = array('newDevice'=>array_sum(array_column($rows, 'newDevice')),'newUser'=>array_sum(array_column($rows, 'newUser')),'actUser'=>array_sum(array_column($rows, 'actUser')),'allPay'=>array_sum(array_column($rows, 'allPay')),'allPayUser'=>array_sum(array_column($rows, 'allPayUser')));

            if($data['export'] == 1){
                $col = array('dayTime'=>'日期','newDevice'=>'新增设备数','newUser'=>'新增账户数','actUser'=>'活跃账户数','allPay'=>'充值金额','allPayUser'=>'充值账户数','payRate'=>'付费率');
                array_unshift($rows, $col);
                $pageSummary['dayTime']  = '汇总';
                array_push($rows,$pageSummary);
                export_to_csv($rows,'部门日报',$col);
                exit();
            }
            $arr = array('rows'=>$rows ? $rows : array(), 'results'=>$results, 'pageSummary'=>$pageSummary);
            unset($rows,$remainArr);
            exit(json_encode($arr));
        }else{
            if(IS_AJAX){
                $response = $this->fetch();
                $this->ajaxReturn(array('status'=>1,'_html'=>$response));
            }else{
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
            $data = I();
            $agent_info     = $_REQUEST["agent"];
            $agent_p_info   = $_REQUEST["agent_p"];

            $join   = null;
            $sum    = false;
            if ($data["showType"] == 1) {
                $table      = "login";
                $info       = "userCode";
                $time       = "time";
                $agent      = "regAgent";
                $distinct   = true;
                $prefix     = C("DB_PREFIX_LOG");
            } elseif ($data["showType"] == 2) {
                $table      = "login";
                $info       = "udid";
                $time       = "time";
                $agent      = "regAgent";
                $distinct   = true;
                $prefix     = C("DB_PREFIX_LOG");
            } elseif ($data["showType"] == 3) {
                $table      = "device_game";
                $info       = "1";
                $time       = "createTime";
                $agent      = "agent";
                $distinct   = false;
                $prefix     = C("DB_PREFIX_API");
            } elseif ($data["showType"] == 4) {
                $table      = "device_game";
                $info       = "1";
                $time       = "createTime";
                $agent      = "agent";
                $distinct   = false;
                $prefix     = C("DB_PREFIX_API");
                $map["lastLogin"]   = array("EXP",'IS NOT NULL');
            } elseif ($data["showType"] == 5) {
                $table      = "role";
                $info       = "a.udid";
                $time       = "a.createTime";
                $agent      = "b.agent";
                $distinct   = true;
                $prefix     = C("DB_PREFIX_API");
                $join       = "LEFT JOIN lg_device_game b ON a.udid = b.udid";
                $map["b.createTime"]    = array(array("egt", strtotime($data["date"])), array("lt", strtotime($data["date"]."+1 day")), "and");
            } elseif ($data["showType"] == 6) {
                $table      = "order";
                $info       = "userCode";
                $time       = "paymentTime";
                $agent      = "agent";
                $distinct   = true;
                $prefix     = C("DB_PREFIX_API");
                $map["orderType"]   = 0;
                $map["orderStatus"] = 0;
            } elseif ($data["showType"] == 7) {
                $table      = "order";
                $info       = "amount";
                $time       = "paymentTime";
                $agent      = "agent";
                $distinct   = false;
                $sum        = true;
                $prefix     = C("DB_PREFIX_API");
                $map["orderType"]   = 0;
                $map["orderStatus"] = 0;
            } elseif ($data["showType"] == 8) {
                $table      = "order";
                $info       = "1";
                $time       = "paymentTime";
                $agent      = "agent";
                $distinct   = false;
                $prefix     = C("DB_PREFIX_API");
                $map["orderType"]   = 0;
                $map["orderStatus"] = 0;
            } elseif ($data["showType"] == 9) {
                $table      = "fall_open_log";
                $info       = "requestIp";
                $time       = "createTime";
                $agent      = "agent";
                $distinct   = true;
                $prefix     = C("DB_PREFIX");
            } elseif ($data["showType"] == 10) {
                $table      = "fall_download_log";
                $info       = "requestIp";
                $time       = "createTime";
                $agent      = "agent";
                $distinct   = true;
                $prefix     = C("DB_PREFIX");
            }

            //处理搜索条件
            $agentArr = dealList($agent_info,$agent_p_info);

            if($agentArr['agent']){
                $map['agent'] = array('in',$agentArr['agent']); 
            }else{
                $agent_infos = $map_arr = array();

                if(!empty($agentArr['pAgent'])){
                    $map_arr['_string'] = "id IN ('".implode("','", $agentArr['pAgent'])."') OR pid IN ('".implode("','", $agentArr['pAgent'])."')";
                }

                if($data['advteruser_id']){
                    $map_arr['advteruser_id'] = $data['advteruser_id'];
                }

                if($data['creater']){
                    $map_arr['creater'] = $data['creater'];
                }

                if($data['gameType']){
                    $map_arr['gameType'] = $data['gameType'];
                }

                if($data['department']){
                    $map_arr['departmentId'] = $data['department'];
                }

                if($map_arr){
                    $agent_infos = array_keys(getDataList('agent','agent',C('DB_PREFIX_API'),$map_arr));
                }

                $arr = $this->agentArr;
                if($agent_infos){
                    $arr = array_intersect($arr, $agent_infos);
                }elseif($map_arr && !$agent_infos){
                    exit(json_encode(array('rows'=>array(), 'results'=>0)));
                }

                sort($arr);
                if(count($arr) < 1) $arr[] = '-1';
                $map['agent'] = array('in',$arr);

            }

            if ($data["game_id"]) {
                if ($data["showType"] == 5) {
                    $map["a.game_id"] = $data["game_id"];
                } else {
                    $map["game_id"] = $data["game_id"];
                }
            }

            if ($data["date"]) {
                $map[$time] = array(array("egt", strtotime($data["date"])), array("lt", strtotime($data["date"]."+1 day")), "and");
            }

            $res    = D("Admin")->getHourCount($table, $info, $map, $time, $agent, $distinct, $prefix, $join, $sum);
            $arr    = array();
            $max    = 0;
            $info   = array();
            $list   = array();
            $row    = array();
            foreach ($res as $val) {
                $val["hour"] > $max && $max = $val["hour"];
                $arr[intval($val["hour"])]          += intval($val["num"]);
                $list[$val["agent"]]["agent"]       = $val["agent"];
                $list[$val["agent"]][$val["hour"]]  = $val["num"];
                $list[$val["agent"]]["count"]       += intval($val["num"]);
                $list["统计"][$val["hour"]]         += $val["num"];
                $list["统计"]["count"]              += $val["num"];
            }

            $n      = 1;
            foreach ($list as $k => $v) {
                if ($k == "统计") continue;
                $agent                                      = D("Admin/Admin")->commonQuery("agent", array("agent" => $v["agent"]), 0, 1, "*", "lg_");
                $row[$v["count"]*10000 + $n]                = $v;
                $row[$v["count"]*10000 + $n]["agentName"]   = $agent["agentName"]? $agent["agentName"]: "-";
                $n++;
            }

            krsort($row);
            $list["统计"]["agent"]        = "统计";
            $list["统计"]["agentName"]    = "-";
            $row["统计"]                  = $list["统计"];

            for ($i = 0; $i <= $max; $i++) {
                $info[] = $arr[$i]? $arr[$i]: 0;
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
     * 老用户统计
     */
    public function oldUserTable()
    {
        if (IS_AJAX) {
            $data           = I();
            $start          = $data["start"]? $data["start"]: 0;
            $pageSize       = $data["limit"]? $data["limit"]: 30;
            !$data['game_id'] && exit(json_encode(array('rows'=>array(), 'results'=>0)));

            if($data['os'] == 1){
                $andAgent = array_keys(getDataList('agent','agent',C('DB_PREFIX_API'),array('gameType'=>1,'game_id'=>$data['game_id'])));
                $arr = array_intersect($this->agentArr, $andAgent);
                sort($arr);
                
            }elseif($data['os'] == 2){
                $iosAgent = array_keys(getDataList('agent','agent',C('DB_PREFIX_API'),array('gameType'=>2,'game_id'=>$data['game_id'])));
                $arr = array_intersect($this->agentArr, $iosAgent);
                sort($arr);
            }

            if(empty($arr)) $arr = array('-1');

            //权限控制
            if(!$data['os']){
                $map['a.agent'] = array('in',$this->agentArr);   
            }else{
                $map['a.agent'] = array('in',$arr);   
            }

            if ($data["game_id"]) {
                $map["a.gameId"]    = $data["game_id"];
            }

            if ($data["startDate"] && $data["endDate"]) {
                $map["a.dayTime"]   = array("BETWEEN", array($data["startDate"], $data["endDate"]));
            } elseif ($data["startDate"]) {
                $map["a.dayTime"]   = array("EGT", $data["startDate"]);
            } elseif ($data["endDate"]) {
                $map["a.dayTime"]   = array("ELT", $data["endDate"]);
            }

            $count  = D("Admin")->getOldUserTableCount($map);
            $info   = D("Admin")->getOldUserTable($map, $start, $pageSize);

            if($map['a.gameId']){
                $map['a.game_id'] = $map['a.gameId'];
                unset($map['a.gameId']);
            }
            $payServer = M('sp_agent_server_pay_day a',C('DB_PREFIX'),'CySlave')->field('SUM(allPay) AS allPay,SUM(allPayUser) AS allPayUser,SUM(newPay) AS newPay,SUM(newPayUser) AS newPayUser,game_id,dayTime')->where($map)->group('game_id,dayTime')->select();

            $game   = getDataList("game", "id", C("DB_PREFIX_API"));
            $sum    = array("oldLogin" => 0, "oldPay" => 0, "oldPayAmount" => 0);
            foreach ($info as $k => $v) {
                if($payServer){
                    foreach($payServer as $key=>$val){
                        if($v['gameId'] == $val['game_id'] && $v['day'] == $val['dayTime']){
                            $v['allPay']     += floatval($val['allPay']);
                            $v['allPayUser'] += $val['allPayUser'];
                            $v['newPay']     += floatval($val['newPay']);
                            $v['newPayUser'] += $val['newPayUser'];
                        }
                    }
                }

                $oldPay                     = $v["allPayUser"] - $v["newPayUser"];
                $oldPayAmount               = $v["allPay"] - $v["newPay"];
                if (!$v["oldLogin"] && !$oldPay && !$oldPayAmount) {
                    unset($info[$k]);
                    continue;
                }
                $info[$k]["gameName"]       = $game[$v["gameId"]]["gameName"];
                $info[$k]["oldPay"]         = $oldPay;
                $info[$k]["oldPayAmount"]   = $oldPayAmount;
                $sum["oldLogin"]            += $v["oldLogin"];
                $sum["oldPay"]              += $oldPay;
                $sum["oldPayAmount"]        += $oldPayAmount;
                $info[$k]["oldPayRatio"]    = round($oldPay / $v["oldLogin"] * 100, 2)."%";
                $info[$k]["oldARPU"]        = round($oldPayAmount / $v["oldLogin"], 2);
                $info[$k]["oldARPPU"]       = round($oldPayAmount / $oldPay, 2);
            }
            $sum["oldPayRatio"] = round($sum["oldPay"] / $sum["oldLogin"] * 100, 2)."%";
            $sum["oldARPU"]     = round($sum["oldPayAmount"] / $sum["oldLogin"], 2);
            $sum["oldARPPU"]    = round($sum["oldPayAmount"] / $sum["oldPay"], 2);
            $arr = array("rows" => $info? $info: array(), "results" => $count, "pageSummary" => $sum);
            exit(json_encode($arr));
        } else {
            $this->display();
        }
    }

    /**
     * 用户充值ltv（包）统计
     */
    public function payLtvAgent()
    {
        if(IS_POST){
            $data = I();
            !$data['game_id'] && exit(json_encode(array('rows'=>array(), 'results'=>0)));
            $start          = $data["start"]? $data["start"]: 0;
            $pageSize       = $data["limit"]? $data["limit"]: 30;
            $where = '1';

            if($data['os'] == 1){
                $andAgent = array_keys(getDataList('agent','agent',C('DB_PREFIX_API'),array('gameType'=>1,'game_id'=>$data['game_id'])));
                $arr = array_intersect($this->agentArr, $andAgent);
                sort($arr);
                
            }elseif($data['os'] == 2){
                $iosAgent = array_keys(getDataList('agent','agent',C('DB_PREFIX_API'),array('gameType'=>2,'game_id'=>$data['game_id'])));
                $arr = array_intersect($this->agentArr, $iosAgent);
                sort($arr);
            }

            if(empty($arr)) $arr = array('-1');

            //权限控制
            if(!$data['os']){
                $where .= ' and agent IN("'.implode('","',$this->agentArr).'")'; 
                $map['agent'] = array('IN',$this->agentArr);
            }else{
                $where .= ' and agent in("'.implode('","',$arr).'")'; 
                $map['agent'] = array('IN',$arr);   
            }

            if($data['game_id']) { 
                $where .= ' and gameId='.$data['game_id']; 
                $map['gameId'] = $data['game_id'];
            }

            if($data['startDate'] && $data['endDate']){
                $where .= ' and dayTime>="'.$data['startDate'].'" and dayTime<"'.date('Y-m-d',strtotime($data['endDate'].'+1 day')).'"';
                $map['dayTime'] = array(array('egt',$data['startDate']), array('lt',date('Y-m-d',strtotime($data['endDate'].'+1 day'))), 'and');
            }

            $res        = D('Admin')->getPayLtvAgentData($map,$start,$pageSize,$where); //LTV留存数据
            $res_dau    = D('Admin')->getDauData($map,$start,$pageSize,$where); //Dau数据
            $results    = $res['count'];
            $game_list  = getDataList('game','id',C('DB_PREFIX_API'));
            $agent_list = getDataList('agent','agent',C('DB_PREFIX_API'));
            foreach ($res['list'] as $key=>$val){
                $data['lookType'] == 1 && $res['list'][$key]['agent']    = '-';
                $data['lookType'] == 1 && $res['list'][$key]['serverId'] = '-';
                $res['list'][$key]['gameName']  = $game_list[$val['gameId']]['gameName'];
                $res['list'][$key]['agentName'] = $agent_list[$val['agent']]['agentName'];
            }

            if($data['lookType']==3 || $data['lookType']==4){
                $day_arr = array();
                for($i = 0; $i<=90; $i++){
                    $day_arr[] = $i;
                }
                foreach ($res_dau['list'] as $key => $value) {
                    for ($i=0; $i < 120; $i++) {
                        if(in_array($i, $day_arr)){
                            if(isset($res['list'][$key])){
                                $res['list'][$key]['dau'.$i] = $value['day'.$i];
                            }
                        } 
                    } 
                }
            }

            $remainArr = $this->ltvRemain($res['list'],$data['lookType']);
            $rows = $remainArr;
            //显示图表
            if($data['chart'] == 1){
                $ltvChart = $this->ltvChart($rows);
                if($ltvChart === false){
                    $this->ajaxReturn(array('status'=>0,'info'=>array('dayTime'=>array(),'key'=>array(),'data'=>array())));
                }
                $this->ajaxReturn(array('status'=>1,'info'=>$ltvChart));
            }
            //数据汇总
            $pageSummary = $this->ltvSummarys($rows,$data['lookType']);

            $day_arr = array();
            for($i = 0; $i<=90; $i++){
                $day_arr[] = $i;
            }
            if($data['lookType']==3){
                foreach ($rows as $key => &$value) {
                    for($i=0;$i<=120;$i++){
                        if(in_array($i, $day_arr)){
                            if($value['ltv'.$i]!=0){
                                $value['ltv'.$i] = numFormat($value['ltv'.$i]/100,true);
                            }
                        }
                    }
                }
            }
            if($data['export'] == 1){
                if($data['lookType']==1){
                    $title = 'Ltv';
                }elseif($data['lookType']==2){
                    $title = '充值金额';
                }elseif($data['lookType']==3){
                    $title = '付费率';
                }else{
                    $title = 'ARPU';
                }

                $col = array('dayTime'=>'注册日期','gameName'=>'游戏名称','newUser'=>'新增用户数','allmoney'=>'充值金额');

                for($i=0;$i<=120;$i++){
                    if(in_array($i, $day_arr)){
                        $col['ltv'.$i] = ($i+1).'日'.$title;
                    }
                }
                array_unshift($rows, $col);
                $pageSummary['dayTime']  = '汇总';
                array_push($rows,$pageSummary);
                export_to_csv($rows,$title.'数据统计',$col);
                exit();
            }
            $arr = array('rows'=>$rows ? $rows : array(), 'results'=>$results, 'pageSummary'=>$pageSummary);
            unset($rows,$remainArr);
            exit(json_encode($arr));
        }else{
            if(IS_AJAX){
                $response = $this->fetch();
                $this->ajaxReturn(array('status'=>1,'_html'=>$response));
            }else{
                $this->display();
            }   
        }
    }

    /**
     * 用户充值ltv统计
     */
    public function payLtv()
    {
        if(IS_POST){
            $data = I();
            !$data['game_id'] && exit(json_encode(array('rows'=>array(), 'results'=>0)));
            $start          = $data["start"]? $data["start"]: 0;
            $pageSize       = $data["limit"]? $data["limit"]: 30;
            $where = '1';

            if($data['os'] == 1){
                $andAgent = array_keys(getDataList('agent','agent',C('DB_PREFIX_API'),array('gameType'=>1,'game_id'=>$data['game_id'])));
                $arr = array_intersect($this->agentArr, $andAgent);
                sort($arr);
                
            }elseif($data['os'] == 2){
                $iosAgent = array_keys(getDataList('agent','agent',C('DB_PREFIX_API'),array('gameType'=>2,'game_id'=>$data['game_id'])));
                $arr = array_intersect($this->agentArr, $iosAgent);
                sort($arr);
            }

            if(empty($arr)) $arr = array('-1');

            //权限控制
            if(!$data['os']){
                $where .= ' and agent IN("'.implode('","',$this->agentArr).'")'; 
                $map['agent'] = array('IN',$this->agentArr);
            }else{
                $where .= ' and agent in("'.implode('","',$arr).'")'; 
                $map['agent'] = array('IN',$arr);   
            }

            if($data['game_id']) { 
                $where .= ' and gameId='.$data['game_id']; 
                $map['gameId'] = $data['game_id'];
            }

            if($data['startDate'] && $data['endDate']){
                $where .= ' and dayTime>="'.$data['startDate'].'" and dayTime<"'.date('Y-m-d',strtotime($data['endDate'].'+1 day')).'"';
                $map['dayTime'] = array(array('egt',$data['startDate']), array('lt',date('Y-m-d',strtotime($data['endDate'].'+1 day'))), 'and');
            }

            if($data['serverId']) { 
                $where .= ' and serverId="'.$data['serverId'].'"'; 
                $map['serverId'] = $data['serverId'];
            }

            $res        = D('Admin')->getPayLtvData($map,$start,$pageSize,$where); //LTV留存数据
            $res_dau    = D('Admin')->getDauData($map,$start,$pageSize,$where); //Dau数据
            $results    = $res['count'];
            $game_list  = getDataList('game','id',C('DB_PREFIX_API'));
            $agent_list = getDataList('agent','agent',C('DB_PREFIX_API'));
            foreach ($res['list'] as $key=>$val){
                $data['lookType'] == 1 && $res['list'][$key]['agent']    = '-';
                $data['lookType'] == 1 && $res['list'][$key]['serverId'] = '-';
                $res['list'][$key]['gameName']  = $game_list[$val['gameId']]['gameName'];
                $res['list'][$key]['agentName'] = $agent_list[$val['agent']]['agentName'];

            }

            if($data['lookType']==3 || $data['lookType']==4){
//                $day_arr = array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,29,59,89);
                $day_arr = array();
                for($i = 0; $i<=90; $i++){
                    $day_arr[] = $i;
                }
                foreach ($res_dau['list'] as $key => $value) {
                    for ($i=0; $i < 120; $i++) {
                        if(in_array($i, $day_arr)){
                            if(isset($res['list'][$key])){
                                $res['list'][$key]['dau'.$i] = $value['day'.$i];
                            }
                        } 
                    } 
                }
            }

            $remainArr = $this->ltvRemain($res['list'],$data['lookType']);
            $rows = $remainArr;
            //显示图表
            if($data['chart'] == 1){
                $ltvChart = $this->ltvChart($rows);
                if($ltvChart === false){
                    $this->ajaxReturn(array('status'=>0,'info'=>array('dayTime'=>array(),'key'=>array(),'data'=>array())));
                }
                $this->ajaxReturn(array('status'=>1,'info'=>$ltvChart));
            }
            //数据汇总
            $pageSummary = $this->ltvSummarys($rows,$data['lookType']);

//            $day_arr = array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,29,59,89);
            $day_arr = array();
            for($i = 0; $i<=90; $i++){
                $day_arr[] = $i;
            }
            if($data['lookType']==3){
                foreach ($rows as $key => &$value) {
                    for($i=0;$i<=120;$i++){
                        if(in_array($i, $day_arr)){
                            if($value['ltv'.$i]!=0){
                                $value['ltv'.$i] = numFormat($value['ltv'.$i]/100,true);
                            }
                        }
                    }
                }
            }
            if($data['export'] == 1){
                if($data['lookType']==1){
                    $title = 'Ltv';
                }elseif($data['lookType']==2){
                    $title = '充值金额';
                }elseif($data['lookType']==3){
                    $title = '付费率';
                }else{
                    $title = 'ARPU';
                }

                $col = array('dayTime'=>'注册日期','gameName'=>'游戏名称','newUser'=>'新增用户数','allmoney'=>'充值金额');

                for($i=0;$i<=120;$i++){
                    if(in_array($i, $day_arr)){
                        $col['ltv'.$i] = ($i+1).'日'.$title;
                    }
                }
                array_unshift($rows, $col);
                $pageSummary['dayTime']  = '汇总';
                array_push($rows,$pageSummary);
                export_to_csv($rows,$title.'数据统计',$col);
                exit();
            }
            $arr = array('rows'=>$rows ? $rows : array(), 'results'=>$results, 'pageSummary'=>$pageSummary);
            unset($rows,$remainArr);
            exit(json_encode($arr));
        }else{
            if(IS_AJAX){
                $response = $this->fetch();
                $this->ajaxReturn(array('status'=>1,'_html'=>$response));
            }else{
                $this->display();
            }   
        }
    }

    //衮服数据
    public function gunfuData(){
        if (IS_POST) {
            $data = I();
            !$data['game_id'] && exit(json_encode(array('rows'=>array(), 'results'=>0)));
            $start          = $data["start"]? $data["start"]: 0;
            $pageSize       = $data["limit"]? $data["limit"]: 30;

            if($data['os'] == 1){
                $andAgent = array_keys(getDataList('agent','agent',C('DB_PREFIX_API'),array('gameType'=>1,'game_id'=>$data['game_id'])));
                $arr = array_intersect($this->agentArr, $andAgent);
                sort($arr);
                
            }elseif($data['os'] == 2){
                $iosAgent = array_keys(getDataList('agent','agent',C('DB_PREFIX_API'),array('gameType'=>2,'game_id'=>$data['game_id'])));
                $arr = array_intersect($this->agentArr, $iosAgent);
                sort($arr);
            }

            if(empty($arr)) $arr = array('-1');

            //权限控制
            if(!$data['os']){
                $map['agent'] = array('IN',$this->agentArr);
            }else{
                $map['agent'] = array('IN',$arr);   
            }


            if($data['game_id']) { 
                $map['gameId'] = $data['game_id'];
            }

            if($data['startDate'] && $data['endDate']){
                $map['dayTime'] = array(array('egt',$data['startDate']), array('lt',date('Y-m-d',strtotime($data['endDate'].'+1 day'))), 'AND');
            }

            if($data['serverId']) { 
                $map['serverId'] = $data['serverId'];
            }

            $res      = D('Admin')->getGunfuData($map,$start,$pageSize);
            $results = count($res['list']);

            $game_list = getDataList('game','id',C('DB_PREFIX_API'));
            $rows = array();

            foreach ($res['list'] as $key => $val) {
                $res['list'][$key]['dayTime'] = $val['dayTime'];
                $res['list'][$key]['gameName'] = $game_list[$val['gameId']]['gameName'];
                $res['list'][$key]['newUser'] = $val['newUser'];
                $res['list'][$key]['actUser'] = $val['actUser'];
                $res['list'][$key]['allPay'] = $val['allPay'];
                $res['list'][$key]['allPayUser'] = $val['allPayUser'];
                $res['list'][$key]['payRate'] = numFormat($val['allPayUser']/$val['actUser'],true);
                $res['list'][$key]['ARPPU'] = numFormat($val['allPay']/$val['allPayUser']);
            }
            $row = $res['list'];

            $summary = array();
            foreach ($res['list'] as $key => $val) {
                $summary['newUser'] += $val['newUser'];
                $summary['actUser'] += $val['actUser'];
                $summary['allPay']  += $val['allPay'];
                $summary['allPayUser'] += $val['allPayUser'];
            }
            $summary['payRate'] = numFormat($summary['allPayUser']/$summary['actUser'],true);
            $summary['ARPPU']   = numFormat($summary['allPay']/$summary['allPayUser']);
            $summary['dayTime'] = '汇总';
            $summary['gameName'] = '-';

            if($data['export'] == 1){
                $col = array('dayTime'=>'日期', 'gameName'=>'游戏名称', 'newUser'=>'新增滚服人数','actUser'=>'滚服活跃人数','allPay'=>'充值总额','allPayUser'=>'充值人数','payRate'=>'付费率','ARPPU'=>'ARPPU');

                array_unshift($row, $col);
                array_push($row,$summary);
                export_to_csv($row,'滚服数据概况',$col);
                exit();
            }
            $arr = array('rows'=>$row ? $row : array(), 'results'=>$results,'pageSummary'=>$summary);
            exit(json_encode($arr));
        } else {
            if (IS_AJAX) {
                $response = $this->fetch();
                $this->ajaxReturn(array('status'=>1, '_html'=>$response));
            } else {
                $this->display();
            }
        }
    }
}