<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Banner;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http; // <-- INI OBATNYA BIAR BISA NEMBAK API
use Midtrans\Config;
use Midtrans\Snap;
use App\Models\ProductRating;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class FrontController extends Controller
{
    // ====================================================================
    // LINK API RAILWAY LU (Ganti kalau ada perubahan link)
    // ====================================================================
    private $apiUrl = 'https://uas-backend-panambas-production.up.railway.app/api';

    public function index()
    {
        // LOGIKA BARU: Tarik data dari API Node.js Railway
        // Pakai try-catch biar kalau API lagi mati, web lu gak ikut error 500
        try {
            // 1. Tarik response utuhnya dulu dari API
            $resBanners = Http::get($this->apiUrl . '/banners')->object();
            $resLatest = Http::get($this->apiUrl . '/products/latest')->object();
            $resFeatured = Http::get($this->apiUrl . '/products/bestseller')->object();

            // 2. Bungkus pake collect() dan HIPNOTIS jadi Model biar bawa Gambar
            $banners = collect(isset($resBanners->data) ? $resBanners->data : (is_array($resBanners) ? $resBanners : []));
            
            $latestProducts = collect(isset($resLatest->data) ? $resLatest->data : (is_array($resLatest) ? $resLatest : []))
                ->map(function ($item) {
                    $product = new \App\Models\Product();
                    $product->forceFill((array) $item);
                    $product->exists = true;
                    $product->load(['images']); // Bawa gambar
                    return $product;
                });

            $featuredProducts = collect(isset($resFeatured->data) ? $resFeatured->data : (is_array($resFeatured) ? $resFeatured : []))
                ->map(function ($item) {
                    $product = new \App\Models\Product();
                    $product->forceFill((array) $item);
                    $product->exists = true;
                    $product->load(['images']); // Bawa gambar
                    return $product;
                });
            
        } catch (\Exception $e) {
            // Kalau API lagi mati, kasih Collection kosong biar web nggak hancur
            $banners = collect([]);
            $latestProducts = collect([]);
            $featuredProducts = collect([]);
        }

        // SEMENTARA KITA PAKE DATA DUMMY DULU BIAR TAMPILANNYA KELIHATAN
        $reviews = collect([
            (object)['name' => 'Michelle Matthews', 'time' => '20 jam yang lalu', 'title' => 'Tanaman super segar!', 'content' => 'Bener-bener di luar ekspektasi. Jengkol Rusianya sampai dalam kondisi sangat bugar dan packingnya juara!'],
            (object)['name' => 'Elena Nastase', 'time' => '2 hari yang lalu', 'title' => 'Kado terbaik 🤩', 'content' => 'Beliin bibit pohon buat kado ultah temen. Dia seneng banget! Pelayanan Panambas emang top banget.'],
            (object)['name' => 'Tracey Hodgson', 'time' => '2 hari yang lalu', 'title' => 'Tiba tepat waktu', 'content' => 'Pengiriman cepat, tanaman sehat, potnya juga aman nggak ada yang pecah. Sangat direkomendasikan!'],
            (object)['name' => 'Adam Kadafi', 'time' => '3 hari yang lalu', 'title' => 'Adminnya ramah', 'content' => 'Sempet bingung cara perawatannya, pas nanya admin langsung dijawab detail banget. Bintang 5!']
        ]);

        return view('front.home', compact('featuredProducts', 'latestProducts', 'banners', 'reviews'));
    }

    public function shop(Request $request)
    {
        // 1. Tangkap Kategori, Kata Kunci, & Sinyal Top 20 dari URL
        $categorySelected = $request->get('category');
        $searchKeyword = $request->get('search'); 
        $isTop20 = $request->has('top20'); 

        // 2. LOGIKA BARU: Tembak ke API buat ambil data produk di halaman Shop
        try {
            $response = Http::get($this->apiUrl . '/products', [
                'category' => $categorySelected,
                'search' => $searchKeyword,
                'top20' => $isTop20 ? 'true' : 'false'
            ]);
            
            // 1. Ambil datanya dan Hipnotis Massal
            $apiData = $response->object();
            $itemsArray = isset($apiData->data) ? $apiData->data : (is_array($apiData) ? $apiData : []);
            
            $items = collect($itemsArray)->map(function ($item) {
                $product = new \App\Models\Product();
                $product->forceFill((array) $item);
                $product->exists = true;
                $product->load(['images', 'variations']); // Trik hipnotis biar bawa relasi
                return $product;
            });
            
            // 2. Bikin Pagination buatan biar fungsi ->total() di Blade nggak error
            $limit = $isTop20 ? 20 : 12;
            $currentPage = \Illuminate\Pagination\Paginator::resolveCurrentPage();
            $currentItems = $items->slice(($currentPage - 1) * $limit, $limit)->all();
            
            $products = new \Illuminate\Pagination\LengthAwarePaginator($currentItems, $items->count(), $limit, $currentPage, [
                'path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(),
                'query' => $request->query() // Biar kalau di-klik halaman 2, filter pencariannya nggak hilang
            ]);

        } catch (\Exception $e) {
            // Kalau API mati, balikin pagination kosong
            $products = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 12);
        }

        // ====================================================================
        // 4. BIKIN FILTER OTOMATIS NGITUNG JUMLAH BARANG (Tetap Pakai Database Lokal)
        // ====================================================================
        $filterQuery = \App\Models\Product::query();
        if (!empty($categorySelected)) {
            $filterQuery->where('category_id', 'LIKE', $categorySelected . '%');
        }

        if (!empty($searchKeyword)) {
            $filterQuery->where('title', 'LIKE', '%' . $searchKeyword . '%');
        }

        $brandsCount = (clone $filterQuery)->select('brand', \DB::raw('count(*) as total'))->whereNotNull('brand')->where('brand', '!=', '')->groupBy('brand')->pluck('total', 'brand');
        $sizesCount = (clone $filterQuery)->select('size', \DB::raw('count(*) as total'))->whereNotNull('size')->where('size', '!=', '')->groupBy('size')->pluck('total', 'size');
        $lightReqsCount = (clone $filterQuery)->select('light_requirement', \DB::raw('count(*) as total'))->whereNotNull('light_requirement')->where('light_requirement', '!=', '')->groupBy('light_requirement')->pluck('total', 'light_requirement');
        $careLevelsCount = (clone $filterQuery)->select('care_level', \DB::raw('count(*) as total'))->whereNotNull('care_level')->where('care_level', '!=', '')->groupBy('care_level')->pluck('total', 'care_level');
        $originsCount = (clone $filterQuery)->select('origin', \DB::raw('count(*) as total'))->whereNotNull('origin')->where('origin', '!=', '')->groupBy('origin')->pluck('total', 'origin');
        $bentukPupukCount = (clone $filterQuery)->select('bentuk_pupuk', \DB::raw('count(*) as total'))->whereNotNull('bentuk_pupuk')->where('bentuk_pupuk', '!=', '')->groupBy('bentuk_pupuk')->pluck('total', 'bentuk_pupuk');

        return view('front.shop', compact(
            'products', 'categorySelected', 'searchKeyword',
            'brandsCount', 'sizesCount', 'lightReqsCount', 'careLevelsCount', 'originsCount', 'bentukPupukCount', 'isTop20'
        ));
    }

