/**
 * Admin table header sorting — shared utility.
 *
 * Usage:
 *   AdminTableSort.bindRoot(root, {
 *     getUrl()       — return current URL as URL object or string
 *     onNavigate(url) — called with a URL object when a sort header is clicked
 *     scope          — optional string prefix (e.g. 'pending') → ?pending_sort=&pending_dir=
 *     pageKey        — optional pagination key to reset (default: 'page')
 *   });
 *
 *   AdminTableSort.syncAria(table, activeSort, activeDir)
 *   AdminTableSort.cycleSort(currentSort, currentDir, clickedKey)
 */

const ICON_CLASSES = {
    none: 'bi bi-arrow-down-up',
    asc:  'bi bi-arrow-up',
    desc: 'bi bi-arrow-down',
};

/**
 * @returns {{ sort: string, dir: string, clear: boolean }}
 */
function cycleSort(currentSort, currentDir, key) {
    if (currentSort !== key) {
        return { sort: key, dir: 'asc', clear: false };
    }
    if (currentDir === 'asc') {
        return { sort: key, dir: 'desc', clear: false };
    }
    return { sort: '', dir: '', clear: true };
}

function applyIconState(th, state) {
    th.dataset.sortState = state;
    const icon = th.querySelector('.th-sortable__icon');
    if (icon) {
        icon.className = `th-sortable__icon ${ICON_CLASSES[state] || ICON_CLASSES.none}`;
    }
}

function syncAria(tableOrRoot, activeSort, activeDir) {
    if (!tableOrRoot) return;
    tableOrRoot.querySelectorAll('th[data-sort]').forEach((th) => {
        const key = th.dataset.sort;
        if (key === activeSort && activeSort) {
            const state = activeDir === 'desc' ? 'desc' : 'asc';
            th.setAttribute('aria-sort', state === 'asc' ? 'ascending' : 'descending');
            applyIconState(th, state);
        } else {
            th.removeAttribute('aria-sort');
            applyIconState(th, 'none');
        }
    });
}

/**
 * Bind sort-click handler on a container root (event delegation so it
 * survives innerHTML replacement after fragment/JSON swap).
 *
 * Returns an AbortController whose signal you can pass to remove the listener.
 */
function bindRoot(root, options = {}) {
    if (!root) return null;

    const {
        getUrl,
        onNavigate,
        scope     = null,
        pageKey   = 'page',
    } = options;

    const sortParam = scope ? `${scope}_sort` : 'sort';
    const dirParam  = scope ? `${scope}_dir`  : 'dir';

    const abort = new AbortController();

    root.addEventListener('click', (e) => {
        const th = e.target.closest('th[data-sort]');
        if (!th || !root.contains(th)) return;

        const key = th.dataset.sort;
        if (!key) return;

        const baseUrl = typeof getUrl === 'function'
            ? getUrl()
            : new URL(window.location.href);

        const url = baseUrl instanceof URL ? baseUrl : new URL(baseUrl, window.location.origin);

        const currentSort = url.searchParams.get(sortParam) || '';
        const currentDir  = url.searchParams.get(dirParam)  || '';
        const next = cycleSort(currentSort, currentDir, key);

        if (next.clear) {
            url.searchParams.delete(sortParam);
            url.searchParams.delete(dirParam);
        } else {
            url.searchParams.set(sortParam, next.sort);
            url.searchParams.set(dirParam, next.dir);
        }
        url.searchParams.set(pageKey, '1');

        if (typeof onNavigate === 'function') {
            onNavigate(url);
        }
    }, { signal: abort.signal });

    return abort;
}

export { bindRoot, syncAria, cycleSort };
