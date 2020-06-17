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

require_once(__DIR__.'/connection.php');

// Time Zone Settings for PHP
//date_default_timezone_set("Europe/Dublin"); // You can set Timezone Manually and uncomment this line and comment out following line 
date_default_timezone_set(settings($conn, 'timezone'));

// this function is deprecated --- prepare mysql statement 
function mysqli_prep($value) {
	$magic_quotes_active = get_magic_quotes_gpc();
	$new_enough_php = function_exists("mysqli_real_escape_string");
	//if php 4.3.0 or highre
	if($new_enough_php) {
		//undo magic quotes effect so that real escape sting can do the work	
		if($magic_quotes_active) { $value = stripslashes($value); }
		$value = mysqli_real_escape_string($value);
	} else {		//before php 4.3.0
		// if magic quotes are not on then add slahes
		if(!$magic_quotes_active) { $value = addslashes($value); }
		// if magic quotes are on then slashes already exist
	}
	return $value;	
}

function redirect_to($location = NULL) {
	if($location != NULL) {
		header("Location: {$location}");
		exit;
	}
}

function getWeather() 
  {    
        $file = "./weather_current.json";
        if(file_exists($file))
        {
            $json = file_get_contents($file);            
            $weather_data = json_decode($json, true);
            $arr['temp_kelvin']  = $weather_data['main']['temp'];
            $arr['wind_mps']     = $weather_data['wind']['speed'];
            $arr['temp_celsius'] = round($weather_data['main']['temp']-272.15); 
            $arr['wind_kms']     = round($weather_data['wind']['speed']*1.609344, 2);
            $arr['sunrise']      = $weather_data['sys']['sunrise'];
            $arr['sunset']       = $weather_data['sys']['sunset'];
            $arr['weather_code'] = $weather_data['weather'][0]['id'];
            $arr['title']        = $weather_data['weather'][0]['main'];
            $arr['description']  = $weather_data['weather'][0]['description'];
            $arr['icon']         = $weather_data['weather'][0]['icon'];
            $arr['location']     = $weather_data['name'];
            $arr['lon']          = $weather_data['coord']['lon'];
            $arr['lat']          = $weather_data['coord']['lat'];
            return $arr;
        }else{
            return 0;
        }
  }

/**
* ShowWeather
*
* Show weather at bottom of page, echos content directly.
* Weather_c is now dependent upon the units specified in the weather_update query.
*
* @param object $conn
*   Database connection
*
*/
function ShowWeather($conn)
{
    $query="select * from weather";
    $result = $conn->query($query);
    $weather = mysqli_fetch_array($result);    
    $c_f = settings($conn, 'c_f');
    
    echo 'Outside: ' .DispTemp($conn,$weather['c']). '&deg;&nbsp;';
    if($c_f==1 || $c_f=='1')
        echo 'F';
    else
        echo 'C';
    $Img='images/' . $weather['img'] . '.png';
    if(file_exists($Img))
        echo '<span><img border="0" width="24" src="' . $Img . '" title="' . $weather['title'] . ' - ' . $weather['description'] . '"></span>';
    echo '<span>' . $weather['title'] . ' - ' . $weather['description'] . '</span>';
}
  
//ref: http://stackoverflow.com/questions/14721443/php-convert-seconds-into-mmddhhmmss
// Prefix single-digit values with a zero.
function ensure2Digit($number) {
    if($number < 10) {
        $number = '0' . $number;
    }
    return $number;
}


//function to check if night climate time 
//ref: http://blog.yiannistaos.com/php-check-if-time-is-between-two-times-regardless-of-date/
function TimeIsBetweenTwoTimes($from, $till, $input) {
    $f = DateTime::createFromFormat('H:i:s', $from);
    $t = DateTime::createFromFormat('H:i:s', $till);
    $i = DateTime::createFromFormat('H:i:s', $input);
    if ($f > $t) $t->modify('+1 day');
	return ($f <= $i && $i <= $t) || ($f <= $i->modify('+1 day') && $i <= $t);
}

// Convert seconds into months, days, hours, minutes, and seconds in number formate i.e 00:01:18:11:32 
function secondsToTime($ss) {
    $s = ensure2Digit($ss%60);
    $m = ensure2Digit(floor(($ss%3600)/60));
    $h = ensure2Digit(floor(($ss%86400)/3600));
    $d = ensure2Digit(floor(($ss%2592000)/86400));
    $M = ensure2Digit(floor($ss/2592000));

    return "$M:$d:$h:$m:$s";
}

// Convert seconds into months, days, hours, minutes, and second ie. 1 days 18 hours 11 minutes 32 seconds 
function secondsToWords($seconds)
{
    $ret = "";
    /*** get the days ***/
    $days = intval(intval($seconds) / (3600*24));
    if($days> 0)
    {
        $ret .= "$days days ";
    }
    /*** get the hours ***/
    $hours = (intval($seconds) / 3600) % 24;
    if($hours > 0)
    {
        $ret .= "$hours hours ";
    }
    /*** get the minutes ***/
    $minutes = (intval($seconds) / 60) % 60;
    if($minutes > 0)
    {
        $ret .= "$minutes minutes ";
    }
    /*** get the seconds ***/
    $seconds = intval($seconds) % 60;
    if ($seconds > 0) {
        $ret .= "$seconds seconds";
    }
    return $ret;
}

