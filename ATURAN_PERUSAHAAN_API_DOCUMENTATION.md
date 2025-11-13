# API Management Aturan Perusahaan - PT Pencari Error Sejati

## Base URL
```
Production: https://yourdomain.com/api
Development: http://127.0.0.1:8000/api
```

## Authentication
Semua endpoint aturan perusahaan memerlukan autentikasi menggunakan **Laravel Sanctum Bearer Token** dan biasanya hanya dapat diakses oleh **ADMIN**.

### Header Required:
```http
Authorization: Bearer {your-token}
Content-Type: application/json
Accept: application/json
```

---

## Table of Contents
1. [List Aturan Perusahaan](#1-list-aturan-perusahaan)
2. [Get Active Rule](#2-get-active-rule)
3. [Get Rule Summary](#3-get-rule-summary)
4. [Detail Aturan](#4-detail-aturan)
5. [Create Aturan Baru](#5-create-aturan-baru)
6. [Update Aturan](#6-update-aturan)
7. [Delete Aturan](#7-delete-aturan)
8. [Activate Rule](#8-activate-rule)
9. [Deactivate Rule](#9-deactivate-rule)
10. [Duplicate Rule](#10-duplicate-rule)

---

## 1. List Aturan Perusahaan

### Endpoint:
```
GET /api/aturan-perusahaan
```

### Query Parameters:
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| is_active | boolean | No | Filter by status (true/false) |
| sort_by | string | No | Sort by field (default: created_at) |
| sort_order | string | No | Sort order: `asc` or `desc` (default: desc) |
| per_page | integer | No | Items per page (default: 15) |

### Example Requests:

```bash
# Get all rules
curl -X GET "http://127.0.0.1:8000/api/aturan-perusahaan" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"

# Get only active rule
curl -X GET "http://127.0.0.1:8000/api/aturan-perusahaan?is_active=true" \
  -H "Authorization: Bearer YOUR_TOKEN"

# Get inactive rules
curl -X GET "http://127.0.0.1:8000/api/aturan-perusahaan?is_active=false" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Response Success (200):
```json
{
    "success": true,
    "message": "Data aturan perusahaan berhasil diambil",
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": "uuid-1",
                "jam_masuk_kerja": "08:00",
                "jam_pulang_kerja": "17:00",
                "toleransi_terlambat": 15,
                "potongan_per_menit_terlambat": "1000.00",
                "potongan_per_hari_alpha": "100000.00",
                "tarif_lembur_per_jam": "50000.00",
                "tarif_lembur_libur": "75000.00",
                "bonus_kehadiran_penuh": "500000.00",
                "minimal_hadir_bonus": 22,
                "hari_kerja_per_bulan": 22,
                "is_active": true,
                "created_at": "2025-11-13T14:39:57.000000Z",
                "updated_at": "2025-11-13T14:39:57.000000Z"
            }
        ],
        "per_page": 15,
        "total": 1
    }
}
```

---

## 2. Get Active Rule

### Endpoint:
```
GET /api/aturan-perusahaan/active
```

### Description:
Mendapatkan aturan perusahaan yang sedang aktif (is_active = true). Hanya boleh ada 1 aturan aktif di satu waktu.

### Example Request:
```bash
curl -X GET "http://127.0.0.1:8000/api/aturan-perusahaan/active" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

### Response Success (200):
```json
{
    "success": true,
    "message": "Aturan perusahaan aktif berhasil diambil",
    "data": {
        "id": "uuid-1",
        "jam_masuk_kerja": "08:00",
        "jam_pulang_kerja": "17:00",
        "toleransi_terlambat": 15,
        "potongan_per_menit_terlambat": "1000.00",
        "potongan_per_hari_alpha": "100000.00",
        "tarif_lembur_per_jam": "50000.00",
        "tarif_lembur_libur": "75000.00",
        "bonus_kehadiran_penuh": "500000.00",
        "minimal_hadir_bonus": 22,
        "hari_kerja_per_bulan": 22,
        "is_active": true
    }
}
```

### Response Error (404):
```json
{
    "success": false,
    "message": "Tidak ada aturan perusahaan yang aktif",
    "data": null
}
```

---

## 3. Get Rule Summary

### Endpoint:
```
GET /api/aturan-perusahaan/summary
```

### Description:
Mendapatkan ringkasan aturan perusahaan yang aktif beserta contoh perhitungan untuk berbagai skenario (keterlambatan, alpha, lembur, bonus).

### Example Request:
```bash
curl -X GET "http://127.0.0.1:8000/api/aturan-perusahaan/summary" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

### Response Success (200):
```json
{
    "success": true,
    "message": "Summary aturan perusahaan berhasil diambil",
    "data": {
        "aturan_aktif": {
            "id": "uuid-1",
            "jam_masuk_kerja": "08:00",
            "jam_pulang_kerja": "17:00",
            "toleransi_terlambat": 15,
            "potongan_per_menit_terlambat": "1000.00",
            "potongan_per_hari_alpha": "100000.00",
            "tarif_lembur_per_jam": "50000.00",
            "tarif_lembur_libur": "75000.00",
            "bonus_kehadiran_penuh": "500000.00",
            "minimal_hadir_bonus": 22,
            "hari_kerja_per_bulan": 22,
            "is_active": true
        },
        "contoh_perhitungan": {
            "keterlambatan": {
                "deskripsi": "Jika karyawan terlambat 30 menit",
                "menit_terlambat": 30,
                "toleransi": 15,
                "menit_dihitung": 15,
                "potongan": 15000,
                "format": "Rp 15.000"
            },
            "alpha": {
                "deskripsi": "Jika karyawan alpha 1 hari",
                "potongan_per_hari": "100000.00",
                "format": "Rp 100.000"
            },
            "lembur": {
                "deskripsi": "Jika karyawan lembur 3 jam di hari kerja",
                "jam_lembur": 3,
                "tarif_per_jam": "50000.00",
                "total_kompensasi": 150000,
                "format": "Rp 150.000"
            },
            "lembur_libur": {
                "deskripsi": "Jika karyawan lembur 3 jam di hari libur",
                "jam_lembur": 3,
                "tarif_per_jam": "75000.00",
                "total_kompensasi": 225000,
                "format": "Rp 225.000"
            },
            "bonus_kehadiran": {
                "deskripsi": "Jika karyawan hadir penuh 22 hari atau lebih",
                "minimal_hadir": 22,
                "bonus": "500000.00",
                "format": "Rp 500.000"
            }
        },
        "jam_kerja": {
            "masuk": "08:00",
            "pulang": "17:00",
            "total_jam_kerja": "9 jam 0 menit"
        }
    }
}
```

---

## 4. Detail Aturan

### Endpoint:
```
GET /api/aturan-perusahaan/{id}
```

### Example Request:
```bash
curl -X GET "http://127.0.0.1:8000/api/aturan-perusahaan/uuid-1" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

### Response Success (200):
```json
{
    "success": true,
    "message": "Detail aturan perusahaan berhasil diambil",
    "data": {
        "id": "uuid-1",
        "jam_masuk_kerja": "08:00",
        "jam_pulang_kerja": "17:00",
        "toleransi_terlambat": 15,
        "potongan_per_menit_terlambat": "1000.00",
        "potongan_per_hari_alpha": "100000.00",
        "tarif_lembur_per_jam": "50000.00",
        "tarif_lembur_libur": "75000.00",
        "bonus_kehadiran_penuh": "500000.00",
        "minimal_hadir_bonus": 22,
        "hari_kerja_per_bulan": 22,
        "is_active": true,
        "created_at": "2025-11-13T14:39:57.000000Z",
        "updated_at": "2025-11-13T14:39:57.000000Z"
    }
}
```

---

## 5. Create Aturan Baru

### Endpoint:
```
POST /api/aturan-perusahaan
```

### Request Body:
```json
{
    "jam_masuk_kerja": "08:00",
    "jam_pulang_kerja": "17:00",
    "toleransi_terlambat": 15,
    "potongan_per_menit_terlambat": 1000,
    "potongan_per_hari_alpha": 100000,
    "tarif_lembur_per_jam": 50000,
    "tarif_lembur_libur": 75000,
    "bonus_kehadiran_penuh": 500000,
    "minimal_hadir_bonus": 22,
    "hari_kerja_per_bulan": 22,
    "is_active": true
}
```

### Field Validation:
| Field | Type | Required | Rules |
|-------|------|----------|-------|
| jam_masuk_kerja | string | **Yes** | max:10, format HH:MM (e.g., 08:00) |
| jam_pulang_kerja | string | **Yes** | max:10, format HH:MM (e.g., 17:00) |
| toleransi_terlambat | integer | No | min:0 (menit, default: 15) |
| potongan_per_menit_terlambat | decimal | No | min:0 (default: 0) |
| potongan_per_hari_alpha | decimal | No | min:0 (default: 0) |
| tarif_lembur_per_jam | decimal | No | min:0 (default: 0) |
| tarif_lembur_libur | decimal | No | min:0 (default: 0) |
| bonus_kehadiran_penuh | decimal | No | min:0 (default: 0) |
| minimal_hadir_bonus | integer | No | min:0 (default: 22) |
| hari_kerja_per_bulan | integer | No | min:1 (default: 22) |
| is_active | boolean | No | default: false |

### Important Notes:
- **Format Jam**: Harus HH:MM (contoh: 08:00, 17:30)
- **Aturan Aktif**: Jika `is_active = true`, semua aturan lain akan otomatis di-nonaktifkan
- **Default Values**: Jika field optional tidak diisi, akan menggunakan nilai default

### Example Request:
```bash
curl -X POST "http://127.0.0.1:8000/api/aturan-perusahaan" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "jam_masuk_kerja": "08:00",
    "jam_pulang_kerja": "17:00",
    "toleransi_terlambat": 10,
    "potongan_per_menit_terlambat": 2000,
    "potongan_per_hari_alpha": 150000,
    "tarif_lembur_per_jam": 60000,
    "tarif_lembur_libur": 90000,
    "bonus_kehadiran_penuh": 750000,
    "minimal_hadir_bonus": 22,
    "hari_kerja_per_bulan": 22,
    "is_active": false
  }'
```

### Response Success (201):
```json
{
    "success": true,
    "message": "Aturan perusahaan berhasil dibuat",
    "data": {
        "id": "new-uuid",
        "jam_masuk_kerja": "08:00",
        "jam_pulang_kerja": "17:00",
        "toleransi_terlambat": 10,
        "potongan_per_menit_terlambat": "2000.00",
        "potongan_per_hari_alpha": "150000.00",
        "tarif_lembur_per_jam": "60000.00",
        "tarif_lembur_libur": "90000.00",
        "bonus_kehadiran_penuh": "750000.00",
        "minimal_hadir_bonus": 22,
        "hari_kerja_per_bulan": 22,
        "is_active": false,
        "created_at": "2025-11-13T15:00:00.000000Z",
        "updated_at": "2025-11-13T15:00:00.000000Z"
    }
}
```

### Response Error (422):
```json
{
    "success": false,
    "message": "Format jam_masuk_kerja tidak valid. Gunakan format HH:MM (contoh: 08:00)",
    "errors": {
        "jam_masuk_kerja": [
            "Format harus HH:MM"
        ]
    }
}
```

---

## 6. Update Aturan

### Endpoint:
```
PUT /api/aturan-perusahaan/{id}
```

### Request Body (all fields optional):
```json
{
    "jam_masuk_kerja": "07:30",
    "jam_pulang_kerja": "16:30",
    "toleransi_terlambat": 20,
    "potongan_per_menit_terlambat": 1500,
    "is_active": true
}
```

### Important Notes:
- **Partial Update**: Hanya field yang dikirim yang akan diupdate
- **Aktivasi Otomatis**: Jika `is_active = true`, aturan lain akan di-nonaktifkan
- **Validasi Format**: Format jam tetap harus HH:MM jika diupdate

### Example Request:
```bash
curl -X PUT "http://127.0.0.1:8000/api/aturan-perusahaan/uuid-1" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "toleransi_terlambat": 20,
    "potongan_per_menit_terlambat": 1500
  }'
```

### Response Success (200):
```json
{
    "success": true,
    "message": "Aturan perusahaan berhasil diupdate",
    "data": {
        "id": "uuid-1",
        "jam_masuk_kerja": "08:00",
        "jam_pulang_kerja": "17:00",
        "toleransi_terlambat": 20,
        "potongan_per_menit_terlambat": "1500.00",
        "updated_at": "2025-11-13T15:30:00.000000Z"
    }
}
```

---

## 7. Delete Aturan

### Endpoint:
```
DELETE /api/aturan-perusahaan/{id}
```

### Important Notes:
- **Tidak bisa delete aturan aktif jika hanya ada 1**: Sistem akan mencegah penghapusan aturan aktif terakhir
- **Auto-activate**: Jika menghapus aturan aktif, sistem akan otomatis mengaktifkan aturan terbaru lainnya

### Example Request:
```bash
curl -X DELETE "http://127.0.0.1:8000/api/aturan-perusahaan/uuid-1" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

### Response Success (200):
```json
{
    "success": true,
    "message": "Aturan perusahaan berhasil dihapus"
}
```

### Response Error (422):
```json
{
    "success": false,
    "message": "Tidak dapat menghapus aturan perusahaan yang sedang aktif dan satu-satunya. Buat aturan baru terlebih dahulu."
}
```

---

## 8. Activate Rule

### Endpoint:
```
POST /api/aturan-perusahaan/{id}/activate
```

### Description:
Mengaktifkan aturan perusahaan tertentu. Aturan lain yang aktif akan otomatis di-nonaktifkan.

### Example Request:
```bash
curl -X POST "http://127.0.0.1:8000/api/aturan-perusahaan/uuid-2/activate" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

### Response Success (200):
```json
{
    "success": true,
    "message": "Aturan perusahaan berhasil diaktifkan",
    "data": {
        "id": "uuid-2",
        "jam_masuk_kerja": "07:30",
        "jam_pulang_kerja": "16:30",
        "is_active": true,
        "updated_at": "2025-11-13T16:00:00.000000Z"
    }
}
```

---

## 9. Deactivate Rule

### Endpoint:
```
POST /api/aturan-perusahaan/{id}/deactivate
```

### Description:
Menonaktifkan aturan perusahaan tertentu. Tidak bisa menonaktifkan jika tidak ada aturan aktif lainnya.

### Example Request:
```bash
curl -X POST "http://127.0.0.1:8000/api/aturan-perusahaan/uuid-1/deactivate" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

### Response Success (200):
```json
{
    "success": true,
    "message": "Aturan perusahaan berhasil dinonaktifkan",
    "data": {
        "id": "uuid-1",
        "is_active": false,
        "updated_at": "2025-11-13T16:05:00.000000Z"
    }
}
```

### Response Error (422):
```json
{
    "success": false,
    "message": "Tidak dapat menonaktifkan aturan terakhir yang aktif. Aktifkan aturan lain terlebih dahulu."
}
```

---

## 10. Duplicate Rule

### Endpoint:
```
POST /api/aturan-perusahaan/{id}/duplicate
```

### Description:
Menduplikasi aturan perusahaan yang ada. Duplikat akan dibuat dengan `is_active = false`.

### Example Request:
```bash
curl -X POST "http://127.0.0.1:8000/api/aturan-perusahaan/uuid-1/duplicate" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

### Response Success (201):
```json
{
    "success": true,
    "message": "Aturan perusahaan berhasil diduplikasi",
    "data": {
        "id": "new-uuid",
        "jam_masuk_kerja": "08:00",
        "jam_pulang_kerja": "17:00",
        "toleransi_terlambat": 15,
        "potongan_per_menit_terlambat": "1000.00",
        "is_active": false,
        "created_at": "2025-11-13T16:10:00.000000Z"
    }
}
```

---

## Business Logic

### 1. Perhitungan Keterlambatan:
```
Jika karyawan datang jam 08:30:
- Jam masuk kerja: 08:00
- Toleransi: 15 menit (gratis)
- Terlambat: 30 menit
- Menit dihitung: 30 - 15 = 15 menit
- Potongan: 15 × Rp 1.000 = Rp 15.000
```

### 2. Potongan Alpha:
```
Jika karyawan alpha 1 hari:
- Potongan langsung: Rp 100.000/hari
- Tidak ada toleransi
```

### 3. Lembur Hari Kerja:
```
Jika karyawan lembur 3 jam di hari kerja:
- Tarif: Rp 50.000/jam
- Total: 3 × Rp 50.000 = Rp 150.000
```

### 4. Lembur Hari Libur:
```
Jika karyawan lembur 3 jam di hari libur:
- Tarif: Rp 75.000/jam (lebih tinggi)
- Total: 3 × Rp 75.000 = Rp 225.000
```

### 5. Bonus Kehadiran:
```
Jika karyawan hadir ≥ 22 hari dalam sebulan:
- Bonus: Rp 500.000
- Syarat: minimal 22 hari hadir (tidak alpha)
```

---

## Use Cases

### Use Case 1: Setup Awal Aturan Perusahaan
```bash
# Admin membuat aturan pertama kali
POST /api/aturan-perusahaan
{
    "jam_masuk_kerja": "08:00",
    "jam_pulang_kerja": "17:00",
    "toleransi_terlambat": 15,
    "potongan_per_menit_terlambat": 1000,
    "potongan_per_hari_alpha": 100000,
    "is_active": true
}
```

### Use Case 2: Update Potongan Keterlambatan
```bash
# HRD ingin menaikkan denda keterlambatan
PUT /api/aturan-perusahaan/uuid-1
{
    "potongan_per_menit_terlambat": 2000
}
```

### Use Case 3: Ganti Jam Kerja (Shift Pagi)
```bash
# Perusahaan pindah ke shift pagi
POST /api/aturan-perusahaan
{
    "jam_masuk_kerja": "07:00",
    "jam_pulang_kerja": "16:00",
    "toleransi_terlambat": 10,
    "potongan_per_menit_terlambat": 1000,
    "is_active": true  # Akan otomatis nonaktifkan aturan lama
}
```

### Use Case 4: Duplicate untuk Aturan Alternatif
```bash
# Buat aturan alternatif dari yang sudah ada
POST /api/aturan-perusahaan/uuid-1/duplicate

# Lalu edit sesuai kebutuhan
PUT /api/aturan-perusahaan/new-uuid
{
    "jam_masuk_kerja": "09:00",
    "jam_pulang_kerja": "18:00"
}
```

### Use Case 5: Cek Aturan yang Berlaku
```bash
# Cek aturan apa yang sedang aktif
GET /api/aturan-perusahaan/active

# Atau lihat contoh perhitungannya
GET /api/aturan-perusahaan/summary
```

### Use Case 6: Testing Aturan Baru (Non-Active)
```bash
# Buat aturan baru untuk testing tanpa aktifkan
POST /api/aturan-perusahaan
{
    "jam_masuk_kerja": "08:30",
    "jam_pulang_kerja": "17:30",
    "is_active": false  # Tidak aktif dulu
}

# Setelah yakin, aktifkan
POST /api/aturan-perusahaan/new-uuid/activate
```

---

## Integration dengan Sistem Lain

### 1. Digunakan oleh Absensi API:
Ketika mencatat absensi, sistem akan mengambil aturan aktif untuk:
- Menghitung keterlambatan
- Menghitung potongan
- Menentukan jam kerja normal

```php
$aturan = AturanPerusahaan::where('is_active', true)->first();
$menitTerlambat = // calculate...
$potongan = max(0, $menitTerlambat - $aturan->toleransi_terlambat) 
          * $aturan->potongan_per_menit_terlambat;
```

### 2. Digunakan oleh Lembur API:
Untuk menghitung kompensasi lembur:
```php
$aturan = AturanPerusahaan::active()->first();
$isLibur = // check if holiday...
$tarif = $isLibur ? $aturan->tarif_lembur_libur : $aturan->tarif_lembur_per_jam;
$kompensasi = $jamLembur * $tarif;
```

### 3. Digunakan oleh Gaji API:
Untuk menghitung bonus kehadiran dan potongan:
```php
$aturan = AturanPerusahaan::active()->first();
$bonus = $jumlahHadir >= $aturan->minimal_hadir_bonus 
       ? $aturan->bonus_kehadiran_penuh 
       : 0;
```

---

## Error Responses

### 401 Unauthorized:
```json
{
    "message": "Unauthenticated."
}
```

### 404 Not Found:
```json
{
    "success": false,
    "message": "Aturan perusahaan tidak ditemukan",
    "error": "No query results for model..."
}
```

### 422 Validation Error:
```json
{
    "success": false,
    "message": "Validasi gagal",
    "errors": {
        "jam_masuk_kerja": [
            "The jam masuk kerja field is required."
        ],
        "potongan_per_menit_terlambat": [
            "The potongan per menit terlambat must be at least 0."
        ]
    }
}
```

### 500 Internal Server Error:
```json
{
    "success": false,
    "message": "Gagal membuat aturan perusahaan",
    "error": "Database connection error..."
}
```

---

## Kompatibilitas Shared Hosting cPanel

### ✅ Fitur yang Kompatibel:
1. **Laravel Sanctum** - Token-based authentication
2. **Database Transactions** - MySQL standard operations
3. **UUID Primary Keys** - MySQL 5.7+ support
4. **REST API** - Standard HTTP methods
5. **JSON Responses** - No special extensions needed
6. **Simple CRUD** - No background jobs required

### 📋 Best Practices untuk cPanel:

1. **Single Active Rule**:
   - Sistem enforce hanya 1 aturan aktif untuk performa
   - Menghindari konflik perhitungan
   - Cache-friendly untuk shared hosting

2. **Validation di Controller**:
   - Semua validasi dilakukan sebelum save
   - Mencegah bad data masuk database
   - Mengurangi database operations

3. **DB Transactions**:
   - Aktivasi/deaktivasi menggunakan transaction
   - Rollback otomatis jika error
   - Data integrity terjaga

---

## Testing dengan Postman

### Postman Collection:
```json
{
    "info": {
        "name": "Aturan Perusahaan Management API",
        "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
    },
    "auth": {
        "type": "bearer",
        "bearer": [
            {
                "key": "token",
                "value": "{{token}}",
                "type": "string"
            }
        ]
    },
    "item": [
        {
            "name": "Login",
            "request": {
                "method": "POST",
                "url": "{{base_url}}/api/login",
                "body": {
                    "mode": "raw",
                    "raw": "{\"email\":\"admin@pencarierror.com\",\"password\":\"password123\"}"
                }
            }
        },
        {
            "name": "Get Active Rule",
            "request": {
                "method": "GET",
                "url": "{{base_url}}/api/aturan-perusahaan/active"
            }
        },
        {
            "name": "Get Summary",
            "request": {
                "method": "GET",
                "url": "{{base_url}}/api/aturan-perusahaan/summary"
            }
        },
        {
            "name": "Create Rule",
            "request": {
                "method": "POST",
                "url": "{{base_url}}/api/aturan-perusahaan",
                "body": {
                    "mode": "raw",
                    "raw": "{\"jam_masuk_kerja\":\"08:00\",\"jam_pulang_kerja\":\"17:00\",\"is_active\":false}"
                }
            }
        },
        {
            "name": "Activate Rule",
            "request": {
                "method": "POST",
                "url": "{{base_url}}/api/aturan-perusahaan/{{rule_id}}/activate"
            }
        }
    ]
}
```

### Quick Test dengan cURL:
```bash
# 1. Login
TOKEN=$(curl -s -X POST "http://127.0.0.1:8000/api/login" \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@pencarierror.com","password":"password123"}' \
  | jq -r '.data.token')

# 2. Get active rule
curl -X GET "http://127.0.0.1:8000/api/aturan-perusahaan/active" \
  -H "Authorization: Bearer $TOKEN" | jq

# 3. Get summary (dengan contoh perhitungan)
curl -X GET "http://127.0.0.1:8000/api/aturan-perusahaan/summary" \
  -H "Authorization: Bearer $TOKEN" | jq

# 4. Create new rule
curl -X POST "http://127.0.0.1:8000/api/aturan-perusahaan" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "jam_masuk_kerja": "08:00",
    "jam_pulang_kerja": "17:00",
    "toleransi_terlambat": 15,
    "potongan_per_menit_terlambat": 1000,
    "is_active": false
  }' | jq
```

---

## Field Definitions

### Penjelasan Setiap Field:

| Field | Deskripsi | Contoh | Catatan |
|-------|-----------|--------|---------|
| `jam_masuk_kerja` | Jam masuk kerja standar | "08:00" | Format HH:MM |
| `jam_pulang_kerja` | Jam pulang kerja standar | "17:00" | Format HH:MM |
| `toleransi_terlambat` | Toleransi keterlambatan tanpa denda (menit) | 15 | Gratis 15 menit pertama |
| `potongan_per_menit_terlambat` | Denda per menit keterlambatan (Rupiah) | 1000 | Setelah toleransi |
| `potongan_per_hari_alpha` | Potongan untuk 1 hari alpha (Rupiah) | 100000 | Langsung potong gaji |
| `tarif_lembur_per_jam` | Tarif lembur hari kerja (Rupiah) | 50000 | Per jam lembur |
| `tarif_lembur_libur` | Tarif lembur hari libur (Rupiah) | 75000 | Biasanya lebih tinggi |
| `bonus_kehadiran_penuh` | Bonus jika hadir penuh (Rupiah) | 500000 | Incentive kehadiran |
| `minimal_hadir_bonus` | Minimal hari hadir untuk dapat bonus | 22 | Hari dalam sebulan |
| `hari_kerja_per_bulan` | Jumlah hari kerja standar per bulan | 22 | Untuk perhitungan payroll |
| `is_active` | Status aturan (aktif/tidak) | true/false | Hanya 1 yang boleh aktif |

---

## Support
Untuk pertanyaan atau issue, silakan hubungi:
- Email: support@pencarierror.com
- Phone: +62 21 1234 5678
