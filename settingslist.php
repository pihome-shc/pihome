<?php require_once("st_inc/session.php"); ?>
<?php confirm_logged_in(); ?>
<?php require_once("st_inc/connection.php"); ?>
<?php require_once("st_inc/functions.php"); ?>
<?php 
	//query to frost protection temperature 
	$query = "SELECT * FROM frost_protection LIMIT 1 ";
	$result = mysql_query($query, $connection);
	confirm_query($result);
	$frosttemp = mysql_fetch_array($result);
	$frost_temp = $frosttemp['temperature'];
?>                        <div class="panel panel-primary">
                        <div class="panel-heading">
                            <i class="fa fa-cog fa-fw"></i>   Settings    
						<div class="pull-right"> <div class="btn-group"><?php echo date("H:i"); ?></div> </div>
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
						
							<button class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" data-toggle="modal" data-target="#add_frost">
							<h3 class="buttontop"><small>Frost</small></h3>
							<i class="ion-ios-snowy larger blue"></i>
							<h3 class="status" style="margin-top:-11px;"><small style="color:#048afd;"><i class="fa fa-circle fa-fw"></i></small>
							<small class="statusdegree"><?php echo $frost_temp ;?>&deg;</small><small class="zoonstatus"> <i class="fa"></i></small>
							</h3></button>	

							<button class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" data-href="edit_boiler.php" data-toggle="modal" data-target="#boiler_safety_setup">
							<h3 class="buttontop"><small>Boiler</small></h3>
							<h3 class="degre" ><i class="ionicons ion-flame fa-1x red"></i></h3>
							<h3 class="status"><small style="color:#fff;"><i class="fa"></i></small>
							</h3></button>

							<button class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" data-href="edit_gpio.php" data-toggle="modal" data-target="#boost_setup">
							<h3 class="buttontop"><small>Boost</small></h3>
							<h3 class="degre" ><i class="fa fa-rocket fa-1x blueinfo"></i></h3>
							<h3 class="status"><small style="color:#fff;"><i class="fa"></i></small>
							</h3></button>

							
							<button class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" data-href="edit_override.php" data-toggle="modal" data-target="#override_setup">
							<h3 class="buttontop"><small>Override</small></h3>
							<h3 class="degre" ><i class="fa fa-refresh fa-1x blue"></i></h3>
							<h3 class="status"><small style="color:#fff;"><i class="fa"></i></small>
							</h3></button>

							<button class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" data-href="edit_sensors.php" data-toggle="modal" data-target="#temperature_sensor">
							<h3 class="buttontop"><small>Sensors</small></h3>
							<h3 class="degre" ><i class="ionicons ion-thermometer red"></i></h3>
							<h3 class="status"><small style="color:#fff;"><i class="fa"></i></small>
							</h3></button>

							<button class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" data-href="edit_gpio.php" data-toggle="modal" data-target="#zone_setup">
							<h3 class="buttontop"><small>Zone</small></h3>
							<h3 class="degre" ><i class="glyphicon glyphicon-th-large orange"></i> </h3>
							<h3 class="status"><small style="color:#fff;"><i class="fa"></i></small>
							</h3></button>							

							<button class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" data-href="edit_sensors.php" data-toggle="modal" data-target="#sensor_gateway">
							<h3 class="buttontop"><small>Gateway</small></h3>
							<h3 class="degre" ><i class="fa fa-heartbeat red"></i></h3>
							<h3 class="status"><small style="color:#fff;"><i class="fa"></i></small>
							</h3></button>

							<button class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" data-href="edit_gpio.php" data-toggle="modal" data-target="#cron_jobs">
							<h3 class="buttontop"><small>Cron Jobs</small></h3>
							<h3 class="degre" ><i class="ionicons ion-ios-timer-outline blue"></i></h3>
							<h3 class="status"><small style="color:#fff;"><i class="fa"></i></small>
							</h3></button>
<?php 
	$query = "select * from messages_in where node_id = 0 order by datetime desc limit 1";
	$result = mysql_query($query, $connection);
	$result = mysql_fetch_array($result);
	$system_cc = $result['payload'];
	if ($system_cc < 40){$system_cc="#0bb71b"; $fan=" ";}elseif ($system_cc < 50){$system_cc="#F0AD4E"; $fan="fa-pulse";}elseif ($system_cc > 50){$system_cc="#ff0000"; $fan="fa-pulse";}
