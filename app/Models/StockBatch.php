<?php
// ============================================================
// FILE: app/Models/StockBatch.php
// ============================================================
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'batch_code',
        'buy_price',
        'sell_price',
        'initial_quantity',
        'current_quantity',
        'expired_date',
        'received_date',
        'is_verified',
        'location',
    ];

    protected $casts = [
        'expired_date'  => 'date',
        'received_date' => 'date',
        'buy_price'     => 'decimal:2',
        'sell_price'    => 'decimal:2',
    ];

    protected $appends = ['days_until_expired', 'expiry_status'];

    // =========================================================
    // RELASI
    // =========================================================

    /** Batch dimiliki oleh satu produk */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** Satu batch bisa muncul di banyak detail penjualan */
    public function saleDetails(): HasMany
    {
        return $this->hasMany(SaleDetail::class, 'batch_id');
    }

    // =========================================================
    // ACCESSOR
    // =========================================================

    /** Hitung sisa hari hingga kedaluwarsa */
    public function getDaysUntilExpiredAttribute(): int
    {
        return now()->diffInDays($this->expired_date, false);
    }

    /**
     * Status kedaluwarsa:
     * - 'expired'   : sudah lewat
     * - 'critical'  : ≤ 7 hari
     * - 'warning'   : ≤ 30 hari
     * - 'safe'      : > 30 hari
     */
    public function getExpiryStatusAttribute(): string
    {
        $days = $this->days_until_expired;
        if ($days < 0)  return 'expired';
        if ($days <= 7)  return 'critical';
        if ($days <= 30) return 'warning';
        return 'safe';
    }

    // =========================================================
    // SCOPE
    // =========================================================

    /** Hanya batch yang masih bisa dijual (stok > 0 & belum expired) */
    public function scopeAvailable($query)
    {
        return $query->where('current_quantity', '>', 0)
                     ->where('expired_date', '>=', now()->toDateString());
    }

    /** Urutan FEFO: expired paling dekat dahulu */
    public function scopeFefo($query)
    {
        return $query->orderBy('expired_date', 'asc');
    }
}