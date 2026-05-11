@extends('layouts.app')
@section('title', 'Update Stok Fisik')
@section('page-title', 'Update Stok Fisik')
@section('page-subtitle', 'Sinkronisasi jumlah stok di sistem dengan jumlah barang nyata')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-5 border-b border-gray-100">
                <h3 class="font-bold text-gray-800">Daftar Batch Aktif</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-500 uppercase">Produk / Batch</th>
                            <th class="text-center px-4 py-3.5 text-xs font-semibold text-gray-500 uppercase">Stok Sistem</th>
                            <th class="text-center px-4 py-3.5 text-xs font-semibold text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($batches as $batch)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3">
                                <p class="font-medium text-gray-800">{{ $batch->product?->name ?? '-' }}</p>
                                <p class="text-xs font-mono text-gray-400">{{ $batch->batch_code }}</p>
                            </td>
                            <td class="px-4 py-3 text-center font-bold text-brand-600">{{ $batch->current_quantity }}</td>
                            <td class="px-4 py-3 text-center">
                                <button type="button" 
                                        onclick="selectBatch({{ $batch->id }}, '{{ $batch->product?->name ?? 'Produk Tidak Terdaftar' }}', '{{ $batch->batch_code }}', {{ $batch->current_quantity }})"
                                        class="bg-brand-50 text-brand-600 hover:bg-brand-500 hover:text-white px-3 py-1.5 rounded-lg text-xs font-medium transition-colors">
                                    Pilih
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-6">
            <h3 class="font-bold text-gray-800 mb-4">Form Update Fisik</h3>
            
            <form id="updateForm" method="POST" action="{{ route('kasir.save-physical-stock-update') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="batch_id" id="input_batch_id">
                
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Produk Terpilih</label>
                    <div id="selected_display" class="p-3 bg-gray-50 rounded-xl border border-dashed border-gray-200 text-sm text-gray-400 italic">
                        Belum ada produk dipilih
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Stok Nyata (Fisik)</label>
                    <input type="number" name="actual_quantity" id="input_actual" required min="0"
                           class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-400 focus:outline-none text-lg font-bold text-brand-600">
                    <p class="mt-1 text-xs text-gray-400">Masukkan jumlah barang yang dihitung secara manual.</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Catatan (Opsional)</label>
                    <textarea name="notes" rows="3" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-400 focus:outline-none text-sm"></textarea>
                </div>

                <button type="submit" id="submitBtn" disabled
                        class="w-full bg-brand-500 hover:bg-brand-600 disabled:bg-gray-200 text-white font-bold py-3 rounded-xl transition-colors shadow-lg shadow-brand-100">
                    Simpan Perubahan
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function selectBatch(id, name, code, current) {
    document.getElementById('input_batch_id').value = id;
    document.getElementById('input_actual').value = current;
    document.getElementById('selected_display').innerHTML = `
        <p class="font-bold text-gray-800 not-italic">${name}</p>
        <p class="text-xs font-mono text-gray-500 not-italic">Batch: ${code}</p>
        <p class="text-xs text-brand-600 not-italic mt-1">Stok Sistem: ${current}</p>
    `;
    document.getElementById('selected_display').classList.remove('italic', 'text-gray-400');
    document.getElementById('selected_display').classList.add('bg-brand-50', 'border-brand-200');
    document.getElementById('submitBtn').disabled = false;
    document.getElementById('input_actual').focus();
}
</script>
@endsection
