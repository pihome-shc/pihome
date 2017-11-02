#!/usr/bin/python
class bcolors:
	HEADER = '\033[95m'
	OKBLUE = '\033[94m'
	OKGREEN = '\033[92m'
	YELLOW = '\033[93m'
	FAIL = '\033[91m'
	ENDC = '\033[0m'
print bcolors.HEADER + " "
print "  _____    _   _    _                            "
print " |  __ \  (_) | |  | |                           "
print " | |__) |  _  | |__| |   ___    _ __ ___     ___ "
print " |  ___/  | | |  __  |  / _ \  | |_  \_ \   / _ \ "
print " | |      | | | |  | | | (_) | | | | | | | |  __/"
print " |_|      |_| |_|  |_|  \___/  |_| |_| |_|  \___|"
print " "
print "    S M A R T   H E A T I N G   C O N T R O L "
print "*******************************************************"
print "* WiFi - ESP8266 Gateway Build for Wireless sensors   *"
print "* using MySeonsors serial API Build Date: 18/09/2017  *"
print "*                                Have Fun - PiHome.eu *"
print "*******************************************************"
print " " + bcolors.ENDC
import sys, telnetlib, MySQLdb as mdb, time
# ref: https://forum.mysensors.org/topic/7818/newline-of-debug-output/2
# stty -F /dev/ttyUSB0 115200
# cat /dev/ttyUSB0

