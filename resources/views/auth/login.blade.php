<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="description" content="Login - VMS Vehicle Management System Portal Kendaraan Operasional">
        <title>Login - {{ config('app.name', 'VMS') }}</title>
        @vite(['resources/css/auth.css', 'resources/js/app.js'])
        
        <style>
            /* Gradient tokens — diubah JS saat toggle tema */
            :root {
                --grad-1: #0a1628;
                --grad-2: #162a52;
                --grad-3: #0d1a33;
            }

            .auth-page-body {
                margin: 0;
                padding: 0;
                min-height: 100vh;
                background: linear-gradient(-45deg, var(--grad-1), var(--grad-2), var(--grad-3), var(--grad-1));
                background-size: 400% 400%;
                animation: gradientBG 15s ease infinite;
                overflow: hidden;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            @keyframes gradientBG {
                0% { background-position: 0% 50%; }
                50% { background-position: 100% 50%; }
                100% { background-position: 0% 50%; }
            }

            .auth-bg-canvas {
                position: fixed;
                top: 0;
                left: 0;
                z-index: 0;
                pointer-events: none;
            }

            .auth-card {
                position: relative;
                z-index: 10;
            }
        </style>
    </head>
    <body class="auth-page-body">

        {{-- Theme toggle button --}}
        <button class="auth-theme-toggle" id="theme-toggle" title="Ganti Tema" aria-label="Toggle tema">
            <i class="bi bi-sun-fill" id="theme-icon"></i>
        </button>

        {{-- Animated background canvas --}}
        <canvas class="auth-bg-canvas" id="auth-bg-canvas"></canvas>

        {{-- Login card --}}
        <div class="auth-card" id="login-card">
            <div class="auth-card-image">
                <img src="{{ asset('images/VMSme.png') }}" alt="VMS - Vehicle Management System" class="auth-hero-img">
            </div>

            <div class="auth-card-body">
                @if (session('status'))
                    <div class="auth-alert auth-alert-success" role="alert" id="login-status">
                        <i class="bi bi-check-circle-fill"></i>
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="auth-form" data-login-form id="login-form">
                    @csrf
                    {{-- Username --}}
                    <div class="auth-field">
                        <div class="auth-input-group @error('username') has-error @enderror">
                            <span class="auth-input-icon"><i class="bi bi-person-fill"></i></span>
                            <input id="username" type="text" name="username" class="auth-input" value="{{ old('username') }}" placeholder="Username" required autofocus>
                        </div>
                        @error('username')
                            <div class="auth-error-msg"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div class="auth-field">
                        <div class="auth-input-group @error('password') has-error @enderror">
                            <span class="auth-input-icon"><i class="bi bi-lock-fill"></i></span>
                            <input id="password" type="password" name="password" class="auth-input" placeholder="Password" required>
                            <button class="auth-password-toggle" type="button" data-password-toggle>
                                <i class="bi bi-eye" data-password-icon></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="auth-error-msg"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="auth-btn-submit" id="login-submit">
                        <span class="auth-btn-text">Sign in</span>
                        <i class="bi bi-box-arrow-in-right"></i>
                    </button>
                </form>

                <div class="auth-back-wrap">
                    <a href="{{ route('landing') }}" class="auth-back-link">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                        Kembali ke Halaman Utama
                    </a>
                </div>

                <div class="auth-footer">
                    <p>&copy; {{ date('Y') }} Port Management Unit Suralaya</p>
                </div>
            </div>
        </div>

        <script>
        /* ── Canvas animated lines ── */
        (function () {
            const canvas = document.getElementById('auth-bg-canvas');
            const ctx    = canvas.getContext('2d');

            let W, H;
            function resize() {
                W = canvas.width  = window.innerWidth;
                H = canvas.height = window.innerHeight;
            }
            resize();
            window.addEventListener('resize', resize);

            const lineBase = [
                {
                    startYFrac: 0.22, endYFrac: 0.18,
                    cp1: { xFrac: 0.25, yFrac: 0.08 },
                    cp2: { xFrac: 0.70, yFrac: 0.32 },
                    phases: [0.00, 1.40, 2.60, 0.90],
                    speeds: [0.30, 0.19, 0.23, 0.37],
                    amps:   [0.07, 0.04, 0.06, 0.05],
                    lineWidth: 2.2,
                    dark:  'rgba(201, 162, 39, 0.55)',
                    light: 'rgba(59, 95, 192, 0.38)',
                },
                {
                    startYFrac: 0.80, endYFrac: 0.76,
                    cp1: { xFrac: 0.30, yFrac: 0.92 },
                    cp2: { xFrac: 0.68, yFrac: 0.68 },
                    phases: [1.20, 0.50, 3.10, 1.80],
                    speeds: [0.25, 0.33, 0.18, 0.28],
                    amps:   [0.06, 0.05, 0.07, 0.04],
                    lineWidth: 1.6,
                    dark:  'rgba(201, 162, 39, 0.38)',
                    light: 'rgba(59, 95, 192, 0.24)',
                },
            ];

            let t = 0;

            function drawLine(l, stroke, lw, time) {
                const sy  = (l.startYFrac + Math.sin(time * l.speeds[0] + l.phases[0]) * l.amps[0]) * H;
                const ey  = (l.endYFrac   + Math.cos(time * l.speeds[1] + l.phases[1]) * l.amps[1]) * H;
                const c1x = (l.cp1.xFrac  + Math.sin(time * l.speeds[2] + l.phases[2]) * l.amps[2]) * W;
                const c1y = (l.cp1.yFrac  + Math.cos(time * l.speeds[0] + l.phases[3]) * l.amps[3]) * H;
                const c2x = (l.cp2.xFrac  + Math.cos(time * l.speeds[1] + l.phases[1]) * l.amps[2]) * W;
                const c2y = (l.cp2.yFrac  + Math.sin(time * l.speeds[3] + l.phases[0]) * l.amps[3]) * H;
                ctx.beginPath();
                ctx.moveTo(0, sy);
                ctx.bezierCurveTo(c1x, c1y, c2x, c2y, W, ey);
                ctx.strokeStyle = stroke;
                ctx.lineWidth   = lw;
                ctx.stroke();
            }

            function animate() {
                ctx.clearRect(0, 0, W, H);
                t += 0.025;
                const isLight = document.body.classList.contains('light-mode');
                lineBase.forEach(l => {
                    const s = isLight ? l.light : l.dark;
                    const glow = s.replace(/[\d.]+\)$/, '0.08)');
                    drawLine(l, glow, l.lineWidth * 6, t);
                });
                lineBase.forEach(l => {
                    drawLine(l, isLight ? l.light : l.dark, l.lineWidth, t);
                });
                requestAnimationFrame(animate);
            }
            animate();
        })();

        /* ── Theme toggle ── */
        (function () {
            const btn  = document.getElementById('theme-toggle');
            const icon = document.getElementById('theme-icon');
            const body = document.body;
            const root = document.documentElement;

            const DARK  = { g1: '#0a1628', g2: '#162a52', g3: '#0d1a33' };
            const LIGHT = { g1: '#c7d9f8', g2: '#dbeafe', g3: '#e8f0fe' };

            function applyTheme(isLight) {
                const g = isLight ? LIGHT : DARK;
                root.style.setProperty('--grad-1', g.g1);
                root.style.setProperty('--grad-2', g.g2);
                root.style.setProperty('--grad-3', g.g3);
                body.classList.toggle('light-mode', isLight);
                icon.className = isLight ? 'bi bi-moon-fill' : 'bi bi-sun-fill';
            }

            /* Restore saved preference */
            applyTheme(localStorage.getItem('vms-theme') === 'light');

            btn.addEventListener('click', function () {
                const next = !body.classList.contains('light-mode');
                applyTheme(next);
                localStorage.setItem('vms-theme', next ? 'light' : 'dark');
            });
        })();
        </script>
    </body>
</html>