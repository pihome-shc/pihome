<?php 
#!/usr/bin/php
//http://www.pihome.eu/piconnect/mypihome.php?api=bc8c390f7f2d10cad2a8fbfb4bd66fd6&ip=93.107.150.39&data=new&table=schedule_daily_time&id=0
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
echo "*************************************************************\n";
echo "*   PiConnect Script Version 0.1 Build Date 16/04/2018      *\n";
echo "*   Update on 16/07/218                                     *\n";
echo "*                                      Have Fun - PiHome.eu *\n";
echo "*************************************************************\n";
echo " \033[0m \n";

require_once(__DIR__.'../../st_inc/connection.php');
require_once(__DIR__.'../../st_inc/functions.php');
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - PiHome PiConnect Script Started \n";

//Get PiConnect API from Database. 
$query = "SELECT * FROM piconnect;";
$result = $conn->query($query);
$picrow = mysqli_fetch_array($result);
$protocol = $picrow['protocol'];
$url = $picrow['url'];
$script = $picrow['script'];
$pihome_api = $picrow['api_key'];
$api_url=$protocol."://".$url.$script;
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - PiConnect API URL: \033[1;31m".$api_url."\033[0m \n";

//$api_url = "http://www.pihome.eu/piconnect/";
$my_ip = file_get_contents('http://www.pihome.eu/piconnect/myip.php');

//Set php script execution time in seconds
ini_set('max_execution_time', 40); 
$date_time = date('Y-m-d H:i:s');
$line = "------------------------------------------------------------------\n";

//get api key from database 
//$pihome_api = settings($conn, 'pihome_api');

$url=$api_url."?check_api=${pihome_api}&ip=${my_ip}";
//echo $url."\n";
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - PiHome API key: \033[1;33m".$pihome_api."\033[0m \n";
$api_result = url_get_contents($url);

//check if API is valid then execute code to sync data from local tables 
if ($api_result == "OK"){
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - You have valid API for PiHome \n";
	
	

	
	
	
/*****************************************************************************************************************************************************/	
	//Start Syncing Gateway Logs Table with PiConnect. 
	$query = "SELECT * FROM gateway_logs where sync = 0 order by id asc;";
	$results = $conn->query($query);
	if (mysqli_num_rows($results) != 0){
		echo $line;
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Gateway Log Data to Sync with PiConnect: \033[32m". mysqli_num_rows($results)."\033[0m\n"; 
		while ($row = mysqli_fetch_assoc($results)) {
			$data='push';
			$id=$row['id'];
			$sync=$row['sync'];
			$purge=$row['purge'];
			$type=$row['type'];
			$location=rawurlencode($row['location']);
			$port=rawurlencode($row['port']);
			$pid=$row['pid'];
			$pid_start_time=rawurlencode($row['pid_start_time']);
			$pid_datetime=rawurlencode($row['pid_datetime']);

			//echo row data to console 
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Data to sync with PiConnect: \n";
			echo "\033[1;33m Data Comm:\033[0m            \033[1;32m".$data."\033[0m \n";
			echo "\033[1;33m Table ID:\033[0m             \033[1;32m".$id."\033[0m \n";
			echo "\033[1;33m Purge:\033[0m                \033[1;32m".$purge."\033[0m \n";
			echo "\033[1;33m Type:\033[0m                 \033[1;32m".$type."\033[0m \n";
			echo "\033[1;33m Location:\033[0m             \033[1;32m".$row['location']."\033[0m \n";
			echo "\033[1;33m Port:\033[0m                 \033[1;32m".$row['port']."\033[0m \n";
			echo "\033[1;33m PID:\033[0m                  \033[1;32m".$pid."\033[0m \n";
			echo "\033[1;33m PID Running Since:\033[0m    \033[1;32m".$row['pid_datetime']."\033[0m \n";

			//call out to PiConnect with data 
			$url=$api_url."?api=${pihome_api}&ip=${my_ip}&data=${data}&table=gateway&id=${id}&purge=${purge}&type=${type}&location=${location}&port=${port}pid=${pid}&pid_datetime=${pid_datetime}";
			$result = url_get_contents($url);
			//echo $url."\n";
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Result from PiConnect: \033[1;32m".$result."\033[0m \n";
			if ($result == 'Success'){
				$query = "UPDATE gateway SET sync = '1' WHERE id ='{$id}' LIMIT 1;";
				$conn->query($query);
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Gateway Log Status Updated in Local Database.\n";
			}elseif($result == 'Failed'){
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Result from PiConnect: \033[31m".$result."\033[0m \n";
			}
			echo $line;
		}
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Gateway Log Data to Push to PiConnect \n";
	} 
	//Gateway Logs Sync end here 
	
/*****************************************************************************************************************************************************/		



//api key validation else option
}else{
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Your API Key isnt valid please contact PiHome at http://www.pihome.eu \n";
}

echo "\n"; 
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - PiConnect Script Ended \n"; 
echo "\033[32m**************************************************************\033[0m  \n";

if(isset($conn)) { $conn->close();}


?>