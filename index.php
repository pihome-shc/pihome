<?php require_once("st_inc/session.php");
	if (logged_in()) {
		header("Location: home.php");
		exit;
	}
require_once("st_inc/connection.php"); 
require_once("st_inc/functions.php"); 
//$ip = getenv("REMOTE_ADDR");

    // start process if data is passed from url  http://192.168.99.9/index.php?user=username&pass=password
    if(isset($_GET['user']) && isset($_GET['pass'])) {
        $username = $_GET['user'];
        $password = $_GET['pass'];
    // perform validations on the form data
    if( (((!isset($_GET['user'])) || (empty($_GET['user']))) && (((!isset($_GET['pass'])) || (empty($_GET['pass'])))) )){
        $error_message = "Username and Password is empty.";
    } elseif ((!isset($_GET['user'])) || (empty($_GET['user']))) {
        $error_message = "Username is empty.";
    } elseif((!isset($_GET['pass'])) || (empty($_GET['pass']))) {
        $error_message = "Password is empty.";
    }

    $username = mysql_real_escape_string($_GET['user']);
    $password = mysql_real_escape_string(md5($_GET['pass']));
    if ( !isset($error_message) ) {
        // Check database to see if username and the hashed password exist there.
        $query = "SELECT id, username FROM user WHERE username = '{$username}' AND password = '{$password}' ";
        $result_set = mysql_query($query);
        confirm_query($result_set);
            if (mysql_num_rows($result_set) == 1) {
                // username/password authenticated
                $found_user = mysql_fetch_array($result_set);
                // Set username session variable
                $_SESSION['user_id'] = $found_user['id'];
                $_SESSION['username'] = $found_user['username'];
				//$_SESSION['url'] = $_SERVER['REQUEST_URI']; // i.e. "about.php"
                $lastlogin= date("Y-m-d H:i:s");
                $query = "UPDATE user SET lastlogin = '{$lastlogin}' WHERE username = '{$username}' LIMIT 1";
                $result = mysql_query($query, $connection);
                // redirect to home page after successfull login
                //redirect_to('home.php');
				if(isset($_SESSION['url'])) {
					$url = $_SESSION['url']; // holds url for last page visited.
				}else {
					$url = "index.php"; // default page for 
				}
				redirect_to($url);
            }
        }
    } 

	if (isset($_POST['submit'])) { 
		if( (((!isset($_POST['username'])) || (empty($_POST['username']))) && (((!isset($_POST['password'])) || (empty($_POST['password'])))) )){
			$error_message = "Username and Password is empty.";
		} elseif ((!isset($_POST['username'])) || (empty($_POST['username']))) {
			$error_message = "Username is empty.";
		} elseif((!isset($_POST['password'])) || (empty($_POST['password']))) {
			$error_message = "Password is empty.";
		} 

		$username = mysql_real_escape_string($_POST['username']);
		$password = mysql_real_escape_string(md5($_POST['password']));

		//get client ip address 
		if (!empty($_SERVER["HTTP_CLIENT_IP"]))
		{
			//check for ip from share internet
			$ip = $_SERVER["HTTP_CLIENT_IP"];
		}
		elseif (!empty($_SERVER["HTTP_X_FORWARDED_FOR"]))
		{
			// Check for the Proxy User
			$ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
		}
		else
		{
			$ip = $_SERVER["REMOTE_ADDR"]; 
		}
		//set date and time 
		$lastlogin= date("Y-m-d H:i:s");		

		if ( !isset($error_message) ) {
			// Check database to see if username and the hashed password exist there.
			$query = "SELECT id, username FROM user WHERE username = '{$username}' AND password = '{$password}' ";
			$result_set = mysql_query($query);
			confirm_query($result_set);
			
			if (mysql_num_rows($result_set) == 1) {
				// username/password authenticated
				$found_user = mysql_fetch_array($result_set);
				// Set username session variable
				$_SESSION['user_id'] = $found_user['id'];
        		$_SESSION['username'] = $found_user['username'];
				// add entry to database if login is success
				$query = "INSERT INTO userhistory(username, password, date, audit, ipaddress) VALUES ('{$username}', ' ', '{$lastlogin}', 'Success', '{$ip}')";
				$result = mysql_query($query, $connection);
        		// Jump to secured page
        		//redirect_to('home.php?uid='.$_SESSION['user_id']);
				if(isset($_SESSION['url'])) {
					$url = $_SESSION['url']; // holds url for last page visited.
				}else {
					$url = "index.php"; // default page for 
				}
				redirect_to($url);
				
				
			} else {
				// add entry to database if login is success
				$query = "INSERT INTO userhistory(username, password, date, audit, ipaddress) VALUES ('{$username}', ' ', '{$lastlogin}', 'Failed', '{$ip}')";
				$result = mysql_query($query, $connection);
				// username/password was not found in the database
				$message = "Username/Password combination incorrect. Please make sure your caps lock key is off and try again.";
			}
		} 
	} else { // Form has not been submitted.
		if (isset($_GET['logout']) && $_GET['logout'] == 1) {
			$message = "You are now logged out.";
		} 
		
	}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <meta name="description" content="">
    <meta name="author" content="">
  <title><?php  echo settings('name') ;?></title>
  <meta name="apple-mobile-web-app-capable" content="yes" />
  <link rel="shortcut icon" href="images/favicon.ico">
  <link rel="apple-touch-icon" href="images/apple-touch-icon.png"/>
  <META HTTP-EQUIV="CACHE-CONTROL" CONTENT="NO-CACHE">
  <META HTTP-EQUIV="PRAGMA" CONTENT="NO-CACHE">
  
    <!-- Bootstrap Core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- MetisMenu CSS -->
    <link href="css/plugins/metisMenu/metisMenu.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="css/sb-admin-2.css" rel="stylesheet">

    <!-- Morris Charts CSS -->
    <link href="css/plugins/morris.css" rel="stylesheet">

	<!-- Custom Fonts awesome-->
    <link href="fonts/font-awesome-4.7.0/css/font-awesome.min.css" rel="stylesheet" type="text/css">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
