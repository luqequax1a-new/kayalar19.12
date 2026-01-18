import Chart from "chart.js/auto";

let trendChart = null;
let customersChart = null;
let trafficChart = null;
let categoriesChart = null;
let brandsChart = null;
let conversionChart = null;
let orderStatusChart = null;
let stockStatusChart = null;
let trafficMetric = "orders";

// Chart.js default configuration
Chart.defaults.font.family = '"Inter", "Helvetica Neue", Arial, sans-serif';
Chart.defaults.font.size = 11;
Chart.defaults.color = '#94a3b8';

function moneyFormat(value) {
    try {
        const symbol = FleetCart?.defaultCurrencySymbol || "";
        const num = Number(value || 0);
        return `${symbol}${num.toLocaleString(undefined, {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        })}`;
    } catch (e) {
        return String(value || 0);
    }
}

function fullMoneyFormat(value) {
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

function numberFormat(value) {
    try {
        return Number(value || 0).toLocaleString();
    } catch (e) {
        return String(value || 0);
    }
}

function formatDateShort(dateStr) {
    try {
        const date = new Date(dateStr);
        return date.toLocaleDateString('tr-TR', { day: 'numeric', month: 'short' });
    } catch (e) {
        return dateStr;
    }
}

function trafficSourceLabel(key) {
    const k = String(key || "");
    switch (k) {
        case "google_ads": return "Google Ads";
        case "google_organic": return "Google Organic";
        case "facebook_ads": return "Facebook Ads";
        case "instagram_ads": return "Instagram Ads";
        case "direct": return "Direct";
        case "etsy": return "Etsy";
        default: return "Other";
    }
}

function renderTrendChart(dailyData) {
    const el = document.querySelector("[data-chart-trend]");
    if (!el) return;

    if (trendChart) {
        trendChart.destroy();
        trendChart = null;
    }

    const labels = dailyData.map((d) => formatDateShort(d.date));
    const orders = dailyData.map((d) => Number(d.orders || 0));

    const revenuesLine = dailyData.map((d) => {
        const ord = Number(d.orders || 0);
        return ord > 0 ? Number(d.revenue || 0) : null;
    });

    const ctx = el.getContext('2d');
    const blueGradient = ctx.createLinearGradient(0, 0, 0, 400);
    blueGradient.addColorStop(0, "rgba(0, 104, 225, 0.85)");
    blueGradient.addColorStop(1, "rgba(0, 104, 225, 0.15)");

    trendChart = new Chart(el, {
        data: {
            labels,
            datasets: [
                {
                    type: "bar",
                    label: "Sipariş",
                    data: orders,
                    backgroundColor: blueGradient,
                    hoverBackgroundColor: "rgba(0, 104, 225, 1)",
                    borderRadius: 12,
                    yAxisID: "y",
                    order: 2,
                    barPercentage: 0.75,
                    categoryPercentage: 0.6,
                },
                {
                    type: "line",
                    label: "Ciro",
                    data: revenuesLine,
                    borderColor: "rgba(255, 49, 111, 1)",
                    borderWidth: 3,
                    pointRadius: 0,
                    pointHoverRadius: 6,
                    pointHoverBackgroundColor: "#fff",
                    pointHoverBorderWidth: 4,
                    tension: 0.35,
                    fill: false,
                    yAxisID: "y1",
                    order: 1,
                    spanGaps: false
                }
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: "index", intersect: false },
            layout: {
                padding: { top: 30, right: 30, left: 10, bottom: 0 }
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(30, 41, 59, 0.95)',
                    padding: 18,
                    cornerRadius: 16,
                    titleFont: { size: 14, weight: '800', family: 'Inter' },
                    bodyFont: { size: 13, weight: '500', family: 'Inter' },
                    bodySpacing: 10,
                    boxPadding: 6,
                    usePointStyle: true,
                    callbacks: {
                        label(ctx) {
                            const idx = ctx.dataIndex;
                            const d = dailyData[idx];
                            const ord = Number(d.orders || 0);
                            const rev = Number(d.revenue || 0);
                            const aov = ord > 0 ? (rev / ord) : 0;

                            if (ctx.datasetIndex === 0) {
                                let lines = [
                                    `📦 Sipariş: ${numberFormat(ord)} adet`,
                                    `💰 Ciro: ${fullMoneyFormat(rev)}`
                                ];
                                if (ord > 0) {
                                    lines.push(`🛒 Ort. Sepet (AOV): ${fullMoneyFormat(aov)}`);
                                }
                                return lines;
                            }
                            return null;
                        },
                    },
                    filter: (item) => item.datasetIndex === 0,
                },
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: {
                        font: { weight: '600' },
                        padding: 12,
                        maxTicksLimit: 12
                    }
                },
                y: {
                    type: "linear",
                    display: true,
                    position: "left",
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(241, 245, 249, 0.8)',
                        drawBorder: false
                    },
                    ticks: {
                        precision: 0,
                        stepSize: 1,
                        padding: 12
                    }
                },
                y1: {
                    type: "linear",
                    display: true,
                    position: "right",
                    beginAtZero: true,
                    grid: { display: false },
                    ticks: {
                        padding: 12,
                        callback: v => moneyFormat(v)
                    }
                }
            }
        }
    });
}

