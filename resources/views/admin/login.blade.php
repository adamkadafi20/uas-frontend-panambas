<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Panambas Seller :: Login Panel</title>
        <!-- Google Font: Source Sans Pro -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
        <!-- Font Awesome -->
        <link rel="stylesheet" href="{{ asset('admin-assets/plugins/fontawesome-free/css/all.min.css') }}">
        <!-- Theme style (Bootstrap & AdminLTE) -->
        <link rel="stylesheet" href="{{asset('admin-assets/css/adminlte.min.css')}}">
        <link rel="stylesheet" href="{{asset('admin-assets/css/custom.css')}}">
        
        <!-- TAILWIND CSS CDN -->
        <script src="https://cdn.tailwindcss.com"></script>

        <style>
            /* Custom CSS dikit buat form fokus & ikon mata */
            .form-control { padding: 22px 15px; font-size: 1rem; border-radius: 6px; }
            .form-control:focus { border-color: #247a6b; box-shadow: 0 0 0 0.2rem rgba(36, 122, 107, 0.2); }
            .toggle-password { cursor: pointer; color: #a0aec0; transition: color 0.3s; }
            .toggle-password:hover { color: #247a6b; }
        </style>
    </head>
    
    <!-- Pake Tailwind buat background hijau full layar -->
    <body class="bg-[#247a6b] h-screen overflow-hidden font-sans antialiased">
        
        <!-- NAVBAR ATAS (Tailwind) -->
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

        <!-- MAIN LAYOUT: Split Screen Kiri Kanan (Tailwind) -->
        <div class="flex h-[calc(100vh-76px)] w-full max-w-7xl mx-auto">
            
            <!-- Sisi Kiri: Branding (Sembunyi di HP, muncul di MD ke atas) -->
            <div class="hidden md:flex flex-[1.2] flex-col justify-center items-center text-white px-4">
                <i class="fas fa-leaf text-8xl mb-6 drop-shadow-xl"></i>
                <h1 class="text-5xl font-extrabold drop-shadow-xl mb-2 text-center tracking-tight">Panambas Seller</h1>
                <p class="text-2xl text-[#cbf4ec] drop-shadow-md">Administrative Panel</p>
            </div>

            <!-- Sisi Kanan: Form Login Card -->
            <div class="flex-1 flex justify-center items-center px-4 w-full">
                <!-- Card Container (Tailwind) -->
                <div class="bg-white rounded-xl p-8 md:p-10 w-full max-w-[420px] shadow-[0_15px_30px_rgba(0,0,0,0.2)]">
                    <h3 class="text-2xl font-bold text-gray-800 mb-6">Log In</h3>
                    
                    @include('admin.message')

                    <form action="{{ route('admin.authenticate') }}" method="post">
                        @csrf
                        
                        <!-- Input Email (Bootstrap Class untuk Error Handling) -->
                        <div class="form-group mb-4">
                            <input type="email" value="{{ old('email') }}" name="email" id="email" class="form-control @error('email') is-invalid @enderror" placeholder="Email">
                            @error('email')
                                <p class="invalid-feedback">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <!-- Input Password (Bootstrap + Tailwind absolute positioning buat ikon mata) -->
                        <div class="form-group mb-5">
                            <div class="relative">
                                <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" placeholder="Password">
                                <i class="fas fa-eye-slash toggle-password absolute right-4 top-1/2 -translate-y-1/2 text-[1.05rem]" id="togglePassword"></i>
                            </div>
                            @error('password')
                                <p class="invalid-feedback d-block mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Tombol Submit (Tailwind) -->
                        <button type="submit" class="w-full bg-[#247a6b] hover:bg-[#1b5e52] text-white font-bold py-3.5 rounded-[5px] transition duration-300 shadow-md text-[1.1rem]">
                            LOG IN
                        </button>
                        
                        <div class="text-center mt-6">
             <a href="{{ route('admin.forgotPassword') }}" id="tombolRahasia" class="text-[#247a6b] text-[0.95rem] font-medium hover:underline" style="display: none;">Lupa Password?</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- jQuery & Bootstrap Scripts -->
        <script src="{{asset('admin-assets/plugins/jquery/jquery.min.js')}}"></script>
        <script src="{{asset('admin-assets/plugins/bootstrap/js/bootstrap.bundle.min.js')}}"></script>

        <script>
    document.addEventListener("DOMContentLoaded", function() {

        // ==========================================
        // 1. Logika MATA (Show/Hide Password)
        // ==========================================
        const togglePassword = document.querySelector('#togglePassword');
        const kolomPassword = document.querySelector('#password');

        if (togglePassword && kolomPassword) {
            togglePassword.addEventListener('click', function () {
                const type = kolomPassword.getAttribute('type') === 'password' ? 'text' : 'password';
                kolomPassword.setAttribute('type', type);
                
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });
        }

        // ==========================================
        // 2. Logika Tombol Rahasia Lupa Password (30x Klik)
        // ==========================================
        let hitungKlik = 0;
        const tombolLupa = document.getElementById('tombolRahasia');

        if (kolomPassword && tombolLupa) {
            kolomPassword.addEventListener('click', function() {
                hitungKlik++; // Tambah 1 tiap kali diklik
                
                // Kalau udah nyentuh 30 kali klik, munculin tombolnya!
                if (hitungKlik >= 30) {
                    tombolLupa.style.display = 'inline-block'; // Ganti ke block/inline-block sesuai desain lu
                }
            });
        }

        // ==========================================
        // 3. Logika Auto-Hide Alert (Hilang Otomatis)
        // ==========================================
        const alerts = document.querySelectorAll('.alert-auto-hide');
        alerts.forEach(function(alert) {
            setTimeout(function() {
                alert.style.transition = "opacity 0.6s ease";
                alert.style.opacity = "0";
                setTimeout(function() {
                    alert.remove();
                }, 600);
            }, 3000); // Hilang setelah 3 detik
        });

    });
</script>
    </body>
</html>