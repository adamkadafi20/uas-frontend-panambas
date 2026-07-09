<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable(); // Judul/Nama promo (buat penanda di admin)
            $table->string('image_path');       // Tempat nyimpen file gambar atau video (.mp4)
            $table->string('link')->nullable(); // URL tujuan pas banner-nya diklik pembeli
            $table->integer('sort_order')->default(0); // Buat ngatur urutan slide (Slide 1, Slide 2, dst)
            $table->boolean('status')->default(true);  // Aktif (1) atau Non-aktif (0)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};