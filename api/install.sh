#!/bin/bash

# check if Old Unit File exists and delete it
echo "Checking For Existing Unit File"
FILE=/lib/systemd/system/pihome_homekit_api.service
if [  -f "$FILE" ]; then
    echo "Stopping the service"
    sudo systemctl stop pihome_homekit_api.service
    echo "Disabling the service"
    sudo systemctl daemon-reload
    sudo systemctl disable pihome_homekit_api.service
    echo "Deleting Existing Unit File: $FILE"
    rm $FILE
fi

echo "Enabling mod_rewrite"
sudo a2enmod rewrite
echo "Backing Up and Modifying /etc/apache2/sites-available/000-default.conf"
FILE=/etc/apache2/sites-available/000-default.conf
if grep -q "<Directory /var/www/api>" $FILE
then
    echo "000-default.conf Already Modified"
else
    sudo cp -a -- "$FILE" "$FILE-$(date +"%Y%m%d-%H%M%S")"
    sudo awk '/DocumentRoot/{print $0 RS "" RS "        <Directory /var/www/api>"\
      RS "              Options Indexes FollowSymLinks" \
      RS "              AllowOverride All" \
      RS "              Require all granted" \
      RS "        </Directory>";next}1' $FILE > tmp && mv tmp $FILE

    echo "Restarting the apache service"
    sudo systemctl restart apache2
fi

echo "Backing Up and Updateing /var/lib/homebridge/config.json"
FILE=/var/lib/homebridge/config.json

if grep -q "HTTP-SWITCH" $FILE
then
    echo "Already Has a Switch Accessory so Modify if Required"
    if grep -q "localhost:8081" $FILE
    then
        sudo cp -a -- "$FILE" "$FILE-$(date +"%Y%m%d-%H%M%S")"
        sudo sed -i 's/localhost:8081/127.0.0.1/g' $FILE
        sudo sed -i 's/1000/5000/g' $FILE
        echo "Restarting Homebridge"
        sudo hb-service restart
     else
         echo "No Change required"
     fi
else
    echo "Adding a SWITCH Accessory for Each Zone"
    sudo cp -a -- "$FILE" "$FILE-$(date +"%Y%m%d-%H%M%S")"
    /usr/bin/python config_json.py
    echo "Restarting Homebridge"
    sudo hb-service restart
fi

if grep -q "HTTP-TEMPERATURE" $FILE
then
    echo "Already Has a Temperature Accessory so Modify if Required"
    if grep -q "localhost:8081" $FILE
    then
        sudo cp -a -- "$FILE" "$FILE-$(date +"%Y%m%d-%H%M%S")"
        sudo sed -i 's/localhost:8081/127.0.0.1/g' $FILE
        sudo sed -i 's/1000/5000/g' $FILE
        echo "Restarting Homebridge"
        sudo hb-service restart
     else
         echo "No Change required"
     fi
else
    echo "Adding a TEMPERATURE Accessory for Each Zone where graph_it is set to 1"
    sudo cp -a -- "$FILE" "$FILE-$(date +"%Y%m%d-%H%M%S")"
    /usr/bin/python config_json.py
    echo "Restarting Homebridge"
    sudo hb-service restart
fi

