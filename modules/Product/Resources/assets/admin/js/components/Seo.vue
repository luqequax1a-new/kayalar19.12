<template>
    <div class="box-header">
        <h5>
            {{ trans("product::products.group.seo") }}
        </h5>

        <div class="drag-handle">
            <i class="fa fa-ellipsis-h" aria-hidden="true"></i>
            <i class="fa fa-ellipsis-h" aria-hidden="true"></i>
        </div>
    </div>

    <div class="box-body">
        <div class="form-group row">
            <label for="slug" class="col-sm-12 control-label text-left">
                {{ trans("product::attributes.slug") }}

                <span
                    v-if="window.location.pathname.endsWith('/edit')"
                    class="text-red"
                    >*</span
                >
            </label>

            <div class="col-sm-12">
                <div class="input-group">
                    <input
                        type="text"
                        name="slug"
                        id="slug"
                        class="form-control"
                        @change="setProductSlug($event.target.value)"
                        v-model="form.slug"
                    />
                    <span class="input-group-btn">
                        <button
                            type="button"
                            class="btn btn-default"
                            title="Slug oluştur"
                            aria-label="Slug oluştur"
                            @click="setProductSlug(form.name || '')"
                        >
                            <i class="fa fa-magic" aria-hidden="true"></i>
                        </button>
                    </span>
                </div>

                <div v-if="slugChanged" style="display:flex;align-items:center;gap:10px;margin-top:6px;">
                    <small class="text-muted">
                        Eski: <code>{{ oldUrl }}</code>
                    </small>
                    <small>→</small>
                    <small class="text-muted">
                        Yeni: <code>{{ newUrl }}</code>
                    </small>
                    <div class="switch" style="margin-left:auto;">
                        <input type="checkbox" id="redirect-on-slug-change" name="redirect_on_slug_change" v-model="form.redirect_on_slug_change" />
                        <label for="redirect-on-slug-change">301 yönlendirme</label>
                    </div>
                </div>

                <span
                    class="help-block text-red"
                    v-if="errors.has('slug')"
                    v-text="errors.get('slug')"
                ></span>

                <div v-if="slugAvailability.message || slugAvailability.checking" style="margin-top: 5px;">
                    <span
                        class="help-block text-red"
                        v-if="!slugAvailability.available"
                        style="margin: 0; display: flex; align-items: center; gap: 5px; font-weight: 500;"
                    >
                        <i class="fa fa-exclamation-triangle" aria-hidden="true"></i> {{ slugAvailability.message }}
                    </span>
                    <span
                        class="help-block text-muted"
                        v-else-if="slugAvailability.checking"
                        style="margin: 0; display: flex; align-items: center; gap: 5px;"
                    >
                        <i class="fa fa-circle-o-notch fa-spin" aria-hidden="true"></i> Kontrol ediliyor...
                    </span>
                    <span
                        class="help-block text-green"
                        v-else-if="form.slug && slugAvailability.available"
                        style="margin: 0; display: flex; align-items: center; gap: 5px; font-weight: 500;"
                    >
                        <i class="fa fa-check-circle" aria-hidden="true"></i> Bu URL kullanılabilir.
                    </span>
                </div>
            </div>
        </div>

        <div class="form-group row">
            <label for="meta-title" class="col-sm-12 control-label text-left">
                {{ trans("meta::attributes.meta_title") }}
            </label>

            <div class="col-sm-12">
                <input
                    type="text"
                    name="meta.meta_title"
                    id="meta-title"
                    class="form-control"
                    v-model="form.meta.meta_title"
                />

                <span
                    class="help-block text-red"
                    v-if="errors.has('meta.meta_title')"
                    v-text="errors.get('meta.meta_title')"
                ></span>
            </div>
        </div>

        <div class="form-group row">
            <label
                for="meta-description"
                class="col-sm-12 control-label text-left"
            >
                {{ trans("meta::attributes.meta_description") }}
            </label>

            <div class="col-sm-12">
                <textarea
                    name="meta.meta_description"
                    rows="6"
                    cols="10"
                    id="meta-description"
                    class="form-control"
                    v-model="form.meta.meta_description"
                ></textarea>

                <span
                    class="help-block text-red"
                    v-if="errors.has('meta.meta_description')"
                    v-text="errors.get('meta.meta_description')"
                ></span>
            </div>
        </div>
        <div class="st-seo-wrapper">
            <div class="st-seo-header">
                <div class="st-title-group">
                    <h5 class="st-main-title">Gelişmiş Yönlendirme Kontrolü</h5>
                    <span class="st-status-pill" :class="'is-' + redirectMode">{{ redirectPillText }}</span>
                </div>
                <p class="st-description">Ürün aktif olmadığında veya silindiğinde ziyaretçileri doğru sayfaya yönlendirerek SEO gücünü koruyun.</p>
            </div>

            <div class="st-options-grid">
                <div 
                    v-for="opt in redirectOptions" 
                    :key="opt.value"
                    class="st-option-card"
                    :class="{ 'is-active': currentType === opt.value }"
                    @click="setRedirectType(opt.value)"
                >
                    <div class="st-card-icon">
                        <i :class="opt.icon"></i>
                    </div>
                    <div class="st-card-body">
                        <span class="st-card-title">{{ opt.title }}</span>
                        <span class="st-card-desc">{{ opt.desc }}</span>
                    </div>
                    <div class="st-card-badge">{{ opt.badge }}</div>
                </div>
            </div>

            <div class="st-help-alert" :class="{ 'is-warning': redirectNeedsTarget && !form.redirect_target_id }">
                <i class="fa" :class="redirectNeedsTarget && !form.redirect_target_id ? 'fa-exclamation-circle' : 'fa-lightbulb-o'"></i>
                <span>{{ redirectHelpText }}</span>
            </div>

            <transition name="st-fade">
                <div class="st-target-area" v-if="isCategoryTarget || isProductTarget">
                    <div class="st-target-field">
                        <label class="st-input-label">
                            <i :class="isCategoryTarget ? 'fa fa-folder-open-o' : 'fa fa-search'"></i>
                            {{ isCategoryTarget ? 'Hedef Kategori Seçin' : 'Hedef Ürünü Arayın' }}
                        </label>
                        
                        <div class="st-select-wrapper" v-if="isCategoryTarget">
                            <select 
                                class="st-native-select" 
                                v-model="form.redirect_target_id"
                            >
                                <option value="">{{ trans("admin::admin.form.please_select") }}</option>
                                <option v-for="cat in categories" :key="cat.value" :value="cat.value">
                                    {{ cat.name }}
                                </option>
                            </select>
                        </div>

                        <div class="st-select-wrapper" v-if="isProductTarget" :class="{ 'has-error': redirectNeedsTarget && !form.redirect_target_id }">
                            <select 
                                ref="redirectTargetProdField"
                            ></select>
                        </div>
                        
                        <div class="st-error-container" v-if="redirectNeedsTarget && !form.redirect_target_id">
                            <i class="fa fa-info-circle"></i> Devam etmek için bir hedef belirlemelisiniz.
                        </div>
                    </div>
                </div>
            </transition>
        </div>
        <!-- END CUSTOM SECTION -->

    </div>
