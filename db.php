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

//Set Purge to 1 to mark records for deletion for Zone and all related records
if(($what=="zone") && ($opp=="delete")){

	//Delete Boost Records
	$query = "UPDATE boost SET boost.purge='1' WHERE zone_id = '".$wid."'";
	$conn->query($query);
	
	//Delete All Message Out records
	$query = "DELETE FROM messages_out WHERE zone_id = '".$wid."'";
	$conn->query($query);
	
	//Delete Override records
	$query = "UPDATE override SET override.purge='1' WHERE zone_id = '".$wid."'";
	$conn->query($query);
	
	//Delete Daily Time records
	$query = "UPDATE schedule_daily_time_zone SET schedule_daily_time_zone.purge='1' WHERE zone_id = '".$wid."'";
	$conn->query($query);
	
	//Delete Night Climat records
	$query = "UPDATE schedule_night_climat_zone SET schedule_night_climat_zone.purge='1' WHERE zone_id = '".$wid."'";
	$conn->query($query);
	
	//Delete All Zone Logs records
	$query = "UPDATE zone_logs SET zone_logs.purge='1' WHERE zone_id = '".$wid."'";
	$conn->query($query);
	
	//Delete Zone record
	$query = "UPDATE zone SET zone.purge='1', zone.sync='0' WHERE id = '".$wid."'";
	$conn->query($query);
}
/* Holiday Module isn’t implanted yet!!!! leave code here for future.  
//Holidays 
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
*/

//Users accounts
if(($what=="user") && ($opp=="delete")){
		$query = "DELETE FROM user WHERE id = '".$wid."'"; 
		$conn->query($query);
}
//Heating Schedule 
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
//Override 
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
//Boost 
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
		//this line update message out 
		$query = "UPDATE messages_out SET payload = '{$set}', datetime = '{$time}', sent = '0', sync = '0' WHERE zone_id = '{$wid}' AND node_id = {$row['boost_button_id']} AND child_id = {$row['boost_button_child_id']} LIMIT 1";
		$conn->query($query);
	}
}
//Away 
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
		$temp=TempToDB($conn,$_GET['frost_temp']);
        $query = "UPDATE frost_protection SET temperature = '" . number_format($temp,1) . "', sync = 0 LIMIT 1";
        if($conn->query($query)){
            header('Content-type: application/json');
            echo json_encode(array('Success'=>'Success','Query'=>$query));
            return;
        }else{
            header('Content-type: application/json');
            echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
            return;
        }
	}
}

//update units
if($what=="units"){
	if($opp=="update"){
        $query = "UPDATE `system` SET `c_f`=" . $_GET['val'] . ";";
        if($conn->query($query)){
            header('Content-type: application/json');
            echo json_encode(array('Success'=>'Success','Query'=>$query));
            return;
        }else{
            header('Content-type: application/json');
            echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
            return;
        }
	}
}

//update openweather
if($what=="openweather"){
	if($opp=="update"){
        if($_GET['rad_CityZip']=='City') {
            $query = "UPDATE `system` SET `country`='" . $_GET['sel_Country'] . "'
                    ,`openweather_api`='" . $_GET['inp_APIKEY'] . "'
                    ,`city`='" . $_GET['inp_City'] . "',`zip`=NULL;";
            if($conn->query($query)){
                header('Content-type: application/json');
                echo json_encode(array('Success'=>'Success','Query'=>$query));
                return;
            }else{
                header('Content-type: application/json');
                echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
                return;
            }
        }else if($_GET['rad_CityZip']=='Zip') {
            $query = "UPDATE `system` SET `country`='" . $_GET['sel_Country'] . "'
                    ,`openweather_api`='" . $_GET['inp_APIKEY'] . "'
                    ,`zip`='" . $_GET['inp_Zip'] . "',`city`=NULL;";
            if($conn->query($query)){
                header('Content-type: application/json');
                echo json_encode(array('Success'=>'Success','Query'=>$query));
                return;
            }else{
                header('Content-type: application/json');
                echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
                return;
            }
        }else{
            header('Content-type: application/json');
            echo json_encode(array('Message'=>'Invalid value for rad_CityZip.\r\n$_GET=' . print_r($_GET)));
            return;
        }
	}
}

//Setup PiConnect
if($what=="setup_piconnect"){
	//Update PiConnect API Status and key
	$api_key = $_GET['api_key'];
	$status = $_GET['status'];
	if ($status=='true'){$status = '1';}else {$status = '0';}
	$query = "UPDATE piconnect SET status = '".$status."', api_key = '".$api_key."';";
	$conn->query($query);
	//Set away to 0 
	$query = "UPDATE away SET `sync`='0';";
	$result = $conn->query($query);
	//Update Boiler to sync
	$query = "UPDATE boiler SET `sync`='0';";
	$result = $conn->query($query);
	//Update boiler Records to sync 
	$query = "UPDATE boiler SET `sync`='0';";
	$result = $conn->query($query);
	//Update boiler logs to sync 
	$query = "UPDATE boiler_logs SET `sync`='0';";
	$result = $conn->query($query);
	//upate boost records to sync 
	$query = "UPDATE boost SET `sync` ='0';";
	$result = $conn->query($query);
	//update from protection to sync 
	$query = "UPDATE frost_protection SET `sync`='0';";
	$result = $conn->query($query);
	//update gateway to sync 
	$query = "UPDATE gateway SET `sync`='0';";
	$result = $conn->query($query);
	//update gateway logs NOT sync 
	$query = "UPDATE gateway_logs SET `sync`='1';";
	$result = $conn->query($query);
	//update messages in history to NOT sync 
	$query = "UPDATE messages_in SET `sync`='1';";
	$result = $conn->query($query);
	//update nodes to sync 
	$query = "UPDATE nodes SET `sync`='0';";
	$result = $conn->query($query);
	//update nodes battery to NOT sync 
	$query = "UPDATE nodes_battery SET `sync`='1';";
	$result = $conn->query($query);
	//update schedule daily time to sync 
	$query = "UPDATE schedule_daily_time SET `sync`='0';";
	$result = $conn->query($query);
	//update schedule dailt time for zone to sync
	$query = "UPDATE schedule_daily_time_zone SET `sync`='0';";
	$result = $conn->query($query);
	//update schedule night climate time to sync 
	$query = "UPDATE schedule_night_climate_time SET `sync`='0';";
	$result = $conn->query($query);
	//update schedule night climate zone to sync 
	$query = "UPDATE schedule_night_climat_zone SET `sync`='0';";
	$result = $conn->query($query);
	//update weather to sync 
	$query = "UPDATE weather SET `sync`='0';";
	$result = $conn->query($query);
	//update zone to sync 
	$query = "UPDATE zone SET `sync`='0';";
	$result = $conn->query($query);
	//update zone logs to NOT to sync 
	$query = "UPDATE zone_logs SET `sync`='1';";
	$result = $conn->query($query);
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

//Search for network gateway
if($what=="find_gw"){
	//shell_exec("nohup python /var/www/cron/find_mygw/find_mygw.py");
	$query = "UPDATE gateway SET find_gw = '1' where status = 1;";
	$conn->query($query);
}


//Restart MySensors Gateway
if($what=="resetgw"){
	$query = "UPDATE gateway SET reboot = '1';";
	$conn->query($query);
}

?>
<?php if(isset($conn)) { $conn->close();} ?>