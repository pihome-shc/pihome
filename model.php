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
//PiConnect Settings
echo '
<div class="modal fade" id="piconnect" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">PiConnect '.$lang['settings'].'</h5>
            </div>
            <div class="modal-body">
<p class="text-muted"> 
'.$lang['piconnect_text'].'</a></p>';
$query = "SELECT * FROM piconnect";
$result = $conn->query($query);
$row = mysqli_fetch_array($result);
$status = $row['status'];
$api_key = $row['api_key'];
	echo '
		<form data-toggle="validator" role="form" method="post" action="settings.php" id="form-join">
		<div class="form-group" class="control-label">
		<div class="checkbox checkbox-default checkbox-circle">';
		if ($row['status'] == '1'){ 
			echo '<input id="checkbox0" class="styled" type="checkbox" value="1" name="status" checked>';
		}else {
			echo '<input id="checkbox0" class="styled" type="checkbox" value="1" name="status">';
		}
	echo' 
		<label for="checkbox0"> Enable PiConnect</label></div>
		<div class="form-group" class="control-label"><label>API Key</label>
		<input class="form-control input-sm" type="text" id="api_key" name="api_key" value="'.$row["api_key"].'" placeholder="PiConnect API Key">
		<div class="help-block with-errors"></div></div>';
echo '<br>
<h5 class="strong red" >'.$lang['piconnect_notice_text'].'</h5>';
echo '</div></div>
            <div class="modal-footer">
			    <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['cancel'].'</button>
                <input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-default login btn-sm" onclick="setup_piconnect()">
             </div>
        </div>
    </div>
</div>';

//query to frost protection temperature 
$query = "SELECT * FROM frost_protection LIMIT 1 ";
$result = $conn->query($query);
$frosttemp = mysqli_fetch_array($result);
$frost = $frosttemp['temperature'];
echo '
<div class="modal fade" id="add_frost" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['frost_protection'].'</h5>
            </div>
            <div class="modal-body">
                <p class="text-muted"> '.$lang['frost_protection_text'].'</p>
				<form data-toggle="validator" role="form" method="post" action="settings.php" id="form-join">
				<div class="form-group" class="control-label"><label>'.$lang['temperature'].'</label>
				<select class="form-control input-sm" type="number" id="frost_temp" name="frost_temp" >';
$c_f = settings($conn, 'c_f');
if($c_f==1 || $c_f=='1') {
    for($t=28;$t<=50;$t++){
        echo '<option value="' . $t . '" ' . (DispTemp($conn, $frost)==$t ? 'selected' : '') . '>' . $t . '</option>';
    }
} else {
    for($t=-1;$t<=12;$t++) {
        echo '<option value="' . $t . '" ' . ($frost==$t ? 'selected' : '') . '>' . $t . '</option>';
    }
}
echo '
				</select>
                <div class="help-block with-errors"></div></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['cancel'].'</button>
                <input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-default login btn-sm" onclick="update_frost()">
            </div>
        </div>
    </div>
</div>';

//Units
$c_f = settings($conn, 'c_f');
if($c_f==1 || $c_f=='1')
    $TUnit='F';
else
    $TUnit='C';
echo '
<div class="modal fade" id="change_units" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['unit_change'].'</h5>
            </div>
            <div class="modal-body">
				<form data-toggle="validator" role="form" method="post" action="settings.php" id="form-join">
				<div class="form-group" class="control-label"><label>'.$lang['units'].'</label>
				<select class="form-control input-sm" type="number" id="new_units" name="new_units">
				<option value="0" ' . ($c_f==0 || $c_f=='0' ? 'selected' : '') . '>'.$lang['unit_celsius'].'</option>
				<option value="1" ' . ($c_f==1 || $c_f=='1' ? 'selected' : '') . '>'.$lang['unit_fahrenheit'].'</option>
				</select>
                <div class="help-block with-errors"></div></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['cancel'].'</button>
                <input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-default login btn-sm" onclick="update_units()">
            </div>
        </div>
    </div>
