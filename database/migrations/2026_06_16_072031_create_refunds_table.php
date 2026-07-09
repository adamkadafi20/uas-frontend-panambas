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
    Schema::create('refunds', function (Blueprint $table) {
        $table->id();
        $table->foreignId('order_id')->constrained()->onDelete('cascade');
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        
        $table->string('type'); // Isinya: 'dana_saja' atau 'barang_dana'
        $table->string('reason'); // Alasan (e.g., "Barang rusak")
        $table->text('items')->nullable(); // Nyimpen nama-nama barang yg direfund
        $table->decimal('amount', 15, 2)->default(0); // Estimasi dana yg dibalikin
        
        $table->text('photos')->nullable(); // Nyimpen nama file foto (format JSON array)
        $table->string('video')->nullable(); // Nyimpen nama file video
        
        // Status refund: 'pending' (baru ngajuin), 'waiting_return' (nunggu brg balik), 'approved' (selesai)
        $table->string('status')->default('pending'); 
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
