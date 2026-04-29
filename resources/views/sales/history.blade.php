{{-- resources/views/sales/history.blade.php --}}
@extends('layouts.app')
@section('title', 'Riwayat Penjualan')
@section('page-title', 'Riwayat Penjualan')
@section('page-subtitle', 'Semua transaksi penjualan yang telah diproses')

@section('content')
<div class="space-y-5">

    {{-- Filter Tanggal --}}
    <form method="GET" action="{{ route('penjualan.index') }}"
          class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Dari Tanggal</label>
            <input type="date" name="dari" value="{{ request('dari', now()->startOfMonth()->toDateString()) }}"
                   class="border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Sampai Tanggal</label>
            <input type="date" name="sampai" value="{{ request('sampai', now()->toDateString()) }}"
                   class="border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
        </div>
        <button type="submit" class="bg-brand-500 hover:bg-brand-600 text-white px-5 py-2.5 rounded-xl text-sm font-medium">
            Tampilkan
        </button>
        <a href="{{ route('penjualan.index') }}" class="text-sm text-gray-500 hover:text-gray-700 py-2.5">Reset</a>
    </form>

    {{-- Tabel Riwayat --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase">No. Invoice</th>
                        <th class="text-left px-4 py-3.5 text-xs font-semibold text-gray-500 uppercase">Waktu</th>
                        <th class="text-center px-4 py-3.5 text-xs font-semibold text-gray-500 uppercase">Item</th>
                        <th class="text-right px-4 py-3.5 text-xs font-semibold text-gray-500 uppercase">Total</th>
                        <th class="text-right px-4 py-3.5 text-xs font-semibold text-gray-500 uppercase">Bayar</th>
                        <th class="text-right px-4 py-3.5 text-xs font-semibold text-gray-500 uppercase">Kembalian</th>
                        <th class="text-center px-4 py-3.5 text-xs font-semibold text-gray-500 uppercase">Kasir</th>
                        <th class="text-center px-4 py-3.5 text-xs font-semibold text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($sales as $sale)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3">
                            <span class="font-mono text-xs text-brand-600 font-semibold">{{ $sale->invoice_number }}</span>
                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            <p>{{ $sale->sale_date->format('d/m/Y') }}</p>
                            <p class="text-xs text-gray-400">{{ $sale->sale_date->format('H:i') }}</p>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="bg-gray-100 text-gray-700 text-xs px-2 py-1 rounded-full">
                                {{ $sale->details->count() }} item
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right font-semibold text-gray-800">
                            Rp {{ number_format($sale->total_amount, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right text-gray-600">
                            Rp {{ number_format($sale->total_payment, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right text-green-600 font-medium">
                            Rp {{ number_format($sale->change_amount, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-center text-gray-500 text-xs">{{ $sale->cashier ?? '-' }}</td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('penjualan.show', $sale) }}"
                               class="text-brand-500 hover:text-brand-700 text-xs font-medium">Lihat Detail</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="px-5 py-12 text-center text-gray-400 text-sm">Tidak ada data penjualan pada periode ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($sales->hasPages())
        <div class="px-5 py-4 border-t border-gray-100">{{ $sales->withQueryString()->links() }}</div>
        @endif
    </div>
</div>
@endsection