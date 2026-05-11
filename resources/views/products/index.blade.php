{{-- resources/views/products/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Manajemen Master Produk')
@section('page-title', 'Manajemen Produk')
@section('page-subtitle', 'Kelola data master barang dan kategori')

@section('content')
<div class="space-y-5">
    <div class="flex flex-col sm:flex-row gap-3 justify-between items-start sm:items-center">
        <form method="GET" action="{{ route('produk.index') }}" class="flex gap-2 flex-1 max-w-lg">
            <div class="relative flex-1">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari barcode atau nama produk..."
                       class="w-full pl-9 pr-4 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-400">
                <svg class="absolute left-2.5 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0118 0z"/>
                </svg>
            </div>
            <button type="submit" class="bg-brand-500 hover:bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-medium">Cari</button>
        </form>
        @if(Auth::user()->hasPermission('crud.create') || session('selected_role') === 'manager' || session('selected_role') === 'tim_it')
        <a href="{{ route('produk.create') }}"
           class="bg-brand-500 hover:bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold flex items-center gap-2 shadow-sm whitespace-nowrap">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Produk Baru
        </a>
        @endif
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Barcode & Produk</th>
                        <th class="px-4 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Kategori</th>
                        <th class="px-4 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Satuan</th>
                        <th class="text-center px-4 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Min. Stok</th>
                        <th class="text-center px-4 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Stok</th>
                        <th class="text-center px-4 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Jumlah Batch</th>
                        <th class="text-center px-4 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($products as $product)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-5 py-3">
                            <p class="text-[10px] font-mono text-gray-400 mb-0.5">{{ $product->barcode ?: 'NO BARCODE' }}</p>
                            <p class="font-bold text-gray-800">{{ $product->name }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded-md text-[10px] font-bold uppercase">{{ $product->category }}</span>
                        </td>
                        <td class="px-4 py-3 text-gray-600 capitalize">{{ $product->unit }}</td>
                        <td class="px-4 py-3 text-center font-medium text-gray-600">{{ $product->min_stock }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2.5 py-1 rounded-lg font-bold {{ $product->total_stock <= $product->min_stock ? 'bg-red-50 text-red-600' : 'bg-brand-50 text-brand-600' }}">
                                {{ $product->total_stock }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('produk.show', $product) }}" class="text-brand-600 hover:underline font-medium">
                                {{ $product->active_batches_count }} Batch Aktif
                            </a>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-1">
                                @if(Auth::user()->hasPermission('crud.read') || session('selected_role') !== 'kasir')
                                <a href="{{ route('produk.show', $product) }}" class="p-1.5 text-brand-500 hover:bg-brand-50 rounded-lg" title="Detail">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                @endif

                                @if(Auth::user()->hasPermission('crud.update') || session('selected_role') === 'manager' || session('selected_role') === 'tim_it')
                                <a href="{{ route('produk.edit', $product) }}" class="p-1.5 text-yellow-500 hover:bg-yellow-50 rounded-lg" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                @endif

                                @if(Auth::user()->hasPermission('crud.delete') || session('selected_role') === 'tim_it')
                                <form method="POST" action="{{ route('produk.destroy', $product) }}" onsubmit="return confirm('Hapus produk ini? Semua data stok terkait akan ikut terhapus.')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-1.5 text-red-400 hover:bg-red-50 rounded-lg" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-5 py-12 text-center text-gray-400 text-sm">Belum ada data produk.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($products->hasPages())
        <div class="px-5 py-4 border-t border-gray-100">{{ $products->withQueryString()->links() }}</div>
        @endif
    </div>
</div>
@endsection
