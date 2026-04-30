<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    @include('partials.favicon')
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
        body { font-family: 'Arial', sans-serif; font-size: 9.5pt; color: #1a1a2e; line-height: 1.4; margin: 10px; padding: 10px; position: relative; }

        .page { padding: 10px; padding-bottom: 52px; }

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
        .info-table .label { font-weight: 700; background: #f3f4f6; color: #111827; width: 20%; }
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 8.5pt; page-break-inside: auto; table-layout: fixed; word-wrap: break-word; }
        .data-table th, .data-table td { border: 1px solidhsl(216, 12.20%, 83.90%); padding: 5px 8px; text-align: left; vertical-align: middle; }
        .data-table th { background: #f1f5f9; font-weight: 700; color: #374151; font-size: 8pt; }
        .data-table tr { page-break-inside: avoid; }

        .merge-total-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 8.5pt;
            table-layout: fixed;
            page-break-inside: avoid;
        }
        .merge-total-table th, .merge-total-table td {
            border: 1px solid #d1d5db;
            padding: 5px 8px;
            text-align: center;
            vertical-align: middle;
        }
        .merge-total-table th { background: #f1f5f9; font-weight: 700; color: #374151; font-size: 8pt; }
        /* Nilai total di kolom kanan: sel data putih, vertikal tengah */
        .merge-total-table td.merge-total-col {
            width: 30%;
            vertical-align: middle;
            color: #111827;
            font-size: 8.5pt;
            padding: 8px;
            background: #fff;
        }
        .merge-total-table .merge-rute-col, .merge-total-table .merge-liter-col { width: 35%; }
        .merge-total-table .merge-biaya-col { width: 35%; }
        .merge-total-table td.merge-rute-col, .merge-total-table td.merge-liter-col {
            text-align: center;
        }
        .merge-total-table .merge-subhead-row th {
            border-top-width: 1px;
        }
        .merge-total-table .merge-grand-row td {
            font-weight: 700;
            color: #111827;
            font-size: 9pt;
        }
        .merge-total-table .merge-grand-row td:first-child {
            background: #f1f5f9;
            text-align: center;
        }
        .merge-total-table .merge-grand-row td:last-child {
            background: #fff;
            text-align: center;
        }

        .approval-text {
            font-size: 9.5pt;
            color: #374151;
            line-height: 1.6;
            margin-top: 5px;
            padding: 10px;
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
        }

        /* Validasi: border blok — QR mengikuti skala tipografi sig-meta (em dari font yang sama) */
        .signature-block-table {
            width: 100%;
            margin-top: 8px;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 8.5pt;
            page-break-inside: avoid;
            border: 1px solid #d1d5db;
        }
        .signature-block-table td {
            vertical-align: middle;
            border-bottom: none;
            padding: 8px 10px;
            background: #fff;
            width: 50%;
        }
        .signature-block-table td:first-child {
            border-right: 1px solid #d1d5db;
        }

        /* QR | teks sejajar horizontal — pakai table-cell (DomPDF tidak andal untuk flexbox) */
        .sig-pair-wrap {
            display: table;
            width: 100%;
            table-layout: auto;
            font-size: 8.5pt;
            line-height: 1.28;
        }
        .sig-qr-wrap {
            display: table-cell;
            vertical-align: middle;
            padding-right: 8px;
            box-sizing: border-box;
        }

        .sig-qr-wrap-pm {
            width: 6em;
            height: 6em;
        }

        .sig-qr-wrap-adm {
            width: 6em;
            height: 6em;
        }

        .signature-qr-img {
            max-width: 100%;
            display: block;
            margin: 0;
            border: none;
            padding: 0;
            box-sizing: border-box;
            object-fit: contain;
        }
        .signature-qr-img-pm {
            width: 6em;
            height: 6em;
        }
        .signature-qr-img-adm {
            width: 6em;
            height: 6em;
        }
        .sig-meta {
            display: table-cell;
            vertical-align: middle;
            text-align: left;
            line-height: 1.28;
            color: #111827;
        }
        .sig-line-title {
            font-weight: 700;
            margin-bottom: 1px;
        }
        .sig-line-role {
            font-weight: 700;
            color:#001e56;
            margin-bottom: 2px;
            letter-spacing: 0.02em;
        }
        .sig-line-name {
            font-weight: 700;
            margin-bottom: 2px;
        }
        .sig-line-when {
            font-size: 7.5pt;
            color: #565c68;
            font-weight: 600;
            font-style: italic;
        }

        /* Footer halaman: garis + catatan di dasar lembar PDF */
        .pdf-page-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            width: 100%;
            padding: 6px 10px 10px;
            background: #fff;
            text-align: center;
        }

        .note {
            text-align: center;
            font-size: 8pt;
            color: #6b7280;
            margin-top: 6px;
            margin-bottom: 0;
            font-style: italic;
        }
    </style>
</head>
<body>
@php
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
    $noSurat = 'ADC-'.$tahun.$bulan.'KEU'.$sppdNo;
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
                        REKAPITULASI BIAYA PERJALANAN DINAS
                    </div>
                    <div class="header-pm">
                        PM UNIT SURALAYA
                    </div>
                    <div class="header-number">
                        No. {{ $noSurat }} | {{ $headerDate->translatedFormat('d F Y') }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="section-heading">Data Dinas</div>
    <table class="info-table">
        <tr>
            <td class="label">Nama Driver</td>
            <td>{{ $sppd->nama_driver }}</td>
            <td class="label">Keperluan Dinas</td>
            <td>{{ $sppd->keperluan_dinas }}</td>
        </tr>
        <tr>
            <td class="label">Nomor Kendaraan</td>
            <td>{{ $sppd->no_kendaraan }}</td>
            <td class="label">Tanggal Dinas</td>
            <td>{{ $tgl }}</td>
        </tr>
        <tr>
            <td class="label">Jenis Kendaraan</td>
            <td>{{ $sppd->jenis_kendaraan }}</td>
            <td class="label">Tujuan</td>
            <td>{{ $sppd->tujuan }}</td>
        </tr>
    </table>

    @if($sppd->tolls->isNotEmpty())
    <div class="section-heading">Biaya Tol</div>
    @php
        $tolBerPdf = $sppd->tolls->where('leg', 'berangkat')->values();
        $tolKemPdf = $sppd->tolls->where('leg', 'kembali')->values();
    @endphp
    @if($tolBerPdf->isNotEmpty() || $tolKemPdf->isNotEmpty())
    <table class="merge-total-table">
        @if($tolBerPdf->isNotEmpty())
        @php
            $rowspanBer = $tolBerPdf->count();
            $sumBer = (float) $tolBerPdf->sum(fn ($t) => (float) $t->harga);
        @endphp
        <tr>
            <th class="merge-rute-col">Rute Tol</th>
            <th class="merge-biaya-col">Biaya</th>
            <th class="merge-total-header-col">Total Tol Berangkat</th>
        </tr>
        @foreach($tolBerPdf as $t)
        <tr>
            <td class="merge-rute-col">{{ $t->dari_tol }} – {{ $t->ke_tol }}</td>
            <td class="merge-biaya-col">Rp{{ number_format((float) $t->harga, 2, ',', '.') }}</td>
            @if($loop->first)
            <td class="merge-total-col" rowspan="{{ $rowspanBer }}">Rp{{ number_format($sumBer, 2, ',', '.') }}</td>
            @endif
        </tr>
        @endforeach
        @endif
        @if($tolKemPdf->isNotEmpty())
        @php
            $rowspanKem = $tolKemPdf->count();
            $sumKem = (float) $tolKemPdf->sum(fn ($t) => (float) $t->harga);
        @endphp
        <tr class="merge-subhead-row">
            <th class="merge-rute-col">Rute Tol</th>
            <th class="merge-biaya-col">Biaya</th>
            <th class="merge-total-header-col">Total Tol Kembali</th>
        </tr>
        @foreach($tolKemPdf as $t)
        <tr>
            <td class="merge-rute-col">{{ $t->dari_tol }} – {{ $t->ke_tol }}</td>
            <td class="merge-biaya-col">Rp{{ number_format((float) $t->harga, 2, ',', '.') }}</td>
            @if($loop->first)
            <td class="merge-total-col" rowspan="{{ $rowspanKem }}">Rp{{ number_format($sumKem, 2, ',', '.') }}</td>
            @endif
        </tr>
        @endforeach
        @endif
    </table>
    @endif
    @endif

    <div class="section-heading">BBM</div>
    @php
        $fuelRows = $sppd->fuels;
        $fuelCount = $fuelRows->count();
        $bbmBodyRows = max(1, $fuelCount);
        $rowspanBbm = $bbmBodyRows;
    @endphp
    <table class="merge-total-table">
        <tr>
            <th class="merge-liter-col">Liter</th>
            <th class="merge-biaya-col">Biaya</th>
            <th class="merge-total-header-col">Total BBM</th>
        </tr>
        @forelse($fuelRows as $f)
        <tr>
            <td class="merge-rute-col">{{ number_format((float) $f->liter, 2, ',', '.') }}</td>
            <td class="merge-biaya-col">Rp{{ number_format((float) $f->total, 2, ',', '.') }}</td>
            @if($loop->first)
            <td class="merge-total-col" rowspan="{{ $rowspanBbm }}">Rp{{ number_format((float) $sppd->total_bbm, 2, ',', '.') }}</td>
            @endif
        </tr>
        @empty
        <tr>
            <td class="merge-rute-col">—</td>
            <td class="merge-biaya-col">—</td>
            <td class="merge-total-col">Rp{{ number_format((float) $sppd->total_bbm, 2, ',', '.') }}</td>
        </tr>
        @endforelse

        {{-- Baris Grand Total --}}
        <tr class="merge-grand-row">
            <td class="merge-rute-col" style="border: none; background: transparent;"></td> 
            <td class="merge-biaya-col" style="background: #f3f4f6; font-weight: bold; text-align: center;">Grand Total</td> 
            <td style="background: #f3f4f6; font-weight: bold; text-align: center; border: 1px solid #d1d5db;">
                Rp{{ number_format((float) $sppd->grand_total, 2, ',', '.') }}
            </td>
        </tr>
    </table>

    <div class="section-heading">Validasi</div>
    <div class="approval-text">
        Laporan ini telah <strong>diverifikasi</strong> dan <strong>disetujui</strong> oleh:
    </div>

    @php
        use App\Support\SppdPdfQr;
        $tz = config('app.timezone');
        $pmName = $sppd->approver?->name ?? '—';
        $admName = $sppd->adminVerifier?->name ?? '—';
        $pmWhenCaption = $sppd->approved_at
            ? $sppd->approved_at
                ->timezone($tz)
                ->translatedFormat('d F Y')
                . ', '
                . $sppd->approved_at->timezone($tz)->format('H.i')
                . ' WIB'
            : '—';

        $admWhenCaption = $sppd->admin_verified_at
            ? $sppd->admin_verified_at
                ->timezone($tz)
                ->translatedFormat('d F Y')
                . ', '
                . $sppd->admin_verified_at->timezone($tz)->format('H.i')
                . ' WIB'
            : '—';
        $pmWhen = $sppd->approved_at
            ? $sppd->approved_at
                ->timezone($tz)
                ->translatedFormat('d F Y, H.i'). ' WIB'
            : '—';
        $admWhen = $sppd->admin_verified_at
            ? $sppd->admin_verified_at
                ->timezone($tz)
                ->translatedFormat('d F Y, H.i'). ' WIB'
            : '—';
        $webUrl = config('app.url');
        $qrPmPayload = "MENYETUJUI PORT MANAGER\nNama: {$pmName}\nNo Surat: {$noSurat}\n{$pmWhen}\nDokumen ini diproduksi oleh {$webUrl}";
        $qrAdmPayload = "DIVERIFIKASI KEUANGAN & ADMINISTRASI\nNama: {$admName}\nNo Surat: {$noSurat}\n{$admWhen}\nDokumen ini diproduksi oleh {$webUrl}";
        $qrPm = SppdPdfQr::pngDataUri($qrPmPayload);
        $qrAdm = SppdPdfQr::pngDataUri($qrAdmPayload);
    @endphp

    <table class="signature-block-table">
        <tr>
            <td>
                <div class="sig-pair-wrap">
                    <div class="sig-qr-wrap sig-qr-wrap-pm">
                        <img class="signature-qr-img sig-qr-pm" src="{{ $qrPm }}" alt="QR Port Manager">
                    </div>
                    <div class="sig-meta">
                        <div class="sig-line-title">Menyetujui,</div>
                        <div class="sig-line-role">PORT MANAGER</div>
                        <div class="sig-line-name">{{ mb_strtoupper((string) $pmName, 'UTF-8') }}</div>
                        <div class="sig-line-when">{{ $pmWhenCaption }}</div>
                    </div>
                </div>
            </td>
            <td>
                <div class="sig-pair-wrap">
                    <div class="sig-qr-wrap sig-qr-wrap-adm">
                        <img class="signature-qr-img sig-qr-adm" src="{{ $qrAdm }}" alt="QR Admin">
                    </div>
                    <div class="sig-meta">
                        <div class="sig-line-title">Diverifikasi,</div>
                        <div class="sig-line-role">KEUANGAN &amp; ADMINISTRASI</div>
                        <div class="sig-line-name">{{ mb_strtoupper((string) $admName, 'UTF-8') }}</div>
                        <div class="sig-line-when">{{ $admWhenCaption }}</div>
                    </div>
                </div>
            </td>
        </tr>
    </table>

</div>

<div class="pdf-page-footer">
    <div class="note">
        Dokumen ini dihasilkan secara otomatis oleh Vehicle Management System ADC Port Management.
    </div>
</div>
</body>
</html>
