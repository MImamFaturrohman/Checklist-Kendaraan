<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Berita Acara Ceklist Kendaraan</title>
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
        body { font-family: 'Arial', sans-serif; font-size: 9.5pt; color: #1a1a2e; line-height: 1.4; margin: 10px; padding: 10px; padding-top: 10px; }

        /* .page-border {
            position: fixed;
            top: 30px;
            left: 30px;
            right: 30px;
            bottom: 30px;
            padding: 10px;
            border: 2px solid #002a7a;
            z-index: -1;
        } */

        .page { padding: 10px; padding-top 10px; }

        .header { width: 100%; margin-bottom: 16px; border-bottom: 3px solid #002a7a; padding-bottom: 12px; }
        .header-table { width: 100%; border-collapse: collapse; }
        .header-table td { vertical-align: top; }
        .header-left { width: 40%; text-align: middle; }
        .header-logo { width: 250px; height: auto; max-height: 250px; object-fit: contain; }
        .header-title-main { font-family: 'Arial', sans-serif; font-size: 14pt; color: #002a7a; font-weight: 700; letter-spacing: 0.5px; }
        .header-pm {
            font-size: 12pt;
            font-weight: 700;
            color:#3d4654;
            margin-top: 2px;
        }
        .header-subtitle { font-size: 8.5pt; color: #6b7280; }

        .section-heading { font-family: 'Arial', sans-serif; font-size: 10.5pt; font-weight: 700; color: #002a7a; padding: 5px 0; border-left: 3px solid #ffd300; padding-left: 8px; margin: 14px 0 8px; }

        .info-table { width: 100%; margin-bottom: 12px; border-collapse: collapse; font-size: 9; }
        .info-table td { border: 1px solid #d1d5db; padding: 6px 8px; vertical-align: middle; font-size: 9pt; }
        .info-table .label { font-weight: 700; background: #f3f4f6; color: #111827; width: 22%; }
        .info-table .value { color: #111827; width: 28%; }

        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 8.5pt; }
        .data-table th { background: #f1f5f9; padding: 6px 8px; text-align: left; font-weight: 700; color: #374151; border: 1px solid #d1d5db; font-size: 8pt; }
        .data-table td { padding: 5px 8px; border: 1px solid #d1d5db; }
        .category-header { background: #e0e7ff; font-weight: 700; color: #1e40af; font-size: 8.5pt; }
        .status-ok { color: #16a34a; font-weight: 700; }
        .status-nok { color: #dc2626; font-weight: 700; }

        .photo-section { margin: 14px 0 12px; page-break-inside: avoid; }
        .photo-section p { font-weight: 700; font-size: 8.5pt; margin-bottom: 6px; color: #374151; }
        .photo-inline { margin-top: 4px; }
        .photo-inline img {
                width: auto;
                height: auto;
                min-width: 85px;
                min-height: 63.75px;
                max-width: 150px;
                max-height: 105px;
                object-fit: cover;
                border: 1px solid #d1d5db;
                border-radius: 3px;
                margin-right: 6px;
                margin-bottom: 6px;
                vertical-align: top;
            }

        .perlengkapan-list { font-size: 9pt; line-height: 1.6; margin-bottom: 6px; }
        .notes-box { background: #f8fafc; border: 1px solid #d1d5db; padding: 8px 10px; margin: 6px 0; font-size: 9pt; min-height: 24px; }
        .statement { font-style: italic; color: #374151; font-size: 9pt; margin: 6px 0; }

        .signature-area { width: 100%; margin-top: 20px; page-break-inside: avoid; }
        .signature-area td { width: 50%; text-align: center; padding: 4px; vertical-align: top; }
        .sig-label { font-size: 8.5pt; font-weight: 700; margin-bottom: 4px; }
        .sig-box { height: 70px; margin: 6px auto; width: 160px; position: relative; }
        .sig-box img { width: 100%; height: 100%; object-fit: contain; }
        .sig-name { font-weight: 700; font-size: 9.5pt; margin-top: 3px; }
        .status-text { font-weight: 700; }

        .header-number {
            width: 100%;
            text-align: right;
            margin-top: 2px;
            font-size: 11px;
            color: #002a7a;
        }

        .header-right {
            font-family: 'Arial', sans-serif;
            width: 60%;
            text-align: right;
        }

        .header-table td {
            vertical-align: middle;
        }

        .status-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            margin-bottom: 12px;
        }

        
        .status-table tr:nth-child(odd) {
            background-color: #f3f4f6;
        }
        
        .status-table tr:nth-child(even) {
            background-color: #ffffff;
        }
        
        .status-table th {
            border: 1px solid #d1d5db;
        }

        .status-table td {
            border: 1px solid #d1d5db;
            padding: 6px 8px;
            vertical-align: middle;
        }

        .status-label {
            width: 25%;
            font-weight: 700;
        }

        .status-value {
            width: 35%;
        }

        .status-photo {
            width: 40%;
            text-align: center;
            background-color: #fff;
        }

        .status-photo img {
            width: 100%;
            max-width: 150px;
            max-height: 105px;
            object-fit: cover;
            border: 1px solid #d1d5db;
            border-radius: 4px;
        }

        .data-table {
        page-break-inside: auto;
        }

        .data-table tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }

        .photo-wrapper {
            page-break-inside: avoid;
        }

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
    
    <div class="page">
        {{-- HEADER --}}
        <div class="header">
            <table class="header-table">
                <tr>
                    <td class="header-left">
                        <img class="header-logo" src="{{ public_path('images/ADCPM Landscape NEW.png') }}" alt="Logo ADC PM">
                    </td>
                    <td class="header-right">
                        <div class="header-title-main">
                            BERITA ACARA CEKLIST KENDARAAN
                        </div>
                        <div class="header-pm">
                            PM UNIT SURALAYA
                        </div>
                    </td>
                </tr>
            </table>
            @php
                $tahun = \Carbon\Carbon::parse($checklist->tanggal)->format('y');
    
                $bulanRomawi = [
                    1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV',
                    5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII',
                    9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'
                ];
    
                $bulan = $bulanRomawi[\Carbon\Carbon::parse($checklist->tanggal)->month];
    
                $id = str_pad($checklist->id, 4, '0', STR_PAD_LEFT);
            @endphp
            <div class="header-number">
                No. ADC-{{ $tahun }}{{ $bulan }}{{ $id }} | {{ $checklist->tanggal->format('d F Y') }}
            </div>
        </div>

        {{-- INFO --}}
        <table class="info-table">
            <tr>
                <td class="label">No. Kendaraan</td>
                <td class="value">{{ $checklist->nomor_kendaraan }}</td>
                <td class="label">Tanggal / Shift</td>
                <td class="value">{{ $checklist->tanggal->format('d F Y') }} / {{ $checklist->shift }}</td>
            </tr>
            <tr>
                <td class="label">Jenis Kendaraan</td>
                <td class="value">{{ $checklist->jenis_kendaraan }}</td>
                <td class="label">Jam Serah Terima</td>
                <td class="value">{{ $checklist->jam_serah_terima }} WIB</td>
            </tr>
            <tr>
                <td class="label">Driver Yang Menyerahkan</td>
                <td class="value">{{ $checklist->driver_serah }}</td>
                <td class="label">Driver Yang Menerima</td>
                <td class="value">{{ $checklist->driver_terima }}</td>
            </tr>
        </table>

        {{-- 1. STATUS OPERASIONAL --}}
        <div class="section-heading">A. Status Operasional</div>
        <table class="status-table">
            <tr>
                <td class="status-label">KM Awal: {{ number_format($checklist->km_awal) }}</td>

                <td class="status-label">KM Akhir: {{ number_format($checklist->km_akhir ?? 0) }}</td>

                <td class="status-photo" rowspan="3">
                    @if($checklist->foto_bbm_dashboard)
                        <img src="{{ storage_path('app/public/' . $checklist->foto_bbm_dashboard) }}">
                    @endif
                </td>
            </tr>

            <tr>
                <td class="status-label">Level BBM</td>
                <td class="status-value">
                    <strong>{{ $checklist->level_bbm }}%</strong>
                </td>
            </tr>

            <tr>
                <td class="status-label">Pengisian Terakhir</td>
                <td class="status-value">
                    @if($checklist->bbm_terakhir)
                        {{ \Carbon\Carbon::parse($checklist->bbm_terakhir)->format('d F Y | H:i') }} WIB
                    @else
                        -
                    @endif
                </td>
            </tr>
        </table>

        {{-- 2. KONDISI FISIK --}}
        <div class="section-heading">B. Kondisi Fisik</div>
        <table class="data-table">
            <thead><tr><th style="width:38%; text-align: center;">Bagian Kendaraan</th><th style="width:14%; text-align: center;">Status</th><th style="width:48%; text-align: center;">Keterangan</th></tr></thead>
            <tbody>
                {{-- EXTERIOR --}}
                <tr><td colspan="3" class="category-header">EXTERIOR</td></tr>
                @php $extItems = ['body_kendaraan'=>'Body kendaraan','kaca'=>'Kaca','spion'=>'Kaca Spion','lampu_utama'=>'Lampu utama','lampu_sein'=>'Lampu sein','ban'=>'Ban','velg'=>'Velg','wiper'=>'Wiper']; @endphp
                @foreach ($extItems as $key => $label)
                @php $status = $checklist->exterior?->$key; @endphp
                <tr><td>{{ $label }}</td><td style="text-align: center;" class="{{ $status === 'ok' ? 'status-ok' : 'status-nok' }}"><span class="status-text">{{ $status === 'ok' ? 'OK' : ($status ? 'NO' : '-') }}</span></td><td>{{ $checklist->exterior?->{$key.'_keterangan'} ?: '-' }}</td></tr>
                @endforeach

                {{-- INTERIOR --}}
                <tr><td colspan="3" class="category-header">INTERIOR</td></tr>
                @php $intItems = ['jok'=>'Jok/kursi','dashboard'=>'Dashboard','ac'=>'AC','sabuk_pengaman'=>'Sabuk pengaman','audio'=>'Audio/Head Unit','kebersihan'=>'Kebersihan interior']; @endphp
                @foreach ($intItems as $key => $label)
                @php $status = $checklist->interior?->$key; @endphp
                <tr><td>{{ $label }}</td><td style="text-align: center;" class="{{ $status === 'ok' ? 'status-ok' : 'status-nok' }}"><span class="status-text">{{ $status === 'ok' ? 'OK' : ($status ? 'NO' : '-') }}</span></td><td>{{ $checklist->interior?->{$key.'_keterangan'} ?: '-' }}</td></tr>
                @endforeach

                {{-- MESIN --}}
                <tr><td colspan="3" class="category-header">MESIN</td></tr>
                @php $mesinItems = ['mesin'=>'Mesin (suara normal)','oli'=>'Oli mesin','radiator'=>'Air radiator','rem'=>'Rem','kopling'=>'Kopling (manual)','transmisi'=>'Transmisi','indikator'=>'Indikator panel']; @endphp
                @foreach ($mesinItems as $key => $label)
                @php $status = $checklist->mesin?->$key; @endphp
                <tr><td>{{ $label }}</td><td style="text-align: center;" class="{{ $status === 'ok' ? 'status-ok' : 'status-nok' }}"><span class="status-text">{{ $status === 'ok' ? 'OK' : ($status ? 'NO' : '-') }}</span></td><td>{{ $checklist->mesin?->{$key.'_keterangan'} ?: '-' }}</td></tr>
                @endforeach
            </tbody>
        </table>

        <!-- {{-- CATATAN KONDISI --}}
        @php
            $catatanExt = $checklist->exterior?->catatan;
            $catatanInt = $checklist->interior?->catatan;
            $catatanMesin = $checklist->mesin?->catatan;
            $hasCatatan = $catatanExt || $catatanInt || $catatanMesin;
        @endphp
        @if($hasCatatan)
        <div style="margin-bottom: 12px;">
            <div class="section-heading" style="font-size:9.5pt; margin-top:8px;">Catatan Kondisi</div>
            @if($catatanExt)
            <div style="margin-bottom:4px;">
                <strong style="font-size:8.5pt; color:#1e40af;">Exterior:</strong>
                <div class="notes-box">{{ $catatanExt }}</div>
            </div>
            @endif
            @if($catatanInt)
            <div style="margin-bottom:4px;">
                <strong style="font-size:8.5pt; color:#1e40af;">Interior:</strong>
                <div class="notes-box">{{ $catatanInt }}</div>
            </div>
            @endif
            @if($catatanMesin)
            <div style="margin-bottom:4px;">
                <strong style="font-size:8.5pt; color:#1e40af;">Mesin & Operasional:</strong>
                <div class="notes-box">{{ $catatanMesin }}</div>
            </div>
            @endif
        </div>
        @endif -->

        {{-- PHOTOS --}}
        @php
            $extPhotos = collect(['foto_depan','foto_kanan','foto_kiri','foto_belakang'])->filter(fn($f) => $checklist->exterior?->$f)->map(fn($f) => $checklist->exterior->$f);
            $intPhotos = collect(['foto_1','foto_2','foto_3'])->filter(fn($f) => $checklist->interior?->$f)->map(fn($f) => $checklist->interior->$f);
            $mesinPhotos = collect(['foto_1','foto_2','foto_3'])->filter(fn($f) => $checklist->mesin?->$f)->map(fn($f) => $checklist->mesin->$f);
        @endphp
        <div class="photo-section" style="page-break-inside: avoid; margin-top: 16px;">
            <p style="font-weight:700; font-size:8.5pt; margin-bottom:6px; color:#374151;">Foto Exterior:</p>
            <div class="photo-inline">
                @foreach($extPhotos as $p)
                    <img src="{{ storage_path('app/public/'.$p) }}">
                @endforeach
            </div>
        </div>
        @if($intPhotos->isNotEmpty())
        <div class="photo-section"><p>Foto Interior:</p><div class="photo-inline">@foreach($intPhotos as $p)<img src="{{ storage_path('app/public/'.$p) }}">@endforeach</div></div>
        @endif
        @if($mesinPhotos->isNotEmpty())
        <div class="photo-section"><p>Foto Ruang Mesin:</p><div class="photo-inline">@foreach($mesinPhotos as $p)<img src="{{ storage_path('app/public/'.$p) }}">@endforeach</div></div>
        @endif

        {{-- 3. PERLENGKAPAN --}}
        <div class="section-heading">C. Perlengkapan Tersedia</div>
        @php
            $pLabels = ['stnk'=>'STNK','kir'=>'Kartu KIR dan QR Kartu BBM','dongkrak'=>'Dongkrak','toolkit'=>'Toolkit','segitiga'=>'Segitiga Pengaman','apar'=>'APAR','ban_cadangan'=>'Ban cadangan'];
            $available = collect($pLabels)->filter(fn($l,$k) => $checklist->perlengkapan?->$k === 'ada')->values();
        @endphp
        <div class="perlengkapan-list">{{ $available->isNotEmpty() ? $available->implode(', ') : 'Tidak ada data perlengkapan.' }}</div>

        {{-- 4. CATATAN --}}
        <div class="section-heading">D. Catatan Khusus</div>
        <div class="notes-box">{{ $checklist->catatan_khusus ?: '-' }}</div>
        <p class="statement">"Dengan ini saya menyatakan, bahwa saya sudah melakukan pemeriksaan secara menyeluruh (eksterior, interior, mesin, dan kelengkapan) kendaraan operasional dan kendaraan berada dalam kondisi baik dan siap untuk digunakan"</p>

        {{-- TANDA TANGAN --}}
        <table class="signature-area">
            <tr>
                <td>
                    <div class="sig-label">Yang Menyerahkan,</div>
                    <div class="sig-box">@if($checklist->tanda_tangan_serah)<img src="{{ storage_path('app/public/'.$checklist->tanda_tangan_serah) }}">@endif</div>
                    <div class="sig-name">{{ $checklist->driver_serah }}</div>
                </td>
                <td>
                    <div class="sig-label">Yang Menerima,</div>
                    <div class="sig-box">@if($checklist->tanda_tangan_terima)<img src="{{ storage_path('app/public/'.$checklist->tanda_tangan_terima) }}">@endif</div>
                    <div class="sig-name">{{ $checklist->driver_terima }}</div>
                </td>
            </tr>
        </table>
        <div class="signature-footer-line"></div>
        <div class="note">
            Dokumen ini dihasilkan secara otomatis oleh Sistem Ceklist ADC Port Management.
        </div>
    </div>
</body>
</html>
