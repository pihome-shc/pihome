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
?>
    </div>
    <!-- /#wrapper -->
    <!-- jQuery -->
    <script src="js/jquery.js"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="js/bootstrap.min.js"></script>

    <!-- Metis Menu Plugin JavaScript -->
    <script src="js/plugins/metisMenu/metisMenu.min.js"></script>

	<!-- bootstrap datepicker JavaScript
	<script src="js/plugins/datepicker/bootstrap-datepicker.js"></script>
 -->
 
    <!-- Custom Theme JavaScript -->
    <script src="js/sb-admin-2.js"></script>
	<script src="js/validator.min.js"></script>
	<script type="text/javascript" src="js/request.js"></script>
	<!-- bootstrap waiting for JavaScript -->
	<script src="js/plugins/waitingfor/bootstrap-waitingfor.min.js"></script>

<script>	
$(document).ready(function() {
//delete record 
$('#confirm-delete').on('show.bs.modal', function(e) {
    $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
});

//Automatically close alert message  after 5 seconds
window.setTimeout(function() {
    $(".alert").fadeTo(1500, 0).slideUp(500, function(){
        $(this).remove(); 
    });
}, 10000);

<?php if ($_SERVER['REQUEST_URI'] == '/home.php'){ ?>
	//load homelist.php  
	$(document).ready(function(){
		$.get('homelist.php', function(output) {
			$('#homelist').html(output).fadeIn(50);
		});
	});
<?php } ?>

<?php if ($_SERVER['REQUEST_URI'] == '/schedule.php'){ ?>
//load schedulelist.php  
$(document).ready(function(){
	$.get('schedulelist.php', function(output) {
		$('#schedulelist').html(output).fadeIn(50);
	});
 });
<?php } ?>

<?php if ($_SERVER['REQUEST_URI'] == '/settings.php'){ ?>
//load settingslist
$(document).ready(function(){
	$.get('settingslist.php', function(output) {
		$('#settingslist').html(output).fadeIn(50);
	});
 });
<?php } ?>

//load overridelist.php  
$('#overridelist').load('overridelist.php');

//load schedulelist.php	
$("#schedulelist").load('schedulelist.php')
	
//load boostlist.php 
$('#boostlist').load('boostlist.php');
		
//load charttlist.php 
$('#chartlist').load('chartlist.php');

//load holidayslist.php 
$('#holidayslist').load('holidayslist.php');

//load holidayslist.php 
$('#nightclimatelist').load('nightclimatelist.php');

} );
</script>

<script>
//Automatically refresh following pages after 15 seconds
$(document).ready(function(){
	window.AutoInterval=setInterval(function(){
		$("#schedulelist").load('schedulelist.php');
		$('#overridelist').load('overridelist.php');
		$('#homelist').load('homelist.php');
		$('#boostlist').load('boostlist.php');
		$('#holidayslist').load('holidayslist.php');
	}, 15000);
});
</script>
<?php if ($_SERVER['REQUEST_URI'] == '/chart.php'){include("chartfooter.php");} ?>
</body>
</html>
<?php if(isset($conn)) { $conn->close();} ?>
