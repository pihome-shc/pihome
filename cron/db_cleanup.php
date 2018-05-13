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
echo "*************************************************************\n";
echo "* Database Cleanup Script Version 0.1 Build Date 13/05/2018 *\n";
echo "* Update on 10/04/218                                       *\n";
echo "*                                      Have Fun - PiHome.eu *\n";
echo "*************************************************************\n";
echo " \033[0m \n";

require_once(__DIR__.'../../st_inc/connection.php');
require_once(__DIR__.'../../st_inc/functions.php');

//Set php script execution time in seconds
ini_set('max_execution_time', 300); 
echo date('Y-m-d H:i:s'). " - Database Cleanup Script Started \n"; 
$query = "DELETE FROM messages_in WHERE datetime < DATE_SUB(curdate(), INTERVAL 3 DAY);";
$result = $conn->query($query);
if (isset($result)) {
	echo date('Y-m-d H:i:s'). " - Records Delete from Tables \n"; 
}else {
	echo date('Y-m-d H:i:s'). " - Records Delete from Tables Failed\n";
	echo mysql_error();
}
echo date('Y-m-d H:i:s'). " - Database Cleanup Script Ended \n"; 
echo "\033[32m**************************************************************\033[0m  \n";
if(isset($conn)) { $conn->close();}
?>