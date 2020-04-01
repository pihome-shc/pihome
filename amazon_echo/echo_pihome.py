""" Based on fauxmo_minimal.py - Fabricate.IO

    This is a demo python file showing what can be done with the debounce_handler.
    The handler prints True when you say "Alexa, device on" and False when you say
    "Alexa, device off".

    If you have two or more Echos, it only handles the one that hears you more clearly.
    You can have an Echo per room and not worry about your handlers triggering for
    those other rooms.

    The IP of the triggering Echo is also passed into the act() function, so you can
    do different things based on which Echo triggered the handler.
"""

import fauxmo
import logging
import time
import MySQLdb as mdb
import ConfigParser

from debounce_handler import debounce_handler

logging.basicConfig(level=logging.DEBUG)

# Initialise the database access varables
config = ConfigParser.ConfigParser()
config.read('/var/www/st_inc/db_config.ini')
dbhost = config.get('db', 'hostname')
dbuser = config.get('db', 'dbusername')
dbpass = config.get('db', 'dbpassword')
dbname = config.get('db', 'dbname')

class device_handler(debounce_handler):
    """Publishes the on/off state requested,
       and the IP address of the Echo making the request.
    """
    # Find the active zone names and create the Amazon Echo device name and allocate it a port number
    con = mdb.connect(dbhost, dbuser, dbpass, dbname)
    cur = con.cursor()
    cur.execute("SELECT * FROM zone WHERE status  = 1")
    result = cur.fetchall()
    cur.close()
    zone_name = []
    con.close()
    TRIGGERS = {}
    port = 52000
    # Build a python list of zone names
    for row in result:
        zone_name.append(row[5].strip('Ch. '))
    # Build a python dictionary of zone names and allocate sequential port numbers
    for x in range(0, len(zone_name)) :
        TRIGGERS[zone_name[x]] = port + x

    def act(self, client_address, state, name):
        print "State", state, "on ", name, "from client @", client_address
        # Turn on or off the Amazon Echo device name, which is used to set the Boost status for the zone by name
	if(state) :
		boost = 1
	else :
		boost = 0
        # Get id numbers for the boost table
        con = mdb.connect(dbhost, dbuser, dbpass, dbname)
	cur = con.cursor()
        query = """SELECT * FROM boost_view WHERE name LIKE '%s' Limit 1""" % ('%'+name)
        cur.execute(query)
        row = cur.fetchone();
        boost_id = row[0]
	# Set the selected zone boost ON or OFF
	cur.execute('UPDATE boost SET status=%s WHERE id=%s', (boost,boost_id))
	cur.close()
	con.commit()
        con.close()
        return True

    def status(self, client_address, name):
        con = mdb.connect(dbhost, dbuser, dbpass, dbname)
        cur = con.cursor()
        query = """SELECT * FROM boost_view WHERE name LIKE '%s' Limit 1""" % ('%'+name)
        cur.execute(query)
        row = cur.fetchone();
	cur.close()
        con.close()
        return row[1]

if __name__ == "__main__":
    # Startup the fauxmo server
    fauxmo.DEBUG = True
    p = fauxmo.poller()
    u = fauxmo.upnp_broadcast_responder()
    u.init_socket()
    p.add(u)

    # Register the device callback as a fauxmo handler
    d = device_handler()
    for trig, port in d.TRIGGERS.items():
        fauxmo.fauxmo(trig, u, p, None, port, d)

    # Loop and poll for incoming Echo requests
    logging.debug("Entering fauxmo polling loop")
    while True:
        try:
            # Allow time for a ctrl-c to stop the process
            p.poll(100)
            time.sleep(0.1)
        except Exception, e:
            logging.critical("Critical exception: " + str(e))
            break
