import axios from "axios";

let topProductsLimit = 10;

function escapeHtml(s) {
    return String(s ?? "")
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#039;");
}

function getApi() {
    return window.EnhancedDashboardAnalytics;
}

function renderTopCategoriesTable(categories) {
    const body = document.querySelector("[data-top-categories-body]");
    if (!body) return;

    const moneyFormat = getApi()?.moneyFormat || ((v) => String(v ?? ""));
    const numberFormat = getApi()?.numberFormat || ((v) => String(v ?? ""));

    const rows = categories || [];
    if (!rows.length) {
        body.innerHTML = `<tr><td class="empty" colspan="3">${trans("admin::dashboard.no_data")}</td></tr>`;
        return;
    }

    body.innerHTML = rows
        .map((c) => {
            const name = c.name || `#${c.category_id}`;
            const qty = Number(c.orders_qty || 0);
            const revenue = Number(c.revenue || 0);
            return `
                <tr>
                    <td>
                        <div class="tp-row">
                            <div class="tp-thumb"><span class="tp-thumb-fallback"></span></div>
                            <div class="tp-meta">
                                <div class="tp-name">${escapeHtml(name)}</div>
                            </div>
                        </div>
                    </td>
                    <td class="text-right">${numberFormat(qty)}</td>
                    <td class="text-right">${moneyFormat(revenue)}</td>
                </tr>
            `;
        })
        .join("");
}

function renderTopBrandsTable(brands) {
    const body = document.querySelector("[data-top-brands-body]");
    if (!body) return;

    const moneyFormat = getApi()?.moneyFormat || ((v) => String(v ?? ""));
    const numberFormat = getApi()?.numberFormat || ((v) => String(v ?? ""));

    const rows = brands || [];
    if (!rows.length) {
        body.innerHTML = `<tr><td class="empty" colspan="3">${trans("admin::dashboard.no_data")}</td></tr>`;
        return;
    }

    body.innerHTML = rows
        .map((b) => {
            const name = b.name || `#${b.brand_id}`;
            const qty = Number(b.orders_qty || 0);
            const revenue = Number(b.revenue || 0);
            return `
                <tr>
                    <td>
                        <div class="tp-row">
                            <div class="tp-thumb"><span class="tp-thumb-fallback"></span></div>
                            <div class="tp-meta">
                                <div class="tp-name">${escapeHtml(name)}</div>
                            </div>
                        </div>
                    </td>
                    <td class="text-right">${numberFormat(qty)}</td>
                    <td class="text-right">${moneyFormat(revenue)}</td>
                </tr>
            `;
        })
        .join("");
}

function setTopEntitiesTab(key) {
    const tabsRoot = document.querySelector("[data-top-entities-tabs]");
    const limitRoot = document.querySelector("[data-top-products-limit]");
    const variantsRoot = document.querySelector("[data-top-products-variants]");
    const colTitle = document.querySelector("[data-top-col-title]");

    const productsBody = document.querySelector("[data-top-products-body]");
    const categoriesBody = document.querySelector("[data-top-categories-body]");
    const brandsBody = document.querySelector("[data-top-brands-body]");

    if (tabsRoot) {
        tabsRoot.querySelectorAll("[data-entity-tab]").forEach((t) => t.classList.remove("active"));
        const active = tabsRoot.querySelector(`[data-entity-tab="${key}"]`);
        if (active) active.classList.add("active");
    }

    // Use CSS display for instant switching - no re-rendering needed
    if (productsBody) {
        const tbody = productsBody.closest("tbody");
        if (tbody) {
            tbody.style.display = key === "products" ? "" : "none";
        }
    }
    if (categoriesBody) categoriesBody.style.display = key === "categories" ? "" : "none";
    if (brandsBody) brandsBody.style.display = key === "brands" ? "" : "none";

    if (limitRoot) limitRoot.style.display = key === "products" ? "" : "none";
    if (variantsRoot) variantsRoot.style.display = key === "products" ? "" : "none";

    if (colTitle) {
        colTitle.textContent = key === "products" ? "En Çok Satanlar" : key === "categories" ? "Kategori Bazlı Satış" : "Marka Bazlı Satış";
    }

    const entityHeader = document.querySelector("[data-top-entity-header]");
    if (entityHeader) {
        entityHeader.textContent = key === "products" ? "Ürün" : key === "categories" ? "Kategori" : "Marka";
    }
}

