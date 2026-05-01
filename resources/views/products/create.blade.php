@extends('layouts.app')
@section('title', 'Tambah Produk Baru')
@section('page-title', 'Tambah Produk')
@section('page-subtitle', 'Daftarkan data master produk baru ke sistem')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ route('produk.store') }}" class="space-y-5">
            @csrf

            {{-- Nama Produk --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Produk <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       placeholder="Contoh: Aqua 600ml"
                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400 @error('name') border-red-400 @enderror">
                @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-2 gap-5">
                {{-- Barcode --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Barcode (Opsional)</label>
                    <input type="text" name="barcode" value="{{ old('barcode') }}"
                           placeholder="Scan atau ketik barcode"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400 @error('barcode') border-red-400 @enderror">
                    @error('barcode')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Kategori --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kategori <span class="text-red-500">*</span></label>
                    <input type="text" name="category" value="{{ old('category') }}" required
                           placeholder="Contoh: Minuman"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400 @error('category') border-red-400 @enderror">
                    @error('category')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Satuan --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Satuan <span class="text-red-500">*</span></label>
                    <select name="unit" required
                            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400 @error('unit') border-red-400 @enderror">
                        <option value="pcs" {{ old('unit') == 'pcs' ? 'selected' : '' }}>Pcs</option>
                        <option value="box" {{ old('unit') == 'box' ? 'selected' : '' }}>Box</option>
                        <option value="pack" {{ old('unit') == 'pack' ? 'selected' : '' }}>Pack</option>
                        <option value="liter" {{ old('unit') == 'liter' ? 'selected' : '' }}>Liter</option>
                        <option value="kg" {{ old('unit') == 'kg' ? 'selected' : '' }}>Kg</option>
                    </select>
                    @error('unit')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Ambang Batas Stok --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Stok Minimal <span class="text-red-500">*</span></label>
                    <input type="number" name="min_stock" value="{{ old('min_stock', 5) }}" min="0" required
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400 @error('min_stock') border-red-400 @enderror">
                    @error('min_stock')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="bg-brand-500 hover:bg-brand-600 text-white font-semibold px-6 py-2.5 rounded-xl text-sm">
                    Simpan Produk
                </button>
                <a href="{{ route('produk.index') }}"
                   class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-6 py-2.5 rounded-xl text-sm">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
