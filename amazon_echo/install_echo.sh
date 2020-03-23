#!/bin/bash

cat <<EOT >> /lib/systemd/system/pihome_amazon_echo.service
[Unit]
Description=Echo
After=multi-user.target

[Service]
Type=simple
ExecStart=/usr/bin/python /var/www/amazon_echo/echo_pihome.py
Restart=on-abort

[Install]
WantedBy=multi-user.target
=============================================
EOT

sudo pip install requests
sudo systemctl daemon-reload
sudo systemctl enable pihome_amazon_echo.service
sudo systemctl start pihome_amazon_echo.service
