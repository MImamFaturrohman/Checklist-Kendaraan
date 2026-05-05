<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    @include('partials.favicon')
    <title>Laporan Kejadian — {{ $laporan->nama }}</title>
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
        .header-logo  { width: 240px; height: auto; max-height: 78px; object-fit: contain; }
        .header-title { font-size: 13pt; font-weight: bold; color: #002a7a; letter-spacing: 0.4px; }
        .header-pm    { font-size: 11pt; font-weight: bold; color: #3d4654; margin-top: 1px; }
        .header-no    { font-size: 10pt; font-weight: bold; color: #002a7a; margin-top: 1px; }

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

        .block-label {
            font-weight: 700;
            font-size: 9.5pt;
            color: #374151;
            margin-top: 8px;
            margin-bottom: 3px;
        }
        .block-text {
            font-size: 9.5pt;
            color: #1a1a2e;
            white-space: pre-wrap;
            word-wrap: break-word;
            border: 1px solid #e5e7eb;
            padding: 6px 8px;
            border-radius: 3px;
            background: #f9fafb;
        }

        .foto-wrap {
            margin-top: 10px;
            margin-bottom: 6px;
            text-align: center;
            page-break-inside: avoid;
        }
        .foto-wrap img {
            max-width: 92%;
            max-height: 220px;
            object-fit: contain;
            border: 1px solid #d1d5db;
            border-radius: 4px;
        }

        /* Tanda tangan: dua kolom, teks center — seperti contoh dokumen */
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

    $docId    = str_pad($laporan->id, 4, '0', STR_PAD_LEFT);
    $docBulan = $bulanRomawi[$tgl->month];
    $docTahun = $tgl->format('y');
    $docNo    = "No. ADC-{$docTahun}{$docBulan}LK{$docId}";

    $hariStr = $tgl->locale('id')->isoFormat('dddd');
    $tanggalStr = $tgl->day.' '.($bulanId[$tgl->month]).' '.$tgl->year;
    $jamStr = $tgl->format('H:i').' WIB';

    /** Hanya nama sub-bidang/bagian (tanpa induk), untuk tabel & jabatan tanda tangan manajer */
    $bidangNamaSaja = $laporan->bidang?->nama ?? '–';
    $jabatanManajerLine = 'MANAJER '.mb_strtoupper($bidangNamaSaja, 'UTF-8');

    $katLabel = $laporan->kategori === 'Nearmiss' ? 'Near Miss' : $laporan->kategori;
@endphp

<div class="pdf-main">
    <div class="header">
        <table class="header-table">
            <tr>
                <td class="header-left">
                    <img class="header-logo" src="{{ public_path('images/ADCPM Landscape NEW.png') }}" alt="Logo ADC PM">
                </td>
                <td class="header-right">
                    <div class="header-title">LAPORAN KEJADIAN</div>
                    <div class="header-pm">PM UNIT SURALAYA</div>
                    <div class="header-no">{{ $docNo }} &nbsp;|&nbsp; {{ Carbon::now()->translatedFormat('d F Y') }}</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Satu tabel: identitas → konteks kejadian → waktu → lokasi & kendaraan (urutan baca natural) --}}
    <div class="section-heading">Data Pelapor, Waktu, Lokasi &amp; Kendaraan</div>
    <table class="info-table">
        <tr>
            <td class="label">Nama</td>
            <td>{{ $laporan->nama }}</td>
            <td class="label">NIP</td>
            <td>{{ $laporan->nip }}</td>
        </tr>
        <tr>
            <td class="label">Posisi / Jabatan</td>
            <td>{{ $laporan->jabatan }}</td>
            <td class="label">Bagian</td>
            <td>{{ $bidangNamaSaja }}</td>
        </tr>
        <tr>
            <td class="label">Kategori</td>
            <td colspan="3">{{ $katLabel }}</td>
        </tr>
        <tr>
            <td class="label">Hari / Tanggal kejadian</td>
            <td>{{ $hariStr }}, {{ $tanggalStr }}</td>
            <td class="label">Jam</td>
            <td>{{ $jamStr }}</td>
        </tr>
        <tr>
            <td class="label">Lokasi kejadian</td>
            <td colspan="3">{{ $laporan->lokasi_kejadian }}</td>
        </tr>
        <tr>
            <td class="label">No. Kendaraan</td>
            <td>{{ $laporan->nomor_kendaraan }}</td>
            <td class="label">Jenis kendaraan</td>
            <td>{{ $laporan->jenis_kendaraan }}</td>
        </tr>
    </table>

    <div class="section-heading">Uraian</div>
    <div class="block-label">Peristiwa</div>
    <div class="block-text">{{ $laporan->peristiwa }}</div>
    <div class="block-label">Sebelum Kejadian</div>
    <div class="block-text">{{ $laporan->sebelum_kejadian }}</div>
    <div class="block-label">Kejadian</div>
    <div class="block-text">{{ $laporan->uraian_kejadian }}</div>

    @if(!empty($fotoDataUrl))
        <div class="foto-wrap">
            <img src="{{ $fotoDataUrl }}" alt="Foto kejadian">
        </div>
    @endif

    <div class="block-label">Akibat Dari Kejadian</div>
    <div class="block-text">{{ $laporan->akibat }}</div>

    <table class="sig-table">
        <tr>
            <td>
                <div class="sig-line">Mengetahui,</div>
                <div class="sig-role">{{ $jabatanManajerLine }}</div>
                <div class="sig-img-box">
                    @if($laporan->ttd_manager)
                        <img src="{{ $laporan->ttd_manager }}" alt="Tanda tangan manajer">
                    @endif
                </div>
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
