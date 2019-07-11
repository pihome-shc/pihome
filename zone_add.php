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
//Form submit
if (isset($_POST['submit'])) {
	$zone_status = isset($_POST['zone_status']) ? $_POST['zone_status'] : "0";
	$index_id = $_POST['index_id'];
	$name = $_POST['name'];
	$type = $_POST['type'];
	$max_c = $_POST['max_c'];
	$max_operation_time = $_POST['max_operation_time'];
	$hysteresis_time = $_POST['hysteresis_time'];
	$sp_deadband = $_POST['sp_deadband'];
	$sensor_id = $_POST['sensor_id'];
	$controler = $_POST['controler_id'];
	$controler_id = $_POST['controler_id'];
	$controler_child_id = $_POST['controler_child_id'];
	$boost_button_id = $_POST['boost_button_id'];
	$boost_button_child_id = $_POST['boost_button_child_id'];
	//$zone_gpio = mysqli_prepare($_POST['zone_gpio']);
	$boiler = explode('-', $_POST['boiler_id'], 2);
	$boiler_id = $boiler[0];

	//query to search node id for temperature sensors
	$query = "SELECT * FROM nodes WHERE node_id = '{$sensor_id}' LIMIT 1;";
	$result = $conn->query($query);
	$found_product = mysqli_fetch_array($result);
	$sensor_id = $found_product['id'];
		
	//query to search node id for zone controller
	$query = "SELECT * FROM nodes WHERE node_id = '{$controler_id}' LIMIT 1;";
	$result = $conn->query($query);
	$found_product = mysqli_fetch_array($result);
	$controler_id = $found_product['id'];
	
	//If boost button console isnt installed then no need to add this to message_out
	if ($boost_button_id != 'None'){
		//query to search node id for boost button
		$query = "SELECT * FROM nodes WHERE node_id = '{$boost_button_id}' LIMIT 1;";
		$result = $conn->query($query);
		$found_product = mysqli_fetch_array($result);
		$boost_button_id = $found_product['node_id'];
	}
	
	//Add zone record to Zone Talbe 
	$query = "INSERT INTO zone (status, index_id, name, type, max_c, max_operation_time, hysteresis_time, sp_deadband, sensor_id, sensor_child_id, controler_id, controler_child_id, boiler_id) 
	VALUES ('{$zone_status}', '{$index_id}', '{$name}', '{$type}', '{$max_c}', '{$max_operation_time}', '{$hysteresis_time}', '{$sp_deadband}', '{$sensor_id}', '{$sensor_child_id}', '{$controler_id}', '{$controler_child_id}', '{$boiler_id}');";
	$result = $conn->query($query);
	$zone_id = mysqli_insert_id($conn);
	if ($result) {
		$message_success = "<p>".$lang['zone_record_success']."</p>";
	} else {
		$error = "<p>".$lang['zone_record_fail']." </p> <p>" .mysqli_error($conn). "</p>";
	}

	//Add Zone to message out table at same time to send out instructions to controller for each zone. 
	$query = "INSERT INTO messages_out (node_id, child_id, sub_type, ack, type, payload, sent, zone_id)VALUES ('{$controler}','{$controler_child_id}', '1', '0', '2', '0', '0', '{$zone_id}');";
	$result = $conn->query($query);
	if ($result) {
		$message_success .= "<p>".$lang['zone_controler_success']."</p>";
	} else {
		$error .= "<p>".$lang['zone_controler_fail']."</p> <p>" .mysqli_error($conn). "</p>";
	}
	
	//If boost button console isnt installed then no need to add this to message_out
	if ($boost_button_id != 'None'){
		//Add Zone Boost Button Console to messageout table at same time
		$query = "INSERT INTO messages_out (node_id, child_id, sub_type, payload, sent, zone_id)VALUES ('{$boost_button_id}','{$boost_button_child_id}', '2', '0', '1', '{$zone_id}');";
		$result = $conn->query($query);
		if ($result) {
			$message_success .= "<p>".$lang['zone_button_success']."</p>";
		} else {
			$error .= "<p>".$lang['zone_button_fail']."</p> <p>" .mysqli_error($conn). "</p>";
		}
	}
	
	//Add Zone to boost table at same time
	$query = "INSERT INTO boost (status, zone_id, temperature, minute, boost_button_id, boost_button_child_id)VALUES ('0', '{$zone_id}','{$max_c}','{$max_operation_time}', '{$boost_button_id}', '{$boost_button_child_id}');";
	$result = $conn->query($query);
	if ($result) {
		$message_success .= "<p>".$lang['zone_boost_success']."</p>";
	} else {
		$error .= "<p>".$lang['zone_boost_fail']."</p> <p>" .mysqli_error($conn). "</p>";
	}
	
	//Add Zone to override table at same time
	$query = "INSERT INTO override (status, zone_id, temperature) VALUES ('0', '{$zone_id}','{$max_c}');";
	$result = $conn->query($query);
	if ($result) {
		$message_success .= "<p>".$lang['zone_override_success']."</p>";
	} else {
		$error .= "<p>".$lang['zone_override_fail']."</p> <p>" .mysqli_error($conn). "</p>";
	}
	
	//Add Zone to schedule_night_climat_zone table at same time
	$query = "INSERT INTO schedule_night_climat_zone (status, zone_id, schedule_night_climate_id, min_temperature, max_temperature) VALUES ('0', '{$zone_id}', '1', '18','21');";
	$result = $conn->query($query);
	if ($result) {
		$message_success .= "<p>".$lang['zone_night_climate_success']."</p>
		<p>".$lang['do_not_refresh']."</p>";
		header("Refresh: 10; url=home.php");
	} else {
		$error .= "<p>".$lang['zone_night_climate_fail']."</p> <p>" .mysqli_error($conn). "</p>";
	}
	$alert_message=$lang['zone']." ".$name." ".$lang['zone_not_added_to_schedule'];
}
?>
<?php include("header.php");  ?>
<?php include_once("notice.php"); ?>
<div id="page-wrapper">
<br>
            <div class="row">
                <div class="col-lg-12">
                   <div class="panel panel-primary">
                        <div class="panel-heading">
                            <i class="fa fa-plus fa-1x"></i> <?php echo $lang['zone_add']; ?>   
						<div class="pull-right"> <div class="btn-group"><?php echo date("H:i"); ?></div> </div>
                        </div>
                        <!-- /.panel-heading -->
