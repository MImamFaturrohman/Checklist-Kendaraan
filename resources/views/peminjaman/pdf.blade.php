<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>PERNYATAAN PEMINJAMAN KENDARAAN DINAS</title>
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

        @page { margin: 30px 36px 38px 36px; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Arial', sans-serif;
            font-size: 10pt;
            margin: 15px;
            padding: 15px;
            color: #1a1a2e;
            line-height: 1.5;
            position: relative;
        }
        
        .pdf-main { padding-bottom: 22mm; }

        .header { width: 100%; margin-bottom: 10px; padding-bottom: 10px; border-bottom: 3px solid #002a7a; }
        .header-table { width: 100%; border-collapse: collapse; }
        .header-table td { vertical-align: middle; }
        .header-left  { width: 40%; }
        .header-right { width: 60%; text-align: right; }
        .header-logo  { width: 240px; height: auto; max-height: 80px; object-fit: contain; }
        .header-title { font-size: 13pt; font-weight: bold; color: #002a7a; letter-spacing: 0.4px; }
        .header-pm    { font-size: 12pt; font-weight: bold; color: #3d4654; margin-top: 1px; }
        .header-no    { font-size: 11pt; font-weight: bold; color: #002a7a; margin-top: 1px; }

        .body-text { font-size: 10pt; color: #1a1a2e; margin: 14px 0 10px; }

        /* Kolom label diseragamkan mengikuti teks terpanjang: "Hari / Tanggal Peminjaman" */
        .pdf-kv-table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        .pdf-kv-table td { font-size: 10pt; padding: 2px 0; vertical-align: top; }
        .pdf-kv-label {
            width: 48mm;
            white-space: nowrap;
            font-weight: normal;
        }

        .pdf-kv-colon { width: 3mm; text-align: left; }
        .pdf-kv-value { padding-bottom: 2px; word-wrap: break-word; }
        
        .declaration { font-size: 10pt; color: #1a1a2e; margin: 12px 0 8px; }
        .declaration-list { font-size: 10pt; padding-left: 22px; margin: 6px 0; }
        .declaration-list li { margin-bottom: 4px; }
        
        .closing { font-size: 10pt; margin-top: 14px; color: #1a1a2e; }

        .sig-table { width: 100%; border-collapse: collapse; margin-top: 28px; }
        .sig-table td { width: 50%; text-align: center; vertical-align: top; padding: 0 10px; font-size: 10pt; }
        .sig-label    { font-weight: bold; margin-bottom: 2px; }
        .sig-position { font-size: 9.5pt; color: #374151; margin-bottom: 0px; }
        .sig-img-box  { height: 75px; margin: 6px auto; display: flex; align-items: center; justify-content: center; }
        .sig-img-box img { max-height: 70px; max-width: 180px; object-fit: contain; }
        .sig-name     { font-weight: bold; font-size: 10pt; margin-top: 4px; padding-top: 4px; display: inline-block; min-width: 160px; }

        /* Hanya disclaimer di bagian bawah halaman */
        .pdf-footer-note {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 5mm;
            width: 100%;
            page-break-inside: avoid;
        }
        .note {
            text-align: center;
            font-size: 8pt;
            color: #6b7280;
            margin: 0;
            font-style: italic;
            border-top: 1px solid #d1d5db;
            padding-top: 8px;
        }
        </style>
</head>
<body>
@php
    use Carbon\Carbon;

    $tgl       = Carbon::parse($peminjaman->approved_at ?? $peminjaman->created_at);
    $tglPinjam = Carbon::parse($peminjaman->tanggal_peminjaman);

    $bulanId = [
        1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
        7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'
    ];
    $bulanRomawi = [
        1=>'I',2=>'II',3=>'III',4=>'IV',5=>'V',6=>'VI',
        7=>'VII',8=>'VIII',9=>'IX',10=>'X',11=>'XI',12=>'XII'
    ];

    $docId    = str_pad($peminjaman->id, 4, '0', STR_PAD_LEFT);
    $docBulan = $bulanRomawi[$tgl->month];
    $docTahun = $tgl->format('y');
    $docNo    = "No. ADC-{$docTahun}{$docBulan}KND{$docId}";

    $tglApproved = $tgl->day . ' ' . ($bulanId[$tgl->month]) . ' ' . $tgl->year;
    $tglPinjamStr = $tglPinjam->day . ' ' . ($bulanId[$tglPinjam->month]) . ' ' . $tglPinjam->year;
    $hariPinjam   = $tglPinjam->locale('id')->isoFormat('dddd');

    $bidangTeks = $peminjaman->bidang
        ? $peminjaman->bidang->labelLengkap()
        : '–';
@endphp

    <div class="pdf-main">
    <div class="header">
        <table class="header-table">
            <tr>
                <td class="header-left">
                    <img class="header-logo" src="{{ public_path('images/ADCPM Landscape NEW.png') }}" alt="Logo ADC PM">
                </td>
                <td class="header-right">
                    <div class="header-title">PERNYATAAN PEMINJAMAN KENDARAAN DINAS</div>
                    <div class="header-pm">PM UNIT SURALAYA</div>
                    <div class="header-no">{{ $docNo }} &nbsp;|&nbsp; {{ $tglApproved }}</div>
                </td>
            </tr>
        </table>
    </div>

    <p class="body-text">Saya yang bertanda tangan dibawah ini :</p>

    <table class="pdf-kv-table">
        <tr>
            <td class="pdf-kv-label">Nama Pegawai</td>
            <td class="pdf-kv-colon">:</td>
            <td class="pdf-kv-value">{{ $peminjaman->nama_lengkap }}</td>
        </tr>
        <tr>
            <td class="pdf-kv-label">NIP</td>
            <td class="pdf-kv-colon">:</td>
            <td class="pdf-kv-value">{{ $peminjaman->nip }}</td>
        </tr>
        <tr>
            <td class="pdf-kv-label">Posisi</td>
            <td class="pdf-kv-colon">:</td>
            <td class="pdf-kv-value">{{ $peminjaman->jabatan }}</td>
        </tr>
        <tr>
            <td class="pdf-kv-label">Bidang / Bagian</td>
            <td class="pdf-kv-colon">:</td>
            <td class="pdf-kv-value">{{ $bidangTeks }}</td>
        </tr>
    </table>

    <p class="body-text">Mohon untuk dapat dipinjamkan kendaraan dinas <em>Port Management</em>, sebagai berikut :</p>

    <table class="pdf-kv-table">
        <tr>
            <td class="pdf-kv-label">Jenis Kendaraan</td>
            <td class="pdf-kv-colon">:</td>
            <td class="pdf-kv-value">{{ $peminjaman->jenis_kendaraan }}</td>
        </tr>
        <tr>
            <td class="pdf-kv-label">Nomor Kendaraan</td>
            <td class="pdf-kv-colon">:</td>
            <td class="pdf-kv-value">{{ $peminjaman->nomor_kendaraan }}</td>
        </tr>
        <tr>
            <td class="pdf-kv-label">Hari / Tanggal Peminjaman</td>
            <td class="pdf-kv-colon">:</td>
            <td class="pdf-kv-value">{{ $hariPinjam }}, {{ $tglPinjamStr }}</td>
        </tr>
        <tr>
            <td class="pdf-kv-label">Untuk Keperluan</td>
            <td class="pdf-kv-colon">:</td>
            <td class="pdf-kv-value">{{ $peminjaman->alasan }}</td>
        </tr>
    </table>

    <p class="declaration">{{ $pernyataanPengantar }}</p>
    @if($pernyataans->isNotEmpty())
        <ol class="declaration-list">
            @foreach($pernyataans as $p)
                <li>{{ $p->isi_pernyataan }}</li>
            @endforeach
        </ol>
    @endif

    <p class="closing">Demikian disampaikan. Atas perhatian dan kerjasamanya kami ucapkan terima kasih.</p>

    <table class="sig-table">
        <tr>
            <td>
                <div class="sig-label">Mengetahui,</div>
                <div class="sig-position" style="font-weight: bold;">Manager Bidang</div>
                <div class="sig-img-box">
                    <img src="{{ public_path('images/TTD Manager.png') }}" alt="TTD Manager">
                </div>
                <div>
                    <span class="sig-name">{{ $peminjaman->approver?->name ?? 'Manager' }}</span>
                </div>
            </td>
            <td>
                <div class="sig-label">Suralaya, {{ $tglApproved }}</div>
                <div class="sig-position" style="font-weight: bold;">{{ $peminjaman->jabatan }}</div>
                <div class="sig-img-box">
                    @if($signatureDataUrl)
                        <img src="{{ $signatureDataUrl }}" alt="TTD Pemohon">
                    @endif
                </div>
                <div>
                    <span class="sig-name">{{ $peminjaman->nama_lengkap }}</span>
                </div>
            </td>
        </tr>
    </table>
    </div>

    <div class="pdf-footer-note">
        <p class="note">Dokumen ini dihasilkan secara otomatis oleh Vehicle Management System ADC Port Management.</p>
    </div>

</body>
</html>
