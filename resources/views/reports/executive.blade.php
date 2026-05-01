@extends('layouts.app')
@section('title', 'Laporan Eksekutif & Stok Kritis')
@section('page-title', 'Laporan Eksekutif')
@section('page-subtitle', 'Ringkasan finansial dan pengawasan stok kritis')

@section('content')
<div class="space-y-6">
    {{-- Filter & Export --}}
    <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4">
        <form method="GET" action="{{ route('laporan.eksekutif') }}" class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2">
                <label class="text-xs font-bold text-gray-400 uppercase">Dari:</label>
                <input type="date" name="start_date" value="{{ $startDate }}" 
                       class="border border-gray-200 rounded-xl px-3 py-1.5 text-sm focus:ring-2 focus:ring-brand-400 focus:outline-none">
            </div>
            <div class="flex items-center gap-2">
                <label class="text-xs font-bold text-gray-400 uppercase">S/D:</label>
                <input type="date" name="end_date" value="{{ $endDate }}"
                       class="border border-gray-200 rounded-xl px-3 py-1.5 text-sm focus:ring-2 focus:ring-brand-400 focus:outline-none">
            </div>
            <button type="submit" class="bg-brand-500 hover:bg-brand-600 text-white px-4 py-1.5 rounded-xl text-sm font-bold transition-colors">
                Filter
            </button>
        </form>

        <a href="{{ request()->fullUrlWithQuery(['export' => 1]) }}" 
           class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-xl text-sm font-bold flex items-center gap-2 shadow-sm transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Export CSV
        </a>
    </div>

    {{-- Financial Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm">
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Total Transaksi</p>
            <h3 class="text-3xl font-black text-gray-800">{{ number_format($summary->total_transactions ?? 0, 0, ',', '.') }}</h3>
            <p class="text-xs text-gray-400 mt-1 italic">Periode terpilih</p>
        </div>
        <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm">
            <p class="text-[10px] font-black text-brand-400 uppercase tracking-widest mb-1">Total Pendapatan</p>
            <h3 class="text-3xl font-black text-brand-600">Rp {{ number_format($summary->total_revenue ?? 0, 0, ',', '.') }}</h3>
            <p class="text-xs text-gray-400 mt-1 italic">Gross Revenue</p>
        </div>
        <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm">
            <p class="text-[10px] font-black text-green-400 uppercase tracking-widest mb-1">Estimasi Laba Kotor</p>
            <h3 class="text-3xl font-black text-green-600">Rp {{ number_format($summary->total_profit ?? 0, 0, ',', '.') }}</h3>
            <p class="text-xs text-gray-400 mt-1 italic">Revenue - COGS (HPP)</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Critical Stock Table --}}
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-gray-50 bg-gray-50/50">
                <h3 class="font-black text-gray-800 flex items-center gap-2 text-sm uppercase tracking-wider">
                    <span class="w-1.5 h-5 bg-red-500 rounded-full"></span>
                    Daftar Stok Kritis
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-50">
                            <th class="px-6 py-4">Produk</th>
                            <th class="px-6 py-4 text-center">Stok</th>
                            <th class="px-6 py-4 text-center">Min</th>
                            <th class="px-6 py-4 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($criticalProducts as $product)
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-6 py-4">
                                <p class="font-bold text-gray-800">{{ $product->name }}</p>
                                <p class="text-[10px] text-gray-400 uppercase">{{ $product->category }}</p>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="font-black text-red-600 text-lg">{{ $product->total_stock }}</span>
                                <span class="text-[10px] text-gray-400 uppercase">{{ $product->unit }}</span>
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-gray-400">{{ $product->min_stock }}</td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2 py-1 rounded-lg text-[9px] font-black uppercase {{ $product->total_stock <= 0 ? 'bg-black text-white' : 'bg-red-100 text-red-600' }}">
                                    {{ $product->total_stock <= 0 ? 'HABIS' : 'KRITIS' }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="px-6 py-10 text-center text-gray-400 italic">Tidak ada stok kritis saat ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Expiring Soon Table --}}
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-gray-50 bg-gray-50/50">
                <h3 class="font-black text-gray-800 flex items-center gap-2 text-sm uppercase tracking-wider">
                    <span class="w-1.5 h-5 bg-orange-500 rounded-full"></span>
                    Segera Kedaluwarsa (30 Hari)
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-50">
                            <th class="px-6 py-4">Produk & Batch</th>
                            <th class="px-6 py-4 text-center">Tgl Expired</th>
                            <th class="px-6 py-4 text-center">Sisa Hari</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($expiringBatches as $batch)
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-6 py-4">
                                <p class="font-bold text-gray-800">{{ $batch->product->name }}</p>
                                <p class="text-[10px] font-mono text-gray-400">BATCH: {{ $batch->batch_code ?: '-' }}</p>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <p class="font-bold text-gray-700">{{ $batch->expired_date->format('d/m/Y') }}</p>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-3 py-1 bg-orange-100 text-orange-600 rounded-lg text-[10px] font-black uppercase">
                                    {{ $batch->days_until_expired }} HARI LAGI
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="px-6 py-10 text-center text-gray-400 italic">Tidak ada batch yang segera kedaluwarsa.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