<div class="panel-body">
<form data-toggle="validator" role="form" method="post" action="<?php $_SERVER['PHP_SELF'];?>" id="form-join">
<?php 
$query = "select index_id from zone order by index_id desc limit 1;";
$result = $conn->query($query);
$found_product = mysqli_fetch_array($result);
$new_index_id = $found_product['index_id']+1;
?>
<div class="checkbox checkbox-default checkbox-circle">
<input id="checkbox0" class="styled" type="checkbox" name="zone_status" value="1">
<label for="checkbox0"> <?php echo $lang['zone_enable']; ?> </label>
<div class="help-block with-errors"></div></div>

<div class="form-group" class="control-label"><label><?php echo $lang['zone_index_number']; ?></label>
<input class="form-control" placeholder="<?php echo $lang['zone_index_number']; ?>r" value="<?php if(isset($_POST['index_id'])) { echo $_POST['index_id']; }else {echo $new_index_id; }  ?>" id="index_id" name="index_id" data-error="<?php echo $lang['zone_index_number_help']; ?>" autocomplete="off" required>
<div class="help-block with-errors"></div></div>

<div class="form-group" class="control-label"><label><?php echo $lang['zone_name']; ?></label>
<input class="form-control" placeholder="Zone Name" value="<?php if(isset($_POST['name'])) { echo $_POST['name']; } ?>" id="name" name="name" data-error="<?php echo $lang['zone_name_help']; ?>" autocomplete="off" required>
<div class="help-block with-errors"></div></div>