</template>

<script setup>
import { computed, ref, onMounted, watch, nextTick } from "vue";
import { debounce } from "lodash";
import { useForm } from "../composables/useForm";
import { useProductMethods } from "../composables/useProductMethods";
import { useConfigs } from "../composables/useConfigs";

const { form, errors } = useForm();
const { setProductSlug } = useProductMethods();
const { searchableSelectizeConfig } = useConfigs();

const categories = ref(FleetCart.data["categories"] ?? []);
const redirectTargetProdField = ref(null);

const slugAvailability = ref({
    available: true,
    message: "",
    checking: false,
});

const checkSlugAvailability = debounce(async (slug) => {
    if (!slug) {
        slugAvailability.value = { available: true, message: "", checking: false };
        return;
    }

    slugAvailability.value.checking = true;

    try {
        const baseUrl = String(axios?.defaults?.baseURL || "");
        const url = baseUrl.endsWith("/admin") ? "/slugs/check" : "/admin/slugs/check";

        const response = await axios.get(url, {
            params: {
                slug: slug,
                type: "product",
                entityId: form.id,
            },
        });

        slugAvailability.value = {
            available: response.data.available,
            message: response.data.message || "",
            checking: false,
        };
    } catch (error) {
        slugAvailability.value.checking = false;
    }
}, 500);

