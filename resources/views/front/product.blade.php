@extends('front.layouts.app')

@php
    // Fungsi buat nyingkat angka ribuan jadi RB (Shopee Style)
    if (!function_exists('formatRibuan')) {
        function formatRibuan($angka) {
            if ($angka >= 1000) {
                $hasil = number_format($angka / 1000, 1);
                return str_replace('.0', '', $hasil) . 'RB';
            }
            return $angka;
        }
    }

    // LOGIKA RATING ASLI DARI DATABASE
    $ratingQuery = \Illuminate\Support\Facades\DB::table('product_ratings')
        ->where('product_id', $product->id)
        ->where('status', 1); 
        
    $totalReviews = $ratingQuery->count();
    $avgRating = $totalReviews > 0 ? $ratingQuery->avg('rating') : 0;
    $avgRatingFormatted = $totalReviews > 0 ? number_format($avgRating, 1) : '0';

    // AMBIL 10 ULASAN TERBARU (HANYA BINTANG 4 & 5)
    $productReviews = \Illuminate\Support\Facades\DB::table('product_ratings')
        ->where('product_id', $product->id)
        ->where('status', 1)
        ->whereIn('rating', [4, 5])
        ->orderBy('created_at', 'desc')
        ->take(10)
        ->get();

    // TEKS SUMMARY RATING
    $ratingText = 'Belum Ada Ulasan';
    if ($totalReviews > 0) {
        if ($avgRating >= 4.5) $ratingText = 'Luar Biasa';
        elseif ($avgRating >= 4.0) $ratingText = 'Sangat Bagus';
        elseif ($avgRating >= 3.0) $ratingText = 'Cukup Bagus';
        else $ratingText = 'Biasa';
    }
@endphp

@section('title', $product->title . ' - Panambas')

@push('styles')
    <!-- CSS Swiper buat slider ulasan & produk terkait -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <style>
        .accordion-content { transition: max-height 0.4s ease-out, opacity 0.4s ease-out; max-height: 0; opacity: 0; overflow: hidden; }
        .accordion-content.open { max-height: 1500px; opacity: 1; margin-top: 1rem; padding-bottom: 1.5rem; }
        .accordion-icon { transition: transform 0.3s ease; }
        .accordion-icon.rotate { transform: rotate(180deg); }

        /* Tombol panah Swiper khusus bagian Ulasan */
        #product-review-section .swiper-button-next, 
        #product-review-section .swiper-button-prev {
            opacity: 0; transition: all 0.3s ease;
            background-color: #247a6b !important; color: #ffffff !important; 
            width: 40px; height: 40px; border-radius: 50%; z-index: 50 !important;
        }
        #product-review-section .swiper-button-next:after, 
        #product-review-section .swiper-button-prev:after { font-size: 16px !important; font-weight: bold; }
        
        #product-review-section:hover .swiper-button-next:not(.swiper-button-disabled),
        #product-review-section:hover .swiper-button-prev:not(.swiper-button-disabled) { opacity: 1; }
        #product-review-section .swiper-button-disabled { display: none !important; }

        /* Tombol panah Swiper khusus bagian Produk Terkait (Patch Plants Style: Kotak) */
        #related-section .swiper-button-next, 
        #related-section .swiper-button-prev {
            opacity: 0; transition: all 0.3s ease;
            background-color: #247a6b !important; color: #ffffff !important; 
            width: 40px; height: 40px; border-radius: 0; z-index: 50 !important;
        }
        #related-section .swiper-button-next:after, 
        #related-section .swiper-button-prev:after { font-size: 16px !important; font-weight: bold; }
        
        #related-section:hover .swiper-button-next,
        #related-section:hover .swiper-button-prev { opacity: 1; }

        @media (max-width: 768px) {
            #product-review-section .swiper-button-next:not(.swiper-button-disabled), 
            #product-review-section .swiper-button-prev:not(.swiper-button-disabled),
            #related-section .swiper-button-next, 
            #related-section .swiper-button-prev { opacity: 1 !important; }
            
            #related-section .swiper-button-next, 
            #related-section .swiper-button-prev { transform: scale(0.8); }
        }
    </style>
@endpush

