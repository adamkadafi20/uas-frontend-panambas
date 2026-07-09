@extends('admin.layouts.app')

@section('content')
<section class="content-header">                    
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Dashboard</h1>
            </div>
        </div>
    </div>
</section>

<section class="content pb-4">
    <div class="container-fluid">
        
        <div class="row">
            <div class="col-lg-4 col-6">
                <div class="card shadow-sm border-0" style="border-radius: 8px;">
                    <div class="card-body text-center py-4">
                        <h3 style="color: #247a6b; font-weight: bold; margin-bottom: 5px;">{{ $totalPending ?? 0 }}</h3>
                        <p style="color: #333; font-size: 14px; margin-bottom: 0;">Pengiriman Perlu Diproses</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-6">
                <div class="card shadow-sm border-0" style="border-radius: 8px;">
                    <div class="card-body text-center py-4">
                        <h3 style="color: #247a6b; font-weight: bold; margin-bottom: 5px;">{{ $totalShipped ?? 0 }}</h3>
                        <p style="color: #333; font-size: 14px; margin-bottom: 0;">Pengiriman Telah Diproses</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-6">
                <div class="card shadow-sm border-0" style="border-radius: 8px;">
                    <div class="card-body text-center py-4">
                        <h3 style="color: #247a6b; font-weight: bold; margin-bottom: 5px;">{{ $totalCancelled ?? 0 }}</h3>
                        <p style="color: #333; font-size: 14px; margin-bottom: 0;">Pengembalian/Pembatalan</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4 shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3 border-b pb-2">
                    <h5 class="mb-0 text-dark"><strong>Analisis Toko <span class="text-[#247a6b] text-sm">({{ \Carbon\Carbon::now()->translatedFormat('F Y') }})</span></strong></h5>
                    <div class="card-tools">
                        <a href="{{ route('admin.shop.index') }}" class="text-[#247a6b] font-medium" style="text-decoration: none;">
                            Lainnya <i class="fas fa-chevron-right small"></i>
                        </a>
                    </div>
                </div>
                
                <div class="mb-4">
                    <small class="text-muted"><i class="fas fa-clock text-[#247a6b] mr-1"></i> Waktu update terakhir: {{ now()->format('H:i') }} (Data Khusus Bulan Ini)</small>
                </div>

                <div class="row text-center">
                    <div class="col-md-3 border-right border-gray-200">
                        <p class="text-muted mb-1 small text-uppercase tracking-wider">Penjualan</p>
                        <h4 class="text-[#247a6b]"><strong>Rp {{ number_format($monthSales ?? 0, 0, ',', '.') }}</strong></h4>
                    </div>
                    <div class="col-md-2 border-right border-gray-200">
                        <p class="text-muted mb-1 small text-uppercase tracking-wider">Total Pengunjung</p>
                        <h4 class="text-dark"><strong>{{ $monthVisitors ?? 0 }}</strong></h4>
                    </div>
                    <div class="col-md-2 border-right border-gray-200">
                        <p class="text-muted mb-1 small text-uppercase tracking-wider">Produk Diklik</p>
                        <h4 class="text-dark"><strong>{{ $monthClicks ?? 0 }}</strong></h4>
                    </div>
                    <div class="col-md-2 border-right border-gray-200">
                        <p class="text-muted mb-1 small text-uppercase tracking-wider">Pesanan</p>
                        <h4 class="text-dark"><strong>{{ $monthOrders ?? 0 }}</strong></h4>
                    </div>
                    <div class="col-md-3">
                        <p class="text-muted mb-1 small text-uppercase tracking-wider">Tingkat Konversi</p>
                        <h4 class="text-dark"><strong>{{ number_format($monthConv ?? 0, 2) }}%</strong></h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="row d-flex align-items-stretch">
            
            <div class="col-md-7 d-flex flex-column">
                <div class="card shadow-sm border-0 flex-fill">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                        <h5 class="card-title m-0 text-dark"><strong>Penjualan Best Seller</strong></h5>
                        <a href="{{ route('admin.shop.index') }}" class="text-[#247a6b] text-sm font-medium" style="text-decoration: none;">Lainnya <i class="fas fa-chevron-right small"></i></a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 text-nowrap">
                                <thead class="bg-gray-50 text-muted" style="font-size: 13px;">
                                    <tr>
                                        <th class="text-center border-0" style="width: 70px;">Peringkat</th>
                                        <th class="border-0">Informasi Produk</th>
                                        <th class="text-center border-0">Terjual</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        // Fungsi konversi K (1000 jadi 1K)
                                        $formatK = function($num) {
                                            if ($num >= 1000000) return rtrim(rtrim(number_format($num / 1000000, 1, '.', ''), '0'), '.') . 'M';
                                            elseif ($num >= 1000) return rtrim(rtrim(number_format($num / 1000, 1, '.', ''), '0'), '.') . 'k';
                                            return $num;
                                        };
                                    @endphp

                                    @forelse($topProducts ?? [] as $index => $prod)
                                        @php
                                            $img = $prod->images->first();
                                            $totalSold = $prod->sold ?? 0;
                                        @endphp
                                        <tr>
                                            <td class="text-center border-gray-100">
                                                @if($index == 0) <i class="fas fa-crown fa-2x" style="color: #FFD700;" title="Juara 1"></i>
                                                @elseif($index == 1) <i class="fas fa-crown fa-2x" style="color: #C0C0C0;" title="Juara 2"></i>
                                                @elseif($index == 2) <i class="fas fa-crown fa-2x" style="color: #CD7F32;" title="Juara 3"></i>
                                                @else <span class="badge bg-gray-200 text-gray-600 px-2 py-1" style="font-size: 13px;">{{ $index + 1 }}</span> @endif
                                            </td>
                                            <td class="border-gray-100">
                                                <div class="d-flex align-items-center">
                                                    @if($img) <img src="{{ asset('storage/' . $img->image_path) }}" class="rounded mr-3 border" width="45" height="45" style="object-fit: cover;">
                                                    @else <div class="bg-gray-100 mr-3 rounded" style="width: 45px; height: 45px; display: flex; align-items: center; justify-content: center;"><i class="fas fa-leaf text-gray-400"></i></div> @endif
                                                    <div>
                                                        <a href="{{ route('front.product', $prod->id) }}" target="_blank" class="d-block text-dark font-medium text-wrap hover:text-[#247a6b] transition" style="width: 220px; font-size: 14px; line-height: 1.3;">{{ $prod->title }}</a>
                                                        <small class="text-muted">Stok: {{ $prod->qty ?? 'Habis' }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center font-bold text-[#247a6b] border-gray-100" style="font-size: 15px;">{{ $formatK($totalSold) }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" class="text-center py-4 text-muted"><i class="fas fa-box-open mb-2 opacity-50"></i><br>Belum ada penjualan.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-5 d-flex flex-column">
                <div class="card shadow-sm border-0 flex-fill">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                        <h5 class="card-title m-0 text-dark"><strong>Kategori Terlaris</strong></h5>
                        <a href="{{ route('categories.index') }}" class="text-[#247a6b] text-sm font-medium" style="text-decoration: none;">Lainnya <i class="fas fa-chevron-right small"></i></a>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped table-hover mb-0">
                            <thead class="bg-gray-50 text-muted" style="font-size: 13px;">
                                <tr>
                                    <th class="border-0">Nama Kategori</th>
                                    <th class="text-center border-0">Total Penjualan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topCategories ?? [] as $cat)
                                    <tr>
                                        <td class="border-gray-100 font-medium text-dark"><i class="fas fa-tag text-[#247a6b] mr-2 opacity-50"></i> {{ $cat->name }}</td>
                                        <td class="text-center border-gray-100"><span class="badge bg-[#e8f3f1] text-[#247a6b] px-3 py-1.5 rounded-full" style="font-size: 13px;">{{ $formatK($cat->total_sold) }} Produk</span></td>
                                    </tr>
                                @empty
                                    <tr><td class="font-medium"><i class="fas fa-tag text-[#247a6b] mr-2 opacity-50"></i> Bibit & Benih</td><td class="text-center"><span class="badge bg-[#e8f3f1] text-[#247a6b] px-3 py-1.5 rounded-full">0 Produk</span></td></tr>
                                    <tr><td class="font-medium"><i class="fas fa-tag text-[#247a6b] mr-2 opacity-50"></i> Tanaman Hias</td><td class="text-center"><span class="badge bg-[#e8f3f1] text-[#247a6b] px-3 py-1.5 rounded-full">0 Produk</span></td></tr>
                                    <tr><td class="font-medium"><i class="fas fa-tag text-[#247a6b] mr-2 opacity-50"></i> Media Tanam & Pupuk</td><td class="text-center"><span class="badge bg-[#e8f3f1] text-[#247a6b] px-3 py-1.5 rounded-full">0 Produk</span></td></tr>
                                    <tr><td class="font-medium"><i class="fas fa-tag text-[#247a6b] mr-2 opacity-50"></i> Peralatan Berkebun</td><td class="text-center"><span class="badge bg-[#e8f3f1] text-[#247a6b] px-3 py-1.5 rounded-full">0 Produk</span></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>                  
</section>
@endsection

@section('customJs')
<script>
    console.log("Dashboard Terupdate: Analisis Bulan Ini, Best Seller & Best Kategori Aktif!");
</script>
@endsection