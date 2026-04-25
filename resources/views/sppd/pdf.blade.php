<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Rekap SPPD — {{ $sppd->nama_driver }}</title>
    <style>
        @font-face {
            font-family: 'Arial';
            src: url('{{ public_path("fonts/ARIAL.TTF") }}') format('truetype');
            font-weight: normal;
            font-style: normal;
        }
        @font-face {
            font-family: 'Arial';
            src: url('{{ public_path("fonts/ARIALBD.TTF") }}') format('truetype');
            font-weight: bold;
            font-style: normal;
        }
        @page { margin: 28px 32px; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Arial', sans-serif; font-size: 9.5pt; color: #1a1a2e; line-height: 1.45; padding: 8px; }
        .header { width: 100%; margin-bottom: 12px; border-bottom: 3px solid #002a7a; padding-bottom: 10px; }
        .header-table { width: 100%; border-collapse: collapse; }
        .header-logo { width: 220px; max-height: 70px; object-fit: contain; }
        .header-title { font-size: 12pt; font-weight: 700; color: #002a7a; }
        .header-sub { font-size: 8.5pt; color: #64748b; margin-top: 2px; }
        .section-heading { font-size: 10pt; font-weight: 700; color: #002a7a; margin: 12px 0 6px; border-left: 3px solid #ffd300; padding-left: 8px; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .info-table td { border: 1px solid #d1d5db; padding: 5px 8px; vertical-align: top; font-size: 9pt; }
        .info-table .label { font-weight: 700; background: #f3f4f6; width: 28%; }
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; font-size: 8.5pt; }
        .data-table th, .data-table td { border: 1px solid #d1d5db; padding: 5px 6px; text-align: left; }
        .data-table th { background: #f1f5f9; font-weight: 700; }
        .totals { width: 100%; max-width: 280px; margin-left: auto; margin-top: 8px; border-collapse: collapse; font-size: 9pt; }
        .totals td { padding: 4px 8px; border: 1px solid #d1d5db; }
        .totals .label { background: #f3f4f6; font-weight: 700; }
        .sig-wrap { margin-top: 16px; text-align: center; page-break-inside: avoid; }
        .sig-wrap img { max-height: 80px; max-width: 220px; object-fit: contain; }
        .sig-label { font-weight: 700; font-size: 9pt; margin-bottom: 4px; }
        .thumb { max-width: 120px; max-height: 90px; object-fit: cover; border: 1px solid #d1d5db; margin: 2px 4px 2px 0; }
    </style>
</head>
<body>
@php
    use Illuminate\Support\Facades\Storage;
    $pdfImg = function (?string $rel): string {
        if (! $rel || ! Storage::disk('public')->exists($rel)) {
            return '';
        }
        $path = Storage::disk('public')->path($rel);
        $mime = @mime_content_type($path) ?: 'image/jpeg';
        $b64 = base64_encode((string) file_get_contents($path));

        return 'data:'.$mime.';base64,'.$b64;
    };
    $tgl = $sppd->tanggal_dinas?->format('d/m/Y');
@endphp

<div class="header">
    <table class="header-table">
        <tr>
            <td style="width:42%;vertical-align:middle;">
                <img class="header-logo" src="{{ public_path('images/ADCPM Landscape NEW.png') }}" alt="Logo">
            </td>
            <td style="width:58%;text-align:right;vertical-align:middle;">
                <div class="header-title">REKAP SPPD</div>
                <div class="header-sub">PT ARTHA DAYA COALINDO — Vehicle Management System</div>
                <div class="header-sub" style="margin-top:4px">No. Ref: SPPD-{{ str_pad((string) $sppd->id, 5, '0', STR_PAD_LEFT) }}</div>
            </td>
        </tr>
    </table>
</div>

<div class="section-heading">Data Dinas</div>
<table class="info-table">
    <tr><td class="label">Nama Driver</td><td>{{ $sppd->nama_driver }}</td></tr>
    <tr><td class="label">Keperluan Dinas</td><td>{{ $sppd->keperluan_dinas }}</td></tr>
    <tr><td class="label">Nomor Kendaraan</td><td>{{ $sppd->no_kendaraan }}</td></tr>
    <tr><td class="label">Jenis Kendaraan</td><td>{{ $sppd->jenis_kendaraan }}</td></tr>
    <tr><td class="label">Tanggal Dinas</td><td>{{ $tgl }}</td></tr>
    <tr><td class="label">Tujuan</td><td>{{ $sppd->tujuan }}</td></tr>
</table>

@if($sppd->tolls->isNotEmpty())
<div class="section-heading">Biaya Tol</div>
<table class="data-table">
    <thead><tr><th>Dari Tol</th><th>Ke Tol</th><th>Harga (Rp)</th></tr></thead>
    <tbody>
        @foreach($sppd->tolls as $t)
        <tr>
            <td>{{ $t->dari_tol }}</td>
            <td>{{ $t->ke_tol }}</td>
            <td>{{ number_format((float) $t->harga, 0, ',', '.') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

<div class="section-heading">BBM</div>
<table class="data-table">
    <thead><tr><th>Liter</th><th>Harga/L</th><th>Total</th><th>Foto</th></tr></thead>
    <tbody>
        @foreach($sppd->fuels as $f)
        <tr>
            <td>{{ number_format((float) $f->liter, 2, ',', '.') }}</td>
            <td>{{ number_format((float) $f->harga_per_liter, 0, ',', '.') }}</td>
            <td>{{ number_format((float) $f->total, 0, ',', '.') }}</td>
            <td>
                @php $iu = $pdfImg($f->odometer_path); $su = $pdfImg($f->struk_path); @endphp
                @if($iu)<img class="thumb" src="{{ $iu }}" alt="Odo">@endif
                @if($su)<img class="thumb" src="{{ $su }}" alt="Struk">@endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

<table class="totals">
    <tr><td class="label">Total Tol</td><td>Rp {{ number_format((float) $sppd->total_tol, 0, ',', '.') }}</td></tr>
    <tr><td class="label">Total BBM</td><td>Rp {{ number_format((float) $sppd->total_bbm, 0, ',', '.') }}</td></tr>
    <tr><td class="label">Grand Total</td><td><strong>Rp {{ number_format((float) $sppd->grand_total, 0, ',', '.') }}</strong></td></tr>
</table>

<div class="section-heading">Persetujuan</div>
<table class="info-table">
    <tr><td class="label">Status</td><td>Disetujui Manager</td></tr>
    <tr><td class="label">Tanggal Persetujuan</td><td>{{ $sppd->approved_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') ?? '-' }}</td></tr>
    <tr><td class="label">Disetujui Oleh</td><td>{{ $sppd->approver?->name ?? '-' }}</td></tr>
</table>

@php $sig = $pdfImg($sppd->signature_path); @endphp
@if($sig)
<div class="sig-wrap">
    <div class="sig-label">Tanda Tangan Driver</div>
    <img src="{{ $sig }}" alt="TTD">
</div>
@endif

</body>
</html>
