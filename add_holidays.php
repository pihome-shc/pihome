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
require_once("st_inc/session.php"); 
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
                           <i class="fa fa-paper-plane fa-1x"></i> Holidays   
						<div class="pull-right"> <div class="btn-group"><?php echo date("H:i"); ?></div> </div>
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">

                <form data-toggle="validator" role="form" method="post" action="holidays.php" id="form-join">

				<div class="checkbox checkbox-default checkbox-circle">
                <input id="checkbox1" class="styled" type="checkbox" name="holidays_enable" value="1" >
                <label for="checkbox1"> Enable </label>
                <div class="help-block with-errors"></div></div>


				<div class="form-group" class="control-label"><label> <i class="fa fa-paper-plane fa-1x"></i> Departure </label>
				<input class="form-control input-sm" id="start_date_time" name="start_date_time" value="" placeholder="Holidays Start Date" required>
                <div class="help-block with-errors"></div></div>
				
				<div class="form-group" class="control-label"><label>  <i class="fa fa-home fa-fw fa-1x"></i> Return </label>
				<input class="form-control input-sm" id="end_date_time" name="end_date_time" value="" placeholder="Holidyas End Date " required>
                <div class="help-block with-errors"></div></div>				


                <a href="holidays.php"><button type="button" class="btn btn-primary btn-sm" >Cancel</button></a>
                <input type="submit" name="submit" value="Submit" class="btn btn-default btn-sm">
				</form>
				
				

						</div>
                        <!-- /.panel-body -->
						<div class="panel-footer">
<?php 
$query="select * from weather";
$result = mysql_query($query, $connection);
confirm_query($result);
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
		
		<?php include("footer.php"); ?>
		
		
		


