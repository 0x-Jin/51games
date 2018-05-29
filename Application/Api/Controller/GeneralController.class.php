<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/5/29
 * Time: 20:05
 *
 * 用户控制器
 */

namespace Api\Controller;

class GeneralController extends ApiController
{

    /**
     * 手机验证码接口，验证码类型，0：其他，1：手机注册，2：绑定手机，3：手机解绑，4：修改密码，5：验证码登陆
     */
    public function MobileCode()
    {
        //获取数据
        $input  = $this->getInput("post", "trim");

        //解密出来的数据
        $data   = $this->getDecrypt($input);

        //判断必要数据是否存在
        if (!$data["mobile"]) {
            $res = array(
                "Msg" => "请输入手机号码！"
            );
            $this->returnMsg($res, 4, $input["Gid"], "请输入手机号码", 1, 0, $input["Version"]);
        }

        //获取用户数据
        $info = D("Api/User")->getUser(array("mobileStatus" => 0, "mobile" => $data["mobile"]));
        if ($data["type"] == 1) {
            //判断是否已经被注册
            if ($info) {
                $res = array(
                    "Msg" => "该手机号已被注册！"
                );
                $this->returnMsg($res, 5, $input["Gid"], "该手机号已被注册", 1, 0, $input["Version"]);
            }
        } elseif ($data["type"] == 2) {
            //判断是否已经被绑定
            if ($info) {
                $res = array(
                    "Msg" => "该手机号已被绑定！"
                );
                $this->returnMsg($res, 5, $input["Gid"], "该手机号已被绑定", 1, 0, $input["Version"]);
            }
        } elseif ($data["type"] == 3) {
            //判断是否未被绑定
            if (!$info) {
                $res = array(
                    "Msg" => "该手机号未被绑定！"
                );
                $this->returnMsg($res, 5, $input["Gid"], "该手机号未被绑定", 1, 0, $input["Version"]);
            }
        } elseif ($data["type"] == 4) {
            //判断是否未被注册
            if (!$info) {
                $res = array(
                    "Msg" => "该手机号未被注册！"
                );
                $this->returnMsg($res, 5, $input["Gid"], "该手机号未被注册", 1, 0, $input["Version"]);
            }
        }

        //判断是否有验证码类型
        if (!isset($data["type"])) {
            $res = array(
                "Msg" => "数据异常！请重试！"
            );
            $this->returnMsg($res, 4, $input["Gid"], "详细数据缺失", 1, 0, $input["Version"]);
        }

        //发送手机验证码
        if ($input["Gid"] == "117") {
            //皇城传说的验证码显示为蓝洞科技
            $gameName = "【蓝洞科技】";
        } else {
            $gameName = false;
        }
        $sms = sms_code($data["mobile"], $data["type"], $gameName);

        //发送失败
        if ($sms["Code"] == false) {
            $res = array(
                "Msg" => $sms["Msg"]
            );
            $this->returnMsg($res, 5, $input["Gid"], $sms["Msg"], 1, 0, $input["Version"]);
        }

        //IP地址
        $ip     = get_ip_address();
        $area   = ip_to_location($ip);
        //原因
        switch ($data["type"]) {
            case 1:
                $type = "手机注册账号";
                break;
            case 2:
                $type = "账号绑定手机";
                break;
            case 3:
                $type = "账号解绑手机";
                break;
            case 4:
                $type = "修改账号密码";
                break;
            case 5:
                $type = "验证码登陆账号";
                break;
            default:
                $type = "其他原因";
        }
        //操作日志记录
        $log    = array(
            "time"      => time(),
            "action"    => "MobileCode",
            "user"      => $data["userCode"],
            "agent"     => $data["agent"],
            "type"      => $data["type"],
            "udid"      => $data["udid"],
            "imei"      => $data["imei"],
            "idfa"      => $data["idfa"],
            "ip"        => $ip,
            "city"      => $area["city"],
            "province"  => $area["province"],
            "net"       => $data["net"],
            "log"       => "手机【".$data["mobile"]."】因为".$type."获取验了证码，短信验证码：".$sms["Sms"],
        );
        D("Api/Operation")->addLog($log);

        //发送成功
        $res = array(
            "Msg" => $sms["Msg"]
        );
        $this->returnMsg($res, 0, $input["Gid"], $sms["Msg"], 1, 0, $input["Version"]);
    }

