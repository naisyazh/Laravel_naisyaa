<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer', function (Blueprint $table) {
            $table->id();
            $table->string('kode_customer', 20)->unique();
            $table->string('nama', 100);
            $table->string('email', 100)->nullable();
            $table->string('telepon', 30)->nullable();
            $table->string('alamat', 255)->nullable();
            $table->string('capture_mode', 20);
            $table->binary('photo_blob')->nullable();
            $table->string('photo_blob_mime', 50)->nullable();
            $table->string('photo_path')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer');
    }
};
