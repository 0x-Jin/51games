<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/6/14
 * Time: 19:12
 *
 * 游戏控制器
 */

namespace Admin\Controller;

class GameController extends BackendController
{

    private $update_address = "http://static.chuangyunet.net/";         //下载包地址

    /**
     * 游戏列表
     */
    public function index()
    {
        if (IS_POST) {
            $data       = I();
            $start      = $data['start']? $data['start']: 0;
            $pageSize   = $data['limit']? $data['limit']: 30;
            $data['id'] && $map['id'] = $data['id'];
            $data['gameName'] && $map['gameName'] = array('LIKE', '%'.$data['gameName'].'%');
            !in_array(session('admin.role_id'),array(1,3)) && $map['id'] = array('neq',104);
            $res        = D('Admin')->getBuiList("game", $map, $start, $pageSize, "lg_");
            $results    = $res['count'];
            foreach ($res['list'] as $key=>$val){
                $res['list'][$key]['gameName']      = '<a href="javascript:;" onclick="gameAgent('.$val['id'].',this)">'.$val['gameName'].'</a>';
                $res['list'][$key]['createTime']    = date('Y-m-d H:i:s', $val['createTime']);
                $res['list'][$key]['opt']           = createBtn('<a href="javascript:;" onclick="gameEdit('.$val['id'].',this)">编辑</a>&nbsp;<a href="javascript:;" onclick="goodsEdit('.$val['id'].',this)">商品</a>');
                $rows[] = $res['list'][$key];
            }
            $arr = array('rows'=>$rows, 'results'=>$results);
            exit(json_encode($arr));
        } else {
            $this->display();
        }
    }

    /**
     * 添加游戏
     */
    public function gameAdd()
    {
        if(IS_POST){
            $pinyin = new \Vendor\PinYin\PinYin;
            $data               = I();
            $data['gameAlias']  = $pinyin->getShortPinyin($data['gameName']);
            $data["createTime"] = time();
            $data["updateTime"] = time();
            $data["gameKey"]    = md5("2moxm298adn".time().$data["gameName"]."CyGameKey&jxncvowme");
            $data["payKey"]     = md5("wfmcl;mvo".time().$data["gameName"]."CyPayKey&d;soneddkf");
            if(D('Admin')->commonQuery("game",array('gameName'=>$data['gameName']),0,1,'gameName','lg_')){
                $this->error('游戏名已经存在');
            }
            if($alias = D('Admin')->commonQuery("game",array('gameAlias'=>array('LIKE', $data['gameAlias'].'%')),0,100,'gameAlias','lg_')){
                $data['gameAlias'] = $alias[count($alias)-1]["gameAlias"].'v';
            }
            $res = D("Admin")->commonAdd("game", $data, "lg_");
            if($res){
//                //添加渠道号
//                $agent = array(
//                    "agent"         => $data['gameAlias']."001",
//                    "agentName"     => $data['gameName'],
//                    "game_id"       => $res,
//                    "channel_id"    => 1,
//                    "advteruser_id" => 1,
//                    "principal_id"  => 1,
//                    "createTime"    => time(),
//                    "updateTime"    => time()
//                );
//                D("Admin")->commonAdd("agent", $agent, "lg_");

                bgLog(4, session("admin.realname")."添加了游戏：游戏名称为“{$data['gameName']}”".($data["callbackUrl"]? "，回调地址为“{$data['callbackUrl']}”": "").($data["unit"]? "，游戏币单位为“{$data['unit']}”": "").($data["ratio"]? "，比率为“{$data['ratio']}”": ""));
                $this->setNewBackstageUserInfo($res);
                $this->success("操作成功");
            }else{
                $this->error("操作失败");
            }
        }else{
            $this->assign('partment',$this->partment);
            $this->ajaxReturn(array("status"=>1, "_html"=>$this->fetch()));
        }
    }

