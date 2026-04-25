@php use App\Models\Sppd; @endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Rekap SPPD (Admin) — {{ config('app.name') }}</title>
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
            <path d="M0 300 C 300 250, 400 350, 700 200 C 1000 50, 1200 150, 1440 50 L 1440 400 L 0 400 Z" fill="url(#asppd_fill)"></path>
            <path d="M0 300 C 300 250, 400 350, 700 200 C 1000 50, 1200 150, 1440 50" stroke="url(#asppd_stroke)" stroke-width="3" stroke-linecap="round"></path>
            <defs>
                <linearGradient id="asppd_fill" x1="720" y1="50" x2="720" y2="400" gradientUnits="userSpaceOnUse">
                    <stop stop-color="#D4AF37" stop-opacity="0.12"></stop>
                    <stop offset="1" stop-color="#0A2342" stop-opacity="0"></stop>
                </linearGradient>
                <linearGradient id="asppd_stroke" x1="0" y1="150" x2="1440" y2="150" gradientUnits="userSpaceOnUse">
                    <stop stop-color="#0A2342"></stop>
                    <stop offset="0.4" stop-color="#D4AF37"></stop>
                    <stop offset="1" stop-color="#60A5FA"></stop>
                </linearGradient>
            </defs>
        </svg>
    </div>

    @include('admin.partials.dash-admin-nav', ['pageTitle' => 'Rekap SPPD', 'pageSubtitle' => 'Verifikasi laporan driver'])

    <div class="admin-shell" style="position:relative;z-index:1">
        <div class="portal-wrapper">
            <div class="portal-stats-row">
                <div class="portal-stat-card" style="--accent:#002a7a"><div class="portal-stat-icon" style="background:rgba(0,42,122,.1);color:#002a7a"><i class="bi bi-files"></i></div><div><div class="portal-stat-value">{{ $counts['all'] }}</div><div class="portal-stat-label">Total</div></div></div>
                <div class="portal-stat-card" style="--accent:#d97706"><div class="portal-stat-icon" style="background:rgba(217,119,6,.1);color:#d97706"><i class="bi bi-hourglass-split"></i></div><div><div class="portal-stat-value">{{ $counts['pending'] }}</div><div class="portal-stat-label">Menunggu Verifikasi</div></div></div>
                <div class="portal-stat-card" style="--accent:#7c3aed"><div class="portal-stat-icon" style="background:rgba(124,58,237,.1);color:#7c3aed"><i class="bi bi-pencil-square"></i></div><div><div class="portal-stat-value">{{ $counts['revision'] }}</div><div class="portal-stat-label">Revisi</div></div></div>
                <div class="portal-stat-card" style="--accent:#0891b2"><div class="portal-stat-icon" style="background:rgba(8,145,178,.1);color:#0891b2"><i class="bi bi-person-check"></i></div><div><div class="portal-stat-value">{{ $counts['pending_manager'] }}</div><div class="portal-stat-label">Ke Manager</div></div></div>
            </div>

            <div class="portal-section" id="section-sppd-admin">
                <div class="portal-section-header">
                    <div class="portal-section-title"><i class="bi bi-table"></i> Daftar Rekap SPPD</div>
                </div>
                <form method="get" action="{{ route('admin.sppd.index') }}" class="portal-local-filters ppm-daftar-filters">
                    <div class="admin-search-wrap portal-search-full">
                        <svg class="admin-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/><path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        <input type="text" name="q" value="{{ $search }}" placeholder="Cari driver, keperluan, nopol…" class="admin-search-input">
                    </div>
                    <div class="ppm-status-wrap">
                        <select name="status" class="admin-filter-input" onchange="this.form.submit()">
                            <option value="">Semua status</option>
                            @foreach(\App\Support\SppdStatus::adminFilterOptions() as $st)
                                <option value="{{ $st }}" @selected($currentStatus === $st)>{{ \App\Support\SppdStatus::label($st) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="portal-local-reset ppm-filter-reset">Terapkan</button>
                </form>

                <div class="admin-table-wrap sppd-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Driver</th>
                                <th>Ringkasan</th>
                                <th>Kendaraan</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sppds as $s)
                                <tr>
                                    <td>{{ ($sppds->currentPage()-1)*$sppds->perPage()+$loop->iteration }}</td>
                                    <td>{{ $s->nama_driver }}<br><span class="sppd-cell-muted">{{ $s->user?->username }}</span></td>
                                    <td>{{ \Illuminate\Support\Str::limit($s->keperluan_dinas, 40) }}<br><span class="sppd-cell-muted">{{ $s->tanggal_dinas->format('d/m/Y') }}</span></td>
                                    <td><strong>{{ $s->no_kendaraan }}</strong><br><span class="sppd-cell-muted">{{ $s->jenis_kendaraan }}</span></td>
                                    <td><x-sppd-status-badge :status="$s->status" /></td>
                                    <td>
                                        <div class="sppd-aksi-btns">
                                            <button
                                                type="button"
                                                class="btn btn-sm sppd-icon-btn sppd-btn-primary admin-sppd-detail"
                                                data-id="{{ $s->id }}"
                                                title="Detail Laporan"
                                                aria-label="Detail Laporan"
                                            ><i class="bi bi-eye-fill"></i></button>
                                            @if($s->status === Sppd::STATUS_PENDING)
                                                <button
                                                    type="button"
                                                    class="btn btn-sm sppd-icon-btn sppd-btn-success admin-sppd-ok"
                                                    data-id="{{ $s->id }}"
                                                    title="Setujui Laporan"
                                                    aria-label="Setujui Laporan"
                                                ><i class="bi bi-check-lg"></i></button>
                                                <button
                                                    type="button"
                                                    class="btn btn-sm sppd-icon-btn sppd-btn-danger admin-sppd-reject"
                                                    data-id="{{ $s->id }}"
                                                    title="Tolak / Revisi"
                                                    aria-label="Tolak / Revisi"
                                                ><i class="bi bi-x-lg"></i></button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="portal-empty">Tidak ada data.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="admin-pagination mt-4">{{ $sppds->links() }}</div>
            </div>
        </div>
    </div>

    <div id="sppd-modal-detail-admin" class="modal-overlay" style="display:none">
        <div class="modal-box profile-card sppd-modal-box" style="max-width:min(720px,100%);text-align:left;max-height:86vh;overflow:auto">
            <h3>Detail Rekap SPPD</h3>
            <div id="sppd-admin-detail-body" class="sppd-detail-html"></div>
            <div class="ppm-modal-actions">
                <button type="button" class="ppm-btn-ghost" data-close-admin-sppd-modal>Tutup</button>
            </div>
        </div>
    </div>

    <script>
    (function () {
        const BASE = @json(url('/'));
        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        const detailUrl = (id) => BASE + '/admin/rekap-sppd/' + id;
        const approveUrl = (id) => BASE + '/admin/rekap-sppd/' + id + '/verify-approve';
        const rejectUrl = (id) => BASE + '/admin/rekap-sppd/' + id + '/verify-reject';

        function formatRp(n) {
            return 'Rp ' + (Number(n) || 0).toLocaleString('id-ID');
        }

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
                    <tr><td class="label">Driver</td><td>${esc(d.nama_driver)} (${esc(d.driver_username || '-')})</td></tr>
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
                ${d.revision_note ? `<p class="sppd-detail-sub">Catatan revisi</p><div class="sppd-revisi-inline">${esc(d.revision_note)}</div>` : ''}
                ${d.rejection_note ? `<p class="sppd-detail-sub">Alasan penolakan</p><div class="sppd-revisi-inline">${esc(d.rejection_note)}</div>` : ''}
            `;
        }

        async function showDetail(id) {
            const r = await fetch(detailUrl(id), { headers: { Accept: 'application/json' } });
            const j = await r.json();
            const d = j.sppd;
            const modal = document.getElementById('sppd-modal-detail-admin');
            const body = document.getElementById('sppd-admin-detail-body');
            body.innerHTML = renderDetail(d);
            modal.style.display = 'flex';
        }

        document.querySelectorAll('.admin-sppd-detail').forEach(btn => {
            btn.addEventListener('click', () => showDetail(btn.dataset.id));
        });
        document.querySelectorAll('[data-close-admin-sppd-modal]').forEach(el => {
            el.addEventListener('click', () => { document.getElementById('sppd-modal-detail-admin').style.display = 'none'; });
        });
        document.getElementById('sppd-modal-detail-admin')?.addEventListener('click', (e) => {
            if (e.target.id === 'sppd-modal-detail-admin') e.currentTarget.style.display = 'none';
        });

        document.querySelectorAll('.admin-sppd-ok').forEach(btn => {
            btn.addEventListener('click', async () => {
                const id = btn.dataset.id;
                const c = await Swal.fire({ title: 'Verifikasi?', text: 'Laporan akan diteruskan ke Manager.', icon: 'question', showCancelButton: true, confirmButtonText: 'Ya, setujui' });
                if (!c.isConfirmed) return;
                const r = await fetch(approveUrl(id), { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' } });
                const j = await r.json();
                if (j.success) { await Swal.fire('Berhasil', j.message, 'success'); location.reload(); }
                else Swal.fire('Gagal', j.message || 'Error', 'error');
            });
        });

        document.querySelectorAll('.admin-sppd-reject').forEach(btn => {
            btn.addEventListener('click', async () => {
                const id = btn.dataset.id;
                const { value: note } = await Swal.fire({
                    title: 'Alasan revisi',
                    input: 'textarea',
                    inputLabel: 'Pesan untuk driver',
                    inputPlaceholder: 'Jelaskan bagian yang perlu diperbaiki…',
                    showCancelButton: true,
                    confirmButtonText: 'Kirim revisi',
                    inputValidator: (v) => !v && 'Wajib diisi',
                });
                if (!note) return;
                const r = await fetch(rejectUrl(id), {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json', 'Content-Type': 'application/json' },
                    body: JSON.stringify({ revision_note: note }),
                });
                const j = await r.json();
                if (j.success) { await Swal.fire('Berhasil', j.message, 'success'); location.reload(); }
                else Swal.fire('Gagal', j.message || 'Error', 'error');
            });
        });
    })();
    </script>
</body>
</html>
