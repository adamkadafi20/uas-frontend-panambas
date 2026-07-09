@extends('admin.layouts.app')

@section('content')
<style>
    .btn-custom { background-color: #247a6b; color: white; border: none; transition: all 0.3s; }
    .btn-custom:hover { background-color: #1b5e52; color: white; }
    .form-control:focus { border-color: #247a6b; box-shadow: 0 0 0 0.2rem rgba(36, 122, 107, 0.25); }
    .input-group-text { cursor: pointer; background-color: transparent; border-left: none; }
    .form-control.border-right-0 { border-right: none; }
    .card-header-custom { background-color: #247a6b; color: white; border-radius: 10px 10px 0 0; }
</style>

<section class="content-header">
    <div class="container-fluid my-2">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h2 class="font-weight-bold text-dark m-0">Edit Akses Seller</h2>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-7">
                <div class="card shadow border-0 mt-3" style="border-radius: 10px;">
                    <div class="card-header card-header-custom py-3">
                        <h4 class="card-title font-weight-bold m-0"><i class="fas fa-user-edit mr-2"></i> Edit Akun: {{ $user->email }}</h4>
                    </div>
                    
                    <form action="{{ route('users.update', $user->id) }}" method="post" autocomplete="off">
                        @csrf
                        @method('PUT') <div class="card-body p-4 p-md-5">
                            
                            <div class="form-group mb-4">
                                <label for="email" class="text-muted font-weight-bold">Alamat Email (Untuk Login)</label>
                                <div class="input-group shadow-sm rounded">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text border-right-0 bg-light"><i class="fas fa-envelope text-muted"></i></span>
                                    </div>
                                    <input type="email" name="email" id="email" class="form-control border-left-0 pl-0 @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" autocomplete="off" required>
                                </div>
                                @error('email') <small class="text-danger mt-1 d-block">{{ $message }}</small> @enderror
                            </div>

                            <div class="form-group mb-4">
                                <label for="password" class="text-muted font-weight-bold">Password Akses Baru</label>
                                <div class="input-group shadow-sm rounded">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text border-right-0 bg-light"><i class="fas fa-lock text-muted"></i></span>
                                    </div>
                                    <input type="password" name="password" id="password" class="form-control border-left-0 border-right-0 pl-0 @error('password') is-invalid @enderror" placeholder="Ketik password baru jika ingin diubah..." autocomplete="new-password">
                                    <div class="input-group-append">
                                        <span class="input-group-text" onclick="togglePassword()" style="border-top-right-radius: 0.25rem; border-bottom-right-radius: 0.25rem;">
                                            <i class="fas fa-eye text-muted" id="eye-icon"></i>
                                        </span>
                                    </div>
                                </div>
                                <small class="text-warning mt-2 d-block"><i class="fas fa-info-circle mr-1"></i> Biarkan kosong jika tidak ingin mengubah password.</small>
                                @error('password') <small class="text-danger mt-1 d-block">{{ $message }}</small> @enderror
                            </div>

                        </div>
                        
                        <div class="card-footer bg-white border-top p-4 text-right" style="border-radius: 0 0 10px 10px;">
                            <a href="{{ route('users.index') }}" class="btn btn-light font-weight-bold mr-2 px-4 border shadow-sm">Batal</a>
                            <button type="submit" class="btn btn-custom font-weight-bold px-4 shadow-sm"><i class="fas fa-save mr-2"></i> Update Akun</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    function togglePassword() {
        let passInput = document.getElementById('password');
        let eyeIcon = document.getElementById('eye-icon');
        
        if (passInput.type === 'password') {
            passInput.type = 'text';
            eyeIcon.classList.remove('fa-eye');
            eyeIcon.classList.add('fa-eye-slash');
            eyeIcon.classList.add('text-primary');
        } else {
            passInput.type = 'password';
            eyeIcon.classList.remove('fa-eye-slash');
            eyeIcon.classList.remove('text-primary');
            eyeIcon.classList.add('fa-eye');
        }
    }
</script>
@endsection