<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk #{{ $transaction->id }}</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/struk.css') }}">
</head>
<body>
    <div class="receipt">
        <div class="header">
            <div class="store-name">SANGKURIANG</div>
            <div class="store-info">Jl. Contoh No. 123, Kota</div>
            <div class="store-info">Telp: 0812-3456-7890</div>
        </div>
        
        <div class="receipt-title">
            STRUK PEMBELIAN
        </div>
        
        <div class="info-row">
            <div>No. Transaksi: {{ $transaction->id }}</div>
            <div>{{ $transaction->created_at->format('d/m/Y H:i') }}</div>
        </div>
        
        <div class="divider"></div>
        
        <!-- Items -->
        @foreach($transaction->items as $item)
        <div class="item-row">
            <div class="item-name">{{ $item->product_name }}</div>
            <div class="item-detail">
                <div>{{ $item->quantity }} x {{ number_format($item->price, 0, ',', '.') }}</div>
                <div>{{ number_format($item->subtotal, 0, ',', '.') }}</div>
            </div>
        </div>
        @endforeach
        
        <div class="divider"></div>
        
        <!-- Totals -->
        <div class="totals">
            <div class="total-row grand-total">
                <div>TOTAL:</div>
                <div>Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</div>
            </div>
        </div>
        
        <div class="divider"></div>
        
        <div class="footer">
            <p>Terima Kasih Atas Kunjungan Anda</p>
            <p>Barang yang sudah dibeli tidak dapat dikembalikan</p>
        </div>
    </div>
    
    <div class="print-button no-print" style="text-align: center; margin-top: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; background-color: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer;">
            Cetak Struk
        </button>
        <button onclick="window.close()" style="padding: 10px 20px; background-color: #f44336; color: white; border: none; border-radius: 4px; cursor: pointer; margin-left: 10px;">
            Tutup
        </button>
    </div>
    
    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>