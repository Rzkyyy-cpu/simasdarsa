@extends('layouts.app')
@section('title', 'User Management')
@section('page-title', 'User Management')
@section('page-subtitle', 'Kelola Pengguna & Hak Akses Sistem')

@section('content')
<div x-data="{ 
    showCreateModal: false, 
    showEditModal: false,
    currentUser: { id: null, name: '', email: '', roles: [] },
    openEditModal(user) {
        this.currentUser = { ...user };
        this.showEditModal = true;
    },
    updatePermission(userId, permission, value) {
        fetch('{{ url('tim-it/user-management') }}/' + userId + '/permissions', {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                permission: permission,
                value: value
            })
        });
    }
}" class="space-y-6">

    {{-- HEADER ACTIONS --}}
    <div class="flex justify-between items-center bg-white p-6 rounded-3xl shadow-sm border border-gray-100">
        <div>
            <h2 class="text-xl font-black text-gray-800">Daftar Pengguna</h2>
            <p class="text-xs text-gray-500">Total {{ count($users) }} pengguna terdaftar</p>
        </div>
        <button @click="showCreateModal = true" class="px-6 py-3 bg-brand-600 text-white rounded-2xl font-bold text-sm hover:bg-brand-700 transition-all shadow-lg shadow-brand-200 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah User Baru
        </button>
    </div>

    {{-- USERS TABLE --}}
    <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50/50 border-b border-gray-100 text-[10px] uppercase tracking-widest text-gray-400 font-black">
                    <th class="px-6 py-4">User</th>
                    <th class="px-6 py-4">Email</th>
                    <th class="px-6 py-4">Roles</th>
                    <th class="px-6 py-4">Akses CRUD</th>
                    <th class="px-6 py-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($users as $user)
                <tr class="hover:bg-gray-50/50 transition-colors group">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-brand-100 text-brand-600 rounded-xl flex items-center justify-center font-black">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                            <span class="font-bold text-gray-700">{{ $user->name }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $user->email }}</td>
                    <td class="px-6 py-4">
                        <div class="flex flex-wrap gap-1">
                            @foreach((array)$user->roles as $role)
                            <span class="px-2 py-0.5 bg-brand-50 text-brand-600 rounded-md text-[10px] font-black uppercase tracking-tighter">
                                {{ str_replace('_', ' ', $role) }}
                            </span>
                            @endforeach
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            @foreach(['create', 'read', 'update', 'delete'] as $perm)
                            <label class="flex flex-col items-center gap-1 group/toggle cursor-pointer">
                                <span class="text-[7px] font-black uppercase text-gray-400 group-hover/toggle:text-brand-500 transition-colors">{{ $perm }}</span>
                                <div class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" 
                                           @change="updatePermission({{ $user->id }}, 'crud.{{ $perm }}', $event.target.checked)"
                                           {{ $user->hasPermission("crud.$perm") ? 'checked' : '' }}
                                           class="sr-only peer">
                                    <div class="w-7 h-4 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-brand-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:bg-brand-600"></div>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex justify-center gap-2">
                            <a href="{{ route('tim-it.user-management.details', $user->id) }}" class="p-2 text-purple-400 hover:text-purple-600 hover:bg-purple-50 rounded-lg transition-all" title="Pengaturan Menu">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </a>
                            <button @click="openEditModal({{ json_encode($user) }})" class="p-2 text-blue-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all" title="Edit">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                            @if($user->id !== auth()->id())
                            <form action="{{ route('tim-it.user-management.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus user ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-2 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all" title="Hapus">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- MODAL CREATE --}}
    <div x-show="showCreateModal" class="fixed inset-0 z-[100] overflow-y-auto" x-cloak>
        <div class="flex items-center justify-center min-h-screen p-4">
            <div @click="showCreateModal = false" class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity"></div>
            
            <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all">
                <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <h3 class="font-black text-gray-800 text-lg uppercase tracking-tight">Tambah User Baru</h3>
                    <button @click="showCreateModal = false" class="text-gray-400 hover:text-gray-600">✕</button>
                </div>
                <form action="{{ route('tim-it.user-management.store') }}" method="POST" class="p-6 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Nama Lengkap</label>
                        <input type="text" name="name" required class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl focus:ring-2 focus:ring-brand-500 focus:bg-white outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Email</label>
                        <input type="email" name="email" required class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl focus:ring-2 focus:ring-brand-500 focus:bg-white outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Password</label>
                        <input type="password" name="password" required class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl focus:ring-2 focus:ring-brand-500 focus:bg-white outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Roles</label>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach(['pimpinan', 'tim_it', 'manager', 'kasir'] as $role)
                            <label class="flex items-center gap-2 p-2 bg-gray-50 rounded-lg cursor-pointer hover:bg-brand-50 transition-all border border-transparent hover:border-brand-200">
                                <input type="checkbox" name="roles[]" value="{{ $role }}" class="rounded text-brand-600 focus:ring-brand-500">
                                <span class="text-xs font-bold text-gray-600 uppercase">{{ str_replace('_', ' ', $role) }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="pt-4">
                        <button type="submit" class="w-full py-4 bg-brand-600 text-white rounded-2xl font-black uppercase tracking-widest hover:bg-brand-700 transition-all shadow-lg shadow-brand-200">Simpan User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL EDIT --}}
    <div x-show="showEditModal" class="fixed inset-0 z-[100] overflow-y-auto" x-cloak>
        <div class="flex items-center justify-center min-h-screen p-4">
            <div @click="showEditModal = false" class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity"></div>
            
            <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all">
                <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <h3 class="font-black text-gray-800 text-lg uppercase tracking-tight">Edit Data User</h3>
                    <button @click="showEditModal = false" class="text-gray-400 hover:text-gray-600">✕</button>
                </div>
                <form :action="'{{ url('tim-it/user-management') }}/' + currentUser.id" method="POST" class="p-6 space-y-4">
                    @csrf @method('PUT')
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Nama Lengkap</label>
                        <input type="text" name="name" x-model="currentUser.name" required class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl focus:ring-2 focus:ring-brand-500 focus:bg-white outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Email</label>
                        <input type="email" name="email" x-model="currentUser.email" required class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl focus:ring-2 focus:ring-brand-500 focus:bg-white outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Password Baru (Kosongkan jika tidak ingin diubah)</label>
                        <input type="password" name="password" class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl focus:ring-2 focus:ring-brand-500 focus:bg-white outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Roles</label>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach(['pimpinan', 'tim_it', 'manager', 'kasir'] as $role)
                            <label class="flex items-center gap-2 p-2 bg-gray-50 rounded-lg cursor-pointer hover:bg-brand-50 transition-all border border-transparent hover:border-brand-200">
                                <input type="checkbox" name="roles[]" value="{{ $role }}" :checked="currentUser.roles && currentUser.roles.includes('{{ $role }}')" class="rounded text-brand-600 focus:ring-brand-500">
                                <span class="text-xs font-bold text-gray-600 uppercase">{{ str_replace('_', ' ', $role) }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="pt-4">
                        <button type="submit" class="w-full py-4 bg-blue-600 text-white rounded-2xl font-black uppercase tracking-widest hover:bg-blue-700 transition-all shadow-lg shadow-blue-200">Update User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