<div class="form-group" class="control-label"><label><?php echo $lang['zone_type']; ?></label>
<select id="type" name="type" class="form-control select2" autocomplete="off" required>
<?php if(isset($_POST['type'])) { echo '<option selected >'.$_POST['type'].'</option>'; } ?>
<option><?php echo $lang['zone_type_heating']; ?></option>
<option><?php echo $lang['zone_type_water']; ?></option>
</select>				
<div class="help-block with-errors"></div></div>

<div class="form-group" class="control-label"><label><?php echo $lang['max_temperature']; ?></label>
<input class="form-control" placeholder="<?php echo $lang['zone_max_temperature_help']; ?>" value="<?php if(isset($_POST['max_c'])) { echo $_POST['max_c']; } else {echo '25';}  ?>" id="max_c" name="max_c" data-error="<?php echo $lang['zone_max_temperature_error']; ?>"  autocomplete="off" required>
<div class="help-block with-errors"></div></div>
				
<div class="form-group" class="control-label"><label><?php echo $lang['zone_max_operation_time']; ?></label>
<input class="form-control" placeholder="<?php echo $lang['zone_max_operation_time_help']; ?>" value="<?php if(isset($_POST['max_operation_time'])) { echo $_POST['max_operation_time']; } else {echo '60';}  ?>" id="max_operation_time" name="max_operation_time" data-error="<?php echo $lang['zone_max_operation_time_error']; ?>"  autocomplete="off" required>
<div class="help-block with-errors"></div></div>				

<div class="form-group" class="control-label"><label><?php echo $lang['hysteresis_time']; ?></label>
<input class="form-control" placeholder="<?php echo $lang['zone_hysteresis_time_help']; ?>" value="<?php if(isset($_POST['hysteresis_time'])) { echo $_POST['hysteresis_time']; } else {echo '3';} ?>" id="hysteresis_time" name="hysteresis_time" data-error="<?php echo $lang['zone_hysteresis_time_error']; ?>"  autocomplete="off" required>
<div class="help-block with-errors"></div></div>	

<div class="form-group" class="control-label"><label><?php echo $lang['zone_sp_deadband']; ?></label>
<input class="form-control" placeholder="<?php echo $lang['zone_sp_deadband_help']; ?>" value="<?php if(isset($_POST['sp_deadband'])) { echo $_POST['sp_deadband']; } else {echo '0.5';} ?>" id="sp_deadband" name="sp_deadband" data-error="<?php echo $lang['zone_sp_deadband_error'] ; ?>"  autocomplete="off" required>
<div class="help-block with-errors"></div></div>	

<div class="form-group" class="control-label"><label><?php echo $lang['temp_sensor_id']; ?></label>
<select id="sensor_id" name="sensor_id" class="form-control select2" data-error="<?php echo $lang['zone_temp_sensor_id_error']; ?>" autocomplete="off" required>
<?php if(isset($_POST['node_id'])) { echo '<option selected >'.$_POST['node_id'].'</option>'; } ?>
<?php  $query = "SELECT node_id, child_id_1 FROM nodes where name = 'Temperature Sensor'";
$result = $conn->query($query);
echo "<option></option>";
while ($datarw=mysqli_fetch_array($result)) {
$node_id=$datarw["node_id"];
echo "<option>$node_id</option>";} ?>
</select>				
<div class="help-block with-errors"></div></div>

<input type="hidden" name="sensor_child_id" value="0">			
 
<div class="form-group" class="control-label"><label><?php echo $lang['zone_controller_id']; ?></label>
<select id="controler_id" name="controler_id" class="form-control select2" data-error="<?php echo $lang['zone_controller_id_error']; ?>" autocomplete="off" required>
<?php if(isset($_POST['controler_id'])) { echo '<option selected >'.$_POST['controler_id'].'</option>'; } ?>
<?php  $query = "SELECT node_id FROM nodes where name = 'Zone Controller Relay'";
$result = $conn->query($query);
echo "<option></option>";
while ($datarw=mysqli_fetch_array($result)) {
	$node_id=$datarw["node_id"];
	echo "<option>$node_id</option>";
} ?>
</select>				
<div class="help-block with-errors"></div></div>


