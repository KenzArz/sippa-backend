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
        Schema::create('cluster_commodity_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('cluster_id')->comment('Relasi ke clusters.id');
            $table->uuid('commodity_id')->comment('Relasi ke commodities.id');
            $table->decimal('median_produksi', 15, 3)->comment('Median produksi aktual se-cluster (ton)');
            $table->decimal('mean_z_score', 15, 10)->comment('Lebih dari 0 berarti di atas rata-rata');
            $table->timestamps();
            
            $table->foreign('cluster_id')->references('id')->on('clusters')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('commodity_id')->references('id')->on('commodities')->onDelete('cascade')->onUpdate('cascade');
            $table->unique(['cluster_id', 'commodity_id'], 'uk_cluster_commodity_profile');
            $table->index('cluster_id');
            $table->index('commodity_id');
            $table->index('mean_z_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cluster_commodity_profiles');
    }
};
