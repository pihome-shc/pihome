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
        $query = "SELECT * FROM boost_view where name = '{$zonename}' LIMIT 1;";
        $results = $conn->query($query);
        $row = mysqli_fetch_assoc($results);
	if(! $row) {
	        http_response_code(400);
        	echo json_encode(array("success" => False, "state" => "No record found."));
	} else {
	        $boost_id=$row['id'];
		if(isset($_GET['state'])) {
			switch ($_GET['state']) {
    				case 'true':
        				$boost_status = 1;
        				break;
    				case 'false':
        				$boost_status = 0;
				        break;
                                case '1':
                                        $boost_status = 1;
                                        break;
 				case '0':
				        $boost_status = 0;
				        break;
				default:
	                                http_response_code(400);
        	                        echo json_encode(array("success" => False, "state" => "'state' parameter not correctly set."));
					$boost_status = -1;
			}
			if($boost_status == 0 or $boost_status == 1) {
	        		$query = "UPDATE boost SET status = '{$boost_status}' where id = '{$boost_id}';";
		        	$conn->query($query);
        			if($conn->query($query)){
                			http_response_code(200);
					if($boost_status == 1) {$boost_status = True;} else {$boost_status = False;}
	                		echo json_encode(array("success" => True, "state" => $boost_status));
		        	} else {
        		        	http_response_code(400);
                			echo json_encode(array("success" => False, "state" => "Update database error."));
		        	}
			}
		} else {
        		http_response_code(200);
                        if($row['status'] == 1) {$boost_status = True; $on_off = 'on';} else {$boost_status = False; $on_off = 'off';}
                        echo json_encode(array("success" => True, "state" => $boost_status, "state_str" => $on_off));
		}
	}
} else {
        http_response_code(400);
        echo json_encode(array("success" => False, "state" => "Data is incomplete."));
}
?>

