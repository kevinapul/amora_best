<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Invoice {{ $invoice->invoice_number }}</title>

<style>
body{
    font-family: Arial, Helvetica, sans-serif;
    background:#fff;
    margin:0;
    padding:40px;
    color:#111;
}

.invoice-box{
    width:210mm;
    min-height:297mm;
    margin:auto;
    padding:20mm;
    background:white;
    box-sizing:border-box;
}

/* HEADER */
.top{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    margin-bottom:25px;
}

.company-left{width:60%;}
.company-left img{width:80px;margin-bottom:10px;}

.company-name{
    font-weight:bold;
    font-size:18px;
    margin-bottom:6px;
}

.company-detail{
    font-size:14px;
    line-height:1.6;
}

.invoice-title{
    text-align:right;
    width:40%;
}

.invoice-title h1{
    margin:0;
    font-size:38px;
    letter-spacing:4px;
}

/* INFO BAR */
.info-bar{
    display:grid;
    grid-template-columns:1fr 1fr 1fr;
    margin:30px 0;
}

.info-item{
    border-left:4px solid #0f3d2e;
    padding-left:12px;
    text-align:center;
    font-size:14px;
}

/* TABLE */
table{
    width:100%;
    border-collapse:collapse;
}

thead{
    background:#0f3d2e;
    color:white;
}

th,td{
    padding:12px;
    font-size:14px;
}

td{border-bottom:1px solid #ddd;}

.text-right{text-align:right;}

/* SUMMARY */
.summary{
    width:260px;
    margin-left:auto;
    margin-top:25px;
    border-top:2px solid #0f3d2e;
    padding-top:10px;
}

.summary td{
    padding:4px 0;
    font-size:13px;
}

.total{
    font-weight:700;
    font-size:18px;
}

/* PAYMENT */
.payment{
    margin-top:60px;
    font-size:14px;
}

/* SIGN */
.sign{
    margin-top:80px;
    text-align:right;
}

/* PRINT PERFECT */
@media print{
body{padding:0;margin:0;background:white;}

*{
-webkit-print-color-adjust: exact !important;
print-color-adjust: exact !important;
}

.invoice-box{
    width:210mm;
    min-height:297mm;
    padding:20mm;
    margin:0;
    box-shadow:none;
}

@page{
size:A4;
margin:0;
}
}
</style>
</head>

<body>
<div class="invoice-box">

<!-- HEADER -->
<div class="top">

<div class="company-left">
<img src="{{ asset('img/Alkon.png') }}">
<div class="company-name">PT. ALKON BEST MANDIRI</div>
<div class="company-detail">
Jl. MT. Haryono, Komplek Balikpapan Baru Blok A1/009<br>
NPWP : 085.505.558.8-721.000<br>
📞 +62 542 875565<br>
✉️ accounting@alkonindo.com
</div>
</div>

<div class="invoice-title">
<h1>INVOICE</h1>
<b>Bill to:</b><br>
{{ $invoice->company->name }}
</div>

</div>

<!-- INFO -->
<div class="info-bar">
<div class="info-item">
<b>Invoice #</b><br>
{{ $invoice->invoice_number }}
</div>

<div class="info-item">
<b>Invoice Date</b><br>
{{ now()->format('d F Y') }}
</div>

<div class="info-item">
<b>Invoice Due</b><br>
{{ optional($invoice->due_at)->format('d F Y') }}
</div>
</div>

<!-- TABLE -->
<table>
<thead>
<tr>
<th>Item Description</th>
<th class="text-right">Unit Price</th>
<th class="text-right">Qty</th>
<th class="text-right">Amount</th>
</tr>
</thead>

<tbody>
@foreach($invoice->items as $item)
<tr>

<!-- 🔥 DESKRIPSI DARI CONTROLLER (FINAL) -->
<td>
{{ $item->description }}
</td>

<td class="text-right">
Rp {{ number_format($item->price,0,',','.') }}
</td>

<td class="text-right">
{{ $item->qty }}
</td>

<td class="text-right">
Rp {{ number_format($item->subtotal,0,',','.') }}
</td>

</tr>
@endforeach
</tbody>
</table>

<!-- SUMMARY -->
<div class="summary">
<table>
<tr>
<td>Subtotal</td>
<td class="text-right">
Rp {{ number_format($invoice->total_amount,0,',','.') }}
</td>
</tr>

<tr>
<td>Down Payment (DP)</td>
<td class="text-right">
Rp {{ number_format($invoice->paid_amount,0,',','.') }}
</td>
</tr>

<tr>
<td>Tax</td>
<td class="text-right">Rp 0</td>
</tr>

<tr>
<td class="total">TOTAL</td>
<td class="text-right total">
Rp {{ number_format($invoice->remainingAmount(),0,',','.') }}
</td>
</tr>
</table>
</div>

<!-- PAYMENT -->
<div class="payment">
<b>Payment Method:</b><br>
Account : 149-00-3544984-4<br>
A/C Name : PT. ALKON BEST MANDIRI<br>
Bank Details : BANK MANDIRI
</div>

<!-- SIGN -->
<div class="sign">
Balikpapan, {{ now()->format('d F Y') }}<br><br><br>
<b>H.M. Adenuddin Alwy, SE., MM.</b><br>
Direktur
</div>

</div>
</body>
</html>