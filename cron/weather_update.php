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
echo "***************************************************************\n";
echo "*   Weather Update Script Version 0.1 Build Date 31/01/2018   *\n";
echo "*                                        Have Fun - PiHome.eu *\n";
echo "***************************************************************\n";
echo " \033[0m \n";
require_once(__DIR__.'../../st_inc/connection.php');
require_once(__DIR__.'../../st_inc/functions.php'); 

echo date('Y-m-d H:i:s'). " - Weather Update Script Started \n"; 

//query to get system table
$query = "SELECT * FROM system LIMIT 1";
$result = $conn->query($query);
$row = mysqli_fetch_array($result);
$country = $row['country'];
$city = $row['city'];
$appid = $row['openweather_api'];


//Get Current Weather Data
$weather_current_api = "http://api.openweathermap.org/data/2.5/weather?q=".$city.",".$country."&appid=".$appid;
$json = file_get_contents($weather_current_api);
file_put_contents('/var/www/weather_current.json', $json);

echo date('Y-m-d H:i:s'). " - Weather Data Downloaded \n"; 
//add weather temperature to database
$weather_data = json_decode($json, true);
$weather_c= round($weather_data['main']['temp']-272.15);
$c  = $weather_data['main']['temp'];
$wind_speed    = round($weather_data['wind']['speed']*1.609344, 2);
$sunrise = $weather_data['sys']['sunrise'];
$sunset = $weather_data['sys']['sunset'];
$title = $weather_data['weather'][0]['main'];
$description = $weather_data['weather'][0]['description'];
$icon = $weather_data['weather'][0]['icon'];
$location = $weather_data['name'];

//print_r ($weather_data);

echo date('Y-m-d H:i:s'). " - Current Weather Temperature ".$weather_c."  \n"; 
if ($weather_c != -272){
	$time=date('H:i');
	$date=date('y-m-d');
	$query = "INSERT INTO messages_in (node_id, child_id, payload) VALUES ('1', '0', '{$weather_c}')";
	$conn->query($query);
	echo date('Y-m-d H:i:s'). " - Database Updated \n"; 
	//update weather table
	$query = "update weather SET location = '{$location}', c = '{$weather_c}', wind_speed = '{$wind_speed}', title = '{$title}', description = '{$description}', sunrise = '{$sunrise}', sunset = '{$sunset}', img = '{$icon}' WHERE id = '1' LIMIT 1";
	$conn->query($query);
}else {
	echo date('Y-m-d H:i:s'). " - Current Weather data was not downloaded \n"; 
}

//Call 5 day / 3 hour forecast data
//$weather_fivedays_api = "http://api.openweathermap.org/data/2.5/forecast?q=".$city.",".$country."&units=metric&appid=".$appid;
$weather_fivedays_api = "http://api.openweathermap.org/data/2.5/forecast?q=".$city.",".$country."&units=metric&lang=en&appid=".$appid;
$json = file_get_contents($weather_fivedays_api);
file_put_contents('/var/www/weather_5days.json', $json);
$weather_fivedays = json_decode($json, true);
//print_r($weather_fivedays);
foreach($weather_fivedays['list'] as $day => $value) {
	echo "************************************************** \n";
	//Date and time
	echo "Date and Time : " .$value['dt_txt']." \n" ;	
	// min and max temperature of the day
	echo "Min C for day " . $day . " " .$value['main']['temp_min']." Max C ".$value['main']['temp_max']." \n" ;
	//Weather condition and description
	echo "Weather: " .$value['weather'][0]['main']." - " .$value['weather'][0]['description']." \n" ;
	// Cloud percentage 
	echo "Cloud %: " .$value['clouds']['all']." \n" ;
	// Wind Speed 
	echo "Wind Speed %: " .$value['wind']['speed']." \n" ;

	// Humidity level 
	echo "Humidity : " .$value['main']['humidity']." \n" ;	
	//icon for weather 
	echo "Icon : " .$value['weather'][0]['icon']." \n" ;

}
echo " \n" ;
//6 days weather forecast data 
//$weather_sixdays_api = "http://api.openweathermap.org/data/2.5/forecast/daily?q=".$city.",".$country."&appid=".$appid;
$weather_sixdays_api = "http://api.openweathermap.org/data/2.5/forecast/daily?q=".$city.",".$country."&units=metric&cnt=7&lang=en&appid=".$appid;


$json = file_get_contents($weather_sixdays_api);
file_put_contents('/var/www/weather_6days.json', $json);
echo date('Y-m-d H:i:s'). " - Weather Data Downloaded \n"; 
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

echo "  \n"; 
echo date('Y-m-d H:i:s'). " - Weather Update Script Finished \n"; 
if(isset($conn)) { $conn->close();}
?>
