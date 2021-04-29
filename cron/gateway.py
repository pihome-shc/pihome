#!/usr/bin/python3
class bc:
	hed = '\033[95m'
	dtm = '\033[0;36;40m'
	ENDC = '\033[0m'
	SUB = '\033[3;30;45m'
	WARN = '\033[0;31;40m'
	grn = '\033[0;32;40m'
	wht = '\033[0;37;40m'
	ylw = '\033[93m'
	fail = '\033[91m'
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
print("* MySensors Wifi/Ethernet/Serial Gateway Communication *")
print("* Script to communicate with MySensors Nodes, for more *")
print("* info please check MySensors API.                     *")
print("*      Build Date: 18/09/2017                          *")
print("*      Version 0.11 - Last Modified 26/03/2020         *")
print("*                                 Have Fun - PiHome.eu *")
print("********************************************************")
print(" " + bc.ENDC)

import MySQLdb as mdb, sys, serial, telnetlib, time, datetime, os
import configparser, logging
from datetime import datetime
import struct
import requests
import socket, re

# Get the local ip address
s = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
s.connect(('google.com', 0))
ip = s.getsockname()[0]
base_ip = re.search('^[\d]{1,3}.[\d]{1,3}.[\d]{1,3}.', ip)

# Debug print to screen configuration
dbgLevel = 3 	# 0-off, 1-info, 2-detailed, 3-all
dbgMsgOut = 1 	# 0-disabled, 1-enabled, show details of outgoing messages
dbgMsgIn = 1 	# 0-disabled, 1-enabled, show details of incoming messages

# Logging exceptions to log file
logfile = '/var/www/logs/main.log'
infomsg = 'More info in log file: '+logfile
logging.basicConfig( filename=logfile,
                     level=logging.DEBUG,
                     format= ('\n### %(asctime)s - %(levelname)s ###')
                   )
null_value = None

