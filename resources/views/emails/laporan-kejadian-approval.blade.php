<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Persetujuan Laporan Kejadian</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f1f5f9; margin: 0; padding: 0; color: #1e293b; }
        .wrap { max-width: 600px; margin: 32px auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,.08); }
        .header { background: #002a7a; padding: 28px 32px; text-align: center; }
        .header img { height: 48px; margin-bottom: 10px; display: block; margin-left: auto; margin-right: auto; }
        .header-title { color: #fff; font-size: 18px; font-weight: 700; letter-spacing: .3px; }
        .header-sub { color: rgba(255,255,255,.75); font-size: 13px; margin-top: 4px; }
        .body { padding: 28px 32px; }
        .greeting { font-size: 15px; margin-bottom: 14px; }
        .table { width: 100%; border-collapse: collapse; margin: 16px 0; font-size: 13.5px; }
        .table td { padding: 7px 10px; border: 1px solid #e2e8f0; vertical-align: top; }
        .table td:first-child { font-weight: 600; color: #475569; width: 38%; background: #f8fafc; }
        .kat-inc { display: inline-block; background: #fee2e2; color: #b91c1c; font-weight: 700; font-size: 11.5px; padding: 2px 10px; border-radius: 99px; }
        .kat-nm  { display: inline-block; background: #fef3c7; color: #b45309; font-weight: 700; font-size: 11.5px; padding: 2px 10px; border-radius: 99px; }
        .btn-wrap { text-align: center; margin: 24px 0 16px; }
        .btn { display: inline-block; background: #002a7a; color: #fff !important; text-decoration: none; padding: 13px 32px; border-radius: 8px; font-size: 15px; font-weight: 700; letter-spacing: .2px; }
        .btn:hover { background: #0038a8; }
        .note { font-size: 12px; color: #64748b; background: #f8fafc; border-left: 3px solid #cbd5e1; padding: 10px 14px; border-radius: 4px; margin-top: 20px; }
        .footer { background: #f1f5f9; padding: 14px 32px; font-size: 11.5px; color: #94a3b8; text-align: center; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="header">
        <div class="header-title">Port Management Unit Suralaya</div>
        <div class="header-sub">Permintaan Persetujuan Laporan Kejadian</div>
    </div>
    <div class="body">
        <p class="greeting">Yth. <strong>{{ $managerNama }}</strong>,</p>
        <p style="font-size:14px;line-height:1.6;margin-bottom:14px;">
            Terdapat laporan kejadian baru dari sub-bidang yang Anda pimpin yang memerlukan tanda tangan persetujuan Anda.
            Berikut ringkasan laporan:
        </p>

        <table class="table">
            <tr>
                <td>Pelapor</td>
                <td>{{ $laporan->nama }} | {{ $laporan->nip }}</td>
            </tr>
            <tr>
                <td>Jabatan</td>
                <td>{{ $laporan->jabatan }}</td>
            </tr>
            <tr>
                <td>Bidang / Bagian</td>
                <td>{{ $laporan->bidang?->labelLengkap() ?? '–' }}</td>
            </tr>
            <tr>
                <td>Kategori</td>
                <td>
                    @if($laporan->kategori === 'Incident')
                        <span class="kat-inc">Incident</span>
                    @else
                        <span class="kat-nm">Nearmiss</span>
                    @endif
                </td>
            </tr>
            <tr>
                <td>Waktu Kejadian</td>
                <td>{{ $laporan->waktu_kejadian?->timezone(config('app.timezone'))->translatedFormat('l, d F Y H:i') }}</td>
            </tr>
            <tr>
                <td>Lokasi</td>
                <td>{{ $laporan->lokasi_kejadian }}</td>
            </tr>
            <tr>
                <td>Kendaraan</td>
                <td>{{ $laporan->nomor_kendaraan }} – {{ $laporan->jenis_kendaraan }}</td>
            </tr>
        </table>

        <div class="btn-wrap">
            <a href="{{ $approvalUrl }}" class="btn">Lihat Detail &amp; Tandatangani</a>
        </div>

        <div class="note">
            Tombol di atas akan membawa Anda ke halaman detail laporan lengkap.
            Link ini hanya dapat digunakan satu kali.
        </div>
    </div>
    <div class="footer">
        &copy; {{ date('Y') }} Port Management Unit Suralaya<br>
        Email ini dikirim otomatis, mohon tidak membalas langsung.
    </div>
</div>
</body>
</html>