function initTopEntitiesTabs() {
    const tabsRoot = document.querySelector("[data-top-entities-tabs]");
    if (!tabsRoot) return;

    tabsRoot.querySelectorAll("[data-entity-tab]").forEach((tab) => {
        tab.addEventListener("click", (e) => {
            e.preventDefault();
            const key = tab.getAttribute("data-entity-tab");
            if (key) setTopEntitiesTab(key);
        });
    });
}

initTopProductsLightbox();

function resizeCharts() {
    try {
        getApi()?.resizeCharts?.();
    } catch (e) {
    }
}

function renderActiveTabFromCache(root, activeKey, data) {
    if (!root || !activeKey || !data) return;

    // Only render the chart that is actually visible to the user
    switch (activeKey) {
        case "trend":
            getApi()?.renderTrendChart?.(data.daily || []);
            break;
        case "customers":
            getApi()?.renderCustomersChart?.(data.daily || []);
            break;
        case "traffic":
            getApi()?.renderTrafficChart?.(data.traffic_breakdown || []);
            renderTrafficTable(data.traffic_breakdown || []);
            break;
        case "categories":
            getApi()?.renderCategoriesChart?.(data.top_categories || []);
            break;
        case "brands":
            getApi()?.renderBrandsChart?.(data.top_brands || []);
            break;
        case "order_status":
            getApi()?.renderOrderStatusChart?.(data.order_status || []);
            break;
        case "conversion":
            getApi()?.renderConversionChart?.(data.conversion || {});

            const visitsEl = root.querySelector("[data-conversion-visits]");
            const ordersEl = root.querySelector("[data-conversion-orders]");

            const numberFormat = getApi()?.numberFormat || ((v) => String(v ?? ""));
            if (visitsEl) visitsEl.textContent = numberFormat(data?.conversion?.visits);
            if (ordersEl) ordersEl.textContent = numberFormat(data?.conversion?.orders);
            break;
    }

    requestAnimationFrame(() => resizeCharts());
}

async function fetchDashboardAnalytics(range, extraParams = {}) {
    const url = FleetCart?.data?.dashboardAnalyticsUrl;

    if (!url) {
        return null;
    }

    try {
        const response = await axios.get(url, {
            params: { range, ...extraParams },
        });
        return response.data;
    } catch (e) {
        // eslint-disable-next-line no-console
        console.error(
            "Dashboard analytics fetch failed",
            e?.response?.status,
            e?.response?.data || e
        );
        return null;
    }
}

function renderKpis(totals) {
    const revenueEl = document.querySelector("[data-kpi-revenue]");
    const ordersEl = document.querySelector("[data-kpi-orders]");
    const aovEl = document.querySelector("[data-kpi-aov]");
    const repeatEl = document.querySelector("[data-kpi-repeat-rate]");

    const moneyFormat = getApi()?.moneyFormat || ((v) => String(v ?? ""));
    const numberFormat = getApi()?.numberFormat || ((v) => String(v ?? ""));

    if (revenueEl) revenueEl.textContent = moneyFormat(totals?.revenue);
    if (ordersEl) ordersEl.textContent = numberFormat(totals?.orders);
    if (aovEl) aovEl.textContent = moneyFormat(totals?.aov);

    if (repeatEl) {
        const rate = Number(totals?.repeat_rate || 0) * 100;
        repeatEl.textContent = `${rate.toFixed(0)}%`;
    }
}

