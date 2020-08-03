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
				   <div class="text-center"><br><br><p><?php echo $lang['please_wait_text']; ?></p>
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
	<h6><?php echo settings($conn, 'name').' '.settings($conn, 'version')."&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp;".$lang['build']." ".settings($conn, 'build'); ?>&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp;<?php echo $lang['powerd_by_rpi']; ?></h6>
	<br><br>
	<h6><a style="color: #707070;" href="https://en.wikipedia.org/wiki/<?php echo substr($lang['dedicated_to'], strpos($lang['dedicated_to'], ":") + 2, strlen($lang['dedicated_to']) - strpos($lang['dedicated_to'], ":")); ?>" target="_blank" ><?php echo $lang['dedicated_to']; ?></a></h6>
	</div>
	</div>

       </div>
        <!-- /#page-wrapper -->
		<?php include("footer.php"); ?>
