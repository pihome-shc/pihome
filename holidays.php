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

if (isset($_POST['submit'])) {
	$holidays_enable = isset($_POST['holidays_enable']) ? $_POST['holidays_enable'] : "0";

	$start_date_time = $_POST['start_date_time'];
	$stime = date('Y-m-d H:i:s',strtotime($start_date_time));
	
	$end_date_time = $_POST['end_date_time'];
	$etime = date('Y-m-d H:i:s',strtotime($end_date_time));

	$sql_device_insert= "INSERT INTO holidays(active, start_date_time, end_date_time)values('{$holidays_enable}', '{$start_date_time}', '{$end_date_time}')";
	$result = $conn->query($sql_device_insert);
	
	if ($result) {
		// Success!
		$message_success = "All Done";
	} else {
				// Display error message.
		$error .= "<p>" . mysqli_error($conn) . "</p>";
	}				
 }
 	if(isset($_GET['id'])) {
		$id = $_GET['id'];
		$query = "SELECT * FROM holidays WHERE id = {$id} LIMIT 1";
		$get_product = $conn->query($query);
		$found_product = mysqli_fetch_array($get_product);
		if (!$found_product) {
			$error_message = "No record found for database record number " . $id .mysqli_error($conn);
		} else {		
			$query = "DELETE FROM holidays WHERE id = {$id} LIMIT 1";
			$result = $conn->query($query);
			if ($result) {
				// Success!
				$message_success = "Record Delete Successfully";
			} else {
				$error = "<p>Something went wrong! please try again...</p>";
				$error .= "<p>" .mysqli_error($conn). "</p>";
			}
		}
	}
?>
<?php include("header.php");  ?>
<?php include_once("notice.php"); ?>
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