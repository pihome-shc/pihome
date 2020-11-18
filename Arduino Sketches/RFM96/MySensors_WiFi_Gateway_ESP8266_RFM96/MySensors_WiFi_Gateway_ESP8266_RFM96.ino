//  _____    _   _    _
// |  __ \  (_) | |  | |
// | |__) |  _  | |__| |   ___    _ __ ___     ___
// |  ___/  | | |  __  |  / _ \  | |_  \_ \   / _ \
// | |      | | | |  | | | (_) | | | | | | | |  __/
// |_|      |_| |_|  |_|  \___/  |_| |_| |_|  \___|
//
//    S M A R T   H E A T I N G   C O N T R O L
// *****************************************************************
// *    PiHome MySensors WiFi Gateway Based on ESP82666 Sketch     *
// *            Version 0.2 Build Date 06/11/2017                  *
// *                                          Have Fun - PiHome.eu *
// *****************************************************************

// Enable debug prints to serial monitor
#define MY_DEBUG

//Define Sketch Version
#define SKETCH_VERSION "0.2"

// Use a bit lower baudrate for serial prints on ESP8266 than default in MyConfig.h
#define MY_BAUD_RATE 9600

// Enables and select radio type (if attached)
//#define MY_RADIO_NRF24
//#define MY_RADIO_RFM69

// For RFM95
#define MY_RADIO_RFM95

#define MY_TRANSPORT_STATE_TIMEOUT_MS  (3*1000ul)
#define RFM95_RETRY_TIMEOUT_MS  (3000ul)
#define   MY_DEBUG_VERBOSE_RFM95
//#define   MY_DEBUG_VERBOSE_RFM95_REGISTERS
//#define MY_RFM95_ATC_TARGET_RSSI (-70)  // target RSSI -70dBm
//#define   MY_RFM95_MAX_POWER_LEVEL_DBM (20)   // max. TX power 10dBm = 10mW
#define   MY_RFM95_FREQUENCY (RFM95_434MHZ)
#define MY_RFM95_MODEM_CONFIGRUATION RFM95_BW125CR45SF128

#define MY_RFM95_IRQ_PIN D1
#define MY_RFM95_IRQ_NUM MY_RFM95_IRQ_PIN
#define MY_RFM95_CS_PIN D8
#endif

//#define MY_RF24_PA_LEVEL RF24_PA_MAX
//#define MY_DEBUG_VERBOSE_RF24

// RF channel for the sensor net, 0-127
//#define MY_RF24_CHANNEL 91

//RF24_250KBPS for 250kbs, RF24_1MBPS for 1Mbps, or RF24_2MBPS for 2Mbps
//#define MY_RF24_DATARATE RF24_250KBPS

//#define MY_ENCRYPTION_SIMPLE_PASSWD "pihome2019"

//#define MY_SIGNING_SIMPLE_PASSWD "pihome2019"

#define MY_INDICATION_HANDLER

// Flash leds on rx/tx/err
// Led pins used if blinking feature is enabled above
#define MY_DEFAULT_ERR_LED_PIN 16  // Error led pin
#define MY_DEFAULT_RX_LED_PIN  16  // Receive led pin
#define MY_DEFAULT_TX_LED_PIN  16  // the PCB, on board LED
#define MY_WITH_LEDS_BLINKING_INVERSE

// Set blinking period
#define MY_DEFAULT_LED_BLINK_PERIOD 400


#define MY_GATEWAY_ESP8266

//#define MY_WIFI_SSID "MySSID"
//#define MY_WIFI_PASSWORD "MyVerySecretPassword"
#define MY_HOSTNAME "ESP8266_GW"


// Enable UDP communication
//#define MY_USE_UDP

// Set the hostname for the WiFi Client. This is the hostname
// it will pass to the DHCP server if not static.
//#define MY_ESP8266_HOSTNAME "PiHome_Gateway"

// Enable MY_IP_ADDRESS here if you want a static ip address (no DHCP)
//#define MY_IP_ADDRESS 192,168,99,4

// If using static ip you need to define Gateway and Subnet address as well
//#define MY_IP_GATEWAY_ADDRESS 192,168,99,1
//#define MY_IP_SUBNET_ADDRESS 255,255,255,0

// The port to keep open on node server mode
#define MY_PORT 5003

// How many clients should be able to connect to this gateway (default 1)
#define MY_GATEWAY_MAX_CLIENTS 2

// Controller ip address. Enables client mode (default is "server" mode).
// Also enable this if MY_USE_UDP is used and you want sensor data sent somewhere.
//#define MY_CONTROLLER_IP_ADDRESS 192, 168, 178, 68

