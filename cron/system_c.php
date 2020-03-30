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
echo "********************************************************\n";
echo "* System Temperature Version 0.4 Build Date 31/03/2018 *\n";
echo "* Update on 30/03/2020                                 *\n";
echo "*                                 Have Fun - PiHome.eu *\n";
echo "********************************************************\n";
echo " \033[0m \n";

require_once(__DIR__.'../../st_inc/connection.php');
require_once(__DIR__.'../../st_inc/functions.php');

$date_time = date('Y-m-d H:i:s');
$system_c = exec ("vcgencmd measure_temp | cut -c6,7,8,9");
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - System Temperature: ". $system_c."\n";

if ($system_c == 0) {
	//do nothing
}else {
	$query = "INSERT INTO messages_in (`sync`, `purge`, `node_id`, `child_id`, `sub_type`, `payload`, `datetime`) VALUES ('0', '0', '0', '0','0', '{$system_c}', '{$date_time}')";
	$conn->query($query);
}
if(isset($conn)) { $conn->close();} 
?>