public function product($id) 
    {
        // LOGIKA BARU: Tembak data 1 Produk Spesifik dari API
        try {
            $apiResponse = Http::get($this->apiUrl . '/products/' . $id)->object();
            
            // Cek apakah API gagal atau datanya kosong
            if (!$apiResponse || !isset($apiResponse->success) || !$apiResponse->success || empty($apiResponse->data)) {
                abort(404);
            }

            // MAGIS DI SINI: Hipnotis data mentah API jadi Model Pintar Laravel
            $product = new \App\Models\Product();
            // Masukin data API paksa ke model
            $product->forceFill((array) $apiResponse->data);
            // Kasih tau Laravel kalau barang ini beneran ada di database (bukan barang baru)
            $product->exists = true; 
            
            // Tarik otomatis relasi gambar dan variasi biar Blade (tampilan) lu seneng!
            $product->load(['images', 'variations']);

        } catch (\Exception $e) {
            abort(404);
        }
        
        // Catet ke tabel tracking (product_views) buat analitik
        \App\Models\ProductView::create([
            'product_id' => $product->id,
            'session_id' => session()->getId(),
            'ip_address' => request()->ip() 
        ]);

        $relatedProducts = collect(); // Bikin wadah kosong dulu
        
        if (!empty($product->category_id)) {
            $mainCategory = explode(' > ', $product->category_id)[0];
            
            // Tarik data Related Products dari Database Lokal (Lebih Cepat)
            $relatedProducts = \App\Models\Product::where('category_id', 'LIKE', $mainCategory . '%')
                                    ->where('id', '!=', $product->id)
                                    ->with(['images', 'variations'])
                                    ->orderBy('sold', 'desc') 
                                    ->take(8) 
                                    ->get();
        }
        
        return view('front.product', compact('product', 'relatedProducts'));
    }

    public function cart()
    {
        return redirect()->route('front.home');
    }

    // ====================================================================
    // BAGIAN KERANJANG, CHECKOUT & MIDTRANS TETAP PAKAI LARAVEL
    // Biar fungsi belanja dan bayar lu nggak berantakan.
    // ====================================================================

    public function checkout()
    {
        $userId = \Illuminate\Support\Facades\Auth::id();
        $cartItems = \App\Models\Cart::with(['product', 'product.variations'])->where('user_id', $userId)->get();
        
        $cartTotal = 0;
        foreach($cartItems as $item) {
            $price = $item->product->price;
            $varName = null;
            if($item->variation_id) {
                $var = $item->product->variations->where('id', $item->variation_id)->first();
                if($var) { $price = $var->price; $varName = $var->variation_option; }
            }
            $item->calculated_price = $price;
            $item->var_name = $varName;
            $cartTotal += $price * $item->qty;
        }

        if ($cartTotal < 100000) {
            session()->flash('error', 'Minimal belanja adalah Rp100.000, Bre!');
            return redirect()->route('front.home');
        }

        $addresses = \App\Models\Address::where('user_id', $userId)->orderBy('is_primary', 'desc')->get();
        $deliveryFee = ($cartTotal >= 500000) ? 0 : 20000;
        $countries = \App\Models\Country::orderBy('name', 'asc')->get();
        
        return view('front.checkout', compact('countries', 'cartItems', 'cartTotal', 'deliveryFee', 'addresses'));
    }

    public function thanks($orderId)
    {
        $order = \App\Models\Order::findOrFail($orderId);
        return view('front.thanks', compact('orderId', 'order'));
    }

    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'qty' => 'required|integer|min:1'
        ]);

        $userId = Auth::id();
        $cart = \App\Models\Cart::where('user_id', $userId)
            ->where('product_id', $request->product_id)
            ->where('variation_id', $request->variation_id)
            ->first();

        if ($cart) {
            $cart->qty += $request->qty;
            $cart->save();
        } else {
            \App\Models\Cart::create([
                'user_id' => $userId,
                'product_id' => $request->product_id,
                'variation_id' => $request->variation_id,
                'qty' => $request->qty
            ]);
        }

        $cartCount = \App\Models\Cart::where('user_id', $userId)->sum('qty');
        
        return response()->json([
            'success' => true,
            'message' => 'Berhasil dimasukkan ke keranjang!',
            'cart_count' => $cartCount
        ]);
    }

    public function removeCart(Request $request)
    {
        $cart = \App\Models\Cart::find($request->input('cart_id'));
        if ($cart && $cart->user_id == Auth::id()) {
            $cart->delete();
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false, 'message' => 'Barang gagal dihapus.']);
    }

    public function storeAddress(Request $request)
    {
        $request->validate([
            'receiver_name'  => 'required|string|max:255',
            'phone'          => 'required|string|max:20',
            'province'       => 'required|string',
            'city'           => 'required|string',
            'district'       => 'required|string',
            'postal_code'    => 'required|string|max:10',
            'detail_address' => 'required|string'
        ]);

        if ($request->filled('address_id')) {
            $address = \App\Models\Address::where('id', $request->address_id)->where('user_id', \Illuminate\Support\Facades\Auth::id())->first();
            if ($address) {
                $address->update([
                    'receiver_name'  => $request->receiver_name,
                    'phone'          => $request->phone,
                    'province'       => $request->province,
                    'city'           => $request->city,
                    'district'       => $request->district,
                    'postal_code'    => $request->postal_code,
                    'detail_address' => $request->detail_address,
                ]);
            }
            $message = 'Alamat berhasil diubah Bre!';
        } else {
            $isFirst = \App\Models\Address::where('user_id', \Illuminate\Support\Facades\Auth::id())->count() == 0;
            \App\Models\Address::create([
                'user_id'        => \Illuminate\Support\Facades\Auth::id(),
                'receiver_name'  => $request->receiver_name,
                'phone'          => $request->phone,
                'province'       => $request->province,
                'city'           => $request->city,
                'district'       => $request->district,
                'postal_code'    => $request->postal_code,
                'detail_address' => $request->detail_address,
                'is_primary'     => $isFirst
            ]);
            $message = 'Alamat baru berhasil disimpan Bre!';
        }

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    public function processOrder(Request $request)
    {
        $userId = \Illuminate\Support\Facades\Auth::id();
        $cartItems = \App\Models\Cart::with('product', 'product.variations')->where('user_id', $userId)->get();
        
        if ($cartItems->count() == 0) {
            return redirect()->route('front.home')->with('error', 'Keranjang lu kosong Bre!');
        }

        $subTotal = 0;
        foreach($cartItems as $item) {
            $price = $item->product->price;
            if($item->variation_id) {
                $var = $item->product->variations->where('id', $item->variation_id)->first();
                if($var) $price = $var->price;
            }
            $subTotal += $price * $item->qty;
        }
        
        $listProvinsiJawa = ['DKI JAKARTA', 'JAWA BARAT', 'JAWA TENGAH', 'DI YOGYAKARTA', 'JAWA TIMUR', 'BANTEN'];
        $provinsi = strtoupper($request->province);
        
        $deliveryFee = 0;
        if ($subTotal < 500000) {
            $deliveryFee = in_array($provinsi, $listProvinsiJawa) ? 20000 : 35000;
        }
        $grandTotal = $subTotal + $deliveryFee;

        $invoiceNumber = 'INV-' . date('Ymd') . '-' . rand(1000, 9999);

        $order = new \App\Models\Order;
        $order->user_id = $userId;
        $order->invoice_number = $invoiceNumber;
        $order->subtotal = $subTotal;
        $order->shipping = $deliveryFee;
        $order->grand_total = $grandTotal;
        $order->payment_method = $request->payment; 
        $order->payment_status = 'pending';
        $order->status = 'pending'; 
        
        $order->receiver_name = $request->name;
        $order->phone = $request->phone;
        $order->full_address = $request->address;
        $order->province = $request->province;
        $order->save();

        foreach($cartItems as $item) {
            $orderItem = new \App\Models\OrderItem;
            $orderItem->order_id = $order->id;
            $orderItem->product_id = $item->product_id;
            $orderItem->variation_id = $item->variation_id;
            $orderItem->product_name = $item->product->title;
            $orderItem->qty = $item->qty;

            $price = $item->product->price;
            $varName = null;
            if($item->variation_id) {
                $var = $item->product->variations->where('id', $item->variation_id)->first();
                if($var) {
                    $price = $var->price;
                    $varName = $var->variation_option;
                }
            }
            
            $orderItem->variation_name = $varName; 
            $orderItem->price = $price;
            $orderItem->total = $price * $item->qty;
            $orderItem->save();
        }

        \App\Models\Cart::where('user_id', $userId)->delete();

        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false); 
        Config::$isSanitized = env('MIDTRANS_IS_SANITIZED', true);
        Config::$is3ds = env('MIDTRANS_IS_3DS', true);

        $params = [
            'transaction_details' => [
                'order_id' => $invoiceNumber,
                'gross_amount' => $grandTotal,
            ],
            'customer_details' => [
                'first_name' => $request->name,
                'phone' => $request->phone,
            ],
        ];

        $snapToken = Snap::getSnapToken($params);
        $order->snap_token = $snapToken; 
        $order->save();

        $orderId = $order->id;
        return view('front.payment', compact('snapToken', 'order', 'orderId'));
    }

    public function myOrders(Request $request)
    {
        $userId = \Illuminate\Support\Facades\Auth::id();
        $status = $request->get('status', 'semua');
        
        $ordersQuery = \App\Models\Order::with(['orderItems', 'orderItems.product.images'])
                                        ->where('user_id', $userId)
                                        ->orderBy('created_at', 'desc');
        
        if ($status != 'semua') {
            $ordersQuery->where('status', $status);
        }
        
        $orders = $ordersQuery->get();
        return view('front.pesanan', compact('orders', 'status'));
    }

  public function midtransCallback(Request $request)
    {
        $serverKey = env('MIDTRANS_SERVER_KEY');
        $hashed = hash("sha512", $request->order_id . $request->status_code . $request->gross_amount . $serverKey);
        
        if ($hashed == $request->signature_key) {
            $order = \App\Models\Order::with('orderItems')->where('invoice_number', $request->order_id)->first();
                
            if ($order) {
                $transactionStatus = $request->transaction_status;

                if ($transactionStatus == 'settlement' || $transactionStatus == 'capture') {
                    if ($order->payment_status != 'paid') {
                        foreach ($order->orderItems as $item) {
                            $product = \App\Models\Product::find($item->product_id);
                            if ($product) {
                                $product->sold = $product->sold + $item->qty;

                                if ($item->variation_id) {
                                    $variation = \App\Models\ProductVariation::find($item->variation_id);
                                    if ($variation) {
                                        $variation->stock = $variation->stock - $item->qty;
                                        if ($variation->stock < 0) $variation->stock = 0; 
                                        $variation->save();
                                    }
                                } else {
                                    $product->qty = $product->qty - $item->qty;
                                    if ($product->qty < 0) $product->qty = 0;
                                }
                                $product->save();
                            }
                        }
                    }

                    $order->payment_status = 'paid';
                    $order->status = 'processing'; 

                } elseif ($transactionStatus == 'pending') {
                    $order->payment_status = 'pending';
                    $order->status = 'pending'; 

                } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
                    $order->payment_status = 'failed';
                    $order->status = 'cancelled'; 
                }
                
                $order->save();
            }
        }
        return response()->json(['status' => 'success']);
    }

    public function completeOrder($id)
    {
        $order = \App\Models\Order::where('id', $id)
                    ->where('user_id', \Illuminate\Support\Facades\Auth::id())
                    ->firstOrFail();

        $order->status = 'completed';
        $order->save();

        return redirect()->back()->with('success', 'Mantap! Pesanan berhasil diselesaikan.');
    }

    public function storeRating(\Illuminate\Http\Request $request) 
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000'
        ], [
            'product_id.exists' => 'Maaf, produk ini sudah tidak tersedia atau dihapus oleh penjual.' 
        ]);

        \App\Models\ProductRating::updateOrCreate(
            [
                'product_id' => $request->product_id,
                'email' => Auth::user()->email ?? 'no-email@domain.com',
            ],
            [
                'username' => Auth::user()->name ?? 'Pembeli Anonim', 
                'rating' => $request->rating,
                'comment' => $request->comment ?? '', 
                'status' => 1
            ]
        );

        return redirect()->back()->with('success', 'Penilaian berhasil disimpan! Terima kasih ulasannya.');
    }

    public function cancelOrder(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'cancel_reason' => 'required|string',
        ]);

        $order = \App\Models\Order::where('id', $request->order_id)
                    ->where('user_id', \Illuminate\Support\Facades\Auth::id())
                    ->firstOrFail();

        if ($order->status == 'pending') {
            $order->status = 'cancelled';
            $order->cancelled_by = 'pembeli';
            $order->cancel_reason = $request->cancel_reason;
            $order->save();

            return redirect()->back()->with('success', 'Pesanan lu berhasil dibatalkan secara otomatis Bre.');
        } 
        elseif ($order->status == 'processing') {
            $order->cancel_request_status = 'requested';
            $order->cancelled_by = 'pembeli';
            $order->cancel_reason = $request->cancel_reason;
            $order->save();

            return redirect()->back()->with('success', 'Pengajuan pembatalan berhasil dikirim. Menunggu persetujuan penjual ya Bre.');
        }

        return redirect()->back()->with('error', 'Waduh, pesanan ini udah gak bisa dibatalin.');
    }
}