// Enable inclusion mode
//#define MY_INCLUSION_MODE_FEATURE

// Enable Inclusion mode button on gateway
//#define MY_INCLUSION_BUTTON_FEATURE
// Set inclusion mode duration (in seconds)
//#define MY_INCLUSION_MODE_DURATION 60
// Digital pin used for inclusion mode button
//#define MY_INCLUSION_MODE_BUTTON_PIN D1

#if defined(MY_USE_UDP)
#include <WiFiUdp.h>
#endif



#include <WiFiClient.h>


#include <ESP8266WiFi.h>          //https://github.com/esp8266/Arduino
//needed for library
#include <DNSServer.h>
#include <ESP8266WebServer.h>
#include <WiFiManager.h>         //https://github.com/tzapu/WiFiManager
//#include <ESP8266mDNS.h>		 // Makes the WiFi Gateway accessible throught http://pihomegw.local


ESP8266WebServer WebServer(80);
//Gateway Web Interface Stats
unsigned long startTime=millis();
unsigned long MsgTx = 0;
unsigned long MsgRx = 0;
unsigned long GWMsgTx = 0;
unsigned long GWMsgRx = 0;
unsigned long GWErTx = 0;
unsigned long GWErVer = 0;
unsigned long GWErTran = 0;


//String WebPage = "<h1>PiHome Smart Home Gateway</h1>";
String WebPage = "<!DOCTYPE html><html lang=\"en\"><head><meta charset=\"UTF-8\" name=\"viewport\" content=\"width=device-width, initial-scale=1, user-scalable=no\"/><title>PiHome Smart Home Gateway</title></head><body>";


void setupWebServer();
void showRootPage();
String readableTimestamp(unsigned long milliseconds);


const char* host = "pihomegw";
#include <MySensors.h>

//for LED status
#include <Ticker.h>
Ticker ticker;

void tick(){
  //toggle state
  int state = digitalRead(16);  // get the current state of GPIO1 pin
  digitalWrite(16, !state);     // set pin to the opposite state
}

//gets called when WiFiManager enters configuration mode
void configModeCallback (WiFiManager *myWiFiManager) {
  Serial.println("Smart Home Gateway Entered WiFi Config Mode!!!");
  Serial.println(WiFi.softAPIP());
  //if you used auto generated SSID, print it
  Serial.println(myWiFiManager->getConfigPortalSSID());
  //entered config mode, make led toggle faster
  ticker.attach(0.2, tick);
}

void setup(){
	//set led pin as output
	pinMode(16, OUTPUT);

	wifi_station_set_hostname("pihomegw");
	//start ticker with 0.5 because we start in AP mode and try to connect
	ticker.attach(0.6, tick);

	//Local intialization. Once its business is done, there is no need to keep it around
	WiFiManager wifiManager;

	//WiFihostname("PiHome_Gateway");
	//reset saved settings
	//wifiManager.resetSettings();

	//set callback that gets called when connecting to previous WiFi fails, and enters Access Point mode
	wifiManager.setAPCallback(configModeCallback);

	//set custom ip for portal
    wifiManager.setAPStaticIPConfig(IPAddress(10,0,1,1), IPAddress(10,0,1,1), IPAddress(255,255,255,0));

	//sets timeout until configuration portal gets turned off useful to make it all retry or go to sleep in seconds
	wifiManager.setTimeout(500);

	//fetches ssid and pass and tries to connect if it does not connect it starts an access point with the specified name here  "AutoConnectAP" and goes into a blocking loop awaiting configuration
	if(!wifiManager.autoConnect("PiHome_AP")) {
		Serial.println("Smart Home Gateway Failed to Connect and Hit Timeout");
		delay(3000);
		//reset and try again, or maybe put it to deep sleep
		ESP.reset();
		delay(5000);
	}

	//WiFi.hostname(host);
	//Serial.println(WiFi.hostname());
	/*
	if (!MDNS.begin(host)){
		Serial.println("error starting MDNS responder!");
	}
	Serial.println("mDNS responder started");
	server.begin();
	MDNS.addService("http", "tcp", 80);

*/

	//if you get here you have connected to the WiFi
	Serial.println("Smart Home Gateway Connected to your WiFi Successfully");
	ticker.detach();
	//keep LED on
	digitalWrite(16, LOW);

	Serial.begin(9600);
	setupWebServer();

}

void presentation()
{
	// Present locally attached sensors here
}


void loop(){
WebServer.handleClient();
}

void setupWebServer(){
  WebServer.on("/", HTTP_GET, showRootPage);
  WebServer.begin();
  Serial.println("WebServer started...");
}

