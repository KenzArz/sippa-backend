# SIPPA Backend - Laravel API

Backend API untuk Sistem Informasi Pemetaan Produksi Pertanian (SIPPA).

API ini menyediakan endpoint untuk clustering provinsi berdasarkan produksi pertanian, evaluasi model machine learning (K-Means, PCA), dan data detail provinsi/komoditas.

## 📋 Tech Stack

- **Framework:** Laravel 13.32.0
- **PHP:** 8.5.10
- **Database:** MySQL/MariaDB (default) atau SQLite (development)
- **Architecture:** RESTful API dengan JSON responses

## 🔧 Prerequisites

Pastikan sudah terinstall di sistem Anda:

- **PHP >= 8.2** (with extensions: mbstring, xml, pdo, mysql)
- **Composer** (dependency manager untuk PHP)
- **MySQL/MariaDB** server (atau SQLite untuk development)
- **Git** (untuk clone repository)

## 🚀 Installation Guide

### Step 1: Clone & Navigate

```bash
cd backend
```

### Step 2: Install PHP Dependencies

```bash
composer install
```

**Troubleshooting:** Jika `composer` belum terinstall, download dari [getcomposer.org](https://getcomposer.org/download/)

### Step 3: Environment Configuration

```bash
# Copy file konfigurasi
cp .env.example .env

# Generate application key (WAJIB!)
php artisan key:generate
```

### Step 4: Database Setup

#### Opsi A: MySQL/MariaDB (Recommended untuk Production)

**1. Buat database baru:**

```bash
# Login ke MySQL
mysql -u root -p

# Di MySQL prompt, jalankan:
CREATE DATABASE sippa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

**2. Update kredensial di `.env`:**

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sippa
DB_USERNAME=root
DB_PASSWORD=your_password_here
```

#### Opsi B: SQLite (Quick Start untuk Development)

**1. Buat file database:**

```bash
touch database/database.sqlite
```

**2. Update `.env`:**

```env
DB_CONNECTION=sqlite
# Comment/hapus baris DB_* lainnya untuk MySQL
```

### Step 5: Setup Database & Import Data

**Jalankan migrations + import data dalam 1 command:**

```bash
php artisan migrate:fresh --seed
```

**Output yang diharapkan:**

```
✓ Creating 11 database tables... DONE (1.2s)
✓ Importing data from sippa_database.sql... DONE (0.3s)

📊 Database Summary:
  ✓ Clusters: 4 records
  ✓ Provinces: 38 records
  ✓ Commodities: 55 records
  ✓ Cluster Profiles: 220 records
  ✓ Province Productions: 2090 records
  ✓ K-Means Evaluations: 7 records
  ✓ PCA Variances: 38 records
  ✓ Padi Monthly Productions: 456 records
```

**Catatan:** Data di-import dari file `database/sippa_database.sql` (1.1 MB) yang sudah berisi semua data yang diperlukan.

### Step 6: Start Development Server

```bash
php artisan serve
```

Server akan berjalan di: **http://localhost:8000**

### Step 7: Test API

Buka browser atau gunakan curl:

```bash
# Test endpoint clusters
curl http://localhost:8000/api/clusters

# Test endpoint provinces
curl http://localhost:8000/api/provinces

# Test model evaluation
curl http://localhost:8000/api/model-evaluation
```

✅ **Jika mendapat response JSON, instalasi berhasil!**

## 📁 Project Structure

```
backend/
├── app/
│   ├── Http/Controllers/Api/          # API Controllers
│   │   ├── ClusterController.php      # Endpoints: /api/clusters/*
│   │   ├── ProvinceController.php     # Endpoints: /api/provinces/*
│   │   ├── CommodityController.php    # Endpoints: /api/commodities/*
│   │   └── ModelEvaluationController.php # Endpoints: /api/model-evaluation/*
│   └── Models/                        # Eloquent Models (Database Tables)
│       ├── Cluster.php                # 4 cluster groups (K-Means result)
│       ├── Province.php               # 38 Indonesian provinces
│       ├── Commodity.php              # 55 agricultural commodities
│       ├── ClusterCommodityProfile.php   # Cluster × Commodity profiling
│       ├── ProvinceCommodityProduction.php # Province × Commodity productions
│       ├── ProvincePadiMonthlyProduction.php # Monthly rice data
│       ├── KmeansEvaluation.php       # K-Means metrics (Silhouette, DBI, etc.)
│       └── PcaVariance.php            # PCA variance explained
├── database/
│   ├── migrations/                    # Database schema definitions
│   ├── seeders/
│   │   ├── DatabaseSeeder.php         # Main seeder (calls SqlImportSeeder)
│   │   └── SqlImportSeeder.php        # Imports from sippa_database.sql
│   └── sippa_database.sql             # Pre-generated data dump (1.1 MB)
├── routes/
│   ├── api.php                        # API routes definition
│   └── web.php                        # Web routes (Laravel welcome page)
├── config/
│   └── cors.php                       # CORS configuration for frontend
└── .env                               # Environment configuration (SECRET!)
```

## API Endpoints

See [API_DOCUMENTATION.md](./API_DOCUMENTATION.md) for complete API reference.

### Quick Reference

**Clusters:**
- `GET /api/clusters` - List all clusters
- `GET /api/clusters/{id}` - Cluster detail
- `GET /api/clusters/{id}/top-commodities` - Top commodities

**Provinces:**
- `GET /api/provinces` - List all provinces
- `GET /api/provinces/map-data` - Map visualization data
- `GET /api/provinces/{id}` - Province detail
- `GET /api/provinces/{id}/top-commodities` - Top commodities

**Commodities:**
- `GET /api/commodities` - List all commodities
- `GET /api/commodities/{id}` - Commodity detail
- `GET /api/commodities/categories` - Category list

**Model Evaluation:**
- `GET /api/model-evaluation` - Complete metrics
- `GET /api/model-evaluation/kmeans` - K-Means evaluation
- `GET /api/model-evaluation/pca` - PCA variance

## 📊 Database Schema

### Tabel Utama (8 tables)

| Tabel | Records | Deskripsi |
|---|---|---|
| **clusters** | 4 | Hasil clustering K-Means (cluster 0-3) |
| **provinces** | 38 | Provinsi Indonesia dengan cluster assignment |
| **commodities** | 55 | Komoditas pertanian (padi, jagung, sayuran, buah, dll) |
| **cluster_commodity_profiles** | 220 | Profil komoditas per cluster (median, z-score) |
| **province_commodity_productions** | 2,090 | Produksi komoditas per provinsi |
| **province_padi_monthly_productions** | 456 | Data bulanan produksi padi (12 bulan × 38 provinsi) |
| **kmeans_evaluations** | 7 | Evaluasi K-Means untuk K=2 hingga K=8 |
| **pca_variances** | 38 | Variance explained oleh PCA components |

### Entity Relationship

```
clusters (1) ──< (N) provinces
provinces (1) ──< (N) province_commodity_productions (N) >── (1) commodities
clusters (1) ──< (N) cluster_commodity_profiles (N) >── (1) commodities
provinces (1) ──< (N) province_padi_monthly_productions
```

### Key Fields

**clusters:**
- `cluster_number` (0-3): Nomor cluster dari K-Means
- `name`: Nama deskriptif cluster
- `karakteristik`: Karakteristik profil produksi

**provinces:**
- `name`: Nama provinsi (e.g., "Jawa Barat", "Sumatera Utara")
- `cluster_id`: Foreign key ke clusters
- `geo_alias`: Alias untuk matching dengan GeoJSON map
- `total_production`: Total produksi semua komoditas

**commodities:**
- `slug`: Identifier (e.g., "padi", "jagung", "alpukat")
- `display_name`: Nama tampilan
- `category`: Kategori (default: "Produksi")
- `has_luas_panen_produktivitas`: Boolean untuk ketersediaan data luas panen

**province_commodity_productions:**
- `produksi`: Produksi aktual (ton)
- `luas_panen`: Luas panen (ha) - nullable
- `produktivitas`: Produktivitas (ku/ha) - nullable
- `median_cluster`: Median produksi di cluster yang sama
- `selisih_dengan_median_cluster`: Selisih dengan median

## 🔄 Development Commands

### Database Management

```bash
# Reset database sepenuhnya (hapus semua data + reimport)
php artisan migrate:fresh --seed

# Hanya reset struktur tabel (tanpa data)
php artisan migrate:fresh

# Import data saja (setelah migrate)
php artisan db:seed

# Rollback migration terakhir
php artisan migrate:rollback

# Check migration status
php artisan migrate:status
```

### Route & Cache Management

```bash
# List semua API endpoints
php artisan route:list --path=api

# Clear application cache
php artisan cache:clear

# Clear configuration cache
php artisan config:clear

# Optimize untuk production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Database Inspection

```bash
# Open Laravel Tinker (interactive shell)
php artisan tinker

# Di Tinker, test queries:
>>> App\Models\Cluster::count()
=> 4

>>> App\Models\Province::with('cluster')->first()
=> App\Models\Province {...}

>>> DB::table('provinces')->where('name', 'Jawa Barat')->first()
=> {...}

# Exit tinker
>>> exit
```

## 🌐 CORS Configuration

CORS (Cross-Origin Resource Sharing) dikonfigurasi di `config/cors.php` untuk mengizinkan frontend mengakses API.

**Development (Current):**
```php
'allowed_origins' => ['*'], // Allow all origins
```

**Production (Recommended):**
```php
'allowed_origins' => [
    'https://sippa-frontend.com',
    'https://www.sippa-frontend.com',
],
```

Jika frontend berjalan di port berbeda (e.g., `http://localhost:5173`), pastikan sudah ditambahkan ke `allowed_origins`.

## 📦 Data Source

Data di-import dari **`database/sippa_database.sql`** (1.1 MB) yang berisi:

- ✅ Data sudah di-proses dari CSV ML team
- ✅ Tidak perlu akses ke file CSV eksternal
- ✅ Import cepat (~300ms)
- ✅ Portable - bisa dijalankan di environment manapun

**File SQL ini sudah include:**
- 4 clusters dengan karakteristik lengkap
- 38 provinsi dengan cluster assignment & geo_alias
- 55 komoditas dengan metadata lengkap
- 2,090 produksi komoditas per provinsi
- 456 data bulanan produksi padi
- 7 evaluasi K-Means (K=2 hingga K=8)
- 38 komponen PCA dengan variance explained

**Regenerate SQL (Advanced - Optional):**

Jika tim ML memberikan CSV update baru, ikuti langkah ini:

1. Letakkan CSV files di `../data-resource/ML_OUTPUT/` (di luar folder backend)
2. Jalankan seeder khusus:

```bash
php artisan db:seed --class=SippaSeeder
```

Ini akan:
1. Baca CSV files dari lokasi external (`../data-resource/ML_OUTPUT/`)
2. Import ke database dengan validasi ketat
3. Generate file `sippa_database.sql` baru

**CSV files yang diperlukan:**
- `Ringkasan_Cluster.csv`
- `Hasil_Clustering_Provinsi.csv`
- `Profil_Cluster.csv`
- `Profil_Provinsi.csv`
- `Evaluasi_KMeans.csv`
- `PCA_Variance.csv`
- `Data_Dictionary.csv`
- `Master_Dataset_Pertanian_9_Dataset_Final.csv` (untuk data bulanan padi)

⚠️ **Catatan:** Untuk penggunaan normal, Anda **tidak perlu** melakukan ini. File `sippa_database.sql` sudah berisi semua data yang diperlukan.

## Environment Variables

Key `.env` variables:

```env
APP_NAME=SIPPA-Backend
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite
# DB_CONNECTION=mysql (for MySQL)

CORS_ALLOWED_ORIGINS=*
```

## 🐛 Troubleshooting

### Error: "database not found"

**MySQL:**
```bash
# Pastikan database sudah dibuat
mysql -u root -p -e "SHOW DATABASES;"

# Jika belum ada, buat:
mysql -u root -p -e "CREATE DATABASE sippa;"
```

**SQLite:**
```bash
# Pastikan file database.sqlite ada
ls -la database/database.sqlite

# Jika belum, buat:
touch database/database.sqlite
```

### Error: "Class 'PDO' not found"

Install PHP MySQL extension:

```bash
# Ubuntu/Debian
sudo apt-get install php8.2-mysql php8.2-pdo

# macOS (via Homebrew)
brew install php@8.2

# Windows
# Enable di php.ini: extension=pdo_mysql
```

### Error: "SQLSTATE[HY000] [2002] Connection refused"

MySQL server belum running:

```bash
# Ubuntu/Debian
sudo systemctl start mysql

# macOS
brew services start mysql

# Windows
# Start MySQL dari XAMPP/WAMP Control Panel
```

### Error: "No such file or directory" saat seeding

Pastikan file `database/sippa_database.sql` ada:

```bash
ls -lh database/sippa_database.sql

# Jika tidak ada, file mungkin hilang atau belum di-commit
# Hubungi tim untuk mendapatkan file SQL atau regenerate dari CSV
```

### API returns empty data

```bash
# Check database isinya ada
php artisan tinker
>>> DB::table('provinces')->count()
=> 38

# Jika 0, re-seed:
>>> exit
php artisan db:seed --force
```

### CORS error di browser

Update `config/cors.php`:

```php
'allowed_origins' => ['http://localhost:5173'], // Sesuaikan dengan frontend URL
```

Lalu clear cache:

```bash
php artisan config:clear
```

## 🚀 Production Deployment

### 1. Environment Setup

Update `.env` untuk production:

```env
APP_NAME=SIPPA-Backend
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api.sippa.com

DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_PORT=3306
DB_DATABASE=sippa_production
DB_USERNAME=sippa_user
DB_PASSWORD=secure_password_here

CORS_ALLOWED_ORIGINS=https://sippa-frontend.com
```

### 2. Install Dependencies (Production Mode)

```bash
composer install --optimize-autoloader --no-dev
```

### 3. Optimize Application

```bash
# Generate optimized autoloader
composer dump-autoload --optimize

# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache
```

### 4. Database Migration

```bash
php artisan migrate --force
php artisan db:seed --force
```

### 5. File Permissions

```bash
# Set proper permissions
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 6. Web Server Configuration

**Nginx:**

```nginx
server {
    listen 443 ssl http2;
    server_name api.sippa.com;
    
    root /var/www/sippa-backend/public;
    index index.php;

    # SSL certificates
    ssl_certificate /path/to/cert.pem;
    ssl_certificate_key /path/to/key.pem;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

**Apache (.htaccess already configured):**

Pastikan `mod_rewrite` enabled:

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### 7. Monitoring & Logs

```bash
# View Laravel logs
tail -f storage/logs/laravel.log

# Monitor PHP-FPM (if using Nginx)
tail -f /var/log/php8.2-fpm.log

# Check API health
curl https://api.sippa.com/api/clusters
```

## 👥 Team Workflow

### Menambah Endpoint Baru

**1. Buat/update controller:**

```bash
# Buat controller baru (jika belum ada)
php artisan make:controller Api/NewController

# Edit controller di app/Http/Controllers/Api/NewController.php
```

**2. Tambahkan route di `routes/api.php`:**

```php
Route::get('/new-endpoint', [NewController::class, 'index']);
```

**3. Test endpoint:**

```bash
# Start server
php artisan serve

# Test dengan curl
curl http://localhost:8000/api/new-endpoint
```

**4. Update dokumentasi:**

Tambahkan endpoint baru di `API_DOCUMENTATION.md`

### Mengubah Schema Database

**1. Buat migration:**

```bash
php artisan make:migration add_column_to_table
```

**2. Edit file migration di `database/migrations/`:**

```php
public function up(): void
{
    Schema::table('table_name', function (Blueprint $table) {
        $table->string('new_column')->nullable();
    });
}
```

**3. Jalankan migration:**

```bash
php artisan migrate
```

**4. Update model:**

Tambahkan field baru di `$fillable` dan `$casts` di Model terkait.

### Git Workflow

```bash
# Pull latest changes
git pull origin main

# Buat feature branch
git checkout -b feature/nama-fitur

# Make changes & commit
git add .
git commit -m "feat: deskripsi perubahan"

# Push branch
git push origin feature/nama-fitur

# Buat Pull Request di GitHub/GitLab
```

### Code Style Guidelines

- **PSR-12** coding standards
- **Type hints** untuk semua parameters & return values
- **Eloquent ORM** untuk database queries (avoid raw SQL)
- **Resource Controllers** untuk RESTful endpoints
- **Validation** di controller atau Form Request
- **Comments** untuk logic yang kompleks

**Contoh good code:**

```php
public function show(string $id): JsonResponse
{
    $province = Province::with('cluster')->find($id);
    
    if (!$province) {
        return response()->json([
            'success' => false,
            'message' => 'Province not found',
        ], 404);
    }
    
    return response()->json([
        'success' => true,
        'data' => $province,
    ]);
}
```

## 📚 Additional Resources

- **API Documentation:** `API_DOCUMENTATION.md` - Complete API reference dengan examples
- **Laravel Docs:** [laravel.com/docs](https://laravel.com/docs) - Official Laravel documentation
- **Eloquent ORM:** [laravel.com/docs/eloquent](https://laravel.com/docs/eloquent) - Database queries
- **Routing:** [laravel.com/docs/routing](https://laravel.com/docs/routing) - API routes
- **Postman Collection:** Import API endpoints untuk testing (coming soon)

## 📝 License

Proprietary - Capstone Project Semester 5

## 📞 Contact & Support

Untuk pertanyaan, bug reports, atau request fitur baru:

- **Backend Lead:** [Nama]
- **Database:** [Nama]
- **API Integration:** [Nama]

---

**Status:** ✅ Production Ready  
**Last Updated:** September 19, 2026  
**Version:** 1.0.0
