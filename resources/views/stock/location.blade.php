@extends('layouts.app')
@section('title', 'Status & Lokasi Barang')
@section('page-title', 'Status & Lokasi Barang')
@section('page-subtitle', 'Informasi posisi rak/gudang untuk setiap produk')

@section('content')
<div class="space-y-5">
    <form method="GET" action="{{ url()->current() }}" class="flex gap-2 max-w-lg">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari produk, batch, atau lokasi..."
               class="flex-1 px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-400 focus:outline-none">
        <button type="submit" class="bg-brand-500 text-white px-4 py-2 rounded-xl font-medium">Cari</button>
    </form>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase">Produk</th>
                        <th class="text-left px-4 py-3.5 text-xs font-semibold text-gray-500 uppercase">Kode Batch</th>
                        <th class="text-center px-4 py-3.5 text-xs font-semibold text-gray-500 uppercase">Stok</th>
                        <th class="text-center px-4 py-3.5 text-xs font-semibold text-gray-500 uppercase">Lokasi Rak/Gudang</th>
                        <th class="text-center px-4 py-3.5 text-xs font-semibold text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($batches as $batch)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3 font-medium text-gray-800">{{ $batch->product->name ?? '-' }}</td>
                        <td class="px-4 py-3 font-mono text-gray-600 text-xs">{{ $batch->batch_code ?? '-' }}</td>
                        <td class="px-4 py-3 text-center font-semibold text-gray-800">{{ $batch->current_quantity }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($batch->location)
                                <span class="bg-blue-50 text-blue-700 px-3 py-1 rounded-lg font-medium">{{ $batch->location }}</span>
                            @else
                                <span class="text-gray-400 italic text-xs">Belum diatur</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($batch->is_verified)
                                <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded-full">Terverifikasi</span>
                            @else
                                <span class="bg-yellow-100 text-yellow-700 text-xs px-2 py-1 rounded-full">Pending</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-5 py-12 text-center text-gray-400 text-sm">Data tidak ditemukan.</td></tr>
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
