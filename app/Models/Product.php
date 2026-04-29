<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'barcode',
        'name',
        'category',
        'min_stock',
        'unit',
    ];

    protected $appends = [
        'total_stock',        // Total stok dari semua batch
        'is_critical_stock',  // Apakah stok di bawah minimum
    ];

    // =========================================================
    // RELASI ELOQUENT
    // =========================================================

    /**
     * Satu produk memiliki banyak batch stok.
     */
    public function stockBatches(): HasMany
    {
        return $this->hasMany(StockBatch::class);
    }

    /**
     * Batch stok yang masih aktif (stok > 0 dan belum kedaluwarsa).
     */
    public function activeBatches(): HasMany
    {
        return $this->hasMany(StockBatch::class)
                    ->where('current_quantity', '>', 0)
                    ->where('expired_date', '>=', now()->toDateString())
                    ->orderBy('expired_date', 'asc'); // Urutan FEFO
    }

    /**
     * Batch yang akan kedaluwarsa dalam N hari ke depan.
     */
    public function expiringBatches(int $days = 30): HasMany
    {
        return $this->hasMany(StockBatch::class)
                    ->where('current_quantity', '>', 0)
                    ->whereBetween('expired_date', [
                        now()->toDateString(),
                        now()->addDays($days)->toDateString(),
                    ])
                    ->orderBy('expired_date', 'asc');
    }

    /**
     * Batch yang sudah kedaluwarsa tapi masih ada stok tersisa.
     */
    public function expiredBatches(): HasMany
    {
        return $this->hasMany(StockBatch::class)
                    ->where('current_quantity', '>', 0)
                    ->where('expired_date', '<', now()->toDateString());
    }

    /**
     * Detail penjualan melalui relasi hasMany via batch.
     */
    public function saleDetails(): HasMany
    {
        return $this->hasMany(SaleDetail::class);
    }

    // =========================================================
    // ACCESSOR / COMPUTED ATTRIBUTES
    // =========================================================

    /**
     * Hitung total stok dari semua batch yang aktif.
     * Dipanggil otomatis via $appends: $product->total_stock
     */
    public function getTotalStockAttribute(): int
    {
        return $this->stockBatches()
                    ->where('current_quantity', '>', 0)
                    ->where('expired_date', '>=', now()->toDateString())
                    ->sum('current_quantity');
    }

    /**
     * Cek apakah stok di bawah batas minimal.
     * $product->is_critical_stock → true/false
     */
    public function getIsCriticalStockAttribute(): bool
    {
        return $this->total_stock <= $this->min_stock;
    }

    // =========================================================
    // SCOPE QUERY
    // =========================================================

    /**
     * Scope: Produk dengan stok kritis.
     * Penggunaan: Product::criticalStock()->get()
     */
    public function scopeCriticalStock($query)
    {
        return $query->whereHas('stockBatches', function ($q) {
            $q->where('current_quantity', '>', 0)
              ->where('expired_date', '>=', now()->toDateString());
        }, '<', 1)
        ->orWhere(function ($q) {
            $q->whereHas('stockBatches', function ($inner) {
                $inner->where('current_quantity', '>', 0)
                      ->where('expired_date', '>=', now()->toDateString())
                      ->groupBy('product_id')
                      ->havingRaw('SUM(current_quantity) <= products.min_stock');
            });
        });
    }

    /**
     * Scope: Cari berdasarkan nama atau barcode.
     * Penggunaan: Product::search('teh botol')->get()
     */
    public function scopeSearch($query, string $keyword)
    {
        return $query->where(function ($q) use ($keyword) {
            $q->where('name', 'like', "%{$keyword}%")
              ->orWhere('barcode', 'like', "%{$keyword}%")
              ->orWhere('category', 'like', "%{$keyword}%");
        });
    }
}