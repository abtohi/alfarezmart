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

    /**
     * Recursively apply tiered pricing:
     * - Find the best (highest) tier for the current qty
     * - Apply that tier price to floor(qty / min_qty) * min_qty items
     * - Recurse on the remainder with lower tiers, or use base price
     */
    _applyTieredPricing(pkg, saleMode, qty, basePrice, result) {
        if (qty <= 0) return;

        const tier = this.getActiveTier(pkg, saleMode, qty);
        if (!tier) {
            // No tier applies: charge remainder at base price
            const total = Math.round(basePrice * qty);
            result.total += total;
            result.breakdown.push({
                note: `${qty} ${pkg.unit_name || 'Unit'} (@${basePrice.toLocaleString('id-ID')})`,
                price: total
            });
            return;
        }

        const tierMinQty = parseFloat(tier.min_qty) || 0;
        const tierUnitPrice = parseFloat(tier.unit_price) || 0;

        // How many full tier bundles fit
        const tierBundles = Math.floor(qty / tierMinQty);
        const tierQty = tierBundles * tierMinQty;
        const remainderQty = qty - tierQty;

        // Apply tier price to the bundled quantity
        const tierTotal = Math.round(tierUnitPrice * tierQty);
        result.total += tierTotal;
        result.breakdown.push({
            note: `${tierQty} ${pkg.unit_name || 'Unit'} (Tier ${tierMinQty}+ @${tierUnitPrice.toLocaleString('id-ID')})`,
            price: tierTotal
        });

        // Recursively price the remainder
        if (remainderQty > 0) {
            this._applyTieredPricing(pkg, saleMode, remainderQty, basePrice, result);
        }
    },

    getPricingBreakdown(pkg, saleMode, quantity, useCustom, customLineTotal, allPackagings = null) {
        let result = { total: 0, breakdown: [] };
        if (useCustom) {
            const c = parseFloat(customLineTotal);
            result.total = Number.isFinite(c) && c >= 0 ? c : 0;
            result.breakdown.push({ note: 'Harga custom', price: result.total });
            return result;
        }
        
        let qty = parseFloat(quantity) || 0;
        if (qty <= 0) return result;

        // 1. Check for active tier pricing specific to this packaging level
        //    Tier price applies only to the max multiple of min_qty that fits.
        //    Remainder is recursively priced through lower tiers or base price.
        const activeTier = this.getActiveTier(pkg, saleMode, qty);
        if (activeTier) {
            const basePrice = this.getBaseUnitPrice(pkg, saleMode);
            this._applyTieredPricing(pkg, saleMode, qty, basePrice, result);
            return result;
        }

        // 2. If packaging level > 1 (e.g. Renceng, Pack, Karton, Sak), ALWAYS use its explicit unit price
        // This ensures selecting Level 2/3/4 never gets degraded or mistakenly computed as Level 1
        const pkgLevel = parseInt(pkg?.level || 1, 10);
        if (pkgLevel > 1) {
            const basePrice = this.getBaseUnitPrice(pkg, saleMode);
            const lineTotal = Math.round(basePrice * qty);
            result.total = lineTotal;
            result.breakdown.push({
                note: `${qty} ${pkg.unit_name || 'Unit'} (@${basePrice.toLocaleString('id-ID')})`,
                price: lineTotal
            });
            return result;
        }

        // 3. For Base Level 1 (e.g. Pcs): Check if bundle upgrade across larger packagings is applicable
        if (!allPackagings || !Array.isArray(allPackagings) || allPackagings.length <= 1) {
            const basePrice = this.getBaseUnitPrice(pkg, saleMode);
            const lineTotal = Math.round(basePrice * qty);
            result.total = lineTotal;
            result.breakdown.push({ note: `${qty} ${pkg.unit_name || 'Unit'}`, price: lineTotal });
            return result;
        }

        const targetBaseQty = qty * (parseFloat(pkg.base_qty) || 1);
        let remainingBaseQty = targetBaseQty;
        
        let chunks = [];

        allPackagings.forEach(p => {
            const pBaseQty = parseFloat(p.base_qty) || 1;
            const basePrice = this.getBaseUnitPrice(p, saleMode);
            const unitName = p.unit_name || 'Unit';
            if (basePrice > 0) {
                chunks.push({
                    chunk_size: pBaseQty,
                    chunk_price: basePrice,
                    price_per_base_unit: basePrice / pBaseQty,
                    name: `1 ${unitName}`,
                    is_tier: false
                });
            }
            
            if (p.qty_prices && Array.isArray(p.qty_prices)) {
                p.qty_prices.forEach(t => {
                    const tMode = t.sale_mode || 'both';
                    if (tMode === 'both' || tMode === saleMode) {
                        const tMin = parseFloat(t.min_qty) || 0;
                        const tPrice = parseFloat(t.unit_price) || 0;
                        if (tMin > 0 && tPrice > 0) {
                            const chunkSize = pBaseQty * tMin;
                            const chunkPrice = tPrice * tMin;
                            chunks.push({
                                chunk_size: chunkSize,
                                chunk_price: chunkPrice,
                                price_per_base_unit: chunkPrice / chunkSize,
                                name: `${tMin} ${unitName} (Tier)`,
                                is_tier: true
                            });
                        }
                    }
                });
            }
        });

        chunks.sort((a, b) => {
            if (Math.abs(a.price_per_base_unit - b.price_per_base_unit) > 0.0001) {
                return a.price_per_base_unit - b.price_per_base_unit;
            }
            return b.chunk_size - a.chunk_size;
        });

        for (const chunk of chunks) {
            if (remainingBaseQty >= chunk.chunk_size - 0.001) {
                const applyCount = Math.floor((remainingBaseQty + 0.001) / chunk.chunk_size);
                const lineTotal = applyCount * chunk.chunk_price;
                result.total += lineTotal;
                remainingBaseQty -= (applyCount * chunk.chunk_size);
                
                let countPrefix = applyCount > 1 ? `${applyCount}x ` : '';
                result.breakdown.push({ note: `${countPrefix}${chunk.name}`, price: lineTotal });
            }
        }

        if (remainingBaseQty >= 0.001) {
            const fallbackChunks = chunks.sort((a, b) => a.chunk_size - b.chunk_size);
            if (fallbackChunks.length > 0) {
                const smallest = fallbackChunks[0];
                const fractionalTotal = (remainingBaseQty / smallest.chunk_size) * smallest.chunk_price;
                result.total += fractionalTotal;
                
                const fractionalQty = remainingBaseQty / (parseFloat(pkg.base_qty) || 1);
                result.breakdown.push({ note: `${fractionalQty} ${pkg.unit_name || 'Unit'} (Pecahan)`, price: fractionalTotal });
            }
        }

        result.total = Math.round(result.total);
        return result;
    },

    calculateTotalPrice(pkg, saleMode, quantity, useCustom, customLineTotal, allPackagings = null) {
        return this.getPricingBreakdown(pkg, saleMode, quantity, useCustom, customLineTotal, allPackagings).total;
    },

    getPriceNote(pkg, saleMode, quantity, useCustom, allPackagings = null) {
        if (useCustom) return 'Harga custom';
        const breakdown = this.getPricingBreakdown(pkg, saleMode, quantity, false, null, allPackagings);
        
        if (breakdown.breakdown.length > 1) {
            const formula = breakdown.breakdown.map(b => b.note).join(' + ');
            return `Otomatis: ${formula}`;
        }
        
        // Cek jika harga base unit turun karena beli banyak/kemasan besar tanpa pecah
        const qty = parseFloat(quantity) || 0;
        const basePrice = this.getBaseUnitPrice(pkg, saleMode);
        const normalTotal = Math.round(basePrice * qty);
        if (breakdown.total < normalTotal) {
            return `Otomatis: ${breakdown.breakdown.map(b => b.note).join(' + ')}`;
        }
        
        return '';
    }
};
