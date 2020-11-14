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
				<select class="form-control input-sm" type="text" id="new_lang" name="new_lang">';
				$languages = ListLanguages($language);
				for ($x = 0; $x <=  count($languages) - 1; $x++) {
					echo '<option value="'.$languages[$x][0].'" ' . ($language==$languages[$x][0] ? 'selected' : '') . '>'.$languages[$x][1].'</option>';
				}	
				echo '</select>
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
$query = "select * from zone_view where type = 'Heating'  OR category = 1 order by index_id asc";
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
				$query = "SELECT * FROM nodes where name = 'Boiler Relay' OR name = 'Boiler Controller' OR name = 'GPIO Controller' OR name = 'I2C Controller';";
				$result = $conn->query($query);
				$ncount=mysqli_num_rows($result);
				if ($ncount > 0){
					$query = "SELECT * FROM boiler;";
					$bresult = $conn->query($query);
					$bcount = $bresult->num_rows;
					if ($bcount > 0) { $brow = mysqli_fetch_array($bresult); }
					echo '<p class="text-muted">'.$lang['boiler_info_text'].'</p>';

					echo '
					<form data-toggle="validator" role="form" method="post" action="settings.php" id="form-join">

					<div class="form-group" class="control-label">
						<div class="checkbox checkbox-default checkbox-circle">';
							if ($bcount > 0) {
								if ($bresult and $brow['status'] == '1'){
									echo '<input id="checkbox2" class="styled" type="checkbox" value="1" name="status" checked Disabled>';
								}else {
									echo '<input id="checkbox2" class="styled" type="checkbox" value="1" name="status" Disabled>';
								}
							} else {
								echo '<input id="checkbox2" class="styled" type="checkbox" value="0" name="status" Enabled>';
							}
							echo '<label for="checkbox2"> '.$lang['boiler_enable'].'</label>
						</div>
					</div>
					<!-- /.form-group -->

					<div class="form-group" class="control-label"><label>'.$lang['boiler_name'].'</label>
						<input class="form-control input-sm" type="text" id="name" name="name" value="'.$brow['name'].'" placeholder="Boiler Name to Display on Screen ">
						<div class="help-block with-errors">
						</div>
					</div>
					<!-- /.form-group -->

					<div class="form-group" class="control-label"><label>'.$lang['boiler_node_id'].'</label> <small class="text-muted">'.$lang['boiler_node_id_info'].'</small>
						<select class="form-control input-sm" type="text" id="node_id" name="node_id" onchange=BoilerChildList(this.options[this.selectedIndex].value)>';
						//get current node_id from nodes table 
						if ($bresult) {
							$query = "SELECT * FROM nodes WHERE id ='".$brow['node_id']."' Limit 1;";
							$result = $conn->query($query);
							$row = mysqli_fetch_assoc($result);
							$node_id=$row['node_id'];
					     	   	$node_type=$row['type'];
							$max_child_id=$row['max_child_id'];

							echo '<option value="'.$node_id.'" selected>'.$node_type.' - '.$node_id.'</option>';
							echo "<option></option>";
						}
						//get list from nodes table to display 
						$query = "SELECT * FROM nodes where name = 'Boiler Relay' OR name = 'Boiler Controller' OR name = 'GPIO Controller' OR name = 'I2C Controller';";
						$result = $conn->query($query);
						if ($result){
							while ($nrow=mysqli_fetch_array($result)) {
								echo '<option value="'.$nrow['max_child_id'].'">'.$nrow['type'].' - '.$nrow['node_id'].'</option>';
							}
						}
						echo '</select>
	    					<div class="help-block with-errors">
						</div>
					</div>
					<!-- /.form-group -->
					';

					echo '
					<input class="form-control input-sm" type="hidden" id="selected_node_id" name="selected_node_id" value="'.$node_id.'"/>
				        <input class="form-control input-sm" type="hidden" id="selected_node_type" name="selected_node_type" value="'.$node_type.'"/>
			        	<input class="form-control input-sm" type="hidden" id="gpio_pin_list" name="gpio_pin_list" value="'.implode(",", array_filter(Get_GPIO_List())).'"/>
					<div class="form-group" class="control-label"><label>'.$lang['boiler_node_child_id'].'</label> <small class="text-muted">'.$lang['boiler_relay_gpio_text'].'</small>
						<select class="form-control input-sm" type="text" id="node_child_id" name="node_child_id">
						<option selected>'.$brow['node_child_id'].'</option>';
				        	$pos=strpos($node_type, "GPIO");
					        if($pos !== false) {
				        	        $gpio_list=Get_GPIO_List();
				                	for ($x = 0; $x <= count(array_filter($gpio_list)) - 1; $x++) {
                        					echo "<option value=".$gpio_list[$x].">".$gpio_list[$x]."</option>";
					                }
				        	} else {
				                	for ($x = 1; $x <=  $max_child_id; $x++) {
                        					echo '<option value="'.$x.'">'.$x.'</option>';
					                }
        					}
						echo '
						</select>
	    					<div class="help-block with-errors">
						</div>
					</div>
					<!-- /.form-group -->

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
						<option value="9">9</option>
						<option value="10">10</option>
						<option value="15">15</option>
						</select>
					    	<div class="help-block with-errors">
						</div>
					</div>
					<!-- /.form-group -->

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
						<option value="180">180</option>
						</select>
	    					<div class="help-block with-errors">
						</div>
					</div>
					
					<div class="form-group" class="control-label"><label>'.$lang['boiler_overrun'].'</label> <small class="text-muted">'.$lang['boiler_overrun_info'].'</small>
						<select class="form-control input-sm" type="text" id="overrun" name="overrun">
						<option selected>'.$brow['overrun'].'</option>
						<option value="-1">Keep valve open until next boiler start</option>
						<option value="0">Disable</option>
						<option value="1">1</option>
						<option value="2">2</option>
						<option value="3">3</option>
						<option value="4">4</option>
						<option value="5">5</option>
						<option value="6">6</option>
						<option value="7">7</option>
						<option value="8">8</option>
						<option value="9">9</option>
						</select>
	    					<div class="help-block with-errors">
						</div>
					</div>
					
					
					<!-- /.form-group -->
					';
				} else {
					echo '<p class="text-muted">'.$lang['boiler_no_nodes'].'</p>';
				}
			echo '</div>
			<!-- /.modal-body -->
        	   	<div class="modal-footer">
				<button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>';
				if ($ncount > 0) { echo '<input type="button" name="submit" value="Save" class="btn btn-default login btn-sm" onclick="boiler_settings()">'; }

            		echo '</div>
			<!-- /.modal-footer -->
        	</div>
		<!-- /.modal-content -->
	</div>
	<!-- /.modal-dialog -->