// Force initialization
if (!form.redirect_type) {
    form.redirect_type = '404';
}

const slugChanged = computed(() => !!form.original_slug && form.slug !== form.original_slug);
const oldUrl = computed(() => `/products/${(form.original_slug || '').replace(/^\//,'')}`);
const newUrl = computed(() => `/products/${(form.slug || '').replace(/^\//,'')}`);

// Defensive computed properties
const currentType = computed(() => form.redirect_type || '404');
const isCategoryTarget = computed(() => currentType.value.includes('category'));
const isProductTarget = computed(() => currentType.value.includes('product'));

const redirectNeedsTarget = computed(() => isCategoryTarget.value || isProductTarget.value);

const redirectMode = computed(() => {
    if (currentType.value === '404') return 'none';
    if (currentType.value === '410') return 'gone';
    if (currentType.value.includes('301')) return 'permanent';
    if (currentType.value.includes('302')) return 'temporary';
    return 'none';
});

const redirectPillText = computed(() => {
    if (currentType.value === '404') return '404';
    if (currentType.value === '410') return '410';
    if (currentType.value.includes('301')) return '301';
    if (currentType.value.includes('302')) return '302';
    return '';
});

const redirectOptions = [
    { value: '404', title: 'Yönlendirme Yok', desc: '404 (Sayfa Bulunamadı) döndürülür.', badge: '404', icon: 'fa fa-ban' },
    { value: '410', title: 'İçerik Kaldırıldı', desc: 'Google\'a içeriğin silindiğini söyler.', badge: '410', icon: 'fa fa-trash-o' },
    { value: '301-category', title: 'Kategoriye (Kalıcı)', desc: 'Kategoriye 301 yönlendirmesi yapar.', badge: '301', icon: 'fa fa-folder-open-o' },
    { value: '302-category', title: 'Kategoriye (Geçici)', desc: 'Kategoriye 302 yönlendirmesi yapar.', badge: '302', icon: 'fa fa-folder-o' },
    { value: '301-product', title: 'Ürüne (Kalıcı)', desc: 'Benzer ürüne 301 yönlendirmesi yapar.', badge: '301', icon: 'fa fa-link' },
    { value: '302-product', title: 'Ürüne (Geçici)', desc: 'Benzer ürüne 302 yönlendirmesi yapar.', badge: '302', icon: 'fa fa-random' },
];

const redirectHelpText = computed(() => {
    switch (currentType.value) {
        case '404': return 'Herhangi bir yönlendirme yapılmaz, kullanıcı hata sayfası ile karşılaşır.';
        case '410': return 'SEO açısından en sağlıklısıdır; sayfanın kalıcı olarak kaldırıldığını belirtir.';
        case '301-category': return 'Eski ürünün değerini ana kategorisine aktararak sıralamayı korur.';
        case '302-category': return 'Kısa süreli, geçici durumlar (stok yokluğu vb.) için uygundur.';
        case '301-product': return 'Ziyaretçiyi doğrudan yeni veya benzer ürüne gönderir.';
        case '302-product': return 'İleride geri gelecek ürünlerin yerine geçici yönlendirme sağlar.';
        default: return '';
    }
});

function setRedirectType(type) {
    form.redirect_type = type;
}

function initSelectize(element, options = {}) {
    if (!element) return;
    if (element.selectize) {
        element.selectize.destroy();
    }
    return $(element).selectize(options);
}

