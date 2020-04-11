<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
 
require_once(__DIR__.'../../st_inc/connection.php');
require_once(__DIR__.'../../st_inc/functions.php');

if(isset($_GET['zonename'])) {
	$zonename = $_GET['zonename'];
	$query = "SELECT * FROM zone_view where name = '{$zonename}' LIMIT 1;";
	$results = $conn->query($query);
	$row = mysqli_fetch_assoc($results);
        $zone_sensor_id=$row['sensors_id'];
        $zone_sensor_child_id=$row['sensor_child_id'];

        //query to get temperature from messages_in_view_24h table view
        $query = "SELECT * FROM messages_in_view_24h WHERE node_id = '{$zone_sensor_id}' AND child_id = {$zone_sensor_child_id} ORDER BY datetime desc LIMIT 1;";
        $result = $conn->query($query);
        $sensor = mysqli_fetch_array($result);
        $zone_c = $sensor['payload'];
	http_response_code(200); 
	echo json_encode(floatval($zone_c));
} else {
	http_response_code(400);    
	echo json_encode(array("message" => "Data is incomplete."));
}
?>