</div>';

//Language settings
$language = settings($conn, 'language');
echo '
<div class="modal fade" id="language" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['language'].'</h5>
            </div>
            <div class="modal-body">
				
				<form data-toggle="validator" role="form" method="post" action="settings.php" id="form-join">
				<div class="form-group" class="control-label"><label>'.$lang['language'].'</label>
				<select class="form-control input-sm" type="text" id="new_lang" name="new_lang">
				<option value="en" ' . ($language=='en' ? 'selected' : '') . '>'.$lang['lang_en'].'</option>
				<option value="pt" ' . ($language=='pt' ? 'selected' : '') . '>'.$lang['lang_pt'].'</option>
				<option value="pt" ' . ($language=='fr' ? 'selected' : '') . '>'.$lang['lang_fr'].'</option>
				</select>
                <div class="help-block with-errors"></div></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['cancel'].'</button>
                <input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-default login btn-sm" onclick="update_lang()">
            </div>
        </div>
    </div>
</div>';

//Boiler settings
echo '
<div class="modal fade" id="boiler_safety_setup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['boiler_settings'].'</h5>
            </div>
            <div class="modal-body">
<p class="text-muted"> Boiler safety settings i.e. <i class="ionicons ion-ios-timer blue"></i> '.$lang['boiler_hysteresis_text'].'</p>';
$query = "SELECT * FROM boiler_view";
$results = $conn->query($query);
echo '	<div class=\"list-group\">';
while ($row = mysqli_fetch_assoc($results)) {
	echo " <a href=\"#\" class=\"list-group-item\">
	<i class=\"ionicons ion-flame fa-1x red\"></i> ".$row['name']." - ".$lang['node'].": ".$row['node_id']." ".$lang['child'].": ".$row['node_child_id']."
	<span class=\"pull-right \"><em>&nbsp;&nbsp;<i class=\"ionicons ion-ios-timer blue\"></i> ".$row['hysteresis_time']. " </em></span>
	</a>";
}
echo '</div></div>
            <div class="modal-footer">
				<button type="button" class="btn btn-default login btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
             </div>
        </div>
    </div>
</div>';

//boost model
echo '
<div class="modal fade" id="boost_setup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['boost_settings'].'</h5>
            </div>
            <div class="modal-body">
<p class="text-muted"> '.$lang['boost_settings_text'].'</p>';
$query = "SELECT * FROM boost_view ORDER BY index_id asc";
$results = $conn->query($query);
echo '	<div class=\"list-group\">';
while ($row = mysqli_fetch_assoc($results)) {
	echo "<a href=\"#\" class=\"list-group-item\"><i class=\"fa fa-rocket fa-fw blueinfo\"></i> ".$row['name']."
	<span class=\"pull-right text-muted small\"><em>".$row['minute']." minute ".number_format(DispTemp($conn,$row['temperature']),0)."&deg;  </em></span></a>"; 
}
echo '</div></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default login btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
				
            </div>
        </div>
    </div>
</div>';

//override model
echo '
<div class="modal fade" id="override_setup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['override_settings'].'</h5>
            </div>
            <div class="modal-body">
<p class="text-muted"> '.$lang['override_settings_text'].'</p>';
$query = "SELECT * FROM override_view ORDER BY index_id asc";
$results = $conn->query($query);
echo '	<div class=\"list-group\">';
while ($row = mysqli_fetch_assoc($results)) {
	echo "<a href=\"#\" class=\"list-group-item\">
	<i class=\"fa fa-refresh fa-1x blue\"></i> ".$row['name']." 
    <span class=\"pull-right text-muted small\"><em>".$row['temperature']."&deg; </em></span></a>";
}
echo '</div></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default login btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
            </div>
        </div>
    </div>
</div>';

//Sensor location model
echo '
<div class="modal fade" id="temperature_sensor" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['temperature_sensor'].'</h5>
            </div>
            <div class="modal-body">
