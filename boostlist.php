<?php require_once("st_inc/session.php"); ?>
<?php confirm_logged_in(); ?>
<?php require_once("st_inc/connection.php"); ?>
<?php require_once("st_inc/functions.php"); ?>
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <i class="fa fa-rocket fa-fw"></i>  Boost    
						<div class="pull-right"> <div class="btn-group"><?php echo date("H:i"); ?></div> </div>
                        </div>
                        <!-- /.panel-heading -->
<div class="panel-body">
<ul class="chat"> 
<?php 
$query = "SELECT boost.id, boost.status, boost.zone_id, zone.index_id, boost.time, boost.temperature, boost.minute FROM boost join zone on boost.zone_id = zone.id order by zone.index_id";
$results = mysql_query($query, $connection);
while ($row = mysql_fetch_assoc($results)) {
	echo '
	<li class="left clearfix animated fadeIn">
	<a href="javascript:active_boost('.$row["zone_id"].');">
	<span class="chat-img pull-left override">';
	if($row["status"]=="0"){ $shactive="bluesch"; $status="Off"; }else{ $shactive="redsch"; $status="On"; }
        echo '<div class="circle '. $shactive.'"><p class="schdegree">'.$row["temperature"].'&deg;</p></div>
    </span></a>
	<div class="chat-body clearfix">
    <div class="header">';
	
	//query to search location device_id		
	$query = "SELECT * FROM zone WHERE id = {$row['zone_id']} LIMIT 1";
	$result = mysql_query($query, $connection);
	confirm_query($result);
	$pi_device = mysql_fetch_array($result);
	$device = $pi_device['name'];	
	$type = $pi_device['type'];	
							
	if($row["status"]=="0" && $type=="Heating"){ $pi_image = "radiator.png";  }
	elseif($row["status"]=="0" && $type=="Water"){ $pi_image = "off_hot_water.png";  }
	elseif($row["status"]=="1" && $type=="Heating"){ $pi_image = "radiator1.png";  }
	elseif($row["status"]=="1" && $type=="Water"){ $pi_image = "hot_water.png"; }
	$phpdate = strtotime($row['time']);
	$boost_time = $phpdate + ($row['minute'] * 60);
	echo '<strong class="primary-font">&nbsp;&nbsp;'. $device.' </strong>
	<span class="pull-right text-muted small"><em> <img src="images/'.$pi_image.'" border="0"></em></span>
	<br>';
	if($row["status"]=="1"){echo '&nbsp;&nbsp;'.date("Y-m-d H:i", $boost_time).'';}
	echo '';
	echo '</div></div></li>';				
}			
?>
</ul>
</div>
                        <!-- /.panel-body -->
						<div class="panel-footer">
<?php
$query="select * from weather";
$result = mysql_query($query, $connection);
//confirm_query($result);
$weather = mysql_fetch_array($result);
?>

Outside: <?php //$weather = getWeather(); ?><?php echo $weather['c'] ;?>&deg;C
<span><img border="0" width="24" src="images/<?php echo $weather['img'];?>.png" title="<?php echo $weather['title'];?> - 
<?php echo $weather['description'];?>"></span> <span><?php echo $weather['title'];?> - 
<?php echo $weather['description'];?></span>
                        </div>
                    </div>
<?php if(isset($connection)) { mysql_close($connection); } ?>