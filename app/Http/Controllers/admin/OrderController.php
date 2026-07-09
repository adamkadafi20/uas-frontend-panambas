<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http; 
use Illuminate\Support\Facades\Log; 
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::query();

        // LOGIKA FILTER STATUS YANG DIBENERIN
        if ($request->has('status') && $request->status != '') {
            // Kalau masuk ke tab Pengembalian/Pembatalan, tarik semua 3 status ini
            if (in_array($request->status, ['cancelled', 'refund_processing', 'refunded'])) {
                $query->whereIn('status', ['cancelled', 'refund_processing', 'refunded']);
            } else {
                // Selain itu, cari status yang normal
                $query->where('status', $request->status);
            }
        }

        // (Opsional) Logika Pencarian Keyword kalau lu mau search box nya jalan
        if ($request->has('keyword') && $request->keyword != '') {
            $keyword = $request->keyword;
            $query->where(function($q) use ($keyword) {
                $q->where('invoice_number', 'like', "%{$keyword}%")
                  ->orWhere('receiver_name', 'like', "%{$keyword}%");
            });
        }

        $orders = $query->latest()->paginate(10);

        // Hitung total buat badge di tab menu
        $processingCount = Order::where('status', 'processing')->count();
        $shippedCount = Order::where('status', 'shipped')->count();

        return view('admin.orders.list', compact('orders', 'processingCount', 'shippedCount'));
    }

    public function show($orderId)
    {
        $order = Order::with(['orderItems.product', 'user'])->find($orderId);
        
        if (empty($order)) {
            return redirect()->route('orders.index')->with('error', 'Order not found');
        }
        
        return view('admin.orders.detail', compact('order'));
    }

    public function edit($orderId)
    {
        $order = Order::find($orderId);
        
        if (empty($order)) {
            return redirect()->route('orders.index')->with('error', 'Order not found');
        }
        
        return view('admin.orders.edit', compact('order'));
    }

    public function update($orderId, Request $request) 
    {
        $order = Order::find($orderId);

        if (empty($order)) {
            session()->flash('error', 'Order not found');
            return redirect()->route('orders.index');
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,processing,dikemas,diantar,shipped,delivered,completed,cancelled',
            'payment_status' => 'required|in:pending,paid,failed',
        ]);

        if ($validator->passes()) {
            // 🌟 LOGIKA TOLAK PENGAJUAN OLEH SELLER
            if ($request->has('admin_action') && $request->admin_action == 'reject_cancel') {
                $order->cancel_request_status = 'rejected'; // Tandain kalau udah ditolak
                $order->save();
                
                session()->flash('success', 'Pengajuan pembatalan ditolak. Pesanan kembali diproses.');
                return redirect()->route('orders.index'); // Lempar balik ke list.blade.php
            }

            $order->status = $request->status;
            $order->payment_status = $request->payment_status;

            if ($request->has('shipping_method')) { $order->shipping_method = $request->shipping_method; }
            if ($request->has('resi_number')) { $order->resi_number = $request->resi_number; }
            if (in_array($request->status, ['shipped', 'diantar']) && empty($order->shipped_date)) {
                $order->shipped_date = now();
            }
            
            $order->save();

            session()->flash('success', 'Status pesanan berhasil diperbarui!');
            return redirect()->back();
        } else {
            return redirect()->back()->withErrors($validator)->withInput();
        }
    }

    public function destroy($orderId)
    {
        $order = Order::find($orderId);

        if (empty($order)) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found'
            ]);
        }

        OrderItem::where('order_id', $orderId)->delete();
        $order->delete();

        session()->flash('success', 'Order deleted successfully');

        return response()->json([
            'status' => true,
            'message' => 'Order deleted successfully'
        ]);
    }

    public function sendInvoiceEmail($orderId, Request $request)
    {
        $order = Order::find($orderId);

        if (empty($order)) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found'
            ]);
        }
        
        session()->flash('success', 'Invoice email sent successfully');

        return response()->json([
            'status' => true,
            'message' => 'Invoice email sent successfully'
        ]);
    }

    public function massShipping() 
    {
        $orders = Order::with('orderItems')->whereIn('status', ['processing', 'shipped'])->orderBy('created_at', 'DESC')->get();
        return view('admin.orders.mass_shipping', ['orders' => $orders]);
    }

    public function updateShippingStatus(Request $request, $orderId)
    {
        $order = Order::findOrFail($orderId);
        $order->status = $request->status; 
        $order->save();

        return redirect()->back()->with('success', 'Status pesanan diupdate ke: ' . $request->status);
    }

    public function printLabel($orderId) 
    {
        $order = Order::with('orderItems')->findOrFail($orderId); 
        return view('admin.orders.print_label', compact('order'));
    }

    // =================================================================
    // FUNGSI KHUSUS SELLER (ADMIN) 
    // =================================================================
    public function cancelOrder(Request $request, $orderId)
    {
        $order = \App\Models\Order::with('orderItems.product')->findOrFail($orderId);

        // Hanya proses kalau statusnya belum batal
        if ($order->status != 'cancelled') {

            // 1. KEMBALIKAN STOK BARANG (RESTOCK) 
            // HANYA JIKA PESANAN SUDAH DIBAYAR (Stok sudah pernah terpotong)
            if ($order->payment_status == 'paid' || in_array($order->status, ['processing', 'shipped'])) {
                $orderItems = $order->orderItems ?? $order->items;
                if($orderItems) {
                    foreach ($orderItems as $item) {
                        if ($item->product) {
                            // Kalau alasan "Barang Kosong", set stok jadi 0. Selain itu, balikin sesuai QTY order
                            if ($request->cancel_reason == 'Barang Kosong') {
                                $item->product->qty = 0;
                            } else {
                                $item->product->qty += $item->qty; 
                            }
                            $item->product->save();
                        }
                    }
                }
            }

            // 2. LOGIKA REFUND MIDTRANS JIKA SUDAH BAYAR
            if ($order->payment_status == 'paid') {
                $serverKey = env('MIDTRANS_SERVER_KEY');
                $isProduction = env('MIDTRANS_IS_PRODUCTION', false);
                $baseUrl = $isProduction ? 'https://api.midtrans.com/v2' : 'https://api.sandbox.midtrans.com/v2';
                
                // Nembak API Refund Midtrans
                $response = \Illuminate\Support\Facades\Http::withBasicAuth($serverKey, '')
                    ->withHeaders([
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                    ])
                    ->post("$baseUrl/{$order->invoice_number}/refund", [
                        'refund_key' => 'REF-' . time(),
                        'reason' => 'Dibatalkan Penjual: ' . $request->cancel_reason
                    ]);

                $responseData = $response->json();

                // Cek apakah Refund sukses
                if ($response->successful() && isset($responseData['status_code']) && $responseData['status_code'] == '200') {
                    $order->refund_status = 'success'; // Midtrans berhasil balikin duit
                } else {
                    \Illuminate\Support\Facades\Log::error('Refund Failed: ', $responseData ?? []);
                    $order->refund_status = 'manual'; // Butuh transfer manual
                }
            }

            // 3. Ubah status pesanan dan simpan
            $order->status = 'cancelled';
            $order->cancel_reason = $request->cancel_reason; 
            $order->cancelled_by = 'seller'; 
            
            // Kalau batal dari pengajuan pembeli, update status pengajuannya
            if ($order->cancel_request_status == 'requested') {
                $order->cancel_request_status = 'approved';
            }

            $order->save();

            return redirect()->route('orders.index', ['status' => 'cancelled'])->with('success', 'Pesanan dibatalkan. (Stok barang dikembalikan jika sudah dibayar).');
        }

        return redirect()->back()->with('error', 'Pesanan sudah berstatus batal.');
    }

    public function massPrint(Request $request) 
    {
        // Cek kalau admin nggak milih pesanan sama sekali
        if (empty($request->order_ids)) {
            return redirect()->back()->with('error', 'Pilih minimal satu pesanan untuk dicetak!');
        }

        // Ambil data pesanan sesuai ID yang dicentang
        $orders = Order::with('orderItems.product')->whereIn('id', $request->order_ids)->get();
        
        return view('admin.orders.mass_print_label', compact('orders'));
    }

    // =================================================================
    // FUNGSI KHUSUS PEMBELI (BUYER)
    // =================================================================
    
    // 1. FUNGSI UNTUK MENGAJUKAN PEMBATALAN (DARI MODAL FORM PEMBELI)
    public function buyerCancelOrder(Request $request) 
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'cancel_reason' => 'required|string',
        ]);

        $order = Order::with('orderItems.product')->findOrFail($request->order_id);

        // Kalau status masih PENDING (Belum Bayar), bisa batal langsung
        if ($order->status == 'pending') {
            
            // 🌟 LOGIKA KEMBALIKAN STOK BARANG (DIHAPUS)
            // Stok tidak dikembalikan karena status PENDING belum memotong stok.

            $order->status = 'cancelled';
            $order->cancel_reason = $request->cancel_reason;
            $order->cancelled_by = 'buyer';
            $order->save();

            return redirect()->back()->with('success', 'Pesanan berhasil dibatalkan.');
        } 
        // Kalau status PROCESSING (Sedang Dikemas), jadi pengajuan ke seller
        elseif ($order->status == 'processing') {
            // BACKEND SECURITY: Tolak kalau udah pernah ngajuin
            if (!empty($order->cancel_request_status)) {
                return redirect()->back()->with('error', 'Kamu hanya bisa mengajukan pembatalan 1 kali.');
            }

            $order->cancel_request_status = 'requested';
            $order->cancel_reason = $request->cancel_reason;
            $order->save();
            return redirect()->back()->with('success', 'Pengajuan pembatalan berhasil dikirim ke penjual.');
        }

        return redirect()->back()->with('error', 'Pesanan tidak dapat dibatalkan saat ini.');
    }

    // 2. FUNGSI UNTUK PEMBELI MEMBATALKAN PENGAJUAN SENDIRI (BALIK KE SEDANG DIKEMAS)
    public function undoCancelRequest($id) 
    {
        $order = Order::with('refund')->findOrFail($id);

        // Jika itu pembatalan pesanan sebelum dikirim
        if ($order->status == 'processing' && $order->cancel_request_status == 'requested') {
            $order->cancel_request_status = 'undone';
            $order->save();
            return response()->json(['success' => true, 'message' => 'Pengajuan dibatalkan.']);
        }
        
        // Jika itu penarikan pengajuan REFUND (Setelah selesai)
        if ($order->status == 'refund_processing') {
            $order->status = 'completed'; // Balikin status order ke selesai
            $order->save();

            // Ubah status tabel refund jadi ditarik pembeli
            if ($order->refund) {
                $order->refund->update(['status' => 'undone']);
            }

            return response()->json(['success' => true, 'message' => 'Pengajuan refund berhasil ditarik.']);
        }

        return response()->json(['success' => false, 'message' => 'Aksi tidak valid atau pesanan sudah berubah status.'], 400);
    }

    public function submitRefund(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        // Upload Video (Maks 20MB biar server gak jebol)
        $videoPath = null;
        if ($request->hasFile('video')) {
            $videoPath = $request->file('video')->store('refunds/videos', 'public');
        }

        // Upload Foto-foto (Bisa multiple)
        $photoPaths = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $photoPaths[] = $photo->store('refunds/photos', 'public');
            }
        }

        // Simpan data ke tabel Refunds
        \App\Models\Refund::create([
            'order_id' => $order->id,
            'user_id' => auth()->id(),
            'type' => $request->type,
            'reason' => $request->reason,
            'items' => $request->items, 
            'amount' => $request->amount,
            'photos' => json_encode($photoPaths),
            'video' => $videoPath,
            'status' => 'pending'
        ]);

        // Ubah status order jadi proses pengajuan
        $order->status = 'refund_processing';
        $order->save();

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan pengembalian berhasil dikirim!'
        ]);
    }

    public function approveRefund(Request $request, $id)
    {
        $order = Order::with(['refund', 'orderItems.product'])->findOrFail($id);

        // Pastikan pembeli beneran udah ngajuin refund
        if (!$order->refund) {
            return redirect()->back()->with('error', 'Data pengajuan pengembalian tidak ditemukan.');
        }

        // AKSI 1: KEMBALIKAN DANA SAJA (Tipe 1)
        if ($request->action == 'approve_dana_saja') {
            $order->is_refunded = true;
            $order->refund_amount = $order->refund->amount;
            $order->status = 'completed'; // Status dibalikin ke selesai karena masalah udah clear
            $order->save();

            $order->refund->update(['status' => 'approved']);

            return redirect()->back()->with('success', 'Pengajuan disetujui! Dana berhasil dikembalikan sebagian. Status pesanan kembali Selesai.');
        } 
        // AKSI 2: TUNGGU BARANG KEMBALI (Tipe 2 - Fase 1)
        elseif ($request->action == 'wait_return') {
            $order->refund_status = 'waiting_return';
            $order->save();

            $order->refund->update(['status' => 'waiting_return']);

            return redirect()->back()->with('success', 'Status diubah! Silahkan tunggu pembeli mengirimkan barang retur.');
        }
        // AKSI 3: BARANG DITERIMA & REFUND FULL (Tipe 2 - Fase 2)
        elseif ($request->action == 'approve_barang_dana') {
            
            // 🌟 LOGIKA KEMBALIKAN STOK BARANG (RESTOCK FULL) KARENA DIRETUR
            $orderItems = $order->orderItems ?? $order->items;
            if($orderItems) {
                foreach ($orderItems as $item) {
                    if ($item->product) {
                        $item->product->qty += $item->qty; // Balikin stoknya ke toko
                        $item->product->save();
                    }
                }
            }

            $order->is_refunded = true;
            $order->status = 'refunded'; // Status order beneran jadi dikembalikan
            $order->refund_amount = $order->grand_total;
            $order->grand_total = 0; // Nota di-Nol-kan
            
            // FIX ERROR: Ganti nama kolom jadi 'shipping' (bukan shipping_cost)
            $order->shipping = 0; 
            
            $order->save();

            $order->refund->update(['status' => 'approved']);

            return redirect()->route('orders.index')->with('success', 'Barang retur diterima. Pesanan dibatalkan, Dana & Stok berhasil dikembalikan!');
        }

        return redirect()->back()->with('error', 'Aksi tidak dikenali.');
    }
}