//  _____    _   _    _                             
// |  __ \  (_) | |  | |                           
// | |__) |  _  | |__| |   ___    _ __ ___     ___ 
// |  ___/  | | |  __  |  / _ \  | |_  \_ \   / _ \ 
// | |      | | | |  | | | (_) | | | | | | | |  __/
// |_|      |_| |_|  |_|  \___/  |_| |_| |_|  \___|
//
//    S M A R T   H E A T I N G   C O N T R O L 
// *****************************************************************
// *              Boiler Controller Relay Sketch                   *
// *            Version 0.31 Build Date 09/01/2019                 *
// *            Last Modification Date 09/05/2019                  *
// *                                          Have Fun - PiHome.eu *
// *****************************************************************

// Enable debug prints to serial monitor
#define MY_DEBUG

// Enable and select radio type attached
#define MY_RADIO_RF24
//#define MY_RADIO_NRF5_ESB
//#define MY_RADIO_RFM69
//#define MY_RADIO_RFM95

//IRQ Pin on Arduino
//#define MY_RF24_IRQ_PIN 8

// Set LOW transmit power level as default, if you have an amplified NRF-module and
// power your radio separately with a good regulator you can turn up PA level.
// #define MY_RF24_PA_LEVEL RF24_PA_LOW
// RF24_PA_MIN RF24_PA_LOW RF24_PA_HIGH RF24_PA_MAX RF24_PA_ERROR

#define MY_RF24_PA_LEVEL RF24_PA_MIN
//#define MY_DEBUG_VERBOSE_RF24

// RF channel for the sensor net, 0-127 Default is 76
#define MY_RF24_CHANNEL	91

//PiHome Boiler Controller Node ID 
#define MY_NODE_ID 100

//RF24_250KBPS for 250kbs, RF24_1MBPS for 1Mbps, or RF24_2MBPS for 2Mbps
#define RF24_DATARATE 	   RF24_250KBPS

// Enable repeater functionality for this node
//#define MY_REPEATER_FEATURE

// Set baud rate to same as optibot
//#define MY_BAUD_RATE 9600

//set how long to wait for transport ready in milliseconds
//#define MY_TRANSPORT_WAIT_READY_MS 3000

//If Following LED Blink does not work then modify C:\Program Files (x86)\Arduino\libraries\MySensors_x_x_x\MyConfig.h 
#define MY_DEFAULT_ERR_LED_PIN 8
#define MY_DEFAULT_TX_LED_PIN 6
#define MY_DEFAULT_RX_LED_PIN 7
#define MY_WITH_LEDS_BLINKING_INVERSE

#define MY_DEFAULT_LED_BLINK_PERIOD 600

#include <MySensors.h>

#define RELAY_1  3  // Arduino Digital I/O pin number for first relay (second on pin+1 etc)
#define NUMBER_OF_RELAYS 1 // Total number of attached relays
#define RELAY_ON 0  // GPIO value to write to turn on attached relay
#define RELAY_OFF 1 // GPIO value to write to turn off attached relay

void before()
{
	for (int sensor=1, pin=RELAY_1; sensor<=NUMBER_OF_RELAYS; sensor++, pin++) {
		// Then set relay pins in output mode
		pinMode(pin, OUTPUT);
		// Set relay to last known state (using eeprom storage)
		//digitalWrite(pin, loadState(sensor)?RELAY_ON:RELAY_OFF);
	}
}

void setup(){

}

void presentation()
{
	// Send the sketch version information to the gateway and Controller
	sendSketchInfo("Boiler Relay", "0.31");

	for (int sensor=1, pin=RELAY_1; sensor<=NUMBER_OF_RELAYS; sensor++, pin++) {
		// Register all sensors to gw (they will be created as child devices)
		present(sensor, S_BINARY);
	}
}


void loop(){

}

void receive(const MyMessage &message)
{
	// We only expect one type of message from controller. But we better check anyway.
	if (message.type==V_STATUS) {
	
		// Change relay state
		digitalWrite(message.sensor-1+RELAY_1, message.getBool()?RELAY_ON:RELAY_OFF);
	
		// Store state in eeprom - we dont need to save relay state as controller take care of this, 
		// saveState(message.sensor, message.getBool());
		// Write some debug info
		#ifdef MY_DEBUG
			Serial.print("Incoming change for sensor:");
			Serial.print(message.sensor);
			Serial.print(", New status: ");
			Serial.println(message.getBool());
		#endif
	}
}
