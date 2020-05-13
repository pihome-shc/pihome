#!/usr/bin/env python
import socket, re, time, MySQLdb as mdb
from ping import *
import configparser
class bc:
	hed = '\033[0;36;40m'
	dtm = '\033[0;36;40m'
	ENDC = '\033[0m'
	SUB = '\033[3;30;45m'
	WARN = '\033[0;31;40m'
	grn = '\033[0;32;40m'
	wht = '\033[0;37;40m'
	ylw = '\033[93m'
os.system('clear')
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
print("***********************************************************")
print("*   PiHome Search for MySensors Gateay IP & Save it in    *")
print("*              MySQL Database Gateway Table               *")
print("*                                  Build Date: 06/09/2018 *")
print("*                                    Have Fun - PiHome.eu *")
print("***********************************************************")
print(" " + bc.ENDC)

print(bc.dtm + time.ctime() + bc.ENDC + ' - PiHome Gateway Search Script Started')

s = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
s.connect(('google.com', 0))
ip = s.getsockname()[0]

# Initialise the database access varables
config = configparser.ConfigParser()
config.read('/var/www/st_inc/db_config.ini')
dbhost = config.get('db', 'hostname')
dbuser = config.get('db', 'dbusername')
dbpass = config.get('db', 'dbpassword')
dbname = config.get('db', 'dbname')

#Port Number defined for MySensors Gateway
port = 5003

#Get the Local IP
end = re.search('^[\d]{1,3}.[\d]{1,3}.[\d]{1,3}.[\d]{1,3}', ip)

#Chop down the last IP Digits
create_ip = re.search('^[\d]{1,3}.[\d]{1,3}.[\d]{1,3}.', ip)

#Print IP to the user
print(bc.dtm + time.ctime() + bc.ENDC + ' - PiHome IP Address: '+bc.grn+str(end.group(0))+bc.ENDC)
print("-" * 65)

#Pinging the IP and Checking if Port is open
def ping(ip):
	if verbose_ping(ip) == True:
		print(bc.dtm + time.ctime() + bc.ENDC + ' - Found IP Alive: '+bc.ylw + ip + bc.ENDC)
		print(bc.dtm + time.ctime() + bc.ENDC + ' - Scanning Port: '+bc.ylw + (str(port)) + bc.ENDC)
		s = socket.socket()
		result = s.connect_ex((ip, port))
		if result == 0:
			print(bc.dtm + time.ctime() + bc.ENDC + ' - IP Address: '+bc.ylw + ip + bc.ENDC+' Listening on Port: ' + bc.ylw+(str(port))+ bc.ENDC)
			print(bc.dtm + time.ctime() + bc.ENDC + ' - Search for PiHome Gateway IP Address is Finished')
			con = mdb.connect(dbhost, dbuser, dbpass, dbname)
			cur = con.cursor()
			cur.execute('SELECT COUNT(*) FROM `gateway` where status = 1')
			row = cur.fetchone()  
			row = int(row[0])
			if (row == 0):
				print(bc.dtm + time.ctime() + bc.ENDC + ' - Adding Gateway Record to Database \n\n')
				cur.execute('INSERT INTO `gateway` (location, reboot, find_gw) VALUES(%s, %s, %s)', (ip, 1, 0))
				con.commit()
			else:
				print(bc.dtm + time.ctime() + bc.ENDC + ' - Updating Gateway Record to Database \n\n')
				cur.execute('UPDATE gateway SET sync = %s, location = %s, reboot = %s, find_gw = %s where id = %s', (0, ip, 1, 0, 1))
				con.commit()
			print("-" * 65)	
			s.close()
			exit()
		else:
			print(bc.dtm + time.ctime() + bc.ENDC + ' - Scanning for Next Alive IP Address')

#Check IP 
def CheckLoopBack(ip):
	if (end.group(0) == '127.0.0.1'):
		return True
		print(bc.dtm + time.ctime() + bc.ENDC + ' - Either your IP is a Loop Back or it does not belong in local IP range')

print(bc.dtm + time.ctime() + bc.ENDC + ' - Pinging Local Network IP Range ')

if(CheckLoopBack(create_ip)):
	print(bc.dtm + time.ctime() + bc.ENDC + ' - Either your IP is a Loop Back or it does not belong in local IP range')
else:
	for i in range(1, 254):
		ping(str(create_ip.group(0)) + str(i))
