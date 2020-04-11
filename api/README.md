# Homekit
For controlling local devices with the iOS Home App.

## Quick Start

1. Install Homebridge, see https://github.com/homebridge/homebridge/wiki/Install-Homebridge-on-Raspbian
2. Install the homebridge-http-switch, see https://www.npmjs.com/package/homebridge-http-switch
3. Install the homebridge-http-temperature-sensor, see https://www.npmjs.com/package/homebridge-http-temperature-sensor
4. Execute bash install.sh this will
4a. remove the service for previous python based version
4b. modify /etc/apache2/sites-available/000-default.conf and enable mod_rewrite so that urls without the .php extension can be used (a backup will be created)
4c. check is SWITCH and/or TEMPERATURE entries exist in /var/lib/homebridge/config.json and modify the url if required. If the sections are not there they will be created (a backup will be created)
