#!/usr/bin/python
# add following line to show up when some one ssh to pi /etc/profile 
# sudo python /var/www/cron/login.py
# clear everything from /etc/motd to remove generic message. 
import socket, os, re, time, sys, subprocess
from threading import Thread
class bc:
	HEADER = '\033[0;36;40m'
	ENDC = '\033[0m'
	SUB = '\033[3;30;45m'
	WARN = '\033[0;31;40m'
	GREEN = '\033[0;32;40m'
	org = '\033[91m'
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
print "* extent permitted by applicable law. I take no responsibility for any  *"
print "* loss or damage to you or your property.                               *"
print "* DO NOT MAKE ANY CHANGES TO YOUR HEATING SYSTEM UNTILL UNLESS YOU KNOW *"
print "* WHAT YOU ARE DOING                                                    *"
print "*************************************************************************"
print bc.GREEN +"                                                      Have Fun - PiHome"  + bc.ENDC

df = subprocess.Popen(["df", "-h"], stdout=subprocess.PIPE)
output = df.communicate()[0]
device, size, used, available, percent, mountpoint = \
	output.split("\n")[1].split()

print bc.org +"Disk/SD Card Usage" + bc.ENDC
print "Filesystem  Size  Used   Avail  Used%"
print device+"   "+size+"   "+used+"   "+available+"   "+percent

s = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
s.connect(('google.com', 0))
ip = s.getsockname()[0]
#Get the Local IP
end = re.search('^[\d]{1,3}.[\d]{1,3}.[\d]{1,3}.[\d]{1,3}', ip)
#Chop down the last IP Digits
create_ip = re.search('^[\d]{1,3}.[\d]{1,3}.[\d]{1,3}.', ip)
print "WebServer:  "+bc.GREEN +"http://"+str(end.group(0))+"/"+ bc.ENDC
print "PhpMyAdmin: "+bc.GREEN +"http://"+str(end.group(0))+"/phpmyadmin"+ bc.ENDC