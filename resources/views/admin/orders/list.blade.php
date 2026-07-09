@extends('admin.layouts.app')

@section('content')
<style>
    /* Racikan Desain Modern & Clean */
    .card-modern {
        border: none;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.03);
    }
    
    /* Modifikasi Tab Navigasi biar nggak kaku */
    .nav-tabs-custom {
        border-bottom: 1px solid #eaeaea;
    }
    .nav-tabs-custom .nav-item {
        margin-bottom: -1px;
    }
    .nav-tabs-custom .nav-link {
        border: none;
        color: #888;
        font-weight: 500;
        padding: 16px 24px;
        transition: all 0.3s ease;
        background: transparent;
        font-size: 0.95rem;
    }
    .nav-tabs-custom .nav-link:hover {
        color: #1d7b67;
    }
    .nav-tabs-custom .nav-link.active {
        color: #1d7b67;
        border-bottom: 3px solid #1d7b67;
        font-weight: 700;
        background: transparent;
    }

    /* Tombol Hijau Senada Sidebar */
    .btn-theme {
        background-color: #1d7b67;
        color: white;
        border: none;
        transition: 0.3s;
        border-radius: 6px;
        font-weight: 500;
    }
    .btn-theme:hover {
        background-color: #155e4e;
        color: white;
        box-shadow: 0 4px 8px rgba(29, 123, 103, 0.2);
    }
    
    .btn-outline-theme {
        color: #1d7b67;
        border: 1px solid #1d7b67;
        background: transparent;
        transition: 0.3s;
        border-radius: 6px;
        font-weight: 500;
    }
    .btn-outline-theme:hover {
        background-color: #1d7b67;
        color: white;
    }

    /* Modif Input & Table */
    .form-control-modern {
        border-radius: 6px;
        border: 1px solid #e2e8f0;
        box-shadow: none;
    }
    .form-control-modern:focus {
        border-color: #1d7b67;
        box-shadow: 0 0 0 0.2rem rgba(29, 123, 103, 0.1);
    }
    
    /* Tabel ala Shopee */
    .table-order { border-collapse: separate; border-spacing: 0; }
    .order-header-row { background-color: #fafafa; border-bottom: 1px solid #eaeaea; }
    .order-detail-row { border-bottom: 1px solid #eaeaea; background-color: #fff; }
    .table-hover tbody .order-detail-row:hover { background-color: #fcfcfc; }
</style>

<section class="content-header">
    <div class="container-fluid my-2">
        <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
                <h1 class="font-weight-bold" style="color: #333;">Pesanan Saya</h1>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        @if(Session::has('success'))
            <div class="alert alert-success alert-dismissible fade show" style="background-color: #e6f4ea; border-color: #cce8d6; color: #1d7b67; border-radius: 8px;">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <i class="icon fas fa-check-circle"></i> {{ Session::get('success') }}
            </div>
        @endif

        <div class="card card-modern">
            <div class="card-header p-0 bg-white" style="border-radius: 8px 8px 0 0;">
                <ul class="nav nav-tabs nav-tabs-custom" id="orderTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link {{ request('status') == '' ? 'active' : '' }}" href="{{ route('orders.index') }}">Semua</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request('status') == 'pending' ? 'active' : '' }}" href="{{ route('orders.index', ['status' => 'pending']) }}">Belum Bayar</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request('status') == 'processing' ? 'active' : '' }}" href="{{ route('orders.index', ['status' => 'processing']) }}">
                            Perlu Dikirim @if(isset($processingCount) && $processingCount > 0)<span class="text-danger font-weight-bold ml-1">({{ $processingCount }})</span>@endif
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request('status') == 'shipped' ? 'active' : '' }}" href="{{ route('orders.index', ['status' => 'shipped']) }}">
                            Dikirim @if(isset($shippedCount) && $shippedCount > 0)<span class="text-danger font-weight-bold ml-1">({{ $shippedCount }})</span>@endif
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request('status') == 'completed' ? 'active' : '' }}" href="{{ route('orders.index', ['status' => 'completed']) }}">Selesai</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ in_array(request('status'), ['cancelled', 'refund_processing', 'refunded']) ? 'active' : '' }}" href="{{ route('orders.index', ['status' => 'refund_processing']) }}">Pengembalian/Pembatalan</a>
                    </li>
                </ul>
            </div>

            <div class="card-body bg-white p-4">
                <form action="{{ route('orders.index') }}" method="get">
                    <input type="hidden" name="status" value="{{ request('status') }}">
                    <div class="row mb-4">
                        <div class="col-md-8 mb-2 mb-md-0">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <select class="form-control form-control-modern" style="border-top-right-radius: 0; border-bottom-right-radius: 0; background-color: #f8fafc;">
                                        <option>No. Pesanan</option>
                                        <option>Nama Pembeli</option>
                                    </select>
                                </div>
                                <input type="text" name="keyword" value="{{ request('keyword') }}" class="form-control form-control-modern" placeholder="Masukkan kata kunci...">
                            </div>
                        </div>
                        <div class="col-md-4 d-flex align-items-center">
                            <button type="submit" class="btn btn-theme px-4">Terapkan</button>
                            <a href="{{ route('orders.index', ['status' => request('status')]) }}" class="btn btn-link text-muted ml-2" style="text-decoration: none;">Reset</a>
                        </div>
                    </div>
                </form>

                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <h6 class="text-muted mb-0 font-weight-normal">{{ $orders->total() ?? 0 }} Pesanan Ditemukan</h6>
                    <a href="{{ route('orders.mass_shipping') }}" class="btn btn-theme btn-sm py-2 px-3">
                        <i class="fas fa-truck mr-1"></i> Pengiriman Massal
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-order w-100 mb-0" style="border: 1px solid #eaeaea; border-radius: 8px;">
                        <thead style="background-color: #f8fafc; color: #555;">
                            <tr>
                                <th class="border-top-0 font-weight-bold py-3 pl-4">Produk</th>
                                <th class="border-top-0 font-weight-bold py-3">Dibayar Pembeli</th>
                                <th class="border-top-0 font-weight-bold py-3">Status</th>
                                <th class="border-top-0 font-weight-bold py-3">Jasa Kirim</th>
                                <th class="border-top-0 font-weight-bold py-3 pr-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $order)
                            @if(!$loop->first)
                            <tr><td colspan="5" style="height: 15px; border: none; padding: 0; background: #fff;"></td></tr>
                            @endif

                            <tr class="order-header-row" style="border: 1px solid #eaeaea;">
                                <td colspan="5" class="py-2 px-4" style="border-top: 1px solid #eaeaea; border-left: 1px solid #eaeaea; border-right: 1px solid #eaeaea;">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle text-white d-flex align-items-center justify-content-center mr-2 shadow-sm" style="width: 28px; height: 28px; background: #94a3b8; font-size: 13px;">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            
                                            <strong style="color: #333; font-size: 0.95rem; margin-right: 15px;">
                                                {{ $order->user->name ?? $order->receiver_name }}
                                            </strong>
                                            
                                            <button onclick="openChatAdmin({{ $order->user_id ?? 0 }}, '{{ addslashes($order->user->name ?? $order->receiver_name) }}')" class="btn btn-sm p-0 m-0 text-success font-weight-bold" style="font-size: 0.85rem;" title="Chat Pembeli">
                                                <i class="fas fa-comments mr-1"></i> Chat
                                            </button>
                                        </div>
                                        <div class="text-muted" style="font-size: 0.85rem;">
                                            No. Pesanan: <strong class="text-dark ml-1">{{ $order->invoice_number }}</strong>
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            <tr class="order-detail-row">
                                <td class="pl-4 py-3" style="border-left: 1px solid #eaeaea; border-bottom: 1px solid #eaeaea;">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-light mr-3 rounded shadow-sm" style="width: 65px; height: 65px; display: flex; align-items: center; justify-content: center; overflow: hidden; border: 1px solid #eaeaea;">
                                            @php 
                                                $relasiBarang = $order->items ?? $order->orderItems ?? collect();
                                                $firstItem = $relasiBarang->first(); 
                                            @endphp
                                            
                                            @if($firstItem && $firstItem->product && $firstItem->product->images->isNotEmpty())
                                                <img src="{{ asset('storage/'.$firstItem->product->images->first()->image_path) }}" style="width: 100%; height: 100%; object-fit: cover;">
                                            @else
                                                <i class="fas fa-image text-muted"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <strong style="color: #333; font-size: 0.95rem;">{{ $firstItem->product_name ?? 'Produk Dihapus' }}</strong>
                                            <div class="text-muted mt-1" style="font-size: 0.85rem;">x{{ $firstItem->qty ?? 1 }}</div>
                                            @if($relasiBarang->count() > 1)
                                                <span class="badge bg-light text-secondary mt-1 border">+ {{ $relasiBarang->count() - 1 }} produk lainnya</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3" style="border-bottom: 1px solid #eaeaea;">
                                    <span style="color: #1d7b67; font-weight: 600;">Rp{{ number_format($order->grand_total, 0, ',', '.') }}</span><br>
                                    <small class="text-muted" style="font-size: 0.75rem;">{{ strtoupper($order->payment_method ?? 'TRANSFER') }}</small>
                                </td>
                                <td class="py-3" style="border-bottom: 1px solid #eaeaea;">
                                    @if($order->status == 'pending')
                                        <div class="text-warning font-weight-bold" style="font-size: 0.9rem;">Menunggu Pembayaran</div>
                                    @elseif($order->status == 'processing')
                                        @if($order->cancel_request_status == 'requested')
                                            <div class="text-danger font-weight-bold" style="font-size: 0.9rem;"><i class="fas fa-exclamation-circle mr-1"></i> Menunggu Konfirmasi Batal</div>
                                        @else
                                            <div class="text-success font-weight-bold" style="font-size: 0.9rem;">Perlu Dikirim</div>
                                        @endif
                                    @elseif($order->status == 'refund_processing')
                                        <div class="text-warning font-weight-bold" style="font-size: 0.9rem; color: #d97706 !important;"><i class="fas fa-exclamation-triangle mr-1"></i> Pengajuan Pengembalian</div>
                                    @elseif($order->status == 'shipped')
                                        <div class="text-primary font-weight-bold" style="font-size: 0.9rem;">Dikirim</div>
                                    @elseif($order->status == 'completed')
                                        <div class="text-success font-weight-bold" style="font-size: 0.9rem;">Selesai</div>
                                    @elseif(in_array($order->status, ['cancelled', 'refunded']))
                                        <div class="text-danger font-weight-bold" style="font-size: 0.9rem;">Dibatalkan/Dikembalikan</div>
                                    @endif
                                </td>

                                <td class="py-3" style="border-bottom: 1px solid #eaeaea;">
                                    @if(!empty($order->shipping_method))
                                        <span style="color: #444; font-weight: 600; font-size: 0.9rem;">{{ $order->shipping_method }}</span><br>
                                        <small class="text-muted" style="font-family: monospace;">Resi: {{ $order->resi_number ?? '-' }}</small>
                                    @else
                                        <span class="text-muted" style="font-size: 0.9rem;">Belum Diatur</span>
                                    @endif
                                </td>

                                <td class="pr-4 py-3 text-center" style="border-right: 1px solid #eaeaea; border-bottom: 1px solid #eaeaea;">
                                    <div class="d-flex flex-column gap-2 align-items-center">
                                        
                                        @if($order->status == 'processing' && $order->cancel_request_status == 'requested')
                                            <a href="{{ route('orders.detail', $order->id) }}" class="btn btn-danger btn-sm border py-1 px-3 mb-1 w-100 font-weight-bold shadow-sm">Pengajuan Pembatalan</a>
                                        @elseif($order->status == 'refund_processing')
                                            <a href="{{ route('orders.detail', $order->id) }}" class="btn btn-warning text-dark btn-sm border py-1 px-3 mb-1 w-100 font-weight-bold shadow-sm"><i class="fas fa-reply mr-1"></i> Pengajuan Pengembalian</a>
                                        @else
                                            <a href="{{ route('orders.detail', $order->id) }}" class="btn btn-light btn-sm border py-1 px-3 mb-1 w-100" style="color: #555;">Lihat Detail</a>
                                        @endif
                                        
                                        @if($order->status == 'shipped' || (!empty($order->shipping_method) && !in_array($order->status, ['cancelled', 'refunded'])))
                                            <a href="{{ url('admin/orders/' . $order->id . '/print-label') }}" target="_blank" class="btn btn-outline-primary btn-sm py-1 px-3 mt-1 w-100" style="border-radius: 6px;">
                                                <i class="fas fa-print mr-1"></i> Cetak Resi
                                            </a>
                                        @endif
                                        
                                        @if($order->status == 'processing' && empty($order->cancel_request_status))
                                            <button onclick="openCancelModal({{ $order->id }}, '{{ $order->invoice_number }}')" class="btn btn-outline-danger btn-sm py-1 px-3 mt-1 w-100" style="border-radius: 6px;">
                                                <i class="fas fa-times mr-1"></i> Batalkan
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="my-4">
                                        <i class="fas fa-box-open fa-3x text-muted mb-3" style="opacity: 0.3;"></i>
                                        <h6 class="text-muted font-weight-normal">Belum ada pesanan di kategori ini.</h6>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="card-footer bg-white border-top-0 pt-0 pb-4 px-4 clearfix" style="border-radius: 0 0 8px 8px;">
                {{ $orders->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="cancelModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border: none; border-radius: 10px; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
            <div class="modal-header bg-light" style="border-bottom: 1px solid #eaeaea;">
                <h5 class="modal-title font-weight-bold text-danger">Batalkan Pesanan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formCancelOrder" method="POST" action="">
                @csrf @method('PUT')
                <div class="modal-body p-4">
                    <p class="mb-4">Apakah Anda yakin ingin membatalkan pesanan <strong id="cancelInvoiceText" class="text-danger"></strong>?</p>
                    
                    <div class="form-group mb-0">
                        <label style="font-weight: 500; color: #444;">Alasan Pembatalan:</label>
                        <select name="cancel_reason" class="form-control form-control-modern" required>
                            <option value="Barang Kosong">Barang Kosong (Otomatis set stok jadi 0)</option>
                            <option value="Dibatalkan sepihak oleh penjual">Dibatalkan Sepihak</option>
                        </select>
                        <small class="text-danger mt-2 d-block">*Perhatian: Memilih opsi "Barang Kosong" akan otomatis mengubah stok produk terkait menjadi 0 di database.</small>
                    </div>
                </div>
                <div class="modal-footer bg-light" style="border-top: 1px solid #eaeaea;">
                    <button type="button" class="btn btn-light border px-4" data-dismiss="modal" style="border-radius: 6px;">Tutup</button>
                    <button type="submit" id="btnConfirmCancel" class="btn btn-danger px-4" style="border-radius: 6px;">Konfirmasi Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('customJs')
<script>
    function openChatAdmin(userId, userName) {
        if(typeof toggleAdminChat === 'function') {
            toggleAdminChat(userId, userName);
        } else {
            alert('Fitur Chat untuk ' + userName + ' ditekan! (Pastikan script widget chat admin sudah di-include di layout admin lu ya Bre)');
        }
    }

    function openCancelModal(orderId, invoiceNumber) {
        var baseUrl = "{{ url('admin/orders') }}";
        $('#formCancelOrder').attr('action', baseUrl + '/' + orderId + '/cancel');
        
        $('#cancelInvoiceText').text(invoiceNumber);
        $('#cancelModal').modal('show');
    }

    $('#formCancelOrder').on('submit', function() {
        var btn = $('#btnConfirmCancel');
        btn.html('<i class="fas fa-spinner fa-spin mr-1"></i> Membatalkan...');
        btn.attr('disabled', true);
    });
</script>
@endsection