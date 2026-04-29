<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\StockBatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * SaleController - Mengelola seluruh proses transaksi penjualan
 * dengan logika FEFO (First Expired First Out).
 *
 * ALUR FEFO:
 * 1. Kasir memasukkan produk + jumlah yang dibeli.
 * 2. Sistem mencari batch produk tersebut, diurutkan dari
 *    expired_date PALING DEKAT (ASC).
 * 3. Kurangi stok dari batch pertama. Jika kurang, lanjut ke batch berikutnya.
 * 4. Ulangi hingga total qty terpenuhi atau stok habis.
 */
class SaleController extends Controller
{
    // =========================================================
    // HALAMAN KASIR (POS)
    // =========================================================

    /**
     * Tampilkan halaman kasir.
     * GET /kasir
     */
    public function index()
    {
        return view('pos.index');
    }

    /**
     * Cari produk berdasarkan barcode atau nama (untuk autocomplete kasir).
     * GET /kasir/cari-produk?q=teh+botol
     */
    public function searchProduct(Request $request)
    {
        $keyword = $request->get('q', '');

        $products = Product::with(['activeBatches' => function ($q) {
                        $q->fefo()->available();
                    }])
                    ->search($keyword)
                    ->limit(10)
                    ->get()
                    ->filter(fn($p) => $p->total_stock > 0) // Hanya tampilkan yang ada stok
                    ->map(function ($product) {
                        // Ambil harga jual dari batch FEFO pertama (yang paling dekat expired)
                        $firstBatch = $product->activeBatches->first();
                        return [
                            'id'          => $product->id,
                            'barcode'     => $product->barcode,
                            'name'        => $product->name,
                            'category'    => $product->category,
                            'unit'        => $product->unit,
                            'total_stock' => $product->total_stock,
                            'sell_price'  => $firstBatch?->sell_price ?? 0,
                        ];
                    })
                    ->values();

        return response()->json($products);
    }

    // =========================================================
    // PROSES TRANSAKSI DENGAN LOGIKA FEFO
    // =========================================================

