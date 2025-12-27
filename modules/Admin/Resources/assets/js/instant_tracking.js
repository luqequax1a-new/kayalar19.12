import axios from "axios";

function numberFormat(value) {
    try {
        return Number(value || 0).toLocaleString();
    } catch (e) {
        return String(value || 0);
    }
}

function setText(el, value) {
    if (!el) return;
    el.textContent = value;
}

function escapeHtml(s) {
    return String(s ?? "")
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#039;");
}

function renderInto(root, rangeKey, payload) {
    const carts = payload?.ranges?.[rangeKey] || null;
    const visits = payload?.visits?.[rangeKey] ?? null;

    setText(root.querySelector("[data-it-carts-count]"), numberFormat(carts?.count));
    setText(
        root.querySelector("[data-it-carts-amount]"),
        carts?.total_amount_formatted || "₺0,00"
    );

    setText(root.querySelector("[data-it-visits-count]"), numberFormat(visits));
}

function render(rangeKey, payload) {
    const roots = [];

    const panelRoot = document.querySelector("[data-instant-tracking]");
    if (panelRoot) roots.push(panelRoot);

    document.querySelectorAll("[data-instant-tracking-mini]").forEach((el) => roots.push(el));

    if (roots.length === 0) {
        // Fallback: update any standalone KPIs if present
        const carts = payload?.ranges?.[rangeKey] || null;
        const visits = payload?.visits?.[rangeKey] ?? null;

        setText(document.querySelector("[data-it-carts-count]"), numberFormat(carts?.count));
        setText(document.querySelector("[data-it-carts-amount]"), carts?.total_amount_formatted || "₺0,00");
        setText(document.querySelector("[data-it-visits-count]"), numberFormat(visits));
        return;
    }

    roots.forEach((r) => renderInto(r, rangeKey, payload));
}

async function fetchPayload() {
    const url = FleetCart?.data?.cartActivityUrl;
    if (!url) return null;

    try {
        const resp = await axios.get(url);
        return resp.data;
    } catch (e) {
        // eslint-disable-next-line no-console
        console.error("Instant tracking fetch failed", e?.response?.status, e?.response?.data || e);
        return null;
    }
}

function initInstantTracking() {
    const root = document.querySelector("[data-instant-tracking]");
    const miniRoots = document.querySelectorAll("[data-instant-tracking-mini]");
    if (!root && miniRoots.length === 0) return;

    const rangeRoot = root
        ? root.querySelector("[data-instant-tracking-range]")
        : document.querySelector("[data-instant-tracking-range]");
    const btns = rangeRoot ? rangeRoot.querySelectorAll("[data-range]") : [];

    let rangeKey = "last_30_min";
    let cache = null;
    let timer = null;

    const setActive = (key) => {
        rangeKey = key;
        btns.forEach((b) => b.classList.remove("active"));
        const active = rangeRoot?.querySelector(`[data-range="${key}"]`);
        if (active) active.classList.add("active");

        if (cache) {
            render(rangeKey, cache);
        }
    };

    btns.forEach((b) => {
        b.addEventListener("click", (e) => {
            e.preventDefault();
            const key = b.getAttribute("data-range") || "last_30_min";
            setActive(key);
        });
    });

    const poll = async () => {
        const data = await fetchPayload();
        if (data) {
            cache = data;
            render(rangeKey, cache);
            if (root) {
                root.classList.add("is-live");
                setTimeout(() => root.classList.remove("is-live"), 350);
            }
        }
    };

    poll();

    timer = window.setInterval(poll, 25000);

    window.addEventListener("beforeunload", () => {
        if (timer) window.clearInterval(timer);
    });

    setActive(rangeKey);
}

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", () => initInstantTracking());
} else {
    initInstantTracking();
}
