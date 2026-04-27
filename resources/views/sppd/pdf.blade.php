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
        @page { margin: 30px; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Arial', sans-serif; font-size: 9.5pt; color: #1a1a2e; line-height: 1.4; margin: 10px; padding: 10px; }

        .page { padding: 10px; }

        .header { width: 100%; margin-bottom: 16px; border-bottom: 3px solid #002a7a; padding-bottom: 12px; }
        .header-table { width: 100%; border-collapse: collapse; }
        .header-table td { vertical-align: middle; }
        .header-left { width: 40%; text-align: middle; }
        .header-logo { width: 250px; height: auto; max-height: 250px; object-fit: contain; }
        .header-title-main { font-family: 'Arial', sans-serif; font-size: 14pt; color: #002a7a; font-weight: 700; letter-spacing: 0.5px; }
        .header-pm {
            font-size: 12pt;
            font-weight: 700;
            color: #3d4654;
            margin-top: 1px;
        }
        .header-number {
            width: 100%;
            text-align: right;
            margin-top: 1px;
            font-size: 11px;
            font-weight: 700;
            color: #002a7a;
        }
        .header-right {
            font-family: 'Arial', sans-serif;
            width: 60%;
            text-align: right;
        }

        .section-heading { font-family: 'Arial', sans-serif; font-size: 10.5pt; font-weight: 700; color: #002a7a; padding: 5px 0; border-left: 3px solid #ffd300; padding-left: 8px; margin: 14px 0 8px; }
        .info-table { width: 100%; margin-bottom: 12px; border-collapse: collapse; font-size: 9; }
        .info-table td { border: 1px solid #d1d5db; padding: 6px 8px; vertical-align: middle; font-size: 9pt; }
        .info-table .label { font-weight: 700; background: #f3f4f6; color: #111827; width: 28%; }
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 8.5pt; page-break-inside: auto; table-layout: fixed; word-wrap: break-word; }
        .data-table th, .data-table td { border: 1px solid #d1d5db; padding: 5px 8px; text-align: left; vertical-align: middle; }
        .data-table th { background: #f1f5f9; font-weight: 700; color: #374151; font-size: 8pt; }
        .data-table tr { page-break-inside: avoid; }

        /* BBM foto: ukuran eksplisit agar baris tabel tidak “jebol” di DomPDF */
        .col-bbm-data {
            width: 15%;     
            white-space: nowrap;
        }
        .sppd-foto-cell {
            width: 55%; 
            padding: 5px !important;
            text-align: center;
            vertical-align: middle !important;
            overflow: hidden;
        }
        .sppd-foto-pair {
            display: block;
            width: 100%;
            vertical-align: middle;
        }

        .sppd-foto-pair img {
            display: inline-block;
            width: 100px;   /* Ukuran dikurangi sedikit agar aman dalam cell */
            height: 75px;   
            object-fit: contain; /* Gunakan contain agar foto tidak terpotong */
            border: 1px solid #d1d5db;
            margin: 2px;
        }

        .totals { width: 100%; max-width: 280px; margin-left: auto; margin-top: 8px; border-collapse: collapse; font-size: 9pt; }
        .totals td { padding: 4px 8px; border: 1px solid #d1d5db; }
        .totals .label { background: #f3f4f6; font-weight: 700; }

        .signature-area { width: 100%; margin-top: 20px; page-break-inside: avoid; }
        .signature-area td { width: 50%; text-align: center; padding: 8px 4px; vertical-align: top; }
        .sig-label { font-size: 8.5pt; font-weight: 700; margin-bottom: 4px; }
        .sig-box { height: 70px; margin: 6px auto; width: 160px; position: relative; }
        .sig-box img { max-width: 100%; max-height: 100%; width: auto; height: auto; object-fit: contain; }
        .sig-name { font-weight: 700; font-size: 9.5pt; margin-top: 3px; }

        .signature-footer-line {
            margin-top: 16px;
            border-top: 1px solid #9ca3af;
        }
        .note {
            text-align: center;
            font-size: 8pt;
            color: #6b7280;
            margin-top: 6px;
            font-style: italic;
        }
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
    $tgl = $sppd->tanggal_dinas?->format('d F Y');
    $headerDate = $sppd->tanggal_dinas ?? \Carbon\Carbon::now();
    $tahun = $headerDate->format('y');
    $bulanRomawi = [
        1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV',
        5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII',
        9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
    ];
    $bulan = $bulanRomawi[(int) $headerDate->format('n')];
    $sppdNo = str_pad((string) $sppd->id, 4, '0', STR_PAD_LEFT);
    $managerTtdPath = null;
    foreach ([
        public_path('images/TTD Driver/TTD Manager.png'),
        public_path('images/TTD Driver/manager.png'),
        public_path('images/TTD Manager.png'),
        public_path('images/TTD Driver.png'),
    ] as $candidate) {
        if (is_file($candidate)) {
            $managerTtdPath = $candidate;
            break;
        }
    }
    if (! $managerTtdPath) {
        $ttdDriverDir = public_path('images/TTD Driver');
        if (is_dir($ttdDriverDir)) {
            $first = glob($ttdDriverDir . DIRECTORY_SEPARATOR . '*.{png,jpg,jpeg,webp}', GLOB_BRACE) ?: [];
            $managerTtdPath = $first[0] ?? null;
        }
    }
@endphp

<div class="page">
    {{-- Header selaras checklist PDF --}}
    <div class="header">
        <table class="header-table">
            <tr>
                <td class="header-left">
                    <img class="header-logo" src="{{ public_path('images/ADCPM Landscape NEW.png') }}" alt="Logo ADC PM">
                </td>
                <td class="header-right">
                    <div class="header-title-main">
                        REKAP LAPORAN SPPD
                    </div>
                    <div class="header-pm">
                        PM UNIT SURALAYA
                    </div>
                    <div class="header-number">
                        No. ADC-{{ $tahun }}{{ $bulan }}KEU{{ $sppdNo }} | {{ $headerDate->translatedFormat('d F Y') }}
                    </div>
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
        <thead>
            <tr>
                <th class="col-bbm-data">Liter</th>
                <th class="col-bbm-data">Harga/L</th>
                <th class="col-bbm-data">Total</th>
                <th>Foto Bukti</th> </tr>
        </thead>
        <tbody>
            @foreach($sppd->fuels as $f)
            @php $iu = $pdfImg($f->odometer_path); $su = $pdfImg($f->struk_path); @endphp
            <tr>
                <td class="col-bbm-data">{{ number_format((float) $f->liter, 2, ',', '.') }}</td>
                <td class="col-bbm-data">Rp {{ number_format((float) $f->harga_per_liter, 0, ',', '.') }}</td>
                <td class="col-bbm-data">Rp {{ number_format((float) $f->total, 0, ',', '.') }}</td>
                <td class="sppd-foto-cell">
                    <span class="sppd-foto-pair">
                        @if($iu)<img src="{{ $iu }}" alt="Odometer">@endif
                        @if($su)<img src="{{ $su }}" alt="Struk">@endif
                        @if(! $iu && ! $su)<span style="font-size:9pt;">—</span>@endif
                    </span>
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
    @if($sig || $managerTtdPath)
    <table class="signature-area">
        <tr>
            <td>
                <div class="sig-label">Manager</div>
                <div class="sig-box">
                    @if($managerTtdPath)
                        <img src="{{ $managerTtdPath }}" alt="Tanda tangan Manager">
                    @endif
                </div>
                <div class="sig-name">{{ $sppd->approver?->name ?? 'Manager' }}</div>
            </td>
            <td>
                <div class="sig-label">Driver</div>
                <div class="sig-box">
                    @if($sig)
                        <img src="{{ $sig }}" alt="Tanda tangan Driver">
                    @endif
                </div>
                <div class="sig-name">{{ $sppd->nama_driver }}</div>
            </td>
        </tr>
    </table>
    @endif

    <div class="signature-footer-line"></div>
    <div class="note">
        Dokumen ini dihasilkan secara otomatis oleh Vehicle Management System ADC Port Management.
    </div>
</div>
</body>
</html>
