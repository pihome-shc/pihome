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

$time_id = 0;
if(isset($_GET['hol_id'])) {
        $holidays_id = $_GET['hol_id'];
        $return_url = "holidays.php";
} elseif(isset($_GET['nid'])) {
        $return_url = "home.php";
        $time_id = 1;
} else {
        $holidays_id = 0;
        $return_url = "schedule.php";
}
if(isset($_GET['id'])) {
	$time_id = $_GET['id'];
}

//check if weather api is active
$query = "SELECT * FROM weather WHERE last_update > DATE_SUB( NOW(), INTERVAL 24 HOUR);";
$result = $conn->query($query);
$w_count=mysqli_num_rows($result);

//Form submit
if (isset($_POST['submit'])) {
	$sc_en = isset($_POST['sc_en']) ? $_POST['sc_en'] : "0";
		//PHP: Bitwise operator
		//http://php.net/manual/en/language.operators.bitwise.php
		//https://www.w3resource.com/php/operators/bitwise-operators.php
		$mask = 0;
        $bit = isset($_POST['Sunday_en']) ? $_POST['Sunday_en'] : "0";
        if ($bit) {
          $mask =  $mask | (1 << 0); }
        else {$mask =  $mask & (0 << 0); }
        $bit = isset($_POST['Monday_en']) ? $_POST['Monday_en'] : "0";
        if ($bit) {
          $mask =  $mask | (1 << 1); }
        $bit = isset($_POST['Tuesday_en']) ? $_POST['Tuesday_en'] : "0";
        if ($bit) {
          $mask =  $mask | (1 << 2); }
        $bit = isset($_POST['Wednesday_en']) ? $_POST['Wednesday_en'] : "0";
        if ($bit) {
          $mask =  $mask | (1 << 3); }
        $bit = isset($_POST['Thursday_en']) ? $_POST['Thursday_en'] : "0";
        if ($bit) {
          $mask =  $mask | (1 << 4); }
        $bit = isset($_POST['Friday_en']) ? $_POST['Friday_en'] : "0";
        if ($bit) {
          $mask =  $mask | (1 << 5); }
        $bit = isset($_POST['Saturday_en']) ? $_POST['Saturday_en'] : "0";
        if ($bit) {
          $mask =  $mask | (1 << 6);
		}

	$start_time = $_POST['start_time'];
	$end_time = $_POST['end_time'];

        if(isset($_GET['nid'])) {
                $sc_en = isset($_POST['sc_en']) ? $_POST['sc_en'] : "0";
                $query = "SELECT * FROM schedule_night_climate_time;";
                $result = $conn->query($query);
                $nctcount = $result->num_rows;
                if ($nctcount == 0) {
                        $query = "INSERT INTO `schedule_night_climate_time` VALUES (1,1,0,'{$sc_en}','{$start_time}','{$end_time}','{$mask}');";
                        $result = $conn->query($query);
                        if ($result) {
                                $message_success .= "<p>".$lang['night_climate_time_success']."</p>";
				header("Refresh: 3; url=".$return_url);
                        } else {
                                $error .= "<p>".$lang['night_climate_time_fail']."</p> <p>" .mysqli_error($conn). "</p>";
                        }
                }
		else {
	                $query = "UPDATE schedule_night_climate_time SET sync = '0', status = '{$sc_en}', start_time = '{$start_time}', end_time = '{$end_time}', WeekDays = '{$mask}' where id = 1;";
        	        $timeresults = $conn->query($query);
                	if ($timeresults) {
                        	$message_success = "<p>".$lang['night_climate_time_success']."</p>";
	                        header("Refresh: 3; url=".$return_url);
        	        } else {
                	        $error = "<p>".$lang['night_climate_error']."</p><p>".mysqli_error($conn). "</p>";
	                }
		}
                $query = "SELECT * FROM zone;";
                $result = $conn->query($query);
                $zcount = $result->num_rows;
                if ($zcount != 0) {
	                foreach($_POST['id'] as $id){
        	                $id = $_POST['id'][$id];
                	        $status = isset($_POST['status'][$id]) ? $_POST['status'][$id] : "0";
                        	//$status = $_POST['status'][$id];
	                        $min =TempToDB($conn,$_POST['min'][$id]);
        	                $max =TempToDB($conn,$_POST['max'][$id]);
                	        $query = "UPDATE schedule_night_climat_zone SET sync = '0', status='$status', min_temperature='".number_format(TempToDB($conn,$_POST['min_temp'][$id]),1)."', max_temperature='".number_format(TempToDB($conn,$_POST['max_temp'][$id]),1)."' WHERE id='$id'";
                        	$zoneresults = $conn->query($query);
	                        if ($zoneresults) {
        	                        $message_success .= "<p>".$lang['night_climate_temp_success']."</p>";
					header("Refresh: 3; url=".$return_url);
                	        } else {
                                $error .= "<p>".$lang['night_climate_error']."</p><p>".mysqli_error($conn). "</p>";
                        	}
                	}
		}
        } else {
                $sch_name = $_POST['sch_name'];
                $query = "INSERT INTO `schedule_daily_time`(`id`, `sync`, `purge`, `status`, `start`, `end`, `WeekDays`, `sch_name`) VALUES ('{$time_id}','0', '0', '{$sc_en}', '{$start_time}','{$end_time}','{$mask}', '{$sch_name}') ON DUPLICATE KEY UPDATE sync = VALUES(sync),  status = VALUES(status), start = VALUES(start), end = VALUES(end), WeekDays = VALUES(WeekDays), sch_name=VALUES(sch_name);";
		$result = $conn->query($query);
		$schedule_daily_time_id = mysqli_insert_id($conn);

		if ($result) {
			$message_success = $lang['schedule_time_modify_success'];
			header("Refresh: 3; url=".$return_url);
		} else {
			$error = $lang['schedule_time_modify_error']."<p>".mysqli_error($conn)."</p>"."  id1: ".$time_id;
		}

		foreach($_POST['id'] as $id){
			$id = $_POST['id'][$id];
			if(isset($_GET['id'])) {
				$tzid = $id;
				$schedule_daily_time_id = $time_id;
				$zoneid = $_POST['zoneid'][$id];
			} else {
				$tzid = 0;
				$zoneid = $id;
			}
			$status = isset($_POST['status'][$id]) ? $_POST['status'][$id] : "0";
			$coop = isset($_POST['coop'][$id]) ? $_POST['coop'][$id] : "0";
			$temp=TempToDB($conn,$_POST['temp'][$id]);
                        $sunset = isset($_POST['sunset'][$id]) ? $_POST['sunset'][$id] : "0";
                        $sunset_offset = intval($_POST['sunset_offset'][$id]);

			$query = "INSERT INTO `schedule_daily_time_zone`(`id`, `sync`, `purge`, `status`, `schedule_daily_time_id`, `zone_id`, `temperature`, `holidays_id`, `coop`, `sunset`, `sunset_offset`) VALUES ('{$tzid}', '0', '0', '{$status}', '{$schedule_daily_time_id}','{$zoneid}','".number_format($temp,1)."',{$holidays_id},{$coop},{$sunset},{$sunset_offset}) ON DUPLICATE KEY UPDATE sync = VALUES(sync), status = VALUES(status), temperature = VALUES(temperature), coop = VALUES(coop), sunset = VALUES(sunset), sunset_offset = VALUES(sunset_offset);";
			$zoneresults = $conn->query($query);

			if ($zoneresults) {
				#$message_success = "<p>".$lang['zone_record_success']."</p>";
			} else {
				#$error = "<p>".$lang['zone_record_fail']." </p> <p>" .mysqli_error($conn). "</p>"."  schedule_daily_time_id: ".$schedule_daily_time_id."  id: ".$id."  tzid: ".$tzid."  zone id: ".$zoneid."  holid: ".$holidays_id;
				$error = "<p>".$lang['zone_record_fail']." </p> <p>" .mysqli_error($conn). "</p>";
			}
		}
	}
}
?>