function destroyProductSelectize() {
    const el = redirectTargetProdField.value;
    if (!el) return;

    try {
        if (el.selectize) {
            el.selectize.destroy();
        }
    } catch (_) {
    }

    try {
        el.innerHTML = '';
    } catch (_) {
    }
}

function highlight(text, query) {
    if (!query) return text;
    const regex = new RegExp(`(${query.replace(/[-[\]{}()*+?.,\\^$|#\s]/g, '\\$&')})`, 'gi');
    return text.replace(regex, '<span class="st-highlight">$1</span>');
}

function initProductSelectize() {
    initSelectize(redirectTargetProdField.value, {
        maxItems: 1,
        valueField: "id",
        labelField: "name",
        searchField: ["name", "sku"],
        placeholder: 'Ürün adı veya SKU ile hızlıca aratın…',
        preload: 'focus',
        dropdownClass: 'st-premium-dropdown',
        render: {
            option: function (item, escape) {
                const query = this.lastQuery;
                const name = highlight(escape(item?.name ?? ""), query);
                const sku = escape(item?.sku ?? "");
                const category = escape(item?.category_name ?? "");
                const price = escape(item?.formatted_price ?? "");
                const inStock = item?.is_in_stock;
                const img = item?.base_image?.path ? escape(item.base_image.path) : "";

                const stockHtml = inStock 
                    ? `<span class="st-l-stock is-in"><i class="fa fa-check"></i> Stokta</span>`
                    : `<span class="st-l-stock is-out"><i class="fa fa-times"></i> Tükendi</span>`;

                const imgHtml = img
                    ? `<div class="st-l-img"><img src="${img}" alt="" /></div>`
                    : `<div class="st-l-img st-l-empty"><i class="fa fa-image"></i></div>`;

                return `
                    <div class="st-l-row">
                        ${imgHtml}
                        <div class="st-l-main">
                            <div class="st-l-title">${name}</div>
                            <div class="st-l-meta">
                                <span class="st-l-sku">${sku}</span>
                                <span class="st-l-dot"></span>
                                <span class="st-l-cat">${category}</span>
                            </div>
                        </div>
                        <div class="st-l-side">
                            <div class="st-l-price">${price}</div>
                            ${stockHtml}
                        </div>
                    </div>
                `;
            },
            item: function (item, escape) {
                const name = escape(item?.name ?? "");
                const img = item?.base_image?.path ? escape(item.base_image.path) : "";
                const imgHtml = img
                    ? `<img src="${img}" class="st-s-img" alt="" />`
                    : `<div class="st-s-img st-s-empty"><i class="fa fa-image"></i></div>`;

                return `
                    <div class="st-s-item">
                        ${imgHtml}
                        <span class="st-s-text">${name}</span>
                    </div>
                `;
            }
        },
        onDropdownOpen: function ($dropdown) {
            $dropdown.hide().fadeIn(150);
        },
        onDropdownClose: function ($dropdown) {
            $dropdown.show().fadeOut(100);
        },
        load: function (query, callback) {
            const baseUrl = String(axios?.defaults?.baseURL || "");
            const url = baseUrl.endsWith("/admin") ? "/products" : "/admin/products";

            axios.get(url, {
                params: {
                    query: query,
                    initial: !query ? 1 : undefined,
                    limit: !query ? 15 : 30,
                },
            })
            .then((response) => {
                const payload = response?.data;
                const items = Array.isArray(payload) ? payload : (payload?.data || []);
                callback(items);
            })
            .catch(() => callback());
        },
        onInitialize() {
            if (form.redirect_target && form.redirect_target.id) {
                this.addOption(form.redirect_target);
                this.setValue(form.redirect_target.id, true);
            }
        },
        onChange: (value) => {
            form.redirect_target_id = value || null;
        }
    });
}

// Watchers
watch(isProductTarget, (val) => {
    if (val) {
        nextTick(() => initProductSelectize());
        return;
    }

    nextTick(() => destroyProductSelectize());
});

