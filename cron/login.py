#!/usr/bin/python
# add following line to show up when some one ssh to pi /etc/profile 
# sudo python /var/www/cron/login.py
# clear everything from /etc/motd to remove generic message. 
import socket, os, re, time, sys, subprocess, fcntl, struct
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

def get_interface_ip(ifname):
	s = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
	return socket.inet_ntoa(fcntl.ioctl(s.fileno(), 0x8915, struct.pack('256s', ifname[:15]))[20:24])
	
def get_ip():
	ip = socket.gethostbyname(socket.gethostname())
	if ip.startswith("127."):
		interfaces = ["eth0","eth1","eth2","wlan0","wlan1","wifi0","ath0","ath1","ppp0"]
		for ifname in interfaces:
			try:
				ip = get_interface_ip(ifname)
				break
			except IOError:
				pass
	return ip
print "WebServer:  "+bc.GREEN +"http://"+str(get_ip())+"/"+ bc.ENDC
print "PhpMyAdmin: "+bc.GREEN +"http://"+str(get_ip())+"/phpmyadmin"+ bc.ENDC