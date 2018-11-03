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


//update frost temperate 
function update_frost(){
	var frost_temp   = document.getElementsByName("frost_temp")[0].value;
	var quest = "?w=frost&o=update&frost_temp=" + frost_temp + "&wid=0";
	request('db.php', 'GET', quest, function(){ window.location="settings.php?frost="+ frost_temp; } );
}

//delete user account 
function del_user(wid){
	var quest = "?w=user&o=delete&wid=" + wid + "&frost_temp=0";
	request('db.php', 'GET', quest, function(){ window.location="settings.php?del_user"; });
}

function reboot() {  
  	var quest = "?w=reboot" + "&o=0" + "&frost_temp=0" + "&wid=0";
	request('db.php', 'GET', quest, function(){ window.location="settings.php?reboot"; });
    //window.location="settings.php?status=reboot";  
}

function shutdown() {  
  	var quest = "?w=shutdown" + "&o=0" + "&frost_temp=0" + "&wid=0";
	request('db.php', 'GET', quest, function(){ window.location="settings.php?shutdown"; });
    //window.location="settings.php?status=reboot";  
}

function find_gw() {  
  	var quest = "?w=find_gw" + "&o=0" + "&frost_temp=0" + "&wid=0";
	request('db.php', 'GET', quest, function(){ window.location="settings.php"; });
    //window.location="settings.php?status=reboot";  
}

function db_backup() {  
  	var quest = "?w=db_backup" + "&o=0" + "&frost_temp=0" + "&wid=0";
	request('db.php', 'GET', quest, function(){ window.location="settings.php?db_backup"; });
    //window.location="settings.php?status=reboot";  
}

//Restart MySensors Gateway
function resetgw(wid){
	var quest = "?w=resetgw&o=0&wid=" + wid + "&frost_temp=0";
	request('db.php', 'GET', quest, function(){ window.location="settings.php"; });
}
