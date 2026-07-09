<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('product_variations', function (Blueprint $table) {
            $table->id();
            // Sambungin ke tabel products induknya
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            
            // Nama variasi (contoh: "Warna" atau "Ukuran")
            $table->string('variation_name')->nullable();
            
            // Pilihan variasinya (contoh: "Merah", "Hijau", "XL")
            $table->string('variation_option');
            
            // Harga, Stok, SKU khusus variasi ini
            $table->bigInteger('price')->default(0);
            $table->integer('stock')->default(0);
            $table->string('sku')->nullable();
            
            // Foto khusus variasi ini (gambar kecil pas diklik)
            $table->string('image_path')->nullable();
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_variations');
    }
};