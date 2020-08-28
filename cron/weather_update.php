#!/usr/bin/php
<?php 
header('Content-Type: application/json');
echo "\033[36m";
echo "\n";
echo "   _____    _   _    _                             \n";
echo "  |  __ \  (_) | |  | |                            \n";
echo "  | |__) |  _  | |__| |   ___    _ __ ___     ___  \n";
echo "  |  ___/  | | |  __  |  / _ \  | |_  \_ \   / _ \ \n";
echo "  | |      | | | |  | | | (_) | | | | | | | |  __/ \n";
echo "  |_|      |_| |_|  |_|  \___/  |_| |_| |_|  \___| \n";
echo " \033[0m \n";
echo "     \033[45m S M A R T   H E A T I N G   C O N T R O L \033[0m \n";
echo "\033[31m";
echo "************************************************************\n";
echo "* Weather Update Script Version 0.11 Build Date 31/01/2018 *\n";
echo "* Update on 27/01/2020                                     *\n";
echo "*                                     Have Fun - PiHome.eu *\n";
echo "************************************************************\n";
echo " \033[0m \n";

require_once(__DIR__.'../../st_inc/connection.php');
require_once(__DIR__.'../../st_inc/functions.php'); 
$date_time = date('Y-m-d H:i:s');
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Weather Update Script Started \n"; 
//set the Unit
$c_f = settings($conn, 'c_f');
if($c_f==1 || $c_f=='1'){
    $units='units=imperial';
}else{
    $units='units=metric';
}

