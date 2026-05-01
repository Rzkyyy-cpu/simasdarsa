<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - SIMASDARSA</title>

    {{-- Tailwind CSS & Alpine.js --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f0fdf8', 100: '#ccfbef', 200: '#99f6df', 300: '#5eead4', 400: '#2dd4bf',
                            500: '#14b8a6', 600: '#0d9488', 700: '#0f766e', 800: '#115e59', 900: '#134e4a',
                        }
                    }
                }
            }
        }
    </script>
    <script>
        // Global notification system
        window.notif = {
            success(msg, title = 'Berhasil') {
                window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', title: title, message: msg } }));
            },
            error(msg, title = 'Akses Terbatas') {
                window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'error', title: title, message: msg } }));
            },
            info(msg) {
                window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'info', title: 'Info', message: msg } }));
            }
        };

        function toastSystem() {
            return {
                toasts: [],
                add(data) {
                    const id = Date.now();
                    this.toasts.push({ id, show: true, type: data.type, title: data.title, message: data.message });
                    setTimeout(() => this.remove(id), 5000);
                },
                remove(id) {
                    const idx = this.toasts.findIndex(t => t.id === id);
                    if (idx > -1) {
                        this.toasts[idx].show = false;
                        setTimeout(() => { this.toasts = this.toasts.filter(t => t.id !== id); }, 400);
                    }
                }
            }
        }
    </script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <style>
        [x-cloak] { display: none !important; }
        .sidebar-link { transition: all 0.2s ease; }
        .sidebar-link.active { background: rgba(255,255,255,0.15); border-left: 4px solid white; color: white; }
        .sidebar-link.restricted { opacity: 0.7; }
    </style>
    @stack('styles')
</head>

<body class="h-full bg-gray-50 font-sans antialiased" x-data="{ 
    sidebarOpen: localStorage.getItem('sidebarOpen') !== 'false',
    role: '{{ session('selected_role') }}'
}">

