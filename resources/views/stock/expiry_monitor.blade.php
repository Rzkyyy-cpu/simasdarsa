{{-- resources/views/stock/expiry_monitor.blade.php --}}
@extends('layouts.app')
@section('title', 'Monitoring Kedaluwarsa')
@section('page-title', 'Monitoring Kedaluwarsa')
@section('page-subtitle', 'Pantau semua batch produk berdasarkan status kedaluwarsa')

@section('content')
<div class="space-y-5">

    {{-- Ringkasan --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-red-50 border border-red-100 rounded-xl p-4 text-center">
            <p class="text-2xl font-bold text-red-600">{{ $expiredCount }}</p>
            <p class="text-xs text-red-500 mt-1">Sudah Expired (ada stok)</p>
        </div>
        <div class="bg-orange-50 border border-orange-100 rounded-xl p-4 text-center">
            <p class="text-2xl font-bold text-orange-600">{{ $criticalCount }}</p>
            <p class="text-xs text-orange-500 mt-1">Kritis (≤ 7 hari)</p>
        </div>
        <div class="bg-yellow-50 border border-yellow-100 rounded-xl p-4 text-center">
            <p class="text-2xl font-bold text-yellow-600">{{ $warningCount }}</p>
            <p class="text-xs text-yellow-600 mt-1">Peringatan (≤ 30 hari)</p>
        </div>
        <div class="bg-green-50 border border-green-100 rounded-xl p-4 text-center">
            <p class="text-2xl font-bold text-green-600">{{ $safeCount }}</p>
            <p class="text-xs text-green-600 mt-1">Aman (&gt; 30 hari)</p>
        </div>
    </div>

    {{-- Filter --}}
    <form method="GET" action="{{ route('stok.expiry-monitor') }}" class="flex gap-2 flex-wrap">
        @foreach(['all' => 'Semua', 'expired' => '🔴 Expired', 'critical' => '🟠 Kritis', 'warning' => '🟡 Peringatan', 'safe' => '🟢 Aman'] as $val => $label)
            <a href="{{ route('stok.expiry-monitor') }}?status={{ $val }}"
               class="px-4 py-2 rounded-xl text-sm font-medium border transition-colors
                      {{ request('status', 'all') == $val
                         ? 'bg-brand-500 text-white border-brand-500'
                         : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50' }}">
                {{ $label }}
            </a>
        @endforeach
    </form>

    {{-- Tabel --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase">Produk</th>
                        <th class="text-left px-4 py-3.5 text-xs font-semibold text-gray-500 uppercase">Batch</th>
                        <th class="text-center px-4 py-3.5 text-xs font-semibold text-gray-500 uppercase">Stok</th>
                        <th class="text-center px-4 py-3.5 text-xs font-semibold text-gray-500 uppercase">Tgl Expired</th>
                        <th class="text-center px-4 py-3.5 text-xs font-semibold text-gray-500 uppercase">Sisa Hari</th>
                        <th class="text-center px-4 py-3.5 text-xs font-semibold text-gray-500 uppercase">Status</th>
                        <th class="text-right px-4 py-3.5 text-xs font-semibold text-gray-500 uppercase">Nilai Stok</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($batches as $batch)
                    @php
                        $rowClass = match($batch->expiry_status) {
                            'expired'  => 'bg-red-50/60',
                            'critical' => 'bg-orange-50/50',
                            'warning'  => 'bg-yellow-50/30',
                            default    => '',
                        };
                    @endphp
                    <tr class="hover:bg-gray-50 {{ $rowClass }}">
                        <td class="px-5 py-3">
                            <p class="font-medium text-gray-800">{{ $batch->product->name ?? '-' }}</p>
                            <p class="text-xs text-gray-400">{{ $batch->product->category ?? '' }}</p>
                        </td>
                        <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ $batch->batch_code ?? '-' }}</td>
                        <td class="px-4 py-3 text-center font-semibold text-gray-800">
                            {{ $batch->current_quantity }}
                            <span class="text-xs text-gray-400">{{ $batch->product->unit ?? '' }}</span>
                        </td>
                        <td class="px-4 py-3 text-center font-medium
                                   {{ $batch->expiry_status == 'expired' ? 'text-red-600' : 'text-gray-700' }}">
                            {{ $batch->expired_date->format('d/m/Y') }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($batch->days_until_expired < 0)
                                <span class="font-bold text-red-600">{{ abs($batch->days_until_expired) }} hari lalu</span>
                            @else
                                <span class="{{ $batch->days_until_expired <= 7 ? 'font-bold text-orange-600' : 'text-gray-600' }}">
                                    {{ $batch->days_until_expired }} hari
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @switch($batch->expiry_status)
                                @case('expired') <span class="bg-red-100 text-red-700 text-xs px-2.5 py-1 rounded-full font-semibold">EXPIRED</span> @break
                                @case('critical') <span class="bg-orange-100 text-orange-700 text-xs px-2.5 py-1 rounded-full font-semibold">KRITIS</span> @break
                                @case('warning') <span class="bg-yellow-100 text-yellow-700 text-xs px-2.5 py-1 rounded-full font-medium">Peringatan</span> @break
                                @default <span class="bg-green-100 text-green-700 text-xs px-2.5 py-1 rounded-full">Aman</span>
                            @endswitch
                        </td>
                        <td class="px-4 py-3 text-right text-gray-600 text-xs">
                            Rp {{ number_format($batch->buy_price * $batch->current_quantity, 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center text-gray-400">
                            <svg class="w-12 h-12 mx-auto text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="font-medium">Tidak ada batch dengan status ini</p>
                        </td>
                    </tr>
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