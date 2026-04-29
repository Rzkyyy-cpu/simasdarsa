<?php
 
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
 
return new class extends Migration
{
    /**
     * Membuat tabel sales (header transaksi) dan sale_details (item per transaksi).
     */
    public function up(): void
    {
        // Tabel header penjualan
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 30)->unique()->comment('Nomor invoice otomatis');
            $table->dateTime('sale_date')->comment('Waktu transaksi terjadi');
            $table->decimal('total_payment', 14, 2)->comment('Total pembayaran dari pelanggan');
            $table->decimal('total_amount', 14, 2)->comment('Total harga semua item');
            $table->decimal('change_amount', 14, 2)->default(0)->comment('Uang kembalian');
            $table->string('cashier', 100)->nullable()->comment('Nama kasir yang melayani');
            $table->text('notes')->nullable()->comment('Catatan transaksi');
            $table->timestamps();
 
            $table->index('sale_date'); // Untuk laporan per periode
            $table->index('invoice_number');
        });
 
        // Tabel detail item per transaksi
        Schema::create('sale_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')
                  ->constrained('sales')
                  ->onDelete('cascade')
                  ->comment('Relasi ke transaksi penjualan');
            $table->foreignId('batch_id')
                  ->constrained('stock_batches')
                  ->onDelete('restrict') // Jangan hapus batch jika sudah terjual
                  ->comment('Batch stok yang diambil (sesuai FEFO)');
            $table->foreignId('product_id')
                  ->constrained('products')
                  ->comment('Denormalisasi produk untuk kemudahan laporan');
            $table->unsignedInteger('quantity')->comment('Jumlah item yang terjual');
            $table->decimal('price_at_sale', 12, 2)->comment('Harga jual saat transaksi (snapshot)');
            $table->decimal('buy_price_at_sale', 12, 2)->comment('Harga beli saat transaksi (untuk hitung laba)');
            $table->decimal('subtotal', 14, 2)->comment('Subtotal = quantity * price_at_sale');
            $table->timestamps();
 
            $table->index(['sale_id', 'batch_id']);
        });
    }
 
    /**
     * Rollback: hapus tabel sale_details dan sales.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_details');
        Schema::dropIfExists('sales');
    }
};