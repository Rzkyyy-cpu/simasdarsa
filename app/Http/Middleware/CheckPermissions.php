<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermissions
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect('login');
        }

        // Standard roles that bypass permission checks (optional, depends on policy)
        // If tim_it or pimpinan should have all access, check here
        if ($user->hasAnyRole(['tim_it', 'pimpinan'])) {
            return $next($request);
        }

        if (!$user->hasPermission($permission)) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'Akses Terbatas: Anda tidak memiliki izin.'], 403);
            }
            return redirect()->route('dashboard')->with('error', 'Anda tidak memiliki izin untuk mengakses fitur tersebut.');
        }

        return $next($request);
    }
}