<p class="text-muted"> '.$lang['temperature_sensor_text'].' </p>';
$query = "SELECT * FROM nodes where name = 'Temperature Sensor' ORDER BY node_id asc";
$results = $conn->query($query);
echo '	<div class=\"list-group\">';
while ($row = mysqli_fetch_assoc($results)) {
	$batquery = "select * from nodes_battery where node_id = {$row['node_id']} ORDER BY id desc limit 1;";
	$batresults = $conn->query($batquery);
	$brow = mysqli_fetch_array($batresults);
	if ($row['ms_version'] > 0){
		echo "<a href=\"#\" class=\"list-group-item\">
		<i class=\"ionicons ion-thermometer red\"></i> ".$row['node_id']." - <i class=\"fa fa-battery-full\"></i> ".round($brow ['bat_level'],0)."% - ".$brow ['bat_voltage']."v
		<span class=\"pull-right text-muted small\"><em>".$row['last_seen']."</em></span></a>"; 	
	}else {
		echo "<a href=\"#\" class=\"list-group-item\">
		<i class=\"ionicons ion-thermometer red\"></i> ".$row['node_id']."<span class=\"pull-right text-muted small\"><em>".$row['last_seen']."</em></span></a>"; 
	}
}
echo '</div></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default login btn-sm" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>';

//Zone model	
echo '
<div class="modal fade" id="zone_setup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['zone_settings'].'</h5>
            </div>
            <div class="modal-body">
<p class="text-muted">'.$lang['zone_settings_text'].'</p>';
$query = "select * from zone_view order by index_id asc";
$results = $conn->query($query);
echo '	<div class=\"list-group\">';
while ($row = mysqli_fetch_assoc($results)) {
	if ($row['gpio_pin'] == 0){
		echo "<div class=\"list-group-item\">
		<i class=\"glyphicon glyphicon-th-large orange\"></i> ".$row['name']."
		<span class=\"pull-right \"><em>&nbsp;&nbsp;<small> ".$lang['max']." ".$row['max_c']."&deg; </em> - ".$lang['sensor'].": ".$row['sensors_id']." - ".$lang['ctr'].": ".$row['controler_id']."-".$row['controler_child_id']."</small></span> 
		<br><span class=\"pull-right \"><small>
		<a href=\"zone_edit.php?id=".$row['id']."\" class=\"btn btn-default btn-xs login\"><span class=\"ionicons ion-edit\"></span></a>&nbsp;&nbsp;
		<a href=\"javascript:delete_zone(".$row['id'].");\"><button class=\"btn btn-danger btn-xs\"><span class=\"glyphicon glyphicon-trash\"></span></button></a>
		</small></span>
		<br>
		</div>";
	} else {
		echo "<div class=\"list-group-item\">
		<i class=\"glyphicon glyphicon-th-large orange\"></i> ".$row['name']."
		<span class=\"pull-right \"><em>&nbsp;&nbsp;<small> ".$lang['max']." ".$row['max_c']."&deg; </em> - ".$lang['sensor'].": ".$row['sensors_id']." - GPIO: ".$row['gpio_pin']."</small></span>
		<br><span class=\"pull-right \"><small>
		<a href=\"zone_edit.php?id=".$row['id']."\" class=\"btn btn-default btn-xs login\"><span class=\"ionicons ion-edit\"></span></a>&nbsp;&nbsp;
		<a href=\"javascript:delete_zone(".$row['id'].");\"><button class=\"btn btn-danger btn-xs\"><span class=\"glyphicon glyphicon-trash\"></span></button></a>
		</small></span>
		<br>
		</div>";
	}
}
echo '
</div></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default login btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
            </div>
        </div>
    </div>
</div>';

//gateway model
echo '
<div class="modal fade" id="sensor_gateway" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['smart_home_gateway'].'</h5>
            </div>
            <div class="modal-body">';
