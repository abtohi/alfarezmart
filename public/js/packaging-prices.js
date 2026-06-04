/**
 * PackagingPriceSync - Auto-sync buy/retail/wholesale across packaging levels by base qty (pcs)
 */
const PackagingPriceSync = {
    isSyncing: false,

    getLevels() {
        return [...document.querySelectorAll('.packaging-level, .packaging-level-edit')];
    },

    getBaseQtys() {
        const levels = this.getLevels();
        return levels.map((lv, i) => {
            if (lv.dataset.baseQty) {
                return parseInt(lv.dataset.baseQty, 10) || 1;
            }
            if (i === 0) return 1;
            let running = 1;
            for (let j = 1; j <= i; j++) {
                const cqty = parseInt(levels[j].querySelector('.contained-qty')?.value, 10) || 0;
                if (cqty > 0) running *= cqty;
            }
            return running;
        });
    },

    getPriceInput(levelEl, field) {
        const selectors = {
            buy: '.buy-price, .pkg-buy',
            retail: '.retail-price, .pkg-retail',
            wholesale: '.wholesale-price, .pkg-wholesale',
        };
        const list = selectors[field]?.split(',').map(s => s.trim()) || [];
        for (const sel of list) {
            const el = levelEl.querySelector(sel);
            if (el) return el;
        }
        return null;
    },

    updateMargins(levelEl) {
        if (typeof calcMarginForLevel === 'function') {
            calcMarginForLevel(levelEl);
            return;
        }
        const row = levelEl.querySelector('[style*="rgba(0,0,0,0.15)"]') || levelEl;
        const buy = parseFloat(row.querySelector('.pkg-buy, .buy-price')?.value) || 0;
        const retail = parseFloat(row.querySelector('.pkg-retail, .retail-price')?.value) || 0;
        const wholesale = parseFloat(row.querySelector('.pkg-wholesale, .wholesale-price')?.value) || 0;
        const marginInfo = row.querySelector('.pkg-margin-info, .margin-calc');
        if (!marginInfo) return;
        
        const formatRp = (num) => 'Rp ' + Math.round(num).toLocaleString('id-ID');

        const rText = marginInfo.querySelector('.margin-retail-text');
        const wText = marginInfo.querySelector('.margin-wholesale-text');
        
        if (rText) {
            if (buy > 0 && retail > 0) {
                const m = ((retail - buy) / buy * 100).toFixed(1);
                const profit = retail - buy;
                const color = m >= 10 ? 'var(--success)' : (m >= 0 ? 'var(--warning)' : 'var(--danger)');
                rText.innerHTML = `Ecer: <strong style="color:${color}">${m}%</strong> <span style="font-size:10px;color:var(--text-muted);">(${formatRp(profit)})</span>`;
            } else {
                rText.innerHTML = 'Ecer: 0%';
            }
        }
        
        if (wText) {
            if (buy > 0 && wholesale > 0) {
                const m = ((wholesale - buy) / buy * 100).toFixed(1);
                const profit = wholesale - buy;
                const color = m >= 5 ? 'var(--success)' : (m >= 0 ? 'var(--warning)' : 'var(--danger)');
                wText.innerHTML = `Grosir: <strong style="color:${color}">${m}%</strong> <span style="font-size:10px;color:var(--text-muted);">(${formatRp(profit)})</span>`;
            } else {
                wText.innerHTML = 'Grosir: 0%';
            }
        }
    },

    syncFromInput(inputEl, field) {
        if (this.isSyncing) return;
        const levelEl = inputEl.closest('.packaging-level, .packaging-level-edit');
        if (!levelEl) return;

        const levels = this.getLevels();
        const index = levels.indexOf(levelEl);
        if (index < 0) return;

        // Point 1 Fix: Only Level 1 (index 0) is allowed to propagate prices to other levels.
        // If a user edits a higher level (whether custom or not), it should NEVER change Level 1.
        if (index !== 0) {
            this.updateMargins(levelEl);
            return;
        }

        const value = parseFloat(inputEl.value);
        if (!value || value <= 0) return;

        const baseQtys = this.getBaseQtys();
        const baseQty = baseQtys[index] || 1;
        const unitPrice = value / baseQty;

        this.isSyncing = true;
        levels.forEach((lv, i) => {
            if (i === index) return;
            const target = this.getPriceInput(lv, field);
            if (target) {
                // Check if this level has a custom toggle for this field
                const isBuy = field === 'buy';
                const isSell = field === 'retail' || field === 'wholesale';
                const buyCustom = lv.querySelector('.chk-buy-custom')?.checked;
                const sellCustom = lv.querySelector('.chk-sell-custom')?.checked;
                if (isBuy && buyCustom) return;   // skip — user set custom buy price
                if (isSell && sellCustom) return;  // skip — user set custom sell price

                // Use parseFloat with toFixed(2) to preserve up to 2 decimal places instead of Math.round
                const calculatedVal = unitPrice * (baseQtys[i] || 1);
                const newVal = Number.isInteger(calculatedVal) ? calculatedVal : parseFloat(calculatedVal.toFixed(2));
                target.value = newVal > 0 ? newVal : '';
            }
        });
        levels.forEach(lv => this.updateMargins(lv));
        this.isSyncing = false;
    },

    propagateAllFromLevel1() {
        const levels = this.getLevels();
        if (!levels.length) return;
        const level1 = levels[0];
        ['buy', 'retail', 'wholesale'].forEach(field => {
            const input = this.getPriceInput(level1, field);
            if (input && parseFloat(input.value) > 0) {
                this.syncFromInput(input, field);
            }
        });
        
        // Propagate PPN and Diskon
        const ppnInp = level1.querySelector('.ppn-input');
        if (ppnInp) this.syncTaxFromLevel1(ppnInp);
    },
    
    syncTaxFromLevel1(inputEl) {
        if (this.isSyncing) return;
        const levelEl = inputEl.closest('.packaging-level, .packaging-level-edit');
        const levels = this.getLevels();
        const index = levels.indexOf(levelEl);
        
        // Only Level 1 can propagate PPN/Diskon
        if (index !== 0) {
            this.updateMargins(levelEl);
            return;
        }

        const ppn = levelEl.querySelector('.ppn-input')?.value || '';
        const dMode = levelEl.querySelector('.discount-mode')?.value || 'rp';
        const dVal = levelEl.querySelector('.discount-value')?.value || '';

        this.isSyncing = true;
        levels.forEach((lv, i) => {
            if (i === 0) return;
            const targetPpn = lv.querySelector('.ppn-input');
            const targetDMode = lv.querySelector('.discount-mode');
            const targetDVal = lv.querySelector('.discount-value');
            
            if (targetPpn) targetPpn.value = ppn;
            if (targetDMode) targetDMode.value = dMode;
            if (targetDVal) targetDVal.value = dVal;
            
            this.updateMargins(lv);
        });
        this.isSyncing = false;
    },

    bindLevel(levelEl) {
        const bindings = [
            { selector: '.buy-price, .pkg-buy', field: 'buy' },
            { selector: '.retail-price, .pkg-retail', field: 'retail' },
            { selector: '.wholesale-price, .pkg-wholesale', field: 'wholesale' },
        ];
        bindings.forEach(({ selector, field }) => {
            levelEl.querySelectorAll(selector).forEach(inp => {
                if (inp.dataset.priceSyncBound) return;
                inp.dataset.priceSyncBound = '1';
                inp.addEventListener('input', () => {
                    this.syncFromInput(inp, field);
                    if (!this.isSyncing) this.updateMargins(levelEl);
                });
            });
        });

        // Bind PPN & Diskon to sync from Level 1 (both 'input' and 'change' for select elements)
        ['.ppn-input', '.discount-mode', '.discount-value'].forEach(sel => {
            levelEl.querySelectorAll(sel).forEach(inp => {
                if (inp.dataset.taxSyncBound) return;
                inp.dataset.taxSyncBound = '1';
                const handler = () => {
                    this.syncTaxFromLevel1(inp);
                    if (!this.isSyncing) this.updateMargins(levelEl);
                };
                inp.addEventListener('input', handler);
                inp.addEventListener('change', handler); // needed for <select> elements
            });
        });

        const cqty = levelEl.querySelector('.contained-qty');
        if (cqty && !cqty.dataset.priceSyncBound) {
            cqty.dataset.priceSyncBound = '1';
            cqty.addEventListener('input', () => {
                if (typeof updateBaseQtyInfo === 'function') updateBaseQtyInfo();
                this.propagateAllFromLevel1();
            });
        }

        // Bind custom toggle checkboxes
        this._bindCustomToggles(levelEl);
    },

    /** Wire up custom price toggle checkboxes for a level */
    _bindCustomToggles(levelEl) {
        const chkBuy = levelEl.querySelector('.chk-buy-custom');
        const chkSell = levelEl.querySelector('.chk-sell-custom');
        const buyNote = levelEl.querySelector('.buy-locked-note');
        const sellNote = levelEl.querySelector('.sell-locked-note');
        const buyToggle = levelEl.querySelector('.buy-custom-toggle');
        const sellToggle = levelEl.querySelector('.sell-custom-toggle');

        if (chkBuy) {
            chkBuy.addEventListener('change', () => {
                const isCustom = chkBuy.checked;
                if (buyToggle) buyToggle.classList.toggle('active', isCustom);
                if (buyNote) buyNote.classList.toggle('visible', !isCustom);
                // When enabling custom, unlock — when disabling, re-sync from level 1
                if (!isCustom) this.propagateAllFromLevel1();
            });
            // Initialize: show note if not custom and toggle active class
            if (buyNote) buyNote.classList.toggle('visible', !chkBuy.checked);
            if (buyToggle) buyToggle.classList.toggle('active', chkBuy.checked);
        }

        if (chkSell) {
            chkSell.addEventListener('change', () => {
                const isCustom = chkSell.checked;
                if (sellToggle) sellToggle.classList.toggle('active', isCustom);
                if (sellNote) sellNote.classList.toggle('visible', !isCustom);
                if (!isCustom) this.propagateAllFromLevel1();
            });
            // Initialize: show note if not custom and toggle active class
            if (sellNote) sellNote.classList.toggle('visible', !chkSell.checked);
            if (sellToggle) sellToggle.classList.toggle('active', chkSell.checked);
        }
    },

    bindNewLevel(levelEl) {
        this.bindLevel(levelEl);
        this.propagateAllFromLevel1();
    },

    init() {
        this.getLevels().forEach(lv => this.bindLevel(lv));
    },
};
