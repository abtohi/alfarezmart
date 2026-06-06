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

window.OfflineDB = (function() {
    async function init() {
        return db.open();
    }

    async function syncProductsFromServer() {
        try {
            const data = await api(`${BASE_URL}api/products/sync?_t=` + Date.now());
            if (data && data.products) {
                await db.products.clear();
                await db.products.bulkPut(data.products);
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
                return await saveFromPayload(data);
            }
            return false;
        } catch (e) {
            console.error("Failed to sync all data from server:", e);
            throw e;
        }
    }

    async function saveFromPayload(data, onStep) {
        const call = (key) => { if (typeof onStep === 'function') onStep(key); };

        await db.transaction('rw', db.products, db.suppliers, db.categories, db.brands, db.units, db.finance, db.finance_logs, async () => {
            if (data.products && Array.isArray(data.products)) {
                call('products');
                await db.products.clear();
                await db.products.bulkPut(data.products);
            }
            if (data.suppliers && Array.isArray(data.suppliers)) {
                call('suppliers');
                await db.suppliers.clear();
                await db.suppliers.bulkPut(data.suppliers);
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
    function getAllSuppliers() { return db.suppliers.toArray(); }
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

    async function searchProducts(query) {
        if (!query) return [];
        query = query.toLowerCase().trim();
        const words = query.split(/\s+/).filter(w => w.length > 0);
        if (words.length === 0) return [];
        
        try {
            return await db.products.filter(p => {
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
            }).limit(100).toArray();
        } catch (e) {
            console.error("Offline search failed", e);
            return [];
        }
    }

    async function findByBarcode(barcode) {
        if (!barcode) return null;
        barcode = barcode.replace(/\s+/g, '').toLowerCase();
        
        try {
            return await db.products.filter(p => {
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
            }).first();
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
        getAllCategories,
        getAllBrands,
        getAllUnits,
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
