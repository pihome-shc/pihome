<?php require_once("st_inc/session.php"); ?>
<?php confirm_logged_in(); ?>
<?php require_once("st_inc/connection.php"); ?>
<?php require_once("st_inc/functions.php"); ?>
<?php
if (isset($_POST['submit'])) {
	$sc_en = isset($_POST['sc_en']) ? $_POST['sc_en'] : "0";
	$start_time = mysql_prep($_POST['start_time']);
	$end_time = mysql_prep($_POST['end_time']);
	$query = "UPDATE schedule_night_climate_time SET status = '{$sc_en}', start_time = '{$start_time}', end_time = '{$end_time}'";
	$timeresults = mysql_query($query, $connection);
	if (isset($timeresults)) {$message_success = $LANG['record_add_success'];} else {$error = "<p>{$LANG['record_add_failed']}</p>"; $error .= "<p>" . mysql_error() . "</p>";}
	
	foreach($_POST['id'] as $id){
		$id = $_POST['id'][$id];
		$status = isset($_POST['status'][$id]) ? $_POST['status'][$id] : "0";
		//$status = $_POST['status'][$id];
		$min = $_POST['min'][$id];
		$max = $_POST['max'][$id];
		$query = "UPDATE schedule_night_climat_zone SET status='$status', min_temperature='$min', max_temperature='$max' WHERE id='$id'";
		$zoneresults = mysql_query($query, $connection);
	}
	$message_success = "Night Climate Schedule Modified Successfully!!!";
	header("Refresh: 3; url=home.php");
}
?>
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
				$query = "SELECT * FROM schedule_night_climate_time WHERE id = 1";
				$results = mysql_query($query, $connection);	
				$snct = mysql_fetch_assoc($results);
?>

				<div class="checkbox checkbox-default checkbox-circle">
                <input id="checkbox0" class="styled" type="checkbox" name="sc_en" value="1" <?php $check = ($snct['status'] == 1) ? 'checked' : ''; echo $check; ?>>
                <label for="checkbox0"> Enable Night Climate </label>
                <div class="help-block with-errors"></div></div>

				<div class="form-group" class="control-label"><label>Start Time</label>
				<input class="form-control input-sm" type="time" id="start_time" name="start_time" value="<?php if(isset($_POST['start_time'])) { echo $_POST['start_time']; }else{echo $snct['start_time'];} ?>" placeholder="Start Time" required>
                <div class="help-block with-errors"></div></div>
				
				<div class="form-group" class="control-label"><label>End Time</label>
				<input class="form-control input-sm" type="time" id="end_time" name="end_time" value="<?php if(isset($_POST['end_time'])) { echo $_POST['end_time']; }else{echo $snct['end_time'];} ?>" placeholder="End Time" required>
                <div class="help-block with-errors"></div></div>				
<?php
$zquery = "
SELECT sncz.id, sncz.status, sncz.schedule_night_climate_id, sncz.zone_id, zone.index_id, zone.name as zone_name,  sncz.min_temperature, sncz.max_temperature 
FROM schedule_night_climat_zone sncz
join zone on sncz.zone_id = zone.id
order by zone.index_id";
				$zoneresults = mysql_query($zquery, $connection);	
				while ($sncz = mysql_fetch_assoc($zoneresults)) {
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
				<option selected ><?php echo $sncz["min_temperature"];?></option>
				<option>18</option>
				<option>19</option>
				<option>20</option>
				<option>21</option>
				<option>22</option>
				<option>23</option>
				</select>
                <div class="help-block with-errors"></div>
				
				<label>Maximum Temperature</label>
				<select class="form-control input-sm" type="number" id="<?php echo $sncz["id"];?>" name="max[<?php echo $sncz["id"];?>]" placeholder="Zone Temperature" >
				<option selected ><?php echo $sncz["max_temperature"];?></option>
				<option>18</option>
				<option>19</option>
				<option>20</option>
				<option>21</option>
				<option>22</option>
				<option>23</option>
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
$query="select * from weather";
$result = mysql_query($query, $connection);
$weather = mysql_fetch_array($result);
?>
Outside: <?php //$weather = getWeather(); ?><?php echo $weather['c'] ;?>&deg;C
<span><img border="0" width="24" src="images/<?php echo $weather['img'];?>.png" title="<?php echo $weather['title'];?> - 
<?php echo $weather['description'];?>"></span> <span><?php echo $weather['title'];?> - 
<?php echo $weather['description'];?></span>

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