<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Buat Ceklist Baru - {{ config('app.name', 'Laravel') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="dash-body">
        @php
            $user = auth()->user();
            $userRoleLabel = $user?->role === 'admin' ? 'ADMIN' : 'DRIVER';
        @endphp

        <div class="checklist-shell" data-checklist-wizard>
            <header class="checklist-topbar">
                <div>
                    <h1 class="dash-brand-title">Ceklist Kendaraan</h1>
                    <p class="dash-brand-sub">NEXUS FLEET MANAGEMENT</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="dash-chip">{{ $userRoleLabel }}</span>
                    <a href="{{ route('dashboard') }}" class="checklist-icon-btn" aria-label="Kembali ke dashboard">
                        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                </div>
            </header>

            <main class="checklist-content">
                <form id="checklist-form" class="checklist-card" action="#" method="POST" enctype="multipart/form-data" novalidate>
                    @csrf

                    <div class="checklist-progress-head">
                        <div class="flex justify-between text-xs font-semibold text-slate-400">
                            <span id="checklist-step-label">LANGKAH 1</span>
                            <span>SELESAI</span>
                        </div>
                        <div class="checklist-progress-track">
                            <span id="checklist-progress-fill"></span>
                        </div>
                    </div>

                    <section class="wizard-step active" data-step="1">
                        <h2 class="checklist-section-heading">1. Identitas Kendaraan</h2>
                        <div class="checklist-grid-two">
                            <label class="checklist-field">
                                <span>Tanggal</span>
                                <div class="checklist-control-wrap checklist-control-date">
                                    <input type="date" name="tanggal" required>
                                </div>
                            </label>
                            <label class="checklist-field">
                                <span>Shift</span>
                                <div class="checklist-control-wrap checklist-control-select">
                                    <select name="shift" required>
                                        <option value="">Pilih Shift</option>
                                        <option>Pagi</option>
                                        <option>Siang</option>
                                        <option>Malam</option>
                                    </select>
                                </div>
                            </label>
                            <label class="checklist-field">
                                <span>Jam Serah Terima</span>
                                <div class="checklist-control-wrap checklist-control-time">
                                    <input type="time" name="jam_serah_terima" required>
                                </div>
                            </label>
                            <label class="checklist-field">
                                <span>Nomor Kendaraan</span>
                                <input type="text" name="nomor_kendaraan" placeholder="Contoh: B 1234 ABC" required>
                            </label>
                        </div>
                        <label class="checklist-field">
                            <span>Jenis Kendaraan</span>
                            <input type="text" name="jenis_kendaraan" placeholder="Contoh: Truk Engkel, Pick Up, dll" required>
                        </label>
                        <label class="checklist-field">
                            <span>Pengemudi yang Menyerahkan</span>
                            <input type="text" name="driver_serah" required>
                        </label>
                        <label class="checklist-field">
                            <span>Pengemudi yang Menerima</span>
                            <input type="text" name="driver_terima" required>
                        </label>
                    </section>

                    <section class="wizard-step" data-step="2">
                        <h2 class="checklist-section-heading">2. Checklist Exterior Kendaraan</h2>
                        <div class="checklist-item-list">
                            @foreach (['Body Kendaraan' => 'body_kendaraan', 'Kaca' => 'kaca', 'Spion' => 'spion', 'Lampu Utama' => 'lampu_utama', 'Lampu Sein' => 'lampu_sein', 'Ban' => 'ban', 'Velg' => 'velg', 'Wiper' => 'wiper'] as $label => $name)
                                <div class="checklist-condition-row">
                                    <div class="checklist-condition-head">
                                        <span>{{ $label }}</span>
                                        <div class="checklist-radio-group">
                                            <input type="radio" id="exterior_{{ $name }}_ok" name="exterior_{{ $name }}" value="ok" required>
                                            <label for="exterior_{{ $name }}_ok">OK</label>
                                            <input type="radio" id="exterior_{{ $name }}_no" name="exterior_{{ $name }}" value="tidak_ok">
                                            <label for="exterior_{{ $name }}_no">Tidak OK</label>
                                        </div>
                                    </div>
                                    <input type="text" name="exterior_{{ $name }}_catatan" class="checklist-item-note" placeholder="Keterangan...">
                                </div>
                            @endforeach
                        </div>
                        <label class="checklist-field">
                            <span>Catatan Kondisi Exterior</span>
                            <textarea name="exterior_catatan" rows="3" placeholder="Isi catatan bila ada temuan..."></textarea>
                        </label>
                        <div class="checklist-field">
                            <span>Foto Bukti Exterior (Maks 4)</span>
                            <div class="checklist-photo-grid checklist-photo-grid-4" data-upload-grid data-max-files="4">
                                @foreach (['depan', 'kanan', 'kiri', 'belakang'] as $side)
                                    <label class="checklist-photo-slot">
                                        <input type="file" name="exterior_foto_{{ $side }}" accept="image/*" data-photo-single>
                                        <span class="checklist-photo-icon">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                <rect x="3.5" y="5" width="17" height="13" rx="2" stroke="currentColor" stroke-width="1.8"/>
                                                <circle cx="9" cy="10" r="1.4" stroke="currentColor" stroke-width="1.6"/>
                                                <path d="M20 15L15.3 10.5L8 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </span>
                                        <strong>{{ strtoupper($side) }}</strong>
                                        <small data-file-name>Belum dipilih</small>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </section>

                    <section class="wizard-step" data-step="3">
                        <h2 class="checklist-section-heading">3. Checklist Interior Kendaraan</h2>
                        <div class="checklist-item-list">
                            @foreach (['Jok / Kursi' => 'jok', 'Dashboard' => 'dashboard', 'AC' => 'ac', 'Sabuk Pengaman' => 'sabuk_pengaman', 'Audio / Head Unit' => 'audio', 'Kebersihan Interior' => 'kebersihan'] as $label => $name)
                                <div class="checklist-condition-row">
                                    <div class="checklist-condition-head">
                                        <span>{{ $label }}</span>
                                        <div class="checklist-radio-group">
                                            <input type="radio" id="interior_{{ $name }}_ok" name="interior_{{ $name }}" value="ok" required>
                                            <label for="interior_{{ $name }}_ok">OK</label>
                                            <input type="radio" id="interior_{{ $name }}_no" name="interior_{{ $name }}" value="tidak_ok">
                                            <label for="interior_{{ $name }}_no">Tidak OK</label>
                                        </div>
                                    </div>
                                    <input type="text" name="interior_{{ $name }}_catatan" class="checklist-item-note" placeholder="Keterangan...">
                                </div>
                            @endforeach
                        </div>
                        <label class="checklist-field">
                            <span>Catatan Kondisi Interior</span>
                            <textarea name="interior_catatan" rows="3" placeholder="Isi catatan bila ada temuan..."></textarea>
                        </label>
                        <label class="checklist-field">
                            <span>Upload Foto Interior (Maks 3)</span>
                            <div class="checklist-photo-multi">
                                <input type="file" name="interior_foto[]" accept="image/*" multiple data-photo-multi data-max-files="3">
                                <small data-file-multi-info>Pilih maksimal 3 gambar.</small>
                            </div>
                        </label>
                    </section>

                    <section class="wizard-step" data-step="4">
                        <h2 class="checklist-section-heading">4. Checklist Ruang Mesin</h2>
                        <div class="checklist-item-list">
                            @foreach (['Mesin (Suara Normal)' => 'mesin', 'Oli Mesin' => 'oli', 'Air Radiator' => 'radiator', 'Rem' => 'rem', 'Kopling (Manual)' => 'kopling', 'Transmisi' => 'transmisi', 'Indikator Panel' => 'indikator'] as $label => $name)
                                <div class="checklist-condition-row">
                                    <div class="checklist-condition-head">
                                        <span>{{ $label }}</span>
                                        <div class="checklist-radio-group">
                                            <input type="radio" id="mesin_{{ $name }}_ok" name="mesin_{{ $name }}" value="ok" required>
                                            <label for="mesin_{{ $name }}_ok">OK</label>
                                            <input type="radio" id="mesin_{{ $name }}_no" name="mesin_{{ $name }}" value="tidak_ok">
                                            <label for="mesin_{{ $name }}_no">Tidak OK</label>
                                        </div>
                                    </div>
                                    <input type="text" name="mesin_{{ $name }}_catatan" class="checklist-item-note" placeholder="Keterangan...">
                                </div>
                            @endforeach
                        </div>
                        <label class="checklist-field">
                            <span>Catatan Kondisi Mesin & Operasional</span>
                            <textarea name="mesin_catatan" rows="3" placeholder="Isi catatan bila ada temuan..."></textarea>
                        </label>
                        <label class="checklist-field">
                            <span>Upload Foto Ruang Mesin (Maks 3)</span>
                            <div class="checklist-photo-multi">
                                <input type="file" name="mesin_foto[]" accept="image/*" multiple data-photo-multi data-max-files="3">
                                <small data-file-multi-info>Pilih maksimal 3 gambar.</small>
                            </div>
                        </label>
                    </section>

                    <section class="wizard-step" data-step="5">
                        <h2 class="checklist-section-heading">5. BBM & Kilometer</h2>
                        <div class="checklist-grid-two">
                            <div class="checklist-field checklist-bbm-field">
                                <span>Level BBM (%)</span>
                                <div class="checklist-bbm-card">
                                    <div class="checklist-bbm-top">
                                        <p>Level BBM</p>
                                        <label>
                                            <input type="number" min="0" max="100" name="level_bbm" value="50" required data-bbm-number>
                                            <em>%</em>
                                        </label>
                                    </div>
                                    <input type="range" min="0" max="100" step="1" value="50" data-bbm-range>
                                    <div class="checklist-bbm-scale">
                                        <span>E (Kosong)</span>
                                        <span>1/2</span>
                                        <span>F (Penuh)</span>
                                    </div>
                                </div>
                            </div>
                            <label class="checklist-field">
                                <span>Pengisian BBM Terakhir</span>
                                <div class="checklist-control-wrap checklist-control-date">
                                    <input type="datetime-local" name="bbm_terakhir">
                                </div>
                            </label>
                            <label class="checklist-field">
                                <span>KM Awal</span>
                                <input type="number" min="0" name="km_awal" required>
                            </label>
                            <label class="checklist-field">
                                <span>KM Akhir</span>
                                <input type="number" min="0" name="km_akhir">
                            </label>
                        </div>
                    </section>

                    <section class="wizard-step" data-step="6">
                        <h2 class="checklist-section-heading">6. Checklist Perlengkapan Kendaraan</h2>
                        <div class="checklist-check-grid">
                            @foreach (['STNK' => 'stnk', 'Kartu KIR' => 'kir', 'Dongkrak' => 'dongkrak', 'Toolkit' => 'toolkit', 'Segitiga Pengaman' => 'segitiga', 'APAR' => 'apar', 'Ban Cadangan' => 'ban_cadangan'] as $label => $name)
                                <label class="checklist-checkbox">
                                    <input type="checkbox" name="perlengkapan[{{ $name }}]" value="1">
                                    <span class="checklist-checkmark" aria-hidden="true"></span>
                                    <span>{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </section>

                    <section class="wizard-step" data-step="7">
                        <h2 class="checklist-section-heading">7. Validasi & Konfirmasi</h2>
                        <label class="checklist-field">
                            <span>Catatan / Temuan Khusus</span>
                            <textarea name="catatan_khusus" rows="4" placeholder="Tuliskan catatan khusus jika ada..."></textarea>
                        </label>

                        <label class="checklist-confirm-box">
                            <input type="checkbox" name="konfirmasi_data" required>
                            <span>Saya menyatakan kendaraan sudah dicek dan data sesuai kondisi aktual.</span>
                        </label>

                        <fieldset class="checklist-signature-mode">
                            <legend>Tanda Tangan</legend>
                            <label class="checklist-signature-choice">
                                <input type="radio" name="signature_mode" value="digital" checked>
                                <span>Tanda tangan digital (upload gambar)</span>
                            </label>
                            <label class="checklist-signature-choice">
                                <input type="radio" name="signature_mode" value="basah">
                                <span>Tanda tangan basah (kolom dikosongkan)</span>
                            </label>
                        </fieldset>

                        <label class="checklist-field" id="signature-upload-wrap">
                            <span>Upload Tanda Tangan Digital</span>
                            <input type="file" name="tanda_tangan" accept="image/*">
                        </label>

                        <div class="checklist-manual-sign" id="signature-manual-wrap" hidden>
                            Area ini dikosongkan untuk tanda tangan basah langsung.
                        </div>
                    </section>
                </form>
            </main>

            <footer class="checklist-footer">
                <button type="button" class="checklist-nav-btn checklist-nav-back" id="wizard-prev" disabled>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
                <button type="button" class="checklist-nav-btn checklist-nav-next" id="wizard-next">
                    Lanjut
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </footer>
        </div>
    </body>
</html>
