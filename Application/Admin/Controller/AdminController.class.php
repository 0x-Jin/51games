<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/6/6
 * Time: 10:08
 *
 * 账号控制器
 */

namespace Admin\Controller;

use Admin\Controller\Backend;
use Vendor\ApiMongoDB\ApiMongoDB;

class AdminController extends BackendController
{

    public function mongodb()
    { 
    	// ini_set('mongo.long_as_object', 1);
        $db = new \Vendor\ApiMongoDB\ApiMongoDB(array('host' => '119.29.98.50', 'port' => 59818, 'username' => 'ZgMongoAdvter', 'password' => 'lkjet#$lj10!~!3sji^', 'db' => 'advter', 'cmd' => '$'));
        $insert = array(
        		'advter_id'     =>  '123',
                'game_id'       =>  '102',
                'agent'         =>  'fhxdcAND',
                'muid'          =>  'fdjaskfhdskafdka213213',
                'adUserId'      =>  '2',
                'os'            =>  1,
                'advterType'    =>  'gdt',
                'clickTime'     =>  time(),
                'createTime'    =>  time(),
                'ip'            =>  '127.0.0.1',
                'callBackUrl'   =>  'http://t.gdt.qq.com/conv/app/',
                'appid'         =>  '123',
                'click_id'      =>  '456',
                'app_type'      =>  'app_type',
                'advertiser_id' =>  '23131',
                'status'        =>  1,
                
            );
        $db->close();
        // $res = $db->insert("advios", $insert);
        // var_dump($res);

    }
}