<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/11/30
 * Time: 15:09
 *
 * 后台脚本API接口
 */

namespace Admin\Controller;

use Think\Controller;

class ApiBackstageController extends Controller
{

    private $SESSION_TIME       = 86400;                                            //SESSION的生命周期，单位秒
    private $SIGN_KEY           = "BackstageChuangyu123$%";                         //加密密钥
    private $BACKSTAGE          = array(                                            //平台集合
        2   => "qq",                                                                //广点通
        3   => "aiqiyi",                                                            //爱奇艺
        4   => "tui",                                                               //智汇推
        5   => "baiduss",                                                           //百度搜索
        6   => "toutiao",                                                           //今日头条
        7   => "uc",                                                                //UC
        8   => "allfootball",                                                       //懂球帝
        9   => "feather",                                                           //凤凰账号
        11  => "sina",                                                              //新浪扶翼
        12  => "sm",                                                                //神马
        13  => "sougouss",                                                          //搜狗搜索
        14  => "baidu",                                                             //百度信息流
        17  => "sohu",                                                              //搜狐汇算
        21  => "sougou",                                                            //搜狗
        25  => "wy",                                                                //网易易效
        29  => "kuaishou",                                                          //快手
        34  => "sohuads",                                                           //搜狐新品算
        31  => "win",                                                               //智营销
        35  => "dsp",                                                               //新数dsp
        74  => "miaopai",                                                           //秒拍
        91  => "df",                                                                //东方头条
    );

    /**
     * 登陆接口
     */
    public function Login()
    {
        //获取数据
        $data       = $this->getInput();
        //判断数据是否完整
        if (!$data["username"] || !$data["password"]) exit(json_encode(array("login_status" => 2)));
        //获取账户数据
        $admin      = D("Admin")->commonQuery("admin", array("name" => $data["username"], "status" => 0));
        //判断账户数据是否正确
        if (!$admin) exit(json_encode(array("login_status" => 3)));
        //判断密码是否正确
        if (!check_password($data["password"], $admin['password'])) exit(json_encode(array("login_status" => 2)));
        //生成session
        $session    = $this->makeSession($data["username"]);
        //存储session
        D("Admin")->commonExecute("admin", array("name" => $data["username"], "status" => 0), array("backstageSession" => $session, "backstageSessionTime" => time()));
        //获取账号
        $account    = D("Admin")->getAdvterAccount($admin["backstage_account_id"], 1);
        //插入渠道缩写
        foreach ($account as $key => $value) {
            $account[$key]["channel"]   = $this->BACKSTAGE[$value["backstage_id"]];
        }
        //返回数据
        exit(json_encode(array("login_status" => 1, "sessionid" => $session, "weblist" => $account? $account: array())));
    }

    /**
     * 获取验证码接口
     */
    public function Code()
    {
        //获取数据
        $data           = $this->getInput();
        //判断数据是否完整
        if (!$data["id"] || !$data["sessionid"]) exit(json_encode(array("status" => 3)));
        //获取用户数据
        $admin          = D("Admin")->commonQuery("admin", array("backstageSession" => $data["sessionid"], "status" => 0));
        //判断用户session是否有效
        if (!$admin || !$admin["backstageSessionTime"] || (time() - $admin["backstageSessionTime"] > $this->SESSION_TIME)) exit(json_encode(array("status" => 3)));
        //判断用户是否有该账号的权限
        if ($data["id"] != $admin["backstage_account_id"] && !in_array($data["id"], explode(",", $admin["backstage_account_id"]))) exit(json_encode(array("status" => 3)));
        //获取账号信息
        $account        = D("Admin")->getAdvterAccount($data["id"]);
        //判断平台是否存在
        if (!isset($this->BACKSTAGE[$account["backstage_id"]])) exit(json_encode(array("status" => 3)));
        //请求验证码
        $str            = file_get_contents("http://139.199.181.156:8000/analoglogin/".$this->BACKSTAGE[$account["backstage_id"]]."/");
        $arr            = json_decode($str, true);
        $arr["channel"] = $this->BACKSTAGE[$account["backstage_id"]];
        //返回数据
        exit(json_encode($arr));
    }

