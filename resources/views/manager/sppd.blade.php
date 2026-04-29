@php use App\Models\Sppd; @endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Rekap SPPD (Manager) — {{ config('app.name') }}</title>
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
            <path d="M0 300 C 300 250, 400 350, 700 200 C 1000 50, 1200 150, 1440 50 L 1440 400 L 0 400 Z" fill="url(#msppd_fill)"></path>
            <path d="M0 300 C 300 250, 400 350, 700 200 C 1000 50, 1200 150, 1440 50" stroke="url(#msppd_stroke)" stroke-width="3" stroke-linecap="round"></path>
            <defs>
                <linearGradient id="msppd_fill" x1="720" y1="50" x2="720" y2="400" gradientUnits="userSpaceOnUse">
                    <stop stop-color="#D4AF37" stop-opacity="0.12"></stop>
                    <stop offset="1" stop-color="#0A2342" stop-opacity="0"></stop>
                </linearGradient>
                <linearGradient id="msppd_stroke" x1="0" y1="150" x2="1440" y2="150" gradientUnits="userSpaceOnUse">
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
                    <div class="dash-nav-title">Rekap SPPD — Manager</div>
                    <span class="dash-nav-sub">PT ARTHA DAYA COALINDO</span>
                </div>
            </div>
            <div class="dash-nav-actions" id="dash-nav-actions">
                <button class="dash-theme-btn" id="dash-theme-toggle" title="Ganti Tema" aria-label="Toggle Tema">
                    <i class="bi bi-moon-fill" id="dash-theme-icon"></i>
                    <span class="dash-theme-mode-label" id="dash-theme-label">Dark Mode</span>
                </button>
                <span class="dash-chip dash-chip-manager">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" stroke="currentColor" stroke-width="2"/><circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="2"/></svg>
                    <span class="dash-nav-chip-label">MANAGER</span>
                </span>
                <a href="{{ route('dashboard') }}" class="dash-nav-btn-glass"><span class="dash-nav-btn-label">Dashboard</span></a>
            </div>
            <button class="dash-mobile-menu-btn" id="dash-mobile-menu-btn" aria-label="Menu"><i class="bi bi-list" id="dash-mobile-menu-icon"></i></button>
        </div>
    </nav>

    <div class="admin-shell" style="position:relative;z-index:1">
        <div class="portal-wrapper">
            <div class="portal-section-header" style="margin-bottom:12px">
                <div class="portal-section-title">Menunggu Persetujuan</div>
            </div>
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead><tr><th>Driver</th><th>Ringkasan</th><th>Kendaraan</th><th>Aksi</th></tr></thead>
                    <tbody>
                        @forelse($pending as $s)
                            <tr>
                                <td>{{ $s->nama_driver }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($s->keperluan_dinas, 42) }}<br><span class="sppd-cell-muted">{{ $s->tanggal_dinas->format('d/m/Y') }}</span></td>
                                <td><strong>{{ $s->no_kendaraan }}</strong><br><span class="sppd-cell-muted">{{ $s->jenis_kendaraan }}</span></td>
                                <td>
                                    <div class="sppd-aksi-btns">
                                        <button type="button" class="btn btn-sm sppd-icon-btn sppd-btn-primary mgr-sppd-detail" data-id="{{ $s->id }}" title="Detail Laporan" aria-label="Detail Laporan"><i class="bi bi-eye-fill"></i></button>
                                        <button type="button" class="btn btn-sm sppd-icon-btn sppd-btn-success mgr-sppd-approve" data-id="{{ $s->id }}" title="Setujui Laporan" aria-label="Setujui Laporan"><i class="bi bi-check-lg"></i></button>
                                        <button type="button" class="btn btn-sm sppd-icon-btn sppd-btn-danger mgr-sppd-reject" data-id="{{ $s->id }}" title="Tolak Laporan" aria-label="Tolak Laporan"><i class="bi bi-x-lg"></i></button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="portal-empty">Tidak ada antrian.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="admin-pagination mt-4">{{ $pending->links() }}</div>

            <div class="portal-section-header" style="margin:28px 0 12px">
                <div class="portal-section-title">Riwayat</div>
            </div>
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead><tr><th>Driver</th><th>Ringkasan</th><th>Status</th><th>Tanggal</th><th>Aksi</th></tr></thead>
                    <tbody>
                        @forelse($history as $s)
                            @php
                                $sppdNeedsPdf = in_array($s->status, [Sppd::STATUS_APPROVED, Sppd::STATUS_COMPLETED], true)
                                    && ! ($s->pdf_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($s->pdf_path));
                            @endphp
                            <tr>
                                <td>{{ $s->nama_driver }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($s->keperluan_dinas, 36) }}</td>
                                <td><x-sppd-status-badge :status="$s->status" /></td>
                                <td class="sppd-cell-muted">{{ $s->approved_at?->format('d/m/Y H:i') ?? $s->rejected_at?->format('d/m/Y H:i') ?? $s->updated_at->format('d/m/Y') }}</td>
                                <td>
                                    @if($sppdNeedsPdf)
                                        <button type="button" class="btn btn-sm sppd-icon-btn sppd-btn-primary mgr-sppd-regen-pdf" data-id="{{ $s->id }}" title="Buat PDF (belum tersedia)" aria-label="Buat PDF"><i class="bi bi-file-earmark-pdf"></i></button>
                                    @else
                                        <span class="sppd-cell-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="portal-empty">Belum ada riwayat.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="admin-pagination mt-4">{{ $history->links() }}</div>
        </div>
    </div>

    <div id="sppd-modal-detail-manager" class="modal-overlay" style="display:none">
        <div class="modal-box profile-card sppd-modal-box" style="max-width:min(720px,100%);text-align:left;max-height:86vh;overflow:auto">
            <h3>Detail Rekap SPPD</h3>
            <div id="sppd-manager-detail-body" class="sppd-detail-html"></div>
            <div class="ppm-modal-actions">
                <button type="button" class="ppm-btn-ghost" data-close-manager-sppd-modal>Tutup</button>
            </div>
        </div>
    </div>

    <script>
    (function () {
        const BASE = @json(url('/'));
        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        const detail = (id) => BASE + '/manager/rekap-sppd/' + id;
        const approve = (id) => BASE + '/manager/rekap-sppd/' + id + '/approve';
        const reject = (id) => BASE + '/manager/rekap-sppd/' + id + '/reject';
        const regenPdf = (id) => BASE + '/manager/rekap-sppd/' + id + '/regenerate-pdf';

        function formatRp(n) { return 'Rp ' + (Number(n) || 0).toLocaleString('id-ID'); }
        function esc(s) {
            const d = document.createElement('div');
            d.textContent = s ?? '';
            return d.innerHTML;
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
            let tollRows = (d.tolls || []).map(t => `<tr><td>${esc(t.leg_label || '—')}</td><td>${esc(t.dari_tol)}</td><td>${esc(t.ke_tol)}</td><td>${formatRp(t.harga)}</td></tr>`).join('');
            if (!tollRows) tollRows = '<tr><td colspan="4" class="portal-empty" style="padding:8px">—</td></tr>';
            let fuelRows = (d.fuels || []).map(f => `<tr><td>${esc(f.liter)}</td><td>${formatRp(f.harga_per_liter)}</td><td>${formatRp(f.total)}</td></tr>`).join('');
            if (!fuelRows) fuelRows = '<tr><td colspan="3" class="portal-empty" style="padding:8px">—</td></tr>';
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
                <div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Arah</th><th>Dari</th><th>Ke</th><th>Harga</th></tr></thead><tbody>${tollRows}</tbody></table></div>
                <p class="sppd-detail-sub">BBM</p>
                <div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Liter</th><th>Harga/L</th><th>Total</th></tr></thead><tbody>${fuelRows}</tbody></table></div>
                <p><strong>Total Tol:</strong> ${formatRp(d.total_tol)} &nbsp;|&nbsp; <strong>Total BBM:</strong> ${formatRp(d.total_bbm)} &nbsp;|&nbsp; <strong>Grand Total:</strong> ${formatRp(d.grand_total)}</p>
                ${d.revision_note ? `<p class="sppd-detail-sub">Catatan revisi</p><div class="sppd-revisi-inline">${esc(d.revision_note)}</div>` : ''}
                ${d.rejection_note ? `<p class="sppd-detail-sub">Alasan penolakan</p><div class="sppd-revisi-inline">${esc(d.rejection_note)}</div>` : ''}
            `;
        }

        document.querySelectorAll('.mgr-sppd-detail').forEach(btn => {
            btn.addEventListener('click', async () => {
                const r = await fetch(detail(btn.dataset.id), { headers: { Accept: 'application/json' } });
                const j = await r.json();
                const d = j.sppd;
                const modal = document.getElementById('sppd-modal-detail-manager');
                const body = document.getElementById('sppd-manager-detail-body');
                body.innerHTML = renderDetail(d);
                modal.style.display = 'flex';
            });
        });
        document.querySelectorAll('[data-close-manager-sppd-modal]').forEach(el => {
            el.addEventListener('click', () => { document.getElementById('sppd-modal-detail-manager').style.display = 'none'; });
        });
        document.getElementById('sppd-modal-detail-manager')?.addEventListener('click', (e) => {
            if (e.target.id === 'sppd-modal-detail-manager') e.currentTarget.style.display = 'none';
        });
        document.querySelectorAll('.mgr-sppd-approve').forEach(btn => {
            btn.addEventListener('click', async () => {
                const c = await Swal.fire({ title: 'Setujui rekap ini?', icon: 'question', showCancelButton: true, confirmButtonText: 'Setujui' });
                if (!c.isConfirmed) return;
                const r = await fetch(approve(btn.dataset.id), { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' } });
                const j = await r.json();
                if (j.success) { await Swal.fire('OK', j.message, 'success'); location.reload(); }
                else Swal.fire('Gagal', j.message || '', 'error');
            });
        });
        document.querySelectorAll('.mgr-sppd-regen-pdf').forEach(btn => {
            btn.addEventListener('click', async () => {
                const c = await Swal.fire({
                    title: 'Buat file PDF?',
                    text: 'Diperlukan jika persetujuan sebelumnya gagal menyimpan PDF (misalnya gd belum aktif).',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Buat PDF',
                });
                if (!c.isConfirmed) return;
                const r = await fetch(regenPdf(btn.dataset.id), { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' } });
                const j = await r.json().catch(() => ({}));
                if (j.success) { await Swal.fire('OK', j.message, 'success'); location.reload(); }
                else Swal.fire('Gagal', j.message || 'Tidak dapat membuat PDF', 'error');
            });
        });
        document.querySelectorAll('.mgr-sppd-reject').forEach(btn => {
            btn.addEventListener('click', async () => {
                const { value: note } = await Swal.fire({
                    title: 'Alasan penolakan',
                    input: 'textarea',
                    showCancelButton: true,
                    confirmButtonText: 'Tolak',
                    inputValidator: (v) => !v && 'Wajib diisi',
                });
                if (!note) return;
                const r = await fetch(reject(btn.dataset.id), {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json', 'Content-Type': 'application/json' },
                    body: JSON.stringify({ rejection_note: note }),
                });
                const j = await r.json();
                if (j.success) { await Swal.fire('OK', j.message, 'success'); location.reload(); }
                else Swal.fire('Gagal', j.message || '', 'error');
            });
        });

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
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 992) closeMobileMenu();
        });
    })();
    </script>
</body>
</html>
