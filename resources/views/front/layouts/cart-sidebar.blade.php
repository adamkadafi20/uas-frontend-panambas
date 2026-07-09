@php
    $isCheckoutPage = request()->is('checkout');
    
    $cartItems = collect();
    $cartCount = 0;
    $cartTotal = 0;
    $deliveryFee = 20000; // Default ongkir asumsi Jawa
    $recommendations = collect();
    
    if(Auth::check() && \Illuminate\Support\Facades\Schema::hasTable('carts') && !$isCheckoutPage) {
        $cartItems = \App\Models\Cart::with(['product.images', 'product.variations'])->where('user_id', Auth::id())->get();
        $cartCount = $cartItems->count();
        
        foreach($cartItems as $item) {
            $price = $item->product->price;
            $varName = '';
            if($item->variation_id) {
                $var = $item->product->variations->where('id', $item->variation_id)->first();
                if($var) { 
                    $price = $var->price; 
                    $varName = $var->variation_option; 
                }
            }
            $cartTotal += $price * $item->qty;
            $item->calculated_price = $price;
            $item->var_name = $varName;
        }

        // UPDATE LOGIKA: Gratis ongkir kalau belanja >= 500.000
        if($cartTotal >= 500000) { $deliveryFee = 0; }

        if($cartCount > 0) {
            $categoryIds = $cartItems->pluck('product.category_id')->unique();
            $cartProductIds = $cartItems->pluck('product_id')->toArray();
            $recommendations = \App\Models\Product::with(['images', 'variations'])
                ->whereIn('category_id', $categoryIds)
                ->whereNotIn('id', $cartProductIds)
                ->inRandomOrder()->limit(4)->get();
        }
    }
@endphp

<div id="cartSidebarOverlay" class="fixed inset-0 bg-black/50 z-[1050] hidden transition-opacity opacity-0" onclick="toggleCart()"></div>

