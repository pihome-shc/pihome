# Homekit
For controlling local devices with the iOS Home App.

## Quick Start

1. Install HomeKit, see https://github.com/homebridge/homebridge/wiki/Install-Homebridge-on-Raspbian
2. Install the homebridge-http-switch, see https://www.npmjs.com/package/homebridge-http-switch
3. Install the RESTful API service by executing bash /var/www/homekit/install_homekit.sh 
4. The install shell will automatically update the homebridge configuration file /var/lib/homebridge/config.json and add accessory sections for each of the active zones, an example accessories section is shown below :-

        {
            "accessory": "HTTP-SWITCH",
            "name": "Hot Water",
            "switchType": "stateful",
            "pullInterval": "1000",
            "onUrl": {
                "url": "http://localhost:8081/api/switchOn?zonename=Ch. Hot Water",
                "method": "GET"
            },
            "offUrl": {
                "url": "http://localhost:8081/api/switchOff?zonename=Ch. Hot Water",
                "method": "GET"
            },
            "statusUrl": {
                "url": "http://localhost:8081/api/switchStatus?zonename=Ch. Hot Water",
                "method": "GET"
            }
        },
        {
            "accessory": "HTTP-SWITCH",
            "name": "Central Heating",
            "switchType": "stateful",
            "pullInterval": "1000",
            "onUrl": {
                "url": "http://localhost:8081/api/switchOn?zonename=Ground Floor",
                "method": "GET"
            },
            "offUrl": {
                "url": "http://localhost:8081/api/switchOff?zonename=Ground Floor",
                "method": "GET"
            },
            "statusUrl": {
                "url": "http://localhost:8081/api/switchStatus?zonename=Ground Floor",
                "method": "GET"
            }
        }
        
5. Setup the HOME App on your iOS device
6. Use Siri to "turn off zonename" and "turn on zonename"
