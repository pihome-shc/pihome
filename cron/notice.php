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
echo "*******************************************************\n";
echo "*   Notice Script Version 0.01 Build Date 14/09/20189  *\n";
echo "*   Update on 14/09/2019                               *\n";
echo "*                                Have Fun - PiHome.eu *\n";
echo "*******************************************************\n";
echo " \033[0m \n";

require_once(__DIR__.'../../st_inc/connection.php');
require_once(__DIR__.'../../st_inc/functions.php');
$line = "------------------------------------------------------------------\n";

//Set php script execution time in seconds
ini_set('max_execution_time', 40); 
$date_time = date('Y-m-d H:i:s');

echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Notice Script Started \n"; 
echo $line;

echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Checking Gateway Communication \n"; 
$query = "select * FROM gateway_logs WHERE pid_datetime >= NOW() - INTERVAL 5 MINUTE;";
$resulta = $conn->query($query);
if (mysqli_num_rows($resulta) > 4){
	$gw_restarted = mysqli_num_rows($resulta);
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Gateway Connection Lost in Last 5 minutes: ".$gw_restarted." \n"; 
	$query = "INSERT INTO notice (sync, `purge`, datetime, message, status) VALUES ('0', '0', '{$date_time}', 'Gateway Connection Lost in Last 5 Minutes ".$gw_restarted."', '1');";
	$result = $conn->query($query);
}
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Gateway Check Finished \n"; 
echo $line;

echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Checking Temperature Sensors Communication \n"; 
$query = "select * FROM nodes WHERE name = 'Temperature Sensor' AND last_seen <= NOW() - INTERVAL 5 MINUTE;";
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

echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Checking CPU Temperature \n"; 
$query = "select * FROM messages_in_view_24h WHERE node_id = '0' order by `datetime` desc Limit 1;";
$resulta = $conn->query($query);
if (mysqli_num_rows($resulta) != 0){
	while ($row = mysqli_fetch_assoc($resultd)) {
		$datetime=$row['datetime'];
		$payload=$row['payload'];
		
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - CPU Temperature is very high: ".$payload." \n"; 
		$query = "INSERT INTO notice (sync, `purge`, datetime, message, status) VALUES ('0', '0', '{$date_time}', 'CPU Temperature is very high ".$payload."', '1');";
		$result = $conn->query($query);
	}
}
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - CPU Temperature Check Finished \n"; 
echo $line;


?>
