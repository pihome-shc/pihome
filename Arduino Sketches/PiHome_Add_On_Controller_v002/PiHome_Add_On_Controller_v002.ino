//  _____    _   _    _                             
// |  __ \  (_) | |  | |                           
// | |__) |  _  | |__| |   ___    _ __ ___     ___ 
// |  ___/  | | |  __  |  / _ \  | |_  \_ \   / _ \ 
// | |      | | | |  | | | (_) | | | | | | | |  __/
// |_|      |_| |_|  |_|  \___/  |_| |_| |_|  \___|
//
//    S M A R T   H E A T I N G   C O N T R O L 
// *****************************************************************
// *              Add-On Controller Relay Sketch                   *
// *            Version 0.02 Build Date 19/06/2020                 *
// *            Last Modification Date 19/06/2020                  *
// *                                          Have Fun - PiHome.eu *
// *****************************************************************

// Adapted for AC-DC double solid state relay module, see
// https://www.openhardware.io/view/77/AC-DC-double-solid-state-relay-module#tabs-instructions


// Enable debug prints to serial monitor
#define MY_DEBUG

//Set MY_SPLASH_SCREEN_DISABLED to disable MySensors splash screen. (This saves 120 bytes of flash)
#define MY_SPLASH_SCREEN_DISABLED

// Enable and select radio type attached
#define MY_RADIO_RF24
//#define MY_RADIO_NRF5_ESB
//#define MY_RADIO_RFM69
//#define MY_RADIO_RFM95

//Define this to use the IRQ pin of the RF24 module (optional). 
//#define MY_RF24_IRQ_PIN 8

// * - RF24_PA_MIN = -18dBm
// * - RF24_PA_LOW = -12dBm
// * - RF24_PA_HIGH = -6dBm
// * - RF24_PA_MAX = 0dBm
// Set LOW transmit power level as default, if you have an amplified NRF-module and
// power your radio separately with a good regulator you can turn up PA level.
// RF24_PA_MIN RF24_PA_LOW RF24_PA_HIGH RF24_PA_MAX RF24_PA_ERROR
#define MY_RF24_PA_LEVEL RF24_PA_MIN
//#define MY_DEBUG_VERBOSE_RF24

/**
 * @brief RF channel for the sensor net, 0-125.
 * Frequencies: 2400 Mhz - 2525 Mhz
 * @see https://www.nordicsemi.com/eng/nordic/download_resource/8765/2/42877161/2726
 * - 0 => 2400 Mhz (RF24 channel 1)
 * - 1 => 2401 Mhz (RF24 channel 2)
 * - 76 => 2476 Mhz (RF24 channel 77)
 * - 83 => 2483 Mhz (RF24 channel 84)
 * - 124 => 2524 Mhz (RF24 channel 125)
 * - 125 => 2525 Mhz (RF24 channel 126)
 * In some countries there might be limitations, in Germany for example only the range
 * 2400,0 - 2483,5 Mhz is allowed.
 * @see http://www.bundesnetzagentur.de/SharedDocs/Downloads/DE/Sachgebiete/Telekommunikation/Unternehmen_Institutionen/Frequenzen/Allgemeinzuteilungen/2013_10_WLAN_2,4GHz_pdf.pdf
 */
 
//Default RF channel Default is 76
#define MY_RF24_CHANNEL  91

//PiHome Add-On Controller Node ID
#define MY_NODE_ID 80

//RF24_250KBPS for 250kbs, RF24_1MBPS for 1Mbps, or RF24_2MBPS for 2Mbps
#define MY_RF24_DATARATE RF24_250KBPS

//Enable Signing 
//#define MY_SIGNING_SIMPLE_PASSWD "pihome2019"

//Enable Encryption This uses less memory, and hides the actual data.
//#define MY_ENCRYPTION_SIMPLE_PASSWD "pihome2019"

// Enable repeater functionality for this node
//#define MY_REPEATER_FEATURE

// Comment line below if you don't want to use the temperature sensor
#define USE_TEMP_SENSOR

//Define Sketch Name 
#ifdef USE_TEMP_SENSOR
  #define SKETCH_NAME "Add-On Controller/Sensor"
#else
  #define SKETCH_NAME "Add-On Controller"
