@php use App\Support\SppdStatus; @endphp
@extends('layouts.dash-app')

@section('title', 'TransDinas')
@section('pageTitle', 'TransDinas')

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

@section('content')
<div class="admin-shell" style="position:relative;z-index:1">
    <div class="portal-wrapper">
        <div class="portal-section" id="section-sppd-list">
            <div class="portal-section-header">
                <div class="portal-section-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2" stroke="currentColor" stroke-width="2"/><rect x="9" y="3" width="6" height="4" rx="1" stroke="currentColor" stroke-width="2"/><path d="M9 12h6M9 16h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    Daftar Rekap Biaya Dinas
                </div>
                <a href="{{ route('sppd.create') }}" class="btn-export sppd-btn-create-rekap" style="text-decoration:none;display:inline-flex;align-items:center;gap:6px">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>
                    Buat Rekap SPPD
                </a>
            </div>

            <div id="sppd-driver-live-root" data-vms-sppd-live>
            @fragment('sppd-driver-body')
            <form method="get" action="{{ route('sppd.index') }}" class="portal-local-filters sppd-live-filter-bar" id="sppd-driver-filter-form">
                <div class="admin-search-wrap portal-search-full">
                    <svg class="admin-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/><path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    <input type="search" name="q" id="sppd-live-search" value="{{ request('q') }}" class="admin-search-input" placeholder="Cari keperluan, kendaraan, tanggal…" autocomplete="off" aria-label="Cari daftar SPPD">
                </div>
                <div class="ppm-status-wrap">
                    <label class="sr-only" for="sppd-live-status">Filter status</label>
                    <select name="status" id="sppd-live-status" class="admin-filter-input" aria-label="Filter status">
                        <option value="">Semua status</option>
                        @foreach(SppdStatus::adminFilterOptions() as $st)
                            <option value="{{ $st }}" @selected(request('status') === $st)>{{ SppdStatus::label($st) }}</option>
                        @endforeach
                    </select>
                </div>
                <x-admin-per-page-select
                    id="sppd-per-page"
                    name="per_page"
                    :selected="(int) request('per_page', $sppds->perPage())"
                />
                <input type="hidden" name="sort" value="{{ $activeSort ?? '' }}">
                <input type="hidden" name="dir"  value="{{ $activeDir  ?? '' }}">
            </form>

            <div class="admin-table-wrap sppd-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <x-sortable-th key="keperluan_dinas" label="Ringkasan" :activeSort="$activeSort ?? null" :activeDir="$activeDir ?? null" />
                            <x-sortable-th key="no_kendaraan" label="Kendaraan" :activeSort="$activeSort ?? null" :activeDir="$activeDir ?? null" />
                            <x-sortable-th key="status" label="Status" :activeSort="$activeSort ?? null" :activeDir="$activeDir ?? null" />
                            <th class="sppd-th-aksi">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="sppd-table-body">
                        @forelse($sppds as $s)
                            <tr>
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
                                        ><i class="bi bi-info-circle"></i></button>
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
                                        @php
                                            $sppdPdfOk = $s->pdf_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($s->pdf_path);
                                        @endphp
                                        @if($sppdPdfOk && in_array($s->status, [\App\Models\Sppd::STATUS_APPROVED, \App\Models\Sppd::STATUS_COMPLETED], true))
                                            <a
                                                href="{{ route('sppd.pdf', $s) }}"
                                                class="btn btn-sm sppd-icon-btn sppd-btn-secondary-lite"
                                                target="_blank"
                                                rel="noopener"
                                                title="Unduh PDF"
                                                aria-label="Unduh PDF"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M12 3v12"></path>
                                                    <path d="M7 10l5 5 5-5"></path>
                                                    <path d="M5 21h14"></path>
                                                </svg>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="portal-empty">Belum ada rekap. Klik <strong>Buat Rekap SPPD</strong> untuk mulai.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-admin-pagination :paginator="$sppds" />
            @endfragment
            </div>
        </div>
    </div>
</div>
@endsection

@section('modals')
{{-- Detail modal --}}
<div id="sppd-modal-detail" class="modal-overlay" style="display:none">
    <div class="modal-box profile-card sppd-modal-box" style="max-width:min(720px,100%);text-align:left;max-height:86vh;overflow:auto">
        <h3>Detail Rekap SPPD</h3>
        <div id="sppd-detail-body" class="sppd-detail-html"></div>
        <div class="ppm-modal-actions">
            <div id="sppd-detail-pdf-wrap" class="sppd-detail-pdf-wrap" hidden></div>
            <button type="button" class="btn btn-sm sppd-icon-btn sppd-btn-secondary-lite" data-close-sppd-modal title="Tutup" aria-label="Tutup"><i class="bi bi-x-lg"></i></button>
            <a href="#" id="sppd-detail-edit" class="btn btn-sm sppd-icon-btn sppd-btn-success" style="display:none" title="Edit Laporan" aria-label="Edit Laporan"><i class="bi bi-pencil-fill"></i></a>
            <form id="sppd-form-selesai" method="post" class="sppd-inline-form" style="display:none">
                @csrf
                <button type="submit" class="btn btn-sm sppd-icon-btn sppd-btn-success" title="Tandai Selesai" aria-label="Tandai Selesai"><i class="bi bi-check-circle-fill"></i></button>
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
@endsection

