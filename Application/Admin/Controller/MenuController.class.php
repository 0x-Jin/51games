<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/6/6
 * Time: 10:57
 *
 * 菜单控制器
 */

namespace Admin\Controller;

use Admin\Controller\Backend;

class MenuController extends BackendController
{

    /**
     * 菜单主页
     */
    public function index()
    {
        $menu = array();
        $arr_f = D("Admin/Menu")->getMenu(array("pid" => 0));
        foreach ($arr_f as $v_f) {
            $menu[] = array(
                "id"        => $v_f["id"],
                "icon"      => "&nbsp;&nbsp;".$v_f["name"],
                "status"    => $v_f["status"],
                "order"     => $v_f["order"]
            );
            $arr_s = D("Admin/Menu")->getMenu(array("pid" => $v_f["id"]));
            foreach ($arr_s as $v_s) {
                $menu[] = array(
                    "id"        => $v_s["id"],
                    "icon"      => "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$v_s["name"],
                    "status"    => $v_s["status"],
                    "order"     => $v_s["order"]
                );
                $arr_t = D("Admin/Menu")->getMenu(array("pid" => $v_s["id"]));
                foreach ($arr_t as $v_t) {
                    $menu[] = array(
                        "id"        => $v_t["id"],
                        "icon"      => "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$v_t["name"],
                        "status"    => $v_t["status"],
                        "order"     => $v_t["order"]
                    );
                }
            }
        }

        $this->assign("menu", $menu);
        $this->display();
    }

    /**
     * 获取菜单信息
     */
    public function getMenu()
    {
        if (IS_POST) {
            $id = I("post.id", "", "trim");
            $menu = D("Admin/Menu")->getMenu(array("id" => $id));
            echo json_encode($menu[0]);
        } else {
            echo "";
        }
    }

    /**
     * 修改菜单信息
     */
    public function saveMenu()
    {
        $code = false;
        if (IS_POST) {
            $id     = I("post.id", "", "trim");
            $name   = I("post.name", "", "trim");
            $status = I("post.status", "", "trim");
            $order  = I("post.order", "0", "trim");

            if (!$id || !$name || $status === null) {
                $msg = "获取数据失败！";
            } else {
                if (D("Admin/Menu")->saveMenu(array("name" => $name, "status" => $status, "order" => $order), $id)) {
                    $code   = true;
                    $msg    = "修改成功！";
                } else {
                    $msg    = "修改失败！";
                };
            }
        } else {
            $msg = "数据异常！";
        }
        echo json_encode(array("Code" => $code, "Msg" => $msg));
    }
}