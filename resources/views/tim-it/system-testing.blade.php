@extends('layouts.app')
@section('title', 'System Testing')
@section('page-title', 'System Testing')
@section('content')
<div class="bg-white p-8 rounded-3xl shadow-xl border border-gray-100 text-center">
    <div class="w-20 h-20 bg-brand-50 text-brand-600 rounded-full flex items-center justify-center mx-auto mb-6">
        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    </div>
    <h2 class="text-2xl font-black text-gray-800 mb-2">Diagnostic Tools Ready</h2>
    <p class="text-gray-500 max-w-md mx-auto">Gunakan halaman ini untuk menjalankan simulasi pengujian rute, beban, dan integritas data FEFO.</p>
    <div class="mt-8">
        <button class="px-8 py-3 bg-brand-600 text-white font-bold rounded-2xl hover:bg-brand-700 transition-all shadow-xl shadow-brand-200">Mulai Stress Test</button>
    </div>
</div>
@endsection
