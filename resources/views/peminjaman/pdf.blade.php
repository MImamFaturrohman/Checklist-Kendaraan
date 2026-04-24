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

        @page { margin: 30px 36px; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Arial', sans-serif; font-size: 10pt; padding: 15px; margin: 15px; color: #1a1a2e; line-height: 1.5; }

        /* ── HEADER ── */
        .header { width: 100%; margin-bottom: 10px; padding-bottom: 10px; border-bottom: 3px solid #002a7a; }
        .header-table { width: 100%; border-collapse: collapse; }
        .header-table td { vertical-align: middle; }
        .header-left  { width: 40%; }
        .header-right { width: 60%; text-align: right; }
        .header-logo  { width: 240px; height: auto; max-height: 80px; object-fit: contain; }
        .header-title { font-size: 13pt; font-weight: bold; color: #002a7a; letter-spacing: 0.4px; }
        .header-pm    { font-size: 12pt; font-weight: bold; color: #3d4654; margin-top: 1px; }
        .header-no    { font-size: 11pt; font-weight: bold; color: #002a7a; margin-top: 1px; }

        /* ── BODY ── */
        .body-text { font-size: 10pt; color: #1a1a2e; margin: 14px 0 10px; }

        /* ── IDENTITY TABLE ── */
        .identity-table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        .identity-table td { font-size: 10pt; padding: 3px 4px; vertical-align: bottom; }
        .id-label { width: 34%; font-weight: normal; }
        .id-colon { width: 2%; text-align: center; }
        .id-value { width: 62%; padding-bottom: 1px; }
        .id-slash { width: 2%; text-align: center; }

        /* ── VEHICLE TABLE ── */
        .vehicle-table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        .vehicle-table td { font-size: 10pt; padding: 3px 4px; vertical-align: bottom; }
        .v-label { width: 34%; }
        .v-colon { width: 2%; text-align: center; }
        .v-value { width: 64%; border-bottom: padding-bottom: 1px; }

        /* ── DECLARATION ── */
        .declaration { font-size: 10pt; color: #1a1a2e; margin: 12px 0 8px; }
        .declaration-list { font-size: 10pt; padding-left: 22px; margin: 6px 0; }
        .declaration-list li { margin-bottom: 4px; }

        /* ── CLOSING ── */
        .closing { font-size: 10pt; margin-top: 14px; color: #1a1a2e; }

        /* ── SIGNATURE ── */
        .sig-table { width: 100%; border-collapse: collapse; margin-top: 28px; }
        .sig-table td { width: 50%; text-align: center; vertical-align: top; padding: 0 10px; font-size: 10pt; }
        .sig-label    { font-weight: bold; margin-bottom: 4px; }
        .sig-position { font-size: 9.5pt; color: #374151; margin-bottom: 2px; }
        .sig-date     { font-size: 10pt; margin-bottom: 6px; }
        .sig-img-box  { height: 75px; margin: 6px auto; display: flex; align-items: center; justify-content: center; }
        .sig-img-box img { max-height: 70px; max-width: 180px; object-fit: contain; }
        .sig-name     { font-weight: bold; font-size: 10pt; margin-top: 4px; border-top: 1px solid #374151; padding-top: 4px; display: inline-block; min-width: 160px; }

        .note { text-align: center; font-size: 8pt; color: #6b7280; margin-top: 18px; font-style: italic; border-top: 1px solid #d1d5db; padding-top: 6px; }
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
    $docNo    = "No. ADC-{$docTahun}{$docBulan}{$docId}";

    $tglApproved = $tgl->day . ' ' . ($bulanId[$tgl->month]) . ' ' . $tgl->year;
    $tglPinjamStr = $tglPinjam->day . ' ' . ($bulanId[$tglPinjam->month]) . ' ' . $tglPinjam->year;
    $hariPinjam   = $tglPinjam->locale('id')->isoFormat('dddd');
@endphp

    {{-- ── HEADER ── --}}
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

    {{-- ── IDENTITY ── --}}
    <p class="body-text">Saya yang bertanda tangan dibawah ini :</p>

    <table class="identity-table">
        <tr>
            <td class="id-label">Nama Pegawai</td>
            <td class="id-colon">:</td>
            <td class="id-value">{{ $peminjaman->nama_lengkap }}</td>
        </tr>
        <tr>
            <td class="id-label">NIP</td>
            <td class="id-colon">:</td>
            <td class="id-value">{{ $peminjaman->nip }}</td>
        </tr>
        <tr>
            <td class="id-label">Posisi</td>
            <td class="id-colon">:</td>
            <td colspan="3" class="id-value">{{ $peminjaman->jabatan }}</td>
        </tr>
    </table>

    {{-- ── VEHICLE ── --}}
    <p class="body-text">Mohon untuk dapat dipinjamkan kendaraan dinas <em>Port Management</em>, sebagai berikut :</p>

    <table class="vehicle-table">
        <tr>
            <td class="v-label">Jenis Kendaraan</td>
            <td class="v-colon">:</td>
            <td class="v-value">{{ $peminjaman->jenis_kendaraan }}</td>
        </tr>
        <tr>
            <td class="v-label">Nomor Kendaraan</td>
            <td class="v-colon">:</td>
            <td class="v-value">{{ $peminjaman->nomor_kendaraan }}</td>
        </tr>
        <tr>
            <td class="v-label">Hari / Tanggal Peminjaman</td>
            <td class="v-colon">:</td>
            <td class="v-value">{{ $hariPinjam }}, {{ $tglPinjamStr }}</td>
        </tr>
        <tr>
            <td class="v-label">Untuk Keperluan</td>
            <td class="v-colon">:</td>
            <td class="v-value">{{ $peminjaman->alasan }}</td>
        </tr>
    </table>

    {{-- ── DECLARATION ── --}}
    <p class="declaration">Saya yang meminjam kendaraan dinas PT ADC PM SLA, dengan ini menyatakan <strong>bersedia</strong> untuk:</p>
    <ol class="declaration-list">
        <li>Memperbaiki dan menanggung biaya perbaikan bila terjadi kerusakan pada kendaraan</li>
        <li>Memberikan penggantian kendaraan jika kendaraan hilang (dengan spesifikasi kendaraan yang sama)</li>
        <li>Menyediakan kendaraan pengganti untuk operasional kantor ADC PM SLA selama kendaraan sedang dalam perbaikan, jika kendaraan mengalami kerusakan</li>
        <li>Mengisi ulang bahan bakar yang terpakai</li>
    </ol>

    <p class="closing">Demikian disampaikan. Atas perhatian dan kerjasamanya kami ucapkan terima kasih.</p>

    {{-- ── SIGNATURE ── --}}
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

    <p class="note">Dokumen ini dihasilkan secara otomatis oleh Vehicle Management System ADC Port Management.</p>

</body>
</html>
