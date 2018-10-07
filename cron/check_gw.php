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
echo "*   Gateway Script Version 0.2 Build Date 22/01/2018   *\n";
echo "*                                Have Fun - PiHome.eu  *\n";
echo "********************************************************\n";
echo " \033[0m \n";

require_once(__DIR__.'../../st_inc/connection.php');
require_once(__DIR__.'../../st_inc/functions.php'); 

//Set php script execution time in seconds
ini_set('max_execution_time', 40); 

//query to get gateway information 
$query = "SELECT * FROM gateway where status = 1 order by id asc limit 1;";
$result = $conn->query($query);
$row = mysqli_fetch_array($result);
$gw_type = $row['type'];
$gw_location = $row['location'];
$gw_port = $row['port'];
$gw_pid = $row['pid'];
$gw_reboot = $row['reboot'];

//if reboot set to 1 then kill gateway PID and set reboot status to 0
if ($gw_reboot == '1') {
	exec("kill -9 $gw_pid");
	$query = "UPDATE gateway SET reboot = '0' LIMIT 1;";
	$conn->query($query);
	echo mysqli_error($conn)."\n";
}

echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Python Gateway Script Status Check Script Started \n"; 
if ($gw_type == 'wifi'){
	exec("ps ax | grep wifigw.py", $pids); 
	//exec(" pgrep aux | grep serialgwv2.py", $pids); 
	$gw_script_txt = 'python /var/www/cron/wifigw.py';
	$position = searchArray($gw_script_txt, $pids);
	if($position===false) {
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Python Script for WiFi Gateway \033[41mNot Running\033[0m \n";
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Starting Python Script for WiFi Gateway \n";
		//exec("sh /var/www/cron/wifigw.sh");
		exec("python /var/www/cron/wifigw.py </dev/null >/dev/null 2>&1 & ");
		exec("ps aux | grep '$gw_script_txt' | grep -v grep | awk '{ print $2 }' | head -1", $out);
		echo "\033[36m".date('Y-m-d H:i:s')."\033[0m - The PID is: \033[41m".$out[0]."\033[0m \n";
		$pid_details = exec("ps -p '$out[0]' -o lstart=");
		$query = "UPDATE gateway SET pid = '{$out[0]}', pid_running_since = '{$pid_details}' LIMIT 1";
		$conn->query($query);
		echo mysqli_error($conn)."\n";
		$query = "INSERT INTO gateway_logs (type, location, port, pid, pid_start_time) VALUES ( '{$gw_type}', '{$gw_location}', '{$gw_port}', '{$out[0]}', '{$pid_details}' )";
		$conn->query($query);
		echo mysqli_error($conn)."\n";
	} else {
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Python Script for WiFi Gateway is \033[42mRunning\033[0m \n";
		exec("ps aux | grep '$gw_script_txt' | grep -v grep | awk '{ print $2 }' | head -1", $out);
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - The PID is: \033[42m" . $out[0]."\033[0m \n";
		$pid_details = exec("ps -p '$out[0]' -o lstart=");
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Gateway Process Running Since: ".$pid_details."\n";
		$query = "UPDATE gateway SET pid = '{$out[0]}', pid_running_since = '{$pid_details}' LIMIT 1";
		$conn->query($query);
		echo mysqli_error($conn)."\n";
	}
} elseif ($gw_type == 'serial'){
	exec("ps ax | grep serialgw.py", $pids); 
	//exec(" pgrep aux | grep serialgwv2.py", $pids); 
	$gw_script_txt = 'python /var/www/cron/serialgw.py';
	$position = searchArray($gw_script_txt, $pids);
	if($position===false) {
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Python Gateway Script for Serial Gateway \033[41mNot Running\033[0m \n";
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Starting Python Script for Serial Gateway \n";
		//exec("sh /var/www/cron/serialgw.sh");
		exec("python /var/www/cron/serialgw.py </dev/null >/dev/null 2>&1 & ");
		exec("ps aux | grep '$gw_script_txt' | grep -v grep | awk '{ print $2 }' | head -1", $out);
		echo "\033[36m".date('Y-m-d H:i:s')."\033[0m - The PID is: \033[41m".$out[0]."\033[0m \n";
		$pid_details = exec("ps -p '$out[0]' -o lstart=");
		$query = "UPDATE gateway SET pid = '{$out[0]}', pid_running_since = '{$pid_details}' LIMIT 1";
		$conn->query($query);
		echo mysqli_error($conn)."\n";
		$query = "INSERT INTO gateway_logs (type, location, port, pid, pid_start_time) VALUES ( '{$gw_type}', '{$gw_location}', '{$gw_port}', '{$out[0]}', '{$pid_details}' )";
		$conn->query($query);
		echo mysqli_error($conn)."\n";
	} else {
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Python Gateway Script for Serial Gateway is \033[42mRunning\033[0m \n";
		exec("ps aux | grep '$gw_script_txt' | grep -v grep | awk '{ print $2 }' | head -1", $out);
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - The PID is: \033[42m" . $out[0]."\033[0m \n";
		echo $pids[$position]."\n" ;
		$pid_details = exec("ps -p '$out[0]' -o lstart=");
		$query = "UPDATE gateway SET pid = '{$out[0]}', pid_running_since = '{$pid_details}' LIMIT 1";
		$conn->query($query);
	}
}
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Python Gateway Script Status Check Script Ended \n"; 
echo "\033[32m***************************************************************************\033[0m";
echo "\n";
if(isset($conn)) { $conn->close();}
?>