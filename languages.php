<?php 
require_once(__DIR__.'/st_inc/session.php'); 
require_once(__DIR__.'/st_inc/connection.php');

//Set PiHome Language when user click on Lanage url
if(isset($_GET['lang'])) {
	$lang = $_GET['lang'];
	$query = "UPDATE `system` SET `language`='" . $_GET['lang'] . "';";
        $conn->query($query);
	setcookie("PiHomeLanguage", $lang, time()+(3600*24*90));
	header("Location: " . $_SERVER['HTTP_REFERER']);
	exit;
} else { 
	header("Location: " . $_SERVER['HTTP_REFERER']);
	exit;
}
?>