<div id="cartSidebar" class="fixed top-0 right-0 h-full w-full md:w-[450px] bg-white z-[1100] transform translate-x-full transition-transform duration-300 ease-in-out shadow-2xl flex flex-col">
    
    <div class="flex justify-between items-center p-6 border-b border-gray-100">
        <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Keranjang Anda</h2>
        <button onclick="toggleCart()" class="text-gray-400 hover:text-gray-800 transition"><i class="fas fa-times text-xl"></i></button>
    </div>

    <div class="flex-1 overflow-y-auto hide-scroll p-6">
        @forelse($cartItems as $item)
            <div class="flex gap-4 mb-6 relative group border-b border-gray-50 pb-4">
                <div class="w-24 h-24 bg-gray-50 flex-shrink-0 rounded-sm overflow-hidden border border-gray-100">
                    <img src="{{ isset($item->product->images) && $item->product->images->isNotEmpty() ? asset('storage/'.$item->product->images->first()->image_path) : '' }}" class="w-full h-full object-cover">
                </div>
                <div class="flex-1 flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-start">
                            <a href="{{ url('/product/'.$item->product->id) }}" class="text-[#247a6b] font-bold text-sm leading-tight pr-4 hover:underline">
                                {{ $item->product->title }}
                            </a>
                            <p class="font-bold text-gray-900 text-sm">Rp{{ number_format($item->calculated_price, 0, ',', '.') }}</p>
                        </div>
                        @if($item->var_name) <p class="text-gray-500 text-xs mt-1">Varian: {{ $item->var_name }}</p> @endif
                    </div>
                    <div class="flex justify-between items-end mt-2">
                        <button onclick="removeCartItem({{ $item->id }})" class="text-gray-400 hover:text-red-500 transition text-sm flex items-center">
                            <i class="far fa-trash-alt mr-1"></i> Hapus
                        </button>
                        <div class="text-gray-900 font-medium text-sm bg-gray-100 px-3 py-1 rounded-sm">Qty: {{ $item->qty }}</div>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-10 flex flex-col items-center">
                <i class="fas fa-shopping-basket text-4xl text-gray-300 mb-3"></i>
                <p class="text-gray-500 text-sm">Keranjang Anda masih kosong.</p>
                <button onclick="toggleCart()" class="mt-4 text-[#247a6b] font-medium text-sm hover:underline">Lanjut Belanja</button>
            </div>
        @endforelse

        @if($cartCount > 0)
            <div class="space-y-3 mb-8 mt-4 bg-gray-50 p-4 rounded-md border border-gray-100">
                <div class="flex justify-between text-sm text-gray-600 font-medium">
                    <span>Subtotal ({{ $cartCount }} produk)</span>
                    <span class="text-gray-900">Rp{{ number_format($cartTotal, 0, ',', '.') }}</span>
                </div>
                
                <!-- Info Pengiriman Diganti -->
                <div class="flex justify-between text-sm text-gray-500 italic">
                    <span>Pengiriman</span>
                    <span>Dihitung saat checkout</span>
                </div>
                
                <hr class="border-gray-200 my-2">
                
                <!-- Total Hanya Subtotal Barang -->
                <div class="flex justify-between text-base font-bold text-gray-900">
                    <span>Total Sementara</span>
                    <span class="text-[#247a6b] text-xl">Rp{{ number_format($cartTotal, 0, ',', '.') }}</span>
                </div>
            </div>

            @if($recommendations->count() > 0)
                <div class="mt-8">
                    <h3 class="font-bold text-gray-900 mb-4 text-sm uppercase tracking-wider">Mungkin Anda Juga Suka</h3>
                    <div class="grid grid-cols-2 gap-4">
                        @foreach($recommendations as $rec)
                            <div class="border border-gray-100 rounded-sm p-2">
                                <a href="{{ url('/product/'.$rec->id) }}" class="block w-full h-[100px] bg-gray-50 mb-2 overflow-hidden">
                                    <img src="{{ isset($rec->images) && $rec->images->isNotEmpty() ? asset('storage/'.$rec->images->first()->image_path) : '' }}" class="w-full h-full object-cover">
                                </a>
                                <p class="text-gray-900 font-bold text-xs truncate">{{ $rec->title }}</p>
                                <p class="text-[#247a6b] font-bold text-sm mb-2">
                                    Rp{{ number_format($rec->variations->isNotEmpty() ? $rec->variations->min('price') : $rec->price, 0, ',', '.') }}
                                </p>
                                <button onclick="addToCartDirect({{ $rec->id }})" class="w-full bg-white border border-[#247a6b] text-[#247a6b] font-bold py-1.5 text-[11px] rounded-sm hover:bg-[#247a6b] hover:text-white transition">
                                    Tambah
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endif
    </div>

    <div class="p-6 border-t border-gray-100 bg-white shadow-[0_-10px_20px_rgba(0,0,0,0.02)] relative">
        
        @if($cartCount > 0 && $cartTotal < 100000)
            <div class="mb-4 px-3 py-2.5 bg-yellow-50 border border-yellow-200 rounded-sm text-yellow-700 text-[11px] md:text-xs flex items-start gap-2 shadow-sm">
                <i class="fas fa-info-circle mt-0.5 text-yellow-600"></i>
                <p>
                    Tambah belanjaan <strong>Rp{{ number_format(100000 - $cartTotal, 0, ',', '.') }}</strong> lagi yuk! Minimal total transaksi Rp100.000 untuk bisa lanjut ke pembayaran.
                </p>
            </div>
        @endif

        <a href="{{ route('checkout') }}" class="w-full text-white font-bold py-3.5 rounded-sm transition flex justify-center items-center shadow-sm {{ $cartTotal >= 100000 ? 'bg-[#247a6b] hover:bg-[#1b5e52]' : 'bg-gray-400 cursor-not-allowed pointer-events-none' }}">
            <i class="fas fa-lock mr-2 text-sm"></i> Lanjut ke Pembayaran
        </a>
    </div>
</div>