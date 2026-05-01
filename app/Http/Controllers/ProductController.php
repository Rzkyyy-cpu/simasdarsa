<?php
// ============================================================
// FILE: app/Http/Controllers/ProductController.php
// ============================================================
namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    /** Daftar semua produk dengan filter & pagination */
    public function index(Request $request)
    {
        $products = Product::select('products.*')
            ->selectRaw('
                COALESCE((
                    SELECT SUM(sb.current_quantity)
                    FROM stock_batches sb
                    WHERE sb.product_id = products.id
                      AND sb.current_quantity > 0
                      AND sb.expired_date >= CURDATE()
                ), 0) as total_stock
            ')
            ->selectRaw('
                (SELECT COUNT(*) FROM stock_batches sb2
                 WHERE sb2.product_id = products.id
                   AND sb2.current_quantity > 0
                   AND sb2.expired_date >= CURDATE()) as active_batches_count
            ')
            ->when($request->filled('q'), fn($q) =>
                $q->where(function($inner) use ($request) {
                    $inner->where('products.name', 'like', "%{$request->q}%")
                          ->orWhere('products.barcode', 'like', "%{$request->q}%")
                          ->orWhere('products.category', 'like', "%{$request->q}%");
                })
            )
            ->when($request->filled('kategori'), fn($q) =>
                $q->where('products.category', $request->kategori)
            )
            ->orderBy('products.name')
            ->paginate(20);

        // Ambil daftar kategori untuk filter dropdown
        $categories = Product::distinct()->orderBy('category')->pluck('category');

        return view('products.index', compact('products', 'categories'));
    }

    /** Form tambah produk baru */
    public function create()
    {
        return view('products.create');
    }

    /** Simpan produk baru ke database */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:200',
            'barcode'   => 'nullable|string|max:50|unique:products,barcode',
            'category'  => 'required|string|max:100',
            'unit'      => 'required|string|max:30',
            'min_stock' => 'required|integer|min:0',
        ]);

        Product::create($validated);

        return redirect()->route('produk.index')
                         ->with('success', "Produk '{$validated['name']}' berhasil ditambahkan.");
    }

    /** Detail produk beserta semua batch stok (urut FEFO) */
    public function show(Product $produk)
    {
        $produk->load(['stockBatches' => fn($q) => $q->orderBy('expired_date')]);
        return view('products.show', ['product' => $produk]);
    }

    /** Form edit produk */
    public function edit(Product $produk)
    {
        return view('products.edit', ['product' => $produk]);
    }

    /** Update data produk */
    public function update(Request $request, Product $produk)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:200',
            'barcode'   => "nullable|string|max:50|unique:products,barcode,{$produk->id}",
            'category'  => 'required|string|max:100',
            'unit'      => 'required|string|max:30',
            'min_stock' => 'required|integer|min:0',
        ]);

        $produk->update($validated);

        return redirect()->route('produk.index')
                         ->with('success', "Produk '{$produk->name}' berhasil diperbarui.");
    }

    /** Soft delete produk */
    public function destroy(Product $produk)
    {
        $productName = $produk->name;
        $productId = $produk->id;
        $user = auth()->user();

        $produk->delete();
        Log::info("AUDIT_LOG: PRODUCT DELETED - Product: {$productName} (ID: {$productId}), User: {$user->name} ({$user->email})");
        
        return redirect()->route('produk.index')
                         ->with('success', "Produk '{$productName}' berhasil dihapus.");
    }
}