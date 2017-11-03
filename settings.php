<?php require_once("st_inc/session.php"); ?>
<?php confirm_logged_in(); ?>
<?php require_once("st_inc/connection.php"); ?>
<?php require_once("st_inc/functions.php"); ?>
<?php 

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