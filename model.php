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

//Graph model
echo '
<div class="modal fade" id="zone_graph" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['graph_settings'].'</h5>
            </div>
            <div class="modal-body">
<p class="text-muted">'.$lang['graph_settings_text'].'</p>';
$query = "select * from zone_view where type = 'Heating' order by index_id asc";
$results = $conn->query($query);
echo '  <div class=\"list-group\">';
while ($row = mysqli_fetch_assoc($results)) {
?>
        <hr>
        <div class="checkbox checkbox-default  checkbox-circle">
        <input id="checkbox<?php echo $row["id"];?>" class="styled" type="checkbox" name="graph_it[<?php echo $row["id"];?>]" value="1" <?php $check = ($row['graph_it'] == 1) ? 'checked' : ''; echo $check; ?> onclick="$('#<?php echo $row["id"];?>').toggle();">
        <label for="checkbox<?php echo $row["id"];?>"><?php echo $row["name"];?></label>
        <div class="help-block with-errors"></div></div>
<?php }
echo '
</div></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
                <input type="button" name="submit" value="Save" class="btn btn-default login btn-sm" onclick="setup_graph()">
            </div>
        </div>
    </div>
</div>';

//Boiler settings
echo '
<div class="modal fade" id="boiler" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['boiler_settings'].'</h5>
            </div>
            <div class="modal-body">';
$query = "SELECT * FROM boiler;";
$results = $conn->query($query);
$brow = mysqli_fetch_array($results);
echo '<p class="text-muted">'.$lang['boiler_info_text'].'</p>';

