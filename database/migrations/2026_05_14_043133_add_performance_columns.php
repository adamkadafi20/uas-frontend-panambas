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
        // Nambahin views & sold di tabel produk (induk)
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'views')) {
                $table->integer('views')->default(0);
            }
            if (!Schema::hasColumn('products', 'sold')) {
                $table->integer('sold')->default(0);
            }
        });

        // Nambahin sold di tabel variasi (anak)
        Schema::table('product_variations', function (Blueprint $table) {
            if (!Schema::hasColumn('product_variations', 'sold')) {
                $table->integer('sold')->default(0);
            }
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['views', 'sold']);
        });
        Schema::table('product_variations', function (Blueprint $table) {
            $table->dropColumn('sold');
        });
    }
};
