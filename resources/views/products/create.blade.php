{{-- resources/views/stock/create.blade.php --}}
@extends('layouts.app')
@section('title', 'Tambah Batch Stok')
@section('page-title', 'Tambah Batch Stok')
@section('page-subtitle', 'Tambah stok baru dengan tanggal kedaluwarsa')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ route('stok.store') }}" class="space-y-5">
            @csrf

            {{-- Pilih Produk --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Produk <span class="text-red-500">*</span></label>
                <select name="product_id" required
                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400 @error('product_id') border-red-400 @enderror">
                    <option value="">-- Pilih Produk --</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}"
                                {{ old('product_id', request('product_id')) == $product->id ? 'selected' : '' }}>
                            {{ $product->name }} ({{ $product->category }})
                        </option>
                    @endforeach
                </select>
                @error('product_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-2 gap-5">
                {{-- Kode Batch --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kode Batch</label>
                    <input type="text" name="batch_code" value="{{ old('batch_code') }}"
                           placeholder="Contoh: BTH-A0001"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                </div>

                {{-- Tanggal Expired --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Kedaluwarsa <span class="text-red-500">*</span></label>
                    <input type="date" name="expired_date" value="{{ old('expired_date') }}" required
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400 @error('expired_date') border-red-400 @enderror">
                    @error('expired_date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Harga Beli --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Harga Beli (HPP) <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-3 top-2.5 text-sm text-gray-400">Rp</span>
                        <input type="number" name="buy_price" value="{{ old('buy_price') }}" min="0" required
                               placeholder="0"
                               class="w-full pl-10 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400 @error('buy_price') border-red-400 @enderror">
                    </div>
                    @error('buy_price')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Harga Jual --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Harga Jual <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-3 top-2.5 text-sm text-gray-400">Rp</span>
                        <input type="number" name="sell_price" value="{{ old('sell_price') }}" min="0" required
                               placeholder="0"
                               class="w-full pl-10 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400 @error('sell_price') border-red-400 @enderror">
                    </div>
                    @error('sell_price')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Jumlah Stok --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Stok <span class="text-red-500">*</span></label>
                    <input type="number" name="quantity" value="{{ old('quantity') }}" min="1" required
                           placeholder="Jumlah yang diterima"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400 @error('quantity') border-red-400 @enderror">
                    @error('quantity')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Tanggal Terima --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Diterima</label>
                    <input type="date" name="received_date" value="{{ old('received_date', today()->toDateString()) }}"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="bg-brand-500 hover:bg-brand-600 text-white font-semibold px-6 py-2.5 rounded-xl text-sm">
                    Simpan Batch Stok
                </button>
                <a href="{{ route('stok.index') }}"
                   class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-6 py-2.5 rounded-xl text-sm">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection