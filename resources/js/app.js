import './bootstrap';
import Alpine from 'alpinejs';
import SignaturePad from 'signature_pad';
import 'bootstrap-icons/font/bootstrap-icons.css';
import 'tom-select/dist/css/tom-select.bootstrap5.css';
import * as Turbo from '@hotwired/turbo';
import Swal from 'sweetalert2';
import * as AdminPagination from './admin-pagination';
import * as AdminTableSort from './admin-table-sort';

window.Alpine    = Alpine;
window.SignaturePad = SignaturePad;
window.Swal      = Swal;
window.AdminPagination = AdminPagination;
window.AdminTableSort  = AdminTableSort;

Turbo.start();
Alpine.start();

/* ================================================================
   TURBO BEFORE-CACHE REGISTRY — central cleanup hub
   Inline scripts call window.registerTurboCleanup(fn) instead of
   adding their own document.addEventListener('turbo:before-cache').
   This prevents listener stacking on repeated Turbo visits.
   ================================================================ */
const _turboCleanupRegistry = new Set();
window.registerTurboCleanup = function (fn) { _turboCleanupRegistry.add(fn); };
document.addEventListener('turbo:before-cache', function () {
    _turboCleanupRegistry.forEach(function (fn) { try { fn(); } catch (_) {} });
    _turboCleanupRegistry.clear();
});

/* ================================================================
   VMS DASH CHROME — theme, mobile menu, notifications
   localStorage keys: 'vms-theme' (canonical) + 'vms-dash-theme' (legacy)
   ================================================================ */
(function initVmsDashChrome() {
    const html = document.documentElement;

    /* ── 1. Apply saved theme immediately (module runs before first paint) ── */
    function applyTheme(dark) {
        html.classList.toggle('dark', dark);
        document.body.classList.toggle('dark', dark);
        html.style.colorScheme = dark ? 'dark' : 'light';
        const icon  = document.getElementById('dash-theme-icon');
        const label = document.getElementById('dash-theme-label');
        if (icon)  icon.className    = dark ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
        if (label) label.textContent = dark ? 'Light Mode' : 'Dark Mode';
    }

    const saved  = localStorage.getItem('vms-theme') || localStorage.getItem('vms-dash-theme');
    applyTheme(saved === 'dark');

    /* Preserve dark class on body for JS checks; CSS uses html.dark */
    document.addEventListener('turbo:before-render', function (e) {
        if (html.classList.contains('dark')) {
            e.detail.newBody.classList.add('dark');
        } else {
            e.detail.newBody.classList.remove('dark');
        }
    });

    /* ── 3. Document-level handlers (registered ONCE; use fresh getElementById) ── */

    // Outside-click: close notification panels + legacy mobile menu
    document.addEventListener('click', function (e) {
        // Notification panels
        [
            ['dash-notif-wrap',     'dash-notif-panel',     'dash-notif-toggle'],
            ['dash-nav-notif-wrap', 'dash-nav-notif-panel', 'dash-nav-notif-toggle'],
        ].forEach(function ([wId, pId, bId]) {
            const w = document.getElementById(wId);
            const p = document.getElementById(pId);
            const b = document.getElementById(bId);
            if (w && p && b && !w.contains(e.target)) {
                p.hidden = true;
                b.setAttribute('aria-expanded', 'false');
            }
        });
        // Legacy mobile menu
        const navA  = document.getElementById('dash-nav-actions');
        const menuB = document.getElementById('dash-mobile-menu-btn');
        if (navA && menuB && !navA.contains(e.target) && !menuB.contains(e.target)) {
            navA.classList.remove('mobile-open');
            const menuI = document.getElementById('dash-mobile-menu-icon');
            if (menuI) menuI.className = 'bi bi-list';
            menuB.setAttribute('aria-expanded', 'false');
        }
    });

    // Escape key: close drawers/menus/panels
    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') return;
        // Profile drawer
        const prof = document.getElementById('profile-drawer');
        if (prof && prof.classList.contains('open')) {
            if (typeof closeProfileDrawer === 'function') closeProfileDrawer();
            return;
        }
        // Nav drawer
        if (typeof closeDashNavDrawer === 'function') closeDashNavDrawer();
        // Notification panels
        [
            ['dash-notif-panel', 'dash-notif-toggle'],
            ['dash-nav-notif-panel', 'dash-nav-notif-toggle'],
        ].forEach(function ([pId, bId]) {
            const p = document.getElementById(pId);
            const b = document.getElementById(bId);
            if (p && b) { p.hidden = true; b.setAttribute('aria-expanded', 'false'); }
        });
        // Legacy mobile menu
        const navA  = document.getElementById('dash-nav-actions');
        const menuI = document.getElementById('dash-mobile-menu-icon');
        const menuB = document.getElementById('dash-mobile-menu-btn');
        if (navA) {
            navA.classList.remove('mobile-open');
            if (menuI) menuI.className = 'bi bi-list';
            if (menuB) menuB.setAttribute('aria-expanded', 'false');
        }
    });

    /* ── 4. Cross-page hash scroll via sessionStorage (works with Turbo) ── */
    const SCROLL_KEY = 'dash_pending_smooth_scroll';
    if ('scrollRestoration' in history) history.scrollRestoration = 'manual';

    // Notification hash links — same-page scroll or cross-page store
    document.addEventListener('click', function (e) {
        const link = e.target.closest('a.dash-notif-link');
        if (!link) return;
        const href = link.getAttribute('href');
        if (!href) return;
        let url;
        try { url = new URL(href, window.location.href); } catch (_) { return; }
        if (!url.hash) return;

        e.preventDefault();

        const herePath  = window.location.origin + window.location.pathname + window.location.search;
        const therePath = url.origin + url.pathname + url.search;

        if (therePath === herePath) {
            // Same page — just scroll
            _smoothScrollToHash(url.hash);
        } else {
            // Different page — store hash and navigate via Turbo (avoids full reload)
            sessionStorage.setItem(SCROLL_KEY, url.hash);
            Turbo.visit(therePath);
        }
    });

    function _smoothScrollToHash(hash, attempt) {
        attempt = attempt || 0;
        const id = decodeURIComponent(hash.replace(/^#/, ''));
        const el = document.getElementById(id);
        if (!el) {
            if (attempt < 20) setTimeout(function () { _smoothScrollToHash(hash, attempt + 1); }, 100);
            return;
        }
        el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        history.replaceState(null, '', window.location.pathname + window.location.search + hash);
    }

    /* ── 5. Per-page init: runs on every turbo:load (initial + navigation) ── */
    document.addEventListener('turbo:load', function () {
        // Sync icon/label with current theme
        applyTheme(html.classList.contains('dark'));

        // Theme toggle button — guard: body recreated each nav so new element,
        // but use _vmsBound just in case it's ever marked permanent
        const themeBtn = document.getElementById('dash-theme-toggle');
        if (themeBtn && !themeBtn._vmsBound) {
            themeBtn._vmsBound = true;
            themeBtn.addEventListener('click', function () {
                const next = !html.classList.contains('dark');
                applyTheme(next);
                localStorage.setItem('vms-theme',      next ? 'dark' : 'light');
                localStorage.setItem('vms-dash-theme', next ? 'dark' : 'light');
            });
        }

        // Legacy mobile menu toggle button
        const legacyMenuBtn = document.getElementById('dash-mobile-menu-btn');
        const legacyNavAct  = document.getElementById('dash-nav-actions');
        if (legacyMenuBtn && legacyNavAct && !legacyMenuBtn._vmsBound) {
            legacyMenuBtn._vmsBound = true;
            legacyMenuBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                const icon   = document.getElementById('dash-mobile-menu-icon');
                const isOpen = legacyNavAct.classList.toggle('mobile-open');
                if (icon) icon.className = isOpen ? 'bi bi-x-lg' : 'bi bi-list';
                legacyMenuBtn.setAttribute('aria-expanded', String(isOpen));
            });
            window.addEventListener('resize', function () {
                if (window.innerWidth >= 992) {
                    legacyNavAct.classList.remove('mobile-open');
                    const icon = document.getElementById('dash-mobile-menu-icon');
                    if (icon) icon.className = 'bi bi-list';
                    legacyMenuBtn.setAttribute('aria-expanded', 'false');
                }
            }, { passive: true });
        }

        // Notification toggle buttons
        [
            ['dash-notif-toggle',     'dash-notif-panel'],
            ['dash-nav-notif-toggle', 'dash-nav-notif-panel'],
        ].forEach(function ([bId, pId]) {
            const btn   = document.getElementById(bId);
            const panel = document.getElementById(pId);
            if (!btn || !panel || btn._vmsBound) return;
            btn._vmsBound = true;
            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                const open  = panel.hidden;
                panel.hidden = !open;
                btn.setAttribute('aria-expanded', String(open));
            });
        });

        // Notification read + scroll on link click
        [
            ['dash-notif-panel',     'dash-notif-toggle'],
            ['dash-nav-notif-panel', 'dash-nav-notif-toggle'],
        ].forEach(function ([pId, bId]) {
            const panel = document.getElementById(pId);
            if (!panel || panel._notifLinksBound) return;
            panel._notifLinksBound = true;
            panel.querySelectorAll('.dash-notif-link[data-notification-id]').forEach(function (a) {
                a.addEventListener('click', function (e) {
                    const nid  = a.getAttribute('data-notification-id');
                    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
                    if (nid && csrf) {
                        fetch('/notifications/' + encodeURIComponent(nid) + '/read', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrf,
                                Accept: 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            credentials: 'same-origin',
                            keepalive: true,
                        }).catch(function () {});
                        a.closest('.dash-notif-item')?.classList.remove('is-unread');
                    }

                    const hrefAttr = a.getAttribute('href') || '';
                    let targetUrl;
                    try { targetUrl = new URL(hrefAttr, window.location.href); } catch (_) { return; }
                    if (!targetUrl.hash) return;

                    const herePath  = window.location.origin + window.location.pathname + window.location.search;
                    const therePath = targetUrl.origin + targetUrl.pathname + targetUrl.search;
                    const scrollId  = decodeURIComponent(targetUrl.hash.slice(1));

                    if (therePath === herePath) {
                        e.preventDefault();
                        const btn = document.getElementById(bId);
                        if (panel) panel.hidden = true;
                        if (btn) btn.setAttribute('aria-expanded', 'false');
                        const el = document.getElementById(scrollId);
                        if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                    // Cross-page: let anchor navigate; the document.click handler above stores the hash
                });
            });
        });

        // Hash scroll on page (URL anchor)
        (function smoothScrollCurrentHash() {
            const raw = window.location.hash;
            if (!raw || raw === '#') return;
            let id = raw.slice(1);
            try { id = decodeURIComponent(id); } catch (_) {}
            if (!id) return;
            const el = document.getElementById(id);
            if (!el) return;
            requestAnimationFrame(function () {
                window.setTimeout(function () {
                    el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }, 48);
            });
        }());

        // Pending hash scroll from sessionStorage (cross-page nav)
        const pendingHash = sessionStorage.getItem(SCROLL_KEY);
        if (pendingHash) {
            sessionStorage.removeItem(SCROLL_KEY);
            if (window.location.hash) {
                history.replaceState(null, '', window.location.pathname + window.location.search);
            }
            window.scrollTo(0, 0);
            setTimeout(function () { _smoothScrollToHash(pendingHash); }, 300);
        }

    });

    // Nav drawer close — delegated once at document level, no per-visit re-binding
    document.addEventListener('click', function (e) {
        if (e.target.closest('#dash-nav-drawer a.dash-nav-drawer-link')) {
            if (typeof closeDashNavDrawer === 'function') closeDashNavDrawer();
        }
    });
})();

