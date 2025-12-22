import { Manipulation, Pagination, Navigation } from "swiper/modules";
import md5 from "blueimp-md5";
import Swiper from "swiper";
import Drift from "drift-zoom";
import GLightbox from "glightbox";
import Errors from "../../../components/Errors";
import "../../../components/ProductRating";
import "../../../components/Pagination";
import "../../../components/ProductCard";

function initSizeChartModal() {
    const trigger = document.querySelector('.size-chart-trigger');
    if (!trigger) return;

    const modalEl = document.getElementById('sizeChartModal');
    if (!modalEl || typeof bootstrap === 'undefined') return;

    const tabsEl = modalEl.querySelector('[data-size-chart-tabs]');
    const bodyEl = modalEl.querySelector('[data-size-chart-modal-body]');

    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);

    try {
        modalEl.inert = true;
        modalEl.removeAttribute('aria-hidden');
    } catch (_) {}

    let lastTrigger = trigger;

    // Track last trigger (in case of multiple triggers on the page)
    document.addEventListener('click', (e) => {
        try {
            const t = e.target?.closest?.('.size-chart-trigger');
            if (t) lastTrigger = t;
        } catch (_) {}
    });

    const restoreFocusToTrigger = () => {
        try {
            const active = document.activeElement;
            const focusTarget = lastTrigger || trigger;

            const ariaHidden = modalEl.getAttribute('aria-hidden');
            const shouldRestore = (active && modalEl.contains(active)) || ariaHidden === 'true' || modalEl.inert === true;

            // If focus is inside the modal, move it back to the trigger.
            if (shouldRestore) {
                try {
                    if (typeof active.blur === 'function') active.blur();
                } catch (_) {}

                if (typeof focusTarget?.focus === 'function') {
                    try {
                        focusTarget.focus({ preventScroll: true });
                    } catch (_) {
                        focusTarget.focus();
                    }
                }
            }
        } catch (_) {}
    };

    const forceHideSafely = () => {
        // During hide, Bootstrap may set aria-hidden while the modal is still visible.
        // Ensure focus cannot remain inside the modal at that moment.
        try { modalEl.inert = true; } catch (_) {}
        restoreFocusToTrigger();
    };

    // Use capture so this runs as early as possible during the hide sequence.
    modalEl.addEventListener('hide.bs.modal', forceHideSafely, true);
    // Some browsers warn if aria-hidden is left on a visible modal.
    modalEl.addEventListener('show.bs.modal', () => {
        try {
            modalEl.inert = false;
            modalEl.removeAttribute('aria-hidden');
        } catch (_) {}
    });
    modalEl.addEventListener('shown.bs.modal', () => {
        try {
            modalEl.inert = false;
            modalEl.removeAttribute('aria-hidden');
        } catch (_) {}
    });
    modalEl.addEventListener('hidden.bs.modal', () => {
        // Ensure focus is not left on an element inside an aria-hidden modal.
        try { modalEl.inert = true; } catch (_) {}
        setTimeout(restoreFocusToTrigger, 0);
    });

    const setLoading = () => {
        if (bodyEl) {
            bodyEl.innerHTML = `<div class="py-4 text-center"><div class="spinner-border" role="status" aria-label="loading"></div></div>`;
        }
    };

    const setError = () => {
        if (bodyEl) {
            bodyEl.innerHTML = `<div class="alert alert-danger mb-0">${(FleetCart?.langs?.['storefront::storefront.something_went_wrong'] || 'Something went wrong')}</div>`;
        }
    };

    const openImageLightbox = (src) => {
        try {
            if (!src || typeof GLightbox === 'undefined') return false;

            const lightbox = GLightbox({
                elements: [
                    {
                        href: src,
                        type: 'image',
                        title: '',
                    },
                ],
            });

            lightbox.open();
            return true;
        } catch (_) {
            return false;
        }
    };

    const setTabsVisible = (visible) => {
        try {
            if (!tabsEl) return;
            tabsEl.style.display = visible ? '' : 'none';
        } catch (_) {}
    };

    const clearTabs = () => {
        try {
            if (!tabsEl) return;
            tabsEl.innerHTML = '';
        } catch (_) {}
    };

    const renderTabs = (charts, activeId) => {
        try {
            if (!tabsEl) return;

            const safeTitle = (t) => {
                const v = (t || '').toString();
                return v.length ? v : (FleetCart?.langs?.['size_chart::storefront.size_chart'] || 'Size Chart');
            };

            tabsEl.innerHTML = charts
                .map((c) => {
                    const isActive = String(c.id) === String(activeId);
                    const typeLabel = c.type === 'image' ? 'IMG' : 'HTML';
                    return `
                        <button
                            type="button"
                            class="size-chart-tab${isActive ? ' is-active' : ''}"
                            data-size-chart-tab
                            data-chart-id="${String(c.id)}"
                        >
                            <span class="size-chart-tab-title">${safeTitle(c.title)}</span>
                            <span class="size-chart-tab-type">${typeLabel}</span>
                        </button>
                    `;
                })
                .join('');
        } catch (_) {}
    };

    const renderHtml = (html) => {
        if (!bodyEl) return;
        bodyEl.innerHTML = html || '';
    };

    const renderFromChart = (chart, charts) => {
        try {
            if (!chart) return false;

            if (chart.type === 'image' && chart.image_url) {
                modal.hide();
                const opened = openImageLightbox(chart.image_url);
                if (!opened) {
                    setLoading();
                    modal.show();
                    setError();
                }
                return true;
            }

            if (chart.type === 'html' && chart.html) {
                setTabsVisible(Array.isArray(charts) && charts.length > 1);
                renderHtml(chart.html);
                return true;
            }

            return false;
        } catch (_) {
            return false;
        }
    };

    trigger.addEventListener('click', async (e) => {
        e.preventDefault();

        const url = trigger.getAttribute('data-size-chart-url');

        clearTabs();
        setTabsVisible(false);

        if (!url || typeof axios === 'undefined') {
            setLoading();
            modal.show();
            setError();
            return;
        }

        try {
            const { data } = await axios.get(url);

            if (!data || !data.exists) {
                setLoading();
                modal.show();
                setError();
                return;
            }

            const charts = Array.isArray(data.charts) && data.charts.length ? data.charts : [data];

            // Multiple charts: open modal with tabs; default to first HTML chart if possible.
            if (charts.length > 1) {
                setLoading();
                modal.show();

                const defaultChart = charts.find((c) => c.type === 'html' && c.html) || charts[0];
                setTabsVisible(true);
                renderTabs(charts, defaultChart?.id);

                const ok = renderFromChart(defaultChart, charts);
                if (!ok) {
                    setError();
                    return;
                }

                // Bind events (once per render)
                if (tabsEl) {
                    tabsEl.querySelectorAll('[data-size-chart-tab]').forEach((btn) => {
                        btn.addEventListener('click', () => {
                            try {
                                const id = btn.getAttribute('data-chart-id');
                                const chart = charts.find((c) => String(c.id) === String(id));
                                if (!chart) return;

                                // Update active
                                tabsEl.querySelectorAll('[data-size-chart-tab]').forEach((b) => b.classList.remove('is-active'));
                                btn.classList.add('is-active');

                                const ok2 = renderFromChart(chart, charts);
                                if (!ok2) {
                                    setError();
                                }
                            } catch (_) {
                                setError();
                            }
                        });
                    });
                }

                return;
            }

            const first = charts[0];

            if (first.type === 'image' && first.image_url) {
                const opened = openImageLightbox(first.image_url);

                // If lightbox can't open for any reason, fall back to modal with error.
                if (!opened) {
                    setLoading();
                    modal.show();
                    setError();
                }

                return;
            }

            if (first.type === 'html' && first.html) {
                setLoading();
                modal.show();

                if (!bodyEl) return;
                bodyEl.innerHTML = first.html;
                return;
            }

            setLoading();
            modal.show();
            setError();
        } catch (_) {
            setLoading();
            modal.show();
            setError();
        }
    });
}

function getDefaultQty(product) {
    const def = Number(product?.unit_default_qty);
    if (def > 0) return def;

    const min = Number(product?.unit_min);
    if (min > 0) return min;

    return 1;
}

initSizeChartModal();

function isMobileGalleryViewport() {
    try {
        return window.innerWidth <= 990;
    } catch (_) {
        return false;
    }
}

function setVideoControls(video, enabled) {
    try {
        // Mobile: controls should always be visible inside the grid.
        if (isMobileGalleryViewport()) {
            video.controls = true;
            return;
        }

        video.controls = !!enabled;
    } catch (_) {}
}

function setGalleryVideoOverlayGlyph(wrapper, glyph) {
    try {
        const el = wrapper.querySelector('.fc-video-play-icon');
        if (!el) return;
        el.textContent = glyph;
    } catch (_) {}
}

