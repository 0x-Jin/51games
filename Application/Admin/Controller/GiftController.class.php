<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/12/22
 * Time: 15:58
 *
 * 礼包管理
 */

namespace Admin\Controller;

class GiftController extends BackendController
{

    private $AppId      = "wx54c0837eedc55e99";                                         //微信服务号APPID
    private $WeixinUrl  = "https://open.weixin.qq.com/connect/oauth2/authorize";        //微信链接

    /**
     * 礼包列表
     */
    public function index()
    {
        if (IS_POST) {
            $data       = I();
            $start      = $data["start"]? $data["start"]: 0;
            $pageSize   = $data["limit"]? $data["limit"]: 30;
            $this->parId && $map["department"] = array("IN", $this->parId);
            $data["gift"] && $map["gift"] = array("LIKE", "%".$data["gift"]."%");
            $data["show"] && $map["endTime"] = array(array("GT", time()), array("EXP", "IS NULL"), array("EXP", "= ''"), "OR");
            $res        = D("Admin")->getBuiList("gift", $map, $start, $pageSize, "lg_");
//            $agent      = getDataList("agent", "agent", "lg_");
//            $channel    = getDataList("channel", "id", "lg_");
//            $game       = getDataList("game", "id", "lg_");
            $rows       = $res["list"];
            $results    = $res["count"];
            $department = array(1 => "发行一部", 2 => "发行二部");
            foreach ($rows as $key => $val) {
                $rows[$key]["start"]            = $val["startTime"]? date("Y-m-d H:i:s", $val["startTime"]): "不限";
                $rows[$key]["end"]              = $val["endTime"]? date("Y-m-d H:i:s", $val["endTime"]): "不限";
                $rows[$key]["url"]              = $this->makeWxUrl($val["id"], $val["abbr"]);
                $rows[$key]["departmentName"]   = $department[$val["department"]];
//                if ($val["game_id"] || $val["channel_id"] || $val["agent"]) {
//                    $rows[$key]["ext"]      = trim((($val["game_id"]? "游戏：".$game[$val["game_id"]]["gameName"]."，": "").($val["channel_id"]? "渠道：".$channel[$val["channel_id"]]["channelName"]."，": "").($val["agent"]? "包体：".$agent[$val["agent"]]["agentName"]: "")), "，");
//                } else {
//                    $rows[$key]["ext"]      = "无限制";
//                }
                $card                           = D("Admin")->getGiftCardStock($val["id"]);
                if ($val["type"]) {
                    $rows[$key]["stock"]        = $card["stock"]."/".$card["count"];
                } else {
                    $rows[$key]["stock"]        = $card["count"];
                }
                $rows[$key]["opt"]              = "<a href='javascript:;' onclick='giftEdit(".$val["id"].", this)'>编辑</a>&nbsp;&nbsp;<a href='javascript:;' onclick='giftImport(".$val["id"].", this)'>导入</a>";
            }
            $arr = array("rows" => $rows, "results" => $results);
            exit(json_encode($arr));
        } else {
            $this->display();
        }
    }

    /**
     * 添加礼包码
     */
    public function giftAdd()
    {
        if (IS_POST) {
            $data   = I("");
            if (!$data["gift"]) $this->error("礼包名称不能全为空！");
            $data["startTime"]  = $data["startTime"]? strtotime(date("Y-m-d H:00:00", strtotime($data["startTime"]))): "0";
            $data["endTime"]    = $data["endTime"]? strtotime(date("Y-m-d H:00:00", strtotime($data["endTime"]))): "0";
            $data["createTime"] = time();
            $data["type"]       = 1;
            $data["creator"]    = session("admin.realname");
            $data["abbr"]       = substr(md5(time()), -5);
            $data["department"] = $this->parId? $this->parId: 1;
            $res                = D("admin")->commonAdd("gift", $data, C("DB_PREFIX_API"));
            if (!$res) $this->error("添加失败！");
            $this->success("添加成功！");
        } else {
            $response   = $this->fetch();
            $this->ajaxReturn(array("Result" => true, "Html" => $response));
        }
    }

