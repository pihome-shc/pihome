<?php
/*
   _____    _   _    _
  |  __ \  (_) | |  | |
  | |__) |  _  | |__| |   ___    _ __ ___     ___
  |  ___/  | | |  __  |  / _ \  | |_  \_ \   / _ \
  | |      | | | |  | | | (_) | | | | | | | |  __/
  |_|      |_| |_|  |_|  \___/  |_| |_| |_|  \___|

     S M A R T   H E A T I N G   C O N T R O L

*************************************************************************"
* PiHome is Raspberry Pi based Central Heating Control systems. It runs *"
* from web interface and it comes with ABSOLUTELY NO WARRANTY, to the   *"
* extent permitted by applicable law. I take no responsibility for any  *"
* loss or damage to you or your property.                               *"
* DO NOT MAKE ANY CHANGES TO YOUR HEATING SYSTEM UNTILL UNLESS YOU KNOW *"
* WHAT YOU ARE DOING                                                    *"
*************************************************************************"
*/

require_once(__DIR__.'/st_inc/session.php');
confirm_logged_in();
require_once(__DIR__.'/st_inc/connection.php');
require_once(__DIR__.'/st_inc/functions.php');
?>
<div class="panel panel-primary">
        <div class="panel-heading">
                <div class="Light"><i class="fa fa-home fa-fw"></i> <?php echo $lang['home']; ?>
                        <div class="pull-right">
                                <div class="btn-group"><?php echo date("H:i"); ?>
                                </div>
                        </div>
                </div>
        </div>
        <!-- /.panel-heading -->
        <div class="panel-body">
                <a style="color: #777; cursor: pointer; text-decoration: none;" data-toggle="collapse" data-parent="#accordion" href="#collapseone">
                <button class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn">
                <h3><small><?php echo $lang['one_touch']; ?></small></h3>
                <h3 class="degre" style="margin-top:0px;"><i class="fa fa-bullseye fa-2x"></i></h3>
                <h3 class="status"></h3>
                </button></a>
                <?php
		//query to get frost protection temperature
		$query = "SELECT * FROM frost_protection ORDER BY id desc LIMIT 1; ";
		$result = $conn->query($query);
		$frost_q = mysqli_fetch_array($result);
		$frost_c = $frost_q['temperature'];

		//following two variable set to 0 on start for array index.
		$boost_index = '0';
		$override_index = '0';

		//following variable set to current day of the week.
		$dow = idate('w');

		//query to check away status
		$query = "SELECT * FROM away LIMIT 1";
		$result = $conn->query($query);
		$away = mysqli_fetch_array($result);
		$away_active = $away['status'];

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

		//GET BOILER DATA AND FAIL ZONES IF BOILER COMMS TIMEOUT
		//query to get last boiler operation time and hysteresis time
		$query = "SELECT * FROM boiler LIMIT 1";
		$result = $conn->query($query);
		$row = mysqli_fetch_array($result);
		$bcount=$result->num_rows;
		$fired_status = $row['fired_status'];
		$boiler_name = $row['name'];
		$boiler_max_operation_time = $row['max_operation_time'];
		$boiler_hysteresis_time = $row['hysteresis_time'];

		//Get data from nodes table
		$query = "SELECT * FROM nodes WHERE id = {$row['node_id']} AND status IS NOT NULL LIMIT 1";
		$result = $conn->query($query);
		$boiler_node = mysqli_fetch_array($result);
		$boiler_id = $boiler_node['node_id'];
		$boiler_seen = $boiler_node['last_seen'];
		$boiler_notice = $boiler_node['notice_interval'];

		//Check Boiler Fault
		$boiler_fault = 0;
		if($boiler_notice > 0){
			$now=strtotime(date('Y-m-d H:i:s'));
		  	$boiler_seen_time = strtotime($boiler_seen);
		  	if ($boiler_seen_time  < ($now - ($boiler_notice*60))){
    				$boiler_fault = 1;
  			}
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

		$query = "SELECT * FROM zone where zone.purge = '0' ORDER BY index_id asc; ";
		$results = $conn->query($query);
		while ($row = mysqli_fetch_assoc($results)) {
			$max_room_c=$row['max_c'];
			$max_operation_time=$row['max_operation_time'];
			$location_hysteresis_time=$row['hysteresis_time'];
			$zone_enable=$row['status'];
			$zone_sp_deadband=$row['sp_deadband'];

			//query to get node id from nodes table
			$query = "SELECT * FROM nodes WHERE id = {$row['sensor_id']} AND nodes.`purge` = '0' AND status IS NOT NULL LIMIT 1;";
			$result = $conn->query($query);
			$sensor = mysqli_fetch_array($result);
			$sensor_id = $sensor['node_id'];
			$sensor_child_id = $row['sensor_child_id'];
			$sensor_seen = $sensor['last_seen']; //not using this cause it updates on battery update
			$sensor_notice = $sensor['notice_interval'];

			//Get data from nodes table
			$query = "SELECT * FROM nodes WHERE id ={$row['controler_id']} AND status = 'Active' LIMIT 1;";
			$result = $conn->query($query);
			$controler_node = mysqli_fetch_array($result);
			$controler_id = $controler_node['node_id'];
			$controler_seen = $controler_node['last_seen'];
			$controler_notice = $controler_node['notice_interval'];

			//query to get temperature from table with sensor id
			$query = "SELECT * FROM messages_in WHERE node_id = '{$sensor_id}' AND child_id = '{$sensor_child_id}' ORDER BY id desc LIMIT 1;";
			$result = $conn->query($query);
			$roomtemp = mysqli_fetch_array($result);
			$room_c = $roomtemp['payload'];
			$temp_reading_time = $roomtemp['datetime'];

  			//Check Zone Controller Fault
			$zone_ctr_fault = 0;
			$zone_sensor_fault = 0;
			if($controler_notice > 0) {
				$now=strtotime(date('Y-m-d H:i:s'));
				$controler_seen_time = strtotime($controler_seen);
				if($controler_seen_time  < ($now - ($controler_notice*60))){
					$zone_ctr_fault = 1;
				}
			}

			//Check Zone Temperature Sensors Fault
			if($sensor_notice > 0) {
				$now=strtotime(date('Y-m-d H:i:s'));
				$sensor_seen_time = strtotime($temp_reading_time); //using time from messages_in
				if ($sensor_seen_time  < ($now - ($sensor_notice*60))){
					$zone_sensor_fault = 1;
				}
			}

			//query to get schedule and temperature from table
			if ($holidays_status) {
				$query = "SELECT * FROM schedule_daily_time_zone_view WHERE CURTIME() between start AND end AND zone_id = {$row['id']} AND time_status = '1' AND tz_status = '1' AND (WeekDays & (1 << {$dow})) > 0 AND holidays_id > 0 LIMIT 1";
				//$query = "SELECT * FROM schedule_daily_time_zone_view WHERE CURTIME() between start AND end AND zone_id = {$row['id']} AND tz_status = '1' AND (WeekDays & (1 << {$dow})) > 0 LIMIT 1";
			} else {
				$query = "SELECT * FROM schedule_daily_time_zone_view WHERE CURTIME() between start AND end AND zone_id = {$row['id']} AND time_status = '1' AND tz_status = '1' AND (WeekDays & (1 << {$dow})) > 0 AND holidays_id = 0 LIMIT 1";
				//$query = "SELECT * FROM schedule_daily_time_zone_view WHERE CURTIME() between start AND end AND zone_id = {$row['id']} AND tz_status = '1' LIMIT 1";
			}
			$sch_results = $conn->query($query);
			$schedule = mysqli_fetch_array($sch_results);
			$zone_status = $schedule['tz_status'];
			$start_time = $schedule['start'];
			$end_time = $schedule['end'];
			$schedule_c = $schedule['temperature'];
			$schedule_coop = $schedule['coop'];
			$sch_status = $schedule['time_status'];
  			if (isset($schedule['holidays_id'])) {
    				$sch_holidays = 1;
    				$sch_boiler_holidays = 1;
  			} else {
    				$sch_holidays = 0;
			}
			$shactive=" ";
			/*
			$sch_status = $schedule['status'];
			$sch_active = $schedule['active'];
			*/
			//query to check override status and get temperature from override table
			$query = "SELECT * FROM override WHERE zone_id = {$row['id']} LIMIT 1";
			$result = $conn->query($query);
			$override = mysqli_fetch_array($result);
			$ovactive = $override['status'];
			$override_c = $override['temperature'];

			//query to check boost status and get temperature from boost table
			//$query = "SELECT * FROM boost WHERE zone_id = {$zone_id} LIMIT 1;";
			$query = "SELECT * FROM boost WHERE zone_id = {$row['id']} AND status = 1 LIMIT 1;";
			$result = $conn->query($query);
			if (mysqli_num_rows($result) != 0){
				$boost = mysqli_fetch_array($result);
				$bactive = $boost['status'];
				$time = $boost['time'];
				$boost_c = $boost['temperature'];
				$minute = $boost['minute'];
			} else {
				$bactive = '0';
			}

			//query to check night cliemate status and get temperature from night climate table
			//$query = "select * from schedule_night_climat_zone_view WHERE zone_id = {$row['id']} LIMIT 1";
			$query = "select * from schedule_night_climat_zone_view WHERE zone_id = {$row['id']} AND time_status = '1' AND tz_status = '1' AND (WeekDays & (1 << {$dow})) > 0 LIMIT 1;";
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
				$current_time = date('H:i:s');
				if ((TimeIsBetweenTwoTimes($current_time, $nc_start_time, $nc_end_time)) AND ($nc_time_status =='1') AND ($nc_zone_status =='1')) {
					$night_climate_status='1';
				} else {
					$night_climate_status='0';
				}
			}else {
				$night_climate_status='0';
			}
			//Boost and Override Array
			$boost_arr[$boost_index] = $bactive;
			$boost_index = $boost_index+1;
			$override_arr[$override_index] = $ovactive;
			$override_index = $override_index+1;

			//Zone Temperature calculation
			//$zone_temp = $room_c + $weather_fact + $zone_sp_deadband;
			//Zone Temperature Sensors Reading 		$room_c
			//Zone DeadBand 						$zone_sp_deadband;
			//Zone Weather Factor 					$weather_fact

   			echo '<button class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" data-href="#" data-toggle="modal" data-target="#'.$row['type'].''.$row['id'].'" data-backdrop="static" data-keyboard="false">
			<h3><small>'.$row['name'].'</small></h3>
			<h3 class="degre">'.number_format(DispTemp($conn,$room_c),1).'&deg;</h3>
			<h3 class="status">';
    			//Now show status indicators
    			//Left is circle with color showing heating, on target, away
				//  #dc0000 red     - heating
				//  #F0AD4E orance  - on target, or above max
				//  #5292f7 blue    - away, or
    			//Middle is target temperature
    			//Right is icon for
    			//  frost(snowy)
    			//  away(signout)
    			//  scheduled(clockoutline)
    			//  over temp(thermometer)
    			//  boost(rocket)
    			//  override(refresh)
    			//  bed(bed)

    			if (($zone_ctr_fault == '1') OR ($zone_sensor_fault == '1') OR $boiler_fault == '1') {
      				//Zone fault
      				$status='';
      				$shactive='ion-android-cancel';
      				$shcolor='red';
      				$target='';     //show no target temperature
    			}
    			else{
      				if ($room_c < $frost_c) {
          				//We don't care about any other conditions, protect against frost
          				$status='red';
          				$shactive='ion-ios-snowy';
          				$shcolor='';
          				$target=number_format(DispTemp($conn,$frost_c),0) . '&deg;';
      				}
      				else
      				{
          				//we aren't in danger of freezing, so check our normal conditions.
          				if ($away_active == '0') {
              					//We are under normal operating conditions.
              					if ($room_c >= $max_room_c) {
                  					//We are over temp
                  					$status='orange';
                  					$shactive='ion-thermometer';
                  					$shcolor='red';                 //special color
                  					$target=number_format(DispTemp($conn,$max_room_c),0) . '&deg;';
            					}
						else if (($holidays_status == '1') &&  ($sch_holidays == '0')) {
                					//We are on holiday
                					$status='blue';
                					$shactive='fa-paper-plane';
                					$shcolor='';
                					$target='';
              					}
              					else if ($night_climate_status == '1' && $room_c < $nc_min_c) {
                  					//We are night climate and heating
                 					 $status='red';
                  					$shactive='fa-bed';
                  					$shcolor='';
                  					$target=number_format(DispTemp($conn,$nc_min_c),0) . '&deg;';
              					}
              					else if ($night_climate_status == '1' && $room_c >= $nc_min_c) {
                  					//We are night climate and NOT heating
                  					$status='orange';
                  					$shactive='fa-bed';
                  					$shcolor='';
                  					$target=number_format(DispTemp($conn,$nc_min_c),0) . '&deg;';
              					}
              					else if ($bactive == '1' && $room_c < $boost_c) {
                  					//We are boost and heating
                  					$status='red';
                  					$shactive='fa-rocket';
                  					$shcolor='';
                  					$target=number_format(DispTemp($conn,$boost_c),0) . '&deg;';
              					}
              					else if ($bactive == '1' && $room_c >= $boost_c) {
                  					//We are boost and NOT heating
                  					$status='orange';
                  					$shactive='fa-rocket';
                  					$shcolor='';
                  					$target=number_format(DispTemp($conn,$boost_c),0) . '&deg;';
              					}
              					//else if (($sch_status == 1) && ($ovactive == '1') && ($room_c < $schedule_c)) {
  						else if (($ovactive == '1') && ($room_c < $override_c)) {
                  					//We are override scheduled and heating
                  					$status="blue";
                  					$shactive='fa-refresh';
                  					$shcolor='';
                  					$target=number_format(DispTemp($conn,$override_c),0) . '&deg;';
              					}
              					else if (($ovactive == '1') && ($room_c >= $override_c)) {
                  					//We are override scheduled and NOT heating
                  					$status='orange';
                  					$shactive='fa-refresh';
                  					$shcolor='';
                  					$target=number_format(DispTemp($conn,$override_c),0) . '&deg;';
              					}
/*
              					else if (($sch_status == 1) && ($room_c < ($schedule_c - $weather_fact)) OR ($fired_status == 0)) {
                  					//We are scheduled and heating
                  					$status='orange';
                  					$shactive='fa-leaf green';
                  					$shcolor='';
                  					$target=number_format(DispTemp($conn,$schedule_c),0) . '&deg;';
              					}
*/
              					else if (($sch_status == 1) && ($room_c < $schedule_c) && (($schedule_coop == 0)||($fired_status == 1))) {
                  					//We are scheduled and heating
                  					$status='red';
                  					$shactive='ion-ios-clock-outline';
                  					$shcolor='';
                  					$target=number_format(DispTemp($conn,$schedule_c),0) . '&deg;';
              					}
              					else if (($sch_status == 1) && ($room_c < $schedule_c) && ($schedule_coop == 1) && ($fired_status == 0)) {
                  					//We are coop scheduled and waiting for boiler start
                  					$status='blueinfo';   
                  					$shactive='ion-ios-clock-outline';
                  					$shcolor='orange';
                  					$target=number_format(DispTemp($conn,$schedule_c),0) . '&deg;';
              					}
              					else if (($sch_status == 1) && ($room_c >= $schedule_c)) {
                  					//We are scheduled and heating
                  					$status='orange';
                  					$shactive='ion-ios-clock-outline';
                  					$shcolor='';
                  					$target=number_format(DispTemp($conn,$schedule_c),0) . '&deg;';
              					}
              					else {
                  					//We shouldn't get here.
                  					$status='';
  							//$shactive='fa-question';
  							$shactive='';
                  					$shcolor='';
                  					$target='';     //show no target temperature
              					}
          				}
          				else
          				{
              					//We are away
              					$status='blue';
              					$shactive='fa-sign-out';
              					$shcolor='';
              					$target='';     //show no target temperature
          				}
      				}
    			}
    			//Left small circular icon/color status
    			echo '<small class="statuscircle"><i class="fa fa-circle fa-fw ' . $status . '"></i></small>';
    			//Middle target temp
    			echo '<small class="statusdegree">' . $target .'</small>';
    			//Right icon for what/why
    			echo '<small class="statuszoon"><i class="fa ' . $shactive . ' ' . $shcolor . ' fa-fw"></i></small>';
    			echo '</h3></button>';      //close out status and button


			//Zone Schedule listing model
			echo '<div class="modal fade" id="'.$row['type'].''.$row['id'].'" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
							<h5 class="modal-title">'.$row['name'].'</h5>
						</div>
						<div class="modal-body">';
  							if ($boiler_fault == '1') {
								$date_time = date('Y-m-d H:i:s');
								$datetime1 = strtotime("$date_time");
								$datetime2 = strtotime("$boiler_seen");
								$interval  = abs($datetime2 - $datetime1);
								$ctr_minutes   = round($interval / 60);
								echo '
								<ul class="chat">
									<li class="left clearfix">
										<div class="header">
											<strong class="primary-font red">Boiler Fault!!!</strong>
											<small class="pull-right text-muted">
											<i class="fa fa-clock-o fa-fw"></i> '.secondsToWords(($ctr_minutes)*60).' ago
											</small>
											<br><br>
											<p>Node ID '.$boiler_id.' last seen at '.$boiler_seen.' </p>
											<p class="text-info">Heating system will resume its normal operation once this issue is fixed. </p>
										</div>
									</li>
								</ul>';

  							}elseif ($zone_ctr_fault == '1') {
								$date_time = date('Y-m-d H:i:s');
								$datetime1 = strtotime("$date_time");
								$datetime2 = strtotime("$controler_seen");
								$interval  = abs($datetime2 - $datetime1);
								$ctr_minutes   = round($interval / 60);
								echo '
								<ul class="chat">
									<li class="left clearfix">
										<div class="header">
											<strong class="primary-font red">Controller Fault!!!</strong>
											<small class="pull-right text-muted">
											<i class="fa fa-clock-o fa-fw"></i> '.secondsToWords(($ctr_minutes)*60).' ago
											</small>
											<br><br>
											<p>Controller ID '.$controler_id.' last seen at '.$controler_seen.' </p>
											<p class="text-info">Heating system will resume its normal operation once this issue is fixed. </p>
										</div>
									</li>
								</ul>';
							//echo $zone_senros_txt;
							}elseif ($zone_sensor_fault == '1'){
								$date_time = date('Y-m-d H:i:s');
								$datetime1 = strtotime("$date_time");
								$datetime2 = strtotime("$sensor_seen");
								$interval  = abs($datetime2 - $datetime1);
								$sensor_minutes   = round($interval / 60);
								echo '
								<ul class="chat">
									<li class="left clearfix">
										<div class="header">
											<strong class="primary-font red">Sensor Fault!!!</strong>
											<small class="pull-right text-muted">
											<i class="fa fa-clock-o fa-fw"></i> '.secondsToWords(($sensor_minutes)*60).' ago
											</small>
											<br><br>
											<p>Sensor ID '.$sensor_id.' last seen at '.$sensor_seen.' <br>Last Temperature reading received at '.$temp_reading_time.' </p>
											<p class="text-info"> Heating system will resume for this zone its normal operation once this issue is fixed. </p>
										</div>
									</li>
								</ul>';
							}else{
								$squery = "SELECT * FROM schedule_daily_time_zone_view where zone_id ='{$row['id']}' AND tz_status = 1 AND time_status = '1' AND (WeekDays & (1 << {$dow})) > 0 ORDER BY start asc";
								$sresults = $conn->query($squery);
								if (mysqli_num_rows($sresults) == 0){
									echo '<div class=\"list-group\">
									<a href="#" class="list-group-item"><i class="fa fa-exclamation-triangle red"></i>&nbsp;&nbsp;'.$lang['schedule_active_today'].' '.$row['name'].'!!! </a>
							</div>';
							} else {
								//echo '<h4>'.mysqli_num_rows($sresults).' Schedule Records found.</h4>';
								echo '<p>'.$lang['schedule_disble'].'</p>
								<br>
								<div class=\"list-group\">' ;
									while ($srow = mysqli_fetch_assoc($sresults)) {
										$shactive="orangesch_list";
										$time = strtotime(date("G:i:s"));
										$start_time = strtotime($srow['start']);
										$end_time = strtotime($srow['end']);
										if ($time >$start_time && $time <$end_time){$shactive="redsch_list";}
											//this line to pass unique argument  "?w=schedule_list&o=active&wid=" href="javascript:delete_schedule('.$srow["id"].');"
											echo '<a href="javascript:schedule_zone('.$srow['tz_id'].');" class="list-group-item">
											<div class="circle_list '. $shactive.'"> <p class="schdegree">'.number_format(DispTemp($conn,$srow['temperature']),0).'&deg;</p></div>
											<span class="label label-info">' . $srow['sch_name'] . '</span>
											<span class="pull-right text-muted sch_list"><em>'. $srow['start'].' - ' .$srow['end'].'</em></span></a>';
									}
								echo '</div>';
							}
						}
						echo '
						</div>
						<!-- /.modal-body -->
						<div class="modal-footer"><button type="button" class="btn btn-default btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
						</div>
						<!-- /.modal-footer -->
					</div>
					<!-- /.modal-content -->
				</div>
				<!-- /.modal-dialog -->
			</div>
			<!-- /.modal fade -->
			';
		} // end of zones while loop

		//BOILER BUTTON
		if ($bcount != 0) {
			//query to get last boiler statues change time
			$query = "SELECT * FROM boiler_logs ORDER BY id desc LIMIT 1 ";
			$result = $conn->query($query);
			$boiler_onoff = mysqli_fetch_array($result);
			$boiler_last_off = $boiler_onoff['stop_datetime'];

			//check if hysteresis is passed its time or not
			$hysteresis='0';
			if (isset($boiler_last_off)){
				$boiler_last_off = strtotime( $boiler_last_off );
				$boiler_hysteresis_time = $boiler_last_off + ($boiler_hysteresis_time * 60);
				$now=strtotime(date('Y-m-d H:i:s'));
				if ($boiler_hysteresis_time > $now){$hysteresis='1';}
			} else {
				$hysteresis='0';
			}

			if ($fired_status=='1'){$boiler_colour="red";} elseif ($fired_status=='0'){$boiler_colour="blue";}
			echo '<button class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" data-toggle="modal" href="#boiler" data-backdrop="static" data-keyboard="false">
			<h3 class="text-info"><small>'.$boiler_name.'</small></h3>
			<h3 class="degre" ><i class="ionicons ion-flame fa-1x '.$boiler_colour.'"></i></h3>';
			if($boiler_fault=='1') {echo'<h3 class="status"><small class="statusdegree"></small><small style="margin-left: 70px;" class="statuszoon"><i class="fa ion-android-cancel fa-1x red"></i> </small>';}
			elseif($hysteresis=='1') {echo'<h3 class="status"><small class="statusdegree"></small><small style="margin-left: 70px;" class="statuszoon"><i class="fa fa-hourglass fa-1x orange"></i> </small>';}
			else { echo'<h3 class="status"><small class="statusdegree"></small><small style="margin-left: 48px;" class="statuszoon"></small>';}
			echo '</h3></button>';

			//Boiler Last 5 Status Logs listing model
			echo '<div class="modal fade" id="boiler" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
							<h5 class="modal-title">'.$boiler_name.' - '.$lang['boiler_recent_logs'].'</h5>
						</div>
						<div class="modal-body">';
  							if ($boiler_fault == '1') {
								$date_time = date('Y-m-d H:i:s');
								$datetime1 = strtotime("$date_time");
								$datetime2 = strtotime("$boiler_seen");
								$interval  = abs($datetime2 - $datetime1);
								$ctr_minutes   = round($interval / 60);
								echo '
								<ul class="chat">
									<li class="left clearfix">
										<div class="header">
											<strong class="primary-font red">Boiler Fault!!!</strong>
											<small class="pull-right text-muted">
											<i class="fa fa-clock-o fa-fw"></i> '.secondsToWords(($ctr_minutes)*60).' ago
											</small>
											<br><br>
											<p>Node ID '.$boiler_id.' last seen at '.$boiler_seen.' </p>
											<p class="text-info">Heating system will resume its normal operation once this issue is fixed. </p>
										</div>
									</li>
								</ul>';
  							}
							$bquery = "select DATE_FORMAT(start_datetime, '%H:%i') as start_datetime, DATE_FORMAT(stop_datetime, '%H:%i') as stop_datetime , DATE_FORMAT(expected_end_date_time, '%H:%i') as expected_end_date_time, TIMESTAMPDIFF(MINUTE, start_datetime, stop_datetime) as on_minuts
							from boiler_logs order by id desc limit 5";
							$bresults = $conn->query($bquery);
							if (mysqli_num_rows($bresults) == 0){
								echo '<div class=\"list-group\">
									<a href="#" class="list-group-item"><i class="fa fa-exclamation-triangle red"></i>&nbsp;&nbsp;'.$lang['boiler_no_log'].'</a>
								</div>';
							} else {
								echo '<p class="text-muted">'. mysqli_num_rows($bresults) .' '.$lang['boiler_last_records'].'</p>
								<div class=\"list-group\">' ;
									echo '<a href="#" class="list-group-item"> <i class="ionicons ion-flame fa-1x red"></i> Start &nbsp; - &nbsp;End <span class="pull-right text-muted"><em> '.$lang['boiler_on_minuts'].' </em></span></a>';
									while ($brow = mysqli_fetch_assoc($bresults)) {
										echo '<a href="#" class="list-group-item"> <i class="ionicons ion-flame fa-1x red"></i> '. $brow['start_datetime'].' - ' .$brow['stop_datetime'].' <span class="pull-right text-muted"><em> '.$brow['on_minuts'].'&nbsp;</em></span></a>';
									}
								 echo '</div>';
							}
						echo '</div>
						<div class="modal-footer"><button type="button" class="btn btn-default btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
						</div>
						<!-- /.modal-footer -->
					</div>
					<!-- /.modal-content -->
				</div>
				<!-- /.modal-dialog -->
			</div>
			<!-- /.modal fade -->
			';
		}	// end if boiler button
		?>
		<!-- One touch buttons -->
		<div id="collapseone" class="panel-collapse collapse animated fadeIn">
			<?php
			if (in_array("1", $override_arr)) {$override_status='red';}else{$override_status='blue';}
			echo '<a style="color: #777; cursor: pointer; text-decoration: none;" href="override.php">
			<button type="button" class="btn btn-default btn-circle btn-xxl mainbtn">
			<h3 class="buttontop"><small>'.$lang['override'].'</small></h3>
			<h3 class="degre" ><i class="fa fa-refresh fa-1x"></i></h3>
			<h3 class="status"><small class="statuscircle"><i class="fa fa-circle fa-fw '.$override_status.'"></i></small>
			</h3></button></a>';

			if (in_array("1", $boost_arr)) {$boost_status='red';}else{$boost_status='blue';}
			echo '<a style="color: #777; cursor: pointer; text-decoration: none;" href="boost.php">
			<button type="button" class="btn btn-default btn-circle btn-xxl mainbtn">
			<h3 class="buttontop"><small>'.$lang['boost'].'</small></h3>
			<h3 class="degre" ><i class="fa fa-rocket fa-1x"></i></h3>
			<h3 class="status"><small class="statuscircle"><i class="fa fa-circle fa-fw '.$boost_status.'"></i></small>
			</h3></button></a>';

			$query = "SELECT * FROM schedule_night_climate_time WHERE id = 1";
			$results = $conn->query($query);
			$row = mysqli_fetch_assoc($results);
			if ($row['status'] == 1) {$night_status='red';}else{$night_status='blue';}
			echo '<a style="color: #777; cursor: pointer; text-decoration: none;" href="scheduling.php?nid=0">
			<button type="button" class="btn btn-default btn-circle btn-xxl mainbtn">
			<h3 class="buttontop"><small>'.$lang['night_climate'].'</small></h3>
			<h3 class="degre" ><i class="fa fa-bed fa-1x"></i></h3>
			<h3 class="status"><small class="statuscircle"><i class="fa fa-circle fa-fw '.$night_status.'"></i></small>
			</h3></button>';

			if ($away_active=='1'){$awaystatus="red";}elseif ($away_active=='0'){$awaystatus="blue";}
			echo '<a href="javascript:active_away();">
			<button type="button" class="btn btn-default btn-circle btn-xxl mainbtn">
			<h3 class="buttontop"><small>'.$lang['away'].'</small></h3>
			<h3 class="degre" ><i class="fa fa-sign-out fa-1x"></i></h3>
			<h3 class="status"><small class="statuscircle"><i class="fa fa-circle fa-fw '.$awaystatus.'"></i></small>
			</h3></button></a>';
			if ($holidays_status=='1'){$holidaystatus="red";}elseif ($holidays_status=='0'){$holidaystatus="blue";}
			?>
			<a style="color: #777; cursor: pointer; text-decoration: none;" href="holidays.php">
			<button type="button" class="btn btn-default btn-circle btn-xxl mainbtn">
			<h3 class="buttontop"><small><?php echo $lang['holidays']; ?></small></h3>
			<h3 class="degre" ><i class="fa fa-paper-plane fa-1x"></i></h3>
			<h3 class="status"><small class="statuscircle" style="color:#048afd;"><i class="fa fa-circle fa-fw <?php echo $holidaystatus; ?>"></i></small>
			</h3></button></a>

			<a style="color: #777; cursor: pointer; text-decoration: none;" href="zone.php">
			<button type="button" class="btn btn-default btn-circle btn-xxl mainbtn">
			<h3 class="buttontop"><small><?php echo $lang['zone_add']; ?></small></h3>
			<h3 class="degre" ><i class="fa fa-plus fa-1x"></i></h3>
			<h3 class="status"><small class="statuscircle" style="color:#048afd;"><i class="fa fa-fw"></i></small>
			</h3></button></a>
			</div>
		</div>
                <!-- /.panel-body -->
		<div class="panel-footer">
			<?php
			ShowWeather($conn);
			?>

                       	<div class="pull-right">
                        	<div class="btn-group">
					<?php
					$query="select date(start_datetime) as date,
					sum(TIMESTAMPDIFF(MINUTE, start_datetime, expected_end_date_time)) as total_minuts,
					sum(TIMESTAMPDIFF(MINUTE, start_datetime, stop_datetime)) as on_minuts,
					(sum(TIMESTAMPDIFF(MINUTE, start_datetime, expected_end_date_time)) - sum(TIMESTAMPDIFF(MINUTE, start_datetime, stop_datetime))) as save_minuts
					from boiler_logs WHERE date(start_datetime) = CURDATE() GROUP BY date(start_datetime) asc";
					$result = $conn->query($query);
					$boiler_time = mysqli_fetch_array($result);
					$boiler_time_total = $boiler_time['total_minuts'];
					$boiler_time_on = $boiler_time['on_minuts'];
					$boiler_time_save = $boiler_time['save_minuts'];
					if($boiler_time_on >0){	echo ' <i class="ionicons ion-ios-clock-outline"></i> '.secondsToWords(($boiler_time_on)*60);}
					?>
                        	</div>
                 	</div>
		</div>
		<!-- /.panel-footer -->
	</div>
	<!-- /.panel-primary -->
<?php if(isset($conn)) { $conn->close();} ?>
