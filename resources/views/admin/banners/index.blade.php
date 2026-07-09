@extends('admin.layouts.app')

@section('content')
<section class="content-header">					
    <div class="container-fluid my-2">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Dekorasi Toko (Banner & Video Promo)</h1>
                <p class="text-muted">Kelola banner slider slider halaman depan toko versi HP & Laptop.</p>
            </div>
            <div class="col-sm-6 text-right">
                <a href="{{ route('banners.create') }}" class="btn btn-success" style="background-color: #247a6b; border: none;">
                    <i class="fas fa-plus mr-1"></i> Tambah Banner Baru
                </a>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm mb-3">
                <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
            </div>
        @endif

        <div class="card border-0 shadow-sm" style="border-radius: 8px;">
            <div class="card-body table-responsive p-0">								
                <table class="table table-hover text-nowrap align-middle mb-0 text-center">
                    <thead style="background-color: #fafafa; color: #666;">
                        <tr>
                            <th width="80">ID</th>
                            <th width="250">Preview Media</th>
                            <th>Nama / Judul Promo</th>
                            <th>Link Tujuan</th>
                            <th width="120">Status</th>
                            <th width="100">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($banners as $banner)
                            <tr>
                                <td class="align-middle">{{ $banner->id }}</td>
                                <td class="align-middle p-3">
                                    <div class="border rounded bg-light overflow-hidden mx-auto shadow-sm" style="width: 180px; height: 90px; flex-shrink: 0;">
                                        @php
                                            $extension = pathinfo($banner->image_path, PATHINFO_EXTENSION);
                                        @endphp
                                        
                                        @if($extension == 'mp4')
                                            <video src="{{ asset('storage/' . $banner->image_path) }}" class="w-100 h-100" style="object-fit: cover;" muted autoplay loop></video>
                                        @else
                                            <img src="{{ asset('storage/' . $banner->image_path) }}" class="w-100 h-100" style="object-fit: cover;">
                                        @endif
                                    </div>
                                </td>
                                <td class="align-middle font-weight-bold text-gray-800">{{ $banner->title }}</td>
                                <td class="align-middle text-muted text-sm">
                                    {{ $banner->link ? $banner->link : 'Tidak ada link internal' }}
                                </td>
                                <td class="align-middle">
                                    @if($banner->status == 1)
                                        <span class="badge badge-success px-3 py-2" style="background-color: #d1e7dd; color: #0f5132; border-radius: 4px;">Aktif</span>
                                    @else
                                        <span class="badge badge-danger px-3 py-2" style="background-color: #f8d7da; color: #842029; border-radius: 4px;">Non-Aktif</span>
                                    @endif
                                </td>
                                <td class="align-middle">
                                    <form action="{{ route('banners.destroy', $banner->id) }}" method="POST" onsubmit="return confirm('Yakin mau hapus banner dekorasi ini bre?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm rounded-circle" style="width: 36px; height: 36px; padding: 0;">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fas fa-paint-roller fa-3x mb-3 opacity-30"></i>
                                    <p class="mb-0">Belum ada banner dekorasi promo yang di-upload nih Bre!</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
@endsection