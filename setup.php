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
echo "*   PiHome Install Script Version 0.3 Build Date 31/01/2018   *\n";
echo "*   Last Modified on 13/05/2018                               *\n";
echo "*                                      Have Fun - PiHome.eu   *\n";
echo "***************************************************************\n";
echo "\033[0m";
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - PiHome Install Script Started \n"; 
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
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - PHP Version \033[41m".phpversion()."\033[0m Looks OK \n";
}

$settings = parse_ini_file(__DIR__.'/db_config.ini');
foreach ($settings as $key => $setting) {
    // Notice the double $$, this tells php to create a variable with the same name as key
    $$key = $setting;
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
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - MySQL DataBase Create Dump File for Exiting Database. \n";
	//dump all mysql database and save as sql file
	$dumpfname = $dbname . "_" . date("Y-m-d_H-i-s").".sql";
	$command = "mysqldump --ignore-table=$dbname.backup --add-drop-table --host=$hostname --user=$dbusername ";
	if ($dbpassword)
        $command.= "--password=". $dbpassword ." ";
		$command.= $dbname;
		$command.= " > " . $dumpfname;
		system($command);
		// compress sql file and unlink (delete) sql file after creating zip file. 
		$zipfname = $dbname . "_" . date("Y-m-d_H-i-s").".zip";
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - MySQL DataBase Compressing Dump File \033[41m".$dumpfname."\033[0m \n";
		$zip = new ZipArchive();
		if($zip->open($zipfname,ZIPARCHIVE::CREATE)){
			$zip->addFile($dumpfname,$dumpfname);
			$zip->close();
			unlink($dumpfname);
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - MySQL DataBase Compressed Dump File \033[41m".$zipfname."\033[0m \n";
		}
}
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - MySQL DataBase Importing SQL File to Database \n";
	// Name of the file
	$filename = __DIR__.'/MySQL_Database/pihome_mysql_database.sql';
	// Select database
	mysqli_select_db($conn, $dbname) or die('Error Selecting MySQL Database: ' . mysqli_error($conn));
	// Temporary variable, used to store current query
	$templine = '';
	// Read in entire file
	$lines = file($filename);
	// Loop through each line
	foreach ($lines as $line){
	// Skip it if it's a comment
		if (substr($line, 0, 2) == '--' || $line == '')
			continue;
			// Add this line to the current segment
			$templine .= $line;
			// If it has a semicolon at the end, it's the end of the query
			if (substr(trim($line), -1, 1) == ';'){
				// Perform the query
				$conn->query($templine) or print("MySQL Database Error with Query ".$templine.":". mysqli_error($conn)."\n");
				//mysqli_query($templine) or print("MySQL Database Error with Query ".$templine.":". mysqli_error($conn)."\n");
				// Reset temp variable to empty
				$templine = '';
			}
	}
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - MySQL DataBase File \033[41m".$filename."\033[0m Imported Successfully \n";
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - MySQL DataBase Creating Table View \n";

	//Drop Table View If Exist
	$query = "Drop View if exists schedule_daily_time_zone_view;";
	$result = $conn->query($query);
	if ($result) {echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - MySQL DataBase Table View \033[41m schedule_daily_time_zone_view \033[0m Created \n"; }
	
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

	//Drop Table View If Exist
	$query = "Drop View if exists zone_view;";
	$result = $conn->query($query);
	
	//Create Table View
	$query = "CREATE VIEW zone_view AS
	select zone.status, zone.sync, zone.id, zone.index_id, zone.name, zone.type, zone.max_c, zone.max_operation_time, zone.hysteresis_time, 
	sid.node_id as sensors_id, zone.sensor_child_id, 
	cid.node_id as controler_id, zone.controler_child_id, zone.gpio_pin,
	lasts.last_seen, msv.ms_version, skv.sketch_version
	from zone
	join nodes sid on zone.sensor_id = sid.id
	join nodes cid on zone.controler_id = cid.id
	join nodes lasts on zone.sensor_id = lasts.id
	join nodes msv on zone.sensor_id = msv.id
	join nodes skv on zone.sensor_id = skv.id 
	where zone.`purge` = '0';";
	$result = $conn->query($query);
	if ($result) {echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - MySQL DataBase Table View \033[41m zone_view \033[0m Created \n"; }

	//Drop Table View If Exist
	$query = "Drop View if exists boiler_view;";
	$result = $conn->query($query);
	
	//Create Table View
	$query = "CREATE VIEW boiler_view AS
	select boiler.status, boiler.sync, boiler.`purge`, boiler.fired_status, boiler.name, nodes.node_id, boiler.node_child_id, boiler.hysteresis_time, boiler.max_operation_time, boiler.gpio_pin
	from boiler
	join nodes on boiler.node_id = nodes.id 
	where boiler.`purge` = '0';";
	$result = $conn->query($query);
	if ($result) {echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - MySQL DataBase Table View \033[41m boiler_view \033[0m Created \n"; }


	//Drop Table View If Exist
	$query = "Drop View if exists boost_view;";
	$result = $conn->query($query);
	
	//Create Table View
	$query = "CREATE VIEW boost_view AS
	select boost.`status`, boost.sync, boost.zone_id, zone_idx.index_id, zone.name, boost.temperature, boost.minute
	from boost
	join zone on boost.zone_id = zone.id
	join zone zone_idx on boost.zone_id = zone_idx.id;";
	$result = $conn->query($query);
	if ($result) {echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - MySQL DataBase Table View \033[41m boost_view \033[0m Created \n"; }
	

	//Drop Table View If Exist
	$query = "Drop View if exists override_view;";
	$result = $conn->query($query);
	
	//Create Table View
	$query = "CREATE VIEW override_view AS
	select override.`status`, override.sync, override.zone_id, zone_idx.index_id, zone.name, override.time, override.temperature
	from override
	join zone on override.zone_id = zone.id
	join zone zone_idx on override.zone_id = zone_idx.id;";
	$result = $conn->query($query);
	if ($result) {echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - MySQL DataBase Table View \033[41m override_view \033[0m Created \n"; }
	

	//Drop Table View If Exist
	$query = "Drop View if exists schedule_night_climat_zone_view;";
	$result = $conn->query($query);
	
	//Create Table View
	$query = "CREATE VIEW schedule_night_climat_zone_view AS 
	select tnct.status as t_status, ncz.status as z_status, ncz.sync, ncz.zone_id, snct.start_time, enct.end_time, ncz.min_temperature, ncz.max_temperature
	from schedule_night_climat_zone ncz
	join schedule_night_climate_time snct on ncz.schedule_night_climate_id = snct.id
	join schedule_night_climate_time enct on ncz.schedule_night_climate_id = enct.id
	join schedule_night_climate_time tnct on ncz.schedule_night_climate_id = tnct.id;";
	$result = $conn->query($query);
	if ($result) {echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - MySQL DataBase Table View \033[41m schedule_night_climat_zone_view \033[0m Created \n"; }
	

	//Drop Table View If Exist
	$query = "Drop View if exists messages_in_view_24h;";
	$result = $conn->query($query);
	
	//Create Table View
	$query = "CREATE VIEW messages_in_view_24h AS
	select node_id, child_id, datetime, payload 
	from messages_in 
	where datetime > DATE_SUB( NOW(), INTERVAL 24 HOUR);";
	$result = $conn->query($query);
	if ($result) {echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - MySQL DataBase Table View \033[41m messages_in_view_24h \033[0m Created \n"; }
	

	//Drop Table View If Exist
	$query = "Drop View if exists zone_log_view;";
	$result = $conn->query($query);
	
	//Create Table View
	$query = "CREATE VIEW zone_log_view AS
	select zone_logs.id, zone_logs.sync, zone_logs.zone_id, ztype.type, 
	zone_logs.boiler_log_id, blst.start_datetime, blet.stop_datetime, blext.expected_end_date_time, zone_logs.status
	from zone_logs
	join zone ztype on zone_logs.zone_id = ztype.id
	join boiler_logs blst on zone_logs.boiler_log_id = blst.id
	join boiler_logs blet on zone_logs.boiler_log_id = blet.id
	join boiler_logs blext on zone_logs.boiler_log_id = blext.id
	order by id asc;";
	$result = $conn->query($query);
	if ($result) {echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - MySQL DataBase Table View \033[41m zone_log_view \033[0m Created \n"; }

//Add database settings to _config.inc.php
$dbfilename = __DIR__.'/st_inc/_config.inc.php';
$dbcontents = '<?php
$hostname = \''.$hostname.'\';
$dbname   = \''.$dbname.'\';
$dbusername = \''.$dbusername.'\';
$dbpassword = \''.$dbpassword.'\';
$connect_error = \'Sorry We are Experiencing MySQL Database Connection Problem...\';
?>
';

if (file_exists($dbfilename)) {
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - MySQL DataBase Removing old Configuration file \033[41m ".$dbfilename." \033[0m \n";
	unlink($dbfilename);
}
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - MySQL DataBase Saving New Configuration file \033[41m ".$dbfilename." \033[0m \n";
file_put_contents($dbfilename, $dbcontents, FILE_APPEND);

if (file_exists($cronfile)) {
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - CronTab Removing old CronJobs File \033[41m ".$cronfile." \033[0m \n";
	unlink($cronfile);
}

//CronJobs
$output = shell_exec('crontab -l');
//Add CronJobs 
$message = '#
#   _____    _   _    _
#  |  __ \  (_) | |  | |
#  | |__) |  _  | |__| |   ___    _ __ ___     ___
#  |  ___/  | | |  __  |  / _ \  | |_  \_ \   / _ \
#  | |      | | | |  | | | (_) | | | | | | | |  __/
#  |_|      |_| |_|  |_|  \___/  |_| |_| |_|  \___|
#
#     S M A R T   H E A T I N G   C O N T R O L
#*************************************************************************
#* PiHome is Raspberry Pi based Central Heating Control systems. It runs *
#* from web interface and it comes with ABSOLUTELY NO WARRANTY, to the   *
#* extent permitted by applicable law. I take no responsibility for any  *
#* loss or damage to you or your property.                               *
#* DO NOT MAKE ANY CHANGES TO YOUR HEATING SYSTEM UNTILL UNLESS YOU KNOW *
#* WHAT YOU ARE DOING                                                    *
#*************************************************************************
#
#

# Database Cleaup: Delete Temperature records older then 3 days.
# Delete Node Battery status older then 3 months.
# Delete Gateway Logs data older then 3 days.
# if you want to keep all data comments following line.
0 2 * * * /usr/bin/php /var/www/cron/db_cleanup.php  >/dev/null 2>&1

# Get CPU temperature and update database.
*/5 * * * * /usr/bin/php /var/www/cron/system_c.php >/dev/null 2>&1

# Update Weather from OpenWeather, Make sure you signup to openweather api
# and update api key in database->system table.
*/30 * * * * /usr/bin/php /var/www/cron/weather_update.php >/dev/null 2>&1

# Ping your gateway (home router) and if can not ping reboot wifi on Raspberry pi.
# make sure you modify /var/www/cron/reboot_wifi.sh with your gateway ip
# if you want to save log resuts un-commnets following line.
# */2 * * * * sh /var/www/cron/reboot_wifi.sh >>/var/www/cron/logs/reboot_wifi.log 2>&1
*/2 * * * * sh /var/www/cron/reboot_wifi.sh >/dev/null 2>&1

# If you are using Wireless setup with Smart Home Gateway then you need following crong job
# to  check and start Smart Home Gateway python script if its not running.
*/1 * * * * php /var/www/cron/check_gw.php >/dev/null 2>&1

# If you have Temperature Sensors Wired to Raspberry pi GPIO un-comment
# following line to read temperature sensors data.
# */1 * * * * python /var/www/cron/gpio_ds18b20.py >/dev/null 2>&1


# Main engine for PiHome Smart Heating, If you want to ouput logs then comment first line and uncomment second line.
*/1 * * * * /usr/bin/php /var/www/cron/boiler.php >/dev/null 2>&1
# */1 * * * * /usr/bin/php /var/www/cron/boiler.php >>/var/www/cron/logs/boiler.log 2>&1

# If you signup for PiConnect - Simplify the Connected API Key you can save this key to PiConnect table to sync.
# your data with PiConnect this way you can mange your heating from
# http://www.pihome.eu/piconnect/ as todate this service is still under testing.
# email me at info@pihome.eu if you need help or more information about this.
*/1 * * * * /usr/bin/php /var/www/cron/piconnect.php >/dev/null 2>&1

#Following Script is deprecated
# @reboot sh /var/www/cron/gw.sh >/dev/null 2>&1

#Please add your cron jobs below this line
#-------------------------------------------------------------------------
';

//Save Existing CronJobs to File 
file_put_contents($cronfile, $output);
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - CronTab Exiting CronJobs Saved to \033[41m ".$cronfile." \033[0m File. \n";

//Append Temporary CronJobs File
file_put_contents($cronfile, $message, FILE_APPEND);

//Add CronJobs to CronTab
echo exec('crontab '. $cronfile);	
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - CronTab CronJobs Added from \033[41m ".$cronfile." \033[0m File. \n";	

if (file_exists($cronfile)) {
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - CronTab Removing CronJobs File \033[41m ".$cronfile." \033[0m \n";
	unlink($cronfile);
}

echo "---------------------------------------------------------------------------------------- \n";
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - PiHome Install Script Ended \n"; 
echo "\033[32m****************************************************************************************\033[0m  \n";
?>