    /**
     * 获取cookie接口
     */
    public function Cookie()
    {
        //获取数据
        $data           = $this->getInput();
        //判断数据是否完整
        if (!$data["id"] || !$data["sessionid"] || !$data["code"] || !$data["cookie"]) exit(json_encode(array("status" => 3)));
        //获取用户数据
        $admin          = D("Admin")->commonQuery("admin", array("backstageSession" => $data["sessionid"], "status" => 0));
        //判断用户session是否有效
        if (!$admin || !$admin["backstageSessionTime"] || (time() - $admin["backstageSessionTime"] > $this->SESSION_TIME)) exit(json_encode(array("status" => 3)));
        //判断用户是否有该账号的权限
        if ($data["id"] != $admin["backstage_account_id"] && !in_array($data["id"], explode(",", $admin["backstage_account_id"]))) exit(json_encode(array("status" => 3)));
        //获取账号信息
        $account        = D("Admin")->getAdvterAccount($data["id"]);
        //判断平台是否存在
        if (!isset($this->BACKSTAGE[$account["backstage_id"]])) exit(json_encode(array("status" => 3)));
        //组装数据
        $info           = array(
            "vcode"     => $data["code"],
            "user"      => $account["account"],
            "password"  => $account["password"],
            "cookie"    => $data["cookie"]
        );
        //进行请求
        $res            = curl_post("http://139.199.181.156:8000/analoglogin/".$this->BACKSTAGE[$account["backstage_id"]]."/", http_build_query(array("data" => json_encode($info))));
        //解析数据
        $arr            = json_decode($res, true);
        //拼装数据
        $arr["url"]     = $account["url"];
        $arr["channel"] = $this->BACKSTAGE[$account["backstage_id"]];
        //返回数据
        exit(json_encode($arr));
    }

    /**
     * 保存广告账号cookie
     */
    public function SaveCookie()
    {
        //获取数据
        $data           = $this->getInput();
        //判断数据是否完整
        if (!$data["id"] || !$data["sessionid"] || !$data["cookie"]) exit(json_encode(array("status" => 3)));
        //获取用户数据
        $admin          = D("Admin")->commonQuery("admin", array("backstageSession" => $data["sessionid"], "status" => 0));
        //判断用户session是否有效
        if (!$admin || !$admin["backstageSessionTime"] || (time() - $admin["backstageSessionTime"] > $this->SESSION_TIME)) exit(json_encode(array("status" => 3)));
        //判断用户是否有该账号的权限
        if ($data["id"] != $admin["backstage_account_id"] && !in_array($data["id"], explode(",", $admin["backstage_account_id"]))) exit(json_encode(array("status" => 3)));
        //获取账号信息
        $account        = D("Admin")->getAdvterAccount($data["id"]);
        //判断平台是否存在
        if (!isset($this->BACKSTAGE[$account["backstage_id"]])) exit(json_encode(array("status" => 3)));
        //存储cookie
        D("Admin")->commonExecute("advter_account", array("id" => $data["id"], "status" => 1), array("cookie" => $data["cookie"], "cookieTime" => time()));
        exit(json_encode(array("status" => 0)));
    }

    /**
     * 获取广告账号cookie
     */
    public function GetCookie()
    {
        //获取数据
        $data           = $this->getInput();
        //判断数据是否完整
        if (!$data["id"] || !$data["sessionid"]) exit(json_encode(array("status" => 3)));
        //获取用户数据
        $admin          = D("Admin")->commonQuery("admin", array("backstageSession" => $data["sessionid"], "status" => 0));
        //判断用户session是否有效
        if (!$admin || !$admin["backstageSessionTime"] || (time() - $admin["backstageSessionTime"] > $this->SESSION_TIME)) exit(json_encode(array("status" => 3)));
        //判断用户是否有该账号的权限
        if ($data["id"] != $admin["backstage_account_id"] && !in_array($data["id"], explode(",", $admin["backstage_account_id"]))) exit(json_encode(array("status" => 3)));
        //获取账号信息
        $account        = D("Admin")->getAdvterAccount($data["id"]);
        //判断平台是否存在
        if (!isset($this->BACKSTAGE[$account["backstage_id"]])) exit(json_encode(array("status" => 3)));
        //返回cookie
        exit(json_encode(array("status" => 0, "cookie" => $account["cookie"]? $account["cookie"]: "", "time" => $account["time"]? $account["time"]: "", "control" => $account["control"])));
    }

