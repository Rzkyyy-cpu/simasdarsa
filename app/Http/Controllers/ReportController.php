<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Product;
use App\Models\StockBatch;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Laporan Terpadu: Eksekutif & Stok Kritis
     */
    public function executiveReport(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->toDateString());

        // 1. Ringkasan Eksekutif (Finansial)
        $summary = Sale::join('sale_details', 'sales.id', '=', 'sale_details.sale_id')
            ->selectRaw('
                COUNT(DISTINCT sales.id) as total_transactions,
                SUM(sale_details.subtotal) as total_revenue,
                SUM((sale_details.price_at_sale - sale_details.buy_price_at_sale) * sale_details.quantity) as total_profit
            ')
            ->whereBetween('sales.sale_date', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->first();

        // 2. Statistik Stok Kritis (Produk dengan total stok <= min_stock)
        $criticalProducts = Product::select('products.*')
            ->selectRaw('
                COALESCE((
                    SELECT SUM(sb.current_quantity)
                    FROM stock_batches sb
                    WHERE sb.product_id = products.id
                      AND sb.current_quantity > 0
                      AND sb.expired_date >= CURDATE()
                ), 0) as total_stock
            ')
            ->havingRaw('total_stock <= products.min_stock')
            ->orderBy('total_stock', 'asc')
            ->get();

        // 3. Batch Hampir Kedaluwarsa (<= 30 hari)
        $expiringBatches = StockBatch::whereHas('product')
            ->with('product')
            ->where('current_quantity', '>', 0)
            ->whereBetween('expired_date', [now()->toDateString(), now()->addDays(30)->toDateString()])
            ->orderBy('expired_date', 'asc')
            ->get();

        // Cek jika request adalah untuk export CSV
        if ($request->has('export')) {
            return $this->exportToCsv($summary, $criticalProducts, $expiringBatches, $startDate, $endDate);
        }

        return view('reports.executive', compact(
            'summary', 
            'criticalProducts', 
            'expiringBatches',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Export data ke CSV
     */
    private function exportToCsv($summary, $criticalProducts, $expiringBatches, $startDate, $endDate)
    {
        $filename = "Laporan_Eksekutif_{$startDate}_sd_{$endDate}.csv";

        $callback = function() use ($summary, $criticalProducts, $expiringBatches, $startDate, $endDate) {
            $file = fopen('php://output', 'w');
            
            // Header Laporan
            fputcsv($file, ['LAPORAN EKSEKUTIF SIMASDARSA']);
            fputcsv($file, ["Periode: $startDate s/d $endDate"]);
            fputcsv($file, []);

            // Bagian 1: Ringkasan Finansial
            fputcsv($file, ['RINGKASAN FINANSIAL']);
            fputcsv($file, ['Total Transaksi', 'Total Pendapatan', 'Total Laba Kotor']);
            fputcsv($file, [
                $summary->total_transactions ?? 0,
                $summary->total_revenue ?? 0,
                $summary->total_profit ?? 0
            ]);
            fputcsv($file, []);

            // Bagian 2: Stok Kritis
            fputcsv($file, ['DAFTAR STOK KRITIS (DI BAWAH MINIMUM)']);
            fputcsv($file, ['ID', 'Nama Produk', 'Kategori', 'Stok Minimal', 'Stok Saat Ini', 'Satuan', 'Status']);
            
            foreach ($criticalProducts as $product) {
                fputcsv($file, [
                    $product->id,
                    $product->name,
                    $product->category,
                    $product->min_stock,
                    $product->total_stock,
                    $product->unit,
                    $product->total_stock <= 0 ? 'HABIS' : 'KRITIS'
                ]);
            }

            fputcsv($file, []);

            // Bagian 3: Kedaluwarsa
            fputcsv($file, ['DAFTAR BATCH SEGERA KEDALUWARSA (30 HARI)']);
            fputcsv($file, ['Nama Produk', 'Kode Batch', 'Tgl Kedaluwarsa', 'Sisa Hari', 'Stok Sisa']);
            foreach ($expiringBatches as $batch) {
                fputcsv($file, [
                    $batch->product->name,
                    $batch->batch_code ?: '-',
                    $batch->expired_date->format('d/m/Y'),
                    $batch->days_until_expired,
                    $batch->current_quantity
                ]);
            }

            fclose($file);
        };

        return new StreamedResponse($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }
}
