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
	
        //Delete Zone Sensors record
        $query = "UPDATE zone_sensors SET zone_sensors.purge='1' WHERE zone_id = '".$wid."'";
        $conn->query($query);
        //Delete Zone Controller record
        $query = "UPDATE zone_controllers SET zone_controllers.purge='1' WHERE zone_id = '".$wid."'";
        $conn->query($query);

	//Delete Zone record
	$query = "UPDATE zone SET zone.purge='1', zone.sync='0' WHERE id = '".$wid."'";
	$conn->query($query);
}

//Holidays 
//following variable set to 0 on start for array index.
$sch_time_index = '0';
if($what=="holidays"){
	if($opp=="active"){
		$query = "SELECT * FROM holidays WHERE id ='".$wid."'";
		$results = $conn->query($query);	
		$row = mysqli_fetch_assoc($results);
		$da= $row['status'];
		if($da=="1"){ $set="0"; }else{ $set="1"; }
		$query  = "UPDATE holidays SET status='".$set."' WHERE id = '".$wid."'";
		$conn->query($query);
	}elseif ($opp=="delete") {
		$query = "SELECT * FROM schedule_daily_time_zone WHERE holidays_id = '".$wid."'";
		$results = $conn->query($query);
                $hcount = $results->num_rows;
                if ($hcount == 0) {
                        while ($row = mysqli_fetch_assoc($results)) {
                                $hid = $row['schedule_daily_time_id'];
                                $schedule_time[$sch_time_index] = $hid;
                                $sch_time_index = $sch_time_index+1;
                        }
                        $query = "UPDATE schedule_daily_time_zone SET schedule_daily_time_zone.purge = '1' WHERE holidays_id = '".$wid."';";
                        $conn->query($query);
                        for ($x = 0; $x <= $sch_time_index; $x++) {
                                $query = "UPDATE schedule_daily_time set schedule_daily_time.purge = '1' WHERE id = '".$schedule_time[$x]."';";
                                $conn->query($query);
                        }
                }
                $query = "DELETE FROM holidays WHERE id = '".$wid."'";
                $conn->query($query);
        }
}

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
		$query = "SELECT * FROM boost WHERE id ='".$wid."';";
		$results = $conn->query($query);
		$row = mysqli_fetch_assoc($results);
		$boost_status= $row['status'];
		$zone_id=$row['zone_id'];
		if($boost_status=="1"){ $set="0"; }else{ $set="1";}
		$query = "UPDATE boost SET status = '{$set}', sync = '0', time = '{$time}' WHERE id = '{$wid}' LIMIT 1";
		$conn->query($query);
		//this line update message out 
		$query = "UPDATE messages_out SET payload = '{$set}', datetime = '{$time}', sent = '0', sync = '0' WHERE zone_id = '{$zone_id}' AND node_id = {$row['boost_button_id']} AND child_id = {$row['boost_button_child_id']} LIMIT 1";
		$conn->query($query);
	}
	if($opp=="delete"){
		//get list of Boost console Id and Child ID
		$query = "select * from boost WHERE id = '".$wid."';"; 
		$results = $conn->query($query);
		$row = mysqli_fetch_assoc($results);
		$boost_button_id= $row['boost_button_id'];
		$boost_button_child_id=$row['boost_button_child_id'];
		//delete from message_out related to this boost. 
		$query = "DELETE FROM messages_out WHERE node_id = '".$boost_button_id."' AND child_id = '".$boost_button_child_id."' LIMIT 1;"; 
		$conn->query($query);
		//Now Mark for deletion from Boost 
		$query = "UPDATE boost SET `purge` = '1' WHERE id = '".$wid."';"; 
		$conn->query($query);
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
	if($opp=="add"){
		$datetime = date("Y-m-d H:i:s");
		$zone_id = $_GET['zone_id'];
		$boost_time = $_GET['boost_time'];
		$boost_temperature = $_GET['boost_temperature'];
		$boost_console_id = $_GET['boost_console_id'];
		$boost_button_child_id = $_GET['boost_button_child_id'];
		//If boost Console is selected then add to messages_out table.
		if ($boost_console_id != '0'){
			$query = "INSERT INTO `messages_out`(`sync`, `purge`, `node_id`, `child_id`, `sub_type`, `ack`, `type`, `payload`, `sent`, `datetime`, `zone_id`) VALUES ('0', '0', '{$boost_console_id}', '{$boost_button_child_id}', '1', '0', '2', '0', '0', '{$datetime}', '{$zone_id}')";
			$conn->query($query);
		}
		//Add record to Boost table
		$query = "INSERT INTO `boost`(`sync`, `purge`, `status`, `zone_id`, `time`, `temperature`, `minute`, `boost_button_id`, `boost_button_child_id`) VALUES ('0', '0', '0', '{$zone_id}', '{$datetime}', '{$boost_temperature}', '{$boost_time}', '{$boost_console_id}', '{$boost_button_child_id}')";
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
	if($opp=="update"){
		$datetime = date("Y-m-d H:i:s");
		$sel_query = "SELECT * FROM boost ORDER BY id asc;";
		$results = $conn->query($sel_query);
		while ($row = mysqli_fetch_assoc($results)) {
			$id = $row['id'];
			$input1 = 'minute'.$id;
			$input2 = 'temperature'.$id;
			$input3= 'boost_button_id'.$id;
			$input4 = 'boost_button_child_id'.$id;
			$input5 = 'zone_id'.$id;
			$minute = $_GET[$input1];
			$temperature = $_GET[$input2];
			$boost_button_id = $_GET[$input3];
			$boost_button_child_id = $_GET[$input4];
			$zone_id = $_GET[$input5];
			//Delete all boost console records from messages_out 
			$query = "DELETE FROM messages_out WHERE node_id = '{$boost_button_id}';";
			$conn->query($query);
			//Update Boost table
			$query = "UPDATE boost SET minute = '".$minute."', temperature = '".$temperature."', boost_button_id = '".$boost_button_id."', boost_button_child_id = '".$boost_button_child_id."' WHERE id='".$row['id']."' LIMIT 1;";
			$conn->query($sel_query);
			$update_error=0;
			if(!$conn->query($query)){
				$update_error=1;
			}
		}
		$query = "SELECT * FROM boost WHERE boost_button_id != 0 ORDER BY id asc;";
		$results = $conn->query($query);
		while ($row = mysqli_fetch_assoc($results)) {
			$zone_id = $row['zone_id'];
			$boost_button_id = $row['boost_button_id'];
			$boost_button_child_id = $row['boost_button_child_id'];
			$query = "INSERT INTO `messages_out`(`sync`, `purge`, `node_id`, `child_id`, `sub_type`, `ack`, `type`, `payload`, `sent`, `datetime`, `zone_id`) VALUES ('0', '0', '{$boost_button_id}', '{$boost_button_child_id}', '1', '0', '2', '0', '0', '{$datetime}', '{$zone_id}')";
			$conn->query($query);
			if(!$conn->query($query)){
				$update_error=1;
			}
		}
		if($update_error==0){
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

//HTTP Messages
if($what=="http_msg"){
        if($opp=="delete"){
                //get list of Boost console Id and Child ID
                $query = "DELETE FROM `http_messages` WHERE id = '".$wid."';";
                $conn->query($query);
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
        if($opp=="add"){
                $http_id = $_GET['http_id'];
                $add_msg_type = $_GET['add_msg_type'];
                $http_command = $_GET['http_command'];
                $http_parameter = $_GET['http_parameter'];
                if ($wid == 1) {
                        $query = "SELECT controler_id FROM zone_view WHERE name = '".$add_on_zone_name."' LIMIT 1";
                        $results = $conn->query($query);
                        $row = mysqli_fetch_assoc($results);
                        $node_id = $row['controler_id'];
                        $add_on_zone_name = $http_id;
                } else {
                        $node_id = $http_id;
                        $add_on_zone_name = "";
                }

                //Add record to http_messages table
                $query = "INSERT INTO `http_messages`(`sync`, `purge`, `zone_name`, `node_id`, `message_type`, `command`, `parameter`) VALUES ('0', '0', '{$add_on_zone_name}', '{$node_id}', '{$add_msg_type}', '{$http_command}', '{$http_parameter}')";
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

//Nodes
if($what=="node"){
	if($opp=="delete"){
                //Get id from nodes table
                $query = "SELECT * FROM nodes WHERE id = '".$wid."' LIMIT 1";
                $results = $conn->query($query);
                $row = mysqli_fetch_assoc($results);
                if($row['name'] == 'Boiler Controller') {
                        $query = "UPDATE boiler SET node_id = NULL WHERE node_id = '".$wid."' LIMIT 1";
                        $conn->query($query);
                } elseif($row['name'] == 'Zone Controller') {
                        $query = "UPDATE zone SET controler_id = NULL WHERE  controler_id = '".$wid."' LIMIT 1";
                        $conn->query($query);
                }
		//Now delete from Nodes
		$query = "DELETE FROM nodes WHERE id = '".$wid."';"; 
		$conn->query($query);
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
	if($opp=="add"){
		$datetime = date("Y-m-d H:i:s");
		$node_type = $_GET['node_type'];
		$node_id = $_GET['add_node_id'];
		$node_child_id = $_GET['nodes_max_child_id'];
		$node_name = $_GET['node_name'];
                $notice_interval = $_GET['notice_interval'];
		//Add record to Nodes table
		$query = "INSERT INTO `nodes`(`sync`, `purge`, `type`, `node_id`, `max_child_id`, `name`, `last_seen`, `notice_interval`, `min_value`, `status`, `ms_version`, `sketch_version`, `repeater`) VALUES ('0', '0', '{$node_type}', '{$node_id}', '{$node_child_id}', '{$node_name}', '{$datetime}', '{$notice_interval}', '0', 'Active', '0', '0', '0')";
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

//Zone Types
if($what=="zone_type"){
	if($opp=="delete"){
		//Delete from Zone Type
		$query = "UPDATE zone_type SET zone_type.purge='1' WHERE id = '".$wid."'";
		$conn->query($query);
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
	if($opp=="add"){
		$zone_type = $_GET['zone_type'];
		$zone_category = $_GET['zone_category'];
		//Add record to zone_type table
                $query = "INSERT INTO `zone_type`(`sync`, `purge`, `type`, `category`) VALUES ('0', '0', '{$zone_type}', '{$zone_category}')";
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

//add_on
if($what=="add_on"){
        if($opp=="update"){
                $sch_active = $_GET['sch_active'];
                $time = date("Y-m-d H:i:s");
                $query = "SELECT zone_state FROM zone WHERE id = '{$wid}' LIMIT 1";
                $results = $conn->query($query);
                $zrow = mysqli_fetch_assoc($results);
                $da= $zrow['zone_state'];

                $query = "SELECT * FROM messages_out WHERE zone_id = '{$wid}'";
                $results = $conn->query($query);
                while ($row = mysqli_fetch_assoc($results)) {
                        $id= $row['id'];
                        $node_id = $row['node_id'];
                        $query = "SELECT type FROM nodes WHERE node_id = '{$node_id}' LIMIT 1";
                        $nresults = $conn->query($query);
                        $nrow = mysqli_fetch_assoc($nresults);
                        if ($nrow['type'] == 'Tasmota') {
                                if($da == "1"){ $message_type="0"; }else{ $message_type="1"; }
                                $query = "SELECT  command, parameter FROM http_messages WHERE node_id = '{$node_id}' AND message_type = '{$message_type}' LIMIT 1;";
                                $hresults = $conn->query($query);
                                $hrow = mysqli_fetch_assoc($hresults);
                                $set =  $message_type;
                                $payload = $hrow['command']." ".$hrow['parameter'];
                        } else {
                                if($da=="1"){
                                        $set="0";
                                        $payload = "0";
                                }else{
                                        $set="1";
                                        $payload = "1";
                                }
                        }
                        $query = "UPDATE messages_out SET payload = '{$payload}', datetime = '{$time}', sent = '0' WHERE id = '{$id}';";
                        if($conn->query($query)){
                                $update_error=0;
                        }else{
                                $update_error=1;
                        }

                        $query = "UPDATE zone_controllers SET state = '{$set}' WHERE zone_id = '{$wid}';";
                        if($conn->query($query)){
                                $update_error=0;
                        }else{
                                $update_error=1;
                        }
                }

                $query = "UPDATE zone SET zone_state = '{$set}' WHERE id = '{$wid}' LIMIT 1;";
                if($conn->query($query)){
                        $update_error=0;
                }else{
                        $update_error=1;
                }

                if($sch_active == "1") {
                        $query = "UPDATE override SET status = '{$da}' WHERE zone_id = '{$wid}' LIMIT 1;";
                        if($conn->query($query)){
                                $update_error=0;
                        }else{
                                $update_error=1;
                        }
                }

                if($update_error==0){
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

//update frost temperature
if($what=="frost"){
	if($opp=="update"){
		$datetime = date("Y-m-d H:i:s");
		$temp=TempToDB($conn,$_GET['frost_temp']);
                $query = "SELECT * FROM frost_protection LIMIT 1;";
                $result = $conn->query($query);
                if (mysqli_num_rows($result)==0){
                        //No record in frost_protction table, so add
                        $query = "INSERT INTO frost_protection VALUES(1, 0, 0, '{$datetime}', '" . number_format($temp,1) . "');";
                } else {
                        $query = "UPDATE frost_protection SET datetime = '{$datetime}', temperature = '" . number_format($temp,1) . "', sync = 0 LIMIT 1";
                }
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

//update language
if($what=="lang"){
	if($opp=="update"){
                $lang_val = $_GET['lang_val'];
                $query = "UPDATE `system` SET `language`='" . $lang_val . "';";
                if (file_exists('/var/www/languages/'.$lang_val.'.php')) {
                        if($conn->query($query)){
                                setcookie("PiHomeLanguage", $lang_val, time()+(3600*24*90));
                                header('Content-type: application/json');
                                echo json_encode(array('Success'=>'Success','Query'=>$query));
                                return;
                        }else{
                                header('Content-type: application/json');
                                echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
                                return;
                        }
                } else {
                        header('Content-type: application/json');
                        echo json_encode(array('Success'=>'Success','Query'=>$query));
                        return;
                }
	}
}

//update user email address
if($what=="user_email"){
        if($opp=="update"){
                $email_add = $_GET['email_add'];
                $user_id = $_SESSION['user_id'];
                $query = "UPDATE `user` SET `email`= '{$email_add}' WHERE id = '{$user_id}';";
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

//Boiler Settings
if($what=="boiler_settings"){
	$datetime = date("Y-m-d H:i:s");
	$status = $_GET['status'];
	$name = $_GET['name'];
	$node_id = $_GET['node_id'];
	$node_child_id = $_GET['node_child_id'];
	$hysteresis_time = $_GET['hysteresis_time'];
	$max_operation_time = $_GET['max_operation_time'];
	$overrun = $_GET['overrun'];
	if ($status=='true'){$status = '1';} else {$status = '0';}
	
	//Get id from nodes table
	$query = "SELECT * FROM nodes WHERE node_id ='".$node_id."' LIMIT 1";
	$results = $conn->query($query);
	$row = mysqli_fetch_assoc($results);

	//Check messages_out for Boiler Node ID
	$query = "SELECT * FROM messages_out WHERE node_id='".$node_id."' LIMIT 1;";
	$result = $conn->query($query);
	if (mysqli_num_rows($result)==0){
		//Update messages_out for Boiler Node. 
		$query = "INSERT INTO `messages_out`(`sync`, `purge`, `node_id`, `child_id`, `sub_type`, `ack`, `type`, `payload`, `sent`, `datetime`, `zone_id`) VALUES (0, 0, '".$node_id."', '".$node_child_id."', 1, 1, 2, 0, 0, '".$datetime."', 0);";
		$conn->query($query);
	}

	$query = "SELECT * FROM boiler LIMIT 1;";
        $result = $conn->query($query);
        if (mysqli_num_rows($result)==0){
		//No record in boiler table, so add
		$query = "INSERT INTO `boiler` VALUES (1,0,0,1,1,'".$name."','".$row['id']."','".$node_child_id."','".$hysteresis_time."','".$max_operation_time."','".$overrun."',now());";
	} else {
		//Update Boiler Setting 
		$query = "UPDATE boiler SET status = '".$status."', name = '".$name."', node_id = '".$row['id']."', node_child_id = '".$node_child_id."', hysteresis_time = '".$hysteresis_time."', max_operation_time = '".$max_operation_time."', overrun = '".$overrun."' where ID = 1;";
	}
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
	//update Systems settings to sync 
	$query = "UPDATE system SET `sync`='0';";
	$result = $conn->query($query);
	//Update PiConnect API Status and key
	$api_key = $_GET['api_key'];
	$status = $_GET['status'];
	if ($status=='true'){$status = '1';}else {$status = '0';}
        $query = "SELECT * FROM piconnect LIMIT 1;";
        $result = $conn->query($query);
        if (mysqli_num_rows($result)==0){
                //No record in frost_protction table, so add
                $query = "INSERT INTO piconnect VALUES(1, '".$status."', 'http', 'www.pihome.eu', '/piconnect/mypihome.php', '".$api_key."');";
        } else {
                $query = "UPDATE piconnect SET status = '".$status."', api_key = '".$api_key."';";
        }
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

//Database Backup
if($what=="db_backup"){
	shell_exec("nohup php start_backup.php >/dev/null 2>&1"); 
	$info_message = "Data Base Backup Request Started, This process may take some time complete..." ;
}

//Setup Backup e-mail
if($what=="backup_email_update"){
	$backup_email = $_GET['backup_email'];
	$query = "UPDATE system SET backup_email = '".$backup_email."';";
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

//Reboot System
if($what=="reboot"){
	
	//Stop Cron Service 
	//systemctl stop cron.service
	//exec("systemctl stop cron.service");
	
	//Kill Gateway Process
	$query = "SELECT * FROM gateway where status = 1 order by id asc LIMIT 1;";
	$result = $conn->query($query);
	$row = mysqli_fetch_array($result);
	$gw_pid = $row['pid'];
	exec("kill -9 $gw_pid");
	
	//Stop MySQL/MariaDB Service
	//systemctl stop mysql.service
	//exec("systemctl stop mysql.service"); 
	
	exec("python /var/www/reboot.py"); 
	$info_message = "Server is rebooting <small> Please Do not Refresh... </small>";
}

//Shutdown System
if($what=="shutdown"){
	
	/*
	//Stop Cron Service 
	//systemctl stop cron.service
	exec("systemctl stop cron.service");
	
	//Kill Gateway Process
	$query = "SELECT * FROM gateway where status = 1 order by id asc LIMIT 1;";
	$result = $conn->query($query);
	$row = mysqli_fetch_array($result);
	$gw_pid = $row['pid'];
	exec("kill -9 $gw_pid");
	
	//Stop MySQL/MariaDB Service
	//systemctl stop mysql.service
	exec("systemctl stop mysql.service"); 
	*/
	//Shutdown System
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

//Setup Smart Home Gateway
if($what=="setup_gateway"){
	$status = $_GET['status'];
	$gw_type = $_GET['gw_type'];
	$gw_location = $_GET['gw_location'];
	$gw_port = $_GET['gw_port'];
	$gw_timout = $_GET['gw_timout'];
	if ($status=='true'){$status = '1';}else {$status = '0';}
        $query = "SELECT * FROM gateway LIMIT 1;";
        $result = $conn->query($query);
        if (mysqli_num_rows($result)==0){
                //No record in boiler gateway, so add
                $query = "INSERT INTO `gateway` VALUES (1,1,0,0,'".$gw_type."','".$gw_location."','".$gw_port."','".$gw_timout."',0, 0, 0, 0, 0);";
        } else {
		$query = "UPDATE gateway SET status = '".$status."', `sync` = '0', type = '".$gw_type."', location = '".$gw_location."', port = '".$gw_port."', timout = '".$gw_timout."' where ID = 1;";
	}
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


//network Settings
if($what=="setup_network"){
        $n_primary = $_GET['n_primary'];
        $n_ap_mode = $_GET['n_ap_mode'];
        $n_int_num = $_GET['n_int_num'];
        $n_int_type = $_GET['n_int_type'];
        $n_mac = $_GET['n_mac'];
        $n_hostname = $_GET['n_hostname'];
        $n_ip = $_GET['n_ip'];
        $n_gateway = $_GET['n_gateway'];
        $n_net_mask = $_GET['n_net_mask'];
        $n_dns1 = $_GET['n_dns1'];
        $n_dns2 = $_GET['n_dns2'];

        //Check interface already exists in table
        $query = "SELECT * FROM `network_settings` WHERE `interface_num` = '".$n_int_num."' LIMIT 1;";
        $result = $conn->query($query);
        if (mysqli_num_rows($result)==0){
                //No record, so add
                $query = "INSERT INTO `network_settings`(`sync`, `purge`, `primary_interface`, `ap_mode`, `interface_num`, `interface_type`, `mac_address`, `hostname`, `ip_address`, `gateway_address`, `net_mask`, `dns1_address`, `dns2_address`) VALUES (0,0,'".$n_primary."','".$n_ap_mode."','".$n_int_num."','".$n_int_type."','".$n_mac."','".$n_hostname."','".$n_ip."','".$n_gateway."','".$n_net_mask."','".$n_dns1."','".$n_dns2."');";
        } else {
                //Update
                $query = "UPDATE `network_settings` SET primary_interface = '".$n_primary."', ap_mode = '".$n_ap_mode."', interface_type = '".$n_int_type."', mac_address = '".$n_mac."', hostname = '".$n_hostname."', ip_address = '".$n_ip."', gateway_address = '".$n_gateway."', net_mask = '".$n_net_mask."', dns1_address = '".$n_dns1."', dns2_address = '".$n_dns2."' where interface_num = '".$n_int_num."';";
        }
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

//Setup E-mail Setting
if($what=="setup_email"){
	$status = $_GET['status'];
	$e_smtp = $_GET['e_smtp'];
	$e_username = $_GET['e_username'];
	$e_password = $_GET['e_password'];
	$e_from_address = $_GET['e_from_address'];
	$e_to_address = $_GET['e_to_address'];
	if ($status=='true'){$status = '1';} else {$status = '0';}
	
	//search for exiting record
	$query = "SELECT * FROM email LIMIT 1;";
	$result = $conn->query($query);
	if (mysqli_num_rows($result)==0){
		//Inset New Record
		$query = "INSERT INTO email (`sync`, `purge`, smtp, username, password, `from`, `to`, status) VALUES (0, 0, '".$e_smtp."', '".$e_username."', '".$e_password."', '".$e_from_address."', '".$e_to_address."', '".$status."');";
	} else {
		//Update Exiting Record
		$row = mysqli_fetch_assoc($result);
		$e_id= $row['id'];
		$query = "Update email SET smtp = '".$e_smtp."', username = '".$e_username."', password = '".$e_password."', `from` = '".$e_from_address."', `to` = '".$e_to_address."', status = '".$status."' where ID = '".$e_id."';";
	}
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

//Setup Graph Setting
if($what=="setup_graph"){
        $sel_query = "select zone.* from zone, zone_type where (`zone_type`.id = `type_id`) AND `zone_type`.`type` = 'Heating' order by index_id asc;";
        $results = $conn->query($sel_query);
        while ($row = mysqli_fetch_assoc($results)) {
                $checkbox = 'checkbox'.$row['id'];
                $graph_it =  $_GET[$checkbox];
                if ($graph_it=='true'){$graph_it = '1';} else {$graph_it = '0';}
                $query = "UPDATE zone SET graph_it = '".$graph_it."' WHERE ID='".$row['id']."' LIMIT 1;";
                $update_error=0;
                if(!$conn->query($query)){
                        $update_error=1;
                }
        }
        if($update_error==0){
                header('Content-type: application/json');
                echo json_encode(array('Success'=>'Success','Query'=>$query));
                return;
        }else{
                header('Content-type: application/json');
                echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
                return;
        }
}

//update Node Alerts Notice Interval
if($what=="node_alerts"){
        $update_error=0;
        $sel_query = "SELECT * FROM nodes where status = 'Active' ORDER BY node_id asc";
        $results = $conn->query($sel_query);
        while ($row = mysqli_fetch_assoc($results) and $update_error == 0) {
                $node_id = $row['node_id'];
                if(isset($_GET["interval".$node_id])) {
                        $notice_interval =  $_GET["interval".$node_id];
                        $query = "UPDATE nodes SET notice_interval = '".$notice_interval."' WHERE node_id='".$row['node_id']."' LIMIT 1;";
                        if(!$conn->query($query)){
                                $update_error=1;
                        }
                }
                if(isset($_GET["min_value".$node_id])) {
                        $min_value =  $_GET["min_value".$node_id];
                        if($min_value != 'N/A'){
                                $query = "UPDATE nodes SET min_value = '".$min_value."' WHERE node_id='".$row['node_id']."' LIMIT 1;";
                                if(!$conn->query($query)){
                                        $update_error=1;
                                }
                        }
                }
        }
        if($update_error==0){
                header('Content-type: application/json');
                echo json_encode(array('Success'=>'Success','Query'=>$query));
                return;
        }else{
                header('Content-type: application/json');
                echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
                return;
        }
}

if($what=="mqtt"){
	if($opp=="delete"){
        $query = "DELETE FROM `mqtt` WHERE `id`=" . $_GET['wid'] . ";";
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
	if($opp=="add"){
        $query = "INSERT INTO `mqtt` 
            (`id`, `name`, `ip`, `port`, `username`, `password`, `enabled`, `type`) VALUES 
            (NULL,
             '" . $_GET['inp_Name'] . "',
             '" . $_GET['inp_IP'] . "',
             " . $_GET['inp_Port'] . ",
             '" . $_GET['inp_Username'] . "',
             '" . $_GET['inp_Password'] . "',
             " . $_GET['sel_Enabled'] . ",
             " . $_GET['sel_Type'] . ");";
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
	if($opp=="edit"){
        $query = "UPDATE `mqtt` SET 
            `name`='" . $_GET['inp_Name'] . "',
            `ip`='" . $_GET['inp_IP'] . "',
            `port`=" . $_GET['inp_Port'] . ",
            `username`='" . $_GET['inp_Username'] . "',
            `password`='" . $_GET['inp_Password'] . "',
            `enabled`=" . $_GET['sel_Enabled'] . ",
            `type`=" . $_GET['sel_Type'] . "
            WHERE `id`=" . $_GET['inp_id'] . ";";
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

//update Time zone
if($what=="time_zone"){
	if($opp=="update"){
		$time_zone_val = $_GET['time_zone_val'];
		
		$query = "UPDATE `system` SET `timezone`='" . $time_zone_val . "';";
		exec("sudo timedatectl set-timezone $time_zone_val");
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



?>
<?php if(isset($conn)) { $conn->close();} ?>