/* ================================================================
   VMS PRESENCE — heartbeat / online-offline
   ================================================================ */
(function initVmsPresence() {
    if (!document.body.classList.contains('dash-body')) return;
    // Guard: presence timers must not be duplicated across Turbo navigations
    if (window._vmsPresenceStarted) return;
    window._vmsPresenceStarted = true;

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const HEARTBEAT_URL = '/api/presence/heartbeat';
    const OFFLINE_URL   = '/api/presence/offline';
    const HEARTBEAT_MS  = 60000;
    let heartbeatTimer  = null;

    function updateDashPresenceUI(online) {
        const statusEl = document.getElementById('dash-presence-status');
        const labelEl  = document.getElementById('dash-presence-label');
        if (!statusEl) return;
        statusEl.classList.toggle('mgmt-presence--online',  online);
        statusEl.classList.toggle('mgmt-presence--offline', !online);
        if (labelEl) labelEl.textContent = online ? 'Online' : 'Offline';
    }

    function stopHeartbeat() {
        if (heartbeatTimer !== null) {
            window.clearInterval(heartbeatTimer);
            heartbeatTimer = null;
        }
    }

    function sendHeartbeat() {
        if (!navigator.onLine || !csrf) return;
        fetch(HEARTBEAT_URL, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
            credentials: 'same-origin',
        }).catch(function () {});
    }

    function sendOfflineSignal() {
        if (!csrf) return;
        const payload = new URLSearchParams({ _token: csrf });
        if (navigator.sendBeacon) { navigator.sendBeacon(OFFLINE_URL, payload); return; }
        fetch(OFFLINE_URL, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json', 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
            credentials: 'same-origin',
            body: payload.toString(),
            keepalive: true,
        }).catch(function () {});
    }

    function markOfflineAndStop() {
        stopHeartbeat();
        updateDashPresenceUI(false);
        sendOfflineSignal();
    }

    // Restart heartbeat on every turbo:load (covers initial visit and page navigation)
    document.addEventListener('turbo:load', function () {
        if (!document.body.classList.contains('dash-body')) return;
        updateDashPresenceUI(navigator.onLine);
        sendHeartbeat();
        if (heartbeatTimer === null) {
            heartbeatTimer = window.setInterval(sendHeartbeat, HEARTBEAT_MS);
        }
    });

    window.addEventListener('online', function () {
        updateDashPresenceUI(true);
        sendHeartbeat();
        if (heartbeatTimer === null) heartbeatTimer = window.setInterval(sendHeartbeat, HEARTBEAT_MS);
    });
    window.addEventListener('offline', function () {
        stopHeartbeat();
        updateDashPresenceUI(false);
    });
    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'visible' && navigator.onLine) {
            updateDashPresenceUI(true);
            sendHeartbeat();
            if (heartbeatTimer === null) heartbeatTimer = window.setInterval(sendHeartbeat, HEARTBEAT_MS);
        }
    });
    // Only mark offline on actual logout, not on every Turbo navigation
    document.addEventListener('submit', function (e) {
        if (e.target.matches('form[action*="logout"]')) markOfflineAndStop();
    });
    window.addEventListener('pagehide', function (e) {
        if (e.persisted) return; // bfcache — tab not really closing
        markOfflineAndStop();
    });
})();

/* ================================================================
   MAIN PAGE FEATURES — runs on every page load / Turbo navigation
   ================================================================ */
