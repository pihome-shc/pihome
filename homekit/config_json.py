import json
import MySQLdb as mdb
import ConfigParser
import time, shutil

# Initialise the database access variables
config = ConfigParser.ConfigParser()
config.read('/var/www/st_inc/db_config.ini')
dbhost = config.get('db', 'hostname')
dbuser = config.get('db', 'dbusername')
dbpass = config.get('db', 'dbpassword')
dbname = config.get('db', 'dbname')

# Read existing config.json in to a python dictionary
src = "/var/lib/homebridge/config.json"
with open(src, "r") as read_file:
 	config = json.load(read_file)

# If the 'accessories' key exist in dictionary then delete it using del.
if "accessories" in config:
   	del config['accessories']

# get zone names from the database
con = mdb.connect(dbhost, dbuser, dbpass, dbname)
cur = con.cursor()
cur.execute("SELECT * FROM zone WHERE status  = 1")
result = cur.fetchall()
cur.close()
con.close()

# Fill list using template for each active zone
zonelist =[]
for row in result:
	dict = {
       		"accessory": "HTTP-SWITCH",
       		"name": row[5],
       		"switchType": "stateful",
       		"pullInterval": "1000",
       		"onUrl": {
              		"url": "http://localhost:8081/api/switchOn?zonename=" + row[5],
               		"method": "GET"
       		},
       		"offUrl": {
               		"url": "http://localhost:8081/api/switchOff?zonename=" + row[5],
               		"method": "GET"
       		},
       		"statusUrl": {
               		"url": "http://localhost:8081/api/switchStatus?zonename=" + row[5],
               		"method": "GET"
       		}
	}
	zonelist.append(dict)

# Add list to dictionary
config["accessories"] = zonelist

# Create a backup of the existing config.json
timestr = time.strftime("%Y%m%d-%H%M%S")
shutil.copy(src, src + "_" + timestr)

# Write updated config.json file
with open(src, "w") as write_file:
	json.dump(config, write_file, indent = 4)

