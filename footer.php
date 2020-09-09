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
    var maxField = 10; //Input fields increment limitation
    var addButton = $('.add_button'); //Add button selector
    var wrapper = $('.controler_id_wrapper'); //Input field wrapper
//    var fieldHTML = '<div><input type="text" name="field_name[]" value=""/><a href="javascript:void(0);" class="remove_button"><img src="./images/remove-icon.png"/></a></div>'; //New input field html 

    var controller_HTML = `
                <div class="wrap">
                        <!-- Zone Controller ID -->
                        <div class="form-group" class="control-label"><label><?php echo $lang['zone_controller_id']; ?></label> <small class="text-muted"><?php echo $lang['zone_controler_id_info'];?></small>
                                <input type="hidden" id="selected_controler_id[]" name="selected_controler_id[]" value="<?php echo $zone_controllers[$i]['controler_id']?>"/>
                                <input type="hidden" id="selected_controler_child_id[]" name="selected_controler_child_id[]" value="<?php echo $zone_controllers[$i]['controler_child_id']?>"/>
                                <input type="hidden" id="selected_controler_type[]" name="selected_controler_type[]" value="<?php echo $zone_controllers[$i]['zone_controller_type']?>"/>
                                <div class="entry input-group col-xs-12">
	                                <select id="controler_idx" onchange="ControlerChildList(this.options[this.selectedIndex].value, index_id)" name="controler_idx" class="form-control select2" data-error="<?php echo $lang['zone_controller_id_error']; ?>" autocomplete="off" >
                                                <?php  $query = "SELECT node_id, type, max_child_id FROM nodes where name LIKE '%Controller%'  ORDER BY node_id ASC;";
                                                $result = $conn->query($query);
                                                echo "<option></option>";
                                                while ($datarw=mysqli_fetch_array($result)) {
                                                        echo "<option value=".$datarw['max_child_id'].">".$datarw['type'].' - '.$datarw['node_id']."</option>";
                                                } ?>
                                        </select>
                                        <div class="help-block with-errors"></div>
                                        <span class="input-group-btn">
                                                <a href="javascript:void(0);" class="remove_button"><img src="./images/remove-icon.png"/></a>
                                        </span>
                                </div>
                        </div>

                        <input type="hidden" id="gpio_pin_list" name="gpio_pin_list" value="<?php echo implode(",", array_filter(Get_GPIO_List()))?>"/>
                        <!-- Zone Controller Child ID -->
                        <div class="form-group" class="control-label"><label><?php echo $lang['zone_controller_child_id']; ?></label> <small class="text-muted"><?php echo $lang['zone_controler_child_id_info'];?></small>
		                <select id="controler_child_idx" name="controler_child_idx" onchange="setChild_id(this.options[this.selectedIndex].value, index_id)" class="form-control select2"  data-error="<?php echo $lang['zone_controller_child_id_error']; ?>" autocomplete="off">
                                </select>
                                <div class="help-block with-errors"></div>
                        </div>
                </div>
		`;

    //Once add button is clicked
    $(addButton).click(function(){
        //Check maximum number of input fields
	var x = document.getElementById("controller_count").value
        var temp_HTML = controller_HTML.replace(/controler_idx/g, "controler_id".concat(x));
	temp_HTML = temp_HTML.replace(/controler_child_idx/g, "controler_child_id".concat(x));
        temp_HTML = temp_HTML.replace(/index_id/g, x);
        if(x < maxField){ 
            $(wrapper).append(temp_HTML); //Add field html
            x++; //Increment field counter
	    document.getElementById("controller_count").value = x;
        }
    });
    
    //Once remove button is clicked
    $(wrapper).on('click', '.remove_button', function(e){
	var x = document.getElementById("controller_count").value
        e.preventDefault();
        x--; //Decrement field counter
        document.getElementById("controller_count").value = x;
        $(this).parents('.wrap:first').remove(); //Remove field html
    });

});

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
$('#chart_dailyusage').load('chart_dailyusage.php');

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
if ($_SERVER['SCRIPT_NAME'] == '/scheduling.php'){
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

<?php if (($_SERVER['REQUEST_URI'] == '/holiday.php') OR ($_SERVER['SCRIPT_NAME'] == '/holiday.php')){ ?>
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
<?php if (($_SERVER['SCRIPT_NAME'] == '/scheduling.php') OR ($_SERVER['SCRIPT_NAME'] == '/schedule.php')){ ?>
	 // popover
	$("[data-toggle=popover]")
		.popover()
<?php } ?>
</script>

<?php if ($_SERVER['REQUEST_URI'] == '/chart.php'){include("chart_load.php");} ?>

<?php
//Function to check if email address is valid
function checkEmail($email) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return false;
        } else {
                return true;
        }
}

//Set user id from user session variable
$user_id = $_SESSION['user_id'];
$query = "select * from user where id = '{$user_id}' LIMIT 1;";
$result = $conn->query($query);
$user_row = mysqli_fetch_array($result);
$email = $user_row['email'];
//Check if email address exit
if (!checkEmail($email)){
        echo "
                <script>
                        $(document).ready(function(){
                        $(\"#user_email_Modal\").modal('show');
                        });
                </script>";
}?>

</body>
</html>
<?php if(isset($conn)) { $conn->close();} ?>
