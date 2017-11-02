#!/bin/bash
#python /var/www/cron/wifigw.py
#PID=`ps -ef | grep memcache | grep -v "grep" | awk '{print $2}'`

#to check PID is right
#kill -9 $PID

#PID=`ps -ef | grep "$1" | grep -v "grep" | awk '{print $2}'`
#echo $PID
#kill -9 $PID
echo "$1" 
kill -9 "$1"