//function to search inside array ref: http://forums.phpfreaks.com/topic/195499-partial-text-match-in-array/
function searchArray($search, $array) {
    foreach($array as $key => $value) {
        if (stristr($value, $search)) {
			return $key;
        }
    }
    return false;
}

// Return realy ip address of visitor 
function get_real_ip() {
    if (isset($_SERVER["HTTP_CLIENT_IP"])){return $_SERVER["HTTP_CLIENT_IP"];}
    elseif (isset($_SERVER["HTTP_X_FORWARDED_FOR"])){return $_SERVER["HTTP_X_FORWARDED_FOR"];}
    elseif (isset($_SERVER["HTTP_X_FORWARDED"])){return $_SERVER["HTTP_X_FORWARDED"];}
    elseif (isset($_SERVER["HTTP_FORWARDED_FOR"])){return $_SERVER["HTTP_FORWARDED_FOR"];}
    elseif (isset($_SERVER["HTTP_FORWARDED"])){return $_SERVER["HTTP_FORWARDED"];}
    else{ return $_SERVER["REMOTE_ADDR"];}
}

// Return Systems setting from settings table function
function settings($db, $svalue){
	$rValue = "";
	$query="SELECT * FROM system limit 1;";
	$result = $db->query($query);
	if ($row = mysqli_fetch_array($result))
    {
        if(isset($row[$svalue]))
            $rValue = $row[$svalue];        
    }
	return $rValue;	
}

// Return MySensors Logs from gateway_log function
function gw_logs($db, $value){
	$rValue = "";
	$query = ("SELECT * FROM gateway_logs order by id desc limit 1;");
	$result = $db->query($query);
	if ($row = mysqli_fetch_array($result)){	$rValue = $row[$value];	}
	return $rValue;
}

// Return MySensors Setting from gateway table function
function gw($db, $value){
	$rValue = "";
	$query = ("SELECT * FROM gateway order by id asc limit 1;");
	$result = $db->query($query);
	if ($row = mysqli_fetch_array($result)){	$rValue = $row[$value];	}
	return $rValue;
}

