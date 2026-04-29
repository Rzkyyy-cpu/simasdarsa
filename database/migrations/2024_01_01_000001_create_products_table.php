<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel products untuk menyimpan data master produk.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('barcode', 50)->unique()->nullable()->comment('Kode barcode produk');
            $table->string('name', 200)->comment('Nama produk');
            $table->string('category', 100)->comment('Kategori produk (makanan, minuman, dll)');
            $table->unsignedInteger('min_stock')->default(5)->comment('Ambang batas stok minimal');
            $table->string('unit', 30)->default('pcs')->comment('Satuan produk (pcs, kg, liter, dll)');
            $table->timestamps();
            $table->softDeletes(); // Soft delete agar data tidak hilang permanen

            // Index untuk pencarian cepat berdasarkan barcode dan nama
            $table->index('barcode');
            $table->index('name');
            $table->index('category');
        });
    }

    /**
     * Rollback: hapus tabel products.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};