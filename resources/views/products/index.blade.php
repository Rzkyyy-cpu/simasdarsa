{{-- resources/views/stock/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Manajemen Stok Batch')
@section('page-title', 'Manajemen Stok Batch')
@section('page-subtitle', 'Semua batch stok produk yang tersimpan di sistem')

@section('content')
<div class="space-y-5">
    <div class="flex flex-col sm:flex-row gap-3 justify-between items-start sm:items-center">
        <form method="GET" action="{{ route('stok.index') }}" class="flex gap-2 flex-1 max-w-lg">
            <div class="relative flex-1">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama produk atau kode batch..."
                       class="w-full pl-9 pr-4 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-400">
                <svg class="absolute left-2.5 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0118 0z"/>
                </svg>
            </div>
            <button type="submit" class="bg-brand-500 hover:bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-medium">Cari</button>
        </form>
        <a href="{{ route('stok.create') }}"
           class="bg-brand-500 hover:bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold flex items-center gap-2 shadow-sm whitespace-nowrap">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Batch Stok
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase">Produk</th>
                        <th class="text-left px-4 py-3.5 text-xs font-semibold text-gray-500 uppercase">Kode Batch</th>
                        <th class="text-right px-4 py-3.5 text-xs font-semibold text-gray-500 uppercase">Harga Beli</th>
                        <th class="text-right px-4 py-3.5 text-xs font-semibold text-gray-500 uppercase">Harga Jual</th>
                        <th class="text-center px-4 py-3.5 text-xs font-semibold text-gray-500 uppercase">Stok</th>
                        <th class="text-center px-4 py-3.5 text-xs font-semibold text-gray-500 uppercase">Tgl Expired</th>
                        <th class="text-center px-4 py-3.5 text-xs font-semibold text-gray-500 uppercase">Status</th>
                        <th class="text-center px-4 py-3.5 text-xs font-semibold text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($batches as $batch)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3">
                            <p class="font-medium text-gray-800">{{ $batch->product->name ?? '-' }}</p>
                            <p class="text-xs text-gray-400">{{ $batch->product->category ?? '' }}</p>
                        </td>
                        <td class="px-4 py-3 font-mono text-gray-600 text-xs">{{ $batch->batch_code ?? '-' }}</td>
                        <td class="px-4 py-3 text-right text-gray-600">Rp {{ number_format($batch->buy_price, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right font-semibold">Rp {{ number_format($batch->sell_price, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="font-semibold {{ $batch->current_quantity == 0 ? 'text-gray-300' : 'text-gray-800' }}">
                                {{ $batch->current_quantity }}
                            </span>
                            <span class="text-xs text-gray-400">/ {{ $batch->initial_quantity }}</span>
                        </td>
                        <td class="px-4 py-3 text-center text-sm">
                            {{ $batch->expired_date->format('d/m/Y') }}
                            <span class="block text-xs {{ $batch->days_until_expired < 0 ? 'text-red-500' : 'text-gray-400' }}">
                                {{ $batch->days_until_expired < 0 ? abs($batch->days_until_expired).' hari lalu' : $batch->days_until_expired.' hari lagi' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @switch($batch->expiry_status)
                                @case('expired') <span class="bg-red-100 text-red-700 text-xs px-2 py-1 rounded-full">Expired</span> @break
                                @case('critical') <span class="bg-orange-100 text-orange-700 text-xs px-2 py-1 rounded-full">Kritis</span> @break
                                @case('warning') <span class="bg-yellow-100 text-yellow-700 text-xs px-2 py-1 rounded-full">Peringatan</span> @break
                                @default <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded-full">Aman</span>
                            @endswitch
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('stok.edit', $batch) }}"
                                   class="p-1.5 text-yellow-500 hover:bg-yellow-50 rounded-lg">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                <form method="POST" action="{{ route('stok.destroy', $batch) }}"
                                      onsubmit="return confirm('Hapus batch ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-1.5 text-red-400 hover:bg-red-50 rounded-lg">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="px-5 py-12 text-center text-gray-400 text-sm">Belum ada data batch stok.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($batches->hasPages())
        <div class="px-5 py-4 border-t border-gray-100">{{ $batches->withQueryString()->links() }}</div>
        @endif
    </div>
</div>
@endsection