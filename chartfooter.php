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

// weather table to get sunrise and sun set time 
$query="select * from weather";
$result = $conn->query($query);
$weather_row = mysqli_fetch_array($result);
$sunrise = $weather_row['sunrise']* 1000 ;
$sunset = $weather_row['sunset']* 1000 ;

//date_sun_info ( int $time , float $latitude , float $longitude )
//http://php.net/manual/en/function.date-sun-info.php

//only show on chart page footer  ?>
<!--[if lte IE 8]><script src="js/plugins/flot/excanvas.min.js"></script><![endif]-->   
<!--[if lte IE 8]><script language="javascript" type="text/javascript" src="/js/flot/excanvas.min.js"></script><![endif]-->
    <script type="text/javascript" src="js/plugins/flot/jquery.flot.min.js"></script>
    <script type="text/javascript" src="js/plugins/flot/jshashtable-2.1.js"></script>    
    <script type="text/javascript" src="js/plugins/flot/jquery.numberformatter-1.2.3.min.js"></script>
    <script type="text/javascript" src="js/plugins/flot/jquery.flot.js"></script>
    <script type="text/javascript" src="js/plugins/flot/jquery.flot.time.js"></script>
    <script type="text/javascript" src="js/plugins/flot/jquery.flot.symbol.js"></script>
    <script type="text/javascript" src="js/plugins/flot/jquery.flot.axislabels.js"></script>
    <script type="text/javascript" src="js/plugins/flot/jquery.flot.resize.js"></script>
    <script type="text/javascript" src="js/plugins/flot/jquery.flot.tooltip.min.js"></script>
	<script type="text/javascript" src="js/plugins/flot/curvedLines.js"></script>

<script type="text/javascript">
var ground_floor = <?php echo json_encode($ground_floor); ?>;
var first_floor = <?php echo json_encode($first_floor); ?>;
var weather_c = <?php echo json_encode($weather_c); ?>;

var dataset = [
	{label: "Ground Floor", data: ground_floor, color: "#DE000F"}, 
	{label: "First Floor", data: first_floor, color: "#7D0096"},
	{label: "Out Side", data: weather_c, color: "#009604"}
];

//background-color for boiler on time 
var markings = [
<?php
$query="select start_datetime, stop_datetime from zone_log_view where (start_datetime > DATE_SUB( NOW(), INTERVAL 24 HOUR)) AND type='Heating' AND status= '1';";
$results = $conn->query($query);
$count=mysqli_num_rows($results); 
while ($row = mysqli_fetch_assoc($results)) {
	if((--$count)==-1) break;
	$boiler_start = strtotime($row['start_datetime']) * 1000;
if (is_null($row['stop_datetime'])) {
	$boiler_stop = strtotime("now") * 1000;
} else {$boiler_stop = strtotime($row['stop_datetime']) * 1000;}
	echo "{ xaxis: { from: ".$boiler_start.", to: ".$boiler_stop." }, color: \"#ffe9dc\" },  \n" ;
} ?> ];
 
var options_one = {
    xaxis: { mode: "time", timeformat: "%H:%M"},
    series: { lines: { show: true, lineWidth: 1, fill: false}, curvedLines: { apply: true,  active: true,  monotonicFit: true } },
    grid: { hoverable: true, borderWidth: 1,  backgroundColor: { colors: ["#ffffff", "#fdf9f9"] }, borderColor: "#ff8839", markings: markings,},
    legend: { noColumns: 3, labelBoxBorderColor: "#ffff", position: "nw" }
};

$(document).ready(function () {
	$.plot($("#placeholder"), dataset, options_one);
    $("#placeholder").UseTooltip();
});
</script>	

<script type="text/javascript">
var hot_water = <?php echo json_encode($hot_water); ?>;
//var immersion_room = <?php echo json_encode($immersion_room); ?>;
//var dataset_c = [{label: "Hot Water ", data: hot_water, color: "#0077FF"}, {label: "Immersion Room ", data: immersion_room, color: "#DE000F"} ];
var dataset_c = [{label: "Hot Water ", data: hot_water, color: "#0077FF"}];