document.addEventListener('turbo:load', async () => {
    /* ── Pressable feedback ── */
    document.querySelectorAll('.dash-pressable').forEach(el => {
        if (el._vmsBound) return;
        el._vmsBound = true;
        const clear = () => el.classList.remove('dash-pressing');
        el.addEventListener('pointerdown', () => el.classList.add('dash-pressing'));
        el.addEventListener('pointerup',     clear);
        el.addEventListener('pointercancel', clear);
        el.addEventListener('pointerleave',  clear);
    });

    /* ── Admin real-time search & filter ── */
    document.querySelectorAll('[data-admin-toolbar]').forEach(toolbar => {
        let searchTimer = null;
        const searchInput = toolbar.querySelector('[data-admin-search]');
        if (searchInput) {
            searchInput.addEventListener('input', () => {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => toolbar.submit(), 400);
            });
        }
        toolbar.querySelectorAll('[data-admin-filter]').forEach(filterEl => {
            filterEl.addEventListener('change', () => {
                clearTimeout(searchTimer);
                toolbar.submit();
            });
        });
    });

    /* ── SPPD live fragment ── */
    document.querySelectorAll('[data-vms-sppd-live]').forEach((root) => {
        const DEBOUNCE_MS = 380;
        const HEADER = 'X-VMS-SPPD-Fragment';
        let timer = null;

        const collectParams = () => {
            const params = new URLSearchParams();
            root.querySelectorAll('form').forEach((form) => {
                new FormData(form).forEach((v, k) => { if (typeof v === 'string') params.set(k, v); });
            });
            return params;
        };
        const resetPaging = (url) => {
            url.searchParams.set('page', '1');
            url.searchParams.delete('pending_page');
            url.searchParams.delete('history_page');
        };
        async function fetchFragment(fullUrl) {
            try {
                const res = await fetch(fullUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest', [HEADER]: '1', Accept: 'text/html' }, credentials: 'same-origin' });
                if (!res.ok) return;
                root.innerHTML = await res.text();
                history.replaceState({}, '', fullUrl);
            } catch (_) { window.location.href = fullUrl; }
        }
        function syncFromForms() {
            const url = new URL(window.location.pathname, window.location.origin);
            collectParams().forEach((v, k) => url.searchParams.set(k, v));
            resetPaging(url); fetchFragment(url.toString());
        }
        root.addEventListener('click', (e) => {
            const th = e.target.closest('th[data-sort]');
            if (th && root.contains(th)) {
                e.preventDefault();
                const key = th.dataset.sort;
                // Determine scope from closest ancestor with data-sort-scope
                const scopeWrap = th.closest('[data-sort-scope]');
                const scope = scopeWrap ? scopeWrap.dataset.sortScope : null;
                const sortName = scope ? `${scope}_sort` : 'sort';
                const dirName  = scope ? `${scope}_dir`  : 'dir';
                const form = root.querySelector('form');
                if (form) {
                    const curSort = form.querySelector(`[name="${sortName}"]`)?.value || '';
                    const curDir  = form.querySelector(`[name="${dirName}"]`)?.value  || '';
                    const next = AdminTableSort.cycleSort(curSort, curDir, key);
                    const sortEl = form.querySelector(`[name="${sortName}"]`);
                    const dirEl  = form.querySelector(`[name="${dirName}"]`);
                    if (sortEl) sortEl.value = next.clear ? '' : next.sort;
                    if (dirEl)  dirEl.value  = next.clear ? '' : next.dir;
                }
                syncFromForms(); return;
            }
            const a = e.target.closest('.tbl-pagination a[href]');
            if (!a || !root.contains(a)) return;
            e.preventDefault(); fetchFragment(a.href);
        });
        root.addEventListener('submit', (e) => {
            const form = e.target.closest('form');
            if (!form || !root.contains(form)) return;
            if ((form.getAttribute('method') || 'get').toLowerCase() !== 'get') return;
            e.preventDefault();
            const url = new URL(form.action || window.location.pathname, window.location.origin);
            new FormData(form).forEach((v, k) => { if (typeof v === 'string') url.searchParams.set(k, v); });
            resetPaging(url); fetchFragment(url.toString());
        });
        root.addEventListener('change', (e) => {
            const sel = e.target.closest('select[name]');
            if (!sel || !root.contains(sel)) return;
            const url = new URL(window.location.pathname, window.location.origin);
            collectParams().forEach((v, k) => url.searchParams.set(k, v));
            resetPaging(url); fetchFragment(url.toString());
        });
        root.addEventListener('input', (e) => {
            const inp = e.target.closest('input[type="search"][name="q"],input[type="text"][name="q"]');
            if (!inp || !root.contains(inp)) return;
            clearTimeout(timer);
            timer = setTimeout(() => {
                const url = new URL(window.location.pathname, window.location.origin);
                collectParams().forEach((v, k) => url.searchParams.set(k, v));
                resetPaging(url); fetchFragment(url.toString());
            }, DEBOUNCE_MS);
        });
    });

    /* ── Portal BBM live fragment ── */
    document.querySelectorAll('[data-vms-bbm-portal-live]').forEach((root) => {
        const DEBOUNCE_MS = 380;
        const HEADER = 'X-VMS-BBM-Portal-Fragment';
        let timer = null;

        const collectParams = () => {
            const params = new URLSearchParams();
            root.querySelectorAll('form').forEach((form) => {
                new FormData(form).forEach((v, k) => { if (typeof v === 'string') params.set(k, v); });
            });
            return params;
        };
        const resetPaging = (url) => url.searchParams.set('page', '1');

        async function fetchFragment(fullUrl) {
            try {
                const res = await fetch(fullUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest', [HEADER]: '1', Accept: 'text/html' }, credentials: 'same-origin' });
                if (!res.ok) return;
                root.innerHTML = await res.text();
                history.replaceState({}, '', fullUrl);
            } catch (_) { window.location.href = fullUrl; }
        }
        function syncFromForms() {
            const url = new URL(window.location.pathname, window.location.origin);
            collectParams().forEach((v, k) => url.searchParams.set(k, v));
            resetPaging(url); fetchFragment(url.toString());
        }
        root.addEventListener('click', (e) => {
            const th = e.target.closest('th[data-sort]');
            if (th && root.contains(th)) {
                e.preventDefault();
                const key = th.dataset.sort;
                const form = root.querySelector('#bbm-portal-filter-form');
                if (form) {
                    const curSort = form.querySelector('[name="sort"]')?.value || '';
                    const curDir  = form.querySelector('[name="dir"]')?.value  || '';
                    const next = AdminTableSort.cycleSort(curSort, curDir, key);
                    const sortEl = form.querySelector('[name="sort"]');
                    const dirEl  = form.querySelector('[name="dir"]');
                    if (sortEl) sortEl.value = next.clear ? '' : next.sort;
                    if (dirEl)  dirEl.value  = next.clear ? '' : next.dir;
                }
                syncFromForms(); return;
            }
            const resetBtn = e.target.closest('[data-bbm-portal-reset]');
            if (resetBtn && root.contains(resetBtn)) {
                e.preventDefault();
                const form = root.querySelector('#bbm-portal-filter-form');
                if (form) {
                    ['q','shift','jenis_pengisian','date_from','date_to'].forEach(n => { const el = form.querySelector(`[name="${n}"]`); if (el) el.value = ''; });
                    const pp = form.querySelector('[name="per_page"]'); if (pp) pp.value = '25';
                    const sortEl = form.querySelector('[name="sort"]'); if (sortEl) sortEl.value = '';
                    const dirEl  = form.querySelector('[name="dir"]');  if (dirEl)  dirEl.value  = '';
                }
                syncFromForms(); return;
            }
            const a = e.target.closest('.tbl-pagination a[href]');
            if (!a || !root.contains(a)) return;
            e.preventDefault(); fetchFragment(a.href);
        });
        root.addEventListener('submit', (e) => {
            const form = e.target.closest('form');
            if (!form || !root.contains(form)) return;
            if ((form.getAttribute('method') || 'get').toLowerCase() !== 'get') return;
            e.preventDefault();
            const url = new URL(form.action || window.location.pathname, window.location.origin);
            new FormData(form).forEach((v, k) => { if (typeof v === 'string') url.searchParams.set(k, v); });
            resetPaging(url); fetchFragment(url.toString());
        });
        root.addEventListener('change', (e) => {
            const t = e.target;
            if (!root.contains(t)) return;
            if (t.matches('select[name]') || t.matches('input[type="date"][name]')) syncFromForms();
        });
        root.addEventListener('input', (e) => {
            const inp = e.target.closest('input[type="search"][name="q"],input[type="text"][name="q"]');
            if (!inp || !root.contains(inp)) return;
            clearTimeout(timer); timer = setTimeout(syncFromForms, DEBOUNCE_MS);
        });
    });

    /* ── Vehicle usage log live fragment ── */
    document.querySelectorAll('[data-vms-vul-logs-live]').forEach((root) => {
        const DEBOUNCE_MS = 380;
        const HEADER = 'X-VMS-VUL-Logs-Fragment';
        let timer = null;

        const collectParams = () => {
            const params = new URLSearchParams();
            root.querySelectorAll('form').forEach((form) => {
                new FormData(form).forEach((v, k) => { if (typeof v === 'string') params.set(k, v); });
            });
            return params;
        };
        const resetPaging = (url) => url.searchParams.set('page', '1');

        async function fetchFragment(fullUrl) {
            try {
                const res = await fetch(fullUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest', [HEADER]: '1', Accept: 'text/html' }, credentials: 'same-origin' });
                if (!res.ok) return;
                root.innerHTML = await res.text();
                history.replaceState({}, '', fullUrl);
            } catch (_) { window.location.href = fullUrl; }
        }
        function syncFromForms() {
            const url = new URL(window.location.pathname, window.location.origin);
            collectParams().forEach((v, k) => url.searchParams.set(k, v));
            resetPaging(url); fetchFragment(url.toString());
        }
        root.addEventListener('click', (e) => {
            const th = e.target.closest('th[data-sort]');
            if (th && root.contains(th)) {
                e.preventDefault();
                const key = th.dataset.sort;
                const form = root.querySelector('#vul-logs-filter-form');
                if (form) {
                    const curSort = form.querySelector('[name="sort"]')?.value || '';
                    const curDir  = form.querySelector('[name="dir"]')?.value  || 'asc';
                    const newDir  = (curSort === key) ? (curDir === 'asc' ? 'desc' : 'asc') : 'asc';
                    const sortEl = form.querySelector('[name="sort"]');
                    const dirEl  = form.querySelector('[name="dir"]');
                    if (sortEl) sortEl.value = key;
                    if (dirEl)  dirEl.value  = newDir;
                }
                syncFromForms(); return;
            }
            const resetBtn = e.target.closest('[data-vul-logs-reset]');
            if (resetBtn && root.contains(resetBtn)) {
                e.preventDefault();
                const form = root.querySelector('#vul-logs-filter-form');
                if (form) {
                    ['q','date_from','date_to'].forEach(n => { const el = form.querySelector(`[name="${n}"]`); if (el) el.value = ''; });
                    const pp = form.querySelector('[name="per_page"]'); if (pp) pp.value = '25';
                    const sortEl = form.querySelector('[name="sort"]'); if (sortEl) sortEl.value = '';
                    const dirEl  = form.querySelector('[name="dir"]');  if (dirEl)  dirEl.value  = '';
                }
                syncFromForms(); return;
            }
            const a = e.target.closest('.tbl-pagination a[href]');
            if (!a || !root.contains(a)) return;
            e.preventDefault(); fetchFragment(a.href);
        });
        root.addEventListener('submit', (e) => {
            const form = e.target.closest('form');
            if (!form || !root.contains(form)) return;
            if ((form.getAttribute('method') || 'get').toLowerCase() !== 'get') return;
            e.preventDefault();
            const url = new URL(form.action || window.location.pathname, window.location.origin);
            new FormData(form).forEach((v, k) => { if (typeof v === 'string') url.searchParams.set(k, v); });
            resetPaging(url); fetchFragment(url.toString());
        });
        root.addEventListener('change', (e) => {
            const t = e.target;
            if (!root.contains(t)) return;
            if (t.matches('select[name]') || t.matches('input[type="date"][name]')) syncFromForms();
        });
        root.addEventListener('input', (e) => {
            const inp = e.target.closest('input[type="search"][name="q"],input[type="text"][name="q"]');
            if (!inp || !root.contains(inp)) return;
            clearTimeout(timer); timer = setTimeout(syncFromForms, DEBOUNCE_MS);
        });
    });

    /* ── Checklist wizard ── */
    const wizardRoot = document.querySelector('[data-checklist-wizard]');
    if (!wizardRoot) return;

    const { default: TomSelect } = await import('tom-select');

    const form         = wizardRoot.querySelector('#checklist-form');
    const steps        = Array.from(wizardRoot.querySelectorAll('.wizard-step'));
    const prevButton   = wizardRoot.querySelector('#wizard-prev');
    const nextButton   = wizardRoot.querySelector('#wizard-next');
    const stepLabel    = wizardRoot.querySelector('#checklist-step-label');
    const progressFill = wizardRoot.querySelector('#checklist-progress-fill');
    const progressPct  = wizardRoot.querySelector('#checklist-progress-pct');
    if (!form || !steps.length) return;

    /* Driver select with paired filtering */
    const driverSelectEls   = wizardRoot.querySelectorAll('[data-driver-select]');
    const tomSelectInstances = {};
    const allDriverOptions   = {};

    const tomSelectConfig = (selectEl) => ({
        allowEmptyOption: false, create: false, maxOptions: 100,
        placeholder: selectEl.dataset.placeholder || 'Pilih Driver',
        closeAfterSelect: true,
        dropdownParent: 'body',
        render: {
            option(data, escape) {
                const iconClass = data.icon || 'bi bi-person';
                const isActive  = data.active === '1';
                return `<div class="driver-option-row ${isActive ? 'is-active' : ''}"><i class="${escape(iconClass)}"></i><span>${escape(data.text)}</span></div>`;
            },
            item(data, escape) {
                const iconClass = data.icon || 'bi bi-person';
                const isActive  = data.active === '1';
                return `<div class="driver-option-row ${isActive ? 'is-active' : ''}"><i class="${escape(iconClass)}"></i><span>${escape(data.text)}</span></div>`;
            },
        },
        onInitialize() { this.removeOption(''); this.refreshOptions(false); },
        onItemAdd()    { this.close(); this.blur(); },
    });

    driverSelectEls.forEach(select => {
        const ts = new TomSelect(select, tomSelectConfig(select));
        tomSelectInstances[select.id] = ts;
        allDriverOptions[select.id]   = { ...ts.options };
    });

    const serahTS  = tomSelectInstances['driver_serah'];
    const terimaTS = tomSelectInstances['driver_terima'];
    if (serahTS && terimaTS) {
        const syncDriverOptions = (changedId, selectedValue, previousValue) => {
            const otherTS = changedId === 'driver_serah' ? terimaTS : serahTS;
            const otherId = changedId === 'driver_serah' ? 'driver_terima' : 'driver_serah';
            if (previousValue && allDriverOptions[otherId][previousValue]) otherTS.addOption(allDriverOptions[otherId][previousValue]);
            if (selectedValue && otherTS.options[selectedValue]) {
                if (otherTS.getValue() === selectedValue) otherTS.clear(true);
                otherTS.removeOption(selectedValue);
            }
            otherTS.refreshOptions(false);
        };
        let prevSerah = serahTS.getValue(), prevTerima = terimaTS.getValue();
        serahTS.on('change',  (value) => { syncDriverOptions('driver_serah',  value, prevSerah);  prevSerah  = value; });
        terimaTS.on('change', (value) => { syncDriverOptions('driver_terima', value, prevTerima); prevTerima = value; });
    }

    let currentStep = 1;
    const totalStep = steps.length;
    let refreshSignaturePads = () => {};

    const previewOverlay = document.getElementById('checklist-preview-overlay');
    const previewClose   = document.getElementById('checklist-preview-close');
    const previewCancel  = document.getElementById('checklist-preview-cancel');
    const previewSubmit  = document.getElementById('checklist-submit');
    const previewSubmitHtml = previewSubmit ? previewSubmit.innerHTML : '';

    const openPreviewModal = () => {
        if (!previewOverlay) return;
        previewOverlay.style.display = 'flex';
        previewOverlay.classList.remove('active');
        void previewOverlay.offsetWidth;
        previewOverlay.classList.add('active');
        document.body.style.overflow = 'hidden';
        const body = previewOverlay.querySelector('.checklist-preview-modal-body');
        if (body) body.scrollTop = 0;
    };

    const closePreviewModal = () => {
        if (!previewOverlay) return;
        previewOverlay.classList.remove('active');
        previewOverlay.style.display = 'none';
        document.body.style.overflow = '';
    };

    previewClose?.addEventListener('click', closePreviewModal);
    previewCancel?.addEventListener('click', closePreviewModal);
    previewOverlay?.addEventListener('click', (e) => {
        if (e.target.id === 'checklist-preview-overlay') closePreviewModal();
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && previewOverlay && previewOverlay.style.display !== 'none') {
            closePreviewModal();
        }
    });

    previewSubmit?.addEventListener('click', async () => {
        await submitChecklist();
    });

    const updateWizardUI = () => {
        steps.forEach(s => s.classList.toggle('active', +s.dataset.step === currentStep));
        const pct = Math.round((currentStep / totalStep) * 100);
        progressFill.style.width = `${pct}%`;
        stepLabel.textContent = `LANGKAH ${currentStep} DARI ${totalStep}`;
        if (progressPct) progressPct.textContent = `${pct}%`;
        prevButton.disabled = currentStep === 1;
        if (currentStep === totalStep) {
            nextButton.classList.remove('final');
            nextButton.innerHTML = `LIHAT PREVIEW <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/></svg>`;
        } else {
            nextButton.classList.remove('final');
            nextButton.innerHTML = `LANJUT <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>`;
        }
        requestAnimationFrame(() => refreshSignaturePads());
    };

    const clSwalDialog = () => ({
        customClass: {
            popup:         'cl-swal-dialog',
            title:         'cl-swal-title',
            confirmButton: 'cl-swal-confirm',
            cancelButton:  'cl-swal-cancel',
            denyButton:    'cl-swal-deny-pdf',
        },
        buttonsStyling: false,
    });

    const clSwalError = (title, text) => Swal.fire({
        icon: 'error',
        title,
        text,
        confirmButtonText: 'Mengerti',
        customClass: {
            popup: 'cl-swal-dialog',
            title: 'cl-swal-title',
            htmlContainer: 'cl-swal-error-text',
            confirmButton: 'cl-swal-confirm',
        },
    });

    const validateCurrentStep = async () => {
        const el = steps.find(s => +s.dataset.step === currentStep);

        if ([2, 3, 4].includes(currentStep)) {
            const current = steps.find(s => +s.dataset.step === currentStep);
            if (!current) return true;
            for (const row of current.querySelectorAll('.checklist-condition-row')) {
                const checked = Array.from(row.querySelectorAll('input[type="radio"]')).find(r => r.checked);
                if (!checked) {
                    await clSwalError('Checklist Belum Lengkap', 'Masih ada kondisi yang belum dipilih (OK / NO).');
                    row.style.borderColor = '#ef4444'; return false;
                }
                if (checked.value === 'no') {
                    const note = row.querySelector('.checklist-item-note');
                    if (!note || !note.value.trim()) {
                        await clSwalError('Keterangan Wajib Diisi', 'Item dengan kondisi "NO" harus diberi keterangan.');
                        note.style.borderColor = '#ef4444'; return false;
                    }
                }
            }
        }

        if (currentStep === 2 || currentStep === 5) {
            for (const input of el.querySelectorAll('[data-required-photo]')) {
                if (!input.files || input.files.length === 0) {
                    await clSwalError('Foto Wajib Diisi', 'Harap unggah semua foto yang diperlukan sebelum melanjutkan.');
                    input.classList.add('is-invalid'); return false;
                }
                input.classList.remove('is-invalid');
            }
        }

        const dynamicContainers = steps.find(s => +s.dataset.step === currentStep)?.querySelectorAll('[data-dynamic-photos][data-min-photos]');
        if (dynamicContainers && dynamicContainers.length) {
            for (const container of dynamicContainers) {
                const minPhotos = +(container.dataset.minPhotos) || 1;
                let filled = 0;
                container.querySelectorAll('input[type="file"]').forEach(input => { if (input.files && input.files.length > 0) filled++; });
                if (filled < minPhotos) {
                    await clSwalError('Foto Belum Cukup', `Minimal ${minPhotos} foto harus diupload pada bagian ini.`);
                    return false;
                }
            }
        }

        if (currentStep === 5) {
            if (!isKmAwalValid || !isKmAkhirValid) {
                await clSwalError('Data Tidak Valid', 'Periksa kembali KM Awal dan KM Akhir.');
                return false;
            }
        }

        if (currentStep === 7) {
            if (!window._sigPadSerah || window._sigPadSerah.isEmpty()) {
                await clSwalError('Tanda Tangan Diperlukan', 'Tanda tangan driver yang menyerahkan belum diisi.'); return false;
            }
            if (!window._sigPadTerima || window._sigPadTerima.isEmpty()) {
                await clSwalError('Tanda Tangan Diperlukan', 'Tanda tangan driver yang menerima belum diisi.'); return false;
            }
        }

        if (!el) return true;
        for (const f of el.querySelectorAll('input:not([type="hidden"]):not([type="checkbox"]):not([data-no-validate]), select, textarea')) {
            if (f.closest('.dynamic-photo-container') && !f.hasAttribute('required')) continue;
            if (!f.checkValidity()) { f.reportValidity(); return false; }
        }
        return true;
    };

    document.querySelectorAll('.checklist-condition-row').forEach(row => {
        row.querySelectorAll('input[type="radio"]').forEach(radio => { radio.addEventListener('change', () => { row.style.borderColor = ''; }); });
        const note = row.querySelector('.checklist-item-note');
        if (note) note.addEventListener('input', () => { note.style.borderColor = ''; });
    });

    prevButton.addEventListener('click', () => {
        if (currentStep > 1) { currentStep--; updateWizardUI(); window.scrollTo({ top: 0, behavior: 'smooth' }); }
    });

    nextButton.addEventListener('click', async () => {
        if (!await validateCurrentStep()) return;
        if (currentStep === totalStep) {
            const konfirmasi = document.getElementById('konfirmasi_data');
            if (konfirmasi && !konfirmasi.checked) {
                await clSwalError('Konfirmasi Diperlukan', 'Anda harus mencentang checkbox konfirmasi data sebelum dapat melihat preview.');
                return;
            }
            populatePreview();
            openPreviewModal();
            return;
        }
        if (currentStep < totalStep) {
            currentStep++;
            updateWizardUI();
            window.scrollTo({ top: 0, behavior: 'smooth' });
            return;
        }
    });

    const populatePreview = () => {
        const container = document.getElementById('preview-content');
        if (!container) return;
        const esc = s => String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        const fv  = name => form.querySelector(`[name="${name}"]`)?.value?.trim() || '—';
        const rv  = name => { const r = form.querySelector(`[name="${name}"]:checked`); return r?.value?.toUpperCase() || '—'; };
        const nv  = name => form.querySelector(`[name="${name}"]`)?.value?.trim() || '';
        const cbv = name => !!form.querySelector(`[name="${name}"]`)?.checked;
        const photoSrc = name => {
            const input = form.querySelector(`[name="${name}"]`);
            const slot  = input?.closest('[data-photo-preview-slot]');
            const img   = slot?.querySelector('.photo-slot-preview');
            return (img && img.style.display !== 'none' && img.src) ? img.src : null;
        };
        const sigSerahUrl  = window._sigPadSerah  && !window._sigPadSerah.isEmpty()  ? window._sigPadSerah.toDataURL()  : null;
        const sigTerimaUrl = window._sigPadTerima && !window._sigPadTerima.isEmpty() ? window._sigPadTerima.toDataURL() : null;
        const badge = v => v === 'OK' ? `<span class="pvw-badge pvw-ok">OK</span>` : v === 'NO' ? `<span class="pvw-badge pvw-no">NO</span>` : `<span class="pvw-badge">—</span>`;
        const pvwRow = (label, value) => `<tr><td class="label">${esc(label)}</td><td>${value}</td></tr>`;
        const pvwSection = (title, body) => `<div class="pvw-section-group" style="margin-bottom: 20px;"><h3 class="sppd-toll-leg-title">${esc(title)}</h3><div>${body}</div></div>`;
        const pvwTable = (items, labels, prefix) => {
            const rows = items.map(k => { const val = rv(`${prefix}_${k}`); const note = nv(`${prefix}_${k}_catatan`); return `<tr><td>${esc(labels[k]||k)}</td><td style="width: 15%; text-align: center;">${badge(val)}</td><td>${note ? esc(note) : '<span class="pvw-none">—</span>'}</td></tr>`; }).join('');
            return `<table class="info-table"><thead><tr><th>Item</th><th style="width: 15%; text-align: center;">Status</th><th>Keterangan</th></tr></thead><tbody>${rows}</tbody></table>`;
        };
        const pvwPhotos = sources => { const imgs = sources.filter(p => p.src).map(p => `<div class="pvw-photo-slot"><img src="${p.src}" alt="${esc(p.label)}"><span>${esc(p.label)}</span></div>`).join(''); return imgs ? `<div class="pvw-photo-grid">${imgs}</div>` : ''; };
        const extItems = ['body_kendaraan','kaca','spion','lampu_utama','lampu_sein','ban','velg','wiper'];
        const extLabels = {body_kendaraan:'Body Kendaraan',kaca:'Kaca',spion:'Spion',lampu_utama:'Lampu Utama',lampu_sein:'Lampu Sein',ban:'Ban',velg:'Velg',wiper:'Wiper'};
        const intItems  = ['jok','dashboard','ac','sabuk_pengaman','audio','kebersihan'];
        const intLabels = {jok:'Jok / Kursi',dashboard:'Dashboard',ac:'AC',sabuk_pengaman:'Sabuk Pengaman',audio:'Audio / Head Unit',kebersihan:'Kebersihan Interior'};
        const msnItems  = ['mesin','oli','radiator','rem','kopling','transmisi','indikator'];
        const msnLabels = {mesin:'Mesin (Suara Normal)',oli:'Oli Mesin',radiator:'Air Radiator',rem:'Rem',kopling:'Kopling (Manual)',transmisi:'Transmisi',indikator:'Indikator Panel'};
        const bbmDate = fv('bbm_terakhir_date'), bbmTime = fv('bbm_terakhir_time');
        const bbmTerakhir = [bbmDate,bbmTime].filter(v=>v!=='—').join(' ')||'—';
        const bbmPhotoSrc = photoSrc('foto_bbm_dashboard');
        const plItems = {stnk:'STNK',kir:'Kartu KIR & QR BBM',dongkrak:'Dongkrak',toolkit:'Toolkit',segitiga:'Segitiga Pengaman',apar:'APAR',ban_cadangan:'Ban Cadangan'};
        const catatanVal = nv('catatan_khusus');
        container.innerHTML = `
            <div class="pvw-notice"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" style="flex-shrink:0"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><path d="M12 8v4M12 16h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg><span>Periksa semua data di bawah ini. Klik <strong>GENERATE PDF</strong> untuk menyimpan dan membuat laporan.</span></div>
            ${pvwSection('A. Identitas Unit', `<table class="info-table">${pvwRow('Tanggal',esc(fv('tanggal')))}${pvwRow('Shift',esc(fv('shift')))}${pvwRow('Jam Serah Terima',esc(fv('jam_serah_terima')))}${pvwRow('Nomor Kendaraan',`<strong>${esc(fv('nomor_kendaraan'))}</strong>`)}${pvwRow('Jenis Kendaraan',esc(fv('jenis_kendaraan')))}${pvwRow('Driver Menyerahkan',esc(fv('driver_serah')))}${pvwRow('Driver Menerima',esc(fv('driver_terima')))}</table>`)}
            ${pvwSection('B. Kondisi Eksterior', pvwTable(extItems,extLabels,'exterior')+pvwPhotos(['depan','kanan','kiri','belakang'].map(s=>({label:s.toUpperCase(),src:photoSrc(`exterior_foto_${s}`)}))))}
            ${pvwSection('C. Kondisi Interior', pvwTable(intItems,intLabels,'interior')+pvwPhotos([1,2,3].map(i=>({label:`Foto ${i}`,src:photoSrc(`interior_foto_${i}`)})))) }
            ${pvwSection('D. Kondisi Mesin',    pvwTable(msnItems,msnLabels,'mesin')+pvwPhotos([1,2,3].map(i=>({label:`Foto ${i}`,src:photoSrc(`mesin_foto_${i}`)})))) }
            ${pvwSection('E. BBM & Kilometer', `<table class="info-table">${pvwRow('Level BBM',`<strong>${esc(fv('level_bbm'))}%</strong>`)}${pvwRow('Pengisian BBM Terakhir',esc(bbmTerakhir))}${pvwRow('KM Awal',esc(fv('km_awal')))}${pvwRow('KM Akhir',esc(fv('km_akhir')))}</table>${bbmPhotoSrc?`<div class="pvw-photo-grid"><div class="pvw-photo-slot"><img src="${bbmPhotoSrc}" alt="Dashboard BBM"><span>Dashboard BBM</span></div></div>`:''}`)}
            ${pvwSection('F. Perlengkapan Unit', `<div class="pvw-perlengkapan-grid">${Object.entries(plItems).map(([k,label])=>{const ada=cbv(`perlengkapan[${k}]`);return `<div class="pvw-perlengkapan-item ${ada?'ada':'tidak'}"><span class="pvw-pl-icon">${ada?'✓':'✗'}</span><span>${esc(label)}</span></div>`;}).join('')}</div>`)}
            ${pvwSection('G. Catatan & Tanda Tangan', `<div style="margin-bottom:14px"><span class="pvw-label" style="display:block;margin-bottom:6px">Catatan Tambahan</span>${catatanVal?`<div class="pvw-catatan">${esc(catatanVal)}</div>`:'<span class="pvw-none">Tidak ada catatan tambahan.</span>'}</div><div class="pvw-sig-grid"><div class="pvw-sig-block"><div class="pvw-sig-label">TTD Driver Menyerahkan</div>${sigSerahUrl?`<img src="${sigSerahUrl}" class="pvw-sig-img" alt="TTD Serah">`:'<div class="pvw-sig-empty">Belum ada tanda tangan</div>'}</div><div class="pvw-sig-block"><div class="pvw-sig-label">TTD Driver Menerima</div>${sigTerimaUrl?`<img src="${sigTerimaUrl}" class="pvw-sig-img" alt="TTD Terima">`:'<div class="pvw-sig-empty">Belum ada tanda tangan</div>'}</div></div>`)}
        `;
    };

    const submitChecklist = async () => {
        const confirmResult = await Swal.fire({
            icon: 'question',
            title: 'Kirim checklist?',
            text: 'Data akan disimpan dan PDF laporan akan dibuat. Lanjutkan?',
            showCancelButton: true,
            confirmButtonText: 'Ya, kirim',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            focusCancel: true,
            ...clSwalDialog(),
        });
        if (!confirmResult.isConfirmed) return;

        if (window._sigPadSerah  && !window._sigPadSerah.isEmpty())  document.getElementById('sig-data-serah').value  = window._sigPadSerah.toDataURL();
        if (window._sigPadTerima && !window._sigPadTerima.isEmpty()) document.getElementById('sig-data-terima').value = window._sigPadTerima.toDataURL();
        const bbmDate = form.querySelector('[name="bbm_terakhir_date"]');
        const bbmTime = form.querySelector('[name="bbm_terakhir_time"]');
        if (bbmDate && bbmDate.value) {
            let hidden = form.querySelector('[name="bbm_terakhir"]');
            if (!hidden) { hidden = document.createElement('input'); hidden.type = 'hidden'; hidden.name = 'bbm_terakhir'; form.appendChild(hidden); }
            hidden.value = bbmDate.value + (bbmTime?.value ? ' ' + bbmTime.value : '');
        }
        const formData = new FormData(form);
        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        if (previewSubmit) {
            previewSubmit.disabled = true;
            previewSubmit.innerHTML = '<span style="display:inline-flex;align-items:center;gap:8px"><svg class="spin-icon" width="18" height="18" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-dasharray="31" stroke-linecap="round"></circle></svg> MEMPROSES...</span>';
        }
        try {
            const res  = await fetch(form.action, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' }, body: formData });
            const data = await res.json();
            if (data.success) {
                [{ pad: window._sigPadSerah, hidden: 'sig-data-serah', hint: 'serah' }, { pad: window._sigPadTerima, hidden: 'sig-data-terima', hint: 'terima' }].forEach(({ pad, hidden, hint }) => {
                    if (pad) pad.clear();
                    const hiddenEl = document.getElementById(hidden); if (hiddenEl) hiddenEl.value = '';
                    const hintEl = wizardRoot.querySelector(`[data-sig-hint="${hint}"]`); if (hintEl) hintEl.classList.remove('hidden');
                });
                closePreviewModal();
                const pdfResult = await Swal.fire({
                    icon: 'success',
                    title: 'PDF Berhasil Dibuat!',
                    text: 'Laporan checklist kendaraan telah berhasil disimpan.',
                    showDenyButton: true,
                    confirmButtonText: 'Kembali ke Dashboard',
                    denyButtonText: 'Lihat PDF',
                    ...clSwalDialog(),
                });
                if (pdfResult.isDenied && data.pdf_url) {
                    window.open(data.pdf_url, '_blank');
                }
                window.location.href = '/dashboard';
            } else {
                closePreviewModal();
                await Swal.fire({
                    icon: 'error',
                    title: 'Gagal Membuat PDF',
                    text: data.message || 'Terjadi kesalahan saat menyimpan data.',
                    confirmButtonText: 'Coba Lagi',
                    ...clSwalDialog(),
                });
                if (previewSubmit) {
                    previewSubmit.disabled = false;
                    previewSubmit.innerHTML = previewSubmitHtml;
                }
            }
        } catch (err) {
            console.error(err);
            closePreviewModal();
            await Swal.fire({
                icon: 'error',
                title: 'Koneksi Bermasalah',
                text: 'Terjadi kesalahan jaringan. Silakan periksa koneksi dan coba lagi.',
                confirmButtonText: 'OK',
                ...clSwalDialog(),
            });
            if (previewSubmit) {
                previewSubmit.disabled = false;
                previewSubmit.innerHTML = previewSubmitHtml;
            }
        }
    };

    form.addEventListener('submit', e => e.preventDefault());

    /* Photo preview & compression */
    async function compressImage(file, quality = 0.75, maxWidth = 1280) {
        return new Promise((resolve) => {
            const img = new Image();
            const objectUrl = URL.createObjectURL(file);
            img.onload = () => {
                URL.revokeObjectURL(objectUrl);
                let width = img.width, height = img.height;
                if (width > maxWidth) { height = Math.round(height * maxWidth / width); width = maxWidth; }
                const canvas = document.createElement('canvas');
                canvas.width = width; canvas.height = height;
                canvas.getContext('2d').drawImage(img, 0, 0, width, height);
                canvas.toBlob(blob => resolve(new File([blob], file.name.replace(/\.\w+$/, '.jpg'), { type: 'image/jpeg', lastModified: Date.now() })), 'image/jpeg', quality);
            };
            img.onerror = () => { URL.revokeObjectURL(objectUrl); resolve(file); };
            img.src = objectUrl;
        });
    }

    const initPhotoSlot = slot => {
        const input = slot.querySelector('[data-photo-single]');
        const preview = slot.querySelector('.photo-slot-preview');
        const placeholder = slot.querySelector('.photo-slot-placeholder');
        const removeBtn = slot.querySelector('.photo-slot-remove');
        if (!input || !preview) return;
        input.setAttribute('capture', 'environment'); input.setAttribute('accept', 'image/*');
        input.addEventListener('change', async () => {
            if (!input.files?.[0]) return;
            const compressed = await compressImage(input.files[0]);
            const dt = new DataTransfer(); dt.items.add(compressed); input.files = dt.files;
            if (preview._blobUrl) URL.revokeObjectURL(preview._blobUrl);
            preview._blobUrl = URL.createObjectURL(compressed);
            preview.src = preview._blobUrl; preview.style.display = 'block';
            if (placeholder) placeholder.style.display = 'none';
            if (removeBtn) removeBtn.style.display = 'flex';
            slot.classList.add('has-file');
        });
        if (removeBtn) removeBtn.addEventListener('click', e => {
            e.preventDefault(); e.stopPropagation();
            input.value = '';
            if (preview._blobUrl) { URL.revokeObjectURL(preview._blobUrl); preview._blobUrl = null; }
            preview.style.display = 'none'; preview.src = '';
            if (placeholder) placeholder.style.display = 'flex';
            removeBtn.style.display = 'none'; slot.classList.remove('has-file');
        });
    };
    wizardRoot.querySelectorAll('[data-photo-preview-slot]').forEach(initPhotoSlot);

    wizardRoot.querySelectorAll('[data-dynamic-photos]').forEach(container => {
        const grid = container.querySelector('.dynamic-photo-grid');
        const addBtn = container.querySelector('[data-add-photo-btn]');
        const section = container.dataset.section;
        const maxSlots = +(container.dataset.max || 3);
        if (!grid || !addBtn) return;
        let slotCount = 1;
        addBtn.addEventListener('click', () => {
            if (slotCount >= maxSlots) return;
            slotCount++;
            const label = document.createElement('label');
            label.className = 'checklist-photo-slot slot-animate-in';
            label.setAttribute('data-photo-preview-slot', '');
            label.innerHTML = `<input type="file" name="${section}_foto_${slotCount}" accept="image/*" capture="environment" data-photo-single><div class="photo-slot-placeholder"><span class="checklist-photo-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none"><rect x="3.5" y="5" width="17" height="13" rx="2" stroke="currentColor" stroke-width="1.8"/><circle cx="9" cy="10" r="1.4" stroke="currentColor" stroke-width="1.6"/><path d="M20 15L15.3 10.5L8 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></span><strong>FOTO ${slotCount}</strong></div><img class="photo-slot-preview" alt="Preview" style="display:none"><button type="button" class="photo-slot-remove" style="display:none" aria-label="Hapus foto">×</button>`;
            grid.insertBefore(label, addBtn);
            initPhotoSlot(label);
            if (slotCount >= maxSlots) addBtn.style.display = 'none';
        });
    });

    /* Nomor kendaraan auto-fill */
    const nomorSelect = document.getElementById('nomor_kendaraan');
    const jenisInput  = document.getElementById('jenis_kendaraan');
    const kmAwalInput = document.getElementById('km_awal');
    if (nomorSelect && jenisInput) {
        nomorSelect.addEventListener('change', async () => {
            jenisInput.value = nomorSelect.options[nomorSelect.selectedIndex]?.dataset?.jenis || '';
            if (nomorSelect.value && kmAwalInput) {
                try {
                    const r = await fetch(`/api/kendaraan/last-km?nomor=${encodeURIComponent(nomorSelect.value)}`);
                    const d = await r.json();
                    lastKmDatabase = d.km || 0;
                    kmAwalInput.dispatchEvent(new Event('input'));
                } catch { kmAwalInput.value = 0; lastKmDatabase = 0; kmAwalInput.dispatchEvent(new Event('input')); }
            }
        });
    }

    /* KM validation */
    const kmAwalError  = document.getElementById('km-awal-error');
    const kmAwalErrTxt = document.getElementById('km-awal-error-text');
    let lastKmDatabase = 0, isKmAwalValid = false, isKmAkhirValid = true;

    if (kmAwalInput && kmAwalError) {
        kmAwalInput.addEventListener('input', () => {
            const rawVal = kmAwalInput.value, val = Number(rawVal);
            if (rawVal === '') {
                kmAwalError.style.display = 'flex'; kmAwalErrTxt.textContent = 'Isi KM Awal';
                kmAwalInput.style.borderColor = '#2563eb'; kmAwalError.classList.replace('km-error-danger','km-error-primary'); isKmAwalValid = false;
            } else if (val !== lastKmDatabase) {
                kmAwalError.style.display = 'flex'; kmAwalErrTxt.textContent = `KM Awal (${val}) tidak sesuai dengan data terakhir.`;
                kmAwalInput.style.borderColor = '#ef4444'; kmAwalError.classList.replace('km-error-primary','km-error-danger'); isKmAwalValid = false;
            } else {
                kmAwalError.style.display = 'none'; kmAwalInput.style.borderColor = '';
                kmAwalError.classList.remove('km-error-danger','km-error-primary'); isKmAwalValid = true;
            }
        });
    }

    const kmAkhirInput = document.getElementById('km_akhir');
    const kmError      = document.getElementById('km-error');
    const kmErrTxt     = document.getElementById('km-error-text');
    if (kmAkhirInput && kmAwalInput && kmError) {
        kmAkhirInput.addEventListener('input', () => {
            const awal = +(kmAwalInput.value)||0, akhir = +(kmAkhirInput.value)||0;
            if (akhir > 0 && akhir < awal) {
                kmError.style.display = 'flex'; kmErrTxt.textContent = `KM Akhir (${akhir}) tidak boleh lebih kecil dari KM Awal (${awal}).`;
                kmAkhirInput.style.borderColor = '#ef4444'; kmError.classList.add('km-error-danger'); isKmAkhirValid = false;
            } else {
                kmError.style.display = 'none'; kmAkhirInput.style.borderColor = '';
                kmError.classList.remove('km-error-danger'); isKmAkhirValid = true;
            }
        });
    }

    /* Signature pads */
    const initSigPad = (canvasId, hintSel, clearSel, dataId) => {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return null;
        const hint    = wizardRoot.querySelector(`[data-sig-hint="${hintSel}"]`);
        const clearBtn = wizardRoot.querySelector(`[data-clear-sig="${clearSel}"]`);
        const resize  = () => {
            const rect = canvas.getBoundingClientRect();
            if (!rect.width || !rect.height) return false;
            const r = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = rect.width * r; canvas.height = rect.height * r;
            const ctx = canvas.getContext('2d');
            ctx.setTransform(1,0,0,1,0,0); ctx.scale(r,r); return true;
        };
        resize();
        const pad = new SignaturePad(canvas, { backgroundColor: 'rgba(255,255,255,0)', penColor: '#0f172a', minWidth: 1.5, maxWidth: 3 });
        pad.addEventListener('beginStroke', () => { if (hint) hint.classList.add('hidden'); });
        if (clearBtn) clearBtn.addEventListener('click', () => { pad.clear(); if (hint) hint.classList.remove('hidden'); const di = document.getElementById(dataId); if (di) di.value = ''; });
        pad._refreshCanvas = () => {
            const data = pad.isEmpty() ? [] : pad.toData();
            if (!resize()) return; pad.clear();
            if (data.length) pad.fromData(data); else if (hint) hint.classList.remove('hidden');
        };
        let rt;
        window.addEventListener('resize', () => { clearTimeout(rt); rt = setTimeout(() => pad._refreshCanvas(), 200); }, { passive: true });
        return pad;
    };
    window._sigPadSerah  = initSigPad('sig-pad-serah',  'serah',  'serah',  'sig-data-serah');
    window._sigPadTerima = initSigPad('sig-pad-terima', 'terima', 'terima', 'sig-data-terima');
    refreshSignaturePads = () => { window._sigPadSerah?._refreshCanvas?.(); window._sigPadTerima?._refreshCanvas?.(); };

    const completeAlert = document.getElementById('form-complete-alert');
    const konfirmasiCb  = document.getElementById('konfirmasi_data');
    if (konfirmasiCb && completeAlert) konfirmasiCb.addEventListener('change', () => { completeAlert.style.display = konfirmasiCb.checked ? 'flex' : 'none'; });

    updateWizardUI();
});

/* ================================================================
   BBM RANGE SLIDER — runs on every page load / Turbo navigation
   ================================================================ */
document.addEventListener('turbo:load', function () {
    const slider  = document.getElementById('bbm-range');
    const display = document.getElementById('bbm-value-display');
    if (!slider) return;

    function updateSlider() {
        const value = slider.value;
        if (display) display.innerHTML = value + '<small>%</small>';
        slider.style.background = `linear-gradient(to right, #facc15 ${value}%, #e5e7eb ${value}%)`;
    }
    updateSlider();
    slider.addEventListener('input', updateSlider);
});
