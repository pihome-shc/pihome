<?php 
require("_config.inc.php");
$connection = mysql_connect($hostname, $dbusername, $dbpassword);
if(!$connection) {
	die($connect_error." ". mysql_error());
}
$db_select = mysql_select_db($dbname, $connection);
if(!$db_select) {
	die($connect_error." ". mysql_error());
}?>