//get contents of and url 
function url_get_contents ($Url) {
    if (!function_exists('curl_init')){ 
        die('CURL is not installed!');
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $Url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}


//Return Unique ID for Record Purpose
function UniqueMachineID($salt) {
	//$salt = exec ("cat /proc/cpuinfo | grep Serial | cut -d ' ' -f 2");
	$uuid = exec("sudo blkid -o value -s UUID");  
	return md5($salt.md5($uuid));
}


//Get full URL ref: https://stackoverflow.com/questions/14912943/how-to-print-current-url-path
function get_current_url($strip = true) {
    static $filter, $scheme, $host;
    if (!$filter) {
        // sanitizer
        $filter = function($input) use($strip) {
            $input = trim($input);
            if ($input == '/') {
                return $input;
            }
            // add more chars if needed
            $input = str_ireplace(["\0", '%00', "\x0a", '%0a', "\x1a", '%1a'], '',
                rawurldecode($input));
            // remove markup stuff
            if ($strip) {
                $input = strip_tags($input);
            }
            // or any encoding you use instead of utf-8
            $input = htmlspecialchars($input, ENT_QUOTES, 'utf-8');

            return $input;
        };
        $host = $_SERVER['SERVER_NAME'];
        $scheme = isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : ('http'. (($_SERVER['SERVER_PORT'] == '443') ? 's' : ''));
    }
    return sprintf('%s://%s%s', $scheme, $host, $filter($_SERVER['REQUEST_URI']));
}

/**
* DispTemp
*
* Convert the temp, if necessary, to Fahrenheit. 
*   All database records are expected to be in Celsius.
*
* @param object $conn
*   Database connection
* @param int $C
*   Degrees in C
*
* @return int
*   Degrees in C or F
*/
function DispTemp($conn,$C)
{
    $c_f = settings($conn, 'c_f');
    if($c_f==1 || $c_f=='1')
    {
        return round(($C*9/5)+32,1);
    }
    return round($C,1);
}
/**
* TempToDB
*
* Convert the temp from the UI, either C or F, to Celsius for storage. 
*   All database records are expected to be in Celsius.
*
* @param object $conn
*   Database connection
* @param int $T
*   Degrees in C/F, from UI
*
* @return int
*   Degrees in C
*/
function TempToDB($conn,$T){
    $c_f = settings($conn, 'c_f');
    if($c_f==1 || $c_f=='1'){
        return round(($T-32)*5/9,1);
    }
    return round($T,1);
}




function my_exec($cmd, $input='')
{
    $proc=proc_open($cmd, array(0=>array('pipe', 'r'), 1=>array('pipe', 'w'), 2=>array('pipe', 'w')), $pipes);
    fwrite($pipes[0], $input);fclose($pipes[0]);
    $stdout=stream_get_contents($pipes[1]);fclose($pipes[1]);
    $stderr=stream_get_contents($pipes[2]);fclose($pipes[2]);
    $rtn=proc_close($proc);
    return array('stdout'=>$stdout,
                 'stderr'=>$stderr,
                 'return'=>$rtn
                );
}

function Convert_CRLF($string, $line_break=PHP_EOL)
{
    $patterns = array(  "/(\r\n|\r|\n)/" );
    $replacements = array(  $line_break );
    $string = preg_replace($patterns, $replacements, $string);
    return $string;
}

function Get_GPIO_List()
{
    $file = "/var/www/st_inc/gpio_pin_list";
    if(file_exists($file))
    {
        // Open the file
        $fp = @fopen($file, 'r');

        // Add each line to an array
        if ($fp) {
            $arr = explode("\n", fread($fp, filesize($file)));
        }
            return $arr;
        }else{
            return 0;
        }

}

function ListLanguages($lang)
{
        $dir    = '/var/www/languages/';
        $fpath = $dir.$lang.'.php';
        if (file_exists($fpath)) { $Content = file_get_contents($fpath); } else { $Content = file_get_contents($dir."en.php"); }
        preg_match_all('/(?<match>.*lang_.*)/', $Content, $Matches);
        $Data = array();
        for($j = 0; $j < count($Matches[1]); $j++){
                $Field = trim($Matches[1][$j]);
                $Data[$j][0] = substr($Field, 12, 2);
                $Data[$j][1] = substr($Field, 20, -2);
        }
return($Data);
}

function getIndicators($conn, $zone_mode, $zone_temp_target)
{
	/****************************************************** */
	//Status indicator animation
	/****************************************************** */

	$zone_mode_main=floor($zone_mode/10)*10;
        $zone_mode_sub=floor($zone_mode%10);

	//not running - temperature reached or not running in this mode
	if($zone_mode_sub == 0){
		//fault or idle
		if(($zone_mode_main == 0)||($zone_mode_main == 10)){
			$status='';
		}
		//away, holidays or hysteresis
		else if(($zone_mode_main == 40)||($zone_mode_main == 90)||($zone_mode_main == 100)){ 
			$status='blue';
		}
		//all other modes
		else{
			$status='orange';
		}
	}
	//running
	else if($zone_mode_sub == 1){
		$status='red';
	}
	//not running - deadband
	else if($zone_mode_sub == 2){
		$status='blueinfo';  
						}
	//not running - coop start waiting for boiler
	else if($zone_mode_sub == 3){
		$status='blueinfo';  
	}

	/****************************************************** */
	//Icon Animation and target temperature
	/****************************************************** */

	 //idle
	if($zone_mode_main == 0){
		$shactive='';
		$shcolor='';
		$target='';     //show no target temperature
	}
	//fault
	else if($zone_mode_main == 10){
		$shactive='ion-android-cancel';
		$shcolor='red';
		$target='';     //show no target temperature
	}
	//frost
	else if($zone_mode_main == 20){
		$shactive='ion-ios-snowy';
       		$shcolor='';
		$target=number_format(DispTemp($conn,$zone_temp_target),1) . '&deg;';
	}
	//overtemperature
	else if($zone_mode_main == 30){
		$shactive='ion-thermometer';
		$shcolor='red';
		$target=number_format(DispTemp($conn,$zone_temp_target),1) . '&deg;';   
	}
	//holiday
	else if($zone_mode_main == 40){
		$shactive='fa-paper-plane';
		$shcolor='';
		$target='';     //show no target temperature
	}
	//nightclimate
	else if($zone_mode_main == 50){
		$shactive='fa-bed';
		$shcolor='';
		$target=number_format(DispTemp($conn,$zone_temp_target),1) . '&deg;';
	}
	//boost
	else if($zone_mode_main == 60){
		$shactive='fa-rocket';
		$shcolor='';
		$target=number_format(DispTemp($conn,$zone_temp_target),1) . '&deg;';
	}
	//override
	else if($zone_mode_main == 70){
		$shactive='fa-refresh';
		$shcolor='';
		$target=number_format(DispTemp($conn,$zone_temp_target),1) . '&deg;';
	}
	//sheduled
	else if($zone_mode_main == 80){
		//if not coop start waiting for boiler
		if($zone_mode_sub <> 3){
			$shactive='ion-ios-clock-outline';
               	$shcolor='';
		}
		//if coop start waiting for boiler
		else{
			$shactive='ion-leaf';
	               	$shcolor='green';
		}
		$target=number_format(DispTemp($conn,$zone_temp_target),1) . '&deg;';
	}
	//away
	else if($zone_mode_main == 90){
		$shactive='fa-sign-out';
		$shcolor='';
		$target='';     //show no target temperature
	}
	//hysteresis
	else if($zone_mode_main == 100){
		$shactive='fa-hourglass';
		$shcolor='';
		$target='';     //show no target temperature
	}
	//shouldn't get here
	else {
		$shactive='fa-question';
		$shcolor='';
		$target='';     //show no target temperature
	}

	return array('status'=>$status,
 		'shactive'=>$shactive,
       		'shcolor'=>$shcolor,
       		'target'=>$target
       	);
}
?>
