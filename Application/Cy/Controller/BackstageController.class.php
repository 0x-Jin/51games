<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/10/10
 * Time: 10:06
 *
 * 投放后台管理类
 */

namespace Cy\Controller;

class BackstageController extends BackendController
{

    /**
     * 平台账号
     */
    public function backstageList()
    {
        if (IS_POST) {
            $start      = I("start")? I("start"): 0;
            $pageSize   = I("limit")? I("limit"): 30;

            $res        = D("Admin")->getBuiList("backstage", array(), $start, $pageSize, "la_", "id");
            $results    = $res["count"];
            foreach ($res["list"] as $key => $val){
                $res["list"][$key]["create"]    = date("Y-m-d H:i:s", $val["createTime"]);
                $res["list"][$key]["opt"]       = '<a href="javascript:;" onclick="backstageEdit(\''.$val["id"].'\', this)">编辑</a>';
                $res["list"][$key]["opt"]       .= '&nbsp;<a href="javascript:;" onclick="accountInfo(\''.$val["id"].'\', this)">账号</a>';
                $rows[]                         = $res["list"][$key];
            }
            $arr = array("rows" => $rows, "results" => $results);
            exit(json_encode($arr));
        } else {
            $this->display();
        }
    }

    /**
     * 添加平台
     */
    public function backstageAdd()
    {
        if (IS_POST) {
            $data   = I();
            $data["createTime"] = $data["updateTime"] = time();
            $res    = D("Admin")->commonAdd("backstage", $data);
            if ($res) {
                $this->success("操作成功");
            } else {
                $this->error("操作失败");
            }
        } else {
            $this->ajaxReturn(array("status" => 1, "html" => $this->fetch()));
        }
    }

    /**
     * 编辑平台
     */
    public function backstageEdit()
    {
        if (IS_POST) {
            $data   = I();
            if (!$data["id"]) $this->error("操作失败");
            $data["updateTime"] = time();
            if (D("Admin")->commonExecute("backstage", array("id" => $data["id"]), $data)) {
                $this->success("操作成功");
            } else {
                $this->error("操作失败");
            }
        } else {
            $id     = I("id");
            if (!$id) $this->ajaxReturn(array("status" => 0, "html" => ""));
            $info   = D("Admin")->commonQuery("backstage", array("id" => $id));
            $this->assign("info", $info);
            $this->ajaxReturn(array("status" =>1, "html" => $this->fetch()));
        }
    }

    /**
     * 账号管理
     */
    public function accountInfo()
    {
        if (IS_POST) {
            $data   = I();
            if (!$data["id"]) $this->error("操作失败");
            $data["updateTime"] = time();
            $res    = D("Admin")->commonExecute("backstage", array("id" => $data["id"]), $data);
            if ($res) {
                $this->success("操作成功");
            } else {
                $this->error("操作失败");
            }
        } else {
            $id         = I("id");
            if (!$id) $this->ajaxReturn(array("status" => 0, "html" => ""));
            $backstage  = D("Admin")->commonQuery("backstage", array("id" => $id));
            $account    = D("Admin")->commonQuery("backstage_account", array("backstage_id" => $id), 0, 99999);
            $this->assign("account", $account);
            $this->assign("backstage", $backstage);
            $this->ajaxReturn(array("status" =>1, "html" => $this->fetch()));
        }
    }

    /**
     * 添加账号
     */
    public function accountAdd()
    {
        $data   = I();
        //判断数据是否齐全
        if(!$data["backstage_id"] || !$data["name"] || !$data["account"]) {
            $res = array(
                "Result"    => false,
                "Msg"       => "账号数据未填写完整！"
            );
            exit(json_encode($res));
        }
        $data["createTime"] = $data["updateTime"] = time();
        $id     = D("Admin")->commonAdd("backstage_account", $data);
        if ($id) {
            $res = array(
                "Result"    => true,
                "Msg"       => "添加账号成功！",
                "Id"        => $id
            );
            exit(json_encode($res));
        } else {
            $res = array(
                "Result"    => false,
                "Msg"       => "添加账号失败！"
            );
            exit(json_encode($res));
        }
    }

