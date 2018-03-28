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
	mysql_query($query, $connection);
	//Delete All Message Out records
	$query = "DELETE FROM messages_out WHERE zone_id = '".$wid."'";
	mysql_query($query, $connection);
	//Delete Override records
	$query = "DELETE FROM override WHERE zone_id = '".$wid."'";
	mysql_query($query, $connection);
	//Delete Daily Time records
	$query = "DELETE FROM schedule_daily_time_zone WHERE zone_id = '".$wid."'";
	mysql_query($query, $connection);
	//Delete Night Climat records
	$query = "DELETE FROM schedule_night_climat_zone WHERE zone_id = '".$wid."'";
	mysql_query($query, $connection);
	//Delete All Zone Logs records
	$query = "DELETE FROM zone_logs WHERE zone_id = '".$wid."'";
	mysql_query($query, $connection);
	//Delete Zone record
	$query = "DELETE FROM zone WHERE id = '".$wid."'";
	mysql_query($query, $connection);
}	

if($what=="holidays"){
	if($opp=="active"){
		$query = "SELECT * FROM holidays WHERE id ='".$wid."'";
		$results = mysql_query($query, $connection);	
		$row = mysql_fetch_assoc($results);
		$da= $row['active'];
		if($da=="1"){ $set="0"; }else{ $set="1"; }
		$query  = "UPDATE holidays SET active='".$set."' WHERE id = '".$wid."'";
		mysql_query($query, $connection);
	}elseif ($opp=="delete") {
		$query = "DELETE FROM holidays WHERE id ='".$wid."'";
		mysql_query($query, $connection);
	}
}

if(($what=="user") && ($opp=="delete")){
		$query = "DELETE FROM user WHERE id = '".$wid."'"; 
		mysql_query($query, $connection);
}

if($what=="schedule"){
	if($opp=="active"){
		$query = "SELECT * FROM schedule_daily_time WHERE id ='".$wid."'";
		$results = mysql_query($query, $connection);	
		$row = mysql_fetch_assoc($results);
		$da= $row['status'];
		if($da=="1"){ $set="0"; }else{ $set="1"; }
		$query  = "UPDATE schedule_daily_time SET status='".$set."' WHERE id = '".$wid."'";
		mysql_query($query, $connection);
	}elseif ($opp=="delete") {
		$query = "DELETE FROM schedule_daily_time_zone WHERE schedule_daily_time_id ='".$wid."'";
		mysql_query($query, $connection);
		$query = "DELETE FROM schedule_daily_time WHERE id ='".$wid."'";
		mysql_query($query, $connection);
	}
}

//update each schedule from model from homelist
if($what=="schedule_zone"){
	if($opp=="active"){
		$query = "SELECT * FROM schedule_daily_time_zone WHERE id ='".$wid."'";
		$results = mysql_query($query, $connection);	
		$row = mysql_fetch_assoc($results);
		$da= $row['status'];
		if($da=="1"){ $set="0"; }else{ $set="1"; }
		$query  = "UPDATE schedule_daily_time_zone SET status='".$set."' WHERE id = '".$wid."'";
		mysql_query($query, $connection);
	}
}

if($what=="override"){
	if($opp=="active"){
		//$time = date('H:i:s', time());
		$time = date("Y-m-d H:i:s");
		$query = "SELECT * FROM override WHERE zone_id ='".$wid."'";
		$results = mysql_query($query, $connection);	
		$row = mysql_fetch_assoc($results);
		$da= $row['status'];
		if($da=="1"){ $set="0"; }else{ $set="1"; }
		$query = "UPDATE override SET status = '{$set}', time = '{$time}' WHERE zone_id = '{$wid}' LIMIT 1";
		mysql_query($query, $connection);
	}
}

if($what=="boost"){
	if($opp=="active"){
		$query = "SELECT * FROM boost WHERE status = '1' limit 1;";
		$result = mysql_query($query, $connection);
		$boost_row = mysql_fetch_assoc($result);
		$boost_status = $boost_row['status'];
		$boost_time = $boost_row['time'];
		if ($boost_status == 1){
			$time = $boost_time;
		}else {
			$time = date("Y-m-d H:i:s");
		}
		
		$query = "SELECT * FROM boost WHERE zone_id ='".$wid."'";
		$results = mysql_query($query, $connection);	
		$row = mysql_fetch_assoc($results);
		$boost_status= $row['status'];
		if($boost_status=="1"){ $set="0"; }else{ $set="1";}
		$query = "UPDATE boost SET status = '{$set}', time = '{$time}' WHERE zone_id = '{$wid}' LIMIT 1";
		mysql_query($query, $connection);
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
		$results = mysql_query($query, $connection);	
		$row = mysql_fetch_assoc($results);
		$da= $row['status'];
		if($da=="1"){ $set="0"; }else{ $set="1"; }
		$query = "UPDATE away SET status = '{$set}', start_datetime = '{$time}' LIMIT 1";
		mysql_query($query, $connection);
		
		$query = "UPDATE messages_out SET payload = '{$set}', datetime = '{$time}', sent = '0' WHERE zone_id = '0' AND node_id = {$row['away_button_id']} AND child_id = {$row['away_button_child_id']} LIMIT 1";
		mysql_query($query, $connection);
	}
}

//update frost temperature
if($what=="frost"){
	if($opp=="update"){
			$query = "UPDATE frost_protection SET temperature = '{$frost_temp}' LIMIT 1";
			mysql_query($query, $connection);	
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
<?php if(isset($connection)) { mysql_close($connection); } ?>