echo '
	<form data-toggle="validator" role="form" method="post" action="settings.php" id="form-join">
	
	<div class="form-group" class="control-label">
	<div class="checkbox checkbox-default checkbox-circle">';
	if ($brow['status'] == '1'){
		echo '<input id="checkbox2" class="styled" type="checkbox" value="1" name="status" checked Disabled>';
	}else {
		echo '<input id="checkbox2" class="styled" type="checkbox" value="1" name="status" Disabled>';
	}
	echo '<label for="checkbox2"> '.$lang['boiler_enable'].'</label></div></div>
	
	<div class="form-group" class="control-label"><label>'.$lang['boiler_name'].'</label>
	<input class="form-control input-sm" type="text" id="name" name="name" value="'.$brow['name'].'" placeholder="Boiler Name to Display on Screen ">
	<div class="help-block with-errors"></div></div>

	<div class="form-group" class="control-label"><label>'.$lang['boiler_node_id'].'</label> <small class="text-muted">'.$lang['boiler_node_id_info'].'</small>
	<select class="form-control input-sm" type="text" id="node_id" name="node_id">';
	//get current node_id from nodes table 
	$query = "SELECT * FROM nodes WHERE id ='".$brow['node_id']."' Limit 1;";
	$result = $conn->query($query);
	$row = mysqli_fetch_assoc($result);
	$node_id=$row['node_id'];
	$notice_interval=$row['notice_interval'];
	
	echo '<option value="'.$node_id.'" selected>'.($node_id=='0' ? 'N/A' : $node_id).'</option>';
	
	//get list from nodes table to display 
	$query = "SELECT * FROM nodes where name = 'Boiler Relay' OR name = 'Boiler Controller' AND node_id!=0;";
	$result = $conn->query($query);
	if ($result){
		while ($nrow=mysqli_fetch_array($result)) {
			echo '<option value="'.$nrow['node_id'].'">'.$nrow['node_id'].'</option>';
		}
	}
	echo '<option value="0">N/A</option>
	</select>
    <div class="help-block with-errors"></div></div>';
	
	echo '
	<div class="form-group" class="control-label"><label>'.$lang['boiler_node_child_id'].'</label>
	<select class="form-control input-sm" type="text" id="node_child_id" name="node_child_id">
	<option selected>'.$brow['node_child_id'].'</option>
	<option value="0">0</option>
	<option value="1">1</option>
	<option value="2">2</option>
	<option value="3">3</option>
	<option value="4">4</option>
	<option value="5">5</option>
	<option value="6">6</option>
	<option value="7">7</option>
	<option value="8">8</option>
	</select>
    <div class="help-block with-errors"></div></div>
	
	<div class="form-group" class="control-label"><label>'.$lang['boiler_relay_gpio'].'</label> <small class="text-muted">'.$lang['boiler_relay_gpio_text'].'</small>
	<select id="gpio_pin" name="gpio_pin" class="form-control select2" autocomplete="off" required>
	<option value="'.$brow['gpio_pin'].'" selected>'.($brow['gpio_pin']=='0' ? 'N/A' : $brow['gpio_pin']).'</option>
	<option value="0">N/A</option>
	<option value="1">1</option>
	<option value="2">2</option>
	<option value="3">3</option>
	<option value="4">4</option>
	<option value="5">5</option>
	<option value="6">6</option>
	<option value="7">7</option>
	<option value="21">21</option>
	<option value="22">22</option>
	<option value="23">23</option>
	<option value="24">24</option>
	<option value="25">25</option>
	<option value="26">26</option>
	<option value="27">27</option>
	<option value="28">28</option>
	<option value="29">29</option>
	</select>				
	<div class="help-block with-errors"></div></div>

	<div class="form-group" class="control-label"><label>'.$lang['boiler_hysteresis_time'].'</label> <small class="text-muted">'.$lang['boiler_hysteresis_time_info'].'</small>
	<select class="form-control input-sm" type="text" id="hysteresis_time" name="hysteresis_time">
	<option selected>'.$brow['hysteresis_time'].'</option>
	<option value="0">0</option>
	<option value="1">1</option>
	<option value="2">2</option>
	<option value="3">3</option>
	<option value="4">4</option>
	<option value="5">5</option>
	<option value="6">6</option>
	<option value="7">7</option>
	<option value="8">8</option>
	</select>
    <div class="help-block with-errors"></div></div>

	<div class="form-group" class="control-label"><label>'.$lang['max_operation_time'].'</label> <small class="text-muted">'.$lang['max_operation_time_info'].'</small>
	<select class="form-control input-sm" type="text" id="max_operation_time" name="max_operation_time">
	<option selected>'.$brow['max_operation_time'].'</option>
	<option value="30">30</option>
	<option value="40">40</option>
	<option value="45">45</option>
	<option value="50">50</option>
	<option value="55">55</option>
	<option value="60">60</option>
	<option value="65">65</option>
	<option value="70">70</option>
	<option value="80">80</option>
	<option value="85">85</option>
	<option value="90">90</option>
	<option value="95">95</option>
	<option value="100">100</option>
	<option value="110">110</option>
	<option value="120">120</option>
	</select>
    <div class="help-block with-errors"></div></div>
	
	<div class="form-group" class="control-label"><label>'.$lang['notice_interval'].'</label> <small class="text-muted">'.$lang['notice_interval_info'].'</small>
	<select class="form-control input-sm" type="text" id="notice_interval" name="notice_interval">
	<option selected>'.$notice_interval.'</option>
	<option value="0">0</option>
	<option value="5">5</option>
	<option value="7">7</option>
	<option value="9">9</option>
	<option value="11">11</option>
	<option value="13">13</option>
	<option value="15">15</option>
	<option value="17">17</option>
	<option value="19">19</option>
	</select>
    <div class="help-block with-errors"></div></div>
	';

	echo '</div>
            <div class="modal-footer">
				<button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
				<input type="button" name="submit" value="Save" class="btn btn-default login btn-sm" onclick="boiler_settings()">
				
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
<p class="text-muted"> '.$lang['boost_settings_text'].' </p>';
$query = "SELECT boost.id, boost.status, boost.sync, boost.zone_id, zone_idx.index_id, zone.name, boost.temperature, boost.minute, boost.boost_button_id, boost.boost_button_child_id ";
$query = $query."FROM boost JOIN zone ON boost.zone_id = zone.id JOIN zone zone_idx ON boost.zone_id = zone_idx.id ORDER BY zone.index_id ASC, boost.minute ASC;";
$results = $conn->query($query);
echo '<table class="table table-bordered">
    <tr>
        <th class="col-xs-4"><small>'.$lang['zone'].'</small></th>
        <th class="col-xs-2"><small>'.$lang['boost_time'].'</small></th>
        <th class="col-xs-2"><small>'.$lang['boost_temp'].'</small></th>
        <th class="col-xs-2"><small>'.$lang['boost_console_id'].'</small></th>
        <th class="col-xs-1"><small>'.$lang['boost_button_child_id'].'</small></th>
        <th class="col-xs-1"></th>
    </tr>';

