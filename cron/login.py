#!/usr/bin/python
# add following line to show up when some one ssh to pi /etc/profile 
# sudo python /var/www/cron/login.py
class bc:
	HEADER = '\033[0;36;40m'
	ENDC = '\033[0m'
	SUB = '\033[3;30;45m'
	WARN = '\033[0;31;40m'
	GREEN = '\033[0;32;40m'
	WHITE = '\033[0;37;40m'
print bc.HEADER + " "	
print "  _____    _   _    _                            "
print " |  __ \  (_) | |  | |                           "
print " | |__) |  _  | |__| |   ___    _ __ ___     ___ "
print " |  ___/  | | |  __  |  / _ \  | |_  \_ \   / _ \ "
print " | |      | | | |  | | | (_) | | | | | | | |  __/"
print " |_|      |_| |_|  |_|  \___/  |_| |_| |_|  \___|"
print " "
print "    "+bc.SUB + "S M A R T   H E A T I N G   C O N T R O L "+ bc.ENDC
print bc.WARN +" "
print "*************************************************************************"
print "* PiHome is Raspberry Pi based Central Heating Control systems. It runs *"
print "* from web interface and it comes with ABSOLUTELY NO WARRANTY, to the   *"
print "* extent permitted by applicable law. I take no responcebility for any  *"
print "* loss or damage to you or your property.                               *"
print "* DO NOT MAKE ANY CHANGES TO YOUR HEATING SYSTEM UNTILL UNLESS YOU KNOW *"
print "* WHAT YOU ARE DOING                                                    *"
print "*************************************************************************"
print bc.WHITE + " I trust you have received the usual lecture from the local System   "
print " Administrator. It usually boils down to these three things:"
print "    #1) Respect the privacy of others."
print "    #2) Think before you type."
print "    #3) With great power comes great responsibility."
print bc.GREEN +"                                                           Have Fun - PiHome"  + bc.ENDC