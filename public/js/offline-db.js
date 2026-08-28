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

            // Products: single atomic readwrite transaction to preserve pending local records
            const tx = db.transaction([storeName], 'readwrite');
            const st = tx.objectStore(storeName);
            const reqAll = st.getAll();

            reqAll.onsuccess = () => {
                const existing = reqAll.result || [];
                // Collect pending items not in server list
                const serverIds = new Set(items.map(p => p.id));
                const pendingLocals = existing.filter(p =>
                    (p.is_pending === true || p.is_pending_update === true) &&
                    !serverIds.has(p.id)
                );

                st.clear();
                items.forEach(item => st.put(item));
                // Re-insert pending local items (new products not yet on server)
                pendingLocals.forEach(item => st.put(item));
            };

            tx.oncomplete = () => resolve();
            tx.onerror = (e) => reject(e.target.error);
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
                const variant = (p.variant || '').toLowerCase();
                const code = (p.code || '').toLowerCase();
                const suppCode = (p.supplier_product_code || '').toLowerCase();
                const weightStr = ((p.weight_value || '') + (p.weight_unit || '')).toLowerCase();

                const displayLabel = (shortLabel || fullName).toLowerCase();

                // 1. Full query matches
                if (displayLabel === rawQuery || fullName === rawQuery) score += 500;
                else if (displayLabel.startsWith(rawQuery) || fullName.startsWith(rawQuery)) score += 250;
                else if (displayLabel.includes(rawQuery) || fullName.includes(rawQuery)) score += 150;

                if (code === rawQuery || suppCode === rawQuery) score += 600;
                else if (code.startsWith(rawQuery) || suppCode.startsWith(rawQuery)) score += 350;
                else if (code.includes(rawQuery) || suppCode.includes(rawQuery)) score += 200;
                
                for (const word of words) {
                    const weightMatch = word.match(/^(\d+(?:[.,]\d+)?)\s*(g|gr|gram|kg|kilo|kilogram|ml|l|liter|ltr|oz|pcs|sachet|btg|kotak|dus|rcg|slp|pack|btl|cup)$/i);
                    const isPurePrice = /^(?:rp\.?\s*)?(\d{2,8})$/i.test(word) && !/[a-z]/i.test(word);
                    const isAlphaNumCode = /^(?=.*[a-z])(?=.*\d)[a-z0-9\-_#]+$/i.test(word);

                    let wordMatched = false;

                    // A. Product Code / Supplier Code
                    if (code.includes(word) || suppCode.includes(word)) {
                        wordMatched = true;
                        if (code === word || suppCode === word) score += 300;
                        else if (code.startsWith(word) || suppCode.startsWith(word)) score += 180;
                        else score += 100;
                    }

                    // B. Weight / Size / Volume (e.g. 800g, 4kg, 45g)
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
                            score += 350;
                        } else {
                            score -= 60;
                        }
                    }

                    // C. Name & Label
                    const nameMatch = fullName.includes(word) || shortLabel.includes(word) || invName.includes(word) || suppInvName.includes(word) || variant.includes(word);
                    if (nameMatch) {
                        wordMatched = true;
                        if (displayLabel.startsWith(word) || fullName.startsWith(word)) score += 90;
                        else score += 50;
                    }

                    // D. Brand & Category
                    if (brandName.includes(word)) { wordMatched = true; score += 40; }
                    if (categoryName.includes(word)) { wordMatched = true; score += 25; }

                    // E. Barcode
                    if (p.packagings && Array.isArray(p.packagings)) {
                        for (const pkg of p.packagings) {
                            if (pkg.barcode && pkg.barcode.toLowerCase().includes(word)) {
                                wordMatched = true;
                                score += pkg.barcode.toLowerCase() === word ? 250 : 80;
                            }
                        }
                    }

                    // F. Price (Only pure numeric)
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

                                    if (isLvl1 && pRetail === purePriceNum) isLevel1Match = true;
                                    else if (pRetail === purePriceNum || pWhole === purePriceNum || pBuy === purePriceNum) isOtherPriceMatch = true;
                                    else if (word.length >= 3 && (String(pRetail).includes(word) || String(pWhole).includes(word))) isOtherPriceMatch = true;
                                }
                            }
                            if (p.price_small_retail != null) {
                                const pSmall = Math.round(parseFloat(p.price_small_retail) || 0);
                                if (pSmall === purePriceNum) isLevel1Match = true;
                            }

                            if (isLevel1Match) {
                                wordMatched = true;
                                score += 400; // Level 1 price match priority
                            } else if (isOtherPriceMatch) {
                                wordMatched = true;
                                score += 150;
                            }
                        }
                    }

                    if (!wordMatched) {
                        matchesAllWords = false;
                        break;
                    }
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
    async function getAllSupplierProducts() {
        try {
            return await _getAll(STORE_SUPPLIER_PRODUCTS);
        } catch (e) {
            return [];
        }
    }

    /**
     * Supplier-aware product search with relevance scoring
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

            const supplierProductIds = new Set();
            const supplierProductMap = new Map();
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

                if (displayLabel === query || fullName === query) score += 500;
                else if (displayLabel.startsWith(query) || fullName.startsWith(query)) score += 250;
                else if (displayLabel.includes(query) || fullName.includes(query)) score += 150;

                if (code === query || suppCode === query) score += 600;
                else if (code.startsWith(query) || suppCode.startsWith(query)) score += 350;

                for (const word of words) {
                    const weightMatch = word.match(/^(\d+(?:[.,]\d+)?)\s*(g|gr|gram|kg|kilo|kilogram|ml|l|liter|ltr|oz|pcs|sachet|btg|kotak|dus|rcg|slp|pack|btl|cup)$/i);
                    const isPurePrice = /^(?:rp\.?\s*)?(\d{2,8})$/i.test(word) && !/[a-z]/i.test(word);
                    const isAlphaNumCode = /^(?=.*[a-z])(?=.*\d)[a-z0-9\-_#]+$/i.test(word);

                    let wordMatched = false;

                    // Code match
                    if (code.includes(word) || suppCode.includes(word)) {
                        wordMatched = true;
                        score += (code === word || suppCode === word) ? 300 : 150;
                    }

                    // Weight / Size / Volume Match
                    if (weightMatch) {
                        const numPart = weightMatch[1].replace(',', '.');
                        const unitPart = weightMatch[2].toLowerCase();
                        let normUnit = unitPart;
                        if (['gr', 'gram'].includes(unitPart)) normUnit = 'g';
                        if (['kilo', 'kilogram'].includes(unitPart)) normUnit = 'kg';
                        if (['ltr', 'liter'].includes(unitPart)) normUnit = 'l';

                        const fullWeightNeedle = `${numPart}${normUnit}`;
                        const spaceWeightNeedle = `${numPart} ${normUnit}`;
                        const nameHasWeight = fullName.includes(fullWeightNeedle) || fullName.includes(spaceWeightNeedle) ||
                                              shortLabel.includes(fullWeightNeedle) || shortLabel.includes(spaceWeightNeedle) ||
                                              variant.includes(fullWeightNeedle) || weightStr.includes(fullWeightNeedle) ||
                                              (String(p.weight_value) === numPart);

                        if (nameHasWeight) {
                            wordMatched = true;
                            score += 350;
                        }
                    }

                    // Name / Label / Brand / Category
                    if (fullName.includes(word) || shortLabel.includes(word) || invName.includes(word) || suppInvName.includes(word) || variant.includes(word)) {
                        wordMatched = true;
                        score += (displayLabel.startsWith(word) || fullName.startsWith(word)) ? 90 : 50;
                    }
                    if (brandName.includes(word)) { wordMatched = true; score += 40; }
                    if (categoryName.includes(word)) { wordMatched = true; score += 25; }

                    // Barcode
                    if (p.packagings && Array.isArray(p.packagings)) {
                        for (const pkg of p.packagings) {
                            if (pkg.barcode && pkg.barcode.toLowerCase().includes(word)) {
                                wordMatched = true;
                                score += pkg.barcode.toLowerCase() === word ? 250 : 80;
                            }
                        }
                    }

                    // Price
                    if (isPurePrice && !weightMatch && !isAlphaNumCode) {
                        const purePriceNum = parseFloat(word.replace(/[^\d]/g, ''));
                        if (!isNaN(purePriceNum) && purePriceNum > 0) {
                            if (p.packagings && Array.isArray(p.packagings)) {
                                for (let k = 0; k < p.packagings.length; k++) {
                                    const pkg = p.packagings[k];
                                    const pRetail = Math.round(parseFloat(pkg.sell_price_retail) || 0);
                                    const isLvl1  = (pkg.level == 1 || pkg.level === '1' || k === 0);
                                    if (isLvl1 && pRetail === purePriceNum) {
                                        wordMatched = true;
                                        score += 400;
                                    } else if (pRetail === purePriceNum) {
                                        wordMatched = true;
                                        score += 150;
                                    }
                                }
                            }
                        }
                    }

                    if (!wordMatched) {
                        allMatch = false;
                        break;
                    }
                }

                if (!allMatch) continue;

                // Supplier boost: products associated with this supplier get a massive boost
                const isSupplierMatch = supplierId && supplierProductIds.has(p.id);
                if (isSupplierMatch) {
                    score += 500;
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
