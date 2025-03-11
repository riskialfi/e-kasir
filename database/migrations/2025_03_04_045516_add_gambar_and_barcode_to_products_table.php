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
        Schema::table('products', function (Blueprint $table) {
            $table->string('gambar')->nullable()->after('stok'); // Menyimpan path gambar
            $table->string('barcode')->unique()->after('gambar'); // Menyimpan kode barcode
        });
    }
    
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['gambar', 'barcode']);
        });
    }
    
};
