<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('barang', function (Blueprint $table) {
        $table->string('id_barang', 8)->primary();
        $table->string('nama', 50);
        $table->integer('harga');
        $table->timestamp('timestamp')->useCurrent();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barang');
    }
};
