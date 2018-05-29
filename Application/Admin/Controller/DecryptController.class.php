<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 2018/2/26
 * Time: 10:03
 *
 * 数据解密
 */

namespace Admin\Controller;

class DecryptController extends BackendController
{

    /**
     * API数据解密
     */
    public function apiDecrypt()
    {
        if (IS_POST) {
            $data   = $_POST;
            //解密
            $des    = new \Vendor\DES3\P_DES3(($data["token"]? $data["token"]: "Cy@mwonv2219jdwjcnsmou29&").$data["key"]);
            //解密
            $secret = $des->decrypt($data["info"]);
            if (!$secret) {
                $secret = $des->decrypt(urldecode($data["info"]));
            }
            $res = json_encode($secret? array("code" => 1, "data" => $secret): array("code" => 0, "data" => "解密失败"));
            echo $res;
        } else {
            $this->display();
        }
    }

    /**
     * MD5加密
     */
    public function md5Decrypt()
    {
        if (IS_POST) {
            $data   = $_POST;
            if (!$data["info"]) {
                echo json_encode(array("code" => 0, "data" => "字符串为空"));
            } else {
                //加密
                $arr = array(
                    "l32" => strtolower(md5($data["info"])),
                    "u32" => strtoupper(md5($data["info"])),
                    "l16" => strtolower(substr(md5($data["info"]), 8, 16)),
                    "u16" => strtoupper(substr(md5($data["info"]), 8, 16)),
                );
                echo json_encode(array("code" => 1, "data" => $arr));
            }
        } else {
            $this->display();
        }
    }
}