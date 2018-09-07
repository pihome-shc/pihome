#!/usr/bin/env python
#TheZero
#This code is under Public Domain
#ref: https://gist.github.com/TheZ3ro/7255052

from threading import Thread
import socket
import os, re, time, sys, subprocess
class bc:
	HEADER = '\033[0;36;40m'
	ENDC = '\033[0m'
	SUB = '\033[3;30;45m'
	WARN = '\033[0;31;40m'
	GREEN = '\033[0;32;40m'
	org = '\033[91m'
	
	
s = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
s.connect(('google.com', 0))
ip = s.getsockname()[0]
#Get the Local IP
end = re.search('^[\d]{1,3}.[\d]{1,3}.[\d]{1,3}.[\d]{1,3}', ip)
#Chop down the last IP Digits
create_ip = re.search('^[\d]{1,3}.[\d]{1,3}.[\d]{1,3}.', ip)
print "PiHome IP Address: "+bc.GREEN+str(end.group(0))+bc.ENDC


host = str(end.group(0))

host = '192.168.99.5'
from_port = 5000
to_port = 5005

#host = raw_input('host > ')
#from_port = input('start scan from port > ')
#to_port = input('finish scan to port > ')   
counting_open = []
counting_close = []
threads = []

def scan(port):
	s = socket.socket()
	result = s.connect_ex((host,port))
	print('working on port > '+(str(port)))      
	if result == 0:
		counting_open.append(port)
		print((str(port))+' -> open') 
		s.close()
	else:
		counting_close.append(port)
		#print((str(port))+' -> close') 
		s.close()

for i in range(from_port, to_port+1):
	t = Thread(target=scan, args=(i,))
	threads.append(t)
	t.start()
	
#[x.join() for x in threads]

print(counting_open)
