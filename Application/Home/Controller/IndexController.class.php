<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends Controller {
    public function index(){
        if (in_array($_SERVER['HTTP_HOST'], array("fall.chuangyunet.net","nxs.aiyoumeng.cn"))) {
            $this->display();
        } else {
            header("HTTP/1.0  404  Not Found");
            exit('EOF');
        }
    }
}