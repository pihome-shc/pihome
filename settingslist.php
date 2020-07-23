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
//query to frost protection temperature 
$query = "SELECT * FROM frost_protection LIMIT 1 ";
$result = $conn->query($query);
$frosttemp = mysqli_fetch_array($result);
$frost_temp = $frosttemp['temperature'];
?>
<script language="javascript" type="text/javascript">
$("#ajaxModal").on("show.bs.modal", function(e) {
    //console.log($(e.relatedTarget).data('ajax'));
    $(this).find("#ajaxModalLabel").html("...");
    $(this).find("#ajaxModalBody").html("Waiting ...");
    $(this).find("#ajaxModalFooter").html("...");
    $(this).find("#ajaxModalContent").load($(e.relatedTarget).data('ajax'));
});    
</script>
	<div class="panel panel-primary">
        	<div class="panel-heading">
                	<i class="fa fa-cog fa-fw"></i>   
			<?php echo $lang['settings']; ?>    
			<div class="pull-right"> 
				<div class="btn-group"><?php echo date("H:i"); ?> 
				</div>
			</div>
              	</div>
                <!-- /.panel-heading -->
		<div class="panel-group">
			<div class="panel-body">
				<div class="accordion" id="accordion">
    					<div class="panel">
                      	        		<ul class="nav nav-pills">
							<button class="btn-lg btn-default btn-circle" href="#collapse_status" data-toggle="collapse" data-parent="#accordion" data-toggle="tooltip" title="<?php echo $lang['tooltip_1']; ?>"><i class="fa fa-tachometer orange"></i></button>
							<button class="btn-lg btn-default btn-circle" href="#collapse_system" data-toggle="collapse" data-parent="#accordion" data-toggle="tooltip" title="<?php echo $lang['tooltip_2']; ?>"><i class="fa fa-cogs green"></i></button>
							<button class="btn-lg btn-default btn-circle" href="#collapse_boiler" data-toggle="collapse" data-parent="#accordion" data-toggle="tooltip" title="<?php echo $lang['tooltip_3']; ?>"><i class="fa fa-fire red"></i></button>
							<button class="btn-lg btn-default btn-circle" href="#collapse_nodes" data-toggle="collapse" data-parent="#accordion"" data-toggle="tooltip" title="<?php echo $lang['tooltip_4']; ?>"><i class="fa fa-sitemap blue"></i></button>
							<br><br>
						</ul>

			                	<div id="collapse_status" class="panel-collapse collapse animated fadeIn">
							<h4 class="pull-left"><?php echo $lang['system_status']; ?></h4><br>
		        			        <button type="button" class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" data-toggle="modal" data-target="#wifi_setup">
        		        		        <h3 class="buttontop"><small><?php echo $lang['wifi']; ?></small></h3>
                		        	        <h3 class="degre" ><i class="fa fa-signal green"></i></h3>
                        		        	<h3 class="status"></small></h3>
		                	               	</button>

		        	        	     	<button type="button" class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" data-toggle="modal" data-target="#eth_setup">
        		        	        	<h3 class="buttontop"><small><?php echo $lang['ethernet']; ?></small></h3>
		                		        <h3 class="degre" ><i class="ionicons ion-network orange"></i></h3>
		                        		<h3 class="status"></small></h3>
			                        	</button>

	        		                        <button class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" data-href="edit_gpio.php" data-toggle="modal" data-target="#cron_jobs">
	        			                <h3 class="buttontop"><small><?php echo $lang['cron_jobs']; ?></small></h3>
	        	        			<h3 class="degre" ><i class="ionicons ion-ios-timer-outline blue"></i></h3>
        	        	        		<h3 class="status"></small></h3>
	                	        	        </button>

			        		        <button type="button" class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" data-toggle="modal" data-remote="false" data-target="#ajaxModal" data-target="#ajaxModal" data-ajax="ajax.php?Ajax=GetModal_Uptime">
				        	        <h3 class="buttontop"><small><?php echo $lang['update_etc']; ?></small></h3>
                					<h3 class="degre" ><i class="ionicons ion-clock red"></i></h3>
                        				<h3 class="status"></small></h3>
                                			</button>

		                                	<button type="button" class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" data-toggle="modal" data-target="#os_version">
			        	                <h3 class="buttontop"><small><?php echo $lang['os_version']; ?></small></h3>
		        		        	<h3 class="degre" ><i class="fa fa-linux"></i></h3>
        		        		        <h3 class="status"></small></h3>
                		        		</button>

		        		        	<button class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" data-toggle="modal" data-remote="false" data-target="#ajaxModal" data-ajax="ajax.php?Ajax=GetModal_Services">
        		        		        <h3 class="buttontop"><small><?php echo $lang['services']; ?></small></h3>
                		        		<h3 class="degre" ><i class="ionicons ion-ios-cog-outline"></i></h3>
		                        		<h3 class="status"></small></h3>
			                                </button>

				                        <?php
	        				        $query = "select * from messages_in where node_id = 0 order by datetime desc limit 1";
        	        				$result = $conn->query($query);
                	        			$result = mysqli_fetch_array($result);
                        	        		$system_cc = $result['payload'];
			                        	if ($system_cc < 40){$system_cc="#0bb71b"; $fan=" ";}elseif ($system_cc < 50){$system_cc="#F0AD4E"; $fan="fa-pulse";}elseif ($system_cc > 50){$system_cc="#ff0000"; $fan="fa-pulse";}
			        	                ?>
				                	<button type="button" class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" data-toggle="modal" data-remote="false"  data-target="#ajaxModal" data-ajax="ajax.php?Ajax=GetModal_System">
        				               	<h3 class="buttontop"><small><?php echo $lang['system']; ?> &deg;</small></h3>
                				        <h3 class="degre" ><i class="fa fa-server fa-1x green"></i></h3>
	                				<h3 class="status">
        	              				<small class="statuscircle" style="color:<?php echo $system_cc;?>"><i class="fa fa-circle fa-fw"></i></small>
	                	                	<small class="statusdegree"><?php echo number_format(DispTemp($conn,$result['payload']),0);?>&deg;</small>
				                       	<small class="statuszoon"><i class="fa fa-asterisk <?php echo $fan;?>"></i></small></h3>
        				                </button>

                		                   	<button type="button" class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" data-href="#" data-toggle="modal" data-target="#big_thanks">
                        		                <h3 class="buttontop"><small><?php echo $lang['big_thanks']; ?></small></h3>
                                		        <h3 class="degre" ><i class="ionicons ion-help-buoy blueinfo"></i></h3>
                                        		<h3 class="status"></small></h3>
	                                              	</button>
		                		</div>

				             	<div id="collapse_system" class="panel-collapse collapse animated fadeIn">
							<h4 class="pull-left"><?php echo $lang['system_configuration']; ?></h4><br>

                                                	<button class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" data-href="#" data-toggle="modal" data-target="#network_setting">
                                                        <h3 class="buttontop"><small><?php echo $lang['network']; ?></small></h3>
                                                        <h3 class="degre" ><i class="ionicons ion-network blue"></i></h3>
                                                        <h3 class="status"></small></h3>
                                                        </button>

							<button class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" data-href="edit_piconnect.php" data-toggle="modal" data-target="#piconnect">
        	                		        <h3 class="buttontop"><small>PiConnect</small></h3>
			                		<h3 class="degre" ><i class="fa fa-plug green"></i></h3>
	                			      	<h3 class="status"></small></h3>
        	                        		</button>

				                       	<?php
				                        $c_f = settings($conn, 'c_f');
	        				        if($c_f==1 || $c_f=='1')
        	        					$TUnit='F';
                	     				else
	                        	      			$TUnit='C';
			                	       	?>
        			                	<button class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" data-toggle="modal" data-target="#change_units">
	                			        <h3 class="buttontop"><small><?php echo $lang['units']; ?></small></h3>
		                	        	<h3 class="degre" ><?php echo $TUnit;?></h3>
        		                	        <h3 class="status"></small></h3>
	        		                	</button>

	         	        		        <button class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" data-href="#" data-toggle="modal" data-target="#language">
		                			<h3 class="buttontop"><small><?php echo $lang['language']; ?></small></h3>
        		                		<h3 class="degre" ><i class="fa fa-language fa-1x blueinfo"></i></h3>
                		                	<h3 class="status"></small></h3>
		                	        	</button>

			                	      	<button class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" data-href="edit_gpio.php" data-toggle="modal" data-target="#time_zone">
        			                	<h3 class="buttontop"><small><?php echo $lang['time_zone']; ?></small></h3>
		                			<h3 class="degre" ><i class="fa fa-globe green"></i></h3>
        		                		<h3 class="status"></small></h3>
                		                	</button>

		        	        	       	<button class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" data-toggle="modal" data-remote="false" data-target="#ajaxModal" data-ajax="ajax.php?Ajax=GetModal_OpenWeather">
        		        	        	<h3 class="buttontop"><small><?php echo $lang['openweather']; ?></small></h3>
	                			        <h3 class="degre" ><i class="fa fa-sun-o"></i></h3>
		                	       		<h3 class="status"></small></h3>
	        		                	</button>

		                		      	<button class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" data-href="#" data-toggle="modal" data-target="#zone_graph">
        		                		<h3 class="buttontop"><small><?php echo $lang['graph']; ?></small></h3>
	                		        	<h3 class="degre" ><i class="fa fa-bar-chart fa-1x blueinfo"></i></h3>
		                		       	<h3 class="status"></small></h3>
        		                		</button>

	                		        	<button class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" data-href="#" data-toggle="modal" data-target="#email_setting">
			                        	<h3 class="buttontop"><small><?php echo $lang['email']; ?></small></h3>
        			                        <h3 class="degre" ><i class="fa fa-envelope blueinfo"></i></h3>
	        			                <h3 class="status"></small></h3>
	        		        		</button>

	        	        	        	<button class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" data-toggle="modal" data-remote="false" data-target="#ajaxModal" data-ajax="ajax.php?Ajax=GetModal_MQTT">
		        	        	    	<h3 class="buttontop"><small><?php echo $lang['mqtt']; ?></small></h3>
                	        			<h3 class="degre" ><?php echo $lang['mqtt']; ?></h3>
				        	        <h3 class="status"></small></h3>
			                		</button>

	        		              		<button type="button" class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" data-toggle="modal" data-target="#pihome_update">
        	        		               	<h3 class="buttontop"><small><?php echo $lang['pihome_update']; ?></small></h3>
		        			        <h3 class="degre" ><i class="fa fa-download fa-1x blueinfo"></i></h3>
        		        			<h3 class="status"></small></h3>
                		       			</button>

	                        		        <button type="button" class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" data-toggle="modal" data-target="#backup_image">
			                        	<h3 class="buttontop"><small><?php echo $lang['backup']; ?></small></h3>
				                       	<h3 class="degre" ><i class="fa fa-clone fa-1x blue"></i> </h3>
        				                <h3 class="status"></small></h3>
	                				</button>

        	                			<button type="button" class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" data-href="#" data-toggle="modal" data-target="#user_setup">
                	                		<h3 class="buttontop"><small><?php echo $lang['user_accounts']; ?></small></h3>
			        	               	<h3 class="degre" ><i class="ionicons ion-person blue"></i></h3>
        			        	        <h3 class="status"></small></h3>
	        	        		       	</button>
        	        	        	</div>

			        	        <div id="collapse_boiler" class="panel-collapse collapse animated fadeIn">
							<h4 class="pull-left"><?php echo $lang['boiler_configuration']; ?></h4><br>
	                				<button class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" data-toggle="modal" data-target="#add_frost">
		                                	<h3 class="buttontop"><small><?php echo $lang['frost']; ?> </small></h3>
				                       	<h3 class="degre" ><i class="ionicons ion-ios-snowy blue"></i></h3>
                				        <h3 class="status">
	                	        	        <small class="statuscircle"><i class="fa fa-circle fa-fw blue"></i></small>
			        	        	<small class="statusdegree"><?php echo number_format(DispTemp($conn,$frost_temp),0);?>&deg;</small>
                				      	<small class="statuszoon"><i class="fa"></i></small></h3>
                        	        		</button>

				        	       	<button class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" data-href="edit_boiler.php" data-toggle="modal" data-target="#boiler">
        		        			<h3 class="buttontop"><small><?php echo $lang['boiler']; ?></small></h3>
                		                	<h3 class="degre" ><i class="ionicons ion-flame fa-1x red"></i></h3>
		        		                <h3 class="status"></small></h3>
	                				</button>

        	                        		<button class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" data-href="edit_gpio.php" data-toggle="modal" data-target="#boost_setup">
			                        	<h3 class="buttontop"><small><?php echo $lang['boost']; ?></small></h3>
		                			<h3 class="degre" ><i class="fa fa-rocket fa-1x blueinfo"></i></h3>
        		                	        <h3 class="status"></small></h3>
				                	</button>

	                				<button class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" data-href="edit_override.php" data-toggle="modal" data-target="#override_setup">
        	                	        	<h3 class="buttontop"><small><?php echo $lang['override']; ?></small></h3>
			                	       	<h3 class="degre" ><i class="fa fa-refresh fa-1x blue"></i></h3>
                			        	<h3 class="status"></small></h3>
		                        	        </button>
        		                        </div>

			                       	<div id="collapse_nodes" class="panel-collapse collapse animated fadeIn">
							<h4 class="pull-left"><?php echo $lang['node_zone_configuration']; ?></h4><br>
		                			<button class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" data-href="edit_gpio.php" data-toggle="modal" data-target="#zone_setup">
        		                	        <h3 class="buttontop"><small><?php echo $lang['zone']; ?></small></h3>
				                	<h3 class="degre" ><i class="glyphicon glyphicon-th-large orange"></i> </h3>
	                				<h3 class="status"></small></h3>
        	                        		</button>

                                			<button class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" data-href="edit_override.php" data-toggle="modal" data-target="#zone_types">
                                                        <h3 class="buttontop"><small><?php echo $lang['zone_type']; ?></small></h3>
                                                        <h3 class="degre" ><i class="fa fa-list-ol orange"></i></h3>
                                                        <h3 class="status"></small></h3>
                                                        </button>

			        	        	<button class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" data-href="edit_override.php" data-toggle="modal" data-target="#nodes">
        	        			       	<h3 class="buttontop"><small><?php echo $lang['node']; ?></small></h3>
                	                		<h3 class="degre" ><i class="fa fa-sitemap fa-1x green"></i></h3>
				                        <h3 class="status"></small></h3>
		                	 		</button>
 
                			                <button class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" data-href="#" data-toggle="modal" data-target="#node_alerts">
		                	                <h3 class="buttontop"><small><?php echo $lang['node_alerts']; ?></small></h3>
				        	        <h3 class="degre" ><i class="ion-android-notifications-none blueinfo"></i></h3>
                					<h3 class="status"></small></h3>
	                        		        </button>

							<button class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" data-href="edit_sensors.php" data-toggle="modal" data-target="#temperature_sensor">
				                	<h3 class="buttontop"><small><?php echo $lang['sensors']; ?></small></h3>
	                				<h3 class="degre" ><i class="ionicons ion-thermometer red"></i></h3>
        	                        		<h3 class="status"></small></h3>
			                        	</button>
									  
				                       	<button class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" data-href="#" data-toggle="modal" data-target="#sensor_gateway">
        	        			        <h3 class="buttontop"><small><?php echo $lang['gateway']; ?></small></h3>
	        	        	                <h3 class="degre" ><i class="fa fa-heartbeat red"></i></h3>
				        	        <h3 class="status"></small></h3>
                					</button>

                                                	<button class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" data-href="#" data-toggle="modal" data-target="#add_on_http">
                                                        <h3 class="buttontop"><small><?php echo $lang['add_on']; ?></small></h3>
                                                        <h3 class="degre" ><?php echo $lang['add_on_http']; ?></h3>
                                                        <h3 class="status"></small></h3>
                                                        </button>
			                	</div>
					</div>
					 <!-- /.panel -->
				</div>
				 <!-- /.accordion -->

				<!-- Generic Ajax Modal -->
				<div class="modal fade" id="ajaxModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  					<div class="modal-dialog">
    						<div class="modal-content" id="ajaxModalContent">
      							<div class="modal-header">
        							<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
								<h5 class="modal-title" id="ajaxModalLabel">...</h5>
      							</div>
      							<div class="modal-body" id="ajaxModalBody">
       								<?php echo $lang['waiting']; ?>
     							</div>
      							<div class="modal-footer" id="ajaxModalFooter">
        							...
      							</div>
    						</div>
  					</div>
				</div>
				<?php include("model.php");  ?>

			</div>
			<!-- /.panel-body -->
			<div class="panel-body">
                        	<button type="button" class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" data-toggle="modal" data-target="#reboot_system">
	                        <h3 class="buttontop"><small><?php echo $lang['reboot_pi']; ?></small></h3>
        	                <h3 class="degre" ><i class="ion-ios-refresh-outline orange"></i></h3>
                	        <h3 class="status"></small></h3>
                        	</button>

	                        <button type="button" class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" data-toggle="modal" data-target="#shutdown_system">
        	                <h3 class="buttontop"><small><?php echo $lang['shutdown_pi']; ?></small></h3>
                	        <h3 class="degre" ><i class="fa fa-power-off fa-1x red"></i></h3>
                        	<h3 class="status"></small></h3>
	                        </button>
			</div>
                	<!-- /.panel-body -->
		</div>
		<!-- /.panel-group -->
		<div class="panel-footer">
			<?php
			ShowWeather($conn);
			?>
    		</div>
		<!-- /.panel-footer -->
	</div>
	<!-- /.panel-primary -->
<script>
$(document).ready(function(){
    $('[data-toggle="tooltip"]').tooltip();   
});
</script>
