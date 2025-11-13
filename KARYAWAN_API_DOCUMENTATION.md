# API Management Karyawan - PT Pencari Error Sejati

## Base URL
```
Production: https://yourdomain.com/api
Development: http://127.0.0.1:8000/api
```

## Authentication
Semua endpoint karyawan memerlukan autentikasi menggunakan **Laravel Sanctum Bearer Token**.

### Header Required:
```http
Authorization: Bearer {your-token}
Content-Type: application/json
Accept: application/json
```

### Cara Mendapatkan Token:
```bash
POST /api/login
Content-Type: application/json

{
    "email": "admin@pencarierror.com",
    "password": "password123"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Login berhasil",
    "data": {
        "user": {
            "id": "uuid",
            "name": "Budi Santoso",
            "email": "admin@pencarierror.com",
            "role": "ADMIN"
        },
        "token": "1|abcdefghijklmnopqrstuvwxyz"
    }
}
```

---

## 1. List Karyawan (dengan Filter & Pencarian)

### Endpoint:
```
GET /api/karyawan
```

### Query Parameters:
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| status | string | No | Filter by status: `AKTIF`, `CUTI`, `RESIGN` |
| departemen | string | No | Filter by departemen: `IT`, `HR`, `Finance`, etc |
| jabatan | string | No | Filter by jabatan (partial match) |
| search | string | No | Search by nama, nip, atau email |
| per_page | integer | No | Items per page (default: 15) |
| page | integer | No | Page number (default: 1) |

### Example Request:
```bash
# Get all karyawan
curl -X GET "http://127.0.0.1:8000/api/karyawan" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"

# Filter by departemen IT
curl -X GET "http://127.0.0.1:8000/api/karyawan?departemen=IT" \
  -H "Authorization: Bearer YOUR_TOKEN"

# Search by name
curl -X GET "http://127.0.0.1:8000/api/karyawan?search=Budi" \
  -H "Authorization: Bearer YOUR_TOKEN"

# Combined filters
curl -X GET "http://127.0.0.1:8000/api/karyawan?status=AKTIF&departemen=IT&per_page=20" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Response Success (200):
```json
{
    "success": true,
    "message": "Data karyawan berhasil diambil",
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": "uuid-1",
                "nip": "EMP001",
                "nama": "Budi Santoso",
                "email": "admin@pencarierror.com",
                "jabatan": "CEO",
                "departemen": "IT",
                "telepon": "081234567890",
                "alamat": "Jl. Merdeka No. 123",
                "tanggal_masuk": "2015-01-10",
                "status": "AKTIF",
                "gaji_pokok": "25000000.00",
                "tunjangan_jabatan": "0.00",
                "tunjangan_transport": "0.00",
                "tunjangan_makan": "0.00",
                "rfid_card_number": "A1B2C3D4",
                "created_at": "2025-11-13T14:39:57.000000Z",
                "user": {
                    "id": "uuid-user",
                    "name": "Budi Santoso",
                    "email": "admin@pencarierror.com",
                    "role": "ADMIN"
                },
                "rfid_card": {
                    "id": "uuid-rfid",
                    "card_number": "A1B2C3D4",
                    "status": "ASSIGNED",
                    "assigned_at": "2025-11-13T14:39:57.000000Z"
                }
            }
        ],
        "per_page": 15,
        "total": 20
    }
}
```

---

## 2. Detail Karyawan

### Endpoint:
```
GET /api/karyawan/{id}
```

### Example Request:
```bash
curl -X GET "http://127.0.0.1:8000/api/karyawan/uuid-1" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

### Response Success (200):
```json
{
    "success": true,
    "message": "Detail karyawan berhasil diambil",
    "data": {
        "id": "uuid-1",
        "nip": "EMP001",
        "nama": "Budi Santoso",
        "email": "admin@pencarierror.com",
        "jabatan": "CEO",
        "departemen": "IT",
        "status": "AKTIF",
        "user": {
            "id": "uuid-user",
            "email": "admin@pencarierror.com",
            "role": "ADMIN"
        },
        "rfid_card": { },
        "absensi": [ ],
        "lembur": [ ],
        "gaji": [ ],
        "kpi": [ ]
    }
}
```

---

## 3. Buat Karyawan Baru (dengan Akun Login)

### Endpoint:
```
POST /api/karyawan
```

### Request Body:
```json
{
    "nip": "EMP021",
    "nama": "John Doe",
    "email": "john.doe@pencarierror.com",
    "jabatan": "Software Engineer",
    "departemen": "IT",
    "telepon": "081234567810",
    "alamat": "Jl. Contoh No. 123, Jakarta",
    "tanggal_masuk": "2025-11-13",
    "status": "AKTIF",
    "gaji_pokok": 10000000,
    "tunjangan_jabatan": 2000000,
    "tunjangan_transport": 500000,
    "tunjangan_makan": 400000,
    "rfid_card_number": "1A2B3C4D",
    "password": "password123",
    "role": "USER"
}
```

