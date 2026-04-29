<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HandleAjaxNavigation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Set flag untuk middleware selanjutnya jika ini AJAX navigation
        if ($request->header('X-Requested-With') === 'XMLHttpRequest') {
            $request->attributes->set('isAjaxNavigation', true);
        }

        return $next($request);
    }
}
