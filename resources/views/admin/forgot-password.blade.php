<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Panambas Seller :: Ganti Password</title>
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
        <link rel="stylesheet" href="{{ asset('admin-assets/plugins/fontawesome-free/css/all.min.css') }}">
        <link rel="stylesheet" href="{{asset('admin-assets/css/adminlte.min.css')}}">
        <link rel="stylesheet" href="{{asset('admin-assets/css/custom.css')}}">
        
        <script src="https://cdn.tailwindcss.com"></script>

        <style>
            .form-control { padding: 22px 15px; font-size: 1rem; border-radius: 6px; }
            .form-control:focus { border-color: #247a6b; box-shadow: 0 0 0 0.2rem rgba(36, 122, 107, 0.2); }
            .toggle-password { cursor: pointer; color: #a0aec0; transition: color 0.3s; }
            .toggle-password:hover { color: #247a6b; }
        </style>
    </head>
    
    <body class="bg-[#247a6b] h-screen overflow-hidden font-sans antialiased">
        
        <nav class="bg-white px-6 md:px-10 py-4 shadow-md flex justify-between items-center relative z-10">
            <div class="flex items-center">
                <i class="fas fa-leaf text-[#247a6b] text-2xl md:text-3xl"></i>
                <span class="text-[#247a6b] font-black text-xl md:text-2xl tracking-wide ml-2">PANAMBAS</span>
                <span class="text-gray-600 text-lg ml-3 hidden sm:inline-block font-medium">Seller Center</span>
            </div>
            <div>
                <a href="#" class="text-[#247a6b] font-semibold hover:underline text-sm md:text-base">Butuh bantuan?</a>
            </div>
        </nav>

        <div class="flex h-[calc(100vh-76px)] w-full max-w-7xl mx-auto">
            
            <div class="hidden md:flex flex-[1.2] flex-col justify-center items-center text-white px-4">
                <i class="fas fa-leaf text-8xl mb-6 drop-shadow-xl"></i>
                <h1 class="text-5xl font-extrabold drop-shadow-xl mb-2 text-center tracking-tight">Panambas Seller</h1>
                <p class="text-2xl text-[#cbf4ec] drop-shadow-md">Keamanan Akun</p>
            </div>

            <div class="flex-1 flex justify-center items-center px-4 w-full">
                <div class="bg-white rounded-xl p-8 md:p-10 w-full max-w-[420px] shadow-[0_15px_30px_rgba(0,0,0,0.2)]">
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">Ubah Password</h3>
                    <p class="text-gray-500 text-sm mb-6">Masukkan email terdaftar dan password baru Anda.</p>
                    
                    @include('admin.message')

                    <form action="{{ route('admin.processForgotPassword') }}" method="post" autocomplete="off">
                        @csrf
                        
                        <div class="form-group mb-4">
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" placeholder="Email Terdaftar" autocomplete="off">
                            @error('email')
                                <p class="invalid-feedback">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="form-group mb-6">
                            <div class="relative">
                                <input type="password" name="new_password" id="new_password" class="form-control @error('new_password') is-invalid @enderror" placeholder="Password Baru" autocomplete="new-password">
                                <i class="fas fa-eye-slash toggle-password absolute right-4 top-1/2 -translate-y-1/2 text-[1.05rem]" id="togglePassword"></i>
                            </div>
                            @error('new_password')
                                <p class="invalid-feedback d-block mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex gap-3">
                            <a href="{{ route('admin.login') }}" class="w-1/3 bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold py-3.5 rounded-[5px] transition duration-300 text-center text-[1rem]">
                                Kembali
                            </a>
                            <button type="submit" class="w-2/3 bg-[#247a6b] hover:bg-[#1b5e52] text-white font-bold py-3.5 rounded-[5px] transition duration-300 shadow-md text-[1rem]">
                                Konfirmasi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script src="{{asset('admin-assets/plugins/jquery/jquery.min.js')}}"></script>
        <script src="{{asset('admin-assets/plugins/bootstrap/js/bootstrap.bundle.min.js')}}"></script>

        <script>
            // Logika MATA (Show/Hide Password)
            const togglePassword = document.querySelector('#togglePassword');
            const password = document.querySelector('#new_password');

            togglePassword.addEventListener('click', function () {
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);
                
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });

            // Logika Auto-Hide Alert
            document.addEventListener("DOMContentLoaded", function() {
                const alerts = document.querySelectorAll('.alert-auto-hide');
                alerts.forEach(function(alert) {
                    setTimeout(function() {
                        alert.style.transition = "opacity 0.6s ease";
                        alert.style.opacity = "0";
                        setTimeout(function() {
                            alert.remove();
                        }, 600);
                    }, 3000);
                });
            });
        </script>
    </body>
</html>