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
                            <div class="Light"><i class="fa fa-home fa-fw"></i> Home
						<div class="pull-right"> <div class="btn-group"><?php echo date("H:i"); ?></div> </div>
                        </div></div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
							<a style="color: #777; cursor: pointer; text-decoration: none;" data-toggle="collapse" data-parent="#accordion" href="#collapseone">
							<button class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn"><h3 class="Light"><small>One Touch</small><br><i class="fa fa-bullseye fa-2x"></i></h3>
							<br>
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

//query to check away status 
$query = "SELECT * FROM away LIMIT 1";
$result = $conn->query($query);
$away = mysqli_fetch_array($result);
$away_active = $away['status'];

$query = "SELECT * FROM zone where zone.purge = '0' ORDER BY index_id asc; ";
$results = $conn->query($query);
while ($row = mysqli_fetch_assoc($results)) {
	$max_room_c=$row['max_c'];
	$max_operation_time=$row['max_operation_time'];
	$location_hysteresis_time=$row['hysteresis_time'];
	$zone_enable=$row['status'];

	//query to get node id from nodes table
	$query = "SELECT * FROM nodes WHERE id = {$row['sensor_id']} AND nodes.`purge` = '0' LIMIT 1;";
	$result = $conn->query($query);
	$sensor = mysqli_fetch_array($result);
	$sensor_id = $sensor['node_id'];
	
	//query to get temperature from table with sensor id 
	$query = "SELECT * FROM messages_in WHERE node_id = '{$sensor_id}' ORDER BY id desc LIMIT 1 ";
	$result = $conn->query($query);
	$roomtemp = mysqli_fetch_array($result);
	$room_c = $roomtemp['payload'];	
	
	//query to get schedule and temperature from table 
	$query = "SELECT * FROM schedule_daily_time_zone_view WHERE CURTIME() between start AND end AND zone_id = {$row['id']} AND tz_status = '1' LIMIT 1";
	$sch_results = $conn->query($query);
	$schedule = mysqli_fetch_array($sch_results);
	$zone_status = $schedule['tz_status'];
	$start_time = $schedule['start'];
	$end_time = $schedule['end'];
	$schedule_c = $schedule['temperature'];
	$sch_status = $schedule['time_status'];
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
	
	//query to check override status and get temperature from override table 
	$query = "SELECT * FROM boost WHERE zone_id = {$row['id']} LIMIT 1";
	$result = $conn->query($query);
	$boost = mysqli_fetch_array($result);
	$bactive = $boost['status'];
	$time = $boost['time'];
	$boost_c = $boost['temperature'];
	$minute = $boost['minute'];

	//query to check night cliemate status and get temperature from night climate table 
	$query = "select * from schedule_night_climat_zone_view WHERE zone_id = {$row['id']} LIMIT 1";
	$result = $conn->query($query);
	$night_climate = mysqli_fetch_array($result);
	$nc_time_status = $night_climate['t_status'];
	$nc_zone_status = $night_climate['z_status'];
	$nc_zone_id = $night_climate['zone_id'];
	$nc_start_time = $night_climate['start_time'];
	$nc_end_time = $night_climate['end_time'];
	$nc_min_c = $night_climate['min_temperature'];
	$nc_max_c = $night_climate['max_temperature'];
	$current_time = date('H:i:s');
	if ((TimeIsBetweenTwoTimes($current_time, $nc_start_time, $nc_end_time)) AND ($nc_time_status =='1') AND ($nc_zone_status =='1')) {
		$night_climate_status='1';
	} else {
		$night_climate_status='0';
	}

	//following line to decide which temperature is target temperature 
	if ($bactive=='1' && $room_c < $max_room_c){$target_c=$boost_c;} elseif($night_climate_status=='1' && $room_c < $max_room_c) {$target_c=$nc_min_c;} elseif($ovactive=='1' && $room_c < $max_room_c){$target_c=$override_c;} 
	elseif (($sch_status=="1") && ($zone_status=="1") && ($room_c < $max_room_c)){$target_c=$schedule_c;} 
	else {$target_c="0";}

	//if((isset($sch_status)) AND $target_c > $room_c ) {$shactive = "ion-ios-clock-outline";}
	
	
	if((isset($sch_status)) && $sch_status =="1" && $room_c < $max_room_c && $away_active=="0" && $bactive=="0" )  {$shactive = "ion-ios-clock-outline";}
	elseif($room_c < $frost_c){$shactive="ion-ios-snowy";}
	elseif($away_active=="1"){$shactive="fa-sign-out";}
	elseif($room_c > $max_room_c){$shactive="ion-thermometer";}	
	elseif($bactive=="1"){$shactive="fa-rocket";}
	elseif($ovactive=="1"){$shactive="fa-refresh";}
	elseif($night_climate_status=='1'){$shactive="fa-bed";}

	
	/*
	if((isset($sch_status)) && $sch_status =="1")  {$shactive = "ion-ios-clock-outline";}
	if ($room_c < $frost_c){$shactive="ion-ios-snowy";}else {
		if($room_c > $max_room_c ){$shactive="ion-thermometer";}
		if($night_climate_status=='1'){$shactive="fa-bed";
		}elseif($ovactive=="1"){$shactive="fa-refresh";}
		if($bactive=="1"){$shactive="fa-rocket";}
		if($away_active=="1"){$shactive="fa-sign-out";}
	}
	
	*/
	//#dc0000 red
	//#F0AD4E orance
	//#5292f7 blue
	$status="#555555";
	if ($room_c < $frost_c) {$status="#dc0000";	}
	elseif  ($room_c >= $max_room_c ) {$status="#F0AD4E";	}
	elseif(($room_c > $frost_c) && ($room_c < $max_room_c)){
		if ($away_active=='0'){	
			if($bactive=='0'){
				if($night_climate_status=='0'){
				if (mysqli_num_rows($sch_results) != 0){
					$status="0";
					if (($sch_status=='1') && ($room_c < $target_c) && ($zone_enable =='1')){$status="#dc0000";}
					if (($sch_status=='1') && ($room_c >= $target_c)){$status="#F0AD4E";}
					if (($ovactive=='1' && $zone_status=='1') && ($room_c <= $target_c) && ($zone_enable =='1')){$status="#dc0000";}
					if (($ovactive=='1' && $zone_status=='1') && ($room_c >= $target_c)){$status="#F0AD4E";} 
					if (($sch_status=='0') && ($ovactive=='0') && ($zone_enable =='1')){$status="#5292f7";} 
					if ($zone_status=='0'  && $zone_enable =='1'){$status="#5292f7";}
				} elseif ($zone_enable =='1') {$status="#5292f7";
				} else {$status="555555";}
			}elseif($night_climate_status=='1' && $room_c < $target_c  && $zone_enable =='1'){$status="#dc0000";
			}elseif($night_climate_status=='1' && $room_c >= $target_c){$status="#F0AD4E";}
			}elseif(($bactive=='1') && ($room_c < $target_c)  && ($zone_enable =='1')) {$status="#dc0000";
			}elseif(($bactive=='1') && ($room_c >= $target_c) && ($zone_enable =='1')) {$status="#F0AD4E";}
		}elseif ($away_active=='1'){$status="#5292f7";}
	}
	$boost_arr[$boost_index] = $bactive;
	$boost_index = $boost_index+1;
	$override_arr[$override_index] = $ovactive;
	$override_index = $override_index+1;

	echo ' <button class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" data-href="#" data-toggle="modal" data-target="#'.$row['type'].''.$row['id'].'" data-backdrop="static" data-keyboard="false">
	<h3><small>'.$row['name'].'</small></h3>
	<h3 class="degre">'.number_format($room_c,1).'&deg;</h3>
	<h3 class="status"><small style="color:'.$status.';"><i class="fa fa-circle fa-fw zone-status"></i></small>';
	echo ' <small class="statusdegree">'; 
	if((isset($target_c)) AND $target_c > 0 AND $away_active =='0') {echo $target_c.'&deg;';} 
	if ($room_c >= $max_room_c && $away_active =='0'){echo '</small><small style="margin-left: 24px;" class="zoonstatus">';}
	if ($room_c < $frost_c && $room_c < $max_room_c){echo '</small><small style="margin-left: 20px;" class="zoonstatus">';}
	if($away_active=="1"){echo '</small><small style="margin-left: 48px;" class="zoonstatus">';}else {echo '</small><small class="zoonstatus">'; }
	if($room_c > $max_room_c && $away_active=="0"){echo ' <i style="color:#dc0000;" class="fa '.$shactive.' fa-fw"></i></small></h3></button>';} else {echo ' <i class="fa '.$shactive.' fa-fw"></i></small></h3></button>';}

	

	//Zone Schedule listing model
	echo '<div class="modal fade" id="'.$row['type'].''.$row['id'].'" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
	<div class="modal-content">
	<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
	<h5 class="modal-title">'.$row['name'].' - Active Schedule </h5>
	</div>
	<div class="modal-body">';
	$squery = "SELECT * FROM schedule_daily_time_zone_view where zone_id ='{$row['id']}' AND tz_status = 1 ORDER BY start asc";
	$sresults = $conn->query($squery);
	if (mysqli_num_rows($sresults) == 0){
		echo '<div class=\"list-group\"><a href="#" class="list-group-item"><i class="fa fa-exclamation-triangle red"></i>&nbsp;&nbsp;No Schedule Found for '.$row['name'].'!!! </a>';
	} else {
		echo '<h4>'.mysqli_num_rows($sresults).' Schedule Records found.</h4>
		<p>You can Disable Schedule by clicking on temperature circle.</p>
		<br>
		<div class=\"list-group\">' ;
		while ($srow = mysqli_fetch_assoc($sresults)) {
			$shactive="orangesch_list";
			$time = strtotime(date("G:i:s")); 
			$start_time = strtotime($srow['start']);
			$end_time = strtotime($srow['end']);
			if ($time >$start_time && $time <$end_time){$shactive="redsch_list";}
			//this line to pass unique argument  "?w=schedule_list&o=active&wid=" href="javascript:delete_schedule('.$srow["id"].');"
			echo ' <a href="javascript:schedule_zone('.$srow['tz_id'].');" class="list-group-item">
			<div class="circle_list '. $shactive.'"> <p class="schdegree">'.$srow['temperature'].'&deg;</p></div>
			<span class="pull-right text-muted sch_list"><em>'. $srow['start'].' - ' .$srow['end'].'</em></span></a>';
		}
	}
	echo '</div></div><div class="modal-footer"><button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
	</div></div></div></div>';				
						
//end of while loop	<a href="javascript:active_away();">
}


