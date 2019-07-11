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
echo "*************************************************************\n";
echo "*   PiConnect Script Version 0.32 Build Date 21/01/2019     *\n";
echo "*   Update on 02/02/2019                                    *\n";
echo "*                                      Have Fun - PiHome.eu *\n";
echo "*************************************************************\n";
echo " \033[0m \n";

require_once(__DIR__.'../../st_inc/connection.php');
require_once(__DIR__.'../../st_inc/functions.php');

$date_time = date('Y-m-d H:i:s');
$line = "------------------------------------------------------------------\n";
//Set php script execution time in seconds
ini_set('max_execution_time', 40);

echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - PiHome PiConnect Script Started \n";

//Get PiConnect API from Database. 
$query = "SELECT * FROM piconnect;";
$result = $conn->query($query);
$picrow = mysqli_fetch_array($result);
$status = $picrow['status'];
$protocol = $picrow['protocol'];
$url = $picrow['url'];
$script = $picrow['script'];
$pihome_api = $picrow['api_key'];
$api_url=$protocol."://".$url.$script;
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - PiConnect API URL: \033[1;31m".$api_url."\033[0m \n";
//if PiConnect Status is Enabled then proceed otherwise nothing to do here. 
if ($status == "1"){
	//Check if Piconnect is Online
	$host = 'pihome.eu';
	if($socket =@ fsockopen($host, 80, $errno, $errstr, 30)) {
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - PiConnect is \033[42mOnline\033[0m \n";
		fclose($socket);
		//get public ip address
		$my_ip = file_get_contents('http://www.pihome.eu/piconnect/myip.php');
		$url=$api_url."?check_api=${pihome_api}&ip=${my_ip}";
		//echo $url."\n";
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - PiConnect API key: \033[1;33m".$pihome_api."\033[0m \n";
		$api_result = url_get_contents($url);
	
		//check if API is valid then execute code to sync data from local database tables
		if ($api_result == "OK"){
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - You have valid API for PiConnect \n";
			
			
			/*****************************************************************************************************************************************************/	
			//Start Syncing Notice Data with PiConnect. 
			$query = "SELECT * FROM notice where sync = 0 order by id asc;";
			$results = $conn->query($query);
			if (mysqli_num_rows($results) != 0){
				echo $line;
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Notice Data to Sync with PiConnect: \033[32m". mysqli_num_rows($results)."\033[0m\n"; 
				while ($row = mysqli_fetch_assoc($results)) {
					$data='push';
					$id=$row['id'];
					$sync=$row['sync'];
					$purge=$row['purge'];
					$datetime=rawurlencode($row['datetime']);
					$message=rawurlencode($row['message']);
					$status=$row['status'];
					//echo row data to console 
					echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Data to sync with PiConnect: \n";
					echo "\033[1;33m Data Comm:\033[0m            \033[1;32m".$data."\033[0m \n";
					echo "\033[1;33m Table ID:\033[0m             \033[1;32m".$id."\033[0m \n";
					echo "\033[1;33m Purge:\033[0m                \033[1;32m".$purge."\033[0m \n";
					echo "\033[1;33m Date & Time:\033[0m          \033[1;32m".$row['datetime']."\033[0m \n";
					echo "\033[1;33m Message:\033[0m              \033[1;32m".$row['message']."\033[0m \n";
					echo "\033[1;33m Status:\033[0m               \033[1;32m".$status."\033[0m \n";
					
					//call out to PiConnect with data 
					$url=$api_url."?api=${pihome_api}&ip=${my_ip}&data=${data}&table=notice&id=${id}&purge=${purge}&datetime=${datetime}&message=${message}&status=${status}";
					$result = url_get_contents($url);
					//echo $url."\n";
					echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Result from PiConnect: \033[1;32m".$result."\033[0m \n";
					if ($result == 'Success'){
						$query = "UPDATE notice SET sync = '1' WHERE id ='{$id}' LIMIT 1;";
						$conn->query($query);
						echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Notice Status Updated in Local Database.\n";
					}elseif($result == 'Failed'){
						echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Result from PiConnect: \033[31m".$result."\033[0m \n";
					}
					echo $line;
				}
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Notice Data to Push to PiConnect \n";
			} else {
				echo $line;
				//Start Pulling Frost Protection Data with PiConnect.
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Checking Notice Data to Pull from PiConnect \n";	
				$data='pull';
				$url=$api_url."?api=${pihome_api}&ip=${my_ip}&data=${data}&table=notice&id=0";
				//echo $url."\n";
				$resulta = url_get_contents($url);
				if ($resulta != 'no-data'){
					// Convert JSON string to Array
					$jasonarray = json_decode($resulta, true);
					foreach ($jasonarray as $key => $value) {
						echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Notice Data from PiConnect: \n";
						$id=$value["id"];
						$purge=$value['purge'];
						$datetime=$value["datetime"];
						$message=$value['message'];
						$status=$value['status'];
						echo "\033[1;33m Data Comm:\033[0m            \033[1;32m".$data."\033[0m \n";
						echo "\033[1;33m Table ID:\033[0m             \033[1;32m".$id."\033[0m \n";
						echo "\033[1;33m Purge:\033[0m                \033[1;32m".$purge."\033[0m \n";
						echo "\033[1;33m Date Time:\033[0m            \033[1;32m".$datetime."\033[0m \n";
						echo "\033[1;33m Message:\033[0m              \033[1;32m".$message."\033[0m \n";
						echo "\033[1;33m Status:\033[0m               \033[1;32m".$status."\033[0m \n";
						if ($purge == '1'){
							$query = "DELETE FROM notice WHERE id = '{$id}';";
							$conn->query($query);
							echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Notice Purged in Local Database \n";
						}elseif($purge == '0'){
							$query = "UPDATE notice SET sync = '1', `purge` = '{$purge}', status = '{$status}' where id = '{$id}';";
							$conn->query($query);
							echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Notice Data Updated in Local Database \n";
							echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Notice Data Pull from PiConnect Finished. \n";
						echo $line;
						}
					}
				}else{
					echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Notice Data From PiConnect: \033[1;32m".$resulta."\033[0m \n";
				}
			}
			//Notice Data Sync end here 
			/*****************************************************************************************************************************************************/	
			//Start Syncing Frost Protection Table with PiConnect. 
			$query = "SELECT * FROM frost_protection where sync = 0 order by id asc limit 1;";
			$results = $conn->query($query);
			if (mysqli_num_rows($results) != 0){
				echo $line;
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Frost Protection Data to Sync with PiConnect: \033[32m". mysqli_num_rows($results)."\033[0m\n"; 
				while ($row = mysqli_fetch_assoc($results)) {
					$data='push';
					$id=$row['id'];
					$purge=$row['purge'];
					$sync=$row['sync'];
					$datetime=rawurlencode($row['datetime']);
					$temperature=$row['temperature'];
					//echo row data to console 
					echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Data to sync with PiConnect: \n";
					echo "\033[1;33m Data Comm:\033[0m            \033[1;32m".$data."\033[0m \n";
					echo "\033[1;33m Table ID:\033[0m             \033[1;32m".$id."\033[0m \n";
					echo "\033[1;33m Purge:\033[0m                \033[1;32m".$purge."\033[0m \n";
					echo "\033[1;33m Date & Time:\033[0m          \033[1;32m".$row['datetime']."\033[0m \n";
					echo "\033[1;33m Temperature:\033[0m          \033[1;32m".$temperature."\033[0m \n";
					//call out to PiConnect with data 
					$url=$api_url."?api=${pihome_api}&ip=${my_ip}&data=${data}&table=frost_protection&id=${id}&purge=${purge}&datetime=${datetime}&temperature=${temperature}";
					$result = url_get_contents($url);
					//echo $url."\n";
					echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Result from PiConnect: \033[1;32m".$result."\033[0m \n";
					if ($result == 'Success'){
						$query = "UPDATE frost_protection SET sync = '1' WHERE id ='{$id}' LIMIT 1;";
						$conn->query($query);
						echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Frost Protection Status Updated in Local Database.\n";
					}elseif($result == 'Failed'){
						echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Result from PiConnect: \033[31m".$result."\033[0m \n";
					}
					echo $line;
				}
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Frost Protection Data to Push to PiConnect \n";
			} else {
				echo $line;
				//Start Pulling Frost Protection Data with PiConnect.
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Checking Frost Protection Data to Pull from PiConnect \n";	
				$data='pull';
				$url=$api_url."?api=${pihome_api}&ip=${my_ip}&data=${data}&table=frost_protection&id=0";
				//echo $url."\n";
				$resulta = url_get_contents($url);
				if ($resulta != 'no-data'){
					// Convert JSON string to Array
					$jasonarray = json_decode($resulta, true);
					foreach ($jasonarray as $key => $value) {
						echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Frost Protection Data from PiConnect: \n";
						$id=$value["id"];
						$purge=$value['purge'];
						$datetime=$value["datetime"];
						$temperature=$value['temperature'];
						echo "\033[1;33m Data Comm:\033[0m            \033[1;32m".$data."\033[0m \n";
						echo "\033[1;33m Table ID:\033[0m             \033[1;32m".$id."\033[0m \n";
						echo "\033[1;33m Purge:\033[0m                \033[1;32m".$purge."\033[0m \n";
						echo "\033[1;33m Date Time:\033[0m            \033[1;32m".$datetime."\033[0m \n";
						echo "\033[1;33m Temperature:\033[0m          \033[1;32m".$temperature."\033[0m \n";
						$query = "UPDATE frost_protection SET sync = '1', datetime = '{$datetime}', temperature = '{$temperature}' where id = '{$id}' ;";
						$conn->query($query);
						echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Frost Protection Data Pull from PiConnect Finished. \n";
						echo $line;
					}
				}else{
					echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Frost Protection Data From PiConnect: \033[1;32m".$resulta."\033[0m \n";
				}
			}
			//Frost Protection Sync end here 
			/*****************************************************************************************************************************************************/
			//Start Syncing Frost Protection Table with PiConnect. 
			$query = "SELECT * FROM system where sync = 0 order by id asc limit 1;";
			$results = $conn->query($query);
			if (mysqli_num_rows($results) != 0){
				echo $line;
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - System Settings Data to Sync with PiConnect: \033[32m". mysqli_num_rows($results)."\033[0m\n"; 
				while ($row = mysqli_fetch_assoc($results)) {
					$data='push';
					$id=$row['id'];
					$sync=$row['sync'];
					$purge=$row['purge'];
					$name=rawurlencode($row['name']);
					$version=rawurlencode($row['version']);
					$build=rawurlencode($row['build']);
					$update_location=rawurlencode($row['update_location']);
					$update_file=rawurlencode($row['update_file']);
					$update_alias=rawurlencode($row['update_alias']);
					$country=rawurlencode($row['country']);
					$city=rawurlencode($row['city']);
					$zip=rawurlencode($row['zip']);
					$openweather_api=rawurlencode($row['openweather_api']);
					$backup_email=rawurlencode($row['backup_email']);
					$ping_home=$row['ping_home'];
					$timezone=rawurlencode($row['timezone']);
					$shutdown=$row['shutdown'];
					$reboot=$row['reboot'];
					$c_f=$row['c_f'];
					//echo row data to console 
					echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Data to sync with PiConnect: \n";
					echo "\033[1;33m Data Comm:\033[0m            \033[1;32m".$data."\033[0m \n";
					echo "\033[1;33m Table ID:\033[0m             \033[1;32m".$id."\033[0m \n";
					echo "\033[1;33m Purge:\033[0m                \033[1;32m".$purge."\033[0m \n";
					echo "\033[1;33m System Name:\033[0m          \033[1;32m".$row['name']."\033[0m \n";
					echo "\033[1;33m System Version:\033[0m       \033[1;32m".$row['version']."\033[0m \n";
					echo "\033[1;33m System Build:\033[0m         \033[1;32m".$row['build']."\033[0m \n";
					echo "\033[1;33m Update Location:\033[0m      \033[1;32m".$row['update_location']."\033[0m \n";
					echo "\033[1;33m Update File:\033[0m          \033[1;32m".$row['update_file']."\033[0m \n";
					echo "\033[1;33m Update Alias:\033[0m         \033[1;32m".$row['update_alias']."\033[0m \n";
					echo "\033[1;33m Country:\033[0m              \033[1;32m".$row['country']."\033[0m \n";
					echo "\033[1;33m City:\033[0m                 \033[1;32m".$row['city']."\033[0m \n";
					echo "\033[1;33m Zip Code:\033[0m             \033[1;32m".$row['zip']."\033[0m \n";
					echo "\033[1;33m OpenWeather API:\033[0m      \033[1;32m".$row['openweather_api']."\033[0m \n";
					echo "\033[1;33m Backup Email:\033[0m         \033[1;32m".$row['backup_email']."\033[0m \n";
					echo "\033[1;33m Ping Home:\033[0m            \033[1;32m".$row['ping_home']."\033[0m \n";
					echo "\033[1;33m Timezone:\033[0m             \033[1;32m".$row['timezone']."\033[0m \n";
					echo "\033[1;33m Shutdown Status:\033[0m      \033[1;32m".$row['shutdown']."\033[0m \n";
					echo "\033[1;33m Unit:\033[0m                 \033[1;32m".$row['c_f']."\033[0m \n";
					//call out to PiConnect with data 
					$url=$api_url."?api=${pihome_api}&ip=${my_ip}&data=${data}&table=system&id=${id}&purge=${purge}&name=${name}&version=${version}&build=${build}&update_location=${update_location}&update_file=${update_file}&update_alias=${update_alias}&country=${country}&city=${city}&zip=${zip}&openweather_api=${openweather_api}&backup_email=${backup_email}&ping_home=${ping_home}&timezone=${timezone}&shutdown=${shutdown}&reboot=${reboot}&unit=${c_f}";
					$result = url_get_contents($url);
					echo $url."\n";
					echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Result from PiConnect: \033[1;32m".$result."\033[0m \n";
					if ($result == 'Success'){
						$query = "UPDATE system SET sync = '1' WHERE id ='{$id}' LIMIT 1;";
						$conn->query($query);
						echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - System Settings Status Updated in Local Database.\n";
					}elseif($result == 'Failed'){
						echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Result from PiConnect: \033[31m".$result."\033[0m \n";
					}
					echo $line;
				}
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - System Settings Data to Push to PiConnect Ended\n";
			} else {
				echo $line;
				//Start Pulling System Settings Data with PiConnect.
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - System Settings Data to Pull from PiConnect \n";	
				$data='pull';
				$url=$api_url."?api=${pihome_api}&ip=${my_ip}&data=${data}&table=system&id=0";
				//echo $url."\n";
				$resulta = url_get_contents($url);
				if ($resulta != 'no-data'){
					// Convert JSON string to Array
					$jasonarray = json_decode($resulta, true);
					foreach ($jasonarray as $key => $value) {
						echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - System Settings Data from PiConnect: \n";
						$id=$value["id"];
						$purge=$value['purge'];
						$name=$value['name'];
						$version=$value['version'];
						$build=$value['build'];
						$update_location=$value['update_location'];
						$update_file=$value['update_file'];
						$update_alias=$value['update_alias'];
						$country=$value['country'];
						$city=$value['city'];
						$openweather_api=$value['openweather_api'];
						$backup_email=$value['backup_email'];
						$ping_home=$value['ping_home'];
						$timezone=$value['timezone'];
						$shutdwon=$value['shutdwon'];
						$reboot=$value['reboot'];
						echo "\033[1;33m Data Comm:\033[0m            \033[1;32m".$data."\033[0m \n";
						echo "\033[1;33m Table ID:\033[0m             \033[1;32m".$id."\033[0m \n";
						echo "\033[1;33m Purge:\033[0m                \033[1;32m".$purge."\033[0m \n";
						echo "\033[1;33m System Name:\033[0m          \033[1;32m".$name."\033[0m \n";
						echo "\033[1;33m System Version:\033[0m       \033[1;32m".$version."\033[0m \n";
						echo "\033[1;33m System Build:\033[0m         \033[1;32m".$build."\033[0m \n";
						echo "\033[1;33m Update Location:\033[0m      \033[1;32m".$update_location."\033[0m \n";
						echo "\033[1;33m Update File:\033[0m          \033[1;32m".$update_file."\033[0m \n";
						echo "\033[1;33m Update Alias:\033[0m         \033[1;32m".$update_alias."\033[0m \n";
						echo "\033[1;33m Country:\033[0m              \033[1;32m".$country."\033[0m \n";
						echo "\033[1;33m City:\033[0m                 \033[1;32m".$city."\033[0m \n";
						echo "\033[1;33m OpenWeather API:\033[0m      \033[1;32m".$openweather_api."\033[0m \n";
						echo "\033[1;33m Backup Email:\033[0m         \033[1;32m".$backup_email."\033[0m \n";
						echo "\033[1;33m Ping Home:\033[0m            \033[1;32m".$ping_home."\033[0m \n";
						echo "\033[1;33m Timezone:\033[0m             \033[1;32m".$timezone."\033[0m \n";
						echo "\033[1;33m Shutdwon Status:\033[0m      \033[1;32m".$shutdwon."\033[0m \n";
						echo "\033[1;33m Reboot Status:\033[0m        \033[1;32m".$reboot."\033[0m \n";
						$query = "UPDATE system SET sync = '1', name = '{$name}', version = '{$version}', build = '{$build}', update_location = '{$update_location}', update_file = '{$update_file}', update_alias = '{$update_alias}', country = '{$country}', city = '{$city}', openweather_api = '{$openweather_api}', backup_email = '{$backup_email}', ping_home = '{$ping_home}', timezone = '{$timezone}', shutdwon = '{$shutdwon}', reboot = '{$reboot}' where id = '{$id}' ;";
						$conn->query($query);
						echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - System Settings Data Pull from PiConnect Finished. \n";
						echo $line;
					}
				}else{
					echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - System Settings Data From PiConnect: \033[1;32m".$resulta."\033[0m \n";
				}
			}
			//System Settings Data Sync end here 
			/*****************************************************************************************************************************************************/
			//start syncing away table with PiConnect. 
			$query = "SELECT * FROM away where sync = 0 order by id asc;";
			$results = $conn->query($query);
			if (mysqli_num_rows($results) != 0){
				echo $line;
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Away Data to Sync with PiConnect: \033[32m". mysqli_num_rows($results)."\033[0m\n"; 
				while ($row = mysqli_fetch_assoc($results)) {
					$data='push';
					$id=$row['id'];
					$purge=$row['purge'];
					$status=$row['status'];
					$start_datetime=rawurlencode($row['start_datetime']);
					//echo row data to console 
					echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Data to Sync with PiConnect: \n";
					echo "\033[1;33m Data Comm:\033[0m            \033[1;32m".$data."\033[0m \n";
					echo "\033[1;33m Table ID:\033[0m             \033[1;32m".$id."\033[0m \n";
					echo "\033[1;33m Status:\033[0m               \033[1;32m".$status."\033[0m \n";
					echo "\033[1;33m Date & Time:\033[0m          \033[1;32m".$row['start_datetime']."\033[0m \n";
					//call out to PiConnect with data 
					$url=$api_url."?api=${pihome_api}&ip=${my_ip}&data=push&table=away&id=${id}&purge=${purge}&status=${status}&start_datetime=${start_datetime}";
					$result = url_get_contents($url);
					//echo $url."\n";
					echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Result from PiConnect: \033[1;32m".$result."\033[0m \n";
					if ($result == 'Success'){
						$query = "UPDATE away SET sync = '1' WHERE id ='{$id}' LIMIT 1;";
						$conn->query($query);
						echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Away Status Data Updated in Local Database.\n";
					}elseif($result == 'Failed'){
						echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Result from PiConnect: \033[31m".$result."\033[0m \n";
					}
					echo $line;
				}
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - No Away Status Data to Push to PiConnect \n";
			} else {
				echo $line;
				//start getting away table with PiConnect.
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Checking Away Status Data to Pull from PiConnect \n";	
				$data='pull';
				$url=$api_url."?api=${pihome_api}&ip=${my_ip}&data=${data}&table=away&id=0";
				//echo $url."\n";
				$resulta = url_get_contents($url);
				if ($resulta != 'no-data'){
					// Convert JSON string to Array
					$jasonarray = json_decode($resulta, true);
					foreach ($jasonarray as $key => $value) {
						echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Away Status Data  from PiConnect: \n";
						$id = $value["id"];
						$status = $value["status"];
						$start_datetime = $value["start_datetime"];
						$away_button_id = $value["away_button_id"];
						$away_button_child_id = $value["away_button_child_id"];
						echo "\033[1;33m Data Comm:\033[0m            \033[1;32m".$data."\033[0m \n";
						echo "\033[1;33m Table ID:\033[0m             \033[1;32m".$id."\033[0m \n";
						echo "\033[1;33m Status:\033[0m               \033[1;32m".$status."\033[0m \n";
						echo "\033[1;33m Start Date Time:\033[0m      \033[1;32m".$start_datetime."\033[0m \n";
						echo "\033[1;33m Away Button ID:\033[0m       \033[1;32m".$away_button_id."\033[0m \n";
						echo "\033[1;33m Away Button Child ID:\033[0m \033[1;32m".$away_button_child_id."\033[0m \n";
						$query = "UPDATE away SET status = '{$status}', start_datetime = '{$start_datetime}', away_button_id = '{$away_button_id}', away_button_child_id = '{$away_button_child_id}' where id = '{$id}' ;";
						$conn->query($query);
						echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Away Status Data Pull from PiConnect finished. \n";
						echo $line;
					}
				}else{
					echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Away Status Data From PiConnect: \033[1;32m".$resulta."\033[0m \n";
				}
			}
			//Away sync end here 
			/*****************************************************************************************************************************************************/	
			//Start Syncing Gateway Table with PiConnect. 
			$query = "SELECT * FROM gateway where sync = 0 order by id asc limit 1;";
			$results = $conn->query($query);
			if (mysqli_num_rows($results) != 0){
				echo $line;
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Gateway Data to Sync with PiConnect: \033[32m". mysqli_num_rows($results)."\033[0m\n"; 
				while ($row = mysqli_fetch_assoc($results)) {
					$data='push';
					$id=$row['id'];
					$sync=$row['sync'];
					$purge=$row['purge'];
					$type=$row['type'];
					$location=rawurlencode($row['location']);
					$port=rawurlencode($row['port']);
					$timout=$row['timout'];
					$pid=$row['pid'];
					$pid_running_since=rawurlencode($row['pid_running_since']);
					$reboot=$row['reboot'];
					$find_gw=$row['find_gw'];
					$version=rawurlencode($row['version']);
					//echo row data to console 
					echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Data to sync with PiConnect: \n";
					echo "\033[1;33m Data Comm:\033[0m            \033[1;32m".$data."\033[0m \n";
					echo "\033[1;33m Table ID:\033[0m             \033[1;32m".$id."\033[0m \n";
					echo "\033[1;33m Purge:\033[0m                \033[1;32m".$purge."\033[0m \n";
					echo "\033[1;33m Type:\033[0m                 \033[1;32m".$type."\033[0m \n";
					echo "\033[1;33m Location:\033[0m             \033[1;32m".$row['location']."\033[0m \n";
					echo "\033[1;33m Port:\033[0m                 \033[1;32m".$row['port']."\033[0m \n";
					echo "\033[1;33m Timout:\033[0m               \033[1;32m".$timout."\033[0m \n";
					echo "\033[1;33m PID:\033[0m                  \033[1;32m".$pid."\033[0m \n";
					echo "\033[1;33m PID Running Since:\033[0m    \033[1;32m".$row['pid_running_since']."\033[0m \n";
					echo "\033[1;33m Reboot:\033[0m               \033[1;32m".$reboot."\033[0m \n";
					echo "\033[1;33m Search Gateway:\033[0m       \033[1;32m".$find_gw."\033[0m \n";
					echo "\033[1;33m Version:\033[0m              \033[1;32m".$row['version']."\033[0m \n";
					//call out to PiConnect with data 
					$url=$api_url."?api=${pihome_api}&ip=${my_ip}&data=${data}&table=gateway&id=${id}&purge=${purge}&type=${type}&location=${location}&port=${port}&timout=${timout}&pid=${pid}&pid_running_since=${pid_running_since}&reboot=${reboot}&find_gw=${find_gw}&version=${version}";
					$result = url_get_contents($url);
					//echo $url."\n";
					echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Result from PiConnect: \033[1;32m".$result."\033[0m \n";
					if ($result == 'Success'){
						$query = "UPDATE gateway SET sync = '1' WHERE id ='{$id}' LIMIT 1;";
						$conn->query($query);
						echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Gateway Status Updated in Local Database.\n";
					}elseif($result == 'Failed'){
						echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Result from PiConnect: \033[31m".$result."\033[0m \n";
					}
					echo $line;
				}
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Gateway Data to Push to PiConnect \n";
			} else {
				echo $line;
				//Start Pulling Gateway Data from PiConnect.
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Checking Gateway Data to Pull from PiConnect \n";	
				$data='pull';
				$url=$api_url."?api=${pihome_api}&ip=${my_ip}&data=${data}&table=gateway&id=0";
				//echo $url."\n";
				$resulta = url_get_contents($url);
				if ($resulta != 'no-data'){
					// Convert JSON string to Array
					$jasonarray = json_decode($resulta, true);
					foreach ($jasonarray as $key => $value) {
						echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Gateway Data from PiConnect: \n";
						$id=$value['id'];
						$status=$value['status'];
						$sync=$value['sync'];
						$purge=$value['purge'];
						$type=$value['type'];
						$location=$value['location'];
						$port=$value['port'];
						$timout=$value['timout'];
						$reboot=$value['reboot'];
						$find_gw=$value['find_gw'];
						//echo row data to console 
						echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Data to sync with PiConnect: \n";
						echo "\033[1;33m Table ID:\033[0m             \033[1;32m".$id."\033[0m \n";
						echo "\033[1;33m Status:\033[0m               \033[1;32m".$status."\033[0m \n";
						echo "\033[1;33m Purge:\033[0m                \033[1;32m".$purge."\033[0m \n";
						echo "\033[1;33m Type:\033[0m                 \033[1;32m".$type."\033[0m \n";
						echo "\033[1;33m Location:\033[0m             \033[1;32m".$location."\033[0m \n";
						echo "\033[1;33m Port:\033[0m                 \033[1;32m".$port."\033[0m \n";
						echo "\033[1;33m Time out:\033[0m             \033[1;32m".$timout."\033[0m \n";
						echo "\033[1;33m Reboot:\033[0m               \033[1;32m".$reboot."\033[0m \n";
						echo "\033[1;33m Search Gateway:\033[0m       \033[1;32m".$find_gw."\033[0m \n";
						$query = "UPDATE gateway SET sync = '1', `purge` = '{$purge}', type = '{$type}', location = '{$location}', port = '{$port}', timout = '{$timout}', reboot = '{$reboot}', find_gw = '{$find_gw}' where id = '{$id}' ;";
						$conn->query($query);
						echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Gateway Data Pull from PiConnect Finished. \n";
						echo $line;
					}
				}else{
					echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Gateway Data From PiConnect: \033[1;32m".$resulta."\033[0m \n";
				}
			}
			//Gateway Data Sync end here 
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
					$url=$api_url."?api=${pihome_api}&ip=${my_ip}&data=${data}&table=gateway_logs&id=${id}&purge=${purge}&type=${type}&location=${location}&port=${port}&pid=${pid}&pid_start_time=${pid_start_time}&pid_datetime=${pid_datetime}";
					$result = url_get_contents($url);
					//echo $url."\n";
					echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Result from PiConnect: \033[1;32m".$result."\033[0m \n";
					if ($result == 'Success'){
						$query = "UPDATE gateway_logs SET sync = '1' WHERE id ='{$id}' LIMIT 1;";
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
			//start syncing nodes table with PiConnect. 
			$query = "SELECT * FROM nodes where sync = 0 order by id asc;";
			$results = $conn->query($query);
			if (mysqli_num_rows($results) != 0){
				echo $line;
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Total Nodes Data to sync with PiConnect: \033[32m". mysqli_num_rows($results)."\033[0m\n"; 
				while ($row = mysqli_fetch_assoc($results)) {
					$data='push';
					$id=$row['id'];
					$purge=$row['purge'];
					$node_id=$row['node_id'];
					$child_id_1=$row['child_id_1'];
					$name=rawurlencode($row['name']);
					$last_seen=rawurlencode($row['last_seen']);
					$status=$row['status'];
					$ms_version=rawurlencode($row['ms_version']);
					$sketch_version=rawurlencode($row['sketch_version']);
					//echo row data to console 
					echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Data to Sync with PiConnect: \n";
					echo "\033[1;33m Data Comm:\033[0m          \033[1;32m".$data."\033[0m \n";
					echo "\033[1;33m Table ID:\033[0m           \033[1;32m".$id."\033[0m \n";
					echo "\033[1;33m Purge:\033[0m              \033[1;32m".$purge."\033[0m \n";
					echo "\033[1;33m Node ID:\033[0m            \033[1;32m".$node_id."\033[0m \n";
					echo "\033[1;33m Child ID:\033[0m           \033[1;32m".$child_id_1."\033[0m \n";
					echo "\033[1;33m Name:\033[0m               \033[1;32m".$row['name']."\033[0m \n";
					echo "\033[1;33m Last Seen:\033[0m          \033[1;32m".$row['last_seen']."\033[0m \n";
					echo "\033[1;33m Status:\033[0m             \033[1;32m".$row['status']."\033[0m \n";
					echo "\033[1;33m MySensors version:\033[0m  \033[1;32m".$row['ms_version']."\033[0m \n";
					echo "\033[1;33m Sketch version:\033[0m     \033[1;32m".$row['sketch_version']."\033[0m \n";
					//call out to PiConnect with data 
					$url=$api_url."?api=${pihome_api}&ip=${my_ip}&data=${data}&table=nodes&id=${id}&purge=${purge}&node_id=${node_id}&child_id_1=${child_id_1}&name=${name}&last_seen=${last_seen}&status=${status}&ms_version=${ms_version}&sketch_version=${sketch_version}";
					$result = url_get_contents($url);
					echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Result from PiConnect: \033[1;32m".$result."\033[0m \n";
					if ($result == 'Success'){
						$query = "UPDATE nodes SET sync = '1' WHERE id ='{$id}' LIMIT 1;";
						$conn->query($query);
						echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Nodes sync status updated in local database.\n";
					}elseif($result == 'Failed'){
						echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Result from PiConnect: \033[31m".$result."\033[0m \n";
					}
					echo $line;
				}	
			} else {
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - No Nodes Data to sync with PiConnect \n";
			}
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Nodes Data Sync Finished. \n";
			//Nodes sync end here 
			/*****************************************************************************************************************************************************/		
			//Start Syncing Nodes Battery Table with PiConnect. 
			$query = "SELECT * FROM nodes_battery where sync = 0 order by id asc;";
			$results = $conn->query($query);
			if (mysqli_num_rows($results) != 0){
				echo $line;
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Total Nodes Battery Data to Sync with PiConnect: \033[32m". mysqli_num_rows($results)."\033[0m\n"; 
				while ($row = mysqli_fetch_assoc($results)) {
					$data='push';
					$id=$row['id'];
					$purge=$row['purge'];
					$node_id=$row['node_id'];
					$bat_voltage=rawurlencode($row['bat_voltage']);
					$bat_level=rawurlencode($row['bat_level']);
					$update=rawurlencode($row['update']);
					//echo row data to console 
					echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Data to Sync with PiConnect: \n";
					echo "\033[1;33m Data Comm:\033[0m          \033[1;32m".$data."\033[0m \n";
					echo "\033[1;33m Table ID:\033[0m           \033[1;32m".$id."\033[0m \n";
					echo "\033[1;33m Purge:\033[0m              \033[1;32m".$purge."\033[0m \n";
					echo "\033[1;33m Node ID:\033[0m            \033[1;32m".$node_id."\033[0m \n";
					echo "\033[1;33m Battery Voltage:\033[0m    \033[1;32m".$row['bat_voltage']."\033[0m \n";
					echo "\033[1;33m Battery Level:\033[0m      \033[1;32m".$row['bat_level']."\033[0m \n";
					echo "\033[1;33m Last Update:\033[0m        \033[1;32m".$row['update']."\033[0m \n";
					//call out to PiConnect with data 
					$url=$api_url."?api=${pihome_api}&ip=${my_ip}&data=${data}&table=nodes_battery&id=${id}&purge=${purge}&node_id=${node_id}&bat_voltage=${bat_voltage}&bat_level=${bat_level}&update=${update}";
					//echo $url;
					$result = url_get_contents($url);
					echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Result from PiConnect: \033[1;32m".$result."\033[0m \n";
					if ($result == 'Success'){
						$query = "UPDATE nodes_battery SET sync = '1' WHERE id ='{$id}' LIMIT 1;";
						$conn->query($query);
						echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Nodes Battery Sync Status Updated in Local Database.\n";
					}elseif($result == 'Failed'){
						echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Result from PiConnect: \033[31m".$result."\033[0m \n";
					}
					echo $line;
				}	
			} else {
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - No Nodes Battery Data to Sync with PiConnect \n";
			}
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Nodes Battery Data Sync Finished. \n";
			//Nodes Battery Sync End Here 
			/*****************************************************************************************************************************************************/
			//Start syncing boiler table with PiConnect. 
			$query = "SELECT * FROM boiler where sync = 0 order by id asc;";
			$results = $conn->query($query);
			if (mysqli_num_rows($results) != 0){
				echo $line;
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Total Boiler Data to Sync with PiConnect: \033[32m". mysqli_num_rows($results)."\033[0m\n"; 
				while ($row = mysqli_fetch_assoc($results)) {
					$data='push';
					$id=$row['id'];
					$purge=$row['purge'];
					$status=$row['status'];
					$fired_status=$row['fired_status'];
					$name=rawurlencode($row['name']);
					$node_id=$row['node_id'];
					$node_child_id=$row['node_child_id'];
					$hysteresis_time=$row['hysteresis_time'];
					$max_operation_time=$row['max_operation_time'];
					$datetime=rawurlencode($row['datetime']);
					$gpio_pin=$row['gpio_pin'];
					//echo row data to console 
					echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Data to Sync with PiConnect: \n";
					echo "\033[1;33m Data Comm:\033[0m           \033[1;32m".$data."\033[0m \n";
					echo "\033[1;33m Table ID:\033[0m            \033[1;32m".$id."\033[0m \n";
					echo "\033[1;33m Purge:\033[0m               \033[1;32m".$purge."\033[0m \n";
					echo "\033[1;33m Status:\033[0m              \033[1;32m".$status."\033[0m \n";
					echo "\033[1;33m Fired Status:\033[0m        \033[1;32m".$fired_status."\033[0m \n";
					echo "\033[1;33m Name:\033[0m                \033[1;32m".$row['name']."\033[0m \n";
					echo "\033[1;33m ID:\033[0m                  \033[1;32m".$node_id."\033[0m \n";
					echo "\033[1;33m Node Child ID:\033[0m       \033[1;32m".$node_child_id."\033[0m \n";
					echo "\033[1;33m Hysteresis Time:\033[0m     \033[1;32m".$hysteresis_time."\033[0m \n";
					echo "\033[1;33m Max Operation Time:\033[0m  \033[1;32m".$max_operation_time."\033[0m \n";
					echo "\033[1;33m Date & Time:\033[0m         \033[1;32m".$row['datetime']."\033[0m \n";
					echo "\033[1;33m GPIO Pin:\033[0m            \033[1;32m".$gpio_pin."\033[0m \n";
					//call out to PiConnect with data 
					$url=$api_url."?api=${pihome_api}&ip=${my_ip}&data=${data}&table=boiler&id=${id}&purge=${purge}&status=${status}&fired_status=${fired_status}&name=${name}&node_id=${node_id}&node_child_id=${node_child_id}&hysteresis_time=${hysteresis_time}&max_operation_time=${max_operation_time}&datetime=${datetime}&gpio_pin=${gpio_pin}";
					$result = url_get_contents($url);
					//echo $url."\n";
					echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Result from PiConnect: \033[1;32m".$result."\033[0m \n";
					if ($result == 'Success'){
						$query = "UPDATE boiler SET sync = '1' WHERE id ='{$id}' LIMIT 1;";
						$conn->query($query);
						echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Boiler Sync Status Updated in Local Database.\n";
					}elseif($result == 'Failed'){
						echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Result From PiConnect: \033[31m".$result."\033[0m \n";
					}
					echo $line;
				}		
			} else {
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - No Boiler Data to Sync with PiConnect \n";
			}
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Boiler Data Sync Finished. \n";
			//Boiler sync end here 
			/*****************************************************************************************************************************************************/
			//start syncing boiler Logs table with PiConnect. 
			$query = "SELECT * FROM boiler_logs where sync = 0 AND stop_datetime IS NOT NULL order by id asc;";
			$results = $conn->query($query);
			if (mysqli_num_rows($results) != 0){
				echo $line;
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Boiler Logs Data to Sync with PiConnect: \033[32m". mysqli_num_rows($results)."\033[0m\n"; 
				while ($row = mysqli_fetch_assoc($results)) {
					$data='push';
					$id=$row['id'];
					$purge=$row['purge'];
					$start_datetime=rawurlencode($row['start_datetime']);
					$start_cause=rawurlencode($row['start_cause']);
					$stop_datetime=rawurlencode($row['stop_datetime']);
					$stop_cause=rawurlencode($row['stop_cause']);
					$expected_end_date_time=rawurlencode($row['expected_end_date_time']);
					//echo row data to console 
					echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Data to Sync with PiConnect: \n";
					echo "\033[1;33m Data Comm:\033[0m           \033[1;32m".$data."\033[0m \n";
					echo "\033[1;33m Table ID:\033[0m            \033[1;32m".$id."\033[0m \n";
					echo "\033[1;33m Start DateTime:\033[0m      \033[1;32m".$row['start_datetime']."\033[0m \n";
					echo "\033[1;33m Start Cause:\033[0m         \033[1;32m".$row['start_cause']."\033[0m \n";
					echo "\033[1;33m Stop DateTime:\033[0m       \033[1;32m".$row['stop_datetime']."\033[0m \n";
					echo "\033[1;33m Stop Cause:\033[0m          \033[1;32m".$row['stop_cause']."\033[0m \n";
					echo "\033[1;33m Expected End Time:\033[0m   \033[1;32m".$row['expected_end_date_time']."\033[0m \n";
					//call out to PiConnect with data 
					$url=$api_url."?api=${pihome_api}&ip=${my_ip}&data=${data}&table=boiler_logs&id=${id}&purge=${purge}&start_datetime=${start_datetime}&start_cause=${start_cause}&stop_datetime=${stop_datetime}&stop_cause=${stop_cause}&expected_end_date_time=${expected_end_date_time}";
					$result = url_get_contents($url);
					//echo $url."\n";
					echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Result from PiConnect: \033[1;32m".$result."\033[0m \n";
					if ($result == 'Success'){
						$query = "UPDATE boiler_logs SET sync = '1' WHERE id ='{$id}' LIMIT 1;";
						$conn->query($query);
						echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Boiler Logs sync status updated in local database.\n";
					}elseif($result == 'Failed'){
						echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Result from PiConnect: \033[31m".$result."\033[0m \n";
					}
					echo $line;
				}
			} else {
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - No Boiler Logs Data to Sync with PiConnect \n";
			}
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Boiler Logs data sync finished. \n";
			//Boiler Logs sync end here 
			/*****************************************************************************************************************************************************/
			//start syncing zone table with PiConnect. 
			$query = "SELECT * FROM zone where sync = 0 order by id asc;";
			$results = $conn->query($query);
			if (mysqli_num_rows($results) != 0){
				echo $line;
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Total Zone Data to Sync with PiConnect: \033[32m". mysqli_num_rows($results)."\033[0m\n"; 
				while ($row = mysqli_fetch_assoc($results)) {
					$data='push';
					$id=$row['id'];
					$purge=$row['purge'];
					$status=$row['status'];
					$index_id=$row['index_id'];
					$name=rawurlencode($row['name']);
					$type=$row['type'];
					//$model=$row['model'];
					$max_c=$row['max_c'];
					$max_operation_time=$row['max_operation_time'];
					$hysteresis_time=$row['hysteresis_time'];
					$sensor_id=$row['sensor_id'];
					$sensor_child_id=$row['sensor_child_id'];
					$controler_id=$row['controler_id'];
					$controler_child_id=$row['controler_child_id'];
					$boiler_id=$row['boiler_id'];
					$gpio_pin=$row['gpio_pin'];
					//echo row data to console 
					echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Data to Sync with PiConnect: \n";
					echo "\033[1;33m Data Comm:\033[0m             \033[1;32m".$data."\033[0m \n";
					echo "\033[1;33m Table ID:\033[0m              \033[1;32m".$id."\033[0m \n";
					echo "\033[1;33m Purge:\033[0m                 \033[1;32m".$purge."\033[0m \n";
					echo "\033[1;33m Status:\033[0m                \033[1;32m".$status."\033[0m \n";
					echo "\033[1;33m Index ID:\033[0m              \033[1;32m".$index_id."\033[0m \n";
					echo "\033[1;33m Zone Name:\033[0m             \033[1;32m".$row['name']."\033[0m \n";
					echo "\033[1;33m Zone Type:\033[0m             \033[1;32m".$type."\033[0m \n";
					echo "\033[1;33m Zone Max C:\033[0m            \033[1;32m".$max_c."\033[0m \n";
					echo "\033[1;33m Operation Time:\033[0m        \033[1;32m".$max_operation_time."\033[0m \n";
					echo "\033[1;33m Hysteresis Time:\033[0m       \033[1;32m".$hysteresis_time."\033[0m \n";
					echo "\033[1;33m Sensor ID:\033[0m             \033[1;32m".$sensor_id."\033[0m \n";
					echo "\033[1;33m Sensor Child ID:\033[0m       \033[1;32m".$sensor_child_id."\033[0m \n";
					echo "\033[1;33m Controler ID:\033[0m          \033[1;32m".$controler_id."\033[0m \n";
					echo "\033[1;33m Controler Child ID:\033[0m    \033[1;32m".$controler_child_id."\033[0m \n";
					echo "\033[1;33m Boiler ID:\033[0m             \033[1;32m".$boiler_id."\033[0m \n";
					echo "\033[1;33m GPIO Pin:\033[0m              \033[1;32m".$gpio_pin."\033[0m \n";
					//call out to PiConnect with data 
					$url=$api_url."?api=${pihome_api}&ip=${my_ip}&data=${data}&table=zone&id=${id}&purge=${purge}&status=${status}&index_id=${index_id}&name=${name}&type=${type}&max_c=${max_c}&max_operation_time=${max_operation_time}&hysteresis_time=${hysteresis_time}&sensor_id=${sensor_id}&sensor_child_id=${sensor_child_id}&controler_id=${controler_id}&controler_child_id=${controler_child_id}&boiler_id=${boiler_id}&gpio_pin=${gpio_pin}";
					//echo $url."\n"; 
					$result = url_get_contents($url);
					echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Result from PiConnect: \033[1;32m".$result."\033[0m \n";
					if ($result == 'Update'){
						$query = "UPDATE zone SET sync = '1' WHERE id ='{$id}' LIMIT 1;";
						$conn->query($query);
						echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone Sync Status Updated in Local Database.\n";
					}elseif ($result == 'Purge'){
						//Delete Boost Records
						$query = "DELETE FROM boost WHERE zone_id = '{$id}' LIMIT 1;";
						$conn->query($query);
						//Delete All Message Out records
						$query = "DELETE FROM messages_out WHERE zone_id = '{$id}' LIMIT 1;";
						$conn->query($query);
						//Delete Override records
						$query = "DELETE FROM override WHERE zone_id = '{$id}' LIMIT 1;";
						$conn->query($query);
						//Delete Daily Time records
						$query = "DELETE FROM schedule_daily_time_zone WHERE zone_id = '{$id}';";
						$conn->query($query);
						//Delete Night Climat records
						$query = "DELETE FROM schedule_night_climat_zone WHERE zone_id = '{$id}';";
						$conn->query($query);
						//Delete All Zone Logs records
						$query = "DELETE FROM zone_logs WHERE zone_id = '{$id}';";
						$conn->query($query);
						//Delete Zone record
						$query = "DELETE FROM zone WHERE id = '{$id}' LIMIT 1;";
						$conn->query($query);
						echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone Record Purged in Local Database \n";
					}elseif ($result == 'Success'){
						$query = "UPDATE zone SET sync = '1' WHERE id ='{$id}' LIMIT 1;";
						$conn->query($query);
						echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone Sync Status Updated in Local Database.\n";
					}elseif($result == 'Failed'){
						echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Result from PiConnect: \033[31m".$result."\033[0m \n";
					}
						
					echo $line;
				}
			} else {
				echo $line;
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - No Zone Data to Sync with PiConnect \n";
			}
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone data sync finished. \n";
			//Zone sync end here 
			/*****************************************************************************************************************************************************/
			//start syncing Zone Logs table with PiConnect. 
			//get zone log records with sync status 0
			$query = "SELECT * FROM zone_logs where sync = 0 order by id asc;";
			$zlresults = $conn->query($query);
			$row = mysqli_fetch_array($zlresults);
			$boiler_log_id = $row['boiler_log_id'];
			//check if boiler log is synced
			//$query = "SELECT * FROM boiler_logs where sync = 0 AND id = '{$boiler_log_id}' AND stop_datetime IS NOT NULL order by id asc;";
			$query = "SELECT * FROM boiler_logs where id = '{$boiler_log_id}';";
			$result = $conn->query($query);
			
			if (mysqli_num_rows($zlresults) != 0){
				echo $line;
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone Logs Data to Sync with PiConnect: \033[32m". mysqli_num_rows($zlresults)."\033[0m\n"; 
				while ($row = mysqli_fetch_assoc($zlresults)) {
					$data='push';
					$id=$row['id'];
					$purge=$row['purge'];
					$zone_id=$row['zone_id'];
					$boiler_log_id=$row['boiler_log_id'];
					$status=$row['status'];
					//echo row data to console 
					echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Data to Sync with PiConnect: \n";
					echo "\033[1;33m Data Comm:\033[0m           \033[1;32m".$data."\033[0m \n";
					echo "\033[1;33m Table ID:\033[0m            \033[1;32m".$id."\033[0m \n";
					echo "\033[1;33m Purge:\033[0m               \033[1;32m".$purge."\033[0m \n";
					echo "\033[1;33m Zone ID:\033[0m             \033[1;32m".$zone_id."\033[0m \n";
					echo "\033[1;33m Boiler Log ID:\033[0m       \033[1;32m".$boiler_log_id."\033[0m \n";
					echo "\033[1;33m Status:\033[0m              \033[1;32m".$status."\033[0m \n";
					//call out to PiConnect with data 
					$url=$api_url."?api=${pihome_api}&ip=${my_ip}&data=${data}&table=zone_logs&id=${id}&purge=${purge}&zone_id=${zone_id}&boiler_log_id=${boiler_log_id}&status=${status}";
					$result = url_get_contents($url);
					//echo $url."\n";
					echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Result from PiConnect: \033[1;32m".$result."\033[0m \n";
					if ($result == 'Success'){
						$query = "UPDATE zone_logs SET sync = '1' WHERE id ='{$id}' LIMIT 1;";
						$conn->query($query);
						echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone Logs sync status updated in local database.\n";
					}elseif($result == 'Failed'){
						echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Result from PiConnect: \033[31m".$result."\033[0m \n";
					}
					echo $line;
				}
			} else {
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - No Zone Logs Data to Sync with PiConnect. \n";
			}
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone Logs Data Sync Finished. \n";
			//Zone Logs sync end here 
			/*****************************************************************************************************************************************************/
			//start syncing Schedul Time table with PiConnect. 
			$query = "SELECT * FROM schedule_daily_time where sync = 0 order by id asc;";
			$results = $conn->query($query);
			//check if anything to sync with PiConnect
			if (mysqli_num_rows($results) != 0){
				echo $line;
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Schedul Time Data to Sync with PiConnect: \033[32m". mysqli_num_rows($results)."\033[0m\n"; 
				while ($row = mysqli_fetch_assoc($results)) {
					$data='push';
					$id=$row['id'];
					$purge=$row['purge'];
					$status=$row['status'];
					$start=rawurlencode($row['start']);
					$end=rawurlencode($row['end']);
					$WeekDays=$row['WeekDays'];
					//echo row data to console 
					echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Data to Sync with PiConnect: \n";
					echo "\033[1;33m Data Comm:\033[0m            \033[1;32m".$data."\033[0m \n";
					echo "\033[1;33m Table ID:\033[0m             \033[1;32m".$id."\033[0m \n";
					echo "\033[1;33m Purge:\033[0m                \033[1;32m".$purge."\033[0m \n";
					echo "\033[1;33m Status:\033[0m               \033[1;32m".$status."\033[0m \n";
					echo "\033[1;33m Start Time:\033[0m           \033[1;32m".$row['start']."\033[0m \n";
					echo "\033[1;33m End Time:\033[0m             \033[1;32m".$row['end']."\033[0m \n";
					echo "\033[1;33m Weekdays :\033[0m            \033[1;32m".$row['WeekDays']."\033[0m \n"; 
					//call out to PiConnect with data 
					$url=$api_url."?api=${pihome_api}&ip=${my_ip}&data=${data}&table=schedule_daily_time&id=${id}&purge=${purge}&status=${status}&start=${start}&end=${end}&WeekDays=${WeekDays}";
					$result = url_get_contents($url);
					//echo $url."\n";
					echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Result from PiConnect: \033[1;32m".$result."\033[0m \n";
					if ($result == 'Update'){
						$query = "UPDATE schedule_daily_time SET sync = '1' WHERE id ='{$id}' LIMIT 1;";
						$conn->query($query);
						echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Schedul Time Sync Updated in Local Database \n";
					}elseif ($result == 'Purge'){
						$query = "DELETE FROM schedule_daily_time_zone WHERE schedule_daily_time_id = '{$id}';";
						$conn->query($query);
						$query = "DELETE FROM schedule_daily_time WHERE id = '{$id}';";
						$conn->query($query);
						echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Schedul Time Sync Purged in Local Database \n";
					}elseif($result == 'Failed'){
						echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Schedule Time Sync Faild with Error: \033[31m".$result."\033[0m \n";
					}
					echo $line;
				}
			} else {
				echo $line;
				//Start Pull Request for Schedul Time From PiConnect.
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Checking Schedul Time Data to Pull from PiConnect \n";	
				$data='pull';
				$url=$api_url."?api=${pihome_api}&ip=${my_ip}&data=${data}&table=schedule_daily_time&id=0";
				//echo $url."\n";
				$resulta = url_get_contents($url);
				if ($resulta != 'no-data'){
					// Convert JSON string to Array
					$jasonarray = json_decode($resulta, true);
					foreach ($jasonarray as $key => $value) {
						echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Start Pulling Schedule Time Data From PiConnect \n";
						$id = $value["id"];
						$purge = $value["purge"];
						$status = $value["status"];
						$start = $value["start"];
						$end = $value["end"];
						$WeekDays = $value["WeekDays"];
						echo "\033[1;33m Data Comm:\033[0m            \033[1;32m".$data."\033[0m \n";
						echo "\033[1;33m Table ID:\033[0m             \033[1;32m".$id."\033[0m \n";
						echo "\033[1;33m Purge:\033[0m                \033[1;32m".$purge."\033[0m \n";
						echo "\033[1;33m Status:\033[0m               \033[1;32m".$status."\033[0m \n";
						echo "\033[1;33m Start Time:\033[0m           \033[1;32m".$start."\033[0m \n";
						echo "\033[1;33m End Time:\033[0m             \033[1;32m".$end."\033[0m \n";
						echo "\033[1;33m Weekdays :\033[0m            \033[1;32m".$WeekDays."\033[0m \n"; 
						if ($purge == '1' && $id != '0'){
							$query = "DELETE FROM schedule_daily_time_zone WHERE schedule_daily_time_id = '{$id}';";
							$conn->query($query);
							$query = "DELETE FROM schedule_daily_time WHERE id = '{$id}';";
							$conn->query($query);
							echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Schedul Time Sync Purged in Local Database \n";
						}elseif($purge == '0' && $id != '0'){
							$query = "UPDATE schedule_daily_time SET sync = '1',  status = '{$status}', start = '{$start}', end = '{$end}', WeekDays = '{$WeekDays}' WHERE id ='{$id}' LIMIT 1;";
							$conn->query($query);
							echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Schedul Time Sync Updated in Local Database \n";
						}elseif ($purge == '1' && $id == '0'){
							// Add schedule_daily_time record and set to sync 0
							$query = "INSERT INTO schedule_daily_time (status, start, end, WeekDays) VALUES ('{$status}', '{$start}', '{$end}', '{$WeekDays}');";
							$result = $conn->query($query);
						}
						echo $line;
					}
				}else {
					echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Shedule Daily Time from PiConnect: \033[1;32m".$resulta."\033[0m \n";
				}
			}
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Schedule Time Data Sync Finished. \n";
			//Schedul Time sync end here 
			/*****************************************************************************************************************************************************/
			//start syncing Schedul Time Zone table with PiConnect. 
			$query = "SELECT * FROM schedule_daily_time_zone where sync = 0 order by id asc;";
			$results = $conn->query($query);
			if (mysqli_num_rows($results) != 0){
				echo $line;
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Schedul Time Zone Data to sync with PiConnect: \033[32m". mysqli_num_rows($results)."\033[0m\n"; 
				while ($row = mysqli_fetch_assoc($results)) {
					$data='push';
					$id=$row['id'];
					$purge=$row['purge'];
					$status=$row['status'];
					$schedule_daily_time_id=rawurlencode($row['schedule_daily_time_id']);
					$zone_id=$row['zone_id'];
					$temperature=$row['temperature'];
					//echo row data to console 
					echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Data to Sync with PiConnect: \n";
					echo "\033[1;33m Data Comm:\033[0m            \033[1;32m".$data."\033[0m \n";
					echo "\033[1;33m Table ID:\033[0m             \033[1;32m".$id."\033[0m \n";
					echo "\033[1;33m Purge:\033[0m                \033[1;32m".$purge."\033[0m \n";
					echo "\033[1;33m Status:\033[0m               \033[1;32m".$status."\033[0m \n";
					echo "\033[1;33m Schedule Time ID:\033[0m     \033[1;32m".$row['schedule_daily_time_id']."\033[0m \n";
					echo "\033[1;33m Zone ID:\033[0m              \033[1;32m".$zone_id."\033[0m \n";
					echo "\033[1;33m Temperature:\033[0m          \033[1;32m".$temperature."\033[0m \n";
					//call out to PiConnect with data 
					$url=$api_url."?api=${pihome_api}&ip=${my_ip}&data=${data}&table=schedule_daily_time_zone&id=${id}&purge=${purge}&status=${status}&schedule_daily_time_id=${schedule_daily_time_id}&zone_id=${zone_id}&temperature=${temperature}";
					$result = url_get_contents($url);
					//echo $url."\n";
					echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Result from PiConnect \033[1;32m".$result."\033[0m \n";
					if ($result == 'Success'){
						$query = "UPDATE schedule_daily_time_zone SET sync = '1' WHERE id ='{$id}' LIMIT 1;";
						$conn->query($query);
						echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Schedul Time Zone sync status updated in local database.\n";
					}elseif($result == 'Failed'){
						echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Result from PiConnect: \033[31m".$result."\033[0m \n";
					}
				echo $line;
				}
			} else {
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - No Schedule Time Zone Data to sync with PiConnect \n";
				//Start Pull Request for Schedul Time From PiConnect.
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Checking Schedule Time Zone Data to Pull from PiConnect \n";	
				$data='pull';
				$url=$api_url."?api=${pihome_api}&ip=${my_ip}&data=${data}&table=schedule_daily_time_zone&id=0";
				//echo $url."\n";
				$resulta = url_get_contents($url);
				if ($resulta != 'no-data'){
					// Convert JSON string to Array
					$jasonarray = json_decode($resulta, true);
					foreach ($jasonarray as $key => $value) {
						echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Start Pulling Schedule Time Zone from PiConnect \n";
						$id = $value["id"];
						$status = $value["status"];
						$schedule_daily_time_id = $value["schedule_daily_time_id"];
						$zone_id = $value["zone_id"];
						$temperature = $value["temperature"];
						echo "\033[1;33m Data Comm:\033[0m            \033[1;32m".$data."\033[0m \n";
						echo "\033[1;33m Table ID:\033[0m             \033[1;32m".$id."\033[0m \n";
						echo "\033[1;33m Status:\033[0m               \033[1;32m".$status."\033[0m \n";
						echo "\033[1;33m Schedule Time ID:\033[0m     \033[1;32m".$schedule_daily_time_id."\033[0m \n";
						echo "\033[1;33m Zone ID:\033[0m              \033[1;32m".$zone_id."\033[0m \n";
						echo "\033[1;33m Temperature:\033[0m          \033[1;32m".$temperature."\033[0m \n";
						$query = "UPDATE schedule_daily_time_zone SET sync = '1',  status = '{$status}', schedule_daily_time_id = '{$schedule_daily_time_id}', zone_id = '{$zone_id}', temperature = '{$temperature}' WHERE id ='{$id}' LIMIT 1;";
						$conn->query($query);
						echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Schedul Time Zone Updated in Local Database.\n";
						echo $line;
					}
				}else {
					echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Shedule Daily Time Zone from PiConnect: \033[1;32m".$resulta."\033[0m \n";
				}
			}
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Shedule Daily Time Zone Data Sync Finished. \n";
			//Schedul Time Zone sync end here 
			/*****************************************************************************************************************************************************/
			//New Schedul Time Zone sync end here 
			echo $line;
			//Start Pull Request for Schedul Time From PiConnect.
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Checking New Schedul Time Data to Pull from PiConnect \n";	
			$data='new';
			$url=$api_url."?api=${pihome_api}&ip=${my_ip}&data=${data}&table=schedule_daily_time&id=0";
			//echo $url."\n";
			$resulta = url_get_contents($url);
			if ($resulta != 'no-data'){
				// Convert JSON string to Array
				$jasonarray = json_decode($resulta, true);
				//print_r($jasonarray);
				foreach ($jasonarray as $key => $value) {
					if (isset($value["purge"])){
						echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Start Pulling New Schedule Time Data From PiConnect \n";
						$id = $value["id"];
						$purge = $value["purge"];
						$sync = $value["sync"];
						$status = $value["status"];
						$start = $value["start"];
						$end = $value["end"];
						$WeekDays = $value["WeekDays"];
						echo "\033[1;33m Data Comm:\033[0m            \033[1;32m".$data."\033[0m \n";
						echo "\033[1;33m Table ID:\033[0m             \033[1;32m".$id."\033[0m \n";
						echo "\033[1;33m Purge:\033[0m                \033[1;32m".$purge."\033[0m \n";
						echo "\033[1;33m Status:\033[0m               \033[1;32m".$status."\033[0m \n";
						echo "\033[1;33m Start Time:\033[0m           \033[1;32m".$start."\033[0m \n";
						echo "\033[1;33m End Time:\033[0m             \033[1;32m".$end."\033[0m \n";
						echo "\033[1;33m WeekDays:\033[0m             \033[1;32m".$WeekDays."\033[0m \n";
						if ($id == '0' && $purge == '1' && $sync == '0' ){
							// Add schedule_daily_time record and set to sync 0
							$query = "INSERT INTO schedule_daily_time (sync, `purge`, status, start, end, WeekDays) VALUES ('0', '0', '{$status}', '{$start}', '{$end}', '{$WeekDays}');";
							$result = $conn->query($query);
							echo mysqli_error($conn)."\n";
							$schedule_daily_time_id = mysqli_insert_id($conn);
						}
						echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - New Shedule Daily Time from PiConnect added to Database. \n";
						echo $line;
					}elseif (isset($value["schedule_daily_time_id"])){
						echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Start New Schedule Zone Data From PiConnect \n";
						$id = $value["id"];
						$status = $value["status"];
						$temperature = $value["temperature"];
						$zone_id = $value["zone_id"];
						echo "\033[1;33m Data Comm:\033[0m            \033[1;32m".$data."\033[0m \n";
						echo "\033[1;33m Table ID:\033[0m             \033[1;32m".$id."\033[0m \n";
						echo "\033[1;33m Schedule Time ID:\033[0m     \033[1;32m".$schedule_daily_time_id."\033[0m \n";
						echo "\033[1;33m Temperature:\033[0m          \033[1;32m".$temperature."\033[0m \n";
						echo "\033[1;33m Zone ID:\033[0m              \033[1;32m".$zone_id."\033[0m \n";
						$query = "INSERT INTO schedule_daily_time_zone(sync, `purge`, status, schedule_daily_time_id, zone_id, temperature) VALUES ('0', '0', '{$status}', '{$schedule_daily_time_id}','{$zone_id}','{$temperature}')"; 
						$results = $conn->query($query);
						echo mysqli_error($conn)."\n";
						echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - New Schedule Zone Data From PiConnect added to Database. \n";
						echo $line;
					}
				}
			}else {
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Shedule Daily Time from PiConnect: \033[1;32m".$resulta."\033[0m \n";
			}
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - New Schedule Time Data Sync Finished. \n";
			//New Schedul Time sync end here 
			/*****************************************************************************************************************************************************/
			//start syncing Override table with PiConnect. 
			$query = "SELECT * FROM override where sync = 0 order by id asc;";
			$results = $conn->query($query);
			if (mysqli_num_rows($results) != 0){
				echo $line;
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Override Data to sync with PiConnect : \033[32m". mysqli_num_rows($results)."\033[0m\n"; 
				while ($row = mysqli_fetch_assoc($results)) {
					$data='push';
					$id=$row['id'];
					$purge=$row['purge'];
					$status=$row['status'];
					$zone_id=$row['zone_id'];
					$time=rawurlencode($row['time']);
					$temperature=$row['temperature'];
					//echo row data to console 
					echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Data to Sync with PiConnect: \n";
					echo "\033[1;33m Data Comm:\033[0m            \033[1;32m".$data."\033[0m \n";
					echo "\033[1;33m Table ID:\033[0m             \033[1;32m".$id."\033[0m \n";
					echo "\033[1;33m Purge:\033[0m                \033[1;32m".$purge."\033[0m \n";
					echo "\033[1;33m Status:\033[0m               \033[1;32m".$status."\033[0m \n";
					echo "\033[1;33m Zone ID:\033[0m              \033[1;32m".$zone_id."\033[0m \n";
					echo "\033[1;33m Time:\033[0m                 \033[1;32m".$row['time']."\033[0m \n";
					echo "\033[1;33m Temperature:\033[0m          \033[1;32m".$temperature."\033[0m \n";
					//call out to PiConnect with data 
					$url=$api_url."?api=${pihome_api}&ip=${my_ip}&data=${data}&table=override&id=${id}&purge=${purge}&status=${status}&zone_id=${zone_id}&time=${time}&temperature=${temperature}";
					$result = url_get_contents($url);
					//echo $url."\n";
					echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Result from PiConnect: \033[1;32m".$result."\033[0m \n";
					if ($result == 'Success'){
						$query = "UPDATE override SET sync = '1' WHERE id ='{$id}' LIMIT 1;";
						$conn->query($query);
						echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Override sync status updated in local database.\n";
					}elseif($result == 'Failed'){
						echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Result from PiConnect: \033[31m".$result."\033[0m \n";
					}
					echo $line;
				}
			} else {
				echo $line;
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - No Override Data to Push to PiConnect \n";
				//start pulling Override table with PiConnect.
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Checking Override Data to Pull from PiConnect \n";	
				$data='pull';
				$url=$api_url."?api=${pihome_api}&ip=${my_ip}&data=${data}&table=override&id=0";
				//echo $url."\n";
				$resulta = url_get_contents($url);
				if ($resulta != 'no-data'){
					// Convert JSON string to Array
					$jasonarray = json_decode($resulta, true);
					foreach ($jasonarray as $key => $value) {
						echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Override Data From PiConnect. \n";
						$id = $value["id"];
						//$purge = $value["purge"];
						$status = $value["status"];
						$zone_id = $value["zone_id"];
						$time = $value["time"];
						$temperature = $value["temperature"];
						echo "\033[1;33m Data Comm:\033[0m            \033[1;32m".$data."\033[0m \n";
						echo "\033[1;33m Table ID:\033[0m             \033[1;32m".$id."\033[0m \n";
						//echo "\033[1;33m Purge:\033[0m                \033[1;32m".$purge."\033[0m \n";
						echo "\033[1;33m Zone ID:\033[0m              \033[1;32m".$zone_id."\033[0m \n";
						echo "\033[1;33m Time:\033[0m                 \033[1;32m".$time."\033[0m \n";
						echo "\033[1;33m Temperature:\033[0m          \033[1;32m".$temperature."\033[0m \n";
						//check if Data alarady exist if do then update existing Data.
						$query = "SELECT * FROM override where id = '{$id}';";
						$result = $conn->query($query);
						if (mysqli_num_rows($result) == 1){
							$query = "UPDATE override SET sync = '1',  status = '{$status}', time = '{$time}', temperature = '{$temperature}' WHERE id ='{$id}' LIMIT 1;";
							$conn->query($query);
							echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Override Data Updated in Local Database.\n";
							echo $line;
						}else{
							//Override Data dos not exit add one. 
						}
					}
				}else{
					echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Override Data From PiConnect: \033[1;32m".$resulta."\033[0m \n";
				}
			}
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Override Data Sync Finished. \n";
			//Override sync end here 
			/*****************************************************************************************************************************************************/
			//start syncing Boost table with PiConnect. 
			$query = "SELECT * FROM boost where sync = 0 order by id asc;";
			$results = $conn->query($query);
			if (mysqli_num_rows($results) != 0){
				echo $line;
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Boost Data to sync with PiConnect: \033[32m". mysqli_num_rows($results)."\033[0m\n"; 
				while ($row = mysqli_fetch_assoc($results)) {
					$data='push';
					$id=$row['id'];
					$purge=$row['purge'];
					$status=$row['status'];
					$zone_id=$row['zone_id'];
					$time=rawurlencode($row['time']);
					$temperature=$row['temperature'];
					$minute=$row['minute'];
					//echo row data to console 
					echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Data to Sync with PiConnect: \n";
					echo "\033[1;33m Data Comm:\033[0m           \033[1;32m".$data."\033[0m \n";
					echo "\033[1;33m Table ID:\033[0m            \033[1;32m".$id."\033[0m \n";
					echo "\033[1;33m Purge:\033[0m               \033[1;32m".$purge."\033[0m \n";
					echo "\033[1;33m Status:\033[0m              \033[1;32m".$status."\033[0m \n";
					echo "\033[1;33m Zone ID:\033[0m             \033[1;32m".$zone_id."\033[0m \n";
					echo "\033[1;33m Time:\033[0m                \033[1;32m".$row['time']."\033[0m \n";
					echo "\033[1;33m Temperature:\033[0m         \033[1;32m".$temperature."\033[0m \n";
					echo "\033[1;33m Minutes:\033[0m             \033[1;32m".$minute."\033[0m \n";
					//call out to PiConnect with data 
					$url=$api_url."?api=${pihome_api}&ip=${my_ip}&data=${data}&table=boost&id=${id}&purge=${purge}&status=${status}&zone_id=${zone_id}&time=${time}&temperature=${temperature}&time=${time}&minute=${minute}";
					$result = url_get_contents($url);
					//echo $url."\n";
					echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Result from PiConnect: \033[1;32m".$result."\033[0m \n";
					if ($result == 'Success'){
						$query = "UPDATE boost SET sync = '1' WHERE id ='{$id}' LIMIT 1;";
						$conn->query($query);
						echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Boost sync status updated in local database.\n";
					}elseif($result == 'Failed'){
						echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Result from PiConnect: \033[31m".$result."\033[0m \n";
					}
					echo $line;
				}
			} else {
				echo $line;
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - No Boost Data to Push to PiConnect \n";
				//start pulling boost table with PiConnect.
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Checking Boost Data Pull from PiConnect \n";	
				$data='pull';
				$url=$api_url."?api=${pihome_api}&ip=${my_ip}&data=${data}&table=boost&id=0";
				//echo $url."\n";
				$resulta = url_get_contents($url);
				if ($resulta != 'no-data'){
					// Convert JSON string to Array
					$jasonarray = json_decode($resulta, true);
					foreach ($jasonarray as $key => $value) {
						$id = $value["id"];
						//$purge = $value["purge"];
						$status = $value["status"];
						$name = $value["name"];
						$zone_id = $value["zone_id"];
						$time = $value["time"];
						$temperature = $value["temperature"];
						$minute = $value["minute"];
						$boost_button_id = $value["boost_button_id"];
						$boost_button_child_id = $value["boost_button_child_id"];
						echo "\033[1;33m Data Comm:\033[0m           \033[1;32m".$data."\033[0m \n";
						echo "\033[1;33m Table ID:\033[0m            \033[1;32m".$id."\033[0m \n";
						//echo "\033[1;33m Purge:\033[0m             \033[1;32m".$purge."\033[0m \n";
						echo "\033[1;33m Status:\033[0m              \033[1;32m".$status."\033[0m \n";
						echo "\033[1;33m Zone ID:\033[0m             \033[1;32m".$zone_id."\033[0m \n";
						echo "\033[1;33m Zone Name:\033[0m           \033[1;32m".$name."\033[0m \n";
						echo "\033[1;33m Time:\033[0m                \033[1;32m".$time."\033[0m \n";
						echo "\033[1;33m Temperature:\033[0m         \033[1;32m".$temperature."\033[0m \n";
						echo "\033[1;33m Boost Minute:\033[0m        \033[1;32m".$minute."\033[0m \n";
						echo "\033[1;33m Boost Button ID:\033[0m     \033[1;32m".$boost_button_id."\033[0m \n";
						echo "\033[1;33m Boost Button Child ID:\033[0m \033[1;32m".$boost_button_child_id."\033[0m \n";
						//check if Data alarady exist if do then update existing Data.
						$query = "SELECT * FROM boost where id = '{$id}';";
						$result = $conn->query($query);
						if (mysqli_num_rows($result) == 1){
							$query = "UPDATE boost SET sync = '1',  status = '{$status}', time = '{$time}', temperature = '{$temperature}', minute = '{$minute}', boost_button_id = '{$boost_button_id}', boost_button_child_id = '{$boost_button_child_id}' WHERE id ='{$id}';";
							$conn->query($query);
							echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Boost Data Updated in Local Database.\n";
							echo $line;
						}else{
							//Boost data does not exist add it
						}
					}
				}else{
					echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Boost Data From PiConnect: \033[1;32m".$resulta."\033[0m \n";
				}
			}
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Boost Data Sync Finished. \n";
			//Boost sync end here 
			/*****************************************************************************************************************************************************/
			//start syncing Night Climate Time table with PiConnect. 
			echo $line;
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Checking Night Climate Time Data to Push to PiConnect \n";
			$query = "SELECT * FROM schedule_night_climate_time where sync = 0 order by id asc;";
			$results = $conn->query($query);
			if (mysqli_num_rows($results) != 0){
				echo $line;
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Night Climate Time Data to sync with PiConnect: \033[32m". mysqli_num_rows($results)."\033[0m\n"; 
				while ($row = mysqli_fetch_assoc($results)) {
					$data='push';
					$id=$row['id'];
					$purge=$row['purge'];
					$status=$row['status'];
					$start_time=rawurlencode($row['start_time']);
					$end_time=rawurlencode($row['end_time']);
					//echo row data to console 
					echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Data to Sync with PiConnect: \n";
					echo "\033[1;33m Data Comm:\033[0m            \033[1;32m".$data."\033[0m \n";
					echo "\033[1;33m Table ID:\033[0m             \033[1;32m".$id."\033[0m \n";
					echo "\033[1;33m Purge:\033[0m                \033[1;32m".$purge."\033[0m \n";
					echo "\033[1;33m Status:\033[0m               \033[1;32m".$status."\033[0m \n";
					echo "\033[1;33m Start Time:\033[0m           \033[1;32m".$row['start_time']."\033[0m \n";
					echo "\033[1;33m End Time:\033[0m             \033[1;32m".$row['end_time']."\033[0m \n";
					//call out to PiConnect with data 
					$url=$api_url."?api=${pihome_api}&ip=${my_ip}&data=${data}&table=schedule_night_climate_time&id=${id}&purge=${purge}&status=${status}&start_time=${start_time}&end_time=${end_time}";
					$result = url_get_contents($url);
					//echo $url."\n";
					echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Result from PiConnect: \033[1;32m".$result."\033[0m \n";
					if ($result == 'Success'){
						$query = "UPDATE schedule_night_climate_time SET sync = '1' WHERE id ='{$id}' LIMIT 1;";
						$conn->query($query);
						echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Night Climate Time sync status updated in local database.\n";
					}elseif($result == 'Failed'){
						echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Result from PiConnect: \033[31m".$result."\033[0m \n";
					}
					echo $line;
				}
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Night Climate Time data Push Finished. \n";
			} else {
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - No Night Climate Time Data to Push to PiConnect \n";
				echo $line;
				//Start Pulling Night Climate Time Data from PiConnect.
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Checking Night Climate Time Data to Pull from PiConnect \n";
				$data='pull';
				$url=$api_url."?api=${pihome_api}&ip=${my_ip}&data=${data}&table=schedule_night_climate_time&id=0";
				//echo $url."\n";
				$resulta = url_get_contents($url);
				if ($resulta != 'no-data'){
					// Convert JSON string to Array
					$jasonarray = json_decode($resulta, true);
					foreach ($jasonarray as $key => $value) {
						echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Night Climate Time Data from PiConnect: \n";
						$id=$value["id"];
						$purge=$value['purge'];
						$status=$value["status"];
						$start_time=$value['start_time'];
						$end_time=$value['end_time'];
						echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Data to Sync with PiConnect: \n";
						echo "\033[1;33m Data Comm:\033[0m            \033[1;32m".$data."\033[0m \n";
						echo "\033[1;33m Table ID:\033[0m             \033[1;32m".$id."\033[0m \n";
						echo "\033[1;33m Purge:\033[0m                \033[1;32m".$purge."\033[0m \n";
						echo "\033[1;33m Status:\033[0m               \033[1;32m".$status."\033[0m \n";
						echo "\033[1;33m Start Time:\033[0m           \033[1;32m".$start_time."\033[0m \n";
						echo "\033[1;33m End Time:\033[0m             \033[1;32m".$end_time."\033[0m \n";
						//search for any exiting record with same rt_id and api_id
						$query = "SELECT * FROM schedule_night_climate_time where id = '{$id}';";
						$result = $conn->query($query);
						if (mysqli_num_rows($result) == 1){
							$query = "UPDATE schedule_night_climate_time SET sync = '1', status = '{$status}', start_time = '{$start_time}', end_time = '{$end_time}' where id = '{$id}';";
							$results = $conn->query($query);
							echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Night Climate Time Data Updated in Local Database \n";
						}else {
							//Inset into schedule_daily_time table 
							$query = "INSERT INTO schedule_night_climate_time (sync, status, start_time, end_time) VALUES ('1', '{$status}', '{$start_time}', '{$end_time}') where id = '{$id}';";
							$results = $conn->query($query);
							echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Night Climate Time Data Pull from PiConnect Finished. \n";
							echo $line;
						}
					}
				}else{
					echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Night Climate Schedule Time Data to Pull From PiConnect: \033[1;32m".$resulta."\033[0m \n";
				}
			}
			//Schedul Night Climate Time sync end here 
			/*****************************************************************************************************************************************************/
			//start syncing Schedul Night Climate Zone table with PiConnect. 
			$query = "SELECT * FROM schedule_night_climat_zone where sync = 0 order by id asc;";
			$results = $conn->query($query);
			if (mysqli_num_rows($results) != 0){
				echo $line;
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Schedul Night Climate Zone Data to sync with PiConnect: \033[32m". mysqli_num_rows($results)."\033[0m\n"; 
				while ($row = mysqli_fetch_assoc($results)) {
					$data='push';
					$id=$row['id'];
					$purge=$row['purge'];
					$status=$row['status'];
					$zone_id=$row['zone_id'];
					$schedule_night_climate_id=$row['schedule_night_climate_id'];
					$min_temperature=$row['min_temperature'];
					$max_temperature=$row['max_temperature'];
					//echo row data to console 
					echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Data to Sync with PiConnect: \n";
					echo "\033[1;33m Data Comm:\033[0m            \033[1;32m".$data."\033[0m \n";
					echo "\033[1;33m Table ID:\033[0m             \033[1;32m".$id."\033[0m \n";
					echo "\033[1;33m Purge:\033[0m                \033[1;32m".$purge."\033[0m \n";
					echo "\033[1;33m Status:\033[0m               \033[1;32m".$status."\033[0m \n";
					echo "\033[1;33m Zone ID:\033[0m              \033[1;32m".$zone_id."\033[0m \n";
					echo "\033[1;33m Night Schedule ID:\033[0m    \033[1;32m".$schedule_night_climate_id."\033[0m \n";
					echo "\033[1;33m Min Temperature:\033[0m      \033[1;32m".$min_temperature."\033[0m \n";
					echo "\033[1;33m Max Temperature:\033[0m      \033[1;32m".$max_temperature."\033[0m \n";
					//call out to PiConnect with data 
					$url=$api_url."?api=${pihome_api}&ip=${my_ip}&data=${data}&table=schedule_night_climat_zone&id=${id}&purge=${purge}&status=${status}&zone_id=${zone_id}&schedule_night_climate_id=${schedule_night_climate_id}&min_temperature=${min_temperature}&max_temperature=${max_temperature}";
					$result = url_get_contents($url);
					//echo $url."\n";
					echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Result from PiConnect: \033[1;32m".$result."\033[0m \n";
					if ($result == 'Success'){
						$query = "UPDATE schedule_night_climat_zone SET sync = '1' WHERE id ='{$id}' LIMIT 1;";
						$conn->query($query);
						echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Schedul Night Climate Zone sync status updated in local database.\n";
					}elseif($result == 'Failed'){
						echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Result from PiConnect: \033[31m".$result."\033[0m \n";
					}
					echo $line;
				}
			} else {
				echo $line;
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - No Schedul Night Climate Zone Data to sync with PiConnect \n";
				$data='pull';
				$url=$api_url."?api=${pihome_api}&ip=${my_ip}&data=${data}&table=schedule_night_climat_zone&id=0";
				//echo $url."\n";
				$resulta = url_get_contents($url);
				if ($resulta != 'no-data'){
					// Convert JSON string to Array
					$jasonarray = json_decode($resulta, true);
					foreach ($jasonarray as $key => $value) {
						$id = $value["id"];
						$purge = $value["purge"];
						$status = $value["status"];
						$zone_id = $value["zone_id"];
						$schedule_night_climate_id = $value["schedule_night_climate_id"];
						$min_temperature = $value["min_temperature"];
						$max_temperature = $value["max_temperature"];
						echo "\033[1;33m Data Comm:\033[0m                 \033[1;32m".$data."\033[0m \n";
						echo "\033[1;33m Table ID:\033[0m                  \033[1;32m".$id."\033[0m \n";
						echo "\033[1;33m Status:\033[0m                    \033[1;32m".$status."\033[0m \n";
						echo "\033[1;33m Zone ID:\033[0m                   \033[1;32m".$zone_id."\033[0m \n";
						echo "\033[1;33m Schedule Night Climate ID:\033[0m \033[1;32m".$schedule_night_climate_id."\033[0m \n";
						echo "\033[1;33m Minimum Temperature:\033[0m       \033[1;32m".$min_temperature."\033[0m \n";
						echo "\033[1;33m Maximum Temperature:\033[0m       \033[1;32m".$max_temperature."\033[0m \n";
						$query = "UPDATE schedule_night_climat_zone SET sync = '1',  status = '{$status}', schedule_night_climate_id = '{$schedule_night_climate_id}', zone_id = '{$zone_id}', min_temperature = '{$min_temperature}', max_temperature = '{$max_temperature}' WHERE id ='{$id}' LIMIT 1;";
						$conn->query($query);
						echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Schedul Night Climate Zone Data Updated in Local Database.\n";
						echo $line;
					}
				}else {
					echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Schedul Night Climate Zone Data from PiConnect: \033[1;32m".$resulta."\033[0m \n";
				}
			}
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Schedul Night Climate Zone data sync finished. \n";
			//Schedul Night Climate Zone sync end here 
			/*****************************************************************************************************************************************************/
			//start syncing messages_in (temperature) readings with PiConnect. Do not Sync Data older then 24 hours as these will be discarded. 
			$query = "SELECT * FROM messages_in WHERE sync = 0 AND datetime > DATE_SUB(NOW(), INTERVAL 24 HOUR);";
			$results = $conn->query($query);
			if (mysqli_num_rows($results) != 0){
				echo $line;
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Temperature Data to sync with PiConnect: \033[32m". mysqli_num_rows($results)."\033[0m\n"; 
				while ($row = mysqli_fetch_assoc($results)) {
					$data='push';
					$id=$row['id'];
					$purge=$row['purge'];
					$node_id=$row['node_id'];
					$child_id=$row['child_id'];
					$sub_type=$row['sub_type'];
					$payload=$row['payload'];
					$datetime=rawurlencode($row['datetime']);
					//echo row data to console 
					echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Data to Sync with PiConnect: \n";
					echo "\033[1;33m Data Comm:\033[0m           \033[1;32m".$data."\033[0m \n";
					echo "\033[1;33m Table ID:\033[0m            \033[1;32m".$id."\033[0m \n";
					echo "\033[1;33m Node ID:\033[0m             \033[1;32m".$node_id."\033[0m \n";
					echo "\033[1;33m Child ID:\033[0m            \033[1;32m".$child_id."\033[0m \n";
					echo "\033[1;33m Sub Type:\033[0m            \033[1;32m".$sub_type."\033[0m \n";
					echo "\033[1;33m PayLoad:\033[0m             \033[1;32m".$payload."\033[0m \n";
					echo "\033[1;33m Date & Time:\033[0m         \033[1;32m".$row['datetime']."\033[0m \n";
					//call out to PiConnect with data 
					$url=$api_url."?api=${pihome_api}&ip=${my_ip}&data=${data}&table=messages_in&id=${id}&purge=${purge}&node_id=${node_id}&child_id=${child_id}&sub_type=${sub_type}&payload=${payload}&datetime=${datetime}";
					$result = url_get_contents($url);
					echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Result from PiConnect: \033[1;32m".$result."\033[0m \n";
					if ($result == 'Success'){
						$query = "UPDATE messages_in SET sync = '1' WHERE id ='{$id}' LIMIT 1;";
						$conn->query($query);
						echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Temperature sync status updated in local database.\n";
					}elseif($result == 'Failed'){
						echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Result from PiConnect: \033[31m".$result."\033[0m \n";
					}
					echo $line;
				}
			} else {
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - No Temperature Data to sync with PiConnect. \n";
			}
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Temperature Data Sync Finished. \n";
			//messages_in sync end here 
			/*****************************************************************************************************************************************************/
			//Start syncing Weather with PiConnect.
			$query = "SELECT * FROM weather WHERE sync = 0;";
			$results = $conn->query($query);
			if (mysqli_num_rows($results) != 0){
				echo $line;
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Weather Data to Sync with PiConnect: \033[32m". mysqli_num_rows($results)."\033[0m\n"; 
				while ($row = mysqli_fetch_assoc($results)) {
					$data='push';
					$id=$row['id'];
					$location=rawurlencode($row['location']);
					$c=rawurlencode($row['c']);
					$wind_speed=rawurlencode($row['wind_speed']);
					$title=rawurlencode($row['title']);
					$description=rawurlencode($row['description']);
					$sunrise=$row['sunrise'];
					$sunset=$row['sunset'];
					$img=$row['img'];
					$last_update=rawurlencode($row['last_update']);
					//echo row data to console 
					echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Data to Sync with PiConnect: \n";
					echo "\033[1;33m Data Comm:\033[0m           \033[1;32m".$data."\033[0m \n";
					echo "\033[1;33m Table ID:\033[0m            \033[1;32m".$id."\033[0m \n";
					echo "\033[1;33m Location:\033[0m            \033[1;32m".$row['location']."\033[0m \n";
					echo "\033[1;33m Temperature:\033[0m         \033[1;32m".$c."\033[0m \n";
					echo "\033[1;33m Wind Speed:\033[0m          \033[1;32m".$row['wind_speed']."\033[0m \n";
					echo "\033[1;33m Title:\033[0m               \033[1;32m".$row['title']."\033[0m \n";
					echo "\033[1;33m Description:\033[0m         \033[1;32m".$row['description']."\033[0m \n";
					echo "\033[1;33m Sunrise:\033[0m             \033[1;32m".$sunrise."\033[0m \n";
					echo "\033[1;33m Sunset:\033[0m              \033[1;32m".$sunset."\033[0m \n";
					echo "\033[1;33m img:\033[0m                 \033[1;32m".$img."\033[0m \n";
					echo "\033[1;33m Date & Time:\033[0m         \033[1;32m".$row['last_update']."\033[0m \n";
					//call out to PiConnect with data 
					$url=$api_url."?api=${pihome_api}&ip=${my_ip}&data=${data}&table=weather&id=${id}&location=${location}&c=${c}&wind_speed=${wind_speed}&title=${title}&description=${description}&sunrise=${sunrise}&sunset=${sunset}&img=${img}&last_update=${last_update}";
					$result = url_get_contents($url);
					echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Result from PiConnect: \033[1;32m".$result."\033[0m \n";
					if ($result == 'Success'){
						$query = "UPDATE weather SET sync = '1' WHERE id ='{$id}' LIMIT 1;";
						$conn->query($query);
						echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Weather sync Status Updated in Local Database.\n";
					}elseif($result == 'Failed'){
						echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Result from PiConnect: \033[31m".$result."\033[0m \n";
					}
					echo $line;
				}
			} else {
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - No Weather Data to Sync With PiConnect \n";
			}
			//Weather Data sync End Here 
		//api key validation else option
		}else{
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Your API Key isnt valid please contact PiConnect at http://www.pihome.eu \n";
		}
	} else {
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - PiConnect is \033[41mOffline\Inaccessible !!!\033[0m \n";
	}
//Display Message PiConnect is disabled 
}else {
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - PiConnect is \033[41m Disabled !!!\033[0m You to Enable PiConnect from Settings.\n";
	echo $line;
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Delete all Records Marked for Purge to do Keep Everyting Clean.\n";
	//Delete Boost Records
	$query = "DELETE FROM boost WHERE `purge`= '1' LIMIT 1;";
	$conn->query($query);
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Boost Record Purged in Local Database \n";
	//Delete Override records
	$query = "DELETE FROM override WHERE `purge`= '1'  LIMIT 1;";
	$conn->query($query);
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Override Record Purged in Local Database \n";
	//Delete Daily Time records
	$query = "DELETE FROM schedule_daily_time_zone WHERE `purge`= '1';";
	$conn->query($query);
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Schedule Time Record Purged in Local Database \n";
	//Delete Night Climat records
	$query = "DELETE FROM schedule_night_climat_zone WHERE `purge`= '1';";
	$conn->query($query);
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Schedule Night Cliate Zone Record Purged in Local Database \n";
	//Delete All Zone Logs records
	$query = "DELETE FROM zone_logs WHERE `purge`= '1';";
	$conn->query($query);
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone Logs Record Purged in Local Database \n";
	//Delete Zone record
	$query = "DELETE FROM zone WHERE `purge`= '1' LIMIT 1;";
	$conn->query($query);
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone Record Purged in Local Database \n";
	//Delete Schedul daily time zone 
	$query = "DELETE FROM schedule_daily_time_zone WHERE `purge`= '1';";
	$conn->query($query);
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Schedul Time Zone Purged in Local Database \n";
	//Delete schedule dialy time 
	$query = "DELETE FROM schedule_daily_time WHERE `purge`= '1';";
	$conn->query($query);
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Schedul Time Purged in Local Database \n";
	echo $line;
}
echo "\n"; 
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - PiConnect Script Ended \n"; 
echo "\033[32m******************************************************************\033[0m  \n";
if(isset($conn)) { $conn->close();}