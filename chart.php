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
				                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <i class="fa fa-bar-chart fa-fw"></i> <?php echo $lang['graph']; ?>   
						<div class="pull-right"> <div class="btn-group"><?php echo date("H:i"); ?></div> </div>
                        </div>
                        <!-- /.panel-heading -->
 <div class="panel-body">
                        <!-- Nav tabs -->
        <ul class="nav nav-pills">
            <button class="btn btn-default btn-circle active" href="#temperature-pills" data-toggle="tab"><i class="fa fa-bar-chart red"></i></i></button>
			<button class="btn btn-default btn-circle" href="#boiler-pills" data-toggle="tab"><i class="glyphicon glyphicon-leaf green"></i></button>
			<button class="btn btn-default btn-circle" href="#month-pills" data-toggle="tab"><i class="fa fa-area-chart blue"></i></button>			
        </ul>
        <!-- Tab panes -->
        <div class="tab-content">
            <div class="tab-pane fade in active" id="temperature-pills"><br><?php include("chartlist.php"); ?></div>
            <div class="tab-pane fade" id="boiler-pills"><br><?php include("boilerlist.php"); ?></div>
			<div class="tab-pane fade" id="month-pills"><br><?php include("monthusage.php"); ?></div>
        </div>
</div>
                       <!-- /.panel-body -->
						<div class="panel-footer">
<?php 
ShowWeather($conn);
?>
                        </div>
                    </div>
                </div>
                </div>
                <!-- /.col-lg-4 -->
            </div>
            <!-- /.row -->
        </div>
        <!-- /#page-wrapper -->
		<?php include("footer.php"); ?>