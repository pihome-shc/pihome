#!/usr/bin/python
import time, os, fnmatch, MySQLdb as mdb, logging
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
print("***********************************************************")
print("*   PiHome DS18B20 Temperature Sensors Data to MySQL DB   *")
print("* Use this script if you have DS18B20 Temperature sensors *")
print("* Connected directly on Raspberry Pi GPIO.                *")
print("*                                  Build Date: 14/02/2018 *")
print("*                                    Have Fun - PiHome.eu *")
print("***********************************************************")
print(" " + bc.ENDC)
logging.basicConfig(filename='/var/www/cron/logs/DS18B20_error.log', level=logging.DEBUG, format='%(asctime)s %(levelname)s %(name)s %(message)s')
logger=logging.getLogger(__name__)

#Add in the w1_gpio and w1_therm modules
os.system('modprobe w1-gpio')
os.system('modprobe w1-therm')

# Initialise the database access variables
config = configparser.ConfigParser()
config.read('/var/www/st_inc/db_config.ini')
dbhost = config.get('db', 'hostname')
dbuser = config.get('db', 'dbusername')
dbpass = config.get('db', 'dbpassword')
dbname = config.get('db', 'dbname')

null_value = None

print(bc.dtm + time.ctime() + bc.ENDC + ' - DS18B20 Temperature Sensors Script Started')
print("-" * 68)

#Function for Storing DS18B20 Temperature Readings into MySQL
def insertDB(IDs, temperature):
	try:
		con = mdb.connect(dbhost, dbuser, dbpass, dbname);
		cur = con.cursor()
		for i in range(0,len(temperature)):
			#Check if Sensors Already Exit in Nodes Table, if no then add Sensors into Nodes Table otherwise just update Temperature Readings.
			cur.execute('SELECT COUNT(*) FROM `nodes` where node_id = (%s)', [IDs[i]])
			row = cur.fetchone()
			row = int(row[0])
			if (row == 0):
				print(bc.dtm + time.ctime() + bc.ENDC + ' - New DS18B20 Sensors Discovered' + bc.grn, IDs[i], bc.ENDC)
				cur.execute('INSERT INTO nodes(`sync`, `purge`, `type`, `node_id`, `max_child_id`, `name`, `last_seen`, `notice_interval`, `min_voltage`, `status`, `ms_version`, `sketch_version`, `repeater`) VALUES(%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)', (0, 0, 'GPIOSensor', IDs[i], '0', 'Temperature Sensor', time.strftime("%Y-%m-%d %H:%M:%S"), 0, 0, 'Active', 0, 0, 0))
				con.commit()
			#If DS18B20 Sensor record exist: Update Nodes Table with Last seen status.
			if (row == 1):
				cur.execute('UPDATE `nodes` SET `last_seen`=now() WHERE node_id = %s', [IDs[i]])
				con.commit()
			print(bc.dtm + time.ctime() + bc.ENDC + ' - Sensors ID' + bc.grn, IDs[i], bc.ENDC + 'Temperature' + bc.grn, temperature[i], bc.ENDC)
			cur.execute('INSERT INTO messages_in(`sync`, `purge`, `node_id`, `child_id`, `sub_type`, `payload`, `datetime`) VALUES(%s,%s,%s,%s,%s,%s,%s)', (0, 0, IDs[i], 0, 0, round(temperature[i],2), time.strftime("%Y-%m-%d %H:%M:%S")))
			con.commit()
		con.close()
	except mdb.Error as e:
		logger.error(e)
		print(bc.dtm + time.ctime() + bc.ENDC + ' - DB Connection Closed: %s' % e)

#Read DS18B20 Sensors and Save Them to MySQL
while True:
	temperature = []
	IDs = []
	for filename in os.listdir("/sys/bus/w1/devices"):
		if fnmatch.fnmatch(filename, '28-*'):
			with open("/sys/bus/w1/devices/" + filename + "/w1_slave") as fileobj:
				lines = fileobj.readlines()
				#print lines
				if lines[0].find("YES"):
					pok = lines[1].find('=')
					temperature.append(float(lines[1][pok+1:pok+6])/1000)
					IDs.append(filename)
				else:
					logger.error("Error reading sensor with ID: %s" % (filename))
	if (len(temperature)>0):
		insertDB(IDs, temperature)
		#time.sleep(300)
		break

