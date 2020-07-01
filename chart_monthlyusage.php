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
echo "<h4>".$lang['graph_boiler_usage']."</h4></p>".$lang['graph_boiler_usage_text']."</p>";
?>

<div class="flot-chart">
   <div class="flot-chart-content" id="month_usage"></div>
</div>

<?php
$arr_name='month_usage';
$query="select date(start_datetime) as month, 
sum(TIMESTAMPDIFF(MINUTE, start_datetime, expected_end_date_time))/60 as total_minuts,
sum(TIMESTAMPDIFF(MINUTE, start_datetime, stop_datetime))/60 as on_minuts, 
(sum(TIMESTAMPDIFF(MINUTE, start_datetime, expected_end_date_time)) - sum(TIMESTAMPDIFF(MINUTE, start_datetime, stop_datetime)))/60 as save_minuts
from boiler_logs WHERE start_datetime >= NOW() - INTERVAL 400 DAY GROUP BY YEAR(start_datetime), MONTH(start_datetime) order by month asc";
$result = $conn->query($query);

//create array of pairs of x and y values
$$arr_name = array();
while ($row = mysqli_fetch_assoc($result)) { 
	$total_minuts[] = array(strtotime($row['month']) * 1000, (int) $row['total_minuts'] );
	$on_minuts[] = array(strtotime($row['month']) * 1000, (int) $row['on_minuts'] );
	$save_minuts[] = array(strtotime($row['month']) * 1000, (int) $row['save_minuts'] );
} ?>

<script type="text/javascript">
// Create Monthly Usage dataset
var total_minuts = <?php echo json_encode($total_minuts); ?>;
var on_minuts = <?php echo json_encode($on_minuts); ?>;
var save_minuts = <?php echo json_encode($save_minuts); ?>;

var dataset_mu = [
{label: "<?php echo $lang['graph_total_time']; ?>  ", data: total_minuts, color: "#DE000F"},
{label: "<?php echo $lang['graph_consumed_time']; ?>  ", data: on_minuts, color: "#7D0096"},
{label: "<?php echo $lang['graph_saved_time']; ?>  ", data: save_minuts, color: "#009604"} ];
</script>

<?php
$arr_name='month_usage_bar';
$query="select date(start_datetime) as month, 
sum(TIMESTAMPDIFF(MINUTE, start_datetime, expected_end_date_time))/60 as total_minuts,
sum(TIMESTAMPDIFF(MINUTE, start_datetime, stop_datetime))/60 as on_minuts, 
(sum(TIMESTAMPDIFF(MINUTE, start_datetime, expected_end_date_time)) - sum(TIMESTAMPDIFF(MINUTE, start_datetime, stop_datetime)))/60 as save_minuts
from boiler_logs WHERE start_datetime >= NOW() - INTERVAL 400 DAY GROUP BY month(start_datetime) order by month asc";
$result = $conn->query($query);

//create array of pairs of x and y values
$$arr_name = array();
while ($row = mysqli_fetch_assoc($result)) {
	//$btotal_minuts[] = array(strtotime($row['month']) * 1000, (int) $row['total_minuts'], (int) $row['on_minuts'], (int) $row['save_minuts']  );
	$btotal_minuts[] = array(strtotime($row['month']) * 1000, (int) $row['total_minuts'] );
	$bon_minuts[] = array(strtotime($row['month']) * 1000, (int) $row['on_minuts'] );
	$bsave_minuts[] = array(strtotime($row['month']) * 1000, (int) $row['save_minuts'] );
}
;?>
