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
$weather_c = array();
$system_c = array();

$query="select * from messages_in where datetime > DATE_SUB( NOW(), INTERVAL 24 HOUR)";
$result = $conn->query($query);
//create array of pairs of x and y values
while ($row = mysqli_fetch_assoc($result)) {
        $datetime = $row['datetime'];
        $payload = $row['payload'];
        if ($row['node_id'] == 0) {
                $system_c[] = array(strtotime($datetime) * 1000, $payload);
        } elseif ($row['node_id'] == 1) {
                $weather_c[] = array(strtotime($datetime) * 1000, $payload);
        }
}

// weather table to get sunrise and sun set time
$query="select * from weather";
$result = $conn->query($query);
$weather_row = mysqli_fetch_array($result);
$sunrise = $weather_row['sunrise']* 1000 ;
$sunset = $weather_row['sunset']* 1000 ;

//date_sun_info ( int $time , float $latitude , float $longitude )
//http://php.net/manual/en/function.date-sun-info.php

// create datasets based on all available zones
$querya ="select id, name, type from zone_view where graph_it = 1 order BY index_id asc;";
$resulta = $conn->query($querya);
$counter = 0;
$count = mysqli_num_rows($resulta) + 1;
$zones = '';
$zonesw = '';
while ($row = mysqli_fetch_assoc($resulta)) {
        // grab the zone names to be displayed in the plot legend
        $zone_name=$row['name'];
	$zone_type=$row['type'];
	$zone_id=$row['id'];
	$query="select * from zone_graphs where zone_id = {$zone_id};";
        $result = $conn->query($query);
        // create array of pairs of x and y values for every zone
        $zone_temp = array();
        $water_temp = array();
        while ($rowb = mysqli_fetch_assoc($result)) {
                if(strpos($zone_type, 'Heating') !== false) {
                        $zone_temp[] = array(strtotime($rowb['datetime']) * 1000, $rowb['payload']);
                } elseif((strpos($zone_type, 'Water') !== false) || (strpos($zone_type, 'Immersion') !== false)) {
                        $water_temp[] = array(strtotime($rowb['datetime']) * 1000, $rowb['payload']);
                }

        }
        // create dataset entry using distinct color based on zone index(to have the same color everytime chart is opened)
        if(strpos($zone_type, 'Heating') !== false) {
                $zones = $zones. "{label: \"".$zone_name."\", data: ".json_encode($zone_temp).", color: rainbow(".$count.",".++$counter.") }, \n";
        } elseif((strpos($zone_type, 'Water') !== false) || (strpos($zone_type, 'Immersion') !== false)) {
                $zonesw = $zonesw. "{label: \"".$zone_name."\", data: ".json_encode($water_temp).", color: rainbow(".$count.",".++$counter.") }, \n";
        }
}
// add outside weather temperature
$zonesw = $zonesw."{label: \"".$lang['graph_outsie']."\", data: ".json_encode($weather_c).", color: rainbow(".$count.",".++$counter.") }, \n";

//background-color for boiler on time
$query="select start_datetime, stop_datetime, type from zone_log_view where status= '1' AND start_datetime > current_timestamp() - interval 24 hour;";
$results = $conn->query($query);
$count=mysqli_num_rows($results);
$warn1 = '';
$warn2 = '';
while ($row = mysqli_fetch_assoc($results)) {
        if((--$count)==-1) break;
        $boiler_start = strtotime($row['start_datetime']) * 1000;
        if (is_null($row['stop_datetime'])) {
                $boiler_stop = strtotime("now") * 1000;
        } else {
                $boiler_stop = strtotime($row['stop_datetime']) * 1000;
        }
        if(strpos($zone_type, 'Heating') !== false) {
                $warn1 = $warn1."{ xaxis: { from: ".$boiler_start.", to: ".$boiler_stop." }, color: \"#ffe9dc\" },  \n" ;
        } elseif((strpos($zone_type, 'Water') !== false) || (strpos($zone_type, 'Immersion') !== false)) {
                $warn2 = $warn2."{ xaxis: { from: ".$boiler_start.", to: ".$boiler_stop." }, color: \"#ffe9dc\" },  \n" ;
        }
}

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
// Create datasets for zones, and zone markings
var dataset = [ <?php echo $zones ?>];
var wdataset = [ <?php echo $zonesw ?>];
var markings = [ <?php echo $warn1 ?> ];
var wmarkings = [ <?php echo $warn2 ?> ];
var markings_boiler = [ <?php echo $warn1.$warn2 ?> ];
// Create System Graphs
var system_c = <?php echo json_encode($system_c); ?>;
//var pi_box = <?php echo json_encode($pi_box); ?>;
//var dataset_hw = [{label: "CPU  ", data: system_c, color: "#DE000F"},{label: "Pi Box  ", data: pi_box, color: "#7D0096"} ];
//var dataset_hw = [{label: "CPU  ", data: system_c, color: "#DE000F"}, {label: "FLOW ", data: bflow_c, color: "#0077FF"}];
var dataset_hw = [
        {label: "<?php echo $lang['cpu']; ?>  ", data: system_c, color: "#0077FF"}
];

