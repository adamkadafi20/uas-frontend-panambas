@extends('admin.layouts.app')

@section('content')
<section class="content-header">
    <div class="container-fluid my-3">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="font-weight-bold" style="color: #333;">Edit Produk</h1>
                <p class="text-muted mt-1" style="font-size: 15px;">Edit data produk tanaman yang sudah ada.</p>
            </div>
            <div class="col-sm-6 text-right">
                <a href="{{ route('products.index') }}" class="btn btn-light px-4 border shadow-sm font-weight-bold" style="color: #555; border-radius: 6px;">
                    Kembali
                </a>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <form action="{{ route('products.update', $product->id) }}" method="post" id="productForm" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="container-fluid">
            
            <div id="step-1" class="card mb-4 border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0 px-4">
                    <h5 class="mb-0" style="color: #3f7a08; font-weight: 700;"><i class="fas fa-box-open mr-2"></i>Informasi Dasar</h5>
                </div>
                <div class="card-body p-4">
                    
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <label class="font-weight-bold"><span class="text-danger">*</span> Foto Produk</label>
                            <div class="mt-1">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="ratio" id="ratio1" value="1:1" checked>
                                    <label class="form-check-label text-muted" for="ratio1">Rasio 1:1</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-9">
                            <div class="d-flex flex-wrap" id="image-list-container" style="gap: 12px;">
                                
                                @if(isset($productImages))
                                    @foreach($productImages as $img)
                                        <div class="preview-item existing-img shadow-sm" data-id="{{ $img->id }}" data-type="existing" style="width: 110px; height: 110px; position: relative; border: 1px solid #ddd; overflow: hidden; border-radius: 8px; cursor: grab; flex-shrink: 0;">
                                            <img src="{{ asset('storage/' . $img->image_path) }}" style="width: 100%; height: 100%; object-fit: cover;">
                                            <div class="delete-overlay" onclick="removeExistingImage(this, {{ $img->id }})" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: none; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s;">
                                                <i class="fas fa-trash text-white fa-lg"></i>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif

                                <div id="image-wrapper" class="d-flex align-items-center justify-content-center text-center" style="width: 110px; height: 110px; border: 2px dashed #a3c48c; background-color: #f9fdf6; cursor: pointer; flex-shrink: 0; border-radius: 8px; transition: all 0.3s;">
                                    <div style="color: #3f7a08;">
                                        <i class="fas fa-cloud-upload-alt fa-2x mb-1"></i><br>
                                        <small class="font-weight-bold">Tambah (<span id="count-img">0</span>/9)</small>
                                    </div>
                                </div>
                            </div>
                            <input type="file" id="file-input" multiple accept="image/*" style="display: none;">
                            <small class="text-muted mt-2 d-block"><i class="fas fa-info-circle mr-1"></i> Geser (drag) gambar untuk mengatur urutan. Gambar pertama otomatis menjadi Foto Promosi.</small>

                            <input type="hidden" name="deleted_images" id="deleted_images" value="">
                            <input type="hidden" name="image_order" id="image_order" value="">
                        </div>
                    </div>

                    <div class="row mb-4 align-items-center">
                        <div class="col-md-3">
                            <label class="font-weight-bold"><span class="text-danger">*</span> Foto Promosi</label>
                        </div>
                        <div class="col-md-9 d-flex align-items-center">
                            <div id="promo-box" class="d-flex align-items-center justify-content-center text-center shadow-sm" 
                                 style="width: 90px; height: 90px; border: 2px dashed #a3c48c; background-color: #f9fdf6; border-radius: 8px; overflow: hidden; flex-shrink: 0;">
                                <img id="promo-display" src="" style="width: 100%; height: 100%; object-fit: cover; display: none;">
                                <div id="promo-text" style="color: #3f7a08;">
                                    <i class="fas fa-star fa-lg"></i><br>
                                    <small style="font-size: 11px; font-weight: bold;">Promosi</small>
                                </div>
                            </div>
                            <div class="small text-muted ml-3">
                                <ul class="pl-3 mb-0" style="line-height: 1.6;">
                                    <li>Otomatis diambil dari urutan foto pertama.</li>
                                    <li>Tampil paling depan di halaman etalase pembeli.</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-3">
                            <label class="font-weight-bold">Video Produk</label>
                        </div>
                        <div class="col-md-9 d-flex align-items-start">
                            
                            <input type="hidden" name="remove_existing_video" id="remove_existing_video" value="0">

                            <div class="upload-video-box mr-3 shadow-sm" onclick="document.getElementById('video_upload').click()" style="width: 110px; height: 110px; border: 2px dashed #a3c48c; border-radius: 8px; display: flex; align-items: center; justify-content: center; cursor: pointer; position: relative; overflow: hidden; background-color: #f9fdf6; flex-shrink: 0; transition: all 0.3s;">
                                
                                <input type="file" name="video" id="video_upload" class="d-none" accept="video/mp4" onchange="previewVideo(event)">
                                
                                <div id="video_delete_btn" class="{{ isset($product) && $product->video_path ? '' : 'd-none' }}" onclick="removeVideo(event)" style="position: absolute; top: 5px; right: 5px; background: rgba(220,53,69,0.9); border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; cursor: pointer; z-index: 10; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                                    <i class="fas fa-times text-white" style="font-size: 12px;"></i>
                                </div>

                                @if(isset($product) && $product->video_path)
                                    <video id="video_preview" src="{{ asset('storage/' . $product->video_path) }}" style="width: 100%; height: 100%; object-fit: cover;" controls onclick="event.stopPropagation()"></video>
                                    <div id="video_placeholder" class="text-center d-none" style="color: #3f7a08;">
                                        <i class="fas fa-video fa-2x mb-1"></i>
                                        <div style="font-size: 11px; font-weight: bold;">Tambah Video</div>
                                    </div>
                                @else
                                    <video id="video_preview" class="d-none" style="width: 100%; height: 100%; object-fit: cover;" controls onclick="event.stopPropagation()"></video>
                                    <div id="video_placeholder" class="text-center" style="color: #3f7a08;">
                                        <i class="fas fa-video fa-2x mb-1"></i>
                                        <div style="font-size: 11px; font-weight: bold;">Tambah Video</div>
                                    </div>
                                @endif
                            </div>
                            
                            <div class="small text-muted" style="line-height: 1.6;">
                                <ul class="pl-3 mb-0">
                                    <li>Maksimal 30MB, format MP4.</li>
                                    <li>Durasi ideal 10-60 detik.</li>
                                    <li>Video akan muncul setelah berhasil diproses oleh sistem.</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <hr class="mb-4" style="border-top: 1px dashed #eee;">

                    <div class="row mb-4">
                        <div class="col-md-3">
                            <label class="font-weight-bold"><span class="text-danger">*</span> Nama Produk</label>
                        </div>
                        <div class="col-md-9">
                            <input type="text" name="title" id="title" class="form-control px-3 py-2 @error('title') is-invalid @enderror" value="{{ old('title', $product->title ?? '') }}" placeholder="Contoh: Monstera King Variegata Mature" style="border-radius: 6px;" required>
                            @error('title')
                                <small class="text-danger font-weight-bold d-block mt-1"><i class="fas fa-exclamation-circle"></i> {{ $message }}</small>
                            @enderror
                            <small class="text-muted float-right mt-1" id="title-count">{{ strlen($product->title ?? '') }}/255</small>
                        </div>
                    </div>
                  
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <label class="font-weight-bold">Kode Produk (SKU)</label>
                        </div>
                        <div class="col-md-9">
                            <input type="text" name="sku" id="sku" class="form-control px-3 py-2" value="{{ old('sku', $product->sku ?? '') }}" placeholder="Masukkan Kode Unik Produk (Opsional)" style="border-radius: 6px;">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-3">
                            <label class="font-weight-bold"><span class="text-danger">*</span> Kategori</label>
                        </div>
                        <div class="col-md-9">
                            <div class="input-group mb-3 shadow-sm" id="btn-open-category" style="cursor: pointer; border-radius: 6px; overflow: hidden;">
                                <input type="text" id="category_display" class="form-control px-3" value="{{ $product->category_id }}" readonly required style="background-color: #fff; cursor: pointer; pointer-events: none; border-right: none;">
                                <div class="input-group-append">
                                    <span class="input-group-text bg-white border-left-0"><i class="fas fa-chevron-right" style="color: #3f7a08;"></i></span>
                                </div>
                                <input type="hidden" name="category_id" id="category_id" value="{{ $product->category_id }}">
                            </div>

                            <div class="card shadow-none border-0 mt-2" style="background-color: #f9fdf6; border-radius: 8px;">
                                <div class="card-body p-3">
                                    <small class="d-block mb-2" style="color: #3f7a08; font-weight: 700;"><i class="fas fa-history mr-1"></i> Terakhir Digunakan</small>
                                    <div id="recent-categories-list"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            
            <div class="card mb-4 border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0 px-4">
                    <h5 class="mb-0" style="color: #3f7a08; font-weight: 700;"><i class="fas fa-align-left mr-2"></i>Deskripsi</h5>
                </div>
                <div class="card-body p-4">
                    <div class="form-group row mb-0">
                        <div class="col-md-3">
                            <label class="font-weight-bold" for="description"><span class="text-danger">*</span> Detail Produk</label>
                        </div>
                        <div class="col-md-9">
                            <textarea name="description" id="description" class="form-control p-3" rows="8" placeholder="Tuliskan deskripsi lengkap, keunggulan, atau cara penggunaan produk..." style="border-radius: 8px;" required>{{ old('description', $product->description ?? '') }}</textarea>
                            <div class="d-flex justify-content-end mt-2">
                                <small class="text-muted font-weight-bold" id="char-count">{{ strlen($product->description ?? '') }}/3000</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4 border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0 px-4">
                    <h5 class="mb-0" style="color: #3f7a08; font-weight: 700;"><i class="fas fa-sliders-h mr-2"></i>Spesifikasi</h5>
                    <p class="text-muted small mt-2 mb-0">Lengkapi atribut produkmu agar dapat lebih banyak dilihat oleh Pembeli.</p>
                </div>
                <div class="card-body p-4">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold"><span class="text-danger">*</span> Merek</label>
                            <select name="brand" id="brand-select" class="form-control" style="border-radius: 6px;" required>
                                <option value="Tidak Ada Merek" {{ $product->brand == 'Tidak Ada Merek' ? 'selected' : '' }}>Tidak Ada Merek</option>
                                <option value="Bayer" {{ $product->brand == 'Bayer' ? 'selected' : '' }}>Bayer</option>
                                <option value="Infarm" {{ $product->brand == 'Infarm' ? 'selected' : '' }}>Infarm</option>
                                @if(!in_array($product->brand, ['Tidak Ada Merek', 'Bayer', 'Infarm', 'no-brand', 'bayer', 'infarm']) && $product->brand)
                                    <option value="{{ $product->brand }}" selected>{{ $product->brand }}</option>
                                @endif
                                <option value="add_new" style="color: #3f7a08; font-weight: bold;">+ Tambah Merek Baru...</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold"><span class="text-danger">*</span> Asal Produk</label>
                            <select name="origin" class="form-control" style="border-radius: 6px;" required>
                                <option value="lokal" {{ $product->origin == 'lokal' ? 'selected' : '' }}>Lokal</option>
                                <option value="import" {{ $product->origin == 'import' ? 'selected' : '' }}>Import</option>
                            </select>
                        </div>
                    </div>

                    <div id="more-specs" class="d-none mt-3 p-4" style="background-color: #fbfbfb; border-radius: 8px; border: 1px solid #f0f0f0;">
                        <div id="spec-tanaman" class="spec-group row">
                            <div class="col-md-6 mb-3"><label class="text-muted font-weight-bold">Tinggi / Ukuran Tanaman</label><select name="size" class="form-control"><option value="">Silakan pilih</option><option value="kecil" {{ $product->size == 'kecil' ? 'selected' : '' }}>Bibit (10-30 cm)</option><option value="sedang" {{ $product->size == 'sedang' ? 'selected' : '' }}>Remaja (30-60 cm)</option><option value="besar" {{ $product->size == 'besar' ? 'selected' : '' }}>Dewasa (> 60 cm)</option></select></div>
                            <div class="col-md-6 mb-3"><label class="text-muted font-weight-bold">Kebutuhan Cahaya</label><select name="light_requirement" class="form-control"><option value="">Silakan pilih</option><option value="full_sun" {{ $product->light_requirement == 'full_sun' ? 'selected' : '' }}>Panas Penuh (Full Sun)</option><option value="partial_shade" {{ $product->light_requirement == 'partial_shade' ? 'selected' : '' }}>Teduh Parsial</option><option value="indoor" {{ $product->light_requirement == 'indoor' ? 'selected' : '' }}>Indoor (Minim Cahaya)</option></select></div>
                            <div class="col-md-6 mb-3"><label class="text-muted font-weight-bold">Warna Utama</label><input type="text" name="color" class="form-control" value="{{ $product->color ?? '' }}"></div>
                            <div class="col-md-6 mb-3"><label class="text-muted font-weight-bold">Saran Perawatan</label><select name="care_level" class="form-control"><option value="">Silakan pilih</option><option value="easy" {{ $product->care_level == 'easy' ? 'selected' : '' }}>Mudah (Pemula)</option><option value="medium" {{ $product->care_level == 'medium' ? 'selected' : '' }}>Menengah</option><option value="hard" {{ $product->care_level == 'hard' ? 'selected' : '' }}>Expert</option></select></div>
                        </div>

                        <div id="spec-pupuk" class="spec-group row d-none">
                            <div class="col-md-6 mb-3"><label class="text-muted font-weight-bold">Bentuk Pupuk</label><select name="bentuk_pupuk" class="form-control"><option value="">Silakan pilih</option><option value="cair" {{ ($product->bentuk_pupuk ?? '') == 'cair' ? 'selected' : '' }}>Cair</option><option value="padat" {{ ($product->bentuk_pupuk ?? '') == 'padat' ? 'selected' : '' }}>Padat / Butiran</option><option value="serbuk" {{ ($product->bentuk_pupuk ?? '') == 'serbuk' ? 'selected' : '' }}>Serbuk</option></select></div>
                            <div class="col-md-6 mb-3"><label class="text-muted font-weight-bold">Volume / Berat Isi</label><input type="text" name="volume_isi" class="form-control" value="{{ $product->volume_isi ?? '' }}"></div>
                        </div>

                        <div id="spec-peralatan" class="spec-group row d-none">
                            <div class="col-md-6 mb-3"><label class="text-muted font-weight-bold">Bahan / Material</label><input type="text" name="bahan_material" class="form-control" value="{{ $product->bahan_material ?? '' }}"></div>
                            <div class="col-md-6 mb-3"><label class="text-muted font-weight-bold">Dimensi (P x L x T)</label><input type="text" name="dimensi" class="form-control" value="{{ $product->dimensi ?? '' }}"></div>
                        </div>
                    </div>

                    <button type="button" id="toggle-specs" class="btn btn-link btn-sm p-0 mt-3 font-weight-bold" style="color: #3f7a08; text-decoration: none;">
                        <span id="text-more">Tampilkan atribut lainnya <i class="fas fa-chevron-down ml-1"></i></span>
                        <span id="text-less" class="d-none">Tampilkan lebih sedikit <i class="fas fa-chevron-up ml-1"></i></span>
                    </button>
                </div>
            </div>

            @php
                $hasVariations = isset($product->variations) && $product->variations->count() > 0;
                $varName = $hasVariations ? $product->variations->first()->variation_name : 'Varian';
            @endphp
            
            <div class="card mb-4 border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0 px-4">
                    <h5 class="mb-0" style="color: #3f7a08; font-weight: 700;"><i class="fas fa-tags mr-2"></i>Informasi Penjualan</h5>
                </div>
                <div class="card-body p-4">

                    <div class="form-group mb-4 pb-4 border-bottom">
                        <label class="d-block font-weight-bold mb-3"><span class="text-danger">*</span> Variasi Produk</label>
                        
                        <div id="wrapper-enable-variation" class="{{ $hasVariations ? 'd-none' : '' }}">
                            <button type="button" onclick="toggleVariation(true)" class="shadow-sm" style="width: 180px; height: 45px; border: 1px dashed #3f7a08; background: #f9fdf6; color: #3f7a08; border-radius: 6px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.2s;">
                                <i class="fas fa-plus mr-1"></i> Aktifkan Variasi
                            </button>
                        </div>

                        <div id="variation-form-container" class="{{ $hasVariations ? '' : 'd-none' }} mt-2">
                            <div style="background-color: #fcfcfc; padding: 25px; border-radius: 8px; border: 1px solid #eaeaea; position: relative;">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <div class="font-weight-bold text-dark" style="font-size: 14px; width: 100px;">Nama Variasi</div>
                                    <input type="text" id="var1-name" name="variation_name" class="form-control bg-white flex-fill px-3" value="{{ $varName }}" placeholder="Contoh: Warna, Ukuran, Jenis" style="border-radius: 6px;">
                                    <button type="button" class="close text-muted ml-3" onclick="toggleVariation(false)" style="font-size: 24px;" title="Batalkan Variasi">&times;</button>
                                </div>
                                <div class="d-flex align-items-start">
                                    <div class="font-weight-bold text-dark" style="font-size: 14px; width: 100px; padding-top: 8px;">Pilihan</div>
                                    <div class="flex-fill">
                                        <div id="var1-options-list" class="row">
                                            @if($hasVariations)
                                                @foreach($product->variations as $var)
                                                    <div class="col-md-6 mb-3 var-option-row">
                                                        <div class="d-flex align-items-center">
                                                            <input type="text" class="form-control var1-option mr-2 px-3" value="{{ $var->variation_option }}" placeholder="Contoh: Merah, Besar, dll" style="border-radius: 6px;">
                                                            <button type="button" class="btn btn-outline-danger btn-sm remove-var-btn" style="border-radius: 6px; {{ $product->variations->count() > 1 ? '' : 'display:none;' }}"><i class="fas fa-trash"></i></button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @else
                                                <div class="col-md-6 mb-3 var-option-row">
                                                    <div class="d-flex align-items-center">
                                                        <input type="text" class="form-control var1-option mr-2 px-3" placeholder="Contoh: Merah, Hijau, dll" style="border-radius: 6px;">
                                                        <button type="button" class="btn btn-outline-danger btn-sm remove-var-btn" style="border-radius: 6px; display:none;"><i class="fas fa-trash"></i></button>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                        <button type="button" class="btn btn-link btn-sm p-0 font-weight-bold add-var1-btn mt-1" style="color: #3f7a08; text-decoration: none;"><i class="fas fa-plus-circle mr-1"></i> Tambah Pilihan Lain</button>
                                    </div>
                                </div>
                            </div>

                            <div id="variation-table-section" class="{{ $hasVariations ? '' : 'd-none' }} mt-4 pt-4 border-top">
                                <h6 class="font-weight-bold mb-3 text-dark">Detail Harga & Stok Variasi</h6>
                                
                                <div class="d-flex align-items-center p-3 mb-4 shadow-sm" style="background: #f9fdf6; border: 1px solid #e1ead9; border-radius: 8px; gap: 12px;">
                                    <div class="font-weight-bold text-muted" style="font-size: 13px;">Ubah Massal:</div>
                                    <div class="input-group input-group-sm flex-fill">
                                        <div class="input-group-prepend"><span class="input-group-text bg-white border-right-0 text-muted">Rp</span></div>
                                        <input type="number" id="apply-price" class="form-control border-left-0" placeholder="Harga">
                                    </div>
                                    <input type="number" id="apply-stock" class="form-control form-control-sm flex-fill" placeholder="Stok">
                                    <input type="text" id="apply-sku" class="form-control form-control-sm flex-fill" placeholder="Kode SKU">
                                    <button type="button" id="btn-apply-all" class="btn btn-sm text-white font-weight-bold px-3" style="background-color: #3f7a08; border-radius: 4px;">Terapkan Semua</button>
                                </div>

                                <div class="table-responsive border" style="border-radius: 8px;">
                                    <table class="table table-bordered text-center align-middle mb-0" style="font-size: 14px;" id="var-table">
                                        <thead style="background-color: #f4f6f9; color: #555;" id="var-table-head">
                                            <tr>
                                                <th class="col-var1 align-middle py-3" style="width: 250px;">{{ $varName }}</th>
                                                <th class="align-middle py-3"><span class="text-danger">*</span> Harga</th>
                                                <th width="150" class="align-middle py-3"><span class="text-danger">*</span> Stok</th>
                                                <th width="200" class="align-middle py-3">Kode SKU</th>
                                            </tr>
                                        </thead>
                                        <tbody id="var-table-body">
                                            @if($hasVariations)
                                                @foreach($product->variations as $var)
                                                    <tr class="var-row" data-key="{{ $var->variation_option }}">
                                                        <td class="align-middle">
                                                            <div class="d-flex align-items-center justify-content-start pl-3">
                                                                <div class="var-table-img-box border d-flex align-items-center justify-content-center mr-3 shadow-sm" style="width: 45px; height: 45px; border-style: dashed !important; cursor: pointer; background: #fff; border-radius: 6px; flex-shrink: 0;" data-key="{{ $var->variation_option }}">
                                                                    @if($var->image_path)
                                                                        <img src="{{ asset('storage/' . $var->image_path) }}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 4px;">
                                                                    @else
                                                                        <i class="fas fa-image text-muted"></i>
                                                                    @endif
                                                                </div>
                                                                <input type="file" class="var-table-img-input d-none" accept="image/*" data-key="{{ $var->variation_option }}">
                                                                <input type="hidden" name="variations[{{ $var->variation_option }}][option]" value="{{ $var->variation_option }}">
                                                                <span class="font-weight-bold" style="color: #333;">{{ $var->variation_option }}</span>
                                                            </div>
                                                        </td>
                                                        <td class="p-2 align-middle">
                                                            <div class="input-group input-group-sm">
                                                                <div class="input-group-prepend"><span class="input-group-text bg-white border-right-0 text-muted">Rp</span></div>
                                                                <input type="number" name="variations[{{ $var->variation_option }}][price]" class="form-control border-left-0 var-price" value="{{ $var->price }}">
                                                            </div>
                                                        </td>
                                                        <td class="p-2 align-middle"><input type="number" name="variations[{{ $var->variation_option }}][stock]" class="form-control form-control-sm var-stock" value="{{ $var->stock }}"></td>
                                                        <td class="p-2 align-middle"><input type="text" name="variations[{{ $var->variation_option }}][sku]" class="form-control form-control-sm var-sku" value="{{ $var->sku }}"></td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="single-input-section" class="{{ $hasVariations ? 'd-none' : '' }}">
                        <div class="form-group row mb-4">
                            <div class="col-md-3">
                                <label class="font-weight-bold"><span class="text-danger">*</span> Harga Satuan</label>
                            </div>
                            <div class="col-md-9">
                                <div class="input-group shadow-sm" style="border-radius: 6px; overflow: hidden; max-width: 300px;">
                                    <div class="input-group-prepend"><span class="input-group-text font-weight-bold" style="background: #f4f6f9; border-right: none; color: #555;">Rp</span></div>
                                    <input type="number" name="price" class="form-control" style="border-left: none;" value="{{ old('price', $product->price ?? '') }}" placeholder="0">
                                </div>
                            </div>
                        </div>
                        <div class="form-group row mb-4">
                            <div class="col-md-3">
                                <label class="font-weight-bold"><span class="text-danger">*</span> Stok <i class="far fa-question-circle text-muted ml-1" style="font-size: 12px;"></i></label>
                            </div>
                            <div class="col-md-9">
                                <input type="number" name="qty" class="form-control px-3" value="{{ old('qty', $product->qty ?? '') }}" placeholder="0" style="border-radius: 6px; max-width: 150px;">
                            </div>
                        </div>
                    </div>

                    <div class="form-group row mb-4">
                        <div class="col-md-3">
                            <label class="font-weight-bold"><span class="text-danger">*</span> Min. Pembelian</label>
                        </div>
                        <div class="col-md-9">
                            <input type="number" name="min_qty" class="form-control px-3" value="{{ old('min_qty', $product->min_qty ?? 1) }}" style="border-radius: 6px; max-width: 150px;" required>
                        </div>
                    </div>

                    <div class="form-group row mb-0">
                        <div class="col-md-3">
                            <label class="font-weight-bold">Batas Maks. Beli <i class="far fa-question-circle text-muted ml-1" style="font-size: 12px;"></i></label>
                        </div>
                        <div class="col-md-9">
                            <select name="max_purchase_type" id="max_purchase_type" class="form-control" style="border-radius: 6px; max-width: 250px;">
                                <option value="unlimited" {{ $product->max_purchase_type == 'unlimited' ? 'selected' : '' }}>Tanpa Batas</option>
                                <option value="per_order" {{ $product->max_purchase_type == 'per_order' ? 'selected' : '' }}>Batas Per Pesanan</option>
                            </select>
                            
                            <div id="max_limit_container" class="mt-3 p-3 shadow-sm {{ $product->max_purchase_type == 'per_order' ? '' : 'd-none' }}" style="background-color: #f9fdf6; border-radius: 6px; border: 1px solid #e1ead9; max-width: 350px;">
                                <label class="mb-2 font-weight-bold text-dark" style="font-size: 13px;"><span class="text-danger">*</span> Masukkan batas maksimal</label>
                                <input type="number" name="max_purchase_limit" class="form-control px-3" value="{{ old('max_purchase_limit', $product->max_purchase_limit ?? '') }}" placeholder="Contoh: 5">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-5 border-0 shadow-sm" style="border-radius: 12px; margin-bottom: 80px !important;">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0 px-4">
                    <h5 class="mb-0" style="color: #3f7a08; font-weight: 700;"><i class="fas fa-truck mr-2"></i>Pengiriman</h5>
                </div>
                <div class="card-body p-4">

                    <div class="form-group row mb-4">
                        <div class="col-md-3">
                            <label class="font-weight-bold"><span class="text-danger">*</span> Berat Produk</label>
                        </div>
                        <div class="col-md-9">
                            <div class="input-group shadow-sm" style="border-radius: 6px; overflow: hidden; max-width: 250px;">
                                <input type="number" name="weight" class="form-control px-3" value="{{ old('weight', $product->weight ?? '') }}" placeholder="Berat barang">
                                <div class="input-group-append">
                                    <span class="input-group-text font-weight-bold" style="background: #f4f6f9; color: #555; border-left: none;">Gram</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-right border-top pt-4 mt-2">
                        <a href="{{ route('products.index') }}" class="btn btn-light px-4 mr-2 shadow-sm font-weight-bold" style="color: #555; border-radius: 6px;">Batal</a>
                        <button type="submit" class="btn px-5 shadow-sm text-white font-weight-bold" style="background-color: #3f7a08; border: none; border-radius: 6px; padding-top: 10px; padding-bottom: 10px;">Update Produk</button>
                    </div>
                </div>
            </div>

        </div>
    </form>
