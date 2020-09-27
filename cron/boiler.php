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
echo "**********************************************************\n";
echo "*   Boiler-On Script Version 0.53 Build Date 31/01/2018  *\n";
echo "*   Update on 23/08/2020                                 *\n";
echo "*                                Have Fun - PiHome.eu    *\n";
echo "**********************************************************\n";
echo " \033[0m \n";

require_once(__DIR__.'../../st_inc/connection.php');
require_once(__DIR__.'../../st_inc/functions.php');

//Set php script execution time in seconds
ini_set('max_execution_time', 40);
$date_time = date('Y-m-d H:i:s');

//set to display debug messages if called with any parameter
if(isset($argv[1])) { $debug_msg = 1; } else { $debug_msg = 0; }

//GPIO Value for SainSmart Relay Board to turn on  or off
$relay_on = '0'; //GPIO value to write to turn on attached relay
$relay_off = '1'; // GPIO value to write to turn off attached relay

//Function to recursively check homebridge config.json
function scanArrayRecursively($arr, $index) {
	if ($arr) {
		foreach ($arr as $key => $value) {
			if (is_array($value)) {
				scanArrayRecursively($value, $index);
			} else {
				if ($index == 'id') { // checking for [id] keys in config.json
					if ($key == $index) {
						if (strpos($value, 'switch') !== false) {
							array_push($GLOBALS['switches'], substr($value, 6));
						} elseif (strpos($value, 'sensor') !== false) {
							array_push($GLOBALS['sensors'], substr($value, 6));
						}
					}
				} elseif ($index == 'platform') { // checking for [platform] keys in config.json
					if ($key == $index) {
						if (strpos($value, 'HttpWebHooks') !== false) { $GLOBALS['platform'] = 1; return; }
					}
				}
			}
		}
	}
}

echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Boiler Script Started \n";

$switches = array(); // array of switch zone ids in homebridge config.json
$sensors = array(); // array of sensor zone ids in homebridge config.json
$platform = 0; // flag to indicate if webhooks plugin is present

//check if homebridge service is running
$rval=my_exec("/bin/systemctl status homebridge");
if($rval['stdout']=='') {
	$stat='Error';
} else {
	$stat='Status: Unknown';
	$rval['stdout']=explode(PHP_EOL,$rval['stdout']);
	foreach($rval['stdout'] as $line) {
		if(strstr($line,'Loaded:')) {
			if(strstr($line,'disabled;')) {
				$stat='Status: Disabled';
			}
		}
		if(strstr($line,'Active:')) {
			if(strstr($line,'active (running)')) {
				$stat=trim($line);
				// homebridge service is running so check if config.json file exists
				$path = exec('find /usr/ -name homebridge-http-webhooks'); // path to webhooks directory
				$filename = '/var/lib/homebridge/config.json'; // path to config.json
				if (file_exists($filename) and file_exists($path)) {
					$string = file_get_contents($filename);
					$json_data = json_decode($string, true);
					// check if webhooks platform section is present in config.json
					scanArrayRecursively($json_data, 'platform');
					if ($platform  == 1) {
						// webhooks plugin present so get zone ids for both switches and sensors in to arrarys
						scanArrayRecursively($json_data, 'id');
					}
				}
			} else if(strstr($line,'(dead)')) {
				$stat='Status: Dead';
			}
		}
	}
}
//Only for debouging
if ($debug_msg == 1) {
	$query = "select zone.id as tz_id, zone.name, zone.status as tz_status, zone_type.type, zone_type.category FROM zone, zone_type WHERE (zone.type_id = zone_type.id) AND status = 1 AND zone.`purge`= 0 ORDER BY index_id asc; ";
	$results = $conn->query($query);
	while ($row = mysqli_fetch_assoc($results)) {
		echo "ID ".$row['tz_id']."\n";
		echo "Name ".$row['name']."\n";
		echo "Status ".$row['tz_status']."\n";
		echo "Type ".$row['category']."\n";
		echo "Category ".$row['category']."\n";
		if ($row["category"] == 1 OR $row["category"] == 2) { echo "Found\n"; }
	}
}
//query to check boiler status
$query = "SELECT * FROM boiler_view LIMIT 1;";
$result = $conn->query($query);
$row = mysqli_fetch_array($result);
$boiler_status = $row['status'];
$boiler_fire_status = $row['fired_status'];
$boiler_controller_type = $row['controller_type'];
$boiler_node_id = $row['node_id'];
$boiler_node_child_id = $row['node_child_id'];
$boiler_hysteresis_time = $row['hysteresis_time'];
$boiler_max_operation_time = $row['max_operation_time'];
$boiler_overrun_time = $row['overrun'];

//Get data from nodes table
$query = "SELECT * FROM nodes WHERE node_id ='$boiler_node_id' AND status IS NOT NULL LIMIT 1;";
$result = $conn->query($query);
$boiler_node = mysqli_fetch_array($result);
$boiler_seen = $boiler_node['last_seen'];
$boiler_notice = $boiler_node['notice_interval'];

//query to check away status
$query = "SELECT * FROM away LIMIT 1";
$result = $conn->query($query);
$away = mysqli_fetch_array($result);
$away_status = $away['status'];

//query to check holidays status
$query = "SELECT * FROM holidays WHERE NOW() between start_date_time AND end_date_time AND status = '1' LIMIT 1";
$result = $conn->query($query);
$rowcount=mysqli_num_rows($result);
if ($rowcount > 0) {
	$holidays = mysqli_fetch_array($result);
	$holidays_status = $holidays['status'];
}else {
	$holidays_status = 0;
}

//query to get frost protection temperature
$query = "SELECT * FROM frost_protection ORDER BY id desc LIMIT 1;";
$result = $conn->query($query);
if (mysqli_num_rows($result)==0){
	//No record in frost_protction table, so add
	$frost_c = 5;
	$query = "INSERT INTO frost_protection VALUES(1, 0, 0, '{$date_time}', '" . number_format($frost_c,1) . "');";
	$conn->query($query);
} else {
	$frost_q = mysqli_fetch_array($result);
	$frost_c = $frost_q['temperature'];
}

//query to get last boiler statues change time
$query = "SELECT * FROM boiler_logs ORDER BY id desc LIMIT 1;";
$result = $conn->query($query);
$row = mysqli_fetch_array($result);
$boiler_start_datetime = $row['start_datetime'];
$boiler_stop_datetime = $row['stop_datetime'];
$boiler_expoff_datetime = $row['expected_end_date_time'];

//query to active network gateway address
$query = "SELECT gateway_address FROM network_settings WHERE primary_interface = 1 LIMIT 1;";
$result = $conn->query($query);
$network = mysqli_fetch_array($result);
if (mysqli_num_rows($result) > 0){
        $n_gateway = $network['gateway_address'];
        $base_addr = substr($n_gateway,0,strrpos($n_gateway,'.')+1);
} else {
        $base_addr = '000.000.000.000';
}

//following variable set to 0 on start for array index.
$boiler_index = '0';
$zone_index = '0';
$command_index = '0';
$current_time = date('H:i:s');

