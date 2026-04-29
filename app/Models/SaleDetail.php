<?php
// ============================================================
// FILE: app/Models/SaleDetail.php
// ============================================================

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'batch_id',
        'product_id',
        'quantity',
        'price_at_sale',
        'buy_price_at_sale',
        'subtotal',
    ];

    protected $casts = [
        'price_at_sale'     => 'decimal:2',
        'buy_price_at_sale' => 'decimal:2',
        'subtotal'          => 'decimal:2',
    ];

    protected $appends = ['profit'];

    // =========================================================
    // RELASI
    // =========================================================

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(StockBatch::class, 'batch_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // =========================================================
    // ACCESSOR
    // =========================================================

    /** Laba per item = (harga jual - harga beli) × qty */
    public function getProfitAttribute(): float
    {
        return ($this->price_at_sale - $this->buy_price_at_sale) * $this->quantity;
    }
}