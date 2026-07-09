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
@endphp

@section('title', (isset($isTop20) && $isTop20 ? 'Top 20 Panambas Terlaris' : ($categorySelected ?? 'Semua Produk')) . ' - Panambas')

@push('styles')
<style>
    /* Menghilangkan tanda panah bawaan HTML pada Accordion */
    details > summary::-webkit-details-marker {
        display: none;
    }
</style>
@endpush

@section('content')
    <div class="w-full bg-[#f9fafb] py-16 border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 md:px-8 text-center">
            <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4 font-serif capitalize">
                @if(isset($isTop20) && $isTop20)
                    Top 20 Panambas Terlaris
                @elseif($categorySelected)
                    @php
                        $catParts = explode(' > ', $categorySelected);
                        $mainTitle = end($catParts); 
                    @endphp
                    {{ $mainTitle }}
                @else
                    Semua Produk
                @endif
            </h1>
            @if($categorySelected && str_contains($categorySelected, ' > '))
                <p class="text-[#247a6b] font-medium text-sm mb-2 uppercase tracking-widest">
                    {{ explode(' > ', $categorySelected)[0] }}
                </p>
            @endif
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 md:px-8 py-12 flex flex-col md:flex-row gap-8">
        
        <div class="w-full md:w-1/4">
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden sticky top-24 shadow-sm">
                <div class="bg-gray-50 px-5 py-4 flex justify-between items-center border-b border-gray-100">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-filter text-[#247a6b] text-[13px]"></i>
                        <h3 class="font-bold text-gray-800 text-[14px] uppercase tracking-wider">Filter</h3>
                    </div>
                    <a href="{{ route('front.shop', ['category' => request('category')]) }}" class="text-[12px] font-medium text-gray-500 hover:text-red-500 transition-colors">Reset</a>
                </div>

                <form action="{{ route('front.shop') }}" method="GET" id="filterForm" class="px-5 py-2 divide-y divide-gray-100">
                    @if(request('category'))
                        <input type="hidden" name="category" value="{{ request('category') }}">
                    @endif

                    @if(count($brandsCount) > 0)
                    <details class="group py-4" open>
                        <summary class="flex justify-between items-center font-semibold cursor-pointer list-none text-gray-800 text-[14px] outline-none hover:text-[#247a6b] transition-colors">
                            Merek
                            <span class="transition-transform duration-300 group-open:-rotate-180 text-gray-400"><i class="fas fa-chevron-down text-[10px]"></i></span>
                        </summary>
                        <div class="space-y-1 mt-3">
                            @foreach($brandsCount as $name => $count)
                            <label class="flex items-center justify-between cursor-pointer group/label py-1.5">
                                <div class="flex items-center gap-3">
                                    <input type="checkbox" name="brands[]" value="{{ $name }}" class="form-checkbox h-4 w-4 text-[#247a6b] rounded-sm border-gray-300 focus:ring-[#247a6b] transition-all cursor-pointer shadow-sm" onchange="document.getElementById('filterForm').submit();" {{ in_array($name, request('brands', [])) ? 'checked' : '' }}>
                                    <span class="text-gray-600 text-[13px] capitalize group-hover/label:text-[#247a6b] transition-colors">{{ $name }}</span>
                                </div>
                                <span class="text-[11px] font-medium text-gray-400 bg-gray-50 px-2 py-0.5 rounded-md border border-gray-100">{{ $count }}</span>
                            </label>
                            @endforeach
                        </div>
                    </details>
                    @endif

                    @if(count($sizesCount) > 0)
                    <details class="group py-4" open>
                        <summary class="flex justify-between items-center font-semibold cursor-pointer list-none text-gray-800 text-[14px] outline-none hover:text-[#247a6b] transition-colors">
                            Ukuran Tanaman
                            <span class="transition-transform duration-300 group-open:-rotate-180 text-gray-400"><i class="fas fa-chevron-down text-[10px]"></i></span>
                        </summary>
                        <div class="space-y-1 mt-3">
                            @foreach($sizesCount as $name => $count)
                            <label class="flex items-center justify-between cursor-pointer group/label py-1.5">
                                <div class="flex items-center gap-3">
                                    <input type="checkbox" name="sizes[]" value="{{ $name }}" class="form-checkbox h-4 w-4 text-[#247a6b] rounded-sm border-gray-300 focus:ring-[#247a6b] transition-all cursor-pointer shadow-sm" onchange="document.getElementById('filterForm').submit();" {{ in_array($name, request('sizes', [])) ? 'checked' : '' }}>
                                    <span class="text-gray-600 text-[13px] capitalize group-hover/label:text-[#247a6b] transition-colors">{{ $name }}</span>
                                </div>
                                <span class="text-[11px] font-medium text-gray-400 bg-gray-50 px-2 py-0.5 rounded-md border border-gray-100">{{ $count }}</span>
                            </label>
                            @endforeach
                        </div>
                    </details>
                    @endif

                    @if(count($lightReqsCount) > 0)
                    <details class="group py-4" open>
                        <summary class="flex justify-between items-center font-semibold cursor-pointer list-none text-gray-800 text-[14px] outline-none hover:text-[#247a6b] transition-colors">
                            Cahaya
                            <span class="transition-transform duration-300 group-open:-rotate-180 text-gray-400"><i class="fas fa-chevron-down text-[10px]"></i></span>
                        </summary>
                        <div class="space-y-1 mt-3">
                            @foreach($lightReqsCount as $name => $count)
                            <label class="flex items-center justify-between cursor-pointer group/label py-1.5">
                                <div class="flex items-center gap-3">
                                    <input type="checkbox" name="light_reqs[]" value="{{ $name }}" class="form-checkbox h-4 w-4 text-[#247a6b] rounded-sm border-gray-300 focus:ring-[#247a6b] transition-all cursor-pointer shadow-sm" onchange="document.getElementById('filterForm').submit();" {{ in_array($name, request('light_reqs', [])) ? 'checked' : '' }}>
                                    <span class="text-gray-600 text-[13px] capitalize group-hover/label:text-[#247a6b] transition-colors">{{ $name }}</span>
                                </div>
                                <span class="text-[11px] font-medium text-gray-400 bg-gray-50 px-2 py-0.5 rounded-md border border-gray-100">{{ $count }}</span>
                            </label>
                            @endforeach
                        </div>
                    </details>
                    @endif

                    @if(count($careLevelsCount) > 0)
                    <details class="group py-4" open>
                        <summary class="flex justify-between items-center font-semibold cursor-pointer list-none text-gray-800 text-[14px] outline-none hover:text-[#247a6b] transition-colors">
                            Perawatan
                            <span class="transition-transform duration-300 group-open:-rotate-180 text-gray-400"><i class="fas fa-chevron-down text-[10px]"></i></span>
                        </summary>
                        <div class="space-y-1 mt-3">
                            @foreach($careLevelsCount as $name => $count)
                            <label class="flex items-center justify-between cursor-pointer group/label py-1.5">
                                <div class="flex items-center gap-3">
                                    <input type="checkbox" name="care_levels[]" value="{{ $name }}" class="form-checkbox h-4 w-4 text-[#247a6b] rounded-sm border-gray-300 focus:ring-[#247a6b] transition-all cursor-pointer shadow-sm" onchange="document.getElementById('filterForm').submit();" {{ in_array($name, request('care_levels', [])) ? 'checked' : '' }}>
                                    <span class="text-gray-600 text-[13px] capitalize group-hover/label:text-[#247a6b] transition-colors">{{ $name }}</span>
                                </div>
                                <span class="text-[11px] font-medium text-gray-400 bg-gray-50 px-2 py-0.5 rounded-md border border-gray-100">{{ $count }}</span>
                            </label>
                            @endforeach
                        </div>
                    </details>
                    @endif

                    @if(count($bentukPupukCount) > 0)
                    <details class="group py-4" open>
                        <summary class="flex justify-between items-center font-semibold cursor-pointer list-none text-gray-800 text-[14px] outline-none hover:text-[#247a6b] transition-colors">
                            Bentuk Pupuk
                            <span class="transition-transform duration-300 group-open:-rotate-180 text-gray-400"><i class="fas fa-chevron-down text-[10px]"></i></span>
                        </summary>
                        <div class="space-y-1 mt-3">
                            @foreach($bentukPupukCount as $name => $count)
                            <label class="flex items-center justify-between cursor-pointer group/label py-1.5">
                                <div class="flex items-center gap-3">
                                    <input type="checkbox" name="bentuk_pupuks[]" value="{{ $name }}" class="form-checkbox h-4 w-4 text-[#247a6b] rounded-sm border-gray-300 focus:ring-[#247a6b] transition-all cursor-pointer shadow-sm" onchange="document.getElementById('filterForm').submit();" {{ in_array($name, request('bentuk_pupuks', [])) ? 'checked' : '' }}>
                                    <span class="text-gray-600 text-[13px] capitalize group-hover/label:text-[#247a6b] transition-colors">{{ $name }}</span>
                                </div>
                                <span class="text-[11px] font-medium text-gray-400 bg-gray-50 px-2 py-0.5 rounded-md border border-gray-100">{{ $count }}</span>
                            </label>
                            @endforeach
                        </div>
                    </details>
                    @endif

                    @if(count($originsCount) > 0)
                    <details class="group py-4" open>
                        <summary class="flex justify-between items-center font-semibold cursor-pointer list-none text-gray-800 text-[14px] outline-none hover:text-[#247a6b] transition-colors">
                            Asal Produk
                            <span class="transition-transform duration-300 group-open:-rotate-180 text-gray-400"><i class="fas fa-chevron-down text-[10px]"></i></span>
                        </summary>
                        <div class="space-y-1 mt-3">
                            @foreach($originsCount as $name => $count)
                            <label class="flex items-center justify-between cursor-pointer group/label py-1.5">
                                <div class="flex items-center gap-3">
                                    <input type="checkbox" name="origins[]" value="{{ $name }}" class="form-checkbox h-4 w-4 text-[#247a6b] rounded-sm border-gray-300 focus:ring-[#247a6b] transition-all cursor-pointer shadow-sm" onchange="document.getElementById('filterForm').submit();" {{ in_array($name, request('origins', [])) ? 'checked' : '' }}>
                                    <span class="text-gray-600 text-[13px] capitalize group-hover/label:text-[#247a6b] transition-colors">{{ $name }}</span>
                                </div>
                                <span class="text-[11px] font-medium text-gray-400 bg-gray-50 px-2 py-0.5 rounded-md border border-gray-100">{{ $count }}</span>
                            </label>
                            @endforeach
                        </div>
                    </details>
                    @endif
                </form>
            </div>
        </div>

        <div class="w-full md:w-3/4">
            <div class="flex justify-between items-center mb-6">
                <p class="text-gray-500 text-sm">{{ $products->total() }} results</p>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
                @forelse($products as $product)
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
                            
                        $totalReviews = $ratingQuery->count();
                        $avgRating = $totalReviews > 0 ? $ratingQuery->avg('rating') : 0;
                        $avgRatingFormatted = $totalReviews > 0 ? number_format($avgRating, 1) : '0';
                    @endphp
                    
                    <a href="{{ route('front.product', $product->id) }}" class="group block bg-white border border-gray-200 hover:border-[#247a6b] hover:shadow-md transition-all duration-300 rounded-sm overflow-hidden flex flex-col h-full">
                        <div class="relative aspect-square overflow-hidden bg-gray-100">
                            @if(isset($product->images) && $product->images->isNotEmpty())
                                <img src="{{ asset('storage/' . $product->images->first()->image_path) }}" alt="{{ $product->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            @else
                                <div class="flex items-center justify-center h-full text-gray-300"><i class="fas fa-image fa-2x opacity-50"></i></div>
                            @endif

                            <div class="absolute top-0 left-0 bg-[#247a6b] text-white text-[10px] font-bold px-2 py-1 rounded-br-lg shadow-sm tracking-wide">
                                {{ explode(' > ', $product->category_id)[0] ?? 'Mall' }}
                            </div>
                        </div>

                        <div class="p-2 md:p-3 flex-1 flex flex-col bg-white">
                            <h3 class="text-[12px] md:text-[14px] text-gray-800 mb-2 line-clamp-2 leading-tight group-hover:text-[#247a6b] transition-colors">
                                {{ $product->title }}
                            </h3>

                            <div class="mt-auto flex flex-col gap-1.5">
                                <p class="font-bold text-gray-900 text-[14px] md:text-[16px]">{{ $priceDisplay }}</p>

                                <div class="flex items-center text-[10px] md:text-[11px] text-gray-500">
                                    @if($totalReviews > 0)
                                        <i class="fas fa-star text-[#ffc400] mr-1"></i>
                                    @else
                                        <i class="far fa-star text-gray-300 mr-1"></i>
                                    @endif
                                    <span class="mr-1 font-medium">{{ $avgRatingFormatted }}</span> 
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
                @empty
                    <div class="col-span-2 lg:col-span-3 py-16 flex flex-col items-center justify-center text-center bg-gray-50 rounded-lg border border-dashed border-gray-300">
                        <i class="fas fa-box-open text-6xl text-gray-300 mb-4"></i>
                        <h3 class="text-xl font-bold text-gray-800 mb-2">Belum Ada Produk</h3>
                        <p class="text-gray-500 max-w-md mx-auto">
                            Mohon maaf, saat ini koleksi untuk kategori <strong>{{ $categorySelected }}</strong> sedang kosong atau habis terjual. Silakan cek kembali nanti atau lihat kategori kami yang lain.
                        </p>
                        <a href="{{ route('front.shop') }}" class="mt-6 bg-[#247a6b] text-white px-6 py-2 rounded font-medium hover:bg-[#1b5e52] transition">
                            Lihat Semua Koleksi
                        </a>
                    </div>
                @endforelse
            </div>

            <div class="mt-12 flex justify-center">
                {{ $products->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
@endsection