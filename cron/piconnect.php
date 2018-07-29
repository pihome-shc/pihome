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
echo "*   PiConnect Script Version 0.1 Build Date 16/04/2018      *\n";
echo "*   Update on 16/07/218                                     *\n";
echo "*                                      Have Fun - PiHome.eu *\n";
echo "*************************************************************\n";
echo " \033[0m \n";

require_once(__DIR__.'../../st_inc/connection.php');
require_once(__DIR__.'../../st_inc/functions.php');
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - PiHome PiConnect Script Started \n";

//api url to call 
$api_url = "http://www.pihome.eu/piconnect/";
$my_ip = file_get_contents('http://www.pihome.eu/piconnect/myip.php');

//Set php script execution time in seconds
ini_set('max_execution_time', 40); 
$date_time = date('Y-m-d H:i:s');
$line = "------------------------------------------------------------------\n";

//get api key from database 
$pihome_api = settings($conn, 'pihome_api');

$url=$api_url."mypihome.php?check_api=${pihome_api}&ip=${my_ip}";
//echo $url."\n";
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - PiHome API key: \033[1;33m".$pihome_api."\033[0m \n";
$api_result = url_get_contents($url);

//check if API is valid then execute code to sync data from local tables 
if ($api_result == "OK"){
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - You have valid API for PiHome \n";

	//start syncing away table with PiHome. 
	$query = "SELECT * FROM away where sync = 0 order by id asc;";
	$results = $conn->query($query);
	if (mysqli_num_rows($results) != 0){
		echo $line;
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Away Data to Sync with PiHome: \033[32m". mysqli_num_rows($results)."\033[0m\n"; 
		while ($row = mysqli_fetch_assoc($results)) {
			$data='push';
			$id=$row['id'];
			$purge=$row['purge'];
			$status=$row['status'];
			$start_datetime=rawurlencode($row['start_datetime']);
			//echo row data to console 
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Table details to sync with PiHome: \n";
			echo "\033[1;33m Data Comm:\033[0m            \033[1;32m".$data."\033[0m \n";
			echo "\033[1;33m Table ID:\033[0m             \033[1;32m".$id."\033[0m \n";
			echo "\033[1;33m Status:\033[0m               \033[1;32m".$status."\033[0m \n";
			echo "\033[1;33m Date & Time:\033[0m          \033[1;32m".$row['start_datetime']."\033[0m \n";
			//call out to PiHome with data 
			$url=$api_url."mypihome.php?api=${pihome_api}&ip=${my_ip}&data=push&table=away&id=${id}&purge=${purge}&status=${status}&start_datetime=${start_datetime}";
			$result = url_get_contents($url);
			//echo $url."\n";
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Result from PiHome: \033[1;32m".$result."\033[0m \n";
			if ($result == 'Success'){
				$query = "UPDATE away SET sync = '1' WHERE id ='{$id}' LIMIT 1;";
				$conn->query($query);
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Away Status Data Updated in Local Database.\n";
			}elseif($result == 'Failed'){
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Result from PiHome: \033[31m".$result."\033[0m \n";
			}
			echo $line;
		}
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - No Away Status Data to Push to PiHome \n";
	} else {
		echo $line;
		
		//start getting away table with PiHome.
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Checking Away Status Data to Pull from PiHome \n";	
		$data='pull';
		$url=$api_url."mypihome.php?api=${pihome_api}&ip=${my_ip}&data=${data}&table=away&id=0";
		//echo $url."\n";
		$resulta = url_get_contents($url);
		if ($resulta != 'no-data'){
			// Convert JSON string to Array
			$jasonarray = json_decode($resulta, true);
			foreach ($jasonarray as $key => $value) {
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Away Status Data  from PiHome: \n";
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
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Away Status Data Pull from PiHome finished. \n";
				echo $line;
			}
		}else{
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Away Status Data From PiHome: \033[1;32m".$resulta."\033[0m \n";
		}
	}
	//Away sync end here 
/*****************************************************************************************************************************************************/	

	//start syncing nodes table with PiHome. 
	$query = "SELECT * FROM nodes where sync = 0 order by id asc;";
	$results = $conn->query($query);
	if (mysqli_num_rows($results) != 0){
		echo $line;
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Total Nodes Data to sync with PiHome: \033[32m". mysqli_num_rows($results)."\033[0m\n"; 
		while ($row = mysqli_fetch_assoc($results)) {
			$data='push';
			$id=$row['id'];
			$purge=$row['purge'];
			$node_id=$row['node_id'];
			$child_id_1=$row['child_id_1'];
			$name=rawurlencode($row['name']);
			$last_seen=rawurlencode($row['last_seen']);
			//echo row data to console 
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Table details to sync with PiHome: \n";
			echo "\033[1;33m Data Comm:\033[0m          \033[1;32m".$data."\033[0m \n";
			echo "\033[1;33m Table ID:\033[0m           \033[1;32m".$id."\033[0m \n";
			echo "\033[1;33m Purge:\033[0m              \033[1;32m".$purge."\033[0m \n";
			echo "\033[1;33m Node ID:\033[0m            \033[1;32m".$node_id."\033[0m \n";
			echo "\033[1;33m Child ID:\033[0m           \033[1;32m".$child_id_1."\033[0m \n";
			echo "\033[1;33m Name:\033[0m               \033[1;32m".$row['name']."\033[0m \n";
			echo "\033[1;33m Last Seen:\033[0m          \033[1;32m".$row['last_seen']."\033[0m \n";
			//call out to PiHome with data 
			$url=$api_url."mypihome.php?api=${pihome_api}&ip=${my_ip}&data=${data}&table=nodes&id=${id}&purge=${purge}&node_id=${node_id}&child_id_1=${child_id_1}&name=${name}&last_seen=${last_seen}";
			$result = url_get_contents($url);
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Result from PiHome: \033[1;32m".$result."\033[0m \n";
			if ($result == 'Success'){
				$query = "UPDATE nodes SET sync = '1' WHERE id ='{$id}' LIMIT 1;";
				$conn->query($query);
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Nodes sync status updated in local database.\n";
			}elseif($result == 'Failed'){
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Result from PiHome: \033[31m".$result."\033[0m \n";
			}
			echo $line;
		}	
	} else {
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - No Nodes Data to sync with PiHome \n";
	}
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Nodes Data Sync Finished. \n";
	//Nodes sync end here 
/*****************************************************************************************************************************************************/

	//start syncing boiler table with PiHome. 
	$query = "SELECT * FROM boiler where sync = 0 order by id asc;";
	$results = $conn->query($query);
	if (mysqli_num_rows($results) != 0){
		echo $line;
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Total Boiler Data to sync with PiHome: \033[32m". mysqli_num_rows($results)."\033[0m\n"; 
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
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Table details to sync with PiHome: \n";
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
			//call out to PiHome with data 
			$url=$api_url."mypihome.php?api=${pihome_api}&ip=${my_ip}&data=${data}&table=boiler&id=${id}&purge=${purge}&status=${status}&fired_status=${fired_status}&name=${name}&node_id=${node_id}&node_child_id=${node_child_id}&hysteresis_time=${hysteresis_time}&max_operation_time=${max_operation_time}&datetime=${datetime}&gpio_pin=${gpio_pin}";
			$result = url_get_contents($url);
			//echo $url."\n";
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Result from PiHome: \033[1;32m".$result."\033[0m \n";
			if ($result == 'Success'){
				$query = "UPDATE boiler SET sync = '1' WHERE id ='{$id}' LIMIT 1;";
				$conn->query($query);
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Boiler Sync Status Updated in Local Database.\n";
			}elseif($result == 'Failed'){
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Result From PiHome: \033[31m".$result."\033[0m \n";
			}
			echo $line;
		}		
	} else {
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - No Boiler Data to Sync with PiHome \n";
	}
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Boiler Data Sync Finished. \n";
	//Boiler sync end here 

/*****************************************************************************************************************************************************/
	//start syncing boiler Logs table with PiHome. 
	$query = "SELECT * FROM boiler_logs where sync = 0 AND stop_datetime IS NOT NULL order by id asc;";
	$results = $conn->query($query);
	if (mysqli_num_rows($results) != 0){
		echo $line;
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Boiler Logs Data to Sync with PiHome: \033[32m". mysqli_num_rows($results)."\033[0m\n"; 
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
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Table details to sync with PiHome: \n";
			echo "\033[1;33m Data Comm:\033[0m           \033[1;32m".$data."\033[0m \n";
			echo "\033[1;33m Table ID:\033[0m            \033[1;32m".$id."\033[0m \n";
			echo "\033[1;33m Start DateTime:\033[0m      \033[1;32m".$row['start_datetime']."\033[0m \n";
			echo "\033[1;33m Start Cause:\033[0m         \033[1;32m".$row['start_cause']."\033[0m \n";
			echo "\033[1;33m Stop DateTime:\033[0m       \033[1;32m".$row['stop_datetime']."\033[0m \n";
			echo "\033[1;33m Stop Cause:\033[0m          \033[1;32m".$row['stop_cause']."\033[0m \n";
			echo "\033[1;33m Expected End Time:\033[0m   \033[1;32m".$row['expected_end_date_time']."\033[0m \n";
			
			//call out to PiHome with data 
			$url=$api_url."mypihome.php?api=${pihome_api}&ip=${my_ip}&data=${data}&table=boiler_logs&id=${id}&purge=${purge}&start_datetime=${start_datetime}&start_cause=${start_cause}&stop_datetime=${stop_datetime}&stop_cause=${stop_cause}&expected_end_date_time=${expected_end_date_time}";
			$result = url_get_contents($url);
			//echo $url."\n";
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Result from PiHome: \033[1;32m".$result."\033[0m \n";
			if ($result == 'Success'){
				$query = "UPDATE boiler_logs SET sync = '1' WHERE id ='{$id}' LIMIT 1;";
				$conn->query($query);
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Boiler Logs sync status updated in local database.\n";
			}elseif($result == 'Failed'){
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Result from PiHome: \033[31m".$result."\033[0m \n";
			}
			echo $line;
		}
	} else {
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - No Boiler Logs Data to Sync with PiHome \n";
	}
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Boiler Logs data sync finished. \n";
	//Boiler Logs sync end here 
/*****************************************************************************************************************************************************/

	//start syncing zone table with PiHome. 
	$query = "SELECT * FROM zone where sync = 0 order by id asc;";
	$results = $conn->query($query);
	if (mysqli_num_rows($results) != 0){
		echo $line;
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Total Zone Data to Sync with PiHome: \033[32m". mysqli_num_rows($results)."\033[0m\n"; 
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
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Table details to sync with PiHome: \n";
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
			//call out to PiHome with data 
			$url=$api_url."mypihome.php?api=${pihome_api}&ip=${my_ip}&data=${data}&table=zone&id=${id}&purge=${purge}&status=${status}&index_id=${index_id}&name=${name}&type=${type}&max_c=${max_c}&max_operation_time=${max_operation_time}&hysteresis_time=${hysteresis_time}&sensor_id=${sensor_id}&sensor_child_id=${sensor_child_id}&controler_id=${controler_id}&controler_child_id=${controler_child_id}&boiler_id=${boiler_id}&gpio_pin=${gpio_pin}";
			//echo $url."\n"; 
			$result = url_get_contents($url);
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Result from PiHome: \033[1;32m".$result."\033[0m \n";
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
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Result from PiHome: \033[31m".$result."\033[0m \n";
			}
				
			echo $line;
		}
	} else {
		echo $line;
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - No Zone Data to Sync with PiHome \n";
	}
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone data sync finished. \n";
	//Zone sync end here 
/*****************************************************************************************************************************************************/

	//start syncing Zone Logs table with PiHome. 
	$query = "SELECT * FROM zone_logs where sync = 0 order by id asc;";
	$results = $conn->query($query);
	$row = mysqli_fetch_array($results);
	$boiler_log_id = $row['boiler_log_id'];
	
	//check if boiler log is synced
	//$query = "SELECT * FROM boiler_logs where sync = 0 AND id = '{$boiler_log_id}' AND stop_datetime IS NOT NULL order by id asc;";
	$query = "SELECT * FROM boiler_logs where sync = 1 AND id = '{$boiler_log_id}';";
	$result = $conn->query($query);
 		
	
	if (mysqli_num_rows($results) != 0){
		echo $line;
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone Logs Data to Sync with PiHome: \033[32m". mysqli_num_rows($results)."\033[0m\n"; 
		while ($row = mysqli_fetch_assoc($results)) {
			$data='push';
			$id=$row['id'];
			$purge=$row['purge'];
			$zone_id=$row['zone_id'];
			$boiler_log_id=$row['boiler_log_id'];
			$status=$row['status'];
			
			//echo row data to console 
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Table details to sync with PiHome: \n";
			echo "\033[1;33m Data Comm:\033[0m           \033[1;32m".$data."\033[0m \n";
			echo "\033[1;33m Table ID:\033[0m            \033[1;32m".$id."\033[0m \n";
			echo "\033[1;33m Purge:\033[0m               \033[1;32m".$purge."\033[0m \n";
			echo "\033[1;33m Zone ID:\033[0m             \033[1;32m".$zone_id."\033[0m \n";
			echo "\033[1;33m Boiler Log ID:\033[0m       \033[1;32m".$boiler_log_id."\033[0m \n";
			echo "\033[1;33m Status:\033[0m              \033[1;32m".$status."\033[0m \n";
			//call out to PiHome with data 
			$url=$api_url."mypihome.php?api=${pihome_api}&ip=${my_ip}&data=${data}&table=zone_logs&id=${id}&purge=${purge}&zone_id=${zone_id}&boiler_log_id=${boiler_log_id}&status=${status}";
			$result = url_get_contents($url);
			//echo $url."\n";
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Result from PiHome: \033[1;32m".$result."\033[0m \n";
			if ($result == 'Success'){
				$query = "UPDATE zone_logs SET sync = '1' WHERE id ='{$id}' LIMIT 1;";
				$conn->query($query);
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone Logs sync status updated in local database.\n";
			}elseif($result == 'Failed'){
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Result from PiHome: \033[31m".$result."\033[0m \n";
			}
			echo $line;
		}
	} else {
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - No Zone Logs Data to Sync with PiHome. \n";
	}
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone Logs Data Sync Finished. \n";
	//Zone Logs sync end here 
/*****************************************************************************************************************************************************/
	
	//start syncing Schedul Time table with PiHome. 
	$query = "SELECT * FROM schedule_daily_time where sync = 0 order by id asc;";
	$results = $conn->query($query);
	//check if anything to sync with PiHome
	if (mysqli_num_rows($results) != 0){
		echo $line;
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Schedul Time Data to Sync with PiHome: \033[32m". mysqli_num_rows($results)."\033[0m\n"; 
		while ($row = mysqli_fetch_assoc($results)) {
			$data='push';
			$id=$row['id'];
			$purge=$row['purge'];
			$status=$row['status'];
			$start=rawurlencode($row['start']);
			$end=rawurlencode($row['end']);
			//echo row data to console 
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Table details to sync with PiHome: \n";
			echo "\033[1;33m Data Comm:\033[0m            \033[1;32m".$data."\033[0m \n";
			echo "\033[1;33m Table ID:\033[0m             \033[1;32m".$id."\033[0m \n";
			echo "\033[1;33m Purge:\033[0m                \033[1;32m".$purge."\033[0m \n";
			echo "\033[1;33m Status:\033[0m               \033[1;32m".$status."\033[0m \n";
			echo "\033[1;33m Start Time:\033[0m           \033[1;32m".$row['start']."\033[0m \n";
			echo "\033[1;33m End Time:\033[0m             \033[1;32m".$row['end']."\033[0m \n";
			//call out to PiHome with data 
			$url=$api_url."mypihome.php?api=${pihome_api}&ip=${my_ip}&data=${data}&table=schedule_daily_time&id=${id}&purge=${purge}&status=${status}&start=${start}&end=${end}";
			$result = url_get_contents($url);
			//echo $url."\n";
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Result from PiHome: \033[1;32m".$result."\033[0m \n";
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
		//Start Pull Request for Schedul Time From PiHome.
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Checking Schedul Time Data to Pull from PiHome \n";	
		$data='pull';
		$url=$api_url."mypihome.php?api=${pihome_api}&ip=${my_ip}&data=${data}&table=schedule_daily_time&id=0";
		//echo $url."\n";
		$resulta = url_get_contents($url);
		if ($resulta != 'no-data'){
			// Convert JSON string to Array
			$jasonarray = json_decode($resulta, true);
			foreach ($jasonarray as $key => $value) {
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Start Pulling Schedule Time Data From PiHome \n";
				$id = $value["id"];
				$purge = $value["purge"];
				$status = $value["status"];
				$start = $value["start"];
				$end = $value["end"];
				echo "\033[1;33m Data Comm:\033[0m            \033[1;32m".$data."\033[0m \n";
				echo "\033[1;33m Table ID:\033[0m             \033[1;32m".$id."\033[0m \n";
				echo "\033[1;33m Purge:\033[0m                \033[1;32m".$purge."\033[0m \n";
				echo "\033[1;33m Status:\033[0m               \033[1;32m".$status."\033[0m \n";
				echo "\033[1;33m Start Time:\033[0m           \033[1;32m".$start."\033[0m \n";
				echo "\033[1;33m End Time:\033[0m             \033[1;32m".$end."\033[0m \n";
				if ($purge == '1'){
					$query = "DELETE FROM schedule_daily_time_zone WHERE schedule_daily_time_id = '{$id}';";
					$conn->query($query);
					$query = "DELETE FROM schedule_daily_time WHERE id = '{$id}';";
					$conn->query($query);
					echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Schedul Time Sync Purged in Local Database \n";
				}elseif($purge == '0'){
					$query = "UPDATE schedule_daily_time SET sync = '1',  status = '{$status}', start = '{$start}', end = '{$end}' WHERE id ='{$id}' LIMIT 1;";
					$conn->query($query);
					echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Schedul Time Sync Updated in Local Database \n";
				}
				echo $line;
			}
		}else {
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Shedule Daily Time from PiHome: \033[1;32m".$resulta."\033[0m \n";
		}
	}
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Schedule Time Data Sync Finished. \n";
	//Schedul Time sync end here 
/*****************************************************************************************************************************************************/
	
	//start syncing Schedul Time Zone table with PiHome. 
	$query = "SELECT * FROM schedule_daily_time_zone where sync = 0 order by id asc;";
	$results = $conn->query($query);
	if (mysqli_num_rows($results) != 0){
		echo $line;
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Schedul Time Zone Data to sync with PiHome: \033[32m". mysqli_num_rows($results)."\033[0m\n"; 
		while ($row = mysqli_fetch_assoc($results)) {
			$data='push';
			$id=$row['id'];
			$purge=$row['purge'];
			$status=$row['status'];
			$schedule_daily_time_id=rawurlencode($row['schedule_daily_time_id']);
			$zone_id=$row['zone_id'];
			$temperature=$row['temperature'];
			//echo row data to console 
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Table details to sync with PiHome: \n";
			echo "\033[1;33m Data Comm:\033[0m            \033[1;32m".$data."\033[0m \n";
			echo "\033[1;33m Table ID:\033[0m             \033[1;32m".$id."\033[0m \n";
			echo "\033[1;33m Purge:\033[0m                \033[1;32m".$purge."\033[0m \n";
			echo "\033[1;33m Status:\033[0m               \033[1;32m".$status."\033[0m \n";
			echo "\033[1;33m Schedule Time ID:\033[0m     \033[1;32m".$row['schedule_daily_time_id']."\033[0m \n";
			echo "\033[1;33m Zone ID:\033[0m              \033[1;32m".$zone_id."\033[0m \n";
			echo "\033[1;33m Temperature:\033[0m          \033[1;32m".$temperature."\033[0m \n";
			//call out to PiHome with data 
			$url=$api_url."mypihome.php?api=${pihome_api}&ip=${my_ip}&data=${data}&table=schedule_daily_time_zone&id=${id}&purge=${purge}&status=${status}&schedule_daily_time_id=${schedule_daily_time_id}&zone_id=${zone_id}&temperature=${temperature}";
			$result = url_get_contents($url);
			//echo $url."\n";
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Result from PiHome \033[1;32m".$result."\033[0m \n";
			if ($result == 'Success'){
				$query = "UPDATE schedule_daily_time_zone SET sync = '1' WHERE id ='{$id}' LIMIT 1;";
				$conn->query($query);
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Schedul Time Zone sync status updated in local database.\n";
			}elseif($result == 'Failed'){
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Result from PiHome: \033[31m".$result."\033[0m \n";
			}
		echo $line;
		}
	} else {
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - No Schedule Time Zone Data to sync with PiHome \n";
		//Start Pull Request for Schedul Time From PiHome.
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Checking Schedule Time Zone Data to Pull from PiHome \n";	
		$data='pull';
		$url=$api_url."mypihome.php?api=${pihome_api}&ip=${my_ip}&data=${data}&table=schedule_daily_time_zone&id=0";
		//echo $url."\n";
		$resulta = url_get_contents($url);
		if ($resulta != 'no-data'){
			// Convert JSON string to Array
			$jasonarray = json_decode($resulta, true);
			foreach ($jasonarray as $key => $value) {
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Start Pulling Schedule Time Zone from PiHome \n";
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
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Shedule Daily Time Zone from PiHome: \033[1;32m".$resulta."\033[0m \n";
		}
	}
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Shedule Daily Time Zone Data Sync Finished. \n";
	//Schedul Time Zone sync end here 
/*****************************************************************************************************************************************************/
	
	//start syncing Override table with PiHome. 
	$query = "SELECT * FROM override where sync = 0 order by id asc;";
	$results = $conn->query($query);
	if (mysqli_num_rows($results) != 0){
		echo $line;
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Override Data to sync with PiHome : \033[32m". mysqli_num_rows($results)."\033[0m\n"; 
		while ($row = mysqli_fetch_assoc($results)) {
			$data='push';
			$id=$row['id'];
			$purge=$row['purge'];
			$status=$row['status'];
			$zone_id=$row['zone_id'];
			$time=rawurlencode($row['time']);
			$temperature=$row['temperature'];
			//echo row data to console 
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Table details to sync with PiHome: \n";
			echo "\033[1;33m Data Comm:\033[0m            \033[1;32m".$data."\033[0m \n";
			echo "\033[1;33m Table ID:\033[0m             \033[1;32m".$id."\033[0m \n";
			echo "\033[1;33m Purge:\033[0m                \033[1;32m".$purge."\033[0m \n";
			echo "\033[1;33m Status:\033[0m               \033[1;32m".$status."\033[0m \n";
			echo "\033[1;33m Zone ID:\033[0m              \033[1;32m".$zone_id."\033[0m \n";
			echo "\033[1;33m Time:\033[0m                 \033[1;32m".$row['time']."\033[0m \n";
			echo "\033[1;33m Temperature:\033[0m          \033[1;32m".$temperature."\033[0m \n";
			//call out to PiHome with data 
			$url=$api_url."mypihome.php?api=${pihome_api}&ip=${my_ip}&data=${data}&table=override&id=${id}&purge=${purge}&status=${status}&zone_id=${zone_id}&time=${time}&temperature=${temperature}";
			$result = url_get_contents($url);
			//echo $url."\n";
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Result from PiHome: \033[1;32m".$result."\033[0m \n";
			if ($result == 'Success'){
				$query = "UPDATE override SET sync = '1' WHERE id ='{$id}' LIMIT 1;";
				$conn->query($query);
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Override sync status updated in local database.\n";
			}elseif($result == 'Failed'){
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Result from PiHome: \033[31m".$result."\033[0m \n";
			}
			echo $line;
		}
	} else {
		echo $line;
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - No Override Data to Push to PiHome \n";
		//start pulling Override table with PiHome.
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Checking Override Data to Pull from PiHome \n";	
		$data='pull';
		$url=$api_url."mypihome.php?api=${pihome_api}&ip=${my_ip}&data=${data}&table=override&id=0";
		//echo $url."\n";
		$resulta = url_get_contents($url);
		if ($resulta != 'no-data'){
			// Convert JSON string to Array
			$jasonarray = json_decode($resulta, true);
			foreach ($jasonarray as $key => $value) {
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Override Data From PiHome. \n";
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
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Override Data From PiHome: \033[1;32m".$resulta."\033[0m \n";
		}
	}
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Override Data Sync Finished. \n";
	//Override sync end here 
/*****************************************************************************************************************************************************/

	//start syncing Boost table with PiHome. 
	$query = "SELECT * FROM boost where sync = 0 order by id asc;";
	$results = $conn->query($query);
	if (mysqli_num_rows($results) != 0){
		echo $line;
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Boost Data to sync with PiHome: \033[32m". mysqli_num_rows($results)."\033[0m\n"; 
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
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Table details to sync with PiHome: \n";
			echo "\033[1;33m Data Comm:\033[0m           \033[1;32m".$data."\033[0m \n";
			echo "\033[1;33m Table ID:\033[0m            \033[1;32m".$id."\033[0m \n";
			echo "\033[1;33m Purge:\033[0m               \033[1;32m".$purge."\033[0m \n";
			echo "\033[1;33m Status:\033[0m              \033[1;32m".$status."\033[0m \n";
			echo "\033[1;33m Zone ID:\033[0m             \033[1;32m".$zone_id."\033[0m \n";
			echo "\033[1;33m Time:\033[0m                \033[1;32m".$row['time']."\033[0m \n";
			echo "\033[1;33m Temperature:\033[0m         \033[1;32m".$temperature."\033[0m \n";
			//call out to PiHome with data 
			$url=$api_url."mypihome.php?api=${pihome_api}&ip=${my_ip}&data=${data}&table=boost&id=${id}&purge=${purge}&status=${status}&zone_id=${zone_id}&time=${time}&temperature=${temperature}&time=${time}";
			$result = url_get_contents($url);
			//echo $url."\n";
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Result from PiHome: \033[1;32m".$result."\033[0m \n";
			if ($result == 'Success'){
				$query = "UPDATE boost SET sync = '1' WHERE id ='{$id}' LIMIT 1;";
				$conn->query($query);
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Boost sync status updated in local database.\n";
			}elseif($result == 'Failed'){
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Result from PiHome: \033[31m".$result."\033[0m \n";
			}
			echo $line;
		}
	} else {
		echo $line;
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - No Boost Data to Push to PiHome \n";
		//start pulling boost table with PiHome.
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Checking Boost Data Pull from PiHome \n";	
		$data='pull';
		$url=$api_url."mypihome.php?api=${pihome_api}&ip=${my_ip}&data=${data}&table=boost&id=0";
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
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Boost Data From PiHome: \033[1;32m".$resulta."\033[0m \n";
		}
	}
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Boost Data Sync Finished. \n";
	//Boost sync end here 
/*****************************************************************************************************************************************************/

	//start syncing Schedul Night Climate Time table with PiHome. 
	$query = "SELECT * FROM schedule_night_climate_time where sync = 0 order by id asc;";
	$results = $conn->query($query);
	if (mysqli_num_rows($results) != 0){
		echo $line;
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Schedul Night Climate Time Data to sync with PiHome: \033[32m". mysqli_num_rows($results)."\033[0m\n"; 
		
	} else { 
		echo $line;
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - No Schedul Night Climate Time Data to sync with PiHome \n";
	}
	while ($row = mysqli_fetch_assoc($results)) {
		$data='push';
		$id=$row['id'];
		$purge=$row['purge'];
		$status=$row['status'];
		$start_time=rawurlencode($row['start_time']);
		$end_time=rawurlencode($row['end_time']);
		//echo row data to console 
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Table details to sync with PiHome: \n";
		echo "\033[1;33m Data Comm:\033[0m            \033[1;32m".$data."\033[0m \n";
		echo "\033[1;33m Table ID:\033[0m             \033[1;32m".$id."\033[0m \n";
		echo "\033[1;33m Purge:\033[0m                \033[1;32m".$purge."\033[0m \n";
		echo "\033[1;33m Status:\033[0m               \033[1;32m".$status."\033[0m \n";
		echo "\033[1;33m Start Time:\033[0m           \033[1;32m".$row['start_time']."\033[0m \n";
		echo "\033[1;33m End Time:\033[0m             \033[1;32m".$row['end_time']."\033[0m \n";
		//call out to PiHome with data 
		$url=$api_url."mypihome.php?api=${pihome_api}&ip=${my_ip}&data=${data}&table=schedule_night_climate_time&id=${id}&purge=${purge}&status=${status}&start_time=${start_time}&end_time=${end_time}";
		$result = url_get_contents($url);
		//echo $url."\n";
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Result from PiHome: \033[1;32m".$result."\033[0m \n";
		if ($result == 'Success'){
			$query = "UPDATE schedule_night_climate_time SET sync = '1' WHERE id ='{$id}' LIMIT 1;";
			$conn->query($query);
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Schedul Night Climate Time sync status updated in local database.\n";
		}elseif($result == 'Failed'){
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Result from PiHome: \033[31m".$result."\033[0m \n";
		}
		echo $line;
	}
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Schedul Night Climate Time data sync finished. \n";
	//Schedul Night Climate Time sync end here 

/*****************************************************************************************************************************************************/

	//start syncing Schedul Night Climate Zone table with PiHome. 
	$query = "SELECT * FROM schedule_night_climat_zone where sync = 0 order by id asc;";
	$results = $conn->query($query);
	if (mysqli_num_rows($results) != 0){
		echo $line;
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Schedul Night Climate Zone Data to sync with PiHome: \033[32m". mysqli_num_rows($results)."\033[0m\n"; 
		
	} else { 
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - No Schedul Night Climate Zone Data to sync with PiHome \n";
		echo $line;
	}
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
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Table details to sync with PiHome: \n";
		echo "\033[1;33m Data Comm:\033[0m            \033[1;32m".$data."\033[0m \n";
		echo "\033[1;33m Table ID:\033[0m             \033[1;32m".$id."\033[0m \n";
		echo "\033[1;33m Purge:\033[0m                \033[1;32m".$purge."\033[0m \n";
		echo "\033[1;33m Status:\033[0m               \033[1;32m".$status."\033[0m \n";
		echo "\033[1;33m Zone ID:\033[0m              \033[1;32m".$zone_id."\033[0m \n";
		echo "\033[1;33m Night Schedule ID:\033[0m    \033[1;32m".$schedule_night_climate_id."\033[0m \n";
		echo "\033[1;33m Min Temperature:\033[0m      \033[1;32m".$min_temperature."\033[0m \n";
		echo "\033[1;33m Max Temperature:\033[0m      \033[1;32m".$max_temperature."\033[0m \n";
		//call out to PiHome with data 
		$url=$api_url."mypihome.php?api=${pihome_api}&ip=${my_ip}&data=${data}&table=schedule_night_climat_zone&id=${id}&purge=${purge}&status=${status}&zone_id=${zone_id}&schedule_night_climate_id=${schedule_night_climate_id}&min_temperature=${min_temperature}&max_temperature=${max_temperature}";
		$result = url_get_contents($url);
		//echo $url."\n";
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Result from PiHome: \033[1;32m".$result."\033[0m \n";
		if ($result == 'Success'){
			$query = "UPDATE schedule_night_climat_zone SET sync = '1' WHERE id ='{$id}' LIMIT 1;";
			$conn->query($query);
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Schedul Night Climate Zone sync status updated in local database.\n";
		}elseif($result == 'Failed'){
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Result from PiHome: \033[31m".$result."\033[0m \n";
		}
		echo $line;
	}
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Schedul Night Climate Zone data sync finished. \n";
	//Schedul Night Climate Zone sync end here 
/*****************************************************************************************************************************************************/

	//start syncing messages_in (temperature) readings with PiHome. Do not Sync Data older then 24 hours as these will be discarded. 
	$query = "SELECT * FROM messages_in WHERE sync = 0 AND datetime > DATE_SUB(NOW(), INTERVAL 24 HOUR);";
	$results = $conn->query($query);
	if (mysqli_num_rows($results) != 0){
		echo $line;
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Temperature Data to sync with PiHome: \033[32m". mysqli_num_rows($results)."\033[0m\n"; 
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
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Table details to sync with PiHome: \n";
			echo "\033[1;33m Data Comm:\033[0m           \033[1;32m".$data."\033[0m \n";
			echo "\033[1;33m Table ID:\033[0m            \033[1;32m".$id."\033[0m \n";
			echo "\033[1;33m Node ID:\033[0m             \033[1;32m".$node_id."\033[0m \n";
			echo "\033[1;33m Child ID:\033[0m            \033[1;32m".$child_id."\033[0m \n";
			echo "\033[1;33m Sub Type:\033[0m            \033[1;32m".$sub_type."\033[0m \n";
			echo "\033[1;33m PayLoad:\033[0m             \033[1;32m".$payload."\033[0m \n";
			echo "\033[1;33m Date & Time:\033[0m         \033[1;32m".$row['datetime']."\033[0m \n";
			//call out to PiHome with data 
			$url=$api_url."mypihome.php?api=${pihome_api}&ip=${my_ip}&data=${data}&table=messages_in&id=${id}&purge=${purge}&node_id=${node_id}&child_id=${child_id}&sub_type=${sub_type}&payload=${payload}&datetime=${datetime}";
			$result = url_get_contents($url);
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Result from PiHome: \033[1;32m".$result."\033[0m \n";
			if ($result == 'Success'){
				$query = "UPDATE messages_in SET sync = '1' WHERE id ='{$id}' LIMIT 1;";
				$conn->query($query);
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Temperature sync status updated in local database.\n";
			}elseif($result == 'Failed'){
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Result from PiHome: \033[31m".$result."\033[0m \n";
			}
			echo $line;
		}
	} else {
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - No Temperature Data to sync with PiHome. \n";
	}
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Temperature Data Sync Finished. \n";
	//messages_in sync end here 
/*****************************************************************************************************************************************************/

	//Start syncing Weather with PiHome.
	$query = "SELECT * FROM weather WHERE sync = 0;";
	$results = $conn->query($query);
	if (mysqli_num_rows($results) != 0){
		echo $line;
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Weather Data to Sync with PiHome: \033[32m". mysqli_num_rows($results)."\033[0m\n"; 
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
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Table details to sync with PiHome: \n";
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
			//call out to PiHome with data 
			$url=$api_url."mypihome.php?api=${pihome_api}&ip=${my_ip}&data=${data}&table=weather&id=${id}&location=${location}&c=${c}&wind_speed=${wind_speed}&title=${title}&description=${description}&sunrise=${sunrise}&sunset=${sunset}&img=${img}&last_update=${last_update}";
			$result = url_get_contents($url);
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Result from PiHome: \033[1;32m".$result."\033[0m \n";
			if ($result == 'Success'){
				$query = "UPDATE weather SET sync = '1' WHERE id ='{$id}' LIMIT 1;";
				$conn->query($query);
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Weather sync Status Updated in Local Database.\n";
			}elseif($result == 'Failed'){
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Result from PiHome: \033[31m".$result."\033[0m \n";
			}
			echo $line;
		}
	} else {
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - No Weather Data to Sync With PiHome \n";
	}
	//Weather Data sync End Here 

//api key validation else option
}else{
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Your API Key isnt valid please contact PiHome at http://www.pihome.eu \n";
}

echo "\n"; 
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - PiConnect Script Ended \n"; 
echo "\033[32m**************************************************************\033[0m  \n";

if(isset($conn)) { $conn->close();}