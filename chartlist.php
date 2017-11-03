<?php
$graphs_page = '1';

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

 
$arr_name='ground_floor';
$query="select * from messages_in_view_24h where node_id= 21";
$result = mysql_query($query, $connection);
//create array of pairs of x and y values
$$arr_name = array();
while ($row = mysql_fetch_assoc($result)) { 
	$ground_floor[] = array(strtotime($row['datetime']) * 1000, $row['payload']);
}


$arr_name='first_floor';
$query="select * from messages_in_view_24h where node_id= 20";
$result = mysql_query($query, $connection);
//create array of pairs of x and y values
$$arr_name = array();
while ($row = mysql_fetch_assoc($result)) { 
   $first_floor[] = array(strtotime($row['datetime']) * 1000, $row['payload']);
}

//weather temperature
$query="select * from messages_in_view_24h where node_id= 1";
$result = mysql_query($query, $connection);
//create array of pairs of x and y values
$weather_c = array();
while ($row = mysql_fetch_assoc($result)) { 
   $weather_c[] = array(strtotime($row['datetime']) * 1000, $row['payload']);
}

//hot water temperature
$query="select * from messages_in_view_24h where node_id= 30";
$result = mysql_query($query, $connection);
//create array of pairs of x and y values
$hot_water = array();
while ($row = mysql_fetch_assoc($result)) { 
   $hot_water[] = array(strtotime($row['datetime']) * 1000, $row['payload']);
}

//hot water room
$query="select * from messages_in_view_24h where node_id= 25";
$result = mysql_query($query, $connection);
//create array of pairs of x and y values
$immersion_room = array();
while ($row = mysql_fetch_assoc($result)) { 
   $immersion_room[] = array(strtotime($row['datetime']) * 1000, $row['payload']);
}

//cpu temperature
$query="select * from messages_in_view_24h where node_id= 0";
$result = mysql_query($query, $connection);
//create array of pairs of x and y values
$system_c = array();
while ($row = mysql_fetch_assoc($result)) { 
   $system_c[] = array(strtotime($row['datetime']) * 1000, $row['payload']);
}

//pi box temperature
$query="select * from messages_in_view_24h where node_id= 30";
$result = mysql_query($query, $connection);
//create array of pairs of x and y values
$pi_box = array();
while ($row = mysql_fetch_assoc($result)) { 
   $pi_box[] = array(strtotime($row['datetime']) * 1000, $row['payload']);
}

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
 

 
