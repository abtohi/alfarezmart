/**
 * AlfarezMart — Desktop Enhancement Layer
 * Handles: device detection, sidebar toggle, keyboard shortcuts,
 * hardware barcode scanner, and resize events.
 *
 * IMPORTANT: All desktop-specific code is gated behind isDesktop checks.
 * On mobile, this file is essentially a no-op.
 */
(function () {
    'use strict';

    // ──────────────────────────────────────────
    // 1. Device Detection & Body Classes
    // ──────────────────────────────────────────
    const DESKTOP_BREAKPOINT = 1024;
    const TABLET_BREAKPOINT = 768;

    function updateDeviceClasses() {
        const w = window.innerWidth;
        const isLandscape = window.innerHeight < window.innerWidth;
        const body = document.body;

        body.classList.toggle('is-desktop', w >= DESKTOP_BREAKPOINT);
        body.classList.toggle('is-tablet', w >= TABLET_BREAKPOINT && w < DESKTOP_BREAKPOINT);
        body.classList.toggle('is-mobile', w < TABLET_BREAKPOINT);
        body.classList.toggle('is-landscape', isLandscape);
        body.classList.toggle('is-portrait', !isLandscape);
    }

    updateDeviceClasses();

    let resizeTimer;
    window.addEventListener('resize', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(updateDeviceClasses, 100);
    });

    // Also listen to orientation change for mobile
    window.addEventListener('orientationchange', function () {
        setTimeout(updateDeviceClasses, 200);
    });


    // ──────────────────────────────────────────
    // 2. Viewport Meta Control
    // ──────────────────────────────────────────
    // On desktop: allow zoom. On mobile: prevent zoom for app-like feel.
    function updateViewport() {
        const meta = document.querySelector('meta[name="viewport"]');
        if (!meta) return;

        if (window.innerWidth >= DESKTOP_BREAKPOINT) {
            meta.setAttribute('content', 'width=device-width, initial-scale=1.0');
        } else {
            meta.setAttribute('content', 'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no');
        }
    }
    updateViewport();
    window.addEventListener('resize', function () {
        clearTimeout(window._vpTimer);
        window._vpTimer = setTimeout(updateViewport, 300);
    });


    // ──────────────────────────────────────────
    // 3. Sidebar Collapse Toggle
    // ──────────────────────────────────────────
    const SIDEBAR_KEY = 'alfarezmart_sidebar_collapsed';

    window.toggleSidebar = function () {
        const sidebar = document.getElementById('desktopSidebar');
        if (!sidebar) return;

        const isCollapsed = sidebar.classList.toggle('collapsed');
        document.body.classList.toggle('sidebar-collapsed', isCollapsed);
        localStorage.setItem(SIDEBAR_KEY, isCollapsed ? '1' : '0');
    };

    // Restore sidebar state on load
    document.addEventListener('DOMContentLoaded', function () {
        if (window.innerWidth < DESKTOP_BREAKPOINT) return;

        const sidebar = document.getElementById('desktopSidebar');
        if (!sidebar) return;

        const saved = localStorage.getItem(SIDEBAR_KEY);
        if (saved === '1') {
            sidebar.classList.add('collapsed');
            document.body.classList.add('sidebar-collapsed');
        }
    });


    // ──────────────────────────────────────────
    // 4. Hardware Barcode Scanner Detection
    // ──────────────────────────────────────────
    // USB/Bluetooth barcode scanners send rapid keystrokes ending with Enter.
    // We detect sequences of characters typed within 80ms intervals.
    let _scanBuffer = '';
    let _scanTimeout = null;
    const SCAN_CHAR_TIMEOUT = 80; // ms between keystrokes
    const SCAN_MIN_LENGTH = 4;    // minimum barcode length

    document.addEventListener('keypress', function (e) {
        // Only intercept when no input is focused
        const active = document.activeElement;
        const isInput = active && (
            active.tagName === 'INPUT' ||
            active.tagName === 'TEXTAREA' ||
            active.contentEditable === 'true'
        );

        if (isInput) return; // Let natural input handling work

        // Ignore modifier keys
        if (e.ctrlKey || e.altKey || e.metaKey) return;

        if (e.key === 'Enter') {
            if (_scanBuffer.length >= SCAN_MIN_LENGTH) {
                e.preventDefault();
                const code = _scanBuffer.trim();
                _scanBuffer = '';

                // Dispatch custom event for any page to handle
                document.dispatchEvent(new CustomEvent('hardware-barcode-scanned', {
                    detail: { code: code }
                }));

                _routeBarcodeScan(code);
            }
            _scanBuffer = '';
            return;
        }

        // Only accept printable characters
        if (e.key.length === 1) {
            _scanBuffer += e.key;
            clearTimeout(_scanTimeout);
            _scanTimeout = setTimeout(function () {
                _scanBuffer = '';
            }, SCAN_CHAR_TIMEOUT);
        }
    });

    function _routeBarcodeScan(code) {
        // Route to the appropriate input on the current page
        // Priority: POS barcode input > Scanner barcode input

        // POS page
        const posInput = document.getElementById('posSearch') ||
                         document.getElementById('posBarcode') ||
                         document.querySelector('[id*="barcode"][id*="input" i]') ||
                         document.querySelector('input[placeholder*="barcode" i]');
        if (posInput) {
            posInput.value = code;
            posInput.dispatchEvent(new Event('input', { bubbles: true }));
            posInput.dispatchEvent(new Event('change', { bubbles: true }));
            // Try to trigger lookup
            const form = posInput.closest('form');
            if (form) {
                form.dispatchEvent(new Event('submit', { bubbles: true }));
            } else if (typeof window.lookupBarcode === 'function') {
                window.lookupBarcode();
            } else if (typeof window.addByBarcode === 'function') {
                window.addByBarcode(code);
            }
            // Play beep
            if (typeof window.playBarcodeBeep === 'function') window.playBarcodeBeep();
            return;
        }

        // Scanner page
        const scannerInput = document.getElementById('barcodeInput');
        if (scannerInput) {
            scannerInput.value = code;
            scannerInput.select();
            if (typeof window.lookupBarcode === 'function') {
                window.lookupBarcode();
            }
            return;
        }

        // Fallback: show toast
        if (typeof window.showToast === 'function') {
            window.showToast('Barcode terdeteksi: ' + code, 'info');
        }
    }


    // ──────────────────────────────────────────
    // 5. Keyboard Shortcuts (Desktop Only)
    // ──────────────────────────────────────────
    document.addEventListener('keydown', function (e) {
        if (window.innerWidth < DESKTOP_BREAKPOINT) return;

        const active = document.activeElement;
        const isInput = active && (
            active.tagName === 'INPUT' ||
            active.tagName === 'TEXTAREA' ||
            active.contentEditable === 'true'
        );

        // F2 → Focus barcode/search input
        if (e.key === 'F2') {
            e.preventDefault();
            const target = document.getElementById('posSearch') ||
                           document.getElementById('posBarcode') ||
                           document.getElementById('barcodeInput') ||
                           document.getElementById('productSearchInput') ||
                           document.getElementById('globalSearch');
            if (target) {
                target.focus();
                target.select();
            }
        }

        // F9 → Trigger pay / checkout (POS)
        if (e.key === 'F9') {
            e.preventDefault();
            const payBtn = document.querySelector('.pos-checkout-bar__btn-pay') ||
                           document.querySelector('[onclick*="openPaymentModal"]') ||
                           document.querySelector('[onclick*="proceedCheckout"]');
            if (payBtn) payBtn.click();
        }

        // Ctrl+Shift+S → Quick search
        if (e.ctrlKey && e.shiftKey && e.key === 'S') {
            e.preventDefault();
            const searchBtn = document.getElementById('btnSearch');
            if (searchBtn) searchBtn.click();
        }

        // Escape → Close modals, overlays
        if (e.key === 'Escape' && !isInput) {
            // This is handled by individual modal/overlay close handlers
        }
    });


    // ──────────────────────────────────────────
    // 6. Auto-focus Scanner Input on Desktop
    // ──────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        if (window.innerWidth < DESKTOP_BREAKPOINT) return;

        // Scanner page: auto-focus
        const scannerInput = document.getElementById('barcodeInput');
        if (scannerInput) {
            setTimeout(function () { scannerInput.focus(); }, 500);
        }
    });

})();
