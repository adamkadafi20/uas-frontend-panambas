@extends('admin.layouts.app')

@section('content')
<style>
    .btn-custom { background-color: #247a6b; color: white; border: none; transition: all 0.3s; }
    .btn-custom:hover { background-color: #1b5e52; color: white; }
    .table-hover tbody tr:hover { background-color: #f0f7f5; }
    .badge-seller { background-color: #e6f2f0; color: #247a6b; border: 1px solid #247a6b; }
    .badge-admin { background-color: #fff5f5; color: #c53030; border: 1px solid #c53030; }
</style>

<section class="content-header">
    <div class="container-fluid my-2">
        <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
                <h2 class="font-weight-bold text-dark m-0"><i class="fas fa-users mr-2 text-[#247a6b]"></i> Daftar Pengguna Toko</h2>
            </div>
            <div class="col-sm-6 text-right">
                <a href="{{ route('users.create') }}" class="btn btn-custom font-weight-bold shadow-sm rounded px-4 py-2">
                    <i class="fas fa-plus-circle mr-2"></i> Tambah Akun Baru
                </a>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        @if(Session::has('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert" style="background-color: #e6f2f0; color: #1b5e52;">
                <i class="fas fa-check-circle mr-2"></i> {{ Session::get('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
        
        <div class="card shadow-sm border-0" style="border-radius: 10px; overflow: hidden;">
            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap mb-0 align-middle">
                    <thead style="background-color: #f8f9fa;">
                        <tr>
                            <th width="60" class="text-center text-muted border-0">ID</th>
                            <th class="text-muted border-0">Alamat Email (Akses Login)</th>
                            <th class="text-center text-muted border-0">Peran (Role)</th>
                            <th width="120" class="text-center text-muted border-0">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($users->isNotEmpty())
                            @foreach ($users as $user)
                            <tr>
                                <td class="text-center text-muted align-middle">{{ $user->id }}</td>
                                <td class="align-middle">
                                    <div class="d-flex align-items-center py-1">
                                        <div class="bg-light rounded-circle d-flex justify-content-center align-items-center mr-3 border" style="width: 40px; height: 40px;">
                                            <i class="fas fa-user text-secondary"></i>
                                        </div>
                                        <span class="font-weight-bold text-dark" style="font-size: 15px;">{{ $user->email }}</span>
                                    </div>
                                </td>
                                <td class="text-center align-middle">
                                    @if($user->role == 'admin')
                                        <span class="badge badge-admin px-3 py-2 rounded-pill"><i class="fas fa-crown mr-1"></i> Super Admin</span>
                                    @else
                                        <span class="badge badge-seller px-3 py-2 rounded-pill"><i class="fas fa-store mr-1"></i> Seller</span>
                                    @endif
                                </td>
                                <td class="text-center align-middle">
                                    <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-light text-warning shadow-sm mx-1 rounded-circle pt-2" style="width: 32px; height: 32px;" title="Edit Password">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <form action="{{ route('users.delete', $user->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('delete')
                                        <button type="submit" class="btn btn-sm btn-light text-danger shadow-sm mx-1 rounded-circle" style="width: 32px; height: 32px;" title="Hapus Akun" onclick="return confirm('Yakin mau hapus akun seller ini, Bre?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="4" class="text-center py-5">
                                    <i class="fas fa-users-slash fa-3x text-muted mb-3 opacity-50"></i>
                                    <h5 class="text-muted font-weight-normal">Belum ada data seller tambahan.</h5>
                                    <p class="text-muted text-sm">Klik tombol "Tambah Akun Baru" di atas untuk membuat.</p>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            
            <div class="card-footer bg-white border-top clearfix py-3">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</section>
@endsection