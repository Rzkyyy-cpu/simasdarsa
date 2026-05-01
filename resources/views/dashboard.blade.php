@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Ringkasan operasional toko hari ini')

@section('content')

{{-- ============================================================ --}}
{{-- SECTION 1: KARTU STATISTIK UTAMA --}}
{{-- ============================================================ --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">

    {{-- Penjualan Hari Ini --}}
    <div class="stat-card bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Transaksi Hari Ini</p>
                <p class="text-3xl font-bold text-gray-800 mt-1">{{ number_format($todaySales) }}</p>
                <p class="text-xs text-gray-400 mt-1">transaksi</p>
            </div>
            <div class="w-12 h-12 bg-brand-50 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 3h2l.4 2M7 13h10l4-8H5.4"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- Pendapatan Hari Ini --}}
    <div class="stat-card bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Pendapatan Hari Ini</p>
                <p class="text-2xl font-bold text-gray-800 mt-1">Rp {{ number_format($todayRevenue, 0, ',', '.') }}</p>
                <p class="text-xs text-green-500 mt-1 font-medium">{{-- Laba: Rp {{ number_format($todayProfit, 0, ',', '.') }} --}}</p>
            </div>
            <div class="w-12 h-12 bg-green-50 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- Alert Expired --}}
    <div class="stat-card bg-white rounded-2xl p-5 shadow-sm border border-gray-100
                {{ $expiringBatches->count() > 0 ? 'border-warning-200 bg-warning-50' : '' }}">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Akan Kedaluwarsa</p>
                <p class="text-3xl font-bold {{ $expiringBatches->count() > 0 ? 'text-warning-600' : 'text-gray-800' }} mt-1">
                    {{ $expiringBatches->count() }}
                </p>
                <p class="text-xs text-gray-400 mt-1">batch dalam 30 hari</p>
            </div>
            <div class="w-12 h-12 {{ $expiringBatches->count() > 0 ? 'bg-warning-100' : 'bg-orange-50' }} rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 {{ $expiringBatches->count() > 0 ? 'text-warning-500 badge-pulse' : 'text-orange-400' }}"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- Stok Kritis --}}
    <div class="stat-card bg-white rounded-2xl p-5 shadow-sm border border-gray-100
                {{ $criticalProducts->count() > 0 ? 'border-danger-200 bg-danger-50' : '' }}">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Stok Kritis</p>
                <p class="text-3xl font-bold {{ $criticalProducts->count() > 0 ? 'text-danger-600' : 'text-gray-800' }} mt-1">
                    {{ $criticalProducts->count() }}
                </p>
                <p class="text-xs text-gray-400 mt-1">produk di bawah minimum</p>
            </div>
            <div class="w-12 h-12 {{ $criticalProducts->count() > 0 ? 'bg-danger-100' : 'bg-red-50' }} rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 {{ $criticalProducts->count() > 0 ? 'text-danger-500 badge-pulse' : 'text-red-400' }}"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
            </div>
        </div>
    </div>
</div>

