import axios from "axios";

function getCsrf() {
    return (
        FleetCart?.csrfToken ||
        document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content") ||
        ""
    );
}

function formatQty(qty, suffix) {
    const n = Number(qty);
    if (!Number.isFinite(n)) return "—";
    const isInt = Math.abs(n - Math.round(n)) < 1e-9;
    const s = isInt ? String(Math.round(n)) : String(n).replace(/0+$/, "").replace(/\.$/, "");
    const suf = String(suffix || "").trim();
    return suf ? `${s} ${suf}` : s;
}

function openEditor(container) {
    container.classList.add("is-editing");
    const input = container.querySelector("[data-ls-qty-input]");
    if (input) {
        input.focus();
        try {
            input.select();
        } catch (e) {
        }
    }
}

function closeEditor(container) {
    container.classList.remove("is-editing");
}

function showLightbox(root, url) {
    const lb = root.querySelector("[data-ls-lightbox]");
    const img = root.querySelector("[data-ls-lightbox-img]");
    if (!lb || !img) return;

    img.src = url;
    lb.classList.add("open");
}

function closeLightbox(root) {
    const lb = root.querySelector("[data-ls-lightbox]");
    const img = root.querySelector("[data-ls-lightbox-img]");
    if (!lb) return;

    lb.classList.remove("open");
    if (img) img.src = "";
}

async function updateInventory(container, qty) {
    const url = container.getAttribute("data-inventory-update-url");
    const variantId = Number(container.getAttribute("data-variant-id") || 0);

    if (!url) return false;

    const payload = {};
    if (variantId > 0) {
        payload.variants = {
            [variantId]: { qty },
        };
    } else {
        payload.qty = qty;
    }

    try {
        await axios.patch(url, payload, {
            headers: {
                "X-CSRF-TOKEN": getCsrf(),
            },
        });
        return true;
    } catch (e) {
        // eslint-disable-next-line no-console
        console.error("Low stock inventory update failed", e?.response?.status, e?.response?.data || e);
        return false;
    }
}

function initLowStockWidget() {
    const panel = document.querySelector(".dashboard-panel.dashboard-low-stock");
    if (!panel) return;

    panel.querySelectorAll("[data-ls-lightbox-trigger]").forEach((a) => {
        a.addEventListener("click", (e) => {
            e.preventDefault();
            const url = a.getAttribute("data-preview-url") || "";
            if (!url) return;
            showLightbox(panel, url);
        });
    });

    panel.querySelectorAll("[data-ls-lightbox-close]").forEach((el) => {
        el.addEventListener("click", (e) => {
            e.preventDefault();
            closeLightbox(panel);
        });
    });

    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") {
            closeLightbox(panel);
        }
    });

    panel.querySelectorAll("[data-ls-stock]").forEach((container) => {
        const openBtn = container.querySelector("[data-ls-edit-open]");
        const editor = container.querySelector("[data-ls-editor]");
        const input = container.querySelector("[data-ls-qty-input]");
        const saveBtn = container.querySelector("[data-ls-save]");
        const cancelBtn = container.querySelector("[data-ls-cancel]");
        const textEl = container.querySelector("[data-ls-qty-text]");
        const suffix = container.getAttribute("data-unit-suffix") || "";

        if (!openBtn || !editor || !input || !saveBtn || !cancelBtn) return;

        openBtn.addEventListener("click", (e) => {
            e.preventDefault();
            openEditor(container);
        });

        const doSave = async () => {
            const qty = Number(input.value);
            if (!Number.isFinite(qty) || qty < 0) return;

            saveBtn.disabled = true;
            cancelBtn.disabled = true;
            const ok = await updateInventory(container, qty);
            saveBtn.disabled = false;
            cancelBtn.disabled = false;

            if (ok) {
                if (textEl) textEl.textContent = formatQty(qty, suffix);
                saveBtn.classList.add("ls-saved");
                setTimeout(() => saveBtn.classList.remove("ls-saved"), 800);
                closeEditor(container);
            }
        };

        saveBtn.addEventListener("click", async (e) => {
            e.preventDefault();
            await doSave();
        });

        cancelBtn.addEventListener("click", (e) => {
            e.preventDefault();
            closeEditor(container);
        });

        input.addEventListener("keydown", async (e) => {
            if (e.key === "Enter") {
                e.preventDefault();
                await doSave();
            } else if (e.key === "Escape") {
                e.preventDefault();
                closeEditor(container);
            }
        });

        input.addEventListener("blur", () => {
            setTimeout(() => {
                if (!container.contains(document.activeElement)) {
                    closeEditor(container);
                }
            }, 0);
        });
    });
}

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", () => initLowStockWidget());
} else {
    initLowStockWidget();
}
