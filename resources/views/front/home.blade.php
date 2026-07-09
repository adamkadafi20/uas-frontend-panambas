@extends('front.layouts.app')

@section('title', 'Beranda - Panambas')

@php
    if (!function_exists('formatRibuan')) {
        function formatRibuan($angka) {
            if ($angka >= 1000) {
                $hasil = number_format($angka / 1000, 1);
                return str_replace('.0', '', $hasil) . 'RB'; 
            }
            return $angka;
        }
    }

    // LOGIKA RATING GLOBAL TOKO DARI DATABASE
    $globalRatingQuery = \Illuminate\Support\Facades\DB::table('product_ratings')->where('status', 1); 
    $globalTotalReviews = $globalRatingQuery->count();
    $globalAvgRating = $globalTotalReviews > 0 ? $globalRatingQuery->avg('rating') : 0;
    
    // AMBIL 10 ULASAN TERBARU (HANYA BINTANG 4 & 5) UNTUK DI BERANDA
    $homeReviews = \Illuminate\Support\Facades\DB::table('product_ratings')
        ->where('status', 1)
        ->whereIn('rating', [4, 5])
        ->orderBy('created_at', 'desc')
        ->take(10)
        ->get();

    // TEKS SUMMARY RATING GLOBAL
    $globalRatingText = 'Belum Ada Ulasan';
    if ($globalTotalReviews > 0) {
        if ($globalAvgRating >= 4.5) $globalRatingText = 'Luar Biasa';
        elseif ($globalAvgRating >= 4.0) $globalRatingText = 'Sangat Bagus';
        elseif ($globalAvgRating >= 3.0) $globalRatingText = 'Cukup Bagus';
        else $globalRatingText = 'Biasa';
    }
@endphp

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <style>
        .swiper-pagination-bullet-active { background-color: #247a6b !important; }
        
        .heroSwiper .swiper-button-next, .heroSwiper .swiper-button-prev { 
            color: #247a6b !important; 
            background: rgba(255,255,255,0.8); 
            width: 40px; height: 40px; 
            border-radius: 50%; 
        }
        .heroSwiper .swiper-button-next:after, .heroSwiper .swiper-button-prev:after { 
            font-size: 18px !important; font-weight: bold; 
        }

        .product-card:hover .product-img { transform: scale(1.05); }

        /* Top 20 Slider Arrows */
        .top20-container .swiper-button-next, 
        .top20-container .swiper-button-prev {
            opacity: 0; 
            transition: all 0.3s ease-in-out;
            background-color: #247a6b !important; 
            color: #ffffff !important; 
            width: 45px; height: 45px;
            border-radius: 4px; 
            z-index: 999 !important; 
        }
        .top20-container .swiper-button-next:after, 
        .top20-container .swiper-button-prev:after {
            font-size: 20px !important;
            font-weight: bold;
        }
        
        .top20-container:hover .swiper-button-next:not(.swiper-button-disabled),
        .top20-container:hover .swiper-button-prev:not(.swiper-button-disabled) {
            opacity: 1;
        }
        .top20-container .swiper-button-next { right: -15px; }
        .top20-container .swiper-button-prev { left: -15px; }

        @media (max-width: 768px) {
            .top20-container .swiper-button-next:not(.swiper-button-disabled), 
            .top20-container .swiper-button-prev:not(.swiper-button-disabled) {
                opacity: 1 !important; 
            }
            .top20-container .swiper-button-next, 
            .top20-container .swiper-button-prev {
                transform: scale(0.7); 
            }
            .top20-container .swiper-button-next { right: -10px; }
            .top20-container .swiper-button-prev { left: -10px; }
        }

        .top20-container .swiper-button-next.swiper-button-disabled,
        .top20-container .swiper-button-prev.swiper-button-disabled {
            opacity: 0 !important;
            visibility: hidden !important;
            pointer-events: none !important;
            display: none !important; 
        }

        /* Ulasan Section Arrows */
        #review-section .swiper-button-next, 
        #review-section .swiper-button-prev {
            opacity: 0; transition: all 0.3s ease;
            background-color: #247a6b !important; color: #ffffff !important; 
            width: 40px; height: 40px; border-radius: 50%; z-index: 50 !important;
        }
        #review-section .swiper-button-next:after, 
        #review-section .swiper-button-prev:after { font-size: 16px !important; font-weight: bold; }
        
        #review-section:hover .swiper-button-next:not(.swiper-button-disabled),
        #review-section:hover .swiper-button-prev:not(.swiper-button-disabled) { opacity: 1; }
        #review-section .swiper-button-disabled { display: none !important; }

        @media (max-width: 768px) {
            #review-section .swiper-button-next:not(.swiper-button-disabled), 
            #review-section .swiper-button-prev:not(.swiper-button-disabled) { opacity: 1 !important; }
        }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
            100% { transform: translateY(0px); }
        }
        .animate-float {
            animation: float 4s ease-in-out infinite;
        }
    </style>
