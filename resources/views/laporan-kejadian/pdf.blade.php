<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    @include('partials.favicon')
    <title>Laporan Kejadian Kendaraan Operasional — {{ $laporan->nama }}</title>
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
        body {
            font-family: 'Arial', sans-serif;
            font-size: 9.5pt;
            color: #1a1a2e;
            line-height: 1.4;
            margin: 10px;
            padding: 10px;
            position: relative;
        }

        .pdf-main { padding-bottom: 8px; }

        .header { width: 100%; margin-bottom: 12px; border-bottom: 3px solid #002a7a; padding-bottom: 10px; }
        .header-table { width: 100%; border-collapse: collapse; }
        .header-table td { vertical-align: middle; }
        .header-left  { width: 40%; }
        .header-right { width: 60%; text-align: right; }
        .header-logo  { width: 280px; height: auto; max-height: 78px; object-fit: contain; margin-top: -4mm; margin-left: -2mm; }
        .header-title { font-size: 13pt; font-weight: bold; color: #002a7a; letter-spacing: 0.4px; }
        .header-pm    { font-size: 11pt; font-weight: bold; color: #3d4654; margin-top: -2px; }
        .header-no    { font-size: 10px; font-weight: bold; color: #002a7a; margin-top: 2px; }

        /* Styling Kategori */
        .lk-cat-container {
            margin: 10px 0 15px;
            font-size: 10pt;
            text-align: right;
        }

        .lk-cat-badge {
            display: inline-block;
            padding: 3px 9px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 9pt;
            text-align: center;
            vertical-align: middle;
        }

        .lk-cat-badge.active {
            border: 1px solid #002a7a;
            background-color: #002a7a;
            color: #ffffff;
        }

        .lk-cat-badge.inactive {
            border: 1px solid #6b7280;
            color: #6b7280;
        }

        .section-heading {
            font-family: 'Arial', sans-serif;
            font-size: 10.5pt;
            font-weight: 700;
            color: #002a7a;
            padding: 5px 0;
            border-left: 3px solid #ffd300;
            padding-left: 8px;
            margin: 12px 0 6px;
        }
        .info-table {
            width: 100%;
            margin-bottom: 10px;
            border-collapse: collapse;
            font-size: 9pt;
            table-layout: fixed;
        }
        .info-table td {
            border: 1px solid #d1d5db;
            padding: 5px 8px;
            vertical-align: top;
            word-wrap: break-word;
        }
        .info-table .label {
            font-weight: 700;
            background: #f3f4f6;
            color: #111827;
            width: 22%;
        }
        .block-text {
            font-size: 9.5pt;
            color: #1a1a2e;
            white-space: pre-wrap;
            word-wrap: break-word;
            border: 1px solid #e5e7eb;
            padding: 6px 8px;
            background: #f9fafb;
        }

        .dual-peristiwa-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            table-layout: fixed;
            font-size: 9pt;
        }
        .dual-peristiwa-table td {
            border: 1px solid #d1d5db;
            padding: 6px 8px;
            vertical-align: top;
            word-wrap: break-word;
        }
        .dual-peristiwa-table .subhead {
            font-weight: 700;
            background: #f3f4f6;
            color: #111827;
            width: 50%;
            border-bottom: none;
        }
        .dual-peristiwa-table .subbody {
            border-top: none;
            white-space: pre-wrap;
        }

        /* Kotak satu: header + kolom foto + penjelasan di bawah tiap foto */
        .lk-gambar-shell {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            border: 1px solid #e5e7eb;
            font-size: 9pt;
            page-break-inside: avoid;
            table-layout: fixed;
        }
        .lk-gambar-shell td {
            vertical-align: top;
        }
        /* .lk-gambar-head {
            background: #f3f4f6;
            font-weight: 700;
            padding: 6px 10px;
            text-align: center;
            border-bottom: 1px solid #d1d5db;
            color: #111827;
        } */
        .lk-gambar-col {
            border-right: 1px solid #e5e7eb;
            padding: 0;
        }
        .lk-gambar-col:last-child {
            border-right: none;
        }
        .lk-gambar-col-inner {
            padding: 8px;
        }
        .lk-gambar-col-inner img {
            max-width: 100%;
            width: auto;
            object-fit: contain;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            display: block;
            margin: 0 auto 6px;
        }

        .lk-gambar-col-caption {
            text-align: center;
            white-space: pre-wrap;
            word-wrap: break-word;
            font-size: 8.5pt;
            color: #1a1a2e;
            padding-top: 6px;
            margin-top: 2px;
        }
        .lk-gambar-empty {
            padding: 12px;
            text-align: center;
            color: #9ca3af;
            font-size: 8.5pt;
        }

        .sig-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            page-break-inside: avoid;
        }
        .sig-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 8px 12px;
            font-size: 9.5pt;
        }
        .sig-line {
            text-align: center;
            font-size: 9.5pt;
            margin-bottom: 2px;
        }
        .sig-role {
            font-weight: 700;
            font-size: 9pt;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #111827;
            margin-bottom: 10px;
            line-height: 1.35;
        }
        .sig-img-box {
            min-height: 64px;
            margin: 8px auto 4px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .sig-img-box img {
            max-height: 52px;
            max-width: 200px;
            object-fit: contain;
        }
        .sig-print-name {
            font-weight: 700;
            font-size: 9.5pt;
            text-transform: uppercase;
            margin-top: 1px;
        }

        .pdf-doc-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            width: 100%;
            padding: 6px 10px 10px;
            background: #fff;
            text-align: center;
        }
        .pdf-doc-footer .note {
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
    use Carbon\Carbon;

    $tgl = $laporan->waktu_kejadian instanceof \Carbon\Carbon
        ? $laporan->waktu_kejadian
        : Carbon::parse($laporan->waktu_kejadian);

    $bulanId = [
        1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
        7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'
    ];
    $bulanRomawi = [
        1=>'I',2=>'II',3=>'III',4=>'IV',5=>'V',6=>'VI',
        7=>'VII',8=>'VIII',9=>'IX',10=>'X',11=>'XI',12=>'XII'
    ];

    $docId    = str_pad($laporan->id, 3, '0', STR_PAD_LEFT);
    $docBulan = $bulanRomawi[$tgl->month];
    $docTahun = $tgl->format('y');
    $docNo    = "No. ADC-{$docTahun}{$docBulan}LK{$docId}";

    $hariStr = $tgl->locale('id')->isoFormat('dddd');
    $tanggalStr = $tgl->day.' '.($bulanId[$tgl->month]).' '.$tgl->year;
    $jamLabel = $tgl->format('H:i').' WIB';

    $bidangNama = $laporan->bidang?->nama ?? '–';
    $pimpinanNama = $laporan->bidang?->pimpinan_nama;
    $pimpinanJabatan = mb_strtoupper($laporan->bidang?->jabatan ?? 'PIMPINAN', 'UTF-8');

    $hasPimpinanInfo = filled($pimpinanNama);

    $isIncident = $laporan->kategori === 'Incident';
    $isNearmiss = $laporan->kategori === 'Nearmiss';
    $symOn = '&#9745;';
    $symOff = '&#9744;';
@endphp

<div class="pdf-main">
    <div class="header">
        <table class="header-table">
            <tr>
                <td class="header-left">
                    <img class="header-logo" src="{{ public_path('images/ADCPM Landscape NEW.png') }}" alt="Logo ADC PM">
                </td>
                <td class="header-right">
                    <div class="header-title">LAPORAN KEJADIAN KENDARAAN OPERASIONAL</div>
                    <div class="header-pm">PM UNIT SURALAYA</div>
                    <div class="header-no">{{ $docNo }} &nbsp;|&nbsp; {{ Carbon::now()->translatedFormat('d F Y') }}</div>
                </td>
            </tr>
        </table>
    </div>
    
    <table style="width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 5px;">
        <tr>
            <td style="width: 50%; vertical-align: middle; padding: 0;">
                <div class="section-heading" style="margin: 0;">Data Laporan</div>
            </td>
            <td style="width: 50%; text-align: right; vertical-align: middle; padding: 0;">
                <div class="lk-cat-container" style="display: inline-block; margin: 0; padding-top: 5px;">
                    <span class="lk-cat-badge {{ $isIncident ? 'active' : 'inactive' }}">Insiden</span>
                    <span class="lk-cat-badge {{ $isNearmiss ? 'active' : 'inactive' }}" style="margin-left: 4px;">Nearmiss</span>
                </div>
            </td>
        </tr>
    </table>

    <table class="info-table">
        <tr>
            <td class="label">Nama Pelapor</td>
            <td>{{ $laporan->nama }}</td>
            <td class="label">No. Kendaraan</td>
            <td>{{ $laporan->nomor_kendaraan }}</td>
        </tr>
        <tr>
            <td class="label">NIP</td>
            <td>{{ $laporan->nip }}</td>
            <td class="label">Jenis kendaraan</td>
            <td>{{ $laporan->jenis_kendaraan }}</td>
        </tr>
        <tr>
            <td class="label">Posisi / Jabatan</td>
            <td>{{ $laporan->jabatan }}</td>
            <td class="label">Hari dan tanggal kejadian</td>
            <td>{{ $hariStr }}, {{ $tanggalStr }}</td>
        </tr>
        <tr>
            <td class="label">Bidang / Bagian</td>
            <td>{{ $bidangNama }}</td>
            <td class="label">Waktu Kejadian</td>
            <td>{{ $jamLabel }}</td>
        </tr>
        <tr>
            <td class="label">Lokasi kejadian</td>
            <td colspan="3">{{ $laporan->lokasi_kejadian }}</td>
        </tr>
    </table>

    <div class="section-heading">Uraian</div>
    <table class="dual-peristiwa-table" style="margin-bottom: 15px;">
        <tr>
            <td class="subhead">Peristiwa</td>
            <td class="subhead">Sebelum Kejadian</td>
        </tr>
        <tr>
            <td class="subbody">{{ trim($laporan->peristiwa) }}</td>
            <td class="subbody">{{ trim($laporan->sebelum_kejadian) }}</td>
        </tr>
        <tr>
            <td colspan="2" class="block-text" style="border: 1px solid #d1d5db; border-top: none;"><strong style="color: #374151; display: block; margin-bottom: 4px;">Kejadian</strong>{{ trim($laporan->uraian_kejadian) }}</td>
        </tr>
        @php
            $slides = $fotoSlides ?? [];
            $colCount = max(1, count($slides));
            $imgMaxH = $colCount === 1 ? 200 : ($colCount === 2 ? 168 : 128);
        @endphp
        <tr>
            <td colspan="2" style="padding: 0; border: 1px solid #d1d5db; border-top: none;">
                <table style="width: 100%; border-collapse: collapse; margin: 0; table-layout: fixed; border: none;">
                    <tr>
                        @forelse($slides as $index => $s)
                            <td class="lk-gambar-col" style="width: {{ round(100 / $colCount, 2) }}%; text-align: center; vertical-align: middle; padding: 0; border: none; {{$index < $colCount - 1 ? 'border-right: 1px solid #d1d5db;' : ''}}">
                                <div class="lk-gambar-col-inner" style="padding: 8px;">
                                    @if(!empty($s['data_url']))
                                        <img src="{{ $s['data_url'] }}" alt="Foto kejadian" style="max-height: {{ $imgMaxH }}px; display: block; margin: 0 auto 6px; border: 1px solid #e5e7eb; border-radius: 3px; max-width: 100%; width: auto; object-fit: contain;">
                                    @else
                                        <span style="color:#9ca3af;font-size:8.5pt;">(Tidak ada gambar)</span>
                                    @endif
                                    <div class="lk-gambar-col-caption" style="text-align: center; white-space: pre-wrap; word-wrap: break-word; font-size: 8.5pt; color: #1a1a2e; padding-top: 6px; margin-top: 2px;">{{ $s['penjelasan'] ?? '' }}</div>
                                </div>
                            </td>
                        @empty
                            <td class="lk-gambar-empty" colspan="1" style="padding: 12px; text-align: center; color: #9ca3af; font-size: 8.5pt; border: none;">(Tidak ada gambar)</td>
                        @endforelse
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="sig-table">
        <tr>
            <td>
                @if($hasPimpinanInfo)
                    <div class="sig-line">Mengetahui,</div>
                    <div class="sig-role">{{ $pimpinanJabatan }}</div>

                    <div class="sig-img-box">
                        @if($laporan->ttd_manager)
                            <img src="{{ $laporan->ttd_manager }}" alt="Tanda tangan pimpinan">
                        @endif
                    </div>
                    <div class="sig-print-name">
                        {{ mb_strtoupper($pimpinanNama, 'UTF-8') }}
                    </div>
                @endif
            </td>
            <td>
                <div class="sig-line" style="margin-top:4px">Pelapor,</div>
                <div class="sig-role">{{ mb_strtoupper($laporan->jabatan, 'UTF-8') }}</div>
                <div class="sig-img-box">
                    @if($laporan->ttd_pelapor)
                        <img src="{{ $laporan->ttd_pelapor }}" alt="Tanda tangan pelapor">
                    @endif
                </div>
                <div class="sig-print-name">{{ mb_strtoupper($laporan->nama, 'UTF-8') }}</div>
            </td>
        </tr>
    </table>

    <div class="pdf-doc-footer">
        <p class="note">Dokumen ini dihasilkan secara otomatis oleh Vehicle Management System ADC Port Management.</p>
    </div>
</div>

</body>
</html>
