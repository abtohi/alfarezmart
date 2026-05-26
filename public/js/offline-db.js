/**
 * AlfarezMart PWA - Offline IndexedDB
 */

const OfflineDB = (function() {
    const DB_NAME = 'alfarezmart_offline';
    const DB_VERSION = 3; // Upgraded for full offline support
    const STORE_PRODUCTS = 'products';
    const STORE_SALES = 'sales';
    const STORE_SUPPLIERS = 'suppliers';
    const STORE_PURCHASES = 'purchases';
    const STORE_DEBTS = 'debts';
    const STORE_FINANCE = 'finance';
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
                [STORE_SALES, STORE_SUPPLIERS, STORE_PURCHASES, STORE_DEBTS, STORE_FINANCE].forEach(storeName => {
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
            const data = await api(`${BASE_URL}api/sync/all?_t=` + Date.now());
            if (data) {
                if (data.products) await _saveAll(STORE_PRODUCTS, data.products);
                if (data.sales) await _saveAll(STORE_SALES, data.sales);
                if (data.suppliers) await _saveAll(STORE_SUPPLIERS, data.suppliers);
                if (data.purchases) await _saveAll(STORE_PURCHASES, data.purchases);
                if (data.debts) await _saveAll(STORE_DEBTS, data.debts);
                if (data.finance) await _saveAll(STORE_FINANCE, data.finance);
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
        query = query.toLowerCase();
        
        try {
            const all = await getAllProducts();
            return all.filter(p => {
                const nameMatch = (p.full_name && p.full_name.toLowerCase().includes(query)) ||
                                  (p.short_label && p.short_label.toLowerCase().includes(query));
                const brandMatch = p.brand_name && p.brand_name.toLowerCase().includes(query);
                const codeMatch = p.code && p.code.toLowerCase().includes(query);
                
                let barcodeMatch = false;
                if (p.packagings && Array.isArray(p.packagings)) {
                    barcodeMatch = p.packagings.some(pkg => pkg.barcode && pkg.barcode.toLowerCase().includes(query));
                }

                return nameMatch || brandMatch || codeMatch || barcodeMatch;
            }).slice(0, 15);
        } catch (e) {
            console.error("Offline search failed", e);
            return [];
        }
    }

    async function findByBarcode(barcode) {
        if (!barcode) return null;
        barcode = barcode.toLowerCase();
        
        try {
            const all = await getAllProducts();
            return all.find(p => {
                if (p.packagings && Array.isArray(p.packagings)) {
                    return p.packagings.some(pkg => pkg.barcode && pkg.barcode.toLowerCase() === barcode);
                }
                return false;
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

    return {
        init,
        syncProductsFromServer,
        syncAllDataFromServer,
        getAllProducts,
        getAllSales,
        getAllSuppliers,
        getAllPurchases,
        getAllDebts,
        getAllFinance,
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
