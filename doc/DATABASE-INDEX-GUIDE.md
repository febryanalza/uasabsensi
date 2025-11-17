# 🚀 DATABASE INDEX OPTIMIZATION GUIDE

## 📋 **COMPREHENSIVE INDEXES YANG DIBUAT:**

### **1. 🔐 USERS TABLE**
```sql
-- Authentication & Authorization
idx_users_email              (email)                    -- Login queries
idx_users_role               (role)                     -- Role-based access
idx_users_email_role         (email, role)              -- Combined login+auth
idx_users_karyawan_id        (karyawan_id)              -- Join with karyawan
idx_users_created_at         (created_at)               -- Date-based queries
idx_users_role_created       (role, created_at)         -- Admin dashboard
```

### **2. 👥 KARYAWAN TABLE**
```sql
-- Primary Search Columns
idx_karyawan_nip             (nip)                      -- Employee ID search
idx_karyawan_email           (email)                    -- Email search
idx_karyawan_nama            (nama)                     -- Name search
idx_karyawan_rfid            (rfid_card_number)         -- RFID operations

-- Filtering & Grouping
idx_karyawan_departemen      (departemen)               -- Department filter
idx_karyawan_jabatan         (jabatan)                  -- Position filter  
idx_karyawan_status          (status)                   -- Active/inactive
idx_karyawan_tanggal_masuk   (tanggal_masuk)            -- Hire date

-- Composite Indexes untuk Query Kompleks
idx_karyawan_dept_status     (departemen, status)       -- Dept + Status filter
idx_karyawan_jabatan_status  (jabatan, status)          -- Position + Status
idx_karyawan_status_tanggal  (status, tanggal_masuk)    -- Status + Hire date
idx_karyawan_gaji_pokok      (gaji_pokok)               -- Salary calculations
```

### **3. ⏰ ABSENSI TABLE (MOST CRITICAL!)**
```sql
-- Core Query Indexes
idx_absensi_karyawan_id      (karyawan_id)              -- Employee attendance
idx_absensi_tanggal          (tanggal)                  -- Date-based queries
idx_absensi_status           (status)                   -- Attendance status

-- Time-based Operations
idx_absensi_jam_masuk        (jam_masuk)                -- Check-in time
idx_absensi_jam_keluar       (jam_keluar)               -- Check-out time
idx_absensi_created_at       (created_at)               -- Record creation

-- RFID Scanning
idx_absensi_rfid_masuk       (rfid_masuk)               -- RFID check-in
idx_absensi_rfid_keluar      (rfid_keluar)              -- RFID check-out

-- Performance Calculations
idx_absensi_terlambat        (menit_terlambat)          -- Late minutes
idx_absensi_pulang_cepat     (menit_pulang_cepat)       -- Early leave

-- CRITICAL Composite Indexes
idx_absensi_karyawan_tanggal      (karyawan_id, tanggal)           -- Employee daily
idx_absensi_tanggal_status        (tanggal, status)                -- Daily status
idx_absensi_karyawan_status       (karyawan_id, status)            -- Employee status
idx_absensi_karyawan_tanggal_status (karyawan_id, tanggal, status) -- Full filter

-- Monthly Reports (Functional Index)
idx_absensi_year_month       (YEAR(tanggal), MONTH(tanggal))       -- Monthly reports
```

### **4. 💰 GAJI TABLE**
```sql
-- Primary Query Columns
idx_gaji_karyawan_id         (karyawan_id)              -- Employee salary
idx_gaji_bulan               (bulan)                    -- Month filter
idx_gaji_tahun               (tahun)                    -- Year filter
idx_gaji_created_at          (created_at)               -- Record date

-- Calculation Fields
idx_gaji_gaji_bersih         (gaji_bersih)              -- Net salary
idx_gaji_total_pendapatan    (total_pendapatan)         -- Gross salary

-- Period-based Queries
idx_gaji_periode             (tahun, bulan)             -- Monthly period
idx_gaji_karyawan_tahun      (karyawan_id, tahun)       -- Employee yearly
idx_gaji_karyawan_periode    (karyawan_id, tahun, bulan) -- Full period

-- Attendance Statistics
idx_gaji_hadir               (jumlah_hadir)             -- Attendance count
idx_gaji_alpha               (jumlah_alpha)             -- Absence count
```