//BOILER BUTTON 

//query to get last boiler statues change time
$query = "SELECT * FROM boiler_logs ORDER BY id desc LIMIT 1 ";
$result = $conn->query($query);
$boiler_onoff = mysqli_fetch_array($result);
$boiler_last_off = $boiler_onoff['stop_datetime'];

//query to get last boiler operation time and hysteresis time
$query = "SELECT * FROM boiler LIMIT 1";
$result = $conn->query($query);
$row = mysqli_fetch_array($result);
$fired_status = $row['fired_status'];
$boiler_name = $row['name'];
$boiler_max_operation_time = $row['max_operation_time'];
$boiler_hysteresis_time = $row['hysteresis_time'];

//check if hysteresis is passed its time or not 
$hysteresis='0';
if (isset($boiler_last_off)){
	$boiler_last_off = strtotime( $boiler_last_off );
	$boiler_hysteresis_time = $boiler_last_off + ($boiler_hysteresis_time * 60);
	$now=strtotime(date('Y-m-d H:i:s'));
	if ($boiler_hysteresis_time > $now){$hysteresis='1';}
} else {$hysteresis='0';}

if ($fired_status=='1'){$boiler_colour="red";} elseif ($fired_status=='0'){$boiler_colour="blue";}
echo '	<button class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" data-toggle="modal" href="#boiler" data-backdrop="static" data-keyboard="false">
		<h3 class="text-info"><small>'.$boiler_name.'</small></h3>
		<h3 class="degre" ><i class="ionicons ion-flame fa-1x '.$boiler_colour.'"></i></h3>';
