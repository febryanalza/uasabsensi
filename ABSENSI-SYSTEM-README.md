# 📋 Absensi Management System Documentation

## 🎯 Overview
Sistem Management Absensi yang terintegrasi dengan API dan perhitungan gaji berdasarkan data kehadiran. Dirancang khusus untuk kompatibilitas shared hosting dengan menggunakan teknologi web standard.

## 🚀 Fitur Utama

### 1. Management Absensi
- ✅ **CRUD Operations**: Create, Read, Update, Delete data absensi
- ✅ **Advanced Filtering**: Filter berdasarkan tanggal, status, karyawan
- ✅ **Real-time Search**: Pencarian nama karyawan secara real-time
- ✅ **Bulk Operations**: Operasi massal untuk multiple records
- ✅ **Status Management**: Hadir, Terlambat, Tidak Hadir, Cuti, Sakit

### 2. Perhitungan Gaji Otomatis
- ✅ **Individual Calculation**: Hitung gaji per karyawan berdasarkan absensi
- ✅ **Bulk Calculation**: Hitung gaji untuk multiple karyawan sekaligus
- ✅ **Detailed Breakdown**: Rincian lengkap komponen gaji
- ✅ **Integration with Rules**: Terintegrasi dengan aturan perusahaan

### 3. Statistik dan Reporting
- ✅ **Dashboard Statistics**: Statistik kehadiran real-time
- ✅ **Attendance Analytics**: Analisis pola kehadiran
- ✅ **Bonus Eligibility**: Cek kelayakan bonus karyawan
- ✅ **Year-end Reports**: Laporan akhir tahun

## 🏗️ Arsitektur Sistem

### Backend Architecture
```
Web Controllers (Proxy) → API Controllers → Helpers → Database
```

### File Structure
```
app/Http/Controllers/Web/
├── AbsensiController.php          # Web proxy controller
├── DashboardController.php
└── ...

resources/views/absensi/
├── index.blade.php                # Data management interface
├── create.blade.php               # Add new attendance
├── edit.blade.php                 # Edit attendance
└── salary-calculation.blade.php   # Salary calculation interface

routes/
└── web.php                        # Web routes dengan API proxy endpoints
```

## 🌐 API Endpoints

### Absensi Management API
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/absensi/api/data` | Get paginated absensi data |
| GET | `/absensi/api/statistics` | Get absensi statistics |
| POST | `/absensi/api/store` | Create new absensi |
| GET | `/absensi/api/{id}` | Get specific absensi |
| PUT | `/absensi/api/{id}` | Update absensi |
| DELETE | `/absensi/api/{id}/delete` | Delete absensi |
| POST | `/absensi/api/bulk-operation` | Bulk operations |

### Salary Calculation API
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| POST | `/absensi/api/calculate-salary` | Calculate individual salary |
| POST | `/absensi/api/bulk-calculate-salary` | Bulk salary calculation |

### Statistics & Reports API
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/absensi/api/bonus-eligibility` | Check bonus eligibility |
| GET | `/absensi/api/year-end-bonus` | Year-end bonus calculation |
| GET | `/absensi/api/company-rules` | Get company rules |
| GET | `/absensi/api/rekap/{id}` | Get employee recap |

## 🎨 Frontend Features

### 1. User Interface
- **Responsive Design**: Tailwind CSS untuk UI yang responsive
- **Interactive Components**: Alpine.js untuk interaktivitas
- **Real-time Updates**: AJAX untuk update data real-time
- **Progressive Enhancement**: Fallback untuk shared hosting

### 2. Form Features
- **Dynamic Karyawan Search**: Autocomplete search karyawan
- **Date/Time Pickers**: Input tanggal dan waktu yang user-friendly
- **Validation**: Client-side dan server-side validation
- **Change Tracking**: Track perubahan pada form edit

### 3. Data Visualization
- **Statistics Cards**: Card statistik dengan icons
- **Chart Integration**: Chart.js untuk visualisasi data
- **Progress Indicators**: Loading states dan progress bars

## 📱 Shared Hosting Compatibility

### ✅ Kompatibilitas Features
1. **CDN-based Resources**: 
   - Tailwind CSS dari CDN
   - Alpine.js dari CDN
   - Chart.js dari CDN
   - Lucide icons dari CDN

2. **Standard Laravel Features**:
   - Routing standard Laravel
   - Blade templating
   - HTTP Client untuk API calls
   - Standard MVC architecture

3. **No Build Process Required**:
   - Tidak membutuhkan Node.js
   - Tidak membutuhkan npm/yarn
   - Tidak membutuhkan webpack/vite build

4. **Database Compatibility**:
   - MySQL/MariaDB support
   - SQLite support untuk development
   - PostgreSQL support

