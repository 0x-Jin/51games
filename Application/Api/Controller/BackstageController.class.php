<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/10/25
 * Time: 11:30
 * 后台登陆的软件对应的API接口
 */

namespace Api\Controller;

class BackstageController extends ApiController
{

    private $PASSWORD_PREFIX    = "La_";                                            //后台密码前缀
    private $SESSION_TIME       = 86400;                                            //SESSION的生命周期，单位秒
    private $SIGN_KEY           = "BackstageChuangyu123$%";                         //加密密钥
    private $BACKSTAGE          = array(                                            //平台集合
//        1   => "toutiao",                                                           //今日头条
//        2   => "baidu",                                                             //百度搜索
//        3   => "baidu",                                                             //百度信息流
//        4   => "sm",                                                                //神马
//        5   => "sougou",                                                            //搜狗
//        6   => "wy",                                                                //网易易效
//        7   => "uc",                                                                //UC
//        8   => "sohu",                                                              //搜狐汇算
//        9   => "sohuads",                                                           //搜狐新品算
//        10  => "aiqiyi",                                                            //爱奇艺
//        11  => "qq",                                                                //广点通
//        12  => "tui",                                                               //智汇推
//        13  => "sina",                                                              //新浪扶翼
//        15  => "feather",                                                           //凤凰账号
//        16  => "allfootball",                                                       //懂球帝

        2   => "qq",                                                                //广点通
        3   => "aiqiyi",                                                            //爱奇艺
        4   => "tui",                                                               //智汇推
        5   => "baidu",                                                             //百度搜索
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
        34  => "sohuads",                                                           //搜狐新品算
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
        $admin      = D("Api/Admin")->getAdmin($data["username"]);
        //判断账户数据是否正确
        if (!$admin) exit(json_encode(array("login_status" => 3)));
        //判断密码是否正确
        if (!$this->checkPassword($data["password"], $admin["password"])) exit(json_encode(array("login_status" => 2)));
        //生成session
        $session    = $this->makeSession($data["username"]);
        //存储session
        D("Api/Admin")->saveSession($data["username"], $session);
        //获取账号
        $account    = D("Api/AdvterAccount")->getAdvterAccount($admin["backstage_account_id"]);
//        $account    = D("Api/BackstageAccount")->getAdminAccount($admin["backstage_account_id"]);
        //返回数据
        exit(json_encode(array("login_status" => 1, "sessionid" => $session, "weblist" => $account)));
    }

    /**
     * 获取验证码接口
     */
    public function Code()
    {
        //获取数据
        $data       = $this->getInput();
        //判断数据是否完整
        if (!$data["id"] || !$data["sessionid"]) exit(json_encode(array("status" => 3)));
        //获取用户数据
        $admin      = D("Api/Admin")->getAdminBySession($data["sessionid"]);
        //判断用户session是否有效
        if (!$admin || !$admin["backstageSessionTime"] || (time() - $admin["backstageSessionTime"] > $this->SESSION_TIME)) exit(json_encode(array("status" => 3)));
        //判断用户是否有该账号的权限
        if ($data["id"] != $admin["backstage_account_id"] && !in_array($data["id"], explode(",", $admin["backstage_account_id"]))) exit(json_encode(array("status" => 3)));
        //获取账号信息
        $account    = D("Api/AdvterAccount")->getAccount($data["id"]);
//        $account    = D("Api/BackstageAccount")->getAccount($data["id"]);
        //判断平台是否存在
        if (!isset($this->BACKSTAGE[$account["backstage_id"]])) exit(json_encode(array("status" => 3)));
        //请求验证码
        $str            = file_get_contents("http://139.199.197.21:8000/analoglogin/".$this->BACKSTAGE[$account["backstage_id"]]."/");
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
        $admin          = D("Api/Admin")->getAdminBySession($data["sessionid"]);
        //判断用户session是否有效
        if (!$admin || !$admin["backstageSessionTime"] || (time() - $admin["backstageSessionTime"] > $this->SESSION_TIME)) exit(json_encode(array("status" => 3)));
        //判断用户是否有该账号的权限
        if ($data["id"] != $admin["backstage_account_id"] && !in_array($data["id"], explode(",", $admin["backstage_account_id"]))) exit(json_encode(array("status" => 3)));
        //获取账号信息
        $account        = D("Api/AdvterAccount")->getAccount($data["id"]);
//        $account        = D("Api/BackstageAccount")->getAccount($data["id"]);
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
        $res            = curl_post("http://139.199.197.21:8000/analoglogin/".$this->BACKSTAGE[$account["backstage_id"]]."/", http_build_query(array("data" => json_encode($info))));
        //解析数据
        $arr            = json_decode($res, true);
        //拼装数据
        $arr["url"]     = $account["url"];
        $arr["channel"] = $this->BACKSTAGE[$account["backstage_id"]];
        //返回数据
        exit(json_encode($arr));
    }

    /**
     * 检测密码是否正确
     * @param $password
     * @param $hash
     * @return bool
     */
    private function checkPassword($password, $hash)
    {
        return password_verify($this->PASSWORD_PREFIX.$password, $hash);
    }

    /**
     * 生成session
     * @param $name
     * @return string
     */
    private function makeSession($name)
    {
        return md5($this->PASSWORD_PREFIX.$name.time());
    }

    /**
     * 获取最新的版本号
     */
    public function Ver()
    {
        //获取数据
        $data   = $this->getInput();
        $sign   = 0;
        $time   = time();
        for ($k = $time - 60; $k <= $time + 60; $k ++) {
            if ($data["sign"] == md5($this->SIGN_KEY.$k)) {
                $sign = 1;
                break;
            }
        }
        if ($sign != 1) exit(json_encode(array("status" => 0)));
        $info   = D("Api/BackstageExe")->getVer();
        if (!$info) exit(json_encode(array("status" => 0)));
        $res    = array(
            "status"    => 1,
            "ver"       => $info["ver"],
            "address32" => $info["address32"],
            "address64" => $info["address64"]
        );
        exit(json_encode($res));
    }
}