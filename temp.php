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

require_once(__DIR__.'/st_inc/connection.php');
require_once(__DIR__.'/st_inc/functions.php');
?>

<?php 

function clean($string) {
   $string = str_replace(' ', '', $string); // Replaces all spaces with hyphens.
   $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.

   return preg_replace('/-+/', '-', $string); // Replaces multiple hyphens with single one.
}


$querya="select * from zone_view where `type` = 'Heating' order BY index_id asc;";
$resulta = $conn->query($querya);
while ($row = mysqli_fetch_assoc($resulta)) {
	$zone_id=$row['id'];
	$zone=clean($row['name']);
	//$zone_name=$row['model'];
	$zone_max_c=$row['max_c'];
	$zone_max_operation_time=$row['max_operation_time'];
	$zone_hysteresis_time=$row['hysteresis_time'];
	$zone_sensor_id=$row['sensors_id'];
	$zone_sensor_child_id=$row['sensor_child_id'];
	$zone_controler_id=$row['controler_id'];
	$zone_controler_child_id=$row['controler_child_id'];
	$zone_gpio_pin=$row['gpio_pin'];	
	//echo $zone_name;
	
	$query="select * from messages_in_view_24h where node_id = {$zone_sensor_id};";
	$result = $conn->query($query);
	//create array of pairs of x and y values
	$zone_name = array();
	while ($rowb = mysqli_fetch_assoc($result)) { 
		$zone_name[] = array(strtotime($rowb['datetime']) * 1000, $rowb['payload']);
	}
	echo "<pre>";
	print_r($zone_name);
	echo "</pre>";
}



/*
//following example works and echo our array and all the values correctely but array name isnt specified hence problem 
$querya="select * from zone_view where `type` = 'Heating' order BY index_id asc;";
$resulta = $conn->query($querya);
while ($row = mysqli_fetch_assoc($resulta)) {
	$zone_id=$row['id'];
	//$zone_name=clean($row['name']);
	//$zone_name=$row['model'];
	$zone_max_c=$row['max_c'];
	$zone_max_operation_time=$row['max_operation_time'];
	$zone_hysteresis_time=$row['hysteresis_time'];
	$zone_sensor_id=$row['sensors_id'];
	$zone_sensor_child_id=$row['sensor_child_id'];
	$zone_controler_id=$row['controler_id'];
	$zone_controler_child_id=$row['controler_child_id'];
	$zone_gpio_pin=$row['gpio_pin'];	
	//echo $zone_name;
	
	$query="select * from messages_in_view_24h where node_id = {$zone_sensor_id};";
	$result = $conn->query($query);
	//create array of pairs of x and y values
	$$zone_name = array();
	while ($rowb = mysqli_fetch_assoc($result)) { 
		$zone_name[] = array(strtotime($rowb['datetime']) * 1000, $rowb['payload']);
	}
	echo "<pre>";
	print_r($zone_name);
	echo "</pre>";
}
*/



/*
//$arr_name='ground_floor';
$query="select * from messages_in_view_24h where node_id= 21";
$result = $conn->query($query);
//create array of pairs of x and y values
$$arr_name = array();
while ($row = mysqli_fetch_assoc($result)) { 
	$ground_floor[] = array(strtotime($row['datetime']) * 1000, $row['payload']);
}
//print_r($ground_floor);
echo "<pre>";
   print_r($ground_floor);
echo "</pre>";

*/



?>