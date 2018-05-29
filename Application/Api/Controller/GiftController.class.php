<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 2018/3/5
 * Time: 14:23
 *
 * 礼包控制器
 */

namespace Api\Controller;

class GiftController extends ApiController
{

    /**
     * 获取礼包列表接口
     */
    public function GetList()
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
        if (!$data["agent"]) {
            $res = array(
                "Msg" => "数据缺失！请重新请求！"
            );
            $this->returnMsg($res, 4, $input["Gid"], "无渠道号", 0, $input["Uid"], $input["Version"]);
        }

        //获取渠道信息
        $agent = D("Api/Agent")->getAgent($data["agent"]);
        if (!$agent) {
            $res = array(
                "Msg" => "数据异常！请重新点击！"
            );
            $this->returnMsg($res, 6, $input["Gid"], "无该渠道号！", 0, $input["Uid"], $input["Version"]);
        }

        //判断是子包还是母包
        if ($agent["agentType"] != "1" || $agent["pid"] != "0") {
            $p_agent = D("Api/Agent")->getAgentById($agent["pid"]);
            if (!$p_agent) {
                $res = array(
                    "Msg" => "数据异常！请重新点击！"
                );
                $this->returnMsg($res, 6, $input["Gid"], "无该母渠道号！", 0, $input["Uid"], $input["Version"]);
            }
            $map_agent = $p_agent["agent"];
        } else {
            $map_agent = $data["agent"];
        }

        //获取游戏信息
        $game = D("Api/Game")->getGame($agent["game_id"]);
        if (!$game) {
            $res = array(
                "Msg" => "数据异常！请重新点击！"
            );
            $this->returnMsg($res, 6, $input["Gid"], "无该游戏！", 0, $input["Uid"], $input["Version"]);
        }

        //列表开始字段
        $length = $data["length"]? $data["length"]: 0;

        //获取礼包列表
        $list   = D("Api/SdkGift")->getList($agent["game_id"], $map_agent, $length);
        if ($list === false) {
            $res = array(
                "Msg" => "传递数据错误！"
            );
            $this->returnMsg($res, 6, $input["Gid"], "传递的参数错误！", 0, $input["Uid"], $input["Version"]);
        } elseif (!$list) {
            $res = array(
                "Code"  => 1,
                "Msg"   => "抱歉！暂无礼包",
                "Gift"  => array()
            );
            $this->returnMsg($res, 0, $input["Gid"], "暂无礼包！", 0, $input["Uid"], $input["Version"]);
        }

        //循环获取礼包库存
        foreach ($list as $k => $v) {
            $stock = D("Api/SdkGiftCard")->getCardStock($v["id"]);
            if ($stock["stock"]) {
                $list[$k]["stock"]      = 0;                                                    //礼包存量，0：有，1：无
                $list[$k]["surplus"]    = ceil($stock["stock"] / $stock["count"] * 100);        //礼包剩余百分比
            } else {
                $list[$k]["stock"]      = 1;                                                    //礼包存量，0：有，1：无
                $list[$k]["surplus"]    = 0;                                                    //礼包剩余百分比
            }
        }

        //返回数据
        $res = array(
            "Code"  => 0,
            "Msg"   => "获取礼包列表成功！",
            "Gift"  => $list
        );

