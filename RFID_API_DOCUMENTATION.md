# RFID Absensi API Documentation

## 📡 API Endpoints untuk NodeMCU ESP8266

Base URL: `http://your-domain.com/api/rfid`

---

## 1. Test Connection
**Endpoint:** `GET /api/rfid/test`

Test koneksi antara NodeMCU dan server Laravel.

**Response:**
```json
{
    "success": true,
    "message": "RFID API Ready",
    "timestamp": "2024-01-15 10:30:00",
    "server_time": "10:30:00"
}
```

---

## 2. Scan RFID Card (Absensi)
**Endpoint:** `POST /api/rfid/scan`

Endpoint utama untuk absensi masuk/keluar menggunakan RFID.

**Request Body:**
```json
{
    "rfidCard": "A1B2C3D4",
    "lokasi": "Kantor"
}
```

**Request dari NodeMCU (.ino):**
```cpp
jsonDoc["rfidCard"] = uid;  // UID kartu RFID
jsonDoc["lokasi"] = "Kantor";
```

### Response Scenarios:

#### ✅ Success - Absen Masuk (Pertama kali hari ini)
```json
{
    "success": true,
    "message": "Absen masuk berhasil",
    "type": "masuk",
    "nama": "John Doe",
    "nip": "123456",
    "waktu": "08:05:30",
    "status": "HADIR",
    "terlambat": null
}
```

#### ✅ Success - Absen Masuk Terlambat
```json
{
    "success": true,
    "message": "Absen masuk berhasil",
    "type": "masuk",
    "nama": "John Doe",
    "nip": "123456",
    "waktu": "08:20:00",
    "status": "HADIR",
    "terlambat": "5 menit"
}
```

#### ✅ Success - Absen Keluar
```json
{
    "success": true,
    "message": "Absen keluar berhasil",
    "type": "keluar",
    "nama": "John Doe",
    "nip": "123456",
    "waktu": "17:05:00",
    "jam_masuk": "08:05:30",
    "jam_keluar": "17:05:00"
}
```

#### ❌ Error - Kartu Tidak Terdaftar (404)
```json
{
    "success": false,
    "message": "Kartu tidak terdaftar",
    "rfid": "A1B2C3D4"
}
```

#### ❌ Error - Karyawan Tidak Aktif (403)
```json
{
    "success": false,
    "message": "Karyawan tidak aktif",
    "nama": "John Doe"
}
```

#### ❌ Error - Sudah Absen Lengkap (400)
```json
{
    "success": false,
    "message": "Sudah absen lengkap",
    "nama": "John Doe",
    "jam_masuk": "08:05:30",
    "jam_keluar": "17:05:00"
}
```

---

## 3. Cek Status Absensi
**Endpoint:** `GET /api/rfid/status/{rfidCard}`

Cek status absensi karyawan hari ini berdasarkan RFID card.

**Example:**
```
GET /api/rfid/status/A1B2C3D4
```

**Response - Belum Absen:**
```json
{
    "success": true,
    "message": "Belum absen hari ini",
    "nama": "John Doe",
    "sudah_masuk": false,
    "sudah_keluar": false
}
```

**Response - Sudah Absen Masuk:**
```json
{
    "success": true,
    "nama": "John Doe",
    "nip": "123456",
    "sudah_masuk": true,
    "sudah_keluar": false,
    "jam_masuk": "08:05:30",
    "jam_keluar": null,
    "status": "HADIR"
}
```

**Response - Sudah Absen Lengkap:**
```json
{
    "success": true,
    "nama": "John Doe",
    "nip": "123456",
    "sudah_masuk": true,
    "sudah_keluar": true,
    "jam_masuk": "08:05:30",
    "jam_keluar": "17:05:00",
    "status": "HADIR"
}
```

---

## 🔄 Logic Flow Absensi

### Skenario 1: Absen Masuk (Pertama kali hari ini)
1. NodeMCU scan kartu RFID → dapat UID
2. POST ke `/api/rfid/scan` dengan `rfidCard` dan `lokasi`
3. Server cek: Apakah sudah ada absensi hari ini?
4. **Tidak ada** → Buat record baru dengan `jam_masuk`
5. Hitung keterlambatan jika lewat dari jam masuk + toleransi
6. Response: "Absen masuk berhasil"

### Skenario 2: Absen Keluar (Kedua kali hari ini)
1. NodeMCU scan kartu RFID yang sama
2. POST ke `/api/rfid/scan`
3. Server cek: Sudah ada absensi hari ini?
4. **Ada, tapi `jam_keluar` masih NULL** → Update dengan `jam_keluar`
5. Response: "Absen keluar berhasil"

### Skenario 3: Scan Ketiga Kali (Ditolak)
1. NodeMCU scan kartu RFID
2. POST ke `/api/rfid/scan`
3. Server cek: Sudah ada absensi dengan `jam_masuk` dan `jam_keluar`?
4. **Ya, sudah lengkap** → Tolak request
5. Response: "Sudah absen lengkap"