if($hysteresis=='0') { echo'<h3 class="status"><small class="statusdegree"></small><small style="margin-left: 48px;" class="zoonstatus"></small>';}
if($hysteresis=='1') {echo'<h3 class="status"><small class="statusdegree"></small><small style="margin-left: 70px;" class="zoonstatus"><i class="fa fa-hourglass fa-1x orange"></i> </small>';}
echo '</h3></button>';


	//Boiler Last 5 Status Logs listing model
	echo '<div class="modal fade" id="boiler" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
	<div class="modal-content">
	<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
	<h5 class="modal-title">'.$boiler_name.' - Recent Logs </h5>
	</div>
	<div class="modal-body">';
	
	$bquery = "select DATE_FORMAT(start_datetime, '%H:%i') as start_datetime, DATE_FORMAT(stop_datetime, '%H:%i') as stop_datetime , DATE_FORMAT(expected_end_date_time, '%H:%i') as expected_end_date_time, TIMESTAMPDIFF(MINUTE, start_datetime, stop_datetime) as on_minuts
	from boiler_logs order by id desc limit 5";
	$bresults = $conn->query($bquery);
	if (mysqli_num_rows($bresults) == 0){
		echo '<div class=\"list-group\"><a href="#" class="list-group-item"><i class="fa fa-exclamation-triangle red"></i>&nbsp;&nbsp;No boiler log record found !!! </a>';
	} else {
		echo '<p class="text-muted">'. mysqli_num_rows($bresults) .' Last Boiler Log Records. </p>
		<div class=\"list-group\">' ;
		echo '<a href="#" class="list-group-item"> <i class="ionicons ion-flame fa-1x red"></i> Start &nbsp; - &nbsp;End <span class="pull-right text-muted"><em> On Minuts </em></span></a>';
		while ($brow = mysqli_fetch_assoc($bresults)) {
			echo '<a href="#" class="list-group-item"> <i class="ionicons ion-flame fa-1x red"></i> '. $brow['start_datetime'].' - ' .$brow['stop_datetime'].' <span class="pull-right text-muted"><em> '.$brow['on_minuts'].'&nbsp;</em></span></a>';
		}
	}
	echo '</div>
	</div><div class="modal-footer"><button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
	</div></div></div></div>';	
