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
        Schema::create('clusters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->tinyInteger('cluster_number')->unsigned()->unique()->comment('Nomor cluster: 0, 1, 2, 3');
            $table->string('name', 150)->comment('Nama cluster dari cluster_name');
            $table->smallInteger('jumlah_provinsi')->unsigned()->comment('Jumlah anggota cluster');
            $table->text('karakteristik')->comment('Ringkasan karakteristik produksi');
            $table->timestamps();
            
            $table->index('cluster_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clusters');
    }
};
