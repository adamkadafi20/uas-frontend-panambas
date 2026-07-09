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
    Schema::create('product_views', function (Blueprint $table) {
        $table->id();
        $table->foreignId('product_id')->nullable()->constrained()->onDelete('cascade'); 
        $table->string('session_id'); // Buat nandain pengunjung unik
        $table->string('ip_address')->nullable();
        $table->timestamps(); // Ini yang paling penting (created_at)
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_views');
    }
};