function bindGalleryVideoOverlayState() {
    try {
        document.querySelectorAll('.gallery-preview-item--video').forEach((wrapper) => {
            try {
                if (wrapper.dataset.overlayBound === 'true') return;

                const video = wrapper.querySelector('.product-main-media--video');
                if (!video) return;

                wrapper.dataset.overlayBound = 'true';

                setGalleryVideoOverlayGlyph(wrapper, '▶');

                // Initial state
                const initiallyPlaying = (!video.paused && !video.ended);
                wrapper.dataset.playing = initiallyPlaying ? 'true' : 'false';
                setVideoControls(video, initiallyPlaying);

                const sync = () => {
                    try {
                        const playing = (!video.paused && !video.ended);
                        wrapper.dataset.playing = playing ? 'true' : 'false';
                        setVideoControls(video, playing);
                    } catch (_) {}
                };

                // If metadata loads after binding, ensure state is still correct
                video.addEventListener('loadedmetadata', sync);
                video.addEventListener('loadeddata', sync);

                video.addEventListener('play', () => {
                    wrapper.dataset.playing = 'true';
                    setVideoControls(video, true);
                });

                video.addEventListener('pause', () => {
                    wrapper.dataset.playing = 'false';
                    setVideoControls(video, false);
                    setGalleryVideoOverlayGlyph(wrapper, '▶');
                });

                video.addEventListener('ended', () => {
                    wrapper.dataset.playing = 'false';
                    setVideoControls(video, false);
                    setGalleryVideoOverlayGlyph(wrapper, '▶');
                });
            } catch (_) {}
        });
    } catch (_) {}
}

// Global helper: product header'daki rating'e tıklayınca yorumlar sekmesine git.
window.openReviewsTab = function openReviewsTab() {
    try {
        const tabLink = document.querySelector('.product-details-tab a[href="#reviews"]');
        if (!tabLink) return;

        // Bootstrap tab'i tetikle
        tabLink.click();

        // İçerik göründükten sonra yorumlar bölümüne kaydır
        const target = document.getElementById('reviews');
        if (!target) return;

        setTimeout(() => {
            try {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            } catch (_) {
                target.scrollIntoView(true);
            }
        }, 150);
    } catch (_) {}
};

function handleGalleryVideoTap(e) {
    const wrapper = e.target.closest('.gallery-preview-item--video');
    if (!wrapper) return;

    const video = wrapper.querySelector('.product-main-media--video');
    if (!video) return;

    const isMobile = isMobileGalleryViewport();

    const getClientPoint = () => {
        try {
            if (e.changedTouches && e.changedTouches[0]) {
                return { x: e.changedTouches[0].clientX, y: e.changedTouches[0].clientY };
            }
            if (typeof e.clientX === 'number' && typeof e.clientY === 'number') {
                return { x: e.clientX, y: e.clientY };
            }
        } catch (_) {}
        return null;
    };

    const isTapOnNativeControlsArea = () => {
        if (!isMobile) return false;
        try {
            const p = getClientPoint();
            if (!p) return false;
            const rect = video.getBoundingClientRect();
            // Roughly exclude bottom area where native controls live.
            const controlBarHeight = 72;
            const inX = p.x >= rect.left && p.x <= rect.right;
            const inY = p.y >= (rect.bottom - controlBarHeight) && p.y <= rect.bottom;
            return inX && inY;
        } catch (_) {
            return false;
        }
    };

    // Only hijack clicks intended for the overlay (or the wrapper while paused).
    // When playing, let native video controls handle interaction.
    const clickedOverlay = !!e.target.closest('.fc-video-play-icon');
    const isPlaying = !video.paused && !video.ended;
    if (!isMobile && isPlaying && !clickedOverlay) {
        return;
    }

    // Mobile: do not interfere with taps on the native controls bar.
    if (isMobile && isPlaying && isTapOnNativeControlsArea()) {
        return;
    }

    if (!clickedOverlay && !isPlaying) {
        // Allow tapping the video area to start playback when paused.
        // If the click is coming from some other element, don't interfere.
        const clickedVideo = e.target === video;
        if (!clickedVideo && e.target !== wrapper) {
            return;
        }
    }

    // Don't aggressively block default behavior unless the tap is clearly meant for our toggle.
    const shouldHijack = clickedOverlay || e.target === video || e.target === wrapper;
    if (shouldHijack) {
        e.preventDefault();
        e.stopPropagation();
    }

    try {
        if (isPlaying) {
            video.pause();
            wrapper.dataset.playing = 'false';
            setVideoControls(video, false);
            setGalleryVideoOverlayGlyph(wrapper, '❚❚');
            wrapper.dataset.flash = 'pause';
            setTimeout(() => {
                try {
                    setGalleryVideoOverlayGlyph(wrapper, '▶');
                    if (wrapper.dataset.flash === 'pause') delete wrapper.dataset.flash;
                } catch (_) {}
            }, 650);
        } else {
            pauseAllGalleryVideos(wrapper);
            const p = video.play();
            wrapper.dataset.flash = 'play';
            setTimeout(() => {
                try {
                    setGalleryVideoOverlayGlyph(wrapper, '▶');
                    if (wrapper.dataset.flash === 'play') delete wrapper.dataset.flash;
                } catch (_) {}
            }, 650);
            if (p && typeof p.then === 'function') {
                p.then(() => {
                    wrapper.dataset.playing = 'true';
                    setVideoControls(video, true);
                }).catch(() => {
                    wrapper.dataset.playing = 'false';
                    setVideoControls(video, false);
                });
            } else {
                // Fallback
                wrapper.dataset.playing = 'true';
                setVideoControls(video, true);
            }

            video.addEventListener('ended', () => {
                wrapper.dataset.playing = 'false';
            }, { once: true });
        }
    } catch (_) {}

    try {
        const wrap = document.querySelector('.product-gallery-preview-wrap');
        if (wrap && wrap.classList.contains('visible-variation-image')) {
            wrap.classList.remove('visible-variation-image');
        }
    } catch (_) {}
}

function pauseAllGalleryVideos(exceptWrapper = null) {
    try {
        document.querySelectorAll('.gallery-preview-item--video .product-main-media--video').forEach((v) => {
            try {
                const w = v.closest('.gallery-preview-item--video');
                if (exceptWrapper && w === exceptWrapper) return;
                if (!v.paused) v.pause();
                setVideoControls(v, false);
                if (w) w.dataset.playing = 'false';
            } catch (_) {}
        });
    } catch (_) {}
}

let __galleryTouch = { x: 0, y: 0, moved: false };
document.addEventListener(
    'touchstart',
    (e) => {
        try {
            const t = e.touches && e.touches[0] ? e.touches[0] : null;
            if (!t) return;
            __galleryTouch.x = t.clientX;
            __galleryTouch.y = t.clientY;
            __galleryTouch.moved = false;
        } catch (_) {}
    },
    { passive: true }
);

document.addEventListener(
    'touchmove',
    (e) => {
        try {
            const t = e.touches && e.touches[0] ? e.touches[0] : null;
            if (!t) return;
            const dx = Math.abs(t.clientX - __galleryTouch.x);
            const dy = Math.abs(t.clientY - __galleryTouch.y);
            if (dx > 10 || dy > 10) {
                __galleryTouch.moved = true;
            }
        } catch (_) {}
    },
    { passive: true }
);

// Desktop: click ile play/pause toggle
document.addEventListener('click', handleGalleryVideoTap, false);
// Mobile: swipe sonrası yanlışlıkla tetiklenmesin diye touchend'de swipe/tap ayrımı yap
document.addEventListener(
    'touchend',
    (e) => {
        if (__galleryTouch.moved) return;
        handleGalleryVideoTap(e);
    },
    { passive: false }
);

let galleryPreviewSlider;
let galleryPreviewLightbox;
let galleryPreviewZoomInstances = [];
let galleryPreviewPaginationObserver;

function attachReviewsTabListener() {
    const bind = () => {
        const tabLink = document.querySelector('.product-details-tab a[href="#reviews"]');
        if (!tabLink) {
            return;
        }

        const triggerFetch = () => {
            try {
                window.FleetCart?.page?.fetchReviews?.();
            } catch (_) {}
        };

        const onShown = () => {
            triggerFetch();
            tabLink.removeEventListener("shown.bs.tab", onShown);
        };

        tabLink.addEventListener("shown.bs.tab", onShown);

        if (tabLink.classList.contains("active")) {
            triggerFetch();
        }
    };

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", bind, { once: true });
    } else {
        bind();
    }
}

attachReviewsTabListener();

