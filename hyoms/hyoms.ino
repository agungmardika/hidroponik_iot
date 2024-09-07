#include <Wire.h>
#include <LiquidCrystal_I2C.h>
#include <OneWire.h>
#include <DallasTemperature.h>
#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>

// Inisialisasi LCD
LiquidCrystal_I2C lcd(0x27, 16, 2); // Sesuaikan alamat I2C dan ukuran LCD

// Inisialisasi sensor suhu 1
int sensorPin1 = D4;
OneWire oneWire1(sensorPin1);
DallasTemperature sensors1(&oneWire1);

// Inisialisasi sensor suhu 2
int sensorPin2 = D5;
OneWire oneWire2(sensorPin2);
DallasTemperature sensors2(&oneWire2);

// Inisialisasi sensor TDS
int tds_sensor = A0;

// Inisialisasi water pump
int pump = D8;
bool pumpActive = false;
unsigned long pumpStartTime = 0;
const unsigned long pumpDuration = 5000; // Durasi pompa aktif dalam milidetik (5 detik)

// Konfigurasi WiFi
const char* ssid = "Siswa SMK Telkom Bjb";
const char* password = "skatel@hebat";
const char* serverUrl = "http://10.215.0.88/hidroponik_iot/api.php"; // Sesuaikan dengan URL server Anda

unsigned long previousMillis = 0;   // Waktu terakhir tampilan diperbarui
const long interval = 2000;         // Interval antara perubahan tampilan (2 detik)

int displayState = 0;               // Variabel untuk melacak tampilan saat ini (0 untuk sensor suhu 1, 1 untuk sensor suhu 2)

float temperatureC1 = 0.0;          // Variabel untuk menyimpan nilai suhu sensor 1
float temperatureC2 = 0.0;          // Variabel untuk menyimpan nilai suhu sensor 2

bool connected = false;             // Status koneksi WiFi
unsigned long connectionStartTime = 0; // Waktu mulai koneksi WiFi

void setup() {
  Serial.begin(115200);

  // Inisialisasi LCD
  lcd.begin();
  lcd.backlight();

  // Inisialisasi sensor suhu
  sensors1.begin();
  sensors2.begin();

  // Inisialisasi pin pompa
  pinMode(pump, OUTPUT);
  digitalWrite(pump, LOW);  // Pastikan pompa mati saat memulai

  // Tampilkan pesan "Sedang Menghubungkan" di LCD
  lcd.setCursor(0, 0);
  lcd.print("Sedang menghubung");
  
  // Inisialisasi koneksi WiFi
  WiFi.begin(ssid, password);
  connectionStartTime = millis();
}

void loop() {
  unsigned long currentMillis = millis();

  if (!connected) {
    // Periksa status koneksi WiFi
    if (WiFi.status() != WL_CONNECTED) {
      // Tetap menampilkan pesan "Sedang Menghubungkan" hingga terhubung
      lcd.setCursor(0, 0);
      lcd.print("Sedang menghubung");

      // Jika sudah terhubung ke WiFi
      if (WiFi.status() == WL_CONNECTED) {
        connected = true;
        lcd.clear();
        lcd.setCursor(0, 0);
        lcd.print("Sudah terhubung");
        connectionStartTime = millis();  // Catat waktu saat menampilkan pesan "Sudah terhubung"
      }
    } else {
      connected = true;
      lcd.clear();
      lcd.setCursor(0, 0);
      lcd.print("Sudah terhubung");
      connectionStartTime = millis();  // Catat waktu saat menampilkan pesan "Sudah terhubung"
    }

    // Jika sudah terhubung dan pesan "Sudah terhubung" ditampilkan selama 2 detik
    if (connected && (currentMillis - connectionStartTime >= 2000)) {
      lcd.clear();  // Hapus pesan "Sudah terhubung"
    }
  } else {
    // Periksa apakah sudah waktunya untuk memperbarui tampilan
    if (currentMillis - previousMillis >= interval) {
      // Simpan waktu terakhir tampilan diperbarui
      previousMillis = currentMillis;

      // Hapus tampilan sebelumnya
      lcd.setCursor(0, 0);
      lcd.print("                ");  // Menghapus 16 karakter untuk membersihkan layar

      // Tampilkan nilai sensor suhu 1 atau suhu 2 sesuai dengan displayState
      if (displayState == 0) {
        // Meminta pembacaan suhu dari sensor suhu 1
        sensors1.requestTemperatures();
        temperatureC1 = sensors1.getTempCByIndex(0);

        lcd.setCursor(0, 0);
        lcd.print("Suhu 1: ");
        lcd.print(temperatureC1);
        lcd.print(" C");
      } else if (displayState == 1) {
        // Meminta pembacaan suhu dari sensor suhu 2
        sensors2.requestTemperatures();
        temperatureC2 = sensors2.getTempCByIndex(0);

        lcd.setCursor(0, 0);
        lcd.print("Suhu 2: ");
        lcd.print(temperatureC2);
        lcd.print(" C");
      }

      // Toggle displayState antara 0 dan 1 untuk bergantian antara sensor suhu 1 dan suhu 2
      displayState = 1 - displayState;

      // Tampilkan nilai TDS di baris kedua LCD
      int sensorValue = analogRead(tds_sensor);
      float tdsValue = map(sensorValue, 0, 1023, 0, 1000);

      lcd.setCursor(0, 1);
      lcd.print("TDS: ");
      lcd.print(tdsValue);
      lcd.print(" PPM");

      Serial.print(tdsValue);
      Serial.print("TDS = ");
      Serial.println(" PPM");

      // Kirim data ke server web
      if (WiFi.status() == WL_CONNECTED) {
        WiFiClient client;
        HTTPClient http;

        // Mengirim data menggunakan metode GET
        String address = serverUrl;
        address += "?suhu=";
        address += String(temperatureC1);
        address += "&suhu2=";
        address += String(temperatureC2);
        address += "&tds=";
        address += String(tdsValue);

        http.begin(client, address);  // Spesifikasikan destinasi permintaan
        int httpCode = http.GET();    // Kirim permintaan

        if (httpCode > 0) {           // Periksa kode balasan
          String payload = http.getString();  // Dapatkan payload balasan permintaan
          Serial.print("HTTP Response code: ");
          Serial.println(httpCode);
          Serial.println(payload);
        } else {
          Serial.print("Error on HTTP request. HTTP Error code: ");
          Serial.println(httpCode);
        }

        http.end();  // Tutup koneksi
      } else {
        Serial.println("WiFi Disconnected");
      }

      // Kontrol pompa berdasarkan nilai TDS
      if (tdsValue < 560) {
        if (!pumpActive) {
          digitalWrite(pump, HIGH);  // Aktifkan pompa
          pumpActive = true;
          pumpStartTime = currentMillis;  // Catat waktu pompa dinyalakan
        }
      } else if (tdsValue > 840) {
        if (pumpActive) {
          digitalWrite(pump, LOW);  // Matikan pompa
          pumpActive = false;
        }
      }

      // Matikan pompa setelah 5 detik
      if (pumpActive && (currentMillis - pumpStartTime >= pumpDuration)) {
        digitalWrite(pump, LOW);  // Matikan pompa
        pumpActive = false;
      }
    }

    delay(4230000);
  }
}