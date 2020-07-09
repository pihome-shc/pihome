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
                            <i class="fa fa-rocket fa-fw"></i>  <?php echo $lang['boost']; ?>    
						<div class="pull-right"> <div class="btn-group"><?php echo date("H:i"); ?></div> </div>
                        </div>
                        <!-- /.panel-heading -->
<div class="panel-body">
<ul class="chat"> 
<?php 
$query = "SELECT boost.id, boost.status, boost.zone_id, zone.index_id, boost.time, boost.temperature, boost.minute FROM boost join zone on boost.zone_id = zone.id WHERE boost.`purge` = '0' order by zone.index_id, boost.temperature;";
$results = $conn->query($query);
while ($row = mysqli_fetch_assoc($results)) {
	//query to search location device_id		
	$query = "SELECT * FROM zone_view WHERE id = {$row['zone_id']} LIMIT 1";
	$result = $conn->query($query);
	$pi_device = mysqli_fetch_array($result);
	$device = $pi_device['name'];	
	$type = $pi_device['type'];	
        $category = $pi_device['category'];
	$zone_status = $pi_device['status'];
	if ($zone_status != 0) {
		echo '
		<li class="left clearfix animated fadeIn">
		<a href="javascript:active_boost('.$row["id"].');">
		<span class="chat-img pull-left override">';
		if($row["status"]=="0"){ $shactive="bluesch"; $status="Off"; }else{ $shactive="redsch"; $status="On"; }
                if ($category == 2) {
                        echo '<div class="circle '. $shactive.'"><p class="schdegree"></p></div>
                        </span></a>
                        <div class="chat-body clearfix">
                        <div class="header">';
                } else {
                        echo '<div class="circle '. $shactive.'"><p class="schdegree">'.number_format(DispTemp($conn,$row["temperature"]),0).'&deg;</p></div>
                        </span></a>
                        <div class="chat-body clearfix">
                        <div class="header">';
                }
		if($row["status"]=="0" && $type=="Heating"){ $pi_image = "radiator.png";  }
		elseif($row["status"]=="0" && $type=="Water"){ $pi_image = "off_hot_water.png";  }
		elseif($row["status"]=="1" && $type=="Heating"){ $pi_image = "radiator1.png";  }
		elseif($row["status"]=="1" && $type=="Water"){ $pi_image = "hot_water.png"; }
                elseif($row["status"]=="0" && $category == 2){ $pi_image = "icons8-light-off-30.png";  }
                elseif($row["status"]=="1" && $category == 2){ $pi_image = "icons8-light-automation-30.png";  }
		$phpdate = strtotime($row['time']);
		$boost_time = $phpdate + ($row['minute'] * 60);
		echo '<strong class="primary-font">&nbsp;&nbsp;'. $device.' </strong>
		<span class="pull-right text-muted small"><em> <img src="images/'.$pi_image.'" border="0"></em></span>
		<br>';
		if($row["status"]=="1"){echo '&nbsp;&nbsp;'.date("Y-m-d H:i", $boost_time).'';}
		else{echo '&nbsp;&nbsp;'. number_format(($row['minute']),0).' minutes';}
		echo '';
		echo '</div></div></li>';				
	}	
}		
?>
</ul>
</div>
                        <!-- /.panel-body -->
						<div class="panel-footer">
<?php
ShowWeather($conn);
?>
                        </div>
                    </div>
<?php if(isset($conn)) { $conn->close();} ?>
