import axios from "axios";

const DEBUG = false;
const log = (...args) => DEBUG && console.log('[Upsell]', ...args);

export default function registerCartUpsellBox(Alpine) {
    // Single component for both single and multi-offer scenarios
    Alpine.data("CartUpsellBox", (params) => {
        const isSingleOffer = params.offer && !params.offers;
        const isMultiOffer = params.offers && Array.isArray(params.offers);
        
        if (isSingleOffer) {
            // Single offer mode (legacy upsell_box.blade.php)
            return {
                offer: params.offer,
                addUpsellUrl: params.addUpsellUrl,
                show: true,
                adding: false,
                remainingSeconds: params.offer.countdown_seconds || null,
                countdownTimer: null,
                countdownEndAt: null,
                showQuantityModal: false,
                modalStep: 'variant',
                selectedVariantId: null,
                selectedVariant: null,
                selectedQty: params.offer.unit_min || 1,
                // Quantity editing state (same as product detail)
                isEditingQty: false,
                qtyInput: '',

                init() {
                    if (this.remainingSeconds && this.remainingSeconds > 0) {
                        this.countdownEndAt = Date.now() + this.remainingSeconds * 1000;

                        this.countdownTimer = setInterval(() => {
                            if (this.countdownEndAt) {
                                const diffMs = this.countdownEndAt - Date.now();
                                const nextRemaining = Math.max(0, Math.round(diffMs / 1000));
                                this.remainingSeconds = nextRemaining;
                            }

                            if (!this.showCountdown) {
                                if (this.countdownTimer) {
                                    clearInterval(this.countdownTimer);
                                }
                                this.show = false;
                            }
                        }, 1000);
                    }
                },

                get showCountdown() {
                    return this.remainingSeconds !== null && this.remainingSeconds > 0;
                },

                get countdownLabel() {
                    if (!this.showCountdown) {
                        return "";
                    }

                    const minutes = Math.floor(this.remainingSeconds / 60)
                        .toString()
                        .padStart(2, "0");
                    const seconds = (this.remainingSeconds % 60)
                        .toString()
                        .padStart(2, "0");

                    return `${minutes}:${seconds}`;
                },

                get dismissKey() {
                    return `fc_upsell_rule_${this.offer.rule_id}_dismissed`;
                },

                get formattedOriginalPrice() {
                    return this.formatCurrency(this.offer.original_price || 0);
                },

                get formattedCurrentPrice() {
                    return this.formatCurrency(this.offer.upsell_price || 0);
                },

                reject() {
                    this.trackEvent('offer_rejected', {
                        variant_id: this.selectedVariantId,
                        has_countdown: this.showCountdown
                    });
                    this.show = false;
                },

                openQuantityModal() {
                    this.trackEvent('modal_opened', {
                        has_variants: this.offer.variants?.length > 0,
                        let_customer_choose: this.offer.let_customer_choose
                    });
                    
                    if (this.offer.let_customer_choose && this.offer.variants?.length > 0) {
                        this.modalStep = 'variant';
                        this.selectedVariantId = this.offer.variants.find(v => v.is_default)?.id || this.offer.variants[0]?.id;
                    } else {
                        this.modalStep = 'quantity';
                    }
                    this.selectedQty = this.offer.unit_min || 1;
                    this.showQuantityModal = true;
                },

                closeQuantityModal() {
                    this.showQuantityModal = false;
                },

                selectVariant(variant) {
                    this.selectedVariantId = variant.id;
                    this.selectedVariant = variant;
                    
                    // Update prices based on variant
                    const discountType = this.offer.discount_type;
                    const discountValue = this.offer.discount_value;
                    const variantPrice = variant.selling_price || variant.price || 0;
                    
                    if (discountType === 'percent') {
                        this.offer.upsell_price = variantPrice * (1 - (discountValue / 100));
                    } else if (discountType === 'fixed') {
                        this.offer.upsell_price = Math.max(0, variantPrice - discountValue);
                    } else {
                        this.offer.upsell_price = variantPrice;
                    }
                    this.offer.original_price = variantPrice;
                },

                goToQuantityStep() {
                    // Only require variant selection if there are variants to choose from
                    if (this.offer.let_customer_choose && this.offer.variants?.length > 0 && !this.selectedVariantId) {
                        return;
                    }
                    this.modalStep = 'quantity';
                },

                updateQuantity(newQty) {
                    const min = this.offer.unit_min || (this.offer.unit_decimal ? 0.5 : 1);
                    const max = this.offer.manage_stock ? this.offer.stock_qty : 999999;
                    const step = this.offer.unit_step || (this.offer.unit_decimal ? 0.5 : 1);
                    
                    // Round to step
                    let qty = Math.max(min, Math.min(max, newQty));
                    if (this.offer.unit_decimal) {
                        qty = Math.round(qty * 100) / 100; // 2 decimal places
                    } else {
                        qty = Math.round(qty);
                    }
                    this.selectedQty = qty;
                },

                // Quantity editing functions (same as product detail)
                beginEditQty(event) {
                    this.isEditingQty = true;
                    // Input'a tıklayınca alan boşalsın, kullanıcı baştan yazsın (ürün detay gibi)
                    this.qtyInput = "";
                },

                commitEditQty() {
                    this.isEditingQty = false;
                    let val = parseFloat(this.qtyInput.replace(',', '.'));
                    if (isNaN(val) || val <= 0) {
                        val = this.offer.unit_min || (this.offer.unit_decimal ? 0.5 : 1);
                    }
                    this.updateQuantity(val);
                },

                onQtyInput(event) {
                    // Allow only numbers, comma and dot
                    let val = event.target.value.replace(/[^0-9.,]/g, '');
                    // Replace comma with dot for parsing
                    val = val.replace(',', '.');
                    this.qtyInput = val;
                },

                async add() {
                    if (this.adding) return;

                    this.adding = true;

                    try {
                        const payload = {
                            rule_id: this.offer.rule_id,
                            product_id: this.offer.product_id,
                            variant_id: this.selectedVariantId || this.offer.preselected_variant_id || null,
                            qty: this.selectedQty || 1,
                            placement: this.offer.placement || 'checkout',
                        };

                        await axios.post(this.addUpsellUrl, payload);
                        
                        this.trackEvent('offer_accepted', {
                            variant_id: payload.variant_id,
                            qty: payload.qty,
                            total_value: (this.offer.upsell_price || 0) * payload.qty
                        });
                        
                        this.showSuccessFeedback();
                        this.closeQuantityModal();

                        if (this.offer.placement === 'checkout') {
                            if (window.location && typeof window.location.reload === "function") {
                                window.location.reload();
                            }
                        } else {
                            if (window.location && window.location.href) {
                                window.location.href = '/cart';
                            }
                        }
                    } catch (error) {
                        this.showErrorFeedback(error);
                        console.error("Upsell add failed", error);
                    } finally {
                        this.adding = false;
                    }
                },

                formatCurrency(value) {
                    if (typeof window.formatCurrency === 'function') {
                        return window.formatCurrency(value);
                    }
                    return new Intl.NumberFormat('tr-TR', {
                        style: 'currency',
                        currency: 'TRY',
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2,
                    }).format(value).replace('TRY', '₺');
                },

                showSuccessFeedback() {
                    this.show = false;
                },

                showErrorFeedback(error) {
                    this.handleError(error, 'single_offer_add_failed');
                },

                trackEvent(action, data = {}) {
                    try {
                        if (typeof window.gtag === 'function') {
                            window.gtag('event', 'upsell_' + action, {
                                event_category: 'upsell',
                                event_label: this.offer.placement || 'cart',
                                rule_id: this.offer.rule_id,
                                product_id: this.offer.product_id,
                                ...data
                            });
                        }
                        if (typeof window.dataLayer !== 'undefined') {
                            window.dataLayer.push({
                                event: 'upsell_' + action,
                                upsellData: {
                                    rule_id: this.offer.rule_id,
                                    product_id: this.offer.product_id,
                                    ...data
                                }
                            });
                        }
                    } catch (e) {
                        log('Analytics tracking failed:', e);
                    }
                },

                handleError(error, context = 'unknown') {
                    let errorMessage = 'Teklif eklenirken hata oluştu';
                    let errorType = 'unknown';

                    if (error.response) {
                        const status = error.response.status;
                        errorType = 'http_' + status;
                        
                        if (status === 422) {
                            errorMessage = error.response.data?.message || 'Geçersiz veri';
                        } else if (status === 404) {
                            errorMessage = 'Ürün bulunamadı';
                        } else if (status === 429) {
                            errorMessage = 'Çok fazla istek. Lütfen bekleyin.';
                        } else if (status >= 500) {
                            errorMessage = 'Sunucu hatası. Lütfen tekrar deneyin.';
                        } else {
                            errorMessage = error.response.data?.message || errorMessage;
                        }
                    } else if (error.request) {
                        errorType = 'network';
                        errorMessage = 'Bağlantı hatası. İnternet bağlantınızı kontrol edin.';
                    }

                    this.trackEvent('error', { 
                        context, 
                        error_type: errorType,
                        error_message: errorMessage 
                    });

                    if (window.alert) {
                        alert(errorMessage);
                    }

                    if (DEBUG) {
                        console.error('[Upsell Error]', context, error);
                    }
                },
            };
        } else if (isMultiOffer) {
            // Multi-offer mode (upsell_offers.blade.php)
            return {
                rule: params.rule,
                offers: params.offers || [],
                addUpsellUrl: params.addUpsellUrl,
                placement: params.placement || 'checkout',
                countdownSeconds: params.countdownSeconds,
                activeIndex: 0,
                showQuantityModal: false,
                selectedVariant: null,
                selectedVariantId: null,
                selectedQty: 1,
                adding: false,
                modalStep: 'variant',
                isEditingQty: false,
                qtyInput: '',
                lastAction: null, // 'accepted' or 'rejected'

                get currentOffer() {
                    // Return offer at current activeIndex if it passes trigger check
                    const offer = this.offers[this.activeIndex];
                    return offer || null;
                },
                
                // Find and move to next valid offer based on trigger conditions
                findNextValidOffer() {
                    log('findNextValidOffer called, activeIndex:', this.activeIndex, 'lastAction:', this.lastAction);
                    for (let i = this.activeIndex; i < this.offers.length; i++) {
                        const offer = this.offers[i];
                        const shouldShow = this.shouldShowOffer(offer);
                        log('Checking offer', i, 'trigger:', offer.trigger, 'shouldShow:', shouldShow);
                        if (shouldShow) {
                            this.activeIndex = i;
                            log('Found valid offer at index', i);
                            this.trackEvent('offer_shown', { offer_id: offer.offer_id, product_id: offer.product_id });
                            return true;
                        }
                    }
                    log('No valid offer found, closing');
                    this.activeIndex = this.offers.length;
                    return false;
                },
                
                // Check if offer should be shown based on trigger conditions
                shouldShowOffer(offer) {
                    const trigger = offer.trigger || 'always';
                    
                    // If no previous action yet (first offer), only show if trigger is 'always'
                    if (this.lastAction === null) {
                        return trigger === 'always';
                    }
                    
                    // Check trigger condition against last action
                    // Support both short ('rejected', 'accepted') and long ('rejected_previous', 'accepted_previous') formats
                    switch (trigger) {
                        case 'always':
                            return true;
                        case 'rejected':
                        case 'rejected_previous':
                            return this.lastAction === 'rejected';
                        case 'accepted':
                        case 'accepted_previous':
                            return this.lastAction === 'accepted';
                        default:
                            log('Unknown trigger:', trigger);
                            return false;
                    }
                },

                get showCountdown() {
                    return this.countdownSeconds !== null && this.countdownSeconds > 0;
                },

                get countdownLabel() {
                    if (!this.showCountdown) return "";
                    
                    const minutes = Math.floor(this.countdownSeconds / 60).toString().padStart(2, "0");
                    const seconds = (this.countdownSeconds % 60).toString().padStart(2, "0");
                    return `${minutes}:${seconds}`;
                },

                get currentPrice() {
                    if (!this.currentOffer) return 0;
                    return this.currentOffer.upsell_price || 0;
                },

                init() {
                    if (this.showCountdown) {
                        const timer = setInterval(() => {
                            this.countdownSeconds--;
                            if (this.countdownSeconds <= 0) {
                                clearInterval(timer);
                                this.closeAll();
                            }
                        }, 1000);
                    }
                },

                closeAll() {
                    this.activeIndex = this.offers.length;
                    this.showQuantityModal = false;
                },

                closeQuantityModal() {
                    this.showQuantityModal = false;
                },

                // Called when user rejects current offer
                rejectOffer() {
                    const offer = this.currentOffer;
                    if (offer) {
                        this.trackEvent('offer_rejected', { 
                            offer_id: offer.offer_id, 
                            product_id: offer.product_id,
                            trigger: offer.trigger 
                        });
                    }
                    this.lastAction = 'rejected';
                    this.activeIndex++;
                    this.findNextValidOffer();
                },
                
                // Legacy alias
                nextOffer() {
                    this.rejectOffer();
                },

                handleAction() {
                    const offer = this.currentOffer;
                    if (!offer) return;

                    // Always open quantity modal for user to confirm
                    this.openQuantityModal();
                },

                openQuantityModal() {
                    const offer = this.currentOffer;
                    
                    this.trackEvent('modal_opened', {
                        offer_id: offer.offer_id,
                        product_id: offer.product_id,
                        has_variants: offer.variants?.length > 0
                    });
                    
                    if (offer.let_customer_choose && offer.variants?.length > 0) {
                        this.modalStep = 'variant';
                        this.selectedVariantId = offer.variant_id || (offer.variants?.find(v => v.is_default)?.id || offer.variants?.[0]?.id);
                    } else {
                        this.modalStep = 'quantity';
                        this.selectedVariantId = offer.variant_id || null;
                    }
                    
                    this.selectedQty = offer.unit_min || (offer.unit_decimal ? 0.5 : 1);
                    this.showQuantityModal = true;
                },

                selectVariant(v) {
                    this.selectedVariantId = v.id;
                    this.selectedVariant = v;
                },

                goToQuantityStep() {
                    this.modalStep = 'quantity';
                },

                changeQty(amount) {
                    const step = this.currentOffer?.unit_step || 1;
                    const min = this.currentOffer?.unit_min || 1;
                    const newVal = this.selectedQty + (amount * step);
                    this.selectedQty = Math.max(min, newVal);
                },

                setQty(qty) {
                    const min = this.currentOffer?.unit_min || (this.currentOffer?.unit_decimal ? 0.5 : 1);
                    const max = this.currentOffer?.manage_stock ? this.currentOffer.stock_qty : 999999;
                    let newQty = Math.max(min, Math.min(max, qty));
                    
                    // Round appropriately
                    if (this.currentOffer?.unit_decimal) {
                        newQty = Math.round(newQty * 100) / 100; // 2 decimal places
                    } else {
                        newQty = Math.round(newQty);
                    }
                    this.selectedQty = newQty;
                },

                beginEditQty(event) {
                    this.isEditingQty = true;
                    // Input'a tıklayınca alan boşalsın, kullanıcı baştan yazsın (ürün detay gibi)
                    this.qtyInput = "";
                },

                commitEditQty() {
                    this.isEditingQty = false;
                    const parsed = parseFloat(this.qtyInput.replace(',', '.'));
                    if (!isNaN(parsed) && parsed > 0) {
                        this.setQty(parsed);
                    }
                },

                onQtyInput(event) {
                    this.qtyInput = event.target.value;
                },

                confirmQuantity() {
                    if (this.currentOffer) {
                        this.addToCart(this.currentOffer.product_id, this.selectedVariantId, this.selectedQty);
                        this.closeQuantityModal();
                    }
                },

                async addToCart(productId, variantId, qty) {
                    this.adding = true;

                    try {
                        const payload = {
                            rule_id: this.rule.id,
                            product_id: productId,
                            variant_id: variantId || this.selectedVariant?.id || null,
                            qty: this.selectedQty,
                            placement: this.placement,
                        };

                        await axios.post(this.addUpsellUrl, payload);
                        
                        this.trackEvent('offer_accepted', {
                            offer_id: this.currentOffer.offer_id,
                            product_id: payload.product_id,
                            variant_id: payload.variant_id,
                            qty: payload.qty,
                            total_value: (this.currentOffer.upsell_price || 0) * payload.qty
                        });
                        
                        this.lastAction = 'accepted';
                        this.activeIndex++;
                        
                        // Find next valid offer based on trigger conditions
                        const hasNextOffer = this.findNextValidOffer();
                        if (hasNextOffer && this.currentOffer) {
                            // Show next offer without page reload
                            this.closeQuantityModal();
                        } else {
                            // No more offers, close and reload
                            this.closeAll();
                            if (this.placement === 'checkout') {
                                window.location.reload();
                            } else {
                                window.location.href = '/cart';
                            }
                        }
                    } catch (error) {
                        this.handleError(error, 'add_to_cart_failed');
                    } finally {
                        this.adding = false;
                    }
                },

                formatCurrency(val) {
                    if (typeof window.formatCurrency === 'function') {
                        return window.formatCurrency(val);
                    }
                    return new Intl.NumberFormat('tr-TR', {
                        style: 'currency',
                        currency: 'TRY',
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2,
                    }).format(val).replace('TRY', '₺');
                },

                trackEvent(action, data = {}) {
                    try {
                        if (typeof window.gtag === 'function') {
                            window.gtag('event', 'upsell_' + action, {
                                event_category: 'upsell',
                                event_label: this.placement,
                                ...data
                            });
                        }
                        if (typeof window.dataLayer !== 'undefined') {
                            window.dataLayer.push({
                                event: 'upsell_' + action,
                                upsellData: data
                            });
                        }
                    } catch (e) {
                        log('Analytics tracking failed:', e);
                    }
                },

                handleError(error, context = 'unknown') {
                    let errorMessage = 'Teklif eklenirken hata oluştu';
                    let errorType = 'unknown';

                    if (error.response) {
                        const status = error.response.status;
                        errorType = 'http_' + status;
                        
                        if (status === 422) {
                            errorMessage = error.response.data?.message || 'Geçersiz veri';
                        } else if (status === 404) {
                            errorMessage = 'Ürün bulunamadı';
                        } else if (status === 429) {
                            errorMessage = 'Çok fazla istek. Lütfen bekleyin.';
                        } else if (status >= 500) {
                            errorMessage = 'Sunucu hatası. Lütfen tekrar deneyin.';
                        } else {
                            errorMessage = error.response.data?.message || errorMessage;
                        }
                    } else if (error.request) {
                        errorType = 'network';
                        errorMessage = 'Bağlantı hatası. İnternet bağlantınızı kontrol edin.';
                    }

                    this.trackEvent('error', { 
                        context, 
                        error_type: errorType,
                        error_message: errorMessage 
                    });

                    if (window.alert) {
                        alert(errorMessage);
                    }

                    if (DEBUG) {
                        console.error('[Upsell Error]', context, error);
                    }
                },
            };
        }
        
        // Fallback empty component
        return {
            show: false
        };
    });
}
