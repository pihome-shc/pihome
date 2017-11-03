<?php require_once("st_inc/session.php"); ?>
<?php confirm_logged_in(); ?>
<?php require_once("st_inc/connection.php"); ?>
<?php require_once("st_inc/functions.php"); ?>
<?php 

if (isset($_POST['submit'])) {
	$holidays_enable = isset($_POST['holidays_enable']) ? $_POST['holidays_enable'] : "0";

	$start_date_time = $_POST['start_date_time'];
	$stime = date('Y-m-d H:i:s',strtotime($start_date_time));
	
	$end_date_time = $_POST['end_date_time'];
	$etime = date('Y-m-d H:i:s',strtotime($end_date_time));


	$sql_device_insert= "INSERT INTO holidays(active, start_date_time, end_date_time)values('{$holidays_enable}', '{$start_date_time}', '{$end_date_time}')";
	$result = mysql_query($sql_device_insert);
	
	if ($result) {
		// Success!
		$message_success = "All Done";
	} else {
				// Display error message.
		$error .= "<p>" . mysql_error() . "</p>";
	}				
 }
 
 	if(isset($_GET['id'])) {
		$id = $_GET['id'];
		
		$query = "SELECT * FROM holidays WHERE id = {$id} LIMIT 1";
		$get_product = mysql_query($query, $connection);
		confirm_query($get_product);
		$found_product = mysql_fetch_array($get_product);
		
		if (!$found_product) {
		$error_message = "No record found for database record number " . $id . mysql_error();
		} else {		
		
		$query = "DELETE FROM holidays WHERE id = {$id} LIMIT 1";
		$result = mysql_query($query, $connection);
		confirm_query($result);
		
			if ($result) {
				// Success!
				$message_success = "Record Delete Successfully";
			} else {
				$error = "<p>Something went wrong! please try again...</p>";
				$error .= "<p>" . mysql_error() . "</p>";
			}
		}
	}
 
?>
<?php include("header.php");  ?>
<?php  if(isset($error)) { echo "<div class=\"alert alert-danger alert-dismissable\"> <button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-hidden=\"true\">&times;</button>" . $error . "</div>" ;}  ?>

 <div id="page-wrapper">
<br>
            <div class="row">
                <div class="col-lg-12">

<div id="holidayslist"></div>


                <!-- /.col-lg-4 -->
            </div>
            <!-- /.row -->
        </div>
        <!-- /#page-wrapper -->
		
<?php include("footer.php");  ?>