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
echo "*   Boiler Script Version 0.5 Build Date 31/01/2018   *\n";
echo "*   Update on 10/04/218                               *\n";
echo "*                                Have Fun - PiHome.eu *\n";
echo "*******************************************************\n";
echo " \033[0m \n";

require_once(__DIR__.'../../st_inc/connection.php');
require_once(__DIR__.'../../st_inc/functions.php');

//Set php script execution time in seconds
ini_set('max_execution_time', 40); 
$date_time = date('Y-m-d H:i:s');

//GPIO Value for SainSmart Relay Board to turn on  or off 
$relay_on = '0'; //GPIO value to write to turn on attached relay
$relay_off = '1'; // GPIO value to write to turn off attached relay

echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Boiler Script Started \n"; 

//query to check boiler status 
$query = "SELECT * FROM boiler_view LIMIT 1;";
$result = $conn->query($query);
$row = mysqli_fetch_array($result);
$boiler_status = $row['status'];
$boiler_fire_status = $row['fired_status'];
$boiler_node_id = $row['node_id']; 
$boiler_node_child_id = $row['node_child_id']; 
$boiler_hysteresis_time = $row['hysteresis_time']; 
$boiler_max_operation_time = $row['max_operation_time'];
$boiler_goip_pin = $row['gpio_pin'];

//query to check away status 
$query = "SELECT * FROM away LIMIT 1";
$result = $conn->query($query);
$away = mysqli_fetch_array($result);
$away_status = $away['status'];

//query to get frost protection temperature
$query = "SELECT * FROM frost_protection ORDER BY id desc LIMIT 1;";
$result = $conn->query($query);
$frost_q = mysqli_fetch_array($result);
$frost_c = $frost_q['temperature'];

//query to get last boiler statues change time
$query = "SELECT * FROM boiler_logs ORDER BY id desc LIMIT 1;";
$result = $conn->query($query);
$row = mysqli_fetch_array($result);
$boiler_start_datetime = $row['start_datetime'];
$boiler_stop_datetime = $row['stop_datetime'];
$boiler_expoff_datetime = $row['expected_end_date_time'];

