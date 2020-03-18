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
echo "*   Boiler Script Version 0.53 Build Date 31/01/2018  *\n";
echo "*   Update on 10/07/2019                              *\n";
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
$boiler_controller_type = $row['controller_type'];
$boiler_node_id = $row['node_id'];
$boiler_node_child_id = $row['node_child_id'];
$boiler_hysteresis_time = $row['hysteresis_time'];
$boiler_max_operation_time = $row['max_operation_time'];

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

echo "---------------------------------------------------------------------------------------- \n";
//following variable set to 0 on start for array index.
$boiler_index = '0';
$zone_index = '0';

//following variable set to current day of the week.
$dow = idate('w');
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Day of the Week: \033[41m".$dow. "\033[0m \n";
//echo $dow."\n";
$query = "SELECT * FROM zone_view where status = 1 order by index_id asc;";
$results = $conn->query($query);
while ($row = mysqli_fetch_assoc($results)) {
	$zone_id=$row['id'];
	$zone_name=$row['name'];
	$zone_max_c=$row['max_c'];
	$zone_max_operation_time=$row['max_operation_time'];
	$zone_hysteresis_time=$row['hysteresis_time'];
	$zone_sp_deadband=$row['sp_deadband'];
	$zone_sensor_id=$row['sensors_id'];
	$zone_sensor_child_id=$row['sensor_child_id'];
	$zone_controller_type=$row['controller_type'];
	$zone_controler_id=$row['controler_id'];
	$zone_controler_child_id=$row['controler_child_id'];

	//query to get temperature from messages_in_view_24h table view
	$query = "SELECT * FROM messages_in_view_24h WHERE node_id = '{$zone_sensor_id}' AND child_id = {$zone_sensor_child_id} ORDER BY datetime desc LIMIT 1;";
	$result = $conn->query($query);
	$sensor = mysqli_fetch_array($result);
	$zone_c = $sensor['payload'];
	$temp_reading_time = $sensor['datetime'];

	//Have to account for midnight rollover conditions
	if($holidays_status == 0) {
		$query = "SELECT * FROM schedule_daily_time_zone_view WHERE ((`end`>`start` AND CURTIME() between `start` AND `end`) OR (`end`<`start` AND CURTIME()<`end`) OR (`end`<`start` AND CURTIME()>`start`)) AND zone_id = {$zone_id} AND time_status = '1' AND (WeekDays & (1 << {$dow})) > 0 AND holidays_id IS NULL LIMIT 1;";
	}else{
    $query = "SELECT * FROM schedule_daily_time_zone_view WHERE ((`end`>`start` AND CURTIME() between `start` AND `end`) OR (`end`<`start` AND CURTIME()<`end`) OR (`end`<`start` AND CURTIME()>`start`)) AND zone_id = {$zone_id} AND time_status = '1' AND (WeekDays & (1 << {$dow})) > 0 AND holidays_id IS NOT NULL LIMIT 1;";
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

		if (isset($schedule['holidays_id'])) {
			$sch_holidays = '1';
		}
	}

	//query to check override status and get temperature from override table
	$query = "SELECT * FROM override WHERE zone_id = {$zone_id} LIMIT 1;";
	$result = $conn->query($query);
	$override = mysqli_fetch_array($result);
	$override_status = $override['status'];
	$override_c = $override['temperature'];

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

	//query to check night climate status and get temperature from night climate table
	$query = "select * from schedule_night_climat_zone_view WHERE zone_id = {$zone_id} LIMIT 1;";
	$result = $conn->query($query);
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

$zone_temp = $zone_c + $weather_fact + $zone_sp_deadband;
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone: Sensor Reading     \033[41m".$zone_c."\033[0m \n";
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone: Weather Factor     \033[41m".$weather_fact."\033[0m \n";
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone: DeadBand           \033[41m".$zone_sp_deadband."\033[0m \n";
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone: Temperature        \033[41m".$zone_temp."\033[0m \n";
$zone_c = $zone_c + $weather_fact; //Add to Actual Zone Temperature to Predict Accurate Temperature
 

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

	//Get previous command for zone for use with deadband
	$query = "SELECT * FROM messages_out WHERE node_id ='$zone_controler_id' AND child_id = '$zone_controler_child_id' LIMIT 1;";
	$result = $conn->query($query);
	$command_out = mysqli_fetch_array($result);
	$zone_status_prev = $command_out['payload'];

	//Get data from nodes table
	$query = "SELECT * FROM nodes WHERE node_id ='$zone_controler_id' AND status IS NOT NULL LIMIT 1;";
	$result = $conn->query($query);
	$controler_node = mysqli_fetch_array($result);
	$controler_seen = $controler_node['last_seen'];
	$controler_notice = $controler_node['notice_interval'];

	$query = "SELECT * FROM nodes WHERE node_id ='$zone_sensor_id' AND status IS NOT NULL LIMIT 1;";
	$result = $conn->query($query);
	$sensor_node = mysqli_fetch_array($result);
	$sensor_seen = $sensor_node['last_seen']; //not using this cause it updates on battery update
	$sensor_notice = $sensor_node['notice_interval'];

	//Calculate zone fail
	$zone_fault = 0;
	if($controler_notice > 0){
		$now=strtotime(date('Y-m-d H:i:s'));
		$controler_seen_time = strtotime($controler_seen);
		if ($controler_seen_time  < ($now - ($controler_notice*60))){
			$zone_fault = 1;
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone valve communication timeout for This Zone. Node Last Seen: ".$controler_seen."\n";
		}
	}
	if($sensor_notice > 0) {
      $now=strtotime(date('Y-m-d H:i:s'));
      $sensor_seen_time = strtotime($temp_reading_time); //using time from messages_in
      if ($sensor_seen_time  < ($now - ($sensor_notice*60))){
          $zone_fault = 1;
          echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Temperature sensor communication timeout for This Zone. Last temperature reading: ".$temp_reading_time."\n";
      }
	}
  if($boiler_notice > 0){
		$now=strtotime(date('Y-m-d H:i:s'));
		$boiler_seen_time = strtotime($boiler_seen);
		if ($boiler_seen_time  < ($now - ($boiler_notice*60))){
			$zone_fault = 1;
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Boiler controler communication timeout. Boiler Last Seen: ".$boiler_seen."\n";
		}
	}


	//initialize two variable
	$start_cause ='';
	$stop_cause = '';
	if ($zone_fault == '0'){
	if ($zone_c < $frost_c-$zone_sp_deadband){$zone_status="1"; $start_cause="Frost Protection";}
		elseif(($zone_c >= $frost_c-$zone_sp_deadband) && ($zone_c < $frost_c)){$zone_status=$zone_status_prev; $start_cause="Frost Protection Deadband"; $stop_cause="Frost Protection Deadband";}
		elseif(($zone_c >= $frost_c) && ($zone_c < $zone_max_c) && ($hysteresis=='0')){
			if ($away_status=='0'){
				if (($holidays_status=='0') || ($sch_holidays=='1')) {
					if($boost_status=='0'){$zone_status="0"; $stop_cause="Boost Finished";
						if ($night_climate_status =='0') {
							if (($sch_status =='1') && ($zone_c < $target_c-$zone_sp_deadband)&&(($sch_coop == 0)||($boiler_fire_status == "1"))){$zone_status="1"; $start_cause="Schedule Started"; $expected_end_date_time=date('Y-m-d '.$sch_end_time.''); }
							if (($sch_status =='1') && ($zone_c < $target_c-$zone_sp_deadband)&&($sch_coop == 1)&&($boiler_fire_status == "0")){$zone_status="0"; $stop_cause="Coop Start Schedule Waiting for Boiler Start"; $expected_end_date_time=date('Y-m-d '.$sch_end_time.''); }
							if (($sch_status =='1') && ($zone_c >= $target_c-$zone_sp_deadband) && ($zone_c < $target_c)){$zone_status=$zone_status_prev; $start_cause="Schedule Target Deadband"; $stop_cause="Schedule Target Deadband"; }
							if (($sch_status =='1') && ($zone_c >= $target_c)){$zone_status="0"; $stop_cause="Schedule Target C Achieved"; }
							if (($sch_status =='1') && ($override_status=='1') && ($zone_c < $target_c-$zone_sp_deadband)){$zone_status="1"; $start_cause="Schedule Override Started"; $expected_end_date_time=date('Y-m-d '.$sch_end_time.'');}
							if (($sch_status =='1') && ($override_status=='1') && ($zone_c >= $target_c-$zone_sp_deadband) && ($zone_c < $target_c)){$zone_status=$zone_status_prev; $start_cause="Schedule Override Target Deadband"; $stop_cause="Schedule Override Target Deadband";}
							if (($sch_status =='1') && ($override_status=='1') && ($zone_c >= $target_c)){$zone_status="0"; $stop_cause="Schedule Override Target C Achieved";}
							if (($sch_status =='0') &&($override_status=='0')){$zone_status="0"; $stop_cause="No Schedule For This Zone \n"; }
							if ($sch_status=='0') {$zone_status="0"; $stop_cause="No Schedule"; }
						}elseif(($night_climate_status=='1') && ($zone_c < $target_c-$zone_sp_deadband)){$zone_status="1"; $start_cause="Night Climate"; $expected_end_date_time=date('Y-m-d '.$nc_end_time_rc.'');
						}elseif(($night_climate_status=='1') && ($zone_c >= $target_c-$zone_sp_deadband) && ($zone_c < $target_c)){$zone_status=$zone_status_prev; $start_cause="Night Climate Deadband"; $stop_cause="Night Climate Deadband"; $expected_end_date_time=date('Y-m-d '.$nc_end_time_rc.'');
						}elseif(($night_climate_status=='1') && ($zone_c >= $target_c)){$zone_status="0"; $stop_cause="Night Climate C Reached"; $expected_end_date_time=date('Y-m-d '.$nc_end_time_rc.'');}
					}elseif (($boost_status=='1') && ($zone_c < $target_c-$zone_sp_deadband)) {$zone_status="1"; $start_cause="Boost Active"; $expected_end_date_time=date('Y-m-d H:i:s', $boost_time);
					}elseif (($boost_status=='1') && ($zone_c >= $target_c-$zone_sp_deadband) && ($zone_c < $target_c)) {$zone_status=$zone_status_prev; $start_cause="Boost Target Deadband"; $stop_cause="Boost Target Deadband";
					}elseif (($boost_status=='1') && ($zone_c >= $target_c)) {$zone_status="0"; $stop_cause="Boost Target C Achived";}
				}elseif(($holidays_status=='1') && ($sch_holidays=='0')){$zone_status="0"; $stop_cause="Holiday Active";}
			}elseif($away_status=='1'){$zone_status="0"; $stop_cause="Away Active";}
		}elseif($zone_c >= $zone_max_c){$zone_status="0"; $stop_cause="Zone Reached its Max Temperature ".$zone_max_c;}
		else{$zone_status="0"; $stop_cause="Hysteresis active ";}
	}else{$zone_status="0"; $stop_cause="Zone fault";}
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone ID: \033[41m".$zone_id. "\033[0m \n";
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone: ".$zone_name." Controller: \033[41m".$zone_controler_id."\033[0m Controller Child: \033[41m".$zone_controler_child_id."\033[0m Zone Status: \033[41m".$zone_status."\033[0m \n";
	if ($zone_status=='1') {echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone: ".$zone_name." Start Cause: ".$start_cause." - Target C:\033[41m".$target_c."\033[0m Zone C:\033[31m".$zone_c."\033[0m \n";}
	if ($zone_status=='0') {echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone: ".$zone_name." Stop Cause: ".$stop_cause." - Target C:\033[41m".$target_c."\033[0m Zone C:\033[31m".$zone_c."\033[0m \n";}

	/***************************************************************************************
	Zone Valve Wired to Raspberry Pi GPIO Section: Zone Vole Connected Raspberry Pi GPIO.
	****************************************************************************************/
	if ($zone_controller_type == 'GPIO'){
		$relay_status = ($zone_status == '1') ? $relay_on : $relay_off;
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone: GIOP Relay Status: \033[41m".$relay_status. "\033[0m (0=On, 1=Off) \n";
		exec("/usr/local/bin/gpio write ".$zone_controler_child_id." ".$relay_status );
		exec("/usr/local/bin/gpio mode ".$zone_controler_child_id." out");
	}
	
	/***************************************************************************************
	Zone Valve Wired over I2C Interface Make sure you have i2c Interface enabled 
	****************************************************************************************/
	if ($zone_controller_type == 'I2C'){
		//exec("python /var/www/cron/i2c/i2c_relay.py 50 ".$zone_gpio_pin." ".$zone_status);
		exec("python /var/www/cron/i2c/i2c_relay.py ".$zone_controler_id." ".$zone_controler_child_id." ".$zone_status);
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone Relay Broad: ".$zone_controler_id. " Relay No: ".$zone_controler_child_id." Status: ".$zone_status." \n";
	}

	/***************************************************************************************
	Zone Vole Wireless Section: MySensors Wireless Relay module for your Zone vole control.
	****************************************************************************************/
	if ($zone_controller_type == 'MySensor'){
		//update messages_out table with sent status to 0 and payload to as zone status.
		$query = "UPDATE messages_out SET sent = '0', payload = '{$zone_status}' WHERE node_id ='$zone_controler_id' AND child_id = '$zone_controler_child_id' LIMIT 1;";
		$conn->query($query);
	}

	//all zone status to boiler array and increment array index
	$boiler[$boiler_index] = $zone_status;
	$boiler_index = $boiler_index+1;

	//all zone ids and status to multidimensional Array. and increment array index.
	$zone_log[$zone_index] = (array('zone_id' =>$zone_id, 'status'=>$zone_status));
	$zone_index = $zone_index+1;
	echo "---------------------------------------------------------------------------------------- \n";
} //end of while loop

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

	/***************************************************************************************
	Boiler Wired to Raspberry Pi GPIO Section: Make sure you have WiringPi installed.
	****************************************************************************************/
	if ($boiler_controller_type == 'GPIO'){
		exec("/usr/local/bin/gpio write ".$boiler_node_child_id ." ".$relay_on );
		exec("/usr/local/bin/gpio mode ".$boiler_node_child_id ." out");
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Boiler GIOP: \033[41m".$boiler_node_child_id. "\033[0m Status: \033[41m".$relay_on."\033[0m (0=On, 1=Off) \n";
	}
	
	/***************************************************************************************
	Boiler Wired over I2C Interface Make sure you have i2c Interface enabled 
	****************************************************************************************/
	if ($boiler_controller_type == 'I2C'){
		exec("python /var/www/cron/i2c/i2c_relay.py" .$boiler_node_id." ".$boiler_node_child_id." 1"); 
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Boiler I2C Rrelay Board: \033[41m".$boiler_node_id."\033[0m Relay ID: \033[41m".$boiler_node_child_id."\033[0m \n";
	}

	//Update Boiler Status 
	if ($boiler_fire_status != $new_boiler_status){
		//insert date and time into boiler log table so we can record boiler start date and time.
		$bsquery = "INSERT INTO `boiler_logs`(`sync`, `purge`, `start_datetime`, `start_cause`, `stop_datetime`, `stop_cause`, `expected_end_date_time`) VALUES ('0', '0', '{$date_time}', '{$start_cause}','', '', '{$expected_end_date_time}');";
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

	/***************************************************************************************
	Boiler Wired to Raspberry Pi GPIO Section: Make sure you have WiringPi installed.
	****************************************************************************************/
	if ($boiler_controller_type == 'GPIO'){
		exec("/usr/local/bin/gpio write ".$boiler_node_child_id ." ".$relay_off );
		exec("/usr/local/bin/gpio mode ".$boiler_node_child_id ." out");
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Boiler GIOP: \033[41m".$boiler_node_child_id. "\033[0m Status: \033[41m".$relay_off."\033[0m (0=On, 1=Off) \n";
	}

	/***************************************************************************************
	Boiler Wired over I2C Interface Make sure you have i2c Interface enabled 
	****************************************************************************************/
	if ($boiler_controller_type == 'I2C'){
		exec("python /var/www/cron/i2c/i2c_relay.py" .$boiler_node_id." ".$boiler_node_child_id." 0");
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
	$url="http://www.pihome.eu/piconnect/callhome.php?ip=${external_ip}&serial=${uid}&cpu_model=${cpu_model}&hardware=${hardware}&revision=${revision}&ph_version=${ph_version}&ph_build=${ph_build}";
	//echo $url."\n";
	$result = url_get_contents($url);
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