$gquery = "SELECT * FROM gateway";
$gresult = $conn->query($gquery);
$grow = mysqli_fetch_array($gresult);
echo '<p class="text-muted">'; 
if ($grow['type']=='wifi'){echo $lang['smart_home_gateway_text_wifi'];}elseif ($grow['type']=='serial') {echo $lang['smart_home_gateway_text_serial'];}
echo '</p>';
echo '
	<form data-toggle="validator" role="form" method="post" action="settings.php" id="form-join">
	<div class="form-group" class="control-label">
	<div class="checkbox checkbox-default checkbox-circle">';
	if ($grow['status'] == '1'){
		echo '<input id="checkbox1" class="styled" type="checkbox" value="1" name="status" checked>';
	}else {
		echo '<input id="checkbox1" class="styled" type="checkbox" value="1" name="status">';
	}
echo ' 

	
	<label for="checkbox0"> '.$lang['smart_home_gateway_enable'].'</label></div></div>
	
	<div class="form-group" class="control-label"><label>'.$lang['smart_home_gateway_type'].'</label>
	<select class="form-control input-sm" type="text" id="gw_type" name="gw_type">
	<option value="wifi" ' . ($grow['type']=='wifi' ? 'selected' : '') . '>'.$lang['wifi'].'</option>
	<option value="serial" ' . ($grow['type']=='serial' ? 'selected' : '') . '>'.$lang['serial'].'</option>
	</select>
    <div class="help-block with-errors"></div></div>
	
	<div class="form-group" class="control-label"><label>'.$lang['smart_home_gateway_location'].'</label>
	<input class="form-control input-sm" type="text" id="gw_location" name="gw_location" value="'.$grow['location'].'" placeholder="Gateway Location">
	<div class="help-block with-errors"></div></div>
	
	<div class="form-group" class="control-label"><label>'.$lang['smart_home_gateway_port'].' </label>
	<input class="form-control input-sm" type="text" id="gw_port" name="gw_port" value="'.$grow['port'].'" placeholder="Gateway Port">
	<div class="help-block with-errors"></div></div>

	<div class="form-group" class="control-label"><label>'.$lang['timeout'].' </label>
	<input class="form-control input-sm" type="text" id="gw_timout" name="gw_timout" value="'.$grow['timout'].'" placeholder="Gateway Timeout">
	<div class="help-block with-errors"></div></div>

	<div class="form-group" class="control-label"><label>'.$lang['smart_home_gateway_version'].' </label>
	<input class="form-control input-sm" type="text" id="gw_timout" name="gw_timout" value="'.$grow['version'].'" disabled>
	<div class="help-block with-errors"></div></div>
	
<br><h4 class="info"><i class="fa fa-heartbeat red"></i> '.$lang['smart_home_gateway_scr_info'].'</h4>
<div class=\"list-group\">';
echo "
<a href=\"#\" class=\"list-group-item\"> PID <span class=\"pull-right text-muted small\"><em> ".$grow['pid']."</em></span></a>
<a href=\"#\" class=\"list-group-item\"> ".$lang['smart_home_gateway_pid'].": <span class=\"pull-right text-muted small\"><em>".$grow['pid_running_since']."</em></span></a>";

$query = "select * FROM gateway_logs WHERE pid_datetime >= NOW() - INTERVAL 5 MINUTE;";
$result = $conn->query($query);
if (mysqli_num_rows($result) != 0){
	$gw_restarted = mysqli_num_rows($result);
} else {
	$gw_restarted = '0';
}
echo "<a href=\"#\" class=\"list-group-item\"> ".$lang['smart_home_gateway_scr'].": <span class=\"pull-right text-muted small\"><em>".$gw_restarted."</em></span></a>";
echo '</div></div>
            <div class="modal-footer">
               
				<a href="javascript:resetgw('.$grow['pid'].')" class="btn btn-default login btn-sm btn-edit">Reset GW</a>
				<a href="javascript:find_gw()" class="btn btn-default login btn-sm btn-edit">Search GW</a>
				<input type="button" name="submit" value="Save" class="btn btn-default login btn-sm" onclick="setup_gateway()">
				<button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
            </div>
        </div>
    </div>
