<?php 
require("_config.inc.php");
$conn = new mysqli($hostname, $dbusername, $dbpassword, $dbname);
if ($conn->connect_error){
	die('Database Connecction Failed with Error: '.$conn->connect_error);
} ?>