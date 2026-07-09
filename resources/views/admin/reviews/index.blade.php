@extends('admin.layouts.app')

@section('content')
<section class="content-header">
    <div class="container-fluid my-2">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Penilaian Produk</h1>
            </div>
        </div>
    </div>
</section>

<section class="content pb-4">
    <div class="container-fluid">
        
        @if(Session::has('success'))
        <div class="alert alert-success alert-dismissible fade show" style="background-color: #e9f2f0; border-color: #247a6b; color: #247a6b;">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <i class="icon fas fa-check"></i> {{ Session::get('success') }}
        </div>
        @endif

        <!-- RINGKASAN PENILAIAN -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card shadow-sm border-0 h-100" style="border-radius: 8px;">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center py-4">
                        <h6 class="text-muted mb-2">Penilaian Toko</h6>
                        <div class="d-flex align-items-baseline">
                            <h2 class="font-weight-bold mb-0 text-[#247a6b]" style="color: #247a6b; font-size: 2.5rem;">{{ $avgRating }}</h2>
                            <span class="text-muted ml-1" style="font-size: 1.2rem;">/ 5</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card shadow-sm border-0 h-100" style="border-radius: 8px;">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center py-4">
                        <h6 class="text-muted mb-2">Total Ulasan Diterima</h6>
                        <h2 class="font-weight-bold mb-0 text-dark" style="font-size: 2.5rem;">{{ $totalReviews }}</h2>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm border-0 h-100" style="border-radius: 8px;">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center py-4">
                        <h6 class="text-muted mb-2">Perlu Dibalas</h6>
                        <h2 class="font-weight-bold mb-0 text-danger" style="font-size: 2.5rem;">{{ $needReplyCount }}</h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- AREA FILTER & DAFTAR ULASAN -->
        <div class="card shadow-sm border-0" style="border-radius: 8px;">
            
            <!-- Filter Tabs -->
            <div class="card-header bg-white p-0 border-bottom">
                <ul class="nav nav-tabs border-0 px-3 pt-3" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link {{ request('tab') == '' ? 'active font-weight-bold text-dark' : 'text-muted' }}" href="{{ route('reviews.index', ['stars' => request('stars')]) }}" style="{{ request('tab') == '' ? 'border-top: 3px solid #247a6b; border-bottom: 0;' : '' }}">Semua ({{ $totalReviews }})</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request('tab') == 'perlu_dibalas' ? 'active font-weight-bold text-dark' : 'text-muted' }}" href="{{ route('reviews.index', ['tab' => 'perlu_dibalas', 'stars' => request('stars')]) }}" style="{{ request('tab') == 'perlu_dibalas' ? 'border-top: 3px solid #247a6b; border-bottom: 0;' : '' }}">Perlu Dibalas ({{ $needReplyCount }})</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request('tab') == 'sudah_dibalas' ? 'active font-weight-bold text-dark' : 'text-muted' }}" href="{{ route('reviews.index', ['tab' => 'sudah_dibalas', 'stars' => request('stars')]) }}" style="{{ request('tab') == 'sudah_dibalas' ? 'border-top: 3px solid #247a6b; border-bottom: 0;' : '' }}">Sudah Dibalas ({{ $repliedCount }})</a>
                    </li>
                </ul>
            </div>

            <div class="card-body bg-light pt-3 pb-4">
                
                <!-- Form Filter Checkbox & Pencarian -->
                <form action="{{ route('reviews.index') }}" method="GET" id="filterReviewForm">
                    <input type="hidden" name="tab" value="{{ request('tab') }}">
                    
                    <div class="d-flex flex-wrap align-items-center mb-4 p-3 bg-white border rounded">
                        <div class="mr-4 mb-2 mb-md-0 d-flex align-items-center">
                            <span class="text-muted mr-3" style="font-size: 14px;">Filter Bintang:</span>
                            
                            @php $reqStars = request('stars', []); @endphp
                            
                            <div class="custom-control custom-checkbox custom-control-inline">
                                <input type="checkbox" class="custom-control-input filter-star" id="starAll" {{ empty($reqStars) ? 'checked' : '' }}>
                                <label class="custom-control-label font-weight-normal text-muted" style="cursor:pointer;" for="starAll">Semua</label>
                            </div>
                            
                            @for($i=5; $i>=1; $i--)
                            <div class="custom-control custom-checkbox custom-control-inline">
                                <input type="checkbox" name="stars[]" value="{{ $i }}" class="custom-control-input filter-star star-item" id="star{{ $i }}" {{ in_array($i, $reqStars) ? 'checked' : '' }}>
                                <label class="custom-control-label font-weight-normal text-muted" style="cursor:pointer;" for="star{{ $i }}">{{ $i }} <i class="fas fa-star text-warning"></i></label>
                            </div>
                            @endfor
                        </div>

                        <div class="flex-grow-1">
                            <div class="input-group input-group-sm">
                                <input type="text" name="keyword" value="{{ request('keyword') }}" class="form-control" placeholder="Cari Nama Produk / Username">
                                <div class="input-group-append">
                                    <button class="btn text-white px-3" type="submit" style="background-color: #247a6b;">Cari</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Header Tabel Virtual -->
                <div class="row text-muted text-center mb-2 px-3" style="font-size: 13px; font-weight: 500;">
                    <div class="col-md-3 text-left">Informasi Produk</div>
                    <div class="col-md-7 text-left">Penilaian Pembeli</div>
                    <div class="col-md-2">Tindakan</div>
                </div>

                <!-- DAFTAR ULASAN DARI DATABASE -->
                @forelse($reviews as $rev)
                <div class="card shadow-none border mb-3">
                    <div class="card-header bg-white py-2 border-bottom d-flex align-items-center">
                        <i class="fas fa-user-circle text-muted fa-lg mr-2"></i>
                        <span class="font-weight-bold text-dark mr-3" style="font-size: 14px;">{{ $rev->username ?? 'Pembeli Anonim' }}</span>
                        <span class="text-muted" style="font-size: 12px;"><i class="fas fa-envelope mr-1"></i> {{ $rev->email }}</span>
                    </div>
                    <div class="card-body p-3">
                        <div class="row">
                            
                            <!-- Kiri: Produk -->
                            <div class="col-md-3 border-right">
                                <div class="d-flex">
                                    @if($rev->product && $rev->product->images->isNotEmpty())
                                        <img src="{{ asset('storage/' . $rev->product->images->first()->image_path) }}" class="rounded border mr-2" width="50" height="50" style="object-fit: cover;">
                                    @else
                                        <div class="bg-light rounded border mr-2 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; flex-shrink: 0;">
                                            <i class="fas fa-seedling text-muted"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <a href="{{ route('front.product', $rev->product_id) }}" target="_blank" class="text-dark font-weight-bold d-block text-wrap" style="font-size: 13px; line-height: 1.2;">{{ $rev->product->title ?? 'Produk Telah Dihapus' }}</a>
                                    </div>
                                </div>
                            </div>

                            <!-- Tengah: Bintang & Komentar -->
                            <div class="col-md-7 px-4 border-right">
                                <div class="text-warning mb-1" style="font-size: 12px;">
                                    @for($i=1; $i<=5; $i++)
                                        @if($i <= $rev->rating) <i class="fas fa-star"></i> @else <i class="far fa-star"></i> @endif
                                    @endfor
                                </div>
                                <div class="text-muted mb-2" style="font-size: 11px;">{{ $rev->created_at->format('d/m/Y H:i') }}</div>
                                <p class="text-dark mb-3" style="font-size: 14px; line-height: 1.4;">{{ $rev->comment }}</p>
                                
                                <!-- Respons Penjual -->
                                @if($rev->reply)
                                <div class="p-3 rounded" style="background-color: #f7fbfa; border-left: 3px solid #247a6b;">
                                    <strong class="d-block text-dark mb-1" style="font-size: 13px;">Respons Penjual:</strong>
                                    <p class="text-muted m-0" style="font-size: 13px;">{{ $rev->reply }}</p>
                                </div>
                                @endif
                            </div>

                            <!-- Kanan: Aksi (Tombol Balas) -->
                            <div class="col-md-2 d-flex align-items-center justify-content-center">
                                @if($rev->reply)
                                <button class="btn btn-outline-secondary btn-sm px-4" disabled>Selesai</button>
                                @else
                                <button class="btn btn-sm text-white font-weight-bold px-4" style="background-color: #247a6b; border-color: #247a6b;" data-toggle="modal" data-target="#modalReply{{ $rev->id }}">Balas</button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Pop-Up Balas Ulasan -->
                <div class="modal fade" id="modalReply{{ $rev->id }}" tabindex="-1" role="dialog">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <form action="{{ route('reviews.reply', $rev->id) }}" method="POST">
                                @csrf
                                <div class="modal-header bg-light">
                                    <h5 class="modal-title font-weight-bold">Balas Ulasan</h5>
                                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                </div>
                                <div class="modal-body">
                                    <div class="p-3 mb-3 bg-light rounded border">
                                        <strong class="d-block text-dark" style="font-size: 13px;">{{ $rev->username }} berkata:</strong>
                                        <p class="text-muted m-0" style="font-size: 13px;">"{{ $rev->comment }}"</p>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label>Respons Anda</label>
                                        <textarea name="reply" class="form-control" rows="4" placeholder="Ketik balasan untuk pembeli di sini..." required></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer border-top-0 pt-0">
                                    <button type="button" class="btn btn-light border" data-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn text-white" style="background-color: #247a6b;">Kirim Balasan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @empty
                    <div class="text-center py-5 bg-white border rounded">
                        <i class="fas fa-star-half-alt fa-3x mb-3 text-muted opacity-50"></i>
                        <p class="text-muted">Belum ada data ulasan yang sesuai.</p>
                    </div>
                @endforelse

                <!-- Pagination Bawah -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $reviews->appends(request()->query())->links() }}
                </div>

            </div>
        </div>
    </div>
</section>

<!-- CSS Warna Checkbox -->
<style>
    .custom-control-input:checked ~ .custom-control-label::before {
        border-color: #247a6b;
        background-color: #247a6b;
    }
</style>
@endsection

@section('customJs')
<script>
    $(document).ready(function() {
        // Otomatis ilangin notifikasi success setelah 3 detik
        setTimeout(function() {
            $(".alert-success").fadeOut("slow");
        }, 3000);

        // LOGIKA CHECKBOX BINTANG
        $('#starAll').change(function() {
            if($(this).is(':checked')) {
                // Kalau klik Semua, bintang 1-5 di-uncheck
                $('.star-item').prop('checked', false);
                $('#filterReviewForm').submit();
            }
        });

        $('.star-item').change(function() {
            // Kalau ada bintang 1-5 yang diklik, "Semua" di-uncheck
            $('#starAll').prop('checked', false);
            
            // Kalau bintang dicentang sampai habis, centang lagi "Semua"
            if ($('.star-item:checked').length == 0) {
                $('#starAll').prop('checked', true);
            }
            
            // Otomatis Submit Form pas checkbox diklik
            $('#filterReviewForm').submit();
        });
    });
</script>
@endsection