function renderTopProductsTable(products) {
    const body = document.querySelector("[data-top-products-body]");
    if (!body) return;

    const moneyFormat = getApi()?.moneyFormat || ((v) => String(v ?? ""));
    const numberFormat = getApi()?.numberFormat || ((v) => String(v ?? ""));

    const placeholderUrl =
        body.getAttribute("data-image-placeholder-url") || "";

    const rows = products || [];

    if (!rows.length) {
        body.innerHTML = `<tr><td class="empty" colspan="3">${trans(
            "admin::dashboard.no_data"
        )}</td></tr>`;
        return;
    }

    const maxQty = Math.max(...rows.map((r) => Number(r.orders_qty || 0)), 0);

    const escapeHtml = (s) =>
        String(s ?? "")
            .replaceAll("&", "&amp;")
            .replaceAll("<", "&lt;")
            .replaceAll(">", "&gt;")
            .replaceAll('"', "&quot;")
            .replaceAll("'", "&#039;");

    body.innerHTML = rows
        .map((p) => {
            const name = p.name || `#${p.product_id}`;
            const variantText =
                typeof p.variant === "string" && p.variant.trim() !== ""
                    ? p.variant
                    : "";
            const qty = Number(p.orders_qty || 0);
            const qtyDisplay =
                typeof p.qty_display === "string" && p.qty_display.trim() !== ""
                    ? p.qty_display
                    : numberFormat(qty);
            const revenue = Number(p.revenue || 0);
            const pct = maxQty > 0 ? Math.round((qty / maxQty) * 100) : 0;
            const imageUrl = p.image_url || "";

            const resolvedImageUrl = imageUrl || placeholderUrl;
            const imgHtml = resolvedImageUrl
                ? `<img src="${escapeHtml(
                    resolvedImageUrl
                )}" alt="${escapeHtml(name)}" loading="lazy" width="40" height="40" style="object-fit: cover;" data-tp-lightbox data-preview-url="${escapeHtml(resolvedImageUrl)}" />`
                : `<span class="tp-thumb-fallback"></span>`;

            return `
                <tr>
                    <td>
                        <div class="tp-row">
                            <div class="tp-thumb">
                                ${imgHtml}
                            </div>
                            <div class="tp-meta">
                                <div class="tp-name">${escapeHtml(name)}</div>
                                ${variantText
                    ? `<div class="tp-variant">${escapeHtml(
                        variantText
                    )}</div>`
                    : ""
                }
                                <div class="tp-bar"><span style="width:${pct}%"></span></div>
                            </div>
                        </div>
                    </td>
                    <td class="text-right">${escapeHtml(qtyDisplay)}</td>
                    <td class="text-right">${moneyFormat(revenue)}</td>
                </tr>
            `;
        })
        .join("");
}

function ensureAdminLightbox() {
    let lb = document.getElementById("admin-image-lightbox");
    if (lb) return lb;

    lb = document.createElement("div");
    lb.id = "admin-image-lightbox";
    lb.className = "admin-image-lightbox";
    lb.innerHTML = `
        <div class="admin-image-lightbox__backdrop" data-admin-lightbox-close></div>
        <div class="admin-image-lightbox__dialog" role="dialog" aria-modal="true">
            <button type="button" class="admin-image-lightbox__close" data-admin-lightbox-close aria-label="Close">×</button>
            <img class="admin-image-lightbox__img" data-admin-lightbox-img alt="" />
        </div>
    `;

    document.body.appendChild(lb);

    lb.querySelectorAll("[data-admin-lightbox-close]").forEach((el) => {
        el.addEventListener("click", (e) => {
            e.preventDefault();
            lb.classList.remove("open");
            const img = lb.querySelector("[data-admin-lightbox-img]");
            if (img) img.setAttribute("src", "");
        });
    });

    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") {
            lb.classList.remove("open");
            const img = lb.querySelector("[data-admin-lightbox-img]");
            if (img) img.setAttribute("src", "");
        }
    });

    return lb;
}

