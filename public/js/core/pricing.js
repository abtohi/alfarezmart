/**
 * PricingHelper - Client-side Single Source of Truth for Margin, Nett & Tier Calculations
 * Synchronized with app/services/pricing/PricingService.php
 * 
 * @version 1.0
 */
const PricingHelper = (function () {
    'use strict';

    /**
     * Hitung harga modal bersih (Nett Price)
     * @param {number} buyPrice 
     * @param {number} ppnPercent 
     * @param {string} discountMode 'rp' | 'pct'
     * @param {number} discountValue 
     * @param {number} quantity 
     * @returns {number}
     */
    function calculateNett(buyPrice, ppnPercent = 0, discountMode = 'rp', discountValue = 0, quantity = 1) {
        const buy = Math.max(0, parseFloat(buyPrice) || 0);
        const ppn = Math.max(0, parseFloat(ppnPercent) || 0);
        const dv  = Math.max(0, parseFloat(discountValue) || 0);
        const qty = Math.max(1, parseFloat(quantity) || 1);

        const ppnAmount = buy * (ppn / 100);
        let discAmount = 0;

        if (discountMode === 'pct') {
            discAmount = buy * (dv / 100);
        } else {
            discAmount = qty > 0 ? (dv / qty) : 0;
        }

        const nett = buy + ppnAmount - discAmount;
        return Math.max(0, Math.round(nett * 100) / 100);
    }

    /**
     * Hitung persentase markup terhadap modal nett
     * @param {number} nettPrice 
     * @param {number} sellPrice 
     * @returns {number|null}
     */
    function calculateMarkup(nettPrice, sellPrice) {
        const nett = parseFloat(nettPrice) || 0;
        const sell = parseFloat(sellPrice) || 0;
        if (nett <= 0 || sell <= 0) return null;

        const profit = sell - nett;
        return Math.round((profit / nett) * 1000) / 10;
    }

    /**
     * Hitung keuntungan nominal dalam rupiah
     * @param {number} nettPrice 
     * @param {number} sellPrice 
     * @returns {number}
     */
    function calculateProfit(nettPrice, sellPrice) {
        const nett = parseFloat(nettPrice) || 0;
        const sell = parseFloat(sellPrice) || 0;
        return Math.round((sell - nett) * 100) / 100;
    }

    /**
     * Format badge margin dengan keuntungan rupiah
     * @param {number} nettPrice 
     * @param {number} sellPrice 
     * @param {string} label 
     * @returns {string} HTML string
     */
    function formatMarginBadge(nettPrice, sellPrice, label = 'Margin') {
        const nett = parseFloat(nettPrice) || 0;
        const sell = parseFloat(sellPrice) || 0;

        if (sell <= 0) {
            return `<span style="font-size:10px;color:var(--text-muted);">${label}: -</span>`;
        }
        if (nett <= 0) {
            return `<span style="font-size:10px;color:var(--text-muted);">${label}: N/A (modal 0)</span>`;
        }

        const profit = sell - nett;
        const mkp = (profit / nett) * 100;
        const isLoss = profit < 0;
        const isHigh = mkp >= 15;

        const color = isLoss ? 'var(--danger)' : (isHigh ? 'var(--success)' : 'var(--warning)');
        const icon  = isLoss ? 'bi-arrow-down-right' : (isHigh ? 'bi-arrow-up-right' : 'bi-dash');
        const sign  = profit >= 0 ? '+' : '';

        return `<span style="font-size:10.5px;font-weight:600;color:${color};display:inline-flex;align-items:center;gap:3px;">
            <i class="bi ${icon}"></i> ${label}: ${sign}${mkp.toFixed(1)}% (Rp${Math.round(profit).toLocaleString('id-ID')})
        </span>`;
    }

    return {
        calculateNett,
        calculateMarkup,
        calculateProfit,
        formatMarginBadge
    };
})();

// Export for module/Node environments if available
if (typeof module !== 'undefined' && module.exports) {
    module.exports = PricingHelper;
}
