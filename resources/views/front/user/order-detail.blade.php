@extends('front.layouts.app')

@section('title', 'Detail Pesanan - Panambas')

@push('styles')
<style>
    /* Animasi Ceklis & Scrollbar */
    .success-checkmark {
        width: 80px; height: 80px; margin: 0 auto;
        border-radius: 50%; display: block; stroke-width: 2;
        stroke: #247a6b; stroke-miterlimit: 10; margin-bottom: 20px;
        box-shadow: inset 0px 0px 0px #247a6b;
        animation: fill .4s ease-in-out .4s forwards, scale .3s ease-in-out .9s both;
    }
    .checkmark__circle { stroke-dasharray: 166; stroke-dashoffset: 166; stroke-width: 2; stroke-miterlimit: 10; stroke: #247a6b; fill: none; animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards; }
    .checkmark__check { transform-origin: 50% 50%; stroke-dasharray: 48; stroke-dashoffset: 48; animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards; }
    @keyframes stroke { 100% { stroke-dashoffset: 0; } }
    @keyframes scale { 0%, 100% { transform: none; } 50% { transform: scale3d(1.1, 1.1, 1); } }
    @keyframes fill { 100% { box-shadow: inset 0px 0px 0px 30px #e6f2f0; } }
    
    .modal-scroll::-webkit-scrollbar { width: 6px; }
    .modal-scroll::-webkit-scrollbar-track { background: #f8f9fa; border-radius: 10px; }
    .modal-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .modal-scroll::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>
<script type="text/javascript" src="{{ env('MIDTRANS_IS_PRODUCTION') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}" data-client-key="{{ env('MIDTRANS_CLIENT_KEY') }}"></script>
@endpush

@section('content')
<div class="bg-[#f5f5f5] min-h-screen pb-20 pt-4 md:pt-8 relative overflow-x-hidden">
    <div class="max-w-3xl mx-auto px-4"> 
        
        <a href="{{ route('user.orders') }}" class="inline-flex items-center text-gray-600 hover:text-[#247a6b] font-medium mb-4 transition">
            <i class="fas fa-arrow-left mr-2"></i> Kembali ke Daftar Pesanan
        </a>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-4">
            
            @if($order->status == 'cancelled' || $order->status == 'refunded')
                <div class="bg-red-50 p-5 border-l-4 border-red-500">
                    <h2 class="text-lg font-bold text-red-700 mb-3 flex items-center">
                        <i class="fas fa-times-circle mr-2"></i> {{ $order->status == 'refunded' ? 'Pengembalian Selesai' : 'Pesanan Dibatalkan' }}
                    </h2>
                    <div class="bg-white/60 p-3 rounded-lg border border-red-100 text-sm">
                        <p class="mb-1 text-gray-700"><span class="font-semibold">Oleh:</span> {{ ucfirst($order->cancelled_by ?? 'Sistem') }}</p>
                        <p class="mb-2 text-gray-700"><span class="font-semibold">Alasan:</span> {{ $order->cancel_reason ?? ($order->status == 'refunded' ? 'Pengajuan Retur Disetujui' : 'Waktu pembayaran habis') }}</p>
                        <div class="mt-3 pt-3 border-t border-red-200">
                            <span class="font-semibold text-gray-800">Status Pembayaran:</span>
                            @if($order->refund_status == 'success' || $order->status == 'refunded')
                                <p class="text-green-600 font-bold mt-1"><i class="fas fa-check-circle mr-1"></i> Dana telah dikembalikan penuh ke metode pembayaran awal.</p>
                            @elseif(in_array(strtolower($order->payment_method ?? ''), ['qris', 'gopay', 'shopeepay', 'transfer']))
                                <p class="text-red-600 mt-1"><i class="fas fa-wallet mr-1"></i> Dana otomatis dikembalikan (Cek status di Midtrans).</p>
                            @else
                                <p class="text-red-600 mt-1"> Silahkan hubungi Penjual untuk info lebih lanjut.</p>
                            @endif
                        </div>
                    </div>
                </div>

            @elseif($order->status == 'refund_processing')
                <div class="bg-orange-50 p-5 border-l-4 border-orange-500 shadow-sm">
                    <h2 class="text-lg font-bold text-orange-700 mb-3 flex items-center"><i class="fas fa-sync fa-spin mr-2"></i> Pengajuan Pengembalian Dalam Proses</h2>
                    <div class="bg-white/70 p-4 rounded-lg border border-orange-200 text-sm">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-gray-500 text-[10px] mb-1 uppercase tracking-wider font-bold">Tipe Pengajuan</p>
                                <p class="font-bold text-gray-800">{{ ($order->refund->type ?? '') == 'dana_saja' ? 'Hanya Pengembalian Dana' : 'Pengembalian Barang & Dana' }}</p>
                            </div>
                            <div>
                                <p class="text-gray-500 text-[10px] mb-1 uppercase tracking-wider font-bold">Status Penjual</p>
                                <p class="font-bold {{ ($order->refund_status ?? '') == 'waiting_return' ? 'text-blue-600' : 'text-orange-600' }}">
                                    {{ ($order->refund_status ?? '') == 'waiting_return' ? 'Menunggu Anda mengembalikan barang' : 'Menunggu respon penjual' }}
                                </p>
                            </div>
                            
                            @if(($order->refund->type ?? 'dana_saja') == 'dana_saja')
                            <div class="md:col-span-2">
                                <p class="text-gray-500 text-[10px] mb-1 uppercase tracking-wider font-bold">Barang Bermasalah</p>
                                <ul class="list-disc pl-4 text-gray-800 font-bold">
                                    @if(isset($order->refund->items) && !empty($order->refund->items))
                                        @foreach(explode(',', $order->refund->items) as $rItem)
                                            <li>{{ trim($rItem) }}</li>
                                        @endforeach
                                    @else
                                        <li><i>Menunggu rincian barang...</i></li>
                                    @endif
                                </ul>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

            @elseif($order->status == 'processing' && $order->cancel_request_status == 'requested')
                <div id="statusBannerDetail" class="bg-yellow-50 p-5 border-l-4 border-yellow-500">
                    <h2 class="text-lg font-bold text-yellow-700 mb-2 flex items-center"><i class="fas fa-clock mr-2"></i> Menunggu Konfirmasi Batal</h2>
                    <p class="text-sm text-yellow-700 leading-relaxed font-medium mb-1">Pengajuan pembatalan lu sedang dikirim ke penjual.</p>
                </div>

            @elseif($order->status == 'processing')
                <div class="bg-blue-50 p-5 border-l-4 border-blue-500">
                    <h2 class="text-lg font-bold text-blue-700 mb-1 flex items-center"><i class="fas fa-box text-blue-600 mr-2"></i> Sedang Dikemas</h2>
                    <p class="text-sm text-blue-600 leading-relaxed">Paket sedang disiapkan dengan aman oleh penjual.</p>
                </div>

            @elseif($order->status == 'pending')
                <div class="bg-orange-50 p-5 border-l-4 border-orange-500">
                    <h2 class="text-lg font-bold text-orange-700 mb-1 flex items-center"><i class="far fa-clock mr-2"></i> Belum Bayar</h2>
                    <p class="text-sm text-orange-600 leading-relaxed">Segera lakukan pembayaran agar pesananmu diproses.</p>
                </div>

            @elseif($order->status == 'shipped')
                <div class="bg-[#f0f7f5] p-5 border-l-4 border-[#247a6b]">
                    <h2 class="text-lg font-bold text-[#247a6b] mb-1 flex items-center"><i class="fas fa-truck mr-2"></i> Paket Sedang Dikirim</h2>
                    <p class="text-sm text-[#1b5e52] leading-relaxed">Paketmu sedang dalam perjalanan menuju lokasimu.</p>
                </div>

            @elseif($order->status == 'completed')
                @if(($order->is_refunded ?? false) && ($order->refund_type ?? '') == 'dana_saja')
                <div class="bg-[#fff8f1] p-5 border-l-4 border-[#f59e0b]">
                    <h2 class="text-lg font-bold text-[#b45309] mb-1 flex items-center"><i class="fas fa-hand-holding-usd mr-2"></i> Dana Sebagian Dikembalikan</h2>
                    <p class="text-sm text-[#92400e] leading-relaxed">Pesanan selesai. Pengembalian dana untuk barang yang bermasalah telah disetujui penjual.</p>
                </div>
                @else
                <div class="bg-green-50 p-5 border-l-4 border-green-500">
                    <h2 class="text-lg font-bold text-green-700 mb-1 flex items-center"><i class="fas fa-check-circle mr-2"></i> Pesanan Selesai</h2>
                    <p class="text-sm text-green-600 leading-relaxed">Paket telah diterima. Bila ada kendala, pengajuan pengembalian berlaku maksimal 3 hari setelah paket sampai.</p>
                </div>
                @endif
            @endif
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-4">
            <h3 class="text-gray-800 font-bold mb-3 border-b pb-2"> Alamat Pengiriman</h3>
            <div class="text-sm text-gray-700">
                <!-- GANTI Auth::user()->name JADI $order->receiver_name -->
                <p class="font-bold text-base mb-1">{{ $order->receiver_name ?? Auth::user()->name }}</p>
                <p class="mb-1">{{ $order->phone ?? Auth::user()->phone }}</p>
                <p class="leading-relaxed text-gray-500">{{ $order->full_address ?? 'Alamat lengkap tidak ditemukan.' }}</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
            <div class="flex justify-between items-center mb-4 border-b pb-3">
                <h3 class="text-gray-800 font-bold"> Roadmap Pengiriman</h3>
            </div>

            <div class="bg-gray-50/80 border border-gray-200 border-dashed rounded-lg p-4 mb-6 flex flex-col md:flex-row justify-between gap-4 relative">
                <div>
                    <p class="text-[10px] text-gray-500 font-bold uppercase tracking-wider mb-1">No. Invoice</p>
                    <p class="font-bold text-gray-800 text-sm mb-4">{{ $order->invoice_number }}</p>

                    <p class="text-[10px] text-gray-500 font-bold uppercase tracking-wider mb-1">No. Resi Pengiriman</p>
                    @if($order->resi_number)
                        <div class="flex items-center">
                            <span class="font-bold text-[#247a6b] text-base font-mono mr-3 tracking-wide" id="teksResi">{{ $order->resi_number }}</span>
                            <button onclick="salinResi()" class="bg-white border border-gray-300 text-gray-600 text-[11px] px-2.5 py-1 rounded-md hover:bg-gray-100 hover:text-[#247a6b] transition shadow-sm font-medium flex items-center">
                                 Copy
                            </button>
                        </div>
                    @else
                        <span class="text-gray-400 text-sm italic font-medium">Belum tersedia</span>
                    @endif
                </div>
                <div class="md:text-right">
                    <p class="text-[10px] text-gray-500 font-bold uppercase tracking-wider mb-1">Tujuan Pengiriman</p>
                    <p class="font-bold text-gray-800 text-sm w-full md:max-w-[200px] leading-tight">
                        @php
                            $tujuan = 'ALAMAT PEMBELI';
                            if (!empty($order->province)) { $tujuan = strtoupper($order->province); } 
                            elseif (!empty($order->full_address)) {
                                if (preg_match('/Provinsi\s+(.*?)(?:\s*\()/i', $order->full_address, $matches)) {
                                    $tujuan = strtoupper(trim($matches[1]));
                                } else { $tujuan = strtoupper($order->city ?? 'ALAMAT TUJUAN'); }
                            }
                        @endphp
                        {{ $tujuan }}
                    </p>
                </div>
            </div>

            <div class="relative border-l-2 border-gray-200 ml-3 space-y-7 pb-2 mt-2">
                
                @if(in_array($order->status, ['refund_processing', 'refunded']))
                <div class="relative pl-6">
                    <div class="absolute w-4 h-4 {{ $order->status == 'refunded' ? 'bg-red-500' : 'bg-orange-500' }} rounded-full -left-[9px] top-0.5 border-4 border-white shadow"></div>
                    <h4 class="{{ $order->status == 'refunded' ? 'text-red-600 font-bold' : 'text-orange-600 font-bold' }} text-sm">
                        {{ $order->status == 'refunded' ? 'Pengembalian Selesai' : 'Pengajuan Pengembalian' }}
                    </h4>
                    <p class="text-[11px] text-gray-400 mt-1.5 font-medium">{{ $order->updated_at->format('d F Y, H:i') }}</p>
                </div>
                @endif

                @if(in_array($order->status, ['completed', 'refund_processing']))
                <div class="relative pl-6">
                    <div class="absolute w-4 h-4 bg-[#247a6b] rounded-full -left-[9px] top-0.5 border-4 border-white shadow"></div>
                    <h4 class="text-[#247a6b] font-bold text-sm">Telah Dikirim (Pesanan Diterima)</h4>
                    <p class="text-[11px] text-gray-400 mt-1.5 font-medium">{{ $order->updated_at->format('d F Y, H:i') }}</p>
                </div>
                @endif

                @if(in_array($order->status, ['shipped', 'completed', 'refund_processing', 'refunded']))
                <div class="relative pl-6">
                    <div class="absolute w-4 h-4 {{ $order->status == 'shipped' ? 'bg-[#247a6b]' : 'bg-gray-300' }} rounded-full -left-[9px] top-0.5 border-4 border-white shadow"></div>
                    <h4 class="{{ $order->status == 'shipped' ? 'text-[#247a6b] font-bold' : 'text-gray-700 font-bold' }} text-sm">Sudah Dikirim</h4>
                </div>
                @endif

                @if(in_array($order->status, ['processing', 'shipped', 'completed', 'refund_processing', 'refunded']))
                <div class="relative pl-6">
                    <div class="absolute w-4 h-4 {{ $order->status == 'processing' ? 'bg-[#247a6b]' : 'bg-gray-300' }} rounded-full -left-[9px] top-0.5 border-4 border-white shadow"></div>
                    <h4 class="{{ $order->status == 'processing' ? 'text-[#247a6b] font-bold' : 'text-gray-700 font-bold' }} text-sm">Perlu Dikirim</h4>
                </div>
                @endif

                @if($order->status != 'cancelled' || $order->status == 'refunded')
                <div class="relative pl-6">
                    <div class="absolute w-4 h-4 {{ $order->status == 'pending' ? 'bg-[#247a6b]' : 'bg-gray-300' }} rounded-full -left-[9px] top-0.5 border-4 border-white shadow"></div>
                    <h4 class="{{ $order->status == 'pending' ? 'text-[#247a6b] font-bold' : 'text-gray-700 font-bold' }} text-sm">Pesanan Dibuat</h4>
                    <p class="text-[11px] text-gray-400 mt-1.5 font-medium">{{ $order->created_at->format('d F Y, H:i') }}</p>
                </div>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-4">
            <div class="p-4 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                <h3 class="text-gray-800 font-bold"> Rincian Produk</h3>
                <span class="text-xs font-mono bg-white px-2 py-1 rounded border text-gray-500">{{ $order->invoice_number }}</span>
            </div>
            <div class="p-0">
                @php $daftarBarang = $order->orderItems ?? $order->items ?? []; @endphp
                @foreach($daftarBarang as $item)
                <a href="{{ route('front.product', $item->product_id ?? '#') }}" class="block hover:bg-gray-50 transition">
                    <div class="p-4 flex flex-row gap-4 border-b border-gray-50">
                        <div class="w-16 h-16 md:w-20 md:h-20 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0 border border-gray-200">
                            @if(isset($item->product) && $item->product->images->isNotEmpty())
                                <img src="{{ asset('storage/'.$item->product->images->first()->image_path) }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-300"><i class="fas fa-image"></i></div>
                            @endif
                        </div>
                        <div class="flex-1 flex flex-col justify-center">
                            <h4 class="text-sm font-semibold text-gray-800 line-clamp-2 leading-snug">
                                {{ $item->product_name ?? 'Produk Dihapus' }}
                                @if(($order->is_refunded ?? false) && isset($order->refund) && str_contains($order->refund->items ?? '', $item->product_name))
                                    @if($order->refund->type == 'dana_saja')
                                        <span class="bg-yellow-100 text-yellow-700 text-[10px] px-2 py-0.5 rounded ml-2 font-bold tracking-wide">PENYESUAIAN</span>
                                    @else
                                        <span class="bg-red-100 text-red-600 text-[10px] px-2 py-0.5 rounded ml-2 font-bold tracking-wide">DIKEMBALIKAN</span>
                                    @endif
                                @endif
                            </h4>
                            <div class="flex justify-between items-center mt-2">
                                <p class="text-xs font-medium text-gray-500">x{{ $item->qty ?? 1 }}</p>
                                <p class="text-sm font-bold text-gray-900">Rp{{ number_format($item->price ?? 0, 0, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
            <h3 class="text-gray-800 font-bold mb-4 border-b pb-2"> Rincian Keuangan {{ ($order->is_refunded ?? false) ? '(Penyesuaian)' : '' }}</h3>
            <div class="space-y-3 text-sm text-gray-600">
                
                @php
                    $hitungOngkir = $order->shipping_cost ?? 0;
                    if ($hitungOngkir == 0 && ($order->grand_total > $order->subtotal)) {
                        $hitungOngkir = $order->grand_total - $order->subtotal;
                    }
                @endphp

                @if($order->status == 'refunded' || $order->status == 'cancelled')
                    <div class="flex justify-between line-through text-gray-400">
                        <p>Total Harga Barang</p>
                        <p>Rp{{ number_format($order->subtotal ?? 0, 0, ',', '.') }}</p>
                    </div>
                    <div class="flex justify-between line-through text-gray-400">
                        <p>Ongkos Kirim</p>
                        <p>Rp{{ number_format($hitungOngkir, 0, ',', '.') }}</p>
                    </div>
                    <div class="border-t pt-3 mt-3 flex justify-between items-center">
                        <p class="font-bold text-red-600">Total Belanja (Dibatalkan)</p>
                        <p class="text-xl font-bold text-red-600">Rp0</p>
                    </div>
                
                @elseif(($order->is_refunded ?? false) && isset($order->refund) && $order->refund->type == 'dana_saja')
                    <div class="flex justify-between">
                        <p>Total Harga Barang</p>
                        <p class="font-medium text-gray-800">Rp{{ number_format($order->subtotal ?? 0, 0, ',', '.') }}</p>
                    </div>
                    <div class="flex justify-between">
                        <p>Ongkos Kirim</p>
                        <p class="font-medium text-gray-800">Rp{{ number_format($hitungOngkir, 0, ',', '.') }}</p>
                    </div>
                    <div class="flex justify-between text-orange-500 font-bold">
                        <p>Pengembalian Dana (Barang Kurang)</p>
                        <p>-Rp{{ number_format($order->refund_amount ?? 0, 0, ',', '.') }}</p>
                    </div>
                    <div class="border-t pt-3 mt-3 flex justify-between items-center">
                        <p class="font-bold text-gray-800">Total Belanja Akhir</p>
                        <p class="text-xl font-bold text-[#247a6b]">Rp{{ number_format(($order->grand_total ?? 0) - ($order->refund_amount ?? 0), 0, ',', '.') }}</p>
                    </div>

                @else
                    <div class="flex justify-between">
                        <p>Total Harga Barang</p>
                        <p class="font-medium text-gray-800">Rp{{ number_format($order->subtotal ?? 0, 0, ',', '.') }}</p>
                    </div>
                    <div class="flex justify-between">
                        <p>Ongkos Kirim</p>
                        @if($hitungOngkir <= 0)
                            <p class="font-bold text-[#247a6b]">Gratis Ongkir</p>
                        @else
                            <p class="font-medium text-gray-800">Rp{{ number_format($hitungOngkir, 0, ',', '.') }}</p>
                        @endif
                    </div>
                    <div class="border-t pt-3 mt-3 flex justify-between items-center">
                        <p class="font-bold text-gray-800">Total Belanja</p>
                        <p class="text-xl font-bold text-[#247a6b]">Rp{{ number_format($order->grand_total ?? 0, 0, ',', '.') }}</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="flex flex-col sm:flex-row gap-3 w-full">
            
            @if($order->status == 'pending')
                <button type="button" onclick="openCancelModal('pending')" class="flex-1 px-4 py-3 border border-red-200 text-red-600 font-bold text-sm rounded-lg hover:bg-red-50 transition bg-white text-center">Batalkan Pesanan</button>
                <button type="button" onclick="payMidtrans('{{ $order->snap_token }}')" class="flex-1 px-4 py-3 bg-[#247a6b] text-white font-bold text-sm rounded-lg hover:bg-[#1b5e52] transition shadow-md text-center">Bayar Sekarang</button>
            @endif

            @if($order->status == 'processing')
                @if($order->cancel_request_status == 'requested')
                    <button type="button" onclick="cancelCancellationRequest({{ $order->id }}, this)" class="flex-1 px-4 py-3 border border-red-500 text-red-600 font-bold text-sm rounded-lg hover:bg-red-50 transition shadow-sm bg-white text-center">Batalkan Pengajuan</button>
                @elseif(empty($order->cancel_request_status))
                    <button type="button" onclick="openCancelModal({{ $order->id }}, 'processing')" class="flex-1 px-4 py-3 border border-red-200 text-red-600 font-bold text-sm rounded-lg hover:bg-red-50 transition bg-white text-center">Batalkan Pesanan</button>
                @endif
            @endif

            @if($order->status == 'shipped')
                <a href="https://cekresi.com/?noresi={{ $order->tracking_number }}" target="_blank" class="flex-1 px-4 py-3 border border-[#247a6b] text-[#247a6b] font-bold text-sm rounded-lg hover:bg-[#f0f7f5] transition shadow-sm bg-white text-center">Lacak Paket</a>
                <form action="{{ route('user.orders.complete', $order->id) }}" method="POST" class="flex-1 flex">
                    @csrf @method('PUT')
                    <button type="button" onclick="this.innerHTML='<i class=\'fas fa-spinner fa-spin mr-2\'></i> Memproses...'; this.style.pointerEvents='none'; this.classList.add('opacity-75'); this.form.submit();" class="w-full px-4 py-3 bg-[#247a6b] text-white font-bold text-sm rounded-lg hover:bg-[#1b5e52] transition shadow-md text-center">Pesanan Diterima</button>
                </form>
            @endif

            @if($order->status == 'refund_processing')
                <button type="button" onclick="cancelRefundRequest({{ $order->id }}, this)" class="flex-1 px-4 py-3 border border-red-500 text-red-600 font-bold text-sm rounded-lg hover:bg-red-50 transition shadow-sm bg-white text-center">Batalkan Pengajuan</button>
            @elseif($order->status == 'completed')
                @php
                    $idProduk = $daftarBarang->first()->product_id ?? 0;
                    $cekRating = \App\Models\ProductRating::where('product_id', $idProduk)->where('email', Auth::user()->email ?? '')->first();
                    
                    $batasRefund = \Carbon\Carbon::parse($order->updated_at)->addDays(3);
                    $bisaRefund = now()->lessThanOrEqualTo($batasRefund);
                    
                    // Cek kalau udah pernah ngajuin lalu ditarik/ditolak seller
                    $pernahNgajuin = in_array($order->cancel_request_status, ['undone', 'rejected']) || in_array($order->refund_status ?? '', ['undone', 'rejected', 'cancelled']); 
                @endphp

                @if(!($order->is_refunded ?? false) && $bisaRefund && !$pernahNgajuin)
                    @php
                        $refundItems = [];
                        foreach($daftarBarang as $itm) {
                            $refundItems[] = ['name' => $itm->product_name ?? 'Produk', 'price' => $itm->price ?? 0, 'qty' => $itm->qty ?? 1];
                        }
                    @endphp
                    <button type="button" data-items='{{ json_encode($refundItems) }}' onclick="openRefundModal({{ $order->id }}, '{{ $order->invoice_number }}', this)" class="flex-1 px-4 py-3 border border-red-500 text-red-600 font-bold text-sm rounded-lg hover:bg-red-50 transition shadow-sm bg-white text-center">Ajukan Pengembalian</button>
                @endif

                @if($cekRating)
                    <button type="button" onclick="openRatingModal({{ $idProduk }}, true)" class="flex-1 px-4 py-3 border border-[#247a6b] text-[#247a6b] font-bold text-sm rounded-lg hover:bg-[#f0f7f5] transition shadow-sm bg-white text-center">Edit Penilaian</button>
                @else
                    <button type="button" onclick="openRatingModal({{ $idProduk }}, false)" class="flex-1 px-4 py-3 bg-[#247a6b] text-white font-bold text-sm rounded-lg hover:bg-[#1b5e52] transition shadow-md text-center">Beri Penilaian</button>
                @endif
                
                <a href="{{ route('front.shop') }}" class="flex-1 px-4 py-3 bg-yellow-500 text-white font-bold text-sm rounded-lg hover:bg-yellow-600 transition shadow-md text-center">Beli Lagi</a>

            @endif

            @if(in_array($order->status, ['refunded', 'cancelled']))
                <button type="button" class="flex-1 px-4 py-3 bg-gray-100 border border-gray-300 text-gray-700 font-bold text-sm rounded-lg transition shadow-sm text-center">Detail Batal/Pengembalian</button>
                <a href="{{ route('front.shop') }}" class="flex-1 px-4 py-3 bg-[#247a6b] text-white font-bold text-sm rounded-lg hover:bg-[#1b5e52] transition shadow-md text-center">Beli Lagi</a>
            @endif
        </div>
        
    </div>
</div>

<!-- ================= MODALS ================= -->
<div id="cancelModal" class="fixed inset-0 bg-black/60 z-[9999] hidden flex items-center justify-center backdrop-blur-sm transition-opacity px-4 py-6">
    <div class="bg-white w-full max-w-lg rounded-xl shadow-2xl flex flex-col max-h-[90vh] transform transition-all scale-95 opacity-0" id="cancelModalContent">
        <div class="flex justify-between items-center p-5 border-b border-gray-100 shrink-0">
            <h3 class="text-lg font-bold text-gray-800">Batalkan Pesanan</h3>
            <button type="button" onclick="closeCancelModal()" class="text-gray-400 hover:text-red-500"><i class="fas fa-times text-xl"></i></button>
        </div>
        
        <div class="p-5 overflow-y-auto modal-scroll grow">
            <form id="cancelForm" action="{{ route('user.orders.cancel') }}" method="POST">
                @csrf
                <input type="hidden" name="order_id" value="{{ $order->id }}">
                
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-3">Alasan Pembatalan <span class="text-red-500">*</span></label>
                    <div class="space-y-3">
                        <label class="flex items-center gap-3 cursor-pointer p-2 rounded hover:bg-gray-50 transition border border-transparent hover:border-gray-200">
                            <input type="radio" name="cancel_reason" value="Ingin mengubah alamat pengiriman" class="text-[#247a6b] focus:ring-[#247a6b] w-4 h-4" required>
                            <span class="text-sm text-gray-700 font-medium">Ingin mengubah alamat pengiriman</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer p-2 rounded hover:bg-gray-50 transition border border-transparent hover:border-gray-200">
                            <input type="radio" name="cancel_reason" value="Ingin menambah/mengurangi barang pesanan" class="text-[#247a6b] focus:ring-[#247a6b] w-4 h-4">
                            <span class="text-sm text-gray-700 font-medium">Ingin menambah/mengurangi barang pesanan</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer p-2 rounded hover:bg-gray-50 transition border border-transparent hover:border-gray-200">
                            <input type="radio" name="cancel_reason" value="Berubah pikiran" class="text-[#247a6b] focus:ring-[#247a6b] w-4 h-4">
                            <span class="text-sm text-gray-700 font-medium">Berubah pikiran</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer p-2 rounded hover:bg-gray-50 transition border border-transparent hover:border-gray-200">
                            <input type="radio" name="cancel_reason" value="Lainnya" class="text-[#247a6b] focus:ring-[#247a6b] w-4 h-4">
                            <span class="text-sm text-gray-700 font-medium">Lainnya</span>
                        </label>
                    </div>
                </div>

                <div id="cancelWarningPaid" class="bg-yellow-50 text-yellow-800 p-3 rounded-lg text-xs mb-5 leading-relaxed hidden border border-yellow-200">
                    Karena pesanan sudah dibayar, pembatalan <strong>membutuhkan persetujuan penjual</strong> maksimal 1x24 jam.
                </div>
                
                <div class="flex gap-3">
                    <button type="button" onclick="closeCancelModal()" class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg text-gray-700 font-bold hover:bg-gray-50">Tutup</button>
                    <button type="submit" onclick="this.innerHTML='Memproses...'; this.classList.add('opacity-75');" class="flex-1 px-4 py-2.5 bg-red-600 text-white rounded-lg font-bold hover:bg-red-700 shadow-md">Konfirmasi Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="ratingModal" class="fixed inset-0 bg-black/60 z-[9999] hidden flex items-center justify-center backdrop-blur-sm transition-opacity px-4 py-6">
    <div class="bg-white w-full max-w-md rounded-xl shadow-2xl flex flex-col max-h-[90vh] transform transition-all scale-95 opacity-0" id="ratingModalContent">
        <div class="flex justify-between items-center p-5 border-b border-gray-100 shrink-0">
            <h3 class="text-lg font-bold text-gray-800" id="ratingTitle">Nilai Produk</h3>
            <button type="button" onclick="closeRatingModal()" class="text-gray-400 hover:text-red-500"><i class="fas fa-times text-xl"></i></button>
        </div>
        
        <div class="p-5 overflow-y-auto modal-scroll grow">
            <form id="ratingForm" action="{{ route('front.rating.store') }}" method="POST" onsubmit="showLoading()">
                @csrf
                <input type="hidden" id="ratingProductId" name="product_id" value="">
                <input type="hidden" id="ratingStarValue" name="rating" value="0">
                <div class="flex justify-center space-x-2 my-4">
                    @for($i=1; $i<=5; $i++)
                    <i class="fas fa-star text-3xl text-gray-300 cursor-pointer hover:scale-110 transition star-btn" data-value="{{ $i }}" onclick="selectStar({{ $i }})"></i>
                    @endfor
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tulis ulasan Anda (Opsional)</label>
                    <textarea name="comment" rows="3" class="w-full border border-gray-300 rounded-lg p-3 text-sm focus:border-[#247a6b] focus:ring-1 focus:ring-[#247a6b] outline-none" placeholder="Bagaimana kualitas produknya?"></textarea>
                </div>
                <div class="flex gap-3">
                    <button type="button" onclick="closeRatingModal()" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 font-bold hover:bg-gray-50">Kembali</button>
                    <button type="submit" id="btnSubmitRating" disabled class="flex-1 px-4 py-2 bg-[#247a6b] text-white rounded-lg font-bold opacity-50 cursor-not-allowed transition">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="refundModal" class="fixed inset-0 bg-black/60 z-[9999] hidden flex items-center justify-center backdrop-blur-sm transition-opacity px-4 py-6">
    <div class="bg-white w-full max-w-lg rounded-xl shadow-2xl flex flex-col max-h-[90vh] transform transition-all scale-95 opacity-0" id="refundModalContent">
        <div class="flex justify-between items-center p-5 border-b border-gray-100 shrink-0">
            <h3 class="text-lg font-bold text-red-600">Pengajuan Pengembalian</h3>
            <button type="button" onclick="closeRefundModal()" class="text-gray-400 hover:text-red-500"><i class="fas fa-times text-xl"></i></button>
        </div>
        
        <div class="p-5 overflow-y-auto modal-scroll grow">
            <form id="refundForm" onsubmit="submitRefundRequest(event)">
                <input type="hidden" id="refundOrderId">
                <input type="hidden" id="refundInvoice">

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Pengajuan <span class="text-red-500">*</span></label>
                    <select id="refundType" onchange="toggleRefundInfo()" class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:border-[#247a6b] outline-none font-medium text-gray-800" required>
                        <option value="" disabled selected>Pilih tipe pengajuan...</option>
                        <option value="dana_saja">1. Hanya Pengembalian Dana (Barang kurang / tidak sampai)</option>
                        <option value="barang_dana">2. Pengembalian Barang & Dana (Barang rusak / salah kirim)</option>
                    </select>
                </div>

                <div id="containerPilihBarang" class="mb-4 hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Barang yang Bermasalah <span class="text-red-500">*</span></label>
                    <button type="button" onclick="document.getElementById('itemSelectionModal').classList.remove('hidden')" class="w-full bg-white border border-gray-300 text-gray-700 font-medium py-2.5 px-4 rounded-lg hover:bg-gray-50 flex justify-between items-center shadow-sm">
                        <span>Klik Untuk Pilih Barang...</span>
                    </button>
                    <div id="selectedRefundItemsDisplay"></div>
                </div>

                <div id="containerEstimasiDana" class="mb-4 hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Estimasi Dana Dikembalikan</label>
                    <input type="text" id="refundAmountDisplay" class="w-full border border-gray-200 bg-gray-50 text-gray-800 rounded-lg p-2.5 text-sm font-bold outline-none cursor-not-allowed" readonly value="Rp0">
                    <input type="hidden" id="refundAmount" value="0">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Alasan Detail <span class="text-red-500">*</span></label>
                    <select id="refundReason" class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:border-[#247a6b] outline-none" required>
                        <option value="" disabled selected>Pilih alasan...</option>
                        <option value="Barang tidak sesuai deskripsi">Barang tidak sesuai deskripsi / salah produk</option>
                        <option value="Diterima dalam kondisi rusak">Diterima dalam kondisi rusak / cacat / layu</option>
                        <option value="Barang yang diterima kurang">Barang yang diterima jumlahnya kurang</option>
                        <option value="Paket kosong / Tidak sampai">Paket kosong / Tidak sampai ke alamat</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Bukti Foto (Maksimal 3) <span class="text-red-500">*</span></label>
                    <input type="file" id="refundPhotos" accept="image/*" multiple required class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-[#f0f7f5] file:text-[#247a6b] hover:file:bg-[#e6f2f0]">
                </div>

                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Video Unboxing (Wajib, Maks 3 Menit) <span class="text-red-500">*</span></label>
                    <input type="file" id="refundVideo" accept="video/*" required class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100">
                </div>

                <button type="submit" class="w-full px-4 py-3 bg-red-600 text-white rounded-lg font-bold hover:bg-red-700 transition shadow-md">Ajukan Sekarang</button>
            </form>
        </div>
    </div>
</div>

<div id="itemSelectionModal" class="fixed inset-0 bg-black/70 z-[10000] hidden flex items-center justify-center backdrop-blur-sm px-4 py-6">
    <div class="bg-white w-full max-w-sm rounded-xl shadow-2xl flex flex-col max-h-[85vh] transform transition-all">
        <div class="p-5 border-b border-gray-100 shrink-0">
            <h3 class="text-lg font-bold text-gray-800">Pilih Barang yang Kurang</h3>
        </div>
        <div id="refundItemSelectionList" class="p-5 overflow-y-auto modal-scroll grow space-y-2"></div>
        <div class="p-5 border-t border-gray-100 shrink-0">
            <button type="button" onclick="closeItemSelection()" class="w-full bg-[#247a6b] text-white font-bold py-2.5 rounded-lg shadow-md hover:bg-[#1b5e52]">Oke, Simpan Pilihan</button>
        </div>
    </div>
</div>

<div id="paymentSuccessModal" class="fixed inset-0 bg-black/60 z-[9999] hidden flex items-center justify-center backdrop-blur-sm transition-opacity px-4">
    <div class="bg-white w-full max-w-sm rounded-2xl shadow-2xl p-8 text-center transform transition-all scale-95 opacity-0" id="paymentSuccessContent">
        <svg class="success-checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
            <circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none"/>
            <path class="checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
        </svg>
        <h3 class="text-2xl font-bold text-gray-900 mb-2">Berhasil!</h3>
        <p class="text-gray-500 text-sm mb-6">Aksi berhasil diproses. Halaman akan dimuat ulang.</p>
        <button onclick="window.location.reload()" class="w-full px-4 py-3 bg-[#247a6b] text-white rounded-lg font-bold hover:bg-[#1b5e52] transition shadow-md">Muat Ulang Halaman</button>
    </div>
</div>

<div id="confirmUndoModal" class="fixed inset-0 bg-black/60 z-[9999] hidden flex items-center justify-center backdrop-blur-sm transition-opacity px-4 py-6">
    <div class="bg-white w-full max-w-sm rounded-2xl shadow-2xl flex flex-col transform transition-all scale-95 opacity-0 text-center p-6" id="confirmUndoContent">
        <div class="w-16 h-16 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-4 border border-red-100">
            <i class="fas fa-exclamation-triangle text-red-500 text-2xl"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-900 mb-2">Tarik Pengajuan?</h3>
        <p class="text-gray-500 text-sm mb-6">Yakin ingin membatalkan pengajuan ini? Pesanan kamu akan dilanjutkan secara normal.</p>
        <div class="flex gap-3">
            <button type="button" onclick="closeConfirmUndoModal()" class="flex-1 px-4 py-3 border border-gray-300 text-gray-700 font-bold text-sm rounded-xl hover:bg-gray-50 transition">Batal</button>
            <button type="button" id="btnConfirmUndoAction" class="flex-1 px-4 py-3 bg-red-600 text-white font-bold text-sm rounded-xl hover:bg-red-700 shadow-md transition">Ya, Tarik</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // ==========================================
    // 1. FUNGSI DOM READY
    // ==========================================
    document.addEventListener('DOMContentLoaded', function() {
        const alerts = document.querySelectorAll('[role="alert"]');
        alerts.forEach(function(alert) {
            setTimeout(() => {
                alert.style.transition = "opacity 0.5s ease-out";
                alert.style.opacity = "0";
                setTimeout(() => { alert.remove(); }, 500);
            }, 4000);
        });

        const photoInput = document.getElementById('refundPhotos');
        if(photoInput) {
            photoInput.addEventListener('change', function() {
                if(this.files.length > 3) { alert("Maksimal upload foto cuma 3 lembar aja Bre!"); this.value = ""; }
            });
        }

        const videoInput = document.getElementById('refundVideo');
        if(videoInput) {
            videoInput.addEventListener('change', function() {
                if(this.files.length > 0) {
                    var file = this.files[0];
                    var video = document.createElement('video');
                    video.preload = 'metadata';
                    video.onloadedmetadata = function() {
                        window.URL.revokeObjectURL(video.src);
                        if (video.duration > 180) { alert("Durasi video kepanjangan! Maksimal 3 menit ya."); document.getElementById('refundVideo').value = ""; }
                    }
                    video.src = URL.createObjectURL(file);
                }
            });
        }
    });

    // ==========================================
    // 2. FUNGSI PEMBATALAN PESANAN
    // ==========================================
    function openCancelModal(orderId, status) {
        document.getElementById('cancelOrderId').value = orderId;
        const warningBox = document.getElementById('cancelWarningPaid');
        if(status === 'processing') { warningBox.classList.remove('hidden'); } 
        else { warningBox.classList.add('hidden'); }

        const modal = document.getElementById('cancelModal');
        const content = document.getElementById('cancelModalContent');
        if(modal) {
            modal.classList.remove('hidden');
            setTimeout(() => { content.classList.remove('scale-95', 'opacity-0'); }, 10);
        }
    }

    function closeCancelModal() {
        const modal = document.getElementById('cancelModal');
        const content = document.getElementById('cancelModalContent');
        if(content) content.classList.add('scale-95', 'opacity-0');
        if(modal) setTimeout(() => { modal.classList.add('hidden'); document.getElementById('cancelForm').reset(); }, 300);
    }

    // ==========================================
    // 2.B. FUNGSI BATALKAN PENGAJUAN (CUSTOM MODAL)
    // ==========================================
    let currentUndoOrderId = null;
    let currentUndoBtn = null;

    function openConfirmUndoModal() {
        const modal = document.getElementById('confirmUndoModal');
        const content = document.getElementById('confirmUndoContent');
        if(modal) {
            modal.classList.remove('hidden');
            setTimeout(() => { content.classList.remove('scale-95', 'opacity-0'); }, 10);
        }
    }

    function closeConfirmUndoModal() {
        const modal = document.getElementById('confirmUndoModal');
        const content = document.getElementById('confirmUndoContent');
        if(content) content.classList.add('scale-95', 'opacity-0');
        if(modal) setTimeout(() => { modal.classList.add('hidden'); }, 300);
    }

    function cancelCancellationRequest(orderId, btnElement) {
        currentUndoOrderId = orderId;
        currentUndoBtn = btnElement;
        openConfirmUndoModal();
    }

    function cancelRefundRequest(orderId, btnElement) {
        cancelCancellationRequest(orderId, btnElement); 
    }

    document.addEventListener('DOMContentLoaded', function() {
        const btnConfirmUndoAction = document.getElementById('btnConfirmUndoAction');
        if(btnConfirmUndoAction) {
            btnConfirmUndoAction.addEventListener('click', function() {
                const orderId = currentUndoOrderId;
                const btnElement = currentUndoBtn;
                closeConfirmUndoModal();

                if(btnElement) {
                    btnElement.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Memproses...';
                    btnElement.classList.add('opacity-75', 'cursor-not-allowed');
                    btnElement.disabled = true;
                }

                fetch(`/user/orders/${orderId}/undo-cancel`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                    return response.json();
                })
                .then(data => {
                    if(data.success) {
                        const modal = document.getElementById('paymentSuccessModal');
                        const content = document.getElementById('paymentSuccessContent');
                        if(modal && content) {
                            modal.querySelector('h3').innerText = "Pengajuan Dibatalkan!";
                            modal.querySelector('p').innerText = "Status pesanan berhasil dikembalikan normal.";
                            modal.classList.remove('hidden');
                            setTimeout(() => { content.classList.remove('scale-95', 'opacity-0'); }, 10);
                        }
                        setTimeout(() => { window.location.reload(); }, 2000);
                    } else {
                        alert(data.message || 'Gagal membatalkan pengajuan.');
                        window.location.reload();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Sistem sedang sibuk, coba lagi nanti Bre.');
                    window.location.reload();
                });
            });
        }
    });

    // ==========================================
    // 3. FUNGSI MIDTRANS & UTILITIES
    // ==========================================
    function salinResi() {
        var resi = document.getElementById("teksResi").innerText;
        navigator.clipboard.writeText(resi).then(function() { alert("Nomor Resi " + resi + " berhasil disalin!"); });
    }

    function payMidtrans(snapToken) {
        if (!snapToken) { alert('Token pembayaran tidak ditemukan.'); return; }
        window.snap.pay(snapToken, {
            onSuccess: function(result){
                const modal = document.getElementById('paymentSuccessModal');
                const content = document.getElementById('paymentSuccessContent');
                modal.querySelector('h3').innerText = "Pembayaran Berhasil!";
                modal.querySelector('p').innerText = "Terima kasih, pesanan lu udah lunas.";
                if(modal && content) {
                    modal.classList.remove('hidden');
                    setTimeout(() => { content.classList.remove('scale-95', 'opacity-0'); }, 10);
                } else { alert("Pembayaran Berhasil! Halaman akan dimuat ulang."); }
                setTimeout(() => { window.location.reload(); }, 3000);
            },
            onPending: function(result){ alert("Menunggu pembayaran Anda diselesaikan."); },
            onError: function(result){ alert("Pembayaran Gagal! Silakan coba lagi."); },
            onClose: function(){ console.log('Popup ditutup'); }
        });
    }

    // ==========================================
    // 4. FUNGSI RATING BINTANG
    // ==========================================
    function showLoading() {
        const btn = document.getElementById('btnSubmitRating');
        if(btn) { btn.innerHTML = 'Menyimpan...'; btn.classList.add('opacity-75', 'cursor-not-allowed'); }
    }

    function openRatingModal(productId, isEdit = false) {
        document.getElementById('ratingProductId').value = productId;
        document.getElementById('ratingTitle').innerText = isEdit ? 'Edit Penilaian' : 'Nilai Produk';
        const btn = document.getElementById('btnSubmitRating');
        if(btn) { btn.innerHTML = 'Simpan'; btn.disabled = true; btn.classList.add('opacity-50', 'cursor-not-allowed'); }
        const modal = document.getElementById('ratingModal');
        const content = document.getElementById('ratingModalContent');
        if(modal) { modal.classList.remove('hidden'); setTimeout(() => { content.classList.remove('scale-95', 'opacity-0'); }, 10); }
    }

    function closeRatingModal() {
        const modal = document.getElementById('ratingModal');
        const content = document.getElementById('ratingModalContent');
        if(content) content.classList.add('scale-95', 'opacity-0');
        if(modal) setTimeout(() => { modal.classList.add('hidden'); resetStars(); }, 300);
    }

    function selectStar(value) {
        document.getElementById('ratingStarValue').value = value;
        document.querySelectorAll('.star-btn').forEach(star => {
            if (star.getAttribute('data-value') <= value) { star.classList.remove('text-gray-300'); star.classList.add('text-yellow-400'); } 
            else { star.classList.remove('text-yellow-400'); star.classList.add('text-gray-300'); }
        });
        const btn = document.getElementById('btnSubmitRating');
        if(btn) { btn.disabled = false; btn.classList.remove('opacity-50', 'cursor-not-allowed'); }
    }

    function resetStars() {
        document.getElementById('ratingStarValue').value = 0;
        document.querySelectorAll('.star-btn').forEach(s => { s.classList.remove('text-yellow-400'); s.classList.add('text-gray-300'); });
        document.querySelector('textarea[name="comment"]').value = '';
        const btn = document.getElementById('btnSubmitRating');
        if(btn) { btn.disabled = true; btn.classList.add('opacity-50', 'cursor-not-allowed'); }
    }

    // ==========================================
    // 5. FUNGSI REFUND (DENGAN QTY DYNAMIC)
    // ==========================================
    function openRefundModal(orderId, invoice, btnElement) {
        document.getElementById('refundOrderId').value = orderId;
        document.getElementById('refundInvoice').value = invoice;
        
        const form = document.getElementById('refundForm');
        if (form) form.reset();
        
        document.getElementById('selectedRefundItemsDisplay').innerHTML = '';
        document.getElementById('refundAmount').value = 0;
        document.getElementById('refundAmountDisplay').value = 'Rp0';
        toggleRefundInfo();

        if (btnElement && btnElement.hasAttribute('data-items')) {
            try {
                let items = JSON.parse(btnElement.getAttribute('data-items'));
                let html = '';
                items.forEach((item, index) => {
                    html += `
                        <div class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-gray-800 leading-tight">${item.name}</p>
                                <p class="text-xs text-[#247a6b] font-bold mt-1">Rp${new Intl.NumberFormat('id-ID').format(item.price)} <span class="text-gray-400 font-normal">/ pcs (Maks: ${item.qty})</span></p>
                            </div>
                            <div class="flex items-center border border-gray-300 rounded-md bg-white">
                                <button type="button" onclick="updateRefundQty(${index}, -1, ${item.qty}, ${item.price})" class="w-8 h-8 flex items-center justify-center bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-l-md transition">-</button>
                                <input type="number" id="refund_qty_${index}" class="w-10 text-center text-sm font-bold border-0 focus:ring-0 p-0 refund-qty-input" value="0" min="0" max="${item.qty}" data-name="${item.name}" data-price="${item.price}" readonly>
                                <button type="button" onclick="updateRefundQty(${index}, 1, ${item.qty}, ${item.price})" class="w-8 h-8 flex items-center justify-center bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-r-md transition">+</button>
                            </div>
                        </div>
                    `;
                });
                document.getElementById('refundItemSelectionList').innerHTML = html;
            } catch (error) {
                console.error("Gagal baca data barang Bre:", error);
            }
        }

        const modal = document.getElementById('refundModal');
        const content = document.getElementById('refundModalContent');
        if(modal) { 
            modal.classList.remove('hidden'); 
            setTimeout(() => { if(content) content.classList.remove('scale-95', 'opacity-0'); }, 10); 
        }
    }

    // Fungsi Plus Minus QTY
    function updateRefundQty(index, change, maxQty, price) {
        let input = document.getElementById(`refund_qty_${index}`);
        let currentVal = parseInt(input.value);
        let newVal = currentVal + change;

        if (newVal >= 0 && newVal <= maxQty) {
            input.value = newVal;
            calculateLiveRefundTotal();
        }
    }

    // Hitung Live Total Uang pas tombol + - dipencet
    function calculateLiveRefundTotal() {
        let total = 0;
        document.querySelectorAll('.refund-qty-input').forEach(input => {
            let qty = parseInt(input.value);
            let price = parseInt(input.getAttribute('data-price'));
            total += (qty * price);
        });
        
        document.getElementById('refundAmount').value = total;
        document.getElementById('refundAmountDisplay').value = 'Rp' + new Intl.NumberFormat('id-ID').format(total);
    }

    function toggleRefundInfo() {
        const typeEl = document.getElementById('refundType');
        if(!typeEl) return;
        const type = typeEl.value;
        const boxPilihBarang = document.getElementById('containerPilihBarang');
        const boxEstimasi = document.getElementById('containerEstimasiDana');

        if (type === 'dana_saja') { 
            if(boxPilihBarang) boxPilihBarang.classList.remove('hidden'); 
            if(boxEstimasi) boxEstimasi.classList.remove('hidden'); 
        } 
        else if (type === 'barang_dana') { 
            if(boxPilihBarang) boxPilihBarang.classList.add('hidden'); 
            if(boxEstimasi) boxEstimasi.classList.add('hidden'); 
        }
        else {
            if(boxPilihBarang) boxPilihBarang.classList.add('hidden'); 
            if(boxEstimasi) boxEstimasi.classList.add('hidden'); 
        }
    }

    function closeItemSelection() {
        const itemModal = document.getElementById('itemSelectionModal');
        if(itemModal) itemModal.classList.add('hidden');
        
        let selectedNames = [];
        document.querySelectorAll('.refund-qty-input').forEach(input => { 
            let qty = parseInt(input.value);
            if(qty > 0) {
                let name = input.getAttribute('data-name');
                selectedNames.push(`${name} (x${qty})`); 
            }
        });
        
        let selContainer = document.getElementById('selectedRefundItemsDisplay');
        if(selContainer) {
            if(selectedNames.length > 0) { 
                selContainer.innerHTML = '<div class="text-xs p-3 bg-gray-50 border border-gray-200 rounded-lg mt-3"><strong>Barang Bermasalah yang dipilih:</strong><ul class="list-disc pl-4 mt-1.5 text-gray-600 space-y-1"><li>' + selectedNames.join('</li><li>') + '</li></ul></div>'; 
            } 
            else { 
                selContainer.innerHTML = ''; 
            }
        }
    }

    function closeRefundModal() {
        const modal = document.getElementById('refundModal');
        const content = document.getElementById('refundModalContent');
        if(content) content.classList.add('scale-95', 'opacity-0');
        if(modal) setTimeout(() => { modal.classList.add('hidden'); document.getElementById('refundForm').reset(); }, 300);
    }

    function submitRefundRequest(e) {
        e.preventDefault();
        const typeEl = document.getElementById('refundType');
        const amountEl = document.getElementById('refundAmount');
        const reasonEl = document.getElementById('refundReason');
        const orderId = document.getElementById('refundOrderId').value;
        
        if (!typeEl || !amountEl || !reasonEl) return;
        
        if(typeEl.value === 'dana_saja' && parseInt(amountEl.value) === 0) {
            alert('Pilih jumlah barang yang bermasalah terlebih dahulu Bre!');
            return;
        }

        const submitBtn = e.target.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Mengunggah Bukti...';
        submitBtn.disabled = true;
        submitBtn.classList.add('opacity-75', 'cursor-not-allowed');

        let formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('type', typeEl.value);
        formData.append('amount', amountEl.value);
        formData.append('reason', reasonEl.value);

        let selectedItems = [];
        document.querySelectorAll('.refund-qty-input').forEach(input => { 
            let qty = parseInt(input.value);
            if(qty > 0) {
                selectedItems.push(`${input.getAttribute('data-name')} (x${qty})`);
            }
        });
        formData.append('items', selectedItems.join(', '));

        const photoFiles = document.getElementById('refundPhotos').files;
        for(let i = 0; i < photoFiles.length; i++) { formData.append('photos[]', photoFiles[i]); }

        const videoFile = document.getElementById('refundVideo').files[0];
        if(videoFile) { formData.append('video', videoFile); }

        fetch(`/user/orders/${orderId}/refund`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                closeRefundModal();
                const modal = document.getElementById('paymentSuccessModal');
                const content = document.getElementById('paymentSuccessContent');
                if(modal && content) {
                    modal.querySelector('h3').innerText = "Pengajuan Terkirim!";
                    modal.querySelector('p').innerText = "Penjual akan meninjau pengajuan kamu.";
                    modal.classList.remove('hidden');
                    setTimeout(() => { content.classList.remove('scale-95', 'opacity-0'); }, 10);
                }
                setTimeout(() => { window.location.reload(); }, 2500);
            } else {
                alert(data.message || 'Gagal mengirim pengajuan.');
                submitBtn.innerHTML = originalBtnText;
                submitBtn.disabled = false; submitBtn.classList.remove('opacity-75', 'cursor-not-allowed');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Gagal! Pastikan ukuran video maksimal 20MB.');
            submitBtn.innerHTML = originalBtnText;
            submitBtn.disabled = false; submitBtn.classList.remove('opacity-75', 'cursor-not-allowed');
        });
    }
</script>
@endpush