while ($row = mysqli_fetch_assoc($results)) {
    echo '
        <tr>
            <th scope="row"><small>'.$row['name'].'</small></th>
            <td><input id="minute'.$row["id"].'" type="text" class="pull-left text" style="border: none" name="minute" size="3" value="'.$row["minute"].'" placeholder="Minutes" required></td>
            <td><input id="temperature'.$row["id"].'" type="text" class="pull-left text" style="border: none" name="temperature" size="3" value="'.$row["temperature"].'" placeholder="Temperature" required></td>
            <td><input id="boost_button_id'.$row["id"].'" type="text" class="pull-left text" style="border: none" name="button_id"  size="3" value="'.$row["boost_button_id"].'" placeholder="Button ID" required></td>
            <td><input id="boost_button_child_id'.$row["id"].'" type="text" class="pull-left text" style="border: none" name="button_child_id" size="3" value="'.$row["boost_button_child_id"].'" placeholder="Child ID" required></td>
            <td><a href="javascript:delete_boost('.$row["id"].');"><button class="btn btn-danger btn-xs"><span class="glyphicon glyphicon-trash"></span></button> </a></td>
        </tr>';

}

echo '</table></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
                <input type="button" name="submit" value="Save" class="btn btn-default login btn-sm" onclick="update_boost()">
                <button type="button" class="btn btn-default login btn-sm" data-href="#" data-toggle="modal" data-target="#add_boost">'.$lang['add_boost'].'</button>
            </div>
        </div>
    </div>
</div>';

//Add Boost
echo '
<div class="modal fade" id="add_boost" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['add_boost'].'</h5>
            </div>
            <div class="modal-body">';
echo '<p class="text-muted">'.$lang['boost_info_text'].'</p>
	<form data-toggle="validator" role="form" method="post" action="settings.php" id="form-join">
	
	<div class="form-group" class="control-label"><label>'.$lang['zone'].'</label> 
	<select class="form-control input-sm" type="text" id="zone_id" name="zone_id">';
	//Get Zone List
	$query = "SELECT * FROM zone where status = 1;";
	$result = $conn->query($query);
	if ($result){
		while ($zrow=mysqli_fetch_array($result)) {
			echo '<option value="'.$zrow['id'].'">'.$zrow['name'].'</option>';
		}
	}
	echo '
	</select>
    <div class="help-block with-errors"></div></div>
	
	<div class="form-group" class="control-label"><label>'.$lang['boost_temperature'].'</label> <small class="text-muted">'.$lang['boost_temperature_info'].'</small>
	<select class="form-control input-sm" type="text" id="boost_temperature" name="boost_temperature">
	<option value="20">20</option>
	<option value="21">22</option>
	<option value="23">23</option>
	<option value="24">24</option>
	<option value="25">25</option>
	<option value="30">30</option>
	<option value="35">35</option>
	<option value="40">40</option>
	<option value="45">45</option>
	<option value="50">50</option>
	<option value="55">55</option>
	<option value="60">60</option>
	<option value="65">65</option>
	<option value="70">70</option>
	<option value="75">75</option>
	<option value="80">80</option>
	<option value="85">85</option>
	<option value="90">90</option>
	<option value="95">95</option>
	</select>
    <div class="help-block with-errors"></div></div>

	<div class="form-group" class="control-label"><label>'.$lang['boost_time'].'</label> <small class="text-muted">'.$lang['boost_time_info'].'</small>
	<select class="form-control input-sm" type="text" id="boost_time" name="boost_time">
	<option value="20">20</option>
	<option value="25">25</option>
	<option value="30">30</option>
	<option value="35">35</option>
	<option value="40">40</option>
	<option value="45">45</option>
	<option value="50">50</option>
	<option value="55">55</option>
	<option value="60">60</option>
	<option value="65">65</option>
	<option value="70">70</option>
	<option value="80">80</option>
	<option value="85">85</option>
	<option value="90">90</option>
	<option value="95">95</option>
	<option value="100">100</option>
	<option value="110">110</option>
	<option value="120">120</option>
	</select>
    <div class="help-block with-errors"></div></div>
	
	<div class="form-group" class="control-label"><label>'.$lang['boost_console_id'].'</label> <small class="text-muted">'.$lang['boost_console_id_info'].'</small>
	<select class="form-control input-sm" type="text" id="boost_console_id" name="boost_console_id">';
	//get list from nodes table to display 
	$query = "SELECT * FROM nodes where name = 'Button Console' OR name = 'Boost Controller' AND node_id!=0;";
	$result = $conn->query($query);
	if ($result){
		while ($nrow=mysqli_fetch_array($result)) {
			echo '<option value="'.$nrow['node_id'].'">'.$nrow['node_id'].'</option>';
		}
	}
	echo '<option value="0">N/A</option>
	</select>
    <div class="help-block with-errors"></div></div>
	
	<div class="form-group" class="control-label"><label>'.$lang['boost_button_child_id'].'</label> <small class="text-muted">'.$lang['boost_button_child_id_info'].'</small>
	<select class="form-control input-sm" type="text" id="boost_button_child_id" name="boost_button_child_id">
	<option value="0">N/A</option>
	<option value="1">1</option>
	<option value="2">2</option>
	<option value="3">3</option>
	<option value="4">4</option>
	<option value="5">5</option>
	<option value="6">6</option>
	<option value="7">7</option>
	<option value="8">8</option>
	</select>
    <div class="help-block with-errors"></div></div>	';
