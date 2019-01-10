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
	$sc_en = isset($_POST['sc_en']) ? $_POST['sc_en'] : "0";
      
		$mask = 0;
        $bit = isset($_POST['Sunday_en']) ? $_POST['Sunday_en'] : "0";
        if ($bit) {
          $mask =  $mask | (1 << 0); }
        else {$mask =  $mask & (0 << 0); }
        $bit = isset($_POST['Monday_en']) ? $_POST['Monday_en'] : "0";
        if ($bit) {
          $mask =  $mask | (1 << 1); }
        $bit = isset($_POST['Tuesday_en']) ? $_POST['Tuesday_en'] : "0";
        if ($bit) {
          $mask =  $mask | (1 << 2); }
        $bit = isset($_POST['Wednesday_en']) ? $_POST['Wednesday_en'] : "0";
        if ($bit) {
          $mask =  $mask | (1 << 3); }
        $bit = isset($_POST['Thursday_en']) ? $_POST['Thursday_en'] : "0";
        if ($bit) {
          $mask =  $mask | (1 << 4); }
        $bit = isset($_POST['Friday_en']) ? $_POST['Friday_en'] : "0";
        if ($bit) {
          $mask =  $mask | (1 << 5); }
        $bit = isset($_POST['Saturday_en']) ? $_POST['Saturday_en'] : "0";
        if ($bit) {
          $mask =  $mask | (1 << 6); }
		  
	$start_time = $_POST['start_time'];
	$end_time = $_POST['end_time'];
	$query = "INSERT INTO schedule_daily_time(sync, status, start, end, WeekDays) VALUES ('0', '{$sc_en}', '{$start_time}','{$end_time}','{$mask}')"; 
	$result = $conn->query($query);
	$schedule_daily_time_id = mysqli_insert_id($conn);
	
	if ($result) {
		$message_success = "Schedule Time Added Successfully!!!";
		header("Refresh: 3; url=schedule.php");
	} else {
		$error = "Schedule Time Addition failed with error: <p>" . mysqli_error($conn) . "</p>";
	}
	foreach($_POST['id'] as $id){
		$id = $_POST['id'][$id];
		$status = isset($_POST['status'][$id]) ? $_POST['status'][$id] : "0";
		$status = $_POST['status'][$id];
		$temp = $_POST['temp'][$id];
		$query = "INSERT INTO schedule_daily_time_zone(sync, status, schedule_daily_time_id, zone_id, temperature) VALUES ('0', '{$status}', '{$schedule_daily_time_id}','{$id}','" . number_format($temp,1) . "')"; 
		$zoneresults = $conn->query($query);
	}
}
?>
<?php include("header.php"); ?>
<?php include_once("notice.php"); ?>
        <div id="page-wrapper">
