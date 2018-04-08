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
?>
<?php  if(isset($message_success)) { echo '<div class="notice notice-success"><i class="glyphicon glyphicon-ok-circle fa-lg"></i> ' . $message_success . '</div>' ;}  ?>
<?php  if(isset($error_message)) { echo '<div class="notice notice-danger"> <i class="fa fa-exclamation-triangle fa-lg"></i> ' . $error_message . '</div>' ;}  ?>
<?php  if(isset($error)) { echo '<div class="notice notice-danger"><i class="fa fa-exclamation-triangle fa-lg"></i> ' . $error . '</div>' ;} ?>
<?php  if(isset($alert_message)) { echo '<div class="notice notice-warning"><i class="fa fa-exclamation-triangle fa-lg"></i> ' . $alert_message . '</div>' ;}  ?>
<?php  if(isset($info_message)) { echo '<div class="notice notice-info"><span class="glyphicon glyphicon-info-sign" data-notify="icon"></span> ' . $info_message . '</div>' ;}  ?>