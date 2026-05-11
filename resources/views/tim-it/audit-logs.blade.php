@extends('layouts.app')

@section('title', 'Audit Log Activity')
@section('page-title', 'Audit Log & Traceability')
@section('page-subtitle', 'Monitoring Aktivitas Sistem & Jalur Penelusuran')

@section('content')
<div class="space-y-6">
    {{-- STATS CARDS --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="p-3 bg-blue-50 text-blue-600 rounded-xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-xs text-gray-400 font-bold uppercase tracking-wider">Total Logs</p>
                <p class="text-2xl font-black text-gray-800">{{ count($logs) }}</p>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="p-3 bg-red-50 text-red-600 rounded-xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div>
                <p class="text-xs text-gray-400 font-bold uppercase tracking-wider">Security Alerts</p>
                <p class="text-2xl font-black text-gray-800">
                    {{ collect($logs)->where('level', 'WARNING')->count() + collect($logs)->where('level', 'ERROR')->count() }}
                </p>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="p-3 bg-brand-50 text-brand-600 rounded-xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
            <div>
                <p class="text-xs text-gray-400 font-bold uppercase tracking-wider">System Health</p>
                <p class="text-2xl font-black text-brand-600">Stable</p>
            </div>
        </div>
    </div>

    {{-- LOG TABLE --}}
    <div class="bg-white rounded-3xl shadow-xl shadow-gray-200/50 border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-50 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-gray-50/50">
            <div>
                <h3 class="font-black text-gray-800 flex items-center gap-2 text-xl">
                    <span class="w-2 h-8 bg-brand-500 rounded-full"></span>
                    Recent Activity Logs
                </h3>
                <p class="text-xs text-gray-500 mt-1">Menampilkan 100 aktivitas sistem terbaru</p>
            </div>
            <div class="flex gap-2">
                <button class="px-4 py-2 bg-white border border-gray-200 rounded-xl text-xs font-bold text-gray-600 hover:bg-gray-50 transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    Filter
                </button>
                <a href="{{ route('tim-it.audit-logs.export') }}" class="px-4 py-2 bg-brand-600 text-white rounded-xl text-xs font-bold hover:bg-brand-700 transition-all shadow-lg shadow-brand-200 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Export Log
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 text-[10px] uppercase tracking-widest text-gray-400 font-black border-b border-gray-100">
                        <th class="px-6 py-4">Waktu & Level</th>
                        <th class="px-6 py-4">Aktivitas / Pesan</th>
                        <th class="px-6 py-4 text-center">Jalur Traceability</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($logs as $index => $log)
                    <tr class="hover:bg-gray-50/50 transition-colors group">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex flex-col">
                                <span class="text-xs font-bold text-gray-700">{{ $log['timestamp'] }}</span>
                                <span class="mt-1 px-2 py-0.5 rounded-md text-[9px] font-black uppercase inline-block w-fit
                                    {{ $log['level'] === 'ERROR' ? 'bg-red-100 text-red-600' : 
                                       ($log['level'] === 'WARNING' ? 'bg-yellow-100 text-yellow-600' : 'bg-blue-100 text-blue-600') }}">
                                    {{ $log['level'] }}
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-gray-600 leading-relaxed font-medium">
                                {{ is_array($log['message']) ? json_encode($log['message']) : $log['message'] }}
                            </p>
                            <p class="text-[10px] text-gray-400 mt-1 italic">Environment: {{ $log['env'] }}</p>
                        </td>
                        <td class="px-6 py-4 min-w-[300px]">
                            @if(!empty($log['trace']))
                            <div class="flex items-center gap-2 relative h-12">
                                {{-- Background Line --}}
                                <div class="absolute left-0 right-0 h-0.5 bg-gray-100 top-1/2 -translate-y-1/2"></div>
                                
                                @foreach($log['trace'] as $traceIndex => $step)
                                <div class="relative z-10 flex flex-col items-center group/step">
                                    {{-- Node --}}
                                    <div class="w-3 h-3 rounded-full border-2 transition-all duration-300
                                        {{ $traceIndex === count($log['trace']) - 1 ? 'bg-brand-500 border-brand-200 scale-125' : 'bg-white border-gray-300' }}">
                                    </div>
                                    
                                    {{-- Tooltip/Label --}}
                                    <div class="absolute -bottom-8 whitespace-nowrap opacity-0 group-hover/step:opacity-100 transition-opacity bg-gray-800 text-white text-[9px] px-2 py-1 rounded shadow-xl pointer-events-none">
                                        {{ $step['action'] }} ({{ $step['time'] }})
                                        @if(isset($step['user'])) | {{ $step['user'] }} @endif
                                    </div>

                                    {{-- Visible Label --}}
                                    <div class="absolute -top-6 whitespace-nowrap text-[9px] font-bold text-gray-400">
                                        {{ $step['time'] }}
                                    </div>
                                </div>
                                
                                {{-- Connector for last items --}}
                                @if(!$loop->last)
                                <div class="flex-1 min-w-[40px]"></div>
                                @endif
                                @endforeach
                            </div>
                            <div class="mt-2 text-right">
                                <button class="text-[9px] font-bold text-brand-600 hover:text-brand-800 uppercase tracking-tighter">Detail Trace →</button>
                            </div>
                            @else
                            <div class="flex items-center justify-center h-12 bg-gray-50 rounded-xl border border-dashed border-gray-200">
                                <span class="text-[10px] text-gray-400 font-bold uppercase tracking-widest italic">No Trace Data</span>
                            </div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-6 py-20 text-center">
                            <div class="flex flex-col items-center opacity-30">
                                <svg class="w-16 h-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <p class="text-xl font-black">Tidak ada log ditemukan</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
