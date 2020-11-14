// request function used for every function below.
function request(url, method, data, callback) {
	var http = new XMLHttpRequest;
	if (!http)
		return false;
	var _data;
	if (data != null && typeof data == "object") {
		_data = [];
		for (var i in data)
			_data.push(i + "=" + data[i]);
		_data = _data.join("&");
	} else {
		_data = data;
	}
	method = method.toUpperCase();
	if (method == "POST") {
		http.open(method, url, true);
		http.setRequestHeader("Method", "POST "+url+" HTTP/1.1");
		http.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	} else {
		if (_data)
			url += _data;
		_data = "";
		http.open(method, url, true);
	}
	if (callback)
		http.onreadystatechange = function() {
			if (http.readyState == 4) {
				http.onreadystatechange = function(){};
				callback(http, data);
			}
		};
	http.send(_data);
	return http;
}

//delete Zone 
function delete_zone(wid){
	var quest = "?w=zone&o=delete&wid=" + wid + "&frost_temp=0";
	request('db.php', 'GET', quest, function(){ window.location="settings.php?zone_deleted"; } );
}

//activate and deactivate holidays schedule 
function active_holidays(wid){
	var quest = "?w=holidays&o=active&wid=" + wid + "&frost_temp=0";
	request('db.php', 'GET', quest, function(){ $('#holidayslist').load('holidayslist.php'); } );
}

//delete holidays
function delete_holidays(wid){
        var quest = "?w=holidays&o=delete&wid=" + wid + "&frost_temp=0";
        request('db.php', 'GET', quest, function(){ $('#holidayslist').load('holidayslist.php'); } );
}

//activate and deactivate schedule 
function active_schedule(wid){
	var quest = "?w=schedule&o=active&wid=" + wid + "&frost_temp=0";
	request('db.php', 'GET', quest, function(){ $('#schedulelist').load('schedulelist.php'); } );
}

//activate and deactivate schedule 
function schedule_zone(wid){
	var quest = "?w=schedule_zone&o=active&wid=" + wid + "&frost_temp=0";
	request('db.php', 'GET', quest, function(){ $('#homelist').load('homelist.php'); } );
}

//delete schedule 
function delete_schedule(wid){
	var quest = "?w=schedule&o=delete&wid=" + wid + "&frost_temp=0";
	request('db.php', 'GET', quest, function(){ $('#schedulelist').load('schedulelist.php'); } );
}

//activate and deactivate override 
function active_override(wid){
	var quest = "?w=override&o=active&wid=" + wid + "&frost_temp=0";
	request('db.php', 'GET', quest, function(){ $('#overridelist').load('overridelist.php'); } );
}

//activate and deactivate boost 
function active_boost(wid){
	var quest = "?w=boost&o=active&wid=" + wid + "&frost_temp=0";
	request('db.php', 'GET', quest, function(){ $('#boostlist').load('boostlist.php'); } );
}

//activate and deactivate away 
function active_away(){
	var quest = "?w=away&o=active" + "&frost_temp=0" + "&wid=0";
	request('db.php', 'GET', quest, function(){ $('#homelist').load('homelist.php'); } );
}