</div>
<!-- /.modal fade -->
';

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
$query = "SELECT * FROM boost_view ORDER BY index_id ASC, minute ASC;";
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
    $minute = $row["minute"];
    $temperature = $row["temperature"];
    $boost_button_id = $row["boost_button_id"];
    $boost_button_child_id = $row["boost_button_child_id"];
    echo '
        <tr>
            <th scope="row"><small>'.$row['name'].'</small></th>
            <td><input id="minute'.$row["id"].'" type="text" class="pull-left text" style="border: none" name="minute" size="3" value="'.$minute.'" placeholder="Minutes" required></td>';
	    if($row["category"] < 2) {
            	echo '<td><input id="temperature'.$row["id"].'" type="text" class="pull-left text" style="border: none" name="temperature" size="3" value="'.$temperature.'" placeholder="Temperature" required></td>
            	<td><input id="boost_button_id'.$row["id"].'" type="text" class="pull-left text" style="border: none" name="button_id"  size="3" value="'.$boost_button_id.'" placeholder="Button ID" required></td>
            	<td><input id="boost_button_child_id'.$row["id"].'" type="text" class="pull-left text" style="border: none" name="button_child_id" size="3" value="'.$boost_button_child_id.'" placeholder="Child ID" required></td>';
	    } else {
            	echo '<td><input id="temperature'.$row["id"].'" type="hidden" class="pull-left text" style="border: none" name="temperature" size="3" value="'.$temperature.'" placeholder="Temperature" required></td>
            	<td><input id="boost_button_id'.$row["id"].'" type="hidden" class="pull-left text" style="border: none" name="button_id"  size="3" value="'.$boost_button_id.'" placeholder="Button ID" required></td>
            	<td><input id="boost_button_child_id'.$row["id"].'" type="hidden" class="pull-left text" style="border: none" name="button_child_id" size="3" value="'.$boost_button_child_id.'" placeholder="Child ID" required></td>';
	    }
	     echo '<input type="hidden" id="zone_id'.$row["id"].'" name="zone_id" value="'.$row["zone_id"].'">
            <td><a href="javascript:delete_boost('.$row["id"].');"><button class="btn btn-danger btn-xs" data-toggle="confirmation" data-title="'.$lang['confirmation'].'" data-content="You are about to DELETE this BOOST Setting"><span class="glyphicon glyphicon-trash"></span></button> </a></td>
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
$query = "SELECT * FROM override_view WHERE category < 2 ORDER BY index_id asc";
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


//nodes model
echo '
<div class="modal fade" id="nodes" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['node_setting'].'</h5>
            </div>
            <div class="modal-body">
<p class="text-muted"> '.$lang['node_settings_text'].' </p>';

$query = "SELECT * FROM nodes where type not like '%Sensor';";
$results = $conn->query($query);
echo '<table class="table table-bordered">
    <tr>
        <th class="col-xs-2"><small>'.$lang['type'].'</small></th>
        <th class="col-xs-2"><small>'.$lang['node_id'].'</small></th>
        <th class="col-xs-2"><small>'.$lang['max_child'].'</small></th>
        <th class="col-xs-4"><small>'.$lang['name'].'</small></th>
        <th class="col-xs-1"></th>
    </tr>';
while ($row = mysqli_fetch_assoc($results)) {
    if($row["name"]=="Boiler Controller" or $row["name"]=="GPIO Controller" or $row["name"]=="I2C Controller") {
        $query = "SELECT * FROM boiler where node_id = {$row['id']} LIMIT 1;";
        $b_results = $conn->query($query);
        $rowcount=mysqli_num_rows($b_results);
        if($rowcount > 0) {
                $content_msg=$lang['confirm_del_controller_use2'];
        } else {
                $content_msg=$lang['confirm_del_controller'];
        }

    } else {
        $query = "SELECT zone.name, zone_controllers.* FROM zone, zone_controllers where (zone_id = zone.id) AND zone_controllers.controler_id  = {$row['id']} LIMIT 1;";
        $z_results = $conn->query($query);
        $rowcount=mysqli_num_rows($z_results);
        if($rowcount > 0) {
			$z_row = mysqli_fetch_assoc($z_results);
			$content_msg=$lang['confirm_del_controller_use']." ".$z_row["name"]." ".$lang['zone'];
        } else {
			$content_msg=$lang['confirm_del_controller'];
        }
    }
    echo '
        <tr>
            <td>'.$row["type"].'</td>
            <td>'.$row["node_id"].'</td>
            <td>'.$row["max_child_id"].'</td>
            <td>'.$row["name"].'</td>
			<td><a href="javascript:delete_node('.$row["id"].');"><button class="btn btn-danger btn-xs" data-toggle="confirmation" data-title="'.$lang['confirmation'].'" data-content="'.$content_msg.'"><span class="glyphicon glyphicon-trash"></span></button> </a></td>
        </tr>';
}
echo '</table></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
                <button type="button" class="btn btn-default login btn-sm" data-href="#" data-toggle="modal" data-target="#add_node">'.$lang['node_add'].'</button>
            </div>
        </div>
    </div>
</div>';


//Add Node
echo '
<div class="modal fade" id="add_node" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['node_add'].'</h5>
            </div>
            <div class="modal-body">';
echo '<p class="text-muted">'.$lang['node_add_info_text'].'</p>
	
	<form data-toggle="validator" role="form" method="post" action="settings.php" id="form-join">

	<div class="form-group" class="control-label"><label>'.$lang['node_type'].'</label> <small class="text-muted">'.$lang['node_type_info'].'</small>

	<select class="form-control input-sm" type="text" id="node_type" onchange=show_hide_devices() name="node_type">
	<option value="I2C" selected="selected">I2C</option>
	<option value="GPIO">GPIO</option>
        <option value="Tasmota">Tasmota</option>
	</select>
    <div class="help-block with-errors"></div></div>


	<div class="form-group" class="control-label"><label>'.$lang['node_id'].'</label> <small class="text-muted">'.$lang['node_id_info'].'</small>
	<input class="form-control input-sm" type="text" id="add_node_id" name="add_node_id" value="" placeholder="'.$lang['node_id'].'">
	<div class="help-block with-errors"></div></div>
		
	<div class="form-group" class="control-label" id="add_devices_label" style="display:block"><label>'.$lang['node_child_id'].'</label> <small class="text-muted">'.$lang['node_child_id_info'].'</small>
	<input class="form-control input-sm" type="text" id="nodes_max_child_id" name="nodes_max_child_id" value="0" placeholder="'.$lang['node_max_child_id'].'">
	<div class="help-block with-errors"></div></div>

</div>
            <div class="modal-footer">
				<button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
				<input type="button" name="submit" value="Save" class="btn btn-default login btn-sm" onclick="add_node()">
				
            </div>
        </div>
    </div>
</div>';

//Zone Type
echo '
<div class="modal fade" id="zone_types" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['zone_type'].'</h5>
            </div>
            <div class="modal-body">
<p class="text-muted"> '.$lang['zone_type_text'].' </p>';

