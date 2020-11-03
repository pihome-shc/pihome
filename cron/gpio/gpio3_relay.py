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
    elif board.board_id.find('ORANGE_PI_ONE') != -1:
        pindict = {
            "3": "PA12",
            "5": "PA11",
            "7": "PA6",
            "8": "PA13",
            "10": "PA14",
            "11": "PA1",
            "12": "PD14",
            "13": "PA0",
            "15": "PA3",
            "16": "PC4",
            "18": "PC7",
            "19": "PC0",
            "21": "PC1",
            "22": "PA2",
            "23": "PC2",
            "24": "PC3",
            "26": "PA21",
            "27": "PA19",
            "28": "PA18",
            "29": "PA7",
            "31": "PA8",
            "32": "PG8",
            "33": "PA9",
            "35": "PA10",
            "36": "PG9",
            "37": "PA20",
            "38": "PG6",
            "40": "PG7",
        }
    elif board.board_id.find('ORANGE_PI_ZERO_PLUS_2H5') != -1:
        pindict = {
            "3": "PA12",
            "5": "PA11",
            "7": "PA6",
            "8": "PA0",
            "10": "PA1",
            "11": "PL0",
            "12": "PD11",
            "13": "PL1",
            "15": "PA3",
            "16": "PA19",
            "18": "PA18",
            "19": "PA15",
            "21": "PA16",
            "22": "PA2",
            "23": "PA14",
            "24": "PA13",
            "26": "PD14",
        }
    elif board.board_id.find('BANANA_PI_M2_ZERO') != -1:
        pindict = {
            "3": "PA12",
            "5": "PA11",
            "7": "PA6",
            "8": "PA13",
            "10": "PA14",
            "11": "PA1",
            "12": "PA16",
            "13": "PA0",
            "15": "PA3",
            "16": "PA15",
            "18": "PCC",
            "19": "PC0",
            "21": "PC1",
            "22": "PA2",
            "23": "PC2",
            "24": "PC3",
            "26": "PC7",
            "27": "PA19",
            "28": "PA18",
            "29": "PA7",
            "31": "PA8",
            "32": "PL2",
            "33": "PA9",
            "35": "PA10",
            "36": "PL4",
            "37": "PA17",
            "38": "PA21",
            "40": "PA20",
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

