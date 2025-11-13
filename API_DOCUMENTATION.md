# API Documentation - Sistem Absensi

## Base URL
```
http://localhost:8000/api
```

## Authentication
API menggunakan Laravel Sanctum dengan Bearer Token authentication.

Setiap request yang memerlukan autentikasi harus menyertakan header:
```
Authorization: Bearer {token}
```

---

## Endpoints

### 1. Health Check
```http
GET /api/health
```

**Response:**
```json
{
    "success": true,
    "message": "API is running",
    "timestamp": "2024-01-15 10:30:00"
}
```

---

### 2. Register (Opsional)
```http
POST /api/register
```

**Request Body:**
```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "role": "USER",
    "karyawan_id": "uuid-karyawan"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Registrasi berhasil",
    "data": {
        "user": {
            "id": "uuid",
            "name": "John Doe",
            "email": "john@example.com",
            "role": "USER"
        },
        "token": "1|xxxxxxxxxxxxxx",
        "token_type": "Bearer"
    }
}
```

---

### 3. Login
```http
POST /api/login
```

**Request Body:**
```json
{
    "email": "john@example.com",
    "password": "password123"
}
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "Login berhasil",
    "data": {
        "user": {
            "id": "uuid",
            "name": "John Doe",
            "email": "john@example.com",
            "role": "USER",
            "karyawan_id": "uuid-karyawan"
        },
        "token": "1|xxxxxxxxxxxxxx",
        "token_type": "Bearer"
    }
}
```

**Error Response (422):**
```json
{
    "message": "The email field is required. (and 1 more error)",
    "errors": {
        "email": ["Email atau password salah."]
    }
}
```

---

### 4. Get Profile (Protected)
```http
GET /api/profile
```

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "message": "Data user berhasil diambil",
    "data": {
        "id": "uuid",
        "name": "John Doe",
        "email": "john@example.com",
        "role": "USER",
        "karyawan_id": "uuid-karyawan",
        "karyawan": {
            "id": "uuid-karyawan",
            "nip": "123456",
            "nama": "John Doe",
            "jabatan": "Developer",
            "departemen": "IT"
        },
        "created_at": "2024-01-15T10:30:00.000000Z",
        "updated_at": "2024-01-15T10:30:00.000000Z"
    }
}
```

---

### 5. Update Profile (Protected)
```http
PUT /api/profile
```

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
    "name": "John Doe Updated",
    "email": "johnnew@example.com",
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Profile berhasil diupdate",
    "data": {
        "id": "uuid",
        "name": "John Doe Updated",
        "email": "johnnew@example.com",
        "role": "USER"
    }
}
```

---

### 6. Logout (Protected)
```http
POST /api/logout
```

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "message": "Logout berhasil"
}
```

---

### 7. Get Current User (Protected)
```http
GET /api/user
```

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "id": "uuid",
        "name": "John Doe",
        "email": "john@example.com",
        "role": "USER",
        "karyawan_id": "uuid-karyawan",
        "created_at": "2024-01-15T10:30:00.000000Z",
        "updated_at": "2024-01-15T10:30:00.000000Z"
    }
}
```

---

## Error Responses

### Unauthenticated (401)
```json
{
    "message": "Unauthenticated."
}
```

### Validation Error (422)
```json
{
    "message": "The email field is required.",
    "errors": {
        "email": ["The email field is required."]
    }
}
```

### Server Error (500)
```json
{
    "message": "Server Error",
    "error": "Error details"
}
```

---

## Testing dengan Postman/Insomnia

### 1. Login
- Method: POST
- URL: `http://localhost:8000/api/login`
- Body (JSON):
```json
{
    "email": "admin@example.com",
    "password": "password"
}
```
- Copy `token` dari response

### 2. Get Profile (with token)
- Method: GET
- URL: `http://localhost:8000/api/profile`
- Headers:
  - Key: `Authorization`
  - Value: `Bearer {paste-token-here}`

---

## Deployment ke Shared Hosting cPanel

### File yang Perlu Diupload:
1. Semua file kecuali folder `public` → upload ke root/private folder
2. Isi folder `public` → upload ke `public_html`

### Setting di cPanel:
1. Buat database MySQL dan user
2. Update `.env` di server:
```env
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=nama_database
DB_USERNAME=user_database
DB_PASSWORD=password_database
```

### File .htaccess di public_html:
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

### Jalankan migration di cPanel Terminal:
```bash
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Set Permissions:
```bash
chmod -R 755 storage
chmod -R 755 bootstrap/cache
```
