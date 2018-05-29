<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/6/14
 * Time: 19:12
 *
 * 游戏控制器
 */

namespace ThirdParty\Controller;

class GameController extends BackendController
{

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
            $res        = D('Admin')->getBuiList("game", $map, $start, $pageSize, "lg_");
            $results    = $res['count'];
            foreach ($res['list'] as $key=>$val){
                $res['list'][$key]['gameName']      = '<a href="javascript:;" onclick="gameAgent('.$val['id'].',this)">'.$val['gameName'].'</a>';
                $res['list'][$key]['createTime']    = date('Y-m-d H:i:s', $val['createTime']);
                $res['list'][$key]['opt']           = '<a href="javascript:;" onclick="gameEdit('.$val['id'].',this)">编辑</a>';
                $res['list'][$key]['opt']           .= '&nbsp;<a href="javascript:;" onclick="goodsEdit('.$val['id'].',this)">商品</a>';
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

        if(IS_POST){
            if (!$data['id']) {
                IS_AJAX && $this->ajaxReturn(array(
                    'status'    => 0,
                    'info'      => '参数有误'
                ));
                $this->error('参数有误');
            }

            $game   = D('Admin')->commonQuery('game', array('id' => $data['id']), 0, 1, '*', 'lg_' );

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
            $game   = D('Admin')->commonQuery('game', array('id' => $data['id']), 0, 1, '*', 'lg_' );
            $goods  = D('Admin')->commonQuery('goods', array('game_id' => $data['id']), 0, 100, '*', 'lg_' );
            $this->assign('game', $game);
            $this->assign('goods', $goods);

            if (IS_AJAX) {
                $respose = $this->fetch();
                $this->ajaxReturn(array('status' => 1, '_html' => $respose));
            } else {
                $this->display();
            }
        }
    }

    /**
     * 添加商品ID
     */
    public function goodsAdd()
    {
        $data = I();
        //判断数据是否齐全
        if(!$data["game_id"] || !$data["goodsCode"] || !$data["name"] || !$data["amount"]) {
            $res = array(
                "Result"    => false,
                "Msg"       => "商品数据不全！",
            );
            echo json_encode($res);
            exit();
        }
        $data["type"] = $data["status"] = 0;
        $data["goodsCode"] = trim($data["goodsCode"]);
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
}