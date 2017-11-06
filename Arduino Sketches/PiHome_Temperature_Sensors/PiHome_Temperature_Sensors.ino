//  _____    _   _    _                             
// |  __ \  (_) | |  | |                           
// | |__) |  _  | |__| |   ___    _ __ ___     ___ 
// |  ___/  | | |  __  |  / _ \  | |_  \_ \   / _ \ 
// | |      | | | |  | | | (_) | | | | | | | |  __/
// |_|      |_| |_|  |_|  \___/  |_| |_| |_|  \___|
//
//    S M A R T   H E A T I N G   C O N T R O L 
// *****************************************************************
// * 18650 Battery Powered OneWire DS18B20 Temperature Sensor      *
// *            Version 0.2 Build Date 06/11/2017                  *
// *                                          Have Fun - PiHome.eu *
// *****************************************************************



// Enable debug prints to serial monitor
//#define MY_DEBUG 

// Enable and select radio type attached
#define MY_RADIO_NRF24
//#define MY_RADIO_RFM69

// Set LOW transmit power level as default, if you have an amplified NRF-module and
// power your radio separately with a good regulator you can turn up PA level.
// #define MY_RF24_PA_LEVEL RF24_PA_LOW

#define MY_RF24_PA_LEVEL RF24_PA_MAX
//#define MY_DEBUG_VERBOSE_RF24

// RF channel for the sensor net, 0-127
#define RF24_CHANNEL     125
//PiHome - Make Sure you change Node ID, for each temperature sensor. 
#define MY_NODE_ID 21

//RF24_250KBPS for 250kbs, RF24_1MBPS for 1Mbps, or RF24_2MBPS for 2Mbps
#define RF24_DATARATE 	   RF24_250KBPS

#include <SPI.h>
#include <MySensors.h>  
#include <DallasTemperature.h>
#include <OneWire.h>

// Define sensor node childs
#define CHILD_ID_BATT 1
#define CHILD_ID_TEMP 0

#define COMPARE_TEMP 1 // Send temperature only if changed? 1 = Yes 0 = No
#define COMPARE_BVOLT 0 // Send battery voltage only if changed? 1 = Yes 0 = No
#define ONE_WIRE_BUS 3 // Pin where dallase sensor is connected 

#define MAX_ATTACHED_DS18B20 16
unsigned long SLEEP_TIME = 56000; // Sleep time between reads (in milliseconds)

// Battery related init
int BATTERY_SENSE_PIN = A0;  // select the input pin for the battery sense point
float oldBatteryV = 0;
MyMessage msgBatt(CHILD_ID_BATT, V_VOLTAGE);

// Dallas Temperature related init
OneWire oneWire(ONE_WIRE_BUS); // Setup a oneWire instance to communicate with any OneWire devices (not just Maxim/Dallas temperature ICs)
DallasTemperature sensors(&oneWire); // Pass the oneWire reference to Dallas Temperature. 
float lastTemperature[MAX_ATTACHED_DS18B20];
int numSensors=0;
bool receivedConfig = false;
bool metric = true;
MyMessage msgTemp(CHILD_ID_TEMP, V_TEMP);

void before()
{
  // Startup up the OneWire library
  sensors.begin();
}

void setup()  
{ 
  // requestTemperatures() will not block current thread
  sensors.setWaitForConversion(false);
  // needed for battery soc
  // use the 1.1 V internal reference
  #if defined(__AVR_ATmega2560__)
      analogReference(INTERNAL1V1);
  #else
      analogReference(INTERNAL);
  #endif
}

void presentation() {
  // Send the sketch version information to the gateway and Controller
  sendSketchInfo("Temperature Sensor", "1.34");

  // Fetch the number of attached temperature sensors  
  numSensors = sensors.getDeviceCount();

  // Present all sensors to controller
  for (int i=0; i<numSensors && i<MAX_ATTACHED_DS18B20; i++) {   
     present(i, S_TEMP);
  }
}

void loop()     
{     

  // get the battery Voltage
  int battSensorValue = analogRead(BATTERY_SENSE_PIN);
  float batteryV  = battSensorValue * 0.011828;
    // 1M, 100K divider across battery and using internal ADC ref of 1.1V
    // Sense point is bypassed with 0.1 uF cap to reduce noise at that point
    // ((1e6+100e3)/100e3)*1.1 = Vmax = 12.1 Volts
    // 12.1/1023 = Volts per bit = 0.011828 
	int batteryPcnt = (batteryV / 4.2) * 100; //18650 gives 4.2 max Voltage 
	
	#ifdef MY_DEBUG
		Serial.print("Pin Reading: ");
		Serial.println(battSensorValue);
		Serial.print("Battery Voltage: ");
		Serial.print(batteryV);
		Serial.println(" v");
		//Print Battery Percentage
		Serial.print("Battery percent: ");
		Serial.print(batteryPcnt);
		Serial.println(" %");
	#endif
	#if COMPARE_BVOLT == 1
		send(msgBatt.set(batteryV, 2));
		sendBatteryLevel(batteryPcnt);
	#else 
		if (oldBatteryV != batteryV) {
			send(msgBatt.set(batteryV, 2));
			sendBatteryLevel(batteryPcnt);
			oldBatteryV = batteryV;
		}
	#endif


  // Fetch temperatures from Dallas sensors
  sensors.requestTemperatures();

  // query conversion time and sleep until conversion completed
  int16_t conversionTime = sensors.millisToWaitForConversion(sensors.getResolution());
  // sleep() call can be replaced by wait() call if node need to process incoming messages (or if node is repeater)
  sleep(conversionTime);

  // Read temperatures and send them to controller 
  for (int i=0; i<numSensors && i<MAX_ATTACHED_DS18B20; i++) {

    // Fetch and round temperature to one decimal
    float temperature = static_cast<float>(static_cast<int>((getControllerConfig().isMetric?sensors.getTempCByIndex(i):sensors.getTempFByIndex(i)) * 10.)) / 10.;

    // Only send data if temperature has changed and no error
    #if COMPARE_TEMP == 1
    if (lastTemperature[i] != temperature && temperature != -127.00 && temperature != 85.00) {
    #else
    if (temperature != -127.00 && temperature != 85.00) {
    #endif

      // Send in the new temperature
      send(msgTemp.setSensor(i).set(temperature,1));
      // Save new temperatures for next compare
      lastTemperature[i]=temperature;
    }
  }
  sleep(SLEEP_TIME);
}