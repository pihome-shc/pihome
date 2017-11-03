<?php
$arr_name='month_usage';
$query="select date(start_datetime) as month, 
sum(TIMESTAMPDIFF(MINUTE, start_datetime, expected_end_date_time))/60 as total_minuts,
sum(TIMESTAMPDIFF(MINUTE, start_datetime, stop_datetime))/60 as on_minuts, 
(sum(TIMESTAMPDIFF(MINUTE, start_datetime, expected_end_date_time)) - sum(TIMESTAMPDIFF(MINUTE, start_datetime, stop_datetime)))/60 as save_minuts
from boiler_logs WHERE start_datetime >= NOW() - INTERVAL 400 DAY GROUP BY month(start_datetime) order by month asc";
$result = mysql_query($query, $connection);

//create array of pairs of x and y values
$$arr_name = array();
while ($row = mysql_fetch_assoc($result)) { 
	$total_minuts[] = array(strtotime($row['month']) * 1000, (int) $row['total_minuts'] );
	$on_minuts[] = array(strtotime($row['month']) * 1000, (int) $row['on_minuts'] );
	$save_minuts[] = array(strtotime($row['month']) * 1000, (int) $row['save_minuts'] );
}


$arr_name='month_usage_bar';
$query="select date(start_datetime) as month, 
sum(TIMESTAMPDIFF(MINUTE, start_datetime, expected_end_date_time))/60 as total_minuts,
sum(TIMESTAMPDIFF(MINUTE, start_datetime, stop_datetime))/60 as on_minuts, 
(sum(TIMESTAMPDIFF(MINUTE, start_datetime, expected_end_date_time)) - sum(TIMESTAMPDIFF(MINUTE, start_datetime, stop_datetime)))/60 as save_minuts
from boiler_logs WHERE start_datetime >= NOW() - INTERVAL 400 DAY GROUP BY month(start_datetime) order by month asc";
$result = mysql_query($query, $connection);

//create array of pairs of x and y values
$$arr_name = array();
while ($row = mysql_fetch_assoc($result)) {
	//$btotal_minuts[] = array(strtotime($row['month']) * 1000, (int) $row['total_minuts'], (int) $row['on_minuts'], (int) $row['save_minuts']  );
	$btotal_minuts[] = array(strtotime($row['month']) * 1000, (int) $row['total_minuts'] );
	$bon_minuts[] = array(strtotime($row['month']) * 1000, (int) $row['on_minuts'] );
	$bsave_minuts[] = array(strtotime($row['month']) * 1000, (int) $row['save_minuts'] );
}
;?>
<div class="flot-chart">
   <div class="flot-chart-content" id="month_usage"></div>
</div>

