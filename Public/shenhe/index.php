<?php
    $data = $_REQUEST;
    $file_path = "/data/Cyshell/svnUpdate.sh";
    if(file_exists($file_path)){
        exec("$file_path",$out);
        echo $data['callback'].'('.json_encode($out).')';
        exit;
    }
?>