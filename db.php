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

$what = $_GET['w'];   # what to do, override, schedule, away etc..
$opp =  $_GET['o'];   # insert, update, delete, ( active only device )
$wid = $_GET['wid'];  # which id
$frost_temp = $_GET['frost_temp']; #update frost temperature

//Delete Zone and all related records
if(($what=="zone") && ($opp=="delete")){
	//Delete Boost Records
	$query = "DELETE FROM boost WHERE zone_id = '".$wid."'";
	$conn->query($query);
	//Delete All Message Out records
	$query = "DELETE FROM messages_out WHERE zone_id = '".$wid."'";
	$conn->query($query);
	//Delete Override records
	$query = "DELETE FROM override WHERE zone_id = '".$wid."'";
	$conn->query($query);
	//Delete Daily Time records
	$query = "DELETE FROM schedule_daily_time_zone WHERE zone_id = '".$wid."'";
	$conn->query($query);
	//Delete Night Climat records
	$query = "DELETE FROM schedule_night_climat_zone WHERE zone_id = '".$wid."'";
	$conn->query($query);
	//Delete All Zone Logs records
	$query = "DELETE FROM zone_logs WHERE zone_id = '".$wid."'";
	$conn->query($query);
	//Delete Zone record
	$query = "DELETE FROM zone WHERE id = '".$wid."'";
	$conn->query($query);
}	

if($what=="holidays"){
	if($opp=="active"){
		$query = "SELECT * FROM holidays WHERE id ='".$wid."'";
		$results = $conn->query($query);	
		$row = mysqli_fetch_assoc($results);
		$da= $row['active'];
		if($da=="1"){ $set="0"; }else{ $set="1"; }
		$query  = "UPDATE holidays SET active='".$set."' WHERE id = '".$wid."'";
		$conn->query($query);
	}elseif ($opp=="delete") {
		$query = "DELETE FROM holidays WHERE id ='".$wid."'";
		$conn->query($query);
	}
}

if(($what=="user") && ($opp=="delete")){
		$query = "DELETE FROM user WHERE id = '".$wid."'"; 
		$conn->query($query);
}

if($what=="schedule"){
	if($opp=="active"){
		$query = "SELECT * FROM schedule_daily_time WHERE id ='".$wid."'";
		$results = $conn->query($query);	
		$row = mysqli_fetch_assoc($results);
		$da= $row['status'];
		if($da=="1"){ $set="0"; }else{ $set="1"; }
		$query  = "UPDATE schedule_daily_time SET sync = '0', status='".$set."' WHERE id = '".$wid."'";
		$conn->query($query);
	}elseif ($opp=="delete") {
		$query  = "UPDATE schedule_daily_time_zone SET schedule_daily_time_zone.purge = '1', schedule_daily_time_zone.sync = '0' WHERE schedule_daily_time_id = '".$wid."';";
		$conn->query($query);
		$query  = "UPDATE schedule_daily_time SET schedule_daily_time.purge = '1', schedule_daily_time.sync = '0' WHERE id = '".$wid."';";
		$conn->query($query);
		
		//$query = "DELETE FROM schedule_daily_time_zone WHERE schedule_daily_time_id ='".$wid."'";
		//$conn->query($query);
		//$query = "DELETE FROM schedule_daily_time WHERE id ='".$wid."'";
		//$conn->query($query);
	}
}

//update each schedule from model from homelist
if($what=="schedule_zone"){
	if($opp=="active"){
		$query = "SELECT * FROM schedule_daily_time_zone WHERE id ='".$wid."'";
		$results = $conn->query($query);	
		$row = mysqli_fetch_assoc($results);
		$da= $row['status'];
		if($da=="1"){ $set="0"; }else{ $set="1"; }
		$query  = "UPDATE schedule_daily_time_zone SET sync = '0', status='".$set."' WHERE id = '".$wid."'";
		$conn->query($query);
	}
}

if($what=="override"){
	if($opp=="active"){
		//$time = date('H:i:s', time());
		$time = date("Y-m-d H:i:s");
		$query = "SELECT * FROM override WHERE zone_id ='".$wid."'";
		$results = $conn->query($query);	
		$row = mysqli_fetch_assoc($results);
		$da= $row['status'];
		if($da=="1"){ $set="0"; }else{ $set="1"; }
		$query = "UPDATE override SET status = '{$set}', sync = '0', time = '{$time}' WHERE zone_id = '{$wid}' LIMIT 1";
		$conn->query($query);
	}
}

if($what=="boost"){
	if($opp=="active"){
		$query = "SELECT * FROM boost WHERE status = '1' limit 1;";
		$result = $conn->query($query);
		$boost_row = mysqli_fetch_assoc($result);
		$boost_status = $boost_row['status'];
		$boost_time = $boost_row['time'];
		if ($boost_status == 1){
			$time = $boost_time;
		}else {
			$time = date("Y-m-d H:i:s");
		}
		
		$query = "SELECT * FROM boost WHERE zone_id ='".$wid."'";
		$results = $conn->query($query);	
		$row = mysqli_fetch_assoc($results);
		$boost_status= $row['status'];
		if($boost_status=="1"){ $set="0"; }else{ $set="1";}
		$query = "UPDATE boost SET status = '{$set}', sync = '0', time = '{$time}' WHERE zone_id = '{$wid}' LIMIT 1";
		$conn->query($query);
		/* Following is commented out to test wireless communication to zone relay module.
		$query = "UPDATE messages_out SET payload = '{$set}', datetime = '{$time}', sent = '0' WHERE zone_id = '{$wid}' AND node_id = {$row['boost_button_id']} AND child_id = {$row['boost_button_child_id']} LIMIT 1";
		mysql_query($query, $connection);
		*/
	}
}

if($what=="away"){
	if($opp=="active"){
		$time = date("Y-m-d H:i:s");
		$query = "SELECT * FROM away";
		$results = $conn->query($query);	
		$row = mysqli_fetch_assoc($results);
		$da= $row['status'];
		if($da=="1"){ $set="0"; }else{ $set="1"; }
		$query = "UPDATE away SET status = '{$set}', sync = '0', start_datetime = '{$time}' LIMIT 1";
		$conn->query($query);
		
		$query = "UPDATE messages_out SET payload = '{$set}', datetime = '{$time}', sent = '0' WHERE zone_id = '0' AND node_id = {$row['away_button_id']} AND child_id = {$row['away_button_child_id']} LIMIT 1";
		$conn->query($query);
	}
}

//update frost temperature
if($what=="frost"){
	if($opp=="update"){
			$query = "UPDATE frost_protection SET temperature = '{$frost_temp}' LIMIT 1";
			$conn->query($query);	
	}
}

//Database Backup
if($what=="db_backup"){
	 shell_exec("php start_backup.php"); 
	$info_message = "Data Base Backup Request Started, This process may take some time complete..." ;
}

//Reboot System
if($what=="reboot"){
	exec("python /var/www/reboot.py"); 
	$info_message = "Server is rebooting <small> Please Do not Refresh... </small>";
}

//Shutdown System
if($what=="shutdown"){
	exec("python /var/www/shutdown.py"); 
	$info_message = "Server is Shutting down <small> Please Do not Refresh... </small>";
}

//Restart MySensors Gateway
if($what=="resetgw"){
	//shell_exec('kill -9 '.$wid.' '); 
	//exec('sh /var/www/cron/restart_gw.sh 'sudo python /var/www/cron/wifigw.py')
	//shell_exec('sh /var/www/cron/restart_gw.sh '.$wid.'');
	//exec('kill -9 '.$wid.' ');
}

?>
<?php if(isset($conn)) { $conn->close();} ?>