<div class="flex h-full">
    {{-- SIDEBAR --}}
    <aside class="flex flex-col min-h-screen bg-gradient-to-b from-brand-700 to-brand-900 text-white shadow-2xl flex-shrink-0 transition-all duration-300" :class="sidebarOpen ? 'w-64' : 'w-20'">
        
        <div class="border-b border-brand-600 p-4 flex items-center gap-3">
            <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center shadow-lg">
                <svg class="w-6 h-6 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/></svg>
            </div>
            <div x-show="sidebarOpen" x-cloak>
                <p class="font-bold text-sm">SIMASDARSA</p>
                <p class="text-[10px] text-brand-200 uppercase tracking-tighter">Inventory System</p>
            </div>
        </div>

        <nav class="flex-1 overflow-y-auto p-3 space-y-1">
            {{-- Dashboard --}}
            <a href="{{ route('dashboard') }}" class="sidebar-link flex items-center rounded-lg text-sm font-medium p-2.5 {{ request()->routeIs('dashboard') ? 'active' : 'text-brand-100 hover:bg-white/10 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span x-show="sidebarOpen">Dashboard</span>
            </a>

            <p x-show="sidebarOpen" class="px-3 pt-3 pb-1 text-[10px] font-bold text-brand-300 uppercase tracking-widest">Inventori</p>

            {{-- Produk (Manager, Tim IT, Pimpinan) --}}
            @php $canProduk = in_array(session('selected_role'), ['manager', 'tim_it', 'pimpinan']); @endphp
            <a href="{{ route('produk.index') }}" class="sidebar-link flex items-center rounded-lg text-sm font-medium p-2.5 transition-all {{ request()->routeIs('produk.*') ? 'active' : 'text-brand-100 hover:bg-white/10' }} {{ !$canProduk ? 'restricted' : '' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/></svg>
                <span x-show="sidebarOpen" class="flex-1">Manajemen Produk</span>
                @if(!$canProduk) <svg x-show="sidebarOpen" class="w-3 h-3 text-brand-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg> @endif
            </a>

            {{-- Stok (Manager, Kasir, Pimpinan) --}}
            @php $canStok = in_array(session('selected_role'), ['manager', 'kasir', 'pimpinan']); @endphp
            <a href="{{ route('stok.index') }}" class="sidebar-link flex items-center rounded-lg text-sm font-medium p-2.5 transition-all {{ request()->routeIs('stok.index') ? 'active' : 'text-brand-100 hover:bg-white/10' }} {{ !$canStok ? 'restricted' : '' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                <span x-show="sidebarOpen" class="flex-1">Batch Stok</span>
                @if(!$canStok) <svg x-show="sidebarOpen" class="w-3 h-3 text-brand-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg> @endif
            </a>

            {{-- Monitoring Kedaluarsa (Manager, Kasir, Pimpinan) --}}
            @php $canExpiry = in_array(session('selected_role'), ['manager', 'kasir', 'pimpinan']); @endphp
            <a href="{{ route('stok.expiry-monitor') }}" class="sidebar-link flex items-center rounded-lg text-sm font-medium p-2.5 transition-all {{ request()->routeIs('stok.expiry-monitor') ? 'active' : 'text-brand-100 hover:bg-white/10' }} {{ !$canExpiry ? 'restricted' : '' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span x-show="sidebarOpen" class="flex-1">Monitoring Kedaluarsa</span>
                @if(!$canExpiry) <svg x-show="sidebarOpen" class="w-3 h-3 text-brand-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg> @endif
            </a>

            <p x-show="sidebarOpen" class="px-3 pt-3 pb-1 text-[10px] font-bold text-brand-300 uppercase tracking-widest">Transaksi</p>

            {{-- Kasir (Kasir, Pimpinan) --}}
            @php $canPOS = in_array(session('selected_role'), ['kasir', 'pimpinan']); @endphp
            <a href="{{ route('kasir.index') }}" class="sidebar-link flex items-center rounded-lg text-sm font-medium p-2.5 transition-all {{ request()->routeIs('kasir.*') ? 'active' : 'text-brand-100 hover:bg-white/10' }} {{ !$canPOS ? 'restricted' : '' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span x-show="sidebarOpen" class="flex-1">Kasir (POS)</span>
                @if(!$canPOS) <svg x-show="sidebarOpen" class="w-3 h-3 text-brand-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg> @endif
            </a>

            {{-- Riwayat Penjualan (Manager, Kasir, Pimpinan) --}}
            @php $canHistory = in_array(session('selected_role'), ['manager', 'kasir', 'pimpinan']); @endphp
            <a href="{{ route('penjualan.index') }}" class="sidebar-link flex items-center rounded-lg text-sm font-medium p-2.5 transition-all {{ request()->routeIs('penjualan.*') ? 'active' : 'text-brand-100 hover:bg-white/10' }} {{ !$canHistory ? 'restricted' : '' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01m-.01 4h.01"/></svg>
                <span x-show="sidebarOpen" class="flex-1">Riwayat Penjualan</span>
                @if(!$canHistory) <svg x-show="sidebarOpen" class="w-3 h-3 text-brand-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg> @endif
            </a>

            {{-- Laporan (Pimpinan, Manager) --}}
            @php $canLaporan = in_array(session('selected_role'), ['pimpinan', 'manager']); @endphp
            <p x-show="sidebarOpen" class="px-3 pt-3 pb-1 text-[10px] font-bold text-brand-300 uppercase tracking-widest">Analitik</p>
            
            <a href="{{ route('laporan.eksekutif') }}" class="sidebar-link flex items-center rounded-lg text-sm font-medium p-2.5 transition-all {{ request()->routeIs('laporan.eksekutif') ? 'active' : 'text-brand-100 hover:bg-white/10' }} {{ !$canLaporan ? 'restricted' : '' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span x-show="sidebarOpen" class="flex-1">Laporan Eksekutif</span>
                @if(!$canLaporan) <svg x-show="sidebarOpen" class="w-3 h-3 text-brand-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg> @endif
            </a>

            <a href="{{ route('laporan.laba-rugi') }}" class="sidebar-link flex items-center rounded-lg text-sm font-medium p-2.5 transition-all {{ request()->routeIs('laporan.laba-rugi') ? 'active' : 'text-brand-100 hover:bg-white/10' }} {{ !$canLaporan ? 'restricted' : '' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span x-show="sidebarOpen" class="flex-1">Laba Rugi</span>
                @if(!$canLaporan) <svg x-show="sidebarOpen" class="w-3 h-3 text-brand-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg> @endif
            </a>

            {{-- Tim IT (Tim IT, Pimpinan) --}}
            @php $canIT = in_array(session('selected_role'), ['tim_it', 'pimpinan']); @endphp
            @if($canIT)
            <p x-show="sidebarOpen" class="px-3 pt-3 pb-1 text-[10px] font-bold text-brand-300 uppercase tracking-widest">Administrasi Sistem</p>
            
            <a href="{{ route('tim-it.user-management') }}" class="sidebar-link flex items-center rounded-lg text-sm font-medium p-2.5 transition-all {{ request()->routeIs('tim-it.user-management') ? 'active' : 'text-brand-100 hover:bg-white/10' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                <span x-show="sidebarOpen" class="flex-1">User Management</span>
            </a>

            <a href="{{ route('tim-it.audit-logs') }}" class="sidebar-link flex items-center rounded-lg text-sm font-medium p-2.5 transition-all {{ request()->routeIs('tim-it.audit-logs') ? 'active' : 'text-brand-100 hover:bg-white/10' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span x-show="sidebarOpen" class="flex-1">Audit Log Activity</span>
            </a>
            @endif
        </nav>

        {{-- Logout Sidebar --}}
        <div class="border-t border-brand-600 p-4">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center bg-brand-800/50 hover:bg-red-600 text-white rounded-lg p-2.5 transition-all shadow-md group">
                    <svg class="w-5 h-5 transition-transform group-hover:scale-110" :class="sidebarOpen ? 'mr-3' : 'mx-auto'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    <span x-show="sidebarOpen" class="font-bold text-xs uppercase tracking-wider">Logout</span>
                </button>
            </form>
        </div>
    </aside>

    {{-- MAIN CONTENT --}}
    <div class="flex-1 flex flex-col min-h-screen overflow-hidden">
        <header class="bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-4">
                <button @click="sidebarOpen = !sidebarOpen" class="p-2 rounded-lg text-gray-400 hover:bg-gray-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div>
                    <h1 class="text-lg font-bold text-gray-800 tracking-tight">@yield('page-title', 'Dashboard')</h1>
                    <p class="text-[10px] text-gray-400 uppercase font-medium">@yield('page-subtitle', 'SIMASDARSA Management')</p>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <div class="text-right hidden sm:block">
                    <p class="text-xs font-bold text-gray-800 leading-none">{{ Auth::user()->name }}</p>
                    <p class="text-[9px] text-brand-600 font-bold uppercase tracking-widest mt-1">{{ session('selected_role') }}</p>
                </div>
                <div class="w-10 h-10 bg-brand-500 rounded-xl flex items-center justify-center text-white font-black shadow-inner ring-2 ring-brand-50 ring-offset-1">
                    {{ substr(Auth::user()->name, 0, 1) }}
                </div>
                {{-- Logout Topbar --}}
                <form method="POST" action="{{ route('logout') }}" class="border-l pl-4 ml-1">
                    @csrf
                    <button type="submit" class="p-2 text-gray-300 hover:text-red-500 hover:bg-red-50 rounded-lg transition-all" title="Logout">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </button>
                </form>
            </div>
        </header>

        <main id="page-content" class="flex-1 overflow-y-auto p-6">
            @yield('content')
        </main>
    </div>