//following variable set to current day of the week.
$dow = idate('w');
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Day of the Week: \033[41m".$dow. "\033[0m \n";
echo "---------------------------------------------------------------------------------------- \n";
$query = "SELECT zone.id, zone.status, zone.zone_state, zone.name, zone_type.type, zone_type.category, zone.max_operation_time FROM zone, zone_type WHERE zone.type_id = zone_type.id order by index_id asc;";
$results = $conn->query($query);
while ($row = mysqli_fetch_assoc($results)) {
        $zone_status=$row['status'];
        $zone_state = $row['zone_state'];
        $zone_id=$row['id'];
        $zone_name=$row['name'];
        $zone_type=$row['type'];
        $zone_category=$row['category'];
        $zone_max_operation_time=$row['max_operation_time'];

        //get the zone controllers for this zone to array
        $query = "SELECT zone_controllers.id AS zc_id, nodes.node_id AS controler_id, zone_controllers.controler_child_id, zone_controllers.state, zone_controllers.current_state, nodes.type  FROM  zone_controllers,nodes WHERE (zone_controllers.controler_id = nodes.id) AND zone_id = '$zone_id';";
        $cresult = $conn->query($query);
        $index = 0;
        $zone_controllers=[];
        while ($crow = mysqli_fetch_assoc($cresult)) {
                $zone_controllers[$index] = array('zc_id' =>$crow['zc_id'], 'controler_id' =>$crow['controler_id'], 'controler_child_id' =>$crow['controler_child_id'], 'zone_controller_state' =>$crow['state'], 'zone_controller_current_state' =>$crow['current_state'], 'zone_controller_type' =>$crow['type'], 'manual_button_override' >=0);
                $index = $index + 1;
        }
	//query to check if zone_current_state record exists tor the zone
	$query = "SELECT * FROM zone_current_state WHERE zone_id = {$zone_id} LIMIT 1;";
	$result = $conn->query($query);
	if (mysqli_num_rows($result)==0){
		//No record in zone_current_statw table, so add
		$query = "INSERT INTO zone_current_state (id, `sync`, `purge`, `zone_id`, `mode`, `status`, `temp_reading`, `temp_target`, `temp_cut_in`, `temp_cut_out`, `controler_fault`, `controler_seen_time`, `sensor_fault`, `sensor_seen_time`, `sensor_reading_time`, `overrun`) VALUES('{$zone_id}', 0, 0, '{$zone_id}', 0, 0, 0, 0, 0, 0, 0, NULL , 0, NULL, NULL, 0);";
		$conn->query($query);
	}

	//query to get zone previous running status
	$query = "SELECT * FROM zone_current_state WHERE zone_id = '{$zone_id}' LIMIT 1;";
	$result = $conn->query($query);
	$zone_current_state = mysqli_fetch_array($result);
	$zone_status_prev = $zone_current_state['status'];
	$zone_overrun_prev = $zone_current_state['overrun'];
	$zone_current_mode = $zone_current_state['mode'];

	// process if a sensor is attached to this zone
	if ($zone_category == 0 OR $zone_category == 1) {
		$query = "SELECT * FROM  zone_sensors WHERE zone_id = '{$zone_id}' LIMIT 1;";
                $result = $conn->query($query);
                $sensor = mysqli_fetch_array($result);
	        $zone_max_c=$sensor['max_c'];
	        $zone_hysteresis_time=$sensor['hysteresis_time'];
        	$zone_sp_deadband=$sensor['sp_deadband'];
	        $zone_sensor_id=$sensor['sensor_id'];
        	$zone_sensor_child_id=$sensor['sensor_child_id'];

                $query = "SELECT node_id FROM nodes WHERE id = '{$zone_sensor_id}' LIMIT 1;";
                $result = $conn->query($query);
                $nodes = mysqli_fetch_array($result);
                $zone_node_id=$nodes['node_id'];


		//query to get temperature from messages_in_view_24h table view
		$query = "SELECT * FROM messages_in_view_24h WHERE node_id = '{$zone_node_id}' AND child_id = {$zone_sensor_child_id} ORDER BY datetime desc LIMIT 1;";
		$result = $conn->query($query);
		$msg_out = mysqli_fetch_array($result);
		$zone_c = $msg_out['payload'];
		$temp_reading_time = $msg_out['datetime'];
		// check if webhooks plugin installed and if a sensor is configured for this zone
		if ($platform == 1 and in_array($row['id'], $sensors)) {
			// get current temperature vale
			$url = "http://127.0.0.1:51828/?accessoryId=sensor" . $zone_id;
			$contents = file_get_contents($url);
			$contents = utf8_encode($contents);
			$temp = json_decode($contents, true);
			// update if the vales do not match
			if (floatval($temp['state']) != floatval($zone_c)) {
				$url = $url . "&value=" . $zone_c;
				$contents = file_get_contents($url);
			}
		}
	}
        // only process active zones
        if ($zone_status == 1) {
                //Have to account for midnight rollover conditions
                if ($zone_category == 2) { //Check if using sunset for schedule start time
                        $query = "SELECT schedule_daily_time.start, schedule_daily_time_zone.sunset, schedule_daily_time_zone.sunset_offset FROM schedule_daily_time, schedule_daily_time_zone WHERE (schedule_daily_time_zone.schedule_daily_time_id = schedule_daily_time.id) AND zone_id = {$zone_id} LIMIT 1;";
                        $result = $conn->query($query);
                        $sch_row = mysqli_fetch_array($result);
                        $sunset = $sch_row['sunset'];
                        $start_time = $sch_row['start'];
                        $sunset_offset = $sch_row['sunset_offset'];
                        if ($sunset == 1) {
                                $query = "SELECT * FROM weather WHERE last_update > DATE_SUB( NOW(), INTERVAL 24 HOUR);";
                                $result = $conn->query($query);
                                $rowcount=mysqli_num_rows($result);
                                if ($rowcount > 0) {
                                        $wrow = mysqli_fetch_array($result);
                                        if (date('H:i:s', $wrow['sunset']) < $start_time) {
                                                $sunset_time = date('H:i:s', $wrow['sunset']);
                                                $start_time = strtotime($sunset_time);
                                                $start_time = $start_time + ($sunset_offset * 60); //set to start $sunset_offset minutes before sunset
                                                $start_time = date('H:i:s', $start_time);
                                         }
                                }
                        }
	                if($holidays_status == 0) {
				$query = "SELECT * FROM schedule_daily_time_zone_view WHERE ((`end`> CAST('{$start_time}' AS time) AND CURTIME() between CAST('{$start_time}' AS time) AND `end`) OR (`end` < CAST('{$start_time}' AS time) AND CURTIME() < `end`) OR (`end` < CAST('{$start_time}' AS time) AND CURTIME() > CAST('{$start_time}' AS time))) AND zone_id = {$zone_id} AND time_status = '1' AND (WeekDays & (1 << {$dow})) > 0 AND holidays_id = 0 LIMIT 1;";
               		}else{
                       		$query = "SELECT * FROM schedule_daily_time_zone_view WHERE ((`end`> CAST('{$start_time}' AS time) AND CURTIME() between CAST('{$start_time}' AS time) AND `end`) OR (`end` < CAST('{$start_time}' AS time) AND CURTIME() < `end`) OR (`end` < CAST('{$start_time}' AS time) AND CURTIME() > CAST('{$start_time}' AS time))) AND zone_id = {$zone_id} AND time_status = '1' AND (WeekDays & (1 << {$dow})) > 0 AND holidays_id > 0 LIMIT 1;";
                	}
		} else { //not cat2 zone
			if($holidays_status == 0) {
				$query = "SELECT * FROM schedule_daily_time_zone_view WHERE ((`end`>`start` AND CURTIME() between `start` AND `end`) OR (`end`<`start` AND CURTIME()<`end`) OR (`end`<`start` AND CURTIME()>`start`)) AND zone_id = {$zone_id} AND time_status = '1' AND (WeekDays & (1 << {$dow})) > 0 AND holidays_id = 0 LIMIT 1;";
			}else{
				$query = "SELECT * FROM schedule_daily_time_zone_view WHERE ((`end`>`start` AND CURTIME() between `start` AND `end`) OR (`end`<`start` AND CURTIME()<`end`) OR (`end`<`start` AND CURTIME()>`start`)) AND zone_id = {$zone_id} AND time_status = '1' AND (WeekDays & (1 << {$dow})) > 0 AND holidays_id > 0 LIMIT 1;";
			}
		}
		//echo $query . PHP_EOL;
		$result = $conn->query($query);
		if(mysqli_num_rows($result)<=0){
			$sch_status=0;
			$sch_c=0;
			$sch_holidays = '0';
		}else{
			$schedule = mysqli_fetch_array($result);
			$sch_status = $schedule['tz_status'];
			$sch_start_time = $schedule['start'];
			$sch_end_time = $schedule['end'];
			$sch_c = $schedule['temperature'];
			$sch_coop = $schedule['coop'];

			if ($schedule['holidays_id']>0) {
				$sch_holidays = '1';
			}else{
				$sch_holidays = '0';
			}
		}

                //query to check override status and get temperature from override table
                $query = "SELECT * FROM override WHERE zone_id = {$zone_id} LIMIT 1;";
                $result = $conn->query($query);
                if (mysqli_num_rows($result) != 0){
                        $override = mysqli_fetch_array($result);
                        $zone_override_status = $override['status'];
                        $override_c = $override['temperature'];
                }else {
                        $zone_override_status = '0';
                }

              	//Calculate zone fail using the zone_controllers array
                for ($crow = 0; $crow < count($zone_controllers); $crow++){
                        $zone_controler_id = $zone_controllers[$crow]["controler_id"];
                        $zone_controler_child_id = $zone_controllers[$crow]["controler_child_id"];
                        $zone_fault = 0;
                        $zone_ctr_fault = 0;
                        $zone_sensor_fault = 0;
			$manual_button_override = 0;

                        //Get data from nodes table
                        $query = "SELECT * FROM nodes WHERE node_id ='$zone_controler_id' AND status IS NOT NULL LIMIT 1;";
                        $result = $conn->query($query);
                        $controler_node = mysqli_fetch_array($result);
                        $controler_type = $controler_node['type'];
                        $controler_seen = $controler_node['last_seen'];
                        $controler_notice = $controler_node['notice_interval'];
                        if($controler_notice > 0){
                                $now=strtotime(date('Y-m-d H:i:s'));
                                $controler_seen_time = strtotime($controler_seen);
                                if ($controler_seen_time  < ($now - ($controler_notice*60))){
                                        $zone_fault = 1;
                                        $zone_ctr_fault = 1;
                                        echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone valve communication timeout for This Zone. Node Last Seen: ".$controler_seen."\n";
                                }
                        }

			// if add-on controller then process state change from GUI
			if ($zone_category == 2) {
				$current_state = $zone_controllers[$crow]["zone_controller_current_state"];
                                $add_on_state = $zone_controllers[$crow]["zone_controller_state"];
				if (($zone_current_mode == 114) && ($current_state != $add_on_state)) {
                                	$query = "UPDATE override SET status = 1, sync = '0' WHERE zone_id = {$zone_id};";
                                        $conn->query($query);
				} elseif ($sch_status == 0 && $zone_override_status == 1) {
                                        $query = "UPDATE override SET status = 0, sync = '0' WHERE zone_id = {$zone_id};";
                                        $conn->query($query);
				}

	                        // check is switch has manually changed the ON/OFF state
        	                if ($controler_type == 'Tasmota') {
                	                if ($base_addr == '000.000.000.000') {
                        	                echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - NO Gateway Address is Set \n";
                                	} else {
                		                $query = "SELECT * FROM http_messages WHERE zone_name = '{$zone_name}' AND message_type = 1 LIMIT 1;";
		                                $result = $conn->query($query);
                                		$http = mysqli_fetch_array($result);
                                        	$url = "http://".$base_addr.$zone_controler_child_id."/cm?cmnd=power";
	                                        $contents = file_get_contents($url);
        	                                $contents = utf8_encode($contents);
                	                        $resp = json_decode($contents, true);
                        	                if ($resp[strtoupper($http['command'])] == 'ON' ) {
                                	                $button_override = 1;
                                        	} else {
                                                	$button_override = 0;
	                                        }
						if ($current_state == $button_override) { $manual_button_override = 0; } else { $manual_button_override = 1; }
        	                        }
                	        }
                                if ($debug_msg == 1) { echo "zone_current_mode ".$zone_current_mode.", current_state ".$current_state.", add_on_state ".$add_on_state.", manual_button_override ".$manual_button_override.", sch_status ".$sch_status."\n"; }
			}
                        $zone_controllers[$crow]['manual_button_override'] = $manual_button_override;
                } // end for ($crow = 0; $crow < count($zone_controllers); $crow++)

		//query to check boost status and get temperature from boost table
		$query = "SELECT * FROM boost WHERE zone_id = {$zone_id} AND status = 1 LIMIT 1;";
		$result = $conn->query($query);
		if (mysqli_num_rows($result) != 0){
			$boost = mysqli_fetch_array($result);
			$boost_status = $boost['status'];
			$boost_time = $boost['time'];
			$boost_c = $boost['temperature'];
			$boost_minute = $boost['minute'];
		} else {
			$boost_status = '0';
		}

		//check boost time is passed, if it passed then update db and set to boost status to 0
		if ($boost_status=='1'){
			$phpdate = strtotime( $boost_time );
			$boost_time = $phpdate + ($boost_minute * 60);
			$now=strtotime(date('Y-m-d H:i:s'));
			if (($boost_time > $now) && ($boost_status=='1')){
				$boost_active='1';
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Boost is Active for This Zone \n";
			}elseif (($boost_time < $now) && ($boost_status=='1')){
				$boost_active='0';
				//You can comment out if you dont have Boost Button Console installed.
				$query = "SELECT * FROM boost WHERE zone_id ={$row['id']} AND status = '1';";
				$bresults = $conn->query($query);
				$brow = mysqli_fetch_assoc($bresults);
				$brow['boost_button_id'];
				$brow['boost_button_child_id'];
				$query = "UPDATE messages_out SET payload = '{$boost_active}', sent = '0' WHERE zone_id = {$row['id']} AND node_id = {$brow['boost_button_id']} AND child_id = {$brow['boost_button_child_id']} LIMIT 1;";
				$conn->query($query);
				//update Boost Records in database
				$query = "UPDATE boost SET status = '{$boost_active}', sync = '0' WHERE zone_id = {$row['id']};";
				$conn->query($query);
			}else {
				$boost_active='0';
			}
		}else {
			$boost_active='0';
		}

		//Check Zone category 0 or 1
		if ($zone_category == 0 OR $zone_category == 1) {
			//query to check night climate status and get temperature from night climate table
			//$query = "select * from schedule_night_climat_zone_view WHERE zone_id = {$zone_id} LIMIT 1;";
			$query = "SELECT * from schedule_night_climat_zone_view WHERE ((`end`>`start` AND CURTIME() between `start` AND `end`) OR (`end`<`start` AND CURTIME()<`end`) OR (`end`<`start` AND CURTIME()>`start`)) AND zone_id = {$zone_id} AND time_status = '1' AND tz_status = '1' AND (WeekDays & (1 << {$dow})) > 0 LIMIT 1;";
			$result = $conn->query($query);
			if (mysqli_num_rows($result) != 0){
				$night_climate = mysqli_fetch_array($result);
				$nc_time_status = $night_climate['time_status'];
				$nc_zone_status = $night_climate['tz_status'];
				$nc_zone_id = $night_climate['zone_id'];
				$nc_start_time = $night_climate['start'];
				$nc_end_time = $night_climate['end'];
				$nc_min_c = $night_climate['min_temperature'];
				$nc_max_c = $night_climate['max_temperature'];
				$nc_weekday = $night_climate['WeekDays'] & (1 << $dow);
				//night climate time to add 10 minuts for record purpose
				$timestamp =strtotime(date('H:i:s')) + 60 *10;
				$nc_end_time_rc = date('H:i:s', $timestamp);
				$current_time = date('H:i:s');
				if ((TimeIsBetweenTwoTimes($current_time, $nc_start_time, $nc_end_time)) && ($nc_time_status =='1') && ($nc_zone_status =='1') && ($nc_weekday > 0)) {
					echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Night Climate Enabled for This Zone \n";
					$night_climate_status='1';
				} else {
					$night_climate_status='0';
				}
			}else {
				$night_climate_status='0';
			}

			//Get Weather Temperature
			$query = "SELECT * FROM messages_in WHERE node_id = '1' ORDER BY id desc LIMIT 1";
			$result = $conn->query($query);
			$weather_temp = mysqli_fetch_array($result);
			$weather_c = $weather_temp['payload'];
			//    1    00-05    0.3
			//    2    06-10    0.4
			//    3    11-15    0.5
			//    4    16-20    0.6
			//    5    21-30    0.7
			$weather_fact = 0;
			if ($weather_c <= 5 ) {$weather_fact = 0.3;} elseif ($weather_c <= 10 ) {$weather_fact = 0.4;} elseif ($weather_c <= 15 ) {$weather_fact = 0.5;} elseif ($weather_c <= 20 ) {$weather_fact = 0.6;} elseif ($weather_c <= 30 ) {$weather_fact = 0.7;}

			//Following line to decide which temperature is target temperature
			if ($boost_active=='1'){$target_c=$boost_c;} elseif ($night_climate_status =='1') {$target_c=$nc_min_c;} elseif($zone_override_status=='1'){$target_c=$override_c;} elseif($zone_override_status=='0'){$target_c=$sch_c;}

			//calculate cutin/cut out temperatures
			$temp_cut_in = $target_c - $weather_fact - $zone_sp_deadband;
			$temp_cut_out = $target_c - $weather_fact;

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

			//Check sensor notice interval and notice logic
			$query = "SELECT * FROM nodes WHERE id ='$zone_sensor_id' AND status IS NOT NULL LIMIT 1;";
			$result = $conn->query($query);
			$sensor_node = mysqli_fetch_array($result);
			$sensor_seen = $sensor_node['last_seen']; //not using this cause it updates on battery update
			$sensor_notice = $sensor_node['notice_interval'];
			if($sensor_notice > 0) {
				$now=strtotime(date('Y-m-d H:i:s'));
				$sensor_seen_time = strtotime($temp_reading_time); //using time from messages_in
				if ($sensor_seen_time  < ($now - ($sensor_notice*60))){
					$zone_fault = 1;
					$zone_sensor_fault = 1;  
					echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Temperature sensor communication timeout for This Zone. Last temperature reading: ".$temp_reading_time."\n";
				}
			}

			//Check boiler controller notice interval and notice logic
			if($boiler_notice > 0){
				$now=strtotime(date('Y-m-d H:i:s'));
				$boiler_seen_time = strtotime($boiler_seen);
				if ($boiler_seen_time  < ($now - ($boiler_notice*60))){
					$zone_fault = 1;
					echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Boiler controler communication timeout. Boiler Last Seen: ".$boiler_seen."\n";
				}
			}
		// end Check Zone category 0 or 1
		} 

		// check if webhooks plugin installed and if a switch is configured for this zone
		if ($platform == 1 and in_array($row['id'], $switches)) {
			if (strcasecmp($boost_active,'1') == 0) {$boost_state = 'true';} else {$boost_state = 'false';}
			// get the current state of the switch
			$url = "http://127.0.0.1:51828/?accessoryId=switch" . $row['id'];
			$contents = file_get_contents($url);
			$contents = utf8_encode($contents);
			$resp = json_decode($contents, true);
			if ($resp['state']) {$state = 'true';} else {$state = 'false';}
			// update if the states do not match
			if ($state !=  $boost_state) {
				$url = $url . "&state=" . $boost_state;
				$contents = file_get_contents($url);
			}
		}

		//initialize three variable
		$start_cause ='';
		$stop_cause = '';
		$zone_mode = 0;
		if ($zone_fault == '0'){
			if ($zone_category == 0 OR $zone_category == 1) {
				if ($zone_c < $frost_c-$zone_sp_deadband){
					$zone_status="1";
					$zone_mode = 21;
					$start_cause="Frost Protection";
					$zone_state= 1;
				}
				elseif(($zone_c >= $frost_c-$zone_sp_deadband) && ($zone_c < $frost_c)){
					$zone_status=$zone_status_prev;
					$zone_mode = 22 - $zone_status_prev;
					$start_cause="Frost Protection Deadband";
					$stop_cause="Frost Protection Deadband";
					$zone_state = $zone_status_prev;
				}elseif(($zone_c >= $frost_c) && ($zone_c < $zone_max_c) && ($hysteresis=='0')){
					if ($away_status=='0'){
						if (($holidays_status=='0') || ($sch_holidays=='1')) {
							if($boost_status=='0'){
								$zone_status="0";
								$stop_cause="Boost Finished";
								if ($night_climate_status =='0') {
									if (($sch_status =='1') && ($zone_c < $temp_cut_in)&&(($sch_coop == 0)||($boiler_fire_status == "1"))){
										$zone_status="1";
										$zone_mode = 81;
										$start_cause="Schedule Started";
										$expected_end_date_time=date('Y-m-d '.$sch_end_time.'');
										$zone_state = 1;
									}
									if (($sch_status =='1') && ($zone_c < $temp_cut_in)&&($sch_coop == 1)&&($boiler_fire_status == "0")){
										$zone_status="0";
										$zone_mode = 83;
										$stop_cause="Coop Start Schedule Waiting for Boiler Start";
										$expected_end_date_time=date('Y-m-d '.$sch_end_time.'');
										$zone_state = 0;
									}
									if (($sch_status =='1') && ($zone_c >= $temp_cut_in) && ($zone_c < $temp_cut_out)){
										$zone_status=$zone_status_prev;
										$zone_mode = 82 - $zone_status_prev;
										$start_cause="Schedule Target Deadband";
										$stop_cause="Schedule Target Deadband";
										$zone_state = $zone_status_prev;
									}
									if (($sch_status =='1') && ($zone_c >= $temp_cut_out)){
										$zone_status="0";
										$zone_mode = 80;
										$stop_cause="Schedule Target C Achieved";
										$zone_state = 0;
									}
									if (($sch_status =='1') && ($zone_override_status=='1') && ($zone_c < $temp_cut_in)){
										$zone_status="1";
										$zone_mode = 71;
										$start_cause="Schedule Override Started";
										$expected_end_date_time=date('Y-m-d '.$sch_end_time.'');
										$zone_state = 1;
									}
									if (($sch_status =='1') && ($zone_override_status=='1') && ($zone_c >= $temp_cut_in && ($zone_c < $temp_cut_out))){
										$zone_status=$zone_status_prev;
										$zone_mode = 72 - $zone_status_prev;
										$start_cause="Schedule Override Target Deadband";
										$stop_cause="Schedule Override Target Deadband";
										$zone_state = $zone_status_prev;
									}
									if (($sch_status =='1') && ($zone_override_status=='1') && ($zone_c >= $temp_cut_out)){
										$zone_status="0";
										$zone_mode = 70;
										$stop_cause="Schedule Override Target C Achieved";
										$zone_state = 0;
									}
									if (($sch_status =='0') &&($sch_holidays=='1')){
										$zone_status="0";
										$zone_mode = 40;
										$stop_cause="Holidays - No Schedule";
										$zone_state = 0;
									}
									if (($sch_status =='0') && ($sch_holidays=='0')) {
										$zone_status="0";
										$zone_mode = 0;
										$stop_cause="No Schedule";
										$zone_state = 0;
									}
								}elseif(($night_climate_status=='1') && ($zone_c < $temp_cut_in)){
									$zone_status="1";
									$zone_mode = 51;
									$start_cause="Night Climate";
									$expected_end_date_time=date('Y-m-d '.$nc_end_time_rc.'');
									$zone_state = 1;
								}elseif(($night_climate_status=='1') && ($zone_c >= $temp_cut_in) && ($zone_c < $temp_cut_out)){
									$zone_status=$zone_status_prev;
									$zone_mode = 52 - $zone_status_prev;
									$start_cause="Night Climate Deadband";
									$stop_cause="Night Climate Deadband";
									$expected_end_date_time=date('Y-m-d '.$nc_end_time_rc.'');
									$zone_state = $zone_status_prev;
								}elseif(($night_climate_status=='1') && ($zone_c >= $temp_cut_out)){
									$zone_status="0";
									$zone_mode = 50;
									$stop_cause="Night Climate C Reached";
									$expected_end_date_time=date('Y-m-d '.$nc_end_time_rc.'');
									$zone_state = 0;
								}
							}elseif (($boost_status=='1') && ($zone_c < $temp_cut_in)) {
								$zone_status="1";
								$zone_mode = 61;
								$start_cause="Boost Active";
								$expected_end_date_time=date('Y-m-d H:i:s', $boost_time);
								$zone_state = 1;
							}elseif (($boost_status=='1') && ($zone_c >= $temp_cut_in) && ($zone_c < $temp_cut_out)) {
								$zone_status=$zone_status_prev;
								$zone_mode = 62 - $zone_status_prev;
								$start_cause="Boost Target Deadband";
								$stop_cause="Boost Target Deadband";
								$zone_state = $zone_status_prev;
							}elseif (($boost_status=='1') && ($zone_c >= $temp_cut_out)) {
								$zone_status="0";
								$zone_mode = 60;
								$stop_cause="Boost Target C Achived";
								$zone_state = 0;
							}
						}elseif(($holidays_status=='1') && ($sch_holidays=='0')){
							$zone_status="0";
							$zone_mode = 40;
							$stop_cause="Holiday Active";
							$zone_state = 0;
						}
					}elseif($away_status=='1'){
						$zone_status="0";
						$zone_mode = 90;
						$stop_cause="Away Active";
						$zone_state = 0;
					}
				}elseif($zone_c >= $zone_max_c){
					$zone_status="0";
					$zone_mode = 30;
					$stop_cause="Zone Reached its Max Temperature ".$zone_max_c;
					$zone_state = 0;
				} else {
					$zone_status="0";
					$zone_mode = 100;
					$stop_cause="Hysteresis active ";
					$zone_state = 0;
				}
			} else { // end process Zone Category 0 and 1
				// process Zone Category 2
				if ($away_status=='1'){
					$zone_status="0";
					$zone_mode = 90;
          				$zone_state = 0;
					$stop_cause="Away Active";
				} elseif (($holidays_status=='1') && ($sch_holidays=='0')){
					$zone_status="0";
					$zone_mode = 40;
            				$zone_state = 0;
					$stop_cause="Holiday Active";
				} elseif(($boost_status=='0') && ($zone_current_mode == 64)){
					$zone_status="0";
            				$zone_mode = 0;
            				$zone_state = 0;
					$stop_cause="Boost Finished";
        			} elseif (($zone_state == '0') && ($zone_override_status == '0') && ($zone_status_prev == 1)) {
                                        $zone_status="0";
					$zone_mode = 0;
          				$zone_state = 0;
					$stop_cause="Manual Stop";
        			} elseif ($sch_status == '0' && $zone_state == '0' && $boost_status == '0') {
				  	$zone_status="0";
					$zone_mode = 0;
					$stop_cause="No Schedule";
        			} elseif ($sch_status =='1') {
					if ($zone_override_status=='0') {
					  	$zone_status="1";
						$zone_mode = 114;
          					$zone_state = 1;
						$start_cause="Schedule Started";
						$expected_end_date_time=date('Y-m-d '.$sch_end_time.'');
					} else {
	                                        $zone_status = (($add_on_state == 1) || ($zone_state == 1)) ? "1":"0" ;
        	                                $zone_mode = 74 + $add_on_state;
                	                        $zone_state = $add_on_state;
                        	                $stop_cause="Manual Override Started";
                                	        $expected_end_date_time=date('Y-m-d '.$sch_end_time.'');
					}
				} elseif ($boost_status=='1') {
				  	$zone_status="1";
					$zone_mode = 64;
					$start_cause="Boost Active";
          				$zone_state = 1;
					$expected_end_date_time=date('Y-m-d H:i:s', $boost_time);
					$expected_end_date_time=date('Y-m-d '.$sch_end_time.'');
				} elseif ($zone_state == 1) {
					if ($zone_current_mode == 114) {
	                                        $zone_status="0";
        	                                $zone_mode = 0;
	                                        $zone_state = 0;
                	                        $stop_cause="Schedule Finished";
					} else {
                                        	$zone_status="1";
                                        	$zone_mode = 111;
	                                        $zone_state = 1;
                                        	$start_cause="Manual Start";
					}
				}
			} // end process
		} else {
			$zone_status="0";
			$zone_mode = 10;
			$zone_state = 0;
			$stop_cause="Zone fault";
		}
                $query = "UPDATE zone SET sync = '0', zone_state = {$zone_state} WHERE id = '{$zone_id}' LIMIT 1";
                $conn->query($query);
		if ($debug_msg == 1) { echo "sch_status ".$sch_status.", zone_state ".$zone_state.", boost_status ".$boost_status.", override_status ".$zone_override_status.", zone_current_mode ".$zone_current_mode.", zone_status_prev ".$zone_status_prev."\n"; }
		// Update the individual zone controller states for controllers associated with this zone
		for ($crow = 0; $crow < count($zone_controllers); $crow++){
			$zone_controllers[$crow]['zone_controller_state'] = $zone_state;
		} // for ($crow = 0; $crow < count($zone_controllers); $crow++)

		//Update temperature values fore zone current status table (frost protection and overtemperature)
		if (floor($zone_mode/10) == 2 ) { $target_c= $frost_c;$temp_cut_in = $frost_c-$zone_sp_deadband; $temp_cut_out = $frost_c;}
		if (floor($zone_mode/10) == 3 ) { $target_c= $zone_max_c;$temp_cut_in = 0; $temp_cut_out = 0;}
		//reset if temperature control is not active
		if ((floor($zone_mode/10) == 0 ) || (floor($zone_mode/10) == 1 ) || (floor($zone_mode/10) == 4 ) || (floor($zone_mode/10) == 9 )||(floor($zone_mode/10) == 10 ))  { $target_c= 0;$temp_cut_in = 0; $temp_cut_out = 0;}

		//***************************************************************************************
		//update zone_current_state table
		//***************************************************************************************
		//Zone Main Mode
		/*	0 - idle
			10 - fault
			20 - frost
			30 - overtemperature
			40 - holiday
			50 - nightclimate
			60 - boost
			70 - override
			80 - sheduled
			90 - away
			100 - hysteresis 
			110 - Add-On*/

			//Zone sub mode - running/ stopped different types
		/*	0 - stopped (above cut out setpoint or not running in this mode)
			1 - running 
			2 - stopped (within deadband) 
			3 - stopped (coop start waiting for boiler)
			4 - manual operation ON
			5 - manual operation OFF */

		$query = "UPDATE zone_current_state SET `sync` = 0, mode = {$zone_mode}, status = {$zone_status}, temp_reading = '{$zone_c}', temp_target = {$target_c},temp_cut_in = {$temp_cut_in}, temp_cut_out = {$temp_cut_out}, controler_fault = {$zone_ctr_fault}, controler_seen_time = '{$controler_seen}', sensor_fault  = {$zone_sensor_fault}, sensor_seen_time = '{$sensor_seen}', sensor_reading_time = '{$temp_reading_time}' WHERE zone_id ={$zone_id} LIMIT 1;";
		$conn->query($query);

		if ($zone_category == 0 OR $zone_category == 1) {
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone: Sensor Reading     \033[41m".$zone_c."\033[0m \n";
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone: Weather Factor     \033[41m".$weather_fact."\033[0m \n";
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone: DeadBand           \033[41m".$zone_sp_deadband."\033[0m \n";
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone: Cut In Temperature        \033[41m".$temp_cut_in."\033[0m \n";
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone: Cut Out Temperature       \033[41m".$temp_cut_out."\033[0m \n";
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone: Mode       \033[41m".$zone_mode."\033[0m \n";
		}
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone ID: \033[41m".$zone_id. "\033[0m \n";
                for ($crow = 0; $crow < count($zone_controllers); $crow++){
                        $zone_controler_id = $zone_controllers[$crow]["controler_id"];
                        $zone_controler_child_id = $zone_controllers[$crow]["controler_child_id"];
                        echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone: ".$zone_name." Controller: \033[41m".$zone_controler_id."\033[0m Controller Child: \033[41m".$zone_controler_child_id."\033[0m Zone Status: \033[41m".$zone_status."\033[0m \n";
                }
		if ($zone_category == 0) {
			if ($zone_status=='1') {echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone: ".$zone_name." Start Cause: ".$start_cause." - Target C:\033[41m".$target_c."\033[0m Zone C:\033[31m".$zone_c."\033[0m \n";}
			if ($zone_status=='0') {echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone: ".$zone_name." Stop Cause: ".$stop_cause." - Target C:\033[41m".$target_c."\033[0m Zone C:\033[31m".$zone_c."\033[0m \n";}
		} else {
			if ($zone_status=='1') {echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone: ".$zone_name." Start Cause: ".$start_cause."\033[0m \n";}
			if ($zone_status=='0') {echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone: ".$zone_name." Stop Cause: ".$stop_cause."\033[0m \n";}
		}

		//Pass data to zone commands loop
                $zone_commands[$command_index] = (array('controllers' =>$zone_controllers, 'zone_id' =>$zone_id, 'zone_name' =>$zone_name, 'zone_category' =>$zone_category, 'zone_status'=>$zone_status, 'zone_status_prev'=>$zone_status_prev, 'zone_overrun_prev'=>$zone_overrun_prev, 'zone_override_status'=>$zone_override_status));
		$command_index = $command_index+1;
		//process Zone Cat 0 logs
		if ($zone_category == 0){
			//all zone status to boiler array and increment array index
			$boiler[$boiler_index] = $zone_status;
			$boiler_index = $boiler_index+1;
			//all zone ids and status to multidimensional Array. and increment array index.
			$zone_log[$zone_index] = (array('zone_id' =>$zone_id, 'status'=>$zone_status));
			$zone_index = $zone_index+1;
			//process Zone Cat 1 and 2 logs
		} else {
			// Process Logs Category 1 and 2 logs if zone status has changed
			// zone switching ON
			if($zone_status_prev == '0' &&  ($zone_status == '1' || $zone_state  == '1')) {
				if($zone_mode == '111' || $zone_mode == '21' ||  $zone_mode == '10') {
					$aoquery = "INSERT INTO `add_on_logs`(`sync`, `purge`, `start_datetime`, `start_cause`, `stop_datetime`, `stop_cause`, `expected_end_date_time`) VALUES ('0', '0', '{$date_time}', '{$start_cause}', NULL, NULL, NULL);";
				} else {
					$aoquery = "INSERT INTO `add_on_logs`(`sync`, `purge`, `start_datetime`, `start_cause`, `stop_datetime`, `stop_cause`, `expected_end_date_time`) VALUES ('0', '0', '{$date_time}', '{$start_cause}', NULL, NULL,'{$expected_end_date_time}');";
				}
				$result = $conn->query($aoquery);
				$add_on_log_id = mysqli_insert_id($conn);

				//echo all zone and status
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone ID: ".$zone_id." Status: ".$zone_status."\n";
				$zlquery = "INSERT INTO `add_on_zone_logs`(`sync`, `purge`, `zone_id`, `add_on_log_id`, `status`) VALUES (0, 0, '{$zone_id}', '{$add_on_log_id}', '{$zone_status}');";
				$zlresults = $conn->query($zlquery);
				if ($zlresults) {echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Add-On Zone Log table updated successfully. \n";} else {echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone log update failed... ".mysql_error(). " \n";}
				if ($result) {
					echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Add-On Log table added Successfully. \n";
				}else {
					echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Add-On Log table addition failed. \n";
				}
			// zone switching OFF
			} elseif ($zone_status_prev == '1' &&  $zone_status == '0') {
				$query = "UPDATE add_on_logs SET stop_datetime = '{$date_time}', stop_cause = '{$stop_cause}' ORDER BY id DESC LIMIT 1";
				$result = $conn->query($query);
				if ($result) {
					echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Add-On Log table updated Successfully. \n";
				}else {
					echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Add-On Log table update failed. \n";
				}
			}
		} //end process Zone Cat 1 and 2 logs
		echo "---------------------------------------------------------------------------------------- \n";
        } //end if($zone_status == 1)
} //end of while loop

/***************************************************************************************
                                   Zone Commands loop
 ***************************************************************************************/
for ($row = 0; $row < count($zone_commands); $row++){
        $zone_id = $zone_commands[$row]["zone_id"];
        $zone_category = $zone_commands[$row]["zone_category"];
        $zone_name = $zone_commands[$row]["zone_name"];
        $zone_status = $zone_commands[$row]["zone_status"];
        $zone_status_prev = $zone_commands[$row]["zone_status_prev"];
        $zone_overrun_prev = $zone_commands[$row]["zone_overrun_prev"];
        $zone_override_status = $zone_commands[$row]["zone_override_status"];
        $controllers = $zone_commands[$row]["controllers"];

	//Zone category 0 and boiler is not requested calculate if overrun needed
	if($zone_category==0 && !in_array("1", $boiler)) {
		//overrun time <0 - latch overrun for the zone zone untill next boiler start
		if($boiler_overrun_time < 0){
			$zone_overrun = (($zone_status_prev == 1)||($zone_overrun_prev == 1)) ? 1:0;
		// overrun time = 0 - overrun not needed	 
		}else if ($boiler_overrun_time == 0){
			$zone_overrun = 0;
		// overrun time > 0	
		}else {
			$now=strtotime(date('Y-m-d H:i:s'));
			//when switching off boiler
			if($boiler_fire_status == 1) {
				$overrun_end_time = $now + ($boiler_overrun_time * 60);
				$zone_overrun = $zone_status_prev;
			//boiler was switched of previous script run
			} else {
				$overrun_end_time = strtotime( $boiler_stop_datetime ) + ($boiler_overrun_time * 60);
				// if overrun flag was switched on when boiler was switching on and ovverrun did not pass keep overrun on
				if(($now < $overrun_end_time)&&($zone_overrun_prev)){
					$zone_overrun = 1;
				}else {
					$zone_overrun = 0;
				}
			}
		}
	}else {
		//if zone is not category 0 or boiler is running overrun not needed
		$zone_overrun = 0;
	}
	$zone_command = (($zone_status == 1) || ($zone_overrun == 1)) ? 1:0 ;
	
	// update zone current state table
	if($zone_overrun<>$zone_overrun_prev){
		$query = "UPDATE zone_current_state SET `sync` = 0, overrun = {$zone_overrun} WHERE id ={$zone_id} LIMIT 1;";
		$conn->query($query);
	}
	if($zone_overrun == 1){
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone ".$zone_id. " circulation pump overrun active. \n";
		if ($boiler_overrun_time > 0) {
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Overrun end time ".date('Y-m-d H:i:s',$overrun_end_time). " \n";
		} else{
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Overrun will end on the next boiler start. \n";
		}
	}

        for ($crow = 0; $crow < count($controllers); $crow++){
		$zc_id = $controllers[$crow]["zc_id"];
                $zone_controler_id = $controllers[$crow]["controler_id"];
                $zone_controler_child_id = $controllers[$crow]["controler_child_id"];
                $zone_controller_type = $controllers[$crow]["zone_controller_type"];
                $manual_button_override = $controllers[$crow]["manual_button_override"];
		$zone_controller_state = $controllers[$crow]["zone_controller_state"];
		$zone_command = (($zone_controller_state == 1) || ($zone_overrun == 1)) ? 1:0 ;
		if ($debug_msg == 1) { echo $zone_controler_id."-".$zone_controler_child_id.", ".$zone_controller_state.", ".$manual_button_override."\n"; }
		if (($manual_button_override == 0) || ($manual_button_override == 1 && $zone_command == 0)) {
			/***************************************************************************************
			Zone Valve Wired to Raspberry Pi GPIO Section: Zone Valve Connected Raspberry Pi GPIO.
			****************************************************************************************/
			if ($zone_controller_type == 'GPIO'){
		    		$relay_status = ($zone_command == '1') ? $relay_on : $relay_off;
		    		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone: GIOP Relay Status: \033[41m".$relay_status. "\033[0m (".$relay_on."=On, ".$relay_off."=Off) \n";
		    		exec("python3 /var/www/cron/gpio/gpio3_relay.py ".$zone_controler_child_id." ".$relay_status);
			}

			/***************************************************************************************
			Zone Valve Wired over I2C Interface Make sure you have i2c Interface enabled 
			****************************************************************************************/
			if ($zone_controller_type == 'I2C'){
				exec("python3 /var/www/cron/i2c/i2c_relay.py ".$zone_controler_id." ".$zone_controler_child_id." ".$zone_command);
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone Relay Broad: ".$zone_controler_id. " Relay No: ".$zone_controler_child_id." Status: ".$zone_command." \n";
			}

			/***************************************************************************************
			Zone Valve Wireless Section: MySensors Wireless Relay module for your Zone Valve control.
			****************************************************************************************/
			if ($zone_controller_type == 'MySensor'){
				//update messages_out table with sent status to 0 and payload to as zone status.
				$query = "UPDATE messages_out SET sent = '0', payload = '{$zone_command}' WHERE node_id ='$zone_controler_id' AND child_id = '$zone_controler_child_id' LIMIT 1;";
				$conn->query($query);
			}
			/***************************************************************************************
			Sonoff Switch Section: Tasmota WiFi Relay module for your Zone control.
			****************************************************************************************/
        		if ($zone_controller_type == 'Tasmota'){
                		$query = "SELECT * FROM http_messages WHERE zone_name = '$zone_name' AND message_type = '$zone_command' LIMIT 1;";
	                	$result = $conn->query($query);
	        	        $http = mysqli_fetch_array($result);
        	        	$add_on_msg = $http['command'].' '.$http['parameter'];
	        	        $query = "UPDATE messages_out SET sent = '0', payload = '{$add_on_msg}' WHERE node_id ='$zone_controler_id' AND child_id = '$zone_controler_child_id' LIMIT 1;";
        	        	$conn->query($query);
	        	}

			if ($zone_category <= 2) {
				if ($zone_override_status == 0) {
					$query = "UPDATE zone_controllers SET state = {$zone_command}, current_state = {$zone_command} WHERE id = {$zc_id} LIMIT 1;";
					$conn->query($query);
				}
			}
		} //end if ($manual_button_override == 0) {
	} //end for ($crow = 0; $crow < count($controllers); $crow++)
} //end for ($row = 0; $row < count($zone_commands); $row++)

//For debug info only
//print_r ($zone_log);
//count($zone_log)
//print_r ($boiler);
if (isset($boiler_stop_datetime)) {echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Boiler Switched Off At: ".$boiler_stop_datetime. "\n";}
if (isset($expected_end_date_time)){echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Boiler Expected End Time: ".$expected_end_date_time. "\n"; }

/******************************
      Boiler On section
/******************************/
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
	if ($boiler_controller_type == 'MySensor'){
		$query = "UPDATE messages_out SET sent = '0', payload = '{$new_boiler_status}' WHERE node_id ='{$boiler_node_id}' AND child_id = '{$boiler_node_child_id}' LIMIT 1;";
		$conn->query($query);
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Boiler Node ID: \033[41m".$boiler_node_id."\033[0m Child ID: \033[41m".$boiler_node_child_id."\033[0m \n";
	}

	/*****************************************
	Boiler Wired to Raspberry Pi GPIO Section.
	******************************************/
	if ($boiler_controller_type == 'GPIO'){
		exec("python3 /var/www/cron/gpio/gpio3_relay.py " .$boiler_node_child_id ." ".$relay_on );
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Boiler GIOP: \033[41m".$boiler_node_child_id. "\033[0m Status: \033[41m".$relay_on."\033[0m (".$relay_on."=On, ".$relay_off."=Off) \n";
	}
	
	/***************************************************************************************
	Boiler Wired over I2C Interface Make sure you have i2c Interface enabled 
	****************************************************************************************/
	if ($boiler_controller_type == 'I2C'){
		exec("python3 /var/www/cron/i2c/i2c_relay.py" .$boiler_node_id." ".$boiler_node_child_id." 1"); 
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Boiler I2C Rrelay Board: \033[41m".$boiler_node_id."\033[0m Relay ID: \033[41m".$boiler_node_child_id."\033[0m \n";
	}
	//Update Boiler Status 
	if ($boiler_fire_status != $new_boiler_status){
		//insert date and time into boiler log table so we can record boiler start date and time.
                if (isset($expected_end_date_time)) {
			$bsquery = "INSERT INTO `boiler_logs`(`sync`, `purge`, `start_datetime`, `start_cause`, `stop_datetime`, `stop_cause`, `expected_end_date_time`) VALUES ('0', '0', '{$date_time}', '{$start_cause}', NULL, NULL,'{$expected_end_date_time}');";
		} else {
			$bsquery = "INSERT INTO `boiler_logs`(`sync`, `purge`, `start_datetime`, `start_cause`, `stop_datetime`, `stop_cause`, `expected_end_date_time`) VALUES ('0', '0', '{$date_time}', '{$start_cause}', NULL, NULL,NULL);";
		}
		$result = $conn->query($bsquery);
		$boiler_log_id = mysqli_insert_id($conn);
		//echo all zone and status
		for ($row = 0; $row < count($zone_log); $row++){
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone ID: ".$zone_log[$row]["zone_id"]." Status: ".$zone_log[$row]["status"]."\n";
			$zlquery = "INSERT INTO `zone_logs`(`sync`, `purge`, `zone_id`, `boiler_log_id`, `status`) VALUES ('0', '0', '{$zone_log[$row]["zone_id"]}', '{$boiler_log_id}', '{$zone_log[$row]["status"]}');";
			$zlresults = $conn->query($zlquery);
			if ($zlresults) {echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone Log table updated successfully. \n";} else {echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone log update failed... ".mysql_error(). " \n";}
		}
		if ($result) {
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Boiler Log table added Successfully. \n";
		}else {
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Boiler Log table addition failed. \n";
		}
	}

/******************************
      Boiler Off section
/******************************/
}else{
	$new_boiler_status='0';
	//update boiler fired status to 0
	$query = "UPDATE boiler SET sync = '0', fired_status = '{$new_boiler_status}' WHERE id ='1' LIMIT 1";
	$conn->query($query);

	/***************************************************************************************
	GAS Boiler Wirelss Section:	MySensors Wireless Relay module for your GAS Boiler control
	****************************************************************************************/
	if ($boiler_controller_type == 'MySensor'){
		//update messages_out table with sent status to 0 and payload to as boiler status.
		$query = "UPDATE messages_out SET sent = '0', payload = '{$new_boiler_status}' WHERE node_id ='{$boiler_node_id}' AND child_id = '{$boiler_node_child_id}' LIMIT 1;";
		$conn->query($query);
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Boiler Node ID: \033[41m".$boiler_node_id."\033[0m Child ID: \033[41m".$boiler_node_child_id."\033[0m \n";
	}

	/*****************************************
	Boiler Wired to Raspberry Pi GPIO Section.
	******************************************/
	if ($boiler_controller_type == 'GPIO'){
		exec("python3 /var/www/cron/gpio/gpio3_relay.py " .$boiler_node_child_id ." ".$relay_off );
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Boiler GIOP: \033[41m".$boiler_node_child_id. "\033[0m Status: \033[41m".$relay_off."\033[0m (".$relay_on."=On, ".$relay_off."=Off) \n";
	}

	/***************************************************************************************
	Boiler Wired over I2C Interface Make sure you have i2c Interface enabled 
	****************************************************************************************/
	if ($boiler_controller_type == 'I2C'){
		exec("python3 /var/www/cron/i2c/i2c_relay.py" .$boiler_node_id." ".$boiler_node_child_id." 0");
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Boiler I2C Rrelay Board: \033[41m".$boiler_node_id."\033[0m Relay ID: \033[41m".$boiler_node_child_id."\033[0m \n";
	}

	//Update last record with boiler stop date and time in boiler log table.
	if ($boiler_fire_status != $new_boiler_status){
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
	$query = "select * from user LIMIT 1;";
	$result = $conn->query($query);
	$user_row = mysqli_fetch_array($result);
	$email = $user_row['email'];
	echo "---------------------------------------------------------------------------------------- \n";
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Calling Home \n";
	$external_ip = file_get_contents('http://www.pihome.eu/piconnect/myip.php');
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
	$url="http://www.pihome.eu/piconnect/callhome.php?ip=${external_ip}&serial=${uid}&cpu_model=${cpu_model}&hardware=${hardware}&revision=${revision}&ph_version=${ph_version}&ph_build=${ph_build}&email=${email}";
	//echo $url."\n";
	$result = url_get_contents($url);
	$result = file_get_contents($url);
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - PiHome Says: ".$result."\n";
	echo "---------------------------------------------------------------------------------------- \n";
}

echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Boiler Fired Status: \033[41m".$new_boiler_status."\033[0m \n";
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Boiler Hysteresis Status: \033[41m".$hysteresis."\033[0m \n";
echo "---------------------------------------------------------------------------------------- \n";
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Boiler Script Ended \n";
echo "\033[32m****************************************************************************************\033[0m  \n";
if(isset($conn)) { $conn->close();}
?>

