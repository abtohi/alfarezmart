const DB_NAME = 'AlfarezMartOfflineDB';
const DB_VERSION = 1;

class IDBHelper {
    constructor() {
        this.db = null;
    }

    async init() {
        if (this.db) return this.db;
        return new Promise((resolve, reject) => {
            const request = indexedDB.open(DB_NAME, DB_VERSION);

            request.onerror = (e) => reject('IndexedDB error: ' + e.target.error);

            request.onsuccess = (e) => {
                this.db = e.target.result;
                resolve(this.db);
            };

            request.onupgradeneeded = (e) => {
                const db = e.target.result;
                
                // Cache data from server (products, categories, etc.)
                // Key will be the API endpoint or resource name
                if (!db.objectStoreNames.contains('cache_data')) {
                    db.createObjectStore('cache_data', { keyPath: 'id' });
                }

                // Outbox for pending API requests
                if (!db.objectStoreNames.contains('outbox')) {
                    db.createObjectStore('outbox', { keyPath: 'id', autoIncrement: true });
                }
            };
        });
    }

    async getCache(id) {
        await this.init();
        return new Promise((resolve, reject) => {
            const tx = this.db.transaction('cache_data', 'readonly');
            const store = tx.objectStore('cache_data');
            const request = store.get(id);
            request.onsuccess = () => resolve(request.result ? request.result.data : null);
            request.onerror = () => reject(request.error);
        });
    }

    async setCache(id, data) {
        await this.init();
        return new Promise((resolve, reject) => {
            const tx = this.db.transaction('cache_data', 'readwrite');
            const store = tx.objectStore('cache_data');
            const request = store.put({ id, data, timestamp: Date.now() });
            request.onsuccess = () => resolve();
            request.onerror = () => reject(request.error);
        });
    }

    async addToOutbox(requestConfig) {
        await this.init();
        return new Promise((resolve, reject) => {
            const tx = this.db.transaction('outbox', 'readwrite');
            const store = tx.objectStore('outbox');
            // requestConfig: { url, method, body, headers, timestamp }
            const request = store.add({ ...requestConfig, timestamp: Date.now() });
            request.onsuccess = () => resolve(request.result); // returns the ID
            request.onerror = () => reject(request.error);
        });
    }

    async getOutbox() {
        await this.init();
        return new Promise((resolve, reject) => {
            const tx = this.db.transaction('outbox', 'readonly');
            const store = tx.objectStore('outbox');
            const request = store.getAll();
            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    }

    async removeFromOutbox(id) {
        await this.init();
        return new Promise((resolve, reject) => {
            const tx = this.db.transaction('outbox', 'readwrite');
            const store = tx.objectStore('outbox');
            const request = store.delete(id);
            request.onsuccess = () => resolve();
            request.onerror = () => reject(request.error);
        });
    }

    async clearCache() {
        await this.init();
        return new Promise((resolve, reject) => {
            const tx = this.db.transaction('cache_data', 'readwrite');
            const store = tx.objectStore('cache_data');
            const request = store.clear();
            request.onsuccess = () => resolve();
            request.onerror = () => reject(request.error);
        });
    }
}

window.idb = new IDBHelper();
