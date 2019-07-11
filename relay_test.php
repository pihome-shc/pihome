<?php 
#!/usr/bin/php
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
echo "***********************************************************\n";
echo "* Zone/Boiler Relay Testing Script for PiHome Version 0.1 *\n";
echo "* Build Date 27/02/2018 -  Update on 05/08/218            *\n";
echo "*                                    Have Fun - PiHome.eu *\n";
echo "***********************************************************\n";
echo " \033[0m \n";

require_once(__DIR__.'/st_inc/connection.php');
require_once(__DIR__.'/st_inc/functions.php');

//Set php script execution time in seconds
ini_set('max_execution_time', 40); 

//GPIO Value for SainSmart Relay Board to turn on  or off 
$relay_on = '0'; //GPIO value to write to turn on attached relay
$relay_off = '1'; // GPIO value to write to turn off attached relay

$query = "SELECT * FROM zone_view where status = 1 order by index_id asc;";
$results = $conn->query($query);
while ($row = mysqli_fetch_assoc($results)) {
	$zone_id=$row['id'];
	$zone_name=$row['name'];
	$zone_gpio_pin=$row['gpio_pin'];

	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m -  GPIO \033[41m".$zone_gpio_pin."\033[0m set to \033[41mOn Status\033[0m for Zone: \033[32m".$zone_name."\033[0m\n";
	exec("/usr/local/bin/gpio write ".$zone_gpio_pin." ".$relay_on);
	exec("/usr/local/bin/gpio mode ".$zone_gpio_pin." out");
	sleep(5);
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m -  GPIO \033[41m".$zone_gpio_pin."\033[0m set to \033[44mOff Status\033[0m for Zone: \033[32m".$zone_name."\033[0m\n";
	exec("/usr/local/bin/gpio write ".$zone_gpio_pin." ".$relay_off);
	exec("/usr/local/bin/gpio mode ".$zone_gpio_pin." out");
	echo "----------------------------------------------------------------------- \n";
}

$query = "SELECT * FROM boiler_view;";
$results = $conn->query($query);
while ($row = mysqli_fetch_assoc($results)) {
	$boiler_status=$row['status'];
	$boiler_name=$row['name'];
	$boiler_gpio_pin=$row['gpio_pin'];
	
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m -  GPIO \033[41m".$boiler_gpio_pin."\033[0m set to \033[41mOn Status\033[0m for Boiler: \033[32m".$boiler_name."\033[0m\n";
	exec("/usr/local/bin/gpio write ".$boiler_gpio_pin." ".$relay_on);
	exec("/usr/local/bin/gpio mode ".$boiler_gpio_pin." out");
	sleep(5);
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m -  GPIO \033[41m".$boiler_gpio_pin."\033[0m set to \033[44mOff Status\033[0m for Boiler: \033[32m".$boiler_name."\033[0m\n";
	exec("/usr/local/bin/gpio write ".$boiler_gpio_pin." ".$relay_off);
	exec("/usr/local/bin/gpio mode ".$boiler_gpio_pin." out");
	echo "----------------------------------------------------------------------- \n";
}
?>