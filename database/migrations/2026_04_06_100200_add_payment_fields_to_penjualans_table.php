<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penjualans', function (Blueprint $table) {
            $table->foreignId('vendor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('customer_name', 100)->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_phone', 30)->nullable();
            $table->string('payment_status', 30)->default('pending');
            $table->string('payment_type', 50)->nullable();
            $table->string('midtrans_transaction_status', 50)->nullable();
            $table->string('midtrans_transaction_id')->nullable();
            $table->string('fraud_status', 30)->nullable();
            $table->text('snap_token')->nullable();
            $table->text('snap_redirect_url')->nullable();
            $table->text('status_message')->nullable();
            $table->json('payment_payload')->nullable();
            $table->timestamp('paid_at')->nullable();
        });

        DB::table('penjualans')
            ->whereNull('snap_token')
            ->update([
                'payment_status' => 'paid',
                'payment_type' => 'manual',
                'midtrans_transaction_status' => 'manual',
                'status_message' => 'Transaksi lama sebelum integrasi Midtrans.',
                'paid_at' => DB::raw('created_at'),
            ]);
    }

    public function down(): void
    {
        Schema::table('penjualans', function (Blueprint $table) {
            $table->dropConstrainedForeignId('vendor_id');
            $table->dropColumn([
                'customer_name',
                'customer_email',
                'customer_phone',
                'payment_status',
                'payment_type',
                'midtrans_transaction_status',
                'midtrans_transaction_id',
                'fraud_status',
                'snap_token',
                'snap_redirect_url',
                'status_message',
                'payment_payload',
                'paid_at',
            ]);
        });
    }
};
