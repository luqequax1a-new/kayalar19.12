import Chart from "chart.js/auto";

let trendChart = null;
let customersChart = null;
let topProductsChart = null;
let trafficChart = null;
let trafficMetric = "orders";
let topProductsLimit = 10;

function moneyFormat(value) {
    try {
        const symbol = FleetCart?.defaultCurrencySymbol || "";
        const num = Number(value || 0);

        return `${symbol}${num.toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        })}`;
    } catch (e) {
        return String(value || 0);
    }
}

function initTopProductsControls(onChange) {
    const root = document.querySelector("[data-top-products-limit]");
    if (!root) return;

    const btns = root.querySelectorAll("[data-limit]");

    btns.forEach((b) => {
        b.addEventListener("click", (e) => {
            e.preventDefault();

            const raw = b.getAttribute("data-limit") || "10";
            const n = Number(raw);
            const allowed = [5, 10, 15, 20];
            topProductsLimit = allowed.includes(n) ? n : 10;

            btns.forEach((x) => x.classList.remove("active"));
            b.classList.add("active");

            try {
                onChange?.(topProductsLimit);
            } catch (err) {
            }
        });
    });
}

function trafficSourceLabel(key) {
    const k = String(key || "");

    switch (k) {
        case "google_ads":
            return "Google Ads";
        case "google_organic":
            return "Google Organic";
        case "facebook_ads":
            return "Facebook Ads";
        case "instagram_ads":
            return "Instagram Ads";
        case "direct":
            return "Direct";
        case "etsy":
            return "Etsy";
        default:
            return "Other";
    }
}

function renderTrafficTable(breakdown) {
    const body = document.querySelector("[data-traffic-body]");
    if (!body) return;

    const rows = Array.isArray(breakdown) ? breakdown : [];

    const ordersUrlBase = FleetCart?.data?.adminOrdersUrl || "";

    const escapeHtml = (s) =>
        String(s ?? "")
            .replaceAll("&", "&amp;")
            .replaceAll("<", "&lt;")
            .replaceAll(">", "&gt;")
            .replaceAll('"', "&quot;")
            .replaceAll("'", "&#039;");

    if (!rows.length) {
        body.innerHTML = `<tr><td class="empty" colspan="3">${trans(
            "admin::dashboard.no_data"
        )}</td></tr>`;
        return;
    }

    body.innerHTML = rows
        .map((r) => {
            const label = trafficSourceLabel(r.source);
            const orders = Number(r.orders || 0);
            const revenue = Number(r.revenue || 0);

            const sourceKey = String(r.source || "other");
            const href = ordersUrlBase
                ? `${ordersUrlBase}?traffic_source=${encodeURIComponent(sourceKey)}`
                : "#";

            const labelHtml = ordersUrlBase
                ? `<a href="${escapeHtml(href)}">${escapeHtml(label)}</a>`
                : escapeHtml(label);

            return `<tr>
                <td>${labelHtml}</td>
                <td class="text-right">${escapeHtml(numberFormat(orders))}</td>
                <td class="text-right">${escapeHtml(moneyFormat(revenue))}</td>
            </tr>`;
        })
        .join("");
}

function renderTrafficChart(breakdown) {
    const el = document.querySelector("[data-chart-traffic]");
    if (!el) return;

    const rows = Array.isArray(breakdown) ? breakdown : [];
    const labels = rows.map((r) => trafficSourceLabel(r.source));

    const orders = rows.map((r) => Number(r.orders || 0));
    const revenue = rows.map((r) => Number(r.revenue || 0));

    const values = trafficMetric === "revenue" ? revenue : orders;
    const datasetLabel = trafficMetric === "revenue" ? "Ciro" : "Sipariş";
    const datasetColor =
        trafficMetric === "revenue"
            ? "rgba(0, 104, 225, .55)"
            : "rgba(14, 30, 62, .65)";

    if (trafficChart) trafficChart.destroy();

    trafficChart = new Chart(el, {
        type: "bar",
        data: {
            labels,
            datasets: [
                {
                    label: datasetLabel,
                    data: values,
                    borderRadius: 6,
                    backgroundColor: datasetColor,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label(ctx) {
                            const idx = ctx.dataIndex;
                            const o = orders[idx] ?? 0;
                            const r = revenue[idx] ?? 0;
                            return [
                                `Sipariş: ${numberFormat(o)}`,
                                `Ciro: ${moneyFormat(r)}`,
                            ];
                        },
                    },
                },
            },
            scales: {
                x: {
                    ticks: {
                        maxRotation: 45,
                        minRotation: 0,
                        callback(value) {
                            const label = this.getLabelForValue(value);
                            const s = String(label ?? "");
                            return s.length > 14 ? `${s.slice(0, 14)}…` : s;
                        },
                    },
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback(value) {
                            if (trafficMetric === "revenue") {
                                return moneyFormat(value);
                            }
                            return numberFormat(value);
                        },
                    },
                },
            },
        },
    });
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
            trafficMetric = metric === "revenue" ? "revenue" : "orders";
            btns.forEach((x) => x.classList.remove("active"));
            b.classList.add("active");

            try {
                const data = window.__dashboardAnalyticsCache;
                if (data) {
                    renderTrafficChart(data.traffic_breakdown || []);
                    setTimeout(resizeCharts, 50);
                }
            } catch (err) {
            }
        });
    });
}

