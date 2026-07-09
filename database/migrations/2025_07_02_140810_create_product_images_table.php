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
        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            // Relasi ke tabel products (kalau produk dihapus, fotonya ikut kehapus dari DB)
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade'); 
            $table->string('image_path'); // Buat simpen nama file foto
            $table->boolean('is_promo')->default(false); // Nandain mana foto yang promosi
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_images');
    }
};