    /**
     * 设置新后台用户游戏ID
     * @AuthorHTL
     * @DateTime  2018-04-16T19:08:01+0800
     * @param     [type]                   $game_id [description]
     */
    private function setNewBackstageUserInfo ($game_id)
    {
        if (!$game_id) return false;
        $user = D('Admin')->commonQuery('admin_new',array('status'=>0),0,1000);
        foreach ($user as $value) {
            $gameId = explode(',', $value['gameId']);
            if (in_array('0', $gameId)) {
                //给用户加入对于的游戏权限,roleId=5的没有权限
                $game_id = trim($value['gameId'],',').','.$game_id;
                if ($value['roleId'] != 5) {
                    D('Admin')->commonExecute('admin_new',array('id'=>$value['id']),array('gameId'=>$game_id));
                }
            }
        }
    }

    /**
     * 编辑游戏
     */
    public function gameEdit()
    {
        $data   = I();
        $game   = D('Admin')->commonQuery('game', array('id' => $data['id']), 0, 1, '*', 'lg_' );
        if(IS_POST){
            if (!$data['id']) {
                IS_AJAX && $this->ajaxReturn(array(
                    'status'    => 0,
                    'info'      => '参数有误'
                ));
                $this->error('参数有误');
            }

            $data["updateTime"] = time();
            if (D('Admin')->commonExecute('game', array('id' => $data['id']), $data, 'lg_')) {
                $str = '';
                $login = array(
                    0 => '开启登陆',
                    1 => '关闭登陆',
                    2 => '关闭新增'
                );
                $pay = array(
                    0 => '开启充值',
                    1 => '关闭充值',
                    2 => '切充值'
                );
                foreach ($data as $k => $v) {
                    if ($v == $game[$k]) continue;
                    if ($k == "gameName") $str .= "，游戏名称由“".($game[$k]? $game[$k]: "（无）")."”改为“".($v? $v: "（无）")."”";
                    if ($k == "callbackUrl") $str .= "，回调地址由“".($game[$k]? $game[$k]: "（无）")."”改为“".($v? $v: "（无）")."”";
                    if ($k == "loginStatus") $str .= "，登陆状态由“".$login[$game[$k]]."”改为“".$login[$v]."”";
                    if ($k == "payStatus") $str .= "，充值状态由“".$pay[$game[$k]]."”改为“".$login[$v]."”";
                    if ($k == "unit") $str .= "，游戏币单位由“".($data[$k]? $data[$k]: "（无）")."”改为“".($v? $v: "（无）")."”";
                    if ($k == "ratio") $str .= "，比率由“".($data[$k]? $data[$k]: "（无）")."”改为“".($v? $v: "（无）")."”";
                }
                bgLog(3, session("admin.realname")."修改了游戏“{$game['gameName']}”：".trim($str, "，"));

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
                $this->success('修改失败');
            }
        }else{
            $this->assign('game', $game);
            $this->assign('partment',$this->partment);

            if (IS_AJAX) {
                $respose = $this->fetch();
                $this->ajaxReturn(array('status' => 1, '_html' => $respose));
            } else {
                $this->display();
            }
        }
    }

    /**
     * 商品ID
     */
    public function goods()
    {
        $data   = I();
        $game   = D('Admin')->commonQuery('game', array('id' => $data['id']), 0, 1, '*', 'lg_');
        $agent  = D('Admin')->commonQuery('agent', array('game_id' => $data['id'], 'agentType' => 1, 'pid' => 0), 0, 99999, '*', 'lg_');
        $this->assign('game', $game);
        $this->assign('agent', $agent);

        if (IS_AJAX) {
            $respose = $this->fetch();
            $this->ajaxReturn(array('status' => 1, '_html' => $respose));
        } else {
            $this->display();
        }
    }

    /**
     * 显示商品ID
     */
    public function showGoods()
    {
        $data = I();
        //判断数据是否齐全
        if (!$data["game_id"]) {
            $res = array(
                "Result"    => false,
                "Msg"       => "获取不到游戏ID！"
            );
            echo json_encode($res);
            exit();
        }
        if ($data["agent"] != "agent") {
            $goods  = D("Admin")->commonQuery("goods", array("game_id" => $data["game_id"], "agent" => $data["agent"]), 0, 99999, "goodsCode,name,amount", C("DB_PREFIX_API"));
        } else {
            $goods  = D("Admin")->commonQuery("goods", array("game_id" => $data["game_id"], "_string" => "agent = '' OR agent IS NULL"), 0, 99999, "goodsCode,name,amount", C("DB_PREFIX_API"));
        }
        $res = array(
            "Result"    => true,
            "Msg"       => "获取商品ID成功！",
            "Data"      => $goods
        );
        echo json_encode($res);
    }

