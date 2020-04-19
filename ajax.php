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

if(!isset($_GET['Ajax'])){
    //Check this once, instead of everytime. Should be more efficient.
    //if($DEBUG==true)
    //{
        var_dump($_GET);
        echo '<br />';
    //}
    echo __FILE__ . ' ' . __LINE__ . ' Error: Ajax action is not set.';
    return;
}

function GetModal_OpenWeather($conn){
	//foreach($_GET as $variable => $value) echo $variable . "&nbsp;=&nbsp;" . $value . "<br />\r\n";

    echo '<div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
            <h5 class="modal-title" id="ajaxModalLabel">OpenWeather Settings</h5>
        </div>
        <div class="modal-body" id="ajaxModalBody">
            <p class="text-muted">Refer to <a class="green" target="_blank" href="http://OpenWeatherMap.org">OpenWeatherMap.org</a> for more information.
            <p>An account (free options) must be setup in order to use OpenWeather.

            <form name="form-openweather" id="form-openweather" role="form" onSubmit="return false;" action="javascript:return false;" >
            <div class="form-group">
                <label>Country</label>&nbsp;(ISO-3166-1: Alpha-2 Codes)
                <select class="form-control" id="sel_Country" name="sel_Country" >
                    <option value="AF">Afghanistan</option>
                    <option value="AX">Åland Islands</option>
                    <option value="AL">Albania</option>
                    <option value="DZ">Algeria</option>
                    <option value="AS">American Samoa</option>
                    <option value="AD">Andorra</option>
                    <option value="AO">Angola</option>
                    <option value="AI">Anguilla</option>
                    <option value="AQ">Antarctica</option>
                    <option value="AG">Antigua and Barbuda</option>
                    <option value="AR">Argentina</option>
                    <option value="AM">Armenia</option>
                    <option value="AW">Aruba</option>
                    <option value="AU">Australia</option>
                    <option value="AT">Austria</option>
                    <option value="AZ">Azerbaijan</option>
                    <option value="BS">Bahamas</option>
                    <option value="BH">Bahrain</option>
                    <option value="BD">Bangladesh</option>
                    <option value="BB">Barbados</option>
                    <option value="BY">Belarus</option>
                    <option value="BE">Belgium</option>
                    <option value="BZ">Belize</option>
                    <option value="BJ">Benin</option>
                    <option value="BM">Bermuda</option>
                    <option value="BT">Bhutan</option>
                    <option value="BO">Bolivia, Plurinational State of</option>
                    <option value="BQ">Bonaire, Sint Eustatius and Saba</option>
                    <option value="BA">Bosnia and Herzegovina</option>
                    <option value="BW">Botswana</option>
                    <option value="BV">Bouvet Island</option>
                    <option value="BR">Brazil</option>
                    <option value="IO">British Indian Ocean Territory</option>
                    <option value="BN">Brunei Darussalam</option>
                    <option value="BG">Bulgaria</option>
                    <option value="BF">Burkina Faso</option>
                    <option value="BI">Burundi</option>
                    <option value="KH">Cambodia</option>
                    <option value="CM">Cameroon</option>
                    <option value="CA">Canada</option>
                    <option value="CV">Cape Verde</option>
                    <option value="KY">Cayman Islands</option>
                    <option value="CF">Central African Republic</option>
                    <option value="TD">Chad</option>
                    <option value="CL">Chile</option>
                    <option value="CN">China</option>
                    <option value="CX">Christmas Island</option>
                    <option value="CC">Cocos (Keeling) Islands</option>
                    <option value="CO">Colombia</option>
                    <option value="KM">Comoros</option>
                    <option value="CG">Congo</option>
                    <option value="CD">Congo, the Democratic Republic of the</option>
                    <option value="CK">Cook Islands</option>
                    <option value="CR">Costa Rica</option>
                    <option value="CI">Côte d\'Ivoire</option>
                    <option value="HR">Croatia</option>
                    <option value="CU">Cuba</option>
                    <option value="CW">Curaçao</option>
                    <option value="CY">Cyprus</option>
                    <option value="CZ">Czech Republic</option>
                    <option value="DK">Denmark</option>
                    <option value="DJ">Djibouti</option>
                    <option value="DM">Dominica</option>
                    <option value="DO">Dominican Republic</option>
                    <option value="EC">Ecuador</option>
                    <option value="EG">Egypt</option>
                    <option value="SV">El Salvador</option>
                    <option value="GQ">Equatorial Guinea</option>
                    <option value="ER">Eritrea</option>
                    <option value="EE">Estonia</option>
                    <option value="ET">Ethiopia</option>
                    <option value="FK">Falkland Islands (Malvinas)</option>
                    <option value="FO">Faroe Islands</option>
                    <option value="FJ">Fiji</option>
                    <option value="FI">Finland</option>
                    <option value="FR">France</option>
                    <option value="GF">French Guiana</option>
                    <option value="PF">French Polynesia</option>
                    <option value="TF">French Southern Territories</option>
                    <option value="GA">Gabon</option>
                    <option value="GM">Gambia</option>
                    <option value="GE">Georgia</option>
                    <option value="DE">Germany</option>
                    <option value="GH">Ghana</option>
                    <option value="GI">Gibraltar</option>
                    <option value="GR">Greece</option>
                    <option value="GL">Greenland</option>
                    <option value="GD">Grenada</option>
                    <option value="GP">Guadeloupe</option>
                    <option value="GU">Guam</option>
                    <option value="GT">Guatemala</option>
                    <option value="GG">Guernsey</option>
                    <option value="GN">Guinea</option>
                    <option value="GW">Guinea-Bissau</option>
                    <option value="GY">Guyana</option>
                    <option value="HT">Haiti</option>
                    <option value="HM">Heard Island and McDonald Islands</option>
                    <option value="VA">Holy See (Vatican City State)</option>
                    <option value="HN">Honduras</option>
                    <option value="HK">Hong Kong</option>
                    <option value="HU">Hungary</option>
                    <option value="IS">Iceland</option>
                    <option value="IN">India</option>
                    <option value="ID">Indonesia</option>
                    <option value="IR">Iran, Islamic Republic of</option>
                    <option value="IQ">Iraq</option>
                    <option value="IE">Ireland</option>
                    <option value="IM">Isle of Man</option>
                    <option value="IL">Israel</option>
                    <option value="IT">Italy</option>
                    <option value="JM">Jamaica</option>
                    <option value="JP">Japan</option>
                    <option value="JE">Jersey</option>
                    <option value="JO">Jordan</option>
                    <option value="KZ">Kazakhstan</option>
                    <option value="KE">Kenya</option>
                    <option value="KI">Kiribati</option>
                    <option value="KP">Korea, Democratic People\'s Republic of</option>
                    <option value="KR">Korea, Republic of</option>
                    <option value="KW">Kuwait</option>
                    <option value="KG">Kyrgyzstan</option>
                    <option value="LA">Lao People\'s Democratic Republic</option>
                    <option value="LV">Latvia</option>
                    <option value="LB">Lebanon</option>
                    <option value="LS">Lesotho</option>
                    <option value="LR">Liberia</option>
                    <option value="LY">Libya</option>
                    <option value="LI">Liechtenstein</option>
                    <option value="LT">Lithuania</option>
                    <option value="LU">Luxembourg</option>
                    <option value="MO">Macao</option>
                    <option value="MK">Macedonia, the former Yugoslav Republic of</option>
                    <option value="MG">Madagascar</option>
                    <option value="MW">Malawi</option>
                    <option value="MY">Malaysia</option>
                    <option value="MV">Maldives</option>
                    <option value="ML">Mali</option>
                    <option value="MT">Malta</option>
                    <option value="MH">Marshall Islands</option>
                    <option value="MQ">Martinique</option>
                    <option value="MR">Mauritania</option>
                    <option value="MU">Mauritius</option>
                    <option value="YT">Mayotte</option>
                    <option value="MX">Mexico</option>
                    <option value="FM">Micronesia, Federated States of</option>
                    <option value="MD">Moldova, Republic of</option>
                    <option value="MC">Monaco</option>
                    <option value="MN">Mongolia</option>
                    <option value="ME">Montenegro</option>
                    <option value="MS">Montserrat</option>
                    <option value="MA">Morocco</option>
                    <option value="MZ">Mozambique</option>
                    <option value="MM">Myanmar</option>
                    <option value="NA">Namibia</option>
                    <option value="NR">Nauru</option>
                    <option value="NP">Nepal</option>
                    <option value="NL">Netherlands</option>
                    <option value="NC">New Caledonia</option>
                    <option value="NZ">New Zealand</option>
                    <option value="NI">Nicaragua</option>
                    <option value="NE">Niger</option>
                    <option value="NG">Nigeria</option>
                    <option value="NU">Niue</option>
                    <option value="NF">Norfolk Island</option>
                    <option value="MP">Northern Mariana Islands</option>
                    <option value="NO">Norway</option>
                    <option value="OM">Oman</option>
                    <option value="PK">Pakistan</option>
                    <option value="PW">Palau</option>
                    <option value="PS">Palestinian Territory, Occupied</option>
                    <option value="PA">Panama</option>
                    <option value="PG">Papua New Guinea</option>
                    <option value="PY">Paraguay</option>
                    <option value="PE">Peru</option>
                    <option value="PH">Philippines</option>
                    <option value="PN">Pitcairn</option>
                    <option value="PL">Poland</option>
                    <option value="PT">Portugal</option>
                    <option value="PR">Puerto Rico</option>
                    <option value="QA">Qatar</option>
                    <option value="RE">Réunion</option>
                    <option value="RO">Romania</option>
                    <option value="RU">Russian Federation</option>
                    <option value="RW">Rwanda</option>
                    <option value="BL">Saint Barthélemy</option>
                    <option value="SH">Saint Helena, Ascension and Tristan da Cunha</option>
                    <option value="KN">Saint Kitts and Nevis</option>
                    <option value="LC">Saint Lucia</option>
                    <option value="MF">Saint Martin (French part)</option>
                    <option value="PM">Saint Pierre and Miquelon</option>
                    <option value="VC">Saint Vincent and the Grenadines</option>
                    <option value="WS">Samoa</option>
                    <option value="SM">San Marino</option>
                    <option value="ST">Sao Tome and Principe</option>
                    <option value="SA">Saudi Arabia</option>
                    <option value="SN">Senegal</option>
                    <option value="RS">Serbia</option>
                    <option value="SC">Seychelles</option>
                    <option value="SL">Sierra Leone</option>
                    <option value="SG">Singapore</option>
                    <option value="SX">Sint Maarten (Dutch part)</option>
                    <option value="SK">Slovakia</option>
                    <option value="SI">Slovenia</option>
                    <option value="SB">Solomon Islands</option>
                    <option value="SO">Somalia</option>
                    <option value="ZA">South Africa</option>
                    <option value="GS">South Georgia and the South Sandwich Islands</option>
                    <option value="SS">South Sudan</option>
                    <option value="ES">Spain</option>
                    <option value="LK">Sri Lanka</option>
                    <option value="SD">Sudan</option>
                    <option value="SR">Suriname</option>
                    <option value="SJ">Svalbard and Jan Mayen</option>
                    <option value="SZ">Swaziland</option>
                    <option value="SE">Sweden</option>
                    <option value="CH">Switzerland</option>
                    <option value="SY">Syrian Arab Republic</option>
                    <option value="TW">Taiwan, Province of China</option>
                    <option value="TJ">Tajikistan</option>
                    <option value="TZ">Tanzania, United Republic of</option>
                    <option value="TH">Thailand</option>
                    <option value="TL">Timor-Leste</option>
                    <option value="TG">Togo</option>
                    <option value="TK">Tokelau</option>
                    <option value="TO">Tonga</option>
                    <option value="TT">Trinidad and Tobago</option>
                    <option value="TN">Tunisia</option>
                    <option value="TR">Turkey</option>
                    <option value="TM">Turkmenistan</option>
                    <option value="TC">Turks and Caicos Islands</option>
                    <option value="TV">Tuvalu</option>
                    <option value="UG">Uganda</option>
                    <option value="UA">Ukraine</option>
                    <option value="AE">United Arab Emirates</option>
                    <option value="GB">United Kingdom</option>
                    <option value="US">United States</option>
                    <option value="UM">United States Minor Outlying Islands</option>
                    <option value="UY">Uruguay</option>
                    <option value="UZ">Uzbekistan</option>
                    <option value="VU">Vanuatu</option>
                    <option value="VE">Venezuela, Bolivarian Republic of</option>
                    <option value="VN">Viet Nam</option>
                    <option value="VG">Virgin Islands, British</option>
                    <option value="VI">Virgin Islands, U.S.</option>
                    <option value="WF">Wallis and Futuna</option>
                    <option value="EH">Western Sahara</option>
                    <option value="YE">Yemen</option>
                    <option value="ZM">Zambia</option>
                    <option value="ZW">Zimbabwe</option>
                </select>
            </div>
            <div class="form-group">
                <label>City or Zip</label>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="rad_CityZip" id="rad_CityZip_City" value="City" onchange="rad_CityZip_Changed();">
                  City
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="rad_CityZip" id="rad_CityZip_Zip" value="Zip" onchange="rad_CityZip_Changed();">
                  Zip
                </div>
            </div>
            <div class="form-group CityZip City">
                <label>City:</label>
                <input type="text" class="form-control" name="inp_City" id="inp_City">
            </div>               
            <div class="form-group CityZip Zip">
                <label>Zip:</label>
                <input type="text" class="form-control" name="inp_Zip" id="inp_Zip">
            </div>
            <div class="form-group">
                <label>API Key:</label>
                <input type="text" class="form-control" name="inp_APIKEY" id="inp_APIKEY">
            </div>               
            </form>';
    echo '</div>';      //close class="modal-body">
    echo '<div class="modal-footer" id="ajaxModalFooter">
            <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Cancel</button>
            <input type="button" name="submit" value="Save" class="btn btn-default login btn-sm" onclick="update_openweather()">
        </div>';      //close class="modal-footer">

    echo '<script language="javascript" type="text/javascript">
        rad_CityZip_Changed=function() {
            if($(\'#form-openweather [name="rad_CityZip"]:checked\').val()=="City") {
                $(".CityZip").hide();
                $(".CityZip.City").show();
            }
            else {
                $(".CityZip").hide();
                $(".CityZip.Zip").show();
            }
        };
        $("#sel_Country").val("' . settings($conn,'country') . '");
        $("#inp_APIKEY").val("' . settings($conn,'openweather_api') . '");';
    $City=settings($conn,'city');
    if($City!=NULL) {
        echo '$(\'#form-openweather [name="rad_CityZip"]\').val(["City"]);';
        echo '$("#inp_City").val("' . $City . '");';
    }else {
        echo '$(\'#form-openweather [name="rad_CityZip"]\').val(["Zip"]);';
        echo '$("#inp_Zip").val("' . settings($conn,'zip') . '");';
    }
    echo 'rad_CityZip_Changed();
        update_openweather=function(){
            var idata="w=openweather&o=update";
            idata+="&"+$("#form-openweather").serialize();
            $.get("db.php",idata)
            .done(function(odata){
                if(odata.Success)
                    $("#ajaxModal").modal("hide");
                else
                    alert(odata.Message);
            })
            .fail(function( jqXHR, textStatus, errorThrown ){
                if(jqXHR==401 || jqXHR==403) return;
                alert("update_openweather: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
            })
            .always(function() {
            });
        }
    </script>';

    return;
}
if($_GET['Ajax']=='GetModal_OpenWeather')
{
    GetModal_OpenWeather($conn);
    return;
}


function GetModal_System($conn)
{
	//foreach($_GET as $variable => $value) echo $variable . "&nbsp;=&nbsp;" . $value . "<br />\r\n";
    //System temperature
    echo '<div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
            <h5 class="modal-title" id="ajaxModalLabel">CPU Temperature</h5>
        </div>
        <div class="modal-body" id="ajaxModalBody">
    <p class="text-muted"> Last 5 CPU in-built temperature sensor reading. </p>';
    $query = "select * from messages_in where node_id = 0 order by datetime desc limit 5";
    $results = $conn->query($query);
    echo '<div class="list-group">';
    while ($row = mysqli_fetch_assoc($results)) {
        echo '<span class="list-group-item">
        <i class="fa fa-server fa-1x green"></i> '.$row['datetime'].' 
        <span class="pull-right text-muted small"><em>'.number_format(DispTemp($conn,$row['payload']),1).'&deg;</em></span>
        </span>'; 
    }
    echo '</div>';      //close class="list-group">';
    echo '</div>';      //close class="modal-body">
    echo '<div class="modal-footer" id="ajaxModalFooter">
            <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Close</button>            
        </div>';      //close class="modal-footer">
    return;
}
if($_GET['Ajax']=='GetModal_System')
{
    GetModal_System($conn);
    return;
}



function GetModal_MQTT($conn)
{
	//foreach($_GET as $variable => $value) echo $variable . "&nbsp;=&nbsp;" . $value . "<br />\r\n";

    echo '<div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
            <h5 class="modal-title" id="ajaxModalLabel">MQTT Connections</h5>
        </div>
        <div class="modal-body" id="ajaxModalBody">';
    $query = "SELECT * FROM `mqtt` ORDER BY `name`;";
    $results = $conn->query($query);
    echo '<div class="list-group">';
    echo '<span class="list-group-item" style="height:40px;">&nbsp;';
    echo '<span class="pull-right text-muted small"><button type="button" class="btn btn-primary btn-sm" 
             data-remote="false" data-target="#ajaxModal" data-ajax="ajax.php?Ajax=GetModal_MQTTAdd" onclick="mqtt_AddEdit(this);">Add</button></span>';
    echo '</span>';
    while ($row = mysqli_fetch_assoc($results)) {
        echo '<span class="list-group-item">';
        echo $row['name'] . ($row['enabled'] ? '' : ' (Disabled)');
        echo '<span class="pull-right text-muted small" style="width:200px;text-align:right;">Username:&nbsp;' . $row['username'] . '</span>';
        echo '<br/><span class="text-muted small">Type:&nbsp;';
        if($row['type']==0) echo 'Default, monitor.';
        else if($row['type']==1) echo 'Sonoff Tasmota.';
        else echo 'Unknown.';
        echo '</span>';
        echo '<span class="pull-right text-muted small" style="width:200px;text-align:right;">Password:&nbsp;' . $row['password'] . '</span>';
        echo '<br/><span class="text-muted small">' . $row['ip'] . '&nbsp;:&nbsp;' . $row['port'] . '</span>';

        echo '<span class="pull-right text-muted small" style="width:200px;text-align:right;">';
        echo '<button class="btn btn-default btn-xs" data-remote="false" data-target="#ajaxModal" data-ajax="ajax.php?Ajax=GetModal_MQTTEdit&id=' . $row['id'] . '" onclick="mqtt_AddEdit(this);">
            <span class="ionicons ion-edit"></span></button>&nbsp;&nbsp;
		<button class="btn btn-danger btn-xs" onclick="mqtt_delete(' . $row['id'] . ');"><span class="glyphicon glyphicon-trash"></span></button>';
        echo '</span>';
        echo '</span>';
    }
    echo '</div>';      //close class="list-group">';
    echo '</div>';      //close class="modal-body">
    echo '<div class="modal-footer" id="ajaxModalFooter">
            <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Close</button>            
        </div>';      //close class="modal-footer">
    echo '<script language="javascript" type="text/javascript">
        mqtt_AddEdit=function(ithis){ $("#ajaxModal").one("hidden.bs.modal", function() { $("#ajaxModal").modal("show",$(ithis)); }).modal("hide");};
    </script>';
    return;
}
if($_GET['Ajax']=='GetModal_MQTT')
{
    GetModal_MQTT($conn);
    return;
}
function GetModal_MQTTAddEdit($conn)
{
	//foreach($_GET as $variable => $value) echo $variable . "&nbsp;=&nbsp;" . $value . "<br />\r\n";

    $IsAdd=true;
    if(isset($_GET['id'])) {
        $query = "SELECT * FROM `mqtt` WHERE `id`=" . $_GET['id'] . ";";
        $results = $conn->query($query);
        $row = mysqli_fetch_assoc($results);
        $IsAdd=false;
    }

    echo '<div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
            <h5 class="modal-title" id="ajaxModalLabel">' . ($IsAdd ? 'Add MQTT Connection' : 'Edit MQTT Connection') . '</h5>
        </div>
        <div class="modal-body" id="ajaxModalBody">';
    
    
    echo '<form name="form-mqtt" id="form-mqtt" role="form" onSubmit="return false;" action="javascript:return false;" >
            ' . ($IsAdd ? '' : '<input type="hidden" name="inp_id" id="inp_id" value="' . $row['id'] . '">') . '
            <div class="form-group">
                <label>Name</label>
                <input type="text" class="form-control" name="inp_Name" id="inp_Name" value="' . ($IsAdd ? '' : $row['name']) . '">
            </div>               
            <div class="form-group">
                <label>IP</label>
                <input type="text" class="form-control" name="inp_IP" id="inp_IP" value="' . ($IsAdd ? '' : $row['ip']) . '">
            </div>               
            <div class="form-group">
                <label>Port</label>
                <input type="text" class="form-control" name="inp_Port" id="inp_Port" value="' . ($IsAdd ? '' : $row['port']) . '">
            </div>               
            <div class="form-group">
                <label>Username</label>
                <input type="text" class="form-control" name="inp_Username" id="inp_Username" value="' . ($IsAdd ? '' : $row['username']) . '">
            </div>               
            <div class="form-group">
                <label>Password</label>
                <input type="text" class="form-control" name="inp_Password" id="inp_Password" value="' . ($IsAdd ? '' : $row['password']) . '">
            </div>               
            <div class="form-group">
                <label>Enabled</label>
                <select class="form-control" id="sel_Enabled" name="sel_Enabled" >
                    <option value="0" ' . ($IsAdd ? '' : ($row['enabled'] ? 'selected' : '')) . '>Disabled</option>
                    <option value="1" ' . ($IsAdd ? '' : ($row['enabled'] ? 'selected' : '')) . '>Enabled</option>
                </select>
            </div>
            <div class="form-group">
                <label>Type</label>
                <select class="form-control" id="sel_Type" name="sel_Type" >
                    <option value="0" ' . ($IsAdd ? '' : ($row['type'] ? 'selected' : '')) . '>Default - view all</option>
                    <option value="1" ' . ($IsAdd ? '' : ($row['type'] ? 'selected' : '')) . '>Sonoff - Tasmota</option>
                </select>
            </div>
            </form>';
    echo '</div>';      //close class="modal-body">
    echo '<div class="modal-footer" id="ajaxModalFooter">' . ($IsAdd ?
            '<button type="button" class="btn btn-warning btn-sm" data-dismiss="modal" onclick="mqtt_add()">Add Conn</button>'
            : '<button type="button" class="btn btn-warning btn-sm" data-dismiss="modal" onclick="mqtt_edit()">Edit Conn</button>') . '
            <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Close</button>            
        </div>';      //close class="modal-footer">
    echo '<script language="javascript" type="text/javascript">
        mqtt_add=function(){
            var idata="w=mqtt&o=add";
            idata+="&"+$("#form-mqtt").serialize();
            $.get("db.php",idata)
            .done(function(odata){
                if(odata.Success)
                    $("#ajaxModal").modal("hide");
                else
                    alert(odata.Message);
            })
            .fail(function( jqXHR, textStatus, errorThrown ){
                if(jqXHR==401 || jqXHR==403) return;
                alert("mqtt_add: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
            })
            .always(function() {
            });
        }
        mqtt_edit=function(){
            var idata="w=mqtt&o=edit";
            idata+="&"+$("#form-mqtt").serialize();
            $.get("db.php",idata)
            .done(function(odata){
                if(odata.Success)
                    $("#ajaxModal").modal("hide");
                else
                    alert(odata.Message);
            })
            .fail(function( jqXHR, textStatus, errorThrown ){
                if(jqXHR==401 || jqXHR==403) return;
                alert("mqtt_edit: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
            })
            .always(function() {
            });
        }
    </script>';
    return;
}
if($_GET['Ajax']=='GetModal_MQTTEdit' || $_GET['Ajax']=='GetModal_MQTTAdd')
{
    GetModal_MQTTAddEdit($conn);
    return;
}



function GetModal_Services($conn)
{
	//foreach($_GET as $variable => $value) echo $variable . "&nbsp;=&nbsp;" . $value . "<br />\r\n";

    echo '<div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
            <h5 class="modal-title" id="ajaxModalLabel">Services</h5>
        </div>
        <div class="modal-body" id="ajaxModalBody">';
    $SArr=[['name'=>'Apache','service'=>'apache2.service'],
           ['name'=>'MySQL','service'=>'mysql.service'],
           ['name'=>'MariaDB','service'=>'mariadb.service'],
           ['name'=>'PiHome MQTT','service'=>'pihome.mqtt.service'],
	   ['name'=>'Amazon Echo','service'=>'pihome_amazon_echo.service'],
           ['name'=>'Homebridge','service'=>'homebridge.service']];	   
    echo '<div class="list-group">';
    foreach($SArr as $SArrKey=>$SArrVal) {
        echo '<span class="list-group-item">';
        echo $SArrVal['name'];
        $rval=my_exec("/bin/systemctl status " . $SArrVal['service']);
        echo '<span class="pull-right text-muted small">';
        if($rval['stdout']=='') {
            echo 'Error: ' . $rval['stderr'];
        } else {
            $stat='Status: Unknown';
            $rval['stdout']=explode(PHP_EOL,$rval['stdout']);
            foreach($rval['stdout'] as $line) {
                if(strstr($line,'Loaded:')) {
                    if(strstr($line,'disabled;')) {
                        $stat='Status: Disabled';
                        break;
                    }
                }
                if(strstr($line,'Active:')) {
                    if(strstr($line,'active (running)')) {
                        $stat=trim($line);
                        break;                        
                    } else if(strstr($line,'(dead)')) {
                        $stat='Status: Dead';
                        break;
                    }
                }
            }
            echo $stat;
        }
        echo '</span>';
        echo '<br/>&nbsp;<span class="pull-right text-muted small" style="width:200px;text-align:right;">';
        echo '<button class="btn btn-default btn-xs" data-remote="false" data-target="#ajaxModal" data-ajax="ajax.php?Ajax=GetModal_ServicesInfo&id=' . $SArrVal['service'] . '" onclick="services_Info(this);">
            <span class="ionicons ion-ios-information-outline"></span></button>';
        echo '</span>';
        echo '</span>';
    }
    echo '</div>';      //close class="list-group">';
    echo '</div>';      //close class="modal-body">
    echo '<div class="modal-footer" id="ajaxModalFooter">
            <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Close</button>            
        </div>';      //close class="modal-footer">
    echo '<script language="javascript" type="text/javascript">
        services_Info=function(ithis){ $("#ajaxModal").one("hidden.bs.modal", function() { $("#ajaxModal").modal("show",$(ithis)); }).modal("hide");};
    </script>';
    return;
}
if($_GET['Ajax']=='GetModal_Services')
{
    GetModal_Services($conn);
    return;
}
function GetModal_ServicesInfo($conn)
{
	//foreach($_GET as $variable => $value) echo $variable . "&nbsp;=&nbsp;" . $value . "<br />\r\n";

    echo '<div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
            <h5 class="modal-title" id="ajaxModalLabel">Services Info</h5>
        </div>
        <div class="modal-body" id="ajaxModalBody">';
    echo '<div class="list-group">';
    if(isset($_GET['Action'])) {
        if($_GET['Action']=='start' || $_GET['Action']=='stop' || $_GET['Action']=='enable' || $_GET['Action']=='disable') {
            if(substr($_GET['id'],0,10)=='homebridge') {
                if($_GET['Action']=='start' || $_GET['Action']=='stop') {
                        $rval=my_exec("sudo hb-service " . $_GET['Action']);
                } elseif ($_GET['Action']=='enable') {
                        $rval=my_exec("sudo hb-service install --user homebridge");
                } else {
                        $rval=my_exec("sudo hb-service uninstall");
                }
            } else {
                $rval=my_exec("/usr/bin/sudo /bin/systemctl " . $_GET['Action'] . " " . $_GET['id']);
            }
            $per='';
            similar_text($rval['stderr'],'We trust you have received the usual lecture from the local System Administrator. It usually boils down to these three things: #1) Respect the privacy of others. #2) Think before you type. #3) With great power comes great responsibility. sudo: no tty present and no askpass program specified',$per);
            if($per>80) {
		if(substr($_GET['id'],0,10)=='homebridge') {
                	$rval['stdout']='www-data cannot issue  hb-service commands.<br/><br/>If you would like it to be able to, add<br/><code>www-data ALL=/usr/bin/hb-service<br/>www-data ALL=NOPASSWD: /usr/bin/hb-service</code><br/>to /etc/sudoers.d/010_pi-nopasswd.';
		} else {
			$rval['stdout']='www-data cannot issue systemctl commands.<br/><br/>If you would like it to be able to, add<br/><code>www-data ALL=/bin/systemctl<br/>www-data ALL=NOPASSWD: /bin/systemctl</code><br/>to /etc/sudoers.d/010_pi-nopasswd.';
		}
                $rval['stderr']='';
            }
            echo '<p class="text-muted">systemctl ' . $_GET['Action'] . ' ' . $_GET['id'] . '<br/>stdout: ' . $rval['stdout'] . '<br/>stderr: ' . $rval['stderr'] . '</p>';
        }
    }
    
    $rval=my_exec("/bin/systemctl status " . $_GET['id']);
    echo '<span class="list-group-item">' . $_GET['id'] . '<br/>';
    echo '<span class="text-muted small">';
    if($rval['stdout']=='') {
        echo 'Error: ' . $rval['stderr'];
    } else {
        $stat='Status: Unknown';
        $rval['stdout']=explode(PHP_EOL,$rval['stdout']);
        foreach($rval['stdout'] as $line) {
            if(strstr($line,'Loaded:')) {
                if(strstr($line,'disabled;')) {
                    $stat='Status: Disabled';
                    break;
                }
            }
            if(strstr($line,'Active:')) {
                if(strstr($line,'active (running)')) {
                    $stat=trim($line);
                    break;                        
                } else if(strstr($line,'(dead)')) {
                    $stat='Status: Dead';
                    break;
                }
            }
        }
        echo $stat . '<br/>';
    }    
    echo '</span>';
    echo '</span>';
    
    if(substr($_GET['id'],0,7)=='pihome.' or substr($_GET['id'],0,7)=='pihome_' or substr($_GET['id'],0,10)=='homebridge') {
        echo '<span class="list-group-item" style="height:40px;">&nbsp;';
        echo '<span class="pull-right text-muted small">
              <button class="btn btn-warning btn-xs" data-remote="false" data-target="#ajaxModal" data-ajax="ajax.php?Ajax=GetModal_ServicesInfo&id=' . $_GET['id'] . '&Action=start" onclick="services_Info(this);">
                Start</button>
              <button class="btn btn-warning btn-xs" data-remote="false" data-target="#ajaxModal" data-ajax="ajax.php?Ajax=GetModal_ServicesInfo&id=' . $_GET['id'] . '&Action=stop" onclick="services_Info(this);">
                Stop</button>
              <button class="btn btn-warning btn-xs" data-remote="false" data-target="#ajaxModal" data-ajax="ajax.php?Ajax=GetModal_ServicesInfo&id=' . $_GET['id'] . '&Action=enable" onclick="services_Info(this);">
                Enable</button>
              <button class="btn btn-warning btn-xs" data-remote="false" data-target="#ajaxModal" data-ajax="ajax.php?Ajax=GetModal_ServicesInfo&id=' . $_GET['id'] . '&Action=disable" onclick="services_Info(this);">
                Disable</button>
              </span>';
        echo '</span>';
    }

    $rval=my_exec("/bin/journalctl -u " . $_GET['id'] . " -n 10 --no-pager");
    $per='';
    similar_text($rval['stderr'],'Hint: You are currently not seeing messages from other users and the system. Users in the \'systemd-journal\' group can see all messages. Pass -q to turn off this notice. No journal files were opened due to insufficient permissions.',$per);
    if($per>80)
    {
        $rval['stdout']='www-data cannot access journalctl.<br/><br/>If you would like it to be able to, run<br/><code>sudo usermod -a -G systemd-journal www-data</code><br/>and then reboot the RPi.';
    }
    echo '<span class="list-group-item" style="overflow:hidden;">&nbsp;';
    echo 'Status: <i class="ion-ios-refresh-outline" data-remote="false" data-target="#ajaxModal" data-ajax="ajax.php?Ajax=GetModal_ServicesInfo&id=' . $_GET['id'] . '" onclick="services_Info(this);"></i><br/>';
    echo '<span class="text-muted small">';
    echo Convert_CRLF($rval['stdout'],'<br/>');
    echo '</span></span>';
    
    if($_GET['id']=='pihome.mqtt.service' or $_GET['id']=='pihome_amazon_echo.service') {
        echo '<span class="list-group-item" style="overflow:hidden;">Install Service:';
        echo '<span class="pull-right text-muted small">Edit /lib/systemd/system/' . $_GET['id'] . '<br/>
<code>sudo nano /lib/systemd/system/' . $_GET['id'] . '</code><br/>
Put the following contents in the file:<br/>
(make sure the -u is supplied to python<br/>
to ensure the output is not buffered and delayed)<br/>
<code>[Unit]<br/>';
if($_GET['id']=='pihome.mqtt.service') {
        echo 'Description=PiHome MQTT Service<br/>';
} elseif($_GET['id']=='pihome_amazon_echo.service') {
        echo 'Description=Amazon Echo Service<br/>';
}
echo 'After=multi-user.target<br/>
<br/>
[Service]<br/>
Type=simple<br/>';
if($_GET['id']=='pihome.mqtt.service') {
        echo 'ExecStart=/usr/bin/python -u /var/www/cron/mqtt.py<br/>';
} elseif($_GET['id']=='pihome_amazon_echo.service') {
        echo 'ExecStart=/usr/bin/python -u /var/www/add_on/amazon_echo/echo_pihome.py<br/>';
}
echo 'Restart=on-abort<br/>
<br/>
[Install]<br/>
WantedBy=multi-user.target</code><br/>
Update the file permissions:<br/>
<code>sudo chmod 644 /lib/systemd/system/' . $_GET['id'] . '</code><br/>
Update systemd:<br/>
<code>sudo systemctl daemon-reload</code><br/>
<br/>
For improved performance, lower SD card writes:<br/>
Edit /etc/systemd/journald.conf<br/>
<code>sudo nano /etc/systemd/journald.conf</code><br/>
Edit/Add the following:<br/>
<code>Storage=volatile<br/>
RuntimeMaxUse=50M</code><br/>
Then restart journald:<br/>
<code>sudo systemctl restart systemd-journald</code><br/>
Refer to: <a href="www.freedesktop.org/software/systemd/man/journald.conf.html">www.freedesktop.org/software/systemd/man/journald.conf.html</a><br/>
              </span>';
        echo '</span>';
    }

    echo '</div>';      //close class="list-group">';
    echo '</div>';      //close class="modal-body">
    echo '<div class="modal-footer" id="ajaxModalFooter">
            <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Close</button>            
        </div>';      //close class="modal-footer">
    echo '<script language="javascript" type="text/javascript">
        services_Info=function(ithis){ $("#ajaxModal").one("hidden.bs.modal", function() { $("#ajaxModal").modal("show",$(ithis)); }).modal("hide");};
    </script>';
    return;
}
if($_GET['Ajax']=='GetModal_ServicesInfo')
{
    GetModal_ServicesInfo($conn);
    return;
}



function GetModal_Uptime($conn)
{
	//foreach($_GET as $variable => $value) echo $variable . "&nbsp;=&nbsp;" . $value . "<br />\r\n";

    echo '<div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
            <h5 class="modal-title" id="ajaxModalLabel">System Uptime</h5>
        </div>
        <div class="modal-body" id="ajaxModalBody">
			<p class="text-muted"> Raspberry PI up time since last reboot. </p>
			<i class="fa fa-clock-o fa-1x red"></i>';
    $uptime = (exec ("cat /proc/uptime"));
    $uptime=substr($uptime, 0, strrpos($uptime, ' '));
    echo secondsToWords($uptime) . '<br/><br/>';

    echo '<div class="list-group">';
    echo '<span class="list-group-item" style="overflow:hidden;"><pre>';
    $rval=my_exec("df -h");
    echo $rval['stdout'];
    echo '</pre></span>';

    echo '<span class="list-group-item" style="overflow:hidden;"><pre>';
    $rval=my_exec("free -h");
    echo $rval['stdout'];
    echo '</pre></span>';
    

/*    while ($row = mysqli_fetch_assoc($results)) {
        echo '<span class="list-group-item">';
        echo $row['name'] . ($row['enabled'] ? '' : ' (Disabled)');
        echo '<span class="pull-right text-muted small" style="width:200px;text-align:right;">Username:&nbsp;' . $row['username'] . '</span>';
        echo '<br/><span class="text-muted small">Type:&nbsp;';
        if($row['type']==0) echo 'Default, monitor.';
        else if($row['type']==1) echo 'Sonoff Tasmota.';
        else echo 'Unknown.';
        echo '</span>';
        echo '<span class="pull-right text-muted small" style="width:200px;text-align:right;">Password:&nbsp;' . $row['password'] . '</span>';
        echo '<br/><span class="text-muted small">' . $row['ip'] . '&nbsp;:&nbsp;' . $row['port'] . '</span>';

        echo '<span class="pull-right text-muted small" style="width:200px;text-align:right;">';
        echo '<button class="btn btn-default btn-xs" data-remote="false" data-target="#ajaxModal" data-ajax="ajax.php?Ajax=GetModal_MQTTEdit&id=' . $row['id'] . '" onclick="mqtt_AddEdit(this);">
            <span class="ionicons ion-edit"></span></button>&nbsp;&nbsp;
		<button class="btn btn-danger btn-xs" onclick="mqtt_delete(' . $row['id'] . ');"><span class="glyphicon glyphicon-trash"></span></button>';
        echo '</span>';
        echo '</span>';
    }*/
    echo '</div>';      //close class="list-group">';
    echo '</div>';      //close class="modal-body">
    echo '<div class="modal-footer" id="ajaxModalFooter">
            <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Close</button>            
        </div>';      //close class="modal-footer">
    return;
}
if($_GET['Ajax']=='GetModal_Uptime')
{
    GetModal_Uptime($conn);
    return;
}
