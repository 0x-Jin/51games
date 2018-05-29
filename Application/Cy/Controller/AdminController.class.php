<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/6/6
 * Time: 10:08
 *
 * 账号控制器
 */

namespace Cy\Controller;

use Cy\Controller\Backend;
use Vendor\ApiMongoDB\ApiMongoDB;

class AdminController extends BackendController
{

    public function mongodb()
    {
        $db = new ApiMongoDB();
        $a = $db->select("test");

        $res = $db->insert("test", array("testName" => "12333"));
        var_dump($a,$res);
    }
}