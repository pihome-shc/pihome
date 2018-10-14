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
print "*      Version 0.07 - Last Modified 04/10/2018         *"
print "*                                 Have Fun - PiHome.eu *"
print "********************************************************"
print " " + bc.ENDC
import sys, telnetlib, MySQLdb as mdb, time
# ref: https://forum.mysensors.org/topic/7818/newline-of-debug-output/2
# stty -F /dev/ttyUSB0 115200
# cat /dev/ttyUSB0

#PiHome Database Settings Variables 
dbhost = 'localhost'
dbuser = 'root'
dbpass = 'passw0rd'
dbname = 'pihome'

con = mdb.connect(dbhost, dbuser, dbpass, dbname)
cur = con.cursor()
cur.execute('SELECT * FROM gateway where status = 1 order by id asc limit 1')
row = cur.fetchone();
gatewayip = row[5]     #ip address of your MySensors gateway
gatewayport = row[6]   #UDP port number for MySensors gateway
timeout = 3    		   #Connection timout in Seconds

print bc.grn + "MySensors IP   : ",gatewayip, bc.ENDC 
print bc.grn + "MySensors Port : ",gatewayport, bc.ENDC

#MySensors Wifi/Ethernet Gateway Manuall override to specific ip Otherwise ip from MySQL Databased is used. 
#mysgw = "192.168.99.3" 	#ip address of your MySensors gateway
#mysport = "5003" 		#UDP port number for MySensors gateway