    /**
     * 添加商品ID
     */
    public function goodsAdd()
    {
        $data = I();
        //判断数据是否齐全
        if(!$data["game_id"] || !$data["goodsCode"] || !$data["name"] || !$data["amount"] || !$data["agent"]) {
            $res = array(
                "Result"    => false,
                "Msg"       => "商品数据不全！",
            );
            echo json_encode($res);
            exit();
        }
        if ($data["agent"] == "agent") $data["agent"] = "";
        $data["type"]       = $data["status"] = 0;
        $data["goodsCode"]  = trim($data["goodsCode"]);
        $data["createTime"] = $data["updateTime"] = time();
        if (D("Admin")->commonAdd("goods", $data, "lg_")) {
            $res = array(
                "Result"    => true,
                "Msg"       => "添加商品成功！",
            );
            echo json_encode($res);
            exit();
        } else {
            $res = array(
                "Result"    => false,
                "Msg"       => "添加商品失败！",
            );
            echo json_encode($res);
            exit();
        }
    }

    /**
     * 删除商品ID
     */
    public function goodsDelete()
    {
        //判断操作权限
        if (session("admin.role_id") != 1) {
            $res = array(
                "Result"    => false,
                "Msg"       => "您权限不够！无法删除！",
            );
            echo json_encode($res);
            exit();
        };

        $data = I();
        //判断数据是否齐全
        if(!$data["goodsCode"]) {
            $res = array(
                "Result"    => false,
                "Msg"       => "商品ID不全！",
            );
            echo json_encode($res);
            exit();
        }
        if (D("Admin")->commonDelete("goods", array("goodsCode" => $data["goodsCode"]), "lg_")) {
            $res = array(
                "Result"    => true,
                "Msg"       => "删除商品成功！",
            );
            echo json_encode($res);
            exit();
        } else {
            $res = array(
                "Result"    => false,
                "Msg"       => "删除商品成功！",
            );
            echo json_encode($res);
            exit();
        }
    }

    /**
     * 游戏渠道信息
     */
    public function gameAgent()
    {
        $game_id = I('id',0,'intval');
        if(empty($game_id)) return false;
        $gameAgent = D('Admin')->commonQuery('agent', array('game_id'=>$game_id,'agent'=>array('in',$this->agentArr),'pid'=>array('eq',0)), 0, 1000, '*', 'lg_');
        $game      = D('Admin')->commonQuery('game', array('id'=>$game_id), 0, 1, 'gameName', 'lg_');
        if($gameAgent){
            $principal_list = getDataList('principal','id',C('DB_PREFIX'),array('status'=>1)); //负责人列表
            foreach ($gameAgent as $key => $value) {
                $gameAgent[$key]['principalName'] = $principal_list[$value['principal_id']]['principal_name'];
            }
            $this->assign('gameName',$game['gameName']);
            $this->assign('gameAgent',$gameAgent);
            if (IS_AJAX) {
                $respose = $this->fetch();
                $this->ajaxReturn(array('status' => 1, '_html' => $respose));
            } else {
                $this->display();
            }
        }else{
            $this->ajaxReturn(array('status' => 0, '_html' => ''));
        }
    }

