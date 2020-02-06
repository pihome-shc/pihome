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
    <script type="text/javascript">
        $.ajaxSetup ({
            // Disable caching of AJAX responses
            cache: false
        });
    </script>
    <!-- Bootstrap Core JavaScript -->
    <script src="js/bootstrap.min.js"></script>

    <!-- Metis Menu Plugin JavaScript -->
    <script src="js/plugins/metisMenu/metisMenu.min.js"></script>

	<!-- bootstrap datepicker JavaScript -->
	<script src="js/plugins/datepicker/bootstrap-datetimepicker.js"></script>

 
    <!-- Custom Theme JavaScript -->
    <script src="js/sb-admin-2.js"></script>
	<script src="js/validator.min.js"></script>
	<script type="text/javascript" src="js/request.js"></script>
	<!-- bootstrap waiting for JavaScript -->
	<script src="js/plugins/waitingfor/bootstrap-waitingfor.min.js"></script>

	<!-- bootstrap slider -->
	<script src="js/plugins/slider/bootstrap-slider.min.js"></script>

        <!-- bootstrap confirmation -->
        <script src="js/plugins/confirmation/bootstrap-confirmation.min.js"></script>

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
$("#schedulelist").load('schedulelist.php');
	
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

<script>
<?php 
if (($_SERVER['SCRIPT_NAME'] == '/schedule_edit.php') OR ($_SERVER['SCRIPT_NAME'] == '/schedule_add.php')){
	$query = "select * from zone where status = 1;";
	$results = $conn->query($query);	
/*	while ($row = mysqli_fetch_assoc($results)) { ?>
		var slider<?php echo $row["id"];?> = document.getElementById("bb<?php echo $row["id"];?>");
		var output<?php echo $row["id"];?> = document.getElementById("val<?php echo $row["id"];?>");
		output<?php echo $row["id"];?>.innerHTML = slider<?php echo $row["id"];?>.value;
		slider<?php echo $row["id"];?>.oninput = function() {
		output<?php echo $row["id"];?>.innerHTML = this.value;
		}
<?php
	}
*/
}
?>

<?php if (($_SERVER['REQUEST_URI'] == '/holidays_add.php') OR ($_SERVER['SCRIPT_NAME'] == '/holidays_edit.php')){ ?>
    $(".form_datetime").datetimepicker({
        //format: "dd MM yyyy - hh:ii",
		format: "yyyy-mm-dd hh:ii",
        autoclose: true,
        todayBtn: true,
        startDate: "2019-07-09 10:00",
        minuteStep: 10
    });
<?php } ?>
</script>

<script>
<?php if (($_SERVER['SCRIPT_NAME'] == '/schedule_edit.php') OR ($_SERVER['SCRIPT_NAME'] == '/schedule_add.php') OR ($_SERVER['SCRIPT_NAME'] == '/schedule.php')){ ?>
	 // popover
	$("[data-toggle=popover]")
		.popover()
<?php } ?>
</script>

<?php if ($_SERVER['REQUEST_URI'] == '/chart.php'){include("chartfooter.php");} ?>
</body>
</html>
<?php if(isset($conn)) { $conn->close();} ?>
