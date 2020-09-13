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
echo "*   PiConnect Script Version 0.32 Build Date 21/01/2019     *\n";
echo "*   Update on 05/06/2019                                    *\n";
echo "*                                      Have Fun - PiHome.eu *\n";
echo "*************************************************************\n";
echo " \033[0m \n";

require_once(__DIR__.'../../st_inc/connection.php');
require_once(__DIR__.'../../st_inc/functions.php');

$date_time = date('Y-m-d H:i:s');
$line = "------------------------------------------------------------------\n";
//Set php script execution time in seconds
ini_set('max_execution_time', 40);

echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - PiHome PiConnect Script Started \n";

echo $line;
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Delete all Records Marked for Purge to do Keep Everyting Clean.\n";
//Delete Boost Records
$query = "DELETE FROM boost WHERE `purge`= '1' LIMIT 1;";
$conn->query($query);
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Boost Record Purged in Local Database \n";
//Delete Override records
$query = "DELETE FROM override WHERE `purge`= '1'  LIMIT 1;";
$conn->query($query);
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Override Record Purged in Local Database \n";
//Delete Daily Time records
$query = "DELETE FROM schedule_daily_time_zone WHERE `purge`= '1';";
$conn->query($query);
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Schedule Time Record Purged in Local Database \n";
//Delete Night Climat records
$query = "DELETE FROM schedule_night_climat_zone WHERE `purge`= '1';";
$conn->query($query);
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Schedule Night Cliate Zone Record Purged in Local Database \n";
//Delete All Zone Logs records
$query = "DELETE FROM zone_logs WHERE `purge`= '1';";
$conn->query($query);
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone Logs Record Purged in Local Database \n";
//Delete All Zone Sensors records
$query = "DELETE FROM zone_sensors WHERE `purge`= '1';";
$conn->query($query);
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone Sensors Record Purged in Local Database \n";
//Delete All Zone Controllers records
$query = "DELETE FROM zone_controllers WHERE `purge`= '1';";
$conn->query($query);
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone Controllers Record Purged in Local Database \n";
//Delete Zone record
$query = "DELETE FROM zone WHERE `purge`= '1' LIMIT 1;";
$conn->query($query);
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone Record Purged in Local Database \n";
//Delete Schedul daily time zone 
$query = "DELETE FROM schedule_daily_time_zone WHERE `purge`= '1';";
$conn->query($query);
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Schedul Time Zone Purged in Local Database \n";
//Delete Holidays records 
$query = "DELETE FROM holidays WHERE `purge`= '1';";
$conn->query($query);
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Holidays Records Purged in Local Database \n";
//Delete schedule dialy time 
$query = "DELETE FROM schedule_daily_time WHERE `purge`= '1';";
$conn->query($query);
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Schedul Time Purged in Local Database \n";
echo $line;

echo "\n"; 
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - PiConnect Script Ended \n"; 
echo "\033[32m******************************************************************\033[0m  \n";
if(isset($conn)) { $conn->close();}
