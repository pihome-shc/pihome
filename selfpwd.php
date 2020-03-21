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
confirm_logged_in();
require_once(__DIR__.'/st_inc/connection.php');
require_once(__DIR__.'/st_inc/functions.php');

$id = $_SESSION['user_id'];
if (isset($_POST['submit'])) { 
	if ((!isset($_POST['old_pass'])) || (empty($_POST['old_pass']))) {
		$error_message = $LANG['old_password_error'];
	}elseif ((!isset($_POST['new_pass'])) || (empty($_POST['new_pass']))) {
		$error_message = $LANG['new_password_error'];
	} elseif((!isset($_POST['con_pass'])) || (empty($_POST['con_pass']))) {
		$error_message = $LANG['conf_password_error'];
	} elseif($_POST['new_pass'] != $_POST['con_pass']) {
		$error_message = $LANG['conf_password_error2'];
	}
	$old_pass = mysqli_real_escape_string($conn,(md5($_POST['old_pass'])));
	$new_pass = mysqli_real_escape_string($conn,(md5($_POST['new_pass'])));
	$con_pass = mysqli_real_escape_string($conn,(md5($_POST['con_pass'])));
	
	$query = "SELECT * FROM user WHERE id = {$id}";
	$results = $conn->query($query);	
	$user_oldpass = mysqli_fetch_assoc($results);
	if ($user_oldpass['password'] != $old_pass ){
		$error_message = 'Your Old Password is Incorrect!';
	} else {
		if ( !isset($error_message) && ($new_pass == $con_pass)) {
			$cpdate= date("Y-m-d H:i:s");
			$query = "UPDATE user SET password = '{$new_pass}', cpdate = '{$cpdate}' WHERE id = '{$id}' LIMIT 1";
			$result = $conn->query($query);
				if ($result) {
					$message_success = "Password is successfully changed!!!";
					header("Refresh: 10; url=home.php");
				} else {
					$error = "<p>Password chaneg failed!!!</p>";
					$error .= "<p>".mysqli_error($conn)."</p>";
				}
		}
	}
}
$query = "SELECT * FROM user WHERE id = {$id}";
$results = $conn->query($query);	
$row = mysqli_fetch_assoc($results);
?>
<?php include("header.php"); ?>
<?php include_once("notice.php"); ?>
<div id="page-wrapper">
	<br>
	<div class="row">
        	<div class="panel panel-primary">
                	<div class="panel-heading">
                        	<i class="fa fa-key fa-fw"></i> Change your password!!!
                        </div>
                        <!-- /.panel-heading -->
              		<div class="panel-body">
				<p > Please enter your password details below.  <p class="text-danger"> <strong>Do not use any special character i.e 
				' &nbsp;&nbsp; ` &nbsp;&nbsp; , &nbsp;&nbsp; & &nbsp;&nbsp; ? &nbsp;&nbsp; { &nbsp;&nbsp; } &nbsp;&nbsp; [ &nbsp;&nbsp; ] &nbsp;&nbsp; ( &nbsp;&nbsp; ) &nbsp;&nbsp; - &nbsp;&nbsp; &nbsp;&nbsp; ; &nbsp;&nbsp; ! &nbsp;&nbsp; ~ &nbsp;&nbsp; * &nbsp;&nbsp; % &nbsp;&nbsp; \ &nbsp;&nbsp; |</strong></p> 
        	        	<form method="post" action="<?php $PHP_SELF ?>" data-toggle="validator" role="form" >

				<div class="form-group"><label><?php echo $LANG['fullname']; ?></label>
                			<input type="text" class="form-control" placeholder="Full Name" value="<?php echo $row['fullname'] ;?>" disabled> 
	                	</div>

        	        	<div class="form-group"><label><?php echo $LANG['username']; ?></label>
                			<input type="text" class="form-control" placeholder="User Name" value="<?php echo $row['username'] ;?>" disabled> 
                		</div>

	                	<div class="form-group"><label><?php echo $LANG['old_password']; ?></label>
        	        		<input class="form-control" type="password" class="form-control" placeholder="Old Password" value="" id="old_pass" name="old_pass" data-error="Old Password is Required" autocomplete="off" required> 
                			<div class="help-block with-errors"></div>
				</div>

	                	<div class="form-group"><label><?php echo $LANG['new_password']; ?></label>
        	        		<input class="form-control" type="password" class="form-control" placeholder="New Password" value="" id="example-progress-bar" name="new_pass" data-error="New Password is Required" autocomplete="off" required> 
                			<div class="help-block with-errors"></div>
				</div>

	                	<div class="form-group"><label><?php echo $LANG['confirm_password']; ?></label>
        	        		<input class="form-control" type="password" class="form-control" placeholder="Confirm New Password" value="" id="con_pass" name="con_pass" data-error="Confirm New Password is Required" autocomplete="off" required> 
                			<div class="help-block with-errors"></div>
				</div>
				<a href="home.php"><button type="button" class="btn btn-primary btn-sm">Cancel</button></a>
        	        	<input type="submit" name="submit" value="Submit" class="btn btn-default btn-sm">

                		</form>
	           	</div>
        	        <!-- /.panel-body -->
			<div class="panel-footer">
				<?php 
				ShowWeather($conn);
				?>
                    	</div>
			<!-- /.panel-footer -->
  		</div>
                <!-- /.panel-primary -->
	</div>
        <!-- /.row -->
</div>
<!-- /#page-wrapper -->
<?php include("footer.php");  ?>
