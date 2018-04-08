<?php 

//Error reporting on php ON
error_reporting(E_ALL);
//Error reporting on php OFF
//error_reporting(0);


//*********************************************************
//* Modify Following variable according to your settings  *
//*********************************************************
$hostname = 'localhost';
$dbname   = 'pihome';
$dbusername = 'root';
$dbpassword = 'passw0rd';
$connect_error = 'Sorry We are Experiencing MySQL Database Connection Problem...';

//Test Connection to MySQL Server with Given Username & Password 
$conn = new mysqli($hostname, $dbusername, $dbpassword, $dbname);
if ($conn->connect_error){
	die('Database Connecction Failed with Error: '.$conn->connect_error);
}



// Return Systems setting from settings table function
function settings($db, $svalue){
	$rValue = "";
	$query="SELECT * FROM system limit 1;";
	$result = $db->query($query);
	if ($row = mysqli_fetch_array($result)){$rValue = $row[$svalue];}
	return $rValue;	
}



echo settings($conn, 'name');

//print_r($conn);

?>