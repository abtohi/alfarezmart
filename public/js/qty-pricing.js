/**
 * QtyPricing — harga per kuantitas & harga custom POS
 */
const QtyPricing = {
    getBaseUnitPrice(pkg, saleMode) {
        if (!pkg) return 0;
        const retail = parseFloat(pkg.sell_price_retail) || 0;
        const wholesale = parseFloat(pkg.sell_price_wholesale) || 0;
        return saleMode === 'wholesale' ? wholesale : retail;
    },

    getActiveTier(pkg, saleMode, quantity) {
        const tiers = pkg?.qty_prices || [];
        if (!tiers.length) return null;
        const qty = parseFloat(quantity) || 0;
        let best = null;
        tiers.forEach((t) => {
            const minQty = parseFloat(t.min_qty) || 0;
            if (minQty <= 0 || qty < minQty) return;
            const mode = t.sale_mode || 'both';
            if (mode !== 'both' && mode !== saleMode) return;
            if (!best || minQty > parseFloat(best.min_qty)) {
                best = t;
            }
        });
        return best;
    },

    resolveUnitPrice(pkg, saleMode, quantity, useCustom, customUnitPrice) {
        if (useCustom) {
            const c = parseFloat(customUnitPrice);
            return Number.isFinite(c) && c >= 0 ? c : 0;
        }
        const tier = this.getActiveTier(pkg, saleMode, quantity);
        if (tier) {
            return parseFloat(tier.unit_price) || 0;
        }
        return this.getBaseUnitPrice(pkg, saleMode);
    },

    calculateTotalPrice(pkg, saleMode, quantity, useCustom, customLineTotal, allPackagings = null) {
        if (useCustom) {
            const c = parseFloat(customLineTotal);
            return Number.isFinite(c) && c >= 0 ? c : 0;
        }
        
        let qty = parseFloat(quantity) || 0;
        if (qty <= 0) return 0;

        if (!allPackagings || !Array.isArray(allPackagings) || allPackagings.length === 0) {
            allPackagings = [pkg];
        }

        const targetBaseQty = qty * (parseFloat(pkg.base_qty) || 1);
        let remainingBaseQty = targetBaseQty;
        
        let chunks = [];
        allPackagings.forEach(p => {
            const pBaseQty = parseFloat(p.base_qty) || 1;
            const basePrice = this.getBaseUnitPrice(p, saleMode);
            if (basePrice > 0) {
                // Base packaging chunk (e.g. 1 slop = 10 units)
                chunks.push({
                    chunk_size: pBaseQty,
                    chunk_price: basePrice,
                    price_per_base_unit: basePrice / pBaseQty
                });
            }
            
            if (p.qty_prices && Array.isArray(p.qty_prices)) {
                p.qty_prices.forEach(t => {
                    const tMode = t.sale_mode || 'both';
                    if (tMode === 'both' || tMode === saleMode) {
                        const tMin = parseFloat(t.min_qty) || 0;
                        const tPrice = parseFloat(t.unit_price) || 0; // tPrice is PER UNIT
                        if (tMin > 0 && tPrice > 0) {
                            // Tier chunk (e.g. min 5 units -> chunk size is 5 * pBaseQty)
                            const chunkSize = pBaseQty * tMin;
                            const chunkPrice = tPrice * tMin; // total price for this tier chunk
                            chunks.push({
                                chunk_size: chunkSize,
                                chunk_price: chunkPrice,
                                price_per_base_unit: chunkPrice / chunkSize
                            });
                        }
                    }
                });
            }
        });

        // Sort chunks by cheapest price_per_base_unit, then by largest chunk_size
        chunks.sort((a, b) => {
            if (Math.abs(a.price_per_base_unit - b.price_per_base_unit) > 0.0001) {
                return a.price_per_base_unit - b.price_per_base_unit;
            }
            return b.chunk_size - a.chunk_size;
        });

        let total = 0;

        // Greedy algorithm: take as many of the cheapest/largest chunks as possible
        for (const chunk of chunks) {
            if (remainingBaseQty >= chunk.chunk_size - 0.001) {
                const applyCount = Math.floor((remainingBaseQty + 0.001) / chunk.chunk_size);
                total += applyCount * chunk.chunk_price;
                remainingBaseQty -= (applyCount * chunk.chunk_size);
            }
        }

        // If there's still remainder (due to fractions or no small chunks), calculate fractionally using the smallest base chunk
        if (remainingBaseQty >= 0.001) {
            const fallbackChunks = chunks.sort((a, b) => a.chunk_size - b.chunk_size);
            if (fallbackChunks.length > 0) {
                const smallest = fallbackChunks[0];
                total += (remainingBaseQty / smallest.chunk_size) * smallest.chunk_price;
            }
        }

        return Math.round(total);
    },

    getPriceNote(pkg, saleMode, quantity, useCustom, allPackagings = null) {
        if (useCustom) return 'Harga custom';
        
        let qty = parseFloat(quantity) || 0;
        if (qty <= 0) return '';
        
        const basePrice = this.getBaseUnitPrice(pkg, saleMode);
        const normalTotal = Math.round(basePrice * qty);
        const actualTotal = this.calculateTotalPrice(pkg, saleMode, quantity, false, null, allPackagings);
        
        if (actualTotal < normalTotal) {
            return 'Otomatis kemasan besar / grosir';
        }
        return '';
    }
};
