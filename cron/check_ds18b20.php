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
echo "*   DS18b20 Script Version 0.1 Build Date 24/04/2021   *\n";
echo "*          Last Modification Date 24/04/2021           *\n";
echo "*                                Have Fun - PiHome.eu  *\n";
echo "********************************************************\n";
echo " \033[0m \n";

require_once(__DIR__.'../../st_inc/connection.php');
require_once(__DIR__.'../../st_inc/functions.php'); 

//Set php script execution time in seconds
ini_set('max_execution_time', 60); 
$date_time = date('Y-m-d H:i:s');
$ds18b20_script_txt = 'python3 /var/www/cron/gpio_ds18b20.py';
$line = "--------------------------------------------------------------------------\n";

echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Python DS18b20 Script Status Check Script Started \n"; 

// Checking if DS18b20 script is running
exec("ps ax | grep '$ds18b20_script_txt' | grep -v grep", $pids);
$nopids = count($pids);
if($nopids==0) { // Script not running
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Python DS18b20 Script \033[41mNot Running\033[0m \n";
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Starting Python Script for DS18b20 \n";
	exec("$ds18b20_script_txt </dev/null >/dev/null 2>&1 & ");
	exec("ps aux | grep '$ds18b20_script_txt' | grep -v grep | awk '{ print $2 }' | head -1", $out);
	echo "\033[36m".date('Y-m-d H:i:s')."\033[0m - The PID is: \033[41m".$out[0]."\033[0m \n";
} else {
	if($nopids>1) { // Proceed if more than one DS18b20  script running
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Multiple DS18b20 Scripts are Detected \033[41m$nopids\033[0m \n";
		$regex = preg_quote($ds18b20_script_txt, '/');
		exec("ps -eo s,pid,cmd | grep 'T.*$regex' | grep -v grep | awk '{ print $2 }'", $tpids);
		$notpids=count($tpids);
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Number of Terminated Script Killed \033[41m$notpids\033[0m \n";
		foreach($tpids as $tpid){
			exec("kill -9 $tpid 2> /dev/null"); // Kill all DS18b20 script ghost processes (in stat "T"(Terminated)). Common occurrence after running script in terminal and terminating by Ctrl+z
		}
		if($nopids-$notpids>1 || $nopids-$notpids==0) { // Proceed if none or more than one script runs
			if($nopids-$notpids>1) { // Proceed if more than one active DS18b20  script 
				exec("ps -eo s,pid,cmd | grep '$ds18b20_script_txt' | grep -v grep | awk '{ print $2 }'", $tpids);
				$notpids=$nopids-$notpids;
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Multiple Active DS18b20 Script are Running \033[41m$notpids\033[0m \n";
				foreach($tpids as $tpid){
					exec("kill -9 $tpid 2> /dev/null"); // Kill all DS18b20 scripts
				}
			}
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - All Script Killed. Started New \n";
			exec("$ds18b20_script_txt </dev/null >/dev/null 2>&1 & ");
			exec("ps aux | grep '$ds18b20_script_txt' | grep -v grep | awk '{ print $2 }' | head -1", $out);
		}
	}
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Python DS18b20 Script is \033[42mRunning\033[0m \n";
	exec("ps -eo s,pid,cmd | grep '$ds18b20_script_txt' | grep -v grep | awk '{ print $2 }' | head -1", $out);
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - The PID is: \033[42m" . $out[0]."\033[0m \n";
}
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Python DS18b20 Script Status Check Script Ended \n"; 
echo "\033[32m***************************************************************************\033[0m";
echo "\n";
?>
