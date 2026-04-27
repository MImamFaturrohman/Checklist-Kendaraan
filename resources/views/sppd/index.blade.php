@php use App\Support\SppdStatus; @endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Rekap SPPD — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="dash-body">
    <div class="dash-bg-cubes" aria-hidden="true"></div>
    <div class="dash-bg-stardust" aria-hidden="true"></div>
    <div class="dash-bg-orb-gold" aria-hidden="true"></div>
    <div class="dash-bg-orb-blue" aria-hidden="true"></div>
    <div class="dash-bg-wave" aria-hidden="true">
        <svg viewBox="0 0 1440 400" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:100%" preserveAspectRatio="none">
            <path d="M0 300 C 300 250, 400 350, 700 200 C 1000 50, 1200 150, 1440 50 L 1440 400 L 0 400 Z" fill="url(#sppd_fill)"></path>
            <path d="M0 300 C 300 250, 400 350, 700 200 C 1000 50, 1200 150, 1440 50" stroke="url(#sppd_stroke)" stroke-width="3" stroke-linecap="round"></path>
            <defs>
                <linearGradient id="sppd_fill" x1="720" y1="50" x2="720" y2="400" gradientUnits="userSpaceOnUse">
                    <stop stop-color="#D4AF37" stop-opacity="0.12"></stop>
                    <stop offset="1" stop-color="#0A2342" stop-opacity="0"></stop>
                </linearGradient>
                <linearGradient id="sppd_stroke" x1="0" y1="150" x2="1440" y2="150" gradientUnits="userSpaceOnUse">
                    <stop stop-color="#0A2342"></stop>
                    <stop offset="0.4" stop-color="#D4AF37"></stop>
                    <stop offset="1" stop-color="#60A5FA"></stop>
                </linearGradient>
            </defs>
        </svg>
    </div>

    <nav class="dash-nav" id="dash-nav">
        <div class="dash-nav-inner">
            <div class="dash-nav-brand">
                <img src="{{ asset('images/VMS.png') }}" alt="VMS" class="dash-nav-logo">
                <div>
                    <div class="dash-nav-title">Rekap SPPD</div>
                    <span class="dash-nav-sub">PT ARTHA DAYA COALINDO</span>
                </div>
            </div>
            <div class="dash-nav-actions" id="dash-nav-actions">
                <button class="dash-theme-btn" id="dash-theme-toggle" title="Ganti Tema" aria-label="Toggle Tema">
                    <i class="bi bi-moon-fill" id="dash-theme-icon"></i>
                    <span class="dash-theme-mode-label" id="dash-theme-label">Dark Mode</span>
                </button>
                <span class="dash-chip dash-chip-driver">
                    <i class="bi bi-person-check-fill"></i>
                    <span class="dash-nav-chip-label">DRIVER</span>
                </span>
                <a href="{{ route('dashboard') }}" class="dash-nav-btn-glass" aria-label="Dashboard">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M19 12H5M12 19l-7-7 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span class="dash-nav-btn-label">Dashboard</span>
                </a>
            </div>
            <button class="dash-mobile-menu-btn" id="dash-mobile-menu-btn" aria-label="Buka Menu" aria-expanded="false">
                <i class="bi bi-list" id="dash-mobile-menu-icon"></i>
            </button>
        </div>
    </nav>

    <div class="admin-shell" style="position:relative;z-index:1">
        <div class="portal-wrapper">
            <div class="portal-section" id="section-sppd-list">
                <div class="portal-section-header">
                    <div class="portal-section-title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2" stroke="currentColor" stroke-width="2"/><rect x="9" y="3" width="6" height="4" rx="1" stroke="currentColor" stroke-width="2"/><path d="M9 12h6M9 16h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        Daftar Rekap SPPD
                    </div>
                    <a href="{{ route('sppd.create') }}" class="btn-export" style="text-decoration:none;display:inline-flex;align-items:center;gap:6px">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>
                        Buat Rekap SPPD
                    </a>
                </div>

                @if($sppds->count() > 0)
                <div class="portal-local-filters sppd-live-filter-bar" id="sppd-live-filter-bar">
                    <div class="admin-search-wrap portal-search-full">
                        <svg class="admin-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/><path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        <input type="search" id="sppd-live-search" class="admin-search-input" placeholder="Cari keperluan, kendaraan, tanggal…" autocomplete="off" aria-label="Cari daftar SPPD">
                    </div>
                    <div class="ppm-status-wrap">
                        <label class="sr-only" for="sppd-live-status">Filter status</label>
                        <select id="sppd-live-status" class="admin-filter-input" aria-label="Filter status">
                            <option value="">Semua status</option>
                            @foreach(SppdStatus::adminFilterOptions() as $st)
                                <option value="{{ $st }}">{{ SppdStatus::label($st) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @endif

                <div class="admin-table-wrap sppd-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Ringkasan</th>
                                <th>Kendaraan</th>
                                <th>Status</th>
                                <th class="sppd-th-aksi">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="sppd-table-body">
                            @forelse($sppds as $s)
                                @php
                                    $haystack = strtolower($s->keperluan_dinas.' '.$s->no_kendaraan.' '.$s->jenis_kendaraan.' '.$s->tanggal_dinas->format('d/m/Y').' '.SppdStatus::label($s->status));
                                @endphp
                                <tr
                                    data-sppd-row
                                    data-sppd-status="{{ $s->status }}"
                                    data-sppd-haystack="{{ e($haystack) }}"
                                >
                                    <td data-label="Ringkasan"><span class="sppd-cell-title">{{ \Illuminate\Support\Str::limit($s->keperluan_dinas, 48) }}</span><br><span class="sppd-cell-muted">{{ $s->tanggal_dinas->format('d/m/Y') }}</span></td>
                                    <td data-label="Kendaraan"><strong>{{ $s->no_kendaraan }}</strong><br><span class="sppd-cell-muted">{{ $s->jenis_kendaraan }}</span></td>
                                    <td data-label="Status"><x-sppd-status-badge :status="$s->status" /></td>
                                    <td data-label="Aksi" class="sppd-aksi">
                                        <div class="sppd-aksi-btns">
                                            <button
                                                type="button"
                                                class="btn btn-sm sppd-icon-btn sppd-btn-primary sppd-btn-detail"
                                                data-detail-id="{{ $s->id }}"
                                                title="Detail Laporan"
                                                aria-label="Detail Laporan"
                                            ><i class="bi bi-eye-fill"></i></button>
                                            @if($s->status === \App\Models\Sppd::STATUS_REVISION)
                                                <button
                                                    type="button"
                                                    class="btn btn-sm sppd-icon-btn sppd-btn-warning sppd-btn-revisi"
                                                    data-revisi-note="{{ e((string) ($s->revision_note ?? '')) }}"
                                                    data-revisi-at="{{ $s->revision_at?->format('d/m/Y H:i') ?? '' }}"
                                                    data-revisi-edit="{{ route('sppd.edit', $s) }}"
                                                    title="Lihat Revisi"
                                                    aria-label="Lihat Revisi"
                                                ><i class="bi bi-chat-left-text-fill"></i></button>
                                                <a
                                                    href="{{ route('sppd.edit', $s) }}"
                                                    class="btn btn-sm sppd-icon-btn sppd-btn-success"
                                                    title="Edit Laporan"
                                                    aria-label="Edit Laporan"
                                                ><i class="bi bi-pencil-fill"></i></a>
                                                <form action="{{ route('sppd.destroy', $s) }}" method="post" class="sppd-inline-form sppd-delete-form">
                                                    @csrf @method('DELETE')
                                                    <button
                                                        type="submit"
                                                        class="btn btn-sm sppd-icon-btn sppd-btn-danger sppd-delete-submit"
                                                        title="Hapus Laporan"
                                                        aria-label="Hapus Laporan"
                                                    ><i class="bi bi-trash-fill"></i></button>
                                                </form>
                                            @endif
                                            @if(in_array($s->status, [\App\Models\Sppd::STATUS_APPROVED, \App\Models\Sppd::STATUS_COMPLETED], true) && $s->pdf_path)
                                                <a
                                                    href="{{ route('sppd.pdf', $s) }}"
                                                    class="btn btn-sm sppd-icon-btn sppd-btn-secondary-lite"
                                                    target="_blank"
                                                    rel="noopener"
                                                    title="Cetak PDF"
                                                    aria-label="Cetak PDF"
                                                ><i class="bi bi-file-earmark-pdf-fill"></i></a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="portal-empty">Belum ada rekap. Klik <strong>Buat Rekap SPPD</strong> untuk mulai.</td></tr>
                            @endforelse
                            @if($sppds->count() > 0)
                                <tr id="sppd-filter-no-match" class="sppd-filter-no-match" style="display:none">
                                    <td colspan="4" class="portal-empty">Tidak ada baris yang cocok dengan pencarian atau filter.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                <div class="admin-pagination mt-4 portal-pagination-wrap">{{ $sppds->links() }}</div>
            </div>
        </div>
    </div>

    {{-- Detail modal --}}
    <div id="sppd-modal-detail" class="modal-overlay" style="display:none">
        <div class="modal-box profile-card sppd-modal-box" style="max-width:min(720px,100%);text-align:left;max-height:86vh;overflow:auto">
            <h3>Detail Rekap SPPD</h3>
            <div id="sppd-detail-body" class="sppd-detail-html"></div>
            <div class="ppm-modal-actions">
                <button type="button" class="btn btn-sm sppd-icon-btn sppd-btn-secondary-lite" data-close-sppd-modal title="Tutup" aria-label="Tutup"><i class="bi bi-x-lg"></i></button>
                <a href="#" id="sppd-detail-edit" class="btn btn-sm sppd-icon-btn sppd-btn-success" style="display:none" title="Edit Laporan" aria-label="Edit Laporan"><i class="bi bi-pencil-fill"></i></a>
                <form id="sppd-form-selesai" method="post" style="display:none">
                    @csrf
                    <button type="submit" class="btn-export">Tandai Selesai</button>
                </form>
            </div>
        </div>
    </div>

    {{-- Revisi modal --}}
    <div id="sppd-modal-revisi" class="modal-overlay" style="display:none">
        <div class="modal-box profile-card sppd-modal-box" style="max-width:min(520px,100%);text-align:left">
            <h3>Revisi dari Admin</h3>
            <p class="sppd-revisi-date" id="sppd-revisi-date"></p>
            <div class="sppd-revisi-note" id="sppd-revisi-note"></div>
            <div class="ppm-modal-actions">
                <button type="button" class="btn btn-sm sppd-icon-btn sppd-btn-secondary-lite" data-close-revisi-modal title="Tutup" aria-label="Tutup"><i class="bi bi-x-lg"></i></button>
                <a href="#" id="sppd-revisi-edit" class="btn btn-sm sppd-icon-btn sppd-btn-success" title="Edit Laporan" aria-label="Edit Laporan"><i class="bi bi-pencil-fill"></i></a>
            </div>
        </div>
    </div>

    <script>
    (function () {
        const BASE = @json(url('/'));
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const flashOk = @json(session('ok'));
        if (flashOk && typeof Swal !== 'undefined') {
            queueMicrotask(() => Swal.fire({ icon: 'success', title: 'Berhasil', text: flashOk }));
        }

        document.querySelectorAll('.sppd-delete-form').forEach((form) => {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                const c = await Swal.fire({
                    title: 'Hapus laporan ini?',
                    text: 'Data tidak dapat dikembalikan.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus',
                    cancelButtonText: 'Batal',
                    reverseButtons: true,
                });
                if (!c.isConfirmed) return;
                const btn = form.querySelector('.sppd-delete-submit');
                if (btn) btn.disabled = true;
                try {
                    const r = await fetch(form.action, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });
                    const ct = r.headers.get('content-type') || '';
                    if (r.ok && ct.includes('application/json')) {
                        const j = await r.json();
                        if (j.success) {
                            await Swal.fire({ icon: 'success', title: 'Berhasil', text: j.message || 'Rekap dihapus.' });
                            window.location.reload();
                            return;
                        }
                    }
                    await Swal.fire({ icon: 'error', title: 'Gagal', text: 'Tidak dapat menghapus laporan.' });
                } catch (err) {
                    await Swal.fire({ icon: 'error', title: 'Gagal', text: 'Terjadi kesalahan jaringan.' });
                } finally {
                    if (btn) btn.disabled = false;
                }
            });
        });

        const sppdSearchEl = document.getElementById('sppd-live-search');
        const sppdStatusEl = document.getElementById('sppd-live-status');
        const sppdNoMatchRow = document.getElementById('sppd-filter-no-match');
        function applySppdLiveFilter() {
            if (!sppdSearchEl && !sppdStatusEl) return;
            const q = (sppdSearchEl?.value || '').trim().toLowerCase();
            const st = sppdStatusEl?.value || '';
            let visible = 0;
            document.querySelectorAll('#sppd-table-body tr[data-sppd-row]').forEach((tr) => {
                const hay = tr.getAttribute('data-sppd-haystack') || '';
                const rowSt = tr.getAttribute('data-sppd-status') || '';
                const okQ = !q || hay.includes(q);
                const okS = !st || rowSt === st;
                const show = okQ && okS;
                tr.style.display = show ? '' : 'none';
                if (show) visible++;
            });
            if (sppdNoMatchRow) sppdNoMatchRow.style.display = visible === 0 ? '' : 'none';
        }
        sppdSearchEl?.addEventListener('input', applySppdLiveFilter);
        sppdStatusEl?.addEventListener('change', applySppdLiveFilter);

        function esc(s) {
            const d = document.createElement('div');
            d.textContent = s ?? '';
            return d.innerHTML;
        }

        function formatRp(n) {
            const x = Number(n) || 0;
            return 'Rp ' + x.toLocaleString('id-ID');
        }

        function normalizeUrl(u) {
            if (!u) return '';
            const raw = String(u);
            if (/^data:image/i.test(raw)) return raw;
            if (/^https?:\/\//i.test(raw)) return raw;
            if (raw.startsWith('/')) return BASE + raw;
            return BASE + '/' + raw.replace(/^\/+/, '');
        }

        function renderDetail(d) {
            let tollRows = (d.tolls || []).map(t => `<tr><td>${esc(t.dari_tol)}</td><td>${esc(t.ke_tol)}</td><td>${formatRp(t.harga)}</td></tr>`).join('');
            if (!tollRows) tollRows = '<tr><td colspan="3" class="portal-empty" style="padding:8px">—</td></tr>';
            let fuelRows = (d.fuels || []).map(f => `<tr><td>${esc(f.liter)}</td><td>${formatRp(f.harga_per_liter)}</td><td>${formatRp(f.total)}</td></tr>`).join('');
            if (!fuelRows) fuelRows = '<tr><td colspan="3" class="portal-empty" style="padding:8px">—</td></tr>';
            const fuelPhotos = (d.fuels || []).map((f, i) => {
                const odoUrl = normalizeUrl(f.odometer_url);
                const strukUrl = normalizeUrl(f.struk_url);
                const odo = f.odometer_url
                    ? `<a href="${String(odoUrl).replace(/"/g, '&quot;')}" target="_blank" rel="noopener"><img src="${String(odoUrl).replace(/"/g, '&quot;')}" class="sppd-photo-thumb" alt="Odometer ${i + 1}"></a>`
                    : '';
                const struk = f.struk_url
                    ? `<a href="${String(strukUrl).replace(/"/g, '&quot;')}" target="_blank" rel="noopener"><img src="${String(strukUrl).replace(/"/g, '&quot;')}" class="sppd-photo-thumb" alt="Struk ${i + 1}"></a>`
                    : '';
                if (!odo && !struk) return '';
                return `<div class="sppd-photo-group"><p class="sppd-photo-group-title">Baris BBM ${i + 1}</p><div class="sppd-photo-grid">${odo}${struk}</div></div>`;
            }).join('');
            return `
                <table class="info-table sppd-mini-table">
                    <tr><td class="label">Driver</td><td>${esc(d.nama_driver)}</td></tr>
                    <tr><td class="label">Keperluan</td><td>${esc(d.keperluan_dinas)}</td></tr>
                    <tr><td class="label">Kendaraan</td><td>${esc(d.no_kendaraan)} — ${esc(d.jenis_kendaraan)}</td></tr>
                    <tr><td class="label">Tanggal</td><td>${esc(d.tanggal_dinas)}</td></tr>
                    <tr><td class="label">Tujuan</td><td>${esc(d.tujuan)}</td></tr>
                    <tr><td class="label">Status</td><td>${esc(d.status_label)}</td></tr>
                </table>
                <p class="sppd-detail-sub">Biaya Tol</p>
                <div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Dari</th><th>Ke</th><th>Harga</th></tr></thead><tbody>${tollRows}</tbody></table></div>
                <p class="sppd-detail-sub">BBM</p>
                <div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Liter</th><th>Harga/L</th><th>Total</th></tr></thead><tbody>${fuelRows}</tbody></table></div>
                ${fuelPhotos ? `<p class="sppd-detail-sub">Foto Odometer & Struk</p>${fuelPhotos}` : ''}
                <p><strong>Total Tol:</strong> ${formatRp(d.total_tol)} &nbsp;|&nbsp; <strong>Total BBM:</strong> ${formatRp(d.total_bbm)} &nbsp;|&nbsp; <strong>Grand Total:</strong> ${formatRp(d.grand_total)}</p>
                ${d.signature_url ? `<p class="sppd-detail-sub">Tanda tangan</p><img src="${String(normalizeUrl(d.signature_url)).replace(/"/g,'&quot;')}" alt="TTD" class="sppd-sig-preview">` : ''}
                ${d.revision_note ? `<p class="sppd-detail-sub sppd-detail-sub--spaced">Catatan revisi</p><div class="sppd-revisi-inline">${esc(d.revision_note)}</div>` : ''}
                ${d.rejection_note ? `<p class="sppd-detail-sub sppd-detail-sub--spaced">Alasan penolakan</p><div class="sppd-revisi-inline">${esc(d.rejection_note)}</div>` : ''}
            `;
        }

        document.querySelectorAll('.sppd-btn-detail').forEach(btn => {
            btn.addEventListener('click', async () => {
                const id = btn.getAttribute('data-detail-id');
                const modal = document.getElementById('sppd-modal-detail');
                const body = document.getElementById('sppd-detail-body');
                const formSelesai = document.getElementById('sppd-form-selesai');
                const editBtn = document.getElementById('sppd-detail-edit');
                body.innerHTML = '<p>Memuat…</p>';
                modal.style.display = 'flex';
                formSelesai.style.display = 'none';
                editBtn.style.display = 'none';
                try {
                    const r = await fetch(BASE + '/sppd/' + id + '/json', { headers: { Accept: 'application/json' } });
                    const j = await r.json();
                    const d = j.sppd;
                    body.innerHTML = renderDetail(d);
                    if (d.status === 'revision') {
                        editBtn.href = BASE + '/sppd/' + id + '/edit';
                        editBtn.style.display = 'inline-flex';
                    }
                    if (d.status === 'approved') {
                        formSelesai.action = BASE + '/sppd/' + id + '/selesai';
                        formSelesai.style.display = 'block';
                    }
                } catch (e) {
                    body.innerHTML = '<p>Gagal memuat data.</p>';
                }
            });
        });

        document.querySelectorAll('[data-close-sppd-modal]').forEach(el => {
            el.addEventListener('click', () => { document.getElementById('sppd-modal-detail').style.display = 'none'; });
        });

        document.querySelectorAll('.sppd-btn-revisi').forEach(btn => {
            btn.addEventListener('click', () => {
                const note = btn.getAttribute('data-revisi-note') || '';
                const at = btn.getAttribute('data-revisi-at') || '';
                const edit = btn.getAttribute('data-revisi-edit') || '#';
                document.getElementById('sppd-revisi-note').textContent = note;
                document.getElementById('sppd-revisi-date').textContent = at ? ('Tanggal revisi: ' + at) : '';
                document.getElementById('sppd-revisi-edit').href = edit;
                document.getElementById('sppd-modal-revisi').style.display = 'flex';
            });
        });
        document.querySelectorAll('[data-close-revisi-modal]').forEach(el => {
            el.addEventListener('click', () => { document.getElementById('sppd-modal-revisi').style.display = 'none'; });
        });
        document.getElementById('sppd-modal-detail')?.addEventListener('click', (e) => {
            if (e.target.id === 'sppd-modal-detail') e.currentTarget.style.display = 'none';
        });
        document.getElementById('sppd-modal-revisi')?.addEventListener('click', (e) => {
            if (e.target.id === 'sppd-modal-revisi') e.currentTarget.style.display = 'none';
        });

        // Theme + mobile nav behaviour (same interaction as dashboard)
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
        const saved = localStorage.getItem('vms-theme') || localStorage.getItem('vms-dash-theme');
        applyTheme(saved === 'dark');
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
            const isOpen = navActions?.classList.toggle('mobile-open');
            if (menuIcon) menuIcon.className = isOpen ? 'bi bi-x-lg' : 'bi bi-list';
            menuBtn.setAttribute('aria-expanded', String(!!isOpen));
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
