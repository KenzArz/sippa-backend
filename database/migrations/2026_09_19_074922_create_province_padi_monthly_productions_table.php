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
        Schema::create('province_padi_monthly_productions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('province_id')->comment('Relasi ke provinces.id');
            $table->tinyInteger('month')->unsigned()->comment('Bulan (1-12)');
            $table->smallInteger('year')->unsigned()->comment('Tahun data');
            $table->decimal('luas_panen', 15, 3)->comment('Luas panen (ha)');
            $table->decimal('produksi', 15, 3)->comment('Produksi (ton)');
            $table->timestamps();
            
            $table->foreign('province_id')->references('id')->on('provinces')->onDelete('cascade')->onUpdate('cascade');
            $table->unique(['province_id', 'month', 'year'], 'uk_province_month_year');
            $table->index('province_id');
            $table->index('year');
            $table->index('month');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('province_padi_monthly_productions');
    }
};
