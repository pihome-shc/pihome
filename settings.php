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
require_once("st_inc/session.php"); 
confirm_logged_in();
require_once(__DIR__.'/st_inc/connection.php');
require_once(__DIR__.'/st_inc/functions.php');

if(isset($_GET["frost"])) {
	$frost_temp = $_GET['frost'];
	$info_message = "Frost Protection Temperature Changed to $frost_temp&deg;";
}
if(isset($_GET["reboot"])) {
	$info_message = "Server is Rebooting <small> Please Do not Refresh... </small>";
}
if(isset($_GET["shutdown"])) {
	$info_message = "Server is Shutting down <small> Please Do not Refresh... </small>";
}

if(isset($_GET["del_user"])) {
	$info_message = "User account removed successfully...</small>";
}

//backup process start
 if(isset($_GET['db_backup'])) {
$info_message = "Data Base Backup Request Started, This process may take some time complete..." ;
include("start_backup.php");
 }


	//query to frost protection temperature 
	$query = "SELECT * FROM frost_protection LIMIT 1 ";
	$result = mysql_query($query, $connection);
	confirm_query($result);
	$frosttemp = mysql_fetch_array($result);
	$frost_temp = $frosttemp['temperature'];
?>
<?php include("header.php");  ?>
<?php  if(isset($message_success)) { echo "<div class=\"alert alert-success alert-dismissable\"> <button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-hidden=\"true\">&times;</button>" . $message_success . "</div>" ;}  ?>
<?php  if(isset($error)) { echo "<div class=\"alert alert-danger alert-dismissable\"> <button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-hidden=\"true\">&times;</button>" . $error . "</div>" ;}  ?>	
<?php  if(isset($info_message)) { echo "<div class=\"alert alert-info alert-dismissable\"> <button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-hidden=\"true\">&times;</button><span class=\"glyphicon glyphicon-info-sign\" data-notify=\"icon\"></span> " . $info_message . "</div>" ;}  ?>
        <div id="page-wrapper">
<br>
            <div class="row">
                <div class="col-lg-12">
                  	<div id="settingslist" >
				   <div class="text-center"><br><br><p>Please wait while system grab latest information from database...</p>
				   <br><br><img src="images/loader.gif">
				   </div>
				   </div>
	
                </div>

                <!-- /.col-lg-4 -->
            </div>
            <!-- /.row -->
        </div>
        <!-- /#page-wrapper -->
		
<?php include("footer.php");  ?>