<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

class UserController extends Controller
{
    // Halaman Profil (Akun Saya)
    public function profile()
    {
        return view('front.user.profile');
    }

    // Halaman Pesanan Saya
    public function orders(Request $request)
    {
        $userId = \Illuminate\Support\Facades\Auth::id();
        
        // Tarik data pesanan user yang lagi login beserta relasi barangnya
        $query = \App\Models\Order::with(['orderItems.product.images'])->where('user_id', $userId);

        // Logika untuk Tab Status (Belum Bayar, Dikemas, dll)
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Logika untuk Kotak Pencarian
        if ($request->has('keyword') && $request->keyword != '') {
            $query->where('invoice_number', 'like', '%' . $request->keyword . '%');
        }

        // Urutkan dari pesanan paling baru, batasi 10 pesanan per halaman
        $orders = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('front.user.orders', compact('orders'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        // 1. Validasi Data
        $request->validate([
            'name' => 'required|string|max:255',
            'gender' => 'nullable|in:Laki-laki,Perempuan',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:1024', // Maksimal 1MB
        ], [
            // Custom error message (opsional)
            'photo.image' => 'File harus berupa gambar.',
            'photo.mimes' => 'Format gambar harus .JPEG, .PNG, .JPG.',
            'photo.max' => 'Ukuran gambar maksimal 1 MB.'
        ]);

        // 2. Set Attributes (Manually)
        $user->name = $request->name;
        $user->gender = $request->gender;

        // 3. Logika Upload Foto
        if ($request->hasFile('photo')) {
            // Hapus foto lama kalau ada
            if ($user->photo && Storage::disk('public')->exists($user->photo)) {
                Storage::disk('public')->delete($user->photo);
            }
            
            // Simpan foto baru ke folder 'profile_photos'
            $photoPath = $request->file('photo')->store('profile_photos', 'public');
            $user->photo = $photoPath;
        }

        // 4. Simpan ke database
        $user->save();

        return redirect('/')->with('success', 'Akun berhasil di ubah!');
    }

    public function orderDetail($id)
    {
        // Cari pesanan berdasarkan ID dan mastiin itu milik user yang lagi login
        $order = \App\Models\Order::with(['orderItems.product.images']) 
                    ->where('id', $id)
                    ->where('user_id', \Illuminate\Support\Facades\Auth::id())
                    ->firstOrFail();

        // Arahin ke view order-detail yang udah kita bikin tadi
        return view('front.user.order-detail', compact('order'));
    }

    public function cancelOrder(\Illuminate\Http\Request $request, $id)
    {
        // 1. Cari pesanannya dan pastiin punya user yang lagi login
        $order = \App\Models\Order::where('id', $id)
                    ->where('user_id', \Illuminate\Support\Facades\Auth::id())
                    ->firstOrFail();

        // 2. Pastiin statusnya masih pending (belum dibayar), kalau udah dikirim gak bisa dibatalin
        if ($order->status != 'pending') {
            return redirect()->back()->with('error', 'Pesanan sudah diproses, tidak bisa dibatalkan.');
        }

        // 3. Ubah status dan simpan alasan batal dari form modal tadi
        $order->status = 'cancelled';
        $order->cancelled_by = 'buyer'; // Tandai kalau yang batalin si pembeli
        $order->cancel_reason = $request->cancel_reason; // Ambil alasan dari select dropdown
        $order->save();

        // 4. Balikin ke halaman dengan pesan sukses
        return redirect()->back()->with('success', 'Pesanan berhasil dibatalkan.');
    }
}