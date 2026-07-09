<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\StoreSetting;

class SettingController extends Controller
{
    public function index()
    {
        if(Auth::user()->id != 1) {
            abort(403, 'Cuma Boss (Super Admin) yang boleh masuk sini Bre!');
        }

        // Ambil data toko baris pertama (karena toko cuma 1)
        $store = StoreSetting::first(); 

        // Lempar variabel $store ke halaman view
        return view('admin.settings', compact('store'));
    }

    public function updateProfile(Request $request)
    {
        // Pastiin lagi yang nge-post data ini beneran Super Admin
        if(Auth::user()->id != 1) {
            abort(403, 'Cuma Boss (Super Admin) yang boleh ubah pengaturan ini!');
        }

        $user = Auth::user();

        // 1. Validasi dan Update Nama & Email
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);

        $user->name = $request->name;
        $user->email = $request->email;

        // 2. Logika Ganti Password Log In
        if ($request->filled('new_login_password')) {
            if (!Hash::check($request->current_login_password, $user->password)) {
                return back()->with('error', 'Password Log In saat ini salah!');
            }
            if ($request->new_login_password !== $request->confirm_login_password) {
                return back()->with('error', 'Konfirmasi Password Log In baru tidak cocok!');
            }
            $user->password = Hash::make($request->new_login_password);
        }

        // 3. Logika Bikin/Ganti Password Keamanan (Saldo & Pengaturan)
        if ($request->filled('new_security_password')) {
            // Kalau sebelumnya udah pernah bikin password keamanan, wajib masukin yg lama
            if (!empty($user->security_password)) { 
                if (!Hash::check($request->current_security_password, $user->security_password)) {
                    return back()->with('error', 'Pin/Password Keamanan saat ini salah!');
                }
            }
            if ($request->new_security_password !== $request->confirm_security_password) {
                return back()->with('error', 'Konfirmasi Pin/Password Keamanan tidak cocok!');
            }
            $user->security_password = Hash::make($request->new_security_password);
        }

        // Simpan semua perubahan ke database
        $user->save();

        return back()->with('success', 'Profil dan Pengaturan Keamanan berhasil diperbarui!');
    }

    public function updateAddress(Request $request)
    {
        if(Auth::user()->id != 1) {
            abort(403, 'Cuma Boss (Super Admin) yang boleh ubah pengaturan ini!');
        }

        // Cari data toko, kalau belum ada sama sekali, bikin baru (new)
        $store = StoreSetting::first();
        if (!$store) {
            $store = new StoreSetting();
        }

        // Masukin data dari form ke database
        $store->sender_name = $request->sender_name;
        $store->sender_phone = $request->sender_phone;
        $store->province = $request->province;
        $store->city = $request->city;
        $store->district = $request->district;
        $store->postal_code = $request->postal_code;
        $store->detail_address = $request->detail_address;
        
        $store->save();

        return back()->with('success', 'Alamat Pengiriman Toko berhasil diperbarui!');
    }

   public function verifySecurity(\Illuminate\Http\Request $request)
    {
        $user = Auth::user();
        
        // 🚨 KUNCI JAWABANNYA DI SINI:
        // Kalau di database kosong (karena barusan lu hapus),
        // LANGSUNG BUKA PINTU biar lu bisa masuk ke Pengaturan buat nyetting ulang!
        if (empty($user->security_password)) {
            return response()->json(['success' => true]);
        }

        // Kalau udah disetting, baru cek cocok apa nggak sama yang di database
        if (\Illuminate\Support\Facades\Hash::check($request->password, $user->security_password)) {
            return response()->json(['success' => true]);
        }

        // Kalau salah
        return response()->json(['success' => false, 'message' => 'Password Keamanan SALAH Bre! Ingat-ingat lagi!']);
    }
}