watch(() => form.redirect_type, (newVal, oldVal) => {
    if (!oldVal) return;
    
    // Reset ID if target type changes (e.g. category -> product)
    const oldGroup = oldVal.includes('product') ? 'product' : (oldVal.includes('category') ? 'category' : 'none');
    const newGroup = (newVal || '').includes('product') ? 'product' : ((newVal || '').includes('category') ? 'category' : 'none');
    
    if (oldGroup !== newGroup) {
        if (oldGroup === 'product') {
            destroyProductSelectize();
        }

        form.redirect_target_id = null;
        form.redirect_target = null;
    }
});

watch(() => form.slug, (newSlug) => {
    checkSlugAvailability(newSlug);
}, { immediate: true });

onMounted(async () => {
    await nextTick();
    
    if (isProductTarget.value) initProductSelectize();
});

</script>

<style scoped>
/* Resizable & Borderless Main Wrapper */
.st-seo-wrapper {
    margin-top: 20px;
    padding: 0;
    background: transparent;
    border: none;
    box-shadow: none;
}

.st-seo-header { margin-bottom: 24px; padding-left: 2px; }
.st-title-group { display: flex; align-items: center; gap: 14px; margin-bottom: 8px; }
.st-main-title { margin: 0; font-size: 16px; font-weight: 800; color: #1a202c; }

.st-status-pill {
    font-size: 10px;
    font-weight: 800;
    padding: 2px 10px;
    border-radius: 100px;
    text-transform: uppercase;
}
.st-status-pill.is-permanent { background: #d1fae5; color: #065f46; }
.st-status-pill.is-temporary { background: #fef3c7; color: #92400e; }
.st-status-pill.is-gone { background: #fee2e2; color: #991b1b; }
.st-status-pill.is-none { background: #f7fafc; color: #4a5568; }

.st-description { font-size: 13px; color: #718096; line-height: 1.5; margin: 0; }

/* Selection Grid */
.st-options-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 16px;
    margin-top: 20px;
}

.st-option-card {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 20px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    cursor: pointer;
    position: relative;
    transition: all 0.2s ease;
}

.st-option-card:hover {
    border-color: #cbd5e1;
    background: #f8fafc;
    transform: translateY(-2px);
}

.st-option-card.is-active {
    border-color: #3182ce;
    background: #f0f7ff;
    box-shadow: 0 4px 12px rgba(49, 130, 206, 0.08);
}

.st-card-icon {
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f7fafc;
    border-radius: 10px;
    color: #4a5568;
    font-size: 18px;
    transition: all 0.2s ease;
}

.is-active .st-card-icon {
    background: #3182ce;
    color: #ffffff;
}

.st-card-body { flex: 1; min-width: 0; }
.st-card-title { display: block; font-weight: 700; font-size: 14px; color: #2d3748; margin-bottom: 2px; }
.st-card-desc { display: block; font-size: 12px; color: #718096; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

.st-card-badge {
    font-size: 9px;
    font-weight: 900;
    color: #a0aec0;
    position: absolute;
    top: 10px;
    right: 12px;
}

.st-help-alert {
    margin-top: 24px;
    padding: 14px 18px;
    background: #f0f9ff;
    border-radius: 12px;
    font-size: 13px;
    color: #2c5282;
    display: flex;
    align-items: center;
    gap: 12px;
    border-left: 4px solid #3182ce;
}

.st-help-alert.is-warning { background: #fffaf0; color: #7b341e; border-left-color: #dd6b20; }

.st-target-area {
    margin-top: 24px;
    padding-top: 24px;
    border-top: 1px solid #edf2f7;
}

.st-input-label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 12px;
}

.st-input-label i { color: #3182ce; }

.st-select-wrapper { position: relative; z-index: 1000; }

.st-native-select {
    width: 100%;
    height: 46px;
    padding: 0 12px;
    border-radius: 8px;
    border: 1px solid #cbd5e1;
    background: #ffffff;
    font-size: 14px;
    color: #2d3748;
}

.st-error-container {
    margin-top: 12px;
    display: flex;
    align-items: center;
    gap: 8px;
    color: #e53e3e;
    font-size: 13px;
    font-weight: 600;
}

.st-fade-enter-active, .st-fade-leave-active { transition: all 0.3s ease; }
.st-fade-enter-from, .st-fade-leave-to { opacity: 0; transform: translateY(-10px); }
</style>

<style>
/* =========================
   FAST & CLEAN PRODUCT SELECTOR
   ========================= */

.st-select-wrapper .selectize-input {
    min-height: 48px !important;
    padding: 10px 16px !important;
    border-radius: 10px !important;
    border: 1px solid #d2d6de !important;
    background: #ffffff !important;
    box-shadow: none !important;
    display: flex !important;
    align-items: center !important;
    transition: border-color 0.2s ease !important;
}

.st-select-wrapper .selectize-input.focus {
    border-color: #3b82f6 !important;
}

/* Fast Dropdown Container */
.st-premium-dropdown.selectize-dropdown {
    margin-top: 4px !important;
    border-radius: 12px !important;
    overflow: hidden !important;
    border: 1px solid rgba(226, 232, 240, 0.9) !important;
    background: #ffffff !important;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
    z-index: 99999 !important;
}

.st-premium-dropdown .selectize-dropdown-content {
    max-height: 380px !important;
    padding: 8px !important;
}

/* Redesigned Product Row - Fast & Structured */
.st-premium-dropdown .option {
    padding: 12px 14px !important;
    margin-bottom: 4px !important;
    border-radius: 8px !important;
    border: 1px solid transparent !important;
    background: #ffffff !important;
    transition: all 0.1s ease !important;
    cursor: pointer !important;
}

.st-premium-dropdown .option.active,
.st-premium-dropdown .option:hover {
    background: #f1f5f9 !important;
    border-color: #e2e8f0 !important;
}

.st-l-row {
    display: flex;
    align-items: center;
    gap: 14px;
    width: 100%;
}

/* Image */
.st-l-img {
    width: 44px;
    height: 44px;
    border-radius: 6px;
    background: #f8fafc;
    border: 1px solid #edf2f7;
    overflow: hidden;
    flex-shrink: 0;
}
.st-l-img img { width: 100%; height: 100%; object-fit: cover; }
.st-l-img.st-l-empty { display: flex; align-items: center; justify-content: center; }
.st-l-img.st-l-empty i { color: #cbd5e1; font-size: 16px; }

/* Text Main */
.st-l-main { flex: 1; min-width: 0; }
.st-l-title {
    font-size: 14px;
    font-weight: 700;
    color: #1a202c;
    margin-bottom: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.st-l-meta {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 11px;
    font-weight: 600;
    color: #718096;
}
.st-l-sku { background: #f1f5f9; padding: 0 5px; border-radius: 4px; }
.st-l-dot { width: 3px; height: 3px; border-radius: 50%; background: #cbd5e1; }
.st-l-cat { color: #a0aec0; }

/* Side Area */
.st-l-side { text-align: right; flex-shrink: 0; }
.st-l-price { font-size: 13px; font-weight: 800; color: #059669; margin-bottom: 2px; }
.st-l-stock { font-size: 10px; font-weight: 800; text-transform: uppercase; display: flex; align-items: center; gap: 4px; }
.st-l-stock.is-in { color: #10b981; }
.st-l-stock.is-out { color: #ef4444; }

/* Search Highlight */
.st-highlight {
    background: #fef9c3;
    color: #92400e;
    padding: 0 2px;
    font-weight: 800;
}

/* Selected Input Item */
.st-s-item { display: flex; align-items: center; gap: 10px; width: 100%; }
.st-s-img { width: 28px; height: 28px; border-radius: 4px; border: 1px solid #edf2f7; object-fit: cover; }
.st-s-text { font-size: 14px; font-weight: 700; color: #1a202c; }
</style>
