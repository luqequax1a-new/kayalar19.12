import ProductMixin from "../mixins/ProductMixin";
import "./ProductRating";
import Swiper from "swiper";
import { Navigation, Pagination } from "swiper/modules";

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
    } catch (e) { }

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

    // chunked run implementation
    const run = (root = document) => {
        if (!canInject) return;

        // If root allows querySelectorAll, find candidates
        const nodes = root.querySelectorAll?.(".product-image-shell[data-real-img]");
        if (!nodes || !nodes.length) return;

        let i = 0;
        const step = () => {
            // Process in chunks of 20
            const end = Math.min(i + 20, nodes.length);
            for (; i < end; i++) {
                const el = nodes[i];
                if (!el || el.dataset.loaded) continue;
                const src = el.dataset.realImg;
                if (!src) continue;
                el.dataset.loaded = "1";
                injectRealImage(el, src, el.dataset.alt);
            }
            if (i < nodes.length) {
                requestAnimationFrame(step);
            }
        };
        requestAnimationFrame(step);
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
            if (!canInject) return; // Wait until global enable

            for (const m of mutations) {
                for (const node of m.addedNodes) {
                    if (!node || node.nodeType !== 1) continue;

                    // Fast filter
                    if (node.matches?.(".product-image-shell[data-real-img]")) {
                        // Pass parent or document to safely scope (though 'run' finds children)
                        // Actually 'run' finds children in 'root'. If node itself is the shell,
                        // querySelectorAll works on it if it's an element, but search within itself
                        // might not find itself depending on browser. 
                        // Safest is to run on node if it has children, or check node itself.
                        // Our 'run' uses querySelectorAll. Let's make it work on itself too?
                        // Actually easier: if it matches, inject directly:
                        if (!node.dataset.loaded && node.dataset.realImg) {
                            node.dataset.loaded = "1";
                            injectRealImage(node, node.dataset.realImg, node.dataset.alt);
                        }
                    }
                    // Search children if it's a wrapper
                    if (node.querySelector?.(".product-image-shell[data-real-img]")) {
                        run(node);
                    }
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
    gallerySwiper: null,
    galleryInitTried: false,
    galleryInitRetries: 0,

    init() {
        startIkasImageLoad();

        // Initialize gallery lazily when visible
        const el = this.$el;
        if ("IntersectionObserver" in window) {
            const io = new IntersectionObserver((entries) => {
                if (entries.some(e => e.isIntersecting)) {
                    this.$nextTick(() => requestAnimationFrame(() => this.initGallery()));
                    io.disconnect();
                }
            }, { rootMargin: "200px" });
            io.observe(el);
        } else {
            this.$nextTick(() => requestAnimationFrame(() => this.initGallery()));
        }

        // --- Watchers ---

        // When variant/hover changes, Alpine updates :data-src; trigger a re-scan so the
        // new real image can be swapped in after LCP without touching LCP timing.
        this.$watch("currentImage", () => {
            const shell = this.$refs?.pshell;
            if (!shell) return;

            // Just mark/inject this specific shell
            this.$nextTick(() => {
                if (shell.dataset?.loaded === "1") {
                    queueMicrotask(() => {
                        injectRealImage(shell, shell.dataset.realImg, shell.dataset.alt);
                    });
                }
            });

            queueMicrotask(() => {
                if (this.gallerySwiper && typeof this.gallerySwiper.update === "function") {
                    this.gallerySwiper.update();
                }
            });
        });

        this.$watch("selectedVariantUid", () => {
            queueMicrotask(() => {
                if (!this.gallerySwiper) return;
                if (typeof this.gallerySwiper.slideTo === "function") {
                    this.gallerySwiper.slideTo(0, 0);
                }
                if (typeof this.gallerySwiper.update === "function") {
                    this.gallerySwiper.update();
                }
            });
        });

        this.$watch("previewImagePath", () => {
            queueMicrotask(() => {
                if (this.gallerySwiper && typeof this.gallerySwiper.update === "function") {
                    this.gallerySwiper.update();
                }
            });
        });
    },

    initGallery() {
        try {
            const items = this.galleryItems;
            if (!Array.isArray(items) || items.length <= 1) return;

            const container = this.$refs?.gallery;
            if (!container) return;

            if (container.classList.contains("swiper-initialized") || container.__swiperInstance) {
                this.gallerySwiper = container.__swiperInstance;
                return;
            }

            const slideCount = container.querySelectorAll('.swiper-slide:not(.swiper-slide-duplicate)').length;

            // If we expect to loop (more than 1 item) but DOM isn't ready, retry safely
            if (slideCount < 2) {
                if (this.galleryInitRetries < 5) {
                    this.galleryInitRetries++;
                    this.$nextTick(() => requestAnimationFrame(() => this.initGallery()));
                }
                return;
            }

            // Disable loop for 2 or fewer items to suppress warnings and ensure stability
            const shouldLoop = items.length > 2 && slideCount > 2;

            const nextEl = this.$refs?.galleryNext;
            const prevEl = this.$refs?.galleryPrev;
            const paginationEl = this.$refs?.galleryPagination;

            container.__swiperInstance = new Swiper(container, {
                modules: [Navigation, Pagination],
                slidesPerView: 1,
                spaceBetween: 0,
                allowTouchMove: true,

                // Enable observers to handle dynamic rendering/v-cloak/x-cloak
                observer: true,
                observeParents: true,
                watchSlidesProgress: true,
                checkOverflow: true,

                loop: shouldLoop,

                navigation: nextEl && prevEl ? { nextEl, prevEl } : undefined,
                pagination: paginationEl
                    ? {
                        el: paginationEl,
                        clickable: true,
                    }
                    : undefined,
            });

            this.gallerySwiper = container.__swiperInstance;
        } catch (e) {
            // ignore
        }
    },

    get galleryItems() {
        const out = [];

        const p = this.previewImagePath;

        const variants = Array.isArray(this.product?.variants) ? this.product.variants : [];
        const selected = this.selectedVariantUid
            ? variants.find((v) => v && v.uid === this.selectedVariantUid)
            : null;
        const hovered = p ? variants.find((v) => v?.base_image?.path === p) : null;

        // 1) Variant images first (hovered > selected > product.variant)
        const primaryVariant = hovered || selected || this.product?.variant || null;
        const vBase = primaryVariant?.base_image;
        if (vBase && vBase.path) out.push(vBase);
        const vMedia = Array.isArray(primaryVariant?.media) ? primaryVariant.media : [];
        for (const m of vMedia) {
            if (m && m.path) out.push(m);
        }

        // 2) Then product images
        const pBase = this.product?.base_image;
        if (pBase && pBase.path) out.push(pBase);
        const pMedia = Array.isArray(this.product?.media) ? this.product.media : [];
        for (const m of pMedia) {
            if (m && m.path) out.push(m);
        }

        const seen = new Set();
        const uniq = [];
        for (const f of out) {
            const p = String(f?.path || "");
            if (!p) continue;
            if (seen.has(p)) continue;
            seen.add(p);
            uniq.push(f);
        }

        return uniq.slice(0, 6);
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
        // İkas-style: Use backend URL or clean format
        let url = this.product.url || `/${this.product.slug}`;
        const uid = this.selectedVariantUid || (this.hasAnyVariant && this.item?.uid ? this.item.uid : null);
        if (uid) {
            const separator = url.includes('?') ? '&' : '?';
            url += `${separator}variant=${uid}`;
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
        const baseUrl = this.product.url || `/${this.product.slug}`;
        const separator = baseUrl.includes('?') ? '&' : '?';
        return `${baseUrl}${separator}variant=${variant.uid}`;
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

        // Prefer listing srcsets (optimized for category pages)
        if (f?.listing_avif_srcset || f?.listing_webp_srcset || f?.listing_jpeg_srcset) {
            return {
                avif: f?.listing_avif_srcset || '',
                webp: f?.listing_webp_srcset || '',
                jpeg: f?.listing_jpeg_srcset || '',
            };
        }

        // Fallback to building srcset from individual variants
        const makeSrcset = (thumbUrl, cardUrl, card2xUrl, gridUrl) => {
            const entries = [];
            if (thumbUrl) entries.push(`${thumbUrl} 80w`);
            if (cardUrl && cardUrl !== thumbUrl) entries.push(`${cardUrl} 260w`);
            if (card2xUrl && card2xUrl !== cardUrl && card2xUrl !== thumbUrl)
                entries.push(`${card2xUrl} 520w`);
            if (gridUrl && gridUrl !== thumbUrl) entries.push(`${gridUrl} 400w`);
            return entries.join(", ");
        };

        const avif = makeSrcset(
            f?.thumb_avif_url,
            f?.card_avif_url,
            f?.card_2x_avif_url,
            f?.fast_avif_url || f?.grid_avif_url
        );
        const webp = makeSrcset(
            f?.thumb_webp_url,
            f?.card_webp_url,
            f?.card_2x_webp_url,
            f?.fast_webp_url || f?.grid_webp_url
        );
        const jpeg = makeSrcset(
            f?.thumb_jpeg_url,
            f?.card_jpeg_url,
            f?.card_2x_jpeg_url,
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
        return this.imageSizesDetail;
    },

    get imageSizesDetail() {
        return "(max-width: 320px) 136px, (max-width: 450px) 200px, (max-width: 768px) 400px, 400px";
    },

    get imageSizesGrid() {
        return "(max-width: 576px) calc((100vw - 24px) / 2), (max-width: 992px) 33vw, 25vw";
    },
}));
