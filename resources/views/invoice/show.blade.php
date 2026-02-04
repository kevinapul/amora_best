<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>

    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f5f7f6;
            color: #111827;
            margin: 0;
            padding: 40px;
        }

        .invoice-container {
            max-width: 900px;
            margin: auto;
            background: white;
            padding: 40px;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
        }

        .header h1 {
            margin: 0;
            font-size: 28px;
            letter-spacing: 1px;
        }

        .company-info,
        .invoice-info {
            font-size: 14px;
            line-height: 1.6;
        }

        .invoice-info {
            text-align: right;
        }

        .status {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: bold;
            margin-top: 6px;
        }

        .status.paid {
            background: #dcfce7;
            color: #166534;
        }

        .status.partial {
            background: #fef3c7;
            color: #92400e;
        }

        .status.draft {
            background: #e5e7eb;
            color: #374151;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }

        table th {
            background: #f9fafb;
            text-align: left;
            padding: 12px;
            border-bottom: 2px solid #e5e7eb;
            font-size: 13px;
        }

        table td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 14px;
        }

        .text-right {
            text-align: right;
        }

        .summary {
            margin-top: 30px;
            width: 100%;
            max-width: 360px;
            margin-left: auto;
        }

        .summary table td {
            padding: 8px 0;
        }

        .total {
            font-size: 18px;
            font-weight: bold;
        }

        .footer {
            margin-top: 50px;
            font-size: 12px;
            color: #6b7280;
            text-align: center;
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }

            .invoice-container {
                border: none;
                border-radius: 0;
            }
        }
    </style>
</head>
<body>

<div class="invoice-container">

    {{-- ================= HEADER ================= --}}
    <div class="header">
        <div>
            <h1>INVOICE</h1>
            <div class="company-info">
                <b>Ditagihkan kepada:</b><br>
                {{ $invoice->company->name }}<br>
                {{-- alamat / kontak nanti --}}
            </div>
        </div>

        <div class="invoice-info">
            <div>No. Invoice</div>
            <b>{{ $invoice->invoice_number }}</b><br><br>

            <div>Tanggal Terbit</div>
            <b>{{ optional($invoice->issued_at)->format('d M Y') }}</b><br><br>

            <span class="status {{ $invoice->status }}">
                {{ strtoupper($invoice->status) }}
            </span>
        </div>
    </div>

    {{-- ================= ITEMS ================= --}}
    <table>
        <thead>
            <tr>
                <th style="width: 40px;">#</th>
                <th>Deskripsi</th>
                <th class="text-right" style="width: 140px;">Harga</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->items as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $item->description }}</td>
                    <td class="text-right">
                        Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ================= SUMMARY ================= --}}
    <div class="summary">
        <table>
            <tr>
                <td>Total Tagihan</td>
                <td class="text-right">
                    Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}
                </td>
            </tr>
            <tr>
                <td>Sudah Dibayar</td>
                <td class="text-right">
                    Rp {{ number_format($invoice->paid_amount, 0, ',', '.') }}
                </td>
            </tr>
            <tr>
                <td class="total">Sisa Tagihan</td>
                <td class="text-right total">
                    Rp {{ number_format($invoice->remainingAmount(), 0, ',', '.') }}
                </td>
            </tr>
        </table>
    </div>

    {{-- ================= FOOTER ================= --}}
    <div class="footer">
        Invoice ini dibuat secara otomatis oleh sistem.<br>
        Jika ada pertanyaan, silakan hubungi bagian administrasi.
    </div>

</div>

</body>
</html>
