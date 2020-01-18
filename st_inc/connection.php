<?php 
$settings = parse_ini_file(__DIR__.'/db_config.ini');
foreach ($settings as $key => $setting) {
    // Notice the double $$, this tells php to create a variable with the same name as key
    $$key = $setting;
}
$conn = new mysqli($hostname, $dbusername, $dbpassword, $dbname);
if ($conn->connect_error){
	die('Database Connecction Failed with Error: '.$conn->connect_error);
} ?>
