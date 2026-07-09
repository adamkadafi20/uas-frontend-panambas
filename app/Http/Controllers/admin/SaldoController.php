<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order; // Pastiin manggil model Order
use Carbon\Carbon;    // Pastiin manggil Carbon buat ngurus tanggal

class SaldoController extends Controller
{
    public function index(Request $request)
    {
        // 1. SETTING FILTER TANGGAL (DEFAULT: BULAN INI)
        $startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
        $dateDisplay = Carbon::now()->startOfMonth()->format('d/m/Y') . ' - ' . Carbon::now()->endOfMonth()->format('d/m/Y');

        if ($request->filled('date_range')) {
            $dateDisplay = $request->date_range;
            $parts = explode(' - ', $request->date_range);
            $startDate = Carbon::createFromFormat('d/m/Y', trim($parts[0]))->format('Y-m-d');
            $endDate = Carbon::createFromFormat('d/m/Y', trim($parts[1]))->format('Y-m-d');
        }

        // 2. QUERY AMBIL PESANAN SELESAI UNTUK RIWAYAT SALDO
        // Kita filter berdasarkan keyword pencarian invoice jika ada
        $query = Order::where('status', 'completed')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('keyword')) {
            $query->where('invoice_number', 'like', "%{$request->keyword}%");
        }

        // Ambil semua data tanpa pagination khusus buat ngitung total box atas
        $allSettledOrders = $query->get();

        // 3. LOGIKA NGITUNG SALDO BERSIH (Uang yang masuk ke ATM lu)
        $totalNetBalance = 0;
        foreach ($allSettledOrders as $order) {
            // Simulasi potongan Midtrans: misal VA Rp4.000 atau QRIS 2%
            // Di sini kita pukul rata Rp4.000 per transaksi biar aman
            $feeMidtrans = 4000; 
            $netIncome = $order->grand_total - $feeMidtrans;
            if($netIncome < 0) $netIncome = 0;
            
            $totalNetBalance += $netIncome;
        }

        // Paginate datanya buat ditampilin di tabel bawah (10 data per halaman)
        $orders = $query->paginate(20);

        return view('admin.saldo.index', compact('orders', 'totalNetBalance', 'dateDisplay'));
    }
}