    /**
     * Proses penjualan — inti dari logika FEFO.
     * POST /kasir/proses
     *
     * Request body:
     * {
     *   "items": [
     *     {"product_id": 1, "quantity": 3},
     *     {"product_id": 5, "quantity": 10}
     *   ],
     *   "total_payment": 50000,
     *   "cashier": "Budi"
     * }
     */
    public function processSale(Request $request)
    {
        // --- Validasi input ---
        $validated = $request->validate([
            'items'                => 'required|array|min:1',
            'items.*.product_id'  => 'required|exists:products,id',
            'items.*.quantity'    => 'required|integer|min:1',
            'total_payment'       => 'required|numeric|min:0',
            'cashier'             => 'nullable|string|max:100',
            'notes'               => 'nullable|string|max:500',
        ]);

        // Gunakan DB Transaction agar semua perubahan stok atomic
        // (jika ada error di tengah jalan, semua rollback)
        return DB::transaction(function () use ($validated) {

            $saleDetailsData = [];
            $totalAmount     = 0;

            // -------------------------------------------------------
            // LANGKAH 1: Validasi stok & hitung total untuk semua item
            // -------------------------------------------------------
            foreach ($validated['items'] as $item) {
                $product       = Product::findOrFail($item['product_id']);
                $qtyNeeded     = $item['quantity'];

                // Ambil stok tersedia (FEFO: diurutkan expired_date paling dekat dulu)
                $availableStock = StockBatch::where('product_id', $product->id)
                                            ->available()
                                            ->fefo()
                                            ->sum('current_quantity');

                // Tolak jika stok tidak cukup
                if ($availableStock < $qtyNeeded) {
                    throw new \Exception(
                        "Stok {$product->name} tidak cukup. " .
                        "Dibutuhkan: {$qtyNeeded}, Tersedia: {$availableStock}"
                    );
                }

                // Hitung subtotal dari batch FEFO pertama (harga snapshot)
                $firstBatch   = StockBatch::where('product_id', $product->id)
                                          ->available()->fefo()->first();
                $subtotal     = $firstBatch->sell_price * $qtyNeeded;
                $totalAmount += $subtotal;

                $saleDetailsData[] = [
                    'product'    => $product,
                    'qty_needed' => $qtyNeeded,
                    'subtotal'   => $subtotal,
                ];
            }

            // Validasi uang pembayaran cukup
            if ($validated['total_payment'] < $totalAmount) {
                throw new \Exception(
                    "Pembayaran tidak cukup. " .
                    "Total: Rp " . number_format($totalAmount) .
                    ", Dibayar: Rp " . number_format($validated['total_payment'])
                );
            }

            // -------------------------------------------------------
            // LANGKAH 2: Buat header transaksi
            // -------------------------------------------------------
            $sale = Sale::create([
                'sale_date'     => now(),
                'total_amount'  => $totalAmount,
                'total_payment' => $validated['total_payment'],
                'change_amount' => $validated['total_payment'] - $totalAmount,
                'cashier'       => $validated['cashier'] ?? 'Kasir',
                'notes'         => $validated['notes'] ?? null,
            ]);

            // -------------------------------------------------------
            // LANGKAH 3: Kurangi stok menggunakan algoritma FEFO
            // -------------------------------------------------------
            foreach ($saleDetailsData as $itemData) {
                $this->deductStockFefo(
                    sale:       $sale,
                    product:    $itemData['product'],
                    qtyNeeded:  $itemData['qty_needed'],
                );
            }

            // Muat relasi untuk response
            $sale->load('details.product', 'details.batch');

            Log::info("Transaksi berhasil: {$sale->invoice_number}, Total: {$totalAmount}");

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil!',
                'data'    => [
                    'invoice_number' => $sale->invoice_number,
                    'total_amount'   => $totalAmount,
                    'total_payment'  => $sale->total_payment,
                    'change_amount'  => $sale->change_amount,
                    'details'        => $sale->details,
                ],
            ]);
        });
    }

    // =========================================================
    // ALGORITMA FEFO — INTI SISTEM
    // =========================================================

    /**
     * Kurangi stok produk dengan metode FEFO.
     *
     * Cara kerja:
     * 1. Query semua batch produk yang masih aktif, urut dari expired_date ASC.
     * 2. Loop setiap batch:
     *    - Ambil sebanyak mungkin dari batch ini (hingga qty_needed terpenuhi).
     *    - Catat ke sale_details.
     *    - Update current_quantity batch.
     * 3. Lanjut ke batch berikutnya jika masih ada sisa kebutuhan.
     *
     * @param Sale    $sale      Header transaksi
     * @param Product $product   Produk yang dibeli
     * @param int     $qtyNeeded Jumlah yang dibutuhkan
     */
    private function deductStockFefo(Sale $sale, Product $product, int $qtyNeeded): void
    {
        // Ambil semua batch yang available, diurutkan FEFO (expired paling dekat dulu)
        // Gunakan lockForUpdate() agar aman dari race condition (concurrent request)
        $batches = StockBatch::where('product_id', $product->id)
                             ->available()
                             ->fefo()
                             ->lockForUpdate() // Kunci row selama transaksi DB
                             ->get();

        $remainingQty = $qtyNeeded;

        foreach ($batches as $batch) {
            if ($remainingQty <= 0) break; // Kebutuhan sudah terpenuhi

            // Ambil sebanyak yang bisa diambil dari batch ini
            $qtyFromThisBatch = min($batch->current_quantity, $remainingQty);

            // Simpan detail penjualan (snapshot harga)
            SaleDetail::create([
                'sale_id'           => $sale->id,
                'batch_id'          => $batch->id,
                'product_id'        => $product->id,
                'quantity'          => $qtyFromThisBatch,
                'price_at_sale'     => $batch->sell_price,    // Snapshot harga jual
                'buy_price_at_sale' => $batch->buy_price,     // Snapshot harga beli (untuk laba)
                'subtotal'          => $batch->sell_price * $qtyFromThisBatch,
            ]);

            // Kurangi stok batch
            $batch->decrement('current_quantity', $qtyFromThisBatch);

            // Kurangi sisa kebutuhan
            $remainingQty -= $qtyFromThisBatch;

            Log::debug(
                "FEFO: Produk [{$product->name}] " .
                "Batch #{$batch->id} (exp: {$batch->expired_date->format('d/m/Y')}) " .
                "dikurangi {$qtyFromThisBatch}, sisa stok batch: " .
                ($batch->current_quantity - $qtyFromThisBatch)
            );
        }

        // Jika masih ada sisa (tidak seharusnya terjadi karena sudah divalidasi di atas)
        if ($remainingQty > 0) {
            throw new \Exception(
                "Gagal memotong stok {$product->name}. " .
                "Kekurangan: {$remainingQty} {$product->unit}"
            );
        }
    }

    // =========================================================
    // RIWAYAT PENJUALAN
    // =========================================================

    /**
     * Tampilkan daftar riwayat penjualan.
     * GET /penjualan
     */
    public function history(Request $request)
    {
        $query = Sale::with('details.product')
                     ->latest('sale_date');

        // Filter berdasarkan tanggal
        if ($request->filled('dari')) {
            $query->whereDate('sale_date', '>=', $request->dari);
        }
        if ($request->filled('sampai')) {
            $query->whereDate('sale_date', '<=', $request->sampai);
        }

        $sales = $query->paginate(20);

        return view('sales.history', compact('sales'));
    }

    /**
     * Detail satu transaksi.
     * GET /penjualan/{id}
     */
    public function show(Sale $sale)
    {
        $sale->load('details.product', 'details.batch');
        return view('sales.show', compact('sale'));
    }

    // =========================================================
    // LAPORAN LABA RUGI
    // =========================================================

    /**
     * Laporan laba rugi sederhana.
     * GET /laporan/laba-rugi
     */
    public function profitReport(Request $request)
    {
        $dari    = $request->get('dari', now()->startOfMonth()->toDateString());
        $sampai  = $request->get('sampai', now()->toDateString());

        // Hitung total pendapatan, HPP, dan laba per produk
        $report = SaleDetail::select(
                        'product_id',
                        DB::raw('SUM(quantity) as total_qty'),
                        DB::raw('SUM(subtotal) as total_revenue'),
                        DB::raw('SUM(buy_price_at_sale * quantity) as total_cogs'),
                        DB::raw('SUM((price_at_sale - buy_price_at_sale) * quantity) as total_profit')
                    )
                    ->whereHas('sale', function ($q) use ($dari, $sampai) {
                        $q->whereDate('sale_date', '>=', $dari)
                          ->whereDate('sale_date', '<=', $sampai);
                    })
                    ->with('product:id,name,category,unit')
                    ->groupBy('product_id')
                    ->orderByDesc('total_profit')
                    ->get();

        // Summary keseluruhan
        $summary = [
            'total_revenue' => $report->sum('total_revenue'),
            'total_cogs'    => $report->sum('total_cogs'),
            'total_profit'  => $report->sum('total_profit'),
            'total_qty'     => $report->sum('total_qty'),
        ];

        return view('reports.profit', compact('report', 'summary', 'dari', 'sampai'));
    }
}