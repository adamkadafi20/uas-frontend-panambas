@extends('admin.layouts.app')

@section('content')
<section class="content-header">
    <div class="container-fluid my-2">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Produk Saya</h1>
            </div>
            <div class="col-sm-6 text-right">
                <a href="{{ route('products.create') }}" class="btn text-white" style="background-color: #247a6b; border-color: #247a6b;">
                    <i class="fas fa-plus"></i> Tambah Produk Baru
                </a>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        @if(Session::has('success'))
        <div class="alert alert-success alert-dismissible fade show" style="background-color: #e9f2f0; border-color: #247a6b; color: #247a6b;">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <i class="icon fas fa-check"></i> {{ Session::get('success') }}
        </div>
        @endif

        <div class="card card-primary card-outline card-outline-tabs shadow-sm border-0">
            <div class="card-header p-0 border-bottom-0">
                <ul class="nav nav-tabs" id="productTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link {{ request('tab') == '' ? 'active font-weight-bold text-dark' : 'text-muted' }}" href="{{ route('products.index') }}" style="{{ request('tab') == '' ? 'border-top: 3px solid #247a6b;' : '' }}">Semua</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request('tab') == 'tersedia' ? 'active font-weight-bold text-dark' : 'text-muted' }}" href="{{ route('products.index', ['tab' => 'tersedia']) }}" style="{{ request('tab') == 'tersedia' ? 'border-top: 3px solid #247a6b;' : '' }}">Tersedia</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request('tab') == 'habis' ? 'active font-weight-bold text-dark' : 'text-muted' }}" href="{{ route('products.index', ['tab' => 'habis']) }}" style="{{ request('tab') == 'habis' ? 'border-top: 3px solid #247a6b;' : '' }}">Habis</a>
                    </li>
                </ul>
            </div>

            <div class="card-body">
                
                <form action="{{ route('products.index') }}" method="GET" class="row mb-3" id="filterForm">
                    <input type="hidden" name="tab" value="{{ request('tab') }}">
                    <input type="hidden" name="category" id="category_filter_input" value="{{ request('category') }}">

                    <div class="col-md-4 mb-2">
                        <div class="input-group shadow-sm" id="btn-open-filter-category" style="cursor: pointer; border-radius: 4px;">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light text-muted" style="border-right: 0;">Kategori</span>
                            </div>
                            @php
                                $catDisplay = 'Semua Kategori';
                                if(request('category')) {
                                    $parts = explode(' > ', request('category'));
                                    $catDisplay = count($parts) > 1 ? $parts[1] : $parts[0];
                                }
                            @endphp
                            <input type="text" class="form-control bg-white text-truncate font-weight-bold" value="{{ $catDisplay }}" readonly style="cursor: pointer; border-left: 0; border-right: 0; color: #247a6b;">
                            <div class="input-group-append">
                                <span class="input-group-text bg-white"><i class="fas fa-chevron-down text-muted" style="font-size: 12px;"></i></span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-5 mb-2">
                        <div class="input-group shadow-sm" style="border-radius: 4px;">
                            <input type="text" name="keyword" value="{{ request('keyword') }}" class="form-control border-right-0" placeholder="Cari Nama Produk / SKU">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-outline-secondary border bg-light text-muted"><i class="fas fa-search"></i></button>
                                @if(request('keyword') || request('category'))
                                    <a href="{{ route('products.index', ['tab' => request('tab')]) }}" class="btn btn-outline-danger border" title="Reset Filter"><i class="fas fa-times"></i></a>
                                @endif
                            </div>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover border text-nowrap">
                        <thead class="bg-light">
                            <tr>
                                <th width="300">Nama Produk</th>
                                <th>Kategori</th> 
                                <th>Harga</th>
                                <th>Stok</th>
                                <th>Performa</th> 
                                <th>Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($products->isNotEmpty())
                                
                                @php
                                    $formatRibuan = function($num) {
                                        if ($num >= 1000000) return rtrim(rtrim(number_format($num / 1000000, 1, '.', ''), '0'), '.') . 'm';
                                        elseif ($num >= 1000) return rtrim(rtrim(number_format($num / 1000, 1, '.', ''), '0'), '.') . 'k';
                                        return $num;
                                    };
                                @endphp

                                @foreach($products as $product)
                                
                                @php
                                    $hasVariations = $product->variations && $product->variations->count() > 0;
                                    $totalStock = $hasVariations ? $product->variations->sum('stock') : ($product->qty ?? 0);
                                    $totalSold = $hasVariations ? $product->variations->sum('sold') : ($product->sold ?? 0);
                                @endphp

                                <tr style="{{ $hasVariations ? 'background-color: #fafafa; border-bottom: 2px solid #eaeaea;' : '' }}">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @php $productImage = $product->images->first(); @endphp
                                            @if(!empty($productImage))
                                                <img src="{{ asset('storage/' . $productImage->image_path) }}" class="rounded mr-3 border" width="45" height="45" style="object-fit: cover;">
                                            @else
                                                <div class="bg-secondary mr-3" style="width: 45px; height: 45px; display: flex; align-items: center; justify-content: center; border-radius: 4px;">
                                                    <i class="fas fa-leaf text-white"></i>
                                                </div>
                                            @endif

                                            <div>
                                                <strong class="d-block text-wrap" style="width: 200px; color: #247a6b;">{{ $product->title }}</strong>
                                                <small class="text-muted">SKU: {{ $product->sku ?? '-' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <td class="align-middle">
                                        <small class="text-muted d-block">{{ $product->category_id ?? 'Tanpa Kategori' }}</small>
                                        @if($product->size)
                                        <small class="text-muted">Ukuran: {{ ucfirst($product->size) }}</small>
                                        @endif
                                    </td>
                                    
                                    <td class="align-middle">
                                        @if($hasVariations)
                                            <span class="font-weight-bold">Rp{{ number_format($product->variations->min('price'), 0, ',', '.') }} - Rp{{ number_format($product->variations->max('price'), 0, ',', '.') }}</span>
                                        @else
                                            <span class="font-weight-bold">Rp{{ number_format($product->price, 0, ',', '.') }}</span>
                                        @endif
                                        <a href="javascript:void(0);" onclick="openQuickEdit('price', {{ $product->id }})" class="ml-2" style="font-size: 13px; color: #247a6b;" title="Atur Harga"><i class="fas fa-pencil-alt"></i></a>
                                    </td>
                                    
                                    <td class="align-middle">
                                        <span class="text-{{ ($totalStock <= 5 && $totalStock > 0) ? 'warning' : ($totalStock <= 0 ? 'danger' : 'dark') }} font-weight-bold">
                                            {{ $totalStock }}
                                        </span>
                                        <a href="javascript:void(0);" onclick="openQuickEdit('stock', {{ $product->id }})" class="ml-2" style="font-size: 13px; color: #247a6b;" title="Atur Stok"><i class="fas fa-pencil-alt"></i></a>
                                    </td>
                                    
                                    <td class="align-middle">
                                        <div class="text-sm">
                                            <div class="d-flex justify-content-between">
                                                <span class="text-muted">Dilihat:</span>
                                                <span class="ml-2 font-weight-bold" style="color: #666;">{{ $formatRibuan($product->views ?? 0) }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between mt-1">
                                                <span class="text-muted">Terjual:</span>
                                                <span class="ml-2 font-weight-bold" style="color: #247a6b;">{{ $formatRibuan($totalSold) }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <td class="align-middle">
                                        @if($totalStock > 0)
                                            <span class="badge px-2 py-1" style="background-color: #e9f2f0; color: #247a6b; border: 1px solid #247a6b;">Live</span>
                                        @else
                                            <span class="badge badge-danger px-2 py-1">Habis</span>
                                        @endif
                                    </td>
                                    
                                    <td class="align-middle text-left pl-4">
                                        <a href="{{ route('products.edit', $product->id) }}" class="text-decoration-none font-weight-bold d-block mb-2" style="color: #247a6b; font-size: 14px;">Ubah</a>
                                        <a href="{{ route('front.product', $product->id) }}" target="_blank" class="text-decoration-none font-weight-bold d-block mb-2" style="color: #247a6b; font-size: 14px;">Tampilan Produk</a>
                                        <a href="javascript:void(0);" onclick="deleteProduct({{ $product->id }})" class="text-decoration-none font-weight-bold text-danger d-block" style="font-size: 14px;">Hapus</a>
                                        <form id="delete-product-form-{{ $product->id }}" action="{{ route('products.destroy', $product->id) }}" method="POST" class="d-none">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </td>
                                </tr>

                                @if($hasVariations)
                                    @php
                                        $variationsToShow = $product->variations;
                                        if (request('tab') == 'habis') $variationsToShow = $product->variations->where('stock', '<=', 0);
                                        elseif (request('tab') == 'tersedia') $variationsToShow = $product->variations->where('stock', '>', 0);

                                        $varCount = $variationsToShow->count();
                                        $hiddenCount = $varCount - 3;
                                    @endphp

                                    @foreach($variationsToShow as $var)
                                        <tr class="var-row-{{ $product->id }} {{ $loop->iteration > 3 ? 'd-none hidden-var-'.$product->id : '' }}" style="border-bottom: 1px solid #f4f6f9; background-color: #fff;">
                                            <td>
                                                <div class="d-flex align-items-center" style="padding-left: 30px;">
                                                    <span style="width: 8px; height: 8px; background-color: {{ $var->stock <= 0 ? '#dc3545' : '#247a6b' }}; border-radius: 50%; display: inline-block; margin-right: 15px;"></span>
                                                    @if($var->image_path)
                                                        <img src="{{ asset('storage/' . $var->image_path) }}" class="rounded mr-3 border" width="40" height="40" style="object-fit: cover;">
                                                    @endif
                                                    <div>
                                                        <strong class="d-block text-dark text-wrap" style="width: 200px; font-size: 14px;">{{ $var->variation_option }}</strong>
                                                        <small class="text-muted">Kode: {{ $var->sku ?? '-' }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="align-middle border-0"><small class="text-muted">-</small></td>
                                            <td class="align-middle border-0">
                                                <span class="font-weight-bold" style="font-size: 14px;">Rp{{ number_format($var->price, 0, ',', '.') }}</span>
                                            </td>
                                            <td class="align-middle border-0">
                                                <span class="text-{{ ($var->stock <= 5 && $var->stock > 0) ? 'warning' : ($var->stock <= 0 ? 'danger' : 'dark') }} font-weight-bold" style="font-size: 14px;">
                                                    {{ $var->stock <= 0 ? 'Habis' : $var->stock }}
                                                </span>
                                            </td>
                                            <td class="align-middle border-0">
                                                <span class="text-muted font-weight-bold" style="font-size: 13px;">Penjualan {{ $formatRibuan($var->sold ?? 0) }}</span>
                                            </td>
                                            <td class="align-middle border-0"></td>
                                            <td class="align-middle border-0"></td>
                                        </tr>
                                    @endforeach

                                    @if($varCount > 3)
                                        <tr class="toggle-row-{{ $product->id }}" style="background-color: #fdfdfd;">
                                            <td colspan="7" class="text-center py-2" style="border-bottom: 1px solid #eaeaea;">
                                                <div style="display: flex; align-items: center; justify-content: center; width: 100%;">
                                                    <hr style="flex-grow: 1; border-top: 1px dashed #ddd; margin: 0 15px;">
                                                    <a href="javascript:void(0);" onclick="toggleVariations({{ $product->id }}, {{ $hiddenCount }})" id="toggle-btn-{{ $product->id }}" class="text-muted text-decoration-none" style="font-size: 13px;">
                                                        Lihat Semua ({{ $hiddenCount }} SKU Produk) <i class="fas fa-chevron-down ml-1"></i>
                                                    </a>
                                                    <hr style="flex-grow: 1; border-top: 1px dashed #ddd; margin: 0 15px;">
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                @endif
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="fas fa-box-open fa-3x mb-3 opacity-50"></i>
                                            <p>Belum ada produk yang ditemukan.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="card-footer bg-white clearfix">
                {{ $products->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="categoryFilterModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title font-weight-bold" style="color: #333;"><i class="fas fa-filter mr-2" style="color: #247a6b;"></i> Filter Kategori</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <div class="row m-0" style="height: 350px;">
                    <div class="col-6 p-0 border-right" style="overflow-y: auto;">
                        <ul class="list-group list-group-flush" id="filter-main-category-list"></ul>
                    </div>
                    <div class="col-6 p-0" style="overflow-y: auto; background-color: #fafafa;">
                        <ul class="list-group list-group-flush" id="filter-sub-category-list">
                            <li class="list-group-item text-muted text-center pt-5" style="background: transparent; border: none;">Pilih kategori utama dahulu</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top-0 pt-0">
                <button type="button" class="btn text-white px-4 d-none" style="background-color: #247a6b; font-weight: bold;" id="btn-confirm-filter-category">Terapkan Filter</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="quickEditModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title font-weight-bold" id="quickEditTitle">Atur Harga</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="quickEditForm">
                <div class="modal-body">
                    <p id="quickEditProductTitle" class="text-muted mb-4" style="font-size: 14px;"></p>
                    
                    <div class="d-flex align-items-center mb-4 pb-3 border-bottom" style="gap: 15px;">
                        <strong style="font-size: 14px; white-space: nowrap;">Ubah Massal</strong>
                        <div class="input-group input-group-sm flex-fill">
                            <div class="input-group-prepend" id="massal-prepend"><span class="input-group-text bg-white">Rp</span></div>
                            <input type="number" id="massal-input" class="form-control border-left-0" placeholder="Masukkan angka">
                        </div>
                        <button type="button" id="btn-apply-massal" class="btn btn-sm text-white px-3" style="background-color: #247a6b; border-color: #247a6b;">Terapkan Ke Semua</button>
                    </div>

                    <div id="quick-edit-list" style="max-height: 250px; overflow-y: auto; overflow-x: hidden;"></div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light border" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success" style="background-color: #247a6b; border-color: #247a6b;" id="btn-save-quick-edit">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div style="height: 2px;"></div>
@endsection

@section('customJs')
<script>
    const dataKategoriFilter = {
        "Semua Kategori": [], 
        "Bibit & Benih": ["Sayuran", "Buah-buahan", "Herbal & Rempah", "Biji Bunga", "Pohon Kayu"],
        "Tanaman Hias": ["Tanaman Daun", "Tanaman Bunga", "Kaktus & Sukulen", "Tanaman Gantung", "Bonsai"],
        "Media Tanam & Pupuk": ["Tanah & Campuran", "Sekam & Cocopeat", "Pupuk Organik / Kompos", "Pupuk Kimia / NPK", "Vitamin & Hormon Tanaman"],
        "Peralatan Berkebun": ["Pot & Wadah", "Alat Tanam & Potong", "Penyiram Air", "Pembasmi Hama / Pestisida"]
    };

    let productsData = {!! $products->getCollection()->load('variations')->keyBy('id')->toJson() !!};
    let currentEditType = '';
    let currentEditId = null;

    $(document).ready(function() {
        $('#btn-open-filter-category').click(function() {
            $('#categoryFilterModal').modal('show');
            let html = '';
            
            for(let main in dataKategoriFilter) {
                if (main === "Semua Kategori") {
                    html += `<li class="list-group-item list-group-item-action filter-main-cat-item font-weight-bold text-danger" data-main="" style="cursor:pointer; border-left: 3px solid transparent; background: #fff5f5;">${main} <i class="fas fa-undo float-right mt-1"></i></li>`;
                } else {
                    html += `<li class="list-group-item list-group-item-action filter-main-cat-item" data-main="${main}" style="cursor:pointer; border-left: 3px solid transparent;">${main} <i class="fas fa-chevron-right float-right text-muted mt-1" style="font-size:12px;"></i></li>`;
                }
            }
            
            $('#filter-main-category-list').html(html);
            $('#filter-sub-category-list').html('<li class="list-group-item text-muted text-center pt-5" style="background: transparent; border: none;">Pilih kategori utama dahulu</li>');
            $('#btn-confirm-filter-category').addClass('d-none');
        });

        $(document).on('click', '.filter-main-cat-item', function() {
            $('.filter-main-cat-item').css('border-left', '3px solid transparent').removeClass('bg-light font-weight-bold').css('color', '#555');
            let tempSelectedMain = $(this).data('main');
            
            if (tempSelectedMain === '') {
                $(this).css('border-left', '3px solid #dc3545').addClass('bg-light font-weight-bold').css('color', '#dc3545');
                $('#filter-sub-category-list').html('<li class="list-group-item text-center pt-5 text-muted" style="background: transparent; border: none;"><i class="fas fa-layer-group fa-2x mb-3 opacity-50"></i><br>Menampilkan semua kategori</li>');
                window.tempFilterMainStr = '';
                window.tempFilterSubStr = '';
                $('#btn-confirm-filter-category').removeClass('d-none');
                return;
            }

            $(this).css('border-left', '3px solid #247a6b').addClass('bg-light font-weight-bold').css('color', '#247a6b');
            let subs = dataKategoriFilter[tempSelectedMain];
            
            let html = `<li class="list-group-item list-group-item-action filter-sub-cat-item font-weight-bold" data-sub="Semua" style="cursor:pointer; background: transparent; color: #247a6b;">Semua dalam ${tempSelectedMain}</li>`;
            subs.forEach(sub => {
                html += `<li class="list-group-item list-group-item-action filter-sub-cat-item" data-sub="${sub}" style="cursor:pointer; background: transparent;">${sub}</li>`;
            });
            
            $('#filter-sub-category-list').html(html);
            $('#btn-confirm-filter-category').addClass('d-none');
            window.tempFilterMainStr = tempSelectedMain;
        });

        $(document).on('click', '.filter-sub-cat-item', function() {
            $('.filter-sub-cat-item').removeClass('font-weight-bold').css('color', '#555');
            $(this).addClass('font-weight-bold').css('color', '#247a6b');
            
            let sub = $(this).data('sub');
            window.tempFilterSubStr = sub === 'Semua' ? '' : sub;
            $('#btn-confirm-filter-category').removeClass('d-none');
        });

        $('#btn-confirm-filter-category').click(function() {
            let fullCat = '';
            if (window.tempFilterMainStr !== '') {
                fullCat = window.tempFilterMainStr;
                if (window.tempFilterSubStr !== '') {
                    fullCat += ' > ' + window.tempFilterSubStr;
                }
            }
            $('#category_filter_input').val(fullCat);
            $('#categoryFilterModal').modal('hide');
            $('#filterForm').submit(); 
        });

        setTimeout(function() {
            $(".alert-success").fadeOut("slow", function() { $(this).remove(); });
        }, 3000); 
    });

    function deleteProduct(id) {
        if (confirm("Yakin ingin menghapus produk ini? Datanya nggak bisa balik lagi lho.")) {
            document.getElementById('delete-product-form-' + id).submit();
        }
    }

    function toggleVariations(productId, hiddenCount) {
        let hiddenRows = $('.hidden-var-' + productId);
        let btn = $('#toggle-btn-' + productId);
        if (hiddenRows.hasClass('d-none')) {
            hiddenRows.removeClass('d-none').hide().fadeIn(300);
            btn.html('Tutup <i class="fas fa-chevron-up ml-1"></i>');
        } else {
            hiddenRows.fadeOut(300, function() { $(this).addClass('d-none'); });
            btn.html('Lihat Semua (' + hiddenCount + ' SKU Produk) <i class="fas fa-chevron-down ml-1"></i>');
        }
    }

    function openQuickEdit(type, productId) {
        currentEditType = type;
        currentEditId = productId;
        let p = productsData[productId];

        $('#quickEditTitle').text(type === 'price' ? 'Atur Harga' : 'Atur Stok');
        $('#quickEditProductTitle').text(p.title);
        $('#massal-input').val(''); 

        if (type === 'price') {
            $('#massal-prepend').show();
            $('#massal-input').attr('placeholder', 'Harga');
            $('#btn-save-quick-edit').text('Update Harga');
        } else {
            $('#massal-prepend').hide();
            $('#massal-input').attr('placeholder', 'Stok');
            $('#massal-input').addClass('border-left');
            $('#btn-save-quick-edit').text('Update Stok');
        }

        let html = '';
        if (p.variations && p.variations.length > 0) {
            html += `<div class="row text-muted mb-2" style="font-size: 13px;"><div class="col-6">Variasi</div><div class="col-6">${type === 'price' ? 'Harga' : 'Total Stok'}</div></div>`;
            p.variations.forEach(v => {
                html += `
                <div class="row align-items-center mb-3 pb-2 border-bottom">
                    <div class="col-6">
                        <strong class="d-block" style="font-size: 14px;">${v.variation_option}</strong>
                        <small class="text-muted" style="font-size: 12px;">SKU: ${v.sku || '-'}</small>
                    </div>
                    <div class="col-6">
                        <div class="input-group input-group-sm">
                            ${type === 'price' ? '<div class="input-group-prepend"><span class="input-group-text bg-white">Rp</span></div>' : ''}
                            <input type="number" name="variations[${v.id}]" class="form-control var-quick-input ${type === 'stock' ? 'border-left' : 'border-left-0'}" value="${type === 'price' ? v.price : v.stock}" required>
                        </div>
                    </div>
                </div>`;
            });
        } else {
            html += `
            <div class="row text-muted mb-2" style="font-size: 13px;"><div class="col-6">Produk</div><div class="col-6">${type === 'price' ? 'Harga' : 'Total Stok'}</div></div>
            <div class="row align-items-center mb-3">
                <div class="col-6"><strong style="font-size: 14px;">Produk Utama</strong></div>
                <div class="col-6">
                    <div class="input-group input-group-sm">
                        ${type === 'price' ? '<div class="input-group-prepend"><span class="input-group-text bg-white">Rp</span></div>' : ''}
                        <input type="number" name="main_val" class="form-control var-quick-input ${type === 'stock' ? 'border-left' : 'border-left-0'}" value="${type === 'price' ? p.price : p.qty}" required>
                    </div>
                </div>
            </div>`;
        }

        $('#quick-edit-list').html(html);
        $('#quickEditModal').modal('show');
    }

    $('#btn-apply-massal').click(function() {
        let val = $('#massal-input').val();
        if(val !== '') $('.var-quick-input').val(val);
    });

    $('#quickEditForm').submit(function(e) {
        e.preventDefault();
        let formData = $(this).serialize() + '&type=' + currentEditType;
        let btnSubmit = $('#btn-save-quick-edit');
        btnSubmit.prop('disabled', true).text('Menyimpan...');

        $.ajax({
            url: '/admin/products/' + currentEditId + '/quick-update',
            type: 'POST',
            data: formData,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(response) {
                if(response.status) {
                    $('#quickEditModal').modal('hide');
                    location.reload();
                } else {
                    alert(response.message);
                    btnSubmit.prop('disabled', false).text('Update');
                }
            },
            error: function() {
                alert('Terjadi kesalahan, coba lagi!');
                btnSubmit.prop('disabled', false).text('Update');
            }
        });
    });
</script>
@endsection