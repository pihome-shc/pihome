#!/usr/bin/python
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
print bc.hed + " "	
print "  _____    _   _    _                            "
print " |  __ \  (_) | |  | |                           "
print " | |__) |  _  | |__| |   ___    _ __ ___     ___ "
print " |  ___/  | | |  __  |  / _ \  | |_  \_ \   / _ \ "
print " | |      | | | |  | | | (_) | | | | | | | |  __/"
print " |_|      |_| |_|  |_|  \___/  |_| |_| |_|  \___|"
print " "
print "    "+bc.SUB + "S M A R T   H E A T I N G   C O N T R O L "+ bc.ENDC
print bc.WARN +" "
print "********************************************************"
print "* MySensors Wifi/Ethernet Gateway Communication Script *"
print "* to communicate with MySensors Nodes, for more info   *"
print "* please check MySensors API. Build Date: 18/09/2017   *"
print "*      Version 0.08 - Last Modified 20/07/2019         *"
print "*                                 Have Fun - PiHome.eu *"
print "********************************************************"
print " " + bc.ENDC

import sys, telnetlib, MySQLdb as mdb, time
import ConfigParser, logging

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

try:
	# Initialise the database access varables
	config = ConfigParser.ConfigParser()
	config.read('/var/www/st_inc/db_config.ini')
	dbhost = config.get('db', 'hostname')
	dbuser = config.get('db', 'dbusername')
	dbpass = config.get('db', 'dbpassword')
	dbname = config.get('db', 'dbname')

	con = mdb.connect(dbhost, dbuser, dbpass, dbname)
	cur = con.cursor()
	cur.execute('SELECT * FROM gateway where status = 1 order by id asc limit 1')
	row = cur.fetchone();
	gatewayip = row[5]     #ip address of your MySensors gateway
	gatewayport = row[6]   #UDP port number for MySensors gateway
	timeout = 3    		   #Connection timeout in Seconds

	msgcount = 0 # Defining variable for counting messages processed
	print bc.grn + "MySensors IP   : ",gatewayip, bc.ENDC 
	print bc.grn + "MySensors Port : ",gatewayport, bc.ENDC

	#MySensors Wifi/Ethernet Gateway Manuall override to specific ip Otherwise ip from MySQL Databased is used. 
	#mysgw = "192.168.99.3" 	#ip address of your MySensors gateway
	#mysport = "5003" 		#UDP port number for MySensors gateway


	tn = telnetlib.Telnet(gatewayip, gatewayport, timeout) # Connect mysensors gateway from MySQL Database
	#tn = telnetlib.Telnet(mysgw, mysport, timeout) # Connect mysensors gateway
	
	while 1:
	## Outgoing messages
		cur.execute('SELECT COUNT(*) FROM `messages_out` where sent = 0') # MySQL query statement
		count = cur.fetchone() # Grab all messages from database for Outgoing. 
		count = count[0] # Parse first and the only one part of data table named "count" - there is number of records grabbed in SELECT above
		if count > 0: #If greater then 0 then we have something to send out. 
			cur.execute('SELECT * FROM `messages_out` where sent = 0') #grab all messages that where not send yet (sent ==0)
			msg = cur.fetchone(); 	#Grab first record and build a message: if you change table fields order you need to change following lines as well. 
			out_id = int(msg[0]) 	#Record ID - only DB info,
			out_node_id = msg[3] 	#Node ID 
			out_child_id = msg[4] 	#Child ID of the node where sensor/relay is attached.
			out_sub_type = msg[5] 	#Command Type  
			out_ack = msg[6] 		#Ack req/resp
			out_type = msg[7]  		#Type  
			out_payload = msg[8] 	#Payload to send out. 
			sent = msg[9] 			#Status of message either its sent or not. (1 for sent, 0 for not sent yet)
			if dbgLevel >= 1 and dbgMsgOut == 1: # Debug print to screen
				print bc.grn + "\nTotal Messages to Sent : ",count, bc.ENDC # Print how many Messages we have to send out.
				print "Date & Time:            ",time.ctime()
				print "Message From Database:  ",out_id, out_node_id, out_child_id, out_sub_type, out_ack, out_type, out_payload, sent #Print what will be sent including record id and sent status.
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
				print "Full Message to Send:   ",msg.replace("\n","\\n") #Print Full Message
				print "Node ID:                 ",out_node_id
				print "Child Sensor ID:         ",out_child_id
				print "Command Type:            ",out_sub_type			
				print "Ack Req/Resp:            ",out_ack			
				print "Type:                    ",out_type			
				print "Pay Load:                ",out_payload

			# node-id ; child-sensor-id ; command ; ack ; type ; payload \n
			tn.write(msg)
			cur.execute('UPDATE `messages_out` set sent=1 where id=%s', [out_id]) #update DB so this message will not be processed in next loop
			con.commit() #commit above
			
	## Incoming messages
		in_str =  tn.read_until('\n', timeout=1) #Here is receiving part of the code
		if dbgLevel >= 2: # Debug print to screen
			if time.strftime("%S",time.gmtime())== '00' and msgcount != 0:
				print bc.hed + "\nMessages processed in last 60s:	",msgcount
				print "Bytes in outgoing buffer:	",ser.in_waiting
				print "Date & Time:                 	",time.ctime(),bc.ENDC
				msgcount = 0 
			if not sys.getsizeof(in_str) <= 22:
				msgcount += 1
			
		if not sys.getsizeof(in_str) <= 22 and in_str[:1] != '0': #here is the line where sensor are processed
			if dbgLevel >= 1 and dbgMsgIn == 1: # Debug print to screen
				print bc.ylw + "\nSize of the String Received: ", sys.getsizeof(in_str), bc.ENDC
				print "Date & Time:             ", time.ctime()
				print "Full String Received:    ", in_str.replace("\n","\\n")
			statement = in_str.split(";")
			if dbgLevel >= 3 and dbgMsgIn == 1: 
				print "Full Statement Received: ", statement
			
			if len(statement) == 6 and statement[0].isdigit(): #check if received message is right format
				node_id = int(statement[0])
				child_sensor_id = int(statement[1])
				message_type = int(statement[2])
				ack = int(statement[3])
				sub_type = int(statement[4])
				payload = statement[5].rstrip() # remove \n from payload
				
				if dbgLevel >= 3 and dbgMsgIn == 1: # Debug print to screen
					print "Node ID:                 ", node_id
					print "Child Sensor ID:         ", child_sensor_id
					print "Message Type:            ", message_type
					print "Acknowledge:             ", ack
					print "Sub Type:                ", sub_type
					print "Pay Load:                ", payload

				# ..::Step One::..
				# First time Temperature Sensors Node Comes online: Add Node to The Nodes Table.
				if (node_id != 0 and child_sensor_id == 255 and message_type == 0 and sub_type == 17):
				#if (child_sensor_id != 255 and message_type == 0):
					cur.execute('SELECT COUNT(*) FROM `nodes` where node_id = (%s)', (node_id, )) 
					row = cur.fetchone()  
					row = int(row[0])
					if (row == 0):
						if dbgLevel >= 2 and dbgMsgIn == 1:
							print "1: Adding Node ID:",node_id, "MySensors Version:", payload, "\n\n"
						cur.execute('INSERT INTO nodes(type, node_id, status, ms_version) VALUES(%s, %s, %s, %s)', ('MySnRF', node_id, 'Active', payload))
						con.commit()
					else: 
						if dbgLevel >= 2 and dbgMsgIn == 1:
							print "1: Node ID:",node_id," Already Exist In Node Table, Updating MS Version \n\n"
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
							print "1-B: Adding Node ID:",node_id, "MySensors Version:", payload, "\n\n"
						cur.execute('INSERT INTO nodes(type, node_id, repeater, ms_version) VALUES(%s, %s, %s, %s)', ('MySnRF', node_id, '1', payload))
						con.commit()
					else: 
						if dbgLevel >= 2 and dbgMsgIn == 1:
							print "1-B: Node ID:",node_id," Already Exist In Node Table, Updating MS Version \n\n"
						cur.execute('UPDATE nodes SET ms_version = %s where node_id = %s', (payload, node_id))
						con.commit()

				# ..::Step Two ::..
				# Add Nodes Name i.e. Relay, Temperature Sensor etc. to Nodes Table.
				if (child_sensor_id == 255 and message_type == 3 and sub_type == 11):
					payload = payload[:-1] # remove \n from payload otherwise you will endup two lines sensors name in database. 
					if dbgLevel >= 2 and dbgMsgIn == 1:
						print "2: Update Node Record for Node ID:", node_id, " Sensor Type:", payload, "\n\n"
					cur.execute('UPDATE nodes SET name = %s where node_id = %s', (payload, node_id))
					con.commit()

				# ..::Step Three ::..
				# Add Nodes Sketch Version to Nodes Table.  
				if (node_id != 0 and child_sensor_id == 255 and message_type == 3 and sub_type == 12):
					payload = payload[:-1] # remove \n from payload otherwise you will endup two lines sensors name in database. 
					if dbgLevel >= 2 and dbgMsgIn == 1:
						print "3: Update Node ID: ", node_id, " Node Sketch Version: ", payload, "\n\n"
					cur.execute('UPDATE nodes SET sketch_version = %s where node_id = %s', (payload, node_id))
					con.commit()
					
				# ..::Step Four::..
				# Add Node Child ID to Node Table
				#25;0;0;0;6;
				if (node_id != 0 and child_sensor_id != 255 and message_type == 0 and sub_type == 6):
					if dbgLevel >= 2 and dbgMsgIn == 1:
						print "4: Adding Node's Child ID for Node ID:", node_id, " Child Sensor ID:", child_sensor_id, "\n\n"
					cur.execute('UPDATE nodes SET child_id_1 = %s WHERE node_id = %s', [child_sensor_id, node_id])
					con.commit()

				# ..::Step Five::..
				# Add Temperature Reading to database 
				if (node_id != 0 and child_sensor_id != 255 and message_type == 1 and sub_type == 0):
					if dbgLevel >= 2 and dbgMsgIn == 1:
						print "5: Adding Temperature Reading From Node ID:", node_id, " Child Sensor ID:", child_sensor_id, " PayLoad:", payload, "\n\n"
					cur.execute('INSERT INTO messages_in(node_id, child_id, sub_type, payload) VALUES(%s,%s,%s,%s)', (node_id,child_sensor_id,sub_type,payload))
					con.commit()
					cur.execute('UPDATE `nodes` SET `last_seen`=now(), `sync`=0  WHERE node_id = %s', [node_id])
					con.commit()

				# ..::Step Six::..
				# Add Battery Voltage Nodes Battery Table
				# Example: 25;1;1;0;38;4.39
				if (node_id != 0 and child_sensor_id != 255 and message_type == 1 and sub_type == 38):
					if dbgLevel >= 2 and dbgMsgIn == 1:
						print "6: Battery Voltage for Node ID:", node_id, " Battery Voltage:", payload, "\n\n"
					##b_volt = payload # dont add record to table insted add record with battery voltage and level in next step
					cur.execute('INSERT INTO nodes_battery(node_id, bat_voltage) VALUES(%s,%s)', (node_id,payload))
					##cur.execute('UPDATE `nodes` SET `last_seen`=now() WHERE node_id = %s', [node_id])
					con.commit()

				# ..::Step Seven::..
				# Add Battery Level Nodes Battery Table
				# Example: 25;255;3;0;0;104
				if (node_id != 0 and child_sensor_id == 255 and message_type == 3 and sub_type == 0):
					if dbgLevel >= 2 and dbgMsgIn == 1:
						print "7: Adding Battery Level & Voltage for Node ID:", node_id, "Battery Voltage:",b_volt,"Battery Level:",payload,"\n\n"
					##cur.execute('INSERT INTO nodes_battery(node_id, bat_voltage, bat_level) VALUES(%s,%s,%s)', (node_id, b_volt, payload)) ## This approach causes to crash this script, if variable b_volt is missing. As well battery voltage could be assigned to wrong node.
					cur.execute('UPDATE nodes_battery SET bat_level = %s WHERE id=(SELECT nid from (SELECT MAX(id) as nid FROM nodes_battery WHERE node_id = %s ) as n)',(payload, node_id))
					cur.execute('UPDATE nodes SET last_seen=now(), `sync`=0 WHERE node_id = %s', [node_id])
					con.commit()

					# ..::Step Eight::..
				# Add Boost Status Level to Database/Relay Last seen gets added here as well when ACK is set to 1 in messages_out table. 
				if (node_id != 0 and child_sensor_id != 255 and message_type == 1 and sub_type == 2):
				# print "2 insert: ", node_id, " , ", child_sensor_id, "payload", payload
					if dbgLevel >= 2 and dbgMsgIn == 1:
						print "8. Adding Database Record: Node ID:",node_id," Child Sensor ID:", child_sensor_id, " PayLoad:", payload, "\n"
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
						print "9. Adding Database Record: Node ID:", node_id, " Child Sensor ID:", child_sensor_id, " PayLoad:", payload, "\n"
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
						print "10: PiHome MySensors Gateway Version :", payload, "\n\n"
					cur.execute('UPDATE gateway SET version = %s', [payload])
					con.commit()
					
				# ..::Step Eleven::.. 40;0;3;0;1;02:27 
				# When client is requesting time
				if (node_id != 0 and child_sensor_id == 255 and message_type == 3 and sub_type == 1):
					if dbgLevel >= 2 and dbgMsgIn == 1:
						print "11: Node ID: ",node_id," Requested Time \n"
					#nowtime = time.ctime()
					nowtime = time.strftime('%H:%M')
					ntime = "UPDATE messages_out SET payload=%s, sent=%s WHERE node_id=%s AND child_id = %s"
					cur.execute(ntime, (nowtime, '0', node_id, child_sensor_id,))
					con.commit()
				
				# ..::Step Twelve::.. 40;0;3;0;1;02:27 
				# When client is requesting text
				if (node_id != 0 and message_type == 2 and sub_type == 47):
					if dbgLevel >= 2 and dbgMsgIn == 1:
						print "12: Node ID: ",node_id,"Child ID: ", child_sensor_id," Requesting Text \n"
					nowtime = time.strftime('%H:%M')
					ntime = "UPDATE messages_out SET payload=%s, sent=%s WHERE node_id=%s AND child_id = %s"
					#cur.execute(ntime, (nowtime, '0', node_id, child_sensor_id,))
					#con.commit()

		time.sleep(0.1)

except ConfigParser.Error as e:
	print "ConfigParser:",format(e)
	con.close()
except mdb.Error, e:
	print "DB Error %d: %s" % (e.args[0], e.args[1])
	con.close()
except EOFError as e:
	try:
		print "Connection Lost to Smart Home Gateway with Error: %s" % e
		con = mdb.connect(dbhost, dbuser, dbpass, dbname)
		cur = con.cursor()
		#e = "Connection Lost to Smart Home Gateway" + e 
		cur.execute("INSERT INTO notice(message) VALUES(%s)", (e))
		con.commit()
		con.close()
	except mdb.Error, e:
		print "DB Error %d: %s" % (e.args[0], e.args[1])
		con.close()
except Exception as e:
	print format(e)
	con.close()
finally:
	print infomsg
	logging.exception(e)
	sys.exit(1)