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
        Schema::create('pca_variances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->tinyInteger('component')->unsigned()->unique()->comment('Nomor principal component (1-38)');
            $table->decimal('explained_variance', 12, 10)->comment('Proporsi variasi yang dijelaskan');
            $table->decimal('cumulative_variance', 12, 10)->comment('Total variasi kumulatif');
            $table->timestamps();
            
            $table->index('component');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pca_variances');
    }
};
