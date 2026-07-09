@extends('admin.layouts.app')

@section('content')
<style>
    .nav-pills .nav-link.active, .nav-pills .show > .nav-link { background-color: #247a6b !important; color: #fff !important; font-weight: 600; }
    .nav-pills .nav-link { color: #4b5563; transition: all 0.2s ease-in-out; }
    .nav-pills .nav-link:hover { background-color: #f0f7f5; color: #247a6b; }
    .btn-custom { background-color: #247a6b; color: white; border: none; transition: all 0.3s; }
    .btn-custom:hover { background-color: #1b5e52; color: white; }
    .form-control:focus { border-color: #247a6b; box-shadow: 0 0 0 0.2rem rgba(36, 122, 107, 0.15); }
    .section-title { font-size: 11px; font-weight: 700; color: #247a6b; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 15px; display: flex; align-items: center; }
    .section-title i { margin-right: 8px; font-size: 14px; }
    .card-alamat { border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; margin-bottom: 20px; }
</style>

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-3 mt-2">
            <div class="col-sm-6"><h2 class="font-weight-bold text-dark">Pengaturan Toko</h2></div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3">
                <div class="card shadow-sm border-0" style="border-radius: 10px;">
                    <div class="card-body p-2">
                        <ul class="nav nav-pills flex-column" id="settings-tab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active py-3 rounded" data-toggle="pill" href="#tab-profil" role="tab">
                                    <i class="fas fa-user-shield mr-2 w-5 text-center"></i> Profil & Keamanan
                                </a>
                            </li>
                            <li class="nav-item mt-1">
                                <a class="nav-link py-3 rounded" data-toggle="pill" href="#tab-alamat" role="tab">
                                    <i class="fas fa-map-marked-alt mr-2 w-5 text-center"></i> Alamat Toko
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-md-9">
                <div class="card shadow-sm border-0" style="border-radius: 10px;">
                    <div class="card-body p-4 p-md-5">
                        
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert" style="background-color: #e6f2f0; color: #1b5e52;">
                                <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif
                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert" style="background-color: #fff5f5; color: #c53030;">
                                <i class="fas fa-exclamation-triangle mr-2"></i> {{ session('error') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        <div class="tab-content">
                            
                            <div class="tab-pane fade show active" id="tab-profil" role="tabpanel">
                                <h4 class="mb-4 font-weight-bold text-dark">Profil & Keamanan</h4>
                                
                                <form action="{{ route('admin.settings.updateProfile') }}" method="POST">
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-6 form-group">
                                            <label class="text-muted font-weight-normal">Nama Lengkap (Super Admin)</label>
                                            <input type="text" class="form-control" name="name" value="{{ Auth::user()->name }}" required>
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label class="text-muted font-weight-normal">Alamat Email (Gmail)</label>
                                            <input type="email" class="form-control" name="email" value="{{ Auth::user()->email }}" required>
                                        </div>
                                    </div>

                                    <hr class="my-4 border-light">
                                    
                                    <h5 class="mb-3 font-weight-bold text-dark">Ubah Password Log In Toko</h5>
                                    <div class="row bg-light p-3 rounded mb-4 border">
                                        <div class="col-md-4 form-group mb-0">
                                            <label class="text-muted text-sm">Password Saat Ini</label>
                                            <input type="password" class="form-control form-control-sm" name="current_login_password">
                                        </div>
                                        <div class="col-md-4 form-group mb-0">
                                            <label class="text-muted text-sm">Password Baru</label>
                                            <input type="password" class="form-control form-control-sm" name="new_login_password">
                                        </div>
                                        <div class="col-md-4 form-group mb-0">
                                            <label class="text-muted text-sm">Konfirmasi Password</label>
                                            <input type="password" class="form-control form-control-sm" name="confirm_login_password">
                                        </div>
                                    </div>

                                    <h5 class="mb-3 font-weight-bold text-danger"><i class="fas fa-lock mr-2"></i>Password Keamanan (Saldo & Pengaturan)</h5>
                                    <div class="row bg-danger-light p-3 rounded border" style="background-color: #fff5f5; border-color: #ffe0e0 !important;">
                                        <div class="col-12 mb-2">
                                            <small class="text-danger"><i>*Password ini wajib dimasukkan saat ingin membuka menu Saldo, User, atau Pengaturan Toko.</i></small>
                                        </div>
                                        <div class="col-md-4 form-group mb-0">
                                            <label class="text-muted text-sm">Pin/Password Saat Ini</label>
                                            <input type="password" class="form-control form-control-sm" name="current_security_password">
                                        </div>
                                        <div class="col-md-4 form-group mb-0">
                                            <label class="text-muted text-sm">Pin/Password Baru</label>
                                            <input type="password" class="form-control form-control-sm" name="new_security_password">
                                        </div>
                                        <div class="col-md-4 form-group mb-0">
                                            <label class="text-muted text-sm">Konfirmasi</label>
                                            <input type="password" class="form-control form-control-sm" name="confirm_security_password">
                                        </div>
                                    </div>

                                    <div class="mt-4 pt-3 border-top text-right">
                                        <button type="submit" class="btn btn-custom px-4 py-2 font-weight-bold rounded shadow-sm"><i class="fas fa-save mr-2"></i> Simpan Keamanan</button>
                                    </div>
                                </form>
                            </div>

                            <div class="tab-pane fade" id="tab-alamat" role="tabpanel">
                                <h4 class="mb-4 font-weight-bold text-dark">Alamat Pengiriman (Toko)</h4>
                                
                                <form action="{{ route('admin.settings.updateAddress') }}" method="POST">
                                    @csrf
                                    <div class="card-alamat bg-white shadow-sm">
                                        <div class="section-title"><i class="fas fa-user-circle"></i> INFO PENGIRIM</div>
                                        <div class="row">
                                            <div class="col-md-6 form-group mb-md-0">
                                                <input type="text" class="form-control" name="sender_name" placeholder="Nama Pengirim (Contoh: Panambas Store)" value="{{ $store->sender_name ?? '' }}">
                                            </div>
                                            <div class="col-md-6 form-group mb-0">
                                                <input type="text" class="form-control" name="sender_phone" placeholder="Nomor Telepon (Contoh: 0812345678)" value="{{ $store->sender_phone ?? '' }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card-alamat bg-white shadow-sm">
                                        <div class="section-title"><i class="fas fa-map-marker-alt"></i> WILAYAH PENGIRIMAN</div>
                                        <div class="row">
                                            <div class="col-md-6 form-group">
                                                <select name="province" class="form-control">
    <option value="" disabled {{ empty($store->province) ? 'selected' : '' }}>Pilih Provinsi...</option>
    <option value="ACEH" {{ ($store->province ?? '') == 'ACEH' ? 'selected' : '' }}>Aceh</option>
    <option value="SUMATERA UTARA" {{ ($store->province ?? '') == 'SUMATERA UTARA' ? 'selected' : '' }}>Sumatera Utara</option>
    <option value="SUMATERA BARAT" {{ ($store->province ?? '') == 'SUMATERA BARAT' ? 'selected' : '' }}>Sumatera Barat</option>
    <option value="RIAU" {{ ($store->province ?? '') == 'RIAU' ? 'selected' : '' }}>Riau</option>
    <option value="JAMBI" {{ ($store->province ?? '') == 'JAMBI' ? 'selected' : '' }}>Jambi</option>
    <option value="SUMATERA SELATAN" {{ ($store->province ?? '') == 'SUMATERA SELATAN' ? 'selected' : '' }}>Sumatera Selatan</option>
    <option value="BENGKULU" {{ ($store->province ?? '') == 'BENGKULU' ? 'selected' : '' }}>Bengkulu</option>
    <option value="LAMPUNG" {{ ($store->province ?? '') == 'LAMPUNG' ? 'selected' : '' }}>Lampung</option>
    <option value="KEPULAUAN BANGKA BELITUNG" {{ ($store->province ?? '') == 'KEPULAUAN BANGKA BELITUNG' ? 'selected' : '' }}>Kepulauan Bangka Belitung</option>
    <option value="KEPULAUAN RIAU" {{ ($store->province ?? '') == 'KEPULAUAN RIAU' ? 'selected' : '' }}>Kepulauan Riau</option>
    <option value="DKI JAKARTA" {{ ($store->province ?? '') == 'DKI JAKARTA' ? 'selected' : '' }}>DKI Jakarta</option>
    <option value="JAWA BARAT" {{ ($store->province ?? '') == 'JAWA BARAT' ? 'selected' : '' }}>Jawa Barat</option>
    <option value="JAWA TENGAH" {{ ($store->province ?? '') == 'JAWA TENGAH' ? 'selected' : '' }}>Jawa Tengah</option>
    <option value="DI YOGYAKARTA" {{ ($store->province ?? '') == 'DI YOGYAKARTA' ? 'selected' : '' }}>DI Yogyakarta</option>
    <option value="JAWA TIMUR" {{ ($store->province ?? '') == 'JAWA TIMUR' ? 'selected' : '' }}>Jawa Timur</option>
    <option value="BANTEN" {{ ($store->province ?? '') == 'BANTEN' ? 'selected' : '' }}>Banten</option>
    <option value="BALI" {{ ($store->province ?? '') == 'BALI' ? 'selected' : '' }}>Bali</option>
    <option value="NUSA TENGGARA BARAT" {{ ($store->province ?? '') == 'NUSA TENGGARA BARAT' ? 'selected' : '' }}>Nusa Tenggara Barat</option>
    <option value="NUSA TENGGARA TIMUR" {{ ($store->province ?? '') == 'NUSA TENGGARA TIMUR' ? 'selected' : '' }}>Nusa Tenggara Timur</option>
    <option value="KALIMANTAN BARAT" {{ ($store->province ?? '') == 'KALIMANTAN BARAT' ? 'selected' : '' }}>Kalimantan Barat</option>
    <option value="KALIMANTAN TENGAH" {{ ($store->province ?? '') == 'KALIMANTAN TENGAH' ? 'selected' : '' }}>Kalimantan Tengah</option>
    <option value="KALIMANTAN SELATAN" {{ ($store->province ?? '') == 'KALIMANTAN SELATAN' ? 'selected' : '' }}>Kalimantan Selatan</option>
    <option value="KALIMANTAN TIMUR" {{ ($store->province ?? '') == 'KALIMANTAN TIMUR' ? 'selected' : '' }}>Kalimantan Timur</option>
    <option value="KALIMANTAN UTARA" {{ ($store->province ?? '') == 'KALIMANTAN UTARA' ? 'selected' : '' }}>Kalimantan Utara</option>
    <option value="SULAWESI UTARA" {{ ($store->province ?? '') == 'SULAWESI UTARA' ? 'selected' : '' }}>Sulawesi Utara</option>
    <option value="SULAWESI TENGAH" {{ ($store->province ?? '') == 'SULAWESI TENGAH' ? 'selected' : '' }}>Sulawesi Tengah</option>
    <option value="SULAWESI SELATAN" {{ ($store->province ?? '') == 'SULAWESI SELATAN' ? 'selected' : '' }}>Sulawesi Selatan</option>
    <option value="SULAWESI TENGGARA" {{ ($store->province ?? '') == 'SULAWESI TENGGARA' ? 'selected' : '' }}>Sulawesi Tenggara</option>
    <option value="GORONTALO" {{ ($store->province ?? '') == 'GORONTALO' ? 'selected' : '' }}>Gorontalo</option>
    <option value="SULAWESI BARAT" {{ ($store->province ?? '') == 'SULAWESI BARAT' ? 'selected' : '' }}>Sulawesi Barat</option>
    <option value="MALUKU" {{ ($store->province ?? '') == 'MALUKU' ? 'selected' : '' }}>Maluku</option>
    <option value="MALUKU UTARA" {{ ($store->province ?? '') == 'MALUKU UTARA' ? 'selected' : '' }}>Maluku Utara</option>
    <option value="PAPUA" {{ ($store->province ?? '') == 'PAPUA' ? 'selected' : '' }}>Papua</option>
    <option value="PAPUA BARAT" {{ ($store->province ?? '') == 'PAPUA BARAT' ? 'selected' : '' }}>Papua Barat</option>
    <option value="PAPUA SELATAN" {{ ($store->province ?? '') == 'PAPUA SELATAN' ? 'selected' : '' }}>Papua Selatan</option>
    <option value="PAPUA TENGAH" {{ ($store->province ?? '') == 'PAPUA TENGAH' ? 'selected' : '' }}>Papua Tengah</option>
    <option value="PAPUA PEGUNUNGAN" {{ ($store->province ?? '') == 'PAPUA PEGUNUNGAN' ? 'selected' : '' }}>Papua Pegunungan</option>
    <option value="PAPUA BARAT DAYA" {{ ($store->province ?? '') == 'PAPUA BARAT DAYA' ? 'selected' : '' }}>Papua Barat Daya</option>
</select>
                                            </div>
                                            <div class="col-md-6 form-group">
                                                <input type="text" class="form-control" name="city" placeholder="Kota / Kabupaten" value="{{ $store->city ?? '' }}">
                                            </div>
                                            <div class="col-md-6 form-group mb-md-0">
                                                <input type="text" class="form-control" name="district" placeholder="Kecamatan" value="{{ $store->district ?? '' }}">
                                            </div>
                                            <div class="col-md-6 form-group mb-0">
                                                <input type="text" class="form-control" name="postal_code" placeholder="Kode Pos" value="{{ $store->postal_code ?? '' }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card-alamat bg-white shadow-sm">
                                        <div class="section-title"><i class="fas fa-home"></i> DETAIL ALAMAT & PATOKAN</div>
                                        <div class="form-group mb-0">
                                            <textarea class="form-control" name="detail_address" rows="3" placeholder="Nama Jalan, Gedung, Patokan (Contoh: Depan toko ikan...)">{{ $store->detail_address ?? '' }}</textarea>
                                        </div>
                                    </div>

                                    <div class="mt-4 pt-3 border-top text-right">
                                        <button type="reset" class="btn btn-light px-4 py-2 font-weight-bold rounded mr-2">Reset</button>
                                        <button type="submit" class="btn btn-custom px-4 py-2 font-weight-bold rounded shadow-sm"><i class="fas fa-save mr-2"></i> Simpan & Gunakan</button>
                                    </div>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection