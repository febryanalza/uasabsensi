# RFID Auto-Register API Documentation

## 📡 API Endpoints untuk NodeMCU - RFID Card Registration

Base URL: `http://your-domain.com/api`

---

## 1. Auto-Register RFID Card (Main Endpoint untuk NodeMCU)
**Endpoint:** `POST /api/rfid`

Endpoint untuk auto-register kartu RFID baru. Jika kartu sudah terdaftar, akan return data existing card.

**Request Body:**
```json
{
    "rfidUid": "A1B2C3D4"
}
```

**Request dari NodeMCU (.ino):**
```cpp
jsonDoc["rfidUid"] = uid;  // UID kartu RFID yang di-scan
```

### Response Scenarios:

#### ✅ Success - Kartu Baru Berhasil Didaftarkan (201)
```json
{
    "success": true,
    "message": "Kartu berhasil didaftarkan",
    "data": {
        "cardNumber": "A1B2C3D4",
        "cardType": "MIFARE",
        "status": "AVAILABLE",
        "isNew": true,
        "isAssigned": false,
        "registeredAt": "2024-01-15 10:30:00"
    }
}
```

#### ✅ Success - Kartu Sudah Terdaftar (Belum di-assign) (200)
```json
{
    "success": true,
    "message": "Kartu sudah terdaftar",
    "data": {
        "cardNumber": "A1B2C3D4",
        "cardType": "MIFARE",
        "status": "AVAILABLE",
        "isNew": false,
        "isAssigned": false,
        "assignedAt": null,
        "karyawan": null
    }
}
```

#### ✅ Success - Kartu Sudah Terdaftar & Di-assign ke Karyawan (200)
```json
{
    "success": true,
    "message": "Kartu sudah terdaftar",
    "data": {
        "cardNumber": "A1B2C3D4",
        "cardType": "MIFARE",
        "status": "ASSIGNED",
        "isNew": false,
        "isAssigned": true,
        "assignedAt": "2024-01-15 08:00:00",
        "karyawan": {
            "id": "uuid-karyawan",
            "nip": "123456",
            "nama": "John Doe",
            "jabatan": "Developer",
            "departemen": "IT"
        }
    }
}
```

#### ❌ Error - Data Tidak Valid (422)
```json
{
    "success": false,
    "message": "Data tidak valid",
    "errors": {
        "rfidUid": ["The rfid uid field is required."]
    }
}
```

---

## 2. Get List RFID Cards
**Endpoint:** `GET /api/rfid`

Mengambil daftar semua kartu RFID yang terdaftar.

**Query Parameters:**
- `status` (optional): Filter by status (AVAILABLE, ASSIGNED, DAMAGED, LOST, INACTIVE)
- `per_page` (optional): Jumlah data per halaman (default: 15)
- `page` (optional): Nomor halaman

**Example:**
```
GET /api/rfid?status=AVAILABLE&per_page=20
```

**Response:**
```json
{
    "success": true,
    "message": "Data kartu RFID berhasil diambil",
    "data": [
        {
            "id": "uuid",
            "card_number": "A1B2C3D4",
            "card_type": "MIFARE",
            "status": "AVAILABLE",
            "assigned_at": null,
            "notes": "Auto-registered via NodeMCU",
            "karyawan_id": null,
            "karyawan": null,
            "created_at": "2024-01-15T10:30:00.000000Z",
            "updated_at": "2024-01-15T10:30:00.000000Z"
        }
    ],
    "pagination": {
        "total": 50,
        "per_page": 15,
        "current_page": 1,
        "last_page": 4
    }
}
```

---

## 3. Get Detail RFID Card
**Endpoint:** `GET /api/rfid/{cardNumber}`

Mengambil detail spesifik kartu RFID.

**Example:**
```
GET /api/rfid/A1B2C3D4
```

