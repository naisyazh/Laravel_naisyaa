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
        Schema::create('tokos', function (Blueprint $table) {
            $table->id();
            $table->string('barcode')->unique()->comment('Barcode toko untuk scanning');
            $table->string('nama_toko');
            $table->text('alamat')->nullable();
            $table->decimal('latitude', 10, 8)->comment('Latitude koordinat toko');
            $table->decimal('longitude', 11, 8)->comment('Longitude koordinat toko');
            $table->decimal('accuracy', 8, 2)->comment('Accuracy GPS dalam meter');
            $table->foreignId('vendor_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tokos');
    }
};