HOST = "192.168.99.3" 	# ip address of your mysensors wifi gateway
port = "5003" 			# UDP port number for mysensors gateway
timeout = 3    			# Connection timout in Seconds
tn = telnetlib.Telnet(HOST, port, timeout) # Connect mysensors gateway 
while 1:
	try:
		con = mdb.connect('localhost', 'root', 'passw0rd', 'pihome') # MySQL Database Connection Settings
		cur = con.cursor() # Cursor object to Current Connection
		cur.execute('SELECT COUNT(*) FROM `messages_out` where sent = 0') # MySQL query statement
		count = cur.fetchone() # Grab all messages from database for Outgoing. 
		count = count[0] # Parse first and the only one part of data table named "count" - there is number of records grabbed in SELECT above
		if count > 0: #If greater then 0 then we have something to send out. 
			print bcolors.OKGREEN + "Total Messages to Sent : ",count, bcolors.ENDC # Print how many Messages we have to send out.
			cur.execute('SELECT * FROM `messages_out` where sent = 0') #grab all messages that where not send yet (sent ==0)
			msg = cur.fetchone(); 	#Grab first record and build a message: if you change table fields order you need to change following lines as well. 
			out_id = int(msg[0]) 	#Record ID - only DB info,
			out_node_id = msg[1] 	#Node ID 
			out_child_id = msg[2] 	#Child ID of the node where sensor/relay is attached.
			out_sub_type = msg[3] 	#Command Type  
			out_ack = msg[4] 		#Ack req/resp
			out_type = msg[5]  		#Type  
			out_payload = msg[6] 	#Payload to send out. 
			sent = msg[7] 			#Status of message either its sent or not. (1 for sent, 0 for not sent yet)
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
			print "Full Message to Send:   ",msg #Print Full Message
			print "Node ID:                 ",out_node_id
			print "Child Sensor ID:         ",out_child_id
			print "Command Type:            ",out_sub_type			
			print "Ack Req/Resp:            ",out_ack			
			print "Type:                    ",out_type			
			print "Pay Load:                ",out_payload
			print " \n"
			# node-id ; child-sensor-id ; command ; ack ; type ; payload \n
			try: 
				tn.write(msg)
			except:
				print "Sending Message Failed"
			# help http://stackoverflow.com/questions/21740359/python-mysqldb-typeerror-not-all-arguments-converted-during-string-formatting
			cur.execute('UPDATE `messages_out` set sent=1 where id=%s', [out_id]) #update DB so this message will not be processed in next loop
			con.commit() #commit above
	except mdb.Error, e:
		print "Error %d: %s" % (e.args[0], e.args[1])
		sys.exit(1)
	finally:
		if con:
			con.close()
	try:
		in_str =  tn.read_until('\n', timeout=1) #Here is receiving part of the code
	except EOFError as e:
		print "Connection closed: %s" % e
	# ..:: Un-comments Following two lines to see what you are receing and size of string ::..
	# print "Size of String:          ", sys.getsizeof(in_str)," \n"
	# print "String as Received:      ",in_str," \n"
	if not sys.getsizeof(in_str) <= 22 : # string value less then 21 is null value hence we need to 

		print bcolors.YELLOW + "Size of the String Received:      ", sys.getsizeof(in_str), bcolors.ENDC
		print "Date & Time:             ", time.ctime()
		print "Full String Received:    ", in_str
		statement = in_str.split(";")
		print "Full Statement Received: ", statement
		node_id = int(statement[0])
		print "Node ID:                 ", node_id
		child_sensor_id = int(statement[1])
		print "Child Sensor ID:         ", child_sensor_id
		message_type = int(statement[2])
		print "Message Type:            ", message_type
		ack = int(statement[3])
		print "Acknowledge:             ", ack
		sub_type = int(statement[4])
		print "Sub Type:                ", sub_type
		payload = statement[5]
		print "Pay Load:                ", payload
		try:
			con = mdb.connect('localhost', 'root', 'passw0rd', 'pihome')#Database Connection Settings 
			cur = con.cursor()
			
			# ..::Step One::..
			# First time sensor comes online and add node to nodes table, 
			if (child_sensor_id != 255 and message_type == 0):
				cur.execute('SELECT COUNT(*) FROM `nodes` where node_id = (%s)', (node_id)) 
				row = cur.fetchone()  
				row = int(row[0])
				if (row == 0):
					print "1 Add Node: ", node_id, " Child Sensor ID:", child_sensor_id
					cur.execute('INSERT INTO nodes(node_id, child_id_1) VALUES(%s,%s)', (node_id,child_sensor_id))
					con.commit()
					
			# ..::Step Two ::..
			# Add Nodes name ie. relay, temperature sensor etc... to Database
			if (child_sensor_id == 255 and message_type == 3 and sub_type == 11):
				payload = payload[:-1] # remove \n from payload otherwise you will endup two lines sensors name in database. 
				print "2 Update NodeID:", node_id, "Child Sensor ID:", child_sensor_id, " Sensor Type:", payload
				cur.execute('UPDATE nodes SET name = %s where node_id = %s', (payload, node_id))
				con.commit()

			# ..::Step Three ::..
			# Add Nodes MySensors Version to database 
			if (node_id != 0 and child_sensor_id == 255 and message_type == 0 and sub_type == 17):
				payload = payload[:-1] # remove \n from payload otherwise you will endup two lines sensors name in database. 
				print "3 Update NodeID:", node_id, "Child Sensor ID:", child_sensor_id, " Sensor Type:", payload
				cur.execute('UPDATE nodes SET ms_version = %s where node_id = %s', (payload, node_id))
				con.commit()

			# ..::Step Four ::..
			# Add Nodes Sketch Version to database 
			if (node_id != 0 and child_sensor_id == 255 and message_type == 3 and sub_type == 12):
				payload = payload[:-1] # remove \n from payload otherwise you will endup two lines sensors name in database. 
				print "4 Update NodeID:", node_id, "Child Sensor ID:", child_sensor_id, " Sensor Type:", payload
				cur.execute('UPDATE nodes SET sketch_version = %s where node_id = %s', (payload, node_id))
				con.commit()
				
			# ..::Step Five::..
			# Add Temperature Reading to database 
			if (node_id != 0 and child_sensor_id != 255 and message_type == 1 and sub_type == 0):
				print "5. Adding Database Record: Node ID:", node_id, " Child Sensor ID:", child_sensor_id, " PayLoad:", payload, "\n"
				cur.execute('INSERT INTO messages_in(node_id, child_id, sub_type, payload) VALUES(%s,%s,%s,%s)', (node_id,child_sensor_id,sub_type,payload))
				con.commit()
				cur.execute('UPDATE `nodes` SET `last_seen`=now() WHERE node_id = %s', [node_id])
				con.commit()

			# ..::Step Six::..
			# Add Battery Level to Database
			# 20;255;3;0;0;102
			if (child_sensor_id == 255 and message_type == 3 and sub_type == 0):  #BATTERY Level
				print "6. Adding Database Record: Node ID:", node_id, " Battery Level:", payload, "Battery Voltage ", b_volt, "\n"
				cur.execute('INSERT INTO nodes_battery(node_id, bat_level, bat_voltage) VALUES(%s,%s,%s)', (node_id,payload,b_volt))
				cur.execute('UPDATE `nodes` SET `last_seen`=now() WHERE node_id = %s', [node_id])
				con.commit()

			# ..::Step Seven::..
			# Add Battery Voltage to Database
			# 20;1;1;0;38;3.78
			# node-id ; child-sensor-id ; command ; ack ; type ; payload \n
			if (child_sensor_id != 255 and message_type == 1 and sub_type == 38):  #BATTERY VOLTAGE
				b_volt = payload

				# ..::Step Eight::..
			# Add Boost Status Level to Database/Relay Last seen gets added here as well when ACK is set to 1 in messages_out table. 
			if (node_id != 0 and child_sensor_id != 255 and message_type == 1 and sub_type == 2):
			# print "2 insert: ", node_id, " , ", child_sensor_id, "payload", payload
				print "8. Adding Database Record: Node ID:", node_id, " Child Sensor ID:", child_sensor_id, " PayLoad:", payload, "\n"
				xboost = "UPDATE boost SET status=%s WHERE boost_button_id=%s AND boost_button_child_id = %s"
				cur.execute(xboost, (payload, node_id,child_sensor_id,))
				con.commit()
				cur.execute('UPDATE `nodes` SET `last_seen`=now() WHERE node_id = %s', [node_id])
				con.commit()

				# ..::Step Nine::..
			# Add Away Status Level to Database 
			if (node_id != 0 and child_sensor_id != 255 and child_sensor_id == 4 and message_type == 1 and sub_type == 2):
			# print "2 insert: ", node_id, " , ", child_sensor_id, "payload", payload
				print "9. Adding Database Record: Node ID:", node_id, " Child Sensor ID:", child_sensor_id, " PayLoad:", payload, "\n"
				xaway = "UPDATE away SET status=%s WHERE away_button_id=%s AND away_button_child_id = %s"
				cur.execute(xaway, (payload, node_id,child_sensor_id,))
				con.commit()
				cur.execute('UPDATE `nodes` SET `last_seen`=now() WHERE node_id = %s', [node_id])
				con.commit()


				
		except mdb.Error, e:
				print "Error %d: %s" % (e.args[0], e.args[1])
				sys.exit(1)
		finally:
			if con:
				con.close()
	time.sleep(1)