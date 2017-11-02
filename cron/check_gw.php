<?php 
#!/usr/bin/php
echo "\n";
require_once("connection.php"); 
require_once("functions.php");
//Set php script execution time in seconds
ini_set('max_execution_time', 40); 
$gw_type = gw('type');
$gw_location = gw('location');
$gw_port = gw('port');

echo date('Y-m-d H:i:s'). " - Python Gateway Script Status Check Script Started \n"; 
if (gw_logs('type') == 'wifi'){
	exec("ps ax | grep wifigw.py", $pids); 
	//exec(" pgrep aux | grep serialgwv2.py", $pids); 
	$gw_script_txt = 'python /var/www/cron/wifigw.py';
	$position = searchArray($gw_script_txt, $pids);
	if($position===false) {
		echo date('Y-m-d H:i:s'). " - Python Script for WiFi Gateway Not Running \n";
		echo date('Y-m-d H:i:s'). " - Starting Python Script for WiFi Gateway \n";
		//exec("sh /var/www/cron/wifigw.sh");
		exec("python /var/www/cron/wifigw.py </dev/null >/dev/null 2>&1 & ");
		exec("ps aux | grep '$gw_script_txt' | grep -v grep | awk '{ print $2 }' | head -1", $out);
		echo date('Y-m-d H:i:s'). " - The PID is: " . $out[0]."\n";
		$pid_details = exec("ps -p '$out[0]' -o lstart=");
		$query = "UPDATE gateway SET pid = '{$out[0]}', pid_running_since = '{$pid_details}' LIMIT 1";
		mysql_query($query, $connection);
		$query = "INSERT INTO gateway_logs (type, location, port, pid, pid_start_time) VALUES ( '{$gw_type}', '{$gw_location}', '{$gw_port}', '{$out[0]}', '{$pid_details}' )";
		mysql_query($query, $connection);
		
	} else {
		echo date('Y-m-d H:i:s'). " - Python Script for WiFi Gateway is Running \n";
		exec("ps aux | grep '$gw_script_txt' | grep -v grep | awk '{ print $2 }' | head -1", $out);
		echo date('Y-m-d H:i:s'). " - The PID is: " . $out[0]."\n";
		$pid_details = exec("ps -p '$out[0]' -o lstart=");
		echo date('Y-m-d H:i:s'). " - Gateway Process Running Since: ".$pid_details."\n";
		$query = "UPDATE gateway SET pid = '{$out[0]}', pid_running_since = '{$pid_details}' LIMIT 1";
		mysql_query($query, $connection);
	}
} elseif (gw_logs('type') == 'serial'){
	exec("ps ax | grep serialgw.py", $pids); 
	//exec(" pgrep aux | grep serialgwv2.py", $pids); 
	$gw_script_txt = 'python /var/www/cron/serialgw.py';
	$position = searchArray($gw_script_txt, $pids);
	if($position===false) {
		echo date('Y-m-d H:i:s'). " - Python Gateway Script for Serial Gateway Not Running \n";
		echo date('Y-m-d H:i:s'). " - Starting Python Script for Serial Gateway \n";
		//exec("sh /var/www/cron/serialgw.sh");
		exec("python /var/www/cron/serialgw.py </dev/null >/dev/null 2>&1 & ");
	} else {
		echo date('Y-m-d H:i:s'). " - Python Gateway Script for Serial Gateway is Running \n";
		exec("ps aux | grep '$gw_script_txt' | grep -v grep | awk '{ print $2 }' | head -1", $out);
		echo "The PID is: " . $out[0]."\n";
		echo $pids[$position]."\n" ;
		$query = "UPDATE gateway SET pid = '{$out[0]}', pid_running_since = '{$pid_details}' LIMIT 1";
		mysql_query($query, $connection);
	}
}
echo date('Y-m-d H:i:s'). " - Python Gateway Script Status Check Script Ended \n"; 
echo "***************************************************************************";
echo "\n";
if(isset($connection)) { mysql_close($connection); }

?>