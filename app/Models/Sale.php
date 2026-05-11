<?php
// ============================================================
// FILE: app/Models/Sale.php
// ============================================================
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'sale_date',
        'total_payment',
        'total_amount',
        'change_amount',
        'cashier',
        'notes',
    ];

    protected $casts = [
        'sale_date'     => 'datetime',
        'total_payment' => 'decimal:2',
        'total_amount'  => 'decimal:2',
        'change_amount' => 'decimal:2',
    ];

    protected $appends = ['total_profit'];

    // =========================================================
    // RELASI
    // =========================================================

    /** Satu transaksi memiliki banyak item detail */
    public function details(): HasMany
    {
        return $this->hasMany(SaleDetail::class);
    }

    // =========================================================
    // ACCESSOR
    // =========================================================

    /** Hitung total laba dari semua item dalam transaksi ini */
    public function getTotalProfitAttribute(): float
    {
        return $this->details->sum(function ($detail) {
            return ($detail->price_at_sale - $detail->buy_price_at_sale) * $detail->quantity;
        });
    }

    // =========================================================
    // BOOT: Generate nomor invoice otomatis
    // =========================================================
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($sale) {
            $sale->sale_date = Carbon::parse($sale->sale_date ?: now());

            $saleDate = $sale->sale_date; // Define $saleDate AFTER $sale->sale_date is set

            if (empty($sale->invoice_number)) {
                $dateString = $saleDate->format('Ymd');
                $lastSale = static::whereDate('sale_date', $saleDate->toDateString())
                    ->latest('id')
                    ->first();

                $sequence = $lastSale ? (intval(substr($lastSale->invoice_number, -4)) + 1) : 1;
                $sale->invoice_number = 'INV-' . $dateString . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
            }
        });
    }
}