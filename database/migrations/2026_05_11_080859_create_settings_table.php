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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->text('store_address')->nullable();
            // Pengiriman
            $table->boolean('global_reguler')->default(1);
            $table->boolean('global_kargo')->default(1);
            $table->boolean('global_instant')->default(0);
            // Pembayaran
            $table->boolean('pay_va')->default(1);
            $table->boolean('pay_ewallet')->default(1);
            $table->boolean('pay_cod')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
