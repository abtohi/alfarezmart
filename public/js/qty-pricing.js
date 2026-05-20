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

    calculateTotalPrice(pkg, saleMode, quantity, useCustom, customLineTotal) {
        if (useCustom) {
            const c = parseFloat(customLineTotal);
            return Number.isFinite(c) && c >= 0 ? c : 0;
        }
        
        let qty = parseFloat(quantity) || 0;
        if (qty <= 0) return 0;
        
        // Sort tiers descending by min_qty
        const tiers = (pkg?.qty_prices || [])
            .filter(t => {
                const mode = t.sale_mode || 'both';
                return (mode === 'both' || mode === saleMode) && parseFloat(t.min_qty) > 0;
            })
            .sort((a, b) => parseFloat(b.min_qty) - parseFloat(a.min_qty));
            
        // Find the highest applicable tier
        const bestTier = tiers.find(t => qty >= parseFloat(t.min_qty));
        
        let total = 0;
        let remainingQty = qty;
        
        if (bestTier) {
            const minQty = parseFloat(bestTier.min_qty);
            const bundles = Math.floor(remainingQty / minQty);
            const bundlePrice = Math.round(minQty * parseFloat(bestTier.unit_price));
            total += bundles * bundlePrice;
            remainingQty -= (bundles * minQty);
        }
        
        // Apply base price to the remainder
        if (remainingQty > 0) {
            const basePrice = this.getBaseUnitPrice(pkg, saleMode);
            total += remainingQty * basePrice;
        }
        
        return Math.round(total);
    },

    getPriceNote(pkg, saleMode, quantity, useCustom) {
        if (useCustom) return 'Harga custom';
        const tier = this.getActiveTier(pkg, saleMode, quantity);
        if (!tier) return '';
        if (tier.label) return tier.label;
        const minQty = parseFloat(tier.min_qty);
        return minQty > 1 ? `Harga bundle (kelipatan ${minQty})` : 'Harga khusus';
    },
};