    /**
     * 编辑礼包码
     */
    public function giftEdit()
    {
        if (IS_POST) {
            $data   = I("");
            if (!$data["id"]) $this->error("获取礼包ID失败！");
            if (!$data["gift"]) $this->error("礼包名称不能全为空！");
            $data["startTime"]  = $data["startTime"]? strtotime(date("Y-m-d H:00:00", strtotime($data["startTime"]))): "0";
            $data["endTime"]    = $data["endTime"]? strtotime(date("Y-m-d H:00:00", strtotime($data["endTime"]))): "0";
            $res                = D("admin")->commonExecute("gift", array("id" => $data["id"]), $data, C("DB_PREFIX_API"));
            if (!$res) $this->error("编辑失败！");
            $this->success("编辑成功！");
        } else {
            $id         = I("id");
            if (!$id) $this->ajaxReturn(array("Result" => false));
            $info       = D("admin")->commonQuery("gift", array("id" => $id), 0, 1, "*", "lg_");
            $this->assign("info", $info);
            $response   = $this->fetch();
            $this->ajaxReturn(array("Result" => true, "Html" => $response));
        }
    }

    /**
     * 导入礼包码
     */
    public function giftImport()
    {
        if (IS_POST) {
            $id     = I("id");
            if (!$id) $this->error("获取礼包ID失败！");
            $info   = D("admin")->commonQuery("gift", array("id" => $id), 0, 1, "type", "lg_");
            if (!$_FILES["file"]["name"]) {
                $this->error("没有传入Excel！");
            }
            //文件上传
            $file_info  = excel_file_upload("gift");
            if ($file_info && $file_info != "没有文件被上传！") {
                //获取文件数据并且转数组
                $fileName   = "./Uploads/".$file_info["file"]["savepath"].$file_info["file"]["savename"];
                $data       = excel_to_array($fileName);
                if ($data) {
                    unset($data[1]);//第一个行为标题，不需要入库
                    $arr    = array();
                    foreach ($data as $val) {
                        $arr[] = $val[0];
                    }
                    $count  = count($arr);
                    if (!$count) {
                        $this->error("获取礼包码失败！");
                    } else {
                        $gift   = D("Admin")->commonQuery("gift_card", array("gift_id" => $id, "card" => array("IN", $arr)), 0, 99999999, "card", C("DB_PREFIX_API"));
                        if ($gift) {
                            $giftCount = count($gift);
                        } else {
                            $giftCount = 0;
                        }
                        $giftList   = array();
                        foreach ($gift as $value) {
                            $giftList[] = $value["card"];
                        }
                        $add    = array();
                        foreach ($arr as $v) {
                            if (!in_array($v, $giftList)) {
                                $add[]  = array(
                                    "gift_id"       => $id,
                                    "card"          => $v,
                                    "status"        => $info["type"] == "0"? 2: 0,
                                    "use"           => 0,
                                    "createTime"    => time()
                                );
                            }
                        }
                        if ($add) {
                            $res    = D("admin")->commonAddAll("gift_card", $add, C("DB_PREFIX_API"));
                            if (!$res) {
                                $this->error("添加失败！");
                            } else {
                                $successCount   = count($add);
                                $this->success("礼包码导入成功！<br />导入数：{$count}，重复数：{$giftCount}，导入成功数：{$successCount}", "", 10);
                            }
                        } else {
                            $this->success("礼包码导入成功！<br />导入数：{$count}，重复数：{$giftCount}，导入成功数：0", "", 10);
                        }
                    }
                } else {
                    $this->error("礼包码导入失败！");
                }
            } else {
                $this->error("礼包码导入失败！");
            }
        } else {
            $id         = I("id");
            if (!$id) $this->ajaxReturn(array("Result" => true, "Html" => "ID获取失败！"));
            $this->assign("id", $id);
            $response   = $this->fetch();
            $this->ajaxReturn(array("Result" => true, "Html" => $response));
        }
    }

    /**
     * 生成微信跳转链接
     * @param $id 礼包ID
     * @param $state 礼包标识
     * @return string
     */
    private function makeWxUrl($id, $state)
    {
        return $this->WeixinUrl."?appid=".$this->AppId."&redirect_uri=".urlencode("https://apisdk.chuangyunet.net/Api/Wx/getGift/gift/".$id)."&response_type=code&scope=snsapi_base&state=".$state."#wechat_redirect";
    }