        $this->returnMsg($res, 0, $input["Gid"], "获取礼包列表成功", 0, $input["Uid"], $input["Version"]);
    }

    /**
     * 显示拥有的礼包码
     */
    public function GetOwnGift()
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
        if (!$data["agent"] || !$data["userCode"]) {
            $res = array(
                "Msg" => "数据缺失！请重新请求！"
            );
            $this->returnMsg($res, 4, $input["Gid"], "无UID或渠道号", 0, $input["Uid"], $input["Version"]);
        }

        //获取用户信息
        $user = D("Api/User")->getUserByCode($data["userCode"]);
        if (!$user) {
            $res = array(
                "Msg" => "数据异常！请重新登录！"
            );
            $this->returnMsg($res, 6, $input["Gid"], "无该用户！", 0, $input["Uid"], $input["Version"]);
        }

        //获取渠道信息
        $agent = D("Api/Agent")->getAgent($data["agent"]);
        if (!$agent) {
            $res = array(
                "Msg" => "数据异常！请重新点击！"
            );
            $this->returnMsg($res, 6, $input["Gid"], "无该渠道号！", 0, $input["Uid"], $input["Version"]);
        }

        //判断是子包还是母包
        if ($agent["agentType"] != "1" || $agent["pid"] != "0") {
            $p_agent = D("Api/Agent")->getAgentById($agent["pid"]);
            if (!$p_agent) {
                $res = array(
                    "Msg" => "数据异常！请重新点击！"
                );
                $this->returnMsg($res, 6, $input["Gid"], "无该母渠道号！", 0, $input["Uid"], $input["Version"]);
            }
            $map_agent = $p_agent["agent"];
        } else {
            $map_agent = $data["agent"];
        }

        //获取游戏信息
        $game = D("Api/Game")->getGame($agent["game_id"]);
        if (!$game) {
            $res = array(
                "Msg" => "数据异常！请重新点击！"
            );
            $this->returnMsg($res, 6, $input["Gid"], "无该游戏！", 0, $input["Uid"], $input["Version"]);
        }

        //列表开始字段
        $length = $data["length"]? $data["length"]: 0;

        //获取已经获取的礼包列表
        $list   = D("Api/SdkGiftCard")->getOwnCard($agent["game_id"], $map_agent, $data["userCode"], $length);
        if ($list === false) {
            $res = array(
                "Msg" => "传递数据错误！"
            );
            $this->returnMsg($res, 6, $input["Gid"], "传递的参数错误！", 0, $input["Uid"], $input["Version"]);
        } elseif (!$list) {
            $res = array(
                "Code"  => 1,
                "Msg"   => "没有更多了",
                "Gift"  => array()
            );
            $this->returnMsg($res, 0, $input["Gid"], "暂未领取礼包！", 0, $input["Uid"], $input["Version"]);
        }

        //返回数据
        $res = array(
            "Code"  => 0,
            "Msg"   => "获取礼包列表成功！",
            "Gift"  => $list
        );

        $this->returnMsg($res, 0, $input["Gid"], "获取礼包列表成功", 0, $input["Uid"], $input["Version"]);
    }

    /**
     * 显示礼包信息
     */
    public function ShowGift()
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
        if (!$data["giftId"]) {
            $res = array(
                "Msg" => "数据缺失！请重新请求！"
            );
            $this->returnMsg($res, 4, $input["Gid"], "无区服ID", 0, $input["Uid"], $input["Version"]);
        }

        //获取礼包列表
        $info   = D("Api/SdkGift")->getInfo($data["giftId"]);
        if (!$info) {
            $res = array(
                "Msg" => "数据错误！请重新请求！"
            );
            $this->returnMsg($res, 6, $input["Gid"], "无该礼包", 0, $input["Uid"], $input["Version"]);
        }

        //循环获取礼包库存
        $stock = D("Api/SdkGiftCard")->getCardStock($data["giftId"]);

        //返回的礼包信息
        $gift = array(
            "id"        => $info["id"],
            "gift"      => $info["gift"],
            "end"       => $info["endTime"]? date("Y-m-d H:00:00", $info["endTime"]): "永久有效",
            "show"      => json_decode($info["show"], true),
            "surplus"   => $stock["stock"]? ceil($stock["stock"] / $stock["count"] * 100): 0
        );

        //返回数据
        $res = array(
            "Code"  => 0,
            "Msg"   => "获取礼包列表成功！",
            "Gift"  => $gift
        );

        $this->returnMsg($res, 0, $input["Gid"], "获取礼包信息成功", 0, $input["Uid"], $input["Version"]);
    }

    /**
     * 显示区服列表
     */
    public function GetServerList()
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
        if (!$data["agent"]) {
            $res = array(
                "Msg" => "数据缺失！请重新请求！"
            );
            $this->returnMsg($res, 4, $input["Gid"], "无渠道号", 0, $input["Uid"], $input["Version"]);
        }

        //获取渠道信息
        $agent = D("Api/Agent")->getAgent($data["agent"]);
        if (!$agent) {
            $res = array(
                "Msg" => "数据异常！请重新点击！"
            );
            $this->returnMsg($res, 6, $input["Gid"], "无该渠道号！", 0, $input["Uid"], $input["Version"]);
        }

        //判断是子包还是母包
        if ($agent["agentType"] != "1" || $agent["pid"] != "0") {
            $p_agent = D("Api/Agent")->getAgentById($agent["pid"]);
            if (!$p_agent) {
                $res = array(
                    "Msg" => "数据异常！请重新点击！"
                );
                $this->returnMsg($res, 6, $input["Gid"], "无该母渠道号！", 0, $input["Uid"], $input["Version"]);
            }
            $map_agent = $p_agent["agent"];
        } else {
            $map_agent = $data["agent"];
        }

        //获取已经获取的礼包列表
        $list   = D("Api/Server")->getList(array("agent" => $map_agent, "game_id" => $agent["game_id"], "openTime" => array("LT", date("Y-m-d H:i:s"))));
        if (!$list) {
            $res = array(
                "Code"      => 1,
                "Msg"       => "暂无区服",
                "Server"    => array()
            );
            $this->returnMsg($res, 0, $input["Gid"], "暂无区服！", 0, $input["Uid"], $input["Version"]);
        }

        //返回数据
        $res = array(
            "Code"      => 0,
            "Msg"       => "获取区服列表成功！",
            "Server"    => $list
        );

        $this->returnMsg($res, 0, $input["Gid"], "获取区服列表成功", 0, $input["Uid"], $input["Version"]);
    }

    /**
     * 获取某个区服的角色信息
     */
    public function GetServerRole()
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
        if (!$data["agent"] || !$data["userCode"] || !$data["serverId"]) {
            $res = array(
                "Msg" => "数据缺失！请重新请求！"
            );
            $this->returnMsg($res, 4, $input["Gid"], "无UID、渠道号获取区服ID", 0, $input["Uid"], $input["Version"]);
        }

        //获取用户信息
        $user   = D("Api/User")->getUserByCode($data["userCode"]);
        if (!$user) {
            $res = array(
                "Msg" => "数据异常！请重新登录！"
            );
            $this->returnMsg($res, 6, $input["Gid"], "无该用户！", 0, $input["Uid"], $input["Version"]);
        }

        //获取渠道信息
        $agent  = D("Api/Agent")->getAgent($data["agent"]);
        if (!$agent) {
            $res = array(
                "Msg" => "数据异常！请重新点击！"
            );
            $this->returnMsg($res, 6, $input["Gid"], "无该渠道号！", 0, $input["Uid"], $input["Version"]);
        }

        //获取已经获取的礼包列表
        $role   = D("Api/Role")->getList(array("userCode" => $data["userCode"], "game_id" => $agent["game_id"], "serverId" => $data["serverId"]));
        if (!$role) {
            $res = array(
                "Code"  => 1,
                "Msg"   => "暂无角色",
                "Role"  => array()
            );
            $this->returnMsg($res, 0, $input["Gid"], "暂无角色！", 0, $input["Uid"], $input["Version"]);
        }

        //返回数据
        $res = array(
            "Code"  => 0,
            "Msg"   => "获取角色列表成功！",
            "Role"  => $role
        );

        $this->returnMsg($res, 0, $input["Gid"], "获取角色列表成功", 0, $input["Uid"], $input["Version"]);
    }

    /**
     * 获取礼包码
     */
    public function GetGift()
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
        if (!$data["userCode"] || !$data["agent"] || !$data["serverId"] || !$data["roleId"] || !$data["giftId"]) {
            $res = array(
                "Msg" => "数据缺失！请重新请求！"
            );
            $this->returnMsg($res, 4, $input["Gid"], "无UID、渠道号、区服ID、角色ID获取礼包ID", 0, $input["Uid"], $input["Version"]);
        }

        //获取用户信息
        $user   = D("Api/User")->getUserByCode($data["userCode"]);
        if (!$user) {
            $res = array(
                "Msg" => "数据异常！请重新登录！"
            );
            $this->returnMsg($res, 6, $input["Gid"], "无该用户！", 0, $input["Uid"], $input["Version"]);
        }

        //获取渠道信息
        $agent  = D("Api/Agent")->getAgent($data["agent"]);
        if (!$agent) {
            $res = array(
                "Msg" => "数据异常！请重新点击！"
            );
            $this->returnMsg($res, 6, $input["Gid"], "无该渠道号！", 0, $input["Uid"], $input["Version"]);
        }

        //判断是子包还是母包
        if ($agent["agentType"] != "1" || $agent["pid"] != "0") {
            $p_agent = D("Api/Agent")->getAgentById($agent["pid"]);
            if (!$p_agent) {
                $res = array(
                    "Msg" => "数据异常！请重新点击！"
                );
                $this->returnMsg($res, 6, $input["Gid"], "无该母渠道号！", 0, $input["Uid"], $input["Version"]);
            }
            $getAgent   = $p_agent["agent"];
        } else {
            $getAgent   = $data["agent"];
        }

        //获取角色列表
        $role   = D("Api/Role")->getRole(array("userCode" => $data["userCode"], "game_id" => $agent["game_id"], "serverId" => $data["serverId"], "roleId" => $data["roleId"]));
        if (!$role) {
            $res = array(
                "Msg" => "数据异常！请重新操作！"
            );
            $this->returnMsg($res, 6, $input["Gid"], "无该角色！", 0, $input["Uid"], $input["Version"]);
        }

        //获取礼包列表
        $gift   = D("Api/SdkGift")->getInfo($data["giftId"]);
        if (!$gift) {
            $res = array(
                "Msg" => "数据错误！请重新请求！"
            );
            $this->returnMsg($res, 6, $input["Gid"], "无该礼包", 0, $input["Uid"], $input["Version"]);
        }

        //判断是否游戏条件不对
        if (($gift["game_id"] && $gift["game_id"] != $agent["game_id"]) || ($gift["agent"] && $gift["agent"] != $getAgent)) {
            //返回数据
            $res = array(
                "Code"  => 4,
                "Msg"   => "您无权领取该礼包！",
                "Card"  => ""
            );
            $this->returnMsg($res, 0, $input["Gid"], "无权领取该礼包", 0, $input["Uid"], $input["Version"]);
        }

        //判断是否等级足够
        if ($gift["levelMin"] && $role["level"] < $gift["levelMin"]) {
            //返回数据
            $res = array(
                "Code"  => 5,
                "Msg"   => "您等级太低，暂时无法领取该礼包！",
                "Card"  => ""
            );
            $this->returnMsg($res, 0, $input["Gid"], "等级太低", 0, $input["Uid"], $input["Version"]);
        }

        //判断是否等级足够
        if ($gift["levelMax"] && $role["level"] > $gift["levelMax"]) {
            //返回数据
            $res = array(
                "Code"  => 5,
                "Msg"   => "您等级太高，无法领取该礼包！",
                "Card"  => ""
            );
            $this->returnMsg($res, 0, $input["Gid"], "等级太高", 0, $input["Uid"], $input["Version"]);
        }

        //是否已领取过该礼包
        $L_card = D("Api/SdkGiftCard")->getOneCard(array("sdk_gift_id" => $data["giftId"], "status" => 1, "userCode" => $data["userCode"], "roleId" => $data["roleId"], "serverId" => $data["serverId"]));
        if ($L_card) {
            //返回数据
            $res = array(
                "Code"  => 1,
                "Msg"   => "您已经领取过该礼包！",
                "Card"  => $L_card["card"]
            );
            $this->returnMsg($res, 0, $input["Gid"], "已领取该礼包", 0, $input["Uid"], $input["Version"]);
        }

        //判断是否是多次领取礼包
        if (D("Api/SdkGiftCard")->getOneCard(array("sdk_gift_id" => $data["giftId"], "status" => 1, "userCode" => $data["userCode"], "receiveTime" => array("EGT", strtotime(date("Y-m-d")))))) {
            //返回数据
            $res = array(
                "Code"  => 2,
                "Msg"   => "同一个礼包，一个账号一天只能领取一次！",
                "Card"  => ""
            );
            $this->returnMsg($res, 0, $input["Gid"], "一天多次领取礼包", 0, $input["Uid"], $input["Version"]);
        }

        $update = array(
            "status"        => 1,
            "userCode"      => $data["userCode"],
            "getAgent"      => $getAgent,
            "roleId"        => $data["roleId"],
            "roleName"      => $data["roleName"]? $data["roleName"]: $role["roleName"],
            "serverId"      => $data["serverId"],
            "serverName"    => $data["serverName"]? $data["serverName"]: $role["serverName"],
            "receiveTime"   => time()
        );

        //更新礼包码
        D("Api/SdkGiftCard")->updateGiftCardOne(array("sdk_gift_id" => $data["giftId"], "status" => 0), $update);

        //获取礼包码
        $card   = D("Api/SdkGiftCard")->getOneCard(array("sdk_gift_id" => $data["giftId"], "status" => 1, "userCode" => $data["userCode"], "roleId" => $data["roleId"], "serverId" => $data["serverId"]));
        if ($card) {
            //返回数据
            $res = array(
                "Code"  => 0,
                "Msg"   => "获取礼包码成功！",
                "Card"  => $card["card"]
            );
            $this->returnMsg($res, 0, $input["Gid"], "获取礼包码成功", 0, $input["Uid"], $input["Version"]);
        } else {
            //返回数据
            $res = array(
                "Code"  => 3,
                "Msg"   => "礼包码已被领取完毕！",
                "Card"  => ""
            );
            $this->returnMsg($res, 0, $input["Gid"], "礼包码已被领取完毕", 0, $input["Uid"], $input["Version"]);
        }
    }
}