import ProductMixin from "../mixins/ProductMixin";
import "./ProductRating";

const LQIP_GIF =
    "data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7";

function ikasDebugEnabled() {
    try {
        return window.localStorage && window.localStorage.getItem("ikas_debug") === "1";
    } catch (e) {
        return false;
    }
}

function ikasDebugState(label) {
    if (!ikasDebugEnabled()) return;
    const shells = document.querySelectorAll(".product-image-shell").length;
    const injected = document.querySelectorAll(".product-image-shell img[data-injected='1']").length;
    const placeholders = document.querySelectorAll(".product-image-shell img.product-image-img.lqip").length;
    console.log("[IKAS_DEBUG]", label, {
        t: Math.round(performance.now()),
        shells,
        injected,
        placeholders,
    });
}

if (ikasDebugEnabled()) {
    try {
        const po = new PerformanceObserver((list) => {
            for (const e of list.getEntries()) {
                const el = e.element;
                console.log("[IKAS_DEBUG][LCP]", {
                    startTime_ms: Math.round(e.startTime),
                    size: e.size,
                    tag: el?.tagName,
                    cls: el?.className,
                    src: el?.tagName === "IMG" ? el.currentSrc || el.src : null,
                });
            }
        });
        po.observe({ type: "largest-contentful-paint", buffered: true });
    } catch (e) {}

    document.addEventListener(
        "DOMContentLoaded",
        () => {
            ikasDebugState("DOMContentLoaded");
        },
        { once: true }
    );
    window.addEventListener(
        "load",
        () => {
            ikasDebugState("load");
            setTimeout(() => ikasDebugState("load+500"), 500);
            setTimeout(() => ikasDebugState("load+1500"), 1500);
            setTimeout(() => ikasDebugState("load+2500"), 2500);
            setTimeout(() => ikasDebugState("load+4000"), 4000);
        },
        { once: true }
    );
}

function injectRealImage(container, src, alt) {
    if (!container || !src) return;

    const existing = container.querySelector("img[data-injected='1']");

    const applyTo = (img) => {
        img.decoding = "async";
        img.loading = "lazy";
        img.src = src;
        if (alt) img.alt = alt;
        img.setAttribute("data-injected", "1");
        img.style.position = "absolute";
        img.style.inset = "0";
        img.style.width = "100%";
        img.style.height = "100%";
        img.style.objectFit = "cover";
        img.style.objectPosition = "center";
        img.style.zIndex = "2";
        img.style.opacity = "0";
        img.style.transition = "opacity .25s ease";

        img.onload = () => {
            img.style.opacity = "1";
            const placeholder = container.querySelector("img.product-image-img.lqip");
            if (placeholder) placeholder.remove();
        };
    };

    if (existing) {
        // Update already injected image (variant/hover).
        existing.style.opacity = "0";
        applyTo(existing);
        return;
    }

    const img = document.createElement("img");
    applyTo(img);
    container.appendChild(img);
}

function startIkasImageLoad() {
    if (window.__fleetcartIkasImageLoadInitialized) return;
    window.__fleetcartIkasImageLoadInitialized = true;

    let canInject = false;

    const run = (root = document) => {
        if (!canInject) return;
        if (!root || !root.querySelectorAll) return;
        root.querySelectorAll(".product-image-shell[data-real-img]").forEach((el) => {
            if (!el || el.dataset.loaded) return;
            const src = el.dataset.realImg;
            if (!src) return;
            el.dataset.loaded = "1";
            injectRealImage(el, src, el.dataset.alt);
        });
    };

    const schedule = () => {
        ikasDebugState("schedule");
        setTimeout(() => {
            if ("requestIdleCallback" in window) {
                requestIdleCallback(
                    () => {
                        canInject = true;
                        ikasDebugState("canInject=true");
                        run(document);
                    },
                    { timeout: 3000 }
                );
            } else {
                setTimeout(() => {
                    canInject = true;
                    ikasDebugState("canInject=true");
                    run(document);
                }, 0);
            }
        }, 1500);
    };

    window.addEventListener("load", schedule, { once: true });

    if ("MutationObserver" in window) {
        const mo = new MutationObserver((mutations) => {
            for (const m of mutations) {
                for (const node of m.addedNodes) {
                    if (node && node.nodeType === 1) run(node);
                }
            }
        });
        mo.observe(document.documentElement, { subtree: true, childList: true });
    }
}

