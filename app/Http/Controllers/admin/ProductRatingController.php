<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductRating; 

class ProductRatingController extends Controller
{
    public function index(Request $request)
    {
        // 1. NGITUNG RINGKASAN ATAS
        $avgRating = ProductRating::avg('rating') ?? 0;
        $avgRating = number_format($avgRating, 1);
        
        $totalReviews = ProductRating::count();
        $needReplyCount = ProductRating::whereNull('reply')->orWhere('reply', '')->count();
        $repliedCount = ProductRating::whereNotNull('reply')->where('reply', '!=', '')->count();

        // 2. QUERY DAFTAR ULASAN (Relasi ke tabel product)
        $query = ProductRating::with('product')->orderBy('created_at', 'desc');

        // Filter Tab (Perlu Dibalas / Sudah Dibalas)
        if ($request->tab == 'perlu_dibalas') {
            $query->where(function($q) {
                $q->whereNull('reply')->orWhere('reply', '');
            });
        } elseif ($request->tab == 'sudah_dibalas') {
            $query->whereNotNull('reply')->where('reply', '!=', '');
        }

        // Filter Checkbox Bintang (1 - 5)
        if ($request->filled('stars')) {
            $query->whereIn('rating', $request->stars);
        }

        // Filter Pencarian (Nama Produk atau Username)
        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function($q) use ($keyword) {
                $q->whereHas('product', function($qProd) use ($keyword) {
                    $qProd->where('title', 'like', "%{$keyword}%");
                })->orWhere('username', 'like', "%{$keyword}%");
            });
        }

        $reviews = $query->paginate(10); 

        return view('admin.reviews.index', compact(
            'avgRating', 'totalReviews', 'needReplyCount', 'repliedCount', 'reviews'
        ));
    }

    // FUNGSI BUAT NYIMPEN BALASAN
    public function reply(Request $request, $id)
    {
        $request->validate([
            'reply' => 'required|string|max:1000'
        ]);

        $review = ProductRating::findOrFail($id);
        $review->reply = $request->reply;
        $review->save();

        return redirect()->back()->with('success', 'Balasan berhasil dikirim!');
    }
}