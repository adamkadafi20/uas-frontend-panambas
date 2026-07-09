<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            // Cuma nambahin 4 kolom yang belum ada di database lo
            $table->string('bentuk_pupuk')->nullable();
            $table->string('volume_isi')->nullable();
            $table->string('bahan_material')->nullable();
            $table->string('dimensi')->nullable();
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            // Kalau di-rollback, hapus 4 kolom ini aja
            $table->dropColumn([
                'bentuk_pupuk', 
                'volume_isi', 
                'bahan_material', 
                'dimensi'
            ]);
        });
    }
};