    /**
     * 保存获取到的信息
     */
    public function SaveCost()
    {
        //获取数据
        $data       = $this->getInput();
        //判断数据是否完整
        if (!$data["id"] || !$data["sessionid"] || !$data["data"]) exit(json_encode(array("status" => 3, "msg" => "参数缺失")));

        //判断是否是脚本传递的数据
        if ($data["admin"] == 1) {
            $sign   = $this->checkSign($data["sessionid"]);
            if (!$sign) exit(json_encode(array("status" => 3, "msg" => "用户失效")));
            $admin  = array("real" => "系统用户", "partment" => 3);
        } else {
            //获取用户数据
            $admin      = D("Admin")->commonQuery("admin", array("backstageSession" => $data["sessionid"], "status" => 0));
            //判断用户session是否有效
            if (!$admin || !$admin["backstageSessionTime"] || (time() - $admin["backstageSessionTime"] > $this->SESSION_TIME)) exit(json_encode(array("status" => 3, "msg" => "用户失效")));
            //判断用户是否有该账号的权限
            if ($data["id"] != $admin["backstage_account_id"] && !in_array($data["id"], explode(",", $admin["backstage_account_id"]))) exit(json_encode(array("status" => 3, "msg" => "无此权限")));
        }

        //获取账号信息
        $account        = D("Admin")->commonQuery("advter_account", array("id" => $data["id"]));
        //解析数据
        $proxy          = getDataList("proxy", "id", C("DB_PREFIX"));
        $game           = getDataList("game", "id", C("DB_PREFIX_API"));
        $mainbody       = getDataList("mainbody", "id", C("DB_PREFIX"));
        $advterUser     = getDataList("advteruser", "id", C("DB_PREFIX"));
        $agent          = getDataList("agent", "agent", C("DB_PREFIX_API"));
        $eventAgent     = getDataList('events','agent',C('DB_PREFIX'),array('is_zrl'=>1));

        $agent_id       = field_to_key($agent, "id");
        //循环信息
        foreach ($data["data"] as $key => $val) {
            $arr        = array();
            $cost_arr   = array();
            //消耗按渠道号累加
            foreach ($val as $k => $v) {
                if (floatval($v) <= 0) continue;
                preg_match("/(?:\()(.*)(?:\))/i", $k, $agent_str);
                $agent_arr = explode("-", $agent_str[1]);
                if (!array_key_exists($agent_arr[0], $agent) || !$agent_arr[0]) exit(json_encode(array("status" => 2, "msg" => $agent_str[1]? "渠道号匹配错误：".$agent_str[1]: "匹配不到渠道号")));
                $cost_arr[$agent_arr[0]."-".$agent[$agent_arr[0]]["gameType"].($agent_arr[1]? "-".$agent_arr[1]: "")]   += $v;
                if ($agent[$agent_arr[0]]["agentType"] == "1" && $agent[$agent_arr[0]]["pid"] == "0") {
                    $arr[$agent_arr[0]] += $v;
                } else {
                    $arr[$agent_id[$agent[$agent_arr[0]]["pid"]]["agent"]]  += $v;
                }
            }
            //循环插入
            foreach ($arr as $a => $b) {
                $list   = array(
                    "accountId"     => $account["id"],
                    "account"       => $account["account"],
                    "mainbody"      => $mainbody[$agent[$a]["mainbody_id"]]["mainBody"],
                    "proxyName"     => $proxy[$account["proxyId"]]["proxyName"],
                    "companyName"   => $advterUser[$account["advteruserId"]]["company_name"],
                    "gameId"        => $agent[$a]["game_id"],
                    "gameName"      => $game[$agent[$a]["game_id"]]["gameName"],
                    "agent"         => $a,
                    "agentName"     => $agent[$a]["agentName"],
                    "date"          => $key,
                    "cost"          => round($b, 2),
                    "rebate"        => $account["rebate"],
                    "rebateType"    => $account["rebateType"],
                    "realCost"      => $account["rebateType"]? round(round($b, 2)*(1-($account["rebate"])/100), 2): round(round($b, 2)/(1+($account["rebate"])/100), 2),
                    "createTime"    => time(),
                    "createUser"    => $admin["real"],
                    "oneExamine"    => 0,
                    "twoExamine"    => 0
                );
                //判断是否已经存在
                $cost   = D("Admin")->commonQuery("finance_cost", array("date" => $list["date"], "account" => $list["account"], "companyName" => $list["companyName"], "agent" => $list["agent"]), 0, 1);
                if ($cost) {
                    //未被财务审核则可以修改
                    if (!$cost["twoExamine"]) {
                        D("Admin")->commonExecute("finance_cost", array("date" => $list["date"], "account" => $list["account"], "companyName" => $list["companyName"], "agent" => $list["agent"]), $list);
                    }
                } else {
                    //插入
                    D("Admin")->commonAdd("finance_cost", $list);
                }
            }
            //循环插入
            foreach ($cost_arr as $c => $d) {
                $agent_param    = explode("-", $c);
                $param          = array(
                    "costMonth"         => $key,
                    "advter_id"         => $agent_param[2]? $agent_param[2]: $eventAgent[$agent_param[0]]['id'],
                    "principal"         => $admin["real"],
                    "gameType"          => $agent_param[1] == 1? "安卓": "ios",
                    "gameName"          => $agent[$agent_param[0]]["pid"]? $agent_id[$agent[$agent_param[0]]["pid"]]["agentName"]: $agent[$agent_param[0]]["agentName"],
                    "agent"             => $agent_param[0],
                    "channelAccount"    => $account["account"],
                    "cost"              => $account["rebateType"]? round($d*(1-($account["rebate"])/100), 2): round($d/(1+($account["rebate"])/100), 2),
                    "createTime"        => time(),
                    "media"             => $advterUser[$account["advteruserId"]]["company_name"],
                    "creater"           => $admin["real"],
                    "departmentId"      => $account["departmentId"],
                    "game_id"           => $agent[$agent_param[0]]['game_id']
                );
                $map            = array(
                    "costMonth"         => $param["costMonth"],
                    "agent"             => $param["agent"],
                    "channelAccount"    => $param["channelAccount"],
                    "media"             => $param["media"],
                );
                if ($param["advter_id"]) {
                    $map["advter_id"]   = $param["advter_id"];
                } else {
                    $map["_string"]     = "advter_id IS NULL OR advter_id = ''";
                }
                //判断是否已经存在
                $advter_cost    = D("Admin")->commonQuery("advter_cost", $map, 0, 1);
                if ($advter_cost) {
                    //修改
                    D("Admin")->commonExecute("advter_cost", $map, $param);
                } else {
                    //插入
                    D("Admin")->commonAdd("advter_cost", $param);
                }
            }
        }
        exit(json_encode(array("status" => 1, "msg" => "上传成功")));
    }

