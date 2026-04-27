@php
    $fmtRp = fn ($n) => 'Rp '.number_format((float) $n, 0, ',', '.');
    $fmtLiter = fn ($n) => number_format((float) $n, 3, ',', '.');
    $fmtKm = fn ($n) => number_format((int) round((float) $n), 0, ',', '.');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Portal BBM Operasional — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
</head>
<body class="dash-body">
    <div class="dash-bg-cubes" aria-hidden="true"></div>
    <div class="dash-bg-stardust" aria-hidden="true"></div>
    <div class="dash-bg-orb-gold" aria-hidden="true"></div>
    <div class="dash-bg-orb-blue" aria-hidden="true"></div>
    <div class="dash-bg-wave" aria-hidden="true">
        <svg viewBox="0 0 1440 400" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:100%" preserveAspectRatio="none">
            <path d="M0 300 C 300 250, 400 350, 700 200 C 1000 50, 1200 150, 1440 50 L 1440 400 L 0 400 Z" fill="url(#bbmop_fill)"></path>
            <path d="M0 300 C 300 250, 400 350, 700 200 C 1000 50, 1200 150, 1440 50" stroke="url(#bbmop_stroke)" stroke-width="3" stroke-linecap="round"></path>
            <defs>
                <linearGradient id="bbmop_fill" x1="720" y1="50" x2="720" y2="400" gradientUnits="userSpaceOnUse">
                    <stop stop-color="#D4AF37" stop-opacity="0.12"></stop>
                    <stop offset="1" stop-color="#0A2342" stop-opacity="0"></stop>
                </linearGradient>
                <linearGradient id="bbmop_stroke" x1="0" y1="150" x2="1440" y2="150" gradientUnits="userSpaceOnUse">
                    <stop stop-color="#0A2342"></stop>
                    <stop offset="0.4" stop-color="#D4AF37"></stop>
                    <stop offset="1" stop-color="#60A5FA"></stop>
                </linearGradient>
            </defs>
        </svg>
    </div>

    @include('admin.partials.dash-admin-nav', [
        'pageTitle' => 'Portal BBM Operasional',
        'pageSubtitle' => ($bbmPortalChartsOnly ?? false)
            ? 'Ringkasan & grafik pengisian BBM (akses terbatas)'
            : 'Insight laporan pengisian BBM dari driver',
        'navChipLabel' => ($bbmPortalChartsOnly ?? false) ? 'MANAGER' : 'ADMIN',
        'navChipClass' => ($bbmPortalChartsOnly ?? false) ? 'dash-chip-manager' : 'dash-chip-admin',
    ])

    <div class="admin-shell" style="position:relative;z-index:1">
        <div class="portal-wrapper">
            <p class="bbm-portal-month-kicker">
                Data bulan: <span class="bbm-portal-month-kicker__highlight">{{ $stats['month_label'] }}</span>
            </p>

            <div class="portal-stats-row portal-stats-row--bbm">
                <div class="portal-stat-card" style="--accent:#002a7a">
                    <div class="portal-stat-icon" style="background:rgba(0,42,122,.1);color:#002a7a"><i class="bi bi-clipboard-data"></i></div>
                    <div><div class="portal-stat-value">{{ $stats['total_reports_all'] }}</div><div class="portal-stat-label">Total Laporan BBM (keseluruhan)</div></div>
                </div>
                <div class="portal-stat-card" style="--accent:#0d9488">
                    <div class="portal-stat-icon" style="background:rgba(13,148,136,.1);color:#0d9488"><i class="bi bi-calendar-week"></i></div>
                    <div><div class="portal-stat-value">{{ $stats['month_reports'] }}</div><div class="portal-stat-label">Total Laporan BBM (bulanan)</div></div>
                </div>
                <div class="portal-stat-card" style="--accent:#d97706">
                    <div class="portal-stat-icon" style="background:rgba(217,119,6,.1);color:#d97706"><i class="bi bi-droplet-half"></i></div>
                    <div><div class="portal-stat-value" style="font-size: 0.89rem;">{{ $fmtLiter($stats['month_liter']) }} L</div><div class="portal-stat-label">Total Liter (bulanan)</div></div>
                </div>
                <div class="portal-stat-card" style="--accent:#16a34a">
                    <div class="portal-stat-icon" style="background:rgba(22,163,74,.1);color:#16a34a"><i class="bi bi-currency-exchange"></i></div>
                    <div><div class="portal-stat-value" style="font-size: 0.89rem;">{{ $fmtRp($stats['month_rupiah']) }}</div><div class="portal-stat-label">Total Biaya BBM (bulanan)</div></div>
                </div>
                <div class="portal-stat-card" style="--accent:#dc2626">
                    <div class="portal-stat-icon" style="background:rgba(220,38,38,.1);color:#dc2626"><i class="bi bi-arrow-up-circle"></i></div>
                    <div>
                        <div class="portal-stat-value" style="font-size:1rem;line-height:1.3">
                            @if($stats['boros'])
                                <strong>{{ $stats['boros']->nomor_kendaraan }}</strong><br>
                                <span class="portal-stat-sublabel">{{ $fmtLiter($stats['boros']->liters) }} L · {{ $fmtRp($stats['boros']->rupiah) }}</span>
                            @else
                                —
                            @endif
                        </div>
                        <div class="portal-stat-label">Kendaraan paling boros (Jumlah liter tertinggi, bulan ini)</div>
                    </div>
                </div>
                <div class="portal-stat-card" style="--accent:#2563eb">
                    <div class="portal-stat-icon" style="background:rgba(37,99,235,.1);color:#2563eb"><i class="bi bi-arrow-down-circle"></i></div>
                    <div>
                        <div class="portal-stat-value" style="font-size:1rem;line-height:1.3">
                            @if($stats['efisien'])
                                <strong>{{ $stats['efisien']->nomor_kendaraan }}</strong><br>
                                <span class="portal-stat-sublabel">{{ $fmtLiter($stats['efisien']->liters) }} L · {{ $fmtRp($stats['efisien']->rupiah) }}</span>
                            @else
                                —
                            @endif
                        </div>
                        <div class="portal-stat-label">Kendaraan paling efisien (Jumlah liter terendah, bulan ini)</div>
                    </div>
                </div>
            </div>

            <div class="portal-charts-grid">
                <div class="portal-chart-card portal-chart-card--wide">
                    <div class="portal-chart-title-row">
                        <div class="portal-chart-title">Pengeluaran BBM per bulan (perbandingan tahun)</div>
                        <div class="bbm-year-toggles" id="bbm-year-toggles" aria-label="Pilih tahun untuk grafik"></div>
                    </div>
                    <p class="bbm-chart-hint" style="margin:0 0 8px;font-size:0.78rem;color:#64748b">Centang satu atau lebih tahun untuk membandingkan batang nominal (Jan–Des).</p>
                    <div class="portal-chart-container" style="height:260px"><canvas id="bbmChartRupiahYear"></canvas></div>
                </div>
                <div class="portal-chart-card portal-chart-card--wide">
                    <div class="portal-chart-title">Liter per kendaraan (12 bulan terakhir, top 5 unit)</div>
                    <div class="portal-chart-container" style="height:280px"><canvas id="bbmChartLiterVehicle"></canvas></div>
                </div>
                <div class="portal-chart-card portal-chart-card--wide">
                    <div class="portal-chart-title">Top driver — frekuensi pengisian (bulan berjalan)</div>
                    <div class="portal-chart-container" style="height:260px"><canvas id="bbmChartDriverFreq"></canvas></div>
                </div>
            </div>

            @unless($bbmPortalChartsOnly ?? false)
            <div class="portal-section" id="section-bbm-table">
                <div class="portal-section-header">
                    <div class="portal-section-title"><i class="bi bi-table"></i> Data laporan BBM</div>
                </div>
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Tanggal</th>
                                <th>Waktu</th>
                                <th>Driver</th>
                                <th>Kendaraan</th>
                                <th>KM Awal → Akhir</th>
                                <th>Liter</th>
                                <th>Rp/L</th>
                                <th>Total</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reports as $r)
                                @php
                                    $waktuStr = is_string($r->waktu) ? substr($r->waktu, 0, 5) : optional($r->waktu)->format('H:i') ?? '—';
                                @endphp
                                <tr>
                                    <td>{{ ($reports->currentPage() - 1) * $reports->perPage() + $loop->iteration }}</td>
                                    <td>{{ $r->tanggal->format('d/m/Y') }}</td>
                                    <td>{{ $waktuStr }}</td>
                                    <td>{{ $r->user?->name ?? '—' }}<br><span class="sppd-cell-muted">{{ $r->user?->username }}</span></td>
                                    <td><strong>{{ $r->nomor_kendaraan }}</strong><br><span class="sppd-cell-muted">{{ $r->jenis_kendaraan }}</span></td>
                                    <td>{{ $fmtKm($r->odometer_sebelum) }} → {{ $fmtKm($r->odometer_sesudah) }}</td>
                                    <td>{{ $fmtLiter($r->liter) }}</td>
                                    <td>{{ $fmtRp($r->harga_per_liter) }}</td>
                                    <td><strong>{{ $fmtRp($r->total_harga) }}</strong></td>
                                    <td>
                                        <button
                                            type="button"
                                            class="btn btn-sm sppd-icon-btn sppd-btn-primary bbm-btn-detail"
                                            data-json-url="{{ route('admin.portal-bbm-operasional.json', $r) }}"
                                            title="Detail"
                                            aria-label="Detail laporan BBM"
                                        ><i class="bi bi-eye-fill"></i></button>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="10" class="portal-empty">Belum ada laporan BBM dari driver.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="admin-pagination mt-4">{{ $reports->links() }}</div>
            </div>
            @endunless
        </div>
    </div>

    @unless($bbmPortalChartsOnly ?? false)
    {{-- Detail modal (pola mirip sppd/index) --}}
    <div id="bbm-modal-detail" class="modal-overlay" style="display:none">
        <div class="modal-box profile-card sppd-modal-box" style="max-width:min(720px,100%);text-align:left;max-height:86vh;overflow:auto">
            <h3>Detail Laporan BBM</h3>
            <div id="bbm-detail-body" class="sppd-detail-html"></div>
            <div class="ppm-modal-actions">
                <button type="button" class="btn btn-sm sppd-icon-btn sppd-btn-secondary-lite" data-close-bbm-modal title="Tutup" aria-label="Tutup"><i class="bi bi-x-lg"></i></button>
            </div>
        </div>
    </div>
    @endunless

    <style>
        .portal-chart-title-row { display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:10px; margin-bottom:6px; }
        .bbm-year-toggles { display:flex; flex-wrap:wrap; gap:8px 14px; align-items:center; }
        .bbm-year-toggles label { display:inline-flex; align-items:center; gap:6px; font-size:0.78rem; font-weight:600; color:#475569; cursor:pointer; }
        .dash-body.dark .bbm-year-toggles label { color:rgba(200,218,255,0.75); }
        .portal-stat-sublabel { font-size:0.78rem; font-weight:600; color:#64748b; }
        .dash-body.dark .portal-stat-sublabel { color:rgba(200,218,255,0.55); }
    </style>

    <script>
    (function () {
        const BBM_PORTAL_CHARTS_ONLY = @json($bbmPortalChartsOnly ?? false);
        const MONTHLY_RUPIAH_BY_YEAR = @json($monthlyRupiahByYear);
        const YEARS_AVAILABLE = @json($yearsAvailable);
        const LITER_VEH_LABELS = @json($literPerVehicleLabels);
        const LITER_VEH_SERIES = @json($literPerVehicleSeries);
        const TOP_DRIVERS_MONTH = @json($topDriversMonth);

        const MONTH_LABELS = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];

        let chartRupiah, chartLiterVeh, chartDrvFreq;

        function fmtRpShort(n) {
            const x = Number(n) || 0;
            if (x >= 1e9) return (x / 1e9).toFixed(1) + ' M';
            if (x >= 1e6) return (x / 1e6).toFixed(1) + ' jt';
            if (x >= 1e3) return (x / 1e3).toFixed(0) + ' rb';
            return String(Math.round(x));
        }

        function buildYearCheckboxes() {
            const wrap = document.getElementById('bbm-year-toggles');
            if (!wrap || !YEARS_AVAILABLE.length) return;
            const defaultPick = YEARS_AVAILABLE.slice(-2);
            YEARS_AVAILABLE.forEach((y) => {
                const id = 'bbm-yr-' + y;
                const lab = document.createElement('label');
                const cb = document.createElement('input');
                cb.type = 'checkbox';
                cb.value = String(y);
                cb.id = id;
                cb.checked = defaultPick.includes(y);
                lab.appendChild(cb);
                lab.appendChild(document.createTextNode(' ' + y));
                wrap.appendChild(lab);
            });
            wrap.querySelectorAll('input[type="checkbox"]').forEach((el) => {
                el.addEventListener('change', () => buildCharts());
            });
        }

        function selectedYears() {
            const wrap = document.getElementById('bbm-year-toggles');
            if (!wrap) return YEARS_AVAILABLE.slice(-2);
            const ys = Array.from(wrap.querySelectorAll('input[type="checkbox"]:checked')).map((c) => parseInt(c.value, 10));
            return ys.length ? ys.sort((a, b) => a - b) : YEARS_AVAILABLE.slice(-2);
        }

        const palette = ['#002a7a', '#16a34a', '#d97706', '#7c3aed', '#dc2626', '#0891b2', '#ca8a04', '#64748b'];

        function barFill(hex, alpha) {
            const m = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
            if (!m) return hex;
            return 'rgba(' + parseInt(m[1], 16) + ',' + parseInt(m[2], 16) + ',' + parseInt(m[3], 16) + ',' + alpha + ')';
        }

        function buildCharts() {
            [chartRupiah, chartLiterVeh, chartDrvFreq].forEach((c) => { try { c?.destroy(); } catch (e) {} });
            chartRupiah = chartLiterVeh = chartDrvFreq = null;

            const dark = document.body.classList.contains('dark');
            const grid = dark ? 'rgba(200,218,255,0.1)' : 'rgba(0,0,0,0.08)';
            const tick = dark ? 'rgba(200,218,255,0.65)' : '#64748b';
            const bdr = dark ? 'rgba(200,218,255,0.12)' : 'rgba(255,255,255,0.8)';
            const common = { responsive: true, maintainAspectRatio: false };

            const years = selectedYears();
            const elR = document.getElementById('bbmChartRupiahYear');
            if (elR && MONTHLY_RUPIAH_BY_YEAR && years.length) {
                const fillA = dark ? 0.62 : 0.78;
                const datasets = years.map((y, i) => {
                    const arr = MONTHLY_RUPIAH_BY_YEAR[String(y)] || MONTHLY_RUPIAH_BY_YEAR[y] || [];
                    const col = palette[i % palette.length];
                    return {
                        label: String(y),
                        data: arr.map((v) => Math.round(Number(v) / 1000)),
                        backgroundColor: barFill(col, fillA),
                        borderColor: col,
                        borderWidth: 1,
                        borderRadius: 5,
                        borderSkipped: false,
                    };
                });
                chartRupiah = new Chart(elR, {
                    type: 'bar',
                    data: { labels: MONTH_LABELS, datasets },
                    options: {
                        ...common,
                        interaction: { mode: 'index', intersect: false },
                        datasets: { bar: { maxBarThickness: 28 } },
                        plugins: {
                            legend: { display: true, position: 'top', labels: { color: tick, boxWidth: 12 } },
                            tooltip: {
                                callbacks: {
                                    label(ctx) {
                                        const y = years[ctx.datasetIndex];
                                        const raw = (MONTHLY_RUPIAH_BY_YEAR[String(y)] || MONTHLY_RUPIAH_BY_YEAR[y] || [])[ctx.dataIndex] || 0;
                                        return ' ' + ctx.dataset.label + ': Rp ' + Number(raw).toLocaleString('id-ID');
                                    },
                                },
                            },
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: { display: true, text: 'Rp (÷1000)', color: tick },
                                ticks: { color: tick, callback: (v) => fmtRpShort(v * 1000) },
                                grid: { color: grid },
                            },
                            x: { ticks: { color: tick, font: { size: 11 } }, grid: { color: grid } },
                        },
                    },
                });
            }

            const elV = document.getElementById('bbmChartLiterVehicle');
            if (elV && LITER_VEH_LABELS.length && Object.keys(LITER_VEH_SERIES).length) {
                const nopolList = Object.keys(LITER_VEH_SERIES);
                const fillLit = dark ? 0.62 : 0.78;
                const datasets = nopolList.map((nopol, i) => {
                    const col = palette[i % palette.length];
                    return {
                        label: nopol,
                        data: (LITER_VEH_SERIES[nopol] || []).map((v) => Number(v)),
                        backgroundColor: barFill(col, fillLit),
                        borderColor: col,
                        borderWidth: 1,
                        borderRadius: 5,
                        borderSkipped: false,
                    };
                });
                chartLiterVeh = new Chart(elV, {
                    type: 'bar',
                    data: { labels: LITER_VEH_LABELS, datasets },
                    options: {
                        ...common,
                        interaction: { mode: 'index', intersect: false },
                        datasets: { bar: { maxBarThickness: 22 } },
                        plugins: {
                            legend: { display: true, position: 'bottom', labels: { color: tick, font: { size: 10 }, boxWidth: 10 } },
                            tooltip: {
                                callbacks: {
                                    label(ctx) {
                                        return ' ' + ctx.dataset.label + ': ' + Number(ctx.raw).toLocaleString('id-ID', { maximumFractionDigits: 3 }) + ' L';
                                    },
                                },
                            },
                        },
                        scales: {
                            y: { beginAtZero: true, title: { display: true, text: 'Liter', color: tick }, ticks: { color: tick }, grid: { color: grid } },
                            x: { ticks: { maxRotation: 45, font: { size: 9 }, color: tick }, grid: { color: grid } },
                        },
                    },
                });
            }

            const elD = document.getElementById('bbmChartDriverFreq');
            if (elD && TOP_DRIVERS_MONTH.length) {
                chartDrvFreq = new Chart(elD, {
                    type: 'bar',
                    data: {
                        labels: TOP_DRIVERS_MONTH.map((d) => d.name || d.username || 'Driver'),
                        datasets: [{
                            data: TOP_DRIVERS_MONTH.map((d) => Number(d.cnt)),
                            backgroundColor: TOP_DRIVERS_MONTH.map((_, i) => palette[i % palette.length]),
                            borderRadius: 6,
                        }],
                    },
                    options: {
                        ...common,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label(ctx) {
                                        return ' ' + ctx.parsed.y + ' kali isi BBM';
                                    },
                                },
                            },
                        },
                        scales: {
                            y: { beginAtZero: true, ticks: { stepSize: 1, color: tick }, title: { display: true, text: 'Jumlah laporan', color: tick }, grid: { color: grid } },
                            x: { ticks: { maxRotation: 40, font: { size: 10 }, color: tick }, grid: { color: grid } },
                        },
                    },
                });
            }
        }

        buildYearCheckboxes();
        buildCharts();

        function esc(s) {
            const d = document.createElement('div');
            d.textContent = s ?? '';
            return d.innerHTML;
        }
        function formatRp(n) {
            const x = Number(n) || 0;
            return 'Rp ' + x.toLocaleString('id-ID');
        }

        function renderBbmDetail(d) {
            const odo = d.odometer_photo_url
                ? `<a href="${String(d.odometer_photo_url).replace(/"/g, '&quot;')}" target="_blank" rel="noopener"><img src="${String(d.odometer_photo_url).replace(/"/g, '&quot;')}" class="sppd-photo-thumb" alt="Odometer"></a>`
                : '<p class="portal-empty" style="padding:8px">—</p>';
            const struk = d.struk_photo_url
                ? `<a href="${String(d.struk_photo_url).replace(/"/g, '&quot;')}" target="_blank" rel="noopener"><img src="${String(d.struk_photo_url).replace(/"/g, '&quot;')}" class="sppd-photo-thumb" alt="Struk"></a>`
                : '<p class="portal-empty" style="padding:8px">—</p>';
            return `
                <table class="info-table sppd-mini-table">
                    <tr><td class="label">Driver</td><td>${esc(d.driver_name)} (${esc(d.driver_username || '—')})</td></tr>
                    <tr><td class="label">Kendaraan</td><td>${esc(d.nomor_kendaraan)} — ${esc(d.jenis_kendaraan)}</td></tr>
                    <tr><td class="label">Tanggal</td><td>${esc(d.tanggal)}</td></tr>
                    <tr><td class="label">Waktu</td><td>${esc(d.waktu)}</td></tr>
                    <tr><td class="label">KM sebelum</td><td>${esc(d.odometer_sebelum)}</td></tr>
                    <tr><td class="label">KM sesudah</td><td>${esc(d.odometer_sesudah)}</td></tr>
                    <tr><td class="label">Liter</td><td>${esc(String(d.liter))}</td></tr>
                    <tr><td class="label">Harga / L</td><td>${formatRp(d.harga_per_liter)}</td></tr>
                    <tr><td class="label">Total</td><td><strong>${formatRp(d.total_harga)}</strong></td></tr>
                </table>
                <p class="sppd-detail-sub">Foto odometer</p>
                <div class="sppd-photo-grid">${odo}</div>
                <p class="sppd-detail-sub">Foto struk</p>
                <div class="sppd-photo-grid">${struk}</div>
            `;
        }

        if (!BBM_PORTAL_CHARTS_ONLY) {
            document.querySelectorAll('.bbm-btn-detail').forEach((btn) => {
                btn.addEventListener('click', async () => {
                    const url = btn.getAttribute('data-json-url');
                    const modal = document.getElementById('bbm-modal-detail');
                    const body = document.getElementById('bbm-detail-body');
                    body.innerHTML = '<p>Memuat…</p>';
                    modal.style.display = 'flex';
                    try {
                        const res = await fetch(url, { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                        if (!res.ok) throw new Error('HTTP ' + res.status);
                        const j = await res.json();
                        if (!j.report) throw new Error('Invalid payload');
                        body.innerHTML = renderBbmDetail(j.report);
                    } catch (e) {
                        body.innerHTML = '<p>Gagal memuat data.</p>';
                    }
                });
            });
            document.querySelectorAll('[data-close-bbm-modal]').forEach((el) => {
                el.addEventListener('click', () => { document.getElementById('bbm-modal-detail').style.display = 'none'; });
            });
            document.getElementById('bbm-modal-detail')?.addEventListener('click', (e) => {
                if (e.target.id === 'bbm-modal-detail') e.currentTarget.style.display = 'none';
            });
        }

        const body = document.body;
        const themeBtn = document.getElementById('dash-theme-toggle');
        const themeIcon = document.getElementById('dash-theme-icon');
        const themeLabel = document.getElementById('dash-theme-label');
        const navActions = document.getElementById('dash-nav-actions');
        const menuBtn = document.getElementById('dash-mobile-menu-btn');
        const menuIcon = document.getElementById('dash-mobile-menu-icon');

        const applyTheme = (isDark) => {
            body.classList.toggle('dark', isDark);
            if (themeIcon) themeIcon.className = isDark ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
            if (themeLabel) themeLabel.textContent = isDark ? 'Light Mode' : 'Dark Mode';
            buildCharts();
        };
        const saved = localStorage.getItem('vms-theme') || localStorage.getItem('vms-dash-theme');
        themeBtn?.addEventListener('click', () => {
            const next = !body.classList.contains('dark');
            applyTheme(next);
            localStorage.setItem('vms-theme', next ? 'dark' : 'light');
            localStorage.setItem('vms-dash-theme', next ? 'dark' : 'light');
        });
        applyTheme(saved === 'dark');

        const closeMobileMenu = () => {
            navActions?.classList.remove('mobile-open');
            if (menuIcon) menuIcon.className = 'bi bi-list';
            menuBtn?.setAttribute('aria-expanded', 'false');
        };
        menuBtn?.addEventListener('click', (e) => {
            e.stopPropagation();
            const opened = navActions?.classList.toggle('mobile-open');
            if (menuIcon) menuIcon.className = opened ? 'bi bi-x-lg' : 'bi bi-list';
            menuBtn?.setAttribute('aria-expanded', String(!!opened));
        });
        document.addEventListener('click', (e) => {
            if (!navActions?.contains(e.target) && !menuBtn?.contains(e.target)) closeMobileMenu();
        });
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 992) closeMobileMenu();
        });
    })();
    </script>
</body>
</html>
