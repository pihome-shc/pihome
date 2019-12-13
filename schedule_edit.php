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

if(isset($_GET['hol_id'])) {
        $holidays_id = $_GET['id'];
        $return_url = "holidays.php";
} else {
        $holidays_id = NULL;
        $return_url = "schedule.php";
}
if(isset($_GET['id'])) {
	$time_id = $_GET['id'];
}else {
	redirect_to(".$return_url");
}

	//PHP: Bitwise operator
	//http://php.net/manual/en/language.operators.bitwise.php
	//https://www.w3resource.com/php/operators/bitwise-operators.php
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
          $mask =  $mask | (1 << 6);
        }

        $nickname = $_POST['nickname'];
	$start_time = $_POST['start_time'];
	$end_time = $_POST['end_time'];

	$query = "UPDATE schedule_daily_time SET sync = '0',  status = '{$sc_en}', start = '{$start_time}', end = '{$end_time}', WeekDays = '{$mask}', nickname='{$nickname}' WHERE id = '{$time_id}' LIMIT 1";
	$result = $conn->query($query);
	$schedule_daily_time_id = mysqli_insert_id($conn);
	if ($result) {
		$message_success = $lang['schedule_time_modify_success'];
		header("Refresh: 3; url=".$return_url);
	} else {
		$error = $lang['schedule_time_modify_error']."<p>".mysqli_error($conn)."</p>";
	}

	foreach($_POST['id'] as $id){
		$id = $_POST['id'][$id];
		$status = isset($_POST['status'][$id]) ? $_POST['status'][$id] : "0";
		$coop = $_POST['coop'][$id];
		$coop = isset($_POST['coop'][$id]) ? $_POST['coop'][$id] : "0";
		$temp=TempToDB($conn,$_POST['temp'][$id]);
		$query = "UPDATE schedule_daily_time_zone SET sync = '0', status = '{$status}', temperature = '" . number_format($temp,1) . "', coop = '{$coop}' WHERE id = '{$id}' LIMIT 1";
		$zoneresults = $conn->query($query);
		if ($zoneresults) {
			//$message_success .= " & ".$lang['schedule_temp_modify_success'];
			//header("Refresh: 3; url=".$return_url);
		} else {
			$error = "<p>". $lang['schedule_temp_modify_success'].mysqli_error($conn)."</p>";
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
                            <i class="fa fa-clock-o fa-fw"></i> <?php echo $lang['schedule_edit']; ?>
						<div class="pull-right"> <div class="btn-group"><?php echo date("H:i"); ?></div> </div>
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
<form data-toggle="validator" role="form" method="post" action="<?php $_SERVER['PHP_SELF'];?>" id="form-join">
<?php
$query = "SELECT * FROM schedule_daily_time WHERE id = {$time_id}";
$results = $conn->query($query);
$time_row = mysqli_fetch_assoc($results);
?>
    <div class="checkbox checkbox-default checkbox-circle">
    <input id="checkbox0" class="styled" type="checkbox" name="sc_en" value="1" <?php $check = ($time_row['status'] == 1) ? 'checked' : ''; echo $check; ?>>
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
        <input class="form-control input-sm" type="text" id="nickname" name="nickname" value="<?php echo $time_row['nickname']; ?>" placeholder="Nickname">
        <div class="help-block with-errors">
        </div>
    </div>

	<div class="form-group" class="control-label"><label><?php echo $lang['start_time']; ?></label>
	<input class="form-control input-sm" type="time" id="start_time" name="start_time" value="<?php echo $time_row["start"];?>" placeholder="Start Time" required>
    <div class="help-block with-errors"></div></div>

	<div class="form-group" class="control-label"><label><?php echo $lang['end_time']; ?></label>
	<input class="form-control input-sm" type="time" id="end_time" name="end_time" value="<?php echo $time_row["end"];?>" placeholder="End Time" required>
    <div class="help-block with-errors"></div></div>
<?php
$query = "select * from schedule_daily_time_zone_view where time_id = {$time_id}";
$results = $conn->query($query);
while ($row = mysqli_fetch_assoc($results)) {
?>
	<hr>
	<input type="hidden" name="id[<?php echo $row["tz_id"];?>]" value="<?php echo $row["tz_id"];?>">

	<div class="checkbox checkbox-default  checkbox-circle">
    <input id="checkbox<?php echo $row["tz_id"];?>" class="styled" type="checkbox" name="status[<?php echo $row["tz_id"];?>]" value="1" <?php $check = ($row['tz_status'] == 1) ? 'checked' : ''; echo $check; ?> onclick="$('#<?php echo $row["tz_id"];?>').toggle();">
    <label for="checkbox<?php echo $row["tz_id"];?>"><?php echo $row["zone_name"];?></label>
    <div class="help-block with-errors"></div></div>


<?php
if($row['tz_status'] == 1){
	echo '<div id="'.$row["tz_id"].'"><div class="form-group" class="control-label">';
}else{
	echo '<div id="'.$row["tz_id"].'" style="display:none !important;"><div class="form-group" class="control-label">';
	}
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
    <input id="coop<?php echo $row["tz_id"];?>" class="styled" type="checkbox" name="coop[<?php echo $row["tz_id"];?>]" value="1" <?php $check = ($row['coop'] == 1) ? 'checked' : ''; echo $check; ?> >
    <label for="coop<?php echo $row["tz_id"];?>">Coop Start</label> <i class="glyphicon glyphicon-leaf green"></i>
	<i class="fa fa-info-circle fa-lg text-info" data-container="body" data-toggle="popover" data-placement="right" data-content="<?php echo $lang['schedule_coop_help']; ?>"></i>
    <div class="help-block with-errors"></div></div>
    
	<div class="slidecontainer">
		<h4><?php echo $lang['temperature']; ?>: <span id="val<?php echo $row["zone_id"];?>"></span>&deg;</h4><br>
		<input type="range" min="<?php echo $min; ?>" max="<?php echo $max; ?>" step="0.5" value="<?php echo DispTemp($conn, $row['temperature']) ?>" class="slider" id="bb<?php echo $row["zone_id"];?>" name="temp[<?php echo $row["tz_id"];?>]">
	</div>
    </div></div>

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
