<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAuthenticate
{
    public function handle(Request $request, Closure $next)
{
    if (Auth::check()) {
        // Izinkan kalau rolenya 'admin' ATAU 'seller'
        if (Auth::user()->role == 'admin' || Auth::user()->role == 'seller') {
            return $next($request);
        }

        // Kalau rolenya bukan salah satu di atas (misal 'customer'), tendang!
        Auth::logout();
        return redirect()->route('admin.login')->with('error', 'Lo gak punya akses ke sini, Bre!');
    }

    return redirect()->route('admin.login');
}
}