try:
	# Initialise the database access variables
	config = configparser.ConfigParser()
	config.read('/var/www/st_inc/db_config.ini')
	dbhost = config.get('db', 'hostname')
	dbuser = config.get('db', 'dbusername')
	dbpass = config.get('db', 'dbpassword')
	dbname = config.get('db', 'dbname')

	con = mdb.connect(dbhost, dbuser, dbpass, dbname)
	cur = con.cursor()
	cur.execute('SELECT * FROM gateway where status = 1 order by id asc limit 1')
	row = cur.fetchone();
	gateway_to_index = dict(
	(d[0], i)
	for i, d
	in enumerate(cur.description)
	)
	gatewaytype = row[gateway_to_index['type']]                             # serial/wifi
	gatewaylocation = row[gateway_to_index['location']]                     # ip address or serial port of your MySensors gateway
	gatewayport = row[gateway_to_index['port']]                             # UDP port or bound rate for MySensors gateway
	gatewaytimeout = int(row[gateway_to_index['timout']])                   # Connection timeout in Seconds

	if gatewaytype == 'serial':
		# ps. you can troubleshoot with "screen"
		# screen /dev/ttyAMA0 115200
		# gw = serial.Serial('/dev/ttyMySensorsGateway', 115200, timeout=0)
		gw = serial.Serial(gatewaylocation, gatewayport, timeout=5)
		print(bc.grn + "Gateway Type:  Serial", bc.ENDC)
		print(bc.grn + "Serial Port:   ",gatewaylocation, bc.ENDC)
		print(bc.grn + "Baud Rate:     ",gatewayport, bc.ENDC)
	else:
		#MySensors Wifi/Ethernet Gateway Manuall override to specific ip Otherwise ip from MySQL Databased is used.
		#mysgw = "192.168.99.3" 	#ip address of your MySensors gateway
		#mysport = "5003" 		#UDP port number for MySensors gateway
		#gw = telnetlib.Telnet(mysgw, mysport, timeout=3) # Connect mysensors gateway
		gw = telnetlib.Telnet(gatewaylocation, gatewayport, timeout=gatewaytimeout) # Connect mysensors gateway from MySQL Database
		print(bc.grn + "Gateway Type:  Wifi/Ethernet", bc.ENDC)
		print(bc.grn + "IP Address:    ",gatewaylocation, bc.ENDC)
		print(bc.grn + "UDP Port:      ",gatewayport, bc.ENDC)

	msgcount = 0 # Defining variable for counting messages processed

	while 1:
        ## Terminate gateway script if no route to network gateway
        if gatewaytype == "wifi":
            gateway_up  = True if os.system("ping -c 1 " + gatewaylocation) is 0 else False
            if not gateway_up:
                break
	## Outgoing messages
		con.commit()
		cur.execute('SELECT COUNT(*) FROM `messages_out` where sent = 0') # MySQL query statement
		count = cur.fetchone() # Grab all messages from database for Outgoing.
		count = count[0] # Parse first and the only one part of data table named "count" - there is number of records grabbed in SELECT above
		if count > 0: #If greater then 0 then we have something to send out.
			cur.execute('SELECT * FROM `messages_out` where sent = 0') #grab all messages that where not send yet (sent ==0)
			msg = cur.fetchone(); 	#Grab first record and build a message: if you change table fields order you need to change following lines as well.
			msg_to_index = dict(
			(d[0], i)
			for i, d
			in enumerate(cur.description)
			)
			out_id = int(msg[msg_to_index['id']])                   #Record ID - only DB info,
			out_node_id = msg[msg_to_index['node_id']]              #Node ID
			out_child_id = msg[msg_to_index['child_id']]            #Child ID of the node where sensor/relay is attached.
			out_sub_type = msg[msg_to_index['sub_type']]            #Command Type
			out_ack = msg[msg_to_index['ack']]                      #Ack req/resp
			out_type = msg[msg_to_index['type']]                    #Type
			out_payload = msg[msg_to_index['payload']]              #Payload to send out.
			sent = msg[msg_to_index['sent']]                        #Status of message either its sent or not. (1 for sent, 0 for not sent yet)
			cur.execute('SELECT type FROM `nodes` where node_id = (%s)', (out_node_id, ))
			nd = cur.fetchone();
			node_to_index = dict(
			(d[0], i)
			for i, d
			in enumerate(cur.description)
			)
			node_type = nd[node_to_index['type']]
			if dbgLevel >= 1 and dbgMsgOut == 1: # Debug print to screen
				print(bc.grn + "\nTotal Messages to Sent:      ",count, bc.ENDC) # Print how many Messages we have to send out.
				print("Date & Time:                 ",time.ctime())
				print("Message From Database:       ",out_id, out_node_id, out_child_id, out_sub_type, out_ack, out_type, out_payload, sent) #Print what will be sent including record id and sent status.
			msg = str(out_node_id) 	#Node ID
			msg += ';' 				#Separator
			msg += str(out_child_id) #Child ID of the Node.
			msg += ';' 				#Separator
			msg += str(out_sub_type)
			msg += ';' 				#Separator
			msg += str(out_ack)
			msg += ';' 				#Separator
			msg += str(out_type)
			msg += ';' 				#Separator
			msg += str(out_payload) #Payload from DB
			msg += ' \n'			#New line
			if dbgLevel >= 3 and dbgMsgOut == 1:
				print("Full Message to Send:        ",msg.replace("\n","\\n")) #Print Full Message
				print("Node ID:                     ",out_node_id)
				print("Child Sensor ID:             ",out_child_id)
				print("Command Type:                ",out_sub_type)
				print("Ack Req/Resp:                ",out_ack)
				print("Type:                        ",out_type)
				print("Pay Load:                    ",out_payload)
				print("Node Type:                   ",node_type)
			# node-id ; child-sensor-id ; command ; ack ; type ; payload \n
			if node_type.find("Tasmota") == -1: # process normal node
				if gatewaytype == 'serial':
					gw.write(msg.encode('utf-8')) # !!!! send it to serial (arduino attached to rPI by USB port)
				else:
					print('write')
					gw.write(msg.encode('utf-8'))
				cur.execute('UPDATE `messages_out` set sent=1 where id=%s', [out_id]) #update DB so this message will not be processed in next loop
				con.commit() #commit above
			else:
				# process the Sonoff device HTTP action
				url = 'http://' + base_ip.group(0) + str(out_child_id) + '/cm'
				cmd = out_payload.split(' ')[0].upper()
				param = out_payload.split(' ')[1]
				myobj = {'cmnd': str(out_payload)}
				x = requests.post(url, data = myobj) # send request to Sonoff device
				if x.status_code == 200:
					if x.json().get(cmd) == param: # clear send if response is okay
						cur.execute('UPDATE `messages_out` set sent=1 where id=%s', [out_id])
						con.commit() #commit above

	## Incoming messages
		if gatewaytype == 'serial':
			in_str = gw.readline() # Here is receiving part of the code for serial GW
			in_str = in_str.decode('utf-8')
		else:
			in_str = gw.read_until(b'\n', timeout=1) # Here is receiving part of the code for Wifi
			in_str = in_str.decode('utf-8')

		if dbgLevel >= 2: # Debug print to screen
			if time.strftime("%S",time.gmtime())== '00' and msgcount != 0:
				print(bc.hed + "\nMessages processed in last 60s:	",msgcount)
				if gatewaytype == 'serial':
					print("Bytes in outgoing buffer:	",gw.in_waiting)
				print("Date & Time:                 	",time.ctime(),bc.ENDC)
				msgcount = 0
			if not sys.getsizeof(in_str) <= 22:
				msgcount += 1

		if not sys.getsizeof(in_str) <= 25 and in_str[:1] != '0': #here is the line where sensor are processed
			if dbgLevel >= 1 and dbgMsgIn == 1: # Debug print to screen
				print(bc.ylw + "\nSize of the String Received: ", sys.getsizeof(in_str), bc.ENDC)
				print("Date & Time:                 ",time.ctime())
				in_str.replace("\n","\\n")
				print("Full String Received:         ",end = in_str)
			statement = in_str.split(";")
			if dbgLevel >= 3 and dbgMsgIn == 1:
				print("Full Statement Received:     ",statement)

			if len(statement) == 6 and statement[0].isdigit(): #check if received message is right format
				node_id = str(statement[0])
				child_sensor_id = int(statement[1])
				message_type = int(statement[2])
				ack = int(statement[3])
				sub_type = int(statement[4])
				payload = statement[5].rstrip() # remove \n from payload

				if dbgLevel >= 3 and dbgMsgIn == 1: # Debug print to screen
					print("Node ID:                     ",node_id)
					print("Child Sensor ID:             ",child_sensor_id)
					print("Message Type:                ",message_type)
					print("Acknowledge:                 ",ack)
					print("Sub Type:                    ",sub_type)
					print("Pay Load:                    ",payload)

				# ..::Step One::..
				# First time Temperature Sensors Node Comes online: Add Node to The Nodes Table.
				if (node_id != 0 and child_sensor_id == 255 and message_type == 0 and sub_type == 17):
				#if (child_sensor_id != 255 and message_type == 0):
					cur.execute('SELECT COUNT(*) FROM `nodes` where node_id = (%s)', (node_id, ))
					row = cur.fetchone()
					row = int(row[0])
					if (row == 0):
						if dbgLevel >= 2 and dbgMsgIn == 1:
							print("1: Adding Node ID:",node_id, "MySensors Version:", payload)
						timestamp = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
						cur.execute('INSERT INTO `nodes`(`sync`, `purge`, `type`, `node_id`, `max_child_id`, `name`, `last_seen`, `notice_interval`, `min_value`, `status`, `ms_version`, `sketch_version`, `repeater`) VALUES(%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)', (0, 0, 'MySensor', node_id, 0, null_value, timestamp, 0, 0, 'Active', payload, null_value, 0))
						con.commit()
					else:
						if dbgLevel >= 2 and dbgMsgIn == 1:
							print("1: Node ID:",node_id," Already Exist In Node Table, Updating MS Version")
						cur.execute('UPDATE nodes SET ms_version = %s where node_id = %s', (payload, node_id))
						con.commit()

				# ..::Step One B::..
				# First time Node Comes online with Repeater Feature Enabled: Add Node to The Nodes Table.
				if (node_id != 0 and child_sensor_id == 255 and message_type == 0 and sub_type == 18):
				#if (child_sensor_id != 255 and message_type == 0):
					cur.execute('SELECT COUNT(*) FROM `nodes` where node_id = (%s)', (node_id, ))
					row = cur.fetchone()
					row = int(row[0])
					if (row == 0):
						if dbgLevel >= 2 and dbgMsgIn == 1:
							print("1-B: Adding Node ID:",node_id, "MySensors Version:", payload)
						timestamp = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
						cur.execute('INSERT INTO nodes(`sync`, `purge`, `type`, `node_id`, `max_child_id`, `name`, `last_seen`, `notice_interval`, `min_value`, `status`, `ms_version`, `sketch_version`, `repeater`) VALUES(%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)', (0, 0, 'MySensor', node_id, 0, null_value, timestamp, 0, 0, 'Active', payload, null_value, 1))
						con.commit()
					else:
						if dbgLevel >= 2 and dbgMsgIn == 1:
							print("1-B: Node ID:",node_id," Already Exist In Node Table, Updating MS Version")
						cur.execute('UPDATE nodes SET ms_version = %s where node_id = %s', (payload, node_id))
						con.commit()

				# ..::Step One C::..
				# First time Node Comes online set the min_value.
				if (node_id != 0 and child_sensor_id != 255 and message_type == 1 and sub_type == 24):
						if dbgLevel >= 2 and dbgMsgIn == 1:
								print("4: Adding Node's min_value for Node ID:", node_id, " min_value:", payload)
						cur.execute('UPDATE nodes SET min_value = %s where node_id = %s', (payload, node_id))
						con.commit()

				# ..::Step Two ::..
				# Add Nodes Name i.e. Relay, Temperature Sensor etc. to Nodes Table.
				if (child_sensor_id == 255 and message_type == 3 and sub_type == 11):
					if dbgLevel >= 2 and dbgMsgIn == 1:
						print("2: Update Node Record for Node ID:", node_id, " Sensor Type:", payload)
					cur.execute('UPDATE nodes SET name = %s where node_id = %s', (payload, node_id))
					con.commit()

				# ..::Step Three ::..
				# Add Nodes Sketch Version to Nodes Table.
				if (node_id != 0 and child_sensor_id == 255 and message_type == 3 and sub_type == 12):
					if dbgLevel >= 2 and dbgMsgIn == 1:
						print("3: Update Node ID: ", node_id, " Node Sketch Version: ", payload)
					cur.execute('UPDATE nodes SET sketch_version = %s where node_id = %s', (payload, node_id))
					con.commit()

				# ..::Step Four::..
				# Add Node Child ID to Node Table
				#25;0;0;0;6;
				if (node_id != 0 and child_sensor_id != 255 and message_type == 0 and (sub_type == 3 or sub_type == 6)):
					if dbgLevel >= 2 and dbgMsgIn == 1:
						print("4: Adding Node's Max Child ID for Node ID:", node_id, " Child Sensor ID:", child_sensor_id)
					cur.execute('UPDATE nodes SET max_child_id = %s WHERE node_id = %s', (child_sensor_id, node_id))
					con.commit()

				# ..::Step Five::..
				# Add Temperature Reading to database
				if (node_id != 0 and child_sensor_id != 255 and message_type == 1 and sub_type == 0):
					if dbgLevel >= 2 and dbgMsgIn == 1:
						print("5: Adding Temperature Reading From Node ID:", node_id, " Child Sensor ID:", child_sensor_id, " PayLoad:", payload)
					timestamp = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
					cur.execute('INSERT INTO messages_in(`sync`, `purge`, `node_id`, `child_id`, `sub_type`, `payload`, `datetime`) VALUES(%s,%s,%s,%s,%s,%s,%s)', (0,0,node_id,child_sensor_id,sub_type,payload,timestamp))
					con.commit()
					cur.execute('UPDATE `nodes` SET `last_seen`=now(), `sync`=0  WHERE node_id = %s', [node_id])
					con.commit()
                                        # Check is sensor is attached to a zone which is being graphed
					cur.execute('SELECT * FROM `zone_view` where sensors_id = (%s) AND sensor_child_id = (%s) LIMIT 1;', (node_id, child_sensor_id))
					results =cur.fetchone()
					if cur.rowcount > 0:
                                                name_to_index = dict(
                                                (d[0], i)
                                                for i, d
                                                in enumerate(cur.description)
                                                )
                                                zone_id = int(results[name_to_index['id']])
                                                name = results[name_to_index['name']]
                                                type = results[name_to_index['type']]
                                                category = int(results[name_to_index['category']])
                                                graph_it = int(results[name_to_index['graph_it']])
                                                if category < 2 and graph_it == 1:
                                                        if dbgLevel >= 2 and dbgMsgIn == 1:
                                                                print("5a: Adding Temperature Reading to Graph Table From Node ID:", node_id, " Child Sensor ID:", child_sensor_id, " PayLoad:", payload)
                                                        cur.execute('INSERT INTO zone_graphs(`sync`, `purge`, `zone_id`, `name`, `type`, `category`, `node_id`,`child_id`, `sub_type`, `payload`, `datetime`) VALUES(%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)', (0,0,zone_id,name,type,category,node_id,child_sensor_id,sub_type,payload,timestamp))
                                                        con.commit()
                                                        cur.execute('DELETE FROM zone_graphs WHERE node_id = (%s) AND child_id = (%s) AND datetime < CURRENT_TIMESTAMP - INTERVAL 24 HOUR;', (node_id, child_sensor_id))
                                                        con.commit()

				# ..::Step Six::..
				# Add Battery Voltage Nodes Battery Table
				# Example: 25;1;1;0;38;4.39
				if (node_id != 0 and child_sensor_id != 255 and message_type == 1 and sub_type == 38):
					if dbgLevel >= 2 and dbgMsgIn == 1:
						print("6: Battery Voltage for Node ID:", node_id, " Battery Voltage:", payload)
					##b_volt = payload # dont add record to table insted add record with battery voltage and level in next step
					timestamp = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
					cur.execute('INSERT INTO nodes_battery(`sync`, `purge`, `node_id`, `bat_voltage`, `update`) VALUES(%s,%s,%s,%s,%s)', (0,0,node_id,payload,timestamp))
					##cur.execute('UPDATE `nodes` SET `last_seen`=now() WHERE node_id = %s', [node_id])
					con.commit()

				# ..::Step Seven::..
				# Add Battery Level Nodes Battery Table
				# Example: 25;255;3;0;0;104
				if (node_id != 0 and child_sensor_id == 255 and message_type == 3 and sub_type == 0):
					if dbgLevel >= 2 and dbgMsgIn == 1:
						print("7: Adding Battery Level & Voltage for Node ID:", node_id, "Battery Level:",payload)
					##cur.execute('INSERT INTO nodes_battery(node_id, bat_voltage, bat_level) VALUES(%s,%s,%s)', (node_id, b_volt, payload)) ## This approach causes to crash this script, if variable b_volt is missing. As well battery voltage could be assigned to wrong node.
					cur.execute('UPDATE nodes_battery SET bat_level = %s WHERE id=(SELECT nid from (SELECT MAX(id) as nid FROM nodes_battery WHERE node_id = %s ) as n)',(payload, node_id))
					cur.execute('UPDATE nodes SET last_seen=now(), `sync`=0 WHERE node_id = %s', [node_id])
					con.commit()

				# ..::Step Eight::..
				# Add Boost Status Level to Database/Relay Last seen gets added here as well when ACK is set to 1 in messages_out table.
				if (node_id != 0 and child_sensor_id != 255 and message_type == 1 and sub_type == 2):
				# print "2 insert: ", node_id, " , ", child_sensor_id, "payload", payload
					if dbgLevel >= 2 and dbgMsgIn == 1:
						print("8. Adding Database Record: Node ID:",node_id," Child Sensor ID:", child_sensor_id, " PayLoad:", payload)
					xboost = "UPDATE boost SET status=%s WHERE boost_button_id=%s AND boost_button_child_id = %s"
					cur.execute(xboost, (payload, node_id, child_sensor_id,))
					con.commit()
					cur.execute('UPDATE `nodes` SET `last_seen`=now(), `sync`=0 WHERE node_id = %s', [node_id])
					con.commit()

				# ..::Step Nine::..
				# Add Away Status Level to Database
				if (node_id != 0 and child_sensor_id != 255 and child_sensor_id == 4 and message_type == 1 and sub_type == 2):
				# print "2 insert: ", node_id, " , ", child_sensor_id, "payload", payload
					if dbgLevel >= 2 and dbgMsgIn == 1:
						print("9. Adding Database Record: Node ID:", node_id, " Child Sensor ID:", child_sensor_id, " PayLoad:", payload)
					xaway = "UPDATE away SET status=%s WHERE away_button_id=%s AND away_button_child_id = %s"
					cur.execute(xaway, (payload, node_id, child_sensor_id,))
					con.commit()
					cur.execute('UPDATE `nodes` SET `last_seen`=now(), `sync`=0  WHERE node_id = %s', [node_id])
					con.commit()
				#else:
					#print bc.WARN+ "No Action Defined Incomming Node Message Ignored \n\n" +bc.ENDC

				# ..::Step Ten::..
				# When Gateway Startup Completes
				if (node_id == 0 and child_sensor_id == 255 and message_type == 0 and sub_type == 18):
					if dbgLevel >= 2 and dbgMsgIn == 1:
						print("10: PiHome MySensors Gateway Version :", payload)
					cur.execute('UPDATE gateway SET version = %s', [payload])
					con.commit()

				# ..::Step Eleven::.. 40;0;3;0;1;02:27
				# When client is requesting time
				if (node_id != 0 and child_sensor_id == 255 and message_type == 3 and sub_type == 1):
					if dbgLevel >= 2 and dbgMsgIn == 1:
						print("11: Node ID: ",node_id," Requested Time")
					#nowtime = time.ctime()
					nowtime = time.strftime('%H:%M')
					ntime = "UPDATE messages_out SET payload=%s, sent=%s WHERE node_id=%s AND child_id = %s"
					cur.execute(ntime, (nowtime, '0', node_id, child_sensor_id,))
					con.commit()

				# ..::Step Twelve::.. 40;0;3;0;1;02:27
				# When client is requesting text
				if (node_id != 0 and message_type == 2 and sub_type == 47):
					if dbgLevel >= 2 and dbgMsgIn == 1:
						print("12: Node ID: ",node_id,"Child ID: ", child_sensor_id," Requesting Text")
					nowtime = time.strftime('%H:%M')
					ntime = "UPDATE messages_out SET payload=%s, sent=%s WHERE node_id=%s AND child_id = %s"
					#cur.execute(ntime, (nowtime, '0', node_id, child_sensor_id,))
					#con.commit()

				# ..::Step Thirteen::.. 255;18;3;0;3;
				# When Node is requesting ID
				if (node_id != 0 and message_type == 3 and sub_type == 3): # best is to check node_id is 255 but i can not get to work with that.
					if dbgLevel >= 2 and dbgMsgIn == 1:
						print("12: Node ID: ",node_id," Child ID: ", child_sensor_id," Requesting Node ID")
					nowtime = time.strftime('%H:%M')
					cur.execute('SELECT COUNT(*) FROM `node_id` where sent = 0') # MySQL query statemen
					count = cur.fetchone()
					count = count[0]
					if count > 0:
						cur.execute('SELECT * FROM `node_id` where sent = 0 Limit 1;') # MySQL query statement
						node_row = cur.fetchone();
						node_id_to_index = dict(
						(d[0], i)
						for i, d
						in enumerate(cur.description)
						)
						out_id = node_row[node_id_to_index['id']]                #Record ID - only DB info
						new_node_id = node_row[node_id_to_index['node_id']]        # Node ID from Table
						msg = str(node_id) 			#Broadcast Node ID
						msg += ';' 					#Separator
						msg += str(child_sensor_id) #Child ID of the Node.
						msg += ';' 					#Separator
						msg += str(3)
						msg += ';' 					#Separator
						msg += str(0)
						msg += ';' 					#Separator
						msg += str(4)
						msg += ';' 					#Separator
						msg += str(new_node_id) 	#Payload from DB
						msg += ' \n'				#New line
						if dbgLevel >= 3 and dbgMsgOut == 1:
							print("Full Message to Send:        ",msg.replace("\n","\\n")) #Print Full Message
							print("Node ID:                     ",node_id)
							print("Child Sensor ID:             ",child_sensor_id)
							print("Command Type:                ",3)
							print("Ack Req/Resp:                ",0)
							print("Type:                        ",4)
							print("Pay Load:                    ",new_node_id)
						# node-id ; child-sensor-id ; command ; ack ; type ; payload \n
						if gatewaytype == 'serial':
							gw.write(msg.encode('utf-8')) # !!!! send it to serial (arduino attached to rPI by USB port)
						else:
							print('write')
							gw.write(msg.encode('utf-8'))
							cur.execute('UPDATE `node_id` set sent=1, `date_time`=now() where id=%s', [out_id]) #update DB so this message will not be processed in next loop
							con.commit() #commit above
					else:
						print(bc.WARN +"All exiting IDs are assigned: " + bc.ENDC)
		time.sleep(0.1)

except configparser.Error as e:
	print("ConfigParser:",format(e))
	con.close()
except mdb.Error as e:
	print("DB Error %d: %s" % (e.args[0], e.args[1]))
	con.close()
except serial.SerialException as e:
	print("SerialException:",format(e))
	con.close()
except EOFError as e:
	print("EOFError:",format(e))
	con.close()
except Exception as e:
	print(format(e))
	con.close()
finally:
	print(infomsg)
	logging.exception(Exception)
	sys.exit(1)