    /**
     * 区服列表
     */
    public function serverList()
    {
        if (IS_POST) {
            $data       = I();
            $start      = $data["start"]? $data["start"]: 0;
            $pageSize   = $data["limit"]? $data["limit"]: 30;
            $data["agent"] && $map["agent"] = $data["agent"];
            $data["game_id"] && $map["game_id"] = $data["game_id"];
            $data["serverName"] && $map["serverName"] = array("LIKE", "%".$data["serverName"]."%");
            $res        = D("Admin")->getBuiList("server", $map, $start, $pageSize, "lg_");
            $agent      = getDataList("agent", "agent", "lg_");
            $game       = getDataList("game", "id", "lg_");
            $results    = $res["count"];
            foreach ($res["list"] as $key => $val){
                $res["list"][$key]["agentName"]     = $agent[$val["agent"]]["agentName"];
                $res["list"][$key]["gameName"]      = $game[$val["game_id"]]["gameName"];
//                $res["list"][$key]["opt"]           = '<a href="javascript:;" onclick="serverEdit('.$val["id"].',this)">编辑</a>';
                $rows[]                             = $res["list"][$key];
            }
            $arr = array("rows" => $rows, "results" => $results);
            exit(json_encode($arr));
        } else {
            $this->display();
        }
    }

    /**
     * 添加区服
     */
    public function serverAdd()
    {
        if(IS_POST){
            $data               = I();
            if(empty($data['agent_p'])) $this->error('母包必选！');
            $data["createTime"] = time();
            $agent = D('Admin')->commonQuery("agent", array("id" => array('IN',$data['agent_p'])), 0, 9999, "id,agent,gameType", "lg_");
            $data["admin_id"]   = session("admin.uid");
            $data["real"]       = session("admin.realname");
            $data["department"] = session("admin.partment");
            foreach ($agent as $v) {
                $data["agent"]      = $v["agent"];
                $data["serverType"] = 3 - $v["gameType"];
                $res = D("Admin")->commonQuery("server", array("agent" => $v["agent"], "serverId" => $data["serverId"]), 0, 1, "id", "lg_");
                if (!$res) {
                    D("Admin")->commonAdd("server", $data, "lg_");
                } else {
                    $param = $data;
                    unset($param["createTime"]);
                    D("Admin")->commonExecute("server", array("id" => $res["id"]), $param, "lg_");
                }
            }
            $this->success("操作成功");
        }else{
            $this->ajaxReturn(array("status" => 1, "_html" => $this->fetch()));
        }
    }

    /**
     * 快速添加区服
     * @return [type] [description]
     */
    public function quickAdd()
    {
        if (IS_POST) {
            $data               = I();
            if(empty($data['agent_p'])) $this->error('母包必选！');
            $data["createTime"] = time();
            $agent = D('Admin')->commonQuery("agent", array("id" => array('IN',$data['agent_p'])), 0, 9999, "id,agent,gameType", "lg_");
            $data["admin_id"]   = session("admin.uid");
            $data["real"]       = session("admin.realname");
            $data["department"] = session("admin.partment");
            foreach ($agent as $v) {
                $data["agent"]      = $v["agent"];
                $data["serverType"] = 3 - $v["gameType"];
                $res = D("Admin")->commonQuery("server", array("agent" => $v["agent"], "serverId" => $data["serverId"]), 0, 1, "id", "lg_");
                if (!$res) {
                    D("Admin")->commonAdd("server", $data, "lg_");
                } else {
                    $param = $data;
                    unset($param["createTime"]);
                    D("Admin")->commonExecute("server", array("id" => $res["id"]), $param, "lg_");
                }
            }
            $this->success("操作成功");
        } else {
            $map['game_id'] = 112;
            $map['serverId'] = ['LIKE', '39%'];
            $map['createTime'] = ["BETWEEN", [time()-86400, time()]];
            $testAgent = ['sqmxTAND','jxqtAND','jxqtcsAND','sqmxAND','mxjhzHDTAND','xxcqJL','xxcqlyAND','xhdtsgsYYBAND','xhdtsgsYYBTAND'];
            $serverInfo = D("Admin")->getLastServer($map);
            krsort($serverInfo);
            
            $info['game_id'] = 112;
            $agents = '';
            foreach ($serverInfo as $k => $val) {
                if(D("Admin")->commonQuery('server', ['game_id' => 112, 'serverId' => $val['serverId']], 0, 1, "id", "lg_")){
                    continue;
                }else{
                    $info['serverId']   = $val['serverId'];
                    $info['serverName'] = $val['serverName'];
                    $info['openTime']   = dateHandle($val['openTime']);
                    break;
                }
            }
            $agentInfo = D('Admin')->getAgentList(['game_id'=>112, 'power'=>1]);
            if(!empty($agentInfo)){
                foreach ($agentInfo as $key => $value) {
                    if(!in_array($value['agent'], $testAgent)){
                        $agents .= $value['agent'].',';
                    }
                }
            }
            $this->assign("info", $info);
            $this->assign("agents", $agents);
            $response   = $this->fetch();
            $this->ajaxReturn(array("status" => 1, "_html" => $response));
        }
    }

