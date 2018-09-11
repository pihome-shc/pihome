<?php 

//Error reporting on php ON
error_reporting(E_ALL);
//Error reporting on php OFF
//error_reporting(0);


//*********************************************************
//* Modify Following variable according to your settings  *
//*********************************************************
$hostname = 'localhost';
$dbname   = 'pihome';
$dbusername = 'root';
$dbpassword = 'passw0rd';
$connect_error = 'Sorry We are Experiencing MySQL Database Connection Problem...';

//Test Connection to MySQL Server with Given Username & Password 
$conn = new mysqli($hostname, $dbusername, $dbpassword, $dbname);
if ($conn->connect_error){
	die('Database Connecction Failed with Error: '.$conn->connect_error);
}



// Return Systems setting from settings table function
function settings($db, $svalue){
	$rValue = "";
	$query="SELECT * FROM system limit 1;";
	$result = $db->query($query);
	if ($row = mysqli_fetch_array($result)){$rValue = $row[$svalue];}
	return $rValue;	
}



echo settings($conn, 'name');

//print_r($conn);

	//Nodes Battery Table 
	if (($_GET['table'] == 'nodes_battery') AND ($_GET['data'] == 'push')){
		$api = $_GET['api'];
		$ip = $_GET['ip'];
		$table = $_GET['table'];
		$rt_id = $_GET['id'];
		$purge = $_GET['purge'];
		$node_id = $_GET['node_id'];
		$bat_voltage = $_GET['bat_voltage'];
		$bat_level = $_GET['bat_level'];
		$update = $_GET['update'];

		$api = mysqli_real_escape_string($conn, $_GET['api']);
		$ip = mysqli_real_escape_string($conn, $_GET['ip']);
		$rt_id = mysqli_real_escape_string($conn, $_GET['id']);
		$purge = mysqli_real_escape_string($conn, $_GET['purge']);
		$node_id = mysqli_real_escape_string($conn, $_GET['node_id']);
		$bat_voltage = mysqli_real_escape_string($conn, $_GET['bat_voltage']);
		$bat_level = mysqli_real_escape_string($conn, $_GET['bat_level']);
		$update = mysqli_real_escape_string($conn, $_GET['update']);
		//sleep for time in seconds
		sleep(3);
		//get userid from users tale to resolve api to user id. 
		$query = "SELECT * FROM user where api_id = '{$api}';";
		$result = $conn->query($query);
		$api_row = mysqli_fetch_array($result);
		$api_id = $api_row['id'];

		//search for any exiting record with same rt_id and api_id
		$querya = "SELECT * FROM nodes_battery where api_id = '{$api_id}' AND rt_id = '{$rt_id}';";
		$resulta = $conn->query($querya);
		if ((mysqli_num_rows($resulta) != 0) AND ($purge ==1)){
			$queryb = "DELETE FROM nodes_battery WHERE api_id = '{$api_id}' AND rt_id = '{$rt_id}';";
			$resultb = $conn->query($queryb);
			if ($resultb) {echo "Success";}else {echo "Failed";}
		} elseif ((mysqli_num_rows($resulta) != 0) AND ($purge ==0)){
				$queryc = "UPDATE nodes_battery SET node_id = '{$node_id}', bat_voltage = '{$bat_voltage}', bat_level = '{$bat_level}', update = '{$update}' WHERE api_id = '{$api_id}' AND rt_id = '{$rt_id}';";
				$resultc = $conn->query($queryc);
				if ($resultc) {echo "Success";}else {echo "Failed";}
		}else {
			//Inset temperature readings into messages_in table 
			$query = "INSERT INTO nodes_battery (rt_id, api_id, sync, node_id, bat_voltage, bat_level, update) VALUES ('{$rt_id}', '{$api_id}', '1', '{$node_id}', '{$bat_voltage}', '{$bat_level}', '{$update}');";
			$results = $conn->query($query);
			if ($results) {echo "Success";}else {echo "Failed";}
		}
	}
//Nodes Battery Table


?>