echo '</div>
            <div class="modal-footer">
				<button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
				<input type="button" name="submit" value="Save" class="btn btn-default login btn-sm" onclick="add_boost()">
				
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
                <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
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
                <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
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
		<a href=\"zone_add.php?id=".$row['id']."\" class=\"btn btn-default btn-xs login\"><span class=\"ionicons ion-edit\"></span></a>&nbsp;&nbsp;
		<a href=\"javascript:delete_zone(".$row['id'].");\"><button class=\"btn btn-danger btn-xs\"><span class=\"glyphicon glyphicon-trash\"></span></button></a>
		</small></span>
		<br>
		</div>";
	} else {
		echo "<div class=\"list-group-item\">
		<i class=\"glyphicon glyphicon-th-large orange\"></i> ".$row['name']."
		<span class=\"pull-right \"><em>&nbsp;&nbsp;<small> ".$lang['max']." ".$row['max_c']."&deg; </em> - ".$lang['sensor'].": ".$row['sensors_id']." - GPIO: ".$row['gpio_pin']."</small></span>
		<br><span class=\"pull-right \"><small>
		<a href=\"zone_add.php?id=".$row['id']."\" class=\"btn btn-default btn-xs login\"><span class=\"ionicons ion-edit\"></span></a>&nbsp;&nbsp;
		<a href=\"javascript:delete_zone(".$row['id'].");\"><button class=\"btn btn-danger btn-xs\"><span class=\"glyphicon glyphicon-trash\"></span></button></a>
		</small></span>
		<br>
		</div>";
	}
}
echo '
</div></div>
			<div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
                <a class="btn btn-default login btn-sm" href="zone_add.php">'.$lang['zone_add'].'</a>
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


//email settings model
echo '
<div class="modal fade" id="email_setting" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['email_settings'].'</h5>
            </div>
            <div class="modal-body">';
$gquery = "SELECT * FROM email";
$gresult = $conn->query($gquery);
$erow = mysqli_fetch_array($gresult);

echo '<p class="text-muted">'.$lang['email_text'].'</p>';
echo '
	<form data-toggle="validator" role="form" method="post" action="settings.php" id="form-join">
	
	<div class="form-group" class="control-label">
	<div class="checkbox checkbox-default checkbox-circle">';
	if ($erow['status'] == '1'){
		echo '<input id="checkbox2" class="styled" type="checkbox" value="1" name="status" checked>';
	}else {
		echo '<input id="checkbox2" class="styled" type="checkbox" value="1" name="status">';
	}
