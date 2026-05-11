@extends('layouts.app')
@section('title', 'Detail Hak Akses')
@section('page-title', 'Detail Hak Akses')
@section('page-subtitle', 'Pengaturan Menu untuk ' . $user->name)

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden">
        <div class="p-8 border-b border-gray-100 bg-gray-50/50">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 bg-brand-100 text-brand-600 rounded-2xl flex items-center justify-center text-2xl font-black">
                    {{ substr($user->name, 0, 1) }}
                </div>
                <div>
                    <h2 class="text-2xl font-black text-gray-800">{{ $user->name }}</h2>
                    <p class="text-gray-500 font-medium">{{ $user->email }}</p>
                </div>
            </div>
        </div>

        <form action="{{ route('tim-it.user-management.update-details', $user->id) }}" method="POST" class="p-8">
            @csrf @method('PUT')
            
            <div class="mb-8">
                <h3 class="text-lg font-black text-gray-800 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    Akses Menu Sistem
                </h3>
                <p class="text-sm text-gray-500 mb-6">Pilih menu mana saja yang dapat diakses oleh user ini. Perubahan ini akan langsung berdampak pada navigasi user.</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @php
                        $userMenus = $user->permissions['menus'] ?? [];
                    @endphp
                    @foreach($menus as $label => $route)
                    <label class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl cursor-pointer hover:bg-brand-50 transition-all border border-transparent hover:border-brand-100 group">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center shadow-sm group-hover:text-brand-600 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </div>
                            <span class="font-bold text-gray-700">{{ $label }}</span>
                        </div>
                        <input type="checkbox" name="menus[]" value="{{ $route }}" 
                               {{ in_array($route, $userMenus) ? 'checked' : '' }}
                               class="w-6 h-6 rounded-lg text-brand-600 border-gray-300 focus:ring-brand-500 transition-all">
                    </label>
                    @endforeach
                </div>
            </div>

            <div class="flex items-center justify-between pt-8 border-t border-gray-100">
                <a href="{{ route('tim-it.user-management') }}" class="px-6 py-3 text-gray-500 font-bold hover:text-gray-700 transition-colors">Batal</a>
                <button type="submit" class="px-10 py-4 bg-brand-600 text-white rounded-2xl font-black uppercase tracking-widest hover:bg-brand-700 transition-all shadow-lg shadow-brand-200">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
