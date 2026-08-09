/**
 * AlfarezMart PWA - Offline Dexie.js DB
 */

const db = new Dexie('AlfarezMartDB');

db.version(1).stores({
    products: 'id, full_name, brand_id, category_id, code',
    suppliers: 'id, name',
    categories: 'id, name',
    brands: 'id, name',
    units: 'id, name',
    finance: 'id, _type, name', // Accounts and Finance Categories
    finance_logs: 'id, date, category_id, account_id, amount',
    sales: 'id, invoice_number, date',
    purchases: 'id, invoice_number, date',
    debts: 'id, entity_id, entity_type',
    pending_changes: '++id, endpoint, method, payload, timestamp, status',
    auth_cache: 'key'
});

db.version(2).stores({
    supplier_products: 'id, supplier_id, product_id'
});

db.version(3).stores({
    sales_reps: 'id, supplier_id, name, supplier_name'
});

window.OfflineDB = (function() {
    async function init() {
        return db.open();
    }

    async function syncProductsFromServer() {
        try {
            const data = await api(`${BASE_URL}api/products/sync?_t=` + Date.now());
            if (data && data.products) {
                // Preserve pending local products before clearing
                const pendingLocals = await db.products
                    .filter(p => p.is_pending === true || p.is_pending_update === true)
                    .toArray();
                const serverIds = new Set(data.products.map(p => p.id));
                const trulyPending = pendingLocals.filter(p => !serverIds.has(p.id));

                await db.products.clear();
                await db.products.bulkPut(data.products);
                // Re-insert locally-pending products
                if (trulyPending.length > 0) {
                    await db.products.bulkPut(trulyPending);
                }
                return data.products.length;
            }
            return 0;
        } catch (e) {
            console.error("Failed to sync products from server:", e);
            throw e;
        }
    }

    async function syncAllDataFromServer() {
        try {
            const data = await api(`${BASE_URL}api/sync/all?_t=` + Date.now(), { timeout: 60000, silent: true });
            if (data) {
                return await saveFromPayload(data);
            }
            return false;
        } catch (e) {
            console.warn("Background sync warning:", e.message || e);
            return false;
        }
    }

    async function cacheProductImages(products) {
        if (!products || !Array.isArray(products) || typeof caches === 'undefined') return;
        try {
            const cache = await caches.open('alfarezmart-dynamic-v16.0');
            const baseUrl = typeof BASE_URL !== 'undefined' ? BASE_URL : '/';
            
            const photoUrls = [];
            products.forEach(p => {
                if (p.photo) {
                    let photoPath = p.photo;
                    if (!photoPath.startsWith('http') && !photoPath.startsWith('/')) {
                        photoPath = baseUrl + photoPath;
                    }
                    if (!photoUrls.includes(photoPath)) {
                        photoUrls.push(photoPath);
                    }
                }
            });

            if (photoUrls.length === 0) return;

            const BATCH_SIZE = 5;
            for (let i = 0; i < photoUrls.length; i += BATCH_SIZE) {
                const batch = photoUrls.slice(i, i + BATCH_SIZE);
                await Promise.all(batch.map(async (url) => {
                    try {
                        const existing = await cache.match(url);
                        if (!existing) {
                            const res = await fetch(url, { cache: 'no-cache' });
                            if (res.ok) {
                                await cache.put(url, res);
                            }
                        }
                    } catch (err) {
                        console.warn('Pre-cache product photo skipped:', url);
                    }
                }));
            }
        } catch (e) {
            console.error('Error pre-caching product images:', e);
        }
    }

    async function saveFromPayload(data, onStep) {
        const call = (key) => { if (typeof onStep === 'function') onStep(key); };

        await db.transaction('rw', db.products, db.suppliers, db.sales_reps, db.categories, db.brands, db.units, db.finance, db.finance_logs, db.supplier_products, async () => {
            if (data.products && Array.isArray(data.products)) {
                call('products');
                // Preserve pending local products before clearing
                const pendingLocals = await db.products
                    .filter(p => p.is_pending === true || p.is_pending_update === true)
                    .toArray();
                const serverIds = new Set(data.products.map(p => p.id));
                const trulyPending = pendingLocals.filter(p => !serverIds.has(p.id));

                await db.products.clear();
                await db.products.bulkPut(data.products);
                // Re-insert locally-pending products not yet on server
                if (trulyPending.length > 0) {
                    await db.products.bulkPut(trulyPending);
                }
                cacheProductImages(data.products).catch(e => console.warn('Image pre-cache background error:', e));
            }
            if (data.suppliers && Array.isArray(data.suppliers)) {
                call('suppliers');
                await db.suppliers.clear();
                await db.suppliers.bulkPut(data.suppliers);
            }
            if (data.sales_reps && Array.isArray(data.sales_reps)) {
                call('sales_reps');
                await db.sales_reps.clear();
                await db.sales_reps.bulkPut(data.sales_reps);
            }
            if (data.categories && Array.isArray(data.categories)) {
                call('categories');
                await db.categories.clear();
                await db.categories.bulkPut(data.categories);
            }
            if (data.brands && Array.isArray(data.brands)) {
                call('brands');
                await db.brands.clear();
                await db.brands.bulkPut(data.brands);
            }
            if (data.units && Array.isArray(data.units)) {
                call('units');
                await db.units.clear();
                await db.units.bulkPut(data.units);
            }
            if (data.supplier_products && Array.isArray(data.supplier_products)) {
                call('supplier_products');
                await db.supplier_products.clear();
                await db.supplier_products.bulkPut(data.supplier_products);
            }
            if (data.finance) {
                call('finance');
                const financeItems = [];
                if (Array.isArray(data.finance.accounts)) {
                    data.finance.accounts.forEach(a => financeItems.push({ ...a, _type: 'account', id: 'acc_' + a.id }));
                }
                if (Array.isArray(data.finance.categories)) {
                    data.finance.categories.forEach(c => financeItems.push({ ...c, _type: 'finance_cat', id: 'fcat_' + c.id }));
                }
                if (financeItems.length > 0) {
                    await db.finance.clear();
                    await db.finance.bulkPut(financeItems);
                }
            }
            if (data.finance_logs && Array.isArray(data.finance_logs)) {
                call('finance_logs');
                await db.finance_logs.clear();
                await db.finance_logs.bulkPut(data.finance_logs);
            }
        });
        return true;
    }

    // Read methods
    function getAllProducts() { return db.products.toArray(); }
    function getAllSales() { return db.sales.toArray(); }
    function getAllSupplierProducts() { return db.supplier_products.toArray(); }

    /**
     * Offline-first supplier-aware product search with multi-keyword relevance scoring.
     * Returns products sorted by: supplier match first, then relevance score.
     */
    async function searchProductsBySupplier(query, supplierId, salesRepId) {
        if (!query) return [];
        query = query.toLowerCase().trim();
        const words = query.split(/\s+/).filter(w => w.length > 0);
        if (words.length === 0) return [];

        try {
            const [allProducts, allSP] = await Promise.all([
                db.products.toArray(),
                db.supplier_products.toArray()
            ]);

            // Build a set of product IDs that belong to the given supplier
            const supplierProductIds = new Set();
            const supplierProductMap = new Map(); // productId -> {last_buy_price, purchase_count}
            if (supplierId) {
                allSP.forEach(sp => {
                    if (sp.supplier_id == supplierId) {
                        supplierProductIds.add(sp.product_id);
                        supplierProductMap.set(sp.product_id, {
                            last_buy_price: sp.last_buy_price,
                            purchase_count: sp.purchase_count || 0
                        });
                    }
                });
            }

            // Score and filter products
            const scored = [];
            for (const p of allProducts) {
                // Multi-keyword AND match: every word must match at least one field
                const searchText = [
                    p.full_name || '',
                    p.short_label || '',
                    p.invoice_name || '',
                    p.supplier_invoice_name || '',
                    p.brand_name || '',
                    p.code || '',
                    p.supplier_product_code || ''
                ].join(' ').toLowerCase();

                // Also check barcodes
                let barcodeText = '';
                if (p.packagings && Array.isArray(p.packagings)) {
                    barcodeText = p.packagings.map(pkg => pkg.barcode || '').join(' ').toLowerCase();
                }

                const allText = searchText + ' ' + barcodeText;

                // Every word must match somewhere
                const allMatch = words.every(w => allText.includes(w));
                if (!allMatch) continue;

                // Calculate relevance score (higher = better)
                let score = 0;
                const label = (p.short_label || p.full_name || '').toLowerCase();
                const fullName = (p.full_name || '').toLowerCase();

                // Bonus: exact full query match in label
                if (label.includes(query)) score += 100;
                if (fullName.includes(query)) score += 80;

                // Bonus per word: where does it match?
                for (const w of words) {
                    if (label.startsWith(w)) score += 30;
                    else if (label.includes(w)) score += 20;
                    else if (fullName.includes(w)) score += 15;
                    else score += 5; // matched in brand/code/barcode
                }

                // Bonus for supplier product
                const isSupplier = supplierProductIds.has(p.id);
                if (isSupplier) {
                    score += 200; // Strong priority for supplier products
                    const spData = supplierProductMap.get(p.id);
                    if (spData) {
                        score += Math.min(spData.purchase_count, 50); // Frequency bonus (capped)
                        p.last_buy_price = spData.last_buy_price;
                    }
                }

                scored.push({
                    ...p,
                    is_supplier_product: isSupplier ? 1 : 0,
                    _score: score
                });
            }

            // Sort by score descending
            scored.sort((a, b) => b._score - a._score);

            // Clean up internal score before returning
            return scored.slice(0, 30).map(p => {
                const { _score, ...rest } = p;
                return rest;
            });
        } catch (e) {
            console.error("Offline supplier search failed", e);
            return [];
        }
    }
    function getAllSuppliers() { return db.suppliers.toArray(); }
    function getAllSalesReps() { return db.sales_reps.toArray(); }
    function getAllCategories() { return db.categories.toArray(); }
    function getAllBrands() { return db.brands.toArray(); }
    function getAllUnits() { return db.units.toArray(); }
    function getAllPurchases() { return db.purchases.toArray(); }
    function getAllDebts() { return db.debts.toArray(); }
    function getAllFinance() { return db.finance.toArray(); }
    function getAllFinanceLogs() { return db.finance_logs.toArray(); }

    function getProductById(id) {
        return db.products.get(parseInt(id));
    }

    async function searchProducts(query, isPos = false) {
        if (!query) return [];
        query = query.toLowerCase().trim();
        const words = query.split(/\s+/).filter(w => w.length > 0);
        if (words.length === 0) return [];
        
        try {
            return await db.products.filter(p => {
                // Ensure type safety since IndexedDB might store it as a string "0"
                if (isPos && (p.is_available == 0 || p.is_available === '0' || p.is_available === false)) return false;

                return words.every(word => {
                    const nameMatch = (p.full_name && p.full_name.toLowerCase().includes(word)) ||
                                      (p.short_label && p.short_label.toLowerCase().includes(word)) ||
                                      (p.invoice_name && p.invoice_name.toLowerCase().includes(word)) ||
                                      (p.supplier_invoice_name && p.supplier_invoice_name.toLowerCase().includes(word));
                    const brandMatch = p.brand_name && p.brand_name.toLowerCase().includes(word);
                    const codeMatch = p.code && p.code.toLowerCase().includes(word);
                    const supplierCodeMatch = p.supplier_product_code && p.supplier_product_code.toLowerCase().includes(word);
                    
                    let barcodeMatch = false;
                    if (p.packagings && Array.isArray(p.packagings)) {
                        barcodeMatch = p.packagings.some(pkg => pkg.barcode && pkg.barcode.toLowerCase().includes(word));
                    }

                    return nameMatch || brandMatch || codeMatch || supplierCodeMatch || barcodeMatch;
                });
            }).limit(100).toArray();
        } catch (e) {
            console.error("Offline search failed", e);
            return [];
        }
    }

    async function findByBarcode(barcode, isPos = false) {
        if (!barcode) return null;
        const cleanCode = barcode.replace(/\s+/g, '').toLowerCase();
        
        try {
            const products = await db.products.toArray();
            for (const p of products) {
                if (isPos && (p.is_available == 0 || p.is_available === '0' || p.is_available === false)) continue;

                let matchedLevel = null;
                if (p.code) {
                    let pCode = p.code.replace(/\s+/g, '').toLowerCase();
                    if (pCode === cleanCode) matchedLevel = 1;
                }
                
                if (!matchedLevel && p.packagings && Array.isArray(p.packagings)) {
                    const matchedPkg = p.packagings.find(pkg => {
                        if (!pkg.barcode) return false;
                        let b = pkg.barcode.replace(/\s+/g, '').toLowerCase();
                        return b === cleanCode || b === '0' + cleanCode || '0' + b === cleanCode || b === '00' + cleanCode || '00' + b === cleanCode;
                    });
                    if (matchedPkg && matchedPkg.level) {
                        matchedLevel = parseInt(matchedPkg.level, 10);
                    }
                }

                if (matchedLevel) {
                    return {
                        ...p,
                        level: matchedLevel,
                        scanned_barcode: barcode
                    };
                }
            }
            return null;
        } catch (e) {
            console.error("Offline barcode lookup failed", e);
            return null;
        }
    }

    // Write methods
    function saveProduct(product) {
        return db.products.put(product);
    }
    
    function saveFinanceLog(log) {
        return db.finance_logs.put(log);
    }

    // Pending Changes
    function addPendingChange(endpoint, method, payload) {
        return db.pending_changes.add({
            endpoint,
            method,
            payload,
            timestamp: Date.now(),
            status: 'pending'
        });
    }

    function getPendingChanges() {
        return db.pending_changes.orderBy('timestamp').toArray();
    }

    function removePendingChange(id) {
        return db.pending_changes.delete(id);
    }

    function countPending() {
        return db.pending_changes.count();
    }

    function saveAuth(key, data) {
        return db.auth_cache.put({ key, data, timestamp: Date.now() });
    }

    async function getAuth(key) {
        const result = await db.auth_cache.get(key);
        return result ? result.data : null;
    }

    return {
        init,
        syncProductsFromServer,
        syncAllDataFromServer,
        saveFromPayload,
        getAllProducts,
        getAllSales,
        getAllSuppliers,
        getAllSalesReps,
        getAllCategories,
        getAllBrands,
        getAllUnits,
        getAllPurchases,
        getAllDebts,
        getAllFinance,
        getAllFinanceLogs,
        getAllSupplierProducts,
        saveFinanceLog,
        saveProduct,
        getProductById,
        searchProducts,
        searchProductsBySupplier,
        findByBarcode,
        addPendingChange,
        getPendingChanges,
        removePendingChange,
        countPending,
        saveAuth,
        getAuth
    };
})();