startIkasImageLoad();

function toLqipUrl(url) {
    if (!url || typeof url !== "string") return url;

    // fast dosyayı normal varyanta düşürmeye çalış
    const u = url.replace("-fast-400w", "-400w");

    const candidates = [
        u.replace(/-520w(\.\w+)$/, "-40w$1"),
        u.replace(/-400w(\.\w+)$/, "-40w$1"),
        u.replace(/-520w(\.\w+)$/, "-80w$1"),
        u.replace(/-400w(\.\w+)$/, "-80w$1"),
        u.replace(/(\.\w+)$/, "-80w$1"),
    ];

    return candidates[0];
}

function preloadImage(src) {
    return new Promise((resolve) => {
        if (!src) return resolve(false);
        const img = new Image();
        img.decoding = "async";
        img.onload = () => resolve(true);
        img.onerror = () => resolve(false);
        img.src = src;
    });
}

Alpine.data("ProductCard", (product, idx = 0) => ({
    ...ProductMixin(product),
    idx,
    inView: true,
    previewImagePath: null,
    selectedVariantUid: null,
    showAllVariants: false,

    init() {
        startIkasImageLoad();

        // When variant/hover changes, Alpine updates :data-src; trigger a re-scan so the
        // new real image can be swapped in after LCP without touching LCP timing.
        this.$watch("currentImage", () => {
            const shell = this.$refs?.pshell;
            if (!shell) return;

            // If image is already injected, update it; otherwise do nothing (ikas timing).
            if (shell.dataset?.loaded === "1") {
                queueMicrotask(() => {
                    injectRealImage(shell, shell.dataset.realImg, shell.dataset.alt);
                });
            }
        });
    },

    get currentImage() {
        const p = this.previewImagePath;
        const sel = Array.isArray(this.product?.variants)
            ? this.product.variants.find((v) => v.uid === this.selectedVariantUid)
            : null;
        const selPath = sel?.base_image?.path;
        const v = this.product?.variant?.base_image?.path;
        const b = this.product?.base_image?.path;
        if (p) return p;
        if (selPath) return selPath;
        if (v) return v;
        if (b) return b;
        return this.baseImage;
    },

    get productUrl() {
        let url = `/products/${this.product.slug}`;
        const uid = this.selectedVariantUid || (this.hasAnyVariant && this.item?.uid ? this.item.uid : null);
        if (uid) {
            url += `?variant=${uid}`;
        }
        return url;
    },

    get inWishlist() {
        return this.$store.wishlist.inWishlist(this.product.id);
    },

    get inCompareList() {
        return this.$store.compare.inCompareList(this.product.id);
    },

    get hasVisibleRating() {
        const reviewsCount = Number(
            this.product.reviews_count ?? (Array.isArray(this.product.reviews) ? this.product.reviews.length : 0)
        );
        return reviewsCount > 0;
    },

    previewVariant(variant) {
        this.previewImagePath = variant?.base_image?.path || null;
    },

    clearPreview() {
        this.previewImagePath = null;
    },

    selectVariant(variant) {
        this.selectedVariantUid = variant?.uid || null;
        this.previewVariant(variant);
    },

    urlForVariant(variant) {
        return `/products/${this.product.slug}?variant=${variant.uid}`;
    },

    toggleAllVariants() {
        this.showAllVariants = !this.showAllVariants;
    },

    closeAllVariants() {
        this.showAllVariants = false;
    },
    get currentSourceFile() {
        const p = this.previewImagePath;
        if (p && Array.isArray(this.product?.variants)) {
            const hovered = this.product.variants.find((v) => v?.base_image?.path === p);
            if (hovered?.base_image) return hovered.base_image;
        }
        const sel = Array.isArray(this.product?.variants)
            ? this.product.variants.find((v) => v.uid === this.selectedVariantUid)
            : null;
        if (sel?.base_image) return sel.base_image;
        if (this.product?.variant?.base_image) return this.product.variant.base_image;
        if (this.product?.base_image) return this.product.base_image;
        return null;
    },

    get imageSources() {
        const f = this.currentSourceFile;
        const avif = f?.fast_avif_url || f?.grid_avif_url || f?.detail_avif_url || null;
        const webp = f?.fast_webp_url || f?.grid_webp_url || f?.detail_webp_url || null;
        const jpeg = f?.grid_jpeg_url || f?.detail_jpeg_url || f?.path || this.baseImage;

        // Use webp (or avif) as fallback when available; browser may still pick sources,
        // but this prevents JS preload from pulling jpeg unnecessarily.
        const fallback = webp || avif || jpeg;
        return {
            avif,
            webp,
            fallback,
        };
    },

    get lqipSources() {
        const f = this.currentSourceFile;

        const avifBase = f?.fast_avif_url || f?.grid_avif_url || f?.detail_avif_url || null;
        const webpBase = f?.fast_webp_url || f?.grid_webp_url || f?.detail_webp_url || null;
        const jpegBase = f?.grid_jpeg_url || f?.detail_jpeg_url || f?.path || this.baseImage;

        const avif = f?.thumb_avif_url || (avifBase ? toLqipUrl(avifBase) : null);
        const webp = f?.thumb_webp_url || (webpBase ? toLqipUrl(webpBase) : null);
        const jpeg = f?.thumb_jpeg_url || (jpegBase ? toLqipUrl(jpegBase) : null);

        return {
            avif,
            webp,
            fallback: jpeg,
        };
    },

    get imageSrcsets() {
        const f = this.currentSourceFile;
        const makeSrcset = (thumbUrl, cardUrl, card2xUrl, card3xUrl, gridUrl) => {
            const entries = [];
            if (thumbUrl) entries.push(`${thumbUrl} 80w`);
            if (cardUrl && cardUrl !== thumbUrl) entries.push(`${cardUrl} 260w`);
            if (card2xUrl && card2xUrl !== cardUrl && card2xUrl !== thumbUrl)
                entries.push(`${card2xUrl} 520w`);
            if (card3xUrl && card3xUrl !== card2xUrl && card3xUrl !== cardUrl && card3xUrl !== thumbUrl)
                entries.push(`${card3xUrl} 780w`);
            if (gridUrl && gridUrl !== thumbUrl) entries.push(`${gridUrl} 400w`);
            return entries.join(", ");
        };

        const avif = makeSrcset(
            f?.thumb_avif_url,
            f?.card_avif_url,
            f?.card_2x_avif_url,
            f?.card_3x_avif_url,
            f?.fast_avif_url || f?.grid_avif_url
        );
        const webp = makeSrcset(
            f?.thumb_webp_url,
            f?.card_webp_url,
            f?.card_2x_webp_url,
            f?.card_3x_webp_url,
            f?.fast_webp_url || f?.grid_webp_url
        );
        const jpeg = makeSrcset(
            f?.thumb_jpeg_url,
            f?.card_jpeg_url,
            f?.card_2x_jpeg_url,
            f?.card_3x_jpeg_url,
            f?.grid_jpeg_url || f?.path || this.baseImage
        );

        return {
            avif,
            webp,
            jpeg,
        };
    },

    get lqipSrcsets() {
        const s = this.lqipSources;
        return {
            avif: s?.avif ? `${s.avif} 80w` : "",
            webp: s?.webp ? `${s.webp} 80w` : "",
            jpeg: s?.fallback ? `${s.fallback} 80w` : "",
        };
    },

    get imageSizes() {
        return "(max-width: 320px) 136px, (max-width: 450px) 200px, (max-width: 768px) 400px, 400px";
    },
}));