### Field Validation:
| Field | Type | Required | Rules |
|-------|------|----------|-------|
| nip | string | **Yes** | max:100, unique |
| nama | string | **Yes** | max:255 |
| email | string | **Yes** | email, unique |
| jabatan | string | **Yes** | max:255 |
| departemen | string | **Yes** | max:255 |
| telepon | string | No | max:50 |
| alamat | text | No | - |
| tanggal_masuk | date | No | format: YYYY-MM-DD |
| status | enum | No | AKTIF, CUTI, RESIGN (default: AKTIF) |
| gaji_pokok | decimal | No | min:0 (default: 0) |
| tunjangan_jabatan | decimal | No | min:0 (default: 0) |
| tunjangan_transport | decimal | No | min:0 (default: 0) |
| tunjangan_makan | decimal | No | min:0 (default: 0) |
| rfid_card_number | string | No | must exist in available_rfid table |
| password | string | **Yes** | min:8 |
| role | enum | No | ADMIN, MANAGER, USER (default: USER) |

### Example Request:
```bash
curl -X POST "http://127.0.0.1:8000/api/karyawan" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "nip": "EMP021",
    "nama": "John Doe",
    "email": "john.doe@pencarierror.com",
    "jabatan": "Software Engineer",
    "departemen": "IT",
    "password": "password123",
    "role": "USER"
  }'
```

### Response Success (201):
```json
{
    "success": true,
    "message": "Karyawan dan akun login berhasil dibuat",
    "data": {
        "karyawan": {
            "id": "new-uuid",
            "nip": "EMP021",
            "nama": "John Doe",
            "email": "john.doe@pencarierror.com",
            "jabatan": "Software Engineer",
            "departemen": "IT",
            "status": "AKTIF",
            "created_at": "2025-11-13T15:00:00.000000Z"
        },
        "user": {
            "id": "user-uuid",
            "email": "john.doe@pencarierror.com",
            "role": "USER"
        },
        "login_credentials": {
            "email": "john.doe@pencarierror.com",
            "password": "(hidden for security)"
        }
    }
}
```

### Response Error (422):
```json
{
    "success": false,
    "message": "Validasi gagal",
    "errors": {
        "email": [
            "The email has already been taken."
        ],
        "nip": [
            "The nip has already been taken."
        ]
    }
}
```

---

## 4. Update Karyawan

### Endpoint:
```
PUT /api/karyawan/{id}
```

### Request Body (semua field optional):
```json
{
    "nama": "John Doe Updated",
    "jabatan": "Senior Software Engineer",
    "gaji_pokok": 12000000,
    "status": "AKTIF",
    "password": "newpassword123",
    "role": "MANAGER"
}
```

### Example Request:
```bash
curl -X PUT "http://127.0.0.1:8000/api/karyawan/uuid-1" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "jabatan": "Senior Software Engineer",
    "gaji_pokok": 12000000
  }'
```

### Response Success (200):
```json
{
    "success": true,
    "message": "Data karyawan berhasil diupdate",
    "data": {
        "id": "uuid-1",
        "nip": "EMP021",
        "nama": "John Doe",
        "jabatan": "Senior Software Engineer",
        "gaji_pokok": "12000000.00",
        "status": "AKTIF",
        "updated_at": "2025-11-13T15:30:00.000000Z"
    }
}
```

---

## 5. Hapus Karyawan

### Endpoint:
```
DELETE /api/karyawan/{id}
```

### Example Request:
```bash
curl -X DELETE "http://127.0.0.1:8000/api/karyawan/uuid-1" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

### Response Success (200):
```json
{
    "success": true,
    "message": "Karyawan dan akun login berhasil dihapus"
}
```

### Notes:
- Menghapus karyawan akan otomatis:
  - Menghapus akun user terkait
  - Melepaskan RFID card (status menjadi AVAILABLE)
  - Menghapus data absensi, lembur, gaji, KPI (cascade delete)

---

## 6. Statistik Karyawan

### Endpoint:
```
GET /api/karyawan/statistics
```

### Example Request:
```bash
curl -X GET "http://127.0.0.1:8000/api/karyawan/statistics" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

