<?php
// ============================================================
// FILE: app/Http/Controllers/StockBatchController.php
// ============================================================
namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockBatch;
use Illuminate\Http\Request;

class StockBatchController extends Controller
{
    /** Daftar semua batch stok dengan pagination */
    public function index(Request $request)
    {
        $batches = StockBatch::with('product:id,name,category,unit')
            ->when($request->filled('q'), fn($q) =>
                $q->whereHas('product', fn($inner) =>
                    $inner->where('name', 'like', "%{$request->q}%")
                )->orWhere('batch_code', 'like', "%{$request->q}%")
            )
            ->orderBy('expired_date', 'asc')
            ->paginate(25);

        return view('stock.index', compact('batches'));
    }

    /** Form tambah batch stok baru */
    public function create()
    {
        $products = Product::orderBy('name')->get(['id', 'name', 'category']);
        return view('stock.create', compact('products'));
    }

    /** Simpan batch stok baru */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id'    => 'required|exists:products,id',
            'batch_code'    => 'nullable|string|max:50',
            'buy_price'     => 'required|numeric|min:0',
            'sell_price'    => 'required|numeric|min:0',
            'quantity'      => 'required|integer|min:1',
            'expired_date'  => 'required|date',
            'received_date' => 'nullable|date',
        ]);

        StockBatch::create([
            'product_id'       => $validated['product_id'],
            'batch_code'       => $validated['batch_code'],
            'buy_price'        => $validated['buy_price'],
            'sell_price'       => $validated['sell_price'],
            'initial_quantity' => $validated['quantity'],
            'current_quantity' => $validated['quantity'],
            'expired_date'     => $validated['expired_date'],
            'received_date'    => $validated['received_date'] ?? today(),
        ]);

        return redirect()->route('stok.index')
                         ->with('success', 'Batch stok berhasil ditambahkan.');
    }

    /** Form edit batch */
    public function edit(StockBatch $batch)
    {
        $batch->load('product:id,name');
        return view('stock.edit', compact('batch'));
    }

    /** Update batch stok */
    public function update(Request $request, StockBatch $batch)
    {
        $validated = $request->validate([
            'batch_code'       => 'nullable|string|max:50',
            'buy_price'        => 'required|numeric|min:0',
            'sell_price'       => 'required|numeric|min:0',
            'current_quantity' => 'required|integer|min:0',
            'expired_date'     => 'required|date',
            'received_date'    => 'nullable|date',
        ]);

        $batch->update($validated);

        return redirect()->route('stok.index')
                         ->with('success', 'Batch stok berhasil diperbarui.');
    }

    /** Hapus batch stok (hanya jika belum ada transaksi) */
    public function destroy(StockBatch $batch)
    {
        // Cek apakah batch ini sudah pernah digunakan di transaksi
        if ($batch->saleDetails()->exists()) {
            return back()->with('error', 'Batch ini tidak dapat dihapus karena sudah ada transaksi penjualan.');
        }

        $batch->delete();
        return redirect()->route('stok.index')
                         ->with('success', 'Batch stok berhasil dihapus.');
    }

    /**
     * Halaman monitoring kedaluwarsa dengan filter status.
     * GET /stok/monitoring-expired?status=expired|critical|warning|safe
     */
    public function expiryMonitor(Request $request)
    {
        $status = $request->get('status', 'all');

        // Hitung jumlah per status untuk kartu ringkasan
        $expiredCount  = StockBatch::where('current_quantity', '>', 0)
                                   ->where('expired_date', '<', now()->toDateString())->count();
        $criticalCount = StockBatch::where('current_quantity', '>', 0)
                                   ->whereBetween('expired_date', [now()->toDateString(), now()->addDays(7)->toDateString()])->count();
        $warningCount  = StockBatch::where('current_quantity', '>', 0)
                                   ->whereBetween('expired_date', [now()->addDays(8)->toDateString(), now()->addDays(30)->toDateString()])->count();
        $safeCount     = StockBatch::where('current_quantity', '>', 0)
                                   ->where('expired_date', '>', now()->addDays(30)->toDateString())->count();

        // Query sesuai filter status
        $query = StockBatch::with('product:id,name,category,unit')
                           ->where('current_quantity', '>', 0);

        $query = match($status) {
            'expired'  => $query->where('expired_date', '<', now()->toDateString()),
            'critical' => $query->whereBetween('expired_date', [now()->toDateString(), now()->addDays(7)->toDateString()]),
            'warning'  => $query->whereBetween('expired_date', [now()->addDays(8)->toDateString(), now()->addDays(30)->toDateString()]),
            'safe'     => $query->where('expired_date', '>', now()->addDays(30)->toDateString()),
            default    => $query, // 'all' = tampilkan semua
        };

        $batches = $query->orderBy('expired_date', 'asc')->paginate(30);

        return view('stock.expiry_monitor', compact(
            'batches', 'status',
            'expiredCount', 'criticalCount', 'warningCount', 'safeCount'
        ));
    }
}