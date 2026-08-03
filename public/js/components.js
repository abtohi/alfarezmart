/**
 * AlfarezMart PWA - Reusable UI Components
 * 
 * AppModal  — Bottom-sheet style modal dialog
 * SearchBox — Searchable combobox/dropdown replacement
 */

/* ============================================
   AppModal — Modern bottom-sheet dialog
   ============================================ */
const AppModal = {
    _overlay: null,
    _resolve: null,

    /**
     * Initialize modal container (called once on DOMContentLoaded)
     */
    init() {
        if (document.getElementById('appModalOverlay')) return;
        const el = document.createElement('div');
        el.id = 'appModalOverlay';
        el.className = 'modal-overlay';
        el.innerHTML = `<div class="modal-dialog" id="appModalDialog">
            <div class="modal-handle"></div>
            <div class="modal-header">
                <div class="modal-header-title">
                    <div class="modal-icon" id="appModalIcon"></div>
                    <div>
                        <h3 id="appModalTitle"></h3>
                        <div class="modal-subtitle" id="appModalSubtitle"></div>
                    </div>
                </div>
                <button class="modal-close" id="appModalClose"><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="modal-body" id="appModalBody"></div>
            <div class="modal-footer" id="appModalFooter">
                <button class="btn-modal-cancel" id="appModalCancelBtn">Batal</button>
                <button class="btn-modal-extra" id="appModalExtraBtn" style="display:none;"></button>
                <button class="btn-modal-submit" id="appModalSubmitBtn">Simpan</button>
            </div>
        </div>`;
        document.body.appendChild(el);
        this._overlay = el;

        // Close handlers
        document.getElementById('appModalClose').addEventListener('click', () => this.close(null));
        document.getElementById('appModalCancelBtn').addEventListener('click', () => this.close(null));
        
        // Close modal when clicking on the overlay (backdrop), not the dialog
        const dialog = document.getElementById('appModalDialog');
        dialog.addEventListener('click', (e) => { e.stopPropagation(); });
        el.addEventListener('click', (e) => { 
            if (e.target === el) this.close(null);
        });
    },

    /**
     * Show modal with configuration
     * @param {Object} config
     * @param {string} config.title — Modal title
     * @param {string} [config.subtitle] — Subtitle text
     * @param {string} [config.icon] — Bootstrap icon class (e.g. 'bi-tag')
     * @param {string} [config.iconColor] — CSS color for icon background
     * @param {string} config.bodyHTML — HTML content for modal body
     * @param {string} [config.submitText] — Submit button label (default: 'Simpan')
     * @param {string} [config.cancelText] — Cancel button label (default: 'Batal')
     * @param {string} [config.extraBtnText] — Extra middle button label (optional)
     * @param {Function} [config.onExtra] — Called when extra button clicked
     * @param {Function} [config.onSubmit] — Called when submit clicked. Gets formData arg.
     * @param {boolean} [config.hideFooter] — Hide footer buttons
     * @returns {Promise} Resolves when modal closes (with result or null)
     */
    show(config) {
        this.init();
        const overlay = this._overlay;
        const icon = document.getElementById('appModalIcon');
        const title = document.getElementById('appModalTitle');
        const subtitle = document.getElementById('appModalSubtitle');
        const body = document.getElementById('appModalBody');
        const footer = document.getElementById('appModalFooter');
        const submitBtn = document.getElementById('appModalSubmitBtn');
        const cancelBtn = document.getElementById('appModalCancelBtn');
        const extraBtn = document.getElementById('appModalExtraBtn');

        // Handle centered option
        if (config.centered) {
            this._overlay.classList.add('modal-centered');
        } else {
            this._overlay.classList.remove('modal-centered');
        }

        // Set content
        title.textContent = config.title || '';
        subtitle.textContent = config.subtitle || '';
        subtitle.style.display = config.subtitle ? 'block' : 'none';

        if (config.icon) {
            icon.innerHTML = `<i class="bi ${config.icon}"></i>`;
            icon.style.background = config.iconColor || 'var(--primary-bg)';
            icon.style.color = config.iconAccent || 'var(--primary)';
            icon.style.display = 'flex';
        } else {
            icon.style.display = 'none';
        }

        body.innerHTML = config.bodyHTML || '';
        submitBtn.innerHTML = config.submitText || 'Simpan';
        cancelBtn.textContent = config.cancelText || 'Batal';

        if (extraBtn) {
            if (config.extraBtnText) {
                extraBtn.innerHTML = config.extraBtnText;
                extraBtn.style.display = 'inline-flex';
                extraBtn.className = config.extraBtnClass || 'btn-modal-extra';
                extraBtn.onclick = async () => {
                    if (config.onExtra) {
                        extraBtn.disabled = true;
                        try {
                            const res = await config.onExtra();
                            if (res !== false) this.close('extra');
                        } catch (e) {
                            console.error('onExtra error:', e);
                        } finally {
                            extraBtn.disabled = false;
                        }
                    } else {
                        this.close('extra');
                    }
                };
            } else {
                extraBtn.style.display = 'none';
            }
        }

        footer.style.display = config.hideFooter ? 'none' : 'flex';

        // Focus first input after animation
        setTimeout(() => {
            const firstInput = body.querySelector('input:not([type=hidden]), textarea, select');
            if (firstInput) firstInput.focus();
        }, 350);

        // Submit handler
        submitBtn.onclick = async () => {
            if (config.onSubmit) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="spinner-border spinner-border-sm"></i>';
                try {
                    const result = await config.onSubmit();
                    if (result !== false) this.close(result);
                } catch (e) {
                    // onSubmit handles its own errors
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = config.submitText || 'Simpan';
                }
            } else {
                this.close('submit');
            }
        };

        // Show
        overlay.classList.add('active');
        document.body.style.overflow = 'hidden';

        if (typeof config.onShown === 'function') {
            setTimeout(config.onShown, 50);
        }

        return new Promise(resolve => { this._resolve = resolve; });
    },

    /**
     * Close modal
     * @param {*} result — Value to resolve promise with
     */
    close(result) {
        if (this._overlay) {
            this._overlay.classList.remove('active');
            document.body.style.overflow = '';
        }
        if (this._resolve) {
            this._resolve(result);
            this._resolve = null;
        }
    },

    /**
     * Helper for quick confirmation dialogs
     */
    confirm(title, message, confirmText = 'Ya', confirmColor = 'var(--danger)') {
        return this.show({
            title: title,
            bodyHTML: `<p style="font-size:var(--font-size-sm);color:var(--text-secondary);">${message}</p>`,
            icon: 'bi-question-circle',
            iconColor: 'var(--warning-bg)',
            iconAccent: 'var(--warning)',
            submitText: confirmText,
            onSubmit: () => true
        });
    }
};