**Response:**
```json
{
    "success": true,
    "message": "Detail kartu RFID",
    "data": {
        "id": "uuid",
        "cardNumber": "A1B2C3D4",
        "cardType": "MIFARE",
        "status": "ASSIGNED",
        "assignedAt": "2024-01-15 08:00:00",
        "notes": "Auto-registered via NodeMCU",
        "isAssigned": true,
        "karyawan": {
            "id": "uuid-karyawan",
            "nip": "123456",
            "nama": "John Doe",
            "email": "john@example.com",
            "jabatan": "Developer",
            "departemen": "IT",
            "status": "AKTIF"
        },
        "createdAt": "2024-01-15 10:30:00",
        "updatedAt": "2024-01-15 10:30:00"
    }
}
```

**Response - Kartu Tidak Ditemukan (404):**
```json
{
    "success": false,
    "message": "Kartu tidak ditemukan"
}
```

---

## 4. Delete RFID Card
**Endpoint:** `DELETE /api/rfid/{cardNumber}`

Menghapus kartu RFID dari database. Kartu yang sudah di-assign tidak bisa dihapus.

**Example:**
```
DELETE /api/rfid/A1B2C3D4
```

**Response - Success:**
```json
{
    "success": true,
    "message": "Kartu berhasil dihapus"
}
```

**Response - Kartu Sedang Digunakan (400):**
```json
{
    "success": false,
    "message": "Kartu sedang digunakan, tidak dapat dihapus"
}
```

---

## 🔄 Logic Flow Auto-Register

### Skenario 1: Kartu Baru (Belum Terdaftar)
1. NodeMCU scan kartu RFID → dapat UID
2. POST ke `/api/rfid` dengan `rfidUid`
3. Server cek: Apakah UID sudah ada di database?
4. **Tidak ada** → Insert ke tabel `available_rfid` dengan status AVAILABLE
5. Response: "Kartu berhasil didaftarkan" + `isNew: true`
6. **LCD NodeMCU:** "NEW CARD! Registered OK!"

### Skenario 2: Kartu Sudah Terdaftar (Belum Di-assign)
1. NodeMCU scan kartu RFID
2. POST ke `/api/rfid`
3. Server cek: UID sudah ada
4. **Ada, status AVAILABLE** → Return data existing card
5. Response: "Kartu sudah terdaftar" + `isNew: false, isAssigned: false`
6. **LCD NodeMCU:** "Already Exist Available"

### Skenario 3: Kartu Sudah Terdaftar & Di-assign ke Karyawan
1. NodeMCU scan kartu RFID
2. POST ke `/api/rfid`
3. Server cek: UID sudah ada dan `karyawan_id` tidak null
4. **Ada, status ASSIGNED** → Return dengan data karyawan
5. Response: "Kartu sudah terdaftar" + `isNew: false, isAssigned: true` + data karyawan
6. **LCD NodeMCU:** "Already Exist & Assigned"

---

## 📊 Database Schema

### Tabel: available_rfid
```sql
CREATE TABLE available_rfid (
    id CHAR(36) PRIMARY KEY,
    card_number VARCHAR(255) UNIQUE NOT NULL,  -- UID dari RFID
    card_type VARCHAR(100) NULL,                -- MIFARE, NFC, dll
    status ENUM('AVAILABLE','ASSIGNED','DAMAGED','LOST','INACTIVE'),
    assigned_at DATETIME NULL,
    notes TEXT NULL,
    karyawan_id CHAR(36) NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Status Kartu RFID:
- **AVAILABLE**: Kartu terdaftar, belum di-assign ke karyawan
- **ASSIGNED**: Kartu sudah di-assign ke karyawan
- **DAMAGED**: Kartu rusak
- **LOST**: Kartu hilang
- **INACTIVE**: Kartu tidak aktif

---

## 🔧 Konfigurasi NodeMCU (.ino)

### Development (Localhost):
```cpp
const char* serverHost = "192.168.1.100";
const int serverPort = 8000;
const char* apiEndpoint = "/api/rfid";
const bool useHTTPS = false;
```

### Production (Shared Hosting):
```cpp
const char* serverHost = "yourdomain.com";
const int serverPort = 443;  // HTTPS
const char* apiEndpoint = "/api/rfid";
const bool useHTTPS = true;
```

### Vercel Deployment:
```cpp
const char* serverHost = "absensi-delta-amber.vercel.app";
const int serverPort = 443;
const char* apiEndpoint = "/api/rfid";
const bool useHTTPS = true;
```

---

## 🧪 Testing dengan Postman

### Test Auto-Register Kartu Baru
```
POST http://localhost:8000/api/rfid
Content-Type: application/json

