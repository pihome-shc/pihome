# Home Assistant intehration
For monitoring PiHome from Home Assistant via MQTT. PiHome will automatically broadcast over MQTT all the sensor definition needed to setup and then update the following HA sensors:
* PiHome CPU Usage
* PiHome CPU Load (1m, 5m and 15m)
* PiHome CPU temperature
* PiHome Memory Use
* PiHome Swap Usage
* PiHome Disk Use
* PiHome Host Ip
* PiHome Last Boot
* PiHome Network throughput (up & down)
* PiHome Wifi Strength
* Boiler status
* Zone Temperature (for each zone)
* Zone status (for each zone)
* Zone sensor battery percentage (for each zone using a MySensor sensor)
* Zone sensor battery voltage (for each zone using a MySensor sensor)

## Quick Start

1. Ensure your database is updated (i.e. the *mqtt* table includes the *topic* column)
2. Execute bash install.sh this will:
    Install the Phyton moudles needed
    Create and enable a service for autostart
    Start service that was created
