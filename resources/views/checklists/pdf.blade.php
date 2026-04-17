<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Berita Acara Ceklist Kendaraan</title>
    <style>
        @page { margin: 18mm 14mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 9.5pt; color: #1a1a2e; line-height: 1.4; }
        .page { border: 2px solid #002a7a; padding: 24px 28px; }

        .header { width: 100%; margin-bottom: 16px; border-bottom: 3px solid #ffd300; padding-bottom: 12px; }
        .header-table { width: 100%; border-collapse: collapse; }
        .header-table td { vertical-align: top; }
        .header-logo-cell { width: 84px; padding-right: 12px; }
        .header-logo { width: 72px; max-height: 72px; object-fit: contain; }
        .header-title h1 { font-size: 14pt; color: #002a7a; font-weight: 800; letter-spacing: 0.5px; margin-bottom: 2px; }
        .header-subtitle { font-size: 8.5pt; color: #6b7280; }
        .header-number { text-align: right; font-size: 8.5pt; color: #6b7280; white-space: nowrap; width: 200px; }

        .section-heading { font-size: 10.5pt; font-weight: 800; color: #002a7a; padding: 5px 0; border-bottom: 3px solid #ffd300; margin: 14px 0 8px; }

        .info-grid { width: 100%; margin-bottom: 12px; }
        .info-grid td { padding: 2px 0; vertical-align: top; font-size: 9pt; }
        .info-label { font-weight: 700; color: #374151; width: 130px; }
        .info-value { color: #111827; }

        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 8.5pt; }
        .data-table th { background: #f1f5f9; padding: 6px 8px; text-align: left; font-weight: 700; color: #374151; border: 1px solid #d1d5db; font-size: 8pt; }
        .data-table td { padding: 5px 8px; border: 1px solid #d1d5db; }
        .category-header { background: #e0e7ff; font-weight: 800; color: #1e40af; font-size: 8.5pt; }
        .status-ok { color: #16a34a; font-weight: 700; }
        .status-nok { color: #dc2626; font-weight: 700; }

        .photo-section { margin: 10px 0 12px; page-break-before:  auto; margin-top: 12px; }
        .photo-section p { font-weight: 700; font-size: 8.5pt; margin-bottom: 4px; color: #374151; }
        .photo-inline { margin-top: 2px; }
        .photo-inline img {
                width: auto;
                height: auto; 
                max-width: 150px;        
                max-height: 82px;     
                object-fit: cover;     
            }

        .perlengkapan-list { font-size: 9pt; line-height: 1.6; margin-bottom: 6px; }
        .notes-box { background: #f8fafc; border: 1px solid #d1d5db; padding: 8px 10px; margin: 6px 0; font-size: 9pt; min-height: 24px; }
        .statement { font-style: italic; color: #374151; font-size: 9pt; margin: 6px 0; }

        .signature-area { width: 100%; margin-top: 20px; page-break-inside: avoid; }
        .signature-area td { width: 50%; text-align: center; padding: 4px; vertical-align: top; }
        .sig-label { font-size: 8.5pt; font-weight: 700; color: #6b7280; margin-bottom: 4px; }
        .sig-box { border: 1px dashed #cbd5e1; height: 70px; margin: 6px auto; width: 160px; position: relative; }
        .sig-box img { width: 100%; height: 100%; object-fit: contain; }
        .sig-name { font-weight: 700; font-size: 9.5pt; margin-top: 3px; }
        .status-text { font-weight: 700; }

        .header-number-below {
            text-align: left;
            padding-top: 8px;
            font-size: 12px;
            color: #555;
        }

        .header-logo {
            width: 120px; 
            height: auto;
        }

        .header-title {
            padding-left: 20px; 
        }

        .header-table td {
            vertical-align: middle;
        }

        .header-logo-cell {
            width: 160px;
        }

        .status-row {
        width: 100%;
        margin-top: 6px;
        }

        .status-row:after {
            content: "";
            display: block;
            clear: both;
        }

        /* kiri (teks) */
        .status-left {
            float: left;
            width: 55%;
            font-size: 9pt;
        }

        /* kanan (gambar) */
        .status-right {
            float: right;
            width: 40%;
            text-align: right;
        }

        /* gambar diperbesar */
        .status-right img {
            width: 100%;
            max-width: 170px; 
            height: auto;
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
    </style>
</head>
<body>
    <div class="page">
        {{-- HEADER --}}
        <div class="header">
            <table class="header-table">
                <tr>
                    <td class="header-logo-cell">
                        <img class="header-logo" src="{{ public_path('images/ADCPM Landscape NEW.png') }}" alt="Logo ADC PM">
                    </td>
                    <td class="header-title">
                        <h1>BERITA ACARA CEKLIST KENDARAAN</h1>
                    </td>
                </tr>

                <tr>
                    <td colspan="2" class="header-number-below">
                        Nomor Laporan: ADC-{{ str_pad($checklist->id, 10, '0', STR_PAD_LEFT) }}
                        <div class="header-subtitle">{{ $checklist->tanggal->format('d F Y') }}</div>
                    </td>
                </tr>
            </table>
        </div>

        {{-- INFO --}}
        <table class="info-grid">
            <tr>
                <td class="info-label">Kendaraan:</td>
                <td class="info-value"><strong>{{ $checklist->nomor_kendaraan }}</strong> ({{ $checklist->jenis_kendaraan }})</td>
                <td class="info-label" style="width:90px">Waktu:</td>
                <td class="info-value">{{ $checklist->tanggal->format('d/m/Y') }} / {{ $checklist->jam_serah_terima }} ({{ $checklist->shift }})</td>
            </tr>
            <tr>
                <td class="info-label">Driver Yang Menyerahkan:</td>
                <td class="info-value">{{ $checklist->driver_serah }}</td>
                <td class="info-label">Driver Yang Menerima:</td>
                <td class="info-value">{{ $checklist->driver_terima }}</td>
            </tr>
        </table>

        {{-- 1. STATUS OPERASIONAL --}}
        <div class="section-heading">1. Status Operasional</div>
        <div class="status-row">
            <div class="status-left">
                <p>
                    KM Awal: <strong>{{ number_format($checklist->km_awal) }}</strong><br>
                    KM Akhir: <strong>{{ number_format($checklist->km_akhir ?? 0) }}</strong><br>
                    BBM: <strong>{{ $checklist->level_bbm }}%</strong><br>
                    @if($checklist->bbm_terakhir)
                        Isi Terakhir: {{ $checklist->bbm_terakhir }}
                    @endif
                </p>
            </div>

            <div class="status-right">
                @if($checklist->foto_bbm_dashboard)
                    <img src="{{ storage_path('app/public/' . $checklist->foto_bbm_dashboard) }}">
                @endif
            </div>
        </div>

        {{-- 2. KONDISI FISIK --}}
        <div class="section-heading">2. Kondisi Fisik & Keterangan</div>
        <table class="data-table">
            <thead><tr><th style="width:38%">Bagian Kendaraan</th><th style="width:14%">Status</th><th style="width:48%">Keterangan</th></tr></thead>
            <tbody>
                {{-- EXTERIOR --}}
                <tr><td colspan="3" class="category-header">EXTERIOR</td></tr>
                @php $extItems = ['body_kendaraan'=>'Body kendaraan','kaca'=>'Kaca','spion'=>'Kaca Spion','lampu_utama'=>'Lampu utama','lampu_sein'=>'Lampu sein','ban'=>'Ban','velg'=>'Velg','wiper'=>'Wiper']; @endphp
                @foreach ($extItems as $key => $label)
                @php $status = $checklist->exterior?->$key; @endphp
                <tr><td>{{ $label }}</td><td class="{{ $status === 'ok' ? 'status-ok' : 'status-nok' }}"><span class="status-text">{{ $status === 'ok' ? 'OK' : ($status ? 'NO' : '-') }}</span></td><td>{{ $checklist->exterior?->{$key.'_keterangan'} ?: '-' }}</td></tr>
                @endforeach

                {{-- INTERIOR --}}
                <tr><td colspan="3" class="category-header">INTERIOR</td></tr>
                @php $intItems = ['jok'=>'Jok/kursi','dashboard'=>'Dashboard','ac'=>'AC','sabuk_pengaman'=>'Sabuk pengaman','audio'=>'Audio/Head Unit','kebersihan'=>'Kebersihan interior']; @endphp
                @foreach ($intItems as $key => $label)
                @php $status = $checklist->interior?->$key; @endphp
                <tr><td>{{ $label }}</td><td class="{{ $status === 'ok' ? 'status-ok' : 'status-nok' }}"><span class="status-text">{{ $status === 'ok' ? 'OK' : ($status ? 'NO' : '-') }}</span></td><td>{{ $checklist->interior?->{$key.'_keterangan'} ?: '-' }}</td></tr>
                @endforeach

                {{-- MESIN --}}
                <tr><td colspan="3" class="category-header">MESIN & OPERASIONAL</td></tr>
                @php $mesinItems = ['mesin'=>'Mesin (suara normal)','oli'=>'Oli mesin','radiator'=>'Air radiator','rem'=>'Rem','kopling'=>'Kopling (manual)','transmisi'=>'Transmisi','indikator'=>'Indikator panel']; @endphp
                @foreach ($mesinItems as $key => $label)
                @php $status = $checklist->mesin?->$key; @endphp
                <tr><td>{{ $label }}</td><td class="{{ $status === 'ok' ? 'status-ok' : 'status-nok' }}"><span class="status-text">{{ $status === 'ok' ? 'OK' : ($status ? 'NO' : '-') }}</span></td><td>{{ $checklist->mesin?->{$key.'_keterangan'} ?: '-' }}</td></tr>
                @endforeach
            </tbody>
        </table>

        {{-- PHOTOS --}}
        @php
            $extPhotos = collect(['foto_depan','foto_kanan','foto_kiri','foto_belakang'])->filter(fn($f) => $checklist->exterior?->$f)->map(fn($f) => $checklist->exterior->$f);
            $intPhotos = collect(['foto_1','foto_2','foto_3'])->filter(fn($f) => $checklist->interior?->$f)->map(fn($f) => $checklist->interior->$f);
            $mesinPhotos = collect(['foto_1','foto_2','foto_3'])->filter(fn($f) => $checklist->mesin?->$f)->map(fn($f) => $checklist->mesin->$f);
        @endphp
        <table style="width:100%; margin-top:10px;" class="photo-wrapper">
            <tr>
                <td>
                    <p style="font-weight:700; font-size:8.5pt;">Foto Exterior:</p>
                    <div class="photo-inline">
                        @foreach($extPhotos as $p)
                            <img src="{{ storage_path('app/public/'.$p) }}">
                        @endforeach
                    </div>
                </td>
            </tr>
        </table>
        @if($intPhotos->isNotEmpty())
        <div class="photo-section"><p>Foto Interior:</p><div class="photo-inline">@foreach($intPhotos as $p)<img src="{{ storage_path('app/public/'.$p) }}">@endforeach</div></div>
        @endif
        @if($mesinPhotos->isNotEmpty())
        <div class="photo-section"><p>Foto Ruang Mesin:</p><div class="photo-inline">@foreach($mesinPhotos as $p)<img src="{{ storage_path('app/public/'.$p) }}">@endforeach</div></div>
        @endif

        {{-- 3. PERLENGKAPAN --}}
        <div class="section-heading">3. Perlengkapan Tersedia</div>
        @php
            $pLabels = ['stnk'=>'STNK','kir'=>'Kartu KIR','dongkrak'=>'Dongkrak','toolkit'=>'Toolkit','segitiga'=>'Segitiga Pengaman','apar'=>'APAR','ban_cadangan'=>'Ban cadangan'];
            $available = collect($pLabels)->filter(fn($l,$k) => $checklist->perlengkapan?->$k === 'ada')->values();
        @endphp
        <div class="perlengkapan-list">{{ $available->isNotEmpty() ? $available->implode(', ') : 'Tidak ada data perlengkapan.' }}</div>

        {{-- 4. CATATAN --}}
        <div class="section-heading">4. Catatan Khusus & Validasi</div>
        <div class="notes-box">{{ $checklist->catatan_khusus ?: '-' }}</div>
        <p class="statement">"Dengan ini saya menyatakan, bahwa saya sudah melakukan pemeriksaan secara menyeluruh (eksterior, interior, mesin, dan kelengkapan) kendaraan operasional dan kendaraan berada dalam kondisi baik dan siap untuk digunakan"</p>

        {{-- TANDA TANGAN --}}
        <table class="signature-area">
            <tr>
                <td>
                    <div class="sig-label">Tanda Tangan Driver Yang Menyerahkan:</div>
                    <div class="sig-box">@if($checklist->tanda_tangan_serah)<img src="{{ storage_path('app/public/'.$checklist->tanda_tangan_serah) }}">@endif</div>
                    <div class="sig-name">({{ $checklist->driver_serah }})</div>
                </td>
                <td>
                    <div class="sig-label">Tanda Tangan Driver Yang Menerima:</div>
                    <div class="sig-box">@if($checklist->tanda_tangan_terima)<img src="{{ storage_path('app/public/'.$checklist->tanda_tangan_terima) }}">@endif</div>
                    <div class="sig-name">({{ $checklist->driver_terima }})</div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