/* ============================================
   SearchBox — Searchable combobox
   ============================================ */
class SearchBox {
    /**
     * @param {HTMLElement} container — The wrapper element
     * @param {Object} config
     * @param {Array} config.options — [{value, label}]
     * @param {string} [config.placeholder] — Placeholder text
     * @param {string} [config.icon] — Bootstrap icon class
     * @param {Function} [config.onSelect] — Callback(value, label)
     * @param {Function} [config.onChange] — Callback(value, label) same as onSelect
     * @param {Function} [config.onAdd] — Callback() for adding new item. If set, shows "+" button
     * @param {string} [config.addLabel] — Label for add button
     * @param {string} [config.name] — Hidden input name for form submission
     * @param {string} [config.value] — Initial selected value
     * @param {boolean} [config.required] — Whether field is required
     * @param {boolean} [config.clearable] — Show clear button when a value is selected
     */
    constructor(container, config) {
        this.container = container;
        this.config = config;
        this.options = [...(config.options || [])];
        this.selectedValue = config.value || '';
        this.selectedLabel = '';
        this.isOpen = false;
        this._backdrop = null;

        this._buildDOM();
        this._bindEvents();

        // Set initial value
        if (this.selectedValue) {
            const found = this.options.find(o => String(o.value) === String(this.selectedValue));
            if (found) {
                this.selectedLabel = found.label;
                this._updateDisplay();
            }
        }
    }