    /**
     * 用户实名制接口
     */
    public function RealName()
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

        //判断是否输入真实姓名
        if (!$data["realName"]) {
            $res = array(
                "Msg" => "请输入您的真实姓名！"
            );
            $this->returnMsg($res, 4, $input["Gid"], "请输入您的真实姓名", 0, $input["Uid"], $input["Version"]);
        }

        //判断是否输入身份证号码
        if (!$data["IDCard"]) {
            $res = array(
                "Msg" => "请输入您的身份证号码！"
            );
            $this->returnMsg($res, 4, $input["Gid"], "请输入您的身份证号码", 0, $input["Uid"], $input["Version"]);
        }

        //判断身份证格式是否正确
        if (!(preg_match("/^\d{18}$/", $data["IDCard"]) || preg_match("/^\d{17}[Xx]$/", $data["IDCard"]) || preg_match("/^\d{15}$/", $data["IDCard"])) || !check_IDCard($data["IDCard"])) {
            $res = array(
                "Msg" => "身份证号码格式错误！"
            );
            $this->returnMsg($res, 4, $input["Gid"], "身份证号码验证错误", 0, $input["Uid"], $input["Version"]);
        }

        //判断是否传递用户唯一标识符
        if (!$data["userCode"]) {
            $res = array(
                "Msg" => "数据异常！请重新提交！"
            );
            $this->returnMsg($res, 4, $input["Gid"], "详细数据获取失败", 0, $input["Uid"], $input["Version"]);
        }

        //获取用户数据
        $user = D("Api/User")->getUser(array("userCode" => $data["userCode"]));

        //判断用户数据是否存在
        if (!$user) {
            $res = array(
                "Msg" => "数据异常！请重新提交！"
            );
            $this->returnMsg($res, 6, $input["Gid"], "无此用户", 0, $input["Uid"], $input["Version"]);
        }

        //判断用户是否需要实名制
        if (!$user["IDCardStatus"]) {
            $res = array(
                "Msg" => "您已完成实名制！请勿重复提交！"
            );
            $this->returnMsg($res, 5, $input["Gid"], "重复实名制", 0, $input["Uid"], $input["Version"]);
        }

        //用户实名制数据
        $info   = array(
            "IDCardStatus"  => 0,
            "IDCard"        => $data["IDCard"],
            "realName"      => $data["realName"]
        );

