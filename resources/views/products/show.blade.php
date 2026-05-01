{{-- resources/views/products/show.blade.php --}}
@extends('layouts.app')
@section('title', 'Detail Produk & Batch')
@section('page-title', 'Detail Produk')
@section('page-subtitle', $product->name)

@section('content')
<div class="space-y-6">
    {{-- Info Card --}}
    <div class="bg-white rounded-3xl shadow-xl shadow-gray-200/50 border border-gray-100 overflow-hidden">
        <div class="p-8">
            <div class="flex flex-col md:flex-row justify-between gap-6">
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-brand-100 text-brand-600 rounded-2xl flex items-center justify-center text-xl font-black">
                            {{ substr($product->name, 0, 1) }}
                        </div>
                        <div>
                            <h2 class="text-2xl font-black text-gray-800 tracking-tight">{{ $product->name }}</h2>
                            <p class="text-xs font-mono text-gray-400 uppercase tracking-widest">Barcode: {{ $product->barcode ?: '-' }}</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <span class="px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-[10px] font-black uppercase tracking-wider">{{ $product->category }}</span>
                        <span class="px-3 py-1 bg-brand-50 text-brand-600 rounded-full text-[10px] font-black uppercase tracking-wider">Satuan: {{ $product->unit }}</span>
                    </div>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('produk.edit', $product) }}" class="px-6 py-3 bg-white border border-gray-200 rounded-2xl font-bold text-sm text-gray-600 hover:bg-gray-50 transition-all flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Edit Produk
                    </a>
                </div>
            </div>
        </div>
        <div class="bg-gray-50/50 p-8 border-t border-gray-100">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Total Stok Saat Ini</p>
                    <p class="text-3xl font-black text-brand-600">{{ $product->stockBatches->sum('current_quantity') }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Stok Minimum</p>
                    <p class="text-3xl font-black text-gray-800">{{ $product->min_stock }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Batch Aktif</p>
                    <p class="text-3xl font-black text-gray-800">{{ $product->stockBatches->where('current_quantity', '>', 0)->count() }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Status Inventori</p>
                    @php $total = $product->stockBatches->sum('current_quantity'); @endphp
                    <span class="inline-block px-4 py-1.5 rounded-xl text-xs font-black uppercase tracking-widest
                        {{ $total <= $product->min_stock ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-600' }}">
                        {{ $total <= $product->min_stock ? 'STOK RENDAH' : 'STOK AMAN' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Batches List --}}
    <div class="bg-white rounded-3xl shadow-xl shadow-gray-200/50 border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-50 flex items-center justify-between bg-gray-50/50">
            <h3 class="font-black text-gray-800 flex items-center gap-2">
                <span class="w-1.5 h-6 bg-brand-500 rounded-full"></span>
                Daftar Batch Stok (FEFO)
            </h3>
            <a href="{{ route('stok.create', ['product_id' => $product->id]) }}" class="text-sm font-bold text-brand-600 hover:text-brand-800">+ Tambah Batch</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-[10px] uppercase tracking-widest text-gray-400 font-black border-b border-gray-100">
                        <th class="px-6 py-4">Kode Batch</th>
                        <th class="px-6 py-4">Harga Beli/Jual</th>
                        <th class="px-6 py-4 text-center">Stok Sisa</th>
                        <th class="px-6 py-4 text-center">Tgl Kedaluwarsa</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($product->stockBatches->sortBy('expired_date') as $batch)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-4">
                            <span class="font-mono text-sm font-bold text-gray-600">{{ $batch->batch_code }}</span>
                            <p class="text-[10px] text-gray-400 mt-1">Diterima: {{ $batch->received_date->format('d M Y') }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-xs text-gray-400">Beli: Rp {{ number_format($batch->buy_price, 0, ',', '.') }}</p>
                            <p class="text-sm font-black text-gray-800">Jual: Rp {{ number_format($batch->sell_price, 0, ',', '.') }}</p>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="text-lg font-black {{ $batch->current_quantity <= 10 ? 'text-red-500' : 'text-gray-800' }}">
                                {{ $batch->current_quantity }}
                            </span>
                            <span class="text-xs text-gray-400">/ {{ $batch->initial_quantity }}</span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="text-sm font-bold text-gray-700">{{ $batch->expired_date->format('d M Y') }}</span>
                            <span class="block text-[10px] font-black uppercase tracking-tighter {{ $batch->days_until_expired <= 30 ? 'text-red-500' : 'text-gray-400' }}">
                                {{ $batch->days_until_expired < 0 ? 'SUDAH EXPIRED' : $batch->days_until_expired . ' HARI LAGI' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @switch($batch->expiry_status)
                                @case('expired') <span class="px-3 py-1 bg-red-100 text-red-600 rounded-lg text-[10px] font-black uppercase">Expired</span> @break
                                @case('critical') <span class="px-3 py-1 bg-orange-100 text-orange-600 rounded-lg text-[10px] font-black uppercase">Kritis</span> @break
                                @case('warning') <span class="px-3 py-1 bg-yellow-100 text-yellow-600 rounded-lg text-[10px] font-black uppercase">Segera Expired</span> @break
                                @default <span class="px-3 py-1 bg-green-100 text-green-600 rounded-lg text-[10px] font-black uppercase">Aman</span>
                            @endswitch
                        </td>
                        <td class="px-6 py-4 text-center">
                            <a href="{{ route('stok.edit', $batch) }}" class="text-blue-500 hover:text-blue-700 font-bold text-xs uppercase">Edit</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-6 py-12 text-center text-gray-400 text-sm italic">Belum ada batch stok untuk produk ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <a href="{{ route('produk.index') }}" class="inline-flex items-center gap-2 text-sm font-black text-gray-400 hover:text-brand-600 uppercase tracking-widest transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Kembali ke Daftar Produk
    </a>
</div>
@endsection