function renderTrafficChart(breakdown) {
    const el = document.querySelector("[data-chart-traffic]");
    if (!el) return;
    if (trafficChart) trafficChart.destroy();
    const rows = Array.isArray(breakdown) ? breakdown : [];
    const labels = rows.map((r) => trafficSourceLabel(r.source));
    const orders = rows.map((r) => Number(r.orders || 0));
    const revenue = rows.map((r) => Number(r.revenue || 0));
    const values = trafficMetric === "revenue" ? revenue : orders;
    const datasetColor = trafficMetric === "revenue" ? "rgba(255, 49, 111, 1)" : "rgba(0, 104, 225, 1)";
    const ctx = el.getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0, datasetColor);
    gradient.addColorStop(1, datasetColor.replace('1)', '0.4)'));

    trafficChart = new Chart(el, {
        type: "bar",
        data: {
            labels,
            datasets: [{
                data: values,
                borderRadius: 8,
                backgroundColor: gradient,
                barThickness: 28,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(30, 41, 59, 0.95)',
                    padding: 12,
                    cornerRadius: 12,
                    callbacks: {
                        label(ctx) {
                            const idx = ctx.dataIndex;
                            return [`📦 Sipariş: ${numberFormat(orders[idx])}`, `💰 Ciro: ${fullMoneyFormat(revenue[idx])}`];
                        }
                    }
                }
            },
            scales: {
                x: { grid: { display: false }, ticks: { font: { weight: '600' } } },
                y: { beginAtZero: true, grid: { color: 'rgba(241, 245, 249, 0.8)', drawBorder: false }, ticks: { callback: v => trafficMetric === 'revenue' ? moneyFormat(v) : v } }
            }
        }
    });
}

function renderCategoriesChart(categories) {
    const el = document.querySelector("[data-chart-categories]");
    if (!el) return;

    if (categoriesChart) {
        categoriesChart.destroy();
        categoriesChart = null;
    }

    const rows = (categories || []).sort((a, b) => Number(b.revenue) - Number(a.revenue)).slice(0, 10);
    const labels = rows.map((c) => c.name || "Diğer");
    const revenueValues = rows.map((c) => Number(c.revenue || 0));
    const orderValues = rows.map((c) => Number(c.orders_qty || 0));

    const ctx = el.getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, "rgba(139, 92, 246, 0.85)");
    gradient.addColorStop(1, "rgba(139, 92, 246, 0.15)");

    categoriesChart = new Chart(el, {
        type: "bar",
        data: {
            labels,
            datasets: [
                {
                    label: "Ciro",
                    data: revenueValues,
                    backgroundColor: gradient,
                    hoverBackgroundColor: "rgba(139, 92, 246, 1)",
                    borderRadius: 8,
                    barThickness: 30,
                }
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(30, 41, 59, 0.95)',
                    padding: 14,
                    cornerRadius: 12,
                    callbacks: {
                        label(ctx) {
                            const idx = ctx.dataIndex;
                            return [
                                `💰 Ciro: ${fullMoneyFormat(revenueValues[idx])}`,
                                `📦 Sipariş: ${numberFormat(orderValues[idx])} adet`
                            ];
                        }
                    }
                },
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { weight: '600' } }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(241, 245, 249, 0.8)', drawBorder: false },
                    ticks: { callback: v => moneyFormat(v) }
                }
            }
        }
    });
}