### 🚫 Limitations untuk Shared Hosting
- WebSocket real-time features (gunakan polling sebagai alternative)
- Server-side rendering yang complex
- Background job processing (gunakan cron jobs)

## 🔧 Installation & Setup

### Development Environment
1. **Clone Repository**
   ```bash
   git clone [repository-url]
   cd uasabsensi
   ```

2. **Install Dependencies**
   ```bash
   composer install
   ```

3. **Environment Setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database Setup**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

5. **Run Development Server**
   ```bash
   php artisan serve
   ```

### Shared Hosting Deployment
1. **Upload Files**: Upload semua files ke public_html atau directory hosting
2. **Set Document Root**: Arahkan document root ke folder `public`
3. **Environment Config**: Set environment variables di .env
4. **Database**: Setup database dan jalankan migrations
5. **Permissions**: Set proper file permissions (755 untuk folders, 644 untuk files)

## 🧪 Testing

### Manual Testing
1. **Access Test Page**: `http://your-domain/tests/absensi-api-test.php`
2. **Test API Endpoints**: Click tombol test untuk verify API responses
3. **Test Web Routes**: Access halaman absensi management
4. **Check Functionality**: Test CRUD operations dan salary calculations

### API Testing dengan curl
```bash
# Test statistics API
curl -X GET http://your-domain/absensi/api/statistics

# Test company rules API
curl -X GET http://your-domain/absensi/api/company-rules

# Test karyawan list API
curl -X GET http://your-domain/absensi/api/karyawan-list
```

## 📋 Usage Guide

### 1. Mengakses Management Absensi
1. Login ke dashboard
2. Click menu "Management Absensi" di sidebar
3. Pilih submenu yang diinginkan

### 2. Menambah Data Absensi
1. Click "Tambah Absensi Baru"
2. Pilih karyawan dengan search
3. Set tanggal dan waktu
4. Pilih status kehadiran
5. Add keterangan jika perlu
6. Submit form

### 3. Menghitung Gaji Berdasarkan Absensi
1. Access "Perhitungan Gaji" dari menu absensi
2. Pilih karyawan dan periode
3. Click "Hitung Gaji" untuk individual calculation
4. Atau pilih multiple karyawan untuk bulk calculation
5. Review detailed breakdown results

### 4. Monitoring Statistik
1. Dashboard menampilkan overview statistics
2. Halaman absensi menampilkan detailed statistics
3. Filter berdasarkan periode untuk analisis specific

## 🔐 Security Features

### 1. Authentication & Authorization
- Laravel authentication system
- Role-based access control (future enhancement)
- CSRF protection pada semua forms
- Session management

### 2. Input Validation
- Server-side validation untuk semua inputs
- Client-side validation untuk UX
- SQL injection protection via Eloquent/QueryBuilder
- XSS protection via Blade templating

### 3. API Security
- Rate limiting (configurable)
- Input sanitization
- Error handling yang tidak expose sensitive information

## 📞 Support & Troubleshooting

### Common Issues
1. **API Not Responding**: Check .env configuration dan database connection
2. **Permission Denied**: Set proper file permissions pada shared hosting
3. **Missing Icons**: Verify CDN resources dapat diakses
4. **Slow Loading**: Optimize database queries dan consider caching

### Error Logging
- Check `storage/logs/laravel.log` untuk error details
- Enable debug mode di development: `APP_DEBUG=true`
- Disable debug mode di production: `APP_DEBUG=false`

### Performance Optimization
1. **Database**: Add proper indexes pada frequently queried columns
2. **Caching**: Enable Laravel cache untuk API responses
3. **CDN**: Use CDN untuk static assets
4. **Compression**: Enable gzip compression pada server

## 🔮 Future Enhancements

### Planned Features
- [ ] Export data ke Excel/PDF
- [ ] Email notifications untuk attendance alerts
- [ ] Mobile-responsive PWA features
- [ ] Advanced reporting dengan charts
- [ ] Integration dengan fingerprint/RFID systems
- [ ] Payroll integration
- [ ] Leave management system

### API Enhancements
- [ ] RESTful API dengan API versioning
- [ ] OAuth 2.0 authentication untuk external integrations
- [ ] Webhook support untuk external systems
- [ ] GraphQL endpoint (optional)

---

## 📝 Changelog

### v1.0.0 (Current)
- ✅ Complete absensi CRUD operations
- ✅ Salary calculation integration
- ✅ Statistics and reporting
- ✅ Shared hosting compatibility
- ✅ Responsive web interface
- ✅ API proxy architecture

---

**Developed by**: [Your Name]
**Last Updated**: November 2025
**Laravel Version**: 12.38.1
**PHP Version**: 8.4.14+