//query to get system table
$query = "SELECT * FROM system LIMIT 1";
$result = $conn->query($query);
$row = mysqli_fetch_array($result);
if ($row['openweather_api'] != NULL){
	$country = $row['country'];
	$city = $row['city'];
    $zip = $row['zip'];
	$appid = $row['openweather_api'];
	//Get Current Weather Data
    if($city != NULL)
        $weather_current_api = "http://api.openweathermap.org/data/2.5/weather?q=".$city.",".$country."&" . $units . "&appid=".$appid;
    else
        $weather_current_api = "http://api.openweathermap.org/data/2.5/weather?zip=".$zip.",".$country."&" . $units . "&appid=".$appid;
	$json = file_get_contents($weather_current_api);
	//Check if results are not empty 
	if(strlen($json) > 0) {
	
		file_put_contents('/var/www/weather_current.json', $json);
	
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Weather Data Downloaded \n"; 
		//add weather temperature to database
		$weather_data = json_decode($json, true);
		//weather_c is now dependent upon the units specified in the query.
		if($c_f==1 || $c_f=='1'){
			//the value returned in in Fahrenheit, but we store it in C, so convert.    
			$weather_c=round(($weather_data['main']['temp']-32)*(5/9));
		} else {
			$weather_c=round($weather_data['main']['temp']);   // -272.15);
		}
		//$c  = $weather_data['main']['temp'];
		$wind_speed    = round($weather_data['wind']['speed']);   // *1.609344, 2);
		$sunrise = $weather_data['sys']['sunrise'];
		$sunset = $weather_data['sys']['sunset'];
		$title = $weather_data['weather'][0]['main'];
		$description = $weather_data['weather'][0]['description'];
		$icon = $weather_data['weather'][0]['icon'];
		$location = $weather_data['name'];
		
		//print_r ($weather_data);
		
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Current Weather Temperature ".$weather_c."  \n"; 
		if ($weather_c != -272){
			$time=date('H:i');
			$date=date('y-m-d');
			$query = "INSERT INTO messages_in (`sync`, `purge`, `node_id`, `child_id`, `sub_type`, `payload`, `datetime`) VALUES ('0', '0', '1', '0','0', '{$weather_c}', '{$date_time}')";
			$conn->query($query);
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Database Updated \n"; 
			//update weather table
                        $query = "SELECT * FROM weather";
                        $result = $conn->query($query);
                        $wcount = $result->num_rows;
                        if ($wcount == 0) {
        			$query = "INSERT INTO weather VALUES(1, 0, '{$location}', '{$weather_c}', '{$wind_speed}', '{$title}', '{$description}', '{$sunrise}', '{$sunset}', '{$icon}', '{$date_time}');";
                        } else {
				$query = "update weather SET sync = '0', location = '{$location}', c = '{$weather_c}', wind_speed = '{$wind_speed}', title = '{$title}', description = '{$description}', sunrise = '{$sunrise}', sunset = '{$sunset}', img = '{$icon}' WHERE id = '1' LIMIT 1";
			}	
			$conn->query($query);
		}else {
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m  -Current Weather data was not downloaded \n"; 
		}
	
		//Call 5 day / 3 hour forecast data
		//$weather_fivedays_api = "http://api.openweathermap.org/data/2.5/forecast?q=".$city.",".$country."&units=metric&appid=".$appid;
		if($city != NULL)
			$weather_fivedays_api = "http://api.openweathermap.org/data/2.5/forecast?q=".$city.",".$country."&" . $units . "&lang=en&appid=".$appid;
		else
			$weather_fivedays_api = "http://api.openweathermap.org/data/2.5/forecast?zip=".$zip.",".$country."&" . $units . "&lang=en&appid=".$appid;
		$json = file_get_contents($weather_fivedays_api);
		file_put_contents('/var/www/weather_5days.json', $json);
		$weather_fivedays = json_decode($json, true);
		//print_r($weather_fivedays);
		foreach($weather_fivedays['list'] as $day => $value) {
			echo "\033[31m------------------------------------------------------\033[0m \n";
			//Date and time
			echo "\033[1;33mDate and Time: \033[0m          ".$value['dt_txt']." \n" ;	
			// min and max temperature of the day
			echo "\033[1;33mMin Temperature for day: \033[0m".$day." ".$value['main']['temp_min']."  \n" ;
			echo "\033[1;33mMax Temperature for day: \033[0m".$day." ".$value['main']['temp_max']." \n" ;
			//Weather condition and description
			echo "\033[1;33mWeather: \033[0m                ".$value['weather'][0]['main']." - " .$value['weather'][0]['description']." \n" ;
			// Cloud percentage 
			echo "\033[1;33mCloud %: \033[0m                ".$value['clouds']['all']." \n" ;
			// Wind Speed 
			echo "\033[1;33mWind Speed %: \033[0m           ".$value['wind']['speed']." \n" ;
			// Humidity level 
			echo "\033[1;33mHumidity : \033[0m              ".$value['main']['humidity']." \n" ;	
			//icon for weather 
			echo "\033[1;33mIcon : \033[0m                  ".$value['weather'][0]['icon']." \n" ;
		}
		echo " \n" ;
		//6 days weather forecast data 
		//$weather_sixdays_api = "http://api.openweathermap.org/data/2.5/forecast/daily?q=".$city.",".$country."&appid=".$appid;
		if($city != NULL)
			$weather_sixdays_api = "http://api.openweathermap.org/data/2.5/forecast?q=".$city.",".$country."&" . $units . "&cnt=7&lang=en&appid=".$appid;
		else
			$weather_sixdays_api = "http://api.openweathermap.org/data/2.5/forecast?zip=".$zip.",".$country."&" . $units . "&cnt=7&lang=en&appid=".$appid;
		$json = file_get_contents($weather_sixdays_api);
		file_put_contents('/var/www/weather_6days.json', $json);
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Weather Data Downloaded \n"; 
		$weather_data = json_decode($json, true);
		//print_r($weather_data);
		/*
		foreach($weather_data['list'] as $day => $value) {
			echo "************************************************** \n";
			// min and max temperature of the day
			echo "Min C for day " . $day . " " .$value['temp']['max']." Max C ".$value['temp']['min']." \n" ;
			//Weather condition and description
			echo "Weather: " .$value['weather'][0]['main']." - " .$value['weather'][0]['description']." \n" ;
			// Cloud percentage 
			echo "Cloud %: " .$value['clouds']." \n" ;
			// Wind Speed 
			echo "Wind Speed %: " .$value['speed']." \n" ;
			// Wind Direction 
			echo "Wind Direction %: " .$value['deg']." \n" ;
			// Humidity level 
			echo "Humidity : " .$value['humidity']." \n" ;	
			//icon for weather 
			echo "Icon : " .$value['weather'][0]['icon']." \n" ;
		}
		*/
// end if 
	} else {
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - No Weather Data Downloaded \n"; 
		echo "\033[31m------------------------------------------------------\033[0m \n";
	}
}
echo "  \n"; 
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Weather Update Script Finished \n"; 
if(isset($conn)) { $conn->close();}
?>
