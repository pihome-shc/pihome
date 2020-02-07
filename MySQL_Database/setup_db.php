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
echo "*   PiHome Datase Script Version 0.41 Build Date 31/01/2018   *\n";
echo "*   Last Modified on 27/01/2020                               *\n";
echo "*                                      Have Fun - PiHome.eu   *\n";
echo "***************************************************************\n";
echo "\033[0m";
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - PiHome Database Install Script Started \n"; 
$line = "--------------------------------------------------------------- \n";

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
echo "User Name:    ".$dbname."\n";
echo "Password:     ".$dbpassword."\n";
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Are you sure you want to do this?  Type 'yes' to continue? \n Exiting Database will be Deleted. \n";
echo $line;
/*
$handle = fopen ("php://stdin","r");
$response  = fgets($handle);
if(trim($response ) != 'yes'){
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - ABORTING!!! : Run script again and Type yes to continue\n";
    exit;
}
fclose($handle);

*/

//Test Connection to MySQL Server with Given Username & Password 
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Testing Connection to MySQL/MariaDB Server. \n";
$conn = new mysqli($hostname, $dbusername, $dbpassword);
if ($conn->connect_error){
	die('Database Connecction Failed with Error: '.$conn->connect_error);
}else {
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Database Server Connection Successfull \n";
}

echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Checking if Database Already Exits \n";
$db_selected = mysqli_select_db($conn, $dbname);
if (!$db_selected) {
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - DataBase \033[41m".$dbname."\033[0m Does not Exist \n"; 
	$query = "CREATE DATABASE {$dbname};";
	$result = $conn->query($query);
	if ($result) {
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - DataBase \033[41m".$dbname."\033[0m Created Successfully!!! \n"; 
	}else {
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - DataBase Error Creating Database \n"; 
		mysqli_error($conn). "\n";
	}
}else {
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - DataBase \033[41m".$dbname."\033[0m Already Exist. \n";
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Creating Dump File for Exiting Database. \n";
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
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Compressing Database Dump File \033[41m".$dumpfname."\033[0m \n";
		$zip = new ZipArchive();
		if($zip->open($zipfname,ZIPARCHIVE::CREATE)){
			$zip->addFile($dumpfname,$dumpfname);
			$zip->close();
			unlink($dumpfname);
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Compressed Database Dump File \033[41m".$zipfname."\033[0m \n";
		}
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Deleting Exiting Database \n"; 
		$query = "DROP DATABASE IF EXISTS {$dbname};";
		$conn->query($query);
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Creating Database \n"; 
		$query = "CREATE DATABASE {$dbname};";
		$result = $conn->query($query);
		if ($result) {
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - DataBase \033[41m".$dbname."\033[0m Created Successfully!!! \n"; 
		}else {
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - DataBase Error Creating Database \n"; 
			mysqli_error($conn). "\n";
		}
}
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Importing SQL File to Database, This could take few minuts.  \n";
	// Name of the file
	$filename = __DIR__.'/pihome_mysql_database.sql';
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
	
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - DataBase File \033[41m".$filename."\033[0m Imported Successfully \n";
	//Table View 
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Creating Table View \n";
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Importing SQL Table View File to Database.  \n";
	// Name of the file
	$tableviewfilename = __DIR__.'/MySQL_View.sql';
	// Select database
	mysqli_select_db($conn, $dbname) or die('Error Selecting MySQL Database: ' . mysqli_error($conn));
	// Temporary variable, used to store current query
	$viewtempline = '';
	// Read in entire file
	$viewlines = file($tableviewfilename);
	// Loop through each line
	foreach ($viewlines as $viewline){
	// Skip it if it's a comment
		if (substr($viewline, 0, 2) == '--' || $viewline == '')
			continue;
			// Add this line to the current segment
			$viewtempline .= $viewline;
			// If it has a semicolon at the end, it's the end of the query
			if (substr(trim($viewline), -1, 1) == ';'){
				// Perform the query
				$conn->query($viewtempline) or print("MySQL Database Error with Query ".$viewtempline.":". mysqli_error($conn)."\n");
				//mysqli_query($viewtempline) or print("MySQL Database Error with Query ".$viewtempline.":". mysqli_error($conn)."\n");
				// Reset temp variable to empty
				$viewtempline = '';
			}
	}
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - DataBase File \033[41m".$tableviewfilename."\033[0m Imported Successfully \n";

echo "---------------------------------------------------------------------------------------- \n";
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - MySQL DataBase Table View Script Ended \n"; 
echo "\033[32m****************************************************************************************\033[0m  \n";
?>
