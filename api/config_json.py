import json
import MySQLdb as mdb
import ConfigParser
import time
import collections

# Initialise the database access variables
config = ConfigParser.ConfigParser()
config.read('/var/www/st_inc/db_config.ini')
dbhost = config.get('db', 'hostname')
dbuser = config.get('db', 'dbusername')
dbpass = config.get('db', 'dbpassword')
dbname = config.get('db', 'dbname')

# Read existing config.json in to a python dictionary
src = "./config.json"
with open(src, "r") as read_file:
 	config = json.load(read_file)

# If the 'accessories' key exist in dictionary then delete it using del.
if "accessories" in config:
   	del config['accessories']

# get zone names from the database
con = mdb.connect(dbhost, dbuser, dbpass, dbname)
cur = con.cursor()
cur.execute("SELECT * FROM zone WHERE status  = 1 OR graph_it = 1")
result = cur.fetchall()
cur.close()
con.close()

# Fill list using template for each active zone
zonelist =[]
for row in result:
	d = collections.OrderedDict()
	sub_d = collections.OrderedDict()
	sub_d['url'] = 'http://127.0.0.1/api/getTemperature?zonename=' + row[5]
	sub_d['method'] = 'GET'
	d['accessory'] = 'HTTP-TEMPERATURE'
        d['name'] = row[5]
        d['pullInterval'] = '5000'
	d['getUrl'] = sub_d 
	zonelist.append(d)

for row in result:
	if row[3] == 1:
		d = collections.OrderedDict()
		sub_d = collections.OrderedDict()
	        sub_d['url'] = 'http://127.0.0.1/api/switchOn?zonename=' + row[5]
        	sub_d['method'] = 'GET'
        	d['accessory'] = 'HTTP-SWITCH'
	        d['name'] = row[5]
                d['switchType'] = 'stateful'
	        d['pullInterval'] = '5000'
	        d['onUrl'] = sub_d
                sub_d['url'] = 'http://127.0.0.1/api/switchOff?zonename=' + row[5]
                d['offUrl'] = sub_d
                sub_d['url'] = 'http://127.0.0.1/api/switchStatus?zonename=' + row[5]
                d['statusUrl'] = sub_d
	        zonelist.append(d)

# Add list to dictionary
config["accessories"] = zonelist

# Write updated config.json file
with open(src, "w") as write_file:
	json.dump(config, write_file, indent = 4)

