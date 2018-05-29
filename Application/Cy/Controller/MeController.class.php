<?php
/**
 * Created by Zend.
 * User: XSM
 * Date: 2017/6/8
 * Time: 14:28
 *
 * sql预览控制器
 */
namespace Cy\Controller;

use Cy\Controller\BackendController;

class MeController extends BackendController{
	var $dbconf = array();
    public function index(){
        error_reporting(0);
        date_default_timezone_set ( 'PRC' );
        ini_set('memory_limit','1024M'); 
        set_time_limit(0);

        // mkdir('../Runtime/Logs/');
        
        $date = I('get.date');
        $num  = I('get.num');
        if(!$num){
            $num = 10;
        }
        $filename = I('get.filename');
        $pathfilename = I('get.pfilename');
        $down = I('get.down');
        if($filename){
            $date = $filename;
        }elseif(!$date){
            $date = date('y_m_d');
        }
        
        if($pathfilename){
            $logfile = RUNTIME_PATH.$pathfilename.'.log';
        }else{
            $logfile = RUNTIME_PATH.'Logs'.DIRECTORY_SEPARATOR.'Cy'.DIRECTORY_SEPARATOR.$date.'.log';
        }
        
        
        if($down){
            Header("Content-type: application/octet-stream");
            Header("Accept-Ranges: bytes");
            Header("Accept-Length: ".filesize($logfile));
            Header("Content-Disposition: attachment; filename=" . ($pathfilename ? $pathfilename:$date).'.log');
            $handle = fopen($logfile,"r");
            echo fread($handle, filesize($logfile));
            fclose($handle);
            exit;
        }else{
            $handle = fopen($logfile, 'r');
            $contents = array();
            $i = 1;
            
            while(!feof($handle)){
                $ts = fgets($handle, 8192);
                if(str_replace(PHP_EOL, '8', $ts) == '8'){
                    $i++;
                }
                if(!isset($contents[$i]))$contents[$i] = '';
                $contents[$i] .= str_replace(PHP_EOL, '<br/>', $ts);
                unset($ts);
            }
            krsort($contents);
            foreach($contents as $k => $v){
                if($num && count($contents)-$k > $num){
                    continue;
                }
                echo $v.'<br/>';
            }
        }
    }
}