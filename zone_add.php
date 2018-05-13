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
	
	//query to search node id for boost button
	$query = "SELECT * FROM nodes WHERE node_id = '{$boost_button_id}' LIMIT 1;";
	$result = $conn->query($query);
	$found_product = mysqli_fetch_array($result);
	$boost_button_id = $found_product['node_id'];
	
	//Add zone record to Zone Talbe 
	$query = "INSERT INTO zone (status, index_id, name, type, max_c, max_operation_time, hysteresis_time, sensor_id, sensor_child_id, controler_id, controler_child_id, boiler_id) 
	VALUES ('{$zone_status}', '{$index_id}', '{$name}', '{$type}', '{$max_c}', '{$max_operation_time}', '{$hysteresis_time}', '{$sensor_id}', '{$sensor_child_id}', '{$controler_id}', '{$controler_child_id}', '{$boiler_id}');";
	$result = $conn->query($query);
	$zone_id = mysqli_insert_id($conn);
	if ($result) {
		$message_success = "<p>Zone Record Added Successfuly.</p>";
	} else {
		$error = "<p>Zone Record Addition Failed: </p> <p>" .mysqli_error($conn). "</p>";
	}

	//Add Zone to message out table at same time to send out instructions to controller for each zone. 
	$query = "INSERT INTO messages_out (node_id, child_id, sub_type, payload, sent, zone_id)VALUES ('{$controler}','{$controler_child_id}', '2', '0', '1', '{$zone_id}');";
	$result = $conn->query($query);
	if ($result) {
		$message_success .= "<p>Zone Controler Record Added Successfuly.</p>";
	} else {
		$error .= "<p>Zone Controler Recrd Addition Failed!!!</p> <p>" .mysqli_error($conn). "</p>";
	}

	//Add Zone Boost Button Console to messageout table at same time
	$query = "INSERT INTO messages_out (node_id, child_id, sub_type, payload, sent, zone_id)VALUES ('{$boost_button_id}','{$boost_button_child_id}', '2', '0', '1', '{$zone_id}');";
	$result = $conn->query($query);
	if ($result) {
		$message_success .= "<p>Zone Boost Button Record Added Successfuly.</p>";
	} else {
		$error .= "<p>Zone Boost Button Recrd Addition Failed!!!</p> <p>" .mysqli_error($conn). "</p>";
	}
	
	//Add Zone to boost table at same time
	$query = "INSERT INTO boost (status, zone_id, temperature, minute, boost_button_id, boost_button_child_id)VALUES ('0', '{$zone_id}','{$max_c}','{$max_operation_time}', '{$boost_button_id}', '{$boost_button_child_id}');";
	$result = $conn->query($query);
	if ($result) {
		$message_success .= "<p>Zone Boost Record Added Successfuly.</p>";
	} else {
		$error .= "<p>Zone Boost Recrd Addition Failed!!!</p> <p>" .mysqli_error($conn). "</p>";
	}
	
	//Add Zone to override table at same time
	$query = "INSERT INTO override (status, zone_id, temperature) VALUES ('0', '{$zone_id}','{$max_c}');";
	$result = $conn->query($query);
	if ($result) {
		$message_success .= "<p>Zone Override Record Added Successfuly.</p>";
	} else {
		$error .= "<p>Zone Override Record Addition Failed!!!</p> <p>" .mysqli_error($conn). "</p>";
	}
	
	//Add Zone to schedule_night_climat_zone table at same time
	$query = "INSERT INTO schedule_night_climat_zone (status, zone_id, schedule_night_climate_id, min_temperature, max_temperature) VALUES ('0', '{$zone_id}', '1', '18','21');";
	$result = $conn->query($query);
	if ($result) {
		$message_success .= "<p>Zone Night Climate Record Added Successfuly. </p>
		<p>Do Not refrest and click Back button on your Browser...</p>";
		header("Refresh: 10; url=home.php");
	} else {
		$error .= "<p>Zone Night Climate Record Addition Failed!!!</p> <p>" .mysqli_error($conn). "</p>";
	}
	$alert_message="Zone ".$name." will not be added to any exiting heating Schedule!!!";
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
                            <i class="fa fa-plus fa-1x"></i> Add Zone   
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
<label for="checkbox0"> Enable Zone </label>
<div class="help-block with-errors"></div></div>

<div class="form-group" class="control-label"><label>Index Number</label>
<input class="form-control" placeholder="Index Number" value="<?php if(isset($_POST['index_id'])) { echo $_POST['index_id']; }else {echo $new_index_id; }  ?>" id="index_id" name="index_id" data-error="Index Number should contain numbers only! This number will dertimin where icon will be placed on home screen for this Zone." autocomplete="off" required>
<div class="help-block with-errors"></div></div>

<div class="form-group" class="control-label"><label>Zone Name</label>
<input class="form-control" placeholder="Zone Name" value="<?php if(isset($_POST['name'])) { echo $_POST['name']; } ?>" id="name" name="name" data-error="Zone Name Can Not be Empty!" autocomplete="off" required>
<div class="help-block with-errors"></div></div>

<div class="form-group" class="control-label"><label>Zone Type</label>
<select id="type" name="type" class="form-control select2" placeholder="Zone Type i.e Heating or Water"  data-error="Zone type Either Heating or Water!" autocomplete="off" required>
<?php if(isset($_POST['type'])) { echo '<option selected >'.$_POST['type'].'</option>'; } ?>
<option>Heating</option>
<option>Water</option>
</select>				
<div class="help-block with-errors"></div></div>