    /**
     * SDK礼包页面
     */
    public function sdkGift()
    {
        if (IS_POST) {
            $data       = I();
            $start      = $data["start"]? $data["start"]: 0;
            $pageSize   = $data["limit"]? $data["limit"]: 30;
            $data["gift"] && $map["gift"] = array("LIKE", "%".$data["gift"]."%");
            $data["game_id"] && $map["game_id"] = $data["game_id"];
            $data["show"] && $map["endTime"] = array(array("GT", time()), array("EXP", "IS NULL"), array("EXP", "= ''"), "OR");
            $res        = D("Admin")->getBuiList("sdk_gift", $map, $start, $pageSize, "lg_");
            $agent      = getDataList("agent", "agent", "lg_");
            $game       = getDataList("game", "id", "lg_");
            $rows       = $res["list"];
            $results    = $res["count"];
            foreach ($rows as $key => $val) {
                $rows[$key]["gameName"]     = $val["game_id"]? $game[$val["game_id"]]["gameName"]: "无";
                if ($val["startTime"] && $val["endTime"]) {
                    $rows[$key]["time"]     = date("Y-m-d H:i:s", $val["startTime"])." -- ".date("Y-m-d H:i:s", $val["endTime"]);
                } elseif ($val["startTime"]) {
                    $rows[$key]["time"]     = date("Y-m-d H:i:s", $val["startTime"])." -- 无";
                } elseif ($val["endTime"]) {
                    $rows[$key]["time"]     = "无 -- ".date("Y-m-d H:i:s", $val["endTime"]);
                } else {
                    $rows[$key]["time"]     = "无限制";
                }
                if ($val["game_id"] || $val["agent"] || $val["levelMin"] || $val["levelMax"]) {
                    $rows[$key]["ext"]      = trim((($val["agent"]? "母包：".$agent[$val["agent"]]["agentName"]."，": ($val["game_id"]? "游戏：".$game[$val["game_id"]]["gameName"]."，": "")).($val["levelMin"]? "最低等级：".$val["levelMin"]."，": "").($val["levelMax"]? "最高等级：".$val["levelMax"]."，": "")), "，");
                } else {
                    $rows[$key]["ext"]      = "无限制";
                }
                $card                       = D("Admin")->getSdkGiftCardStock($val["id"]);
                if ($val["type"]) {
                    $rows[$key]["stock"]    = $card["stock"]."/".$card["count"];
                } else {
                    $rows[$key]["stock"]    = $card["count"];
                }
                $show                       = json_decode($val["show"], true);
                $list                       = array();
                foreach ($show as $k => $v) {
                    $list[]                 = $k.":".$v;
                }
                $rows[$key]["show"]         = implode("\n", $list);
                $rows[$key]["opt"]          = "<a href='javascript:;' onclick='giftEdit(".$val["id"].", this)'>编辑</a>&nbsp;&nbsp;<a href='javascript:;' onclick='giftImport(".$val["id"].", this)'>导入</a>";
            }
            $arr = array("rows" => $rows, "results" => $results);
            exit(json_encode($arr));
        } else {
            $this->display();
        }
    }

    /**
     * 添加SDK礼包码
     */
    public function sdkGiftAdd()
    {
        if (IS_POST) {
            $data   = I("");
            if (!$data["gift"]) $this->error("礼包名称不能全为空！");
            $data["startTime"]  = $data["startTime"]? strtotime(date("Y-m-d H:00:00", strtotime($data["startTime"]))): "0";
            $data["endTime"]    = $data["endTime"]? strtotime(date("Y-m-d H:00:00", strtotime($data["endTime"]))): "0";
            //等级限制
            if ($data["level_type"] == "1") {
                $data["levelMin"] = $data["level"];
            } elseif ($data["level_type"] == "2") {
                $data["levelMax"] = $data["level"];
            }
            $show = array("礼包内容" => $data["content"]? $data["content"]: "无");
            foreach ($data as $k => $v) {
                if (strpos($k, "name") !== false && $v) {
                    $num        = substr($k, 4);
                    $show[$v]   = $data["value".$num]? $data["value".$num]: "无";
                }
            }
            $data["show"]       = json_encode($show);
            $data["createTime"] = time();
            $data["type"]       = 1;
            $data["channel_id"] = 1;
            $data["admin_id"]   = session("admin.uid");
            $data["creator"]    = session("admin.realname");
            $res                = D("admin")->commonAdd("sdk_gift", $data, C("DB_PREFIX_API"));
            if (!$res) $this->error("添加失败！");
            $this->success("添加成功！");
        } else {
            $response   = $this->fetch();
            $this->ajaxReturn(array("Result" => true, "Html" => $response));
        }
    }

