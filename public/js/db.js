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
        if (!navigator.onLine) return 0;
        try {
            const data = await api(`${BASE_URL}api/products/sync?_t=` + Date.now(), { silent: true, noOfflineQueue: true });
            if (data && data.products && Array.isArray(data.products) && data.products.length > 0) {
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
                invalidateProductsCache();
                return data.products.length;
            }
            return 0;
        } catch (e) {
            return 0;
        }
    }

    async function syncAllDataFromServer() {
        if (!navigator.onLine) return false;
        try {
            const data = await api(`${BASE_URL}api/sync/all?_t=` + Date.now(), { timeout: 60000, silent: true, noOfflineQueue: true });
            if (data) {
                return await saveFromPayload(data);
            }
            return false;
        } catch (e) {
            return false;
        }
    }


    async function cacheProductImages(products) {
        // Images are dynamically cached on-demand by SW during browsing
        // Pre-caching all photos on mobile wastes data and slows down CPU
        return;
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

                // Also check barcodes & prices
                let barcodeText = '';
                let priceText = '';
                if (p.packagings && Array.isArray(p.packagings)) {
                    barcodeText = p.packagings.map(pkg => pkg.barcode || '').join(' ').toLowerCase();
                    priceText = p.packagings.map(pkg => {
                        const r = Math.round(parseFloat(pkg.sell_price_retail) || 0);
                        const w = Math.round(parseFloat(pkg.sell_price_wholesale) || 0);
                        const b = Math.round(parseFloat(pkg.buy_price) || 0);
                        return `${r} ${w} ${b}`;
                    }).join(' ');
                }
                if (p.price_small_retail != null) {
                    priceText += ' ' + Math.round(parseFloat(p.price_small_retail) || 0);
                }

                const allText = (searchText + ' ' + barcodeText + ' ' + priceText).toLowerCase();

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

    // In-Memory RAM cache for instantaneous (0ms) search on mobile devices
    let _inMemoryProductsCache = null;
    let _inMemoryCacheTimestamp = 0;

    async function getCachedProductsArray() {
        const now = Date.now();
        if (_inMemoryProductsCache && (now - _inMemoryCacheTimestamp < 300000)) {
            return _inMemoryProductsCache;
        }
        try {
            _inMemoryProductsCache = await db.products.toArray();
            _inMemoryCacheTimestamp = now;
            return _inMemoryProductsCache;
        } catch (e) {
            return [];
        }
    }

    function invalidateProductsCache() {
        _inMemoryProductsCache = null;
        _inMemoryCacheTimestamp = 0;
    }

    function getProductById(id) {
        if (_inMemoryProductsCache) {
            const found = _inMemoryProductsCache.find(p => p.id === parseInt(id));
            if (found) return Promise.resolve(found);
        }
        return db.products.get(parseInt(id));
    }

    async function searchProducts(query, isPos = false) {
        if (!query) return [];
        const rawQuery = query.toLowerCase().trim();
        const words = rawQuery.split(/\s+/).filter(w => w.length > 0);
        if (words.length === 0) return [];
        
        try {
            // Use In-Memory array for instant 0-2ms response
            const products = await getCachedProductsArray();
            const matched = [];

            for (let i = 0; i < products.length; i++) {
                const p = products[i];
                if (isPos && (p.is_available == 0 || p.is_available === '0' || p.is_available === false)) continue;

                const fullName = (p.full_name || '').toLowerCase();
                const shortLabel = (p.short_label || '').toLowerCase();
                const invName = (p.invoice_name || '').toLowerCase();
                const suppInvName = (p.supplier_invoice_name || '').toLowerCase();
                const brandName = (p.brand_name || '').toLowerCase();
                const categoryName = (p.category_name || '').toLowerCase();
                const variant = (p.variant || '').toLowerCase();
                const code = (p.code || '').toLowerCase();
                const suppCode = (p.supplier_product_code || '').toLowerCase();
                const weightStr = ((p.weight_value || '') + (p.weight_unit || '')).toLowerCase();

                const displayLabel = (shortLabel || fullName).toLowerCase();
                let score = 0;
                let allMatch = true;

                // 1. Full Query Matching Boosts
                if (displayLabel === rawQuery || fullName === rawQuery) {
                    score += 500;
                } else if (displayLabel.startsWith(rawQuery) || fullName.startsWith(rawQuery)) {
                    score += 250;
                } else if (displayLabel.includes(rawQuery) || fullName.includes(rawQuery)) {
                    score += 150;
                }

                // Exact Code Matching Boost (e.g. CLNOS65)
                if (code === rawQuery || suppCode === rawQuery) {
                    score += 600;
                } else if (code.startsWith(rawQuery) || suppCode.startsWith(rawQuery)) {
                    score += 350;
                } else if (code.includes(rawQuery) || suppCode.includes(rawQuery)) {
                    score += 200;
                }

                // 2. Per-Word Matching
                for (let j = 0; j < words.length; j++) {
                    const word = words[j];
                    const weightMatch = word.match(/^(\d+(?:[.,]\d+)?)\s*(g|gr|gram|kg|kilo|kilogram|ml|l|liter|ltr|oz|pcs|sachet|btg|kotak|dus|rcg|slp|pack|btl|cup)$/i);
                    const isPurePrice = /^(?:rp\.?\s*)?(\d{2,8})$/i.test(word) && !/[a-z]/i.test(word);
                    const isAlphaNumCode = /^(?=.*[a-z])(?=.*\d)[a-z0-9\-_#]+$/i.test(word);

                    let wordMatched = false;

                    // A. Product Code / Supplier Product Code Match
                    if (code.includes(word) || suppCode.includes(word)) {
                        wordMatched = true;
                        if (code === word || suppCode === word) score += 300;
                        else if (code.startsWith(word) || suppCode.startsWith(word)) score += 180;
                        else score += 100;
                    }

                    // B. Size / Weight / Volume Match (e.g. "800g", "4kg", "45g")
                    if (weightMatch) {
                        const numPart = weightMatch[1].replace(',', '.');
                        const unitPart = weightMatch[2].toLowerCase();
                        let normUnit = unitPart;
                        if (['gr', 'gram'].includes(unitPart)) normUnit = 'g';
                        if (['kilo', 'kilogram'].includes(unitPart)) normUnit = 'kg';
                        if (['ltr', 'liter'].includes(unitPart)) normUnit = 'l';

                        const fullWeightNeedle = `${numPart}${normUnit}`;
                        const spaceWeightNeedle = `${numPart} ${normUnit}`;
                        const rawWeightNeedle = `${numPart}${unitPart}`;

                        const nameHasWeight = fullName.includes(fullWeightNeedle) || fullName.includes(spaceWeightNeedle) || fullName.includes(rawWeightNeedle) ||
                                              shortLabel.includes(fullWeightNeedle) || shortLabel.includes(spaceWeightNeedle) || shortLabel.includes(rawWeightNeedle) ||
                                              variant.includes(fullWeightNeedle) || variant.includes(spaceWeightNeedle) ||
                                              weightStr.includes(fullWeightNeedle) || (String(p.weight_value) === numPart);

                        // Unit conversion check
                        let convMatch = false;
                        if (normUnit === 'kg') {
                            const grams = parseFloat(numPart) * 1000;
                            convMatch = fullName.includes(`${grams}g`) || fullName.includes(`${grams} g`) || weightStr.includes(`${grams}g`);
                        } else if (normUnit === 'g' && parseFloat(numPart) >= 1000) {
                            const kg = parseFloat(numPart) / 1000;
                            convMatch = fullName.includes(`${kg}kg`) || fullName.includes(`${kg} kg`) || weightStr.includes(`${kg}kg`);
                        }

                        if (nameHasWeight || convMatch) {
                            wordMatched = true;
                            score += 350; // HUGE boost for matching size/weight/volume!
                        } else {
                            score -= 60;
                        }
                    }

                    // C. Name, Short Label, Variant, Invoice Name Match
                    const nameHasWord = fullName.includes(word) || shortLabel.includes(word) || invName.includes(word) || suppInvName.includes(word) || variant.includes(word);
                    if (nameHasWord) {
                        wordMatched = true;
                        if (displayLabel.startsWith(word) || fullName.startsWith(word)) {
                            score += 90;
                        } else {
                            score += 50;
                        }
                    }

                    // D. Brand & Category Match
                    if (brandName.includes(word)) {
                        wordMatched = true;
                        score += 40;
                    }
                    if (categoryName.includes(word)) {
                        wordMatched = true;
                        score += 25;
                    }

                    // E. Barcode Match
                    if (p.packagings && Array.isArray(p.packagings)) {
                        for (let k = 0; k < p.packagings.length; k++) {
                            const pkg = p.packagings[k];
                            if (pkg.barcode && pkg.barcode.toLowerCase().includes(word)) {
                                wordMatched = true;
                                score += pkg.barcode.toLowerCase() === word ? 250 : 80;
                            }
                        }
                    }

                    // F. Price Match (ONLY if pure numeric search like "Daia 500" - NOT alphanumeric codes or unit tokens)
                    if (isPurePrice && !weightMatch && !isAlphaNumCode) {
                        const purePriceNum = parseFloat(word.replace(/[^\d]/g, ''));
                        if (!isNaN(purePriceNum) && purePriceNum > 0) {
                            let isLevel1Match = false;
                            let isOtherPriceMatch = false;

                            if (p.packagings && Array.isArray(p.packagings)) {
                                for (let k = 0; k < p.packagings.length; k++) {
                                    const pkg = p.packagings[k];
                                    const pRetail = Math.round(parseFloat(pkg.sell_price_retail) || 0);
                                    const pWhole  = Math.round(parseFloat(pkg.sell_price_wholesale) || 0);
                                    const pBuy    = Math.round(parseFloat(pkg.buy_price) || 0);
                                    const isLvl1  = (pkg.level == 1 || pkg.level === '1' || k === 0);

                                    if (isLvl1 && pRetail === purePriceNum) {
                                        isLevel1Match = true;
                                    } else if (pRetail === purePriceNum || pWhole === purePriceNum || pBuy === purePriceNum) {
                                        isOtherPriceMatch = true;
                                    } else if (word.length >= 3 && (String(pRetail).includes(word) || String(pWhole).includes(word))) {
                                        isOtherPriceMatch = true;
                                    }
                                }
                            }

                            if (p.price_small_retail != null) {
                                const pSmall = Math.round(parseFloat(p.price_small_retail) || 0);
                                if (pSmall === purePriceNum) isLevel1Match = true;
                            }

                            if (isLevel1Match) {
                                wordMatched = true;
                                score += 400; // TOP PRIORITY: Level 1 Retail Price Match!
                            } else if (isOtherPriceMatch) {
                                wordMatched = true;
                                score += 150;
                            }
                        }
                    }

                    if (!wordMatched) {
                        allMatch = false;
                        break;
                    }
                }

                if (allMatch) {
                    matched.push({ item: p, score });
                    if (matched.length >= 80) break;
                }
            }

            // Sort by score descending, then alphabetically
            matched.sort((a, b) => {
                if (b.score !== a.score) return b.score - a.score;
                const la = (a.item.short_label && a.item.short_label.trim() !== '') ? a.item.short_label : (a.item.full_name || '');
                const lb = (b.item.short_label && b.item.short_label.trim() !== '') ? b.item.short_label : (b.item.full_name || '');
                return la.localeCompare(lb, 'id', { sensitivity: 'base' });
            });

            return matched.map(r => r.item);
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

    function clearPending() {
        return db.pending_changes.clear();
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
        clearPending,
        saveAuth,
        getAuth
    };
})();
