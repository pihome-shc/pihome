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
//query to frost protection temperature 
$query = "SELECT * FROM frost_protection LIMIT 1 ";
$result = $conn->query($query);
$frosttemp = mysqli_fetch_array($result);
$frost_temp = $frosttemp['temperature'];
?>                      <div class="panel panel-primary">
                        <div class="panel-heading">
                        <i class="fa fa-cog fa-fw"></i>   Settings    
						<div class="pull-right"> <div class="btn-group"><?php echo date("H:i"); ?></div> </div>
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
						
							<button class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" data-href="edit_piconnect.php" data-toggle="modal" data-target="#piconnect">
							<h3 class="buttontop"><small>PiConnect</small></h3>
							<h3 class="degre" ><i class="fa fa-plug green"></i></h3>
							<h3 class="status"></small></h3>
                            </button>


							<button class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" data-toggle="modal" data-target="#add_frost">
							<h3 class="buttontop"><small>Frost</small></h3>
                            <h3 class="degre" ><i class="ionicons ion-ios-snowy blue"></i></h3>
							<h3 class="status">
                                <small class="statuscircle"><i class="fa fa-circle fa-fw blue"></i></small>
                                <small class="statusdegree"><?php echo number_format(DispTemp($conn,$frost_temp),0);?>&deg;</small>
                                <small class="statuszoon"><i class="fa"></i></small>
							</h3></button>	

<?php
$c_f = settings($conn, 'c_f');
if($c_f==1 || $c_f=='1')
    $TUnit='F';
else
    $TUnit='C';
?>
							<button class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" data-toggle="modal" data-target="#change_units">
							<h3 class="buttontop"><small>Units</small></h3>
                            <h3 class="degre" ><?php echo $TUnit;?></h3>
							<h3 class="status"></small></h3>
                            </button>

							<button class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" data-href="edit_boiler.php" data-toggle="modal" data-target="#boiler_safety_setup">
							<h3 class="buttontop"><small>Boiler</small></h3>
							<h3 class="degre" ><i class="ionicons ion-flame fa-1x red"></i></h3>
							<h3 class="status"></small></h3>
                            </button>

							<button class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" data-href="edit_gpio.php" data-toggle="modal" data-target="#boost_setup">
							<h3 class="buttontop"><small>Boost</small></h3>
							<h3 class="degre" ><i class="fa fa-rocket fa-1x blueinfo"></i></h3>
							<h3 class="status"></small></h3>
                            </button>

							
							<button class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" data-href="edit_override.php" data-toggle="modal" data-target="#override_setup">
							<h3 class="buttontop"><small>Override</small></h3>
							<h3 class="degre" ><i class="fa fa-refresh fa-1x blue"></i></h3>
							<h3 class="status"></small></h3>
                            </button>

							<button class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" data-href="edit_sensors.php" data-toggle="modal" data-target="#temperature_sensor">
							<h3 class="buttontop"><small>Sensors</small></h3>
							<h3 class="degre" ><i class="ionicons ion-thermometer red"></i></h3>
							<h3 class="status"></small></h3>
                            </button>

							<button class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" data-href="edit_gpio.php" data-toggle="modal" data-target="#zone_setup">
							<h3 class="buttontop"><small>Zone</small></h3>
							<h3 class="degre" ><i class="glyphicon glyphicon-th-large orange"></i> </h3>
							<h3 class="status"></small></h3>
                            </button>							

							<button class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" data-href="edit_sensors.php" data-toggle="modal" data-target="#sensor_gateway">
							<h3 class="buttontop"><small>Gateway</small></h3>
							<h3 class="degre" ><i class="fa fa-heartbeat red"></i></h3>
							<h3 class="status"></small></h3>
                            </button>

							<button class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" data-href="edit_gpio.php" data-toggle="modal" data-target="#cron_jobs">
							<h3 class="buttontop"><small>Cron Jobs</small></h3>
							<h3 class="degre" ><i class="ionicons ion-ios-timer-outline blue"></i></h3>
							<h3 class="status"></small></h3>
                            </button>
