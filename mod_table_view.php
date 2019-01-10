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
echo "***************************************************************\n";
echo "*   Modify Table schedule_daily_time        Date 15/10/2018   *\n";
echo "*   Last Modified on 16/10/2018                               *\n";
echo "*                                      Have Fun - PiHome.eu   *\n";
echo "***************************************************************\n";
echo "\033[0m";
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - PiHome Modify Table Started \n"; 
echo "---------------------------------------------------------------------------------------- \n";

//Set php script execution time in seconds
ini_set('max_execution_time', 400); 
$date_time = date('Y-m-d H:i:s');
//Temporary File to save exiting CronJobs
$cronfile = '/tmp/crontab.txt';

//Check php version before doing anything else 
$version = explode('.', PHP_VERSION);
if ($version[0] > 7){
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - PiHome Supported on php version 5.x you are running version \033[41m".phpversion()."\033[0m \n"; 
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Please visit http://www.pihome.eu/2017/10/11/apache-php-mysql-raspberry-pi-lamp/ to install correction version. \n";
	exit();
}else {
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - php version \033[41m".phpversion()."\033[0m looks OK \n";
}

//*********************************************************
//* Modify Following variable according to your settings  *
//*********************************************************
$hostname = 'localhost';
$dbname   = 'pihome';
$dbusername = 'pihome';
$dbpassword = 'pihome2018';
$connect_error = 'Sorry We are Experiencing MySQL Database Connection Problem...';

//Test Connection to MySQL Server with Given Username & Password 
$conn = new mysqli($hostname, $dbusername, $dbpassword);
if ($conn->connect_error){
	die('Database Connecction Failed with Error: '.$conn->connect_error);
}

$db_selected = mysqli_select_db($conn, $dbname);
if (!$db_selected) {
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - MySQL DataBase \033[41m".$dbname."\033[0m Does not Exist \n"; 
	$query = "CREATE DATABASE {$dbname};";
	$result = $conn->query($query);
	if ($result) {
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - MySQL DataBase \033[41m".$dbname."\033[0m Created Successfully!!! \n"; 
	}else {
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - MySQL DataBase Error Creating Database \n"; 
		mysqli_error($conn). "\n";
	}
}else {
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - MySQL DataBase \033[41m".$dbname."\033[0m Already Exist. \n";
}
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - MySQL DataBase Creating Table View \n";

        //Add Column WeekDays as last in table - schedule_daily_time
        $query = "ALTER TABLE schedule_daily_time ADD COLUMN WeekDays SMALLINT NOT NULL DEFAULT 127;";
        $result = $conn->query($query);
        if ($result) {echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - MySQL DataBase Table \033[41m schedule_daily_time \033[0m Column Added \n"; }

	//Drop Table View If Exist
	$query = "Drop View if exists schedule_daily_time_zone_view;";
	$result = $conn->query($query);
	if ($result) {echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - MySQL DataBase Table View \033[41m schedule_daily_time_zone_view \033[0m Dropped \n"; }
	
	//Create Table View
        $query = "CREATE VIEW schedule_daily_time_zone_view AS
        select ss.id as time_id, ss.status as time_status, sstart.start, send.end, sWeekDays.WeekDays,
        sdtz.sync as tz_sync, sdtz.id as tz_id, sdtz.status as tz_status,
        sdtz.zone_id, zone.index_id, zone.name as zone_name, temperature
        from schedule_daily_time_zone sdtz
        join schedule_daily_time ss on sdtz.schedule_daily_time_id = ss.id
        join schedule_daily_time sstart on sdtz.schedule_daily_time_id = sstart.id
        join schedule_daily_time send on sdtz.schedule_daily_time_id = send.id
        join schedule_daily_time sWeekDays on sdtz.schedule_daily_time_id = sWeekDays.id
        join zone on sdtz.zone_id = zone.id
        where sdtz.`purge` = '0' order by zone.index_id;";
        $result = $conn->query($query);
        if ($result) {echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - MySQL DataBase Table View \033[41m schedule_daily_time_zone_view \033[0m Created \n"; }
 

echo "---------------------------------------------------------------------------------------- \n";
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - PiHome Install Script Ended \n"; 
echo "\033[32m****************************************************************************************\033[0m  \n";
?>
