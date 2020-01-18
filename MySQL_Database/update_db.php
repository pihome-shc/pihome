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
echo "*   PiHome Datase Script Version 0.01 Build Date 15/09/2019   *\n";
echo "*   Last Modified on 16/09/2019                               *\n";
echo "*                                      Have Fun - PiHome.eu   *\n";
echo "***************************************************************\n";
echo "\033[0m";
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - PiHome Database Update Script Started \n"; 
$line = "--------------------------------------------------------------- \n";

require_once(__DIR__.'../st_inc/dbStruct.php');
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
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Checking GITHUB for Database Update \n";
	// create an image of the currently installed database, without VIEWS
	mysqli_select_db($conn, $dbname) or die('Error Selecting MySQL Database: ' . mysqli_error($conn));
	$query = "SHOW FULL TABLES IN `$dbname` WHERE TABLE_TYPE LIKE 'VIEW';";
	$result = $conn->query($query);
	$views=array();
	while($row = $result->fetch_assoc()) {
   		$views[]="--ignore-table={$dbname}.".$row['Tables_in_'."{$dbname}"];
	}
	//dump the databse with no data or views or triggers
	exec("mysqldump -d -u root --password=\"$dbpassword\" $dbname --skip-triggers ".implode(" ",$views), $struct1);

	//create an image of the latest database from GITHUB
	$struct2 = file_get_contents('https://raw.githubusercontent.com/pihome-shc/pihome/master/MySQL_Database/pihome_mysql_database.sql');

	//create an array of SQL commands to transform the structure of the currently installed database to match the GITHUB image
	$updater = new dbStructUpdater();
	$res = $updater->getUpdates(implode("\n",$struct1), $struct2);
	//print_r($res);
	if(!empty($res)) {
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Database Update Found on GITHUB\n";
		// create sql file to update the database structure
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - The Following Updates will be Applied to the Database. \n";
        	$myfile = fopen("currentDB_dump.sql", "w") or die("Unable to open file!");
        	foreach($res as $value){
                	$templine = $value . ";\n";
                	fwrite($myfile, $templine);
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - " . $templine;
        	}
        	fclose($myfile);

		echo "\033[36m".date('Y-m-d H:i:s'). "\n\033[0mExisting Database Table Structures will be amended. \nAre you sure you want to do this?  Type 'yes' to continue? \n";
		echo $line;

		$handle = fopen ("php://stdin","r");
		$response  = fgets($handle);
		if(trim($response ) != 'yes'){
        		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - ABORTING!!! : Run script again and Type yes to continue\n";
    			exit;
		}
		fclose($handle); 

                //dump all mysql database and save as sql file
                echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Creating Dump File for Exiting Database. \n";
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

		// get the new version and build values
		$ver =substr($struct2, strpos($struct2, 'PiHome - Smart Heating Control') + 34, 4);
		$build =substr($struct2, strpos($struct2, 'PiHome - Smart Heating Control') + 42, 6);

		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Applying Updates to Database. \n";
		// Name of the file
		$filename = __DIR__.'/currentDB_dump.sql';
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
                $query = "UPDATE system SET version = '{$ver}', build = '{$build}' LIMIT 1;";
                $conn->query($query);

                echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Database Updates Applied \n";
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Update Version: ".$ver."\n";
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Update Build: ".$build."\n";
	} else {
                echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - NO Database Updates Found \n";
	}
} else {
        	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Database ".$dbname." Not Found \n";
	}
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - MySQL Update DataBase Script Ended \n"; 
echo "\033[32m****************************************************************************************\033[0m  \n";
if(isset($conn)) { $conn->close(); }
?>