</div>';

//cronetab model	
echo '
<div class="modal fade" id="cron_jobs" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['cron_jobs'].'</h5>
            </div>
            <div class="modal-body">
<p class="text-muted"> '.$lang['cron_jobs_text'].' </p>';
echo '	<div class=\"list-group\">';
//exec ("crontab -l >/var/www/cronjob.txt"); this didnt work need to investigate 
$file_handle = fopen("/var/www/cronjob.txt", "r");
while ($file_handle && !feof($file_handle)) {
	$line = fgets($file_handle);
	echo "<a href=\"#\" class=\"list-group-item\">
    <i class=\"ionicons ion-ios-timer-outline red\"></i> ".$line."
    <span class=\"pull-right text-muted small\"><em></em></span>
    </a>";
}
fclose($file_handle);
echo ' </div></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default login btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
            </div>
        </div>
    </div>
</div>';

//OS version model
//$osversion = exec ("cat /etc/os-release");
//$lines=file('/etc/os-release');
$lines=array();
$fp=fopen('/etc/os-release', 'r');
while ($fp && !feof($fp)){
    $line=fgets($fp);
    //process line however you like
    $line=trim($line);
    //add to array
    $lines[]=$line;
}
fclose($fp);
echo '
<div class="modal fade" id="os_version" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['os_version'].'</h5>
            </div>
            <div class="modal-body">
			   <div class="list-group">
				<a href="#" class="list-group-item"><i class="fa fa-linux"></i> '.$lines[1].'</a>
				<a href="#" class="list-group-item"><i class="fa fa-linux"></i> '.$lines[3].'</a>
				</div>				
           </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default login btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
            </div>
        </div>
    </div>
</div>';

//Pihome Update
echo '
<div class="modal fade" id="pihome_update" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['pihome_update'].'</h5>
            </div>
            <div class="modal-body">
			<p class="text-muted"> '.$lang['pihome_update_text'].' </p>';

		
echo '	<div class=\"list-group\">';
echo "                            <a href=\"#\" class=\"list-group-item\">
                                    <i class=\"fa fa-server fa-1x blueinfo\"></i> ".$lang['pihome_update_c_version']."
                                    <span class=\"pull-right text-muted small\"><em>".settings($conn, 'version')."</em>
                                    </span>
                                </a>"; 
ini_set('max_execution_time',90);
$getVersions = file_get_contents(''.settings($conn, 'update_location').''.settings($conn, 'update_file').'');
if ($getVersions != ''){
$versionList = explode("\n", $getVersions);	
	foreach ($versionList as $aV)
	{
		echo "<a href=\"settings.php?uid=10\"  class=\"list-group-item\">
        <i class=\"fa fa-download fa-1x blueinfo\"></i> ".$lang['pihome_update_u_version']."
        <span class=\"pull-right text-muted small\"><em>".$aV."</em></span>
         </a>";
	}
}	
echo '</div></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default login btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
            </div>
        </div>
    </div>
</div>';


// backup_image
echo '
<div class="modal fade" id="backup_image" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['pihome_backup'].'</h5>
            </div>
            <div class="modal-body">
			<p class="text-muted"> '.$lang['pihome_backup_text'].' </p>
			<i class="fa fa-clone fa-1x blue"></i> '.$lang['pihome_backup'].'
			';
echo '     </div>
            <div class="modal-footer">
			<button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
			<a href="javascript:db_backup()" class="btn btn-default login btn-sm">'.$lang['start'].'</a>
            </div>
        </div>
    </div>
</div>';

// Reboot Modal
echo '
<div class="modal fade" id="reboot_system" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['reboot_system'].'</h5>
            </div>
            <div class="modal-body">
                        <p class="text-muted"> <i class="ion-ios-refresh-outline orange"></i> '.$lang['reboot_system_text'].' </p>
                        ';
