#!/usr/bin/php
<?php require_once("connection.php"); ?>
<?php require_once("functions.php"); ?>
<?php 
$current_time = date('H:i:s');
$start_time = '23:57:00';
$end_time = '00:00:00';
if (TimeIsBetweenTwoTimes($current_time, $start_time, $end_time)) {
	echo date('Y-m-d H:i:s'). " - Time to call Home \n";
	$external_ip = file_get_contents('http://ddns.pihome.eu/myip.php');
	$pi_serial = exec ("cat /proc/cpuinfo | grep Serial | cut -d ' ' -f 2");
	$cpu_model = exec ("cat /proc/cpuinfo | grep 'model name' | cut -d ' ' -f 3-");
	$cpu_model = urlencode($cpu_model);
	$hardware = exec ("cat /proc/cpuinfo | grep Hardware | cut -d ' ' -f 2");
	$revision = exec ("cat /proc/cpuinfo | grep Revision | cut -d ' ' -f 2");
	$uid = UniqueMachineID($pi_serial);
	echo date('Y-m-d H:i:s'). " - External IP Address: ".$external_ip."\n";
	echo date('Y-m-d H:i:s'). " - Raspberry Pi Serial: " .$pi_serial."\n";
	echo date('Y-m-d H:i:s'). " - Raspberry Pi Hardware: " .$hardware."\n";
	echo date('Y-m-d H:i:s'). " - Raspberry Pi CPU Model: " .$cpu_model."\n";
	echo date('Y-m-d H:i:s'). " - Raspberry Pi Revision: " .$revision."\n";
	echo date('Y-m-d H:i:s'). " - Raspberry Pi UID: " .$uid."\n";
	$url="http://ddns.pihome.eu/home.php?ip=${external_ip}&serial=${uid}&cpu_model=${cpu_model}&hardware=${hardware}&revision=${revision}";
	echo $url."\n";
	$result = url_get_contents($url);
	echo date('Y-m-d H:i:s'). " - PiHome Says: ".$result;	
}
if(isset($connection)) { mysql_close($connection); }
?>



