/**
 * AlfarezMart PWA - Offline IndexedDB
 */

window.OfflineDB = (function() {
    const DB_NAME = 'alfarezmart_offline';
    const DB_VERSION = 5; // Upgraded to ensure all stores exist
    const STORE_PRODUCTS = 'products';
    const STORE_SALES = 'sales';
    const STORE_SUPPLIERS = 'suppliers';
    const STORE_PURCHASES = 'purchases';
    const STORE_DEBTS = 'debts';
    const STORE_FINANCE = 'finance';
    const STORE_FINANCE_LOGS = 'finance_logs';
    const STORE_CATEGORIES = 'categories';
    const STORE_PENDING = 'pending_changes';
    const STORE_AUTH = 'auth_cache';

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
                [STORE_SALES, STORE_SUPPLIERS, STORE_PURCHASES, STORE_DEBTS, STORE_FINANCE, STORE_FINANCE_LOGS, STORE_CATEGORIES].forEach(storeName => {
                    if (!db.objectStoreNames.contains(storeName)) {
                        db.createObjectStore(storeName, { keyPath: 'id' });
                    }
                });

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

    async function syncAllDataFromServer() {
        try {
            if (!db) await init();
            const data = await api(`${BASE_URL}api/sync/all?_t=` + Date.now());
            if (data) {
                // Products
                if (data.products && Array.isArray(data.products)) {
                    await _saveAll(STORE_PRODUCTS, data.products);
                }
                // Suppliers
                if (data.suppliers && Array.isArray(data.suppliers)) {
                    await _saveAll(STORE_SUPPLIERS, data.suppliers);
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

    function _saveAll(storeName, items) {
        return new Promise((resolve, reject) => {
            if (!db) return reject("DB not initialized");
            
            const transaction = db.transaction([storeName], 'readwrite');
            const store = transaction.objectStore(storeName);
            
            store.clear();
            items.forEach(item => {
                store.put(item);
            });

            transaction.oncomplete = () => resolve();
            transaction.onerror = (e) => reject(e.target.error);
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

    async function searchProducts(query) {
        if (!query) return [];
        query = query.toLowerCase().trim();
        const words = query.split(/\s+/).filter(w => w.length > 0);
        if (words.length === 0) return [];
        
        try {
            const all = await getAllProducts();
            return all.filter(p => {
                return words.every(word => {
                    const nameMatch = (p.full_name && p.full_name.toLowerCase().includes(word)) ||
                                      (p.short_label && p.short_label.toLowerCase().includes(word));
                    const brandMatch = p.brand_name && p.brand_name.toLowerCase().includes(word);
                    const codeMatch = p.code && p.code.toLowerCase().includes(word);
                    
                    let barcodeMatch = false;
                    if (p.packagings && Array.isArray(p.packagings)) {
                        barcodeMatch = p.packagings.some(pkg => pkg.barcode && pkg.barcode.toLowerCase().includes(word));
                    }

                    return nameMatch || brandMatch || codeMatch || barcodeMatch;
                });
            }).slice(0, 100);
        } catch (e) {
            console.error("Offline search failed", e);
            return [];
        }
    }

    async function findByBarcode(barcode) {
        if (!barcode) return null;
        barcode = barcode.replace(/\s+/g, '').toLowerCase();
        
        try {
            const all = await getAllProducts();
            return all.find(p => {
                let match = false;
                if (p.code) {
                    let pCode = p.code.replace(/\s+/g, '').toLowerCase();
                    if (pCode === barcode) match = true;
                }
                
                if (!match && p.packagings && Array.isArray(p.packagings)) {
                    match = p.packagings.some(pkg => {
                        if (!pkg.barcode) return false;
                        let b = pkg.barcode.replace(/\s+/g, '').toLowerCase();
                        return b === barcode || b === '0' + barcode || '0' + b === barcode || b === '00' + barcode || '00' + b === barcode;
                    });
                }
                return match;
            });
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

    return {
        init,
        syncProductsFromServer,
        syncAllDataFromServer,
        saveFromPayload,
        getAllProducts,
        getAllSales,
        getAllSuppliers,
        getAllPurchases,
        getAllDebts,
        getAllFinance,
        getAllFinanceLogs,
        saveFinanceLog,
        saveProduct,
        getProductById,
        searchProducts,
        findByBarcode,
        addPendingChange,
        getPendingChanges,
        removePendingChange,
        countPending,
        saveAuth,
        getAuth
    };
})();
