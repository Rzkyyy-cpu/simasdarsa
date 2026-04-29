<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Product;
use App\Models\StockBatch;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Executive Report - Pimpinan
     */
    public function executiveReport()
    {
        // Monthly sales data for the last 12 months
        $monthlySales = Sale::select(
            DB::raw('YEAR(created_at) as year'),
            DB::raw('MONTH(created_at) as month'),
            DB::raw('SUM(total_amount) as total_sales'),
            DB::raw('COUNT(*) as total_transactions')
        )
        ->where('created_at', '>=', Carbon::now()->subMonths(12))
        ->groupBy('year', 'month')
        ->orderBy('year', 'desc')
        ->orderBy('month', 'desc')
        ->get();

        // Top selling products
        $topProducts = Sale::join('sale_details', 'sales.id', '=', 'sale_details.sale_id')
            ->join('products', 'sale_details.product_id', '=', 'products.id')
            ->select(
                'products.name',
                DB::raw('SUM(sale_details.quantity) as total_quantity'),
                DB::raw('SUM(sale_details.subtotal) as total_revenue')
            )
            ->where('sales.created_at', '>=', Carbon::now()->subMonths(3))
            ->groupBy('products.id', 'products.name')
            ->orderBy('total_revenue', 'desc')
            ->limit(10)
            ->get();

        // Profit margin calculation
        $totalRevenue = Sale::where('created_at', '>=', Carbon::now()->startOfMonth())->sum('total_amount');
        $totalCost = StockBatch::sum(DB::raw('purchase_price * current_stock'));
        $profitMargin = $totalRevenue > 0 ? (($totalRevenue - $totalCost) / $totalRevenue) * 100 : 0;

        return view('pimpinan.executive-report', compact(
            'monthlySales',
            'topProducts',
            'totalRevenue',
            'profitMargin'
        ));
    }

    /**
     * Critical Stock Statistics - Pimpinan
     */
    public function criticalStockStats()
    {
        // Products with low stock (less than 10 units)
        $criticalStock = StockBatch::join('products', 'stock_batches.product_id', '=', 'products.id')
            ->select(
                'products.name',
                'products.sku',
                DB::raw('SUM(stock_batches.current_stock) as total_stock'),
                DB::raw('MIN(stock_batches.expiry_date) as earliest_expiry')
            )
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->having('total_stock', '<', 10)
            ->orderBy('total_stock', 'asc')
            ->get();

        // Expiring soon (within 30 days)
        $expiringSoon = StockBatch::join('products', 'stock_batches.product_id', '=', 'products.id')
            ->select(
                'products.name',
                'stock_batches.batch_number',
                'stock_batches.expiry_date',
                'stock_batches.current_stock'
            )
            ->where('expiry_date', '<=', Carbon::now()->addDays(30))
            ->where('expiry_date', '>=', Carbon::now())
            ->where('current_stock', '>', 0)
            ->orderBy('expiry_date', 'asc')
            ->get();

        // Stock turnover rate
        $stockTurnover = Sale::join('sale_details', 'sales.id', '=', 'sale_details.sale_id')
            ->join('products', 'sale_details.product_id', '=', 'products.id')
            ->select(
                'products.name',
                DB::raw('SUM(sale_details.quantity) as sold_quantity'),
                DB::raw('AVG(products.price) as avg_price')
            )
            ->where('sales.created_at', '>=', Carbon::now()->subMonths(3))
            ->groupBy('products.id', 'products.name')
            ->orderBy('sold_quantity', 'desc')
            ->get();

        return view('pimpinan.critical-stock-stats', compact(
            'criticalStock',
            'expiringSoon',
            'stockTurnover'
        ));
    }
}