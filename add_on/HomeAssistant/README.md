# Home Assistant intehration
For monitoring and controlling PiHome from Home Assistant via MQTT. PiHome will automatically broadcast over MQTT all the entity definitions needed to setup and then update the following HA entities:
* PiHome CPU Usage - sensor
* PiHome CPU Load (1m, 5m and 15m) - sensors
* PiHome CPU temperature - sensor
* PiHome Memory Use - sensor
* PiHome Swap Usage - sensor
* PiHome Disk Use - sensor
* PiHome Host Ip - sensor
* PiHome Last Boot - sensor
* PiHome Network throughput (up & down) - sensors
* PiHome Wifi Strength - sendor
* Boiler Status - binary sensor
* Climate entity for each zone defined in PiHome
  * Away Status (this is the same for all zones)
  * Zone Current Temperature (for each zone)
  * Zone Target Temperature (for each zone)
  * Zone Current Mode (for each zone)
  * Zone Current Status (for each zone)
  * Zone Boost (for each zone)
  * Zone Override (for each zone)
  * Zone sensor battery percentage (for each zone using a MySensor sensor)
  * Zone sensor battery voltage (for each zone using a MySensor sensor)

The climate entites allow to trigger the PiHome Boost function (Aux Heat in Home Assistant) for each zone, enable or disable the PiHome Away status and adjust the target tempearture for each zone. When the target tempearture of a zone is adjusted in Home Assisatant the Override mode is triggered in PiHome with the new target tempearture. When installing this add-on a change will be made to the PiHome Override mode logic: **the Override mode will automatically be disabled when a new schedule for the zone starts**.

## Quick Start

1. Ensure your database is updated (i.e. the *mqtt* table includes the *topic* column)
2. Execute bash install.sh this will:
    Install the Phyton moudles needed
    Create and enable a service for autostart
    Start service that was created