async function fetchDashboardCartActivity() {
    const url = FleetCart?.data?.dashboardCartActivityUrl;

    if (!url) {
        return null;
    }

    try {
        const response = await axios.get(url);
        return response.data;
    } catch (e) {
        // eslint-disable-next-line no-console
        console.error("Dashboard cart activity fetch failed", e);
        return null;
    }
}

function numberFormat(value) {
    try {
        return Number(value || 0).toLocaleString();
    } catch (e) {
        return String(value || 0);
    }
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

    if (revenueEl) revenueEl.textContent = moneyFormat(totals?.revenue);
    if (ordersEl) ordersEl.textContent = numberFormat(totals?.orders);
    if (aovEl) aovEl.textContent = moneyFormat(totals?.aov);

    if (repeatEl) {
        const rate = Number(totals?.repeat_rate || 0) * 100;
        repeatEl.textContent = `${rate.toFixed(0)}%`;
    }
}

function renderTrendChart(daily) {
    const el = document.querySelector("[data-chart-trend]");
    if (!el) return;

    const labels = daily.map((d) => d.date);
    const orders = daily.map((d) => Number(d.orders || 0));

    if (trendChart) trendChart.destroy();

    trendChart = new Chart(el, {
        type: "line",
        data: {
            labels,
            datasets: [
                {
                    label: "Sipariş",
                    data: orders,
                    borderColor: "rgba(255, 49, 111, 0.9)",
                    backgroundColor: "rgba(255, 49, 111, 0.10)",
                    yAxisID: "yOrders",
                    tension: 0.35,
                    fill: false,
                    pointRadius: 0,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: "index", intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    displayColors: false,
                    callbacks: {
                        label(ctx) {
                            return `Sipariş: ${numberFormat(ctx.parsed.y)}`;
                        },
                    },
                },
            },
            scales: {
                yOrders: {
                    type: "linear",
                    position: "right",
                    beginAtZero: true,
                    grid: { drawOnChartArea: false },
                    ticks: {
                        precision: 0,
                    },
                },
            },
        },
    });
}

function renderCustomersChart(daily) {
    const el = document.querySelector("[data-chart-customers]");
    if (!el) return;

    const labels = daily.map((d) => d.date);
    const newCustomers = daily.map((d) => Number(d.new_customers || 0));
    const returning = daily.map((d) => Number(d.returning_customers || 0));

    if (customersChart) customersChart.destroy();

    customersChart = new Chart(el, {
        type: "bar",
        data: {
            labels,
            datasets: [
                {
                    label: "Yeni",
                    data: newCustomers,
                    borderRadius: 6,
                    backgroundColor: "rgba(136, 194, 115, .7)",
                },
                {
                    label: "Dönen",
                    data: returning,
                    borderRadius: 6,
                    backgroundColor: "rgba(139, 93, 255, .7)",
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: true },
                tooltip: { displayColors: false },
            },
            scales: {
                y: { beginAtZero: true },
            },
        },
    });
}

