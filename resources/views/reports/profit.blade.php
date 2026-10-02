{{-- resources/views/reports/profit.blade.php --}}
@extends('layouts.app')
@section('title', 'Laporan Laba Rugi')
@section('page-title', 'Laporan Laba Rugi')
@section('page-subtitle', 'Rekap pendapatan, HPP, dan laba bersih per produk')

@section('content')
<div class="space-y-5">

    {{-- Filter --}}
    <form method="GET" action="{{ route('laporan.laba-rugi') }}"
          class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Dari Tanggal</label>
            <input type="date" name="dari" value="{{ $dari }}"
                   class="border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Sampai Tanggal</label>
            <input type="date" name="sampai" value="{{ $sampai }}"
                   class="border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
        </div>
        <button type="submit" class="bg-brand-500 hover:bg-brand-600 text-white px-5 py-2.5 rounded-xl text-sm font-medium">
            Tampilkan
        </button>
    </form>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
            <p class="text-xs text-gray-400 mb-1">Total Pendapatan</p>
            <p class="text-xl font-bold text-gray-800">Rp {{ number_format($summary['total_revenue'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
            <p class="text-xs text-gray-400 mb-1">Total HPP (Modal)</p>
            <p class="text-xl font-bold text-gray-800">Rp {{ number_format($summary['total_cogs'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-brand-100 p-4 text-center bg-brand-50">
            <p class="text-xs text-brand-500 mb-1">Total Laba Bersih</p>
            <p class="text-xl font-bold text-brand-700">Rp {{ number_format($summary['total_profit'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
            <p class="text-xs text-gray-400 mb-1">Total Item Terjual</p>
            <p class="text-xl font-bold text-gray-800">{{ number_format($summary['total_qty']) }}</p>
        </div>
    </div>

    {{-- Tabel Per Produk --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800">Detail Per Produk</h3>
            <span class="text-xs text-gray-400">{{ $dari }} s/d {{ $sampai }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase">#</th>
                        <th class="text-left px-4 py-3.5 text-xs font-semibold text-gray-500 uppercase">Produk</th>
                        <th class="text-center px-4 py-3.5 text-xs font-semibold text-gray-500 uppercase">Qty Terjual</th>
                        <th class="text-right px-4 py-3.5 text-xs font-semibold text-gray-500 uppercase">Pendapatan</th>
                        <th class="text-right px-4 py-3.5 text-xs font-semibold text-gray-500 uppercase">HPP</th>
                        <th class="text-right px-4 py-3.5 text-xs font-semibold text-gray-500 uppercase">Laba</th>
                        <th class="text-center px-4 py-3.5 text-xs font-semibold text-gray-500 uppercase">Margin</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($report as $index => $item)
                    @php $margin = $item->total_revenue > 0 ? ($item->total_profit / $item->total_revenue * 100) : 0; @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3 text-gray-400 text-xs">{{ $index + 1 }}</td>
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-800">{{ $item->product->name ?? '-' }}</p>
                            <p class="text-xs text-gray-400">{{ $item->product->category ?? '' }}</p>
                        </td>
                        <td class="px-4 py-3 text-center font-medium text-gray-700">
                            {{ number_format($item->total_qty) }}
                            <span class="text-xs text-gray-400">{{ $item->product->unit ?? '' }}</span>
                        </td>
                        <td class="px-4 py-3 text-right text-gray-700">Rp {{ number_format($item->total_revenue, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-gray-500">Rp {{ number_format($item->total_cogs, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right font-semibold {{ $item->total_profit >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            Rp {{ number_format($item->total_profit, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <div class="h-1.5 bg-brand-400 rounded-full" style="width: {{ min(60, $margin) }}px"></div>
                                <span class="text-xs font-medium text-gray-600">{{ number_format($margin, 1) }}%</span>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-5 py-12 text-center text-gray-400 text-sm">Belum ada data penjualan untuk periode ini.</td></tr>
                    @endforelse
                </tbody>
                @if($report->count() > 0)
                <tfoot class="bg-brand-50 border-t-2 border-brand-100">
                    <tr>
                        <td colspan="3" class="px-5 py-3.5 font-bold text-gray-700">TOTAL</td>
                        <td class="px-4 py-3.5 text-right font-bold text-gray-800">Rp {{ number_format($summary['total_revenue'], 0, ',', '.') }}</td>
                        <td class="px-4 py-3.5 text-right font-bold text-gray-600">Rp {{ number_format($summary['total_cogs'], 0, ',', '.') }}</td>
                        <td class="px-4 py-3.5 text-right font-bold text-green-700">Rp {{ number_format($summary['total_profit'], 0, ',', '.') }}</td>
                        <td class="px-4 py-3.5 text-center font-bold text-brand-600">
                            {{ $summary['total_revenue'] > 0 ? number_format($summary['total_profit'] / $summary['total_revenue'] * 100, 1) : 0 }}%
                        </td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection