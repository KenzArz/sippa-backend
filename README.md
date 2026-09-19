# SIPPA Backend - Laravel API

Backend API untuk Sistem Informasi Pemetaan Produksi Pertanian (SIPPA).

## Tech Stack

- **Framework:** Laravel 13.32.0
- **PHP:** 8.5.10
- **Database:** MySQL (default) / SQLite (optional)
- **Architecture:** RESTful API with JSON responses

## Prerequisites

- PHP >= 8.2
- Composer
- MySQL server (or SQLite for development)

## Installation

### 1. Install Dependencies
```bash
composer install
```

### 2. Environment Configuration
```bash
cp .env.example .env
php artisan key:generate
```

### 3. Database Setup

**Using MySQL (Default):**

1. Create database:
```bash
mysql -u root -p -e "CREATE DATABASE sippa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

2. Update `.env` credentials if needed:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sippa
DB_USERNAME=root
DB_PASSWORD=your_password_here
```

**Alternative - Using SQLite:**

If you prefer SQLite for development:
```bash
touch database/database.sqlite
```

Then update `.env`:
```env
DB_CONNECTION=sqlite
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=sippa
# DB_USERNAME=root
# DB_PASSWORD=
```

### 4. Run Migrations & Seed Data
```bash
php artisan migrate --seed
```

**What this does:**
- Creates 8 SIPPA tables (clusters, provinces, commodities, etc.)
- Imports data from `database/sippa_complete.sql` file (600KB)
- Seeds 4 clusters, 38 provinces, 55 commodities, 2090+ productions

```bash
php artisan db:seed --class=SippaSeeder
```

### 5. Start Development Server
```bash
php artisan serve
```

API available at: `http://localhost:8000/api`

## Project Structure

```
backend/
├── app/
│   ├── Http/Controllers/Api/     # API Controllers
│   │   ├── ClusterController.php
│   │   ├── ProvinceController.php
│   │   ├── CommodityController.php
│   │   └── ModelEvaluationController.php
│   └── Models/                   # Eloquent Models
│       ├── Cluster.php
│       ├── Province.php
│       ├── Commodity.php
│       ├── ClusterCommodityProfile.php
│       ├── ProvinceCommodityProduction.php
│       ├── KmeansEvaluation.php
│       └── PcaVariance.php
├── database/
│   ├── migrations/               # Database schema migrations
│   └── seeders/
│       ├── DatabaseSeeder.php
│       └── SippaSeeder.php       # Main CSV data importer
├── routes/
│   ├── api.php                   # API routes
│   └── web.php                   # Web routes
└── config/
    └── cors.php                  # CORS configuration
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

## Database Schema

### Core Tables

1. **clusters** - 4 K-Means clusters (0-3)
   - UUID primary key
   - cluster_number, name, jumlah_provinsi, karakteristik

2. **provinces** - 38 Indonesian provinces
   - UUID primary key
   - Foreign key to clusters
   - name, cluster_id, geo_alias, total_production

3. **commodities** - 55 agricultural commodities
   - UUID primary key
   - slug, display_name, category, reference_year

4. **cluster_commodity_profiles** - 220 profiles
   - Cluster × Commodity profiling
   - median_produksi, mean_z_score

5. **province_commodity_productions** - 2090+ records
   - Province × Commodity productions
   - produksi, median_cluster, selisih

6. **kmeans_evaluations** - 7 K-Means metrics (K=2 to K=8)
   - inertia, silhouette_score, davies_bouldin_index

7. **pca_variances** - 38 PCA components
   - explained_variance, cumulative_variance

## Development

### Running Tests
```bash
php artisan test
```

### Database Management

**Reset database:**
```bash
php artisan migrate:fresh
```

**Re-seed only:**
```bash
php artisan db:seed --class=SippaSeeder
```

**Check routes:**
```bash
php artisan route:list --path=api
```

### Code Style

Follow Laravel conventions:
- PSR-12 coding standards
- Eloquent ORM for database queries
- Resource Controllers for API
- Type hints for parameters and return values

## CORS Configuration

CORS is configured in `config/cors.php`:

**Development (Current):**
```php
'allowed_origins' => ['*'],
```

**Production (Recommended):**
```php
'allowed_origins' => [
    'https://sippa-frontend.com',
],
```

## Data Source

### Primary: SQL File (Recommended)
Pre-generated SQL dump: `backend/database/sippa_complete.sql` (600KB)
- ✅ No dependency on CSV file locations
- ✅ Works in any directory structure
- ✅ Fast import (~130ms)

### Alternative: CSV Files
If you need to regenerate from source CSV files located in `../frontend/data-resource/ML_OUTPUT/`:
- `Ringkasan_Cluster.csv` - Cluster summaries
- `Hasil_Clustering_Provinsi.csv` - Province-cluster mapping
- `Profil_Cluster.csv` - Cluster commodity profiles
- `Profil_Provinsi.csv` - Province commodity productions
- `Evaluasi_KMeans.csv` - K-Means evaluation metrics
- `PCA_Variance.csv` - PCA variance explained
- `Data_Dictionary.csv` - Commodity metadata

Run: `php artisan db:seed --class=SippaSeeder`

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

## Troubleshooting

### Database connection error
```bash
# Check if database file exists (SQLite)
ls -la database/database.sqlite

# Or check MySQL credentials (MySQL)
mysql -u root -p
```

### Seeding fails
```bash
# Check CSV files exist
ls -la ../frontend/data-resource/ML_OUTPUT/

# Run with verbose output
php artisan db:seed --class=SippaSeeder -v
```

### CORS errors
Update `config/cors.php` or check browser console for actual origin.

## Production Deployment

### 1. Optimize Configuration
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 2. Set Environment
```env
APP_ENV=production
APP_DEBUG=false
```

### 3. Database Migration
```bash
php artisan migrate --force
php artisan db:seed --class=SippaSeeder --force
```

### 4. Web Server Configuration

**Nginx example:**
```nginx
server {
    listen 80;
    server_name api.sippa.com;
    root /path/to/backend/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.5-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

## Team Workflow

### Adding New Endpoints

1. Create/update controller method
2. Add route in `routes/api.php`
3. Test with `curl` or Postman
4. Update `API_DOCUMENTATION.md`

### Updating Database Schema

1. Create migration: `php artisan make:migration add_field_to_table`
2. Update model's `$fillable` and `$casts`
3. Update seeder if needed
4. Run: `php artisan migrate`

## Contributing

1. Create feature branch from `main`
2. Make changes with clear commit messages
3. Test all affected endpoints
4. Update documentation if API changes
5. Create pull request

## License

Proprietary - Capstone Project Semester 5

## Contact

For questions or issues, contact the development team.
