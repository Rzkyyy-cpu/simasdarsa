@extends('layouts.app')
@section('title', 'Verifikasi Stok Masuk')
@section('page-title', 'Verifikasi Stok Masuk')
@section('page-subtitle', 'Manajer memverifikasi batch stok yang baru masuk')

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase">Produk</th>
                    <th class="text-left px-4 py-3.5 text-xs font-semibold text-gray-500 uppercase">Kode Batch</th>
                    <th class="text-center px-4 py-3.5 text-xs font-semibold text-gray-500 uppercase">Jumlah</th>
                    <th class="text-center px-4 py-3.5 text-xs font-semibold text-gray-500 uppercase">Tgl Terima</th>
                    <th class="text-center px-4 py-3.5 text-xs font-semibold text-gray-500 uppercase">Tgl Expired</th>
                    <th class="text-center px-4 py-3.5 text-xs font-semibold text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($batches as $batch)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3">
                        <p class="font-medium text-gray-800">{{ $batch->product->name ?? '-' }}</p>
                    </td>
                    <td class="px-4 py-3 font-mono text-gray-600 text-xs">{{ $batch->batch_code ?? '-' }}</td>
                    <td class="px-4 py-3 text-center">{{ $batch->initial_quantity }}</td>
                    <td class="px-4 py-3 text-center">{{ $batch->received_date->format('d/m/Y') }}</td>
                    <td class="px-4 py-3 text-center">{{ $batch->expired_date->format('d/m/Y') }}</td>
                    <td class="px-4 py-3 text-center">
                        <form method="POST" action="{{ route('manager.verify-batch', $batch) }}">
                            @csrf
                            <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1.5 rounded-lg text-xs font-medium">
                                Verifikasi
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-5 py-12 text-center text-gray-400 text-sm">Tidak ada batch yang menunggu verifikasi.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
