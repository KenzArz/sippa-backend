# SIPPA Backend API Documentation

## Base URL
```
Development: http://localhost:8000/api
Production: https://your-domain.com/api
```

## Response Format
All endpoints return JSON with the following structure:
```json
{
  "success": true,
  "data": { ... }
}
```

Error responses:
```json
{
  "success": false,
  "message": "Error message"
}
```

---

## Clusters Endpoints

### 1. Get All Clusters
**GET** `/api/clusters`

Returns list of all clusters with basic info and provinces.

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "cluster_number": 0,
      "name": "Produksi Multi-Komoditas Relatif Rendah",
      "jumlah_provinsi": 8,
      "karakteristik": "Description...",
      "provinces": [...]
    }
  ]
}
```

### 2. Get Cluster Summary
**GET** `/api/clusters/summary`

Returns cluster summary without provinces.

### 3. Get Cluster Detail
**GET** `/api/clusters/{id}`

Returns detailed cluster info with commodity profiles.

**Response:**
```json
{
  "success": true,
  "data": {
    "id": "uuid",
    "cluster_number": 1,
    "name": "...",
    "provinces": [...],
    "commodityProfiles": [
      {
        "commodity": {
          "id": "uuid",
          "slug": "padi",
          "display_name": "Padi",
          "category": "pangan"
        },
        "median_produksi": 1382697.0,
        "mean_z_score": 0.5234
      }
    ]
  }
}
```

### 4. Get Top Commodities for Cluster
**GET** `/api/clusters/{id}/top-commodities?limit=10`

Returns top N commodities for cluster sorted by z-score.

**Query Parameters:**
- `limit` (optional, default: 10) - Number of commodities to return

---

## Provinces Endpoints

### 1. Get All Provinces
**GET** `/api/provinces`

Returns list of all provinces with cluster info.

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "name": "Jawa Barat",
      "cluster_id": "uuid",
      "geo_alias": null,
      "total_production": null,
      "cluster": {
        "id": "uuid",
        "cluster_number": 2,
        "name": "Sentra Produksi..."
      }
    }
  ]
}
```

### 2. Get Map Data
**GET** `/api/provinces/map-data`

Returns optimized data for map visualization.

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "name": "Aceh",
      "geo_alias": null,
      "cluster_number": 1,
      "cluster_name": "Produksi Tinggi...",
      "total_production": null
    }
  ]
}
```

### 3. Get Province Detail
**GET** `/api/provinces/{id}`

Returns detailed province info with all commodity productions.

**Response:**
```json
{
  "success": true,
  "data": {
    "id": "uuid",
    "name": "Jawa Barat",
    "cluster": {...},
    "commodityProductions": [
      {
        "commodity": {
          "slug": "padi",
          "display_name": "Padi",
          "category": "pangan"
        },
        "produksi": 9876543.0,
        "luas_panen": null,
        "produktivitas": null,
        "median_cluster": 5000000.0,
        "selisih_dengan_median_cluster": 4876543.0
      }
    ]
  }
}
```

### 4. Get Top Commodities for Province
**GET** `/api/provinces/{id}/top-commodities?limit=10`

Returns top N commodities produced in province.

**Query Parameters:**
- `limit` (optional, default: 10)

### 5. Get Commodity Comparison
**GET** `/api/provinces/{id}/commodity-comparison`

Returns commodity production comparison with cluster median.

---

## Commodities Endpoints

### 1. Get All Commodities
**GET** `/api/commodities?category={category}&year={year}`

Returns list of all commodities with optional filters.

**Query Parameters:**
- `category` (optional) - Filter by category: pangan, buah-tahunan, sayuran-semusim, jamur
- `year` (optional) - Filter by reference year: 2024, 2025

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "slug": "padi",
      "display_name": "Padi",
      "category": "pangan",
      "reference_year": 2025,
      "unit": "ton",
      "has_luas_panen_produktivitas": true
    }
  ]
}
```

### 2. Get Commodity Categories
**GET** `/api/commodities/categories`

Returns list of categories with counts.

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "category": "pangan",
      "count": 2
    },
    {
      "category": "buah-tahunan",
      "count": 14
    }
  ]
}
```

### 3. Get Commodity Detail
**GET** `/api/commodities/{id}`

Returns detailed commodity info with province productions.

### 4. Get Top Provinces for Commodity
**GET** `/api/commodities/{id}/top-provinces?limit=10`

Returns top N provinces producing this commodity.

**Query Parameters:**
- `limit` (optional, default: 10)

---

## Model Evaluation Endpoints

### 1. Get Complete Model Evaluation
**GET** `/api/model-evaluation`

Returns complete K-Means and PCA evaluation metrics.

**Response:**
```json
{
  "success": true,
  "data": {
    "kmeans": {
      "evaluations": [...],
      "optimal_k": 4,
      "optimal_silhouette_score": "0.27307103"
    },
    "pca": {
      "variances": [...],
      "components_for_90_percent": 15,
      "total_components": 38
    }
  }
}
```

### 2. Get K-Means Evaluation
**GET** `/api/model-evaluation/kmeans`

Returns K-Means evaluation for K=2 to K=8.

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "k": 4,
      "inertia": "622.8143680775",
      "silhouette_score": "0.27307103",
      "davies_bouldin_index": "1.08358781",
      "smallest_cluster": 3,
      "largest_cluster": 14
    }
  ]
}
```

### 3. Get PCA Variance
**GET** `/api/model-evaluation/pca`

Returns PCA variance explained data.

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "component": 1,
      "explained_variance": "0.3456789012",
      "cumulative_variance": "0.3456789012"
    }
  ]
}
```

---

## CORS Configuration

CORS is configured to allow all origins (`*`) in development. For production, update `config/cors.php`:

```php
'allowed_origins' => [
    'https://sippa-frontend.com',
    'https://www.sippa-frontend.com',
],
```

---

## Running the API Server

### Development
```bash
cd backend
php artisan serve
# API available at http://localhost:8000/api
```

### Production
Configure your web server (Nginx/Apache) to serve the `public/` directory.

---

## Database Seeding

To reset and re-seed the database:

```bash
php artisan migrate:fresh --seed
```

This will:
1. Drop all tables
2. Run migrations
3. Seed data from CSV files in `../frontend/data-resource/ML_OUTPUT/`

---

## Testing Endpoints

Using curl:
```bash
# Test clusters endpoint
curl http://localhost:8000/api/clusters

# Test provinces map data
curl http://localhost:8000/api/provinces/map-data

# Test model evaluation
curl http://localhost:8000/api/model-evaluation

# Test with query parameters
curl "http://localhost:8000/api/commodities?category=pangan"
```

---

## Database Schema

- **clusters** - 4 records (K-Means clusters 0-3)
- **provinces** - 38 records (Indonesian provinces)
- **commodities** - 55 records (agricultural commodities)
- **cluster_commodity_profiles** - 220 records
- **province_commodity_productions** - 2090 records
- **kmeans_evaluations** - 7 records (K=2 to K=8)
- **pca_variances** - 38 records (PC1 to PC38)

---

## Notes

- All IDs are UUIDs (not auto-increment integers)
- All numeric values are stored as DECIMAL for precision
- Relationships are eagerly loaded using `with()` for performance
- Foreign keys have CASCADE on delete for child tables
- Province→Cluster uses RESTRICT to prevent accidental deletions
