@extends('front.layouts.app')

@php
    // Fungsi buat nyingkat angka ribuan jadi RB
    if (!function_exists('formatRibuan')) {
        function formatRibuan($angka) {
            if ($angka >= 1000) {
                $hasil = number_format($angka / 1000, 1);
                return str_replace('.0', '', $hasil) . 'RB';
            }
            return $angka;
        }
    }

    // LOGIKA TARIK BARANG "MUNGKIN ANDA JUGA SUKA"
    // Ambil kategori dari barang pertama yang ada di orderan ini
    $firstItem = $order->orderItems->first();
    $relatedProducts = collect();
    
    if ($firstItem) {
        $productOrdered = \App\Models\Product::find($firstItem->product_id);
        if ($productOrdered && !empty($productOrdered->category_id)) {
            $mainCategory = explode(' > ', $productOrdered->category_id)[0];
            
            $relatedProducts = \App\Models\Product::where('category_id', 'LIKE', $mainCategory . '%')
                                    ->with(['images', 'variations'])
                                    ->orderBy('sold', 'desc')
                                    ->take(8)
                                    ->get();
        }
    }
@endphp

@section('title', 'Pembayaran - Panambas')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <style>
        /* Animasi Ceklis Sukses */
        .success-checkmark {
            width: 80px; height: 80px; margin: 0 auto;
            border-radius: 50%; display: block; stroke-width: 2;
            stroke: #247a6b; stroke-miterlimit: 10; margin-bottom: 20px;
            box-shadow: inset 0px 0px 0px #247a6b;
            animation: fill .4s ease-in-out .4s forwards, scale .3s ease-in-out .9s both;
        }
        .checkmark__circle {
            stroke-dasharray: 166; stroke-dashoffset: 166; stroke-width: 2; stroke-miterlimit: 10;
            stroke: #247a6b; fill: none;
            animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
        }
        .checkmark__check {
            transform-origin: 50% 50%; stroke-dasharray: 48; stroke-dashoffset: 48;
            animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards;
        }
        @keyframes stroke { 100% { stroke-dashoffset: 0; } }
        @keyframes scale { 0%, 100% { transform: none; } 50% { transform: scale3d(1.1, 1.1, 1); } }
        @keyframes fill { 100% { box-shadow: inset 0px 0px 0px 30px #e6f2f0; } }

        /* Tombol panah Swiper Produk Terkait */
        #related-section .swiper-button-next, 
        #related-section .swiper-button-prev {
            opacity: 0; transition: all 0.3s ease;
            background-color: #247a6b !important; color: #ffffff !important; 
            width: 40px; height: 40px; border-radius: 0; z-index: 50 !important;
        }
        #related-section .swiper-button-next:after, 
        #related-section .swiper-button-prev:after { font-size: 16px !important; font-weight: bold; }
        #related-section:hover .swiper-button-next, #related-section:hover .swiper-button-prev { opacity: 1; }
        
        @media (max-width: 768px) {
            #related-section .swiper-button-next, 
            #related-section .swiper-button-prev { opacity: 1 !important; transform: scale(0.8); }
        }
    </style>
@endpush

@section('content')
<div class="max-w-2xl mx-auto px-4 py-16 md:py-24 text-center min-h-[400px] flex flex-col justify-center items-center" id="payment-container">
    
    <div id="pending-state" class="w-full">
        <h1 class="text-3xl font-bold text-gray-900 mb-4">Selesaikan Pembayaran Lu, Bre!</h1>
        <p class="text-gray-500 mb-8 text-lg">Order ID: <span class="font-bold text-gray-800">{{ $order->invoice_number ?? $orderId }}</span></p>

        <div class="flex flex-col md:flex-row items-center justify-center gap-4 w-full px-4 md:px-0">
            <a href="{{ route('front.home') }}" class="w-full md:w-auto bg-[#f0f7f5] text-[#247a6b] border border-[#247a6b] font-bold py-3.5 px-8 rounded-sm hover:bg-white transition shadow-sm flex items-center justify-center">
                <i class="fas fa-arrow-left mr-2"></i> Kembali ke Beranda
            </a>
            <button id="pay-button" class="w-full md:w-auto bg-[#247a6b] text-white font-bold py-3.5 px-10 rounded-sm hover:bg-[#1b5e52] transition shadow-lg flex items-center justify-center">
                Bayar Sekarang
            </button>
        </div>
    </div>

    <div id="success-state" class="hidden flex-col items-center justify-center w-full animate-[slideUp_0.5s_ease-out]">
        <svg class="success-checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
            <circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none"/>
            <path class="checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
        </svg>
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Pembayaran Berhasil!</h1>
        <p class="text-gray-500 mb-8">Mantap Bre, pesanan lu udah lunas dan segera kami proses.</p>
        
        <a href="{{ route('user.orders') }}" class="inline-block bg-[#247a6b] text-white font-bold py-3.5 px-10 rounded-sm hover:bg-[#1b5e52] transition shadow-lg">
            Lihat Pesanan Saya <i class="fas fa-arrow-right ml-2"></i>
        </a>
    </div>

</div>

@if(isset($relatedProducts) && $relatedProducts->count() > 0)
<div class="w-full bg-[#f9fafb] py-16 border-t border-gray-200">
    <div class="max-w-7xl mx-auto px-4 md:px-8">
        <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mb-8 text-center font-serif">Mungkin Anda juga suka</h2>
        
        <div class="relative" id="related-section">
            <div class="swiper relatedSwiper py-2">
                <div class="swiper-wrapper">
                    @foreach($relatedProducts as $relProduct)
                        @php
                            // Logika harga
                            $relPriceDisplay = 'Rp0';
                            if (isset($relProduct->variations) && $relProduct->variations->isNotEmpty()) {
                                $minP = $relProduct->variations->min('price');
                                $maxP = $relProduct->variations->max('price');
                                $relPriceDisplay = ($minP == $maxP) ? 'Rp' . number_format($minP, 0, ',', '.') : 'Rp' . number_format($minP, 0, ',', '.') . ' - Rp' . number_format($maxP, 0, ',', '.');
                            } else {
                                $relPriceDisplay = 'Rp' . number_format($relProduct->price ?? 0, 0, ',', '.');
                            }

                            // Logika rating
                            $relRatingQuery = \Illuminate\Support\Facades\DB::table('product_ratings')->where('product_id', $relProduct->id)->where('status', 1);
                            $relTotalRev = $relRatingQuery->count();
                            $relAvg = $relTotalRev > 0 ? $relRatingQuery->avg('rating') : 0;
                            $relAvgFmt = $relTotalRev > 0 ? number_format($relAvg, 1) : '0';
                        @endphp
                        
                        <div class="swiper-slide h-auto">
                            <a href="{{ route('front.product', $relProduct->id) }}" class="group block bg-white border border-gray-200 hover:border-[#247a6b] hover:shadow-md transition-all duration-300 rounded-sm overflow-hidden flex flex-col h-full">
                                <div class="relative aspect-square overflow-hidden bg-gray-100">
                                    @if(isset($relProduct->images) && $relProduct->images->isNotEmpty())
                                        <img src="{{ asset('storage/' . $relProduct->images->first()->image_path) }}" alt="{{ $relProduct->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                    @else
                                        <div class="flex items-center justify-center h-full text-gray-300"><i class="fas fa-image fa-2x opacity-50"></i></div>
                                    @endif
                                </div>

                                <div class="p-3 flex-1 flex flex-col bg-white">
                                    <h3 class="text-[13px] md:text-[14px] font-medium text-gray-800 mb-2 line-clamp-2 leading-tight group-hover:text-[#247a6b] transition-colors">
                                        {{ $relProduct->title }}
                                    </h3>

                                    <div class="mt-auto flex flex-col gap-1.5">
                                        <p class="font-bold text-gray-900 text-[14px]">{{ $relPriceDisplay }}</p>

                                        <div class="flex items-center text-[11px] text-gray-500">
                                            @if($relTotalRev > 0)
                                                <i class="fas fa-star text-[#ffc400] mr-1"></i>
                                            @else
                                                <i class="far fa-star text-gray-300 mr-1"></i>
                                            @endif
                                            <span class="mr-1 font-medium">{{ $relAvgFmt }}</span> 
                                            <span class="mx-1 border-l border-gray-300 h-2"></span>
                                            <span>{{ formatRibuan($relProduct->sold ?? 0) }} terjual</span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="swiper-button-next !bg-[#247a6b] !text-white !w-10 !h-10 !rounded-none shadow-md hidden md:flex" style="right: -15px;"></div>
            <div class="swiper-button-prev !bg-[#247a6b] !text-white !w-10 !h-10 !rounded-none shadow-md hidden md:flex" style="left: -15px;"></div>
        </div>
    </div>
</div>
@endif

<div class="w-full bg-[#f3f0ea] py-6 border-y border-gray-200 mt-4">
    <div class="max-w-7xl mx-auto px-4 md:px-8 flex flex-col md:flex-row items-center justify-center gap-4 text-center">
        <i class="fas fa-truck-moving text-3xl text-[#247a6b]"></i>
        <h3 class="text-xl md:text-2xl font-bold text-gray-900 tracking-wide">
            Bebas biaya kirim untuk pesanan di atas 500 Ribu
        </h3>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 md:px-8 py-16 mb-10">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 lg:gap-8">
        <div>
            <div class="flex items-center gap-3 mb-5">
                <i class="fas fa-headset text-2xl text-gray-800"></i>
                <h4 class="font-bold text-gray-900 tracking-widest text-sm uppercase">Bantuan</h4>
            </div>
            <p class="text-gray-600 text-sm mb-4 leading-relaxed">
                Butuh panduan merawat tanaman atau ada kendala dengan pesanan? Jangan ragu untuk mengirim pesan kepada tim kami.
            </p>
            <div class="text-sm text-gray-800 mb-4">
                <p class="font-semibold mb-1">Jam Operasional CS:</p>
                <p class="text-gray-600">Senin - Jumat</p>
                <p class="text-gray-600">08:00 - 17:00 WIB</p>
            </div>
            <button onclick="toggleBuyerChat()" class="text-[#247a6b] font-semibold text-sm hover:text-[#1b5e52] transition-colors focus:outline-none">
                Hubungi tim kami &rarr;
            </button>
        </div>
        <div>
            <div class="flex items-center gap-3 mb-5">
                <i class="fas fa-lock text-2xl text-gray-800"></i>
                <h4 class="font-bold text-gray-900 tracking-widest text-sm uppercase">Keamanan</h4>
            </div>
            <p class="text-gray-600 text-sm leading-relaxed">
                Belanja tanpa rasa khawatir. Sistem pembayaran kami telah terintegrasi dengan keamanan tingkat tinggi yang dienkripsi secara profesional. Seluruh data dan transaksi Anda terjamin 100% keamanannya.
            </p>
        </div>
        <div>
            <div class="flex items-center gap-3 mb-5">
                <i class="fas fa-seedling text-2xl text-gray-800"></i>
                <h4 class="font-bold text-gray-900 tracking-widest text-sm uppercase">Kualitas Utama</h4>
            </div>
            <p class="text-gray-600 text-sm leading-relaxed">
                Kami menyeleksi dan bekerja sama langsung dengan para petani lokal spesialis untuk memastikan Anda hanya menerima tanaman, bibit, dan peralatan kebun dengan kualitas paling premium.
            </p>
        </div>
        <div>
            <div class="flex items-center gap-3 mb-5">
                <i class="fas fa-hand-holding-heart text-2xl text-gray-800"></i>
                <h4 class="font-bold text-gray-900 tracking-widest text-sm uppercase">Janji Panambas</h4>
            </div>
            <p class="text-gray-600 text-sm leading-relaxed">
                Jika Anda butuh saran, anggap kami sebagai guru tanaman pribadi Anda. Kepuasan Anda adalah prioritas kami. Jika tanaman tiba dalam kondisi tidak prima, beri tahu kami maksimal 1x24 jam sejak diterima — kami akan segera menyelesaikannya.
            </p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ env('MIDTRANS_CLIENT_KEY') }}"></script>

