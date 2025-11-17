# NodeMCU ESP8266 - RFID Absensi Code

## 📱 Kode Arduino (.ino) untuk NodeMCU

```cpp
#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <WiFiClient.h>
#include <SPI.h>
#include <MFRC522.h>
#include <ArduinoJson.h>

// WiFi Configuration
const char* ssid = "YOUR_WIFI_NAME";
const char* password = "YOUR_WIFI_PASSWORD";

// Server Configuration
const char* serverURL = "http://absensi.fazcreateve.my.id/api/rfid";
// const char* serverURL = "http://192.168.1.100:8000/api/rfid";  // Local development

// RFID Configuration
#define RST_PIN         0  // GPIO0 (D3)
#define SS_PIN          2  // GPIO2 (D4)
MFRC522 rfid(SS_PIN, RST_PIN);

// LED Configuration
#define LED_RED         16  // GPIO16 (D0) - Merah (Error)
#define LED_GREEN       5   // GPIO5 (D1)  - Hijau (Success)
#define LED_BLUE        4   // GPIO4 (D2)  - Biru (Processing)

// Buzzer Configuration
#define BUZZER_PIN      14  // GPIO14 (D5)

// Variables
String lastCardUID = "";
unsigned long lastScanTime = 0;
const unsigned long SCAN_COOLDOWN = 3000; // 3 detik cooldown

void setup() {
  Serial.begin(115200);
  
  // Initialize pins
  pinMode(LED_RED, OUTPUT);
  pinMode(LED_GREEN, OUTPUT);
  pinMode(LED_BLUE, OUTPUT);
  pinMode(BUZZER_PIN, OUTPUT);
  
  // Turn off all LEDs
  setLEDStatus("off");
  
  // Initialize SPI and RFID
  SPI.begin();
  rfid.PCD_Init();
  
  // Connect to WiFi
  connectToWiFi();
  
  // Test server connection
  testServerConnection();
  
  Serial.println("=== RFID Absensi System Ready ===");
  Serial.println("Tap your RFID card...");
  
  // Ready indication
  blinkLED("green", 3);
  beepSuccess();
}

void loop() {
  // Check if WiFi is still connected
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("WiFi disconnected! Reconnecting...");
    connectToWiFi();
    return;
  }
  
  // Check for new RFID card
  if (rfid.PICC_IsNewCardPresent() && rfid.PICC_ReadCardSerial()) {
    String cardUID = getCardUID();
    
    // Check cooldown to prevent double scanning
    unsigned long currentTime = millis();
    if (cardUID == lastCardUID && (currentTime - lastScanTime) < SCAN_COOLDOWN) {
      Serial.println("Cooldown active, ignoring scan...");
      rfid.PICC_HaltA();
      return;
    }
    
    // Process absensi
    processAbsensi(cardUID);
    
    // Update last scan info
    lastCardUID = cardUID;
    lastScanTime = currentTime;
    
    // Halt RFID card
    rfid.PICC_HaltA();
    
    delay(1000); // Additional delay
  }
  
  delay(100);
}

void connectToWiFi() {
  Serial.print("Connecting to WiFi");
  WiFi.begin(ssid, password);
  
  setLEDStatus("blue");
  
  int attempts = 0;
  while (WiFi.status() != WL_CONNECTED && attempts < 30) {
    delay(500);
    Serial.print(".");
    attempts++;
  }
  
  if (WiFi.status() == WL_CONNECTED) {
    Serial.println();
    Serial.println("WiFi connected!");
    Serial.print("IP address: ");
    Serial.println(WiFi.localIP());
    
    setLEDStatus("green");
    beepSuccess();
    delay(1000);
    setLEDStatus("off");
  } else {
    Serial.println();
    Serial.println("WiFi connection failed!");
    
    setLEDStatus("red");
    beepError();
    delay(5000);
    
    // Restart ESP if WiFi fails
    ESP.restart();
  }
}

void testServerConnection() {
  Serial.println("Testing server connection...");
  
  if (WiFi.status() == WL_CONNECTED) {
    WiFiClient client;
    HTTPClient http;
    
    String testURL = String(serverURL) + "/test";
    http.begin(client, testURL);
    http.addHeader("Content-Type", "application/json");
    
    int httpResponseCode = http.GET();
    
    if (httpResponseCode == 200) {
      String response = http.getString();
      Serial.println("Server connection: OK");
      Serial.println("Response: " + response);
      
      setLEDStatus("green");
      beepSuccess();
    } else {
      Serial.println("Server connection: FAILED");
      Serial.println("HTTP Code: " + String(httpResponseCode));
      
      setLEDStatus("red");
      beepError();
    }
    
    http.end();
    delay(1000);
    setLEDStatus("off");
  }
}

String getCardUID() {
  String uid = "";
  for (byte i = 0; i < rfid.uid.size; i++) {
    uid += String(rfid.uid.uidByte[i] < 0x10 ? "0" : "");
    uid += String(rfid.uid.uidByte[i], HEX);
  }
  uid.toUpperCase();
  return uid;
}

void processAbsensi(String cardUID) {
  Serial.println("=== Processing Absensi ===");
  Serial.println("Card UID: " + cardUID);
  
  setLEDStatus("blue"); // Processing
  
  if (WiFi.status() == WL_CONNECTED) {
    WiFiClient client;
    HTTPClient http;
    
    // Prepare JSON payload
    DynamicJsonDocument jsonDoc(1024);
    jsonDoc["rfidCard"] = cardUID;
    jsonDoc["lokasi"] = "Kantor";
    
    String jsonString;
    serializeJson(jsonDoc, jsonString);
    
    // Send HTTP POST request
    String scanURL = String(serverURL) + "/scan";
    http.begin(client, scanURL);
    http.addHeader("Content-Type", "application/json");
    
    Serial.println("Sending request to: " + scanURL);
    Serial.println("Payload: " + jsonString);
    
    int httpResponseCode = http.POST(jsonString);
    String response = http.getString();
    
    Serial.println("HTTP Code: " + String(httpResponseCode));
    Serial.println("Response: " + response);
    
    // Parse response
    parseAbsensiResponse(httpResponseCode, response);
    
    http.end();
  } else {
    Serial.println("WiFi not connected!");
    handleError("WiFi Error", "No connection");
  }
  
  Serial.println("========================");
}

void parseAbsensiResponse(int httpCode, String response) {
  DynamicJsonDocument doc(1024);
  DeserializationError error = deserializeJson(doc, response);
  
  if (error) {
    Serial.println("JSON parsing failed!");
    handleError("JSON Error", "Parse failed");
    return;
  }
  
  bool success = doc["success"];
  String message = doc["message"];
  
  if (httpCode == 200 && success) {
    // Success response
    String nama = doc["nama"];
    String type = doc["type"]; // "masuk" atau "keluar"
    String waktu = doc["waktu"];
    String terlambat = doc["terlambat"];
    
    Serial.println("✅ SUCCESS: " + message);
    Serial.println("Nama: " + nama);
    Serial.println("Type: " + type);
    Serial.println("Waktu: " + waktu);
    
    if (terlambat != "null" && terlambat.length() > 0) {
      Serial.println("⚠️ Terlambat: " + terlambat);
      handleWarning(message, nama + " (" + terlambat + ")");
    } else {
      handleSuccess(message, nama + " - " + type);
    }
    
  } else {
    // Error response
    Serial.println("❌ ERROR: " + message);
    
    if (httpCode == 404) {
      handleError("Card Not Found", "Kartu tidak terdaftar");
    } else if (httpCode == 403) {
      String nama = doc["nama"];
      handleError("Access Denied", nama + " tidak aktif");
    } else if (httpCode == 400) {
      String nama = doc["nama"];
      String jamMasuk = doc["jam_masuk"];
      String jamKeluar = doc["jam_keluar"];
      handleError("Already Complete", nama + " sudah absen lengkap");
    } else {
      handleError("Server Error", message);
    }
  }
}

void handleSuccess(String title, String details) {
  Serial.println("✅ " + title + ": " + details);
  
  setLEDStatus("green");
  beepSuccess();
  
  delay(2000);
  setLEDStatus("off");
}

void handleWarning(String title, String details) {
  Serial.println("⚠️ " + title + ": " + details);
  
  // Blink green-red for warning (late)
  for (int i = 0; i < 3; i++) {
    setLEDStatus("green");
    delay(200);
    setLEDStatus("red");
    delay(200);
  }
  
  beepWarning();
  setLEDStatus("off");
}

void handleError(String title, String details) {
  Serial.println("❌ " + title + ": " + details);
  
  setLEDStatus("red");
  beepError();
  
  delay(3000);
  setLEDStatus("off");
}

void setLEDStatus(String status) {
  if (status == "red") {
    digitalWrite(LED_RED, HIGH);
    digitalWrite(LED_GREEN, LOW);
    digitalWrite(LED_BLUE, LOW);
  } else if (status == "green") {
    digitalWrite(LED_RED, LOW);
    digitalWrite(LED_GREEN, HIGH);
    digitalWrite(LED_BLUE, LOW);
  } else if (status == "blue") {
    digitalWrite(LED_RED, LOW);
    digitalWrite(LED_GREEN, LOW);
    digitalWrite(LED_BLUE, HIGH);
  } else {
    // off
    digitalWrite(LED_RED, LOW);
    digitalWrite(LED_GREEN, LOW);
    digitalWrite(LED_BLUE, LOW);
  }
}

void blinkLED(String color, int times) {
  for (int i = 0; i < times; i++) {
    setLEDStatus(color);
    delay(200);
    setLEDStatus("off");
    delay(200);
  }
}

void beepSuccess() {
  // Double beep for success
  digitalWrite(BUZZER_PIN, HIGH);
  delay(100);
  digitalWrite(BUZZER_PIN, LOW);
  delay(100);
  digitalWrite(BUZZER_PIN, HIGH);
  delay(100);
  digitalWrite(BUZZER_PIN, LOW);
}

void beepError() {
  // Long beep for error
  digitalWrite(BUZZER_PIN, HIGH);
  delay(1000);
  digitalWrite(BUZZER_PIN, LOW);
}

void beepWarning() {
  // Triple beep for warning
  for (int i = 0; i < 3; i++) {
    digitalWrite(BUZZER_PIN, HIGH);
    delay(150);
    digitalWrite(BUZZER_PIN, LOW);
    delay(150);
  }
}
```

