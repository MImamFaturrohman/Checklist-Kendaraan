<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Log Foto Fisik - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="dash-body">
    <div class="admin-shell">
        <header class="checklist-topbar">
            <div><h1 class="dash-brand-title">Log Foto Fisik</h1><p class="dash-brand-sub">PT ARTHA DAYA COALINDO</p></div>
            <div class="flex items-center gap-2">
                <span class="dash-chip dash-chip-admin">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke="currentColor" stroke-width="2"/></svg>
                    ADMIN
                </span>
                <a href="{{ route('dashboard') }}" class="checklist-icon-btn"><svg width="19" height="19" viewBox="0 0 24 24" fill="none"><path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
            </div>
        </header>
        <div class="admin-card" data-tab-group>
            @php
                $baseUrl = 'http://127.0.0.1:8000';
                $resolvePhotoUrl = function (?string $path) use ($baseUrl) {
                    if (!$path) {
                        return null;
                    }

                    if (str_starts_with($path, 'http://localhost')) {
                        return str_replace('http://localhost', $baseUrl, $path);
                    }

                    if (str_starts_with($path, $baseUrl)) {
                        return $path;
                    }

                    if (str_starts_with($path, '/storage/')) {
                        return $baseUrl . $path;
                    }

                    if (str_starts_with($path, 'storage/')) {
                        return $baseUrl . '/' . $path;
                    }

                    return $baseUrl . '/storage/' . ltrim($path, '/');
                };

                $collectRows = function ($fieldMap, ?string $relation = null) use ($checklists, $resolvePhotoUrl) {
                    $rows = collect();

                    foreach ($checklists as $c) {
                        $waktuChecklist = trim(($c->tanggal?->format('d/m/Y') ?? '-') . ' ' . ($c->jam_serah_terima ?? ''));
                        $photos = collect();
                        foreach ($fieldMap as $field => $label) {
                            $path = $relation ? data_get($c, "{$relation}.{$field}") : data_get($c, $field);
                            if (!$path) {
                                continue;
                            }

                            $photos->push([
                                'label' => $label,
                                'url' => $resolvePhotoUrl($path),
                            ]);
                        }

                        if ($photos->isNotEmpty()) {
                            $rows->push([
                                'id' => $c->id,
                                'waktu' => $waktuChecklist,
                                'created_sort' => $c->created_at?->timestamp ?? 0,
                                'unit' => $c->nomor_kendaraan,
                                'photos' => $photos->values(),
                            ]);
                        }
                    }

                    return $rows->sortByDesc('created_sort')->values();
                };

                $rowsEksterior = $collectRows([
                    'foto_depan' => 'Depan',
                    'foto_kanan' => 'Kanan',
                    'foto_kiri' => 'Kiri',
                    'foto_belakang' => 'Belakang',
                ], 'exterior');

                $rowsInterior = $collectRows([
                    'foto_1' => 'Interior 1',
                    'foto_2' => 'Interior 2',
                    'foto_3' => 'Interior 3',
                ], 'interior');

                $rowsMesin = $collectRows([
                    'foto_1' => 'Mesin 1',
                    'foto_2' => 'Mesin 2',
                    'foto_3' => 'Mesin 3',
                ], 'mesin');

                $rowsBbm = $collectRows([
                    'foto_bbm_dashboard' => 'BBM',
                ]);
            @endphp

            <div class="admin-tabs">
                <button class="admin-tab active" data-tab-btn="exterior">Eksterior</button>
                <button class="admin-tab" data-tab-btn="interior">Interior</button>
                <button class="admin-tab" data-tab-btn="mesin">Mesin</button>
                <button class="admin-tab" data-tab-btn="bbm">BBM</button>
            </div>

            {{-- Exterior photos --}}
            <div data-tab-panel="exterior">
                <p style="font-size:0.78rem;color:#64748b;margin-bottom:10px;font-weight:600">{{ $rowsEksterior->count() }} foto eksterior ditemukan</p>
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Nomor Unit</th>
                                <th>Foto</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rowsEksterior as $row)
                                <tr>
                                    <td>{{ $row['waktu'] }}</td>
                                    <td><strong>{{ $row['unit'] }}</strong></td>
                                    <td>
                                        <div style="display:flex;gap:8px;flex-wrap:wrap">
                                            @foreach($row['photos'] as $photo)
                                                <a href="{{ $photo['url'] }}" target="_blank" rel="noopener" title="{{ $photo['label'] }}">
                                                    <img
                                                        src="{{ $photo['url'] }}"
                                                        alt="{{ $photo['label'] }}"
                                                        class="photo-log-table-thumb"
                                                        loading="lazy"
                                                        style="width:110px;height:82px;min-width:110px;max-width:110px;min-height:82px;max-height:82px;object-fit:cover;display:block;"
                                                    >
                                                </a>
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" style="text-align:center;color:#94a3b8;padding:24px">Belum ada foto eksterior.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Interior photos --}}
            <div data-tab-panel="interior" style="display:none">
                <p style="font-size:0.78rem;color:#64748b;margin-bottom:10px;font-weight:600">{{ $rowsInterior->count() }} foto interior ditemukan</p>
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Nomor Unit</th>
                                <th>Foto</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rowsInterior as $row)
                                <tr>
                                    <td>{{ $row['waktu'] }}</td>
                                    <td><strong>{{ $row['unit'] }}</strong></td>
                                    <td>
                                        <div style="display:flex;gap:8px;flex-wrap:wrap">
                                            @foreach($row['photos'] as $photo)
                                                <a href="{{ $photo['url'] }}" target="_blank" rel="noopener" title="{{ $photo['label'] }}">
                                                    <img
                                                        src="{{ $photo['url'] }}"
                                                        alt="{{ $photo['label'] }}"
                                                        class="photo-log-table-thumb"
                                                        loading="lazy"
                                                        style="width:110px;height:82px;min-width:110px;max-width:110px;min-height:82px;max-height:82px;object-fit:cover;display:block;"
                                                    >
                                                </a>
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" style="text-align:center;color:#94a3b8;padding:24px">Belum ada foto interior.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Mesin photos --}}
            <div data-tab-panel="mesin" style="display:none">
                <p style="font-size:0.78rem;color:#64748b;margin-bottom:10px;font-weight:600">{{ $rowsMesin->count() }} foto mesin ditemukan</p>
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Nomor Unit</th>
                                <th>Foto</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rowsMesin as $row)
                                <tr>
                                    <td>{{ $row['waktu'] }}</td>
                                    <td><strong>{{ $row['unit'] }}</strong></td>
                                    <td>
                                        <div style="display:flex;gap:8px;flex-wrap:wrap">
                                            @foreach($row['photos'] as $photo)
                                                <a href="{{ $photo['url'] }}" target="_blank" rel="noopener" title="{{ $photo['label'] }}">
                                                    <img
                                                        src="{{ $photo['url'] }}"
                                                        alt="{{ $photo['label'] }}"
                                                        class="photo-log-table-thumb"
                                                        loading="lazy"
                                                        style="width:110px;height:82px;min-width:110px;max-width:110px;min-height:82px;max-height:82px;object-fit:cover;display:block;"
                                                    >
                                                </a>
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" style="text-align:center;color:#94a3b8;padding:24px">Belum ada foto mesin.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- BBM photos --}}
            <div data-tab-panel="bbm" style="display:none">
                <p style="font-size:0.78rem;color:#64748b;margin-bottom:10px;font-weight:600">{{ $rowsBbm->count() }} foto BBM ditemukan</p>
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Nomor Unit</th>
                                <th>Foto</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rowsBbm as $row)
                                <tr>
                                    <td>{{ $row['waktu'] }}</td>
                                    <td><strong>{{ $row['unit'] }}</strong></td>
                                    <td>
                                        <div style="display:flex;gap:8px;flex-wrap:wrap">
                                            @foreach($row['photos'] as $photo)
                                                <a href="{{ $photo['url'] }}" target="_blank" rel="noopener" title="{{ $photo['label'] }}">
                                                    <img
                                                        src="{{ $photo['url'] }}"
                                                        alt="{{ $photo['label'] }}"
                                                        class="photo-log-table-thumb"
                                                        loading="lazy"
                                                        style="width:110px;height:82px;min-width:110px;max-width:110px;min-height:82px;max-height:82px;object-fit:cover;display:block;"
                                                    >
                                                </a>
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" style="text-align:center;color:#94a3b8;padding:24px">Belum ada foto BBM.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
