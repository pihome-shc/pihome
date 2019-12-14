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
        $holidays_id = $_GET['id'];
        $return_url = "holidays.php";
} else {
        $holidays_id = "NULL";
        $return_url = "schedule.php";
}

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
	$nickname = $_POST['nickname'];
	$query = "INSERT INTO schedule_daily_time(sync, status, start, end, WeekDays, nickname) VALUES ('0', '{$sc_en}', '{$start_time}','{$end_time}','{$mask}', '{$nickname}')";
	$result = $conn->query($query);
	$schedule_daily_time_id = mysqli_insert_id($conn);
	
	if ($result) {
		$message_success = "Schedule Time Added Successfully!!!";
		header("Refresh: 3; url=".$return_url);
	} else {
		$error = $lang['schedule_time_add_error']." <p>" . mysqli_error($conn) . "</p>";
	}
	foreach($_POST['id'] as $id){
		$id = $_POST['id'][$id];
		$status = isset($_POST['status'][$id]) ? $_POST['status'][$id] : "0";
		$coop = isset($_POST['coop'][$id]) ? $_POST['coop'][$id] : "0";
		//$status = $_POST['status'][$id];
		$temp=TempToDB($conn,$_POST['temp'][$id]);
		$query = "INSERT INTO schedule_daily_time_zone(sync, `status`, schedule_daily_time_id, zone_id, temperature, holidays_id, coop) VALUES ('0', '{$status}', '{$schedule_daily_time_id}','{$id}','".number_format($temp,1)."',{$holidays_id},{$coop}); ";
		$zoneresults = $conn->query($query);
		//echo $query;
		if ($zoneresults) {
			$message_success = "<p>".$lang['zone_record_success']."</p>";
		} else {
			$error = "<p>".$lang['zone_record_fail']." </p> <p>" .mysqli_error($conn). "</p>";
		}
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
                            <i class="fa fa-clock-o fa-fw"></i> <?php echo $lang['schedule_add']; ?>
						<div class="pull-right"> <div class="btn-group"><?php echo date("H:i"); ?></div> </div>
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">

                <form data-toggle="validator" role="form" method="post" action="<?php $_SERVER['PHP_SELF'];?>" id="form-join">

			<div class="checkbox checkbox-default checkbox-circle">
			<input id="checkbox0" class="styled" type="checkbox" name="sc_en" value="1" <?php if(isset($_POST['sc_en'])){ echo "checked";}?>>
			<label for="checkbox0"> <?php echo $lang['schedule_enable']; ?></label></div>

			<div class="row">
			<div class="col-xs-3"><div class="checkbox checkbox-default checkbox-circle">
    		<input id="checkbox1" class="styled" type="checkbox" name="Sunday_en" value="1" <?php $check = (($time_row['WeekDays'] & 1) > 0) ? 'checked' : ''; echo $check; ?>>
    		<label for="checkbox1"> <?php echo $lang['sun']; ?></label></div></div>

			<div class="col-xs-3"><div class="checkbox checkbox-default checkbox-circle">
    		<input id="checkbox2" class="styled" type="checkbox" name="Monday_en" value="1" <?php $check = (($time_row['WeekDays'] & 2) > 0) ? 'checked' : ''; echo $check; ?>>
    		<label for="checkbox2"> <?php echo $lang['mon']; ?></label></div></div>

        	<div class="col-xs-3"><div class="checkbox checkbox-default checkbox-circle">
    		<input id="checkbox3" class="styled" type="checkbox" name="Tuesday_en" value="1" <?php $check = (($time_row['WeekDays'] & 4) > 0) ? 'checked' : ''; echo $check; ?>>
    		<label for="checkbox3"> <?php echo $lang['tue']; ?></label></div></div>

			<div class="col-xs-3"><div class="checkbox checkbox-default checkbox-circle">
    		<input id="checkbox4" class="styled" type="checkbox" name="Wednesday_en" value="1" <?php $check = (($time_row['WeekDays'] & 8) > 0) ? 'checked' : ''; echo $check; ?>>
    		<label for="checkbox4"> <?php echo $lang['wed']; ?></label></div></div>

        	<div class="col-xs-3"><div class="checkbox checkbox-default checkbox-circle">
    		<input id="checkbox5" class="styled" type="checkbox" name="Thursday_en" value="1" <?php $check = (($time_row['WeekDays'] & 16) > 0) ? 'checked' : ''; echo $check; ?>>
    		<label for="checkbox5"> <?php echo $lang['thu']; ?></label></div></div>

			<div class="col-xs-3"><div class="checkbox checkbox-default checkbox-circle">
    		<input id="checkbox6" class="styled" type="checkbox" name="Friday_en" value="1" <?php $check = (($time_row['WeekDays'] & 32) > 0) ? 'checked' : ''; echo $check; ?>>
    		<label for="checkbox6"> <?php echo $lang['fri']; ?></label></div></div>

			<div class="col-xs-3"><div class="checkbox checkbox-default checkbox-circle">
    		<input id="checkbox7" class="styled" type="checkbox" name="Saturday_en" value="1" <?php $check = (($time_row['WeekDays'] & 64) > 0) ? 'checked' : ''; echo $check; ?>>
    		<label for="checkbox7"> <?php echo $lang['sat']; ?></label></div></div>
			</div>

                    <div class="form-group" class="control-label">
                        <label><?php echo $lang['nickname']; ?></label>
                        <input class="form-control input-sm" type="text" id="nickname" name="nickname" value="<?php if(isset($_POST['nickname'])) { echo $_POST['nickname']; } ?>" placeholder="Nickname">
                        <div class="help-block with-errors">
                        </div>
                    </div>

				<div class="form-group" class="control-label"><label><?php echo $lang['start_time']; ?></label>
				<input class="form-control input-sm" type="time" id="start_time" name="start_time" value="<?php if(isset($_POST['start_time'])) { echo $_POST['start_time']; } ?>" placeholder="Start Time" required>
                <div class="help-block with-errors"></div></div>

				<div class="form-group" class="control-label"><label><?php echo $lang['end_time']; ?></label>
				<input class="form-control input-sm" type="time" id="end_time" name="end_time" value="<?php if(isset($_POST['end_time'])) { echo $_POST['end_time']; } ?>" placeholder="End Time" required>
                <div class="help-block with-errors"></div></div>
<?php
$query = "select * from zone where status = 1 AND `purge`= 0 order by index_id asc;";
$results = $conn->query($query);
while ($row = mysqli_fetch_assoc($results)) {
?>
	<hr>
	<input type="hidden" name="id[<?php echo $row["id"];?>]" value="<?php echo $row["id"];?>">

	<div class="checkbox checkbox-default  checkbox-circle">
    <input id="checkbox<?php echo $row["id"];?>" class="styled" type="checkbox" name="status[<?php echo $row["id"];?>]" value="1" onclick="$('#<?php echo $row["id"];?>').toggle();">
    <label for="checkbox<?php echo $row["id"];?>"><?php echo $row["name"];?></label>
    <div class="help-block with-errors"></div></div>

	<div id="<?php echo $row["id"];?>" style="display:none !important;">
	<?php
	//0=C, 1=F
	$c_f = settings($conn, 'c_f');
    if(($c_f==1 || $c_f=='1') AND ($row["type"]=='Heating')) {
		$min = 50;
		$max = 85;
	}elseif (($c_f==1 || $c_f=='1') AND ($row["type"]=='Water')) {
		$min = 50;
		$max = 170;
	}elseif (($c_f==0 || $c_f=='0') AND ($row["type"]=='Heating')) {
		$min = 10;
		$max = 30;
	}elseif (($c_f==0 || $c_f=='0') AND ($row["type"]=='Water')) {
		$min = 10;
		$max = 80;
	}
	?>
	<div class="checkbox checkbox-default  checkbox-circle">
    <input id="coop<?php echo $row["id"];?>" class="styled" type="checkbox" name="coop[<?php echo $row["id"];?>]" value="1" >
    <label for="coop<?php echo $row["id"];?>">Coop Start</label>
	<i class="glyphicon glyphicon-leaf green"></i> 
	<i class="fa fa-info-circle fa-lg text-info" data-container="body" data-toggle="popover" data-placement="right" data-content="<?php echo $lang['schedule_coop_help']; ?>"></i>
    <div class="help-block with-errors"></div></div>
    
	<div class="slidecontainer">
		<h4><?php echo $lang['temperature']; ?>: <span id="val<?php echo $row["id"];?>"></span>&deg;</h4><br>
		<input type="range" min="<?php echo $min; ?>" max="<?php echo $max; ?>" step="0.5" value="15.0" class="slider" id="bb<?php echo $row["id"];?>" name="temp[<?php echo $row["id"];?>]">
	</div>
	</div>
<?php }?>
                <br>
				<a href="<?php echo $return_url ?>"><button type="button" class="btn btn-primary btn-sm" ><?php echo $lang['cancel']; ?></button></a>
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
		<?php include("footer.php"); ?>