tn = telnetlib.Telnet(gatewayip, gatewayport, timeout) # Connect mysensors gateway from MySQL Database
#tn = telnetlib.Telnet(mysgw, mysport, timeout) # Connect mysensors gateway 
while 1:
	try:
		con = mdb.connect(dbhost, dbuser, dbpass, dbname) # MySQL Database Connection Settings
		cur = con.cursor() # Cursor object to Current Connection
		cur.execute('SELECT COUNT(*) FROM `messages_out` where sent = 0') # MySQL query statement
		count = cur.fetchone() # Grab all messages from database for Outgoing. 
		count = count[0] # Parse first and the only one part of data table named "count" - there is number of records grabbed in SELECT above
		if count > 0: #If greater then 0 then we have something to send out. 
			print bc.grn + "Total Messages to Sent : ",count, bc.ENDC # Print how many Messages we have to send out.
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
		try:
			print "Connection Lost to Smart Home Gateway with Error: %s" % e
			con = mdb.connect(dbhost, dbuser, dbpass, dbname)
			cur = con.cursor()
			#e = "Connection Lost to Smart Home Gateway" + e 
			cur.execute("INSERT INTO notice(message) VALUES(%s)", (e))
			con.commit()
			con.close()
			sys.exit()
		except mdb.Error, e:
			print "Error %d: %s" % (e.args[0], e.args[1])
			sys.exit(1)
			
	# ..:: Un-comments Following two lines to see what you are receing and size of string ::..
	# print "Size of String:          ", sys.getsizeof(in_str)," \n"
	# print "String as Received:      ",in_str," \n"
	if not sys.getsizeof(in_str) <= 22 : # string value less then 21 is null value hence we need to 

		print bc.ylw + "Size of the String Received:      ", sys.getsizeof(in_str), bc.ENDC
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
			con = mdb.connect(dbhost, dbuser, dbpass, dbname)
			cur = con.cursor()
			
			# ..::Step One::..
			# First time MySensors Node Comes online: Add Node to The Nodes Table.
			if (node_id != 0 and child_sensor_id == 255 and message_type == 0 and sub_type == 17):
			#if (child_sensor_id != 255 and message_type == 0):
				cur.execute('SELECT COUNT(*) FROM `nodes` where node_id = (%s)', (node_id, )) 
				row = cur.fetchone()  
				row = int(row[0])
				if (row == 0):
					print "1: Adding Node ID:",node_id, "MySensors Version:", payload, "\n\n"
					cur.execute('INSERT INTO nodes(node_id, ms_version) VALUES(%s, %s)', (node_id, payload))
					con.commit()
				else: 
					print "1: Node ID:",node_id," Already Exist In Node Table \n\n"
					
			# ..::Step Two ::..
			# Add Nodes Name i.e. Relay, Temperature Sensor etc. to Nodes Table.
			if (child_sensor_id == 255 and message_type == 3 and sub_type == 11):
				payload = payload[:-1] # remove \n from payload otherwise you will endup two lines sensors name in database. 
				print "2: Update Node Record for Node ID:", node_id, " Sensor Type:", payload, "\n\n"
				cur.execute('UPDATE nodes SET name = %s where node_id = %s', (payload, node_id))
				con.commit()

			# ..::Step Three ::..
			# Add Nodes Sketch Version to Nodes Table.  
			if (node_id != 0 and child_sensor_id == 255 and message_type == 3 and sub_type == 12):
				payload = payload[:-1] # remove \n from payload otherwise you will endup two lines sensors name in database. 
				print "3: Update Node ID: ", node_id, " Node Sketch Version: ", payload, "\n\n"
				cur.execute('UPDATE nodes SET sketch_version = %s where node_id = %s', (payload, node_id))
				con.commit()
				
			# ..::Step Four::..
			# Add Node Child ID to Node Table
			#25;0;0;0;6;
			if (node_id != 0 and child_sensor_id != 255 and message_type == 0 and sub_type == 6):
				print "4: Adding Node's Child ID for Node ID:", node_id, " Child Sensor ID:", child_sensor_id, "\n\n"
				cur.execute('UPDATE nodes SET child_id_1 = %s WHERE node_id = %s', [child_sensor_id, node_id])
				con.commit()

			# ..::Step Five::..
			# Add Temperature Reading to database 
			if (node_id != 0 and child_sensor_id != 255 and message_type == 1 and sub_type == 0):
				print "5: Adding Temperature Reading From Node ID:", node_id, " Child Sensor ID:", child_sensor_id, " PayLoad:", payload, "\n\n"
				cur.execute('INSERT INTO messages_in(node_id, child_id, sub_type, payload) VALUES(%s,%s,%s,%s)', (node_id,child_sensor_id,sub_type,payload))
				con.commit()
				cur.execute('UPDATE `nodes` SET `last_seen`=now(), `sync`=0  WHERE node_id = %s', [node_id])
				con.commit()

			# ..::Step Six::..
			# Add Battery Voltage Nodes Battery Table
			# Example: 25;1;1;0;38;4.39
			if (node_id != 0 and child_sensor_id != 255 and message_type == 1 and sub_type == 38):
				print "6: Battery Voltage for Node ID:", node_id, " Battery Voltage:", payload, "\n\n"
				b_volt = payload # dont add record to table insted add record with battery voltage and level in next step
				##cur.execute('INSERT INTO nodes_battery(node_id, bat_level) VALUES(%s,%s)', (node_id,payload))
				##cur.execute('UPDATE `nodes` SET `last_seen`=now() WHERE node_id = %s', [node_id])
				##con.commit()

			# ..::Step Seven::..
			# Add Battery Level Nodes Battery Table
			# Example: 25;255;3;0;0;104
			if (node_id != 0 and child_sensor_id == 255 and message_type == 3 and sub_type == 0):
				print "7: Adding Battery Level & Voltage for Node ID:", node_id, "Battery Voltage:",b_volt,"Battery Level:",payload,"\n\n"
				cur.execute('INSERT INTO nodes_battery(node_id, bat_voltage, bat_level) VALUES(%s,%s,%s)', (node_id, b_volt, payload))
				cur.execute('UPDATE nodes SET last_seen=now(), `sync`=0 WHERE node_id = %s', [node_id])
				con.commit()

				# ..::Step Eight::..
			# Add Boost Status Level to Database/Relay Last seen gets added here as well when ACK is set to 1 in messages_out table. 
			if (node_id != 0 and child_sensor_id != 255 and message_type == 1 and sub_type == 2):
			# print "2 insert: ", node_id, " , ", child_sensor_id, "payload", payload
				print "8. Adding Database Record: Node ID:",node_id," Child Sensor ID:", child_sensor_id, " PayLoad:", payload, "\n"
				xboost = "UPDATE boost SET status=%s WHERE boost_button_id=%s AND boost_button_child_id = %s"
				cur.execute(xboost, (payload, node_id,child_sensor_id,))
				con.commit()
				cur.execute('UPDATE `nodes` SET `last_seen`=now(), `sync`=0 WHERE node_id = %s', [node_id])
				con.commit()

				# ..::Step Nine::..
			# Add Away Status Level to Database 
			if (node_id != 0 and child_sensor_id != 255 and child_sensor_id == 4 and message_type == 1 and sub_type == 2):
			# print "2 insert: ", node_id, " , ", child_sensor_id, "payload", payload
				print "9. Adding Database Record: Node ID:", node_id, " Child Sensor ID:", child_sensor_id, " PayLoad:", payload, "\n"
				xaway = "UPDATE away SET status=%s WHERE away_button_id=%s AND away_button_child_id = %s"
				cur.execute(xaway, (payload, node_id,child_sensor_id,))
				con.commit()
				cur.execute('UPDATE `nodes` SET `last_seen`=now(), `sync`=0  WHERE node_id = %s', [node_id])
				con.commit()
			#else: 
				#print bc.WARN+ "No Action Defined Incomming Node Message Ignored \n\n" +bc.ENDC
			
			# ..::Step Ten::..
			# When Gateway Startup Completes
			if (node_id == 0 and child_sensor_id == 255 and message_type == 0 and sub_type == 18):
				print "10: PiHome MySensors Gateway Version :", payload, "\n\n"
				cur.execute('UPDATE gateway SET version = %s', [payload])
				con.commit()	

		except mdb.Error, e:
				print "Error %d: %s" % (e.args[0], e.args[1])
				sys.exit(1)
		finally:
			if con:
				con.close()
	time.sleep(1)