## 📦 Library Dependencies

Tambahkan library ini di Arduino IDE:

```
1. ESP8266WiFi (Built-in)
2. ESP8266HTTPClient (Built-in)  
3. ArduinoJson (Install via Library Manager)
4. MFRC522 (Install via Library Manager)
```

## 🔧 Wiring Diagram

### NodeMCU + MFRC522 + LED + Buzzer

```
MFRC522    NodeMCU
========   =======
SDA    ->  D4 (GPIO2)
SCK    ->  D5 (GPIO14)
MOSI   ->  D7 (GPIO13)
MISO   ->  D6 (GPIO12)
IRQ    ->  Not connected
GND    ->  GND
RST    ->  D3 (GPIO0)
3.3V   ->  3V3

LED        NodeMCU
====       =======
Red    ->  D0 (GPIO16)
Green  ->  D1 (GPIO5)
Blue   ->  D2 (GPIO4)
GND    ->  GND

Buzzer     NodeMCU
======     =======
+      ->  D5 (GPIO14)
-      ->  GND
```

## ⚙️ Configuration

Edit konfigurasi di bagian atas kode:

```cpp
// WiFi Settings
const char* ssid = "Your_WiFi_Name";
const char* password = "Your_WiFi_Password";

// Server Settings
const char* serverURL = "http://absensi.fazcreateve.my.id/api/rfid";
// Untuk development lokal:
// const char* serverURL = "http://192.168.1.100:8000/api/rfid";
```

