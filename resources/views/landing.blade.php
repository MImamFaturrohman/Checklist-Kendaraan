<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ceklist Kendaraan - PT ARTHA DAYA COALINDO</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* ── HERO ── */
        .lp-hero {
            background: linear-gradient(135deg, #011d5c 0%, #031e6b 40%, #06318a 100%);
            padding: 60px 0 64px;
            position: relative;
            overflow: hidden;
        }
        .lp-hero::before {
            content: "";
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse 70% 80% at 80% 50%, rgba(255,211,0,.06) 0%, transparent 70%),
                        radial-gradient(ellipse 40% 60% at 10% 80%, rgba(6,49,138,.5) 0%, transparent 60%);
            pointer-events: none;
        }
        .lp-hero-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 56px;
            align-items: center;
            position: relative;
        }
        .lp-hero-left {}
        .lp-hero-kicker {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255,211,0,.12);
            border: 1px solid rgba(255,211,0,.25);
            color: var(--dash-yellow);
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 2px;
            padding: 6px 12px;
            border-radius: 999px;
            margin-bottom: 18px;
            text-transform: uppercase;
        }
        .lp-hero-title {
            font-size: clamp(2rem, 3.8vw, 3rem);
            font-weight: 800;
            color: #fff;
            line-height: 1.12;
            margin: 0 0 16px;
        }
        .lp-hero-title span { color: var(--dash-yellow); }
        .lp-hero-desc {
            font-size: 1rem;
            color: rgba(255,255,255,.65);
            line-height: 1.65;
            margin: 0 0 30px;
            max-width: 420px;
        }
        .lp-hero-btns {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }
        .lp-btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--dash-yellow);
            color: #011d5c;
            font-weight: 700;
            font-size: 0.88rem;
            padding: 12px 22px;
            border-radius: 12px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all .2s ease;
            box-shadow: 0 6px 18px rgba(255,211,0,.28);
        }
        .lp-btn-primary:hover { background: #ffe033; transform: translateY(-2px); box-shadow: 0 10px 24px rgba(255,211,0,.35); }
        .lp-btn-secondary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255,255,255,.08);
            color: #fff;
            font-weight: 600;
            font-size: 0.88rem;
            padding: 12px 22px;
            border-radius: 12px;
            text-decoration: none;
            border: 1px solid rgba(255,255,255,.18);
            cursor: pointer;
            transition: all .2s ease;
        }
        .lp-btn-secondary:hover { background: rgba(255,255,255,.14); transform: translateY(-2px); }

        /* Feature cards */
        .lp-hero-right { display: flex; flex-direction: column; gap: 14px; }
        .lp-feat-card {
            display: flex;
            align-items: flex-start;
            gap: 16px;
            background: rgba(255,255,255,.055);
            border: 1px solid rgba(255,255,255,.1);
            border-radius: 16px;
            padding: 18px 20px;
            backdrop-filter: blur(6px);
            transition: background .2s, transform .2s;
        }
        .lp-feat-card:hover { background: rgba(255,255,255,.09); transform: translateX(4px); }
        .lp-feat-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            flex-shrink: 0;
        }
        .lp-feat-icon-yellow { background: rgba(255,211,0,.15); color: #ffd300; }
        .lp-feat-icon-blue   { background: rgba(96,165,250,.15); color: #60a5fa; }
        .lp-feat-icon-green  { background: rgba(52,211,153,.15); color: #34d399; }
        .lp-feat-title { font-weight: 700; color: #fff; font-size: .92rem; margin: 0 0 4px; }
        .lp-feat-desc  { font-size: .8rem; color: rgba(255,255,255,.55); margin: 0; line-height: 1.5; }

        /* ── NAVBAR links ── */
        .lp-nav-links { display: flex; align-items: center; gap: 4px; }
        .lp-nav-link {
            color: rgba(255,255,255,.78);
            font-size: .84rem;
            font-weight: 600;
            text-decoration: none;
            padding: 7px 13px;
            border-radius: 9px;
            transition: all .18s;
            white-space: nowrap;
        }
        .lp-nav-link:hover { color: #fff; background: rgba(255,255,255,.1); }

        /* ── ARMADA SECTION ── */
        .lp-section-heading {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }
        .lp-search-wrap {
            position: relative;
            display: flex;
            align-items: center;
            min-width: 240px;
            flex: 1;
            max-width: 360px;
        }
        .lp-search-icon {
            position: absolute;
            left: 13px;
            color: #94a3b8;
            pointer-events: none;
        }
        .lp-search-input {
            width: 100%;
            padding: 10px 38px 10px 40px;
            border: 1.5px solid #d1d5db;
            border-radius: 12px;
            font-size: .86rem;
            background: #fff;
            color: #0f172a;
            transition: border-color .2s, box-shadow .2s;
        }
        .lp-search-input:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,.1); }
        .lp-search-input::placeholder { color: #94a3b8; }
        .lp-search-clear {
            position: absolute;
            right: 11px;
            width: 22px; height: 22px;
            display: flex; align-items: center; justify-content: center;
            border-radius: 50%;
            background: #e2e8f0;
            color: #64748b;
            font-size: .9rem;
            cursor: pointer;
            border: none;
            line-height: 1;
            transition: background .15s;
        }
        .lp-search-clear:hover { background: #cbd5e1; }

        /* Vehicle table animation */
        #armada-tbody tr { animation: rowIn .2s ease both; }
        @keyframes rowIn { from { opacity:0; transform:translateY(6px); } to { opacity:1; transform:none; } }
        #armada-tbody tr:nth-child(2) { animation-delay:.03s; }
        #armada-tbody tr:nth-child(3) { animation-delay:.06s; }
        #armada-tbody tr:nth-child(4) { animation-delay:.09s; }
        #armada-tbody tr:nth-child(5) { animation-delay:.12s; }

        /* Client-side pagination */
        .lp-pagination { display: flex; justify-content: center; gap: 5px; margin-top: 16px; flex-wrap: wrap; }
        .lp-page-btn {
            min-width: 36px; height: 36px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            color: #475569;
            border-radius: 10px;
            font-size: .82rem;
            font-weight: 600;
            cursor: pointer;
            transition: all .15s;
            padding: 0 10px;
            display: inline-flex; align-items: center; justify-content: center;
        }
        .lp-page-btn:hover:not(.active):not(:disabled) { background: #e0e7ff; color: var(--dash-blue); border-color: #c7d2fe; }
        .lp-page-btn.active { background: var(--dash-blue); color: #fff; border-color: var(--dash-blue); box-shadow: 0 3px 10px rgba(0,42,122,.2); }
        .lp-page-btn:disabled { opacity: .4; cursor: not-allowed; }

        /* ── FORM SECTION ── */
        .lp-form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px 16px;
        }
        .lp-form-full { grid-column: 1 / -1; }

        /* ── SCROLL REVEAL ── */
        .reveal { opacity: 0; transform: translateY(22px); transition: opacity .55s ease, transform .55s ease; }
        .reveal.visible { opacity: 1; transform: none; }

        /* ── RESPONSIVE ── */
        @media (max-width: 768px) {
            .lp-hero { padding: 44px 0 48px; }
            .lp-hero-grid { grid-template-columns: 1fr; gap: 36px; }
            .lp-hero-title { font-size: 1.9rem; }
            .lp-nav-links { display: none; }
            .lp-form-grid { grid-template-columns: 1fr; }
            .lp-section-heading { flex-direction: column; align-items: flex-start; }
            .lp-search-wrap { max-width: 100%; width: 100%; }
        }
        @media (max-width: 480px) {
            .lp-hero-title { font-size: 1.6rem; }
            .lp-hero-btns { flex-direction: column; }
            .lp-btn-primary, .lp-btn-secondary { justify-content: center; }
        }

        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body class="landing-body">

{{-- ══════════════════ NAVBAR ══════════════════ --}}
<nav class="landing-nav">
    <div class="landing-container landing-nav-inner">
        <a href="{{ route('landing') }}" class="landing-nav-brand">
            <img src="{{ asset('images/ADC PM Logo NEW.png') }}" alt="Logo" class="landing-nav-logo">
            <div>
                <div class="landing-nav-title">Ceklist Kendaraan</div>
                <div class="landing-nav-sub">PT ARTHA DAYA COALINDO</div>
            </div>
        </a>

        <div class="lp-nav-links">
            <a href="#armada" class="lp-nav-link" onclick="smoothTo('armada',event)">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" style="display:inline;vertical-align:middle;margin-right:4px"><path d="M5 17H3a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v6a2 2 0 01-2 2h-2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="9" cy="17" r="2" stroke="currentColor" stroke-width="2"/><circle cx="17" cy="17" r="2" stroke="currentColor" stroke-width="2"/></svg>
                Daftar Armada
            </a>
            <a href="#form-peminjaman" style="vertical-align:middle; display: inline-flex; align-items: center;" class="lp-nav-link" onclick="smoothTo('form-peminjaman',event)">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" style="margin-right:4px">
                    <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                Request Peminjaman
            </a>
        </div>

        <div class="landing-nav-actions">
            @auth
                <a href="{{ route('dashboard') }}" class="landing-nav-login-btn">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none">
                        <path d="M3 12h18M12 5l7 7-7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Dashboard
                </a>
            @else
                <a href="{{ route('login') }}" class="landing-nav-login-btn">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4M10 17l5-5-5-5M15 12H3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Masuk
                </a>
            @endauth
        </div>
    </div>
</nav>

{{-- ══════════════════ HERO ══════════════════ --}}
<section class="lp-hero">
    <div class="landing-container">
        <div class="lp-hero-grid">

            {{-- LEFT --}}
            <div class="lp-hero-left">
                <div class="lp-hero-kicker">
                    <svg width="10" height="10" viewBox="0 0 10 10" fill="currentColor"><circle cx="5" cy="5" r="5"/></svg>
                    Sistem Armada Kendaraan
                </div>
                <h1 class="lp-hero-title">
                    Cek Ketersediaan &amp;<br>
                    <span>Ajukan Peminjaman</span>
                </h1>
                <p class="lp-hero-desc">
                    Lihat daftar kendaraan operasional yang tersedia, kemudian ajukan permintaan peminjaman.
                </p>
                <div class="lp-hero-btns">
                    <a href="#armada" class="lp-btn-primary" onclick="smoothTo('armada',event)">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M5 17H3a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v6a2 2 0 01-2 2h-2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="9" cy="17" r="2" stroke="currentColor" stroke-width="2"/><circle cx="17" cy="17" r="2" stroke="currentColor" stroke-width="2"/></svg>
                        Lihat Armada
                    </a>
                    <a href="#form-peminjaman" class="lp-btn-secondary" onclick="smoothTo('form-peminjaman',event)">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"">
                            <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        Request Peminjaman
                    </a>
                </div>
            </div>

            {{-- RIGHT: Feature cards --}}
            <div class="lp-hero-right">
                <div class="lp-feat-card">
                    <div class="lp-feat-icon lp-feat-icon-yellow">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/><path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    </div>
                    <div>
                        <p class="lp-feat-title">Cek Ketersediaan</p>
                        <p class="lp-feat-desc">Lihat seluruh armada kendaraan operasional yang terdaftar secara real-time.</p>
                    </div>
                </div>
                <div class="lp-feat-card">
                    <div class="lp-feat-icon lp-feat-icon-blue">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M12 12h.01M12 16h.01M8 12h.01M8 16h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    </div>
                    <div>
                        <p class="lp-feat-title">Ajukan Permintaan</p>
                        <p class="lp-feat-desc">Isi formulir online dengan detail kebutuhan peminjaman Anda.</p>
                    </div>
                </div>
                <div class="lp-feat-card">
                    <div class="lp-feat-icon lp-feat-icon-green">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" stroke="currentColor" stroke-width="2"/></svg>
                    </div>
                    <div>
                        <p class="lp-feat-title">Menunggu Persetujuan</p>
                        <p class="lp-feat-desc">Request Anda akan diproses dan disetujui oleh Manager yang berwenang.</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

{{-- ══════════════════ CONTENT ══════════════════ --}}
<div class="landing-container">

    {{-- ARMADA SECTION --}}
    <section class="landing-section reveal" id="armada">
        <div class="lp-section-heading">
            <div>
                <h2 class="landing-section-title">Daftar Armada Kendaraan</h2>
                <p class="landing-section-sub">Total <span id="armada-count">{{ $kendaraans->count() }}</span> kendaraan terdaftar</p>
            </div>
            <div class="lp-search-wrap">
                <svg class="lp-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none">
                    <circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/>
                    <path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                <input type="text" id="armada-search" class="lp-search-input"
                    placeholder="Cari nomor polisi atau jenis kendaraan..."
                    autocomplete="off">
                <button type="button" class="lp-search-clear" id="search-clear-btn" style="display:none" onclick="clearSearch()">&#x2715;</button>
            </div>
        </div>

        <div class="landing-card">
            <div class="admin-table-wrap">
                <table class="admin-table" style="table-layout:fixed">
                    <thead>
                        <tr>
                            <th style="width:56px">#</th>
                            <th style="width:38%">Nomor Kendaraan</th>
                            <th>Jenis Kendaraan</th>
                            <th>Bidang</th>
                        </tr>
                    </thead>
                    <tbody id="armada-tbody">
                        {{-- filled by JS --}}
                    </tbody>
                </table>
            </div>
            <div class="lp-pagination" id="armada-pagination"></div>
            <p id="armada-empty" style="display:none;text-align:center;color:#9ca3af;padding:32px;font-size:.88rem">
                Tidak ada kendaraan yang cocok dengan pencarian.
            </p>
        </div>
    </section>

    {{-- FORM SECTION --}}
    <section class="landing-section reveal" id="form-peminjaman">
        <div style="margin-bottom:20px">
            <h2 class="landing-section-title">Form Request Peminjaman Kendaraan</h2>
            <p class="landing-section-sub">Isi formulir di bawah untuk mengajukan permintaan peminjaman kendaraan</p>
        </div>

        <div class="landing-card landing-form-card">
            <div class="landing-form-banner">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" style="flex-shrink:0">
                    <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    <path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                <span>Request Peminjaman Kendaraan</span>
            </div>

            <form id="form-request" autocomplete="off">
                @csrf
                <div class="lp-form-grid">

                    <div class="checklist-field">
                        <span>Nama Lengkap <span style="color:#ef4444">*</span></span>
                        <input type="text" id="nama_lengkap" name="nama_lengkap"
                            placeholder="Masukkan nama lengkap Anda" required>
                    </div>

                    <div class="checklist-field">
                        <span>NIP <span style="color:#ef4444">*</span></span>
                        <input type="text" id="nip" name="nip"
                            placeholder="Nomor Induk Pegawai" required>
                    </div>

                    <div class="checklist-field">
                        <span>Divisi / Departemen <span style="color:#ef4444">*</span></span>
                        <input type="text" id="divisi" name="divisi"
                            placeholder="Contoh: HSE, Operasional, HR" required>
                    </div>

                    <div class="checklist-field">
                        <span>Kendaraan yang Dipinjam <span style="color:#ef4444">*</span></span>
                        <div class="checklist-control-wrap checklist-control-select">
                            <select id="nomor_kendaraan" name="nomor_kendaraan" required
                                onchange="onKendaraanChange(this)">
                                <option value="">-- Pilih Nomor Polisi --</option>
                                @foreach($kendaraans as $k)
                                    <option value="{{ $k->nomor_kendaraan }}"
                                        data-jenis="{{ $k->jenis_kendaraan }}">
                                        {{ $k->nomor_kendaraan }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="checklist-field lp-form-full">
                        <span>Jenis Kendaraan</span>
                        <input type="text" id="jenis_kendaraan" name="jenis_kendaraan"
                            placeholder="Terisi otomatis setelah memilih kendaraan" readonly>
                    </div>

                    <div class="checklist-field lp-form-full">
                        <span>Alasan / Keperluan Peminjaman <span style="color:#ef4444">*</span></span>
                        <textarea id="alasan" name="alasan" rows="4"
                            placeholder="Jelaskan keperluan atau tujuan peminjaman kendaraan..." required></textarea>
                    </div>

                </div>

                <div class="landing-form-note" style="margin-top:12px">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" style="flex-shrink:0;color:#2563eb">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                        <path d="M12 8v4M12 16h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    <span>Request Anda akan dikirim ke Manager untuk mendapatkan persetujuan. Harap menunggu konfirmasi lebih lanjut.</span>
                </div>

                <button type="submit" class="landing-submit-btn" id="btn-submit-request" style="margin-top:14px">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                    <path d="M14 2H6A2 2 0 0 0 4 4V20A2 2 0 0 0 6 22H18A2 2 0 0 0 20 20V8L14 2ZM14 2V8H20M12 18V12M12 12L9 15M12 12L15 15"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Kirim Request Peminjaman
                </button>
            </form>
        </div>
    </section>

</div>

{{-- FOOTER --}}
<footer class="landing-footer">
    <div class="landing-container">
        <div class="landing-footer-inner">
            <div class="landing-footer-brand">
                <img src="{{ asset('images/ADC PM Logo NEW.png') }}" alt="Logo" style="height:30px;border-radius:6px">
                <span>Ceklist Kendaraan &mdash; PT ARTHA DAYA COALINDO</span>
            </div>
            <p class="landing-footer-copy">&copy; {{ date('Y') }} All rights reserved.</p>
        </div>
    </div>
</footer>

{{-- ══════════════════ SCRIPTS ══════════════════ --}}
<script>
/* ── DATA ── */
const KENDARAANS = @json($kendaraans);
const PER_PAGE   = 10;
let currentPage  = 1;
let filtered     = [...KENDARAANS];

/* ── RENDER ── */
function renderTable() {
    const tbody    = document.getElementById('armada-tbody');
    const emptyMsg = document.getElementById('armada-empty');
    const pgWrap   = document.getElementById('armada-pagination');
    const count    = document.getElementById('armada-count');

    count.textContent = filtered.length;

    if (filtered.length === 0) {
        tbody.innerHTML = '';
        emptyMsg.style.display = '';
        pgWrap.innerHTML = '';
        return;
    }
    emptyMsg.style.display = 'none';

    const totalPages = Math.ceil(filtered.length / PER_PAGE);
    if (currentPage > totalPages) currentPage = totalPages;

    const start  = (currentPage - 1) * PER_PAGE;
    const pageData = filtered.slice(start, start + PER_PAGE);

    tbody.innerHTML = pageData.map((k, i) => `
        <tr>
            <td>${start + i + 1}</td>
            <td><span class="landing-nopol-badge">${escHtml(k.nomor_kendaraan)}</span></td>
            <td>${escHtml(k.jenis_kendaraan)}</td>
            <td>${k.bidang ? escHtml(k.bidang) : '<span style="color:#94a3b8">—</span>'}</td>
        </tr>
    `).join('');

    /* pagination */
    pgWrap.innerHTML = buildPagination(totalPages);
}

function buildPagination(total) {
    if (total <= 1) return '';
    let html = '';

    html += `<button class="lp-page-btn" onclick="goPage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>&#8592;</button>`;

    const range = pageRange(currentPage, total);
    let prev = null;
    for (const p of range) {
        if (p === '…') { html += `<span class="lp-page-btn" style="cursor:default;opacity:.5">…</span>`; }
        else {
            html += `<button class="lp-page-btn ${p === currentPage ? 'active' : ''}" onclick="goPage(${p})">${p}</button>`;
        }
        prev = p;
    }

    html += `<button class="lp-page-btn" onclick="goPage(${currentPage + 1})" ${currentPage === total ? 'disabled' : ''}>&#8594;</button>`;
    return html;
}

function pageRange(cur, total) {
    if (total <= 7) return Array.from({length: total}, (_, i) => i + 1);
    const pages = [];
    pages.push(1);
    if (cur > 3) pages.push('…');
    for (let i = Math.max(2, cur - 1); i <= Math.min(total - 1, cur + 1); i++) pages.push(i);
    if (cur < total - 2) pages.push('…');
    pages.push(total);
    return pages;
}

function goPage(p) {
    const total = Math.ceil(filtered.length / PER_PAGE);
    if (p < 1 || p > total) return;
    currentPage = p;
    renderTable();
    document.getElementById('armada').scrollIntoView({behavior: 'smooth', block: 'start'});
}

function escHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

/* ── SEARCH ── */
let debounceTimer;
document.getElementById('armada-search').addEventListener('input', function () {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        const q = this.value.trim().toLowerCase();
        document.getElementById('search-clear-btn').style.display = q ? '' : 'none';
        filtered = KENDARAANS.filter(k =>
            k.nomor_kendaraan.toLowerCase().includes(q) ||
            k.jenis_kendaraan.toLowerCase().includes(q) ||
            (k.bidang && k.bidang.toLowerCase().includes(q))
        );
        currentPage = 1;
        renderTable();
    }, 220);
});

function clearSearch() {
    document.getElementById('armada-search').value = '';
    document.getElementById('search-clear-btn').style.display = 'none';
    filtered = [...KENDARAANS];
    currentPage = 1;
    renderTable();
    document.getElementById('armada-search').focus();
}

/* ── SMOOTH SCROLL ── */
function smoothTo(id, e) {
    e.preventDefault();
    const el = document.getElementById(id);
    if (el) el.scrollIntoView({behavior: 'smooth', block: 'start'});
}

/* ── FORM AUTO-FILL ── */
function onKendaraanChange(select) {
    const opt = select.selectedOptions[0];
    document.getElementById('jenis_kendaraan').value = opt ? (opt.dataset.jenis || '') : '';
}

/* ── FORM SUBMIT ── */
document.getElementById('form-request').addEventListener('submit', async function (e) {
    e.preventDefault();
    const btn = document.getElementById('btn-submit-request');
    const orig = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span style="display:inline-block;width:15px;height:15px;border:2px solid rgba(255,255,255,.35);border-top-color:#fff;border-radius:50%;animation:spin .6s linear infinite;margin-right:8px;vertical-align:middle"></span> Mengirim...';

    try {
        const res = await fetch('{{ route("peminjaman.store") }}', {
            method: 'POST',
            body: new FormData(this),
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        });
        const data = await res.json();

        if (res.ok && data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Request Terkirim!',
                html: `<p style="color:#374151;font-size:.93rem;margin-bottom:12px">${data.message}</p>
                       <div style="padding:12px;background:#fffbeb;border:1px solid #fde68a;border-radius:10px;font-size:.8rem;color:#92400e;text-align:left">
                           <strong>ℹ️ Catatan:</strong> Request sedang menunggu <strong>persetujuan Manager</strong>. Anda akan dihubungi lebih lanjut.
                       </div>`,
                confirmButtonText: 'OK, Mengerti',
                confirmButtonColor: '#002a7a',
            });
            this.reset();
            document.getElementById('jenis_kendaraan').value = '';
        } else if (res.status === 422 && data.errors) {
            Swal.fire({ icon: 'warning', title: 'Data Tidak Lengkap', html: Object.values(data.errors).flat().join('<br>'), confirmButtonColor: '#002a7a' });
        } else {
            Swal.fire({ icon: 'error', title: 'Gagal Mengirim', text: data.message || 'Terjadi kesalahan sistem.', confirmButtonColor: '#002a7a' });
        }
    } catch {
        Swal.fire({ icon: 'error', title: 'Koneksi Bermasalah', text: 'Tidak dapat terhubung ke server.', confirmButtonColor: '#002a7a' });
    } finally {
        btn.disabled = false;
        btn.innerHTML = orig;
    }
});

/* ── SCROLL REVEAL ── */
const revealEls = document.querySelectorAll('.reveal');
const observer = new IntersectionObserver(entries => {
    entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('visible'); observer.unobserve(e.target); } });
}, { threshold: 0.08 });
revealEls.forEach(el => observer.observe(el));

/* ── INIT ── */
renderTable();
</script>
</body>
</html>