</section>

<div class="modal fade" id="categoryModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header" style="background-color: #f9fdf6; border-bottom: 1px solid #e1ead9;">
                <h5 class="modal-title font-weight-bold" style="color: #3f7a08;"><i class="fas fa-sitemap mr-2"></i> Pilih Kategori</h5>
                <button type="button" class="close text-muted" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <div class="row m-0" style="height: 380px;">
                    <div class="col-6 p-0 border-right" style="overflow-y: auto;">
                        <ul class="list-group list-group-flush" id="main-category-list"></ul>
                    </div>
                    <div class="col-6 p-0" style="overflow-y: auto; background-color: #fdfdfd;">
                        <ul class="list-group list-group-flush" id="sub-category-list">
                            <li class="list-group-item text-muted text-center pt-5" style="background: transparent; border: none;">
                                <i class="fas fa-hand-point-left fa-2x mb-3 opacity-25"></i><br>
                                Pilih kategori utama di samping kiri
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="modal-footer pt-3 pb-3 border-top" style="background-color: #fff;">
                <button type="button" class="btn text-white px-5 font-weight-bold d-none shadow-sm" id="btn-confirm-category" data-dismiss="modal" style="background-color: #3f7a08; border-radius: 6px;">Konfirmasi Kategori</button>
            </div>
        </div>
    </div>
