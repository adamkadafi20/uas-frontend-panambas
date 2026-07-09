<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="{{ url('admin/dashboard') }}" class="brand-link" style="border-bottom: 1px solid rgba(255,255,255,0.1);">
        <i class="fas fa-seedling ml-3 mr-2" style="color: #a3e635; font-size: 20px;"></i>
        <span class="brand-text font-weight-bold" style="color: #ffffff; letter-spacing: 1px;">Panambas Seller</span>
    </a>
    
    <div class="sidebar">
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                
                <li class="nav-item">
                    <a href="{{ url('admin/dashboard') }}" class="nav-link {{ Route::is('admin.dashboard') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-shopping-bag"></i>
                        <p>
                            Pesanan
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('orders.index') }}" class="nav-link {{ Route::is('orders.index') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Pesanan Saya</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('orders.mass_shipping') }}" class="nav-link {{ Route::is('orders.mass_shipping') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Pengiriman Massal</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-tag"></i>
                        <p>
                            Produk
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ url('admin/products') }}" class="nav-link {{ Request::is('admin/products') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Produk Saya</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('products.create') }}" class="nav-link {{ Route::is('products.create') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Tambah Produk Baru</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item">
                    <a href="{{ route('reviews.index') }}" class="nav-link {{ Route::is('reviews.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-star"></i>
                        <p>Penilaian Produk</p>
                    </a>
                </li>

                {{-- Menu Informasi Saldo - Cuma buat Super Admin --}}
                @if(Auth::check() && Auth::user()->id == 1)
                    <li class="nav-item">
                        <a href="{{ route('admin.saldo.index') }}" class="nav-link {{ Route::is('admin.saldo.index') ? 'active' : '' }} secure-menu">
                            <i class="nav-icon fas fa-wallet"></i>
                            <p>Informasi Saldo</p>
                        </a>
                    </li>
                    @endif
                
                <li class="nav-item">
                    <a href="{{ route('admin.shop.index') }}" class="nav-link {{ Route::is('admin.shop.index') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-store"></i>
                        <p>Analisis Toko</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('banners.index') }}" class="nav-link {{ Route::is('banners.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-paint-roller"></i>
                        <p>Dekorasi Toko</p>
                    </a>
                </li>

                <li class="nav-header"><hr style="border-top: 1px solid #4f6255ff; margin: 0.5rem 0;"></li>
                {{-- Menu User - Cuma buat Super Admin --}}
                @if(Auth::check() && Auth::user()->id == 1)
                <li class="nav-item">
                    <a href="{{ route('users.index') }}" class="nav-link {{ Route::is('users.index') ? 'active' : '' }} secure-menu">
                        <i class="nav-icon fas fa-users"></i>
                        <p>User</p>
                    </a>
                </li>
                @endif
            </ul>
        </nav>
    </div>
</aside>