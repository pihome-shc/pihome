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

// Time Zone Settings for PHP

//date_default_timezone_set("Europe/Dublin"); // You can set Timezone Manually and uncomment this line and comment out following line 
date_default_timezone_set(settings("timezone"));

$sysversion="0.125";

// This file is to include basic functions
function mysql_prep($value) {
	$magic_quotes_active = get_magic_quotes_gpc();
	$new_enough_php = function_exists("mysql_real_escape_string");
	//if php 4.3.0 or highre
	if($new_enough_php) {
		//undo magic quotes effect so that real escape sting can do the work	
		if($magic_quotes_active) { $value = stripslashes($value); }
		$value = mysql_real_escape_string($value);
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

function confirm_query($result_set) {
if(!$result_set) {
        die("Sorry We are experiencing connection Problem..." . mysql_error());
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
function searchArray($search, $array)
{
    foreach($array as $key => $value)
    {
        if (stristr($value, $search))
        {
            return $key;
        }
    }
    return false;
}

function get_real_ip()
{
    if (isset($_SERVER["HTTP_CLIENT_IP"])){return $_SERVER["HTTP_CLIENT_IP"];}
    elseif (isset($_SERVER["HTTP_X_FORWARDED_FOR"])){return $_SERVER["HTTP_X_FORWARDED_FOR"];}
    elseif (isset($_SERVER["HTTP_X_FORWARDED"])){return $_SERVER["HTTP_X_FORWARDED"];}
    elseif (isset($_SERVER["HTTP_FORWARDED_FOR"])){return $_SERVER["HTTP_FORWARDED_FOR"];}
    elseif (isset($_SERVER["HTTP_FORWARDED"])){return $_SERVER["HTTP_FORWARDED"];}
    else{ return $_SERVER["REMOTE_ADDR"];}
}

// Return Systems setting from settings table function
function settings($svalue){
	$rValue = "";
	$query = ("SELECT * FROM system;");
	$result = mysql_query($query);
	if ($row = mysql_fetch_array($result)){	$rValue = $row[$svalue];	}
	return $rValue;
} 

// Return MySensors Logs from gateway_log function
function gw_logs($value){
	$rValue = "";
	$query = ("SELECT * FROM gateway_logs order by id desc limit 1;");
	$result = mysql_query($query);
	if ($row = mysql_fetch_array($result)){	$rValue = $row[$value];	}
	return $rValue;
}

// Return MySensors Setting from gateway table function
function gw($value){
	$rValue = "";
	$query = ("SELECT * FROM gateway order by id asc limit 1;");
	$result = mysql_query($query);
	if ($row = mysql_fetch_array($result)){	$rValue = $row[$value];	}
	return $rValue;
}


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
	$result = shell_exec("blkid -o value -s UUID");  
	return md5($salt.md5($result));
}

?>