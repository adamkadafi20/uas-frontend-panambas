<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            // Cukup tambahin 2 kolom ini aja karena refund_status udah ada
            $table->boolean('is_refunded')->default(false)->after('status');
            $table->decimal('refund_amount', 15, 2)->default(0)->after('is_refunded');
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['is_refunded', 'refund_amount']);
        });
    }
};