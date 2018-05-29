<?php

require_once dirname(dirname(__FILE__)).'/service/SDKServerService.php';
require_once dirname(dirname(__FILE__)).'/model/SDKException.php';

//玩家的sid
$sid = "sst1game38774acc88074d0e9a1798bc1fc97e64165638";
try{
    $realNameInfo = SDKServerService::getRealNameStatus($sid);
    echo $realNameInfo->realNameStatus;
}
catch (SDKException $e){
    echo $e->getCode()." ".$e->getMessage();
}