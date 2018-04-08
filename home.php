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
?>
<?php include("header.php"); ?>
        <div id="page-wrapper">
<br>
            <div class="row">
                <div class="col-lg-12">
                   	<div id="homelist" >
				   <div class="text-center"><br><br><p>Please wait while system grab latest information from database...</p>
				   <br><br><img src="images/loader.gif">
				   <br><br><br><br>
				   </div>
				   </div>
                </div>
                <!-- /.col-lg-4 -->
            </div>
			<!-- /.row -->
	<div class="col-md-8 col-md-offset-2">
	<div class="login-panel-foother">
	<h6><a style="color: #707070;" href="https://en.wikipedia.org/wiki/Sudan_(rhinoceros)" target="_blank" >Dedicated to Sudan (Rhinoceros) 1973 - 2018</a></h6>
	</div>
	</div>
	
            
        </div>
        <!-- /#page-wrapper -->
		<?php include("footer.php"); ?>