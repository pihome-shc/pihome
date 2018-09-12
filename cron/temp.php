<?php 
#!/usr/bin/php
//http://www.pihome.eu/piconnect/mypihome.php?api=bc8c390f7f2d10cad2a8fbfb4bd66fd6&ip=93.107.150.39&data=new&table=schedule_daily_time&id=0
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
echo "*************************************************************\n";
echo "*   PiConnect Script Version 0.1 Build Date 16/04/2018      *\n";
echo "*   Update on 16/07/218                                     *\n";
echo "*                                      Have Fun - PiHome.eu *\n";
echo "*************************************************************\n";
echo " \033[0m \n";

require_once(__DIR__.'../../st_inc/connection.php');
require_once(__DIR__.'../../st_inc/functions.php');
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - PiHome PiConnect Script Started \n";

//Get PiConnect API from Database. 
$query = "SELECT * FROM piconnect;";
$result = $conn->query($query);
$picrow = mysqli_fetch_array($result);
$protocol = $picrow['protocol'];
$url = $picrow['url'];
$script = $picrow['script'];
$pihome_api = $picrow['api_key'];
$api_url=$protocol."://".$url.$script;
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - PiConnect API URL: \033[1;31m".$api_url."\033[0m \n";

//$api_url = "http://www.pihome.eu/piconnect/";
$my_ip = file_get_contents('http://www.pihome.eu/piconnect/myip.php');

//Set php script execution time in seconds
ini_set('max_execution_time', 40); 
$date_time = date('Y-m-d H:i:s');
$line = "------------------------------------------------------------------\n";

//get api key from database 
//$pihome_api = settings($conn, 'pihome_api');

$url=$api_url."?check_api=${pihome_api}&ip=${my_ip}";
//echo $url."\n";
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - PiHome API key: \033[1;33m".$pihome_api."\033[0m \n";
$api_result = url_get_contents($url);

//check if API is valid then execute code to sync data from local tables 
if ($api_result == "OK"){
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - You have valid API for PiHome \n";
	
	

	
	
	
/*****************************************************************************************************************************************************/	

	


	
/*****************************************************************************************************************************************************/		



//api key validation else option
}else{
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Your API Key isnt valid please contact PiHome at http://www.pihome.eu \n";
}

echo "\n"; 
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - PiConnect Script Ended \n"; 
echo "\033[32m**************************************************************\033[0m  \n";

if(isset($conn)) { $conn->close();}


?>