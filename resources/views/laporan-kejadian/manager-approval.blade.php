<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.favicon')
    <title>Persetujuan Laporan Kejadian</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.2.0/dist/signature_pad.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>
    <style>
        :root {
            --blue: #002a7a;
            --blue-light: #1d4ed8;
            --bg: #f1f5f9;
            --card: #fff;
            --border: #e2e8f0;
            --text: #1e293b;
            --muted: #64748b;
        }
        *, *::before, *::after { box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: var(--bg); color: var(--text); margin: 0; padding: 0; min-height: 100vh; }

        .page-header { background: var(--blue); padding: 18px 0; display: flex; justify-content: center; }
        .page-header-inner { max-width: 780px; margin: 0 auto; padding: 0 20px; display: flex; align-items: center; gap: 14px; }
        .page-header img { height: 44px; }
        .page-header-title { color: #fff; font-size: 1.1rem; font-weight: 700; line-height: 1.3; }
        .page-header-sub { color: rgba(255,255,255,.7); font-size: 0.8rem; margin-top: 2px; }

        .container { max-width: 780px; margin: 28px auto 48px; padding: 0 16px; }

        .card { background: var(--card); border-radius: 14px; box-shadow: 0 2px 12px rgba(0,0,0,.07); overflow: hidden; margin-bottom: 20px; }
        .card-head { padding: 16px 22px; border-bottom: 1px solid var(--border); display: flex; align-items: center; gap: 10px; }
        .card-head-icon { width: 34px; height: 34px; border-radius: 8px; background: rgba(0,42,122,.08); display: flex; align-items: center; justify-content: center; color: var(--blue); font-size: 1rem; flex-shrink: 0; }
        .card-head-title { font-weight: 700; font-size: 0.95rem; }
        .card-body { padding: 20px 22px; }

        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px 20px; }
        @media (max-width: 520px) { .info-grid { grid-template-columns: 1fr; } }
        .info-item { }
        .info-item-label { font-size: 0.72rem; font-weight: 600; color: var(--muted); text-transform: uppercase; letter-spacing: .4px; margin-bottom: 2px; }
        .info-item-value { font-size: 0.88rem; color: var(--text); }
        .info-item-full { grid-column: 1 / -1; }

        .kat-badge { display: inline-block; font-size: 0.72rem; font-weight: 700; padding: 3px 12px; border-radius: 99px; }
        .kat-inc { background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; }
        .kat-nm  { background: #fef3c7; color: #b45309; border: 1px solid #fcd34d; }

        .foto-grid { display: flex; gap: 12px; flex-wrap: wrap; margin-top: 4px; }
        .foto-item { flex: 1 1 200px; max-width: 240px; border: 1px solid var(--border); border-radius: 10px; overflow: hidden; }
        .foto-item img { width: 100%; height: 160px; object-fit: cover; display: block; }
        .foto-caption { padding: 8px 10px; font-size: 0.78rem; color: var(--muted); border-top: 1px solid var(--border); }

        .sig-section { margin-top: 4px; }
        .sig-label { font-size: 0.82rem; font-weight: 600; color: var(--text); margin-bottom: 8px; }
        .sig-pad-wrap { position: relative; border: 2px dashed #cbd5e1; border-radius: 10px; background: #f8fafc; overflow: hidden; cursor: crosshair; }
        .sig-pad-wrap canvas { display: block; width: 100%; height: 160px; touch-action: none; }
        .sig-hint { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; pointer-events: none; color: var(--muted); font-size: 0.82rem; transition: opacity .2s; }
        .sig-hint.hidden { opacity: 0; }
        .sig-hint svg { opacity: .45; }
        .sig-clear-btn { margin-top: 8px; background: none; border: 1px solid #cbd5e1; color: var(--muted); font-size: 0.78rem; border-radius: 6px; padding: 4px 14px; cursor: pointer; }
        .sig-clear-btn:hover { background: #f1f5f9; }

        .submit-btn { width: 100%; padding: 14px; background: var(--blue); color: #fff; border: none; border-radius: 10px; font-size: 1rem; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: background .15s; margin-top: 18px; }
        .submit-btn:hover { background: var(--blue-light); }
        .submit-btn:disabled { opacity: .65; cursor: default; }

        .already-done { text-align: center; padding: 40px 20px; }
        .already-done-icon { font-size: 3.5rem; color: #16a34a; margin-bottom: 14px; }
        .already-done-title { font-size: 1.25rem; font-weight: 700; margin-bottom: 8px; }
        .already-done-text { color: var(--muted); font-size: 0.9rem; }

        .divider { height: 1px; background: var(--border); margin: 18px 0; }
        .text-area-preview { background: #f8fafc; border: 1px solid var(--border); border-radius: 8px; padding: 12px 14px; font-size: 0.85rem; line-height: 1.65; white-space: pre-wrap; color: var(--text); }
    </style>
</head>
<body>

<div class="page-header">
    <div class="page-header-inner">
        <img src="{{ asset('images/VMS.png') }}" alt="Logo">
        <div>
            <div class="page-header-title">Persetujuan Laporan Kejadian</div>
            <div class="page-header-sub">Vehicle Management System</div>
        </div>
    </div>
</div>

<div class="container">

@if($alreadySigned)
    <div class="card">
        <div class="card-body already-done">
            <div class="already-done-icon"><i class="bi bi-patch-check-fill"></i></div>
            <div class="already-done-title">Laporan Sudah Disetujui</div>
            <div class="already-done-text">
                Anda telah menandatangani laporan kejadian dari <strong>{{ $laporan->nama }}</strong>.<br>
                PDF laporan telah dibuat dan tersedia di sistem administrasi.
            </div>
        </div>
    </div>
@else
    {{-- Data Pelapor --}}
    <div class="card">
        <div class="card-head">
            <div class="card-head-icon"><i class="bi bi-person-badge"></i></div>
            <div class="card-head-title">Data Pelapor</div>
        </div>
        <div class="card-body">
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-item-label">Nama Pelapor</div>
                    <div class="info-item-value">{{ $laporan->nama }}</div>
                </div>
                <div class="info-item">
                    <div class="info-item-label">NIP</div>
                    <div class="info-item-value">{{ $laporan->nip }}</div>
                </div>
                <div class="info-item">
                    <div class="info-item-label">Jabatan</div>
                    <div class="info-item-value">{{ $laporan->jabatan }}</div>
                </div>
                <div class="info-item">
                    <div class="info-item-label">Bidang / Bagian</div>
                    <div class="info-item-value">{{ $laporan->bidang?->labelLengkap() ?? '–' }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Detail Kejadian --}}
    <div class="card">
        <div class="card-head">
            <div class="card-head-icon"><i class="bi bi-exclamation-triangle"></i></div>
            <div class="card-head-title">Detail Kejadian</div>
        </div>
        <div class="card-body">
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-item-label">Kategori</div>
                    <div class="info-item-value">
                        @if($laporan->kategori === 'Incident')
                            <span class="kat-badge kat-inc">Incident</span>
                        @else
                            <span class="kat-badge kat-nm">Nearmiss</span>
                        @endif
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-item-label">Waktu Kejadian</div>
                    <div class="info-item-value">{{ $laporan->waktu_kejadian?->timezone(config('app.timezone'))->translatedFormat('d F Y, H:i') }}</div>
                </div>
                <div class="info-item info-item-full">
                    <div class="info-item-label">Lokasi Kejadian</div>
                    <div class="info-item-value">{{ $laporan->lokasi_kejadian }}</div>
                </div>
                <div class="info-item">
                    <div class="info-item-label">No. Kendaraan</div>
                    <div class="info-item-value">{{ $laporan->nomor_kendaraan }}</div>
                </div>
                <div class="info-item">
                    <div class="info-item-label">Jenis Kendaraan</div>
                    <div class="info-item-value">{{ $laporan->jenis_kendaraan }}</div>
                </div>
            </div>

            <div class="divider"></div>

            <div style="margin-bottom: 12px;">
                <div class="info-item-label" style="margin-bottom:6px">Peristiwa</div>
                <div class="text-area-preview">{{ $laporan->peristiwa }}</div>
            </div>
            <div style="margin-bottom: 12px;">
                <div class="info-item-label" style="margin-bottom:6px">Sebelum Kejadian</div>
                <div class="text-area-preview">{{ $laporan->sebelum_kejadian }}</div>
            </div>
            <div>
                <div class="info-item-label" style="margin-bottom:6px">Uraian Kejadian</div>
                <div class="text-area-preview">{{ $laporan->uraian_kejadian }}</div>
            </div>
        </div>
    </div>

    {{-- Foto Lampiran --}}
    @if(count($fotoSlides ?? []) > 0)
    <div class="card">
        <div class="card-head">
            <div class="card-head-icon"><i class="bi bi-images"></i></div>
            <div class="card-head-title">Foto Lampiran</div>
        </div>
        <div class="card-body">
            <div class="foto-grid">
                @foreach($fotoSlides as $slide)
                    <div class="foto-item">
                        @if($slide['url'])
                            <img src="{{ $slide['url'] }}" alt="Foto kejadian">
                        @else
                            <div style="height:160px;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:0.8rem;background:#f8fafc;">
                                <i class="bi bi-image" style="font-size:2rem"></i>
                            </div>
                        @endif
                        @if($slide['penjelasan'])
                            <div class="foto-caption">{{ $slide['penjelasan'] }}</div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- Tanda Tangan --}}
    <div class="card">
        <div class="card-head">
            <div class="card-head-icon"><i class="bi bi-pen"></i></div>
            <div class="card-head-title">Tanda Tangan Persetujuan Manager</div>
        </div>
        <div class="card-body">
            <p style="font-size:0.85rem;color:var(--muted);margin-bottom:14px;">
                Dengan menandatangani di bawah ini, Anda menyatakan bahwa laporan kejadian di atas telah Anda baca
                dan setujui untuk diterbitkan dalam bentuk PDF resmi.
            </p>
            <div class="sig-section">
                <div class="sig-label">Tanda Tangan <span style="color:#ef4444">*</span></div>
                <div class="sig-pad-wrap" id="sig-wrap">
                    <canvas id="sig-canvas" style="height:160px"></canvas>
                    <div class="sig-hint" id="sig-hint">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <path d="M17 3a2.83 2.83 0 114 4L7.5 20.5 2 22l1.5-5.5L17 3z" stroke="currentColor" stroke-width="2"/>
                        </svg>
                        <span>Tanda tangan di sini</span>
                    </div>
                </div>
                <button type="button" class="sig-clear-btn" id="sig-clear">&#x2715; Hapus</button>
            </div>

            <button type="button" class="submit-btn" id="btn-approve">
                <i class="bi bi-check-circle-fill"></i>
                Setujui &amp; Kirim Tanda Tangan
            </button>
        </div>
    </div>
@endif

</div>

<script>
(function () {
    const canvas = document.getElementById('sig-canvas');
    if (!canvas) return;

    const hint = document.getElementById('sig-hint');
    const clearBtn = document.getElementById('sig-clear');
    const approveBtn = document.getElementById('btn-approve');

    function resizePad() {
        const rect = canvas.getBoundingClientRect();
        if (!rect.width || !rect.height) return;
        const r = Math.max(window.devicePixelRatio || 1, 1);
        canvas.width = rect.width * r;
        canvas.height = rect.height * r;
        const ctx = canvas.getContext('2d');
        ctx.setTransform(1, 0, 0, 1, 0, 0);
        ctx.scale(r, r);
    }

    resizePad();

    const pad = new window.SignaturePad(canvas, {
        backgroundColor: 'rgba(255,255,255,0)',
        penColor: '#0f172a',
        minWidth: 1.5,
        maxWidth: 3,
    });

    pad.addEventListener('beginStroke', () => { if (hint) hint.classList.add('hidden'); });

    clearBtn.addEventListener('click', () => {
        pad.clear();
        if (hint) hint.classList.remove('hidden');
    });

    let resizeTimer;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            const data = pad.isEmpty() ? [] : pad.toData();
            resizePad();
            pad.clear();
            if (data.length) pad.fromData(data);
            else if (hint) hint.classList.remove('hidden');
        }, 200);
    });

    approveBtn.addEventListener('click', async () => {
        if (pad.isEmpty()) {
            Swal.fire({ icon: 'warning', title: 'Tanda Tangan Kosong', text: 'Mohon tanda tangan sebelum menyetujui laporan.', confirmButtonColor: '#002a7a' });
            return;
        }

        const confirm = await Swal.fire({
            icon: 'question',
            title: 'Setujui Laporan?',
            text: 'Tanda tangan Anda akan disimpan permanen dan PDF laporan akan dibuat. Lanjutkan?',
            showCancelButton: true,
            confirmButtonText: 'Ya, setujui',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#002a7a',
        });
        if (!confirm.isConfirmed) return;

        const ttd = pad.toDataURL('image/png');

        approveBtn.disabled = true;
        approveBtn.innerHTML = '<span style="display:inline-block;width:15px;height:15px;border:2px solid rgba(255,255,255,.35);border-top-color:#fff;border-radius:50%;animation:spin .6s linear infinite;margin-right:8px;vertical-align:middle"></span> Memproses...';

        try {
            const res = await fetch('{{ route("laporan-kejadian.approval.sign", ["token" => $laporan->manager_approval_token ?? "x"]) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ ttd_manager: ttd }),
            });
            const data = await res.json().catch(() => ({}));

            if (res.ok && data.success) {
                await Swal.fire({
                    icon: 'success',
                    title: 'Laporan Disetujui',
                    text: 'Tanda tangan Anda telah disimpan dan PDF laporan telah dibuat.',
                    confirmButtonColor: '#002a7a',
                });
                location.reload();
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal', text: data.message || 'Terjadi kesalahan. Coba lagi.', confirmButtonColor: '#002a7a' });
                approveBtn.disabled = false;
                approveBtn.innerHTML = '<i class="bi bi-check-circle-fill"></i> Setujui &amp; Kirim Tanda Tangan';
            }
        } catch {
            Swal.fire({ icon: 'error', title: 'Koneksi Bermasalah', text: 'Tidak dapat terhubung ke server.', confirmButtonColor: '#002a7a' });
            approveBtn.disabled = false;
            approveBtn.innerHTML = '<i class="bi bi-check-circle-fill"></i> Setujui &amp; Kirim Tanda Tangan';
        }
    });
})();
</script>
<style>
    @keyframes spin { to { transform: rotate(360deg); } }
</style>
</body>
</html>