function renderBrandsChart(brands) {
    const el = document.querySelector("[data-chart-brands]");
    if (!el) return;

    if (brandsChart) {
        brandsChart.destroy();
        brandsChart = null;
    }

    const rows = (brands || []).sort((a, b) => Number(b.revenue) - Number(a.revenue)).slice(0, 10);
    const labels = rows.map((b) => b.name || "Diğer");
    const revenueValues = rows.map((b) => Number(b.revenue || 0));
    const orderValues = rows.map((b) => Number(b.orders_qty || 0));

    const ctx = el.getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, "rgba(34, 197, 94, 0.85)");
    gradient.addColorStop(1, "rgba(34, 197, 94, 0.15)");

    brandsChart = new Chart(el, {
        type: "bar",
        data: {
            labels,
            datasets: [
                {
                    label: "Ciro",
                    data: revenueValues,
                    backgroundColor: gradient,
                    hoverBackgroundColor: "rgba(34, 197, 94, 1)",
                    borderRadius: 8,
                    barThickness: 30,
                }
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(30, 41, 59, 0.95)',
                    padding: 14,
                    cornerRadius: 12,
                    callbacks: {
                        label(ctx) {
                            const idx = ctx.dataIndex;
                            return [
                                `💰 Ciro: ${fullMoneyFormat(revenueValues[idx])}`,
                                `📦 Sipariş: ${numberFormat(orderValues[idx])} adet`
                            ];
                        }
                    }
                },
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { weight: '600' } }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(241, 245, 249, 0.8)', drawBorder: false },
                    ticks: { callback: v => moneyFormat(v) }
                }
            }
        }
    });
}

function renderCustomersChart(daily) {
    const el = document.querySelector("[data-chart-customers]");
    if (!el) return;
    if (customersChart) customersChart.destroy();
    const labels = daily.map(d => formatDateShort(d.date));
    customersChart = new Chart(el, {
        type: "bar",
        data: {
            labels,
            datasets: [
                { label: "Yeni", data: daily.map(d => d.new_customers), backgroundColor: "rgba(34, 197, 94, 0.8)", borderRadius: 6 },
                { label: "Dönen", data: daily.map(d => d.returning_customers), backgroundColor: "rgba(139, 92, 246, 0.8)", borderRadius: 6 }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top', align: 'end', labels: { usePointStyle: true, pointStyle: 'circle' } },
                tooltip: { backgroundColor: 'rgba(30, 41, 59, 0.95)', padding: 12, cornerRadius: 12 }
            },
            scales: {
                x: { stacked: true, grid: { display: false } },
                y: { stacked: true, grid: { color: 'rgba(241, 245, 249, 0.8)', drawBorder: false } }
            }
        }
    });
}

function renderConversionChart(conversion) {
    const el = document.querySelector("[data-chart-conversion]");
    if (!el) return;
    if (conversionChart) conversionChart.destroy();
    conversionChart = new Chart(el, {
        type: "doughnut",
        data: {
            labels: ["Dönüşüm", "Diğer"],
            datasets: [{ data: [conversion.conversion_rate, 100 - conversion.conversion_rate], backgroundColor: ["#22c55e", "#f1f5f9"], borderWidth: 0 }]
        },
        options: { cutout: '85%', plugins: { legend: { display: false } } }
    });
}

function renderOrderStatusChart(data) {
    const el = document.querySelector("[data-chart-order-status]");
    if (!el || !data) return;
    if (orderStatusChart) orderStatusChart.destroy();
    orderStatusChart = new Chart(el, {
        type: "doughnut",
        data: {
            labels: data.map(i => i.label),
            datasets: [{ data: data.map(i => i.count), backgroundColor: ['#ff316f', '#0068e1', '#22c55e', '#f97316', '#8b5cf6', '#0ea5e9', '#ec4899'], borderWidth: 2, borderColor: '#fff' }]
        },
        options: { cutout: '70%', plugins: { legend: { position: 'right', labels: { usePointStyle: true, pointStyle: 'circle' } } } }
    });
}

function renderStockStatusChart(data) {
    const el = document.querySelector("[data-chart-stock-status]");
    if (!el || !data) return;
    if (stockStatusChart) stockStatusChart.destroy();
    stockStatusChart = new Chart(el, {
        type: "doughnut",
        data: {
            labels: ["Stokta", "Kritik", "Stok Yok"],
            datasets: [{ data: [data.in_stock, data.low_stock, data.out_of_stock], backgroundColor: ['#22c55e', '#f97316', '#ff316f'], borderWidth: 2, borderColor: '#fff' }]
        },
        options: { cutout: '70%', plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, pointStyle: 'circle' } } } }
    });
}

function resizeCharts() {
    [trendChart, customersChart, trafficChart, categoriesChart, brandsChart, conversionChart, orderStatusChart, stockStatusChart].forEach(c => c?.resize());
}

window.EnhancedDashboardAnalytics = {
    moneyFormat,
    numberFormat,
    renderTrafficChart,
    renderTrendChart,
    renderCustomersChart,
    renderCategoriesChart,
    renderBrandsChart,
    renderConversionChart,
    renderOrderStatusChart,
    renderStockStatusChart,
    resizeCharts,
    setTrafficMetric(metric) { trafficMetric = metric === "revenue" ? "revenue" : "orders"; },
    getTrafficMetric() { return trafficMetric; },
};
