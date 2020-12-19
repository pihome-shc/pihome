#!/usr/bin/python3
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
    blu = '\033[36m'


print(bc.hed + " ")
print("  _____    _   _    _                            ")
print(" |  __ \  (_) | |  | |                           ")
print(" | |__) |  _  | |__| |   ___    _ __ ___     ___ ")
print(" |  ___/  | | |  __  |  / _ \  | |_  \_ \   / _ \ ")
print(" | |      | | | |  | | | (_) | | | | | | | |  __/")
print(" |_|      |_| |_|  |_|  \___/  |_| |_| |_|  \___|")
print(" ")
print("    S M A R T   H E A T I N G   C O N T R O L ")
print("********************************************************")
print("*          Script to send status Email messages        *")
print("*                Build Date: 19/06/2019                *")
print("*      Version 0.04 - Last Modified 12/05/2020         *")
print("*                                 Have Fun - PiHome.eu *")
print("********************************************************")
print(" ")
print(" " + bc.ENDC)

import MySQLdb as mdb, datetime, sys, smtplib, string
import configparser

# Initialise the database access varables
config = configparser.ConfigParser()
config.read('/var/www/st_inc/db_config.ini')
dbhost = config.get('db', 'hostname')
dbuser = config.get('db', 'dbusername')
dbpass = config.get('db', 'dbpassword')
dbname = config.get('db', 'dbname')

# Create the container (outer) email message.
try:
    con = mdb.connect(dbhost, dbuser, dbpass, dbname)
    cursorselect = con.cursor()
    query = ("SELECT * FROM email;")
    cursorselect.execute(query)
    name_to_index = dict(
        (d[0], i)
        for i, d
        in enumerate(cursorselect.description)
    )
    results = cursorselect.fetchone()
    cursorselect.close()
    if cursorselect.rowcount > 0:
        USER = results[name_to_index['username']]
        PASS = results[name_to_index['password']]
        HOST = results[name_to_index['smtp']]
        TO = results[name_to_index['to']]
        FROM = results[name_to_index['from']]
        SUBJECT = "PiHome Status"
        MESSAGE = ""
        send_status = results[name_to_index['status']]
    else:
        print("Error - No Email Account Found in Database.")
        sys.exit(1)

except mdb.Error as e:
    print("Error %d: %s" % (e.args[0], e.args[1]))
    sys.exit(1)
finally:
    if con:
        con.close()

