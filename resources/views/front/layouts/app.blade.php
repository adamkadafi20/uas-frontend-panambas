<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Panambas - Toko Tanaman Hias & Bibit')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #ffffff; }
        .hide-scroll::-webkit-scrollbar { display: none; }
        
        /* Animasi Mega Menu & Search */
        .mega-menu { opacity: 0; visibility: hidden; transform: translateY(10px); transition: all 0.3s ease; }
        .nav-item-group:hover .mega-menu { opacity: 1; visibility: visible; transform: translateY(0); }
        .search-overlay { max-height: 0; overflow: hidden; transition: max-height 0.3s ease-in-out; }
        .search-overlay.active { max-height: 250px; }

        /* Trik Naikkan Widget Chat di Mobile Biar Ga Nabrak Navbar Bawah */
        @media (max-width: 768px) {
            .chat-widget-adjuster > * {
                margin-bottom: 70px !important;
            }
        }
    </style>
    
    @stack('styles')
</head>
<body class="bg-white pb-16 md:pb-0 relative">
    
    @php
        $globalCartCount = 0;
        if(Auth::check() && \Illuminate\Support\Facades\Schema::hasTable('carts')) {
            $globalCartCount = \App\Models\Cart::where('user_id', Auth::id())->count();
        }
    @endphp

    @if(session('success'))
        <div id="toast-success" class="fixed top-24 right-5 z-[9999] flex items-center w-full max-w-xs p-4 space-x-3 text-gray-600 bg-white rounded-md shadow-xl border-l-4 border-[#247a6b]" role="alert">
            <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-white bg-[#247a6b] rounded-full">
                <i class="fas fa-check text-sm"></i>
            </div>
            <div class="text-sm font-medium">{{ session('success') }}</div>
        </div>
        <script>
            // Logika ngilangin notif otomatis setelah 3 detik
            setTimeout(() => {
                const toast = document.getElementById('toast-success');
                if(toast) {
                    toast.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                    toast.style.opacity = '0';
                    toast.style.transform = 'translateX(100%)';
                    setTimeout(() => toast.remove(), 500);
                }
            }, 3000);
        </script>
    @endif

    <div class="sticky top-0 z-[150] w-full flex flex-col shadow-sm">
        
        <div class="bg-[#247a6b] text-white text-center text-[11px] md:text-sm py-2 font-medium tracking-wide">
            <span id="announcement-text" class="transition-opacity duration-500 opacity-100 inline-block">
                Dipercaya oleh 10.000+ pecinta tanaman di Indonesia 🌿
            </span>
        </div>

        <nav class="bg-white border-b border-gray-200 relative">
            <div class="max-w-7xl mx-auto px-4 md:px-8">
                <div class="flex justify-between items-center h-16 md:h-20">
                    <div class="flex items-center">
                        <button class="md:hidden text-gray-800 hover:text-[#247a6b] focus:outline-none mr-4" onclick="toggleMobileMenu()">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                        <a href="{{ route('front.home') }}" class="flex items-center group">
                            <i class="fas fa-seedling text-[#247a6b] text-2xl mr-2 group-hover:rotate-12 transition-transform"></i>
                            <span class="font-bold text-xl md:text-2xl text-gray-900 tracking-tighter uppercase">Panambas</span>
                        </a>
                    </div>
                    
                    <div class="hidden md:flex flex-1 justify-center h-full">
                        <div class="flex space-x-8 lg:space-x-12 h-full">
                            
                            <div class="nav-item-group h-full flex items-center">
                                <a href="{{ route('front.shop') }}" class="text-gray-800 font-bold text-[15px] hover:text-[#247a6b] transition">Semua Produk</a>
                            </div>

                            <div class="nav-item-group h-full flex items-center">
                                <a href="{{ route('front.shop', ['category' => 'Bibit & Benih']) }}" class="text-gray-800 font-medium text-[15px] hover:text-[#247a6b] transition">Bibit & Benih</a>
                                <div class="mega-menu absolute left-0 top-full w-full bg-white shadow-xl border-t border-gray-200 z-50">
                                    <div class="max-w-7xl mx-auto flex h-[380px]">
                                        <div class="w-1/4 py-12 pr-8"><h2 class="text-[26px] font-normal text-gray-500 tracking-wide">Bibit & Benih</h2></div>
                                        <div class="w-1/3 py-12 px-4 flex flex-col gap-4">
                                            <a href="{{ route('front.shop', ['category' => 'Bibit & Benih > Sayuran']) }}" class="group flex items-center justify-between text-[15px] text-gray-800 hover:text-[#247a6b] transition w-4/5">Sayuran <i class="fas fa-chevron-right text-[10px] opacity-0 group-hover:opacity-100 transition"></i></a>
                                            <a href="{{ route('front.shop', ['category' => 'Bibit & Benih > Buah-buahan']) }}" class="group flex items-center justify-between text-[15px] text-gray-800 hover:text-[#247a6b] transition w-4/5">Buah-buahan <i class="fas fa-chevron-right text-[10px] opacity-0 group-hover:opacity-100 transition"></i></a>
                                            <a href="{{ route('front.shop', ['category' => 'Bibit & Benih > Herbal & Rempah']) }}" class="group flex items-center justify-between text-[15px] text-gray-800 hover:text-[#247a6b] transition w-4/5">Herbal & Rempah <i class="fas fa-chevron-right text-[10px] opacity-0 group-hover:opacity-100 transition"></i></a>
                                            <a href="{{ route('front.shop', ['category' => 'Bibit & Benih > Biji Bunga']) }}" class="group flex items-center justify-between text-[15px] text-gray-800 hover:text-[#247a6b] transition w-4/5">Biji Bunga <i class="fas fa-chevron-right text-[10px] opacity-0 group-hover:opacity-100 transition"></i></a>
                                            <a href="{{ route('front.shop', ['category' => 'Bibit & Benih > Pohon Kayu']) }}" class="group flex items-center justify-between text-[15px] text-gray-800 hover:text-[#247a6b] transition w-4/5">Pohon Kayu <i class="fas fa-chevron-right text-[10px] opacity-0 group-hover:opacity-100 transition"></i></a>
                                        </div>
                                        <div class="w-5/12 h-full"><img src="https://images.unsplash.com/photo-1523348837708-15d4a09cfac2?auto=format&fit=crop&w=800&q=80" class="w-full h-full object-cover"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="nav-item-group h-full flex items-center">
                                <a href="{{ route('front.shop', ['category' => 'Tanaman Hias']) }}" class="text-gray-800 font-medium text-[15px] hover:text-[#247a6b] transition">Tanaman Hias</a>
                                <div class="mega-menu absolute left-0 top-full w-full bg-white shadow-xl border-t border-gray-200 z-50">
                                    <div class="max-w-7xl mx-auto flex h-[380px]">
                                        <div class="w-1/4 py-12 pr-8"><h2 class="text-[26px] font-normal text-gray-500 tracking-wide">Tanaman Hias</h2></div>
                                        <div class="w-1/3 py-12 px-4 flex flex-col gap-4">
                                            <a href="{{ route('front.shop', ['category' => 'Tanaman Hias > Tanaman Daun']) }}" class="group flex items-center justify-between text-[15px] text-gray-800 hover:text-[#247a6b] transition w-4/5">Tanaman Daun <i class="fas fa-chevron-right text-[10px] opacity-0 group-hover:opacity-100 transition"></i></a>
                                            <a href="{{ route('front.shop', ['category' => 'Tanaman Hias > Tanaman Bunga']) }}" class="group flex items-center justify-between text-[15px] text-gray-800 hover:text-[#247a6b] transition w-4/5">Tanaman Bunga <i class="fas fa-chevron-right text-[10px] opacity-0 group-hover:opacity-100 transition"></i></a>
                                            <a href="{{ route('front.shop', ['category' => 'Tanaman Hias > Kaktus & Sukulen']) }}" class="group flex items-center justify-between text-[15px] text-gray-800 hover:text-[#247a6b] transition w-4/5">Kaktus & Sukulen <i class="fas fa-chevron-right text-[10px] opacity-0 group-hover:opacity-100 transition"></i></a>
                                            <a href="{{ route('front.shop', ['category' => 'Tanaman Hias > Tanaman Gantung']) }}" class="group flex items-center justify-between text-[15px] text-gray-800 hover:text-[#247a6b] transition w-4/5">Tanaman Gantung <i class="fas fa-chevron-right text-[10px] opacity-0 group-hover:opacity-100 transition"></i></a>
                                            <a href="{{ route('front.shop', ['category' => 'Tanaman Hias > Bonsai']) }}" class="group flex items-center justify-between text-[15px] text-gray-800 hover:text-[#247a6b] transition w-4/5">Bonsai <i class="fas fa-chevron-right text-[10px] opacity-0 group-hover:opacity-100 transition"></i></a>
                                        </div>
                                        <div class="w-5/12 h-full"><img src="https://images.unsplash.com/photo-1485955900006-10f4d324d411?auto=format&fit=crop&w=800&q=80" class="w-full h-full object-cover"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="nav-item-group h-full flex items-center">
                                <a href="{{ route('front.shop', ['category' => 'Media Tanam & Pupuk']) }}" class="text-gray-800 font-medium text-[15px] hover:text-[#247a6b] transition">Media Tanam & Pupuk</a>
                                <div class="mega-menu absolute left-0 top-full w-full bg-white shadow-xl border-t border-gray-200 z-50">
                                    <div class="max-w-7xl mx-auto flex h-[380px]">
                                        <div class="w-1/4 py-12 pr-8"><h2 class="text-[26px] font-normal text-gray-500 tracking-wide leading-tight">Media Tanam <br>& Pupuk</h2></div>
                                        <div class="w-1/3 py-12 px-4 flex flex-col gap-4">
                                            <a href="{{ route('front.shop', ['category' => 'Media Tanam & Pupuk > Tanah & Campuran']) }}" class="group flex items-center justify-between text-[15px] text-gray-800 hover:text-[#247a6b] transition w-4/5">Tanah & Campuran <i class="fas fa-chevron-right text-[10px] opacity-0 group-hover:opacity-100 transition"></i></a>
                                            <a href="{{ route('front.shop', ['category' => 'Media Tanam & Pupuk > Sekam & Cocopeat']) }}" class="group flex items-center justify-between text-[15px] text-gray-800 hover:text-[#247a6b] transition w-4/5">Sekam & Cocopeat <i class="fas fa-chevron-right text-[10px] opacity-0 group-hover:opacity-100 transition"></i></a>
                                            <a href="{{ route('front.shop', ['category' => 'Media Tanam & Pupuk > Pupuk Organik / Kompos']) }}" class="group flex items-center justify-between text-[15px] text-gray-800 hover:text-[#247a6b] transition w-4/5">Pupuk Organik / Kompos <i class="fas fa-chevron-right text-[10px] opacity-0 group-hover:opacity-100 transition"></i></a>
                                            <a href="{{ route('front.shop', ['category' => 'Media Tanam & Pupuk > Pupuk Kimia / NPK']) }}" class="group flex items-center justify-between text-[15px] text-gray-800 hover:text-[#247a6b] transition w-4/5">Pupuk Kimia / NPK <i class="fas fa-chevron-right text-[10px] opacity-0 group-hover:opacity-100 transition"></i></a>
                                            <a href="{{ route('front.shop', ['category' => 'Media Tanam & Pupuk > Vitamin & Hormon Tanaman']) }}" class="group flex items-center justify-between text-[15px] text-gray-800 hover:text-[#247a6b] transition w-4/5">Vitamin & Hormon Tanaman <i class="fas fa-chevron-right text-[10px] opacity-0 group-hover:opacity-100 transition"></i></a>
                                        </div>
                                        <div class="w-5/12 h-full">
                                            <img src="{{ asset('assets/images/pupuk.jpg') }}" alt="Gambar Pupuk" class="w-full h-full object-cover">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="nav-item-group h-full flex items-center">
                                <a href="{{ route('front.shop', ['category' => 'Peralatan Berkebun']) }}" class="text-gray-800 font-medium text-[15px] hover:text-[#247a6b] transition">Peralatan Berkebun</a>
                                <div class="mega-menu absolute left-0 top-full w-full bg-white shadow-xl border-t border-gray-200 z-50">
                                    <div class="max-w-7xl mx-auto flex h-[380px]">
                                        <div class="w-1/4 py-12 pr-8"><h2 class="text-[26px] font-normal text-gray-500 tracking-wide leading-tight">Peralatan <br>Berkebun</h2></div>
                                        <div class="w-1/3 py-12 px-4 flex flex-col gap-4">
                                            <a href="{{ route('front.shop', ['category' => 'Peralatan Berkebun > Pot & Wadah']) }}" class="group flex items-center justify-between text-[15px] text-gray-800 hover:text-[#247a6b] transition w-4/5">Pot & Wadah <i class="fas fa-chevron-right text-[10px] opacity-0 group-hover:opacity-100 transition"></i></a>
                                            <a href="{{ route('front.shop', ['category' => 'Peralatan Berkebun > Alat Tanam & Potong']) }}" class="group flex items-center justify-between text-[15px] text-gray-800 hover:text-[#247a6b] transition w-4/5">Alat Tanam & Potong <i class="fas fa-chevron-right text-[10px] opacity-0 group-hover:opacity-100 transition"></i></a>
                                            <a href="{{ route('front.shop', ['category' => 'Peralatan Berkebun > Penyiram Air']) }}" class="group flex items-center justify-between text-[15px] text-gray-800 hover:text-[#247a6b] transition w-4/5">Penyiram Air <i class="fas fa-chevron-right text-[10px] opacity-0 group-hover:opacity-100 transition"></i></a>
                                            <a href="{{ route('front.shop', ['category' => 'Peralatan Berkebun > Pembasmi Hama / Pestisida']) }}" class="group flex items-center justify-between text-[15px] text-gray-800 hover:text-[#247a6b] transition w-4/5">Pembasmi Hama / Pestisida <i class="fas fa-chevron-right text-[10px] opacity-0 group-hover:opacity-100 transition"></i></a>
                                        </div>
                                        <div class="w-5/12 h-full">
                                            <img src="{{ asset('assets/images/Gardening Tools.jpg') }}" alt="Gambar Peralatan" class="w-full h-full object-cover">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center space-x-6 md:space-x-8">
                        <button onclick="toggleSearch()" class="text-gray-800 hover:text-[#247a6b] transition">
                            <i class="fas fa-search text-[19px]"></i>
                        </button>
                        
                        @guest
                            <a href="{{ route('login') }}" class="text-gray-800 hover:text-[#247a6b] transition">
                                <i class="far fa-user text-[19px]"></i>
                            </a>
                        @else
                            <div class="relative group hidden md:block pb-2 pt-2">
                                <div class="text-[#247a6b] cursor-pointer flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-full bg-[#f0f7f5] border border-[#247a6b] flex items-center justify-center overflow-hidden">
                                        <i class="fas fa-user text-sm"></i>
                                    </div>
                                    <span class="text-sm font-medium text-gray-700 group-hover:text-[#247a6b]">{{ explode(' ', Auth::user()->name)[0] }}</span>
                                </div>
                                
                                <div class="absolute right-0 top-[100%] w-48 bg-white rounded-md shadow-lg border border-gray-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50 overflow-hidden">
                                    <a href="{{ route('user.profile') }}" class="block px-5 py-3 text-sm text-gray-700 hover:bg-[#f0f7f5] hover:text-[#247a6b] transition">
                                        <i class="far fa-user-circle w-5"></i> Akun Saya
                                    </a>
                                    <a href="{{ route('user.orders') }}" class="block px-5 py-3 text-sm text-gray-700 hover:bg-[#f0f7f5] hover:text-[#247a6b] transition">
                                        <i class="fas fa-clipboard-list w-5"></i> Pesanan Saya
                                    </a>
                                    <hr class="border-gray-100">
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="w-full text-left px-5 py-3 text-sm text-red-500 font-medium hover:bg-red-50 transition">
                                            <i class="fas fa-sign-out-alt w-5"></i> Log Out
                                        </button>
                                    </form>
                                </div>
                            </div>
                            
                            <div class="relative md:hidden flex items-center">
                                <button onclick="toggleMobileUserMenu()" class="text-gray-800 hover:text-[#247a6b] transition focus:outline-none">
                                    <i class="fas fa-user-check text-[19px]"></i>
                                </button>
                                <div id="mobileUserMenu" class="absolute top-[150%] right-[-10px] w-48 bg-white rounded-md shadow-[0_5px_15px_rgba(0,0,0,0.1)] border border-gray-100 opacity-0 invisible transition-all duration-300 z-[200] overflow-hidden">
                                    <a href="{{ route('user.profile') }}" class="block px-5 py-3 text-sm text-gray-700 hover:bg-[#f0f7f5] hover:text-[#247a6b] transition border-b border-gray-50">
                                        <i class="far fa-user-circle w-5"></i> Akun Saya
                                    </a>
                                    <a href="{{ route('user.orders') }}" class="block px-5 py-3 text-sm text-gray-700 hover:bg-[#f0f7f5] hover:text-[#247a6b] transition border-b border-gray-50">
                                        <i class="fas fa-clipboard-list w-5"></i> Pesanan Saya
                                    </a>
                                    <form id="logout-form-mobile" action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="button" onclick="if(confirm('Yakin mau log out dari Panambas, Bre?')) document.getElementById('logout-form-mobile').submit();" class="w-full text-left px-5 py-3 text-sm text-red-500 font-medium hover:bg-red-50 transition">
                                            <i class="fas fa-sign-out-alt w-5"></i> Log Out
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endguest
                        
                        <button onclick="toggleCart()" class="text-gray-800 hover:text-[#247a6b] transition relative focus:outline-none">
                            <i class="fas fa-shopping-bag text-[19px]"></i>
                            <span class="cart-badge absolute -top-1.5 -right-2.5 bg-[#247a6b] text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full">{{ $globalCartCount }}</span>
                        </button>
                        
                    </div>
                </div>
            </div>

            <div id="searchBarOverlay" class="search-overlay bg-[#fafafa] border-t border-gray-200 absolute w-full left-0 z-40 shadow-inner">
                <div class="max-w-4xl mx-auto px-4 py-8 md:py-12 relative">
                    <form action="{{ route('front.shop') }}" method="GET" class="flex items-stretch bg-white border border-gray-300 w-full max-w-3xl mx-auto focus-within:border-[#247a6b] transition-colors shadow-sm h-12 md:h-14">
                        <input type="text" name="search" class="w-full text-base md:text-lg text-gray-700 placeholder-[#a3b1c6] px-5 focus:outline-none bg-transparent" placeholder="Cari nama tanaman, bibit, pot..." autofocus>
                        <button type="submit" class="bg-[#247a6b] hover:bg-[#1b5e52] text-white px-6 md:px-8 transition-colors flex items-center justify-center">
                            <i class="fas fa-search text-lg md:text-xl"></i>
                        </button>
                    </form>
                </div>
            </div>
        </nav>
    </div>
    <div id="mobileMenu" class="fixed inset-0 bg-white z-[200] hidden flex-col transition-all duration-300">
        <div class="flex justify-between items-center p-5 border-b border-gray-100 bg-gray-50 shadow-sm">
            <span class="font-bold text-lg uppercase tracking-wider text-[#247a6b]">Kategori Produk</span>
            <button onclick="toggleMobileMenu()" class="text-gray-500 hover:text-red-500 text-2xl transition focus:outline-none">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="flex-1 overflow-y-auto px-4 py-2 flex flex-col">
            
            <a href="{{ route('front.shop') }}" class="py-4 border-b border-gray-100 text-base font-bold text-gray-800 hover:text-[#247a6b] flex items-center">
                <i class="fas fa-th-large w-6 text-[#247a6b] mr-2"></i> Semua Produk
            </a>

            <div class="border-b border-gray-100">
                <button onclick="toggleSubmenu('sub-bibit')" class="w-full flex justify-between items-center py-4 text-base font-bold text-gray-800 hover:text-[#247a6b] focus:outline-none">
                    <span class="flex items-center"><i class="fas fa-seedling w-6 text-[#247a6b] mr-2"></i> Bibit & Benih</span>
                    <i id="icon-sub-bibit" class="fas fa-chevron-down text-sm text-gray-400 transition-transform duration-300"></i>
                </button>
                <div id="sub-bibit" class="hidden flex-col pl-9 pb-4 space-y-3">
                    <a href="{{ route('front.shop', ['category' => 'Bibit & Benih']) }}" class="text-[13px] font-bold text-[#247a6b]">Lihat Semua Bibit & Benih</a>
                    <a href="{{ route('front.shop', ['category' => 'Bibit & Benih > Sayuran']) }}" class="text-[13px] text-gray-600 hover:text-[#247a6b]">Sayuran</a>
                    <a href="{{ route('front.shop', ['category' => 'Bibit & Benih > Buah-buahan']) }}" class="text-[13px] text-gray-600 hover:text-[#247a6b]">Buah-buahan</a>
                    <a href="{{ route('front.shop', ['category' => 'Bibit & Benih > Herbal & Rempah']) }}" class="text-[13px] text-gray-600 hover:text-[#247a6b]">Herbal & Rempah</a>
                    <a href="{{ route('front.shop', ['category' => 'Bibit & Benih > Biji Bunga']) }}" class="text-[13px] text-gray-600 hover:text-[#247a6b]">Biji Bunga</a>
                    <a href="{{ route('front.shop', ['category' => 'Bibit & Benih > Pohon Kayu']) }}" class="text-[13px] text-gray-600 hover:text-[#247a6b]">Pohon Kayu</a>
                </div>
            </div>

            <div class="border-b border-gray-100">
                <button onclick="toggleSubmenu('sub-hias')" class="w-full flex justify-between items-center py-4 text-base font-bold text-gray-800 hover:text-[#247a6b] focus:outline-none">
                    <span class="flex items-center"><i class="fas fa-leaf w-6 text-[#247a6b] mr-2"></i> Tanaman Hias</span>
                    <i id="icon-sub-hias" class="fas fa-chevron-down text-sm text-gray-400 transition-transform duration-300"></i>
                </button>
                <div id="sub-hias" class="hidden flex-col pl-9 pb-4 space-y-3">
                    <a href="{{ route('front.shop', ['category' => 'Tanaman Hias']) }}" class="text-[13px] font-bold text-[#247a6b]">Lihat Semua Tanaman Hias</a>
                    <a href="{{ route('front.shop', ['category' => 'Tanaman Hias > Tanaman Daun']) }}" class="text-[13px] text-gray-600 hover:text-[#247a6b]">Tanaman Daun</a>
                    <a href="{{ route('front.shop', ['category' => 'Tanaman Hias > Tanaman Bunga']) }}" class="text-[13px] text-gray-600 hover:text-[#247a6b]">Tanaman Bunga</a>
                    <a href="{{ route('front.shop', ['category' => 'Tanaman Hias > Kaktus & Sukulen']) }}" class="text-[13px] text-gray-600 hover:text-[#247a6b]">Kaktus & Sukulen</a>
                    <a href="{{ route('front.shop', ['category' => 'Tanaman Hias > Tanaman Gantung']) }}" class="text-[13px] text-gray-600 hover:text-[#247a6b]">Tanaman Gantung</a>
                    <a href="{{ route('front.shop', ['category' => 'Tanaman Hias > Bonsai']) }}" class="text-[13px] text-gray-600 hover:text-[#247a6b]">Bonsai</a>
                </div>
            </div>

            <div class="border-b border-gray-100">
                <button onclick="toggleSubmenu('sub-media')" class="w-full flex justify-between items-center py-4 text-base font-bold text-gray-800 hover:text-[#247a6b] focus:outline-none text-left">
                    <span class="flex items-center"><i class="fas fa-sack-xmark w-6 text-[#247a6b] mr-2"></i> Media Tanam & Pupuk</span>
                    <i id="icon-sub-media" class="fas fa-chevron-down text-sm text-gray-400 transition-transform duration-300"></i>
                </button>
                <div id="sub-media" class="hidden flex-col pl-9 pb-4 space-y-3">
                    <a href="{{ route('front.shop', ['category' => 'Media Tanam & Pupuk']) }}" class="text-[13px] font-bold text-[#247a6b]">Lihat Semua Media & Pupuk</a>
                    <a href="{{ route('front.shop', ['category' => 'Media Tanam & Pupuk > Tanah & Campuran']) }}" class="text-[13px] text-gray-600 hover:text-[#247a6b]">Tanah & Campuran</a>
                    <a href="{{ route('front.shop', ['category' => 'Media Tanam & Pupuk > Sekam & Cocopeat']) }}" class="text-[13px] text-gray-600 hover:text-[#247a6b]">Sekam & Cocopeat</a>
                    <a href="{{ route('front.shop', ['category' => 'Media Tanam & Pupuk > Pupuk Organik / Kompos']) }}" class="text-[13px] text-gray-600 hover:text-[#247a6b]">Pupuk Organik / Kompos</a>
                    <a href="{{ route('front.shop', ['category' => 'Media Tanam & Pupuk > Pupuk Kimia / NPK']) }}" class="text-[13px] text-gray-600 hover:text-[#247a6b]">Pupuk Kimia / NPK</a>
                    <a href="{{ route('front.shop', ['category' => 'Media Tanam & Pupuk > Vitamin & Hormon Tanaman']) }}" class="text-[13px] text-gray-600 hover:text-[#247a6b]">Vitamin & Hormon</a>
                </div>
            </div>

            <div class="border-b border-gray-100">
                <button onclick="toggleSubmenu('sub-alat')" class="w-full flex justify-between items-center py-4 text-base font-bold text-gray-800 hover:text-[#247a6b] focus:outline-none">
                    <span class="flex items-center"><i class="fas fa-tools w-6 text-[#247a6b] mr-2"></i> Peralatan Berkebun</span>
                    <i id="icon-sub-alat" class="fas fa-chevron-down text-sm text-gray-400 transition-transform duration-300"></i>
                </button>
                <div id="sub-alat" class="hidden flex-col pl-9 pb-4 space-y-3">
                    <a href="{{ route('front.shop', ['category' => 'Peralatan Berkebun']) }}" class="text-[13px] font-bold text-[#247a6b]">Lihat Semua Peralatan</a>
                    <a href="{{ route('front.shop', ['category' => 'Peralatan Berkebun > Pot & Wadah']) }}" class="text-[13px] text-gray-600 hover:text-[#247a6b]">Pot & Wadah</a>
                    <a href="{{ route('front.shop', ['category' => 'Peralatan Berkebun > Alat Tanam & Potong']) }}" class="text-[13px] text-gray-600 hover:text-[#247a6b]">Alat Tanam & Potong</a>
                    <a href="{{ route('front.shop', ['category' => 'Peralatan Berkebun > Penyiram Air']) }}" class="text-[13px] text-gray-600 hover:text-[#247a6b]">Penyiram Air</a>
                    <a href="{{ route('front.shop', ['category' => 'Peralatan Berkebun > Pembasmi Hama / Pestisida']) }}" class="text-[13px] text-gray-600 hover:text-[#247a6b]">Pembasmi Hama / Pestisida</a>
                </div>
            </div>
            
        </div>
    </div>
    @yield('content')

    <footer class="bg-gray-900 text-white pt-16 pb-8 border-t-4 border-[#247a6b] mt-auto">
        <div class="max-w-7xl mx-auto px-4 md:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-12 mb-12">
                
                <div>
                    <h3 class="text-3xl font-extrabold mb-2 tracking-widest text-white uppercase">PANAMBAS</h3>
                    <p class="text-gray-400 text-sm mb-6">Trusted by 10.000+ happy plant lovers.</p>
                    
                    <div class="w-full h-48 rounded-lg overflow-hidden shadow-lg border border-gray-700">
                        <iframe 
                            src="https://maps.google.com/maps?q=Reni%20Jaya,%20Blok%20J-2,%20No.%202,%20Rt.008,%20Rw.006,%20Pondok%20Benda,%20Pamulang,%20Kota%20Tangerang%20Selatan,%20Banten,%2015416&t=&z=15&ie=UTF8&iwloc=&output=embed" 
                            width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                    </div>
                </div>

                <div>
                    <h4 class="text-lg font-bold mb-6 uppercase tracking-wider border-b border-gray-700 pb-2">Contact Us</h4>
                    <ul class="space-y-5 text-gray-300 text-sm">
                        <li class="flex items-start gap-3">
                            <i class="fas fa-map-marker-alt mt-1 text-[#247a6b] text-lg"></i>
                            <div class="leading-relaxed">
                                <span class="block font-semibold text-white mb-1">Office:</span>
                                Reni Jaya, Blok J-2, No. 2, Rt.008, Rw.006, Pondok Benda, Pamulang, Kota Tangerang Selatan, Banten, 15416.<br><br>
                                <span class="block font-semibold text-white mb-1">Factory:</span>
                                Pangandaran District, West Java.<br><br>
                                <span class="block font-semibold text-white mb-1">Plantation:</span>
                                Bogor, West Java.
                            </div>
                        </li>
                        <li class="flex items-center gap-3">
                            <i class="fas fa-envelope text-[#247a6b] text-lg"></i>
                            <a href="mailto:marketing@panambas.com" class="hover:text-white transition-colors duration-300">marketing@panambas.com</a>
                        </li>
                        <li class="flex items-center gap-3">
                            <i class="fas fa-phone-alt text-[#247a6b] text-lg"></i>
                            <a href="tel:+6285952469007" class="hover:text-white transition-colors duration-300">(+62) 85952469007</a>
                        </li>
                        <li class="flex items-center gap-3">
                            <i class="fas fa-clock text-[#247a6b] text-lg"></i>
                            <span>Mon - Fri 8 am - 5 pm (GMT+7)</span>
                        </li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-lg font-bold mb-6 uppercase tracking-wider border-b border-gray-700 pb-2">Follow Us</h4>
                    <p class="text-gray-400 text-sm mb-6 leading-relaxed">
                        Dapatkan tips perawatan tanaman dan info promo terbaru dengan mengikuti sosial media kami.
                    </p>
                    <div class="flex gap-4">
                        <a href="https://www.instagram.com/ornamental_plants_panambas?igsh=dGRiNDUzandod3J2" target="_blank" class="w-12 h-12 rounded-full bg-gray-800 flex items-center justify-center hover:bg-[#E4405F] hover:-translate-y-1 transition-all duration-300 text-xl shadow-lg">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="https://www.facebook.com/people/Pangestu-Anam-Bersama/100095699914967/" target="_blank" class="w-12 h-12 rounded-full bg-gray-800 flex items-center justify-center hover:bg-[#1877F2] hover:-translate-y-1 transition-all duration-300 text-xl shadow-lg">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="https://wa.me/6285952469007" target="_blank" class="w-12 h-12 rounded-full bg-gray-800 flex items-center justify-center hover:bg-[#25D366] hover:-translate-y-1 transition-all duration-300 text-xl shadow-lg">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                    </div>
                </div>

            </div>

            <div class="border-t border-gray-800 pt-8 flex flex-col md:flex-row justify-between items-center text-xs text-gray-500">
                <p>© {{ date('Y') }} PT. Pangestu Anam Bersama. All rights reserved.</p>
                <p class="mt-3 md:mt-0">PopularFX Theme | Crafted for Plant Lovers</p>
            </div>
        </div>
    </footer>

    <div class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 flex justify-around px-2 py-3 z-[100]">
        <a href="{{ route('front.home') }}" class="flex flex-col items-center text-gray-400 hover:text-gray-900 w-1/3">
            <i class="fas fa-home text-[20px] mb-1"></i>
        </a>
        <button onclick="toggleSearch()" class="flex flex-col items-center text-gray-400 hover:text-gray-900 w-1/3">
            <i class="fas fa-search text-[20px] mb-1"></i>
        </button>
        <button onclick="toggleCart()" class="text-gray-800 hover:text-[#247a6b] transition relative focus:outline-none w-1/3 flex flex-col items-center">
            <div class="relative">
                <i class="fas fa-shopping-bag text-[20px] mb-1"></i>
                <span class="cart-badge absolute -top-1.5 -right-2.5 bg-[#247a6b] text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full">{{ $globalCartCount }}</span>
            </div>
        </button>
    </div>

    <script>
        // 1. Fungsi buat Search Bar
        function toggleSearch() {
            const overlay = document.getElementById('searchBarOverlay');
            overlay.classList.toggle('active');
            if(overlay.classList.contains('active')) {
                setTimeout(() => { overlay.querySelector('input').focus(); }, 100);
            }
        }

        // 2. Fungsi buat Menu HP Utama
        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            menu.classList.toggle('hidden');
            menu.classList.toggle('flex');
        }

        // 3. FUNGSI ACCORDION SUBMENU HP (BARU)
        function toggleSubmenu(id) {
            const submenu = document.getElementById(id);
            const icon = document.getElementById('icon-' + id);
            
            if (submenu.classList.contains('hidden')) {
                submenu.classList.remove('hidden');
                submenu.classList.add('flex');
                icon.classList.add('rotate-180');
            } else {
                submenu.classList.add('hidden');
                submenu.classList.remove('flex');
                icon.classList.remove('rotate-180');
            }
        }

        // 4. Fungsi buat buka tutup menu user di HP
        function toggleMobileUserMenu() {
            const menu = document.getElementById('mobileUserMenu');
            menu.classList.toggle('opacity-0');
            menu.classList.toggle('invisible');
        }

        // 5. Biar klik sembarang tempat, menu user HP nutup otomatis
        document.addEventListener('click', function(event) {
            const menu = document.getElementById('mobileUserMenu');
            const button = event.target.closest('button[onclick="toggleMobileUserMenu()"]');
            
            if (menu && !menu.classList.contains('invisible') && !button && !menu.contains(event.target)) {
                menu.classList.add('opacity-0');
                menu.classList.add('invisible');
            }
        });

        // 6. Fungsi Buka/Tutup Sidebar Keranjang
        function toggleCart() {
            const sidebar = document.getElementById('cartSidebar');
            const overlay = document.getElementById('cartSidebarOverlay');
            
            if (sidebar.classList.contains('translate-x-full')) {
                sidebar.classList.remove('translate-x-full');
                overlay.classList.remove('hidden');
                setTimeout(() => overlay.classList.remove('opacity-0'), 10);
                document.body.style.overflow = 'hidden'; 
            } else {
                sidebar.classList.add('translate-x-full');
                overlay.classList.add('opacity-0');
                setTimeout(() => overlay.classList.add('hidden'), 300);
                document.body.style.overflow = 'auto'; 
            }
        }

        // 7. Fungsi Hapus Barang dari Keranjang
        function removeCartItem(cartId) {
            if(!confirm('Yakin mau hapus barang ini dari keranjang, Bre?')) return;
            
            fetch('{{ route('cart.remove') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ cart_id: cartId })
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Gagal menghapus barang.');
                }
            })
            .catch(error => console.error('Error:', error));
        }

        // 8. Fungsi Tambah Barang Rekomendasi (Direct)
        function addToCartDirect(productId) {
            fetch('{{ route('cart.add') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ 
                    product_id: productId, 
                    qty: 1,
                    variation_id: null 
                })
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    window.location.reload(); 
                } else {
                    alert('Gagal menambah produk.');
                }
            })
            .catch(error => console.error('Error:', error));
        }

        // 9. Fungsi Top Announcement Bar
        document.addEventListener("DOMContentLoaded", function() {
            const textElement = document.getElementById('announcement-text');
            
            if (textElement) {
                const messages = [
                    "Dipercaya oleh 10.000+ pecinta tanaman di Indonesia 🌿",
                    "Gratis Ongkir untuk pesanan di atas Rp 500.000 🚚",
                    "Garansi 100% tanaman segar sampai di depan rumahmu ✨",
                    "Spesial! Diskon 10% untuk pelanggan baru toko kami 🎉"
                ];

                let currentIndex = 0;

                setInterval(() => {
                    textElement.style.opacity = '0';
                    
                    setTimeout(() => {
                        currentIndex = (currentIndex + 1) % messages.length;
                        textElement.innerHTML = messages[currentIndex];
                        textElement.style.opacity = '1';
                    }, 500); 

                }, 4000); 
            }
        });
    </script>

    @stack('scripts')
    @include('front.layouts.cart-sidebar')
    
    <div class="chat-widget-adjuster">
        @include('front.layouts.chat-widget')
    </div>
</body>
</html>