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
print("    " +bc.SUB + "S M A R T   H E A T I N G   C O N T R O L " + bc.ENDC)
print(bc.WARN +" ")
print("********************************************************")
print("*   GPIO Interface Relay Support Communication Script  *")
print("*    Build Date: 25/04/2020 Version 0.01               *")
print("*    Last Modified: 25/04/2020                         *")
print("*                                 Have Fun - PiHome.eu *")
print("********************************************************")
print(" " + bc.ENDC)

import time
import board
import digitalio
import sys

def main():
    # create a pin mapping
    # check if a Raspberry Pi
    if board.board_id.find('RASPBERRY_PI') != -1:
        pindict = {
            "3": "D2",
            "5": "D3",
            "7": "D4",
            "8": "D14",
            "10": "D15",
            "11": "D17",
            "12": "D18",
            "13": "D27",
            "15": "D22",
            "16": "D23",
            "18": "D24",
            "19": "D10",
            "21": "D9",
            "22": "D25",
            "23": "D11",
            "24": "D8",
            "26": "D7",
            "27": "D0",
            "28": "D1",
            "29": "D5",
            "31": "D6",
            "32": "D12",
            "33": "D13",
            "35": "D19",
            "36": "D16",
            "37": "D26",
            "38": "D20",
            "40": "D21",
        }
    # check if a BEAGLEBONE
    elif board.board_id.find('BEAGLEBONE') != -1:
        # add 100 to P9 header pins so they can be referenced with an interger P9 pin number + 100
        pindict = dict()
        for x in dir(board):
           y = x.find('_')
           if x.find('P8') != -1:
               pin = {x[y + 1:]: x }
               pindict.update(pin)
           elif x.find('P9') != -1:
               pin = {str(100 + int(x[y + 1:])): x }
               pindict.update(pin)

    if sys.argv[1] in pindict:
        pin_num = pindict[sys.argv[1]] 
        print("Pin Number ",pin_num)
        relay = digitalio.DigitalInOut(getattr(board, pin_num))
        relay.direction = digitalio.Direction.OUTPUT
        if sys.argv[2] == '0' :
            relay.value = False
            print ("LOW")
        else :
            relay.value = True
            print ("HIGH")

if __name__ == '__main__':
    main()

