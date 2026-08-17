/**
 * AlfarezMart — Daily Auto-Backup Engine (IndexedDB Edition)
 *
 * Menyimpan snapshot data harian ke IndexedDB untuk keperluan darurat offline.
 * - Penyimpanan data menggunakan IndexedDB (Kapasitas Gigabytes, tidak ada limit 5MB localStorage).
 * - Index metadata ringan (date, size, counts) disimpan untuk UI.
 * - Backup TIDAK disimpan di server/database, hanya di browser lokal.
 * - Backup TIDAK akan dihapus oleh AppCleaner.
 *
 * Aturan:
 *   - Backup otomatis 1x per hari saat aplikasi dibuka
 *   - Menyimpan produk, suppliers, categories, brands, units
 *   - Riwayat maksimal 14 hari (2 minggu)
 *   - Backup per hari tidak pernah di-overwrite (kecuali force=true)
 *   - Backup lebih dari 14 hari otomatis dihapus
 */

window.DailyBackup = (function () {
    'use strict';

    const PREFIX_KEY   = 'alfarezmart_daily_backup_';
    const INDEX_KEY    = 'alfarezmart_daily_backup_index';
    const MAX_DAYS     = 14;

    const IDB_NAME     = 'AlfarezMartDailyBackupDB';
    const IDB_STORE    = 'daily_snapshots';

    /* ─────────────────────────────────────────────────
       INDEXEDDB STORAGE ENGINE (Unlimited Quota)
    ───────────────────────────────────────────────── */

    function _openIDB() {
        return new Promise((resolve, reject) => {
            if (!('indexedDB' in window)) {
                return reject(new Error('IndexedDB tidak didukung oleh browser ini'));
            }
            const req = indexedDB.open(IDB_NAME, 1);
            req.onupgradeneeded = (e) => {
                const db = e.target.result;
                if (!db.objectStoreNames.contains(IDB_STORE)) {
                    db.createObjectStore(IDB_STORE, { keyPath: 'date' });
                }
            };
            req.onsuccess = (e) => resolve(e.target.result);
            req.onerror = (e) => reject(e.target.error);
        });
    }

    async function _saveToIDB(dateStr, data) {
        try {
            const db = await _openIDB();
            return new Promise((resolve, reject) => {
                const tx = db.transaction(IDB_STORE, 'readwrite');
                const store = tx.objectStore(IDB_STORE);
                const req = store.put({ date: dateStr, data: data, updatedAt: Date.now() });
                req.onsuccess = () => resolve(true);
                req.onerror = (e) => reject(e.target.error);
            });
        } catch(e) {
            console.warn('[DailyBackup] Gagal simpan ke IndexedDB:', e);
            throw e;
        }
    }

    async function _getFromIDB(dateStr) {
        try {
            const db = await _openIDB();
            return new Promise((resolve, reject) => {
                const tx = db.transaction(IDB_STORE, 'readonly');
                const store = tx.objectStore(IDB_STORE);
                const req = store.get(dateStr);
                req.onsuccess = () => {
                    const record = req.result;
                    resolve(record ? record.data : null);
                };
                req.onerror = (e) => reject(e.target.error);
            });
        } catch(e) {
            console.warn('[DailyBackup] Gagal membaca dari IndexedDB:', e);
            return null;
        }
    }

    async function _removeFromIDB(dateStr) {
        try {
            const db = await _openIDB();
            return new Promise((resolve, reject) => {
                const tx = db.transaction(IDB_STORE, 'readwrite');
                const store = tx.objectStore(IDB_STORE);
                const req = store.delete(dateStr);
                req.onsuccess = () => resolve(true);
                req.onerror = (e) => reject(e.target.error);
            });
        } catch(e) {}
    }

    async function _clearAllIDB() {
        try {
            const db = await _openIDB();
            return new Promise((resolve, reject) => {
                const tx = db.transaction(IDB_STORE, 'readwrite');
                const store = tx.objectStore(IDB_STORE);
                const req = store.clear();
                req.onsuccess = () => resolve(true);
                req.onerror = (e) => reject(e.target.error);
            });
        } catch(e) {}
    }

    /* ─────────────────────────────────────────────────
       CLEANUP & MIGRATE LEGACY LOCALSTORAGE
    ───────────────────────────────────────────────── */
    (function _cleanupLegacyLocalStorage() {
        try {
            const keysToRemove = [];
            for (let i = 0; i < localStorage.length; i++) {
                const k = localStorage.key(i);
                if (k && k.startsWith(PREFIX_KEY) && k !== INDEX_KEY) {
                    keysToRemove.push(k);
                }
            }
            keysToRemove.forEach(k => {
                try {
                    // Try to migrate into IDB if possible
                    const raw = localStorage.getItem(k);
                    if (raw) {
                        const dateStr = k.replace(PREFIX_KEY, '');
                        try {
                            const parsed = JSON.parse(raw);
                            _saveToIDB(dateStr, parsed).catch(() => {});
                        } catch(pe) {}
                    }
                    localStorage.removeItem(k);
                } catch(e) {}
            });
        } catch(e) {}
    })();

    /* ─────────────────────────────────────────────────
       HELPERS
    ───────────────────────────────────────────────── */

    function _todayStr() {
        const d = new Date();
        const y = d.getFullYear();
        const m = String(d.getMonth() + 1).padStart(2, '0');
        const dd = String(d.getDate()).padStart(2, '0');
        return `${y}-${m}-${dd}`;
    }

    function _formatDateDisplay(dateStr) {
        if (!dateStr) return '-';
        try {
            const parts = dateStr.split('-');
            const d = new Date(Number(parts[0]), Number(parts[1]) - 1, Number(parts[2]));
            const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
            const days   = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
            return `${days[d.getDay()]}, ${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}`;
        } catch(e) { return dateStr; }
    }

    function _formatBytes(bytes) {
        if (!bytes || bytes === 0) return '0 B';
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
    }

    function _getIndex() {
        try {
            const raw = localStorage.getItem(INDEX_KEY);
            return raw ? JSON.parse(raw) : [];
        } catch(e) { return []; }
    }

    function _saveIndex(index) {
        try {
            localStorage.setItem(INDEX_KEY, JSON.stringify(index));
        } catch(e) {
            console.warn('[DailyBackup] Gagal menyimpan index metadata:', e);
        }
    }

    async function _getBackupData(dateStr) {
        // 1. Baca dari IndexedDB
        const fromIDB = await _getFromIDB(dateStr);
        if (fromIDB) return fromIDB;

        // 2. Fallback jika masih ada di legacy localStorage
        try {
            const raw = localStorage.getItem(PREFIX_KEY + dateStr);
            return raw ? JSON.parse(raw) : null;
        } catch(e) { return null; }
    }

    /* ─────────────────────────────────────────────────
       CORE: Collect data from OfflineDB (IndexedDB)
    ───────────────────────────────────────────────── */

    async function _collectData() {
        const result = {
            products: [],
            suppliers: [],
            categories: [],
            brands: [],
            units: [],
            supplier_products: [],
            collected_at: new Date().toISOString()
        };

        if (typeof OfflineDB === 'undefined' && typeof db === 'undefined') {
            throw new Error('OfflineDB belum tersedia');
        }

        // Collect products
        try {
            if (typeof OfflineDB !== 'undefined' && typeof OfflineDB.getAllProducts === 'function') {
                result.products = await OfflineDB.getAllProducts() || [];
            } else if (typeof db !== 'undefined' && db.products) {
                result.products = await db.products.toArray() || [];
            }
        } catch(e) {
            console.warn('[DailyBackup] Could not collect products:', e);
        }

        // Collect suppliers
        try {
            if (typeof OfflineDB !== 'undefined' && typeof OfflineDB.getAllSuppliers === 'function') {
                result.suppliers = await OfflineDB.getAllSuppliers() || [];
            } else if (typeof db !== 'undefined' && db.suppliers) {
                result.suppliers = await db.suppliers.toArray() || [];
            }
        } catch(e) {
            console.warn('[DailyBackup] Could not collect suppliers:', e);
        }

        // Collect categories
        try {
            if (typeof OfflineDB !== 'undefined' && typeof OfflineDB.getAllCategories === 'function') {
                result.categories = await OfflineDB.getAllCategories() || [];
            } else if (typeof db !== 'undefined' && db.categories) {
                result.categories = await db.categories.toArray() || [];
            }
        } catch(e) {
            console.warn('[DailyBackup] Could not collect categories:', e);
        }

        // Collect brands
        try {
            if (typeof OfflineDB !== 'undefined' && typeof OfflineDB.getAllBrands === 'function') {
                result.brands = await OfflineDB.getAllBrands() || [];
            } else if (typeof db !== 'undefined' && db.brands) {
                result.brands = await db.brands.toArray() || [];
            }
        } catch(e) {}

        // Collect units
        try {
            if (typeof OfflineDB !== 'undefined' && typeof OfflineDB.getAllUnits === 'function') {
                result.units = await OfflineDB.getAllUnits() || [];
            } else if (typeof db !== 'undefined' && db.units) {
                result.units = await db.units.toArray() || [];
            }
        } catch(e) {}

        // Collect supplier_products
        try {
            if (typeof OfflineDB !== 'undefined' && typeof OfflineDB.getAllSupplierProducts === 'function') {
                result.supplier_products = await OfflineDB.getAllSupplierProducts() || [];
            } else if (typeof db !== 'undefined' && db.supplier_products) {
                result.supplier_products = await db.supplier_products.toArray() || [];
            }
        } catch(e) {}

        return result;
    }

    /* ─────────────────────────────────────────────────
       CORE: Prune backups older than MAX_DAYS
    ───────────────────────────────────────────────── */

    function _pruneOldBackups() {
        const index = _getIndex();
        if (!index.length) return;

        const cutoff = new Date();
        cutoff.setDate(cutoff.getDate() - MAX_DAYS);
        const cutoffStr = cutoff.getFullYear() + '-'
            + String(cutoff.getMonth() + 1).padStart(2, '0') + '-'
            + String(cutoff.getDate()).padStart(2, '0');

        const surviving = index.filter(entry => {
            if (entry.date < cutoffStr) {
                // Remove data from IDB & legacy localStorage
                _removeFromIDB(entry.date).catch(() => {});
                try { localStorage.removeItem(PREFIX_KEY + entry.date); } catch(e) {}
                return false;
            }
            return true;
        });

        if (surviving.length !== index.length) {
            _saveIndex(surviving);
        }

        return surviving;
    }

    /* ─────────────────────────────────────────────────
       PUBLIC: Run daily backup check (called on app start)
    ───────────────────────────────────────────────── */

    async function runDailyCheck(force = false) {
        const today = _todayStr();

        // First, prune old backups
        _pruneOldBackups();

        // Check if today's backup already exists
        const index = _getIndex();
        const todayEntry = index.find(e => e.date === today);

        if (todayEntry && !force) {
            return { skipped: true, date: today, reason: 'already_backed_up' };
        }

        // Collect data
        let data;
        try {
            data = await _collectData();
        } catch(e) {
            console.warn('[DailyBackup] Data collection failed:', e);
            return { skipped: true, error: e.message };
        }

        // Check if we have meaningful data
        const totalCount = (data.products || []).length + (data.suppliers || []).length;
        if (totalCount === 0 && !force) {
            return { skipped: true, reason: 'no_data_available' };
        }

        // Optimize product snapshot size (strip photos, description, and heavy blobs)
        if (data.products && Array.isArray(data.products)) {
            data.products = data.products.map(p => ({
                id: p.id,
                code: p.code || '',
                full_name: p.full_name || '',
                short_label: p.short_label || '',
                brand_id: p.brand_id,
                brand_name: p.brand_name || '',
                category_id: p.category_id,
                category_name: p.category_name || '',
                product_type: p.product_type || '',
                variant: p.variant || '',
                weight_value: p.weight_value,
                weight_unit: p.weight_unit,
                is_available: p.is_available ?? 1,
                is_active: p.is_active ?? 1,
                packagings: (p.packagings || []).map(pkg => ({
                    id: pkg.id,
                    level: pkg.level,
                    unit_id: pkg.unit_id,
                    unit_name: pkg.unit_name || '',
                    base_qty: pkg.base_qty || 1,
                    contained_qty: pkg.contained_qty || 1,
                    barcode: pkg.barcode || '',
                    buy_price: pkg.buy_price || 0,
                    sell_price_retail: pkg.sell_price_retail || 0,
                    sell_price_wholesale: pkg.sell_price_wholesale || 0,
                    is_default_scan: pkg.is_default_scan || 0
                }))
            }));
        }

        // Estimate size
        let finalSize = 0;
        try {
            finalSize = new Blob([JSON.stringify(data)]).size;
        } catch(e) {
            finalSize = 500000;
        }

        // Save snapshot to IndexedDB (No quota limit)
        try {
            await _saveToIDB(today, data);
        } catch(idbErr) {
            console.error('[DailyBackup] Gagal menyimpan snapshot ke IndexedDB:', idbErr);
            return { skipped: true, error: 'idb_save_failed' };
        }

        // Update lightweight index in localStorage
        const newEntry = {
            date: today,
            label: _formatDateDisplay(today),
            size: finalSize,
            size_label: _formatBytes(finalSize),
            counts: {
                products: (data.products || []).length,
                suppliers: (data.suppliers || []).length,
                categories: (data.categories || []).length
            },
            ts: Date.now()
        };

        // Remove existing today entry if force
        const updatedIndex = index.filter(e => e.date !== today);
        updatedIndex.push(newEntry);

        // Sort desc by date
        updatedIndex.sort((a, b) => b.date.localeCompare(a.date));

        // Keep only MAX_DAYS entries
        if (updatedIndex.length > MAX_DAYS) {
            const removed = updatedIndex.splice(MAX_DAYS);
            removed.forEach(e => {
                _removeFromIDB(e.date).catch(() => {});
                try { localStorage.removeItem(PREFIX_KEY + e.date); } catch(er) {}
            });
        }

        _saveIndex(updatedIndex);

        return {
            success: true,
            date: today,
            size: finalSize,
            size_label: _formatBytes(finalSize),
            counts: newEntry.counts
        };
    }

    /* ─────────────────────────────────────────────────
       PUBLIC: Get backup list (for UI)
    ───────────────────────────────────────────────── */

    function getBackupList() {
        _pruneOldBackups();
        return _getIndex();
    }

    /* ─────────────────────────────────────────────────
       PUBLIC: Restore backup to OfflineDB
    ───────────────────────────────────────────────── */

    async function restoreFromBackup(dateStr) {
        if (!dateStr) throw new Error('Tanggal backup tidak valid');

        const data = await _getBackupData(dateStr);
        if (!data) throw new Error(`Backup tanggal ${dateStr} tidak ditemukan`);

        if (typeof OfflineDB === 'undefined') {
            throw new Error('OfflineDB belum tersedia — coba refresh halaman');
        }

        // Ensure OfflineDB is initialized
        if (typeof OfflineDB.init === 'function') {
            try { await OfflineDB.init(); } catch(e) {}
        }

        let restoredCounts = { products: 0, suppliers: 0, categories: 0 };

        // Use saveFromPayload if available (cleanest approach)
        if (typeof OfflineDB.saveFromPayload === 'function') {
            try {
                await OfflineDB.saveFromPayload(data, function(key) {
                    // Progress callback — silent
                });
                restoredCounts.products = (data.products || []).length;
                restoredCounts.suppliers = (data.suppliers || []).length;
                restoredCounts.categories = (data.categories || []).length;
            } catch(e) {
                console.warn('[DailyBackup] saveFromPayload failed, trying _saveAll:', e);
                // Fallback to _saveAll
                if (typeof OfflineDB._saveAll === 'function') {
                    if (data.products && data.products.length > 0) {
                        try { await OfflineDB._saveAll('products', data.products); restoredCounts.products = data.products.length; } catch(e2) {}
                    }
                    if (data.suppliers && data.suppliers.length > 0) {
                        try { await OfflineDB._saveAll('suppliers', data.suppliers); restoredCounts.suppliers = data.suppliers.length; } catch(e2) {}
                    }
                    if (data.categories && data.categories.length > 0) {
                        try { await OfflineDB._saveAll('categories', data.categories); restoredCounts.categories = data.categories.length; } catch(e2) {}
                    }
                }
            }
        } else if (typeof OfflineDB._saveAll === 'function') {
            // Direct _saveAll fallback
            if (data.products && data.products.length > 0) {
                try { await OfflineDB._saveAll('products', data.products); restoredCounts.products = data.products.length; } catch(e) {}
            }
            if (data.suppliers && data.suppliers.length > 0) {
                try { await OfflineDB._saveAll('suppliers', data.suppliers); restoredCounts.suppliers = data.suppliers.length; } catch(e) {}
            }
            if (data.categories && data.categories.length > 0) {
                try { await OfflineDB._saveAll('categories', data.categories); restoredCounts.categories = data.categories.length; } catch(e) {}
            }
        }

        // Save restore metadata to localStorage for UI awareness
        localStorage.setItem('alfarezmart_active_restore', JSON.stringify({
            date: dateStr,
            label: _formatDateDisplay(dateStr),
            restoredAt: new Date().toISOString(),
            counts: restoredCounts
        }));

        return { success: true, date: dateStr, counts: restoredCounts };
    }

    /* ─────────────────────────────────────────────────
       PUBLIC: Clear active restore (back to live mode)
    ───────────────────────────────────────────────── */

    function clearActiveRestore() {
        localStorage.removeItem('alfarezmart_active_restore');
    }

    /* ─────────────────────────────────────────────────
       PUBLIC: Get active restore info
    ───────────────────────────────────────────────── */

    function getActiveRestore() {
        try {
            const raw = localStorage.getItem('alfarezmart_active_restore');
            return raw ? JSON.parse(raw) : null;
        } catch(e) { return null; }
    }

    /* ─────────────────────────────────────────────────
       PUBLIC: Trigger manual backup now
    ───────────────────────────────────────────────── */

    async function runManualBackup() {
        return runDailyCheck(true);
    }

    /* ─────────────────────────────────────────────────
       PUBLIC: Get storage usage stats
    ───────────────────────────────────────────────── */

    function getStorageStats() {
        const index = _getIndex();
        let totalBytes = 0;
        index.forEach(entry => { totalBytes += (entry.size || 0); });

        return {
            count: index.length,
            total_size: totalBytes,
            total_size_label: _formatBytes(totalBytes),
            oldest_date: index.length > 0 ? index[index.length - 1].date : null,
            newest_date: index.length > 0 ? index[0].date : null,
            today_backed: index.some(e => e.date === _todayStr())
        };
    }

    /* ─────────────────────────────────────────────────
       PUBLIC: Delete all backups (manual)
    ───────────────────────────────────────────────── */

    async function deleteAllBackups() {
        await _clearAllIDB();
        const index = _getIndex();
        index.forEach(entry => {
            try { localStorage.removeItem(PREFIX_KEY + entry.date); } catch(e) {}
        });
        try { localStorage.removeItem(INDEX_KEY); } catch(e) {}
        clearActiveRestore();
    }

    /* ─────────────────────────────────────────────────
       EXPOSE helpers for UI page
    ───────────────────────────────────────────────── */

    return {
        runDailyCheck,
        runManualBackup,
        getBackupList,
        restoreFromBackup,
        clearActiveRestore,
        getActiveRestore,
        getStorageStats,
        deleteAllBackups,
        // Used by OfflineDB restore path
        _formatDateDisplay,
        _formatBytes,
        _todayStr
    };

})();