echo '            </div>
            <div class="modal-footer">
                        <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['cancel'].'</button>
                        <a href="javascript:reboot()" class="btn btn-default login btn-sm">'.$lang['yes'].'</a>
            </div>
        </div>
    </div>
</div>';

// Shutdown Model
echo '
<div class="modal fade" id="shutdown_system" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['shutdown_system'].'</h5>
            </div>
            <div class="modal-body">
                        <p class="text-muted"><i class="fa fa-power-off fa-1x red"></i> '.$lang['shutdown_system_text'].' </p>
                        ';
echo '            </div>
            <div class="modal-footer">
                        <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['cancel'].'</button>
                        <a href="javascript:shutdown()" class="btn btn-default login btn-sm">'.$lang['yes'].'</a>
            </div>
        </div>
    </div>
</div>';

//wifi model
$rxwifidata = exec ("cat /sys/class/net/wlan0/statistics/rx_bytes");
$txwifidata = exec ("cat /sys/class/net/wlan0/statistics/tx_bytes");
$rxwifidata = $rxwifidata/1024; // convert to kb
$rxwifidata = $rxwifidata/1024; // convert to mb

$txwifidata = $txwifidata/1024; // convert to kb
$txwifidata = $txwifidata/1024; // convert to mb
$wifimac = exec ("cat /sys/class/net/wlan0/address");
//$wifipeed = exec ("cat /sys/class/net/wlan0/speed");
//$wifipeed = exec("iwconfig wlan0 | grep -i --color quality");
$wifistatus = exec ("cat /sys/class/net/wlan0/operstate");
echo '
<div class="modal fade" id="wifi_setup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['wifi_settings'].'</h5>
            </div>
            <div class="modal-body">
			<p class="text-muted"> '.$lang['wifi_settings_text'].' </p>
<div class="list-group">
<a href="#" class="list-group-item">
<i class="fa fa-signal green"></i> '.$lang['status'].': '.$wifistatus.'
</a>
<a href="#" class="list-group-item">
<i class="fa fa-signal green"></i> '.$lang['mac'].': '.$wifimac.'
</a>
<a href="#" class="list-group-item">
<i class="fa fa-signal green"></i> '.$lang['download'].': '.number_format($rxwifidata,0).' MB 
</a>
<a href="#" class="list-group-item">
<i class="fa fa-signal green"></i> '.$lang['upload'].': '.number_format($txwifidata,0).' MB 
</a>
</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default login btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
            </div>
        </div>
    </div>
</div>';

//ethernet model
$rxdata = exec ("cat /sys/class/net/eth0/statistics/rx_bytes");
$txdata = exec ("cat /sys/class/net/eth0/statistics/tx_bytes");
$rxdata = $rxdata/1024; // convert to kb
$rxdata = $rxdata/1024; // convert to mb
$txdata = $txdata/1024; // convert to kb
$txdata = $txdata/1024; // convert to mb
$nicmac = exec ("cat /sys/class/net/eth0/address");
$nicpeed = exec ("cat /sys/class/net/eth0/speed");
$nicactive = exec ("cat /sys/class/net/eth0/operstate");
echo '
<div class="modal fade" id="eth_setup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['ethernet_settings'].'</h5>
            </div>
            <div class="modal-body">
			   <div class="list-group">
				<a href="#" class="list-group-item"><i class="ionicons ion-network green"></i>
				'.$lang['status'].': '.$nicactive.'</a>
				<a href="#" class="list-group-item"><i class="ionicons ion-network green"></i>
				'.$lang['speed'].': '.$nicpeed.'Mb</a>
				<a href="#" class="list-group-item"><i class="ionicons ion-network green"></i>
				'.$lang['mac'].': '.$nicmac.'</a>
				<a href="#" class="list-group-item"><i class="ionicons ion-network green"></i>
				'.$lang['download'].': '.number_format($rxdata,0).' MB </a> 
				<a href="#" class="list-group-item"><i class="ionicons ion-network green"></i>
				'.$lang['upload'].': '.number_format($txdata,0).' MB </a>
				</div>
           </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default login btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
            </div>
        </div>
    </div>
