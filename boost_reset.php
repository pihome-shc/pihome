<?php 
/*
   _____    _   _    _                             
  |  __ \  (_) | |  | |                            
  | |__) |  _  | |__| |   ___    _ __ ___     ___  
  |  ___/  | | |  __  |  / _ \  | |_  \_ \   / _ \ 
  | |      | | | |  | | | (_) | | | | | | | |  __/ 
  |_|      |_| |_|  |_|  \___/  |_| |_| |_|  \___| 

     S M A R T   H E A T I N G   C O N T R O L 

*************************************************************************"
* PiHome is Raspberry Pi based Central Heating Control systems. It runs *"
* from web interface and it comes with ABSOLUTELY NO WARRANTY, to the   *"
* extent permitted by applicable law. I take no responsibility for any  *"
* loss or damage to you or your property.                               *"
* DO NOT MAKE ANY CHANGES TO YOUR HEATING SYSTEM UNTILL UNLESS YOU KNOW *"
* WHAT YOU ARE DOING                                                    *"
*************************************************************************"
*/

require_once(__DIR__.'/st_inc/session.php');
require_once(__DIR__.'/st_inc/connection.php');
require_once(__DIR__.'/st_inc/functions.php');
 
$query = "SELECT * FROM boost ORDER BY id asc";
$results = $conn->query($query);
while ($row = mysqli_fetch_assoc($results)) {

	$phpdate = strtotime( $row["time"] );
	$boost_time = $phpdate + ($row["minute"] * 60);
	$now=strtotime(date('Y-m-d H:i:s'));
	if (($boost_time > $now) && ($row["active"]=='1')){$boost='1';}else {$boost='0';}
	$query = "UPDATE boost SET active = '{$boost}' WHERE id = {$row['id']} LIMIT 1";
	$conn->query($query);
}?>
<?php if(isset($conn)) { $conn->close();} ?>