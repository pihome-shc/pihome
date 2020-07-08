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
                            <i class="fa fa-refresh fa-fw"></i>  <?php echo $lang['override']; ?>   
						<div class="pull-right"> <div class="btn-group"><?php echo date("H:i"); ?></div> </div>
                        </div>
                        <!-- /.panel-heading -->
<div class="panel-body">
<ul class="chat">
<?php 
$query = "SELECT * FROM override_view WHERE `purge` = '0' AND `category` < 2 order by index_id;";
$results = $conn->query($query);
while ($row = mysqli_fetch_assoc($results)) {
	//query to search location device_id		
	$query = "SELECT * FROM zone WHERE id = {$row['zone_id']} LIMIT 1";
	$result = $conn->query($query);
	$pi_device = mysqli_fetch_array($result);
	$zone = $pi_device['name'];	
	$type = $pi_device['type'];	
	$zone_status = $pi_device['status'];
	if ($zone_status != 0) {
		echo '
		<li class="left clearfix animated fadeIn">
		<a href="javascript:active_override('.$row["zone_id"].');">
		<span class="chat-img pull-left override">';
		if($row["status"]=="0"){ $shactive="bluesch"; $status="Off"; }else{ $shactive="orangesch"; $status="On"; }
		echo '<div class="circle '. $shactive.'"><p class="schdegree">'.$row["temperature"].'&deg;</p></div>
		</span></a>
		<div class="chat-body clearfix">
		<div class="header">';
		if($row["status"]=="0" && $type=="Heating"){ $image = "radiator.png";  }
		elseif($row["status"]=="0" && $type=="Water"){ $image = "off_hot_water.png";  }
		elseif($row["status"]=="1" && $type=="Heating"){ $image = "radiator1.png";  }
		elseif($row["status"]=="1" && $type=="Water"){ $image = "hot_water.png"; }
		echo '<strong class="primary-font">&nbsp;&nbsp;'. $zone.' </strong><span class="pull-right text-muted small"><em> <img src="images/'.$image.'" border="0"></em></span>
		<br> &nbsp;&nbsp;'. $row['time']. '';
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
