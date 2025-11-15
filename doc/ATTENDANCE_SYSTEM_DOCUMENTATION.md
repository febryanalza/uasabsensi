# 📊 SISTEM ATTENDANCE DENGAN PERHITUNGAN OTOMATIS

## 🎯 Overview
Sistem ini mengintegrasikan **aturan perusahaan** untuk melakukan perhitungan otomatis ketika karyawan melakukan absensi. Semua perhitungan keterlambatan, bonus kehadiran, dan potongan dilakukan secara otomatis berdasarkan data di tabel `aturan_perusahaan`.

## 🏗️ Arsitektur Sistem

### 1. **AbsensiCalculatorHelper**
Helper class yang menangani semua perhitungan attendance:
- ✅ Perhitungan keterlambatan berdasarkan `jam_masuk_kerja` + `toleransi_terlambat`
- ✅ Perhitungan pulang awal berdasarkan `jam_pulang_kerja` + `toleransi_pulang_awal`
- ✅ Deteksi hari libur dan weekend
- ✅ Perhitungan bonus kehadiran bulanan
- ✅ Perhitungan bonus akhir tahun untuk kehadiran sempurna

### 2. **AbsensiController Enhancement**
Controller yang telah diupgrade untuk menggunakan automatic calculation:
- ✅ Auto-calculation pada method `store()`
- ✅ Validasi aturan perusahaan aktif
- ✅ Integration dengan company rules
- ✅ Error handling yang comprehensive

## 📋 Aturan Perusahaan (Company Rules)

### **Field yang Digunakan untuk Perhitungan:**

| Field | Tipe | Fungsi | Contoh |
|-------|------|--------|---------|
| `jam_masuk_kerja` | TIME | Jam kerja standar | 08:00:00 |
| `jam_pulang_kerja` | TIME | Jam pulang standar | 17:00:00 |
| `toleransi_terlambat` | INT | Toleransi keterlambatan (menit) | 15 |
| `toleransi_pulang_awal` | INT | Toleransi pulang awal (menit) | 15 |
| `minimal_hadir_bonus` | INT | Min. kehadiran untuk bonus | 22 |
| `bonus_kehadiran_penuh` | DECIMAL | Nominal bonus bulanan | 500000.00 |
| `potongan_per_hari_alpha` | DECIMAL | Potongan per hari alpha | 50000.00 |
| `potongan_per_menit_terlambat` | DECIMAL | Potongan per menit terlambat | 5000.00 |
| `potongan_pulang_awal` | DECIMAL | Potongan pulang awal | 25000.00 |

## 🔧 API Endpoints

### 1. **Create Attendance (Dengan Auto-Calculation)**
```http
POST /api/absensi
Content-Type: application/json

{
    "karyawan_id": "uuid-karyawan",
    "tanggal": "2024-12-21",
    "jam_masuk": "08:30:00",
    "jam_pulang": "17:00:00",
    "keterangan": "Hadir normal"
}
```

**Response dengan Auto-Calculation:**
```json
{
    "success": true,
    "message": "Absensi berhasil dicatat dengan perhitungan otomatis",
    "data": {
        "id": "uuid-absensi",
        "karyawan_id": "uuid-karyawan",
        "tanggal": "2024-12-21",
        "jam_masuk": "08:30:00",
        "jam_pulang": "17:00:00",
        "menit_terlambat": 30,
        "bonus_kehadiran": 0,
        "potongan_keterlambatan": 150000.00,
        "keterangan": "Hadir normal | Auto: Terlambat 30 menit, potongan Rp 150,000",
        "perhitungan_detail": {
            "jam_kerja_standar": "08:00:00 - 17:00:00",
            "toleransi_terlambat": "15 menit",
            "keterlambatan_efektif": "15 menit",
            "potongan_per_menit": 5000,
            "total_potongan": 150000,
            "eligible_bonus_bulan_ini": false
        }
    }
}
```

### 2. **Get Company Rules**
```http
GET /api/absensi/company-rules
```

**Response:**
```json
{
    "success": true,
    "message": "Aturan perusahaan berhasil diambil",
    "data": {
        "id": "uuid-aturan",
        "nama": "Aturan Standar 2024",
        "jam_kerja": {
            "jam_masuk": "08:00:00",
            "jam_pulang": "17:00:00",
            "toleransi_terlambat": "15 menit",
            "toleransi_pulang_awal": "15 menit"
        },
        "bonus_kehadiran": {
            "minimal_hadir_bonus": "22 hari",
            "bonus_kehadiran_penuh": "Rp 500,000",
            "minimal_bulan_bonus_tahunan": 12,
            "multiplier_bonus_tahunan": 1
        },
        "potongan": {
            "potongan_per_hari_alpha": "Rp 50,000",
            "potongan_per_menit_terlambat": "Rp 5,000",
            "potongan_pulang_awal": "Rp 25,000"
        }
    }
}
```

### 3. **Get Attendance Statistics**
```http
GET /api/absensi/stats/{karyawan_id}
```

**Response:**
```json
{
    "success": true,
    "message": "Statistik kehadiran berhasil diambil",
    "data": {
        "karyawan": {
            "id": "uuid-karyawan",
            "nama": "John Doe",
            "nik": "EMP001"
        },
        "statistik_kehadiran": {
            "periode": {
                "bulan": 12,
                "tahun": 2024
            },
            "kehadiran": {
                "total_hari_kerja": 22,
                "total_hadir": 20,
                "total_alpha": 2,
                "persentase_kehadiran": 90.91
            },
            "keterlambatan": {
                "total_hari_terlambat": 5,
                "total_menit_terlambat": 150,
                "rata_rata_menit_terlambat": 30,
                "total_potongan_terlambat": 750000.00
            },
            "bonus": {
                "eligible_bonus_bulanan": false,
                "alasan": "Kehadiran hanya 20 hari, minimal 22 hari",
                "proyeksi_bonus": 0
            }
        }
    }
}
```

