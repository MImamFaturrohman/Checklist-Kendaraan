<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    @include('partials.favicon')
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
        .pdf-body { padding-left: 10mm; padding-right: 10mm; margin-top: -3mm; }
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

        /* Kolom label + titik dua dijajarkan: table-layout + colgroup (DomPDF/Chrome PDF) */
        .pdf-kv-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            table-layout: auto;
        }
        .pdf-kv-table td { font-size: 10pt; padding: 2px 0; vertical-align: top; }
        .pdf-kv-label {
            font-weight: normal;
        }
        .pdf-kv-label span { display: block; padding-left: 12mm; }

        .pdf-kv-colon {
            padding: 2px 0;
            text-align: left;
            white-space: nowrap;
            vertical-align: top;
        }
        /* Nilai langsung rapat setelah kolom ":" (tanpa padding kiri berlebihan) */
        .pdf-kv-value {
            padding: 2px 0 2px 2mm;
            word-wrap: break-word;
        }
        
        .declaration { font-size: 10pt; color: #1a1a2e; margin: 12px 0 8px; }
        .declaration-list { font-size: 10pt; padding-left: 10mm; margin: 6px 0; margin-left: 5mm; }
        .declaration-list li { margin-bottom: 4px; }
        
        .closing { font-size: 10pt; margin-top: 14px; color: #1a1a2e; }

        .sig-table { width: 100%; border-collapse: collapse; margin-top: 28px; }
        .sig-table td { width: 50%; text-align: center; vertical-align: top; padding: 0 10px; font-size: 10pt; }
        .sig-label    { font-weight: bold; margin-bottom: 2px; }
        .sig-position { font-size: 9.5pt; color: #374151; margin-bottom: 0px; }
        .sig-img-box  { height: 75px; margin: 6px auto; margin-bottom: 1px; display: flex; align-items: center; justify-content: center; }
        .sig-img-box img { max-height: 70px; max-width: 180px; object-fit: contain; }
        .sig-name     { font-weight: bold; font-size: 10pt; display: inline-block; min-width: 160px; }

        /* Hanya disclaimer di bagian bawah halaman */
        .pdf-footer-note {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            width: 100%;
            padding: 6px 10px 10px;
            page-break-inside: avoid;
        }

        .note {
            text-align: center;
            font-size: 8pt;
            color: #6b7280;
            margin: 0;
            font-style: italic;
            padding-top: 8px;
        }
        </style>
</head>
<body>
@php
    use Carbon\Carbon;

    $tgl       = Carbon::parse($peminjaman->approved_at);
    $tglPinjam = Carbon::parse($peminjaman->tanggal_peminjaman);

    $bulanId = [
        1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
        7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'
    ];
    $bulanRomawi = [
        1=>'I',2=>'II',3=>'III',4=>'IV',5=>'V',6=>'VI',
        7=>'VII',8=>'VIII',9=>'IX',10=>'X',11=>'XI',12=>'XII'
    ];

    $docId    = str_pad($peminjaman->id, 3, '0', STR_PAD_LEFT);
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

        <div class="pdf-body">
            <p class="body-text"><br>Saya yang bertanda tangan dibawah ini:</p>
            <table class="pdf-kv-table">
                <tr>
                    <td class="pdf-kv-label" style="width: 62mm;"><span>Nama Pegawai</span></td>
                    <td class="pdf-kv-colon" style="width: 2.6mm;">:</td>
                    <td class="pdf-kv-value">{{ $peminjaman->nama_lengkap }}</td>
                </tr>
                <tr>
                    <td class="pdf-kv-label" style="width: 62mm;"><span>NIP</span></td>
                    <td class="pdf-kv-colon" style="width: 2.6mm;">:</td>
                    <td class="pdf-kv-value">{{ $peminjaman->nip }}</td>
                </tr>
                <tr>
                    <td class="pdf-kv-label" style="width: 62mm;"><span>Posisi</span></td>
                    <td class="pdf-kv-colon" style="width: 2.6mm;">:</td>
                    <td class="pdf-kv-value">{{ $peminjaman->jabatan }}</td>
                </tr>
                <tr>
                    <td class="pdf-kv-label" style="width: 62mm;"><span>Bidang / Bagian</span></td>
                    <td class="pdf-kv-colon" style="width: 2.6mm;">:</td>
                    <td class="pdf-kv-value">{{ $bidangTeks }}</td>
                </tr>
            </table>

            <p class="body-text"><br>Mohon untuk dapat dipinjamkan kendaraan dinas <em>Port Management</em>, sebagai berikut:</p>

            <table class="pdf-kv-table">
                <colgroup>
                    <col style="width: 72mm;">
                    <col style="width: 2.6mm;">
                    <col>
                </colgroup>
                <tr>
                    <td class="pdf-kv-label" style="width: 62mm;"><span>Jenis Kendaraan</span></td>
                    <td class="pdf-kv-colon" style="width: 2.6mm;">:</td>
                    <td class="pdf-kv-value">{{ $peminjaman->jenis_kendaraan }}</td>
                </tr>
                <tr>
                    <td class="pdf-kv-label" style="width: 62mm;"><span>Nomor Kendaraan</span></td>
                    <td class="pdf-kv-colon" style="width: 2.6mm;">:</td>
                    <td class="pdf-kv-value">{{ $peminjaman->nomor_kendaraan }}</td>
                </tr>
                <tr>
                    <td class="pdf-kv-label" style="width: 62mm;"><span>Hari / Tanggal Peminjaman</span></td>
                    <td class="pdf-kv-colon" style="width: 2.6mm;">:</td>
                    <td class="pdf-kv-value">{{ $hariPinjam }}, {{ $tglPinjamStr }}</td>
                </tr>
                <tr>
                    <td class="pdf-kv-label" style="width: 62mm;"><span>Untuk Keperluan</span></td>
                    <td class="pdf-kv-colon" style="width: 2.6mm;">:</td>
                    <td class="pdf-kv-value">{{ $peminjaman->alasan }}</td>
                </tr>
            </table>

            <p class="declaration"><br>{{ $pernyataanPengantar }}</p>
            @if($pernyataans->isNotEmpty())
                <ol class="declaration-list">
                    @foreach($pernyataans as $p)
                        <li>{{ $p->isi_pernyataan }}</li>
                    @endforeach
                </ol>
            @endif

            <p class="closing"><br>Demikian disampaikan. Atas perhatian dan kerjasamanya kami ucapkan terima kasih.</p>

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
    </div>

</body>
</html>