//update add_on
function update_add_on(wid){
    var idata="w=add_on&o=update";
    idata+="&sch_active="+document.getElementById("sch_active").value;;
    idata+="&wid=" + wid;
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            reload_page();
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("update_add_on: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//update frost temperate 
function update_frost(){
    var idata="w=frost&o=update";
    idata+="&frost_temp="+document.getElementsByName("frost_temp")[0].value;
    idata+="&wid=0";
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            reload_page();
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("update_frost: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//update units
function update_units(){
    var idata="w=units&o=update";
    idata+="&val="+$("#new_units").val();
    idata+="&wid=0";
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            reload_page();
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("update_units: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//update language
function update_lang(){
    var idata="w=lang&o=update";
    idata+="&lang_val="+$("#new_lang").val();
    idata+="&wid=0";
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            reload_page();
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("update_lang: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//update boiler settings
function boiler_settings(){
var idata="w=boiler_settings&o=update&status="+document.getElementById("checkbox2").checked;
    idata+="&name="+document.getElementById("name").value;
	idata+="&node_id="+document.getElementById("selected_node_id").value;
	idata+="&node_child_id="+document.getElementById("node_child_id").value;
	idata+="&hysteresis_time="+document.getElementById("hysteresis_time").value;
	idata+="&max_operation_time="+document.getElementById("max_operation_time").value;
	idata+="&overrun="+document.getElementById("overrun").value;
    idata+="&wid=0";
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            reload_page();
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("boiler_settings: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//Add Boost
function add_boost(){
var idata="w=boost&o=add&zone_id="+document.getElementById("zone_id").value;
	idata+="&boost_temperature="+document.getElementById("boost_temperature").value;
	idata+="&boost_time="+document.getElementById("boost_time").value;
	idata+="&boost_console_id="+document.getElementById("boost_console_id").value;
	idata+="&boost_button_child_id="+document.getElementById("boost_button_child_id").value;
    idata+="&wid=0";
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            reload_page();
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("add_boost: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//Delete Boost 
function delete_boost(wid){
var idata="w=boost&o=delete&wid="+wid;
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            reload_page();
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("delete_boost: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//update boost settings
function update_boost(){
var x = document.getElementsByTagName("input");
var i;
var idata="w=boost&o=update";
    for (i = 0; i < x.length; i++) {
        idata+="&"+x[i].id+"="+x[i].value;
    }
    idata+="&wid=0";
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            reload_page();
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("update_boost: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//Add Node
function add_node(){
var idata="w=node&o=add&node_type="+document.getElementById("node_type").value;
	idata+="&add_node_id="+document.getElementById("add_node_id").value;
	idata+="&nodes_max_child_id="+document.getElementById("nodes_max_child_id").value;
	idata+="&node_name="+document.getElementById("node_type").value+" Controller";
        idata+="&notice_interval=0";
    idata+="&wid=0";
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            reload_page();
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("add_node: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//Delete Node
function delete_node(wid){
var idata="w=node&o=delete&wid="+wid;
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            reload_page();
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("delete_node: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//Add Zone Type
function add_zone_type(){
var idata="w=zone_type&o=add&zone_type="+document.getElementById("zone_type").value;
    idata+="&zone_category="+document.getElementById("category").value;
    idata+="&wid=0";
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            reload_page();
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("add_zone_type: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//Delete Zone Type
function delete_zone_type(wid){
var idata="w=zone_type&o=delete&wid="+wid;
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            reload_page();
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("delete_zone_type: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

function reload_page()
{
    var loc = window.location;
    /*console.log(loc.protocol);
    console.log(loc.host);
    console.log(loc.pathname);
    console.log(loc.search);
    console.log(loc.protocol + '//' + loc.host + loc.pathname);*/
    window.location.href=loc.protocol + '//' + loc.host + loc.pathname;
}

//delete user account 
function del_user(wid){
	var quest = "?w=user&o=delete&wid=" + wid + "&frost_temp=0";
	request('db.php', 'GET', quest, function(){ window.location="settings.php?del_user"; });
}
//reboot pi
function reboot() {  
  	var quest = "?w=reboot" + "&o=0" + "&frost_temp=0" + "&wid=0";
	request('db.php', 'GET', quest, function(){ window.location="settings.php?reboot"; });
    //window.location="settings.php?status=reboot";  
}

//shutdown Pi
function shutdown() {  
  	var quest = "?w=shutdown" + "&o=0" + "&frost_temp=0" + "&wid=0";
	request('db.php', 'GET', quest, function(){ window.location="settings.php?shutdown"; });
    //window.location="settings.php?status=reboot";  
}

//start database backup <--- this function need some work. 
function db_backup() {  
  	var quest = "?w=db_backup" + "&o=0" + "&frost_temp=0" + "&wid=0";
	request('db.php', 'GET', quest, function(){ window.location="settings.php?db_backup"; });
    //window.location="settings.php?status=reboot";  
}

//update backup email adress 
function backup_email_update(){
var idata="w=backup_email_update&o=update&backup_email="+document.getElementById("backup_email").value;
    idata+="&wid=0";
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            reload_page();
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("setup_email: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//Restart MySensors Gateway
function resetgw(wid){
	var quest = "?w=resetgw&o=0&wid=" + wid + "&frost_temp=0";
	request('db.php', 'GET', quest, function(){ window.location="settings.php"; });
}

//triger search for PiHome network Gateway. 
function find_gw() {  
  	var quest = "?w=find_gw" + "&o=0" + "&frost_temp=0" + "&wid=0";
	request('db.php', 'GET', quest, function(){ window.location="settings.php"; });
    //window.location="settings.php?status=reboot";  
}

//update Gateway 
function setup_gateway(){
var selected_gw_type=document.getElementById("gw_type").value;
var idata="w=setup_gateway&o=update&status="+document.getElementById("checkbox1").checked;
    idata+="&gw_type="+document.getElementById("gw_type").value;
        if(selected_gw_type.includes("wifi")) {
            idata+="&gw_location="+document.getElementById("wifi_location").value;
            idata+="&gw_port="+document.getElementById("wifi_port_num").value;
        } else {
            idata+="&gw_location="+document.getElementById("serial_location").value;
            idata+="&gw_port="+document.getElementById("serial_port_speed").value;
        }
	idata+="&gw_timout="+document.getElementById("gw_timout").value;
    idata+="&wid=0";
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            reload_page();
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("setup_gateway: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//update network settings
function setup_network(){
var idata="w=setup_network&o=update&n_primary="+document.getElementById("n_primary").value;
        idata+="&n_ap_mode="+document.getElementById("n_ap_mode").value;
        idata+="&n_int_num="+document.getElementById("n_int_num").value;
        idata+="&n_int_type="+document.getElementById("n_int_type").value;
        idata+="&n_mac="+document.getElementById("n_mac").value;
        idata+="&n_hostname"+document.getElementById("n_hostname").value;
        idata+="&n_ip="+document.getElementById("n_ip").value;
        idata+="&n_gateway="+document.getElementById("n_gateway").value;
        idata+="&n_net_mask="+document.getElementById("n_net_mask").value;
        idata+="&n_dns1="+document.getElementById("n_dns1").value;
        idata+="&n_dns2="+document.getElementById("n_dns2").value;
        idata+="&wid=0";
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            reload_page();
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("setup_network: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//update email 
function setup_email(){
var idata="w=setup_email&o=update&status="+document.getElementById("checkbox3").checked;
    idata+="&e_smtp="+document.getElementById("e_smtp").value;
	idata+="&e_username="+document.getElementById("e_username").value;
	idata+="&e_password="+document.getElementById("e_password").value;
	idata+="&e_from_address="+document.getElementById("e_from_address").value;
	idata+="&e_to_address="+document.getElementById("e_to_address").value;
    idata+="&wid=0";
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            reload_page();
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("setup_email: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//update graph
function setup_graph(){
var x = document.getElementsByTagName("input");
var i;
var idata="w=setup_graph&o=update";
    for (i = 0; i < x.length; i++) {
        idata+="&"+x[i].id+"="+x[i].checked;
    }
    idata+="&wid=0";
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            reload_page();
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("setup_graph: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//update notice interval
function node_alerts(){
var x = document.getElementsByTagName("input");
var i;
var idata="w=node_alerts&o=update";
    for (i = 0; i < x.length; i++) {
        idata+="&"+x[i].id+"="+x[i].value;
    }
    idata+="&wid=0";
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            reload_page();
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("node_alerts: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//update Time Zone
function update_timezone(){
    var idata="w=time_zone&o=update";
    idata+="&time_zone_val="+$("#new_time_zone").val();
    idata+="&wid=0";
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            reload_page();
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("update_lang: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//update PiConnect 
function setup_piconnect(){
var idata="w=setup_piconnect&o=update&status="+document.getElementById("checkbox0").checked;
    idata+="&api_key="+document.getElementById("api_key").value;
    idata+="&wid=0";
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            reload_page();
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("setup_piconnect: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}


function mqtt_delete(wid){
    var result = confirm("Confirm delete MQTT server?");
    if (!result) return;

    var idata="w=mqtt&o=delete";
    idata+="&wid="+wid;
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            $('#ajaxModal').modal('hide')
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("delete_mqtt: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//Add Zone HTTP Message
function add_zone_http_msg(){
var idata="w=http_msg&o=add";
        idata+="&http_id="+document.getElementById("zone_http_id").value;
        idata+="&add_msg_type="+document.getElementById("zone_add_msg_type").value;
        idata+="&http_command="+document.getElementById("zone_http_command").value;
        idata+="&http_parameter="+document.getElementById("zone_http_parameter").value;
    idata+="&wid=1";
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            reload_page();
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("add_zone_http_msg: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//Add Node HTTP Message
function add_node_http_msg(){
var idata="w=http_msg&o=add";
        idata+="&http_id="+document.getElementById("node_http_id").value;
        idata+="&add_msg_type="+document.getElementById("node_add_msg_type").value;
        idata+="&http_command="+document.getElementById("node_http_command").value;
        idata+="&http_parameter="+document.getElementById("node_http_parameter").value;
    idata+="&wid=0";
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            reload_page();
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("add_node_http_msg: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//Delete HTTP Message
function delete_http_msg(wid){
var idata="w=http_msg&o=delete&wid="+wid;
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            reload_page();
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("delete_http_msg: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//update user email address
function update_email(){
    var idata="w=user_email&o=update";
    idata+="&email_add="+document.getElementById("email_add").value;
    idata+="&wid=0";
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            reload_page();
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("update_email: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