<!-- ### Visible Page ### -->
<?php include("header.php"); ?>
<?php include_once("notice.php"); ?>

<!-- Don't display form after submit -->
<?php if (!(isset($_POST['submit']))) { ?>

<!-- If the request is to EDIT, retrieve selected items from DB   -->
<?php if(isset($_GET['nid'])) {
        $query = "SELECT `id`, `sync`, `purge`, `status`, `start_time` as start, `end_time` as end, `WeekDays` FROM schedule_night_climate_time WHERE id = 1;";
        $results = $conn->query($query);
        $time_row = mysqli_fetch_assoc($results);
        $query = "select * from schedule_night_climat_zone_view where zone_status = 1;";
        $zoneresults = $conn->query($query);
} elseif ($time_id != 0) {
        $query = "SELECT * FROM schedule_daily_time WHERE id = {$time_id}";
        $results = $conn->query($query);
        $time_row = mysqli_fetch_assoc($results);

        $query = "select * from schedule_daily_time_zone_view where time_id = {$time_id}";
        $zoneresults = $conn->query($query);
} else {
        $query = "select zone.id as tz_id, zone.name as zone_name, zone.status as tz_status, zone_type.type, zone_type.category, zone_sensors.max_c FROM zone, zone_type, zone_sensors WHERE (zone.type_id = zone_type.id) AND (zone.id = zone_sensors.zone_id) AND status = 1 AND zone.`purge`= 0 ORDER BY index_id asc;";
	$zoneresults = $conn->query($query);
}
?>

<!-- Title (e.g. Add Schedule or Edit Schedule) -->
<div id="page-wrapper">
	<br>
 	<div class="row">
        	<div class="col-lg-12">
			<div class="panel panel-primary">
                        	<div class="panel-heading">
					<i class="fa fa-clock-o fa-fw"></i>
                                        <?php if(isset($_GET['nid'])) {
                                        	echo $lang['night_climate'];
                                     	} elseif ($time_id != 0) {
                                        	echo $lang['schedule_edit'] . ": " . $time_row['sch_name'];
                                        } else {
                                        	echo $lang['schedule_add'];
                                    	} ?>
					<div class="pull-right"> 
						<div class="btn-group"><?php echo date("H:i"); ?></div> 
					</div>
                        	</div>
                        	<!-- /.panel-heading -->
                        	<div class="panel-body">

            				<form data-toggle="validator" role="form" method="post" action="<?php $_SERVER['PHP_SELF'];?>" id="form-join">

					<!-- Enable Schedule -->
					<div class="checkbox checkbox-default checkbox-circle">
						<input id="checkbox0" class="styled" type="checkbox" name="sc_en" value="1" <?php $check = ($time_row['status'] == 1) ? 'checked' : ''; echo $check; ?>>
						<label for="checkbox0"> <?php echo $lang['schedule_enable']; ?></label>
					</div>

					<!-- Day Selector -->
					<div class="row">
						<div class="col-xs-3">
							<div class="checkbox checkbox-default checkbox-circle">
    								<input id="checkbox1" class="styled" type="checkbox" name="Sunday_en" value="1" <?php $check = (($time_row['WeekDays'] & 1) > 0) ? 'checked' : ''; echo $check; ?>>
    								<label for="checkbox1"> <?php echo $lang['sun']; ?></label>
							</div>
						</div>

						<div class="col-xs-3">
							<div class="checkbox checkbox-default checkbox-circle">
    								<input id="checkbox2" class="styled" type="checkbox" name="Monday_en" value="1" <?php $check = (($time_row['WeekDays'] & 2) > 0) ? 'checked' : ''; echo $check; ?>>
    								<label for="checkbox2"> <?php echo $lang['mon']; ?></label>
							</div>
						</div>

        					<div class="col-xs-3">
							<div class="checkbox checkbox-default checkbox-circle">
    								<input id="checkbox3" class="styled" type="checkbox" name="Tuesday_en" value="1" <?php $check = (($time_row['WeekDays'] & 4) > 0) ? 'checked' : ''; echo $check; ?>>
    								<label for="checkbox3"> <?php echo $lang['tue']; ?></label>
							</div>
						</div>

						<div class="col-xs-3">
							<div class="checkbox checkbox-default checkbox-circle">
    								<input id="checkbox4" class="styled" type="checkbox" name="Wednesday_en" value="1" <?php $check = (($time_row['WeekDays'] & 8) > 0) ? 'checked' : ''; echo $check; ?>>
    								<label for="checkbox4"> <?php echo $lang['wed']; ?></label>
							</div>
						</div>

        					<div class="col-xs-3">
							<div class="checkbox checkbox-default checkbox-circle">
    								<input id="checkbox5" class="styled" type="checkbox" name="Thursday_en" value="1" <?php $check = (($time_row['WeekDays'] & 16) > 0) ? 'checked' : ''; echo $check; ?>>
	    							<label for="checkbox5"> <?php echo $lang['thu']; ?></label>
							</div>
						</div>

						<div class="col-xs-3">
							<div class="checkbox checkbox-default checkbox-circle">
    								<input id="checkbox6" class="styled" type="checkbox" name="Friday_en" value="1" <?php $check = (($time_row['WeekDays'] & 32) > 0) ? 'checked' : ''; echo $check; ?>>
    								<label for="checkbox6"> <?php echo $lang['fri']; ?></label>
							</div>
						</div>

						<div class="col-xs-3">
							<div class="checkbox checkbox-default checkbox-circle">
    								<input id="checkbox7" class="styled" type="checkbox" name="Saturday_en" value="1" <?php $check = (($time_row['WeekDays'] & 64) > 0) ? 'checked' : ''; echo $check; ?>>
    								<label for="checkbox7"> <?php echo $lang['sat']; ?></label>
							</div>
						</div>
					</div>
					<!-- /.row -->

					<!-- Schedule Name -->
                        		<?php if(!isset($_GET['nid'])) {
                        		echo '<div class="form-group" class="control-label">
                                		<label><'.$lang['sch_name'].'></label>
                                		<input class="form-control input-sm" type="text" id="sch_name" name="sch_name" value="'.$time_row["sch_name"].'" placeholder="Schedule Name">
                                		<div class="help-block with-errors"></div>
                        		</div>'; } ?>

					<!-- Start Time -->
                                        <div class="form-group" class="control-label"><label><?php echo $lang['start_time']; ?></label>
                        			<input class="form-control input-sm" type="time" id="start_time" name="start_time" value="<?php echo $time_row["start"];?>" placeholder="Start Time" required>
                                                <div class="help-block with-errors"></div>
                                        </div>
						
					<!-- End Time -->
					<div class="form-group" class="control-label"><label><?php echo $lang['end_time']; ?></label>
						<input class="form-control input-sm" type="time" id="end_time" name="end_time" value="<?php echo $time_row["end"];?>" placeholder="End Time" required>
						<div class="help-block with-errors"></div>
					</div>
						
					<label><?php echo $lang['select_zone']; ?></label>
					<?php
					// Zone List Loop
					while ($row = mysqli_fetch_assoc($zoneresults)) {
						?>
						<hr>
						<!-- Zone ID (tz_id) -->
						<input type="hidden" name="id[<?php echo $row["tz_id"];?>]" value="<?php echo $row["tz_id"];?>">
						<?php if($time_id != 0){
							echo '<input type="hidden" name="zoneid['.$row["tz_id"].']" value="'.$row["zone_id"].'">';
						}?>
						<!-- Zone Enable Checkbox -->
						<div class="checkbox checkbox-default  checkbox-circle">
							<input id="checkbox<?php echo $row["tz_id"];?>" class="styled" type="checkbox" name="status[<?php echo $row["tz_id"];?>]" value="1" <?php if($time_id != 0){ $check = ($row['tz_status'] == 1) ? 'checked' : ''; echo $check;} ?> onclick="$('#<?php echo $row["tz_id"];?>').toggle();">
    							<label for="checkbox<?php echo $row["tz_id"];?>"><?php echo $row["zone_name"];?></label>
							
    							<div class="help-block with-errors"></div>
						</div>

						<!-- Group Zone Settings -->
						<?php
						if ($row["category"] == 0 OR $row["category"] == 1) {
							if($row['tz_status'] == 1 AND $time_id != 0){
								//if($time_id != 0){
								$style_text = "";
							}else{
								$style_text = "display:none !important;";
							}
							 echo '<div id="'.$row["tz_id"].'" style="'.$style_text.'">
								<div class="form-group" class="control-label">';
									//0=C, 1=F
									$c_f = settings($conn, 'c_f');
    									if(($c_f==1 || $c_f=='1') AND ($row["type"]=='Heating')) {
										$min = 50;
									}elseif (($c_f==1 || $c_f=='1') AND ($row["type"]=='Water' OR $row["type"]=='Immersion')) {
										$min = 50;
									}elseif (($c_f==0 || $c_f=='0') AND ($row["type"]=='Heating')) {
										$min = 10;
									}elseif (($c_f==0 || $c_f=='0') AND ($row["type"]=='Water' OR $row["type"]=='Immersion')) {
										$min = 10;
									}
									$max = $row['max_c'];
        								if(!isset($_GET['nid'])) {
										//<!-- Zone Coop Enable Checkbox -->
								        	if($time_id != 0){ $check = ($row['coop'] == 1) ? 'checked' : ''; }
									        echo '<div class="checkbox checkbox-default  checkbox-circle">
											<input id="coop'.$row["tz_id"].'" class="styled" type="checkbox" name="coop['.$row["tz_id"].']" value="1" '.$check.'>
										        <label for="coop'.$row["tz_id"].'">Coop Start</label> <i class="glyphicon glyphicon-leaf green"></i>
										        <i class="fa fa-info-circle fa-lg text-info" data-container="body" data-toggle="popover" data-placement="right" data-content="'.$lang['schedule_coop_help'].'"></i>
											<div class="help-block with-errors"></div>
										</div>';
									        // <!-- Temperature and Slider -->
									        if($time_id != 0){ $temp = DispTemp($conn, $row['temperature']);} else { $temp = '15.0';}
									        echo '<div class="slidecontainer">
											<h4>'.$lang['temperature'].': <span id="val'.$row["zone_id"].'" style="display: inline-flex !important; font-size:18px !important;"><output name="show_temp_val" id="temp'.$row["tz_id"].'" style="padding-top:0px !important; font-size:18px !important;">'.$temp.'</output></span>&deg;</h4><br>
									        	<input type="range" min="'.$min.'" max="'.$max.'" step="0.5" value="'.$temp.'" class="slider" id="bb'.$row["tz_id"].'" name="temp['.$row["tz_id"].']" oninput=update_temp(this.value,"temp'.$row["tz_id"].'")>
	                							</div>';
        								} else {
										// <!-- Temperature and Slider -->
									        echo '<div class="slidecontainer">
										 	<h4>'.$lang['min_temperature'].': <span id="min_val'.$row["zone_id"].'" style="display: inline-flex !important; font-size:18px !important;"><output name="show_min_temp_val" id="min_temp'.$row["tz_id"].'" style="padding-top:0px !important; font-size:18px !important;">'.DispTemp($conn, $row['min_temperature']).'</output></span>&deg;</h4><br>
										        <input type="range" min="'.$min.'" max="'.$max.'" step="0.5" value="'.DispTemp($conn, $row['min_temperature']).'" class="slider" id="min_bb'.$row["tz_id"].'" name="min_temp['.$row["tz_id"].']" oninput=update_temp(this.value,"min_temp'.$row["tz_id"].'")>
									        </div>';

									        echo '<div class="slidecontainer">
										 	<h4>'.$lang['max_temperature'].': <span id="max_val'.$row["zone_id"].'" style="display: inline-flex !important; font-size:18px !important;"><output name="show_max_temp_val" id="max_temp'.$row["tz_id"].'" style="padding-top:0px !important; font-size:18px !important;">'.DispTemp($conn, $row['max_temperature']).'</output></span>&deg;</h4><br>
										        <input type="range" min="'.$min.'" max="'.$max.'" step="0.5" value="'.DispTemp($conn, $row['max_temperature']).'" class="slider" id="max_bb'.$row["tz_id"].'" name="max_temp['.$row["tz_id"].']" oninput=update_temp(this.value,"max_temp'.$row["tz_id"].'")>
									       	</div>'; 
									}
        								?>
    								</div>
								<!-- /.form-group -->
							</div>
                                                        <!-- /.row -->
                                                <?php } else {


                                                        if($row['tz_status'] == 1 AND $time_id != 0){
                                                                //if($time_id != 0){
                                                                $style_text = "";
                                                        }else{
                                                                $style_text = "display:none !important;";
                                                        }
                                                         echo '<div id="'.$row["tz_id"].'" style="'.$style_text.'">
                                                                <div class="form-group" class="control-label">';
                                                                        if(!isset($_GET['nid'])) {
                                                                                //<!-- Zone Sunset Enable Checkbox -->
                                                                                if($time_id != 0){ $check = ($row['sunset'] == 1) ? 'checked' : ''; }
                                                                                echo '<div class="checkbox checkbox-default  checkbox-circle">
                                                                			<input id="sunset'.$row["tz_id"].'" class="styled" type="checkbox" name="sunset['.$row["tz_id"].']" value="1" '.$check.'>
                                                                			<label for="sunset'.$row["tz_id"].'">'.$lang['sunset_enable'].'</label>
											&nbsp;&nbsp;<input class="styled" type="text" id="sunset_offset['.$row["tz_id"].']" name="sunset_offset['.$row["tz_id"].']" style="width: 40px" value="'.$row["sunset_offset"].'"/>
                                                                                        &nbsp;&nbsp;<img src="./images/sunset.png">
                									<i class="fa fa-info-circle fa-lg text-info" data-container="body" data-toggle="popover" data-placement="right" data-content="'.$lang['sunset_enable_info'].'"></i>
                                                                                        <div class="help-block with-errors"></div>
                                                                                </div>';
                                                                        }
                                                                        ?>
                                                                </div>
                                                                <!-- /.form-group -->
                                                        </div>
                                                        <!-- /.row -->
                                                <?php }

                                        }?> <!-- End of Zone List Loop  -->
                                        <br>
					<!-- Buttons -->
					<a href="<?php echo $return_url ?>"><button type="button" class="btn btn-primary btn-sm" ><?php echo $lang['cancel']; ?></button></a>
                			<input type="submit" name="submit" value="<?php echo $lang['submit']; ?>" class="btn btn-default btn-sm login">
					</form>
				</div>
                        	<!-- /.panel-body -->
				<div class="panel-footer">
					<?php
					ShowWeather($conn);
					?>
                        	</div>
				<!-- /.panel-footer -->
                    	</div>
			<!-- /.panel-primary -->
		</div>
                <!-- /.col-lg-12 -->
	</div>
        <!-- /.row -->
</div>
<!-- /#page-wrapper -->
<?php }  ?>
<?php include("footer.php"); ?>

<script language="javascript" type="text/javascript">
function update_temp(value, id)
{
 var valuetext = value;
 var idtext = id;
 document.getElementById(id).innerTex = parseFloat(value);
 document.getElementById(id).value = parseFloat(value);
}
</script>