{
    "rfidUid": "A1B2C3D4"
}
```

### Test Scan Kartu yang Sudah Ada
```
POST http://localhost:8000/api/rfid
Content-Type: application/json

{
    "rfidUid": "A1B2C3D4"
}
```
Response akan menunjukkan `isNew: false`

### Get List Kartu
```
GET http://localhost:8000/api/rfid
```

### Get List Kartu dengan Filter
```
GET http://localhost:8000/api/rfid?status=AVAILABLE
```

### Get Detail Kartu
```
GET http://localhost:8000/api/rfid/A1B2C3D4
```

### Delete Kartu
```
DELETE http://localhost:8000/api/rfid/A1B2C3D4
```

---

## 🔗 Integrasi dengan Sistem Absensi

Setelah kartu terdaftar, admin dapat:

1. **Assign kartu ke karyawan** via web admin:
```sql
-- Update karyawan dengan RFID
UPDATE karyawan 
SET rfid_card_number = 'A1B2C3D4' 
WHERE id = 'uuid-karyawan';

-- Update status kartu RFID
UPDATE available_rfid 
SET status = 'ASSIGNED', 
    karyawan_id = 'uuid-karyawan',
    assigned_at = NOW()
WHERE card_number = 'A1B2C3D4';
```

2. **Gunakan untuk absensi**:
   - Kartu yang sudah di-assign bisa digunakan di endpoint `/api/rfid/scan` untuk absensi

---

## 🌐 Deployment ke Shared Hosting cPanel

### 1. CORS Configuration (Jika Diperlukan)
Tambahkan di `bootstrap/app.php`:
```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->api(prepend: [
        \Illuminate\Http\Middleware\HandleCors::class,
    ]);
})
```

### 2. .htaccess untuk HTTPS Redirect (Optional)
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
</IfModule>
```

### 3. Testing Production
```bash
# Test dari NodeMCU
POST https://yourdomain.com/api/rfid
{
    "rfidUid": "A1B2C3D4"
}
```

---

## 🐛 Troubleshooting

### NodeMCU gagal POST ke server
1. Cek WiFi connection
2. Pastikan `serverHost` benar
3. Jika HTTPS, pastikan `wifiClientSecure.setInsecure()` aktif
4. Test manual dengan browser: `https://yourdomain.com/api/rfid/test`

### Response "Data tidak valid"
1. Cek format JSON yang dikirim
2. Pastikan field name: `rfidUid` (bukan rfidCard atau uid)
3. Cek Serial Monitor untuk melihat payload yang dikirim

### Kartu selalu "isNew: true" padahal sudah di-scan
1. Cek format UID (harus uppercase)
2. Cek database: `SELECT * FROM available_rfid WHERE card_number = 'A1B2C3D4'`
3. Pastikan tidak ada spasi di UID

---

## ✨ Fitur API

✅ Auto-register kartu RFID baru
✅ Deteksi kartu yang sudah terdaftar
✅ Info kartu sudah di-assign atau belum
✅ Response simple untuk NodeMCU (hemat memory)
✅ Support HTTPS untuk production
✅ Support pagination untuk list kartu
✅ CRUD lengkap untuk manajemen kartu
✅ Compatible dengan shared hosting cPanel
✅ No authentication required (untuk endpoint register)

---

## 📈 Statistics Tracking di NodeMCU

NodeMCU akan tracking:
- **Total Scanned**: Total kartu yang di-scan
- **New Registered**: Jumlah kartu baru yang terdaftar
- **Already Exist**: Jumlah kartu yang sudah ada

Display di LCD setiap beberapa scan.

---

**Made with ❤️ for IoT RFID Registration System**
