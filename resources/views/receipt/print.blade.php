

<!DOCTYPE html>
<html>
<head>
    <title>Receipt</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            width: 80mm;
            margin: 0 auto;
            padding: 5mm;
            font-size: 12px;
        }
        .receipt-header {
            text-align: center;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        .receipt-total {
            margin-top: 10px;
            border-top: 1px dashed #000;
            padding-top: 10px;
            font-weight: bold;
        }
        .receipt-footer {
            margin-top: 20px;
            text-align: center;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 3px 0;
        }
        h2 {
            margin: 0;
            font-size: 16px;
        }
        p {
            margin: 5px 0;
        }
        .divider {
            border-top: 1px dashed #000;
            margin: 5px 0;
        }
        @media print {
            @page {
                margin: 0;
                size: 80mm auto;
            }
            body {
                margin: 0;
                padding: 5mm;
            }
        }
    </style>
</head>
<body>
    <div class="receipt-header">
        <h2>NAMA TOKO ANDA</h2>
        <p>Alamat Toko, Kota, Kode Pos</p>
        <p>Telp: 0812-3456-7890</p>
        <div class="divider"></div>
        <p>{{ date('d F Y') }}</p>
        <p>{{ date('H:i:s') }}</p>
        <p>No. Transaksi: {{ $invoice }}</p>
    </div>
    
    <table>
        <tr>
            <th style="text-align: left;">Item</th>
            <th style="text-align: right;">Harga</th>
            <th style="text-align: center;">Qty</th>
            <th style="text-align: right;">Subtotal</th>
        </tr>
        <tr>
            <td colspan="4">--------------------------------</td>
        </tr>
        @foreach($items as $item)
        <tr>
            <td style="text-align: left;">{{ $item['nama'] }}</td>
            <td style="text-align: right;">Rp{{ number_format($item['harga'], 0, ',', '.') }}</td>
            <td style="text-align: center;">{{ $item['jumlah'] }}x</td>
            <td style="text-align: right;">Rp{{ number_format($item['subtotal'], 0, ',', '.') }}</td>
        </tr>
        @endforeach
        <tr>
            <td colspan="4">--------------------------------</td>
        </tr>
    </table>
    
    <div class="receipt-total">
        <table>
            <tr>
                <td style="text-align: left; font-weight: bold;">TOTAL</td>
                <td style="text-align: right; font-weight: bold;">Rp{{ number_format($total, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>
    
    <div class="receipt-footer">
        <p>Terima kasih telah berbelanja</p>
        <p>Barang yang sudah dibeli tidak dapat ditukar/dikembalikan</p>
        <p>*** Semoga hari Anda menyenangkan ***</p>
    </div>
    
    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>