<?php
 
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
 
return new class extends Migration
{
    /**
     * Membuat tabel stock_batches untuk menyimpan batch stok per produk.
     * Setiap batch memiliki harga beli, harga jual, dan tanggal kedaluwarsa sendiri.
     */
    public function up(): void
    {
        Schema::create('stock_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')
                  ->constrained('products')
                  ->onDelete('cascade')
                  ->comment('Relasi ke produk');
            $table->string('batch_code', 50)->nullable()->comment('Kode batch dari supplier');
            $table->decimal('buy_price', 12, 2)->comment('Harga beli per satuan (HPP)');
            $table->decimal('sell_price', 12, 2)->comment('Harga jual per satuan');
            $table->unsignedInteger('initial_quantity')->comment('Jumlah stok awal saat batch masuk');
            $table->unsignedInteger('current_quantity')->comment('Jumlah stok tersisa saat ini');
            $table->date('expired_date')->comment('Tanggal kedaluwarsa batch ini');
            $table->date('received_date')->default(now())->comment('Tanggal batch diterima');
            $table->timestamps();
 
            // Index krusial untuk logika FEFO:
            // Saat query stok, kita akan ORDER BY expired_date ASC
            // sehingga batch yang paling dekat expired-nya diprioritaskan
            $table->index(['product_id', 'expired_date', 'current_quantity'], 'idx_fefo');
            $table->index('expired_date'); // Untuk monitoring kedaluwarsa di dashboard
        });
    }
 
    /**
     * Rollback: hapus tabel stock_batches.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_batches');
    }
};