</div>

{{-- TOAST SYSTEM --}}
<div x-data="toastSystem()" @notify.window="add($event.detail)" class="fixed bottom-6 right-6 z-[9999] flex flex-col gap-3 w-full max-w-xs">
    <template x-for="toast in toasts" :key="toast.id">
        <div x-show="toast.show" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-x-full opacity-0" x-transition:enter-end="translate-x-0 opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-end="opacity-0"
             class="bg-white border-l-4 shadow-2xl rounded-xl p-4 flex items-start gap-4 ring-1 ring-black/5"
             :class="{'border-green-500': toast.type === 'success', 'border-red-500': toast.type === 'error', 'border-yellow-500': toast.type === 'warning'}">
            <div class="flex-1">
                <p class="text-sm font-bold text-gray-900" x-text="toast.title"></p>
                <p class="text-xs text-gray-500 mt-0.5" x-text="toast.message"></p>
            </div>
            <button @click="remove(toast.id)" class="text-gray-300 hover:text-gray-500">✕</button>
        </div>
    </template>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        @if(session('success')) window.notif.success("{{ session('success') }}", 'Berhasil'); @endif
        @if(session('error')) window.notif.error("{{ session('error') }}", 'Akses Terbatas'); @endif
    });
</script>

@stack('scripts')
</body>
</html>
