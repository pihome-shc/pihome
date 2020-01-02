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

$graphs_page = '1';
echo "<h4>".$lang['graph_temperature']."</h4></p>".$lang['graph_24h']."</p>";
/*
//query to get system table
$query = "SELECT * FROM location where zone IS NOT NULL AND zone != '' ORDER BY index_id asc";
$result = mysql_query($query, $connection);
confirm_query($result);
$row = mysql_fetch_array($result);
$zone1 = $row['device'];
$zone2 = $row['device'];
$zone3 = $row['device'];
$zone4 = $row['device'];
*/

/*
function clean($string) {
   $string = str_replace(' ', '', $string); // Replaces all spaces with hyphens.
   $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.

   return preg_replace('/-+/', '-', $string); // Replaces multiple hyphens with single one.
}


$querya="select * from zone_view where `type` = 'Heating' order BY index_id asc;";
$resulta = $conn->query($querya);
while ($row = mysqli_fetch_assoc($resulta)) {
	$zone_id=$row['id'];
	$zone_name=clean($row['name']);
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
	//echo "<pre>";
	//print_r($zone_name);
	//echo "</pre>";
}



$query="select * from messages_in_view_24h where node_id= 21";
$result = $conn->query($query);
//create array of pairs of x and y values
while ($row = mysqli_fetch_assoc($result)) { 
	$ground_floor[] = array(strtotime($row['datetime']) * 1000, $row['payload']);
}


//$arr_name='first_floor';
$query="select * from messages_in_view_24h where node_id= 20";
$result = $conn->query($query);
//create array of pairs of x and y values
while ($row = mysqli_fetch_assoc($result)) { 
   $first_floor[] = array(strtotime($row['datetime']) * 1000, $row['payload']);
}


*/

//weather temperature
$query="select * from messages_in_view_24h where node_id= 1";
$result = $conn->query($query);
//create array of pairs of x and y values
$weather_c = array();
while ($row = mysqli_fetch_assoc($result)) { 
   $weather_c[] = array(strtotime($row['datetime']) * 1000, $row['payload']);
}

/*
//hot water temperature
$query="select * from messages_in_view_24h where node_id= 30";
$result = $conn->query($query);
//create array of pairs of x and y values
$hot_water = array();
while ($row = mysqli_fetch_assoc($result)) { 
   $hot_water[] = array(strtotime($row['datetime']) * 1000, $row['payload']);
}

*/

/*
//No Temperature Sensors installed, if you have Temperature Sensors with ID 25 put it in Immersion Room and un-comment. 
//hot water room
$query="select * from messages_in_view_24h where node_id= 25";
$result = $conn->query($query);
//create array of pairs of x and y values
$immersion_room = array();
while ($row = mysqli_fetch_assoc($result)) { 
   $immersion_room[] = array(strtotime($row['datetime']) * 1000, $row['payload']);
}
*/

//cpu temperature
$query="select * from messages_in_view_24h where node_id= 0";
$result = $conn->query($query);
//create array of pairs of x and y values
$system_c = array();
while ($row = mysqli_fetch_assoc($result)) { 
   $system_c[] = array(strtotime($row['datetime']) * 1000, $row['payload']);
}

/*

//pi box temperature
No Temperature Sensors Installed for Raspberry Pi only CPU Temperature is recorded. 
$query="select * from messages_in_view_24h where node_id= 30";
$result = $conn->query($query);
//create array of pairs of x and y values
$pi_box = array();
while ($row = mysqli_fetch_assoc($result)) { 
   $pi_box[] = array(strtotime($row['datetime']) * 1000, $row['payload']);
}

*/
;?>
<div class="flot-chart">
	<div class="flot-chart-content" id="placeholder"></div>
</div>
<br>
<div class="flot-chart">
   <div class="flot-chart-content" id="hot_water"></div>
</div>
<br>
<div class="flot-chart">
   <div class="flot-chart-content" id="system_c"></div>
</div>