function openAdminLightbox(url) {
    if (!url) return;
    const lb = ensureAdminLightbox();
    const img = lb.querySelector("[data-admin-lightbox-img]");
    if (img) img.setAttribute("src", url);
    lb.classList.add("open");
}

function initTopProductsLightbox() {
    document.addEventListener("click", (e) => {
        const target = e.target;
        if (!(target instanceof HTMLElement)) return;

        const img = target.closest("[data-tp-lightbox]");
        if (!img) return;

        const url = img.getAttribute("data-preview-url") || img.getAttribute("src") || "";
        if (!url) return;

        e.preventDefault();
        openAdminLightbox(url);
    });
}

function renderTrafficTable(breakdown) {
    const body = document.querySelector("[data-traffic-body]");
    if (!body) return;

    const moneyFormat = getApi()?.moneyFormat || ((v) => String(v ?? ""));
    const numberFormat = getApi()?.numberFormat || ((v) => String(v ?? ""));

    const rows = Array.isArray(breakdown) ? breakdown : [];
    if (!rows.length) {
        body.innerHTML = `<tr><td class="empty" colspan="3">${trans("admin::dashboard.no_data")}</td></tr>`;
        return;
    }

    const trafficSourceLabel = (key) => {
        const k = String(key || "");
        switch (k) {
            case "google_ads": return "Google Ads";
            case "google_organic": return "Google Organic";
            case "facebook_ads": return "Facebook Ads";
            case "instagram_ads": return "Instagram Ads";
            case "direct": return "Doğrudan (Direct)";
            case "etsy": return "Etsy Shop";
            default: return k.charAt(0).toUpperCase() + k.slice(1) || "Diğer";
        }
    };

    body.innerHTML = rows
        .map((r) => {
            return `
                <tr>
                    <td><strong>${trafficSourceLabel(r.source)}</strong></td>
                    <td class="text-right">${numberFormat(r.orders || 0)}</td>
                    <td class="text-right">${moneyFormat(r.revenue || 0)}</td>
                </tr>
            `;
        })
        .join("");
}

function initTrafficControls() {
    const root = document.querySelector("[data-dashboard-analytics]");
    if (!root) return;

    const controls = root.querySelector("[data-traffic-controls]");
    if (!controls) return;

    const btns = controls.querySelectorAll("[data-traffic-metric]");

    btns.forEach((b) => {
        b.addEventListener("click", (e) => {
            e.preventDefault();
            const metric = b.getAttribute("data-traffic-metric") || "orders";
            getApi()?.setTrafficMetric?.(metric);
            btns.forEach((x) => x.classList.remove("active"));
            b.classList.add("active");

            try {
                const data = window.__dashboardAnalyticsCache;
                if (data) {
                    getApi()?.renderTrafficChart?.(data.traffic_breakdown || []);
                    renderTrafficTable(data.traffic_breakdown || []);
                    setTimeout(resizeCharts, 50);
                }
            } catch (err) {
            }
        });
    });
}
function initTopProductsControls(onChange) {
    const limitRoot = document.querySelector("[data-top-products-limit]");
    const variantsRoot = document.querySelector("[data-top-products-variants]");

    let debounceTimer = null;
    const debouncedOnChange = () => {
        if (debounceTimer) clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            onChange?.();
        }, 300); // Wait 300ms after last click
    };

    if (limitRoot) {
        const limitBtns = limitRoot.querySelectorAll("[data-limit]");
        limitBtns.forEach((b) => {
            b.addEventListener("click", (e) => {
                e.preventDefault();
                e.stopPropagation();
                const raw = b.getAttribute("data-limit") || "10";
                const newLimit = Number(raw) || 10;
                if (newLimit === topProductsLimit) return; // No change, skip
                topProductsLimit = newLimit;
                limitBtns.forEach((x) => x.classList.remove("active"));
                b.classList.add("active");
                debouncedOnChange();
            });
        });
    }

    if (variantsRoot) {
        const variantBtns = variantsRoot.querySelectorAll("[data-variants]");
        variantBtns.forEach((b) => {
            b.addEventListener("click", (e) => {
                e.preventDefault();
                e.stopPropagation();
                const raw = b.getAttribute("data-variants") || "1";
                const group = raw === "1";
                const currentGroup = window.__topProductsGroupVariants !== false;
                if (group === currentGroup) return; // No change, skip
                variantBtns.forEach((x) => x.classList.remove("active"));
                b.classList.add("active");
                window.__topProductsGroupVariants = group;
                debouncedOnChange();
            });
        });
    }
}


