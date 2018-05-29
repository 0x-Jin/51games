<?php

$sign = $_GET['sign'];
if ( $sign != '4cf2736db325cab18f50f8cfe5583108') {
	header('HTTP/1.1 404 Not Found');
    die();
}
phpinfo();