function renderTopProductsTable(products) {
    const body = document.querySelector("[data-top-products-body]");
    if (!body) return;

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
                  )}" alt="" loading="lazy" onerror="this.onerror=null;${
                      placeholderUrl
                          ? `this.src='${escapeHtml(placeholderUrl)}';`
                          : "this.remove();"
                  }" />`
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
                                ${
                                    variantText
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

function resizeCharts() {
    try {
        trendChart?.resize();
        customersChart?.resize();
        topProductsChart?.resize();
        trafficChart?.resize();
    } catch (e) {
    }
}

async function initCartActivityPanel() {
    const root = document.querySelector("[data-dashboard-cart-activity]");
    if (!root) return;

    const last30CountEl = root.querySelector(
        "[data-cart-activity-last-30-count]"
    );
    const last30AmountEl = root.querySelector(
        "[data-cart-activity-last-30-amount]"
    );
    const last60CountEl = root.querySelector(
        "[data-cart-activity-last-60-count]"
    );
    const last60AmountEl = root.querySelector(
        "[data-cart-activity-last-60-amount]"
    );
    const todayCountEl = root.querySelector(
        "[data-cart-activity-today-count]"
    );
    const todayAmountEl = root.querySelector(
        "[data-cart-activity-today-amount]"
    );
    const list = root.querySelector("[data-cart-activity-list]");
    const empty = root.querySelector("[data-cart-activity-empty]");

    const data = await fetchDashboardCartActivity();

    if (!data) {
        return;
    }

    const ranges = data.ranges || {};
    const last30 = ranges.last_30_min || {};
    const last60 = ranges.last_60_min || {};
    const today = ranges.today || {};

    if (last30CountEl) last30CountEl.textContent = numberFormat(last30.count);
    if (last30AmountEl) last30AmountEl.textContent =
        last30.total_amount_formatted || moneyFormat(last30.total_amount);

    if (last60CountEl) last60CountEl.textContent = numberFormat(last60.count);
    if (last60AmountEl) last60AmountEl.textContent =
        last60.total_amount_formatted || moneyFormat(last60.total_amount);

    if (todayCountEl) todayCountEl.textContent = numberFormat(today.count);
    if (todayAmountEl) todayAmountEl.textContent =
        today.total_amount_formatted || moneyFormat(today.total_amount);

    const rows = Array.isArray(data.recent_carts) ? data.recent_carts : [];

    if (!list) {
        return;
    }

    if (!rows.length) {
        if (empty) empty.style.display = "block";
        list.querySelectorAll(".cart-activity-item").forEach((n) => n.remove());
        return;
    }

    if (empty) empty.style.display = "none";

    const escapeHtml = (s) =>
        String(s ?? "")
            .replaceAll("&", "&amp;")
            .replaceAll("<", "&lt;")
            .replaceAll(">", "&gt;")
            .replaceAll('"', "&quot;")
            .replaceAll("'", "&#039;");

    list.querySelectorAll(".cart-activity-item").forEach((n) => n.remove());

    const html = rows
        .map((c) => {
            const itemsQty = Number(c.items_qty || 0);
            const total =
                c.total_amount_formatted || moneyFormat(Number(c.total_amount));

            const items = Array.isArray(c.items) ? c.items : [];
            const previewText = items.length
                ? items
                      .map((it) => {
                          const name = it.name ? String(it.name) : "";
                          const qty = it.qty == null ? 1 : Number(it.qty || 0);
                          return `${name} x${qty}`;
                      })
                      .join(", ")
                : "";

            const previewHtml = previewText
                ? `<div class="cart-items-preview">${escapeHtml(
                      previewText
                  )}</div>`
                : "";

            return `<div class="cart-activity-item">
                <div class="cart-activity-item-head">
                    <div class="cart-activity-item-metrics">${numberFormat(
                        itemsQty
                    )} ürün</div>
                    <div class="cart-activity-item-total">${escapeHtml(
                        total
                    )}</div>
                </div>
                ${previewHtml}
            </div>`;
        })
        .join("");

    list.insertAdjacentHTML("beforeend", html);
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

        setTimeout(resizeCharts, 50);
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
        renderTrendChart(data.daily || []);
        renderCustomersChart(data.daily || []);
        renderTrafficChart(data.traffic_breakdown || []);
        renderTrafficTable(data.traffic_breakdown || []);
        renderTopProductsTable(data.top_products || []);
        setTimeout(resizeCharts, 50);
    };

    const load = async (range) => {
        const data = await fetchDashboardAnalytics(range, {
            top_products_limit: topProductsLimit,
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

            if (cache) {
                renderAll(cache);
            }
        });
    });

    activateTab("trend");
    await load(currentRange);

    initTrafficControls();

    initTopProductsControls(async () => {
        await load(currentRange);
        if (cache) {
            renderAll(cache);
        }
    });

    window.addEventListener("resize", () => {
        resizeCharts();
    });
}

async function fetchSalesAnalyticsData() {
    const el = document.querySelector(".sales-analytics .chart");
    if (!el) {
        return;
    }

    const response = await axios.get("/sales-analytics");

    let data = {
        labels: response.data.labels,
        sales: [],
        formatted: [],
        totalOrders: [],
    };

    for (let item of response.data.data) {
        data.sales.push(item.total.amount);
        data.formatted.push(item.total.formatted);
        data.totalOrders.push(item.total_orders);
    }

    initSalesAnalyticsChart(data);
}

fetchSalesAnalyticsData();

initDashboardAnalyticsPanel();

function initSalesAnalyticsChart(data) {
    const el = document.querySelector(".sales-analytics .chart");
    if (!el) {
        return;
    }

    new Chart(el, {
        type: "bar",
        data: {
            labels: data.labels,
            datasets: [
                {
                    data: data.sales,
                    borderRadius: 6,
                    backgroundColor: [
                        "rgba(76, 201, 254, .7)",
                        "rgba(71, 90, 255, .7)",
                        "rgba(255, 119, 183, .7)",
                        "rgba(250, 64, 50, .7)",
                        "rgba(136, 194, 115, .7)",
                        "rgba(139, 93, 255, .7)",
                        "rgba(255, 127, 62, .7)",
                    ],
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: false,
                tooltip: {
                    displayColors: false,
                    callbacks: {
                        label(item) {
                            let orders = `${trans(
                                "admin::dashboard.sales_analytics.orders"
                            )}: ${data.totalOrders[item.dataIndex]}`;

                            let sales = `${trans(
                                "admin::dashboard.sales_analytics.sales"
                            )}: ${data.formatted[item.dataIndex]}`;

                            return [orders, sales];
                        },
                    },
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        // Include the currency symbol in the ticks
                        callback: function (value) {
                            return data.formatted[0].charAt(0) + value;
                        },
                    },
                },
            },
        },
    });
}
