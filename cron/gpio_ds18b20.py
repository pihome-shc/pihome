#!/usr/bin/python
import time, os, fnmatch, MySQLdb as mdb, logging
class bc:
	hed = '\033[0;36;40m'
	dtm = '\033[0;36;40m'
	ENDC = '\033[0m'
	SUB = '\033[3;30;45m'
	WARN = '\033[0;31;40m'
	grn = '\033[0;32;40m'
	wht = '\033[0;37;40m'
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
print "***********************************************************"
print "*   PiHome DS18B20 Temperature Sensors Data to MySQL DB   *"
print "* Use this script if you have DS18B20 Temperature sensors *"
print "* Connected directly on Raspberry Pi GPIO.                *"
print "*                                  Build Date: 14/02/2018 *"
print "*                                    Have Fun - PiHome.eu *"
print "***********************************************************"
print " " + bc.ENDC
logging.basicConfig(filename='/var/www/cron/logs/DS18B20_error.log', level=logging.DEBUG, format='%(asctime)s %(levelname)s %(name)s %(message)s')
logger=logging.getLogger(__name__)

# Add in the w1_gpio and w1_therm modules
os.system('modprobe w1-gpio')
os.system('modprobe w1-therm')

print bc.dtm + time.ctime() + bc.ENDC + ' - DS Temperature Sensors Script Started'
print "-" * 68

#function for storing readings into MySql
def insertDB(IDs, temperature):
	try:
		con = mdb.connect('localhost', 'root', 'passw0rd', 'pihome');
		cursor = con.cursor()
		for i in range(0,len(temperature)):
			print bc.dtm + time.ctime() + bc.ENDC + ' - Sensors ID' + bc.grn, IDs[i], bc.ENDC + 'Temperature' + bc.grn, temperature[i], bc.ENDC
			sql = "INSERT INTO temp_reading(sensor_id, date, time, value) \
			VALUES ('%s', '%s', '%s', '%s' )" % \
			(IDs[i], time.strftime("%Y-%m-%d"), time.strftime("%H:%M"), temperature[i])
			cursor.execute(sql)
			sql = []
			con.commit()
		con.close()
	except mdb.Error, e:
		logger.error(e)

#get readings from sensors and store them to MySql
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
	