{{-- ============================================================ --}}
{{-- SECTION 2: ALERT PANEL --}}
{{-- ============================================================ --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-6">

    {{-- ALERT: Produk Akan Kedaluwarsa --}}
    @if($expiringBatches->count() > 0 || $expiredWithStock->count() > 0)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 bg-warning-50 border-b border-warning-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-warning-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h3 class="font-semibold text-warning-800 text-sm">⚠️ Alert Kedaluwarsa</h3>
            </div>
            <a href="{{ route('stok.expiry-monitor') }}"
               class="text-xs text-warning-600 hover:text-warning-800 font-medium">Lihat Semua →</a>
        </div>
        <div class="divide-y divide-gray-50 max-h-72 overflow-y-auto">
            {{-- Batch sudah expired --}}
            @foreach($expiredWithStock->take(3) as $batch)
            <div class="px-5 py-3 flex items-center justify-between bg-danger-50">
                <div>
                    <p class="text-sm font-medium text-danger-800">{{ $batch->product->name }}</p>
                    <p class="text-xs text-danger-500">
                        Kedaluwarsa: {{ $batch->expired_date->format('d/m/Y') }}
                        <span class="font-bold">(SUDAH EXPIRED)</span>
                    </p>
                </div>
                <div class="text-right">
                    <span class="inline-block bg-danger-100 text-danger-700 text-xs px-2 py-1 rounded-full font-semibold">
                        Sisa {{ $batch->current_quantity }} {{ $batch->product->unit }}
                    </span>
                </div>
            </div>
            @endforeach

            {{-- Batch akan expired --}}
            @foreach($expiringBatches->take(5) as $batch)
            <div class="px-5 py-3 flex items-center justify-between hover:bg-gray-50">
                <div>
                    <p class="text-sm font-medium text-gray-700">{{ $batch->product->name }}</p>
                    <p class="text-xs text-gray-400">
                        Exp: {{ $batch->expired_date->format('d/m/Y') }}
                        &bull; {{ $batch->days_until_expired }} hari lagi
                    </p>
                </div>
                <div class="text-right">
                    @php
                        $days = $batch->days_until_expired;
                        $badgeClass = $days <= 7 ? 'bg-danger-100 text-danger-700' : 'bg-warning-100 text-warning-700';
                    @endphp
                    <span class="inline-block {{ $badgeClass }} text-xs px-2 py-1 rounded-full font-semibold">
                        {{ $batch->current_quantity }} {{ $batch->product->unit }}
                    </span>
                </div>
            </div>
            @endforeach

            @if($expiringBatches->isEmpty() && $expiredWithStock->isEmpty())
            <div class="px-5 py-6 text-center text-sm text-gray-400">
                ✅ Tidak ada produk yang akan kedaluwarsa
            </div>
            @endif
        </div>
    </div>
    @else
    <div class="bg-green-50 rounded-2xl shadow-sm border border-green-100 flex items-center justify-center p-8">
        <div class="text-center">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <p class="font-semibold text-green-700">Tidak Ada Produk Akan Expired</p>
            <p class="text-sm text-green-500 mt-1">Semua batch stok dalam kondisi aman</p>
        </div>
    </div>
    @endif

    {{-- ALERT: Stok Kritis --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 bg-red-50 border-b border-red-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h3 class="font-semibold text-red-800 text-sm">🚨 Stok Kritis</h3>
            </div>
            <a href="{{ route('produk.index') }}?filter=critical"
               class="text-xs text-red-600 hover:text-red-800 font-medium">Lihat Semua →</a>
        </div>
        <div class="divide-y divide-gray-50 max-h-72 overflow-y-auto">
            @forelse($criticalProducts as $product)
            <div class="px-5 py-3 flex items-center justify-between hover:bg-gray-50">
                <div>
                    <p class="text-sm font-medium text-gray-700">{{ $product->name }}</p>
                    <p class="text-xs text-gray-400">
                        Min: {{ $product->min_stock }} {{ $product->unit }}
                    </p>
                </div>
                <div class="text-right">
                    <span class="inline-block bg-red-100 text-red-700 text-xs px-2 py-1 rounded-full font-bold">
                        Sisa {{ $product->total_stock }} {{ $product->unit }}
                    </span>
                </div>
            </div>
            @empty
            <div class="px-5 py-6 text-center text-sm text-gray-400">
                ✅ Semua produk memiliki stok yang cukup
            </div>
            @endforelse
        </div>
    </div>
</div>

{{-- ============================================================ --}}
{{-- SECTION 3: GRAFIK & TOP PRODUK --}}
{{-- ============================================================ --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- Grafik Penjualan 7 Hari --}}
    <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-800">Grafik Penjualan 7 Hari Terakhir</h3>
            <span class="text-xs text-gray-400 bg-gray-100 px-2 py-1 rounded-full">
                Bulan ini: Rp {{ number_format($monthRevenue, 0, ',', '.') }}
            </span>
        </div>
        <canvas id="salesChart" height="120"></canvas>
    </div>

    {{-- Top 5 Produk Terlaris --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <h3 class="font-semibold text-gray-800 mb-4">🏆 Top 5 Terlaris Bulan Ini</h3>
        <div class="space-y-3">
            @forelse($topProducts as $index => $item)
            <div class="flex items-center gap-3">
                <span class="w-6 h-6 rounded-full text-xs font-bold flex items-center justify-center flex-shrink-0
                             {{ $index === 0 ? 'bg-yellow-400 text-white' : ($index === 1 ? 'bg-gray-300 text-white' : 'bg-orange-300 text-white') }}">
                    {{ $index + 1 }}
                </span>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-700 truncate">{{ $item->product->name ?? '-' }}</p>
                    <div class="flex items-center gap-1 mt-0.5">
                        <div class="h-1.5 bg-brand-400 rounded-full"
                             style="width: {{ min(100, ($item->total_terjual / ($topProducts->first()->total_terjual ?? 1)) * 100) }}%">
                        </div>
                    </div>
                </div>
                <span class="text-xs font-bold text-brand-600 flex-shrink-0">{{ number_format($item->total_terjual) }} pcs</span>
            </div>
            @empty
            <p class="text-sm text-gray-400 text-center py-4">Belum ada data penjualan bulan ini</p>
            @endforelse
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Data grafik dari Laravel (PHP → JS)
const weeklyData = @json($weeklyChart);

const labels = weeklyData.map(d => {
    const date = new Date(d.tanggal);
    return date.toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric', month: 'short' });
});

const revenues = weeklyData.map(d => parseFloat(d.total) || 0);
const transactions = weeklyData.map(d => parseInt(d.jumlah_transaksi) || 0);

// Render grafik
const ctx = document.getElementById('salesChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: labels,
        datasets: [
            {
                label: 'Pendapatan (Rp)',
                data: revenues,
                backgroundColor: 'rgba(20, 184, 166, 0.2)',
                borderColor: 'rgba(20, 184, 166, 1)',
                borderWidth: 2,
                borderRadius: 6,
                yAxisID: 'y',
            },
            {
                label: 'Jumlah Transaksi',
                data: transactions,
                type: 'line',
                borderColor: '#f59e0b',
                backgroundColor: 'rgba(245, 158, 11, 0.1)',
                borderWidth: 2,
                pointBackgroundColor: '#f59e0b',
                pointRadius: 4,
                tension: 0.4,
                yAxisID: 'y1',
            }
        ]
    },
    options: {
        responsive: true,
        interaction: { mode: 'index', intersect: false },
        plugins: {
            legend: { position: 'top', labels: { font: { size: 11 } } },
            tooltip: {
                callbacks: {
                    label: (ctx) => {
                        if (ctx.datasetIndex === 0) {
                            return ' Rp ' + new Intl.NumberFormat('id-ID').format(ctx.raw);
                        }
                        return ' ' + ctx.raw + ' transaksi';
                    }
                }
            }
        },
        scales: {
            y: {
                type: 'linear',
                position: 'left',
                ticks: {
                    callback: (val) => 'Rp ' + new Intl.NumberFormat('id-ID').format(val),
                    font: { size: 10 }
                },
                grid: { color: 'rgba(0,0,0,0.05)' }
            },
            y1: {
                type: 'linear',
                position: 'right',
                grid: { drawOnChartArea: false },
                ticks: { font: { size: 10 } }
            }
        }
    }
});
</script>
@endpush