echo ' 

	<label for="checkbox2"> '.$lang['email_enable'].'</label></div></div>
	
	<div class="form-group" class="control-label"><label>'.$lang['email_smtp_server'].'</label>
	<input class="form-control input-sm" type="text" id="e_smtp" name="e_smtp" value="'.$erow['smtp'].'" placeholder="e-mail SMTP Server Address ">
	<div class="help-block with-errors"></div></div>
	
	<div class="form-group" class="control-label"><label>'.$lang['email_username'].' </label>
	<input class="form-control input-sm" type="text" id="e_username" name="e_username" value="'.$erow['username'].'" placeholder="Username for e-mail Server">
	<div class="help-block with-errors"></div></div>

	<div class="form-group" class="control-label"><label>'.$lang['email_password'].' </label>
	<input class="form-control input-sm" type="password" id="e_password" name="e_password" value="'.$erow['password'].'" placeholder="Password for e-mail Server">
	<div class="help-block with-errors"></div></div>

	<div class="form-group" class="control-label"><label>'.$lang['email_from_address'].' </label>
	<input class="form-control input-sm" type="text" id="e_from_address" name="e_from_address" value="'.$erow['from'].'" placeholder="From e-mail" >
	<div class="help-block with-errors"></div></div>

	<div class="form-group" class="control-label"><label>'.$lang['email_to_address'].' </label>
	<input class="form-control input-sm" type="text" id="e_to_address" name="e_to_address" value="'.$erow['to'].'" placeholder="To e-mail Address">
	<div class="help-block with-errors"></div></div>';

echo '</div>
            <div class="modal-footer">
				<button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
				<input type="button" name="submit" value="Save" class="btn btn-default login btn-sm" onclick="setup_email()">
				
            </div>
        </div>
    </div>
</div>';

//Alert Setting model
echo '
<div class="modal fade" id="node_alerts" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['node_alerts_edit'].'</h5>
            </div>
            <div class="modal-body">
<p class="text-muted"> '.$lang['node_alerts_edit_info'].' </p>';
$query = "SELECT * FROM nodes where node_id != 0 AND status = 'Active' ORDER BY node_id asc";
$results = $conn->query($query);
echo '<table>
    <tr>
        <th class="col-xs-1">'.$lang['node_id'].'</th>
        <th class="col-xs-4">'.$lang['name'].'</th>
        <th class="col-xs-5">'.$lang['notice_interval'].'
	<span class="fa fa-info-circle fa-lg text-info" data-container="body" data-toggle="popover" data-placement="left" data-content="'.$lang['notice_interval_info'].'"</span>
	</th>
    </tr>';

while ($row = mysqli_fetch_assoc($results)) {
    echo '
        <tr>
            <td>'.$row['node_id'].'</td>
            <td>'.$row['name'].'</td>
            <td>'.$row['last_seen'].'</td>
            <td><input id="'.$row["node_id"].'" type="value" class="form-control pull-right" style="border: none" name="notice_interval" value="'.$row["notice_interval"].'" placeholder="Notice Interval" required></td>
        </tr>';

}

