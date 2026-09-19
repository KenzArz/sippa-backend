<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class SqlImportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * This seeder imports data from the sippa_database.sql file.
     * No dependency on CSV files - works anywhere!
     */
    public function run(): void
    {
        $this->command->info('[INFO] Importing SIPPA database from SQL file...');

        $sqlFile = database_path('sippa_database.sql');

        if (!File::exists($sqlFile)) {
            $this->command->error("❌ SQL file not found: {$sqlFile}");
            $this->command->info("[TIP] Run: php artisan db:seed --class=SippaSeeder");
            $this->command->info("   to generate the SQL file from CSV data.");
            return;
        }

        $sql = File::get($sqlFile);

        // Skip CREATE TABLE statements (tables already created by migrations)
        // Only execute INSERT statements
        $this->command->info('  > Cleaning SQL (keeping only INSERT statements)...');
        
        $lines = explode("\n", $sql);
        $cleanedStatements = [];
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Skip if: empty, comment, PRAGMA, BEGIN, CREATE TABLE, or migrations/Laravel tables
            if (empty($line) || 
                str_starts_with($line, '--') ||
                str_starts_with($line, 'PRAGMA') ||
                str_starts_with($line, 'BEGIN') ||
                str_starts_with($line, 'COMMIT') ||
                str_starts_with($line, 'CREATE ') ||
                str_contains($line, 'migrations VALUES') ||
                str_contains($line, 'users VALUES') ||
                str_contains($line, 'sessions VALUES') ||
                str_contains($line, 'cache VALUES') ||
                str_contains($line, 'failed_jobs VALUES')
            ) {
                continue;
            }
            
            // Only keep INSERT statements for SIPPA tables
            if (str_starts_with($line, 'INSERT INTO commodities') ||
                str_starts_with($line, 'INSERT INTO clusters') ||
                str_starts_with($line, 'INSERT INTO provinces') ||
                str_starts_with($line, 'INSERT INTO kmeans_evaluations') ||
                str_starts_with($line, 'INSERT INTO pca_variances') ||
                str_starts_with($line, 'INSERT INTO cluster_commodity_profiles') ||
                str_starts_with($line, 'INSERT INTO province_commodity_productions') ||
                str_starts_with($line, 'INSERT INTO province_padi_monthly_productions')
            ) {
                $cleanedStatements[] = $line;
            }
        }
        
        $this->command->info('  > Executing ' . count($cleanedStatements) . ' INSERT statements...');
        
        // Execute in transaction
        DB::beginTransaction();
        try {
            foreach ($cleanedStatements as $statement) {
                DB::unprepared($statement);
            }
            DB::commit();
            $this->command->info('✅ Database imported successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('❌ Import failed: ' . $e->getMessage());
            throw $e;
        }

        // Display summary
        $this->displaySummary();
    }

    private function displaySummary(): void
    {
        $this->command->newLine();
        $this->command->info('📊 Database Summary:');
        
        $tables = [
            'clusters' => 'Clusters',
            'provinces' => 'Provinces',
            'commodities' => 'Commodities',
            'cluster_commodity_profiles' => 'Cluster Profiles',
            'province_commodity_productions' => 'Province Productions',
            'kmeans_evaluations' => 'K-Means Evaluations',
            'pca_variances' => 'PCA Variances',
            'province_padi_monthly_productions' => 'Padi Monthly Productions',
        ];

        foreach ($tables as $table => $label) {
            try {
                $count = DB::table($table)->count();
                $this->command->info("  ✓ {$label}: {$count} records");
            } catch (\Exception $e) {
                $this->command->warn("  ⚠ {$label}: Error counting");
            }
        }

        $this->command->newLine();
        $this->command->info('[READY] SIPPA database ready!');
    }
}