    /**
     * 批量导入区服信息
     */
    public function serverImport()
    {
        if(IS_POST){
            if(!$_FILES['serverFile']['name'] ){
                $this->error('没有传入Excel');
            }
            //文件上传
            $file_info = excel_file_upload('server');
            if($file_info && $file_info != '没有文件被上传！'){
                //获取文件数据并且转数组
                $fileName = './Uploads/'.$file_info['serverFile']['savepath'].$file_info['serverFile']['savename'];
                $data = excel_to_array($fileName);
                if ($data) {
                    $game   = getDataList("game", "gameName", "lg_");
                    unset($data[1]);//第一个行为标题，不需要入库
                    foreach($data as $val){
                        $arr = array(
                            "game_id"       => is_null($val[0])? "": (is_numeric(trim($val[0]))? trim($val[0]): $game[trim($val[0])]["id"]),
                            "agent"         => is_null($val[1])? "": trim($val[1]),
                            "serverId"      => is_null($val[2])? "": trim($val[2]),
                            "serverName"    => is_null($val[3])? "": trim($val[3]),
                            "serverType"    => is_null($val[4])? 0: trim($val[4]),
                            "openTime"      => is_null($val[5])? "": date("Y-m-d H:i:s", strtotime(trim($val[5]))),
                            "createTime"    => time(),
                            "admin_id"      => session("admin.uid"),
                            "department"    => session("admin.partment"),
                            "real"          => session("admin.realname")
                        );

                        if($arr['agent'] == '*'){
                            if ($arr["serverType"] == 1 || $arr["serverType"] == "IOS") {
                                $agent = D("Admin")->commonQuery("agent", array("game_id" => $arr["game_id"], "gameType" => 2, "agentType" => 1, "pid" => 0), 0, 9999999, "agent,gameType", "lg_");
                            } elseif ($arr["serverType"] == 2 || $arr["serverType"] == "安卓") {
                                $agent = D("Admin")->commonQuery("agent", array("game_id" => $arr["game_id"], "gameType" => 1, "agentType" => 1, "pid" => 0), 0, 9999999, "agent,gameType", "lg_");
                            } else {
                                $agent = D("Admin")->commonQuery("agent", array("game_id" => $arr["game_id"], "agentType" => 1, "pid" => 0), 0, 9999999, "agent,gameType", "lg_");
                            }

                        }else{
                            $agent = D("Admin")->commonQuery("agent", array("agent" => $arr["agent"]), 0, 2, "id,agent,gameType", "lg_");
                        }

                        foreach ($agent as $v) {
                            $arr["agent"]       = $v["agent"];
                            $arr["serverType"]  = 3 - $v["gameType"];
                            $res = D("Admin")->commonQuery("server", array("agent" => $v["agent"], "serverId" => $arr["serverId"]), 0, 1, "id", "lg_");
                            if (!$res) {
                                D("Admin")->commonAdd("server", $arr, "lg_");
                            } else {
                                $param = $arr;
                                unset($param["createTime"]);
                                D("Admin")->commonExecute("server", array("id" => $res["id"]), $param, "lg_");
                            }
                        }
                    }
                    $this->success('区服导入成功');
                } else {
                    $this->error('区服导入失败');
                }
            } else {
                $this->error('区服导入失败');
            }
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
     * 游戏更新
     */
    public function update()
    {
        if (IS_POST) {
            $data       = I();
            $start      = $data["start"]? $data["start"]: 0;
            $pageSize   = $data["limit"]? $data["limit"]: 30;
            $data["status"] && $map["a.status"] = $data["status"] - 1;
            $data["show"] && $map["endTime"] = array(array("GT", time()), array("EXP", "IS NULL"), array("EXP", "= ''"), "OR");
            if ($data["search"]) {
                $search = explode("|", $data["search"]);
                $str    = "";
                foreach ($search as $k => $v) {
                    if ($v == "IOS" || $v == "ios" || $v == "苹果") {
                        $str .= ($k == 0? "": " AND ")."(type = 2)";
                    } elseif ($v == "ANDROID" || $v == "android" || $v == "Android" || $v == "安卓") {
                        $str .= ($k == 0? "": " AND ")."(type = 1)";
                    } else {
                        $str .= ($k == 0? "": " AND ")."(gameName LIKE '%".$v."%' OR channelName LIKE '%".$v."%' OR channelAbbr = '".$v."' OR agent = '".$v."' OR ver = '".$v."')";
                    }
                }
                $str && $map["_string"] = $str;
            }
            $count      = D("Admin")->getUpdateCount($map);
            $res        = D("Admin")->getUpdateInfo($map, $start, $pageSize);
            foreach ($res as $key => $val) {
                if ($val["type"] == "1") {
                    $type   = "安卓";
                } elseif ($val["type"] == "2") {
                    $type   = "IOS";
                } else {
                    $type   = "其他";
                }
                $map_str    = ($val["game_id"]? "游戏：".$val["gameName"]."，":"").($val["channel_id"]? "渠道：".$val["channelName"]."，":"").($val["agent"]? "渠道号：".$val["agent"]."，":"")."设备类型：".$type;
                $res[$key]["start"]         = $val["startTime"]? date("Y-m-d H:i:s", $val["startTime"]): "（无）";
                $res[$key]["end"]           = $val["endTime"]? date("Y-m-d H:i:s", $val["endTime"]): "（无）";
                $res[$key]["create"]        = $val["createTime"]? date("Y-m-d H:i:s", $val["createTime"]): "（无）";
                $res[$key]["map"]           = $map_str;
            }
            $arr = array("rows" => $res, "results" => $count);
            exit(json_encode($arr));
        } else {
            $this->display();
        }
    }

    /**
     * 添加更新
     */
    public function updateAdd()
    {
        if (IS_POST) {
            $data   = I("");
            if (!$data["game_id"] && !$data["channel_id"] && !$data["agent"]) $this->error("游戏、渠道、渠道号这三项不能全为空！");
            if (!$data["ver"] || !$data["path"]) $this->error("版本号以及下载地址不能为空！");
            $data["startTime"]  = $data["startTime"]? strtotime(date("Y-m-d H:00:00", strtotime($data["startTime"]))): "";
            $data["endTime"]    = $data["endTime"]? strtotime(date("Y-m-d H:00:00", strtotime($data["endTime"]))): "";
            $data["createTime"] = $data["updateTime"] = time();
            $res                = D("admin")->commonAdd("update", $data, C("DB_PREFIX_API"));
            if (!$res) $this->error("添加失败！");
            $this->success("添加成功！");
        } else {
            $game       = D("Admin")->commonQuery("game", array(), 0, 9999, "*", "lg_");
            $channel    = D("Admin")->commonQuery("channel", array(), 0, 9999, "*", "lg_");
            $this->assign("game", $game);
            $this->assign("channel", $channel);
            $response   = $this->fetch();
            $this->ajaxReturn(array("Result" => true, "Html" => $response));
        }
    }

    /**
     * 编辑更新
     */
    public function updateEdit()
    {
        if (IS_POST) {
            $data   = I("");
            if (!$data["id"]) $this->error("错误ID！");
            if (!$data["game_id"] && !$data["channel_id"] && !$data["agent"]) $this->error("游戏、渠道、渠道号这三项不能全为空！");
            if (!$data["ver"] || !$data["path"]) $this->error("版本号以及下载地址不能为空！");
            $data["startTime"]  = strtotime(date("Y-m-d H:00:00", strtotime($data["startTime"])));
            $data["endTime"]    = strtotime(date("Y-m-d H:00:00", strtotime($data["endTime"])));
            $data["updateTime"] = time();
            $res                = D("admin")->commonExecute("update", array("id" => $data["id"]), $data, C("DB_PREFIX_API"));
            if (!$res) $this->error("更新失败！");
            $this->success("更新成功！");
        } else {
            $id         = I("id");
            if (!$id) $this->ajaxReturn(array("Result" => false, "Msg" => "ID错误！"));
            $info       = D("Admin")->commonQuery("update", array("id" => $id), 0, 1, "*", "lg_");
            if (!$info) $this->ajaxReturn(array("Result" => false, "Msg" => "数据异常！"));
            $game       = D("Admin")->commonQuery("game", array(), 0, 9999, "*", "lg_");
            $channel    = D("Admin")->commonQuery("channel", array(), 0, 9999, "*", "lg_");
            $this->assign("info", $info);
            $this->assign("game", $game);
            $this->assign("channel", $channel);
            $response   = $this->fetch();
            $this->ajaxReturn(array("Result" => true, "Html" => $response));
        }
    }

    /**
     * 游戏批量按渠道号生成更新
     */
    public function updateAgent()
    {
        if (IS_POST) {
            $data   = I("");
            if (!$data["prefix"] || !$data["first"] || !$data["last"]) $this->error("渠道号未填写完整！");
            if (!$data["ver"]) $this->error("版本号不能为空！");
            $data["startTime"]  = $data["startTime"]? strtotime(date("Y-m-d H:00:00", strtotime($data["startTime"]))): "";
            $data["endTime"]    = $data["endTime"]? strtotime(date("Y-m-d H:00:00", strtotime($data["endTime"]))): "";
            $data["createTime"] = $data["updateTime"] = time();
            $list               = array();
            for ($i = $data["first"]; $i <= $data["last"]; $i++) {
                $arr            = $data;
                $arr["agent"]   = $data["prefix"].str_pad($i, 3, "0", STR_PAD_LEFT);
                $arr["path"]    = $this->update_address.$arr["agent"].".apk";
                $list[]         = $arr;
            }
            $res                = D("admin")->commonAddAll("update", $list, C("DB_PREFIX_API"));
            if (!$res) $this->error("添加失败！");
            $this->success("添加成功！");
        } else {
            $response   = $this->fetch();
            $this->ajaxReturn(array("Result" => true, "Html" => $response));
        }
    }

    /**
     * 版本补丁
     */
    public function patch()
    {
        if (IS_POST) {
            $data       = I();
            $start      = $data["start"]? $data["start"]: 0;
            $pageSize   = $data["limit"]? $data["limit"]: 30;
            $data["status"] && $map["a.status"] = $data["status"] - 1;
            $data["show"] && $map["endTime"] = array(array("GT", time()), array("EXP", "IS NULL"), array("EXP", "= ''"), "OR");
            if ($data["search"]) {
                $search = explode("|", $data["search"]);
                $str    = "";
                foreach ($search as $k => $v) {
                    if ($v == "IOS" || $v == "ios" || $v == "苹果") {
                        $str .= ($k == 0? "": " AND ")."(type = 2)";
                    } elseif ($v == "ANDROID" || $v == "android" || $v == "Android" || $v == "安卓") {
                        $str .= ($k == 0? "": " AND ")."(type = 1)";
                    } else {
                        $str .= ($k == 0? "": " AND ")."(gameName LIKE '%".$v."%' OR channelName LIKE '%".$v."%' OR channelAbbr = '".$v."' OR agent = '".$v."' OR ver = '".$v."')";
                    }
                }
                $str && $map["_string"] = $str;
            }
            $count      = D("Admin")->getPatchCount($map);
            $res        = D("Admin")->getPatchInfo($map, $start, $pageSize);
            foreach ($res as $key => $val) {
                if ($val["type"] == "1") {
                    $type   = "安卓";
                } elseif ($val["type"] == "2") {
                    $type   = "IOS";
                } else {
                    $type   = "其他";
                }
                $map_str    = ($val["game_id"]? "游戏：".$val["gameName"]."，":"").($val["channel_id"]? "渠道：".$val["channelName"]."，":"").($val["agent"]? "渠道号：".$val["agent"]."，":"")."设备类型：".$type;
                $res[$key]["start"]         = $val["startTime"]? date("Y-m-d H:i:s", $val["startTime"]): "（无）";
                $res[$key]["end"]           = $val["endTime"]? date("Y-m-d H:i:s", $val["endTime"]): "（无）";
                $res[$key]["create"]        = $val["createTime"]? date("Y-m-d H:i:s", $val["createTime"]): "（无）";
                $res[$key]["map"]           = $map_str;
            }
            $arr = array("rows" => $res, "results" => $count);
            exit(json_encode($arr));
        } else {
            $this->display();
        }
    }

    /**
     * 添加补丁
     */
    public function patchAdd()
    {
        if (IS_POST) {
            $data   = I("");
            if (!$data["game_id"] && !$data["channel_id"] && !$data["agent"]) $this->error("游戏、渠道、渠道号这三项不能全为空！");
            if (!$data["ver"] || !$data["patchVer"] || !$data["path"]) $this->error("SDK版本、补丁版本以及下载地址不能为空！");
            $data["startTime"]  = $data["startTime"]? strtotime(date("Y-m-d H:00:00", strtotime($data["startTime"]))): "";
            $data["endTime"]    = $data["endTime"]? strtotime(date("Y-m-d H:00:00", strtotime($data["endTime"]))): "";
            $data["createTime"] = $data["updateTime"] = time();
            $res                = D("admin")->commonAdd("patch", $data, C("DB_PREFIX_API"));
            if (!$res) $this->error("添加失败！");
            $this->success("添加成功！");
        } else {
            $game       = D("Admin")->commonQuery("game", array(), 0, 9999, "*", "lg_");
            $channel    = D("Admin")->commonQuery("channel", array(), 0, 9999, "*", "lg_");
            $this->assign("game", $game);
            $this->assign("channel", $channel);
            $response   = $this->fetch();
            $this->ajaxReturn(array("Result" => true, "Html" => $response));
        }
    }

    /**
     * 编辑补丁
     */
    public function patchEdit()
    {
        if (IS_POST) {
            $data   = I("");
            if (!$data["id"]) $this->error("错误ID！");
            if (!$data["game_id"] && !$data["channel_id"] && !$data["agent"]) $this->error("游戏、渠道、渠道号这三项不能全为空！");
            if (!$data["ver"] || !$data["patchVer"] || !$data["path"]) $this->error("SDK版本、补丁版本以及下载地址不能为空！");
            $data["startTime"]  = strtotime(date("Y-m-d H:00:00", strtotime($data["startTime"])));
            $data["endTime"]    = strtotime(date("Y-m-d H:00:00", strtotime($data["endTime"])));
            $data["updateTime"] = time();
            $res                = D("admin")->commonExecute("patch", array("id" => $data["id"]), $data, C("DB_PREFIX_API"));
            if (!$res) $this->error("更新失败！");
            $this->success("更新成功！");
        } else {
            $id         = I("id");
            if (!$id) $this->ajaxReturn(array("Result" => false, "Msg" => "ID错误！"));
            $info       = D("Admin")->commonQuery("patch", array("id" => $id), 0, 1, "*", "lg_");
            if (!$info) $this->ajaxReturn(array("Result" => false, "Msg" => "数据异常！"));
            $game       = D("Admin")->commonQuery("game", array(), 0, 9999, "*", "lg_");
            $channel    = D("Admin")->commonQuery("channel", array(), 0, 9999, "*", "lg_");
            $this->assign("info", $info);
            $this->assign("game", $game);
            $this->assign("channel", $channel);
            $response   = $this->fetch();
            $this->ajaxReturn(array("Result" => true, "Html" => $response));
        }
    }
}