<script type="text/javascript">
    // INIT SWIPER MUNGKIN ANDA JUGA SUKA
    if(document.querySelector('.relatedSwiper')) {
        var relatedSwiper = new Swiper(".relatedSwiper", {
            slidesPerView: 2, 
            spaceBetween: 15,
            slidesPerGroup: 1, 
            loop: true,        
            autoplay: {
                delay: 4000,   
                disableOnInteraction: false,
            },
            observer: true,
            observeParents: true,
            navigation: {
                nextEl: "#related-section .swiper-button-next", 
                prevEl: "#related-section .swiper-button-prev", 
            },
            breakpoints: {
                640: { slidesPerView: 2, spaceBetween: 15 },
                768: { slidesPerView: 3, spaceBetween: 20 },
                1024: { slidesPerView: 4, spaceBetween: 20 }, 
            }
        });
    }

    // LOGIKA MIDTRANS & ANIMASI POPUP SUKSES
    document.getElementById('pay-button').onclick = function () {
        snap.pay('{{ $snapToken }}', {
            onSuccess: function(result){
                console.log(result);
                
                // Hilangin Tampilan Tombol Bayar
                document.getElementById('pending-state').classList.add('hidden');
                
                // Munculin Tampilan Ceklis Sukses & Tombol Lihat Pesanan
                const successState = document.getElementById('success-state');
                successState.classList.remove('hidden');
                successState.classList.add('flex');
            },
            onPending: function(result){
                console.log(result);
                alert("Menunggu pembayaran lu nih Bre..."); 
            },
            onError: function(result){
                console.log(result);
                alert("Waduh, pembayaran gagal Bre!"); 
            },
            onClose: function(){
                console.log('Tertutup sebelum bayar');
            }
        });
    };
</script>
@endpush