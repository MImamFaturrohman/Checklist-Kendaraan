<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Berita Acara Ceklist Kendaraan</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', Arial, Helvetica, sans-serif;
            font-size: 10pt;
            color: #1a1a2e;
            line-height: 1.4;
        }
        .page {
            border-left: 5px solid #002a7a;
            padding: 24px 28px 24px 24px;
            min-height: 100%;
        }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 {
            font-size: 14pt;
            color: #002a7a;
            font-weight: 800;
            letter-spacing: 1px;
        }
        .header .company {
            font-size: 10pt;
            color: #6b7280;
            margin-top: 2px;
        }
        .header .ref {
            font-size: 8pt;
            color: #9ca3af;
            margin-top: 4px;
        }
        .section-heading {
            font-size: 11pt;
            font-weight: 800;
            color: #002a7a;
            padding: 4px 0;
            border-bottom: 3px solid #ffd300;
            margin: 16px 0 10px;
        }
        .info-grid {
            width: 100%;
            margin-bottom: 14px;
        }
        .info-grid td {
            padding: 3px 0;
            vertical-align: top;
        }
        .info-label {
            font-weight: 700;
            color: #374151;
            width: 140px;
        }
        .info-value {
            color: #111827;
        }
        .info-value strong {
            font-weight: 800;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 9pt;
        }
        .data-table th {
            background: #f1f5f9;
            padding: 7px 10px;
            text-align: left;
            font-weight: 700;
            color: #374151;
            border: 1px solid #e2e8f0;
            font-size: 8pt;
        }
        .data-table td {
            padding: 6px 10px;
            border: 1px solid #e2e8f0;
        }
        .status-ok { color: #16a34a; font-weight: 700; }
        .status-nok { color: #dc2626; font-weight: 700; }
        .photo-section {
            margin: 10px 0;
        }
        .photo-grid {
            display: inline-block;
        }
        .photo-grid img {
            width: 120px;
            height: 90px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #e2e8f0;
            margin-right: 6px;
            margin-bottom: 6px;
        }
        .perlengkapan-list {
            font-size: 9.5pt;
            line-height: 1.6;
        }
        .notes-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 12px;
            margin: 8px 0;
            font-size: 9.5pt;
            min-height: 30px;
        }
        .statement {
            font-style: italic;
            color: #374151;
            font-size: 9.5pt;
            margin: 8px 0;
        }
        .signature-area {
            margin-top: 24px;
            width: 100%;
        }
        .signature-area td {
            width: 50%;
            text-align: center;
            padding: 6px;
            vertical-align: top;
        }
        .sig-label {
            font-size: 9pt;
            font-weight: 700;
            color: #6b7280;
            margin-bottom: 6px;
        }
        .sig-box {
            border: 1px dashed #cbd5e1;
            height: 80px;
            margin: 8px auto;
            width: 180px;
            position: relative;
        }
        .sig-box img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        .sig-name {
            font-weight: 700;
            font-size: 10pt;
            margin-top: 4px;
        }
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <div class="page">
        {{-- HEADER --}}
        <div class="header">
            <h1>BERITA ACARA CEKLIST KENDARAAN</h1>
            <div class="company">PT ARTHA DAYA COALINDO</div>
            <div class="ref">ID Referensi: ADC-{{ str_pad($checklist->id, 10, '0', STR_PAD_LEFT) }}</div>
        </div>

        {{-- INFO --}}
        <table class="info-grid">
            <tr>
                <td class="info-label">Kendaraan:</td>
                <td class="info-value"><strong>{{ $checklist->nomor_kendaraan }}</strong> ({{ $checklist->jenis_kendaraan }})</td>
                <td class="info-label" style="width:80px">Waktu:</td>
                <td class="info-value">{{ $checklist->tanggal->format('Y-m-d') }} / {{ $checklist->jam_serah_terima }} ({{ $checklist->shift }})</td>
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
        <p style="margin-bottom:6px">
            KM Awal: <strong>{{ number_format($checklist->km_awal) }}</strong> |
            KM Akhir: <strong>{{ number_format($checklist->km_akhir ?? 0) }}</strong>
        </p>
        <p style="margin-bottom:10px">
            BBM: <strong>{{ $checklist->level_bbm }}%</strong>
            @if($checklist->bbm_terakhir)
                (Isi Terakhir: {{ $checklist->bbm_terakhir }})
            @endif
        </p>

        @if($checklist->foto_bbm_dashboard)
        <div class="photo-section">
            <p style="font-weight:700;font-size:9pt;margin-bottom:4px">Foto Indikator BBM & Dashboard:</p>
            <img src="{{ storage_path('app/public/' . $checklist->foto_bbm_dashboard) }}" style="width:200px;height:auto;border:1px solid #e2e8f0;border-radius:4px">
        </div>
        @endif

        {{-- 2. KONDISI FISIK & KETERANGAN --}}
        <div class="section-heading">2. Kondisi Fisik & Keterangan</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:40%">Bagian Kendaraan</th>
                    <th style="width:15%">Status</th>
                    <th style="width:45%">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $exteriorItems = [
                        'body_kendaraan' => 'Body kendaraan',
                        'kaca' => 'Kaca',
                        'spion' => 'Kaca Spion',
                        'lampu_utama' => 'Lampu utama',
                        'lampu_sein' => 'Lampu sein',
                        'ban' => 'Ban',
                        'velg' => 'Velg',
                        'wiper' => 'Wiper',
                    ];
                    $interiorItems = [
                        'jok' => 'Jok/kursi',
                        'dashboard' => 'Dashboard',
                        'ac' => 'AC',
                        'sabuk_pengaman' => 'Sabuk pengaman',
                        'audio' => 'Audio/Head Unit',
                        'kebersihan' => 'Kebersihan interior',
                    ];
                    $mesinItems = [
                        'mesin' => 'Mesin (suara normal)',
                        'oli' => 'Oli mesin',
                        'radiator' => 'Air radiator',
                        'rem' => 'Rem',
                        'kopling' => 'Kopling (manual)',
                        'transmisi' => 'Transmisi',
                        'indikator' => 'Indikator panel',
                    ];
                @endphp

                @foreach ($exteriorItems as $key => $label)
                    <tr>
                        <td>{{ $label }}</td>
                        <td class="{{ $checklist->exterior?->$key === 'ok' ? 'status-ok' : 'status-nok' }}">
                            {{ strtoupper($checklist->exterior?->$key ?? '-') }}
                        </td>
                        <td>{{ $checklist->exterior?->{$key . '_keterangan'} ?: '-' }}</td>
                    </tr>
                @endforeach

                @foreach ($interiorItems as $key => $label)
                    <tr>
                        <td>{{ $label }}</td>
                        <td class="{{ $checklist->interior?->$key === 'ok' ? 'status-ok' : 'status-nok' }}">
                            {{ strtoupper($checklist->interior?->$key ?? '-') }}
                        </td>
                        <td>{{ $checklist->interior?->{$key . '_keterangan'} ?: '-' }}</td>
                    </tr>
                @endforeach

                @foreach ($mesinItems as $key => $label)
                    <tr>
                        <td>{{ $label }}</td>
                        <td class="{{ $checklist->mesin?->$key === 'ok' ? 'status-ok' : 'status-nok' }}">
                            {{ strtoupper($checklist->mesin?->$key ?? '-') }}
                        </td>
                        <td>{{ $checklist->mesin?->{$key . '_keterangan'} ?: '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- FOTO EXTERIOR --}}
        @php
            $extPhotos = collect(['foto_depan', 'foto_kanan', 'foto_kiri', 'foto_belakang'])
                ->filter(fn($f) => $checklist->exterior?->$f)
                ->map(fn($f) => $checklist->exterior->$f);
        @endphp
        @if($extPhotos->isNotEmpty())
        <div class="photo-section">
            <p style="font-weight:700;font-size:9pt;margin-bottom:4px">Foto Exterior:</p>
            @foreach($extPhotos as $path)
                <img src="{{ storage_path('app/public/' . $path) }}" style="width:120px;height:90px;object-fit:cover;border:1px solid #e2e8f0;border-radius:4px;margin-right:4px">
            @endforeach
        </div>
        @endif

        {{-- FOTO INTERIOR --}}
        @php
            $intPhotos = collect(['foto_1', 'foto_2', 'foto_3'])
                ->filter(fn($f) => $checklist->interior?->$f)
                ->map(fn($f) => $checklist->interior->$f);
        @endphp
        @if($intPhotos->isNotEmpty())
        <div class="photo-section">
            <p style="font-weight:700;font-size:9pt;margin-bottom:4px">Foto Interior:</p>
            @foreach($intPhotos as $path)
                <img src="{{ storage_path('app/public/' . $path) }}" style="width:120px;height:90px;object-fit:cover;border:1px solid #e2e8f0;border-radius:4px;margin-right:4px">
            @endforeach
        </div>
        @endif

        {{-- FOTO MESIN --}}
        @php
            $mesinPhotos = collect(['foto_1', 'foto_2', 'foto_3'])
                ->filter(fn($f) => $checklist->mesin?->$f)
                ->map(fn($f) => $checklist->mesin->$f);
        @endphp
        @if($mesinPhotos->isNotEmpty())
        <div class="photo-section">
            <p style="font-weight:700;font-size:9pt;margin-bottom:4px">Foto Ruang Mesin:</p>
            @foreach($mesinPhotos as $path)
                <img src="{{ storage_path('app/public/' . $path) }}" style="width:120px;height:90px;object-fit:cover;border:1px solid #e2e8f0;border-radius:4px;margin-right:4px">
            @endforeach
        </div>
        @endif

        {{-- 3. PERLENGKAPAN --}}
        <div class="section-heading">3. Perlengkapan Tersedia</div>
        @php
            $perlengkapanLabels = [
                'stnk' => 'STNK',
                'kir' => 'Kartu KIR',
                'dongkrak' => 'Dongkrak',
                'toolkit' => 'Toolkit',
                'segitiga' => 'Segitiga Pengaman',
                'apar' => 'APAR',
                'ban_cadangan' => 'Ban cadangan',
            ];
            $availableItems = [];
            foreach ($perlengkapanLabels as $key => $label) {
                if ($checklist->perlengkapan?->$key === 'ada') {
                    $availableItems[] = $label;
                }
            }
        @endphp
        <div class="perlengkapan-list">
            {{ implode(', ', $availableItems) ?: 'Tidak ada data perlengkapan.' }}
        </div>

        {{-- 4. CATATAN & VALIDASI --}}
        <div class="section-heading">4. Catatan Khusus & Validasi</div>
        <div class="notes-box">
            {{ $checklist->catatan_khusus ?: '-' }}
        </div>
        <p class="statement">
            "Dengan ini saya menyatakan, bahwa saya sudah melakukan pemeriksaan secara menyeluruh (eksterior, interior, mesin, dan kelengkapan) kendaraan operasional dan kendaraan berada dalam kondisi baik dan siap utk digunakan."
        </p>

        {{-- TANDA TANGAN --}}
        <table class="signature-area">
            <tr>
                <td>
                    <div class="sig-label">Tanda Tangan Driver Yang Menyerahkan:</div>
                    <div class="sig-box">
                        @if($checklist->tanda_tangan_serah)
                            <img src="{{ storage_path('app/public/' . $checklist->tanda_tangan_serah) }}" alt="TTD Serah">
                        @endif
                    </div>
                    <div class="sig-name">({{ $checklist->driver_serah }})</div>
                </td>
                <td>
                    <div class="sig-label">Tanda Tangan Driver Yang Menerima:</div>
                    <div class="sig-box">
                        @if($checklist->tanda_tangan_terima)
                            <img src="{{ storage_path('app/public/' . $checklist->tanda_tangan_terima) }}" alt="TTD Terima">
                        @endif
                    </div>
                    <div class="sig-name">({{ $checklist->driver_terima }})</div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
