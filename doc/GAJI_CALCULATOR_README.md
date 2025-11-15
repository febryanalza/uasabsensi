# Helper Perhitungan Gaji - Laravel UAS Absensi

## 📋 Overview

Helper `GajiCalculatorHelper` menyediakan fungsi perhitungan gaji otomatis yang mengintegrasikan data dari:
- ✅ **Absensi** (kehadiran, keterlambatan, alpha)
- ✅ **Lembur** (jam lembur, kompensasi)  
- ✅ **KPI** (bonus performa)
- ✅ **Aturan Perusahaan** (bonus kehadiran, potongan)
- ✅ **Master Karyawan** (gaji pokok, tunjangan)

## 🚀 Cara Penggunaan

### 1. Perhitungan Gaji Single Employee

```php
use App\Helpers\GajiCalculatorHelper;

$result = GajiCalculatorHelper::calculateGaji($karyawanId, $bulan, $tahun);

if ($result['success']) {
    $gajiData = $result['data'];
    // Data sudah siap untuk disimpan ke tabel gaji
} else {
    echo "Error: " . $result['error'];
}
```

### 2. Perhitungan Bulk Gaji

```php
$karyawanIds = ['uuid1', 'uuid2', 'uuid3'];
$results = GajiCalculatorHelper::bulkCalculateGaji($karyawanIds, $bulan, $tahun);

foreach ($results['success'] as $success) {
    echo "Berhasil: " . $success['karyawan_name'];
}

foreach ($results['failed'] as $failed) {
    echo "Gagal: " . $failed['karyawan_name'] . " - " . $failed['error'];
}
```

### 3. Validasi Persyaratan

```php
$validation = GajiCalculatorHelper::validateCalculationRequirements($karyawanId, $bulan, $tahun);

if (!$validation['valid']) {
    foreach ($validation['errors'] as $error) {
        echo "Error: " . $error;
    }
}
```

### 4. Summary Periode

```php
$summary = GajiCalculatorHelper::getSalarySummary($bulan, $tahun);

echo "Hari kerja: " . $summary['working_days'];
echo "Karyawan aktif: " . $summary['active_employees'];
```

## 🌐 API Endpoints

### GET /api/gaji/summary
```json
{
  "bulan": 11,
  "tahun": 2025
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "period": {
      "bulan": 11,
      "tahun": 2025,
      "period_name": "November 2025"
    },
    "working_days": 21,
    "holidays_count": 2,
    "active_employees": 20,
    "calculation_ready": true
  }
}
```

### POST /api/gaji/validate
```json
{
  "karyawan_id": "uuid-karyawan",
  "bulan": 11,
  "tahun": 2025
}
```

### POST /api/gaji (Generate Gaji)
```json
{
  "karyawan_id": "uuid-karyawan",
  "bulan": 11,
  "tahun": 2025,
  "dibuat_oleh": "uuid-user"
}
```

### POST /api/gaji/bulk (Generate Bulk Gaji)
```json
{
  "bulan": 11,
  "tahun": 2025,
  "dibuat_oleh": "uuid-user",
  "karyawan_ids": ["uuid1", "uuid2"] // optional, jika kosong = semua karyawan aktif
}
```

## ⚙️ Konfigurasi

File: `config/payroll.php`

```php
return [
    'bpjs_kesehatan_rate' => 0.02, // 2%
    'bpjs_ketenagakerjaan_rate' => 0.01, // 1%
    'ptkp_annual' => 54000000, // PTKP tahunan
    'round_to_nearest' => 100, // Pembulatan
];
```

## 📊 Struktur Data Output

```php
[
    'karyawan_id' => 'uuid',
    'bulan' => 11,
    'tahun' => 2025,
    
    // Komponen Gaji Pokok
    'gaji_pokok' => 5000000,
    'tunjangan_jabatan' => 1000000,
    'tunjangan_transport' => 500000,
    'tunjangan_makan' => 300000,
    
    // Data Kehadiran
    'jumlah_hadir' => 21,
    'jumlah_izin' => 1,
    'jumlah_sakit' => 0,
    'jumlah_alpha' => 0,
    'jumlah_terlambat' => 3,
    'total_menit_terlambat' => 45,
    
    // Lembur & Bonus
    'total_jam_lembur' => 12.5,
    'tunjangan_lembur' => 625000,
    'bonus_kehadiran' => 500000,
    'bonus_kpi' => 750000,
    
    // Potongan
    'potongan_terlambat' => 150000,
    'potongan_alpha' => 0,
    'potongan_lainnya' => 0,
    'bpjs_kesehatan' => 136000,
    'bpjs_ketenagakerjaan' => 68000,
    'pph21' => 125000,
    
    // Total
    'total_pendapatan' => 7675000,
    'total_potongan' => 479000,
    'gaji_bersih' => 7196000,
    
    // Metadata
    'status' => 'DRAFT',
    'tanggal_dibuat' => '2025-11-13 10:30:00'
]
```

## 🏗️ Kompatibilitas Shared Hosting

✅ **Static Methods** - Tidak butuh dependency injection
✅ **No External Libraries** - Hanya menggunakan Laravel built-in
✅ **Optimized Queries** - Efisien untuk shared hosting
✅ **Error Handling** - Comprehensive error handling
✅ **Config Cache** - Support untuk config caching
✅ **Autoload Ready** - Terdaftar di service provider

## 🔧 Formula Perhitungan

### 1. Kehadiran
- **Hadir**: Dari tabel `absensi` status = 'HADIR'
- **Terlambat**: Sum `menit_terlambat` dari absensi
- **Potongan**: `(total_menit_terlambat - toleransi) * tarif_per_menit`

### 2. Lembur
- **Total Jam**: Sum `durasi_jam` dari lembur status = 'DISETUJUI'
- **Kompensasi**: Sum `total_kompensasi` dari lembur

### 3. Bonus KPI
- **Nilai**: Dari tabel `kpi` field `bonus_kpi`
- **Kategori**: EXCELLENT, GOOD, SATISFACTORY, dll

### 4. Pajak PPh21 (Simplified)
```
Penghasilan Tahunan - PTKP = Penghasilan Kena Pajak
- 0-60jt: 5%
- 60jt-250jt: 15%
- >250jt: 25%
```

### 5. BPJS
- **Kesehatan**: 2% dari gaji pokok + tunjangan tetap
- **Ketenagakerjaan**: 1% dari gaji pokok + tunjangan tetap

---

**📝 Catatan:** Helper ini sudah dioptimasi untuk shared hosting cPanel dan dapat digunakan langsung tanpa konfigurasi tambahan.