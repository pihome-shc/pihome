# Homekit
For controlling local devices with the iOS Home App.

## Quick Start

1. Install Homebridge, see https://github.com/homebridge/homebridge/wiki/Install-Homebridge-on-Raspbian
2. Execute bash install.sh this will:
    Install the Homebridge Http Webhooks plugin.
    Modify /etc/apache2/sites-available/000-default.conf and enable mod_rewrite so that urls without the .php extension can be used (a backup will be created),
    Check that the Webhooks cache storage directory has been created and is empty.
    Create a backup of /var/lib/homebridge/config.json.
    Add to config.json the Webhooks platform
    Add to config.json Webhooks switches for each zone where status = 1, the [id] value will be switchxx where xx is the zone_id.
    Add to config.json Webhooks sensors for each zone where status = 1 or graph_it = 1, the [id] value will be sensorxx where xx is the zone_id.
