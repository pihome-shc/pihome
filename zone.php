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

$date_time = date('Y-m-d H:i:s');

if(isset($_GET['id'])) {
	$id = $_GET['id'];
} else {
	$id = 0;
}
//Form submit
if (isset($_POST['submit'])) {
        $zone_category = $_POST['selected_zone_category'];
	$zone_status = isset($_POST['zone_status']) ? $_POST['zone_status'] : "0";
	$index_id = $_POST['index_id'];
	$name = $_POST['name'];
	$type = $_POST['selected_zone_type'];
	$max_c = $_POST['max_c'];
	$max_operation_time = $_POST['max_operation_time'];
	$hysteresis_time = $_POST['hysteresis_time'];
	$sp_deadband = $_POST['sp_deadband'];
	$sensor_id = $_POST['selected_sensor_id'];
	$sensor_child_id = $_POST['sensor_child_id'];
	$controler = $_POST['selected_controler_id'];
	$controler_id = $_POST['selected_controler_id'];
	$controler_child_id = $_POST['controler_child_id'];
	$boost_button_id = $_POST['boost_button_id'];
	$boost_button_child_id = $_POST['boost_button_child_id'];
	if ($_POST['zone_gpio'] == 0){$gpio_pin='0';} else {$gpio_pin = $_POST['zone_gpio'];}

	$boiler = explode('-', $_POST['boiler_id'], 2);
	$boiler_id = $boiler[0];

	//query to search node id for temperature sensors
	if ($zone_category < 2) {
		$query = "SELECT * FROM nodes WHERE node_id = '{$sensor_id}' LIMIT 1;";
		$result = $conn->query($query);
		$found_product = mysqli_fetch_array($result);
		$sensor_id = $found_product['id'];
	}

	//query to search node id for zone controller
	$query = "SELECT * FROM nodes WHERE node_id = '{$controler_id}' LIMIT 1;";
	$result = $conn->query($query);
	$found_product = mysqli_fetch_array($result);
	$controler_id = $found_product['id'];

	//If boost button console isnt installed then no need to add this to message_out
	if ($boost_button_id != 0 && $id==0){
		//query to search node id for boost button
		$query = "SELECT * FROM nodes WHERE node_id = '{$boost_button_id}' LIMIT 1;";
		$result = $conn->query($query);
		$found_product = mysqli_fetch_array($result);
		$boost_button_id = $found_product['node_id'];
	}

    //Add or Edit Zone record to Zone Table
        if ($zone_category == 0) {
		$query = "INSERT INTO `zone` (`id`, `sync`, `purge`, `status`, `index_id`, `name`, `type`, `model`, `graph_it`, `max_c`, `max_operation_time`, `hysteresis_time`, `sp_deadband`, `sensor_id`, `sensor_child_id`, `controler_id`, `controler_child_id`) VALUES ('{$id}', '0', '0', '{$zone_status}', '{$index_id}', '{$name}', '{$type}', 'NULL', '1', '{$max_c}', '{$max_operation_time}', '{$hysteresis_time}', '{$sp_deadband}', '{$sensor_id}', '{$sensor_child_id}', '{$controler_id}', '{$controler_child_id}') ON DUPLICATE KEY UPDATE status=VALUES(status), index_id=VALUES(index_id), name=VALUES(name), type=VALUES(type), max_c=VALUES(max_c), max_operation_time=VALUES(max_operation_time), hysteresis_time=VALUES(hysteresis_time), sp_deadband=VALUES(sp_deadband), sensor_id=VALUES(sensor_id), sensor_child_id=VALUES(sensor_child_id), controler_id=VALUES(controler_id), controler_child_id=VALUES(controler_child_id);";
	} elseif ($zone_category == 1) {
		$query = "INSERT INTO `zone` (`id`, `sync`, `purge`, `status`, `index_id`, `name`, `type`, `model`, `graph_it`, `max_c`, `max_operation_time`, `hysteresis_time`, `sp_deadband`, `sensor_id`, `sensor_child_id`, `controler_id`, `controler_child_id`) VALUES ('{$id}', '0', '0', '{$zone_status}', '{$index_id}', '{$name}', '{$type}', 'NULL', '1', '{$max_c}', '{$max_operation_time}', '{$hysteresis_time}', '{$sp_deadband}', '{$sensor_id}', '{$sensor_child_id}', '{$controler_id}', '{$controler_child_id}') ON DUPLICATE KEY UPDATE status=VALUES(status), index_id=VALUES(index_id), name=VALUES(name), type=VALUES(type), max_c=VALUES(max_c), max_operation_time=VALUES(max_operation_time), hysteresis_time=VALUES(hysteresis_time), sp_deadband=VALUES(sp_deadband), sensor_id=VALUES(sensor_id), sensor_child_id=VALUES(sensor_child_id), controler_id=VALUES(controler_id), controler_child_id=VALUES(controler_child_id);";
	} else {
		$query = "INSERT INTO `zone` (`id`, `sync`, `purge`, `status`, `index_id`, `name`, `type`, `model`, `graph_it`, `max_operation_time`, `sp_deadband`, `controler_id`, `controler_child_id`) VALUES ('{$id}', '0', '0', '{$zone_status}', '{$index_id}', '{$name}', '{$type}', 'NULL', '1', '{$max_operation_time}', '0', '{$controler_id}', '{$controler_child_id}') ON DUPLICATE KEY UPDATE status=VALUES(status), index_id=VALUES(index_id), name=VALUES(name), type=VALUES(type), max_operation_time=VALUES(max_operation_time), sp_deadband=VALUES(sp_deadband), controler_id=VALUES(controler_id), controler_child_id=VALUES(controler_child_id);";
	}
	$result = $conn->query($query);
	$zone_id = mysqli_insert_id($conn);
	if ($result) {
		$message_success = "<p>".$lang['zone_record_success']."</p>";
	} else {
		$error = "<p>".$lang['zone_record_fail']." </p> <p>" .mysqli_error($conn). "</p>";
	}

	//check if Controller id already exist in message_out table
	$query = "SELECT * FROM messages_out WHERE node_id = '{$controler}' AND child_id = '{$controler_child_id}' AND zone_id = '{$zone_id}' LIMIT 1;";
	$result = $conn->query($query);
	if ($result) {
		//Add Zone to message out table at same time to send out instructions to controller for each zone.
		if ($node_id !=0 OR $node_id !='0'){
			$query = "INSERT INTO `messages_out` (`sync`, `purge`, `node_id`, `child_id`, `sub_type`, `ack`, `type`, `payload`, `sent`, `datetime`, `zone_id`) VALUES ('0', '0', '{$controler}','{$controler_child_id}', '1', '1', '2', '0', '0', '{$date_time}', '{$zone_id}');";
			$result = $conn->query($query);
			$result = $conn->query($query);
			if ($result) {
				$message_success .= "<p>".$lang['zone_controler_success']."</p>";
			} else {
				$error .= "<p>".$lang['zone_controler_fail']."</p> <p>" .mysqli_error($conn). "</p>";
			}
		}
	}

	//If boost button console isnt installed and editing existing zone, then no need to add this to message_out
	if ($boost_button_id != 0 && $id==0){
		//Add Zone Boost Button Console to messageout table at same time
		$query = "INSERT INTO `messages_out` (`sync`, `purge`, `node_id`, `child_id`, `sub_type`, `ack`, `type`, `payload`, `sent`,  `datetime`, `zone_id`) VALUES ('0', '0', '{$boost_button_id}','{$boost_button_child_id}', '2', '0', '0', '2', '1', '{$date_time}', '{$zone_id}');";
		$result = $conn->query($query);
		if ($result) {
			$message_success .= "<p>".$lang['zone_button_success']."</p>";
		} else {
			$error .= "<p>".$lang['zone_button_fail']."</p> <p>" .mysqli_error($conn). "</p>";
		}
	}

	//Add Zone to boost table at same time
	if ($id==0){
		$query = "INSERT INTO `boost`(`sync`, `purge`, `status`, `zone_id`, `time`, `temperature`, `minute`, `boost_button_id`, `boost_button_child_id`) VALUES ('0', '0', '0', '{$zone_id}', '{$date_time}', '{$max_c}','{$max_operation_time}', '{$boost_button_id}', '{$boost_button_child_id}');";
		$result = $conn->query($query);
		if ($result) {
			$message_success .= "<p>".$lang['zone_boost_success']."</p>";
		} else {
			$error .= "<p>".$lang['zone_boost_fail']."</p> <p>" .mysqli_error($conn). "</p>";
		}
	}

        if ($zone_category < 2) {
		//Add or Edit Zone to override table at same time
		if ($id==0){
			$query = "INSERT INTO `override`(`sync`, `purge`, `status`, `zone_id`, `time`, `temperature`) VALUES ('0', '0', '0', '{$zone_id}', '{$date_time}', '{$max_c}');";
		} else {
			$query = "UPDATE override SET temperature='{$max_c}' WHERE zone_id='{$zone_id}';";
		}
		$result = $conn->query($query);
		if ($result) {
			$message_success .= "<p>".$lang['zone_override_success']."</p>";
		} else {
			$error .= "<p>".$lang['zone_override_fail']."</p> <p>" .mysqli_error($conn). "</p>";
		}

		//Add Zone to schedule_night_climat_zone table at same time
		if ($id==0){
			$query = "SELECT * FROM schedule_night_climate_time;";
        		$result = $conn->query($query);
			$nctcount = $result->num_rows;
        		if ($nctcount == 0) {
				$query = "INSERT INTO `schedule_night_climate_time` VALUES (1,1,0,0,'18:00:00','23:30:00',0);";
		        	$result = $conn->query($query);
	        		if ($result) {
        	        		$message_success .= "<p>".$lang['schedule_night_climate_time_success']."</p>";
        			} else {
                			$error .= "<p>".$lang['schedule_night_climate_time_fail']."</p> <p>" .mysqli_error($conn). "</p>";
	        		}
			}
			$query = "INSERT INTO `schedule_night_climat_zone` (`sync`, `purge`, `status`, `zone_id`, `schedule_night_climate_id`, `min_temperature`, `max_temperature`) VALUES ('0', '0', '0', '{$zone_id}', '1', '18','21');";
			$result = $conn->query($query);
			if ($result) {
				$message_success .= "<p>".$lang['zone_night_climate_success']."</p>";
			} else {
				$error .= "<p>".$lang['zone_night_climate_fail']."</p> <p>" .mysqli_error($conn). "</p>";
			}
		}
	}
        $date_time = date('Y-m-d H:i:s');
	//query to check if default away record exists
        $query = "SELECT * FROM away LIMIT 1;";
	$result = $conn->query($query);
        $acount = $result->num_rows;
	if ($acount == 0) {
                $query = "INSERT INTO `away` VALUES (1,0,0,0,'{$date_time}','{$date_time}',40, 4);";
               	$result = $conn->query($query);
	        if ($result) {
                        $message_success .= "<p>".$lang['away_success']."</p>";
               	} else {
                       	$error .= "<p>".$lang['away_fail']."</p> <p>" .mysqli_error($conn). "</p>";
	        }
        }

	//query to check if default holiday record exists
        $query = "SELECT * FROM holidays LIMIT 1;";
	$result = $conn->query($query);
        $hcount = $result->num_rows;
	if ($hcount == 0) {
                $query = "INSERT INTO `holidays` VALUES (1,0,0,0,'{$date_time}','{$date_time}');";
               	$result = $conn->query($query);
	        if ($result) {
                        $message_success .= "<p>".$lang['holidays_success']."</p>";
               	} else {
                       	$error .= "<p>".$lang['holidays_fail']."</p> <p>" .mysqli_error($conn). "</p>";
	        }
        }
	$message_success .= "<p>".$lang['do_not_refresh']."</p>";
	header("Refresh: 10; url=home.php");
	// After update on all required tables, set $id to mysqli_insert_id.
	if ($id==0){$id=$zone_id;}
}
?>

