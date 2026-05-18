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
        Schema::create('kunjungan_tokos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('toko_id')->constrained('tokos')->onDelete('cascade');
            $table->foreignId('sales_id')->constrained('users')->onDelete('cascade');
            $table->decimal('toko_latitude', 10, 8)->comment('Latitude toko saat kunjungan');
            $table->decimal('toko_longitude', 11, 8)->comment('Longitude toko saat kunjungan');
            $table->decimal('toko_accuracy', 8, 2)->comment('Accuracy toko');
            $table->decimal('sales_latitude', 10, 8)->comment('Latitude sales saat kunjungan');
            $table->decimal('sales_longitude', 11, 8)->comment('Longitude sales saat kunjungan');
            $table->decimal('sales_accuracy', 8, 2)->comment('Accuracy sales');
            $table->decimal('jarak_meter', 8, 2)->comment('Jarak antara sales dan toko (meter)');
            $table->decimal('threshold_meter', 8, 2)->default(300)->comment('Threshold jarak maksimal');
            $table->enum('status', ['diterima', 'ditolak'])->comment('Status validasi kunjungan');
            $table->text('keterangan')->nullable();
            $table->timestamp('waktu_kunjungan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kunjungan_tokos');
    }
};
