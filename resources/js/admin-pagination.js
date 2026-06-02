/**
 * Shared table pagination helpers (tbl-pagination namespace).
 */

function pageRange(cur, total) {
    if (total <= 7) {
        return Array.from({ length: total }, (_, i) => i + 1);
    }
    const pages = [1];
    if (cur > 3) pages.push('…');
    for (let i = Math.max(2, cur - 1); i <= Math.min(total - 1, cur + 1); i++) {
        pages.push(i);
    }
    if (cur < total - 2) pages.push('…');
    pages.push(total);
    return pages;
}

export function mountPagination(container, html) {
    if (!container) return;
    container.innerHTML = html || '';
}

export function bindPaginationLinks(container, onNavigate, { pathname } = {}) {
    if (!container || typeof onNavigate !== 'function') return;

    container.addEventListener('click', (e) => {
        const link = e.target.closest('a[href]');
        if (!link || !container.contains(link)) return;

        const url = new URL(link.getAttribute('href'), window.location.origin);
        if (pathname && url.pathname !== pathname) return;

        e.preventDefault();
        onNavigate(url);
    });
}

export function bindClientPagination(container, onPage) {
    if (!container || typeof onPage !== 'function') return;

    container.addEventListener('click', (e) => {
        const link = e.target.closest('[data-page]');
        if (!link || !container.contains(link)) return;
        e.preventDefault();
        const page = parseInt(link.dataset.page, 10);
        if (!Number.isNaN(page)) onPage(page);
    });
}

export function buildClientPagination({ currentPage, lastPage }) {
    if (lastPage <= 1) return '';

    const cur = currentPage;
    const range = pageRange(cur, lastPage);
    let links = '';

    if (cur > 1) {
        links += `<a href="#" data-page="${cur - 1}" aria-label="Halaman sebelumnya">‹</a>`;
    } else {
        links += '<span aria-disabled="true"><span>‹</span></span>';
    }

    let prev = null;
    for (const p of range) {
        if (p === '…') {
            links += '<span class="relative"><span>…</span></span>';
        } else if (p === cur) {
            links += `<span aria-current="page"><span>${p}</span></span>`;
        } else {
            links += `<a href="#" data-page="${p}">${p}</a>`;
        }
        prev = p;
    }

    if (cur < lastPage) {
        links += `<a href="#" data-page="${cur + 1}" aria-label="Halaman berikutnya">›</a>`;
    } else {
        links += '<span aria-disabled="true"><span>›</span></span>';
    }

    return (
        '<div class="tbl-pagination-scroll">' +
        '<div class="tbl-pagination tbl-pagination--unified">' +
        '<nav role="navigation" aria-label="Pagination">' +
        '<div aria-hidden="true"></div>' +
        `<div><div>${links}</div></div>` +
        '</nav></div></div>'
    );
}
