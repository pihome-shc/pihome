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
print(bc.hed + " ")	
print("  _____    _   _    _                            ")
print(" |  __ \  (_) | |  | |                           ")
print(" | |__) |  _  | |__| |   ___    _ __ ___     ___ ")
print(" |  ___/  | | |  __  |  / _ \  | |_  \_ \   / _ \ ")
print(" | |      | | | |  | | | (_) | | | | | | | |  __/")
print(" |_|      |_| |_|  |_|  \___/  |_| |_| |_|  \___|")
print(" ")
print("    "+bc.SUB + "S M A R T   H E A T I N G   C O N T R O L "+ bc.ENDC)
print(bc.WARN +" ")
print("********************************************************")
print("*   I2C Interface Relay Support Communication Script   *")
print("*   Build Date: 11/09/2019 Version 0.01                *")
print("    Last Modified: 20/07/2019                          *")
print("*                                 Have Fun - PiHome.eu *")
print("********************************************************")
print(" " + bc.ENDC)
# Simple test for BV4627 using RPi i2c
# use wget http://www.byvac.com/downloads/py/easyi2c.py

import easyi2c # have easyi2c.py in same directory
import sys
from time import sleep

def main():

    address = int(sys.argv[1])
    relayno = int(sys.argv[2])
    status = int(sys.argv[3])
    dev = easyi2c.IIC(address,1)
    # click relays in turn one after the other
    print("Relay ",relayno)
    dev.i2c([relayno,status,0,1],0) # on
    sleep(0.2)
    dev.close()

if __name__ == '__main__':
    main()
        