    setRequired(isRequired) {
        this.config.required = !!isRequired;
        if (this._hiddenInput) {
            this._hiddenInput.required = this.config.required;
        }
    }

    _buildDOM() {
        const c = this.config;
        const iconHTML = c.icon ? `<i class="bi ${c.icon} sb-icon"></i>` : '';
        const selectedLabel = this._getSelectedLabel();
        const clearBtnHTML = c.clearable
            ? `<button type="button" class="sb-clear" aria-label="Hapus pilihan" title="Hapus pilihan" style="display:none;"><i class="bi bi-x-circle-fill"></i></button>`
            : '';

        this.container.classList.add('searchbox-wrapper');
        this.container.innerHTML = `
            <input type="hidden" name="${c.name || ''}" value="${this.selectedValue}" ${c.required ? 'required' : ''}>
            <div class="searchbox-trigger" tabindex="0">
                ${iconHTML}
                <span class="sb-value ${!selectedLabel ? 'sb-placeholder' : ''}">${selectedLabel || c.placeholder || 'Pilih...'}</span>
                ${clearBtnHTML}
                <i class="bi bi-chevron-down sb-arrow"></i>
            </div>
            <div class="searchbox-dropdown">
                <div class="searchbox-search">
                    <i class="bi bi-search"></i>
                    <input type="text" placeholder="Cari..." autocomplete="off">
                </div>
                <div class="searchbox-options"></div>
                ${c.onAdd ? `<div class="searchbox-add-btn"><i class="bi bi-plus-circle"></i> ${c.addLabel || 'Tambah Baru'}</div>` : ''}
                ${c.linkUrl ? `<a href="${c.linkUrl}" target="_blank" class="searchbox-link-btn" style="display:flex;align-items:center;gap:8px;padding:10px 14px;font-size:var(--font-size-sm);color:var(--info);text-decoration:none;border-top:1px solid rgba(255,255,255,0.05);font-weight:600;background:var(--surface-1);"><i class="bi bi-box-arrow-up-right"></i> ${c.linkLabel || 'Kelola Data'}</a>` : ''}
            </div>
        `;

        this._trigger = this.container.querySelector('.searchbox-trigger');
        this._dropdown = this.container.querySelector('.searchbox-dropdown');
        this._searchInput = this._dropdown.querySelector('.searchbox-search input');
        this._optionsList = this._dropdown.querySelector('.searchbox-options');
        this._hiddenInput = this.container.querySelector('input[type=hidden]');
        this._valueDisplay = this._trigger.querySelector('.sb-value');
        this._addBtn = this._dropdown.querySelector('.searchbox-add-btn');
        this._clearBtn = this._trigger.querySelector('.sb-clear');

        // Move dropdown to body to avoid CSS transform containing block issues
        document.body.appendChild(this._dropdown);

        this._renderOptions();
        this._syncClearButton();
    }

