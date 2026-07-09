<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;


class FrontAuthController extends Controller
{
    // Nampilin Halaman Login
    public function login()
    {
        return view('front.auth.login');
    }

    // Proses Cek Login
    public function authenticate(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $request->session()->regenerate();

            // Kalau yang login admin, lempar ke dashboard admin
            if (Auth::user()->role == 'admin') {
                return redirect()->intended('/admin/dashboard');
            }
            
            // Kalau pembeli biasa, lempar ke halaman depan
            return redirect()->intended('/');
        }

        return back()->withErrors([
            'email' => 'Email atau password salah nih Bre.',
        ])->onlyInput('email');
    }

    // Nampilin Halaman Register
    public function register()
    {
        return view('front.auth.register');
    }

    // Proses Simpan Data Register
    public function storeRegister(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed', // Harus ada password_confirmation
        ], [
            'email.unique' => 'Email ini udah dipakai, coba login aja.',
            'password.confirmed' => 'Konfirmasi password nggak sama nih.',
            'password.min' => 'Password minimal 6 karakter.'
        ]);

        User::create([
            'name' => 'Pelanggan Baru', // Nama default, bisa lo kembangin nanti
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user' // Otomatis jadi pembeli, bukan admin
        ]);

        // Redirect ke halaman login bawa pesan sukses
        return redirect()->route('login')->with('success', 'Akun sukses di buat! Silakan login.');
    }

    // Proses Keluar (Logout)
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    // Nampilin Halaman Lupa Password
    public function forgotPassword()
    {
        return view('front.auth.forgot-password');
    }

    // Proses Ubah Password Baru
    public function processForgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email', // Ngecek emailnya ada di database nggak
            'password' => 'required|min:6|confirmed', // Harus cocok sama konfirmasi
        ], [
            'email.exists' => 'Email ini belum terdaftar nih Bre.',
            'password.confirmed' => 'Konfirmasi password nggak sama nih.',
            'password.min' => 'Password minimal 6 karakter.'
        ]);

        // Cari user berdasarkan email, terus timpa passwordnya
        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        // Lempar balik ke login bawa pesan sukses
        return redirect()->route('login')->with('success', 'Password sukses di ubah! Silakan login dengan password baru.');
    }

    public function removeCart(Request $request)
 {
     $cart = \App\Models\Cart::where('user_id', \Illuminate\Support\Facades\Auth::id())
                             ->where('id', $request->cart_id)
                             ->first();
     if ($cart) {
         $cart->delete();
         return response()->json(['success' => true]);
     }
     return response()->json(['success' => false, 'message' => 'Barang tidak ditemukan.']);
 }
}