echo '</table></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
                <input type="button" name="submit" value="Save" class="btn btn-default login btn-sm" onclick="node_alerts()">
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
<p class="text-muted">'.$lang['cron_jobs_text'].'</p>';
echo '	<div class=\"list-group\">';
    $SArr=[['name'=>'Boiler','interval'=>'60','service'=>'/var/www/cron/boiler.php'],
           ['name'=>'DB Cleanup','interval'=>'86400','service'=>'/var/www/cron/db_cleanup.php'],
           ['name'=>'Check Gateway','interval'=>'60','service'=>'/var/www/cron/check_gw.php'],
           ['name'=>'Weather Update','interval'=>'1800','service'=>'/var/www/cron/weather_update.php'],
		   ['name'=>'System Temperature','interval'=>'300','service'=>'/var/www/cron/system_c.php'],
           ['name'=>'Reboot WiFi','interval'=>'120','service'=>'/var/www/cron/reboot_wifi.sh'],
           ['name'=>'PI Connect','interval'=>'60','service'=>'/var/www/cron/piconnect.php']];
    foreach($SArr as $SArrKey=>$SArrVal) {
		// Get last cron job entry from syslogs
		$rval=my_exec("grep -h \"".$SArrVal['service']."\" /var/log/syslog | tail -n 1");
		// If no log entry found in syslogs and no error, check in log rotating file syslog.1
		if($rval['stdout']=='' && $rval['stderr']==''){
			$rval=my_exec("grep -h \"".$SArrVal['service']."\" /var/log/syslog.1 | tail -n 1");
		}
		$logDateLabel='';
		$errLabel='';
		$errMsg='';
		$statusIcon='ion-alert-circled red';
		// Check for possible issues reading logs
        if($rval['stdout']=='') {
            $errLabel='Error: ' . $rval['stderr'];
			if($rval['stderr']=='') {
				$errLabel='Error: No log entries.';
				$errMsg='Logs don\'t contain any entries for CRON job ( ' . $SArrVal['service'].' ). Make sure you have executed ( sudo php /var/www/setup.php ) or CRON jobs are set to be logged in /var/logs/syslog.';
			} elseif (strstr($rval['stderr'],'Permission denied')) {
				$errLabel='Error: Permission denied.';
				$errMsg = $rval['stderr'].'. This function requires read access to syslogs. Please set permission to read for others for syslog and syslog.1 files, e.g.: sudo chmod 644 /var/log/syslog';
			} else {
				$errLabel='Error.';
				$errMsg = $rval['stderr'];
			}
        } else { // Log entry found
			// Split log entry to array
            $rval=explode(" ",$rval['stdout']);
			
			// Get correct year of log entry
			if ($rval[0]=='Dec' && date('M')=='Jan'){
				$logYear=date('Y')-1;
			}else{
				$logYear=date('Y');
			}
			
			// Log DateTime
			$logDateLabel= $rval[1]." ".$rval[0]." ".$rval[2];
			$logDateObj = DateTime::createFromFormat('YMdH:i:s', $logYear.$rval[0].$rval[1].$rval[2]);
			$logDate = $logDateObj->format("Y/m/d H:i:s");
			
			// Current DateTime
			$currDateObj = new DateTime();
			$currDate = $currDateObj->format("Y/m/d H:i:s");
			$currDateObj->sub(new DateInterval('PT'.$SArrVal['interval'].'S')); // Subtract cron job interval for comparison
			
			// Check if Log entry is older that current date minus interval
			if ($logDateObj>=$currDateObj) {
				$statusIcon='ion-checkmark-circled green';
			} else {
				$errLabel='Error: Delay in run time.';
				$errMsg=$SArrVal['name'].' cron job last ran on '.$logDate.', current datetime '.$currDate.', duration between is more than expected interval of '.$SArrVal['interval'].' seconds.';
			}
        }
		
		echo "<a href=\"#\" class=\"list-group-item\">
			<i class=\"ionicons ".$statusIcon."\"></i>  ".$SArrVal['name']."<span class=\"pull-right text-muted small\"><em>Interval in seconds: ".$SArrVal['interval']."</em></span>
			<span class=\"center-block text-muted small\"><em>  Last run time: ".$logDateLabel."</em>
			<div class=\"pull-right text-muted small\"><em>".$errLabel."</em></div>";
		if($errLabel!=''){ // If error exist, display icon for popup notification.
			echo "
			<span class=\"pull-right fa fa-info-circle fa-lg text-info\" data-container=\"body\" data-toggle=\"popover\" data-placement=\"left\" data-content=\"".$errMsg."\"</span>";
		}
		echo "</span>
			</a>";
    }
echo ' </div></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
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
                <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
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
                <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
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
                <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
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
                <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
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
                <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
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
                <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
            </div>
        </div>
    </div>
</div>';
?>

<script>
$(document).ready(function(){
  $('[data-toggle="popover"]').popover();
});
</script>

