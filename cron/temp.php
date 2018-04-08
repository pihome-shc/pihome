<?php 
require_once(__DIR__.'../../st_inc/functions.php');
/********************************************************************************************************************************************************************
Following section is Optional for States collection  
I thank you for not commenting it out as it will help me to allocate time to keep this systems updated. 
I am using CPU serial as salt and then using MD5 hasing to get unique reference, i have no other intention if you want you can set variable to anything you like
/********************************************************************************************************************************************************************/
$current_time = date('H:i:s');
$start_time = '23:58:00';
$end_time = '00:00:00';
if (TimeIsBetweenTwoTimes($current_time, $start_time, $end_time)) {
	echo "---------------------------------------------------------------------------------------- \n";
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Calling Home \n";
	$external_ip = file_get_contents('http://ddns.pihome.eu/myip.php');
	$pi_serial = exec ("cat /proc/cpuinfo | grep Serial | cut -d ' ' -f 2");
	$cpu_model = exec ("cat /proc/cpuinfo | grep 'model name' | cut -d ' ' -f 3-");
	$cpu_model = urlencode($cpu_model);
	$hardware = exec ("cat /proc/cpuinfo | grep Hardware | cut -d ' ' -f 2");
	$revision = exec ("cat /proc/cpuinfo | grep Revision | cut -d ' ' -f 2");
	$uid = UniqueMachineID($pi_serial);
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - External IP Address: ".$external_ip."\n";
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Raspberry Pi Serial: " .$pi_serial."\n";
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Raspberry Pi Hardware: " .$hardware."\n";
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Raspberry Pi CPU Model: " .$cpu_model."\n";
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Raspberry Pi Revision: " .$revision."\n";
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Raspberry Pi UID: " .$uid."\n";
	$url="http://ddns.pihome.eu/home.php?ip=${external_ip}&serial=${uid}&cpu_model=${cpu_model}&hardware=${hardware}&revision=${revision}";
	//echo $url."\n";
	$result = url_get_contents($url);
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - PiHome Says: ".$result."\n";
	echo "---------------------------------------------------------------------------------------- \n";
}

echo "---------------------------------------------------------------------------------------- \n";
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Boiler Script Ended \n"; 
echo "\033[32m****************************************************************************************\033[0m  \n";

?>