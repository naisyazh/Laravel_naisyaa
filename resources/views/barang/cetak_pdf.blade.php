<!DOCTYPE html>
<html>
<head>
    <title>Cetak Label Harga</title>
    <style>
        @page { margin: 0; }
        body { margin: 10mm; font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        td {
            width: 20%;
            height: 38mm;
            border: 0.1pt solid #eee;
            text-align: center;
            vertical-align: middle;
            font-size: 10px;
            padding: 2px 1px;
        }
        .product-name {
            display: block;
            line-height: 1.2;
        }
        .barcode-wrap {
            display: block;
            width: 100%;
            margin: 2px auto 1px;
            padding: 0 1mm;
            background: #fff;
            box-sizing: border-box;
        }
        .barcode-image {
            display: block;
            width: 100%;
            height: auto;
        }
        .harga { color: #d33; font-weight: bold; font-size: 12px; }
        .id { font-size: 9px; color: #777; display: block; margin-top: 1px; }
        .barcode-note { font-size: 7px; color: #555; display: block; margin-top: 1px; }
    </style>
</head>
<body>
<table>
@php
$counter = 0;
@endphp

@for ($i = 0; $i < 8; $i++)
<tr>
    @for ($j = 0; $j < 5; $j++)
        @php $counter++; @endphp
        <td>
            @if ($counter > $skip && $barangs->count())
                @php $item = $barangs->shift(); @endphp
                <strong class="product-name">{{ $item->nama }}</strong>
                <span class="harga">Rp {{ number_format($item->harga, 0, ',', '.') }}</span><br>
                <div class="barcode-wrap" aria-label="Barcode master toko {{ $item->barcode_display_value }}">
                    <img src="{{ $item->barcode_data_uri }}" alt="Barcode {{ $item->barcode_display_value }}" class="barcode-image">
                </div>
                <span class="id">{{ $item->barcode_display_value }}</span>
                <span class="barcode-note">Barcode barang</span>
            @endif
        </td>
    @endfor
</tr>
@endfor

</table>
</body>
</html>