<div class="form-group" class="control-label"><label>Maximum Temperature</label>
<input class="form-control" placeholder="Maximum temperature for this zone" value="<?php if(isset($_POST['max_c'])) { echo $_POST['max_c']; } ?>" id="max_c" name="max_c" data-error="Maximum temperature this Zone can reach before system shutdown this Zone for safty!"  autocomplete="off" required>
<div class="help-block with-errors"></div></div>
				
<div class="form-group" class="control-label"><label>Maximum Operation Time</label>
<input class="form-control" placeholder="Maximum opertation time for this zone, normally 45 minuts to 60 minuts." value="<?php if(isset($_POST['max_operation_time'])) { echo $_POST['max_operation_time']; } ?>" id="max_operation_time" name="max_operation_time" data-error="Maximum operation time this Zone can run before systems shutdown this Zone for safty!"  autocomplete="off" required>
<div class="help-block with-errors"></div></div>				

<div class="form-group" class="control-label"><label>Hysteresis Time</label>
<input class="form-control" placeholder="Hysteresis Time for Safty, Please consult your motorized volve technical manule for more details. Default is 3 Minuts." value="<?php if(isset($_POST['hysteresis_time'])) { echo $_POST['hysteresis_time']; } else {echo '3';} ?>" id="hysteresis_time" name="hysteresis_time" data-error="Hysteresis time for safty, Please consult your motorized volve technical manule for more details. Default is 3 Minuts."  autocomplete="off" required>
<div class="help-block with-errors"></div></div>	

<div class="form-group" class="control-label"><label>Temperature Sensor ID</label>
<select id="sensor_id" name="sensor_id" class="form-control select2" data-error="Zone sensor id can not be empty!" autocomplete="off" required>
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
 
<div class="form-group" class="control-label"><label>Zone Relay Controller ID</label>
<select id="controler_id" name="controler_id" class="form-control select2" data-error="Zone Controler ID can not be empty! Select Node connect to Zone's motorized valve, If your zone connected to RPI GPIO you can select 0" autocomplete="off" required>
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


<div class="form-group" class="control-label"><label>Zone Relay Controller's Child ID</label>
<select id="controler_child_id" name="controler_child_id" class="form-control select2"  data-error="Child ID on Zone Controller, This value is from 1 to 8 that connect to your Zone's motorized valve relay, If your zone connected to RPI GPIO you can select 0" autocomplete="off" required>
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


<div class="form-group" class="control-label"><label>Zone Relay GPIO Pin</label>
<select id="zone_gpio" name="zone_gpio" class="form-control select2" data-error="If Zone motorized valve connected to Raspberry pi GPIO Pin,  Make sure you follow WiringPi GPIO Pin. if you are using Wireless Relay you can select any GPIO." autocomplete="off" required>
<?php if(isset($_POST['zone_gpio'])) { echo '<option selected >'.$_POST['zone_gpio'].'</option>'; } ?>
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


<div class="form-group" class="control-label"><label>Boost Button ID</label>
<select id="boost_button_id" name="boost_button_id" class="form-control select2" data-error="If you have Boost Console then you can select Boost Console ID" autocomplete="off" >
<?php if(isset($_POST['boost_button_id'])) { echo '<option selected >'.$_POST['boost_button_id'].'</boost_button_id>'; } ?>
<?php  $query = "SELECT node_id FROM nodes where name = 'Button Console'";
$result = $conn->query($query);
echo "<option></option>";
while ($datarw=mysqli_fetch_array($result)) {
$node_id=$datarw["node_id"];
echo "<option>$node_id</option>";} ?>
</select>				
<div class="help-block with-errors"></div></div>

<div class="form-group" class="control-label"><label>Boost Button's Child ID</label>
<select id="boost_button_child_id" name="boost_button_child_id" class="form-control select2" data-error="Boost Console Button ID for this Zone" autocomplete="off" required>
<?php if(isset($_POST['boost_button_child_id'])) { echo '<option selected >'.$_POST['boost_button_child_id'].'</option>'; } ?>
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

<div class="form-group" class="control-label"><label>Boiler</label>
<select id="boiler_id" name="boiler_id" class="form-control select2" data-error="Boiler ID can not be empty!" autocomplete="off" required>
<?php if(isset($_POST['boiler_id'])) { echo '<option selected >'.$_POST['boiler_id'].'</option>'; } ?>
<?php  $query = "SELECT id, node_id, name FROM boiler;";
$result = $conn->query($query);
while ($datarw=mysqli_fetch_array($result)) {
$boiler_id=$datarw["id"].'-'.$datarw["name"].' Node ID: '.$datarw["node_id"] ;
echo "<option>$boiler_id</option>";} ?>
</select>				
<div class="help-block with-errors"></div></div>

<input type="submit" name="submit" value="Submit" class="btn btn-default btn-sm">
<a href="home.php"><button type="button" class="btn btn-primary btn-sm">Cancel</button></a>
</form>
                        </div>
                        <!-- /.panel-body -->
						<div class="panel-footer">
<?php 
$query="select * from weather";
$result = $conn->query($query);
$weather = mysqli_fetch_array($result);
?>
Outside: <?php //$weather = getWeather(); ?><?php echo $weather['c'] ;?>&deg;C
<span><img border="0" width="24" src="images/<?php echo $weather['img'];?>.png" title="<?php echo $weather['title'];?> - 
<?php echo $weather['description'];?>"></span> <span><?php echo $weather['title'];?> - 
<?php echo $weather['description'];?></span>
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