    /**
     * 获取最新的版本号
     */
    public function Ver()
    {
        //获取数据
        $data   = $this->getInput();
        $sign   = $this->checkSign($data["sign"]);
        if (!$sign) exit(json_encode(array("status" => 0)));
        $info   = D("Admin")->getBackstageExe();
        if (!$info) exit(json_encode(array("status" => 0)));
        $res    = array(
            "status"    => 1,
            "ver"       => $info["ver"],
            "mac"       => "https://".$_SERVER["SERVER_NAME"]."/".$info["mac"],
            "win"       => "https://".$_SERVER["SERVER_NAME"]."/".$info["win"]
        );
        exit(json_encode($res));
    }

    /**
     * 获取请求的数据
     * @param string $type  接收类型
     * @param string $fun  过滤的方法
     * @return bool|mixed
     */
    private function getInput($type = "request", $fun = "")
    {
        $do = "htmlspecialchars";
        if ($fun) {
            if (is_string($fun)) {
                $do .= ",".$fun;
            } else {
                return false;
            }
        }
        if ($type == "get") {
            $data = I("get.", "", $do);
        } elseif ($type == "post") {
            $data = I("post.", "", $do);
        } elseif ($type == "request") {
            $data = I("request.", "", $do);
        }
        if (!$data) {
            $data = file_get_contents("php://input");
            $data = json_decode($data, true);
            isset($data["Data"]) && $data["Data"] = urldecode($data["Data"]);
        }

        return $data;
    }

    /**
     * 生成session
     * @param $name
     * @return string
     */
    private function makeSession($name)
    {
        return md5(C("COMPANY_PASSWORD").$name.time());
    }

    /**
     * 检测SIGN
     * @param $sign
     * @return bool
     */
    private function checkSign($sign)
    {
        $key    = 0;
        $time   = time();
        for ($k = $time - 60; $k <= $time + 60; $k ++) {
            if ($sign == md5($this->SIGN_KEY.$k)) {
                $key = 1;
                break;
            }
        }
        return $key? true: false;
    }

    /**
     * 机器获取所有cookie
     */
    public function MachineGetCookie()
    {
        $sign   = I("sign");
        $key    = $this->checkSign($sign);
        if (!$key) exit(json_encode(array("status" => 0)));
        //获取cookie信息
        $account    = D("Admin")->commonQuery("advter_account", array("status" => 1), 0, 999999, "id,account AS name,password,advteruserId AS backstage_id,cookie,cookieTime AS `time`,controlStatus AS control");
        foreach ($account as $a => $b) {
            $account[$a]["channel"] = $this->BACKSTAGE[$b["backstage_id"]];
        }
        //返回cookie
        exit(json_encode(array("status" => 0, "account" => $account)));
    }

