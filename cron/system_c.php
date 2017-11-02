#!/usr/bin/php
<?php require_once("connection.php"); ?>
<?php require_once("functions.php"); ?>
<?php
$time=date('H:i');
$date=date('y-m-d');
$system_c = exec ("vcgencmd measure_temp | cut -c6,7,8,9");
echo $system_c."\n";
if ($system_c == 0) {
	//do nothing
}else {
$query = "INSERT INTO messages_in (node_id, child_id, payload) VALUES ('0', '0', '{$system_c}')";
mysql_query($query, $connection);
}
if(isset($connection)) { mysql_close($connection); } 
?>