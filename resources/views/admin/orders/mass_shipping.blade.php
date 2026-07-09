@extends('admin.layouts.app')

@section('content')
<style>
    /* Styling khusus biar search bar-nya cakep */
    .search-modern {
        border-radius: 8px;
        padding: 8px 16px;
        border: 1px solid #e2e8f0;
        outline: none;
        transition: 0.3s;
        width: 250px;
    }
    .search-modern:focus {
        border-color: #1d7b67;
        box-shadow: 0 0 0 3px rgba(29, 123, 103, 0.1);
    }
    .action-container {
        display: flex;
        gap: 15px;
        align-items: center;
    }
</style>

<section class="content-header pt-4">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="font-weight-bold" style="color: #333;">Pengiriman & Cetak Massal</h2>
            <a href="{{ route('orders.index') }}" class="btn btn-light border" style="border-radius: 8px; font-weight: 500;">
                <i class="fas fa-arrow-left mr-2"></i> Kembali
            </a>
        </div>
    </div>
</section>

<section class="content mt-3">
    <div class="container-fluid">
        @if(Session::has('error'))
            <div class="alert alert-danger" style="border-radius: 8px;">{{ Session::get('error') }}</div>
        @endif

        <div class="card" style="border: none; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
            <form action="{{ route('orders.massPrint') }}" method="POST" target="_blank">
                @csrf
                <div class="card-header bg-white py-3 d-flex align-items-center" style="border-radius: 12px 12px 0 0; border-bottom: 1px solid #f1f5f9;">
    
    <h5 class="m-0 font-weight-bold" style="color: #1d7b67;">
        <i class="fas fa-list-check mr-2"></i>Daftar Pesanan Siap Cetak
    </h5>
    
    <div class="action-container ml-auto d-flex align-items-center">
        
        <div class="position-relative mr-2">
            <i class="fas fa-search position-absolute text-muted" style="left: 12px; top: 50%; transform: translateY(-50%);"></i>
            <input type="text" id="searchInvoice" class="search-modern" style="padding-left: 35px;" placeholder="Cari No. Invoice...">
        </div>

        <button type="submit" class="btn shadow-sm m-0" style="background: #1d7b67; color: #fff; font-weight: 600; border-radius: 8px; padding: 8px 20px; transition: 0.2s;">
            <i class="fas fa-print mr-2"></i> Cetak Label
        </button>
        
    </div>
</div>
                
                <div class="card-body p-0 table-responsive">
                    <table class="table table-hover align-middle mb-0" id="orderTable">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-center" style="width: 50px; border-bottom: 2px solid #e2e8f0;">
                                    <input type="checkbox" id="checkAll" style="transform: scale(1.2); cursor: pointer;">
                                </th>
                                <th style="border-bottom: 2px solid #e2e8f0;">No. Invoice</th>
                                <th style="border-bottom: 2px solid #e2e8f0;">Pembeli</th>
                                <th style="border-bottom: 2px solid #e2e8f0;">Jasa Kirim & Resi</th>
                                <th style="border-bottom: 2px solid #e2e8f0;">Status</th>
                            </tr>
                        </thead>
                        <tbody id="orderTableBody">
                            @forelse($orders as $order)
                            <tr class="order-row">
                                <td class="text-center">
                                    <input type="checkbox" name="order_ids[]" value="{{ $order->id }}" class="checkItem" style="transform: scale(1.2); cursor: pointer;">
                                </td>
                                <td><strong class="invoice-text" style="color: #1d7b67; font-size: 1.05rem;">{{ $order->invoice_number }}</strong></td>
                                <td>
                                    <strong style="color: #334155;">{{ $order->receiver_name }}</strong><br>
                                    <small class="text-muted"><i class="fas fa-map-marker-alt mr-1"></i>{{ $order->city }}, {{ $order->province }}</small>
                                </td>
                                <td>
                                    @if(!empty($order->shipping_method))
                                        <strong style="color: #334155;">{{ $order->shipping_method }}</strong><br>
                                        <small class="text-muted"><i class="fas fa-barcode mr-1"></i>{{ $order->resi_number ?? 'Resi belum diinput' }}</small>
                                    @else
                                        <span class="badge" style="background: #fee2e2; color: #dc2626;"><i class="fas fa-exclamation-circle mr-1"></i>Belum diatur</span>
                                    @endif
                                </td>
                                <td>
                                    @if($order->status == 'processing')
                                        <span class="badge" style="background: #fef3c7; color: #b45309; padding: 6px 10px; border-radius: 6px;"><i class="fas fa-box-open mr-1"></i>Perlu Dikirim</span>
                                    @else
                                        <span class="badge" style="background: #e0e7ff; color: #3730a3; padding: 6px 10px; border-radius: 6px;"><i class="fas fa-truck-fast mr-1"></i>Sudah Dikirim</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr id="emptyRow">
                                <td colspan="5" class="text-center py-5">
                                    <i class="fas fa-box text-muted mb-3" style="font-size: 40px; opacity: 0.5;"></i>
                                    <h6 class="text-muted font-weight-bold">Belum ada pesanan yang siap dicetak.</h6>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </form>
        </div>
    </div>
</section>

@endsection

@section('customJs')
<script>
    // Fitur Check All
    document.getElementById('checkAll').addEventListener('change', function() {
        let checkboxes = document.querySelectorAll('.checkItem');
        checkboxes.forEach(checkbox => {
            // Hanya centang baris yang tidak disembunyikan oleh fitur pencarian
            if(checkbox.closest('tr').style.display !== 'none') {
                checkbox.checked = this.checked;
            }
        });
    });

    // Fitur Live Search Invoice
    document.getElementById('searchInvoice').addEventListener('keyup', function() {
        let filter = this.value.toUpperCase();
        let rows = document.querySelectorAll('.order-row');
        
        rows.forEach(row => {
            let invoiceElement = row.querySelector('.invoice-text');
            if (invoiceElement) {
                let invoiceValue = invoiceElement.textContent || invoiceElement.innerText;
                // Cek apakah ketikan cocok dengan invoice
                if (invoiceValue.toUpperCase().indexOf(filter) > -1) {
                    row.style.display = ""; // Tampilkan baris
                } else {
                    row.style.display = "none"; // Sembunyikan baris
                }
            }
        });
    });
</script>
@endsection