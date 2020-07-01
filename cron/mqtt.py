#!/usr/bin/python
import sys
import time, os, fnmatch, MySQLdb as mdb, logging
import paho.mqtt.client as mqtt
import json
from decimal import Decimal
import configparser
class bc:
	hed = '\033[0;36;40m'
	dtm = '\033[0;36;40m'
	ENDC = '\033[0m'
	SUB = '\033[3;30;45m'
	WARN = '\033[0;31;40m'
	grn = '\033[0;32;40m'
	wht = '\033[0;37;40m'
print(bc.hed + " ")
print("  _____    _   _    _                            ")
print(" |  __ \  (_) | |  | |                           ")
print(" | |__) |  _  | |__| |   ___    _ __ ___     ___ ")
print(" |  ___/  | | |  __  |  / _ \  | |_  \_ \   / _ \ ")
print(" | |      | | | |  | | | (_) | | | | | | | |  __/")
print(" |_|      |_| |_|  |_|  \___/  |_| |_| |_|  \___|")
print(" ")
print("    "+bc.SUB + "S M A R T   H E A T I N G   C O N T R O L "+ bc.ENDC)
print(bc.WARN +" ")
print("********************************************************")
print("*   PiHome MQTT Temperature Sensors Data to MySQL DB   *")
print("* Use this script if you have MQTT Temperature sensors *")
print("* Use this script if you have MQTT Temperature sensors *")
print("*                               Build Date: 09/02/2019 *")
print("*                                 Have Fun - PiHome.eu *")
print("********************************************************")
print(" " + bc.ENDC)

logging.basicConfig(filename='/var/www/cron/logs/MQTT_error.log', level=logging.DEBUG, format='%(asctime)s %(levelname)s %(name)s %(message)s')
logger=logging.getLogger(__name__)

# Initialise the database access varables
config = configparser.ConfigParser()
config.read('/var/www/st_inc/db_config.ini')
dbhost = config.get('db', 'hostname')
dbuser = config.get('db', 'dbusername')
dbpass = config.get('db', 'dbpassword')
dbname = config.get('db', 'dbname')

print(bc.dtm + time.ctime() + bc.ENDC + ' - MQTT Temperature Sensors Script Started')
print( "-" * 68)

'''
CREATE TABLE `pihome`.`mqtt` ( 
    `id` INT NOT NULL AUTO_INCREMENT , 
    `name` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '' , 
    `ip` VARCHAR(39) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '127.0.0.1' , 
    `port` INT NOT NULL DEFAULT '1883' , 
    `username` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '' , 
    `password` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '' , 
    `enabled` TINYINT NOT NULL DEFAULT '1' , 
    `type` INT NOT NULL DEFAULT '0' ,
    PRIMARY KEY (`id`)) ENGINE = InnoDB CHARSET=utf8mb4 COLLATE utf8mb4_unicode_520_ci;
INSERT INTO `mqtt` (`id`, `name`, `ip`, `port`, `username`, `password`, `enabled`, `type`) VALUES (NULL, 'Demo', '127.0.0.1', '1883', 'mosquitto', 'mosquitto', '0', '0');

sudo pip install mysqlclient
'''

clients=[]