@endpush

@section('content')
    @if(isset($banners) && $banners->isNotEmpty())
        <div class="w-full bg-[#f3f0ea]">
            <div class="swiper heroSwiper w-full h-[250px] md:h-[500px]">
                <div class="swiper-wrapper">
                    @foreach($banners as $banner)
                        <div class="swiper-slide cursor-pointer" onclick="window.location='{{ $banner->link ?? '#' }}'">
                            @php $ext = pathinfo($banner->image_path, PATHINFO_EXTENSION); @endphp
                            @if($ext == 'mp4')
                                <video src="{{ asset('storage/'.$banner->image_path) }}" class="w-full h-full object-cover" autoplay loop muted playsinline></video>
                            @else
                                <img src="{{ asset('storage/'.$banner->image_path) }}" class="w-full h-full object-cover" alt="{{ $banner->title }}">
                            @endif
                        </div>
                    @endforeach
                </div>
                <div class="swiper-pagination"></div>
                <div class="swiper-button-next hidden md:flex"></div>
                <div class="swiper-button-prev hidden md:flex"></div>
            </div>
        </div>
    @else
        <div class="bg-[#f3f0ea] py-12 md:py-24">
            <div class="max-w-7xl mx-auto px-4 md:px-8 flex flex-col md:flex-row items-center">
                <div class="w-full md:w-1/2 mb-10 md:mb-0 text-center md:text-left">
                    <h1 class="text-4xl md:text-7xl font-bold text-gray-900 leading-[1.1] mb-6">Plants made<br>easy.</h1>
                    <p class="text-lg md:text-xl text-gray-700 mb-10 max-w-md mx-auto md:mx-0">Tanaman berkualitas, dikirim aman langsung ke rumahmu.</p>
                    <a href="{{ route('front.shop') }}" class="bg-[#247a6b] text-white px-10 py-4 rounded font-medium hover:bg-[#1b5e52] transition shadow-lg text-lg">Shop all plants</a>
                </div>
                <div class="w-full md:w-1/2">
                    <img src="https://images.unsplash.com/photo-1463320726281-696a485928c7?auto=format&fit=crop&w=800&q=80" class="rounded shadow-2xl w-full h-[300px] md:h-[500px] object-cover">
                </div>
            </div>
        </div>
    @endif

    <div class="max-w-7xl mx-auto px-4 md:px-8 py-16 mt-8 border-b border-gray-200">
        <div class="flex flex-col md:flex-row items-center">
            <div class="w-full md:w-1/4 pr-0 md:pr-6 mb-8 md:mb-0 text-center md:text-left">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4 tracking-tight">Top 20 Panambas</h2>
                <p class="text-gray-600 mb-6 text-sm md:text-base">Temukan tanaman, media tanam, dan peralatan paling populer pilihan pelanggan kami.</p>
                <a href="{{ route('front.shop', ['top20' => 'true']) }}" class="text-[#247a6b] font-semibold border-b-2 border-[#247a6b] pb-0.5 hover:text-[#1b5e52] transition">Check Sekarang &rarr;</a>
            </div>
            
            <div class="w-full md:w-3/4 relative top20-container" id="top20-section">
                <div class="swiper top20Swiper py-2">
                    <div class="swiper-wrapper">
                        @forelse($featuredProducts ?? [] as $product)
                            @php
                                $priceDisplay = 'Rp0';
                                if (isset($product->variations) && $product->variations->isNotEmpty()) {
                                    $minPrice = $product->variations->min('price');
                                    $maxPrice = $product->variations->max('price');
                                    $priceDisplay = ($minPrice == $maxPrice) ? 'Rp' . number_format($minPrice, 0, ',', '.') : 'Rp' . number_format($minPrice, 0, ',', '.') . ' - Rp' . number_format($maxPrice, 0, ',', '.');
                                } else {
                                    $priceDisplay = 'Rp' . number_format($product->price ?? 0, 0, ',', '.');
                                }

                                // LOGIKA RATING ASLI DARI DATABASE
                                $ratingQuery = \Illuminate\Support\Facades\DB::table('product_ratings')
                                    ->where('product_id', $product->id)
                                    ->where('status', 1);
                                    
                                $totalProductReviews = $ratingQuery->count();
                                $avgProductRating = $totalProductReviews > 0 ? $ratingQuery->avg('rating') : 0;
                                $avgProductRatingFormatted = $totalProductReviews > 0 ? number_format($avgProductRating, 1) : '0';
                            @endphp
                            
                            <div class="swiper-slide h-auto">
                                <a href="{{ route('front.product', $product->id) }}" class="group block bg-white border border-gray-200 hover:border-[#247a6b] hover:shadow-md transition-all duration-300 rounded-sm overflow-hidden flex flex-col h-full">
                                    <div class="relative aspect-square overflow-hidden bg-gray-100">
                                        @if(isset($product->images) && $product->images->isNotEmpty())
                                            <img src="{{ asset('storage/' . $product->images->first()->image_path) }}" alt="{{ $product->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                        @else
                                            <div class="flex items-center justify-center h-full text-gray-300"><i class="fas fa-image fa-2x opacity-50"></i></div>
                                        @endif

                                        <div class="absolute top-0 left-0 bg-[#247a6b] text-white text-[10px] font-bold px-2 py-1 rounded-br-lg shadow-sm tracking-wide">
                                            {{ explode(' > ', $product->category_id)[0] ?? 'Tanaman' }}
                                        </div>
                                    </div>

                                    <div class="p-2 md:p-3 flex-1 flex flex-col bg-white">
                                        <h3 class="text-[12px] md:text-[14px] text-gray-800 mb-2 line-clamp-2 leading-tight group-hover:text-[#247a6b] transition-colors">
                                            {{ $product->title }}
                                        </h3>

                                        <div class="mt-auto flex flex-col gap-1.5">
                                            <p class="font-bold text-gray-900 text-[14px] md:text-[16px]">{{ $priceDisplay }}</p>

                                            <div class="flex items-center text-[10px] md:text-[11px] text-gray-500">
                                                @if($totalProductReviews > 0)
                                                    <i class="fas fa-star text-[#ffc400] mr-1"></i>
                                                @else
                                                    <i class="far fa-star text-gray-300 mr-1"></i>
                                                @endif
                                                <span class="mr-1 font-medium">{{ $avgProductRatingFormatted }}</span> 
                                                <span class="mx-1 border-l border-gray-300 h-2"></span>
                                                <span>{{ formatRibuan($product->sold ?? 0) }} terjual</span>
                                            </div>

                                            <div class="flex items-center text-[10px] md:text-[11px] text-gray-400 mt-1">
                                                <i class="fas fa-map-marker-alt mr-1"></i>
                                                <span class="truncate">{{ $product->origin ?? 'Bogor' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @empty
                            <div class="swiper-slide h-auto w-full flex justify-center py-10 text-gray-400">
                                Belum ada data best seller Bre!
                            </div>
                        @endforelse
                    </div>
                </div>
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
            </div>
        </div>
    </div>

    <div class="relative w-full py-20 bg-cover bg-center bg-no-repeat bg-fixed" style="background-image: url('https://images.unsplash.com/photo-1542273917363-3b1817f69a2d?auto=format&fit=crop&w=1920&q=80');">
        <div class="absolute inset-0 bg-black bg-opacity-60"></div>
        <div class="relative max-w-7xl mx-auto px-4 md:px-8 text-white z-10">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-5xl font-medium tracking-wide">Cara kami menjalankan bisnis</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-12 md:gap-16">
                <div class="flex flex-col items-center">
                    <i class="fas fa-tree text-5xl md:text-7xl mb-6"></i>
                    <h3 class="text-xl md:text-2xl font-medium mb-4">Integritas</h3>
                    <p class="text-gray-200 text-sm md:text-base leading-relaxed text-justify">
                        berarti menyampaikan kebenaran, menepati janji, dan memperlakukan orang lain secara adil dan terhormat, baik dengan pembeli, petani, pemasok, pemilik hasil bumi, perantara, maupun pemerintah daerah. Integritas adalah salah satu aset kami yang paling berharga dan tidak boleh dikompromikan.
                    </p>
                </div>
                <div class="flex flex-col items-center">
                    <i class="fas fa-cloud-sun text-5xl md:text-7xl mb-6"></i>
                    <h3 class="text-xl md:text-2xl font-medium mb-4">Kualitas</h3>
                    <p class="text-gray-200 text-sm md:text-base leading-relaxed text-justify">
                        Kualitas produk kami adalah sesuatu yang tidak akan pernah kami kompromikan. Mulai dari perkebunan alami hingga tempat pengolahan, kami memastikan praktik terbaik yang mematuhi standar higienitas dan kesehatan bagi konsumen.
                    </p>
                </div>
                <div class="flex flex-col items-center">
                    <i class="fas fa-lightbulb text-5xl md:text-7xl mb-6"></i>
                    <h3 class="text-xl md:text-2xl font-medium mb-4">Komitmen</h3>
                    <p class="text-gray-200 text-sm md:text-base leading-relaxed text-justify">
                        sangat penting karena kami akan terus memberikan yang terbaik kepada pelanggan kami. Kesempatan kami untuk melayani harus dipandang sebagai sebuah hak istimewa yang tidak boleh dianggap remeh. Kesepakatan dengan pembeli, kejujuran dalam bertransaksi, menjadi fondasi kami dalam mengembangkan bisnis dan produk.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 md:px-8 py-16 md:py-24 border-t border-gray-200 bg-[#f9fafb]">
        <div class="flex flex-col md:flex-row items-center gap-10 md:gap-16">
            <div class="w-full md:w-1/2">
                <div class="relative overflow-hidden rounded-lg shadow-2xl aspect-[4/3]">
                    <img src="https://images.unsplash.com/photo-1585320806297-9794b3e4eeae?auto=format&fit=crop&w=800&q=80" alt="Ide Kebun Rumahan" class="w-full h-full object-cover hover:scale-105 transition-transform duration-700">
                </div>
            </div>
            
            <div class="w-full md:w-1/2 text-center md:text-left">
                <h2 class="text-3xl md:text-5xl font-extrabold text-gray-900 mb-6 tracking-tight">Ide Kebun Rumahan</h2>
                <p class="text-lg text-gray-600 mb-8 leading-relaxed max-w-lg mx-auto md:mx-0">
                    Dari menyulap sudut sempit menjadi hutan mini, hingga inspirasi taman vertikal dari berbagai penjuru dunia. Temukan beragam cara cerdas menyegarkan rumahmu dengan tanaman.
                </p>
                <button onclick="showDevToast()" class="inline-block bg-[#247a6b] text-white px-8 py-4 rounded font-bold hover:bg-[#1b5e52] transition-colors shadow-lg text-lg focus:outline-none">
                    Lihat Inspirasi &rarr;
                </button>
            </div>
        </div>
    </div>

    <!-- BAGIAN ULASAN PEMBELI GLOBAL TOKO -->
    <div class="max-w-7xl mx-auto px-4 md:px-8 py-16 md:py-24 bg-white border-t border-gray-100">
        <div class="flex flex-col md:flex-row items-center gap-8 md:gap-12">
            
            <div class="w-full md:w-1/4 text-center md:text-left flex flex-col items-center md:items-start border-b md:border-b-0 md:border-r border-gray-200 pb-8 md:pb-0 md:pr-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ $globalRatingText }}</h2>
                <div class="flex text-[#00b67a] gap-1 mb-2">
                    @for($i = 1; $i <= 5; $i++)
                        @if($globalTotalReviews == 0)
                            <i class="far fa-star fa-2x text-gray-300"></i>
                        @elseif($i <= floor($globalAvgRating))
                            <i class="fas fa-star fa-2x"></i>
                        @elseif($i == ceil($globalAvgRating) && $globalAvgRating - floor($globalAvgRating) > 0)
                            <i class="fas fa-star-half-alt fa-2x"></i>
                        @else
                            <i class="far fa-star fa-2x text-gray-300"></i>
                        @endif
                    @endfor
                </div>
                <p class="text-sm text-gray-600 mb-4">Berdasarkan ulasan pelanggan</p>
                <div class="flex items-center gap-2 font-bold text-lg text-gray-800">
                    <i class="fas fa-leaf text-[#247a6b]"></i> PANAMBAS
                </div>
            </div>

            <div class="w-full md:w-3/4 relative" id="review-section">
                @if($homeReviews->count() > 0)
                    <div class="swiper reviewSwiper py-4 px-1">
                        <div class="swiper-wrapper">
                            @foreach($homeReviews as $review)
                            <div class="swiper-slide h-auto">
                                <div class="bg-gray-50 p-6 rounded-lg h-full flex flex-col border border-gray-100 transition hover:shadow-md">
                                    <div class="flex text-[#00b67a] text-sm gap-0.5 mb-3">
                                        @for($i=1; $i<=5; $i++)
                                            @if($i <= $review->rating) <i class="fas fa-star"></i> @else <i class="far fa-star text-gray-300"></i> @endif
                                        @endfor
                                    </div>
                                    <p class="text-sm text-gray-700 mb-4 flex-1 line-clamp-4 leading-relaxed">"{{ $review->comment }}"</p>
                                    <div class="text-xs text-gray-500 font-medium mb-3">
                                        <span class="font-bold text-gray-900">{{ $review->username ?? 'Pembeli' }}</span>, {{ \Carbon\Carbon::parse($review->created_at)->translatedFormat('d M Y') }}
                                    </div>
                                    
                                    @if(!empty($review->reply))
                                        <button onclick="openReplyModal(`{{ htmlspecialchars($review->reply, ENT_QUOTES) }}`)" class="text-left text-[#247a6b] text-xs font-bold hover:underline mt-auto flex items-center gap-1 w-max">
                                            Lihat balasan penjual <i class="fas fa-arrow-right text-[10px]"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="swiper-button-next" style="right: -10px;"></div>
                    <div class="swiper-button-prev" style="left: -10px;"></div>
                @else
                    <div class="text-center py-12 bg-gray-50 rounded-lg border border-dashed border-gray-300 flex flex-col items-center w-full">
                        <i class="fas fa-comment-dots text-4xl text-gray-300 mb-3"></i>
                        <p class="text-gray-500 font-medium text-lg">Belum Ada Ulasan</p>
                        <p class="text-gray-400 text-sm mt-1">Ulasan pelanggan akan ditampilkan di sini.</p>
                    </div>
                @endif
            </div>

        </div>
    </div>

    <div class="w-full bg-[#f3f0ea] py-6 border-y border-gray-200 mt-16">
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

    <!-- MODAL POPUP BUAT BALASAN PENJUAL -->
    <div id="replyModal" class="fixed inset-0 z-[1050] hidden items-center justify-center">
        <div class="absolute inset-0 bg-black/60 transition-opacity" onclick="closeReplyModal()"></div>
        <div class="relative bg-white w-[90%] md:w-[450px] rounded-xl shadow-2xl p-6 animate-[slideUp_0.3s_ease-out]">
            <button onclick="closeReplyModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700"><i class="fas fa-times text-lg"></i></button>
            <div class="flex items-center gap-3 mb-4 border-b border-gray-100 pb-4">
                <div class="w-12 h-12 rounded-full bg-[#e6f2f0] flex items-center justify-center text-[#247a6b]">
                    <i class="fas fa-store text-lg"></i>
                </div>
                <div>
                    <h4 class="font-bold text-gray-900 text-base">Panambas Official</h4>
                    <p class="text-xs text-gray-500 font-medium mt-0.5">Balasan Penjual</p>
                </div>
            </div>
            <div class="bg-gray-50 rounded-lg p-4 mb-2">
                <p id="replyContent" class="text-[14px] text-gray-700 leading-relaxed whitespace-pre-line"></p>
            </div>
            <button onclick="closeReplyModal()" class="mt-4 w-full bg-white border border-gray-300 hover:bg-gray-50 text-gray-800 font-bold py-2.5 rounded transition text-sm">Tutup</button>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script>
        var heroSwiper = new Swiper(".heroSwiper", {
            spaceBetween: 0, 
            centeredSlides: true, 
            loop: true,
            autoplay: { delay: 4000, disableOnInteraction: false },
            pagination: { el: ".heroSwiper .swiper-pagination", clickable: true },
            navigation: { nextEl: ".heroSwiper .swiper-button-next", prevEl: ".heroSwiper .swiper-button-prev" },
        });

       var top20Swiper = new Swiper(".top20Swiper", {
            slidesPerView: 2, 
            spaceBetween: 15,
            slidesPerGroup: 2, 

            loop: false,
            observer: true,
            observeParents: true,

            navigation: {
                nextEl: "#top20-section .swiper-button-next",
                prevEl: "#top20-section .swiper-button-prev",
            },
            breakpoints: {
                640: { slidesPerView: 2, spaceBetween: 20, slidesPerGroup: 2 },
                768: { slidesPerView: 3, spaceBetween: 20, slidesPerGroup: 3 },
                1024: { slidesPerView: 4, spaceBetween: 20, slidesPerGroup: 4 }, 
            }
        });

        var reviewSwiper = new Swiper(".reviewSwiper", {
            slidesPerView: 1, 
            spaceBetween: 20,
            loop: false,
            observer: true,
            observeParents: true,
            navigation: {
                nextEl: "#review-section .swiper-button-next", 
                prevEl: "#review-section .swiper-button-prev", 
            },
            breakpoints: {
                640: { slidesPerView: 2, spaceBetween: 20 },
                1024: { slidesPerView: 3, spaceBetween: 30 }, 
            }
        });

        // FUNGSI NOTIFIKASI FITUR DALAM PENGEMBANGAN
        function showDevToast() {
            if(document.getElementById('dev-toast')) return;
            const toast = document.createElement('div');
            toast.id = 'dev-toast';
            toast.className = 'fixed top-24 right-5 z-[9999] flex items-center w-full max-w-xs p-4 space-x-3 text-white bg-[#247a6b] rounded-md shadow-xl border-l-4 border-[#1b5e52] transform translate-x-full transition-transform duration-500';
            toast.innerHTML = `<div class="text-sm font-medium leading-relaxed"><i class="fas fa-tools mr-2 text-[#ffc400]"></i>Fitur "Ide Kebun" sedang dalam tahap pengembangan. Ditunggu ya Bre! 🌿</div>`;
            document.body.appendChild(toast);
            setTimeout(() => toast.classList.remove('translate-x-full'), 100);
            setTimeout(() => {
                toast.classList.add('translate-x-full');
                setTimeout(() => toast.remove(), 500);
            }, 3500);
        }

        // FUNGSI BUKA TUTUP MODAL BALASAN PENJUAL
        function openReplyModal(content) {
            document.getElementById('replyContent').innerText = content;
            const modal = document.getElementById('replyModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }

        function closeReplyModal() {
            const modal = document.getElementById('replyModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = 'auto';
        }
    </script>
@endpush