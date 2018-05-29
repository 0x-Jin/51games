<?php 
class Connect
{
	public function __construct()
	{
		error_reporting(0);
        date_default_timezone_set ( 'PRC' );
	}
	
	protected function dbConnect($host, $user, $password, $db)
	{
        $conn = new mysqli($host, $user, $password, $db);
        if(mysqli_connect_errno()){
            die("Error:(".mysqli_connect_errno().")".mysqli_connect_error());
        }
        $conn->query("set names utf8;");
        return $conn;
    }
}

?>