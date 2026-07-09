@extends('admin.layouts.app')

@section('content')
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<style>
    /* Paksa kalender nampil paling depan! */
    .daterangepicker { z-index: 99999 !important; font-family: inherit; }

    /* Override warna Daterangepicker jadi Hijau Panambas */
    /* 1. Warna latar untuk tanggal awal dan akhir (yang dipilih) */
    .daterangepicker td.active, .daterangepicker td.active:hover {
        background-color: #247a6b !important;
        border-color: transparent !important;
        color: #fff !important;
    }

    /* 2. Warna latar untuk tanggal yang ada di tengah-tengah rentang (in-range) */
    .daterangepicker td.in-range {
        background-color: #e6f2f0 !important; /* Hijau pudar banget */
        color: #1b5e52 !important;
        border-radius: 0;
    }

    /* 3. Warna menu pilihan di sebelah kiri (Hari Ini, Bulan Ini, dll) saat Aktif */
    .daterangepicker .ranges li.active {
        background-color: #247a6b !important;
        color: #fff !important;
    }

    /* 4. Warna menu saat disorot mouse (Hover) */
    .daterangepicker .ranges li:hover {
        background-color: #f0f7f5 !important;
        color: #247a6b !important;
    }

    /* 5. Warna Tombol "Terapkan" (Apply) */
    .daterangepicker .btn-primary {
        background-color: #247a6b !important;
        border-color: #247a6b !important;
        color: #fff !important;
        font-weight: bold;
    }
    .daterangepicker .btn-primary:hover {
        background-color: #1b5e52 !important;
        border-color: #1b5e52 !important;
    }
</style>

<section class="content-header">
    <div class="container-fluid my-2">
        <h1 class="text-[22px] font-bold text-gray-800" style="margin:0;">Informasi Pendapatan Toko</h1>
    </div>
</section>