echo '<table class="table table-bordered">
    <tr>
        <th class="col-xs-4"><small>'.$lang['type'].'</small></th>
        <th class="col-xs-7"><small>'.$lang['category'].'</small></th>
        <th class="col-xs-1"></th>
    </tr>';

$query = "SELECT * FROM zone_type where `purge`=0;";
$results = $conn->query($query);
while ($row = mysqli_fetch_assoc($results)) {
    $query = "SELECT * FROM `zone` WHERE `type_id` = '".$row['id']."' LIMIT 1;";
    $t_results = $conn->query($query);
    $rowcount=mysqli_num_rows($t_results);
    if($rowcount > 0) {
        $content_msg=$lang['confirm_dell_active_zone_type'];
    } else {
        $content_msg=$lang['confirm_dell_de_active_zone_type'];
    }

    echo '
        <tr>
            <td>'.$row["type"].'</td>
            <td>'.$lang['zone_category'.$row["category"]].'</td>
            <td><a href="javascript:delete_zone_type('.$row["id"].');"><button class="btn btn-danger btn-xs" data-toggle="confirmation" data-title="'.$lang['confirmation'].'" data-content="'.$content_msg.'"><span class="glyphicon glyphicon-trash"></span></button> </a></td>
        </tr>';
}
echo '</table></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
                <button type="button" class="btn btn-default login btn-sm" data-href="#" data-toggle="modal" data-target="#add_zone_type">'.$lang['zone_type_add'].'</button>
            </div>
        </div>
    </div>
</div>';

//Add Zone Type
echo '
<div class="modal fade" id="add_zone_type" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['node_add'].'</h5>
            </div>
            <div class="modal-body">';
echo '<p class="text-muted">'.$lang['zone_type_add_info_text'].'</p>

        <form data-toggle="validator" role="form" method="post" action="settings.php" id="form-join">

        <div class="form-group" class="control-label"><label>'.$lang['zone_type'].'</label> <small class="text-muted">'.$lang['zone_type_info'].'</small>
        <input class="form-control input-sm" type="text" id="zone_type" name="zone_type" value="" placeholder="'.$lang['zone_type'].'">
        <div class="help-block with-errors"></div></div>

        <div class="form-group" class="control-label"><label>'.$lang['category'].'</label> <small class="text-muted">'.$lang['zone_category_info'].'</small>

        <select class="form-control input-sm" type="text" id="category" name="category">
        <option value=0 selected>'.$lang['zone_category0'].'</option>
        <option value=1>'.$lang['zone_category1'].'</option>
        <option value=2>'.$lang['zone_category2'].'</option>
        </select>
    <div class="help-block with-errors"></div></div>

