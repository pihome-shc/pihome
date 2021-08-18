#!/bin/bash

echo "Installing Phyton modules"
REQUIREMENTS=requirements.txt
sudo pip3 install -r $REQUIREMENTS

echo "Creating service for auto start"
sudo cp HA_integration.service /etc/systemd/system/HA_integration.service
sudo systemctl enable HA_integration.service

echo "Starting the service"
sudo systemctl start HA_integration.service