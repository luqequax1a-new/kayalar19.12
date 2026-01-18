(function () {
    const MODAL_ID = "#bulk-edit-modal";
    const DEBUG = true;

    function log(...args) { if (DEBUG) console.log("[BulkEditPro]", ...args); }

    // --- Selection Detection ---
    function getSelectedIds() {
        if (window.DataTable && typeof window.DataTable.getSelectedIds === 'function') {
            const ids = window.DataTable.getSelectedIds(".table");
            if (ids && ids.length > 0) return ids.map(id => id.toString());
        }
        const checkboxes = document.querySelectorAll('.table input[type="checkbox"]:checked, input.select-row:checked, input[name="product_ids[]"]:checked');
        const ids = Array.from(checkboxes).map(cb => cb.value).filter(v => v && !isNaN(v));
        return [...new Set(ids)];
    }

    function updateSelectionUI() {
        const ids = getSelectedIds();
        const count = ids.length;
        const scope = document.querySelector('input[name="bulk_scope"]:checked')?.value || "selected";
        const titleEl = document.getElementById("bulk-edit-modal-title");
        const countLabel = document.getElementById("lbl-selected-count");

        if (scope === "all") {
            if (titleEl) titleEl.innerText = "Tüm Ürünleri Düzenle";
            if (countLabel) countLabel.innerText = "Tüm Ürünler";
        } else {
            if (titleEl) titleEl.innerText = `${count} Ürünü Düzenle`;
            if (countLabel) countLabel.innerText = `Seçilen ${count} Ürün`;
        }
    }

    // --- Category Selectize ---
    function initCategorySelectize(el, multiple = false) {
        const categories = window.BulkEditData?.categories || {};
        const $el = $(el);
        if ($el[0].selectize) $el[0].selectize.destroy();

        const options = Object.keys(categories).map(id => {
            const name = categories[id];
            return {
                id: id,
                name: name.replace(/&nbsp;/g, "").trim(),
                level: (name.match(/&nbsp;/g) || []).length
            };
        });

        $el.selectize({
            valueField: "id", labelField: "name", searchField: ["name"],
            options: options, maxItems: multiple ? null : 1,
            plugins: multiple ? ["remove_button"] : [],
            dropdownParent: null, // Render adjacent to input for perfect positioning
            dropdownClass: "ikas-pro-cat-dropdown",
            render: {
                option: (item, escape) => {
                    const indent = (item.level || 0) * 12;
                    return `<div class="option" style="padding-left: ${16 + indent}px">
                        ${item.level > 0 ? '<span style="color:#d1d5db;margin-right:8px">↳</span>' : ''}
                        <span>${escape(item.name)}</span>
                    </div>`;
                },
                item: (item, escape) => `<div class="item">${escape(item.name)}</div>`
            }
        });
    }

    // --- Action Management ---
    const ATTR_CONFIG = {
        name: { label: "Ürün Adı", icon: "fa-font", type: "text", modes: ["set", "search_replace", "prefix", "suffix"] },
        sku: { label: "SKU", icon: "fa-barcode", type: "text", modes: ["set", "search_replace", "prefix", "suffix"] },
        price: { label: "Satış Fiyatı", icon: "fa-tag", type: "number", modes: ["set", "increase_percent", "decrease_percent", "increase_fixed", "decrease_fixed"] },
        special_price: { label: "İndirimli Fiyat", icon: "fa-percent", type: "number", modes: ["set", "increase_percent", "decrease_percent", "increase_fixed", "decrease_fixed", "clear"] },
        inventory: { label: "Stok", icon: "fa-archive", type: "inventory", modes: ["set"] },
        primary_category: { label: "Ana Kategori", icon: "fa-star", type: "category", modes: ["set", "clear"] },
        category_action: { label: "Kategoriler", icon: "fa-folder-open", type: "categories", modes: ["add", "remove"] },
        is_active: { label: "Durum", icon: "fa-toggle-on", type: "toggle", modes: ["set"] },
        brand_id: { label: "Marka", icon: "fa-copyright", type: "brand", modes: ["set", "clear"] },
        tax_class_id: { label: "Vergi Sınıfı", icon: "fa-calculator", type: "tax", modes: ["set", "clear"] },
        description: { label: "Açıklama", icon: "fa-align-left", type: "text", modes: ["set", "search_replace", "prefix", "suffix"] },
        short_description: { label: "Kısa Açıklama", icon: "fa-align-justify", type: "text", modes: ["set", "search_replace", "prefix", "suffix"] }
    };

    function buildActionRow(attr) {
        const config = ATTR_CONFIG[attr];
        if (!config) return null;

        const row = document.createElement("div");
        row.className = "ikas-action-row";
        row.dataset.attribute = attr;

        // Label Column
        const labelCol = document.createElement("div");
        labelCol.className = "ikas-row-label";
        labelCol.innerHTML = `<i class="fa ${config.icon}"></i> <span>${config.label}</span>`;
        row.appendChild(labelCol);

        // Mode Column
        const modeSelect = document.createElement("select");
        modeSelect.className = "ikas-select";
        const modeLabels = {
            set: "Güncelle", increase_percent: "% Artır", decrease_percent: "% Azalt",
            increase_fixed: "Tutar Artır", decrease_fixed: "Tutar Azalt", clear: "Temizleme",
            add: "Ekle", remove: "Çıkar", search_replace: "Bul/Değiştir", prefix: "Başına Ekle", suffix: "Sonuna Ekle"
        };
        modeSelect.innerHTML = config.modes.map(m => `<option value="${m}">${modeLabels[m] || m}</option>`).join("");
        row.appendChild(modeSelect);

        // Value Column
        const valueCol = document.createElement("div");
        valueCol.className = "ikas-row-value";
        row.appendChild(valueCol);

        const buildValueInput = () => {
            valueCol.innerHTML = "";
            const mode = modeSelect.value;
            if (mode === "clear") return;

            if (config.type === "inventory") {
                const wrap = document.createElement("div"); wrap.className = "ikas-inventory-group";
                wrap.innerHTML = `
                    <select class="ikas-select ikas-stock-manage" style="width:160px"><option value="1">Stok takibi yap</option><option value="0">Stok takibi yapma</option></select>
                    <input type="number" step="any" min="0" class="ikas-input ikas-stock-qty" placeholder="Miktar" style="flex:1">
                `;
                valueCol.appendChild(wrap);
                const s = wrap.querySelector(".ikas-stock-manage"); const q = wrap.querySelector(".ikas-stock-qty");
                s.onchange = () => { q.style.display = s.value === "1" ? "block" : "none"; if (s.value === "0") q.value = ""; };
                s.onchange();
            } else if (config.type === "category" || config.type === "categories") {
                const s = document.createElement("select");
                valueCol.appendChild(s);
                initCategorySelectize(s, config.type === "categories");
            } else if (config.type === "brand" || config.type === "tax") {
                const s = document.createElement("select"); s.className = "ikas-select";
                const list = (config.type === "brand" ? window.BulkEditData?.brands : window.BulkEditData?.taxClasses) || {};
                s.innerHTML = Object.keys(list).map(id => `<option value="${id}">${list[id]}</option>`).join("");
                valueCol.appendChild(s);
            } else if (config.type === "toggle") {
                const s = document.createElement("select"); s.className = "ikas-select";
                s.innerHTML = '<option value="1">Aktif</option><option value="0">Pasif</option>';
                valueCol.appendChild(s);
            } else if (mode === "search_replace") {
                const wrap = document.createElement("div"); wrap.className = "ikas-inventory-group";
                wrap.innerHTML = `<input type="text" class="ikas-input ikas-sr-search" placeholder="Aranan" style="flex:1"><input type="text" class="ikas-input ikas-sr-replace" placeholder="Yeni" style="flex:1">`;
                valueCol.appendChild(wrap);
            } else {
                const i = document.createElement("input"); i.className = "ikas-input";
                i.type = config.type === "number" ? "number" : "text";
                if (config.type === "number") i.step = "any";
                i.placeholder = "Yeni değer girin...";
                valueCol.appendChild(i);
            }
        };

        modeSelect.onchange = () => { buildValueInput(); };
        buildValueInput();

        // Remove Column
        const removeBtn = document.createElement("button");
        removeBtn.className = "btn-remove-row";
        removeBtn.innerHTML = "&times;";
        removeBtn.onclick = () => { row.remove(); handleEmptyState(); };
        row.appendChild(removeBtn);

        // Remove empty state if this is the first row
        const list = document.getElementById("bulk-action-list");
        const empty = list.querySelector(".ikas-empty-state");
        if (empty) empty.remove();

        return row;
    }

    function handleEmptyState() {
        const list = document.getElementById("bulk-action-list");
        if (list.children.length === 0) {
            list.innerHTML = `<div class="ikas-empty-state"><i class="fa fa-plus-circle"></i><p>Henüz bir işlem eklemediniz. Yukarıdaki butonları kullanarak başlayın.</p></div>`;
        }
    }

    // --- Update Logic ---

    function collectActions() {
        const rows = document.querySelectorAll(".ikas-action-row");
        const actions = [];
        rows.forEach(row => {
            const attr = row.dataset.attribute;
            const mode = row.querySelector(".ikas-select").value;
            const config = ATTR_CONFIG[attr];

            if (attr === "inventory") {
                const m = parseInt(row.querySelector(".ikas-stock-manage").value);
                const q = row.querySelector(".ikas-stock-qty").value;
                actions.push({ attribute: "manage_stock", mode: "set", value: m });
                if (m === 1 && q !== "") {
                    const fq = parseFloat(q);
                    actions.push({ attribute: "qty", mode: "set", value: fq });
                    actions.push({ attribute: "in_stock", mode: "set", value: fq > 0 ? 1 : 0 });
                } else if (m === 0) actions.push({ attribute: "in_stock", mode: "set", value: 1 });
                return;
            }

            let val = null;
            if (mode !== "clear") {
                const valueWrap = row.querySelector(".ikas-row-value");
                if (config.type === "category" || config.type === "categories") {
                    const el = valueWrap.querySelector("select");
                    val = el.selectize ? el.selectize.getValue() : el.value;
                    if (config.type === "categories") val = (Array.isArray(val) ? val : [val]).filter(v => v).map(Number);
                    else val = val ? Number(val) : null;
                } else if (mode === "search_replace") {
                    val = { search: valueWrap.querySelector(".ikas-sr-search").value, replace: valueWrap.querySelector(".ikas-sr-replace").value };
                } else {
                    const el = valueWrap.querySelector(".ikas-input, select");
                    val = el ? el.value : null;
                    if (config.type === "number" || ["brand_id", "tax_class_id", "is_active"].includes(attr)) {
                        val = (val === "" || val === null) ? null : (config.type === "number" ? parseFloat(val) : parseInt(val));
                    }
                }
            }
            actions.push({ attribute: attr, mode: mode, value: val });
        });
        return actions;
    }

    // --- Initialization ---
    function init() {
        // Toolbar Buttons
        document.querySelectorAll(".btn-add-attr, .btn-add-attr-link").forEach(btn => {
            btn.onclick = (e) => {
                e.preventDefault();
                const attr = btn.dataset.attr;
                const row = buildActionRow(attr);
                if (row) {
                    document.getElementById("bulk-action-list").appendChild(row);
                }
            };
        });

        // Scope Change
        document.querySelectorAll('input[name="bulk_scope"]').forEach(i => {
            i.onchange = () => { updateSelectionUI(); };
        });

        // Open Modal Trigger
        document.addEventListener("click", e => {
            if (e.target.closest("#btn-open-bulk-edit")) {
                e.preventDefault();
                updateSelectionUI();
                document.getElementById("bulk-action-list").innerHTML = "";
                handleEmptyState();
                $(MODAL_ID).modal("show");
            }
        });

        // Apply Button
        document.getElementById("bulk-edit-apply-btn").onclick = async () => {
            const actions = collectActions();
            if (!actions.length) return;

            const btn = document.getElementById("bulk-edit-apply-btn");
            btn.disabled = true;
            btn.innerText = "Güncelleniyor...";

            const scope = document.querySelector('input[name="bulk_scope"]:checked').value;
            const ids = scope === "all" ? [] : getSelectedIds();

            try {
                const { data } = await axios.post(`${FleetCart.baseUrl}/admin/products/bulk-update`, {
                    product_ids: ids,
                    actions: actions,
                    apply_to_variants: document.getElementById("bulk-apply-to-variants").checked
                });
                if (window.toastr) window.toastr.success(data.message || "İşlem başarılı.");
                if (window.DataTable) window.DataTable.reload(".table");
                $(MODAL_ID).modal("hide");
            } catch (err) {
                if (window.toastr) window.toastr.error(err.response?.data?.message || "Bir hata oluştu.");
            } finally {
                btn.disabled = false;
                btn.innerText = "Değişiklikleri Uygula";
            }
        };
    }

    $(document).ready(init);
})();
