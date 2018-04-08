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
require_once(__DIR__.'/st_inc/session.php'); 
confirm_logged_in();
require_once(__DIR__.'/st_inc/connection.php');
require_once(__DIR__.'/st_inc/functions.php');
?>
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <i class="fa fa-clock-o fa-fw"></i> Schedule   
						<div class="pull-right"> <div class="btn-group"><?php echo date("H:i"); ?></div> </div>
                        </div>
                        <!-- /.panel-heading -->
 <div class="panel-body">
 <ul class="chat"> 
 				 <li class="left clearfix">
                     <a href="schedule_add.php" style="color: #777; cursor: pointer;" ><span class="chat-img pull-left">
                        <div class="circle orangesch"> <i class="ionicons ion-plus"></i> </div>
                     </span>
                     <div class="chat-body clearfix">
                         <div class="header">
                             <strong class="primary-font">   </strong> 
							 <small class="pull-right text-muted">
								Add Schedule <i class="fa fa-chevron-right fa-fw"></i></a>
                             </small>
                         </div>
                     </div>
                </li>
<?php 
//following variable set to 0 on start for array index. 
$sch_time_index = '0';
//$query = "SELECT * FROM schedule_daily_time_zone_view group by time_id ORDER BY start asc";
$query = "SELECT time_id, time_status, `start`, `end`, tz_id, tz_status, zone_id, index_id, zone_name, temperature, max(temperature) as max_c FROM schedule_daily_time_zone_view group by time_id ORDER BY start asc";
$results = $conn->query($query);
while ($row = mysqli_fetch_assoc($results)) {

	if($row["time_status"]=="0"){ $shactive="bluesch"; }else{ $shactive="orangesch"; }
	$time = strtotime(date("G:i:s")); 
	$start_time = strtotime($row['start']);
	$end_time = strtotime($row['end']);
	if ($time >$start_time && $time <$end_time && $row["time_status"]=="1"){$shactive="redsch";}

	//time shchedule listing
	echo '
	<li class="left clearfix scheduleli animated fadeIn">
	
	<a href="javascript:active_schedule('.$row["time_id"].');"><span class="chat-img pull-left"><div class="circle '. $shactive.'"> <p class="schdegree">'.$row["max_c"].'&deg;</p></div></span></a>
	
	<a style="color: #333; cursor: pointer; text-decoration: none;" data-toggle="collapse" data-parent="#accordion" href="#collapse'.$row['tz_id'].'">
	<div class="chat-body clearfix">
	<div class="header"><div class="text-info">&nbsp;&nbsp;'. $row['start'].' - ' .$row['end'].' &nbsp;&nbsp;<i class="fa fa-angle-double-right fa-fw"></i></div></a>
	<div id="collapse'.$row["tz_id"].'" class="panel-collapse collapse">
	<br>';


	//zone listing of each time schedule 
	$query="SELECT * FROM  schedule_daily_time_zone_view WHERE time_id = {$row['time_id']} order by index_id";
	$result = $conn->query($query);
	while ($datarw=mysqli_fetch_array($result)) {
	if($datarw["tz_status"]=="0"){ $status_icon="ion-close-circled"; $status_color="bluefa"; }else{ $status_icon="ion-checkmark-circled"; $status_color="orangefa"; }
	echo '
		<div class="list-group">
		<div class="list-group-item">
		<i class="ionicons '.$status_icon.' fa-lg '.$status_color.'"></i>  '.$datarw['zone_name'].'<span class="pull-right text-muted small"><em>'.$datarw['temperature'].'&deg;</em></span>
		</div>';
	}

//delete and edit button for each schedule			
echo '
<div class="list-group-item">
<a href="javascript:delete_schedule('.$row["time_id"].');"><button class="btn btn-danger btn-xs"><span class="glyphicon glyphicon-trash"></span></button> </a>	
<a href="schedule_edit.php?id='.$row["time_id"].'" class="btn btn-default btn-xs login"><span class="ionicons ion-edit"></span></a>

</div>
</div>
 </div>
 </div>
 </div>
 </li>';				

//calculate total time of day schedule using array schedule_time with index as sch_time_index variable
	if($row["time_status"]=="1"){
		$total_time=$end_time-$start_time;
		$total_time=$total_time/60;
		//save all total_time variable value to schedule_time array and incriment array index (sch_time_index)
		$schedule_time[$sch_time_index] = $total_time;
		$sch_time_index = $sch_time_index+1;
	}
	//end of schedule time while loop
} ?>
</ul>
                       </div>
                        <!-- /.panel-body -->
						<div class="panel-footer">
<?php 
$query="select * from weather";
$result = $conn->query($query);
$weather = mysqli_fetch_array($result);
?>
<?php //$weather = getWeather(); ?><?php echo $weather['c'] ;?>&deg;C
<span><img border="0" width="24" src="images/<?php echo $weather['img'];?>.png" title="<?php echo $weather['title'];?> - 
<?php echo $weather['description'];?>"></span> <span><?php echo $weather['title'];?>
</span>
                            <div class="pull-right">
                                <div class="btn-group">
<?php
echo '<i class="ionicons ion-ios-clock-outline"></i> All Schedule: '.secondsToWords((array_sum($schedule_time)*60));
?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
<?php if(isset($conn)) { $conn->close();} ?>