</div>
            <div class="modal-footer">
                                <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
                                <input type="button" name="submit" value="Save" class="btn btn-default login btn-sm" onclick="add_zone_type()">

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
$query = "SELECT * FROM nodes where name LIKE '%Sensor' ORDER BY node_id asc;";
$results = $conn->query($query);
echo '	<div class=\"list-group\">';
while ($row = mysqli_fetch_assoc($results)) {
	$batquery = "select * from nodes_battery where node_id = {$row['node_id']} ORDER BY id desc limit 1;";
	$batresults = $conn->query($batquery);
	$brow = mysqli_fetch_array($batresults);
	//check if sensors in use by any zone 
	$query = "SELECT * FROM zone_sensors where sensor_id = {$row['id']} Limit 1;";
	$zresult = $conn->query($query);
	$rcount = mysqli_num_rows($zresult);
	echo "<div class=\"list-group-item\"><i class=\"ionicons ion-thermometer red\"></i> ".$row['node_id'];
	if ($row['ms_version'] > 0){echo "- <i class=\"fa fa-battery-full\"></i> ".round($brow ['bat_level'],0)."% - ".$brow ['bat_voltage'];}
	echo "<span class=\"pull-right text-muted small\"><em>".$row['last_seen']."</em> ";
	//if sensor in use disable delete button
	if ($rcount > 0){
		echo '<a href="javascript:delete_node('.$row["id"].');">&nbsp;&nbsp;<button class="btn btn-danger btn-xs disabled" data-toggle="confirmation" data-title="'.$lang['confirmation'].'" data-content="#"><span class="glyphicon glyphicon-trash"></span></button></a>';
	//if sensors not in use by zone enable delete button
	}else{
		echo '<a href="javascript:delete_node('.$row["id"].');">&nbsp;&nbsp;<button class="btn btn-danger btn-xs" data-toggle="confirmation" data-title="'.$lang['confirmation'].'" data-content="'.$lang['confirm_del_sensor'].'"><span class="glyphicon glyphicon-trash"></span></button></a>';
	}
	echo "</span></div> "; 	
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
$query = "select * from zone order by index_id asc";
$results = $conn->query($query);
echo '	<div class=\"list-group\">';
while ($row = mysqli_fetch_assoc($results)) {
        if($row['status'] == 1) {
                $content_msg=$lang['confirm_dell_active_zone'];
        } else {
                $content_msg=$lang['confirm_dell_de_active_zone'];
        }
	echo "<div class=\"list-group-item\">
        <i class=\"glyphicon glyphicon-th-large orange\"></i> ".$row['name']."";
        $query = "select * from zone_view WHERE id = '{$row['id']}'order by index_id asc";
        $vresult = $conn->query($query);
        while ($vrow = mysqli_fetch_assoc($vresult)) {
                if ($vrow['category'] == 2) {
                        echo "<span class=\"pull-right \"><em>&nbsp;&nbsp;<small> ".$lang['controller'].": ".$vrow['controller_type'].": ".$vrow['controler_id']."-".$vrow['controler_child_id']."</small></span><br>";
                } else {
                        echo "<span class=\"pull-right \"><em>&nbsp;&nbsp;<small> ".$lang['max']." ".$vrow['max_c']."&deg; </em> - ".$lang['sensor'].": ".$vrow['sensors_id']." - ".$vrow['controller_type'].": ".$vrow['controler_id']."-".$vrow['controler_child_id']."</small></span><br>";
                }
        }
        echo "<span class=\"pull-right \"><small>
        <a href=\"zone.php?id=".$row['id']."\" class=\"btn btn-default btn-xs login\"><span class=\"ionicons ion-edit\"></span></a>&nbsp;&nbsp;
        <a href=\"javascript:delete_zone(".$row['id'].");\"><button class=\"btn btn-danger btn-xs\" data-toggle=\"confirmation\" data-title=".$lang['confirmation']." data-content=\"$content_msg\"><span class=\"glyphicon glyphicon-trash\"></span></button></a>
        </small></span>
        <br>
        </div>";
}
echo '
</div></div>
			<div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
                <a class="btn btn-default login btn-sm" href="zone.php">'.$lang['zone_add'].'</a>
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
				if ($grow['type']=='wifi'){
					echo $lang['smart_home_gateway_text_wifi'];
					$display_wifi = "display:block";
					$display_serial = "display:none";
				} elseif ($grow['type']=='serial') {
					echo $lang['smart_home_gateway_text_serial'];
                                        $display_wifi = "display:none";
                                        $display_serial = "display:block";
 				}
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
						<label for="checkbox0"> '.$lang['smart_home_gateway_enable'].'</label>
					</div>
				</div>
                               	<!-- /.form-group -->

                                <div class="form-group" class="control-label"><label>'.$lang['smart_home_gateway_type'].'</label>
                                        <select class="form-control input-sm" type="text" id="gw_type" name="gw_type" onchange=gw_location()>
                                        <option value="wifi" ' . ($grow['type']=='wifi' ? 'selected' : '') . '>'.$lang['wifi'].'</option>
                                        <option value="serial" ' . ($grow['type']=='serial' ? 'selected' : '') . '>'.$lang['serial'].'</option>
                                        </select>
                                        <div class="help-block with-errors">
                                        </div>
                                </div>
                                <!-- /.form-group -->

                                <div class="form-group" class="control-label" id="wifi_gw" style="'.$display_wifi.'"><label>'.$lang['wifi_gateway_location'].'</label>
                                	<input class="form-control input-sm" type="text" id="wifi_location" name="wifi_location" value="'.$grow['location'].'" placeholder="Gateway Location">
                                        <div class="help-block with-errors">
                                        </div>
                                </div>
                                <!-- /.form-group -->

                                <div class="form-group" class="control-label" id="serial_gw" style="'.$display_serial.'"><label>'.$lang['serial_gateway_location'].'</label>
                                        <select class="form-control input-sm" type="text" id="serial_location" name="serial_location">
                                        <option selected>'.$grow['location'].'</option>';
                                        $dev_tty = glob("/dev/tty*");
                                        for ($x = 0; $x <=  count($dev_tty) - 1; $x++) {
                                                echo '<option value="'.$dev_tty[$x].'" ' . '>'.$dev_tty[$x].'</option>';
                                        }
                                        echo '</select>
                                        <div class="help-block with-errors">
                                        </div>
                                </div>
                                <!-- /.form-group -->

                                <div class="form-group" class="control-label" id="wifi_port" style="'.$display_wifi.'"><label>'.$lang['wifi_gateway_port'].' </label>
                                        <input class="form-control input-sm" type="text" id="wifi_port_num" name="wifi_port_num" value="'.$grow['port'].'" placeholder="Gateway Port">
                                        <div class="help-block with-errors">
                                        </div>
                                </div>
                                <!-- /.form-group -->

                                <div class="form-group" class="control-label" id="serial_port" style="'.$display_serial.'"><label>'.$lang['serial_gateway_port'].' </label>
                                        <select class="form-control input-sm" type="text" id="serial_port_speed" name="serial_port_speed">
                                                <option selected>'.$grow['port'].'</option>
                                                <option value="9600">9600</option>
                                                <option value="19200">19200</option>
                                                <option value="38400">38400</option>
                                                <option value="57600">57600</option>
                                                <option value="74880">74880</option>
                                                <option value="115200">115200</option>
                                                <option value="230400">233400</option>
                                                <option value="250000">250000</option>
                                                <option value="500000">500000</option>
                                                <option value="1000000">1000000</option>
                                                <option value="2000000">2000000</option>
                                                </select>
                                        <div class="help-block with-errors">
                                        </div>
                                </div>
                                <!-- /.form-group -->

				<div class="form-group" class="control-label"><label>'.$lang['timeout'].' </label>
					<input class="form-control input-sm" type="text" id="gw_timout" name="gw_timout" value="'.$grow['timout'].'" placeholder="Gateway Timeout">
					<div class="help-block with-errors">
					</div>
				</div>
                                <!-- /.form-group -->

				<div class="form-group" class="control-label"><label>'.$lang['smart_home_gateway_version'].' </label>
					<input class="form-control input-sm" type="text" id="gw_version" name="gw_version" value="'.$grow['version'].'" disabled>
					<div class="help-block with-errors">
					</div>
				</div>
                                <!-- /.form-group -->

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
				echo '</div>
                                <!-- /.list-group -->
			</div>
			<!-- /.modal-body -->
            		<div class="modal-footer">
				<a href="javascript:resetgw('.$grow['pid'].')" class="btn btn-default login btn-sm btn-edit">Reset GW</a>
				<a href="javascript:find_gw()" class="btn btn-default login btn-sm btn-edit">Search GW</a>
				<input type="button" name="submit" value="Save" class="btn btn-default login btn-sm" onclick="setup_gateway()">
				<button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
            		</div>
			<!-- /.modal-footer -->
        	</div>
		<!-- /.modal-content -->
    	</div>
	<!-- /.modal-dialog -->
</div>
<!-- /.modal fade -->
';

//Add HTTP Message model
echo '
<div class="modal fade" id="add_on_http" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>';
                $query = "SELECT `name` FROM `zone_view` WHERE `controller_type` = 'Tasmota' ORDER BY `name` ASC;";
                $zresult = $conn->query($query);
                $zcount = $zresult->num_rows;
                $query = "SELECT `node_id` FROM `nodes` WHERE `type` = 'Tasmota' ORDER BY `node_id` ASC;";
                $nresult = $conn->query($query);
                $ncount = $nresult->num_rows;
                if ($zcount + $ncount == 0) {
                        echo '<h5 class="modal-title">'.$lang['no_tasmota'].'</h5>';
                } else {
                        echo '<h5 class="modal-title">'.$lang['add_on_settings'].'</h5>';
                }
            echo '</div>
            <div class="modal-body">';

if ($zcount + $ncount > 0) {
        echo '<p class="text-muted"> '.$lang['add_on_settings_text'].' </p>';

        $query = "SELECT http_messages.*, nodes.type FROM http_messages, nodes WHERE http_messages.node_id = nodes.node_id;";
        $results = $conn->query($query);
        echo '<table class="table table-bordered">
        <tr>
                <th class="col-xs-2"><small>'.$lang['type'].'</small></th>
                <th class="col-xs-3"><small>'.$lang['zone_name'].'</small></th>
                <th class="col-xs-2"><small>'.$lang['message_type'].'</small></th>
                <th class="col-xs-2"><small>'.$lang['command'].'</small></th>
                <th class="col-xs-2"><small>'.$lang['parameter'].'</small></th>
                <th class="col-xs-1"></th>
        </tr>';
        while ($row = mysqli_fetch_assoc($results)) {
                echo '
                        <tr>
                        <td>'.$row["type"].'</td>
                        <td>'.$row["zone_name"].'</td>
                        <td>'.$row["message_type"].'</td>
                        <td>'.$row["command"].'</td>
                        <td>'.$row["parameter"].'</td>
                        <td><a href="javascript:delete_http_msg('.$row["id"].');"><button class="btn btn-danger btn-xs" data-toggle="confirmation" data-title="'.$lang['confirmation'].'" data-content="'.$content_msg.'"><span class="glyphicon glyphicon-trash"></span></button> </a></td>
                        </tr>';
        }
}
echo '</table></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>';
                if ($zcount > 0) {
			echo '<button type="button" class="btn btn-default login btn-sm" data-href="#" data-toggle="modal" data-target="#zone_add_http_msg">'.$lang['zone_add_http_msg'].'</button>';
                }
                if ($ncount > 0) {
			echo '<button type="button" class="btn btn-default login btn-sm" data-href="#" data-toggle="modal" data-target="#node_add_http_msg">'.$lang['node_add_http_msg'].'</button>';
                }
            echo '</div>
        </div>
    </div>
</div>';

//Add New HTTP Message based on Zone Name
echo '
<div class="modal fade" id="zone_add_http_msg" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['add_on_messages'].'</h5>
            </div>
            <div class="modal-body">';
echo '<p class="text-muted">'.$lang['add_on_add_info_text'].'</p>
        <form data-toggle="validator" role="form" method="post" action="settings.php" id="form-join">
       	<div class="form-group" class="control-label"><label>'.$lang['zone_name'].'</label> <small class="text-muted">'.$lang['add_zone_name_info'].'</small>
        <select class="form-control input-sm" type="text" id="zone_http_id" name="zone_http_id">';
        while ($zrow=mysqli_fetch_array($zresult)) {
        	echo '<option value="'.$zrow['name'].'">'.$zrow['name'].'</option>';
        }
        echo '</select>
    	<div class="help-block with-errors"></div></div>

	<div class="form-group" class="control-label"><label>'.$lang['message_type'].'</label> <small class="text-muted">'.$lang['message_type_info'].'</small>
	<select <input class="form-control input-sm" type="text" id="zone_add_msg_type" name="zone_add_msg_type" value="" placeholder="'.$lang['message_type'].'">
	<option selected value="0">0 </option>
        <option value="1">1 </option>
	</select>
	<div class="help-block with-errors"></div></div>

	<div class="form-group" class="control-label"><label>'.$lang['http_command'].'</label> <small class="text-muted">'.$lang['http_command_info'].'</small>
	<input class="form-control input-sm" type="text" id="zone_http_command" name="zone_http_command" value="" placeholder="'.$lang['http_command'].'">
	<div class="help-block with-errors"></div></div>

        <div class="form-group" class="control-label"><label>'.$lang['http_parameter'].'</label> <small class="text-muted">'.$lang['http_parameter_info'].'</small>
        <input class="form-control input-sm" type="text" id="zone_http_parameter" name="zone_http_parameter" value="" placeholder="'.$lang['http_parameter'].'">
        <div class="help-block with-errors"></div></div>
</div>
            <div class="modal-footer">
				<button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
                                <input type="button" name="submit" value="Save" class="btn btn-default login btn-sm" onclick="add_zone_http_msg()">
            </div>
        </div>
    </div>
</div>';

//Add New HTTP Message based on Node ID
echo '
<div class="modal fade" id="node_add_http_msg" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['add_on_messages'].'</h5>
            </div>
            <div class="modal-body">';
echo '<p class="text-muted">'.$lang['add_on_add_info_text'].'</p>
        <form data-toggle="validator" role="form" method="post" action="settings.php" id="form-join">
        <div class="form-group" class="control-label"><label>'.$lang['node_id'].'</label> <small class="text-muted">'.$lang['add_node_id_info'].'</small>
        <select class="form-control input-sm" type="text" id="node_http_id" name="node_http_id">';
        while ($nrow=mysqli_fetch_array($nresult)) {
                echo '<option value="'.$nrow['node_id'].'">'.$nrow['node_id'].'</option>';
        }
        echo '</select>
        <div class="help-block with-errors"></div></div>

        <div class="form-group" class="control-label"><label>'.$lang['message_type'].'</label> <small class="text-muted">'.$lang['message_type_info'].'</small>
        <select <input class="form-control input-sm" type="text" id="node_add_msg_type" name="node_add_msg_type" value="" placeholder="'.$lang['message_type'].'">
        <option selected value="0">0 </option>
        <option value="1">1 </option>
        </select>
        <div class="help-block with-errors"></div></div>

        <div class="form-group" class="control-label"><label>'.$lang['http_command'].'</label> <small class="text-muted">'.$lang['http_command_info'].'</small>
        <input class="form-control input-sm" type="text" id="node_http_command" name="node_http_command" value="" placeholder="'.$lang['http_command'].'">
        <div class="help-block with-errors"></div></div>

        <div class="form-group" class="control-label"><label>'.$lang['http_parameter'].'</label> <small class="text-muted">'.$lang['http_parameter_info'].'</small>
        <input class="form-control input-sm" type="text" id="node_http_parameter" name="node_http_parameter" value="" placeholder="'.$lang['http_parameter'].'">
        <div class="help-block with-errors"></div></div>
</div>
            <div class="modal-footer">
                                <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
                                <input type="button" name="submit" value="Save" class="btn btn-default login btn-sm" onclick="add_node_http_msg()">
            </div>
        </div>
    </div>
</div>';

//network settings model
echo '
<div class="modal fade" id="network_setting" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['network_settings'].'</h5>
            </div>
            <div class="modal-body">';

$query = "SELECT * FROM `network_settings` ORDER BY `id` ASC;";
$result = $conn->query($query);

$rowArray = array();

while($row = mysqli_fetch_assoc($result)) {
   $rowArray[] = $row;
}

echo '<p class="text-muted">'.$lang['network_text'].'</p>';
echo '
        <form data-toggle="validator" role="form" method="post" action="settings.php" id="form-join">

        <input class="form-control input-sm" type="hidden" id="n_int_type" name="n_int_type" value="'.$rowArray[0]['interface_type'].'"/>
        <div class="form-group" class="control-label"><label>'.$lang['network_interface'].'</label>
                <select class="form-control input-sm" type="text" id="n_int_num" name="n_int_num" onchange=change(this.options[this.selectedIndex].value)>
                <option value=0>wlan0</option>
                <option value=1>wlan1</option>
                <option value=2>eth0</option>
                <option value=3>eth1</option>
                </select>
                <div class="help-block with-errors">
                </div>
        </div>
        <div class="form-group" class="control-label"><label>'.$lang['network_primary'].'</label>
                <select class="form-control input-sm" type="text" id="n_primary" name="n_primary">
                <option value=0>No</option>
                <option selected value=1>Yes</option>
                </select>
                <div class="help-block with-errors">
                </div>
        </div>
        <div class="form-group" class="control-label"><label>'.$lang['network_ap_mode'].'</label>
                <select class="form-control input-sm" type="text" id="n_ap_mode" name="n_ap_mode">
                <option selected value=0>No</option>
                <option value=1>Yes</option>
                </select>
                <div class="help-block with-errors">
                </div>
        </div>
        <div class="form-group" class="control-label"><label>'.$lang['network_mac_address'].'</label>
                <input class="form-control input-sm" type="text" id="n_mac" name="n_mac" value="'.$rowArray[0]['mac_address'].'" placeholder="MAC Address">
                <div class="help-block with-errors">
                </div>
        </div>
        <div class="form-group" class="control-label"><label>'.$lang['network_hostname'].'</label>
                <input class="form-control input-sm" type="text" id="n_hostname" name="n_hostname" value="'.$rowArray[0]['hostname'].'" placeholder="Hostname">
                <div class="help-block with-errors">
                </div>
        </div>
        <div class="form-group" class="control-label"><label>'.$lang['network_ip_address'].'</label>
                <input class="form-control input-sm" type="text" id="n_ip" name="n_ip" value="'.$rowArray[0]['ip_address'].'" placeholder="IP Address">
                <div class="help-block with-errors">
                </div>
        </div>
        <div class="form-group" class="control-label"><label>'.$lang['network_gateway_address'].'</label>
                <input class="form-control input-sm" type="text" id="n_gateway" name="n_gateway" value="'.$rowArray[0]['gateway_address'].'" placeholder="Gateway Address">
                <div class="help-block with-errors">
                </div>
        </div>
        <div class="form-group" class="control-label"><label>'.$lang['network_net_mask'].'</label>
                <input class="form-control input-sm" type="text" id="n_net_mask" name="n_net_mask" value="'.$rowArray[0]['net_mask'].'" placeholder="Net Mask">
                <div class="help-block with-errors">
                </div>
        </div>
        <div class="form-group" class="control-label"><label>'.$lang['network_dns1_address'].'</label>
                <input class="form-control input-sm" type="text" id="n_dns1" name="n_dns1" value="'.$rowArray[0]['dns1_address'].'" placeholder="DNS1 Address">
                <div class="help-block with-errors">
                </div>
        </div>
        <div class="form-group" class="control-label"><label>'.$lang['network_dns2_address'].'</label>
                <input class="form-control input-sm" type="text" id="n_dns2" name="n_dns2" value="'.$rowArray[0]['dns2_address'].'" placeholder="DNS2 Address">
                <div class="help-block with-errors">
                </div>
        </div>

        </div>
        <!-- /.modal-body -->

            <div class="modal-footer">
                                <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
                                <input type="button" name="submit" value="Save" class="btn btn-default login btn-sm" onclick="setup_network()">
            </div>
            <!-- /.modal-footer -->
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal-fade -->
';
?>
<script>
function change(value){
        var jArray = <?php echo json_encode($rowArray); ?>;
        var valuetext = value;
        document.getElementById("n_primary").value = jArray[value]['primary_interface'];
        document.getElementById("n_mac").value = jArray[value]['mac_address'];
        document.getElementById("n_hostname").value = jArray[value]['hostname'];
        document.getElementById("n_ip").value = jArray[value]['ip_address'];
        document.getElementById("n_gateway").value = jArray[value]['gateway_address'];
        document.getElementById("n_net_mask").value = jArray[value]['net_mask'];
        document.getElementById("n_dns1").value = jArray[value]['dns1_address'];
        document.getElementById("n_dns2").value = jArray[value]['dns2_address'];
        switch (value) {
                case '0':
                        document.getElementById("n_int_type").value = 'wlan0';
                        break;
                case '1':
                        document.getElementById("n_int_type").value = 'wlan1';
                        break;
                case '2':
                        document.getElementById("n_int_type").value = 'eth0';
                        break;
                case '3':
                        document.getElementById("n_int_type").value = 'eth1';
                        break;
                default:
        }
}
</script>
<?php

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
		echo '<input id="checkbox3" class="styled" type="checkbox" value="1" name="status" checked>';
	}else {
		echo '<input id="checkbox3" class="styled" type="checkbox" value="1" name="status">';
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
$query = "SELECT * FROM nodes where status = 'Active' ORDER BY node_id asc";
$results = $conn->query($query);
echo '<table>
    <tr>
        <th class="col-xs-1">'.$lang['node_id'].'</th>
        <th class="col-xs-2">'.$lang['name'].'</th>
        <th class="col-xs-3">'.$lang['last_seen'].'</th>
        <th class="col-xs-3">'.$lang['notice_interval'].'
        <span class="fa fa-info-circle fa-lg text-info" data-container="body" data-toggle="popover" data-placement="left" data-content="'.$lang['notice_interval_info'].'"</span></th>
        <th class="col-xs-3">'.$lang['min_battery_level'].'
        <span class="fa fa-info-circle fa-lg text-info" data-container="body" data-toggle="popover" data-placement="left" data-content="'.$lang['battery_level_info'].'"</span></th>
    </tr>';

while ($row = mysqli_fetch_assoc($results)) {
    $query = "SELECT * FROM nodes_battery where  node_id ='".$row['node_id']."'; ";
    $result = $conn->query($query);
    $count = $result->num_rows;
    echo '
        <tr>
            <td>'.$row['node_id'].'</td>
            <td>'.$row['name'].'</td>
            <td>'.$row['last_seen'].'</td>
            <td><input id="interval'.$row["node_id"].'" type="value" class="form-control pull-right" style="border: none" name="notice_interval" value="'.$row["notice_interval"].'" placeholder="Notice Interval" required></td>';
	    if($count == 0) {
	            echo '<td><input id="min_value'.$row["node_id"].'" type="value" class="form-control pull-right" style="border: none" name="min_value" value="N/A" readonly="readonly" placeholder="Min Value"></td>';
	    } else {
                    echo '<td><input id="min_value'.$row["node_id"].'" type="value" class="form-control pull-right" style="border: none" name="min_value" value="'.$row["min_value"].'" placeholder="Min Value"></td>';
	    }
        echo '</tr>';

}

echo '</table></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
                <input type="button" name="submit" value="Save" class="btn btn-default login btn-sm" onclick="node_alerts()">
            </div>
        </div>
    </div>
</div>';


//Time Zone
echo '
<div class="modal fade" id="time_zone" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['time_zone'].'</h5>
            </div>
            <div class="modal-body">
                <p class="text-muted"> '.$lang['time_zone_text'].'</p>
				<form data-toggle="validator" role="form" method="post" action="settings.php" id="form-join">
				<div class="form-group" class="control-label"><label>'.$lang['time_zone'].'</label>
				<select class="form-control input-sm" type="number" id="new_time_zone" name="new_time_zone" >
				
				<option selected >'.settings($conn, 'timezone').'</option>';
$timezones = array(
    'Pacific/Midway'       => "(GMT-11:00) Midway Island",
    'US/Samoa'             => "(GMT-11:00) Samoa",
    'US/Hawaii'            => "(GMT-10:00) Hawaii",
    'US/Alaska'            => "(GMT-09:00) Alaska",
    'US/Pacific'           => "(GMT-08:00) Pacific Time (US &amp; Canada)",
    'America/Tijuana'      => "(GMT-08:00) Tijuana",
    'US/Arizona'           => "(GMT-07:00) Arizona",
    'US/Mountain'          => "(GMT-07:00) Mountain Time (US &amp; Canada)",
    'America/Chihuahua'    => "(GMT-07:00) Chihuahua",
    'America/Mazatlan'     => "(GMT-07:00) Mazatlan",
    'America/Mexico_City'  => "(GMT-06:00) Mexico City",
    'America/Monterrey'    => "(GMT-06:00) Monterrey",
    'Canada/Saskatchewan'  => "(GMT-06:00) Saskatchewan",
    'US/Central'           => "(GMT-06:00) Central Time (US &amp; Canada)",
    'US/Eastern'           => "(GMT-05:00) Eastern Time (US &amp; Canada)",
    'US/East-Indiana'      => "(GMT-05:00) Indiana (East)",
    'America/Bogota'       => "(GMT-05:00) Bogota",
    'America/Lima'         => "(GMT-05:00) Lima",
    'America/Caracas'      => "(GMT-04:30) Caracas",
    'Canada/Atlantic'      => "(GMT-04:00) Atlantic Time (Canada)",
    'America/La_Paz'       => "(GMT-04:00) La Paz",
    'America/Santiago'     => "(GMT-04:00) Santiago",
    'Canada/Newfoundland'  => "(GMT-03:30) Newfoundland",
    'America/Buenos_Aires' => "(GMT-03:00) Buenos Aires",
    'Greenland'            => "(GMT-03:00) Greenland",
    'Atlantic/Stanley'     => "(GMT-02:00) Stanley",
    'Atlantic/Azores'      => "(GMT-01:00) Azores",
    'Atlantic/Cape_Verde'  => "(GMT-01:00) Cape Verde Is.",
    'Africa/Casablanca'    => "(GMT) Casablanca",
    'Europe/Dublin'        => "(GMT) Dublin",
    'Europe/Lisbon'        => "(GMT) Lisbon",
    'Europe/London'        => "(GMT) London",
    'Africa/Monrovia'      => "(GMT) Monrovia",
    'Europe/Amsterdam'     => "(GMT+01:00) Amsterdam",
    'Europe/Belgrade'      => "(GMT+01:00) Belgrade",
    'Europe/Berlin'        => "(GMT+01:00) Berlin",
    'Europe/Bratislava'    => "(GMT+01:00) Bratislava",
    'Europe/Brussels'      => "(GMT+01:00) Brussels",
    'Europe/Budapest'      => "(GMT+01:00) Budapest",
    'Europe/Copenhagen'    => "(GMT+01:00) Copenhagen",
    'Europe/Ljubljana'     => "(GMT+01:00) Ljubljana",
    'Europe/Madrid'        => "(GMT+01:00) Madrid",
    'Europe/Paris'         => "(GMT+01:00) Paris",
    'Europe/Prague'        => "(GMT+01:00) Prague",
    'Europe/Rome'          => "(GMT+01:00) Rome",
    'Europe/Sarajevo'      => "(GMT+01:00) Sarajevo",
    'Europe/Skopje'        => "(GMT+01:00) Skopje",
    'Europe/Stockholm'     => "(GMT+01:00) Stockholm",
    'Europe/Vienna'        => "(GMT+01:00) Vienna",
    'Europe/Warsaw'        => "(GMT+01:00) Warsaw",
    'Europe/Zagreb'        => "(GMT+01:00) Zagreb",
    'Europe/Athens'        => "(GMT+02:00) Athens",
    'Europe/Bucharest'     => "(GMT+02:00) Bucharest",
    'Africa/Cairo'         => "(GMT+02:00) Cairo",
    'Africa/Harare'        => "(GMT+02:00) Harare",
    'Europe/Helsinki'      => "(GMT+02:00) Helsinki",
    'Europe/Istanbul'      => "(GMT+02:00) Istanbul",
    'Asia/Jerusalem'       => "(GMT+02:00) Jerusalem",
    'Europe/Kiev'          => "(GMT+02:00) Kyiv",
    'Europe/Minsk'         => "(GMT+02:00) Minsk",
    'Europe/Riga'          => "(GMT+02:00) Riga",
    'Europe/Sofia'         => "(GMT+02:00) Sofia",
    'Europe/Tallinn'       => "(GMT+02:00) Tallinn",
    'Europe/Vilnius'       => "(GMT+02:00) Vilnius",
    'Asia/Baghdad'         => "(GMT+03:00) Baghdad",
    'Asia/Kuwait'          => "(GMT+03:00) Kuwait",
    'Africa/Nairobi'       => "(GMT+03:00) Nairobi",
    'Asia/Riyadh'          => "(GMT+03:00) Riyadh",
    'Europe/Moscow'        => "(GMT+03:00) Moscow",
    'Asia/Tehran'          => "(GMT+03:30) Tehran",
    'Asia/Baku'            => "(GMT+04:00) Baku",
    'Europe/Volgograd'     => "(GMT+04:00) Volgograd",
    'Asia/Muscat'          => "(GMT+04:00) Muscat",
    'Asia/Tbilisi'         => "(GMT+04:00) Tbilisi",
    'Asia/Yerevan'         => "(GMT+04:00) Yerevan",
    'Asia/Kabul'           => "(GMT+04:30) Kabul",
    'Asia/Karachi'         => "(GMT+05:00) Karachi",
    'Asia/Tashkent'        => "(GMT+05:00) Tashkent",
    'Asia/Kolkata'         => "(GMT+05:30) Kolkata",
    'Asia/Kathmandu'       => "(GMT+05:45) Kathmandu",
    'Asia/Yekaterinburg'   => "(GMT+06:00) Ekaterinburg",
    'Asia/Almaty'          => "(GMT+06:00) Almaty",
    'Asia/Dhaka'           => "(GMT+06:00) Dhaka",
    'Asia/Novosibirsk'     => "(GMT+07:00) Novosibirsk",
    'Asia/Bangkok'         => "(GMT+07:00) Bangkok",
    'Asia/Jakarta'         => "(GMT+07:00) Jakarta",
    'Asia/Krasnoyarsk'     => "(GMT+08:00) Krasnoyarsk",
    'Asia/Chongqing'       => "(GMT+08:00) Chongqing",
    'Asia/Hong_Kong'       => "(GMT+08:00) Hong Kong",
    'Asia/Kuala_Lumpur'    => "(GMT+08:00) Kuala Lumpur",
    'Australia/Perth'      => "(GMT+08:00) Perth",
    'Asia/Singapore'       => "(GMT+08:00) Singapore",
    'Asia/Taipei'          => "(GMT+08:00) Taipei",
    'Asia/Ulaanbaatar'     => "(GMT+08:00) Ulaan Bataar",
    'Asia/Urumqi'          => "(GMT+08:00) Urumqi",
    'Asia/Irkutsk'         => "(GMT+09:00) Irkutsk",
    'Asia/Seoul'           => "(GMT+09:00) Seoul",
    'Asia/Tokyo'           => "(GMT+09:00) Tokyo",
    'Australia/Adelaide'   => "(GMT+09:30) Adelaide",
    'Australia/Darwin'     => "(GMT+09:30) Darwin",
    'Asia/Yakutsk'         => "(GMT+10:00) Yakutsk",
    'Australia/Brisbane'   => "(GMT+10:00) Brisbane",
    'Australia/Canberra'   => "(GMT+10:00) Canberra",
    'Pacific/Guam'         => "(GMT+10:00) Guam",
    'Australia/Hobart'     => "(GMT+10:00) Hobart",
    'Australia/Melbourne'  => "(GMT+10:00) Melbourne",
    'Pacific/Port_Moresby' => "(GMT+10:00) Port Moresby",
    'Australia/Sydney'     => "(GMT+10:00) Sydney",
    'Asia/Vladivostok'     => "(GMT+11:00) Vladivostok",
    'Asia/Magadan'         => "(GMT+12:00) Magadan",
    'Pacific/Auckland'     => "(GMT+12:00) Auckland",
    'Pacific/Fiji'         => "(GMT+12:00) Fiji",
);

foreach($timezones as $xzone => $x_value) {
	echo '<option value="'.$xzone.'">'.$x_value.'</option>';
}
echo '</select>';
echo'
				</select>
                <div class="help-block with-errors"></div></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['cancel'].'</button>
                <input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-default login btn-sm" onclick="update_timezone()">
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
		$rval=my_exec("grep -a \"".$SArrVal['service']."\" /var/log/syslog | tail -n 1");
		// If no log entry found in syslogs and no error, check in log rotating file syslog.1
		if($rval['stdout']=='' && $rval['stderr']==''){
			$rval=my_exec("grep -a \"".$SArrVal['service']."\" /var/log/syslog.1 | tail -n 1");
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
                        if($rval[1]=='') {
                                $logDateLabel= $rval[2]." ".$rval[0]." ".$rval[3];
                                $logDateObj = DateTime::createFromFormat('YMdH:i:s', $logYear.$rval[0].$rval[2].$rval[3]);
                        } else {
                                $logDateLabel= $rval[1]." ".$rval[0]." ".$rval[2];
                                $logDateObj = DateTime::createFromFormat('YMdH:i:s', $logYear.$rval[0].$rval[1].$rval[2]);
                        }
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
			<form data-toggle="validator" role="form" method="post" action="#" id="form-join">
			<div class="form-group" class="control-label"><label>E-Mail Address</label> <small class="text-muted">'.$lang['pihome_backup_email_info'].'</small>
			<input class="form-control input-sm" type="text" id="backup_email" name="backup_email" value="'.settings($conn, backup_email).'" placeholder="Email Address to Receive your Backup file">
			<div class="help-block with-errors"></div>
			</div>
			</form>';
echo '     </div>
            <div class="modal-footer">
			<button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
			<a href="javascript:backup_email_update()" class="btn btn-default login btn-sm">'.$lang['save'].'</a>
			<a href="javascript:db_backup()" class="btn btn-default login btn-sm">'.$lang['backup_start'].'</a>
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
        if($row['account_enable'] == 1) {
                $content_msg="You are about to DELETE an ENABLED USER";
        } else {
                $content_msg="You are about to DELETE a CURRENTLY DISABLED USER";
        }
	echo "<div href=\"settings.php?uid=".$row['id']."\"  class=\"list-group-item\"> 
    <i class=\"ionicons ion-person blue\"></i> ".$username."
    <span class=\"pull-right text-muted small\"><em>
	<a href=\"javascript:del_user(".$row["id"].");\"><button class=\"btn btn-danger btn-xs\" data-toggle=\"confirmation\" data-title=".$lang['confirmation']." data-content=\"$content_msg\"><span class=\"glyphicon glyphicon-trash\"></span></button> </a>
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

$('[data-toggle=confirmation]').confirmation({
  rootSelector: '[data-toggle=confirmation]',
  container: 'body'
});
</script>

<script language="javascript" type="text/javascript">
function BoilerChildList(value)
{
 var valuetext = value;
 var e = document.getElementById("node_id");
 var selected_node_id = e.options[e.selectedIndex].text;
 var selected_node_id = selected_node_id.split(" - ");
 document.getElementById("selected_node_id").value = selected_node_id[1];
 document.getElementById("selected_node_type").value = selected_node_id[0];
 var gpio_pins = document.getElementById('gpio_pin_list').value

 var opt = document.getElementById("node_child_id").getElementsByTagName("option");
 for(j=opt.length-1;j>=0;j--)
 {
        document.getElementById("node_child_id").options.remove(j);
 }
 if(selected_node_id.includes("GPIO")) {
        var pins_arr = gpio_pins.split(',');
        for(j=0;j<=pins_arr.length-1;j++)
        {
                var optn = document.createElement("OPTION");
                optn.text = pins_arr[j];
                optn.value = pins_arr[j];
                document.getElementById("node_child_id").options.add(optn);
        }
 } else {
        for(j=1;j<=valuetext;j++)
        {
                var optn = document.createElement("OPTION");
                optn.text = j;
                optn.value = j;
                document.getElementById("node_child_id").options.add(optn);
        }
 }
}
function show_hide_devices()
{
 var e = document.getElementById("node_type");
 var selected_node_type = e.options[e.selectedIndex].text;
 if(selected_node_type.includes("GPIO")) {
        document.getElementById("nodes_max_child_id").style.visibility = 'hidden';;
        document.getElementById("add_devices_label").style.visibility = 'hidden';;
 } else {
        document.getElementById("nodes_max_child_id").style.visibility = 'visible';;
        document.getElementById("add_devices_label").style.visibility = 'visible';;
 }
}
function gw_location()
{
 var e = document.getElementById("gw_type");
 var selected_gw_type = e.value;
 if(selected_gw_type.includes("wifi")) {
        document.getElementById("serial_gw").style.display = 'none';
        document.getElementById("wifi_gw").style.display = 'block';
        document.getElementById("serial_port").style.display = 'none';
        document.getElementById("wifi_port").style.display = 'block';
        document.getElementById("wifi_location").value = "192.168.0.100";
        document.getElementById("wifi_port_num").value = "5003";
 } else {
        document.getElementById("wifi_gw").style.display = 'none';
        document.getElementById("serial_gw").style.display = 'block';
        document.getElementById("wifi_port").style.display = 'none';
        document.getElementById("serial_port").style.display = 'block';
        document.getElementById("serial_location").value = "/dev/ttyAMA0";
        document.getElementById("serial_port_speed").value = "115200";
 }
}
 </script>

