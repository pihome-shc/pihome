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

# get zone names from the database
con = mdb.connect(dbhost, dbuser, dbpass, dbname)
cur = con.cursor()
cur.execute("SELECT * FROM zone_view WHERE status  = 1")
result = cur.fetchall()
cur.close()
con.close()

src = "/etc/fauxmo/config.json"
port = 12340
switches = []
for row in result:
        if row[0] == 1:
                sub_d = collections.OrderedDict()
                sub_d['port'] = port
                sub_d['on_cmd'] = 'http://127.0.0.1/api/boostSet?zonename=' + row[4].replace(" ", "%20") + '&state=1'
                sub_d['off_cmd'] = 'http://127.0.0.1/api/boostSet?zonename=' + row[4].replace(" ", "%20") + '&state=0'
                sub_d['state_cmd'] = 'http://127.0.0.1/api/boostSet?zonename=' + row[4].replace(" ", "%20")
                sub_d['method'] = 'GET'
                sub_d['name'] = row[4]
                sub_d['state_response_on'] = 'on'
                sub_d['state_response_off'] = 'off'
                switches.append(sub_d)
                port = port + 1

config = {
    "FAUXMO": {
        "ip_address": "auto"
    },
    "PLUGINS": {
        "SimpleHTTPPlugin": {
                "DEVICES": switches
        }
    }
}

with open(src, "w") as write_file:
        json.dump(config, write_file, indent = 4)