// distinct color implementation for plot lines
function rainbow(numOfSteps, step) {
    var r, g, b;
    var h = step / numOfSteps;
    var i = ~~(h * 6);
    var f = h * 6 - i;
    var q = 1 - f;
    switch(i % 6){
        case 0: r = 1; g = f; b = 0; break;
        case 5: r = q; g = 1; b = 0; break;
        case 2: r = 0; g = 1; b = f; break;
        case 3: r = 0; g = q; b = 1; break;
        case 4: r = f; g = 0; b = 1; break;
        case 1: r = 1; g = 0; b = q; break;
    }
    var c = "#" + ("00" + (~ ~(r * 255)).toString(16)).slice(-2) + ("00" + (~ ~(g * 255)).toString(16)).slice(-2) + ("00" + (~ ~(b * 255)).toString(16)).slice(-2);
    return (c);
}

// Create Zone Graphs
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

// Create Hot Water Graphs
var options_two = {
    xaxis: { mode: "time", timeformat: "%H:%M"},
    series: { lines: { show: true, lineWidth: 1, fill: false}, curvedLines: { apply: true,  active: true,  monotonicFit: true } },
    grid: { hoverable: true, borderWidth: 1,  backgroundColor: { colors: ["#ffffff", "#fdf9f9"] }, borderColor: "#ff8839", markings: wmarkings,},
    legend: { noColumns: 3, labelBoxBorderColor: "#ffff", position: "nw" }
};
$(document).ready(function () {
	$.plot($("#graph2"), wdataset, options_two);
	$("#graph2").UseTooltip();
});

var options_three = {
    xaxis: { mode: "time", timeformat: "%H:%M"},
    series: { lines: { show: true, lineWidth: 1, fill: false}, curvedLines: { apply: true,  active: true,  monotonicFit: true } },
    grid: { hoverable: true, borderWidth: 1,  backgroundColor: { colors: ["#ffffff", "#fdf7f4"] }, borderColor: "#ff8839", markings: markings_boiler, },
    legend: { noColumns: 3, labelBoxBorderColor: "#ffff", position: "nw" }
};

$(document).ready(function () {$.plot($("#graph3"), dataset_hw, options_three);$("#graph3").UseTooltip();});
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
                        "<strong>" + item.series.label + "</strong> At: " + (new Date(x).getHours()<10?'0':'') + new Date(x).getHours() + ":"  + (new Date(x).getMinutes()<10?'0':'') + new Date(x).getMinutes() +"<br> <strong><?php echo $lang['temp']; ?>  : " + $.formatNumber(y, { format: "#,###", locale: "us" }) + "&deg;</strong> ");
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

// Create Monthly Usage Graphs
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
                "<strong>" + item.series.label + " in " + z +" <strong><br><?php echo $lang['hours']; ?> : " + $.formatNumber(y, { format: "#,###", locale: "us" }) + "</strong> ");
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

// Create Battery Usage Graphs
var options_bat = {
	xaxis: { mode: "time", timeformat: "%b %Y"},
    series: { lines: { show: true, lineWidth: 1, fill: false}, curvedLines: { apply: true,  active: true,  monotonicFit: true } },
    grid: { hoverable: true, borderWidth: 1,  backgroundColor: { colors: ["#ffffff", "#fdf9f9"] }, borderColor: "#ff8839",},
    legend: { noColumns: 3, labelBoxBorderColor: "#ffff", position: "nw" }
};

$(document).ready(function () {$.plot($("#battery_level"), bat_level_dataset, options_bat);$("#battery_level").UseTooltipl();});
var previousPoint = null, previousLabel = null;
var weekday = new Array(7);
weekday[0] = "Sunday";
weekday[1] = "Monday";
weekday[2] = "Tuesday";
weekday[3] = "Wednesday";
weekday[4] = "Thursday";
weekday[5] = "Friday";
weekday[6] = "Saturday";

$.fn.UseTooltipl = function () {
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
                        "<strong>" + item.series.label + "</strong> At: " + weekday[new Date(x).getDay()] + " " + (new Date(x).getHours()<10?'0':'') + new Date(x).getHours() + ":"  + (new Date(x).getMinutes()<10?'0':'') + new Date(x).getMinutes() +"<br> <strong><?php echo $lang['battery_level']; ?>  : " + $.formatNumber(y, { format: "#,###", locale: "us" }) + "%</strong> ");
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