    _bindEvents() {
        // Toggle dropdown
        this._trigger.addEventListener('click', (e) => {
            if (e.target.closest('.sb-clear')) return;
            e.stopPropagation();
            this.isOpen ? this.close() : this.open();
        });

        if (this._clearBtn) {
            this._clearBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.clear();
            });
        }

        // Search filter
        this._searchInput.addEventListener('input', () => {
            this._renderOptions(this._searchInput.value.trim().toLowerCase());
        });

        // Keyboard navigation
        this._searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') { this.close(); }
            else if (e.key === 'Enter') {
                e.preventDefault();
                e.stopPropagation();
                const highlighted = this._optionsList.querySelector('.highlighted');
                if (highlighted) highlighted.click();
            } else if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                e.preventDefault();
                this._navigateOptions(e.key === 'ArrowDown' ? 1 : -1);
            }
        });

        // Add button
        if (this._addBtn && this.config.onAdd) {
            this._addBtn.addEventListener('click', async (e) => {
                e.stopPropagation();
                this.close();
                await this.config.onAdd();
            });
        }

        // Close on outside click
        document.addEventListener('click', (e) => {
            if (this.isOpen && !this.container.contains(e.target)) {
                this.close();
            }
        });
    }

    _renderOptions(filter = '') {
        let filtered = this.options;
        if (filter) {
            const words = filter.split(' ').filter(w => w);
            filtered = this.options.filter(o => {
                const lowerLabel = o.label.toLowerCase();
                return words.every(w => lowerLabel.includes(w));
            });
        }

        if (filtered.length === 0) {
            this._optionsList.innerHTML = `<div class="searchbox-empty"><i class="bi bi-inbox"></i> Tidak ditemukan</div>`;
            return;
        }

        this._optionsList.innerHTML = filtered.map(o => `
            <div class="searchbox-option ${String(o.value) === String(this.selectedValue) ? 'selected' : ''}" data-value="${o.value}">
                <span class="sb-opt-check"><i class="bi bi-check2"></i></span>
                <span>${this._highlight(o.label, filter)}</span>
            </div>
        `).join('');

        // Bind click
        this._optionsList.querySelectorAll('.searchbox-option').forEach(el => {
            el.addEventListener('click', (e) => {
                e.stopPropagation();
                const val = el.dataset.value;
                const opt = this.options.find(o => String(o.value) === String(val));
                if (opt) this.select(opt.value, opt.label);
            });
        });
    }

    _highlight(text, filter) {
        if (!filter) return text;
        const words = filter.split(' ').filter(w => w);
        if (words.length === 0) return text;
        
        let highlighted = text;
        words.forEach(word => {
            const regex = new RegExp(`(${word.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
            highlighted = highlighted.replace(regex, '___HIGHLIGHT_START___$1___HIGHLIGHT_END___');
        });
        
        return highlighted.replace(/___HIGHLIGHT_START___/g, '<strong style="color:var(--primary)">').replace(/___HIGHLIGHT_END___/g, '</strong>');
    }

    _navigateOptions(direction) {
        const items = [...this._optionsList.querySelectorAll('.searchbox-option')];
        if (items.length === 0) return;
        const currentIdx = items.findIndex(el => el.classList.contains('highlighted'));
        items.forEach(el => el.classList.remove('highlighted'));
        let nextIdx = currentIdx + direction;
        if (nextIdx < 0) nextIdx = items.length - 1;
        if (nextIdx >= items.length) nextIdx = 0;
        items[nextIdx].classList.add('highlighted');
        items[nextIdx].scrollIntoView({ block: 'nearest' });
    }

    _getSelectedLabel() {
        const found = this.options.find(o => String(o.value) === String(this.selectedValue));
        return found ? found.label : '';
    }

    _updateDisplay() {
        if (this.selectedLabel) {
            this._valueDisplay.textContent = this.selectedLabel;
            this._valueDisplay.classList.remove('sb-placeholder');
        } else {
            this._valueDisplay.textContent = this.config.placeholder || 'Pilih...';
            this._valueDisplay.classList.add('sb-placeholder');
        }
        this._syncClearButton();
    }

    _syncClearButton() {
        if (!this._clearBtn) return;
        const hasValue = this.selectedValue !== '' && this.selectedValue != null;
        this._clearBtn.style.display = hasValue ? 'flex' : 'none';
    }

    /** Clear selection (does not close dropdown) */
    clear() {
        const hadValue = !!this.selectedValue;
        this.selectedValue = '';
        this.selectedLabel = '';
        this._hiddenInput.value = '';
        this._updateDisplay();
        this._hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
        if (hadValue && this.config.onClear) this.config.onClear();
    }

    open() {
        this.isOpen = true;
        this._trigger.classList.add('active');
        this._searchInput.value = '';
        this._renderOptions();

        // Position dropdown using fixed coordinates from trigger rect
        this._positionDropdown();

        this._dropdown.classList.add('open');
        
        // Delay focus to prevent browser auto-scroll + listen for keyboard changes
        this._setupKeyboardDetection();
        setTimeout(() => {
            // Only focus if dropdown still open (user might have closed it)
            if (this.isOpen && this._searchInput) {
                this._searchInput.focus();
            }
        }, 100);

        // Add backdrop (pointer-events:none so it doesn't block clicks inside modal stacking contexts)
        if (!this._backdrop) {
            this._backdrop = document.createElement('div');
            this._backdrop.className = 'searchbox-backdrop active';
            this._backdrop.style.pointerEvents = 'none';
            document.body.appendChild(this._backdrop);
        } else {
            this._backdrop.classList.add('active');
        }
    }

    _setupKeyboardDetection() {
        // For mobile: detect virtual keyboard and reposition if needed
        if (!('visualViewport' in window)) return;
        
        const visualViewport = window.visualViewport;
        let lastHeight = visualViewport.height;
        
        const handler = () => {
            const currentHeight = visualViewport.height;
            // If viewport height decreased, keyboard likely appeared
            if (currentHeight < lastHeight) {
                lastHeight = currentHeight;
                if (this.isOpen) {
                    setTimeout(() => this._repositionForKeyboard(), 50);
                }
            } else {
                lastHeight = currentHeight;
            }
        };
        
        visualViewport.addEventListener('resize', handler);
        // Store handler reference for cleanup
        if (!this._keyboardHandler) {
            this._keyboardHandler = handler;
        }
    }

    _repositionForKeyboard() {
        if (!this.isOpen || !this._dropdown) return;
        
        const rect = this._trigger.getBoundingClientRect();
        const viewportWidth = window.innerWidth;
        const visualViewport = window.visualViewport;
        const availableHeight = visualViewport ? visualViewport.height : window.innerHeight;
        
        // Calculate space available below trigger
        const spaceBelow = availableHeight - rect.bottom;
        const spaceAbove = rect.top;
        const dropdownHeight = Math.min(260, this._dropdown.scrollHeight);
        
        // Ensure minimum space above keyboard
        const keyboardBuffer = 16;
        
        if (spaceBelow < dropdownHeight + keyboardBuffer && spaceAbove > dropdownHeight + keyboardBuffer) {
            // Open upwards
            const topValue = rect.top - dropdownHeight - 8;
            this._dropdown.style.top = Math.max(8, topValue) + 'px';
            this._dropdown.style.bottom = 'auto';
        } else {
            // Open downwards
            this._dropdown.style.top = (rect.bottom + 8) + 'px';
            this._dropdown.style.bottom = 'auto';
        }
    }

    _positionDropdown() {
        const rect = this._trigger.getBoundingClientRect();
        const viewportWidth = window.innerWidth;
        const visualViewport = window.visualViewport;
        const viewportHeight = visualViewport ? visualViewport.height : window.innerHeight;
        
        // Use fixed positioning for both mobile and desktop (consistency)
        this._dropdown.style.position = 'fixed';
        
        // Set width to match trigger width (or min/max bounds)
        let width = Math.max(rect.width, 200);
        width = Math.min(width, viewportWidth - 16);
        this._dropdown.style.width = width + 'px';
        this._dropdown.style.maxWidth = 'none';
        
        // Calculate horizontal position
        let left = rect.left;
        if (left + width > viewportWidth - 8) {
            left = viewportWidth - width - 8;
        }
        if (left < 8) left = 8;
        this._dropdown.style.left = left + 'px';
        this._dropdown.style.right = 'auto';
        
        // Calculate vertical position with keyboard awareness
        const dropdownHeight = 260;
        const spaceBelow = viewportHeight - rect.bottom;
        const spaceAbove = rect.top;
        const gapSize = 8;
        const keyboardBuffer = 16;
        
        // Try to open downwards if enough space
        let shouldOpenBelow = spaceBelow >= dropdownHeight + keyboardBuffer;
        
        // If not enough space below, check if can open above
        if (!shouldOpenBelow && spaceAbove > spaceBelow) {
            shouldOpenBelow = false;
        } else if (!shouldOpenBelow) {
            // Not enough space either direction - default to below
            shouldOpenBelow = true;
        }
        
        if (shouldOpenBelow) {
            this._dropdown.style.top = (rect.bottom + gapSize) + 'px';
            this._dropdown.style.bottom = 'auto';
        } else {
            this._dropdown.style.top = 'auto';
            this._dropdown.style.bottom = (viewportHeight - rect.top + gapSize) + 'px';
        }
        
        // Set max-height based on available space
        let maxHeight = dropdownHeight;
        if (shouldOpenBelow) {
            maxHeight = Math.max(150, viewportHeight - rect.bottom - gapSize - keyboardBuffer);
        } else {
            maxHeight = Math.max(150, rect.top - gapSize - keyboardBuffer);
        }
        this._dropdown.style.maxHeight = maxHeight + 'px';
        
        // Styling
        this._dropdown.style.borderRadius = 'var(--radius-md)';
        this._dropdown.style.border = '1px solid var(--border-color)';
        this._dropdown.style.boxShadow = 'var(--shadow-lg)';
        this._dropdown.style.zIndex = 'calc(var(--z-modal) + 10)';
        
        // Animation transform
        this._dropdown.style.transform = 'translateY(-8px)';
        void this._dropdown.offsetWidth; // Force reflow
        
        if (this.isOpen) {
            this._dropdown.style.transform = 'translateY(0)';
        }
        
        // Setup backdrop
        if (this._backdrop) {
            this._backdrop.style.background = 'transparent';
            this._backdrop.style.backdropFilter = 'none';
            this._backdrop.style.webkitBackdropFilter = 'none';
        }
    }

    close() {
        this.isOpen = false;
        this._trigger.classList.remove('active');
        this._dropdown.classList.remove('open');
        this._dropdown.style.transform = '';
        
        // Cleanup keyboard handler
        if (this._keyboardHandler && 'visualViewport' in window) {
            window.visualViewport.removeEventListener('resize', this._keyboardHandler);
            this._keyboardHandler = null;
        }
        
        if (this._backdrop) {
            this._backdrop.classList.remove('active');
            this._backdrop.style.background = 'transparent';
            this._backdrop.style.backdropFilter = 'none';
            this._backdrop.style.webkitBackdropFilter = 'none';
        }
    }

    select(value, label) {
        this.selectedValue = String(value);
        this.selectedLabel = label;
        this._hiddenInput.value = this.selectedValue;
        this._updateDisplay();
        this.close();

        // Trigger change event on hidden input
        this._hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));

        if (this.config.onSelect) this.config.onSelect(value, label);
        if (this.config.onChange) this.config.onChange(value, label);
    }

    /** Add a new option and optionally select it */
    addOption(value, label, autoSelect = true) {
        this.options.push({ value: String(value), label });
        if (autoSelect) this.select(value, label);
        else this._renderOptions();
    }

    /** Update entire options list */
    setOptions(options) {
        this.options = [...options];
        this._renderOptions();
        // If current selected value no longer exists, clear
        if (this.selectedValue && !this.options.find(o => String(o.value) === String(this.selectedValue))) {
            this.selectedValue = '';
            this.selectedLabel = '';
            this._hiddenInput.value = '';
            this._updateDisplay();
        }
    }

    /** Programmatically set value without triggering callbacks */
    setValue(value, label = '') {
        this.selectedValue = String(value);
        if (label) {
            this.selectedLabel = label;
        } else {
            const found = this.options.find(o => String(o.value) === String(this.selectedValue));
            this.selectedLabel = found ? found.label : '';
        }
        this._hiddenInput.value = this.selectedValue;
        this._updateDisplay();
    }

    /** Get current value */
    getValue() { return this.selectedValue; }

    /** Get current label */
    getLabel() { return this.selectedLabel; }



    /** Programmatic reset */
    reset() {
        this.clear();
    }

    /** Destroy and clean up */
    destroy() {
        // Cleanup keyboard handler
        if (this._keyboardHandler && 'visualViewport' in window) {
            window.visualViewport.removeEventListener('resize', this._keyboardHandler);
            this._keyboardHandler = null;
        }
        
        if (this._backdrop) {
            this._backdrop.remove();
            this._backdrop = null;
        }
        if (this._dropdown) {
            this._dropdown.remove();
            this._dropdown = null;
        }
        this.container.innerHTML = '';
        this.container.classList.remove('searchbox-wrapper');
    }
}

// Initialize modal on DOMContentLoaded
document.addEventListener('DOMContentLoaded', () => {
    AppModal.init();

    // Prevent right-click / context menu globally
    document.addEventListener('contextmenu', e => {
        // Allow context menu if the user is clicking on an input/textarea
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.isContentEditable) {
            return;
        }
        e.preventDefault();
    });
});