@section('content')
    <div class="max-w-7xl mx-auto px-4 md:px-8 py-5 text-xs text-gray-400 font-medium tracking-wide uppercase">
        Beranda <i class="fas fa-chevron-right text-[8px] mx-2 opacity-50"></i> 
        {!! str_replace(' > ', ' <i class="fas fa-chevron-right text-[8px] mx-2 opacity-50"></i> ', $product->category_id) !!}
    </div>

    <div class="max-w-7xl mx-auto px-4 md:px-8 py-4 md:py-10 grid grid-cols-1 md:grid-cols-12 gap-10 md:gap-16">
        
        <div class="md:col-span-5 md:col-start-1 lg:col-start-2">
            <div class="w-full aspect-[4/5] bg-[#f8f9fa] rounded-sm overflow-hidden mb-4 relative shadow-sm border border-gray-100" id="mainMediaContainer">
                @if($product->video_path)
                    <video id="mainVideo" src="{{ asset('storage/'.$product->video_path) }}" class="w-full h-full object-cover" autoplay loop muted controls></video>
                    <img id="mainImage" class="w-full h-full object-cover hidden">
                @elseif(isset($product->images) && $product->images->isNotEmpty())
                    <img id="mainImage" src="{{ asset('storage/'.$product->images->first()->image_path) }}" class="w-full h-full object-cover">
                    <video id="mainVideo" class="w-full h-full object-cover hidden" controls muted></video>
                @else
                    <div class="flex items-center justify-center h-full text-gray-200"><i class="fas fa-image fa-4x"></i></div>
                @endif
            </div>

            @php $hasVariations = $product->variations && $product->variations->isNotEmpty(); @endphp
            
            <div class="relative group mt-4">
                <button onclick="document.getElementById('thumbContainer').scrollBy({left: -150, behavior: 'smooth'})" class="absolute left-1 top-1/2 -translate-y-1/2 bg-white/90 shadow-md w-8 h-8 rounded-full z-10 hidden group-hover:flex items-center justify-center text-gray-700 hover:bg-[#247a6b] hover:text-white transition">
                    <i class="fas fa-chevron-left text-sm"></i>
                </button>

                <div id="thumbContainer" class="flex space-x-3 overflow-x-auto hide-scroll pb-2 scroll-smooth snap-x">
                    @if($product->video_path)
                        <div class="w-16 h-16 md:w-20 md:h-20 flex-shrink-0 border-2 rounded-sm cursor-pointer overflow-hidden transition-all duration-200 border-[#247a6b] media-thumbnail" 
                               onclick="changeMainMedia('{{ asset('storage/'.$product->video_path) }}', 'mp4', this, true, null)">
                            <div class="relative w-full h-full bg-gray-100">
                                <video src="{{ asset('storage/'.$product->video_path) }}" class="w-full h-full object-cover"></video>
                                <div class="absolute inset-0 flex items-center justify-center bg-black/40"><i class="fas fa-play text-white text-sm"></i></div>
                            </div>
                        </div>
                    @endif

                    @if(isset($product->images))
                        @foreach($product->images as $index => $media)
                            <div class="w-16 h-16 md:w-20 md:h-20 flex-shrink-0 border-2 rounded-sm cursor-pointer overflow-hidden transition-all duration-200 {{ (!$product->video_path && $index == 0) ? 'border-[#247a6b]' : 'border-transparent hover:border-gray-300' }} media-thumbnail" 
                                   onclick="changeMainMedia('{{ asset('storage/'.$media->image_path) }}', 'img', this, true, null)">
                                <img src="{{ asset('storage/'.$media->image_path) }}" class="w-full h-full object-cover">
                            </div>
                        @endforeach
                    @endif

                    @if($hasVariations)
                        @foreach($product->variations as $var)
                            @if($var->image_path)
                                <div data-var-thumb-id="{{ $var->id }}" class="w-16 h-16 md:w-20 md:h-20 flex-shrink-0 border-2 rounded-sm cursor-pointer overflow-hidden transition-all duration-200 border-transparent hover:border-gray-300 media-thumbnail" 
                                       onclick="changeMainMedia('{{ asset('storage/'.$var->image_path) }}', 'img', this, true, '{{ $var->id }}')">
                                    <img src="{{ asset('storage/'.$var->image_path) }}" class="w-full h-full object-cover">
                                </div>
                            @endif
                        @endforeach
                    @endif
                </div>

                <button onclick="document.getElementById('thumbContainer').scrollBy({left: 150, behavior: 'smooth'})" class="absolute right-1 top-1/2 -translate-y-1/2 bg-white/90 shadow-md w-8 h-8 rounded-full z-10 hidden group-hover:flex items-center justify-center text-gray-700 hover:bg-[#247a6b] hover:text-white transition">
                    <i class="fas fa-chevron-right text-sm"></i>
                </button>
            </div>
        </div>

        <div class="md:col-span-6 lg:col-span-5">
            <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4 leading-tight tracking-tight">{{ $product->title }}</h1>
            
            <div class="flex items-center text-sm mb-6 pb-6 border-b border-gray-100">
                <div class="flex space-x-1 text-sm mr-2">
                    @for($i = 1; $i <= 5; $i++)
                        @if($totalReviews == 0)
                            <i class="far fa-star text-gray-300"></i>
                        @elseif($i <= floor($avgRating))
                            <i class="fas fa-star text-[#ffc400]"></i>
                        @elseif($i == ceil($avgRating) && $avgRating - floor($avgRating) > 0)
                            <i class="fas fa-star-half-alt text-[#ffc400]"></i>
                        @else
                            <i class="far fa-star text-gray-300"></i>
                        @endif
                    @endfor
                </div>
                <span class="text-gray-900 font-bold mr-4 border-b border-gray-900">{{ $totalReviews > 0 ? $avgRatingFormatted : '0' }}</span>
                <span class="text-gray-300 mr-4">|</span>
                <span class="text-gray-900 font-medium">{{ formatRibuan($totalReviews) }} <span class="text-gray-500 font-normal">Penilaian</span></span> 
                <span class="text-gray-300 mx-4">|</span>
                <span class="text-gray-900 font-medium">{{ formatRibuan($product->sold ?? 0) }} <span class="text-gray-500 font-normal">Terjual</span></span>
            </div>

            @php
                $minPrice = $hasVariations ? $product->variations->min('price') : $product->price;
                $maxPrice = $hasVariations ? $product->variations->max('price') : $product->price;
                $priceText = ($minPrice != $maxPrice) 
                             ? 'Rp'.number_format($minPrice,0,',','.').' - Rp'.number_format($maxPrice,0,',','.') 
                             : 'Rp'.number_format($minPrice,0,',','.');
                
                $totalStock = $hasVariations ? $product->variations->sum('stock') : ($product->qty ?? 0);
            @endphp

            <div class="mb-8">
                <p id="productPriceDisplay" class="text-3xl md:text-4xl font-bold text-gray-900 tracking-tight">
                    {{ $priceText }}
                </p>
            </div>

            @if($hasVariations)
                <div class="mb-8">
                    <h4 class="text-gray-400 text-[11px] font-bold uppercase tracking-widest mb-3">
                        {{ $product->variations->first()->variation_name ?? 'Pilih Varian' }}
                    </h4>
                    <div class="flex flex-wrap gap-3">
                        @foreach($product->variations as $var)
                            <button type="button" 
                                    class="variation-btn border border-gray-300 text-gray-700 py-2 px-5 rounded-sm hover:border-[#247a6b] hover:text-[#247a6b] transition text-sm font-medium bg-white"
                                    data-var-id="{{ $var->id }}"
                                    data-price="{{ $var->price }}"
                                    data-stock="{{ $var->stock }}"
                                    data-image="{{ $var->image_path ? asset('storage/'.$var->image_path) : '' }}"
                                    onclick="selectVariation(this)">
                                {{ $var->variation_option }}
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif

            @if(!$hasVariations)
                <div class="flex items-center mb-10">
                    <h4 class="text-gray-400 text-[11px] font-bold uppercase tracking-widest w-24">Kuantitas</h4>
                    <div class="flex items-center border border-gray-300 rounded-sm bg-white">
                        <button type="button" onclick="minQtyMain()" class="px-4 py-2 text-gray-500 hover:bg-gray-100 transition"><i class="fas fa-minus text-xs"></i></button>
                        <input type="text" id="qtyInputMain" value="1" readonly class="w-12 text-center text-gray-900 text-sm font-bold border-x border-gray-300 py-2 focus:outline-none">
                        <button type="button" onclick="addQtyMain()" class="px-4 py-2 text-gray-500 hover:bg-gray-100 transition"><i class="fas fa-plus text-xs"></i></button>
                    </div>
                    <span class="text-gray-400 text-xs ml-4">Tersisa <span id="stockDisplay" class="font-bold text-[#247a6b]">{{ $totalStock }}</span> stok</span>
                </div>
            @else
                <div class="flex items-center mb-10">
                    <h4 class="text-gray-400 text-[11px] font-bold uppercase tracking-widest w-24">Stok</h4>
                    <span class="text-sm text-gray-900 font-bold bg-gray-100 px-3 py-1 rounded-sm"><span id="stockDisplay">{{ $totalStock }}</span> Tersedia</span>
                </div>
            @endif

            <div class="flex gap-2 md:gap-4 w-full mt-4 md:mt-0">
                @guest
                    <a href="{{ route('login') }}" class="flex-1 bg-[#f0f7f5] border border-[#247a6b] text-[#247a6b] font-bold py-3 md:py-4 rounded-sm hover:bg-white transition flex justify-center items-center text-[11px] md:text-base text-center leading-tight px-1">
                        <i class="fas fa-cart-plus mr-1.5 md:mr-2 text-sm md:text-lg"></i> Masukkan Keranjang
                    </a>
                    <a href="{{ route('login') }}" class="flex-1 bg-[#247a6b] border border-[#247a6b] text-white font-bold py-3 md:py-4 rounded-sm hover:bg-[#1b5e52] transition shadow-lg shadow-green-900/10 flex items-center justify-center text-[11px] md:text-base text-center leading-tight px-1">
                        Beli Sekarang
                    </a>
                @else
                    <button onclick="openModal('cart')" class="flex-1 bg-[#f0f7f5] border border-[#247a6b] text-[#247a6b] font-bold py-3 md:py-4 rounded-sm hover:bg-white transition flex justify-center items-center text-[11px] md:text-base text-center leading-tight px-1">
                        <i class="fas fa-cart-plus mr-1.5 md:mr-2 text-sm md:text-lg"></i> Masukkan Keranjang
                    </button>
                    <button onclick="openModal('buy')" class="flex-1 bg-[#247a6b] border border-[#247a6b] text-white font-bold py-3 md:py-4 rounded-sm hover:bg-[#1b5e52] transition shadow-lg shadow-green-900/10 flex items-center justify-center text-[11px] md:text-base text-center leading-tight px-1">
                        Beli Sekarang
                    </button>
                @endguest
            </div>

            <div class="mt-8 pt-6 border-t border-gray-100 flex items-center text-sm text-gray-500">
                <i class="fas fa-shield-alt text-[#247a6b] text-lg mr-3"></i> 
                <span><strong>Garansi Panambas:</strong> Tanaman sampai dalam kondisi segar atau uang kembali.</span>
            </div>
        </div>
    </div>

    <!-- BAGIAN DESKRIPSI & SPESIFIKASI -->
    <div class="max-w-4xl mx-auto px-4 md:px-8 py-10">
        <div class="border-t border-gray-200">
            <button class="w-full py-6 flex justify-between items-center text-left focus:outline-none group" onclick="toggleAccordion('spesifikasi')">
                <h3 class="text-lg font-bold text-gray-900 group-hover:text-[#247a6b] transition bg-gray-50 px-4 py-2 rounded-md">Spesifikasi Produk</h3>
                <i id="icon-spesifikasi" class="fas fa-chevron-down text-gray-400 accordion-icon"></i>
            </button>
            <div id="content-spesifikasi" class="accordion-content text-sm leading-relaxed px-4">
                <div class="grid grid-cols-3 gap-y-4 pb-4">
                    <div class="text-gray-500">Kategori</div>
                    <div class="col-span-2 text-[#247a6b] font-medium">{{ $product->category_id }}</div>
                    
                    <div class="text-gray-500">Merek</div>
                    <div class="col-span-2 text-gray-900">{{ $product->brand ?? 'Tidak Ada Merek' }}</div>
                    
                    @if(!empty($product->origin))
                        <div class="text-gray-500">Asal Produk</div>
                        <div class="col-span-2 text-gray-900 capitalize">{{ $product->origin == 'import' ? 'Tanaman Import' : 'Lokal Indonesia' }}</div>
                    @endif
                    
                    @if(!empty($product->max_purchase_type))
                        <div class="text-gray-500">Batas Pembelian</div>
                        <div class="col-span-2 text-gray-900 capitalize">{{ $product->max_purchase_type == 'unlimited' ? 'Tanpa Batas' : 'Maksimal ' . $product->max_purchase_limit . ' item' }}</div>
                    @endif
                    
                    @if(!empty($product->size))
                        <div class="text-gray-500">Ukuran Tanaman</div>
                        <div class="col-span-2 text-gray-900 capitalize">{{ $product->size }}</div>
                    @endif
                    
                    @if(!empty($product->light_requirement))
                        <div class="text-gray-500">Kebutuhan Cahaya</div>
                        <div class="col-span-2 text-gray-900">{{ $product->light_requirement }}</div>
                    @endif

                    @if(!empty($product->care_level))
                        <div class="text-gray-500">Saran Perawatan</div>
                        <div class="col-span-2 text-gray-900">{{ $product->care_level }}</div>
                    @endif
                    
                    @if(!empty($product->weight))
                        <div class="text-gray-500">Berat Produk</div>
                        <div class="col-span-2 text-gray-900">{{ number_format($product->weight, 0, ',', '.') }} Gram</div>
                    @endif
                    
                    @if(!empty($product->bentuk_pupuk))
                        <div class="text-gray-500">Bentuk Pupuk</div>
                        <div class="col-span-2 text-gray-900 capitalize">{{ $product->bentuk_pupuk }}</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="border-t border-gray-200">
            <button class="w-full py-6 flex justify-between items-center text-left focus:outline-none group" onclick="toggleAccordion('deskripsi')">
                <h3 class="text-lg font-bold text-gray-900 group-hover:text-[#247a6b] transition bg-gray-50 px-4 py-2 rounded-md">Deskripsi Produk</h3>
                <i id="icon-deskripsi" class="fas fa-chevron-down text-gray-400 accordion-icon"></i>
            </button>
            <div id="content-deskripsi" class="accordion-content text-gray-700 text-[15px] leading-relaxed whitespace-pre-line pb-4 px-4">
                {{ $product->description ?? 'Tidak ada deskripsi detail untuk produk ini.' }}
            </div>
        </div>
    </div>

    <!-- BAGIAN ULASAN PEMBELI -->
    <div class="max-w-7xl mx-auto px-4 md:px-8 py-10 mb-2 border-t border-gray-200">
        <h3 class="text-2xl font-bold text-gray-900 mb-8">Ulasan Pembeli</h3>
        
        <div class="flex flex-col md:flex-row items-center gap-8 md:gap-12">
            <!-- Summary Rating -->
            <div class="w-full md:w-1/4 text-center md:text-left flex flex-col items-center md:items-start border-b md:border-b-0 md:border-r border-gray-200 pb-8 md:pb-0 md:pr-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ $ratingText }}</h2>
                <div class="flex text-[#00b67a] gap-1 mb-2">
                    @for($i = 1; $i <= 5; $i++)
                        @if($totalReviews == 0)
                            <i class="far fa-star fa-2x text-gray-300"></i>
                        @elseif($i <= floor($avgRating))
                            <i class="fas fa-star fa-2x"></i>
                        @elseif($i == ceil($avgRating) && $avgRating - floor($avgRating) > 0)
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

            <!-- Slider Ulasan -->
            <div class="w-full md:w-3/4 relative" id="product-review-section">
                @if($productReviews->count() > 0)
                    <div class="swiper productReviewSwiper py-4 px-1">
                        <div class="swiper-wrapper">
                            @foreach($productReviews as $review)
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
                        <p class="text-gray-400 text-sm mt-1">Jadilah yang pertama memberikan ulasan untuk produk ini setelah membeli!</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- BAGIAN PRODUK TERKAIT (MUNGKIN ANDA JUGA SUKA) -->
    @if(isset($relatedProducts) && $relatedProducts->count() > 0)
    <div class="w-full bg-[#f9fafb] py-12 border-t border-gray-200">
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
                
                <!-- Panah Slider Ala Patch Plants (Kotak Hijau di Samping) -->
                <div class="swiper-button-next !bg-[#247a6b] !text-white !w-10 !h-10 !rounded-none shadow-md hidden md:flex" style="right: -15px;"></div>
                <div class="swiper-button-prev !bg-[#247a6b] !text-white !w-10 !h-10 !rounded-none shadow-md hidden md:flex" style="left: -15px;"></div>
            </div>
        </div>
    </div>
    @endif
    <!-- BAGIAN INFO PENGIRIMAN & LAYANAN (DARI HOME) -->
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
    <!-- AKHIR BAGIAN INFO PENGIRIMAN & LAYANAN -->

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

    <!-- MODAL KERANJANG/BELI SEKARANG -->
    <div id="actionModal" class="fixed inset-0 z-[1000] hidden items-end md:items-center justify-center">
        <div class="absolute inset-0 bg-black/60 transition-opacity" onclick="closeModal()"></div>
        <div class="relative bg-white w-full md:w-[500px] rounded-t-xl md:rounded-xl shadow-2xl flex flex-col max-h-[90vh] animate-[slideUp_0.3s_ease-out]">
            <button onclick="closeModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 z-10"><i class="fas fa-times text-xl"></i></button>
            <div class="flex gap-4 p-5 border-b border-gray-100">
                <div class="w-24 h-24 rounded-sm border border-gray-200 overflow-hidden bg-white flex-shrink-0 -mt-8 shadow-md">
                    <img id="modalImg" src="{{ isset($product->images) && $product->images->isNotEmpty() ? asset('storage/'.$product->images->first()->image_path) : '' }}" class="w-full h-full object-cover">
                </div>
                <div class="pt-2">
                    <p id="modalPrice" class="text-gray-900 text-xl font-bold">{{ $priceText }}</p>
                    <p class="text-gray-500 text-sm mt-1">Stok: <span id="modalStock" class="font-medium text-gray-800">{{ $totalStock }}</span></p>
                </div>
            </div>
            <div class="p-5 overflow-y-auto hide-scroll flex-1">
                @if($hasVariations)
                    <div class="mb-6">
                        <h4 class="text-gray-700 text-sm font-medium mb-3">{{ $product->variations->first()->variation_name ?? 'Pilih Variasi' }}</h4>
                        <div class="flex flex-wrap gap-2.5">
                            @foreach($product->variations as $var)
                                <button type="button" 
                                        class="modal-var-btn border border-gray-300 text-gray-700 py-1.5 px-4 rounded-sm hover:border-[#247a6b] hover:text-[#247a6b] transition text-sm font-medium bg-white"
                                        data-var-id="{{ $var->id }}" data-price="{{ $var->price }}" data-stock="{{ $var->stock }}" data-image="{{ $var->image_path ? asset('storage/'.$var->image_path) : '' }}"
                                        onmouseenter="hoverVar(this)" onmouseleave="leaveVar()" onclick="selectVar(this)">
                                    {{ $var->variation_option }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif
                <div class="flex justify-between items-center py-2 border-t border-gray-50 mt-2">
                    <h4 class="text-gray-700 text-sm font-medium">Jumlah</h4>
                    <div class="flex items-center border border-gray-300 rounded-sm bg-white">
                        <button type="button" onclick="minQty()" class="px-3 py-1 text-gray-500 hover:bg-gray-100 transition"><i class="fas fa-minus text-xs"></i></button>
                        <input type="text" id="qtyInput" value="1" readonly class="w-10 text-center text-gray-900 text-sm font-medium border-x border-gray-300 py-1 focus:outline-none bg-white">
                        <button type="button" onclick="addQty()" class="px-3 py-1 text-gray-500 hover:bg-gray-100 transition"><i class="fas fa-plus text-xs"></i></button>
                    </div>
                </div>
            </div>
            <div class="p-4 border-t border-gray-100 bg-white md:rounded-b-xl">
                @guest
                    <a href="{{ route('login') }}" id="modalActionBtn" class="block w-full text-center font-bold py-3 rounded-sm transition uppercase text-sm tracking-wide border-2 bg-[#247a6b] text-white border-[#247a6b]">Lanjut</a>
                @else
                    <button type="button" id="modalActionBtn" onclick="processAction({{ $product->id }})" class="w-full font-bold py-3 rounded-sm transition uppercase text-sm tracking-wide border-2 disabled:bg-gray-400 disabled:border-gray-400 disabled:cursor-not-allowed bg-[#247a6b] text-white border-[#247a6b]">Lanjut</button>
                @endguest
            </div>
        </div>
    </div>

    <style> @keyframes slideUp { from { transform: translateY(100%); } to { transform: translateY(0); } } </style>
@endsection

@push('scripts')
    <!-- Script Swiper -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script>
        // INIT SWIPER ULASAN PRODUK
        var productReviewSwiper = new Swiper(".productReviewSwiper", {
            slidesPerView: 1, 
            spaceBetween: 20,
            loop: false,
            observer: true,
            observeParents: true,
            navigation: {
                nextEl: "#product-review-section .swiper-button-next", 
                prevEl: "#product-review-section .swiper-button-prev", 
            },
            breakpoints: {
                640: { slidesPerView: 2, spaceBetween: 20 },
                1024: { slidesPerView: 3, spaceBetween: 25 }, 
            }
        });

        // INIT SWIPER PRODUK TERKAIT (MUNGKIN ANDA JUGA SUKA)
        var relatedSwiper = new Swiper(".relatedSwiper", {
            slidesPerView: 2, 
            spaceBetween: 15,
            slidesPerGroup: 1, // Geser cuma 1 kotak tiap di-klik
            loop: true,        // Muter terus menerus tiada henti
            autoplay: {
                delay: 4000,   // Jalan sendiri tiap 4 detik
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

        const defaultTotalStock = '{{ $totalStock }}';
        const defaultPriceText = '{{ $priceText }}';
        const defaultImg = "{{ isset($product->images) && $product->images->isNotEmpty() ? asset('storage/'.$product->images->first()->image_path) : '' }}";
        
        let activeVarBtn = null;
        let currentActionType = ''; 

        function updateModalButton(stock) {
            const btnAction = document.getElementById('modalActionBtn');
            const baseClasses = "w-full font-bold py-3 rounded-sm transition uppercase text-sm tracking-wide border-2 block text-center ";
            
            if (stock <= 0) {
                btnAction.disabled = true;
                btnAction.innerText = 'STOK HABIS';
                btnAction.className = baseClasses + "bg-gray-400 border-gray-400 text-white cursor-not-allowed";
            } else {
                btnAction.disabled = false;
                if (currentActionType === 'cart') {
                    btnAction.innerText = 'MASUKKAN KERANJANG';
                    btnAction.className = baseClasses + "bg-white text-[#247a6b] border-[#247a6b] hover:bg-[#f0f7f5]";
                } else {
                    btnAction.innerText = 'BELI SEKARANG';
                    btnAction.className = baseClasses + "bg-[#247a6b] text-white border-[#247a6b] hover:bg-[#1b5e52] hover:border-[#1b5e52]";
                }
            }
        }

        function hoverVar(btn) {
            let stock = parseInt(btn.getAttribute('data-stock'));
            let price = btn.getAttribute('data-price');
            let img = btn.getAttribute('data-image');
            
            document.getElementById('modalStock').innerText = stock;
            document.getElementById('modalPrice').innerText = 'Rp' + parseInt(price).toLocaleString('id-ID');
            
            updateModalButton(stock);
            
            if(img && img !== '') document.getElementById('modalImg').src = img;
            else document.getElementById('modalImg').src = defaultImg;
        }

        function leaveVar() {
            if(activeVarBtn) hoverVar(activeVarBtn);
            else {
                document.getElementById('modalStock').innerText = defaultTotalStock;
                document.getElementById('modalPrice').innerText = defaultPriceText;
                document.getElementById('modalImg').src = defaultImg;
                updateModalButton(parseInt(defaultTotalStock));
            }
        }

        function selectVar(btn) {
            document.querySelectorAll('.modal-var-btn').forEach(el => {
                el.classList.remove('border-[#247a6b]', 'text-[#247a6b]', 'bg-[#f0f7f5]'); 
                el.classList.add('border-gray-300', 'text-gray-700', 'bg-white');
            });
            btn.classList.remove('border-gray-300', 'text-gray-700', 'bg-white'); 
            btn.classList.add('border-[#247a6b]', 'text-[#247a6b]', 'bg-[#f0f7f5]');
            activeVarBtn = btn;
            hoverVar(btn);
        }

        function changeMainMedia(url, ext, element, isThumbnailClick = false, variationId = null) {
            const mainImg = document.getElementById('mainImage');
            const mainVid = document.getElementById('mainVideo');
            document.querySelectorAll('.media-thumbnail').forEach(el => {
                el.classList.remove('border-[#247a6b]'); el.classList.add('border-transparent');
            });
            element.classList.remove('border-transparent'); element.classList.add('border-[#247a6b]');
            if(ext === 'mp4') { if(mainImg) mainImg.classList.add('hidden'); if(mainVid) { mainVid.classList.remove('hidden'); mainVid.src = url; mainVid.play(); } } 
            else { if(mainVid) { mainVid.classList.add('hidden'); mainVid.pause(); } if(mainImg) { mainImg.classList.remove('hidden'); mainImg.src = url; } }
            if(isThumbnailClick) { if(variationId) { let varBtn = document.querySelector(`.variation-btn[data-var-id="${variationId}"]`); if(varBtn) selectVariation(varBtn); } else { document.querySelectorAll('.variation-btn').forEach(el => { el.classList.remove('border-[#247a6b]', 'text-[#247a6b]'); el.classList.add('border-gray-300', 'text-gray-700'); }); document.getElementById('productPriceDisplay').innerText = defaultPriceText; let stockDisp = document.getElementById('stockDisplay'); if(stockDisp) stockDisp.innerText = defaultTotalStock; activeVarBtn = null; } }
        }
        
        function toggleAccordion(id) { const content = document.getElementById('content-' + id); const icon = document.getElementById('icon-' + id); if (content.classList.contains('open')) { content.classList.remove('open'); icon.classList.remove('rotate'); } else { content.classList.add('open'); icon.classList.add('rotate'); } }
        
        function selectVariation(btn) { document.querySelectorAll('.variation-btn').forEach(el => { el.classList.remove('border-[#247a6b]', 'text-[#247a6b]'); el.classList.add('border-gray-300', 'text-gray-700'); }); btn.classList.remove('border-gray-300', 'text-gray-700'); btn.classList.add('border-[#247a6b]', 'text-[#247a6b]'); let newPrice = btn.getAttribute('data-price'); document.getElementById('productPriceDisplay').innerText = 'Rp' + parseInt(newPrice).toLocaleString('id-ID'); let varStock = btn.getAttribute('data-stock'); let stockDisp = document.getElementById('stockDisplay'); if(stockDisp) stockDisp.innerText = varStock; let varImage = btn.getAttribute('data-image'); let varId = btn.getAttribute('data-var-id'); if (varImage && varImage !== '') { const mainImg = document.getElementById('mainImage'); const mainVid = document.getElementById('mainVideo'); if(mainVid) { mainVid.classList.add('hidden'); mainVid.pause(); } if(mainImg) { mainImg.classList.remove('hidden'); mainImg.src = varImage; } document.querySelectorAll('.media-thumbnail').forEach(el => { el.classList.remove('border-[#247a6b]'); el.classList.add('border-transparent'); if(el.getAttribute('data-var-thumb-id') == varId) { el.classList.remove('border-transparent'); el.classList.add('border-[#247a6b]'); el.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' }); } }); } }
        
        function addQtyMain() { 
            let qty = document.getElementById('qtyInputMain'); 
            let maxStock = parseInt(document.getElementById('stockDisplay').innerText);
            if(qty && parseInt(qty.value) < maxStock) { 
                qty.value = parseInt(qty.value) + 1; 
            } else {
                showToast('Mentok Bre! Stok sisa ' + maxStock + ' aja.');
            }
        }
        function minQtyMain() { 
            let qty = document.getElementById('qtyInputMain'); 
            if(qty && parseInt(qty.value) > 1) { qty.value = parseInt(qty.value) - 1; } 
        }
        
        function openModal(actionType) { 
            currentActionType = actionType; 
            const modal = document.getElementById('actionModal'); 
            let mainQty = document.getElementById('qtyInputMain'); 
            let modalQty = document.getElementById('qtyInput'); 
            if(mainQty && modalQty) { modalQty.value = mainQty.value; } 
            
            let currentStock = activeVarBtn ? parseInt(activeVarBtn.getAttribute('data-stock')) : parseInt(defaultTotalStock);
            updateModalButton(currentStock);
            
            modal.classList.remove('hidden'); 
            modal.classList.add('flex'); 
            document.body.style.overflow = 'hidden'; 
        }

        function closeModal() { const modal = document.getElementById('actionModal'); modal.classList.add('hidden'); modal.classList.remove('flex'); document.body.style.overflow = 'auto'; }
        
        function addQty() { 
            let qty = document.getElementById('qtyInput'); 
            let maxStock = parseInt(document.getElementById('modalStock').innerText);
            if(qty && parseInt(qty.value) < maxStock) { 
                qty.value = parseInt(qty.value) + 1; 
            } else {
                showToast('Mentok Bre! Stok sisa ' + maxStock + ' aja.');
            }
        }
        function minQty() { 
            let qty = document.getElementById('qtyInput'); 
            if(parseInt(qty.value) > 1) { qty.value = parseInt(qty.value) - 1; } 
        }
        
        function processAction(productId) { 
            let hasVariations = {{ $product->variations->count() > 0 ? 'true' : 'false' }}; 
            if(hasVariations && !activeVarBtn) { 
                showToast('Pilih varian dulu ya Bre!'); 
                return; 
            } 
            
            let variationId = activeVarBtn ? activeVarBtn.getAttribute('data-var-id') : null; 
            let qty = document.getElementById('qtyInput').value; 
            
            const btn = document.getElementById('modalActionBtn'); 
            const originalText = btn.innerText; 
            btn.innerText = 'Loading...'; 
            btn.disabled = true; 
            
            fetch('{{ route('cart.add') }}', { 
                method: 'POST', 
                headers: { 
                    'X-CSRF-TOKEN': '{{ csrf_token() }}', 
                    'Content-Type': 'application/json', 
                    'Accept': 'application/json' 
                }, 
                body: JSON.stringify({ 
                    product_id: productId, 
                    variation_id: variationId, 
                    qty: qty 
                }) 
            })
            .then(res => res.json())
            .then(data => { 
                btn.innerText = originalText; 
                btn.disabled = false; 
                
                if(data.success) { 
                    closeModal(); 
                    
                    if(currentActionType === 'buy') { 
                        showToast('Menyiapkan pembayaran...');
                        setTimeout(() => {
                            window.location.href = "{{ route('checkout') }}"; 
                        }, 1000);
                        
                    } else { 
                        document.querySelectorAll('.cart-badge').forEach(el => el.innerText = data.cart_count); 
                        showToast(data.message); 
                        
                        setTimeout(() => { window.location.reload(); }, 1000); 
                    } 
                } 
            })
            .catch(error => { 
                btn.innerText = originalText; 
                btn.disabled = false; 
                console.error('Error:', error); 
                showToast('Terjadi kesalahan jaringan!');
            }); 
        }

        function showToast(message) { 
            const toast = document.createElement('div'); 
            toast.className = 'fixed top-24 right-5 z-[9999] flex items-center w-full max-w-xs p-4 space-x-3 text-white bg-[#247a6b] rounded-md shadow-xl border-l-4 border-[#1b5e52] transform translate-x-full transition-transform duration-500'; 
            toast.innerHTML = `<div class="text-sm font-medium"><i class="fas fa-check-circle mr-2"></i>${message}</div>`; 
            document.body.appendChild(toast); 
            setTimeout(() => toast.classList.remove('translate-x-full'), 100); 
            setTimeout(() => { 
                toast.classList.add('translate-x-full'); 
                setTimeout(() => toast.remove(), 500); 
            }, 3000); 
        }
    </script>
@endpush