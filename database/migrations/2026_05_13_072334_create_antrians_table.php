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
        
    }Schema::create('antrians', function (Blueprint $table) {
            $table->id();
            $table->integer('nomor_antrian')->unique()->comment('Nomor antrian unik');
            $table->string('nama')->comment('Nama tamu/pasien');
            $table->enum('status', ['menunggu', 'dipanggil', 'selesai', 'terlewat'])->default('menunggu');
            $table->integer('ruangan')->nullable()->comment('Nomor ruangan tujuan');
            $table->timestamp('waktu_daftar')->useCurrent();
            $table->timestamp('waktu_dipanggil')->nullable();
            $table->timestamps();
        });

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('antrians');
    }
};
