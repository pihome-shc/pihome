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
	$sc_en = isset($_POST['sc_en']) ? $_POST['sc_en'] : "0";
	$start_time = $_POST['start_time'];
	$end_time = $_POST['end_time'];
	$query = "UPDATE schedule_night_climate_time SET sync = '0', status = '{$sc_en}', start_time = '{$start_time}', end_time = '{$end_time}', WeekDays = '{$mask}' where id = 1;";
	$timeresults = $conn->query($query);
	if ($timeresults) {
        	$message_success = "<p>".$lang['night_climate_time_success']."</p>";
    		header("Refresh: 3; url=home.php");
    	} else {
        	$error = "<p>".$lang['night_climate_error']."</p><p>".mysqli_error($conn). "</p>";        
    	}

	foreach($_POST['id'] as $id){
		$id = $_POST['id'][$id];
		$status = isset($_POST['status'][$id]) ? $_POST['status'][$id] : "0";
		//$status = $_POST['status'][$id];
		$min =TempToDB($conn,$_POST['min'][$id]);
		$max =TempToDB($conn,$_POST['max'][$id]);
		$query = "UPDATE schedule_night_climat_zone SET sync = '0', status='$status', min_temperature='" . number_format($min,1) . "', max_temperature='" . number_format($max,1) . "' WHERE id='$id'";
		$zoneresults = $conn->query($query);
		if ($zoneresults) {
            		$message_success .= "<p>".$lang['night_climate_temp_success']."</p>";
        	} else {
            		$error .= "<p>".$lang['night_climate_error']."</p><p>".mysqli_error($conn). "</p>";        
        	}
	}
} ?>
<?php include("header.php");  ?>
<?php include_once("notice.php"); ?>
 <div id="page-wrapper">
<br>
            <div class="row">
                <div class="col-lg-12">
                   <div class="panel panel-primary">
                        <div class="panel-heading">
                            <i class="fa fa-bed fa-1x"></i> <?php echo $lang['night_climate']; ?>
						<div class="pull-right"> <div class="btn-group"><?php echo date("H:i"); ?></div> </div>
                        </div>
                        <!-- /.panel-heading -->
 <div class="panel-body">
                <form data-toggle="validator" role="form" method="post" action="<?php $_SERVER['PHP_SELF'];?>" id="form-join">
<?php
				$query = "SELECT * FROM schedule_night_climate_time WHERE id = 1;";
				$results = $conn->query($query);	
				$snct = mysqli_fetch_assoc($results);
?>
            	<!-- Enable Schedule -->
                <div class="checkbox checkbox-default checkbox-circle">
                <input id="checkbox0" class="styled" type="checkbox" name="sc_en" value="1" <?php $check = ($snct['status'] == 1) ? 'checked' : ''; echo $check; ?>>
                <label for="checkbox0"> <?php echo $lang['night_climate_enable']; ?></label></div>
		<div class="checkbox checkbox-default checkbox-circle">

                <!-- Day Selector -->
                <div class="row">
                <div class="col-xs-3"><div class="checkbox checkbox-default checkbox-circle">
                <input id="checkbox1" class="styled" type="checkbox" name="Sunday_en" value="1" <?php $check = (($snct['WeekDays'] & 1) > 0) ? 'checked' : ''; echo $check; ?>>
                <label for="checkbox1"> <?php echo $lang['sun']; ?></label></div></div>
                <div class="col-xs-3"><div class="checkbox checkbox-default checkbox-circle">
                <input id="checkbox2" class="styled" type="checkbox" name="Monday_en" value="1" <?php $check = (($snct['WeekDays'] & 2) > 0) ? 'checked' : ''; echo $check; ?>>
                <label for="checkbox2"> <?php echo $lang['mon']; ?></label></div></div>

                <div class="col-xs-3"><div class="checkbox checkbox-default checkbox-circle">
                <input id="checkbox3" class="styled" type="checkbox" name="Tuesday_en" value="1" <?php $check = (($snct['WeekDays'] & 4) > 0) ? 'checked' : ''; echo $check; ?>>
                <label for="checkbox3"> <?php echo $lang['tue']; ?></label></div></div>

                <div class="col-xs-3"><div class="checkbox checkbox-default checkbox-circle">
                <input id="checkbox4" class="styled" type="checkbox" name="Wednesday_en" value="1" <?php $check = (($snct['WeekDays'] & 8) > 0) ? 'checked' : ''; echo $check; ?>>
                <label for="checkbox4"> <?php echo $lang['wed']; ?></label></div></div>

                <div class="col-xs-3"><div class="checkbox checkbox-default checkbox-circle">
                <input id="checkbox5" class="styled" type="checkbox" name="Thursday_en" value="1" <?php $check = (($snct['WeekDays'] & 16) > 0) ? 'checked' : ''; echo $check; ?>>
                <label for="checkbox5"> <?php echo $lang['thu']; ?></label></div></div>

                <div class="col-xs-3"><div class="checkbox checkbox-default checkbox-circle">
                <input id="checkbox6" class="styled" type="checkbox" name="Friday_en" value="1" <?php $check = (($snct['WeekDays'] & 32) > 0) ? 'checked' : ''; echo $check; ?>>
                <label for="checkbox6"> <?php echo $lang['fri']; ?></label></div></div>

                <div class="col-xs-3"><div class="checkbox checkbox-default checkbox-circle">
                <input id="checkbox7" class="styled" type="checkbox" name="Saturday_en" value="1" <?php $check = (($snct['WeekDays'] & 64) > 0) ? 'checked' : ''; echo $check; ?>>
                <label for="checkbox7"> <?php echo $lang['sat']; ?></label></div></div>
                </div>

		<!-- Start Time -->
		<div class="form-group" class="control-label"><label><?php echo $lang['start_time']; ?></label>
		<input class="form-control input-sm" type="time" id="start_time" name="start_time" value="<?php if(isset($_POST['start_time'])) { echo $_POST['start_time']; }else{echo $snct['start_time'];} ?>" required>
                <div class="help-block with-errors"></div></div>

		<!-- End Time -->
		<div class="form-group" class="control-label"><label><?php echo $lang['end_time']; ?></label>
		<input class="form-control input-sm" type="time" id="end_time" name="end_time" value="<?php if(isset($_POST['end_time'])) { echo $_POST['end_time']; }else{echo $snct['end_time'];} ?>" required>
                <div class="help-block with-errors"></div></div>				
