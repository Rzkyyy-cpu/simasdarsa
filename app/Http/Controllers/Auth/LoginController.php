<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function authenticate(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'sub_role' => 'required|in:pimpinan,tim_it,manager,kasir',
        ]);

        // Find user by email
        $user = \App\Models\User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors([
                'email' => 'Email tidak ditemukan.',
            ])->withInput($request->only('email'));
        }

        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'password' => 'Password salah.',
            ])->withInput($request->only('email'));
        }

        // Check if user has the selected role
        if (!$user->hasRole($request->sub_role)) {
            return back()->withErrors([
                'sub_role' => 'Anda tidak memiliki akses untuk role tersebut.',
            ])->withInput($request->only('email'));
        }

        // Login user
        Auth::login($user);

        // Store selected role in session for RBAC
        session(['selected_role' => $request->sub_role]);

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}