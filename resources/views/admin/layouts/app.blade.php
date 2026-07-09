<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Panambas Seller :: Administrative Panel</title>
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
        <link rel="stylesheet" href="{{asset ('admin-assets/plugins/fontawesome-free/css/all.min.css')}}">
        <link rel="stylesheet" href="{{asset ('admin-assets/css/adminlte.min.css')}}">
        <link rel="stylesheet" href="{{asset ('admin-assets/plugins/dropzone/min/dropzone.min.css')}}">
        <link rel="stylesheet" href="{{asset ('admin-assets/css/custom.css')}}">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        
        <script src="https://cdn.tailwindcss.com"></script>

        <style>
            /* 1. Warna Background Sidebar Kiri (Hijau Tema Pembeli) */
            .main-sidebar { background-color: #247a6b !important; box-shadow: 0 4px 6px rgba(0,0,0,0.3) !important; }
            
            /* 2. Warna Teks Menu Sidebar */
            .nav-sidebar .nav-item > .nav-link { color: #e6f2f0 !important; }
            
            /* 3. Warna Saat Menu Di-hover (Disorot Mouse) */
            .nav-sidebar .nav-item > .nav-link:hover { background-color: #1f6a5d !important; color: #ffffff !important; }
            
            /* 4. Warna Menu Aktif */
            .nav-pills .nav-link.active, .nav-sidebar .nav-link.active { background-color: #1b5e52 !important; color: #ffffff !important; box-shadow: 0 1px 3px rgba(0,0,0,0.2); }
            
            /* 5. Warna Ikon di Sidebar */
            .nav-sidebar .nav-link i { color: #ffffff !important; }
            
            /* 6. Aksen Header/Navbar Atas */
            .main-header.navbar { border-bottom: 2px solid #247a6b !important; }
            
            /* 7. Chat Widget Scrollbar */
            .custom-scrollbar::-webkit-scrollbar { width: 4px; }
            .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
            .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
            .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        </style>
    </head>
    <body class="hold-transition sidebar-mini">
        <div class="wrapper">
            <nav class="main-header navbar navbar-expand navbar-white navbar-light">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                    </li>                   
                </ul>
                <div class="navbar-nav pl-2"></div>
                
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                            <i class="fas fa-expand-arrows-alt"></i>
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link p-0 pr-3" data-toggle="dropdown" href="#">
                            <img src="{{asset ('admin-assets/img/avatar5.png')}}" class='img-circle elevation-2' width="40" height="40" alt="">
                        </a>
                        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right p-3">
                            <h4 class="h4 mb-0"><strong>{{ Auth::user()->name }}</strong></h4>
                            <div class="mb-3">{{ Auth::user()->email }}</div>
                            <div class="dropdown-divider"></div>
                            
                            @if(Auth::check() && Auth::user()->id == 1)
                            <a href="{{ route('admin.settings') ?? '#' }}" class="dropdown-item secure-menu">
                                <i class="fas fa-store-cog mr-2 text-[#247a6b]"></i> Pengaturan Toko
                            </a>
                            <div class="dropdown-divider"></div>
                            @endif

                            <a href="{{route('admin.logout')}}" class="dropdown-item text-danger">
                                <i class="fas fa-sign-out-alt mr-2"></i> Logout                         
                            </a>                            
                        </div>
                    </li>
                </ul>
            </nav>
            
            @include('admin.layouts.sidebar')

            <div class="content-wrapper">
                @yield('content')
            </div>
            <footer class="main-footer">            
                <strong>Copyright &copy; 2014-{{ date('Y') }} Panambas Seller All rights reserved.</strong>
            </footer>

            <div id="draggableChatWidget" class="fixed right-0 bottom-10 flex flex-col space-y-2 z-[9999]" style="cursor: grab;">
                <div id="chatWidgetBtn" class="bg-white p-3 shadow-[0_4px_15px_rgba(0,0,0,0.15)] rounded-l-md border border-r-0 border-gray-200 hover:bg-gray-50 transition relative group flex items-center justify-center w-12 h-12" style="cursor: grab;">
                    <i class="fas fa-comments text-[#247a6b] text-xl pointer-events-none"></i>
                    <span id="adminGlobalUnreadBadge" class="hidden absolute top-1 right-1 bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full border border-white pointer-events-none">0</span>
                </div>
            </div>

            <div class="modal fade" id="securityModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 400px;">
        <div class="modal-content border-0 shadow-xl" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header border-0 pb-3 pt-3" style="background-color: #247a6b;">
                <h5 class="modal-title text-white font-weight-bold text-sm"><i class="fas fa-shield-alt mr-2"></i> Verifikasi Keamanan</h5>
                <button type="button" class="close text-white opacity-75" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body px-4 pb-4 pt-4 text-center bg-white">
                <div class="mb-3" style="color: #247a6b;">
                    <i class="fas fa-lock fa-3x opacity-25"></i>
                </div>
                <p class="text-gray-600 text-[14px] mb-4">Masukkan <b>Password Keamanan</b> Anda untuk mengakses halaman rahasia ini.</p>
                
                <div class="input-group mb-4 shadow-sm rounded-lg overflow-hidden border border-gray-200">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-gray-50 border-0 text-gray-400"><i class="fas fa-key"></i></span>
                    </div>
                   <input type="password" class="form-control border-0 focus:ring-0 shadow-none bg-gray-50" id="securityPasswordInput" placeholder="Ketik password keamanan..." autocomplete="new-password">
                </div>
                
                <div class="row">
                    <div class="col-6 pr-2">
                        <button type="button" class="btn btn-light btn-block font-weight-bold text-gray-600 border rounded-lg py-2" data-dismiss="modal">Batal</button>
                    </div>
                    <div class="col-6 pl-2">
                        <button type="button" class="btn btn-block font-weight-bold text-white rounded-lg py-2 shadow-sm" style="background-color: #247a6b;" onclick="verifySecurityPassword()">Lanjutkan</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

        </div> <script src="{{asset ('admin-assets/plugins/jquery/jquery.min.js')}}"></script>
        <script src="{{asset ('admin-assets/plugins/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
        <script src="{{asset ('admin-assets/js/adminlte.min.js')}}"></script>
        <script src="{{asset ('admin-assets/plugins/dropzone/min/dropzone.min.js')}}"></script>
        <script src="{{asset ('admin-assets/js/demo.js')}}"></script>

        <script type="text/javascript">
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // ==========================================
            // LOGIKA MODAL KEAMANAN
            // ==========================================
            let targetSecureUrl = '';

            document.querySelectorAll('.secure-menu').forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault(); 
                    targetSecureUrl = this.getAttribute('href');
                    $('#securityModal').modal('show');
                });
            });

           function verifySecurityPassword() {
        let pass = document.getElementById('securityPasswordInput').value;
        if(pass === '') {
            alert('Password keamanan wajib diisi!');
            return;
        }
        
        // Sekarang nembak ke database beneran, bukan bypass lagi!
        $.ajax({
            url: "{{ route('admin.verifySecurity') }}",
            type: "POST",
            data: {
                password: pass,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    // Kalau bener, baru ganti halaman
                    window.location.href = targetSecureUrl; 
                } else {
                    // Kalau salah, keluarin alert dan kosongin kolomnya
                    alert(response.message);
                    document.getElementById('securityPasswordInput').value = '';
                }
            },
            error: function() {
                alert('Terjadi kesalahan pada server!');
            }
        });
    }

            // ==========================================
            // LOGIKA DRAG & DROP CHAT WIDGET
            // ==========================================
            function toggleShopeeChat() {
                const panel = document.getElementById('shopeeChatPanel');
                if (panel) {
                    if (panel.classList.contains('hidden')) {
                        panel.classList.remove('hidden');
                        panel.classList.add('flex');
                    } else {
                        panel.classList.add('hidden');
                        panel.classList.remove('flex');
                    }
                }
            }

            const widgetWrap = document.getElementById('draggableChatWidget');
            const widgetBtn = document.getElementById('chatWidgetBtn');
            
            if (widgetWrap && widgetBtn) {
                let isDraggingWidget = false;
                let startX, startY;

                // --- SENSOR MOUSE (PC) ---
                widgetWrap.onmousedown = function(e) {
                    e.preventDefault(); 
                    isDraggingWidget = false;
                    startX = e.clientX;
                    startY = e.clientY;
                    widgetWrap.style.cursor = "grabbing";
                    widgetBtn.style.cursor = "grabbing";
                    document.onmouseup = closeDragElement;
                    document.onmousemove = elementDrag;
                };

                function elementDrag(e) {
                    e.preventDefault();
                    if (Math.abs(e.clientX - startX) > 3 || Math.abs(e.clientY - startY) > 3) isDraggingWidget = true;
                    
                    let pos1 = startX - e.clientX;
                    let pos2 = startY - e.clientY;
                    startX = e.clientX;
                    startY = e.clientY;

                    widgetWrap.style.top = (widgetWrap.offsetTop - pos2) + "px";
                    widgetWrap.style.left = (widgetWrap.offsetLeft - pos1) + "px";
                    widgetWrap.style.bottom = "auto";
                    widgetWrap.style.right = "auto";
                }

                function closeDragElement() {
                    widgetWrap.style.cursor = "grab";
                    widgetBtn.style.cursor = "grab";
                    document.onmouseup = null;
                    document.onmousemove = null;
                }

                // --- SENSOR JARI (MOBILE) ---
                widgetWrap.addEventListener('touchstart', function(e) {
                    isDraggingWidget = false;
                    startX = e.touches[0].clientX;
                    startY = e.touches[0].clientY;
                }, {passive: false});

                widgetWrap.addEventListener('touchmove', function(e) {
                    e.preventDefault(); 
                    if (Math.abs(e.touches[0].clientX - startX) > 3 || Math.abs(e.touches[0].clientY - startY) > 3) isDraggingWidget = true;
                    
                    let pos1 = startX - e.touches[0].clientX;
                    let pos2 = startY - e.touches[0].clientY;
                    startX = e.touches[0].clientX;
                    startY = e.touches[0].clientY;

                    widgetWrap.style.top = (widgetWrap.offsetTop - pos2) + "px";
                    widgetWrap.style.left = (widgetWrap.offsetLeft - pos1) + "px";
                    widgetWrap.style.bottom = "auto";
                    widgetWrap.style.right = "auto";
                }, {passive: false});

                // --- PENENTU KLIK ---
                widgetBtn.onclick = function(e) {
                    if (isDraggingWidget) {
                        e.preventDefault();
                        return; 
                    }
                    toggleShopeeChat(); 
                };
            }
        </script>

        @yield('customJs')
        @include('admin.layouts.chat-widget')
    </body>
</html>