void indication( const indication_t ind ){
	switch (ind) {
		case INDICATION_TX:
		MsgTx++;
		break;

		case INDICATION_RX:
		MsgRx++;
		break;

		case INDICATION_GW_TX:
		GWMsgTx++;
		break;

		case INDICATION_GW_RX:
		GWMsgRx++;
		break;


		case INDICATION_ERR_TX:
		GWErTx++;
		break;

		case INDICATION_ERR_VERSION:
		GWErVer++;
		break;

		case INDICATION_ERR_INIT_GWTRANSPORT:
		GWErTran++;
		break;
    default:
    break;
  };
}

void showRootPage()
{
  unsigned long runningTime = millis() - startTime;
  String page = WebPage;

  page+="<div style='text-align:center;display:inline-block;min-width:300px;'><h2>PiHome Smart Home Gateway</h2><h4>General Information</h4>";
  page+="<style>body{text-align: center;font-family:verdana;font-size:1rem;} tr, td {border-bottom:1px solid #ff8839;padding:10px;text-align:left;} tr:hover {background-color: #ffede2;}</style>";
//  page+="<table style=\"width:400\">";
  page+="<table align=\"center\">";

//Message Related
	page+="<tr>"; page+= "<td>Gateway Up Time</td>"; page+= "<td>"; page += readableTimestamp(runningTime); page+= "</td>"; page+="</tr>";

	page+="<tr>"; page+= "<td>Wi-Fi SSID</td>"; page+= "<td>"; page += WiFi.SSID(); page+= "</td>"; page+="</tr>";
	page+="<tr>"; page+= "<td>Wi-Fi Signal</td>"; page+= "<td>"; page += WiFi.RSSI(); page+= "</td>"; page+="</tr>";
	//page+="<tr>"; page+= "<td>Hostname</td>"; page+= "<td>"; page += WiFi.hostname(); page+= "</td>"; page+="</tr>";
	page+="<tr>"; page+= "<td>MAC Address</td>"; page+= "<td>"; page += WiFi.macAddress(); page+= "</td>"; page+="</tr>";
	page+="<tr>"; page+= "<td>Free Memory</td>"; page+= "<td>"; page += ESP.getFreeHeap(); page+= "</td>"; page+="</tr>";

	page+="<tr>"; page+= "<td>Network Transmited Messages</td>"; page+= "<td>"; page += MsgTx; page+= "</td>"; page+="</tr>";
	page+="<tr>"; page+= "<td>Network Received Messages</td>"; page+= "<td>"; page += MsgRx; page+= "</td>"; page+="</tr>";
	page+="<tr>"; page+= "<td>Gateway Transmit Message</td>"; page+= "<td>"; page += GWMsgTx; page+= "</td>"; page+="</tr>";
	page+="<tr>"; page+= "<td>Gateway Received Message</td>"; page+= "<td>"; page += GWMsgRx; page+= "</td>"; page+="</tr>";
	page+="<tr>"; page+= "<td>Gateway Failed to Transmit Message</td>"; page+= "<td>"; page += GWErTx; page+= "</td>"; page+="</tr>";
	page+="<tr>"; page+= "<td>Gateway Protocol Version Mismatch</td>"; page+= "<td>"; page += GWErVer; page+= "</td>"; page+="</tr>";
	page+="<tr>"; page+= "<td>Gateway Transport Hardware Failure</td>"; page+= "<td>"; page += GWErTran; page+= "</td>"; page+="</tr>";
	page+="<tr>"; page+= "<td>Gateway Sketch Version</td>"; page+= "<td>"; page += SKETCH_VERSION; page+= "</td>"; page+="</tr>";

	page+="</table></div></body></html>";

  Serial.println("Smart Home Gateway Served Web Interface");
  WebServer.send(200, "text/html", page);
}

String readableTimestamp(unsigned long milliseconds)
{
  int days = milliseconds / 86400000;

  milliseconds=milliseconds % 86400000;
  int hours = milliseconds / 3600000;
  milliseconds = milliseconds %3600000;

   int minutes = milliseconds / 60000;
   milliseconds = milliseconds % 60000;

   int seconds = milliseconds / 1000;
   milliseconds = milliseconds % 1000;

    String timeStamp;
    timeStamp = days; timeStamp += " days, ";
    timeStamp += hours; timeStamp += ":";
    timeStamp += minutes ; timeStamp +=  ":";
    timeStamp +=seconds; timeStamp += ".";
    timeStamp +=milliseconds;
    Serial.println(timeStamp);
    return timeStamp;
}