    /**
     * 修改账号
     */
    public function accountEdit()
    {
        $data   = I();
        //判断数据是否齐全
        if(!$data["id"] || !$data["name"] || !$data["account"]) {
            $res = array(
                "Result"    => false,
                "Msg"       => "账号数据未填写完整！"
            );
            exit(json_encode($res));
        }
        $data["updateTime"] = time();
        if (D("Admin")->commonExecute("backstage_account", array("id" => $data["id"]), $data)) {
            $res = array(
                "Result"    => true,
                "Msg"       => "修改账号成功！"
            );
            exit(json_encode($res));
        } else {
            $res = array(
                "Result"    => false,
                "Msg"       => "修改账号失败！"
            );
            exit(json_encode($res));
        }
    }

    /**
     * 删除账号
     */
    public function accountDelete()
    {
        //判断操作权限
        if (session("admin.role_id") != 1) {
            $res = array(
                "Result"    => false,
                "Msg"       => "您权限不够！无法删除！",
            );
            exit(json_encode($res));
        };

        $id = I("id");
        //判断数据是否齐全
        if(!$id) {
            $res = array(
                "Result"    => false,
                "Msg"       => "获取不到ID！",
            );
            exit(json_encode($res));
        }

        if (D("Admin")->commonDelete("backstage_account", array("id" => $id))) {
            $res = array(
                "Result"    => true,
                "Msg"       => "删除账号成功！",
            );
            exit(json_encode($res));
        } else {
            $res = array(
                "Result"    => false,
                "Msg"       => "删除账号失败！",
            );
            exit(json_encode($res));
        }
    }

    /**
     * 登陆平台
     */
    public function backstageLogin()
    {
        $admin      = D("Admin")->commonQuery("admin", array("id" => session("admin.uid")));
        $account    = D("BackstageAccount")->getAccount(array("a.id" => array("IN", $admin["backstage_account_id"]? explode(",", $admin["backstage_account_id"]): "0"), "a.status" => 1));
        $this->assign("account", $account);
        $this->display();

//        $admin      = D("Admin")->commonQuery("admin", array("id" => session("admin.uid")));
//        $account    = D("BackstageAccount")->getAccount(array("a.id" => array("IN", $admin["backstage_account_id"]? explode(",", $admin["backstage_account_id"]): "0"), "a.status" => 1));
//        $this->assign("account", $account);
//        $exe = D("Admin")->getBackstageExe();
//        $this->assign("exe", $exe);
//        $this->display();
    }

    /**
     * 获取登录验证码
     */
    public function loginToken()
    {
        //获取ID
        $id         = I("id");
        if (!$id) exit(json_encode(array("Result" => 0, "Msg" => "ID缺失！")));

        //获取账号信息
        $account    = D("Admin")->commonQuery("backstage_account", array("id" => $id));
        if (!$account) exit(json_encode(array("Result" => 0, "Msg" => "账号缺失！")));

        if ($account["backstage_id"] == 1) {
            //今日头条
//            $str    = file_get_contents("http://139.199.197.21:8000/analoglogin/toutiao/");
//            $info   = json_decode($str, true);
//            if (!$info || $info["login_status"] != 0) exit(json_encode(array("Result" => 0, "Msg" => "请求失败！")));
//            $this->assign("account", $account);
//            $this->assign("code", $info["image"]);
//            $this->assign("toutiao", json_encode($info["cookie"]));
//            exit(json_encode(array("Result" => 1, "Html" => $this->fetch("Backstage/loginToutiao"))));
        } elseif ($account["backstage_id"] == 7) {
            //UC
            $str    = file_get_contents("http://139.199.197.21:8000/analoglogin/uc/");
            $info   = json_decode($str, true);
            if (!$info || $info["status"] != 0) exit(json_encode(array("Result" => 0, "Msg" => "请求失败！")));
            $this->assign("account", $account);
            $this->assign("code", $info["image"]);
            $this->assign("uc", json_encode($info["cookie"]));
            exit(json_encode(array("Result" => 1, "Html" => $this->fetch("Backstage/loginUc"))));
        }
        exit(json_encode(array("Result" => 0, "Msg" => "该平台暂未开放！")));
    }

