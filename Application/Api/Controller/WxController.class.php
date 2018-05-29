<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/12/21
 * Time: 17:24
 *
 * 微信公众号接口
 */

namespace Api\Controller;

use Think\Controller;

class WxController extends Controller
{

    private $token  = "omNv2jlakmldf";                      //微信验证token
    private $AppId  = "wx54c0837eedc55e99";                 //微信服务号APPID
    private $Secret = "d5235522358fa9d23c2e12abe73745c0";   //微信服务号SECRET

    /**
     * token验证接口
     */
    public function check()
    {
        $nonce      = $_GET["nonce"];
        $timestamp  = $_GET["timestamp"];
        $echostr    = $_GET["echostr"];
        $signature  = $_GET["signature"];
        //形成数组，然后按字典序排序
        $array      = array($nonce, $timestamp, $this->token);
        //拼接成字符串，sha1加密，后与signature进行校验
        $imp        = implode($array);
        $str        = sha1($imp);
        if ($str == $signature && $echostr) {
            //第一次接入weixin api接口的时候
            echo $echostr;
        } else {
            echo "fail";
        }
        exit;
    }

    /**
     * 获取礼包接口
     */
    public function getGift()
    {
//        $this->showWxMsg("礼包激活码：", "123abc", 1);
//        "https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx54c0837eedc55e99&redirect_uri=http%3a%2f%2fapisdk.chuangyunet.net%2fApi%2fWx%2fgetGift%2fgift%2f1&response_type=code&scope=snsapi_base&state=abcde#wechat_redirect";
        $gift   = $_GET["gift"];
        $code   = $_GET["code"];
        $state  = $_GET["state"];
        if (!$gift || !$code || !$state) $this->showWxMsg("提示：", "获取礼包数据失败！");
        $info   = D("Api/Gift")->getGift($gift[0]);
        if (!$info || $info["abbr"] != $state) $this->showWxMsg("提示：", "礼包识别失败！");
        if ($info["startTime"] && $info["startTime"] > time()) $this->showWxMsg("提示：", "礼包暂未开放领取！");
        if ($info["endTime"] && $info["endTime"] < time()) $this->showWxMsg("提示：", "礼包已结束领取！");
        if ($info["game_id"] || $info["channel_id"] || $info["agent"]) $this->showWxMsg("提示：", "您无权获取该礼包！");
        if ($info["type"]) {
            $res    = file_get_contents("https://api.weixin.qq.com/sns/oauth2/access_token?appid={$this->AppId}&secret={$this->Secret}&code=$code&grant_type=authorization_code");
            if (!$res) $this->showWxMsg("提示：", "微信接口获取失败！");
            $Res    = json_decode($res, true);
            if (!$Res || !$Res["openid"]) $this->showWxMsg("提示：", "用户信息获取失败！");
            $map    = array(
                "gift_id"   => $info["id"],
                "status"    => 1,
                "userType"  => 1,
                "userCode"  => $Res["openid"]."_WX"
            );
            $card   = D("Api/GiftCard")->getGiftCard($map);
            if ($card) $this->showWxMsg("礼包激活码：", $card["card"], 1);
            $where  = array(
                "gift_id"   => $info["id"],
                "status"    => 0
            );
            $data   = array(
                "status"        => 1,
                "userType"      => 1,
                "userCode"      => $Res["openid"]."_WX",
                "receiveTime"   => time()
            );
            if (!D("Api/GiftCard")->updateGiftCardOne($where, $data)) $this->showWxMsg("提示：", "礼包已被领取完啦！");
            $card   = D("Api/GiftCard")->getGiftCard($map);
            if ($card) $this->showWxMsg("礼包激活码：", $card["card"], 1);
            $this->showWxMsg("提示：", "领取礼包失败！");
        } else {
            $map    = array(
                "gift_id"   => $info["id"],
                "status"    => 2
            );
            $card   = D("Api/GiftCard")->getGiftCard($map);
            if ($card) $this->showWxMsg("礼包激活码：", $card["card"], 1);
            $this->showWxMsg("提示：", "暂无该礼包！");
        }
    }

    /**
     * 页面显示
     * @param $title
     * @param $gift
     * @param $status
     */
    private function showWxMsg($title, $gift, $status = 0)
    {
        $this->assign("title", $title);
        $this->assign("gift", $gift);
        $this->assign("status", $status);
        $this->display("./WxGift");
        die();
    }
}