@push('scripts')
<script>
(function () {
    const BASE = @json(url('/'));
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    function swalTheme(extra) {
        const isDark = document.body.classList.contains('dark');
        if (!isDark || typeof Swal === 'undefined') return extra || {};
        return {
            background: '#0f172a',
            color: '#e2e8f0',
            iconColor: '#38bdf8',
            confirmButtonColor: '#2563eb',
            cancelButtonColor: '#475569',
            ...(extra || {}),
        };
    }

    const flashOk = @json(session('ok'));
    if (flashOk && typeof Swal !== 'undefined') {
        queueMicrotask(() =>
            Swal.fire(swalTheme({ icon: 'success', title: 'Berhasil', text: flashOk }))
        );
    }

    document.addEventListener('submit', async (e) => {
        const form = e.target.closest('.sppd-delete-form');
        if (!form || !document.getElementById('sppd-driver-live-root')?.contains(form)) return;
        e.preventDefault();
        const c = await Swal.fire(swalTheme({
            title: 'Hapus laporan ini?',
            text: 'Data tidak dapat dikembalikan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal',
            reverseButtons: true,
        }));
        if (!c.isConfirmed) return;
        const btn = form.querySelector('.sppd-delete-submit');
        if (btn) btn.disabled = true;
        try {
            const r = await fetch(form.action, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const ct = r.headers.get('content-type') || '';
            if (r.ok && ct.includes('application/json')) {
                const j = await r.json();
                if (j.success) {
                    await Swal.fire(swalTheme({ icon: 'success', title: 'Berhasil', text: j.message || 'Rekap dihapus.' }));
                    window.location.reload();
                    return;
                }
            }
            await Swal.fire(swalTheme({ icon: 'error', title: 'Gagal', text: 'Tidak dapat menghapus laporan.' }));
        } catch {
            await Swal.fire(swalTheme({ icon: 'error', title: 'Gagal', text: 'Terjadi kesalahan jaringan.' }));
        } finally {
            if (btn) btn.disabled = false;
        }
    });

    function esc(s) { const d = document.createElement('div'); d.textContent = s ?? ''; return d.innerHTML; }
    function formatRp(n) { return 'Rp ' + (Number(n) || 0).toLocaleString('id-ID'); }
    function normalizeUrl(u) {
        if (!u) return '';
        const raw = String(u);
        if (/^data:image/i.test(raw)) return raw;
        if (/^https?:\/\//i.test(raw)) return raw;
        if (raw.startsWith('/')) return BASE + raw;
        return BASE + '/' + raw.replace(/^\/+/, '');
    }
    function qaAttr(s) { return String(s ?? '').replace(/"/g, '&quot;'); }
    function renderPdfActionsHtml(d) {
        if (d.pdf_download_url && d.pdf_available) {
            return `<a href="${qaAttr(d.pdf_download_url)}" class="btn btn-sm sppd-btn-modal-pdf" target="_blank" rel="noopener" title="Unduh PDF"><i class="bi bi-file-earmark-arrow-down"></i> Unduh PDF</a>`;
        }
        return '';
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
            ${d.revision_note ? `<p class="sppd-detail-sub sppd-detail-sub--spaced">Catatan revisi</p><div class="sppd-revisi-inline">${esc(d.revision_note)}</div>` : ''}
            ${d.rejection_note ? `<p class="sppd-detail-sub sppd-detail-sub--spaced">Alasan penolakan</p><div class="sppd-revisi-inline">${esc(d.rejection_note)}</div>` : ''}
        `;
    }

    document.getElementById('section-sppd-list')?.addEventListener('click', async (e) => {
        const detailBtn = e.target.closest('.sppd-btn-detail');
        if (detailBtn) {
            const id = detailBtn.getAttribute('data-detail-id');
            const modal = document.getElementById('sppd-modal-detail');
            const detailBody = document.getElementById('sppd-detail-body');
            const formSelesai = document.getElementById('sppd-form-selesai');
            const editBtn = document.getElementById('sppd-detail-edit');
            const pdfWrap = document.getElementById('sppd-detail-pdf-wrap');
            pdfWrap.hidden = true; pdfWrap.innerHTML = '';
            detailBody.innerHTML = '<p>Memuat…</p>';
            modal.style.display = 'flex';
            formSelesai.style.display = 'none';
            editBtn.style.display = 'none';
            try {
                const r = await fetch(BASE + '/sppd/' + id + '/json', { headers: { Accept: 'application/json' } });
                const j = await r.json();
                const d = j.sppd;
                detailBody.innerHTML = renderDetail(d);
                const pdfHtml = renderPdfActionsHtml(d);
                if (pdfHtml) { pdfWrap.innerHTML = pdfHtml; pdfWrap.hidden = false; }
                if (d.status === 'revision') { editBtn.href = BASE + '/sppd/' + id + '/edit'; editBtn.style.display = 'inline-flex'; }
                if (d.status === 'approved') { formSelesai.action = BASE + '/sppd/' + id + '/selesai'; formSelesai.style.display = 'inline-flex'; }
            } catch {
                detailBody.innerHTML = '<p>Gagal memuat data.</p>';
                pdfWrap.hidden = true; pdfWrap.innerHTML = '';
            }
            return;
        }
        const revisiBtn = e.target.closest('.sppd-btn-revisi');
        if (revisiBtn) {
            document.getElementById('sppd-revisi-note').textContent = revisiBtn.getAttribute('data-revisi-note') || '';
            document.getElementById('sppd-revisi-date').textContent = revisiBtn.getAttribute('data-revisi-at') ? ('Tanggal revisi: ' + revisiBtn.getAttribute('data-revisi-at')) : '';
            document.getElementById('sppd-revisi-edit').href = revisiBtn.getAttribute('data-revisi-edit') || '#';
            document.getElementById('sppd-modal-revisi').style.display = 'flex';
        }
    });

    document.querySelectorAll('[data-close-sppd-modal]').forEach(el => {
        el.addEventListener('click', () => { document.getElementById('sppd-modal-detail').style.display = 'none'; });
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
})();
</script>
@endpush