## 🎯 Fitur Kode:

1. **Auto WiFi Connection** - Otomatis connect dan reconnect WiFi
2. **Server Test** - Test koneksi ke server Laravel saat startup
3. **RFID Reading** - Baca UID kartu RFID MIFARE
4. **HTTP POST** - Kirim data absensi via POST request
5. **JSON Parsing** - Parse response dari server
6. **Visual Feedback** - LED indikator status (Hijau/Merah/Biru)
7. **Audio Feedback** - Buzzer untuk notifikasi
8. **Cooldown Protection** - Mencegah double scan dalam 3 detik
9. **Error Handling** - Handle berbagai jenis error
10. **Serial Monitor** - Debug info lengkap

## 🚀 Upload & Test:

1. **Install Libraries** di Arduino IDE
2. **Select Board**: NodeMCU 1.0 (ESP-12E Module)
3. **Upload** kode ke NodeMCU
4. **Open Serial Monitor** (115200 baud)
5. **Test scan** kartu RFID

## 📊 Serial Monitor Output Example:

```
=== RFID Absensi System Ready ===
Tap your RFID card...

=== Processing Absensi ===
Card UID: A1B2C3D4
Sending request to: http://absensi.fazcreateve.my.id/api/rfid/scan
Payload: {"rfidCard":"A1B2C3D4","lokasi":"Kantor"}
HTTP Code: 200
Response: {"success":true,"message":"Absen masuk berhasil","type":"masuk","nama":"John Doe","nip":"123456","waktu":"08:05:30","status":"HADIR","terlambat":null}
✅ SUCCESS: Absen masuk berhasil
Nama: John Doe
Type: masuk
Waktu: 08:05:30
========================
```