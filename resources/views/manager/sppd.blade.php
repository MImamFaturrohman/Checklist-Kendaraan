@php use App\Models\Sppd; @endphp

@extends('layouts.dash-app')

@section('title', 'Rekap SPPD (Manager)')
@section('pageTitle', 'Rekap SPPD')
@section('pageSubtitle', 'PT ARTHA DAYA COALINDO')

@php $premiumBgId = 'manager_sppd'; @endphp

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

@section('content')
    <div class="admin-shell" style="position:relative;z-index:1">
        <div class="portal-wrapper">
            <div id="mgr-sppd-live-root" data-vms-sppd-live>
            @fragment('manager-sppd-body')

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
                                    @php
                                        $mgrPendingPdf = $s->pdf_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($s->pdf_path);
                                    @endphp
                                    <div class="sppd-aksi-btns">
                                        <button type="button" class="btn btn-sm sppd-icon-btn sppd-btn-primary mgr-sppd-detail" data-id="{{ $s->id }}" title="Detail Laporan" aria-label="Detail Laporan"><i class="bi bi-eye-fill"></i></button>
                                        @if($mgrPendingPdf)
                                            <a href="{{ route('manager.sppd.pdf', $s) }}" class="btn btn-sm sppd-icon-btn sppd-btn-secondary-lite" target="_blank" rel="noopener" title="Unduh PDF" aria-label="Unduh PDF"><i class="bi bi-file-earmark-pdf-fill"></i></a>
                                        @endif
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
            <div class="sppd-pagination-scroll">
                <div class="admin-pagination portal-pagination-wrap sppd-pagination--unified">{{ $pending->links() }}</div>
            </div>

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
                                $mgrHistoryPdfOk = $s->pdf_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($s->pdf_path);
                            @endphp
                            <tr>
                                <td>{{ $s->nama_driver }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($s->keperluan_dinas, 36) }}</td>
                                <td><x-sppd-status-badge :status="$s->status" /></td>
                                <td class="sppd-cell-muted">{{ $s->approved_at?->format('d/m/Y H:i') ?? $s->rejected_at?->format('d/m/Y H:i') ?? $s->updated_at->format('d/m/Y') }}</td>
                                <td>
                                    <div class="sppd-aksi-btns">
                                        @if($sppdNeedsPdf)
                                            <button type="button" class="btn btn-sm sppd-icon-btn sppd-btn-primary mgr-sppd-regen-pdf" data-id="{{ $s->id }}" title="Buat PDF (belum tersedia)" aria-label="Buat PDF"><i class="bi bi-file-earmark-pdf"></i></button>
                                        @endif
                                        @if($mgrHistoryPdfOk)
                                            <a href="{{ route('manager.sppd.pdf', $s) }}" class="btn btn-sm sppd-icon-btn sppd-btn-secondary-lite" target="_blank" rel="noopener" title="Unduh PDF" aria-label="Unduh PDF"><i class="bi bi-file-earmark-pdf-fill"></i></a>
                                        @elseif(!$sppdNeedsPdf)
                                            <span class="sppd-cell-muted">—</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="portal-empty">Belum ada riwayat.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="sppd-pagination-scroll">
                <div class="admin-pagination portal-pagination-wrap sppd-pagination--unified">{{ $history->links() }}</div>
            </div>
            @endfragment
            </div>
        </div>
    </div>

    <div id="sppd-modal-detail-manager" class="modal-overlay" style="display:none">
        <div class="modal-box profile-card sppd-modal-box" style="max-width:min(720px,100%);text-align:left;max-height:86vh;overflow:auto">
            <h3>Detail Rekap SPPD</h3>
            <div id="sppd-manager-detail-body" class="sppd-detail-html"></div>
            <div class="ppm-modal-actions">
                <div id="sppd-manager-detail-pdf-wrap" class="sppd-detail-pdf-wrap" hidden></div>
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

        function qaAttr(s) {
            return String(s ?? '').replace(/"/g, '&quot;');
        }

        function renderManagerPdfActions(d) {
            if (d.pdf_download_url && d.pdf_available) {
                return `<a href="${qaAttr(d.pdf_download_url)}" class="btn btn-sm sppd-btn-modal-pdf" target="_blank" rel="noopener" title="Unduh PDF"><i class="bi bi-file-earmark-arrow-down"></i> Unduh PDF</a>`;
            }
            return '';
        }

        document.getElementById('mgr-sppd-live-root')?.addEventListener('click', async (e) => {
            const detailBtn = e.target.closest('.mgr-sppd-detail');
            if (detailBtn) {
                const r = await fetch(detail(detailBtn.dataset.id), { headers: { Accept: 'application/json' } });
                const j = await r.json();
                const d = j.sppd;
                const modal = document.getElementById('sppd-modal-detail-manager');
                const detailBody = document.getElementById('sppd-manager-detail-body');
                const pdfWrap = document.getElementById('sppd-manager-detail-pdf-wrap');
                pdfWrap.hidden = true;
                pdfWrap.innerHTML = '';
                detailBody.innerHTML = renderDetail(d);
                const pdfHtml = renderManagerPdfActions(d);
                if (pdfHtml) {
                    pdfWrap.innerHTML = pdfHtml;
                    pdfWrap.hidden = false;
                }
                modal.style.display = 'flex';
                return;
            }
            const apprBtn = e.target.closest('.mgr-sppd-approve');
            if (apprBtn) {
                const c = await Swal.fire({ title: 'Setujui rekap ini?', icon: 'question', showCancelButton: true, confirmButtonText: 'Setujui' });
                if (!c.isConfirmed) return;
                const r = await fetch(approve(apprBtn.dataset.id), { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' } });
                const j = await r.json();
                if (j.success) { await Swal.fire('OK', j.message, 'success'); location.reload(); }
                else Swal.fire('Gagal', j.message || '', 'error');
                return;
            }
            const regenBtn = e.target.closest('.mgr-sppd-regen-pdf');
            if (regenBtn) {
                const c = await Swal.fire({
                    title: 'Buat file PDF?',
                    text: 'Diperlukan jika persetujuan sebelumnya gagal menyimpan PDF (misalnya gd belum aktif).',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Buat PDF',
                });
                if (!c.isConfirmed) return;
                const r = await fetch(regenPdf(regenBtn.dataset.id), { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' } });
                const j = await r.json().catch(() => ({}));
                if (j.success) { await Swal.fire('OK', j.message, 'success'); location.reload(); }
                else Swal.fire('Gagal', j.message || 'Tidak dapat membuat PDF', 'error');
                return;
            }
            const rejBtn = e.target.closest('.mgr-sppd-reject');
            if (rejBtn) {
                const { value: note } = await Swal.fire({
                    title: 'Alasan penolakan',
                    input: 'textarea',
                    showCancelButton: true,
                    confirmButtonText: 'Tolak',
                    inputValidator: (v) => !v && 'Wajib diisi',
                });
                if (!note) return;
                const r = await fetch(reject(rejBtn.dataset.id), {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json', 'Content-Type': 'application/json' },
                    body: JSON.stringify({ rejection_note: note }),
                });
                const j = await r.json();
                if (j.success) { await Swal.fire('OK', j.message, 'success'); location.reload(); }
                else Swal.fire('Gagal', j.message || '', 'error');
            }
        });
        document.querySelectorAll('[data-close-manager-sppd-modal]').forEach(el => {
            el.addEventListener('click', () => { document.getElementById('sppd-modal-detail-manager').style.display = 'none'; });
        });
        document.getElementById('sppd-modal-detail-manager')?.addEventListener('click', (e) => {
            if (e.target.id === 'sppd-modal-detail-manager') e.currentTarget.style.display = 'none';
        });

    })();
    </script>
@endsection
