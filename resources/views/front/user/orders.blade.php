@extends('front.layouts.app')

@section('title', 'Pesanan Saya - Panambas')

@push('styles')
<style>
    /* Bikin scrollbar horizontal hilang tapi tetep bisa digeser mulus di HP */
    .hide-scroll::-webkit-scrollbar { display: none; }
    .hide-scroll { -ms-overflow-style: none; scrollbar-width: none; overflow-x: auto; -webkit-overflow-scrolling: touch; }

    /* Animasi Ceklis Sukses */
    .success-checkmark {
        width: 80px; height: 80px; margin: 0 auto;
        border-radius: 50%; display: block; stroke-width: 2;
        stroke: #247a6b; stroke-miterlimit: 10; margin-bottom: 20px;
        box-shadow: inset 0px 0px 0px #247a6b;
        animation: fill .4s ease-in-out .4s forwards, scale .3s ease-in-out .9s both;
    }
    .checkmark__circle {
        stroke-dasharray: 166; stroke-dashoffset: 166; stroke-width: 2; stroke-miterlimit: 10;
        stroke: #247a6b; fill: none;
        animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
    }
    .checkmark__check {
        transform-origin: 50% 50%; stroke-dasharray: 48; stroke-dashoffset: 48;
        animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards;
    }
    @keyframes stroke { 100% { stroke-dashoffset: 0; } }
    @keyframes scale { 0%, 100% { transform: none; } 50% { transform: scale3d(1.1, 1.1, 1); } }
    @keyframes fill { 100% { box-shadow: inset 0px 0px 0px 30px #e6f2f0; } }

    /* Custom Scrollbar untuk Modal biar rapi */
    .modal-scroll::-webkit-scrollbar { width: 6px; }
    .modal-scroll::-webkit-scrollbar-track { background: #f8f9fa; border-radius: 10px; }
    .modal-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .modal-scroll::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>
<script type="text/javascript" src="{{ env('MIDTRANS_IS_PRODUCTION') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}" data-client-key="{{ env('MIDTRANS_CLIENT_KEY') }}"></script>
@endpush

@section('content')
<div class="bg-[#f9f9f9] min-h-screen py-4 md:py-8">
    <div class="max-w-7xl mx-auto px-0 md:px-8">
        <div class="flex flex-col md:flex-row gap-0 md:gap-8">
            
            <div class="hidden md:block w-full md:w-1/4 px-4 md:px-0 mb-4 md:mb-0">
                <div class="flex items-center gap-4 mb-8">
                    <div class="w-14 h-14 rounded-full bg-white shadow-sm border border-gray-100 flex items-center justify-center overflow-hidden">
                        <i class="fas fa-user text-gray-400 text-2xl"></i>
                    </div>
                    <div>
                        <div class="font-bold text-gray-900 text-lg">{{ Auth::user()->name }}</div>
                        <a href="{{ route('user.profile') }}" class="text-xs text-gray-500 hover:text-[#247a6b] transition"><i class="fas fa-pen mr-1"></i> Ubah Profil</a>
                    </div>
                </div>

                <div class="bg-white rounded-md border border-gray-100 p-2 shadow-sm">
                    <a href="{{ route('user.profile') }}" class="flex items-center px-4 py-3 text-gray-600 hover:text-[#247a6b] hover:bg-gray-50 transition rounded-md">
                        <i class="far fa-user-circle w-6 text-center mr-3 text-lg"></i> Akun Saya
                    </a>
                    <a href="{{ route('user.orders') }}" class="flex items-center px-4 py-3 text-[#247a6b] font-medium bg-[#f0f7f5] rounded-md transition mt-1">
                        <i class="fas fa-clipboard-list w-6 text-center mr-3 text-lg"></i> Pesanan Saya
                    </a>
                </div>
            </div>

            <div class="w-full md:w-3/4">
                
                @if(Session::has('success'))
                <div class="bg-[#f0f7f5] border border-[#247a6b] text-[#247a6b] px-4 py-3 rounded relative mb-4 shadow-sm mx-4 md:mx-0" role="alert">
                    <span class="block sm:inline font-medium">{{ Session::get('success') }}</span>
                </div>
                @endif
                @if(Session::has('error'))
                <div class="bg-red-50 border border-red-500 text-red-600 px-4 py-3 rounded relative mb-4 shadow-sm mx-4 md:mx-0" role="alert">
                    <span class="block sm:inline font-medium">{{ Session::get('error') }}</span>
                </div>
                @endif

                <div class="bg-white rounded-md border border-gray-100 mb-4 sticky top-[60px] md:top-20 z-40 shadow-sm mx-0 md:mx-0 relative">
                    <button onclick="document.getElementById('tabMenuPesanan').scrollBy({left: -150, behavior: 'smooth'})" class="md:hidden absolute left-0 top-0 bottom-0 w-10 flex items-center justify-center bg-gradient-to-r from-white via-white/80 to-transparent z-10 text-gray-400 hover:text-[#247a6b] focus:outline-none">
                        <i class="fas fa-chevron-left text-[10px] bg-white rounded-full p-1.5 shadow-sm border border-gray-100"></i>
                    </button>

                    <div id="tabMenuPesanan" class="flex md:justify-between overflow-x-auto hide-scroll w-full snap-x scroll-smooth relative z-0 px-2 md:px-0">
                        <a href="{{ route('user.orders') }}" class="snap-start flex-none md:flex-1 text-center px-4 py-4 text-sm whitespace-nowrap transition {{ request('status') == '' ? 'font-bold text-[#247a6b] border-b-2 border-[#247a6b]' : 'font-medium text-gray-500 hover:text-[#247a6b]' }}">Semua</a>
                        <a href="{{ route('user.orders', ['status' => 'pending']) }}" class="snap-start flex-none md:flex-1 text-center px-4 py-4 text-sm whitespace-nowrap transition {{ request('status') == 'pending' ? 'font-bold text-[#247a6b] border-b-2 border-[#247a6b]' : 'font-medium text-gray-500 hover:text-[#247a6b]' }}">Belum Bayar</a>
                        <a href="{{ route('user.orders', ['status' => 'processing']) }}" class="snap-start flex-none md:flex-1 text-center px-4 py-4 text-sm whitespace-nowrap transition {{ request('status') == 'processing' ? 'font-bold text-[#247a6b] border-b-2 border-[#247a6b]' : 'font-medium text-gray-500 hover:text-[#247a6b]' }}">Sedang Dikemas</a>
                        <a href="{{ route('user.orders', ['status' => 'shipped']) }}" class="snap-start flex-none md:flex-1 text-center px-4 py-4 text-sm whitespace-nowrap transition {{ request('status') == 'shipped' ? 'font-bold text-[#247a6b] border-b-2 border-[#247a6b]' : 'font-medium text-gray-500 hover:text-[#247a6b]' }}">Dikirim</a>
                        <a href="{{ route('user.orders', ['status' => 'completed']) }}" class="snap-start flex-none md:flex-1 text-center px-4 py-4 text-sm whitespace-nowrap transition {{ request('status') == 'completed' ? 'font-bold text-[#247a6b] border-b-2 border-[#247a6b]' : 'font-medium text-gray-500 hover:text-[#247a6b]' }}">Selesai</a>
                        <a href="{{ route('user.orders', ['status' => 'cancelled']) }}" class="snap-start flex-none md:flex-1 text-center px-4 py-4 text-sm whitespace-nowrap transition {{ request('status') == 'cancelled' ? 'font-bold text-[#247a6b] border-b-2 border-[#247a6b]' : 'font-medium text-gray-500 hover:text-[#247a6b]' }}">Dibatalkan</a>
                        <a href="{{ route('user.orders', ['status' => 'refunded']) }}" class="snap-start flex-none md:flex-1 text-center px-4 py-4 text-sm whitespace-nowrap transition {{ in_array(request('status'), ['refund_processing', 'refunded']) ? 'font-bold text-[#247a6b] border-b-2 border-[#247a6b]' : 'font-medium text-gray-500 hover:text-[#247a6b]' }}">Pengembalian</a>
                    </div>

                    <button onclick="document.getElementById('tabMenuPesanan').scrollBy({left: 150, behavior: 'smooth'})" class="md:hidden absolute right-0 top-0 bottom-0 w-10 flex items-center justify-center bg-gradient-to-l from-white via-white/80 to-transparent z-10 text-gray-400 hover:text-[#247a6b] focus:outline-none">
                        <i class="fas fa-chevron-right text-[10px] bg-white rounded-full p-1.5 shadow-sm border border-gray-100"></i>
                    </button>
                </div>

                <form action="{{ route('user.orders') }}" method="GET" class="bg-white rounded-md mb-6 px-4 py-3 mx-4 md:mx-0 flex items-center border border-gray-200 focus-within:border-[#247a6b] focus-within:ring-1 focus-within:ring-[#247a6b] transition shadow-sm">
                    <input type="hidden" name="status" value="{{ request('status') }}">
                    <input type="text" name="keyword" value="{{ request('keyword') }}" placeholder="Cari nama produk atau nomor pesanan..." class="bg-transparent w-full focus:outline-none text-sm text-gray-800 placeholder-gray-400">
                </form>

                <div class="px-0 md:px-0"> 
                    @forelse($orders as $order)
                    <div class="bg-white rounded-md border border-gray-200 mb-6 overflow-hidden shadow-sm hover:shadow-md transition">
                        
                        <div class="flex justify-between items-center p-4 border-b border-gray-100 bg-white">
                            <div class="flex items-center flex-wrap gap-2">
                                <span class="bg-[#1b5e52] text-white text-[10px] tracking-wider font-bold px-2 py-1 rounded-sm uppercase flex items-center">Official</span>
                                <span class="font-bold text-gray-900 text-sm">Panambas</span>
                                <a href="{{ route('user.orders.detail', $order->id) }}" class="text-xs text-gray-600 font-mono bg-gray-100 px-2 py-1 rounded border border-gray-200 hover:text-[#247a6b] hover:border-[#247a6b] hover:bg-white transition cursor-pointer" title="Lihat Detail Pesanan">
                                    {{ $order->invoice_number }}
                                </a>
                            </div>
                            <div class="text-xs md:text-sm font-bold flex items-center tracking-wide uppercase ml-2">
                                @if($order->status == 'pending')
                                    <span class="text-red-600">Belum Bayar</span>
                                @elseif($order->status == 'processing')
                                    @if($order->cancel_request_status == 'requested')
                                        <span class="text-yellow-600">Menunggu Konfirmasi Batal</span>
                                    @else
                                        <span class="text-[#247a6b]">Sedang Dikemas</span>
                                    @endif
                                @elseif($order->status == 'shipped')
                                    <span class="text-blue-600">Dikirim</span>
                                @elseif($order->status == 'refund_processing')
                                    <span class="text-orange-500">Proses Pengajuan</span>
                                @elseif($order->status == 'completed')
                                    <span class="text-[#247a6b]">Selesai</span>
                                @elseif($order->status == 'refunded')
                                    <span class="text-orange-500">Dikembalikan</span>
                                @else
                                    <span class="text-red-500">Dibatalkan</span>
                                @endif
                            </div>
                        </div>

                        @php 
                            $daftarBarang = $order->orderItems ?? $order->items ?? []; 
                        @endphp
                        
                        @foreach($daftarBarang as $item)
                        <a href="{{ route('front.product', $item->product_id ?? '#') }}" class="block hover:bg-gray-50 transition">
                            <div class="p-5 flex flex-col md:flex-row gap-5 border-b border-gray-50">
                                <div class="w-20 h-20 bg-gray-100 rounded-md overflow-hidden flex-shrink-0 border border-gray-200 shadow-sm">
                                    @if(isset($item->product) && $item->product->images->isNotEmpty())
                                        <img src="{{ asset('storage/'.$item->product->images->first()->image_path) }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-gray-300">Image</div>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-[15px] font-semibold text-gray-900 line-clamp-2 leading-snug">{{ $item->product_name ?? 'Produk Dihapus' }}</h3>
                                    @if(!empty($item->variation_name))
                                        <p class="text-xs text-gray-500 mt-1.5">Variasi: <span class="font-medium text-gray-700">{{ $item->variation_name }}</span></p>
                                    @endif
                                    <p class="text-sm text-gray-700 mt-2 font-medium">x{{ $item->qty ?? 1 }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-[15px] text-[#247a6b] font-bold">Rp{{ number_format($item->price ?? 0, 0, ',', '.') }}</p>
                                </div>
                            </div>
                        </a>
                        @endforeach

                        <div class="p-5 flex flex-col items-end bg-white gap-4">
                            <div class="w-full text-right text-sm text-gray-500 font-medium border-b border-gray-100 pb-4">
                                Total Pesanan: <span class="text-xl text-[#247a6b] font-bold ml-2">Rp{{ number_format($order->grand_total, 0, ',', '.') }}</span>
                            </div>
                            
                            <div class="flex flex-col sm:flex-row flex-wrap justify-end gap-3 w-full">
                                
                                @if($order->status == 'pending')
                                    <button type="button" onclick="openCancelModal({{ $order->id }}, 'pending')" class="flex-1 px-4 py-3 border border-gray-300 text-gray-700 font-bold text-sm rounded-lg hover:bg-gray-50 transition shadow-sm text-center">Batalkan Pesanan</button>
                                    <button type="button" onclick="payMidtrans('{{ $order->snap_token }}')" class="flex-1 px-4 py-3 bg-[#247a6b] text-white font-bold text-sm rounded-lg hover:bg-[#1b5e52] transition shadow-md text-center">Bayar Sekarang</button>
                                
                                @elseif($order->status == 'processing')
                                    @if($order->cancel_request_status == 'requested')
                                        <button type="button" onclick="cancelCancellationRequest({{ $order->id }}, this)" class="flex-1 px-4 py-3 border border-red-500 text-red-600 font-bold text-sm rounded-lg hover:bg-red-50 transition shadow-sm bg-white text-center">Batalkan Pengajuan</button>
                                    @elseif(empty($order->cancel_request_status))
                                        <button type="button" onclick="openCancelModal({{ $order->id }}, 'processing')" class="flex-1 px-4 py-3 border border-gray-300 text-gray-700 font-bold text-sm rounded-lg hover:bg-gray-50 transition shadow-sm text-center">Batalkan Pesanan</button>
                                    @endif
                                
                                @elseif($order->status == 'shipped')
    <a href="https://cekresi.com/?noresi={{ $order->tracking_number }}" target="_blank" class="flex-1 px-4 py-3 border border-[#247a6b] text-[#247a6b] font-bold text-sm rounded-lg hover:bg-[#f0f7f5] transition shadow-sm bg-white text-center">Lacak Paket</a>
    <form action="{{ route('user.orders.complete', $order->id) }}" method="POST" class="flex-1 flex">
        @csrf @method('PUT')
        <button type="button" onclick="this.innerHTML='<i class=\'fas fa-spinner fa-spin mr-2\'></i> Memproses...'; this.style.pointerEvents='none'; this.classList.add('opacity-75'); this.form.submit();" class="w-full px-4 py-3 bg-[#247a6b] text-white font-bold text-sm rounded-lg hover:bg-[#1b5e52] transition shadow-md text-center">Pesanan Diterima</button>
    </form>
                                @elseif($order->status == 'refund_processing')
                                    <!-- FIX: Tombol batalkan pengajuan (dikirim parameter id) -->
                                    <button type="button" onclick="cancelRefundRequest({{ $order->id }}, this)" class="flex-1 px-4 py-3 border border-red-500 text-red-600 font-bold text-sm rounded-lg hover:bg-red-50 transition shadow-sm bg-white text-center">Batalkan Pengajuan</button>

                                @elseif($order->status == 'completed')
                                    @php
                                        $idProduk = 0;
                                        if (isset($order->orderItems) && $order->orderItems->isNotEmpty()) { $idProduk = $order->orderItems->first()->product_id; } 
                                        elseif (isset($order->items) && $order->items->isNotEmpty()) { $idProduk = $order->items->first()->product_id; } 
                                        else { $idProduk = $order->product_id ?? 0; }

                                        $cekRating = \App\Models\ProductRating::where('product_id', $idProduk)->where('email', Auth::user()->email ?? '')->first();

                                        $batasRefund = \Carbon\Carbon::parse($order->updated_at)->addDays(3);
                                        $bisaRefund = now()->lessThanOrEqualTo($batasRefund);
                                        
                                        // FIX: Masukin kondisi buat cek apakah pernah ngajuin refund (ditarik pembeli/ditolak seller)
                                        $pernahNgajuinBatalRefund = in_array($order->cancel_request_status, ['undone', 'rejected']) || in_array($order->refund_status ?? '', ['undone', 'rejected', 'cancelled']); 
                                    @endphp

                                    @if(!($order->is_refunded ?? false) && $bisaRefund && !$pernahNgajuinBatalRefund)
                                        @php
                                            $refundItems = [];
                                            foreach($daftarBarang as $itm) {
                                                $refundItems[] = ['name' => $itm->product_name ?? 'Produk', 'price' => $itm->price ?? 0, 'qty' => $itm->qty ?? 1];
                                            }
                                        @endphp
                                       <button type="button" data-items="{{ json_encode($refundItems) }}" onclick="openRefundModal({{ $order->id }}, '{{ $order->invoice_number }}', this)" class="flex-1 px-4 py-3 border border-red-500 text-red-600 font-bold text-sm rounded-lg hover:bg-red-50 transition shadow-sm bg-white text-center">Ajukan Pengembalian</button> 
                                    @endif

                                    @if($cekRating)
                                        <button type="button" onclick="openRatingModal({{ $idProduk }}, true)" class="flex-1 px-4 py-3 border border-[#247a6b] text-[#247a6b] font-bold text-sm rounded-lg hover:bg-[#f0f7f5] transition shadow-sm bg-white text-center">Edit Penilaian</button>
                                    @else
                                        <button type="button" onclick="openRatingModal({{ $idProduk }}, false)" class="flex-1 px-4 py-3 bg-[#247a6b] text-white font-bold text-sm rounded-lg hover:bg-[#1b5e52] transition shadow-md text-center">Beri Penilaian</button>
                                    @endif

                                    <a href="{{ route('front.shop') }}" class="flex-1 px-4 py-3 border border-gray-300 text-gray-700 font-bold text-sm rounded-lg hover:bg-gray-50 transition shadow-sm text-center">Beli Lagi</a>

                                @elseif($order->status == 'cancelled')
                                    <a href="{{ route('user.orders.detail', $order->id) }}" class="flex-1 px-4 py-3 border border-gray-300 text-gray-700 font-bold text-sm rounded-lg hover:bg-gray-50 transition shadow-sm text-center block w-full sm:w-auto">Lihat Rincian Batal</a>
                                
                                @elseif($order->status == 'refunded')
                                    <a href="{{ route('user.orders.detail', $order->id) }}" class="flex-1 px-4 py-3 border border-gray-300 text-gray-700 font-bold text-sm rounded-lg hover:bg-gray-50 transition shadow-sm text-center block w-full sm:w-auto">Detail Pengembalian</a>
                                @endif
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="bg-white rounded-md border border-gray-200 p-10 text-center flex flex-col items-center justify-center shadow-sm mx-4 md:mx-0">
                        <div class="w-24 h-24 bg-gray-50 rounded-full flex items-center justify-center mb-4 border border-gray-100">
                            <i class="fas fa-clipboard-list text-4xl text-gray-300"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-1">Belum Ada Pesanan</h3>
                        <p class="text-gray-500 text-sm mb-6">Kamu belum punya pesanan dengan status ini nih.</p>
                        <a href="{{ route('front.shop') }}" class="px-6 py-2.5 bg-[#247a6b] text-white font-bold text-sm rounded-md hover:bg-[#1b5e52] transition shadow-md">Mulai Belanja</a>
                    </div>
                    @endforelse

                    <div class="mt-4 mx-4 md:mx-0">
                        {{ $orders->appends(request()->query())->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<div id="cancelModal" class="fixed inset-0 bg-black/60 z-[9999] hidden flex items-center justify-center backdrop-blur-sm transition-opacity px-4 py-6">
    <div class="bg-white w-full max-w-lg rounded-xl shadow-2xl flex flex-col max-h-[90vh] transform transition-all scale-95 opacity-0" id="cancelModalContent">
        <div class="flex justify-between items-center p-5 border-b border-gray-100 shrink-0">
            <h3 class="text-lg font-bold text-gray-800">Batalkan Pesanan</h3>
            <button type="button" onclick="closeCancelModal()" class="text-gray-400 hover:text-red-500">Tutup</button>
        </div>
        
        <div class="p-5 overflow-y-auto modal-scroll grow">
            <form id="cancelForm" action="{{ route('user.orders.cancel') }}" method="POST">
                @csrf
                <input type="hidden" id="cancelOrderId" name="order_id">

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

                <div class="bg-yellow-50 text-yellow-800 p-3 rounded-lg text-xs mb-5 leading-relaxed hidden" id="cancelWarningPaid">
                    Karena pesanan sudah dibayar, pembatalan <strong>membutuhkan persetujuan penjual</strong>.
                </div>

                <div class="flex gap-3 mt-2">
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
            <button type="button" onclick="closeRatingModal()" class="text-gray-400 hover:text-red-500">Tutup</button>
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
            <button type="button" onclick="closeRefundModal()" class="text-gray-400 hover:text-red-500">Tutup</button>
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
                            modal.querySelector('p').innerText = "Pesanan kamu akan dilanjutkan secara normal.";
                            modal.classList.remove('hidden');
                            setTimeout(() => { content.classList.remove('scale-95', 'opacity-0'); }, 10);
                        }
                        setTimeout(() => { window.location.reload(); }, 2500);
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
                selectedNames.push(`${name} (x${qty})`); // Bikin string Gunting Rumput (x2)
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

        // Kumpulin nama barang + qty-nya
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