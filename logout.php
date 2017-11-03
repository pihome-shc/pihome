<?php
session_start();
$_SESSION = array();
if(isset($_COOKIE[session_name()])) {
	setcookie(session_name(), '', time()-42000, '/');
}
session_destroy();
header("Location: index.php?logout=1");
exit;
?>
<?php if(isset($connection)) { mysql_close($connection); } ?>