### Response Success (200):
```json
{
    "success": true,
    "message": "Statistik karyawan berhasil diambil",
    "data": {
        "total_karyawan": 20,
        "karyawan_aktif": 18,
        "karyawan_cuti": 1,
        "karyawan_resign": 1,
        "by_departemen": [
            {
                "departemen": "IT",
                "total": 12
            },
            {
                "departemen": "HR",
                "total": 3
            },
            {
                "departemen": "Finance",
                "total": 3
            },
            {
                "departemen": "Marketing",
                "total": 2
            }
        ],
        "by_jabatan": [
            {
                "jabatan": "Developer",
                "total": 6
            },
            {
                "jabatan": "Manager",
                "total": 4
            }
        ]
    }
}
```

---

## 7. Bulk Create Karyawan

### Endpoint:
```
POST /api/karyawan/bulk
```

### Request Body:
```json
{
    "karyawan": [
        {
            "nip": "EMP022",
            "nama": "Alice Smith",
            "email": "alice@pencarierror.com",
            "jabatan": "QA Engineer",
            "departemen": "IT",
            "password": "password123",
            "role": "USER"
        },
        {
            "nip": "EMP023",
            "nama": "Bob Johnson",
            "email": "bob@pencarierror.com",
            "jabatan": "DevOps",
            "departemen": "IT",
            "password": "password123",
            "role": "USER"
        }
    ]
}
```

### Example Request:
```bash
curl -X POST "http://127.0.0.1:8000/api/karyawan/bulk" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d @bulk_karyawan.json
```

### Response Success (201):
```json
{
    "success": true,
    "message": "2 karyawan berhasil dibuat",
    "data": {
        "created": [
            {
                "id": "uuid-1",
                "nip": "EMP022",
                "nama": "Alice Smith"
            },
            {
                "id": "uuid-2",
                "nip": "EMP023",
                "nama": "Bob Johnson"
            }
        ],
        "errors": []
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
    "message": "Karyawan tidak ditemukan",
    "error": "No query results for model..."
}
```

### 500 Internal Server Error:
```json
{
    "success": false,
    "message": "Gagal membuat karyawan",
    "error": "Database connection error..."
}
```

---

## Kompatibilitas Shared Hosting cPanel

### ✅ Fitur yang Kompatibel:
1. **Laravel Sanctum** - Token-based authentication (tidak perlu Redis/Memcached)
2. **Database Transactions** - Menggunakan MySQL standar
3. **UUID Primary Keys** - Compatible dengan semua versi MySQL 5.7+
4. **REST API** - Standard HTTP methods
5. **JSON Responses** - No special extensions needed

### 📋 Deployment ke cPanel:

1. **Upload Files:**
   ```bash
   # Zip project (exclude vendor, node_modules)
   zip -r absensi.zip . -x "vendor/*" "node_modules/*" ".git/*"
   ```

2. **Di cPanel:**
   - Upload `absensi.zip` ke public_html
   - Extract files
   - Pindahkan semua file (kecuali `public`) ke folder di luar public_html
   - Pindahkan isi folder `public` ke `public_html`

3. **Install Dependencies:**
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

4. **Environment Setup:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Database Migration:**
   ```bash
   php artisan migrate --force
   php artisan db:seed --force
   ```

6. **File Permissions:**
   ```bash
   chmod -R 755 storage bootstrap/cache
   ```

### 🔧 .htaccess Configuration:
File `public_html/.htaccess`:
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

File `public_html/public/.htaccess`:
```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

---

## Testing Endpoints

### Postman Collection:
Import this JSON to Postman:
```json
{
    "info": {
        "name": "Karyawan Management API",
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
                    "raw": "{\n  \"email\": \"admin@pencarierror.com\",\n  \"password\": \"password123\"\n}"
                }
            }
        },
        {
            "name": "List Karyawan",
            "request": {
                "method": "GET",
                "url": "{{base_url}}/api/karyawan"
            }
        },
        {
            "name": "Create Karyawan",
            "request": {
                "method": "POST",
                "url": "{{base_url}}/api/karyawan",
                "body": {
                    "mode": "raw",
                    "raw": "{\n  \"nip\": \"EMP021\",\n  \"nama\": \"Test User\",\n  \"email\": \"test@example.com\",\n  \"jabatan\": \"Developer\",\n  \"departemen\": \"IT\",\n  \"password\": \"password123\"\n}"
                }
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

# 2. List karyawan
curl -X GET "http://127.0.0.1:8000/api/karyawan" \
  -H "Authorization: Bearer $TOKEN" | jq

# 3. Create karyawan
curl -X POST "http://127.0.0.1:8000/api/karyawan" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "nip": "EMP099",
    "nama": "Test User",
    "email": "test@example.com",
    "jabatan": "Tester",
    "departemen": "QA",
    "password": "password123"
  }' | jq
```

---

## Support
Untuk pertanyaan atau issue, silakan hubungi:
- Email: support@pencarierror.com
- Phone: +62 21 1234 5678
