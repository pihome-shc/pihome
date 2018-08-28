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
	$query = "INSERT INTO schedule_daily_time(sync, status, start, end) VALUES ('0', '{$sc_en}', '{$start_time}','{$end_time}')"; 
	$result = $conn->query($query);
	$schedule_daily_time_id = mysqli_insert_id($conn);
	
	if ($result) {
		$message_success = "Schedule Time Added Successfully!!!";
		header("Refresh: 3; url=schedule.php");
	} else {
		$error = "<p>Schedule Addtion failed with error </p><p>" . mysqli_error() . "</p>";
	}
	foreach($_POST['id'] as $id){
		$id = $_POST['id'][$id];
		$status = isset($_POST['status'][$id]) ? $_POST['status'][$id] : "0";
		$status = $_POST['status'][$id];
		$temp = $_POST['temp'][$id];
		$query = "INSERT INTO schedule_daily_time_zone(sync, status, schedule_daily_time_id, zone_id, temperature) VALUES ('0', '{$status}', '{$schedule_daily_time_id}','{$id}','{$temp}')"; 
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
                <label for="checkbox0"> Enable Schedule</label>
                <div class="help-block with-errors"></div></div>

		
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
	<option>0</option>
	<option>16</option>
	<option>17</option>
	<option>18</option>
	<option>19</option>
	<option>20</option>
	<option>21</option>
	<option>22</option>
	<option>23</option>
	<option>24</option>
	<option>25</option>
	<option>26</option>
	<option>27</option>
	<option>28</option>
	<option>29</option>
	<option>30</option>
	<option>31</option>
	<option>32</option>
	<option>33</option>
	<option>34</option>
	<option>35</option>
	<option>36</option>
	<option>37</option>
	<option>38</option>
	<option>39</option>
	<option>40</option>
	<option>41</option>
	<option>42</option>
	<option>43</option>
	<option>44</option>
	<option>45</option>
	
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
$query="select * from weather";
$result = $conn->query($query);
$weather = mysqli_fetch_array($result);
?>

Outside: <?php //$weather = getWeather(); ?><?php echo $weather['c'] ;?>&deg;C
<span><img border="0" width="24" src="images/<?php echo $weather['img'];?>.png" title="<?php echo $weather['title'];?> - 
<?php echo $weather['description'];?>"></span> <span><?php echo $weather['title'];?> - 
<?php echo $weather['description'];?></span>
                        </div>
                    </div>
                </div>
                <!-- /.col-lg-4 -->
            </div>
            <!-- /.row -->
        </div>
        <!-- /#page-wrapper -->
		<?php include("footer.php"); ?>