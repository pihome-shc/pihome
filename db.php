<?php require_once("st_inc/session.php"); ?>
<?php confirm_logged_in(); ?>
<?php require_once("st_inc/connection.php"); ?>
<?php require_once("st_inc/functions.php"); ?>
<?php 
$what = $_GET['w'];   # what to do, override, schedule, away etc..
$opp =  $_GET['o'];   # insert, update, delete, ( active only device )
$wid = $_GET['wid'];  # which id
$frost_temp = $_GET['frost_temp']; #update frost temperature

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
		$time = date("Y-m-d H:i:s");
		$query = "SELECT * FROM boost WHERE zone_id ='".$wid."'";
		$results = mysql_query($query, $connection);	
		$row = mysql_fetch_assoc($results);
		$ba= $row['status'];
		if($ba=="1"){ $set="0"; }else{ $set="1"; }
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

//Restart MySensors Gateway this isnt working yet 
if($what=="resetgw"){
	//shell_exec('kill -9 '.$wid.' '); 
	//exec('sh /var/www/cron/restart_gw.sh 'sudo python /var/www/cron/wifigw.py')
	//shell_exec('sh /var/www/cron/restart_gw.sh '.$wid.'');
	//exec('kill -9 '.$wid.' ');

}

?>
<?php if(isset($connection)) { mysql_close($connection); } ?>