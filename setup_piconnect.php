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
require_once(__DIR__.'/st_inc/connection.php');
require_once(__DIR__.'/st_inc/functions.php');
?>
<?php include("header.php"); ?>
        <div id="page-wrapper">
<br>
            <div class="row">
                <div class="col-lg-12">
				<div class="panel panel-primary">
                        <div class="panel-heading">
                            <div class="Light"><i class="fa fa-plug fa-fw"></i> Setup PiConnect
						<div class="pull-right"> <div class="btn-group"><?php echo date("H:i"); ?></div> </div>
                        </div></div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
	<form data-toggle="validator" role="form" method="post" action="<?php $_SERVER['PHP_SELF'];?>" id="form-join">
	<?php 
	$query = "SELECT * FROM piconnect Limit 1;";
	$result = $conn->query($query);	
	$row = mysqli_fetch_assoc($result);
	?>
    <div class="checkbox checkbox-default checkbox-circle">
    <input id="checkbox0" class="styled" type="checkbox" name="sc_en" value="1" <?php $check = ($row['status'] == 1) ? 'checked' : ''; echo $check; ?>>
    <label for="checkbox0"> Enable PiConnect</label></div>
	
	<div class="form-group" class="control-label"><label>Protocol</label>
	<input class="form-control input-sm" type="text" id="protocol" name="protocol" value="<?php echo $row["protocol"];?>" placeholder="PiConnect Protocol" disabled>
    <div class="help-block with-errors"></div></div>
	
	
	<div class="form-group" class="control-label"><label>API Key</label>
	<input class="form-control input-sm" type="text" id="api_key" name="api_key" value="<?php echo $row["api_key"];?>" placeholder="PiConnect API Key" >
    <div class="help-block with-errors"></div></div>
						
						
<?php