#endif
//Define Sketch Version 
#define SKETCH_VERSION "0.02"

// Set baud rate to same as optibot
//#define MY_BAUD_RATE 115200

//set how long to wait for transport ready in milliseconds
//#define MY_TRANSPORT_WAIT_READY_MS 3000

#include <MySensors.h>
#include <Bounce2.h>

// MySensor Debug
#define MY_DEBUG

// Comment line below if you don't want to use the temperature sensor
#define USE_TEMP_SENSOR

#include <MySensors.h>
#include <SPI.h>
#include <Bounce2.h>

#define BUTTON_PIN   4  // Arduino Digital I/O pin number for button 
#define BUTTON_PIN_2 7
#define NUMBER_OF_RELAYS 2 // Total number of attached relays
int relayPins[]={5, 3}; // Arduino Digital I/O pin numbers for relays

#define CHILD_ID 1 // Id of the sensor child for 1st relay
#define CHILD_ID_2 2 // Id of the sensor child for 2nd relay

// Relay status
#define RELAY_ON 1
#define RELAY_OFF 0

// Source of state change (used when printing debug information)
#define CHANGE_STATE_SOURCE_RADIO 0
#define CHANGE_STATE_SOURCE_SWITCH 1


// Temperature sensor definitions
#ifdef USE_TEMP_SENSOR
  #include <OneWire.h>
  #include <DallasTemperature.h>
  #define COMPARE_TEMP 1 // Send temperature only if changed? 1 = Yes 0 = No, > 1 - force send if it value not sent that number of times and value is valid (keep lower than notice interval)
  #define ONE_WIRE_BUS 8
  #define CHILD_DSB_ID 0 // Id of the sensor child for temperature sensor
  #define TEMPERATURE_ROUNDING 5.f   // Change value to change rounding of temperature value: 10.f for 0.1°C change, 5.f for 0.2°C change, 2.f for 0.5°C change
  MyMessage msgTemp(CHILD_DSB_ID, V_TEMP);
  OneWire oneWire(ONE_WIRE_BUS);
  DallasTemperature sensors(&oneWire); // Pass the oneWire reference to Dallas Temperature.
  float lastTemperature;
  bool receivedConfig = false;
  bool metric = true;
  int temperatureNotSentCount;
#endif

Bounce debouncer = Bounce();
int oldValue;
bool state;
Bounce debouncer2 = Bounce();
int oldValue2;
bool state2;

MyMessage msg(CHILD_ID, V_LIGHT);
MyMessage msg2(CHILD_ID_2, V_LIGHT);

void presentation() {
  // Send the sketch version information to the gateway and Controller
  sendSketchInfo(SKETCH_NAME, SKETCH_VERSION);
  // Register all sensors to gw (they will be created as child devices)
  #ifdef USE_TEMP_SENSOR
    present(CHILD_DSB_ID, S_TEMP);
  #endif
  for (int i = 1; i <= NUMBER_OF_RELAYS; i++) {
    present(i, S_LIGHT);
  }
}

void setup()
{
#ifdef USE_TEMP_SENSOR
  sensors.begin();
  sensors.setWaitForConversion(false);
#endif

  // Setup the button
  pinMode(BUTTON_PIN, INPUT);
  // Activate internal pull-up
  digitalWrite(BUTTON_PIN, HIGH);

  // Setup the button
  pinMode(BUTTON_PIN_2, INPUT);
  // Activate internal pull-up
  digitalWrite(BUTTON_PIN_2, HIGH);

  // After setting up the button, setup debouncer
  debouncer.attach(BUTTON_PIN);
  debouncer.interval(5);

  debouncer2.attach(BUTTON_PIN_2);
  debouncer2.interval(5);

  // Set the initial values of oldValue/oldValue2 variables from status of physical switches
  //  if this is not done the loop() will detect status change and switch the relays on or off
  debouncer.update();
  debouncer2.update();
  oldValue = debouncer.read();
  oldValue2 = debouncer2.read();

  // Setup relay pins
  for (int thisPin = 0; thisPin < NUMBER_OF_RELAYS; thisPin++) {
    // Make sure relays are off when starting up
    setRelayState(relayPins[thisPin], RELAY_OFF);
    // Then set relay pins in output mode
    pinMode(relayPins[thisPin], OUTPUT);
    // Set relay to last known state (using eeprom storage)
    digitalWrite(relayPins[thisPin], loadState(thisPin+1)?RELAY_ON:RELAY_OFF);
    delay(3);
  }
}

