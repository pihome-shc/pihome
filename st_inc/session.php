<?php
session_start();
error_reporting(0); // stop/disable php error reporting 
function logged_in() {
	return isset($_SESSION['username']);
}

function confirm_logged_in() {
	if (!logged_in()) {
		header("Location: index.php");
		exit;
	}
}
//Set Cookies for PiHome Lanauge 
if(isset($_COOKIE['PiHomeLanguage'])){
	require_once "languages/".$_COOKIE['PiHomeLanguage'].".php";
} else {
	require_once('languages/en.php');
}
?>
