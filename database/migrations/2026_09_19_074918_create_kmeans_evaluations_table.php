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
        Schema::create('kmeans_evaluations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->tinyInteger('k')->unsigned()->unique()->comment('Jumlah cluster (2-8)');
            $table->decimal('inertia', 20, 10)->comment('Dipakai pada elbow method');
            $table->decimal('silhouette_score', 10, 8)->comment('Semakin tinggi semakin baik');
            $table->decimal('davies_bouldin_index', 10, 8)->comment('Semakin rendah semakin baik');
            $table->tinyInteger('smallest_cluster')->unsigned()->comment('Jumlah anggota cluster terkecil');
            $table->tinyInteger('largest_cluster')->unsigned()->comment('Jumlah anggota cluster terbesar');
            $table->timestamps();
            
            $table->index('k');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kmeans_evaluations');
    }
};
