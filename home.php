<?php require_once("st_inc/session.php"); ?>
<?php confirm_logged_in(); ?>
<?php require_once("st_inc/connection.php"); ?>
<?php require_once("st_inc/functions.php"); ?>
<?php include("header.php"); ?>
        <div id="page-wrapper">
<br>
            <div class="row">
                <div class="col-lg-12">
                   	<div id="homelist" >
				   <div class="text-center"><br><br><p>Please wait while system grab latest information from database...</p>
				   <br><br><img src="images/loader.gif">
				   </div>
				   </div>
                </div>

                <!-- /.col-lg-4 -->
            </div>
            <!-- /.row -->
        </div>
        <!-- /#page-wrapper -->
<?php include("footer.php"); ?>