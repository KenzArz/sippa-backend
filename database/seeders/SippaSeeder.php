<?php

namespace Database\Seeders;

use App\Models\Cluster;
use App\Models\ClusterCommodityProfile;
use App\Models\Commodity;
use App\Models\KmeansEvaluation;
use App\Models\PcaVariance;
use App\Models\Province;
use App\Models\ProvinceCommodityProduction;
use App\Models\ProvincePadiMonthlyProduction;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SippaSeeder extends Seeder
{
    private string $csvPath;

    public function __construct()
    {
        // Path ke data-resource (moved to root, not in frontend anymore)
        $this->csvPath = base_path('../data-resource/ML_OUTPUT/');
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $this->command->info('[INFO] Seeding SIPPA Database...');

            // 1. Seed Clusters
            $this->seedClusters();

            // 2. Seed Provinces
            $this->seedProvinces();

            // 3. Seed Commodities
            $this->seedCommodities();

            // 4. Seed Cluster Commodity Profiles
            $this->seedClusterCommodityProfiles();

            // 5. Seed Province Commodity Productions
            $this->seedProvinceCommodityProductions();

            // 6. Seed KMeans Evaluations
            $this->seedKmeansEvaluations();

            // 7. Seed PCA Variances
            $this->seedPcaVariances();
            
            // 8. Seed Province Padi Monthly Productions
            $this->seedProvincePadiMonthlyProductions();
            
            // 9. Calculate province total_production & geo_alias
            $this->calculateProvinceAggregates();

            $this->command->info('[DONE] SIPPA Database seeded successfully!');
        });
    }

    private function seedClusters(): void
    {
        $this->command->info('[INFO] Seeding Clusters...');
        
        $file = fopen($this->csvPath . 'Ringkasan_Cluster.csv', 'r');
        $header = fgetcsv($file);
        
        // Validate all required columns exist - NO GUESSING!
        $clusterNumIdx = array_search('cluster', $header);
        $nameIdx = array_search('cluster_name', $header);
        $jumlahIdx = array_search('jumlah_provinsi', $header);
        $karakteristikIdx = array_search('karakteristik', $header); // FIXED: 'karakteristik', NOT 'karakteristik_ringkas'
        
        if ($clusterNumIdx === false || $nameIdx === false || $jumlahIdx === false || $karakteristikIdx === false) {
            fclose($file);
            throw new \Exception('Required columns not found in Ringkasan_Cluster.csv! Expected: cluster, cluster_name, jumlah_provinsi, karakteristik');
        }
        
        $clusters = [];
        while (($row = fgetcsv($file)) !== false) {
            $clusters[] = [
                'id' => Str::uuid(),
                'cluster_number' => (int)$row[$clusterNumIdx],
                'name' => $row[$nameIdx],
                'jumlah_provinsi' => (int)$row[$jumlahIdx],
                'karakteristik' => $row[$karakteristikIdx],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        fclose($file);

        Cluster::insert($clusters);
        $this->command->info('  OK ' . count($clusters) . ' clusters seeded');
    }

    private function seedProvinces(): void
    {
        $this->command->info('[INFO]  Seeding Provinces...');
        
        $file = fopen($this->csvPath . 'Hasil_Clustering_Provinsi.csv', 'r');
        $header = fgetcsv($file);
        
        // Validate required columns - NO GUESSING!
        $provIdx = array_search('provinsi', $header);
        $clusterIdx = array_search('cluster', $header);
        
        if ($provIdx === false || $clusterIdx === false) {
            fclose($file);
            throw new \Exception('Required columns not found in Hasil_Clustering_Provinsi.csv! Expected: provinsi, cluster');
        }
        
        $provinces = [];
        while (($row = fgetcsv($file)) !== false) {
            $clusterNumber = (int)$row[$clusterIdx];
            $cluster = Cluster::where('cluster_number', $clusterNumber)->first();
            
            if (!$cluster) {
                $this->command->warn("  WARNING Cluster {$clusterNumber} not found for province {$row[$provIdx]}");
                continue;
            }
            
            $provinces[] = [
                'id' => Str::uuid(),
                'name' => $row[$provIdx],
                'cluster_id' => $cluster->id,
                'geo_alias' => null,
                'total_production' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        fclose($file);

        Province::insert($provinces);
        $this->command->info('  OK ' . count($provinces) . ' provinces seeded');
    }

    private function seedCommodities(): void
    {
        $this->command->info('[INFO] Seeding Commodities...');
        
        // Auto-detect which commodities have luas_panen & produktivitas from MASTER
        $this->command->info('  [SCAN] Auto-detecting commodities with luas_panen...');
        $commoditiesWithLuasPanen = $this->detectCommoditiesWithLuasPanen();
        $this->command->info('  OK Found ' . count($commoditiesWithLuasPanen) . ' commodities with luas_panen: ' . implode(', ', $commoditiesWithLuasPanen));
        
        // Read Data_Dictionary.csv
        $file = fopen($this->csvPath . 'Data_Dictionary.csv', 'r');
        $header = fgetcsv($file);
        
        // Validate columns
        $fieldNameIdx = array_search('field_name', $header);
        $displayNameIdx = array_search('display_name', $header);
        $categoryIdx = array_search('category', $header);
        $yearIdx = array_search('year', $header);
        $unitIdx = array_search('unit', $header);
        
        if ($fieldNameIdx === false || $displayNameIdx === false || $categoryIdx === false || $yearIdx === false || $unitIdx === false) {
            fclose($file);
            throw new \Exception('Required columns not found in Data_Dictionary.csv! Expected: field_name, display_name, category, year, unit');
        }
        
        $commodities = [];
        $seen = [];
        
        while (($row = fgetcsv($file)) !== false) {
            $fieldName = $row[$fieldNameIdx];
            $displayName = $row[$displayNameIdx];
            $category = $row[$categoryIdx];
            $yearRaw = $row[$yearIdx];
            $unit = $row[$unitIdx];
            
            // Skip non-commodity rows (provinsi, cluster, dll)
            if (!str_starts_with($fieldName, 'produksi_')) {
                continue;
            }
            
            // Extract slug from field_name: "produksi_padi_2025" -> "padi"
            if (!preg_match('/^produksi_(.+)_(\d{4})$/', $fieldName, $matches)) {
                $this->command->warn("  WARNING Field name format tidak sesuai: {$fieldName}");
                continue;
            }
            
            $slug = $matches[1];
            $year = (int)$matches[2];
            
            // Skip duplicates
            if (isset($seen[$slug])) {
                continue;
            }
            
            // Validate required fields - NO FALLBACK!
            if (empty($category)) {
                throw new \Exception("Category kosong untuk komoditas: {$displayName} (field: {$fieldName})");
            }
            if (empty($unit)) {
                throw new \Exception("Unit kosong untuk komoditas: {$displayName} (field: {$fieldName})");
            }
            
            // Auto-detect from MASTER dataset
            $hasLuasPanen = in_array($slug, $commoditiesWithLuasPanen);
            
            $commodities[] = [
                'id' => Str::uuid(),
                'slug' => $slug,
                'display_name' => $displayName,
                'category' => $category,
                'reference_year' => $year,
                'unit' => $unit,
                'has_luas_panen_produktivitas' => $hasLuasPanen,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            $seen[$slug] = true;
        }
        fclose($file);

        Commodity::insert($commodities);
        $this->command->info('  OK ' . count($commodities) . ' commodities seeded');
    }
    
    /**
     * Auto-detect which commodities have luas_panen & produktivitas in MASTER dataset
     * Returns: ['padi', 'jagung', ...]
     */
    private function detectCommoditiesWithLuasPanen(): array
    {
        $masterPath = base_path('../data-resource/MASTER/Master_Dataset_Pertanian_9_Dataset_Final.csv');
        
        if (!file_exists($masterPath)) {
            $this->command->warn('  WARNING MASTER dataset not found, cannot auto-detect luas_panen commodities');
            return [];
        }
        
        $file = fopen($masterPath, 'r');
        $header = fgetcsv($file);
        fclose($file);
        
        // Remove BOM if present
        if (!empty($header) && isset($header[0])) {
            $header[0] = preg_replace('/^\x{FEFF}/u', '', $header[0]);
        }
        
        $commodities = [];
        foreach ($header as $colName) {
            // Find columns matching: luas_panen_{slug}_{year}
            if (preg_match('/^luas_panen_(.+)_(\d{4})$/', $colName, $matches)) {
                $slug = $matches[1];
                $commodities[$slug] = true;
            }
        }
        
        return array_keys($commodities);
    }

    private function seedClusterCommodityProfiles(): void
    {
        $this->command->info('[INFO] Seeding Cluster Commodity Profiles...');
        
        $file = fopen($this->csvPath . 'Profil_Cluster.csv', 'r');
        $header = fgetcsv($file); // cluster,cluster_name,komoditas,tahun,median_produksi,mean_z_score
        
        // Validate columns exist - NO GUESSING!
        $clusterIdx = array_search('cluster', $header);
        $komoditasIdx = array_search('komoditas', $header);
        $medianIdx = array_search('median_produksi', $header);
        $zScoreIdx = array_search('mean_z_score', $header);
        
        if ($clusterIdx === false || $komoditasIdx === false || $medianIdx === false || $zScoreIdx === false) {
            fclose($file);
            throw new \Exception('Required columns not found in Profil_Cluster.csv! Expected: cluster, komoditas, median_produksi, mean_z_score');
        }
        
        $profiles = [];
        while (($row = fgetcsv($file)) !== false) {
            $clusterNumber = (int)$row[$clusterIdx];
            $komoditas = $row[$komoditasIdx];
            $medianProduksi = (float)$row[$medianIdx];
            $meanZScore = (float)$row[$zScoreIdx];
            
            $cluster = Cluster::where('cluster_number', $clusterNumber)->first();
            if (!$cluster) {
                $this->command->warn("  WARNING Cluster {$clusterNumber} not found");
                continue;
            }
            
            // Find commodity by display_name
            $commodity = Commodity::where('display_name', $komoditas)->first();
            if (!$commodity) {
                $this->command->warn("  WARNING Commodity '{$komoditas}' not found");
                continue;
            }
            
            $profiles[] = [
                'id' => Str::uuid(),
                'cluster_id' => $cluster->id,
                'commodity_id' => $commodity->id,
                'median_produksi' => $medianProduksi,
                'mean_z_score' => $meanZScore,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        fclose($file);

        ClusterCommodityProfile::insert($profiles);
        $this->command->info('  OK ' . count($profiles) . ' cluster commodity profiles seeded');
    }

    private function seedProvinceCommodityProductions(): void
    {
        $this->command->info('[INFO] Seeding Province Commodity Productions...');
        
        // Load luas_panen & produktivitas data from MASTER dataset
        $this->command->info('  [LOAD] Loading luas_panen & produktivitas from MASTER...');
        $masterData = $this->loadMasterData();
        
        $file = fopen($this->csvPath . 'Profil_Provinsi.csv', 'r');
        $header = fgetcsv($file); // provinsi,cluster,cluster_name,komoditas,tahun,produksi,median_cluster,selisih_dengan_median_cluster
        
        // Validate columns - NO GUESSING!
        $provIdx = array_search('provinsi', $header);
        $komoditasIdx = array_search('komoditas', $header);
        $produksiIdx = array_search('produksi', $header);
        $medianIdx = array_search('median_cluster', $header);
        $selisihIdx = array_search('selisih_dengan_median_cluster', $header);
        
        if ($provIdx === false || $komoditasIdx === false || $produksiIdx === false || $medianIdx === false || $selisihIdx === false) {
            fclose($file);
            throw new \Exception('Required columns not found in Profil_Provinsi.csv! Expected: provinsi, komoditas, produksi, median_cluster, selisih_dengan_median_cluster');
        }
        
        $productions = [];
        $count = 0;
        
        while (($row = fgetcsv($file)) !== false) {
            $provinceName = $row[$provIdx];
            $komoditas = $row[$komoditasIdx];
            $produksi = (float)$row[$produksiIdx];
            $medianCluster = (float)$row[$medianIdx];
            $selisih = (float)$row[$selisihIdx];
            
            $province = Province::where('name', $provinceName)->first();
            if (!$province) {
                $this->command->warn("  WARNING Province '{$provinceName}' not found");
                continue;
            }
            
            // Find commodity by display_name
            $commodity = Commodity::where('display_name', $komoditas)->first();
            if (!$commodity) {
                $this->command->warn("  WARNING Commodity '{$komoditas}' not found");
                continue;
            }
            
            // Get luas_panen & produktivitas from MASTER if available
            $luasPanen = null;
            $produktivitas = null;
            
            if (isset($masterData[$provinceName])) {
                $slug = $commodity->slug;
                $year = $commodity->reference_year;
                
                // Check for luas_panen_{slug}_{year}
                $luasPanenKey = "luas_panen_{$slug}_{$year}";
                if (isset($masterData[$provinceName][$luasPanenKey])) {
                    $luasPanen = $masterData[$provinceName][$luasPanenKey];
                }
                
                // Check for produktivitas_{slug}_{year}
                $produktivitasKey = "produktivitas_{$slug}_{$year}";
                if (isset($masterData[$provinceName][$produktivitasKey])) {
                    $produktivitas = $masterData[$provinceName][$produktivitasKey];
                }
            }
            
            $productions[] = [
                'id' => Str::uuid(),
                'province_id' => $province->id,
                'commodity_id' => $commodity->id,
                'produksi' => $produksi,
                'luas_panen' => $luasPanen, // From MASTER dataset (can be NULL)
                'produktivitas' => $produktivitas, // From MASTER dataset (can be NULL)
                'median_cluster' => $medianCluster,
                'selisih_dengan_median_cluster' => $selisih,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            // Insert in batches of 500
            if (count($productions) >= 500) {
                ProvinceCommodityProduction::insert($productions);
                $count += count($productions);
                $productions = [];
            }
        }
        fclose($file);

        // Insert remaining
        if (count($productions) > 0) {
            ProvinceCommodityProduction::insert($productions);
            $count += count($productions);
        }

        $this->command->info('  OK ' . $count . ' province commodity productions seeded');
    }
    
    /**
     * Load luas_panen & produktivitas data from MASTER dataset
     * Returns: ['ProvinsiName' => ['luas_panen_padi_2025' => value, ...]]
     */
    private function loadMasterData(): array
    {
        $masterPath = base_path('../data-resource/MASTER/Master_Dataset_Pertanian_9_Dataset_Final.csv');
        
        if (!file_exists($masterPath)) {
            $this->command->warn('  WARNING MASTER dataset not found, luas_panen & produktivitas will be NULL');
            return [];
        }
        
        $file = fopen($masterPath, 'r');
        $header = fgetcsv($file);
        
        // Remove BOM if present (UTF-8 BOM: \xEF\xBB\xBF)
        if (!empty($header) && isset($header[0])) {
            $header[0] = preg_replace('/^\x{FEFF}/u', '', $header[0]);
        }
        
        // Validate provinsi column exists - NO GUESSING!
        $provIdx = array_search('provinsi', $header);
        if ($provIdx === false) {
            fclose($file);
            throw new \Exception('Column "provinsi" not found in MASTER dataset!');
        }
        
        $data = [];
        while (($row = fgetcsv($file)) !== false) {
            $provinceName = $row[$provIdx];
            $data[$provinceName] = [];
            
            // Map all columns to province data
            foreach ($header as $idx => $colName) {
                if ($idx === $provIdx) continue; // Skip provinsi column itself
                
                // Only store luas_panen and produktivitas columns
                if (str_starts_with($colName, 'luas_panen_') || str_starts_with($colName, 'produktivitas_')) {
                    $value = $row[$idx];
                    // Convert to float, NULL if empty (NOT if 0!)
                    // Per ML docs: "0 memiliki arti data tersedia tetapi nilainya nol"
                    $data[$provinceName][$colName] = ($value !== '' && $value !== null) ? (float)$value : null;
                }
            }
        }
        fclose($file);
        
        $this->command->info('  OK MASTER data loaded for ' . count($data) . ' provinces');
        return $data;
    }

    private function seedKmeansEvaluations(): void
    {
        $this->command->info('[INFO] Seeding KMeans Evaluations...');
        
        $file = fopen($this->csvPath . 'Evaluasi_KMeans.csv', 'r');
        $header = fgetcsv($file);
        
        // Validate all columns exist - NO GUESSING!
        $kIdx = array_search('k', $header);
        $inertiaIdx = array_search('inertia', $header);
        $silhouetteIdx = array_search('silhouette_score', $header);
        $dbIdx = array_search('davies_bouldin_index', $header);
        $smallestIdx = array_search('smallest_cluster', $header);
        $largestIdx = array_search('largest_cluster', $header);
        
        if ($kIdx === false || $inertiaIdx === false || $silhouetteIdx === false || 
            $dbIdx === false || $smallestIdx === false || $largestIdx === false) {
            fclose($file);
            throw new \Exception('Required columns not found in Evaluasi_KMeans.csv! Expected: k, inertia, silhouette_score, davies_bouldin_index, smallest_cluster, largest_cluster');
        }
        
        $evaluations = [];
        while (($row = fgetcsv($file)) !== false) {
            $evaluations[] = [
                'id' => Str::uuid(),
                'k' => (int)$row[$kIdx],
                'inertia' => (float)$row[$inertiaIdx],
                'silhouette_score' => (float)$row[$silhouetteIdx],
                'davies_bouldin_index' => (float)$row[$dbIdx],
                'smallest_cluster' => (int)$row[$smallestIdx],
                'largest_cluster' => (int)$row[$largestIdx],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        fclose($file);

        KmeansEvaluation::insert($evaluations);
        $this->command->info('  OK ' . count($evaluations) . ' KMeans evaluations seeded');
    }

    private function seedPcaVariances(): void
    {
        $this->command->info('[INFO] Seeding PCA Variances...');
        
        $file = fopen($this->csvPath . 'PCA_Variance.csv', 'r');
        $header = fgetcsv($file);
        
        // Validate all columns exist - NO GUESSING!
        $compIdx = array_search('component', $header);
        $explainedIdx = array_search('explained_variance', $header);
        $cumulativeIdx = array_search('cumulative_variance', $header);
        
        if ($compIdx === false || $explainedIdx === false || $cumulativeIdx === false) {
            fclose($file);
            throw new \Exception('Required columns not found in PCA_Variance.csv! Expected: component, explained_variance, cumulative_variance');
        }
        
        $variances = [];
        while (($row = fgetcsv($file)) !== false) {
            $variances[] = [
                'id' => Str::uuid(),
                'component' => (int)$row[$compIdx],
                'explained_variance' => (float)$row[$explainedIdx],
                'cumulative_variance' => (float)$row[$cumulativeIdx],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        fclose($file);

        PcaVariance::insert($variances);
        $this->command->info('  OK ' . count($variances) . ' PCA variances seeded');
    }
    
    private function seedProvincePadiMonthlyProductions(): void
    {
        $this->command->info('[INFO] Seeding Province Padi Monthly Productions...');
        
        $masterPath = base_path('../data-resource/MASTER/Master_Dataset_Pertanian_9_Dataset_Final.csv');
        
        if (!file_exists($masterPath)) {
            $this->command->warn('  WARNING MASTER dataset not found, monthly padi data skipped');
            return;
        }
        
        $file = fopen($masterPath, 'r');
        $header = fgetcsv($file);
        
        // Remove BOM if present
        if (!empty($header) && isset($header[0])) {
            $header[0] = preg_replace('/^\x{FEFF}/u', '', $header[0]);
        }
        
        // Validate provinsi column
        $provIdx = array_search('provinsi', $header);
        if ($provIdx === false) {
            fclose($file);
            throw new \Exception('Column "provinsi" not found in MASTER dataset!');
        }
        
        // Find monthly columns for 2025
        $monthNames = [
            'januari' => 1, 'februari' => 2, 'maret' => 3, 'april' => 4,
            'mei' => 5, 'juni' => 6, 'juli' => 7, 'agustus' => 8,
            'september' => 9, 'oktober' => 10, 'november' => 11, 'desember' => 12
        ];
        
        $luasPanenColumns = [];
        $produksiColumns = [];
        
        foreach ($monthNames as $monthName => $monthNum) {
            $luasPanenCol = "luas_panen_padi_{$monthName}_2025";
            $produksiCol = "produksi_padi_{$monthName}_2025";
            
            $luasPanenIdx = array_search($luasPanenCol, $header);
            $produksiIdx = array_search($produksiCol, $header);
            
            if ($luasPanenIdx !== false && $produksiIdx !== false) {
                $luasPanenColumns[$monthNum] = $luasPanenIdx;
                $produksiColumns[$monthNum] = $produksiIdx;
            } else {
                $this->command->warn("  WARNING Monthly columns not found for {$monthName} 2025");
            }
        }
        
        if (empty($luasPanenColumns)) {
            fclose($file);
            $this->command->warn('  WARNING No monthly padi data found in MASTER');
            return;
        }
        
        $productions = [];
        $count = 0;
        
        while (($row = fgetcsv($file)) !== false) {
            $provinceName = $row[$provIdx];
            $province = Province::where('name', $provinceName)->first();
            
            if (!$province) {
                $this->command->warn("  WARNING Province '{$provinceName}' not found");
                continue;
            }
            
            // Insert data for each month
            foreach ($monthNames as $monthName => $monthNum) {
                if (!isset($luasPanenColumns[$monthNum]) || !isset($produksiColumns[$monthNum])) {
                    continue;
                }
                
                $luasPanen = $row[$luasPanenColumns[$monthNum]];
                $produksi = $row[$produksiColumns[$monthNum]];
                
                // Skip if both are empty
                if (($luasPanen === '' || $luasPanen === null) && ($produksi === '' || $produksi === null)) {
                    continue;
                }
                
                $productions[] = [
                    'id' => Str::uuid(),
                    'province_id' => $province->id,
                    'month' => $monthNum,
                    'year' => 2025,
                    'luas_panen' => ($luasPanen !== '' && $luasPanen !== null) ? (float)$luasPanen : 0,
                    'produksi' => ($produksi !== '' && $produksi !== null) ? (float)$produksi : 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                
                // Insert in batches
                if (count($productions) >= 500) {
                    ProvincePadiMonthlyProduction::insert($productions);
                    $count += count($productions);
                    $productions = [];
                }
            }
        }
        fclose($file);
        
        // Insert remaining
        if (count($productions) > 0) {
            ProvincePadiMonthlyProduction::insert($productions);
            $count += count($productions);
        }
        
        $this->command->info('  OK ' . $count . ' monthly padi productions seeded');
    }
    
    private function calculateProvinceAggregates(): void
    {
        $this->command->info('[INFO] Calculating province aggregates...');
        
        $provinces = Province::all();
        
        foreach ($provinces as $province) {
            // Calculate total production (sum of all commodity productions for this province)
            $totalProduction = ProvinceCommodityProduction::where('province_id', $province->id)
                ->sum('produksi');
            
            // Set geo_alias based on province name normalization for map matching
            // Common aliases for Indonesia map data (e.g., "DI Yogyakarta" -> "Yogyakarta")
            $geoAlias = $this->normalizeProvinceName($province->name);
            
            $province->update([
                'total_production' => $totalProduction > 0 ? $totalProduction : null,
                'geo_alias' => $geoAlias !== $province->name ? $geoAlias : null,
            ]);
        }
        
        $this->command->info('  OK ' . $provinces->count() . ' provinces updated with total_production & geo_alias');
    }
    
    /**
     * Normalize province name for geo matching
     * Based on official aliases from frontend README.md
     */
    private function normalizeProvinceName(string $name): string
    {
        // Official GeoJSON aliases used in frontend (from README.md)
        $aliases = [
            'DKI Jakarta' => 'Jakarta Raya',
            'DI Yogyakarta' => 'Yogyakarta',
            'Kepulauan Bangka Belitung' => 'Bangka-Belitung',
            'Papua Barat' => 'Irian Jaya Barat',
        ];
        
        return $aliases[$name] ?? $name;
    }
}
