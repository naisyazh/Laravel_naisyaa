<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nfc_absensi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nfc_kartu_id')->nullable()->constrained('nfc_kartu')->onDelete('cascade');
            $table->string('serial_number')->comment('Disimpan langsung untuk referensi cepat');
            $table->string('mata_kuliah')->default('Pemrograman Web');
            $table->enum('status', ['hadir', 'tidak_dikenal'])->default('hadir');
            $table->timestamp('waktu_absen')->useCurrent();
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nfc_absensi');
    }
};
