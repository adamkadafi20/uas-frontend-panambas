<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductView;
use App\Models\Category;

class HomeController extends Controller
{
    public function index()
    {
        // ==========================================
        // 1. DATA PENGIRIMAN (KOTAK PALING ATAS)
        // ==========================================
        // Perlu Diproses = Sudah dibayar, seller harus kirim (status: processing)
        $totalPending = Order::where('status', 'processing')->count();
        
        // Telah Diproses = Barang sedang dibawa kurir (status: shipped)
        $totalShipped = Order::where('status', 'shipped')->count();
        
        // Pembatalan = Pesanan dibatalkan (status: cancelled)
        $totalCancelled = Order::where('status', 'cancelled')->count();

        // ==========================================
        // 2. ANALISIS TOKO (KHUSUS BULAN INI)
        // ==========================================
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        // Total Penjualan (kecuali yang batal)
        $monthSales = Order::whereBetween('created_at', [$startOfMonth, $endOfMonth])
                        ->where('status', '!=', 'cancelled')->sum('grand_total');
        
        // Total Pesanan
        $monthOrders = Order::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();
        $monthCancel = Order::whereBetween('created_at', [$startOfMonth, $endOfMonth])
                        ->where('status', 'cancelled')->count();
        
        // Data Trafik (Pengunjung & Klik)
        $monthVisitors = ProductView::whereBetween('created_at', [$startOfMonth, $endOfMonth])
                        ->distinct('session_id')->count('session_id');
        $monthClicks = ProductView::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();

        // Tingkat Konversi Bulan Ini
        $validOrders = $monthOrders - $monthCancel;
        $monthConv = $monthOrders > 0 ? ($validOrders / $monthOrders) * 100 : 0;

        // ==========================================
        // 3. BEST SELLER (ALL TIME) - AMBIL 5
        // ==========================================
        $topProducts = Product::with('images')->orderBy('sold', 'desc')->take(5)->get();

        // ==========================================
        // 4. KATEGORI TERLARIS (ALL TIME)
        // ==========================================
        // Ngambil semua kategori, terus dihitung manual total 'sold' dari produk-produk di dalamnya
        // ==========================================
        // 4. KATEGORI TERLARIS (ALL TIME)
        // ==========================================
        // Langsung tembak pakai DB Join biar otomatis ngejumlahin kolom 'sold' di tiap kategori
       $topCategories = \Illuminate\Support\Facades\DB::table('products')
            ->selectRaw("TRIM(SUBSTRING_INDEX(category_id, '>', 1)) as name, SUM(sold) as total_sold")
            ->whereNotNull('category_id')
            ->where('category_id', '!=', '')
            ->groupBy('name')
            ->orderByDesc('total_sold')
            ->limit(4)
            ->get();
            
        return view('admin.dashboard', compact(
            'totalPending', 'totalShipped', 'totalCancelled',
            'monthSales', 'monthOrders', 'monthVisitors', 'monthClicks', 'monthConv',
            'topProducts', 'topCategories'
        ));
    }

    public function logout() 
    {
        Auth::guard('admin')->logout();
        return redirect()->route('admin.login');
    }
}