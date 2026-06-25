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
    @include('partials.theme-init')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        
        .lp-nav-inner-button {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .lp-section-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }
        .lp-section-heading .landing-section-sub {
            margin: 0;
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
        html.dark .dash-body .lp-status-on { background: rgba(20, 83, 45, 0.62); color: #4ade80; border: 1px solid rgba(74, 222, 128, 0.38); }
        html.dark .dash-body .lp-status-maint { background: rgba(120, 53, 15, 0.55); color: #fbbf24; border: 1px solid rgba(251, 191, 36, 0.38); }
        html.dark .dash-body .lp-status-off { background: rgba(127, 29, 29, 0.58); color: #fca5a5; border: 1px solid rgba(248, 113, 113, 0.4); }

        .lp-armada-controls {
            display: flex;
            align-items: center;
            gap: 12px;
            flex: 1;
            justify-content: flex-end;
            flex-wrap: wrap;
        }

        /* ── FORM SECTION ── */
        .dash-body input[type="date"]::-webkit-calendar-picker-indicator { filter: none; }
        html.dark .dash-body input[type="date"]::-webkit-calendar-picker-indicator { filter: brightness(0) invert(1); }

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

        /* ── RESPONSIVE ── */
        @media (max-width: 768px) {
            .lp-form-grid { grid-template-columns: 1fr; }
            .lp-section-heading { flex-direction: column; align-items: flex-start; }
            .lp-armada-controls { width: 100%; justify-content: stretch; }
            .lp-search-wrap { max-width: 100%; width: 100%; }
        }
        @media (max-width: 480px) {
            .lp-btn-primary, .lp-btn-secondary { justify-content: center; }
        }

        /* Laporan kejadian: multi foto + penjelasan per foto */
        .lp-lk-gambar-hint { font-size: 0.82rem; color: #64748b; margin: 4px 0 10px; line-height: 1.45; }
        html.dark .dash-body .lp-lk-gambar-hint { color: #94a3b8; }
        .lp-lk-gambar-list { display: flex; flex-direction: column; gap: 14px; margin-top: 6px; }
        .lp-lk-gambar-item {
            border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px 14px;
            background: #f8fafc;
        }
        html.dark .dash-body .lp-lk-gambar-item { border-color: #334155; background: rgba(30,41,59,.5); }
        .lp-lk-gambar-item-top {
            display: flex; align-items: center; justify-content: space-between; gap: 8px;
            margin-bottom: 8px;
        }
        .lp-lk-gambar-item-label { font-size: 0.8rem; font-weight: 700; color: #334155; letter-spacing: .02em; }
        html.dark .dash-body .lp-lk-gambar-item-label { color: #cbd5e1; }
        .lp-lk-remove-gambar {
            padding: 6px 12px; font-size: 0.78rem; font-weight: 600; cursor: pointer;
            border-radius: 8px; border: 1px solid #fecaca; background: #fff; color: #b91c1c;
        }
        .lp-lk-remove-gambar:hover { background: #fef2f2; }
        html.dark .dash-body .lp-lk-remove-gambar { background: rgba(30,41,59,.8); border-color: #7f1d1d; color: #fca5a5; }
        .lp-lk-photo-actions { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px; }
        .lp-landing-file-btn--secondary {
            background: transparent !important; color: var(--dash-blue, #0A2342) !important;
            border: 1px solid #c7d2fe !important; box-shadow: none !important;
        }
        html.dark .dash-body .lp-landing-file-btn--secondary {
            color: #93c5fd !important; border-color: #475569 !important;
        }
        .lp-lk-photo-preview {
            margin-top: 10px; max-width: 420px; border-radius: 12px; overflow: hidden;
            border: 1px solid #e2e8f0; background: #f8fafc; display: none;
        }
        .lp-lk-photo-preview.is-on { display: block; }
        .lp-lk-photo-preview img { width: 100%; max-height: 220px; object-fit: contain; display: block; }
        .lp-lk-gambar-item .checklist-field { margin-top: 8px; }
        .lp-lk-gambar-item .checklist-field span { font-size: 0.82rem; }
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

{{-- ══════════════════ NAVBAR ══════════════════ --}}
<nav class="dash-nav lp-top-nav" id="lp-top-nav" aria-label="Navigasi utama">
    <div class="dash-nav-inner lp-nav-inner">
        <a href="#landing" onclick="smoothTo('landing',event)" class="dash-nav-brand lp-landing-brand">
            <img src="{{ asset('images/ADCPM Landscape NEW.png') }}" alt="Logo" class="dash-nav-logo logo-desktop lp-landing-logo" fetchpriority="high">
            <img src="{{ asset('images/ADC PM Logo.png') }}" alt="Logo" class="dash-nav-logo logo-mobile lp-landing-logo" fetchpriority="high">
            <div>
                <!-- <div class="dash-nav-title">Vehicle Management System</div> -->
                <span class="dash-nav-title sub-mobile-only">PT. ARTHA DAYA COALINDO</span>
            </div>
        </a>

        <nav class="lp-nav-links-desktop" aria-label="Menu utama">
            <a href="#armada" class="lp-nav-link" onclick="smoothTo('armada',event)">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 17H3a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v6a2 2 0 01-2 2h-2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="9" cy="17" r="2" stroke="currentColor" stroke-width="2"/><circle cx="17" cy="17" r="2" stroke="currentColor" stroke-width="2"/></svg>
                Daftar Kendaraan
            </a>
            <a href="#form-peminjaman" class="lp-nav-link" onclick="smoothTo('form-peminjaman',event)">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                Peminjaman Kendaraan
            </a>
            <a href="#form-laporan-kejadian" class="lp-nav-link" onclick="smoothTo('form-laporan-kejadian',event)">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 9v4M12 17h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                Laporan Kejadian &amp; Kerusakan
            </a>
        </nav>
        
        <div class="lp-nav-inner-button">
            <button type="button" class="dash-theme-btn" id="dash-theme-toggle" title="Ganti Tema" aria-label="Ganti tema terang atau gelap">
                <i class="bi bi-moon-fill" id="dash-theme-icon"></i>
            </button>
            <button type="button" class="dash-mobile-menu-btn lp-mobile-menu-btn" id="lp-mobile-menu-btn" aria-label="Buka menu" aria-expanded="false" aria-controls="lp-nav-actions">
                <i class="bi bi-list" id="lp-mobile-menu-icon"></i>
            </button>
        </div>

        <div class="dash-nav-actions lp-nav-actions" id="lp-nav-actions">
            <div class="lp-nav-links-mobile" aria-label="Menu utama (mobile)">
                <a href="#landing" class="lp-nav-link lp-nav-link--drawer" onclick="smoothTo('landing',event)">
                    <i class="bi bi-house-door-fill" aria-hidden="true"></i> Beranda
                </a>
                <a href="#armada" class="lp-nav-link lp-nav-link--drawer" onclick="smoothTo('armada',event)">
                    <i class="bi bi-truck-front-fill" aria-hidden="true"></i> Daftar Kendaraan
                </a>
                <a href="#form-peminjaman" class="lp-nav-link lp-nav-link--drawer" onclick="smoothTo('form-peminjaman',event)">
                    <i class="bi bi-clipboard2-check-fill" aria-hidden="true"></i> Peminjaman Kendaraan
                </a>
                <a href="#form-laporan-kejadian" class="lp-nav-link lp-nav-link--drawer" onclick="smoothTo('form-laporan-kejadian',event)">
                    <i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i> Laporan Kejadian
                </a>
            </div>

            
            @auth
            <a href="{{ route('dashboard') }}" class="dash-nav-btn-gold">
                <i class="bi bi-arrow-right" aria-hidden="true"></i>
                <span class="dash-nav-btn-label">Dashboard</span>
            </a>
            @else
            <a href="{{ route('login') }}" class="dash-nav-btn-gold" data-turbo="false">
                <i class="bi bi-box-arrow-in-right" aria-hidden="true"></i>
                <span class="dash-nav-btn-label">Login</span>
            </a>
            @endauth
        </div>
    </div>
</nav>

{{-- ══════════════════ HERO ══════════════════ --}}
<section class="dash-hero-section lp-landing-hero lp-landing-hero--viewport">
    <div class="dash-hero-inner lp-hero-inner-landing">
        <div class="lp-hero-dash-grid">
            {{-- LEFT --}}
            <div class="lp-hero-left">
                <img src="{{ asset('images/hero_img.png') }}" alt="Hero Image" class="lp-hero-image">
            </div>

            {{-- RIGHT: Feature cards --}}
            <div class="lp-hero-right">
                <a href="#armada" onclick="smoothTo('armada',event)" class="lp-feat-card">
                    <div class="lp-feat-icon lp-feat-icon-yellow">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/><path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    </div>
                    <div>
                        <p class="lp-feat-title">Daftar Unit Kendaraan</p>
                        <p class="lp-feat-desc">Lihat seluruh unit kendaraan operasional yang terdaftar secara real-time.</p>
                    </div>
                </a>
                <a href="#form-peminjaman" onclick="smoothTo('form-peminjaman',event)" class="lp-feat-card">
                    <div class="lp-feat-icon lp-feat-icon-blue">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M12 12h.01M12 16h.01M8 12h.01M8 16h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    </div>
                    <div>
                        <p class="lp-feat-title">Peminjaman Kendaraan</p>
                        <p class="lp-feat-desc">Isi formulir online dengan detail kebutuhan peminjaman Anda.</p>
                    </div>
                </a>
                <a href="#form-laporan-kejadian" onclick="smoothTo('form-laporan-kejadian',event)" class="lp-feat-card">
                    <div class="lp-feat-icon lp-feat-icon-red">
                        <i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i> 
                    </div>
                    <div>
                        <p class="lp-feat-title">Laporkan Kejadian & Kerusakan</p>
                        <p class="lp-feat-desc">Laporkan kendala, insiden, atau kerusakan kendaraan operasional.</p>
                    </div>
                </a>
            </div>

        </div>
    </div>
</section>

{{-- ══════════════════ CONTENT ══════════════════ --}}
<div class="landing-container">

    {{-- ARMADA SECTION --}}
    <section class="landing-section" id="armada">
        
        <div class="landing-card">
            <div class="lp-section-heading">
                <div>
                    <h2 class="landing-section-title">Daftar Kendaraan</h2>
                    <p class="landing-section-sub">Total <span id="armada-count">{{ $kendaraans->count() }}</span> kendaraan terdaftar</p>
                </div>
                <div class="lp-armada-controls">
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
            </div>
            <div class="admin-table-wrap">
                <table class="admin-table" style="table-layout:fixed">
                    <thead>
                        <tr>
                            <th style="width:52px; border-top-left-radius: 10px;">#</th>
                            <th style="width:24%">Nomor</th>
                            <th>Jenis Kendaraan</th>
                            <th>Bidang</th>
                            <th style="width:22%; border-top-right-radius: 10px;">Status</th>
                        </tr>
                    </thead>
                    <tbody id="armada-tbody">
                        {{-- filled by JS --}}
                    </tbody>
                </table>
            </div>
            <div id="armada-pagination"></div>
            <p id="armada-empty" class="lp-armada-empty" style="display:none">
                Tidak ada kendaraan yang cocok dengan pencarian.
            </p>
        </div>
    </section>

    {{-- FORM SECTION --}}
    <section class="landing-section" id="form-peminjaman">
        <div class="landing-card landing-form-card">
            <div class="landing-form-banner">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" style="flex-shrink:0">
                    <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    <path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                <span>Permohonan Peminjaman Kendaraan</span>
            </div>
            <p class="landing-section-sub">Isi formulir di bawah untuk mengajukan permohonan peminjaman kendaraan</p>
            
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
                            placeholder="Contoh: Staff HSE" required>
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
    <section class="landing-section" id="form-laporan-kejadian">
        <div class="landing-card landing-form-card">
            <div class="landing-form-banner">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 9v4M12 17h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                <span>Formulir Laporan Kejadian dan Kerusakan Kendaraan</span>
            </div>
            <p class="landing-section-sub">Laporkan incident atau near miss terkait operasi kendaraan / lingkungan kerja</p>

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
                                <option value="">-- Pilih kategori --</option>
                                <option value="Incident">Insiden</option>
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
                        <span>Gambar / Foto lampiran <span style="color:#ef4444">*</span></span>
                        <p class="lp-lk-gambar-hint">Unggah 1 sampai 3 foto. Tiap foto wajib diberi penjelasan di bawahnya. Gunakan tombol + untuk menambah slot, atau &ldquo;Pilih beberapa foto&rdquo; untuk mengisi hingga 3 sekaligus.</p>
                        <div id="lk_gambar_rows" class="lp-lk-gambar-list" aria-live="polite"></div>
                        <div class="lp-lk-photo-actions">
                            <button type="button" class="lp-landing-file-btn" id="lk_btn_tambah_gambar">+ Tambah foto</button>
                            <button type="button" class="lp-landing-file-btn lp-landing-file-btn--secondary" id="lk_btn_multi_foto">Pilih beberapa foto</button>
                            <input type="file" id="lk_foto_multi" accept="image/*" multiple style="display:none" aria-hidden="true">
                        </div>
                    </div>

                    <div class="checklist-field lp-form-full">
                        <div>
                            <span>TTD Pelapor <span style="color:#ef4444">*</span></span>
                            <div style="max-width:480px;margin-top:6px">
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
            <template id="lk_tpl_gambar_row">
                <div class="lp-lk-gambar-item" data-lk-gambar-item>
                    <div class="lp-lk-gambar-item-top">
                        <span class="lp-lk-gambar-item-label">Lampiran foto</span>
                        <button type="button" class="lp-lk-remove-gambar" data-lk-remove-gambar>Hapus gambar</button>
                    </div>
                    <input type="file" name="foto[]" accept="image/*" capture="environment" class="checklist-file-input lk-foto-in" style="display:none">
                    <button type="button" class="lp-landing-file-btn lk-pick-foto">Pilih / ambil foto</button>
                    <div class="lp-lk-photo-preview lk-slot-preview">
                        <img src="" alt="Pratinjau">
                    </div>
                    <div class="checklist-field lp-form-full" style="margin-bottom:0">
                        <span>Penjelasan gambar ini <span style="color:#ef4444">*</span></span>
                        <textarea name="penjelasan_gambar[]" rows="3" placeholder="Jelaskan isi foto ini"></textarea>
                    </div>
                </div>
            </template>
        </div>
    </section>

</div>

{{-- FOOTER --}}
@include('partials.footer')

{{-- ══════════════════ SCRIPTS ══════════════════ --}}
<script>
/** SweetAlert2 — warna popup mengikuti tema light/dark landing */
function landingSwalOpts(opts) {
    const base = {
        customClass: {
            popup: 'lp-swal-popup',
            title: 'lp-swal-title',
            confirmButton: 'lp-swal-confirm',
            cancelButton: 'lp-swal-cancel',
        },
        buttonsStyling: false,
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
let perPage      = 10;
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

    const totalPages = Math.ceil(filtered.length / perPage);
    if (currentPage > totalPages) currentPage = totalPages;

    const start  = (currentPage - 1) * perPage;
    const pageData = filtered.slice(start, start + perPage);

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
    if (window.AdminPagination) {
        window.AdminPagination.mountPagination(
            pgWrap,
            window.AdminPagination.buildClientPagination({ currentPage, lastPage: totalPages })
        );
    } else {
        pgWrap.innerHTML = '';
    }
}

function goPage(p) {
    const total = Math.ceil(filtered.length / perPage);
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

const armadaPaginationEl = document.getElementById('armada-pagination');
if (armadaPaginationEl && window.AdminPagination) {
    window.AdminPagination.bindClientPagination(armadaPaginationEl, goPage);
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

/* ── LAPORAN KEJADIAN: multi foto (maks 3) + penjelasan per foto ── */
(function setupLkGambarRows() {
    const LK_MAX_FOTO = 3;
    const tpl = document.getElementById('lk_tpl_gambar_row');
    const wrap = document.getElementById('lk_gambar_rows');
    const btnAdd = document.getElementById('lk_btn_tambah_gambar');
    const btnMulti = document.getElementById('lk_btn_multi_foto');
    const inpMulti = document.getElementById('lk_foto_multi');
    if (!tpl || !wrap || !btnAdd) return;

    function lkGambarRowCount() {
        return wrap.querySelectorAll('[data-lk-gambar-item]').length;
    }

    function refreshLkGambarUi() {
        const n = lkGambarRowCount();
        btnAdd.disabled = n >= LK_MAX_FOTO;
        btnAdd.style.opacity = n >= LK_MAX_FOTO ? '.45' : '';
        btnAdd.setAttribute('aria-disabled', n >= LK_MAX_FOTO ? 'true' : 'false');
    }

    function showSlotPreview(prevWrap, prevImg, file) {
        if (!file || !file.type.startsWith('image/')) return;
        if (prevImg.dataset.objUrl) {
            try { URL.revokeObjectURL(prevImg.dataset.objUrl); } catch (_) {}
        }
        const u = URL.createObjectURL(file);
        prevImg.dataset.objUrl = u;
        prevImg.src = u;
        prevWrap.classList.add('is-on');
    }

    function clearSlotPreview(prevWrap, prevImg) {
        if (prevImg.dataset.objUrl) {
            try { URL.revokeObjectURL(prevImg.dataset.objUrl); } catch (_) {}
            delete prevImg.dataset.objUrl;
        }
        prevImg.src = '';
        prevWrap.classList.remove('is-on');
    }

    function bindLkGambarRow(row) {
        const input = row.querySelector('.lk-foto-in');
        const pickBtn = row.querySelector('.lk-pick-foto');
        const prevWrap = row.querySelector('.lk-slot-preview');
        const prevImg = prevWrap ? prevWrap.querySelector('img') : null;
        const removeBtn = row.querySelector('[data-lk-remove-gambar]');

        function onFileChange() {
            const f = input.files && input.files[0];
            if (f && prevWrap && prevImg) showSlotPreview(prevWrap, prevImg, f);
            else if (prevWrap && prevImg) clearSlotPreview(prevWrap, prevImg);
        }

        if (pickBtn && input) pickBtn.addEventListener('click', () => input.click());
        if (input) input.addEventListener('change', onFileChange);

        if (removeBtn) {
            removeBtn.addEventListener('click', () => {
                if (lkGambarRowCount() <= 1) {
                    input.value = '';
                    row.querySelector('textarea').value = '';
                    if (prevWrap && prevImg) clearSlotPreview(prevWrap, prevImg);
                    return;
                }
                if (prevImg && prevImg.dataset.objUrl) {
                    try { URL.revokeObjectURL(prevImg.dataset.objUrl); } catch (_) {}
                }
                row.remove();
                refreshLkGambarUi();
            });
        }
    }

    window.lkAppendGambarRow = function () {
        if (lkGambarRowCount() >= LK_MAX_FOTO) return;
        const row = tpl.content.firstElementChild.cloneNode(true);
        wrap.appendChild(row);
        bindLkGambarRow(row);
        refreshLkGambarUi();
        return row;
    };

    window.lkResetGambarRows = function () {
        wrap.querySelectorAll('[data-lk-gambar-item]').forEach((row) => {
            const prevImg = row.querySelector('.lk-slot-preview img');
            if (prevImg && prevImg.dataset.objUrl) {
                try { URL.revokeObjectURL(prevImg.dataset.objUrl); } catch (_) {}
            }
            row.remove();
        });
        window.lkAppendGambarRow();
    };

    btnAdd.addEventListener('click', () => {
        if (lkGambarRowCount() >= LK_MAX_FOTO) return;
        window.lkAppendGambarRow();
    });

    if (btnMulti && inpMulti) {
        btnMulti.addEventListener('click', () => inpMulti.click());
        inpMulti.addEventListener('change', function () {
            const files = Array.from(this.files || []).filter((f) => f.type.startsWith('image/')).slice(0, LK_MAX_FOTO);
            this.value = '';
            if (!files.length) return;
            while (lkGambarRowCount() < files.length) window.lkAppendGambarRow();
            const rows = [...wrap.querySelectorAll('[data-lk-gambar-item]')];
            files.forEach((file, i) => {
                const row = rows[i];
                if (!row) return;
                const input = row.querySelector('.lk-foto-in');
                const prevWrap = row.querySelector('.lk-slot-preview');
                const prevImg = prevWrap ? prevWrap.querySelector('img') : null;
                try {
                    const dt = new DataTransfer();
                    dt.items.add(file);
                    input.files = dt.files;
                    if (prevWrap && prevImg) showSlotPreview(prevWrap, prevImg, file);
                } catch (_) {
                    input.value = '';
                    if (prevWrap && prevImg) clearSlotPreview(prevWrap, prevImg);
                }
            });
        });
    }

    window.lkAppendGambarRow();
    refreshLkGambarUi();
})();

/* ── LAPORAN KEJADIAN: signature pads ── */
const _lkSigPads = [];

function initLkSigPads() {
    if (!window.SignaturePad) return;
    const configs = [
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
    if (window.SignaturePad && document.getElementById('sig-pad-lk-pelapor')) {
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

        const hP = document.getElementById('sig-data-lk-pelapor');
        if (_lkSigPads.length >= 1) {
            if (_lkSigPads[0].isEmpty()) {
                Swal.fire(landingSwalOpts({ icon: 'warning', title: 'TTD Pelapor Kosong', text: 'Mohon tanda tangan Pelapor.' }));
                return;
            }
            hP.value = _lkSigPads[0].toDataURL('image/png');
        }

        const wrapG = document.getElementById('lk_gambar_rows');
        if (wrapG) {
            let didPrune = true;
            while (didPrune) {
                didPrune = false;
                const rs = [...wrapG.querySelectorAll('[data-lk-gambar-item]')];
                if (rs.length <= 1) break;
                for (const row of rs) {
                    const fi = row.querySelector('.lk-foto-in');
                    const ta = row.querySelector('textarea[name="penjelasan_gambar[]"]');
                    const hasF = fi && fi.files && fi.files[0];
                    const hasT = ta && ta.value.trim() !== '';
                    if (!hasF && !hasT) {
                        row.remove();
                        didPrune = true;
                        break;
                    }
                }
            }
        }
        const rowsAfter = wrapG ? [...wrapG.querySelectorAll('[data-lk-gambar-item]')] : [];
        const filled = rowsAfter.filter((row) => row.querySelector('.lk-foto-in')?.files?.[0]);
        if (!filled.length) {
            Swal.fire(landingSwalOpts({ icon: 'warning', title: 'Foto wajib', text: 'Mohon unggah minimal satu foto kejadian.' }));
            return;
        }
        for (const row of filled) {
            const ta = row.querySelector('textarea[name="penjelasan_gambar[]"]');
            if (!ta || !ta.value.trim()) {
                Swal.fire(landingSwalOpts({ icon: 'warning', title: 'Penjelasan wajib', text: 'Setiap foto lampiran harus dijelaskan pada kolom penjelasan di bawah foto tersebut.' }));
                return;
            }
        }
        const orphan = rowsAfter.filter((row) => !row.querySelector('.lk-foto-in')?.files?.[0] && row.querySelector('textarea[name="penjelasan_gambar[]"]')?.value.trim());
        if (orphan.length) {
            Swal.fire(landingSwalOpts({ icon: 'warning', title: 'Foto tidak lengkap', text: 'Ada penjelasan tanpa foto — unggah fotonya atau kosongkan baris tersebut (hapus dengan tombol Hapus gambar).' }));
            return;
        }

        const confirm = await Swal.fire(landingSwalOpts({
            title: 'Kirim laporan?',
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
                const successText = data.pending_manager_approval
                    ? 'Laporan berhasil dikirim. Tautan persetujuan telah dikirimkan ke email manager bidang Anda.'
                    : 'Laporan berhasil dikirim dan PDF telah dibuat.';
                await Swal.fire(landingSwalOpts({
                    icon: 'success',
                    title: 'Laporan Terkirim',
                    text: successText,
                }));
                this.reset();
                document.getElementById('lk_jenis_kendaraan').value = '';
                if (typeof window.lkResetGambarRows === 'function') window.lkResetGambarRows();
                _lkSigPads.forEach((pad) => {
                    pad.clear();
                    const hint = document.getElementById('sig-hint-lk-pelapor');
                    if (hint) hint.classList.remove('hidden');
                });
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

/* ── INIT ── */
renderTable();
</script>
</body>
</html>
