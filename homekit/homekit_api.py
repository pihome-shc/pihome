from flask import Flask, request, jsonify
import MySQLdb as mdb, sys, serial, telnetlib, time, datetime
import ConfigParser, logging

# Debug print to screen configuration
dbgLevel = 3    # 0-off, 1-info, 2-detailed, 3-all
dbgMsgOut = 1   # 0-disabled, 1-enabled, show details of outgoing messages
dbgMsgIn = 1    # 0-disabled, 1-enabled, show details of incoming messages

# Logging exceptions to log file
logfile = '/var/www/logs/main.log'
infomsg = 'More info in log file: '+logfile
logging.basicConfig( filename=logfile,
                     level=logging.DEBUG,
                     format= ('\n### %(asctime)s - %(levelname)s ###')
                   )

null_value = None

try:
        # Initialise the database access variables
        config = ConfigParser.ConfigParser()
        config.read('/var/www/st_inc/db_config.ini')
        dbhost = config.get('db', 'hostname')
        dbuser = config.get('db', 'dbusername')
        dbpass = config.get('db', 'dbpassword')
        dbname = config.get('db', 'dbname')

        def getBoost():
                zonename = request.args.get('zonename')
		if len(zonename) > 0 :
	                con = mdb.connect(dbhost, dbuser, dbpass, dbname)
        	        cur = con.cursor()
                	cur.execute('SELECT * FROM boost_view where name = (%s) limit 1', (zonename, ))
	                zrow = cur.fetchone()
        	        cur.close()
                	con.close()
			if zrow :
	                	return zrow[1]
			else :
                        	return False
		else :
			return False

        def setBoost(state):
                zonename = request.args.get('zonename')
                if len(zonename) > 0 :
	                con = mdb.connect(dbhost, dbuser, dbpass, dbname)
        	        cur = con.cursor()
                	cur.execute('SELECT * FROM boost_view where name = (%s) limit 1', (zonename, ))
	                zrow = cur.fetchone()
			if zrow :
        	        	cur.execute('UPDATE boost SET status = %s where id = %s', (state, zrow[0]))
                		con.commit()
	                	cur.close()
        	        	con.close()
                		return True
			else :
				return False
                else :
                        return False

        app = Flask(__name__)

        @app.route("/api/switchOn", methods=["GET"])
        def on():
                status = setBoost(1)
                return jsonify(status)

        @app.route("/api/switchOff", methods=["GET"])
        def off():
                status = setBoost(0)
                return jsonify(status)

        @app.route("/api/switchStatus", methods=["GET"])
        def status():
                status = getBoost()
                return jsonify(status)

        if __name__ == '__main__':
                app.run(port=8081)

        time.sleep(0.1)

except ConfigParser.Error as e:
        print "ConfigParser:",format(e)
        con.close()
except mdb.Error, e:
        print "DB Error %d: %s" % (e.args[0], e.args[1])
        con.close()
except serial.SerialException as e:
        print "SerialException:",format(e)
        con.close()
except EOFError as e:
        print "EOFError:",format(e)
        con.close()
except Exception as e:
        print format(e)
        con.close()
finally:
        print infomsg
        logging.exception(e)
        sys.exit(1)

