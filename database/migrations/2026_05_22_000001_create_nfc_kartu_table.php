<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nfc_kartu', function (Blueprint $table) {
            $table->id();
            $table->string('serial_number')->unique()->comment('Serial number unik dari tag NFC');
            $table->string('nama_mahasiswa');
            $table->string('nim')->unique();
            $table->string('program_studi')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nfc_kartu');
    }
};
