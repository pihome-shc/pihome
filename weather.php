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
<?php include("header.php");  ?>
<?php  if(isset($message_success)) { echo "<div class=\"alert alert-success alert-dismissable\"> <button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-hidden=\"true\">&times;</button>" . $message_success . "</div>" ;}  ?>
<?php  if(isset($error)) { echo "<div class=\"alert alert-danger alert-dismissable\"> <button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-hidden=\"true\">&times;</button>" . $error . "</div>" ;}  ?>	
<?php  if(isset($info_message)) { echo "<div class=\"alert alert-info alert-dismissable\"> <button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-hidden=\"true\">&times;</button><span class=\"glyphicon glyphicon-info-sign\" data-notify=\"icon\"></span> " . $info_message . "</div>" ;}  ?>
        <div id="page-wrapper">
<br>
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <i class="fa fa-cog fa-fw"></i>   <?php echo $lang['weather_outside']; ?> <?php echo $weather['temp_celsius'] ;?>&deg;    
						<div class="pull-right"> <div class="btn-group"><?php echo date("H:i"); ?></div> </div>
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
<?php
echo '<div class="list-group">';
$weather_api = file_get_contents('weather_5days.json');
$weather_data = json_decode($weather_api, true);
//echo '<pre>' . print_r($weather_data, true) . '</pre>';
foreach($weather_data['list'] as $day => $value) {
	//date('H:i', $weather['sunrise'])

//echo date("D H:i", strtotime($value['dt_txt'])); 
echo '<a href="#" class="list-group-item">'
.date("D H:i", strtotime($value['dt_txt'])).
'<img border="0" width="28" height="28" src="images/'.$value['weather'][0]['icon'].'.png">'
.$value['weather'][0]['main']." - " .$value['weather'][0]['description'].
'<span class="pull-right text-muted small"><em>'

.round($value['main']['temp_min'],0)."&deg; - ".round($value['main']['temp_max'],0).

'&deg;</em></span></a>';
}
?>
</div>
                        </div>
                        <!-- /.panel-body -->
						<div class="panel-footer">
                            <?php echo $lang['schedule_next']; ?>: 
                        </div>
                    </div>
                </div>
                <!-- /.col-lg-4 -->
            </div>
            <!-- /.row -->
        </div>
        <!-- /#page-wrapper -->
<?php include("footer.php");  ?> 