echo "---------------------------------------------------------------------------------------- \n";
//following variable set to 0 on start for array index. 
$boiler_index = '0';
$zone_index = '0';
$query = "SELECT * FROM zone_view where status = 1 order by index_id asc;";
$results = $conn->query($query);
while ($row = mysqli_fetch_assoc($results)) {
	$zone_id=$row['id'];
	$zone_name=$row['name'];
	$zone_max_c=$row['max_c'];
	$zone_max_operation_time=$row['max_operation_time'];
	$zone_hysteresis_time=$row['hysteresis_time'];
	$zone_sensor_id=$row['sensors_id'];
	$zone_sensor_child_id=$row['sensor_child_id'];
	$zone_controler_id=$row['controler_id'];
	$zone_controler_child_id=$row['controler_child_id'];
	$zone_gpio_pin=$row['gpio_pin'];
	
	//query to get temperature from messages_in table 
	$query = "SELECT * FROM messages_in_view_24h WHERE node_id = {$zone_sensor_id} ORDER BY datetime desc LIMIT 1;";
	$result = $conn->query($query);
	$sensor = mysqli_fetch_array($result);
	$zone_c = $sensor['payload'];
							
	$query = "SELECT * FROM schedule_daily_time_zone_view WHERE CURTIME() between `start` AND `end` AND zone_id = {$zone_id} AND time_status = '1' LIMIT 1;";
	$result = $conn->query($query);
	$schedule = mysqli_fetch_array($result);
	$sch_status = $schedule['tz_status'];
	$sch_start_time = $schedule['start'];
	$sch_end_time = $schedule['end'];
	$sch_c = $schedule['temperature'];
	
	//query to check override status and get temperature from override table 
	$query = "SELECT * FROM override WHERE zone_id = {$zone_id} LIMIT 1;";
	$result = $conn->query($query);
	$override = mysqli_fetch_array($result);
	$override_status = $override['status'];
	$override_c = $override['temperature'];

	//query to check boost status and get temperature from boost table 
	$query = "SELECT * FROM boost WHERE zone_id = {$zone_id} LIMIT 1;";
	$result = $conn->query($query);
	$boost = mysqli_fetch_array($result);
	$boost_status = $boost['status'];
	$boost_time = $boost['time'];
	$boost_c = $boost['temperature'];
	$boost_minute = $boost['minute'];

	//query to check night climate status and get temperature from night climate table 
	$query = "select * from schedule_night_climat_zone_view WHERE zone_id = {$zone_id} LIMIT 1;";
	$result = $conn->query($query);
	$night_climate = mysqli_fetch_array($result);
	$nc_time_status = $night_climate['t_status'];
	$nc_zone_status = $night_climate['z_status'];
	$nc_zone_id = $night_climate['zone_id'];
	$nc_start_time = $night_climate['start_time'];
	$nc_end_time = $night_climate['end_time'];
	$nc_min_c = $night_climate['min_temperature'];
	$nc_max_c = $night_climate['max_temperature'];
	
	//night climate time to add 10 minuts for record purpose 
	$timestamp =strtotime(date('H:i:s')) + 60 *10;
	$nc_end_time_rc = date('H:i:s', $timestamp);
	
	$current_time = date('H:i:s');
	if ((TimeIsBetweenTwoTimes($current_time, $nc_start_time, $nc_end_time)) && ($nc_time_status =='1') && ($nc_zone_status =='1')) {
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Night Climate Enabled for This Zone \n";
		$night_climate_status='1';
	} else {
		$night_climate_status='0';
	}

	//check boost time is passed, if it passed then update db and set to boost status to 0
	$phpdate = strtotime( $boost_time );
	$boost_time = $phpdate + ($boost_minute * 60);
	$now=strtotime(date('Y-m-d H:i:s'));
	if (($boost_time > $now) && ($boost_status=='1')){
		$boost_active='1';
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Boost is Active for This Zone \n";
	}else {
		$boost_active='0';
		$query = "UPDATE boost SET status = '{$boost_active}' WHERE zone_id = {$row['id']} LIMIT 1";
		$conn->query($query);
		/* 
		Following is commented out if you dont have Boost Console Build. 
		$query = "SELECT * FROM boost WHERE zone_id ={$row['id']}";
		$bresults = mysql_query($query, $connection);
		$brow = mysqli_fetch_assoc($bresults);
		$brow['boost_button_id'];
		$brow['boost_button_child_id'];
		$query = "UPDATE messages_out SET payload = '{$boost_active}', sent = '0' WHERE zone_id = {$row['id']} AND node_id = {$brow['boost_button_id']} AND child_id = {$brow['boost_button_child_id']} LIMIT 1";
		mysql_query($query, $connection);
		*/
	}
	
	//Following line to decide which temperature is target temperature 
	if ($boost_active=='1'){$target_c=$boost_c;} elseif ($night_climate_status =='1') {$target_c=$nc_min_c;} elseif($override_status=='1'){$target_c=$override_c;} elseif($override_status=='0'){$target_c=$sch_c;}

	//check if hysteresis is passed its time or not 
	$hysteresis='0';
	if (isset($boiler_stop_datetime)){
		$boiler_time = strtotime( $boiler_stop_datetime );
		$hysteresis_time = $boiler_time + ($boiler_hysteresis_time * 60);
		$now=strtotime(date('Y-m-d H:i:s'));
		if ($hysteresis_time > $now){
			$hysteresis='1';
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Hysteresis time: ".date('Y-m-d H:i:s',$hysteresis_time)." \n";
		}else{$hysteresis='0';}
	}
	//initialize two variable
	$start_cause ='';
	$stop_cause = '';
if ($zone_c < $frost_c){$zone_status="1"; $start_cause="Frost Protection";}
	elseif(($zone_c >= $frost_c) && ($zone_c < $zone_max_c) && ($hysteresis=='0')){ 
		if ($away_status=='0'){
			if($boost_status=='0'){$zone_status="0"; $stop_cause="Boost Finished";
				if ($night_climate_status =='0') {
					if (($sch_status =='1') && ($zone_c < $target_c)){$zone_status="1"; $start_cause="Schedule Started"; $expected_end_date_time=date('Y-m-d '.$sch_end_time.''); }
					if (($sch_status =='1') && ($zone_c > $target_c)){$zone_status="0"; $stop_cause="Schedule Target C Achieved"; }
					if (($sch_status =='1') && ($override_status=='1') && ($zone_c < $target_c)){$zone_status="1"; $start_cause="Schedule Override Started"; $expected_end_date_time=date('Y-m-d '.$sch_end_time.'');} 
					if (($sch_status =='1') && ($override_status=='1') && ($zone_c > $target_c)){$zone_status="0"; $stop_cause="Schedule Override Target C Achieved";} 
					if (($sch_status =='0') &&($override_status=='0')){$zone_status="0"; $stop_cause="No Schedule For This Zone \n"; } 
					if ($sch_status=='0') {$zone_status="0"; $stop_cause="No Schedule"; } 
				}elseif($night_climate_status=='1' && $zone_c < $target_c){$zone_status="1"; $start_cause="Night Climate"; $expected_end_date_time=date('Y-m-d '.$nc_end_time_rc.'');
				}elseif($night_climate_status=='1' && $zone_c >= $target_c){$zone_status="0"; $start_cause="Night Climate C Reached"; $expected_end_date_time=date('Y-m-d '.$nc_end_time_rc.'');}
			}elseif ($boost_status=='1' && $zone_c < $target_c) {$zone_status="1"; $start_cause="Boost Active"; $expected_end_date_time=date('Y-m-d H:i:s', $boost_time);
			}elseif ($boost_status=='1' && $zone_c > $target_c) {$zone_status="0"; $stop_cause="Boost Target C Achived";}
		}elseif($away_status=='1'){$zone_status="0"; $stop_cause="Away Active";}
	}else{$zone_status="0"; $stop_cause="Zone Reached its Max Temperature ".$zone_max_c;;}
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone ID: \033[41m".$zone_id. "\033[0m \n";
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone: ".$zone_name." Controller: \033[41m".$zone_controler_id."\033[0m Controller Child: \033[41m".$zone_controler_child_id."\033[0m Zone Status: \033[41m".$zone_status."\033[0m \n";	
if ($zone_status=='1') {echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone: ".$zone_name." Start Cause: ".$start_cause." - Target C:\033[41m".$target_c."\033[0m Zone C:\033[31m".$zone_c."\033[0m \n";}
if ($zone_status=='0') {echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone: ".$zone_name." Stop Cause: ".$stop_cause." - Target C:\033[41m".$target_c."\033[0m Zone C:\033[31m".$zone_c."\033[0m \n";}

/***************************************************************************************
Zone Vole Wired to Raspberry Pi GPIO Section: Zone Vole Connected Raspberry Pi GPIO. 
****************************************************************************************/
$relay_status = ($zone_status == '1') ? $relay_off : $relay_on;
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone:  GIOP Relay Status: \033[41m".$relay_status. "\033[0m (1=On, 0=Off) \n";
exec("/usr/local/bin/gpio write ".$zone_gpio_pin." ".$relay_status ); 
exec("/usr/local/bin/gpio mode ".$zone_gpio_pin." out");

/***************************************************************************************
Zone Vole Wireless Section: MySensors Wireless Relay module for your Zone vole control. 
****************************************************************************************/
//update messages_out table with sent status to 0 and payload to as zone status.
$query = "UPDATE messages_out SET sent = '0', payload = '{$zone_status}' WHERE node_id ='$zone_controler_id' AND child_id = '$zone_controler_child_id' LIMIT 1;";
$conn->query($query);

//all zone status to boiler array and increment array index
$boiler[$boiler_index] = $zone_status;
$boiler_index = $boiler_index+1;

//all zone ids and status to multidimensional Array. and increment array index. 
$zone_log[$zone_index] = (array('zone_id' =>$zone_id, 'status'=>$zone_status));
$zone_index = $zone_index+1;

//end of while loop
echo "---------------------------------------------------------------------------------------- \n";
}

//For debug info only 
//print_r ($zone_log);
//print_r ($boiler);
if (isset($boiler_stop_datetime)) {echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Boiler Switched Off At: ".$boiler_stop_datetime. "\n";}
if (isset($expected_end_date_time)){echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Boiler Expected End Time: ".$expected_end_date_time. "\n"; }

//Boiler On section
//Search inside array if any value is set to 1 then we need to update db with boiler status
if (in_array("1", $boiler)) {
	$new_boiler_status='1';
	
	//update boiler fired status to 1
	$query = "UPDATE boiler SET sync = '0', fired_status = '{$new_boiler_status}' WHERE id ='1' LIMIT 1";
	$conn->query($query);
	
	/***************************************************************************************
	GAS Boiler Wirelss Section:	MySensors Wireless Relay module for your GAS Boiler control
	****************************************************************************************/
	//update messages_out table with sent status to 0 and payload to as boiler status.
	$query = "UPDATE messages_out SET sent = '0', payload = '{$new_boiler_status}' WHERE node_id ='{$boiler_node_id}' AND child_id = '{$boiler_node_child_id}' LIMIT 1;";
	$conn->query($query);

	/***************************************************************************************
	Boiler Wired to Raspberry Pi GPIO Section: Make sure you have WiringPi installed. 
	****************************************************************************************/
	exec("/usr/local/bin/gpio write ".$boiler_goip_pin ." ".$relay_on ); 
	exec("/usr/local/bin/gpio mode ".$boiler_goip_pin ." out");

	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Boiler Node ID: \033[41m".$boiler_node_id."\033[0m Child ID: \033[41m".$boiler_node_child_id."\033[0m \n";	
	if ($boiler_fire_status != $new_boiler_status){
		//insert date and time into boiler log table so we can record boiler start date and time.
		$bsquery = "INSERT INTO boiler_logs(start_datetime, start_cause, expected_end_date_time) VALUES ('{$date_time}', '{$start_cause}', '{$expected_end_date_time}');";
		$result = $conn->query($bsquery);
		$boiler_log_id = mysqli_insert_id($conn);
	
		//echo all zone and status 
		for ($row = 0; $row < 3; $row++){
			echo "Zone ID: ".$zone_log[$row]["zone_id"]." Status: ".$zone_log[$row]["status"]."\n";
			$zlquery = "INSERT INTO zone_logs(zone_id, boiler_log_id, status) VALUES ('{$zone_log[$row]["zone_id"]}', '{$boiler_log_id}', '{$zone_log[$row]["status"]}');";
			$zlresults = $conn->query($zlquery);
			if ($zlresults) {echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone Log table updated successfully. \n";} else {echo "zone log update failed... ".mysql_error(). " \n";}
			}
		if ($result) {
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Boiler Log table added Successfully. \n";
		}else {
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Boiler Log table addition failed. \n";
		}
	}
//Boiler Off section
}else{
	$new_boiler_status='0';
	//update boiler fired status to 0
	$query = "UPDATE boiler SET sync = '0', fired_status = '{$new_boiler_status}' WHERE id ='1' LIMIT 1";
	$conn->query($query);
	
	/***************************************************************************************
	GAS Boiler Wirelss Section:	MySensors Wireless Relay module for your GAS Boiler control
	****************************************************************************************/
	//update messages_out table with sent status to 0 and payload to as boiler status.
	$query = "UPDATE messages_out SET sent = '0', payload = '{$new_boiler_status}' WHERE node_id ='{$boiler_node_id}' AND child_id = '{$boiler_node_child_id}' LIMIT 1;";
	$conn->query($query);

	/***************************************************************************************
	Boiler Wired to Raspberry Pi GPIO Section: Make sure you have WiringPi installed. 
	****************************************************************************************/
	exec("/usr/local/bin/gpio write ".$boiler_goip_pin ." ".$relay_off ); 
	exec("/usr/local/bin/gpio mode ".$boiler_goip_pin ." out");
	
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Boiler Node ID: \033[41m".$boiler_node_id."\033[0m Child ID: \033[41m".$boiler_node_child_id."\033[0m \n";	
	if ($boiler_fire_status != $new_boiler_status){
		//Update last record with boiler stop date and time in boiler log table. 
		$query = "UPDATE boiler_logs SET stop_datetime = '{$date_time}', stop_cause = '{$stop_cause}' ORDER BY id DESC LIMIT 1";
		$result = $conn->query($query);
		if ($result) {
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Boiler Log table updated Successfully. \n";
		}else {
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Boiler Log table update failed. \n";
		}
	}
}

/********************************************************************************************************************************************************************
Following section is Optional for States collection  
I thank you for not commenting it out as it will help me to allocate time to keep this systems updated. 
I am using CPU serial as salt and then using MD5 hasing to get unique reference, i have no other intention if you want you can set variable to anything you like
/********************************************************************************************************************************************************************/
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
	$ph_version = settings($conn, 'version');
	$ph_build = settings($conn, 'build');
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - External IP Address: ".$external_ip."\n";
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Raspberry Pi Serial: " .$pi_serial."\n";
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Raspberry Pi Hardware: " .$hardware."\n";
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Raspberry Pi CPU Model: " .$cpu_model."\n";
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Raspberry Pi Revision: " .$revision."\n";
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - PiHome Version: " .$ph_version."\n";
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - PiHome Build: " .$ph_build."\n";
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Raspberry Pi UID: " .$uid."\n";
	$url="http://ddns.pihome.eu/home.php?ip=${external_ip}&serial=${uid}&cpu_model=${cpu_model}&hardware=${hardware}&revision=${revision}&ph_version=${ph_version}&ph_build=${ph_build}";
	echo $url."\n";
	$result = url_get_contents($url);
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - PiHome Says: ".$result."\n";
	echo "---------------------------------------------------------------------------------------- \n";
}

echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Boiler Fired Status: ".$new_boiler_status."\n";	
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Boiler Hysteresis Status: ".$hysteresis."\n";
echo "---------------------------------------------------------------------------------------- \n";
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Boiler Script Ended \n"; 
echo "\033[32m****************************************************************************************\033[0m  \n";
if(isset($conn)) { $conn->close();}
?>