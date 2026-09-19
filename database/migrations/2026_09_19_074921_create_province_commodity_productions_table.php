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
        Schema::create('province_commodity_productions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('province_id')->comment('Relasi ke provinces.id');
            $table->uuid('commodity_id')->comment('Relasi ke commodities.id');
            $table->decimal('produksi', 15, 3)->comment('Produksi aktual (ton)');
            $table->decimal('luas_panen', 15, 3)->nullable()->comment('Luas panen (ha) - hanya untuk padi & jagung');
            $table->decimal('produktivitas', 10, 3)->nullable()->comment('Produktivitas (ku/ha) - hanya untuk padi & jagung');
            $table->decimal('median_cluster', 15, 3)->comment('Median produksi komoditas dalam cluster yang sama');
            $table->decimal('selisih_dengan_median_cluster', 15, 3)->comment('Produksi dikurangi median cluster');
            $table->timestamps();
            
            $table->foreign('province_id')->references('id')->on('provinces')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('commodity_id')->references('id')->on('commodities')->onDelete('cascade')->onUpdate('cascade');
            $table->unique(['province_id', 'commodity_id'], 'uk_province_commodity');
            $table->index('province_id');
            $table->index('commodity_id');
            $table->index('produksi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('province_commodity_productions');
    }
};