//background-color for boiler on time 
var markings_chwater = [
<?php
$query="select start_datetime, stop_datetime from zone_log_view where (start_datetime > DATE_SUB( NOW(), INTERVAL 24 HOUR)) AND type = 'Water' AND status= '1';";
$results = $conn->query($query);
$count=mysqli_num_rows($results); 
while ($row = mysqli_fetch_assoc($results)) {
	if((--$count)==0) break;
$boiler_start = strtotime($row['start_datetime']) * 1000;
$boiler_stop = strtotime($row['stop_datetime'])* 1000;
echo "{ xaxis: { from: ".$boiler_stop.", to: ".$boiler_start." }, color: \"#ffe9dc\" },  \n" ;
} ?> ];

var options_two = {
    xaxis: { mode: "time", timeformat: "%H:%M"},
    series: { lines: { show: true, lineWidth: 1, fill: false}, curvedLines: { apply: true,  active: true,  monotonicFit: true } },
    grid: { hoverable: true, borderWidth: 1,  backgroundColor: { colors: ["#ffffff", "#fdf7f4"] }, borderColor: "#ff8839", markings: markings_chwater,},
    legend: { noColumns: 3, labelBoxBorderColor: "#ffff", position: "nw" }
};
$(document).ready(function () {
	$.plot($("#hot_water"), dataset_c, options_two);
	$("#hot_water").UseTooltip();
});

</script>	

<script type="text/javascript">
var system_c = <?php echo json_encode($system_c); ?>;
//var pi_box = <?php echo json_encode($pi_box); ?>;
//var dataset_hw = [{label: "CPU  ", data: system_c, color: "#DE000F"},{label: "Pi Box  ", data: pi_box, color: "#7D0096"} ];
var dataset_hw = [{label: "CPU  ", data: system_c, color: "#DE000F"}];

//background-color for All boiler on time 
var markings_boiler = [
<?php
$query="select start_datetime, stop_datetime from zone_log_view where (start_datetime > DATE_SUB( NOW(), INTERVAL 24 HOUR));";
$results = $conn->query($query);
$count=mysqli_num_rows($results); 
while ($row = mysqli_fetch_assoc($results)) {
	if((--$count)==0) break;
$boiler_start = strtotime($row['start_datetime']) * 1000;
$boiler_stop = strtotime($row['stop_datetime'])* 1000;
echo "{ xaxis: { from: ".$boiler_stop.", to: ".$boiler_start." }, color: \"#ffe9dc\" },  \n" ;
} ?> ];

var options_three = {
    xaxis: { mode: "time", timeformat: "%H:%M"},
    series: { lines: { show: true, lineWidth: 1, fill: false}, curvedLines: { apply: true,  active: true,  monotonicFit: true } },
    grid: { hoverable: true, borderWidth: 1,  backgroundColor: { colors: ["#ffffff", "#fdf7f4"] }, borderColor: "#ff8839", markings: markings_boiler, },
    legend: { noColumns: 3, labelBoxBorderColor: "#ffff", position: "nw" }
};
     
$(document).ready(function () {$.plot($("#system_c"), dataset_hw, options_three);$("#system_c").UseTooltip();});
var previousPoint = null, previousLabel = null;
$.fn.UseTooltip = function () {
    $(this).bind("plothover", function (event, pos, item) {
        if (item) {
            if ((previousLabel != item.series.label) ||
                 (previousPoint != item.dataIndex)) {
                previousPoint = item.dataIndex;
                previousLabel = item.series.label;
                $("#tooltip").remove();
                var x = item.datapoint[0];
                var y = item.datapoint[1];
                var color = item.series.color;                        
                showTooltip(item.pageX,
                        item.pageY,
                        color,
                        "<strong>" + item.series.label + "</strong> At: " + new Date(x).getHours() + ":"  + (new Date(x).getMinutes()<10?'0':'') + new Date(x).getMinutes() +"<br> <strong>Temp : " + $.formatNumber(y, { format: "#,###", locale: "us" }) + "&deg;</strong> ");               
            }
        } else {
            $("#tooltip").remove();
            previousPoint = null;
        }
    });
};
 
