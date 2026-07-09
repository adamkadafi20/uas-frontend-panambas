<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Cek kalau belum ada, baru dibikin. Kalau udah ada, lewatin aja.
            if (!Schema::hasColumn('orders', 'cancel_reason')) {
                $table->text('cancel_reason')->nullable();
            }
            if (!Schema::hasColumn('orders', 'cancelled_by')) {
                $table->string('cancelled_by')->nullable();
            }
            if (!Schema::hasColumn('orders', 'cancel_request_status')) {
                $table->string('cancel_request_status')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['cancel_reason', 'cancelled_by', 'cancel_request_status']);
        });
    }
};