<div class="form-group" class="control-label"><label><?php echo $lang['zone_controller_child_id']; ?></label>
<select id="controler_child_id" name="controler_child_id" class="form-control select2"  data-error="<?php echo $lang['zone_controller_child_id_error']; ?>" autocomplete="off" required>
<?php if(isset($_POST['controler_child_id'])) { echo '<option selected >'.$_POST['controler_child_id'].'</option>'; } ?>
<option></option>
<option>0</option>
<option>1</option>
<option>2</option>
<option>3</option>
<option>4</option>
<option>5</option>
<option>6</option>
<option>7</option>
<option>8</option>
</select>				
<div class="help-block with-errors"></div></div>


<div class="form-group" class="control-label"><label><?php echo $lang['zone_relay_gpio']; ?></label>
<select id="zone_gpio" name="zone_gpio" class="form-control select2" data-error="<?php echo $lang['zone_gpio_pin_error']; ?>" autocomplete="off" required>
<?php if(isset($_POST['zone_gpio'])) { echo '<option selected >'.$_POST['zone_gpio'].'</option>'; } else { echo '<option selected>0</option>';}?>
<option></option>
<option>0</option>
<option>1</option>
<option>2</option>
<option>3</option>
<option>4</option>
<option>5</option>
<option>6</option>
<option>7</option>
<option>21</option>
<option>22</option>
<option>23</option>
<option>24</option>
<option>25</option>
<option>26</option>
<option>27</option>
<option>28</option>
<option>29</option>
</select>				
<div class="help-block with-errors"></div></div>


<div class="form-group" class="control-label"><label><?php echo $lang['zone_boost_button_id']; ?></label>
<select id="boost_button_id" name="boost_button_id" class="form-control select2" data-error="<?php echo $lang['zone_boost_id_error']; ?>" autocomplete="off" >
<?php if(isset($_POST['boost_button_id'])) { echo '<option selected >'.$_POST['boost_button_id'].'</boost_button_id>'; } ?>
<?php  $query = "SELECT node_id FROM nodes where name = 'Button Console'";
$result = $conn->query($query);
echo "<option>None</option>";
while ($datarw=mysqli_fetch_array($result)) {
$node_id=$datarw["node_id"];
echo "<option>$node_id</option>";} ?>
</select>				
<div class="help-block with-errors"></div></div>

<div class="form-group" class="control-label"><label><?php echo $lang['zone_boost_button_child_id']; ?></label>
<select id="boost_button_child_id" name="boost_button_child_id" class="form-control select2" data-error="<?php echo $lang['zone_boost_child_id_error']; ?>" autocomplete="off" required>
<?php if(isset($_POST['boost_button_child_id'])) { echo '<option selected >'.$_POST['boost_button_child_id'].'</option>'; }else { echo '<option selected>None</option>';} ?>
<option></option>
<option>0</option>
<option>1</option>
<option>2</option>
<option>3</option>
<option>4</option>
<option>5</option>
<option>6</option>
<option>7</option>
<option>8</option>
</select>				
<div class="help-block with-errors"></div></div>

<div class="form-group" class="control-label"><label><?php echo $lang['boiler']; ?></label>
<select id="boiler_id" name="boiler_id" class="form-control select2" data-error="Boiler ID can not be empty!" autocomplete="off" required>
<?php if(isset($_POST['boiler_id'])) { echo '<option selected >'.$_POST['boiler_id'].'</option>'; } ?>
<?php  $query = "SELECT id, node_id, name FROM boiler;";
$result = $conn->query($query);
while ($datarw=mysqli_fetch_array($result)) {
$boiler_id=$datarw["id"].'-'.$datarw["name"].' Node ID: '.$datarw["node_id"] ;
echo "<option>$boiler_id</option>";} ?>
</select>				
<div class="help-block with-errors"></div></div>

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
<?php include("footer.php");  ?>
