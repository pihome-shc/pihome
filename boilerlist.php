<?php
//boiler usage time 
echo "<h4>PiHome Saving</h4></p>These Savings are based on schedule start and stop time. </p>";
$query="select date(start_datetime) as date, 
sum(TIMESTAMPDIFF(MINUTE, start_datetime, expected_end_date_time)) as total_minuts,
sum(TIMESTAMPDIFF(MINUTE, start_datetime, stop_datetime)) as on_minuts, 
(sum(TIMESTAMPDIFF(MINUTE, start_datetime, expected_end_date_time)) - sum(TIMESTAMPDIFF(MINUTE, start_datetime, stop_datetime))) as save_minuts
from boiler_logs WHERE start_datetime >= NOW() - INTERVAL 30 DAY GROUP BY date(start_datetime) desc";
$result = mysql_query($query, $connection);
echo '<table id="example" class="table table-bordered table-hover dt-responsive" width="100%">';
echo '<thead><tr><th>Date</th><th>T. Min</th><th class="all">On Min</th><th>S. Min</th><th> <i class="glyphicon glyphicon-leaf green"></th></tr></thead><tbody>';
while ($row = mysql_fetch_assoc($result)) {
	echo '
	<tr>
	<td class="all">' . $row['date'] . '</td>
	<td class="all">' . $row['total_minuts'] . '</td>
	<td class="all">' . $row['on_minuts'] . '</td>
	<td class="all">' . $row['save_minuts'] . '</td>
	<td class="all">'.number_format(($row['save_minuts']/$row['total_minuts'])*100,0).'%</td>
	</tr>';
}
 echo '</tbody></table>';
;?>


 