<?php 
	$query = "select * from messages_in where node_id = 0 order by datetime desc limit 1";
	$result = $conn->query($query);
	$result = mysqli_fetch_array($result);
	$system_cc = $result['payload'];
	if ($system_cc < 40){$system_cc="#0bb71b"; $fan=" ";}elseif ($system_cc < 50){$system_cc="#F0AD4E"; $fan="fa-pulse";}elseif ($system_cc > 50){$system_cc="#ff0000"; $fan="fa-pulse";}
?>							
							<button type="button" class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" data-toggle="modal" data-target="#system_c">
							<h3 class="buttontop"><small>System &deg;</small></h3>
							<h3 class="degre" ><i class="fa fa-server fa-1x green"></i></h3>
							<h3 class="status">
                                <small class="statuscircle" style="color:<?php echo $system_cc;?>"><i class="fa fa-circle fa-fw"></i></small>
                                <small class="statusdegree"><?php echo number_format(DispTemp($conn,$result['payload']),0);?>&deg;</small>
                                <small class="statuszoon"><i class="fa fa-asterisk <?php echo $fan;?>"></i></small>
							</h3></button>
							
							<button type="button" class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" data-toggle="modal" data-target="#os_version">
							<h3 class="buttontop"><small>OS Version</small></h3>
							<h3 class="degre" ><i class="fa fa-linux"></i></h3>
							<h3 class="status"></small></h3>
                            </button>
							
							<button type="button" class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" data-toggle="modal" data-target="#pihome_update">
							<h3 class="buttontop"><small>PiHome Update</small></h3>
							<h3 class="degre" ><i class="fa fa-download fa-1x blueinfo"></i></h3>
							<h3 class="status"></small></h3>
                            </button>
							
							<button type="button" class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" data-toggle="modal" data-target="#system_uptime">
							<h3 class="buttontop"><small>Uptime</small></h3>
							<h3 class="degre" ><i class="ionicons ion-clock red"></i></h3>
							<h3 class="status"></small></h3>
                            </button>

							<button type="button" class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" data-toggle="modal" data-target="#backup_image">
							<h3 class="buttontop"><small>Backup</small></h3>
							<h3 class="degre" ><i class="fa fa-clone fa-1x blue"></i> </h3>
							<h3 class="status"></small></h3>
                            </button>
							
							<button type="button" class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" data-toggle="modal" data-target="#wifi_setup">
							<h3 class="buttontop"><small>WiFi</small></h3>
							<h3 class="degre" ><i class="fa fa-signal green"></i></h3>
							<h3 class="status"></small></h3>
                            </button>							

							<button type="button" class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" data-toggle="modal" data-target="#eth_setup">
							<h3 class="buttontop"><small>Ethernet</small></h3>
							<h3 class="degre" ><i class="ionicons ion-network orange"></i></h3>
							<h3 class="status"></small></h3>
                            </button>
							
							<button type="button" class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" data-href="edit_users.php" data-toggle="modal" data-target="#user_setup">
							<h3 class="buttontop"><small>User Accounts</small></h3>
							<h3 class="degre" ><i class="ionicons ion-person blue"></i></h3>
							<h3 class="status"></small></h3>
                            </button>

							<button type="button" class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" data-href="edit_users.php" data-toggle="modal" data-target="#big_thanks">
							<h3 class="buttontop"><small>Big Thanks</small></h3>
							<h3 class="degre" ><i class="ionicons ion-help-buoy blueinfo"></i></h3>
							<h3 class="status"></small></h3>
                            </button>
							
							<button type="button" class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" onClick="reboot()">
							<h3 class="buttontop"><small>Reboot Pi</small></h3>
                            <h3 class="degre" ><i class="ion-ios-refresh-outline larger orange"></i></h3>
							<h3 class="status"></small></h3>
                            </button>

							<button type="button" class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" onClick="shutdown()">
							<h3 class="buttontop"><small>Shutdown Pi</small></h3>
							<h3 class="degre" ><i class="fa fa-power-off fa-1x red"></i></h3>
							<h3 class="status"></small></h3>
                            </button>	
				
<?php include("model.php");  ?>
                        </div>
                        <!-- /.panel-body -->
						<div class="panel-footer">
<?php 
ShowWeather($conn);
?>
                        </div>
                    </div>