<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductView; // <-- Tambahan wajib buat narik data pengunjung

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $periodeType = $request->input('periode_type', 'realtime');
        $periodeValue = $request->input('periode_value', ''); 
        
        $start = now()->startOfDay();
        $end = now()->endOfDay();
        $prevStart = now()->subDay()->startOfDay();
        $prevEnd = now()->subDay()->endOfDay();
        $vsText = 'vs Hari Kemarin';
        $chartLabels = ['00:00', '04:00', '08:00', '12:00', '16:00', '20:00', '24:00']; 
        $labelDisplay = 'Hari ini ' . now()->format('d/m/Y');

        $getBulanIndo = function($bln) {
            $bulan = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
            return $bulan[(int)$bln - 1];
        };

        try {
            if ($periodeType == 'kemarin') {
                $start = now()->subDay()->startOfDay();
                $end = now()->subDay()->endOfDay();
                $prevStart = now()->subDays(2)->startOfDay();
                $prevEnd = now()->subDays(2)->endOfDay();
                $vsText = 'vs Hari Sebelumnya';
                $labelDisplay = 'Kemarin ' . now()->subDay()->format('d/m/Y');
            } elseif ($periodeType == 'per_hari' && $periodeValue) {
                $date = Carbon::parse($periodeValue);
                $start = $date->copy()->startOfDay();
                $end = $date->copy()->endOfDay();
                $prevStart = $date->copy()->subDay()->startOfDay();
                $prevEnd = $date->copy()->subDay()->endOfDay();
                $vsText = 'vs Hari Sebelumnya';
                $labelDisplay = $date->format('d/m/Y');
            } elseif ($periodeType == 'per_minggu' && $periodeValue) {
                // Konversi tanggal yang dipilih jadi seminggu (Senin - Minggu)
                $date = Carbon::parse($periodeValue);
                $start = $date->copy()->startOfWeek();
                $end = $date->copy()->endOfWeek();
                $prevStart = $date->copy()->subWeek()->startOfWeek();
                $prevEnd = $date->copy()->subWeek()->endOfWeek();
                $vsText = 'vs Minggu Sebelumnya';
                $labelDisplay = $start->format('d/m/Y') . ' - ' . $end->format('d/m/Y');

                $chartLabels = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
            } elseif ($periodeType == 'per_bulan' && $periodeValue) {
                $date = Carbon::parse($periodeValue . '-01');
                $start = $date->copy()->startOfMonth();
                $end = $date->copy()->endOfMonth();
                $prevStart = $date->copy()->subMonth()->startOfMonth();
                $prevEnd = $date->copy()->subMonth()->endOfMonth();
                $vsText = 'vs Bulan Sebelumnya';
                $labelDisplay = $getBulanIndo($date->format('m')) . ' ' . $date->format('Y');
                
                $chartLabels = [];
                for($i = 1; $i <= $date->daysInMonth; $i++) { $chartLabels[] = $i; }
            } elseif ($periodeType == 'per_tahun' && $periodeValue) {
                $date = Carbon::createFromDate($periodeValue, 1, 1);
                $start = $date->copy()->startOfYear();
                $end = $date->copy()->endOfYear();
                $prevStart = $date->copy()->subYear()->startOfYear();
                $prevEnd = $date->copy()->subYear()->endOfYear();
                $vsText = 'vs Tahun Sebelumnya';
                $labelDisplay = 'Tahun ' . $date->format('Y');
                
                $chartLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            } else {
                $periodeType = 'realtime';
                $labelDisplay = 'Hari ini ' . now()->format('d/m/Y');
            }
        } catch (\Exception $e) {
            $periodeType = 'realtime';
            $labelDisplay = 'Hari ini ' . now()->format('d/m/Y');
        }

        // ==========================================
        // AMBIL DATA DARI DATABASE
        // ==========================================
        
        // 1. Data Pesanan & Penjualan (Dari tabel orders)
        $totalSales = Order::whereBetween('created_at', [$start, $end])->where('status', '!=', 'cancelled')->sum('grand_total');
        $prevSales = Order::whereBetween('created_at', [$prevStart, $prevEnd])->where('status', '!=', 'cancelled')->sum('grand_total');
        
        $totalOrders = Order::whereBetween('created_at', [$start, $end])->count();
        $prevOrders = Order::whereBetween('created_at', [$prevStart, $prevEnd])->count();
        
        $totalCancel = Order::whereBetween('created_at', [$start, $end])->where('status', 'cancelled')->count();
        $prevCancel = Order::whereBetween('created_at', [$prevStart, $prevEnd])->where('status', 'cancelled')->count();
        
        // 2. Data Trafik (Dari tabel product_views yg baru dibikin)
        $totalClicks = ProductView::whereBetween('created_at', [$start, $end])->count();
        $prevClicks = ProductView::whereBetween('created_at', [$prevStart, $prevEnd])->count();
        
        // Total Visitor ngitung Session ID yang beda aja biar valid
        $totalVisitors = ProductView::whereBetween('created_at', [$start, $end])->distinct('session_id')->count('session_id');
        $prevVisitors = ProductView::whereBetween('created_at', [$prevStart, $prevEnd])->distinct('session_id')->count('session_id');

        // 3. Data Khusus "Hari Ini" statis buat panel Real-time kanan
        $todaySales = Order::whereDate('created_at', today())->where('status', '!=', 'cancelled')->sum('grand_total');
        $todayOrders = Order::whereDate('created_at', today())->count();
        $todayClicks = ProductView::whereDate('created_at', today())->count();
        $todayVisitors = ProductView::whereDate('created_at', today())->distinct('session_id')->count('session_id');

        // Hitung Trend Naik/Turun
        $calcTrend = function($current, $prev) {
            if ($prev == 0) return $current > 0 ? 100 : 0;
            return round((($current - $prev) / $prev) * 100, 1);
        };

        $trendSales = $calcTrend($totalSales, $prevSales);
        $trendOrders = $calcTrend($totalOrders, $prevOrders);
        $trendCancel = $calcTrend($totalCancel, $prevCancel);
        $trendClicks = $calcTrend($totalClicks, $prevClicks);
        $trendVisitors = $calcTrend($totalVisitors, $prevVisitors);

        // DATA UNTUK GRAFIK (Default kosong)
        $chartDataSales = array_fill(0, count($chartLabels), 0);
        $chartDataOrders = array_fill(0, count($chartLabels), 0);
        $chartDataCancel = array_fill(0, count($chartLabels), 0);
        $chartDataConv = array_fill(0, count($chartLabels), 0);

        // ISI DATA GRAFIK DARI DATABASE
        $ordersRaw = Order::select('created_at', 'status', 'grand_total')
                          ->whereBetween('created_at', [$start, $end])
                          ->get();

        foreach ($ordersRaw as $order) {
            $index = -1;
            $date = Carbon::parse($order->created_at);

            if ($periodeType == 'realtime' || $periodeType == 'kemarin' || $periodeType == 'per_hari') {
                $hour = $date->hour;
                if ($hour < 4) $index = 0;
                elseif ($hour < 8) $index = 1;
                elseif ($hour < 12) $index = 2;
                elseif ($hour < 16) $index = 3;
                elseif ($hour < 20) $index = 4;
                elseif ($hour < 24) $index = 5;
            } elseif ($periodeType == 'per_minggu') {
                $index = $date->dayOfWeekIso - 1; 
            } elseif ($periodeType == 'per_bulan') {
                $index = $date->day - 1;
            } elseif ($periodeType == 'per_tahun') {
                $index = $date->month - 1;
            }

            if ($index >= 0 && $index < count($chartLabels)) {
                $chartDataOrders[$index] += 1;
                
                if ($order->status == 'cancelled') {
                    $chartDataCancel[$index] += 1;
                } else {
                    $chartDataSales[$index] += $order->grand_total;
                }
            }
        }

        // Kalkulasi Konversi Tiap Titik Grafik
        for ($i = 0; $i < count($chartLabels); $i++) {
            if ($chartDataOrders[$i] > 0) {
                $valid = $chartDataOrders[$i] - $chartDataCancel[$i];
                $chartDataConv[$i] = round(($valid / $chartDataOrders[$i]) * 100, 1);
            }
        }

        // ==========================================
        // PERINGKAT PRODUK (Filter Dinamis Mengikuti Kalender)
        // ==========================================
        $rankBy = $request->input('rank_by', 'penjualan'); 
        $filterCategory = $request->input('category', 'all');
        
        $categories = Product::select('category_id')->whereNotNull('category_id')->where('category_id', '!=', '')->distinct()->pluck('category_id');

        // Bikin Subquery maut biar ngitung Sold & Views khusus di tanggal yang dipilih ($start sampai $end)
        $queryTopProducts = Product::with('images', 'variations')
            ->select('products.*')
            ->selectRaw("(SELECT COALESCE(SUM(oi.qty), 0) FROM order_items oi JOIN orders o ON oi.order_id = o.id WHERE oi.product_id = products.id AND o.status != 'cancelled' AND o.created_at BETWEEN ? AND ?) as period_sold", [$start, $end])
            ->selectRaw("(SELECT COUNT(pv.id) FROM product_views pv WHERE pv.product_id = products.id AND pv.created_at BETWEEN ? AND ?) as period_views", [$start, $end]);

        if ($filterCategory != 'all') {
            $queryTopProducts->where('category_id', $filterCategory);
        }

        // Trik biar kalau bulan itu kosong (0 terjual & 0 dilihat), produknya disembunyiin
        $queryTopProducts->havingRaw("period_sold > 0 OR period_views > 0");

        // Urutin berdasarkan filter dropdown
        if ($rankBy == 'dilihat') {
            $queryTopProducts->orderBy('period_views', 'desc');
        } else {
            $queryTopProducts->orderBy('period_sold', 'desc');
        }

        $topProducts = $queryTopProducts->take(5)->get();

        return view('admin.shop.index', compact(
            'periodeType', 'periodeValue', 'labelDisplay', 'vsText', 'chartLabels', 
            'totalSales', 'trendSales', 'totalOrders', 'trendOrders', 
            'totalCancel', 'trendCancel', 'todaySales', 'todayOrders',
            'totalClicks', 'trendClicks', 'totalVisitors', 'trendVisitors',
            'todayClicks', 'todayVisitors',
            'chartDataSales', 'chartDataOrders', 'chartDataCancel', 'chartDataConv',
            'categories', 'rankBy', 'filterCategory', 'topProducts'
        ));
    }

    public function saldo() {
        return view('admin.saldo.index');
    }
}