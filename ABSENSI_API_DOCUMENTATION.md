# API Management Absensi Karyawan - PT Pencari Error Sejati

## Base URL
```
Production: https://yourdomain.com/api
Development: http://127.0.0.1:8000/api
```

## Authentication
Semua endpoint absensi memerlukan autentikasi menggunakan **Laravel Sanctum Bearer Token**.

### Header Required:
```http
Authorization: Bearer {your-token}
Content-Type: application/json
Accept: application/json
```

---

## Table of Contents
1. [List Absensi dengan Filter](#1-list-absensi-dengan-filter)
2. [Detail Absensi](#2-detail-absensi)
3. [Create Absensi Baru](#3-create-absensi-baru)
4. [Update Absensi](#4-update-absensi)
5. [Delete Absensi](#5-delete-absensi)
6. [Batalkan Absensi](#6-batalkan-absensi)
7. [Statistik Absensi](#7-statistik-absensi)
8. [Rekap Absensi per Karyawan](#8-rekap-absensi-per-karyawan)
9. [Bulk Create Absensi](#9-bulk-create-absensi)

---

## 1. List Absensi dengan Filter

### Endpoint:
```
GET /api/absensi
```

### Query Parameters:
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| karyawan_id | uuid | No | Filter by specific karyawan |
| status | enum | No | Filter by status: `HADIR`, `IZIN`, `SAKIT`, `ALPHA`, `CUTI` |
| tanggal_dari | date | No | Filter from date (format: YYYY-MM-DD) |
| tanggal_sampai | date | No | Filter until date (format: YYYY-MM-DD) |
| bulan | integer | No | Filter by month (1-12) |
| tahun | integer | No | Filter by year (e.g., 2025) |
| departemen | string | No | Filter by departemen |
| terlambat | boolean | No | Filter hanya yang terlambat (true/false) |
| pulang_cepat | boolean | No | Filter hanya yang pulang cepat (true/false) |
| search | string | No | Search by nama karyawan atau NIP |
| sort_by | string | No | Sort by field (default: tanggal) |
| sort_order | string | No | Sort order: `asc` or `desc` (default: desc) |
| per_page | integer | No | Items per page (default: 15) |
| page | integer | No | Page number (default: 1) |

### Example Requests:

```bash
# Get all absensi (paginated)
curl -X GET "http://127.0.0.1:8000/api/absensi" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"

# Filter by karyawan
curl -X GET "http://127.0.0.1:8000/api/absensi?karyawan_id=uuid-karyawan" \
  -H "Authorization: Bearer YOUR_TOKEN"

# Filter by status HADIR
curl -X GET "http://127.0.0.1:8000/api/absensi?status=HADIR" \
  -H "Authorization: Bearer YOUR_TOKEN"

# Filter by date range
curl -X GET "http://127.0.0.1:8000/api/absensi?tanggal_dari=2025-11-01&tanggal_sampai=2025-11-30" \
  -H "Authorization: Bearer YOUR_TOKEN"

# Filter by bulan dan tahun
curl -X GET "http://127.0.0.1:8000/api/absensi?bulan=11&tahun=2025" \
  -H "Authorization: Bearer YOUR_TOKEN"

# Filter karyawan yang terlambat
curl -X GET "http://127.0.0.1:8000/api/absensi?terlambat=true" \
  -H "Authorization: Bearer YOUR_TOKEN"

# Filter by departemen IT
curl -X GET "http://127.0.0.1:8000/api/absensi?departemen=IT" \
  -H "Authorization: Bearer YOUR_TOKEN"

# Search by nama
curl -X GET "http://127.0.0.1:8000/api/absensi?search=Budi" \
  -H "Authorization: Bearer YOUR_TOKEN"

# Combined filters
curl -X GET "http://127.0.0.1:8000/api/absensi?bulan=11&tahun=2025&status=HADIR&departemen=IT&per_page=20" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Response Success (200):
```json
{
    "success": true,
    "message": "Data absensi berhasil diambil",
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": "uuid-1",
                "karyawan_id": "uuid-karyawan",
                "tanggal": "2025-11-13",
                "jam_masuk": "2025-11-13 08:05:00",
                "jam_keluar": "2025-11-13 17:00:00",
                "status": "HADIR",
                "keterangan": null,
                "lokasi": "Kantor Pusat",
                "foto_masuk": null,
                "foto_keluar": null,
                "rfid_masuk": "A1B2C3D4",
                "rfid_keluar": "A1B2C3D4",
                "menit_terlambat": 5,
                "menit_pulang_cepat": 0,
                "potongan_terlambat": "5000.00",
                "potongan_alpha": "0.00",
                "created_at": "2025-11-13T08:05:00.000000Z",
                "updated_at": "2025-11-13T17:00:00.000000Z",
                "karyawan": {
                    "id": "uuid-karyawan",
                    "nip": "EMP001",
                    "nama": "Budi Santoso",
                    "email": "admin@pencarierror.com",
                    "jabatan": "CEO",
                    "departemen": "IT",
                    "status": "AKTIF"
                }
            }
        ],
        "per_page": 15,
        "total": 540
    }
}
```

---

## 2. Detail Absensi

### Endpoint:
```
GET /api/absensi/{id}
```

### Example Request:
```bash
curl -X GET "http://127.0.0.1:8000/api/absensi/uuid-1" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

### Response Success (200):
```json
{
    "success": true,
    "message": "Detail absensi berhasil diambil",
    "data": {
        "id": "uuid-1",
        "karyawan_id": "uuid-karyawan",
        "tanggal": "2025-11-13",
        "jam_masuk": "2025-11-13 08:05:00",
        "jam_keluar": "2025-11-13 17:00:00",
        "status": "HADIR",
        "keterangan": null,
        "lokasi": "Kantor Pusat",
        "menit_terlambat": 5,
        "menit_pulang_cepat": 0,
        "potongan_terlambat": "5000.00",
        "potongan_alpha": "0.00",
        "karyawan": {
            "id": "uuid-karyawan",
            "nip": "EMP001",
            "nama": "Budi Santoso",
            "departemen": "IT"
        }
    }
}
```

---

## 3. Create Absensi Baru

### Endpoint:
```
POST /api/absensi
```

### Request Body:
```json
{
    "karyawan_id": "uuid-karyawan",
    "tanggal": "2025-11-13",
    "jam_masuk": "2025-11-13 08:05:00",
    "jam_keluar": "2025-11-13 17:00:00",
    "status": "HADIR",
    "keterangan": "Masuk normal",
    "lokasi": "Kantor Pusat",
    "foto_masuk": "/storage/absensi/masuk_20251113.jpg",
    "foto_keluar": "/storage/absensi/keluar_20251113.jpg",
    "rfid_masuk": "A1B2C3D4",
    "rfid_keluar": "A1B2C3D4"
}
```

### Field Validation:
| Field | Type | Required | Rules |
|-------|------|----------|-------|
| karyawan_id | uuid | **Yes** | must exist in karyawan table |
| tanggal | date | **Yes** | format: YYYY-MM-DD |
| jam_masuk | datetime | No | format: YYYY-MM-DD HH:MM:SS |
| jam_keluar | datetime | No | format: YYYY-MM-DD HH:MM:SS, after jam_masuk |
| status | enum | **Yes** | HADIR, IZIN, SAKIT, ALPHA, CUTI |
| keterangan | text | No | - |
| lokasi | string | No | max:255 |
| foto_masuk | string | No | max:255 (path to image) |
| foto_keluar | string | No | max:255 (path to image) |
| rfid_masuk | string | No | max:255 |
| rfid_keluar | string | No | max:255 |

### Notes:
- **Automatic Calculation**: Sistem otomatis menghitung `menit_terlambat` dan `potongan_terlambat` berdasarkan `jam_masuk_kerja` dari tabel `aturan_perusahaan`
- **Unique Constraint**: Satu karyawan hanya bisa punya satu absensi per tanggal
- **Status ALPHA**: Otomatis mendapat potongan sesuai `potongan_alpha` dari aturan perusahaan

### Example Request:
```bash
curl -X POST "http://127.0.0.1:8000/api/absensi" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "karyawan_id": "uuid-karyawan",
    "tanggal": "2025-11-13",
    "jam_masuk": "2025-11-13 08:05:00",
    "jam_keluar": "2025-11-13 17:00:00",
    "status": "HADIR",
    "lokasi": "Kantor Pusat"
  }'
```

### Response Success (201):
```json
{
    "success": true,
    "message": "Absensi berhasil dicatat",
    "data": {
        "id": "new-uuid",
        "karyawan_id": "uuid-karyawan",
        "tanggal": "2025-11-13",
        "jam_masuk": "2025-11-13 08:05:00",
        "jam_keluar": "2025-11-13 17:00:00",
        "status": "HADIR",
        "menit_terlambat": 5,
        "potongan_terlambat": "5000.00",
        "karyawan": {
            "nip": "EMP001",
            "nama": "Budi Santoso"
        }
    }
}
```

### Response Error (422):
```json
{
    "success": false,
    "message": "Absensi untuk karyawan ini pada tanggal tersebut sudah ada",
    "existing_data": {
        "id": "existing-uuid",
        "tanggal": "2025-11-13",
        "status": "HADIR"
    }
}
```

---

## 4. Update Absensi

### Endpoint:
```
PUT /api/absensi/{id}
```

### Request Body (all fields optional):
```json
{
    "jam_masuk": "2025-11-13 08:00:00",
    "jam_keluar": "2025-11-13 17:00:00",
    "status": "HADIR",
    "keterangan": "Update jam masuk",
    "lokasi": "Kantor Pusat",
    "foto_masuk": "/storage/absensi/masuk_updated.jpg",
    "foto_keluar": "/storage/absensi/keluar_updated.jpg"
}
```

### Notes:
- **Automatic Recalculation**: Sistem otomatis menghitung ulang `menit_terlambat` dan potongan saat `jam_masuk` atau `status` diupdate
- Tidak bisa mengubah `karyawan_id` atau `tanggal`

### Example Request:
```bash
curl -X PUT "http://127.0.0.1:8000/api/absensi/uuid-1" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "jam_keluar": "2025-11-13 18:00:00",
    "keterangan": "Lembur hingga jam 6 sore"
  }'
```

### Response Success (200):
```json
{
    "success": true,
    "message": "Data absensi berhasil diupdate",
    "data": {
        "id": "uuid-1",
        "jam_keluar": "2025-11-13 18:00:00",
        "keterangan": "Lembur hingga jam 6 sore",
        "updated_at": "2025-11-13T18:05:00.000000Z"
    }
}
```

---

## 5. Delete Absensi

### Endpoint:
```
DELETE /api/absensi/{id}
```

### Example Request:
```bash
curl -X DELETE "http://127.0.0.1:8000/api/absensi/uuid-1" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

### Response Success (200):
```json
{
    "success": true,
    "message": "Absensi Budi Santoso tanggal 2025-11-13 berhasil dihapus"
}
```

---

## 6. Batalkan Absensi

### Endpoint:
```
POST /api/absensi/{id}/cancel
```

### Description:
Membatalkan absensi dengan mengubah status menjadi ALPHA dan menambahkan keterangan pembatalan.

### Request Body:
```json
{
    "keterangan_batal": "Karyawan tidak hadir, dibatalkan oleh HRD"
}
```

### Field Validation:
| Field | Type | Required | Rules |
|-------|------|----------|-------|
| keterangan_batal | string | **Yes** | max:500 |

### Example Request:
```bash
curl -X POST "http://127.0.0.1:8000/api/absensi/uuid-1/cancel" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "keterangan_batal": "Karyawan tidak hadir, dibatalkan oleh HRD"
  }'
```

### Response Success (200):
```json
{
    "success": true,
    "message": "Absensi berhasil dibatalkan",
    "data": {
        "id": "uuid-1",
        "status": "ALPHA",
        "keterangan": "[DIBATALKAN] Karyawan tidak hadir, dibatalkan oleh HRD | Keterangan sebelumnya: Masuk normal"
    }
}
```

---

## 7. Statistik Absensi

### Endpoint:
```
GET /api/absensi/statistics
```

### Query Parameters:
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| tanggal_dari | date | No | Filter from date (YYYY-MM-DD) |
| tanggal_sampai | date | No | Filter until date (YYYY-MM-DD) |
| bulan | integer | No | Filter by month (1-12) |
| tahun | integer | No | Filter by year |

### Example Requests:

```bash
# Get overall statistics
curl -X GET "http://127.0.0.1:8000/api/absensi/statistics" \
  -H "Authorization: Bearer YOUR_TOKEN"

# Statistics for November 2025
curl -X GET "http://127.0.0.1:8000/api/absensi/statistics?bulan=11&tahun=2025" \
  -H "Authorization: Bearer YOUR_TOKEN"

# Statistics for date range
curl -X GET "http://127.0.0.1:8000/api/absensi/statistics?tanggal_dari=2025-11-01&tanggal_sampai=2025-11-30" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Response Success (200):
```json
{
    "success": true,
    "message": "Statistik absensi berhasil diambil",
    "data": {
        "total_absensi": 540,
        "by_status": {
            "hadir": 450,
            "izin": 35,
            "sakit": 25,
            "alpha": 20,
            "cuti": 10
        },
        "persentase_hadir": 83.33,
        "kedisiplinan": {
            "total_terlambat": 85,
            "total_pulang_cepat": 12,
            "total_potongan_terlambat": 425000.00,
            "total_potongan_alpha": 2000000.00
        },
        "karyawan_rajin": [
            {
                "karyawan_id": "uuid-1",
                "total_hadir": 30,
                "karyawan": {
                    "id": "uuid-1",
                    "nip": "EMP001",
                    "nama": "Budi Santoso",
                    "departemen": "IT"
                }
            }
        ],
        "karyawan_terlambat": [
            {
                "karyawan_id": "uuid-5",
                "jumlah_terlambat": 15,
                "total_menit_terlambat": 320,
                "karyawan": {
                    "id": "uuid-5",
                    "nip": "EMP005",
                    "nama": "Ahmad Rizki",
                    "departemen": "Marketing"
                }
            }
        ],
        "by_departemen": [
            {
                "departemen": "IT",
                "total_absensi": 300,
                "total_hadir": 270,
                "total_alpha": 5
            },
            {
                "departemen": "HR",
                "total_absensi": 90,
                "total_hadir": 85,
                "total_alpha": 2
            }
        ]
    }
}
```

---

## 8. Rekap Absensi per Karyawan

### Endpoint:
```
GET /api/absensi/rekap/{karyawanId}
```

### Query Parameters (Required):
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| bulan | integer | **Yes** | Month (1-12) |
| tahun | integer | **Yes** | Year (e.g., 2025) |

### Example Request:
```bash
curl -X GET "http://127.0.0.1:8000/api/absensi/rekap/uuid-karyawan?bulan=11&tahun=2025" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

### Response Success (200):
```json
{
    "success": true,
    "message": "Rekap absensi berhasil diambil",
    "data": {
        "karyawan": {
            "id": "uuid-karyawan",
            "nip": "EMP001",
            "nama": "Budi Santoso",
            "departemen": "IT",
            "jabatan": "CEO"
        },
        "periode": {
            "bulan": 11,
            "tahun": 2025,
            "nama_bulan": "November 2025"
        },
        "summary": {
            "total_hari": 27,
            "hadir": 25,
            "izin": 1,
            "sakit": 0,
            "alpha": 1,
            "cuti": 0,
            "total_terlambat": 3,
            "total_menit_terlambat": 45,
            "total_potongan_terlambat": 45000.00,
            "total_potongan_alpha": 100000.00,
            "total_potongan": 145000.00
        },
        "detail_absensi": [
            {
                "id": "uuid-1",
                "tanggal": "2025-11-01",
                "jam_masuk": "2025-11-01 08:00:00",
                "jam_keluar": "2025-11-01 17:00:00",
                "status": "HADIR",
                "menit_terlambat": 0,
                "potongan_terlambat": "0.00"
            },
            {
                "id": "uuid-2",
                "tanggal": "2025-11-02",
                "jam_masuk": "2025-11-02 08:15:00",
                "jam_keluar": "2025-11-02 17:00:00",
                "status": "HADIR",
                "menit_terlambat": 15,
                "potongan_terlambat": "15000.00"
            }
        ]
    }
}
```

---

## 9. Bulk Create Absensi

### Endpoint:
```
POST /api/absensi/bulk
```

### Description:
Import multiple absensi records sekaligus (useful untuk import dari Excel/CSV).

### Request Body:
```json
{
    "absensi": [
        {
            "karyawan_id": "uuid-karyawan-1",
            "tanggal": "2025-11-13",
            "jam_masuk": "2025-11-13 08:00:00",
            "jam_keluar": "2025-11-13 17:00:00",
            "status": "HADIR",
            "lokasi": "Kantor Pusat"
        },
        {
            "karyawan_id": "uuid-karyawan-2",
            "tanggal": "2025-11-13",
            "jam_masuk": "2025-11-13 08:10:00",
            "jam_keluar": "2025-11-13 17:00:00",
            "status": "HADIR",
            "lokasi": "Kantor Pusat"
        },
        {
            "karyawan_id": "uuid-karyawan-3",
            "tanggal": "2025-11-13",
            "status": "IZIN",
            "keterangan": "Izin keperluan keluarga"
        }
    ]
}
```

### Field Validation (per item):
| Field | Type | Required | Rules |
|-------|------|----------|-------|
| karyawan_id | uuid | **Yes** | must exist in karyawan table |
| tanggal | date | **Yes** | format: YYYY-MM-DD |
| status | enum | **Yes** | HADIR, IZIN, SAKIT, ALPHA, CUTI |

### Example Request:
```bash
curl -X POST "http://127.0.0.1:8000/api/absensi/bulk" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d @bulk_absensi.json
```

### Response Success (201):
```json
{
    "success": true,
    "message": "15 absensi berhasil dibuat",
    "data": {
        "created": [
            {
                "id": "uuid-1",
                "karyawan_id": "uuid-karyawan-1",
                "tanggal": "2025-11-13",
                "status": "HADIR"
            },
            {
                "id": "uuid-2",
                "karyawan_id": "uuid-karyawan-2",
                "tanggal": "2025-11-13",
                "status": "HADIR"
            }
        ],
        "errors": [
            {
                "index": 5,
                "data": {
                    "karyawan_id": "uuid-karyawan-5",
                    "tanggal": "2025-11-13"
                },
                "error": "Absensi sudah ada untuk tanggal ini"
            }
        ]
    }
}
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
    "message": "Absensi tidak ditemukan",
    "error": "No query results for model..."
}
```

### 422 Validation Error:
```json
{
    "success": false,
    "message": "Validasi gagal",
    "errors": {
        "karyawan_id": [
            "The karyawan_id field is required."
        ],
        "tanggal": [
            "The tanggal field is required."
        ],
        "status": [
            "The selected status is invalid."
        ]
    }
}
```

### 500 Internal Server Error:
```json
{
    "success": false,
    "message": "Gagal mencatat absensi",
    "error": "Database connection error..."
}
```

---

## Business Logic

### 1. Perhitungan Keterlambatan:
- Sistem membandingkan `jam_masuk` karyawan dengan `jam_masuk_kerja` dari tabel `aturan_perusahaan`
- Jika terlambat: `menit_terlambat` = selisih menit
- Potongan: `potongan_terlambat` = `menit_terlambat` × `potongan_per_menit` (dari aturan)

### 2. Perhitungan Pulang Cepat:
- Sistem membandingkan `jam_keluar` karyawan dengan `jam_pulang_kerja` dari aturan
- Jika pulang lebih awal: `menit_pulang_cepat` = selisih menit

### 3. Potongan Alpha:
- Jika status = `ALPHA`, otomatis mendapat potongan sesuai `potongan_alpha` dari aturan perusahaan

### 4. Unique Constraint:
- Satu karyawan hanya bisa punya 1 absensi per tanggal
- Kombinasi `karyawan_id` + `tanggal` harus unique

---

## Integration dengan IoT (RFID)

### RFID Auto Absensi:
Ketika karyawan scan RFID card mereka, sistem akan:

1. **Check-in Otomatis** (jam masuk):
```bash
POST /api/rfid/scan
{
    "rfid_card": "A1B2C3D4",
    "lokasi": "Kantor Pusat"
}
```

2. **Check-out Otomatis** (jam keluar):
Scan RFID lagi di akhir hari akan update `jam_keluar`

3. **Auto-calculate**:
- Sistem otomatis hitung keterlambatan
- Sistem otomatis hitung pulang cepat
- Sistem otomatis hitung potongan

---

## Use Cases

### Use Case 1: Manual Input Absensi (HRD)
```bash
# HRD input absensi untuk karyawan yang lupa scan RFID
POST /api/absensi
{
    "karyawan_id": "uuid-karyawan",
    "tanggal": "2025-11-13",
    "jam_masuk": "2025-11-13 08:00:00",
    "jam_keluar": "2025-11-13 17:00:00",
    "status": "HADIR",
    "keterangan": "Input manual oleh HRD - lupa scan RFID"
}
```

### Use Case 2: Koreksi Jam Masuk
```bash
# Karyawan complaint jam masuk salah, HRD update
PUT /api/absensi/uuid-1
{
    "jam_masuk": "2025-11-13 07:55:00",
    "keterangan": "Koreksi jam masuk sesuai CCTV"
}
```

### Use Case 3: Input Izin/Sakit
```bash
# Karyawan submit izin/sakit
POST /api/absensi
{
    "karyawan_id": "uuid-karyawan",
    "tanggal": "2025-11-13",
    "status": "IZIN",
    "keterangan": "Izin keperluan keluarga, surat izin terlampir"
}
```

### Use Case 4: Batalkan Absensi Salah
```bash
# HRD batalkan absensi yang keliru
POST /api/absensi/uuid-1/cancel
{
    "keterangan_batal": "Absensi keliru, karyawan sedang cuti"
}
```

### Use Case 5: Import Absensi Bulanan dari Excel
```bash
# Upload CSV/Excel yang di-convert ke JSON
POST /api/absensi/bulk
{
    "absensi": [
        { ... }, # 100+ records
        { ... }
    ]
}
```

### Use Case 6: Monitoring Keterlambatan Departemen
```bash
# Manager cek siapa saja yang terlambat di departemennya
GET /api/absensi?departemen=IT&terlambat=true&bulan=11&tahun=2025
```

### Use Case 7: Generate Laporan Bulanan
```bash
# HRD generate rekap absensi karyawan untuk payroll
GET /api/absensi/rekap/uuid-karyawan?bulan=10&tahun=2025
```

---

## Kompatibilitas Shared Hosting cPanel

### ✅ Fitur yang Kompatibel:
1. **Laravel Sanctum** - Token-based authentication
2. **Database Transactions** - MySQL standard
3. **UUID Primary Keys** - MySQL 5.7+
4. **REST API** - Standard HTTP methods
5. **JSON Responses** - No special extensions needed
6. **Carbon Dates** - PHP 8.0+ built-in

### 📋 Deployment Notes:
- Gunakan `.env` untuk konfigurasi database
- Pastikan `storage/` dan `bootstrap/cache/` writable (chmod 755)
- Upload files via FTP/cPanel File Manager
- Run migrations: `php artisan migrate --force`
- Seed aturan perusahaan: `php artisan db:seed --class=AturanPerusahaanSeeder`

---

## Testing dengan Postman

### Postman Collection:
Import JSON ini ke Postman:

```json
{
    "info": {
        "name": "Absensi Management API",
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
            "name": "List Absensi",
            "request": {
                "method": "GET",
                "url": "{{base_url}}/api/absensi"
            }
        },
        {
            "name": "Create Absensi",
            "request": {
                "method": "POST",
                "url": "{{base_url}}/api/absensi",
                "body": {
                    "mode": "raw",
                    "raw": "{\"karyawan_id\":\"uuid\",\"tanggal\":\"2025-11-13\",\"status\":\"HADIR\"}"
                }
            }
        },
        {
            "name": "Get Statistics",
            "request": {
                "method": "GET",
                "url": "{{base_url}}/api/absensi/statistics?bulan=11&tahun=2025"
            }
        },
        {
            "name": "Rekap Karyawan",
            "request": {
                "method": "GET",
                "url": "{{base_url}}/api/absensi/rekap/{{karyawan_id}}?bulan=11&tahun=2025"
            }
        }
    ]
}
```

### Quick Test dengan cURL:
```bash
# 1. Login dan simpan token
TOKEN=$(curl -s -X POST "http://127.0.0.1:8000/api/login" \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@pencarierror.com","password":"password123"}' \
  | jq -r '.data.token')

# 2. Get list absensi bulan ini
curl -X GET "http://127.0.0.1:8000/api/absensi?bulan=11&tahun=2025" \
  -H "Authorization: Bearer $TOKEN" | jq

# 3. Get statistics
curl -X GET "http://127.0.0.1:8000/api/absensi/statistics?bulan=11&tahun=2025" \
  -H "Authorization: Bearer $TOKEN" | jq

# 4. Get rekap karyawan (ganti KARYAWAN_ID)
curl -X GET "http://127.0.0.1:8000/api/absensi/rekap/KARYAWAN_ID?bulan=11&tahun=2025" \
  -H "Authorization: Bearer $TOKEN" | jq
```

---

## Best Practices

### 1. Gunakan Filter untuk Performa:
```bash
# ❌ Bad - load all data
GET /api/absensi

# ✅ Good - filter by period
GET /api/absensi?bulan=11&tahun=2025&per_page=50
```

### 2. Bulk Import untuk Data Banyak:
```bash
# ❌ Bad - create one by one (20x request)
POST /api/absensi # 20 times

# ✅ Good - bulk create (1x request)
POST /api/absensi/bulk # with array of 20 items
```

### 3. Gunakan Rekap untuk Laporan:
```bash
# ❌ Bad - manual calculation from list
GET /api/absensi?karyawan_id=X # then calculate in frontend

# ✅ Good - use rekap endpoint
GET /api/absensi/rekap/X?bulan=11&tahun=2025 # pre-calculated
```

---

## Support
Untuk pertanyaan atau issue, silakan hubungi:
- Email: support@pencarierror.com
- Phone: +62 21 1234 5678
