import json
import MySQLdb as mdb
import configparser
import time
import collections

# Initialise the database access variables
config = configparser.ConfigParser()
config.read('/var/www/st_inc/db_config.ini')
dbhost = config.get('db', 'hostname')
dbuser = config.get('db', 'dbusername')
dbpass = config.get('db', 'dbpassword')
dbname = config.get('db', 'dbname')

# Read existing config.json in to a python dictionary
src = "/var/lib/homebridge/config.json"
with open(src, "r") as read_file:
        config = json.load(read_file)

# get zone names from the database
con = mdb.connect(dbhost, dbuser, dbpass, dbname)
cur = con.cursor()
cur.execute("SELECT * FROM zone_view WHERE status  = 1 or graph_it = 1")
result = cur.fetchall()
cur.close()
con.close()

# Fill list using template for each active zone
zonelist =[]
d = collections.OrderedDict()
d['platform'] = 'config'
d['name'] = 'Config'
d['port'] = '8581'
zonelist.append(d)

d = collections.OrderedDict()
d['platform'] = 'HttpWebHooks'
d['webhook_port'] = '51828'
d['cache_directory'] = './.node-persist/storage'
d['https'] = False

# Add switches for active zone controllers
switches = []
for row in result:
        if row[0] == 1:
                sub_d = collections.OrderedDict()
                sub_d['id'] = 'switch' + str(row[3])
                sub_d['name'] = row[5] + ' Zone'
                sub_d['on_url'] = 'http://127.0.0.1/api/boostSet?zonename=' + row[5] + '&state=1'
                sub_d['on_method'] = 'GET'
                sub_d['off_url'] = 'http://127.0.0.1/api/boostSet?zonename=' + row[5] + '&state=0'
                sub_d['off_method'] = 'GET'
                switches.append(sub_d)
d['switches'] = switches

# Add sensors for zones with graph_it set
sensors = []
for row in result:
        sub_d = collections.OrderedDict()
        sub_d['id'] = 'sensor' + str(row[3])
        sub_d['name'] = row[5] + ' Temperature'
        sub_d['type'] = 'temperature'
        sensors.append(sub_d)
d['sensors'] = sensors
zonelist.append(d)

# Add list to dictionary
config["platforms"] = zonelist

# If the 'accessories' key exist in dictionary then delete it using del.
if "accessories" in config:
        del config['accessories']
# Add empty accessories block
config["accessories"] = []

# Write updated config.json file
with open(src, "w") as write_file:
        json.dump(config, write_file, indent = 4)

