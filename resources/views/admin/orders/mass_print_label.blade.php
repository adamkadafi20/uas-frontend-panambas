<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Resi Massal</title>
    <style>
    body { font-family: 'Courier New', Courier, monospace; background: #eee; margin: 0; padding: 20px; }
    .label-container { width: 100%; max-width: 100mm; background: #fff; border: 2px dashed #333; margin: 0 auto; padding: 15px; color: #000; box-sizing: border-box; }
    .page-break { page-break-after: always; margin-bottom: 30px; }
    .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 15px; }
    .header h2 { margin: 0; font-size: 24px; font-weight: bold; text-transform: uppercase; }
    .kurir-box { border: 2px solid #000; padding: 10px; text-align: center; margin-bottom: 15px; font-weight: bold; font-size: 20px;}
    .section-title { font-weight: bold; text-decoration: underline; margin-bottom: 5px; font-size: 14px;}
    .info-box { margin-bottom: 15px; font-size: 14px; line-height: 1.5; }
    .product-table { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 12px; }
    .product-table th, .product-table td { border: 1px solid #000; padding: 5px; text-align: left; }
    
    @media print {
        @page { size: 100mm 150mm; margin: 0; }
        body { background: #fff; padding: 0; margin: 0; }
        .label-container { border: none; width: 100mm; height: 148mm; max-width: 100%; margin: 0; padding: 5mm; box-shadow: none; }
        .page-break { margin-bottom: 0; }
        .no-print { display: none; }
    }
    </style>
</head>
<body onload="window.print()">

    @php 
        $store = \App\Models\StoreSetting::first(); 
    @endphp

    <div class="no-print" style="text-align: center; margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 16px; cursor: pointer; background: #1d7b67; color: #fff; border: none; border-radius: 5px;">Cetak Sekarang</button>
    </div>

    @foreach($orders as $order)
    <div class="page-break">
        <div class="label-container">
            
            <div class="header">
                <h2>{{ strtoupper($store->sender_name ?? 'PANAMBAS') }}</h2>
                <small>Invoice: {{ $order->invoice_number }}</small>
            </div>

            <div class="kurir-box">
                {{ strtoupper($order->shipping_method ?? 'BELUM DIATUR') }}<br>
                <span style="font-size: 16px;">RESI: {{ $order->resi_number ?? '-' }}</span>
            </div>

            <div class="info-box">
                <div class="section-title">PENGIRIM:</div>
                <strong>{{ $store->sender_name ?? 'Panambas Store' }}</strong> &nbsp; {{ $store->sender_phone ?? '' }}<br>
                {{ $store->detail_address ?? '' }}<br>
                {{ $store->district ?? '' }}, {{ $store->city ?? '' }}, {{ strtoupper($store->province ?? '') }}, {{ $store->postal_code ?? '' }}
            </div>

            <div class="info-box">
                <div class="section-title">PENERIMA:</div>
                <strong>{{ strtoupper($order->receiver_name) }}</strong><br>
                {{ $order->phone }}<br>
                {{ $order->full_address }}, {{ $order->city }}, {{ $order->province }}
            </div>

            <table class="product-table">
                <thead>
                    <tr>
                        <th>Qty</th>
                        <th>Nama Produk</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items ?? $order->orderItems as $item)
                    <tr>
                        <td style="text-align: center;"><strong>{{ $item->qty }}x</strong></td>
                        <td>{{ $item->product_name }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            
        </div>
    </div>
    @endforeach
</body>
</html>