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
echo "****************************************************************\n";
echo "*   PiHome Migration Script Version 0.02 Build Date 31/07/2020 *\n";
echo "*   Last Modified on 31/07/2020                                *\n";
echo "*                                      Have Fun - PiHome.eu    *\n";
echo "****************************************************************\n";
echo "\033[0m";
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - PiHome Database Migration Script Started \n"; 
$line = "--------------------------------------------------------------- \n";

require_once(__DIR__.'/../st_inc/dbStruct.php');

//Set php script execution time in seconds
ini_set('max_execution_time', 400); 
$date_time = date('Y-m-d H:i:s');
echo $line;
//Check php version before doing anything else 
$version = explode('.', PHP_VERSION);
if ($version[0] > 7){
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - PiHome Supported on php version 5.x or above you are running version \033[41m".phpversion()."\033[0m \n"; 
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Please visit http://www.pihome.eu/2017/10/11/apache-php-mysql-raspberry-pi-lamp/ to install correction version. \n";
	exit();
}else {
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - php version \033[41m".phpversion()."\033[0m looks OK \n";
}

$settings = parse_ini_file(__DIR__.'/../st_inc/db_config.ini');
foreach ($settings as $key => $setting) {
    // Notice the double $$, this tells php to create a variable with the same name as key
    $$key = $setting;
}

echo "\033[32mMake Sure you have correct MySQL/MariaDB credentials as following \033[0m\n";
echo "Hostname:     ".$hostname."\n";
echo "Database:     ".$dbname."\n";
echo "User Name:    ".$dbusername."\n";
echo "Password:     ".$dbpassword."\n";

//Test Connection to MySQL Server with Given Username & Password 
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Testing Connection to MySQL/MariaDB Server. \n";
$conn = new mysqli($hostname, $dbusername, $dbpassword);
if ($conn->connect_error){
	die('Database Connecction Failed with Error: '.$conn->connect_error);
}else {
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Database Server Connection Successfull \n";
}
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Checking if Database Exits \n";
$db_selected = mysqli_select_db($conn, $dbname);
if ($db_selected) {
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Database ".$dbname." Found \n";
	// create an image of the currently installed database, without VIEWS
	mysqli_select_db($conn, $dbname) or die('Error Selecting MySQL Database: ' . mysqli_error($conn));
        //dump all mysql database and save as sql file
        echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Creating Dump File for Exiting Database. \n";
        $dumpfname = $dbname . "_" . date("Y-m-d_H-i-s").".sql";
        $command = "mysqldump --host=$hostname --user=$dbusername ";
        if ($dbpassword) {
        	$command.= "--password=". $dbpassword ." ";
	        $command.= $dbname;
                $command.= " > " . $dumpfname;
                system($command);
                // compress sql file and unlink (delete) sql file after creating zip file.
                $zipfname = $dbname . "_" . date("Y-m-d_H-i-s").".zip";
                echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Compressing Database Dump File \033[41m".$dumpfname."\033[0m \n";
                $zip = new ZipArchive();
                if($zip->open($zipfname,ZIPARCHIVE::CREATE)){
                	$zip->addFile($dumpfname,$dumpfname);
                        $zip->close();
                        unlink($dumpfname);
                        echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Compressed Database Dump File \033[41m".$zipfname."\033[0m \n";
                }

	}
	// Create zone_type table with data if it does not exist
        echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Adding zone_type Table.  \n";
	$query = "ALTER TABLE `zone` DROP FOREIGN KEY IF EXISTS `FK_zone_type`;";
	$conn->query($query);
	$query = "DROP TABLE IF EXISTS `zone_type`;";
        $conn->query($query);
	$query = "CREATE TABLE IF NOT EXISTS `zone_type` (";
	$query = $query."`id` int(11) NOT NULL AUTO_INCREMENT,";
	$query = $query."`sync` tinyint(4) NOT NULL,";
	$query = $query."`purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',";
	$query = $query."`type` char(50) COLLATE utf8_bin,";
	$query = $query."`category` int(11),";
	$query = $query."PRIMARY KEY (`id`)";
	$query = $query.") ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;";
        $conn->query($query);
	$query = "/*!40000 ALTER TABLE `zone_type` DISABLE KEYS */;";
        $conn->query($query);
	$query = "REPLACE INTO `zone_type` (`sync`,  `purge`,  `type`, `category`) VALUES (0,0,'Heating',0),(0,0,'Hot Water',0),(0,0,'Lamp',2),(0,0,'Immersion',1);";
        $conn->query($query);
	$query = "/*!40000 ALTER TABLE `zone_type` ENABLE KEYS */;";
        $conn->query($query);

	// Save the current zone data to an array
	$query = "SELECT `zone`.*, `zone_type`.`id` AS type_id, `zone_type`.`category` FROM `zone`, `zone_type` WHERE `zone_type`.`type` LIKE concat('%',`zone`.`type`)";
        $results = $conn->query($query);
	while ($row = mysqli_fetch_assoc($results)) {
		$zone_array[] = $row;
	}
	$arrayLength = count($zone_array);

	//Apply the Migration SQL file
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Modifying Zone Table.  \n";

        $query = "ALTER TABLE `zone` DROP FOREIGN KEY IF EXISTS `FK_zone_nodes`;";
        $conn->query($query);
        $query = "ALTER TABLE `zone` DROP FOREIGN KEY IF EXISTS `FK_zone_boiler`;";
        $conn->query($query);
        $query = "ALTER TABLE `zone` CHANGE COLUMN IF EXISTS `sync` `sync` tinyint(4) NOT NULL;";
        $conn->query($query);
        $query = "ALTER TABLE `zone` CHANGE COLUMN IF EXISTS `purge` `purge` tinyint(4)NOT NULL COMMENT 'Mark For Deletion';";
        $conn->query($query);
        $query = "ALTER TABLE `zone` CHANGE COLUMN IF EXISTS `status` `status` tinyint(4);";
        $conn->query($query);
        $query = "ALTER TABLE `zone` CHANGE COLUMN IF EXISTS `name` `name` char(50) COLLATE utf8_bin;";
        $conn->query($query);
        $query = "ALTER TABLE `zone` DROP COLUMN IF EXISTS `type`;";
        $conn->query($query);
        $query = "ALTER TABLE `zone` DROP COLUMN IF EXISTS `model`;";
        $conn->query($query);
        $query = "ALTER TABLE `zone` DROP COLUMN IF EXISTS `max_c`;";
        $conn->query($query);
        $query = "ALTER TABLE `zone` DROP COLUMN IF EXISTS `hysteresis_time`;";
        $conn->query($query);
        $query = "ALTER TABLE `zone` DROP COLUMN IF EXISTS `sp_deadband`;";
        $conn->query($query);
        $query = "ALTER TABLE `zone` DROP COLUMN IF EXISTS `sensor_id`;";
        $conn->query($query);
        $query = "ALTER TABLE `zone` DROP COLUMN IF EXISTS `sensor_child_id`;";
        $conn->query($query);
        $query = "ALTER TABLE `zone` DROP COLUMN IF EXISTS `boiler_id`;";
        $conn->query($query);
        $query = "ALTER TABLE `zone` DROP COLUMN IF EXISTS `gpio_pin`;";
        $conn->query($query);
        $query = "ALTER TABLE `zone` CHANGE COLUMN IF EXISTS `controler_id` `controler_id` int(11);";
        $conn->query($query);
        $query = "ALTER TABLE `zone` CHANGE COLUMN IF EXISTS `controler_child_id` `controler_child_id` int(11);";
        $conn->query($query);
        $query = "ALTER TABLE `zone` CHANGE COLUMN IF EXISTS `zone_status` `zone_state` tinyint(4);";
        $conn->query($query);
        $query = "ALTER TABLE `zone` ADD COLUMN IF NOT EXISTS `zone_state` tinyint(4) AFTER `status`;";
        $conn->query($query);
        $query = "ALTER TABLE `zone` ADD COLUMN `type_id` int(11) AFTER `name`;";
        $conn->query($query);
        $query = "ALTER TABLE `zone` ADD COLUMN IF NOT EXISTS `graph_it` tinyint(1) AFTER `type_id`;";
        $conn->query($query);
      	$query = "ALTER TABLE `zone` ADD CONSTRAINT `FK_zone_type_id` FOREIGN KEY (`type_id`) REFERENCES `zone_type` (`id`);";
        $conn->query($query);
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone Table Successfully Modified\n";
	// Update the zone table and populate the zone_sensors table
        echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Importing the data to the zone_sensors Table and Updating the zone Table.  \n";

	$query = "DROP TABLE IF EXISTS `zone_sensors`;";
        $conn->query($query);
        $query = "CREATE TABLE IF NOT EXISTS `zone_sensors` (";
        $query =  $query."`id` int(11) NOT NULL AUTO_INCREMENT,";
        $query =  $query."`sync` tinyint(4) NOT NULL,";
        $query =  $query."`purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',";
        $query =  $query."`zone_id` int(11),";
        $query =  $query."`max_c` tinyint(4),";
        $query =  $query."`hysteresis_time` tinyint(4),";
        $query =  $query."`sp_deadband` float NOT NULL,";
        $query =  $query."`sensor_id` int(11),";
        $query =  $query."`sensor_child_id` int(11),";
        $query =  $query."PRIMARY KEY (`id`),";
        $query =  $query."KEY `FK_zone_sensors_nodes` (`sensor_id`),";
        $query =  $query."KEY `FK_zone_sensors_zone` (`zone_id`),";
        $query =  $query."CONSTRAINT `FK_zone_sensors_nodes` FOREIGN KEY (`sensor_id`) REFERENCES `nodes` (`id`),";
        $query =  $query."CONSTRAINT `FK_zone_sensors_zone` FOREIGN KEY (`zone_id`) REFERENCES `zone` (`id`)";
        $query =  $query.") ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;";
        $conn->query($query);

        $row = 0;
        while ($row < $arrayLength)
        {
        	$id = $zone_array[$row]['id'];
                $type_id =$zone_array[$row]['type_id'];
                $query = "UPDATE `zone` SET `type_id` = '{$type_id}' WHERE `id` = '{$id}';";
                $result = $conn->query($query);
                if ($zone_array[$row]['category'] < 2) {
                        $max_c =$zone_array[$row]['max_c'];
                        $hysteresis_time =$zone_array[$row]['hysteresis_time'];
                        if (array_key_exists('sp_deadband',$zone_array[$row])) {
                                $sp_deadband = $zone_array[$row]['sp_deadband'];
                        } else {
                                $sp_deadband = floatval('0.5');
                        }
                        $sensor_id =$zone_array[$row]['sensor_id'];
                        $sensor_child_id =$zone_array[$row]['sensor_child_id'];
                        $query = "INSERT INTO `zone_sensors`(`sync`, `purge`, `zone_id`, `max_c`, `hysteresis_time`, `sp_deadband`, `sensor_id`, `sensor_child_id`)  VALUES ('0', '0', '{$id}','{$max_c}','{$hysteresis_time}','{$sp_deadband}','{$sensor_id}','{$sensor_child_id}');";
                        $result = $conn->query($query);
                }
            $row++;
        }

       // Fix for database ver 1.73
        $query = "ALTER TABLE `schedule_night_climate_time` ADD COLUMN IF NOT EXISTS `WeekDays` smallint(6) NOT NULL;";
        $conn->query($query);

 	//Apply the Migration Views file
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Importing Migration SQL View File to Database, This could take few minuts.  \n";
	// Name of the file
	$migratefilename = __DIR__.'/migrate_views.sql';
	// Select database
	mysqli_select_db($conn, $dbname) or die('Error Selecting MySQL Database: ' . mysqli_error($conn));
	// Temporary variable, used to store current query
	$migratetempline = '';
	// Read in entire file
	$migratelines = file($migratefilename);
	// Loop through each line
	foreach ($migratelines as $migrateline){
	// Skip it if it's a comment
		if (substr($migrateline, 0, 2) == '--' || $migrateline == '')
			continue;
			// Add this line to the current segment
			$migratetempline .= $migrateline;
			// If it has a semicolon at the end, it's the end of the query
			if (substr(trim($migrateline), -1, 1) == ';'){
				// Perform the query
				$conn->query($migratetempline) or print("MySQL Database Error with Query ".$migratetempline.":". mysqli_error($conn)."\n");
				//mysqli_query($migratetempline) or print("MySQL Database Error with Query ".$migratetempline.":". mysqli_error($conn)."\n");
				// Reset temp variable to empty
				$migratetempline = '';
			}
		}
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - DataBase Views File \033[41m".$migratefilename."\033[0m Imported Successfully \n";

        echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - DataBase Updated Successfully \n";
	}
if(isset($conn)) { $conn->close(); }
?>