print(bc.blu + (datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - Notice Script Started")
print("------------------------------------------------------------------")
# Check Gateway Logs for last 10 minuts and start search for gateway connected failed.
print(bc.blu + (datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - Checking Gateway Communication")

try:
    con = mdb.connect(dbhost, dbuser, dbpass, dbname)
    cur = con.cursor()
    query = ('SELECT COUNT(*) FROM gateway_logs WHERE pid_datetime >= DATE_SUB(NOW(),INTERVAL 10 MINUTE);')
    cur.execute(query)
    count = cur.fetchone()  # Grab all messages from database for gateway_logs.
    count = count[
        0]  # Parse first and the only one part of data table named "count" - there is number of records grabbed in SELECT above
    if count > 9:  # If greater then 10 then we have something to send out.
        message = "Gateway Connection Lost in Last 10 minutes: " + str(count) + "\n"
        query = ("SELECT * FROM notice WHERE message = '" + message + "'")
        cursorsel = con.cursor()
        cursorsel.execute(query)
        name_to_index = dict(
            (d[0], i)
            for i, d
            in enumerate(cursorsel.description)
        )
        messages = cursorsel.fetchone()  # Grab all notices with the same message content.
        cursorsel.close()
        cursorupdate = con.cursor()
        if cursorsel.rowcount > 0:
            if messages[name_to_index['status']] == 1:
                cursorupdate.execute("UPDATE notice SET status = '0'")
        else:
            cursorupdate.execute('INSERT INTO notice (sync, `purge`, datetime, message, status) VALUES(%s,%s,%s,%s,%s)',
                                 (0, 0, datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S"), message, 1))
            print(bc.blu + (datetime.datetime.now().strftime(
                "%Y-%m-%d %H:%M:%S")) + bc.wht + " - Gateway Connection Lost in Last 10 minutes: " + str(count))

        cursorupdate.close()
        con.commit()
    elif count == 0:  # no gateway errors in the last hour so clear any existing messages to allow new ones
        query = ("DELETE FROM notice WHERE message LIKE 'Gateway Connection Lost in Last 10 minutes:%'")
        cursordelete = con.cursor()
        cursordelete.execute(query)
        cursordelete.close()
        con.commit()

except mdb.Error as e:
    print("Error %d: %s" % (e.args[0], e.args[1]))
    sys.exit(1)
finally:
    if con:
        con.close()
print(bc.blu + (datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - Gateway Notice Finished")
print("------------------------------------------------------------------")

# *************************************************************************************************************
# Active Nodes Last Seen status and Battery Level
print(bc.blu + (datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - Checking Node Communication")
try:
    con = mdb.connect(dbhost, dbuser, dbpass, dbname)
    cursorselect = con.cursor()
    query = ("SELECT * FROM nodes WHERE status = 'Active';")
    cursorselect.execute(query)
    node_to_index = dict(
        (d[0], i)
        for i, d
        in enumerate(cursorselect.description)
    )
    results = cursorselect.fetchall()
    cursorselect.close()
    if cursorselect.rowcount > 0:  # Some Active Nodes
        for i in results:  # loop through active nodes
            node_id = i[node_to_index['node_id']]
            name = i[node_to_index['name']]
            last_seen = i[node_to_index['last_seen']]
            notice_interval = i[node_to_index['notice_interval']]
            min_value = i[node_to_index['min_value']]
            timeDifference = (datetime.datetime.now() - last_seen)
            time_difference_in_minutes = (timeDifference.days * 24 * 60) + (timeDifference.seconds / 60)
            message = name + " " + node_id + " last reported on " + str(last_seen)
            # select any records in the notice table which match the current message
            query = ("SELECT * FROM notice WHERE message = '" + message + "'")
            cursorsel = con.cursor()
            cursorsel.execute(query)
            name_to_index = dict(
                (d[0], i)
                for i, d
                in enumerate(cursorsel.description)
            )
            messages = cursorsel.fetchone()
            cursorsel.close()
            if time_difference_in_minutes >= notice_interval:  # Active Sensor found which has not reported in the last test interval
                cursorupdate = con.cursor()
                if cursorsel.rowcount > 0:  # This message already exists
                    if messages[name_to_index['status']] == 1:  # This node has already sent an email with this content
                        cursorupdate.execute("UPDATE notice SET status = '0'")  # so clear status to stop further emails
                else:  # new notification so add a new message to the notification table
                    cursorupdate.execute(
                        'INSERT INTO notice (sync, `purge`, datetime, message, status) VALUES(%s,%s,%s,%s,%s)',
                        (0, 0, datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S"), message, 1))
                    print(bc.blu + (
                        datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - " + name + " - " + str(
                        notice_interval) + " Minutes Ago.")

                cursorupdate.close()
                con.commit()
            else:  # node has now reported so delete any 'notice' records
                query = "DELETE FROM notice WHERE message LIKE '" + name + " " + str(node_id) + "%'"
                cursordelete = con.cursor()
                cursordelete.execute(query)
                cursordelete.close()
                con.commit()

            if min_value is not None:  # This is a Battery Powered Node, so check battery status
                print(bc.blu + (datetime.datetime.now().strftime(
                    "%Y-%m-%d %H:%M:%S")) + bc.wht + " - Checking Battery Node - " + node_id + " Communication")
                try:
                    cnx = mdb.connect(dbhost, dbuser, dbpass, dbname)
                    cursorselect = cnx.cursor()
                    query = (
                            "SELECT * FROM `nodes_battery` WHERE `node_id` = " + node_id + " ORDER BY `update` DESC LIMIT 1;")
                    cursorselect.execute(query)
                    bat_node_to_index = dict(
                        (d[0], i)
                        for i, d
                        in enumerate(cursorselect.description)
                    )
                    results = cursorselect.fetchone()
                    cursorselect.close()
                    if cursorselect.rowcount > 0:  # Battery Record Found
                        update = results[bat_node_to_index['update']]
                        if results[bat_node_to_index['bat_level']] is None :
                            bat_level = 0
                        else :
                            bat_level = int(results[bat_node_to_index['bat_level']])
                        timeDifference = (datetime.datetime.now() - update)
                        time_difference_in_minutes = (timeDifference.days * 24 * 60) + (timeDifference.seconds / 60)
                        message = "Battery Node " + node_id + " last reported on " + str(update)
                        # select any records in the notice table which match the current message
                        query = ("SELECT * FROM notice WHERE message = '" + message + "'")
                        cursorsel = con.cursor()
                        cursorsel.execute(query)
                        name_to_index = dict(
                            (d[0], i)
                            for i, d
                            in enumerate(cursorsel.description)
                        )
                        messages = cursorsel.fetchone()
                        cursorsel.close()
                        if time_difference_in_minutes >= notice_interval:  # Active Sensor found which has not reported in the last test interval
                            cursorupdate = con.cursor()
                            if cursorsel.rowcount > 0:  # This message already exists
                                if messages[name_to_index[
                                    'status']] == 1:  # This node has already sent an email with this content
                                    cursorupdate.execute(
                                        "UPDATE notice SET status = '0'")  # so clear status to stop further emails
                            else:  # new notification so add a new message to the notification table
                                cursorupdate.execute(
                                    'INSERT INTO notice (sync, `purge`, datetime, message, status) VALUES(%s,%s,%s,%s,%s)',
                                    (0, 0, datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S"), message, 1))
                                print(bc.blu + (datetime.datetime.now().strftime(
                                    "%Y-%m-%d %H:%M:%S")) + bc.wht + " - Battery Node - " + node_id + " Reported - " + str(
                                    2) + " Minutes Ago.")

                            cursorupdate.close()
                            con.commit()
                        else:  # node has now reported so delete any 'notice' records
                            query = "DELETE FROM notice WHERE message LIKE 'Battery Node " + str(
                                node_id) + " last reported on%'"
                            cursordelete = con.cursor()
                            cursordelete.execute(query)
                            cursordelete.close()
                            con.commit()

                        # Check latest Battery Level
                        # ----------------------------
                        message = "Battery Node " + node_id + " Level < " + str(min_value) + " %"
                        # select any records in the notice table which match the current message
                        query = ("SELECT * FROM notice WHERE message = '" + message + "'")
                        cursorsel = con.cursor()
                        cursorsel.execute(query)
                        name_to_index = dict(
                            (d[0], i)
                            for i, d
                            in enumerate(cursorsel.description)
                        )
                        messages = cursorsel.fetchone()
                        cursorsel.close()
                        if bat_level < min_value:  # Active Sensor found where level is less than minimum
                            cursorupdate = con.cursor()
                            if cursorsel.rowcount > 0:  # This message already exists
                                if messages[name_to_index[
                                    'status']] == 1:  # This node has already sent an email with this content
                                    cursorupdate.execute(
                                        "UPDATE notice SET status = '0'")  # so clear status to stop further emails
                            else:  # new notification so add a new message to the notification table
                                cursorupdate.execute(
                                    'INSERT INTO notice (sync, `purge`, datetime, message, status) VALUES(%s,%s,%s,%s,%s)',
                                    (0, 0, datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S"), message, 1))
                                print(bc.blu + (datetime.datetime.now().strftime(
                                    "%Y-%m-%d %H:%M:%S")) + bc.wht + " - Battery Node - " + node_id + " Level < " + str(
                                    min_value) + " %.")

                            cursorupdate.close()
                            con.commit()
                        elif bat_level > min_value:  # node has now reported a sulitable level so delete any 'notice' records
                            query = "DELETE FROM notice WHERE message LIKE 'Battery Node " + str(node_id) + " Level <%'"
                            cursordelete = con.cursor()
                            cursordelete.execute(query)
                            cursordelete.close()
                            con.commit()

                        # Delete any No level found notices
                        query = "DELETE FROM notice WHERE message LIKE 'Battery Node " + str(
                            node_id) + " No Level Records%'"
                        cursordelete = con.cursor()
                        cursordelete.execute(query)
                        cursordelete.close()
                        con.commit()

                    else:  # no battery records found
                        message = "Battery Node " + node_id + " No Level Records Found"
                        # select any records in the notice table which match the current message
                        query = ("SELECT * FROM notice WHERE message = '" + message + "'")
                        cursorsel = con.cursor()
                        cursorsel.execute(query)
                        name_to_index = dict(
                            (d[0], i)
                            for i, d
                            in enumerate(cursorsel.description)
                        )
                        messages = cursorsel.fetchone()
                        cursorsel.close()
                        cursorupdate = con.cursor()
                        if cursorsel.rowcount > 0:  # This message already exists
                            if messages[
                                name_to_index['status']] == 1:  # This node has already sent an email with this content
                                cursorupdate.execute(
                                    "UPDATE notice SET status = '0'")  # so clear status to stop further emails
                        else:  # new notification so add a new message to the notification table
                            cursorupdate.execute(
                                'INSERT INTO notice (sync, `purge`, datetime, message, status) VALUES(%s,%s,%s,%s,%s)',
                                (0, 0, datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S"), message, 1))
                            print(bc.blu + (datetime.datetime.now().strftime(
                                "%Y-%m-%d %H:%M:%S")) + bc.wht + " - Battery Node - " + node_id + " No Level Records Found.")

                        cursorupdate.close()
                        con.commit()

                except mdb.Error as e:
                    print("Error %d: %s" % (e.args[0], e.args[1]))
                    sys.exit(1)
                finally:
                    if cnx:
                        cnx.close()

                print(bc.blu + (
                    datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - Battery Node Check Finished")

except mdb.Error as e:
    print("Error %d: %s" % (e.args[0], e.args[1]))
    sys.exit(1)
finally:
    if con:
        con.close()

print(bc.blu + (datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - Active Node Check Finished")
print("------------------------------------------------------------------")

# *************************************************************************************************************
# Check CPU Temperature from last one hour if it was over 50c
print(bc.blu + (datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - Checking CPU Temperature")

try:
    con = mdb.connect(dbhost, dbuser, dbpass, dbname)
    cursorselect = con.cursor()
    cursorselect.execute(
        "SELECT COUNT(*) FROM messages_in_view_24h WHERE node_id = '0' AND payload > 50 AND DATETIME >= DATE_SUB(NOW(),INTERVAL 60 MINUTE);")
    count = cursorselect.fetchone()  # Grab all messages from database for CPU temperature.
    count = count[
        0]  # Parse first and the only one part of data table named "count" - there is number of records grabbed in SELECT above
    if count > 0:  # If greater then 0 then we have something to send out.
        message = "Over 50c CPU Temperature Recorded in last one Hour"
        cursorsel = con.cursor()
        cursorsel.execute(query)
        name_to_index = dict(
            (d[0], i)
            for i, d
            in enumerate(cursorsel.description)
        )
        messages = cursorsel.fetchone()  # Grab all notices with the same message content.
        cursorsel.close()
        cursorupdate = con.cursor()
        if cursorsel.rowcount > 0:
            if messages[name_to_index['status']] == 1:
                cursorupdate.execute("UPDATE notice SET status = '0'")
            else:
                cursorupdate.execute(
                    'INSERT INTO notice (sync, `purge`, datetime, message, status) VALUES(%s,%s,%s,%s,%s)',
                    (0, 0, datetime.datetime.now().strftime("%Y-%m-%d$-%m-%d %H:%M:%S"), message, 1))
            print(bc.blu + (
                datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - CPU Temperature is very high")

        cursorupdate.close()
        con.commit()
    elif count == 0:  # no CPU temperature errors in the last hour so clear any existing messages to allow new ones
        query = ("DELETE FROM notice WHERE message LIKE 'Over 50c CPU Temperature Recorded in last one Hour'")
        cursordelete = con.cursor()
        cursordelete.execute(query)
        cursordelete.close()
        con.commit()

except mdb.Error as e:
    print("Error %d: %s" % (e.args[0], e.args[1]))
    sys.exit(1)
finally:
    if con:
        con.close()

print(bc.blu + (datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - CPU Temperature Check Finished")
print("------------------------------------------------------------------")

# Send Email Message
if send_status:
    try:
        con = mdb.connect(dbhost, dbuser, dbpass, dbname)
        cursorselect = con.cursor()
        query = ("SELECT * FROM notice WHERE status = 1;")
        cursorselect.execute(query)
        name_to_index = dict(
            (d[0], i)
            for i, d
            in enumerate(cursorselect.description)
        )
        results = cursorselect.fetchall()
        cursorselect.close()
        if cursorselect.rowcount > 0:
            for i in results:
                MESSAGE = MESSAGE + i[name_to_index['message']] + "\n"

    except mdb.Error as e:
        print("Error %d: %s" % (e.args[0], e.args[1]))
        sys.exit(1)
    finally:
        if con:
            con.close()

    if len(MESSAGE) > 0:
        print(bc.blu + (datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - Sending Email Message")
        BODY = "\r\n".join((
            "From: %s" % FROM,
            "To: %s" % TO,
            "Subject: %s" % SUBJECT,
            "",
            MESSAGE
            ))

        try:
            server = smtplib.SMTP(HOST)
            #               	server.set_debuglevel(1)
            server.login(USER, PASS)
            server.sendmail(FROM, TO, BODY)
            server.quit()
        except:
            print(bc.fail + (
                datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - ERROR Sending Email Message")
    else:
        print(bc.blu + (datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - NO Email Message Sent")
else:
    print(bc.blu + (datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - Email Sending Disabled")
print("------------------------------------------------------------------")

print(bc.blu + (datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - Notice Script Ended \n")
