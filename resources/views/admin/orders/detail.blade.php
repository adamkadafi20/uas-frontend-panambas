@extends('admin.layouts.app')

@section('content')
<style>
    /* DESAIN FRESH & CLEAN (Anti-Shopee) */
    .fresh-card { background: #ffffff; border: none; border-radius: 16px; box-shadow: 0 4px 24px rgba(0,0,0,0.04); padding: 24px; margin-bottom: 24px; }
    .text-theme { color: #1d7b67; }
    .bg-theme { background-color: #1d7b67; }
    .bg-theme-soft { background-color: #f0fdf4; color: #16a34a; }
    .status-banner { border-radius: 16px; padding: 24px; display: flex; align-items: center; gap: 20px; }
    .status-icon { width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; flex-shrink: 0; }
    .status-processing { background: #fffbeb; color: #b45309; border: 1px solid #fef3c7; }
    .status-processing .status-icon { background: #fde68a; color: #d97706; }
    .status-shipped { background: #eff6ff; color: #1e40af; border: 1px solid #dbeafe; }
    .status-shipped .status-icon { background: #bfdbfe; color: #2563eb; }
    .status-completed { background: #f0fdf4; color: #166534; border: 1px solid #dcfce7; }
    .status-completed .status-icon { background: #bbf7d0; color: #16a34a; }
    .tracking-roadmap { position: relative; padding-left: 28px; margin-top: 10px; }
    .tracking-roadmap::before { content: ''; position: absolute; left: 6px; top: 8px; bottom: 8px; width: 2px; background: #e2e8f0; border-radius: 2px; }
    .track-step { position: relative; margin-bottom: 24px; }
    .track-step:last-child { margin-bottom: 0; }
    .track-dot { position: absolute; left: -28px; top: 4px; width: 14px; height: 14px; border-radius: 50%; background: #cbd5e1; border: 3px solid #fff; box-shadow: 0 0 0 1px #e2e8f0; }
    .track-step.active .track-dot { background: #1d7b67; box-shadow: 0 0 0 2px #1d7b67; }
    .btn-fresh { background: #1d7b67; color: #fff; font-weight: 600; border-radius: 10px; padding: 10px 20px; transition: 0.3s; }
    .btn-fresh:hover { background: #155e4e; color: #fff; transform: translateY(-1px); }
    .badge-internal { background: #f1f5f9; color: #475569; padding: 6px 12px; border-radius: 8px; font-weight: 600; font-size: 0.8rem; }
    .btn-copy { background: #e2e8f0; color: #475569; border: none; border-radius: 6px; padding: 4px 10px; font-size: 0.8rem; transition: 0.2s; }
    .btn-copy:hover { background: #cbd5e1; color: #1e293b; }
</style>

<section class="content-header pt-4">
    <div class="container-fluid">
        <a href="{{ route('orders.index') }}" class="text-muted text-decoration-none font-weight-bold">
            <i class="fas fa-arrow-left mr-2"></i> Kembali ke Daftar Pesanan
        </a>
    </div>
</section>

<section class="content mt-3">
    <div class="container-fluid">
        
        @if($order->status == 'processing' && $order->cancel_request_status == 'requested')
        <div style="background-color: #fffdef; border-left: 4px solid #ffc107; padding: 16px; border-radius: 4px; border-top: 1px solid #f8ecc2; border-right: 1px solid #f8ecc2; border-bottom: 1px solid #f8ecc2; margin-bottom: 24px;">
            <div class="d-flex align-items-center mb-3">
                <i class="fas fa-exclamation-triangle mr-2" style="color: #ffc107; font-size: 1.5rem;"></i>
                <h5 class="font-weight-bold m-0" style="color: #333;">Pengajuan Pembatalan Pesanan</h5>
            </div>
            <div class="bg-white p-3 mb-3" style="border: 1px solid #eaeaea; border-radius: 4px;">
                <p class="mb-1 text-muted" style="font-size: 0.75rem; font-weight: 700; letter-spacing: 0.5px;">ALASAN PEMBELI:</p>
                <p class="font-weight-bold m-0" style="color: #333; font-size: 1rem;">{{ $order->cancel_reason ?? 'Tidak ada alasan.' }}</p>
            </div>
            
            <div class="d-flex gap-2">
                <button type="button" onclick="$('#approveCancelModal').modal('show')" class="btn btn-danger font-weight-bold" style="border-radius: 4px; padding: 6px 16px;">
                    <i class="fas fa-check-circle mr-1"></i> Setujui Batal
                </button>
                <form action="{{ route('orders.update', $order->id) }}" method="POST" class="d-inline">
                    @csrf @method('PUT')
                    <input type="hidden" name="status" value="processing">
                    <input type="hidden" name="payment_status" value="paid">
                    <input type="hidden" name="admin_action" value="reject_cancel">
                    <button type="button" onclick="this.innerHTML='<i class=\'fas fa-spinner fa-spin mr-1\'></i> Memproses...'; this.style.pointerEvents='none'; this.form.submit();" class="btn btn-light font-weight-bold text-secondary" style="border: 1px solid #ddd; border-radius: 4px; padding: 6px 16px;">
                        <i class="fas fa-times-circle mr-1"></i> Tolak
                    </button>
                </form>
                <button type="button" onclick="openChatAdmin({{ $order->user_id ?? 0 }}, '{{ addslashes($order->user->name ?? $order->receiver_name) }}')" class="btn btn-success font-weight-bold ml-2" style="border-radius: 4px; padding: 6px 16px;">
                    <i class="fas fa-comments mr-1"></i> Hubungi Pembeli
                </button>
            </div>
        </div>

        <div class="modal fade" id="approveCancelModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content" style="border: none; border-radius: 10px;">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title font-weight-bold text-danger">Konfirmasi Persetujuan Batal</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <form action="{{ route('orders.cancel', $order->id) }}" method="POST">
                        @csrf @method('PUT')
                        <input type="hidden" name="cancel_reason" value="Disetujui Penjual. Alasan Awal: {{ $order->cancel_reason }}">
                        <div class="modal-body p-4">
                            <p class="mb-0 text-dark">Apakah Anda yakin menyetujui pembatalan ini? Karena pesanan sudah dibayar, proses <strong>Refund (Pengembalian Dana)</strong> akan dilakukan.</p>
                        </div>
                        <div class="modal-footer bg-light border-0">
                            <button type="button" class="btn btn-light border" data-dismiss="modal">Tutup</button>
                            <button type="submit" onclick="this.innerHTML='<i class=\'fas fa-spinner fa-spin\'></i> Memproses...'; this.disabled=true; this.form.submit();" class="btn btn-danger font-weight-bold">Ya, Batalkan & Refund</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif

        @php
            $refund = $order->refund;
            // Tampil pas lagi proses atau kalau tipenya 'dana_saja' (Selesai)
            $showRefundBanner = ($order->status == 'refund_processing' || $order->refund_status == 'waiting_return' || (($order->is_refunded ?? false) && $refund && $refund->type == 'dana_saja')); 
        @endphp

        @if($showRefundBanner && $refund)
        <div style="background-color: #fff5f5; border-left: 4px solid #ef4444; padding: 16px; border-radius: 4px; border: 1px solid #fecaca; margin-bottom: 24px;">
            <div class="d-flex align-items-center mb-3">
                <i class="fas fa-undo-alt mr-2" style="color: #ef4444; font-size: 1.5rem;"></i>
                <h5 class="font-weight-bold m-0" style="color: #b91c1c;">
                    Riwayat Pengembalian: {{ $refund->type == 'dana_saja' ? 'Hanya Dana' : 'Barang & Dana' }}
                </h5>
                @if($refund->status == 'approved')
                    <span class="badge badge-success ml-3 px-2 py-1"><i class="fas fa-check"></i> Selesai (Disetujui)</span>
                @endif
            </div>
            
            <div class="row">
                <div class="col-md-8">
                    <div class="bg-white p-3 mb-3 h-100" style="border: 1px solid #eaeaea; border-radius: 4px;">
                        <div class="row">
                            <div class="col-sm-6 mb-2 mb-sm-0">
                                <p class="mb-1 text-muted" style="font-size: 0.75rem; font-weight: 700;">ALASAN DETAIL:</p>
                                <p class="font-weight-bold m-0 text-dark">{{ $refund->reason }}</p>
                            </div>
                            <div class="col-sm-6">
                                <p class="mb-1 text-muted" style="font-size: 0.75rem; font-weight: 700;">BARANG BERMASALAH:</p>
                                <p class="font-weight-bold m-0 text-dark">{{ $refund->items }}</p>
                                <p class="text-danger font-weight-bold m-0 mt-1">
                                    {{ $refund->status == 'approved' ? 'Dana Dikembalikan:' : 'Estimasi Dana:' }} Rp{{ number_format($refund->amount, 0, ',', '.') }}
                                </p>
                            </div>
                        </div>
                        <div class="mt-3 pt-2 border-top">
                            <p class="mb-2 text-muted" style="font-size: 0.75rem; font-weight: 700;">BUKTI LAMPIRAN PEMBELI:</p>
                            <div class="d-flex flex-wrap gap-2">
                                @if($refund->photos)
                                    @php $photos = json_decode($refund->photos); @endphp
                                    @if(is_array($photos))
                                        @foreach($photos as $index => $photo)
                                            <a href="{{ asset('storage/'.$photo) }}" target="_blank" class="btn btn-sm btn-outline-secondary py-1">
                                                <i class="fas fa-image mr-1"></i> Foto {{ $index + 1 }}
                                            </a>
                                        @endforeach
                                    @endif
                                @endif
                                
                                @if($refund->video)
                                    <a href="{{ asset('storage/'.$refund->video) }}" target="_blank" class="btn btn-sm btn-outline-secondary py-1">
                                        <i class="fas fa-video mr-1"></i> Video Unboxing
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            @if($refund->status != 'approved')
            <div class="d-flex gap-2 mt-2">
                @if($refund->type == 'dana_saja')
                    <form action="{{ route('admin.orders.processRefund', $order->id) }}" method="POST" class="d-inline">
                        @csrf
                        <input type="hidden" name="action" value="approve_dana_saja">
                        <button type="button" onclick="this.innerHTML='<i class=\'fas fa-spinner fa-spin mr-1\'></i> Memproses...'; this.style.pointerEvents='none'; this.form.submit();" class="btn btn-danger font-weight-bold" style="border-radius: 4px; padding: 6px 16px;">
                            <i class="fas fa-hand-holding-usd mr-1"></i> Kembalikan Dana (Rp{{ number_format($refund->amount, 0, ',', '.') }})
                        </button>
                    </form>
                @else
                    @if($order->refund_status == 'waiting_return')
                        <button type="button" disabled class="btn btn-secondary font-weight-bold" style="border-radius: 4px; padding: 6px 16px;">
                            <i class="fas fa-clock mr-1"></i> Menunggu Barang Kembali...
                        </button>
                        <form action="{{ route('admin.orders.processRefund', $order->id) }}" method="POST" class="d-inline ml-2">
                            @csrf
                            <input type="hidden" name="action" value="approve_barang_dana">
                            <button type="button" onclick="this.innerHTML='<i class=\'fas fa-spinner fa-spin mr-1\'></i> Memproses...'; this.style.pointerEvents='none'; this.form.submit();" class="btn btn-success font-weight-bold" style="border-radius: 4px; padding: 6px 16px;">
                                <i class="fas fa-check-circle mr-1"></i> Barang Diterima & Kembalikan Dana
                            </button>
                        </form>
                    @else
                        <form action="{{ route('admin.orders.processRefund', $order->id) }}" method="POST" class="d-inline">
                            @csrf
                            <input type="hidden" name="action" value="wait_return">
                            <button type="button" onclick="this.innerHTML='<i class=\'fas fa-spinner fa-spin mr-1\'></i> Memproses...'; this.style.pointerEvents='none'; this.form.submit();" class="btn btn-warning text-dark font-weight-bold" style="border-radius: 4px; padding: 6px 16px;">
                                <i class="fas fa-box-open mr-1"></i> Tunggu Barang Kembali
                            </button>
                        </form>
                    @endif
                @endif

                <button type="button" onclick="openChatAdmin({{ $order->user_id ?? 0 }}, '{{ addslashes($order->user->name ?? $order->receiver_name) }}')" class="btn btn-success font-weight-bold ml-2" style="border-radius: 4px; padding: 6px 16px;">
                    <i class="fas fa-comments mr-1"></i> Hubungi Pembeli
                </button>
            </div>
            @endif
        </div>
        @endif

        <div class="row">
            <div class="col-lg-8">
                
                @if($order->status == 'processing')
                <div class="status-banner status-processing mb-4">
                    <div class="status-icon"><i class="fas fa-box-open"></i></div>
                    <div>
                        <h4 class="font-weight-bold mb-1">Perlu Dikirim</h4>
                        <p class="mb-0 opacity-75">Pesanan sudah lunas. Segera siapkan tanaman dan serahkan ke kurir.</p>
                    </div>
                </div>
                @elseif($order->status == 'shipped')
                <div class="status-banner status-shipped mb-4">
                    <div class="status-icon"><i class="fas fa-truck-fast"></i></div>
                    <div>
                        <h4 class="font-weight-bold mb-1">Sudah Dikirim</h4>
                        <p class="mb-0 opacity-75">Paket sedang dalam perjalanan menuju lokasi pembeli.</p>
                    </div>
                </div>
                
                @elseif($order->status == 'completed' || $order->status == 'refund_processing')
                    <div class="status-banner status-completed mb-4">
                        <div class="status-icon"><i class="fas fa-check-circle"></i></div>
                        <div>
                            <h4 class="font-weight-bold mb-1">Telah Dikirim</h4>
                            <p class="mb-0 opacity-75">Pesanan telah diterima oleh pembeli. Transaksi selesai.</p>
                        </div>
                    </div>
                
                @elseif($order->status == 'cancelled' || $order->status == 'refunded')
                <div class="status-banner mb-4" style="background: #fef2f2; color: #dc2626; border: 1px solid #fecaca;">
                    <div class="status-icon" style="background: #fee2e2; color: #b91c1c;"><i class="fas fa-times-circle"></i></div>
                    <div>
                        <h4 class="font-weight-bold mb-1">Pesanan Dibatalkan / Dikembalikan</h4>
                        <p class="mb-0 opacity-75">
                            Dibatalkan oleh: <strong>{{ $order->cancelled_by == 'seller' ? 'Penjual' : ($order->cancelled_by == 'buyer' ? 'Pembeli' : 'Sistem') }}</strong> <br>
                            Alasan: <strong>{{ $order->cancel_reason ?? 'Retur Pembeli' }}</strong> <br>
                            Status Pembayaran: <strong class="text-success">Dana berhasil dikembalikan otomatis</strong>
                        </p>
                    </div>
                </div>
                @endif
                
                <div class="fresh-card">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="font-weight-bold mb-0">Roadmap Pengiriman</h5>
                        @if(!empty($order->shipping_method))
                            <span class="badge-internal"><i class="fas fa-motorcycle mr-1"></i> {{ $order->shipping_method }}</span>
                        @endif
                    </div>

                    <div class="bg-light rounded p-3 mb-4 d-flex justify-content-between align-items-start" style="border: 1px dashed #cbd5e1;">
                        <div>
                            <small class="text-muted d-block text-uppercase mb-1">No. Invoice Seller</small>
                            <span class="font-weight-bold" style="font-size: 1.1rem; color: #333;">{{ $order->invoice_number }}</span>
                            @if(!empty($order->resi_number))
                                <div class="mt-3">
                                    <small class="text-muted d-block text-uppercase mb-1">No. Resi Pengiriman</small>
                                    <div class="d-flex align-items-center">
                                        <span class="font-weight-bold text-theme mr-2" style="font-size: 1.1rem;" id="resiText">{{ $order->resi_number }}</span>
                                        <button onclick="copyResi()" class="btn-copy" title="Copy Resi"><i class="fas fa-copy"></i> Copy</button>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="text-right">
                            <small class="text-muted d-block">Tujuan</small>
                            <span class="font-weight-bold">{{ $order->city }}, {{ $order->province }}</span>
                        </div>
                    </div>

                    <div class="tracking-roadmap">
                        <!-- ROADMAP PENGAJUAN -->
                        @php
                            // Bikin ulang variabelnya di sini biar gak error
                            $isRefundRequested = ($order->status == 'refund_processing' || $order->refund_status == 'waiting_return'); 
                        @endphp
                        
                        @if($isRefundRequested || $order->status == 'refunded')
                        <div class="track-step active">
                            <div class="track-dot" style="background: #ef4444; box-shadow: 0 0 0 2px #ef4444;"></div>
                            <h6 class="font-weight-bold text-danger mb-1">
                                {{ $order->status == 'refunded' ? 'Pengembalian Selesai' : 'Pengajuan Pengembalian' }}
                            </h6>
                            <p class="text-muted small mb-0">Pembeli mengajukan komplain atas pesanan ini.</p>
                        </div>
                        @endif

                        @if(in_array($order->status, ['completed', 'refund_processing']) && empty($order->cancel_request_status))
                        <div class="track-step {{ !$isRefundRequested ? 'active' : '' }}">
                            <div class="track-dot"></div>
                            <h6 class="font-weight-bold text-success mb-1">Telah Dikirim (Pesanan Diterima)</h6>
                            <p class="text-muted small mb-0">Paket telah diserahkan langsung ke tangan pembeli.</p>
                            <small class="text-muted">{{ \Carbon\Carbon::parse($order->updated_at)->format('d F Y, H:i') }}</small>
                        </div>
                        @endif
                        
                        @if(in_array($order->status, ['shipped', 'completed', 'refund_processing', 'refunded']))
                        <div class="track-step {{ $order->status == 'shipped' ? 'active' : '' }}">
                            <div class="track-dot"></div>
                            <h6 class="font-weight-bold {{ $order->status == 'shipped' ? 'text-primary' : '' }} mb-1">Sudah Dikirim</h6>
                            <p class="text-muted small mb-0">Paket dibawa oleh {{ $order->shipping_method ?? 'Kurir Internal' }} menuju lokasi tujuan.</p>
                            <small class="text-muted">{{ $order->shipped_date ? \Carbon\Carbon::parse($order->shipped_date)->format('d F Y, H:i') : '' }}</small>
                        </div>
                        @endif

                        @if(in_array($order->status, ['processing', 'shipped', 'completed', 'refund_processing', 'refunded']))
                        <div class="track-step {{ $order->status == 'processing' ? 'active' : '' }}">
                            <div class="track-dot"></div>
                            <h6 class="font-weight-bold {{ $order->status == 'processing' ? 'text-warning' : '' }} mb-1">Perlu Dikirim (Sedang Disiapkan)</h6>
                            <p class="text-muted small mb-0">Pembayaran terverifikasi. Penjual sedang mengemas tanaman.</p>
                            <small class="text-muted">{{ \Carbon\Carbon::parse($order->created_at)->addMinutes(5)->format('d F Y, H:i') }}</small>
                        </div>
                        @endif
                        
                        <div class="track-step">
                            <div class="track-dot"></div>
                            <h6 class="font-weight-bold text-muted mb-1">Pesanan Dibuat</h6>
                            <small class="text-muted">{{ \Carbon\Carbon::parse($order->created_at)->format('d F Y, H:i') }}</small>
                        </div>
                    </div>
                </div>

                <div class="fresh-card">
                    <h5 class="font-weight-bold mb-4">Rincian Produk</h5>
                    <div class="table-responsive">
                        <table class="table table-borderless align-middle mb-0">
                            <thead style="border-bottom: 2px solid #f1f5f9;">
                                <tr>
                                    <th class="text-muted font-weight-normal pb-3">Produk</th>
                                    <th class="text-muted font-weight-normal pb-3 text-center">Harga</th>
                                    <th class="text-muted font-weight-normal pb-3 text-center">Qty</th>
                                    <th class="text-muted font-weight-normal pb-3 text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items ?? $order->orderItems ?? [] as $item)
                                <tr style="border-bottom: 1px solid #f8fafc;">
                                    <td class="py-3">
                                        <div class="d-flex align-items-center">
                                            @if(isset($item->product) && $item->product->images->isNotEmpty())
                                                <img src="{{ asset('storage/'.$item->product->images->first()->image_path) }}" width="60" height="60" class="rounded mr-3" style="object-fit: cover;">
                                            @else
                                                <div class="bg-light rounded mr-3 d-flex align-items-center justify-content-center" style="width:60px; height:60px;"><i class="fas fa-leaf text-muted"></i></div>
                                            @endif
                                            <div>
                                                <h6 class="mb-1 font-weight-bold" style="font-size: 0.95rem;">
                                                    {{ $item->product_name }}
                                                    @if(($order->is_refunded ?? false) && str_contains($refund->items ?? '', $item->product_name))
                                                        @if($refund->type == 'dana_saja')
                                                            <span class="badge badge-warning text-dark ml-2">Penyesuaian</span>
                                                        @else
                                                            <span class="badge badge-danger ml-2">Dikembalikan</span>
                                                        @endif
                                                    @endif
                                                </h6>
                                                @if(!empty($item->variation_name))
                                                    <span class="badge bg-light text-secondary">{{ $item->variation_name }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 text-center">Rp{{ number_format($item->price, 0, ',', '.') }}</td>
                                    <td class="py-3 text-center font-weight-bold">{{ $item->qty }}</td>
                                    <td class="py-3 text-right font-weight-bold text-theme">Rp{{ number_format($item->total, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                @if($order->status == 'processing')
                <div class="fresh-card bg-theme-soft p-4 mb-4" style="border: 1px solid #bbf7d0;">
                    <form id="shippingForm" action="{{ route('orders.update', $order->id) }}" method="POST" class="mb-0">
                        @csrf @method('PUT')
                        <input type="hidden" name="status" value="shipped">
                        <input type="hidden" name="payment_status" value="paid">
                        
                        <h6 class="font-weight-bold text-success mb-3 text-center border-bottom pb-2">Proses Pengiriman</h6>
                        <div class="form-group mb-3">
                            <label class="small font-weight-bold text-success">Pilih Jasa Kirim:</label>
                            <select name="shipping_method" id="shippingMethodInput" class="form-control form-control-sm" style="border-radius: 8px;" required>
                                <option value="">-- Pilih Kurir --</option>
                                <option value="JNE Express">📦 JNE Express</option>
                                <option value="J&T Express">📦 J&T Express</option>
                                <option value="SiCepat Ekspres">⚡ SiCepat Ekspres</option>
                                <option value="AnterAja">🛵 AnterAja</option>
                                <option value="Ninja Xpress">🥷 Ninja Xpress</option>
                                <option value="ID Express">🚚 ID Express</option>
                                <option value="SPX Express">🚚 SPX Express</option>
                                <option value="Pos Indonesia">📮 Pos Indonesia</option>
                                <option value="Wahana Express">📦 Wahana Express</option>
                                <option value="Paxel">❄️ Paxel</option>
                                <option value="GoSend">🏍️ GoSend (Instant/Sameday)</option>
                                <option value="GrabExpress">🏍️ GrabExpress (Instant/Sameday)</option>
                                <option value="Kurir Internal / Kurir Toko">🏠 Kurir Internal / Kurir Toko</option>
                            </select>
                        </div>
                        <div class="form-group mb-4">
                            <label class="small font-weight-bold text-success">Masukkan Nomor Resi:</label>
                            <input type="text" name="resi_number" id="resiNumberInput" class="form-control form-control-sm" style="border-radius: 8px;" placeholder="Contoh: JP123..." required>
                        </div>
                        <div class="d-flex flex-column" style="gap: 12px;">
                            <button type="button" class="btn btn-light btn-block shadow-sm" style="border-radius: 10px; color: #16a34a; border: 1px solid #16a34a; font-weight: 600;" id="printBtn"><i class="fas fa-print mr-2"></i> Cetak Label</button>
                            <button type="submit" class="btn btn-fresh btn-block shadow-sm m-0" id="submitBtn" disabled><i class="fas fa-motorcycle mr-2"></i> Serahkan ke Kurir</button>
                        </div>
                    </form>
                </div>
                @endif
                <div class="fresh-card pb-3">
                    <h6 class="font-weight-bold text-muted mb-4 text-uppercase" style="font-size: 0.8rem; letter-spacing: 1px;">Informasi Pembeli</h6>
                    <div class="d-flex align-items-center mb-4">
                        <div class="bg-light rounded-circle d-flex justify-content-center align-items-center mr-3" style="width: 50px; height: 50px;"><i class="fas fa-user text-muted" style="font-size: 20px;"></i></div>
                        <div>
                            <h6 class="font-weight-bold mb-0">{{ $order->receiver_name }}</h6>
                            <small class="text-muted">{{ $order->phone }}</small>
                        </div>
                    </div>
                    <div class="bg-light p-3 rounded mb-0">
                        <small class="text-muted d-block mb-1">Alamat Pengiriman</small>
                        <p class="mb-0 font-weight-bold" style="font-size: 0.9rem; line-height: 1.5;">{{ $order->full_address }}</p>
                    </div>
                </div>

                <div class="fresh-card">
                    <h6 class="font-weight-bold text-muted mb-4 text-uppercase" style="font-size: 0.8rem; letter-spacing: 1px;">
                        Rincian Keuangan {{ ($order->is_refunded ?? false) ? '(Penyesuaian)' : '' }}
                    </h6>
                    
                    @php
                        $midtrans_fee = 4500; 
                        $ongkir = $order->shipping ?? 0;
                        $gross_total = $order->grand_total; 
                        $net_income = $gross_total - $midtrans_fee; 
                    @endphp

                    @if($order->status == 'refunded' || $order->status == 'cancelled')
                        <div class="d-flex justify-content-between mb-3 text-muted" style="text-decoration: line-through;">
                            <span>Subtotal Produk</span>
                            <span>Rp{{ number_format($order->subtotal, 0, ',', '.') }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3 text-muted" style="text-decoration: line-through;">
                            <span>Ongkir Internal</span>
                            <span>Rp{{ number_format($ongkir, 0, ',', '.') }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3 pb-3 border-bottom text-muted" style="text-decoration: line-through;">
                            <span>Total Bayar Pembeli</span>
                            <span>Rp{{ number_format($gross_total, 0, ',', '.') }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-4 text-muted" style="text-decoration: line-through;">
                            <span><i class="fas fa-receipt mr-1"></i> Potongan Midtrans</span>
                            <span>Rp0</span>
                        </div>
                        
                        <div class="p-3 rounded" style="background: #fef2f2; border: 1px solid #fecaca;">
                            <span class="text-danger d-block mb-1 font-weight-bold">Penghasilan (Dibatalkan)</span>
                            <h4 class="font-weight-bold text-danger mb-0">Rp0</h4>
                        </div>

                    @elseif(($order->is_refunded ?? false) && ($refund->type ?? '') == 'dana_saja')
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-secondary">Subtotal Produk</span>
                            <span class="font-weight-bold">Rp{{ number_format($order->subtotal, 0, ',', '.') }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-secondary">Ongkir Internal</span>
                            <span class="font-weight-bold">Rp{{ number_format($ongkir, 0, ',', '.') }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3 text-danger font-weight-bold">
                            <span>Pengajuan Pengembalian</span>
                            <span>-Rp{{ number_format($order->refund_amount ?? 0, 0, ',', '.') }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3 pb-3 border-bottom">
                            <span class="text-secondary">Total Bayar Pembeli (Sisa)</span>
                            <span class="font-weight-bold">Rp{{ number_format($gross_total - ($order->refund_amount ?? 0), 0, ',', '.') }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-4">
                            <span class="text-danger"><i class="fas fa-receipt mr-1"></i> Potongan Midtrans</span>
                            <span class="text-danger font-weight-bold">-Rp{{ number_format($midtrans_fee, 0, ',', '.') }}</span>
                        </div>
                        <div class="p-3 rounded" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                            <span class="text-muted d-block mb-1 font-weight-bold">Penghasilan Akhir</span>
                            <h4 class="font-weight-bold text-theme mb-0">Rp{{ number_format(($gross_total - ($order->refund_amount ?? 0)) - $midtrans_fee, 0, ',', '.') }}</h4>
                        </div>

                    @else
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-secondary">Subtotal Produk</span>
                            <span class="font-weight-bold">Rp{{ number_format($order->subtotal, 0, ',', '.') }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-secondary">Ongkir Internal</span>
                            <span class="font-weight-bold">Rp{{ number_format($ongkir, 0, ',', '.') }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3 pb-3 border-bottom">
                            <span class="text-secondary">Total Bayar Pembeli</span>
                            <span class="font-weight-bold">Rp{{ number_format($gross_total, 0, ',', '.') }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-4">
                            <span class="text-danger"><i class="fas fa-receipt mr-1"></i> Potongan Midtrans</span>
                            <span class="text-danger font-weight-bold">-Rp{{ number_format($midtrans_fee, 0, ',', '.') }}</span>
                        </div>
                        <div class="p-3 rounded" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                            <span class="text-muted d-block mb-1 font-weight-bold">Penghasilan Akhir</span>
                            <h4 class="font-weight-bold text-theme mb-0">Rp{{ number_format($net_income, 0, ',', '.') }}</h4>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    function copyResi() {
        var resiText = document.getElementById("resiText").innerText;
        var tempInput = document.createElement("input");
        tempInput.value = resiText;
        document.body.appendChild(tempInput);
        tempInput.select();
        document.execCommand("copy");
        document.body.removeChild(tempInput);
        alert("Nomor Resi " + resiText + " berhasil disalin!");
    }

    document.addEventListener('DOMContentLoaded', function() {
        let printBtn = document.getElementById('printBtn');
        let submitBtn = document.getElementById('submitBtn');
        let kurirInput = document.getElementById('shippingMethodInput');
        let resiInput = document.getElementById('resiNumberInput');

        if (printBtn && submitBtn) {
            // Cek di memori apa label pesanan ini udah pernah diprint
            if(localStorage.getItem('label_printed_{{ $order->id }}') === 'true') {
                submitBtn.disabled = false;
            }

            printBtn.addEventListener('click', function() {
                let kurir = kurirInput ? kurirInput.value : '';
                let resi = resiInput ? resiInput.value : '';

                if(!kurir || !resi) {
                    alert('Bre, isi dulu Jasa Kirim sama Nomor Resinya sebelum cetak label!');
                    return;
                }

                let originalText = printBtn.innerHTML;
                printBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Loading...';
                printBtn.disabled = true;

                // Trik: Buka tab kosong dulu biar gak kena blokir Safari/Chrome Popup Blocker
                let printWindow = window.open('', '_blank');
                printWindow.document.write('<h3 style="font-family:sans-serif; text-align:center; margin-top:20%;">Sedang menyiapkan label pengiriman...</h3>');

                let formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('_method', 'PUT');
                formData.append('status', 'processing'); 
                formData.append('payment_status', 'paid');
                formData.append('shipping_method', kurir);
                formData.append('resi_number', resi);

                // Tembak data resi ke database diam-diam
                fetch("{{ route('orders.update', $order->id) }}", {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    printBtn.innerHTML = originalText;
                    printBtn.disabled = false;
                    
                    // Arahin tab kosong tadi ke halaman print label aslinya
                    printWindow.location.href = "{{ url('admin/orders/' . $order->id . '/print-label') }}";

                    // Nyalain tombol serahkan ke kurir secara permanen
                    submitBtn.disabled = false; 
                    localStorage.setItem('label_printed_{{ $order->id }}', 'true');
                })
                .catch(error => {
                    console.error('Error:', error);
                    printWindow.close();
                    alert('Sistem lagi sibuk, gagal nyimpen resi. Coba klik lagi Bre!');
                    printBtn.innerHTML = originalText;
                    printBtn.disabled = false;
                });
            });
        }
    });
</script>
@endsection