void loop()
{
  debouncer.update();
  debouncer2.update();
  // Get the update value
  int value = debouncer.read();
  int value2 = debouncer2.read();

  if (value != oldValue) {
    state =  !state;                                                  // Toggle the state
    send(msg.set(state), false);                          // send new state to controller, no ack requested
    setRelayState(relayPins[0], state);                // switch the relay to the new state
    saveState(CHILD_ID, state);        // Store state in eeprom
    // Write some debug info
    printStateChangedDebug(CHANGE_STATE_SOURCE_SWITCH, CHILD_ID, value);
    oldValue = value;
  }
  
  if (value2 != oldValue2) {
    state2 =  !state2;                                         // Toggle the state
    send(msg2.set(state2), false);                 // send new state to controller, no ack requested
    setRelayState(relayPins[1], state2);     // switch the relay to the new state
    saveState(CHILD_ID_2, state2);     // Store state in eeprom
    // Write some debug info
    printStateChangedDebug(CHANGE_STATE_SOURCE_SWITCH, CHILD_ID_2, value2);
    oldValue2 = value2;
  }
  
  // Fetch temperatures from Dallas sensors
  #ifdef USE_TEMP_SENSOR
    sensors.requestTemperatures();
    // Fetch and round temperature to one decimal
    float temperature = static_cast<float>(static_cast<int>(sensors.getTempCByIndex(0) * TEMPERATURE_ROUNDING)) / TEMPERATURE_ROUNDING;

    #if COMPARE_TEMP == 1
      if (temperature != -127.00f && temperature != 85.00f && lastTemperature != temperature) {
        // Send in the new temperature
        send(msgTemp.set(temperature, 1));
        lastTemperature = temperature;
        #ifdef MY_DEBUG
          Serial.print("Sent temperature: ");
          Serial.println(temperature);
        #endif
      }
    #elif COMPARE_TEMP == 0
      if (temperature != -127.00 && temperature != 85.00) {
        // Send in the new temperature
        send(msg.setSensor(i).set(temperature,1));
      }
    #else
      if ((lastTemperature != temperature || temperatureNotSentCount>=COMPARE_TEMP) && temperature != -127.00 && temperature != 85.00) {
        // Send in the new temperature
        send(msg.setSensor(0).set(temperature,1));
        // Save new temperatures for next compare
        lastTemperature=temperature; 
        //Reset values not sent count
        temperatureNotSentCount=0;      
      }else{
        lastTemperature=temperature; 
        temperatureNotSentCount++;
      }
    #endif
  #endif
}

void receive(const MyMessage &message) { 
if (message.type == V_STATUS) {
 switch (message.sensor) {
  case CHILD_ID:    
    state = message.getBool();          // Change relay state
    setRelayState(relayPins[0], state);    
    saveState(CHILD_ID, state);        // Store state in eeprom
    // Write some debug info
    printStateChangedDebug(CHANGE_STATE_SOURCE_RADIO, CHILD_ID, state);
    break; 
  case CHILD_ID_2:
    state2 = message.getBool();
    setRelayState(relayPins[1], state2);
    saveState(CHILD_ID_2, state2);     // Store state in eeprom
    // Write some debug info
    printStateChangedDebug(CHANGE_STATE_SOURCE_RADIO, CHILD_ID_2, state2);
    break;
  }
 }
}

// Set status of a relay pin
void setRelayState(byte relayPin, bool value) {
  digitalWrite(relayPin, value ? RELAY_ON : RELAY_OFF);
}

// Print debug info, centralized in one place to minimize memory usage and have only one #ifdef MY_DEBUG for all state change messages
void printStateChangedDebug(int source, int sensorID, bool value) {
#ifdef MY_DEBUG
  Serial.print(F("Sensor value changed, source="));
  Serial.print(source == CHANGE_STATE_SOURCE_RADIO ? F("Radio") : F("Physical switch"));
  Serial.print(F(", Sensor="));
  Serial.print(sensorID);
  Serial.print(F(", New status: "));
  Serial.println(value);
#endif
}