?>


<!-- One touch buttons -->

							<div id="collapseone" class="panel-collapse collapse animated fadeIn">
<?php 
							if (in_array("1", $override_arr)) {$override_status='red';}else{$override_status='blue';}
echo '						<a style="color: #777; cursor: pointer; text-decoration: none;" href="override.php">
							<button type="button" class="btn btn-default btn-circle btn-xxl mainbtn">
							<h3 class="buttontop"><small>Override</small></h3>
							<h3 class="degre" ><i class="fa fa-refresh fa-1x"></i></h3>
							<h3 class="status"><small><i class="fa fa-circle fa-fw '.$override_status.'"></i></small>
							</h3></button></a>';

							if (in_array("1", $boost_arr)) {$boost_status='red';}else{$boost_status='blue';}
echo '						<a style="color: #777; cursor: pointer; text-decoration: none;" href="boost.php">
							<button type="button" class="btn btn-default btn-circle btn-xxl mainbtn">
							<h3 class="buttontop"><small>Boost</small></h3>
							<h3 class="degre" ><i class="fa fa-rocket fa-1x"></i></h3>
							<h3 class="status"><small><i class="fa fa-circle fa-fw '.$boost_status.'"></i></small>
							</h3></button></a>';
							
							$query = "SELECT * FROM schedule_night_climate_time WHERE id = 1";
							$results = $conn->query($query);	
							$row = mysqli_fetch_assoc($results);
							if ($row['status'] == 1) {$night_status='red';}else{$night_status='blue';}
echo '						<a style="color: #777; cursor: pointer; text-decoration: none;" href="night_climate.php">
							<button type="button" class="btn btn-default btn-circle btn-xxl mainbtn">
							<h3 class="buttontop"><small>Night Climate</small></h3>
							<h3 class="degre" ><i class="fa fa-bed fa-1x"></i></h3>
							<h3 class="status"><small><i class="fa fa-circle fa-fw '.$night_status.'"></i></small>
							</h3></button>';
							
							if ($away_active=='1'){$awaystatus="red";}elseif ($away_active=='0'){$awaystatus="blue";}
echo '						<a href="javascript:active_away();">
							<button type="button" class="btn btn-default btn-circle btn-xxl mainbtn">
							<h3 class="buttontop"><small>Away</small></h3>
							<h3 class="degre" ><i class="fa fa-sign-out fa-1x"></i></h3>
							<h3 class="status"><small><i class="fa fa-circle fa-fw '.$awaystatus.'"></i></small>
							</h3></button></a>';
?>
							<a style="color: #777; cursor: pointer; text-decoration: none;" href="holidays.php">
							<button type="button" class="btn btn-default btn-circle btn-xxl mainbtn">
							<h3 class="buttontop"><small>Holidays</small></h3>
							<h3 class="degre" ><i class="fa fa-paper-plane fa-1x"></i></h3>
							<h3 class="status"><small style="color:#048afd;"><i class="fa fa-circle fa-fw"></i></small>
							</h3></button></a>

							<a style="color: #777; cursor: pointer; text-decoration: none;" href="zone_add.php">
							<button type="button" class="btn btn-default btn-circle btn-xxl mainbtn">
							<h3 class="buttontop"><small>Add Zone</small></h3>
							<h3 class="degre" ><i class="fa fa-plus fa-1x"></i></h3>
							<h3 class="status"><small style="color:#048afd;"><i class="fa fa-fw"></i></small>
							</h3></button></a>

							</div></div>
                        <!-- /.panel-body -->
						<div class="panel-footer">
<?php 
$query="select * from weather";
$result = $conn->query($query);
$weather = mysqli_fetch_array($result);
?>
<?php //$weather = getWeather(); ?><?php echo $weather['c'] ;?>&deg;C
<span><img border="0" width="24" src="images/<?php echo $weather['img'];?>.png" title="<?php echo $weather['title'];?> - 
<?php echo $weather['description'];?>"></span> <span><?php echo $weather['title'];?> - 
<?php echo $weather['description'];?></span>

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
                    </div>
<?php if(isset($conn)) { $conn->close();} ?>