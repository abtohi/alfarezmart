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
        
        let bundles = [];
        allPackagings.forEach(p => {
            const pBaseQty = parseFloat(p.base_qty) || 1;
            const basePrice = this.getBaseUnitPrice(p, saleMode);
            if (basePrice > 0) {
                bundles.push({
                    base_qty_size: pBaseQty,
                    min_bundles: 1,
                    price_per_bundle: basePrice,
                    price_per_base_unit: basePrice / pBaseQty
                });
            }
            
            if (p.qty_prices && Array.isArray(p.qty_prices)) {
                p.qty_prices.forEach(t => {
                    const tMode = t.sale_mode || 'both';
                    if (tMode === 'both' || tMode === saleMode) {
                        const tMin = parseFloat(t.min_qty) || 0;
                        const tPrice = parseFloat(t.unit_price) || 0;
                        if (tMin > 0 && tPrice > 0) {
                            bundles.push({
                                base_qty_size: pBaseQty,
                                min_bundles: tMin,
                                price_per_bundle: tPrice,
                                price_per_base_unit: tPrice / pBaseQty
                            });
                        }
                    }
                });
            }
        });

        bundles.sort((a, b) => {
            if (Math.abs(a.price_per_base_unit - b.price_per_base_unit) > 0.0001) {
                return a.price_per_base_unit - b.price_per_base_unit;
            }
            return (b.base_qty_size * b.min_bundles) - (a.base_qty_size * a.min_bundles);
        });

        let total = 0;
        let lastRemaining = remainingBaseQty;

        while (remainingBaseQty >= 0.001) {
            let bestBundle = null;
            for (const b of bundles) {
                if (remainingBaseQty >= (b.base_qty_size * b.min_bundles) - 0.001) {
                    bestBundle = b;
                    break;
                }
            }

            if (!bestBundle) {
                const fallbackBundles = bundles.filter(b => b.min_bundles === 1);
                if (fallbackBundles.length > 0) {
                    bestBundle = fallbackBundles.sort((a, b) => a.base_qty_size - b.base_qty_size)[0];
                } else {
                    break; 
                }
            }

            const applyCount = Math.floor((remainingBaseQty + 0.001) / bestBundle.base_qty_size);
            if (applyCount <= 0) {
                // If even the smallest bundle cannot fit, just charge fractionally
                total += (remainingBaseQty / bestBundle.base_qty_size) * bestBundle.price_per_bundle;
                break;
            }

            total += applyCount * bestBundle.price_per_bundle;
            remainingBaseQty -= (applyCount * bestBundle.base_qty_size);

            if (remainingBaseQty >= lastRemaining) break;
            lastRemaining = remainingBaseQty;
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
