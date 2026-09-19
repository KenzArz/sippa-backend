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
        Schema::create('provinces', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 100)->unique()->comment('Nama provinsi');
            $table->uuid('cluster_id')->comment('Relasi ke clusters.id');
            $table->string('geo_alias', 100)->nullable()->comment('Alias nama untuk GeoJSON mapping');
            $table->decimal('total_production', 15, 2)->nullable()->comment('Total produksi semua komoditas (ton)');
            $table->timestamps();
            
            $table->foreign('cluster_id')->references('id')->on('clusters')->onDelete('restrict')->onUpdate('cascade');
            $table->index('cluster_id');
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provinces');
    }
};