Alpine.data(
    "ProductShow",
    ({ product, variant, reviewCount, avgRating, ratingBreakdown, flashSalePrice }) => ({
        product: product,
        item: variant || product,
        optionPrices: {},
        errors: new Errors(),
        addingToCart: false,
        oldMediaLength: null,
        activeVariationValues: {},
        variationImagePath: null,
        showDescriptionContent: false,
        showMore: false,
        showCustomTabContent: false,
        showCustomTabMore: false,
        showCustomTab2Content: false,
        showCustomTab2More: false,
        fetchingReviews: false,
        reviewsLoaded: false,
        reviews: { data: [], total: 0 },
        reviewCount,
        avgRating,
        // Rating dağılımı (yorum istatistikleri)
        ratingBreakdown: ratingBreakdown || { 1: 0, 2: 0, 3: 0, 4: 0, 5: 0 },
        addingNewReview: false,
        _reviewsObserverInitialized: false,
        reviewForm: {
            rating: null,
            reviewer_name: "",
            comment: "",
        },
        currentPage: 1,

        // Review kuponu için: review request mailinden gelen order_id query parametresi
        orderIdFromQuery: null,
        cartItemForm: {
            product_id: product.id,
            // Qty başlangıcı tamamen unit modülüne bağlı: önce unit_default_qty, sonra unit_min; yoksa 1
            qty: getDefaultQty(product),
            variations: {},
            options: {},
        },

        prefetchVariantMedia(variationIndex, valueIndex) {
            try {
                if (!this.hasAnyVariant) return;

                const variation = this.product.variations?.[variationIndex];
                const value = variation?.values?.[valueIndex];

                if (!variation || !value) return;

                const nextVariations = {
                    ...(this.cartItemForm?.variations || {}),
                    [variation.uid]: value.uid,
                };

                const selectedUids = Object.values(nextVariations)
                    .filter(Boolean)
                    .sort()
                    .join(".");

                const variant = this.product.variants?.find((v) => v && v.uids === selectedUids);

                const urls = [];

                const firstMedia = Array.isArray(variant?.media) ? variant.media.slice(0, 2) : [];
                const fallbackMedia = Array.isArray(this.product?.media) ? this.product.media.slice(0, 1) : [];

                [...firstMedia, ...fallbackMedia].forEach((m) => {
                    if (!m) return;

                    const main = m.detail_webp_url || m.detail_jpeg_url || m.grid_webp_url || m.grid_jpeg_url || m.path;
                    const thumb = m.thumb_webp_url || m.thumb_jpeg_url || m.grid_webp_url || m.grid_jpeg_url || m.path;

                    if (main) urls.push(main);
                    if (thumb) urls.push(thumb);
                });

                const seen = (window.__fc_prefetched_images = window.__fc_prefetched_images || {});

                urls.forEach((u) => {
                    if (!u || seen[u]) return;
                    seen[u] = true;
                    const img = new Image();
                    img.decoding = "async";
                    img.src = u;
                });
            } catch (_) {}
        },

        prefetchPopularVariantMedia() {
            try {
                if (!this.hasAnyVariant) return;

                const conn = navigator.connection;
                if (conn && (conn.saveData || /2g/.test(conn.effectiveType || ""))) {
                    return;
                }

                const variants = Array.isArray(this.product?.variants) ? this.product.variants : [];
                if (!variants.length) return;

                const seen = (window.__fc_prefetched_images = window.__fc_prefetched_images || {});

                // Limit network impact: prefetch first image for first N variants
                const maxVariants = 6;
                const maxPerVariant = 1;

                let totalQueued = 0;
                const maxTotal = 8;

                for (let i = 0; i < variants.length && i < maxVariants; i++) {
                    const v = variants[i];
                    const media = Array.isArray(v?.media) ? v.media : [];
                    const first = media.slice(0, maxPerVariant);

                    for (const m of first) {
                        if (!m) continue;
                        const url =
                            m.detail_webp_url ||
                            m.detail_jpeg_url ||
                            m.grid_webp_url ||
                            m.grid_jpeg_url ||
                            m.path;

                        if (!url || seen[url]) continue;
                        seen[url] = true;

                        const img = new Image();
                        img.decoding = "async";
                        img.src = url;

                        totalQueued += 1;
                        if (totalQueued >= maxTotal) {
                            return;
                        }
                    }
                }
            } catch (_) {}
        },

        deferRelatedProducts() {
            try {
                if (this._relatedProductsRequested) {
                    return;
                }
                this._relatedProductsRequested = true;

                setTimeout(async () => {
                    try {
                        const base = (window.FleetCart && FleetCart.baseUrl) ? FleetCart.baseUrl : '';
                        const res = await axios.get(`${base}/products/${this.product.id}/related`);
                        const items = Array.isArray(res.data)
                            ? res.data
                            : Array.isArray(res.data?.data)
                              ? res.data.data
                              : [];

                        const root = document.querySelector('[data-related-products]');
                        if (!items.length) {
                            if (root) root.classList.add('d-none');
                            return;
                        }

                        if (root) root.classList.remove('d-none');

                        const wrapper = document.querySelector('[data-related-products] .swiper-wrapper');
                        if (!wrapper) return;

                        const tpl = document.querySelector('[data-related-product-card-template]');
                        const tplHtml = tpl ? tpl.innerHTML : '';
                        if (!tplHtml) return;

                        // remove skeletons
                        wrapper.querySelectorAll('.swiper-slide-skeleton').forEach((el) => el.remove());
                        wrapper.querySelectorAll('[data-related-products-placeholder]').forEach((el) => el.remove());

                        items.forEach((p) => {
                            const slide = document.createElement('div');
                            slide.className = 'swiper-slide';
                            const safeJson = JSON.stringify(p)
                                .replace(/</g, "\\u003c")
                                .replace(/>/g, "\\u003e")
                                .replace(/&/g, "\\u0026")
                                .replace(/'/g, "\\u0027");
                            slide.innerHTML = tplHtml.replace('__PRODUCT__', safeJson);
                            wrapper.appendChild(slide);

                            try {
                                if (window.Alpine && typeof window.Alpine.initTree === 'function') {
                                    window.Alpine.initTree(slide);
                                }
                            } catch (_) {}
                        });

                        this.$nextTick(() => {
                            try {
                                this.initRelatedProductsSlider();
                            } catch (_) {}
                        });
                    } catch (_) {}
                }, 350);
            } catch (_) {}
        },
        isEditingQty: false,
        qtyInput: "",
        // Min qty: sadece unit_min'den okunur; yoksa güvenli default 1
        minQty: (Number(product.unit_min) > 0 ? Number(product.unit_min) : 1),
        // Step qty: sadece unit_step'ten okunur; yoksa 1 (adet ürünler için)
        stepQty: (Number(product.unit_step) > 0 ? Number(product.unit_step) : 1),

        // ---- Review upload state ----
        isDraggingUpload: false,
        reviewImages: [],
        maxReviewPhotos: 4,

        previewVariantName: null,

        get productName() {
            const base = this.product?.name || "";

            if (this.previewVariantName) {
                return `${base} (${this.previewVariantName})`.trim();
            }

            if (this.hasAnyVariant && this.item?.name) {
                return `${base} (${this.item.name})`.trim();
            }

            return base;
        },

        get isActiveItem() {
            return this.item.is_active === true;
        },

        get productUrl() {
            const slugify = (input) => {
                try {
                    if (input === null || input === undefined) return "";

                    let str = String(input)
                        .trim()
                        .toLowerCase();

                    str = str
                        .replace(/ğ/g, "g")
                        .replace(/ü/g, "u")
                        .replace(/ş/g, "s")
                        .replace(/ı/g, "i")
                        .replace(/i̇/g, "i")
                        .replace(/ö/g, "o")
                        .replace(/ç/g, "c");

                    try {
                        str = str.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
                    } catch (_) {}

                    str = str
                        .replace(/[^a-z0-9\s-]/g, " ")
                        .replace(/\s+/g, "-")
                        .replace(/-+/g, "-")
                        .replace(/^-|-$/g, "");

                    return str;
                } catch (_) {
                    return "";
                }
            };

            const base = `/products/${this.product.slug}`;

            try {
                if (!this.hasAnyVariant || !this.product || !Array.isArray(this.product.variations)) {
                    return base;
                }

                const params = new URLSearchParams();

                for (const variation of this.product.variations) {
                    const variationUid = variation?.uid;
                    const key = slugify(variation?.name);
                    if (!variationUid || !key) continue;

                    const selectedValueUid = this.cartItemForm?.variations?.[variationUid];
                    if (!selectedValueUid) continue;

                    const selectedValue = (variation?.values || []).find(
                        (v) => String(v?.uid) === String(selectedValueUid)
                    );

                    const valueSlug = slugify(selectedValue?.label);
                    if (!valueSlug) continue;

                    params.set(key, valueSlug);
                }

                const qs = params.toString();
                return qs ? `${base}?${qs}` : base;
            } catch (_) {
                return base;
            }
        },

        get hasAnyMedia() {
            return this.item.media.length !== 0;
        },

        get productPrice() {
            return this.hasSpecialPrice
                ? this.item.selling_price.inCurrentCurrency.amount
                : this.item.price.inCurrentCurrency.amount;
        },

        get regularPrice() {
            let productPrice = this.item.price.inCurrentCurrency.amount;

            if (
                this.hasAnyOption &&
                !this.hasSpecialPrice &&
                this.hasAnyOptionPrice
            ) {
                return productPrice + this.optionsPrice;
            }

            return productPrice;
        },

        get hasSpecialPrice() {
            return (
                this.product.is_in_flash_sale ||
                this.item.special_price !== null
            );
        },

        get hasPercentageSpecialPrice() {
            return this.item.has_percentage_special_price;
        },

        get specialPrice() {
            let productPrice = this.item.selling_price.inCurrentCurrency.amount;

            if (flashSalePrice && !this.hasAnyVariant) {
                productPrice = flashSalePrice;
            }

            if (
                this.hasAnyOption &&
                this.hasSpecialPrice &&
                this.hasAnyOptionPrice
            ) {
                return productPrice + this.optionsPrice;
            }

            return productPrice;
        },

        get isInStock() {
            return this.item.is_in_stock;
        },

        get isOutOfStock() {
            return this.item.is_out_of_stock;
        },

        get doesManageStock() {
            return this.item.does_manage_stock;
        },

        get hasAnyVariationImage() {
            return this.variationImagePath !== null;
        },

        get inWishlist() {
            return this.$store.wishlist.inWishlist(this.product.id);
        },

        get inCompareList() {
            return this.$store.compare.inCompareList(this.product.id);
        },

        get hasAnyVariant() {
            return this.product.variant !== null;
        },

        get hasAnyOption() {
            return this.product.options.length > 0;
        },

        get hasAnyOptionPrice() {
            return Object.keys(this.optionPrices).length !== 0;
        },

        get optionsPrice() {
            return Object.values(this.optionPrices).reduce(
                (total, value) => total + value,
                0
            );
        },

        get isAddToCartDisabled() {
            return this.isActiveItem ? this.isOutOfStock : true;
        },

        get maxQuantity() {
            return this.isInStock && this.doesManageStock
                ? this.item.qty
                : null;
        },

        get isQtyIncreaseDisabled() {
            return (
                this.isOutOfStock ||
                (this.maxQuantity !== null &&
                    this.cartItemForm.qty >= this.item.qty) ||
                !this.isActiveItem
            );
        },

        get isQtyDecreaseDisabled() {
            return (
                this.isOutOfStock ||
                this.cartItemForm.qty <= this.minQty ||
                !this.isActiveItem
            );
        },

        get totalReviews() {
            if (!this.reviews.total) {
                return this.reviewCount;
            }

            return this.reviews.total;
        },

        get ratingPercent() {
            return (this.avgRating / 5) * 100;
        },

        get emptyReviews() {
            return this.totalReviews === 0;
        },

        get totalPage() {
            return Math.ceil(this.reviews.total / 5);
        },

        getRatingColor(rating) {
            const r = Number(rating) || 0;

            if (r >= 4.5) {
                return "#16a34a"; // çok iyi
            }

            if (r >= 3) {
                return "#facc15"; // orta
            }

            if (r > 0) {
                return "#ef4444"; // düşük
            }

            return "#9ca3af"; // rating yoksa gri
        },

        init() {
            try {
                const pid = FleetCart?.data?.productId;
                const piw = FleetCart?.data?.productInWishlist;
                if (pid && typeof piw !== 'undefined') {
                    this.$store.wishlist.bootstrap(pid, piw);
                }
            } catch (_) {}

            try {
                window.FleetCart = window.FleetCart || {};
                window.FleetCart.page = this;
            } catch (_) {}

            // URL'den order_id query parametresini oku (yorum kuponu için gerekecek)
            try {
                const params = new URLSearchParams(window.location.search || "");
                const rawOrderId = params.get("order_id");
                const parsed = rawOrderId ? parseInt(rawOrderId, 10) : null;
                this.orderIdFromQuery = Number.isFinite(parsed) && parsed > 0 ? parsed : null;
            } catch (_) {
                this.orderIdFromQuery = null;
            }

            this.$watch("cartItemForm.options", () => {
                this.productPriceWithOptionsPrice();
            });

            galleryPreviewSlider = this.initGalleryPreviewSlider();
            galleryPreviewLightbox = this.initGalleryPreviewLightbox();

            // On first load, Swiper may render pagination before we can hide it.
            // Run the pagination visibility check multiple times to be safe.
            try {
                this.updateGalleryPaginationVisibility();
            } catch (_) {}
            this.$nextTick(() => {
                try {
                    this.updateGalleryPaginationVisibility();
                } catch (_) {}
                try {
                    setTimeout(() => {
                        try {
                            this.updateGalleryPaginationVisibility();
                        } catch (_) {}
                    }, 80);
                } catch (_) {}
            });
            

            this.initReviewsDefer();
            try {
                const relatedRoot = document.querySelector('[data-related-products]');
                const carousel = relatedRoot
                    ? relatedRoot.querySelector('.related-products-carousel.swiper')
                    : null;

                // SSR carousel mode: init swiper when markup exists
                if (carousel) {
                    this.$nextTick(() => {
                        try {
                            this.initRelatedProductsSlider();
                        } catch (_) {}
                    });
                } else {
                    const relatedHasRealSlides = !!(relatedRoot && relatedRoot.querySelector('.swiper-slide') && !relatedRoot.querySelector('.swiper-slide-skeleton'));
                    const shouldDeferRelated = !relatedRoot || relatedRoot.classList.contains('d-none') || !relatedHasRealSlides;
                    if (shouldDeferRelated) {
                        this.deferRelatedProducts();
                    }
                }
            } catch (_) {}

            try {
                const upsellRoot = document.querySelector('[data-upsell-products]');
                // Upsell is rendered server-side in the left sidebar (vertical-products).
                // No deferred fetching/rendering to avoid overriding the default layout.
                if (!upsellRoot || upsellRoot.classList.contains('d-none')) {
                    return;
                }
            } catch (_) {}
            this.setOldMediaLength();
            this.initGalleryPreviewZoom();
            bindGalleryVideoOverlayState();
            this.setActiveVariationsValue();
            this.setDescriptionContentHeight();
            this.setCustomTabContentHeight();
            this.setCustomTab2ContentHeight();
            this.initUpSellProductsSlider();

            // Yorum görselleri için lightbox başlat
            this.initReviewLightbox();
            this.fetchReviews();
            this.updateBadgeVisibilityForActiveSlide();

            try {
                const run = () => this.prefetchPopularVariantMedia();
                if ("requestIdleCallback" in window) {
                    window.requestIdleCallback(run, { timeout: 1500 });
                } else {
                    setTimeout(run, 700);
                }
            } catch (_) {}
        },

        syncWishlist() {
            this.$store.wishlist.syncWishlist(this.product.id);
        },

        syncCompareList() {
            this.$store.compare.syncCompareList(this.product.id);
        },

        setOldMediaLength() {
            if (this.hasAnyVariant) {
                this.oldMediaLength = this.item.media.length;
            }
        },

        initGalleryPreviewSlider() {
            const slider = new Swiper(".product-gallery-preview", {
                modules: [Manipulation, Navigation, Pagination],
                slidesPerView: 1,
                // Masaüstünde oklarla, mobilde parmakla kaydırma
                allowTouchMove: this.shouldAllowGalleryTouchMove(),
                navigation: {
                    nextEl: ".swiper-button-next",
                    prevEl: ".swiper-button-prev",
                },
                pagination: {
                    el: ".product-gallery-preview .swiper-pagination",
                    clickable: true,
                },
            });
            const syncTouchAllowance = () => {
                try {
                    const allowTouchMove = this.shouldAllowGalleryTouchMove();
                    slider.allowTouchMove = allowTouchMove;
                    if (slider.params) {
                        slider.params.allowTouchMove = allowTouchMove;
                    }
                } catch (_) {}
            };

            slider.on("slideChange", () => {
                pauseAllGalleryVideos(null);
                this.updateBadgeVisibilityForActiveSlide();
            });
            slider.on("resize", syncTouchAllowance);
            try {
                const coarseMq = window.matchMedia
                    ? window.matchMedia("(pointer: coarse)")
                    : null;
                if (coarseMq) {
                    const handleMqChange = () => syncTouchAllowance();
                    if (typeof coarseMq.addEventListener === "function") {
                        coarseMq.addEventListener("change", handleMqChange);
                    } else if (typeof coarseMq.addListener === "function") {
                        coarseMq.addListener(handleMqChange);
                    }
                }
            } catch (_) {}
            syncTouchAllowance();

            // Aggressive pagination control: ensure dots are removed when there is only 1 slide.
            try {
                this.updateGalleryPaginationVisibility();
            } catch (_) {}
            slider.on("update", () => {
                try {
                    this.updateGalleryPaginationVisibility();
                } catch (_) {}
            });
            slider.on("slidesLengthChange", () => {
                try {
                    this.updateGalleryPaginationVisibility();
                } catch (_) {}
            });

            try {
                requestAnimationFrame(() => {
                    try {
                        this.updateGalleryPaginationVisibility();
                    } catch (_) {}
                });
            } catch (_) {}

            return slider;
        },

        updateGalleryPaginationVisibility() {
            try {
                if (!galleryPreviewSlider) return;

                const rootEl = document.querySelector(".product-gallery-preview");
                const paginationEl = document.querySelector(
                    ".product-gallery-preview .swiper-pagination"
                );

                const slidesCount = Array.isArray(galleryPreviewSlider.slides)
                    ? galleryPreviewSlider.slides.length
                    : 0;

                const shouldHide = slidesCount <= 1;

                if (rootEl) {
                    rootEl.classList.toggle("is-pagination-hidden", shouldHide);
                }

                if (shouldHide) {
                    // Kill pagination behaviors and markup to prevent any dots from appearing.
                    try {
                        if (galleryPreviewSlider.pagination && typeof galleryPreviewSlider.pagination.destroy === "function") {
                            galleryPreviewSlider.pagination.destroy();
                        }
                    } catch (_) {}

                    if (paginationEl) {
                        paginationEl.innerHTML = "";
                        paginationEl.style.setProperty("display", "none", "important");
                        paginationEl.style.setProperty("visibility", "hidden", "important");
                        paginationEl.style.setProperty("pointer-events", "none", "important");
                        paginationEl.setAttribute("hidden", "hidden");

                        // Keep it hidden even if Swiper (or other code) re-injects bullets.
                        try {
                            if (galleryPreviewPaginationObserver) {
                                galleryPreviewPaginationObserver.disconnect();
                            }

                            galleryPreviewPaginationObserver = new MutationObserver(() => {
                                try {
                                    paginationEl.innerHTML = "";
                                    paginationEl.style.setProperty("display", "none", "important");
                                    paginationEl.style.setProperty("visibility", "hidden", "important");
                                    paginationEl.style.setProperty("pointer-events", "none", "important");
                                    paginationEl.setAttribute("hidden", "hidden");
                                } catch (_) {}
                            });

                            galleryPreviewPaginationObserver.observe(paginationEl, {
                                childList: true,
                                subtree: true,
                            });
                        } catch (_) {}
                    }

                    try {
                        if (galleryPreviewSlider.params && galleryPreviewSlider.params.pagination) {
                            galleryPreviewSlider.params.pagination.clickable = false;
                        }
                    } catch (_) {}

                    return;
                }

                // Re-enable pagination when there are multiple slides.
                if (paginationEl) {
                    try {
                        if (galleryPreviewPaginationObserver) {
                            galleryPreviewPaginationObserver.disconnect();
                        }
                    } catch (_) {}

                    paginationEl.removeAttribute("hidden");
                    paginationEl.style.setProperty("display", "", "important");
                    paginationEl.style.setProperty("visibility", "", "important");
                    paginationEl.style.setProperty("pointer-events", "", "important");
                }

                try {
                    if (galleryPreviewSlider.params && galleryPreviewSlider.params.pagination) {
                        galleryPreviewSlider.params.pagination.clickable = true;
                    }
                } catch (_) {}

                try {
                    if (galleryPreviewSlider.pagination && typeof galleryPreviewSlider.pagination.init === "function") {
                        galleryPreviewSlider.pagination.init();
                        if (typeof galleryPreviewSlider.pagination.render === "function") {
                            galleryPreviewSlider.pagination.render();
                        }
                        if (typeof galleryPreviewSlider.pagination.update === "function") {
                            galleryPreviewSlider.pagination.update();
                        }
                    }
                } catch (_) {}
            } catch (_) {}
        },

        updateGallerySlider() {
            if (!galleryPreviewSlider) {
                return;
            }

            this.removeAllGallerySlides();

            // If product and variant has not media
            if (this.product.media.length === 0 && !this.hasAnyMedia) {
                this.addGalleryEmptySlide();
            } else {
                // 1) Sadece image slidelarını kur
                this.addGallerySlides();
                // 2) Videoları 3. kanal gibi en sona ekle
                this.appendVideoSlides();
            }

            this.addGalleryEventListeners();
            this.updateBadgeVisibilityForActiveSlide();
            this.updateGalleryPaginationVisibility();
        },

        addGallerySlides() {
            // Swiper yoksa devam etme
            if (!galleryPreviewSlider) {
                return;
            }

            const galleryPreviewSlides = [];
            

            const variantMedia = Array.isArray(this.item.media) ? this.item.media : [];
            const productMedia = Array.isArray(this.product.media) ? this.product.media : [];

            // DEFAULT: sadece item.media + product.media (sadece IMAGES)
            const allMedia = [...variantMedia, ...productMedia];

            const seen = new Set();

            allMedia.forEach((m) => {
                const path = m?.path || m?.thumb || m?.jpeg || m?.url;

                if (!path) return;
                if (seen.has(path)) return;

                seen.add(path);

                // Keep original ordering behavior (variant media should stay ahead)
                galleryPreviewSlides.unshift(this.galleryPreviewSlide(m));
            });

            if (!galleryPreviewSlides.length) {
                this.addGalleryEmptySlide();
                return;
            }

            galleryPreviewSlider.addSlide(0, galleryPreviewSlides);
            galleryPreviewSlider.update();
            galleryPreviewSlider.slideTo(0);
        },

        appendVideoSlides() {
            if (!galleryPreviewSlider) {
                return;
            }

            const rawVideos = Array.isArray(this.product.product_media)
                ? this.product.product_media
                : (Array.isArray(this.product.productMedia)
                    ? this.product.productMedia
                    : []);

            if (!rawVideos.length) {
                return;
            }

            rawVideos
                .filter((v) => v && v.type === "video" && v.path)
                .forEach((v) => {
                    const videoPath = v.path;

                    const poster =
                        v.poster ||
                        v.thumb ||
                        this.item?.base_image?.path ||
                        this.product?.base_image?.path ||
                        `${FleetCart.baseUrl}/build/assets/image-placeholder.png`;

                    const previewSlide = this.galleryPreviewVideoSlide(videoPath, poster);
                    const lastIndex = galleryPreviewSlider.slides.length;
                    galleryPreviewSlider.addSlide(lastIndex, previewSlide);
                });
            galleryPreviewSlider.update();
        },

        addGalleryEmptySlide() {
            const filePath = `${FleetCart.baseUrl}/build/assets/image-placeholder.png`;

            const placeholderFile = {
                path: filePath,
            };

            galleryPreviewSlider.addSlide(
                0,
                this.galleryPreviewSlide(placeholderFile, true)
            );
        },

        removeAllGallerySlides() {
            if (!galleryPreviewSlider) {
                return;
            }

            galleryPreviewSlider.removeAllSlides();
        },

        addGalleryEventListeners() {
            this.$nextTick(() => {
                this.initGalleryPreviewZoom();
                bindGalleryVideoOverlayState();
                galleryPreviewLightbox.reload();
                try {
                    this.updateGalleryPaginationVisibility();
                } catch (_) {}
            });
        },

        updateBadgeVisibilityForActiveSlide() {
            try {
                const wrap = document.querySelector(".product-gallery-preview-wrap");
                if (!wrap || !galleryPreviewSlider || !galleryPreviewSlider.slides) {
                    return;
                }

                const activeIndex = galleryPreviewSlider.activeIndex || 0;
                const activeSlide = galleryPreviewSlider.slides[activeIndex];
                if (!activeSlide) {
                    wrap.classList.remove("is-video-active");
                    return;
                }

                const isVideo = !!activeSlide.querySelector(".gallery-preview-item--video");

                if (isVideo) {
                    wrap.classList.add("is-video-active");
                } else {
                    wrap.classList.remove("is-video-active");
                }
            } catch (_) {}
        },

        initGalleryPreviewZoom() {
            // Disable Drift zoom entirely (all devices). Lightbox is used on click.
            return;
        },

        initGalleryPreviewLightbox() {
            return GLightbox({
                zoomable: true,
                preload: false,
            });
        },

        triggerGalleryPreviewLightbox(event) {
            try {
                if (this.isMobileDevice()) {
                    return;
                }

                if (window.innerWidth > 990) {
                    event.currentTarget.nextElementSibling.click();
                }
            } catch (_) {}
        },

        buildPreviewImageSources(file) {
            if (!file) return { avif: null, webp: null, jpeg: `${FleetCart.baseUrl}/build/assets/image-placeholder.png`, zoom: `${FleetCart.baseUrl}/build/assets/image-placeholder.png` };

            const avif = file.detail_avif_url || file.grid_avif_url || null;
            const webp = file.detail_webp_url || file.grid_webp_url || null;
            const jpeg =
                file.detail_jpeg_url ||
                file.grid_jpeg_url ||
                file.path ||
                `${FleetCart.baseUrl}/build/assets/image-placeholder.png`;

            const zoom =
                file.detail_jpeg_url ||
                file.detail_webp_url ||
                jpeg;

            return { avif, webp, jpeg, zoom };
        },

        galleryPreviewSlide(file, isPlaceholder = false) {
            const sources = this.buildPreviewImageSources(file);
            const imgClass = isPlaceholder ? "image-placeholder" : "";

            const avifSource = sources.avif
                ? `<source srcset="${sources.avif}" type="image/avif">`
                : "";
            const webpSource = sources.webp
                ? `<source srcset="${sources.webp}" type="image/webp">`
                : "";

            return `
                <div class="swiper-slide">
                    <div class="gallery-preview-slide">
                        <div class="gallery-preview-item" @click="triggerGalleryPreviewLightbox(event)">
                            <picture>
                                ${avifSource}
                                ${webpSource}
                                <img src="${sources.jpeg}" alt="${this.productName}" loading="lazy" decoding="async" class="${imgClass}">
                            </picture>
                        </div>

                        <a href="${sources.jpeg}" data-gallery="product-gallery-preview" class="gallery-view-icon glightbox">
                            <i class="las la-search-plus"></i>
                        </a>
                    </div>
                </div>
            `;
        },

        galleryPreviewVideoSlide(videoPath, posterPath) {
            return `
                <div class="swiper-slide">
                    <div class="gallery-preview-slide">
                        <div class="gallery-preview-item gallery-preview-item--video" data-media-type="video">
                            <video
                                class="product-main-media product-main-media--video"
                                controls
                                controlslist="nofullscreen"
                                playsinline
                                preload="metadata"
                                poster="${posterPath}"
                            >
                                <source src="${videoPath}" type="video/mp4">
                            </video>
                            <span class="fc-video-play-icon">▶</span>
                        </div>
                    </div>
                </div>
            `;
        },

        productPriceWithOptionsPrice() {
            const cartItemoptions = Object.entries(this.cartItemForm.options);

            cartItemoptions.forEach(([key, value]) => {
                const option = this.product.options.find(
                    ({ id }) => id === Number(key)
                );

                // Single select with single value
                if (
                    ["field", "textarea", "date", "date_time", "time"].includes(
                        option.type
                    )
                ) {
                    if (!Boolean(this.cartItemForm.options[option.id])) {
                        delete this.optionPrices[option.id];

                        return;
                    }

                    const optionValue = option.values[0];
                    const price =
                        optionValue.price?.inCurrentCurrency?.amount ??
                        (+optionValue.price / 100) * this.productPrice;

                    this.optionPrices[key] = price;

                    return;
                }

                // Single select with multiple values
                if (
                    ["dropdown", "radio", "radio_custom"].includes(option.type)
                ) {
                    const optionValue = option.values.find(
                        ({ id }) => id === Number(value)
                    );

                    const price =
                        optionValue.price?.inCurrentCurrency?.amount ??
                        (+optionValue.price / 100) * this.productPrice;

                    this.optionPrices[key] = price;

                    return;
                }

                // Multiple select with multiple values
                if (
                    ["checkbox", "checkbox_custom", "multiple_select"].includes(
                        option.type
                    ) &&
                    value.length !== 0
                ) {
                    const values = this.product.options
                        .find(({ id }) => id === Number(key))
                        .values.filter((data) => value.includes(data.id));

                    const price = values.reduce(
                        (accumulator, value) =>
                            accumulator +
                            (value.price?.inCurrentCurrency?.amount ??
                                (+value.price / 100) * this.productPrice),
                        0
                    );

                    this.optionPrices[key] = price;
                }
            });
        },

        isVariationValueEnabled(variationUid, variationIndex, valueUid) {
            // Check if enabled first variation values
            if (variationIndex === 0) {
                return this.doesVariantExist(valueUid);
            }

            // Check if enabled variation values between first and last variation
            if (
                variationIndex > 0 &&
                variationIndex < this.product.variations.length - 1
            ) {
                return this.doesVariantExist(valueUid);
            }

            // Check if enabled last variation values
            if (variationIndex === this.product.variations.length - 1) {
                const variations = this.cartItemForm.variations;
                const valueUids = Object.values(variations).filter(
                    (uid) => uid !== variations[variationUid]
                );

                valueUids.push(valueUid);

                return this.doesVariantExist(valueUids.sort().join("."));
            }
        },

        setActiveVariationsValue() {
            if (!this.hasAnyVariant) return;

            this.item.uids.split(".").forEach((uid) => {
                this.product.variations.some((variation) => {
                    const value = variation.values.find(
                        (value) => value.uid === uid
                    );

                    if (value !== undefined) {
                        this.activeVariationValues[variation.uid] = value.label;
                        this.cartItemForm.variations[variation.uid] = uid;

                        return true;
                    }
                });
            });
        },

        setActiveVariationValueLabel(variationIndex) {
            this.variationImagePath = null;
            this.previewVariantName = null;

            const variation = this.product.variations[variationIndex];
            if (!variation || !variation.values || !Array.isArray(variation.values)) {
                return;
            }
            const value = variation.values.find(
                (value) =>
                    value.uid === this.cartItemForm.variations[variation.uid]
            );

            if (!value || typeof value.label === "undefined") {
                return;
            }

            this.activeVariationValues[variation.uid] = value.label;
        },

        setVariationValueLabel(variationIndex, valueIndex) {
            const variation = this.product.variations[variationIndex];
            const value = variation.values[valueIndex];

            try {
                const nextVariations = {
                    ...(this.cartItemForm?.variations || {}),
                    [variation.uid]: value.uid,
                };

                const selectedUids = Object.values(nextVariations)
                    .filter(Boolean)
                    .sort()
                    .join(".");

                const variant = this.product?.variants?.find((v) => v && v.uids === selectedUids);

                this.previewVariantName = variant?.name || null;
            } catch (_) {
                this.previewVariantName = null;
            }

            if (!this.isMobileDevice() && variation.type === "image") {
                const img = value?.image || {};
                this.variationImagePath =
                    img.detail_webp_url ||
                    img.detail_jpeg_url ||
                    img.url ||
                    img.path ||
                    null;
            }

            this.activeVariationValues[variation.uid] = value.label;
        },

        isActiveVariationValue(variationUid, valueUid) {
            if (!this.cartItemForm.variations.hasOwnProperty(variationUid)) {
                return false;
            }

            return this.cartItemForm.variations[variationUid] === valueUid;
        },

        syncVariationValue(variationUid, variationIndex, valueUid, valueIndex) {
            if (!this.isActiveVariationValue(variationUid, valueUid)) {
                this.cartItemForm.variations[variationUid] = valueUid;

                this.setVariationValueLabel(variationIndex, valueIndex);
                this.updateVariantDetails();

                // Mobile UX: after variant selection, scroll back to the main gallery.
                if (this.isMobileDevice()) {
                    this.$nextTick(() => {
                        try {
                            // Behave like the ScrollToTop button.
                            // Only needed on mobile where layout stacks vertically.
                            const variation = this.product?.variations?.[variationIndex];
                            const shouldScrollTop = !variation || variation.type === 'image';

                            if (shouldScrollTop) {
                                window.scrollTo({ top: 0, behavior: 'smooth' });
                            }
                        } catch (_) {}
                    });
                }
            }
        },

        doesVariantExist(uid) {
            return this.product.variants.some(({ uids }) => uids.includes(uid));
        },

        setVariant() {
            const selectedUids = Object.values(this.cartItemForm.variations)
                .sort()
                .join(".");

            const variant = this.product.variants.find(
                (variant) => variant.uids === selectedUids
            );

            if (variant !== undefined) {
                this.item = { ...variant };
                return;
            }

            // Set empty variant data if variant does not exist
            const uid = md5(
                Object.values(this.cartItemForm.variations).sort().join(".")
            );

            this.item = {
                uid,
                media: [],
                base_image: [],
            };
        },

        setVariantSlug() {
            const slugify = (input) => {
                try {
                    if (input === null || input === undefined) return "";

                    let str = String(input)
                        .trim()
                        .toLowerCase();

                    // Turkish-safe transliteration (keep aligned with backend Str::slug output)
                    str = str
                        .replace(/ğ/g, "g")
                        .replace(/ü/g, "u")
                        .replace(/ş/g, "s")
                        .replace(/ı/g, "i")
                        .replace(/i̇/g, "i")
                        .replace(/ö/g, "o")
                        .replace(/ç/g, "c");

                    // Strip diacritics where supported
                    try {
                        str = str.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
                    } catch (_) {}

                    str = str
                        .replace(/[^a-z0-9\s-]/g, " ")
                        .replace(/\s+/g, "-")
                        .replace(/-+/g, "-")
                        .replace(/^-|-$/g, "");

                    return str;
                } catch (_) {
                    return "";
                }
            };

            try {
                const current = new URL(window.location.href);

                // Preserve query params but remove legacy variant param.
                current.searchParams.delete("variant");

                // Also remove readable variation params (?renk=kirmizi&beden=xl) to avoid conflicts.
                try {
                    if (this.product && Array.isArray(this.product.variations)) {
                        for (const variation of this.product.variations) {
                            const key = slugify(variation?.name);
                            if (key) {
                                current.searchParams.delete(key);
                            }
                        }
                    }
                } catch (_) {}

                // Preserve locale prefix by reusing the existing pathname prefix up to /products/.
                const pathname = current.pathname || "";
                const productsMarker = "/products/";
                const idx = pathname.lastIndexOf(productsMarker);
                const prefix = idx >= 0 ? pathname.slice(0, idx + productsMarker.length) : productsMarker;

                const baseSlug = this.product?.slug || "";

                if (!baseSlug) {
                    return;
                }

                // Keep pathname as base product URL and write readable variation params.
                current.pathname = `${prefix}${baseSlug}`;

                try {
                    if (this.product && Array.isArray(this.product.variations)) {
                        for (const variation of this.product.variations) {
                            const variationUid = variation?.uid;
                            const key = slugify(variation?.name);
                            if (!variationUid || !key) {
                                continue;
                            }

                            const selectedValueUid = this.cartItemForm?.variations?.[variationUid];
                            if (!selectedValueUid) {
                                continue;
                            }

                            const selectedValue = (variation?.values || []).find(
                                (v) => String(v?.uid) === String(selectedValueUid)
                            );

                            const valueSlug = slugify(selectedValue?.label);
                            if (!valueSlug) {
                                continue;
                            }

                            current.searchParams.set(key, valueSlug);
                        }
                    }
                } catch (_) {}

                window.history.replaceState({}, "", current.toString());
            } catch (_) {
                // Fallback: if URL() isn't available for any reason, do nothing.
            }
        },

        updateVariantDetails() {
            this.previewVariantName = null;
            this.setOldMediaLength();
            this.setVariant();
            this.resetQuantityToDefault();
            this.reduceToMaxQuantity();
            this.setVariantSlug();
            this.updateGallerySlider();
        },

        resetQuantityToDefault() {
            this.isEditingQty = false;
            this.qtyInput = "";
            this.updateQuantity(getDefaultQty(this.product));
        },

        updateSelectTypeOptionValue(optionId, event) {
            this.cartItemForm.options = Object.assign(
                {},
                this.cartItemForm.options,
                {
                    [optionId]: event.target.value,
                }
            );

            this.errors.clear(`options.${optionId}`);
        },

        updateCheckboxTypeOptionValue(optionId, event) {
            let values = $(event.target)
                .parents(".variant-check")
                .find('input[type="checkbox"]:checked')
                .map((_, el) => {
                    return el.value;
                });

            this.cartItemForm.options = Object.assign(
                {},
                this.cartItemForm.options,
                {
                    [optionId]: values.get(),
                }
            );
        },

        customRadioTypeOptionValueIsActive(optionId, valueId) {
            if (!this.cartItemForm.options.hasOwnProperty(optionId)) {
                return false;
            }

            return this.cartItemForm.options[optionId] === valueId;
        },

        syncCustomRadioTypeOptionValue(optionId, valueId) {
            if (this.customRadioTypeOptionValueIsActive(optionId, valueId)) {
                delete this.cartItemForm.options[optionId];
            } else {
                this.cartItemForm.options = Object.assign(
                    {},
                    this.cartItemForm.options,
                    {
                        [optionId]: valueId,
                    }
                );

                this.errors.clear(`options.${optionId}`);
            }
        },

        customCheckboxTypeOptionValueIsActive(optionId, valueId) {
            if (!this.cartItemForm.options.hasOwnProperty(optionId)) {
                this.cartItemForm.options = Object.assign(
                    {},
                    this.cartItemForm.options,
                    {
                        [optionId]: [],
                    }
                );

                return false;
            }

            return this.cartItemForm.options[optionId].includes(valueId);
        },

        syncCustomCheckboxTypeOptionValue(optionId, valueId) {
            if (this.customCheckboxTypeOptionValueIsActive(optionId, valueId)) {
                this.cartItemForm.options[optionId].splice(
                    this.cartItemForm.options[optionId].indexOf(valueId),
                    1
                );
            } else {
                this.cartItemForm.options[optionId].push(valueId);

                // Reassign the existing data due to reactivity issue
                this.cartItemForm = Object.assign(
                    {},
                    this.cartItemForm,
                    this.cartItemForm.options
                );

                this.errors.clear(`options.${optionId}`);
            }
        },

        setDescriptionContentHeight() {
            this.$nextTick(() => {
                this.showMore =
                    this.$refs.descriptionContent.clientHeight >= 400
                        ? true
                        : false;
            });
        },

        setCustomTabContentHeight() {
            this.$nextTick(() => {
                const el = this.$refs?.customTabContent || null;
                if (!el) {
                    this.showCustomTabMore = false;
                    return;
                }

                this.showCustomTabMore = el.clientHeight >= 400 ? true : false;
            });
        },

        setCustomTab2ContentHeight() {
            this.$nextTick(() => {
                const el = this.$refs?.customTab2Content || null;
                if (!el) {
                    this.showCustomTab2More = false;
                    return;
                }

                this.showCustomTab2More = el.clientHeight >= 400 ? true : false;
            });
        },

        setInactiveItemData() {
            this.item = {
                uid: this.item.uid,
                media: [],
                base_image: [],
            };
        },

        isMobileDevice() {
            return window.matchMedia("only screen and (max-width: 992px)")
                .matches;
        },

        shouldAllowGalleryTouchMove() {
            try {
                if (navigator?.maxTouchPoints > 0) {
                    return true;
                }
            } catch (_) {}

            try {
                if (window.matchMedia("(pointer: coarse)").matches) {
                    return true;
                }
            } catch (_) {}

            try {
                if (window.matchMedia("(hover: none)").matches) {
                    return true;
                }
            } catch (_) {}

            try {
                if (this.isMobileDevice()) {
                    return true;
                }
            } catch (_) {}

            return false;
        },

        // Unit tabanlı qty normalizasyonu
        normalizeQty(raw) {
            let v;

            if (typeof raw === "number") {
                v = raw;
            } else {
                const str = String(raw ?? "").trim().replace(",", ".");
                v = parseFloat(str);
            }

            if (!isFinite(v) || v <= 0) {
                v = this.minQty || 1;
            }

            if (typeof this.minQty === "number") {
                v = Math.max(v, this.minQty);
            }

            if (typeof this.maxQuantity === "number" && this.maxQuantity > 0) {
                v = Math.min(v, this.maxQuantity);
            }

            const step = this.stepQty || 0;
            if (step > 0) {
                v = Math.round(v / step) * step;
                v = Number(v.toFixed(3));
            }

            return v;
        },

        updateQuantity(nextQty) {
            const value = this.normalizeQty(nextQty);
            this.cartItemForm.qty = value;
        },

        exceedsMaxStock(qty) {
            return this.doesManageStock && this.item.qty < qty;
        },

        reduceToMaxQuantity() {
            if (this.doesManageStock && this.cartItemForm.qty > this.item.qty) {
                this.cartItemForm.qty = this.item.qty || 1;
            }
        },

        beginEditQty(event) {
            this.isEditingQty = true;
            // Input'a tıklayınca alan boşalsın, kullanıcı baştan yazsın
            this.qtyInput = "";
        },

        commitEditQty() {
            this.isEditingQty = false;

            const raw = (this.qtyInput || "").trim();
            if (raw === "") return;

            const val = Number(raw.replace(",", "."));
            if (Number.isNaN(val)) return;

            this.setQuantityManual(val);
        },

        setQuantityManual(val) {
            let v = Number(val);
            if (Number.isNaN(v)) return;

            const min = this.minQty || 1;
            if (v < min) v = min;

            if (this.exceedsMaxStock(v)) {
                this.cartItemForm.qty = this.item.qty;
                return;
            }

            if (this.product.unit_decimal) {
                this.cartItemForm.qty = Number(v.toFixed(2));
                return;
            }

            this.cartItemForm.qty = Math.round(v);
        },

        onQtyInput(event) {
            this.qtyInput = event.target.value;
        },

        addToCart() {
            if (this.isAddToCartDisabled) return;

            this.addingToCart = true;

            axios
                .post("/cart/items", {
                    ...this.cartItemForm,
                    ...(this.hasAnyVariant && { variant_id: this.item.id }),
                })
                .then((response) => {
                    this.$store.cart.updateCart(response.data);
                    this.$store.layout.openSidebarCart();
                    this.resetQuantityToDefault();
                })
                .catch(({ response }) => {
                    if (response.status === 422) {
                        this.errors.record(response.data.errors);
                    }

                    notify(response.data.message);
                })
                .finally(() => {
                    this.addingToCart = false;
                });
        },

        toggleDescriptionContent() {
            this.showDescriptionContent = !this.showDescriptionContent;
        },

        toggleCustomTabContent() {
            this.showCustomTabContent = !this.showCustomTabContent;
        },

        toggleCustomTab2Content() {
            this.showCustomTab2Content = !this.showCustomTab2Content;
        },

        initReviewLightbox() {
            try {
                if (this.reviewLightbox && typeof this.reviewLightbox.destroy === "function") {
                    this.reviewLightbox.destroy();
                }
            } catch (_) {}

            this.$nextTick(() => {
                try {
                    this.reviewLightbox = GLightbox({
                        selector: ".review-image-lightbox",
                        touchNavigation: true,
                        preload: false,
                        openEffect: "fade",
                        closeEffect: "fade",
                    });
                } catch (_) {}
            });
        },

        async fetchReviews() {
            if (this.reviewsLoaded && this.currentPage === 1) {
                return;
            }

            this.fetchingReviews = true;

            try {
                const response = await axios.get(
                    `/products/${this.product.id}/reviews?page=${this.currentPage}`
                );

                this.reviews = response.data;
                this.reviewsLoaded = true;
                this.initReviewLightbox();
            } catch (error) {
                notify(error.response.data.message);
            } finally {
                this.fetchingReviews = false;
            }
        },

        initReviewsDefer() {
            try {
                if (this._reviewsObserverInitialized) {
                    return;
                }

                const reviewsSection = document.querySelector("#reviews");

                const triggerFetch = () => {
                    if (!this.reviewsLoaded) {
                        this.fetchReviews();
                    }
                };

                if (!reviewsSection) {
                    setTimeout(triggerFetch, 1200);
                    return;
                }

                if ("IntersectionObserver" in window) {
                    this._reviewsObserverInitialized = true;

                    const observer = new IntersectionObserver(
                        (entries, obs) => {
                            entries.forEach((entry) => {
                                if (entry.isIntersecting) {
                                    triggerFetch();
                                    obs.disconnect();
                                }
                            });
                        },
                        {
                            rootMargin: "0px 0px -25% 0px",
                            threshold: 0.25,
                        }
                    );

                    observer.observe(reviewsSection);
                } else {
                    triggerFetch();
                }
            } catch (_) {
                setTimeout(() => {
                    if (!this.reviewsLoaded) {
                        this.fetchReviews();
                    }
                }, 1000);
            }
        },

        addNewReview(event) {
            this.addingNewReview = true;

            const formEl = event?.target || null;
            const formData = new FormData();

            formData.append("rating", this.reviewForm.rating || "");
            formData.append("reviewer_name", this.reviewForm.reviewer_name || "");
            formData.append("comment", this.reviewForm.comment || "");

            // Review kuponu için order_id bilgisini de ilet
            if (this.orderIdFromQuery) {
                formData.append("order_id", this.orderIdFromQuery);
            }

            this.reviewImages.forEach(({ file }) => {
                if (file) {
                    formData.append("images[]", file);
                }
            });

            if (window.grecaptcha) {
                formData.append("g-recaptcha-response", grecaptcha.getResponse());
            }

            axios
                .post(`/products/${this.product.id}/reviews`, formData, {
                    headers: { "Content-Type": "multipart/form-data" },
                })
                .then((response) => {
                    const newReview = response.data;

                    this.reviews.total = (this.reviews.total || 0) + 1;
                    this.reviews.data = Array.isArray(this.reviews.data)
                        ? [newReview, ...this.reviews.data]
                        : [newReview];

                    notify(trans("storefront::product.review_submitted"));

                    this.errors.reset();
                    this.reviewForm.rating = null;
                    this.reviewForm.reviewer_name = "";
                    this.reviewForm.comment = "";

                    if (formEl && typeof formEl.reset === "function") {
                        formEl.reset();
                    }

                    // URL.revokeObjectURL ile oluşturulan tüm preview'leri temizle
                    this.reviewImages.forEach((item) => {
                        if (item.preview) {
                            try { URL.revokeObjectURL(item.preview); } catch (_) {}
                        }
                    });

                    this.reviewImages = [];
                })
                .catch((error) => {
                    const response = error.response;

                    if (response && response.status === 422) {
                        this.errors.record(response.data.errors);

                        return;
                    }

                    if (response && response.data && response.data.message) {
                        notify(response.data.message);
                    }
                })
                .finally(() => {
                    this.addingNewReview = false;

                    if (window.grecaptcha) {
                        try { grecaptcha.reset(); } catch (_) {}
                    }
                });
        },

        openReviewFilePicker() {
            if (this.$refs.reviewFileInput) {
                this.$refs.reviewFileInput.click();
            }
        },

        onSelectReviewImages(event) {
            const input = event.target;
            const files = Array.from(input.files || []);

            if (!files.length) {
                return;
            }

            const allowed = new Set(["image/jpeg", "image/png", "image/webp", "image/avif"]);
            const existing = this.reviewImages.slice();
            const remainingSlots = Math.max(0, this.maxReviewPhotos - existing.length);

            const selected = files
                .filter((f) => allowed.has(f.type))
                .slice(0, remainingSlots)
                .map((file) => ({
                    file,
                    preview: URL.createObjectURL(file),
                }));

            this.reviewImages = existing.concat(selected);

            if (input) {
                input.value = "";
            }
        },

        onDropReviewImages(event) {
            this.isDraggingUpload = false;

            const dt = event.dataTransfer;
            if (!dt || !dt.files) return;

            const pseudoEvent = { target: { files: dt.files } };
            this.onSelectReviewImages(pseudoEvent);
        },

        removeReviewImage(index) {
            const item = this.reviewImages[index];

            if (item && item.preview) {
                try { URL.revokeObjectURL(item.preview); } catch (_) {}
            }

            this.reviewImages.splice(index, 1);
        },

        changePage(page) {
            this.currentPage = page;

            this.fetchReviews();
        },

        hideRelatedProductsSkeleton() {
            const skeletons = document.querySelectorAll(
                "[data-related-products] .related-products-carousel .swiper-slide-skeleton"
            );

            skeletons.forEach((skeleton) => skeleton.remove());
        },

        initUpSellProductsSlider() {
            const container = this.$refs.upSellProducts;
            if (!container) {
                return;
            }

            const nextEl = container.querySelector('.swiper-button-next');
            const prevEl = container.querySelector('.swiper-button-prev');

            // Prevent double initialization
            if (container.classList.contains('swiper-initialized') || container.__swiperInstance) {
                return;
            }

            container.__swiperInstance = new Swiper(container, {
                modules: [Navigation],
                slidesPerView: 1,
                navigation: {
                    nextEl,
                    prevEl,
                },
            });
        },

        initRelatedProductsSlider() {
            try {
                const root = document.querySelector('[data-related-products]');
                if (!root || root.classList.contains('d-none')) {
                    return;
                }
            } catch (_) {
                return;
            }

            const container = document.querySelector(
                '[data-related-products] .related-products-carousel.swiper'
            );
            if (!container) {
                return;
            }

            // Prevent double initialization which can break Alpine state (badges flicker/disappear)
            if (container.classList.contains('swiper-initialized') || container.__swiperInstance) {
                return;
            }

            this.hideRelatedProductsSkeleton();
            const paginationEl = container.querySelector('.swiper-pagination');

            container.__swiperInstance = new Swiper(container, {
                modules: [Pagination],
                slidesPerView: 2,
                pagination: paginationEl
                    ? {
                          el: paginationEl,
                          clickable: true,
                      }
                    : undefined,
                breakpoints: {
                    640: {
                        slidesPerView: 3,
                    },
                    880: {
                        slidesPerView: 4,
                    },
                    992: {
                        slidesPerView: 3,
                    },
                    1100: {
                        slidesPerView: 4,
                    },
                    1300: {
                        slidesPerView: 5,
                    },
                    1600: {
                        slidesPerView: 6,
                    },
                },
            });
        },
    })
);
