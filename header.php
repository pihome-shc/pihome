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

?>
<?php //$start_time = microtime(TRUE);

require_once(__DIR__.'/st_inc/session.php');
confirm_logged_in();
require_once(__DIR__.'/st_inc/connection.php');
require_once(__DIR__.'/st_inc/functions.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
	<meta name="HandheldFriendly" content="true" />
    <meta name="description" content="PiHome Smart Heating Control">
    <meta name="author" content="Waseem Javid">
	<link rel="shortcut icon" href="images/favicon.ico" />
	<link rel="apple-touch-icon" href="images/apple-touch-icon.png"/>
    <title><?php echo settings($conn, 'name') ;?></title>

    <!-- Bootstrap Core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
	
	<link href="css/animate.css" rel="stylesheet">

    <!-- MetisMenu CSS -->
    <link href="css/plugins/metisMenu/metisMenu.min.css" rel="stylesheet">
	    <!-- DataTables CSS -->
    <link href="css/plugins/dataTables.bootstrap.css" rel="stylesheet">
	<!-- extra line added later for responsive test -->
	<link href="css/plugins/dataTables.responsive.css" rel="stylesheet">
	<!-- animate CSS -->
	<link href="css/plugins/animate/animate.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="css/sb-admin-2.css" rel="stylesheet">

    <!--Bootstrap datepicker css -->
	<link href="css/plugins/datepicker3.css" rel="stylesheet">
	
	<!-- Custom Fonts awesome-->
    <link href="fonts/font-awesome-4.7.0/css/font-awesome.min.css" rel="stylesheet" type="text/css">
 
	<!-- Custom Fonts ionicons-->
	<link rel="stylesheet" type="text/css" media="screen" href="fonts/ionicons-2.0.1/css/ionicons.min.css">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
	
	
<script src="js/request.js"></script>	
    <script type="text/javascript">
        (function(document,navigator,standalone) {
            // prevents links from apps from oppening in mobile safari
            // this javascript must be the first script in your <head>
            if ((standalone in navigator) && navigator[standalone]) {
                var curnode, location=document.location, stop=/^(a|html)$/i;
                document.addEventListener('click', function(e) {
                    curnode=e.target;
                    while (!(stop).test(curnode.nodeName)) {
                        curnode=curnode.parentNode;
                    }
                    // Condidions to do this only on links to your own app
                    // if you want all links, use if('href' in curnode) instead.
                    if('href' in curnode && ( curnode.href.indexOf('http') || ~curnode.href.indexOf(location.host) ) ) {
                        e.preventDefault();
                        location.href = curnode.href;
                    }
                },false);
            }
        })(document,window.navigator,'standalone');
    </script>
</head>
<body>
    <div id="wrapper">
        <!-- Navigation -->
        <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
		<a class="navbar-brand" href="home.php"><img src="images/navi-logo.png" width="32"></a>
            <!-- /.navbar-header -->
            <ul class="nav navbar-top-links navbar-right">
                <li class="dropdown">
                    <a href="index.php">
                        <i class="fa fa-home fa-fw"></i>
                    </a>
                </li>
				<?php // Alert icon need some thinking: May be table with list of alerts and one cron job to check if any thing not communicating. 
				/*<li class="dropdown">
                    <a class="dropdown-toggle" href="#">
                        <i class="fa fa-exclamation-triangle fa-fw"></i>  
                    </a>
                </li>
				*/
				?>
                <li class="dropdown">
                    <a class="dropdown-toggle" href="schedule.php">
                        <i class="fa fa-clock-o fa-fw"></i>  
                    </a>
                </li>
                <li class="dropdown">
                    <a class="dropdown-toggle" href="chart.php">
                        <i class="fa fa-bar-chart fa-fw"></i>  
                    </a>
                </li>		

                <li class="dropdown">
                    <a class="dropdown-toggle" data-toggle="modal" href="#weather" data-backdrop="static" data-keyboard="false">
                        <i class="fa fa-sun-o fa-fw"></i>  
                    </a>
                </li>
                <li class="dropdown">
                    <a class="dropdown-toggle" href="settings.php">
                        <i class="fa fa-cog fa-fw"></i>  
                    </a>
                </li>			

                <li class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                        <i class="fa fa-user fa-fw"></i>  <i class="fa fa-caret-down"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-user">
                        <li><a href="selfpwd.php"><i class="fa fa-user fa-key fa-fw"></i> Change Password</a></li>
                        <li class="divider"></li>
                        <li><a href="logout.php"><i class="fa fa-sign-out fa-fw"></i> Logout</a></li>
                    </ul>
                    <!-- /.dropdown-user -->
                </li>
                <!-- /.dropdown -->
            </ul>
        </nav>
<?php 
$query="select * from weather;";
$result=$conn->query($query);
$weather = mysqli_fetch_array($result);
?>

<div class="modal fade" id="weather" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title"><i class="fa fa-sun-o fa-fw"></i> <?php echo $weather['location'] ;?> Weather</h5>
            </div>
            <div class="modal-body">
			<div class="row"> 
				<div class="col-xs-10 col-sm-10 col-md-10">
<h5><span><img border="0" src="images/<?php echo $weather['img'];?>.png" title="<?php echo $weather['title'];?> - 
<?php echo $weather['description'];?>"></span> <span><?php echo $weather['title'];?> - 
<?php echo $weather['description'];?></span></h5>
				</div>
            <div class="col-xs-7 col-sm-6 col-md-6 wdata">
                Sunrise: <?php echo date('H:i', $weather['sunrise']);?> <br>
                Sunset: <?php echo date('H:i', $weather['sunset']);?> <br>
                Wind: <?php echo $weather['wind_speed'];?> km/s
			<?php //date_sun_info( int $weather['sunrise'], float $weather['lat'] , float $weather['lon']) ;?>
			</div>     
            <div class="col-xs-5 col-sm-6 col-md-6">
                <span class="pull-right degrees"><?php echo $weather['c'] ;?>&deg;C</span>
            </div> 
        </div> 
		<br>
			<div class="row"> 
			<div class="col-lg-12">
<?php if(filesize('weather_6days.json')>0) { ?>                
			<h4 class="text-center">Six Days Weather</h4>
	<div class="list-group">
<?php
$weather_api = file_get_contents('weather_6days.json');
$weather_data = json_decode($weather_api, true);
//echo '<pre>' . print_r($weather_data, true) . '</pre>';
foreach($weather_data['list'] as $day => $value) {
echo '<a href="weather.php" class="list-group-item"><img border="0" width="28" height="28" src="images/'.$value['weather'][0]['icon'].'.png">
'.$value['weather'][0]['main']." - " .$value['weather'][0]['description'].'<span class="pull-right text-muted small"><em>'.round($value['temp']['max'],0)."&deg; - ".round($value['temp']['min'],0).'&deg;</em></span></a>';
}
?>
</div>
<?php } //end of filesize if ?>            
<a href="weather.php" button type="button" class="btn btn-default login btn-sm btn-edit">3 Hour Forcast</a>
<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
        </div></div>
</div>
        </div>
    </div>
</div>
