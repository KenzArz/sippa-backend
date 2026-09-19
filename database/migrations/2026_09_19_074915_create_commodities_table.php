<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('commodities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('slug', 100)->unique()->comment('Slug: padi, jagung, alpukat, dll.');
            $table->string('display_name', 100)->comment('Nama tampilan: Padi, Jagung, Alpukat');
            $table->string('category', 50)->comment('Kategori dari Data_Dictionary.csv (semua adalah "Produksi")');
            $table->smallInteger('reference_year')->comment('Tahun data: 2024 atau 2025');
            $table->string('unit', 20)->default('ton')->comment('Satuan: ton, ha, ku/ha');
            $table->boolean('has_luas_panen_produktivitas')->default(false)->comment('TRUE untuk padi & jagung');
            $table->timestamps();
            
            $table->index('category');
            $table->index('reference_year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commodities');
    }
};
