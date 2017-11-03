<?php require_once("st_inc/session.php"); ?>
<?php confirm_logged_in(); ?>
<?php require_once("st_inc/connection.php"); ?>
<?php require_once("st_inc/functions.php"); ?>
<?php
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
	$old_pass = mysql_real_escape_string(md5($_POST['old_pass']));
	$new_pass = mysql_real_escape_string(md5($_POST['new_pass']));
	$con_pass = mysql_real_escape_string(md5($_POST['con_pass']));
	
	$query = "SELECT * FROM user WHERE id = {$id}";
	$results = mysql_query($query, $connection);	
	$user_oldpass = mysql_fetch_assoc($results);
	if ($user_oldpass['password'] != $old_pass ){
		$error_message = 'Your Old Password is Incorrect!';
	} else {
		if ( !isset($error_message) && ($new_pass == $con_pass)) {
			$cpdate= date("Y-m-d H:i:s");
			$query = "UPDATE user SET password = '{$new_pass}', cpdate = '{$cpdate}' WHERE id = '{$id}' LIMIT 1";
			$result = mysql_query($query, $connection);
				if ($result) {
					$message_success = $LANG['password_changed'];
					header("Refresh: 10; url=home.php");
				} else {
					$error = "<p>{$LANG['password_x_changed']}</p>";
					$error .= "<p>" . mysql_error() . "</p>";
				}
		} 
	}
}
	
$query = "SELECT * FROM user WHERE id = {$id}";
$results = mysql_query($query, $connection);	
$row = mysql_fetch_assoc($results);
?>
<?php include("header.php"); ?>
<?php include_once("notice.php"); ?>
        <div id="page-wrapper">
<br>
            <div class="row">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <i class="fa fa-key fa-fw"></i> <?php echo $LANG['change_password'] ?>
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
				<p > <?php echo $LANG['change_password_introtext']; ?>.  <p class="text-danger"> <strong>Do not use any special character i.e 
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
                <div class="help-block with-errors"></div></div>

                <div class="form-group"><label><?php echo $LANG['new_password']; ?></label>
                <input class="form-control" type="password" class="form-control" placeholder="New Password" value="" id="example-progress-bar" name="new_pass" data-error="New Password is Required" autocomplete="off" required> 
                <div class="help-block with-errors"></div></div>
				
                <div class="form-group"><label><?php echo $LANG['confirm_password']; ?></label>
                <input class="form-control" type="password" class="form-control" placeholder="Confirm New Password" value="" id="con_pass" name="con_pass" data-error="Confirm New Password is Required" autocomplete="off" required> 
                <div class="help-block with-errors"></div></div>
				<a href="home.php"><button type="button" class="btn btn-primary btn-sm">Cancel</button></a>
                <input type="submit" name="submit" value="Submit" class="btn btn-default btn-sm">
                        
                </form>
                        </div>
                        <!-- /.panel-body -->
						<div class="panel-footer">

<?php 
$query="select * from weather";
$result = mysql_query($query, $connection);
$weather = mysql_fetch_array($result);
?>

Outside: <?php //$weather = getWeather(); ?><?php echo $weather['c'] ;?>&deg;C
<span><img border="0" width="24" src="images/<?php echo $weather['img'];?>.png" title="<?php echo $weather['title'];?> - 
<?php echo $weather['description'];?>"></span> <span><?php echo $weather['title'];?> - 
<?php echo $weather['description'];?></span>
                        </div>
                    </div>
                </div>

                <!-- /.col-lg-4 -->
            </div>
            <!-- /.row -->
        </div>
        <!-- /#page-wrapper -->
		
<?php include("footer.php");  ?>