</div>
<div style="height: 2px;"></div>
@endsection

@section('customJs')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

<script>
    const dataKategori = {
        "Bibit & Benih": ["Sayuran", "Buah-buahan", "Herbal & Rempah", "Biji Bunga", "Pohon Kayu"],
        "Tanaman Hias": ["Tanaman Daun", "Tanaman Bunga", "Kaktus & Sukulen", "Tanaman Gantung", "Bonsai"],
        "Media Tanam & Pupuk": ["Tanah & Campuran", "Sekam & Cocopeat", "Pupuk Organik / Kompos", "Pupuk Kimia / NPK", "Vitamin & Hormon Tanaman"],
        "Peralatan Berkebun": ["Pot & Wadah", "Alat Tanam & Potong", "Penyiram Air", "Pembasmi Hama / Pestisida"]
    };

    // ================= LOGIKA VIDEO PREVIEW & HAPUS =================
    window.previewVideo = function(event) {
        let file = event.target.files[0];
        if (file) {
            if(file.size > 30 * 1024 * 1024) {
                alert('Ukuran video terlalu besar! Maksimal 30MB.');
                event.target.value = '';
                return;
            }
            let blobURL = URL.createObjectURL(file);
            let videoPreview = document.getElementById('video_preview');
            let placeholder = document.getElementById('video_placeholder');
            let deleteBtn = document.getElementById('video_delete_btn');
            
            videoPreview.src = blobURL;
            videoPreview.classList.remove('d-none');
            placeholder.classList.add('d-none');
            deleteBtn.classList.remove('d-none'); 
        }
    }

    window.removeVideo = function(event) {
        event.stopPropagation(); 
        document.getElementById('video_upload').value = '';
        let videoPreview = document.getElementById('video_preview');
        videoPreview.src = '';
        videoPreview.classList.add('d-none');
        document.getElementById('video_placeholder').classList.remove('d-none');
        document.getElementById('video_delete_btn').classList.add('d-none');
        let removeInput = document.getElementById('remove_existing_video');
        if(removeInput) { removeInput.value = '1'; }
    }

    $(document).ready(function() {

        // ================= LOGIKA FOTO DRAG & DROP =================
        let newUploadedFiles = {};
        let fileCounter = 0;

        updateCountAndPromo();

        function updateCountAndPromo() {
            let total = $('.preview-item').length;
            $('#count-img').text(total);
            
            if (total >= 9) $('#image-wrapper').hide();
            else $('#image-wrapper').show();

            let firstImgSrc = $('.preview-item').first().find('img').attr('src');
            if (firstImgSrc) {
                $('#promo-display').attr('src', firstImgSrc).show();
                $('#promo-text').hide();
            } else {
                $('#promo-display').hide();
                $('#promo-text').show();
            }
        }

        $(document).on('mouseenter', '.preview-item', function() {
            $(this).find('.delete-overlay').css('display', 'flex');
        }).on('mouseleave', '.preview-item', function() {
            $(this).find('.delete-overlay').hide();
        });

        window.removeNewImage = function(element, index) {
            delete newUploadedFiles[index];
            $(element).closest('.preview-item').remove();
            updateCountAndPromo();
        }

        window.removeExistingImage = function(element, id) {
            let currentVal = $('#deleted_images').val();
            $('#deleted_images').val(currentVal + id + ',');
            $(element).closest('.preview-item').remove();
            updateCountAndPromo();
        }

        $('#image-wrapper').click(function() {
            if ($('.preview-item').length < 9) $('#file-input').click();
            else alert("Maksimal 9 foto bre!");
        });

        $('#file-input').change(function(e) {
            let files = e.target.files;
            for (let i = 0; i < files.length; i++) {
                if ($('.preview-item').length < 9) {
                    let reader = new FileReader();
                    reader.onload = function(event) {
                        let id = fileCounter++;
                        newUploadedFiles[id] = event.target.result;
                        let html = `
                            <div class="preview-item new-img shadow-sm" data-index="${id}" data-type="new" style="width: 110px; height: 110px; position: relative; border: 1px solid #ddd; overflow: hidden; border-radius: 8px; cursor: grab; flex-shrink: 0;">
                                <img src="${event.target.result}" style="width: 100%; height: 100%; object-fit: cover;">
                                <div class="delete-overlay" onclick="removeNewImage(this, ${id})" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: none; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s;">
                                    <i class="fas fa-trash text-white fa-lg"></i>
                                </div>
                            </div>`;
                        $('#image-wrapper').before(html);
                        updateCountAndPromo();
                    }
                    reader.readAsDataURL(files[i]);
                }
            }
            $(this).val('');
        });

        let imageList = document.getElementById('image-list-container');
        new Sortable(imageList, {
            animation: 150,
            ghostClass: 'bg-light',
            filter: '#image-wrapper',
            onMove: function (evt) { return evt.related.id !== 'image-wrapper'; },
            onEnd: function () { updateCountAndPromo(); }
        });

        // ================= LOGIKA TABEL VARIASI =================
        let variationState = {}; 
        
        $('.var-row').each(function() {
            let key = $(this).attr('data-key');
            let imgSrc = $(this).find('img').length ? $(this).find('img').attr('src') : '';
            variationState[key] = {
                price: $(this).find('.var-price').val(),
                stock: $(this).find('.var-stock').val(),
                sku: $(this).find('.var-sku').val(),
                img: imgSrc
            };
        });
        updateTrashButtons();

        function saveTableState() {
            $('.var-row').each(function() {
                let key = $(this).attr('data-key');
                if(!variationState[key]) variationState[key] = {};
                variationState[key].price = $(this).find('.var-price').val();
                variationState[key].stock = $(this).find('.var-stock').val();
                variationState[key].sku = $(this).find('.var-sku').val();
            });
        }

        function renderVariationTable() {
            saveTableState(); 
            let var1Name = $('#var1-name').val() || 'Variasi 1';
            let var1Options = [];
            $('.var1-option').each(function() {
                let val = $(this).val().trim();
                if(val && !var1Options.includes(val)) var1Options.push(val);
            });

            if (var1Options.length === 0) {
                $('#variation-table-section').addClass('d-none');
                return;
            }

            $('#variation-table-section').removeClass('d-none');
            $('#var-table-head .col-var1').text(var1Name);

            let tbody = '';
            var1Options.forEach(function(opt) {
                let key = opt;
                let state = variationState[key] || { price: '', stock: '', sku: '', img: '' };
                let imgHtml = state.img ? `<img src="${state.img}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 4px;">` : `<i class="fas fa-image text-muted"></i>`;
                
                tbody += `
                    <tr class="var-row" data-key="${key}">
                        <td class="align-middle">
                            <div class="d-flex align-items-center justify-content-start pl-3">
                                <div class="var-table-img-box border d-flex align-items-center justify-content-center mr-3 shadow-sm" style="width: 45px; height: 45px; border-style: dashed !important; cursor: pointer; background: #fff; border-radius: 6px; flex-shrink: 0;" data-key="${key}">
                                    ${imgHtml}
                                </div>
                                <input type="file" class="var-table-img-input d-none" accept="image/*" data-key="${key}">
                                <input type="hidden" name="variations[${key}][option]" value="${key}">
                                <span class="font-weight-bold" style="color: #333;">${opt}</span>
                            </div>
                        </td>
                        <td class="p-2 align-middle">
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend"><span class="input-group-text bg-white border-right-0 text-muted">Rp</span></div>
                                <input type="number" name="variations[${key}][price]" class="form-control border-left-0 var-price" value="${state.price || ''}">
                            </div>
                        </td>
                        <td class="p-2 align-middle"><input type="number" name="variations[${key}][stock]" class="form-control form-control-sm var-stock" value="${state.stock || ''}"></td>
                        <td class="p-2 align-middle"><input type="text" name="variations[${key}][sku]" class="form-control form-control-sm var-sku" value="${state.sku || ''}"></td>
                    </tr>`;
            });
            $('#var-table-body').html(tbody);
        }

        $(document).on('blur', '.var1-option, #var1-name', function() { renderVariationTable(); });
        function updateTrashButtons() { $('#var1-options-list .var-option-row').length > 1 ? $('#var1-options-list .remove-var-btn').show() : $('#var1-options-list .remove-var-btn').hide(); }
        
        $('.add-var1-btn').click(function() {
            $('#var1-options-list').append(`
                <div class="col-md-6 mb-3 var-option-row">
                    <div class="d-flex align-items-center">
                        <input type="text" class="form-control var1-option mr-2 px-3" placeholder="Contoh: Pilihan Baru" style="border-radius: 6px;">
                        <button type="button" class="btn btn-outline-danger btn-sm remove-var-btn" style="border-radius: 6px;"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
            `);
            updateTrashButtons();
        });
        
        $(document).on('click', '.remove-var-btn', function() { $(this).closest('.var-option-row').remove(); updateTrashButtons(); renderVariationTable(); });
        $(document).on('click', '.var-table-img-box', function() { $(this).siblings('.var-table-img-input').click(); });
        $(document).on('change', '.var-table-img-input', function(e) {
            let file = e.target.files[0]; let key = $(this).attr('data-key');
            if(file) { let reader = new FileReader(); reader.onload = function(event) { if(!variationState[key]) variationState[key] = {}; variationState[key].img = event.target.result; renderVariationTable(); }; reader.readAsDataURL(file); }
        });

        $('#btn-apply-all').click(function() {
            let p = $('#apply-price').val(); let s = $('#apply-stock').val(); let k = $('#apply-sku').val();
            if(p !== "") $('.var-price').val(p); if(s !== "") $('.var-stock').val(s); if(k !== "") $('.var-sku').val(k);
        });

        $('#brand-select').on('change', function() {
            if ($(this).val() === 'add_new') {
                let newBrand = prompt("Masukkan nama merek baru:");
                if (newBrand && newBrand.trim() !== "") {
                    let formattedBrand = newBrand.trim().charAt(0).toUpperCase() + newBrand.trim().slice(1);
                    $(this).find('option[value="add_new"]').before(`<option value="${formattedBrand}" selected>${formattedBrand}</option>`);
                } else $(this).val('Tidak Ada Merek');
            }
        });

        function updateSpesifikasiForm(fullCategoryStr) {
            if(!fullCategoryStr) return;
            let mainCat = fullCategoryStr.split(' > ')[0].trim(); 
            $('.spec-group').addClass('d-none');
            if (mainCat === 'Bibit & Benih' || mainCat === 'Tanaman Hias') $('#spec-tanaman').removeClass('d-none');
            else if (mainCat === 'Media Tanam & Pupuk') $('#spec-pupuk').removeClass('d-none');
            else if (mainCat === 'Peralatan Berkebun') $('#spec-peralatan').removeClass('d-none');
        }

        function loadRecentCategories() {
            let recent = JSON.parse(localStorage.getItem('recent_categories'));
            if (!recent || recent.length === 0) {
                recent = ["Tanaman Hias > Tanaman Daun", "Bibit & Benih > Buah-buahan", "Media Tanam & Pupuk > Pupuk Organik"];
                localStorage.setItem('recent_categories', JSON.stringify(recent));
            }
            let html = '';
            recent.forEach((cat, index) => { 
                html += `
                <div class="custom-control custom-radio mb-2">
                    <input class="custom-control-input recent-cat-radio" type="radio" id="cat${index}" name="quick_cat" value="${cat}" data-label="${cat}">
                    <label for="cat${index}" class="custom-control-label text-muted" style="cursor: pointer; font-size: 14px;">${cat}</label>
                </div>`; 
            });
            $('#recent-categories-list').html(html);

            let firstCat = $('#category_display').val() || recent[0];
            $('#category_display').val(firstCat);
            $('#category_id').val(firstCat);
            updateSpesifikasiForm(firstCat);
        }
        loadRecentCategories();

        $(document).on('change', '.recent-cat-radio', function() {
            let selectedCat = $(this).val(); $('#category_display').val(selectedCat); $('#category_id').val(selectedCat); updateSpesifikasiForm(selectedCat); 
        });

        $('#btn-open-category').click(function() {
            $('#categoryModal').modal('show'); let html = '';
            for(let main in dataKategori) { 
                html += `<li class="list-group-item list-group-item-action main-cat-item py-3 font-weight-bold" data-main="${main}" style="cursor:pointer; border-left: 4px solid transparent; color: #555;">${main} <i class="fas fa-chevron-right float-right mt-1 text-muted" style="font-size:12px;"></i></li>`; 
            }
            $('#main-category-list').html(html); $('#sub-category-list').html('<li class="list-group-item text-muted text-center pt-5" style="background: transparent; border: none;"><i class="fas fa-hand-point-left fa-2x mb-3 opacity-25"></i><br>Pilih kategori utama di samping kiri</li>'); $('#btn-confirm-category').addClass('d-none');
        });

        $(document).on('click', '.main-cat-item', function() {
            $('.main-cat-item').css('border-left', '4px solid transparent').removeClass('bg-light font-weight-bold text-success').css('color', '#555'); 
            $(this).css('border-left', '4px solid #3f7a08').addClass('bg-light font-weight-bold').css('color', '#3f7a08');
            let tempSelectedMain = $(this).data('main'); let subs = dataKategori[tempSelectedMain]; let html = '';
            subs.forEach(sub => { html += `<li class="list-group-item list-group-item-action sub-cat-item py-3" data-sub="${sub}" style="cursor:pointer; background: transparent; border-left: 3px solid transparent;">${sub}</li>`; });
            $('#sub-category-list').html(html); $('#btn-confirm-category').addClass('d-none'); window.tempSelectedMainStr = tempSelectedMain;
        });

        $(document).on('click', '.sub-cat-item', function() { 
            $('.sub-cat-item').css('border-left', '3px solid transparent').css('color', '#555').removeClass('font-weight-bold'); 
            $(this).css('border-left', '3px solid #3f7a08').css('color', '#3f7a08').addClass('font-weight-bold'); 
            window.tempSelectedSubStr = $(this).data('sub'); $('#btn-confirm-category').removeClass('d-none'); 
        });

        $('#btn-confirm-category').click(function() {
            let fullCat = window.tempSelectedMainStr + ' > ' + window.tempSelectedSubStr; $('#category_display').val(fullCat); $('#category_id').val(fullCat); updateSpesifikasiForm(fullCat); 
            let recent = JSON.parse(localStorage.getItem('recent_categories')) || []; recent = recent.filter(item => item !== fullCat); recent.unshift(fullCat); if(recent.length > 3) recent.pop(); 
            localStorage.setItem('recent_categories', JSON.stringify(recent)); loadRecentCategories(); $(`input[value="${fullCat}"]`).prop('checked', true);
        });

        $('#title').on('input', function() { $('#title-count').text($(this).val().length + '/255'); });
        $('#description').on('input', function() {
            let count = $(this).val().length; $('#char-count').text(count + '/3000');
            if(count > 3000) $('#char-count').addClass('text-danger'); else $('#char-count').removeClass('text-danger');
        });

        $('#max_purchase_type').on('change', function() {
            if ($(this).val() === 'per_order') { $('#max_limit_container').removeClass('d-none'); $('input[name="max_purchase_limit"]').attr('required', true); } 
            else { $('#max_limit_container').addClass('d-none'); $('input[name="max_purchase_limit"]').removeAttr('required').val(''); }
        });

        $('#toggle-specs').click(function() {
            let moreSpecs = $('#more-specs'); let textMore = $('#text-more'); let textLess = $('#text-less');
            if (moreSpecs.hasClass('d-none')) { moreSpecs.hide().removeClass('d-none').fadeIn(); textMore.addClass('d-none'); textLess.removeClass('d-none'); } 
            else { moreSpecs.fadeOut(function() { $(this).addClass('d-none'); }); textMore.removeClass('d-none'); textLess.addClass('d-none'); }
        });

        // =================================================================
        // VALIDASI & PROSES SUBMIT
        // =================================================================
        $('#productForm').submit(function(e) {
            let isValid = true;
            let firstErrorElement = null;

            $('.error-text-js').remove();
            $('.is-invalid').removeClass('is-invalid border-danger');

            $(this).find('input[required], textarea[required], select[required]').each(function() {
                if ($(this).is(':visible') && $(this).val().trim() === '') {
                    isValid = false;
                    $(this).addClass('is-invalid border-danger');
                    $(this).after('<small class="text-danger error-text-js mt-1 d-block font-weight-bold">Kolom ini wajib diisi bre!</small>');
                    if (!firstErrorElement) firstErrorElement = $(this);
                }
            });

            if ($('#single-input-section').is(':visible')) {
                let priceEl = $('input[name="price"]');
                if (priceEl.val().trim() === '') {
                    isValid = false;
                    priceEl.addClass('is-invalid border-danger');
                    priceEl.parent().after('<small class="text-danger error-text-js d-block font-weight-bold mt-1">Harga wajib diisi!</small>');
                    if (!firstErrorElement) firstErrorElement = priceEl;
                }
            }

            let weightEl = $('input[name="weight"]');
            if (weightEl.val().trim() === '') {
                isValid = false;
                weightEl.addClass('is-invalid border-danger');
                weightEl.parent().after('<small class="text-danger error-text-js d-block font-weight-bold mt-1">Berat pengiriman wajib diisi!</small>');
                if (!firstErrorElement) firstErrorElement = weightEl;
            }

            if (!isValid) {
                e.preventDefault(); 
                $('html, body').animate({ scrollTop: firstErrorElement.offset().top - 150 }, 500);
                setTimeout(function() {
                    $('.error-text-js').fadeOut('slow');
                    $('.is-invalid').removeClass('is-invalid border-danger');
                }, 4000);
                return false; 
            }

            $('.hidden-image-input').remove();

            let finalOrder = [];
            $('.preview-item').each(function() {
                let type = $(this).data('type');
                if (type === 'existing') { finalOrder.push('old_' + $(this).data('id')); } 
                else if (type === 'new') { finalOrder.push('new_' + $(this).data('index')); }
            });
            $('#image_order').val(finalOrder.join(','));

            $('.preview-item.new-img').each(function() {
                let idx = $(this).data('index');
                if (newUploadedFiles[idx]) {
                    $('<input>').attr({ type: 'hidden', name: 'images_base64[' + idx + ']', class: 'hidden-image-input', value: newUploadedFiles[idx] }).appendTo('#productForm');
                }
            });
            
            saveTableState(); 
            $('.hidden-var-image-input').remove();
            for (let key in variationState) {
                if (variationState[key].img && variationState[key].img.startsWith('data:image')) {
                    $('<input>').attr({ type: 'hidden', name: `var_images[${key}]`, class: 'hidden-var-image-input', value: variationState[key].img }).appendTo('#productForm');
                }
            }

            return true; 
        });
    });

    window.toggleVariation = function(enable) {
        if (enable) {
            $('#wrapper-enable-variation').addClass('d-none'); $('#single-input-section').addClass('d-none'); $('#variation-form-container').removeClass('d-none'); $('#single-input-section input').val(''); 
        } else {
            $('#wrapper-enable-variation').removeClass('d-none'); $('#single-input-section').removeClass('d-none'); $('#variation-form-container').addClass('d-none');
            $('#var-table-body').empty(); $('#variation-table-section').addClass('d-none'); $('.var1-option').val('');
        }
    }
</script>
@endsection