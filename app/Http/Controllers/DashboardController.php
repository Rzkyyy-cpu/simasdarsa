<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\StockBatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * DashboardController - Halaman utama dengan ringkasan & alert.
 */
class DashboardController extends Controller
{
    /**
     * Tampilkan dashboard utama.
     * GET /dashboard
     */
    public function index()
    {
        // -------------------------------------------------------
        // 1. ALERT KEDALUWARSA (produk akan expired ≤ 30 hari)
        // -------------------------------------------------------
        $expiringBatches = StockBatch::with('product:id,name,unit,category')
            ->where('current_quantity', '>', 0)
            ->whereBetween('expired_date', [
                now()->toDateString(),
                now()->addDays(30)->toDateString(),
            ])
            ->orderBy('expired_date', 'asc')
            ->get();

        // Batch yang SUDAH expired tapi masih ada stok (kerugian potensial)
        $expiredWithStock = StockBatch::with('product:id,name,unit')
            ->where('current_quantity', '>', 0)
            ->where('expired_date', '<', now()->toDateString())
            ->orderBy('expired_date', 'asc')
            ->get();

        // -------------------------------------------------------
        // 2. ALERT STOK KRITIS
        // -------------------------------------------------------
        // Query produk yang total stok aktifnya ≤ min_stock
        $criticalProducts = Product::select('products.*')
            ->selectRaw('
                COALESCE(SUM(sb.current_quantity), 0) as total_stock
            ')
            ->leftJoin('stock_batches as sb', function ($join) {
                $join->on('sb.product_id', '=', 'products.id')
                     ->where('sb.current_quantity', '>', 0)
                     ->where('sb.expired_date', '>=', now()->toDateString());
            })
            ->groupBy('products.id')
            ->havingRaw('total_stock <= products.min_stock')
            ->orderBy('total_stock', 'asc')
            ->limit(10)
            ->get();

        // -------------------------------------------------------
        // 3. STATISTIK HARI INI
        // -------------------------------------------------------
        $todaySales = Sale::whereDate('sale_date', today())->count();
        $todayRevenue = Sale::whereDate('sale_date', today())->sum('total_amount');
        $todayProfit = SaleDetail::whereHas('sale', fn($q) => $q->whereDate('sale_date', today()))
                                  ->selectRaw('SUM((price_at_sale - buy_price_at_sale) * quantity) as profit')
                                  ->value('profit') ?? 0;

        // -------------------------------------------------------
        // 4. STATISTIK BULAN INI
        // -------------------------------------------------------
        $monthSales   = Sale::whereMonth('sale_date', now()->month)
                             ->whereYear('sale_date', now()->year)
                             ->count();
        $monthRevenue = Sale::whereMonth('sale_date', now()->month)
                             ->whereYear('sale_date', now()->year)
                             ->sum('total_amount');

        // -------------------------------------------------------
        // 5. GRAFIK PENJUALAN 7 HARI TERAKHIR
        // -------------------------------------------------------
        $weeklyChart = Sale::select(
                            DB::raw('DATE(sale_date) as tanggal'),
                            DB::raw('SUM(total_amount) as total'),
                            DB::raw('COUNT(*) as jumlah_transaksi')
                        )
                        ->where('sale_date', '>=', now()->subDays(6)->startOfDay())
                        ->groupBy('tanggal')
                        ->orderBy('tanggal')
                        ->get();

        // -------------------------------------------------------
        // 6. TOP 5 PRODUK TERLARIS BULAN INI
        // -------------------------------------------------------
        $topProducts = SaleDetail::select('product_id', DB::raw('SUM(quantity) as total_terjual'))
                        ->with('product:id,name,unit')
                        ->whereHas('sale', fn($q) =>
                            $q->whereMonth('sale_date', now()->month)
                              ->whereYear('sale_date', now()->year)
                        )
                        ->groupBy('product_id')
                        ->orderByDesc('total_terjual')
                        ->limit(5)
                        ->get();

        return view('dashboard', compact(
            'expiringBatches',
            'expiredWithStock',
            'criticalProducts',
            'todaySales',
            'todayRevenue',
            'todayProfit',
            'monthSales',
            'monthRevenue',
            'weeklyChart',
            'topProducts',
        ));
    }
}