### 4. **Check Bonus Eligibility**
```http
GET /api/absensi/bonus-eligibility?karyawan_id={uuid}&bulan=12&tahun=2024
```

### 5. **Get Year-End Bonus**
```http
GET /api/absensi/year-end-bonus?karyawan_id={uuid}&tahun=2024
```

## 🎯 Fitur Automatic Calculation

### **1. Keterlambatan (Tardiness)**
```php
// Automatic calculation logic:
$jamMasukStandar = $aturanPerusahaan->jam_masuk_kerja; // 08:00:00
$toleransi = $aturanPerusahaan->toleransi_terlambat;   // 15 menit
$jamMasukActual = $request->jam_masuk;                 // 08:30:00

if ($jamMasukActual > $jamMasukStandar) {
    $menitTerlambat = hitungMenitTerlambat($jamMasukStandar, $jamMasukActual);
    
    if ($menitTerlambat > $toleransi) {
        $menitKenaPelanggaran = $menitTerlambat - $toleransi;
        $potongan = $menitKenaPelanggaran * $aturanPerusahaan->potongan_per_menit_terlambat;
    }
}
```

### **2. Pulang Awal (Early Departure)**
```php
$jamPulangStandar = $aturanPerusahaan->jam_pulang_kerja; // 17:00:00
$toleransiPulangAwal = $aturanPerusahaan->toleransi_pulang_awal; // 15 menit

if ($jamPulangActual < $jamPulangStandar) {
    $menitPulangAwal = hitungMenitPulangAwal($jamPulangStandar, $jamPulangActual);
    
    if ($menitPulangAwal > $toleransiPulangAwal) {
        $potongan = $aturanPerusahaan->potongan_pulang_awal;
    }
}
```

### **3. Bonus Kehadiran Bulanan**
```php
$totalHadirBulanIni = hitungKehadiranBulanan($karyawanId, $bulan, $tahun);
$minimalHadir = $aturanPerusahaan->minimal_hadir_bonus; // 22 hari

if ($totalHadirBulanIni >= $minimalHadir) {
    $bonusKehadiran = $aturanPerusahaan->bonus_kehadiran_penuh; // Rp 500,000
}
```

## 🚀 Testing & Usage

### **1. Menjalankan Server**
```bash
cd "d:\TUGAS KULIAH\Semester 5\IOT\Project\uasabsensi"
php artisan serve --host=127.0.0.1 --port=8000
```

### **2. Test API dengan Script**
```bash
php test_attendance_api.php
```

### **3. Manual Testing Scenarios**

#### **Scenario 1: Kehadiran Normal**
```json
{
    "karyawan_id": "uuid-karyawan",
    "tanggal": "2024-12-21",
    "jam_masuk": "07:55:00", // 5 menit lebih awal
    "jam_pulang": "17:00:00", // Tepat waktu
    "keterangan": "Hadir normal"
}
// Expected: Tidak ada potongan, eligible untuk bonus
```

#### **Scenario 2: Terlambat dalam Toleransi**
```json
{
    "jam_masuk": "08:10:00", // Terlambat 10 menit (dalam toleransi 15 menit)
    "jam_pulang": "17:00:00"
}
// Expected: Tidak ada potongan
```

#### **Scenario 3: Terlambat di atas Toleransi**
```json
{
    "jam_masuk": "08:30:00", // Terlambat 30 menit
    "jam_pulang": "17:00:00"
}
// Expected: Potongan = (30-15) × 5000 = Rp 75,000
```

#### **Scenario 4: Pulang Awal**
```json
{
    "jam_masuk": "08:00:00",
    "jam_pulang": "16:30:00" // Pulang awal 30 menit (di atas toleransi 15 menit)
}
// Expected: Potongan pulang awal = Rp 25,000
```

## 📊 Business Logic Summary

### **Calculation Flow:**
1. **Validasi Aturan Perusahaan** - Cek apakah ada aturan aktif
2. **Deteksi Hari Kerja** - Skip weekend dan hari libur
3. **Perhitungan Keterlambatan** - Hitung menit terlambat vs toleransi
4. **Perhitungan Pulang Awal** - Hitung pulang awal vs toleransi
5. **Evaluasi Bonus** - Cek kelayakan bonus kehadiran
6. **Update Record** - Simpan dengan detail perhitungan
7. **Auto-Generate Keterangan** - Tambahkan info perhitungan ke keterangan

### **Integration Points:**
- ✅ **Tabel aturan_perusahaan** - Sumber semua business rules
- ✅ **Tabel hari_libur** - Deteksi hari libur nasional
- ✅ **Helper GajiCalculatorHelper** - Integrasi dengan perhitungan gaji
- ✅ **Config payroll.php** - Konfigurasi default untuk shared hosting

### **Error Handling:**
- ✅ Validasi aturan perusahaan aktif
- ✅ Validasi format jam dan tanggal
- ✅ Rollback transaction jika ada error
- ✅ Response JSON yang konsisten
- ✅ Logging untuk debugging

## 🎉 Hasil Implementasi

**✅ COMPLETED FEATURES:**
- [x] Automatic tardiness calculation based on company rules
- [x] Early departure penalty calculation
- [x] Monthly attendance bonus eligibility
- [x] Holiday and weekend detection
- [x] Year-end perfect attendance bonus
- [x] API endpoints for attendance analytics
- [x] Company rules management
- [x] Comprehensive error handling
- [x] Test scripts and documentation

**🚀 READY FOR PRODUCTION:**
Sistem siap digunakan dengan semua business rules terintegrasi otomatis!