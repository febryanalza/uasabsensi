# Contoh Penggunaan API RFID untuk Absensi

## 📡 Base URL
```
http://localhost:8000/api/rfid  (Development)
https://absensi.fazcreateve.my.id/api/rfid  (Production)
```

## 🚀 1. Test Koneksi API

### Request:
```http
GET /api/rfid/test
```

### Response JSON:
```json
{
    "success": true,
    "message": "RFID API Ready",
    "timestamp": "2024-01-15 10:30:00",
    "server_time": "10:30:00"
}
```

---

## 🏷️ 2. Registrasi Kartu RFID Baru

### Request:
```http
POST /api/rfid
Content-Type: application/json

{
    "rfidUid": "A1B2C3D4"
}
```

### Response JSON - Kartu Baru:
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

### Response JSON - Kartu Sudah Terdaftar:
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
        "karyawan": {
            "id": "uuid-karyawan",
            "nip": "123456",
            "nama": "John Doe",
            "email": "john@company.com",
            "jabatan": "Software Engineer",
            "departemen": "IT"
        }
    }
}
```

---

## ✅ 3. Scan RFID untuk Absensi (Utama)

### Request:
```http
POST /api/rfid/scan
Content-Type: application/json

{
    "rfidCard": "A1B2C3D4",
    "lokasi": "Kantor"
}
```

### Response JSON - Absen Masuk (Pertama kali):
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

### Response JSON - Absen Masuk Terlambat:
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

### Response JSON - Absen Keluar:
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

---

## ❌ 4. Error Response Examples

### Kartu Tidak Terdaftar:
```json
{
    "success": false,
    "message": "Kartu tidak terdaftar",
    "rfid": "A1B2C3D4"
}
```

### Karyawan Tidak Aktif:
```json
{
    "success": false,
    "message": "Karyawan tidak aktif",
    "nama": "John Doe"
}
```

### Sudah Absen Lengkap (Ditolak):
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

## 📊 5. Cek Status Absensi Karyawan

### Request:
```http
GET /api/rfid/status/A1B2C3D4
```

### Response JSON - Belum Absen:
```json
{
    "success": true,
    "message": "Belum absen hari ini",
    "nama": "John Doe",
    "sudah_masuk": false,
    "sudah_keluar": false
}
```

### Response JSON - Sudah Absen Masuk:
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

### Response JSON - Sudah Absen Lengkap:
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

## 📝 6. Daftar Semua Kartu RFID

### Request:
```http
GET /api/rfid
```

### Query Parameters (Optional):
- `status=AVAILABLE` - Filter by status
- `page=1` - Pagination

### Response JSON:
```json
{
    "success": true,
    "data": [
        {
            "id": "uuid-card-1",
            "cardNumber": "A1B2C3D4",
            "cardType": "MIFARE",
            "status": "ASSIGNED",
            "karyawan": {
                "id": "uuid-karyawan",
                "nip": "123456",
                "nama": "John Doe",
                "email": "john@company.com",
                "jabatan": "Software Engineer",
                "departemen": "IT",
                "status": "AKTIF"
            },
            "createdAt": "2024-01-15 10:30:00",
            "updatedAt": "2024-01-15 10:30:00"
        }
    ],
    "pagination": {
        "current_page": 1,
        "total": 10,
        "per_page": 15
    }
}
```

---

## 🔍 7. Detail Kartu RFID Spesifik

### Request:
```http
GET /api/rfid/A1B2C3D4
```

### Response JSON:
```json
{
    "success": true,
    "data": {
        "id": "uuid-card",
        "cardNumber": "A1B2C3D4",
        "cardType": "MIFARE",
        "status": "ASSIGNED",
        "assignedAt": "2024-01-15 10:30:00",
        "notes": "Auto-registered via NodeMCU",
        "karyawan": {
            "id": "uuid-karyawan",
            "nip": "123456",
            "nama": "John Doe",
            "email": "john@company.com",
            "jabatan": "Software Engineer",
            "departemen": "IT",
            "status": "AKTIF"
        },
        "createdAt": "2024-01-15 10:30:00",
        "updatedAt": "2024-01-15 10:30:00"
    }
}
```

---

## 🗑️ 8. Hapus Kartu RFID

### Request:
```http
DELETE /api/rfid/A1B2C3D4
```

### Response JSON:
```json
{
    "success": true,
    "message": "Kartu RFID berhasil dihapus"
}
```

---

## 🔄 9. Flow Lengkap Penggunaan

### Step 1: Registrasi Kartu Baru
```bash
curl -X POST http://localhost:8000/api/rfid \
  -H "Content-Type: application/json" \
  -d '{"rfidUid": "A1B2C3D4"}'
```

### Step 2: Assign ke Karyawan (Manual via Database)
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

### Step 3: Test Absensi Masuk
```bash
curl -X POST http://localhost:8000/api/rfid/scan \
  -H "Content-Type: application/json" \
  -d '{"rfidCard": "A1B2C3D4", "lokasi": "Kantor"}'
```

### Step 4: Test Absensi Keluar (Scan Lagi)
```bash
curl -X POST http://localhost:8000/api/rfid/scan \
  -H "Content-Type: application/json" \
  -d '{"rfidCard": "A1B2C3D4", "lokasi": "Kantor"}'
```

### Step 5: Cek Status
```bash
curl -X GET http://localhost:8000/api/rfid/status/A1B2C3D4
```

---

## 🏷️ 10. Status Code Reference

| Code | Meaning |
|------|---------|
| 200 | ✅ Success |
| 201 | ✅ Created (Kartu baru) |
| 400 | ❌ Bad Request (Sudah absen lengkap) |
| 403 | ❌ Forbidden (Karyawan tidak aktif) |
| 404 | ❌ Not Found (Kartu/Karyawan tidak ditemukan) |
| 422 | ❌ Validation Error |
| 500 | ❌ Server Error |

---

## 📋 11. Field Description

### Absensi Fields:
- `type`: "masuk" atau "keluar"
- `nama`: Nama karyawan
- `nip`: Nomor Induk Pegawai
- `waktu`: Waktu scan saat ini (H:i:s)
- `status`: "HADIR", "IZIN", "SAKIT", "ALFA"
- `terlambat`: Durasi keterlambatan (contoh: "5 menit")
- `jam_masuk`: Waktu absen masuk (H:i:s)
- `jam_keluar`: Waktu absen keluar (H:i:s)

### Card Status:
- `AVAILABLE`: Kartu tersedia, belum di-assign
- `ASSIGNED`: Kartu sudah di-assign ke karyawan
- `DAMAGED`: Kartu rusak
- `LOST`: Kartu hilang
- `INACTIVE`: Kartu tidak aktif

---

## 🎯 Tips Implementasi:

1. **NodeMCU**: Gunakan POST `/api/rfid/scan` untuk absensi utama
2. **Web Admin**: Gunakan GET `/api/rfid` untuk manajemen kartu
3. **Mobile App**: Gunakan GET `/api/rfid/status/{card}` untuk cek status
4. **Auto Register**: POST `/api/rfid` untuk registrasi otomatis kartu baru
5. **Error Handling**: Selalu cek field `success` dalam response