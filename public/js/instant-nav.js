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
            if (parsed.pathname.startsWith('/api/') || 
                parsed.pathname.includes('/logout') ||
                parsed.pathname.includes('/login') ||
                parsed.pathname.includes('/setup')) return;
            
            prefetchedUrls.add(url);
            
            // Low priority background fetch into Service Worker cache
            if (window.fetch) {
                fetch(url, { 
                    priority: 'low', 
                    credentials: 'same-origin',
                    headers: { 'X-Prefetch': '1' }
                }).catch(() => {});
            }
        } catch (e) {}
    }

    // Attach listeners to document for event delegation
    document.addEventListener('touchstart', handleIntent, { passive: true });
    document.addEventListener('mouseenter', handleIntent, { capture: true, passive: true });

    function handleIntent(event) {
        if (!event || !event.target) return;
        try {
            // Guard: Handle text nodes, document, window, or elements without .closest
            let target = event.target;
            if (target.nodeType === 3) target = target.parentElement; // Text node -> parent
            if (!target || typeof target.closest !== 'function') return;

            const link = target.closest('a[href]');
            if (!link) return;

            const href = link.getAttribute('href');
            if (!href || href.startsWith('#') || href.startsWith('javascript:') || href.startsWith('tel:') || href.startsWith('mailto:')) {
                return;
            }

            prefetchUrl(href);
        } catch (e) {}
    }

    // Instant prefetch visible bottom navigation links after DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', prefetchCriticalNavLinks);
    } else {
        prefetchCriticalNavLinks();
    }

    function prefetchCriticalNavLinks() {
        const base = (typeof BASE_URL !== 'undefined' ? BASE_URL : '/');
        // Prefetch main bottom navigation & sidebar destinations for instant offline availability
        const criticalNav = [
            base + 'sales/pos',
            base + 'scanner',
            base + 'products',
            base + 'purchases/create',
            base + 'sales',
            base + 'purchases',
            base + 'ppob',
            base + 'finance',
            base + 'reports',
            base + 'settings',
            base + 'settings/error-logs',
            base + 'settings/backup',
        ];

        // Stagger prefetch in background to avoid consuming network during initial page paint
        let delay = 1200;
        criticalNav.forEach(url => {
            setTimeout(() => {
                prefetchUrl(url);
            }, delay);
            delay += 300;
        });
    }
})();
