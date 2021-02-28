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
        if(! $row) {
                http_response_code(400);
                echo json_encode(array("success" => False, "state" => "No Zone with that name found."));
        } else {
	        $zone_sensor_id=$row['sensors_id'];
        	$zone_sensor_child_id=$row['sensor_child_id'];

	        //query to get temperature from messages_in_view_24h table view
        	$query = "SELECT * FROM messages_in_view_24h WHERE node_id = '{$zone_sensor_id}' AND child_id = {$zone_sensor_child_id} ORDER BY datetime desc LIMIT 1;";
	        $result = $conn->query($query);
	        $sensor = mysqli_fetch_array($result);
        	if(! $sensor) {
                	http_response_code(400);
                	echo json_encode(array("success" => False, "state" => "Sensor has not reported in the last 24 hours."));
        	} else {
		        $zone_temp = $sensor['payload'];
				$zone_time = $sensor['datetime'];

				//query to get battery info from nodes_battery table
				$query = "SELECT * FROM nodes_battery WHERE node_id = '{$zone_sensor_id}' ORDER BY id desc LIMIT 1;";
				$result = $conn->query($query);
				$node = mysqli_fetch_array($result);
				if(! $node) {
                	http_response_code(200);
        			echo json_encode(array("success" => True, "state" => $zone_temp, "datetime" => $zone_time));
				} else {
					$zone_bat_voltage = $node['bat_voltage'];
					$zone_bat_level = $node['bat_level'];
        			http_response_code(200);
        			echo json_encode(array("success" => True, "state" => $zone_temp, "datetime" => $zone_time, "bat_voltage" => $zone_bat_voltage, "bat_level" => $zone_bat_level));
				}
			}
	}
} else {
        http_response_code(400);
        echo json_encode(array("success" => False, "state" => "Data is incomplete."));
}
?>

