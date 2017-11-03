#!/usr/bin/php
<?php require_once("session.php"); 
require_once("connection.php"); 
require_once("functions.php");
 
$query = "SELECT * FROM boost ORDER BY id asc";
$results = mysql_query($query, $connection);
while ($row = mysql_fetch_assoc($results)) {

	$phpdate = strtotime( $row["time"] );
	$boost_time = $phpdate + ($row["minute"] * 60);
	$now=strtotime(date('Y-m-d H:i:s'));
	if (($boost_time > $now) && ($row["active"]=='1')){$boost='1';}else {$boost='0';}
	$query = "UPDATE boost SET active = '{$boost}' WHERE id = {$row['id']} LIMIT 1";
	mysql_query($query, $connection);
	}
?>
<?php if(isset($connection)) { mysql_close($connection); } ?>