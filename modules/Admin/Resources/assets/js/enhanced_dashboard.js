import Chart from "chart.js/auto";

let trendChart = null;
let customersChart = null;
let trafficChart = null;
let hourlyChart = null;
let conversionChart = null;
let orderStatusChart = null;
let stockStatusChart = null;
let trafficMetric = "orders";
let topProductsLimit = 10;

// Chart.js default configuration
Chart.defaults.font.family = '"Inter", "Helvetica Neue", Arial, sans-serif';
Chart.defaults.font.size = 12;
Chart.defaults.color = '#64748b';

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
            ? "rgba(0, 104, 225, .75)"
            : "rgba(14, 30, 62, .75)";

    if (trafficChart) trafficChart.destroy();

    trafficChart = new Chart(el, {
        type: "bar",
        data: {
            labels,
            datasets: [
                {
                    label: datasetLabel,
                    data: values,
                    borderRadius: 8,
                    borderSkipped: false,
                    backgroundColor: datasetColor,
                    borderColor: datasetColor.replace('.75', '1'),
                    borderWidth: 1,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(14, 30, 62, 0.9)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    borderColor: '#e2e8f0',
                    borderWidth: 1,
                    cornerRadius: 8,
                    displayColors: false,
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
                    grid: {
                        display: false,
                    },
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
                    grid: {
                        color: 'rgba(226, 232, 240, 0.5)',
                    },
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

function numberFormat(value) {
    try {
        return Number(value || 0).toLocaleString();
    } catch (e) {
        return String(value || 0);
    }
}

function renderTrendChart(daily) {
    const el = document.querySelector("[data-chart-trend]");
    if (!el) return;

    const labels = daily.map((d) => d.date);
    const orders = daily.map((d) => Number(d.orders || 0));
    const revenue = daily.map((d) => Number(d.revenue || 0));

    if (trendChart) trendChart.destroy();

    trendChart = new Chart(el, {
        type: "line",
        data: {
            labels,
            datasets: [
                {
                    label: "Sipariş",
                    data: orders,
                    borderColor: "rgba(255, 49, 111, 1)",
                    backgroundColor: "rgba(255, 49, 111, 0.1)",
                    yAxisID: "yOrders",
                    tension: 0.4,
                    fill: true,
                    pointRadius: 4,
                    pointBackgroundColor: "#fff",
                    pointBorderWidth: 2,
                    pointHoverRadius: 6,
                },
                {
                    label: "Ciro",
                    data: revenue,
                    borderColor: "rgba(0, 104, 225, 1)",
                    backgroundColor: "rgba(0, 104, 225, 0.1)",
                    yAxisID: "yRevenue",
                    tension: 0.4,
                    fill: true,
                    pointRadius: 4,
                    pointBackgroundColor: "#fff",
                    pointBorderWidth: 2,
                    pointHoverRadius: 6,
                }
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: "index", intersect: false },
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        boxWidth: 12,
                        padding: 15,
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(14, 30, 62, 0.9)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    borderColor: '#e2e8f0',
                    borderWidth: 1,
                    cornerRadius: 8,
                    displayColors: false,
                    callbacks: {
                        label(ctx) {
                            if (ctx.datasetIndex === 0) {
                                return `Sipariş: ${numberFormat(ctx.parsed.y)}`;
                            } else {
                                return `Ciro: ${moneyFormat(ctx.parsed.y)}`;
                            }
                        },
                    },
                },
            },
            scales: {
                yOrders: {
                    type: "linear",
                    position: "left",
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(226, 232, 240, 0.5)',
                        drawOnChartArea: true
                    },
                    ticks: {
                        precision: 0,
                        callback(value) {
                            return numberFormat(value);
                        }
                    },
                },
                yRevenue: {
                    type: "linear",
                    position: "right",
                    beginAtZero: true,
                    grid: { drawOnChartArea: false },
                    ticks: {
                        callback(value) {
                            return moneyFormat(value);
                        }
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
                    borderRadius: 8,
                    borderSkipped: false,
                    backgroundColor: "rgba(136, 194, 115, .85)",
                    borderColor: "rgba(136, 194, 115, 1)",
                    borderWidth: 1,
                },
                {
                    label: "Dönen",
                    data: returning,
                    borderRadius: 8,
                    borderSkipped: false,
                    backgroundColor: "rgba(139, 93, 255, .85)",
                    borderColor: "rgba(139, 93, 255, 1)",
                    borderWidth: 1,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        boxWidth: 12,
                        padding: 15,
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(14, 30, 62, 0.9)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    borderColor: '#e2e8f0',
                    borderWidth: 1,
                    cornerRadius: 8,
                    displayColors: false
                },
            },
            scales: {
                x: {
                    grid: {
                        display: false,
                    },
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(226, 232, 240, 0.5)',
                    },
                },
            },
        },
    });
}

function renderHourlyChart(hourly) {
    const el = document.querySelector("[data-chart-hourly]");
    if (!el) return;

    const hours = Array.from({ length: 24 }, (_, i) => `${i}:00`);
    const data = Array(24).fill(0);

    hourly.forEach(item => {
        data[item.hour] = item.orders;
    });

    if (hourlyChart) hourlyChart.destroy();

    hourlyChart = new Chart(el, {
        type: "bar",
        data: {
            labels: hours,
            datasets: [
                {
                    label: "Sipariş",
                    data: data,
                    borderRadius: 8,
                    borderSkipped: false,
                    backgroundColor: (context) => {
                        const index = context.dataIndex;
                        const currentHour = new Date().getHours();
                        return index === currentHour ?
                            "rgba(255, 159, 64, .85)" :
                            "rgba(76, 201, 254, .85)";
                    },
                    borderColor: (context) => {
                        const index = context.dataIndex;
                        const currentHour = new Date().getHours();
                        return index === currentHour ?
                            "rgba(255, 159, 64, 1)" :
                            "rgba(76, 201, 254, 1)";
                    },
                    borderWidth: 1,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(14, 30, 62, 0.9)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    borderColor: '#e2e8f0',
                    borderWidth: 1,
                    cornerRadius: 8,
                    displayColors: false,
                    callbacks: {
                        label(ctx) {
                            return `Sipariş: ${numberFormat(ctx.parsed.y)}`;
                        },
                    },
                },
            },
            scales: {
                x: {
                    grid: {
                        display: false,
                    },
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(226, 232, 240, 0.5)',
                    },
                    ticks: {
                        precision: 0,
                    }
                },
            },
        },
    });
}

function renderConversionChart(conversion) {
    const el = document.querySelector("[data-chart-conversion]");
    if (!el) return;

    if (conversionChart) conversionChart.destroy();

    conversionChart = new Chart(el, {
        type: "doughnut",
        data: {
            labels: ["Dönüşüm Oranı", "Diğer"],
            datasets: [
                {
                    data: [conversion.conversion_rate, 100 - conversion.conversion_rate],
                    backgroundColor: [
                        "rgba(136, 194, 115, .85)",
                        "rgba(226, 232, 240, .5)"
                    ],
                    borderColor: [
                        "rgba(136, 194, 115, 1)",
                        "rgba(226, 232, 240, 1)"
                    ],
                    borderWidth: 1,
                    cutout: '75%',
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { enabled: false },
                centerText: {
                    display: true,
                    text: `${conversion.conversion_rate}%`
                }
            },
        },
        plugins: [{
            id: 'centerText',
            beforeDraw: function (chart) {
                if (chart.config.options.plugins.centerText.display !== true)
                    return;

                const ctx = chart.ctx;
                const canvas = chart.canvas;
                const centerX = (chart.chartArea.left + chart.chartArea.right) / 2;
                const centerY = (chart.chartArea.top + chart.chartArea.bottom) / 2;
                const fontSize = (chart.height / 100).toFixed(2);

                ctx.font = `${fontSize}em sans-serif`;
                ctx.textBaseline = "middle";
                ctx.fillStyle = "#0e1e3e";
                ctx.textAlign = 'center';

                const text = chart.config.options.plugins.centerText.text;
                ctx.fillText(text, centerX, centerY);
            }
        }]
    });
}

function renderOrderStatusChart(data) {
    const el = document.querySelector("[data-chart-order-status]");
    if (!el || !data) return;

    const labels = data.map(i => i.label);
    const counts = data.map(i => i.count);

    if (orderStatusChart) orderStatusChart.destroy();

    orderStatusChart = new Chart(el, {
        type: "doughnut",
        data: {
            labels,
            datasets: [{
                data: counts,
                backgroundColor: [
                    'rgba(255, 49, 111, 0.8)',
                    'rgba(0, 104, 225, 0.8)',
                    'rgba(92, 200, 88, 0.8)',
                    'rgba(250, 109, 66, 0.8)',
                    'rgba(255, 159, 64, 0.8)',
                    'rgba(153, 102, 255, 0.8)',
                    'rgba(76, 201, 254, 0.8)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: { boxWidth: 12 }
                }
            }
        }
    });
}

function renderStockStatusChart(data) {
    const el = document.querySelector("[data-chart-stock-status]");
    if (!el || !data) return;

    if (stockStatusChart) stockStatusChart.destroy();

    stockStatusChart = new Chart(el, {
        type: "pie",
        data: {
            labels: ["Stokta", "Kritik Stok", "Stok Yok"],
            datasets: [{
                data: [data.in_stock, data.low_stock, data.out_of_stock],
                backgroundColor: [
                    'rgba(92, 200, 88, 0.8)',
                    'rgba(255, 159, 64, 0.8)',
                    'rgba(255, 49, 111, 0.8)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { boxWidth: 12 }
                }
            }
        }
    });
}

function resizeCharts() {
    try {
        trendChart?.resize();
        customersChart?.resize();
        trafficChart?.resize();
        hourlyChart?.resize();
        conversionChart?.resize();
        orderStatusChart?.resize();
        stockStatusChart?.resize();
    } catch (e) {
    }
}

window.EnhancedDashboardAnalytics = {
    moneyFormat,
    numberFormat,
    renderTrafficChart,
    renderTrendChart,
    renderCustomersChart,
    renderHourlyChart,
    renderConversionChart,
    renderOrderStatusChart,
    renderStockStatusChart,
    resizeCharts,
    setTrafficMetric(metric) {
        trafficMetric = metric === "revenue" ? "revenue" : "orders";
    },
    getTrafficMetric() {
        return trafficMetric;
    },
};
