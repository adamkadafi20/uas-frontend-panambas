@extends('admin.layouts.app')

@section('content')
<section class="content-header">					
    <div class="container-fluid my-2">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Tambah Banner Dekorasi Toko</h1>
            </div>
            <div class="col-sm-6 text-right">
                <a href="{{ route('banners.index') }}" class="btn btn-secondary">Kembali</a>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <form action="{{ route('banners.store') }}" method="POST" enctype="multipart/form-data" id="bannerForm">
            @csrf
            <div class="card border-0 shadow-sm" style="border-radius: 8px;">
                <div class="card-body p-4">
                    
                    <div class="row">
                        <div class="col-md-7">
                            <div class="form-group mb-4">
                                <label class="font-weight-bold"><span class="text-danger">*</span> Nama / Judul Promo Banner</label>
                                <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" placeholder="Contoh: Promo Gila Gajian Mei Tanaman Daun" value="{{ old('title') }}" required>
                                @error('title')
                                    <small class="text-danger font-weight-bold">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="form-group mb-4">
                                <label class="font-weight-bold"><span class="text-danger">*</span> File Media (Gambar / Video Canva)</label>
                                <div class="custom-file">
                                    <input type="file" name="image" class="custom-file-input @error('image') is-invalid @enderror" id="imageInput" accept="image/*,video/mp4" onchange="previewMedia(event)" required>
                                    <label class="custom-file-label" for="imageInput">Pilih file dari laptop...</label>
                                </div>
                                <small class="text-muted d-block mt-2">Format: <b>JPG, PNG, WEBP, atau MP4 (Video Canva)</b>. Ukuran maksimal file: <b>20MB</b>.</small>
                                @error('image')
                                    <small class="text-danger font-weight-bold d-block mt-1">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="form-group mb-4">
                                <label class="font-weight-bold">Link Redirect Tujuan (Opsional)</label>
                                <input type="text" name="link" class="form-control" placeholder="Contoh: /shop?category=Tanaman Hias" value="{{ old('link') }}">
                                <small class="text-muted">Kosongkan saja jika banner ini tidak perlu diarahkan ke halaman manapun pas diklik pembeli.</small>
                            </div>

                            <div class="form-group mb-4">
                                <label class="font-weight-bold">Status Tampilkan</label>
                                <select name="status" class="form-control">
                                    <option value="1">Aktif (Langsung Tampilkan di Slide Halaman Utama)</option>
                                    <option value="0">Non-Aktif (Simpan Dulu, Jangan Ditampilkan)</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-5 d-flex flex-col justify-content-start pt-2">
                            <label class="font-weight-bold text-center d-block w-100 mb-3">Live Real-time Preview Banner</label>
                            
                            <div class="border rounded d-flex align-items-center justify-content-center text-center mx-auto bg-light text-muted overflow-hidden shadow-sm" 
                                 id="previewContainer" 
                                 style="width: 100%; max-width: 400px; height: 200px; border-style: dashed !important; border-width: 2px !important;">
                                
                                <div id="placeholderText">
                                    <i class="fas fa-photo-video fa-3x mb-2 text-muted opacity-50"></i>
                                    <p class="small mb-0 text-muted px-3">Belum ada file dipilih.<br>Preview gambar/video Canva lo bakal muncul di sini bre.</p>
                                </div>

                                <img id="imgPreview" class="w-100 h-100 d-none" style="object-fit: cover;">
                                <video id="videoPreview" class="w-100 h-100 d-none" style="object-fit: cover;" controls muted loop></video>
                            </div>
                        </div>
                    </div>

                    <div class="border-top pt-4 mt-4 text-right">
                        <a href="{{ route('banners.index') }}" class="btn btn-light border px-4 mr-2">Batal</a>
                        <button type="submit" class="btn btn-success px-5 font-weight-bold" style="background-color: #247a6b; border: none;">Simpan & Terapkan Dekorasi</button>
                    </div>

                </div>
            </div>
        </form>
    </div>
</section>
@endsection

@section('customJs')
<script>
    // Logic Live Preview File Media Dinamis (.jpg / .mp4)
    function previewMedia(event) {
        const file = event.target.files[0];
        const label = event.target.nextElementSibling;
        
        if (file) {
            // Update nama teks di form upload input
            label.textContent = file.name;

            // Validasi ukuran max 20MB di sisi javascript browser
            if(file.size > 20 * 1024 * 1024) {
                alert('Ukuran file terlalu jumbo bre! Maksimal cuma boleh 20MB.');
                event.target.value = '';
                label.textContent = 'Pilih file dari laptop...';
                return;
            }

            const reader = new FileReader();
            const container = document.getElementById('previewContainer');
            const placeholder = document.getElementById('placeholderText');
            const imgPreview = document.getElementById('imgPreview');
            const videoPreview = document.getElementById('videoPreview');

            reader.onload = function(e) {
                placeholder.classList.add('d-none');
                container.style.borderStyle = 'solid';

                if (file.type.includes('video')) {
                    // Jika file yang diupload ternyata video mp4
                    imgPreview.classList.add('d-none');
                    videoPreview.src = e.target.result;
                    videoPreview.classList.remove('d-none');
                    videoPreview.play();
                } else {
                    // Jika file yang diupload ternyata gambar biasa
                    videoPreview.classList.add('d-none');
                    videoPreview.src = '';
                    imgPreview.src = e.target.result;
                    imgPreview.classList.remove('d-none');
                }
            }
            reader.readAsDataURL(file);
        }
    }
</script>
@endsection