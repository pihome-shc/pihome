<?php
$hostname = 'mycryptodb.db.5979598.be9.hostedresource.net'
;$dbname   = 'mycryptodb';
$dbusername = 'mycryptodb';
$dbpassword = 'zvbCBAxnPW3853v8#xfpE#';
?>
<?php 
 

$conn = new mysqli($hostname, $dbusername, $dbpassword, $dbname);
if ($conn->connect_error){
	die('Database Connecction Failed with Error: '.$conn->connect_error);
}

//ini_set('max_execution_time', 240);


echo "\033[36m";
echo " \n";
echo "   _____    _   _    _   _                  \n";
echo "  |  __ \  (_) | |  | | (_)                 \n";
echo "  | |__) |  _  | |__| |  _  __   __   ___   \n";
echo "  |  ___/  | | |  __  | | | \ \ / /  / _ \  \n";
echo "  | |      | | | |  | | | |  \ V /  |  __/  \n";
echo "  |_|      |_| |_|  |_| |_|   \_/    \___|  \n";
echo " \033[0m \n";
echo "     \033[45m C R Y P T O   D A S H B O A R D  \033[0m \n";
echo "\033[31m";
echo " ***********************************************\n";
echo " *  Crypto Rates Update Build Date 19/01/2018  *\n";
echo " *                       Have Fun - PiHive.net *\n";
echo " ***********************************************\n";
echo " \033[0m \n";
$debug = '1';

	$g_json_string = file_get_contents("https://api.coinmarketcap.com/v1/global/?convert=EUR");
	$g_parsed_json = json_decode($g_json_string, true);
	
	$total_market_cap_usd = $g_parsed_json['total_market_cap_usd'];
	$total_24h_volume_usd = $g_parsed_json['total_24h_volume_usd'];
	$bitcoin_percentage_of_market_cap = $g_parsed_json['bitcoin_percentage_of_market_cap'];
	$active_currencies = $g_parsed_json['active_currencies'];
	$active_assets = $g_parsed_json['active_assets'];
	$active_markets = $g_parsed_json['active_markets'];
	$last_updated = $g_parsed_json['last_updated'];
	$total_market_cap_eur = $g_parsed_json['total_market_cap_eur'];
	$total_24h_volume_eur = $g_parsed_json['total_24h_volume_eur'];


	$query = "INSERT INTO global_data (total_market_cap_usd, total_24h_volume_usd, bitcoin_percentage_of_market_cap, active_currencies, active_assets, active_markets, last_updated, total_market_cap_eur, total_24h_volume_eur
	) VALUES ('{$total_market_cap_usd}', '{$total_24h_volume_usd}', '{$bitcoin_percentage_of_market_cap}', '{$active_currencies}', '{$active_assets}', '{$active_markets}', '{$last_updated}', '{$total_market_cap_eur}', '{$total_24h_volume_eur}')"; 
	$result = $conn->query($query);
		
		if ($debug == '1'){ // simply change debug value to 1 for this output 
			if ($result) {
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Crypto Global Rates Update Successfully \n";
			} else {
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Crypto Global Rates Update Failed ".mysqli_error()."\n";
			}
		}
/*
if ($debug == '1'){
	echo '<pre>';
	print_r($g_parsed_json);
	echo '<pre>';
}
echo " <br> \n";
*/
	
$query = "SELECT * FROM products where status = '1';";
$results = $conn->query($query);
while ($row = mysqli_fetch_assoc($results)) {
	$product_id = $row['id'];
	$row['name_id'];
	$row['symbol'];
	sleep(3); 
	$json_string = file_get_contents("https://api.coinmarketcap.com/v1/ticker/".$row['name_id']."/?convert=EUR");
	$parsed_json = json_decode($json_string, true);
	
	$date_time = date('Y-m-d H:i:s');
	$rank = $parsed_json[0]['rank'];
	$price_usd= $parsed_json[0]['price_usd'];
	$price_btc= $parsed_json[0]['price_btc'];
	$h24_volume_usd = $parsed_json[0]['24h_volume_usd'];
	$market_cap_usd = $parsed_json[0]['market_cap_usd'];
	$available_supply = $parsed_json[0]['available_supply'];
	$total_supply = $parsed_json[0]['total_supply'];
	$max_supply = $parsed_json[0]['max_supply'];
	$percent_change_1h = $parsed_json[0]['percent_change_1h'];
	$percent_change_24h = $parsed_json[0]['percent_change_24h'];
	$percent_change_7d = $parsed_json[0]['percent_change_7d'];
	$last_updated = $parsed_json[0]['last_updated'];
	$price_eur = $parsed_json[0]['price_eur'];
	
	if ($rank != '0' ){
	
		$query = "INSERT INTO rates (date_time, product_id, rank, price_usd, price_btc, 24h_volume_usd, market_cap_usd, available_supply, total_supply, max_supply, percent_change_1h, percent_change_24h, percent_change_7d, last_updated, price_eur 
		) VALUES ('{$date_time}', '{$product_id}', '{$rank}', '{$price_usd}', '{$price_btc}', '{$h24_volume_usd}', '{$market_cap_usd}', '{$available_supply}', '{$total_supply}', '{$max_supply}', '{$percent_change_1h}', '{$percent_change_24h}', '{$percent_change_7d}', '{$last_updated}', '{$price_eur}')"; 
		$result = $conn->query($query);
		
		if ($debug == '1'){ // simply change debug value to 1 for this output 
			if ($result) {
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Crypto BTC Price \033[41m".number_format($price_btc,8)."\033[0m Updated For: \033[32m".$row['name_id']."\033[0m \n";
			} else {
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Crypto Update Failed For: ".$row['name_id']."  ".mysqli_error()." \n";
			}
		}
	}
}

//delete all garbage records from database
$query = "DELETE FROM rates WHERE rank ='0';";
$conn->query($query);


//delete all garbage records from database
$query = "DELETE FROM global_data WHERE total_market_cap_usd ='0';";
$conn->query($query);



//ref: 
//https://stackoverflow.com/questions/535020/tracking-the-script-execution-time-in-php
// Script end
function rutime($ru, $rus, $index) {
    return ($ru["ru_$index.tv_sec"]*1000 + intval($ru["ru_$index.tv_usec"]/1000))
     -  ($rus["ru_$index.tv_sec"]*1000 + intval($rus["ru_$index.tv_usec"]/1000));
}
echo "\033[31m-------------------------------------------------------------------- \033[0m \n";
$ru = getrusage();
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Crypto Update Script Ended \n"; 
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Crypto Update Process used: \033[41m".rutime($ru, $rustart, "utime")."\033[0m ms for its computations. \n";
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Crypto Update Process spent \033[41m".rutime($ru, $rustart, "stime")."\033[0m ms in system calls. \n";

?>