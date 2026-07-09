@extends('front.layouts.app')

@section('content')
<div class="bg-[#f5f5f5] min-h-[70vh] flex items-center justify-center py-12 px-4">
    <div class="bg-white p-8 md:p-12 rounded-sm shadow-sm border border-gray-200 text-center max-w-lg w-full">
        
        <div class="w-20 h-20 bg-[#e6f2f0] rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="fas fa-check text-4xl text-[#247a6b]"></i>
        </div>
        
        @if($order->payment_status == 'pending' || $order->payment_status == 'unpaid')
            <h2 class="text-2xl font-bold text-gray-800 mb-2">Pesanan Berhasil Dibuat!</h2>
            <p class="text-gray-600 mb-6">Terima kasih telah berbelanja di Panambas. ID Pesanan Anda: <br><strong class="text-gray-900 text-lg">#{{ $order->id }}</strong></p>
            
            <p class="text-lg font-semibold text-gray-800 mt-2">Silakan lakukan pembayaran</p>
            <p class="text-gray-600 mb-8">Pesanan Anda akan kami proses setelah melakukan pembayaran.</p>
        @else
            <h2 class="text-2xl font-bold text-gray-800 mb-2">Pembayaran Berhasil</h2>
            <p class="text-gray-600 mb-6">Terima kasih telah berbelanja di Panambas. ID Pesanan Anda: <br><strong class="text-gray-900 text-lg">#{{ $order->id }}</strong></p>
            
            <p class="text-gray-600 mb-8">Pesanan segera kami proses, silakan cek di halaman pesanan saya untuk melihat status pesanan.</p>
        @endif
        <a href="{{ route('front.pesanan') }}" class="bg-[#247a6b] text-white px-8 py-3 rounded-sm font-bold hover:bg-[#1b5e52] transition shadow-sm w-full inline-block">
            Cek Pesanan Saya
        </a>
        
        <div class="mt-4">
            <a href="{{ route('front.home') }}" class="text-[#247a6b] font-medium hover:underline text-sm">Kembali ke Beranda</a>
        </div>
    </div>
</div>
@endsection