    /**
     * 今日头条登陆
     */
    public function toutiaoLogin()
    {
        $data   = I();
        if (!$data["code"] || !$data["account"] || !$data["password"] || !$data["toutiao"]) $this->error("登陆失败");
        $info   = array(
            "vcode"     => $data["code"],
            "user"      => $data["account"],
            "password"  => $data["password"],
            "cookie"    => json_decode(urldecode($data["toutiao"]), true)
        );
        $res    = curl_post("http://139.199.197.21:8000/analoglogin/toutiao/", http_build_query(array("data" => json_encode($info))));
        $list   = json_decode($res, true);
        if ($list["login_status"] == "1") {
//            $file = file_get_contents("https://ad.toutiao.com/overture/data/advertiser/ad");
//            $str = '<script>$(function(){$("#loginbox:input[name=\'account\']").val("'.$data["account"].'");$(":input[name=\'password\']").val("'.$data["password"].'");}</script>';
//            echo $file.$str;

            echo '<iframe onload="doThis(this)" src="https://ad.toutiao.com/overture/data/advertiser/ad" name="toutiao_i" id="toutiao_i" width="100%" height="100%" frameborder="0"></iframe><script src="https://ad.toutiao.com/static/libs/jquery/1.10.0/jquery.min.js"></script><script>function doThis(obj){alert("abcc");var inp = $("#toutiao_i").find(\':input[name="account"]\').val("1112222");}</script>';

//            header("Content-type:text/html;Charset=utf8");
//            $ch =curl_init();
//            curl_setopt($ch, CURLOPT_URL,'https://ad.toutiao.com/overture/data/advertiser/ad/');
//            curl_setopt($ch, CURLOPT_HEADER, 0);
//            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
//            curl_setopt($ch, CURLOPT_COOKIE, $list["cookie"]); //使用上面获取的cookies
//            $response = curl_exec($ch);
//            curl_close($ch);
//            echo $response;



//            setcookie("Cookie", $list["cookie"], time() + 86400, "/overture/data/advertiser/ad/", "https://ad.toutiao.com");
//            header("Referer: https://ad.toutiao.com/login/");
//            header("Location: https://ad.toutiao.com/overture/data/advertiser/ad/");
//            $arr = explode(";", $list["cookie"]);
//            foreach ($arr as $value) {
//                $Arr = explode("=", $value);
////                setcookie($Arr[0], $Arr[1]);
//                setcookie($Arr[0], $Arr[1]);
//            }
//            setcookie("adddd", "bccc123c", time() + 86400, "./", ".toutiao.com/");

//            header("Access-Control-Allow-Origin: *");
//            header("Access-Control-Allow-Credentials: true");
//            header("Cookie: ".$list["cookie"]);
//            header("Location: http://lgame.com/Admin/AdvterData/monthTarget.html?table=month_target");
//            header("Location: https://ad.toutiao.com/overture/data/advertiser/ad/");
//            echo '<script>$(window.frames[0].document).find(":input[name=\'account\']").val("'.$data["account"].'");</script>';
//            echo '<script>$(function(){$(":input[name=\'account\']").val("'.$data["account"].'");$(":input[name=\'password\']").val("'.$data["password"].'");}</script>';
//            echo "<script>alert('".$list["cookie"]."');location.href='https://ad.toutiao.com/overture/data/advertiser/ad/';</script>";
//            $arr = explode(";", $list["cookie"]);
//            $str = implode("&", $arr);
//            echo '<script>window.location = "https://ad.toutiao.com/overture/data/advertiser/ad?'.urlencode($str).'"</script>';
        } else {
            $this->error("登陆失败");
        }
    }

