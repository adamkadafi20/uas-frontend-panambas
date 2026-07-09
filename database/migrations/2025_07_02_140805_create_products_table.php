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
    Schema::create('products', function (Blueprint $table) {
        $table->id();
        
        // --- INFORMASI PRODUK ---
        $table->string('title'); // Menangkap name="title" (Nama Tanaman)
        $table->string('sku')->nullable(); // Menangkap name="sku"
        $table->string('category_id')->nullable(); // Menangkap name="category_id"
        
        // --- DESKRIPSI ---
        $table->text('description'); // Menangkap name="description"
        
        // --- SPESIFIKASI (Boleh kosong/nullable) ---
        $table->string('brand')->nullable(); // Menangkap name="brand"
        $table->string('origin')->nullable(); // Menangkap name="origin"
        $table->string('size')->nullable(); // Menangkap name="size" (Tinggi/Ukuran)
        $table->string('light_requirement')->nullable(); // Menangkap name="light_requirement"
        $table->string('color')->nullable(); // Menangkap name="color"
        $table->string('care_level')->nullable(); // Menangkap name="care_level" (Saran Perawatan)
        
        // --- INFORMASI PENJUALAN (Mode Single Input) ---
        $table->bigInteger('price')->default(0); // Menangkap name="price"
        $table->integer('qty')->nullable(); // Menangkap name="qty" (Stok)
        $table->integer('min_qty')->default(1); // Menangkap name="min_qty"
        $table->string('max_purchase_type')->default('unlimited'); // Menangkap name="max_purchase_type"
        $table->integer('max_purchase_limit')->nullable(); // Menangkap input dari trigger Javascript
        
        // --- PENGIRIMAN ---
        $table->integer('weight')->nullable(); // Menangkap name="weight"
        $table->boolean('is_reguler')->default(true); // Untuk toggle kurir reguler
        $table->boolean('is_instant')->default(false); // Untuk toggle kurir instant
        
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};