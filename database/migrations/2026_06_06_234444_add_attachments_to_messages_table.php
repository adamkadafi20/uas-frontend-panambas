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
    Schema::table('messages', function (Blueprint $table) {
        $table->string('type')->default('text'); // text, image, video, product, order
        $table->string('file_path')->nullable(); // Buat nyimpen link foto/video
        $table->unsignedBigInteger('reference_id')->nullable(); // Buat nyimpen ID Produk/Pesanan
    });
}

public function down(): void
{
    Schema::table('messages', function (Blueprint $table) {
        $table->dropColumn(['type', 'file_path', 'reference_id']);
    });
}
};
