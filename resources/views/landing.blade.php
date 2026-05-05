<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Open Graph -->
    <meta property="og:title" content="Vehicle Management System">
    <meta property="og:description" content="Vehicle Management System - PT. Artha Daya Coalindo">
    <meta property="og:image" content="{{ asset('images/ADC.png') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Vehicle Management System">
    <meta name="twitter:description" content="Vehicle Management System - PT. Artha Daya Coalindo">
    <meta name="twitter:image" content="{{ asset('images/ADC.png') }}">

    <title>Vehicle Management System - PT ARTHA DAYA COALINDO</title>
    @include('partials.favicon')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* ── ARMADA SECTION (ringkas; hero & navigasi di app.css agar selaras dashboard) ── */
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

        .lp-status-pill {
            display: inline-block;
            font-size: 0.72rem;
            font-weight: 700;
            padding: 5px 12px;
            border-radius: 999px;
            white-space: nowrap;
        }
        .lp-status-on { background: rgba(22, 101, 52, 0.12); color: #15803d; border: 1px solid rgba(22, 101, 52, 0.2); }
        .lp-status-maint { background: rgba(180, 83, 9, 0.1); color: #b45309; border: 1px solid rgba(180, 83, 9, 0.2); }
        .lp-status-off { background: rgba(153, 27, 27, 0.1); color: #b91c1c; border: 1px solid rgba(153, 27, 27, 0.2); }
        .dash-body.dark .lp-status-on { background: rgba(20, 83, 45, 0.62); color: #4ade80; border: 1px solid rgba(74, 222, 128, 0.38); }
        .dash-body.dark .lp-status-maint { background: rgba(120, 53, 15, 0.55); color: #fbbf24; border: 1px solid rgba(251, 191, 36, 0.38); }
        .dash-body.dark .lp-status-off { background: rgba(127, 29, 29, 0.58); color: #fca5a5; border: 1px solid rgba(248, 113, 113, 0.4); }

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
        /* Desktop: tanggal peminjaman hanya 50% lebar (kolom kiri) */
        @media (min-width: 769px) {
            .lp-form-half-desktop { grid-column: 1 / 2; }
        }

        /* ── SCROLL REVEAL ── */
        .reveal { opacity: 0; transform: translateY(22px); transition: opacity .55s ease, transform .55s ease; }
        .reveal.visible { opacity: 1; transform: none; }

        /* ── RESPONSIVE ── */
        @media (max-width: 768px) {
            .lp-form-grid { grid-template-columns: 1fr; }
            .lp-section-heading { flex-direction: column; align-items: flex-start; }
            .lp-search-wrap { max-width: 100%; width: 100%; }
        }
        @media (max-width: 480px) {
            .lp-btn-primary, .lp-btn-secondary { justify-content: center; }
        }

        /* Laporan kejadian: foto + TTG */
        .lp-lk-photo-actions { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 8px; }
        .lp-lk-photo-preview {
            margin-top: 10px; max-width: 360px; border-radius: 12px; overflow: hidden;
            border: 1px solid #e2e8f0; background: #f8fafc; display: none;
        }
        .lp-lk-photo-preview.is-on { display: block; }
        .lp-lk-photo-preview img { width: 100%; max-height: 220px; object-fit: contain; display: block; }
        .lp-lk-sig-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        @media (max-width: 768px) {
            .lp-lk-sig-row { grid-template-columns: 1fr; }
        }

        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body class="dash-body" id="landing">

    @include('partials.premium-dash-bg', ['premiumBgId' => 'dashmain'])

{{-- ══════════════════ NAVBAR (selaras dashboard + menu mobile) ══════════════════ --}}
<nav class="dash-nav lp-top-nav" id="lp-top-nav" aria-label="Navigasi utama">
    <div class="dash-nav-inner lp-nav-inner">
        <a href="#landing" onclick="smoothTo('landing',event)" class="dash-nav-brand lp-landing-brand">
            <img src="{{ asset('images/ADCPM Landscape NEW.png') }}" alt="Logo" class="dash-nav-logo logo-desktop lp-landing-logo">
            <img src="{{ asset('images/ADC PM Logo.png') }}" alt="Logo" class="dash-nav-logo logo-mobile lp-landing-logo">
            <div>
                <div class="dash-nav-title">Vehicle Management System</div>
                <span class="dash-nav-sub sub-mobile-only">PT. ARTHA DAYA COALINDO</span>
            </div>
        </a>

        <nav class="lp-nav-links-desktop" aria-label="Menu utama">
            <a href="#armada" class="lp-nav-link" onclick="smoothTo('armada',event)">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 17H3a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v6a2 2 0 01-2 2h-2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="9" cy="17" r="2" stroke="currentColor" stroke-width="2"/><circle cx="17" cy="17" r="2" stroke="currentColor" stroke-width="2"/></svg>
                Daftar Armada
            </a>
            <a href="#form-peminjaman" class="lp-nav-link" onclick="smoothTo('form-peminjaman',event)">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                Ajukan Peminjaman
            </a>
            <a href="#form-laporan-kejadian" class="lp-nav-link" onclick="smoothTo('form-laporan-kejadian',event)">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 9v4M12 17h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                Laporan Kejadian
            </a>
        </nav>

        <div class="dash-nav-actions lp-nav-actions" id="lp-nav-actions">
            <div class="lp-nav-links-mobile" aria-label="Menu utama (mobile)">
                <a href="#landing" class="lp-nav-link lp-nav-link--drawer" onclick="smoothTo('landing',event)">
                    <i class="bi bi-house-door-fill" aria-hidden="true"></i> Beranda
                </a>
                <a href="#armada" class="lp-nav-link lp-nav-link--drawer" onclick="smoothTo('armada',event)">
                    <i class="bi bi-truck-front-fill" aria-hidden="true"></i> Daftar Armada
                </a>
                <a href="#form-peminjaman" class="lp-nav-link lp-nav-link--drawer" onclick="smoothTo('form-peminjaman',event)">
                    <i class="bi bi-clipboard2-check-fill" aria-hidden="true"></i> Ajukan Peminjaman
                </a>
                <a href="#form-laporan-kejadian" class="lp-nav-link lp-nav-link--drawer" onclick="smoothTo('form-laporan-kejadian',event)">
                    <i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i> Laporan Kejadian
                </a>
            </div>

            <button type="button" class="dash-theme-btn" id="dash-theme-toggle" title="Ganti Tema" aria-label="Ganti tema terang atau gelap">
                <i class="bi bi-moon-fill" id="dash-theme-icon"></i>
                <span class="dash-theme-mode-label" id="dash-theme-label">Dark Mode</span>
            </button>

            @auth
                <a href="{{ route('dashboard') }}" class="dash-nav-btn-gold">
                    <i class="bi bi-arrow-right" aria-hidden="true"></i>
                    <span class="dash-nav-btn-label">Dashboard</span>
                </a>
            @else
                <a href="{{ route('login') }}" class="dash-nav-btn-gold">
                    <i class="bi bi-box-arrow-in-right" aria-hidden="true"></i>
                    <span class="dash-nav-btn-label">Login</span>
                </a>
            @endauth
        </div>

        <button type="button" class="dash-mobile-menu-btn lp-mobile-menu-btn" id="lp-mobile-menu-btn" aria-label="Buka menu" aria-expanded="false" aria-controls="lp-nav-actions">
            <i class="bi bi-list" id="lp-mobile-menu-icon"></i>
        </button>
    </div>
</nav>

{{-- ══════════════════ HERO (gaya dash-hero) ══════════════════ --}}
<section class="dash-hero-section lp-landing-hero lp-landing-hero--viewport">
    <div class="dash-hero-inner lp-hero-inner-landing">
        <div class="lp-hero-dash-grid">

            {{-- LEFT --}}
            <div class="lp-hero-left">
                <p class="dash-hero-kicker">
                    <span class="dash-hero-kicker-dot" aria-hidden="true"></span>
                    Sistem Armada Kendaraan
                </p>
                <h1 class="dash-hero-name lp-hero-title-large">
                    Cek Ketersediaan &amp;<br>
                    <span class="lp-hero-accent">Ajukan Peminjaman</span>
                </h1>
                <p class="lp-hero-desc">
                    Lihat daftar kendaraan operasional, kemudian ajukan permohonan peminjaman.
                </p>
                <div class="lp-hero-btns">
                    <a href="#armada" class="lp-btn-primary" onclick="smoothTo('armada',event)">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 17H3a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v6a2 2 0 01-2 2h-2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="9" cy="17" r="2" stroke="currentColor" stroke-width="2"/><circle cx="17" cy="17" r="2" stroke="currentColor" stroke-width="2"/></svg>
                        Lihat Armada
                    </a>
                    <a href="#form-peminjaman" class="lp-btn-secondary" onclick="smoothTo('form-peminjaman',event)">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        Ajukan Peminjaman
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
                        <p class="lp-feat-title">Ajukan Permohonan</p>
                        <p class="lp-feat-desc">Isi formulir online dengan detail kebutuhan peminjaman Anda.</p>
                    </div>
                </div>
                <div class="lp-feat-card">
                    <div class="lp-feat-icon lp-feat-icon-green">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" stroke="currentColor" stroke-width="2"/></svg>
                    </div>
                    <div>
                        <p class="lp-feat-title">Menunggu Persetujuan</p>
                        <p class="lp-feat-desc">Permohonan Anda akan diproses dan disetujui oleh Manager yang berwenang.</p>
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
                            <th style="width:52px">#</th>
                            <th style="width:24%">Nomor</th>
                            <th>Jenis Kendaraan</th>
                            <th>Bidang</th>
                            <th style="width:22%">Status</th>
                        </tr>
                    </thead>
                    <tbody id="armada-tbody">
                        {{-- filled by JS --}}
                    </tbody>
                </table>
            </div>
            <div class="lp-pagination" id="armada-pagination"></div>
            <p id="armada-empty" class="lp-armada-empty" style="display:none">
                Tidak ada kendaraan yang cocok dengan pencarian.
            </p>
        </div>
    </section>

    {{-- FORM SECTION --}}
    <section class="landing-section reveal" id="form-peminjaman">
        <div style="margin-bottom:20px">
            <h2 class="landing-section-title">Form Permohonan Peminjaman Kendaraan</h2>
            <p class="landing-section-sub">Isi formulir di bawah untuk mengajukan permohonan peminjaman kendaraan</p>
        </div>

        <div class="landing-card landing-form-card">
            <div class="landing-form-banner">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" style="flex-shrink:0">
                    <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    <path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                <span>Permohonan Peminjaman Kendaraan</span>
            </div>

            <form id="form-request" autocomplete="off">
                @csrf
                <div class="lp-form-grid">

                    {{-- Nama Lengkap --}}
                    <div class="checklist-field">
                        <span>Nama Lengkap <span style="color:#ef4444">*</span></span>
                        <input type="text" id="nama_lengkap" name="nama_lengkap"
                            placeholder="Masukkan nama lengkap Anda" required>
                    </div>

                    {{-- NIP --}}
                    <div class="checklist-field">
                        <span>NIP <span style="color:#ef4444">*</span></span>
                        <input type="text" id="nip" name="nip"
                            placeholder="Nomor Induk Pegawai" required>
                    </div>

                    {{-- Posisi / Jabatan --}}
                    <div class="checklist-field">
                        <span>Posisi / Jabatan <span style="color:#ef4444">*</span></span>
                        <input type="text" id="jabatan" name="jabatan"
                            placeholder="Contoh: Staff HSE, Supervisor Operasional" required>
                    </div>

                    {{-- Bidang / Bagian (samping Posisi / Jabatan di desktop) --}}
                    <div class="checklist-field">
                        <span>Bidang / Bagian <span style="color:#ef4444">*</span></span>
                        <div class="checklist-control-wrap checklist-control-select">
                            <select id="bidang_id" name="bidang_id" required>
                                <option value="">-- Pilih bidang / bagian --</option>
                                @foreach($bidangRoots as $parent)
                                    @if($parent->children->isNotEmpty())
                                        <optgroup label="{{ $parent->nama }}">
                                            @foreach($parent->children as $bd)
                                                <option value="{{ $bd->id }}">{{ $bd->nama }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Nomor Kendaraan --}}
                    <div class="checklist-field">
                        <span>No. Polisi Kendaraan <span style="color:#ef4444">*</span></span>
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

                    {{-- Jenis Kendaraan (samping No. Polisi di desktop) --}}
                    <div class="checklist-field">
                        <span>Jenis Kendaraan</span>
                        <input type="text" id="jenis_kendaraan" name="jenis_kendaraan"
                            placeholder="Terisi otomatis setelah memilih nomor polisi" readonly>
                    </div>

                    {{-- Hari / Tanggal Peminjaman (50% lebar di desktop) --}}
                    <div class="checklist-field lp-form-half-desktop">
                        <span>Hari / Tanggal Peminjaman <span style="color:#ef4444">*</span></span>
                        <input type="date" id="tanggal_peminjaman" name="tanggal_peminjaman" required
                            min="{{ date('Y-m-d') }}">
                    </div>

                    {{-- Keperluan --}}
                    <div class="checklist-field lp-form-full">
                        <span>Keperluan / Tujuan Peminjaman <span style="color:#ef4444">*</span></span>
                        <textarea id="alasan" name="alasan" rows="4"
                            placeholder="Jelaskan keperluan atau tujuan peminjaman kendaraan..." required></textarea>
                    </div>

                    {{-- Pernyataan --}}
                    <div class="checklist-field lp-form-full" style="margin-top:4px">
                        <div class="lp-info-callout">
                            <p>{{ $pernyataanPengantar }}</p>
                            @if($pernyataans->isNotEmpty())
                                <ol>
                                    @foreach($pernyataans as $p)
                                        <li>{{ $p->isi_pernyataan }}</li>
                                    @endforeach
                                </ol>
                            @endif
                        </div>
                    </div>

                    {{-- Tanda Tangan --}}
                    <div class="checklist-field lp-form-full">
                        <span>Tanda Tangan Pemohon <span style="color:#ef4444">*</span></span>
                        <div style="max-width:380px;margin-top:6px">
                            <div class="signature-pad-wrap" style="height:140px">
                                <canvas id="sig-pad-peminjaman" class="signature-canvas" style="height:120px"></canvas>
                                <div class="signature-pad-hint" id="sig-hint-peminjaman">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                                        <path d="M17 3a2.83 2.83 0 114 4L7.5 20.5 2 22l1.5-5.5L17 3z" stroke="currentColor" stroke-width="2"/>
                                    </svg>
                                    <span>TAP UNTUK TANDA TANGAN</span>
                                </div>
                            </div>
                            <button type="button" id="sig-clear-peminjaman" class="signature-clear-btn">
                                &#x2715; Hapus Tanda Tangan
                            </button>
                        </div>
                        <input type="hidden" name="tanda_tangan" id="sig-data-peminjaman">
                    </div>

                </div>

                <div class="landing-form-note" style="margin-top:12px">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" style="flex-shrink:0;color:#2563eb">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                        <path d="M12 8v4M12 16h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    <span>Permohonan Anda akan dikirim ke Manager untuk mendapatkan persetujuan. Harap menunggu konfirmasi lebih lanjut.</span>
                </div>

                <button type="submit" class="landing-submit-btn" id="btn-submit-request" style="margin-top:14px">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                    <path d="M14 2H6A2 2 0 0 0 4 4V20A2 2 0 0 0 6 22H18A2 2 0 0 0 20 20V8L14 2ZM14 2V8H20M12 18V12M12 12L9 15M12 12L15 15"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Kirim Permohonan Peminjaman
                </button>
            </form>
        </div>
    </section>

    {{-- LAPORAN KEJADIAN --}}
    <section class="landing-section reveal" id="form-laporan-kejadian">
        <div style="margin-bottom:20px">
            <h2 class="landing-section-title">Laporan Kejadian</h2>
            <p class="landing-section-sub">Laporkan incident atau near miss terkait operasi kendaraan / lingkungan kerja</p>
        </div>

        <div class="landing-card landing-form-card">
            <div class="landing-form-banner">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 9v4M12 17h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                <span>Formulir Laporan Kejadian</span>
            </div>

            <form id="form-laporan-kejadian-form" autocomplete="off" enctype="multipart/form-data">
                @csrf
                <div class="lp-form-grid">

                    <div class="checklist-field">
                        <span>Nama <span style="color:#ef4444">*</span></span>
                        <input type="text" name="nama" id="lk_nama" placeholder="Nama lengkap pelapor" required>
                    </div>
                    <div class="checklist-field">
                        <span>NIP <span style="color:#ef4444">*</span></span>
                        <input type="text" name="nip" id="lk_nip" placeholder="Nomor Induk Pegawai" required>
                    </div>

                    <div class="checklist-field">
                        <span>Posisi / Jabatan <span style="color:#ef4444">*</span></span>
                        <input type="text" name="jabatan" id="lk_jabatan" placeholder="Contoh: Staff HSE" required>
                    </div>

                    <div class="checklist-field">
                        <span>Bidang / Bagian <span style="color:#ef4444">*</span></span>
                        <div class="checklist-control-wrap checklist-control-select">
                            <select id="lk_bidang_id" name="bidang_id" required>
                                <option value="">-- Pilih bidang / bagian --</option>
                                @foreach($bidangRoots as $parent)
                                    @if($parent->children->isNotEmpty())
                                        <optgroup label="{{ $parent->nama }}">
                                            @foreach($parent->children as $bd)
                                                <option value="{{ $bd->id }}">{{ $bd->nama }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="checklist-field">
                        <span>Tanggal &amp; Waktu Kejadian <span style="color:#ef4444">*</span></span>
                        <input type="datetime-local" name="waktu_kejadian" id="lk_waktu" required>
                    </div>

                    <div class="checklist-field">
                        <span>Kategori <span style="color:#ef4444">*</span></span>
                        <div class="checklist-control-wrap checklist-control-select">
                            <select name="kategori" id="lk_kategori" required>
                                <option value="">-- Pilih Kategori --</option>
                                <option value="Incident">Incident</option>
                                <option value="Nearmiss">Nearmiss</option>
                            </select>
                        </div>
                    </div>

                    <div class="checklist-field lp-form-full">
                        <span>Lokasi Kejadian <span style="color:#ef4444">*</span></span>
                        <input type="text" name="lokasi_kejadian" id="lk_lokasi" placeholder="Lokasi kejadian" required>
                    </div>

                    <div class="checklist-field">
                        <span>No. Polisi Kendaraan <span style="color:#ef4444">*</span></span>
                        <div class="checklist-control-wrap checklist-control-select">
                            <select id="lk_nomor_kendaraan" name="nomor_kendaraan" required onchange="onLkKendaraanChange(this)">
                                <option value="">-- Pilih Nomor Polisi --</option>
                                @foreach($kendaraans as $k)
                                    <option value="{{ $k->nomor_kendaraan }}" data-jenis="{{ $k->jenis_kendaraan }}">{{ $k->nomor_kendaraan }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="checklist-field">
                        <span>Jenis Kendaraan</span>
                        <input type="text" id="lk_jenis_kendaraan" name="jenis_kendaraan"
                            placeholder="Terisi otomatis" readonly>
                    </div>

                    <div class="checklist-field lp-form-full">
                        <span>Peristiwa <span style="color:#ef4444">*</span></span>
                        <textarea name="peristiwa" id="lk_peristiwa" rows="3" required placeholder="Ringkas peristiwa yang dilaporkan"></textarea>
                    </div>
                    <div class="checklist-field lp-form-full">
                        <span>Sebelum Kejadian <span style="color:#ef4444">*</span></span>
                        <textarea name="sebelum_kejadian" id="lk_sebelum" rows="3" required placeholder="Kondisi / aktivitas sebelum kejadian"></textarea>
                    </div>
                    <div class="checklist-field lp-form-full">
                        <span>Kejadian <span style="color:#ef4444">*</span></span>
                        <textarea name="uraian_kejadian" id="lk_kejadian" rows="4" required placeholder="Uraian kejadian secara berurutan"></textarea>
                    </div>

                    <div class="checklist-field lp-form-full">
                        <span>Gambar / Foto <span style="color:#ef4444">*</span></span>
                            <input type="file" name="foto" id="lk_foto" accept="image/*" required class="checklist-file-input" style="display:none">
                        <div class="lp-lk-photo-actions">
                            <button type="button" class="lp-landing-file-btn" id="lk_btn_foto">Upload foto kejadian</button>
                        </div>
                        <div class="lp-lk-photo-preview" id="lk_foto_preview_wrap">
                            <img src="" alt="Pratinjau" id="lk_foto_preview_img">
                        </div>
                    </div>

                    <div class="checklist-field lp-form-full">
                        <span>Akibat dari Kejadian <span style="color:#ef4444">*</span></span>
                        <textarea name="akibat" id="lk_akibat" rows="3" required placeholder="Dampak atau akibat yang timbul"></textarea>
                    </div>

                    <div class="checklist-field lp-form-full lp-lk-sig-row">
                        <div>
                            <span>TTD Manager (Bidang / Bagian) <span style="color:#ef4444">*</span></span>
                            <div style="max-width:100%;margin-top:6px">
                                <div class="signature-pad-wrap" style="height:140px">
                                    <canvas id="sig-pad-lk-manager" class="signature-canvas" style="height:120px"></canvas>
                                    <div class="signature-pad-hint" id="sig-hint-lk-manager">
                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                                            <path d="M17 3a2.83 2.83 0 114 4L7.5 20.5 2 22l1.5-5.5L17 3z" stroke="currentColor" stroke-width="2"/>
                                        </svg>
                                        <span>TANDA TANGAN MANAGER</span>
                                    </div>
                                </div>
                                <button type="button" id="sig-clear-lk-manager" class="signature-clear-btn">&#x2715; Hapus</button>
                            </div>
                            <input type="hidden" name="ttd_manager" id="sig-data-lk-manager">
                        </div>
                        <div>
                            <span>TTD Pelapor <span style="color:#ef4444">*</span></span>
                            <div style="max-width:100%;margin-top:6px">
                                <div class="signature-pad-wrap" style="height:140px">
                                    <canvas id="sig-pad-lk-pelapor" class="signature-canvas" style="height:120px"></canvas>
                                    <div class="signature-pad-hint" id="sig-hint-lk-pelapor">
                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                                            <path d="M17 3a2.83 2.83 0 114 4L7.5 20.5 2 22l1.5-5.5L17 3z" stroke="currentColor" stroke-width="2"/>
                                        </svg>
                                        <span>TANDA TANGAN PELAPOR</span>
                                    </div>
                                </div>
                                <button type="button" id="sig-clear-lk-pelapor" class="signature-clear-btn">&#x2715; Hapus</button>
                            </div>
                            <input type="hidden" name="ttd_pelapor" id="sig-data-lk-pelapor">
                        </div>
                    </div>
                </div>

                <div class="landing-form-note" style="margin-top:12px">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" style="flex-shrink:0;color:#2563eb">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                        <path d="M12 8v4M12 16h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    <span>Pastikan keseluruhan data sudah benar sebelum mengirim.</span>
                </div>

                <button type="submit" class="landing-submit-btn" id="btn-submit-laporan" style="margin-top:14px">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                        <path d="M22 2L11 13M22 2L15 22L11 13M22 2L2 9L11 13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Kirim Laporan
                </button>
            </form>
        </div>
    </section>

</div>

{{-- FOOTER --}}
<footer class="landing-footer">
    <div class="landing-container">
        <div class="landing-footer-inner">
            <p class="landing-footer-copy">&copy; {{ date('Y') }} Port Management Unit Suralaya</p>
        </div>
    </div>
</footer>

{{-- ══════════════════ SCRIPTS ══════════════════ --}}
<script>
/* ── Tema (selaras dashboard / login) ── */
(function () {
    const body = document.body;
    const icon = document.getElementById('dash-theme-icon');
    const btn = document.getElementById('dash-theme-toggle');
    const label = document.getElementById('dash-theme-label');

    function applyTheme(isDark) {
        body.classList.toggle('dark', isDark);
        if (icon) icon.className = isDark ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
        if (label) label.textContent = isDark ? 'Light Mode' : 'Dark Mode';
    }

    const saved = localStorage.getItem('vms-theme') || localStorage.getItem('vms-dash-theme');
    applyTheme(saved === 'dark');

    if (btn) {
        btn.addEventListener('click', function () {
            const next = !body.classList.contains('dark');
            applyTheme(next);
            localStorage.setItem('vms-theme', next ? 'dark' : 'light');
            localStorage.setItem('vms-dash-theme', next ? 'dark' : 'light');
        });
    }
})();

/** SweetAlert2 — warna popup mengikuti tema light/dark landing */
function landingSwalOpts(opts) {
    const dark = document.body.classList.contains('dark');
    const base = {
        background: dark ? '#1e293b' : '#ffffff',
        color: dark ? '#f1f5f9' : '#0f172a',
        confirmButtonColor: '#0A2342',
        cancelButtonColor: dark ? '#475569' : '#94a3b8',
        customClass: { popup: 'lp-swal-popup' },
    };
    return Object.assign({}, base, opts || {});
}

/* ── Menu mobile navbar ── */
(function () {
    const menuBtn = document.getElementById('lp-mobile-menu-btn');
    const navActions = document.getElementById('lp-nav-actions');
    const menuIcon = document.getElementById('lp-mobile-menu-icon');

    if (!menuBtn || !navActions) return;

    function closeMenu() {
        navActions.classList.remove('mobile-open');
        if (menuIcon) menuIcon.className = 'bi bi-list';
        menuBtn.setAttribute('aria-expanded', 'false');
    }

    menuBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        const isOpen = navActions.classList.toggle('mobile-open');
        if (menuIcon) menuIcon.className = isOpen ? 'bi bi-x-lg' : 'bi bi-list';
        menuBtn.setAttribute('aria-expanded', String(isOpen));
    });

    document.addEventListener('click', function (e) {
        if (!navActions.contains(e.target) && !menuBtn.contains(e.target)) closeMenu();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeMenu();
    });
})();

/* ── DATA ── */
const KENDARAANS = @json($kendaraans);
const PER_PAGE   = 10;
let currentPage  = 1;
let filtered     = [...KENDARAANS];

function lpStatusPillHtml(st) {
    const raw = st != null && String(st).trim() !== '' ? String(st).trim() : '';
    if (!raw) {
        return '<span style="color:#94a3b8">—</span>';
    }
    const norm = raw.toLowerCase();
    let label = raw;
    let cls = 'lp-status-pill lp-status-on';
    if (norm === 'maintenance') {
        label = 'Maintenance';
        cls = 'lp-status-pill lp-status-maint';
    } else if (norm === 'non aktif') {
        label = 'Non Aktif';
        cls = 'lp-status-pill lp-status-off';
    } else if (norm === 'aktif') {
        label = 'Aktif';
    }
    return `<span class="${cls}">${escHtml(label)}</span>`;
}

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
            <td>${lpStatusPillHtml(k.status_kendaraan)}</td>
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
            (k.bidang && k.bidang.toLowerCase().includes(q)) ||
            (k.status_kendaraan && k.status_kendaraan.toLowerCase().includes(q))
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

    const navActions = document.getElementById('lp-nav-actions');
    const menuBtn = document.getElementById('lp-mobile-menu-btn');
    const menuIcon = document.getElementById('lp-mobile-menu-icon');
    if (navActions && navActions.classList.contains('mobile-open')) {
        navActions.classList.remove('mobile-open');
        if (menuIcon) menuIcon.className = 'bi bi-list';
        if (menuBtn) menuBtn.setAttribute('aria-expanded', 'false');
    }
}

/* ── SIGNATURE PAD ── */
let _sigPad = null;

function initSigPad() {
    const canvas = document.getElementById('sig-pad-peminjaman');
    if (!canvas || !window.SignaturePad) return;

    const hint    = document.getElementById('sig-hint-peminjaman');
    const clearBtn = document.getElementById('sig-clear-peminjaman');
    const dataIn  = document.getElementById('sig-data-peminjaman');

    const resize = () => {
        const rect = canvas.getBoundingClientRect();
        if (!rect.width || !rect.height) return false;
        const r = Math.max(window.devicePixelRatio || 1, 1);
        canvas.width  = rect.width  * r;
        canvas.height = rect.height * r;
        const ctx = canvas.getContext('2d');
        ctx.setTransform(1, 0, 0, 1, 0, 0);
        ctx.scale(r, r);
        return true;
    };

    resize();
    _sigPad = new window.SignaturePad(canvas, {
        backgroundColor: 'rgba(255,255,255,0)',
        penColor: '#0f172a',
        minWidth: 1.5,
        maxWidth: 3
    });

    _sigPad.addEventListener('beginStroke', () => {
        if (hint) hint.classList.add('hidden');
    });

    if (clearBtn) {
        clearBtn.addEventListener('click', () => {
            _sigPad.clear();
            if (hint) hint.classList.remove('hidden');
            if (dataIn) dataIn.value = '';
        });
    }

    let rt;
    window.addEventListener('resize', () => {
        clearTimeout(rt);
        rt = setTimeout(() => {
            const data = _sigPad.isEmpty() ? [] : _sigPad.toData();
            resize();
            _sigPad.clear();
            if (data.length) _sigPad.fromData(data);
            else if (hint) hint.classList.remove('hidden');
        }, 200);
    });
}

/* Wait for app.js to expose SignaturePad, then init */
(function tryInit(attempts) {
    if (window.SignaturePad) { initSigPad(); return; }
    if (attempts > 0) setTimeout(() => tryInit(attempts - 1), 150);
})(20);

/* ── FORM AUTO-FILL ── */
function onKendaraanChange(select) {
    const opt = select.selectedOptions[0];
    document.getElementById('jenis_kendaraan').value = opt ? (opt.dataset.jenis || '') : '';
}

/* ── FORM SUBMIT ── */
document.getElementById('form-request').addEventListener('submit', async function (e) {
    e.preventDefault();

    /* Capture signature before sending */
    const dataIn = document.getElementById('sig-data-peminjaman');
    if (_sigPad) {
        if (_sigPad.isEmpty()) {
            Swal.fire(landingSwalOpts({ icon: 'warning', title: 'Tanda Tangan Kosong', text: 'Mohon berikan tanda tangan Anda sebelum mengirim.' }));
            return;
        }
        dataIn.value = _sigPad.toDataURL('image/png');
    }

    const confirmSend = await Swal.fire(landingSwalOpts({
        title: 'Kirim permohonan peminjaman?',
        text: 'Pastikan data sudah benar. Permohonan akan dikirim ke Manager untuk persetujuan.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, kirim',
        cancelButtonText: 'Batal',
    }));
    if (!confirmSend.isConfirmed) return;

    const btn  = document.getElementById('btn-submit-request');
    const orig = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span style="display:inline-block;width:15px;height:15px;border:2px solid rgba(255,255,255,.35);border-top-color:#fff;border-radius:50%;animation:spin .6s linear infinite;margin-right:8px;vertical-align:middle"></span> Mengirim...';

    try {
        const res  = await fetch('{{ route("peminjaman.store") }}', {
            method: 'POST',
            body: new FormData(this),
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        });
        const data = await res.json();

        if (res.ok && data.success) {
            await Swal.fire(landingSwalOpts({
                icon: 'success',
                title: 'Permohonan Terkirim!',
                html: '<div class="lp-swal-note"><strong>Catatan:</strong> Permohonan sedang menunggu <strong>persetujuan Manager</strong>. Anda akan dihubungi oleh Administrator lebih lanjut.</div>',
                confirmButtonText: 'OK, Mengerti',
            }));
            this.reset();
            document.getElementById('jenis_kendaraan').value = '';
            if (_sigPad) {
                _sigPad.clear();
                const hint = document.getElementById('sig-hint-peminjaman');
                if (hint) hint.classList.remove('hidden');
                dataIn.value = '';
            }
        } else if (res.status === 422 && data.errors) {
            Swal.fire(landingSwalOpts({ icon: 'warning', title: 'Data Tidak Lengkap', html: Object.values(data.errors).flat().join('<br>') }));
        } else {
            Swal.fire(landingSwalOpts({ icon: 'error', title: 'Gagal Mengirim', text: data.message || 'Terjadi kesalahan sistem.' }));
        }
    } catch {
        Swal.fire(landingSwalOpts({ icon: 'error', title: 'Koneksi Bermasalah', text: 'Tidak dapat terhubung ke server.' }));
    } finally {
        btn.disabled = false;
        btn.innerHTML = orig;
    }
});

/* ── LAPORAN KEJADIAN: kendaraan ── */
function onLkKendaraanChange(select) {
    const opt = select.selectedOptions[0];
    const j = document.getElementById('lk_jenis_kendaraan');
    if (j) j.value = opt ? (opt.dataset.jenis || '') : '';
}

/* ── LAPORAN KEJADIAN: foto (satu tombol → input file + kamera di perangkat mendukung) ── */
(function setupLkFoto() {
    const input = document.getElementById('lk_foto');
    const btn = document.getElementById('lk_btn_foto');
    const prevWrap = document.getElementById('lk_foto_preview_wrap');
    const prevImg = document.getElementById('lk_foto_preview_img');
    if (!input || !prevWrap || !prevImg) return;

    function showPreview(file) {
        if (!file || !file.type.startsWith('image/')) return;
        prevImg.src = URL.createObjectURL(file);
        prevWrap.classList.add('is-on');
    }

    input.addEventListener('change', function () {
        const f = this.files && this.files[0];
        if (f) showPreview(f);
        else {
            prevImg.src = '';
            prevWrap.classList.remove('is-on');
        }
    });
    if (btn) btn.addEventListener('click', () => input.click());
})();

/* ── LAPORAN KEJADIAN: signature pads ── */
const _lkSigPads = [];

function initLkSigPads() {
    if (!window.SignaturePad) return;
    const configs = [
        { canvas: 'sig-pad-lk-manager', hint: 'sig-hint-lk-manager', clear: 'sig-clear-lk-manager', hidden: 'sig-data-lk-manager' },
        { canvas: 'sig-pad-lk-pelapor', hint: 'sig-hint-lk-pelapor', clear: 'sig-clear-lk-pelapor', hidden: 'sig-data-lk-pelapor' },
    ];

    const pads = [];

    function bindOne(cfg) {
        const canvas = document.getElementById(cfg.canvas);
        if (!canvas) return;
        const hint = document.getElementById(cfg.hint);
        const clearBtn = document.getElementById(cfg.clear);
        const dataIn = document.getElementById(cfg.hidden);

        const resize = () => {
            const rect = canvas.getBoundingClientRect();
            if (!rect.width || !rect.height) return false;
            const r = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = rect.width * r;
            canvas.height = rect.height * r;
            const ctx = canvas.getContext('2d');
            ctx.setTransform(1, 0, 0, 1, 0, 0);
            ctx.scale(r, r);
            return true;
        };

        resize();
        const pad = new window.SignaturePad(canvas, {
            backgroundColor: 'rgba(255,255,255,0)',
            penColor: '#0f172a',
            minWidth: 1.5,
            maxWidth: 3,
        });

        pad.addEventListener('beginStroke', () => { if (hint) hint.classList.add('hidden'); });

        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                pad.clear();
                if (hint) hint.classList.remove('hidden');
                if (dataIn) dataIn.value = '';
            });
        }

        pads.push({ pad, canvas, hint, resize });
    }

    configs.forEach(bindOne);

    let rt;
    window.addEventListener('resize', () => {
        clearTimeout(rt);
        rt = setTimeout(() => {
            pads.forEach(({ pad, canvas, hint, resize }) => {
                const data = pad.isEmpty() ? [] : pad.toData();
                resize();
                pad.clear();
                if (data.length) pad.fromData(data);
                else if (hint) hint.classList.remove('hidden');
            });
        }, 200);
    });

    _lkSigPads.length = 0;
    pads.forEach(p => _lkSigPads.push(p.pad));
}

(function tryInitLkPads(attempts) {
    if (window.SignaturePad && document.getElementById('sig-pad-lk-manager')) {
        initLkSigPads();
        return;
    }
    if (attempts > 0) setTimeout(() => tryInitLkPads(attempts - 1), 150);
})(25);

/* ── LAPORAN KEJADIAN: submit ── */
const formLk = document.getElementById('form-laporan-kejadian-form');
if (formLk) {
    formLk.addEventListener('submit', async function (e) {
        e.preventDefault();

        const hM = document.getElementById('sig-data-lk-manager');
        const hP = document.getElementById('sig-data-lk-pelapor');
        if (_lkSigPads.length >= 2) {
            if (_lkSigPads[0].isEmpty()) {
                Swal.fire(landingSwalOpts({ icon: 'warning', title: 'TTD Manager Kosong', text: 'Mohon tanda tangan Manager (Bidang/Bagian).' }));
                return;
            }
            if (_lkSigPads[1].isEmpty()) {
                Swal.fire(landingSwalOpts({ icon: 'warning', title: 'TTD Pelapor Kosong', text: 'Mohon tanda tangan Pelapor.' }));
                return;
            }
            hM.value = _lkSigPads[0].toDataURL('image/png');
            hP.value = _lkSigPads[1].toDataURL('image/png');
        }

        const fotoEl = document.getElementById('lk_foto');
        if (!fotoEl || !fotoEl.files || !fotoEl.files[0]) {
            Swal.fire(landingSwalOpts({ icon: 'warning', title: 'Foto wajib', text: 'Mohon unggah atau ambil foto kejadian terlebih dahulu.' }));
            return;
        }

        const confirm = await Swal.fire(landingSwalOpts({
            title: 'Kirim laporan kejadian?',
            text: 'Data akan disimpan dan PDF laporan dibuat. Lanjutkan?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, kirim',
            cancelButtonText: 'Batal',
        }));
        if (!confirm.isConfirmed) return;

        const btn = document.getElementById('btn-submit-laporan');
        const orig = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span style="display:inline-block;width:15px;height:15px;border:2px solid rgba(255,255,255,.35);border-top-color:#fff;border-radius:50%;animation:spin .6s linear infinite;margin-right:8px;vertical-align:middle"></span> Mengirim...';

        try {
            const res = await fetch('{{ route("laporan-kejadian.store") }}', {
                method: 'POST',
                body: new FormData(this),
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            });
            const data = await res.json().catch(() => ({}));

            if (res.ok && data.success) {
                await Swal.fire(landingSwalOpts({
                    icon: 'success',
                    title: 'Laporan Terkirim',
                    text: 'Laporan kejadian berhasil dikirim dan PDF telah dibuat.',
                }));
                this.reset();
                document.getElementById('lk_jenis_kendaraan').value = '';
                const prevWrap = document.getElementById('lk_foto_preview_wrap');
                const prevImg = document.getElementById('lk_foto_preview_img');
                const fotoIn = document.getElementById('lk_foto');
                if (prevImg) prevImg.src = '';
                if (prevWrap) prevWrap.classList.remove('is-on');
                if (fotoIn) fotoIn.value = '';
                _lkSigPads.forEach((pad, i) => {
                    pad.clear();
                    const hint = document.getElementById(i === 0 ? 'sig-hint-lk-manager' : 'sig-hint-lk-pelapor');
                    if (hint) hint.classList.remove('hidden');
                });
                if (hM) hM.value = '';
                if (hP) hP.value = '';
            } else if (res.status === 422 && data.errors) {
                Swal.fire(landingSwalOpts({ icon: 'warning', title: 'Data Tidak Valid', html: Object.values(data.errors).flat().join('<br>') }));
            } else {
                Swal.fire(landingSwalOpts({ icon: 'error', title: 'Gagal Mengirim', text: data.message || 'Terjadi kesalahan sistem.' }));
            }
        } catch {
            Swal.fire(landingSwalOpts({ icon: 'error', title: 'Koneksi Bermasalah', text: 'Tidak dapat terhubung ke server.' }));
        } finally {
            btn.disabled = false;
            btn.innerHTML = orig;
        }
    });
}

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
