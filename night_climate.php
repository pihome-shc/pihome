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
	$start_time = $_POST['start_time'];
	$end_time = $_POST['end_time'];
	$query = "UPDATE schedule_night_climate_time SET sync = '0', status = '{$sc_en}', start_time = '{$start_time}', end_time = '{$end_time}' where id = 1;";
	$timeresults = $conn->query($query);
	if ($timeresults) {
        $message_success = "<p>Night Climate Time Changed Successfully!!!</p>";
    	header("Refresh: 3; url=home.php");
    } else {
        $error = "<p>Night Climate Changes Failed with error: </p><p>".mysqli_error($conn). "</p>";        
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
            $message_success .= "<p>Night Climate Temp Changed Successfully!!!</p>";
        } else {
            $error .= "<p>Night Climate Changes Failed with error: </p><p>".mysqli_error($conn). "</p>";        
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
                            <i class="fa fa-bed fa-1x"></i> Night Climate
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
				<div class="checkbox checkbox-default checkbox-circle">
                <input id="checkbox0" class="styled" type="checkbox" name="sc_en" value="1" <?php $check = ($snct['status'] == 1) ? 'checked' : ''; echo $check; ?>>
                <label for="checkbox0"> Enable Night Climate </label>
                <div class="help-block with-errors"></div></div>

				<div class="form-group" class="control-label"><label>Start Time</label>
				<input class="form-control input-sm" type="time" id="start_time" name="start_time" value="<?php if(isset($_POST['start_time'])) { echo $_POST['start_time']; }else{echo $snct['start_time'];} ?>" required>
                <div class="help-block with-errors"></div></div>
				
				<div class="form-group" class="control-label"><label>End Time</label>
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
				<label>Minimum Temperature</label>
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
				
				<label>Maximum Temperature</label>
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
                <input type="submit" name="submit" value="Submit" class="btn btn-default btn-sm">
				<a href="home.php"><button type="button" class="btn btn-primary btn-sm">Cancel</button></a>
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