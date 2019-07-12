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
                            <i class="fa fa-paper-plane fa-1x"></i> <?php echo $lang['holidays']; ?>   
						<div class="pull-right"> <div class="btn-group"><?php echo date("H:i"); ?></div> </div>
                        </div>
                        <!-- /.panel-heading -->
 <div class="panel-body">
 
 <ul class="chat"> 
 				 <li class="left clearfix">
                     <a href="holidays_add.php" style="color: #777; cursor: pointer;" ><span class="chat-img pull-left">
                        <div class="circle orangesch"> <i class="ionicons ion-plus"></i> </div>
                     </span>
                     <div class="chat-body clearfix">
                         <div class="header">
                             <strong class="primary-font">   </strong> 
                             
							 <small class="pull-right text-muted">
								<?php echo $lang['holidays_add']; ?> <i class="fa fa-chevron-right fa-fw"></i></a>
                             </small>
                         </div>
                     </div>
                </li>
<?php
//get the current holidays 
$query = "SELECT * FROM holidays ORDER BY start_date_time asc";
$results = $conn->query($query);
while ($row = mysqli_fetch_assoc($results)) {
				echo '
				<li class="left clearfix holidaysli">
					<a href="javascript:active_holidays('.$row["id"].');">

					<span class="chat-img pull-left">';
					if($row["status"]=="0"){ $shactive="bluesch"; }else{ $shactive="orangesch"; }
					$time = strtotime(date("G:i:s")); 
					$start_date_time = strtotime($row['start_date_time']);
					$end_date_time = strtotime($row['end_date_time']);
					if ($time >$start_date_time && $time <$end_date_time && $row["status"]=="1"){$shactive="redsch";}
					echo '<div class="circle '. $shactive.'"> <i class="fa fa-paper-plane"></i></div>
                     </span></a>

					 <a style="color: #333; cursor: pointer; text-decoration: none;" data-toggle="collapse" data-parent="#accordion" href="#collapse'.$row['id'].'">
                     <div class="header">
                     <div class="text-info">&nbsp;&nbsp;&nbsp;&nbsp;From: '.date('d-m-Y  H:i', $start_date_time).' </div> 
					 <div class="text-info">&nbsp;&nbsp;&nbsp;&nbsp;Until:&nbsp; '.date('d-m-Y  H:i', $end_date_time).'</div></a>

	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<a href="javascript:delete_holidays('.$row["id"].');"><button class="btn btn-danger btn-xs"><span class="glyphicon glyphicon-trash"></span></button> </a>
	<a href="holidays_edit.php?id='.$row["id"].'" class="btn btn-default btn-xs login"><span class="ionicons ion-edit"></span></a>
    </div></li>';
	
	//end of while loop
} ?>
</ul>

                        </div>
                        <!-- /.panel-body -->
						<div class="panel-footer">
<?php 
ShowWeather($conn);
?>
                            <div class="pull-right">
                                <div class="btn-group">
-
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
<?php if(isset($conn)) { $conn->close();} ?>
