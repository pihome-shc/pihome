<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once(__DIR__.'../../st_inc/connection.php');
require_once(__DIR__.'../../st_inc/functions.php');

if(isset($_GET['zonename'])) {
	$zonename = $_GET['zonename'];
	$query = "SELECT * FROM boost_view where name = '{$zonename}' LIMIT 1;";
	$results = $conn->query($query);
	$row = mysqli_fetch_assoc($results);
        $boost_status=$row['status'];
	http_response_code(200);
	echo json_encode(intval($boost_status));
} else {
	http_response_code(400);
	echo json_encode(array("message" => "Data is incomplete."));
}
?>
