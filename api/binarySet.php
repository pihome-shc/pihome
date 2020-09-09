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
        $query = "SELECT zone_controllers.`controler_id`, zone_controllers.`controler_child_id`, zone.`name`, zone.`zone_state` FROM `zone_controllers`, `zone` WHERE (zone.id = zone_controllers.zone_id) AND name = '{$zonename}';";
        $results = $conn->query($query);
	if(mysqli_num_rows($results) == 0) {
	        http_response_code(400);
        	echo json_encode(array("success" => False, "state" => "No record found."));
	} else {
		while ($row = mysqli_fetch_assoc($results)) {
		        $controler_id=$row['controler_id'];
        	        $controler_child_id=$row['controler_child_id'];
			$query = "SELECT node_id, type FROM nodes WHERE id = '{$controler_id}' LIMIT 1;";
			$result = $conn->query($query);
			$node = mysqli_fetch_array($result);
                        $controler_node_id=$node['node_id'];
			$type = $node['type'];
	                if(isset($_GET['state'])) {
				switch ($_GET['state']) {
    					case 'true':
        					$status = 1;
						$http_status = 'Power ON';
        					break;
	    				case 'false':
        					$status = 0;
						$http_status = 'Power OFF';
					        break;
                                	case '1':
                                        	$status = 1;
						$http_status = 'Power ON';
        	                                break;
 					case '0':
					        $status = 0;
						$http_status = 'Power OFF';
				        	break;
					default:
		                                http_response_code(400);
        		                        echo json_encode(array("success" => False, "state" => "'state' parameter not correctly set."));
						$status = -1;
				}
				if($status == 0 or $status == 1) {
					if (strpos($type, 'Tasmota') !== false) {
						$query = "UPDATE messages_out SET payload = '{$http_status}', sent = 0 where node_id = '{$controler_node_id}' AND child_id = '{$controler_child_id}';";
					} else {
        					$query = "UPDATE messages_out SET payload = '{$status}', sent = 0 where node_id = '{$controler_node_id}' AND child_id = '{$controler_child_id}';";
					}
			        	$conn->query($query);
        				if($conn->query($query)){
						$update = 0;
		        		} else {
                                        	$update = 1;
			        	}

                                        $query = "UPDATE zone_controllers SET state = '{$status}' WHERE controler_id = '{$controler_id}';";
                                        if($conn->query($query)){
                                                $update_error=0;
                                        }else{
                                                $update_error=1;
                                        }

        	                        $query = "UPDATE zone SET zone_state = '{$status}' where name = '{$zonename}';";
                	                $conn->query($query);
                        	        if($conn->query($query)){
                                	        $update = 0;
	                                } else {
        	                                $update = 1;
                	                }

                        	        if($update == 0){
                                	        http_response_code(200);
                                        	if($status == 1) {$status = True;} else {$status = False;}
	                                        echo json_encode(array("success" => True, "state" => $status));
        	                        } else {
                	                        http_response_code(400);
                        	                echo json_encode(array("success" => False, "state" => "Update messages_out record error."));
                                	}

				}
			} else {
        			http_response_code(200);
                        	if($row['zone_state'] == 1) {$status = True; $on_off = 'on';} else {$status = False; $on_off = 'off';}
	                        echo json_encode(array("success" => True, "state" => $status, "state_str" => $on_off));
			}
		} //end while ($row = mysqli_fetch_assoc($results))
	} //end if(mysqli_num_rows($results) == 0)
} else {
        http_response_code(400);
        echo json_encode(array("success" => False, "state" => "Data is incomplete."));
}
?>