        //判断是否实名制绑定成功
        if (D("Api/User")->saveUser($info, $data["userCode"])) {
            //IP地址
            $ip     = get_ip_address();
            $area   = ip_to_location($ip);
            //操作日志记录
            $log    = array(
                "time"      => time(),
                "action"    => "RealName",
                "user"      => $data["userCode"],
                "agent"     => $data["agent"],
                "type"      => $data["type"],
                "udid"      => $data["udid"],
                "imei"      => $data["imei"],
                "idfa"      => $data["idfa"],
                "ip"        => $ip,
                "city"      => $area["city"],
                "province"  => $area["province"],
                "net"       => $data["net"],
                "log"       => "用户【".$data["userCode"]."】进行了实名验证，真实姓名：".$data["realName"]."，身份证号：".$data["IDCard"],
            );
            D("Api/Operation")->addLog($log);

            //实名成功
            $res = array(
                "Msg"           => "实名制绑定成功！",
                "IDCardStatus"  => 0
            );
            $this->returnMsg($res, 0, $input["Gid"], "实名制绑定成功", 0, $input["Uid"], $input["Version"]);
        } else {
            //实名失败
            $res = array(
                "Msg"   => "实名制绑定失败！",
            );
            $this->returnMsg($res, 5, $input["Gid"], "实名制绑定失败", 0, $input["Uid"], $input["Version"]);
        }
    }

    /**
     * 绑定用户接口
     */
    public function BindUser()
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

        //判断是否输入账号
        if (!$data["userName"]) {
            $res = array(
                "Msg" => "请输入账号名称！"
            );
            $this->returnMsg($res, 4, $input["Gid"], "请输入账号名称", 0, $input["Uid"], $input["Version"]);
        }

        //判断是否输入密码
        if (!$data["password"]) {
            $res = array(
                "Msg" => "请输入密码！"
            );
            $this->returnMsg($res, 4, $input["Gid"], "请输入密码", 0, $input["Uid"], $input["Version"]);
        }

        //判断输入的用户名格式是否正确
        if (!ctype_alnum($data["userName"]) || preg_match("/^[0-9]$/", substr($data["userName"], 0, 1)) || strlen($data["userName"]) > 20 || strlen($data["userName"]) < 6) {
            $res = array(
                "Msg" => "请输入正确的账号名称！6~20位字母数字组成，需字母开头！"
            );
            $this->returnMsg($res, 4, $input["Gid"], "账号错误！", 0, $input["Uid"], $input["Version"]);
        }

        //判断是否传递用户唯一标识符
        if (!$data["userCode"]) {
            $res = array(
                "Msg" => "数据异常！请重新提交！"
            );
            $this->returnMsg($res, 4, $input["Gid"], "详细数据获取失败", 0, $input["Uid"], $input["Version"]);
        }

        //获取用户数据
        $user = D("Api/User")->getUser(array("userCode" => $data["userCode"]));

        //判断用户数据是否存在
        if (!$user) {
            $res = array(
                "Msg" => "数据异常！请重新提交！"
            );
            $this->returnMsg($res, 6, $input["Gid"], "无此用户", 0, $input["Uid"], $input["Version"]);
        }

        //判断用户是否拥有绑定权限
        if ($user["oneKey"] == "2") {
            $res = array(
                "Msg" => "您已是绑定账号的用户！请勿重复提交！"
            );
            $this->returnMsg($res, 5, $input["Gid"], "重复绑定账号", 0, $input["Uid"], $input["Version"]);
        }

        //判断账号是否已经被占用
        if (D("Api/User")->getUser(array("userName" => $data["userName"]))) {
            $res = array(
                "Msg" => "账号已被注册！请选择其他用户名进行绑定！"
            );
            $this->returnMsg($res, 5, $input["Gid"], "账号已绑定！", 0, $input["Uid"], $input["Version"]);
        };

        //用户绑定账号数据
        $info   = array(
            "oneKey"        => 2,
            "userName"      => $data["userName"],
            "password"      => make_password($data["password"])
        );

        //判断是否绑定账号成功
        if (D("Api/User")->saveUser($info, $data["userCode"])) {
            //绑定成功
            $res = array(
                "Msg"       => "账号绑定成功！",
                "OneKey"    => 1
            );
            $this->returnMsg($res, 0, $input["Gid"], "账号绑定成功", 0, $input["Uid"], $input["Version"]);
        } else {
            //绑定失败
            $res = array(
                "Msg"   => "账号绑定失败！",
            );
            $this->returnMsg($res, 5, $input["Gid"], "账号绑定失败", 0, $input["Uid"], $input["Version"]);
        }
    }

    /**
     * 绑定手机号码
     */
    public function BindMobile()
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

        //判断是否输入手机号码
        if (!$data["mobile"]) {
            $res = array(
                "Msg" => "请输入手机号码！"
            );
            $this->returnMsg($res, 4, $input["Gid"], "请输入手机号码", 0, $input["Uid"], $input["Version"]);
        }

        //判断输入的手机号码格式是否正确
        if (!preg_match("/^1\d{10}$/", $data["mobile"])) {
            $res = array(
                "Msg" => "请输入正确的手机号码!"
            );
            $this->returnMsg($res, 4, $input["Gid"], "手机号码格式错误!", 0, $input["Uid"], $input["Version"]);
        }

        //判断是否输入验证码
        if (!$data["code"]) {
            $res = array(
                "Msg" => "请输入验证码！"
            );
            $this->returnMsg($res, 4, $input["Gid"], "请输入验证码", 0, $input["Uid"], $input["Version"]);
        }

        //判断是否传递用户唯一标识符
        if (!$data["userCode"]) {
            $res = array(
                "Msg" => "数据异常！请重新提交！"
            );
            $this->returnMsg($res, 4, $input["Gid"], "详细数据获取失败", 0, $input["Uid"], $input["Version"]);
        }

        //获取用户数据
        $user = D("Api/User")->getUser(array("userCode" => $data["userCode"]));

        //判断用户数据是否存在
        if (!$user) {
            $res = array(
                "Msg" => "数据异常！请重新提交！"
            );
            $this->returnMsg($res, 6, $input["Gid"], "无此用户", 0, $input["Uid"], $input["Version"]);
        }

        //判断用户是否拥有绑定权限
        if (!$user["mobileStatus"]) {
            $res = array(
                "Msg" => "您已绑定了手机号码！请勿重复绑定！"
            );
            $this->returnMsg($res, 5, $input["Gid"], "重复绑定账号", 0, $input["Uid"], $input["Version"]);
        }

        //判断手机号是否已经被绑定
        if (D("Api/User")->getUser(array("mobileStatus" => 0, "mobile" => $data["mobile"]))) {
            $res = array(
                "Msg" => "手机号码已被绑定！请使用其他手机号进行绑定！"
            );
            $this->returnMsg($res, 5, $input["Gid"], "手机号码已被绑定！", 0, $input["Uid"], $input["Version"]);
        };

        //获取保存的验证码
        $code = D("Api/Sms")->getSmsByMobile($data["mobile"]);

        //验证码不正确或类型不对
        if ($data["code"] != $code["code"] || $code["type"] != 2) {
            $res = array(
                "Msg" => "验证码不正确！请重新输入！"
            );
            $this->returnMsg($res, 5, $input["Gid"], "验证码不正确", 0, $input["Uid"], $input["Version"]);
        }

        //验证码过期
        if (time() - $code["time"] > 300) {
            $res = array(
                "Msg" => "验证码已过期！请重新获取！"
            );
            $this->returnMsg($res, 5, $input["Gid"], "验证码已过期", 0, $input["Uid"], $input["Version"]);
        }

        //验证码已使用
        if ($code["status"] == '0') {
            $res = array(
                "Msg" => "验证码已使用！请重新获取！"
            );
            $this->returnMsg($res, 5, $input["Gid"], "验证码已使用", 0, $input["Uid"], $input["Version"]);
        }

        //用户绑定账号数据
        $info   = array(
            "mobileStatus"  => 0,
            "mobile"        => $data["mobile"],
            "oneKey"        => $user["oneKey"] == 2? 2: 1
        );

        //如果传递了密码，则保存密码
        if ($data["password"]) $info["password"] = make_password($data["password"]);

        //判断是否绑定手机成功
        if (D("Api/User")->saveUser($info, $data["userCode"])) {
            //绑定成功
            //使用短信验证码
            D("Api/Sms")->useSms($data["mobile"]);

            //IP地址
            $ip     = get_ip_address();
            $area   = ip_to_location($ip);
            //操作日志记录
            $log    = array(
                "time"      => time(),
                "action"    => "BindMobile",
                "user"      => $data["userCode"],
                "agent"     => $data["agent"],
                "type"      => $data["type"],
                "udid"      => $data["udid"],
                "imei"      => $data["imei"],
                "idfa"      => $data["idfa"],
                "ip"        => $ip,
                "city"      => $area["city"],
                "province"  => $area["province"],
                "net"       => $data["net"],
                "log"       => "用户【".$data["userCode"]."】绑定了手机，绑定的手机号码：".$data["mobile"]."，短信验证码：".$code["code"],
            );
            D("Api/Operation")->addLog($log);

            $res = array(
                "Msg"       => "手机绑定成功！",
                "Mobile"    => 0
            );
            $this->returnMsg($res, 0, $input["Gid"], "手机绑定成功", 0, $input["Uid"], $input["Version"]);
        } else {
            //绑定失败
            $res = array(
                "Msg"   => "手机绑定失败！",
            );
            $this->returnMsg($res, 5, $input["Gid"], "手机绑定失败", 0, $input["Uid"], $input["Version"]);
        }
    }

    /**
     * 手机号码解绑
     */
    public function UnbindMobile()
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

        //判断是否输入手机号码
        if (!$data["mobile"]) {
            $res = array(
                "Msg" => "请输入手机号码！"
            );
            $this->returnMsg($res, 4, $input["Gid"], "请输入手机号码", 0, $input["Uid"], $input["Version"]);
        }

        //判断输入的手机号码格式是否正确
        if (!preg_match("/^1\d{10}$/", $data["mobile"])) {
            $res = array(
                "Msg" => "请输入正确的手机号码!"
            );
            $this->returnMsg($res, 4, $input["Gid"], "手机号码格式错误!", 0, $input["Uid"], $input["Version"]);
        }

        //判断是否输入验证码
        if (!$data["code"]) {
            $res = array(
                "Msg" => "请输入验证码！"
            );
            $this->returnMsg($res, 4, $input["Gid"], "请输入验证码", 0, $input["Uid"], $input["Version"]);
        }

        //判断是否传递用户唯一标识符
        if (!$data["userCode"]) {
            $res = array(
                "Msg" => "数据异常！请重新提交！"
            );
            $this->returnMsg($res, 4, $input["Gid"], "详细数据获取失败", 0, $input["Uid"], $input["Version"]);
        }

        //获取用户数据
        $user = D("Api/User")->getUser(array("userCode" => $data["userCode"]));

        //判断用户数据是否存在
        if (!$user) {
            $res = array(
                "Msg" => "数据异常！请重新提交！"
            );
            $this->returnMsg($res, 6, $input["Gid"], "无此用户", 0, $input["Uid"], $input["Version"]);
        }

        //判断用户是否已经解绑手机
        if ($user["mobileStatus"]) {
            $res = array(
                "Msg" => "您并未绑定手机号码！"
            );
            $this->returnMsg($res, 5, $input["Gid"], "重复解绑", 0, $input["Uid"], $input["Version"]);
        }

        //判断手机号是否正确
        if ($user["mobile"] != $data["mobile"]) {
            $res = array(
                "Msg" => "您解绑的手机号码不符！请使用正确手机号进行解绑！"
            );
            $this->returnMsg($res, 5, $input["Gid"], "手机号码不符！", 0, $input["Uid"], $input["Version"]);
        };

        //获取保存的验证码
        $code = D("Api/Sms")->getSmsByMobile($data["mobile"]);

        //验证码不正确或类型不对
        if ($data["code"] != $code["code"] || $code["type"] != 3) {
            $res = array(
                "Msg" => "验证码不正确！请重新输入！"
            );
            $this->returnMsg($res, 5, $input["Gid"], "验证码不正确", 0, $input["Uid"], $input["Version"]);
        }

        //验证码过期
        if (time() - $code["time"] > 300) {
            $res = array(
                "Msg" => "验证码已过期！请重新获取！"
            );
            $this->returnMsg($res, 5, $input["Gid"], "验证码已过期", 0, $input["Uid"], $input["Version"]);
        }

        //验证码已使用
        if ($code["status"] == '0') {
            $res = array(
                "Msg" => "验证码已使用！请重新获取！"
            );
            $this->returnMsg($res, 5, $input["Gid"], "验证码已使用", 0, $input["Uid"], $input["Version"]);
        }

        //用户解绑账号数据
        $info   = array(
            "mobileStatus"  => 1
        );

        //判断是否解绑手机成功
        if (D("Api/User")->saveUser($info, $data["userCode"])) {
            //解绑成功
            //使用短信验证码
            D("Api/Sms")->useSms($data["mobile"]);

            //IP地址
            $ip     = get_ip_address();
            $area   = ip_to_location($ip);
            //操作日志记录
            $log    = array(
                "time"      => time(),
                "action"    => "UnbindMobile",
                "user"      => $data["userCode"],
                "agent"     => $data["agent"],
                "type"      => $data["type"],
                "udid"      => $data["udid"],
                "imei"      => $data["imei"],
                "idfa"      => $data["idfa"],
                "ip"        => $ip,
                "city"      => $area["city"],
                "province"  => $area["province"],
                "net"       => $data["net"],
                "log"       => "用户【".$data["userCode"]."】解绑了手机，解绑的手机号码：".$data["mobile"]."，短信验证码：".$code["code"],
            );
            D("Api/Operation")->addLog($log);

            $res = array(
                "Msg"       => "手机解绑成功！",
                "UserName"  => $user["userName"],
                "Mobile"    => 1,
                "OneKey"    => in_array($user["oneKey"], array(1, 3, 4, 5))? 0: 1
            );
            $this->returnMsg($res, 0, $input["Gid"], "手机解绑成功", 0, $input["Uid"], $input["Version"]);
        } else {
            //解绑失败
            $res = array(
                "Msg"   => "手机解绑失败！",
            );
            $this->returnMsg($res, 5, $input["Gid"], "手机解绑失败", 0, $input["Uid"], $input["Version"]);
        }
    }

    /**
     * 密码改密
     */
    public function AccountPassword()
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

        //判断是否输入原始密码
        if (!$data["passwordOld"]) {
            $res = array(
                "Msg" => "请输入原始密码！"
            );
            $this->returnMsg($res, 4, $input["Gid"], "请输入原始密码", 0, $input["Uid"], $input["Version"]);
        }

        //判断是否输入新密码
        if (!$data["passwordNew"]) {
            $res = array(
                "Msg" => "请输入新密码!"
            );
            $this->returnMsg($res, 4, $input["Gid"], "请输入新密码", 0, $input["Uid"], $input["Version"]);
        }

        //判断是否传递用户唯一标识符
        if (!$data["userCode"]) {
            $res = array(
                "Msg" => "数据异常！请重新提交！"
            );
            $this->returnMsg($res, 4, $input["Gid"], "详细数据获取失败", 0, $input["Uid"], $input["Version"]);
        }

        //获取用户数据
        $user = D("Api/User")->getUser(array("userCode" => $data["userCode"]));

        //判断用户数据是否存在
        if (!$user) {
            $res = array(
                "Msg" => "数据异常！请重新提交！"
            );
            $this->returnMsg($res, 6, $input["Gid"], "无此用户", 0, $input["Uid"], $input["Version"]);
        }

        //判断原始密码是否正确
        if (!check_password($data["passwordOld"], $user["password"])) {
            $res = array(
                "Msg" => "原始密码错误！请输入正确的原始密码！"
            );
            $this->returnMsg($res, 5, $input["Gid"], "密码错误", 0, $input["Uid"], $input["Version"]);
        }

        //判断是否修改密码成功
        if (D("Api/User")->saveUser(array("password" => make_password($data["passwordNew"])), $data["userCode"])) {
            log_save("userCode:".$data["userCode"].",udid:".$data["udid"].",ip:".get_client_ip().",time:".date("Y-m-d H:i:s"), "info", "", "editPassword".date('Y-m-d'));

            //IP地址
            $ip     = get_ip_address();
            $area   = ip_to_location($ip);
            //操作日志记录
            $log    = array(
                "time"      => time(),
                "action"    => "AccountPassword",
                "user"      => $data["userCode"],
                "agent"     => $data["agent"],
                "type"      => $data["type"],
                "udid"      => $data["udid"],
                "imei"      => $data["imei"],
                "idfa"      => $data["idfa"],
                "ip"        => $ip,
                "city"      => $area["city"],
                "province"  => $area["province"],
                "net"       => $data["net"],
                "log"       => "用户【".$data["userCode"]."】通过旧密码重新设置了新密码",
            );
            D("Api/Operation")->addLog($log);

            //修改密码成功
            $res = array(
                "Msg"   => "修改密码成功！",
            );
            $this->returnMsg($res, 0, $input["Gid"], "修改密码成功", 0, $input["Uid"], $input["Version"]);
        } else {
            //修改密码失败
            $res = array(
                "Msg"   => "修改密码失败！",
            );
            $this->returnMsg($res, 5, $input["Gid"], "修改密码失败", 0, $input["Uid"], $input["Version"]);
        }
    }

    /**
     * 手机改密
     */
    public function MobilePassword()
    {
        //获取数据
        $input  = $this->getInput("post", "trim");

        //解密出来的数据
        $data   = $this->getDecrypt($input);

        //判断是否输入手机号码
        if (!$data["mobile"]) {
            $res = array(
                "Msg" => "请输入手机号码！"
            );
            $this->returnMsg($res, 4, $input["Gid"], "请输入原始密码", 1, 0, $input["Version"]);
        }

        //判断是否输入手机验证码
        if (!$data["code"]) {
            $res = array(
                "Msg" => "请输入手机验证码！"
            );
            $this->returnMsg($res, 4, $input["Gid"], "请输入手机验证码", 1, 0, $input["Version"]);
        }

        //判断是否输入新密码
        if (!$data["password"]) {
            $res = array(
                "Msg" => "请输入新密码!"
            );
            $this->returnMsg($res, 4, $input["Gid"], "请输入新密码", 1, 0, $input["Version"]);
        }

        //获取用户数据
        $user = D("Api/User")->getUser(array("mobile" => $data["mobile"], "mobileStatus" => 0));

        //判断用户数据是否存在
        if (!$user) {
            $res = array(
                "Msg" => "找不到该用户！请输入正确的手机号码！"
            );
            $this->returnMsg($res, 6, $input["Gid"], "无此用户", 1, 0, $input["Version"]);
        }

        //获取保存的验证码
        $code = D("Api/Sms")->getSmsByMobile($data["mobile"]);

        //验证码不正确或类型不对
        if ($data["code"] != $code["code"] || $code["type"] != 4) {
            $res = array(
                "Msg" => "验证码不正确！请重新输入！"
            );
            $this->returnMsg($res, 5, $input["Gid"], "验证码不正确", 1, 0, $input["Version"]);
        }

        //验证码过期
        if (time() - $code["time"] > 300) {
            $res = array(
                "Msg" => "验证码已过期！请重新获取！"
            );
            $this->returnMsg($res, 5, $input["Gid"], "验证码已过期", 1, 0, $input["Version"]);
        }

        //验证码已使用
        if ($code["status"] == '0') {
            $res = array(
                "Msg" => "验证码已使用！请重新获取！"
            );
            $this->returnMsg($res, 5, $input["Gid"], "验证码已使用", 1, 0, $input["Version"]);
        }

        //判断是否修改密码成功
        if (D("Api/User")->saveUser(array("password" => make_password($data["password"])), $user["userCode"])) {
            //修改密码成功
            //使用短信验证码
            D("Api/Sms")->useSms($data["mobile"]);

            log_save("mobile:".$data["mobile"].",udid:".$data["udid"].",ip:".get_client_ip().",time:".date("Y-m-d H:i:s"), "info", "", "editPassword".date('Y-m-d'));

            //IP地址
            $ip     = get_ip_address();
            $area   = ip_to_location($ip);
            //操作日志记录
            $log    = array(
                "time"      => time(),
                "action"    => "MobilePassword",
                "user"      => $user["userCode"],
                "agent"     => $data["agent"],
                "type"      => $data["type"],
                "udid"      => $data["udid"],
                "imei"      => $data["imei"],
                "idfa"      => $data["idfa"],
                "ip"        => $ip,
                "city"      => $area["city"],
                "province"  => $area["province"],
                "net"       => $data["net"],
                "log"       => "用户【".$user["userCode"]."】通过手机验证码重新设置了新密码，修改密码的手机号码：".$data["mobile"]."，短信验证码：".$code["code"],
            );
            D("Api/Operation")->addLog($log);

            $res = array(
                "Msg"   => "修改密码成功！",
            );
            $this->returnMsg($res, 0, $input["Gid"], "修改密码成功", 1, 0, $input["Version"]);
        } else {
            //修改密码失败
            $res = array(
                "Msg"   => "修改密码失败！",
            );
            $this->returnMsg($res, 5, $input["Gid"], "修改密码失败", 1, 0, $input["Version"]);
        }
    }

    /**
     * 获取用户真实信息
     */
    public function UserReal()
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

        //判断是否输入原始密码
        if (!$data["userCode"]) {
            $res = array(
                "Msg" => "无法获取用户标识符！"
            );
            $this->returnMsg($res, 4, $input["Gid"], "无法获取用户标识符", 0, $input["Uid"], $input["Version"]);
        }

        //获取用户数据
        $user = D("Api/User")->getUser(array("userCode" => $data["userCode"]));

        //判断用户数据是否存在
        if (!$user) {
            $res = array(
                "Msg" => "用户标识符无效！"
            );
            $this->returnMsg($res, 6, $input["Gid"], "用户标识符无效", 0, $input["Uid"], $input["Version"]);
        }

        //判断用户是否已绑定身份证
        if ($user["IDCardStatus"]) {
            $res = array(
                "Msg" => "用户未实名验证！"
            );
            $this->returnMsg($res, 6, $input["Gid"], "用户未实名验证", 0, $input["Uid"], $input["Version"]);
        }

        //拼凑字符串
        $len    = mb_strlen($user["realName"], "UTF-8");
        if ($len < 2) {
            $name   = $user["realName"];
        } elseif ($len == 2) {
            $name   = mb_substr($user["realName"], 0, 1, "UTF-8")."*";
        } else {
            $name   = mb_substr($user["realName"], 0, 1, "UTF-8").str_repeat("*", $len - 2).mb_substr($user["realName"], -1, 1, "UTF-8");
        }
        $IDCard = substr($user["IDCard"], 0, 4).str_repeat("*", strlen($user["IDCard"]) - 8).substr($user["IDCard"], -4);

        //返回数据
        $res = array(
            "Msg"   => "获取个人信息成功！",
            "Name"  => $name,
            "Card"  => $IDCard
        );
        $this->returnMsg($res, 0, $input["Gid"], "获取个人信息成功", 0, $input["Uid"], $input["Version"]);
    }

    /**
     * 获取设备注册的账号
     */
    public function FindAccount()
    {
        set_time_limit(300);

        //获取数据
        $input  = $this->getInput("post", "trim");

        //解密出来的数据
        $data   = $this->getDecrypt($input);

        //判断必要数据是否完整
        if (!$data["type"]) {
            $res = array(
                "Msg" => "无法获取类型！"
            );
            $this->returnMsg($res, 4, $input["Gid"], "无法获取类型", 1, 0, $input["Version"]);
        }

        //账号列表
        $user   = array();

        if ($data["type"] == 1) {
            //安卓
            if (!$data["imei"]) {
                $res = array(
                    "Msg" => "无法获取设备码！"
                );
                $this->returnMsg($res, 4, $input["Gid"], "无法获取设备码", 1, 0, $input["Version"]);
            }

            $user = D("Api/User")->getUserByMap(array("imei" => $data["imei"]));
        } elseif ($data["type"] == 2) {
            //IOS
            if (!$data["idfa"] || $data["idfa"] == "00000000-0000-0000-0000-000000000000") {
                $res = array(
                    "Msg" => "无法获取设备码！"
                );
                $this->returnMsg($res, 4, $input["Gid"], "无法获取设备码", 1, 0, $input["Version"]);
            }

            $user = D("Api/User")->getUserByMap(array("idfa" => $data["idfa"]));
        }

        //判断是否有无账号
        if (!$user) {
            $res = array(
                "Msg" => "该设备并未注册过账号！"
            );
            $this->returnMsg($res, 5, $input["Gid"], "该设备并未注册过账号", 1, 0, $input["Version"]);
        }

        //用户账号
        $info   = array();
        foreach ($user as $arr) {
            $agent  = D("Api/Agent")->getAgent($arr["agent"]);
            if (!$agent["agentType"] && $agent["pid"]) {
                $agent  = D("Api/Agent")->getAgentById($agent["pid"]);
            }
            $game   = str_replace(array("母包", "一部", "二部", "IOS", "安卓", "（作废）", "专服", "混服"), array(), $agent["agentName"]);
            $list   = array(
                "UserCode"  => $arr["userCode"],
                "UserName"  => $arr["userName"],
                "Mobile"    => $arr["mobileStatus"]? "未绑定": $arr["mobile"],
                "LastTime"  => date("Y-m-d H:i:s", $arr["lastLogin"]),
                "LastGame"  => preg_replace("/\[.*\]/", "", $game)
            );
            array_unshift($info, $list);
        }

        $res = array(
            "Msg"       => "获取账号成功！",
            "Account"   => $info
        );
        $this->returnMsg($res, 0, $input["Gid"], "获取账号成功", 1, 0, $input["Version"]);
    }
}