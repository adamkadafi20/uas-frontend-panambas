<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('addresses', function (Blueprint $table) {
        $table->id();
        // Relasi ke tabel users (karena tiap alamat punya user tertentu)
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        
        $table->string('receiver_name'); // Nama penerima
        $table->string('phone');
        $table->string('province'); // Provinsi buat cek ongkir
        $table->string('city');
        $table->string('district'); // Kecamatan
        $table->string('postal_code');
        $table->text('detail_address'); // Jalan, patokan, dll
        $table->boolean('is_primary')->default(false); // Alamat Utama (True/False)
        
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
