{{-- resources/views/stock/edit.blade.php --}}
@extends('layouts.app')
@section('title', 'Edit Batch Stok')
@section('page-title', 'Edit Batch Stok')
@section('page-subtitle', 'Ubah data batch: ' . $batch->batch_code)

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ route('stok.update', $batch) }}" class="space-y-5">
            @csrf @method('PUT')

            <div class="bg-gray-50 rounded-xl p-4 text-sm">
                <p class="font-medium text-gray-700">Produk: <span class="text-brand-600">{{ $batch->product->name }}</span></p>
                <p class="text-gray-400 text-xs mt-0.5">Perubahan stok tidak bisa diubah manual jika sudah ada transaksi. Edit hanya untuk koreksi data.</p>
            </div>

            <div class="grid grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kode Batch</label>
                    <input type="text" name="batch_code" value="{{ old('batch_code', $batch->batch_code) }}"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Kedaluwarsa <span class="text-red-500">*</span></label>
                    <input type="date" name="expired_date" value="{{ old('expired_date', $batch->expired_date->format('Y-m-d')) }}" required
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Harga Beli (HPP)</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2.5 text-sm text-gray-400">Rp</span>
                        <input type="number" name="buy_price" value="{{ old('buy_price', $batch->buy_price) }}" min="0"
                               class="w-full pl-10 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Harga Jual</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2.5 text-sm text-gray-400">Rp</span>
                        <input type="number" name="sell_price" value="{{ old('sell_price', $batch->sell_price) }}" min="0"
                               class="w-full pl-10 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Stok Saat Ini (koreksi)</label>
                    <input type="number" name="current_quantity" value="{{ old('current_quantity', $batch->current_quantity) }}" min="0"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Diterima</label>
                    <input type="date" name="received_date" value="{{ old('received_date', $batch->received_date->format('Y-m-d')) }}"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="bg-brand-500 hover:bg-brand-600 text-white font-semibold px-6 py-2.5 rounded-xl text-sm">
                    Update Batch
                </button>
                <a href="{{ route('stok.index') }}"
                   class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-6 py-2.5 rounded-xl text-sm">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection