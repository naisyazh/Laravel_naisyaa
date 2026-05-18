<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('barang', function (Blueprint $table) {
            $table->foreignId('vendor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_active')->default(true);
        });

        $defaultVendorId = DB::table('users')
            ->where('role', 'admin')
            ->orderBy('id')
            ->value('id');

        if ($defaultVendorId) {
            DB::table('barang')
                ->whereNull('vendor_id')
                ->update(['vendor_id' => $defaultVendorId]);
        }
    }

    public function down(): void
    {
        Schema::table('barang', function (Blueprint $table) {
            $table->dropConstrainedForeignId('vendor_id');
            $table->dropColumn('is_active');
        });
    }
};
