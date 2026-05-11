<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string  $role
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $selectedRole = session('selected_role');
    

        // Jika tidak ada role yang dipilih di session, paksa login ulang
        if (!$selectedRole) {
            Auth::logout();
            return redirect()->route('login')->withErrors(['sub_role' => 'Sesi role habis, silakan login kembali.']);
        }

        // Pimpinan memiliki akses ke semua halaman
        if ($selectedRole === 'pimpinan') {
            return $next($request);
        }

        // Cek apakah role yang sedang diakses ada dalam daftar role yang diizinkan untuk rute ini
        if (!in_array($selectedRole, $roles) || !method_exists($user, 'hasRole') || !$user->hasRole($selectedRole)) {
            $allowedRoles = implode(', ', $roles);
            return redirect()->route('dashboard')->with('error', "Akses Ditolak: Peran '" . strtoupper($selectedRole) . "' tidak diizinkan mengakses halaman ini. Halaman ini khusus untuk: " . strtoupper($allowedRoles));
        }

        return $next($request);
    }
}