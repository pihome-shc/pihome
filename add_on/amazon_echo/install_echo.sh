#!/bin/bash

# check if python-requests is installed
echo "Checking if python-requests is Installed"
var=$(python -c 'import pkgutil; print(1 if pkgutil.find_loader("requests") else 0)')
if [ "$var" = "0" ]
then
        echo "python-requests is Not Installed"
        echo "Check if python-pip is Installed"
        var=$(which pip)
        if [ -z "$var" ]
        then
            echo "Installing pip"
            sudo apt-get insall python-pip
        else
            echo "pip already installed"
        fi
else
        echo "python-requests already installed"
fi

# check if Unit File already exists
echo "Checking For Existing Unit File"
FILE=/lib/systemd/system/pihome_amazon_echo.service
if [  -f "$FILE" ]; then
    echo "Deleting Existing Unit File: $FILE"
    rm $FILE
fi
echo "Creating Unit File: $FILE"
sudo cat <<EOT >> /lib/systemd/system/pihome_amazon_echo.service
[Unit]
Description=Echo
After=multi-user.target

[Service]
Type=simple
ExecStart=/usr/bin/python -u /var/www/add_on/amazon_echo/echo_pihome.py
Restart=on-abort

[Install]
WantedBy=multi-user.target
=============================================
EOT

echo "Enabling the service"
sudo systemctl daemon-reload
sudo systemctl enable pihome_amazon_echo.service
echo "Starting the service"
sudo systemctl start pihome_amazon_echo.service