<br>
            <div class="row">
                <div class="col-lg-12">
				<div class="panel panel-primary">
                        <div class="panel-heading">
                            <i class="fa fa-clock-o fa-fw"></i> Add Schedule 
						<div class="pull-right"> <div class="btn-group"><?php echo date("H:i"); ?></div> </div>
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">

                <form data-toggle="validator" role="form" method="post" action="<?php $_SERVER['PHP_SELF'];?>" id="form-join">

			<div class="checkbox checkbox-default checkbox-circle">
			<input id="checkbox0" class="styled" type="checkbox" name="sc_en" value="1" <?php if(isset($_POST['sc_en'])){ echo "checked";}?>>
			<label for="checkbox0"> Enable Schedule</label></div>

			<div class="row">
			<div class="col-xs-3"><div class="checkbox checkbox-default checkbox-circle">
    		<input id="checkbox1" class="styled" type="checkbox" name="Sunday_en" value="1" <?php $check = (($time_row['WeekDays'] & 1) > 0) ? 'checked' : ''; echo $check; ?>>
    		<label for="checkbox1"> Sun</label></div></div>
        	
			<div class="col-xs-3"><div class="checkbox checkbox-default checkbox-circle">
    		<input id="checkbox2" class="styled" type="checkbox" name="Monday_en" value="1" <?php $check = (($time_row['WeekDays'] & 2) > 0) ? 'checked' : ''; echo $check; ?>>
    		<label for="checkbox2"> Mon</label></div></div>
			
        	<div class="col-xs-3"><div class="checkbox checkbox-default checkbox-circle">
    		<input id="checkbox3" class="styled" type="checkbox" name="Tuesday_en" value="1" <?php $check = (($time_row['WeekDays'] & 4) > 0) ? 'checked' : ''; echo $check; ?>>
    		<label for="checkbox3"> Tue</label></div></div>
        	
			<div class="col-xs-3"><div class="checkbox checkbox-default checkbox-circle">
    		<input id="checkbox4" class="styled" type="checkbox" name="Wednesday_en" value="1" <?php $check = (($time_row['WeekDays'] & 8) > 0) ? 'checked' : ''; echo $check; ?>>
    		<label for="checkbox4"> Wed</label></div></div>
			
        	<div class="col-xs-3"><div class="checkbox checkbox-default checkbox-circle">
    		<input id="checkbox5" class="styled" type="checkbox" name="Thursday_en" value="1" <?php $check = (($time_row['WeekDays'] & 16) > 0) ? 'checked' : ''; echo $check; ?>>
    		<label for="checkbox5"> Thu</label></div></div>
        	
			<div class="col-xs-3"><div class="checkbox checkbox-default checkbox-circle">
    		<input id="checkbox6" class="styled" type="checkbox" name="Friday_en" value="1" <?php $check = (($time_row['WeekDays'] & 32) > 0) ? 'checked' : ''; echo $check; ?>>
    		<label for="checkbox6"> Fri</label></div></div>
        	
			<div class="col-xs-3"><div class="checkbox checkbox-default checkbox-circle">
    		<input id="checkbox7" class="styled" type="checkbox" name="Saturday_en" value="1" <?php $check = (($time_row['WeekDays'] & 64) > 0) ? 'checked' : ''; echo $check; ?>>
    		<label for="checkbox7"> Sat</label></div></div>
			</div>

		
				<div class="form-group" class="control-label"><label>Start Time</label>
				<input class="form-control input-sm" type="time" id="start_time" name="start_time" value="<?php if(isset($_POST['start_time'])) { echo $_POST['start_time']; } ?>" placeholder="Start Time" required>
                <div class="help-block with-errors"></div></div>
				
				<div class="form-group" class="control-label"><label>End Time</label>
				<input class="form-control input-sm" type="time" id="end_time" name="end_time" value="<?php if(isset($_POST['end_time'])) { echo $_POST['end_time']; } ?>" placeholder="End Time" required>
                <div class="help-block with-errors"></div></div>				
<?php 
$query = "select * from zone where status = 1;";
$results = $conn->query($query);	
while ($row = mysqli_fetch_assoc($results)) {
?>

	<input type="hidden" name="id[<?php echo $row["id"];?>]" value="<?php echo $row["id"];?>">

	<div class="checkbox checkbox-default  checkbox-circle">
    <input id="checkbox<?php echo $row["id"];?>" class="styled" type="checkbox" name="status[<?php echo $row["id"];?>]" value="1" onclick="$('#<?php echo $row["id"];?>').toggle();">
    <label for="checkbox<?php echo $row["id"];?>"><?php echo $row["name"];?></label>
    <div class="help-block with-errors"></div></div>

	<div id="<?php echo $row["id"];?>" style="display:none !important;"><div class="form-group" class="control-label">
	<select class="form-control input-sm" type="number" id="<?php echo $row["id"];?>" name="temp[<?php echo $row["id"];?>]" placeholder="Ground Floor Temperature" >
<?php 
    $c_f = settings($conn, 'c_f');
    if($c_f==1 || $c_f=='1') {
        for($t=50;$t<=100;$t+=.5){
            echo '<option value="' . $t . '" >' . $t . '</option>';
        }
    }else {
        for($t=10;$t<=45;$t+=.5){
            echo '<option value="' . $t . '" >' . $t . '</option>';
        }
    }
?>	
	</select>
    <div class="help-block with-errors"></div></div></div>
<?php }?>				
                <a href="schedule.php"><button type="button" class="btn btn-primary btn-sm" >Cancel</button></a>
                <input type="submit" name="submit" value="Submit" class="btn btn-default btn-sm login">
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
		<?php include("footer.php"); ?>