<?php 
require_once(__DIR__.'/st_inc/session.php'); 

//Set PiHome Language when user click on Lanage url
if(isset($_GET['lang'])) {
	$lang = $_GET['lang'];
	setcookie("PiHomeLanguage", $lang, time()+(3600*24*90));
	header("Location: " . $_SERVER['HTTP_REFERER']);
	exit;
} else { 
	header("Location: " . $_SERVER['HTTP_REFERER']);
	exit;
}
?>
