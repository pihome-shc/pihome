<?php require_once("st_inc/session.php"); ?>
<?php confirm_logged_in(); ?>
<?php require_once("st_inc/connection.php"); ?>
<?php require_once("st_inc/functions.php"); ?>
        <ul class="nav nav-pills">
            <button class="btn btn-default btn-circle active" href="#temperature-pills" data-toggle="tab"><i class="fa fa-bar-chart red"></i></i></button>
			<button class="btn btn-default btn-circle" href="#boiler-pills" data-toggle="tab"><i class="glyphicon glyphicon-leaf green"></i></button>	
        </ul>
        <!-- Tab panes -->
        <div class="tab-content">
            <div class="tab-pane fade in active" id="temperature-pills"><br><?php include("chartlist.php"); ?> </div>
            <div class="tab-pane fade" id="boiler-pills"><br><?php include("boilerlist.php"); ?></div>
        </div>