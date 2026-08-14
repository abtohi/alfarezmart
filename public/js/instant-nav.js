/**
 * AlfarezMart PWA - Instant Navigation Optimizer
 * Provides instant prefetching on touchstart/mouseenter to make mobile page transitions ultra-fast (<30ms).
 */
(function() {
    'use strict';

    const prefetchedUrls = new Set();

    function prefetchUrl(url) {
        if (!url || prefetchedUrls.has(url)) return;
        try {
            const parsed = new URL(url, window.location.origin);
            // Only prefetch same-origin HTML pages
            if (parsed.origin !== window.location.origin) return;
            if (parsed.pathname.startsWith('/api/') || parsed.pathname.includes('/logout')) return;
            
            prefetchedUrls.add(url);
            
            // Low priority background fetch into Service Worker cache
            if (window.fetch) {
                fetch(url, { priority: 'low', credentials: 'same-origin' }).catch(() => {});
            }
        } catch (e) {}
    }

    // Attach listeners to document for event delegation
    document.addEventListener('touchstart', handleIntent, { passive: true });
    document.addEventListener('mouseenter', handleIntent, { capture: true, passive: true });

    function handleIntent(event) {
        const link = event.target.closest('a[href]');
        if (!link) return;

        const href = link.getAttribute('href');
        if (!href || href.startsWith('#') || href.startsWith('javascript:') || href.startsWith('tel:') || href.startsWith('mailto:')) {
            return;
        }

        prefetchUrl(href);
    }

    // Instant prefetch visible bottom navigation links after DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', prefetchCriticalNavLinks);
    } else {
        prefetchCriticalNavLinks();
    }

    function prefetchCriticalNavLinks() {
        // Prefetch main bottom navigation / sidebar destinations
        const criticalNav = [
            (typeof BASE_URL !== 'undefined' ? BASE_URL : '/') + 'sales',
            (typeof BASE_URL !== 'undefined' ? BASE_URL : '/') + 'sales/pos',
            (typeof BASE_URL !== 'undefined' ? BASE_URL : '/') + 'products',
            (typeof BASE_URL !== 'undefined' ? BASE_URL : '/') + 'purchases',
            (typeof BASE_URL !== 'undefined' ? BASE_URL : '/') + 'ppob',
            (typeof BASE_URL !== 'undefined' ? BASE_URL : '/') + 'finance',
            (typeof BASE_URL !== 'undefined' ? BASE_URL : '/') + 'reports',
            (typeof BASE_URL !== 'undefined' ? BASE_URL : '/') + 'settings',
        ];

        // Stagger prefetch to avoid consuming network during initial page paint
        let delay = 1500;
        criticalNav.forEach(url => {
            setTimeout(() => {
                prefetchUrl(url);
            }, delay);
            delay += 400;
        });
    }
})();