<script type='text/javascript' src='http://code.jquery.com/jquery-1.7.2.min.js'></script>
<script>
    if(("standalone" in window.navigator) && window.navigator.standalone){

	// If you want to prevent remote links in standalone web apps opening Mobile Safari, change 'remotes' to true
	var noddy, remotes = true;

	document.addEventListener('click', function(event) {

		noddy = event.target;

		// Bubble up until we hit link or top HTML element. Warning: BODY element is not compulsory so better to stop on HTML
		while(noddy.nodeName !== "A" && noddy.nodeName !== "HTML") {
	        noddy = noddy.parentNode;
	    }

		if('href' in noddy && noddy.href.indexOf('http') !== -1 && (noddy.href.indexOf(document.location.host) !== -1 || remotes))
		{
			event.preventDefault();
			document.location.href = noddy.href;
		}

	},false);
}
</script> 
</head>
<style type="text/css" >
html {
    height: 100%;
}

</style>
<body>
    <div class="container">
        <div class="row">
		<br><br>
		<h6 class="text-center"><img src="images/pi-home_logo.png" height="64"> <br><br><?php  echo settings('name') ;?></h6>
            <div class="col-md-4 col-md-offset-4">
                <div class="login-panel panel panel-primary">
                    <div class="panel-heading">
                      Please Sign In
                    </div>
                    <div class="panel-body">
					<div class="row">
					
                        <form method="post" action="index.php" role="form">
<?php  if(isset($error_message)) { echo "<div class=\"alert alert-success alert-dismissable\"> <button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-hidden=\"true\">&times;</button>" . $error_message . "</div>" ;}  ?>
<?php  if(isset($message)) { echo "<div class=\"alert alert-danger alert-dismissable\"> <button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-hidden=\"true\">&times;</button>" . $message . "</div>" ;}  ?>
<br>

                            <fieldset>
                                <div class="form-group">

								<input class="form-control" placeholder="User Name" name="username" type="input" autofocus>
								</div>

                                <div class="form-group">
                                    <input class="form-control" placeholder="Password" name="password" type="password" value="">
                                </div>

                                <!-- Change this to a button or input when using this as a form -->
								<input type="submit" name="submit" value="Login" class="btn btn-block btn-default btn-block login"/>

                            </fieldset>
                        </form>
<br>
                    </div></div>	
<!--<div class="panel-footer">	</div> -->

                 </div>

            
        </div>


    </div>
	<div class="col-md-8 col-md-offset-2">
	<div class="login-panel-foother"> <h6><?php echo settings('name').' '.settings('version')."&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp;Build ".settings('build'); ?>&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp;Powered By: RaspberryPi</h6> </div>
</div>
</div>

    <!-- jQuery -->
    <script src="js/jquery.js"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="js/bootstrap.min.js"></script>

    <!-- Metis Menu Plugin JavaScript -->
    <script src="js/plugins/metisMenu/metisMenu.min.js"></script>

    <!-- Custom Theme JavaScript -->
    <script src="js/sb-admin-2.js"></script>
<script>
//Automatically close alert message  after 5 seconds
window.setTimeout(function() {
    $(".alert").fadeTo(1500, 0).slideUp(500, function(){
        $(this).remove(); 
    });
}, 10000);
</script>
</body>
</html>
<?php if(isset($connection)) { mysql_close($connection); } ?>