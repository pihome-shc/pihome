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
if(isset($_GET['id'])) {
	$id = $_GET['id'];
} else {
	$id = 0;
}
//Form submit
if (isset($_POST['submit'])) {
        $holidays_enable = isset($_POST['holidays_enable']) ? $_POST['holidays_enable'] : "0";
        $start_date_time = $_POST['start_date_time'];
        $end_date_time = $_POST['end_date_time'];

		//Add or Edit Holiday record in Holidays Table
		$query = "INSERT INTO holidays(id, `sync`, `purge`, status, start_date_time, end_date_time) VALUES ('{$id}', '0', '0', '{$holidays_enable}', '{$start_date_time}','{$end_date_time}') ON DUPLICATE KEY UPDATE sync = VALUES(sync), status = VALUES(status), start_date_time = VALUES(start_date_time), end_date_time = VALUES(end_date_time);";
        $result = $conn->query($query);
        if ($result) {
			if ($id==0){
				$message_success = $lang['holidays_add_success'];
			}else{
				$message_success = $lang['holidays_modify_success'];
			}
			header("Refresh: 3; url=holidays.php");
        } else {
			if ($id==0){
				$error = $lang['holidays_add_error']."<p>".mysqli_error($conn)."</p>";
			}else{
				$error = $lang['holidays_modify_error']."<p>".mysqli_error($conn)."</p>";
			}
        }
 }
?>

<!-- ### Visible Page ### -->
<?php include("header.php"); ?>
<?php include_once("notice.php"); ?>

<!-- Don't display form after submit -->
<?php if (!(isset($_POST['submit']))) { ?>

<!-- If the request is to EDIT, retrieve selected items from DB   -->
<?php if ($id != 0) {
	$query = "SELECT * FROM holidays WHERE id = {$id}";
	$results = $conn->query($query);
	$holidays_row = mysqli_fetch_assoc($results);
}
?>
<!-- Title (e.g. Add or Edit Holiday) -->	
<div id="page-wrapper">
	<br>
	<div class="row">
		<div class="col-lg-12">
			<div class="panel panel-primary">
				<div class="panel-heading">
					<i class="fa fa-paper-plane fa-1x"></i> <?php echo $lang['holidays_add']; ?>    
					<div class="pull-right"> <div class="btn-group"><?php echo date("H:i"); ?></div> </div>
				</div>
				<!-- /.panel-heading -->
				<div class="panel-body">

					<form data-toggle="validator" role="form" method="post" action="<?php $_SERVER['PHP_SELF'];?>" id="form-join">

						<!-- Enable Holiday -->
						<div class="checkbox checkbox-default checkbox-circle">
						<input id="checkbox0" class="styled" type="checkbox" name="holidays_enable" value="1" <?php $checked = ($holidays_row['status'] == 1) ? 'checked' : ''; echo $checked; ?>>
						<label for="checkbox0"> <?php echo $lang['holidays_enable']; ?></label></div>

						<!-- Departure DateTime -->
						<div class="form-group input-append date form_datetime" class="control-label"><label><i class="fa fa-paper-plane fa-1x"></i> <?php echo $lang['holidays_departure']; ?></label>
						<input class="form-control input-sm" type="text" id="start_date_time" name="start_date_time" value="<?php echo $holidays_row['start_date_time']; ?>" placeholder="Holiday Start Date & Time ">
						<span class="add-on"><i class="icon-th"></i></span>
						<div class="help-block with-errors"></div></div>
						
						<!-- Return DateTime -->
						<div class="form-group input-append date form_datetime" class="control-label"><label> <i class="fa fa-home fa-fw fa-1x"></i> <?php echo $lang['holidays_return']; ?></label>
						<input class="form-control input-sm" type="text" id="end_date_time" name="end_date_time" value="<?php echo $holidays_row['end_date_time']; ?>" placeholder="Holiday End Date & Time ">
						<span class="add-on"><i class="icon-th"></i></span>
						<div class="help-block with-errors"></div></div>

						<!-- Buttons -->
						<a href="holidays.php"><button type="button" class="btn btn-primary btn-sm" ><?php echo $lang['cancel']; ?></button></a>
						<input type="submit" name="submit" value="<?php echo $lang['submit']; ?>" class="btn btn-default btn-sm login">
					</form>
				</div>
				<!-- /.panel-body -->
				<div class="panel-footer">
					<?php 
					ShowWeather($conn);
					?>
				</div>
			</div>
		</div>
		<!-- /.col-lg-4 -->
	</div>
	<!-- /.row -->
</div>
<!-- /#page-wrapper -->
<?php } ?>
<?php include("footer.php"); ?>
