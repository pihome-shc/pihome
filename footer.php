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


/*
$(document).ready(function() {
			//date picker java
			$('#start_date_time').datepicker({
            icons: {time: "fa fa-clock-o",date: "fa fa-calendar", up: "fa fa-arrow-up", down: "fa fa-arrow-down"},
			todayHighlight: true,
			format: "yyyy/mm/dd",
            autoclose: true
            });
			$('#end_date_time').datepicker({
            todayHighlight: true,
			format: "yyyy/mm/dd",
            autoclose: true
            });
} );
*/

//load schedulelist.php  
$(document).ready(function(){
	$.get('schedulelist.php', function(output) {
		$('#schedulelist').html(output).fadeIn(50);
	});
 });
 //$('#schedulelist').load('schedulelist.php');

//load schedulelist.php  
$('#overridelist').load('overridelist.php');

//load homelist.php  
$(document).ready(function(){
	$.get('homelist.php', function(output) {
		$('#homelist').html(output).fadeIn(50);
	});
 });
 
//load settingslist
$(document).ready(function(){
	$.get('settingslist.php', function(output) {
		$('#settingslist').html(output).fadeIn(50);
	});
 });

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
	setInterval(function(){
		$("#schedulelist").load('schedulelist.php')
		$('#overridelist').load('overridelist.php');
		$('#homelist').load('homelist.php');
		$('#boostlist').load('boostlist.php');
		$('#holidayslist').load('holidayslist.php');
	}, 15000);
});
</script>
<?php if (isset($graphs_page)){include("chartfooter.php");} ?>
</body>
</html>
<?php if(isset($connection)) { mysql_close($connection); } ?>