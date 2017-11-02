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
?>
