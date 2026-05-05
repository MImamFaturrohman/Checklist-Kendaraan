<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Laporan Kejadian — {{ config('app.name') }}</title>
    @include('partials.favicon')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Selaras dengan admin/peminjaman & admin/sppd */
        .lk-admin-name { font-weight: 700; color: var(--dash-text-primary, #0f172a); }
        .lk-admin-meta { font-size: 0.76rem; opacity: 0.85; color: #64748b; }
        .dash-body.dark .lk-admin-meta { color: rgba(200, 218, 255, 0.62); }
        .lk-admin-lokasi { font-size: 0.84rem; line-height: 1.45; max-width: 280px; }
        .lk-admin-waktu { font-size: 0.84rem; white-space: nowrap; }
        .peminj-pdf {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 5px 10px; background: #002a7a; color: #fff !important;
            border-radius: 8px; font-size: 0.75rem; font-weight: 700; text-decoration: none;
            transition: background 0.15s;
        }
        .peminj-pdf:hover { background: #0038a8; color: #fff !important; }
        .dash-body.dark .peminj-pdf {
            background: rgba(30, 64, 128, 0.95);
            border: 1px solid rgba(212, 175, 55, 0.28);
        }
        .dash-body.dark .peminj-pdf:hover { background: rgba(40, 80, 150, 0.98); }
        .lk-kat { font-size: 0.72rem; font-weight: 700; padding: 5px 12px; border-radius: 999px; }
        .lk-kat-inc { background: rgba(239, 68, 68, 0.12); color: #b91c1c; border: 1px solid rgba(239, 68, 68, 0.25); }
        .lk-kat-nm { background: rgba(245, 158, 11, 0.12); color: #b45309; border: 1px solid rgba(245, 158, 11, 0.25); }
        .dash-body.dark .lk-kat-inc { background: rgba(239, 68, 68, 0.18); color: #fca5a5; border-color: rgba(248, 113, 113, 0.35); }
        .dash-body.dark .lk-kat-nm { background: rgba(245, 158, 11, 0.18); color: #fcd34d; border-color: rgba(251, 191, 36, 0.35); }
    </style>
</head>
<body class="dash-body">
    @include('partials.premium-dash-bg', ['premiumBgId' => 'admin_laporan_kejadian'])

    @include('admin.partials.dash-admin-nav', [
        'pageTitle' => 'Laporan Kejadian',
        'pageSubtitle' => 'Arsip dari halaman publik — unduh PDF',
        'navChipLabel' => 'SUPERADMIN',
        'navChipClass' => 'dash-chip-admin',
    ])

    <div class="admin-shell" style="position:relative;z-index:1">
        <div class="portal-wrapper">

            <div class="portal-stats-row">
                <div class="portal-stat-card" style="--accent:#002a7a">
                    <div class="portal-stat-icon" style="background:rgba(0,42,122,.1);color:#002a7a">
                        <i class="bi bi-clipboard-data"></i>
                    </div>
                    <div>
                        <div class="portal-stat-value">{{ $stats['total'] }}</div>
                        <div class="portal-stat-label">Total Laporan</div>
                    </div>
                </div>
                <div class="portal-stat-card" style="--accent:#dc2626">
                    <div class="portal-stat-icon" style="background:rgba(220,38,38,.1);color:#dc2626">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                    <div>
                        <div class="portal-stat-value" style="color:#b91c1c">{{ $stats['incident'] }}</div>
                        <div class="portal-stat-label">Incident</div>
                    </div>
                </div>
                <div class="portal-stat-card" style="--accent:#d97706">
                    <div class="portal-stat-icon" style="background:rgba(217,119,6,.1);color:#d97706">
                        <i class="bi bi-shield-exclamation"></i>
                    </div>
                    <div>
                        <div class="portal-stat-value" style="color:#b45309">{{ $stats['nearmiss'] }}</div>
                        <div class="portal-stat-label">Near Miss</div>
                    </div>
                </div>
            </div>

            <div class="portal-section" style="margin-top: 4px">
                <div class="portal-section-header">
                    <div class="portal-section-title">
                        <i class="bi bi-table"></i> Daftar Laporan Kejadian
                    </div>
                </div>

                <div class="admin-table-wrap" style="margin-top: 16px">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th style="width:52px">#</th>
                                <th>Pelapor</th>
                                <th>Waktu</th>
                                <th>Kategori</th>
                                <th>Lokasi</th>
                                <th>Kendaraan</th>
                                <th style="width:1%; white-space:nowrap">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($laporans as $row)
                                <tr>
                                    <td>{{ $laporans->firstItem() + $loop->index }}</td>
                                    <td>
                                        <span class="lk-admin-name">{{ $row->nama }}</span>
                                        <div class="lk-admin-meta">NIP {{ $row->nip }}</div>
                                    </td>
                                    <td class="lk-admin-waktu">
                                        {{ $row->waktu_kejadian?->timezone(config('app.timezone'))->format('d/m/Y H:i') }}
                                    </td>
                                    <td>
                                        @if($row->kategori === 'Incident')
                                            <span class="lk-kat lk-kat-inc">Incident</span>
                                        @else
                                            <span class="lk-kat lk-kat-nm">Near Miss</span>
                                        @endif
                                    </td>
                                    <td class="lk-admin-lokasi">{{ \Illuminate\Support\Str::limit($row->lokasi_kejadian, 52) }}</td>
                                    <td>
                                        <span class="mgmt-nopol">{{ $row->nomor_kendaraan }}</span>
                                        <div class="lk-admin-meta">{{ $row->jenis_kendaraan }}</div>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.laporan-kejadian.pdf', $row) }}" class="peminj-pdf" target="_blank" rel="noopener">
                                            <i class="bi bi-file-earmark-pdf-fill"></i> Unduh
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="portal-empty">Belum ada laporan kejadian.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($laporans->hasPages())
                    <div class="sppd-pagination-scroll">
                        <div class="admin-pagination portal-pagination-wrap sppd-pagination--unified">
                            {{ $laporans->links() }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
    (function () {
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
        };
        const savedTheme = localStorage.getItem('vms-theme') || localStorage.getItem('vms-dash-theme');
        applyTheme(savedTheme === 'dark');
        themeBtn?.addEventListener('click', () => {
            const next = !body.classList.contains('dark');
            applyTheme(next);
            localStorage.setItem('vms-theme', next ? 'dark' : 'light');
            localStorage.setItem('vms-dash-theme', next ? 'dark' : 'light');
        });

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
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeMobileMenu();
        });
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 992) closeMobileMenu();
        });
    })();
    </script>
</body>
</html>