function showTooltip(x, y, color, contents) {
    $('<div id="tooltip">' + contents + '</div>').css({
        position: 'absolute',
        display: 'none',
        top: y - 10,
        left: x + 10,
        border: '1px solid ' + color,
        padding: '3px',
        'font-size': '9px',
        'border-radius': '5px',
        'background-color': '#fff',
        'font-family': 'Verdana, Arial, Helvetica, Tahoma, sans-serif',
        opacity: 0.7
    }).appendTo("body").fadeIn(200);
}
</script>

<script type="text/javascript">
function getMonthName(numericMonth) {
    var monthArray = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    var alphaMonth = monthArray[numericMonth];
    return alphaMonth;
}

function convertToDate(timestamp) {
    var newDate = new Date(timestamp);
    var dateString = newDate.getMonth();
    var monthName = getMonthName(dateString);
    return monthName;
}

var total_minuts = <?php echo json_encode($total_minuts); ?>;
var on_minuts = <?php echo json_encode($on_minuts); ?>;
var save_minuts = <?php echo json_encode($save_minuts); ?>;

var dataset_mu = [
{label: "Total Time  ", data: total_minuts, color: "#DE000F"},
{label: "Consumed Time  ", data: on_minuts, color: "#7D0096"}, 
{label: "Saved Time  ", data: save_minuts, color: "#009604"} ];

/*
Timeformat specifiers
%a: weekday name (customizable)
%b: month name (customizable)
%d: day of month, zero-padded (01-31)
%e: day of month, space-padded ( 1-31)
%H: hours, 24-hour time, zero-padded (00-23)
%I: hours, 12-hour time, zero-padded (01-12)
%m: month, zero-padded (01-12)
%M: minutes, zero-padded (00-59)
%q: quarter (1-4)
%S: seconds, zero-padded (00-59)
%y: year (two digits)
%Y: year (four digits)
%p: am/pm
%P: AM/PM (uppercase version of %p)
%w: weekday as number (0-6, 0 being Sunday)
*/
var options_four = {
    xaxis: { mode: "time", timeformat: "%b %Y"},
	//yaxis: {axisLabel: 'Hours', axisLabelPadding: 15 },
    series: { lines: { show: true, lineWidth: 1, fill: false}, curvedLines: { apply: true,  active: true,  monotonicFit: true } },
    grid: { hoverable: true, borderWidth: 1,  backgroundColor: { colors: ["#ffffff", "#fdf7f4"] }, borderColor: "#ff8839" },
    legend: { noColumns: 3, labelBoxBorderColor: "#ffff", position: "nw" }
};

$(document).ready(function () {$.plot($("#month_usage"), dataset_mu, options_four);$("#month_usage").UseTooltipu();});

var previousPoint = null, previousLabel = null;
$.fn.UseTooltipu = function () {
    $(this).bind("plothover", function (event, pos, item) {
        if (item) {
            if ((previousLabel != item.series.label) || (previousPoint != item.dataIndex)) {
                previousPoint = item.dataIndex;
                previousLabel = item.series.label;
                $("#tooltip").remove();
                var z = convertToDate(item.datapoint[0]);
				var x = item.datapoint[0];
                var y = item.datapoint[1];
                var color = item.series.color;                        
                showTooltipu(item.pageX, item.pageY, color,
                "<strong>" + item.series.label + " in " + z +" <strong><br>Hours : " + $.formatNumber(y, { format: "#,###", locale: "us" }) + "</strong> ");                
            }
        } else {
            $("#tooltip").remove();
            previousPoint = null;
        }
    });
};
 
function showTooltipu(x, y, color, contents) {
    $('<div id="tooltip">' + contents + '</div>').css({
        position: 'absolute',
        display: 'none',
        top: y - 10,
        left: x + 10,
        border: '1px solid ' + color,
        padding: '3px',
        'font-size': '9px',
        'border-radius': '5px',
        'background-color': '#fff',
        'font-family': 'Verdana, Arial, Helvetica, Tahoma, sans-serif',
        opacity: 0.7
    }).appendTo("body").fadeIn(200);
}

</script>
<script type="text/javascript">
var btotal_minuts = <?php echo json_encode($btotal_minuts); ?>;
var bon_minuts = <?php echo json_encode($bon_minuts); ?>;
var bsave_minuts = <?php echo json_encode($bsave_minuts); ?>;
</script>