    /**
     * SDK礼包修改
     */
    public function sdkGiftEdit()
    {
        if (IS_POST) {
            $data   = I("");
            if (!$data["id"]) $this->error("获取礼包ID失败！");
            if (!$data["gift"]) $this->error("礼包名称不能全为空！");
            $data["startTime"]  = $data["startTime"]? strtotime(date("Y-m-d H:00:00", strtotime($data["startTime"]))): "0";
            $data["endTime"]    = $data["endTime"]? strtotime(date("Y-m-d H:00:00", strtotime($data["endTime"]))): "0";
            //等级限制
            if ($data["level_type"] == "1") {
                $data["levelMin"] = $data["level"];
                $data["levelMax"] = "";
            } elseif ($data["level_type"] == "2") {
                $data["levelMin"] = "";
                $data["levelMax"] = $data["level"];
            } else {
                $data["levelMin"] = "";
                $data["levelMax"] = "";
            }
            $show = array("礼包内容" => $data["content"]? $data["content"]: "无");
            foreach ($data as $k => $v) {
                if (strpos($k, "name") !== false && $v) {
                    $num        = substr($k, 4);
                    $show[$v]   = $data["value".$num]? $data["value".$num]: "无";
                }
            }
            $data["show"]       = json_encode($show);
            $data["updateTime"] = time();
            $res                = D("admin")->commonExecute("sdk_gift", array("id" => $data["id"]), $data, C("DB_PREFIX_API"));
            if (!$res) $this->error("编辑失败！");
            $this->success("编辑成功！");
        } else {
            $id         = I("id");
            if (!$id) $this->ajaxReturn(array("Result" => false));
            $info       = D("admin")->commonQuery("sdk_gift", array("id" => $id), 0, 1, "*", "lg_");
            $show       = json_decode($info["show"], true);
            unset($show["礼包内容"]);
            $arr        = array();
            foreach ($show as $k => $v) {
                $arr[]  = array("name" => $k, "value" => $v);
            }
            $this->assign("info", $info);
            $this->assign("arr", $arr);
            $response   = $this->fetch();
            $this->ajaxReturn(array("Result" => true, "Html" => $response));
        }
    }

    /**
     * 导入SDK礼包码
     */
    public function sdkGiftImport()
    {
        if (IS_POST) {
            $id     = I("id");
            if (!$id) $this->error("获取礼包ID失败！");
            $info   = D("admin")->commonQuery("sdk_gift", array("id" => $id), 0, 1, "type,gift,game_id,channel_id,agent", "lg_");
            if (!$_FILES["file"]["name"]) {
                $this->error("没有传入Excel！");
            }
            //文件上传
            $file_info  = excel_file_upload("gift");
            if ($file_info && $file_info != "没有文件被上传！") {
                //获取文件数据并且转数组
                $fileName   = "./Uploads/".$file_info["file"]["savepath"].$file_info["file"]["savename"];
                $data       = excel_to_array($fileName);
                if ($data) {
                    unset($data[1]);//第一个行为标题，不需要入库
                    $arr    = array();
                    foreach ($data as $val) {
                        $arr[] = $val[0];
                    }
                    $count  = count($arr);
                    if (!$count) {
                        $this->error("获取礼包码失败！");
                    } else {
                        $gift   = D("Admin")->commonQuery("sdk_gift_card", array("gift_id" => $id, "card" => array("IN", $arr)), 0, 99999999, "card", C("DB_PREFIX_API"));
                        if ($gift) {
                            $giftCount = count($gift);
                        } else {
                            $giftCount = 0;
                        }
                        $giftList   = array();
                        foreach ($gift as $value) {
                            $giftList[] = $value["card"];
                        }
                        $add    = array();
                        foreach ($arr as $v) {
                            if (!in_array($v, $giftList)) {
                                $add[]  = array(
                                    "sdk_gift_id"   => $id,
                                    "gift"          => $info["gift"],
                                    "card"          => $v,
                                    "game_id"       => $info["game_id"],
                                    "channel_id"    => $info["channel_id"],
                                    "agent"         => $info["agent"],
                                    "status"        => $info["type"] == "0"? 2: 0,
                                    "createTime"    => time()
                                );
                            }
                        }
                        if ($add) {
                            $res    = D("admin")->commonAddAll("sdk_gift_card", $add, C("DB_PREFIX_API"));
                            if (!$res) {
                                $this->error("添加失败！");
                            } else {
                                $successCount   = count($add);
                                $this->success("礼包码导入成功！<br />导入数：{$count}，重复数：{$giftCount}，导入成功数：{$successCount}", "", 10);
                            }
                        } else {
                            $this->success("礼包码导入成功！<br />导入数：{$count}，重复数：{$giftCount}，导入成功数：0", "", 10);
                        }
                    }
                } else {
                    $this->error("礼包码导入失败！");
                }
            } else {
                $this->error("礼包码导入失败！");
            }
        } else {
            $id         = I("id");
            if (!$id) $this->ajaxReturn(array("Result" => true, "Html" => "ID获取失败！"));
            $info       = D("admin")->commonQuery("sdk_gift", array("id" => $id), 0, 1, "*", "lg_");
            $this->assign("info", $info);
            $response   = $this->fetch();
            $this->ajaxReturn(array("Result" => true, "Html" => $response));
        }
    }
}