    /**
     * UC登陆
     */
    public function ucLogin()
    {
        $data   = I();
        if (!$data["code"] || !$data["account"] || !$data["password"] || !$data["uc"]) $this->error("登陆失败");
        $info   = array(
            "vcode"     => $data["code"],
            "user"      => $data["account"],
            "password"  => $data["password"],
            "cookie"    => json_decode(urldecode($data["uc"]), true)
        );
        $res    = curl_post("http://139.199.197.21:8000/analoglogin/uc/", http_build_query(array("data" => json_encode($info))));
        $list   = json_decode($res, true);
        if ($list["status"] == "1") {
            echo '<script>window.location = "'.$list["url"].'"</script>';
            exit();
        } else {
            $this->error("登陆失败");
        }
    }

    /**
     * 平台软件
     */
    public function exeList()
    {
        if (IS_POST) {
            $data       = I();
            $start      = $data["start"]? $data["start"]: 0;
            $pageSize   = $data["limit"]? $data["limit"]: 30;
            $res        = D("Admin")->getBuiList("backstage_exe", array(), $start, $pageSize);
            foreach ($res["list"] as $key => $val) {
                $res["list"][$key]["address"]   = ($val["address32"]? '<a href="http://'.$_SERVER['SERVER_NAME']."/".$val["address32"].'">32位</a>': "").'&nbsp;'.($val["address64"]? '<a href="http://'.$_SERVER['SERVER_NAME']."/".$val["address64"].'">64位</a>': "");
                $res["list"][$key]["create"]    = date("Y-m-d H:i:s", $val["createTime"]);
                $rows[]                         = $res["list"][$key];
            }
            $arr = array("rows" => $rows, "results" => $res["count"]);
            exit(json_encode($arr));
        } else {
            $this->display();
        }
    }

    /**
     * 添加版本
     */
    public function exeAdd()
    {
        if (IS_POST) {
            $data       = I();
            if (!$data["name"] || !$data["ver"]) {
                $this->error("必要数据缺失");
            }
            //文件上传
            $file_32    = $_FILES["address32"];
            $file_64    = $_FILES["address64"];
            $type       = array("rar", "zip");
            //设置上传路径
            $savePath   = "Uploads/exe/";
            @mkdir($savePath);
            $date       = date("YmdHis");
            //32位
            if (!empty($file_32["name"])) {
                $tmp_file   = $file_32["tmp_name"];
                $file_types = explode(".", $file_32["name"]);
                $file_type  = $file_types[count($file_types) - 1];
                //判别是不是压缩文件
                if(!in_array($file_type, $type)){
                    $text = implode(",", $type);
                    $this->error("您只能上传以下类型文件: ".$text."<br>");
                }
                //32位保存
                $address_32 = $savePath.mb_substr($file_32["name"], 0, mb_strlen($file_32["name"], "UTF-8") - mb_strlen($file_type, "UTF-8") - 1)."_32_".$date.".".$file_type;
                //是否上传成功
                if(!move_uploaded_file($tmp_file, $address_32)){
                    $this->error("上传失败");
                }
                $data["address32"] = $address_32;
            }
            //64位
            if (!empty($file_64["name"])) {
                $tmp_file   = $file_64["tmp_name"];
                $file_types = explode(".", $file_64["name"]);
                $file_type  = $file_types[count($file_types) - 1];
                //判别是不是压缩文件
                if(!in_array($file_type, $type)){
                    $text = implode(",", $type);
                    $this->error("您只能上传以下类型文件: ".$text."<br>");
                }
                //64位保存
                $address_64 = $savePath.mb_substr($file_64["name"], 0, mb_strlen($file_64["name"], "UTF-8") - mb_strlen($file_type, "UTF-8") - 1)."_64_".$date.".".$file_type;
                //是否上传成功
                if(!move_uploaded_file($tmp_file, $address_64)){
                    $this->error("上传失败");
                }
                $data["address64"] = $address_64;
            }
            $data["createTime"] = $data["updateTime"] = time();
            $res = D("Admin")->commonAdd("backstage_exe", $data);
            if($res){
                $this->success("操作成功");
            }else{
                $this->error("操作失败");
            }
        } else {
            $this->ajaxReturn(array("status" => 1, "_html" => $this->fetch()));
        }
    }

    /**
     * 软件下载
     */
    public function down()
    {
        $exe = D("Admin")->getBackstageExe();
        $this->assign("exe", $exe);
        $this->display();
    }
}