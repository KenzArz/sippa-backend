# Setup Guide - SIPPA Backend

**Quick Start untuk Tim Dev** - Tanpa dependency ke CSV files!

## ⚡ Quick Start (5 menit)

```bash
# 1. Install dependencies
cd backend
composer install

# 2. Configure environment
cp .env.example .env
php artisan key:generate

# 3. Create MySQL database
mysql -u root -p -e "CREATE DATABASE sippa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# (Optional) Update DB password di .env jika tidak pakai root

# 4. Run migrations & import data
php artisan migrate --seed

# 5. Start server
php artisan serve
```

✅ API ready at: `http://localhost:8000/api`

---

## 📦 Data Import Methods

### Method 1: SQL File (Recommended) ✨

**Keuntungan:**
- ✅ **Tidak butuh CSV files** - works di mana saja!
- ✅ Super cepat (~100ms)
- ✅ Tidak ada path dependency
- ✅ Portable dan mudah di-share

**Cara pakai:**
```bash
php artisan migrate --seed
```

File yang dipakai: `backend/database/sippa_complete.sql` (600KB)

### Method 2: Import dari CSV

**Hanya jika punya akses ke CSV files** di `frontend/data-resource/ML_OUTPUT/`:

```bash
php artisan db:seed --class=SippaSeeder
```

---

## 🔄 Reset Database

```bash
# Drop semua table, run migrations, import data
php artisan migrate:fresh --seed
```

---

## 📊 Hasil Import

Setelah seeding berhasil, database akan berisi:

| Tabel | Records |
|-------|---------|
| clusters | 4 |
| provinces | 38 |
| commodities | 55 |
| cluster_commodity_profiles | 220 |
| province_commodity_productions | 2,090 |
| kmeans_evaluations | 7 |
| pca_variances | 38 |

**Total: 2,452 records**

---

## 🧪 Test API Endpoints

```bash
# Test clusters
curl http://localhost:8000/api/clusters

# Test provinces for map
curl http://localhost:8000/api/provinces/map-data

# Test model evaluation
curl http://localhost:8000/api/model-evaluation
```

Semua endpoint mengembalikan JSON dengan format:
```json
{
  "success": true,
  "data": { ... }
}
```

---

## 🗂️ File Structure

```
backend/
├── database/
│   ├── database.sqlite           # SQLite database file
│   ├── sippa_complete.sql        # ✨ Pre-generated SQL dump (600KB)
│   ├── migrations/               # 8 migration files
│   └── seeders/
│       ├── DatabaseSeeder.php    # Main seeder (calls SqlImportSeeder)
│       ├── SqlImportSeeder.php   # ✨ Import from .sql file
│       └── SippaSeeder.php       # Alternative: import from CSV
├── app/Models/                   # 8 Eloquent models
├── app/Http/Controllers/Api/     # 4 API controllers
├── routes/api.php                # API routes
└── config/cors.php               # CORS config
```

---

## 💾 Database Options

### MySQL (Default - Recommended)

**Setup:**
```bash
# Create database
mysql -u root -p -e "CREATE DATABASE sippa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

**Configure `.env`:**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sippa
DB_USERNAME=root
DB_PASSWORD=your_password
```

**Keuntungan:**
- Production-ready
- Better performance untuk large datasets
- Full ACID compliance
- Recommended untuk deployment

### SQLite (Optional - untuk Development)

```bash
# Create database file
touch database/database.sqlite
```

**Update `.env`:**
```env
DB_CONNECTION=sqlite
# Comment out MySQL configs
```

**Keuntungan:**
- Zero configuration
- File-based (portable)
- Perfect untuk quick testing

---

## 🚨 Troubleshooting

### Error: "SQL file not found"

**Solusi:** File `sippa_complete.sql` harus ada di `backend/database/`

Jika hilang, regenerate dengan:
```bash
php artisan db:seed --class=SippaSeeder  # Import dari CSV
sqlite3 database/database.sqlite ".dump" > database/sippa_complete.sql  # Export ke .sql
```

### Error: "Class 'SQLite3' not found"

**Solusi:** Install PHP SQLite extension
```bash
# Ubuntu/Debian
sudo apt-get install php-sqlite3

# macOS (usually included)
brew install php

# Windows (enable in php.ini)
extension=sqlite3
```

### Error: "SQLSTATE[HY000] [2002] Connection refused"

**Solusi:** MySQL server tidak running
```bash
# Start MySQL
# Ubuntu/Debian
sudo systemctl start mysql

# macOS
brew services start mysql

# Windows
net start MySQL80
```

### Error: "Access denied for user 'root'@'localhost'"

**Solusi:** Update password di `.env`
```env
DB_PASSWORD=your_actual_password
```

Atau reset MySQL root password:
```bash
# Ubuntu/Debian
sudo mysql
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'new_password';
FLUSH PRIVILEGES;
```

### Seeding lambat / timeout

**Solusi:** Pastikan pakai `SqlImportSeeder` (bukan `SippaSeeder`)

SqlImportSeeder: ~100ms  
SippaSeeder (CSV): ~1500ms

---

## 📚 Dokumentasi Lengkap

- `README.md` - Setup lengkap, project structure, development workflow
- `API_DOCUMENTATION.md` - Complete API reference (16 endpoints)
- `BACKEND_SETUP_COMPLETE.md` - Summary & statistics

---

## 👥 Untuk Tim Dev

### Clone Repository Baru

```bash
# 1. Clone repo
git clone <repo-url> sippa-backend
cd sippa-backend

# 2. Install & setup
composer install
cp .env.example .env
php artisan key:generate

# 3. Create MySQL database
mysql -u root -p -e "CREATE DATABASE sippa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# (Update .env dengan MySQL password jika perlu)

# 4. Run migrations & seed
php artisan migrate --seed

# 5. Start server
php artisan serve
```

### Update dari Git Pull

```bash
# Setelah git pull, cek apakah ada migrations baru
php artisan migrate

# Jika butuh reset data
php artisan migrate:fresh --seed
```

---

## 🎯 Checklist Setup

- [ ] PHP >= 8.2 installed
- [ ] Composer installed
- [ ] MySQL server installed & running
- [ ] `composer install` berhasil
- [ ] `.env` file configured dengan MySQL credentials
- [ ] Database `sippa` created
- [ ] `php artisan migrate --seed` berhasil
- [ ] API accessible: `curl http://localhost:8000/api/clusters`
- [ ] Response JSON dengan `"success": true`

**Jika semua ✅ maka backend ready!** 🚀

---

## 🔗 Next: Frontend Integration

Update frontend API base URL:
```typescript
// src/config/api.ts
export const API_BASE_URL = 'http://localhost:8000/api';
```

Replace static JSON imports dengan API calls:
```typescript
// Before
import clustersData from './data/clusters.json';

// After
const response = await fetch(`${API_BASE_URL}/clusters`);
const { data: clustersData } = await response.json();
```

---

**Setup selesai! Tim dev bisa langsung coding tanpa setup rumit.** ✨