    /**
     * 机器批量保存cookie
     */
    public function MachineSaveCookie()
    {
        $sign   = I("sign");
        $key    = $this->checkSign($sign);
        if (!$key) exit(json_encode(array("status" => 2)));
        //保存cookie
        $data   = I("data");
        foreach (json_decode($data, true) as $list) {
            if ($list["id"] && $list["cookie"]) D("Admin")->commonExecute("advter_account", array("id" => $list["id"], "status" => 1), array("cookie" => $list["cookie"], "cookieTime" => time()));
        }
        //返回信息
        exit(json_encode(array("status" => 0)));
    }

    /**
     * 机器保存获取的信息
     */
    public function MachineSaveInfo()
    {
        //返回信息
        exit(json_encode(array("status" => 0)));
    }

    /**
     * 获取苹果日报的信息
     */
    public function AppleDaily()
    {
        //获取数据
        $data   = $this->getInput();
        $sign   = $this->checkSign($data["sign"]);
        if (!$sign) exit(json_encode(array("status" => 1, "msg" => "加密验证错误！")));
        D("Admin")->deleteAppleDaily(array("account" => $data["account"], "day" => $data["day"]));
        $arr    = array();
        //添加数据
        foreach ($data["data"] as $list) {
            $arr[] = array(
                "account"       => $data["account"],
                "bundle"        => $data["bundle"],
                "package"       => $list["Package"],
                "title"         => $list["Title"],
                "units"         => $list["Units"],
                "proceeds"      => $list["Proceeds"],
                "currency"      => $list["Currency"],
                "price"         => $list["Price"],
                "day"           => $data["day"],
                "createTime"    => time()
            );
        }
        if (D("Admin")->commonAddAll("apple_platform_daily", $arr)) {
            exit(json_encode(array("status" => 0)));
        } else {
            exit(json_encode(array("status" => 1, "msg" => "添加失败！")));
        }
    }

    /**
     * 获取所有在用的账号
     */
    public function AccountList()
    {
        //获取数据
        $data   = $this->getInput();
        $sign   = $this->checkSign($data["sign"]);
        if (!$sign) exit(json_encode(array("status" => 1, "msg" => "加密验证错误！")));
        $user   = D("Admin")->commonQuery("admin", array("status" => 0, "manager_id" => 6), 0, 99999, "backstage_account_id");
        $id     = array();
        foreach ($user as $value) {
            if (!$value["backstage_account_id"]) continue;
            $arr = explode(",", $value["backstage_account_id"]);
            foreach ($arr as $v) {
                if ($v && !in_array($v, $id)) $id[] = $v;
            }
        }
        //获取账号
        $account    = D("Admin")->commonQuery("advter_account", array("id" => array("IN", $id), "controlStatus" => 1), 0, 99999999, "id,account,password,advteruserId AS backstage_id");
        //插入渠道缩写
        foreach ($account as $key => $value) {
            $account[$key]["channel"]   = $this->BACKSTAGE[$value["backstage_id"]];
        }
        //返回数据
        exit(json_encode(array("status" => 0, "account" => $account? $account: array())));
    }

    /**
     * 存储账号的充值情况
     */
    public function SaveAccountDetail()
    {
        //获取数据
        $data   = $this->getInput();
        $sign   = $this->checkSign($data["sign"]);
        if (!$sign) exit(json_encode(array("status" => 1, "msg" => "加密验证错误！")));
        //判断数据是否完整
        if (!$data["data"]) exit(json_encode(array("status" => 3, "msg" => "参数缺失")));
        //循环插入数据
        foreach ($data["data"] as $value) {
            if (!$value["id"] || !$value["type"] || !$value["date"] || !$value["amount"]) continue;
            $account    = D("Admin")->commonQuery("advter_account", array("id" => $value["id"]));
            $list       = array(
                "accountId" => $value["id"],
                "account"   => $account["account"],
                "type"      => $value["type"],
                "date"      => $value["date"],
                "amount"    => $value["amount"],
                "time"      => time(),
                "creator"   => "系统用户",
            );
            D("Admin")->commonAdd("advter_account_detail", $list);
        }
        exit(json_encode(array("status" => 1, "msg" => "添加成功")));
    }
}