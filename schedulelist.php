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
require_once(__DIR__ . '/st_inc/session.php');
confirm_logged_in();
require_once(__DIR__ . '/st_inc/connection.php');
require_once(__DIR__ . '/st_inc/functions.php');
?>
<div class="panel panel-primary">
       	<div class="panel-heading">
        	<i class="fa fa-clock-o fa-fw"></i> <?php echo $lang['schedule']; ?>
           	<div class="pull-right">
                	<div class="btn-group"><?php echo date("H:i"); ?></div>
            	</div>
        </div>
        <!-- /.panel-heading -->
        <div class="panel-body">
        	<ul class="chat">
                <li class="left clearfix">
                <a href="scheduling.php" style="color: #777; cursor: pointer;">
                <span class="chat-img pull-left">
                <div class="circle orangesch">
                       	<i class="ionicons ion-plus"></i>
                </div>
                </span>
                <div class="chat-body clearfix">
                       	<div class="header">
                               	<strong class="primary-font"> </strong>
                               	<small class="pull-right text-muted">
                                <?php echo $lang['schedule_add']; ?> <i class="fa fa-chevron-right fa-fw"></i>
                                </small>
                        </div>
               	</div>
                </a>
                </li>
                <?php
		//following variable set to 0 on start for array index.
		$sch_time_index = '0';
		//$query = "SELECT time_id, time_status, `start`, `end`, tz_id, tz_status, zone_id, index_id, zone_name, temperature, max(temperature) as max_c FROM schedule_daily_time_zone_view group by time_id ORDER BY start asc";
		$query = "SELECT time_id, time_status, `start`, `end`, WeekDays,tz_id, tz_status, zone_id, index_id, zone_name, `category`, temperature, FORMAT(max(temperature),2) as max_c, sch_name, max(sunset) AS sunset FROM schedule_daily_time_zone_view WHERE holidays_id = 0 group by time_id ORDER BY start, sch_name asc";
		$results = $conn->query($query);
		while ($row = mysqli_fetch_assoc($results)) {
                        if($row["sunset"] == 1) { $sunset = 1; } else { $sunset = 0; }
			if($row["WeekDays"]  & (1 << 0)){ $Sunday_status_icon="ion-checkmark-circled"; $Sunday_status_color="orangefa"; }else{ $Sunday_status_icon="ion-close-circled"; $Sunday_status_color="bluefa"; }
			if($row["WeekDays"]  & (1 << 1)){ $Monday_status_icon="ion-checkmark-circled"; $Monday_status_color="orangefa"; }else{ $Monday_status_icon="ion-close-circled"; $Monday_status_color="bluefa"; }
			if($row["WeekDays"]  & (1 << 2)){ $Tuesday_status_icon="ion-checkmark-circled"; $Tuesday_status_color="orangefa"; }else{ $Tuesday_status_icon="ion-close-circled"; $Tuesday_status_color="bluefa"; }
			if($row["WeekDays"]  & (1 << 3)){ $Wednesday_status_icon="ion-checkmark-circled"; $Wednesday_status_color="orangefa"; }else{ $Wednesday_status_icon="ion-close-circled"; $Wednesday_status_color="bluefa"; }
			if($row["WeekDays"]  & (1 << 4)){ $Thursday_status_icon="ion-checkmark-circled"; $Thursday_status_color="orangefa"; }else{ $Thursday_status_icon="ion-close-circled"; $Thursday_status_color="bluefa"; }
			if($row["WeekDays"]  & (1 << 5)){ $Friday_status_icon="ion-checkmark-circled"; $Friday_status_color="orangefa"; }else{ $Friday_status_icon="ion-close-circled"; $Friday_status_color="bluefa"; }
			if($row["WeekDays"]  & (1 << 6)){ $Saturday_status_icon="ion-checkmark-circled"; $Saturday_status_color="orangefa"; }else{ $Saturday_status_icon="ion-close-circled"; $Saturday_status_color="bluefa"; }

			if($row["time_status"]=="0"){ $shactive="bluesch"; }else{ $shactive="orangesch"; }
			$time = strtotime(date("G:i:s"));
			$start_time = strtotime($row['start']);
			$end_time = strtotime($row['end']);
			if($row["WeekDays"]  & (1 << idate('w'))){if ($time >$start_time && $time <$end_time && $row["time_status"]=="1"){$shactive="redsch";}}

			//time shchedule listing
			echo '
			<li class="left clearfix scheduleli animated fadeIn">
			<a href="javascript:active_schedule(' . $row["time_id"] . ');">
			<span class="chat-img pull-left">
                        <div class="circle ' . $shactive . '">';
                                if($row["category"] < 2) { echo '<p class="schdegree">' . DispTemp($conn, number_format($row["max_c"]), 1) . '&deg;</p>'; }
                        echo ' </div>
			</span>
			</a>

			<a style="color: #333; cursor: pointer; text-decoration: none;" data-toggle="collapse" data-parent="#accordion" href="#collapse' . $row['tz_id'] . '">
                        <div class="chat-body clearfix">
                                <div class="header text-info">&nbsp;&nbsp;';
                                        echo '<span class="label label-info">' . $row['sch_name'] . '</span>';
                                        if($row["category"] == 2 && $sunset == 1) { echo '&nbsp;&nbsp;<img src="./images/sunset.png">'; }
                                        echo '<br>&nbsp;&nbsp; '. $row['start'] . ' - ' . $row['end'] . ' &nbsp;&nbsp;

					<small class="pull-right pull-right-days pull-right-sch-list">
					&nbsp;&nbsp;&nbsp;&nbsp;S&nbsp;&nbsp;&nbsp;M&nbsp;&nbsp;&nbsp;T&nbsp;&nbsp;W&nbsp;&nbsp;&nbsp;T&nbsp;&nbsp;&nbsp;F&nbsp;&nbsp;&nbsp;S<br>
					&nbsp;&nbsp;&nbsp;
					<i class="ionicons ' . $Sunday_status_icon . ' fa-lg ' . $Sunday_status_color . '"></i>
					<i class="ionicons ' . $Monday_status_icon . ' fa-lg ' . $Monday_status_color . '"></i>
					<i class="ionicons ' . $Tuesday_status_icon . ' fa-lg ' . $Tuesday_status_color . '"></i>
					<i class="ionicons ' . $Wednesday_status_icon . ' fa-lg ' . $Wednesday_status_color . '"></i>
					<i class="ionicons ' . $Thursday_status_icon . ' fa-lg ' . $Thursday_status_color . '"></i>
					<i class="ionicons ' . $Friday_status_icon . ' fa-lg ' . $Friday_status_color . '"></i>
					<i class="ionicons ' . $Saturday_status_icon . ' fa-lg ' . $Saturday_status_color . '"></i>
					</small>
				</div>
			</div>
			</a>

			<div id="collapse' . $row["tz_id"] . '" class="panel-collapse collapse">
				<br>';

				//zone listing of each time schedule
				$query = "SELECT * FROM  schedule_daily_time_zone_view WHERE holidays_id = 0 AND time_id = {$row['time_id']} order by index_id;";
				$result = $conn->query($query);
				while ($datarw = mysqli_fetch_array($result)) {
					if ($datarw["tz_status"] == "0") {
						$status_icon = "ion-close-circled";
						$status_color = "bluefa";
					} else {
						$status_icon = "ion-checkmark-circled";
						$status_color = "orangefa";
					}
					if ($datarw["coop"] == "1") {
						$coop = '<i class="glyphicon glyphicon-leaf green" data-container="body" data-toggle="popover" data-placement="right" data-content="' . $lang['schedule_coop_help'] . '"></i>';
					} else {
						$coop = '';
					}

					echo '
					<div class="list-group">
						<div class="list-group-item">';
                                                        if ($datarw["category"] < 2) {
								echo '<i class="ionicons ' . $status_icon . ' fa-lg ' . $status_color . '"></i>  ' . $datarw['zone_name'] . ' ' . $coop . '<span class="pull-right text-muted small"><em>' . number_format(DispTemp($conn, $datarw['temperature']), 1) . '&deg;</em></span>';
							} else {
								echo '<i class="ionicons ' . $status_icon . ' fa-lg ' . $status_color . '"></i>  ' . $datarw['zone_name'] . '<span class="pull-right text-muted small"></em></span>';
							}
						echo '</div>';
				} // end while loop

				//delete and edit button for each schedule
				echo '
				<small class="pull-right"><br>
				<a href="javascript:delete_schedule(' . $row["time_id"] . ');"><button class="btn btn-danger btn-sm" data-toggle="confirmation" data-title="ARE YOU SURE?" data-content="You are about to DELETE this SCHEDULE"><span class="glyphicon glyphicon-trash"></span></button> </a> &nbsp;&nbsp;
				<a href="scheduling.php?id=' . $row["time_id"] . '" class="btn btn-default btn-sm login"><span class="ionicons ion-edit"></span></a>
				</small>
			</div>
			<!-- /.list-group -->
		</div>
		<!-- /.panel-colapse -->
		</li>';

		//calculate total time of day schedule using array schedule_time with index as sch_time_index variable
		if ($row["time_status"] == "1") {
        		$total_time = $end_time - $start_time;
                	$total_time = $total_time / 60;
	                //save all total_time variable value to schedule_time array and incriment array index (sch_time_index)
        	        $schedule_time[$sch_time_index] = $total_time;
                	$sch_time_index = $sch_time_index + 1;
     		}
      	} //end of schedule time while loop 
	?>
        </ul>
        </div>
	<!-- /.panel-body -->
        <div class="panel-footer">
        	<?php
            	ShowWeather($conn);
            	?>
            	<div class="pull-right">
                	<div class="btn-group">
                    		<?php
                    		echo '<i class="ionicons ion-ios-clock-outline"></i> All Schedule: ' . secondsToWords((array_sum($schedule_time) * 60));
                    		?>
                	</div>
            	</div>
        </div>
	<!-- /.panel-footer -->
</div>
<!-- /.panel-primary -->
<?php if (isset($conn)) {
    $conn->close();
} ?>
<script>
$('[data-toggle=confirmation]').confirmation({
  rootSelector: '[data-toggle=confirmation]',
  container: 'body'
});
</script>