<!-- ### Visible Page ### -->
<?php include("header.php");  ?>
<?php include_once("notice.php"); ?>

<!-- Don't display form after submit -->
<?php if (!(isset($_POST['submit']))) { ?>

<!-- If the request is to EDIT, retrieve selected items from DB   -->
<?php if ($id != 0) {
	$query = "select * from zone where id = {$id} limit 1;";
	$result = $conn->query($query);
	$row = mysqli_fetch_assoc($result);

	$query = "SELECT * FROM nodes WHERE id = '{$row['sensor_id']}' LIMIT 1;";
	$result = $conn->query($query);
	$rownode = mysqli_fetch_assoc($result);

	$query = "SELECT * FROM nodes WHERE id = '{$row['controler_id']}' LIMIT 1;";
	$result = $conn->query($query);
	$rowcont = mysqli_fetch_assoc($result);

	$query = "SELECT * FROM boost WHERE zone_id = '{$row['id']}' LIMIT 1;";
	$result = $conn->query($query);
	$rowboost = mysqli_fetch_assoc($result);

	$query = "SELECT id, node_id, name FROM boiler WHERE id = '{$row['boiler_id']}' LIMIT 1;";
	$result = $conn->query($query);
	$rowboiler = mysqli_fetch_assoc($result);
}
?>

<!-- Title (e.g. Add Zone or Edit Zone) -->
<div id="page-wrapper">
<br>
            <div class="row">
                <div class="col-lg-12">
                   <div class="panel panel-primary">
                        <div class="panel-heading">
							<?php if ($id != 0) { echo $lang['zone_edit'] . ": " . $row['name']; }else{
                            echo "<i class=\"fa fa-plus fa-1x\"></i>" . $lang['zone_add'];} ?>
						<div class="pull-right"> <div class="btn-group"><?php echo date("H:i"); ?></div> </div>
                        </div>
                        <!-- /.panel-heading -->
<div class="panel-body">

<form data-toggle="validator" role="form" method="post" action="<?php $_SERVER['PHP_SELF'];?>" id="form-join">

<!-- Enable Zone -->
<div class="checkbox checkbox-default checkbox-circle">
<input id="checkbox0" class="styled" type="checkbox" name="zone_status" value="1" <?php $check = ($row['status'] == 1) ? 'checked' : ''; echo $check; ?>>>
<label for="checkbox0"> <?php echo $lang['zone_enable']; ?> </label> <small class="text-muted"><?php echo $lang['zone_enable_info'];?></small>
<div class="help-block with-errors"></div></div>

<!-- Index Number -->
<?php 
$query = "select index_id from zone order by index_id desc limit 1;";
$result = $conn->query($query);
$found_product = mysqli_fetch_array($result);
$new_index_id = $found_product['index_id']+1;
?>
<div class="form-group" class="control-label"><label><?php echo $lang['zone_index_number']; ?>  </label> <small class="text-muted"><?php echo $lang['zone_index_number_info'];?></small>
<input class="form-control" placeholder="<?php echo $lang['zone_index_number']; ?>r" value="<?php if(isset($row['index_id'])) { echo $row['index_id']; }else {echo $new_index_id; }  ?>" id="index_id" name="index_id" data-error="<?php echo $lang['zone_index_number_help']; ?>" autocomplete="off" required>
<div class="help-block with-errors"></div></div>

<!-- Zone Name -->
<div class="form-group" class="control-label"><label><?php echo $lang['zone_name']; ?></label> <small class="text-muted"><?php echo $lang['zone_name_info'];?></small>
<input class="form-control" placeholder="Zone Name" value="<?php if(isset($row['name'])) { echo $row['name']; } ?>" id="name" name="name" data-error="<?php echo $lang['zone_name_help']; ?>" autocomplete="off" required>
<div class="help-block with-errors"></div></div>

<!-- Zone Type -->
<input type="hidden" id="selected_zone_category" name="selected_zone_category" value="<?php echo $category?>"/>
<input type="hidden" id="selected_zone_type" name="selected_zone_type" value="<?php echo $type?>"/>
<div class="form-group" class="control-label"><label><?php echo $lang['zone_type']; ?></label> <small class="text-muted"><?php echo $lang['zone_type_info'];?></small>
<select id="type" onchange=zone_category(this.options[this.selectedIndex].value) name="type" class="form-control select2" autocomplete="off" required>
<?php if(isset($row['type'])) { echo '<option selected >'.$row['type'].'</option>'; } ?>
<?php  $query = "SELECT DISTINCT `type`, `category` FROM `zone_type` ORDER BY `id` ASC;";
$result = $conn->query($query);
echo "<option></option>";
while ($datarw=mysqli_fetch_array($result)) {
        echo "<option value=".$datarw['category'].">".$datarw['type']."</option>"; } ?>
</select>
<div class="help-block with-errors"></div></div>

<script language="javascript" type="text/javascript">
function zone_category(value)
{
        var valuetext = value;
	document.getElementById("selected_zone_category").value = value;
        var e = document.getElementById("type");
        var selected_type = e.options[e.selectedIndex].text;
        document.getElementById("selected_zone_type").value = selected_type;
        switch (valuetext) {
                case "0":
                        document.getElementById("max_c").style.display = 'block';
                        document.getElementById("max_operation_time_label").style.visibility = 'visible';;
                        document.getElementById("hysteresis_time").style.display = 'block';
                        document.getElementById("hysteresis_time_label").style.visibility = 'visible';;
                        document.getElementById("sp_deadband").style.display = 'block';
                        document.getElementById("sp_deadband_label").style.visibility = 'visible';;
                        document.getElementById("sensor_id").style.display = 'block';
                        document.getElementById("sensor_id_label").style.visibility = 'visible';;
                        document.getElementById("sensor_child_id").style.display = 'block';
                        document.getElementById("sensor_child_id_label").style.visibility = 'visible';;
                        document.getElementById("boost_button_id").style.display = 'block';
                        document.getElementById("boost_button_id_label").style.visibility = 'visible';;
                        document.getElementById("boost_button_child_id").style.display = 'block';
                        document.getElementById("boost_button_child_id_label").style.visibility = 'visible';;
                        document.getElementById("boiler_id").style.display = 'block';
                        document.getElementById("boiler_id_label").style.visibility = 'visible';;
                        break;
                case "1":
                        document.getElementById("max_c").style.display = 'block';
                        document.getElementById("max_c_label").style.visibility = 'visible';;
                        document.getElementById("hysteresis_time").style.display = 'block';
                        document.getElementById("hysteresis_time_label").style.visibility = 'visible';;
                        document.getElementById("sp_deadband").style.display = 'block';
                        document.getElementById("sp_deadband_label").style.visibility = 'visible';;
                        document.getElementById("sensor_id").style.display = 'block';
                        document.getElementById("sensor_id_label").style.visibility = 'visible';;
                        document.getElementById("sensor_child_id").style.display = 'block';
                        document.getElementById("sensor_child_id_label").style.visibility = 'visible';;
                        document.getElementById("boost_button_id").style.display = 'block';
                        document.getElementById("boost_button_id_label").style.visibility = 'visible';;
                        document.getElementById("boost_button_child_id").style.display = 'block';
                        document.getElementById("boost_button_child_id_label").style.visibility = 'visible';;
                        document.getElementById("boiler_id").style.display = 'none';
                        document.getElementById("boiler_id_label").style.visibility = 'hidden';;
                        break;
                case "2":
                        document.getElementById("max_c").style.display = 'none';
                        document.getElementById("max_c_label").style.visibility = 'hidden';;
                        document.getElementById("hysteresis_time").style.display = 'none';
                        document.getElementById("hysteresis_time_label").style.visibility = 'hidden';;
                        document.getElementById("sp_deadband").style.display = 'none';
                        document.getElementById("sp_deadband_label").style.visibility = 'hidden';;
                        document.getElementById("sensor_id").style.display = 'none';
                        document.getElementById("sensor_id_label").style.visibility = 'hidden';;
                        document.getElementById("sensor_child_id").style.display = 'none';
                        document.getElementById("sensor_child_id_label").style.visibility = 'hidden';;
                        document.getElementById("boost_button_id").style.display = 'none';
                        document.getElementById("boost_button_id_label").style.visibility = 'hidden';;
                        document.getElementById("boost_button_child_id").style.display = 'none';
                        document.getElementById("boost_button_child_id_label").style.visibility = 'hidden';;
                        document.getElementById("boiler_id").style.display = 'none';
                        document.getElementById("boiler_id_label").style.visibility = 'hidden';;
                        break;
                default:
        }
}
</script>

<!-- Maximum Temperature -->
<div class="form-group" class="control-label" id="max_c_label" style="display:block"><label><?php echo $lang['max_temperature']; ?></label> <small class="text-muted"><?php echo $lang['zone_max_temperature_info'];?></small>
<input class="form-control" placeholder="<?php echo $lang['zone_max_temperature_help']; ?>" value="<?php if(isset($row['max_c'])) { echo $row['max_c']; } else {echo '25';}  ?>" id="max_c" name="max_c" data-error="<?php echo $lang['zone_max_temperature_error']; ?>"  autocomplete="off" required>
<div class="help-block with-errors"></div></div>

<!-- Maximum Operation Time -->
<div class="form-group" class="control-label" id="max_operation_time_label" style="display:block"><label><?php echo $lang['zone_max_operation_time']; ?></label> <small class="text-muted"><?php echo $lang['zone_max_operation_time_info'];?></small>
<input class="form-control" placeholder="<?php echo $lang['zone_max_operation_time_help']; ?>" value="<?php if(isset($row['max_operation_time'])) { echo $row['max_operation_time']; } else {echo '60';}  ?>" id="max_operation_time" name="max_operation_time" data-error="<?php echo $lang['zone_max_operation_time_error']; ?>"  autocomplete="off" required>
<div class="help-block with-errors"></div></div>

<!-- Hysteresis Time -->
<div class="form-group" class="control-label" id="hysteresis_time_label" style="display:block"><label><?php echo $lang['hysteresis_time']; ?></label> <small class="text-muted"><?php echo $lang['zone_hysteresis_info'];?></small>
<input class="form-control" placeholder="<?php echo $lang['zone_hysteresis_time_help']; ?>" value="<?php if(isset($row['hysteresis_time'])) { echo $row['hysteresis_time']; } else {echo '3';} ?>" id="hysteresis_time" name="hysteresis_time" data-error="<?php echo $lang['zone_hysteresis_time_error']; ?>"  autocomplete="off" required>
<div class="help-block with-errors"></div></div>

<!-- Temperature Setpoint Deadband -->
<div class="form-group" class="control-label" id="sp_deadband_label" style="display:block"><label><?php echo $lang['zone_sp_deadband']; ?></label> <small class="text-muted"><?php echo $lang['zone_sp_deadband_info'];?></small>
<input class="form-control" placeholder="<?php echo $lang['zone_sp_deadband_help']; ?>" value="<?php if(isset($row['sp_deadband'])) { echo $row['sp_deadband']; } else {echo '0.5';} ?>" id="sp_deadband" name="sp_deadband" data-error="<?php echo $lang['zone_sp_deadband_error'] ; ?>"  autocomplete="off" required>
<div class="help-block with-errors"></div></div>

<!-- Temperature Sensor ID -->
<div class="form-group" class="control-label" id="sensor_id_label" style="display:block"><label><?php echo $lang['temp_sensor_id']; ?></label> <small class="text-muted"><?php echo $lang['zone_sensor_id_info'];?></small>
<select id="sensor_id" onchange=SensorChildList(this.options[this.selectedIndex].value) name="sensor_id" class="form-control select2" data-error="<?php echo $lang['zone_temp_sensor_id_error']; ?>" autocomplete="off" required>
<?php if(isset($rownode['node_id'])) { echo '<option selected >'.$rownode['node_id'].'</option>'; } ?>
<?php  $query = "SELECT node_id, max_child_id FROM nodes where name = 'Temperature Sensor' ORDER BY node_id ASC;";
$result = $conn->query($query);
echo "<option></option>";
while ($datarw=mysqli_fetch_array($result)) {
	echo "<option value=".$datarw['max_child_id'].">".$datarw['node_id']."</option>"; } ?>
</select>
<div class="help-block with-errors"></div></div>

<script language="javascript" type="text/javascript">
function SensorChildList(value)
{
        var valuetext = value;
	var e = document.getElementById("sensor_id");
	var selected_sensor_id = e.options[e.selectedIndex].text;
	document.getElementById("selected_sensor_id").value = selected_sensor_id;

        var opt = document.getElementById("sensor_child_id").getElementsByTagName("option");
        for(j=opt.length-1;j>=0;j--)
        {
                document.getElementById("sensor_child_id").options.remove(j);
        }
        for(j=0;j<=valuetext;j++)
        {
                var optn = document.createElement("OPTION");
                optn.text = j;
                optn.value = j;
                document.getElementById("sensor_child_id").options.add(optn);
        }}
</script>
<input type="hidden" id="selected_sensor_id" name="selected_sensor_id" value="<?php echo $rownode['node_id']?>"/>

<!-- Temperature Sensor Child ID -->
<div class="form-group" class="control-label" id="sensor_child_id_label" style="display:block"><label><?php echo $lang['temp_sensor_child_id']; ?></label> <small class="text-muted"><?php echo $lang['zone_sensor_id_info'];?></small>
<select id="sensor_child_id" name="sensor_child_id" class="form-control select2" data-error="<?php echo $lang['zone_temp_sensor_id_error']; ?>" autocomplete="off" required>
<?php if(isset($row['sensor_child_id'])) { echo '<option selected >'.$row['sensor_child_id'].'</option>';
for ($x = 0; $x <= $rownode['max_child_id']; $x++) {
        echo "<option value=".$x.">".$x."</option>";
        }
} ?>
</select>
<div class="help-block with-errors"></div></div>

<!-- Zone Controller ID -->
<div class="form-group" class="control-label"><label><?php echo $lang['zone_controller_id']; ?></label> <small class="text-muted"><?php echo $lang['zone_controler_id_info'];?></small>
<select id="controler_id" onchange=ControlerChildList(this.options[this.selectedIndex].value) name="controler_id" class="form-control select2" data-error="<?php echo $lang['zone_controller_id_error']; ?>" autocomplete="off" required>
<?php if(isset($rowcont['node_id'])) { echo '<option selected >'.$rowcont['type'].' - '.$rowcont['node_id'].'</option>'; } ?>
<?php  $query = "SELECT node_id, type, max_child_id FROM nodes where name = 'Zone Controller Relay' OR name = 'Zone Controller' OR name = 'Relay Controller' OR name = 'GPIO Controller' OR name = 'I2C Controller' ORDER BY node_id ASC;";
$result = $conn->query($query);
echo "<option></option>";
while ($datarw=mysqli_fetch_array($result)) {
	echo "<option value=".$datarw['max_child_id'].">".$datarw['type'].' - '.$datarw['node_id']."</option>"; } ?>
</select>
<div class="help-block with-errors"></div></div>

<script language="javascript" type="text/javascript">
function ControlerChildList(value)
{
        var valuetext = value;
        var e = document.getElementById("controler_id");
        var selected_controler_id = e.options[e.selectedIndex].text;
        var selected_controler_id = selected_controler_id.split(" - ");
        document.getElementById("selected_controler_id").value = selected_controler_id[1];
        document.getElementById("selected_controler_type").value = selected_controler_id[0];
	var gpio_pins = document.getElementById('gpio_pin_list').value

        var opt = document.getElementById("controler_child_id").getElementsByTagName("option");
        for(j=opt.length-1;j>=0;j--)
        {
                document.getElementById("controler_child_id").options.remove(j);
        }
	if(selected_controler_id.includes("GPIO")) {
		var pins_arr = gpio_pins.split(',');
                for(j=0;j<=pins_arr.length-1;j++)
                {
                        var optn = document.createElement("OPTION");
                        optn.text = pins_arr[j];
                        optn.value = pins_arr[j];
                        document.getElementById("controler_child_id").options.add(optn);
                }
	} else {
        	for(j=1;j<=valuetext;j++)
        	{
                	var optn = document.createElement("OPTION");
                	optn.text = j;
                	optn.value = j;
                	document.getElementById("controler_child_id").options.add(optn);
        	}
	}
}
</script>
<input type="hidden" id="selected_controler_id" name="selected_controler_id" value="<?php echo $rowcont['node_id']?>"/>
<input type="hidden" id="selected_controler_type" name="selected_controler_type" value="<?php echo $rowcont['type']?>"/>
<input type="hidden" id="gpio_pin_list" name="gpio_pin_list" value="<?php echo implode(",", array_filter(Get_GPIO_List()))?>"/>
<!-- Zone Controller Child ID -->
<div class="form-group" class="control-label"><label><?php echo $lang['zone_controller_child_id']; ?></label> <small class="text-muted"><?php echo $lang['zone_controler_child_id_info'];?></small>
<select id="controler_child_id" name="controler_child_id" class="form-control select2"  data-error="<?php echo $lang['zone_controller_child_id_error']; ?>" autocomplete="off" required>
<?php if(isset($row['controler_child_id'])) {
	echo '<option selected >'.$row['controler_child_id'].'</option>';
	$pos=strpos($rowcont['type'], "GPIO");
	if($pos !== false) {
		$gpio_list=Get_GPIO_List();
		for ($x = 0; $x <= count(array_filter($gpio_list)) - 1; $x++) {
        		echo "<option value=".$gpio_list[$x].">".$gpio_list[$x]."</option>";
        	}
	} else {
                for ($x = 1; $x <= $rowcont['max_child_id']; $x++) {
                        echo "<option value=".$x.">".$x."</option>";
                }
	}
} ?>
</select>
<div class="help-block with-errors"></div></div>

<!-- Boost Button ID -->
<?php if($id==0) {
	echo '<div class="form-group" class="control-label" id="boost_button_id_label" style="display:block"><label>'.$lang['zone_boost_button_id'].'</label> <small class="text-muted">'.$lang['zone_boost_info'].'</small><select id="boost_button_id" name="boost_button_id" class="form-control select2" data-error="'.$lang['zone_boost_id_error'].'" autocomplete="off" >';
	if(isset($rowboost['boost_button_id'])) {
		echo '<option selected >'.$rowboost['boost_button_id'].'</option>';
	} else {
		echo '<option selected value="0">N/A</option>';
	}
	$query = "SELECT node_id FROM nodes where name = 'Button Console';";
	$result = $conn->query($query);
	while ($datarw=mysqli_fetch_array($result)) {
		$node_id=$datarw["node_id"];
		echo "<option>$node_id</option>";
	}
	echo '</select><div class="help-block with-errors"></div></div>';
}?>

<!-- Boost Button Child ID -->
<?php if($id==0) {
	echo '<div class="form-group" class="control-label" id="boost_button_child_id_label" style="display:block"><label>'.$lang['zone_boost_button_child_id'].'</label> <small class="text-muted">'.$lang['zone_boost_button_info'].'</small><select id="boost_button_child_id" name="boost_button_child_id" class="form-control select2" data-error="'.$lang['zone_boost_child_id_error'].'" autocomplete="off" required>';
	if(isset($rowboost['boost_button_child_id'])) {
		echo '<option selected >'.$rowboost['boost_button_child_id'].'</option>';
	}else {
		echo '<option selected value="0">N/A</option>';
	}
	echo '<option>1</option><option>2</option><option>3</option><option>4</option><option>5</option><option>6</option><option>7</option><option>8</option>';
	echo '</select><div class="help-block with-errors"></div></div>';
}?>

<!-- Boiler -->
<div class="form-group" class="control-label" id="boiler_id_label" style="display:block"><label><?php echo $lang['boiler']; ?></label>
<select id="boiler_id" name="boiler_id" class="form-control select2" data-error="Boiler ID can not be empty!" autocomplete="off" required>
<?php if(isset($rowboiler['id'])) { echo '<option selected >'.$rowboiler['id'].'-'.$rowboiler['name'].' Node ID: '.$rowboiler['node_id'].'</option>'; } ?>
<?php  $query = "SELECT id, node_id, name FROM boiler;";
$result = $conn->query($query);
while ($datarw=mysqli_fetch_array($result)) {
$boiler_id=$datarw["id"].'-'.$datarw["name"].' Node ID: '.$datarw["node_id"];
echo "<option>$boiler_id</option>";} ?>
</select>
<div class="help-block with-errors"></div></div>

<!-- Buttons -->
<input type="submit" name="submit" value="<?php echo $lang['submit']; ?>" class="btn btn-default btn-sm">
<a href="home.php"><button type="button" class="btn btn-primary btn-sm"><?php echo $lang['cancel']; ?></button></a>
</form>
                        </div>
                        <!-- /.panel-body -->
						<div class="panel-footer">
<?php
ShowWeather($conn);
?>
                            <div class="pull-right">
                                <div class="btn-group">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.col-lg-4 -->
            </div>
            <!-- /.row -->
        </div>
        <!-- /#page-wrapper -->
<?php }  ?>
<?php include("footer.php");  ?>

