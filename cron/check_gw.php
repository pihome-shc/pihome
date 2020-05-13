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
echo "*   Gateway Script Version 0.3 Build Date 22/01/2018   *\n";
echo "*          Last Modification Date 24/04/2020           *\n";
echo "*                                Have Fun - PiHome.eu  *\n";
echo "********************************************************\n";
echo " \033[0m \n";

require_once(__DIR__.'../../st_inc/connection.php');
require_once(__DIR__.'../../st_inc/functions.php'); 

//Set php script execution time in seconds
ini_set('max_execution_time', 60); 
$date_time = date('Y-m-d H:i:s');
$gw_script_txt = 'python3 /var/www/cron/gateway.py';
$line = "--------------------------------------------------------------------------\n";

echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Python Gateway Script Status Check Script Started \n"; 
//query to get gateway information 
$query = "SELECT * FROM gateway order by id asc LIMIT 1;";
$result = $conn->query($query);
$row = mysqli_fetch_array($result);
$gw_status = $row['status'];
$gw_type = $row['type'];
$gw_location = $row['location'];
$gw_port = $row['port'];
$gw_pid = $row['pid'];
$gw_reboot = $row['reboot'];
$find_gw = $row['find_gw'];

//if status set to 0 then check if gateway PID is running and if found then kill
if ($gw_status == '0') {
	//Gateway Python Script location
	$gw_script_txt = 'python3 /var/www/cron/gateway.py';
	//Check if Porocess is running and get its PID
	exec("ps aux | grep '$gw_script_txt' | grep -v grep | awk '{ print $2 }' | head -1", $out);
	if (count($out) > 1) {
		echo "\033[36m".date('Y-m-d H:i:s')."\033[0m - Gateway PID is: \033[41m".$out[0]."\033[0m \n";
		echo "\033[36m".date('Y-m-d H:i:s')."\033[0m - Stopping Python Gateway Script \n";
		//Kill Gateway Python Scrip PID
		exec("kill -9 '$out[0]'");
	} else {
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Gateway is DISABLED \n";
	}
} else {
	//if reboot set to 1 then kill gateway PID and set reboot status to 0
	if ($gw_reboot == '1' OR $gw_status == '0') {
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Stopping Python Gateway Script \n"; 
		exec("kill -9 $gw_pid");
		$query = "UPDATE gateway SET reboot = '0' LIMIT 1;";
		$conn->query($query);
		echo $line;
	}
	//if find_gw set to 1 then start the search script and set find_gw to 0
	if ($find_gw == '1') {
		if ($gw_type == 'wifi') {
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Checking Python Script Status to Find Smart Home Gateway \n";
			exec("ps ax | grep find_mygw.py", $fgw_pids);
			$gw_script_txt = 'python3 /var/www/cron/find_mygw/find_mygw.py';
			$fgw_position = searchArray($gw_script_txt, $fgw_pids);
			if($fgw_position===false) {
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Search for Smart Home Gateway \033[41mNot Running\033[0m \n";
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Starting Search for Smart Home Gateway \n";
				exec("python3 /var/www/cron/find_mygw/find_mygw.py </dev/null >/dev/null 2>&1 & ");
			exec("ps aux | grep '$gw_script_txt' | grep -v grep | awk '{ print $2 }' | head -1", $out);
				echo "\033[36m".date('Y-m-d H:i:s')."\033[0m - Search Script Started on PID: \033[41m".$out[0]."\033[0m \n";
				echo $line;
			} else {
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Search for Smart Home Gateway \033[42mRunning\033[0m \n";
				exec("ps aux | grep '$gw_script_txt' | grep -v grep | awk '{ print $2 }' | head -1", $out);
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Search Script PID is: \033[42m" . $out[0]."\033[0m \n";
				$pid_details = exec("ps -p '$out[0]' -o lstart=");
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Search Script Running Since: ".$pid_details."\n";
				echo $line;
			}
		}
		$query = "UPDATE gateway SET find_gw = '0' LIMIT 1;";
		$conn->query($query);
	}

	//Check Gateway Logs for last 10 minutes and start search for gateway if connected failed. 
	$queryg = "select count(*) as cnt from gateway_logs where pid_datetime >= DATE_SUB(NOW(), INTERVAL 10 MINUTE);";
	$resultg = $conn->query($queryg);
	$gl_row = mysqli_fetch_array($resultg);
	$gl_cnt = $gl_row['cnt'];
	if($gl_cnt > 0){
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Gateway Connection Lost in Last 10 minutes: ".$gl_cnt." \n";
	}
	if($gl_cnt > 9 && $gw_type == 'wifi') {
		#echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Gateway Connection Lost in Last 10 minutes: ".$gl_cnt." \n";
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Checking Python Script Status to Find Smart Home Gateway \n";
		//Check if Search Script already started 
		exec("ps ax | grep find_mygw.py", $fgw_pids);
		$gw_script_txt = 'python3 /var/www/cron/find_mygw/find_mygw.py';
		$fgw_position = searchArray($gw_script_txt, $fgw_pids);
		if($fgw_position===false) {
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Search for Smart Home Gateway \033[41mNot Running\033[0m \n";
			//If Search script isnt started set to database one
			$query = "UPDATE gateway SET find_gw='1';";
			$conn->query($query);
			//Adding Notice Record 
			//$query = "INSERT INTO notice (sync, `purge`, datetime, message, status) VALUES ('0', '0', '{$date_time}', 'Gateway Connection Lost in Last 10 Minutes ".$gl_cnt."', '1');";
			//$conn->query($query);
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Gateway Search Status set to 1 \n";
			echo $line;
		}
	}

	// Checking if Gateway script is running
	exec("ps ax | grep '$gw_script_txt' | grep -v grep", $pids);
	$nopids = count($pids);
	if($nopids==0) { // Script not running
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Python Gateway Script for Gateway \033[41mNot Running\033[0m \n";
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Starting Python Script for Gateway \n";
		exec("$gw_script_txt </dev/null >/dev/null 2>&1 & ");
		exec("ps aux | grep '$gw_script_txt' | grep -v grep | awk '{ print $2 }' | head -1", $out);
		echo "\033[36m".date('Y-m-d H:i:s')."\033[0m - The PID is: \033[41m".$out[0]."\033[0m \n";
		$pid_details = exec("ps -p '$out[0]' -o lstart=");
		$query = "UPDATE gateway SET pid = '{$out[0]}', pid_running_since = '{$pid_details}' LIMIT 1";
		$conn->query($query);
		echo mysqli_error($conn)."\n";
		$query = "INSERT INTO gateway_logs (`sync`, `purge`, type, location, port, pid, pid_start_time, pid_datetime) VALUES ('0', '0', '{$gw_type}', '{$gw_location}', '{$gw_port}', '{$out[0]}', '{$pid_details}', '{$date_time}' )";
		$conn->query($query);
		echo mysqli_error($conn)."\n";
		echo $line;
	} else {
		if($nopids>1) { // Proceed if more than one gateway script running
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Multiple Gateway Scripts are Detected \033[41m$nopids\033[0m \n";
			$regex = preg_quote($gw_script_txt, '/');
			exec("ps -eo s,pid,cmd | grep 'T.*$regex' | grep -v grep | awk '{ print $2 }'", $tpids);
			$notpids=count($tpids);
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Number of Terminated Script Killed \033[41m$notpids\033[0m \n";
			foreach($tpids as $tpid){
				exec("kill -9 $tpid 2> /dev/null"); // Kill all gateway script ghost processes (in stat "T"(Terminated)). Common occurrence after running script in terminal and terminating by Ctrl+z
			}
			if($nopids-$notpids>1 || $nopids-$notpids==0) { // Proceed if none or more than one script runs
				if($nopids-$notpids>1) { // Proceed if more than one active gateway script 
					exec("ps -eo s,pid,cmd | grep '$gw_script_txt' | grep -v grep | awk '{ print $2 }'", $tpids);
					$notpids=$nopids-$notpids;
					echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Multiple Active Gateway Script are Running \033[41m$notpids\033[0m \n";
					foreach($tpids as $tpid){
						exec("kill -9 $tpid 2> /dev/null"); // Kill all gateway scripts
					}
				}
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - All Script Killed. Started New \n";
				exec("$gw_script_txt </dev/null >/dev/null 2>&1 & ");
				exec("ps aux | grep '$gw_script_txt' | grep -v grep | awk '{ print $2 }' | head -1", $out);
			}
		}
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Python Gateway Script for Gateway is \033[42mRunning\033[0m \n";
		exec("ps -eo s,pid,cmd | grep '$gw_script_txt' | grep -v grep | awk '{ print $2 }' | head -1", $out);
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - The PID is: \033[42m" . $out[0]."\033[0m \n";
		$pid_details = exec("ps -p '$out[0]' -o lstart=");
		$query = "UPDATE gateway SET pid = '{$out[0]}', pid_running_since = '{$pid_details}' LIMIT 1";
		$conn->query($query);
		echo $line;
	}
}
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Python Gateway Script Status Check Script Ended \n"; 
echo "\033[32m***************************************************************************\033[0m";
echo "\n";
if(isset($conn)) { $conn->close();}
?>
