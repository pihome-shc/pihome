#!/bin/bash
#=================================================================
# Script Variables Settings
clear
wlan='wlan0'
gateway='192.168.99.1'
#gateway=ip route get 8.8.8.8 | grep via | cut -d ' ' -f 3
alias ifup='/sbin/ifup'
alias ifdown='/sbin/ifdown'
alias ifconfig='/sbin/ifconfig'
#=================================================================
echo "  _____    _   _    _                            "
echo " |  __ \  (_) | |  | |                           "
echo " | |__) |  _  | |__| |   ___    _ __ ___     ___ "
echo " |  ___/  | | |  __  |  / _ \  | |_  \_ \   / _ \ "
echo " | |      | | | |  | | | (_) | | | | | | | |  __/"
echo " |_|      |_| |_|  |_|  \___/  |_| |_| |_|  \___|"
echo " "
echo "    S M A R T   H E A T I N G   C O N T R O L "
echo "*************************************************************************"
echo "* PiHome is Raspberry Pi based Central Heating Control systems. It runs *"
echo "* from web interface and it comes with ABSOLUTELY NO WARRANTY, to the   *"
echo "* extent permitted by applicable law. I take no responsibility for any  *"
echo "* loss or damage to you or your property.                               *"
echo "* DO NOT MAKE ANY CHANGES TO YOUR HEATING SYSTEM UNTIL UNLESS YOU KNOW  *"
echo "* WHAT YOU ARE DOING                                                    *"
echo "*************************************************************************"
echo
echo "                                                           Have Fun - PiHome" 
date
echo " - Auto Reconnect Wi-Fi Status for $wlan Script Started ";
echo

# Only send two pings, sending output to /dev/null as we don't want to fill logs on our sd card. 
# If you want to force ping from your wlan0 you can connect next line and un comment second line 
ping -c2 ${gateway} > /dev/null # ping to gateway from Wi-Fi or from Ethernet
# ping -I ${wlan} -c2 ${gateway} > /dev/null # only ping through Wi-Fi 

# If the return code from ping ($?) is not 0 (meaning there was an error)
if [ $? != 0 ]
then
    # Restart the wireless interface
    ifdown --force wlan0
    ifup wlan0
	sleep 5
	ifup wlan0
fi
ping -I ${wlan} -c2 ${gateway} > /dev/null
date
echo 
echo " - Auto Reconnect Wi-Fi Status for $wlan Script Ended ";