<?php 
#!/usr/bin/php
echo "\033[36m";
echo "\n";
echo "   _____    _   _    _                             \n";
echo "  |  __ \  (_) | |  | |                            \n";
echo "  | |__) |  _  | |__| |   ___    _ __ ___     ___  \n";
echo "  |  ___/  | | |  __  |  / _ \  | |_  \_ \   / _ \ \n";
echo "  | |      | | | |  | | | (_) | | | | | | | |  __/ \n";
echo "  |_|      |_| |_|  |_|  \___/  |_| |_| |_|  \___| \n";
echo " \033[0m \n";
echo "     \033[45m S M A R T   H E A T I N G   C O N T R O L \033[0m \n";
echo "\033[31m";
echo "********************************************************\n";
echo "*   Notice Script Version 0.01 Build Date 14/09/20189  *\n";
echo "*   Update on 15/06/2019                               *\n";
echo "*                                Have Fun - PiHome.eu *\n";
echo "*******************************************************\n";
echo " \033[0m \n";

require_once(__DIR__.'../../st_inc/connection.php');
require_once(__DIR__.'../../st_inc/functions.php');
require_once(__DIR__.'../../st_inc/class.phpmailer.php');
$line = "------------------------------------------------------------------\n";

//Set php script execution time in seconds
ini_set('max_execution_time', 40); 
$date_time = date('Y-m-d H:i:s');

echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Notice Script Started \n"; 
echo $line;

echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Checking Gateway Communication \n"; 
//Check Gateway Logs for last 10 minuts and start search for gateway connected failed. 
$query = "select count(*) as cnt from  gateway_logs where pid_datetime >= DATE_SUB(NOW(),INTERVAL 10 MINUTE);";
$result = $conn->query($query);
$gl_row = mysqli_fetch_array($result);
$gl_cnt = $gl_row['cnt'];
if ($gl_cnt > 10){
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Gateway Connection Lost in Last 10 minutes: ".$gl_cnt." \n"; 
	$query = "INSERT INTO notice (sync, `purge`, datetime, message, status) VALUES ('0', '0', '{$date_time}', 'Gateway Connection Lost in Last 10 Minutes ".$gl_cnt."', '1');";
	$conn->query($query);
}
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Gateway Notice Finished \n"; 
echo $line;

//*************************************************************************************************************
//Temperature Sensors Last Seen status 
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Checking Temperature Sensors Communication \n"; 
$query = "select * FROM nodes WHERE name = 'Temperature Sensor' AND last_seen <= DATE_SUB(NOW(),INTERVAL 10 MINUTE);";
$resultb = $conn->query($query);
if (mysqli_num_rows($resultb) != 0){
	while ($row = mysqli_fetch_assoc($resultb)) {
		$node_id=$row['node_id'];
		$name=$row['name'];
		$last_seen=$row['last_seen'];
		
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Temperature Sensor Reported long ago. \n"; 
		$query = "INSERT INTO notice (sync, `purge`, datetime, message, status) VALUES ('0', '0', '{$date_time}', 'Temperature Sensors ID ".$node_id." last reported on ".$last_seen."', '1');";
		$result = $conn->query($query);
	}
}
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Temperature Sensor Check Finished \n"; 
echo $line;

//*************************************************************************************************************
//Boiler Controller Last Seen. 
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Checking Boiler Controller Communication \n"; 
$query = "select * FROM nodes WHERE name = 'Boiler Relay' AND last_seen <= NOW() - INTERVAL 5 MINUTE;";
$resultc = $conn->query($query);
if (mysqli_num_rows($resultc) != 0){
	while ($row = mysqli_fetch_assoc($resultc)) {
		$node_id=$row['node_id'];
		$name=$row['name'];
		$last_seen=$row['last_seen'];
	
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Boiler Relay Connection in Last 5 minutes: \n"; 
		$query = "INSERT INTO notice (sync, `purge`, datetime, message, status) VALUES ('0', '0', '{$date_time}', 'Boiler Controller Relay ".$node_id." last reported on ".$last_seen."', '1');";
		$result = $conn->query($query);
	}
}
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Boiler Controller Relay Check Finished \n"; 
echo $line;

//*************************************************************************************************************
//Zone Controller Last Seen.
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Checking Zone Controller Communication \n"; 
$query = "select * FROM nodes WHERE name = 'Zone Controller Relay' AND node_id != '0' AND last_seen <= NOW() - INTERVAL 5 MINUTE;";
$resultd = $conn->query($query);
if (mysqli_num_rows($resultd) != 0){
	while ($row = mysqli_fetch_assoc($resultd)) {
		$node_id=$row['node_id'];
		$name=$row['name'];
		$last_seen=$row['last_seen'];
	
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone Relay Connection in Last 5 minutes: \n"; 
		$query = "INSERT INTO notice (sync, `purge`, datetime, message, status) VALUES ('0', '0', '{$date_time}', 'Zone Controller Relay ".$node_id." last reported on ".$last_seen."', '1');";
		$result = $conn->query($query);
	}
}
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone Controller Relay Check Finished \n"; 
echo $line;
//*************************************************************************************************************
//Check CPU Temperature from last one hour if it was over 50c
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Checking CPU Temperature \n"; 
$query = "select * FROM messages_in_view_24h WHERE node_id = '0' AND payload > 50 AND DATETIME >= DATE_SUB(NOW(),INTERVAL 60 MINUTE);";
$resulta = $conn->query($query);
if (mysqli_num_rows($resulta) != 0){
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - CPU Temperature is very high: \n"; 
	$query = "INSERT INTO notice (sync, `purge`, datetime, message, status) VALUES ('0', '0', '{$date_time}', 'Over 50c CPU Temperature Recorded in last one Hour', '1');";
	$conn->query($query);
}
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - CPU Temperature Check Finished \n"; 
echo $line;


?>
