<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/5/31
 * Time: 12:32
 *
 * 登陆控制器
 */

namespace Api\Controller;

class LoginController extends ApiController
{

    /**
     * 二登验证接口
     */
    public function CheckLogin(){
        //获取数据
        $data   = $this->getInput("post", "trim");

        //判断必要数据是否存在
        if (!$data["UserCode"]) $this->backMsg(2);
        if (!$data["LoginToken"]) $this->backMsg(3);
        if (!$data["GameId"]) $this->backMsg(5);
        if (!$data["Sign"]) $this->backMsg(4);

        //获取游戏信息
        $game = D("Api/Game")->getGame($data["GameId"]);
        if (!$game) $this->backMsg(6);

        //验证加密算法
        if ($data["Sign"] != md5($data["GameId"].$data["UserCode"].$data["LoginToken"].$game["gameKey"])) $this->backMsg(7);

        $token = D("Api/TokenGame")->getToken($data["UserCode"], $data["GameId"]);
        //验证登陆TOKEN
//        !$token && $token = D("Api/Token")->getToken($data["UserCode"]);
        if ($data["LoginToken"] != $token["loginToken"] || $token["loginTime"] - time() > 300) {
            $this->backMsg(1);
        }

        //成功
        $this->backMsg(0);
    }

    /**
     * 返回数据
     * @param $code 编码
     */
    private function backMsg($code)
    {
        $temp = array(
            0 => "验证成功！",
            1 => "验证失败！",
            2 => "获取不到用户唯一标识符！",
            3 => "获取不到用户登陆TOKEN！",
            4 => "获取不到加密参数SIGN！",
            5 => "获取不到游戏ID！",
            6 => "游戏ID错误！",
            7 => "加密SIGN错误！",
        );
        $res = array(
            "Status"    => $code,
            "Msg"       => $temp[$code]
        );
        echo json_encode($res);
        exit();
    }
}