/*
	$query = "UPDATE away SET `sync`='0';";
	$result = $conn->query($query);
	if ($result) {echo "<p class=\"text-info\"> <strong>".date('Y-m-d H:i:s'). "</strong> - MySQL DataBase Table away Records set to Sync 0 </p>"; }else {echo "<p class=\"text-danger\" <strong>".date('Y-m-d H:i:s'). "</strong> - MySQL DataBase Table away Records set to Sync 0 Failed </p>";}

	$query = "UPDATE boiler SET `sync`='0';";
	$result = $conn->query($query);
	if ($result) {echo "<p class=\"text-info\"> <strong>".date('Y-m-d H:i:s'). "</strong> - MySQL DataBase Table boiler Records set to Sync 0 </p>"; }else {echo "<p class=\"text-danger\" <strong>".date('Y-m-d H:i:s'). "</strong> - MySQL DataBase Table boiler Records set to Sync 0 Failed </p>";}

	$query = "UPDATE boiler_logs SET `sync`='0';";
	$result = $conn->query($query);
	if ($result) {echo "<p class=\"text-info\"> <strong>".date('Y-m-d H:i:s'). "</strong> - MySQL DataBase Table boiler_logs Records set to Sync 0 </p>"; }else {echo "<p class=\"text-danger\" <strong>".date('Y-m-d H:i:s'). "</strong> - MySQL DataBase Table boiler_logs Records set to Sync 0 Failed </p>";}

	$query = "UPDATE boost SET `sync` ='0';";
	$result = $conn->query($query);
	if ($result) {echo "<p class=\"text-info\"> <strong>".date('Y-m-d H:i:s'). "</strong> - MySQL DataBase Table boost Records set to Sync 0 </p>"; }else {echo "<p class=\"text-danger\" <strong>".date('Y-m-d H:i:s'). "</strong> - MySQL DataBase Table boost Records set to Sync 0 Failed </p>";}

	$query = "UPDATE frost_protection SET `sync`='0';";
	$result = $conn->query($query);
	if ($result) {echo "<p class=\"text-info\"> <strong>".date('Y-m-d H:i:s'). "</strong> - MySQL DataBase Table frost_protection Records set to Sync 0 </p>"; }else {echo "<p class=\"text-danger\" <strong>".date('Y-m-d H:i:s'). "</strong> - MySQL DataBase Table frost_protection Records set to Sync 0 Failed </p>";}

	$query = "UPDATE gateway SET `sync`='0';";
	$result = $conn->query($query);
	if ($result) {echo "<p class=\"text-info\"> <strong>".date('Y-m-d H:i:s'). "</strong> - MySQL DataBase Table gateway Records set to Sync 0 </p>"; }else {echo "<p class=\"text-danger\" <strong>".date('Y-m-d H:i:s'). "</strong> - MySQL DataBase Table gateway Records set to Sync 0 Failed </p>";}

	$query = "UPDATE gateway_logs SET `sync`='1';";
	$result = $conn->query($query);
	if ($result) {echo "<p class=\"text-info\"> <strong>".date('Y-m-d H:i:s'). "</strong> - MySQL DataBase Table gateway_logs Records set to Sync 1 </p>"; }else {echo "<p class=\"text-danger\" <strong>".date('Y-m-d H:i:s'). "</strong> - MySQL DataBase Table gateway_logs Records set to Sync 1 Failed </p>";}

	$query = "UPDATE messages_in SET `sync`='1';";
	$result = $conn->query($query);
	if ($result) {echo "<p class=\"text-info\"> <strong>".date('Y-m-d H:i:s'). "</strong> - MySQL DataBase Table messages_in Records set to Sync 1 </p>"; }else {echo "<p class=\"text-danger\" <strong>".date('Y-m-d H:i:s'). "</strong> - MySQL DataBase Table messages_in Records set to Sync 1 Failed </p>";}

	$query = "UPDATE nodes SET `sync`='0';";
	$result = $conn->query($query);
	if ($result) {echo "<p class=\"text-info\"> <strong>".date('Y-m-d H:i:s'). "</strong> - MySQL DataBase Table nodes Records set to Sync 0 </p>"; }else {echo "<p class=\"text-danger\" <strong>".date('Y-m-d H:i:s'). "</strong> - MySQL DataBase Table nodes Records set to Sync 0 Failed </p>";}

	$query = "UPDATE nodes_battery SET `sync`='1';";
	$result = $conn->query($query);
	if ($result) {echo "<p class=\"text-info\"> <strong>".date('Y-m-d H:i:s'). "</strong> - MySQL DataBase Table nodes_battery Records set to Sync 1 </p>"; }else {echo "<p class=\"text-danger\" <strong>".date('Y-m-d H:i:s'). "</strong> - MySQL DataBase Table nodes_battery Records set to Sync 1 Failed </p>";}

	$query = "UPDATE schedule_daily_time SET `sync`='0';";
	$result = $conn->query($query);
	if ($result) {echo "<p class=\"text-info\"> <strong>".date('Y-m-d H:i:s'). "</strong> - MySQL DataBase Table schedule_daily_time Records set to Sync 0 </p>"; }else {echo "<p class=\"text-danger\" <strong>".date('Y-m-d H:i:s'). "</strong> - MySQL DataBase Table schedule_daily_time Records set to Sync 0 Failed </p>";}

	$query = "UPDATE schedule_daily_time_zone SET `sync`='0';";
	$result = $conn->query($query);
	if ($result) {echo "<p class=\"text-info\"> <strong>".date('Y-m-d H:i:s'). "</strong> - MySQL DataBase Table schedule_daily_time_zone Records set to Sync 0 </p>"; }else {echo "<p class=\"text-danger\" <strong>".date('Y-m-d H:i:s'). "</strong> - MySQL DataBase Table schedule_daily_time_zone Records set to Sync 0 Failed </p>";}

	$query = "UPDATE schedule_night_climate_time SET `sync`='0';";
	$result = $conn->query($query);
	if ($result) {echo "<p class=\"text-info\"> <strong>".date('Y-m-d H:i:s'). "</strong> - MySQL DataBase Table schedule_night_climate_time Records set to Sync 0 </p>"; }else {echo "<p class=\"text-danger\" <strong>".date('Y-m-d H:i:s'). "</strong> - MySQL DataBase Table schedule_night_climate_time Records set to Sync 0 Failed </p>";}

	$query = "UPDATE schedule_night_climat_zone SET `sync`='0';";
	$result = $conn->query($query);
	if ($result) {echo "<p class=\"text-info\"> <strong>".date('Y-m-d H:i:s'). "</strong> - MySQL DataBase Table schedule_night_climat_zone Records set to Sync 0 </p>"; }else {echo "<p class=\"text-danger\" <strong>".date('Y-m-d H:i:s'). "</strong> - MySQL DataBase Table schedule_night_climat_zone Records set to Sync 0 Failed </p>";}

	$query = "UPDATE weather SET `sync`='0';";
	$result = $conn->query($query);
	if ($result) {echo "<p class=\"text-info\"> <strong>".date('Y-m-d H:i:s'). "</strong> - MySQL DataBase Table weather Records set to Sync 0 </p>"; }else {echo "<p class=\"text-danger\" <strong>".date('Y-m-d H:i:s'). "</strong> - MySQL DataBase Table weather Records set to Sync 0 Failed </p>";}

	$query = "UPDATE zone SET `sync`='0';";
	$result = $conn->query($query);
	if ($result) {echo "<p class=\"text-info\"> <strong>".date('Y-m-d H:i:s'). "</strong> - MySQL DataBase Table zone Records set to Sync 0 </p>"; }else {echo "<p class=\"text-danger\" <strong>".date('Y-m-d H:i:s'). "</strong> - MySQL DataBase Table zone Records set to Sync 0 Failed </p>";}

	$query = "UPDATE zone_logs SET `sync`='1';";
	$result = $conn->query($query);
	if ($result) {echo "<p class=\"text-info\"> <strong>".date('Y-m-d H:i:s'). "</strong> - MySQL DataBase Table zone_logs Records set to Sync 0 </p>"; }else {echo "<p class=\"text-danger\" <strong>".date('Y-m-d H:i:s'). "</strong> - MySQL DataBase Table zone_logs Records set to Sync 0 Failed </p>";}

*/	
?>	
                        <!-- /.panel-body -->
						</div><div class="panel-footer">
<?php 
ShowWeather($conn);
?>
                            <div class="pull-right">
                                <div class="btn-group">
                                </div>
                            </div>
                        </div>
                    </div>
<?php if(isset($conn)) { $conn->close();} ?>
                </div>
                <!-- /.col-lg-4 -->
            </div>
			<!-- /.row -->
	<div class="col-md-8 col-md-offset-2">
	<div class="login-panel-foother">
	<h6><a style="color: #707070;" href="https://en.wikipedia.org/wiki/Sudan_(rhinoceros)" target="_blank" >Dedicated to Sudan (Rhinoceros) 1973 - 2018</a></h6>
	</div>
	</div>
        </div>
        <!-- /#page-wrapper -->
		<?php include("footer.php"); ?>