### **5. 🕐 LEMBUR TABLE** 
```sql
-- Basic Queries
idx_lembur_karyawan_id       (karyawan_id)              -- Employee overtime
idx_lembur_tanggal           (tanggal)                  -- Date filter
idx_lembur_status            (status)                   -- Approval status

-- Time Management
idx_lembur_jam_mulai         (jam_mulai)                -- Start time
idx_lembur_jam_selesai       (jam_selesai)              -- End time

-- Composite
idx_lembur_karyawan_tanggal  (karyawan_id, tanggal)     -- Employee daily OT
```

### **6. 📱 AVAILABLE_RFID TABLE**
```sql
-- RFID Management
idx_rfid_card_number         (card_number)              -- Card lookup
idx_rfid_is_assigned         (is_assigned)              -- Assignment status
idx_rfid_created_at          (created_at)               -- Registration date
idx_rfid_assigned_created    (is_assigned, created_at)  -- Available cards
```

### **7. 🔐 SESSIONS TABLE** 
```sql
-- Session Management
idx_sessions_user_id         (user_id)                  -- User sessions
idx_sessions_last_activity   (last_activity)            -- Activity tracking
idx_sessions_user_activity   (user_id, last_activity)   -- User activity
```

---

## 🎯 **QUERY PERFORMANCE TARGETS:**

| **Operation** | **Before** | **Target** | **Index Used** |
|---------------|------------|------------|----------------|
| User Login | 100ms+ | <10ms | `idx_users_email_role` |
| Employee Search | 200ms+ | <50ms | `idx_karyawan_nama` |
| Daily Attendance | 300ms+ | <20ms | `idx_absensi_tanggal` |
| Employee Attendance | 400ms+ | <30ms | `idx_absensi_karyawan_tanggal` |
| Monthly Report | 1000ms+ | <100ms | `idx_absensi_year_month` |
| Salary Calculation | 500ms+ | <50ms | `idx_gaji_karyawan_periode` |
| RFID Scan | 150ms+ | <20ms | `idx_rfid_card_number` |

---

## 🛠️ **DEPLOYMENT INSTRUCTIONS:**

### **1. Apply Migration**
```bash
cd /var/www/html/uasabsensi
php artisan migrate
```

### **2. Analyze Performance**
```bash
php artisan tinker --execute="include 'analyze-db-performance.php';"
```

### **3. Monitor Results**
```sql
-- Check index usage
SHOW INDEX FROM absensi;

-- Analyze query performance
EXPLAIN SELECT * FROM absensi WHERE karyawan_id = 'xxx' AND tanggal >= '2025-01-01';

-- Check index effectiveness
SHOW STATUS LIKE 'Handler_read%';
```

### **4. Verify Improvements**
```bash
# Before vs After performance test
php artisan tinker --execute="include 'test-performance.php';"
```

---

## 📊 **INDEX STRATEGY EXPLAINED:**

### **Single Column Indexes:**
- **Primary lookups**: email, nip, card_number
- **Filtering**: status, departemen, jabatan  
- **Sorting**: created_at, tanggal, nama
- **Calculations**: gaji_bersih, menit_terlambat

### **Composite Indexes:**
- **Most selective first**: karyawan_id, tanggal, status
- **Query coverage**: Cover entire WHERE clause
- **Sort optimization**: Match ORDER BY columns
- **Join optimization**: Foreign key combinations

### **Functional Indexes:**
- **Date aggregation**: YEAR(tanggal), MONTH(tanggal)
- **Performance**: Avoid function calls in WHERE
- **Reporting**: Monthly/yearly summaries

---

## ⚠️ **MAINTENANCE NOTES:**

1. **Monitor index usage** via `SHOW STATUS`
2. **Rebuild indexes monthly** via `OPTIMIZE TABLE`
3. **Watch for index bloat** on high-update tables
4. **Remove unused indexes** to save space
5. **Update statistics** via `ANALYZE TABLE`

---

## 🚨 **CRITICAL INDEXES (Must Have):**

1. `idx_absensi_karyawan_tanggal` - Daily attendance queries
2. `idx_users_email` - Authentication 
3. `idx_karyawan_rfid` - RFID scanning
4. `idx_gaji_karyawan_periode` - Salary calculations
5. `idx_absensi_year_month` - Monthly reports

**Expected Performance Improvement: 60-80% reduction in query time**