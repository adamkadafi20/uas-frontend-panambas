@extends('admin.layouts.app')

@section('content')
<style>
    /* CSS Kostum Filter Shopee Style - Warna Panambas #247a6b */
    .periode-nav { color: #555; border-radius: 0; padding: 10px 15px; cursor: pointer; transition: 0.2s; border-left: 3px solid transparent; font-size: 14px; }
    .periode-nav:hover { background-color: #f8f9fa; }
    .periode-nav.active { color: #247a6b !important; font-weight: bold; background-color: #f0f7f5; border-left: 3px solid #247a6b; }
    .dropdown-menu-custom { min-width: 500px; padding: 0; border: 1px solid #e5e5e5; border-radius: 4px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); margin-top: 8px; }
    .cal-nav-btn { cursor: pointer; color: #888; padding: 5px 10px; transition: 0.2s; }
    .cal-nav-btn:hover { color: #247a6b; }
    .cal-item { cursor: pointer; border-radius: 4px; padding: 8px 0; transition: 0.2s; font-size: 14px; }
    .cal-item:not(.disabled):hover { background-color: #f0f7f5; color: #247a6b; }
    .cal-item.active { background-color: #247a6b !important; color: white !important; font-weight: bold; }
    .cal-item.disabled { color: #ccc; cursor: not-allowed; }
    .cal-day-header { font-weight: bold; color: #888; font-size: 12px; margin-bottom: 10px; }

    /* Metrik Card Style ala Shopee */
    .metric-card { cursor: pointer; border-top: 3px solid transparent; transition: 0.2s; }
    .metric-card:hover { background-color: #fdfdfd; }
    .metric-card.active-metric { border-top: 3px solid #247a6b; background-color: #fff; box-shadow: 0 -2px 10px rgba(0,0,0,0.02); }
</style>

<section class="content-header">
    <div class="container-fluid my-2">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Analisis Bisnis Toko</h1>
            </div>
        </div>
    </div>
</section>

<section class="content pb-5">
    <div class="container-fluid">

        <div class="card mb-4 shadow-sm border-0">
            <div class="card-body p-3 d-flex align-items-center" style="gap: 15px;">
                
                <div class="dropdown">
                    <button class="btn bg-white border d-flex align-items-center" type="button" id="periodeBtn" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="border-radius: 4px; padding: 6px 12px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); min-width: 280px;">
                        <span class="text-muted mr-2" style="font-size: 14px;">Periode Data</span>
                        <strong style="color: #333; font-size: 14px; flex-grow: 1; text-align: left;">{{ $labelDisplay ?? 'Hari Ini' }}</strong> 
                        <i class="far fa-calendar-alt text-muted ml-3"></i>
                    </button>
                    
                    <div class="dropdown-menu dropdown-menu-custom" aria-labelledby="periodeBtn" id="dropdownMenuCalendar">
                        <div class="d-flex" style="min-height: 320px;">
                            
                            <div class="bg-white border-right py-2" style="width: 180px;">
                                <div class="periode-nav" data-tab="realtime">Real-time</div>
                                <div class="periode-nav" data-tab="kemarin">Kemarin</div>
                                <hr class="my-2 border-light">
                                <div class="periode-nav d-flex justify-content-between align-items-center" data-tab="per_hari">Per Hari <i class="fas fa-chevron-right" style="font-size:10px;"></i></div>
                                <div class="periode-nav d-flex justify-content-between align-items-center" data-tab="per_minggu">Per Minggu <i class="fas fa-chevron-right" style="font-size:10px;"></i></div>
                                <div class="periode-nav d-flex justify-content-between align-items-center" data-tab="per_bulan">Per Bulan <i class="fas fa-chevron-right" style="font-size:10px;"></i></div>
                                <div class="periode-nav d-flex justify-content-between align-items-center" data-tab="per_tahun">Berdasarkan Tahun <i class="fas fa-chevron-right" style="font-size:10px;"></i></div>
                            </div>
                            
                            <div class="p-4 bg-white flex-fill position-relative d-flex flex-column">
                                <div id="cal-header" class="d-flex justify-content-between align-items-center mb-3 d-none">
                                    <div>
                                        <i class="fas fa-angle-double-left cal-nav-btn mr-2" id="btn-prev-year" title="Mundur Tahun/Dekade"></i>
                                        <i class="fas fa-angle-left cal-nav-btn" id="btn-prev-month" title="Mundur Bulan"></i>
                                    </div>
                                    <strong id="cal-title" style="font-size: 15px; color: #333;"></strong>
                                    <div>
                                        <i class="fas fa-angle-right cal-nav-btn mr-2" id="btn-next-month" title="Maju Bulan"></i>
                                        <i class="fas fa-angle-double-right cal-nav-btn" id="btn-next-year" title="Maju Tahun/Dekade"></i>
                                    </div>
                                </div>

                                <div id="cal-body" class="flex-grow-1 text-center"></div>
                                
                                <div class="mt-3 text-right border-top pt-3">
                                    <button type="button" class="btn btn-light border mr-2" id="btn-batal-cal" style="font-size: 13px;">Batal</button>
                                    <button type="button" class="btn text-white px-4" id="btn-terapkan-cal" style="background-color: #247a6b; font-size: 13px; font-weight: bold;">Terapkan</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @if(($periodeType ?? 'realtime') == 'realtime')
                    <span class="text-muted small"><i class="fas fa-clock text-[#247a6b]"></i> Diperbarui secara real-time hari ini.</span>
                @endif

                <form action="{{ route('admin.shop.index') }}" method="GET" id="realFilterForm" class="d-none">
                    <input type="hidden" name="periode_type" id="form_periode_type" value="{{ $periodeType ?? 'realtime' }}">
                    <input type="hidden" name="periode_value" id="form_periode_value" value="{{ $periodeValue ?? '' }}">
                    <input type="hidden" name="rank_by" id="form_rank_by" value="{{ $rankBy ?? 'penjualan' }}">
                    <input type="hidden" name="category" id="form_category" value="{{ $filterCategory ?? 'all' }}">
                </form>
            </div>
        </div>

        @php
            // Konversi = (Total Pesanan - Batal) / Total Pesanan * 100
            $totalOrders = $totalOrders ?? 0;
            $totalCancel = $totalCancel ?? 0;
            $validOrders = $totalOrders - $totalCancel;
            $konversiRate = $totalOrders > 0 ? ($validOrders / $totalOrders) * 100 : 0;
        @endphp

        <div class="row gx-4 mb-4 d-flex align-items-stretch">
            <div class="col-md-8 d-flex flex-column">
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-header bg-white py-3">
                        <h3 class="card-title text-bold m-0" style="font-size: 16px;">Kriteria Utama</h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="row text-center m-0 align-items-center" style="min-height: 100px;">
                            
                            <div class="col-md-3 py-3 border-right metric-card active-metric">
                                <div class="text-muted mb-1 d-flex justify-content-center align-items-center" style="font-size: 13px;">
                                    Penjualan <i class="far fa-question-circle ml-1 text-gray-400" title="Total produk yang berhasil terjual dan dibayar."></i>
                                </div>
                                <h4 class="font-weight-bold mb-1" style="font-size: 18px; color:#247a6b;">Rp {{ number_format($totalSales ?? 0, 0, ',', '.') }}</h4>
                                <div class="text-xs {{ ($trendSales ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                                    <i class="fas fa-arrow-{{ ($trendSales ?? 0) >= 0 ? 'up' : 'down' }}"></i> {{ abs($trendSales ?? 0) }}% <span class="text-muted ml-1">{{ $vsText ?? 'vs Kemarin' }}</span>
                                </div>
                            </div>
                            
                            <div class="col-md-3 py-3 border-right metric-card">
                                <div class="text-muted mb-1 d-flex justify-content-center align-items-center" style="font-size: 13px;">
                                    Pesanan <i class="far fa-question-circle ml-1 text-gray-400" title="Total semua pesanan masuk (termasuk yang dibatalkan)."></i>
                                </div>
                                <h4 class="font-weight-bold mb-1" style="font-size: 18px;">{{ $totalOrders }}</h4>
                                <div class="text-xs {{ ($trendOrders ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                                    <i class="fas fa-arrow-{{ ($trendOrders ?? 0) >= 0 ? 'up' : 'down' }}"></i> {{ abs($trendOrders ?? 0) }}% <span class="text-muted ml-1">{{ $vsText ?? 'vs Kemarin' }}</span>
                                </div>
                            </div>
                            
                            <div class="col-md-3 py-3 border-right metric-card">
                                <div class="text-muted mb-1" style="font-size: 13px;">Pesanan Dibatalkan</div>
                                <h4 class="font-weight-bold mb-1" style="font-size: 18px;">{{ $totalCancel }}</h4>
                                <div class="text-xs {{ ($trendCancel ?? 0) <= 0 ? 'text-success' : 'text-danger' }}">
                                    <i class="fas fa-arrow-{{ ($trendCancel ?? 0) > 0 ? 'up' : 'down' }}"></i> {{ abs($trendCancel ?? 0) }}% <span class="text-muted ml-1">{{ $vsText ?? 'vs Kemarin' }}</span>
                                </div>
                            </div>
                            
                            <div class="col-md-3 py-3 metric-card">
                                <div class="text-muted mb-1 d-flex justify-content-center align-items-center" style="font-size: 13px;">
                                    Tingkat Konversi <i class="far fa-question-circle ml-1 text-gray-400" title="(Pesanan - Batal) / Pesanan"></i>
                                </div>
                                <h4 class="font-weight-bold mb-1" style="font-size: 18px;">{{ number_format($konversiRate, 2) }}%</h4>
                                <div class="text-xs text-muted"><i class="fas fa-minus"></i> 0% <span class="text-muted ml-1">{{ $vsText ?? 'vs Kemarin' }}</span></div>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0 flex-fill mb-0">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h3 class="card-title text-bold m-0" style="font-size: 16px;">Grafik setiap Kriteria</h3>
                        <div class="text-xs">
                            <span style="color: #247a6b;"><i class="fas fa-circle"></i> Penjualan</span> &nbsp;
                            <span style="color: #ff6b00;"><i class="fas fa-circle"></i> Pesanan</span> &nbsp;
                            <span style="color: #dc3545;"><i class="fas fa-circle"></i> Batal</span> &nbsp;
                            <span style="color: #17a2b8;"><i class="fas fa-circle"></i> Konversi</span>
                        </div>
                    </div>
                    <div class="card-body d-flex flex-column justify-content-center">
                        <div style="height: 320px; width: 100%;"><canvas id="multiAxisChart"></canvas></div>
                    </div>
                </div>
            </div>

            <div class="col-md-4 d-flex flex-column">
                <div class="card shadow-sm border-0 flex-fill mb-0">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3 border-bottom-0">
                        <h3 class="card-title text-bold mb-0" style="font-size: 16px;">Metrik Real-time</h3>
                        <small class="text-[#247a6b] text-bold"><i class="fas fa-circle" style="font-size: 8px;"></i> Update hari ini</small>
                    </div>
                    <div class="card-body pt-0">
                        <div class="mb-4">
                            <label class="text-muted small d-block mb-1">Penjualan Hari Ini</label>
                            <h4 class="text-bold m-0 text-[#247a6b]" style="font-size: 24px;">Rp {{ number_format($todaySales ?? 0, 0, ',', '.') }}</h4>
                            <div class="mt-2"><canvas id="sparklineChart" style="height: 60px; width: 100%;"></canvas></div>
                        </div>
                        <div class="row pt-2" style="row-gap: 20px;">
                            <div class="col-6"><label class="text-muted small d-block mb-1">Total Pengunjung</label><h5 class="text-bold m-0">{{ $todayVisitors }}</h5></div>
                            <div class="col-6"><label class="text-muted small d-block mb-1">Produk Diklik</label><h5 class="text-bold m-0">{{ $todayClicks }}</h5></div>
                            <div class="col-6"><label class="text-muted small d-block mb-1">Pesanan</label><h5 class="text-bold m-0">{{ $todayOrders ?? 0 }}</h5></div>
                            <div class="col-6"><label class="text-muted small d-block mb-1">Tingkat Konversi</label><h5 class="text-bold m-0">{{ number_format($konversiRate, 2) }}%</h5></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-5 mt-2">
            <div class="card-header bg-white py-3 border-bottom">
                <h3 class="card-title text-bold m-0" style="font-size: 16px; color: #333;">Peringkat Penjualan</h3>
            </div>
            <div class="card-body p-0">
                <div class="d-flex justify-content-between align-items-center p-3" style="background-color: #fdfdfd; border-bottom: 1px solid #eee;">
                    <div class="d-flex align-items-center" style="gap: 15px;">
                        <span class="text-muted small">Peringkat Berdasarkan</span>
                        <select class="form-control form-control-sm" id="select_rank_by" style="width: 180px; cursor: pointer; border-color: #ddd;">
                            <option value="penjualan" {{ ($rankBy ?? 'penjualan') == 'penjualan' ? 'selected' : '' }}>Penjualan</option>
                            <option value="dilihat" {{ ($rankBy ?? '') == 'dilihat' ? 'selected' : '' }}>Etalase Dilihat</option>
                        </select>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-nowrap">
                        <thead class="bg-light text-muted" style="font-size: 13px;">
                            <tr>
                                <th class="text-center" style="width: 80px;">Peringkat</th>
                                <th style="width: 350px;">Informasi Produk</th>
                                <th class="text-center">Etalase Dilihat</th>
                                <th class="text-center">Terjual</th>
                                <th class="text-right pe-4">Estimasi Pendapatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                // Fungsi buat ngerubah angka ribuan jadi "k" (contoh: 3000 jadi 3k)
                                $formatRibuan = function($num) {
                                    if ($num >= 1000000) return rtrim(rtrim(number_format($num / 1000000, 1, '.', ''), '0'), '.') . 'm';
                                    elseif ($num >= 1000) return rtrim(rtrim(number_format($num / 1000, 1, '.', ''), '0'), '.') . 'k';
                                    return $num;
                                };
                            @endphp

                            @forelse($topProducts ?? [] as $index => $prod)
                                @php
                                $img = $prod->images->first();
                                $totalSold = $prod->period_sold ?? 0;
                                $totalViews = $prod->period_views ?? 0;
                                $estPrice = $prod->price ?? 0;
                                $estRevenue = $totalSold * $estPrice;
                                @endphp
                                <tr>
                                    <td class="text-center">
                                        @if($index == 0) <i class="fas fa-crown fa-2x" style="color: #FFD700;" title="Juara 1"></i> <br><small class="text-muted text-bold">1</small>
                                        @elseif($index == 1) <i class="fas fa-crown fa-2x" style="color: #C0C0C0;" title="Juara 2"></i> <br><small class="text-muted text-bold">2</small>
                                        @elseif($index == 2) <i class="fas fa-crown fa-2x" style="color: #CD7F32;" title="Juara 3"></i> <br><small class="text-muted text-bold">3</small>
                                        @else <span class="badge bg-secondary text-white px-2 py-1" style="font-size: 14px;">{{ $index + 1 }}</span> @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($img) <img src="{{ asset('storage/' . $img->image_path) }}" class="rounded mr-3 border" width="50" height="50" style="object-fit: cover;">
                                            @else <div class="bg-secondary mr-3" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; border-radius: 4px;"><i class="fas fa-leaf text-white"></i></div> @endif
                                            <div>
                                                <strong class="d-block text-dark text-wrap" style="width: 250px; font-size: 14px; line-height: 1.2;">{{ $prod->title }}</strong>
                                                <small class="text-muted">SKU: {{ $prod->sku ?? '-' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center font-weight-bold" style="font-size: 15px;">{{ $formatRibuan($totalViews) }}</td>
                                    <td class="text-center font-weight-bold text-[#247a6b]" style="font-size: 15px;">{{ $formatRibuan($totalSold) }}</td>
                                    <td class="text-right pe-4 font-weight-bold" style="font-size: 15px;">Rp {{ number_format($estRevenue, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="fas fa-box-open fa-3x mb-3 opacity-50"></i><p>Belum ada data produk yang terjual atau dilihat pada periode ini.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div style="height: 5px;"></div>
    </div>
</section>
@endsection

@section('customJs')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    $(document).ready(function() {
        // Kunci dropdown biar gak ketutup pas ngeklik dalemnya
        $('#dropdownMenuCalendar').on('click', function(e) { e.stopPropagation(); });
        $('#btn-batal-cal').on('click', function() { $('#periodeBtn').dropdown('toggle'); });

        // Data Sistem
        const sysDateObj = new Date();
        const sysYear = {{ (int)date('Y') }};
        const sysMonth = {{ (int)date('n') - 1 }}; 
        const sysDate = {{ (int)date('j') }};
        const namaBulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

        let currentTab = "{{ $periodeType ?? 'realtime' }}";
        let selectedValue = "{{ $periodeValue ?? '' }}";
        let viewYear = sysYear;
        let viewMonth = sysMonth;

        function padZero(num) { return num < 10 ? '0' + num : num; }

        // Set view awal berdasarkan nilai dari database biar gak kereload ke bulan ini
        if (selectedValue) {
            if (currentTab === 'per_hari' || currentTab === 'per_minggu') {
                let parts = selectedValue.split('-');
                if(parts.length === 3) { viewYear = parseInt(parts[0]); viewMonth = parseInt(parts[1]) - 1; }
            } else if (currentTab === 'per_bulan') {
                let parts = selectedValue.split('-');
                if(parts.length >= 1) { viewYear = parseInt(parts[0]); }
            } else if (currentTab === 'per_tahun') {
                viewYear = parseInt(selectedValue);
            }
        }

        function renderCalendar() {
            let bodyHtml = '';
            let titleHtml = '';

            // RENDER: PER HARI / PER MINGGU
            if (currentTab === 'per_hari' || currentTab === 'per_minggu') {
                $('#btn-prev-month, #btn-next-month').removeClass('d-none');
                titleHtml = `${namaBulan[viewMonth]} ${viewYear}`;
                
                let firstDay = new Date(viewYear, viewMonth, 1).getDay();
                let daysInMonth = new Date(viewYear, viewMonth + 1, 0).getDate();
                
                bodyHtml += `<div class="row text-center mb-2">
                                <div class="col px-1 cal-day-header text-danger">Min</div><div class="col px-1 cal-day-header">Sen</div>
                                <div class="col px-1 cal-day-header">Sel</div><div class="col px-1 cal-day-header">Rab</div>
                                <div class="col px-1 cal-day-header">Kam</div><div class="col px-1 cal-day-header">Jum</div><div class="col px-1 cal-day-header text-primary">Sab</div>
                             </div><div class="row text-center">`;
                
                for(let i = 0; i < firstDay; i++) { bodyHtml += `<div class="col px-1 mb-2"></div>`; }
                
                for(let d = 1; d <= daysInMonth; d++) {
                    let isFuture = (viewYear > sysYear) || (viewYear === sysYear && viewMonth > sysMonth) || (viewYear === sysYear && viewMonth === sysMonth && d > sysDate);
                    let disabledClass = isFuture ? 'disabled' : '';
                    let valStr = `${viewYear}-${padZero(viewMonth + 1)}-${padZero(d)}`;
                    let activeClass = (selectedValue === valStr) ? 'active' : '';

                    bodyHtml += `<div class="col px-1 mb-2"><div class="cal-item border ${disabledClass} ${activeClass} date-clicker" data-val="${valStr}">${d}</div></div>`;
                    if ((d + firstDay) % 7 === 0 && d !== daysInMonth) bodyHtml += `</div><div class="row text-center">`;
                }
                bodyHtml += `</div>`;

            // RENDER: PER BULAN
            } else if (currentTab === 'per_bulan') {
                $('#btn-prev-month, #btn-next-month').addClass('d-none');
                titleHtml = viewYear;

                bodyHtml += `<div class="row text-center mt-3">`;
                for(let i = 0; i < 12; i++) {
                    let isFuture = (viewYear > sysYear) || (viewYear === sysYear && i > sysMonth);
                    let disabledClass = isFuture ? 'disabled' : '';
                    let valStr = `${viewYear}-${padZero(i + 1)}`;
                    let activeClass = (selectedValue === valStr) ? 'active' : '';

                    bodyHtml += `<div class="col-4 p-2"><div class="cal-item py-3 border ${disabledClass} ${activeClass} date-clicker" data-val="${valStr}">${namaBulan[i]}</div></div>`;
                }
                bodyHtml += `</div>`;

            // RENDER: PER TAHUN
            } else if (currentTab === 'per_tahun') {
                $('#btn-prev-month, #btn-next-month').addClass('d-none');
                
                let startYear = viewYear - (viewYear % 10);
                let endYear = startYear + 9;
                titleHtml = `${startYear} &ndash; ${endYear}`;

                bodyHtml += `<div class="row text-center mt-3">`;
                for(let y = startYear; y <= endYear; y++) {
                    let isFuture = (y > sysYear);
                    let disabledClass = isFuture ? 'disabled' : '';
                    let activeClass = (selectedValue == y) ? 'active' : '';

                    bodyHtml += `<div class="col-4 p-2"><div class="cal-item py-3 border ${disabledClass} ${activeClass} date-clicker" data-val="${y}">${y}</div></div>`;
                }
                bodyHtml += `</div>`;
            }

            $('#cal-title').html(titleHtml);
            $('#cal-body').html(bodyHtml);
        }

        // FUNGSI GANTI TAB NAVIGASI
        function switchTab(tabName, isInitialLoad = false) {
            currentTab = tabName;
            $('.periode-nav').removeClass('active');
            $('.periode-nav[data-tab="' + tabName + '"]').addClass('active');

            let calHeader = $('#cal-header');
            let calBody = $('#cal-body');

            if (!isInitialLoad) {
                viewYear = sysYear;
                viewMonth = sysMonth;
            }

            if (tabName === 'realtime') {
                calHeader.addClass('d-none');
                if(!isInitialLoad) selectedValue = '';
                let tStr = padZero(sysDateObj.getHours()) + ':' + padZero(sysDateObj.getMinutes());
                calBody.html(`<div class="text-center pt-5 mt-4"><div class="text-muted small mb-2">Hari Ini</div><strong style="color: #247a6b; font-size: 24px;">Pk ${tStr}</strong></div>`);
            
            } else if (tabName === 'kemarin') {
                calHeader.addClass('d-none');
                if(!isInitialLoad) selectedValue = '';
                let yst = new Date(); yst.setDate(yst.getDate() - 1);
                let yStr = padZero(yst.getDate()) + '/' + padZero(yst.getMonth()+1) + '/' + yst.getFullYear();
                calBody.html(`<div class="text-center pt-5 mt-4"><div class="text-muted small mb-2">Tanggal Kemarin</div><strong style="color: #247a6b; font-size: 24px;">${yStr}</strong></div>`);
            
            } else {
                calHeader.removeClass('d-none');
                
                if (!isInitialLoad) {
                    if (tabName === 'per_hari' || tabName === 'per_minggu') {
                        selectedValue = `${sysYear}-${padZero(sysMonth+1)}-${padZero(sysDate)}`;
                    } else if (tabName === 'per_bulan') {
                        selectedValue = `${sysYear}-${padZero(sysMonth+1)}`;
                    } else if (tabName === 'per_tahun') {
                        selectedValue = `${sysYear}`;
                    }
                }

                renderCalendar();
            }
        }

        $('.periode-nav').on('click', function() { 
            switchTab($(this).data('tab'), false); 
        });

        $('#btn-prev-year').on('click', function() { if (currentTab === 'per_tahun') viewYear -= 10; else viewYear--; renderCalendar(); });
        $('#btn-next-year').on('click', function() { if (currentTab === 'per_tahun') viewYear += 10; else viewYear++; renderCalendar(); });
        $('#btn-prev-month').on('click', function() { viewMonth--; if(viewMonth < 0) { viewMonth = 11; viewYear--; } renderCalendar(); });
        $('#btn-next-month').on('click', function() { viewMonth++; if(viewMonth > 11) { viewMonth = 0; viewYear++; } renderCalendar(); });

        // 👇 INI DIA SOLUSI SAKTINYA BRE! Ganti $(document) jadi $('#cal-body') 👇
        $('#cal-body').on('click', '.date-clicker', function() {
            if ($(this).hasClass('disabled')) return; 
            
            $('#cal-body .date-clicker').removeClass('active'); // Bersihin warna ijo yg lama
            $(this).addClass('active'); // Kasih ijo ke yang baru di-klik
            selectedValue = $(this).attr('data-val'); // Simpen nilainya!
        });

        // Event pas klik Terapkan
        $('#btn-terapkan-cal').on('click', function() {
            $('#form_periode_type').val(currentTab);
            
            if (currentTab === 'realtime' || currentTab === 'kemarin') {
                $('#form_periode_value').val('');
            } else {
                if(!selectedValue) { 
                    alert('Pilih tanggal/periode dulu Bre!'); 
                    return; 
                }
                $('#form_periode_value').val(selectedValue);
            }
            
            $('#realFilterForm')[0].submit(); 

        });

        $('#select_rank_by').on('change', function() { 
            $('#form_rank_by').val($(this).val()); 
            $('#realFilterForm')[0].submit(); // Otomatis nge-reload data
        });

        // Run pertama kali saat halaman direload
        switchTab(currentTab, true);
    });

    // ==========================================
    // GRAFIK CHART.JS (Aman, ga perlu dirubah)
    // ==========================================
    const ctxMain = document.getElementById('multiAxisChart').getContext('2d');
    new Chart(ctxMain, {
        type: 'line',
        data: {
            labels: {!! json_encode($chartLabels ?? []) !!},
            datasets: [
                { label: 'Penjualan (Rp)', data: {!! json_encode($chartDataSales ?? []) !!}, borderColor: '#247a6b', backgroundColor: 'transparent', borderWidth: 2, pointRadius: 3, yAxisID: 'y', tension: 0.3 },
                { label: 'Pesanan', data: {!! json_encode($chartDataOrders ?? []) !!}, borderColor: '#ff6b00', backgroundColor: 'transparent', borderWidth: 2, pointRadius: 3, yAxisID: 'y1', tension: 0.3 },
                { label: 'Dibatalkan', data: {!! json_encode($chartDataCancel ?? []) !!}, borderColor: '#dc3545', backgroundColor: 'transparent', borderWidth: 2, pointRadius: 3, yAxisID: 'y1', tension: 0.3 },
                { label: 'Konversi (%)', data: {!! json_encode($chartDataConv ?? []) !!}, borderColor: '#17a2b8', backgroundColor: 'transparent', borderWidth: 2, borderDash: [5, 5], pointRadius: 3, yAxisID: 'y1', tension: 0.3 }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false } },
                y: { type: 'linear', display: true, position: 'left', title: { display: true, text: 'Penjualan (Rp)' }, grid: { borderDash: [5, 5] } },
                y1: { type: 'linear', display: true, position: 'right', title: { display: true, text: 'Pesanan / Batal / %' }, grid: { drawOnChartArea: false } }
            }
        }
    });

    const ctxSpark = document.getElementById('sparklineChart').getContext('2d');
    new Chart(ctxSpark, {
        type: 'line',
        data: {
            labels: ['00', '04', '08', '12', '16', '20', '24'],
            datasets: [{ data: [0, 0, 0, {{ $todaySales ?? 0 }}, 0, 0, 0], borderColor: '#247a6b', backgroundColor: 'rgba(36, 122, 107, 0.1)', fill: true, borderWidth: 2, pointRadius: 0, tension: 0.4 }]
        },
        options: {
            responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { enabled: false } },
            scales: { x: { display: true, grid: { display: false } }, y: { display: false, min: 0 } }, layout: { padding: 0 }
        }
    });
</script>
@endsection