<?php
$zquery = "
SELECT sncz.id, sncz.status, sncz.schedule_night_climate_id, sncz.zone_id, zone.index_id, zone.name as zone_name, zone.status as zone_status, sncz.min_temperature, sncz.max_temperature 
FROM schedule_night_climat_zone sncz 
join zone on sncz.zone_id = zone.id
where zone.status = 1 order by zone.index_id;";
				$zoneresults = $conn->query($zquery);
				while ($sncz = mysqli_fetch_assoc($zoneresults)) {
?>
				<input type="hidden" name="id[<?php echo $sncz["id"];?>]" value="<?php echo $sncz["id"];?>">

				<div class="checkbox checkbox-default  checkbox-circle">
				<input id="checkbox<?php echo $sncz["id"];?>" class="styled" type="checkbox" name="status[<?php echo $sncz["id"];?>]" value="1" <?php $check = ($sncz['status'] == 1) ? 'checked' : ''; echo $check; ?> onclick="$('#<?php echo $sncz["id"];?>').toggle();">
                <label for="checkbox<?php echo $sncz["id"];?>"><?php echo $sncz["zone_name"];?></label>
                <div class="help-block with-errors"></div></div>
				
				<?php 
				if($sncz['status'] == 1){echo '<div id="'.$sncz["id"].'"><div class="form-group" class="control-label">';
					}else{
					echo '<div id="'.$sncz["id"].'" style="display:none !important;"><div class="form-group" class="control-label">';}
				?>
				<label><?php echo $lang['min_temperature']; ?></label>
				<select class="form-control input-sm" type="number" id="<?php echo $sncz["id"];?>" name="min[<?php echo $sncz["id"];?>]" placeholder="Zone Temperature" >
				<?php 
    $c_f = settings($conn, 'c_f');
    if($c_f==1 || $c_f=='1') {
        for($t=64;$t<=74;$t++)
        {
            echo '<option value="' . $t . '" ' . (DispTemp($conn, $sncz['min_temperature'])==$t ? 'selected' : '') . '>' . $t . '</option>';
        }
    }
    else {
        for($t=18;$t<=23;$t++)
        {
            echo '<option value="' . $t . '" ' . ($sncz['min_temperature']==$t ? 'selected' : '') . '>' . $t . '</option>';
        }
    }
?>	
				</select>
                <div class="help-block with-errors"></div>
				
				<label><?php echo $lang['max_temperature']; ?></label>
				<select class="form-control input-sm" type="number" id="<?php echo $sncz["id"];?>" name="max[<?php echo $sncz["id"];?>]" placeholder="Zone Temperature" >
<?php 
    $c_f = settings($conn, 'c_f');
    if($c_f==1 || $c_f=='1') {
        for($t=64;$t<=74;$t++)
        {
            echo '<option value="' . $t . '" ' . (DispTemp($conn, $sncz['max_temperature'])==$t ? 'selected' : '') . '>' . $t . '</option>';
        }
    }
    else {
        for($t=18;$t<=23;$t++)
        {
            echo '<option value="' . $t . '" ' . ($sncz['max_temperature']==$t ? 'selected' : '') . '>' . $t . '</option>';
        }
    }
?>	
				</select>
                <div class="help-block with-errors"></div>
				
				</div></div>
				<?php }?>			
                <input type="submit" name="submit" value="<?php echo $lang['submit']; ?>" class="btn btn-default btn-sm">
				<a href="home.php"><button type="button" class="btn btn-primary btn-sm"><?php echo $lang['cancel']; ?></button></a>
				</form>
                        </div>
                        <!-- /.panel-body -->
						<div class="panel-footer">
<?php 
ShowWeather($conn);
?>
                            <div class="pull-right">
                                <div class="btn-group">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.col-lg-4 -->
            </div>
            <!-- /.row -->
        </div>
        <!-- /#page-wrapper -->
<?php include("footer.php");  ?>
