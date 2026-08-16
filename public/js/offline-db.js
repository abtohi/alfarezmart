/**
 * AlfarezMart PWA - Offline IndexedDB
 */

window.OfflineDB = (function() {
    const DB_NAME = 'alfarezmart_offline';
    const DB_VERSION = 7; // v7: add supplier_products for offline-first search
    const STORE_PRODUCTS = 'products';
    const STORE_SALES = 'sales';
    const STORE_SUPPLIERS = 'suppliers';
    const STORE_SALES_REPS = 'sales_reps';
    const STORE_PURCHASES = 'purchases';
    const STORE_DEBTS = 'debts';
    const STORE_FINANCE = 'finance';
    const STORE_FINANCE_LOGS = 'finance_logs';
    const STORE_CATEGORIES = 'categories';
    const STORE_PENDING = 'pending_changes';
    const STORE_AUTH = 'auth_cache';
    const STORE_SUPPLIER_PRODUCTS = 'supplier_products';

    let db = null;

    function init() {
        return new Promise((resolve, reject) => {
            const request = indexedDB.open(DB_NAME, DB_VERSION);

            request.onerror = (event) => {
                console.error("IndexedDB error:", event.target.error);
                reject(event.target.error);
            };

            request.onsuccess = (event) => {
                db = event.target.result;
                resolve(db);
            };

            request.onupgradeneeded = (event) => {
                const db = event.target.result;
                
                // Products store
                if (!db.objectStoreNames.contains(STORE_PRODUCTS)) {
                    const productStore = db.createObjectStore(STORE_PRODUCTS, { keyPath: 'id' });
                    productStore.createIndex('full_name', 'full_name', { unique: false });
                    productStore.createIndex('short_label', 'short_label', { unique: false });
                    productStore.createIndex('code', 'code', { unique: false });
                }

                // New stores for full offline mode
                [STORE_SALES, STORE_SUPPLIERS, STORE_SALES_REPS, STORE_PURCHASES, STORE_DEBTS, STORE_FINANCE, STORE_FINANCE_LOGS, STORE_CATEGORIES].forEach(storeName => {
                    if (!db.objectStoreNames.contains(storeName)) {
                        db.createObjectStore(storeName, { keyPath: 'id' });
                    }
                });

                // Supplier-Product mappings for offline supplier-aware search
                if (!db.objectStoreNames.contains(STORE_SUPPLIER_PRODUCTS)) {
                    const spStore = db.createObjectStore(STORE_SUPPLIER_PRODUCTS, { keyPath: 'id' });
                    spStore.createIndex('supplier_id', 'supplier_id', { unique: false });
                    spStore.createIndex('product_id', 'product_id', { unique: false });
                }

                // Pending changes store (offline queue)
                if (!db.objectStoreNames.contains(STORE_PENDING)) {
                    db.createObjectStore(STORE_PENDING, { keyPath: 'id', autoIncrement: true });
                }

                // Auth store (for offline login validation if needed)
                if (!db.objectStoreNames.contains(STORE_AUTH)) {
                    db.createObjectStore(STORE_AUTH, { keyPath: 'key' });
                }
            };
        });
    }

    async function syncProductsFromServer() {
        try {
            const data = await api(`${BASE_URL}api/products/sync?_t=` + Date.now());
            if (data && data.products) {
                await _saveAll(STORE_PRODUCTS, data.products);
                return data.products.length;
            }
            return 0;
        } catch (e) {
            console.error("Failed to sync products from server:", e);
            throw e;
        }
    }

    async function cacheProductImages(products) {
        // Images are dynamically cached on-demand by SW during browsing
        return;
    }

    async function syncAllDataFromServer() {
        try {
            if (!db) await init();
            const data = await api(`${BASE_URL}api/sync/all?_t=` + Date.now());
            if (data) {
                // Products
                if (data.products && Array.isArray(data.products)) {
                    await _saveAll(STORE_PRODUCTS, data.products);
                    cacheProductImages(data.products).catch(() => {});
                }
                // Suppliers
                if (data.suppliers && Array.isArray(data.suppliers)) {
                    await _saveAll(STORE_SUPPLIERS, data.suppliers);
                }
                // Sales Reps
                if (data.sales_reps && Array.isArray(data.sales_reps)) {
                    await _saveAll(STORE_SALES_REPS, data.sales_reps);
                }
                // Categories
                if (data.categories && Array.isArray(data.categories)) {
                    await _saveAll(STORE_CATEGORIES, data.categories);
                }
                // Finance: server returns {accounts: [], categories: []}
                // Store as flat array with type prefix for the finance store
                if (data.finance) {
                    const financeItems = [];
                    if (Array.isArray(data.finance.accounts)) {
                        data.finance.accounts.forEach(a => financeItems.push({ ...a, _type: 'account', id: 'acc_' + a.id }));
                    }
                    if (Array.isArray(data.finance.categories)) {
                        data.finance.categories.forEach(c => financeItems.push({ ...c, _type: 'finance_cat', id: 'fcat_' + c.id }));
                    }
                    if (financeItems.length > 0) {
                        await _saveAll(STORE_FINANCE, financeItems);
                    }
                }
                // Finance Logs
                if (data.finance_logs && Array.isArray(data.finance_logs)) {
                    await _saveAll(STORE_FINANCE_LOGS, data.finance_logs);
                }
                // Supplier-Product mappings for offline supplier-aware search
                if (data.supplier_products && Array.isArray(data.supplier_products)) {
                    await _saveAll(STORE_SUPPLIER_PRODUCTS, data.supplier_products);
                }
                // Legacy data (sales, purchases, debts) — may not be in response, ignore
                if (data.sales && Array.isArray(data.sales)) await _saveAll(STORE_SALES, data.sales);
                if (data.purchases && Array.isArray(data.purchases)) await _saveAll(STORE_PURCHASES, data.purchases);
                if (data.debts && Array.isArray(data.debts)) await _saveAll(STORE_DEBTS, data.debts);
                return true;
            }
            return false;
        } catch (e) {
            console.error("Failed to sync all data from server:", e);
            throw e;
        }
    }

    /**
     * Save all items from server into a store.
     * For STORE_PRODUCTS, items marked is_pending or is_pending_update are
     * preserved so locally-created / edited products are never wiped.
     */
    function _saveAll(storeName, items) {
        return new Promise((resolve, reject) => {
            if (!db) return reject("DB not initialized");

            if (storeName !== STORE_PRODUCTS) {
                // Non-product stores: simple clear + bulkput
                const tx = db.transaction([storeName], 'readwrite');
                const st = tx.objectStore(storeName);
                st.clear();
                items.forEach(item => st.put(item));
                tx.oncomplete = () => resolve();
                tx.onerror = (e) => reject(e.target.error);
                return;
            }

            // Products: preserve pending local records
            const txRead = db.transaction([storeName], 'readonly');
            const stRead = txRead.objectStore(storeName);
            const reqAll = stRead.getAll();

            reqAll.onsuccess = () => {
                const existing = reqAll.result || [];
                // Collect pending items not in server list
                const serverIds = new Set(items.map(p => p.id));
                const pendingLocals = existing.filter(p =>
                    (p.is_pending === true || p.is_pending_update === true) &&
                    !serverIds.has(p.id)
                );

                const txWrite = db.transaction([storeName], 'readwrite');
                const stWrite = txWrite.objectStore(storeName);
                stWrite.clear();
                items.forEach(item => stWrite.put(item));
                // Re-insert pending local items (new products not yet on server)
                pendingLocals.forEach(item => stWrite.put(item));

                txWrite.oncomplete = () => resolve();
                txWrite.onerror = (e) => reject(e.target.error);
            };
            reqAll.onerror = (e) => reject(e.target.error);
        });
    }

    function _getAll(storeName) {
        return new Promise((resolve, reject) => {
            if (!db) return reject("DB not initialized");
            const transaction = db.transaction([storeName], 'readonly');
            const store = transaction.objectStore(storeName);
            const request = store.getAll();

            request.onsuccess = () => resolve(request.result);
            request.onerror = (e) => reject(e.target.error);
        });
    }

    function saveProduct(product) {
        return new Promise((resolve, reject) => {
            if (!db) return reject("DB not initialized");
            
            const transaction = db.transaction([STORE_PRODUCTS], 'readwrite');
            const store = transaction.objectStore(STORE_PRODUCTS);
            const request = store.put(product);
            
            request.onsuccess = () => resolve(request.result);
            request.onerror = (e) => reject(e.target.error);
        });
    }

    function getAllProducts() {
        return _getAll(STORE_PRODUCTS);
    }

    function getAllSales() { return _getAll(STORE_SALES); }
    function getAllSuppliers() { return _getAll(STORE_SUPPLIERS); }
    function getAllSalesReps() { return _getAll(STORE_SALES_REPS); }
    function getAllPurchases() { return _getAll(STORE_PURCHASES); }
    function getAllDebts() { return _getAll(STORE_DEBTS); }
    function getAllFinance() { return _getAll(STORE_FINANCE); }
    function getAllFinanceLogs() { return _getAll(STORE_FINANCE_LOGS); }

    function saveFinanceLog(log) {
        return new Promise((resolve, reject) => {
            if (!db) return reject("DB not initialized");
            const transaction = db.transaction([STORE_FINANCE_LOGS], 'readwrite');
            const store = transaction.objectStore(STORE_FINANCE_LOGS);
            const request = store.put(log);
            request.onsuccess = () => resolve(request.result);
            request.onerror = (e) => reject(e.target.error);
        });
    }

    function getProductById(id) {
        return new Promise((resolve, reject) => {
            if (!db) return reject("DB not initialized");
            const transaction = db.transaction([STORE_PRODUCTS], 'readonly');
            const store = transaction.objectStore(STORE_PRODUCTS);
            const request = store.get(parseInt(id));

            request.onsuccess = () => resolve(request.result);
            request.onerror = (e) => reject(e.target.error);
        });
    }

    async function searchProducts(query, isPos = false) {
        if (!query) return [];
        const rawQuery = query.toLowerCase().trim();
        const words = rawQuery.split(/\s+/).filter(w => w.length > 0);
        if (words.length === 0) return [];
        
        try {
            const all = await getAllProducts();
            const results = [];
            
            for (const p of all) {
                if (isPos && p.is_available !== undefined && p.is_available != 1) continue;
                
                let matchesAllWords = true;
                let score = 0;

                const fullName = (p.full_name || '').toLowerCase();
                const shortLabel = (p.short_label || '').toLowerCase();
                const invName = (p.invoice_name || '').toLowerCase();
                const suppInvName = (p.supplier_invoice_name || '').toLowerCase();
                const brandName = (p.brand_name || '').toLowerCase();
                const categoryName = (p.category_name || '').toLowerCase();
                const code = (p.code || '').toLowerCase();
                const suppCode = (p.supplier_product_code || '').toLowerCase();

                // Exact match bonuses
                if (code === rawQuery || suppCode === rawQuery) score += 80;
                if (shortLabel.startsWith(rawQuery) || fullName.startsWith(rawQuery)) score += 60;
                
                for (const word of words) {
                    const isNumWord = !isNaN(word);
                    const nameMatch = fullName.includes(word) || shortLabel.includes(word) || invName.includes(word) || suppInvName.includes(word);
                    const brandMatch = brandName.includes(word);
                    const catMatch = categoryName.includes(word);
                    const codeMatch = code.includes(word) || suppCode.includes(word);
                    
                    let barcodeMatch = false;
                    let priceMatch = false;

                    if (p.packagings && Array.isArray(p.packagings)) {
                        for (const pkg of p.packagings) {
                            if (pkg.barcode && pkg.barcode.toLowerCase().includes(word)) {
                                barcodeMatch = true;
                                if (pkg.barcode.toLowerCase() === rawQuery) score += 100;
                            }
                            if (isNumWord) {
                                if (pkg.sell_price_retail && String(pkg.sell_price_retail).includes(word)) priceMatch = true;
                                if (pkg.sell_price_wholesale && String(pkg.sell_price_wholesale).includes(word)) priceMatch = true;
                            }
                        }
                    }
                    if (isNumWord && !priceMatch) {
                        if (p.price_small_retail && String(p.price_small_retail).includes(word)) priceMatch = true;
                        if (p.price_small_wholesale && String(p.price_small_wholesale).includes(word)) priceMatch = true;
                    }

                    if (!nameMatch && !brandMatch && !catMatch && !codeMatch && !barcodeMatch && !priceMatch) {
                        matchesAllWords = false;
                        break;
                    }

                    if (nameMatch) score += 30;
                    if (brandMatch || catMatch) score += 20;
                    if (codeMatch || barcodeMatch || priceMatch) score += 15;
                }

                if (matchesAllWords) {
                    results.push({ item: p, score });
                }
            }

            results.sort((a, b) => {
                if (b.score !== a.score) return b.score - a.score;
                const labelA = (a.item.short_label && a.item.short_label.trim() !== '') ? a.item.short_label : (a.item.full_name || '');
                const labelB = (b.item.short_label && b.item.short_label.trim() !== '') ? b.item.short_label : (b.item.full_name || '');
                return labelA.localeCompare(labelB, 'id', { sensitivity: 'base' });
            });

            return results.map(r => r.item).slice(0, 100);
        } catch (e) {
            console.error("Offline search failed", e);
            return [];
        }
    }

    /**
     * Get all supplier_products mappings from IndexedDB
     */
    function getAllSupplierProducts() {
        return _getAll(STORE_SUPPLIER_PRODUCTS);
    }

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
                getAllProducts(),
                getAllSupplierProducts()
            ]);

            // Build a set of product IDs that belong to the given supplier
            const supplierProductIds = new Set();
            const supplierProductMap = new Map(); // productId -> {last_buy_price, purchase_count}
            if (supplierId) {
                allSP.forEach(sp => {
                    if (sp.supplier_id == supplierId) {
                        // If salesRepId is specified, also filter by it, but still include
                        // products from same supplier regardless of salesRep
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

    async function findByBarcode(barcode, isPos = false) {
        if (!barcode) return null;
        const cleanCode = barcode.replace(/\s+/g, '').toLowerCase();
        
        try {
            const all = await getAllProducts();
            for (const p of all) {
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

    // --- PENDING CHANGES ---
    function addPendingChange(endpoint, method, payload) {
        return new Promise((resolve, reject) => {
            if (!db) return reject("DB not initialized");
            const transaction = db.transaction([STORE_PENDING], 'readwrite');
            const store = transaction.objectStore(STORE_PENDING);
            const request = store.add({
                endpoint,
                method,
                payload,
                timestamp: Date.now()
            });

            request.onsuccess = () => resolve(request.result);
            request.onerror = (e) => reject(e.target.error);
        });
    }

    function getPendingChanges() {
        return new Promise((resolve, reject) => {
            if (!db) return reject("DB not initialized");
            const transaction = db.transaction([STORE_PENDING], 'readonly');
            const store = transaction.objectStore(STORE_PENDING);
            const request = store.getAll();

            request.onsuccess = () => resolve(request.result);
            request.onerror = (e) => reject(e.target.error);
        });
    }

    function removePendingChange(id) {
        return new Promise((resolve, reject) => {
            if (!db) return reject("DB not initialized");
            const transaction = db.transaction([STORE_PENDING], 'readwrite');
            const store = transaction.objectStore(STORE_PENDING);
            const request = store.delete(id);

            request.onsuccess = () => resolve();
            request.onerror = (e) => reject(e.target.error);
        });
    }

    function countPending() {
        return new Promise((resolve, reject) => {
            if (!db) return reject("DB not initialized");
            const transaction = db.transaction([STORE_PENDING], 'readonly');
            const store = transaction.objectStore(STORE_PENDING);
            const request = store.count();

            request.onsuccess = () => resolve(request.result);
            request.onerror = (e) => reject(e.target.error);
        });
    }

    // --- AUTH CACHE ---
    function saveAuth(key, data) {
        return new Promise((resolve, reject) => {
            if (!db) return reject("DB not initialized");
            const transaction = db.transaction([STORE_AUTH], 'readwrite');
            const store = transaction.objectStore(STORE_AUTH);
            const request = store.put({ key, data, timestamp: Date.now() });

            request.onsuccess = () => resolve();
            request.onerror = (e) => reject(e.target.error);
        });
    }

    function getAuth(key) {
        return new Promise((resolve, reject) => {
            if (!db) return reject("DB not initialized");
            const transaction = db.transaction([STORE_AUTH], 'readonly');
            const store = transaction.objectStore(STORE_AUTH);
            const request = store.get(key);

        request.onsuccess = () => resolve(request.result ? request.result.data : null);
            request.onerror = (e) => reject(e.target.error);
        });
    }

    /**
     * Save data from a pre-fetched /api/sync/all payload.
     * onStep(stepKey) is called before saving each entity group.
     * Allows dashboard to show per-step progress without double-fetching.
     */
    async function saveFromPayload(data, onStep) {
        if (!db) await init();

        const call = (key) => { if (typeof onStep === 'function') onStep(key); };

        if (data.products && Array.isArray(data.products)) {
            call('products');
            await _saveAll(STORE_PRODUCTS, data.products);
        }
        if (data.suppliers && Array.isArray(data.suppliers)) {
            call('suppliers');
            await _saveAll(STORE_SUPPLIERS, data.suppliers);
        }
        if (data.sales_reps && Array.isArray(data.sales_reps)) {
            call('sales_reps');
            await _saveAll(STORE_SALES_REPS, data.sales_reps);
        }
        if (data.categories && Array.isArray(data.categories)) {
            call('categories');
            await _saveAll(STORE_CATEGORIES, data.categories);
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
                await _saveAll(STORE_FINANCE, financeItems);
            }
        }
        if (data.finance_logs && Array.isArray(data.finance_logs)) {
            call('finance_logs');
            await _saveAll(STORE_FINANCE_LOGS, data.finance_logs);
        }
        return true;
    }

    function clearPending() {
        return new Promise((resolve, reject) => {
            if (!db) return reject("DB not initialized");
            const transaction = db.transaction([STORE_PENDING], 'readwrite');
            const store = transaction.objectStore(STORE_PENDING);
            const request = store.clear();

            request.onsuccess = () => resolve();
            request.onerror = (e) => reject(e.target.error);
        });
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
        clearPending,
        countPending,
        saveAuth,
        getAuth,
        // Exposed for DailyBackup restore operations
        _saveAll,
        invalidateProductsCache: function() {
            // No-op if not implemented; kept for forward-compatibility
        }
    };
})();