#Function for Storing DS18B20 Temperature Readings into MySQL
def insertDB(IDs, temperature):
	try:
		con = mdb.connect(dbhost, dbuser, dbpass, dbname)
		cur = con.cursor()
		for i in range(0,len(temperature)):
			#Check if Sensors Already Exit in Nodes Table, if no then add Sensors into Nodes Table otherwise just update Temperature Readings. 
			#print "ID %s" % IDs[i]
			cur.execute('SELECT COUNT(*) FROM `nodes` where node_id = (%s)', [IDs[i]])
			row = cur.fetchone()
			row = int(row[0])
			if (row == 0):
				print(bc.dtm + time.ctime() + bc.ENDC) + ' - New DS18B20 Sensors Discovered' + print(bc.grn, IDs[i], bc.ENDC) 
				cur.execute('INSERT INTO nodes (node_id, max_child_id, name, last_seen, ms_version) VALUES(%s,%s,%s,%s,%s)', (IDs[i], '-1', 'Temperature Sensor', time.strftime("%Y-%m-%d %H:%M:%S"), '0'))
				con.commit()
			#If DS18B20 Sensor record exist: Update Nodes Table with Last seen status. 
			if (row == 1):
				cur.execute('UPDATE `nodes` SET `last_seen`=now() WHERE node_id = %s', [IDs[i]])
				con.commit()
			cur.execute('INSERT INTO messages_in(node_id, child_id, sub_type, payload, datetime) VALUES(%s, %s, %s, %s, %s)', (IDs[i], '-1', '0', round(temperature[i],2), time.strftime("%Y-%m-%d %H:%M:%S")))
			con.commit()
			#print bc.dtm + time.ctime() + bc.ENDC + ' - Sensors ID' + bc.grn, IDs[i], bc.ENDC + 'Temperature' + bc.grn, temperature[i], bc.ENDC
			print ('Sensor ID' + bc.grn, IDs[i], bc.ENDC + 'Temperature' + bc.grn, temperature[i], bc.ENDC)
		con.close()
	except mdb.Error as e:
		logger.error(e)
		print(bc.dtm + time.ctime() + bc.ENDC) + ' - DB Connection Closed: %s' % e


# The callback for when the client receives a CONNACK response from the server.
def on_connect_def(client, userdata, flags, rc):
	print("Connected with result code "+str(rc))
	
	# Subscribing in on_connect() means that if we lose the connection and
	# reconnect then subscriptions will be renewed.
	# "#" is subscribe to all, just watch traffic.
	client.subscribe("#")
	
# The callback for when a PUBLISH message is received from the server.
def on_message_def(client, userdata, msg):
	# We are subscribed to all, just print it for viewing
	print("Topic:    "+msg.topic)
	print("Payload:  "+str(msg.payload))


# The callback for when the client receives a CONNACK response from the server.
def on_connect_SonoffTasmota(client, userdata, flags, rc):
	print("Connected with result code "+str(rc))
	# "+" is a wildcard for that level, so tele/sonoff1/SENSOR and tele/sonoff2/SENSOR ... will all be received
	client.subscribe("tele/+/SENSOR")

# The callback for when a PUBLISH message is received from the server.
def on_message_SonoffTasmota(client, userdata, msg):
	temperature = []
	IDs = []
	jsonObject=json.loads(msg.payload)
	for key in jsonObject:
		value = jsonObject[key]
		if(key[0:8]=="DS18B20-"):
			#print("The key and value are ({}) = ({})".format(key, value))
			temperature.append(jsonObject[key]['Temperature'])
			IDs.append("28-"+jsonObject[key]['Id'])
	if (len(temperature)>0):
		insertDB(IDs, temperature)

con = mdb.connect(dbhost, dbuser, dbpass, dbname);
cur = con.cursor(mdb.cursors.DictCursor)
cur.execute('SELECT * FROM `mqtt` WHERE `enabled`=1;')
if(cur.rowcount<=0):
	print("No enabled MQTT connections. We'll sleep for 30 seconds, quit, and let the system restart us.")
	time.sleep(30)
	sys.exit()

MQTTCons = cur.fetchall()


for Idx,MQTTCon in enumerate(MQTTCons):
	print(MQTTCon)
	clients.insert(Idx,mqtt.Client())
	if(MQTTCon['type']==0):
		clients[Idx].on_connect = on_connect_def
		clients[Idx].on_message = on_message_def
	elif(MQTTCon['type']==1):
		clients[Idx].on_connect = on_connect_SonoffTasmota
		clients[Idx].on_message = on_message_SonoffTasmota
	else:
		print("Unknown MQTT type. Skipping.")
		continue
	clients[Idx].username_pw_set(MQTTCon['username'], MQTTCon['password'])
	clients[Idx].connect(MQTTCon['ip'], MQTTCon['port'], 60)
	clients[Idx].loop_start()       #start background thread to handle this connection

while True:
	#loop forever, letting the background threads and call backs do their work.
	time.sleep(0.1)
