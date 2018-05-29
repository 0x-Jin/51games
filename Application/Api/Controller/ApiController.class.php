<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/5/26
 * Time: 14:57
 *
 * Api接口控制器
 */

namespace Api\Controller;

use Think\Controller;
use Think\Log;

class ApiController extends Controller
{

    protected $desKey = "123456";                    //DES固定字符串密钥

    /**
     * 获取请求的数据
     * @param string $type  接收类型
     * @param string $fun  过滤的方法
     * @return bool|mixed
     */
    protected function getInput($type = "request", $fun = "")
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

        //版本有多个小数点点时，将多个小数点后的数据省略掉
        $data['Version'] = str_replace('_', '.', $data['Version']);
        if (substr_count($data['Version'], '.') > 1) {
            $length1    = strpos($data["Version"], ".");
            $str        = substr($data["Version"], $length1 + 1);
            $length2    = strpos($str, ".");
            $data["Version"] = substr($data["Version"], 0, $length1 + $length2 + 1);
        }

        return $data;
    }

    /**
     * 解密传输过来的数据
     * @param $input
     * @return mixed
     */
    protected function getDecrypt($input)
    {
        //重要数据完整性判断
        if (!$input["Gid"] || $input["Version"] == "" || !$input["Data"]) {
            $res = array(
                "Msg" => "数据异常！请重新再试！"
            );
            if ($input["Version"] >= "1.1") {
                $this->returnMsg($res, 2, $input["Gid"], "必要数据无法获取！", 1, 0, $input["Version"]);
            } else {
                $this->returnMsg($res, 2, $input["Gid"], "必要数据无法获取！", 2, 0, $input["Version"]);
            }
        }

        if (isset($input["Uid"])) {
            $token = D("Api/TokenGame")->getToken($input["Uid"], $input["Gid"]);
//            !$token && $token = D("Api/Token")->getToken($input["Uid"]);
            //判断用户是否存在，并且加密TOKEN未过期
            if ($token && time() - $token["loginTime"] < 86400) {
                $key = $token["secretToken"];
            } else {
                $res = array(
                    "Msg" => "数据异常！请重新再试！"
                );
                if ($input["Version"] >= "1.1") {
                    $this->returnMsg($res, 5, $input["Gid"], "TOKEN已过期！", 1, 0, $input["Version"]);
                } else {
                    $this->returnMsg($res, 5, $input["Gid"], "TOKEN已过期！", 2, 0, $input["Version"]);
                }
            }
        } else {
            $key = $this->desKey;
        }

        //解密
        $decrypt = $this->decryptInfo($input["Data"], $key, $input["Gid"]);

        //解密失败
        if ($decrypt["Code"]) {
            $res = array(
                "Msg" => "您的账号在异地登录！请重新登陆！"
            );
            if ($input["Version"] >= "1.1") {
                $this->returnMsg($res, 3, $input["Gid"], "数据解密失败！", 1, 0, $input["Version"]);
            } else {
                $this->returnMsg($res, 3, $input["Gid"], "数据解密失败！", 2, 0, $input["Version"]);
            }
        }

        //返回解密出来的数据
        return $decrypt["Data"];
    }

    /**
     * 返回数据
     * @param $data  待加密数据
     * @param $code  状态码，0：正常
     * @param $gid  游戏ID
     * @param $msg  返回信息
     * @param int $user  是否有用户，0：有，1：无，2：无密钥
     * @param int $uid  用户UID
     * @param string $ver  版本号
     */
    protected function returnMsg($data, $code, $gid, $msg, $user = 1, $uid = 0, $ver = "1.0")
    {
        if ($user == 2) {
            header('HTTP/1.1 404 Not Found');
            exit();
            //$key = $this->desKey;
        } elseif ($user == 1) {
            $key = $this->desKey;
        } else {
            $token = D("Api/TokenGame")->getToken($uid, $gid);
//            !$token && $token = D("Api/Token")->getToken($uid);
            //判断用户是否存在，并且加密TOKEN未过期
            if ($token && time() - $token["loginTime"] < 86400) {
                $key = $token["secretToken"];
            } else {
                $data   = array("Msg" => "数据异常！请重新再试！");
                $code   = 5;
                $msg    = "TOKEN已过期！";
                $key    = $this->desKey;
            }
        }
        $info       = $this->encryptInfo(json_encode($data), $key, $gid);

        $encrypt    = $info["Data"];
        if ($info["Code"] == 1) {
            $code = "1";
        }
        $res = array(
            "Code"      => $code,
            "Gid"       => $gid,
            "Version"   => $ver,
            "Msg"       => $msg,
            "Data"      => $encrypt
        );
        $uid && $res["Uid"] = $uid;
        echo json_encode($res);
        exit();
    }


    /**
     * 解密
     * @param $data 数据
     * @param $str 密钥字符串
     * @param $game_id 游戏ID
     * @return array 结果
     */
    protected function decryptInfo($data, $str, $game_id)
    {
        //获取游戏Key
        $game   = D("Api/Game")->getGame($game_id);

        if (!$game) {
            $res = array(
                "Code"  => 1,
                "Msg"   => "解密失败",
                "Data"  => array()
            );
            //返回结果
            return $res;
        }
        //初始化加解密类
        $des    = new \Vendor\DES3\P_DES3($str.$game["gameKey"]);
        //解密
        $secret = $des->decrypt($data);
        $res    = array();
        //解密成功
        if ($secret) {
            //处理数据
            //parse_str($secret, $info);

            $info = json_decode($secret, true);
            if (!$info) parse_str($secret, $info);
            if (is_array($info)) {
                //var_dump(json_decode($secret,true));die;
                $res = array(
                    "Code"  => 0,
                    "Msg"   => "解密成功",
                    "Data"  => $info
                );
            }
        }
        //解密失败
        !$res && $res = array(
            "Code"  => 1,
            "Msg"   => "解密失败",
            "Data"  => array()
        );
        //返回结果

        return $res;
    }

    /**
     * 加密
     * @param $data 数据
     * @param $str 密钥字符串
     * @param $game_id 游戏ID
     * @return array 结果
     */
    protected function encryptInfo($data, $str, $game_id)
    {
        //获取游戏Key
        $game   = D("Api/Game")->getGame($game_id);
        if (!$game) {
            $res = array(
                "Code"  => 1,
                "Msg"   => "解密失败",
                "Data"  => array()
            );
            //返回结果
            return $res;
        }

        //初始化加解密类
        $des    = new \Vendor\DES3\P_DES3($str.$game["gameKey"]);
        //加密
        $info   = $des->encrypt($data);

        if ($info) {
            //加密成功
            $res    = array(
                "Code"  => 0,
                "Msg"   => "加密成功",
                "Data"  => $info
            );
        } else {
            //加密失败
            $res    = array(
                "Code"  => 1,
                "Msg"   => "加密失败",
                "Data"  => array()
            );
        }
        //返回结果
        return $res;
    }

    /**
     * 记录日志
     * @param $log 日志内容
     * @param $type 日志类型
     * @param $file
     */
    protected function saveLog($log, $type, $file)
    {
        Log::write($log, $type, "", C("LOG_PATH")."/".$file);
    }
}