{{-- resources/views/sales/show.blade.php --}}
@extends('layouts.app')
@section('title', 'Detail Transaksi')
@section('page-title', 'Detail Transaksi')
@section('page-subtitle', 'Invoice: ' . $sale->invoice_number)

@section('content')
<div class="max-w-3xl space-y-5">

    {{-- Info Header --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
            <div>
                <p class="text-xs text-gray-400 mb-0.5">Invoice</p>
                <p class="font-mono font-semibold text-brand-600">{{ $sale->invoice_number }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-0.5">Waktu Transaksi</p>
                <p class="font-medium">{{ $sale->sale_date->format('d/m/Y H:i') }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-0.5">Kasir</p>
                <p class="font-medium">{{ $sale->cashier ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-0.5">Laba Transaksi</p>
                <p class="font-semibold text-green-600">Rp {{ number_format($sale->total_profit, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    {{-- Detail Item --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">Item yang Terjual</h3>
        </div>
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Produk</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Batch (FEFO)</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Qty</th>
                    <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Harga Jual</th>
                    <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Subtotal</th>
                    <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Laba</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($sale->details as $detail)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3">
                        <p class="font-medium text-gray-800">{{ $detail->product->name ?? '-' }}</p>
                    </td>
                    <td class="px-4 py-3 text-xs">
                        <span class="font-mono text-gray-500">{{ $detail->batch->batch_code ?? '-' }}</span>
                        @if($detail->batch)
                        <span class="text-gray-400 ml-1">(exp: {{ $detail->batch->expired_date->format('d/m/Y') }})</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center font-medium">{{ $detail->quantity }}</td>
                    <td class="px-4 py-3 text-right text-gray-600">Rp {{ number_format($detail->price_at_sale, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right font-semibold">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right text-green-600 font-medium">Rp {{ number_format($detail->profit, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-gray-50 border-t border-gray-200">
                <tr>
                    <td colspan="4" class="px-5 py-3 text-right font-semibold text-gray-700">Total</td>
                    <td class="px-4 py-3 text-right font-bold text-gray-800">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right font-bold text-green-600">Rp {{ number_format($sale->total_profit, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td colspan="4" class="px-5 py-2 text-right text-gray-500 text-sm">Dibayar</td>
                    <td colspan="2" class="px-4 py-2 text-right text-gray-600 font-medium">Rp {{ number_format($sale->total_payment, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td colspan="4" class="px-5 py-2 text-right text-gray-500 text-sm">Kembalian</td>
                    <td colspan="2" class="px-4 py-2 text-right text-green-600 font-semibold">Rp {{ number_format($sale->change_amount, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <a href="{{ route('penjualan.index') }}" class="inline-flex items-center gap-2 text-sm text-brand-600 hover:text-brand-800 font-medium">
        ← Kembali ke Riwayat
    </a>
</div>
@endsection