---

## ⚙️ Konfigurasi Aturan Perusahaan

Sebelum menggunakan API, pastikan sudah ada data di tabel `aturan_perusahaan`:

```sql
INSERT INTO aturan_perusahaan (
    id, 
    jam_masuk_kerja, 
    jam_pulang_kerja, 
    toleransi_terlambat,
    potongan_per_menit_terlambat,
    is_active
) VALUES (
    UUID(),
    '08:00',
    '17:00',
    15,
    5000.00,
    TRUE
);
```

---

## 📝 Setup Karyawan dengan RFID

Tambahkan karyawan dengan RFID card number:

```sql
INSERT INTO karyawan (
    id,
    nip,
    rfid_card_number,
    nama,
    email,
    jabatan,
    departemen,
    status
) VALUES (
    UUID(),
    '123456',
    'A1B2C3D4',
    'John Doe',
    'john@example.com',
    'Developer',
    'IT',
    'AKTIF'
);
```

---

## 🔧 Konfigurasi NodeMCU (.ino)

Update setting di file Arduino:

```cpp
// WiFi
const char* ssid = "Your_WiFi_SSID";
const char* password = "Your_WiFi_Password";

// Server Laravel
const char* serverHost = "192.168.1.100";  // Ganti dengan IP/domain server
const int serverPort = 8000;               // Port Laravel (production: 80)
const char* apiEndpoint = "/api/rfid/scan";
```

### Untuk Production (Shared Hosting):
```cpp
const char* serverHost = "yourdomain.com";
const int serverPort = 80;  // atau 443 untuk HTTPS
const char* apiEndpoint = "/api/rfid/scan";
```

---

## 🌐 Deployment ke Shared Hosting cPanel

### 1. Update .env
```env
APP_URL=https://yourdomain.com
DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=your_db
DB_USERNAME=your_user
DB_PASSWORD=your_pass
```

### 2. CORS Configuration (Optional)
Jika NodeMCU mengalami masalah CORS, tambahkan di `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->api(prepend: [
        \Illuminate\Http\Middleware\HandleCors::class,
    ]);
})
```

### 3. .htaccess untuk RFID API
Pastikan API dapat diakses tanpa autentikasi.

---

## 🧪 Testing dengan Postman

### Test Connection
```
GET http://localhost:8000/api/rfid/test
```

### Test Absensi Masuk
```
POST http://localhost:8000/api/rfid/scan
Content-Type: application/json

{
    "rfidCard": "A1B2C3D4",
    "lokasi": "Kantor"
}
```

### Test Absensi Keluar (scan lagi)
```
POST http://localhost:8000/api/rfid/scan
Content-Type: application/json

{
    "rfidCard": "A1B2C3D4",
    "lokasi": "Kantor"
}
```

---

## 🐛 Troubleshooting

### NodeMCU tidak bisa connect ke API
1. Pastikan IP server benar
2. Cek firewall server (allow port 8000)
3. Test manual dengan browser: `http://server-ip:8000/api/rfid/test`

### Kartu tidak terdaftar
1. Cek UID kartu di Serial Monitor
2. Pastikan format UID uppercase (A1B2C3D4)
3. Cek database: `SELECT * FROM karyawan WHERE rfid_card_number = 'A1B2C3D4'`

### Error 500
1. Cek log Laravel: `storage/logs/laravel.log`
2. Pastikan tabel `aturan_perusahaan` ada data dan `is_active = 1`

---

## 📊 Database Schema Reference

### Tabel: karyawan
- `rfid_card_number` VARCHAR(255) UNIQUE → UID kartu RFID
- `status` ENUM('AKTIF','CUTI','RESIGN') → Harus AKTIF

### Tabel: absensi
- `jam_masuk` DATETIME → Waktu tap pertama
- `jam_keluar` DATETIME → Waktu tap kedua
- `rfid_masuk` VARCHAR(255) → UID kartu saat masuk
- `rfid_keluar` VARCHAR(255) → UID kartu saat keluar
- `menit_terlambat` INT → Otomatis dihitung
- `potongan_terlambat` DECIMAL → Otomatis dihitung

### Tabel: aturan_perusahaan
- `jam_masuk_kerja` VARCHAR(10) → Contoh: "08:00"
- `jam_pulang_kerja` VARCHAR(10) → Contoh: "17:00"
- `toleransi_terlambat` INT → Menit toleransi (default: 15)
- `is_active` BOOLEAN → Harus TRUE

---

## ✨ Fitur API

✅ Auto-detect masuk/keluar (tanpa parameter tambahan)
✅ Simple response untuk NodeMCU (hemat memory)
✅ Hitung keterlambatan otomatis
✅ Validasi status karyawan
✅ Validasi kartu terdaftar
✅ Cooldown 3 detik di NodeMCU (prevent double scan)
✅ Compatible dengan shared hosting cPanel
✅ No authentication required (untuk RFID endpoint)

---

**Made with ❤️ for IoT Absensi System**