<section class="content pb-10">
    <div class="container-fluid">
        
        <div class="bg-white rounded-[4px] border border-gray-200 shadow-sm p-6 mb-4">
            <div class="w-full">
                <p class="text-[14px] font-medium text-gray-500 mb-2" style="margin-bottom: 0.5rem;">Total Pendapatan Bersih (Telah Masuk Rekening ATM)</p>
                <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                    <span class="text-[32px] font-bold text-gray-800 tracking-tight leading-none">
                        Rp{{ number_format($totalNetBalance ?? 0, 0, ',', '.') }}
                    </span>
                    
                    <a href="https://dashboard.midtrans.com/login" target="_blank" class="bg-[#247a6b] hover:bg-[#1b5e52] text-white px-5 py-2.5 rounded-[3px] text-[13px] font-bold transition duration-200 text-center no-underline w-full sm:w-auto">
                        <i class="fas fa-external-link-alt mr-1"></i> Cek Dashboard Midtrans
                    </a>
                </div>
                <small class="text-xs text-gray-400 block mt-3"><i class="fas fa-info-circle mr-1 text-blue-500"></i> Dana settlement dari pembeli otomatis ditransfer oleh Midtrans ke rekening ATM terdaftar Anda dalam 1-2 hari kerja.</small>
            </div>
        </div>

        <div class="bg-white rounded-[4px] border border-gray-200 shadow-sm p-6 mt-6 mb-5">
            <h2 class="text-[18px] font-bold text-gray-800 mb-6">Riwayat Duit Masuk</h2>

            <form action="{{ route('admin.saldo.index') }}" method="GET" id="filterFinanceForm">
                <div class="space-y-4 text-[13px]">
                    
                    <div class="flex items-center">
                        <div class="w-48 text-gray-600 font-medium">Tanggal Transaksi Cair</div>
                        <div class="flex items-center justify-between border border-gray-300 rounded-[3px] px-3 py-1.5 w-[320px] cursor-pointer hover:border-[#247a6b] bg-white transition relative">
                            <div class="flex items-center text-gray-700 w-full">
                                <i class="far fa-calendar-alt text-gray-400 mr-2"></i>
                                <input type="text" id="saldoDateRange" name="date_range" class="bg-transparent focus:outline-none w-full text-[13px] cursor-pointer" readonly value="{{ $dateDisplay ?? '' }}">
                            </div>
                            <i class="fas fa-chevron-down text-gray-400 text-[10px] absolute right-3 pointer-events-none"></i>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 pt-2">
                        <a href="{{ route('admin.saldo.index') }}" class="px-5 py-1.5 border border-gray-300 rounded-[3px] text-gray-700 hover:bg-gray-50 transition font-medium no-underline">Atur Ulang</a>
                        <button type="submit" class="px-5 py-1.5 bg-[#247a6b] border border-[#247a6b] text-white rounded-[3px] hover:bg-[#1b5e52] transition font-bold">Terapkan Filter</button>
                    </div>
                </div>
            </form>

            <hr class="border-gray-200 my-6">

            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end gap-4 mb-4">
                <div class="text-[16px] font-bold text-gray-800">
                    {{ isset($orders) ? $orders->total() : 0 }} Transaksi Selesai <span class="text-[14px] font-normal text-gray-500 ml-1">(Periode Ini)</span>
                </div>
                <form action="{{ route('admin.saldo.index') }}" method="GET" class="flex space-x-3 w-full sm:w-auto">
                    <input type="hidden" name="date_range" value="{{ request('date_range') }}">
                    <div class="relative flex-1 sm:flex-none">
                        <input type="text" name="keyword" value="{{ request('keyword') }}" placeholder="Cari No. Invoice" class="border border-gray-300 rounded-[3px] px-3 py-1.5 text-[13px] pl-9 w-full sm:w-[260px] focus:border-[#247a6b] focus:ring-1 focus:ring-[#247a6b] outline-none transition">
                        <i class="fas fa-search absolute left-3 top-[10px] text-gray-400 text-[13px]"></i>
                    </div>
                    <button type="submit" class="bg-gray-100 border border-gray-300 rounded-[3px] px-3 text-gray-700 text-[13px] hover:bg-gray-200 transition font-medium">Cari</button>
                </form>
            </div>

            <div class="overflow-x-auto border border-gray-200 rounded-[4px]">
                <table class="w-full text-left text-[13px]">
                    <thead class="bg-gray-50 border-b border-gray-200 text-gray-500">
                        <tr>
                            <th class="px-4 py-3.5 font-bold whitespace-nowrap">Tanggal Selesai</th>
                            <th class="px-4 py-3.5 font-bold">Tipe | Deskripsi Uang Masuk</th>
                            <th class="px-4 py-3.5 font-bold whitespace-nowrap">No. Pesanan / Invoice</th>
                            <th class="px-4 py-3.5 font-bold text-right whitespace-nowrap">Jumlah Bersih Diterima</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        
                        @if(isset($orders))
                            @forelse($orders as $order)
                            @php
                                $feeMidtrans = 4000;
                                $cleanIncome = $order->grand_total - $feeMidtrans;
                                if($cleanIncome < 0) $cleanIncome = 0;
                            @endphp
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-4 text-gray-600 whitespace-nowrap font-mono">
                                    {{ $order->updated_at->format('d-m-Y') }}
                                </td>
                                <td class="px-4 py-4 min-w-[350px]">
                                    <div class="flex items-start space-x-3">
                                        <div class="w-8 h-8 rounded-full bg-[#e6f2f0] flex items-center justify-center shrink-0 mt-0.5">
                                            <i class="fas fa-wallet text-[#247a6b] text-[14px]"></i>
                                        </div>
                                        <div>
                                            <div class="font-bold text-gray-800 leading-tight mb-1">Dana Masuk Pembeli</div>
                                            <div class="text-gray-500 text-[12px] leading-snug">
                                                User ID: {{ $order->user_id }} | Berhasil diselesaikan pembeli.
                                            </div>
                                            <div class="text-red-500 text-[11px] mt-0.5">Estimasi Biaya Gateway Midtrans: -Rp {{ number_format($feeMidtrans, 0, ',', '.') }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <a href="{{ route('orders.detail', $order->id) }}" class="text-[#247a6b] font-bold hover:underline font-mono text-[14px]">
                                        {{ $order->invoice_number }}
                                    </a>
                                </td>
                                <td class="px-4 py-4 text-right font-bold text-green-600 whitespace-nowrap text-[14px]">
                                    +Rp{{ number_format($cleanIncome, 0, ',', '.') }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-5 text-gray-400">
                                    <i class="fas fa-money-check-alt fa-3x mb-3 opacity-40"></i>
                                    <p>Tidak ada riwayat dana masuk pada periode ini, Bre.</p>
                                </td>
                            </tr>
                            @endforelse
                        @endif

                    </tbody>
                </table>
            </div>
            
            @if(isset($orders))
            <div class="d-flex justify-content-end mt-4">
                {{ $orders->appends(request()->query())->links() }}
            </div>
            @endif

        </div>
    </div>
</section>
@endsection

@section('customJs')
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<script>
    $(document).ready(function() {
        if ($.fn.daterangepicker) {
            $('#saldoDateRange').daterangepicker({
                opens: 'left',
                autoUpdateInput: false,
                locale: {
                    format: 'DD/MM/YYYY',
                    applyLabel: "Terapkan",
                    cancelLabel: "Batal",
                    daysOfWeek: ["Mi", "Se", "Se", "Ra", "Ka", "Ju", "Sa"],
                    monthNames: ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Ags", "Sep", "Okt", "Nov", "Des"],
                },
                ranges: {
                    'Hari Ini': [moment(), moment()],
                    'Kemarin': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    '7 Hari Terakhir': [moment().subtract(6, 'days'), moment()],
                    'Bulan Ini': [moment().startOf('month'), moment().endOf('month')],
                }
            });

            $('#saldoDateRange').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
            });
            
            $('#saldoDateRange').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
            });
        }
    });
</script>
@endsection