async function initDashboardAnalyticsPanel() {
    const root = document.querySelector("[data-dashboard-analytics]");
    if (!root) return;

    const selector = root.querySelector("[data-dashboard-range-selector]");
    const tabs = root.querySelectorAll("[data-dashboard-analytics-tabs] .tab");
    const panels = root.querySelectorAll(".tab-panel");

    if (!selector) return;

    const ranges = selector.querySelectorAll("[data-range]");
    let currentRange = "30";
    let cache = null;
    let activeTabKey = "trend";

    const activateTab = (key) => {
        const activeTab = root.querySelector(`[data-tab="${key}"]`);
        const activePanel = root.querySelector(`[data-panel="${key}"]`);

        if (!activeTab || !activePanel) {
            return;
        }

        tabs.forEach((t) => t.classList.remove("active"));
        panels.forEach((p) => p.classList.remove("active"));

        activeTab.classList.add("active");
        activePanel.classList.add("active");

        activeTabKey = key;

        if (cache) {
            renderActiveTabFromCache(root, activeTabKey, cache);
        } else {
            requestAnimationFrame(() => resizeCharts());
        }
    };

    tabs.forEach((t) => {
        t.addEventListener("click", (e) => {
            e.preventDefault();
            const key = t.getAttribute("data-tab");
            if (!key) return;
            activateTab(key);
        });
    });

    const renderAll = (data) => {
        window.__dashboardAnalyticsCache = data;
        renderKpis(data.totals);

        // Render all entity tables so they're ready when tabs are switched
        renderTopProductsTable(data.top_products || []);
        renderTopCategoriesTable(data.top_categories || []);
        renderTopBrandsTable(data.top_brands || []);

        getApi()?.renderStockStatusChart?.(data.stock_status || null);

        // Crucial: Only render the ACTIVE tab's chart to save resources
        renderActiveTabFromCache(root, activeTabKey, data);
    };

    const load = async (range) => {
        const data = await fetchDashboardAnalytics(range, {
            top_products_limit: topProductsLimit,
            group_variants: window.__topProductsGroupVariants === false ? 0 : 1
        });
        if (!data) return;
        cache = data;
        renderAll(data);
    };

    ranges.forEach((r) => {
        r.addEventListener("click", async (e) => {
            e.preventDefault();
            const range = r.getAttribute("data-range") || "30";
            if (range === currentRange) return;
            currentRange = range;

            ranges.forEach((x) => x.classList.remove("active"));
            r.classList.add("active");

            await load(range);
        });
    });

    activateTab("trend");
    await load(currentRange);

    initTrafficControls();
    initTopEntitiesTabs();

    initTopProductsControls(async () => {
        await load(currentRange);
    });

    let resizeScheduled = false;
    window.addEventListener("resize", () => {
        if (resizeScheduled) return;
        resizeScheduled = true;
        requestAnimationFrame(() => {
            resizeScheduled = false;
            resizeCharts();
        });
    });
}

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", () => {
        initDashboardAnalyticsPanel();
    });
} else {
    initDashboardAnalyticsPanel();
}
