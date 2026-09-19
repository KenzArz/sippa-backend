<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Use SqlImportSeeder untuk import data dari file .sql
        // Tidak butuh dependency ke CSV files
        $this->call([
            SqlImportSeeder::class,
        ]);
        
        // Alternative: gunakan SippaSeeder jika ingin import dari CSV
        // (requires ../frontend/data-resource/ML_OUTPUT/ directory)
        // $this->call([
        //     SippaSeeder::class,
        // ]);
    }
}