</div>';

//user accounts model 
echo '
<div class="modal fade" id="user_setup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['user_accounts'].'</h5>
            </div>
            <div class="modal-body">
			<p class="text-muted"> '.$lang['user_accounts_text'].' </p>';
echo '<div class=\"list-group\">';
$query = "SELECT * FROM user";
$results = $conn->query($query);
while ($row = mysqli_fetch_assoc($results)) {
	$full_name=$row['fullname'];
	$username=$row['username'];
	echo "<div href=\"settings.php?uid=".$row['id']."\"  class=\"list-group-item\"> 
    <i class=\"ionicons ion-person blue\"></i> ".$username."
    <span class=\"pull-right text-muted small\"><em>
	<a href=\"javascript:del_user(".$row["id"].");\"><button class=\"btn btn-danger btn-xs\"><span class=\"glyphicon glyphicon-trash\"></span></button> </a>
	<a href=\"user_password.php?uid=".$row["id"]."\"><button class=\"btn btn-primary btn-xs\"><span class=\"fa fa-user fa-key\"></span></button> </a>
	</em></span></div>";
}
echo '</div></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default login btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
            </div>
        </div>
    </div>
</div>';

//Big Thank you 	
echo '
<div class="modal fade" id="big_thanks" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['credits'].'</h5>
            </div>
            <div class="modal-body">
<p class="text-muted"> '.$lang['credits_text'].' </p>';
echo '	<div class=\"list-group\">';
echo " 

<a href=\"http://startbootstrap.com/template-overviews/sb-admin-2\" class=\"list-group-item\"><i class=\"ionicons ion-help-buoy blueinfo\"></i> SB Admin 2 Template <span class=\"pull-right text-muted small\"><em>...</em></span></a>
<a href=\"http://www.cssscript.com/pretty-checkbox-radio-inputs-bootstrap-awesome-bootstrap-checkbox-css\" class=\"list-group-item\"><i class=\"ionicons ion-help-buoy blueinfo\"></i> Pretty Checkbox <span class=\"pull-right text-muted small\"><em>...</em></span></a>
<a href=\"https://fortawesome.github.io/Font-Awesome\" class=\"list-group-item\"><i class=\"ionicons ion-help-buoy blueinfo\"></i> Font-Awesome <span class=\"pull-right text-muted small\"><em>...</em></span></a>
<a href=\"http://ionicons.com\" class=\"list-group-item\"><i class=\"ionicons ion-help-buoy blueinfo\"></i> Ionicons <span class=\"pull-right text-muted small\"><em>...</em></span></a>
<a href=\"http://www.cssmatic.com/box-shadow\" class=\"list-group-item\"><i class=\"ionicons ion-help-buoy blueinfo\"></i> Box Shadow CSS <span class=\"pull-right text-muted small\"><em>...</em></span></a>
<a href=\"https://daneden.github.io/animate.css\" class=\"list-group-item\"><i class=\"ionicons ion-help-buoy blueinfo\"></i> Animate.css <span class=\"pull-right text-muted small\"><em>...</em></span></a>
<a href=\"https://www.mysensors.org\" class=\"list-group-item\"><i class=\"ionicons ion-help-buoy blueinfo\"></i> MySensors <span class=\"pull-right text-muted small\"><em>...</em></span></a>
<a href=\"http://www.pihome.eu\" class=\"list-group-item\"><i class=\"ionicons ion-help-buoy blueinfo\"></i> All others if forget them... <span class=\"pull-right text-muted small\"><em>...</em></span></a>
<a href=\"http://pihome.harkemedia.de\" class=\"list-group-item\"><i class=\"ionicons ion-help-buoy blueinfo\"></i> RaspberryPi Home Automation <span class=\"pull-right text-muted small\"><em>...</em></span></a>
";
echo '</div></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
            </div>
        </div>
    </div>
</div>';
?>