?>							
							<button type="button" class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" data-toggle="modal" data-target="#system_c">
							<h3 class="buttontop"><small>System C&deg;</small></h3>
							<h3 class="degre" ><i class="fa fa-server fa-1x green"></i></h3>
							<h3 class="status"><small style="color:<?php echo $system_cc;?>"><i class="fa fa-circle fa-fw"></i></small>
							<small class="statusdegree"><?php echo number_format($result['payload'],0);?>&deg;</small><small class="zoonstatus"> <i class="fa fa-asterisk <?php echo $fan;?>"></i></small>
							</h3></button>	
							
							<button type="button" class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" data-toggle="modal" data-target="#os_version">
							<h3 class="buttontop"><small>OS Version</small></h3>
							<h3 class="degre" ><i class="fa fa-linux"></i></h3>
							<h3 class="status"><small style="color:#fff;"><i class="fa"></i></small>
							</h3></button>
							
							<button type="button" class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" data-toggle="modal" data-target="#pihome_update">
							<h3 class="buttontop"><small>PiHome Update</small></h3>
							<h3 class="degre" ><i class="fa fa-download fa-1x blueinfo"></i></h3>
							<h3 class="status"><small style="color:#fff;"><i class="fa"></i></small>
							</h3></button>
							
							<button type="button" class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" data-toggle="modal" data-target="#system_uptime">
							<h3 class="buttontop"><small>Uptime</small></h3>
							<h3 class="degre" ><i class="ionicons ion-clock red"></i></h3>
							<h3 class="status"><small style="color:#fff;"><i class="fa"></i></small>
							</h3></button>

							<button type="button" class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" data-toggle="modal" data-target="#backup_image">
							<h3 class="buttontop"><small>Backup</small></h3>
							<h3 class="degre" ><i class="fa fa-clone fa-1x blue"></i> </h3>
							<h3 class="status"><small style="color:#fff;"><i class="fa"></i></small>
							</h3></button>
							
							<button type="button" class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" data-toggle="modal" data-target="#wifi_setup">
							<h3 class="buttontop"><small>WiFi</small></h3>
							<h3 class="degre" ><i class="fa fa-signal green"></i></h3>
							<h3 class="status"><small style="color:#fff;"><i class="fa"></i></small>
							</h3></button>							

							<button type="button" class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" data-toggle="modal" data-target="#eth_setup">
							<h3 class="buttontop"><small>Ethernet</small></h3>
							<h3 class="degre" ><i class="ionicons ion-network orange"></i></h3>
							<h3 class="status"><small style="color:#fff;"><i class="fa"></i></small>
							</h3></button>
							
							<button type="button" class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" data-href="edit_users.php" data-toggle="modal" data-target="#user_setup">
							<h3 class="buttontop"><small>User Accounts</small></h3>
							<h3 class="degre" ><i class="ionicons ion-person blue"></i></h3>
							<h3 class="status"><small style="color:#fff;"><i class="fa"></i></small>
							</h3></button>

							<button type="button" class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" data-href="edit_users.php" data-toggle="modal" data-target="#big_thanks">
							<h3 class="buttontop"><small>Big Thanks</small></h3>
							<h3 class="degre" ><i class="ionicons ion-help-buoy blueinfo"></i></h3>
							<h3 class="status"><small style="color:#fff;"><i class="fa"></i></small>
							</h3></button>
							
							<button type="button" class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" onClick="reboot()">
							<h3 class="buttontop"><small>Reboot Pi</small></h3>
							<i class="ion-ios-refresh-outline larger orange"></i>
							<h3 class="status" style="margin-top:-11px;"><small style="color:#fff;"><i class="fa"></i></small>
							<small class="statusdegree"></small>
							</h3></button>

							<button type="button" class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" onClick="shutdown()">
							<h3 class="buttontop"><small>Shutdown Pi</small></h3>
							<h3 class="degre" ><i class="fa fa-power-off fa-1x red"></i></h3>
							<h3 class="status"><small style="color:#fff;"><i class="fa"></i></small>
							</h3></button>	
					
<?php include("add_frost.php");  ?>
<?php include("model.php");  ?>

                        </div>
                        <!-- /.panel-body -->
						<div class="panel-footer">
<?php 
$query="select * from weather";
$result = mysql_query($query, $connection);
confirm_query($result);
$weather = mysql_fetch_array($result);
?>

<?php echo $weather['c'] ;?>&deg;C
<span><img border="0" width="24" src="images/<?php echo $weather['img'];?>.png" title="<?php echo $weather['title'];?> - 
<?php echo $weather['description'];?>"></span> <span><?php echo $weather['title'];?> - 
<?php echo $weather['description'];?></span>
                        </div>
                    </div>