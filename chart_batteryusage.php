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
echo "<h4>".$lang['graph_battery_usage']."</h4></p>".$lang['graph_battery_level_text']."</p>";

;?>
<div class="flot-chart">
   <div class="flot-chart-content" id="battery_level"></div>
</div>
<br>
<script type="text/javascript">
// distinct color implementation for plot lines 
function rainbow(numOfSteps, step) {
    var r, g, b;
    var h = step / numOfSteps;
    var i = ~~(h * 6);
    var f = h * 6 - i;
    var q = 1 - f;
    switch(i % 6){
        case 0: r = 1; g = f; b = 0; break;
        case 1: r = q; g = 1; b = 0; break;
        case 2: r = 0; g = 1; b = f; break;
        case 3: r = 0; g = q; b = 1; break;
        case 4: r = f; g = 0; b = 1; break;
        case 5: r = 1; g = 0; b = q; break;
    }
    var c = "#" + ("00" + (~ ~(r * 255)).toString(16)).slice(-2) + ("00" + (~ ~(g * 255)).toString(16)).slice(-2) + ("00" + (~ ~(b * 255)).toString(16)).slice(-2);
    return (c);
}

// create battery usage dataset based on all available zones
var bat_level_dataset = [
<?php
    $querya ="select * from nodes where `type` = 'MySensor' AND `name` LIKE '%Sensor' AND `min_value` <> 0;";
    $resulta = $conn->query($querya);
    $counter = 0;
    $count = mysqli_num_rows($resulta) + 1;
    while ($row = mysqli_fetch_assoc($resulta)) {
        //grab the node id to be displayed in the plot legend
		$node_id=$row['node_id'];
		$query="select * from zone_view where sensors_id = '{$node_id}' limit 1;";
		$resultz = $conn->query($query);
		$zone_row = mysqli_fetch_array($resultz);
		$zone_name = $zone_row['name'];
		$label = $zone_name ." - ID ".$node_id;
		$query="SELECT bat_voltage, bat_level, `update`  FROM nodes_battery WHERE `update` >= last_day(now()) + interval 1 day - interval 3 MONTH AND bat_level is not NULL and node_id = '{$node_id}' GROUP BY Week(`update`), Day(`update`) ORDER BY `update` ASC;";
        	$result = $conn->query($query);
        	// create array of pairs of x and y values for every zone
        	$bat_level = array();
        	while ($rowb = mysqli_fetch_assoc($result)) { 
            		$bat_level[] = array(strtotime($rowb['update']) * 1000, $rowb['bat_level']);
        	}
        	// create dataset entry using distinct color based on zone index(to have the same color everytime chart is opened)
        	echo "{label: \"".$label."\", data: ".json_encode($bat_level).", color: rainbow(".$count.",".++$counter.") }, \n";
    }
?> ];
</script>
